<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-lg text-gray-800 leading-tight">
                Daftar Misi Sales
            </h2>

            <a href="{{ route('missions.create') }}"
               class="bg-blue-600 text-white px-4 py-2 rounded">
                + Tambah Misi
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="bg-green-100 text-green-700 p-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white shadow sm:rounded-lg p-6">

                <table class="w-full border">

                    <thead class="bg-gray-100">
                        <tr>
                            <th class="p-2 border">ID</th>
                            <th class="p-2 border">Judul</th>
                            <th class="p-2 border">Jenis</th>
                            <th class="p-2 border">Target</th>
                            <th class="p-2 border">Reward</th>
                            <th class="p-2 border">Periode</th>
                            <th class="p-2 border">Status</th>
                            <th class="p-2 border">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse($missions as $mission)

                            <tr>
                                <td class="border p-2">{{ $mission->id }}</td>
                                <td class="border p-2">{{ $mission->title }}</td>
                                <td class="border p-2">{{ $mission->type }}</td>
                                <td class="border p-2">{{ $mission->target }}</td>

                                <td class="border p-2">
                                    Rp {{ number_format($mission->reward_amount) }}
                                </td>

                                <td class="border p-2">
                                    {{ $mission->start_date }}
                                    -
                                    {{ $mission->end_date }}
                                </td>

                                <td class="border p-2">
                                    @if($mission->active)
                                        <span class="text-green-600 font-bold">Aktif</span>
                                    @else
                                        <span class="text-gray-500">Nonaktif</span>
                                    @endif
                                </td>

                                <td class="border p-2">

    <div class="flex gap-2">

        <a href="{{ route('missions.edit', $mission->id) }}"
           class="bg-yellow-500 text-white px-3 py-1 rounded text-sm">
            Edit
        </a>

        <form action="{{ route('missions.destroy', $mission->id) }}"
              method="POST"
              onsubmit="return confirm('Yakin ingin menghapus misi ini?')">

            @csrf
            @method('DELETE')

            <button class="bg-red-600 text-white px-3 py-1 rounded text-sm">
                Hapus
            </button>

        </form>

    </div>

</td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="8" class="text-center p-4">
                                    Belum ada misi
                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>
    </div>
</x-app-layout>