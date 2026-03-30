<x-app-layout>

<x-slot name="header">
<h2 class="font-semibold text-xl text-gray-800 leading-tight">
🤖 AI Dhardhes
</h2>
</x-slot>

<div class="py-6">
<div class="max-w-6xl mx-auto sm:px-6 lg:px-8">

<div class="bg-white shadow-sm sm:rounded-xl p-6">

<div class="bg-white p-6 rounded shadow mb-4">

<h3 class="text-lg font-semibold mb-4 text-gray-800">
Ringkasan Bisnis
</h3>

<div class="grid grid-cols-2 gap-4 text-sm">

<div>
<b>Total produk di semua toko</b><br>
{{ number_format($summary['store_products_total'] ?? 0,0,',','.') }} pcs
</div>

<div>
<b>Nilai stok konsinyasi di toko</b><br>
Rp {{ number_format($summary['store_stock_value'] ?? 0,0,',','.') }}
</div>

<div>
<b>Omzet bulan ini</b><br>
Rp {{ number_format($summary['monthly_omzet'] ?? 0,0,',','.') }}
</div>

<div>
<b>Toko aktif</b><br>
{{ $summary['active_stores'] ?? 0 }}
</div>

<div>
<b>Kunjungan minggu ini</b><br>
{{ $summary['visits_week'] ?? 0 }}
</div>

<div>
<b>Produk terlaris</b><br>
{{ $summary['top_product'] ?? '-' }}
</div>

<div>
<b>Sales terbaik</b><br>
{{ $summary['top_sales'] ?? '-' }}
</div>

<div>
<b>Produk paling lambat</b><br>
{{ $summary['slow_product'] ?? '-' }}
</div>

</div>

<hr class="my-4">

<div class="grid grid-cols-3 gap-4 text-sm">

<div class="bg-yellow-50 border border-yellow-200 p-3 rounded-lg">
<b>Terlambat</b><br>
{{ $summary['late_stores'] ?? 0 }} toko
</div>

<div class="bg-orange-50 border border-orange-200 p-3 rounded-lg">
<b>Terlambat berat</b><br>
{{ $summary['heavy_late_stores'] ?? 0 }} toko
</div>

<div class="bg-red-50 border border-red-200 p-3 rounded-lg">
<b>Pertimbangkan ditarik</b><br>
{{ $summary['withdraw_stores'] ?? 0 }} toko
</div>

</div>

</div>


<div class="bg-white p-6 rounded shadow">

<h3 class="text-lg font-semibold mb-4 text-gray-800">
Analisa AI
</h3>

<div class="ai-text">
{!! nl2br(e(str_replace(['**'], '', $insight ?? 'Belum ada analisa'))) !!}
</div>

</div>

</div>

</div>
</div>

<style>

.ai-text{
font-family:'Inter',system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;
line-height:1.7;
font-size:15px;
color:#374151;
white-space:normal;
}

.ai-text b{
font-weight:600;
}

.ai-text ul{
margin-top:6px;
margin-bottom:10px;
padding-left:18px;
}

.ai-text li{
margin-bottom:4px;
}

</style>

</x-app-layout>