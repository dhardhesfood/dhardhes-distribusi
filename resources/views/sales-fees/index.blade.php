<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-lg sm:text-xl text-gray-800 leading-tight">
            Dashboard Fee Sales
        </h2>
    </x-slot>

    <div class="py-4 sm:py-6">
        <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="mb-4 p-3 sm:p-4 bg-green-50 border border-green-200 text-green-700 rounded text-xs sm:text-sm">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white shadow sm:rounded-lg p-4 sm:p-6 mb-6">

<div class="bg-white shadow sm:rounded-lg p-4 sm:p-6 mb-6">

<form method="GET" class="mb-4 flex gap-2 items-center">

<select name="month"
class="border rounded px-3 py-1"
onchange="this.form.submit()">

@foreach(range(1,12) as $m)

<option value="{{ $m }}"
{{ request('month', now()->month) == $m ? 'selected' : '' }}>

{{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}

</option>

@endforeach

</select>

<select name="year"
class="border rounded px-3 py-1"
onchange="this.form.submit()">

@foreach(range(now()->year-2, now()->year+1) as $y)

<option value="{{ $y }}"
{{ request('year', now()->year) == $y ? 'selected' : '' }}>

{{ $y }}

</option>

@endforeach

</select>

</form>

</div>

<div class="bg-white shadow sm:rounded-lg p-4 sm:p-6 mb-6">

<h3 class="font-semibold mb-3">
Fee Sales Bulan
{{ \Carbon\Carbon::create($year,$month,1)->translatedFormat('F Y') }}
</h3>

<table class="min-w-full text-sm border">

<thead class="bg-gray-100">
<tr>
<th class="p-2 border text-left">Tanggal</th>
<th class="p-2 border text-left">Sales</th>
<th class="p-2 border text-right">Fee Konsinyasi</th>
<th class="p-2 border text-right">Fee Tunai</th>
<th class="p-2 border text-right">Total Fee</th>
</tr>
</thead>

<tbody>

@foreach($dailyFee as $row)

<tr>

<td class="p-2 border">
{{ $row->tanggal ? \Carbon\Carbon::parse($row->tanggal)->format('d-m-Y') : '-' }}
</td>

<td class="p-2 border">
{{ $row->name }}
</td>

<td class="p-2 border text-right">
Rp {{ number_format($row->fee_konsinyasi,0,',','.') }}
</td>

<td class="p-2 border text-right">
Rp {{ number_format($row->fee_tunai,0,',','.') }}
</td>

<td class="p-2 border text-right font-semibold">
Rp {{ number_format($row->total_fee,0,',','.') }}
</td>

</tr>

@endforeach

</tbody>

</table>

</div>

            {{-- ======================== TABEL FEE ======================== --}}
            <div class="bg-white shadow sm:rounded-lg p-4 sm:p-6 mb-6">

                <div class="overflow-x-auto">
                    <table class="min-w-full text-xs sm:text-sm border">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="p-2 sm:p-3 text-left border">Nama Sales</th>
                                <th class="p-2 sm:p-3 text-right border">Total Fee</th>
                                <th class="p-2 sm:p-3 text-right border">Sudah Dibayar</th>
                                <th class="p-2 sm:p-3 text-right border">Sisa Kasbon</th>
                                <th class="p-2 sm:p-3 text-right border">Sisa Fee</th>

                                @if(auth()->user()->role === 'admin')
                                    <th class="p-2 sm:p-3 text-center border">Aksi</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($sales as $row)
                                <tr class="border-t">
                                    <td class="p-2 sm:p-3 border font-medium">
                                        {{ $row['name'] }}
                                    </td>

                                    <td class="p-2 sm:p-3 border text-right">
                                        {{ number_format($row['total_generated'], 0, ',', '.') }}
                                    </td>

                                    <td class="p-2 sm:p-3 border text-right">
                                        {{ number_format($row['total_paid'], 0, ',', '.') }}
                                    </td>

                                    <td class="p-2 sm:p-3 border text-right">
                                        {{ number_format($row['kasbon_remaining'], 0, ',', '.') }}
                                    </td>

                                    <td class="p-2 sm:p-3 border text-right font-semibold">
                                        @if($row['is_minus'])
                                            <span class="text-red-600">
                                                {{ number_format($row['net_fee'], 0, ',', '.') }} (Minus)
                                            </span>
                                        @else
                                            <span class="text-green-600">
                                                {{ number_format($row['net_fee'], 0, ',', '.') }}
                                            </span>
                                        @endif
                                    </td>

                                    @if(auth()->user()->role === 'admin')
                                        <td class="p-2 sm:p-3 border text-center">
                                            @if($row['net_fee'] > 0)
                                                <form method="POST" action="{{ route('sales-fees.pay') }}" class="flex flex-col sm:flex-row items-center justify-center gap-2">
                                                    @csrf
                                                    <input type="hidden" name="user_id" value="{{ $row['user_id'] }}">

                                                    <input 
                                                        type="number"
                                                        name="amount"
                                                        min="1"
                                                        max="{{ $row['net_fee'] }}"
                                                        required
                                                        class="w-20 sm:w-24 px-2 py-1 border rounded text-xs"
                                                        placeholder="Nominal">

                                                    <button
                                                        type="submit"
                                                        onclick="return confirm('Yakin bayar nominal ini?')"
                                                        class="px-3 py-1 bg-blue-600 text-white text-xs rounded hover:bg-blue-700">
                                                        Bayar
                                                    </button>
                                                </form>
                                            @else
                                                <button
                                                    class="px-3 py-1 bg-gray-400 text-white text-xs rounded cursor-not-allowed"
                                                    disabled>
                                                    Bayar
                                                </button>
                                            @endif
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ auth()->user()->role === 'admin' ? 6 : 5 }}" class="p-4 sm:p-6 text-center text-gray-500 text-xs sm:text-sm">
                                        Tidak ada data sales.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>

            {{-- ======================== RINCIAN SETTLEMENT BULAN INI ======================== --}}
            <div class="bg-white shadow sm:rounded-lg p-4 sm:p-6">
                <h3 class="text-sm sm:text-base font-semibold text-gray-700 mb-3">
                    Rincian Settlement Bulan Ini
                </h3>

                @if(isset($monthlySettlements) && $monthlySettlements->count() > 0)

                    @foreach($monthlySettlements as $userId => $settlements)

                        @if(auth()->user()->role === 'admin')
                            <div class="mb-4">
                                <div class="text-xs sm:text-sm font-semibold text-gray-600 mb-2">
                                    {{ collect($sales)->firstWhere('user_id', $userId)['name'] ?? 'Sales' }}
                                </div>
                        @endif

                        <ul class="space-y-1 text-xs sm:text-sm">
                            @foreach($settlements as $settlement)
                                <li>
                                    <a href="{{ route('sales.settlements.show', [$settlement->user_id, $settlement->settlement_date]) }}"
                                       class="text-indigo-600 hover:underline">
                                        Laporan Penjualan {{ \Carbon\Carbon::parse($settlement->settlement_date)->format('d-m-Y') }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>

                        @if(auth()->user()->role === 'admin')
                            </div>
                        @endif

                    @endforeach

                @else
                    <div class="text-xs sm:text-sm text-gray-500">
                        Belum ada settlement bulan ini.
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>