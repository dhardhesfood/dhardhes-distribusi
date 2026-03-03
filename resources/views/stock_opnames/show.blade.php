<x-app-layout>
    <div class="p-6 max-w-5xl mx-auto">

        <h2 class="text-xl font-bold mb-6">
            Detail Stock Opname
        </h2>

        <div class="mb-4 text-sm">
            <p><strong>ID:</strong> {{ $stockOpname->id }}</p>
            <p><strong>Toko:</strong> {{ $stockOpname->store->name ?? '-' }}</p>
            <p><strong>Tanggal:</strong> {{ $stockOpname->created_at }}</p>
            <p><strong>Dibuat oleh:</strong> {{ $stockOpname->creator->name ?? '-' }}</p>
        </div>

        <table class="w-full border text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="border p-2 text-left">Produk</th>
                    <th class="border p-2">System</th>
                    <th class="border p-2">Actual</th>
                    <th class="border p-2">Selisih</th>
                </tr>
            </thead>
            <tbody>
                @foreach($stockOpname->items as $item)
                <tr class="text-center">
                    <td class="border p-2 text-left">
                        {{ $item->product->name }}
                    </td>
                    <td class="border p-2">
                        {{ $item->system_stock }}
                    </td>
                    <td class="border p-2">
                        {{ $item->actual_stock }}
                    </td>
                    <td class="border p-2 font-semibold 
                        @if($item->difference > 0) text-green-600 
                        @elseif($item->difference < 0) text-red-600 
                        @endif">
                        {{ $item->difference }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

    </div>
</x-app-layout>