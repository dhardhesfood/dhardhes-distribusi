<x-app-layout>
<x-slot name="header">
    <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
        KPI Dashboard (Bulan Ini vs Bulan Lalu)
    </h2>
</x-slot>

<div class="py-8">
<div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

    {{-- Summary Cards --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:20px;">

        <div style="background:white;padding:20px;border-radius:16px;box-shadow:0 10px 20px rgba(0,0,0,0.08);">
            <div style="font-size:14px;color:#6b7280;">Penjualan Bulan Ini</div>
            <div style="font-size:22px;font-weight:700;">
                Rp {{ number_format($thisMonth['total_penjualan'],0,',','.') }}
            </div>
            <div style="margin-top:5px;color:{{ $growth['penjualan'] >= 0 ? 'green' : 'red' }};">
                {{ number_format($growth['penjualan'],2) }} %
            </div>
        </div>

        <div style="background:white;padding:20px;border-radius:16px;box-shadow:0 10px 20px rgba(0,0,0,0.08);">
            <div style="font-size:14px;color:#6b7280;">Margin Bulan Ini</div>
            <div style="font-size:22px;font-weight:700;">
                Rp {{ number_format($thisMonth['total_margin'],0,',','.') }}
            </div>
            <div style="margin-top:5px;color:{{ $growth['margin'] >= 0 ? 'green' : 'red' }};">
                {{ number_format($growth['margin'],2) }} %
            </div>
        </div>

        <div style="background:white;padding:20px;border-radius:16px;box-shadow:0 10px 20px rgba(0,0,0,0.08);">
            <div style="font-size:14px;color:#6b7280;">Qty Terjual</div>
            <div style="font-size:22px;font-weight:700;">
                {{ number_format($thisMonth['total_qty'],0,',','.') }}
            </div>
            <div style="margin-top:5px;color:{{ $growth['qty'] >= 0 ? 'green' : 'red' }};">
                {{ number_format($growth['qty'],2) }} %
            </div>
        </div>

        <div style="background:white;padding:20px;border-radius:16px;box-shadow:0 10px 20px rgba(0,0,0,0.08);">
            <div style="font-size:14px;color:#6b7280;">Margin %</div>
            <div style="font-size:22px;font-weight:700;">
                {{ number_format($thisMonth['margin_percent'],2) }} %
            </div>
        </div>

    </div>

    {{-- Comparison Table --}}
    <div style="background:white;border-radius:16px;box-shadow:0 10px 20px rgba(0,0,0,0.08);overflow:hidden;">
        <table style="width:100%;border-collapse:collapse;font-size:14px;">
            <thead style="background:#f3f4f6;">
                <tr>
                    <th style="padding:12px;text-align:left;">Periode</th>
                    <th style="padding:12px;text-align:right;">Penjualan</th>
                    <th style="padding:12px;text-align:right;">Margin</th>
                    <th style="padding:12px;text-align:right;">Qty</th>
                    <th style="padding:12px;text-align:right;">Margin %</th>
                </tr>
            </thead>
            <tbody>
                <tr style="border-top:1px solid #e5e7eb;">
                    <td style="padding:12px;font-weight:600;">Bulan Ini</td>
                    <td style="padding:12px;text-align:right;">
                        Rp {{ number_format($thisMonth['total_penjualan'],0,',','.') }}
                    </td>
                    <td style="padding:12px;text-align:right;">
                        Rp {{ number_format($thisMonth['total_margin'],0,',','.') }}
                    </td>
                    <td style="padding:12px;text-align:right;">
                        {{ number_format($thisMonth['total_qty'],0,',','.') }}
                    </td>
                    <td style="padding:12px;text-align:right;">
                        {{ number_format($thisMonth['margin_percent'],2) }} %
                    </td>
                </tr>

                <tr style="border-top:1px solid #e5e7eb;background:#f9fafb;">
                    <td style="padding:12px;font-weight:600;">Bulan Lalu</td>
                    <td style="padding:12px;text-align:right;">
                        Rp {{ number_format($lastMonth['total_penjualan'],0,',','.') }}
                    </td>
                    <td style="padding:12px;text-align:right;">
                        Rp {{ number_format($lastMonth['total_margin'],0,',','.') }}
                    </td>
                    <td style="padding:12px;text-align:right;">
                        {{ number_format($lastMonth['total_qty'],0,',','.') }}
                    </td>
                    <td style="padding:12px;text-align:right;">
                        {{ number_format($lastMonth['margin_percent'],2) }} %
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

</div>
</div>
</x-app-layout>
