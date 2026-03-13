<x-app-layout>
<x-slot name="header">
    <div class="ticker-stars mb-6 overflow-hidden rounded-2xl shadow bg-red-600 text-white py-6">

<div class="ticker-track px-10 py-2 text-lg font-bold">

<span style="color:#fde047" class="px-32 status-glow-yellow">
🚨 Pertimbangkan ditarik: {{ $withdrawCount }} toko ({{ $withdrawRate }}%)
</span>

<span>|</span>

<span style="color:#4ade80" class="px-32 status-glow-green">
⚠ Terlambat berat: {{ $heavyCount }} toko ({{ $heavyRate }}%)
</span>

<span>|</span>

<span style="color:#ffffff" class="px-32 status-glow-white">
⏰ Terlambat: {{ $lateCount }} toko ({{ $lateRate }}%)
</span>

</div>

<div class="shooting-star star1"></div>
<div class="shooting-star star2"></div>
<div class="shooting-star star3"></div>

</div>
</x-slot>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6 bg-yellow-200 rounded-xl">

<a class="group flex items-center justify-between px-5 py-3 rounded-2xl shadow-sm mb-4 transition duration-200 text-white
@if(isset($backupStatus) && $backupStatus->status === 'success')
bg-green-600 hover:bg-green-700
@elseif(isset($backupStatus) && $backupStatus->status === 'failed')
bg-red-600 hover:bg-red-700
@else
bg-gray-600 hover:bg-gray-700
@endif
">

<div class="flex items-center gap-3">

<div class="text-lg">
💾
</div>

<div>

<div class="text-xs uppercase font-semibold opacity-80">
SYSTEM
</div>

<div class="text-sm font-semibold">
Status Backup Database
</div>

@if(isset($backupStatus))
<div class="text-xs opacity-70">
{{ $backupStatus->created_at }}
</div>
@endif

</div>

</div>

<div class="opacity-80 text-sm">
@if(isset($backupStatus) && $backupStatus->status === 'success')
✔
@elseif(isset($backupStatus) && $backupStatus->status === 'failed')
✖
@else
?
@endif
</div>

</a>

    @if(isset($notificationsCount) && $notificationsCount > 0)

<div class="mb-5 bg-blue-100 border border-blue-300 text-blue-900 px-5 py-4 rounded-xl shadow">

    <div class="flex items-center justify-between">

        <div class="font-semibold">
            @foreach($notifications as $notif)

<div class="flex justify-between items-center py-1">

<div>
{{ $notif->title }}
</div>

<a href="{{ $notif->link ?? route('warehouse.index') }}"
class="text-blue-600 text-sm">
Buka
</a>

</div>

@endforeach
        </div>

        <a href="{{ route('notifications.read') }}"
           class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded-lg text-sm">
            Lihat Pesan
        </a>

    </div>

</div>

@endif

    @if(auth()->user()->role === 'admin' && isset($pendingVisits) && $pendingVisits > 0)

<div class="mb-5 bg-red-100 border border-red-300 text-red-800 px-5 py-4 rounded-xl shadow">

    <div class="flex items-center justify-between">

        <div class="font-semibold">
            ⚠️ {{ $pendingVisits }} Visit menunggu approval admin
        </div>

        <a href="{{ route('visits.index') }}"
           class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded-lg text-sm">
            Lihat Visit
        </a>

    </div>

</div>

@endif

    @php
    $menus = [

        // ================= OPERASIONAL =================
        [
            'route'=>'sales-stock-sessions.index',
            'label'=>'Session Stok Sales',
            'tag'=>'Operasional',
            'color'=>'bg-blue-600 hover:bg-blue-700',
            'emoji'=>'⚙️'
        ],

        // ================= KEUANGAN =================
        [
            'route'=>'sales.settlements.index',
            'label'=>'Rekap Setoran Harian',
            'tag'=>'Keuangan',
            'color'=>'bg-emerald-600 hover:bg-emerald-700',
            'emoji'=>'💰'
        ],

        // ================= PENJUALAN =================
        [
            'route'=>'stores.index',
            'label'=>'Mulai Kunjungan',
            'tag'=>'Penjualan',
            'color'=>'bg-indigo-600 hover:bg-indigo-700',
            'emoji'=>'🛒'
        ],
        [
            'route'=>'visits.index',
            'label'=>'Daftar Kunjungan',
            'tag'=>'Monitoring',
            'color'=>'bg-amber-600 hover:bg-amber-700',
            'emoji'=>'📊'
        ],

        // ================= KPI =================
        [
            'route'=>'reports.kpi.sales',
            'label'=>'KPI Sales',
            'tag'=>'Performance',
            'color'=>'bg-indigo-600 hover:bg-indigo-700',
            'emoji'=>'📈'
        ],

        // ================= STOK =================
        [
            'route'=>'sales.stock',
            'label'=>'✅ Warehouse Price List',
            'tag'=>'Daftar Harga',
            'color'=>'bg-violet-600 hover:bg-violet-700',
            'emoji'=>'📦'
        ],

        // ================= RISIKO =================
        [
            'route'=>'kasbons.index',
            'label'=>'Kasbon Sales',
            'tag'=>'Risiko',
            'color'=>'bg-red-600 hover:bg-red-700',
            'emoji'=>'⚠️'
        ],

        [
    'route'=>'productions.create',
    'label'=>'Produksi',
    'tag'=>'Gudang',
    'color'=>'bg-indigo-600 hover:bg-indigo-700',
    'emoji'=>'🏭',
    'roles'=>['admin','admin_gudang']
],

[
    'route'=>'warehouse.index',
    'label'=>'Stok Gudang',
    'tag'=>'Gudang',
    'color'=>'bg-violet-600 hover:bg-violet-700',
    'emoji'=>'📦'
],

    ];

    // 🔐 FILTER KHUSUS ADMIN GUDANG
    if(auth()->user()->role === 'admin_gudang') {
        $menus = collect($menus)->filter(function($menu){
            return in_array($menu['route'], [
                'sales-stock-sessions.index',
                'productions.create',
                'warehouse.index'
            ]);
        })->values()->toArray();
    }
    @endphp

    <div class="flex flex-col gap-3">

        @foreach($menus as $menu)

          @php
          if(isset($menu['roles']) && !in_array(auth()->user()->role, $menu['roles'])){
          continue;
    }
       @endphp

        <a href="{{ route($menu['route']) }}"
           class="group flex items-center justify-between px-5 py-4 rounded-2xl shadow-sm transition duration-200 text-white {{ $menu['color'] }}">

            <div class="flex items-center gap-3">
                <div class="text-xl">
                    {{ $menu['emoji'] }}
                </div>

                <div>
                    <div class="text-xs uppercase font-semibold opacity-80">
                        {{ $menu['tag'] }}
                    </div>
                    <div class="text-base font-semibold mt-0.5">
                        {{ $menu['label'] }}
                    </div>
                </div>
            </div>

            <div class="opacity-80 group-hover:translate-x-1 transition">
                →
            </div>

        </a>

        @endforeach

    </div>

</div>

<style>

/* ===== CONTAINER TICKER ===== */

.ticker-stars{
position:relative;
overflow:hidden;
padding-top:20px;
padding-bottom:20px;
box-shadow:0 0 20px rgba(255,0,0,0.4);
}

/* ===== ANIMASI TICKER TEXT ===== */

.ticker-track{
display:inline-block;
white-space:nowrap;
padding-left:100%;
animation:tickerMove 12s linear infinite;
}

@keyframes tickerMove{

0%{
transform:translateX(0);
}

100%{
transform:translateX(-100%);
}

}


/* ===== GLOW TEXT ===== */

.status-glow-yellow{
text-shadow:
0 0 6px rgba(253,224,71,0.8),
0 0 12px rgba(253,224,71,0.6);
}

.status-glow-green{
text-shadow:
0 0 6px rgba(74,222,128,0.8),
0 0 12px rgba(74,222,128,0.6);
}

.status-glow-white{
text-shadow:
0 0 6px rgba(255,255,255,0.8),
0 0 12px rgba(255,255,255,0.6);
}


/* ===== SPARKLE STARS ===== */

.ticker-stars{
position:relative;
overflow:hidden;
padding:20px 0;
}

/* bintang kecil */
.ticker-stars::before{
content:'';
position:absolute;
top:0;
left:0;
width:100%;
height:100%;

background-image:
radial-gradient(2px 2px at 5% 20%, white, transparent),
radial-gradient(2px 2px at 15% 80%, white, transparent),
radial-gradient(2px 2px at 25% 40%, white, transparent),
radial-gradient(2px 2px at 40% 60%, white, transparent),
radial-gradient(2px 2px at 55% 30%, white, transparent),
radial-gradient(2px 2px at 70% 75%, white, transparent),
radial-gradient(2px 2px at 85% 45%, white, transparent),
radial-gradient(2px 2px at 95% 20%, white, transparent);

opacity:0.5;
animation:twinkle 3s infinite alternate;
}

@keyframes twinkle{
0%{opacity:0.2;}
100%{opacity:0.7;}
}


/* ===== SHOOTING STAR BASE ===== */

.shooting-star{

position:absolute;

width:140px;
height:2px;

background:linear-gradient(90deg, white, transparent);

opacity:0.9;

}

/* kepala bintang */
.shooting-star::before{

content:'';
position:absolute;

right:0;
top:-3px;

width:8px;
height:8px;

background:white;
border-radius:50%;

box-shadow:
0 0 6px white,
0 0 12px white,
0 0 18px white;

}


/* ===== STAR 1 ===== */

.star1{
top:20%;
left:-200px;
transform:rotate(25deg);
animation:shoot1 7s linear infinite;
}

@keyframes shoot1{

0%{
transform:translateX(-300px) translateY(-40px) rotate(25deg);
opacity:0;
}

10%{opacity:1;}

100%{
transform:translateX(1200px) translateY(80px) rotate(25deg);
opacity:0;
}

}


/* ===== STAR 2 ===== */

.star2{
top:60%;
left:-200px;
transform:rotate(-20deg);
animation:shoot2 9s linear infinite;
}

@keyframes shoot2{

0%{
transform:translateX(-300px) translateY(60px) rotate(-20deg);
opacity:0;
}

10%{opacity:1;}

100%{
transform:translateX(1200px) translateY(-60px) rotate(-20deg);
opacity:0;
}

}


/* ===== STAR 3 ===== */

.star3{
top:40%;
left:-200px;
transform:rotate(15deg);
animation:shoot3 11s linear infinite;
}

@keyframes shoot3{

0%{
transform:translateX(-300px) translateY(-20px) rotate(15deg);
opacity:0;
}

10%{opacity:1;}

100%{
transform:translateX(1200px) translateY(40px) rotate(15deg);
opacity:0;
}

}

</style>


</x-app-layout>