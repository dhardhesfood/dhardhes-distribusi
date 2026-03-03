<x-app-layout>
    <div class="p-6 max-w-6xl mx-auto">

        <h2 class="text-xl font-bold mb-6">
            Stock Opname - {{ $store->name }}
        </h2>

        <form method="POST"
              action="{{ route('stock-opnames.store', $store->id) }}">
            @csrf

            <div class="mb-6">
                <label class="block text-sm font-medium mb-1">
                    Catatan (Opsional)
                </label>
                <textarea name="notes"
                          class="w-full border rounded p-2 text-sm"
                          rows="3"></textarea>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full border text-sm">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="border p-2 text-left">Produk</th>
                            <th class="border p-2">Stok Sistem</th>
                            <th class="border p-2">Stok Aktual</th>
                        </tr>
                    </thead>
                    <tbody>

                        @foreach($products as $product)

                            @php
                                $systemStock = \App\Models\StoreStockMovement::getStoreProductStock(
                                    $store->id,
                                    $product->id
                                );
                            @endphp

                            <tr>
                                <td class="border p-2">
                                    {{ $product->name }}
                                </td>

                                <td class="border p-2 text-center">
                                    {{ $systemStock }}
                                </td>

                                <td class="border p-2">
                                    <input type="number"
                                           name="actual_stock[{{ $product->id }}]"
                                           value="{{ $systemStock }}"
                                           min="0"
                                           class="w-full border rounded p-1 text-center">
                                </td>
                            </tr>

                        @endforeach

                    </tbody>
                </table>
            </div>

            <div class="mt-6 flex gap-3">

                <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded shadow">
                    Simpan Opname
                </button>

                <a href="{{ route('stores.index') }}"
                   class="bg-gray-500 hover:bg-gray-600 text-white px-5 py-2 rounded shadow">
                    Batal
                </a>

            </div>

        </form>

    </div>
</x-app-layout>