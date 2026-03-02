<x-app-layout>
<x-slot name="header">
    <h2 class="text-xl font-semibold text-gray-800 leading-tight">
        Dashboard Distribusi
    </h2>
</x-slot>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

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

        [
            'route'=>'visit.schedules.index',
            'label'=>'Jadwal Pengiriman',
            'tag'=>'Operasional',
            'color'=>'bg-blue-600 hover:bg-blue-700',
            'emoji'=>'🚚'
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

    ];

    // 🔐 FILTER KHUSUS ADMIN GUDANG
    if(auth()->user()->role === 'admin_gudang') {
        $menus = collect($menus)->filter(function($menu){
            return in_array($menu['route'], [
                'sales-stock-sessions.index',
                'visit.schedules.index'
            ]);
        })->values()->toArray();
    }
    @endphp

    <div class="flex flex-col gap-3">

        @foreach($menus as $menu)

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

</x-app-layout>