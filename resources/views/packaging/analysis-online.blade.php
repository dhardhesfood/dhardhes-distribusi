<x-app-layout>

<div class="p-6 max-w-5xl mx-auto">

<h2 class="text-xl font-bold mb-4">
    Analisa Kemasan Order Online
</h2>

<!-- KETERANGAN -->
<div class="text-xs text-gray-500 mb-4">
    Keterangan:
    <span class="text-blue-600 font-semibold">Produk</span> = stok barang jadi |
    <span class="text-purple-600 font-semibold">Kemasan</span> = stok kemasan saat ini
</div>

@forelse($data as $productId => $items)

<div class="bg-white p-4 rounded shadow mb-4">

    <div class="font-semibold">
    {{ $items[0]->package_name }}
</div>

<div class="text-xs text-gray-500 mb-2">
    @php
    $deadlineDate = \Carbon\Carbon::parse($items[0]->deadline);
@endphp

Order: {{ \Carbon\Carbon::parse($items[0]->order_date)->format('d M Y') }} 
| 
<span class="text-red-600 font-semibold">
    Deadline: {{ $deadlineDate->format('d M Y') }}
</span>
</div>

    @foreach($items as $item)

    <div class="flex justify-between text-sm border-b py-1">

        <!-- NAMA VARIANT -->
        <div>
            {{ $item->product_name }} - {{ $item->variant_name }}
        </div>

        <!-- DATA -->
        <div>

            butuh {{ $item->required_qty }} |

            <!-- STOK PRODUK -->
            <span class="text-blue-600">
                produk: {{ $item->product_stock_before }}
                @if(isset($item->product_stock_after))
                    → {{ $item->product_stock_after }}
                @endif
            </span> |

            <!-- STOK KEMASAN -->
            <span class="text-purple-600">
                kemasan: {{ $item->packaging_stock_before }}
                @if(isset($item->packaging_stock_after))
                    → {{ $item->packaging_stock_after }}
                @endif
            </span>

            <!-- STATUS -->
            @if($item->status == 'cukup')
                <span class="text-green-600 font-semibold">
                    ✔ cukup
                </span>
            @else
                <span class="text-red-600 font-semibold">
                    ✖ kurang {{ $item->shortage_qty }}
                </span>
            @endif

        </div>

    </div>

    @endforeach

</div>

@empty
<div class="text-gray-500">
    Tidak ada data
</div>
@endforelse

</div>

</x-app-layout>