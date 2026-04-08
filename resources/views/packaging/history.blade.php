<x-app-layout>

<div class="p-6 max-w-6xl mx-auto">

    <h2 class="text-xl font-bold mb-4">History Stok Kemasan</h2>

    <table class="w-full text-sm border border-gray-200">
        <thead>
            <tr class="bg-gray-100">
                <th class="p-2 border">Tanggal</th>
                <th class="p-2 border">Produk</th>
                <th class="p-2 border">Varian</th>
                <th class="p-2 border">Jenis</th>
                <th class="p-2 border">Qty</th>
            </tr>
        </thead>
        <tbody>
            @foreach($histories as $h)
                <tr class="border-t hover:bg-gray-50">

                    <td class="p-2 border">
                        {{ \Carbon\Carbon::parse($h->created_at)->format('d-m-Y H:i') }}
                    </td>

                    <td class="p-2 border">{{ $h->product_name }}</td>

                    <td class="p-2 border text-red-600">
                        {{ $h->variant_name }}
                    </td>

                    <td class="p-2 border">
                        @if($h->type == 'in')
                            <span class="text-green-600 font-semibold">Bertambah</span>
                        @elseif($h->type == 'out')
                            @if($h->reference_type == 'production_batch')
                                <span class="text-red-600 font-semibold">Produksi</span>
                            @elseif($h->reference_type == 'damage')
                                <span class="text-red-600 font-semibold">Rusak</span>
                            @else
                                <span class="text-red-600 font-semibold">Keluar</span>
                            @endif
                        @elseif($h->type == 'return')
                            <span class="text-blue-600 font-semibold">Return</span>
                        @elseif($h->type == 'adjustment')
                            <span class="text-yellow-500 font-semibold">Penyesuaian</span>
                        @endif
                    </td>

                    <td class="p-2 border font-semibold">
                        @if($h->type == 'out')
                        -{{ abs($h->quantity) }}
                        @elseif($h->type == 'in' || $h->type == 'return')
                        +{{ abs($h->quantity) }}
                        @elseif($h->type == 'adjustment')
                        {{ $h->quantity > 0 ? '+' : '' }}{{ $h->quantity }}
                        @endif
                    </td>

                </tr>
            @endforeach
        </tbody>
    </table>

</div>

</x-app-layout>