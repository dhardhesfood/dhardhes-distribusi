<x-app-layout>

<div class="py-6 px-6 max-w-6xl mx-auto">

    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-bold">
            Tutup Session Stok Sales
        </h2>

        <a href="{{ route('sales-stock-sessions.show', $session->id) }}"
           class="bg-gray-500 text-white px-4 py-2 rounded">
            Kembali
        </a>
    </div>

    @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 p-4 rounded mb-6">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white shadow rounded p-6">

        @if(in_array(auth()->user()->role, ['admin','admin_gudang']))

        <form method="POST"
              action="{{ route('sales-stock-sessions.close', $session->id) }}">
            @csrf

            <table class="w-full border text-sm">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="border p-2 text-left">Produk</th>
                        <th class="border p-2 text-center">Stok Sistem</th>
                        <th class="border p-2 text-center">Stok Fisik</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($session->items as $item)

                        <tr class="text-center">
                            <td class="border p-2 text-left">
                                {{ $item->product->name }}
                            </td>

                            <td class="border p-2">
                                {{ $item->system_remaining_qty }}
                            </td>

                            <td class="border p-2">
                                <input type="number"
                                       name="physical_qty[{{ $item->product_id }}]"
                                       value="{{ $item->system_remaining_qty }}"
                                       min="0"
                                       class="border p-1 rounded w-24 text-center">
                            </td>
                        </tr>

                    @endforeach
                </tbody>
            </table>

            <div class="mt-6 text-right">
                <button type="submit"
                        class="bg-red-600 text-white px-6 py-2 rounded font-semibold">
                    Tutup Session
                </button>
            </div>

        </form>

        @else

            <div class="text-center text-gray-600 font-medium py-10">
                Anda tidak memiliki akses untuk menutup session.
            </div>

        @endif

    </div>

</div>

</x-app-layout>