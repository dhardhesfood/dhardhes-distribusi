<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Product;

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

}