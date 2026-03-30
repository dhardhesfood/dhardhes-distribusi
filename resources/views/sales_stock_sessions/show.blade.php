<x-app-layout>

<div class="py-6 px-4 sm:px-6 max-w-6xl mx-auto">

    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-6">
        <div>
            <h2 class="text-lg sm:text-xl font-bold">
                Detail Session Stok Sales
            </h2>
            <div class="text-xs sm:text-sm text-gray-600 mt-2">
                Sales: {{ $session->user->name }} <br>
                Mulai: 
                @if($session->created_at)
                {{ $session->created_at->format('d-m-Y H:i') }}
                @else
                {{ $session->start_date }}
                @endif
            <br>
                Status:
                @if($session->status === 'open')
                    <span class="px-2 py-1 bg-yellow-400 text-white rounded text-[10px] sm:text-xs">OPEN</span>
                @elseif($session->status === 'minus')
                    <span class="px-2 py-1 bg-red-600 text-white rounded text-[10px] sm:text-xs">STOK MINUS</span>
                @else
                    <span class="px-2 py-1 bg-green-600 text-white rounded text-[10px] sm:text-xs">SELESAI</span>
                @endif
            </div>
        </div>

        @if(in_array(auth()->user()->role, ['admin','admin_gudang']))
        <div class="flex gap-2">
            @if($session->status === 'open')
                <a href="{{ route('sales-stock-sessions.close.form', $session->id) }}"
                   class="px-3 py-2 bg-red-600 text-white rounded hover:bg-red-700 text-xs sm:text-sm">
                    Tutup Session
                </a>
            @else

@if(auth()->user()->role === 'admin')

<form method="POST" action="{{ route('sales-stock-sessions.reopen',$session->id) }}"
      onsubmit="return confirm('Yakin ingin membuka kembali session ini?')">

@csrf

<button type="submit"
style="background:#f59e0b;color:white"
class="px-3 py-2 rounded text-xs sm:text-sm hover:opacity-90">


REOPEN SESSION

</button>

</form>

@endif

<a href="{{ route('sales-stock-sessions.edit', $session->id) }}"
   class="px-3 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-xs sm:text-sm">
    Edit
</a>

@endif
        </div>
        @endif
    </div>

    <div class="bg-white shadow rounded p-3 sm:p-4">

        @if($session->status === 'open')

            <table class="w-full border text-xs sm:text-sm">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="p-2 border text-left">Produk</th>
                        <th class="p-2 border text-right">Stok Awal</th>
                        <th class="p-2 border text-right">Sisa Stok</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($session->items as $item)

                        @php
                            $sisaStok = $item->opening_qty;

                            if(isset($movements) && $movements->count()){
                                $lastMovement = $movements
                                    ->where('product_id', $item->product_id)
                                    ->sortByDesc('id')
                                    ->first();

                                if($lastMovement){
                                    $sisaStok = $lastMovement->running_balance;
                                }
                            }
                        @endphp

                    <tr>
                        <td class="p-2 border">
                            {{ $item->product->name }}
                        </td>
                        <td class="p-2 border text-right">
                            {{ $item->opening_qty }}
                        </td>
                        <td class="p-2 border text-right font-semibold">
                            {{ $sisaStok }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

        @else

            @php $totalMinus = 0; @endphp

            <div class="overflow-x-auto">
            <table class="w-full border text-xs sm:text-sm">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="p-2 border text-left">Produk</th>
                        <th class="p-2 border text-right">Seharusnya</th>
                        <th class="p-2 border text-right">Fisik</th>
                        <th class="p-2 border text-right">Selisih</th>
                        <th class="p-2 border text-right">Minus</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($session->items as $item)

                        @php
                            $selisih = $item->difference_qty ?? 0;
                            $nominalMinus = 0;

                            if($selisih < 0){
                                $nominalMinus = abs($selisih) * $item->product->warehouse_price;
                                $totalMinus += $nominalMinus;
                            }
                        @endphp

                        <tr>
                            <td class="p-2 border">
                                {{ $item->product->name }}
                            </td>

                            <td class="p-2 border text-right">
                                {{ $item->system_remaining_qty ?? 0 }}
                            </td>

                            <td class="p-2 border text-right">
                                {{ $item->physical_remaining_qty ?? 0 }}
                            </td>

                            <td class="p-2 border text-right 
                                @if($selisih < 0) text-red-600 font-semibold @endif">
                                {{ $selisih }}
                            </td>

                            <td class="p-2 border text-right">
                                @if($nominalMinus > 0)
                                    Rp {{ number_format($nominalMinus,0,',','.') }}
                                @else
                                    -
                                @endif
                            </td>
                        </tr>

                    @endforeach
                </tbody>
            </table>
            </div>

            <div class="mt-4 text-right font-semibold text-sm sm:text-lg">
                Total Nilai Minus:
                <span class="text-red-600">
                    Rp {{ number_format($totalMinus,0,',','.') }}
                </span>
            </div>

        @endif

    </div>

    @if(isset($movements) && $movements->count())
    <div class="mt-8">
        <h3 class="text-lg font-semibold mb-3">Laporan Pergerakan Stok Sales</h3>

        <div class="bg-white shadow rounded overflow-x-auto">
            <table class="w-full text-xs sm:text-sm border">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="border p-2 text-left">Tanggal</th>
                        <th class="border p-2 text-left">Produk</th>
                        <th class="border p-2 text-center">Qty</th>
                        <th class="border p-2 text-center">Saldo</th>
                        <th class="border p-2 text-left">Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($movements as $movement)

                    @if(in_array($movement->type, ['warehouse_in','damage']))
                    @continue
                    @endif
                    <tr class="text-center">
                        <td class="border p-2 text-left">
                            {{ $movement->created_at }}
                        </td>
                        <td class="border p-2 text-left">
                            {{ $movement->product->name ?? '-' }}
                        </td>
                        <td class="border p-2 
                            {{ $movement->quantity < 0 ? 'text-red-600' : 'text-green-600' }}">
                            {{ $movement->quantity }}
                        </td>
                        <td class="border p-2 font-semibold">
                            {{ $movement->running_balance }}
                        </td>
                        <td class="border p-2 text-left">

                        {{ $movement->notes }}

                        @if(
                        isset($movement->visit) &&
                        $movement->visit->store &&
                        !in_array($movement->type, ['warehouse_out','warehouse_in','damage'])
                        )
                       <br>
                       <span class="text-gray-500 text-xs">
                        Toko: {{ $movement->visit->store->name }}
                      </span>
                       @endif

                      </td>

                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    @if($session->status !== 'open')
<div class="mt-8">
    <h3 class="text-lg font-semibold mb-3">
        Rekap Tutup Session (Stok Kembali ke Gudang)
    </h3>

    <div class="bg-white shadow rounded overflow-x-auto">
        <table class="w-full text-xs sm:text-sm border">
            <thead class="bg-gray-100">
                <tr>
                    <th class="border p-2 text-left">Produk</th>
                    <th class="border p-2 text-center">Stok Fisik</th>
                    <th class="border p-2 text-center">Rusak</th>
                    <th class="border p-2 text-center">Kembali ke Gudang</th>
                </tr>
            </thead>
            <tbody>
                @foreach($session->items as $item)

                    @php
                        $fisik = $item->physical_remaining_qty ?? 0;
                        $selisih = $item->difference_qty ?? 0;

                        // asumsi: selisih negatif = minus (tidak relevan disini)
                        $rusak = 0;

                        // kita estimasi rusak dari movement
                        $damageMovement = $movements
                            ->where('product_id', $item->product_id)
                            ->where('type', 'damage')
                            ->sum('quantity');

                        $rusak = abs($damageMovement);

                        $kembali = $fisik - $rusak;
                    @endphp

                    <tr class="text-center">
                        <td class="border p-2 text-left">
                            {{ $item->product->name }}
                        </td>
                        <td class="border p-2">
                            {{ $fisik }}
                        </td>
                        <td class="border p-2 text-red-600">
                            {{ $rusak }}
                        </td>
                        <td class="border p-2 text-green-600 font-semibold">
                            {{ $kembali > 0 ? $kembali : 0 }}
                        </td>
                    </tr>

                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

</div>

</x-app-layout>