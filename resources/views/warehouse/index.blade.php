<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Stok Gudang
        </h2>
    </x-slot>

```
<div class="py-6">
    <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">

        <div class="bg-white shadow-sm sm:rounded-lg p-6">

         @if(session('success'))
         <div class="mb-4 bg-green-100 border border-green-300 text-green-800 px-4 py-3 rounded">
           {{ session('success') }}
         </div>
         @endif

            <div class="mb-4 flex gap-2">

    <a href="{{ route('dashboard') }}"
       class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded shadow-sm">
        ← Kembali ke Dashboard
    </a>

    @if(auth()->user()->role === 'admin')
        <a href="{{ route('warehouse.adjustment.create') }}"
           class="bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded shadow-sm">
            Penyesuaian Stok
        </a>
    @endif

   </div>

            <table class="w-full border">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="p-2 border text-left">Produk</th>
                        <th class="p-2 border text-right">Stok Gudang</th>
                        <th class="p-2 border text-center">Ready Pack</th>
                    </tr>
                </thead>

                <form method="POST" action="{{ route('warehouse.ready_packs.update') }}">
@csrf

<tbody>

@foreach($stocks as $stock)

<tr>

<td class="p-2 border">
{{ $stock->name }}
</td>

<td class="p-2 border text-right font-semibold">
{{ $stock->stock }}
</td>

<td class="p-2 border text-center">

<input
type="number"
name="ready_packs[{{ $stock->id }}]"
value="{{ $stock->ready_pack ?? 0 }}"
class="border rounded p-1 w-24 text-center">

{{-- 🔥 TOMBOL CONVERT (ADMIN ONLY + OFFLINE ONLY) --}}
@if(auth()->user()->role === 'admin' && $stock->stock > 0)
<div class="mt-2">
    <a href="{{ route('warehouse.convert.form', $stock->id) }}"
       style="font-size:12px;background:#2563eb;color:white;padding:4px 8px;border-radius:6px;text-decoration:none;">
        Convert
    </a>
</div>
@endif

</td>

</tr>

@endforeach

</tbody>
</table>

<div class="mt-4">
<button
type="submit"
class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded shadow-sm">

Update Ready Pack

</button>
</div>

</form>

            </table>

            <hr class="my-6">

<h3 class="text-lg font-semibold mb-3">Gudang Online (Detail Varian)</h3>

<table class="w-full border border-gray-300">
    <thead class="bg-gray-100">
        <tr>
            <th class="p-2 border text-left">Produk</th>
            <th class="p-2 border text-left">Varian</th>
            <th class="p-2 border text-right">Stok</th>
            <th class="p-2 border text-center">Aksi</th>
        </tr>
    </thead>

    <tbody>

    @forelse($onlineStocks as $row)

    <tr>
        <td class="p-2 border">
            {{ $row->product_name }}
        </td>

        <td class="p-2 border">
            {{ $row->variant_name }}
        </td>

        <td class="p-2 border text-right font-semibold">
            {{ number_format($row->stock_qty) }}
        </td>

        <td class="p-2 border text-center">

@if(auth()->user()->role === 'admin')
    <a href="{{ route('warehouse.convert.offline.form', $row->product_id) }}"
       style="background:#dc2626;color:white;padding:4px 10px;border-radius:6px;font-size:12px;text-decoration:none;">
        Balik
    </a>
@endif

</td>
    </tr>

    @empty

    <tr>
        <td colspan="3" class="text-center py-4 text-gray-500">
            Belum ada stok online
        </td>
        
    </tr>

    @endforelse

    </tbody>
    
</table>

                <div class="mt-6 space-y-3">

                    @foreach($notes as $note)

                    @php
                    $color = match($note->user->role) {
                        'admin' => 'text-red-600',
                        'admin_gudang' => 'text-purple-600',
                        'sales' => 'text-blue-600',
                        default => 'text-gray-700'
                    };
                    @endphp

                    <div class="border rounded p-3 bg-gray-50">

                        <div class="text-xs text-gray-500">
                            {{ $note->created_at->format('d-m-Y H:i') }}
                            -
                            <span class="{{ $color }} font-semibold">
                                {{ $note->user->name }}
                            </span>
                        </div>

                        <div class="mt-1">
                            {{ $note->message }}
                        </div>

                    </div>

                    @endforeach

                </div>

                <div class="mt-8">

                <h3 class="font-semibold mb-2">Catatan Gudang</h3>

                <form method="POST" action="{{ route('warehouse.note.store') }}">
                    @csrf

                    <textarea
                        name="message"
                        rows="3"
                        class="w-full border rounded p-2 mb-2"
                        placeholder="Tulis pesan untuk tim..."></textarea>

                    <button
                        type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow-sm">
                        Kirim Catatan
                    </button>

                </form>

            </div>

        </div>

    </div>
</div>
```

</x-app-layout>
