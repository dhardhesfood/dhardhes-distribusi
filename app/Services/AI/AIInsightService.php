<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\DB;
use App\Services\AI\GeminiService;

class AIInsightService
{

    public static function getBusinessSummary()
    {

        $startMonth = now()->startOfMonth();
        $startWeek = now()->startOfWeek();

        // omzet transaksi
        $omzetTransactions = DB::table('sales_transactions')
            ->where('transaction_date','>=',$startMonth)
            ->sum('total_amount');

        // omzet cash
        $omzetCash = DB::table('cash_sales')
            ->where('status','locked')
            ->where('sale_date','>=',$startMonth)
            ->sum('total');

        $monthlyOmzet = $omzetTransactions + $omzetCash;

        // toko aktif
        $stores = DB::table('stores')
            ->where('is_active',1)
            ->count();

        // visit minggu ini
        $visitsWeek = DB::table('visits')
        ->whereBetween('visit_date', [
        now()->subDays(7)->toDateString(),
        now()->toDateString()
        ])
        ->count();

        // produk paling laku
        $topProduct = DB::table('sales_transaction_items')
            ->join('products','products.id','=','sales_transaction_items.product_id')
            ->select('products.name', DB::raw('SUM(quantity_sold) as qty'))
            ->groupBy('products.name')
            ->orderByDesc('qty')
            ->limit(1)
            ->first();

        // produk paling lambat
         $slowProduct = DB::table('sales_transaction_items')
            ->join('products','products.id','=','sales_transaction_items.product_id')
            ->select('products.name', DB::raw('SUM(quantity_sold) as qty'))
            ->groupBy('products.name')
            ->orderBy('qty')
            ->limit(1)
            ->first();
            
            // toko terlambat
$lateStores = DB::table('stores')
    ->whereRaw("DATEDIFF(CURDATE(), last_visit_date) > visit_interval_days")
    ->count();

// toko terlambat berat
$heavyLateStores = DB::table('stores')
    ->whereRaw("DATEDIFF(CURDATE(), last_visit_date) > visit_interval_days * 2")
    ->count();

// toko pertimbangkan ditarik
$withdrawStores = DB::table('stores')
    ->whereRaw("DATEDIFF(CURDATE(), last_visit_date) > visit_interval_days * 3")
    ->count();

        // sales terbaik
        $topSales = DB::table('sales_transactions')
            ->join('users','users.id','=','sales_transactions.user_id')
            ->select('users.name', DB::raw('SUM(total_amount) as omzet'))
            ->groupBy('users.name')
            ->orderByDesc('omzet')
            ->limit(1)
            ->first();

        return [

    'monthly_omzet'=>$monthlyOmzet,
    'active_stores'=>$stores,
    'visits_week'=>$visitsWeek,
    'top_product'=>$topProduct->name ?? null,
    'slow_product'=>$slowProduct->name ?? null,
    'top_sales'=>$topSales->name ?? null,

    'late_stores'=>$lateStores,
    'heavy_late_stores'=>$heavyLateStores,
    'withdraw_stores'=>$withdrawStores

];

    }

    public static function generateInsight()
{

    $data = self::getBusinessSummary();

    $prompt = "

Kamu adalah konsultan bisnis distribusi makanan ringan.

PENTING:
Status toko berikut adalah STATUS KUNJUNGAN SALES, bukan status pembayaran.

Penjelasan status:
- Terlambat = toko belum dikunjungi melebihi jadwal kunjungan.
- Terlambat berat = toko sangat lama tidak dikunjungi.
- Pertimbangkan ditarik = toko sangat lama tidak dikunjungi dan berpotensi berhenti order.

Berikut data bisnis:

Omzet bulan ini: {$data['monthly_omzet']} rupiah
Jumlah toko aktif: {$data['active_stores']} toko
Jumlah kunjungan sales minggu ini: {$data['visits_week']} kunjungan

Produk paling laku: {$data['top_product']}
Produk paling lambat: {$data['slow_product']}

Sales dengan penjualan tertinggi: {$data['top_sales']}

Status kunjungan toko:
- Terlambat: {$data['late_stores']} toko
- Terlambat berat: {$data['heavy_late_stores']} toko
- Pertimbangkan ditarik: {$data['withdraw_stores']} toko

Tugas kamu:

1. Analisa kondisi distribusi berdasarkan data ini.
2. Berikan saran peningkatan penjualan.
3. Berikan saran peningkatan kunjungan sales.
4. Gunakan bahasa singkat, jelas, dan rapi.
5. Maksimal 6 poin saja.

Format jawaban:

ANALISA BISNIS
- poin
- poin

SARAN PERBAIKAN
- poin
- poin
";

    return GeminiService::ask($prompt);

}

}