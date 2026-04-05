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

<div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6 text-sm">

<div class="font-semibold text-blue-800 mb-2">
Aturan Reward Sales
</div>

<ul class="list-disc pl-5 space-y-1 text-gray-700">

<li>
Fee ≥ <b>Rp 500.000</b> → Reward <b>5%</b>
</li>

<li>
Fee ≥ <b>Rp 1.500.000</b> → Reward <b>7%</b>
</li>

<li>
Fee ≥ <b>Rp 3.000.000</b> → Reward <b>10%</b>
</li>

<li>
Fee ≥ <b>Rp 5.000.000</b> → Reward <b>12%</b>
</li>

</ul>

<div class="mt-3 font-semibold text-blue-800">
Syarat Kesehatan Toko
</div>

<ul class="list-disc pl-5 space-y-1 text-gray-700">

<li>
Jika toko <b>Pertimbangkan Ditarik &gt; 5%</b> → Reward <b>GUGUR</b>
</li>

<li>
Jika toko <b>Terlambat Berat &gt; 10%</b> → Reward <b>GUGUR</b>
</li>

<li>
Jika toko <b>Terlambat &gt; 20%</b> → Reward <b>dipotong 30%</b>
</li>

</ul>

<div class="mt-3 font-semibold text-blue-800">
Syarat Disiplin Request Stok
</div>

<ul class="list-disc pl-5 space-y-1 text-gray-700">

<li>
Jika telat <b>&ge; 3 kali</b> → Reward mulai dipotong <b>5%</b>
</li>

<li>
Jika telat <b>&ge; 5 kali</b> → Reward dipotong <b>10%</b>
</li>

<li>
Jika telat <b>&ge; 7 kali</b> → Reward dipotong <b>20%</b>
</li>

<li>
Jika telat <b>&ge; 9 kali</b> → Reward dipotong <b>30%</b>
</li>

</ul>


</div>

<div class="mt-5 mb-6 p-3 rounded-md bg-green-50 border border-green-200 text-green-700 text-sm font-medium">
💡 Semakin disiplin input request stok minimal 3 jadwal pengiriman, semakin aman reward yang didapat.
</div>

<div class="mt-5 mb-6 p-3 rounded-md bg-green-50 border border-green-200 text-green-700 text-sm font-medium">
💡 Semakin sehat kondisi toko yang dikelola, semakin besar peluang reward yang didapat.
</div>

<div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-6 text-center text-sm">

<div class="p-3 rounded-lg bg-green-50 border border-green-200">
<div class="font-semibold text-green-700">Aman</div>
<div class="text-lg font-bold text-green-800">
{{ round(($storeStatusStats->safe / $storeStatusStats->total) * 100) }}%
</div>
</div>

<div class="p-3 rounded-lg bg-yellow-50 border border-yellow-200">
<div class="font-semibold text-yellow-700">Hari Ini</div>
<div class="text-lg font-bold text-yellow-800">
{{ round(($storeStatusStats->today / $storeStatusStats->total) * 100) }}%
</div>
</div>

<div class="p-3 rounded-lg bg-red-50 border border-red-200">
<div class="font-semibold text-red-700">Terlambat</div>
<div class="text-lg font-bold text-red-800">
{{ round(($storeStatusStats->late / $storeStatusStats->total) * 100) }}%
</div>
</div>

<div class="p-3 rounded-lg bg-orange-50 border border-orange-200">
<div class="font-semibold text-orange-700">Terlambat Berat</div>
<div class="text-lg font-bold text-orange-800">
{{ round(($storeStatusStats->heavy / $storeStatusStats->total) * 100) }}%
</div>
</div>

<div class="p-3 rounded-lg bg-gray-100 border border-gray-300">
<div class="font-semibold text-gray-700">Pertimbangkan Ditarik</div>
<div class="text-lg font-bold text-gray-800">
{{ round(($storeStatusStats->withdraw / $storeStatusStats->total) * 100) }}%
</div>
</div>

</div>

<div class="mb-6 p-4 rounded-lg border text-center
@if($riskColor=='green') bg-green-50 border-green-200 text-green-800
@elseif($riskColor=='yellow') bg-yellow-50 border-yellow-200 text-yellow-800
@else bg-red-50 border-red-200 text-red-800
@endif">

<div class="text-sm font-semibold mb-1">
Status Reward Area
</div>

<div class="text-xl font-bold">
@if($riskStatus=='AMAN')
🟢 REWARD AMAN
@elseif($riskStatus=='WASPADA')
🟡 REWARD WASPADA
@else
🔴 REWARD TERANCAM
@endif
</div>

<div class="text-xs mt-1 opacity-80">
Kondisi toko mempengaruhi kelayakan reward sales.
</div>

</div>

</div>

</div>

</div>

<div class="bg-white shadow sm:rounded-lg p-4 sm:p-6 mb-6">

</div>

</div>

<h3 class="font-semibold mb-3">
Fee Sales Bulan
{{ \Carbon\Carbon::create($year,$month,1)->translatedFormat('F Y') }}
</h3>

<form method="GET" class="mb-3 flex gap-2 items-center">

<select name="month"
class="border rounded px-3 py-1"
onchange="this.form.submit()">

@foreach(range(1,12) as $m)

<option value="{{ $m }}"
{{ request('month',$month) == $m ? 'selected' : '' }}>

{{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}

</option>

@endforeach

</select>

<select name="year"
class="border rounded px-3 py-1"
onchange="this.form.submit()">

@foreach(range(now()->year-2, now()->year+1) as $y)

<option value="{{ $y }}"
{{ request('year',$year) == $y ? 'selected' : '' }}>

{{ $y }}

</option>

@endforeach

</select>

</form>

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

<div class="bg-white shadow sm:rounded-lg p-4 sm:p-6 mb-6">

    <div class="text-sm font-semibold text-gray-700 mb-3">
        Ringkasan Fee (Transparansi Perhitungan Reward)
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full text-xs sm:text-sm border">
            <thead class="bg-gray-100">
                <tr>
                    <th class="p-2 border text-left">Nama Sales</th>
                    <th class="p-2 border text-right">Total Fee (Semua Waktu)</th>
                    <th class="p-2 border text-right">Fee Bulan Ini</th>
                    <th class="p-2 border text-right">Sisa Fee Bulan Sebelumnya</th>
                    <th class="p-2 border text-right">Sisa Fee Sekarang</th>
                </tr>
            </thead>
            <tbody>

                @foreach($sales as $row)
                <tr>
                    <td class="p-2 border">
                        {{ $row['name'] }}
                    </td>

                    <td class="p-2 border text-right">
                        Rp {{ number_format($row['total_generated'],0,',','.') }}
                    </td>

                    <td class="p-2 border text-right text-blue-600 font-semibold">
                        Rp {{ number_format($row['monthly_fee'],0,',','.') }}
                    </td>

                    <td class="p-2 border text-right">
                        Rp {{ number_format($row['previous_fee'],0,',','.') }}
                    </td>

                    <td class="p-2 border text-right text-green-600 font-semibold">
                        Rp {{ number_format($row['net_fee'],0,',','.') }}
                    </td>
                </tr>
                @endforeach

            </tbody>
        </table>
    </div>

    <div class="mt-3 text-xs text-gray-600">
        💡 Reward dihitung hanya dari <b>fee bulan berjalan</b>. Fee bulan sebelumnya tidak dihitung ulang.
    </div>

</div>

<h3 class="font-semibold mt-8 mb-3 text-gray-700">
Rekap Fee Sales
</h3>
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

<h3 class="font-semibold mt-10 mb-3 text-gray-700">
Reward Sales Bulan
{{ \Carbon\Carbon::create($year,$month,1)->translatedFormat('F Y') }}
</h3>

@if(auth()->user()->role === 'admin')

    @if(!$rewardLocked)

        <form method="POST" action="{{ route('sales-rewards.lock') }}" class="mb-4">
        @csrf

        <input type="hidden" name="month" value="{{ $month }}">
        <input type="hidden" name="year" value="{{ $year }}">

        <button
        onclick="return confirm('LOCK reward bulan ini? Setelah dikunci tidak bisa diubah.')"
        class="px-4 py-2 bg-red-600 text-white rounded">

        LOCK REWARD BULAN INI

        </button>

        </form>

    @else

        <div class="mb-4 px-4 py-2 bg-green-100 text-green-700 rounded text-sm inline-block">
        Reward bulan ini sudah dikunci
        </div>

    @endif

@endif

<div class="bg-white shadow sm:rounded-lg p-4 sm:p-6 mb-6">

<div class="overflow-x-auto">

<table class="min-w-full text-xs sm:text-sm border">

<thead class="bg-gray-100">

<tr>

<th class="p-2 border text-left">Nama Sales</th>

<th class="p-2 border text-right">Reward KPI</th>

<th class="p-2 border text-center">Status</th>

</tr>

</thead>

<tbody>

@forelse($totalRewards as $t)

<tr>

<td class="p-2 border">
{{ $t['name'] }}
</td>

<td class="p-2 border text-right text-green-700 font-semibold">
Rp {{ number_format($t['kpi_reward'],0,',','.') }}
</td>

<td class="p-2 border text-center">

@if($t['kpi_reward'] > 0)

<span class="text-green-600 font-semibold">
LOLOS
</span>

@else

<span class="text-red-600 font-semibold">
TIDAK LOLOS
</span>

@endif

</td>

</tr>

@empty

<tr>
<td colspan="3" class="p-3 text-center text-gray-500">
Tidak ada data reward
</td>
</tr>

@endforelse

</tbody>

</table>

</div>
</div>

<h3 class="font-semibold mt-10 mb-3 text-gray-700">
Reward Misi
</h3>

<div class="bg-white shadow sm:rounded-lg p-4 sm:p-6 mb-6">

<div class="overflow-x-auto">

<table class="min-w-full text-xs sm:text-sm border">

<thead class="bg-gray-100">

<tr>
<th class="p-2 border text-left">Sales</th>
<th class="p-2 border text-center">Mission</th>
<th class="p-2 border text-right">Reward</th>
<th class="p-2 border text-center">Tanggal</th>
</tr>

</thead>

<tbody>

@forelse($missionRewards as $m)

<tr>

<td class="p-2 border">
{{ $m->name }}
</td>

<td class="p-2 border text-center">
{{ $m->mission_id }}
</td>

<td class="p-2 border text-right text-green-700 font-semibold">
Rp {{ number_format($m->reward_amount,0,',','.') }}
</td>

<td class="p-2 border text-center">
{{ \Carbon\Carbon::parse($m->reward_date)->format('d-m-Y') }}
</td>

</tr>

@empty

<tr>
<td colspan="4" class="p-3 text-center text-gray-500">
Belum ada reward misi
</td>
</tr>

@endforelse

</tbody>

</table>

</div>
</div>

<h3 class="font-semibold mt-10 mb-3 text-gray-700">
Total Reward (KPI + Misi)
</h3>

<div class="bg-white shadow sm:rounded-lg p-4 sm:p-6 mb-6">

<div class="overflow-x-auto">

<table class="min-w-full text-xs sm:text-sm border">

<thead class="bg-gray-100">

<tr>
<th class="p-2 border text-left">Sales</th>
<th class="p-2 border text-right">Reward KPI</th>
<th class="p-2 border text-right">Reward Misi</th>
<th class="p-2 border text-right">Total Reward</th>
<th class="p-2 border text-right">Reward Dibayar</th>
<th class="p-2 border text-right">Sisa Reward</th>
@if(auth()->user()->role === 'admin')
<th class="p-2 border text-center">Bayar</th>
@endif
</tr>

</thead>

<tbody>

@forelse($totalRewards as $t)

<tr>

<td class="p-2 border">
{{ $t['name'] }}
</td>

<td class="p-2 border text-right">
Rp {{ number_format($t['kpi_reward'],0,',','.') }}
</td>

<td class="p-2 border text-right">
Rp {{ number_format($t['mission_reward'],0,',','.') }}
</td>

<td class="p-2 border text-right font-semibold text-green-700">
Rp {{ number_format($t['total_reward'],0,',','.') }}
</td>

<td class="p-2 border text-right">
Rp {{ number_format($t['reward_paid'],0,',','.') }}
</td>

<td class="p-2 border text-right text-blue-700 font-semibold">
Rp {{ number_format($t['reward_remaining'],0,',','.') }}
</td>
@if(auth()->user()->role === 'admin')
<td class="p-2 border text-center">

@if($t['reward_remaining'] > 0)

<form method="POST" action="{{ route('sales-rewards.pay') }}"
class="flex flex-col sm:flex-row items-center justify-center gap-2">

@csrf

<input type="hidden" name="user_id" value="{{ $t['user_id'] }}">

<input type="number"
name="amount"
min="1"
max="{{ $t['reward_remaining'] }}"
required
class="w-20 sm:w-24 px-2 py-1 border rounded text-xs text-center">

<button
class="px-3 py-1 mt-1 sm:mt-0 bg-blue-600 text-white text-xs rounded hover:bg-blue-700">

Bayar

</button>

</form>

@else

<button
class="px-3 py-1 bg-gray-400 text-white text-xs rounded"
disabled>

Lunas

</button>

@endif

</td>

@endif

</tr>

@empty

<tr>
<td colspan="{{ auth()->user()->role === 'admin' ? 7 : 6 }}" class="p-3 text-center text-gray-500">
Belum ada data total reward
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