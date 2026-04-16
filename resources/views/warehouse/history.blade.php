<x-app-layout>

<div class="max-w-7xl mx-auto px-4 py-6">

    {{-- HEADER + ACTION --}}
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-bold text-gray-800">
            📦 History Stok Gudang
        </h2>

        <div class="flex gap-2">

            <a href="{{ route('warehouse.index') }}"
               class="bg-red-700 hover:bg-gray-800 text-white px-4 py-2 rounded shadow-sm">
                ← Kembali
            </a>
        </div>
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
                        <th class="px-4 py-3 text-left">Catatan</th>
                        <th class="px-4 py-3 text-left">User</th>
                    </tr>
                </thead>

                <tbody class="divide-y">

                    @foreach($movements as $m)
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
                            @php
                            $qty = $m->quantity;

                            // balik arah untuk keluar
                            if ($m->type == 'warehouse_out') {
                            $qty = -$qty;
                            }
                            @endphp

                            <td class="px-4 py-2 text-center font-bold
                            {{ $qty > 0 ? 'text-green-600' : 'text-red-600' }}">
                            {{ $qty > 0 ? '+' : '' }}{{ $qty }}
                            </td>

                            {{-- TIPE --}}
                            <td class="px-4 py-2 text-center">
                                @switch($m->type)
                                    @case('warehouse_in')
                                        <span class="bg-green-100 text-green-700 px-2 py-1 rounded text-xs">Masuk</span>
                                    @break

                                    @case('warehouse_out')
                                        <span class="bg-red-100 text-red-700 px-2 py-1 rounded text-xs">Keluar</span>
                                    @break

                                    @case('adjustment')
                                        <span class="bg-yellow-100 text-yellow-700 px-2 py-1 rounded text-xs">Adjust</span>
                                    @break

                                    @case('send_to_store')
                                        <span class="bg-blue-100 text-blue-700 px-2 py-1 rounded text-xs">Kirim</span>
                                    @break

                                    @case('return_from_store')
                                        <span class="bg-cyan-100 text-cyan-700 px-2 py-1 rounded text-xs">Retur</span>
                                    @break

                                    @case('bonus')
                                        <span class="bg-gray-200 text-gray-700 px-2 py-1 rounded text-xs">Bonus</span>
                                    @break

                                    @case('damage')
                                        <span class="bg-black text-white px-2 py-1 rounded text-xs">Rusak</span>
                                    @break

                                    @default
                                        <span class="bg-gray-100 px-2 py-1 rounded text-xs">
                                            {{ $m->type }}
                                        </span>
                                @endswitch
                            </td>

                            {{-- CATATAN --}}
                            <td class="px-4 py-2">
                                {{ $m->notes ?? '-' }}
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