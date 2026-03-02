<x-app-layout>

<div class="py-6 px-4 max-w-7xl mx-auto space-y-8">

    <h2 class="text-2xl font-bold">
        KPI Sales Bulan Ini
    </h2>


    {{-- ================= SUMMARY ================= --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">

        <div class="bg-white shadow rounded p-5">
            <div class="text-sm text-gray-500">Visit Bulan Ini</div>
            <div class="text-2xl font-bold mt-2">
                {{ $totalVisits }}
            </div>
        </div>

        <div class="bg-white shadow rounded p-5">
            <div class="text-sm text-gray-500">Omzet Bulan Ini</div>
            <div class="text-2xl font-bold mt-2">
                Rp {{ number_format($totalOmzet,0,',','.') }}
            </div>
        </div>

        <div class="bg-white shadow rounded p-5">
            <div class="text-sm text-gray-500">Avg Omzet / Visit</div>
            <div class="text-2xl font-bold mt-2">
                Rp {{ number_format($avgOmzetPerVisit,0,',','.') }}
            </div>
        </div>

        <div class="bg-white shadow rounded p-5">
            <div class="text-sm text-gray-500">Settlement Delay (Hari)</div>
            <div class="text-2xl font-bold mt-2">
                {{ $maxDelay }}
            </div>
        </div>

    </div>


    {{-- ================= GRAFIK ================= --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <div class="bg-white shadow rounded p-5">
            <h3 class="font-semibold mb-4">Grafik Omzet Harian</h3>
            <canvas id="omzetChart"></canvas>
        </div>

        <div class="bg-white shadow rounded p-5">
            <h3 class="font-semibold mb-4">Grafik Visit Harian</h3>
            <canvas id="visitChart"></canvas>
        </div>

    </div>


    {{-- ================= RANKING ================= --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        {{-- Ranking Toko --}}
        <div class="bg-white shadow rounded p-5">
            <h3 class="font-semibold mb-4">Top 10 Toko</h3>

            <div class="space-y-2">
                @forelse($rankingStores as $store)
                    <div class="flex justify-between border-b pb-2">
                        <span>{{ $store->store_name }}</span>
                        <span class="font-semibold">
                            Rp {{ number_format($store->total,0,',','.') }}
                        </span>
                    </div>
                @empty
                    <div class="text-gray-500 text-sm">
                        Tidak ada data.
                    </div>
                @endforelse
            </div>
        </div>


        {{-- Ranking Produk --}}
        <div class="bg-white shadow rounded p-5">
            <h3 class="font-semibold mb-4">Top 10 Produk</h3>

            <div class="space-y-2">
                @forelse($rankingProducts as $product)
                    <div class="flex justify-between border-b pb-2">
                        <span>{{ $product->product_name }}</span>
                        <span class="font-semibold">
                            Rp {{ number_format($product->total,0,',','.') }}
                        </span>
                    </div>
                @empty
                    <div class="text-gray-500 text-sm">
                        Tidak ada data.
                    </div>
                @endforelse
            </div>
        </div>

    </div>

</div>


{{-- ================= CHART JS ================= --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    const omzetCtx = document.getElementById('omzetChart');
    const visitCtx = document.getElementById('visitChart');

    const omzetData = {
        labels: {!! json_encode($dailyOmzet->pluck('date')) !!},
        datasets: [{
            label: 'Omzet',
            data: {!! json_encode($dailyOmzet->pluck('total')) !!},
            borderWidth: 2,
            tension: 0.3
        }]
    };

    const visitData = {
        labels: {!! json_encode($dailyVisits->pluck('date')) !!},
        datasets: [{
            label: 'Visit',
            data: {!! json_encode($dailyVisits->pluck('total')) !!},
            borderWidth: 2,
            tension: 0.3
        }]
    };

    new Chart(omzetCtx, {
        type: 'line',
        data: omzetData,
    });

    new Chart(visitCtx, {
        type: 'line',
        data: visitData,
    });
</script>

</x-app-layout>
