<x-app-layout>

<style>

@media print {

    /* Semua teks jadi biru */
    body, table, th, td, div, span {
        color:#1d4ed8 !important;
    }

    /* Hilangkan warna abu bawaan tailwind */
    .text-gray-600,
    .text-gray-500,
    .text-gray-700,
    .text-gray-800 {
        color:#1d4ed8 !important;
    }

    /* Link juga biru */
    a {
        color:#1d4ed8 !important;
        text-decoration:none;
    }

    /* Angka negatif / status merah */
    .text-red-600,
    .bg-red-600 {
        color:#dc2626 !important;
        background:none !important;
        font-weight:bold;
    }

    /* Status kurang setor */
    .status-minus {
        color:#dc2626 !important;
        font-weight:bold;
    }

    /* status lunas */
    .status-ok {
        color:#1d4ed8 !important;
        font-weight:bold;
    }

    /* Hilangkan shadow agar print bersih */
    .shadow {
        box-shadow:none !important;
    }

    /* Hilangkan background tailwind */
    .bg-white,
    .bg-gray-100,
    .bg-blue-50 {
        background:none !important;
    }

}

</style>

<div class="py-6 px-6">

    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-xl font-bold">
                Rekap Setoran Sales
            </h2>
            <div class="text-sm text-gray-600">
                {{ $settlement->user->name }} -
                {{ $settlement->settlement_date->format('d M Y') }}
            </div>

            <div class="print-only text-xs text-gray-600 mt-1">
                Waktu Cetak: {{ now()->format('d M Y H:i') }}
            </div>

            <div class="print-only text-xs font-semibold mt-1">
                Status Settlement:
                @if($settlement->status === 'draft')
                    BELUM SETOR
                @else
                    @php
                        if ($difference == 0) {
                            echo 'LUNAS';
                        } elseif ($difference < 0) {
                            echo 'KURANG SETOR';
                        } else {
                            echo 'LEBIH SETOR';
                        }
                    @endphp
                @endif
            </div>
        </div>

        <div class="flex gap-2 no-print">
            <button onclick="window.print()"
                class="bg-blue-600 text-white px-4 py-2 rounded shadow">
                Print Rekap
            </button>

            @php
                $isAdmin = auth()->user()->role === 'admin';
            @endphp

            @if($isAdmin && $settlement->status === 'closed')
                <form method="POST" action="{{ route('sales-settlements.reopen', $settlement->id) }}"
                      onsubmit="return confirm('Yakin ingin membuka kembali settlement ini?')">
                    @csrf
                    <button type="submit"
                        class="bg-red-600 text-white px-4 py-2 rounded shadow">
                        Reopen Settlement
                    </button>
                </form>
            @endif
        </div>
    </div>


    @if($settlement->status === 'draft')

        <div class="bg-yellow-500 text-white text-center font-bold py-3 rounded mb-4 text-lg no-print">
            STATUS: BELUM SETOR
        </div>

    @else

        @php
            if ($difference == 0) {
                $statusText = 'LUNAS';
                $statusClass = 'bg-green-600';
            } elseif ($difference < 0) {
                $statusText = 'KURANG SETOR';
                $statusClass = 'bg-red-600';
            } else {
                $statusText = 'LEBIH SETOR';
                $statusClass = 'bg-yellow-500';
            }
        @endphp

        <div class="{{ $statusClass }} text-white text-center font-bold py-3 rounded mb-6 text-lg no-print">
            STATUS: {{ $statusText }}
        </div>

    @endif


    {{-- DETAIL PER TOKO --}}
    <div class="bg-white shadow rounded mb-6 p-4">
        <h3 class="font-semibold mb-4">Detail Per Toko</h3>
        <table class="w-full border">
            <thead class="bg-blue-50">
                <tr>
                    <th class="p-2 text-left">Nama Toko</th>
                    <th class="p-2 text-right">Total Penjualan</th>
                    <th class="p-2 text-right">Biaya Admin</th>
                    <th class="p-2 text-right">Cash</th>
                    <th class="p-2 text-right">Piutang Toko</th>
                    <th class="p-2 text-right">Fee</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @foreach($storeDetails as $store)
                <tr>
                    <td class="p-2">
                        @if($store->visit_id)
                            <a href="{{ route('visits.show', $store->visit_id) }}"
                               class="text-blue-600 font-semibold hover:underline">
                                {{ $store->store_name }}
                            </a>
                        @else
                            <span class="font-semibold">
                                {{ $store->store_name }}
                            </span>
                        @endif
                    </td>
                    <td class="p-2 text-right">Rp {{ number_format($store->total_penjualan,0,',','.') }}</td>
                    <td class="p-2 text-right">Rp {{ number_format($store->admin_fee ?? 0,0,',','.') }}</td>
                    <td class="p-2 text-right">Rp {{ number_format($store->total_cash,0,',','.') }}</td>
                    <td class="p-2 text-right">Rp {{ number_format($store->total_consignment,0,',','.') }}</td>
                    <td class="p-2 text-right">Rp {{ number_format($store->total_fee,0,',','.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>


    {{-- DETAIL PRODUK VISIT --}}
    <div class="bg-white shadow rounded mb-6 p-4">
        <h3 class="font-semibold mb-4">Detail Produk (Visit Toko)</h3>
        <table class="w-full border">
            <thead class="bg-gray-100">
                <tr>
                    <th class="p-2 text-left">Produk</th>
                    <th class="p-2 text-right">Qty</th>
                    <th class="p-2 text-right">Revenue</th>
                    <th class="p-2 text-right">Fee</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @foreach($productDetails as $item)
                <tr>
                    <td class="p-2">{{ $item->product_name }}</td>
                    <td class="p-2 text-right">{{ $item->total_qty }}</td>
                    <td class="p-2 text-right">Rp {{ number_format($item->total_revenue,0,',','.') }}</td>
                    <td class="p-2 text-right">Rp {{ number_format($item->total_fee,0,',','.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>


    {{-- DETAIL PRODUK DIRECT --}}
    @if(isset($cashSaleProductDetails) && $cashSaleProductDetails->count())
    <div class="bg-white shadow rounded mb-6 p-4">
        <h3 class="font-semibold mb-4">Detail Produk (Penjualan Tunai Langsung)</h3>
        <table class="w-full border">
            <thead class="bg-gray-100">
                <tr>
                    <th class="p-2 text-left">Produk</th>
                    <th class="p-2 text-right">Qty</th>
                    <th class="p-2 text-right">Revenue</th>
                    <th class="p-2 text-right">Fee</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @foreach($cashSaleProductDetails as $item)
                <tr>
                    <td class="p-2">{{ $item->product_name }}</td>
                    <td class="p-2 text-right">{{ $item->total_qty }}</td>
                    <td class="p-2 text-right">Rp {{ number_format($item->total_revenue,0,',','.') }}</td>
                    <td class="p-2 text-right">Rp {{ number_format($item->total_fee,0,',','.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- BIAYA OPERASIONAL --}}
<div class="bg-white shadow rounded mb-6 p-4 no-print">
    <h3 class="font-semibold mb-4">Biaya Operasional</h3>

    {{-- LIST BIAYA --}}
    @if($costDetails->count())
        <table class="w-full border mb-4">
            <thead class="bg-gray-100">
                <tr>
                    <th class="p-2 text-left">Jenis</th>
                    <th class="p-2 text-left">Keterangan</th>
                    <th class="p-2 text-right">Nominal</th>
                    <th class="p-2 text-center">Aksi</th>
                </tr>
            </thead>

            <tbody class="divide-y">
            @foreach($costDetails as $cost)

<tr>

<td class="p-2 capitalize">
{{ $cost->jenis_biaya }}
</td>

<td class="p-2">
{{ $cost->keterangan ?? '-' }}
</td>

<td class="p-2 text-right">
Rp {{ number_format($cost->nominal,0,',','.') }}
</td>

<td class="p-2 text-center">

@if($settlement->status === 'draft')

<form method="POST"
action="{{ route('sales.settlements.costs.destroy',$cost->id) }}"
class="inline"
onsubmit="return confirm('Hapus biaya ini?')">

@csrf
@method('DELETE')

<button class="bg-red-600 text-white px-2 py-1 rounded text-xs">
Hapus
</button>

</form>

@else

<span class="text-gray-400 text-xs">
Locked
</span>

@endif

</td>

</tr>

@endforeach
            </tbody>
        </table>
    @endif

    {{-- FORM INPUT --}}
    @php
        $canAddCost = false;

        if ($settlement->status === 'draft') {
            $canAddCost = true;
        }

        if ($settlement->status === 'closed' && auth()->user()->role === 'admin') {
            $canAddCost = true;
        }
    @endphp

    @if($canAddCost)
        <form method="POST"
              action="{{ route('sales.settlements.costs.store', $settlement->id) }}"
              class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end">

            @csrf

            <div>
                <label class="text-sm font-medium">Jenis Biaya</label>
                <select name="jenis_biaya"
                        class="w-full border rounded px-2 py-1"
                        required>
                    <option value="">-- Pilih --</option>
                    <option value="bensin">Bensin</option>
                    <option value="parkir">Parkir</option>
                    <option value="makan">Makan</option>
                    <option value="tol">Tol</option>
                    <option value="lain_lain">Lain-lain</option>
                </select>
            </div>

            <div>
                <label class="text-sm font-medium">Nominal</label>
                <input type="number"
                       name="nominal"
                       step="0.01"
                       min="0"
                       class="w-full border rounded px-2 py-1"
                       required>
            </div>

            <div>
                <label class="text-sm font-medium">Keterangan</label>
                <input type="text"
                       name="keterangan"
                       class="w-full border rounded px-2 py-1">
            </div>

            <div>
                <button type="submit"
                        class="bg-green-600 text-white px-4 py-2 rounded shadow w-full">
                    Tambah
                </button>
            </div>

        </form>
    @endif
</div>
    {{-- RINGKASAN --}}
    <div class="bg-white shadow rounded mb-6 p-4">
        <h3 class="font-semibold mb-4">Ringkasan</h3>
        <table class="w-full border">
            <tbody class="divide-y">
                <tr>
                    <td class="p-2 font-medium">Penjualan Tunai (Visit/Kunjungan)</td>
                    <td class="p-2 text-right">Rp {{ number_format($cashVisitGross,0,',','.') }}</td>
                </tr>
                <tr>
                    <td class="p-2 font-medium">Penjualan Tunai Langsung</td>
                    <td class="p-2 text-right">Rp {{ number_format($cashSaleDirect,0,',','.') }}</td>
                </tr>
                <tr>
                    <td class="p-2 font-medium">Total Piutang Toko</td>
                    <td class="p-2 text-right">Rp {{ number_format($consignmentSales,0,',','.') }}</td>
                </tr>
                <tr>
                    <td class="p-2 font-medium">Pembayaran Piutang Toko</td>
                    <td class="p-2 text-right">Rp {{ number_format($receivablePayments,0,',','.') }}</td>
                </tr>
                <tr>
                    <td class="p-2 font-medium">Total Biaya Admin</td>
                    <td class="p-2 text-right">
                    Rp {{ number_format($adminFee,0,',','.') }}
                </td>
                </tr>
                <tr class="font-semibold">
                    <td class="p-2">Cash Bersih Visit</td>
                    <td class="p-2 text-right">
                    Rp {{ number_format($cashSales,0,',','.') }}
                    </td>
                    </tr>

                    <tr>
                    <td class="p-2 font-medium">Total Biaya Operasional</td>
                    <td class="p-2 text-right">
                    Rp {{ number_format($totalCost,0,',','.') }}
                    </td>
                    </tr>
                <tr class="bg-blue-50 font-semibold">
                    <td class="p-2">Total Seharusnya Setor</td>
                    <td class="p-2 text-right">
                        Rp {{ number_format($expected,0,',','.') }}
                    </td>
                </tr>
                <tr>
                    <td class="p-2 font-medium">Total Diterima</td>
                    <td class="p-2 text-right">
                        Rp {{ number_format($settlement->actual_amount,0,',','.') }}
                    </td>
                </tr>
                <tr class="font-bold {{ $difference < 0 ? 'status-minus' : 'status-ok' }}">
                    <td class="p-2">Selisih</td>
                    <td class="p-2 text-right">
                        Rp {{ number_format($difference,0,',','.') }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

</div>

</x-app-layout>