<x-app-layout>

<x-slot name="header">
<h2 class="font-semibold text-xl text-gray-800 leading-tight">
Request Stok Sales
</h2>
</x-slot>

<div class="py-6">
<div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

<div class="bg-white shadow-sm sm:rounded-lg p-6">

@if(session('success'))
<div class="mb-4 text-green-600 font-semibold">
{{ session('success') }}
</div>
@endif

@if(auth()->user()->role !== 'admin_gudang')
<form method="POST" action="{{ route('stock.requests.store') }}">
@csrf

<div class="mb-4">
<label class="block text-sm font-medium mb-1">Tanggal Request</label>
<input type="date"
name="request_date"
required
class="border rounded w-full p-2">
</div>

<div class="mb-4">
<label class="block text-sm font-medium mb-1">Produk</label>

<select name="product_id"
class="border rounded w-full p-2"
required>

<option value="">-- pilih produk --</option>

@foreach($products as $product)
<option value="{{ $product->id }}">
{{ $product->name }}
</option>
@endforeach

</select>
</div>

<div class="mb-4">
<label class="block text-sm font-medium mb-1">Jumlah Pack</label>
<input type="number"
name="qty_pack"
min="1"
required
class="border rounded w-full p-2">
</div>

<div class="flex gap-2">

<button
type="submit"
class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow-sm">

Simpan Request

</button>

<button
type="button"
onclick="addAnother()"
class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded shadow-sm">

Tambah Produk Lagi

</button>

</div>

</form>

@endif

</div>

</div>
</div>

<div class="mt-8 max-w-4xl mx-auto sm:px-6 lg:px-8">

<div class="bg-white shadow-sm sm:rounded-lg p-6">

<h3 class="font-semibold mb-4">Daftar Request Sales</h3>

<div class="overflow-x-auto">

<table class="min-w-full border text-sm">

<thead>
<tr class="bg-gray-100">
<th class="p-2 border">Tanggal</th>
<th class="p-2 border">Produk</th>
<th class="p-2 border text-right">Qty Pack</th>
<th class="p-2 border text-center">Aksi</th>
</tr>
</thead>

<tbody>

@php
$lastDate = null;
@endphp

@foreach($requests as $req)

@if($lastDate !== null && $lastDate != $req->request_date)

<tr>
<td colspan="4" class="h-4 border-0"></td>
</tr>

@endif

<tr>

<td class="p-2 border">
{{ $req->request_date }}
</td>

<td class="p-2 border">
{{ $req->product_name }}
</td>

<td class="p-2 border text-right">
{{ $req->qty_pack }}
</td>

<td class="p-2 border text-center">

@if(auth()->user()->role !== 'admin_gudang')

<form method="POST"
action="{{ route('stock.requests.destroy',$req->id) }}"
onsubmit="return confirm('Hapus request ini?')">

@csrf
@method('DELETE')

<button
class="bg-red-600 hover:bg-red-700 text-white px-2 py-1 rounded text-sm">

Hapus

</button>

</form>

@endif

</td>

</tr>

@php
$lastDate = $req->request_date;
@endphp

@endforeach

</tbody>

</table>

</div>

</div>

</div>

<div class="mt-8 max-w-4xl mx-auto sm:px-6 lg:px-8">

<div class="bg-white shadow-sm sm:rounded-lg p-6">

<h3 class="font-semibold mb-4">
Analisa FIFO Request vs Stok Gudang
</h3>

<div class="overflow-x-auto">

<table class="min-w-full border text-sm">

<thead>
<tr class="bg-gray-100">
<th class="p-2 border">Tanggal</th>
<th class="p-2 border">Produk</th>
<th class="p-2 border text-right">Ready Pack</th>
<th class="p-2 border text-right">Request</th>
<th class="p-2 border text-right">Dialokasikan</th>
<th class="p-2 border text-right">Kurang</th>
<th class="p-2 border text-center">Status</th>
</tr>
</thead>

<tbody>

@php
$lastFifoDate = null;

/*
Urutkan FIFO berdasarkan tanggal lama → baru
*/
$sortedFifo = collect($fifo)->sortBy('date');
@endphp

@foreach($sortedFifo as $row)

@if($lastFifoDate !== null && $lastFifoDate != $row['date'])

<tr>
<td colspan="7" class="h-4 border-0"></td>
</tr>

@endif

<tr>

<td class="p-2 border">
{{ $row['date'] }}
</td>

<td class="p-2 border">
{{ $row['product'] }}
</td>

<td class="p-2 border text-right">
{{ $row['ready'] }}
</td>

<td class="p-2 border text-right">
{{ $row['request'] }}
</td>

<td class="p-2 border text-right">
{{ $row['allocated'] }}
</td>

<td class="p-2 border text-right">
{{ $row['short'] }}
</td>

<td class="p-2 border text-center">

@if($row['status'] == 'Terpenuhi')

<span class="text-green-600 font-semibold">
Terpenuhi
</span>

@else

<span class="text-red-600 font-semibold">
Kurang
</span>

@endif

</td>

</tr>

@php
$lastFifoDate = $row['date'];
@endphp

@endforeach

</tbody>

</table>

</div>

</div>

</div>

@if(!empty($shortage))

<div class="mt-6 max-w-4xl mx-auto sm:px-6 lg:px-8">

<div class="bg-yellow-100 border border-yellow-300 text-yellow-900 px-6 py-4 rounded-lg shadow">

<div class="font-semibold mb-2">
Prioritas Produksi
</div>

@foreach($shortage as $product => $qty)

<div>
{{ $product }} kurang {{ $qty }} pack
</div>

@endforeach

</div>

</div>

@endif

<script>

function addAnother() {

    const form = document.querySelector('form');

    const date = document.querySelector('[name=request_date]').value;

    if(!date){
        alert('Isi tanggal dulu');
        return;
    }

    const product = document.querySelector('[name=product_id]').value;
    const qty = document.querySelector('[name=qty_pack]').value;

    if(!product || !qty){
        alert('Isi produk dan qty dulu');
        return;
    }

    form.submit();

}

</script>

</x-app-layout>