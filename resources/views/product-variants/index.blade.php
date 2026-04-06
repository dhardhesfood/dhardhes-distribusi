<x-app-layout>
    <x-slot name="header">
        <div style="display:flex;justify-content:space-between;align-items:center;">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Master Varian Produk
            </h2>

            <a href="{{ url('/dashboard') }}"
               style="background:#2563eb;color:white;padding:6px 12px;border-radius:6px;text-decoration:none;">
                Dashboard
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white shadow-sm sm:rounded-lg p-6">

                {{-- SUCCESS --}}
                @if(session('success'))
                    <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">
                        {{ session('success') }}
                    </div>
                @endif

                {{-- PILIH PRODUK --}}
                <form method="GET" class="mb-6">
                    <label class="block text-sm font-medium mb-1">Pilih Produk</label>
                    <select name="product_id" onchange="this.form.submit()" class="w-full border rounded p-2">
                        <option value="">-- Pilih Produk --</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}"
                                {{ $selectedProductId == $product->id ? 'selected' : '' }}>
                                {{ $product->name }}
                            </option>
                        @endforeach
                    </select>
                </form>

                @if($selectedProductId)

                    {{-- FORM TAMBAH VARIAN --}}
                    <form method="POST" action="{{ route('product-variants.store') }}" class="mb-6">
                        @csrf

                        <input type="hidden" name="product_id" value="{{ $selectedProductId }}">

                        <div style="display:flex;gap:10px;">
                            <input type="text" name="name" placeholder="Nama varian (contoh: Balado)"
                                class="border rounded p-2 w-full" required>

                            <button class="bg-green-600 text-white px-4 py-2 rounded">
                                + Tambah
                            </button>
                        </div>
                    </form>
                     
                    {{-- LIST VARIAN --}}
                    <table class="w-full border border-gray-300">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="border px-3 py-2 text-left">Nama Varian</th>
                                <th class="border px-3 py-2 text-center">Status</th>
                                <th class="border px-3 py-2 text-center">Aksi</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($variants as $variant)
                                <tr>
                                    <td class="border px-3 py-2">
                                        {{ $variant->name }}
                                    </td>

                                    <td class="border px-3 py-2 text-center">
                                        @if($variant->is_active)
                                            <span style="color:green;">Aktif</span>
                                        @else
                                            <span style="color:red;">Nonaktif</span>
                                        @endif
                                    </td>

                                    <td class="border px-3 py-2 text-center">
                                        @if($variant->is_active)
                                            <form method="POST"
                                                action="{{ route('product-variants.destroy', $variant->id) }}"
                                                onsubmit="return confirm('Nonaktifkan varian ini?')">
                                                @csrf
                                                @method('DELETE')

                                                <button class="bg-red-600 text-white px-3 py-1 rounded">
                                                    Nonaktifkan
                                                </button>
                                            </form>
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center py-4">
                                        Belum ada varian
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                @else
                    <div class="text-gray-500">
                        Pilih produk terlebih dahulu untuk melihat varian
                    </div>
                @endif

            </div>
        </div>
    </div>
</x-app-layout>