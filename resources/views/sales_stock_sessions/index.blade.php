<x-app-layout>

<div class="py-6 px-4 sm:px-6 max-w-6xl mx-auto">

    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3 mb-6">
        <h2 class="text-xl font-bold">
            Sales Stock Sessions
        </h2>

        @if(in_array(auth()->user()->role, ['admin','admin_gudang']))
            <a href="{{ route('sales-stock-sessions.create') }}"
               class="bg-blue-600 text-white px-4 py-2 rounded shadow text-sm whitespace-nowrap">
                + Mulai Session
            </a>
        @endif
    </div>

    @if(session('success'))
        <div class="bg-green-500 text-white p-4 rounded mb-6">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white shadow rounded p-3 sm:p-4">

        <div class="overflow-x-auto">

            <table class="min-w-[560px] w-full border text-xs">
                <thead class="bg-gray-100 text-sm">
                    <tr>
                        <th class="border px-2 py-2 text-left">Sales</th>
                        <th class="border px-2 py-2 text-center">Mulai</th>
                        <th class="border px-2 py-2 text-center">Selesai</th>
                        <th class="border px-2 py-2 text-center">Status</th>
                        <th class="border px-2 py-2 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sessions as $session)
                        <tr class="text-center">
                            <td class="border px-2 py-2 text-left whitespace-nowrap">
                                {{ $session->user->name ?? '-' }}
                            </td>

                            {{-- MULAI (pakai created_at untuk jam real) --}}
                            <td class="border px-2 py-2">
                                @if($session->start_date)
                            <div class="leading-tight text-[11px] text-gray-800">
                               {{ \Carbon\Carbon::parse($session->start_date)->format('d-m-Y') }}
                       </div>

                            <div class="leading-tight text-[10px] text-gray-600">
                               {{ $session->created_at ? $session->created_at->format('H:i') : '-' }}
                       </div>
                            @else
                            -
                           @endif
                            </td>

                            {{-- SELESAI --}}
                            <td class="border px-2 py-2">
                                @if($session->end_date)
                                    <div class="leading-tight text-[11px] text-gray-800">
                                        {{ \Carbon\Carbon::parse($session->end_date)->format('d-m-Y') }}
                                    </div>
                                    <div class="leading-tight text-[10px] text-gray-600">
                                        {{-- Jam ambil dari updated_at (waktu close) --}}
                                        {{ $session->updated_at ? $session->updated_at->format('H:i') : '-' }}
                                    </div>
                                @else
                                    -
                                @endif
                            </td>

                            <td class="border px-2 py-2">
                                @if($session->status === 'open')
                                    <span class="bg-yellow-500 text-white px-2 py-1 rounded text-[10px]">
                                        OPEN
                                    </span>
                                @elseif($session->status === 'minus')
                                    <span class="bg-red-600 text-white px-2 py-1 rounded text-[10px]">
                                        STOK MINUS
                                    </span>
                                @elseif($session->status === 'done')
                                    <span class="bg-green-600 text-white px-2 py-1 rounded text-[10px]">
                                        SELESAI
                                    </span>
                                @else
                                    <span class="bg-gray-400 text-white px-2 py-1 rounded text-[10px]">
                                        -
                                    </span>
                                @endif
                            </td>

                            <td class="border px-2 py-2 whitespace-nowrap">
                                <a href="{{ route('sales-stock-sessions.show', $session->id) }}"
                                   class="text-blue-600 underline text-xs">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="border p-4 text-center text-gray-500">
                                Belum ada session
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

        </div>

    </div>

</div>

</x-app-layout>