<x-app-layout>

<div class="p-6 max-w-4xl mx-auto">

    <h2 class="text-xl font-bold mb-4">Setting Komposisi Pack</h2>

    {{-- 🔥 FORM FILTER PRODUK --}}
    <form method="GET" action="/recipes">
        <div class="mb-4">
            <label>Produk</label>
            <select name="product_id" class="border p-2 w-full" onchange="this.form.submit()">
                @foreach($products as $p)
                    <option value="{{ $p->id }}" 
                        {{ $productId == $p->id ? 'selected' : '' }}>
                        {{ $p->name }}
                    </option>
                @endforeach
            </select>
        </div>
    </form>

    {{-- 🔥 FORM SIMPAN RECIPE --}}
    <form method="POST" action="/recipes/store">
        @csrf

        <input type="hidden" name="product_id" value="{{ $productId }}">

        <div class="border p-4 rounded">

            <h3 class="font-semibold mb-2">Komposisi per Pack</h3>

            @foreach($variants as $v)
                <div class="flex items-center gap-3 mb-2">
                    <input type="hidden" name="variants[{{ $loop->index }}][id]" value="{{ $v->id }}">

                    <div class="w-48">
                        {{ $v->name }}
                    </div>

                    <input 
                        type="number" 
                        name="variants[{{ $loop->index }}][qty]" 
                        class="border p-1 w-20"
                        placeholder="0"
                    >
                </div>
            @endforeach

        </div>

        <button class="mt-4 bg-blue-600 text-white px-4 py-2 rounded">
            Simpan Recipe
        </button>

    </form>

    {{-- 🔥 LIST RECIPE --}}
    <div class="mt-8">

        <h3 class="text-lg font-bold mb-3">Recipe Aktif</h3>

        @foreach($products as $p)

            @if(isset($recipes[$p->id]))

                @php
                    $recipeId = $recipes[$p->id];
                    $items = $recipeItems[$recipeId] ?? [];
                @endphp

                <div class="border rounded p-4 mb-4 bg-gray-50">

                    <div class="font-semibold mb-2">
                        {{ $p->name }}
                    </div>

                    <table class="text-sm w-full">
                        @foreach($items as $item)
                            <tr>
                                <td class="py-1">
                                    {{ $variantNames[$item->product_variant_id] ?? '-' }}
                                </td>
                                <td class="py-1 text-right">
                                    {{ $item->qty_per_pack }} pcs
                                </td>
                            </tr>
                        @endforeach
                    </table>

                </div>

            @endif

        @endforeach

    </div>

</div>

</x-app-layout>