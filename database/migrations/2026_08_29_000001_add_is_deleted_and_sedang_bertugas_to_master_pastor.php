<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('master_pastor')) {
            Schema::table('master_pastor', function (Blueprint $table) {
                if (!Schema::hasColumn('master_pastor', 'is_deleted')) {
                    $table->boolean('is_deleted')->default(false)->after('status')->index();
                }
                if (!Schema::hasColumn('master_pastor', 'sedang_bertugas')) {
                    $table->boolean('sedang_bertugas')->default(true)->after('status');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('master_pastor')) {
            Schema::table('master_pastor', function (Blueprint $table) {
                if (Schema::hasColumn('master_pastor', 'is_deleted')) {
                    $table->dropColumn('is_deleted');
                }
                if (Schema::hasColumn('master_pastor', 'sedang_bertugas')) {
                    $table->dropColumn('sedang_bertugas');
                }
            });
        }
    }
};
