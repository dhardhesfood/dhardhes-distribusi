<x-app-layout>

<div class="py-8">
<div class="max-w-6xl mx-auto sm:px-6 lg:px-8">

<div class="bg-white shadow rounded-lg p-6 overflow-visible">

    <div class="flex justify-between items-center mb-4">

    <h2 class="text-xl font-bold">
        Data Iklan & Real Closing
    </h2>

    <form method="GET" class="flex flex-wrap gap-2 mb-4">

    <select name="month"
    class="border rounded px-2 py-1 text-xs"
    onchange="this.form.submit()">

    @foreach(range(1,12) as $m)
        <option value="{{ $m }}"
            {{ request('month') == $m ? 'selected' : '' }}>
            {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
        </option>
    @endforeach

</select>

<select name="year"
    class="border rounded px-2 py-1 text-xs"
    onchange="this.form.submit()">

    @foreach(range(now()->year-2, now()->year+1) as $y)
        <option value="{{ $y }}"
            {{ request('year', now()->year) == $y ? 'selected' : '' }}>
            {{ $y }}
        </option>
    @endforeach

</select>

    <a href="?filter=today"
        class="px-3 py-1 rounded text-xs font-semibold
        {{ request('filter')=='today' ? 'bg-blue-600 text-white' : 'bg-white border' }}">
        Hari Ini
    </a>

    <a href="?filter=7days"
        class="px-3 py-1 rounded text-xs font-semibold
        {{ request('filter')=='7days' ? 'bg-blue-600 text-white' : 'bg-white border' }}">
        7 Hari
    </a>

    <a href="?filter=30days"
        class="px-3 py-1 rounded text-xs font-semibold
        {{ request('filter')=='30days' ? 'bg-blue-600 text-white' : 'bg-white border' }}">
        30 Hari
    </a>

</form>

    <a href="{{ route('ads.create') }}"
       class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow text-sm font-semibold">
        + Input Iklan
    </a>

</div>


    <div class="bg-white border rounded p-5 mb-4 shadow">

    <div class="bg-yellow-50 border rounded p-4 mb-4">

    <div class="text-sm font-semibold mb-2">
        🔎 Analisa Otomatis (Agregat)
    </div>

    <ul class="text-sm list-disc pl-5 space-y-1">
        @foreach($analysis as $item)
            <li>{{ $item }}</li>
        @endforeach
    </ul>

</div>

    <div class="text-sm mb-3 font-semibold">
        Summary KPI (Filter Aktif)
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-2 text-xs mb-3">

    <div class="bg-gray-100 p-2 rounded text-center">
    Klik → LP<br>
    <b>{{ number_format($summary['rate_lp'],1) }}%</b>
    <div class="text-[11px] mt-1">
        {{ $status['lp'] }}
    </div>
    </div>

    <div class="bg-gray-100 p-2 rounded text-center">
        LP → WA<br>
        <b>{{ number_format($summary['rate_wa'],1) }}%</b>
        <div class="text-[11px] mt-1">
        {{ $status['wa'] }}
    </div>
    </div>

    <div class="bg-gray-100 p-2 rounded text-center">
        WA → Chat<br>
        <b>{{ number_format($summary['rate_chat'],1) }}%</b>
        <div class="text-[11px] mt-1">
            {{ $status['chat'] }}
       </div>
    </div>

    <div class="bg-gray-100 p-2 rounded text-center">
        Chat → Closing<br>
        <b>{{ number_format($summary['rate_closing'],1) }}%</b>
        <div class="text-[11px] mt-1">
           {{ $status['closing'] }}
    </div>
    </div>

</div>

    <div class="grid grid-cols-2 md:grid-cols-6 gap-3 text-sm">

        <div>
            Budget + PPN 11%<br>
            <b class="text-lg">Rp {{ number_format($summary['budget'],0,',','.') }}</b>
        </div>

        <div>
            Klik<br>
            <b class="text-lg">{{ $summary['klik'] }}</b>
        </div>

        <div>
            Landing<br>
            <b class="text-lg">{{ $summary['landing'] }}</b>
        </div>

        <div>
            WA Klik<br>
            <b class="text-lg">{{ $summary['wa'] }}</b>
        </div>

        <div>
            Real Chat<br>
            <b class="text-lg">{{ $summary['chat'] }}</b>
        </div>

        <div>
            Closing<br>
            <b class="text-lg">{{ $summary['closing'] }}</b>
        </div>

    </div>

    <div class="grid grid-cols-2 gap-3 mt-4 text-sm">

        <div>
            Closing Rate<br>
            <b class="text-lg">
                {{ number_format($summary['closing_rate'],1) }}%
            </b>
        </div>

        <div>
            Cost per Closing<br>
            <b class="text-lg">
                Rp {{ number_format($summary['cost_per_closing'],0,',','.') }}
            </b>
        </div>

    </div>

</div>

{{-- ================= SUCCESS MESSAGE ================= --}}
@if(session('success'))
<div class="bg-green-100 text-green-700 p-3 mb-4 rounded">
    {{ session('success') }}
</div>
@endif

    <div class="space-y-4">

        @foreach($ads as $ad)

        @php

// =======================
// FUNNEL RATIO
// =======================

$tayanganRate = $ad->klik_tautan > 0 
    ? ($ad->tayangan_konten / $ad->klik_tautan) * 100 
    : 0;

$waRate = $ad->tayangan_konten > 0 
    ? ($ad->hasil / $ad->tayangan_konten) * 100 
    : 0;

$realChatRate = $ad->hasil > 0 
    ? ($ad->real_chat / $ad->hasil) * 100 
    : 0;

$closingRate = $ad->real_chat > 0 
    ? ($ad->closing / $ad->real_chat) * 100 
    : 0;


// =======================
// COST KPI
// =======================

$cpr = $ad->hasil > 0 
    ? $ad->budget / $ad->hasil 
    : 0;

$cpChat = $ad->real_chat > 0 
    ? $ad->budget / $ad->real_chat 
    : 0;

$cpClosing = $ad->closing > 0 
    ? $ad->budget / $ad->closing 
    : 0;

@endphp

        <div class="border rounded p-4">

            <!-- HEADER -->
            <div class="flex justify-between mb-2 items-center">
                <div>
                    <div class="font-bold">
                        {{ $ad->report_date }}
                    </div>
                    <div class="text-sm text-gray-500">
                        Budget + PPN 11%: Rp {{ number_format($ad->budget,0,',','.') }}
                    </div>
                </div>

                   <button type="button"
    onclick="toggleEditDaily({{ $ad->id }})"
    class="ml-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm font-semibold whitespace-nowrap">
    Edit Iklan
</button>
</div>

<!-- FORM EDIT DAILY -->
<div id="edit-daily-{{ $ad->id }}" class="hidden mb-3 bg-yellow-50 p-3 rounded border">

    <form method="POST" action="{{ route('ads.update.daily', $ad->id) }}">
        @csrf

        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-xs">

    <div>
        <label class="text-gray-600">Budget</label>
        <input type="number" name="budget"
            value="{{ $ad->budget }}"
            class="border rounded px-2 py-1 w-full">
    </div>

    <div>
        <label class="text-gray-600">Tayangan Konten (LP)</label>
        <input type="number" name="tayangan_konten"
            value="{{ $ad->tayangan_konten }}"
            class="border rounded px-2 py-1 w-full">
    </div>

    <div>
        <label class="text-gray-600">Klik Tautan (Iklan)</label>
        <input type="number" name="klik_tautan"
            value="{{ $ad->klik_tautan }}"
            class="border rounded px-2 py-1 w-full">
    </div>

    <div>
        <label class="text-gray-600">WA Klik</label>
        <input type="number" name="hasil"
            value="{{ $ad->hasil }}"
            class="border rounded px-2 py-1 w-full">
    </div>

</div>

        <button class="mt-2 bg-blue-600 text-white px-3 py-1 rounded">
            Update Iklan
        </button>

    </form>

</div>

            <!-- DATA FUNNEL -->
            <div class="text-sm mb-3">
                Klik (dari iklan): {{ $ad->klik_tautan }} |
                Masuk Landing (tayangan konten): {{ $ad->tayangan_konten }} |
                WA Klik: {{ $ad->hasil }}
            </div>

            <div class="text-xs text-gray-400 mb-3">
              Alur: Klik → Tayangan Konten → WA Klik → Real Chat → Closing
            </div>

            <!-- ================= KPI ================= -->
<div class="bg-gray-50 border rounded p-3 mb-3">

    <div class="text-xs font-semibold text-gray-500 mb-2">
        KPI Funnel
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-2 text-xs">

        <div class="bg-white border rounded p-2 text-center">
            <div class="text-gray-400">Klik Tautan → LP</div>
            <div class="font-bold text-blue-600">
                {{ number_format($tayanganRate,1) }}%
            </div>
        </div>

        <div class="bg-white border rounded p-2 text-center">
            <div class="text-gray-400">LP → Klik WA</div>
            <div class="font-bold text-indigo-600">
                {{ number_format($waRate,1) }}%
            </div>
        </div>

        <div class="bg-white border rounded p-2 text-center">
            <div class="text-gray-400">Klik WA → Real Chat</div>
            <div class="font-bold text-yellow-600">
                {{ number_format($realChatRate,1) }}%
            </div>
        </div>

        <div class="bg-white border rounded p-2 text-center">
            <div class="text-gray-400">Real Chat → Closing</div>
            <div class="font-bold text-green-600">
                {{ number_format($closingRate,1) }}%
            </div>
        </div>

    </div>

    <div class="grid grid-cols-3 gap-2 mt-3 text-xs">

        <div class="bg-white border rounded p-2 text-center">
            <div class="text-gray-400">Cost / Klik WA</div>
            <div class="font-bold text-red-500">
                Rp {{ number_format($cpr,0,',','.') }}
            </div>
        </div>

        <div class="bg-white border rounded p-2 text-center">
            <div class="text-gray-400">Cost / Real Chat</div>
            <div class="font-bold text-orange-500">
                Rp {{ number_format($cpChat,0,',','.') }}
            </div>
        </div>

        <div class="bg-white border rounded p-2 text-center">
            <div class="text-gray-400">Cost / Closing</div>
            <div class="font-bold text-green-700">
                Rp {{ number_format($cpClosing,0,',','.') }}
            </div>
        </div>

    </div>

</div>

            <!-- FORM UPDATE -->
            @php
    $isFilled = ($ad->real_chat > 0 || $ad->closing > 0);
@endphp

@if(!$isFilled)

    <!-- ===================== -->
    <!-- FORM INPUT -->
    <!-- ===================== -->
    <form method="POST" action="{{ route('ads.update.real', $ad->id) }}">
        @csrf

        <div class="flex flex-col md:flex-row gap-3 w-full">

            <!-- CLOSING -->
            <div class="w-full">
                <label class="text-xs text-gray-500">
                    Jumlah Closing
                </label>
                <input type="number" name="closing"
                    class="border rounded px-2 py-1 w-full"
                    placeholder="Contoh: 5">
            </div>

            <div class="flex items-end">
                <button class="bg-blue-600 text-white px-3 py-1 rounded">
                    Save
                </button>
            </div>

        </div>

    </form>

@else

    <!-- ===================== -->
    <!-- DATA SAJA -->
    <!-- ===================== -->
    <div class="bg-gray-50 border rounded p-3">

        <div class="flex justify-between items-center">

            <div class="text-sm">
                Real Chat: <b class="text-blue-600">{{ $ad->real_chat }}</b> |
                Closing: <b class="text-green-600">{{ $ad->closing }}</b>
            </div>

            <!-- BUTTON EDIT -->
            <button onclick="toggleEdit({{ $ad->id }})"
                class="text-xs bg-yellow-500 hover:bg-yellow-600 text-white px-2 py-1 rounded">
                Edit
            </button>

        </div>

        <!-- FORM EDIT (HIDDEN) -->
        <form id="edit-form-{{ $ad->id }}"
              method="POST"
              action="{{ route('ads.update.real', $ad->id) }}"
              class="mt-3 hidden">
            @csrf

            <div class="flex gap-2">

               <input type="number" name="closing"
                      value="{{ $ad->closing }}"
                      class="border rounded px-2 py-1 w-full">

               <button class="bg-blue-600 text-white px-3 py-1 rounded">
                           Update
               </button>

             </div>

        </form>

    </div>

@endif

        </div>

        @endforeach

    </div>

</div>
</div>
</div>

<script>
function toggleEdit(id) {
    let form = document.getElementById('edit-form-' + id);
    form.classList.toggle('hidden');
}
</script>

<script>
function toggleEditDaily(id) {
    let form = document.getElementById('edit-daily-' + id);
    form.classList.toggle('hidden');
}
</script>

</x-app-layout>