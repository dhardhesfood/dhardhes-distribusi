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
use Illuminate\Support\Facades\DB;

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
        
        // Status backup distribusi
        $backupStatus = DB::table('backup_logs')
                   ->where('system','distribusi')
                   ->latest()
                   ->first();

        // ================= MISI SALES =================

if(auth()->user()->role === 'admin'){

    $missions = DB::table('missions as m')
        ->leftJoin('mission_progress as mp','mp.mission_id','=','m.id')
        ->leftJoin('users as u','u.id','=','mp.user_id')
        ->select(
            'm.id',
            'm.title',
            'm.target',
            'm.type',
            'm.start_date',
            'm.end_date',
            'm.reward_amount',
            'u.name as sales_name',
            'mp.progress',
            'mp.completed'
        )
        ->where('m.active',1)
        ->orderBy('m.id','desc')
        ->get();

}else{

    $missions = DB::table('missions as m')
        ->leftJoin('mission_progress as mp', function ($join) {
            $join->on('mp.mission_id','=','m.id')
                 ->where('mp.user_id',auth()->id());
        })
        ->select(
            'm.id',
            'm.title',
            'm.target',
            'm.type',
            'm.start_date',
            'm.end_date',
            'm.reward_amount',
            'mp.progress',
            'mp.completed'
        )
        ->where('m.active',1)
        ->whereDate('m.start_date','<=',now())
        ->whereDate('m.end_date','>=',now())
        ->get();
 
}          

// ================= REMINDER REQUEST STOK =================

$futureRequestDays = DB::table('sales_stock_requests')
    ->whereDate('request_date','>=', today())
    ->distinct()
    ->count('request_date');

$needRequestReminder = $futureRequestDays < 5;


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
            'withdrawRate',
            'backupStatus',
            'needRequestReminder',
            'futureRequestDays',
            'missions'

        ));
    }
}
