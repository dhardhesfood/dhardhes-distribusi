<x-app-layout>

<div class="py-8">
<div class="max-w-3xl mx-auto sm:px-6 lg:px-8">

<div class="bg-white shadow rounded-lg p-6">

    <div class="flex justify-between items-center mb-4">

    <!-- KIRI -->
    <div>
        <h2 class="text-xl font-bold">
            Input Data Iklan (FB Ads)
        </h2>
        <div class="text-xs text-gray-500">
            Input data dari dashboard Facebook Ads
        </div>
    </div>

    <!-- KANAN (TOMBOL) -->
    <div class="flex gap-2">

        <!-- KEMBALI -->
        <a href="{{ url('/ads') }}"
           class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-3 py-2 rounded text-sm">
            ← Kembali
        </a>

        <!-- DASHBOARD -->
        <a href="{{ route('dashboard') }}"
           class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-2 rounded text-sm">
            Dashboard
        </a>

    </div>

</div>

    @if(session('success'))
        <div class="bg-green-100 text-green-700 p-3 mb-4 rounded">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('ads.store') }}">
        @csrf

        <div class="grid gap-4">

            <!-- TANGGAL -->
            <div>
                <label class="text-sm">Tanggal</label>
                <input type="date" name="report_date"
                       value="{{ old('report_date', now()->toDateString()) }}"
                       class="w-full border rounded px-3 py-2">
            </div>

            <!-- BUDGET -->
            <div>
                <label class="text-sm">Budget (Rp)</label>
                <input type="text" name="budget"
                            id="budget"
                            class="w-full border rounded px-3 py-2"
                            placeholder="Contoh: 100.000">
                        </div>

                        <div>
                        <label class="text-sm">Total + PPN 11%</label>
                        <input type="text"
                               id="budget_with_tax"
                               class="w-full border rounded px-3 py-2 bg-gray-100"
                               readonly>
                        </div>

            <!-- TAYANGAN -->
            <div>
                <label class="text-sm">Tayangan ke Landing</label>
                <input type="number" name="tayangan_konten"
                       class="w-full border rounded px-3 py-2">
            </div>

            <!-- KLIK TAUTAN -->
            <div>
                <label class="text-sm">Klik Tautan</label>
                <input type="number" name="klik_tautan"
                       class="w-full border rounded px-3 py-2">
            </div>

            <!-- HASIL -->
            <div>
                <label class="text-sm">Hasil (Klik WA)</label>
                <input type="number" name="hasil"
                       class="w-full border rounded px-3 py-2">
            </div>

        </div>

        <div class="mt-6 flex justify-end">
            <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                Simpan
            </button>
        </div>

    </form>

</div>
</div>
</div>

<script>
const budgetInput = document.getElementById('budget');
const budgetWithTax = document.getElementById('budget_with_tax');

budgetInput.addEventListener('input', function () {

    // ambil angka bersih
    let raw = this.value.replace(/[^0-9]/g, '');
    let number = parseInt(raw || 0);

    // hitung +11%
    let withTax = Math.round(number * 1.11);

    // format rupiah
    if (number) {
        this.value = new Intl.NumberFormat('id-ID').format(number);
        budgetWithTax.value = new Intl.NumberFormat('id-ID').format(withTax);
    } else {
        this.value = '';
        budgetWithTax.value = '';
    }
});
</script>

</x-app-layout>