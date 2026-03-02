<x-app-layout>
<x-slot name="header">
    <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
        Detail Rekap Setoran
    </h2>
</x-slot>

<div class="py-8">
<div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

    <div class="bg-white shadow rounded p-6 space-y-4">

        <div class="text-lg font-semibold mb-4">
            Tanggal: {{ \Carbon\Carbon::parse($date)->format('d M Y') }}
        </div>

        <div class="flex justify-between border-b pb-2">
            <span>Penjualan Cash</span>
            <span>Rp {{ number_format($cash,0,',','.') }}</span>
        </div>

        <div class="flex justify-between border-b pb-2">
            <span>Penjualan Konsinyasi</span>
            <span>Rp {{ number_format($consignment,0,',','.') }}</span>
        </div>

        <div class="flex justify-between border-b pb-2">
            <span>Pembayaran Piutang</span>
            <span>Rp {{ number_format($receivable,0,',','.') }}</span>
        </div>

        <div class="flex justify-between border-b pb-2">
            <span>Admin Fee</span>
            <span>Rp {{ number_format($adminFee,0,',','.') }}</span>
        </div>

        <div class="flex justify-between font-semibold text-lg pt-4 border-t">
            <span>Total Seharusnya</span>
            <span>
                Rp {{ number_format(($cash + $receivable - $adminFee),0,',','.') }}
            </span>
        </div>

        @if($settlement)
            <div class="flex justify-between pt-4">
                <span>Total Disetor</span>
                <span>
                    Rp {{ number_format($settlement->actual_amount,0,',','.') }}
                </span>
            </div>

            <div class="flex justify-between">
                <span>Status</span>
                <span class="font-semibold">
                    {{ strtoupper($settlement->status) }}
                </span>
            </div>
        @else
            <div class="mt-4 p-3 bg-yellow-100 text-yellow-800 rounded">
                Settlement belum dibuat untuk tanggal ini.
            </div>
        @endif

    </div>

    <div class="mt-6">
        <a href="{{ route('sales.settlements.index') }}"
           class="bg-gray-600 text-white px-4 py-2 rounded">
            Kembali
        </a>
    </div>

</div>
</div>
</x-app-layout>
