<x-app-layout>

<div class="p-6 max-w-6xl mx-auto space-y-6">

    {{-- INFO --}}
    <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded-lg shadow">
        <div class="font-bold text-yellow-700 mb-1">
            ⚠️ Perhatian Produksi
        </div>

        <div class="text-sm text-gray-700 space-y-1">
            <div>Data berdasarkan stok pack siap jual.</div>
            <div>Pastikan cek stok belum dipack di gudang.</div>

            <div class="mt-2 font-semibold">
                👉 Sebelum produksi:
            </div>

            <ul class="list-disc ml-5">
                <li>Cek stok gudang</li>
                <li>Sesuaikan kebutuhan</li>
            </ul>

            <div class="text-red-600 font-semibold mt-2">
                Hindari over produksi
            </div>
        </div>
    </div>

    <div class="bg-white p-4 rounded-xl shadow flex justify-between items-center">
        <h2 class="text-lg font-bold">Analisa Kemasan Offline</h2>

        <a href="/packaging"
           class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded">
            ← Kembali
        </a>
    </div>

    @if(!empty($packagingAnalysis))

    <div class="space-y-4">

        @foreach($packagingAnalysis as $date => $productGroups)

            <div class="bg-white p-4 rounded-xl shadow">

                <div class="font-bold text-lg mb-3">
                    📅 {{ \Carbon\Carbon::parse($date)->format('d-m-Y') }}
                </div>

                @foreach($productGroups as $productName => $items)

                    <div class="mb-4">

                        <div class="font-semibold mb-2">
                            {{ $productName }}
                        </div>

                        <div class="space-y-1">

                            @foreach($items as $item)

                                <div class="flex justify-between border-b py-1 text-sm">

                                    <div class="text-red-600">
                                        {{ $item['variant'] }}
                                    </div>

                                    <div>
                                        butuh {{ $item['needed'] }} |
                                        stok {{ $item['stock'] }} |
                                        <span class="{{ $item['short'] > 0 ? 'text-red-600 font-bold' : 'text-green-600' }}">
                                            {{ $item['short'] > 0 ? 'kurang '.$item['short'] : 'cukup' }}
                                        </span>
                                    </div>

                                </div>

                            @endforeach

                        </div>

                    </div>

                @endforeach

            </div>

        @endforeach

    </div>

    @else
        <div class="text-center text-gray-500">
            Tidak ada kebutuhan kemasan
        </div>
    @endif

</div>

</x-app-layout>