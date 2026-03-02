<x-app-layout>
<x-slot name="header">
    <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
        Report Margin Real
    </h2>
</x-slot>

<div class="py-8">
<div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

    {{-- FILTER --}}
    <div style="background:white;padding:20px;border-radius:16px;box-shadow:0 10px 20px rgba(0,0,0,0.08);">
        <form method="GET" action="{{ route('reports.margin.index') }}" style="display:flex;gap:20px;align-items:end;flex-wrap:wrap;">

            <div>
                <label>Dari Tanggal</label><br>
                <input type="date" name="from"
                       value="{{ request('from', $from->toDateString()) }}"
                       style="padding:8px;border:1px solid #ddd;border-radius:8px;">
            </div>

            <div>
                <label>Sampai Tanggal</label><br>
                <input type="date" name="to"
                       value="{{ request('to', $to->toDateString()) }}"
                       style="padding:8px;border:1px solid #ddd;border-radius:8px;">
            </div>

            <div>
                <button type="submit"
                        style="background:#2563eb;color:white;padding:10px 18px;border-radius:10px;border:none;font-weight:600;">
                    Filter
                </button>
            </div>

        </form>
    </div>

    {{-- SUMMARY --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:20px;">

        <div style="background:white;padding:20px;border-radius:16px;box-shadow:0 10px 20px rgba(0,0,0,0.08);">
            <div style="font-size:13px;color:#6b7280;">Total Penjualan</div>
            <div style="font-size:20px;font-weight:700;">
                Rp {{ number_format($summary['total_penjualan'],0,',','.') }}
            </div>
        </div>

        <div style="background:white;padding:20px;border-radius:16px;box-shadow:0 10px 20px rgba(0,0,0,0.08);">
            <div style="font-size:13px;color:#6b7280;">Total Fee</div>
            <div style="font-size:20px;font-weight:700;">
                Rp {{ number_format($summary['total_fee'],0,',','.') }}
            </div>
        </div>

        <div style="background:white;padding:20px;border-radius:16px;box-shadow:0 10px 20px rgba(0,0,0,0.08);">
            <div style="font-size:13px;color:#6b7280;">Total HPP</div>
            <div style="font-size:20px;font-weight:700;">
                Rp {{ number_format($summary['total_hpp'],0,',','.') }}
            </div>
        </div>

        <div style="background:white;padding:20px;border-radius:16px;box-shadow:0 10px 20px rgba(0,0,0,0.08);">
            <div style="font-size:13px;color:#6b7280;">Total Margin</div>
            <div style="font-size:20px;font-weight:700;">
                Rp {{ number_format($summary['total_margin'],0,',','.') }}
            </div>
            <div style="font-size:13px;color:#16a34a;font-weight:600;">
                {{ number_format($summary['margin_percent'],2) }} %
            </div>
        </div>

    </div>

    {{-- TABLE --}}
    <div style="background:white;border-radius:16px;box-shadow:0 10px 20px rgba(0,0,0,0.08);overflow:hidden;">
        <table style="width:100%;border-collapse:collapse;font-size:14px;">
            <thead style="background:#f3f4f6;">
                <tr>
                    <th style="padding:12px;text-align:left;">Transaksi</th>
                    <th style="padding:12px;text-align:left;">Tanggal</th>
                    <th style="padding:12px;text-align:left;">Toko</th>
                    <th style="padding:12px;text-align:right;">Total Penjualan</th>
                    <th style="padding:12px;text-align:right;">Total Fee</th>
                    <th style="padding:12px;text-align:right;">Total HPP</th>
                    <th style="padding:12px;text-align:right;">Margin</th>
                    <th style="padding:12px;text-align:right;">Margin %</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reports as $row)
                @php
                    $marginPercent = $row->total_penjualan > 0
                        ? ($row->margin / $row->total_penjualan) * 100
                        : 0;
                @endphp
                <tr style="border-top:1px solid #e5e7eb;">
                    <td style="padding:12px;font-weight:600;">
                        #{{ $row->transaction_id }}
                    </td>
                    <td style="padding:12px;">
                        {{ $row->transaction_date }}
                    </td>
                    <td style="padding:12px;">
                        {{ $row->store_name }}
                    </td>
                    <td style="padding:12px;text-align:right;">
                        Rp {{ number_format($row->total_penjualan,0,',','.') }}
                    </td>
                    <td style="padding:12px;text-align:right;">
                        Rp {{ number_format($row->total_fee,0,',','.') }}
                    </td>
                    <td style="padding:12px;text-align:right;">
                        Rp {{ number_format($row->total_hpp,0,',','.') }}
                    </td>
                    <td style="padding:12px;text-align:right;font-weight:700;">
                        Rp {{ number_format($row->margin,0,',','.') }}
                    </td>
                    <td style="padding:12px;text-align:right;">
                        {{ number_format($marginPercent,2) }} %
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="padding:20px;text-align:center;color:#6b7280;">
                        Belum ada data transaksi pada periode ini.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>
</div>
</x-app-layout>
