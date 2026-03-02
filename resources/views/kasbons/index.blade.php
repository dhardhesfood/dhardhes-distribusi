<x-app-layout>
<x-slot name="header">
    <h2 class="font-semibold text-xl sm:text-2xl text-gray-800 leading-tight">
        Kasbon Sales
    </h2>
</x-slot>

<div class="py-6 sm:py-8">
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6 sm:space-y-8">

    {{-- FILTER PERIODE --}}
    <div class="bg-white rounded-2xl shadow p-4 sm:p-5">
        <form method="GET" class="flex flex-wrap gap-3 items-end">

            <div>
                <label class="text-xs text-gray-600">Filter Periode</label>
                <select name="filter"
                        class="border rounded-lg px-3 py-2 text-sm">
                    <option value="">Semua</option>
                    <option value="daily" {{ request('filter')=='daily'?'selected':'' }}>Harian</option>
                    <option value="weekly" {{ request('filter')=='weekly'?'selected':'' }}>Mingguan</option>
                    <option value="monthly" {{ request('filter')=='monthly'?'selected':'' }}>Bulanan</option>
                </select>
            </div>

            <button type="submit"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold">
                Terapkan
            </button>

        </form>
    </div>

    {{-- TOTAL OPEN GLOBAL --}}
    <div class="bg-red-100 rounded-2xl p-4 sm:p-6">
        <div class="text-xs sm:text-sm text-red-900">
            Total Kasbon Open
        </div>
        <div class="text-xl sm:text-3xl font-bold text-red-700">
            Rp {{ number_format($totalOpen,0,',','.') }}
        </div>
    </div>

    {{-- TOMBOL TAMBAH (ADMIN ONLY) --}}
    @if(auth()->user()->role === 'admin')
    <div>
        <a href="{{ route('kasbons.create') }}"
           class="bg-blue-600 hover:bg-blue-700 text-white text-sm sm:text-base px-4 py-2 rounded-lg font-semibold inline-block">
            + Tambah Kasbon
        </a>
    </div>
    @endif

    {{-- GROUP PER SALES --}}
    @forelse($groupedKasbons as $userId => $items)

        @php
            $salesName    = $items->first()->user->name ?? '-';
            $totalKasbon  = $items->where('status','open')->sum('amount_total');
            $totalFee     = $fees[$userId] ?? 0;
            $sisaFee      = $totalFee - $totalKasbon;
        @endphp

        <div class="bg-white rounded-2xl shadow-md overflow-hidden">

            {{-- HEADER SALES --}}
            <div class="p-4 sm:p-5 border-b bg-gray-50">

                <div class="text-sm sm:text-lg font-bold">
                    {{ $salesName }}
                </div>

                {{-- RINGKASAN FEE --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 sm:gap-4 mt-3 text-xs sm:text-sm">

                    <div class="bg-green-100 rounded-lg p-3">
                        <div class="text-gray-600">Total Fee</div>
                        <div class="font-bold text-green-700">
                            Rp {{ number_format($totalFee,0,',','.') }}
                        </div>
                    </div>

                    <div class="bg-red-100 rounded-lg p-3">
                        <div class="text-gray-600">Total Kasbon</div>
                        <div class="font-bold text-red-700">
                            Rp {{ number_format($totalKasbon,0,',','.') }}
                        </div>
                    </div>

                    <div class="bg-blue-100 rounded-lg p-3">
                        <div class="text-gray-600">Sisa Fee</div>
                        <div class="font-bold {{ $sisaFee >= 0 ? 'text-blue-700' : 'text-red-700' }}">
                            Rp {{ number_format($sisaFee,0,',','.') }}
                        </div>
                    </div>

                </div>
            </div>

            {{-- TABLE WRAPPER --}}
            <div class="overflow-x-auto">

                <table class="w-full min-w-[600px] border-collapse text-xs sm:text-sm">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="p-2 sm:p-3 text-left">Tanggal</th>
                            <th class="p-2 sm:p-3 text-right">Nominal</th>
                            <th class="p-2 sm:p-3 text-left">Keterangan</th>
                            <th class="p-2 sm:p-3 text-center">Status</th>
                            <th class="p-2 sm:p-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $kasbon)
                        <tr class="border-t">
                            <td class="p-2 sm:p-3 whitespace-nowrap">
                                {{ $kasbon->created_at->format('d M Y') }}
                            </td>
                            <td class="p-2 sm:p-3 text-right font-semibold whitespace-nowrap">
                                Rp {{ number_format($kasbon->amount_total,0,',','.') }}
                            </td>
                            <td class="p-2 sm:p-3">
                                {{ $kasbon->description }}
                            </td>
                            <td class="p-2 sm:p-3 text-center whitespace-nowrap">
                                @if($kasbon->status === 'open')
                                    <span class="text-red-600 font-semibold text-xs sm:text-sm">
                                        OPEN
                                    </span>
                                @else
                                    <span class="text-green-600 font-semibold text-xs sm:text-sm">
                                        SETTLED
                                    </span>
                                @endif
                            </td>
                            <td class="p-2 sm:p-3 text-center whitespace-nowrap">

                                @if(auth()->user()->role === 'admin')

                                    <div class="flex justify-center gap-2">

                                        <a href="{{ route('kasbons.edit',$kasbon->id) }}"
                                           class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded text-xs font-semibold">
                                            Edit
                                        </a>

                                        @if($kasbon->type !== 'shortage')
                                        <form method="POST"
                                              action="{{ route('kasbons.destroy',$kasbon->id) }}"
                                              onsubmit="return confirm('Yakin ingin menghapus kasbon ini?')">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit"
                                                class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-xs font-semibold">
                                                Hapus
                                            </button>
                                        </form>
                                        @endif

                                    </div>

                                @endif

                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

            </div>
        </div>

    @empty
        <div class="bg-white rounded-2xl p-6 text-center text-gray-500 text-sm">
            Belum ada data kasbon.
        </div>
    @endforelse

</div>
</div>
</x-app-layout>