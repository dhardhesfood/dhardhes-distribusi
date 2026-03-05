<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Transfer Gudang → Sales
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white shadow-sm sm:rounded-lg p-6">

                <form method="POST" action="{{ route('warehouse.transfer.store') }}">
                    @csrf

                    <div class="mb-6">
                        <label class="block font-medium text-sm text-gray-700">
                            Pilih Sales
                        </label>

                        <select name="user_id" class="mt-1 block w-full border-gray-300 rounded-md">
                            <option value="">-- pilih sales --</option>

                            @foreach($sales as $s)
                                <option value="{{ $s->id }}">
                                    {{ $s->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-6">
                        <table class="w-full border">
                            <thead>
                                <tr class="bg-gray-100">
                                    <th class="p-2 text-left">Produk</th>
                                    <th class="p-2 text-left">Qty Transfer</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach($products as $product)
                                <tr class="border-t">
                                    <td class="p-2">
                                        {{ $product->name }}
                                    </td>

                                    <td class="p-2">
                                        <input
                                            type="number"
                                            name="products[{{ $product->id }}]"
                                            value="0"
                                            min="0"
                                            class="border rounded p-1 w-32"
                                        >
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <button
                        type="submit"
                        class="bg-blue-600 text-white px-4 py-2 rounded"
                    >
                        Transfer Stok
                    </button>

                </form>

            </div>

        </div>
    </div>
</x-app-layout>
