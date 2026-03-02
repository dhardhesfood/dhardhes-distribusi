<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SystemBackupController extends Controller
{
    protected string $backupPath = '/var/backups/dhardhes';

    public function index()
    {
        if (!is_dir($this->backupPath)) {
            abort(500, 'Backup directory not found.');
        }

        $files = collect(File::files($this->backupPath))
            ->filter(fn ($file) => $file->getExtension() === 'sql')
            ->sortByDesc(fn ($file) => $file->getMTime())
            ->map(function ($file) {
                return [
                    'name' => $file->getFilename(),
                    'size' => $this->formatSize($file->getSize()),
                    'last_modified' => date('Y-m-d H:i:s', $file->getMTime()),
                ];
            })
            ->values();

        return view('system.backups.index', compact('files'));
    }

    public function store()
    {
        Artisan::call('db:backup');

        return redirect()
            ->route('system.backups.index')
            ->with('success', 'Backup berhasil dibuat.');
    }

    public function download(string $filename): BinaryFileResponse
    {
        $filePath = $this->backupPath . '/' . basename($filename);

        if (!file_exists($filePath)) {
            abort(404, 'File tidak ditemukan.');
        }

        return response()->download($filePath);
    }

    private function formatSize($bytes): string
    {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }

        return $bytes . ' B';
    }
    public function restore(string $filename)
{
    $filePath = $this->backupPath . '/' . basename($filename);

    if (!file_exists($filePath)) {
        return redirect()
            ->route('system.backups.index')
            ->with('error', 'File backup tidak ditemukan.');
    }

    // Masuk maintenance mode
    Artisan::call('down');

    $dbHost = config('database.connections.mysql.host');
    $dbName = config('database.connections.mysql.database');
    $dbUser = config('database.connections.mysql.username');
    $dbPass = config('database.connections.mysql.password');

    $command = sprintf(
        'mysql -h %s -u %s -p%s %s < %s 2>&1',
        escapeshellarg($dbHost),
        escapeshellarg($dbUser),
        escapeshellarg($dbPass),
        escapeshellarg($dbName),
        escapeshellarg($filePath)
    );

    exec($command, $output, $resultCode);

    // Keluar maintenance mode
    Artisan::call('up');

    if ($resultCode !== 0) {
        return redirect()
            ->route('system.backups.index')
            ->with('error', 'Restore gagal. Periksa log server.');
    }

    return redirect()
        ->route('system.backups.index')
        ->with('success', 'Database berhasil direstore.');
}
}