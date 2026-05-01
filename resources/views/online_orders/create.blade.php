<x-app-layout>

<div class="py-8">
<div class="max-w-5xl mx-auto sm:px-6 lg:px-8">

<div class="bg-white shadow rounded-lg p-6">

    <!-- HEADER -->
    <div class="flex justify-between items-center mb-6">
    <div>
        <h2 class="text-2xl font-bold text-gray-800">
            Order Online
        </h2>
        <p class="text-sm text-gray-500">
            Buat order paket usaha (bisa custom)
        </p>
    </div>

    <div class="flex gap-2">

        <!-- 🔥 CEK ORDER -->
        <a href="/online-orders"
           class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded shadow">
            Cek Order
        </a>

        <!-- KEMBALI -->
        <a href="{{ url('/dashboard') }}"
           class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded shadow">
            Kembali
        </a>

    </div>
</div>
        

    <!-- SUCCESS -->
    @if(session('success'))
        <div class="bg-green-100 text-green-700 p-3 mb-4 rounded">
            {{ session('success') }}
        </div>
    @endif

    <!-- FORM -->
    <form method="POST" action="/online-orders/store" onsubmit="cleanPrice()">
        @csrf

        <!-- CUSTOMER -->
         <div class="mb-4">
    <label class="block text-sm font-semibold mb-1">Tanggal Order</label>
    <input type="date" name="order_date"
        class="w-full border border-gray-300 rounded-lg px-3 py-2"
        value="{{ date('Y-m-d') }}"
        required>
        </div>

        <label>Customer</label>
        <select id="customerSelect" name="customer_id" class="w-full border p-2">
        <option value="">-- Pilih Customer --</option>
        @foreach($customers as $c)
        <option value="{{ $c->id }}"
                data-name="{{ $c->name }}"
                data-phone="{{ $c->phone }}">
                {{ $c->name }} - {{ $c->phone }}
        </option>
        @endforeach
        </select>

        <div class="mt-3 p-3 border rounded bg-gray-50">
    <p class="text-sm font-semibold mb-2">Atau Tambah Customer Baru</p>

    <input type="text" id="inputName" name="new_customer_name"
        placeholder="Nama Customer"
        class="w-full border border-gray-300 rounded px-3 py-2 mb-2">

    <input type="text" id="inputPhone" name="new_customer_phone"
    placeholder="No WA (62xxxx)"
    class="w-full border border-gray-300 rounded px-3 py-2">

        <label>Jenis Pembayaran</label>
        <select name="payment_type" class="w-full border p-2">
        <option value="transfer">Transfer</option>
        <option value="cod">COD</option>
        </select>

        <!-- TEMPLATE -->
        <div class="mb-4">
            <label class="block text-sm font-semibold mb-1">Pilih Paket</label>
            <select id="template" name="template_id"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-white focus:ring focus:ring-blue-200">
                <option value="">-- Pilih Paket --</option>
                @foreach($templates as $t)
                    <option value="{{ $t->id }}">{{ $t->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-4">
       <label class="block text-sm font-semibold mb-1">Harga Jual Paket</label>
       
       <input type="text" id="total_price"
            name="total_price"
            placeholder="Masukkan harga jual paket"
            class="w-full border border-gray-300 rounded-lg px-3 py-2"
            required>
       </div>

       <div class="mb-4">
       <label class="block text-sm font-semibold mb-1">Subsidi Ongkir</label>
       <input type="text" id="shipping_subsidy"
              name="shipping_subsidy"
              placeholder="Masukkan subsidi ongkir"
              class="w-full border border-gray-300 rounded-lg px-3 py-2">
       </div>

        <!-- ITEMS -->
        <div id="items" class="mb-4 space-y-2"></div>

        <!-- BUTTON TAMBAH -->
        <div class="mb-4">
            <button type="button" onclick="addRow()"
                class="bg-gray-600 hover:bg-gray-700 text-white px-3 py-2 rounded shadow text-sm">
                + Tambah Item
            </button>
        </div>

        <!-- CATATAN -->
        <div class="mb-4">
            <label class="block text-sm font-semibold mb-1">Catatan</label>
            <textarea name="notes"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring focus:ring-blue-200"></textarea>
        </div>

        <!-- SUBMIT -->
        <button
            class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded shadow font-semibold">
            Simpan Order
        </button>

    </form>

</div>
</div>
</div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Select2 -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>

let index = 0;

// LOAD TEMPLATE
document.getElementById('template').addEventListener('change', function () {

    let templateId = this.value;

    if (!templateId) return;

    fetch('/online-orders/template/' + templateId)
        .then(res => res.json())
        .then(data => {

            document.getElementById('items').innerHTML = '';
            index = 0;

            data.forEach(item => {
                addRow(item);
            });

        });
});

// TAMBAH ITEM
function addRow(item = null) {

    let html = `
    <div class="flex items-center justify-between border rounded-lg px-3 py-2 bg-gray-50">

        <div class="flex-1 text-sm font-medium text-gray-700">
            ${item ? item.product_name + ' - ' + item.variant_name : 'Custom Item'}
        </div>

        <div class="flex items-center gap-2">

            <input type="hidden" name="items[${index}][product_id]" value="${item ? item.product_id : ''}">
            <input type="hidden" name="items[${index}][variant_id]" value="${item ? item.variant_id : ''}">

            <input type="number"
                name="items[${index}][qty]"
                value="${item ? item.qty : 0}"
                class="w-20 border border-gray-300 rounded px-2 py-1 text-center">

            <button type="button"
                onclick="this.parentElement.parentElement.remove()"
                class="bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded text-xs">
                Hapus
            </button>

        </div>
    </div>
    `;

    document.getElementById('items').insertAdjacentHTML('beforeend', html);
    index++;
}

// AUTO FORMAT NOMOR HP
document.getElementById('inputPhone').addEventListener('blur', function() {

    let val = this.value.replace(/\D/g, '');

    if (val.startsWith('0')) {
        val = '62' + val.substring(1);
    }

    if (!val.startsWith('62')) {
        val = '62' + val;
    }

    this.value = val;
});

// AKTIFKAN SEARCH CUSTOMER
$(document).ready(function() {
    $('#customerSelect').select2({
        placeholder: "Cari customer...",
        allowClear: true,
        width: '100%'
    });
});

$('#customerSelect').on('change', function() {

    let selected = $(this).find(':selected');

    let name = selected.data('name');
    let phone = selected.data('phone');

    if(name){
        $('#inputName').val(name);
    }

    if(phone){
        $('#inputPhone').val(phone);
    }

});

function formatRupiah(angka) {
    return angka.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

document.getElementById('total_price').addEventListener('input', function(e) {

    let value = this.value.replace(/\D/g, '');

    this.value = formatRupiah(value);

});

document.getElementById('shipping_subsidy').addEventListener('input', function(e) {
    let value = this.value.replace(/\D/g, '');
    this.value = formatRupiah(value);
});

function cleanPrice() {
    let price = document.getElementById('total_price');
    let ongkir = document.getElementById('shipping_subsidy');

    if(price) price.value = price.value.replace(/\./g, '');
    if(ongkir) ongkir.value = ongkir.value.replace(/\./g, '');
}

</script>

</x-app-layout>