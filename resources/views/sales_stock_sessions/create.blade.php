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

            {{-- STOK AWAL --}}
            <div>
                <h3 class="font-semibold mb-4">
                    Input Stok Awal (Dari Gudang)
                </h3>

                <table class="w-full border text-sm">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="border p-2 text-left">Produk</th>
                            <th class="border p-2 text-center">Qty Awal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($products as $product)
                            <tr>
                                <td class="border p-2">
                                    {{ $product->name }}
                                </td>
                                <td class="border p-2 text-center">
                                    <input type="number"
                                           name="opening_qty[{{ $product->id }}]"
                                           value="0"
                                           min="0"
                                           class="border p-1 rounded w-24 text-center">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-6 text-right">
                <button type="submit"
                        class="bg-green-600 text-white px-6 py-2 rounded font-semibold">
                    Mulai Session
                </button>
            </div>

        </form>

    </div>

</div>

</x-app-layout>