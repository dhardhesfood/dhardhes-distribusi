<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DatabaseBackup extends Command
{
    protected $signature = 'db:backup';

    protected $description = 'Create full MySQL database backup';

    public function handle()
    {
        $dbHost = config('database.connections.mysql.host');
        $dbName = config('database.connections.mysql.database');
        $dbUser = config('database.connections.mysql.username');
        $dbPass = config('database.connections.mysql.password');

        $backupPath = '/var/backups/dhardhes';

        if (!is_dir($backupPath)) {
            $this->error('Backup directory not found.');
            return 1;
        }

        $timestamp = now()->format('Y-m-d_H-i-s');
        $fileName = "dhardhes_{$timestamp}.sql";
        $fullPath = "{$backupPath}/{$fileName}";

        $this->info("Starting backup...");

        /*
         |--------------------------------------------------------------------------
         | FIXED VERSION
         |--------------------------------------------------------------------------
         | 1. Use MYSQL_PWD to avoid CLI password warning
         | 2. Add --no-tablespaces (required for MySQL 8 non-root user)
         | 3. Do NOT use 2>&1 to avoid corrupting SQL file
         |--------------------------------------------------------------------------
        */

        $command = sprintf(
            'MYSQL_PWD=%s mysqldump --no-tablespaces -h %s -u %s %s > %s',
            escapeshellarg($dbPass),
            escapeshellarg($dbHost),
            escapeshellarg($dbUser),
            escapeshellarg($dbName),
            escapeshellarg($fullPath)
        );

        exec($command, $output, $resultCode);

        if ($resultCode !== 0) {
            $this->error('Backup failed.');
            return 1;
        }

        $this->info("Backup completed successfully.");
        $this->info("File saved to: {$fullPath}");

        // Auto cleanup: keep last 7 backups
        $files = glob($backupPath . '/dhardhes_*.sql');
        usort($files, function ($a, $b) {
            return filemtime($b) - filemtime($a);
        });

        if (count($files) > 7) {
            $filesToDelete = array_slice($files, 7);
            foreach ($filesToDelete as $file) {
                unlink($file);
            }
            $this->info('Old backups cleaned (kept last 7).');
        }

        return 0;
    }
}