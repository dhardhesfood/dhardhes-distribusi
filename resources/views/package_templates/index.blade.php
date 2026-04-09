<x-app-layout>

<div class="p-6 max-w-5xl mx-auto">

    <div class="flex justify-between mb-4">
        <h2 class="text-xl font-bold">Master Paket</h2>

        <a href="/package-templates/create"
            class="bg-green-600 text-white px-4 py-2 rounded">
            + Tambah Paket
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 p-3 mb-3 rounded">
            {{ session('success') }}
        </div>
    @endif

    <table class="w-full border">
        <thead class="bg-gray-100">
            <tr>
                <th class="p-2 text-left">Nama Paket</th>
                <th class="p-2 text-center">Aksi</th>
            </tr>
        </thead>

        <tbody>
        @foreach($templates as $t)
            <tr class="border-t">
                <td class="p-2">{{ $t->name }}</td>
                <td class="p-2 text-center">
                    <form method="POST" action="/package-templates/{{ $t->id }}">
                        @csrf
                        @method('DELETE')
                        <button class="bg-red-500 text-white px-3 py-1 rounded">
                            Hapus
                        </button>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

</div>

</x-app-layout>