<x-app-layout>
<x-slot name="header">
    <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
        Buat Settlement Sales
    </h2>
</x-slot>

<div class="py-8">
<div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

    @if ($errors->any())
        <div style="background:#fee2e2;color:#991b1b;padding:12px;border-radius:8px;margin-bottom:16px;">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>- {{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('sales.settlements.store') }}">
        @csrf

        <div class="bg-white shadow rounded p-6 space-y-6">

            <div>
                <label class="block font-semibold mb-1">Sales</label>
                <select name="user_id" required
                        class="w-full border rounded p-2">
                    <option value="">-- Pilih Sales --</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}">
                            {{ $user->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block font-semibold mb-1">Tanggal Settlement</label>
                <input type="date"
                       name="settlement_date"
                       required
                       class="w-full border rounded p-2">
            </div>

            <div>
                <label class="block font-semibold mb-1">Biaya (Transport / Lainnya)</label>
                <input type="number"
                       name="total_cost"
                       step="0.01"
                       value="0"
                       class="w-full border rounded p-2">
            </div>

            <div>
                <label class="block font-semibold mb-1">Setoran Aktual (Uang Fisik Diterima)</label>
                <input type="number"
                       name="actual_amount"
                       step="0.01"
                       required
                       class="w-full border rounded p-2">
            </div>

            <div>
                <button type="submit"
                        style="background:#16a34a;color:white;padding:10px 20px;border-radius:8px;font-weight:600;">
                    Simpan Settlement
                </button>

                <a href="{{ route('sales.settlements.index') }}"
                   style="margin-left:12px;text-decoration:none;color:#6b7280;">
                    Batal
                </a>
            </div>

        </div>

    </form>

</div>
</div>
</x-app-layout>
