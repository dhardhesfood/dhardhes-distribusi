<x-app-layout>
    <div class="p-6 max-w-6xl mx-auto">

        <h2 class="text-xl font-bold mb-4">Daftar Kunjungan</h2>

        <div class="mb-4 flex gap-2">

    <a href="{{ route('visits.index', ['range'=>'today']) }}"
       class="px-3 py-1 rounded text-sm {{ $range=='today' ? 'bg-blue-600 text-white' : 'bg-gray-200' }}">
        Hari Ini
    </a>

    <a href="{{ route('visits.index', ['range'=>'yesterday']) }}"
       class="px-3 py-1 rounded text-sm {{ $range=='yesterday' ? 'bg-blue-600 text-white' : 'bg-gray-200' }}">
        Kemarin
    </a>

    <a href="{{ route('visits.index', ['range'=>'7days']) }}"
       class="px-3 py-1 rounded text-sm {{ $range=='7days' ? 'bg-blue-600 text-white' : 'bg-gray-200' }}">
        7 Hari
    </a>

    <a href="{{ route('visits.index', ['range'=>'30days']) }}"
       class="px-3 py-1 rounded text-sm {{ $range=='30days' ? 'bg-blue-600 text-white' : 'bg-gray-200' }}">
        30 Hari
    </a>

</div>

        <form method="GET" class="mb-4 flex items-center gap-2">

    <input type="date"
           name="date"
           value="{{ $date }}"
           class="border p-2 rounded">

    <button type="submit"
            class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded text-sm">
        Filter
    </button>

</form>

        <table class="w-full border text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="border p-2">Tanggal</th>
                    <th class="border p-2">Toko</th>
                    <th class="border p-2">Status</th>
                    <th class="border p-2">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($visits as $visit)
                <tr class="text-center">
                    <td class="border p-2">
                        {{ \Carbon\Carbon::parse($visit->visit_date)->format('d-m-Y H:i') }}
                   </td>

                    <td class="border p-2">
                        {{ $visit->store->name ?? '-' }}
                    </td>

                    <td class="border p-2">
                        @if($visit->status === 'draft')
                            <span class="bg-yellow-500 text-white px-3 py-1 rounded text-xs">
                                Draft
                            </span>
                        @elseif($visit->status === 'completed')
                            <span class="bg-blue-600 text-white px-3 py-1 rounded text-xs">
                                Completed
                            </span>
                        @elseif($visit->status === 'approved')
                            <span class="bg-green-600 text-white px-3 py-1 rounded text-xs">
                                Approved
                            </span>
                        @else
                            <span class="bg-gray-500 text-white px-3 py-1 rounded text-xs capitalize">
                                {{ $visit->status }}
                            </span>
                        @endif
                    </td>

                    <td class="border p-2">
                        <div class="flex justify-center items-center gap-2 flex-nowrap">

                            <a href="{{ route('visits.show', $visit->id) }}"
                               class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-xs transition">
                                Detail
                            </a>

                            @if(auth()->user()->role === 'admin')

                                <a href="{{ route('visits.edit', $visit->id) }}"
                                   class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1 rounded text-xs transition">
                                    Edit
                                </a>

                                @if($visit->status === 'draft')
                                    <form action="{{ route('visits.destroy', $visit->id) }}"
                                          method="POST"
                                          onsubmit="return confirm('Yakin hapus kunjungan ini?')">
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit"
                                                class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-xs transition">
                                            Hapus
                                        </button>
                                    </form>
                                @endif

                            @endif

                        </div>
                    </td>
                </tr>

                @empty
                <tr>
                    <td colspan="4" class="border p-2 text-center text-gray-500">
                        Belum ada kunjungan
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

    </div>
</x-app-layout>