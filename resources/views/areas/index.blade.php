<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Master Area
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">

            @if (session('success'))
                <div class="mb-6 p-4 bg-green-100 text-green-700 rounded-md">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-6 p-4 bg-red-100 text-red-700 rounded-md">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Tombol Tambah (Admin Only) -->
            @if(auth()->user()->role === 'admin')
                <div class="mb-6">
                    <a href="{{ route('areas.create') }}"
                       class="inline-block px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-md shadow transition">
                        + Tambah Area
                    </a>
                </div>
            @endif

            <div class="bg-white shadow-md rounded-lg overflow-hidden">
                <table class="min-w-full border border-gray-200">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-4 py-2 border text-left text-sm font-semibold text-gray-700">ID</th>
                            <th class="px-4 py-2 border text-left text-sm font-semibold text-gray-700">Nama</th>
                            <th class="px-4 py-2 border text-left text-sm font-semibold text-gray-700">Kode</th>
                            <th class="px-4 py-2 border text-left text-sm font-semibold text-gray-700">Status</th>
                            <th class="px-4 py-2 border text-left text-sm font-semibold text-gray-700">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm text-gray-700">
                        @forelse($areas as $area)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 border">{{ $area->id }}</td>

                                <td class="px-4 py-2 border">
                                    <a href="{{ route('stores.index', array_filter([
                                        'area_id'  => $area->id,
                                        'sales_id' => request('sales_id')
                                    ])) }}"
                                       class="text-blue-600 hover:text-blue-800 font-semibold underline">
                                        {{ $area->name }}
                                    </a>
                                </td>

                                <td class="px-4 py-2 border">{{ $area->code }}</td>

                                <td class="px-4 py-2 border">
                                    @if($area->is_active)
                                        <span class="px-2 py-1 bg-green-100 text-green-700 rounded text-xs font-semibold">
                                            Aktif
                                        </span>
                                    @else
                                        <span class="px-2 py-1 bg-red-100 text-red-700 rounded text-xs font-semibold">
                                            Nonaktif
                                        </span>
                                    @endif
                                </td>

                                <td class="px-4 py-2 border">
                                    @if(auth()->user()->role === 'admin')
                                        <a href="{{ route('areas.edit', $area->id) }}"
                                           class="text-blue-600 hover:underline mr-3">
                                            Edit
                                        </a>

                                        <form action="{{ route('areas.destroy', $area->id) }}"
                                              method="POST"
                                              style="display:inline;"
                                              onsubmit="return confirm('Yakin ingin menghapus area ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="text-red-600 hover:underline"
                                                    style="background:none; border:none; padding:0;">
                                                Hapus
                                            </button>
                                        </form>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-4 border text-center text-gray-500">
                                    Belum ada data
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</x-app-layout>