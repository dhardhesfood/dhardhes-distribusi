<x-app-layout>
    <div class="p-6 max-w-5xl mx-auto">

        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-2xl font-bold">
                    Riwayat HPP
                </h2>
                <div class="text-gray-600 text-sm">
                    Produk: {{ $product->name }}
                </div>
            </div>

            <a href="{{ route('products.index') }}"
               class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded shadow">
                Kembali
            </a>
        </div>

        @if(session('success'))
            <div class="mb-4 p-3 bg-green-100 text-green-700 rounded">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-4 p-3 bg-red-100 text-red-700 rounded">
                <ul class="list-disc ml-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- FORM TAMBAH HPP -->
        <div class="bg-white shadow rounded p-6 mb-6 border">
            <h3 class="font-semibold mb-4">Tambah HPP Baru</h3>

            <form method="POST"
                  action="{{ route('products.costs.store', $product->id) }}"
                  class="grid grid-cols-3 gap-4 items-end">
                @csrf

                <div>
                    <label class="block text-sm font-medium mb-1">
                        Tanggal Berlaku
                    </label>
                    <input type="date"
                           name="effective_date"
                           required
                           class="w-full border rounded px-3 py-2">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">
                        HPP
                    </label>
                    <input type="number"
                           name="cost"
                           min="0"
                           required
                           class="w-full border rounded px-3 py-2">
                </div>

                <div>
                    <button type="submit"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow font-semibold">
                        Simpan
                    </button>
                </div>

            </form>
        </div>

        <!-- TABLE RIWAYAT -->
        <div class="bg-white shadow rounded border overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="border p-3 text-left">Tanggal Berlaku</th>
                        <th class="border p-3 text-right">HPP</th>
                        <th class="border p-3 text-left">Input Oleh</th>
                        <th class="border p-3 text-left">Dibuat</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($costs as $cost)
                        <tr class="hover:bg-gray-50">
                            <td class="border p-3">
                                {{ $cost->effective_date }}
                            </td>
                            <td class="border p-3 text-right font-semibold">
                                Rp {{ number_format($cost->cost, 0, ',', '.') }}
                            </td>
                            <td class="border p-3">
                                {{ optional($cost->creator)->name ?? '-' }}
                            </td>
                            <td class="border p-3">
                                {{ $cost->created_at }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="border p-4 text-center text-gray-500">
                                Belum ada riwayat HPP.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
</x-app-layout>
