<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Store;
use App\Models\Area;
use App\Models\Product;
use App\Models\StorePrice;

class StoreController extends Controller
{
    public function index(Request $request)
    {
        // =====================================================
        // WAJIB PILIH SALES DULU (KHUSUS ADMIN - SESSION BASED)
        // =====================================================
        if (auth()->user()->role === 'admin' && !$request->filled('sales_id')) {
        return redirect()->route('visits.choose_sales');
        }

        $query = Store::with('area');

        if ($request->filled('area_id')) {
            $query->where('area_id', $request->area_id);
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $stores = $query->orderBy('name')->get();

        // ==============================
        // HITUNG PIUTANG TOKO
        // ==============================

        $receivables = DB::table('receivables')
                ->select('store_id', DB::raw('SUM(remaining_amount) as total_receivable'))
                ->whereIn('status', ['unpaid','partial'])
                ->groupBy('store_id')
                ->get()
                ->keyBy('store_id');

            foreach ($stores as $store) {

        $store->receivable_amount = $receivables[$store->id]->total_receivable ?? 0;

    }

        // ==============================
        // LIST SEMUA TOKO (UNTUK SEARCH DROPDOWN)
        // ==============================
        $allStores = Store::select('name')
            ->orderBy('name')
            ->get();

        // ==============================
        // HITUNG STOK PER TOKO (LEDGER)
        // ==============================
        foreach ($stores as $store) {

    $stockData = DB::table('store_stock_movements as ssm')
        ->join('products as p', 'p.id', '=', 'ssm.product_id')
        ->select(
            'ssm.product_id',
            'p.name as product_name',
            'p.default_fee_nominal',
            DB::raw("SUM(ssm.quantity) as total_qty")
        )
        ->where('ssm.store_id', $store->id)
        ->groupBy('ssm.product_id', 'p.name', 'p.default_fee_nominal')
        ->having('total_qty', '>', 0)
        ->get();

    $products = [];
    $totalQty = 0;
    $totalEstimasiFee = 0;

    foreach ($stockData as $row) {

        $storePrice = StorePrice::where('store_id', $store->id)
            ->where('product_id', $row->product_id)
            ->value('price');

        $qty = (int) $row->total_qty;
        $price = (float) ($storePrice ?? 0);
        $feeNominal = (float) $row->default_fee_nominal;
        $estimasiFee = $qty * $feeNominal;

        $products[] = [
            'product_id' => $row->product_id,
            'name'       => $row->product_name,
            'qty'        => $qty,
            'price'      => $price,
            'subtotal'   => $qty * $price,
            'fee_nominal'  => $feeNominal,
            'estimasi_fee' => $estimasiFee,
        ];

        $totalQty += $qty;
        $totalEstimasiFee += $estimasiFee;
    }
    $store->total_estimasi_fee = $totalEstimasiFee;
    $store->products_stock = $products;
    $store->total_stock_qty = $totalQty;
}

        // ==============================
        // HITUNG STATUS KUNJUNGAN TOKO
        // ==============================

        $lateCount = $stores->where('visit_status', 'late')->count();

        $heavyLateCount = $stores->where('visit_status', 'heavy')->count();

        $withdrawCount = $stores->where('visit_status', 'withdraw')->count();

            $areas = Area::withCount('stores')
            ->orderBy('name')
            ->get();

        return view('stores.index', compact(
    'stores',
    'areas',
    'allStores',
    'lateCount',
    'heavyLateCount',
    'withdrawCount'
    ));
    }

    public function create()
    {
        $areas = Area::where('is_active', 1)
            ->orderBy('name')
            ->get();

        return view('stores.create', compact('areas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'area_id'             => 'required|exists:areas,id',
            'name'                => 'required|string|max:255',
            'owner_name'          => 'nullable|string|max:255',
            'phone'               => 'nullable|string|max:50',
            'address'             => 'nullable|string',
            'city'                => 'nullable|string|max:100',
            'visit_interval_days' => 'required|integer|min:1',
            'is_active'           => 'nullable|boolean',
        ]);

        DB::transaction(function () use ($request) {

            $store = Store::create([
                'area_id'             => $request->area_id,
                'name'                => $request->name,
                'owner_name'          => $request->owner_name,
                'phone'               => $request->phone,
                'address'             => $request->address,
                'city'                => $request->city,
                'visit_interval_days' => $request->visit_interval_days,
                'last_visit_date'     => null,
                'is_active'           => $request->boolean('is_active'),
            ]);

            $products = Product::where('is_active', 1)->get();

            foreach ($products as $product) {
                StorePrice::create([
                    'store_id'   => $store->id,
                    'product_id' => $product->id,
                    'price'      => $product->default_selling_price,
                ]);
            }
        });

        return redirect()
            ->route('stores.index')
            ->with('success', 'Toko berhasil ditambahkan dan harga otomatis dibuat.');
    }

    public function edit(Store $store)
    {
        $areas = Area::where('is_active', 1)
            ->orderBy('name')
            ->get();

        return view('stores.edit', compact('store', 'areas'));
    }

    public function update(Request $request, Store $store)
    {
        if (auth()->user()->role === 'admin') {

            $request->validate([
                'area_id'             => 'required|exists:areas,id',
                'name'                => 'required|string|max:255',
                'owner_name'          => 'nullable|string|max:255',
                'phone'               => 'nullable|string|max:50',
                'address'             => 'nullable|string',
                'city'                => 'nullable|string|max:100',
                'visit_interval_days' => 'required|integer|min:1',
                'is_active'           => 'nullable|boolean',
            ]);

            $store->update([
                'area_id'             => $request->area_id,
                'name'                => $request->name,
                'owner_name'          => $request->owner_name,
                'phone'               => $request->phone,
                'address'             => $request->address,
                'city'                => $request->city,
                'visit_interval_days' => $request->visit_interval_days,
                'is_active'           => $request->boolean('is_active'),
            ]);

        } else {

            $request->validate([
                'area_id' => 'required|exists:areas,id',
            ]);

            $store->update([
                'area_id' => $request->area_id,
            ]);
        }

        return redirect()
            ->route('stores.index')
            ->with('success', 'Toko berhasil diperbarui.');
    }

    public function editPrices(Store $store)
    {
        $this->syncStorePrices($store);

        $products = Product::where('is_active', 1)
            ->orderBy('name')
            ->get();

        $storePrices = StorePrice::where('store_id', $store->id)
            ->pluck('price', 'product_id');

        return view('stores.prices', compact('store', 'products', 'storePrices'));
    }

    public function updatePrices(Request $request, Store $store)
    {
        $this->syncStorePrices($store);

        $request->validate([
            'prices'   => 'required|array',
            'prices.*' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request, $store) {

            foreach ($request->prices as $productId => $price) {
                StorePrice::updateOrCreate(
                    [
                        'store_id'   => $store->id,
                        'product_id' => $productId,
                    ],
                    [
                        'price' => $price,
                    ]
                );
            }
        });

        return redirect()
            ->route('stores.prices.edit', $store->id)
            ->with('success', 'Harga toko berhasil diperbarui.');
    }

    private function syncStorePrices(Store $store)
    {
        $products = Product::where('is_active', 1)->get();

        foreach ($products as $product) {
            StorePrice::firstOrCreate(
                [
                    'store_id'   => $store->id,
                    'product_id' => $product->id,
                ],
                [
                    'price' => $product->default_selling_price,
                ]
            );
        }
    }

    public function destroy(string $id)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }

        $store = Store::findOrFail($id);
        $store->delete();

        return redirect()
            ->route('stores.index')
            ->with('success', 'Toko berhasil dihapus');
    }
}