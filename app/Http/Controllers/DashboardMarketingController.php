<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\AI\GeminiService;
use App\Models\Product;
use Carbon\Carbon;



class DashboardMarketingController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->type ?? 'monthly';

        $start = $request->start_date;
        $end   = $request->end_date;

        $prevStart = null;
        $prevEnd = null;

if (!empty($start) && !empty($end)) {

    $startDate = Carbon::parse($start);
    $endDate   = Carbon::parse($end);

    $diffDays = $startDate->diffInDays($endDate);

    $prevStart = $startDate->copy()->subDays($diffDays + 1)->format('Y-m-d');
    $prevEnd   = $startDate->copy()->subDay()->format('Y-m-d');
}

        

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

// ================= PREVIOUS DATA =================
$prevOffline = 0;
$prevOnline = 0;
$prevAds = 0;

if ($prevStart && $prevEnd) {

    $prevOffline = DB::selectOne("
        SELECT COALESCE(SUM(omzet),0) as total FROM (
            SELECT subtotal_amount as omzet
            FROM sales_transaction_items sti
            JOIN sales_transactions st ON st.id = sti.sales_transaction_id
            WHERE st.transaction_date BETWEEN '$prevStart' AND '$prevEnd'

            UNION ALL

            SELECT csi.subtotal as omzet
            FROM cash_sale_items csi
            JOIN cash_sales cs ON cs.id = csi.cash_sale_id
            WHERE cs.sale_date BETWEEN '$prevStart' AND '$prevEnd'
        ) x
    ")->total ?? 0;

    $prevOnline = DB::selectOne("
        SELECT COALESCE(SUM(total_price),0) as total
        FROM online_orders
        WHERE status = 'done'
        AND order_date BETWEEN '$prevStart' AND '$prevEnd'
    ")->total ?? 0;

    $prevAds = DB::selectOne("
        SELECT COALESCE(SUM(budget),0) as total
        FROM ads_reports
        WHERE report_date BETWEEN '$prevStart' AND '$prevEnd'
    ")->total ?? 0;
}

// ================= RATIO =================
$totalAll = $totalOffline + $totalOnline;

$prevTotalAll = $prevOffline + $prevOnline;

// growth omzet
$growthOmzet = $prevTotalAll > 0 
    ? (($totalAll - $prevTotalAll) / $prevTotalAll) * 100 
    : 0;

// growth ads
$growthAds = $prevAds > 0 
    ? (($totalAds - $prevAds) / $prevAds) * 100 
    : 0;

// acos
$acosNow = $totalAll > 0 ? ($totalAds / $totalAll) * 100 : 0;
$acosPrev = $prevTotalAll > 0 ? ($prevAds / $prevTotalAll) * 100 : 0;

// growth acos (selisih, bukan persen)
$growthAcos = $acosNow - $acosPrev;

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

// ================= SUMMARY PRODUK OFFLINE =================
$offlineSummary = DB::select("
SELECT 
    p.name,
    SUM(qty) as total_qty,
    SUM(omzet) as total_omzet
FROM (

    SELECT 
        sti.product_id,
        sti.quantity_sold as qty,
        sti.subtotal_amount as omzet,
        st.transaction_date as tanggal
    FROM sales_transaction_items sti
    JOIN sales_transactions st 
        ON st.id = sti.sales_transaction_id

    UNION ALL

    SELECT 
        csi.product_id,
        csi.qty as qty,
        csi.subtotal as omzet,
        cs.sale_date as tanggal
    FROM cash_sale_items csi
    JOIN cash_sales cs 
        ON cs.id = csi.cash_sale_id

) x
JOIN products p ON p.id = x.product_id

" . (!empty($start) && !empty($end) ? "
WHERE x.tanggal BETWEEN '$start' AND '$end'
" : "") . "

GROUP BY p.name
ORDER BY total_qty DESC
");


// ================= SUMMARY PRODUK ONLINE =================
$onlineSummary = DB::select("
SELECT 
    p.name,
    SUM(oi.qty) as total_qty,

    ROUND(SUM(
        (oi.qty / t.total_qty_per_order) * o.total_price
    ),0) as total_omzet

FROM online_order_items oi

JOIN online_orders o 
    ON o.id = oi.online_order_id

JOIN (
    SELECT 
        online_order_id,
        SUM(qty) as total_qty_per_order
    FROM online_order_items
    GROUP BY online_order_id
) t 
    ON t.online_order_id = oi.online_order_id

JOIN products p 
    ON p.id = oi.product_id

WHERE o.status = 'done'
" . (!empty($start) && !empty($end) ? "
AND o.order_date BETWEEN '$start' AND '$end'
" : "") . "

GROUP BY p.name
ORDER BY total_qty DESC
");

// ================= HITUNG HPP =================
$totalHpp = 0;

// OFFLINE
foreach ($offlineSummary as $row) {

    $product = Product::where('name', $row->name)->first();

    if (!$product) continue;

    $hpp = $product->getCostAt($end);

    $totalHpp += $hpp * $row->total_qty;
}

// ONLINE
foreach ($onlineSummary as $row) {

    $product = Product::where('name', $row->name)->first();

    if (!$product) continue;

    $hpp = $product->getCostAt($end);

    $totalHpp += $hpp * $row->total_qty;
}

// ================= HITUNG PROFIT =================
$profit = $totalAll - $totalAds - $totalHpp;

$margin = $totalAll > 0 
    ? ($profit / $totalAll) * 100 
    : 0;


    $acosGlobal = $totalAll > 0 ? ($totalAds / $totalAll) * 100 : 0;

       return view('marketing.index', compact(
    'data',
    'type',
    'start',
    'end',
    'offlineProduk',
    'onlinePaket',
    'acosGlobal',
    'offlineSummary',
    'onlineSummary',
    'growthOmzet',
    'growthAds',
    'growthAcos',
    'totalHpp',
    'profit',
    'margin',
));

    }

    

public function aiAnalysis(Request $request)
{
   $start = !empty($request->start_date) 
    ? $request->start_date 
    : now()->startOfMonth()->format('Y-m-d');

   $end = !empty($request->end_date) 
    ? $request->end_date 
    : now()->format('Y-m-d');

    $startLabel = \Carbon\Carbon::parse($start)->translatedFormat('d M Y');
    $endLabel   = \Carbon\Carbon::parse($end)->translatedFormat('d M Y');

    // ===== ambil data sederhana (reuse logika) =====
    $totalOmzet = DB::table('dashboard_marketing_base')
        ->when($start && $end, fn($q) => $q->whereBetween('tanggal', [$start, $end]))
        ->sum('total_omzet');

    $totalAds = DB::table('dashboard_marketing_base')
        ->when($start && $end, fn($q) => $q->whereBetween('tanggal', [$start, $end]))
        ->sum('total_ads');

        // ================= SUMMARY PRODUK OFFLINE =================
$offlineSummary = DB::select("
SELECT 
    p.id,
    p.name,
    SUM(qty) as total_qty
FROM (
    SELECT sti.product_id, sti.quantity_sold as qty
FROM sales_transaction_items sti
JOIN sales_transactions st ON st.id = sti.sales_transaction_id
WHERE st.transaction_date BETWEEN '$start' AND '$end'

    UNION ALL

    SELECT csi.product_id, csi.qty as qty
FROM cash_sale_items csi
JOIN cash_sales cs ON cs.id = csi.cash_sale_id
WHERE cs.sale_date BETWEEN '$start' AND '$end'
) x
JOIN products p ON p.id = x.product_id
GROUP BY p.id, p.name
");

// ================= SUMMARY PRODUK ONLINE =================
$onlineSummary = DB::select("
SELECT 
    p.id,
    p.name,
    SUM(oi.qty) as total_qty
FROM online_order_items oi
JOIN online_orders o ON o.id = oi.online_order_id
JOIN products p ON p.id = oi.product_id
WHERE o.status = 'done'
AND o.order_date BETWEEN '$start' AND '$end'
GROUP BY p.id, p.name
");

// ================= HITUNG HPP =================
$totalHpp = 0;

// OFFLINE
foreach ($offlineSummary as $row) {

    $product = Product::find($row->id);

    if (!$product) continue;

    $hpp = $product->getCostAt($end);

    $totalHpp += $hpp * $row->total_qty;
}

// ONLINE
foreach ($onlineSummary as $row) {

    $product = Product::find($row->id);

    if (!$product) continue;

    $hpp = $product->getCostAt($end ?? now());

    $totalHpp += $hpp * $row->total_qty;
}

// ================= HITUNG PROFIT =================
$profit = $totalOmzet - $totalAds - $totalHpp;

$margin = $totalOmzet > 0 
    ? ($profit / $totalOmzet) * 100 
    : 0;

    $acos = $totalOmzet > 0 ? ($totalAds / $totalOmzet) * 100 : 0;

 $roas = $totalAds > 0 ? $totalOmzet / $totalAds : 0;
$acos = $totalOmzet > 0 ? ($totalAds / $totalOmzet) * 100 : 0;


// ================= OMZET CHANNEL =================
$totalOffline = DB::selectOne("
    SELECT COALESCE(SUM(omzet),0) as total FROM (
        SELECT subtotal_amount as omzet
        FROM sales_transaction_items sti
        JOIN sales_transactions st ON st.id = sti.sales_transaction_id
        WHERE st.transaction_date BETWEEN '$start' AND '$end'

        UNION ALL

        SELECT csi.subtotal as omzet
        FROM cash_sale_items csi
        JOIN cash_sales cs ON cs.id = csi.cash_sale_id
        WHERE cs.sale_date BETWEEN '$start' AND '$end'
    ) x
")->total ?? 0;

$totalOnline = DB::selectOne("
    SELECT COALESCE(SUM(total_price),0) as total
    FROM online_orders
    WHERE status = 'done'
    AND order_date BETWEEN '$start' AND '$end'
")->total ?? 0;

// ================= PROPORSI =================
$offlineRatio = $totalOmzet > 0 ? ($totalOffline / $totalOmzet) * 100 : 0;
$onlineRatio  = $totalOmzet > 0 ? ($totalOnline / $totalOmzet) * 100 : 0;

$prompt = "
Data Marketing (REAL DATA):

Periode Analisa:
$startLabel sampai $endLabel

Omzet Total: Rp ".number_format($totalOmzet)."
- Offline: Rp ".number_format($totalOffline)." (".number_format($offlineRatio,1)." %)
- Online: Rp ".number_format($totalOnline)." (".number_format($onlineRatio,1)." %)

CATATAN PENTING:
Omzet total = gabungan omzet offline + omzet online.
Semua analisa HARUS berdasarkan periode di atas.

Ads: Rp ".number_format($totalAds)."
HPP: Rp ".number_format($totalHpp)."
Profit: Rp ".number_format($profit)."
Margin: ".number_format($margin,2)."%
ROAS: ".number_format($roas,2)."
ACOS: ".number_format($acos,2)."%

TUGAS ANALISA:

1. Jelaskan kondisi bisnis dengan menyebut:
   - total omzet
   - proporsi offline vs online
   - efisiensi iklan

2. Analisa proporsi channel:
   - apakah terlalu bergantung offline / online
   - apakah komposisi sehat atau tidak

3. Tentukan TARGET ANGKA untuk periode berikutnya:
   - Target ROAS
   - Target ACOS
   - Target komposisi channel (%) ideal
   - Target omzet realistis
   - Sebutkan periode target (misal: 30 hari ke depan dari $endLabel)

4. Berikan 3 langkah konkret berbasis kondisi channel:
   (WAJIB spesifik, bukan teori)

FORMAT WAJIB:

**Kondisi:**
...

**Analisa Channel:**
...

**Masalah:**
- ...
- ...

**Target (Periode Berikutnya):**
- Periode: ...
- ROAS: ...
- ACOS: ...
- Komposisi: Offline ...% | Online ...%
- Omzet: ...

**Action Plan:**
1. ...
2. ...
3. ...

Fokus eksekusi, jangan teori umum.
";

    $result = GeminiService::ask($prompt);

    return response()->json([
        'result' => $result
    ]);
}

}