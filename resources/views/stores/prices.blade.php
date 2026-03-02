<x-app-layout>
<x-slot name="header">
    <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
        Kelola Harga — {{ $store->name }}
    </h2>
</x-slot>

<div class="py-8">
<div class="max-w-5xl mx-auto sm:px-6 lg:px-8">

    @if(session('success'))
        <div style="margin-bottom:20px;padding:12px;background:#dcfce7;color:#166534;border-radius:8px;">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('stores.prices.update', $store->id) }}">
        @csrf

        <div style="background:white;border-radius:16px;box-shadow:0 10px 20px rgba(0,0,0,0.08);overflow:hidden;">
            <table style="width:100%;border-collapse:collapse;font-size:14px;">
                <thead style="background:#f3f4f6;">
                    <tr>
                        <th style="padding:12px;text-align:left;">Produk</th>
                        <th style="padding:12px;text-align:right;">Harga</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $product)
                    <tr style="border-top:1px solid #e5e7eb;">
                        <td style="padding:12px;font-weight:600;">
                            {{ $product->name }}
                        </td>
                        <td style="padding:12px;text-align:right;">
                            <input type="number"
                                   name="prices[{{ $product->id }}]"
                                   value="{{ $storePrices[$product->id] ?? 0 }}"
                                   required
                                   min="0"
                                   style="width:120px;padding:6px;border:1px solid #ddd;border-radius:6px;text-align:right;">
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div style="margin-top:20px;">
            <button type="submit"
                    style="background:#2563eb;color:white;padding:12px 20px;border-radius:10px;font-weight:600;border:none;">
                Simpan Perubahan
            </button>

            <a href="{{ route('stores.index') }}"
               style="margin-left:10px;padding:12px 20px;border-radius:10px;background:#e5e7eb;color:#111;text-decoration:none;">
                Kembali
            </a>
        </div>

    </form>

</div>
</div>
</x-app-layout>
