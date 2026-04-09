<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">

    <div style="display:flex;align-items:center;gap:10px;">

        <a href="{{ url('/dashboard') }}"
           style="background:#2563eb;color:white;padding:8px 16px;border-radius:8px;text-decoration:none;font-weight:600;">
            Dashboard
        </a>

        <a href="javascript:history.back()"
           style="background:#6b7280;color:white;padding:8px 16px;border-radius:8px;text-decoration:none;font-weight:600;">
            Kembali
        </a>

        <h2 style="font-size:18px;font-weight:700;margin-left:10px;">
            Daftar Produk
        </h2>

    </div>

    <a href="{{ route('products.create') }}"
       style="background:#16a34a;color:white;padding:8px 16px;border-radius:8px;text-decoration:none;font-weight:600;">
        + Tambah Produk
    </a>
</div>

<table style="width:100%;border-collapse:collapse;font-size:14px;">
    <thead style="background:#f3f4f6;">
        <tr>
            <th style="padding:12px;text-align:left;">ID</th>
            <th style="padding:12px;text-align:left;">Nama Produk</th>
            <th style="padding:12px;text-align:left;">Kode SKU</th>
            <th style="padding:12px;text-align:right;">Harga Jual</th>
            <th style="padding:12px;text-align:right;">Fee</th>
            <th style="padding:12px;text-align:right;">Warehouse Price</th>
            <th style="padding:12px;text-align:center;">Channel</th>
            <th style="padding:12px;text-align:center;">Aksi</th>
        </tr>
    </thead>
    <tbody>
        @forelse($products as $product)
        <tr style="border-top:1px solid #e5e7eb;">
            <td style="padding:12px;">{{ $product->id }}</td>

            <td style="padding:12px;font-weight:600;">
                {{ $product->name }}
            </td>

            <td style="padding:12px;">
                {{ $product->sku }}
            </td>

            <td style="padding:12px;text-align:right;">
                Rp {{ number_format($product->default_selling_price,0,',','.') }}
            </td>

            <td style="padding:12px;text-align:right;">
                Rp {{ number_format($product->default_fee_nominal,0,',','.') }}
            </td>

            <td style="padding:12px;text-align:right;">
                Rp {{ number_format($product->warehouse_price,0,',','.') }}
            </td>

            <td style="padding:12px;text-align:center;">
    @if($product->channel_type == 'online')
        <span style="color:#2563eb;font-weight:600;">ONLINE</span>
    @else
        <span style="color:#6b7280;">OFFLINE</span>
    @endif
</td>

            <td style="padding:12px;text-align:center;">
                <a href="{{ route('products.edit', $product->id) }}"
                   style="background:#f59e0b;color:white;padding:6px 12px;border-radius:8px;text-decoration:none;font-weight:600;margin-right:6px;">
                    Edit
                </a>

                <a href="{{ route('products.costs.index', $product->id) }}"
                   style="background:#4f46e5;color:white;padding:6px 12px;border-radius:8px;text-decoration:none;font-weight:600;">
                    Kelola HPP
                </a>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="7" style="padding:20px;text-align:center;color:#6b7280;">
                Belum ada data produk.
            </td>
        </tr>
        @endforelse
    </tbody>
</table>