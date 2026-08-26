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
            'kub' => 'Ketua KUB',
            'bendahara' => 'Bendahara',
            'penulis' => 'Penulis',
            'umat' => 'Umat',
        ];
        $resolvedRole = $roleMap[$firstSegment] ?? auth()->user()?->role?->nama_role ?? 'Super Admin';

        // Scan actual storage/app/backups folder to sync any existing .sql files
        $backupDir = storage_path('app/backups');
        if (!file_exists($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $files = glob($backupDir . '/*.sql');
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
            ->get();

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

        return Inertia::render('Inertia/BackupDatabase', [
            'role' => $resolvedRole,
            'prefix' => $firstSegment,
            'backups' => $backups,
            'databaseName' => $databaseName,
            'tableCount' => (int) ($dbStatus->table_count ?? 0),
            'dbSizeMb' => (float) ($dbStatus->db_size_mb ?? 0),
            'totalRows' => (int) ($dbStatus->total_rows ?? 0),
            'lastBackupAt' => $backups->first()?->created_at ?? null,
        ]);
    }


    public function generateDatabaseBackup(Request $request)
    {
        try {
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
                    $sqlContent .= "INSERT INTO `{$tableName}` VALUES \n";
                    $rowStrings = [];
                    foreach ($rows as $row) {
                        $values = array_map(function ($val) {
                            if ($val === null) return 'NULL';
                            return "'" . addslashes((string)$val) . "'";
                        }, (array)$row);
                        $rowStrings[] = "(" . implode(", ", $values) . ")";
                    }
                    $sqlContent .= implode(",\n", $rowStrings) . ";\n\n";
                }
            }

            $sqlContent .= "SET FOREIGN_KEY_CHECKS=1;\n";

            $fileName = 'backup-paroki-' . date('Y-m-d_His') . '.sql';
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

            return back()->with('success', "Database berhasil di-backup! File: {$fileName} (" . round($fileSize / 1024, 2) . " KB)");
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal mem-backup database: ' . $e->getMessage());
        }
    }


    public function downloadDatabaseBackup($id)
    {
        $backup = DB::table('backup_database')->where('id', $id)->first();
        if (!$backup) {
            abort(404, 'File backup tidak ditemukan.');
        }

        $filePath = storage_path('app/backups/' . $backup->nama_file);
        if (!file_exists($filePath)) {
            abort(404, 'File fisik .sql tidak ditemukan di server.');
        }

        return response()->download($filePath, $backup->nama_file, [
            'Content-Type' => 'application/sql',
        ]);
    }


    public function restoreDatabaseBackup(Request $request, $id)
    {
        try {
            $backup = DB::table('backup_database')->where('id', $id)->first();
            if (!$backup) {
                return back()->with('error', 'Data backup tidak ditemukan.');
            }

            $filePath = storage_path('app/backups/' . $backup->nama_file);
            if (!file_exists($filePath)) {
                return back()->with('error', "File fisik backup {$backup->nama_file} tidak ditemukan di server.");
            }

            $sqlContent = file_get_contents($filePath);
            $this->executeSqlDump($sqlContent);
            $this->clearFastAccessCache();

            return back()->with('success', "Database berhasil dipulihkan (restore) dari file {$backup->nama_file}!");
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal memulihkan database: ' . $e->getMessage());
        }
    }


    public function uploadRestoreDatabaseBackup(Request $request)
    {
        $request->validate([
            'file_sql' => ['required', 'file', 'max:51200'], // max 50MB
        ]);

        try {
            $file = $request->file('file_sql');
            $sqlContent = file_get_contents($file->getRealPath());
            $this->executeSqlDump($sqlContent);
            $this->clearFastAccessCache();

            return back()->with('success', "Database berhasil dipulihkan (restore) dari file upload: " . $file->getClientOriginalName());
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal memulihkan database dari file: ' . $e->getMessage());
        }
    }


    public function deleteDatabaseBackup($id)
    {
        $backup = DB::table('backup_database')->where('id', $id)->first();
        if ($backup) {
            $filePath = storage_path('app/backups/' . $backup->nama_file);
            if (file_exists($filePath)) {
                @unlink($filePath);
            }
            DB::table('backup_database')->where('id', $id)->delete();
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
                    $table->string('dibuat_oleh', 100)->nullable();
                    $table->timestamps();
                });
            }
        } catch (\Throwable $e) {
            // Silently continue
        }
    }

}
