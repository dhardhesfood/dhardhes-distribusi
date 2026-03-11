<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SalesSettlement;
use App\Models\SalesSettlementCostDetail;
use App\Models\SalesTransaction;
use App\Models\ReceivablePayment;
use App\Models\Visit;
use App\Models\User;
use App\Models\Kasbon;
use App\Models\CashSale;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SalesSettlementController extends Controller
{
    public function index(Request $request)
    {
        $visitsQuery = Visit::where('status', 'approved');

        if ($request->filled('tanggal_dari')) {
            $visitsQuery->whereDate('visit_date','>=',$request->tanggal_dari);
        }

        if ($request->filled('tanggal_sampai')) {
            $visitsQuery->whereDate('visit_date','<=',$request->tanggal_sampai);
        }

        if ($request->filled('user_id')) {
            $visitsQuery->where('user_id',$request->user_id);
        }

        $visits = $visitsQuery->get();

        $grouped = $visits->groupBy(function ($item) {
            return $item->user_id.'|'.$item->visit_date;
        });

        $users = User::pluck('name','id');
        $results = [];

        foreach ($grouped as $items) {

            $userId = $items->first()->user_id;
            $date   = $items->first()->visit_date;
            $visitIds = $items->pluck('id');

            $cash = SalesTransaction::whereIn('visit_id',$visitIds)
                ->sum('cash_paid');

            $consignment = DB::table('sales_transactions as st')
                ->join('visits as v','st.visit_id','=','v.id')
                ->whereIn('st.visit_id',$visitIds)
                ->sum(DB::raw('(st.total_amount - v.admin_fee) - st.cash_paid'));

            $receivable = ReceivablePayment::where('user_id',$userId)
                ->whereDate('payment_date',$date)
                ->sum('amount');

            $cashSaleDirect = CashSale::where('user_id',$userId)
                ->whereDate('sale_date',$date)
                ->where('status','locked')
                ->sum('total');

            $adminFee = $items->sum('admin_fee');

            $settlement = SalesSettlement::where('user_id',$userId)
                ->whereDate('settlement_date',$date)
                ->first();

            $results[] = (object)[
                'user_id'          => $userId,
                'user_name'        => $users[$userId] ?? '-',
                'settlement_date'  => $date,
                'total_cash'       => $cash,
                'total_consignment'=> $consignment,
                'total_receivable' => $receivable,
                'total_cash_direct'=> $cashSaleDirect,
                'total_admin_fee'  => $adminFee,
                'actual_amount'    => $settlement->actual_amount ?? 0,
                'status'           => $settlement->status ?? 'draft'
            ];
        }

        return view('sales_settlements.index', [
            'settlements' => collect($results)->sortByDesc('settlement_date'),
            'users'       => User::orderBy('name')->get()
        ]);
    }


    public function show($userId, $date)
    {
        $date = Carbon::parse($date)->format('Y-m-d');

        $visits = Visit::where('user_id', $userId)
            ->whereDate('visit_date', $date)
            ->where('status', 'approved')
            ->get();

        $visitIds = $visits->pluck('id');

        $cashSales = SalesTransaction::whereIn('visit_id', $visitIds)
            ->sum('cash_paid');

        $consignmentSales = DB::table('sales_transactions as st')
            ->join('visits as v', 'st.visit_id', '=', 'v.id')
            ->whereIn('st.visit_id', $visitIds)
            ->sum(DB::raw('(st.total_amount - v.admin_fee) - st.cash_paid'));

        $receivablePayments = ReceivablePayment::where('user_id', $userId)
            ->whereDate('payment_date', $date)
            ->sum('amount');

        $cashSaleDirect = CashSale::where('user_id',$userId)
            ->whereDate('sale_date',$date)
            ->where('status','locked')
            ->sum('total');

        $adminFee = $visits->sum('admin_fee');
        $cashVisitGross = $cashSales + $adminFee;

        $settlement = SalesSettlement::firstOrCreate(
            [
                'user_id' => $userId,
                'settlement_date' => $date
            ],
            [
                'created_by' => auth()->id(),
                'actual_amount' => 0,
                'status' => 'draft'
            ]
        );

        $costDetails = SalesSettlementCostDetail::where('sales_settlement_id', $settlement->id)->get();
        $totalCost   = $costDetails->sum('nominal');

        $expected = $cashSales
            + $cashSaleDirect
            + $receivablePayments
            - $totalCost;

        $difference = $settlement->actual_amount - $expected;

        $storeDetails = DB::table('sales_transactions as st')
            ->join('stores as s', 'st.store_id', '=', 's.id')
            ->join('visits as v', 'st.visit_id', '=', 'v.id')
            ->whereIn('st.visit_id', $visitIds)
            ->select(
                'st.visit_id',
                's.id as store_id',
                's.name as store_name',
                DB::raw("SUM(st.total_amount) as total_penjualan"),
                DB::raw("SUM(v.admin_fee) as admin_fee"),
                DB::raw("SUM(st.total_fee) as total_fee"),
                DB::raw("SUM(st.cash_paid) as total_cash"),
                DB::raw("SUM((st.total_amount - v.admin_fee) - st.cash_paid) as total_consignment")
            )
            ->groupBy('st.visit_id','s.id','s.name')
            ->get();

        if ($cashSaleDirect > 0) {

            $directFee = CashSale::where('user_id',$userId)
                ->whereDate('sale_date',$date)
                ->where('status','locked')
                ->sum('fee_total');

            $storeDetails->push((object)[
                'visit_id' => null,
                'store_id' => null,
                'store_name' => 'Penjualan Tunai (Direct)',
                'total_penjualan' => $cashSaleDirect,
                'total_fee' => $directFee,
                'total_cash' => $cashSaleDirect,
                'total_consignment' => 0
            ]);
        }

        $productDetails = DB::table('sales_transaction_items as sti')
            ->join('sales_transactions as st', 'sti.sales_transaction_id', '=', 'st.id')
            ->join('products as p', 'sti.product_id', '=', 'p.id')
            ->whereIn('st.visit_id', $visitIds)
            ->select(
                'p.name as product_name',
                DB::raw('SUM(sti.quantity_sold) as total_qty'),
                DB::raw('SUM(sti.subtotal_amount) as total_revenue'),
                DB::raw('SUM(sti.subtotal_fee) as total_fee')
            )
            ->groupBy('p.name')
            ->get();

        $cashSaleProductDetails = DB::table('cash_sale_items as csi')
            ->join('cash_sales as cs', 'csi.cash_sale_id', '=', 'cs.id')
            ->join('products as p', 'csi.product_id', '=', 'p.id')
            ->where('cs.user_id', $userId)
            ->whereDate('cs.sale_date', $date)
            ->where('cs.status','locked')
            ->select(
                'p.name as product_name',
                DB::raw('SUM(csi.qty) as total_qty'),
                DB::raw('SUM(csi.subtotal) as total_revenue'),
                DB::raw('SUM(csi.fee_nominal * csi.qty) as total_fee')
            )
            ->groupBy('p.name')
            ->get();

        return view('sales_settlements.show', compact(
            'settlement',
            'cashSales',
            'cashVisitGross',
            'cashSaleDirect',
            'consignmentSales',
            'receivablePayments',
            'adminFee',
            'totalCost',
            'expected',
            'difference',
            'storeDetails',
            'productDetails',
            'cashSaleProductDetails',
            'costDetails'
        ));
    }


    public function setor(Request $request)
    {
        $request->validate([
            'user_id'         => 'required',
            'settlement_date' => 'required|date',
            'actual_amount'   => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request) {

            $settlement = SalesSettlement::firstOrCreate(
                [
                    'user_id' => $request->user_id,
                    'settlement_date' => $request->settlement_date
                ],
                [
                    'created_by' => auth()->id(),
                    'actual_amount' => 0,
                    'status' => 'draft'
                ]
            );

            if ($settlement->status === 'closed' && auth()->user()->role !== 'admin') {
                throw new \Exception('Settlement sudah ditutup.');
            }

            $settlement->update([
                'actual_amount' => $request->actual_amount,
                'status'        => 'closed'
            ]);

            $date = Carbon::parse($request->settlement_date)->format('Y-m-d');

            $visits = Visit::where('user_id', $request->user_id)
                ->whereDate('visit_date', $date)
                ->where('status', 'approved')
                ->get();

            $visitIds = $visits->pluck('id');

            $cashSales = SalesTransaction::whereIn('visit_id', $visitIds)
                ->sum('cash_paid');

            $receivablePayments = ReceivablePayment::where('user_id', $request->user_id)
                ->whereDate('payment_date', $date)
                ->sum('amount');

            $cashSaleDirect = CashSale::where('user_id',$request->user_id)
                ->whereDate('sale_date',$date)
                ->where('status','locked')
                ->sum('total');

            $adminFee = $visits->sum('admin_fee');
            $cashVisitGross = $cashSales + $adminFee;

            $totalCost = SalesSettlementCostDetail::where('sales_settlement_id', $settlement->id)
                ->sum('nominal');

            $expected = $cashSales
                + $cashSaleDirect
                + $receivablePayments
                - $totalCost;

            $difference = $request->actual_amount - $expected;

            Kasbon::where('reference_id', $settlement->id)
                ->where('reference_type', 'sales_settlement')
                ->delete();

            if ($difference < 0) {
                Kasbon::create([
                    'user_id'        => $request->user_id,
                    'created_by'     => auth()->id(),
                    'amount_total'   => abs($difference),
                    'type'           => 'shortage',
                    'reference_id'   => $settlement->id,
                    'reference_type' => 'sales_settlement',
                    'description'    => 'Kurang setor tanggal '.$date,
                ]);
            }
        });

        return back()->with('success','Setoran berhasil disimpan.');
    }


        public function storeCost(Request $request, SalesSettlement $settlement)
    {
        $request->validate([
            'jenis_biaya' => 'required|in:bensin,parkir,makan,tol,lain_lain',
            'nominal'     => 'required|numeric|min:0.01',
            'keterangan'  => 'nullable|string'
        ]);

        // RULE BISNIS
        if ($settlement->status === 'closed' && auth()->user()->role !== 'admin') {
            abort(403, 'Settlement sudah ditutup. Hanya admin yang bisa menambahkan biaya.');
        }

        DB::transaction(function () use ($request, $settlement) {

            SalesSettlementCostDetail::create([
                'sales_settlement_id' => $settlement->id,
                'jenis_biaya'         => $request->jenis_biaya,
                'nominal'             => $request->nominal,
                'keterangan'          => $request->keterangan,
            ]);

        });

        return back()->with('success', 'Biaya operasional berhasil ditambahkan.');
    }


    public function reopen(SalesSettlement $settlement)
    {
        DB::transaction(function () use ($settlement) {

            // Hapus kasbon terkait settlement ini
            Kasbon::where('reference_id', $settlement->id)
                ->where('reference_type', 'sales_settlement')
                ->delete();

            // Ubah status kembali ke draft
            $settlement->update([
                'status' => 'draft'
            ]);
        });

        return back()->with('success', 'Settlement berhasil dibuka kembali.');
    }
}