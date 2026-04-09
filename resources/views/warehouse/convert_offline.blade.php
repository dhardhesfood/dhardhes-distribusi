<x-app-layout>

<div class="py-8">
<div class="max-w-3xl mx-auto sm:px-6 lg:px-8">

    <div class="bg-white shadow-md rounded-xl p-6">

        <h2 class="text-xl font-bold mb-4">
            Convert ke Offline
        </h2>

        <div class="mb-4">
            <div class="text-sm text-gray-500">Produk</div>
            <div class="text-lg font-semibold">
                {{ $product->name }}
            </div>
        </div>

        <div class="mb-6">
            <div class="text-sm text-gray-500">Total Stok Online</div>
            <div class="text-2xl font-bold text-red-600">
                {{ number_format($total) }}
            </div>
        </div>

        <div class="space-y-2 mb-6">
            @foreach($variants as $v)
                <div class="flex justify-between border rounded p-2">
                    <span>{{ $v->name }}</span>
                    <span class="font-semibold">{{ $v->stock_qty }}</span>
                </div>
            @endforeach
        </div>

        <form method="POST" action="{{ route('warehouse.convert.offline', $product->id) }}">
            @csrf

            <div class="flex justify-between items-center">

                <a href="{{ route('warehouse.index') }}"
                   class="text-gray-600 hover:underline">
                    ← Kembali
                </a>

                <button type="submit"
                    onclick="return confirm('Yakin ingin convert ke offline?')"
                    class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg shadow font-semibold">
                    Convert ke Offline
                </button>

            </div>

        </form>

    </div>

</div>
</div>

</x-app-layout>