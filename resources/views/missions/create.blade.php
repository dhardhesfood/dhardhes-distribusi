<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-lg text-gray-800 leading-tight">
            Tambah Misi Sales
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white shadow sm:rounded-lg p-6">

                <form method="POST" action="{{ route('missions.store') }}">
                    @csrf

                    <div class="mb-4">
                        <x-input-label for="title" value="Judul Misi" />
                        <x-text-input id="title" name="title" type="text" class="mt-1 block w-full" required />
                        <x-input-error :messages="$errors->get('title')" class="mt-2" />
                    </div>

                    <div class="mb-4">
                        <x-input-label for="type" value="Jenis Misi" />
                        <select name="type" id="type" class="border-gray-300 rounded w-full">
                            <option value="visit_count">Kunjungan Toko</option>
                            <option value="product_sales">Penjualan Produk</option>
                            <option value="new_store">Tambah Toko Baru</option>
                            <option value="revenue">Omzet Penjualan</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <x-input-label for="product_id" value="Produk (Opsional)" />
                        <select name="product_id" class="border-gray-300 rounded w-full">
                            <option value="">-- Tidak spesifik produk --</option>

                            @foreach($products as $product)
                                <option value="{{ $product->id }}">
                                    {{ $product->name }}
                                </option>
                            @endforeach

                        </select>
                    </div>

                    <div class="mb-4">
                        <x-input-label for="target" value="Target" />
                        <x-text-input id="target" name="target" type="number" class="mt-1 block w-full" required />
                    </div>

                    <div class="mb-4">
                        <x-input-label for="reward_amount" value="Reward (Rp)" />
                        <x-text-input id="reward_amount" name="reward_amount" type="number" class="mt-1 block w-full" required />
                    </div>

                    <div class="mb-4 grid grid-cols-2 gap-4">

                        <div>
                            <x-input-label for="start_date" value="Tanggal Mulai" />
                            <x-text-input id="start_date" name="start_date" type="date" class="mt-1 block w-full" required />
                        </div>

                        <div>
                            <x-input-label for="end_date" value="Tanggal Selesai" />
                            <x-text-input id="end_date" name="end_date" type="date" class="mt-1 block w-full" required />
                        </div>

                    </div>

                    <div class="mb-4">
                        <label class="flex items-center">
                            <input type="checkbox" name="active" checked class="mr-2">
                            Misi Aktif
                        </label>
                    </div>

                    <div class="mt-6">
                        <x-primary-button>
                            Simpan Misi
                        </x-primary-button>
                    </div>

                </form>

            </div>

        </div>
    </div>
</x-app-layout>