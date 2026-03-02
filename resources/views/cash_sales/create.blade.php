<x-app-layout>
<div class="py-6 px-6 max-w-7xl mx-auto">

    <h2 class="text-2xl font-bold mb-6">Input Cash Sale</h2>

    <div class="bg-white shadow rounded p-6">

        {{-- SUCCESS MESSAGE --}}
        @if(session('success'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                {{ session('success') }}
            </div>
        @endif

        {{-- ERROR MESSAGE --}}
        @if ($errors->any())
            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                <ul class="list-disc pl-5 text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('cash-sales.store') }}">
            @csrf

            <!-- Informasi Umum -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">

                <div>
                    <label class="block text-sm font-medium mb-1">Tanggal</label>
                    <input type="date" name="sale_date"
                        value="{{ old('sale_date', date('Y-m-d')) }}"
                        class="w-full border rounded px-3 py-2" required>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Metode Pembayaran</label>
                    <select name="payment_method"
                        class="w-full border rounded px-3 py-2" required>
                        <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                        <option value="transfer" {{ old('payment_method') == 'transfer' ? 'selected' : '' }}>Transfer</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Discount</label>
                    <input type="number" name="discount" id="discount"
                        value="{{ old('discount', 0) }}"
                        class="w-full border rounded px-3 py-2"
                        oninput="calculateTotal()">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Nominal Dibayar</label>
                    <input type="number" name="paid_amount" id="paid_amount"
                        value="{{ old('paid_amount', 0) }}"
                        class="w-full border rounded px-3 py-2"
                        required>
                </div>

            </div>

            <!-- Tabel Produk -->
            <div class="mb-4">
                <button type="button"
                        onclick="addRow()"
                        class="bg-blue-600 text-white px-4 py-2 rounded mb-4">
                    + Tambah Produk
                </button>

                <table class="w-full border" id="items-table">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="p-2 text-left">Produk</th>
                            <th class="p-2 text-right">Harga</th>
                            <th class="p-2 text-right">Qty</th>
                            <th class="p-2 text-right">Bonus</th>
                            <th class="p-2 text-right">Subtotal</th>
                            <th class="p-2"></th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

            <!-- Total -->
            <div class="text-right mt-6">
                <div class="text-lg font-semibold">
                    Subtotal: Rp <span id="subtotal_display">0</span>
                </div>
                <div class="text-xl font-bold mt-2">
                    Total: Rp <span id="total_display">0</span>
                </div>
            </div>

            <div class="mt-6 text-right">
                <button type="submit"
                    class="bg-green-600 text-white px-6 py-2 rounded">
                    Simpan Cash Sale
                </button>
            </div>

        </form>
    </div>
</div>

<script>
let products = @json($products);
let rowIndex = 0;

function addRow() {

    let tbody = document.querySelector("#items-table tbody");
    let currentIndex = rowIndex++;

    let row = document.createElement("tr");

    row.innerHTML = `
        <td class="p-2">
            <select name="items[${currentIndex}][product_id]"
                class="w-full border rounded px-2 py-1 product-select"
                onchange="updatePrice(this)">
                <option value="">-- Pilih Produk --</option>
                ${products.map(p => `
                    <option value="${p.id}" data-price="${p.warehouse_price}">
                        ${p.name}
                    </option>
                `).join('')}
            </select>
        </td>

        <td class="p-2 text-right">
            <span class="price-display">0</span>
        </td>

        <td class="p-2">
            <input type="number" name="items[${currentIndex}][qty]"
                value="0"
                class="w-full border rounded px-2 py-1 qty-input"
                oninput="calculateTotal()">
        </td>

        <td class="p-2">
            <input type="number" name="items[${currentIndex}][bonus_qty]"
                value="0"
                class="w-full border rounded px-2 py-1"
                oninput="calculateTotal()">
        </td>

        <td class="p-2 text-right">
            <span class="subtotal-display">0</span>
        </td>

        <td class="p-2 text-center">
            <button type="button"
                onclick="this.closest('tr').remove(); calculateTotal();"
                class="text-red-600 font-bold">
                X
            </button>
        </td>
    `;

    tbody.appendChild(row);
}

function updatePrice(select) {
    let price = select.options[select.selectedIndex]?.dataset.price || 0;
    select.closest('tr').querySelector('.price-display').innerText =
        parseInt(price).toLocaleString();
    calculateTotal();
}

function calculateTotal() {

    let subtotal = 0;

    document.querySelectorAll("#items-table tbody tr").forEach(row => {

        let price = row.querySelector('.product-select')
            ?.selectedOptions[0]?.dataset.price || 0;

        let qty = row.querySelector('.qty-input').value || 0;

        let lineSubtotal = price * qty;

        row.querySelector('.subtotal-display').innerText =
            parseInt(lineSubtotal).toLocaleString();

        subtotal += parseInt(lineSubtotal);
    });

    document.getElementById('subtotal_display').innerText =
        subtotal.toLocaleString();

    let discount = document.getElementById('discount').value || 0;

    let total = subtotal - discount;

    document.getElementById('total_display').innerText =
        total.toLocaleString();
}
</script>

</x-app-layout>