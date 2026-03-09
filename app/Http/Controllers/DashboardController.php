<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SalesSettlement;
use App\Models\Kasbon;
use App\Models\Receivable;
use App\Models\Store;
use App\Models\Visit;
use App\Models\Notification;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();

        // Settlement hari ini
        $totalSettlementToday = SalesSettlement::whereDate('settlement_date', $today)
            ->sum('expected_amount');

        // Shortage hari ini
        $totalShortageToday = SalesSettlement::whereDate('settlement_date', $today)
            ->sum('shortage_amount');

        // Settlement bulan ini
        $totalSettlementMonth = SalesSettlement::where('settlement_date', '>=', $startOfMonth)
            ->sum('expected_amount');

        // Kasbon aktif
        $totalKasbonActive = Kasbon::where('status', 'open')
            ->sum('amount_total');

        // Total piutang
        $totalPiutang = Receivable::where('status','!=','paid')
            ->sum('remaining_amount');

        // Toko aktif
        $stores = Store::with('area')
            ->where('is_active',1)
            ->get();

            // ================= STATUS TOKO =================

$totalStores = $stores->count();

$lateCount = 0;
$heavyCount = 0;
$withdrawCount = 0;

foreach($stores as $store){

    $lastVisit = $store->last_visit_date
        ? Carbon::parse($store->last_visit_date)
        : null;

    if(!$lastVisit) continue;

    $nextVisit = $lastVisit->copy()->addDays($store->visit_interval_days);

    $diff = $today->diffInDays($nextVisit, false) * -1;

    if($diff > 135){
        $withdrawCount++;
    }
    elseif($diff > 100){
        $heavyCount++;
    }
    elseif($diff > 0){
        $lateCount++;
    }

}

$lateRate = $totalStores ? round(($lateCount/$totalStores)*100,1) : 0;
$heavyRate = $totalStores ? round(($heavyCount/$totalStores)*100,1) : 0;
$withdrawRate = $totalStores ? round(($withdrawCount/$totalStores)*100,1) : 0;

        // Visit menunggu approval admin
        $pendingVisits = Visit::where('status', 'completed')->count();

        // Notifikasi belum dibaca
        $notifications = Notification::where('user_id', auth()->id())
                    ->where('is_read',0)
                    ->latest()
                    ->take(5)
                    ->get();

        $notificationsCount = $notifications->count();    

        return view('dashboard', compact(
            'totalSettlementToday',
            'totalShortageToday',
            'totalSettlementMonth',
            'totalKasbonActive',
            'totalPiutang',
            'stores',
            'pendingVisits',
            'notifications',
            'notificationsCount',
            'lateCount',
            'heavyCount',
            'withdrawCount',
            'lateRate',
            'heavyRate',
            'withdrawRate'

        ));
    }
}
