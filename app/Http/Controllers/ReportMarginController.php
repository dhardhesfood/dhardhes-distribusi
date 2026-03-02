<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportMarginController extends Controller
{
    private function resolveDateRange(Request $request)
    {
        $from = $request->input('from')
            ? Carbon::parse($request->input('from'))->startOfDay()
            : Carbon::now()->startOfMonth();

        $to = $request->input('to')
            ? Carbon::parse($request->input('to'))->endOfDay()
            : Carbon::now()->endOfMonth();

        return [$from, $to];
    }

    private function buildSummary($reports)
    {
        $summary = [
            'total_penjualan' => $reports->sum('total_penjualan'),
            'total_fee'       => $reports->sum('total_fee'),
            'total_hpp'       => $reports->sum('total_hpp'),
            'total_margin'    => $reports->sum('margin'),
        ];

        $summary['margin_percent'] = $summary['total_penjualan'] > 0
            ? ($summary['total_margin'] / $summary['total_penjualan']) * 100
            : 0;

        return $summary;
    }

    /*
    |--------------------------------------------------------------------------
    | KPI Dashboard (Bulan Ini vs Bulan Lalu)
    |--------------------------------------------------------------------------
    */

    public function kpi()
    {
        $now = Carbon::now();

        $thisMonthStart = $now->copy()->startOfMonth();
        $thisMonthEnd   = $now->copy()->endOfMonth();

        $lastMonthStart = $now->copy()->subMonth()->startOfMonth();
        $lastMonthEnd   = $now->copy()->subMonth()->endOfMonth();

        $thisMonth = $this->aggregatePeriod($thisMonthStart, $thisMonthEnd);
        $lastMonth = $this->aggregatePeriod($lastMonthStart, $lastMonthEnd);

        $growth = [
            'penjualan' => $this->growthPercent($lastMonth['total_penjualan'], $thisMonth['total_penjualan']),
            'margin'    => $this->growthPercent($lastMonth['total_margin'], $thisMonth['total_margin']),
            'qty'       => $this->growthPercent($lastMonth['total_qty'], $thisMonth['total_qty']),
        ];

        return view('reports.margin.kpi', compact(
            'thisMonth',
            'lastMonth',
            'growth'
        ));
    }

    private function aggregatePeriod($from, $to)
    {
        $data = DB::table('sales_transactions as st')
            ->join('sales_transaction_items as sti', 'sti.sales_transaction_id', '=', 'st.id')
            ->whereBetween('st.transaction_date', [$from->toDateString(), $to->toDateString()])
            ->select(
                DB::raw('SUM(sti.quantity_sold) as total_qty'),
                DB::raw('SUM(sti.subtotal_amount) as total_penjualan'),
                DB::raw('SUM(sti.subtotal_fee) as total_fee'),
                DB::raw('SUM(sti.quantity_sold * sti.cost_snapshot) as total_hpp'),
                DB::raw('
                    SUM(sti.subtotal_amount)
                    - SUM(sti.subtotal_fee)
                    - SUM(sti.quantity_sold * sti.cost_snapshot)
                    as total_margin
                ')
            )
            ->first();

        return [
            'total_qty'       => $data->total_qty ?? 0,
            'total_penjualan' => $data->total_penjualan ?? 0,
            'total_fee'       => $data->total_fee ?? 0,
            'total_hpp'       => $data->total_hpp ?? 0,
            'total_margin'    => $data->total_margin ?? 0,
            'margin_percent'  => ($data->total_penjualan ?? 0) > 0
                ? (($data->total_margin ?? 0) / $data->total_penjualan) * 100
                : 0,
        ];
    }

    private function growthPercent($old, $new)
    {
        if ($old == 0) {
            return $new > 0 ? 100 : 0;
        }

        return (($new - $old) / $old) * 100;
    }

    /*
    |--------------------------------------------------------------------------
    | Margin Per Transaksi
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        [$from, $to] = $this->resolveDateRange($request);

        $reports = DB::table('sales_transactions as st')
            ->join('sales_transaction_items as sti', 'sti.sales_transaction_id', '=', 'st.id')
            ->join('stores as s', 's.id', '=', 'st.store_id')
            ->whereBetween('st.transaction_date', [$from->toDateString(), $to->toDateString()])
            ->select(
                'st.id as transaction_id',
                'st.transaction_date',
                's.name as store_name',
                DB::raw('SUM(sti.subtotal_amount) as total_penjualan'),
                DB::raw('SUM(sti.subtotal_fee) as total_fee'),
                DB::raw('SUM(sti.quantity_sold * sti.cost_snapshot) as total_hpp'),
                DB::raw('
                    SUM(sti.subtotal_amount)
                    - SUM(sti.subtotal_fee)
                    - SUM(sti.quantity_sold * sti.cost_snapshot)
                    as margin
                ')
            )
            ->groupBy('st.id', 'st.transaction_date', 's.name')
            ->orderByDesc('st.id')
            ->get();

        $summary = $this->buildSummary($reports);

        return view('reports.margin.index', compact(
            'reports',
            'summary',
            'from',
            'to'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | Margin Per Produk
    |--------------------------------------------------------------------------
    */

    public function products(Request $request)
    {
        [$from, $to] = $this->resolveDateRange($request);

        $reports = DB::table('sales_transactions as st')
            ->join('sales_transaction_items as sti', 'sti.sales_transaction_id', '=', 'st.id')
            ->join('products as p', 'p.id', '=', 'sti.product_id')
            ->whereBetween('st.transaction_date', [$from->toDateString(), $to->toDateString()])
            ->select(
                'p.id as product_id',
                'p.name as product_name',
                DB::raw('SUM(sti.quantity_sold) as total_qty'),
                DB::raw('SUM(sti.subtotal_amount) as total_penjualan'),
                DB::raw('SUM(sti.subtotal_fee) as total_fee'),
                DB::raw('SUM(sti.quantity_sold * sti.cost_snapshot) as total_hpp'),
                DB::raw('
                    SUM(sti.subtotal_amount)
                    - SUM(sti.subtotal_fee)
                    - SUM(sti.quantity_sold * sti.cost_snapshot)
                    as margin
                ')
            )
            ->groupBy('p.id', 'p.name')
            ->orderByDesc(DB::raw('margin'))
            ->get();

        $summary = $this->buildSummary($reports);

        return view('reports.margin.products', compact(
            'reports',
            'summary',
            'from',
            'to'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | Margin Per Toko
    |--------------------------------------------------------------------------
    */

    public function stores(Request $request)
    {
        [$from, $to] = $this->resolveDateRange($request);

        $reports = DB::table('sales_transactions as st')
            ->join('sales_transaction_items as sti', 'sti.sales_transaction_id', '=', 'st.id')
            ->join('stores as s', 's.id', '=', 'st.store_id')
            ->whereBetween('st.transaction_date', [$from->toDateString(), $to->toDateString()])
            ->select(
                's.id as store_id',
                's.name as store_name',
                DB::raw('SUM(sti.quantity_sold) as total_qty'),
                DB::raw('SUM(sti.subtotal_amount) as total_penjualan'),
                DB::raw('SUM(sti.subtotal_fee) as total_fee'),
                DB::raw('SUM(sti.quantity_sold * sti.cost_snapshot) as total_hpp'),
                DB::raw('
                    SUM(sti.subtotal_amount)
                    - SUM(sti.subtotal_fee)
                    - SUM(sti.quantity_sold * sti.cost_snapshot)
                    as margin
                ')
            )
            ->groupBy('s.id', 's.name')
            ->orderByDesc(DB::raw('margin'))
            ->get();

        $summary = $this->buildSummary($reports);

        return view('reports.margin.stores', compact(
            'reports',
            'summary',
            'from',
            'to'
        ));
    }
}
