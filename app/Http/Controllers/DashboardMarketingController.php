<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardMarketingController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->type ?? 'monthly';

        if ($type == 'weekly') {

            $data = DB::table('dashboard_marketing_base')
                ->selectRaw("
                    YEAR(tanggal) as tahun,
                    WEEK(tanggal,1) as minggu,

                    SUM(total_omzet) as omzet,
                    SUM(total_ads) as ads,

                    ROUND(SUM(total_omzet)/NULLIF(SUM(total_ads),0),2) as roas,
                    ROUND(SUM(total_ads)/NULLIF(SUM(total_omzet),0)*100,2) as acos
                ")
                ->groupBy('tahun','minggu')
                ->orderByDesc('tahun')
                ->orderByDesc('minggu')
                ->get();

        } else {

            $data = DB::table('dashboard_marketing_base')
                ->selectRaw("
                    DATE_FORMAT(tanggal,'%Y-%m') as periode,

                    SUM(total_omzet) as omzet,
                    SUM(total_ads) as ads,

                    ROUND(SUM(total_omzet)/NULLIF(SUM(total_ads),0),2) as roas,
                    ROUND(SUM(total_ads)/NULLIF(SUM(total_omzet),0)*100,2) as acos
                ")
                ->groupBy('periode')
                ->orderByDesc('periode')
                ->get();
        }

        return view('marketing.index', compact('data','type'));
    }
}