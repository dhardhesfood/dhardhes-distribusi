<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Stok Gudang
        </h2>
    </x-slot>

```
<div class="py-6">
    <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">

        <div class="bg-white shadow-sm sm:rounded-lg p-6">

            {{-- TOMBOL PENYESUAIAN STOK (HANYA ADMIN) --}}
            @if(auth()->user()->role === 'admin')
                <div class="mb-4">
                    <a href="{{ route('warehouse.adjustment.create') }}"
                       class="bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded shadow-sm">
                        Penyesuaian Stok
                    </a>
                </div>
            @endif

            <table class="w-full border">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="p-2 border text-left">Produk</th>
                        <th class="p-2 border text-right">Stok Gudang</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($stocks as $stock)
                        <tr>
                            <td class="p-2 border">
                                {{ $stock->name }}
                            </td>

                            <td class="p-2 border text-right font-semibold">
                                {{ $stock->stock }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

                <div class="mt-6 space-y-3">

                    @foreach($notes as $note)

                    @php
                    $color = match($note->user->role) {
                        'admin' => 'text-red-600',
                        'admin_gudang' => 'text-purple-600',
                        'sales' => 'text-blue-600',
                        default => 'text-gray-700'
                    };
                    @endphp

                    <div class="border rounded p-3 bg-gray-50">

                        <div class="text-xs text-gray-500">
                            {{ $note->created_at->format('d-m-Y H:i') }}
                            -
                            <span class="{{ $color }} font-semibold">
                                {{ $note->user->name }}
                            </span>
                        </div>

                        <div class="mt-1">
                            {{ $note->message }}
                        </div>

                    </div>

                    @endforeach

                </div>

                <div class="mt-8">

                <h3 class="font-semibold mb-2">Catatan Gudang</h3>

                <form method="POST" action="{{ route('warehouse.note.store') }}">
                    @csrf

                    <textarea
                        name="message"
                        rows="3"
                        class="w-full border rounded p-2 mb-2"
                        placeholder="Tulis pesan untuk tim..."></textarea>

                    <button
                        type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow-sm">
                        Kirim Catatan
                    </button>

                </form>

            </div>

        </div>

    </div>
</div>
```

</x-app-layout>
