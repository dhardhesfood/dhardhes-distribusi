<x-app-layout>

<div class="py-8">
<div class="max-w-6xl mx-auto sm:px-6 lg:px-8">

<div class="bg-white shadow rounded-lg p-6 overflow-visible">

    <div class="flex justify-between items-center mb-4">

    <h2 class="text-xl font-bold">
        Data Iklan & Real Closing
    </h2>

    <form method="GET" class="flex flex-wrap gap-2 mb-4">

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

    <div class="text-sm mb-3 font-semibold">
        Summary KPI (Filter Aktif)
    </div>

    <div class="grid grid-cols-2 md:grid-cols-6 gap-3 text-sm">

        <div>
            Budget<br>
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
            <div class="flex justify-between mb-2">
                <div>
                    <div class="font-bold">
                        {{ $ad->report_date }}
                    </div>
                    <div class="text-sm text-gray-500">
                        Budget: Rp {{ number_format($ad->budget,0,',','.') }}
                    </div>
                </div>
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
            <div class="text-gray-400">Klik → Landing</div>
            <div class="font-bold text-blue-600">
                {{ number_format($tayanganRate,1) }}%
            </div>
        </div>

        <div class="bg-white border rounded p-2 text-center">
            <div class="text-gray-400">Landing → WA</div>
            <div class="font-bold text-indigo-600">
                {{ number_format($waRate,1) }}%
            </div>
        </div>

        <div class="bg-white border rounded p-2 text-center">
            <div class="text-gray-400">WA → Chat</div>
            <div class="font-bold text-yellow-600">
                {{ number_format($realChatRate,1) }}%
            </div>
        </div>

        <div class="bg-white border rounded p-2 text-center">
            <div class="text-gray-400">Chat → Closing</div>
            <div class="font-bold text-green-600">
                {{ number_format($closingRate,1) }}%
            </div>
        </div>

    </div>

    <div class="grid grid-cols-3 gap-2 mt-3 text-xs">

        <div class="bg-white border rounded p-2 text-center">
            <div class="text-gray-400">Cost / WA</div>
            <div class="font-bold text-red-500">
                Rp {{ number_format($cpr,0,',','.') }}
            </div>
        </div>

        <div class="bg-white border rounded p-2 text-center">
            <div class="text-gray-400">Cost / Chat</div>
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

</x-app-layout>