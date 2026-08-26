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
    public function module(Request $request, string $slug)
    {
        if ($slug === 'statistik' || $slug === 'demografi') {
            return $this->statistik($request);
        }

        $moduleMap = $this->getModuleMap();

        if (!isset($moduleMap[$slug])) {
            return redirect()->route('dashboard');
        }

        $config = $moduleMap[$slug];
        $modelClass = $config['model'];
        $search = $request->input('search');
        $perPage = (int) $request->input('per_page', 10);
        if ($perPage < 5 || $perPage > 100) {
            $perPage = 10;
        }

        if ($slug === 'riwayat-pastor' || $slug === 'riwayat_pastor_paroki') {
            $this->ensureRiwayatPastorParokiTableAndData();
        }

        if ($slug === 'kategori-konten' || $slug === 'kategori_konten') {
            $this->ensureKategoriKontenTableAndData();
        }

        if ($slug === 'komentar-artikel' || $slug === 'komentar_artikel') {
            $this->ensureKomentarArtikelTableAndData();
        }

        $query = $modelClass::query();
        if ($slug === 'keuskupan') {
            $query->with([
                'dekenats.parokis',
                'provinsi:id_provinsi,nama_provinsi',
                'kabupaten:id_kabupaten,nama_kabupaten',
                'kecamatan:id_kecamatan,nama_kecamatan',
                'desa:id_desa,nama_desa'
            ]);
        } elseif ($slug === 'dekenat' || $slug === 'kevikepan') {
            $query->with([
                'keuskupan',
                'parokis'
            ]);
        } elseif ($slug === 'paroki') {
            $query->with([
                'keuskupan',
                'dekenat',
                'provinsi:id_provinsi,nama_provinsi',
                'kabupaten:id_kabupaten,nama_kabupaten',
                'kecamatan:id_kecamatan,nama_kecamatan',
                'desa:id_desa,nama_desa',
                'wilayahs'
            ]);
        } elseif ($slug === 'kuasi-paroki') {
            $query->with(['paroki.dekenat']);
        } elseif ($slug === 'kapela' || $slug === 'stasi') {
            if (Schema::hasColumn('kapela', 'paroki_id')) {
                $query->with(['paroki']);
            }
            if (Schema::hasColumn('kub', 'kapela_id')) {
                $query->with(['kubs']);
            }
            if (Schema::hasColumn('wilayah', 'kapela_id')) {
                $query->with(['wilayahs']);
            }
        } elseif ($slug === 'wilayah') {
            $query->with(['paroki', 'kubs']);
        } elseif ($slug === 'kub') {
            $query->with(['wilayah', 'kapela', 'paroki']);
        } elseif ($slug === 'provinsi') {
            $query->with(['kabupatens']);
        } elseif ($slug === 'kabupaten') {
            $query->with(['provinsi:id_provinsi,nama_provinsi', 'kecamatans']);
        } elseif ($slug === 'kecamatan') {
            $query->with(['kabupaten', 'desas']);
        } elseif ($slug === 'desa-kelurahan') {
            $query->with(['kecamatan']);
        } elseif ($slug === 'kk-katolik') {
            $query->withCount('anggota');
        } elseif (in_array($slug, ['umat', 'data-umat'], true)) {
            $query->with(['kk.wilayah', 'kk.kapela', 'kk.kub', 'lingkungan']);
        } elseif ($slug === 'user') {
            $query->with(['role', 'wilayah', 'kapela', 'kub']);
        } elseif ($slug === 'anggota-kategorial') {
            $query->with(['peranKategorial']);
        } elseif ($slug === 'komentar-artikel' || $slug === 'komentar_artikel') {
            $query->with(['konten']);
        }

        $modelInstance = new $modelClass;
        $tableName = $modelInstance->getTable();
        $tableColumns = \Illuminate\Support\Facades\Cache::remember(
            "schema_columns_{$tableName}",
            86400,
            fn () => \Illuminate\Support\Facades\Schema::getColumnListing($tableName)
        );
        $defaultParokiId = $this->defaultParokiIdFromProfile();

        if (($slug === 'kapela' || $slug === 'stasi') && $defaultParokiId && in_array('paroki_id', $tableColumns, true)) {
            $query->where('paroki_id', $defaultParokiId);
        }

        // Automatic Scope Filtering based on Role (matches CI3 reference)
        $authUser = auth()->user();
        $userRoleSlug = strtolower(preg_replace('/[^a-z0-9]/', '', $authUser?->role?->slug ?? $authUser?->role?->nama_role ?? ''));

        if (str_contains($userRoleSlug, 'wilayah') && !empty($authUser?->wilayah_id)) {
            if ($slug === 'wilayah' && in_array('id', $tableColumns, true)) {
                $query->where('id', $authUser->wilayah_id);
            } elseif (in_array('wilayah_id', $tableColumns, true)) {
                $query->where('wilayah_id', $authUser->wilayah_id);
            }
        } elseif ((str_contains($userRoleSlug, 'kapela') || str_contains($userRoleSlug, 'stasi')) && !empty($authUser?->kapela_id)) {
            if (in_array($slug, ['kapela', 'stasi']) && in_array('id', $tableColumns, true)) {
                $query->where('id', $authUser->kapela_id);
            } elseif (in_array('kapela_id', $tableColumns, true)) {
                $query->where('kapela_id', $authUser->kapela_id);
            }
        } elseif (str_contains($userRoleSlug, 'kub') && !empty($authUser?->kub_id)) {
            if ($slug === 'kub' && in_array('id', $tableColumns, true)) {
                $query->where('id', $authUser->kub_id);
            } elseif (in_array('kub_id', $tableColumns, true)) {
                $query->where('kub_id', $authUser->kub_id);
            }
        }

        $keuskupanFilter = $request->input('keuskupan_id');
        $dekenatFilter = $request->input('dekenat_id');
        $provinsiFilter = $request->input('provinsi_id');
        $kabupatenFilter = $request->input('kabupaten_id');
        $kecamatanFilter = $request->input('kecamatan_id');
        $tipeFilter = $request->input('tipe');
        $roleIdFilter = $request->input('role_id');
        $wilayahIdFilter = $request->input('wilayah_id');
        $kapelaIdFilter = $request->input('kapela_id');
        $kubIdFilter = $request->input('kub_id');
        $statusFilter = $request->input('status');

        if ($keuskupanFilter && in_array('keuskupan_id', $tableColumns)) {
            $query->where('keuskupan_id', $keuskupanFilter);
        }

        if ($dekenatFilter && in_array('dekenat_id', $tableColumns)) {
            $query->where('dekenat_id', $dekenatFilter);
        }

        if ($provinsiFilter) {
            if (in_array('provinsi_id', $tableColumns)) {
                $query->where('provinsi_id', $provinsiFilter);
            } elseif ($slug === 'kecamatan') {
                $query->whereHas('kabupaten', function ($q) use ($provinsiFilter) {
                    $q->where('provinsi_id', $provinsiFilter);
                });
            } elseif ($slug === 'desa-kelurahan') {
                $query->whereHas('kecamatan.kabupaten', function ($q) use ($provinsiFilter) {
                    $q->where('provinsi_id', $provinsiFilter);
                });
            }
        }

        if ($kabupatenFilter) {
            if (in_array('kabupaten_id', $tableColumns)) {
                $query->where('kabupaten_id', $kabupatenFilter);
            } elseif ($slug === 'desa-kelurahan') {
                $query->whereHas('kecamatan', function ($q) use ($kabupatenFilter) {
                    $q->where('kabupaten_id', $kabupatenFilter);
                });
            }
        }

        if ($kecamatanFilter && in_array('kecamatan_id', $tableColumns)) {
            $query->where('kecamatan_id', $kecamatanFilter);
        }

        if ($slug === 'konten' && $tipeFilter && in_array('tipe', $tableColumns, true)) {
            $query->where('tipe', $tipeFilter);
        }

        if (in_array($slug, ['umat', 'data-umat'], true)) {
            if ($wilayahIdFilter) {
                $query->where(function ($q) use ($wilayahIdFilter, $tableColumns) {
                    if (in_array('wilayah_id', $tableColumns, true)) {
                        $q->where('wilayah_id', $wilayahIdFilter);
                    }
                    $q->orWhereHas('kk', function ($kkQ) use ($wilayahIdFilter) {
                        $kkQ->where('wilayah_id', $wilayahIdFilter);
                    });
                });
            }

            if ($kapelaIdFilter) {
                $query->where(function ($q) use ($kapelaIdFilter, $tableColumns) {
                    if (in_array('kapela_id', $tableColumns, true)) {
                        $q->where('kapela_id', $kapelaIdFilter);
                    }
                    $q->orWhereHas('kk', function ($kkQ) use ($kapelaIdFilter) {
                        $kkQ->where('kapela_id', $kapelaIdFilter);
                    });
                });
            }

            if ($kubIdFilter) {
                $query->where(function ($q) use ($kubIdFilter, $tableColumns) {
                    if (in_array('kub_id', $tableColumns, true)) {
                        $q->where('kub_id', $kubIdFilter);
                    }
                    $q->orWhereHas('kk', function ($kkQ) use ($kubIdFilter) {
                        $kkQ->where('kub_id', $kubIdFilter);
                    });
                });
            }
        }

        foreach (['paroki_id', 'wilayah_id', 'kapela_id', 'kub_id', 'role_id', 'status', 'status_kk', 'status_verifikasi'] as $relationFilter) {
            if (in_array($slug, ['umat', 'data-umat'], true) && in_array($relationFilter, ['wilayah_id', 'kapela_id', 'kub_id'], true)) {
                continue;
            }
            $value = $request->input($relationFilter);
            if ($value && in_array($relationFilter, $tableColumns, true)) {
                $query->where($relationFilter, $value);
            }
        }

        if ($search && !empty($config['columns'])) {
            $query->where(function ($q) use ($config, $search, $tableColumns) {
                $isFirst = true;
                foreach ($config['columns'] as $col) {
                    $columnToSearch = null;
                    if (in_array($col['key'], $tableColumns)) {
                        $columnToSearch = $col['key'];
                    } elseif (isset($col['altKey']) && in_array($col['altKey'], $tableColumns)) {
                        $columnToSearch = $col['altKey'];
                    }

                    if ($columnToSearch) {
                        if ($isFirst) {
                            $q->where($columnToSearch, 'like', "%{$search}%");
                            $isFirst = false;
                        } else {
                            $q->orWhere($columnToSearch, 'like', "%{$search}%");
                        }
                    }
                }
            });
        }

        if (in_array('created_at', $tableColumns)) {
            $query->latest('created_at');
        } elseif ($modelInstance->getKeyName() && in_array($modelInstance->getKeyName(), $tableColumns)) {
            $query->orderBy($modelInstance->getKeyName(), 'desc');
        }

        $items = $query->paginate($perPage)->withQueryString();

        $items->getCollection()->transform(function ($item) {
            if (is_object($item)) {
                $pkVal = method_exists($item, 'getKey') ? $item->getKey() : ($item->id ?? null);
                if ($pkVal) {
                    $item->hashid = encode_id($pkVal);
                    $item->iid = $item->hashid;
                }
            }
            return $item;
        });

        $firstSegment = explode('/', trim($request->path(), '/'))[0] ?? '';
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

        $needsKeuskupanReferences = in_array($slug, ['keuskupan', 'dekenat', 'kevikepan', 'paroki', 'kuasi-paroki', 'kapela', 'stasi', 'wilayah', 'kub'], true);
        $needsDekenatReferences = in_array($slug, ['keuskupan', 'dekenat', 'kevikepan', 'paroki', 'kuasi-paroki'], true);
        $needsProvinsiReferences = in_array($slug, ['keuskupan', 'paroki', 'kuasi-paroki', 'kapela', 'stasi', 'kabupaten', 'kecamatan', 'desa-kelurahan'], true);
        $needsKabupatenReferences = in_array($slug, ['keuskupan', 'paroki', 'kuasi-paroki', 'kapela', 'stasi', 'kecamatan', 'desa-kelurahan'], true);
        $needsKecamatanReferences = in_array($slug, ['keuskupan', 'paroki', 'kuasi-paroki', 'kapela', 'stasi', 'desa-kelurahan'], true);
        $needsParokiReferences = in_array($slug, ['keuskupan', 'dekenat', 'kevikepan', 'paroki', 'kuasi-paroki', 'kapela', 'stasi', 'wilayah', 'kub', 'user'], true);
        $needsPastors = in_array($slug, ['paroki', 'kuasi-paroki', 'dekenat', 'kevikepan', 'master-pastor', 'riwayat-pastor', 'sakramen', 'pengajuan-sakramen']);
        $needsUmatReferences = in_array($slug, ['pengajuan-sakramen', 'sakramen', 'iuran', 'umat', 'data-umat'], true);
        $needsKontenReferences = $slug === 'konten';

        $keuskupanList = $needsKeuskupanReferences
            ? \Illuminate\Support\Facades\Cache::remember('ref_keuskupan_list_v2', 3600, function() {
                return \App\Models\Keuskupan::orderBy('nama_keuskupan')->get(['id_keuskupan', 'nama_keuskupan', 'kode_keuskupan']);
            })
            : [];

        $dekenatList = $needsDekenatReferences
            ? \Illuminate\Support\Facades\Cache::remember('ref_dekenat_list_v3', 3600, function() {
                return \App\Models\Dekenat::all();
            })
            : [];

        $provinsiList = $needsProvinsiReferences
            ? \Illuminate\Support\Facades\Cache::remember('ref_provinsi_list_v2', 3600, function() {
                return \App\Models\Provinsi::orderBy('nama_provinsi')->get(['id_provinsi', 'nama_provinsi']);
            })
            : [];

        $kabupatenList = $needsKabupatenReferences
            ? \Illuminate\Support\Facades\Cache::remember('ref_kabupaten_list_v2', 3600, function() {
                return \App\Models\Kabupaten::orderBy('nama_kabupaten')->get(['id_kabupaten', 'provinsi_id', 'nama_kabupaten']);
            })
            : [];

        $kecamatanList = [];
        if ($needsKecamatanReferences) {
            $kecamatanList = \Illuminate\Support\Facades\Cache::remember('ref_kecamatan_list_ntt_v1', 3600, function() {
                $nttKecIds = \App\Models\Kecamatan::whereHas('kabupaten.provinsi', function($q) {
                    $q->where('nama_provinsi', 'like', '%Nusa Tenggara Timur%')
                      ->orWhere('nama_provinsi', 'like', '%NTT%');
                })->pluck('id_kecamatan');

                if ($nttKecIds->isNotEmpty()) {
                    return \App\Models\Kecamatan::whereIn('id_kecamatan', $nttKecIds)
                        ->orderBy('nama_kecamatan')
                        ->get(['id_kecamatan', 'kabupaten_id', 'nama_kecamatan']);
                }
                return \App\Models\Kecamatan::orderBy('nama_kecamatan')->get(['id_kecamatan', 'kabupaten_id', 'nama_kecamatan']);
            });

            if ($slug === 'desa-kelurahan' && $kabupatenFilter) {
                $kecamatanList = $kecamatanList
                    ->filter(fn ($kecamatan) => (string) $kecamatan->kabupaten_id === (string) $kabupatenFilter)
                    ->values();
            }
        }

        $desaList = [];
        if ($slug === 'keuskupan' || $slug === 'paroki' || $slug === 'kapela' || $slug === 'stasi') {
            $desaList = \Illuminate\Support\Facades\Cache::remember('ref_desa_list_ntt_v3', 3600, function() {
                $nttKecIds = \App\Models\Kecamatan::whereHas('kabupaten.provinsi', function($q) {
                    $q->where('nama_provinsi', 'like', '%Nusa Tenggara Timur%')
                      ->orWhere('nama_provinsi', 'like', '%NTT%');
                })->pluck('id_kecamatan');

                if ($nttKecIds->isNotEmpty()) {
                    return \App\Models\DesaKelurahan::whereIn('kecamatan_id', $nttKecIds)->orderBy('nama_desa')->get(['id_desa', 'kecamatan_id', 'nama_desa']);
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
        $umatList = $needsUmatReferences
            ? \Illuminate\Support\Facades\Cache::remember('ref_umat_select_list_v1', 600, fn () => \App\Models\Umat::orderBy('nama_lengkap')->take(500)->get(['id', 'nama_lengkap', 'nik', 'no_kk_kw', 'handphone']))
            : [];

        if (str_contains($userRoleSlug, 'wilayah') && !empty($authUser?->wilayah_id)) {
            $wilayahList = $wilayahList->where('id', $authUser->wilayah_id)->values();
            $kubList = $kubList->where('wilayah_id', $authUser->wilayah_id)->values();
            $kapelaList = collect();
            if ($needsUmatReferences) {
                $umatList = \App\Models\Umat::whereHas('kk', fn($kQ) => $kQ->where('wilayah_id', $authUser->wilayah_id))
                    ->orderBy('nama_lengkap')
                    ->get(['id', 'nama_lengkap', 'nik', 'no_kk_kw', 'handphone']);
            }
        } elseif ((str_contains($userRoleSlug, 'kapela') || str_contains($userRoleSlug, 'stasi')) && !empty($authUser?->kapela_id)) {
            $kapelaList = $kapelaList->where('id', $authUser->kapela_id)->values();
            $kubList = $kubList->where('kapela_id', $authUser->kapela_id)->values();
            $wilayahList = collect();
            if ($needsUmatReferences) {
                $umatList = \App\Models\Umat::whereHas('kk', fn($kQ) => $kQ->where('kapela_id', $authUser->kapela_id))
                    ->orderBy('nama_lengkap')
                    ->get(['id', 'nama_lengkap', 'nik', 'no_kk_kw', 'handphone']);
            }
        } elseif (str_contains($userRoleSlug, 'kub') && !empty($authUser?->kub_id)) {
            $kubList = $kubList->where('id', $authUser->kub_id)->values();
            $wilayahList = collect();
            $kapelaList = collect();
            if ($needsUmatReferences) {
                $umatList = \App\Models\Umat::whereHas('kk', fn($kQ) => $kQ->where('kub_id', $authUser->kub_id))
                    ->orderBy('nama_lengkap')
                    ->get(['id', 'nama_lengkap', 'nik', 'no_kk_kw', 'handphone']);
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

        return Inertia::render('Inertia/GenericModule', [
            'title' => $config['title'],
            'moduleKey' => $slug,
            'columns' => $config['columns'],
            'items' => $items,
            'role' => $resolvedRole,
            'hasImport' => $config['has_import'] ?? false,
            'hasExport' => $config['has_export'] ?? true,
            'hasPdf' => $config['has_pdf'] ?? true,
            'hasCreate' => $config['has_create'] ?? true,
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
        if ($slug === 'direktori-dpp' || $slug === 'direktori_dpp') {
            $data = $this->normalizeDirektoriDppPayload($data);
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

        $firstSegment = explode('/', trim($request->path(), '/'))[0] ?? 'superadmin';
        if (in_array($slug, ['kk-katolik', 'kk', 'keluarga'], true)) {
            return redirect("/{$firstSegment}/kk-katolik")->with('success', 'Data Kartu Keluarga (KK) Katolik berhasil ditambahkan.');
        }

        return back()->with('success', 'Data ' . $config['title'] . ' berhasil ditambahkan.');
    }


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

        $decodedId = decode_id($id) ?: $id;

        // Try by the model's declared primary key first
        $item = $modelClass::where($pk, $decodedId)->first();

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
                    $found = $modelClass::where($cand, $decodedId)->first();
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
        if ($slug === 'direktori-dpp' || $slug === 'direktori_dpp') {
            $data = $this->normalizeDirektoriDppPayload($data);
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

        $firstSegment = explode('/', trim($request->path(), '/'))[0] ?? 'superadmin';
        if (in_array($slug, ['kk-katolik', 'kk', 'keluarga'], true)) {
            return redirect("/{$firstSegment}/kk-katolik")->with('success', 'Data Kartu Keluarga (KK) Katolik berhasil diperbarui.');
        }

        return back()->with('success', 'Data ' . $config['title'] . ' berhasil diperbarui.');
    }


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
        $decodedId = decode_id($id) ?: $id;
        $item = $modelClass::where($pk, $decodedId)->first();
        if (!$item && is_numeric($decodedId)) {
            $item = $modelClass::find($decodedId);
        }
        if (!$item) {
            $item = $modelClass::where('id', $decodedId)->first();
        }
        if (!$item && in_array('slug', \Illuminate\Support\Facades\Schema::getColumnListing($modelInstance->getTable()))) {
            $item = $modelClass::where('slug', $id)->first();
        }

        if (!$item) {
            return back()->with('error', 'Data tidak ditemukan.');
        }

        $firstSegment = explode('/', trim($request->path(), '/'))[0] ?? 'superadmin';
        $userRoleSlug = strtolower(auth()->user()?->role?->slug ?? auth()->user()?->role?->nama_role ?? '');
        if (in_array($slug, ['umat', 'data-umat'], true) && (in_array($firstSegment, ['wilayah', 'kapela', 'stasi'], true) || str_contains($userRoleSlug, 'wilayah') || str_contains($userRoleSlug, 'kapela') || str_contains($userRoleSlug, 'stasi'))) {
            return back()->with('error', 'Akses ditolak. Pengelolaan data Umat (tambah/edit/hapus) hanya dapat dilakukan pada tingkat KUB atau Sekretariat Paroki.');
        }

        if ($slug === 'user' && auth()->id() && (int) auth()->id() === (int) $item->getKey()) {
            return back()->with('error', 'Akun yang sedang digunakan tidak dapat dihapus.');
        }

        $this->logAudit('DELETE_' . strtoupper($slug), $slug, $item->getKey());
        $item->delete();
        $this->clearFastAccessCache();

        return back()->with('success', 'Data ' . $config['title'] . ' berhasil dihapus.');
    }


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
        [$headings, $rows] = $this->moduleExportPayload($request, $slug, $config);

        if (in_array($format, ['template', 'template-excel'], true)) {
            $fileName = 'template-import-' . Str::slug($config['title']) . '.xlsx';

            return $this->styledExcelDownload(
                $config['title'],
                $headings,
                $this->moduleTemplateRows($slug, $headings),
                $fileName,
                true
            );
        }

        if (in_array($format, ['excel', 'xlsx'], true)) {
            $fileName = Str::slug($config['title']) . '-' . now()->format('Ymd-His') . '.xlsx';

            return $this->styledExcelDownload($config['title'], $headings, $rows, $fileName);
        }

        if (in_array($format, ['pdf', 'print', 'cetak'], true)) {
            $defaultParokiId = $this->defaultParokiIdFromProfile();
            $paroki = Paroki::with('keuskupan')->find($defaultParokiId)
                ?? Paroki::with('keuskupan')->first();
            $keuskupan = $paroki?->keuskupan ?? \App\Models\Keuskupan::first();
            $profilParoki = \App\Models\ProfilParoki::first();

            $keuskupanLogo = $keuskupan?->logo ?: '/uploads/keuskupan/logo_keuskupan_kupang.svg';
            $parokiLogo = $paroki?->logo ?: '/assets/uploads/profil/logo_paroki_1787370466.jpeg';

            $summaryLabel = in_array($slug, ['umat', 'data-umat'], true) ? 'Total Jiwa / Umat' : (in_array($slug, ['kk-katolik', 'kk', 'keluarga'], true) ? 'Total Kepala Keluarga' : 'Total Data');

            return response()->view('exports.keuskupan-print', [
                'title' => $config['title'],
                'headings' => $headings,
                'rows' => $rows,
                'paroki' => $paroki,
                'keuskupan' => $keuskupan,
                'profilParoki' => $profilParoki,
                'keuskupanLogo' => $keuskupanLogo,
                'parokiLogo' => $parokiLogo,
                'summaryNumber' => number_format($rows->count(), 0, ',', '.'),
                'summaryLabel' => $summaryLabel,
                'printedAt' => now()->format('d/m/Y H:i'),
            ]);
        }

        abort(404);
    }


    public function importModule(Request $request, string $slug)
    {
        if (in_array($slug, ['kk-katolik', 'kk', 'keluarga'], true)) {
            $this->ensureKkKatolikColumns();
        }
        $moduleMap = $this->getModuleMap();
        if (!isset($moduleMap[$slug])) {
            abort(404);
        }

        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv,txt', 'max:10240'],
        ]);

        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($request->file('file')->getRealPath());
        $firstSheet = $spreadsheet->getSheet(0);
        $allRows = $firstSheet->toArray(null, true, true, true);
        if (count($allRows) < 2) {
            return back()->with('error', 'File impor kosong atau belum memiliki baris data.');
        }

        $config = $moduleMap[$slug];
        $modelClass = $config['model'];
        $table = (new $modelClass)->getTable();
        $validColumns = $this->schemaColumns($table);
        $fieldMap = $this->moduleImportFieldMap($slug, $config, $validColumns);

        // Cari baris header secara cerdas (baris yang paling banyak cocok dengan fieldMap)
        $headerRowIndex = null;
        $headers = [];
        $rawHeaders = [];
        $maxMatchedFields = 0;

        foreach ($allRows as $rowIndex => $row) {
            $currentMatched = 0;
            $tempHeaders = [];
            $tempRawHeaders = [];
            foreach ($row as $column => $label) {
                if (empty($label)) continue;
                $normalizedHeading = $this->normalizeImportHeading((string) $label);
                $tempRawHeaders[$column] = $normalizedHeading;
                if (isset($fieldMap[$normalizedHeading])) {
                    $currentMatched++;
                    $tempHeaders[$column] = $fieldMap[$normalizedHeading];
                }
                if (str_starts_with($normalizedHeading, 'anggota_')) {
                    $currentMatched++;
                }
            }

            if ($currentMatched > $maxMatchedFields) {
                $maxMatchedFields = $currentMatched;
                $headerRowIndex = $rowIndex;
                $headers = $tempHeaders;
                $rawHeaders = $tempRawHeaders;
            }
        }

        if (!$headerRowIndex || $maxMatchedFields === 0) {
            return back()->with('error', 'Format kolom header tidak dikenali. Silakan unduh Template Excel resmi untuk panduan kolom.');
        }

        // Ambil data setelah baris header
        $sheetRows = [];
        $passedHeader = false;
        foreach ($allRows as $rowIndex => $row) {
            if ($rowIndex == $headerRowIndex) {
                $passedHeader = true;
                continue;
            }
            if ($passedHeader) {
                $sheetRows[] = $row;
            }
        }

        $created = 0;
        $updated = 0;
        $skipped = 0;
        $seenLookups = [];

        DB::transaction(function () use ($sheetRows, $headers, $rawHeaders, $validColumns, $slug, $modelClass, &$created, &$updated, &$skipped, &$seenLookups) {
            foreach ($sheetRows as $row) {
                $payload = $this->mapModuleImportRow($row, $headers, $validColumns);
                $kkMembers = in_array($slug, ['kk-katolik', 'kk', 'keluarga'], true)
                    ? $this->extractKkImportMembers($row, $rawHeaders)
                    : [];

                if ($slug === 'kuasi-paroki') {
                    $payload = $this->normalizeKuasiParokiPayload($payload);
                }

                $lookup = $this->moduleImportLookup($slug, $payload, $validColumns);
                if (empty($lookup)) {
                    $skipped++;
                    continue;
                }

                $lookupKey = Str::lower(key($lookup) . ':' . trim((string) current($lookup)));
                if (in_array($slug, ['kk-katolik', 'kk', 'keluarga'], true)) {
                    $identityKeys = $this->kkImportIdentityKeys($payload, $validColumns);
                    if (empty($identityKeys)) {
                        $skipped++;
                        continue;
                    }
                    foreach ($identityKeys as $identityKey) {
                        if (isset($seenLookups[$identityKey])) {
                            $skipped++;
                            continue 2;
                        }
                    }
                    foreach ($identityKeys as $identityKey) {
                        $seenLookups[$identityKey] = true;
                    }
                } else {
                    if (isset($seenLookups[$lookupKey])) {
                        $skipped++;
                        continue;
                    }
                    $seenLookups[$lookupKey] = true;
                }

                $existing = in_array($slug, ['kk-katolik', 'kk', 'keluarga'], true)
                    ? $this->findExistingKkForImport($modelClass, $payload, $validColumns)
                    : $modelClass::where($lookup)->first();
                if ($existing) {
                    $existing->update($payload);
                    if (!empty($kkMembers)) {
                        $this->syncKkAnggota($existing, $kkMembers);
                    }
                    $updated++;
                } else {
                    $createdItem = $modelClass::create($payload);
                    if (!empty($kkMembers)) {
                        $this->syncKkAnggota($createdItem, $kkMembers);
                    }
                    $created++;
                }
            }
        });

        $this->clearFastAccessCache();

        return back()->with('success', "Import {$config['title']} selesai. Baru: {$created}, diperbarui: {$updated}, dilewati: {$skipped}.");
    }


    private function schemaColumns(string $table): array
    {
        return Cache::remember("schema_columns_{$table}", 86400, fn () => \Illuminate\Support\Facades\Schema::getColumnListing($table));
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
            } elseif (in_array($slug, ['umat', 'data-umat'], true)) {
                $query->where(function ($q) use ($authUser, $tableColumns) {
                    if (in_array('wilayah_id', $tableColumns, true)) {
                        $q->where('wilayah_id', $authUser->wilayah_id);
                    }
                    $q->orWhereHas('kk', fn($kkQ) => $kkQ->where('wilayah_id', $authUser->wilayah_id));
                });
            } elseif (in_array($slug, ['sakramen', 'pengajuan-sakramen'], true)) {
                if (in_array('wilayah_id', $tableColumns, true)) {
                    $query->where('wilayah_id', $authUser->wilayah_id);
                } elseif (in_array('umat_id', $tableColumns, true)) {
                    $query->whereHas('umat', function ($uQ) use ($authUser) {
                        $uQ->whereHas('kk', fn($kQ) => $kQ->where('wilayah_id', $authUser->wilayah_id));
                    });
                }
            }
        } elseif ((str_contains($userRoleSlug, 'kapela') || str_contains($userRoleSlug, 'stasi')) && !empty($authUser?->kapela_id)) {
            if (in_array($slug, ['kapela', 'stasi']) && in_array('id', $tableColumns, true)) {
                $query->where('id', $authUser->kapela_id);
            } elseif (in_array('kapela_id', $tableColumns, true)) {
                $query->where('kapela_id', $authUser->kapela_id);
            } elseif (in_array($slug, ['umat', 'data-umat'], true)) {
                $query->where(function ($q) use ($authUser, $tableColumns) {
                    if (in_array('kapela_id', $tableColumns, true)) {
                        $q->where('kapela_id', $authUser->kapela_id);
                    }
                    $q->orWhereHas('kk', fn($kkQ) => $kkQ->where('kapela_id', $authUser->kapela_id));
                });
            } elseif (in_array($slug, ['sakramen', 'pengajuan-sakramen'], true)) {
                if (in_array('kapela_id', $tableColumns, true)) {
                    $query->where('kapela_id', $authUser->kapela_id);
                } elseif (in_array('umat_id', $tableColumns, true)) {
                    $query->whereHas('umat', function ($uQ) use ($authUser) {
                        $uQ->whereHas('kk', fn($kQ) => $kQ->where('kapela_id', $authUser->kapela_id));
                    });
                }
            }
        } elseif (str_contains($userRoleSlug, 'kub') && !empty($authUser?->kub_id)) {
            if ($slug === 'kub' && in_array('id', $tableColumns, true)) {
                $query->where('id', $authUser->kub_id);
            } elseif (in_array('kub_id', $tableColumns, true)) {
                $query->where('kub_id', $authUser->kub_id);
            } elseif (in_array($slug, ['umat', 'data-umat'], true)) {
                $query->where(function ($q) use ($authUser, $tableColumns) {
                    if (in_array('kub_id', $tableColumns, true)) {
                        $q->where('kub_id', $authUser->kub_id);
                    }
                    $q->orWhereHas('kk', fn($kkQ) => $kkQ->where('kub_id', $authUser->kub_id));
                });
            } elseif (in_array($slug, ['sakramen', 'pengajuan-sakramen'], true)) {
                if (in_array('kub_id', $tableColumns, true)) {
                    $query->where('kub_id', $authUser->kub_id);
                } elseif (in_array('umat_id', $tableColumns, true)) {
                    $query->whereHas('umat', function ($uQ) use ($authUser) {
                        $uQ->whereHas('kk', fn($kQ) => $kQ->where('kub_id', $authUser->kub_id));
                    });
                }
            }
        }

        $search = $request->input('search');
        if ($search) {
            $query->where(function ($q) use ($config, $search, $tableColumns) {
                $isFirst = true;
                foreach ($config['columns'] as $col) {
                    $columnToSearch = null;
                    if (in_array($col['key'], $tableColumns, true)) {
                        $columnToSearch = $col['key'];
                    } elseif (isset($col['altKey']) && in_array($col['altKey'], $tableColumns, true)) {
                        $columnToSearch = $col['altKey'];
                    }

                    if ($columnToSearch) {
                        $method = $isFirst ? 'where' : 'orWhere';
                        $q->{$method}($columnToSearch, 'like', "%{$search}%");
                        $isFirst = false;
                    }
                }
            });
        }

        if (in_array($slug, ['umat', 'data-umat'], true)) {
            $wilayahIdFilter = $request->input('wilayah_id');
            $kapelaIdFilter = $request->input('kapela_id');
            $kubIdFilter = $request->input('kub_id');

            if ($wilayahIdFilter) {
                $query->where(function ($q) use ($wilayahIdFilter, $tableColumns) {
                    if (in_array('wilayah_id', $tableColumns, true)) {
                        $q->where('wilayah_id', $wilayahIdFilter);
                    }
                    $q->orWhereHas('kk', function ($kkQ) use ($wilayahIdFilter) {
                        $kkQ->where('wilayah_id', $wilayahIdFilter);
                    });
                });
            }

            if ($kapelaIdFilter) {
                $query->where(function ($q) use ($kapelaIdFilter, $tableColumns) {
                    if (in_array('kapela_id', $tableColumns, true)) {
                        $q->where('kapela_id', $kapelaIdFilter);
                    }
                    $q->orWhereHas('kk', function ($kkQ) use ($kapelaIdFilter) {
                        $kkQ->where('kapela_id', $kapelaIdFilter);
                    });
                });
            }

            if ($kubIdFilter) {
                $query->where(function ($q) use ($kubIdFilter, $tableColumns) {
                    if (in_array('kub_id', $tableColumns, true)) {
                        $q->where('kub_id', $kubIdFilter);
                    }
                    $q->orWhereHas('kk', function ($kkQ) use ($kubIdFilter) {
                        $kkQ->where('kub_id', $kubIdFilter);
                    });
                });
            }
        }

        foreach (['keuskupan_id', 'dekenat_id', 'paroki_id', 'provinsi_id', 'kabupaten_id', 'kecamatan_id', 'wilayah_id', 'kapela_id', 'kub_id'] as $filter) {
            if (in_array($slug, ['umat', 'data-umat'], true) && in_array($filter, ['wilayah_id', 'kapela_id', 'kub_id'], true)) {
                continue;
            }
            $value = $request->input($filter);
            if ($value && in_array($filter, $tableColumns, true)) {
                $query->where($filter, $value);
            }
        }

        $headings = collect($config['columns'])
            ->reject(fn ($col) => !empty($col['isImage']) || !empty($col['isIcon']))
            ->pluck('label')
            ->values()
            ->all();

        if (in_array($slug, ['kk-katolik', 'kk', 'keluarga'], true)) {
            $headings = array_merge([
                'No KK Paroki',
                'No KK Sipil',
                'NIK Kepala Keluarga',
                'Nama Lengkap Kepala Keluarga',
                'Nama Baptis Kepala Keluarga',
                'Nama Pasangan',
                'Stasi / Kapela',
                'Wilayah Pastoral',
                'Lingkungan',
                'KUB / KBG',
                'Alamat Domisili',
                'RT',
                'RW',
                'Desa / Kelurahan',
                'Kecamatan',
                'Kabupaten / Kota',
                'No Telepon / WA',
                'Email',
                'Status Kepemilikan Rumah',
                'Kategori Ekonomi Pastoral',
                'Bantuan Pastoral Khusus',
                'Pekerjaan',
                'Pendidikan',
                'Golongan Darah',
                'Status Verifikasi',
                'Status KK',
            ], $this->kkMemberImportHeadings(2));
        }

        $statusColumn = collect(['status', 'status_aktif', 'StatusAktif'])->first(fn ($column) => in_array($column, $tableColumns, true));
        if ($statusColumn && !in_array('Status', $headings, true)) {
            $headings[] = 'Status';
        }

        $orderColumn = collect($config['columns'])
            ->map(fn ($col) => in_array($col['key'], $tableColumns, true) ? $col['key'] : ($col['altKey'] ?? null))
            ->first(fn ($column) => $column && in_array($column, $tableColumns, true));
        if ($orderColumn) {
            $query->orderBy($orderColumn);
        }

        $rows = $query->get()->map(function ($item) use ($config, $statusColumn, $slug) {
            if (in_array($slug, ['kk-katolik', 'kk', 'keluarga'], true)) {
                $familyRow = [
                    $item->no_kk_kw,
                    $item->no_kk_dukcapil,
                    $item->nik_pemilik,
                    $item->nama_lahir_pemilik,
                    $item->nama_baptis_pemilik,
                    $item->nama_pasangan,
                    $item->kapela?->nama_kapela ?? '',
                    $item->wilayah?->nama_wilayah ?? '',
                    $item->lingkungan?->nama_lingkungan ?? '',
                    $item->kub?->nama_kub ?? '',
                    $item->alamat_sekarang,
                    $item->rt,
                    $item->rw,
                    $item->desa_kelurahan,
                    $item->kecamatan,
                    $item->kota_kabupaten,
                    $item->handphone,
                    $item->email,
                    $item->status_kepemilikan_rumah ?? 'Milik Sendiri',
                    $item->kategori_ekonomi ?? 'Sejahtera / Mandiri',
                    $item->bantuan_pastoral ?? '-',
                    $item->pekerjaan ?? '',
                    $item->pendidikan ?? '',
                    $item->golongan_darah ?? '',
                    $item->status_verifikasi,
                    $item->status_kk,
                ];

                return array_merge($familyRow, $this->kkMemberExportValues($item, 2));
            }

            $row = [];
            foreach ($config['columns'] as $col) {
                if (!empty($col['isImage']) || !empty($col['isIcon'])) {
                    continue;
                }
                $row[] = $this->moduleExportValue($item, $col);
            }
            if ($statusColumn) {
                $row[] = $this->formatExportStatus($item->{$statusColumn});
            }
            return $row;
        });

        return [$headings, $rows];
    }


    private function moduleExportQuery(string $slug, string $modelClass)
    {
        $query = $modelClass::query();

        if ($slug === 'keuskupan') {
            $query->with(['provinsi', 'kabupaten', 'kecamatan', 'desa']);
        } elseif ($slug === 'dekenat' || $slug === 'kevikepan') {
            $query->with(['keuskupan', 'parokis']);
        } elseif ($slug === 'paroki') {
            $query->with(['keuskupan', 'dekenat', 'provinsi', 'kabupaten', 'kecamatan', 'desa']);
        } elseif ($slug === 'kuasi-paroki') {
            $query->with(['paroki.dekenat']);
        } elseif ($slug === 'kapela' || $slug === 'stasi') {
            $with = [];
            if (Schema::hasColumn('kapela', 'paroki_id')) {
                $with[] = 'paroki';
            }
            if (Schema::hasColumn('kub', 'kapela_id')) {
                $with[] = 'kubs';
            }
            if (Schema::hasColumn('wilayah', 'kapela_id')) {
                $with[] = 'wilayahs';
            }
            if (!empty($with)) {
                $query->with($with);
            }
        } elseif ($slug === 'wilayah') {
            $query->with(['paroki', 'kapela', 'kubs']);
        } elseif ($slug === 'kub') {
            $query->with(['wilayah', 'kapela', 'paroki']);
        } elseif ($slug === 'provinsi') {
            $query->with(['kabupatens']);
        } elseif ($slug === 'kabupaten') {
            $query->with(['provinsi', 'kecamatans']);
        } elseif ($slug === 'kecamatan') {
            $query->with(['kabupaten', 'desas']);
        } elseif ($slug === 'desa-kelurahan') {
            $query->with(['kecamatan']);
        } elseif (in_array($slug, ['kk-katolik', 'kk', 'keluarga'], true)) {
            $query->with(['wilayah', 'kapela', 'kub', 'lingkungan', 'anggota']);
        }

        return $query;
    }


    private function moduleExportValue($item, array $col)
    {
        if (!empty($col['isRelationLink'])) {
            $relation = $item->{$col['relation']} ?? null;
            return $relation instanceof \Illuminate\Support\Collection ? $relation->count() : ($relation ? 1 : 0);
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

        if (in_array('nama_lahir', $validColumns, true) && empty($payload['nama_lahir'])) {
            $payload['nama_lahir'] = $payload['nama_lengkap'] ?? $payload['nama_baptis'] ?? '-';
        }

        if (in_array('nama_lengkap', $validColumns, true) && empty($payload['nama_lengkap'])) {
            $payload['nama_lengkap'] = $payload['nama_lahir'] ?? $payload['nama_baptis'] ?? '-';
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

        if (in_array($slug, ['umat', 'data-umat'], true)) {
            if (!empty($payload['nik']) && strlen(preg_replace('/\D+/', '', (string) $payload['nik'])) >= 8) {
                return ['nik' => preg_replace('/\D+/', '', (string) $payload['nik'])];
            }
            if (!empty($payload['niu'])) {
                return ['niu' => trim((string) $payload['niu'])];
            }
            if (!empty($payload['nama_lengkap'])) {
                return ['nama_lengkap' => trim((string) $payload['nama_lengkap'])];
            }

            return [];
        }

        if (in_array($slug, ['user', 'users'], true)) {
            if (!empty($payload['email'])) {
                return ['email' => strtolower(trim((string) $payload['email']))];
            }
            if (!empty($payload['username'])) {
                return ['username' => strtolower(trim((string) $payload['username']))];
            }
            return [];
        }

        if (in_array($slug, ['master-pastor', 'pastor', 'riwayat-pastor'], true)) {
            foreach (['nama_pastor', 'nama'] as $col) {
                if (!empty($payload[$col])) {
                    return [$col => trim((string) $payload[$col])];
                }
            }
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
            'lingkungan' => ['kode_lingkungan'],
            'kub' => ['kode_kub'],
            'provinsi' => ['kode_provinsi'],
            'kabupaten' => ['kode_kabupaten'],
            'kecamatan' => ['kode_kecamatan'],
            'desa-kelurahan' => ['kode_desa', 'kode_desa_kelurahan'],
            'kategori-konten' => ['slug', 'kode_kategori'],
            'konten' => ['slug'],
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
            'lingkungan' => ['nama_lingkungan'],
            'kub' => ['nama_kub'],
            'provinsi' => ['nama_provinsi'],
            'kabupaten' => ['nama_kabupaten'],
            'kecamatan' => ['nama_kecamatan'],
            'desa-kelurahan' => ['nama_desa', 'nama_kelurahan'],
            'kategori-konten' => ['nama_kategori', 'nama'],
            'konten' => ['judul', 'title'],
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

        // Generic fallback for any other table
        foreach ($validColumns as $col) {
            if (str_starts_with($col, 'nama_') || str_starts_with($col, 'kode_') || $col === 'nama' || $col === 'judul') {
                if (!empty($payload[$col])) {
                    return [$col => $payload[$col]];
                }
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


    protected function normalizeDirektoriDppPayload(array $data): array
    {
        if (isset($data['seksi']) && !empty($data['seksi'])) {
            $data['bidang'] = $data['seksi'];
        } elseif (isset($data['bidang']) && !empty($data['bidang'])) {
            $data['seksi'] = $data['bidang'];
        }
        if (isset($data['nama']) && !isset($data['nama_lengkap'])) {
            $data['nama_lengkap'] = $data['nama'];
        } elseif (isset($data['nama_lengkap']) && !isset($data['nama'])) {
            $data['nama'] = $data['nama_lengkap'];
        }
        if (isset($data['status'])) {
            $data['status_aktif'] = $data['status'];
        } elseif (isset($data['status_aktif'])) {
            $data['status'] = $data['status_aktif'];
        }
        if (isset($data['kontak']) && !isset($data['no_hp'])) {
            $data['no_hp'] = $data['kontak'];
        }
        if (isset($data['periode']) && str_contains((string)$data['periode'], '-')) {
            $parts = explode('-', (string)$data['periode']);
            $data['periode_mulai'] = trim($parts[0] ?? '');
            $data['periode_selesai'] = trim($parts[1] ?? '');
        }
        if (empty($data['paroki_id']) && Schema::hasColumn('direktori_dpp', 'paroki_id')) {
            $data['paroki_id'] = $this->defaultParokiIdFromProfile();
        }
        return $data;
    }

}
