<x-app-layout>

<div class="py-8">
<div class="max-w-3xl mx-auto">

<h2 class="text-xl font-bold mb-4">Edit Order</h2>

<div class="bg-white shadow rounded-lg p-6">
<form method="POST" action="/online-orders/{{ $order->id }}">
@csrf
@method('PUT')

<div class="mb-4">
    <label>Customer</label>
    <input type="text" name="customer_name"
        value="{{ $order->customer_name }}"
        class="border p-2 w-full">
</div>

@foreach($items as $i => $item)

<div class="flex items-center justify-between border rounded-lg px-4 py-2 mb-2 bg-gray-50">

    <!-- NAMA PRODUK -->
    <div class="flex-1 text-sm font-medium text-gray-700">
        {{ $item->product_name }} ({{ $item->variant_name }})
    </div>

    <!-- INPUT -->
    <div class="flex items-center gap-2">

        <input type="hidden" name="items[{{ $i }}][product_id]" value="{{ $item->product_id }}">
        <input type="hidden" name="items[{{ $i }}][variant_id]" value="{{ $item->product_variant_id }}">

        <input type="number"
            name="items[{{ $i }}][qty]"
            value="{{ $item->qty }}"
            class="w-20 border border-gray-300 rounded px-2 py-1 text-center">

    </div>

</div>

@endforeach

<button class="bg-blue-600 text-white px-4 py-2 rounded">
    Update
</button>

</form>
</div>

</div>
</div>

</x-app-layout>