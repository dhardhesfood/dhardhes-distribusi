<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Visit;
use App\Models\Store;
use App\Models\Product;
use App\Models\StorePrice;
use App\Models\VisitItem;
use App\Models\ProductCostHistory;
use App\Models\VisitBonus;
use App\Models\SalesStockSession;
use App\Models\SalesSettlement;
use App\Services\VisitService;

class VisitController extends Controller
{
    protected $visitService;
    public function __construct(VisitService $visitService)
    {
        $this->visitService = $visitService;
    }
    public function index(Request $request)
    {
        // 🔹 AUTO CLEANUP VISIT DRAFT KOSONG (KHUSUS SALES)
        if (auth()->user()->role === 'sales') {
            $draftVisits = Visit::where('user_id', auth()->id())
                ->where('status', 'draft')
                ->get();
            foreach ($draftVisits as $draft) {
                // Skip jika settlement sudah ditutup
                if (SalesSettlement::isClosed($draft->user_id, $draft->visit_date)) {
                    continue;
                }
                // jika belum pernah diproses (tidak punya sales movement)
                if (!$draft->salesMovements()->exists()) {
                    DB::transaction(function () use ($draft) {
                        $draft->bonuses()->delete();
                        $draft->items()->delete();
                        $draft->delete();
                    });
                }
            }
        }
        $date = $request->date ?? now()->toDateString();
        $range = $request->range ?? null;

if ($range === 'today') {
    $startDate = now()->toDateString();
    $endDate = now()->toDateString();
}

elseif ($range === 'yesterday') {
    $startDate = now()->subDay()->toDateString();
    $endDate = $startDate;
}

elseif ($range === '7days') {
    $startDate = now()->subDays(6)->toDateString();
    $endDate = now()->toDateString();
}

elseif ($range === '30days') {
    $startDate = now()->subDays(29)->toDateString();
    $endDate = now()->toDateString();
}

else {
    $startDate = $date;
    $endDate = $date;
}
        
        if (auth()->user()->role === 'admin') {
            $visits = Visit::with('store')
            ->whereBetween('visit_date', [$startDate, $endDate])
            ->latest()
            ->get();
        } else {
            $visits = Visit::with('store')
            ->where('user_id', auth()->id())
            ->whereBetween('visit_date', [$startDate, $endDate])
            ->latest()
            ->get();
        }
        return view('visits.index', compact('visits','date','range'));
    }
    public function chooseSales()
    {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }
        $salesUsers = \App\Models\User::whereIn('role', ['sales', 'admin'])
            ->orderBy('name')
            ->get();
        return view('visits.choose-sales', compact('salesUsers'));
    }
    public function create(Request $request, $storeId)
    {
        $store = Store::findOrFail($storeId);
        // Tentukan sales target
        if (auth()->user()->role === 'admin') {
            $targetUserId = $request->query('sales_id');
            if (!$targetUserId) {
                return redirect()->route('visits.choose_sales');
            }
        } else {
            $targetUserId = auth()->id();
        }
        if (SalesSettlement::isClosed($targetUserId, now())) {
            abort(403, 'Settlement tanggal ini sudah ditutup.');
        }
        $session = SalesStockSession::where('user_id', $targetUserId)
            ->where('status', 'open')
            ->whereDate('start_date', now()->toDateString())
            ->first();
        if (!$session) {
            abort(403, 'Anda belum memulai session stok.');
        }
        try {
            $visit = Visit::create([
                'store_id'   => $store->id,
                'user_id' => $targetUserId,
                'visit_date' => now(),
                'status'     => 'draft',
            ]);
            $this->visitService->generateVisitItems($visit);
            return redirect()->route('visits.edit', $visit->id);
        } catch (\Exception $e) {
            if (isset($visit)) {
                $visit->delete();
            }
            return redirect()
                ->route('stores.index', ['sales_id' => $targetUserId])
                ->with('error', $e->getMessage());
        }
    }
    public function edit($visitId)
    {
        $visit = Visit::with([
            'items.product',
            'store.area',
            'bonuses.product'
        ])
            ->where('id', $visitId)
            ->firstOrFail();
        if (SalesSettlement::isClosed($visit->user_id, $visit->visit_date)) {
            abort(403, 'Settlement sudah ditutup. Visit tidak dapat diubah.');
        }
        if ($visit->status === 'completed') {
            abort(403, 'Visit sudah selesai.');
        }
        return view('visits.edit', ['visit' => $visit]);
    }
    public function destroy($visitId)
    {
        $visit = Visit::where('id', $visitId)->firstOrFail();
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Hanya admin yang dapat menghapus visit.');
        }
        if ($visit->status !== 'draft') {
            abort(422, 'Hanya visit dengan status sdraft yang dapat dihapus.');
        }
        if (SalesSettlement::isClosed($visit->user_id, $visit->visit_date)) {
            abort(403, 'Settlement sudah ditutup. Visit tidak dapat dihapus.');
        }
        DB::transaction(function () use ($visit) {
            $visit->bonuses()->delete();
            $visit->items()->delete();
            $visit->delete();
        });
        return redirect()
            ->route('visits.index')
            ->with('success', 'Visit berhasil dihapus.');
    }
    public function addProduct(Request $request, $visitId)
    {
        $visit = Visit::with('items')->where('id', $visitId)->firstOrFail();
        if (SalesSettlement::isClosed($visit->user_id, $visit->visit_date)) {
            abort(403, 'Settlement sudah ditutup. Visit tidak dapat diubah.');
        }
        if ($visit->status === 'completed') {
            abort(403, 'Visit sudah selesai.');
        }
        $request->validate([
            'product_id' => 'required|exists:products,id'
        ]);
        $productId = $request->product_id;
        if ($visit->items()->where('product_id', $productId)->exists()) {
            return redirect()->route('visits.edit', $visit->id)
                ->with('error', 'Produk sudah ada di visit.');
        }
        $storePrice = StorePrice::where('store_id', $visit->store_id)
            ->where('product_id', $productId)
            ->first();
        if (!$storePrice) {
            return redirect()->route('visits.edit', $visit->id)
                ->with('error', 'Produk tidak memiliki harga untuk toko ini.');
        }
        $cost = ProductCostHistory::getCostForDate(
            $productId,
            $visit->visit_date
        );
        if ($cost <= 0) {
            return redirect()->route('visits.edit', $visit->id)
                ->with('error', 'Produk belum memiliki HPP aktif.');
        }
        $product = Product::findOrFail($productId);
        VisitItem::create([
            'visit_id'            => $visit->id,
            'product_id'          => $productId,
            'initial_stock'       => 0,
            'remaining_stock'     => 0,
            'sold_qty'            => 0,
            'return_qty'          => 0,
            'new_delivery_qty'    => 0,
            'bonus_qty'           => 0,
            'stock_reduction_qty' => 0,
            'price_snapshot'      => $storePrice->price,
            'fee_snapshot'        => $product->default_fee_nominal,
            'cost_snapshot'       => $cost,
        ]);
        return redirect()->route('visits.edit', $visit->id)
            ->with('success', 'Produk berhasil ditambahkan ke visit.');
    }
    public function submit(Request $request, $visitId)
    {

        $visit = Visit::with(['items', 'bonuses'])
            ->where('id', $visitId)
            ->firstOrFail();
        if (SalesSettlement::isClosed($visit->user_id, $visit->visit_date)) {
            abort(403, 'Settlement sudah ditutup. Visit tidak dapat diproses.');
        }
        if ($visit->status === 'completed') {
            abort(403, 'Visit sudah diproses.');
        }
        if ($visit->items->count() === 0) {
            abort(422, 'Visit tidak memiliki item.');
        }
        $validated = $request->validate([
            'admin_fee'          => 'nullable|numeric|min:0',
            'cash_paid'          => 'nullable|numeric|min:0',
            'visit_date'         => 'nullable|date',
            'bonus_product_id'   => 'nullable|array',
            'bonus_product_id.*' => 'nullable|exists:products,id',
            'bonus_qty'          => 'nullable|array',
            'bonus_qty.*'        => 'nullable|numeric|min:1',
        ]);
        foreach ($visit->items as $item) {

    $remaining = (int) ($request->remaining_stock[$item->id] ?? $item->initial_stock);
    $physicalStock = (int) ($request->physical_stock[$item->id] ?? 0);
    $newQty       = (int) ($request->new_delivery_qty[$item->id] ?? 0);
    $reductionQty = (int) ($request->stock_reduction_qty[$item->id] ?? 0);

    $maxPossible = $item->initial_stock + $newQty;

    if ($remaining > $maxPossible) {
        abort(422, "Sisa stok tidak boleh melebihi stok tersedia.");
    }

    if ($remaining < 0 || $newQty < 0 || $reductionQty < 0) {
        abort(422, "Nilai tidak boleh negatif.");
    }

    $finalStock = $remaining - $reductionQty + $newQty;

    if ($finalStock < 0) {
        abort(422, "Stok toko tidak boleh minus.");
    }

    $item->update([
    'remaining_stock'     => $remaining,
    'physical_stock'      => $physicalStock,
    'new_delivery_qty'    => $newQty,
    'stock_reduction_qty' => $reductionQty,
    'sold_qty'            => 0,
    ]);
   }
        $visit->bonuses()->delete();
        if (!empty($validated['bonus_product_id'])) {
            foreach ($validated['bonus_product_id'] as $index => $productId) {
                $qty = (int) ($validated['bonus_qty'][$index] ?? 0);
                if ($productId && $qty > 0) {
                    VisitBonus::create([
                        'visit_id'   => $visit->id,
                        'product_id' => $productId,
                        'qty'        => $qty,
                    ]);
                }
            }
        }
        $updateData = [
            'admin_fee' => (float) $request->input('admin_fee', 0),
        ];
        // hanya admin boleh ubah tanggal
        if (auth()->user()->role === 'admin' && !empty($validated['visit_date'])) {
            if (SalesSettlement::isClosed($visit->user_id, $validated['visit_date'])) {
                abort(403, 'Settlement pada tanggal tersebut sudah ditutup.');
            }
            $updateData['visit_date'] = $validated['visit_date'];
        }
        $visit->update($updateData);
        $this->visitService->processVisit(
            $visit->fresh()->load(['items', 'bonuses']),
            (float) ($validated['cash_paid'] ?? 0)
        );
        return redirect()->route('visits.show', $visit->id)
            ->with('success', 'Visit berhasil diproses.');
    }
    public function show($visitId)
    {
        $visit = Visit::with([
            'items.product',
            'store.area',
            'storeMovements.product',
            'salesMovements.product',
            'bonuses.product'
        ])
            ->where('id', $visitId)
            ->firstOrFail();
        if (auth()->user()->role === 'sales' && $visit->user_id !== auth()->id()) {
            abort(403);
        }
        return view('visits.show', compact('visit'));
    }
    public function approve($visitId)
    {
        $visit = Visit::with('store')->where('id', $visitId)->firstOrFail();
        if ($visit->status !== 'completed') {
            abort(422, 'Visit belum selesai.');
        }
        if (!is_null($visit->approved_at)) {
            abort(422, 'Visit sudah di-approve.');
        }
        if (SalesSettlement::isClosed($visit->user_id, $visit->visit_date)) {
            abort(403, 'Settlement sudah ditutup. Visit tidak dapat di-approve.');
        }
        DB::transaction(function () use ($visit) {
            $visit->status = 'approved';
            $visit->approved_at = now();
            $visit->approved_by = auth()->id();
            $visit->save();
            $visit->store->update([
                'last_visit_date' => $visit->visit_date
            ]);
        });
        return redirect()
            ->route('visits.show', $visit->id)
            ->with('success', 'Visit berhasil di-approve.');
    }
    /*
|--------------------------------------------------------------------------
| REOPEN VISIT (ADMIN ONLY)
|--------------------------------------------------------------------------
*/
    public function reopen($visitId)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Hanya admin yang dapat membuka kembali visit.');
        }
        $visit = Visit::with('salesTransaction')
            ->where('id', $visitId)
            ->firstOrFail();
        // Cek settlement belum closed
        if (SalesSettlement::isClosed($visit->user_id, $visit->visit_date)) {
            abort(403, 'Settlement sudah ditutup. Visit tidak dapat dibuka kembali.');
        }
        try {
            $this->visitService->rollbackVisit($visit);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
        return redirect()
            ->route('visits.edit', $visit->id)
            ->with('success', 'Visit berhasil dibuka kembali.');
    }
}
