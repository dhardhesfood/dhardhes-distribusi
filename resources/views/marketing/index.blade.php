<x-app-layout>

<div class="py-6">
<div class="max-w-6xl mx-auto sm:px-6 lg:px-8">

<div class="bg-white p-6 rounded shadow">

{{-- ================= TOTAL GLOBAL ================= --}}
@php
    $totalOmzet = $data->sum('omzet');
    $totalAds = $data->sum('ads');

    $roas = $totalAds > 0 ? $totalOmzet / $totalAds : 0;
    $acos = $totalOmzet > 0 ? ($totalAds / $totalOmzet) * 100 : 0;
@endphp

<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">

    <div class="bg-gray-100 p-4 rounded text-center">
        <div class="text-xs text-gray-500">Total Omzet (All Time)</div>
        <div class="text-xl font-bold">
            Rp {{ number_format($totalOmzet,0,',','.') }}
        </div>
    </div>

    <div class="bg-gray-100 p-4 rounded text-center">
        <div class="text-xs text-gray-500">Total Ads (All Time)</div>
        <div class="text-xl font-bold">
            Rp {{ number_format($totalAds,0,',','.') }}
        </div>
    </div>

    <div class="bg-gray-100 p-4 rounded text-center">
        <div class="text-xs text-gray-500">ROAS (All Time)</div>
        <div class="text-xl font-bold">
            {{ number_format($roas,2) }}
        </div>
    </div>

    <div class="bg-gray-100 p-4 rounded text-center">
        <div class="text-xs text-gray-500">ACOS (All Time)</div>
        <div class="text-xl font-bold">
            {{ number_format($acos,2) }}%
        </div>
    </div>

</div>

<div class="border-t mb-6"></div>

<details class="mb-6">

<!-- ================= SUMMARY PRODUK OFFLINE ================= -->
<div class="mt-10">

<h3 class="font-bold mb-2">📦 Produk Terjual (Offline)</h3>

<table class="w-full text-sm border">
    <thead class="bg-gray-100">
        <tr>
            <th class="p-2 border text-left">Produk</th>
            <th class="p-2 border text-center">Qty</th>
            <th class="p-2 border text-right">Omzet</th>
        </tr>
    </thead>
    <tbody>
        @foreach($offlineSummary as $row)
        <tr class="border-t">
            <td class="p-2 border">{{ $row->name }}</td>
            <td class="p-2 border text-center">{{ number_format($row->total_qty) }}</td>
            <td class="p-2 border text-right">
                Rp {{ number_format($row->total_omzet,0,',','.') }}
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

</div>


<!-- ================= SUMMARY PRODUK ONLINE ================= -->
<div class="mt-10">

<h3 class="font-bold mb-2">🌐 Produk Terjual (Online)</h3>

<table class="w-full text-sm border">
    <thead class="bg-gray-100">
        <tr>
            <th class="p-2 border text-left">Produk</th>
            <th class="p-2 border text-center">Qty</th>
            <th class="p-2 border text-right">Omzet</th>
        </tr>
    </thead>
    <tbody>
        @foreach($onlineSummary as $row)
        <tr class="border-t">
            <td class="p-2 border">{{ $row->name }}</td>
            <td class="p-2 border text-center">{{ number_format($row->total_qty) }}</td>
            <td class="p-2 border text-right">
                Rp {{ number_format($row->total_omzet,0,',','.') }}
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

</div>

<summary class="cursor-pointer font-semibold text-sm bg-gray-100 px-4 py-2 rounded">
    🔍 Lihat Detail ACOS Produk & Paket
</summary>

<div class="mt-4">

<div class="mt-8">

<h3 class="font-bold mb-2">ACOS Produk (Offline)</h3>

@foreach($offlineProduk as $row)

<div class="bg-white p-3 rounded shadow mb-2 flex justify-between">

<div>
<div class="font-semibold">
{{ $row->name }} - Rp {{ number_format($row->price,0,',','.') }}
</div>
</div>

<div class="text-right">

<div class="font-bold">
{{ number_format($acos,2) }}%
</div>

<div class="text-xs text-gray-500">
Rp {{ number_format($row->price * ($acos/100),0,',','.') }}
</div>

</div>

</div>
@endforeach

</div>

<div class="mt-8">

<h3 class="font-bold mb-2">ACOS Paket (Online)</h3>

@foreach($onlinePaket as $row)

<div class="bg-white p-3 rounded shadow mb-2 flex justify-between">

<div>
<div class="font-semibold">
{{ $row->package_name }} - Rp {{ number_format($row->price,0,',','.') }}
</div>
</div>

<div class="text-right">

<div class="font-bold">
{{ number_format($acos,2) }}%
</div>

<div class="text-xs text-gray-500">
Rp {{ number_format($row->price * ($acos/100),0,',','.') }}
</div>

</div>

</div>

@endforeach

</div> <!-- end isi spoiler -->

</details>

 <!-- HEADER -->
    <div class="flex flex-col md:flex-row md:justify-between md:items-center mb-4 gap-2">

        <h2 class="text-xl font-bold">
            Dashboard Marketing
        </h2>

        <!-- FILTER -->
        <div class="flex gap-2">

            <a href="?type=monthly"
                class="px-3 py-1 rounded text-sm font-semibold
                {{ $type == 'monthly' ? 'bg-blue-600 text-white' : 'bg-gray-200' }}">
                Monthly
            </a>

            <a href="?type=weekly"
                class="px-3 py-1 rounded text-sm font-semibold
                {{ $type == 'weekly' ? 'bg-blue-600 text-white' : 'bg-gray-200' }}">
                Weekly
            </a>

        </div>

        <form method="GET" class="flex flex-col md:flex-row gap-2 mt-2 md:mt-0 md:ml-4 w-full md:w-auto">

<input type="date" name="start_date" value="{{ $start }}"
class="border rounded px-2 py-1 text-sm w-full md:w-auto">

<input type="date" name="end_date" value="{{ $end }}"
class="border rounded px-2 py-1 text-sm w-full md:w-auto">

<input type="hidden" name="type" value="{{ $type }}">

<button class="bg-blue-600 text-white px-3 py-2 rounded text-sm w-full md:w-auto">
Filter
</button>

</form>

    </div>

    <!-- ================= MOBILE CARD ================= -->
<div class="block md:hidden space-y-3">

@foreach($data as $row)

@php
    $acos = $row->acos ?? 0;

    if ($acos <= 5) {
        $color = 'text-green-600';
        $label = '🔥 Sehat';
    } elseif ($acos <= 10) {
        $color = 'text-yellow-600';
        $label = '⚠️ Perhatian';
    } else {
        $color = 'text-red-600';
        $label = '❌ Berat';
    }
@endphp

<div class="bg-gray-50 p-4 rounded-xl shadow">

    <!-- PERIODE -->
    <div class="font-semibold text-sm mb-2">

        @if($type == 'weekly')

            @php
            $start = \Carbon\Carbon::now()
                ->setISODate($row->tahun, $row->minggu)
                ->startOfWeek(\Carbon\Carbon::MONDAY);

            $end = (clone $start)->endOfWeek(\Carbon\Carbon::SUNDAY);
            @endphp

            {{ $start->format('d M') }} - {{ $end->format('d M Y') }}

        @else

            {{ \Carbon\Carbon::createFromFormat('Y-m', $row->periode)->translatedFormat('F Y') }}

        @endif

    </div>

    <!-- DATA -->
    <div class="grid grid-cols-2 gap-2 text-sm">

        <div>
            <div class="text-gray-500 text-xs">Omzet</div>
            <div class="font-semibold">
                Rp {{ number_format($row->omzet,0,',','.') }}
            </div>
        </div>

        <div>
            <div class="text-gray-500 text-xs">Ads</div>
            <div class="font-semibold">
                Rp {{ number_format($row->ads,0,',','.') }}
            </div>
        </div>

        <div>
            <div class="text-gray-500 text-xs">ROAS</div>
            <div class="font-semibold">
                {{ $row->roas ?? '-' }}
            </div>
        </div>

        <div>
            <div class="text-gray-500 text-xs">ACOS</div>
            <div class="font-semibold {{ $color }}">
                {{ $row->acos ?? '-' }}%
                <div class="text-xs">{{ $label }}</div>
            </div>
        </div>

    </div>

</div>

@endforeach

</div>


<!-- ================= DESKTOP TABLE ================= -->
<div class="hidden md:block overflow-x-auto">

<table class="w-full text-sm border">

    <thead class="bg-gray-100">
        <tr>

            @if($type == 'weekly')
                <th class="p-2 border">Tahun</th>
                <th class="p-2 border">Minggu</th>
            @else
                <th class="p-2 border">Periode</th>
            @endif

            <th class="p-2 border">Omzet</th>
            <th class="p-2 border">Ads</th>
            <th class="p-2 border">ROAS</th>
            <th class="p-2 border">ACOS</th>

        </tr>
    </thead>

    <tbody>

    @foreach($data as $row)

    @php
        $acos = $row->acos ?? 0;

        if ($acos <= 5) {
            $color = 'text-green-600 font-bold';
            $label = '🔥 Sehat';
        } elseif ($acos <= 10) {
            $color = 'text-yellow-600 font-bold';
            $label = '⚠️ Perhatian';
        } else {
            $color = 'text-red-600 font-bold';
            $label = '❌ Berat';
        }
    @endphp

    <tr class="text-center border-t">

        @if($type == 'weekly')
            <td class="p-2 border">{{ $row->tahun }}</td>
            <td class="p-2 border">
                @php
                $start = \Carbon\Carbon::now()
                    ->setISODate($row->tahun, $row->minggu)
                    ->startOfWeek(\Carbon\Carbon::MONDAY);

                $end = (clone $start)->endOfWeek(\Carbon\Carbon::SUNDAY);
                @endphp

                {{ $start->format('d M') }} - {{ $end->format('d M Y') }}
            </td>
        @else
            <td class="p-2 border">
                {{ \Carbon\Carbon::createFromFormat('Y-m', $row->periode)->translatedFormat('F Y') }}
            </td>
        @endif

        <td class="p-2 border">
            Rp {{ number_format($row->omzet,0,',','.') }}
        </td>

        <td class="p-2 border">
            Rp {{ number_format($row->ads,0,',','.') }}
        </td>

        <td class="p-2 border">
            {{ $row->roas ?? '-' }}
        </td>

        <td class="p-2 border {{ $color }}">
            {{ $row->acos ?? '-' }}%
            <div class="text-xs">{{ $label }}</div>
        </td>

    </tr>

    @endforeach

    </tbody>

</table>

</div>

</div>

</div>

</div>
</div>

</x-app-layout>