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

        <a href="{{ url('/dashboard') }}"
           class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded shadow">
            Kembali
        </a>
    </div>

    <!-- SUCCESS -->
    @if(session('success'))
        <div class="bg-green-100 text-green-700 p-3 mb-4 rounded">
            {{ session('success') }}
        </div>
    @endif

    <!-- FORM -->
    <form method="POST" action="/online-orders/store">
        @csrf

        <!-- CUSTOMER -->
        <div class="mb-4">
            <label class="block text-sm font-semibold mb-1">Nama Customer</label>
            <input type="text" name="customer_name"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring focus:ring-blue-200"
                required>
        </div>

        <!-- TEMPLATE -->
        <div class="mb-4">
            <label class="block text-sm font-semibold mb-1">Pilih Paket</label>
            <select id="template"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-white focus:ring focus:ring-blue-200">
                <option value="">-- Pilih Paket --</option>
                @foreach($templates as $t)
                    <option value="{{ $t->id }}">{{ $t->name }}</option>
                @endforeach
            </select>
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

</script>

</x-app-layout>