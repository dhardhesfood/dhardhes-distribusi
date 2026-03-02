<x-app-layout>
<div class="p-6 max-w-3xl mx-auto text-gray-900">

    {{-- HEADER INFO TOKO --}}
    <div class="bg-white shadow rounded p-5 mb-6 border">
        <div class="text-xl font-bold">
            {{ $visit->store->name }}
        </div>
        <div class="text-sm mt-1">
            Area: {{ optional($visit->store->area)->name ?? '-' }}
        </div>
        <div class="text-sm mt-1">
            {{ \Carbon\Carbon::parse($visit->visit_date)->format('d M Y H:i') }}
        </div>
    </div>

    {{-- TAMBAH PRODUK --}}
    <div class="bg-white shadow rounded p-5 mb-6 border">
        <form method="POST" action="{{ route('visits.add_product', $visit->id) }}">
            @csrf
            <div class="flex gap-3 items-end">
                <div class="flex-1">
                    <label class="block font-semibold mb-1">
                        Tambah Produk Baru
                    </label>
                    <select name="product_id"
                            class="w-full border p-2 rounded"
                            required>
                        <option value="">-- Pilih Produk --</option>
                        @php
                            $existingProductIds = $visit->items->pluck('product_id')->toArray();
                            $allProducts = \App\Models\Product::whereNull('deleted_at')->get();
                        @endphp
                        @foreach($allProducts as $product)
                            @if(!in_array($product->id, $existingProductIds))
                                <option value="{{ $product->id }}">
                                    {{ $product->name }}
                                </option>
                            @endif
                        @endforeach
                    </select>
                </div>

                <button type="submit"
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded font-semibold">
                    + Tambah
                </button>
            </div>
        </form>
    </div>

    {{-- FORM VISIT --}}
    <form method="POST" action="{{ route('visits.submit', $visit->id) }}" id="visitForm">
        @csrf
        @if(auth()->user()->role === 'admin')
<div class="mb-4">
    <label class="block font-semibold mb-1">Tanggal Visit</label>
    <input type="date"
           name="visit_date"
           value="{{ old('visit_date', \Carbon\Carbon::parse($visit->visit_date)->format('Y-m-d')) }}"
           class="w-full border p-2 rounded">
</div>
@endif

        {{-- LIST PRODUK --}}
        @foreach($visit->items as $item)
        <div class="bg-white shadow rounded p-4 mb-4 border product-row"
             data-initial="{{ $item->initial_stock }}"
             data-price="{{ $item->price_snapshot }}">

            <div class="flex justify-between mb-3">
                <div class="font-semibold">
                    {{ $item->product->name }}
                </div>
                <div class="text-sm">
                    Stok: {{ $item->initial_stock }} |
                    Rp {{ number_format($item->price_snapshot,0,',','.') }}
                </div>
            </div>

            <div class="grid grid-cols-3 gap-3 text-sm">

                <div>
                    <label>Sisa</label>
                    <input type="number"
                           name="return_qty[{{ $item->id }}]"
                           value="{{ $item->initial_stock }}"
                           min="0"
                           max="{{ $item->initial_stock }}"
                           class="w-full border p-2 rounded return-input">
                </div>

                <div>
                    <label>Tambah</label>
                    <input type="number"
                           name="new_delivery_qty[{{ $item->id }}]"
                           value="0"
                           min="0"
                           class="w-full border p-2 rounded">
                </div>

                <div>
                    <label>Tarik</label>
                    <input type="number"
                           name="stock_reduction_qty[{{ $item->id }}]"
                           value="0"
                           min="0"
                           class="w-full border p-2 rounded">
                </div>

            </div>

            <div class="mt-2 text-sm">
                Terjual:
                <span class="font-bold text-blue-600 soldQty">0</span>
            </div>

        </div>
        @endforeach

        {{-- BONUS PRODUK --}}
        <div class="bg-white shadow rounded p-5 mb-6 border">
            <div class="font-bold text-base mb-3">
                Bonus Produk (Gratis)
            </div>

            <div id="bonus-container"></div>

            <button type="button"
                    onclick="addBonus()"
                    class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded font-semibold">
                + Tambah Bonus
            </button>
        </div>

        {{-- RINCIAN --}}
        <div class="bg-white shadow rounded p-5 mb-6 border text-sm">

            <div class="flex justify-between mb-1">
                <span>Total Penjualan</span>
                <span id="total_penjualan">Rp 0</span>
            </div>

            <div class="flex justify-between mb-1">
                <span>Biaya Admin</span>
                <span id="display_admin">Rp 0</span>
            </div>

            <hr class="my-2">

            <div class="flex justify-between font-bold text-base">
                <span>Total Tagihan</span>
                <span id="total_tagihan">Rp 0</span>
            </div>

        </div>

        {{-- ADMIN FEE --}}
        <div class="mb-4">
            <label class="block font-semibold mb-1">Biaya Admin</label>
            <input type="number"
                   name="admin_fee"
                   id="admin_fee"
                   value="0"
                   min="0"
                   class="w-full border p-2 rounded">
        </div>

        {{-- CASH PAID --}}
        <div class="mb-6">
            <label class="block font-semibold mb-1">Jumlah Dibayar</label>
            <input type="number"
                   name="cash_paid"
                   id="cash_paid"
                   min="0"
                   class="w-full border p-2 rounded">
        </div>

        <button type="button"
                onclick="openConfirmModal()"
                class="w-full bg-blue-600 hover:bg-blue-700 text-white p-3 rounded font-semibold text-lg">
            Selesaikan Kunjungan
        </button>

    </form>
</div>

{{-- MODAL KONFIRMASI --}}
<div id="confirmModal"
     class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">

    <div class="bg-white rounded-xl shadow-lg max-w-md w-full p-6">

        <div class="text-lg font-bold mb-3">
            Konfirmasi Penyelesaian
        </div>

        <div class="text-sm text-gray-700 mb-4 leading-relaxed">
            Pastikan:
            <ul class="list-disc pl-5 mt-2 space-y-1">
                <li>Nota sudah dicek dengan benar</li>
                <li>Jumlah dibayar sudah sesuai</li>
                <li>Data tidak bisa diubah setelah disimpan</li>
            </ul>
        </div>

        <div class="flex justify-end gap-3">
            <button onclick="closeConfirmModal()"
                    class="px-4 py-2 rounded bg-gray-300 hover:bg-gray-400 text-gray-800">
                Batal
            </button>

            <button onclick="submitVisitForm()"
                    class="px-4 py-2 rounded bg-blue-600 hover:bg-blue-700 text-white font-semibold">
                Ya, Saya Sudah Cek Nota
            </button>
        </div>

    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {

    function calculate() {

        let totalPenjualan = 0;

        document.querySelectorAll(".product-row").forEach(function(row){

            let initial = parseInt(row.dataset.initial);
            let price   = parseInt(row.dataset.price);

            let retur = parseInt(row.querySelector(".return-input").value) || 0;

            if (retur > initial) retur = initial;

            let sold = initial - retur;
            if (sold < 0) sold = 0;

            row.querySelector(".soldQty").innerText = sold;

            totalPenjualan += sold * price;
        });

        let adminFee = parseInt(document.getElementById("admin_fee").value) || 0;
        let totalTagihan = totalPenjualan - adminFee;

        document.getElementById("total_penjualan").innerText =
            "Rp " + totalPenjualan.toLocaleString("id-ID");

        document.getElementById("display_admin").innerText =
            "Rp " + adminFee.toLocaleString("id-ID");

        document.getElementById("total_tagihan").innerText =
            "Rp " + totalTagihan.toLocaleString("id-ID");
    }

    document.querySelectorAll("input").forEach(function(el){
        el.addEventListener("input", calculate);
    });

    calculate();
});

function openConfirmModal() {
    document.getElementById("confirmModal").classList.remove("hidden");
    document.getElementById("confirmModal").classList.add("flex");
}

function closeConfirmModal() {
    document.getElementById("confirmModal").classList.add("hidden");
    document.getElementById("confirmModal").classList.remove("flex");
}

function submitVisitForm() {
    document.getElementById("visitForm").submit();
}

function addBonus() {

    let container = document.getElementById('bonus-container');

    let html = `
        <div class="flex gap-3 mb-3">
            <select name="bonus_product_id[]"
                    class="border p-2 rounded w-2/3" required>
                @foreach(\App\Models\Product::whereNull('deleted_at')->get() as $product)
                    <option value="{{ $product->id }}">
                        {{ $product->name }}
                    </option>
                @endforeach
            </select>

            <input type="number"
                   name="bonus_qty[]"
                   min="1"
                   class="border p-2 rounded w-1/3"
                   required>

            <button type="button"
                    onclick="this.parentElement.remove()"
                    class="text-red-600 font-bold px-2">
                ×
            </button>
        </div>
    `;

    container.insertAdjacentHTML('beforeend', html);
}
</script>

</x-app-layout>