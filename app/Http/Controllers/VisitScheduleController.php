<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Store;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class VisitScheduleController extends Controller
{
    public function index(Request $request)
    {
        $selectedDate = $request->input('tanggal', Carbon::today()->toDateString());
        $selectedDate = Carbon::parse($selectedDate);

        $filterStatus = $request->input('status');

        $stores = Store::with('area')
            ->where('is_active', 1)
            ->get();

        $data = [];

        $summary = [
            'Terlambat' => 0,
            'Siap Dikunjungi' => 0,
            'Akan Datang' => 0,
            'Belum Pernah Dikunjungi' => 0,
        ];

        $grandTotalProduk = 0;
        $grandTotalQty = 0;

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

            // ==========================
            // FILTER STATUS (JIKA ADA)
            // ==========================
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
                    DB::raw("SUM(ssm.quantity) as total_qty")
                )
                ->where('ssm.store_id', $store->id)
                ->groupBy('ssm.product_id', 'p.name')
                ->having('total_qty', '>', 0)
                ->get();

            $products = [];
            $totalProduk = 0;
            $totalQty = 0;

            foreach ($stockData as $row) {
                $products[] = [
                    'name' => $row->product_name,
                    'qty'  => (int) $row->total_qty,
                ];

                $totalProduk++;
                $totalQty += (int) $row->total_qty;
            }

            // ==========================
            // HITUNG SUMMARY & GRAND TOTAL
            // (HANYA YANG LOLOS FILTER)
            // ==========================
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
            'selectedDate' => $selectedDate->format('Y-m-d'),
            'summary' => $summary,
            'totalStores' => count($data),
            'grandTotalProduk' => $grandTotalProduk,
            'grandTotalQty' => $grandTotalQty,
        ]);
    }
}