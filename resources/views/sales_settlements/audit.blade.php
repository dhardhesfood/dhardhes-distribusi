<x-app-layout>
<x-slot name="header">
    <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
        Audit Log Settlement
    </h2>
</x-slot>

<div class="py-8">
<div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">

    <div class="bg-white shadow rounded p-6">
        <h3 class="font-bold text-lg">
            Settlement {{ $settlement->settlement_date->format('d M Y') }}
            - {{ $settlement->user->name }}
        </h3>
    </div>

    @forelse($logs as $log)
        <div class="bg-white shadow rounded p-6 border">
            <div class="mb-4 text-sm text-gray-500">
                {{ $log->created_at->format('d M Y H:i') }}
                oleh {{ $log->user->name ?? '-' }}
            </div>

            <div class="grid grid-cols-2 gap-6 text-sm">

                <div>
                    <div class="font-semibold mb-2 text-red-600">
                        Nilai Sebelum
                    </div>
                    <pre class="bg-gray-100 p-3 rounded text-xs overflow-auto">
{{ json_encode($log->old_values, JSON_PRETTY_PRINT) }}
                    </pre>
                </div>

                <div>
                    <div class="font-semibold mb-2 text-green-600">
                        Nilai Sesudah
                    </div>
                    <pre class="bg-gray-100 p-3 rounded text-xs overflow-auto">
{{ json_encode($log->new_values, JSON_PRETTY_PRINT) }}
                    </pre>
                </div>

            </div>
        </div>
    @empty
        <div class="bg-white shadow rounded p-6 text-gray-500">
            Belum ada perubahan pada settlement ini.
        </div>
    @endforelse

    <div>
        <a href="{{ route('sales.settlements.index') }}"
           class="bg-gray-500 text-white px-4 py-2 rounded">
            Kembali
        </a>
    </div>

</div>
</div>
</x-app-layout>
