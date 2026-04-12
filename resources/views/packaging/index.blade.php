<x-app-layout>

<div class="p-6 max-w-6xl mx-auto space-y-6">

{{-- 🔥 INFO PENTING KE TIM KEMASAN --}}
<div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded-lg shadow">

    <div class="font-bold text-yellow-700 mb-1">
        ⚠️ Perhatian Produksi
    </div>

    <div class="text-sm text-gray-700 space-y-1">

        <div>
            Data analisa ini hanya berdasarkan stok <b>pack siap jual</b> di sistem.
        </div>

        <div>
            Kemungkinan masih ada stok produk <b>belum dipack di gudang</b>.
        </div>

        <div class="mt-2 font-semibold">
            👉 Sebelum produksi kemasan:
        </div>

        <ul class="list-disc ml-5">
            <li>Tanyakan ke gudang stok produk yang belum dipack</li>
            <li>Sesuaikan dengan kebutuhan kemasan saat ini</li>
        </ul>

        <div class="text-red-600 font-semibold mt-2">
            Hindari produksi berlebih (over produksi)
        </div>

    </div>

</div>

<div class="bg-white p-4 rounded-xl shadow">

    <h2 class="text-lg font-bold mb-3">Analisa Kebutuhan Kemasan</h2>

    <div class="flex gap-3">

        <!-- OFFLINE -->
        <a href="/packaging/analysis-offline"
           class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
            Analisa Kemasan Offline
        </a>

        <!-- ONLINE -->
        <a href="/packaging/analysis-online"
           class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">
            Analisa Kemasan Order Online
        </a>

    </div>

</div>

    <!-- CARD INPUT -->
    <div class="bg-white p-6 rounded-xl shadow">
        @if(auth()->user()->role !== 'admin_gudang')
        <h2 class="text-xl font-bold mb-4">Produksi Kemasan</h2>

        <form method="POST" action="{{ route('packaging.store') }}">
            <div class="mb-4">
        <label class="font-semibold">Tanggal Produksi</label>
        <input 
               type="date" 
               name="tanggal"
               value="{{ date('Y-m-d') }}"
               class="border rounded w-full p-2 mt-1"
               required
               >
           </div>
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
        @endif

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

     <!-- HISTORY -->
        <div class="mt-6">
            <div class="mt-6">
       <a href="{{ route('packaging.history') }}"
       class="inline-block bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
        Lihat History Stok Kemasan
       </a>
       </div>

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

        <div class="space-y-4">

@forelse($groupedStocks as $productName => $variants)

<div class="border rounded-lg p-4 shadow-sm">

    <!-- NAMA PRODUK -->
    <div class="font-bold text-lg mb-2">
        {{ $productName }}
    </div>

    <!-- VARIAN -->
    <div class="space-y-2">

        @foreach($variants as $v)
        <div class="flex justify-between items-center border-b pb-2">

            <div class="text-red-600 font-semibold">
                {{ $v->variant_name }}
            </div>

            <div class="flex items-center gap-2">

                <div class="text-center w-10">
                    {{ $v->stock_qty }}
                </div>

                @if(auth()->user()->role === 'admin')
                <form method="POST" action="{{ route('packaging.update') }}" class="flex items-center gap-1">
    @csrf

    <input type="hidden" name="product_id" value="{{ $v->product_id }}">
    <input type="hidden" name="variant_id" value="{{ $v->variant_id }}">

    <!-- DISPLAY MODE -->
    <span class="cursor-pointer text-blue-600 text-sm"
          onclick="toggleEdit(this)">
        ✏️
    </span>

    <!-- EDIT MODE (HIDDEN DEFAULT) -->
    <div class="hidden flex items-center gap-1">

        <input 
            type="number" 
            name="qty" 
            value="{{ $v->stock_qty }}" 
            class="border rounded p-1 w-14 text-center text-sm"
        >

        <button type="submit" 
            class="bg-yellow-500 text-white px-2 py-1 rounded text-xs">
            ✔
        </button>

    </div>
</form>
                @endif

            </div>

        </div>
        @endforeach

    </div>

</div>

@empty
<div class="text-center text-gray-500 py-4">
    Belum ada data produk
</div>
@endforelse

</div>

        <!-- PRODUKSI HARIAN -->
        <div class="mt-6">
            <h3 class="text-lg font-bold mb-3">Produksi Harian</h3>

            <form method="GET" class="flex gap-2 mb-4">

    <!-- FILTER TAHUN -->
    <select name="year" class="border rounded p-2">
        @for($y = now()->year; $y >= now()->year - 3; $y--)
            <option value="{{ $y }}" {{ request('year', now()->year) == $y ? 'selected' : '' }}>
                {{ $y }}
            </option>
        @endfor
    </select>

    <!-- FILTER BULAN -->
    <select name="month" onchange="this.form.submit()" class="border rounded p-2">
        <option value="">-- Pilih Bulan --</option>
        @for($m=1; $m<=12; $m++)
            <option value="{{ $m }}" {{ request('month', now()->month) == $m ? 'selected' : '' }}>
                {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
            </option>
        @endfor
    </select>

</form>

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
function toggleEdit(el) {
    const form = el.closest('form');
    const editBox = form.querySelector('div');

    editBox.classList.toggle('hidden');
}
</script>

</x-app-layout>