<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Stok Gudang
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white shadow-sm sm:rounded-lg p-6">

                {{-- TOMBOL PENYESUAIAN STOK (HANYA ADMIN) --}}
                @if(auth()->user()->role === 'admin')
                    <div class="mb-4">
                        <a href="{{ route('warehouse.adjustment.create') }}"
                           class="bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded shadow-sm">
                            Penyesuaian Stok
                        </a>
                    </div>
                @endif

                <table class="w-full border">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="p-2 border text-left">Produk</th>
                            <th class="p-2 border text-right">Stok Gudang</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($stocks as $stock)
                            <tr>
                                <td class="p-2 border">
                                    {{ $stock->name }}
                                </td>

                                <td class="p-2 border text-right font-semibold">
                                    {{ $stock->stock }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

            </div>

        </div>
    </div>
</x-app-layout>