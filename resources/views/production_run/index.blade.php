<x-app-layout>
<div class="p-6 max-w-4xl mx-auto">

@if ($errors->any())
    <div class="bg-red-100 text-red-700 p-3 mb-3">
        <ul>
            @foreach ($errors->all() as $error)
                <li>- {{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

    <!-- 🔥 FORM -->
    <div class="bg-white p-5 rounded shadow mb-6">

        <h2 class="text-xl font-bold mb-4">Input Hasil Produksi</h2>

        @if(session('success'))
            <div class="bg-green-100 text-green-800 p-3 mb-4 rounded">
                {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('production-run.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="mb-3">
                <label class="text-sm font-semibold">Tanggal</label>
                <input type="date" name="tanggal" class="border rounded w-full p-2"
                    value="{{ date('Y-m-d') }}">
            </div>

            <div class="mb-3">
                <label class="text-sm font-semibold">Produk</label>
                <select name="product_id" class="border rounded w-full p-2">
                    @foreach($products as $p)
                        <option value="{{ $p->id }}">{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="text-sm font-semibold">Hasil Produksi (gram)</label>
                <input type="number" name="output_gram" class="border rounded w-full p-2" required>
            </div>

            <div class="mb-4">
               <label class="text-sm font-semibold">Persentase Pekerja</label>
               <select name="labor_percentage" class="border rounded w-full p-2">
               @for($i = 0; $i <= 100; $i+=10)
               <option value="{{ $i/100 }}">{{ $i }}%</option>
               @endfor
            </select>
            </div>

            <div class="mb-3">
           <label class="text-sm font-semibold">Foto Produksi</label>
           <input 
                type="file" 
                name="photo" 
                accept="image/*" 
                capture="environment"
                class="border rounded w-full p-2"
                required>
            </div>

            <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                Simpan Produksi
            </button>
        </form>

    </div>

    <div class="bg-white p-4 rounded shadow mb-4">

    <form method="GET" action="{{ route('production-run.index') }}" class="flex gap-2">

        <select name="month" class="border p-2 rounded">
            @for($m = 1; $m <= 12; $m++)
                <option value="{{ $m }}" {{ $selectedMonth == $m ? 'selected' : '' }}>
                    {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                </option>
            @endfor
        </select>

        <select name="year" class="border p-2 rounded">
            @for($y = now()->year - 2; $y <= now()->year + 1; $y++)
                <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>
                    {{ $y }}
                </option>
            @endfor
        </select>

        <button class="bg-blue-600 text-white px-4 rounded">
            Filter
        </button>

    </form>

</div>


    <!-- 🔥 LAPORAN -->
    <div class="bg-white p-5 rounded shadow">

        <h3 class="text-lg font-bold mb-4">
            Laporan Bulan {{ \Carbon\Carbon::create($selectedYear, $selectedMonth)->translatedFormat('F Y') }}
        </h3>

        <div class="overflow-x-auto">
            <table class="w-full text-sm border border-gray-200">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="p-2 border">Tanggal</th>
                        <th class="p-2 border">Produk</th>
                        <th class="p-2 border text-right">Gram</th>
                        <th class="p-2 border text-right">Upah/kg</th>
                        <th class="p-2 border text-center">%</th>
                        <th class="p-2 border text-right">Total</th>
                        @auth
                        @if(auth()->user()->role === 'admin')
                        <th class="p-2 border text-center">Aksi</th>
                        @endif
                        @endauth
                    </tr>
                </thead>

                <tbody>
                    @foreach($runs as $r)
                    <tr class="hover:bg-gray-50">
                        <td class="p-2 border">
                            {{ \Carbon\Carbon::parse($r->created_at)->format('d-m-Y') }}
                        </td>

                        <td class="p-2 border">
                        @if($r->photo)
                        <a href="{{ asset('storage/' . $r->photo) }}" target="_blank" class="text-blue-600 underline">
                        {{ $r->product->name ?? '-' }}
                        </a>
                        @else
                        {{ $r->product->name ?? '-' }}
                       @endif
                        </td>

                        <td class="p-2 border text-right">
                            {{ number_format($r->output_gram) }}
                        </td>

                        <td class="p-2 border text-right">
                            {{ number_format($r->labor_rate_per_gram * 1000) }}
                        </td>

                        <td class="p-2 border text-center">
                            {{ $r->labor_percentage * 100 }}%
                        </td>

                        <td class="p-2 border text-right font-semibold">
                            Rp {{ number_format($r->total_labor_cost) }}
                        </td>
                        @auth
@if(auth()->user()->role === 'admin')
<td class="p-2 border text-center">

    <!-- EDIT -->
    <a href="{{ route('production-run.edit', $r->id) }}"
       class="bg-yellow-500 hover:bg-yellow-600 text-white px-2 py-1 rounded text-xs">
        Edit
    </a>

    <!-- HAPUS -->
    <form action="{{ route('production-run.destroy', $r->id) }}"
          method="POST"
          style="display:inline;">
        @csrf
        @method('DELETE')

        <button type="submit"
            class="bg-red-600 hover:bg-red-700 text-white px-2 py-1 rounded text-xs"
            onclick="return confirm('Yakin hapus data ini?')">
            Hapus
        </button>
    </form>

</td>
@endif
@endauth
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    </div>

    <!-- 🔥 RINGKASAN UPAH -->
<div class="bg-white p-5 rounded shadow mt-6">

    <h3 class="text-lg font-bold mb-4">
        Ringkasan Upah Bulan Ini
    </h3>

    <!-- 🔥 REWARD BULANAN -->
<div class="bg-yellow-50 border border-yellow-300 p-4 rounded mb-4">

    <h4 class="font-bold text-lg mb-2">🎁 Reward Bulanan</h4>

    <div class="bg-white border border-gray-200 p-3 rounded mb-3 text-sm">

    <div class="font-semibold mb-2 text-gray-700">
        🎯 Target Reward Bulanan:
    </div>

    <ul class="list-disc pl-5 space-y-1">
        <li>800.000 gram → <b>Rp 50.000</b></li>
        <li>1.000.000 gram → <b>Rp 100.000</b></li>
        <li>1.300.000 gram → <b>Rp 150.000</b></li>
        <li>1.600.000 gram → <b>Rp 250.000</b></li>
    </ul>

    <div class="mt-3 text-gray-700">
        💡 <b>Semakin tinggi produksi, semakin besar bonus yang didapat.</b>
    </div>

    <div class="mt-2 text-red-600 font-semibold">
        ⚠ Reward hanya dihitung jika target tercapai.
    </div>

</div>

    <div class="text-sm mb-2">
        <b>Total Produksi:</b> {{ number_format($totalGram) }} gram
    </div>

    <div class="text-sm mt-2 mb-4">

    @if($totalGram < 800000)
        <span class="text-red-600 font-semibold">
            🚨 Target pertama belum tercapai. Ayo tingkatkan produksi!
        </span>
    @elseif($totalGram < 1000000)
        <span class="text-yellow-600 font-semibold">
            🔥 Sedikit lagi tembus 1 juta gram!
        </span>
    @elseif($totalGram < 1300000)
        <span class="text-blue-600 font-semibold">
            💪 Mantap! Tinggal dorong lagi ke 1.3 juta!
        </span>
    @elseif($totalGram < 1600000)
        <span class="text-green-600 font-semibold">
            🚀 Hampir maksimal! Gas ke 1.6 juta!
        </span>
    @else
        <span class="text-green-700 font-bold">
            🏆 Target tertinggi tercapai! Pertahankan!
        </span>
    @endif

</div>

    <div class="text-sm mb-2">
        <b>Reward:</b> 
        <span class="font-bold text-green-600">
            Rp {{ number_format($rewardAmount) }}
        </span>
    </div>

    <div class="text-sm mb-3">
        <b>Status:</b> 
        @if($isLocked)
            <span class="text-green-600 font-semibold">Terkunci</span>
        @else
            <span class="text-yellow-600 font-semibold">Belum Dikunci</span>
        @endif
    </div>

    @auth
    @if(auth()->user()->role === 'admin')
       @if(!$isLocked)

    <!-- 🔒 LOCK -->
    <form method="POST" action="{{ route('production-reward.lock') }}">
        @csrf
        <input type="hidden" name="month" value="{{ $selectedMonth }}">
        <input type="hidden" name="year" value="{{ $selectedYear }}">

        <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
        🔒 Lock Reward Bulan Ini
        </button>
    </form>

@else

    <!-- STATUS BAYAR -->
    <div class="mb-3">
        <b>Status Pembayaran:</b>
        @if($isPaid)
            <span class="text-green-600 font-semibold">Sudah Dibayar</span>
        @else
            <span class="text-red-600 font-semibold">Belum Dibayar</span>
        @endif
    </div>

    @if(!$isPaid)
    <!-- 💰 BAYAR -->
    <form method="POST" action="{{ route('production-reward.pay') }}" class="mb-2">
        @csrf
        <input type="hidden" name="month" value="{{ $selectedMonth }}">
        <input type="hidden" name="year" value="{{ $selectedYear }}">

        <button class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">
            💰 Tandai Sudah Dibayar
        </button>
    </form>
    @endif

    <!-- 🔓 UNLOCK -->
    <form method="POST" action="{{ route('production-reward.unlock') }}">
        @csrf
        <input type="hidden" name="month" value="{{ $selectedMonth }}">
        <input type="hidden" name="year" value="{{ $selectedYear }}">

        <button class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded">
            🔓 Buka Kunci Reward
        </button>
    </form>

@endif
</div>
@endif
@endauth

    <table class="w-full text-sm border border-gray-200">
        <tr class="bg-gray-100">
            <th class="p-3 border text-left">Total Upah</th>
            <th class="p-3 border text-left">Pencairan</th>
            <th class="p-3 border text-left">Sisa</th>
        </tr>

        <tr>
            <td class="p-3 border font-semibold text-green-600">
                Rp {{ number_format($totalUpah) }}
            </td>

            <td class="p-3 border">
                Rp {{ number_format($totalPencairan) }}
            </td>

            <td class="p-3 border font-bold">
                Rp {{ number_format($sisa) }}
            </td>
        </tr>
    </table>

</div>

<div class="bg-white p-5 rounded shadow mt-4">

    <h4 class="font-bold mb-3">Pencairan Upah</h4>

    @if(session('error'))
        <div class="bg-red-100 text-red-700 p-2 mb-3">
            {{ session('error') }}
        </div>
    @endif

    @php
    $isEmpty = $sisa <= 0;
    @endphp

    <form method="POST" action="{{ route('production-run.withdraw') }}">
        @csrf

            <div class="mb-3">
            <label>Jumlah Pencairan</label>
            @if($isEmpty)
            <div class="bg-yellow-100 text-yellow-700 p-2 mb-3 rounded">
            ⚠️ Tidak ada sisa upah yang bisa dicairkan
            </div>
            @endif
            <input type="number" name="amount"
                   max="{{ $sisa }}" 
                   class="border w-full p-2 {{ $isEmpty ? 'bg-gray-100 cursor-not-allowed' : '' }}" 
                   {{ $isEmpty ? 'disabled' : '' }}
                   required>
                   
           </div>

            <button 
                  class="px-4 py-2 rounded text-white
                  {{ $isEmpty ? 'bg-gray-400 cursor-not-allowed' : 'bg-red-600 hover:bg-red-700' }}"
                  {{ $isEmpty ? 'disabled' : '' }}
                  >
                 Cairkan
            </button>
       </form>

</div>

<!-- 🔥 HISTORY PENCAIRAN -->
<div class="bg-white p-5 rounded shadow mt-6">

    <h3 class="text-lg font-bold mb-4">
        Riwayat Pencairan Upah
    </h3>

    <div class="overflow-x-auto">
        <table class="w-full text-sm border border-gray-200">
            <thead class="bg-gray-100">
                <tr>
                    <th class="p-2 border">Tanggal</th>
                    <th class="p-2 border text-right">Request</th>
                    <th class="p-2 border text-center">Status</th>
                    <th class="p-2 border text-right">Disetujui</th>
                @auth
                    @if(auth()->user()->role == 'admin')
                    <th class="p-2 border text-center">Aksi</th>
                @endif
                @endauth
                </tr>
            </thead>

            <tbody>
@forelse($withdrawals as $w)
<tr class="hover:bg-gray-50">

    <td class="p-2 border">
        {{ \Carbon\Carbon::parse($w->withdraw_date)->format('d-m-Y') }}
    </td>

    <td class="p-2 border text-right">
        Rp {{ number_format($w->requested_amount) }}
    </td>

    <td class="p-2 border text-center">
        @if($w->status == 'approved')
            <span class="text-green-600 font-semibold">Approved</span>
        @else
            <span class="text-yellow-600 font-semibold">Pending</span>
        @endif
    </td>

    <td class="p-2 border text-right">
        Rp {{ number_format($w->approved_amount ?? 0) }}
    </td>

    @auth
    @if(auth()->user()->role == 'admin')
    <td class="p-2 border text-center">
        @if($w->status == 'pending')
            <form method="POST" action="{{ route('withdraw.approve', $w->id) }}">
                @csrf
                <input type="number" name="approved_amount" 
                    class="border p-1 w-24 mb-1"
                    placeholder="Nominal">

                <button class="bg-green-600 text-white px-2 py-1 rounded text-xs">
                    ACC
                </button>
            </form>
        @else
            -
        @endif
    </td>
    @endif
    @endauth

</tr>
@empty
<tr>
    <td colspan="5" class="p-3 text-center text-gray-500">
        Belum ada pencairan
    </td>
</tr>
@endforelse
</tbody>
        </table>
    </div>

</div>

</div>


</x-app-layout>