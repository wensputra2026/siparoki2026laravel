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

trait GenericModuleTrait
{
                }
                return \App\Models\DesaKelurahan::take(500)->orderBy('nama_desa')->get(['id_desa', 'kecamatan_id', 'nama_desa']);
            });
        }
        $parokiList = $needsParokiReferences
            ? \Illuminate\Support\Facades\Cache::remember('ref_paroki_list_all_v2', 3600, function() {
                return \App\Models\Paroki::orderBy('nama_paroki')->get(['id_paroki', 'keuskupan_id', 'nama_paroki', 'kode_paroki']);
            })
            : collect();
        $defaultParoki = $needsParokiReferences
            ? $parokiList->firstWhere('id_paroki', $defaultParokiId)
            : null;
        $roleList = $slug === 'user'
            ? \Illuminate\Support\Facades\Cache::remember('ref_role_list_v1', 3600, function() {
                return \App\Models\Role::where('status', 1)->orderBy('nama_role')->get(['id', 'nama_role', 'slug']);
            })
            : [];
        $wilayahList = \Illuminate\Support\Facades\Cache::remember('ref_wilayah_list_v2', 1800, fn () => \App\Models\Wilayah::orderBy('nama_wilayah')->get());
        $kapelaList = \Illuminate\Support\Facades\Cache::remember('ref_kapela_list_v2', 1800, fn () => \App\Models\Kapela::orderBy('nama_kapela')->get());
        $kubList = \Illuminate\Support\Facades\Cache::remember('ref_kub_list_v2', 1800, fn () => \App\Models\Kub::orderBy('nama_kub')->get());
        // Daftar umat kini diambil lazy via endpoint /umat-options (tenant-scoped)
        // agar tidak memuat ratusan baris umat setiap buka modul.
        $umatList = [];
        if (str_contains($userRoleSlug, 'wilayah') && !empty($authUser?->wilayah_id)) {
            $wilayahList = $wilayahList->where('id', $authUser->wilayah_id)->values();
            $kubList = $kubList->where('wilayah_id', $authUser->wilayah_id)->values();
            $kapelaList = collect();
            if ($needsUmatReferences) {
                $umatList = collect([]);
            }
        } elseif ((str_contains($userRoleSlug, 'kapela') || str_contains($userRoleSlug, 'stasi')) && !empty($authUser?->kapela_id)) {
            $kapelaList = $kapelaList->where('id', $authUser->kapela_id)->values();
            $kubList = $kubList->where('kapela_id', $authUser->kapela_id)->values();
            $wilayahList = collect();
            if ($needsUmatReferences) {
                $umatList = collect([]);
            }
        } elseif (str_contains($userRoleSlug, 'kub') && !empty($authUser?->kub_id)) {
            $kubList = $kubList->where('id', $authUser->kub_id)->values();
            $wilayahList = collect();
            $kapelaList = collect();
            if ($needsUmatReferences) {
                $umatList = collect([]);
            }
        }
        $kategoriKontenList = $needsKontenReferences && Schema::hasTable('kategori_konten')
            ? DB::table('kategori_konten')
                ->when(Schema::hasColumn('kategori_konten', 'is_deleted'), fn ($q) => $q->where(function ($qq) {
                    $qq->where('is_deleted', 0)->orWhereNull('is_deleted');
                }))
                ->when(Schema::hasColumn('kategori_konten', 'status'), fn ($q) => $q->where(function ($qq) {
                    $qq->where('status', 1)->orWhere('status', 'Aktif')->orWhereNull('status');
                }))
                ->orderBy('nama_kategori')
                ->get(['id', 'nama_kategori', 'slug', 'tipe'])
            : [];
        $penulisList = $needsKontenReferences
            ? collect(DB::table('users')->whereNotNull('nama_lengkap')->where('nama_lengkap', '!=', '')->pluck('nama_lengkap'))
                ->merge(DB::table('konten')->whereNotNull('penulis')->where('penulis', '!=', '')->distinct()->pluck('penulis'))
                ->filter()
                ->unique(fn ($name) => Str::lower(trim((string) $name)))
                ->sort(SORT_NATURAL | SORT_FLAG_CASE)
                ->values()
            : [];
        $mergedPastors = $needsPastors
            ? \Illuminate\Support\Facades\Cache::remember('ref_merged_pastors_v2', 3600, function() {
                $defaultPastors = [
                    'RD. Adrianus Dimu', 'RD. Adrianus Manek', 'RD. Adrianus Ngenggor', 'RD. Agustinus Bastian', 'RD. Agustinus Parera',
                    'RD. Alandjino Da costa Dos Santos Soares', 'RD. Alfons Nara Hokon', 'RD Aloysius Lake', 'RD Aloysius Monteiro',
                    'RD Ambrosius Ladjar', 'RD Andreas Sikka', 'RD. Anselmus Leu', 'RD. Anselmus Sengga', 'RD. Antonius Moruk',
                    'RD. Antonius Nggino Tukan', 'RD. Antonius Sutarjo Duka', 'RD. Apolinarius Ladjar', 'RD. Arkhingelius Asa',
                    'RD. Beatus Ninu', 'RD. Benedictus Usnaat', 'RD. Benyamin Sanak', 'RP. Berto Wontong', 'P. Blas, OCD',
                    'RD. Blasius T. S. Ujan', 'RD. Claretian House', 'RD. Dagobertus Sota Ringgi', 'RP. Damianus Tasaeb',
                    'RD. Damianus Tasaeb Lamak', 'RD. Daniel Banamtuan', 'P Didi, SVD', 'RD. Diksel Susang',
                    'RD. Don Bosco Verantino Igo Making', 'RD. Emanuel Assan Boro', 'RD. Emmanuel Bere Damian', 'RD. Erick Fkun',
                    'RD. Eusebius Nofu', 'RD. Firmus Ninu', 'RD. Florens Maximus Un Bria', 'RD. Frans Lackner',
                    'RD. Fransisco Freinademetz Mada', 'RD. Fransiskus Atamau', 'RD. Fransiskus Kopong Mamu',
                    'RD. Fransiskus Xaverius Paut', 'RD. Frederikus A Tamelab', 'RD. Gabriel Benu', 'RD. Hendrikus Agusto Naifios',
                    'RD. Hergi Jebarus', 'RD. Herman Hilers Penga', 'Mgr Hironimus Pakaenoni', 'RD. Januarius Kado', 'RD Joanes Bria',
                    'RD. Johanes Puri Ujan', 'RD. Johannes A. Tnomel', 'RD John Subani', 'RD. Jonisius Semar Taek', 'RD. Kandidus Luan',
                    'RD. Kanisius Ati', 'RD. Kanisius Pen', 'RD. Kayetanus Un', 'RD. Krisostomus Taus', 'RD Krispinus Saku',
                    'RD. Kristoforus Muda', 'RD. Leonardus Apong', 'RD. Leonardus Enos Dau', 'RD. Marselinus Bouk',
                    'RD. Marselinus Nubatonis', 'RD. Marselinus Seludin', 'RD. Martin', 'RD. Maximus Amfotis', 'RD. Melkiades Deza',
                    'RD. Melkior tTamelb', 'Mgr. Agustinus Tri Budi Utomo', 'Mgr. Anton Pain Ratu SVD', 'Mgr. Antonio Guido Filipazzi',
                    'Mgr. Bernardus Bofitwos Baru OSA', 'Mgr. Fransiskus Nipa', 'Mgr. Gregorius Monteiro SVD', 'Mgr. Hironimus Pakaenoni',
                    'Mgr. Maksimus Regus', 'Mgr. Paulus Budi Kleden SVD', 'Mgr. Petrus Turang Pr.', 'Mgr. Valentinus Saeng CP',
                    'Mgr. Victorius Dwiardy OFM Cap.', 'Mgr. Vincensius Setiawan Triatmojo', 'Mgr. Yanuarius Theofilus Matopai You',
                    'Ordo Scholapio', 'P Oris, OCD', 'RD. Pastor Claretian', 'RD. Pastor Hati Kudus', 'RD. Pastor Pra Novis I',
                    'RD Patris Neonhub', 'RD. Patrisius Bakior', 'RD. Patrisius Tampani', 'RD. Paulus Ito Bari',
                    'RD. Petrus Damianus Leo Tapo', 'RD. Petrus Olin', 'RD. Philipus Philich', 'Pra Novist Claret',
                    'RD. Primus Mui Taimenas', 'RD. Primus Taimenas', 'RD. Primus Tjung Lake', 'Provinsialat Claretian (CMF)',
                    'RD. Ronaldus Kobesi', 'P Ronny, SVD', 'RD. Rudolf Tjung Lake', 'Scolastika Hati Maria CMF',
                    'RD. Sebastianus Kefi', 'RD. Sebastianus Wadjang', 'RD. Severinus Saka', 'RD Sintus Efi', 'P Sintus Kiik, MI',
                    'RD Stef Mau', 'RD. Thimotius R. Wake', 'RD. Venansius Dua', 'RD. Vinsensius Knaofmone', 'RD. Vinsensius Tamelab',
                    'RD. Wensislaus Yustino Raring', 'RD. Willem Laga Udjan', 'P Xave, MI', 'RD. Xaverius T. Alupan',
                    'RD. Yakobus Sarumaha Ximenes', 'RD. Yarid Kornelis Munah', 'RD. Yeremias Robinson Seran',
                    'RD. Yeremias Sora Kewohon', 'RD. Yoakim Konis', 'RD. Yoh. Berchmans Manuk', 'RD Yohanes Bria',
                    'RD. Yohanes Fransiskus Nino', 'RD. Yohanes Soni Keraf', 'RD. Yohanes Wattimena', 'RD. Yonas Kamlasi',
                    'RD. Yoseph Binsasi', 'RD. Yovinianus Nitti', 'RD. Yulius Bonlay', 'RD. Yulius Efu', 'RD. Yustinus Phoa',
                    'RD. Yustinus Raring', 'RD. Yustinus Tegu Wona'
                ];
                $pastorsFromDb = \App\Models\MasterPastor::orderBy('nama_pastor')->get()->map(fn($p) => $p->nama_lengkap_gelar ?: $p->nama_pastor)->toArray();
                $merged = array_values(array_unique(array_filter(array_merge($pastorsFromDb, $defaultPastors))));
                sort($merged);
                return $merged;
            })
            : [];
        $kkList = [];
        if ($slug === 'iuran' || in_array($slug, ['kk-katolik', 'kk', 'keluarga'], true)) {
            $kkQuery = \App\Models\KkKatolik::when(Schema::hasColumn('kk_katolik', 'is_deleted'), fn ($q) => $q->where(function ($qq) {
                $qq->where('is_deleted', 0)->orWhereNull('is_deleted');
            }));
            if (str_contains($userRoleSlug, 'wilayah') && !empty($authUser?->wilayah_id)) {
                $kkQuery->where('wilayah_id', $authUser->wilayah_id);
            } elseif ((str_contains($userRoleSlug, 'kapela') || str_contains($userRoleSlug, 'stasi')) && !empty($authUser?->kapela_id)) {
                $kkQuery->where('kapela_id', $authUser->kapela_id);
            } elseif (str_contains($userRoleSlug, 'kub') && !empty($authUser?->kub_id)) {
                $kkQuery->where('kub_id', $authUser->kub_id);
            }
            $kkList = $kkQuery->orderBy('nama_lahir_pemilik')->get(['id', 'no_kk_kw', 'no_kk_dukcapil', 'nama_lahir_pemilik', 'nama_baptis_pemilik', 'wilayah_id', 'kub_id', 'kapela_id'])
                ->map(function ($k) {
                    $baptis = trim($k->nama_baptis_pemilik ?? '');
                    $lahir = trim($k->nama_lahir_pemilik ?? '');
                    $fullName = $lahir;
                    if ($baptis && !str_contains(strtolower($lahir), strtolower($baptis))) {
                        $fullName = "{$baptis} {$lahir}";
                    }
                    $noKk = $k->no_kk_kw ?: $k->no_kk_dukcapil ?: ('KK#' . $k->id);
                    return [
                        'id' => $k->id,
                        'no_kk' => $noKk,
                        'nama_kepala' => $fullName,
                        'label' => "[{$noKk}] {$fullName}",
                    ];
                });
        }
        $jenisIuranList = ($slug === 'iuran' || $slug === 'jenis-iuran') && Schema::hasTable('jenis_iuran')
            ? DB::table('jenis_iuran')
                ->when(Schema::hasColumn('jenis_iuran', 'is_deleted'), fn ($q) => $q->where(function ($qq) {
                    $qq->where('is_deleted', 0)->orWhereNull('is_deleted');
                }))
                ->when(Schema::hasColumn('jenis_iuran', 'status'), fn ($q) => $q->where(function ($qq) {
                    $qq->where('status', 1)->orWhere('status', 'Aktif')->orWhereNull('status');
                }))
                ->orderBy('nama_iuran')
                ->get(['id', 'nama_iuran', 'nominal_default', 'periode', 'kategori_iuran'])
            : [];
        // Enrich column metadata with ENUM options so the generic form can
        // render a proper dropdown instead of a free-text input (prevents
        // "Data truncated" DB errors on enum columns like jenis_tugas).
        $enumTable = (new $config['model'])->getTable();
        foreach ($config['columns'] as &$col) {
            if (!empty($col['key'])) {
                $opts = $this->getEnumOptions($enumTable, $col['key']);
                if ($opts !== null) {
                    $col['isEnum'] = true;
                    $col['enumOptions'] = $opts;
                }
            }
        }
        unset($col);
        return Inertia::render('Inertia/GenericModule', [
            'title' => $config['title'],
            'moduleKey' => $slug,
            'columns' => $config['columns'],
            'items' => $items,
            'role' => $resolvedRole,
            'keuskupanList' => $keuskupanList,
            'dekenatList' => $dekenatList,
            'parokiList' => $parokiList,
            'defaultParokiId' => $defaultParokiId,
            'defaultParoki' => $defaultParoki,
            'provinsiList' => $provinsiList,
            'kabupatenList' => $kabupatenList,
            'kecamatanList' => $kecamatanList,
            'desaList' => $desaList,
            'pastorList' => $mergedPastors,
            'roleList' => $roleList,
            'wilayahList' => $wilayahList,
            'kapelaList' => $kapelaList,
            'kubList' => $kubList,
            'umatList' => $umatList,
            'kkList' => $kkList,
            'jenisIuranList' => $jenisIuranList,
            'kategoriKontenList' => $kategoriKontenList,
            'penulisList' => $penulisList,
            'filters' => [
                'search' => $search,
                'per_page' => $perPage,
                'keuskupan_id' => $keuskupanFilter,
                'dekenat_id' => $dekenatFilter,
                'provinsi_id' => $provinsiFilter,
                'kabupaten_id' => $kabupatenFilter,
                'kecamatan_id' => $kecamatanFilter,
                'tipe' => $tipeFilter,
                'role_id' => $roleIdFilter ?? null,
                'wilayah_id' => $wilayahIdFilter ?? null,
                'kapela_id' => $kapelaIdFilter ?? null,
                'kub_id' => $kubIdFilter ?? null,
                'status' => $statusFilter ?? null,
            ],
        ]);
    }
    /**
     * Store new record for pastoral modules with file upload support.
     */
    public function storeModule(Request $request, string $slug)
    {
        $moduleMap = $this->getModuleMap();
        if (!isset($moduleMap[$slug])) {
            return back()->with('error', 'Modul tidak ditemukan.');
        }
        $config = $moduleMap[$slug];
        $modelClass = $config['model'];
        // Financial data must be protected from manipulation (positive amount,
        // known type/category). This also satisfies the audit requirement.
        if ($slug === 'keuangan') {
            $request->validate([
                'jumlah' => 'required|numeric|min:0.01',
                'jenis' => 'required|string|max:50',
                'kategori' => 'required|string|max:100',
            ], [
                'jumlah.required' => 'Nominal wajib diisi.',
                'jumlah.numeric' => 'Nominal harus berupa angka.',
                'jumlah.min' => 'Nominal harus lebih besar dari nol.',
                'jenis.required' => 'Jenis transaksi wajib dipilih.',
                'kategori.required' => 'Kategori transaksi wajib dipilih.',
            ]);
        }
        $data = $request->except(['_token', '_method']);
        // Universal file upload processing
        foreach ($request->allFiles() as $fileKey => $uploadedFile) {
            $data[$fileKey] = $this->storeModuleUploadedFile($slug, $fileKey, $uploadedFile);
        }
        if ($slug === 'user') {
            $data = $this->normalizeUserPayload($data, true);
        }
        if ($slug === 'konten') {
            $data = $this->normalizeKontenPayload($data, true);
        }
        $firstSegment = explode('/', trim($request->path(), '/'))[0] ?? 'superadmin';
        $userRoleSlug = strtolower(auth()->user()?->role?->slug ?? auth()->user()?->role?->nama_role ?? '');
        if (in_array($slug, ['kk-katolik', 'kk', 'keluarga', 'umat', 'data-umat', 'sakramen', 'wilayah', 'kub'], true) && (in_array($firstSegment, ['wilayah', 'kapela', 'stasi'], true) || str_contains($userRoleSlug, 'wilayah') || str_contains($userRoleSlug, 'kapela') || str_contains($userRoleSlug, 'stasi'))) {
            return back()->with('error', 'Akses ditolak. Penambahan data hanya dapat dilakukan oleh Sekretariat Paroki / Super Admin.');
        }
        if (in_array($slug, ['kk-katolik', 'kk', 'keluarga'], true)) {
            $this->validateKkRequest($request);
            $data = $this->normalizeKkPayload($data, true);
        }
        if ($slug === 'kegiatan') {
            if (isset($data['nama_kegiatan']) && !isset($data['judul'])) {
                $data['judul'] = $data['nama_kegiatan'];
            } elseif (isset($data['judul']) && !isset($data['nama_kegiatan'])) {
                $data['nama_kegiatan'] = $data['judul'];
            }
            if (isset($data['gambar']) && !isset($data['foto'])) {
                $data['foto'] = $data['gambar'];
            } elseif (isset($data['foto']) && !isset($data['gambar'])) {
                $data['gambar'] = $data['foto'];
            }
            if (empty($data['slug']) && !empty($data['nama_kegiatan'] ?? $data['judul'])) {
                $data['slug'] = Str::slug($data['nama_kegiatan'] ?? $data['judul']);
            }
        }
        if (isset($data['nama_pastor_rekan']) && is_array($data['nama_pastor_rekan'])) {
            $data['nama_pastor_rekan'] = implode(', ', array_filter($data['nama_pastor_rekan']));
        }
        if ($slug === 'kuasi-paroki') {
            $data = $this->normalizeKuasiParokiPayload($data);
        }
        if ($slug === 'iuran') {
            $data = $this->normalizeIuranPayload($data);
        }
        if (($slug === 'kapela' || $slug === 'stasi') && Schema::hasColumn('kapela', 'paroki_id')) {
            $data['paroki_id'] = $this->defaultParokiIdFromProfile();
        }
        $nullableFks = ['keuskupan_id', 'dekenat_id', 'paroki_id', 'provinsi_id', 'kabupaten_id', 'kecamatan_id', 'desa_id', 'wilayah_id', 'kapela_id', 'kub_id', 'umat_id'];
        foreach ($nullableFks as $fk) {
            if (isset($data[$fk]) && ($data[$fk] === '' || $data[$fk] === 'null' || $data[$fk] === null)) {
                $data[$fk] = null;
            }
        }
        // Filter data strictly by database table schema to prevent unknown column errors
        $table = (new $modelClass)->getTable();
        $validColumns = $this->schemaColumns($table);
        $cleanData = [];
        foreach ($data as $k => $v) {
            if ($k === 'is_deleted') {
                continue;
            }
            if (in_array($k, $validColumns, true) && !in_array($k, ['created_at', 'updated_at', 'deleted_at'], true)) {
                if ($v === 'false') {
                    $v = 0;
                } elseif ($v === 'true') {
                    $v = 1;
                }
                $cleanData[$k] = $v;
            }
        }
        if (in_array($slug, ['kk-katolik', 'kk', 'keluarga'], true)) {
            $created = DB::transaction(function () use ($modelClass, $cleanData, $request) {
                $created = $modelClass::create($cleanData);
                $this->syncKkAnggota($created, $request->input('anggota', []));
                return $created;
            });
        } else {
            $created = $modelClass::create($cleanData);
        }
        if ($slug === 'kuasi-paroki' && $this->shouldPromoteKuasiParoki($data)) {
            DB::transaction(function () use ($created, $data) {
                $this->promoteKuasiParokiToParoki($created, $data);
            });
        }
        $this->logAudit('CREATE_' . strtoupper($slug), $slug, $created->getKey(), $cleanData);
        $this->clearFastAccessCache();
        return back()->with('success', 'Data ' . $config['title'] . ' berhasil ditambahkan.');
    }
    /**
     * Update existing record in database.
     */
    public function updateModule(Request $request, string $slug, $id)
    {
        $moduleMap = $this->getModuleMap();
        if (!isset($moduleMap[$slug])) {
            return back()->with('error', 'Modul tidak ditemukan.');
        }
        $config = $moduleMap[$slug];
        $modelClass = $config['model'];
        $modelInstance = new $modelClass;
        $pk = $modelInstance->getKeyName();
        // Try by the model's declared primary key first
        $item = $modelClass::where($pk, $id)->first();
        // Fallback: try common primary key patterns
        if (!$item) {
            $table = $modelInstance->getTable();
            $candidates = array_unique([
                $pk,
                'id',
                'id_' . str_replace('-', '_', $slug),
                'id_' . $table,
                // e.g. id_riwayat_pastor_paroki → too long, try short form
                'id_' . explode('_paroki', $table)[0] . '_paroki',
            ]);
            foreach ($candidates as $cand) {
                if ($cand !== $pk && Schema::hasColumn($table, $cand)) {
                    $found = $modelClass::where($cand, $id)->first();
                    if ($found) { $item = $found; break; }
                }
            }
        }
        if (!$item) {
            return back()->with('error', 'Data ' . $config['title'] . ' tidak ditemukan.');
        }
        $data = $request->except(['_token', '_method']);
        // Universal file upload processing
        foreach ($request->allFiles() as $fileKey => $uploadedFile) {
            $data[$fileKey] = $this->storeModuleUploadedFile($slug, $fileKey, $uploadedFile);
        }
        if ($slug === 'user') {
            $data = $this->normalizeUserPayload($data, false);
        }
        if ($slug === 'konten') {
            $data = $this->normalizeKontenPayload($data, false, $item);
        }
        $firstSegment = explode('/', trim($request->path(), '/'))[0] ?? 'superadmin';
        $userRoleSlug = strtolower(auth()->user()?->role?->slug ?? auth()->user()?->role?->nama_role ?? '');
        if (in_array($slug, ['kk-katolik', 'kk', 'keluarga', 'umat', 'data-umat', 'wilayah', 'kub', 'sakramen'], true) && (in_array($firstSegment, ['wilayah', 'kapela', 'stasi'], true) || str_contains($userRoleSlug, 'wilayah') || str_contains($userRoleSlug, 'kapela') || str_contains($userRoleSlug, 'stasi'))) {
            return back()->with('error', 'Akses ditolak. Pengelolaan data (tambah/edit/hapus) hanya dapat dilakukan pada tingkat KUB atau Sekretariat Paroki.');
        }
        if (in_array($slug, ['kk-katolik', 'kk', 'keluarga'], true)) {
            $this->validateKkRequest($request, $item);
            $data = $this->normalizeKkPayload($data, false, $item);
        }
        if ($slug === 'kegiatan') {
            if (isset($data['nama_kegiatan']) && !isset($data['judul'])) {
                $data['judul'] = $data['nama_kegiatan'];
            } elseif (isset($data['judul']) && !isset($data['nama_kegiatan'])) {
                $data['nama_kegiatan'] = $data['judul'];
            }
            if (isset($data['gambar']) && !isset($data['foto'])) {
                $data['foto'] = $data['gambar'];
            } elseif (isset($data['foto']) && !isset($data['gambar'])) {
                $data['gambar'] = $data['foto'];
            }
            if (empty($data['slug']) && !empty($data['nama_kegiatan'] ?? $data['judul'])) {
                $data['slug'] = Str::slug($data['nama_kegiatan'] ?? $data['judul']);
            }
        }
        if (isset($data['nama_pastor_rekan']) && is_array($data['nama_pastor_rekan'])) {
            $data['nama_pastor_rekan'] = implode(', ', array_filter($data['nama_pastor_rekan']));
        }
        if ($slug === 'kuasi-paroki') {
            $data = $this->normalizeKuasiParokiPayload($data, $item);
        }
        if ($slug === 'iuran') {
            $data = $this->normalizeIuranPayload($data);
        }
        if (($slug === 'kapela' || $slug === 'stasi') && Schema::hasColumn('kapela', 'paroki_id')) {
            $data['paroki_id'] = $this->defaultParokiIdFromProfile();
        }
        $nullableFks = ['keuskupan_id', 'dekenat_id', 'paroki_id', 'provinsi_id', 'kabupaten_id', 'kecamatan_id', 'desa_id', 'wilayah_id', 'kapela_id', 'kub_id', 'umat_id'];
        foreach ($nullableFks as $fk) {
            if (isset($data[$fk]) && ($data[$fk] === '' || $data[$fk] === 'null' || $data[$fk] === null)) {
                $data[$fk] = null;
            }
        }
        // Filter data strictly by database table schema to prevent unknown column errors
        $table = (new $modelClass)->getTable();
        $validColumns = $this->schemaColumns($table);
        $cleanData = [];
        foreach ($data as $k => $v) {
            if ($k === 'is_deleted') {
                continue;
            }
            if (in_array($k, $validColumns, true) && !in_array($k, ['created_at', 'updated_at', 'deleted_at'], true)) {
                if ($v === 'false') {
                    $v = 0;
                } elseif ($v === 'true') {
                    $v = 1;
                }
                $cleanData[$k] = $v;
            }
        }
        if (in_array($slug, ['kk-katolik', 'kk', 'keluarga'], true)) {
            DB::transaction(function () use ($item, $cleanData, $request) {
                $item->update($cleanData);
                $item->refresh();
                $this->syncKkAnggota($item, $request->input('anggota', []));
            });
        } elseif ($slug === 'kuasi-paroki' && $this->shouldPromoteKuasiParoki($data)) {
            DB::transaction(function () use ($item, $cleanData, $data) {
                $item->update($cleanData);
                $item->refresh();
                $this->promoteKuasiParokiToParoki($item, $data);
            });
        } else {
            $item->update($cleanData);
        }
        $this->clearFastAccessCache();
        $this->logAudit('UPDATE_' . strtoupper($slug), $slug, $item->getKey(), $cleanData);
        // Auto-sync into global settings if the updated record is paroki
        if ($slug === 'paroki') {
            $this->syncParokiToGlobalSettings($item);
        }
        return back()->with('success', 'Data ' . $config['title'] . ' berhasil diperbarui.');
    }
    /**
     * Delete record from database.
     */
    public function destroyModule(Request $request, string $slug, $id)
    {
        $moduleMap = $this->getModuleMap();
        if (!isset($moduleMap[$slug])) {
            return back()->with('error', 'Modul tidak ditemukan.');
        }
        $config = $moduleMap[$slug];
        $modelClass = $config['model'];
        $modelInstance = new $modelClass;
        $pk = $modelInstance->getKeyName();
        $item = $modelClass::where($pk, $id)->first();
        if (!$item && is_numeric($id)) {
            $item = $modelClass::find($id);
        }
        if (!$item) {
            $item = $modelClass::where('id', $id)->first();
        }
        if (!$item && in_array('slug', \Illuminate\Support\Facades\Schema::getColumnListing($modelInstance->getTable()))) {
            $item = $modelClass::where('slug', $id)->first();
        }
        if (!$item) {
            return back()->with('error', 'Data tidak ditemukan.');
        }
        $firstSegment = explode('/', trim($request->path(), '/'))[0] ?? 'superadmin';
        $userRoleSlug = strtolower(auth()->user()?->role?->slug ?? auth()->user()?->role?->nama_role ?? '');
        if (in_array($slug, ['umat', 'data-umat', 'kk-katolik', 'kk', 'keluarga', 'sakramen', 'wilayah', 'kub'], true) && (in_array($firstSegment, ['wilayah', 'kapela', 'stasi'], true) || str_contains($userRoleSlug, 'wilayah') || str_contains($userRoleSlug, 'kapela') || str_contains($userRoleSlug, 'stasi'))) {
            return back()->with('error', 'Akses ditolak. Penghapusan data struktur Wilayah & KUB hanya dapat dilakukan oleh Sekretariat Paroki / Super Admin.');
        }
        if ($slug === 'user' && auth()->id() && (int) auth()->id() === (int) $item->getKey()) {
            return back()->with('error', 'Akun yang sedang digunakan tidak dapat dihapus.');
        }
        $this->logAudit('DELETE_' . strtoupper($slug), $slug, $item->getKey());
        $item->delete();
        $this->clearFastAccessCache();
        return back()->with('success', 'Data ' . $config['title'] . ' berhasil dihapus.');
    }
    /**
     * Export generic module data to Excel (CSV), PDF, or Printable View.
     */
    private function exportModuleLegacyCsv(Request $request, string $slug, string $format)
    {
        $moduleMap = $this->getModuleMap();
        if (!isset($moduleMap[$slug])) {
            abort(404, 'Modul tidak ditemukan.');
        }
        $config = $moduleMap[$slug];
        $modelClass = $config['model'];
        $query = $modelClass::query();
        // Apply filters
        $search = $request->query('search');
        if ($search) {
            $query->where(function ($q) use ($search, $config) {
                foreach ($config['columns'] as $col) {
                    if (empty($col['isImage']) && empty($col['isRelationLink'])) {
                        $q->orWhere($col['key'], 'like', "%{$search}%");
                    }
                }
            });
        }
        $items = $query->latest()->get();
        $title = $config['title'];
        $columns = $config['columns'];
        if ($format === 'excel' || $format === 'csv' || $format === 'template') {
            $filename = ($format === 'template' ? 'template-' : '') . Str::slug($title) . '-' . date('Y-m-d_His') . '.csv';
            $headers = [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
                'Pragma' => 'no-cache',
                'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
                'Expires' => '0',
            ];
            $callback = function () use ($items, $columns, $format) {
                $file = fopen('php://output', 'w');
                // Output UTF-8 BOM for Excel compatibility
                fputs($file, "\xEF\xBB\xBF");
                // Headers
                $headerRow = [];
                foreach ($columns as $col) {
                    if (empty($col['isImage']) && empty($col['isRelationLink'])) {
                        $headerRow[] = $col['key'];
                    }
                }
                fputcsv($file, $headerRow);
                if ($format !== 'template') {
                    // Data Rows
                    foreach ($items as $item) {
                        $row = [];
                        foreach ($columns as $col) {
                            if (empty($col['isImage']) && empty($col['isRelationLink'])) {
                                $val = $item->{$col['key']} ?? ($col['altKey'] ? ($item->{$col['altKey']} ?? '') : '');
                                if (is_array($val)) $val = implode(', ', $val);
                                $row[] = (string) $val;
                            }
                        }
                        fputcsv($file, $row);
                    }
                }
                fclose($file);
            };
            return response()->stream($callback, 200, $headers);
        }
        // PDF / Print View
        $paroki = \App\Models\Paroki::first();
        $namaParoki = $paroki->nama_paroki ?? 'Paroki Katolik';
        $html = '<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan ' . htmlspecialchars($title) . ' - ' . htmlspecialchars($namaParoki) . '</title>
    <style>
        body { font-family: "Segoe UI", Arial, sans-serif; font-size: 11px; color: #1e293b; margin: 20px; line-height: 1.4; }
        .header { text-align: center; border-bottom: 2px solid #0f172a; padding-bottom: 12px; margin-bottom: 16px; }
        .header h1 { font-size: 16px; margin: 0; text-transform: uppercase; color: #0f172a; }
        .header h2 { font-size: 13px; margin: 4px 0 0 0; color: #b45309; font-weight: bold; }
        .header p { font-size: 10px; color: #64748b; margin: 2px 0 0 0; }

        .meta { display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 10px; color: #475569; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th { background-color: #f1f5f9; color: #334155; font-weight: bold; text-align: left; padding: 6px 8px; border: 1px solid #cbd5e1; font-size: 10px; text-transform: uppercase; }
        td { padding: 5px 8px; border: 1px solid #e2e8f0; font-size: 10.5px; }
        tr:nth-child(even) { background-color: #f8fafc; }
        .footer { margin-top: 24px; display: flex; justify-content: space-between; font-size: 10px; color: #64748b; }
        @media print {
            .no-print { display: none; }
            body { margin: 0; }
        }
    </style>
</head>
<body onload="' . ($format === 'print' || $format === 'pdf' ? 'window.print()' : '') . '">
    <div class="no-print" style="margin-bottom: 15px; padding: 10px; background: #fef3c7; border: 1px solid #fde68a; border-radius: 8px; display: flex; justify-content: space-between; align-items: center;">
        <span>Dokumen siap dicetak / diexport PDF. Klik tombol cetak jika dialog tidak muncul otomatis.</span>
        <button onclick="window.print()" style="padding: 6px 14px; background: #d97706; color: white; border: none; border-radius: 6px; font-weight: bold; cursor: pointer;">Cetak Dokumen</button>
    </div>
    <div class="header">
        <h1>' . htmlspecialchars($namaParoki) . '</h1>
        <h2>Laporan Data ' . htmlspecialchars($title) . '</h2>
        <p>Dicetak pada: ' . date('d F Y, H:i') . ' WITA | Total: ' . count($items) . ' Data</p>
    </div>
    <table>
        <thead>
            <tr>
                <th style="width: 30px; text-align: center;">No</th>';
        foreach ($columns as $col) {
            if (empty($col['isImage']) && empty($col['isRelationLink'])) {
                $html .= '<th>' . htmlspecialchars($col['label']) . '</th>';
            }
        }
        $html .= '</tr>
        </thead>
        <tbody>';
        $no = 1;
        foreach ($items as $item) {
            $html .= '<tr><td style="text-align: center;">' . $no++ . '</td>';
            foreach ($columns as $col) {
                if (empty($col['isImage']) && empty($col['isRelationLink'])) {
                    $val = $item->{$col['key']} ?? ($col['altKey'] ? ($item->{$col['altKey']} ?? '') : '');
                    if (is_array($val)) $val = implode(', ', $val);
                    $html .= '<td>' . htmlspecialchars((string)$val) . '</td>';
                }
            }
            $html .= '</tr>';
        }
        $html .= '</tbody>
    </table>
    <div class="footer">
        <div>SIPAROKI &copy; ' . date('Y') . ' - Sistem Informasi Paroki</div>
        <div>Halaman 1 / 1</div>
    </div>
</body>
</html>';
        return response($html, 200, ['Content-Type' => 'text/html; charset=UTF-8']);
    }
    /**
     * Import generic module data from CSV / Excel file.
     */
    /**
     * Import generic module data from CSV / Excel file with strict anti-duplicate validation.
     */
    private function importModuleLegacyCsv(Request $request, string $slug)
    {
        $moduleMap = $this->getModuleMap();
        if (!isset($moduleMap[$slug])) {
            return back()->with('error', 'Modul tidak ditemukan.');
        }
        $request->validate([
            'file' => 'required|file|max:15360',
        ]);
        $config = $moduleMap[$slug];
        $modelClass = $config['model'];
        $modelInstance = new $modelClass;
        $table = $modelInstance->getTable();
        $tableCols = \Illuminate\Support\Facades\Schema::getColumnListing($table);
        $file = $request->file('file');
        try {
            $handle = fopen($file->getRealPath(), 'r');
            if ($handle === false) {
                return back()->with('error', 'Gagal membaca file.');
            }
            // Remove UTF-8 BOM if present
            $bom = fread($handle, 3);
            if ($bom !== "\xEF\xBB\xBF") {
                rewind($handle);
            }
            $header = fgetcsv($handle, 2000, ',');
            if (!$header) {
                fclose($handle);
                return back()->with('error', 'Format header file CSV / Excel kosong atau tidak valid.');
            }
            // Header dictionary mapper
            $headerMap = [
                'no_kk' => 'no_kk_kw',
                'nomor_kk' => 'no_kk_kw',
                'no_kk_katolik' => 'no_kk_kw',
                'nomor_kartu_keluarga' => 'no_kk_kw',
                'nomor_kk_katolik' => 'no_kk_kw',
                'no_kk_dukcapil' => 'no_kk_dukcapil',
                'kk_dukcapil' => 'no_kk_dukcapil',
                'nik' => 'nik',
                'no_ktp' => 'nik',
                'nomor_induk_kependudukan' => 'nik',
                'nama' => 'nama_lengkap',
                'nama_lengkap' => 'nama_lengkap',
                'nama_umat' => 'nama_lengkap',
                'nama_baptis' => 'nama_baptis',
                'nama_santo' => 'nama_baptis',
                'nama_lahir' => 'nama_lahir',
                'nama_marga' => 'nama_marga',
                'marga' => 'nama_marga',
                'kepala_keluarga' => 'nama_baptis_pemilik',
                'nama_kepala_keluarga' => 'nama_baptis_pemilik',
                'nama_pasangan' => 'nama_pasangan',
                'jenis_kelamin' => 'jenis_kelamin',
                'jk' => 'jenis_kelamin',
                'gender' => 'jenis_kelamin',
                'tempat_lahir' => 'tempat_lahir',
                'tanggal_lahir' => 'tanggal_lahir',
                'tgl_lahir' => 'tanggal_lahir',
                'hubungan_keluarga' => 'hubungan_keluarga',
                'kedudukan' => 'hubungan_keluarga',
                'status_menikah' => 'status_menikah',
                'status_perkawinan' => 'status_menikah',
                'alamat' => 'alamat_sekarang',
                'alamat_domisili' => 'alamat_sekarang',
                'alamat_sekarang' => 'alamat_sekarang',
                'rt' => 'rt',
                'rw' => 'rw',
                'desa' => 'desa_kelurahan',
                'desa_kelurahan' => 'desa_kelurahan',
                'kelurahan' => 'desa_kelurahan',

                'kecamatan' => 'kecamatan',
                'kota' => 'kota_kabupaten',
                'kabupaten' => 'kota_kabupaten',
                'kota_kabupaten' => 'kota_kabupaten',
                'telepon' => 'handphone',
                'no_hp' => 'handphone',
                'no_telepon' => 'handphone',
                'handphone' => 'handphone',
                'hp' => 'handphone',
                'email' => 'email',
                'pendidikan' => 'pendidikan_saat_ini',
                'pendidikan_terakhir' => 'pendidikan_saat_ini',
                'pendidikan_saat_ini' => 'pendidikan_saat_ini',
                'pekerjaan' => 'pekerjaan',
                'profesi' => 'pekerjaan',
                'golongan_darah' => 'golongan_darah',
                'gol_darah' => 'golongan_darah',
                'status' => 'status_kk',
                'status_kk' => 'status_kk',
                'status_umat' => 'status_umat',
                'status_aktif' => 'status_aktif',
            ];
            $cleanHeader = [];
            foreach ($header as $h) {
                $raw = trim(Str::snake(strtolower($h)));
                // Map via alias or default to raw
                $mapped = $headerMap[$raw] ?? $raw;
                if (!in_array($mapped, $tableCols, true) && in_array($raw, $tableCols, true)) {
                    $mapped = $raw;
                }
                $cleanHeader[] = $mapped;
            }
            $createdCount = 0;
            $updatedCount = 0;
            $skippedCount = 0;
            while (($row = fgetcsv($handle, 2000, ',')) !== false) {
                // Filter out empty rows
                $filledValues = array_filter($row, function ($v) {
                    return trim((string)$v) !== '';
                });
                if (empty($filledValues)) {
                    $skippedCount++;
                    continue;
                }
                $rowData = [];
                foreach ($cleanHeader as $idx => $headerName) {
                    if (isset($row[$idx]) && in_array($headerName, $tableCols, true)) {
                        $val = trim($row[$idx]);
                        // Date normalization (DD/MM/YYYY or DD-MM-YYYY to YYYY-MM-DD)
                        if (str_contains($headerName, 'tanggal') || str_contains($headerName, 'tgl') || str_ends_with($headerName, '_at')) {
                            if (preg_match('/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})$/', $val, $m)) {
                                $val = sprintf('%04d-%02d-%02d', $m[3], $m[2], $m[1]);
                            }
                        }
                        // Gender normalization
                        if ($headerName === 'jenis_kelamin') {
                            $upper = strtoupper($val);
                            if ($upper === 'L' || $upper === 'PRIA' || $upper === 'LAKI-LAKI' || $upper === 'LAKI') {
                                $val = 'Laki-Laki';
                            } elseif ($upper === 'P' || $upper === 'WANITA' || $upper === 'PEREMPUAN') {
                                $val = 'Perempuan';
                            }
                        }
                        // Status normalization
                        if ($headerName === 'status_aktif') {
                            $upper = strtoupper($val);
                            $val = ($upper === '1' || $upper === 'AKTIF' || $upper === 'YA' || $upper === 'TRUE') ? 1 : 0;
                        }
                        $rowData[$headerName] = $val;
                    }
                }
                if (empty($rowData)) {
                    $skippedCount++;
                    continue;
                }
                // Strict Deduplication & Upsert Logic
                $existing = null;
                if (in_array($slug, ['kk-katolik', 'kk', 'keluarga'], true)) {
                    if (!empty($rowData['no_kk_kw'])) {
                        $existing = $modelClass::where('no_kk_kw', $rowData['no_kk_kw'])->first();
                    }
                    if (!$existing && !empty($rowData['no_kk_dukcapil'])) {
                        $existing = $modelClass::where('no_kk_dukcapil', $rowData['no_kk_dukcapil'])->first();
                    }
                    if (!$existing && !empty($rowData['nik_pemilik'])) {
                        $existing = $modelClass::where('nik_pemilik', $rowData['nik_pemilik'])->first();
                    }
                } elseif (in_array($slug, ['umat', 'data-umat'], true)) {
                    if (!empty($rowData['nik']) && strlen($rowData['nik']) >= 10) {
                        $existing = $modelClass::where('nik', $rowData['nik'])->first();
                    }
                    if (!$existing && !empty($rowData['niu'])) {
                        $existing = $modelClass::where('niu', $rowData['niu'])->first();
                    }
                    if (!$existing && !empty($rowData['nama_lengkap']) && !empty($rowData['tanggal_lahir'])) {
                        $existing = $modelClass::where('nama_lengkap', $rowData['nama_lengkap'])
                            ->where('tanggal_lahir', $rowData['tanggal_lahir'])
                            ->first();
                    }
                } elseif ($slug === 'user') {
                    if (!empty($rowData['email'])) {
                        $existing = $modelClass::where('email', $rowData['email'])->first();
                    }
                    if (!$existing && !empty($rowData['username'])) {
                        $existing = $modelClass::where('username', $rowData['username'])->first();
                    }
                } elseif (in_array($slug, ['kategori-konten', 'kategori_konten'], true)) {
                    if (!empty($rowData['slug'])) {
                        $existing = $modelClass::where('slug', $rowData['slug'])->first();
                    }
                    if (!$existing && !empty($rowData['nama_kategori'])) {
                        $existing = $modelClass::where('nama_kategori', $rowData['nama_kategori'])->first();
                    }
                } elseif (in_array($slug, ['lingkungan', 'kub', 'wilayah', 'kapela', 'paroki', 'keuskupan', 'dekenat'], true)) {
                    $codeCols = ['kode_' . str_replace('-', '_', $slug), 'kode_lingkungan', 'kode_kub', 'kode_wilayah', 'kode_kapela', 'kode_paroki', 'kode_keuskupan', 'kode_dekenat'];
                    foreach ($codeCols as $cc) {
                        if (!empty($rowData[$cc])) {
                            $existing = $modelClass::where($cc, $rowData[$cc])->first();
                            if ($existing) break;
                        }
                    }
                    if (!$existing) {
                        $nameCols = ['nama_' . str_replace('-', '_', $slug), 'nama_lingkungan', 'nama_kub', 'nama_wilayah', 'nama_kapela', 'nama_paroki', 'nama_keuskupan', 'nama_kevikepan', 'nama_dekenat'];
                        foreach ($nameCols as $nc) {
                            if (!empty($rowData[$nc])) {
                                $existing = $modelClass::where($nc, $rowData[$nc])->first();
                                if ($existing) break;
                            }
                        }
                    }
                }
                if ($existing) {
                    // Update existing record (anti-duplicate)
                    $existing->update($rowData);
                    $updatedCount++;
                } else {
                    // Insert new record
                    $modelClass::create($rowData);
                    $createdCount++;
                }
            }
            fclose($handle);
            $this->clearFastAccessCache();
            return back()->with('success', "Proses import selesai: {$createdCount} data baru berhasil ditambahkan, {$updatedCount} data diperbarui (anti-duplikat), {$skippedCount} baris kosong dilewati.");
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal mengimpor data: ' . $e->getMessage());
        }
    }
    private function normalizeKontenPayload(array $data, bool $isCreate, $item = null): array
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
    private function validateKkRequest(Request $request, $item = null): void
    {
        $ignoreId = $item?->id;
        $unique = fn (string $column) => \Illuminate\Validation\Rule::unique('kk_katolik', $column)->ignore($ignoreId);
        $request->validate([
            'no_kk_kw' => ['required', 'string', 'max:50', $unique('no_kk_kw')],
            'no_kk_dukcapil' => ['nullable', 'digits_between:10,16', $unique('no_kk_dukcapil')],
            'nik_pemilik' => ['required', 'digits:16', $unique('nik_pemilik')],
            'nama_baptis_pemilik' => ['required', 'string', 'max:150'],
            'nama_lahir_pemilik' => ['required', 'string', 'max:150'],
            'nama_pasangan' => ['nullable', 'string', 'max:150'],
            'wilayah_id' => ['nullable', 'integer', 'exists:wilayah,id'],
            'kub_id' => ['nullable', 'integer', 'exists:kub,id'],
            'kapela_id' => ['nullable', 'integer', 'exists:kapela,id'],
            'lingkungan_id' => ['nullable', 'integer', 'exists:lingkungan,id'],
            'paroki_id' => ['nullable', 'integer'],
            'alamat_sekarang' => ['required', 'string', 'max:500'],
            'handphone' => ['required', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:150'],
            'status_verifikasi' => ['required', \Illuminate\Validation\Rule::in(['Belum', 'Terverifikasi', 'Ditolak'])],
            'status_kk' => ['required', \Illuminate\Validation\Rule::in(['Aktif', 'Pindah KUB', 'Pindah Wilayah', 'Pindah Paroki', 'Pecah KK', 'Tidak Aktif'])],
            'anggota' => ['nullable', 'array'],
            'anggota.*.id' => ['nullable', 'integer'],
            'anggota.*.nama_lengkap' => ['nullable', 'string', 'max:150'],
            'anggota.*.nama_baptis' => ['nullable', 'string', 'max:150'],
            'anggota.*.nik' => ['nullable', 'digits:16', 'distinct'],
            'anggota.*.hubungan_keluarga' => ['nullable', 'string', 'max:50'],
            'anggota.*.jenis_kelamin' => ['nullable', \Illuminate\Validation\Rule::in(['Laki-Laki', 'Perempuan'])],
            'anggota.*.tanggal_lahir' => ['nullable', 'date'],
            'anggota.*.tempat_lahir' => ['nullable', 'string', 'max:120'],
            'anggota.*.status_perkawinan' => ['nullable', 'string', 'max:80'],
        ]);
        foreach (array_values($request->input('anggota', [])) as $idx => $member) {
            if (!is_array($member)) {
                continue;
            }
            $nik = preg_replace('/\D+/', '', (string) ($member['nik'] ?? ''));
            if ($nik === '') {
                continue;
            }
            $query = \App\Models\Umat::where('nik', $nik);
            if (!empty($member['id'])) {
                $query->where('id', '!=', $member['id']);
            }
            if ($ignoreId) {
                $query->where(function ($q) use ($ignoreId) {
                    $q->where('kk_id', '!=', $ignoreId)->orWhereNull('kk_id');
                });
            }
            if ($query->exists()) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    "anggota.{$idx}.nik" => "NIK anggota {$nik} sudah digunakan oleh data umat lain.",
                ]);
            }
        }
    }
    private function normalizeKkPayload(array $data, bool $isCreate, $item = null): array
    {
        unset($data['anggota']);
        foreach (['no_kk_dukcapil', 'nik_pemilik', 'handphone'] as $field) {
            if (!empty($data[$field])) {
                $data[$field] = preg_replace('/\D+/', '', (string) $data[$field]);
            }
        }
        foreach (['wilayah_id', 'kub_id', 'kapela_id', 'paroki_id', 'lingkungan_id'] as $fk) {
            if (array_key_exists($fk, $data) && ($data[$fk] === '' || $data[$fk] === 'null')) {
                $data[$fk] = null;
            }
        }
        $data['status_kk'] = $data['status_kk'] ?? 'Aktif';
        $data['status_verifikasi'] = $data['status_verifikasi'] ?? 'Belum';
        if (empty($data['nama_baptis_pemilik']) && !empty($data['nama_lahir_pemilik'])) {
            $data['nama_baptis_pemilik'] = $data['nama_lahir_pemilik'];
        }
        if (empty($data['nama_lahir_pemilik']) && !empty($data['nama_baptis_pemilik'])) {
            $data['nama_lahir_pemilik'] = $data['nama_baptis_pemilik'];
        }
        if ($isCreate && empty($data['created_by']) && auth()->id()) {
            $data['created_by'] = auth()->id();
        }
        if (!$isCreate && auth()->id()) {
            $data['updated_by'] = auth()->id();
        }
        return $data;
    }
    private function syncKkAnggota(\App\Models\KkKatolik $kk, $members): void
    {
        if (!is_array($members)) {
            return;
        }
        $validColumns = $this->schemaColumns('umat');
        $keepIds = [];
        foreach (array_values($members) as $idx => $member) {
            if (!is_array($member)) {
                continue;
            }
            $namaLengkap = trim((string) ($member['nama_lengkap'] ?? $member['nama_lahir'] ?? ''));
            $namaBaptis = trim((string) ($member['nama_baptis'] ?? ''));
            $nik = preg_replace('/\D+/', '', (string) ($member['nik'] ?? ''));
            if ($namaLengkap === '' && $namaBaptis === '' && $nik === '') {
                continue;
            }
            $payload = [
                'kk_id' => $kk->id,
                'no_urut_anggota' => $idx + 1,
                'kode_anggota' => $member['kode_anggota'] ?? null,
                'suku_etnis' => $member['suku_etnis'] ?? null,
                'nik' => $nik ?: null,
                'nama_lengkap' => $namaLengkap ?: $namaBaptis,
                'nama_lahir' => $namaLengkap ?: $namaBaptis,
                'nama_baptis' => $namaBaptis ?: $namaLengkap,
                'no_kk_kw' => $kk->no_kk_kw,
                'nama_pemilik_kk' => $kk->nama_lahir_pemilik ?: $kk->nama_baptis_pemilik,
                'hubungan_keluarga' => $member['hubungan_keluarga'] ?? ($idx === 0 ? 'Kepala Keluarga' : 'Anak'),
                'jenis_kelamin' => $member['jenis_kelamin'] ?? null,
                'tempat_lahir' => $member['tempat_lahir'] ?? null,
                'tanggal_lahir' => $member['tanggal_lahir'] ?? null,
                'status_menikah' => $member['status_perkawinan'] ?? $member['status_menikah'] ?? null,
                'status_perkawinan' => $member['status_perkawinan'] ?? null,
                'agama_asal' => $member['agama_asal'] ?? null,
                'pendidikan_saat_ini' => $member['pendidikan_saat_ini'] ?? $member['pendidikan'] ?? null,
                'pendidikan' => $member['pendidikan'] ?? $member['pendidikan_saat_ini'] ?? null,
                'pekerjaan' => $member['pekerjaan'] ?? null,
                'golongan_darah' => $member['golongan_darah'] ?? null,
                'talenta' => $member['talenta'] ?? null,
                'disabilitas' => $member['disabilitas'] ?? null,
                'status_baptis' => $member['status_baptis'] ?? null,
                'jenis_penerimaan_baptis' => $member['jenis_penerimaan_baptis'] ?? null,
                'tgl_baptis' => $member['tgl_baptis'] ?? null,
                'paroki_baptis' => $member['paroki_baptis'] ?? null,
                'pastor_baptis' => $member['pastor_baptis'] ?? null,
                'wali_baptis' => $member['wali_baptis'] ?? null,
                'buku_baptis_vol' => $member['buku_baptis_vol'] ?? null,
                'buku_baptis_hal' => $member['buku_baptis_hal'] ?? null,

                'buku_baptis_no' => $member['buku_baptis_no'] ?? null,
                'tgl_komuni_1' => $member['tgl_komuni_1'] ?? null,
                'paroki_komuni_1' => $member['paroki_komuni_1'] ?? null,
                'tgl_krisma' => $member['tgl_krisma'] ?? null,
                'paroki_krisma' => $member['paroki_krisma'] ?? null,
                'tgl_perkawinan' => $member['tgl_perkawinan'] ?? null,
                'paroki_perkawinan' => $member['paroki_perkawinan'] ?? null,
                'nama_pasangan' => $member['nama_pasangan'] ?? null,
                'status_perkawinan_kanonik' => $member['status_perkawinan_kanonik'] ?? null,
                'peristiwa_lain' => $member['peristiwa_lain'] ?? null,
                'no_surat_peristiwa' => $member['no_surat_peristiwa'] ?? null,
                'status_panggilan' => $member['status_panggilan'] ?? 'Awam',
                'nama_ordo_kongregasi' => $member['nama_ordo_kongregasi'] ?? null,
                'tahap_panggilan' => $member['tahap_panggilan'] ?? null,
                'tempat_tugas_biara' => $member['tempat_tugas_biara'] ?? null,
                'tgl_tahbisan_kaul' => $member['tgl_tahbisan_kaul'] ?? null,
                'status_aktif' => 1,
                'status_umat' => 'Aktif',
                'handphone' => $member['handphone'] ?? null,
                'email' => $member['email'] ?? null,
                'updated_by' => auth()->id(),
            ];
            $umat = null;
            if (!empty($member['id'])) {
                $umat = \App\Models\Umat::where('id', $member['id'])->where('kk_id', $kk->id)->first();
            }
            if (!$umat && $nik) {
                $umat = \App\Models\Umat::where('nik', $nik)->where('kk_id', $kk->id)->first();
            }
            if (!$umat && $nik && \App\Models\Umat::where('nik', $nik)->where('kk_id', '!=', $kk->id)->exists()) {
                continue;
            }
            $cleanPayload = array_intersect_key($payload, array_flip($validColumns));
            if ($umat) {
                $umat->update($cleanPayload);
            } else {
                if (in_array('created_by', $validColumns, true)) {
                    $cleanPayload['created_by'] = auth()->id();
                }
                $umat = \App\Models\Umat::create($cleanPayload);
            }
            $keepIds[] = $umat->id;
        }
        if (!empty($keepIds)) {
            \App\Models\Umat::where('kk_id', $kk->id)->whereNotIn('id', $keepIds)->update([
                'kk_id' => null,
                'tanggal_keluar_dari_kk' => now(),
                'updated_by' => auth()->id(),
            ]);
        }
    }
    private function storeModuleUploadedFile(string $slug, string $fileKey, $uploadedFile): string
    {
        $extension = strtolower($uploadedFile->getClientOriginalExtension() ?: '');
        $mime = strtolower($uploadedFile->getMimeType() ?: '');
        $folder = in_array($slug, ['riwayat-pastor', 'riwayat_pastor_paroki', 'master-pastor'], true)
            ? 'uploads/pastor'
            : ($slug === 'user' ? 'uploads/users' : ($slug === 'paroki' ? 'uploads/paroki' : 'uploads/' . str_replace('-', '_', $slug)));
        $imageExt = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        // Only ever store non-executable document types. Anything that could
        // be interpreted as code (php, phtml, pht, html, js, svg, etc.) is rejected.
        $docExt = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt', 'csv', 'zip', 'ppt', 'pptx'];
        if (Str::startsWith($mime, 'image/') || in_array($extension, $imageExt, true)) {
            return \App\Services\ImageOptimizer::optimizeAndSave(
                $uploadedFile,
                $folder,
                800,
                800,
                85
            );
        }
        if (!in_array($extension, $docExt, true)) {
            throw new \Illuminate\Http\Exceptions\PostTooLargeException(
                'Tipe file tidak diizinkan. Hanya gambar (jpg/png/webp/gif) dan dokumen (pdf/doc/xls/csv/zip) yang diperbolehkan.'
            );
        }
        $destinationPath = public_path($folder);
        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0755, true);
        }
        $safeName = time() . '_' . Str::random(12) . '.' . $extension;
        $uploadedFile->move($destinationPath, $safeName);
        return trim($folder, '/') . '/' . $safeName;
    }
    private function generateKontenExcerpt(string $content, int $limit = 180): string
    {
        return $this->cleanKontenExcerpt($content, '', $limit);
    }
    private function cleanKontenExcerpt(string $excerpt, string $fallbackContent = '', int $limit = 180): string
    {
        $text = html_entity_decode(html_entity_decode(strip_tags($excerpt), ENT_QUOTES, 'UTF-8'), ENT_QUOTES, 'UTF-8');
        $text = preg_replace('/\[[^\]]+\]/', '', $text);
        $text = preg_replace('/\s+/', ' ', (string) $text);
        $text = trim((string) $text);
        if ($text === '' && $fallbackContent !== '') {
            return $this->generateKontenExcerpt($fallbackContent, $limit);
        }
        if (mb_strlen($text) <= $limit) {
            return $text;
        }
        $trimmed = mb_substr($text, 0, $limit);
        $lastSpace = mb_strrpos($trimmed, ' ');
        if ($lastSpace !== false && $lastSpace > 50) {
            $trimmed = mb_substr($trimmed, 0, $lastSpace);
        }
        return rtrim($trimmed) . '...';
    }
    /**
     * Lightweight audit trail. Persists an entry into security_logs for
     * sensitive module mutations (keuangan, user, role, etc.) so that
     * financial and administrative changes are traceable.
     */
    private function logAudit(string $event, string $slug, $id, array $payload = []): void
    {
        try {
            if (!\Illuminate\Support\Facades\Schema::hasTable('security_logs')) {
                return;
            }
            $safe = [];
            foreach ($payload as $k => $v) {
                if (in_array($k, ['password', 'remember_token'], true)) {
                    continue;
                }
                $safe[$k] = is_scalar($v) ? $v : null;
            }
            \Illuminate\Support\Facades\DB::table('security_logs')->insert([
                'ip_address' => request()->ip(),
                'user_id' => auth()->id(),
                'username' => auth()->user()?->username ?? auth()->user()?->email ?? 'system',
                'event_type' => $event,
                'user_agent' => substr((string) request()->userAgent(), 0, 255),
                'status' => 'SUCCESS',
                'details' => 'Modul: ' . $slug . ' #' . ($id ?? '-') . ' | ' . json_encode($safe, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $e) {}
    }
    public function resetUserPassword(Request $request, $id)
    {
        $user = \App\Models\User::findOrFail($id);
        if (auth()->id() && (int) auth()->id() === (int) $user->getKey()) {
            return back()->with('error', 'Anda tidak dapat mereset password akun Anda sendiri dari sini.');
        }
        $password = $request->input('password') ?: 'SIPAROKI' . now()->format('Y');
        $user->forceFill([
            'password' => \Illuminate\Support\Facades\Hash::make($password),
            'updated_at' => now(),
        ])->save();
        $this->logAudit('RESET_PASSWORD', 'user', $user->getKey());
        return back()->with('success', "Password {$user->nama_lengkap} berhasil direset. Password baru: {$password}");
    }
    public function toggleUserStatus(Request $request, $id)
    {
        $user = \App\Models\User::findOrFail($id);
        if (auth()->id() && (int) auth()->id() === (int) $user->getKey()) {
            return back()->with('error', 'Akun yang sedang digunakan tidak dapat dinonaktifkan.');
        }
        $user->forceFill([
            'status' => $user->status ? 0 : 1,
            'updated_at' => now(),
        ])->save();
        return back()->with('success', 'Status pengguna berhasil diperbarui.');
    }
    private function normalizeUserPayload(array $data, bool $isCreate): array
    {
        if (isset($data['handphone']) && !isset($data['no_hp'])) {
            $data['no_hp'] = $data['handphone'];
        }
        // Ensure email is never null/empty on create to prevent Integrity Constraint Violation
        if (empty($data['email']) || $data['email'] === 'null' || trim((string)$data['email']) === '') {
            if ($isCreate) {
                $baseEmail = !empty($data['username']) ? Str::slug($data['username']) : (!empty($data['nama_lengkap']) ? Str::slug($data['nama_lengkap']) : 'user');
                $genEmail = strtolower($baseEmail) . '@siparoki.local';
                $counter = 1;
                while (\App\Models\User::where('email', $genEmail)->exists()) {
                    $genEmail = strtolower($baseEmail) . $counter . '@siparoki.local';
                    $counter++;
                }
                $data['email'] = $genEmail;
            } else {
                unset($data['email']);
            }
        }
        // Ensure username is never null/empty on create
        if (empty($data['username']) || $data['username'] === 'null' || trim((string)$data['username']) === '') {
            if ($isCreate) {
                $baseUser = !empty($data['nama_lengkap']) ? Str::slug($data['nama_lengkap'], '') : 'user';
                $genUser = strtolower($baseUser);
                $counter = 1;
                while (\App\Models\User::where('username', $genUser)->exists()) {
                    $genUser = strtolower($baseUser) . $counter;
                    $counter++;
                }
                $data['username'] = $genUser;
            } else {
                unset($data['username']);
            }
        }
        // Compatibility between name and nama_lengkap
        if (empty($data['name']) && !empty($data['nama_lengkap'])) {
            $data['name'] = $data['nama_lengkap'];
        } elseif (empty($data['nama_lengkap']) && !empty($data['name'])) {
            $data['nama_lengkap'] = $data['name'];
        }
        if (array_key_exists('status', $data)) {
            $data['status'] = in_array((string) $data['status'], ['1', 'true', 'Aktif', 'aktif'], true) ? 1 : 0;
        } elseif ($isCreate) {
            $data['status'] = 1;
        }
        if (isset($data['maintenance_access'])) {
            $data['maintenance_access'] = in_array((string) $data['maintenance_access'], ['1', 'true', 'Ya', 'ya'], true) ? 'Ya' : 'Tidak';
        } elseif ($isCreate) {
            $data['maintenance_access'] = 'Tidak';
        }
        if (empty($data['password'])) {
            if ($isCreate) {
                $data['password'] = \Illuminate\Support\Facades\Hash::make('SIPAROKI' . now()->format('Y'));
            } else {
                unset($data['password']);
            }
        } else {
            $data['password'] = \Illuminate\Support\Facades\Hash::make($data['password']);
        }
        unset($data['handphone']);
        return $data;
    }
    public function exportModule(Request $request, string $slug, string $format)
    {
        if ($slug === 'statistik' || $slug === 'demografi') {
            return $this->exportStatistik($request, $format);
        }
        if (in_array($slug, ['kk-katolik', 'kk', 'keluarga'], true)) {
            $this->ensureKkKatolikColumns();
        }
        $moduleMap = $this->getModuleMap();
        if (!isset($moduleMap[$slug])) {
            abort(404);
        }
        $config = $moduleMap[$slug];

                                            $sheet->setAutoFilter("A4:{$lastColumn}{$lastRow}");
                                            $sheet->getRowDimension(1)->setRowHeight(30);
                                            $sheet->getRowDimension(2)->setRowHeight(22);
                                            $sheet->getRowDimension(4)->setRowHeight(26);
                                            $sheet->getColumnDimension('A')->setWidth(7);
                                            $sheet->getStyle("A1:{$lastColumn}1")->applyFromArray([
                                                'font' => ['bold' => true, 'size' => 15, 'color' => ['rgb' => 'FFFFFF']],
                                                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '1B365D']],
                                                'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                                            ]);
                                            $sheet->getStyle("A2:{$lastColumn}2")->applyFromArray([
                                                'font' => ['italic' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
                                                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '2563EB']],
                                                'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                                            ]);
                                            $sheet->getStyle("A4:{$lastColumn}4")->applyFromArray([
                                                'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
                                                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '0F172A']],
                                                'alignment' => ['horizontal' => 'center', 'vertical' => 'center', 'wrapText' => true],
                                            ]);
                                            $sheet->getStyle("A4:{$lastColumn}{$lastRow}")->applyFromArray([
                                                'borders' => [
                                                    'allBorders' => [
                                                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                                        'color' => ['rgb' => 'CBD5E1'],
                                                    ],
                                                ],
                                                'alignment' => ['vertical' => 'center', 'wrapText' => true],
                                            ]);
                                            // Baris Contoh Isian Berwarna Kuning Pastel
                                            $sheet->getStyle("A5:{$lastColumn}5")->applyFromArray([
                                                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'FEF3C7']],
                                                'font' => ['italic' => true],
                                            ]);
                                            $sheet->getStyle("A5:A{$lastRow}")->applyFromArray([
                                                'alignment' => ['horizontal' => 'center'],
                                                'font' => ['bold' => true, 'color' => ['rgb' => '475569']],
                                            ]);
                                        },
                                    ];

                        array_merge(['No'], $this->headings),
                    ];
                    foreach ($this->rows as $index => $row) {
                        $body[] = array_merge([$index + 1], array_values($row));
                    }
                    if (empty($this->rows)) {
                        $body[] = array_merge([''], array_fill(0, count($this->headings), ''));
                    }
                    return $body;
                }
                public function title(): string
                {
                    return \Illuminate\Support\Str::limit(preg_replace('/[\\\\\\/\\?\\*\\[\\]\\:]+/', '', $this->title), 31, '');
                }
                public function registerEvents(): array
                {
                    return [
                        \Maatwebsite\Excel\Events\AfterSheet::class => function (\Maatwebsite\Excel\Events\AfterSheet $event) {
                            $sheet = $event->sheet->getDelegate();
                            $lastColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($this->headings) + 1);
                            $lastRow = max(5, count($this->rows) + 4);
                            $sheet->mergeCells("A1:{$lastColumn}1");
                            $sheet->mergeCells("A2:{$lastColumn}2");
                            $sheet->freezePane('A5');
                            $sheet->setAutoFilter("A4:{$lastColumn}{$lastRow}");
                            $sheet->getRowDimension(1)->setRowHeight(28);
                            $sheet->getRowDimension(2)->setRowHeight(22);
                            $sheet->getRowDimension(4)->setRowHeight(24);
                            $sheet->getColumnDimension('A')->setWidth(7);
                            $sheet->getStyle("A1:{$lastColumn}1")->applyFromArray([
                                'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => 'FFFFFF']],
                                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '1B365D']],
                                'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                            ]);
                            $sheet->getStyle("A2:{$lastColumn}2")->applyFromArray([
                                'font' => ['italic' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
                                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '2F5597']],
                                'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                            ]);
                            $sheet->getStyle("A4:{$lastColumn}4")->applyFromArray([
                                'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
                                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '203864']],
                                'alignment' => ['horizontal' => 'center', 'vertical' => 'center', 'wrapText' => true],
                            ]);
                            $sheet->getStyle("A4:{$lastColumn}{$lastRow}")->applyFromArray([
                                'borders' => [
                                    'allBorders' => [
                                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                        'color' => ['rgb' => 'B0C4DE'],
                                    ],
                                ],
                                'alignment' => ['vertical' => 'center', 'wrapText' => true],
                            ]);
                            for ($row = 5; $row <= $lastRow; $row++) {
                                $fill = $row % 2 === 0 ? 'F8FAFC' : 'FFFFFF';
                                $sheet->getStyle("A{$row}:{$lastColumn}{$row}")->applyFromArray([
                                    'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => $fill]],
                                ]);
                            }
                            $sheet->getStyle("A5:A{$lastRow}")->applyFromArray([
                                'alignment' => ['horizontal' => 'center'],
                                'font' => ['bold' => true, 'color' => ['rgb' => '475569']],
                            ]);
                        },
                    ];

        } catch (\Throwable $e) {
            return null;
        }
        return null;
    }
    private function territoryModuleSlugs(): array
    {
        return [
            'keuskupan',
            'dekenat',
            'kevikepan',
            'paroki',
            'kuasi-paroki',
            'kapela',
            'stasi',
            'wilayah',
            'kub',
            'provinsi',
            'kabupaten',
            'kecamatan',
            'desa-kelurahan',
            'kk-katolik',
            'kk',
            'keluarga',
        ];
    }
    private function moduleTemplateRows(string $slug, array $headings): array
    {
        $examples = [
            'Kode' => strtoupper(Str::slug($slug, '-')) . '-001',
            'Nama Keuskupan' => 'Keuskupan Contoh',
            'Nama Kevikepan / Dekenat' => 'Kevikepan Contoh',
            'Nama Paroki' => 'Paroki Contoh',
            'Nama Kuasi Paroki' => 'Kuasi Paroki Contoh',
            'Nama Stasi / Kapela' => 'Stasi Contoh',
            'Nama Wilayah' => 'Wilayah Contoh',
            'Nama KUB' => 'KUB Contoh',
            'Nama Provinsi' => 'Nusa Tenggara Timur',
            'Nama Kabupaten / Kota' => 'Kota Kupang',
            'Nama Kecamatan' => 'Oebobo',
            'Nama Desa / Kelurahan' => 'Kelurahan Contoh',
            'Nama Latin' => 'Archidioecesis Exemplum',
            'Nama Uskup' => 'Mgr. Contoh',
            'Keuskupan' => 'Keuskupan Agung Kupang',
            'Kevikepan / Dekenat' => 'Kevikepan Contoh',
            'Paroki Induk' => 'Paroki Induk Contoh',
            'Paroki' => 'Paroki Contoh',
            'Provinsi' => 'Nusa Tenggara Timur',
            'Kabupaten / Kota' => 'Kota Kupang',
            'Kecamatan' => 'Oebobo',
            'Tipe' => 'Kota',
            'Pelindung' => 'Santo Pelindung',
            'Pastor Paroki' => 'Rm. Contoh',
            'Pastor Administrator' => 'Rm. Administrator',
            'Penanggung Jawab' => 'Nama Penanggung Jawab',
            'Ketua Wilayah' => 'Nama Ketua Wilayah',
            'Kontak' => '081234567890',
            'Alamat' => 'Alamat lengkap',
            'Lokasi / Alamat' => 'Alamat lengkap',
            'Status' => 'Aktif',
            'No KK Paroki' => 'KK-BENLUTU-001-2026',
            'No KK Katolik' => 'KK-BENLUTU-001-2026',
            'No KK Sipil' => '5302010101010001',
            'No KK Dukcapil' => '5302010101010001',
            'NIK Kepala Keluarga' => '5302010101010001',
            'Nama Lengkap Kepala Keluarga' => 'Yohanes Markus Bria',
            'Nama Baptis Kepala Keluarga' => 'Yohanes Markus',
            'Nama Lahir Kepala Keluarga' => 'Yohanes Markus Bria',
            'Nama Pasangan' => 'Maria Goreti Taolin',
            'Stasi / Kapela' => 'Kapela St. Fransiskus',
            'Wilayah Pastoral' => 'Wilayah I - St. Petrus',
            'Lingkungan' => 'Lingkungan St. Yohanes',
            'KUB / KBG' => 'KUB Sta. Maria',
            'Alamat Domisili' => 'Jl. Jurusan Soe - Kefa KM 12, RT 001 / RW 002',
            'RT' => '001',
            'RW' => '002',
            'Desa / Kelurahan' => 'Desa Benlutu',
            'Kecamatan' => 'Kecamatan Batu Putih',
            'Kabupaten / Kota' => 'Kabupaten Timor Tengah Selatan',
            'No Telepon / WA' => '081234567890',
            'Kontak / HP' => '081234567890',
            'Email' => 'keluarga.bria@gmail.com',
            'Status Kepemilikan Rumah' => 'Milik Sendiri',
            'Kategori Ekonomi Pastoral' => 'Sejahtera / Mandiri',
            'Bantuan Pastoral Khusus' => '-',
            'Pekerjaan' => 'Petani / Pekebun',
            'Pekerjaan Kepala Keluarga' => 'Petani / Pekebun',
            'Pendidikan' => 'SMA / SMK / Sederajat',
            'Golongan Darah' => 'O',
            'Penghasilan' => 'Rp 1.000.000 - Rp 3.000.000',
            'Status KK' => 'Aktif',
            'Status Verifikasi' => 'Terverifikasi',
        ];
        for ($i = 1; $i <= 2; $i++) {
            $examples += [
                "Anggota {$i} - Hubungan Keluarga" => $i === 1 ? 'Kepala Keluarga' : 'Istri',
                "Anggota {$i} - NIK" => $i === 1 ? '5302010101010001' : '5302010101010002',
                "Anggota {$i} - Nama Lengkap Sipil" => $i === 1 ? 'Yohanes Markus Bria' : 'Maria Goreti Taolin',
                "Anggota {$i} - Nama Baptis Santo/Santa" => $i === 1 ? 'Yohanes Markus' : 'Maria Goreti',
                "Anggota {$i} - Jenis Kelamin" => $i === 1 ? 'Laki-Laki' : 'Perempuan',
                "Anggota {$i} - Tempat Lahir" => 'Benlutu',
                "Anggota {$i} - Tanggal Lahir" => $i === 1 ? '1980-01-15' : '1984-05-20',
                "Anggota {$i} - Golongan Darah" => 'O',
                "Anggota {$i} - Agama Asal" => 'Katolik sejak lahir',
                "Anggota {$i} - Pendidikan" => 'SMA / SMK / Sederajat',
                "Anggota {$i} - Pekerjaan" => $i === 1 ? 'Petani / Pekebun' : 'Ibu Rumah Tangga',
                "Anggota {$i} - Bidang Keahlian / Talenta Paroki" => 'Koor / Liturgi',
                "Anggota {$i} - Disabilitas/Kebutuhan Khusus" => 'Tidak Ada',
                "Anggota {$i} - Status Baptis" => 'Sudah Baptis',
                "Anggota {$i} - Jenis Penerimaan Baptis" => 'Baptis Bayi (Infantis)',
                "Anggota {$i} - Tanggal Baptis" => $i === 1 ? '1980-02-20' : '1984-06-25',
                "Anggota {$i} - Paroki Tempat Baptis" => 'Paroki St. Vinsensius a Paulo Benlutu',
                "Anggota {$i} - Pastor Pembaptis" => 'RD. Contoh Pastor',
                "Anggota {$i} - Nama Wali Baptis" => 'Wali Baptis Contoh',
                "Anggota {$i} - Buku Baptis Vol" => 'I',
                "Anggota {$i} - Buku Baptis Hal" => '12',
                "Anggota {$i} - Buku Baptis No" => '34',
                "Anggota {$i} - Tanggal Krisma" => '',
                "Anggota {$i} - Paroki Krisma" => '',
                "Anggota {$i} - Tanggal Perkawinan" => $i <= 2 ? '2006-07-10' : '',
                "Anggota {$i} - Paroki Perkawinan" => $i <= 2 ? 'Paroki St. Vinsensius a Paulo Benlutu' : '',
                "Anggota {$i} - Nama Pasangan" => $i === 1 ? 'Maria Goreti Taolin' : 'Yohanes Markus Bria',
                "Anggota {$i} - Status Perkawinan Kanonik" => 'Katolik Organik',
                "Anggota {$i} - Peristiwa Lain" => '',
                "Anggota {$i} - No Surat Peristiwa" => '',
            ];
        }
        return [
            array_map(fn ($heading) => $examples[$heading] ?? '', $headings),
        ];
    }
    private function moduleExportPayload(Request $request, string $slug, array $config): array
    {
        $modelClass = $config['model'];
        $query = $this->moduleExportQuery($slug, $modelClass);
        $modelInstance = new $modelClass;
        $tableColumns = $this->schemaColumns($modelInstance->getTable());
        if (($slug === 'kapela' || $slug === 'stasi') && in_array('paroki_id', $tableColumns, true)) {
            $defaultParokiId = $this->defaultParokiIdFromProfile();
            if ($defaultParokiId) {
                $query->where('paroki_id', $defaultParokiId);
            }
        }
        $authUser = auth()->user();
        $userRoleSlug = strtolower(preg_replace('/[^a-z0-9]/', '', $authUser?->role?->slug ?? $authUser?->role?->nama_role ?? ''));
        if (str_contains($userRoleSlug, 'wilayah') && !empty($authUser?->wilayah_id)) {
            if ($slug === 'wilayah' && in_array('id', $tableColumns, true)) {
                $query->where('id', $authUser->wilayah_id);
            } elseif (in_array('wilayah_id', $tableColumns, true)) {
                $query->where('wilayah_id', $authUser->wilayah_id);

        }
        if (!empty($col['relation'])) {
            $relation = $item->{$col['relation']} ?? null;
            if ($relation) {
                return $relation->{$col['relationKey']} ?? (isset($col['altRelationKey']) ? ($relation->{$col['altRelationKey']} ?? null) : null) ?? '-';

            }
            return '-';
        }
        return $item->{$col['key']} ?? (isset($col['altKey']) ? ($item->{$col['altKey']} ?? null) : null) ?? '-';
    }
    private function formatExportStatus($value): string
    {
        if ($value === 'Y' || $value === 1 || $value === true) {
            return 'Aktif';
        }
        if ($value === 'N' || $value === 0 || $value === false) {
            return 'Tidak Aktif';
        }
        return $value ?: 'Aktif';
    }
    private function normalizeImportHeading(string $heading): string
    {
        return Str::of($heading)->lower()->ascii()->replaceMatches('/[^a-z0-9]+/', '_')->trim('_')->toString();

    }
    private function moduleImportFieldMap(string $slug, array $config, array $validColumns): array
    {
        $map = [];
        $blockedColumns = ['created_at', 'updated_at', 'deleted_at', 'created_by', 'updated_by', 'deleted_by', 'delete_reason', 'is_deleted'];
        foreach ($validColumns as $column) {
            if (!in_array($column, $blockedColumns, true)) {
                $map[$this->normalizeImportHeading($column)] = $column;
            }
        }
        foreach ($config['columns'] as $col) {
            $field = null;
            if (isset($col['altKey']) && in_array($col['altKey'], $validColumns, true)) {
                $field = $col['altKey'];
            }
            if (in_array($col['key'], $validColumns, true)) {
                $field = $col['key'];
            }
            if (!$field && !empty($col['relation'])) {
                $field = $this->relationFieldForImport($col['relation']);
            }
            if ($field && in_array($field, $validColumns, true)) {

                $map[$this->normalizeImportHeading($col['label'])] = $field;
            }
        }
        foreach (['kode' => 'kode_', 'nama' => 'nama_'] as $alias => $prefix) {
            $field = collect($validColumns)->first(fn ($column) => str_starts_with($column, $prefix));
            if ($field) {
                $map[$alias] = $field;
            }
        }
        $aliasMap = [
            'kontak' => ['telepon', 'no_telp', 'no_hp', 'kontak'],
            'telp' => ['telepon', 'no_telp', 'no_hp'],
            'nomor_telepon' => ['telepon', 'no_telp', 'no_hp'],
            'email' => ['email', 'Email'],
            'website' => ['website', 'Website'],
            'alamat' => ['alamat', 'AlamatKuasiParoki', 'lokasi'],
            'lokasi' => ['lokasi', 'alamat', 'AlamatKuasiParoki'],
            'keterangan' => ['keterangan', 'Keterangan'],
            'catatan' => ['keterangan', 'Keterangan'],
            'status' => ['status', 'status_aktif', 'StatusAktif'],
            'status_aktif' => ['status_aktif', 'StatusAktif', 'status'],
            'nama_latin' => ['nama_latin', 'nama_keuskupan_latin'],
            'uskup' => ['nama_uskup'],
            'nama_uskup' => ['nama_uskup'],
            'paroki_induk' => ['paroki_id'],
            'paroki' => ['paroki_id'],
            'keuskupan' => ['keuskupan_id'],
            'provinsi' => ['provinsi_id'],
            'kabupaten' => ['kabupaten_id'],
            'kabupaten_kota' => ['kabupaten_id'],
            'kecamatan' => ['kecamatan_id'],
            'desa_kelurahan' => ['desa_id'],
            'desa' => ['desa_id'],
            'kelurahan' => ['desa_id'],
            'wilayah' => ['wilayah_id'],
            'stasi_kapela' => ['kapela_id'],
            'stasi' => ['kapela_id'],
            'kapela' => ['kapela_id'],
            'wilayah_pastoral' => ['wilayah_id'],
            'wilayah_pelayanan' => ['wilayah_id'],
            'lingkungan' => ['lingkungan_id'],
            'kub_kbg' => ['kub_id'],
            'kbg' => ['kub_id'],
            'kub' => ['kub_id'],
            'no_kk' => ['no_kk_kw', 'no_kk_dukcapil'],
            'no_kk_paroki' => ['no_kk_kw'],
            'no_kk_katolik' => ['no_kk_kw'],
            'nomor_kk_katolik' => ['no_kk_kw'],
            'no_kk_kw' => ['no_kk_kw'],
            'no_kk_sipil' => ['no_kk_dukcapil'],
            'no_kk_dukcapil' => ['no_kk_dukcapil'],
            'nomor_kk_dukcapil' => ['no_kk_dukcapil'],
            'nik' => ['nik_pemilik'],
            'nik_kepala_keluarga' => ['nik_pemilik'],
            'nik_pemilik' => ['nik_pemilik'],
            'kepala_keluarga' => ['nama_lahir_pemilik', 'nama_baptis_pemilik'],
            'nama_lengkap_kepala_keluarga' => ['nama_lahir_pemilik', 'nama_baptis_pemilik'],
            'nama_kepala_keluarga' => ['nama_lahir_pemilik', 'nama_baptis_pemilik'],
            'nama_baptis_kepala_keluarga' => ['nama_baptis_pemilik'],
            'nama_lahir_kepala_keluarga' => ['nama_lahir_pemilik'],
            'nama_baptis_pemilik' => ['nama_baptis_pemilik'],
            'nama_lahir_pemilik' => ['nama_lahir_pemilik'],
            'nama_pasangan' => ['nama_pasangan'],
            'alamat_domisili' => ['alamat_sekarang'],
            'alamat_sekarang' => ['alamat_sekarang'],
            'rt' => ['rt'],
            'rw' => ['rw'],
            'hp' => ['handphone'],
            'no_hp' => ['handphone'],
            'handphone' => ['handphone'],
            'no_telepon_wa' => ['handphone'],
            'telepon_wa' => ['handphone'],
            'status_kepemilikan_rumah' => ['status_kepemilikan_rumah'],
            'kepemilikan_rumah' => ['status_kepemilikan_rumah'],
            'kategori_ekonomi_pastoral' => ['kategori_ekonomi'],
            'kategori_ekonomi' => ['kategori_ekonomi'],
            'bantuan_pastoral_khusus' => ['bantuan_pastoral'],
            'bantuan_pastoral' => ['bantuan_pastoral'],
            'pekerjaan' => ['pekerjaan'],
            'pekerjaan_kepala_keluarga' => ['pekerjaan'],
            'profesi' => ['pekerjaan'],
            'pendidikan' => ['pendidikan'],
            'pendidikan_terakhir' => ['pendidikan'],
            'golongan_darah' => ['golongan_darah'],
            'gol_darah' => ['golongan_darah'],
            'goldar' => ['golongan_darah'],
            'penghasilan' => ['penghasilan'],
            'penghasilan_ekonomi' => ['penghasilan'],
            'status_kk' => ['status_kk'],
            'status_verifikasi' => ['status_verifikasi'],
        ];
        foreach ($aliasMap as $alias => $candidates) {
            $field = collect($candidates)->first(fn ($column) => in_array($column, $validColumns, true));
            if ($field) {
                $map[$alias] = $field;
            }
        }
        return $map;
    }
    private function relationFieldForImport(string $relation): ?string
    {
        return match ($relation) {
            'keuskupan' => 'keuskupan_id',
            'dekenat' => 'dekenat_id',
            'paroki' => 'paroki_id',
            'provinsi' => 'provinsi_id',
            'kabupaten' => 'kabupaten_id',
            'kecamatan' => 'kecamatan_id',

            'desa' => 'desa_id',
            'wilayah' => 'wilayah_id',
            'kapela' => 'kapela_id',
            'kub' => 'kub_id',
            default => null,
        };
    }
    private function mapModuleImportRow(array $row, array $headers, array $validColumns): array
    {
        $payload = [];
        $blockedColumns = ['id', 'id_keuskupan', 'id_paroki', 'id_provinsi', 'id_kabupaten', 'id_kecamatan', 'id_desa', 'created_at', 'updated_at', 'deleted_at'];
        foreach ($row as $column => $value) {
            $field = $headers[$column] ?? null;
            if (!$field || !in_array($field, $validColumns, true) || in_array($field, $blockedColumns, true)) {
                continue;
            }
            $cleanValue = is_string($value) ? trim($value) : $value;
            if ($cleanValue === '') {
                $cleanValue = null;
            }
            if (in_array($field, ['provinsi_id', 'kabupaten_id', 'kecamatan_id', 'desa_id'], true)) {
                $cleanValue = is_numeric($cleanValue) ? (int) $cleanValue : $this->resolveTerritoryRelationId($field, $cleanValue);
            }
            if (in_array($field, ['keuskupan_id', 'dekenat_id', 'paroki_id', 'wilayah_id', 'kapela_id'], true)) {
                $cleanValue = is_numeric($cleanValue) ? (int) $cleanValue : $this->resolveChurchRelationId($field, $cleanValue);
            }
            if ($field === 'kub_id') {
                $cleanValue = is_numeric($cleanValue) ? (int) $cleanValue : $this->resolveChurchRelationId($field, $cleanValue);
            }
            if (in_array($field, ['no_kk_kw', 'no_kk_dukcapil', 'nik_pemilik', 'handphone'], true) && $cleanValue !== null) {
                $cleanValue = preg_replace('/\.0$/', '', trim((string) $cleanValue));
            }
            if ($field === 'status') {
                $statusText = Str::of((string) $cleanValue)->lower()->ascii()->replace(' ', '')->toString();
                $cleanValue = in_array($statusText, ['tidakaktif', 'nonaktif', 'nonactive', 'inactive', 'n', '0'], true)
                    ? 'Tidak Aktif'
                    : 'Aktif';
            }
            if ($field === 'StatusAktif' || $field === 'status_aktif') {
                $statusText = Str::of((string) $cleanValue)->lower()->ascii()->replace(' ', '')->toString();
                $cleanValue = in_array($statusText, ['tidakaktif', 'nonaktif', 'nonactive', 'inactive', 'n', '0'], true) ? 'N' : 'Y';
            }
            if ($field === 'status_verifikasi') {
                $statusText = Str::of((string) $cleanValue)->lower()->ascii()->replaceMatches('/[^a-z0-9]+/', '')->toString();
                $cleanValue = match (true) {
                    in_array($statusText, ['terverifikasi', 'verified', 'valid', 'sudah'], true) => 'Terverifikasi',
                    in_array($statusText, ['ditolak', 'tolak', 'reject', 'rejected'], true) => 'Ditolak',
                    default => 'Belum',
                };
            }
            if ($field === 'status_kk') {
                $statusText = Str::of((string) $cleanValue)->lower()->ascii()->replaceMatches('/[^a-z0-9]+/', '')->toString();
                $cleanValue = match (true) {
                    in_array($statusText, ['pindahkub'], true) => 'Pindah KUB',
                    in_array($statusText, ['pindahwilayah'], true) => 'Pindah Wilayah',
                    in_array($statusText, ['pindahparoki'], true) => 'Pindah Paroki',
                    in_array($statusText, ['pecahkk'], true) => 'Pecah KK',
                    in_array($statusText, ['tidakaktif', 'nonaktif', 'inactive', '0'], true) => 'Tidak Aktif',
                    default => 'Aktif',
                };
            }
            $payload[$field] = $cleanValue;
        }
        if (empty($payload['status']) && in_array('status', $validColumns, true)) {
            $payload['status'] = 'Aktif';
        }
        if (empty($payload['status_kk']) && in_array('status_kk', $validColumns, true)) {
            $payload['status_kk'] = 'Aktif';
        }
        if (empty($payload['status_verifikasi']) && in_array('status_verifikasi', $validColumns, true)) {
            $payload['status_verifikasi'] = 'Belum';
        }
        if (in_array('nama_baptis_pemilik', $validColumns, true) && empty($payload['nama_baptis_pemilik'])) {
            $payload['nama_baptis_pemilik'] = $payload['nama_lahir_pemilik'] ?? '-';
        }
        if (in_array('nama_lahir_pemilik', $validColumns, true) && empty($payload['nama_lahir_pemilik'])) {
            $payload['nama_lahir_pemilik'] = $payload['nama_baptis_pemilik'] ?? '-';
        }
        if (in_array('is_deleted', $validColumns, true)) {
            $payload['is_deleted'] = 0;
        }
        return $payload;
    }
    private function moduleImportLookup(string $slug, array $payload, array $validColumns): array
    {
        if (in_array($slug, ['kk-katolik', 'kk', 'keluarga'], true)) {
            foreach (['no_kk_kw', 'no_kk_dukcapil', 'nik_pemilik'] as $column) {
                if (in_array($column, $validColumns, true) && !empty($payload[$column])) {
                    return [$column => trim((string) $payload[$column])];
                }
            }
            return [];
        }
        $codeCandidates = [
            'keuskupan' => ['kode_keuskupan'],
            'dekenat' => ['kode_kevikepan', 'kode_dekenat'],
            'kevikepan' => ['kode_kevikepan', 'kode_dekenat'],
            'paroki' => ['kode_paroki'],
            'kuasi-paroki' => ['KodeKuasiParoki'],
            'kapela' => ['kode_kapela'],
            'stasi' => ['kode_kapela'],
            'wilayah' => ['kode_wilayah'],
            'kub' => ['kode_kub'],
            'provinsi' => ['kode_provinsi'],
            'kabupaten' => ['kode_kabupaten'],
            'kecamatan' => ['kode_kecamatan'],
            'desa-kelurahan' => ['kode_desa'],
        ];
        $nameCandidates = [
            'keuskupan' => ['nama_keuskupan'],
            'dekenat' => ['nama_kevikepan', 'nama_dekenat'],
            'kevikepan' => ['nama_kevikepan', 'nama_dekenat'],
            'paroki' => ['nama_paroki'],
            'kuasi-paroki' => ['NamaKuasiParoki'],
            'kapela' => ['nama_kapela'],
            'stasi' => ['nama_kapela'],
            'wilayah' => ['nama_wilayah'],
            'kub' => ['nama_kub'],
            'provinsi' => ['nama_provinsi'],
            'kabupaten' => ['nama_kabupaten'],
            'kecamatan' => ['nama_kecamatan'],
            'desa-kelurahan' => ['nama_desa'],
        ];
        foreach ($codeCandidates[$slug] ?? [] as $column) {
            if (in_array($column, $validColumns, true) && !empty($payload[$column])) {
                return [$column => $payload[$column]];
            }
        }
        foreach ($nameCandidates[$slug] ?? [] as $column) {
            if (in_array($column, $validColumns, true) && !empty($payload[$column])) {
                return [$column => $payload[$column]];
            }
        }
        return [];
    }
    private function resolveTerritoryRelationId(string $field, $value): ?int
    {
        $value = trim((string) $value);
        if ($value === '' || $value === '-') {
            return null;
        }
        return match ($field) {
            'provinsi_id' => Provinsi::where('nama_provinsi', $value)->orWhere('kode_provinsi', $value)->value('id_provinsi'),
            'kabupaten_id' => Kabupaten::where('nama_kabupaten', $value)->orWhere('kode_kabupaten', $value)->value('id_kabupaten'),
            'kecamatan_id' => Kecamatan::where('nama_kecamatan', $value)->orWhere('kode_kecamatan', $value)->value('id_kecamatan'),
            'desa_id' => DesaKelurahan::where('nama_desa', $value)->orWhere('kode_desa', $value)->value('id_desa'),
            default => null,
        };
    }
    private function resolveChurchRelationId(string $field, $value): ?int
    {
        $value = trim((string) $value);
        if ($value === '' || $value === '-') {
            return null;
        }
        return match ($field) {
            'keuskupan_id' => Keuskupan::where('nama_keuskupan', $value)->orWhere('kode_keuskupan', $value)->value('id_keuskupan'),
            'dekenat_id' => Dekenat::where('nama_kevikepan', $value)->orWhere('nama_dekenat', $value)->orWhere('kode_kevikepan', $value)->orWhere('kode_dekenat', $value)->value('id'),
            'paroki_id' => Paroki::where('nama_paroki', $value)->orWhere('kode_paroki', $value)->value('id_paroki'),
            'wilayah_id' => Wilayah::where('nama_wilayah', $value)->orWhere('kode_wilayah', $value)->value('id'),
            'kapela_id' => Kapela::where('nama_kapela', $value)->orWhere('kode_kapela', $value)->value('id'),
            'kub_id' => Kub::where('nama_kub', $value)->orWhere('kode_kub', $value)->value('id'),
            default => null,
        };
    }
    private function clearFastAccessCache(): void
    {
        foreach ([
            'frontend.common_data',
            'frontend.beranda.jadwal_misa',
            'frontend.beranda.pengumuman',
            'frontend.beranda.galeri',
            'frontend.beranda.artikel',
            'frontend.beranda.stats',
            'frontend.kapela_geojson',
            'global_pengaturan_aplikasi_first',
        ] as $key) {
            Cache::forget($key);
        }
        Cache::forever('global_view_data_version', (int) Cache::get('global_view_data_version', 1) + 1);
    }
    private function normalizeKuasiParokiPayload(array $data, $kuasi = null): array
    {
        $name = $data['nama_kuasi'] ?? $data['NamaKuasiParoki'] ?? $kuasi?->nama_kuasi ?? null;
        $code = $data['kode_kuasi'] ?? $data['KodeKuasiParoki'] ?? $kuasi?->kode_kuasi ?? null;
        $address = $data['lokasi'] ?? $data['AlamatKuasiParoki'] ?? $kuasi?->lokasi ?? null;
        $pastor = $this->resolvePastorDisplayName($data['pastor_administrator'] ?? $data['PastorKuasiParoki'] ?? $kuasi?->pastor_administrator ?? null);
        $status = $data['status'] ?? $kuasi?->status ?? 'Aktif';
        if ($name !== null) {
            $data['NamaKuasiParoki'] = $name;
        }
        if ($code !== null) {
            $data['KodeKuasiParoki'] = $code;
        }
        if ($address !== null) {
            $data['AlamatKuasiParoki'] = $address;
        }
        if ($pastor !== null) {
            $data['PastorKuasiParoki'] = $pastor;
        }
        if (isset($data['keterangan'])) {
            $data['Keterangan'] = $data['keterangan'];
        }
        $data['StatusAktif'] = $this->shouldPromoteKuasiParoki($data) || in_array($status, ['Nonaktif', 'N', 0, '0'], true) ? 'N' : 'Y';
        return $data;
    }
    private function shouldPromoteKuasiParoki(array $data): bool
    {
        $status = strtolower((string) ($data['status'] ?? ''));
        return !empty($data['promote_to_paroki'])
            || str_contains($status, 'paroki')
            || str_contains($status, 'definitif');
    }
    private function promoteKuasiParokiToParoki(\App\Models\KuasiParoki $kuasi, array $data): Paroki
    {
        $kuasi->loadMissing('paroki.dekenat');
        $rawName = trim((string) ($data['NamaKuasiParoki'] ?? $data['nama_kuasi'] ?? $kuasi->nama_kuasi ?? 'Paroki Baru'));
        $cleanName = trim(preg_replace('/^Kuasi\s+Paroki\s+/i', '', $rawName));
        $newParokiName = Str::startsWith(strtolower($cleanName), 'paroki ')
            ? $cleanName
            : 'Paroki ' . $cleanName;
        $oldCode = $data['KodeKuasiParoki'] ?? $data['kode_kuasi'] ?? $kuasi->kode_kuasi;
        $newCode = trim((string) ($data['kode_paroki_baru'] ?? '')) ?: $this->generateParokiCode($oldCode);
        $skNumber = trim((string) ($data['no_sk_elevasi'] ?? ''));
        $promotedAt = now();
        $actor = auth()->user()?->name ?? auth()->user()?->nama_lengkap ?? auth()->user()?->email ?? 'Sistem';
        $parentParoki = $kuasi->paroki;
        $parentParokiLabel = $parentParoki
            ? trim($parentParoki->nama_paroki . ' (ID: ' . $parentParoki->getKey() . ', Kode: ' . ($parentParoki->kode_paroki ?: '-') . ')')
            : '-';
        $dekenatId = $data['dekenat_id'] ?? $kuasi->dekenat_id ?? $parentParoki?->dekenat_id;
        $keuskupanId = $data['keuskupan_id'] ?? $kuasi->keuskupan_id ?? $parentParoki?->keuskupan_id;
        $address = $data['AlamatKuasiParoki'] ?? $data['lokasi'] ?? $kuasi->lokasi;
        $pastor = $this->resolvePastorDisplayName($data['pastor_administrator'] ?? $data['PastorKuasiParoki'] ?? $kuasi->pastor_administrator);
        $historyBlock = implode("\n", array_filter([
            '[ELEVASI-KUASI-PAROKI] ' . $promotedAt->format('Y-m-d H:i:s') . ' WITA',
            'Status: Kuasi Paroki dinaikkan menjadi Paroki definitif.',
            'Nama kuasi asal: ' . $rawName,
            'Kode kuasi asal: ' . ($oldCode ?: '-'),
            'Nama paroki definitif: ' . $newParokiName,
            'Kode paroki definitif: ' . $newCode,
            'Paroki induk saat masih kuasi: ' . $parentParokiLabel,
            'Nomor SK/Dekret: ' . ($skNumber ?: '-'),
            'Pastor administrator terakhir: ' . ($pastor ?: '-'),
            'Diproses oleh: ' . $actor,
        ]));
        $existingParoki = Paroki::where('kode_paroki', $newCode)
            ->orWhere('nama_paroki', $newParokiName)
            ->first();
        $parokiPayload = [
            'keuskupan_id' => $keuskupanId,

            'dekenat_id' => $dekenatId,
            'kode_paroki' => $newCode,
            'nama_paroki' => $newParokiName,
            'pelindung_paroki' => $data['pelindung'] ?? $kuasi->pelindung ?? '',
            'status_paroki' => 'Mandiri',
            'status' => 'Aktif',
            'nama_pastor_paroki_aktif' => $pastor ?: '',
            'alamat' => $address ?: '',
            'provinsi_id' => $data['provinsi_id'] ?? $kuasi->provinsi_id,
            'kabupaten_id' => $data['kabupaten_id'] ?? $kuasi->kabupaten_id,
            'kecamatan_id' => $data['kecamatan_id'] ?? $kuasi->kecamatan_id,
            'desa_id' => $data['desa_id'] ?? $kuasi->desa_id,
            'telepon' => $data['Telepon'] ?? $kuasi->Telepon ?? null,
            'email' => $data['Email'] ?? $kuasi->Email ?? null,
            'website' => $data['Website'] ?? $kuasi->Website ?? null,
            'latitude' => $data['Latitude'] ?? $kuasi->Latitude ?? null,
            'longitude' => $data['Longitude'] ?? $kuasi->Longitude ?? null,
            'keterangan' => trim(($existingParoki?->keterangan ? $existingParoki->keterangan . "\n\n" : '') . $historyBlock),
        ];
        if ($existingParoki) {
            $existingParoki->update($parokiPayload);
            $paroki = $existingParoki;
        } else {
            $paroki = Paroki::create($parokiPayload);
        }
        $kuasiHistory = trim(($kuasi->Keterangan ? $kuasi->Keterangan . "\n\n" : '') . $historyBlock . "\nParoki definitif ID: " . $paroki->getKey());
        $kuasi->forceFill([
            'StatusAktif' => 'N',
            'Keterangan' => $kuasiHistory,
            'UpdatedAt' => now(),
            'UpdatedBy' => auth()->id(),
        ])->save();
        return $paroki;
    }
    private function resolvePastorDisplayName($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (is_numeric($value) && \Illuminate\Support\Facades\Schema::hasTable('master_pastor')) {
            $pastor = \App\Models\MasterPastor::find($value);
            if ($pastor) {
                return trim(implode(' ', array_filter([
                    $pastor->gelar_depan ?? null,
                    $pastor->nama_pastor ?? null,

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

        try {
            $checkTable = \Illuminate\Support\Facades\DB::select("SHOW TABLES LIKE 'riwayat_pastor_paroki'");
            $needsSync = empty($checkTable);
            if (!$needsSync) {
                $count = \Illuminate\Support\Facades\DB::table('riwayat_pastor_paroki')->count();

                $hasDamasus = \Illuminate\Support\Facades\DB::table('riwayat_pastor_paroki')->where('nama_pastor', 'like', '%Damasus%')->exists();
                if ($count < 5 || !$hasDamasus) {
                    $needsSync = true;
                }
            }
            if ($needsSync) {
                try {
                    \Illuminate\Support\Facades\DB::statement("DROP TABLE IF EXISTS riwayat_pastor_paroki");
                    \Illuminate\Support\Facades\DB::statement("CREATE TABLE riwayat_pastor_paroki LIKE parokibenlutuci31.riwayat_pastor_paroki");
                    \Illuminate\Support\Facades\DB::statement("INSERT INTO riwayat_pastor_paroki SELECT * FROM parokibenlutuci31.riwayat_pastor_paroki");
                } catch (\Throwable $ex) {
                    \Illuminate\Support\Facades\Schema::create('riwayat_pastor_paroki', function ($table) {
                        $table->increments('id');
                        $table->unsignedInteger('paroki_id')->nullable();
                        $table->string('nama_pastor', 150);
                        $table->string('gelar', 50)->nullable();
                        $table->string('jabatan', 100)->nullable();
                        $table->string('periode_mulai', 50)->nullable();
                        $table->string('periode_selesai', 50)->nullable();
                        $table->string('tahun_mulai', 10)->nullable();
                        $table->string('tahun_selesai', 10)->nullable();
                        $table->string('foto', 255)->nullable();
                        $table->string('status', 50)->default('Aktif');
                        $table->string('status_pelayanan', 50)->nullable();
                        $table->integer('urutan')->default(1);
                        $table->text('keterangan')->nullable();
                        $table->text('karya_pelayanan')->nullable();
                    });
                    \Illuminate\Support\Facades\DB::table('riwayat_pastor_paroki')->insert([
                        ['nama_pastor' => 'P. Damasus Sumardi', 'jabatan' => 'Pastor Paroki', 'periode_mulai' => '2002', 'periode_selesai' => '2007', 'tahun_mulai' => '2002', 'tahun_selesai' => '2007', 'status' => 'Purna Tugas', 'status_pelayanan' => 'Purna Tugas', 'urutan' => 1, 'foto' => 'uploads/pastor/damasus.jpg'],
                        ['nama_pastor' => 'P. Siprianus Asa', 'jabatan' => 'Pastor Paroki', 'periode_mulai' => '2007', 'periode_selesai' => '2010', 'tahun_mulai' => '2007', 'tahun_selesai' => '2010', 'status' => 'Purna Tugas', 'status_pelayanan' => 'Purna Tugas', 'urutan' => 2, 'foto' => null],
                        ['nama_pastor' => 'P. Walburga Poca', 'jabatan' => 'Pastor Paroki', 'periode_mulai' => '2010', 'periode_selesai' => '2012', 'tahun_mulai' => '2010', 'tahun_selesai' => '2012', 'status' => 'Purna Tugas', 'status_pelayanan' => 'Purna Tugas', 'urutan' => 3, 'foto' => null],
                        ['nama_pastor' => 'P. Damianus Lamak Tasaeb', 'jabatan' => 'Pastor Paroki', 'periode_mulai' => '2012', 'periode_selesai' => '2016', 'tahun_mulai' => '2012', 'tahun_selesai' => '2016', 'status' => 'Purna Tugas', 'status_pelayanan' => 'Purna Tugas', 'urutan' => 4, 'foto' => null],
                        ['nama_pastor' => 'RD. Herman Hillers Penga', 'jabatan' => 'Pastor Paroki', 'periode_mulai' => '2026', 'periode_selesai' => 'Sekarang', 'tahun_mulai' => '2026', 'tahun_selesai' => 'Sekarang', 'status' => 'Aktif', 'status_pelayanan' => 'Aktif Melayani', 'urutan' => 5, 'foto' => null],
                    ]);
                }
            }
            if (\Illuminate\Support\Facades\Schema::hasTable('riwayat_pastor_paroki')) {
                $existingCols = \Illuminate\Support\Facades\Schema::getColumnListing('riwayat_pastor_paroki');
                \Illuminate\Support\Facades\Schema::table('riwayat_pastor_paroki', function ($table) use ($existingCols) {
                    if (!in_array('foto', $existingCols, true)) $table->string('foto', 255)->nullable();
                    if (!in_array('status_pelayanan', $existingCols, true)) $table->string('status_pelayanan', 50)->nullable();
                    if (!in_array('periode_mulai', $existingCols, true)) $table->string('periode_mulai', 50)->nullable();
                    if (!in_array('periode_selesai', $existingCols, true)) $table->string('periode_selesai', 50)->nullable();
                    if (!in_array('tahun_mulai', $existingCols, true)) $table->string('tahun_mulai', 50)->nullable();
                    if (!in_array('tahun_selesai', $existingCols, true)) $table->string('tahun_selesai', 50)->nullable();
                    if (!in_array('urutan', $existingCols, true)) $table->integer('urutan')->default(1);
                    if (!in_array('keterangan', $existingCols, true)) $table->text('keterangan')->nullable();
                });
            }
        } catch (\Throwable $e) {
            // Silently continue
        }
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
    private function getModuleMap(): array
    {
        return [
            'keuskupan' => ['model' => \App\Models\Keuskupan::class, 'title' => 'Data Keuskupan', 'columns' => [
                ['key' => 'logo', 'label' => 'Logo', 'isImage' => true],
                ['key' => 'nama_keuskupan', 'label' => 'Nama Keuskupan', 'isPrimary' => true],
                ['key' => 'nama_latin', 'altKey' => 'nama_keuskupan_latin', 'label' => 'Nama Latin'],
                ['key' => 'kode_keuskupan', 'label' => 'Kode'],
                ['key' => 'uskup', 'altKey' => 'nama_uskup', 'label' => 'Nama Uskup'],
                ['key' => 'dekenats', 'label' => 'Dekenat', 'isRelationLink' => true, 'relation' => 'dekenats', 'linkTo' => 'dekenat', 'filterParam' => 'keuskupan_id', 'icon' => 'fa-layer-group', 'color' => 'blue'],
                ['key' => 'no_telp', 'altKey' => 'telepon', 'label' => 'Kontak'],
            ]],
            'dekenat' => ['model' => \App\Models\Dekenat::class, 'title' => 'Data Kevikepan / Dekenat', 'columns' => [
                ['key' => 'ikon', 'label' => 'Ikon', 'isIcon' => true],
                ['key' => 'nama_kevikepan', 'altKey' => 'nama_dekenat', 'label' => 'Nama Kevikepan / Dekenat', 'isPrimary' => true],
                ['key' => 'kode_kevikepan', 'altKey' => 'kode_dekenat', 'label' => 'Kode'],
                ['key' => 'keuskupan_nama', 'relation' => 'keuskupan', 'relationKey' => 'nama_keuskupan', 'label' => 'Keuskupan'],
                ['key' => 'vikep', 'altKey' => 'nama_deken', 'label' => 'Vikep (Deken)'],
                ['key' => 'parokis', 'label' => 'Paroki', 'isRelationLink' => true, 'relation' => 'parokis', 'linkTo' => 'paroki', 'filterParam' => 'dekenat_id', 'icon' => 'fa-place-of-worship', 'color' => 'emerald'],
                ['key' => 'telepon', 'altKey' => 'no_telp', 'label' => 'Kontak'],
            ]],
            'kevikepan' => ['model' => \App\Models\Kevikepan::class, 'title' => 'Data Kevikepan / Dekenat', 'columns' => [
                ['key' => 'ikon', 'label' => 'Ikon', 'isIcon' => true],
                ['key' => 'nama_kevikepan', 'altKey' => 'nama_dekenat', 'label' => 'Nama Kevikepan / Dekenat', 'isPrimary' => true],
                ['key' => 'kode_kevikepan', 'altKey' => 'kode_dekenat', 'label' => 'Kode'],
                ['key' => 'keuskupan_nama', 'relation' => 'keuskupan', 'relationKey' => 'nama_keuskupan', 'label' => 'Keuskupan'],
                ['key' => 'vikep', 'altKey' => 'nama_deken', 'label' => 'Vikep (Deken)'],
                ['key' => 'parokis', 'label' => 'Paroki', 'isRelationLink' => true, 'relation' => 'parokis', 'linkTo' => 'paroki', 'filterParam' => 'dekenat_id', 'icon' => 'fa-place-of-worship', 'color' => 'emerald'],
                ['key' => 'telepon', 'altKey' => 'no_telp', 'label' => 'Kontak'],
            ]],
            'paroki' => ['model' => \App\Models\Paroki::class, 'title' => 'Data Paroki', 'columns' => [['key' => 'logo', 'label' => 'Logo', 'isImage' => true], ['key' => 'banner', 'altKey' => 'foto', 'label' => 'Banner Header (Gambar Latar)', 'isImage' => true], ['key' => 'nama_paroki', 'label' => 'Nama Paroki', 'isPrimary' => true], ['key' => 'kode_paroki', 'label' => 'Kode'], ['key' => 'dekenat_nama', 'relation' => 'dekenat', 'relationKey' => 'nama_kevikepan', 'altRelationKey' => 'nama_dekenat', 'label' => 'Kevikepan / Dekenat'], ['key' => 'pelindung_paroki', 'altKey' => 'pelindung', 'label' => 'Pelindung'], ['key' => 'nama_pastor_paroki_aktif', 'altKey' => 'pastor_paroki', 'label' => 'Pastor Paroki'], ['key' => 'alamat', 'label' => 'Alamat'], ['key' => 'telepon', 'label' => 'Kontak']]],
            'kuasi-paroki' => ['model' => \App\Models\KuasiParoki::class, 'title' => 'Data Kuasi Paroki', 'columns' => [['key' => 'ikon', 'label' => 'Ikon', 'isIcon' => true], ['key' => 'nama_kuasi', 'altKey' => 'NamaKuasiParoki', 'label' => 'Nama Kuasi Paroki', 'isPrimary' => true], ['key' => 'KodeKuasiParoki', 'altKey' => 'kode_kuasi', 'label' => 'Kode'], ['key' => 'paroki_nama', 'relation' => 'paroki', 'relationKey' => 'nama_paroki', 'label' => 'Paroki Induk'], ['key' => 'dekenat_nama', 'label' => 'Kevikepan'], ['key' => 'pastor_administrator', 'altKey' => 'PastorKuasiParoki', 'label' => 'Pastor Administrator'], ['key' => 'lokasi', 'altKey' => 'AlamatKuasiParoki', 'label' => 'Lokasi / Alamat']]],
            'kapela' => ['model' => \App\Models\Kapela::class, 'title' => 'Data Stasi / Kapela', 'columns' => [['key' => 'ikon', 'label' => 'Ikon', 'isIcon' => true], ['key' => 'nama_kapela', 'label' => 'Nama Stasi / Kapela', 'isPrimary' => true], ['key' => 'kode_kapela', 'label' => 'Kode'], ['key' => 'paroki_nama', 'relation' => 'paroki', 'relationKey' => 'nama_paroki', 'label' => 'Paroki Induk'], ['key' => 'kubs', 'label' => 'KUB', 'isRelationLink' => true, 'relation' => 'kubs', 'linkTo' => 'kub', 'filterParam' => 'kapela_id', 'icon' => 'fa-people-group', 'color' => 'teal'], ['key' => 'penanggung_jawab', 'label' => 'Penanggung Jawab'], ['key' => 'lokasi', 'label' => 'Lokasi'], ['key' => 'no_hp', 'label' => 'Kontak']]],
            'wilayah' => ['model' => \App\Models\Wilayah::class, 'title' => 'Data Wilayah Pelayanan', 'columns' => [['key' => 'ikon', 'label' => 'Ikon', 'isIcon' => true], ['key' => 'nama_wilayah', 'label' => 'Nama Wilayah', 'isPrimary' => true], ['key' => 'kode_wilayah', 'label' => 'Kode'], ['key' => 'paroki_nama', 'relation' => 'paroki', 'relationKey' => 'nama_paroki', 'label' => 'Paroki'], ['key' => 'kapela_nama', 'relation' => 'kapela', 'relationKey' => 'nama_kapela', 'label' => 'Stasi / Kapela'], ['key' => 'kubs', 'label' => 'KUB', 'isRelationLink' => true, 'relation' => 'kubs', 'linkTo' => 'kub', 'filterParam' => 'wilayah_id', 'icon' => 'fa-people-group', 'color' => 'teal'], ['key' => 'ketua_wilayah', 'label' => 'Ketua Wilayah'], ['key' => 'no_hp', 'label' => 'Kontak']]],
            'lingkungan' => ['model' => \App\Models\Lingkungan::class, 'title' => 'Lingkungan', 'columns' => [['key' => 'ikon', 'label' => 'Ikon', 'isIcon' => true], ['key' => 'nama_lingkungan', 'label' => 'Nama Lingkungan', 'isPrimary' => true], ['key' => 'kode_lingkungan', 'label' => 'Kode'], ['key' => 'ketua_lingkungan', 'label' => 'Ketua Lingkungan']]],
            'kub' => ['model' => \App\Models\Kub::class, 'title' => 'Komunitas Umat Basis (KUB)', 'columns' => [['key' => 'ikon', 'label' => 'Ikon', 'isIcon' => true], ['key' => 'nama_kub', 'label' => 'Nama KUB', 'isPrimary' => true], ['key' => 'kode_kub', 'label' => 'Kode'], ['key' => 'wilayah_nama', 'relation' => 'wilayah', 'relationKey' => 'nama_wilayah', 'label' => 'Wilayah'], ['key' => 'kapela_nama', 'relation' => 'kapela', 'relationKey' => 'nama_kapela', 'label' => 'Stasi / Kapela'], ['key' => 'paroki_nama', 'relation' => 'paroki', 'relationKey' => 'nama_paroki', 'label' => 'Paroki'], ['key' => 'ketua_kub', 'label' => 'Ketua KUB'], ['key' => 'kontak', 'altKey' => 'no_hp', 'label' => 'Kontak']]],
            'kk-katolik' => ['model' => \App\Models\KkKatolik::class, 'title' => 'Kartu Keluarga (KK) Katolik', 'columns' => [
                ['key' => 'no_kk_kw', 'altKey' => 'no_kk_dukcapil', 'label' => 'No KK Katolik', 'isPrimary' => true],
                ['key' => 'nama_baptis_pemilik', 'altKey' => 'nama_lahir_pemilik', 'label' => 'Nama Kepala Keluarga (Baptis & Lahir)'],

                ['key' => 'wilayah_nama', 'relation' => 'wilayah', 'relationKey' => 'nama_wilayah', 'label' => 'Wilayah Pelayanan'],
                ['key' => 'alamat_sekarang', 'label' => 'Alamat Domisili'],
                ['key' => 'handphone', 'altKey' => 'telepon', 'label' => 'Kontak / HP'],
                ['key' => 'status_kk', 'label' => 'Status'],
            ]],
            'kk' => ['model' => \App\Models\KkKatolik::class, 'title' => 'Kartu Keluarga (KK) Katolik', 'columns' => [
                ['key' => 'no_kk_kw', 'altKey' => 'no_kk_dukcapil', 'label' => 'No KK Katolik', 'isPrimary' => true],
                ['key' => 'nama_baptis_pemilik', 'altKey' => 'nama_lahir_pemilik', 'label' => 'Nama Kepala Keluarga (Baptis & Lahir)'],
                ['key' => 'wilayah_nama', 'relation' => 'wilayah', 'relationKey' => 'nama_wilayah', 'label' => 'Wilayah Pelayanan'],
                ['key' => 'alamat_sekarang', 'label' => 'Alamat Domisili'],
                ['key' => 'handphone', 'altKey' => 'telepon', 'label' => 'Kontak / HP'],
                ['key' => 'status_kk', 'label' => 'Status'],
            ]],
            'keluarga' => ['model' => \App\Models\KkKatolik::class, 'title' => 'Kartu Keluarga (KK) Katolik', 'columns' => [
                ['key' => 'no_kk_kw', 'altKey' => 'no_kk_dukcapil', 'label' => 'No KK Katolik', 'isPrimary' => true],
                ['key' => 'nama_baptis_pemilik', 'altKey' => 'nama_lahir_pemilik', 'label' => 'Nama Kepala Keluarga (Baptis & Lahir)'],
                ['key' => 'wilayah_nama', 'relation' => 'wilayah', 'relationKey' => 'nama_wilayah', 'label' => 'Wilayah Pelayanan'],

                ['key' => 'alamat_sekarang', 'label' => 'Alamat Domisili'],
                ['key' => 'handphone', 'altKey' => 'telepon', 'label' => 'Kontak / HP'],
                ['key' => 'status_kk', 'label' => 'Status'],
            ]],
            'umat' => ['model' => \App\Models\Umat::class, 'title' => 'Data Umat / Jiwa Paroki', 'columns' => [
                ['key' => 'foto', 'label' => 'Foto', 'isImage' => true],
                ['key' => 'nama_lengkap', 'altKey' => 'nama_baptis', 'label' => 'Nama Lengkap & Baptis', 'isPrimary' => true],
                ['key' => 'nik', 'label' => 'NIK'],
                ['key' => 'no_kk_kw', 'label' => 'No KK'],
                ['key' => 'jenis_kelamin', 'label' => 'L/P'],
                ['key' => 'tempat_lahir', 'label' => 'Tempat Lahir'],
                ['key' => 'tanggal_lahir', 'label' => 'Tanggal Lahir', 'isDate' => true],
                ['key' => 'hubungan_keluarga', 'label' => 'Kedudukan'],
                ['key' => 'status_menikah', 'label' => 'Status Perkawinan'],
                ['key' => 'status_umat', 'altKey' => 'status_aktif', 'label' => 'Status'],
            ]],
            'data-umat' => ['model' => \App\Models\Umat::class, 'title' => 'Data Umat / Jiwa Paroki', 'columns' => [
                ['key' => 'foto', 'label' => 'Foto', 'isImage' => true],
                ['key' => 'nama_lengkap', 'altKey' => 'nama_baptis', 'label' => 'Nama Lengkap & Baptis', 'isPrimary' => true],
                ['key' => 'nik', 'label' => 'NIK'],
                ['key' => 'no_kk_kw', 'label' => 'No KK'],
                ['key' => 'jenis_kelamin', 'label' => 'L/P'],
                ['key' => 'tempat_lahir', 'label' => 'Tempat Lahir'],
                ['key' => 'tanggal_lahir', 'label' => 'Tanggal Lahir', 'isDate' => true],
                ['key' => 'hubungan_keluarga', 'label' => 'Kedudukan'],
                ['key' => 'status_menikah', 'label' => 'Status Perkawinan'],
                ['key' => 'status_umat', 'altKey' => 'status_aktif', 'label' => 'Status'],
            ]],
            'sakramen' => ['model' => \App\Models\Sakramen::class, 'title' => 'Buku Sakramen', 'columns' => [
                ['key' => 'tipe_sakramen', 'label' => 'Tipe Sakramen', 'isPrimary' => true],
                ['key' => 'umat_nama', 'relation' => 'umat', 'relationKey' => 'nama_lengkap', 'label' => 'Nama Penerima / Umat'],
                ['key' => 'tanggal', 'label' => 'Tanggal Penerimaan', 'isDate' => true],
                ['key' => 'tempat', 'label' => 'Gereja / Tempat'],
                ['key' => 'pelaksana', 'altKey' => 'pastor', 'label' => 'Pastor Pelayan'],
                ['key' => 'no_surat', 'label' => 'No Akta / Surat'],
                ['key' => 'status', 'label' => 'Status'],
            ]],
            'pengajuan-sakramen' => [
                'model' => \App\Models\PengajuanSakramen::class,
                'title' => 'Pengajuan & Administrasi Sakramen',
                'columns' => [
                    ['key' => 'nama_lengkap', 'label' => 'Nama Pemohon / Penerima', 'isPrimary' => true],
                    ['key' => 'tipe_sakramen', 'label' => 'Tipe Sakramen'],
                    ['key' => 'whatsapp', 'label' => 'No. WhatsApp'],
                    ['key' => 'tanggal_pelaksanaan', 'label' => 'Tgl Pelaksanaan', 'isDate' => true],
                    ['key' => 'biaya_administrasi', 'label' => 'Biaya Admin (Rp)'],
                    ['key' => 'status_pembayaran', 'label' => 'Status Bayar'],
                    ['key' => 'status_pengajuan', 'label' => 'Status Pengajuan'],
                ],
            ],
            'jadwal-misa' => ['model' => \App\Models\JadwalMisa::class, 'title' => 'Jadwal Misa', 'columns' => [['key' => 'jenis_perayaan', 'altKey' => 'jenis_misa', 'label' => 'Nama Misa', 'isPrimary' => true], ['key' => 'tanggal', 'label' => 'Tanggal'], ['key' => 'hari', 'label' => 'Hari'], ['key' => 'waktu', 'altKey' => 'jam_perayaan', 'label' => 'Waktu'], ['key' => 'tempat', 'altKey' => 'lokasi', 'label' => 'Gereja / Tempat']]],
            'jenis-iuran' => ['model' => \App\Models\JenisIuran::class, 'title' => 'Daftar Jenis Iuran Umat', 'columns' => [
                ['key' => 'kode_iuran', 'label' => 'Kode'],
                ['key' => 'nama_iuran', 'label' => 'Nama Iuran', 'isPrimary' => true],
                ['key' => 'kategori_iuran', 'label' => 'Kategori'],
                ['key' => 'basis_penagihan', 'label' => 'Basis Penagihan'],
                ['key' => 'nominal_default', 'label' => 'Nominal Default (Rp)'],
                ['key' => 'periode', 'label' => 'Periode'],
                ['key' => 'wajib', 'label' => 'Wajib / Sukarela'],
                ['key' => 'status', 'label' => 'Status'],
            ]],
            'jenis_iuran' => ['model' => \App\Models\JenisIuran::class, 'title' => 'Daftar Jenis Iuran Umat', 'columns' => [
                ['key' => 'kode_iuran', 'label' => 'Kode'],
                ['key' => 'nama_iuran', 'label' => 'Nama Iuran', 'isPrimary' => true],
                ['key' => 'kategori_iuran', 'label' => 'Kategori'],
                ['key' => 'basis_penagihan', 'label' => 'Basis Penagihan'],
                ['key' => 'nominal_default', 'label' => 'Nominal Default (Rp)'],
                ['key' => 'periode', 'label' => 'Periode'],
                ['key' => 'wajib', 'label' => 'Wajib / Sukarela'],
                ['key' => 'status', 'label' => 'Status'],
            ]],
            'iuran' => ['model' => \App\Models\Iuran::class, 'title' => 'Pencatatan Iuran Umat', 'columns' => [
                ['key' => 'no_kk', 'label' => 'No KK'],
                ['key' => 'nama_kepala', 'label' => 'Kepala Keluarga', 'isPrimary' => true],
                ['key' => 'nama_iuran', 'label' => 'Jenis Iuran'],
                ['key' => 'tahun', 'label' => 'Tahun'],
                ['key' => 'bulan_lunas', 'label' => 'Bulan Lunas'],
                ['key' => 'total_jumlah', 'altKey' => 'jumlah', 'label' => 'Total Bayar (Rp)'],
                ['key' => 'status_bayar', 'label' => 'Status'],
                ['key' => 'tanggal_bayar', 'label' => 'Tgl Bayar', 'isDate' => true],
                ['key' => 'kolektor', 'altKey' => 'petugas', 'label' => 'Petugas / Kolektor'],
            ]],
            'kolekte' => ['model' => \App\Models\Kolekte::class, 'title' => 'Pencatatan Kolekte Misa', 'columns' => [
                ['key' => 'tanggal', 'label' => 'Tanggal Misa', 'isDate' => true, 'isPrimary' => true],
                ['key' => 'kategori_misa', 'label' => 'Kategori / Perayaan Misa'],
                ['key' => 'nominal', 'label' => 'Jumlah Kolekte (Rp)'],
                ['key' => 'lokasi_misa', 'label' => 'Gereja / Tempat'],
                ['key' => 'petugas_penghitung', 'label' => 'Petugas Penghitung'],
                ['key' => 'keterangan', 'label' => 'Keterangan'],
            ]],
            'intensi-misa' => ['model' => \App\Models\IntensiMisa::class, 'title' => 'Pencatatan Intensi Misa', 'columns' => [
                ['key' => 'nama_pemohon', 'label' => 'Nama Pemohon', 'isPrimary' => true],
                ['key' => 'kategori_intensi', 'label' => 'Kategori Intensi'],
                ['key' => 'deskripsi', 'label' => 'Doa / Ujud Intensi'],
                ['key' => 'tanggal_misa', 'label' => 'Tanggal Misa', 'isDate' => true],

                ['key' => 'nominal_stipendium', 'label' => 'Stipendium (Rp)'],
                ['key' => 'status_pembayaran', 'label' => 'Status Bayar'],
            ]],
            'intensi' => ['model' => \App\Models\IntensiMisa::class, 'title' => 'Pencatatan Intensi Misa', 'columns' => [
                ['key' => 'nama_pemohon', 'label' => 'Nama Pemohon', 'isPrimary' => true],
                ['key' => 'kategori_intensi', 'label' => 'Kategori Intensi'],
                ['key' => 'deskripsi', 'label' => 'Doa / Ujud Intensi'],
                ['key' => 'tanggal_misa', 'label' => 'Tanggal Misa', 'isDate' => true],
                ['key' => 'nominal_stipendium', 'label' => 'Stipendium (Rp)'],
                ['key' => 'status_pembayaran', 'label' => 'Status Bayar'],
            ]],
            'kegiatan' => ['model' => \App\Models\Kegiatan::class, 'title' => 'Agenda Kegiatan Paroki', 'columns' => [
                ['key' => 'gambar', 'altKey' => 'foto', 'label' => 'Poster', 'isImage' => true],
                ['key' => 'nama_kegiatan', 'altKey' => 'judul', 'label' => 'Nama Kegiatan', 'isPrimary' => true],
                ['key' => 'kategori', 'label' => 'Kategori'],
                ['key' => 'tanggal_mulai', 'label' => 'Tanggal Mulai', 'isDate' => true],
                ['key' => 'tanggal_selesai', 'label' => 'Tanggal Selesai', 'isDate' => true],
                ['key' => 'waktu', 'altKey' => 'jam', 'label' => 'Waktu / Jam', 'isTime' => true],
                ['key' => 'lokasi', 'label' => 'Lokasi / Tempat'],
                ['key' => 'status', 'label' => 'Status'],
            ]],
            'keuangan' => ['model' => \App\Models\Keuangan::class, 'title' => 'Kas & Transaksi Keuangan Paroki', 'columns' => [
                ['key' => 'tanggal', 'label' => 'Tanggal Transaksi', 'isDate' => true],
                ['key' => 'jenis', 'altKey' => 'jenis_transaksi', 'label' => 'Jenis (Masuk/Keluar)'],
                ['key' => 'kategori', 'label' => 'Kategori Transaksi', 'isPrimary' => true],
                ['key' => 'kode_coa', 'label' => 'Kode COA'],
                ['key' => 'jumlah', 'altKey' => 'nominal', 'label' => 'Jumlah (Rp)'],
                ['key' => 'penerima', 'label' => 'Penerima / Pihak Terkait'],
                ['key' => 'status_approval', 'label' => 'Status Approval'],
                ['key' => 'keterangan', 'label' => 'Keterangan'],
            ]],
            'aset' => ['model' => \App\Models\Aset::class, 'title' => 'Data Aset & Inventaris Paroki', 'columns' => [
                ['key' => 'foto', 'label' => 'Foto', 'isImage' => true],
                ['key' => 'kode_aset', 'label' => 'Kode Aset'],
                ['key' => 'nama_aset', 'label' => 'Nama Barang / Aset', 'isPrimary' => true],
                ['key' => 'kategori', 'label' => 'Kategori Aset'],
                ['key' => 'jumlah', 'label' => 'Jumlah'],
                ['key' => 'satuan', 'label' => 'Satuan'],
                ['key' => 'kondisi', 'label' => 'Kondisi Fisik'],
                ['key' => 'nilai_perolehan', 'label' => 'Nilai Perolehan (Rp)'],
                ['key' => 'lokasi', 'label' => 'Lokasi / Ruangan'],
                ['key' => 'penanggung_jawab', 'label' => 'Penanggung Jawab'],
                ['key' => 'tanggal_perolehan', 'label' => 'Tgl Perolehan', 'isDate' => true],
            ]],
            'surat-masuk' => ['model' => \App\Models\SuratMasuk::class, 'title' => 'Surat Masuk', 'columns' => [['key' => 'no_surat', 'label' => 'No Surat', 'isPrimary' => true], ['key' => 'pengirim', 'label' => 'Pengirim'], ['key' => 'perihal', 'label' => 'Perihal'], ['key' => 'tgl_surat', 'label' => 'Tanggal']]],
            'surat-keluar' => ['model' => \App\Models\SuratKeluar::class, 'title' => 'Surat Keluar', 'columns' => [['key' => 'no_surat', 'label' => 'No Surat', 'isPrimary' => true], ['key' => 'tujuan', 'label' => 'Tujuan'], ['key' => 'perihal', 'label' => 'Perihal'], ['key' => 'tgl_surat', 'label' => 'Tanggal']]],
            'arsip-digital' => ['model' => \App\Models\ArsipDigital::class, 'title' => 'Arsip Digital', 'columns' => [['key' => 'nama_dokumen', 'label' => 'Nama Dokumen', 'isPrimary' => true], ['key' => 'kategori', 'label' => 'Kategori'], ['key' => 'tgl_arsip', 'label' => 'Tanggal Arsip']]],
            'rapat-notulen' => ['model' => \App\Models\Rapat::class, 'title' => 'Rapat & Notulen', 'columns' => [['key' => 'agenda', 'label' => 'Agenda Rapat', 'isPrimary' => true], ['key' => 'tanggal', 'label' => 'Tanggal Rapat', 'isDate' => true], ['key' => 'waktu', 'label' => 'Waktu / Jam', 'isTime' => true], ['key' => 'lokasi', 'label' => 'Lokasi / Tempat'], ['key' => 'notulen', 'label' => 'Notulen & Hasil Rapat'], ['key' => 'status', 'label' => 'Status']]],
            'rapat' => ['model' => \App\Models\Rapat::class, 'title' => 'Rapat & Notulen', 'columns' => [['key' => 'agenda', 'label' => 'Agenda Rapat', 'isPrimary' => true], ['key' => 'tanggal', 'label' => 'Tanggal Rapat', 'isDate' => true], ['key' => 'waktu', 'label' => 'Waktu / Jam', 'isTime' => true], ['key' => 'lokasi', 'label' => 'Lokasi / Tempat'], ['key' => 'notulen', 'label' => 'Notulen & Hasil Rapat'], ['key' => 'status', 'label' => 'Status']]],
            'master-uskup' => ['model' => \App\Models\MasterUskup::class, 'title' => 'Daftar Uskup', 'columns' => [['key' => 'foto', 'label' => 'Foto', 'isImage' => true], ['key' => 'nama_uskup', 'label' => 'Nama Uskup', 'isPrimary' => true], ['key' => 'keuskupan', 'label' => 'Keuskupan'], ['key' => 'status', 'label' => 'Status']]],
            'master-pastor' => ['model' => \App\Models\MasterPastor::class, 'title' => 'Daftar Pastor / Imam', 'columns' => [['key' => 'foto', 'label' => 'Foto', 'isImage' => true], ['key' => 'nama_pastor', 'label' => 'Nama Pastor', 'isPrimary' => true], ['key' => 'gelar_depan', 'label' => 'Gelar'], ['key' => 'jabatan', 'label' => 'Jabatan'], ['key' => 'jenis_imam', 'label' => 'Jenis Imam'], ['key' => 'ordo', 'label' => 'Ordo'], ['key' => 'keuskupan', 'label' => 'Keuskupan'], ['key' => 'no_hp', 'label' => 'Kontak / WA'], ['key' => 'status', 'label' => 'Status']]],
            'riwayat-pastor' => ['model' => \App\Models\RiwayatPastorParoki::class, 'title' => 'Riwayat Pastor Paroki', 'columns' => [
                ['key' => 'foto', 'label' => 'Foto', 'isImage' => true],
                ['key' => 'nama_pastor', 'label' => 'Nama Pastor / Gembala', 'isPrimary' => true],
                ['key' => 'jabatan', 'label' => 'Jabatan di Paroki'],
                ['key' => 'periode_mulai', 'altKey' => 'tahun_mulai', 'label' => 'Mulai Pelayanan'],
                ['key' => 'periode_selesai', 'altKey' => 'tahun_selesai', 'label' => 'Selesai Pelayanan'],
                ['key' => 'status_pelayanan', 'altKey' => 'status', 'label' => 'Status Pelayanan'],
                ['key' => 'urutan', 'label' => 'Urutan'],

                ['key' => 'keterangan', 'altKey' => 'karya_pelayanan', 'label' => 'Catatan / Karya'],
            ]],
            'riwayat_pastor_paroki' => ['model' => \App\Models\RiwayatPastorParoki::class, 'title' => 'Riwayat Pastor Paroki', 'columns' => [
                ['key' => 'foto', 'label' => 'Foto', 'isImage' => true],
                ['key' => 'nama_pastor', 'label' => 'Nama Pastor / Gembala', 'isPrimary' => true],
                ['key' => 'jabatan', 'label' => 'Jabatan di Paroki'],
                ['key' => 'periode_mulai', 'altKey' => 'tahun_mulai', 'label' => 'Mulai Pelayanan'],
                ['key' => 'periode_selesai', 'altKey' => 'tahun_selesai', 'label' => 'Selesai Pelayanan'],
                ['key' => 'status_pelayanan', 'altKey' => 'status', 'label' => 'Status Pelayanan'],
                ['key' => 'urutan', 'label' => 'Urutan'],
                ['key' => 'keterangan', 'altKey' => 'karya_pelayanan', 'label' => 'Catatan / Karya'],
            ]],
            'direktori-dpp' => ['model' => \App\Models\DirektoriDpp::class, 'title' => 'Direktori (DPP)', 'columns' => [['key' => 'foto', 'label' => 'Foto', 'isImage' => true], ['key' => 'nama_lengkap', 'label' => 'Nama Pengurus', 'isPrimary' => true], ['key' => 'jabatan', 'label' => 'Jabatan'], ['key' => 'seksi', 'label' => 'Seksi / Bidang'], ['key' => 'periode', 'label' => 'Periode'], ['key' => 'no_hp', 'label' => 'Kontak / WA'], ['key' => 'status', 'label' => 'Status'], ['key' => 'urutan', 'label' => 'Urutan']]],
            'direktori-katekis' => ['model' => \App\Models\DirektoriKatekis::class, 'title' => 'Direktori Katekis', 'columns' => [['key' => 'foto', 'label' => 'Foto', 'isImage' => true], ['key' => 'nama_lengkap', 'label' => 'Nama Katekis', 'isPrimary' => true], ['key' => 'jenis_katekis', 'label' => 'Jenis Katekis'], ['key' => 'wilayah_pelayanan', 'label' => 'Wilayah Pelayanan'], ['key' => 'sertifikasi', 'label' => 'Sertifikasi'], ['key' => 'no_hp', 'label' => 'Kontak / WA'], ['key' => 'status_aktif', 'label' => 'Status']]],
            'direktori-misdinar' => ['model' => \App\Models\DirektoriMisdinar::class, 'title' => 'Direktori Misdinar', 'columns' => [['key' => 'nama_lengkap', 'label' => 'Nama Anggota', 'isPrimary' => true], ['key' => 'stasi', 'label' => 'Stasi / Kapela'], ['key' => 'status_aktif', 'label' => 'Status']]],
            'kronik' => ['model' => \App\Models\KronikParoki::class, 'title' => 'Kronik Paroki', 'columns' => [['key' => 'foto_utama', 'label' => 'Foto', 'isImage' => true], ['key' => 'judul_kronik', 'label' => 'Judul Kronik', 'isPrimary' => true], ['key' => 'tanggal_peristiwa', 'label' => 'Tanggal Peristiwa'], ['key' => 'kategori_kronik', 'label' => 'Kategori'], ['key' => 'lokasi_peristiwa', 'label' => 'Lokasi'], ['key' => 'penulis', 'label' => 'Penulis'], ['key' => 'status_publish', 'label' => 'Status']]],

            'peran-kategorial' => ['model' => \App\Models\PeranKategorial::class, 'title' => 'Peran Kategorial', 'columns' => [['key' => 'foto', 'label' => 'Foto', 'isImage' => true], ['key' => 'nama_peran', 'label' => 'Nama Peran', 'isPrimary' => true], ['key' => 'kode_peran', 'label' => 'Kode'], ['key' => 'kategori', 'label' => 'Kategori'], ['key' => 'nama_koordinator', 'label' => 'Koordinator'], ['key' => 'lokasi_kegiatan', 'label' => 'Lokasi'], ['key' => 'status', 'label' => 'Status']]],
            'anggota-kategorial' => ['model' => \App\Models\AnggotaKategorial::class, 'title' => 'Anggota Kategorial', 'columns' => [['key' => 'foto', 'label' => 'Foto', 'isImage' => true], ['key' => 'nama_anggota', 'label' => 'Nama Anggota', 'isPrimary' => true], ['key' => 'peran_nama', 'relation' => 'peranKategorial', 'relationKey' => 'nama_peran', 'label' => 'Peran Kategorial'], ['key' => 'jabatan_dalam_kelompok', 'label' => 'Jabatan'], ['key' => 'tanggal_bergabung', 'label' => 'Tanggal Bergabung'], ['key' => 'status_keanggotaan', 'label' => 'Status']]],
            'kategori-konten' => ['model' => \App\Models\KategoriKonten::class, 'title' => 'Kategori Konten & Artikel', 'columns' => [
                ['key' => 'ikon', 'label' => 'Ikon', 'isIcon' => true, 'iconClass' => 'fa-solid fa-folder-open'],
                ['key' => 'nama_kategori', 'label' => 'Nama Kategori', 'isPrimary' => true],
                ['key' => 'slug', 'label' => 'Slug URL'],
                ['key' => 'deskripsi', 'label' => 'Deskripsi Kategori'],
                ['key' => 'urutan', 'label' => 'Urutan'],
                ['key' => 'status', 'label' => 'Status'],
            ]],
            'kategori_konten' => ['model' => \App\Models\KategoriKonten::class, 'title' => 'Kategori Konten & Artikel', 'columns' => [
                ['key' => 'ikon', 'label' => 'Ikon', 'isIcon' => true, 'iconClass' => 'fa-solid fa-folder-open'],
                ['key' => 'nama_kategori', 'label' => 'Nama Kategori', 'isPrimary' => true],
                ['key' => 'slug', 'label' => 'Slug URL'],
                ['key' => 'deskripsi', 'label' => 'Deskripsi Kategori'],
                ['key' => 'urutan', 'label' => 'Urutan'],
                ['key' => 'status', 'label' => 'Status'],
            ]],
}
