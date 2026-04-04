<x-app-layout>

@php

$lateStoresCount = 0;
$heavyStoresCount = 0;
$withdrawStoresCount = 0;

foreach ($stores as $s) {

    if ($s->visit_status === 'late') {
        $lateStoresCount++;
    }

    if ($s->visit_status === 'heavy') {
        $heavyStoresCount++;
    }

    if ($s->visit_status === 'withdraw') {
        $withdrawStoresCount++;
    }

}

@endphp

    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Daftar Toko
            </h2>

            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 sm:gap-3">

                <form method="GET" action="{{ route('stores.index') }}" class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">

                    @if(request('sales_id'))
                        <input type="hidden" name="sales_id" value="{{ request('sales_id') }}">
                    @endif

                    <select name="area_id"
                            onchange="this.form.submit()"
                            class="border-gray-300 rounded-md shadow-sm text-sm px-2 py-2">
                        <option value="">Semua Area</option>
                        @foreach($areas as $area)
                            <option value="{{ $area->id }}"
                                {{ request('area_id') == $area->id ? 'selected' : '' }}>
                                {{ $area->name }} ({{ $area->stores_count }})
                            </option>
                        @endforeach
                    </select>

                </form>

                <button onclick="openStoreSearch()"
                class="inline-flex justify-center items-center w-full sm:w-auto px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg shadow-md">
                🔍 Cari Toko
                </button>

                <a href="{{ route('stores.index', array_merge(request()->all(), ['late' => 1])) }}"
class="alert-late inline-flex justify-center items-center w-full sm:w-auto px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-lg shadow-md transition transform hover:scale-105 hover:shadow-lg active:scale-95">
🔴 Terlambat ({{ $lateStoresCount }})
</a>

                <a href="{{ route('stores.index', array_merge(request()->all(), ['heavy' => 1])) }}"
class="alert-heavy inline-flex justify-center items-center w-full sm:w-auto px-4 py-2 bg-red-500 hover:bg-red-600 text-white text-sm font-semibold rounded-lg shadow-md transition transform hover:scale-105 hover:shadow-lg active:scale-95">
🟠 Terlambat Berat ({{ $heavyStoresCount }})
</a>

                <a href="{{ route('stores.index', array_merge(request()->all(), ['withdraw' => 1])) }}"
class="alert-withdraw inline-flex justify-center items-center w-full sm:w-auto px-4 py-2 text-white text-sm font-semibold rounded-lg shadow-md"
style="background-color:black;"
onmouseover="this.style.backgroundColor='#333'"
onmouseout="this.style.backgroundColor='black'">
⚫ Pertimbangkan Ditarik ({{ $withdrawStoresCount }})
</a>

                <a href="{{ route('stores.create') }}"
                   class="inline-flex justify-center items-center w-full sm:w-auto px-5 py-2.5 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-lg shadow-lg transition duration-150">
                    + Tambah Toko
                </a>

            </div>
        </div>

    </x-slot>

    <div class="py-6">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">

            @if (session('success'))
                <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-700 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded font-semibold">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white shadow sm:rounded-lg p-6">

                @php
                    $globalProducts = [];
                    $globalTotalValue = 0;
                    $globalEstimasiFee = 0;

                    foreach ($stores as $store) {
                        if (!empty($store->products_stock)) {
                            foreach ($store->products_stock as $product) {

                                if (!isset($globalProducts[$product['name']])) {
                                    $globalProducts[$product['name']] = 0;
                                }

                                $globalProducts[$product['name']] += $product['qty'];

                                $globalTotalValue += $product['subtotal'] ?? 0;
                                $globalEstimasiFee += $product['estimasi_fee'] ?? 0;
                            }
                        }
                    }
                @endphp

                @if(count($globalProducts) > 0)
                    <div class="mb-6 border-b pb-4">
                        <div class="font-semibold text-gray-800 mb-2">
                            Total Produk dari Toko yang Ditampilkan
                        </div>

                        <ul class="space-y-1 text-sm">
                            @foreach($globalProducts as $name => $qty)
                                <li class="flex justify-between border-b pb-1">
                                    <span>{{ $name }}</span>
                                    <span class="font-semibold">{{ $qty }}</span>
                                </li>
                            @endforeach
                        </ul>

                        <div class="mt-4 pt-3 border-t text-sm font-semibold text-green-700 flex justify-between">
                           <span>Estimasi Fee Maksimal Jika Stok Habis</span>
                           <span>Rp {{ number_format($globalEstimasiFee,0,',','.') }}</span>
                        </div>

                        <div class="mt-4 pt-3 border-t">
                            <div class="flex justify-between text-sm font-semibold text-gray-800">
                                <span>Total Nilai Rupiah Stok (Sesuai Harga Toko)</span>
                                <span>
                                    Rp {{ number_format($globalTotalValue, 0, ',', '.') }}
                                </span>
                            </div>
                        </div>
                    </div>
                @endif

                @forelse($stores->sortBy(function($s){
    return match($s->visit_status){
        'withdraw' => 1,
        'heavy' => 2,
        'late' => 3,
        'today' => 4,
        'safe' => 5,
        default => 6
    };
}) as $store)

                @php

$showStore = true;

if(request('late')){
    $showStore = $store->visit_status === 'late';
}

if(request('heavy')){
    $showStore = $store->visit_status === 'heavy';
}

if(request('withdraw')){
    $showStore = $store->visit_status === 'withdraw';
}

@endphp

@if(!$showStore)
    @continue
@endif

                    <div class="flex justify-between items-start sm:items-center border-b py-4 gap-4">

                        <div class="pr-2 w-full">

                            <div class="font-semibold text-lg text-gray-800">
                                {{ $store->name }}
                            </div>

                            <div class="text-sm text-gray-500">
                                Area: {{ optional($store->area)->name ?? '-' }}
                            </div>

                            @php
$lastVisit = $store->last_visit_date
    ? \Carbon\Carbon::parse($store->last_visit_date)
    : null;

$nextVisit = $lastVisit
    ? $lastVisit->copy()->addDays($store->visit_interval_days)
    : null;
@endphp

<div class="text-xs text-gray-600 mt-1">
    Terakhir Dikunjungi:
    <span class="font-medium">
        {{ $lastVisit ? $lastVisit->format('d M Y') : '-' }}
    </span>
</div>

<div class="text-xs text-gray-600">
    Kunjungan Berikutnya:
    <span class="font-medium">
        {{ $nextVisit ? $nextVisit->format('d M Y') : '-' }}
    </span>
</div>

<div class="text-xs mt-1">
    Status Kunjungan:

    @if($store->visit_status === 'safe')
<span class="text-green-600 font-semibold">🟢 Aman</span>

@elseif($store->visit_status === 'today')
<span class="text-yellow-600 font-semibold">🟡 Hari Ini</span>

@elseif($store->visit_status === 'late')
<span class="visit-alert-late text-red-600 font-semibold">
🔴 Terlambat
</span>

@elseif($store->visit_status === 'heavy')
<span class="visit-alert-heavy text-orange-600 font-semibold">
🟠 Terlambat Berat
</span>

@elseif($store->visit_status === 'withdraw')
<span class="visit-alert-withdraw text-gray-800 font-semibold">
⚫ Pertimbangkan Ditarik
</span>

@endif

</div>
                            <div class="text-sm mt-1">
                                Status:
                                @if($store->is_active)
                                    <span class="text-green-600 font-medium">Aktif</span>
                                @else
                                    <span class="text-red-600 font-medium">Nonaktif</span>
                                @endif
                            </div>

                            <div class="mt-4 text-sm">
                                <div class="font-semibold mb-1 text-gray-700">
                                    Stok Produk:
                                </div>

                                @if(!empty($store->products_stock))
                                    <ul class="space-y-1">
                                        @foreach($store->products_stock as $product)
                                            <li class="flex justify-between border-b pb-1">
                                                <span>{{ $product['name'] }}</span>
                                                <span class="font-semibold">
                                                    {{ $product['qty'] }}
                                                </span>
                                            </li>
                                        @endforeach
                                    </ul>

                                    @php
$lastPhysicalStocks = \App\Models\VisitItem::whereHas('visit', function($q) use ($store) {
        $q->where('store_id', $store->id);
    })
    ->orderBy('id','desc')
    ->get()
    ->groupBy('product_id')
    ->map(function($items){
        return $items->first()->physical_stock;
    });
@endphp

@if(!empty($store->products_stock))
    <div class="mt-2 text-xs text-red-600 font-semibold">
        Cek Stok Terakhir:
    </div>

    <ul class="text-xs text-red-600">
        @foreach($store->products_stock as $product)

            @php
                $physical = $lastPhysicalStocks[$product['product_id']] ?? null;
            @endphp

            @if(!is_null($physical))
                <li class="flex justify-between">
                    <span>{{ $product['name'] }}</span>
                    <span>{{ $physical }}</span>
                </li>
            @endif

        @endforeach
    </ul>
@endif

                                @else
                                    <div class="text-gray-400 text-xs">
                                        Tidak ada stok
                                    </div>
                                @endif
                            </div>

                        </div>

                        <div class="flex flex-col sm:flex-row gap-2 items-end">

                            @if(request('sales_id'))
                                <a href="{{ route('visits.create', [
                                        'store' => $store->id,
                                         'sales_id' => request('sales_id')
                                          ]) }}"
                                   onclick="return checkReceivable({{ $store->receivable_amount }})"
                                   class="inline-flex justify-center items-center px-3 py-1.5 sm:px-4 sm:py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs sm:text-sm font-medium rounded-md shadow-md transition whitespace-nowrap">
                                   Kunjungan
                                </a>
                            @else
                                <a href="{{ route('visits.create', $store->id) }}"
                                 onclick="return checkReceivable({{ $store->receivable_amount }})"
                                 class="inline-flex justify-center items-center px-3 py-1.5 sm:px-4 sm:py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs sm:text-sm font-medium rounded-md shadow-md transition whitespace-nowrap">
                                 Kunjungan
                                </a>
                            @endif

                            <a href="{{ route('stores.edit', $store->id) }}"
                               class="inline-flex justify-center items-center px-3 py-1.5 sm:px-4 sm:py-2 bg-yellow-500 hover:bg-yellow-600 text-white text-xs sm:text-sm font-medium rounded-md shadow-md transition whitespace-nowrap">
                                Edit
                            </a>

                            @php
    $phone = preg_replace('/[^0-9]/', '', $store->phone ?? '');

    if (substr($phone, 0, 1) === '0') {
        $phone = '62' . substr($phone, 1);
    }

    $message = urlencode("*Assalamualaikum Pak/Bu {$store->name}*\n\nSaya dari *Dhardhes Food* 😊\n\nMau menanyakan terkait *stok produk Dhardhes* di toko saat ini.\n\nSekalian konfirmasi rencana pengiriman:\n\n*Hari :*\n*Tanggal :*\n\nMohon infonya ya Pak/Bu 🙏\nTerima kasih");
@endphp

@if($phone)
    <a href="https://wa.me/{{ $phone }}?text={{ $message }}"
       target="_blank"
       class="inline-flex justify-center items-center px-3 py-1.5 sm:px-4 sm:py-2 bg-green-600 hover:bg-green-700 text-white text-xs sm:text-sm font-medium rounded-md shadow-md transition whitespace-nowrap">
        WA
    </a>
@endif

                            @if(auth()->user()->role === 'admin')

                                <a href="{{ route('stores.prices.edit', $store->id) }}"
                                   class="inline-flex justify-center items-center px-3 py-1.5 sm:px-4 sm:py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs sm:text-sm font-medium rounded-md shadow-md transition whitespace-nowrap">
                                    Kelola Harga
                                </a>

                                <a href="{{ route('stock-opnames.create', $store->id) }}"
                                   class="inline-flex justify-center items-center px-3 py-1.5 sm:px-4 sm:py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs sm:text-sm font-medium rounded-md shadow-md transition whitespace-nowrap">
                                    Penyesuaian Stok
                                </a>

                                <form action="{{ route('stores.destroy', $store->id) }}"
                                      method="POST"
                                      onsubmit="return confirm('Yakin hapus toko ini?')">
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit"
                                            class="inline-flex justify-center items-center px-3 py-1.5 sm:px-4 sm:py-2 bg-red-600 hover:bg-red-700 text-white text-xs sm:text-sm font-medium rounded-md shadow-md transition whitespace-nowrap">
                                        Hapus
                                    </button>
                                </form>

                            @endif

                        </div>

                    </div>

                @empty

                    <div class="text-gray-500 text-center py-10">
                        Belum ada data toko.
                    </div>

                @endforelse

            </div>

        </div>
    </div>

<div id="storeSearchModal"
     onclick="closeStoreSearch()"
     class="fixed inset-0 bg-black bg-opacity-40 hidden items-start justify-center pt-24 z-50">

    <div class="bg-white w-full max-w-xl rounded-lg shadow-xl" onclick="event.stopPropagation()">

        <div class="p-4 border-b">
            <input
                type="text"
                id="storeSearchInput"
                placeholder="Ketik nama toko..."
                class="w-full border-gray-300 rounded-md"
                onkeyup="filterStores()">
        </div>

        <div id="storeSearchList"
             class="max-h-72 overflow-y-auto text-sm">
        </div>

    </div>

</div>

<script>

let stores = @json($allStores->pluck('name'));

function openStoreSearch(){
    document.getElementById('storeSearchModal').classList.remove('hidden');
    document.getElementById('storeSearchInput').focus();
    filterStores();
}

function closeStoreSearch(){
    document.getElementById('storeSearchModal').classList.add('hidden');
}

function filterStores(){

    let keyword = document.getElementById('storeSearchInput').value.toLowerCase();
    let list = document.getElementById('storeSearchList');

    list.innerHTML = '';

    stores
        .filter(s => s.toLowerCase().includes(keyword))
        .slice(0,10)
        .forEach(name => {

            let item = document.createElement('div');

            item.className = "px-4 py-2 hover:bg-gray-100 cursor-pointer";

            item.innerText = name;

            item.onclick = function(){

                let url = new URL(window.location.href);

                url.searchParams.set('search', name);

                window.location = url.toString();

            }

            list.appendChild(item);

        });

}

document.addEventListener('keydown',function(e){

    if(e.key === "Escape"){
        closeStoreSearch();
    }

    if(e.ctrlKey && e.key === "k"){
        e.preventDefault();
        openStoreSearch();
    }

});

</script>

<style>

/* ========================= */
/* ANIMASI STATUS KUNJUNGAN */
/* ========================= */

@keyframes visitBlink {
0%,100% { opacity:1 }
50% { opacity:0.35 }
}

@keyframes visitPulse {
0% { transform:scale(1) }
50% { transform:scale(1.08) }
100% { transform:scale(1) }
}

@keyframes visitShake {
0% { transform:translateX(0) }
25% { transform:translateX(-2px) }
50% { transform:translateX(2px) }
75% { transform:translateX(-2px) }
100% { transform:translateX(0) }
}

/* ========================= */
/* STATUS TERLAMBAT */
/* ========================= */

.visit-alert-late{
display:inline-block;
animation: visitPulse 1.6s infinite !important;
}

/* ========================= */
/* TERLAMBAT BERAT */
/* ========================= */

.visit-alert-heavy{
display:inline-block;
animation: visitPulse 1.2s infinite, visitShake 0.6s infinite !important;
}

/* ========================= */
/* PERTIMBANGKAN DITARIK */
/* ========================= */

.visit-alert-withdraw{
display:inline-block;
animation: visitBlink 1s infinite, visitPulse 2s infinite !important;
}

@keyframes alertPulse {
0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(239,68,68,0.7); }
70% { transform: scale(1.06); box-shadow: 0 0 0 12px rgba(239,68,68,0); }
100% { transform: scale(1); }
}

@keyframes alertShake {
0% { transform: translateX(0); }
25% { transform: translateX(-2px); }
50% { transform: translateX(2px); }
75% { transform: translateX(-2px); }
100% { transform: translateX(0); }
}

/* terlambat ringan */
.alert-late {
animation: alertPulse 2s infinite;
}

/* terlambat berat */
.alert-heavy {
animation: alertPulse 1.4s infinite, alertShake 0.8s infinite;
}

/* pertimbangkan ditarik */
.alert-withdraw {
animation: alertPulse 2.6s infinite;
}

</style>

<script>

function checkReceivable(amount){

    if(amount > 0){

        let formatted = new Intl.NumberFormat('id-ID').format(amount);

        return confirm(
"Toko ini memiliki piutang Rp " + formatted +
"\n\nApakah tetap ingin melanjutkan kunjungan?"
        );

    }

    return true;

}

</script>

</x-app-layout>