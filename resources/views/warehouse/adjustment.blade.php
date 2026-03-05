<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Penyesuaian Stok Gudang
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white shadow-sm sm:rounded-lg p-6">

                <form method="POST" action="{{ route('warehouse.adjustment.store') }}">
                    @csrf

                    <table class="w-full border text-sm">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="p-2 border text-left">Produk</th>
                                <th class="p-2 border text-right">Stok Sistem</th>
                                <th class="p-2 border text-right">Stok Real</th>
                                <th class="p-2 border text-right">Selisih</th>
                            </tr>
                        </thead>

                        <tbody>

                            @foreach($products as $product)

                            <tr>
                                <td class="p-2 border">
                                    {{ $product->name }}
                                </td>

                                <td class="p-2 border text-right font-semibold">
                                    {{ $product->stock }}
                                </td>

                                <td class="p-2 border text-right">

                                    <input
                                        type="number"
                                        name="real_stock[{{ $product->id }}]"
                                        value="{{ $product->stock }}"
                                        data-system="{{ $product->stock }}"
                                        data-product="{{ $product->id }}"
                                        class="real-input border rounded px-2 py-1 w-24 text-right"
                                        min="0"
                                    >

                                </td>

                                <td class="p-2 border text-right">
                                    <span class="difference" data-product="{{ $product->id }}">
                                        0
                                    </span>
                                </td>

                            </tr>

                            @endforeach

                        </tbody>
                    </table>

                    <div class="mt-6 text-right">
                        <button
                            type="submit"
                            class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded font-semibold"
                        >
                            Simpan Penyesuaian
                        </button>
                    </div>

                </form>

            </div>

        </div>
    </div>


<script>

function updateDifference(productId){

let input = document.querySelector(
'input.real-input[data-product="'+productId+'"]'
);

let system = parseInt(input.dataset.system || 0);
let real   = parseInt(input.value || 0);

let diff = real - system;

let el = document.querySelector(
'.difference[data-product="'+productId+'"]'
);

el.innerText = diff;

if(diff < 0){
el.classList.add('text-red-600');
el.classList.remove('text-green-600');
}

else if(diff > 0){
el.classList.add('text-green-600');
el.classList.remove('text-red-600');
}

else{
el.classList.remove('text-red-600');
el.classList.remove('text-green-600');
}

}

document.querySelectorAll('.real-input').forEach(function(input){

input.addEventListener('input', function(){
updateDifference(this.dataset.product);
});

});

</script>

</x-app-layout>