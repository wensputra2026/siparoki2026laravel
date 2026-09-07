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

trait KontenModuleTrait
{
    public function createKonten(Request $request): Response
    {
        return $this->renderKontenForm($request);
    }


    public function editKonten(Request $request, $id): Response
    {
        $item = \App\Models\Konten::query()
            ->whereUuidOrId($id)
            ->orWhere('slug', $id)
            ->first();

        if (!$item) {
            abort(404);
        }

        $item->hashid = encode_id($item->id);
        $item->iid = $item->hashid;

        return $this->renderKontenForm($request, $item);
    }


    public function previewKonten(Request $request, $id): Response
    {
        $item = \App\Models\Konten::query()
            ->whereUuidOrId($id)
            ->orWhere('slug', $id)
            ->first();

        if (!$item) {
            abort(404);
        }

        $item->hashid = encode_id($item->id);
        $item->iid = $item->hashid;

        $firstSegment = explode('/', trim($request->path(), '/'))[0] ?? '';
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

        $category = null;
        if ($item->kategori_id && Schema::hasTable('kategori_konten')) {
            $category = DB::table('kategori_konten')->where('id', $item->kategori_id)->first();
        }

        return Inertia::render('Inertia/KontenPreview', [
            'title' => 'Preview Konten Website',
            'role' => $roleMap[$firstSegment] ?? auth()->user()?->role?->nama_role ?? 'Super Admin',
            'prefix' => $firstSegment ?: 'superadmin',
            'item' => $item,
            'category' => $category,
        ]);
    }


    protected function renderKontenForm(Request $request, $item = null): Response
    {
        $firstSegment = explode('/', trim($request->path(), '/'))[0] ?? '';
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

        $references = $this->kontenFormReferences();

        return Inertia::render('Inertia/KontenForm', [
            'title' => $item ? 'Edit Konten Website' : 'Tambah Konten Website',
            'role' => $roleMap[$firstSegment] ?? auth()->user()?->role?->nama_role ?? 'Super Admin',
            'prefix' => $firstSegment ?: 'superadmin',
            'mode' => $item ? 'edit' : 'create',
            'item' => $item,
            'kategoriKontenList' => $references['kategoriKontenList'],
            'penulisList' => $references['penulisList'],
            'arsipPdfList' => $references['arsipPdfList'],
            'galeriImageList' => $references['galeriImageList'],
        ]);
    }


    protected function kontenFormReferences(): array
    {
        $kategoriKontenList = Schema::hasTable('kategori_konten')
            ? DB::table('kategori_konten')
                ->when(Schema::hasColumn('kategori_konten', 'is_deleted'), fn ($q) => $q->where(function ($qq) {
                    $qq->where('is_deleted', 0)->orWhereNull('is_deleted');
                }))
                ->when(Schema::hasColumn('kategori_konten', 'status'), fn ($q) => $q->where(function ($qq) {
                    $qq->where('status', 1)->orWhere('status', 'Aktif')->orWhereNull('status');
                }))
                ->orderBy('nama_kategori')
                ->get(['id', 'nama_kategori', 'slug', 'tipe'])
            : collect();

        $penulisList = collect(DB::table('users')->whereNotNull('nama_lengkap')->where('nama_lengkap', '!=', '')->pluck('nama_lengkap'))
            ->merge(DB::table('konten')->whereNotNull('penulis')->where('penulis', '!=', '')->distinct()->pluck('penulis'))
            ->filter()
            ->unique(fn ($name) => Str::lower(trim((string) $name)))
            ->sort(SORT_NATURAL | SORT_FLAG_CASE)
            ->values();

        $arsipPdfList = Schema::hasTable('arsip_digital')
            ? DB::table('arsip_digital')
                ->when(Schema::hasColumn('arsip_digital', 'is_deleted'), fn ($q) => $q->where(function ($qq) {
                    $qq->where('is_deleted', 0)->orWhereNull('is_deleted');
                }))
                ->when(Schema::hasColumn('arsip_digital', 'file_type'), fn ($q) => $q->whereRaw('LOWER(file_type) = ?', ['pdf']))
                ->orderByDesc(Schema::hasColumn('arsip_digital', 'tanggal_arsip') ? 'tanggal_arsip' : 'created_at')
                ->limit(150)
                ->get()
                ->map(function ($row) {
                    $filename = basename((string) ($row->file_path ?? ''));

                    return [
                        'id' => $row->id,
                        'title' => $row->judul ?: 'Dokumen Arsip',
                        'nomor' => $row->nomor_arsip ?? '',
                        'kategori' => $row->kategori_arsip ?? 'DOKUMEN_UMUM',
                        'tahun' => $row->tahun_arsip ?? '',
                        'filename' => $filename,
                        'url' => $filename ? '/assets/uploads/arsip/' . $filename : '',
                    ];
                })
                ->values()
            : collect();

        $galeriImageList = Schema::hasTable('galeri')
            ? DB::table('galeri')
                ->when(Schema::hasColumn('galeri', 'is_deleted'), fn ($q) => $q->where(function ($qq) {
                    $qq->where('is_deleted', 0)->orWhereNull('is_deleted');
                }))
                ->when(Schema::hasColumn('galeri', 'status'), fn ($q) => $q->where(function ($qq) {
                    $qq->where('status', 1)->orWhere('status', 'Aktif')->orWhereNull('status');
                }))
                ->when(Schema::hasColumn('galeri', 'tipe'), fn ($q) => $q->where(function ($qq) {
                    $qq->where('tipe', 'Foto')->orWhereNull('tipe')->orWhere('tipe', '');
                }))
                ->orderByDesc(Schema::hasColumn('galeri', 'created_at') ? 'created_at' : 'id')
                ->limit(120)
                ->get()
                ->map(function ($row) {
                    $image = (string) ($row->gambar ?? '');
                    $path = ltrim($image, '/');
                    $url = $path === ''
                        ? ''
                        : (Str::startsWith($path, ['http://', 'https://']) ? $path : '/' . $path);

                    return [
                        'id' => $row->id,
                        'title' => $row->judul ?: 'Foto Galeri',
                        'album' => $row->album ?? '',
                        'description' => $row->deskripsi ?? '',
                        'alt' => $row->judul ?: 'Foto Galeri',
                        'url' => $url,
                    ];
                })
                ->filter(fn ($item) => !empty($item['url']))
                ->values()
            : collect();

        return [
            'kategoriKontenList' => $kategoriKontenList,
            'penulisList' => $penulisList,
            'arsipPdfList' => $arsipPdfList,
            'galeriImageList' => $galeriImageList,
        ];
    }


    protected function normalizeKontenPayload(array $data, bool $isCreate, $item = null): array
    {
        $title = trim((string) ($data['judul'] ?? ''));
        $content = (string) ($data['isi'] ?? $data['konten'] ?? '');
        $customSlug = trim((string) ($data['slug'] ?? ''));
        $baseSlug = Str::slug($customSlug !== '' ? $customSlug : $title);

        if ($baseSlug === '') {
            $baseSlug = 'konten-' . time();
        }

        $query = \App\Models\Konten::where('slug', $baseSlug);
        if (!$isCreate && $item) {
            $query->where($item->getKeyName(), '!=', $item->getKey());
        }
        $data['slug'] = $query->exists() ? $baseSlug . '-' . time() : $baseSlug;

        $submitAction = Str::lower((string) ($data['submit_action'] ?? ''));
        $status = $submitAction === 'draft'
            ? 'Draft'
            : ucfirst(Str::lower((string) ($data['status_publish'] ?? $data['status'] ?? 'Publish')));
        $data['status_publish'] = in_array($status, ['Publish', 'Draft'], true) ? $status : 'Publish';

        $data['isi'] = $content;
        unset($data['konten'], $data['status'], $data['submit_action']);

        $excerpt = trim((string) ($data['excerpt'] ?? ''));
        if ($excerpt === '') {
            $excerpt = $this->generateKontenExcerpt($content);
        } else {
            $excerpt = $this->cleanKontenExcerpt($excerpt, $content);
        }
        $data['excerpt'] = $excerpt;

        if (isset($data['tanggal_publish']) && $data['tanggal_publish'] !== '') {
            $data['tanggal_publish'] = date('Y-m-d H:i:s', strtotime(str_replace('T', ' ', (string) $data['tanggal_publish'])));
            if ($isCreate && empty($data['created_at'])) {
                $data['created_at'] = $data['tanggal_publish'];
            }
        } elseif ($isCreate) {
            $data['tanggal_publish'] = now()->format('Y-m-d H:i:s');
            $data['created_at'] = $data['tanggal_publish'];
        } elseif ($item && empty($data['tanggal_publish'])) {
            unset($data['tanggal_publish']);
        }

        $data['is_featured'] = !empty($data['is_featured']) && $data['is_featured'] !== 'false' ? 1 : 0;
        $data['embed_pdf'] = array_key_exists('embed_pdf', $data)
            ? (!empty($data['embed_pdf']) && $data['embed_pdf'] !== 'false' ? 1 : 0)
            : 1;

        foreach (['kategori_id', 'arsip_id', 'arsip_digital_id', 'file_pdf'] as $nullable) {
            if (array_key_exists($nullable, $data) && ($data[$nullable] === '' || $data[$nullable] === 'null')) {
                $data[$nullable] = null;
            }
        }

        if (!empty($data['kategori_id']) && Schema::hasTable('kategori_konten')) {
            $kategori = DB::table('kategori_konten')->where('id', $data['kategori_id'])->first();
            if ($kategori) {
                $data['kategori'] = $kategori->nama_kategori ?? $data['kategori'] ?? null;
            }
        }

        if (empty($data['tipe'])) {
            $data['tipe'] = 'Berita';
        }

        if (!empty($data['gambar']) && is_string($data['gambar'])) {
            $data['gambar'] = ltrim($data['gambar'], '/');
        }

        if (empty($data['penulis'])) {
            $user = auth()->user();
            $data['penulis'] = $user?->nama_lengkap ?: $user?->username ?: 'Administrator';
        }

        if ($isCreate && empty($data['created_by']) && auth()->id()) {
            $data['created_by'] = auth()->id();
        }

        return $data;
    }


    protected function ensureKategoriKontenTableAndData(): void
    {
        try {
            if (!\Illuminate\Support\Facades\Schema::hasTable('kategori_konten')) {
                \Illuminate\Support\Facades\Schema::create('kategori_konten', function ($table) {
                    $table->increments('id_kategori');
                    $table->string('nama_kategori', 100);
                    $table->string('slug', 120)->nullable();
                    $table->text('deskripsi')->nullable();
                    $table->string('ikon', 50)->default('fa-newspaper');
                    $table->integer('urutan')->default(1);
                    $table->string('status', 20)->default('Aktif');
                    $table->timestamps();
                });

                \Illuminate\Support\Facades\DB::table('kategori_konten')->insert([
                    ['nama_kategori' => 'Berita & Warta Paroki', 'slug' => 'berita-warta-paroki', 'deskripsi' => 'Liputan kegiatan dan berita terkini di Paroki', 'ikon' => 'fa-newspaper', 'urutan' => 1, 'status' => 'Aktif', 'created_at' => now(), 'updated_at' => now()],
                    ['nama_kategori' => 'Pengumuman Resmi Paroki', 'slug' => 'pengumuman-resmi-paroki', 'deskripsi' => 'Pengumuman misa, sakramen, dan sekretariat', 'ikon' => 'fa-bullhorn', 'urutan' => 2, 'status' => 'Aktif', 'created_at' => now(), 'updated_at' => now()],
                    ['nama_kategori' => 'Renungan Harian & Rohani', 'slug' => 'renungan-harian-rohani', 'deskripsi' => 'Santapan rohani, renungan injil, dan katekese', 'ikon' => 'fa-book-open', 'urutan' => 3, 'status' => 'Aktif', 'created_at' => now(), 'updated_at' => now()],
                    ['nama_kategori' => 'Kategorial & Komunitas', 'slug' => 'kategorial-komunitas', 'deskripsi' => 'Warta kegiatan OMK, WKRI, Legio Mariae, dll.', 'ikon' => 'fa-users', 'urutan' => 4, 'status' => 'Aktif', 'created_at' => now(), 'updated_at' => now()],
                    ['nama_kategori' => 'Liturgi & Peribadatan', 'slug' => 'liturgi-peribadatan', 'deskripsi' => 'Pedoman dan jadwal perayaan ekaristi & liturgi', 'ikon' => 'fa-cross', 'urutan' => 5, 'status' => 'Aktif', 'created_at' => now(), 'updated_at' => now()],
                ]);
            }
        } catch (\Throwable $e) {
            // Silently continue
        }
    }


    protected function ensureKomentarArtikelTableAndData(): void
    {
        try {
            \App\Models\KomentarArtikel::ensureTableExists();
        } catch (\Throwable $e) {
            // Silently continue
        }
    }

}
