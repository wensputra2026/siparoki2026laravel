<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

use App\Models\Keuskupan;
use App\Models\Dekenat;
use App\Models\Paroki;
use App\Models\ProfilParoki;
use App\Models\PengaturanAplikasi;

class SetupParokiController extends Controller
{
    /**
     * Display the First-Time Setup & Parish Configuration Wizard.
     */
    public function index(Request $request): Response
    {
        PengaturanAplikasi::ensureSetupColumns();

        // Load all Keuskupans in Indonesia with their Dekenats and Parokis
        $keuskupanList = Keuskupan::with([
            'dekenats' => function ($q) {
                $q->orderBy('nama_dekenat')->with([
                    'parokis' => function ($pq) {
                        $pq->orderBy('nama_paroki');
                    }
                ]);
            },
            'parokis' => function ($pq) {
                $pq->orderBy('nama_paroki');
            }
        ])
        ->orderBy('nama_keuskupan')
        ->get();

        // Current active settings
        $currentPengaturan = DB::table('pengaturan_aplikasi')->first();
        $currentProfil = DB::table('profil_paroki')->first();
        
        $currentParoki = null;
        if (!empty($currentPengaturan?->paroki_id)) {
            $currentParoki = Paroki::find($currentPengaturan->paroki_id);
        } elseif (!empty($currentProfil?->paroki_id)) {
            $currentParoki = Paroki::find($currentProfil->paroki_id);
        } elseif (!empty($currentPengaturan?->nama_paroki)) {
            $currentParoki = Paroki::where('nama_paroki', $currentPengaturan->nama_paroki)->first();
        }

        $isSetupCompleted = (bool) ($currentPengaturan->is_setup_completed ?? ($currentProfil ? true : false));

        return Inertia::render('Inertia/SetupParoki', [
            'keuskupanList' => $keuskupanList,
            'currentPengaturan' => $currentPengaturan,
            'currentProfil' => $currentProfil,
            'currentParoki' => $currentParoki,
            'isSetupCompleted' => $isSetupCompleted,
            'auth' => [
                'user' => $request->user(),
            ],
        ]);
    }

    /**
     * API endpoint to dynamically retrieve hierarchy data.
     */
    public function getHierarchy(Request $request)
    {
        $keuskupanId = $request->query('keuskupan_id');
        $dekenatId = $request->query('dekenat_id');

        $dekenats = [];
        $parokis = [];

        if ($keuskupanId) {
            $dekenats = Dekenat::where('keuskupan_id', $keuskupanId)->orderBy('nama_dekenat')->get();
            $parokis = Paroki::where('keuskupan_id', $keuskupanId)
                ->when($dekenatId, fn ($q) => $q->where('dekenat_id', $dekenatId))
                ->orderBy('nama_paroki')
                ->get();
        }

        return response()->json([
            'dekenats' => $dekenats,
            'parokis' => $parokis,
        ]);
    }

    /**
     * Save / Apply Default Parish Configuration.
     */
    public function save(Request $request)
    {
        PengaturanAplikasi::ensureSetupColumns();

        $validated = $request->validate([
            'mode' => 'required|in:pilih_ada,buat_baru',
            'keuskupan_id' => 'required|integer|exists:keuskupan,id_keuskupan',
            'dekenat_id' => 'nullable|integer',
            'paroki_id' => 'nullable|integer',
            'nama_paroki' => 'required|string|max:150',
            'pelindung_paroki' => 'nullable|string|max:150',
            'nama_pastor_paroki_aktif' => 'nullable|string|max:150',
            'alamat' => 'nullable|string',
            'telepon' => 'nullable|string|max:50',
            'whatsapp' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:100',
            'website' => 'nullable|string|max:150',
            'logo' => 'nullable|file|image|max:3072',
            'banner' => 'nullable|file|image|max:5120',
        ], [
            'keuskupan_id.required' => 'Silakan pilih Keuskupan terlebih dahulu.',
            'nama_paroki.required' => 'Nama Paroki wajib diisi.',
        ]);

        $keuskupan = Keuskupan::find($validated['keuskupan_id']);
        $namaKeuskupan = $keuskupan?->nama_keuskupan ?? 'Keuskupan';

        // 1. Handle File Uploads (Logo & Banner)
        $logoPath = null;
        $bannerPath = null;

        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $filename = 'paroki_logo_' . time() . '_' . Str::random(6) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/paroki'), $filename);
            $logoPath = 'uploads/paroki/' . $filename;
        }

        if ($request->hasFile('banner')) {
            $file = $request->file('banner');
            $filename = 'paroki_banner_' . time() . '_' . Str::random(6) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/paroki'), $filename);
            $bannerPath = 'uploads/paroki/' . $filename;
        }

        // 2. Create or Update Paroki record in 'paroki' table
        $paroki = null;
        if ($validated['mode'] === 'pilih_ada' && !empty($validated['paroki_id'])) {
            $paroki = Paroki::find($validated['paroki_id']);
        }

        $parokiPayload = [
            'keuskupan_id' => $validated['keuskupan_id'],
            'dekenat_id' => $validated['dekenat_id'] ?? $paroki?->dekenat_id,
            'nama_paroki' => trim($validated['nama_paroki']),
            'pelindung_paroki' => $validated['pelindung_paroki'] ?? $paroki?->pelindung_paroki,
            'nama_pastor_paroki_aktif' => $validated['nama_pastor_paroki_aktif'] ?? $paroki?->nama_pastor_paroki_aktif,
            'alamat' => $validated['alamat'] ?? $paroki?->alamat,
            'telepon' => $validated['telepon'] ?? $paroki?->telepon,
            'whatsapp' => $validated['whatsapp'] ?? $paroki?->whatsapp,
            'email' => $validated['email'] ?? $paroki?->email,
            'website' => $validated['website'] ?? $paroki?->website,
            'status' => 'Aktif',
            'status_paroki' => 'Mandiri / Definitif',
        ];

        if ($logoPath) {
            $parokiPayload['logo'] = $logoPath;
        }
        if ($bannerPath) {
            $parokiPayload['banner'] = $bannerPath;
        }

        if ($paroki) {
            $paroki->update($parokiPayload);
        } else {
            // Generate next Paroki code
            $maxId = Paroki::max('id_paroki') ?? 0;
            $parokiPayload['kode_paroki'] = 'PAR-' . str_pad((string) ($maxId + 1), 3, '0', STR_PAD_LEFT);
            $paroki = Paroki::create($parokiPayload);
        }

        $finalLogo = $logoPath ?: ($paroki->logo ?? null);
        $finalBanner = $bannerPath ?: ($paroki->banner ?? null);

        // 3. Synchronize with 'profil_paroki' table
        if (Schema::hasTable('profil_paroki')) {
            $profilCols = Schema::getColumnListing('profil_paroki');
            $profilPayload = [
                'nama_paroki' => $paroki->nama_paroki,
                'keuskupan' => $namaKeuskupan,
                'alamat' => $paroki->alamat,
                'telepon' => $paroki->telepon ?? $paroki->whatsapp,
                'email' => $paroki->email,
                'website' => $paroki->website,
                'pastor_paroki' => $paroki->nama_pastor_paroki_aktif,
            ];

            if (in_array('keuskupan_id', $profilCols, true)) $profilPayload['keuskupan_id'] = $paroki->keuskupan_id;
            if (in_array('dekenat_id', $profilCols, true)) $profilPayload['dekenat_id'] = $paroki->dekenat_id;
            if (in_array('paroki_id', $profilCols, true)) $profilPayload['paroki_id'] = $paroki->id_paroki;
            if (in_array('pelindung', $profilCols, true)) $profilPayload['pelindung'] = $paroki->pelindung_paroki;
            if (in_array('logo', $profilCols, true) && $finalLogo) $profilPayload['logo'] = $finalLogo;
            if (in_array('updated_at', $profilCols, true)) $profilPayload['updated_at'] = now();

            $existingProfil = DB::table('profil_paroki')->first();
            if ($existingProfil) {
                $pkCol = in_array('id', $profilCols, true) ? 'id' : (in_array('id_profil', $profilCols, true) ? 'id_profil' : $profilCols[0]);
                DB::table('profil_paroki')->where($pkCol, $existingProfil->$pkCol)->update($profilPayload);
            } else {
                if (in_array('created_at', $profilCols, true)) $profilPayload['created_at'] = now();
                DB::table('profil_paroki')->insert($profilPayload);
            }
        }

        // 4. Synchronize with 'pengaturan_aplikasi' table
        if (Schema::hasTable('pengaturan_aplikasi')) {
            $pengaturanCols = Schema::getColumnListing('pengaturan_aplikasi');
            $pengaturanPayload = [
                'nama_paroki' => $paroki->nama_paroki,
                'alamat_paroki' => $paroki->alamat,
                'telepon_paroki' => $paroki->telepon ?? $paroki->whatsapp,
                'email_paroki' => $paroki->email,
                'is_setup_completed' => true,
            ];

            if (in_array('nama_aplikasi', $pengaturanCols, true)) $pengaturanPayload['nama_aplikasi'] = 'SIPAROKI ' . $paroki->nama_paroki;
            if (in_array('keuskupan_id', $pengaturanCols, true)) $pengaturanPayload['keuskupan_id'] = $paroki->keuskupan_id;
            if (in_array('dekenat_id', $pengaturanCols, true)) $pengaturanPayload['dekenat_id'] = $paroki->dekenat_id;
            if (in_array('paroki_id', $pengaturanCols, true)) $pengaturanPayload['paroki_id'] = $paroki->id_paroki;
            if (in_array('logo', $pengaturanCols, true) && $finalLogo) $pengaturanPayload['logo'] = $finalLogo;
            if (in_array('updated_at', $pengaturanCols, true)) $pengaturanPayload['updated_at'] = now();

            $existingPengaturan = DB::table('pengaturan_aplikasi')->first();
            if ($existingPengaturan) {
                $pkCol = in_array('id', $pengaturanCols, true) ? 'id' : (in_array('id_pengaturan', $pengaturanCols, true) ? 'id_pengaturan' : $pengaturanCols[0]);
                DB::table('pengaturan_aplikasi')->where($pkCol, $existingPengaturan->$pkCol)->update($pengaturanPayload);
            } else {
                if (in_array('created_at', $pengaturanCols, true)) $pengaturanPayload['created_at'] = now();
                DB::table('pengaturan_aplikasi')->insert($pengaturanPayload);
            }
        }

        // 5. Store in Session and Flush Application Caches
        $request->session()->put('default_paroki_id', $paroki->id_paroki);
        Cache::forget('global_pengaturan_aplikasi_first');
        Cache::forget('siparoki-cache-active_paroki_middleware_v3');
        Cache::forever('global_view_data_version', (int) Cache::get('global_view_data_version', 1) + 1);

        $targetRoute = auth()->check()
            ? (auth()->user()->role?->slug === 'paroki' ? '/paroki' : '/superadmin')
            : '/';

        return redirect($targetRoute)->with('success', 'Konfigurasi Paroki (' . $paroki->nama_paroki . ') berhasil disimpan dan diterapkan sebagai Paroki Default!');
    }

    /**
     * Reset Setup Status so the wizard can be re-run by Admin.
     */
    public function resetSetup(Request $request)
    {
        if (!auth()->check() || !in_array(strtolower($request->user()->role?->slug ?? ''), ['superadmin', 'superadministrator', 'paroki', 'administrator'], true)) {
            abort(403, 'Hanya Super Administrator yang dapat mereset konfigurasi Paroki.');
        }

        PengaturanAplikasi::ensureSetupColumns();

        if (Schema::hasTable('pengaturan_aplikasi') && Schema::hasColumn('pengaturan_aplikasi', 'is_setup_completed')) {
            DB::table('pengaturan_aplikasi')->update(['is_setup_completed' => false]);
        }

        Cache::forget('global_pengaturan_aplikasi_first');
        Cache::forever('global_view_data_version', (int) Cache::get('global_view_data_version', 1) + 1);

        return redirect('/setup-paroki')->with('info', 'Silakan pilih atau atur ulang Keuskupan dan Paroki default Anda.');
    }
}
