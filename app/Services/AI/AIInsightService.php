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
            ->whereNull('deleted_at')
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
            
        // ambil semua toko aktif
       $storesData = \App\Models\Store::where('is_active',1)
            ->whereNull('deleted_at')
            ->get();

       // hitung status berdasarkan engine Store model
       $lateStores = $storesData->where('visit_status','late')->count();

       $heavyLateStores = $storesData->where('visit_status','heavy')->count();

       $withdrawStores = $storesData->where('visit_status','withdraw')->count();

        // sales aktif (sistem saat ini hanya 1 sales)
       $topSales = DB::table('users')
                   ->where('role','sales')
                   ->select('name')
                   ->first();

// ==============================
// TOTAL PRODUK & NILAI STOK TOKO
// (LOGIKA SAMA DENGAN HALAMAN STORES)
// ==============================

$totalStoreProducts = 0;
$totalStoreValue = 0;

$storesList = \App\Models\Store::where('is_active',1)->get();

foreach ($storesList as $store) {

    $stockData = DB::table('store_stock_movements as ssm')
        ->join('products as p','p.id','=','ssm.product_id')
        ->select(
            'ssm.product_id',
            'p.name',
            DB::raw("SUM(ssm.quantity) as total_qty")
        )
        ->where('ssm.store_id',$store->id)
        ->groupBy('ssm.product_id','p.name')
        ->having('total_qty','>',0)
        ->get();

    foreach ($stockData as $row) {

        $storePrice = DB::table('store_prices')
            ->where('store_id',$store->id)
            ->where('product_id',$row->product_id)
            ->value('price');

        $qty = (int)$row->total_qty;
        $price = (float)($storePrice ?? 0);

        $totalStoreProducts += $qty;
        $totalStoreValue += $qty * $price;
    }
}

// ==============================
// AMBIL STOK SEMUA TOKO (SAMA SEPERTI HALAMAN STORES)
// ==============================

$storesStockData = [];

$storesList = \App\Models\Store::where('is_active',1)->get();

foreach ($storesList as $store) {

    $stockData = DB::table('store_stock_movements as ssm')
        ->join('products as p', 'p.id', '=', 'ssm.product_id')
        ->select(
            'ssm.product_id',
            'p.name as product_name',
            'p.default_fee_nominal',
            DB::raw("SUM(ssm.quantity) as total_qty")
        )
        ->where('ssm.store_id', $store->id)
        ->groupBy('ssm.product_id','p.name','p.default_fee_nominal')
        ->having('total_qty','>',0)
        ->get();

    $products = [];

    foreach ($stockData as $row) {

        $storePrice = DB::table('store_prices')
            ->where('store_id',$store->id)
            ->where('product_id',$row->product_id)
            ->value('price');

        $qty = (int)$row->total_qty;
        $price = (float)($storePrice ?? 0);

        $products[] = [
            'product' => $row->product_name,
            'qty' => $qty,
            'price' => $price,
            'subtotal' => $qty * $price
        ];
    }

    $storesStockData[] = [
        'store_name' => $store->name,
        'products' => $products
    ];
} 

// ==============================
// HISTORY KUNJUNGAN TOKO
// ==============================

$visitHistory = DB::table('visits')
    ->join('stores','stores.id','=','visits.store_id')
    ->select(
        'stores.name as store_name',
        'visits.visit_date',
        'visits.status'
    )
    ->orderByDesc('visits.visit_date')
    ->limit(500)
    ->get();


// ==============================
// HISTORY PENJUALAN TOKO
// ==============================

$salesHistory = DB::table('sales_transactions as st')
    ->join('stores as s','s.id','=','st.store_id')
    ->select(
        's.name as store_name',
        'st.transaction_date',
        'st.total_amount',
        'st.cash_paid',
        'st.total_fee'
    )
    ->orderByDesc('st.transaction_date')
    ->limit(500)
    ->get();


// ==============================
// DETAIL PRODUK TERJUAL
// ==============================

$productSales = DB::table('sales_transaction_items as sti')
    ->join('products as p','p.id','=','sti.product_id')
    ->join('sales_transactions as st','st.id','=','sti.sales_transaction_id')
    ->join('stores as s','s.id','=','st.store_id')
    ->select(
        's.name as store_name',
        'p.name as product_name',
        'sti.quantity_sold',
        'sti.price_snapshot',
        'sti.subtotal_amount',
        'st.transaction_date'
    )
    ->orderByDesc('st.transaction_date')
    ->limit(1000)
    ->get();

        return [

        'monthly_omzet'=>$monthlyOmzet,
        'active_stores'=>$stores,
        'visits_week'=>$visitsWeek,
        'top_product'=>$topProduct->name ?? null,
        'slow_product'=>$slowProduct->name ?? null,
        'top_sales'=>$topSales->name ?? null,

        'late_stores'=>$lateStores,
        'heavy_late_stores'=>$heavyLateStores,
        'store_products_total' => $totalStoreProducts,
        'store_stock_value' => $totalStoreValue,
        'stores_stock_detail' => $storesStockData,
        'visit_history' => $visitHistory,
        'sales_history' => $salesHistory,
        'product_sales' => $productSales,
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

Data stok konsinyasi per toko: ".json_encode($data['stores_stock_detail'])."

Total produk di semua toko: {$data['store_products_total']} pcs
Total nilai stok konsinyasi di toko: {$data['store_stock_value']} rupiah

Data kunjungan terbaru:
".json_encode($data['visit_history'])."

Ringkasan transaksi terbaru:
".json_encode($data['sales_history'])."

Produk yang terjual terbaru:
".json_encode($data['product_sales'])."

Tugas kamu:

1. Analisa kondisi distribusi berdasarkan data ini.
2. Berikan saran peningkatan penjualan.
3. Berikan saran peningkatan kunjungan sales.
4. Gunakan bahasa singkat, jelas, dan rapi.
5. Maksimal 6 poin saja. dibolehkan menyebut nama toko

Format jawaban:

ANALISA BISNIS
- poin
- poin

SARAN PERBAIKAN
- poin
- poin

Saran RND
- Berikan saran produk yang lagi trend dengan singkat, produk yang relevan
  
";

    return GeminiService::ask($prompt);

}

public static function generateSalesInsight(array $data)
{
    $prompt = "

Kamu adalah analis performa sales distribusi snack.

PENTING:
- Jangan menyalahkan data
- Gunakan data apa adanya
- Fokus analisa & aksi

Data:
Total Fee: {$data['total_fee']}
Rata-rata / hari: {$data['avg_per_day']}
Target: {$data['target']}
Sisa hari: {$data['remaining_days']}
Kekurangan: {$data['gap']}
Kebutuhan fee: {$data['need_fee']}
Estimasi qty: {$data['need_qty']}
Toko dengan kontribusi tinggi:
".implode(', ', $data['top_stores'] ?? [])."
Toko prioritas utama (potensi tinggi):
".implode(', ', $data['priority_high'] ?? [])."
Toko prioritas standar:
".implode(', ', $data['priority_medium'] ?? [])."

Tugas:

1. Analisa kondisi performa (singkat)
2. Tentukan apakah target realistis
3. Berikan strategi teknis (aksi nyata di lapangan, bukan motivasi)
4. Fokus ke tindakan praktis: stok, repeat order, produk, display

5. PRIORITAS BESOK:
- WAJIB sebutkan nama toko (jika ada data toko)
- Fokus ke toko dengan potensi penjualan tinggi
- Hindari kalimat motivasi seperti kejar target, harus capai target
- Gunakan gaya instruksi operasional (seperti arahan lapangan)
- Gunakan toko prioritas utama sebagai fokus utama
- Gunakan toko prioritas standar sebagai tambahan kunjungan
- Jangan gunakan toko di luar daftar
- Setiap toko harus disertai tindakan spesifik

FORMAT WAJIB:

Gunakan ENTER setiap baris.

ANALISA:
- ...
- ...

STRATEGI:
- ...
- ...

FORMAT PRIORITAS BESOK:

- Sampaikan bahwa toko yang disebut adalah toko yang sudah mendekati jadwal kunjungan

PRIORITAS UTAMA:
- Nama toko → aksi spesifik

PRIORITAS TAMBAHAN:
- Nama toko → aksi ringan / maintenance

QUOTES HARI INI:
- Berikan 1 quotes singkat untuk memotivasi sales lapangan
- Jika quotes menggunakan bahasa Inggris, WAJIB sertakan versi terjemahan bahasa Indonesia
- Terjemahan TIDAK BOLEH literal kata-per-kata
- Terjemahan harus dirangkai ulang menjadi kalimat yang natural, enak dibaca, dan terasa kuat secara makna
- Gunakan gaya bahasa yang sederhana tapi “kena” (bukan kaku atau terlalu formal)
- Utamakan makna dibanding terjemahan harfiah
- WAJIB berasal dari tokoh terkenal (pengusaha, pemimpin, atau tokoh sukses dunia)
- WAJIB sertakan nama tokoh di bawah quotes

Format WAJIB:

QUOTES HARI INI:
Isi quotes
— Nama Tokoh

(Artinya: versi Indonesia yang natural, tidak kaku)

Contoh:

QUOTES HARI INI:
Your most unhappy customers are your greatest source of learning.
— Bill Gates

(Artinya: Pelanggan yang paling tidak puas justru adalah sumber pembelajaran terbaik untuk berkembang.)

Aturan tambahan:
- Jangan buat quotes sendiri
- Jangan gunakan tokoh yang tidak jelas
- Gunakan tokoh terkenal seperti:
  - Bill Gates
  - Elon Musk
  - Steve Jobs
  - Jack Ma
  - Warren Buffett
  
- Pastikan relevan dengan kerja, konsistensi, dan eksekusi lapangan sales
- Quotes harus relevan dengan kerja sales lapangan: konsistensi, kunjungan, eksekusi
- Hindari quotes yang terlalu umum atau tidak nyambung dengan kondisi

JANGAN buat paragraf panjang.
WAJIB pakai bullet dan baris baru.

GAYA BAHASA:
- Hindari kata yang terkesan memaksa atau menekan sales
- Jangan gunakan kata seperti harus capai - target kejar target
- Gunakan bahasa operasional seperti:
  - cek stok
  - dorong repeat order
  - tambahkan SKU
  - perbaiki displays
";

    return GeminiService::ask($prompt);
}

}