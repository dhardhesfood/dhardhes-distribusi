<x-app-layout>

<div class="py-6 px-6 max-w-6xl mx-auto">

    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-bold">
            Tutup Session Stok Sales
        </h2>

        <a href="{{ route('sales-stock-sessions.show', $session->id) }}"
           class="bg-gray-500 text-white px-4 py-2 rounded">
            Kembali
        </a>
    </div>

    @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 p-4 rounded mb-6">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white shadow rounded p-6">

        @if(in_array(auth()->user()->role, ['admin','admin_gudang']))

        <form method="POST"
              action="{{ route('sales-stock-sessions.close', $session->id) }}"
              enctype="multipart/form-data">
            @csrf

            <div class="overflow-x-auto">
            <table class="w-full border text-sm md:text-base min-w-[600px]">
            
                <thead class="bg-gray-100">

                <tr>        
                    
                        <th class="border p-2 text-left">Produk</th>
                        <th class="border p-2 text-center">Stok Sistem</th>
                        <th class="border p-2 text-center">Stok Fisik</th>
                        <th class="border p-2 text-center">Barang Rusak</th>
                        <th class="border p-2 text-center">Selisih</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($session->items as $item)

                        <tr class="text-center">

<td class="border p-2 text-left font-medium text-sm md:text-base">
{{ $item->product->name }}
</td>

<td class="border p-2 font-semibold">
{{ $item->system_remaining_qty }}
</td>

<td class="border p-2">
<input type="number"
name="physical_qty[{{ $item->product_id }}]"
value="{{ $item->system_remaining_qty }}"
min="0"
data-product="{{ $item->product_id }}"
data-system="{{ $item->system_remaining_qty }}"
class="physical-input border px-2 py-1 rounded w-14 md:w-16 text-center">
</td>

<td class="border p-2">
<input type="number"
name="damage_qty[{{ $item->product_id }}]"
value="0"
min="0"
data-product="{{ $item->product_id }}"
class="damage-input border px-2 py-1 rounded w-14 md:w-16 text-center">
</td>

<td class="border p-2 text-center text-gray-600">
<span class="difference" data-product="{{ $item->product_id }}">
0
</span>
</td>

</tr>

                    @endforeach
                </tbody>
            </table>

<div class="mt-6">
    <label class="block text-sm font-semibold mb-2">
        Foto Stok (WAJIB)
    </label>

    <input 
        type="file" 
        name="photo" 
        accept="image/*" 
        capture="environment"
        required
        class="border p-2 rounded w-full"
    >

    <p class="text-xs text-gray-500 mt-1">
        Ambil foto langsung dari kamera
    </p>
</div>

            <div class="mt-6 text-right">
                <button type="submit"
                        class="bg-red-600 text-white px-6 py-2 rounded font-semibold">
                    Tutup Session
                </button>
            </div>

        </form>

        @else

            <div class="text-center text-gray-600 font-medium py-10">
                Anda tidak memiliki akses untuk menutup session.
            </div>

        @endif

    </div>

</div>

<script>

function updateDifference(productId){

let physical = document.querySelector(
'input.physical-input[data-product="'+productId+'"]'
);

let damage = document.querySelector(
'input.damage-input[data-product="'+productId+'"]'
);

let system = parseInt(physical.dataset.system || 0);

let physicalVal = parseInt(physical.value || 0);
let damageVal   = parseInt(damage.value || 0);

let difference = physicalVal - system;

let el = document.querySelector(
'.difference[data-product="'+productId+'"]'
);

el.innerText = difference;

if(difference < 0){
el.classList.add('text-red-600');
el.classList.remove('text-green-600');
}

else if(difference > 0){
el.classList.add('text-green-600');
el.classList.remove('text-red-600');
}

else{
el.classList.remove('text-red-600');
el.classList.remove('text-green-600');
}

}

document.querySelectorAll('.physical-input').forEach(function(input){

input.addEventListener('input', function(){
updateDifference(this.dataset.product);
});

});

document.querySelectorAll('.damage-input').forEach(function(input){

input.addEventListener('input', function(){
updateDifference(this.dataset.product);
});

});

</script>

</x-app-layout>

