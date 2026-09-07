<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Target core tables to receive UUID column.
     */
    protected array $tables = [
        'umat',
        'kk_katolik',
        'konten',
        'galeri',
        'kapela',
        'master_pastor',
        'roles',
        'users',
        'kub',
        'wilayah',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach ($this->tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                if (!Schema::hasColumn($tableName, 'uuid')) {
                    Schema::table($tableName, function (Blueprint $table) {
                        $table->string('uuid', 36)->nullable()->unique()->after('id');
                    });
                }

                // Populate UUID for all existing records that do not have one
                $records = DB::table($tableName)->whereNull('uuid')->orWhere('uuid', '')->get(['id']);
                foreach ($records as $record) {
                    DB::table($tableName)
                        ->where('id', $record->id)
                        ->update(['uuid' => (string) Str::uuid()]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach ($this->tables as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'uuid')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropColumn('uuid');
                });
            }
        }
    }
};
