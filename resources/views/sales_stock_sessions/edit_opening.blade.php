<x-app-layout>

<div class="py-6 px-4 max-w-4xl mx-auto">

    <h2 class="text-xl font-bold mb-6">
        Edit Opening Stock (Admin Only)
    </h2>

    <div class="bg-yellow-100 text-yellow-800 p-3 rounded mb-4 text-sm">
        ⚠️ Perubahan akan menyesuaikan stok gudang & histori movement.
    </div>

    <form method="POST" action="{{ route('sales-stock-sessions.update-opening', $session->id) }}">
        @csrf

        <div class="bg-white shadow rounded p-4">

            <table class="w-full text-sm border">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="border p-2 text-left">Produk</th>
                        <th class="border p-2 text-center">Opening Lama</th>
                        <th class="border p-2 text-center">Opening Baru</th>
                        <th class="border p-2 text-center">Selisih</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($session->items as $item)
                    <tr class="text-center">
                        <td class="border p-2 text-left">
                            {{ $item->product->name }}
                        </td>

                        <td class="border p-2 font-semibold">
                            {{ $item->opening_qty }}
                        </td>

                        <td class="border p-2">
                            <input type="number"
                                name="opening[{{ $item->product_id }}]"
                                value="{{ $item->opening_qty }}"
                                class="border rounded p-1 w-24 text-center opening-input"
                                data-old="{{ $item->opening_qty }}">
                        </td>

                        <td class="border p-2 font-semibold selisih text-gray-700">
                            0
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="mt-6 flex justify-between">
                <a href="{{ route('sales-stock-sessions.show', $session->id) }}"
                   class="bg-gray-500 text-white px-4 py-2 rounded">
                    Kembali
                </a>

                <button type="submit"
                    class="bg-red-600 text-white px-6 py-2 rounded font-semibold">
                    Simpan Perubahan
                </button>
            </div>

        </div>

    </form>

</div>

<script>
document.querySelectorAll('.opening-input').forEach(function(input){

    input.addEventListener('input', function(){

        let oldVal = parseInt(this.dataset.old || 0);
        let newVal = parseInt(this.value || 0);

        let diff = newVal - oldVal;

        let selisihCell = this.closest('tr').querySelector('.selisih');

        selisihCell.innerText = diff;

        if(diff > 0){
            selisihCell.classList.remove('text-red-600');
            selisihCell.classList.add('text-green-600');
        } else if(diff < 0){
            selisihCell.classList.remove('text-green-600');
            selisihCell.classList.add('text-red-600');
        } else {
            selisihCell.classList.remove('text-green-600','text-red-600');
            selisihCell.classList.add('text-gray-700');
        }

    });

});
</script>

</x-app-layout>