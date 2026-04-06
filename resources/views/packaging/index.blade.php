<x-app-layout>

<div class="p-6">

    <!-- CARD INPUT -->
    <div style="background:white; padding:20px; border-radius:10px; margin-bottom:20px;">
        <h4 style="margin-bottom:15px;">Produksi Kemasan</h4>

        <form method="POST" action="{{ route('packaging.store') }}">
            @csrf

            <div style="margin-bottom:15px;">
                <label><b>Produk</b></label><br>
                <select name="product_id" id="product" style="padding:8px; width:300px;">
                    <option value="">Pilih Produk</option>
                    @foreach($products as $p)
                        <option value="{{ $p->id }}">{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>

            <div id="variants"></div>

            <button type="submit" style="
                margin-top:15px;
                background:#16a34a;
                color:white;
                border:none;
                padding:10px 20px;
                border-radius:6px;
                cursor:pointer;
            ">
                Simpan Kemasan
            </button>
            
        </form>

        <hr style="margin:25px 0;">

<h4 style="margin-bottom:15px; color:#dc2626;">Input Kemasan Rusak</h4>

<form method="POST" action="{{ route('packaging.damage') }}">
    @csrf

    <div style="margin-bottom:15px;">
        <label><b>Produk</b></label><br>
        <select name="product_id" id="product_damage" style="padding:8px; width:300px;">
            <option value="">Pilih Produk</option>
            @foreach($products as $p)
                <option value="{{ $p->id }}">{{ $p->name }}</option>
            @endforeach
        </select>
    </div>

    <div id="variants_damage"></div>

    <button type="submit" style="
        margin-top:15px;
        background:#dc2626;
        color:white;
        border:none;
        padding:10px 20px;
        border-radius:6px;
        cursor:pointer;
    ">
        Simpan Kemasan Rusak
    </button>
</form>
    </div>

    <!-- TABLE -->
    <div style="background:white; padding:20px; border-radius:10px;">
        <form method="GET" style="margin-bottom:15px;">
            <select name="year"
    style="padding:8px; border:1px solid #ccc; border-radius:6px; margin-right:10px;">

    @for($y = now()->year; $y >= now()->year - 3; $y--)
        <option value="{{ $y }}" {{ request('year', now()->year) == $y ? 'selected' : '' }}>
            {{ $y }}
        </option>
    @endfor

</select>
    <select name="month" onchange="this.form.submit()"
        style="padding:8px; border:1px solid #ccc; border-radius:6px;">

        <option value="">Semua Bulan</option>

        @for($m=1; $m<=12; $m++)
            <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>
                {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
            </option>
        @endfor

    </select>      
        <h4 style="margin-bottom:15px;">Stok Kemasan</h4>

        <table style="width:100%; border-collapse:collapse;">
            <thead>
                <tr style="background:#f3f4f6;">
                    <th style="padding:10px;">Produk</th>
                    <th style="padding:10px;">Varian</th>
                    <th style="padding:10px;">Stok</th>
                    <th style="padding:10px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($stocks as $s)
                    <tr style="border-top:1px solid #e5e7eb;">
                        <td style="padding:10px;">{{ $s->product_name }}</td>
                        <td style="padding:10px; color:#dc2626; font-weight:bold;">
                            {{ $s->variant_name }}
                        </td>
                        <td style="padding:10px;">
    {{ $s->stock_qty ?? 0 }}
</td>

<td style="padding:10px;">

@if(auth()->user()->role === 'admin')

<form method="POST" action="{{ route('packaging.update') }}" style="display:flex; gap:5px;">
@csrf

<input type="hidden" name="product_id" value="{{ $s->product_id }}">
<input type="hidden" name="variant_id" value="{{ $s->product_variant_id }}">

<input type="number" name="qty" value="{{ $s->stock_qty }}"
       style="width:80px; padding:5px; border:1px solid #ccc; border-radius:4px;">

<button type="submit"
       style="background:#f59e0b; color:white; border:none; padding:5px 10px; border-radius:5px;">
    Edit
</button>

</form>

@else

<span style="color:#9ca3af;">-</span>

@endif

</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" style="padding:15px; text-align:center;">
                            Belum ada stok kemasan
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

    <div style="margin-top:30px;">
    <h4>Produksi Harian</h4>

    <table style="width:100%; border-collapse:collapse;">
        <thead>
            <tr style="background:#f3f4f6;">
                <th style="padding:10px;">Tanggal</th>
                <th style="padding:10px;">Produk</th>
                <th style="padding:10px;">Varian</th>
                <th style="padding:10px;">Qty Produksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($daily as $d)
                <tr style="border-top:1px solid #e5e7eb;">
                    <td style="padding:10px;">{{ $d->tanggal }}</td>
                    <td style="padding:10px;">{{ $d->product_name }}</td>
                    <td style="padding:10px; color:red;">{{ $d->variant_name }}</td>
                    <td style="padding:10px; color:#16a34a; font-weight:bold;">
                    {{ $d->total_qty }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div style="margin-top:30px;">
    <h4>History Stok Kemasan</h4>

    <table style="width:100%; border-collapse:collapse;">
        <thead>
            <tr style="background:#f3f4f6;">
                <th style="padding:10px;">Tanggal</th>
                <th style="padding:10px;">Produk</th>
                <th style="padding:10px;">Varian</th>
                <th style="padding:10px;">Jenis</th>
                <th style="padding:10px;">Qty</th>
            </tr>
        </thead>
        <tbody>
            @foreach($histories as $h)
                <tr style="border-top:1px solid #e5e7eb;">
                    <td style="padding:10px;">
                        {{ \Carbon\Carbon::parse($h->created_at)->format('d-m-Y H:i') }}
                    </td>
                    <td style="padding:10px;">{{ $h->product_name }}</td>
                    <td style="padding:10px; color:red;">{{ $h->variant_name }}</td>
                    <td style="padding:10px;">
    @if($h->type == 'in')
        <span style="color:#16a34a; font-weight:bold;">Bertambah</span>
    @elseif($h->type == 'out')

    @if($h->reference_type == 'production_batch')
        <span style="color:#dc2626; font-weight:bold;">Berkurang (Produksi)</span>

    @elseif($h->reference_type == 'damage')
        <span style="color:#dc2626; font-weight:bold;">Berkurang (Rusak)</span>

    @else
        <span style="color:#dc2626; font-weight:bold;">Berkurang</span>
    @endif

    @elseif($h->type == 'return')
        <span style="color:#2563eb; font-weight:bold;">Dikembalikan</span>
    @elseif($h->type == 'adjustment')
        <span style="color:#f59e0b; font-weight:bold;">Penyesuaian</span>
    @else
        {{ $h->type }}
    @endif
</td>

<td style="padding:10px; font-weight:bold;">
    @if($h->type == 'in')
        <span style="color:#16a34a;">+{{ $h->quantity }}</span>
    @elseif($h->type == 'out')
        <span style="color:#dc2626;">-{{ $h->quantity }}</span>
    @elseif($h->type == 'return')
        <span style="color:#16a34a;">+{{ $h->quantity }}</span>
    @elseif($h->type == 'adjustment')
        <span style="color:#f59e0b;">
            {{ $h->quantity > 0 ? '+' : '' }}{{ $h->quantity }}
        </span>
    @endif
</td>
                </tr>
            @endforeach
        </tbody>
    </table>


</div>

    </div>

</div>

<script>
document.getElementById('product').addEventListener('change', function () {
    let productId = this.value;

    fetch('/api/product-variants/' + productId)
        .then(res => res.json())
        .then(data => {
            let html = '';

            data.forEach(v => {
                html += `
                    <div style="
                        display:flex;
                        justify-content:space-between;
                        padding:8px 0;
                        border-bottom:1px solid #eee;
                    ">
                        <div>${v.name}</div>
                        <div>
                            <input type="number" name="variants[${v.id}][qty]" style="width:100px; padding:6px;">
                            <input type="hidden" name="variants[${v.id}][id]" value="${v.id}">
                        </div>
                    </div>
                `;
            });

            document.getElementById('variants').innerHTML = html;
        });
});

document.getElementById('product_damage').addEventListener('change', function () {
    let productId = this.value;

    fetch('/api/product-variants/' + productId)
        .then(res => res.json())
        .then(data => {
            let html = '';

            data.forEach(v => {
                html += `
                    <div style="
                        display:flex;
                        justify-content:space-between;
                        padding:8px 0;
                        border-bottom:1px solid #eee;
                    ">
                        <div>${v.name}</div>
                        <div>
                            <input type="number" name="variants[${v.id}][qty]" style="width:100px; padding:6px;">
                            <input type="hidden" name="variants[${v.id}][id]" value="${v.id}">
                        </div>
                    </div>
                `;
            });

            document.getElementById('variants_damage').innerHTML = html;
        });
});
</script>


</x-app-layout>