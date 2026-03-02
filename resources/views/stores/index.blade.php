<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Daftar Toko
            </h2>

            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 sm:gap-3">

                <form method="GET" action="{{ route('stores.index') }}" class="w-full sm:w-auto">

                    @if(request('sales_id'))
                        <input type="hidden" name="sales_id" value="{{ request('sales_id') }}">
                    @endif

                    <select name="area_id"
                            onchange="this.form.submit()"
                            class="w-full sm:w-auto border-gray-300 rounded-md shadow-sm text-sm">
                        <option value="">Semua Area</option>
                        @foreach($areas as $area)
                            <option value="{{ $area->id }}"
                                {{ request('area_id') == $area->id ? 'selected' : '' }}>
                                {{ $area->name }} ({{ $area->stores_count }})
                            </option>
                        @endforeach
                    </select>
                </form>

                <a href="{{ route('stores.create') }}"
                   class="inline-flex justify-center items-center w-full sm:w-auto px-5 py-2.5 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-lg shadow-lg transition duration-150">
                    + Tambah Toko
                </a>

            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">

            @if (session('success'))
                <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-700 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded font-semibold">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white shadow sm:rounded-lg p-6">

                @php
                    $globalProducts = [];
                    $globalTotalValue = 0;
                    $globalEstimasiFee = 0;

                    foreach ($stores as $store) {
                        if (!empty($store->products_stock)) {
                            foreach ($store->products_stock as $product) {

                                if (!isset($globalProducts[$product['name']])) {
                                    $globalProducts[$product['name']] = 0;
                                }

                                $globalProducts[$product['name']] += $product['qty'];

                                // Tambahkan subtotal ke total global
                                $globalTotalValue += $product['subtotal'] ?? 0;
                                $globalEstimasiFee += $product['estimasi_fee'] ?? 0;
                            }
                        }
                    }
                @endphp

                @if(count($globalProducts) > 0)
                    <div class="mb-6 border-b pb-4">
                        <div class="font-semibold text-gray-800 mb-2">
                            Total Produk dari Toko yang Ditampilkan
                        </div>

                        <ul class="space-y-1 text-sm">
                            @foreach($globalProducts as $name => $qty)
                                <li class="flex justify-between border-b pb-1">
                                    <span>{{ $name }}</span>
                                    <span class="font-semibold">{{ $qty }}</span>
                                </li>
                            @endforeach
                        </ul>

                        <div class="mt-4 pt-3 border-t text-sm font-semibold text-green-700 flex justify-between">
                           <span>Estimasi Fee Maksimal Jika Stok Habis</span>
                           <span>Rp {{ number_format($globalEstimasiFee,0,',','.') }}</span>
                    </div>

                        {{-- TOTAL NILAI RUPIAH --}}
                        <div class="mt-4 pt-3 border-t">
                            <div class="flex justify-between text-sm font-semibold text-gray-800">
                                <span>Total Nilai Rupiah Stok (Sesuai Harga Toko)</span>
                                <span>
                                    Rp {{ number_format($globalTotalValue, 0, ',', '.') }}
                                </span>
                            </div>
                        </div>
                    </div>
                @endif

                @forelse($stores as $store)

                    <div class="flex justify-between items-start sm:items-center border-b py-4 gap-4">

                        <div class="pr-2 w-full">

                            <div class="font-semibold text-lg text-gray-800">
                                {{ $store->name }}
                            </div>

                            <div class="text-sm text-gray-500">
                                Area: {{ optional($store->area)->name ?? '-' }}
                            </div>

                            <div class="text-sm mt-1">
                                Status:
                                @if($store->is_active)
                                    <span class="text-green-600 font-medium">Aktif</span>
                                @else
                                    <span class="text-red-600 font-medium">Nonaktif</span>
                                @endif
                            </div>

                            <div class="mt-4 text-sm">
                                <div class="font-semibold mb-1 text-gray-700">
                                    Stok Produk:
                                </div>

                                @if(!empty($store->products_stock))
                                    <ul class="space-y-1">
                                        @foreach($store->products_stock as $product)
                                            <li class="flex justify-between border-b pb-1">
                                                <span>{{ $product['name'] }}</span>
                                                <span class="font-semibold">
                                                    {{ $product['qty'] }}
                                                </span>
                                            </li>
                                        @endforeach
                                    </ul>

                                    <div class="mt-2 text-xs text-gray-600">
                                        Total Qty:
                                        <span class="font-semibold">
                                            {{ $store->total_stock_qty }}
                                        </span>
                                    </div>
                                @else
                                    <div class="text-gray-400 text-xs">
                                        Tidak ada stok
                                    </div>
                                @endif
                            </div>

                        </div>

                        <div class="flex flex-col sm:flex-row gap-2 items-end">

                            @if(request('sales_id'))
                                <a href="{{ route('visits.create', [
                                    'store' => $store->id,
                                    'sales_id' => request('sales_id')
                                ]) }}"
                                   class="inline-flex justify-center items-center px-3 py-1.5 sm:px-4 sm:py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs sm:text-sm font-medium rounded-md shadow-md transition whitespace-nowrap">
                                    Kunjungan
                                </a>
                            @else
                                <a href="{{ route('visits.create', $store->id) }}"
                                   class="inline-flex justify-center items-center px-3 py-1.5 sm:px-4 sm:py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs sm:text-sm font-medium rounded-md shadow-md transition whitespace-nowrap">
                                    Kunjungan
                                </a>
                            @endif

                            <a href="{{ route('stores.edit', $store->id) }}"
                               class="inline-flex justify-center items-center px-3 py-1.5 sm:px-4 sm:py-2 bg-yellow-500 hover:bg-yellow-600 text-white text-xs sm:text-sm font-medium rounded-md shadow-md transition whitespace-nowrap">
                                Edit
                            </a>

                            @if(auth()->user()->role === 'admin')

                                <a href="{{ route('stores.prices.edit', $store->id) }}"
                                   class="inline-flex justify-center items-center px-3 py-1.5 sm:px-4 sm:py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs sm:text-sm font-medium rounded-md shadow-md transition whitespace-nowrap">
                                    Kelola Harga
                                </a>

                                <form action="{{ route('stores.destroy', $store->id) }}"
                                      method="POST"
                                      onsubmit="return confirm('Yakin hapus toko ini?')">
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit"
                                            class="inline-flex justify-center items-center px-3 py-1.5 sm:px-4 sm:py-2 bg-red-600 hover:bg-red-700 text-white text-xs sm:text-sm font-medium rounded-md shadow-md transition whitespace-nowrap">
                                        Hapus
                                    </button>
                                </form>

                            @endif

                        </div>

                    </div>

                @empty

                    <div class="text-gray-500 text-center py-10">
                        Belum ada data toko.
                    </div>

                @endforelse

            </div>

        </div>
    </div>
</x-app-layout>