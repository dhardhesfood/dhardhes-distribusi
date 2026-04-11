<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use Illuminate\Support\Facades\Http;

class StockRequestController extends Controller
{

public function create()
{

    /*
    =========================
    PRODUK AKTIF
    =========================
    */

    $products = Product::where('is_active',1)
        ->orderBy('name')
        ->get();

    $areas = DB::table('areas')
    ->orderBy('name')
    ->get();


    /*
    =========================
    TABEL REQUEST SALES
    (HANYA INPUT SALES)
    =========================
    */

    $requests = DB::table('sales_stock_request_items')
    ->join('sales_stock_requests','sales_stock_requests.id','=','sales_stock_request_items.request_id')
    ->join('products','products.id','=','sales_stock_request_items.product_id')
    ->join('areas','areas.id','=','sales_stock_requests.area_id')
        ->select(
    'sales_stock_request_items.id',
    'sales_stock_requests.request_date',
    'areas.name as area_name',
    'products.name as product_name',
    'sales_stock_request_items.qty_pack'
)
        ->whereDate('sales_stock_requests.request_date', '>=', today())
        ->orderBy('sales_stock_requests.request_date','asc')
        ->get();



    /*
    =========================
    DATA REQUEST UNTUK FIFO
    =========================
    */

    $raw = DB::table('sales_stock_requests')
    ->join('sales_stock_request_items','sales_stock_requests.id','=','sales_stock_request_items.request_id')
    ->join('products','products.id','=','sales_stock_request_items.product_id')
    ->select(
        'sales_stock_requests.request_date',
        'products.id as product_id',
        'products.name as product_name',
        DB::raw('SUM(sales_stock_request_items.qty_pack) as qty')
    )
    ->whereDate('sales_stock_requests.request_date', '>=', today()) // 🔥 INI KUNCI
    ->groupBy(
        'sales_stock_requests.request_date',
        'products.id',
        'products.name'
    )
    ->orderBy('products.id','asc')
    ->orderBy('sales_stock_requests.request_date','asc')
    ->get();



    /*
    =========================
    READY PACK GUDANG
    =========================
    */

    $ready = DB::table('warehouse_ready_packs')
        ->pluck('ready_pack','product_id')
        ->toArray();



    /*
    =========================
    ENGINE FIFO
    =========================
    */

    $result = $this->calculateFifo($raw, $ready);

    $fifo = $result['fifo'];
    $shortage = $result['shortage'];
    


    /*
    =========================
    RETURN VIEW
    =========================
    */

    return view(
        'stock_requests.create',
        compact(
            'products',
            'areas',
            'requests',
            'fifo',
            'shortage'
        )
    );

}



public function store(Request $request)
{

    $request->validate([
    'area_id' => 'required|exists:areas,id',
    'request_date' => 'required|date',
    'product_id' => 'required|exists:products,id',
    'qty_pack' => 'required|integer|min:1'
    ]);


    DB::transaction(function () use ($request) {

        $requestId = DB::table('sales_stock_requests')
        ->insertGetId([
        'user_id' => auth()->id(),
        'area_id' => $request->area_id,
        'request_date' => $request->request_date,
        'created_at' => now(),
        'updated_at' => now()
    ]);


        DB::table('sales_stock_request_items')
            ->insert([
                'request_id' => $requestId,
                'product_id' => $request->product_id,
                'qty_pack' => $request->qty_pack,
                'created_at' => now(),
                'updated_at' => now()
            ]);

    });

    // =========================
// DATA REQUEST UNTUK FIFO (ULANGI)
// =========================
$raw = DB::table('sales_stock_requests')
    ->join('sales_stock_request_items','sales_stock_requests.id','=','sales_stock_request_items.request_id')
    ->join('products','products.id','=','sales_stock_request_items.product_id')
    ->select(
        'sales_stock_requests.request_date',
        'products.id as product_id',
        'products.name as product_name',
        DB::raw('SUM(sales_stock_request_items.qty_pack) as qty')
    )
    ->whereDate('sales_stock_requests.request_date', '>=', today())
    ->groupBy(
        'sales_stock_requests.request_date',
        'products.id',
        'products.name'
    )
    ->orderBy('products.id','asc')
    ->orderBy('sales_stock_requests.request_date','asc')
    ->get();

    // =========================
// READY PACK
// =========================
$ready = DB::table('warehouse_ready_packs')
    ->pluck('ready_pack','product_id')
    ->toArray();


// =========================
// HITUNG FIFO
// =========================
$result = $this->calculateFifo($raw, $ready);

$fifo = $result['fifo'];
$shortage = $result['shortage'];

// =========================
// PESAN PRODUKSI (DETAIL REQUEST + STATUS)
// =========================
$messageProduksi = "📦 *REQUEST STOK SALES (PAK HERI)*\n\n";

$groupedProduksi = [];

foreach($fifo as $row){
    $groupedProduksi[$row['date']][] = $row;
}

$urgentProduct = null;
$urgentQty = 0;
$urgentDate = null;

foreach($groupedProduksi as $date => $rows){

    $messageProduksi .= "📅 " . date('d M', strtotime($date)) . "\n";

    // =========================
    // REQUEST
    // =========================
    $messageProduksi .= "🧾 Request:\n";

    foreach($rows as $r){
        $messageProduksi .= "• {$r['product']} → {$r['request']} pack\n";
    }

    // =========================
    // STATUS FIFO
    // =========================
    $messageProduksi .= "\n📊 Status:\n";

    $hasShort = false;

    foreach($rows as $r){

        if($r['short'] > 0){
            $hasShort = true;

            $messageProduksi .= "• {$r['product']} kurang {$r['short']} pack\n";

            // cari prioritas terbesar
            if($r['short'] > $urgentQty){
                $urgentQty = $r['short'];
                $urgentProduct = $r['product'];
                $urgentDate = $date;
            }
        }
    }

    if(!$hasShort){
        $messageProduksi .= "• Semua aman\n";
    }

    $messageProduksi .= "\n";
}

// =========================
// PRIORITAS PRODUKSI
// =========================
if($urgentQty > 0){
    $messageProduksi .= "🔥 *PRIORITAS PRODUKSI*\n";
    $messageProduksi .= "• {$urgentProduct} minimal {$urgentQty} pack (untuk " . date('d M', strtotime($urgentDate)) . ")\n";
}

// =========================
// PESAN SALES (STATUS ONLY + HALUS)
// =========================
$messageSales = "📢 *INFO STOK*\n\n";

$grouped = [];

foreach($fifo as $row){
    $grouped[$row['date']][] = $row;
}

foreach($grouped as $date => $rows){

    $messageSales .= "📅 " . date('d M', strtotime($date)) . "\n";

    $hasShort = false;

    foreach($rows as $r){
        if($r['short'] > 0){
            $hasShort = true;
            $messageSales .= "• {$r['product']} kurang {$r['short']} pack\n";
        }
    }

    if(!$hasShort){
        $messageSales .= "• Semua aman\n";
    }

    $messageSales .= "\n";
}

// PENUTUP HALUS
$messageSales .= "🙏 Silakan menyesuaikan pengiriman dengan stok ready di gudang agar pengiriman tetap lancar\n";


// =========================
// KIRIM WA (WAHA)
// =========================
$apiKey = 'c07522d03e6b4c8e91785b62e4e7676f';
$session = 'MindhesRara';

// =========================
// NOMOR WA
// =========================

// 🏭 PRODUKSI
$targetsProduksi = [
    '6288989393804@c.us', // BU WATI
    '62895808077030@c.us', // BU INTAN
];

// 🧑‍💼 SALES
$targetsSales = [
    '6285227433334@c.us',
];

// =========================
// KIRIM KE PRODUKSI
// =========================
foreach($targetsProduksi as $target){

    Http::withHeaders([
        'X-API-KEY' => $apiKey
    ])->post('http://localhost:3000/api/sendText', [
        'session' => $session,
        'chatId' => $target,
        'text' => $messageProduksi
    ]);

}

// =========================
// KIRIM KE SALES
// =========================
foreach($targetsSales as $target){

    Http::withHeaders([
        'X-API-KEY' => $apiKey
    ])->post('http://localhost:3000/api/sendText', [
        'session' => $session,
        'chatId' => $target,
        'text' => $messageSales
    ]);

}

    
    return redirect()
        ->route('stock.requests.create')
        ->with('success','Request stok berhasil disimpan.');

}



public function destroy($id)
{

    DB::transaction(function () use ($id) {

        $item = DB::table('sales_stock_request_items')
            ->where('id',$id)
            ->first();

        if(!$item){
            return;
        }

        DB::table('sales_stock_request_items')
            ->where('id',$id)
            ->delete();

        $count = DB::table('sales_stock_request_items')
            ->where('request_id',$item->request_id)
            ->count();

        if($count == 0){
            DB::table('sales_stock_requests')
                ->where('id',$item->request_id)
                ->delete();
        }

    });

    return redirect()->back();
}

private function calculateFifo($raw, $ready)
{
    $fifo = [];
    $shortage = [];

    $currentProduct = null;
    $stock = 0;

    foreach($raw as $row){

        if($currentProduct !== $row->product_id){

            $currentProduct = $row->product_id;

            $stock = $ready[$row->product_id] ?? 0;

        }

        $allocated = min($stock,$row->qty);

        $short = $row->qty - $allocated;

        $stock -= $allocated;

        $fifo[] = [
            'date'=>$row->request_date,
            'product'=>$row->product_name,
            'ready'=>$stock + $allocated,
            'request'=>$row->qty,
            'allocated'=>$allocated,
            'short'=>$short,
            'status'=>$short>0 ? 'Kurang' : 'Terpenuhi'
        ];

        if($short>0){

            $shortage[$row->product_name] =
                ($shortage[$row->product_name] ?? 0) + $short;

        }

    }

    return [
        'fifo' => $fifo,
        'shortage' => $shortage
    ];
}

}