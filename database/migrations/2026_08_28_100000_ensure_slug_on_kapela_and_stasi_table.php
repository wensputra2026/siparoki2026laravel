<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Table stasi_kapela
        if (Schema::hasTable('stasi_kapela')) {
            if (!Schema::hasColumn('stasi_kapela', 'slug')) {
                Schema::table('stasi_kapela', function (Blueprint $table) {
                    $table->string('slug', 255)->nullable()->index()->after('nama_stasi_kapela');
                });
            }

            // Populate empty slugs
            try {
                $rows = DB::table('stasi_kapela')->get();
                foreach ($rows as $row) {
                    if (empty($row->slug) && !empty($row->nama_stasi_kapela)) {
                        $baseSlug = Str::slug($row->nama_stasi_kapela);
                        $slug = $baseSlug;
                        $counter = 1;
                        $pk = isset($row->id_stasi_kapela) ? 'id_stasi_kapela' : 'id';
                        $pkVal = $row->{$pk};

                        while (DB::table('stasi_kapela')->where('slug', $slug)->where($pk, '!=', $pkVal)->exists()) {
                            $slug = $baseSlug . '-' . $counter++;
                        }

                        DB::table('stasi_kapela')->where($pk, $pkVal)->update(['slug' => $slug]);
                    }
                }
            } catch (\Throwable $e) {}
        }

        // 2. Table kapela
        if (Schema::hasTable('kapela')) {
            if (!Schema::hasColumn('kapela', 'slug')) {
                Schema::table('kapela', function (Blueprint $table) {
                    $table->string('slug', 255)->nullable()->index()->after('nama_kapela');
                });
            }

            // Populate empty slugs
            try {
                $rows = DB::table('kapela')->get();
                foreach ($rows as $row) {
                    if (empty($row->slug) && !empty($row->nama_kapela)) {
                        $baseSlug = Str::slug($row->nama_kapela);
                        $slug = $baseSlug;
                        $counter = 1;

                        while (DB::table('kapela')->where('slug', $slug)->where('id', '!=', $row->id)->exists()) {
                            $slug = $baseSlug . '-' . $counter++;
                        }

                        DB::table('kapela')->where('id', $row->id)->update(['slug' => $slug]);
                    }
                }
            } catch (\Throwable $e) {}
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('stasi_kapela') && Schema::hasColumn('stasi_kapela', 'slug')) {
            Schema::table('stasi_kapela', function (Blueprint $table) {
                $table->dropColumn('slug');
            });
        }
        if (Schema::hasTable('kapela') && Schema::hasColumn('kapela', 'slug')) {
            Schema::table('kapela', function (Blueprint $table) {
                $table->dropColumn('slug');
            });
        }
    }
};
