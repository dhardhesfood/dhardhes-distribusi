<x-app-layout>

<div class="py-8">
<div class="max-w-3xl mx-auto sm:px-6 lg:px-8">

    <div class="bg-white shadow-md rounded-xl p-6">

        <h2 class="text-xl font-bold mb-4">
            Convert ke Online
        </h2>

        <div class="mb-4">
            <div class="text-sm text-gray-500">Produk</div>
            <div class="text-lg font-semibold">
                {{ $product->name }}
            </div>
        </div>

        <div class="mb-6">
            <div class="text-sm text-gray-500">Total Stok Gudang</div>
            <div class="text-2xl font-bold text-blue-600">
                {{ number_format($stock) }}
            </div>
        </div>

        <div class="mb-4 text-sm text-red-500">
        ⚠ Total input varian harus sama dengan stok: {{ $stock }}
      </div>

        <form method="POST" action="{{ route('warehouse.convert.process', $product->id) }}">
        @csrf

        <div class="space-y-3">

            @foreach($variants as $v)
            <div class="flex items-center justify-between border rounded-lg p-3">

                <div class="font-medium">
                    {{ $v->name }}
                </div>

                <input 
                    type="number"
                    name="variants[{{ $v->id }}][qty]"
                    value="0"
                    min="0"
                    class="w-24 border rounded px-2 py-1 text-center focus:ring focus:ring-blue-200"
                >

            </div>
            @endforeach

        </div>

        <div class="mt-6 flex justify-between items-center">

            <a href="{{ route('warehouse.index') }}"
               class="text-gray-600 hover:underline">
                ← Kembali
            </a>

            <button type="submit"
                class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg shadow font-semibold">
                Convert Sekarang
            </button>

        </div>

        </form>

    </div>

</div>
</div>

</x-app-layout>