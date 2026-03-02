<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class KpiSalesController extends Controller
{
    public function index()
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth   = Carbon::now()->endOfMonth();

        /*
        |--------------------------------------------------------------------------
        | SUMMARY
        |--------------------------------------------------------------------------
        */

        // Total Visit
        $totalVisits = DB::table('visits')
            ->whereIn('status', ['completed','approved'])
            ->whereBetween('visit_date', [$startOfMonth, $endOfMonth])
            ->count();

        // Total Omzet
        $totalOmzet = DB::table('sales_transactions')
            ->join('visits', 'sales_transactions.visit_id', '=', 'visits.id')
            ->whereBetween('visits.visit_date', [$startOfMonth, $endOfMonth])
            ->sum('sales_transactions.total_amount');

        $avgOmzetPerVisit = $totalVisits > 0
            ? $totalOmzet / $totalVisits
            : 0;

        // Settlement Delay (max hari dari draft settlement)
        $maxDelay = DB::table('sales_settlements')
            ->where('status', 'draft')
            ->selectRaw('MAX(DATEDIFF(NOW(), settlement_date)) as delay')
            ->value('delay') ?? 0;


        /*
        |--------------------------------------------------------------------------
        | GRAFIK OMZET HARIAN
        |--------------------------------------------------------------------------
        */

        $dailyOmzet = DB::table('sales_transactions')
            ->join('visits', 'sales_transactions.visit_id', '=', 'visits.id')
            ->whereBetween('visits.visit_date', [$startOfMonth, $endOfMonth])
            ->selectRaw('DATE(visits.visit_date) as date, SUM(sales_transactions.total_amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | GRAFIK VISIT HARIAN
        |--------------------------------------------------------------------------
        */

        $dailyVisits = DB::table('visits')
            ->whereIn('status', ['completed','approved'])
            ->whereBetween('visit_date', [$startOfMonth, $endOfMonth])
            ->selectRaw('DATE(visit_date) as date, COUNT(*) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | RANKING TOKO
        |--------------------------------------------------------------------------
        */

        $rankingStores = DB::table('sales_transactions')
            ->join('visits', 'sales_transactions.visit_id', '=', 'visits.id')
            ->join('stores', 'visits.store_id', '=', 'stores.id')
            ->whereBetween('visits.visit_date', [$startOfMonth, $endOfMonth])
            ->selectRaw('stores.name as store_name, SUM(sales_transactions.total_amount) as total')
            ->groupBy('stores.id','stores.name')
            ->orderByDesc('total')
            ->limit(10)
            ->get();


        /*
        |--------------------------------------------------------------------------
        | RANKING PRODUK
        |--------------------------------------------------------------------------
        */

        $rankingProducts = DB::table('visit_items')
            ->join('visits', 'visit_items.visit_id', '=', 'visits.id')
            ->join('products', 'visit_items.product_id', '=', 'products.id')
            ->whereBetween('visits.visit_date', [$startOfMonth, $endOfMonth])
            ->selectRaw('products.name as product_name, SUM(visit_items.sold_qty * visit_items.price_snapshot) as total')
            ->groupBy('products.id','products.name')
            ->orderByDesc('total')
            ->limit(10)
            ->get();


        return view('reports.kpi-sales', [
            'totalVisits'      => $totalVisits,
            'totalOmzet'       => $totalOmzet,
            'avgOmzetPerVisit' => $avgOmzetPerVisit,
            'maxDelay'         => $maxDelay,
            'dailyOmzet'       => $dailyOmzet,
            'dailyVisits'      => $dailyVisits,
            'rankingStores'    => $rankingStores,
            'rankingProducts'  => $rankingProducts,
        ]);
    }
}