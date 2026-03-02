<x-app-layout>
<x-slot name="header">
    <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
        Edit Produk
    </h2>
</x-slot>

<div class="py-8">
<div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

    <div style="background:white;padding:30px;border-radius:16px;box-shadow:0 10px 20px rgba(0,0,0,0.08);">

        <form method="POST" action="{{ route('products.update', $product->id) }}">
            @csrf
            @method('PUT')

            <div style="margin-bottom:20px;">
                <label>Nama Produk</label><br>
                <input type="text" name="name" value="{{ old('name', $product->name) }}" required
                       style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;">
            </div>

            <div style="margin-bottom:20px;">
                <label>Kode SKU</label><br>
                <input type="text" name="sku" value="{{ old('sku', $product->sku) }}" required
                       style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;">
            </div>

            <div style="margin-bottom:20px;">
                <label>Harga Jual / Konsinyasi Default</label><br>
                <input type="number" name="default_selling_price"
                       value="{{ old('default_selling_price', $product->default_selling_price) }}"
                       required min="0" step="0.01"
                       style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;">
            </div>

            <div style="margin-bottom:20px;">
                <label>Fee Default</label><br>
                <input type="number" name="default_fee_nominal"
                       value="{{ old('default_fee_nominal', $product->default_fee_nominal) }}"
                       required min="0" step="0.01"
                       style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;">
            </div>

            {{-- 🔥 FIELD BARU --}}
            <div style="margin-bottom:20px;">
                <label>Warehouse Price (Harga Gudang)</label><br>
                <input type="number" name="warehouse_price"
                       value="{{ old('warehouse_price', $product->warehouse_price) }}"
                       required min="0" step="0.01"
                       style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;">
                <small style="color:#6b7280;">
                    Digunakan untuk perhitungan minus stok sales.
                </small>
            </div>

            <div style="margin-bottom:20px;">
                <label>
                    <input type="checkbox" name="is_active" value="1"
                        {{ old('is_active', $product->is_active) ? 'checked' : '' }}>
                    Aktif
                </label>
            </div>

            <div>
                <button type="submit"
                        style="background:#2563eb;color:white;padding:12px 20px;border-radius:10px;font-weight:600;border:none;">
                    Update Produk
                </button>
            </div>

        </form>

    </div>

</div>
</div>
</x-app-layout>