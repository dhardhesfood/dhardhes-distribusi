<x-app-layout>
    <div class="p-6 max-w-5xl mx-auto">

        <h2 class="text-xl font-bold mb-4">Detail Kunjungan</h2>

        @php
            $statusText = [
                'draft'     => 'Draft',
                'completed' => 'Selesai (Menunggu Approve)',
                'approved'  => 'Disetujui'
            ];
        @endphp

        <div class="mb-6 text-sm flex justify-between items-start">

            <div>
                <p>
                    Status:
                    <strong class="capitalize">
                        {{ $statusText[$visit->status] ?? $visit->status }}
                    </strong>
                </p>
                <p>Tanggal: {{ $visit->visit_date }}</p>
                <p>Next Visit: {{ $visit->next_visit_date ?? '-' }}</p>
            </div>

            {{-- TOMBOL APPROVE --}}
            @if(auth()->user()->role === 'admin' && $visit->status === 'completed')
                <form method="POST" action="{{ route('visits.approve', $visit->id) }}">
                    @csrf
                    <button type="submit"
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded shadow">
                        Approve Visit
                    </button>
                </form>
            @endif

        </div>

        {{-- ================= TOMBOL NAVIGASI ================= --}}
        <div class="mb-6 flex gap-3">
            <a href="{{ route('dashboard') }}"
               class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded shadow text-sm">
                Dashboard
            </a>

            <a href="{{ route('visits.index') }}"
               class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow text-sm">
                Daftar Kunjungan
            </a>
        </div>

        {{-- ================= AUTO REDIRECT ================= --}}
        @if($visit->status === 'completed')
            <div class="mb-6 p-4 bg-green-100 border border-green-300 rounded text-sm text-green-700">
                Visit berhasil diproses. Anda akan diarahkan ke Daftar Kunjungan dalam 5 detik.
            </div>

            <script>
                setTimeout(function () {
                    window.location.href = "{{ route('visits.index') }}";
                }, 5000);
            </script>
        @endif


        {{-- ================= DETAIL PRODUK ================= --}}
        <table class="w-full border border-gray-300 text-sm">
            <thead>
                <tr class="bg-gray-100">
                    <th class="border p-2 text-left">Produk</th>
                    <th class="border p-2">Stok Awal</th>
                    <th class="border p-2">Sisa Stok</th>
                    <th class="border p-2">Terjual</th>
                    <th class="border p-2">Penambahan</th>
                    <th class="border p-2">Pengurangan</th>
                    <th class="border p-2">Bonus</th>
                </tr>
            </thead>
            <tbody>
                @forelse($visit->items as $item)
                <tr class="text-center">
                    <td class="border p-2 text-left">
                        {{ $item->product->name }}
                    </td>

                    <td class="border p-2">
                        {{ $item->initial_stock }}
                    </td>

                    <td class="border p-2">
                        {{ $item->return_qty }}
                    </td>

                    <td class="border p-2 font-semibold text-blue-600">
                        {{ $item->sold_qty }}
                    </td>

                    <td class="border p-2 text-green-600">
                        {{ $item->new_delivery_qty }}
                    </td>

                    <td class="border p-2 text-red-600">
                        {{ $item->stock_reduction_qty }}
                    </td>

                    <td class="border p-2">
                        {{ optional($visit->bonuses)
                            ->where('product_id', $item->product_id)
                            ->sum('qty') }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="border p-2 text-center text-gray-500">
                        Tidak ada data produk
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>


        {{-- ================= TOTAL ================= --}}
        <div class="mt-8 border-t pt-4 text-sm">

            <div class="flex justify-between">
                <span>Total Fee</span>
                <span>
                    Rp {{ number_format($visit->salesTransaction->total_fee ?? 0, 0, ',', '.') }}
                </span>
            </div>

            <div class="flex justify-between font-bold text-lg mt-2">
                <span>Total Tagihan</span>
                <span>
                    Rp {{ number_format($visit->salesTransaction->total_amount ?? 0, 0, ',', '.') }}
                </span>
            </div>

        </div>

        {{-- ================= HISTORY MOVEMENT ================= --}}
        <hr class="my-8">

        <h3 class="text-lg font-bold mb-4">History Pergerakan Stok</h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">

            {{-- STORE STOCK --}}
            <div>
                <h4 class="font-semibold mb-2">Stok Toko</h4>

                <table class="w-full border">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="border p-2 text-left">Produk</th>
                            <th class="border p-2">Tipe</th>
                            <th class="border p-2">Qty</th>
                            <th class="border p-2">Waktu</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($visit->storeMovements as $movement)
                        <tr class="text-center">
                            <td class="border p-2 text-left">
                                {{ $movement->product->name ?? '-' }}
                            </td>
                            <td class="border p-2">
                                {{ $movement->type }}
                            </td>
                            <td class="border p-2">
                                {{ $movement->quantity }}
                            </td>
                            <td class="border p-2">
                                {{ $movement->created_at }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="border p-2 text-center text-gray-500">
                                Tidak ada pergerakan
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- SALES STOCK --}}
            <div>
                <h4 class="font-semibold mb-2">Stok Sales</h4>

                <table class="w-full border">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="border p-2 text-left">Produk</th>
                            <th class="border p-2">Tipe</th>
                            <th class="border p-2">Qty</th>
                            <th class="border p-2">Waktu</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($visit->salesMovements as $movement)
                        <tr class="text-center">
                            <td class="border p-2 text-left">
                                {{ $movement->product->name ?? '-' }}
                            </td>
                            <td class="border p-2">
                                {{ $movement->type }}
                            </td>
                            <td class="border p-2">
                                {{ $movement->quantity }}
                            </td>
                            <td class="border p-2">
                                {{ $movement->created_at }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="border p-2 text-center text-gray-500">
                                Tidak ada pergerakan
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>

    </div>
</x-app-layout>