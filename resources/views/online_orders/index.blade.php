<x-app-layout>

<div class="py-8">
<div class="max-w-5xl mx-auto sm:px-6 lg:px-8">

<div class="bg-white shadow rounded-lg p-6">

    <!-- HEADER -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">
                List Order Online
            </h2>
        </div>

        <a href="/online-orders/create"
           class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow">
            + Buat Order
        </a>
    </div>

    <!-- TABLE -->
     @if(session('error'))
    <div class="bg-red-100 text-red-700 p-3 mb-4 rounded">
        {{ session('error') }}
    </div>
@endif
   <div class="space-y-4">

@forelse($orders as $order)

<div class="bg-white border rounded-lg shadow p-4">

    <!-- HEADER -->
    <div class="flex justify-between items-start mb-2">

        <div>
            <div class="font-bold text-lg">
                {{ $order->customer_name }}
            </div>

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

</x-app-layout>