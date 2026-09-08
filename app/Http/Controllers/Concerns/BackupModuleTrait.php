<?php

namespace App\Http\Controllers\Concerns;

use App\Models\DesaKelurahan;
use App\Models\Dekenat;
use App\Models\Kabupaten;
use App\Models\Kapela;
use App\Models\Kecamatan;
use App\Models\Keuskupan;
use App\Models\Kevikepan;
use App\Models\KkKatolik;
use App\Models\Kub;
use App\Models\Lingkungan;
use App\Models\Paroki;
use App\Models\Provinsi;
use App\Models\Sakramen;
use App\Models\Umat;
use App\Models\Wilayah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

trait BackupModuleTrait
{
    public function backupDatabase(Request $request): Response
    {
        $this->ensureBackupDatabaseTable();
        $firstSegment = explode('/', trim($request->path(), '/'))[0] ?? 'superadmin';
        $roleMap = [
            'superadmin' => 'Super Admin',
            'paroki' => 'Admin Paroki',
            'pastor' => 'Pastor',
            'wilayah' => 'Admin Wilayah',
            'kapela' => 'Admin Kapela / Stasi',
            'kub' => 'Admin KUB',
            'bendahara' => 'Bendahara',
            'penulis' => 'Penulis',
            'umat' => 'Umat',
        ];
        $resolvedRole = $roleMap[$firstSegment] ?? auth()->user()?->role?->nama_role ?? 'Super Admin';

        $this->ensureBackupDatabaseTable();

        // Scan actual storage/app/backups folder to sync any existing .sql and .zip files
        $backupDir = storage_path('app/backups');
        if (!file_exists($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $files = array_merge(
            glob($backupDir . '/*.sql') ?: [],
            glob($backupDir . '/*.zip') ?: []
        );
        foreach ($files as $filePath) {
            $fileName = basename($filePath);
            $size = filesize($filePath);
            $time = filemtime($filePath);

            $exists = DB::table('backup_database')->where('nama_file', $fileName)->exists();
            if (!$exists) {
                DB::table('backup_database')->insert([
                    'nama_file' => $fileName,
                    'ukuran' => $size,
                    'dibuat_oleh' => 'Sistem / File',
                    'created_at' => date('Y-m-d H:i:s', $time),
                    'updated_at' => date('Y-m-d H:i:s', $time),
                ]);
            }
        }

        $backups = DB::table('backup_database')
            ->orderBy('id', 'desc')
            ->get()
            ->map(function ($b) {
                $b->hashid = encode_id($b->id);
                $b->iid = $b->hashid;
                $b->is_media = str_ends_with(strtolower($b->nama_file), '.zip');
                $b->tipe_label = $b->is_media ? 'Media & Uploads' : 'Database SQL';
                return $b;
            });

        // Calculate database metrics
        $databaseName = config('database.connections.mysql.database');
        $dbStatus = null;
        try {
            $dbStatus = DB::select("SELECT 
                COUNT(table_name) AS table_count, 
                ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS db_size_mb,
                SUM(table_rows) AS total_rows
                FROM information_schema.tables 
                WHERE table_schema = ?", [$databaseName])[0] ?? null;
        } catch (\Throwable $e) {}

        // Calculate public/uploads media metrics
        $uploadsDir = public_path('uploads');
        $mediaSizeMb = 0;
        $mediaFileCount = 0;
        if (is_dir($uploadsDir)) {
            try {
                $it = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($uploadsDir, \RecursiveDirectoryIterator::SKIP_DOTS),
                    \RecursiveIteratorIterator::LEAVES_ONLY
                );
                $totalBytes = 0;
                foreach ($it as $file) {
                    if ($file->isFile()) {
                        $totalBytes += $file->getSize();
                        $mediaFileCount++;
                    }
                }
                $mediaSizeMb = round($totalBytes / 1024 / 1024, 2);
            } catch (\Throwable $e) {}
        }

        return Inertia::render('Inertia/BackupDatabase', [
            'role' => $resolvedRole,
            'prefix' => $firstSegment,
            'backups' => $backups,
            'databaseName' => $databaseName,
            'tableCount' => (int) ($dbStatus->table_count ?? 0),
            'dbSizeMb' => (float) ($dbStatus->db_size_mb ?? 0),
            'totalRows' => (int) ($dbStatus->total_rows ?? 0),
            'mediaSizeMb' => (float) $mediaSizeMb,
            'mediaFileCount' => (int) $mediaFileCount,
            'lastBackupAt' => $backups->first()?->created_at ?? null,
        ]);
    }


    private function buildDatabaseSqlDump(): string
    {
        $databaseName = config('database.connections.mysql.database');
        $tables = DB::select('SHOW TABLES');
        $keyName = "Tables_in_{$databaseName}";

        $sqlContent = "-- SIPAROKI Database Backup\n";
        $sqlContent .= "-- Generated: " . now()->toDateTimeString() . "\n";
        $sqlContent .= "-- Database: {$databaseName}\n\n";
        $sqlContent .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        foreach ($tables as $tableObj) {
            $tableName = $tableObj->{$keyName} ?? array_values((array)$tableObj)[0];

            $createTable = DB::select("SHOW CREATE TABLE `{$tableName}`");
            $createSql = $createTable[0]->{'Create Table'} ?? null;
            if ($createSql) {
                $sqlContent .= "DROP TABLE IF EXISTS `{$tableName}`;\n";
                $sqlContent .= $createSql . ";\n\n";
            }

            $rows = DB::table($tableName)->get();
            if ($rows->count() > 0) {
                foreach ($rows as $row) {
                    $rowArr = (array) $row;
                    $cols = array_keys($rowArr);
                    $escapedCols = array_map(fn($c) => "`{$c}`", $cols);
                    $escapedValues = array_map(function ($val) {
                        if (is_null($val)) return 'NULL';
                        return "'" . addslashes((string) $val) . "'";
                    }, array_values($rowArr));

                    $sqlContent .= "INSERT INTO `{$tableName}` (" . implode(', ', $escapedCols) . ") VALUES (" . implode(', ', $escapedValues) . ");\n";
                }
                $sqlContent .= "\n";
            }
        }

        $sqlContent .= "SET FOREIGN_KEY_CHECKS=1;\n";
        return $sqlContent;
    }

    public function downloadDirectDatabaseBackup(Request $request)
    {
        try {
            $type = strtolower($request->query('type', 'sql'));
            $dateSuffix = now()->format('Y-m-d_H-i-s');
            $backupDir = storage_path('app/backups');
            if (!file_exists($backupDir)) {
                mkdir($backupDir, 0755, true);
            }

            if ($type === 'media' || $type === 'zip') {
                $uploadsDir = public_path('uploads');
                if (!is_dir($uploadsDir)) {
                    return back()->with('error', 'Direktori uploads media tidak ditemukan di server.');
                }

                $fileName = "backup_media_siparoki_{$dateSuffix}.zip";
                $filePath = $backupDir . '/' . $fileName;

                $success = $this->createZipArchive($uploadsDir, $filePath);
                if (!$success || !file_exists($filePath)) {
                    return back()->with('error', 'Gagal membuat file arsip zip media.');
                }

                return response()->download($filePath, $fileName, [
                    'Content-Type' => 'application/zip',
                ])->deleteFileAfterSend(true);
            }

            $sqlContent = $this->buildDatabaseSqlDump();
            $fileName = "backup_siparoki_{$dateSuffix}.sql";
            $filePath = $backupDir . '/' . $fileName;
            file_put_contents($filePath, $sqlContent);

            return response()->download($filePath, $fileName, [
                'Content-Type' => 'application/sql',
            ])->deleteFileAfterSend(true);
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal mengunduh cadangan langsung: ' . $e->getMessage());
        }
    }

    public function clearServerBackups(Request $request)
    {
        try {
            $backupDir = storage_path('app/backups');
            if (is_dir($backupDir)) {
                $files = glob($backupDir . '/*');
                foreach ($files as $file) {
                    if (is_file($file)) {
                        @unlink($file);
                    }
                }
            }
            if (Schema::hasTable('backup_database')) {
                DB::table('backup_database')->truncate();
            }
            return back()->with('success', 'Semua riwayat dan file cadangan yang tersimpan di server berhasil dibersihkan.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal membersihkan cadangan server: ' . $e->getMessage());
        }
    }

    public function generateDatabaseBackup(Request $request)
    {
        try {
            $sqlContent = $this->buildDatabaseSqlDump();
            $dateSuffix = now()->format('Y-m-d_H-i-s');
            $fileName = "backup_siparoki_{$dateSuffix}.sql";
            $backupDir = storage_path('app/backups');
            if (!file_exists($backupDir)) {
                mkdir($backupDir, 0755, true);
            }

            $filePath = $backupDir . '/' . $fileName;
            file_put_contents($filePath, $sqlContent);
            $fileSize = filesize($filePath);

            $this->ensureBackupDatabaseTable();

            DB::table('backup_database')->insert([
                'nama_file' => $fileName,
                'ukuran' => $fileSize,
                'dibuat_oleh' => auth()->user()?->nama_lengkap ?? auth()->user()?->username ?? 'Administrator',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return back()->with('success', "Database berhasil di-backup dan disimpan di server! File: {$fileName} (" . round($fileSize / 1024, 2) . " KB)");
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal mem-backup database: ' . $e->getMessage());
        }
    }


    public function generateMediaBackup(Request $request)
    {
        try {
            $uploadsDir = public_path('uploads');
            if (!is_dir($uploadsDir)) {
                return back()->with('error', 'Direktori uploads tidak ditemukan di server.');
            }

            $dateSuffix = now()->format('Y-m-d_H-i-s');
            $fileName = "backup_media_siparoki_{$dateSuffix}.zip";
            $backupDir = storage_path('app/backups');
            if (!file_exists($backupDir)) {
                mkdir($backupDir, 0755, true);
            }

            $filePath = $backupDir . '/' . $fileName;
            $success = $this->createZipArchive($uploadsDir, $filePath);

            if (!$success || !file_exists($filePath)) {
                return back()->with('error', 'Gagal membuat file arsip zip media.');
            }

            $fileSize = filesize($filePath);
            $this->ensureBackupDatabaseTable();

            DB::table('backup_database')->insert([
                'nama_file' => $fileName,
                'ukuran' => $fileSize,
                'dibuat_oleh' => auth()->user()?->nama_lengkap ?? auth()->user()?->username ?? 'Administrator',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return back()->with('success', "Seluruh file media (gambar, logo, foto pastor, banner, dll.) berhasil di-backup! File: {$fileName} (" . round($fileSize / 1024 / 1024, 2) . " MB)");
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal mem-backup file media: ' . $e->getMessage());
        }
    }


    public function downloadDatabaseBackup($id)
    {
        $decodedId = decode_id($id) ?: $id;
        $backup = DB::table('backup_database')->where('id', $decodedId)->first();
        if (!$backup) {
            abort(404, 'File backup tidak ditemukan.');
        }

        $filePath = storage_path('app/backups/' . $backup->nama_file);
        if (!file_exists($filePath)) {
            abort(404, 'File fisik backup tidak ditemukan di server.');
        }

        $mime = str_ends_with(strtolower($backup->nama_file), '.zip') ? 'application/zip' : 'application/sql';

        return response()->download($filePath, $backup->nama_file, [
            'Content-Type' => $mime,
        ]);
    }


    public function restoreDatabaseBackup(Request $request, $id)
    {
        try {
            $decodedId = decode_id($id) ?: $id;
            $backup = DB::table('backup_database')->where('id', $decodedId)->first();
            if (!$backup) {
                return back()->with('error', 'Data backup tidak ditemukan.');
            }

            $filePath = storage_path('app/backups/' . $backup->nama_file);
            if (!file_exists($filePath)) {
                return back()->with('error', "File fisik backup {$backup->nama_file} tidak ditemukan di server.");
            }

            if (str_ends_with(strtolower($backup->nama_file), '.zip')) {
                $extracted = $this->extractZipArchive($filePath, public_path());
                if (!$extracted) {
                    return back()->with('error', 'Gagal mengekstrak dan memulihkan file media dari arsip zip.');
                }
                $this->clearFastAccessCache();
                return back()->with('success', "Seluruh file media, foto, logo & banner berhasil dipulihkan dari {$backup->nama_file}!");
            } else {
                $sqlContent = file_get_contents($filePath);
                $this->executeSqlDump($sqlContent);
                $this->clearFastAccessCache();
                return back()->with('success', "Database berhasil dipulihkan (restore) dari file {$backup->nama_file}!");
            }
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal memulihkan backup: ' . $e->getMessage());
        }
    }


    public function uploadRestoreDatabaseBackup(Request $request)
    {
        $request->validate([
            'file_backup' => ['nullable', 'file', 'max:102400'], // max 100MB
            'file_sql'    => ['nullable', 'file', 'max:102400'],
        ]);

        $file = $request->file('file_backup') ?? $request->file('file_sql');
        if (!$file) {
            return back()->with('error', 'Silakan pilih file backup (.sql atau .zip).');
        }

        try {
            $originalName = $file->getClientOriginalName();
            $ext = strtolower($file->getClientOriginalExtension());

            if ($ext === 'zip') {
                $tempPath = $file->getRealPath();
                $extracted = $this->extractZipArchive($tempPath, public_path());
                if (!$extracted) {
                    return back()->with('error', 'Gagal mengekstrak dan memulihkan file media dari arsip zip.');
                }
                $this->clearFastAccessCache();
                return back()->with('success', "Seluruh file media, foto & logo berhasil dipulihkan dari file upload: {$originalName}!");
            } else {
                $sqlContent = file_get_contents($file->getRealPath());
                $this->executeSqlDump($sqlContent);
                $this->clearFastAccessCache();
                return back()->with('success', "Database berhasil dipulihkan (restore) dari file upload: {$originalName}");
            }
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal memulihkan database dari file: ' . $e->getMessage());
        }
    }


    protected function createZipArchive(string $sourceDir, string $destinationZip): bool
    {
        if (class_exists(\ZipArchive::class)) {
            $zip = new \ZipArchive();
            if ($zip->open($destinationZip, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
                $files = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($sourceDir, \RecursiveDirectoryIterator::SKIP_DOTS),
                    \RecursiveIteratorIterator::LEAVES_ONLY
                );
                foreach ($files as $file) {
                    if (!$file->isDir()) {
                        $filePath = $file->getRealPath();
                        $relativePath = 'uploads/' . ltrim(str_replace('\\', '/', substr($filePath, strlen($sourceDir))), '/');
                        $zip->addFile($filePath, $relativePath);
                    }
                }
                $zip->close();
                if (file_exists($destinationZip) && filesize($destinationZip) > 0) {
                    return true;
                }
            }
        }

        // Fallback using system tar
        $parent = dirname($sourceDir);
        $folderName = basename($sourceDir);
        $cmd = sprintf('tar -a -c -f %s -C %s %s', escapeshellarg($destinationZip), escapeshellarg($parent), escapeshellarg($folderName));
        @exec($cmd, $out, $ret);
        if ($ret === 0 && file_exists($destinationZip) && filesize($destinationZip) > 0) {
            return true;
        }

        // Fallback using PowerShell on Windows
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $psCmd = sprintf('powershell -NoProfile -Command "Compress-Archive -Path \'%s\' -DestinationPath \'%s\' -Force"', addslashes($sourceDir), addslashes($destinationZip));
            @exec($psCmd, $out, $ret);
            return file_exists($destinationZip) && filesize($destinationZip) > 0;
        }

        return false;
    }


    protected function extractZipArchive(string $zipFilePath, string $targetDir): bool
    {
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        if (class_exists(\ZipArchive::class)) {
            $zip = new \ZipArchive();
            if ($zip->open($zipFilePath) === true) {
                $zip->extractTo($targetDir);
                $zip->close();
                return true;
            }
        }

        // Fallback using system tar
        $cmd = sprintf('tar -x -f %s -C %s', escapeshellarg($zipFilePath), escapeshellarg($targetDir));
        @exec($cmd, $out, $ret);
        if ($ret === 0) {
            return true;
        }

        // Fallback using PowerShell on Windows
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $psCmd = sprintf('powershell -NoProfile -Command "Expand-Archive -Path \'%s\' -DestinationPath \'%s\' -Force"', addslashes($zipFilePath), addslashes($targetDir));
            @exec($psCmd, $out, $ret);
            return $ret === 0;
        }

        return false;
    }


    public function deleteDatabaseBackup($id)
    {
        $decodedId = decode_id($id) ?: $id;
        $backup = DB::table('backup_database')->where('id', $decodedId)->first();
        if ($backup) {
            $filePath = storage_path('app/backups/' . $backup->nama_file);
            if (file_exists($filePath)) {
                @unlink($filePath);
            }
            DB::table('backup_database')->where('id', $decodedId)->delete();
        }

        return back()->with('success', 'File backup berhasil dihapus.');
    }


    protected function executeSqlDump(string $sql): void
    {
        DB::unprepared("SET FOREIGN_KEY_CHECKS=0;");
        DB::unprepared($sql);
        DB::unprepared("SET FOREIGN_KEY_CHECKS=1;");
    }


    protected function ensureBackupDatabaseTable(): void
    {
        try {
            if (!Schema::hasTable('backup_database')) {
                Schema::create('backup_database', function ($table) {
                    $table->increments('id');
                    $table->string('nama_file', 255);
                    $table->bigInteger('ukuran')->default(0);
                    $table->string('dibuat_oleh', 191)->nullable();
                    $table->timestamps();
                });
            } else {
                // Ensure dibuat_oleh is varchar(191), not int
                try {
                    DB::statement("ALTER TABLE `backup_database` MODIFY COLUMN `dibuat_oleh` VARCHAR(191) NULL DEFAULT NULL");
                } catch (\Throwable $e) {}
            }
        } catch (\Throwable $e) {
            // Silently continue
        }
    }

}
