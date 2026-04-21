<x-app-layout>

<div class="max-w-7xl mx-auto px-4 py-6">

    {{-- HEADER --}}
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-bold text-gray-800">
            📦 History Stok Online
        </h2>

        <a href="{{ route('warehouse.index') }}"
           class="bg-red-700 hover:bg-gray-800 text-white px-4 py-2 rounded shadow-sm">
            ← Kembali
        </a>
    </div>

    {{-- CARD --}}
    <div class="bg-white shadow rounded-lg overflow-hidden">

        <div class="overflow-x-auto">

            <table class="min-w-full text-sm text-gray-700">
                <thead class="bg-gray-800 text-white">
                    <tr>
                        <th class="px-4 py-3 text-left">Tanggal</th>
                        <th class="px-4 py-3 text-left">Produk</th>
                        <th class="px-4 py-3 text-center">Qty</th>
                        <th class="px-4 py-3 text-center">Tipe</th>
                        <th class="px-4 py-3 text-left">Keterangan</th>
                        <th class="px-4 py-3 text-left">User</th>
                    </tr>
                </thead>

                <tbody class="divide-y">

                    @foreach($movements as $m)

                        @php
    $qty = $m->quantity;

    // ✅ MASUK ONLINE
    if ($m->reference_type == 'convert_to_online') {
        $qty = +$qty;
    }

    // ✅ KELUAR KE GUDANG
    if ($m->reference_type == 'convert_to_offline') {
        $qty = -$qty;
    }

@endphp

                        <tr class="hover:bg-gray-50">

                            {{-- TANGGAL --}}
                            <td class="px-4 py-2">
                                {{ \Carbon\Carbon::parse($m->created_at)->format('d M Y H:i') }}
                            </td>

                            {{-- PRODUK --}}
                            <td class="px-4 py-2 font-semibold">
                                {{ $m->product_name ?? '-' }}
                            </td>

                            {{-- QTY --}}
                            <td class="px-4 py-2 text-center font-bold
                                {{ $qty > 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ $qty > 0 ? '+' : '' }}{{ $qty }}
                            </td>

                            {{-- TIPE --}}
                            <td class="px-4 py-2 text-center">
                                @if($m->reference_type == 'convert_to_online')

                                <span class="bg-red-100 text-red-700 px-2 py-1 rounded text-xs">
                                        Pindah dari Offline ke Stok Online
                                        
                                    </span>

                                    @elseif($m->reference_type == 'online_order_done')
                                   <span class="bg-yellow-100 text-yellow-700 px-2 py-1 rounded text-xs">
                                   Order Online Terkirim ke {{ $m->customer_name ?? '-' }}
                                   </span>
                               
                            
                                @elseif($m->reference_type == 'convert_to_offline')
                                    <span class="bg-green-100 text-green-700 px-2 py-1 rounded text-xs">
                                        Kembali ke Stok Offline
                                    </span>
                                @endif

                                
                            
                            </td>

                            {{-- KETERANGAN --}}
                            <td class="px-4 py-2">
                                @if($m->reference_type == 'online_order_done')
                                online order done (Terkirim ke {{ $m->customer_name ?? '-' }})
                                 @else
                                {{ $m->reference_type }}
                                @endif
                            </td>

                            {{-- USER --}}
                            <td class="px-4 py-2">
                                {{ $m->created_by_name ?? '-' }}
                            </td>

                        </tr>

                    @endforeach

                </tbody>
            </table>

        </div>

        {{-- PAGINATION --}}
        <div class="p-4">
            {{ $movements->links() }}
        </div>

    </div>

</div>

</x-app-layout>