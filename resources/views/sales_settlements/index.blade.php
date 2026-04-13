<x-app-layout>
<x-slot name="header">
    <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
        Rekap Setoran Harian Sales
    </h2>
</x-slot>

<div class="py-8">
<div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

    {{-- FILTER --}}
    <div class="bg-white shadow rounded p-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">

            <input type="date" name="tanggal_dari"
                value="{{ request('tanggal_dari') }}"
                class="border p-2 rounded">

            <input type="date" name="tanggal_sampai"
                value="{{ request('tanggal_sampai') }}"
                class="border p-2 rounded">

            <select name="user_id" class="border p-2 rounded">
                <option value="">Semua Sales</option>
                @foreach($users as $user)
                @if(auth()->user()->role === 'admin' || $user->id === auth()->id())
                    <option value="{{ $user->id }}"
                        {{ request('user_id') == $user->id ? 'selected' : '' }}>
                        {{ $user->name }}
                    </option>
                    @endif
                @endforeach
            </select>

            <select name="status" class="border p-2 rounded">
                <option value="">Semua Status</option>
                <option value="draft" {{ request('status')=='draft'?'selected':'' }}>Draft</option>
                <option value="closed" {{ request('status')=='closed'?'selected':'' }}>Closed</option>
            </select>

            <div class="md:col-span-4 flex space-x-3">
                <button class="bg-blue-600 text-white px-4 py-2 rounded">
                    Filter
                </button>
                <a href="{{ route('sales.settlements.index') }}"
                   class="bg-gray-400 text-white px-4 py-2 rounded">
                    Reset
                </a>
            </div>

        </form>
    </div>


    {{-- ================= DESKTOP TABLE ================= --}}
    <div class="hidden md:block bg-white shadow rounded overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="border p-3 text-left">Tanggal</th>
                    <th class="border p-3 text-left">Sales</th>
                    <th class="border p-3 text-right">Cash</th>
                    <th class="border p-3 text-right">Piutang</th>
                    <th class="border p-3 text-right">Admin Fee</th>
                    <th class="border p-3 text-right">Diterima</th>
                    <th class="border p-3 text-center">Status</th>
                    <th class="border p-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
            @forelse($settlements as $row)
                <tr>
                    <td class="border p-3">
                        {{ \Carbon\Carbon::parse($row->settlement_date)->format('d M Y') }}
                    </td>

                    <td class="border p-3">
                        {{ $row->user_name }}
                    </td>

                    <td class="border p-3 text-right">
                        Rp {{ number_format($row->total_cash,0,',','.') }}
                    </td>

                    <td class="border p-3 text-right">
                        Rp {{ number_format($row->total_receivable,0,',','.') }}
                    </td>

                    <td class="border p-3 text-right">
                        Rp {{ number_format($row->total_admin_fee,0,',','.') }}
                    </td>

                    <td class="border p-3 text-right">
                        Rp {{ number_format($row->actual_amount,0,',','.') }}
                    </td>

                    <td class="border p-3 text-center">
                        @if($row->status === 'closed')
                            <span class="bg-green-600 text-white px-3 py-1 rounded text-xs">
                                Closed
                            </span>
                        @else
                            <span class="bg-yellow-500 text-white px-3 py-1 rounded text-xs">
                                Draft
                            </span>
                        @endif
                    </td>

                    <td class="border p-3 text-center space-x-2">

                        <a href="{{ route('sales.settlements.show', [$row->user_id, \Carbon\Carbon::parse($row->settlement_date)->format('Y-m-d')]) }}"
                           class="bg-indigo-600 text-white px-3 py-1 rounded text-sm">
                            Detail
                        </a>

                        @if(auth()->user()->role === 'admin' && $row->status !== 'closed')
                            <form method="POST"
                                  action="{{ route('sales.settlements.setor') }}"
                                  class="inline-flex items-center space-x-2">
                                @csrf
                                <input type="hidden" name="user_id" value="{{ $row->user_id }}">
                                <input type="hidden" name="settlement_date" value="{{ \Carbon\Carbon::parse($row->settlement_date)->format('Y-m-d') }}">
                                <input type="number"
                                       name="actual_amount"
                                       placeholder="Nominal"
                                       required
                                       class="border p-1 rounded w-28 text-sm">
                                <button class="bg-green-600 text-white px-3 py-1 rounded text-sm">
                                    Setor
                                </button>
                            </form>
                        @endif

                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8"
                        class="border p-4 text-center text-gray-500">
                        Belum ada data transaksi.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>


    {{-- ================= MOBILE CARD ================= --}}
    <div class="md:hidden space-y-4">
        @forelse($settlements as $row)
            <div class="bg-white shadow rounded p-4 space-y-2 text-sm">

                <div class="flex justify-between">
                    <span class="font-semibold">
                        {{ \Carbon\Carbon::parse($row->settlement_date)->format('d M Y') }}
                    </span>
                    @if($row->status === 'closed')
                        <span class="bg-green-600 text-white px-2 py-1 rounded text-xs">
                            Closed
                        </span>
                    @else
                        <span class="bg-yellow-500 text-white px-2 py-1 rounded text-xs">
                            Draft
                        </span>
                    @endif
                </div>

                <div>Sales: {{ $row->user_name }}</div>
                <div>Cash: Rp {{ number_format($row->total_cash,0,',','.') }}</div>
                <div>Piutang: Rp {{ number_format($row->total_receivable,0,',','.') }}</div>
                <div>Admin Fee: Rp {{ number_format($row->total_admin_fee,0,',','.') }}</div>
                <div>Diterima: Rp {{ number_format($row->actual_amount,0,',','.') }}</div>

                <div class="pt-2 space-y-2">

                    <a href="{{ route('sales.settlements.show', [$row->user_id, \Carbon\Carbon::parse($row->settlement_date)->format('Y-m-d')]) }}"
                       class="block text-center bg-indigo-600 text-white px-3 py-2 rounded">
                        Detail
                    </a>

                    @if(auth()->user()->role === 'admin' && $row->status !== 'closed')
                        <form method="POST"
                              action="{{ route('sales.settlements.setor') }}"
                              class="space-y-2">
                            @csrf
                            <input type="hidden" name="user_id" value="{{ $row->user_id }}">
                            <input type="hidden" name="settlement_date" value="{{ \Carbon\Carbon::parse($row->settlement_date)->format('Y-m-d') }}">
                            <input type="number"
                                   name="actual_amount"
                                   placeholder="Nominal Setor"
                                   required
                                   class="border p-2 rounded w-full text-sm">
                            <button class="w-full bg-green-600 text-white px-3 py-2 rounded">
                                Setor
                            </button>
                        </form>
                    @endif

                </div>

            </div>
        @empty
            <div class="bg-white shadow rounded p-4 text-center text-gray-500">
                Belum ada data transaksi.
            </div>
        @endforelse
    </div>

</div>
</div>
</x-app-layout>