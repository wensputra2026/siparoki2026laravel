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

trait GaleriModuleTrait
{
    public function createGaleri(Request $request): Response
    {
        $this->ensureGaleriColumns();
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

        return Inertia::render('Inertia/GaleriForm', [
            'role' => $resolvedRole,
            'prefix' => $firstSegment,
            'isEdit' => false,
            'item' => null,
        ]);
    }


    public function editGaleri(Request $request, $id): Response
    {
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

        $item = \App\Models\Galeri::query()
            ->where('id', $id)
            ->orWhere('slug', $id)
            ->firstOrFail();

        return Inertia::render('Inertia/GaleriForm', [
            'role' => $resolvedRole,
            'prefix' => $firstSegment,
            'isEdit' => true,
            'item' => $item,
        ]);
    }


    protected function ensureGaleriColumns(): void
    {
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('galeri')) {
                \Illuminate\Support\Facades\Schema::table('galeri', function ($table) {
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('galeri', 'lokasi')) {
                        $table->string('lokasi', 255)->nullable();
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('galeri', 'og_image')) {
                        $table->string('og_image', 255)->nullable();
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('galeri', 'status_publish')) {
                        $table->string('status_publish', 30)->default('Publish');
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('galeri', 'urutan')) {
                        $table->integer('urutan')->default(0);
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('galeri', 'meta_title')) {
                        $table->string('meta_title', 255)->nullable();
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('galeri', 'meta_description')) {
                        $table->text('meta_description')->nullable();
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('galeri', 'meta_keywords')) {
                        $table->string('meta_keywords', 255)->nullable();
                    }
                });
            }
        } catch (\Throwable $e) {
            // Silently continue
        }
    }

}
