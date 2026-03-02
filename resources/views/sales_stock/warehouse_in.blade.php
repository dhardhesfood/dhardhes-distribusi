<x-app-layout>
    <div class="p-6 max-w-xl mx-auto">

        <h2 class="text-2xl font-bold mb-6">
            Tambah Stok Sales (Warehouse → Sales)
        </h2>

        @if(session('success'))
            <div class="mb-4 p-3 bg-green-100 text-green-700 rounded">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 p-3 bg-red-100 text-red-700 rounded">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white shadow rounded border p-6">

            <form method="POST" action="{{ route('sales.stock.warehouse_in.store') }}">
                @csrf

                <div class="mb-4">
                    <label class="block font-semibold mb-1">
                        Produk
                    </label>

                    <select name="product_id"
                            class="w-full border p-2 rounded"
                            required>
                        <option value="">-- Pilih Produk --</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}">
                                {{ $product->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block font-semibold mb-1">
                        Jumlah Stok Masuk
                    </label>

                    <input type="number"
                           name="quantity"
                           min="1"
                           class="w-full border p-2 rounded"
                           required>
                </div>

                <div class="mb-4">
                    <label class="block font-semibold mb-1">
                        Catatan (Opsional)
                    </label>

                    <textarea name="notes"
                              rows="3"
                              class="w-full border p-2 rounded"></textarea>
                </div>

                <button type="submit"
                        class="w-full bg-green-600 text-white p-3 rounded font-semibold shadow">
                    Simpan
                </button>

            </form>

        </div>

        <div class="mt-6">
            <a href="{{ route('sales.stock') }}"
               class="text-blue-600 hover:underline">
                ← Kembali ke Dashboard
            </a>
        </div>

    </div>
</x-app-layout>
