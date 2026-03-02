<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SalesFeeController extends Controller
{
    public function index()
    {
        $query = DB::table('users as u')
            ->leftJoin('sales_transactions as st', 'st.user_id', '=', 'u.id')
            ->where('u.role', 'sales');

        // Jika yang login adalah sales, hanya tampilkan dirinya sendiri
        if (auth()->user()->role === 'sales') {
            $query->where('u.id', auth()->id());
        }

        $salesData = $query
            ->select(
                'u.id',
                'u.name',

                DB::raw('COALESCE(SUM(st.total_fee),0) as total_konsinyasi'),

                DB::raw('(
                    SELECT COALESCE(SUM(cs.fee_total),0)
                    FROM cash_sales cs
                    WHERE cs.user_id = u.id
                      AND cs.status = "locked"
                ) as total_tunai'),

                DB::raw('(
                    SELECT COALESCE(SUM(sfp.amount_paid),0)
                    FROM sales_fee_payments sfp
                    WHERE sfp.user_id = u.id
                ) as total_fee_paid'),

                DB::raw('(
                    SELECT COALESCE(SUM(k.amount_total - k.amount_paid),0)
                    FROM kasbons k
                    WHERE k.user_id = u.id
                      AND k.status = "open"
                ) as kasbon_remaining')
            )
            ->groupBy('u.id', 'u.name')
            ->get();

        $finalData = [];

        foreach ($salesData as $row) {

            $totalGenerated = (float)$row->total_konsinyasi + (float)$row->total_tunai;
            $totalPaid      = (float)$row->total_fee_paid;
            $kasbon         = (float)$row->kasbon_remaining;

            $netFee = $totalGenerated - $totalPaid - $kasbon;

            $finalData[] = [
                'user_id' => $row->id,
                'name' => $row->name,
                'total_generated' => $totalGenerated,
                'total_paid' => $totalPaid,
                'kasbon_remaining' => $kasbon,
                'net_fee' => $netFee,
                'is_minus' => $netFee < 0 ? true : false,
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | RINCIAN SETTLEMENT BULAN INI (STATUS CLOSED)
        |--------------------------------------------------------------------------
        | - Otomatis reset tiap bulan
        | - Sales hanya melihat miliknya
        | - Admin melihat semua
        */

        $settlementQuery = DB::table('sales_settlements')
            ->where('status', 'closed')
            ->whereMonth('settlement_date', Carbon::now()->month)
            ->whereYear('settlement_date', Carbon::now()->year)
            ->orderByDesc('settlement_date');

        if (auth()->user()->role === 'sales') {
            $settlementQuery->where('user_id', auth()->id());
        }

        $monthlySettlements = $settlementQuery->get()
            ->groupBy('user_id');

        return view('sales-fees.index', [
            'sales' => $finalData,
            'monthlySettlements' => $monthlySettlements
        ]);
    }

    public function pay(Request $request)
    {
        // Proteksi backend: hanya admin boleh bayar
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'amount'  => 'required|numeric|min:1',
        ]);

        DB::table('sales_fee_payments')->insert([
            'user_id'    => $request->user_id,
            'amount_paid'=> $request->amount,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()
            ->route('sales-fees.index')
            ->with('success', 'Pembayaran fee berhasil disimpan.');
    }
}