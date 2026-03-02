<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
            Piutang Toko
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-4 p-3 bg-red-100 text-red-800 rounded">
                    {{ $errors->first() }}
                </div>
            @endif

            {{-- FILTER STATUS --}}
            <div class="mb-6 flex justify-end">
                <form method="GET" action="{{ route('receivables.index') }}">
                    <select name="status"
                            onchange="this.form.submit()"
                            class="border rounded px-3 py-2 text-sm">

                        <option value="all" {{ ($currentStatus ?? '') == 'all' ? 'selected' : '' }}>
                            Semua Status
                        </option>

                        <option value="unpaid" {{ ($currentStatus ?? '') == 'unpaid' ? 'selected' : '' }}>
                            Belum Bayar
                        </option>

                        <option value="partial" {{ ($currentStatus ?? '') == 'partial' ? 'selected' : '' }}>
                            Sebagian
                        </option>

                        <option value="paid" {{ ($currentStatus ?? '') == 'paid' ? 'selected' : '' }}>
                            Lunas
                        </option>

                    </select>
                </form>
            </div>

            <div class="space-y-6">

                @forelse($receivables as $r)

                    <div class="bg-white shadow-sm rounded-xl p-6 border
                        {{ $r->is_overdue ? 'border-red-400 bg-red-50' : 'border-gray-200' }}">

                        {{-- HEADER --}}
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h3 class="text-lg font-semibold">
                                    {{ $r->transaction->store->name ?? '-' }}
                                </h3>

                                <div class="text-sm text-gray-500">
                                    Jatuh Tempo: {{ $r->due_date ?? '-' }}
                                </div>

                                {{-- INFO ASAL PIUTANG --}}
                                <div class="text-xs text-gray-400 mt-1">
                                    Asal Piutang:
                                    @if($r->transaction && $r->transaction->visit_id)
                                        <a href="{{ route('visits.show', $r->transaction->visit_id) }}"
                                           class="text-blue-600 hover:underline font-medium">
                                            Visit {{ \Carbon\Carbon::parse($r->transaction->visit->visit_date)->format('d M Y') }}
                                        </a>
                                        (ID: {{ $r->transaction->visit_id }})
                                    @else
                                        -
                                    @endif
                                    | Transaksi ID: {{ $r->sales_transaction_id ?? '-' }}
                                </div>
                            </div>

                            @php
                                $statusText = [
                                    'unpaid' => 'Belum Bayar',
                                    'partial' => 'Sebagian',
                                    'paid' => 'Lunas'
                                ];
                            @endphp

                            <span class="px-3 py-1 text-xs rounded text-white
                                @if($r->status === 'unpaid') bg-red-500
                                @elseif($r->status === 'partial') bg-yellow-500
                                @else bg-green-500
                                @endif">
                                {{ $statusText[$r->status] ?? '-' }}
                            </span>
                        </div>

                        {{-- DATA GRID --}}
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm mb-4">

                            <div>
                                <div class="text-gray-500">Total Transaksi</div>
                                <div class="font-medium">
                                    Rp {{ number_format($r->total_amount) }}
                                </div>
                            </div>

                            <div>
                                <div class="text-gray-500">Dibayar Saat Visit</div>
                                <div class="font-medium">
                                    Rp {{ number_format($r->paid_amount) }}
                                </div>
                            </div>

                            <div>
                                <div class="text-gray-500">Sisa Piutang</div>
                                <div class="font-bold text-red-600">
                                    Rp {{ number_format($r->remaining_amount) }}
                                </div>
                            </div>

                            <div>
                                <div class="text-gray-500">Aging</div>
                                <div class="{{ $r->is_overdue ? 'text-red-600 font-bold' : '' }}">
                                    {{ $r->aging_days ?? '-' }} hari
                                </div>
                            </div>

                        </div>

                        {{-- FORM PEMBAYARAN --}}
                        @if($r->status !== 'paid')
                            <div class="border-t pt-4">

                                <form method="POST"
                                      action="{{ route('receivables.pay', $r->id) }}"
                                      class="grid md:grid-cols-4 gap-3 items-end">

                                    @csrf

                                    <div>
                                        <label class="text-xs text-gray-500">Jumlah Bayar</label>
                                        <input type="number"
                                               name="amount"
                                               max="{{ $r->remaining_amount }}"
                                               min="1"
                                               required
                                               class="w-full border rounded px-3 py-2 text-sm">
                                    </div>

                                    <div>
                                        <label class="text-xs text-gray-500">Metode</label>
                                        <select name="payment_method"
                                                class="w-full border rounded px-3 py-2 text-sm">
                                            <option value="cash">Tunai</option>
                                            <option value="transfer">Transfer</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="text-xs text-gray-500">Tanggal</label>
                                        <input type="date"
                                               name="payment_date"
                                               value="{{ now()->format('Y-m-d') }}"
                                               required
                                               class="w-full border rounded px-3 py-2 text-sm">
                                    </div>

                                    <div>
                                        <button type="submit"
                                                class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded">
                                            Bayar
                                        </button>
                                    </div>

                                </form>
                            </div>
                        @endif

                        {{-- RIWAYAT PEMBAYARAN --}}
                        @if($r->payments->count() > 0)
                            <div class="mt-4 border-t pt-4 text-sm text-gray-600">
                                <div class="font-semibold mb-2">
                                    Riwayat Pembayaran
                                </div>

                                <div class="space-y-1">
                                    @foreach($r->payments as $p)
                                        <div class="flex justify-between">
                                            <span>{{ $p->payment_date }}</span>
                                            <span class="font-medium">
                                                Rp {{ number_format($p->amount) }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                    </div>

                @empty
                    <div class="text-center text-gray-500">
                        Tidak ada data piutang.
                    </div>
                @endforelse

            </div>

        </div>
    </div>
</x-app-layout>