<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardMarketingController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->type ?? 'monthly';

        $start = $request->start_date;
        $end   = $request->end_date;

        // ================= TOTAL CHANNEL =================
$totalOffline = DB::selectOne("
    SELECT COALESCE(SUM(omzet),0) as total FROM (
        SELECT subtotal_amount as omzet
        FROM sales_transaction_items sti
        JOIN sales_transactions st ON st.id = sti.sales_transaction_id
        " . (!empty($start) && !empty($end) ? "WHERE st.transaction_date BETWEEN '$start' AND '$end'" : "") . "

        UNION ALL

        SELECT csi.subtotal as omzet
        FROM cash_sale_items csi
        JOIN cash_sales cs ON cs.id = csi.cash_sale_id
        " . (!empty($start) && !empty($end) ? "WHERE cs.sale_date BETWEEN '$start' AND '$end'" : "") . "
    ) x
")->total ?? 0;

$totalOnline = DB::selectOne("
    SELECT COALESCE(SUM(total_price),0) as total
    FROM online_orders
    WHERE status = 'done'
    " . (!empty($start) && !empty($end) ? "AND order_date BETWEEN '$start' AND '$end'" : "") . "
")->total ?? 0;

$totalAds = DB::selectOne("
    SELECT COALESCE(SUM(budget),0) as total
    FROM ads_reports
    " . (!empty($start) && !empty($end) ? "WHERE report_date BETWEEN '$start' AND '$end'" : "") . "
")->total ?? 0;
$totalAds = (float) $totalAds;

// ================= RATIO =================
$totalAll = $totalOffline + $totalOnline;

$offlineRatio = $totalAll > 0 ? $totalOffline / $totalAll : 0;
$onlineRatio  = $totalAll > 0 ? $totalOnline / $totalAll : 0;

// ================= ADS PER CHANNEL =================
$adsOffline = $totalAds * $offlineRatio;
$adsOnline  = $totalAds * $onlineRatio;

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
                ->when(!empty($start) && !empty($end), function ($q) use ($start, $end) {
                   $q->whereBetween('tanggal', [$start, $end]);
                    })
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
                ->when(!empty($start) && !empty($end), function ($q) use ($start, $end) {
                    $q->whereBetween('tanggal', [$start, $end]);
                    })
                ->groupBy('periode')
                ->orderByDesc('periode')
                ->get();
        }

// ================= MASTER PRODUK =================
$offlineProduk = DB::table('products')
    ->select('id', 'name', 'default_selling_price as price')
    ->orderBy('name')
    ->get();

// ================= MASTER PAKET =================
$onlinePaket = DB::select("
SELECT 
    pt.name as package_name,
    oo.package_template_id,

    AVG(oo.total_price) as price

FROM online_orders oo
JOIN package_templates pt 
    ON pt.id = oo.package_template_id

WHERE oo.status = 'done'
" . (!empty($start) && !empty($end) ? "
AND oo.order_date BETWEEN '$start' AND '$end'
" : "") . "

GROUP BY oo.package_template_id, pt.name
ORDER BY pt.name ASC
");

    $acosGlobal = $totalAll > 0 ? ($totalAds / $totalAll) * 100 : 0;

       return view('marketing.index', compact(
    'data',
    'type',
    'start',
    'end',
    'offlineProduk',
    'onlinePaket',
    'acosGlobal'
));

    }
}