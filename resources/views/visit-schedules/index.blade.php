<x-app-layout>

<x-slot name="header">
<h2 class="text-xl font-semibold text-gray-800 leading-tight">
Jadwal Kunjungan Sales
</h2>
</x-slot>

<div class="py-6 px-6 max-w-7xl mx-auto">

@php
$physicalStocks = \App\Models\VisitItem::with('product')
    ->orderBy('id','desc')
    ->get()
    ->groupBy(function($item){
        return $item->product->name;
    })
    ->map(function($items){
        return $items->first()->physical_stock;
    });
@endphp


{{-- RINGKASAN --}}
<div class="grid grid-cols-2 md:grid-cols-6 gap-4 mb-6">

<a href="{{ route('visit.schedules.index', ['tanggal'=>$selectedDate]) }}"
class="bg-white shadow rounded p-4 text-center block hover:shadow-md transition">
<div class="text-xs text-gray-500">Total Toko Aktif</div>
<div class="text-2xl font-bold mt-1">
{{ $totalStores }}
</div>
</a>

<div class="bg-indigo-600 text-white shadow rounded p-4 text-center">
<div class="text-xs">Total Produk (Semua Toko)</div>
<div class="text-2xl font-bold mt-1">
{{ $grandTotalProduk }}
</div>
</div>

<div class="bg-blue-600 text-white shadow rounded p-4 text-center">
<div class="text-xs">Total Qty Stok</div>
<div class="text-2xl font-bold mt-1">
{{ $grandTotalQty }}
</div>
</div>

<a href="{{ route('visit.schedules.index', ['tanggal'=>$selectedDate,'status'=>'Terlambat']) }}"
class="bg-red-600 text-white shadow rounded p-4 text-center block hover:opacity-90 transition">
<div class="text-xs">Terlambat</div>
<div class="text-2xl font-bold mt-1">
{{ $summary['Terlambat'] }}
</div>
</a>

<a href="{{ route('visit.schedules.index', ['tanggal'=>$selectedDate,'status'=>'Siap Dikunjungi']) }}"
class="bg-green-600 text-white shadow rounded p-4 text-center block hover:opacity-90 transition">
<div class="text-xs">Siap Dikunjungi</div>
<div class="text-2xl font-bold mt-1">
{{ $summary['Siap Dikunjungi'] }}
</div>
</a>

<a href="{{ route('visit.schedules.index', ['tanggal'=>$selectedDate,'status'=>'Akan Datang']) }}"
class="bg-yellow-500 text-white shadow rounded p-4 text-center block hover:opacity-90 transition">
<div class="text-xs">Akan Datang</div>
<div class="text-2xl font-bold mt-1">
{{ $summary['Akan Datang'] }}
</div>
</a>

</div>


{{-- REKAP GLOBAL PER PRODUK --}}
@php
$globalProducts = [];

foreach ($stores as $store) {
foreach ($store['products'] as $product) {
if (!isset($globalProducts[$product['name']])) {
$globalProducts[$product['name']] = 0;
}
$globalProducts[$product['name']] += $product['qty'];
}
}
@endphp


<div class="bg-white shadow rounded p-4 mb-6">

<div class="font-semibold mb-3">
Rekap Total Stok Per Produk (Sesuai Filter)
</div>

@if(count($globalProducts) > 0)

<div class="grid md:grid-cols-3 gap-4">

@foreach($globalProducts as $name => $qty)

<div class="border rounded p-3 flex justify-between">
<span>{{ $name }}</span>
<span class="font-bold">{{ $qty }}</span>
</div>

@endforeach

</div>

@else

<div class="text-gray-400 text-sm">
Tidak ada data produk.
</div>

@endif

<div class="mt-4 border-t pt-3 space-y-2 text-sm">

<div class="flex justify-between text-green-700 font-semibold">
<span>Estimasi Fee Maksimal Jika Stok Habis</span>
<span>
Rp {{ number_format($grandTotalEstimasiFee,0,',','.') }}
</span>
</div>

<div class="flex justify-between font-semibold">
<span>Total Nilai Rupiah Stok (Sesuai Harga Toko)</span>
<span>
Rp {{ number_format($grandTotalNilaiStok,0,',','.') }}
</span>
</div>

</div>


{{-- FILTER TANGGAL --}}
<div class="bg-white shadow rounded p-4 mb-6">

<form method="GET" class="flex flex-col md:flex-row gap-3 md:items-end">

<div>
<label class="text-sm font-medium">Filter Area</label>

<select name="area_id" class="border p-2 rounded w-full">

<option value="">Semua Area</option>

@foreach($areas as $area)

<option value="{{ $area->id }}"
{{ $selectedArea == $area->id ? 'selected' : '' }}>

{{ $area->name }}

</option>

@endforeach

</select>

</div>

<div>
<button class="bg-blue-600 text-white px-4 py-2 rounded">
Tampilkan
</button>
</div>

</form>

</div>


{{-- TABEL DESKTOP --}}
<div class="hidden md:block bg-white shadow rounded overflow-hidden">

<table class="w-full text-sm">

<thead class="bg-gray-100">

<tr>
<th class="p-3 text-left">Nama Toko</th>
<th class="p-3 text-left">Area</th>
<th class="p-3 text-left">Terakhir Dikunjungi</th>
<th class="p-3 text-left">Jadwal Berikutnya</th>
<th class="p-3 text-center">Jumlah Produk</th>
<th class="p-3 text-center">Total Qty</th>
<th class="p-3 text-left">Detail Produk</th>
<th class="p-3 text-center">Status</th>
</tr>

</thead>

<tbody class="divide-y">

@foreach($stores as $store)

@php
$lastVisit = \App\Models\Visit::whereHas('store', function($q) use ($store){
    $q->where('name', $store['name']);
})
->orderBy('visit_date','desc')
->first();

$physicalStocks = [];

if($lastVisit){
    $physicalStocks = \App\Models\VisitItem::with('product')
        ->where('visit_id',$lastVisit->id)
        ->get()
        ->keyBy(function($item){
            return $item->product->name;
        });
}
@endphp

<tr>

<td class="p-3 font-semibold">
{{ $store['name'] }}
</td>

<td class="p-3">
{{ $store['area'] }}
</td>

<td class="p-3">
{{ $store['last_visit'] }}
</td>

<td class="p-3">
{{ $store['next_visit'] }}
</td>

<td class="p-3 text-center font-semibold">
{{ $store['total_produk'] }}
</td>

<td class="p-3 text-center font-semibold">
{{ $store['total_qty'] }}
</td>


<td class="p-3">

@if(count($store['products']) > 0)

<ul class="space-y-1">

@foreach($store['products'] as $product)

<li class="flex justify-between">
<span>{{ $product['name'] }}</span>
<span class="font-semibold">{{ $product['qty'] }}</span>
</li>

@endforeach

</ul>


<div class="mt-2 text-xs text-red-600 font-semibold">
Cek Stok Terakhir
</div>

<ul class="text-xs text-red-600">

@foreach($store['products'] as $product)

@php
$physical = isset($physicalStocks[$product['name']])
    ? $physicalStocks[$product['name']]->physical_stock
    : null;
@endphp

@if(!is_null($physical))

<li class="flex justify-between">
<span>{{ $product['name'] }}</span>
<span>{{ $physical }}</span>
</li>

@endif

@endforeach

</ul>

@else

<span class="text-gray-400 text-xs">Tidak ada stok</span>

@endif

</td>


<td class="p-3 text-center">

@if($store['status'] == 'Siap Dikunjungi')

<span class="bg-green-600 text-white px-3 py-1 rounded text-xs">
Siap Dikunjungi
</span>

@elseif($store['status'] == 'Terlambat')

<span class="bg-red-600 text-white px-3 py-1 rounded text-xs">
Terlambat
</span>

@elseif($store['status'] == 'Akan Datang')

<span class="bg-yellow-500 text-white px-3 py-1 rounded text-xs">
Akan Datang
</span>

@else

<span class="bg-gray-400 text-white px-3 py-1 rounded text-xs">
Belum Pernah Dikunjungi
</span>

@endif

</td>

</tr>

@endforeach

</tbody>

</table>

</div>


{{-- CARD MOBILE --}}
<div class="md:hidden space-y-4">

@foreach($stores as $store)

@php
$lastVisit = \App\Models\Visit::whereHas('store', function($q) use ($store){
    $q->where('name', $store['name']);
})
->orderBy('visit_date','desc')
->first();

$physicalStocks = [];

if($lastVisit){
    $physicalStocks = \App\Models\VisitItem::with('product')
        ->where('visit_id',$lastVisit->id)
        ->get()
        ->keyBy(function($item){
            return $item->product->name;
        });
}
@endphp

<div class="bg-white shadow rounded p-4">

<div class="font-semibold text-lg mb-1">
{{ $store['name'] }}
</div>

<div class="text-sm text-gray-600 mb-2">
Area: {{ $store['area'] }}
</div>

<div class="text-sm mb-2">
<div>Terakhir: {{ $store['last_visit'] }}</div>
<div>Berikutnya: {{ $store['next_visit'] }}</div>
</div>

<div class="text-sm mb-2">
<div>Jumlah Produk: <strong>{{ $store['total_produk'] }}</strong></div>
<div>Total Qty: <strong>{{ $store['total_qty'] }}</strong></div>
</div>


<div class="text-sm mb-2">

<div class="font-semibold mb-1">
Detail Produk:
</div>

@if(count($store['products']) > 0)

<ul class="space-y-1">

@foreach($store['products'] as $product)

<li class="flex justify-between">
<span>{{ $product['name'] }}</span>
<span class="font-semibold">{{ $product['qty'] }}</span>
</li>

@endforeach

</ul>


<div class="mt-2 text-xs text-red-600 font-semibold">
Cek Stok Terakhir
</div>

<ul class="text-xs text-red-600">

@foreach($store['products'] as $product)

@php
$physical = isset($physicalStocks[$product['name']])
    ? $physicalStocks[$product['name']]->physical_stock
    : null;
@endphp

@if(!is_null($physical))

<li class="flex justify-between">
<span>{{ $product['name'] }}</span>
<span>{{ $physical }}</span>
</li>

@endif

@endforeach

</ul>

@else

<div class="text-gray-400 text-xs">
Tidak ada stok
</div>

@endif

</div>


<div class="mt-3">

@if($store['status'] == 'Siap Dikunjungi')

<span class="bg-green-600 text-white px-3 py-1 rounded text-xs">
Siap Dikunjungi
</span>

@elseif($store['status'] == 'Terlambat')

<span class="bg-red-600 text-white px-3 py-1 rounded text-xs">
Terlambat
</span>

@elseif($store['status'] == 'Akan Datang')

<span class="bg-yellow-500 text-white px-3 py-1 rounded text-xs">
Akan Datang
</span>

@else

<span class="bg-gray-400 text-white px-3 py-1 rounded text-xs">
Belum Pernah Dikunjungi
</span>

@endif

</div>

</div>

@endforeach

</div>

</div>

</x-app-layout>