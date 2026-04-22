<x-app-layout>

<div class="p-6 max-w-5xl mx-auto">

    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-bold">
            Data Customer
        </h2>

        <a href="/online-orders"
           class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded">
            ← Kembali
        </a>
    </div>

    <div class="bg-white rounded shadow overflow-x-auto">

        <table class="w-full text-sm border">
            <thead>
                <tr class="bg-gray-100">
                    <th class="p-2 border">Nama</th>
                    <th class="p-2 border">No WA</th>
                    <th class="p-2 border">Paket Terakhir</th>
                    <th class="p-2 border">Tanggal Order</th>
                </tr>
            </thead>

            <tbody>

                @forelse($customers as $c)

                <tr class="hover:bg-gray-50">
                    <td class="p-2 border font-semibold">
                        {{ $c->name }}
                    </td>

                    <td class="p-2 border">
                        {{ $c->phone }}
                    </td>

                    <td class="p-2 border text-blue-600">
                        {{ $c->last_package ?? '-' }}
                    </td>

                    <td class="p-2 border text-gray-500">
                        {{ $c->last_order_date 
                            ? \Carbon\Carbon::parse($c->last_order_date)->format('d M Y') 
                            : '-' 
                        }}
                    </td>
                </tr>

                @empty

                <tr>
                    <td colspan="4" class="text-center p-4 text-gray-500">
                        Tidak ada data customer
                    </td>
                </tr>

                @endforelse

            </tbody>
        </table>

    </div>

</div>

</x-app-layout>