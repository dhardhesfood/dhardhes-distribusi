<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Edit Toko
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">

            @if ($errors->any())
                <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded">
                    <ul class="list-disc list-inside text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white shadow sm:rounded-lg p-6">

                <form method="POST" action="{{ route('stores.update', $store->id) }}">
                    @csrf
                    @method('PUT')

                    {{-- AREA --}}
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Area
                        </label>
                        <select name="area_id"
                                class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                            @foreach($areas as $area)
                                <option value="{{ $area->id }}"
                                    {{ old('area_id', $store->area_id) == $area->id ? 'selected' : '' }}>
                                    {{ $area->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- NAMA TOKO --}}
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Nama Toko
                        </label>
                        <input type="text"
                               name="name"
                               value="{{ old('name', $store->name) }}"
                               class="w-full border-gray-300 rounded-md shadow-sm text-sm"
                               @if(auth()->user()->role !== 'admin') readonly @endif>
                    </div>

                    {{-- OWNER --}}
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Nama Pemilik
                        </label>
                        <input type="text"
                               name="owner_name"
                               value="{{ old('owner_name', $store->owner_name) }}"
                               class="w-full border-gray-300 rounded-md shadow-sm text-sm"
                               @if(auth()->user()->role !== 'admin') readonly @endif>
                    </div>

                    {{-- PHONE --}}
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Telepon
                        </label>
                        <input type="text"
                               name="phone"
                               value="{{ old('phone', $store->phone) }}"
                               class="w-full border-gray-300 rounded-md shadow-sm text-sm"
                               @if(auth()->user()->role !== 'admin') readonly @endif>
                    </div>

                    {{-- ADDRESS --}}
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Alamat
                        </label>
                        <textarea name="address"
                                  class="w-full border-gray-300 rounded-md shadow-sm text-sm"
                                  @if(auth()->user()->role !== 'admin') readonly @endif>{{ old('address', $store->address) }}</textarea>
                    </div>

                    {{-- CITY --}}
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Kota
                        </label>
                        <input type="text"
                               name="city"
                               value="{{ old('city', $store->city) }}"
                               class="w-full border-gray-300 rounded-md shadow-sm text-sm"
                               @if(auth()->user()->role !== 'admin') readonly @endif>
                    </div>

                    {{-- VISIT INTERVAL --}}
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Interval Kunjungan (hari)
                        </label>
                        <input type="number"
                               name="visit_interval_days"
                               value="{{ old('visit_interval_days', $store->visit_interval_days) }}"
                               class="w-full border-gray-300 rounded-md shadow-sm text-sm"
                               @if(auth()->user()->role !== 'admin') readonly @endif>
                    </div>

                    {{-- STATUS --}}
                    @if(auth()->user()->role === 'admin')
                        <div class="mb-6">
                            <label class="inline-flex items-center">
                                <input type="checkbox"
                                       name="is_active"
                                       value="1"
                                       class="rounded border-gray-300"
                                       {{ old('is_active', $store->is_active) ? 'checked' : '' }}>
                                <span class="ml-2 text-sm text-gray-700">Aktif</span>
                            </label>
                        </div>
                    @endif

                    <div class="flex justify-end gap-3">
                        <a href="{{ route('stores.index') }}"
                           class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white text-sm rounded-md">
                            Batal
                        </a>

                        <button type="submit"
                                class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded-md">
                            Simpan Perubahan
                        </button>
                    </div>

                </form>

            </div>

        </div>
    </div>
</x-app-layout>