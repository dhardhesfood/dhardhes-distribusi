<x-app-layout>

<div class="py-6 px-4 sm:px-6 max-w-6xl mx-auto">

    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-6">
        <div>
            <h2 class="text-lg sm:text-xl font-bold">
                Edit Fisik Session Stok Sales
            </h2>
            <div class="text-xs sm:text-sm text-gray-600 mt-2">
                Sales: {{ $session->user->name }} <br>
                Mulai: {{ $session->start_date }}
            </div>
        </div>

        <div>
            <a href="{{ route('sales-stock-sessions.show', $session->id) }}"
               class="px-3 py-2 bg-gray-500 text-white rounded hover:bg-gray-600 text-xs sm:text-sm">
                Kembali
            </a>
        </div>
    </div>

    <div class="bg-white shadow rounded p-3 sm:p-4">

        <form method="POST" action="{{ route('sales-stock-sessions.update', $session->id) }}">
            @csrf
            @method('PUT')

            <div class="overflow-x-auto">
                <table class="w-full border text-xs sm:text-sm">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="p-2 border text-left">Produk</th>
                            <th class="p-2 border text-right">Seharusnya</th>
                            <th class="p-2 border text-right">Fisik (Edit)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($session->items as $item)
                        <tr>
                            <td class="p-2 border">
                                {{ $item->product->name }}
                            </td>

                            <td class="p-2 border text-right">
                                {{ $item->system_remaining_qty ?? 0 }}
                            </td>

                            <td class="p-2 border text-right">
                                <input type="number"
                                       name="physical_qty[{{ $item->product_id }}]"
                                       value="{{ $item->physical_remaining_qty ?? 0 }}"
                                       class="w-20 sm:w-28 border rounded px-2 py-1 text-right"
                                       min="0">
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-6 flex justify-end gap-2">
                <a href="{{ route('sales-stock-sessions.show', $session->id) }}"
                   class="px-4 py-2 bg-gray-400 text-white rounded hover:bg-gray-500 text-xs sm:text-sm">
                    Batal
                </a>

                <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-xs sm:text-sm">
                    Simpan Perubahan
                </button>
            </div>
        </form>

    </div>

</div>

</x-app-layout>
