<x-app-layout>
<x-slot name="header">
    <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
        System Backup
    </h2>
</x-slot>

<div class="py-8">
<div class="max-w-6xl mx-auto sm:px-6 lg:px-8">

    <div style="background:white;padding:30px;border-radius:16px;box-shadow:0 10px 20px rgba(0,0,0,0.08);">

        {{-- Success Message --}}
        @if(session('success'))
            <div style="margin-bottom:20px;padding:15px;background:#dcfce7;border-radius:10px;color:#166534;">
                {{ session('success') }}
            </div>
        @endif

        {{-- Error Message --}}
        @if(session('error'))
            <div style="margin-bottom:20px;padding:15px;background:#fee2e2;border-radius:10px;color:#b91c1c;">
                {{ session('error') }}
            </div>
        @endif

        {{-- Backup Button --}}
        <form method="POST" action="{{ route('system.backups.store') }}" style="margin-bottom:30px;">
            @csrf
            <button type="submit"
                style="background:#16a34a;color:white;padding:12px 20px;border-radius:10px;font-weight:600;border:none;">
                + Backup Sekarang
            </button>
        </form>

        <hr style="margin-bottom:25px;">

        <h3 style="font-size:18px;font-weight:600;margin-bottom:20px;">
            Daftar Backup Tersimpan
        </h3>

        @if($files->isEmpty())
            <div style="color:#6b7280;">
                Belum ada file backup.
            </div>
        @else
            <table style="width:100%;border-collapse:collapse;font-size:14px;">
                <thead style="background:#f3f4f6;">
                    <tr>
                        <th style="padding:12px;text-align:left;">Nama File</th>
                        <th style="padding:12px;text-align:left;">Ukuran</th>
                        <th style="padding:12px;text-align:left;">Terakhir Diubah</th>
                        <th style="padding:12px;text-align:center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($files as $file)
                        <tr style="border-top:1px solid #e5e7eb;">
                            <td style="padding:12px;">
                                {{ $file['name'] }}
                            </td>
                            <td style="padding:12px;">
                                {{ $file['size'] }}
                            </td>
                            <td style="padding:12px;">
                                {{ $file['last_modified'] }}
                            </td>
                            <td style="padding:12px;text-align:center;">

                                <a href="{{ route('system.backups.download', $file['name']) }}"
                                   style="background:#2563eb;color:white;padding:6px 12px;border-radius:8px;text-decoration:none;font-weight:600;">
                                    Download
                                </a>

                                <form method="POST"
                                      action="{{ route('system.backups.restore', $file['name']) }}"
                                      style="display:inline;">
                                    @csrf
                                    <button type="submit"
                                        onclick="return confirm('PERINGATAN: Restore akan mengganti SELURUH database. Lanjutkan?')"
                                        style="background:#dc2626;color:white;padding:6px 12px;border-radius:8px;border:none;font-weight:600;margin-left:6px;">
                                        Restore
                                    </button>
                                </form>

                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

    </div>

</div>
</div>
</x-app-layout>