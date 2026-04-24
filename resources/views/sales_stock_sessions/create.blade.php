<x-app-layout>

<div class="py-6 px-6 max-w-5xl mx-auto">

    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-bold">
            Mulai Session Stok Sales
        </h2>

        <a href="{{ route('sales-stock-sessions.index') }}"
           class="bg-gray-500 text-white px-4 py-2 rounded">
            Kembali
        </a>
    </div>

    @if ($errors->any())
        <div class="bg-red-500 text-white p-4 rounded mb-6">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white shadow rounded p-6">

        <form method="POST" action="{{ route('sales-stock-sessions.store') }}">
            @csrf

            {{-- SALES --}}
            <div class="mb-6">
                <label class="block font-semibold mb-2">
                    Pilih Sales
                </label>

                <select name="user_id"
                        required
                        class="border p-2 rounded w-full">
                    <option value="">-- Pilih Sales --</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}">
                            {{ $user->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- TANGGAL SESSION --}}
            <div class="mb-6">
                <label class="block font-semibold mb-2">
                    Tanggal Session
                </label>

                <input type="date"
                       name="start_date"
                       value="{{ old('start_date', now()->format('Y-m-d')) }}"
                       class="border p-2 rounded w-full">
            </div>

            <div>
            <h3 class="font-semibold mb-4">
            Input Stok Awal (Pilih Produk)
            </h3>

            <div id="product-wrapper" class="space-y-3"></div>

            <button type="button"
            onclick="addRow()"
            class="mt-3 bg-blue-600 text-white px-4 py-2 rounded text-sm w-full">
            + Tambah Produk
           </button>
           </div>

        <div class="mt-8">
            <button type="submit"
            class="w-full bg-green-600 text-white py-3 rounded-lg font-semibold text-lg shadow hover:bg-green-700 active:scale-95 transition">
            Mulai Session
           </button>
        </div>

        </form>

    </div>

</div>

<script>
document.querySelector('form').addEventListener('submit', function(e){

    let inputs = document.querySelectorAll('input[name^="opening_qty"]');
    let total = 0;

    inputs.forEach(function(input){
        total += parseInt(input.value || 0);
    });

    if(total <= 0){
        alert('Minimal isi 1 produk dengan qty > 0');
        e.preventDefault();
    }

});
</script>

<script>

let products = @json($products);

function addRow(){

    let wrapper = document.getElementById('product-wrapper');

    let card = document.createElement('div');
    card.classList.add('border','rounded','p-4','shadow-sm','bg-gray-50');

    let options = '<option value="">-- Pilih Produk --</option>';

    products.forEach(function(p){
        options += `<option value="${p.id}" data-stock="${p.warehouse_stock}">
            ${p.name} (Stok: ${p.warehouse_stock})
        </option>`;
    });

    card.innerHTML = `
        <div class="flex flex-col gap-3">

            <select name="product_id[]" 
                class="product-select border p-2 rounded w-full">
                ${options}
            </select>

            <div class="text-xs text-gray-500 stock-info">
                Stok: -
            </div>

            <div class="flex items-center gap-2 justify-center">

                <button type="button" onclick="minusQty(this)"
                    class="bg-gray-400 text-white px-3 py-1 rounded">-</button>

                <input type="number"
                    name="qty[]"
                    value="0"
                    min="0"
                    class="border p-2 rounded w-20 text-center qty-input">

                <button type="button" onclick="plusQty(this)"
                    class="bg-green-600 text-white px-3 py-1 rounded">+</button>

            </div>

            <button type="button"
                onclick="removeRow(this)"
                class="bg-red-500 text-white px-3 py-1 rounded text-xs">
                Hapus
            </button>

        </div>
    `;

    wrapper.appendChild(card);
}

function plusQty(btn){
    let input = btn.parentElement.querySelector('.qty-input');
    input.value = parseInt(input.value || 0) + 1;
}

function minusQty(btn){
    let input = btn.parentElement.querySelector('.qty-input');
    let val = parseInt(input.value || 0);
    if(val > 0){
        input.value = val - 1;
    }
}

function removeRow(btn){
    btn.parentElement.remove();
}

/*
-----------------------------------------
CEK DUPLIKAT + VALIDASI
-----------------------------------------
*/
document.querySelector('form').addEventListener('submit', function(e){

    let selects = document.querySelectorAll('.product-select');
    let productIds = [];

    let totalQty = 0;

    for(let select of selects){

        let val = select.value;

        if(!val){
            alert('Semua produk harus dipilih');
            e.preventDefault();
            return;
        }

        if(productIds.includes(val)){
            alert('Produk tidak boleh duplikat');
            e.preventDefault();
            return;
        }

        productIds.push(val);
    }

    let qtyInputs = document.querySelectorAll('input[name="qty[]"]');

    qtyInputs.forEach(function(input){
        totalQty += parseInt(input.value || 0);
    });

    if(totalQty <= 0){
        alert('Minimal 1 produk dengan qty > 0');
        e.preventDefault();
    }

});

/*
-----------------------------------------
AUTO TAMBAH 1 ROW SAAT LOAD
-----------------------------------------
*/
window.onload = function(){
    addRow();
};

</script>

</x-app-layout>