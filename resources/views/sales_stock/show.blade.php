<x-app-layout>
    <div class="p-6 max-w-6xl mx-auto">

        <h2 class="text-2xl font-bold mb-4">
            Audit Ledger - {{ $product->name }}
        </h2>

        <div class="mb-4">
            <strong>Saldo Akhir:</strong>
            <span class="{{ $runningBalance < 0 ? 'text-red-600' : 'text-green-600' }}">
                {{ $runningBalance }}
            </span>
        </div>

        <table class="w-full border border-gray-300 text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="border p-2">Tanggal</th>
                    <th class="border p-2">Type</th>
                    <th class="border p-2">Qty</th>
                    <th class="border p-2">Running Balance</th>
                    <th class="border p-2">Reference</th>
                </tr>
            </thead>
            <tbody>
                @foreach($movements as $row)
                    <tr>
                        <td class="border p-2">{{ $row['date'] }}</td>
                        <td class="border p-2">{{ $row['type'] }}</td>
                        <td class="border p-2 {{ $row['quantity'] < 0 ? 'text-red-600' : 'text-green-600' }}">
                            {{ $row['quantity'] }}
                        </td>
                        <td class="border p-2 font-semibold">
                            {{ $row['running_balance'] }}
                        </td>
                        <td class="border p-2">
                            {{ $row['reference_type'] }} #{{ $row['reference_id'] }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

    </div>
</x-app-layout>
