<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('paroki') && !Schema::hasColumn('paroki', 'banner')) {
            Schema::table('paroki', function (Blueprint $table) {
                $table->string('banner', 255)->nullable()->after('logo');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('paroki') && Schema::hasColumn('paroki', 'banner')) {
            Schema::table('paroki', function (Blueprint $table) {
                $table->dropColumn('banner');
            });
        }
    }
};
