<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Tambah Toko
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

            @if ($errors->any())
                <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded">
                    <ul class="list-disc pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white shadow sm:rounded-lg p-6">
                <form method="POST" action="{{ route('stores.store') }}">
                    @csrf

                    <!-- Area -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">
                            Area
                        </label>
                        <select name="area_id"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            <option value="">-- Pilih Area --</option>
                            @foreach($areas as $area)
                                <option value="{{ $area->id }}"
                                    {{ old('area_id') == $area->id ? 'selected' : '' }}>
                                    {{ $area->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Nama Toko -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">
                            Nama Toko
                        </label>
                        <input type="text"
                               name="name"
                               value="{{ old('name') }}"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                    </div>

                    <!-- Nama Pemilik -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">
                            Nama Pemilik
                        </label>
                        <input type="text"
                               name="owner_name"
                               value="{{ old('owner_name') }}"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                    </div>

                    <!-- No HP -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">
                            No HP
                        </label>
                        <input type="text"
                               name="phone"
                               value="{{ old('phone') }}"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                    </div>

                    <!-- Kota -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">
                            Kota
                        </label>
                        <input type="text"
                               name="city"
                               value="{{ old('city') }}"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                    </div>

                    <!-- Alamat -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">
                            Alamat
                        </label>
                        <textarea name="address"
                                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">{{ old('address') }}</textarea>
                    </div>

                    <!-- Interval Kunjungan -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">
                            Interval Kunjungan (hari)
                        </label>
                        <input type="number"
                               name="visit_interval_days"
                               value="{{ old('visit_interval_days', 35) }}"
                               min="1"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                    </div>

                    <!-- Status -->
                    <div class="mb-6">
                        <label class="inline-flex items-center">
                            <input type="checkbox"
                                   name="is_active"
                                   value="1"
                                   {{ old('is_active', 1) ? 'checked' : '' }}>
                            <span class="ml-2 text-sm text-gray-600">Aktif</span>
                        </label>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit"
                                class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-md shadow">
                            Simpan
                        </button>
                    </div>

                </form>
            </div>

        </div>
    </div>
</x-app-layout>
