<x-app-layout>

<div class="p-6 max-w-5xl mx-auto">

    <h2 class="text-xl font-bold mb-4">Buat Paket</h2>

    <form method="POST" action="/package-templates/store">
        @csrf

        <div class="mb-4">
            <label>Nama Paket</label>
            <input type="text" name="name" class="w-full border p-2 rounded" required>
        </div>

        <div id="items"></div>

        <button type="button" onclick="addRow()" class="bg-gray-600 text-white px-3 py-1 rounded mt-2">
            + Tambah Item
        </button>

        <br><br>

        <button class="bg-blue-600 text-white px-4 py-2 rounded">
            Simpan Paket
        </button>

    </form>

</div>

<script>

let index = 0;

function addRow() {

    let html = `
    <div class="flex gap-2 mb-2">

        <select onchange="loadVariants(this, ${index})"
            name="items[${index}][product_id]" class="border p-1">
            <option value="">Pilih Produk</option>
            @foreach($products as $p)
                <option value="{{ $p->id }}">{{ $p->name }}</option>
            @endforeach
        </select>

        <select name="items[${index}][variant_id]" id="variant_${index}" class="border p-1">
            <option value="">Varian</option>
        </select>

        <input type="number" name="items[${index}][qty]" class="border p-1 w-20" placeholder="Qty">

        <button type="button" onclick="this.parentElement.remove()">X</button>

    </div>
    `;

    document.getElementById('items').insertAdjacentHTML('beforeend', html);
    index++;
}

function loadVariants(el, i) {

    fetch('/package-templates/variants/' + el.value)
        .then(res => res.json())
        .then(data => {

            let html = '<option value="">Varian</option>';

            data.forEach(v => {
                html += `<option value="${v.id}">${v.name}</option>`;
            });

            document.getElementById('variant_' + i).innerHTML = html;
        });
}

</script>

</x-app-layout>