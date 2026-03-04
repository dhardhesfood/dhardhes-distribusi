<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Store;
use App\Models\Area;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class VisitScheduleController extends Controller
{
    public function index(Request $request)
    {
        $selectedDate = $request->input('tanggal', Carbon::today()->toDateString());
        $selectedDate = Carbon::parse($selectedDate);

        $filterStatus = $request->input('status');
        $filterArea   = $request->input('area_id');

        // ambil semua area untuk dropdown
        $areas = Area::orderBy('name')->get();

        $storesQuery = Store::with('area')
            ->where('is_active', 1);

        // ==========================
        // FILTER AREA
        // ==========================
        if ($filterArea) {
            $storesQuery->where('area_id', $filterArea);
        }

        $stores = $storesQuery->get();

        $data = [];

        $summary = [
            'Terlambat' => 0,
            'Siap Dikunjungi' => 0,
            'Akan Datang' => 0,
            'Belum Pernah Dikunjungi' => 0,
        ];

        $grandTotalProduk = 0;
        $grandTotalQty = 0;
        $grandTotalEstimasiFee = 0;
        $grandTotalNilaiStok = 0;

        foreach ($stores as $store) {

            $lastVisit = $store->last_visit_date
                ? Carbon::parse($store->last_visit_date)
                : null;

            if (!$lastVisit) {

                $status = 'Belum Pernah Dikunjungi';
                $nextVisit = null;

            } else {

                $nextVisit = $lastVisit->copy()->addDays($store->visit_interval_days);

                if ($selectedDate->gt($nextVisit)) {
                    $status = 'Terlambat';
                } elseif ($selectedDate->equalTo($nextVisit)) {
                    $status = 'Siap Dikunjungi';
                } else {
                    $status = 'Akan Datang';
                }
            }

            if ($filterStatus && $status !== $filterStatus) {
                continue;
            }

            // ==========================
            // HITUNG STOK PRODUK TOKO
            // ==========================
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
            $totalProduk = 0;
            $totalQty = 0;

            foreach ($stockData as $row) {

                $qty = (int) $row->total_qty;

            $price = DB::table('store_prices')
               ->where('store_id', $store->id)
               ->where('product_id', $row->product_id)
               ->value('price');

            $price = (float) ($price ?? 0);
            $feeNominal = (float) ($row->default_fee_nominal ?? 0);

            $estimasiFee = $qty * $feeNominal;
            $subtotal = $qty * $price;

            $products[] = [
            'name' => $row->product_name,
            'qty'  => $qty,
          ];

            $grandTotalEstimasiFee += $estimasiFee;
            $grandTotalNilaiStok += $subtotal;

                $totalProduk++;
                $totalQty += (int) $row->total_qty;
            }

            $summary[$status]++;
            $grandTotalProduk += $totalProduk;
            $grandTotalQty += $totalQty;

            $data[] = [
                'id' => $store->id,
                'name' => $store->name,
                'area' => $store->area->name ?? '-',
                'last_visit' => $lastVisit ? $lastVisit->format('d M Y') : '-',
                'next_visit' => $nextVisit ? $nextVisit->format('d M Y') : '-',
                'status' => $status,
                'total_produk' => $totalProduk,
                'total_qty' => $totalQty,
                'products' => $products,
            ];
        }

        $priorityOrder = [
            'Terlambat' => 1,
            'Siap Dikunjungi' => 2,
            'Akan Datang' => 3,
            'Belum Pernah Dikunjungi' => 4,
        ];

        usort($data, function ($a, $b) use ($priorityOrder) {
            return $priorityOrder[$a['status']] <=> $priorityOrder[$b['status']];
        });

        return view('visit-schedules.index', [
            'stores' => $data,
            'areas' => $areas,
            'selectedDate' => $selectedDate->format('Y-m-d'),
            'selectedArea' => $filterArea,
            'summary' => $summary,
            'totalStores' => count($data),
            'grandTotalProduk' => $grandTotalProduk,
            'grandTotalQty' => $grandTotalQty,
            'grandTotalEstimasiFee' => $grandTotalEstimasiFee,
            'grandTotalNilaiStok' => $grandTotalNilaiStok,
        ]);
    }
}