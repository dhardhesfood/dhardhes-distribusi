<x-app-layout>
<x-slot name="header">
    <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
        Edit Settlement
    </h2>
</x-slot>

<div class="py-8">
<div class="max-w-3xl mx-auto sm:px-6 lg:px-8">

    <div class="bg-white shadow rounded p-6">

        @if ($errors->any())
            <div class="mb-4 text-red-600">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>- {{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('sales.settlements.update',$settlement->id) }}">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label class="block font-medium">Sales</label>
                <input type="text" class="w-full border rounded p-2"
                       value="{{ $settlement->user->name }}" disabled>
            </div>

            <div class="mb-4">
                <label class="block font-medium">Tanggal</label>
                <input type="text" class="w-full border rounded p-2"
                       value="{{ $settlement->settlement_date->format('d M Y') }}" disabled>
            </div>

            <div class="mb-4">
                <label class="block font-medium">Penjualan Tunai</label>
                <input type="text" class="w-full border rounded p-2"
                       value="Rp {{ number_format($settlement->total_sales_amount,0,',','.') }}" disabled>
            </div>

            <div class="mb-4">
                <label class="block font-medium">Pembayaran Piutang</label>
                <input type="text" class="w-full border rounded p-2"
                       value="Rp {{ number_format($settlement->total_receivable_payment,0,',','.') }}" disabled>
            </div>

            <div class="mb-4">
                <label class="block font-medium">Total Biaya</label>
                <input type="number" step="0.01" name="total_cost"
                       class="w-full border rounded p-2"
                       value="{{ old('total_cost',$settlement->total_cost) }}">
            </div>

            <div class="mb-6">
                <label class="block font-medium">Total Diterima (Actual)</label>
                <input type="number" step="0.01" name="actual_amount"
                       class="w-full border rounded p-2"
                       value="{{ old('actual_amount',$settlement->actual_amount) }}" required>
            </div>

            <div class="flex gap-3">
                <button type="submit"
                        class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded font-semibold">
                    Update Settlement
                </button>

                <a href="{{ route('sales.settlements.index') }}"
                   class="bg-gray-400 hover:bg-gray-500 text-white px-4 py-2 rounded">
                    Batal
                </a>
            </div>

        </form>

    </div>

</div>
</div>
</x-app-layout>
