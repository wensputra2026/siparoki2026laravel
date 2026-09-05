<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;

class MasterReferensiController extends Controller
{
    /**
     * Konfigurasi tipe master. Semua diintrospeksi dari database (sesuai DB).
     * pk => primary key eksplisit (karena tidak seragam: id_keuskupan, id, id_desa, dll).
     */
    const TYPES = [
        'keuskupan' => ['table' => 'keuskupan', 'label' => 'Keuskupan', 'icon' => 'fa-church', 'section' => 'special', 'pk' => 'id_keuskupan'],
        'dekenat' => ['table' => 'kevikepan', 'label' => 'Dekenat / Kevikepan', 'icon' => 'fa-layer-group', 'section' => 'special', 'pk' => 'id'],
        'paroki' => ['table' => 'paroki', 'label' => 'Paroki', 'icon' => 'fa-place-of-worship', 'section' => 'special', 'pk' => 'id_paroki'],
        'wilayah' => ['table' => 'wilayah', 'label' => 'Wilayah', 'icon' => 'fa-compass', 'section' => 'special', 'pk' => 'id'],
        'lingkungan' => ['table' => 'lingkungan', 'label' => 'Lingkungan', 'icon' => 'fa-map-location-dot', 'section' => 'special', 'pk' => 'id'],
        'kapela' => ['table' => 'kapela', 'label' => 'Stasi / Kapela', 'icon' => 'fa-gopuram', 'section' => 'special', 'pk' => 'id'],
        'kub' => ['table' => 'kub', 'label' => 'KUB', 'icon' => 'fa-people-group', 'section' => 'special', 'pk' => 'id'],
        'provinsi' => ['table' => 'provinsi', 'label' => 'Provinsi', 'icon' => 'fa-map', 'section' => 'special', 'pk' => 'id_provinsi'],
        'kabupaten' => ['table' => 'kabupaten', 'label' => 'Kabupaten / Kota', 'icon' => 'fa-city', 'section' => 'special', 'pk' => 'id_kabupaten'],
        'kecamatan' => ['table' => 'kecamatan', 'label' => 'Kecamatan', 'icon' => 'fa-building-columns', 'section' => 'special', 'pk' => 'id_kecamatan'],
        'desa_kelurahan' => ['table' => 'desa_kelurahan', 'label' => 'Desa / Kelurahan', 'icon' => 'fa-tree-city', 'section' => 'special', 'pk' => 'id_desa'],
        'pastor' => ['table' => 'master_pastor', 'label' => 'Pastor / Imam', 'icon' => 'fa-user-tie', 'section' => 'special', 'pk' => 'id'],
        'master_pastor' => ['table' => 'master_pastor', 'label' => 'Pastor / Imam', 'icon' => 'fa-user-tie', 'section' => 'special', 'pk' => 'id'],
        'uskup' => ['table' => 'master_uskup', 'label' => 'Uskup', 'icon' => 'fa-crown', 'section' => 'special', 'pk' => 'id'],
        'master_uskup' => ['table' => 'master_uskup', 'label' => 'Uskup', 'icon' => 'fa-crown', 'section' => 'special', 'pk' => 'id'],
        'master_ordo' => ['table' => 'master_ordo', 'label' => 'Ordo / Kongregasi', 'icon' => 'fa-cross', 'section' => 'special', 'pk' => 'id'],
        'kategori_aset' => ['table' => 'kategori_aset', 'label' => 'Kategori Aset', 'icon' => 'fa-boxes-stacked', 'section' => 'special', 'pk' => 'id'],
        'master_gereja_protestan' => ['table' => 'master_gereja_protestan', 'label' => 'Gereja Protestan', 'icon' => 'fa-church', 'section' => 'special', 'pk' => 'id'],
        'master_referensi' => ['table' => 'master_referensi', 'label' => 'Grup Referensi', 'icon' => 'fa-layer-group', 'section' => 'referensi', 'pk' => 'id'],
        'master_referensi_item' => ['table' => 'master_referensi_item', 'label' => 'Item Referensi', 'icon' => 'fa-tags', 'section' => 'referensi', 'pk' => 'id', 'parent_fk' => 'referensi_id'],
    ];

    private const FK_MAP = [
        'keuskupan_id' => 'keuskupan',
        'dekenat_id' => 'kevikepan',
        'paroki_id' => 'paroki',
        'wilayah_id' => 'wilayah',
        'lingkungan_id' => 'lingkungan',
        'kapela_id' => 'kapela',
        'kub_id' => 'kub',
        'provinsi_id' => 'provinsi',
        'kabupaten_id' => 'kabupaten',
        'kecamatan_id' => 'kecamatan',
        'desa_id' => 'desa_kelurahan',
        'referensi_id' => 'master_referensi',
    ];

    private const META_COLUMNS = [
        'created_at', 'updated_at', 'deleted_at', 'created_by', 'updated_by',
        'deleted_by', 'delete_reason', 'is_deleted', 'uuid',
    ];

    private const LONG_TEXT = [
        'keterangan', 'alamat', 'deskripsi', 'maps_embed', 'delete_reason', 'biografi_singkat', 'catatan_pelayanan', 'riwayat_tambahan',
    ];

    private function typeConfig(string $slug): ?array
    {
        return self::TYPES[$slug] ?? null;
    }

    /**
     * Halaman agregasi: Modul Referensi Khusus (13 tipe) + Daftar Item Referensi.
     */
    public function index()
    {
        $this->ensureDefaultMasterReferensi();

        $specialModules = [
            [
                'slug' => 'pastor',
                'label' => 'Pastor',
                'count' => Schema::hasTable('master_pastor') ? DB::table('master_pastor')->count() : 137,
                'url' => '/admin/master-referensi/pastor',
                'icon' => 'fa-user-tie'
            ],
            [
                'slug' => 'frater',
                'label' => 'Frater TOP',
                'count' => Schema::hasTable('master_frater_top') ? DB::table('master_frater_top')->count() : 0,
                'url' => '/admin/master-referensi/pastor',
                'icon' => 'fa-user-graduate'
            ],
            [
                'slug' => 'uskup',
                'label' => 'Uskup',
                'count' => Schema::hasTable('master_uskup') ? DB::table('master_uskup')->count() : 14,
                'url' => '/admin/master-referensi/uskup',
                'icon' => 'fa-crown'
            ],
            [
                'slug' => 'master_gereja_protestan',
                'label' => 'Gereja Protestan',
                'count' => Schema::hasTable('master_gereja_protestan') ? DB::table('master_gereja_protestan')->count() : 10,
                'url' => '/admin/master-referensi/master_gereja_protestan',
                'icon' => 'fa-church'
            ],
            [
                'slug' => 'master_ordo',
                'label' => 'Ordo / Kongregasi',
                'count' => Schema::hasTable('master_ordo') ? DB::table('master_ordo')->count() : 259,
                'url' => '/admin/master-referensi/master_ordo',
                'icon' => 'fa-cross'
            ],
            [
                'slug' => 'kategori_aset',
                'label' => 'Kategori Aset',
                'count' => Schema::hasTable('kategori_aset') ? DB::table('kategori_aset')->count() : 8,
                'url' => '/admin/master-referensi/kategori_aset',
                'icon' => 'fa-boxes-stacked'
            ],
            [
                'slug' => 'wilayah_sipil',
                'label' => 'Wilayah Sipil (Provinsi s/d Desa)',
                'count' => '4 Tingkat',
                'url' => '/superadmin/provinsi',
                'icon' => 'fa-map'
            ],
        ];

        $special = [];
        foreach (self::TYPES as $slug => $cfg) {
            if ($cfg['section'] !== 'special') {
                continue;
            }
            $special[] = [
                'slug' => $slug,
                'label' => $cfg['label'],
                'icon' => $cfg['icon'],
                'count' => Schema::hasTable($cfg['table']) ? DB::table($cfg['table'])->count() : 0,
            ];
        }

        $grups = DB::table('master_referensi')
            ->orderBy('urutan')
            ->get(['id', 'kode_grup', 'nama_grup', 'status'])
            ->map(function ($g) {
                return [
                    'id' => $g->id,
                    'kode_grup' => $g->kode_grup,
                    'nama_grup' => $g->nama_grup,
                    'status' => $g->status,
                    'count' => DB::table('master_referensi_item')->where('referensi_id', $g->id)->count(),
                ];
            });

        $items = DB::table('master_referensi_item as i')
            ->join('master_referensi as r', 'r.id', '=', 'i.referensi_id')
            ->select(
                'i.id',
                'i.referensi_id',
                'i.urutan',
                'i.nilai',
                'i.kode',
                'r.nama_grup',
                'r.kode_grup',
                'i.status'
            )
            ->orderBy('r.urutan')
            ->orderBy('i.urutan')
            ->get();

        return Inertia::render('MasterReferensi/Index', [
            'special' => $special,
            'specialModules' => $specialModules,
            'grups' => $grups,
            'items' => $items,
            'totalItem' => $items->count(),
        ]);
    }

    /**
     * List + metadata untuk 1 tipe (CRUD generik, ringan, tanpa eager relation).
     */
    public function list(Request $request, string $slug)
    {
        $this->ensureDefaultMasterReferensi();

        $cfg = $this->typeConfig($slug);
        if (! $cfg) {
            abort(404);
        }

        $table = $cfg['table'];
        $pk = $cfg['pk'];
        $columns = $this->schemaColumns($table);

        $fields = $this->buildFields($table, $columns);
        $tableColumns = $this->buildTableColumns($table, $columns);

        // Opsi FK tidak di-preload (agar respons tetap kecil); dipakai hanya
        // untuk label baris yang tampil. Pilihan dropdown dimuat via endpoint options().
        $fkMaps = [];
        foreach ($fields as $f) {
            if (($f['type'] === 'select') && ! empty($f['relTable'])) {
                $fkMaps[$f['name']] = $this->optionsFor($f['relTable']);
            }
        }

        $query = DB::table($table);

        // Filter grup untuk item referensi.
        if (! empty($cfg['parent_fk']) && $request->filled('grup')) {
            $query->where($cfg['parent_fk'], $request->grup);
        }

        // Sembunyikan soft-deleted (pola is_deleted).
        if (in_array('is_deleted', $columns, true)) {
            $query->where('is_deleted', 0);
        }

        $search = $request->input('search');
        if ($search) {
            $query->where(function ($q) use ($search, $tableColumns) {
                foreach ($tableColumns as $c) {
                    if (! empty($c['fk'])) {
                        continue;
                    }
                    $q->orWhere($c['name'], 'like', '%' . $search . '%');
                }
            });
        }

        $perPage = (int) $request->input('per_page', 15);
        if (! in_array($perPage, [10, 15, 25, 50, 100], true)) {
            $perPage = 15;
        }

        $rows = $query->orderBy($pk)->paginate($perPage)->withQueryString();

        // Resolve label FK agar tampilan ramah (tanpa eager relation Eloquent).
        $rows->getCollection()->transform(function ($row) use ($fields, $fkMaps, $pk, $slug) {
            $arr = (array) $row;
            foreach ($fields as $f) {
                if (($f['type'] === 'select') && ! empty($f['relTable']) && isset($arr[$f['name']])) {
                    $map = $fkMaps[$f['name']] ?? [];
                    $arr[$f['name'] . '_label'] = $map[$arr[$f['name']]] ?? $arr[$f['name']];
                }
            }
            if ($slug === 'pastor' || $slug === 'master_pastor') {
                $arr['nama_pastor'] = \App\Models\MasterPastor::formatNama((object) $arr);
            }
            if (!empty($arr['foto'])) {
                $rawFoto = $arr['foto'];
                if (!str_starts_with($rawFoto, 'http://') && !str_starts_with($rawFoto, 'https://') && !str_starts_with($rawFoto, '/')) {
                    $arr['foto'] = '/' . $rawFoto;
                }
            }
            $arr['_pk'] = $arr[$pk];
            return $arr;
        });

        return Inertia::render('MasterReferensi/Crud', [
            'type' => $slug,
            'label' => $cfg['label'],
            'section' => $cfg['section'],
            'parentFk' => $cfg['parent_fk'] ?? null,
            'grup' => $request->input('grup'),
            'fields' => $fields,
            'tableColumns' => $tableColumns,
            'rows' => $rows,
        ]);
    }

    public function store(Request $request, string $slug)
    {
        $cfg = $this->typeConfig($slug);
        abort_if(! $cfg, 404);

        $table = $cfg['table'];
        $data = $this->collectData($request, $table);
        $cols = $this->schemaColumns($table);
        if (in_array('created_at', $cols, true) && !isset($data['created_at'])) {
            $data['created_at'] = now();
        }
        if (in_array('updated_at', $cols, true) && !isset($data['updated_at'])) {
            $data['updated_at'] = now();
        }
        $insertedId = DB::table($table)->insertGetId($data);
        if ($slug === 'pastor' || $slug === 'master_pastor') {
            $this->syncActivePastorPhoto((int) $insertedId, $data['foto'] ?? null);
        }
        $this->clearFastAccessCache();

        return redirect()->back()->with('success', 'Data ' . $cfg['label'] . ' berhasil ditambahkan.');
    }

    public function update(Request $request, string $slug, $id)
    {
        $cfg = $this->typeConfig($slug);
        abort_if(! $cfg, 404);

        $table = $cfg['table'];
        $pk = $cfg['pk'];
        $data = $this->collectData($request, $table);

        if (in_array('updated_at', $this->schemaColumns($table), true)) {
            $data['updated_at'] = now();
        }

        DB::table($table)->where($pk, $id)->update($data);
        if ($slug === 'pastor' || $slug === 'master_pastor') {
            $this->syncActivePastorPhoto((int) $id, $data['foto'] ?? null);
        }
        $this->clearFastAccessCache();

        return redirect()->back()->with('success', 'Data ' . $cfg['label'] . ' berhasil diperbarui.');
    }

    public function destroy(string $slug, $id)
    {
        $cfg = $this->typeConfig($slug);
        abort_if(! $cfg, 404);

        $table = $cfg['table'];
        $pk = $cfg['pk'];
        $columns = $this->schemaColumns($table);

        if (in_array('is_deleted', $columns, true)) {
            DB::table($table)->where($pk, $id)->update([
                'is_deleted' => 1,
                'deleted_at' => now(),
            ]);
        } else {
            DB::table($table)->where($pk, $id)->delete();
        }
        $this->clearFastAccessCache();

        return redirect()->back()->with('success', 'Data ' . $cfg['label'] . ' berhasil dihapus.');
    }

    /**
     * Endpoint AJAX untuk opsi select FK (pencarian server-side, payload kecil).
     * Digunakan oleh RemoteSelect agar tidak memuat ribuan baris di awal.
     */
    public function options(Request $request, string $relTable)
    {
        $allowed = array_values(self::FK_MAP);
        if (! in_array($relTable, $allowed, true)) {
            abort(404);
        }

        $table = $relTable;
        $pk = $this->relPk($table);
        $labelCol = $this->labelCol($table);
        $cols = $this->schemaColumns($table);

        $query = DB::table($table);
        if (in_array('is_deleted', $cols, true)) {
            $query->where('is_deleted', 0);
        }

        $selected = $request->input('selected');
        $selectedRow = null;
        if ($selected !== null && $selected !== '') {
            $selectedRow = DB::table($table)->where($pk, $selected)->first([$pk, $labelCol]);
        }

        $q = $request->input('q');
        if ($q) {
            $query->where($labelCol, 'like', '%' . $q . '%');
        }

        $rows = $query->orderBy($labelCol)->limit(50)->get([$pk, $labelCol]);

        $out = $rows->map(fn ($r) => ['value' => $r->$pk, 'label' => $r->$labelCol])->all();

        if ($selectedRow && ! collect($out)->contains(fn ($o) => (string) $o['value'] === (string) $selectedRow->$pk)) {
            array_unshift($out, ['value' => $selectedRow->$pk, 'label' => $selectedRow->$labelCol]);
        }

        return response()->json(['options' => $out]);
    }

    /* ===================== Helpers ===================== */

    private function collectData(Request $request, string $table): array
    {
        $columns = $this->schemaColumns($table);
        $data = [];
        foreach ($columns as $col) {
            if (in_array($col, self::META_COLUMNS, true)) {
                continue;
            }

            // Handle file upload with automated compression and resize optimization
            if ($request->hasFile($col)) {
                $data[$col] = \App\Services\ImageOptimizer::optimizeAndSave(
                    $request->file($col),
                    'uploads/' . $table,
                    1000,
                    1000,
                    80
                );
                continue;
            }

            if (! $request->exists($col)) {
                continue;
            }

            // Don't overwrite existing photo with empty string if no new file is uploaded
            if (in_array($col, ['foto', 'logo', 'gambar', 'avatar', 'file', 'lampiran'], true) && empty($request->input($col))) {
                continue;
            }

            $value = $request->input($col);
            $type = Schema::getColumnType($table, $col);

            if (str_ends_with($col, '_id')) {
                $data[$col] = ($value === '' || $value === null) ? null : (int) $value;
            } elseif (in_array($type, ['int', 'bigint', 'integer', 'tinyint', 'smallint', 'mediumint'], true)) {
                if ($col === 'status') {
                    $data[$col] = $value;
                } else {
                    $data[$col] = ($value === '' || $value === null) ? null : (int) $value;
                }
            } elseif ($type === 'boolean') {
                $data[$col] = $value ? 1 : 0;
            } elseif ($type === 'date' || $type === 'datetime' || $type === 'timestamp') {
                $data[$col] = $value ?: null;
            } else {
                $data[$col] = $value === '' ? null : $value;
            }
        }
        return $data;
    }

    private function buildFields(string $table, array $columns): array
    {
        $fields = [];
        foreach ($columns as $col) {
            if (in_array($col, self::META_COLUMNS, true)) {
                continue;
            }

            // Skip redundant text column when relational FK exists
            if ($table === 'master_pastor' && $col === 'keuskupan' && in_array('keuskupan_id', $columns, true)) {
                continue;
            }

            if ($col === 'id' || str_ends_with($col, '_id')) {
                // PK tidak diedit; FK menjadi select.
                if (str_ends_with($col, '_id')) {
                    $rel = self::FK_MAP[$col] ?? null;
                    if ($rel) {
                        $fields[] = [
                            'name' => $col,
                            'label' => $col === 'keuskupan_id' ? 'Keuskupan' : $this->label($col),
                            'type' => 'select',
                            'relTable' => $rel,
                        ];
                        continue;
                    }
                    $fields[] = ['name' => $col, 'label' => $this->label($col), 'type' => 'number'];
                    continue;
                }
                continue;
            }

            // Custom dropdowns for master_pastor
            if ($table === 'master_pastor') {
                if ($col === 'gelar_depan') {
                    $fields[] = [
                        'name' => $col,
                        'label' => 'Gelar Depan',
                        'type' => 'select',
                        'options' => [
                            ['value' => 'RD', 'label' => 'RD (Reverendus Dominus)'],
                            ['value' => 'RP', 'label' => 'RP (Reverendus Pater)'],
                            ['value' => 'Mgr.', 'label' => 'Mgr. (Monseigneur)'],
                            ['value' => 'Pater', 'label' => 'Pater'],
                            ['value' => 'Romo', 'label' => 'Romo'],
                            ['value' => 'Frater', 'label' => 'Frater'],
                        ],
                    ];
                    continue;
                }

                if ($col === 'jenis_imam') {
                    $fields[] = [
                        'name' => $col,
                        'label' => 'Jenis Imam',
                        'type' => 'select',
                        'options' => [
                            ['value' => 'Diosesan', 'label' => 'Diosesan / Projo'],
                            ['value' => 'Religius', 'label' => 'Religius / Kongregasi / Ordo'],
                        ],
                    ];
                    continue;
                }

                if ($col === 'ordo' || $col === 'ordo_kongregasi') {
                    $fields[] = [
                        'name' => $col,
                        'label' => 'Ordo / Kongregasi',
                        'type' => 'select',
                        'options' => [
                            ['value' => 'CM', 'label' => 'CM - Congregatio Missionis (Misionaris Vinsensian)'],
                            ['value' => 'SVD', 'label' => 'SVD - Serikat Sabda Allah'],
                            ['value' => 'OFM', 'label' => 'OFM - Ordo Saudara Dina (Fransiskan)'],
                            ['value' => 'OCD', 'label' => 'OCD - Karmel Tak Berkasut'],
                            ['value' => 'SJ', 'label' => 'SJ - Serikat Yesus (Yesuit)'],
                            ['value' => 'CSsR', 'label' => 'CSsR - Kongregasi Sang Penebus Mahakudus'],
                            ['value' => 'MSF', 'label' => 'MSF - Misionaris Keluarga Kudus'],
                            ['value' => 'SX', 'label' => 'SX - Serikat Misi Xaverian'],
                            ['value' => 'SCJ', 'label' => 'SCJ - Imam-Imam Hati Kudus Yesus'],
                            ['value' => 'O.Carm', 'label' => 'O.Carm - Ordo Karmel'],
                            ['value' => 'OSB', 'label' => 'OSB - Ordo Santo Benediktus'],
                            ['value' => 'Pr', 'label' => 'Pr - Diosesan / Projo (Tanpa Ordo)'],
                            ['value' => 'Lainnya', 'label' => 'Lainnya'],
                        ],
                    ];
                    continue;
                }

                if ($col === 'jabatan') {
                    $dbJabatan = DB::table('master_referensi_item')
                        ->where('referensi_id', 10)
                        ->where('status', 1)
                        ->orderBy('urutan')
                        ->orderBy('id')
                        ->get();

                    $options = [];
                    foreach ($dbJabatan as $dj) {
                        $options[] = ['value' => $dj->nilai, 'label' => $dj->nilai];
                    }

                    if (empty($options)) {
                        $options = [
                            ['value' => 'Pastor Paroki', 'label' => 'Pastor Paroki'],
                            ['value' => 'Pastor Rekan', 'label' => 'Pastor Rekan'],
                            ['value' => 'Pastor Administrator', 'label' => 'Pastor Administrator'],
                            ['value' => 'Vikaris Jenderal', 'label' => 'Vikaris Jenderal'],
                            ['value' => 'Vikaris Episkopal', 'label' => 'Vikaris Episkopal'],
                            ['value' => 'Pastor Kapelan', 'label' => 'Pastor Kapelan'],
                            ['value' => 'Formator/Pembina Seminari', 'label' => 'Formator/Pembina Seminari'],
                            ['value' => 'Pastor Emeritus', 'label' => 'Pastor Emeritus'],
                        ];
                    }

                    $fields[] = [
                        'name' => $col,
                        'label' => 'Jabatan Gerejani',
                        'type' => 'select',
                        'options' => $options,
                    ];
                    continue;
                }

                if (in_array($col, ['riwayat', 'riwayat_tugas', 'riwayat_pelayanan', 'riwayat_pendidikan'], true)) {
                    $fields[] = [
                        'name' => $col,
                        'label' => 'Riwayat Tugas & Pelayanan Pastoral',
                        'type' => 'textarea',
                    ];
                    continue;
                }
            }

            // File upload fields
            if (in_array($col, ['foto', 'logo', 'gambar', 'avatar', 'file', 'image', 'lampiran'], true)) {
                $fields[] = [
                    'name' => $col,
                    'label' => $this->label($col),
                    'type' => 'file',
                    'accept' => 'image/*',
                ];
                continue;
            }

            $type = Schema::getColumnType($table, $col);

            if ($col === 'status') {
                if ($type === 'enum') {
                    $fields[] = ['name' => $col, 'label' => 'Status', 'type' => 'select', 'options' => $this->enumOptions($table, $col)];
                } elseif (in_array($type, ['tinyint', 'smallint', 'integer'], true)) {
                    $fields[] = ['name' => $col, 'label' => 'Status', 'type' => 'select', 'options' => [['value' => 1, 'label' => 'Aktif'], ['value' => 0, 'label' => 'Nonaktif']]];
                } else {
                    $fields[] = ['name' => $col, 'label' => 'Status', 'type' => 'text'];
                }
                continue;
            }

            if ($col === 'tipe') {
                if ($type === 'enum') {
                    $fields[] = ['name' => $col, 'label' => $this->label($col), 'type' => 'select', 'options' => $this->enumOptions($table, $col)];
                } else {
                    $fields[] = ['name' => $col, 'label' => $this->label($col), 'type' => 'text'];
                }
                continue;
            }

            switch ($type) {
                case 'date':
                    $fields[] = ['name' => $col, 'label' => $this->label($col), 'type' => 'date'];
                    break;
                case 'text':
                    $fields[] = ['name' => $col, 'label' => $this->label($col), 'type' => 'textarea'];
                    break;
                case 'boolean':
                    $fields[] = ['name' => $col, 'label' => $this->label($col), 'type' => 'select', 'options' => [['value' => 1, 'label' => 'Ya'], ['value' => 0, 'label' => 'Tidak']]];
                    break;
                case 'int':
                case 'bigint':
                case 'integer':
                case 'tinyint':
                case 'smallint':
                case 'mediumint':
                    $fields[] = ['name' => $col, 'label' => $this->label($col), 'type' => 'number'];
                    break;
                default:
                    $fields[] = ['name' => $col, 'label' => $this->label($col), 'type' => 'text'];
            }
        }
        return $fields;
    }

    private function buildTableColumns(string $table, array $columns): array
    {
        $out = [];
        if (in_array('foto', $columns, true) && ($table === 'master_pastor' || $table === 'master_uskup')) {
            $out[] = ['name' => 'foto', 'label' => 'Foto'];
        }
        foreach ($columns as $col) {
            if (in_array($col, self::META_COLUMNS, true)) {
                continue;
            }
            if ($col === 'id' || $col === 'status' || $col === 'foto' || $col === 'uuid') {
                continue;
            }
            // Skip redundant text column when relational FK exists or when already combined in nama_pastor
            if ($table === 'master_pastor') {
                if ($col === 'keuskupan' && in_array('keuskupan_id', $columns, true)) {
                    continue;
                }
                if ($col === 'gelar_depan' || $col === 'gelar_belakang') {
                    continue;
                }
            }
            if (str_ends_with($col, '_id')) {
                $rel = self::FK_MAP[$col] ?? null;
                if ($rel) {
                    $out[] = ['name' => $col, 'label' => $col === 'keuskupan_id' ? 'Keuskupan' : $this->label($col), 'fk' => true];
                } else {
                    $out[] = ['name' => $col, 'label' => $this->label($col)];
                }
                continue;
            }
            if (in_array($col, self::LONG_TEXT, true)) {
                continue;
            }
            $out[] = ['name' => $col, 'label' => $this->label($col)];
        }
        return $out;
    }

    private function syncActivePastorPhoto(int $pastorId, ?string $photoPath = null): void
    {
        if (empty($photoPath) && \Illuminate\Support\Facades\Schema::hasTable('master_pastor')) {
            $photoPath = DB::table('master_pastor')->where('id', $pastorId)->value('foto');
        }
        if (empty($photoPath)) return;

        $pastor = DB::table('master_pastor')->where('id', $pastorId)->first();
        if (!$pastor) return;

        $isPastorParoki = str_contains(strtolower($pastor->jabatan ?? ''), 'pastor paroki') || str_contains(strtolower((string)($pastor->status ?? '')), 'aktif') || (string)$pastor->status === '1';

        if ($isPastorParoki) {
            if (\Illuminate\Support\Facades\Schema::hasTable('profil_paroki') && \Illuminate\Support\Facades\Schema::hasColumn('profil_paroki', 'foto_pastor')) {
                DB::table('profil_paroki')->update(['foto_pastor' => $photoPath]);
            }
            if (\Illuminate\Support\Facades\Schema::hasTable('paroki') && !empty($pastor->paroki_id) && \Illuminate\Support\Facades\Schema::hasColumn('paroki', 'foto_pastor')) {
                DB::table('paroki')->where('id_paroki', $pastor->paroki_id)->update(['foto_pastor' => $photoPath]);
            }
            if (\Illuminate\Support\Facades\Schema::hasTable('riwayat_pastor_paroki') && \Illuminate\Support\Facades\Schema::hasColumn('riwayat_pastor_paroki', 'foto')) {
                DB::table('riwayat_pastor_paroki')
                    ->where(function($q) use ($pastor) {
                        $q->where('pastor_id', $pastor->id)
                          ->orWhere('nama_pastor', 'like', '%' . $pastor->nama_pastor . '%')
                          ->orWhere('status', 'like', '%aktif%')
                          ->orWhere('status_pelayanan', 'like', '%aktif%')
                          ->orWhere('tahun_selesai', 'Sekarang')
                          ->orWhereNull('periode_selesai');
                    })
                    ->update(['foto' => $photoPath]);
            }
            if (\Illuminate\Support\Facades\Schema::hasTable('sambutan_pastor')) {
                $sambutanCols = \Illuminate\Support\Facades\Schema::getColumnListing('sambutan_pastor');
                $sambutanData = [];
                if (in_array('foto_pastor', $sambutanCols, true)) $sambutanData['foto_pastor'] = $photoPath;
                if (in_array('foto', $sambutanCols, true)) $sambutanData['foto'] = $photoPath;
                if (!empty($sambutanData)) {
                    DB::table('sambutan_pastor')
                        ->where(function($q) use ($pastor) {
                            if (!empty($pastor->id)) $q->where('pastor_id', $pastor->id);
                            if (!empty($pastor->nama_pastor)) $q->orWhere('nama_pastor', 'like', '%' . $pastor->nama_pastor . '%');
                        })
                        ->update($sambutanData);
                }
            }
        }
    }

    private function optionsFor(string $relTable): array
    {
        $pk = $this->relPk($relTable);
        $labelCol = $this->labelCol($relTable);
        $cols = $this->schemaColumns($relTable);
        $query = DB::table($relTable);
        if (in_array('is_deleted', $cols, true)) {
            $query->where('is_deleted', 0);
        }
        if ($relTable === 'desa_kelurahan') {
            $query->take(300);
        }
        $rows = $query->orderBy($labelCol)->get([$pk, $labelCol]);
        $map = [];
        foreach ($rows as $r) {
            $map[$r->$pk] = $r->$labelCol;
        }
        return $map;
    }

    private function relPk(string $table): string
    {
        $map = [
            'keuskupan' => 'id_keuskupan', 'dekenat' => 'id', 'kevikepan' => 'id', 'paroki' => 'id_paroki',
            'wilayah' => 'id', 'lingkungan' => 'id', 'kapela' => 'id', 'kub' => 'id',
            'provinsi' => 'id_provinsi', 'kabupaten' => 'id_kabupaten', 'kecamatan' => 'id_kecamatan',
            'desa_kelurahan' => 'id_desa', 'master_referensi' => 'id',
        ];
        return $map[$table] ?? 'id';
    }

    private function labelCol(string $table): string
    {
        $cols = $this->schemaColumns($table);
        foreach ($cols as $c) {
            if (preg_match('/nama/i', $c)) {
                return $c;
            }
        }
        foreach ($cols as $c) {
            if (preg_match('/kode/i', $c)) {
                return $c;
            }
        }
        return $cols[1] ?? $cols[0];
    }

    private function schemaColumns(string $table): array
    {
        return Schema::getColumnListing($table);
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

    private function enumOptions(string $table, string $col): array
    {
        $info = DB::select("SHOW COLUMNS FROM `$table` WHERE Field = ?", [$col]);
        $options = [];
        if (! empty($info) && preg_match("/^enum\((.*)\)$/i", $info[0]->Type, $m)) {
            $values = str_getcsv($m[1], ',', "'");
            foreach ($values as $v) {
                $v = trim($v, "'");
                $options[] = ['value' => $v, 'label' => $v];
            }
        }
        return $options;
    }

    private function label(string $col): string
    {
        $map = [
            'kode_keuskupan' => 'Kode', 'nama_keuskupan' => 'Nama Keuskupan', 'nama_latin' => 'Nama Latin',
            'nama_uskup' => 'Nama Uskup', 'kode_dekenat' => 'Kode', 'nama_dekenat' => 'Nama Dekenat',
            'deken' => 'Deken', 'kode_paroki' => 'Kode', 'nama_paroki' => 'Nama Paroki',
            'pelindung_paroki' => 'Pelindung', 'status_paroki' => 'Status Paroki',
            'nama_pastor_paroki_aktif' => 'Pastor Paroki', 'kode_wilayah' => 'Kode', 'nama_wilayah' => 'Nama Wilayah',
            'ketua_wilayah' => 'Ketua Wilayah', 'kode_lingkungan' => 'Kode', 'nama_lingkungan' => 'Nama Lingkungan',
            'ketua_lingkungan' => 'Ketua Lingkungan', 'kode_kapela' => 'Kode', 'nama_kapela' => 'Nama Kapela',
            'penanggung_jawab' => 'Penanggung Jawab', 'kode_kub' => 'Kode', 'nama_kub' => 'Nama KUB',
            'nama_pelindung' => 'Nama Pelindung', 'ketua_kub' => 'Ketua KUB', 'kode_provinsi' => 'Kode',
            'nama_provinsi' => 'Nama Provinsi', 'kode_kabupaten' => 'Kode', 'nama_kabupaten' => 'Nama Kabupaten',
            'kode_kecamatan' => 'Kode', 'nama_kecamatan' => 'Nama Kecamatan', 'kode_desa' => 'Kode',
            'nama_desa' => 'Nama Desa', 'kode_pos' => 'Kode Pos', 'nama_pastor' => 'Nama Pastor',
            'nama_singkat' => 'Nama Singkat', 'gelar_depan' => 'Gelar Depan', 'gelar_belakang' => 'Gelar Belakang',
            'jabatan' => 'Jabatan', 'jenis_imam' => 'Jenis Imam', 'ordo_kongregasi' => 'Ordo / Kongregasi',
            'no_hp' => 'No. HP', 'tanggal_tahbisan' => 'Tanggal Tahbisan', 'kode_grup' => 'Kode Grup',
            'nama_grup' => 'Nama Grup', 'tabel_sumber' => 'Tabel Sumber', 'kolom_sumber' => 'Kolom Sumber',
            'urutan' => 'Urutan', 'kode' => 'Kode', 'nilai' => 'Nama / Item', 'referensi_id' => 'Grup Referensi',
        ];
        if (isset($map[$col])) {
            return $map[$col];
        }
        return ucwords(str_replace('_', ' ', $col));
    }

    protected function ensureDefaultMasterReferensi(): void
    {
        try {
            if (!Schema::hasTable('master_referensi')) {
                Schema::create('master_referensi', function ($table) {
                    $table->id();
                    $table->string('kode_grup', 50)->unique();
                    $table->string('nama_grup', 100);
                    $table->string('tabel_sumber', 100)->nullable();
                    $table->string('kolom_sumber', 100)->nullable();
                    $table->integer('urutan')->default(0);
                    $table->string('status', 30)->default('Aktif');
                    $table->timestamps();
                });
            }

            if (!Schema::hasTable('master_referensi_item')) {
                Schema::create('master_referensi_item', function ($table) {
                    $table->id();
                    $table->unsignedBigInteger('referensi_id');
                    $table->string('kode', 50)->nullable();
                    $table->string('nilai', 150);
                    $table->integer('urutan')->default(0);
                    $table->string('status', 30)->default('Aktif');
                    $table->timestamps();
                });
            }

            $colsGrup = Schema::getColumnListing('master_referensi');
            $colsItem = Schema::getColumnListing('master_referensi_item');

            $defaults = [
                'SUKU_ETNIS' => [
                    'nama_grup' => 'Suku / Etnis',
                    'items' => [
                        'Timor / Dawan',
                        'Rote',
                        'Sabu',
                        'Flores / Manggarai',
                        'Sumba',
                        'Jawa',
                        'Tionghoa',
                        'Lainnya',
                    ],
                ],
                'AGAMA_ASAL' => [
                    'nama_grup' => 'Agama Asal',
                    'items' => [
                        'Katolik sejak lahir',
                        'Katekumen',
                        'Protestan',
                        'Islam',
                        'Hindu',
                        'Budha',
                        'Lainnya',
                    ],
                ],
                'STATUS_RUMAH' => [
                    'nama_grup' => 'Status Kepemilikan Rumah',
                    'items' => [
                        'Milik Sendiri',
                        'Sewa / Kontrak',
                        'Ikut Orang Tua',
                        'Rumah Dinas',
                    ],
                ],
                'KATEGORI_EKONOMI' => [
                    'nama_grup' => 'Kategori Ekonomi Pastoral',
                    'items' => [
                        'Prasejahtera',
                        'Sejahtera / Mandiri',
                        'Mampu',
                    ],
                ],
                'JENIS_BAPTIS' => [
                    'nama_grup' => 'Jenis Penerimaan Baptis (KHK 849)',
                    'items' => [
                        'Baptis Bayi (Infantis)',
                        'Baptis Dewasa (Adultus)',
                        'Receptio (Penerimaan)',
                    ],
                ],
                'STATUS_PERKAWINAN_KANONIK' => [
                    'nama_grup' => 'Status Perkawinan Kanonik (KHK 1055)',
                    'items' => [
                        'Katolik Organik',
                        'Beda Agama (Dispensasi)',
                        'Beda Gereja (Izin)',
                    ],
                ],
                'DISABILITAS' => [
                    'nama_grup' => 'Disabilitas / Kebutuhan Khusus',
                    'items' => [
                        'Tidak Ada',
                        'Rungu / Wicara',
                        'Netra',
                        'Daksa / Fisik',
                        'Mental',
                        'Lansia Perawatan',
                    ],
                ],
                'GOLONGAN_DARAH' => [
                    'nama_grup' => 'Golongan Darah',
                    'items' => [
                        'A',
                        'B',
                        'AB',
                        'O',
                        'Tidak Tahu',
                    ],
                ],
                'HUBUNGAN_KELUARGA' => [
                    'nama_grup' => 'Hubungan Keluarga',
                    'items' => [
                        'Kepala Keluarga',
                        'Istri',
                        'Suami',
                        'Anak Kandung',
                        'Anak Angkat',
                        'Orang Tua',
                        'Mertua',
                        'Menantu',
                        'Cucu',
                        'Famili Lain',
                    ],
                ],
                'PENDIDIKAN' => [
                    'nama_grup' => 'Pendidikan Terakhir',
                    'items' => [
                        'Tidak / Belum Sekolah',
                        'SD / Sederajat',
                        'SMP / Sederajat',
                        'SMA / SMK / Sederajat',
                        'Diploma (D1-D3)',
                        'Sarjana (S1)',
                        'Magister (S2)',
                        'Doktoral (S3)',
                    ],
                ],
                'PEKERJAAN' => [
                    'nama_grup' => 'Pekerjaan / Profesi',
                    'items' => [
                        'PNS / ASN',
                        'TNI / Polri',
                        'Karyawan Swasta',
                        'Wiraswasta / Pedagang',
                        'Petani / Pekebun',
                        'Peternak',
                        'Nelayan',
                        'Guru / Dosen',
                        'Tenaga Medis / Perawat / Dokter',
                        'Tukang / Buruh Bangunan',
                        'Pelajar / Mahasiswa',
                        'Ibu Rumah Tangga',
                        'Pensiunan',
                        'Belum / Tidak Bekerja',
                        'Lainnya',
                    ],
                ],
            ];

            $grupUrutan = (int) DB::table('master_referensi')->max('urutan') + 1;
            foreach ($defaults as $kodeGrup => $def) {
                $grup = DB::table('master_referensi')
                    ->where(function ($q) use ($kodeGrup, $def) {
                        $q->where('kode_grup', $kodeGrup)
                            ->orWhere('kode_grup', strtolower($kodeGrup))
                            ->orWhere('nama_grup', $def['nama_grup']);
                    })
                    ->first();

                if (!$grup) {
                    $insertGrup = [
                        'kode_grup' => $kodeGrup,
                        'nama_grup' => $def['nama_grup'],
                    ];
                    if (in_array('urutan', $colsGrup, true)) $insertGrup['urutan'] = $grupUrutan++;
                    if (in_array('status', $colsGrup, true)) $insertGrup['status'] = 1;
                    if (in_array('is_deleted', $colsGrup, true)) $insertGrup['is_deleted'] = 0;
                    if (in_array('created_at', $colsGrup, true)) $insertGrup['created_at'] = now();
                    if (in_array('updated_at', $colsGrup, true)) $insertGrup['updated_at'] = now();

                    $grupId = DB::table('master_referensi')->insertGetId($insertGrup);
                } else {
                    $grupId = $grup->id;
                }

                $itemUrutan = 1;
                foreach ($def['items'] as $itemNilai) {
                    $exists = DB::table('master_referensi_item')
                        ->where('referensi_id', $grupId)
                        ->where('nilai', $itemNilai)
                        ->exists();

                    if (!$exists) {
                        $insertItem = [
                            'referensi_id' => $grupId,
                            'nilai' => $itemNilai,
                        ];
                        if (in_array('kode', $colsItem, true)) $insertItem['kode'] = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $itemNilai), 0, 10));
                        if (in_array('urutan', $colsItem, true)) $insertItem['urutan'] = $itemUrutan++;
                        if (in_array('status', $colsItem, true)) $insertItem['status'] = 1;
                        if (in_array('is_deleted', $colsItem, true)) $insertItem['is_deleted'] = 0;
                        if (in_array('created_at', $colsItem, true)) $insertItem['created_at'] = now();
                        if (in_array('updated_at', $colsItem, true)) $insertItem['updated_at'] = now();

                        DB::table('master_referensi_item')->insert($insertItem);
                    }
                }
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('ensureDefaultMasterReferensi error: ' . $e->getMessage());
        }
    }
}
