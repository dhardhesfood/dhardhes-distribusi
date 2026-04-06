<x-app-layout>

<div class="p-6 max-w-6xl mx-auto space-y-6">

    <!-- CARD INPUT -->
    <div class="bg-white p-6 rounded-xl shadow">
        <h2 class="text-xl font-bold mb-4">Produksi Kemasan</h2>

        <form method="POST" action="{{ route('packaging.store') }}">
            @csrf

            <div class="mb-4">
                <label class="font-semibold">Produk</label>
                <select name="product_id" id="product" class="border rounded w-full p-2 mt-1">
                    <option value="">Pilih Produk</option>
                    @foreach($products as $p)
                        <option value="{{ $p->id }}">{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>

            <div id="variants"></div>

            <button type="submit" class="mt-4 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">
                Simpan Kemasan
            </button>
        </form>

        <hr class="my-6">

        <h3 class="text-lg font-bold mb-3 text-red-600">Input Kemasan Rusak</h3>

        <form method="POST" action="{{ route('packaging.damage') }}">
            @csrf

            <div class="mb-4">
                <label class="font-semibold">Produk</label>
                <select name="product_id" id="product_damage" class="border rounded w-full p-2 mt-1">
                    <option value="">Pilih Produk</option>
                    @foreach($products as $p)
                        <option value="{{ $p->id }}">{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>

            <div id="variants_damage"></div>

            <button type="submit" class="mt-4 bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded">
                Simpan Kemasan Rusak
            </button>
        </form>
    </div>

    <!-- TABLE -->
    <div class="bg-white p-6 rounded-xl shadow">

        <form method="GET" class="flex items-center gap-3 mb-4">
            <select name="year" class="border rounded p-2">
                @for($y = now()->year; $y >= now()->year - 3; $y--)
                    <option value="{{ $y }}" {{ request('year', now()->year) == $y ? 'selected' : '' }}>
                        {{ $y }}
                    </option>
                @endfor
            </select>

            <select name="month" onchange="this.form.submit()" class="border rounded p-2">
                <option value="">Semua Bulan</option>
                @for($m=1; $m<=12; $m++)
                    <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                    </option>
                @endfor
            </select>
        </form>

        <h3 class="text-lg font-bold mb-3">Stok Kemasan</h3>

        <table class="w-full text-sm border border-gray-200">
            <thead>
                <tr class="bg-gray-100">
                    <th class="p-2 border">Produk</th>
                    <th class="p-2 border">Varian</th>
                    <th class="p-2 border">Stok</th>
                    <th class="p-2 border">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($stocks as $s)
                    <tr class="border-t hover:bg-gray-50">
                        <td class="p-2 border">{{ $s->product_name }}</td>
                        <td class="p-2 border text-red-600 font-semibold">{{ $s->variant_name }}</td>
                        <td class="p-2 border">{{ $s->stock_qty ?? 0 }}</td>

                        <td class="p-2 border">
                            @if(auth()->user()->role === 'admin')
                            <form method="POST" action="{{ route('packaging.update') }}" class="flex gap-2">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $s->product_id }}">
                                <input type="hidden" name="variant_id" value="{{ $s->product_variant_id }}">
                                <input type="number" name="qty" value="{{ $s->stock_qty }}" class="border rounded p-1 w-20">
                                <button type="submit" class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded text-xs">
                                    Edit
                                </button>
                            </form>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="p-4 text-center text-gray-500">
                            Belum ada stok kemasan
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- PRODUKSI HARIAN -->
        <div class="mt-6">
            <h3 class="text-lg font-bold mb-3">Produksi Harian</h3>

            <table class="w-full text-sm border border-gray-200">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="p-2 border">Tanggal</th>
                        <th class="p-2 border">Produk</th>
                        <th class="p-2 border">Varian</th>
                        <th class="p-2 border">Qty Produksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($daily as $d)
                        <tr class="border-t hover:bg-gray-50">
                            <td class="p-2 border">{{ $d->tanggal }}</td>
                            <td class="p-2 border">{{ $d->product_name }}</td>
                            <td class="p-2 border text-red-600">{{ $d->variant_name }}</td>
                            <td class="p-2 border text-green-600 font-semibold">{{ $d->total_qty }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- HISTORY -->
        <div class="mt-6">
            <h3 class="text-lg font-bold mb-3">History Stok Kemasan</h3>

            <table class="w-full text-sm border border-gray-200">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="p-2 border">Tanggal</th>
                        <th class="p-2 border">Produk</th>
                        <th class="p-2 border">Varian</th>
                        <th class="p-2 border">Jenis</th>
                        <th class="p-2 border">Qty</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($histories as $h)
                        <tr class="border-t hover:bg-gray-50">
                            <td class="p-2 border">
                                {{ \Carbon\Carbon::parse($h->created_at)->format('d-m-Y H:i') }}
                            </td>
                            <td class="p-2 border">{{ $h->product_name }}</td>
                            <td class="p-2 border text-red-600">{{ $h->variant_name }}</td>

                            <td class="p-2 border">
                                @if($h->type == 'in')
                                    <span class="text-green-600 font-semibold">Bertambah</span>

                                @elseif($h->type == 'out')
                                    @if($h->reference_type == 'production_batch')
                                        <span class="text-red-600 font-semibold">Berkurang (Produksi)</span>
                                    @elseif($h->reference_type == 'damage')
                                        <span class="text-red-600 font-semibold">Berkurang (Rusak)</span>
                                    @else
                                        <span class="text-red-600 font-semibold">Berkurang</span>
                                    @endif

                                @elseif($h->type == 'return')
                                    <span class="text-blue-600 font-semibold">Dikembalikan</span>

                                @elseif($h->type == 'adjustment')
                                    <span class="text-yellow-500 font-semibold">Penyesuaian</span>
                                @endif
                            </td>

                            <td class="p-2 border font-semibold">
                                @if($h->type == 'in')
                                    <span class="text-green-600">+{{ $h->quantity }}</span>
                                @elseif($h->type == 'out')
                                    <span class="text-red-600">-{{ $h->quantity }}</span>
                                @elseif($h->type == 'return')
                                    <span class="text-green-600">+{{ $h->quantity }}</span>
                                @elseif($h->type == 'adjustment')
                                    <span class="text-yellow-500">
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
                    <div class="flex justify-between py-2 border-b">
                        <div>${v.name}</div>
                        <div>
                            <input type="number" name="variants[${v.id}][qty]" class="border rounded p-2 w-24">
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
                    <div class="flex justify-between py-2 border-b">
                        <div>${v.name}</div>
                        <div>
                            <input type="number" name="variants[${v.id}][qty]" class="border rounded p-2 w-24">
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