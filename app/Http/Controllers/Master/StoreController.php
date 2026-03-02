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
        // HITUNG STOK PER TOKO (LEDGER)
        // ==============================
        foreach ($stores as $store) {

            $stockData = DB::table('store_stock_movements as ssm')
                ->join('products as p', 'p.id', '=', 'ssm.product_id')
                ->select(
                    'ssm.product_id',
                    'p.name as product_name',
                    DB::raw("SUM(ssm.quantity) as total_qty")
                )
                ->where('ssm.store_id', $store->id)
                ->groupBy('ssm.product_id', 'p.name')
                ->having('total_qty', '>', 0)
                ->get();

            $products = [];
            $totalQty = 0;

            foreach ($stockData as $row) {
                $products[] = [
                    'name' => $row->product_name,
                    'qty'  => (int) $row->total_qty,
                ];

                $totalQty += (int) $row->total_qty;
            }

            $store->products_stock = $products;
            $store->total_stock_qty = $totalQty;
        }

        $areas = Area::withCount('stores')
            ->orderBy('name')
            ->get();

        return view('stores.index', compact('stores', 'areas'));
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