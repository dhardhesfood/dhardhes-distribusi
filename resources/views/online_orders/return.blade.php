<x-app-layout>

<div class="max-w-3xl mx-auto py-6">

<h2 class="text-xl font-bold mb-4">
    QC Return - {{ $order->customer_name }}
</h2>

<form method="POST">
@csrf

<table class="w-full border text-sm">

<tr class="bg-gray-100">
    <th class="p-2">Produk</th>
    <th>Qty Kirim</th>
    <th>Stok Fisik</th>
    <th>Rusak</th>
</tr>

@foreach($items as $i => $item)

<tr class="border-t">

<td class="p-2">
    {{ $item->product_name }} ({{ $item->variant_name }})
</td>

<td class="text-center">
    {{ $item->qty }}

    <input type="hidden" name="items[{{ $i }}][qty]" value="{{ $item->qty }}">
    <input type="hidden" name="items[{{ $i }}][product_id]" value="{{ $item->product_id }}">
    <input type="hidden" name="items[{{ $i }}][variant_id]" value="{{ $item->product_variant_id }}">
</td>

<td class="text-center">
    <input type="number"
           name="items[{{ $i }}][fisik]"
           value="{{ $item->qty }}"
           class="border w-20 text-center">
</td>

<td class="text-center">
    <input type="number"
           name="items[{{ $i }}][rusak]"
           value="0"
           class="border w-20 text-center">
</td>

</tr>

@endforeach

</table>

<div class="mt-4 flex gap-2">

    <button class="bg-green-600 text-white px-4 py-2 rounded">
        Simpan Return
    </button>

    <a href="/online-orders"
       class="bg-gray-500 text-white px-4 py-2 rounded inline-block">
        Batal
    </a>

</div>

</form>

</div>

</x-app-layout>