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

        // Visit menunggu approval admin
        $pendingVisits = Visit::where('status', 'completed')->count();

        // Notifikasi belum dibaca
        $notificationsCount = Notification::where('user_id', auth()->id())
            ->where('is_read', false)
            ->count();    

        return view('dashboard', compact(
            'totalSettlementToday',
            'totalShortageToday',
            'totalSettlementMonth',
            'totalKasbonActive',
            'totalPiutang',
            'stores',
            'pendingVisits',
            'notificationsCount'
        ));
    }
}
