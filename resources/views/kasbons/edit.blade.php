<x-app-layout>
<x-slot name="header">
    <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
        Edit Kasbon
    </h2>
</x-slot>

<div class="py-8">
<div class="max-w-xl mx-auto sm:px-6 lg:px-8">

    <div style="background:white;padding:24px;border-radius:16px;box-shadow:0 10px 20px rgba(0,0,0,0.1);">

        @if($kasbon->type === 'shortage')
            <div style="background:#fee2e2;padding:12px;border-radius:8px;margin-bottom:16px;color:#7f1d1d;">
                Kasbon ini berasal dari shortage settlement dan tidak dapat diubah.
            </div>
        @endif

        <form method="POST" action="{{ route('kasbons.update',$kasbon->id) }}">
            @csrf
            @method('PUT')

            <div style="margin-bottom:16px;">
                <label style="display:block;margin-bottom:6px;font-weight:600;">Sales</label>
                <select name="user_id" style="width:100%;padding:10px;border:1px solid #d1d5db;border-radius:8px;"
                    {{ $kasbon->type === 'shortage' ? 'disabled' : '' }}>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}"
                            {{ $kasbon->user_id == $user->id ? 'selected' : '' }}>
                            {{ $user->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div style="margin-bottom:16px;">
                <label style="display:block;margin-bottom:6px;font-weight:600;">Nominal</label>
                <input type="number" name="amount"
                       value="{{ $kasbon->amount_total }}"
                       style="width:100%;padding:10px;border:1px solid #d1d5db;border-radius:8px;"
                       {{ $kasbon->type === 'shortage' ? 'disabled' : '' }}>
            </div>

            <div style="margin-bottom:20px;">
                <label style="display:block;margin-bottom:6px;font-weight:600;">Keterangan</label>
                <textarea name="description"
                          style="width:100%;padding:10px;border:1px solid #d1d5db;border-radius:8px;"
                          {{ $kasbon->type === 'shortage' ? 'disabled' : '' }}>{{ $kasbon->description }}</textarea>
            </div>

            @if($kasbon->type !== 'shortage')
                <button type="submit"
                    style="background:#2563eb;color:white;padding:10px 20px;border-radius:8px;font-weight:600;">
                    Simpan Perubahan
                </button>
            @endif

            <a href="{{ route('kasbons.index') }}"
               style="margin-left:10px;color:#6b7280;text-decoration:none;">
                Batal
            </a>

        </form>

    </div>

</div>
</div>
</x-app-layout>
