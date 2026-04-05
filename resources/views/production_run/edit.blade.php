<x-app-layout>
    <div class="p-6 max-w-xl mx-auto">

        <h2 class="text-xl font-semibold mb-6">Edit Produksi</h2>

        @if ($errors->any())
            <div class="mb-4 p-3 bg-red-100 text-red-700 rounded">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('production-run.update', $run->id) }}">
            @csrf
            @method('PUT')

            <!-- PRODUCT (HIDDEN) -->
            <input type="hidden" name="product_id" value="{{ $run->product_id }}">

            <!-- PRODUK (READ ONLY BIAR USER TAU) -->
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Produk</label>
                <input type="text"
                       value="{{ $run->product->name }}"
                       class="w-full border rounded px-3 py-2 bg-gray-100"
                       readonly>
            </div>

            <!-- OUTPUT -->
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Hasil Produksi (gram)</label>
                <input type="number"
                       name="output_gram"
                       value="{{ $run->output_gram }}"
                       class="w-full border rounded px-3 py-2 focus:ring focus:ring-blue-200"
                       required>
            </div>

            <!-- PERSEN -->
            <div class="mb-6">
                <label class="block text-sm font-medium mb-1">Persentase (%)</label>
                <input type="number"
                       name="labor_percentage"
                       value="{{ $run->labor_percentage * 100 }}"
                       class="w-full border rounded px-3 py-2 focus:ring focus:ring-blue-200"
                       required>
            </div>

            <!-- BUTTON -->
            <div class="flex gap-2">
                <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                    Update
                </button>

                <a href="{{ route('production-run.index') }}"
                   class="bg-gray-400 hover:bg-gray-500 text-white px-4 py-2 rounded">
                    Batal
                </a>
            </div>

        </form>

    </div>
</x-app-layout>