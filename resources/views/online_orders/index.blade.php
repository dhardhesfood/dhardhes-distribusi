<x-app-layout>

<div class="py-8">
<div class="max-w-5xl mx-auto sm:px-6 lg:px-8">

<div class="bg-white shadow rounded-lg p-6">

    <!-- HEADER -->

    {{-- ✅ TARUH DI SINI --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">

    <div class="bg-yellow-100 text-yellow-800 p-3 rounded text-center">
        <div class="text-xs">On Process</div>
        <div class="text-xl font-bold">{{ $statusCounts->on_process ?? 0 }}</div>
    </div>

    <div class="bg-green-100 text-green-800 p-3 rounded text-center">
        <div class="text-xs">Done</div>
        <div class="text-xl font-bold">{{ $statusCounts->done ?? 0 }}</div>
    </div>

    <div class="bg-blue-100 text-blue-800 p-3 rounded text-center">
        <div class="text-xs">Return</div>
        <div class="text-xl font-bold">{{ $statusCounts->returned ?? 0 }}</div>
    </div>

    <div class="bg-red-100 text-red-800 p-3 rounded text-center">
        <div class="text-xs">Cancel</div>
        <div class="text-xl font-bold">{{ $statusCounts->cancelled ?? 0 }}</div>
    </div>

</div>

<!-- ========================= -->
<!-- 🔥 AGREGASI FIFO -->
<!-- ========================= -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-6">

    <div class="bg-gray-100 p-3 rounded text-center">
        <div class="text-xs">Total Kebutuhan</div>
        <div class="text-lg font-bold">
            {{ number_format($aggregation->total_required ?? 0) }}
        </div>
    </div>

    <div class="bg-green-100 text-green-800 p-3 rounded text-center">
        <div class="text-xs">Stok Cukup</div>
        <div class="text-lg font-bold">
            {{ number_format($aggregation->total_available ?? 0) }}
        </div>
    </div>

    <div class="bg-red-100 text-red-800 p-3 rounded text-center">
        <div class="text-xs">Stok Kurang</div>
        <div class="text-lg font-bold">
            {{ number_format($aggregation->total_shortage ?? 0) }}
        </div>
    </div>

    <div class="flex items-center justify-center">
        <form action="{{ route('online-orders.send-wa-global') }}" method="POST">
            @csrf
            <button class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded shadow">
                Kirim WA Produksi
            </button>
        </form>
    </div>

</div>

<!-- ========================= -->
<!-- 🔥 DETAIL PRODUK -->
<!-- ========================= -->
<div class="bg-white border rounded-lg p-4 mb-6">

    <div class="font-bold text-gray-700 mb-3">
        Detail Kebutuhan Produksi
    </div>

    <div class="space-y-2 text-sm">

        @foreach($aggregationDetails as $d)

        <div class="flex justify-between border-b pb-1">

            <div>
                {{ $d->product_name }} ({{ $d->variant_name }})
            </div>

            <div class="text-right">

                @if($d->total_shortage > 0)
                    <span class="text-red-600 font-semibold">
                        kurang {{ $d->total_shortage }}
                    </span>
                @else
                    <span class="text-green-600 font-semibold">
                        cukup
                    </span>
                @endif

                <div class="text-xs text-gray-500">
                    {{ $d->total_available }} / {{ $d->total_required }}
                </div>

            </div>

        </div>

        @endforeach

    </div>

</div>

    <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center gap-3 mb-6">

    <!-- KIRI -->
    <div>
        <h2 class="text-2xl font-bold text-gray-800">
            List Order Online
        </h2>
    </div>

    <!-- KANAN -->
    <div class="flex flex-wrap items-center gap-2 justify-end">

        <!-- FILTER -->
        <form method="GET" class="w-full">

        <div class="grid md:grid-cols-3 gap-4 w-full">

    <!-- CARD 1 -->
    <div class="bg-gray-50 border rounded-lg p-3">
        <div class="text-xs text-gray-500 mb-2">Filter Cepat</div>

        <div class="flex flex-wrap gap-2">

            <a href="{{ request()->fullUrlWithQuery(['filter' => 'today', 'date' => null]) }}"
               class="px-3 py-1 rounded text-xs font-semibold
               {{ request('filter')=='today'
               ? 'bg-blue-600 text-white'
               : 'bg-white border hover:bg-gray-100' }}">
                Hari Ini
            </a>

            <a href="{{ request()->fullUrlWithQuery(['filter' => 'yesterday', 'date' => null]) }}"
               class="px-3 py-1 rounded text-xs font-semibold
               {{ request('filter')=='yesterday'
               ? 'bg-blue-600 text-white'
               : 'bg-white border hover:bg-gray-100' }}">
                Kemarin
            </a>

            <a href="{{ request()->fullUrlWithQuery(['filter' => '7days', 'date' => null]) }}"
               class="px-3 py-1 rounded text-xs font-semibold
               {{ request('filter')=='7days'
               ? 'bg-blue-600 text-white'
               : 'bg-white border hover:bg-gray-100' }}">
                7 Hari
            </a>

            <a href="{{ request()->fullUrlWithQuery(['filter' => '30days', 'date' => null]) }}"
               class="px-3 py-1 rounded text-xs font-semibold
               {{ request('filter')=='30days'
               ? 'bg-blue-600 text-white'
               : 'bg-white border hover:bg-gray-100' }}">
                30 Hari
            </a>

        </div>
    </div>


    <!-- CARD SEARCH -->
<div class="bg-gray-50 border rounded-lg p-3">
    <div class="text-xs text-gray-500 mb-2">Cari Nama Customer</div>

    <div class="flex gap-2">
        <div class="relative w-full">
    <input type="text"
       id="searchInput"
       name="search"
       value="{{ request('search') }}"
       placeholder="Ketik nama..."
       class="border rounded px-2 py-1 text-sm w-full">

    <div id="searchDropdown"
         class="absolute z-50 bg-white border w-full rounded shadow mt-1 hidden max-h-60 overflow-y-auto">
    </div>
</div>

        <button type="submit"
                class="bg-blue-600 text-white px-3 py-1 rounded text-sm">
            Cari
        </button>
    </div>
</div>

    <!-- CARD 2 -->
    <div class="bg-gray-50 border rounded-lg p-3">
        <div class="text-xs text-gray-500 mb-2">Tanggal</div>

        <div class="flex gap-2">
            <input type="date"
                   name="date"
                   value="{{ request('date') }}"
                   class="border rounded px-2 py-1 text-sm w-full">

            <button type="submit"
                    class="bg-gray-800 text-white px-3 py-1 rounded text-sm">
                OK
            </button>
        </div>
    </div>

    <!-- CARD 3 -->
    <div class="bg-gray-50 border rounded-lg p-3">
        <div class="text-xs text-gray-500 mb-2">Bulanan</div>

        <div class="flex gap-2">

            <select name="month"
                onchange="this.form.submit()"
                class="border rounded px-2 py-1 text-sm w-full">

                @for($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}"
                        {{ request('month', now()->month) == $m ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                    </option>
                @endfor

            </select>

            <select name="year"
                onchange="this.form.submit()"
                class="border rounded px-2 py-1 text-sm">

                @for($y = now()->year; $y >= now()->year - 3; $y--)
                    <option value="{{ $y }}"
                        {{ request('year', now()->year) == $y ? 'selected' : '' }}>
                        {{ $y }}
                    </option>
                @endfor

            </select>

        </div>
    </div>

</div>

        </form>

           @php
           $isAdmin = auth()->user()->role ?? null; // sesuaikan kalau nama kolom beda
           @endphp

            @if($isAdmin === 'admin')
           <a href="{{ route('online-orders.omzet') }}"
              class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded shadow font-semibold">
            Omzet
          </a>
            @endif

        <!-- CUSTOMER -->
        <a href="{{ route('customers.data') }}"
           class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded shadow font-semibold">
            Customer
        </a>

        <!-- BUAT ORDER -->
        <a href="/online-orders/create"
           class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow">
            + Order
        </a>

    </div>

</div>

    <!-- TABLE -->
     @if(session('error'))
    <div class="bg-red-100 text-red-700 p-3 mb-4 rounded">
        {{ session('error') }}
    </div>
@endif
   <div class="space-y-4">

@forelse($orders as $order)

<div id="order-{{ $order->id }}" class="bg-white border rounded-lg shadow p-4">

    <!-- HEADER -->
    <div class="flex justify-between items-start mb-2">

        <div>
            <div class="font-bold text-lg">
                {{ $order->customer_name }}
            </div>

            @if($order->total_price)
           <div class="text-sm text-emerald-600 font-semibold">
            Rp {{ number_format($order->total_price, 0, ',', '.') }}
           </div>
            @endif

            @php
    $orderDate = \Carbon\Carbon::parse($order->order_date);
    $deadline = $orderDate->copy()->addDay();
@endphp

<div class="text-xs text-gray-500">
    Order: {{ $orderDate->format('d M Y') }} 
    | 
    <span class="text-red-600 font-semibold">
        Deadline: {{ $deadline->format('d M Y') }}
    </span>
</div>

            <div class="text-sm text-blue-600 font-semibold">
                {{ $order->package_name ?? '-' }}
            </div>
        </div>

        <form method="POST" action="/online-orders/{{ $order->id }}/update-status">
    @csrf

    <select name="status"
        onchange="this.form.submit()"
        class="border rounded px-2 py-1 text-sm">

        <option value="on_process" {{ $order->status == 'on_process' ? 'selected' : '' }}>
            On Process
        </option>

        <option value="done" {{ $order->status == 'done' ? 'selected' : '' }}>
            Done
        </option>

        <option value="returned" {{ $order->status == 'returned' ? 'selected' : '' }}>
            Return
        </option>

        <option value="cancelled" {{ $order->status == 'cancelled' ? 'selected' : '' }}>
            Cancel
        </option>

    </select>
</form>

    </div>

    <!-- ITEMS -->
    <div class="border-t pt-2">

    @if(isset($items[$order->id]))

        @foreach($items[$order->id] as $item)

        @php
            $check = null;

            if (isset($checks[$order->id])) {
                foreach ($checks[$order->id] as $c) {
                    if (
                        $c->product_id == $item->product_id &&
                        $c->product_variant_id == $item->product_variant_id
                    ) {
                        $check = $c;
                        break;
                    }
                }
            }
        @endphp

        <div class="text-sm mb-1 flex justify-between">

            <div>
                - {{ $item->product_name }} ({{ $item->variant_name }}) 
                x {{ $item->qty }}
            </div>

            <div class="text-right">

            @if($check)

                @php
                    $stokAwal = $check->stock_before ?? 0;
                    $stokSisa = $check->stock_after ?? 0;
                    $dipakai = ($check->stock_before ?? 0) - ($check->stock_after ?? 0);
                @endphp

                @if(($check->shortage_qty ?? 0) <= 0)
                    <span class="text-green-600 font-semibold">
                        ✔ cukup
                    </span>
                @else
                    <span class="text-red-600 font-semibold">
                        ✖ kurang {{ $check->shortage_qty }}
                    </span>
                @endif

                @if($check->status == 'kurang')
    <div class="text-xs text-red-500">
        ⚠ stok tidak cukup, tidak bisa DONE
    </div>
@endif

                <div class="text-xs text-gray-500">
                    {{ $stokAwal }} → {{ $stokSisa }} | pakai {{ $dipakai }}
                </div>

            @endif

            </div>

        </div>

        @endforeach

    @else
        <div class="text-gray-400 text-sm">
            Tidak ada item
        </div>
    @endif

    </div>

    <!-- AKSI -->
    <div class="flex justify-end gap-2 mt-3">

     <a href="/online-orders/{{ $order->id }}/send-resi"
       target="_blank"
       class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-xs">
            Update Resi
     </a>

        <!-- 🔥 TOMBOL WA -->
    <form action="{{ route('online-orders.send-wa', $order->id) }}"
          method="POST"
          onsubmit="return confirm('Kirim notifikasi WA untuk order ini?')">
        @csrf
        <button
            class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-xs">
            WA
        </button>
    </form>

        <a href="/online-orders/{{ $order->id }}/edit"
           class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded text-xs">
            Edit
        </a>

        <form action="/online-orders/{{ $order->id }}"
              method="POST"
              onsubmit="return confirm('Yakin hapus order ini?')">
            @csrf
            @method('DELETE')

            <button
                class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-xs">
                Delete
            </button>
        </form>

    </div>

</div>

@empty

<div class="text-center py-6 text-gray-500">
    Belum ada order
</div>

@endforelse

</div>

</div>
</div>
</div>

<script>
const input = document.getElementById('searchInput');
const dropdown = document.getElementById('searchDropdown');

let timeout = null;

input.addEventListener('keyup', function () {
    clearTimeout(timeout);

    let query = this.value;

    if (query.length < 2) {
        dropdown.classList.add('hidden');
        return;
    }

    timeout = setTimeout(() => {

        fetch(`/online-orders/search?search=${query}`)
            .then(res => res.json())
            .then(data => {

                dropdown.innerHTML = '';

                if (data.length === 0) {
                    dropdown.innerHTML = `<div class="p-2 text-sm text-gray-500">Tidak ditemukan</div>`;
                }

                data.forEach(item => {
                    let div = document.createElement('div');
div.className = 'p-2 hover:bg-gray-100 cursor-pointer text-sm';

div.innerHTML = `
    <div class="font-semibold">${item.customer_name}</div>
    <div class="text-xs text-gray-500">Order ID: ${item.id}</div>
`;

div.addEventListener('click', function () {

    div.addEventListener('click', function () {

    input.value = item.customer_name;

    window.location.href = `/online-orders?search=${encodeURIComponent(item.customer_name)}`;

});

    if (el) {
        el.scrollIntoView({ behavior: 'smooth', block: 'center' });

        el.classList.add('ring', 'ring-green-500');

        setTimeout(() => {
            el.classList.remove('ring', 'ring-green-500');
        }, 3000);
    } else {
        alert('Order tidak ada di halaman ini (kena filter)');
    }

    dropdown.classList.add('hidden');
});

dropdown.appendChild(div);
                });

                dropdown.classList.remove('hidden');

            });

    }, 300); // debounce biar nggak brutal ke server
});

</script>

<script>
const urlParams = new URLSearchParams(window.location.search);

if (highlightId) {
    const el = document.getElementById('order-' + highlightId);

    if (el) {
        el.scrollIntoView({ behavior: 'smooth', block: 'center' });

        el.classList.add('ring', 'ring-green-500');

        setTimeout(() => {
            el.classList.remove('ring', 'ring-green-500');
        }, 3000);
    }
}
</script>

</x-app-layout>