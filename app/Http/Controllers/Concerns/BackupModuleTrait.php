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


    protected function dumpDatabaseToFile(string $filePath): void
    {
        @ini_set('memory_limit', '512M');
        @set_time_limit(600);

        $fp = fopen($filePath, 'wb');
        if (!$fp) {
            throw new \RuntimeException("Gagal membuka file backup tujuan: {$filePath}");
        }

        $databaseName = config('database.connections.mysql.database');

        fwrite($fp, "-- ========================================================\n");
        fwrite($fp, "-- SIPAROKI Database Backup (High Performance Stream Dump)\n");
        fwrite($fp, "-- Generated: " . now()->toDateTimeString() . "\n");
        fwrite($fp, "-- Database: {$databaseName}\n");
        fwrite($fp, "-- ========================================================\n\n");
        fwrite($fp, "/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;\n");
        fwrite($fp, "/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;\n");
        fwrite($fp, "/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;\n");
        fwrite($fp, "/*!50503 SET NAMES utf8mb4 */;\n");
        fwrite($fp, "/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;\n");
        fwrite($fp, "/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;\n\n");

        $tables = DB::select("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'");

        foreach ($tables as $tableObj) {
            $tableArr = array_values((array) $tableObj);
            $tableName = $tableArr[0];

            $createTable = DB::select("SHOW CREATE TABLE `{$tableName}`");
            $createSql = $createTable[0]->{'Create Table'} ?? null;
            if ($createSql) {
                fwrite($fp, "--\n-- Table structure for table `{$tableName}`\n--\n\n");
                fwrite($fp, "DROP TABLE IF EXISTS `{$tableName}`;\n");
                fwrite($fp, $createSql . ";\n\n");
            }

            // Write row data using cursor (streaming unbuffered queries without memory overhead)
            fwrite($fp, "--\n-- Dumping data for table `{$tableName}`\n--\n\n");

            $batchSize = 100;
            $batchValues = [];
            $columns = null;
            $escapedCols = '';

            foreach (DB::table($tableName)->cursor() as $row) {
                $rowArr = (array) $row;
                if ($columns === null) {
                    $columns = array_keys($rowArr);
                    $escapedCols = implode(', ', array_map(fn($c) => "`{$c}`", $columns));
                }

                $rowVals = [];
                foreach ($columns as $col) {
                    $val = $rowArr[$col] ?? null;
                    if (is_null($val)) {
                        $rowVals[] = 'NULL';
                    } elseif (is_int($val) || is_float($val)) {
                        $rowVals[] = (string) $val;
                    } elseif (is_bool($val)) {
                        $rowVals[] = $val ? '1' : '0';
                    } else {
                        $escaped = str_replace(
                            ["\\", "\0", "\n", "\r", "'", "\x1a"],
                            ["\\\\", "\\0", "\\n", "\\r", "\\'", "\\Z"],
                            (string) $val
                        );
                        $rowVals[] = "'{$escaped}'";
                    }
                }

                $batchValues[] = "(" . implode(', ', $rowVals) . ")";

                if (count($batchValues) >= $batchSize) {
                    fwrite($fp, "INSERT INTO `{$tableName}` ({$escapedCols}) VALUES\n" . implode(",\n", $batchValues) . ";\n");
                    $batchValues = [];
                }
            }

            if (!empty($batchValues) && $columns !== null) {
                fwrite($fp, "INSERT INTO `{$tableName}` ({$escapedCols}) VALUES\n" . implode(",\n", $batchValues) . ";\n");
            }

            fwrite($fp, "\n");
        }

        fwrite($fp, "/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;\n");
        fwrite($fp, "/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;\n");
        fwrite($fp, "/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;\n");
        fwrite($fp, "/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;\n");

        fclose($fp);
    }

    private function buildDatabaseSqlDump(): string
    {
        @ini_set('memory_limit', '512M');
        @set_time_limit(600);

        $tempPath = storage_path('app/backups/temp_' . Str::random(12) . '.sql');
        $this->dumpDatabaseToFile($tempPath);
        $content = file_exists($tempPath) ? file_get_contents($tempPath) : '';
        if (file_exists($tempPath)) {
            @unlink($tempPath);
        }
        return $content;
    }

    public function downloadDirectDatabaseBackup(Request $request)
    {
        try {
            @ini_set('memory_limit', '512M');
            @set_time_limit(600);

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
                    'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
                ])->deleteFileAfterSend(true);
            }

            $fileName = "backup_siparoki_{$dateSuffix}.sql";
            $filePath = $backupDir . '/' . $fileName;

            $this->dumpDatabaseToFile($filePath);

            if (!file_exists($filePath) || filesize($filePath) === 0) {
                return back()->with('error', 'Gagal membuat file cadangan database.');
            }

            return response()->download($filePath, $fileName, [
                'Content-Type' => 'application/sql',
                'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
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
            @ini_set('memory_limit', '512M');
            @set_time_limit(600);

            $dateSuffix = now()->format('Y-m-d_H-i-s');
            $fileName = "backup_siparoki_{$dateSuffix}.sql";
            $backupDir = storage_path('app/backups');
            if (!file_exists($backupDir)) {
                mkdir($backupDir, 0755, true);
            }

            $filePath = $backupDir . '/' . $fileName;
            $this->dumpDatabaseToFile($filePath);

            if (!file_exists($filePath) || filesize($filePath) === 0) {
                return back()->with('error', 'Gagal membuat file cadangan database.');
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
                $this->executeSqlDumpFromFile($filePath);
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
                $this->executeSqlDumpFromFile($file->getRealPath());
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


    protected function executeSqlDumpFromFile(string $filePath): void
    {
        @ini_set('memory_limit', '512M');
        @set_time_limit(600);

        if (!file_exists($filePath) || !is_readable($filePath)) {
            throw new \RuntimeException("File backup SQL tidak ditemukan atau tidak dapat dibaca: {$filePath}");
        }

        DB::statement("SET FOREIGN_KEY_CHECKS=0;");

        $handle = fopen($filePath, 'r');
        if (!$handle) {
            throw new \RuntimeException("Gagal membuka file SQL untuk restore: {$filePath}");
        }

        $query = '';
        while (($line = fgets($handle)) !== false) {
            $trimmed = trim($line);
            if ($trimmed === '' || str_starts_with($trimmed, '--') || str_starts_with($trimmed, '/*') || str_starts_with($trimmed, '#')) {
                continue;
            }

            $query .= $line;

            if (str_ends_with($trimmed, ';')) {
                try {
                    DB::unprepared($query);
                } catch (\Throwable $e) {
                    // Ignore non-fatal statement errors (e.g. drop non-existent table)
                }
                $query = '';
            }
        }

        if (trim($query) !== '') {
            try {
                DB::unprepared($query);
            } catch (\Throwable $e) {}
        }

        fclose($handle);

        DB::statement("SET FOREIGN_KEY_CHECKS=1;");
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
