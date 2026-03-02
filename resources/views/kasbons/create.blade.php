<x-app-layout>
<x-slot name="header">
    <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
        Tambah Kasbon Sales
    </h2>
</x-slot>

<div class="py-8">
<div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">

    {{-- CARD --}}
    <div style="background:white;padding:30px;border-radius:20px;box-shadow:0 10px 25px rgba(0,0,0,0.1);">

        <form method="POST" action="{{ route('kasbons.store') }}">
            @csrf

            {{-- PILIH SALES --}}
            <div style="margin-bottom:20px;">
                <label style="display:block;font-weight:600;margin-bottom:8px;">
                    Pilih Sales
                </label>

                <select name="user_id"
                        style="width:100%;padding:12px;border:1px solid #d1d5db;border-radius:10px;"
                        required>
                    <option value="">-- Pilih Sales --</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}">
                            {{ $user->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- NOMINAL --}}
            <div style="margin-bottom:20px;">
                <label style="display:block;font-weight:600;margin-bottom:8px;">
                    Nominal Kasbon
                </label>

                <input type="number"
                       name="amount"
                       min="1"
                       required
                       style="width:100%;padding:12px;border:1px solid #d1d5db;border-radius:10px;"
                       placeholder="Masukkan nominal">
            </div>

            {{-- KETERANGAN --}}
            <div style="margin-bottom:25px;">
                <label style="display:block;font-weight:600;margin-bottom:8px;">
                    Keterangan
                </label>

                <textarea name="description"
                          rows="3"
                          style="width:100%;padding:12px;border:1px solid #d1d5db;border-radius:10px;"
                          placeholder="Opsional"></textarea>
            </div>

            {{-- BUTTON --}}
            <div style="display:flex;gap:12px;">
                <button type="submit"
                        style="background:#dc2626;color:white;padding:12px 20px;border-radius:10px;font-weight:600;border:none;">
                    Simpan Kasbon
                </button>

                <a href="{{ route('kasbons.index') }}"
                   style="background:#e5e7eb;color:#111827;padding:12px 20px;border-radius:10px;font-weight:600;text-decoration:none;">
                    Batal
                </a>
            </div>

        </form>

    </div>

</div>
</div>
</x-app-layout>
