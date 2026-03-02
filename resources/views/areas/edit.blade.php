<x-app-layout>
    <x-slot name="header">
        <h2>Edit Area</h2>
    </x-slot>

    <div style="padding:30px; max-width:600px;">

        @if ($errors->any())
            <div style="color:red; margin-bottom:20px;">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('areas.update', $area->id) }}">
            @csrf
            @method('PUT')

            <div style="margin-bottom:20px;">
                <label>Nama Area</label><br>
                <input type="text"
                       name="name"
                       value="{{ old('name', $area->name) }}"
                       style="width:100%; padding:8px; border:1px solid #ccc;">
            </div>

            <div style="margin-bottom:20px;">
                <label>Kode Area</label><br>
                <input type="text"
                       name="code"
                       value="{{ old('code', $area->code) }}"
                       style="width:100%; padding:8px; border:1px solid #ccc;">
            </div>

            <div style="margin-bottom:20px;">
                <label>Deskripsi</label><br>
                <textarea name="description"
                          style="width:100%; padding:8px; border:1px solid #ccc;">{{ old('description', $area->description) }}</textarea>
            </div>

            <div style="margin-bottom:20px;">
                <label>
                    <input type="checkbox"
                           name="is_active"
                           value="1"
                           {{ old('is_active', $area->is_active) ? 'checked' : '' }}>
                    Aktif
                </label>
            </div>

            <div style="margin-top:30px;">
                <button type="submit"
                        style="background:#000; color:#fff; padding:10px 20px; border:none; cursor:pointer;">
                    UPDATE
                </button>
            </div>

        </form>
    </div>
</x-app-layout>