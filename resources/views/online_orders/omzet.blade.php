<x-app-layout>

<div class="py-8">
<div class="max-w-5xl mx-auto sm:px-6 lg:px-8">

<div class="bg-white shadow rounded-lg p-6">

    <!-- HEADER -->
    <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center gap-3 mb-6">

    <!-- KIRI -->
    <div>
        <h2 class="text-2xl font-bold text-gray-800">
            Dashboard Omzet Online
        </h2>
    </div>

    <!-- KANAN -->
    <div class="flex flex-wrap items-center gap-2 justify-end">

        <!-- FILTER -->
        <form method="GET" class="flex gap-2">

            <select name="year" onchange="this.form.submit()" class="border rounded p-2">
                @for($y = now()->year; $y >= now()->year - 3; $y--)
                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>
                        {{ $y }}
                    </option>
                @endfor
            </select>

            <select name="month" onchange="this.form.submit()" class="border rounded p-2">
                @for($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                    </option>
                @endfor
            </select>

        </form>

        <!-- 🔙 KEMBALI -->
        <a href="/online-orders"
           class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded shadow text-sm">
            Kembali
        </a>

        <!-- 🏠 DASHBOARD -->
        <a href="/dashboard"
           class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow text-sm">
            Dashboard
        </a>

    </div>

</div>

    <!-- CARD TOTAL -->
    <div class="bg-emerald-600 text-white rounded-lg p-6 mb-6 shadow">
        <div class="text-sm">Total Omzet</div>
        <div class="text-3xl font-bold">
            Rp {{ number_format($totalOmzet, 0, ',', '.') }}
        </div>
    </div>

    <!-- CARD PER HARI -->
    <div class="space-y-3">

        @foreach($perHari as $row)
        <div class="bg-white border rounded-lg p-4 shadow flex justify-between">
            <div>
                {{ \Carbon\Carbon::parse($row->date)->format('d M Y') }}
            </div>
            <div class="font-semibold text-emerald-600">
                Rp {{ number_format($row->total, 0, ',', '.') }}
            </div>
        </div>
        @endforeach

    </div>

</div>
</div>
</div>

</x-app-layout>