<x-app-layout>
    <div class="p-6 max-w-4xl mx-auto">

        <div class="mb-6">
            <h2 class="text-2xl font-bold">
                Daftar Harga Warehouse
            </h2>
            <p class="text-sm text-gray-500">
                Data harga dasar gudang per produk
            </p>
        </div>

        @if(session('success'))
            <div class="mb-4 p-3 bg-green-100 text-green-700 rounded">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white shadow rounded border overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="border p-3 text-left">Produk</th>
                        <th class="border p-3 text-right">Warehouse Price</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stocks as $row)
                        <tr class="hover:bg-gray-50">
                            <td class="border p-3 font-medium">
                                {{ $row['name'] }}
                            </td>

                            <td class="border p-3 text-right font-semibold">
                                Rp {{ number_format($row['warehouse_price'], 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="border p-4 text-center text-gray-500">
                                Belum ada data produk.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
</x-app-layout>