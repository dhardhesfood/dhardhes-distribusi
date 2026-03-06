<x-app-layout>
    <x-slot name="header">

       <div style="display:flex;justify-content:space-between;align-items:center;">

        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Input Produksi
        </h2>

        <div style="display:flex;gap:10px;">

    <a href="{{ url('/dashboard') }}"
       style="background:#2563eb;color:white;padding:6px 12px;border-radius:6px;text-decoration:none;">
        Dashboard
    </a>

    <a href="javascript:history.back()"
       style="background:#6b7280;color:white;padding:6px 12px;border-radius:6px;text-decoration:none;">
        Kembali
    </a>

</div>

    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white shadow-sm sm:rounded-lg p-6">

                @if(session('success'))
                    <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">
                        {{ session('success') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('productions.store') }}">
                    @csrf

                    <div class="mb-4">
                        <label class="block text-sm font-medium">Produk</label>
                        <select name="product_id" class="w-full border rounded p-2" required>
                            <option value="">Pilih Produk</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}">
                                    {{ $product->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium">Jumlah Produksi</label>
                        <input type="number" name="quantity" class="w-full border rounded p-2" required>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium">Tanggal Produksi</label>
                        <input type="date"
                               name="production_date"
                               value="{{ old('production_date', now()->format('Y-m-d')) }}"
                               class="border p-2 rounded w-full">
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium">Catatan</label>
                        <textarea name="notes" class="w-full border rounded p-2"></textarea>
                    </div>

                    <button class="bg-blue-600 text-white px-4 py-2 rounded">
                        Simpan Produksi
                    </button>

                </form>

            </div>
        </div>
    </div>
</x-app-layout>