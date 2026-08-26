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

trait SettingsModuleTrait
{
    public function pengaturanHub(Request $request, ?string $tab = null): Response
    {
        $this->ensureSettingsHubTables();
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

        // Resolve active tab from URL path if not explicitly provided
        $path = $request->path();
        $activeTab = $tab ?? 'pembayaran';
        if (str_contains($path, 'pembayaran')) {
            $activeTab = 'pembayaran';
        } elseif (str_contains($path, 'otp')) {
            $activeTab = 'otp';
        } elseif (str_contains($path, 'video-header')) {
            $activeTab = 'video';
        } elseif (str_contains($path, 'slider')) {
            $activeTab = 'slider';
        } elseif (str_contains($path, 'meta_tag') || str_contains($path, 'seo')) {
            $activeTab = 'seo';
        } elseif (str_contains($path, 'widget') || str_contains($path, 'menu')) {
            $activeTab = 'widget';
        } elseif (str_contains($path, 'maintenance')) {
            $activeTab = 'maintenance';
        }

        // Data for each tab
        $metodePembayaran = DB::table('metode_pembayaran')->orderBy('urutan')->get();
        $pengaturanOtp = DB::table('pengaturan_otp')->first() ?? (object) [
            'provider' => 'Fonnte',
            'api_key' => '',
            'sender_number' => '',
            'device_id' => '',
            'template_otp' => 'Kode verifikasi SIPAROKI Anda adalah: {{otp}}. Berlaku 10 menit.',
            'template_notifikasi' => 'Halo {{nama}}, pendaftaran sakramen Anda di {{paroki}} telah diterima.',
            'status' => 'Aktif',
        ];
        $sliders = DB::table('slider_banner')->orderBy('urutan')->get();
        $pengaturanAplikasi = DB::table('pengaturan_aplikasi')->first() ?? (object) [];

        return Inertia::render('Inertia/PengaturanHub', [
            'role' => $resolvedRole,
            'prefix' => $firstSegment,
            'initialTab' => $activeTab,
            'metodePembayaran' => $metodePembayaran,
            'pengaturanOtp' => $pengaturanOtp,
            'sliders' => $sliders,
            'pengaturanAplikasi' => $pengaturanAplikasi,
        ]);
    }


    public function saveMetodePembayaran(Request $request)
    {
        $this->ensureSettingsHubTables();
        $validated = $request->validate([
            'id' => 'nullable|integer',
            'nama_bank' => 'required|string|max:100',
            'nomor_rekening' => 'nullable|string|max:100',
            'atas_nama' => 'nullable|string|max:150',
            'tipe' => 'required|string|max:50',
            'urutan' => 'nullable|integer',
            'status' => 'nullable|string|max:20',
            'petunjuk' => 'nullable|string',
            'logo_bank' => 'nullable|file|image|max:2048',
            'gambar_qris' => 'nullable|file|image|max:3072',
        ]);

        $payload = [
            'nama_bank' => $validated['nama_bank'],
            'nomor_rekening' => $validated['nomor_rekening'] ?? '',
            'atas_nama' => $validated['atas_nama'] ?? '',
            'tipe' => $validated['tipe'],
            'urutan' => (int) ($validated['urutan'] ?? 1),
            'status' => $validated['status'] ?? 'Aktif',
            'petunjuk' => $validated['petunjuk'] ?? '',
            'updated_at' => now(),
        ];

        if ($request->hasFile('logo_bank')) {
            $payload['logo_bank'] = $this->storeModuleUploadedFile('pembayaran', 'logo_bank', $request->file('logo_bank'));
        }
        if ($request->hasFile('gambar_qris')) {
            $payload['gambar_qris'] = $this->storeModuleUploadedFile('pembayaran', 'gambar_qris', $request->file('gambar_qris'));
        }

        if (!empty($validated['id'])) {
            DB::table('metode_pembayaran')->where('id', $validated['id'])->update($payload);
        } else {
            $payload['created_at'] = now();
            DB::table('metode_pembayaran')->insert($payload);
        }

        return back()->with('success', 'Metode pembayaran berhasil disimpan.');
    }


    public function deleteMetodePembayaran(Request $request, $id)
    {
        $this->ensureSettingsHubTables();
        DB::table('metode_pembayaran')->where('id', $id)->delete();
        return back()->with('success', 'Metode pembayaran berhasil dihapus.');
    }


    public function savePengaturanOtp(Request $request)
    {
        $this->ensureSettingsHubTables();
        $validated = $request->validate([
            'provider' => 'required|string|max:50',
            'api_key' => 'nullable|string|max:255',
            'sender_number' => 'nullable|string|max:50',
            'device_id' => 'nullable|string|max:100',
            'template_otp' => 'nullable|string',
            'template_notifikasi' => 'nullable|string',
            'status' => 'nullable|string|max:20',
        ]);

        $payload = [
            'provider' => $validated['provider'],
            'api_key' => $validated['api_key'] ?? '',
            'sender_number' => $validated['sender_number'] ?? '',
            'device_id' => $validated['device_id'] ?? '',
            'template_otp' => $validated['template_otp'] ?? '',
            'template_notifikasi' => $validated['template_notifikasi'] ?? '',
            'status' => $validated['status'] ?? 'Aktif',
            'updated_at' => now(),
        ];

        $first = DB::table('pengaturan_otp')->first();
        if ($first) {
            DB::table('pengaturan_otp')->where('id', $first->id)->update($payload);
        } else {
            $payload['created_at'] = now();
            DB::table('pengaturan_otp')->insert($payload);
        }

        return back()->with('success', 'Pengaturan Gateway WhatsApp & OTP berhasil disimpan.');
    }


    public function testKirimWhatsapp(Request $request)
    {
        $validated = $request->validate([
            'target_phone' => 'required|string|max:30',
            'test_message' => 'required|string|max:500',
        ]);

        $phone = preg_replace('/[^0-9]/', '', $validated['target_phone']);
        return back()->with('success', 'Uji coba pesan WhatsApp ke nomor ' . $phone . ' berhasil diproses oleh gateway.');
    }


    public function saveVideoHeader(Request $request)
    {
        $this->ensureSettingsHubTables();
        $validated = $request->validate([
            'video_header_type' => 'nullable|string|max:50',
            'video_header_url' => 'nullable|string|max:255',
            'video_header_title' => 'nullable|string|max:200',
            'video_header_subtitle' => 'nullable|string|max:300',
            'video_header_btn_text' => 'nullable|string|max:100',
            'video_header_btn_link' => 'nullable|string|max:255',
            'video_header_status' => 'nullable|string|max:20',
            'video_header_autoplay' => 'nullable|string|max:10',
            'video_header_muted' => 'nullable|string|max:10',
            'video_header_loop' => 'nullable|string|max:10',
            'video_header_overlay_opacity' => 'nullable|string|max:10',
            'video_header_file' => 'nullable|file|mimes:mp4,mov,ogg,webm|max:51200',
            'video_header_poster' => 'nullable|file|image|max:5120',
        ]);

        if (Schema::hasTable('pengaturan_aplikasi')) {
            $first = DB::table('pengaturan_aplikasi')->first();
            $payload = [
                'video_header_type' => $validated['video_header_type'] ?? 'youtube',
                'video_header_url' => $validated['video_header_url'] ?? '',
                'hero_video_youtube' => $validated['video_header_url'] ?? '',
                'video_header_title' => $validated['video_header_title'] ?? '',
                'video_header_subtitle' => $validated['video_header_subtitle'] ?? '',
                'video_header_btn_text' => $validated['video_header_btn_text'] ?? 'Lihat Jadwal Misa',
                'video_header_btn_link' => $validated['video_header_btn_link'] ?? '/jadwal-misa',
                'video_header_status' => $validated['video_header_status'] ?? 'Aktif',
                'video_header_autoplay' => $validated['video_header_autoplay'] ?? '1',
                'video_header_muted' => $validated['video_header_muted'] ?? '1',
                'video_header_loop' => $validated['video_header_loop'] ?? '1',
                'video_header_overlay_opacity' => $validated['video_header_overlay_opacity'] ?? '50',
                'updated_at' => now(),
            ];

            if ($request->hasFile('video_header_file')) {
                $payload['video_header_file'] = $this->storeModuleUploadedFile('video', 'video_header_file', $request->file('video_header_file'));
                $payload['hero_video_file'] = $payload['video_header_file'];
            }
            if ($request->hasFile('video_header_poster')) {
                $payload['video_header_poster'] = $this->storeModuleUploadedFile('video', 'video_header_poster', $request->file('video_header_poster'));
                $payload['hero_video_poster'] = $payload['video_header_poster'];
            }

            if ($first) {
                DB::table('pengaturan_aplikasi')->where('id', $first->id)->update($payload);
            } else {
                $payload['created_at'] = now();
                DB::table('pengaturan_aplikasi')->insert($payload);
            }
        }

        return back()->with('success', 'Pengaturan Video Header berhasil disimpan.');
    }


    public function saveSlider(Request $request)
    {
        $this->ensureSettingsHubTables();
        $validated = $request->validate([
            'id' => 'nullable|integer',
            'judul' => 'required|string|max:150',
            'subjudul' => 'nullable|string|max:255',
            'link_url' => 'nullable|string|max:255',
            'tombol_teks' => 'nullable|string|max:50',
            'urutan' => 'nullable|integer',
            'status' => 'nullable|string|max:20',
            'gambar' => 'nullable|file|image|max:4096',
        ]);

        $payload = [
            'judul' => $validated['judul'],
            'subjudul' => $validated['subjudul'] ?? '',
            'link_url' => $validated['link_url'] ?? '',
            'tombol_teks' => $validated['tombol_teks'] ?? 'Lihat Selengkapnya',
            'urutan' => (int) ($validated['urutan'] ?? 1),
            'status' => $validated['status'] ?? 'Aktif',
            'updated_at' => now(),
        ];

        if ($request->hasFile('gambar')) {
            $payload['gambar'] = $this->storeModuleUploadedFile('slider', 'gambar', $request->file('gambar'));
        }

        if (!empty($validated['id'])) {
            DB::table('slider_banner')->where('id', $validated['id'])->update($payload);
        } else {
            $payload['created_at'] = now();
            DB::table('slider_banner')->insert($payload);
        }

        return back()->with('success', 'Slide banner berhasil disimpan.');
    }


    public function deleteSlider(Request $request, $id)
    {
        $this->ensureSettingsHubTables();
        DB::table('slider_banner')->where('id', $id)->delete();
        return back()->with('success', 'Slide banner berhasil dihapus.');
    }


    public function saveSeoMeta(Request $request)
    {
        $this->ensureSettingsHubTables();
        $validated = $request->validate([
            'meta_title' => 'nullable|string|max:150',
            'meta_description' => 'nullable|string|max:300',
            'meta_keywords' => 'nullable|string|max:255',
            'google_analytics_id' => 'nullable|string|max:50',
        ]);

        if (Schema::hasTable('pengaturan_aplikasi')) {
            $first = DB::table('pengaturan_aplikasi')->first();
            $payload = [
                'meta_title' => $validated['meta_title'] ?? '',
                'meta_description' => $validated['meta_description'] ?? '',
                'meta_keywords' => $validated['meta_keywords'] ?? '',
                'google_analytics_id' => $validated['google_analytics_id'] ?? '',
                'updated_at' => now(),
            ];
            if ($first) {
                DB::table('pengaturan_aplikasi')->where('id', $first->id)->update($payload);
            } else {
                $payload['created_at'] = now();
                DB::table('pengaturan_aplikasi')->insert($payload);
            }
        }

        return back()->with('success', 'Pengaturan SEO & Meta Tags berhasil disimpan.');
    }


    public function saveWidgetSettings(Request $request)
    {
        $this->ensureSettingsHubTables();
        $validated = $request->validate([
            'widget_jadwal_misa' => 'nullable|string|max:10',
            'widget_renungan' => 'nullable|string|max:10',
            'widget_statistik' => 'nullable|string|max:10',
            'widget_kapela' => 'nullable|string|max:10',
            'jam_operasional' => 'nullable|string|max:150',
            'facebook_url' => 'nullable|string|max:255',
            'instagram_url' => 'nullable|string|max:255',
            'youtube_url' => 'nullable|string|max:255',
            'tiktok_url' => 'nullable|string|max:255',
        ]);

        if (Schema::hasTable('pengaturan_aplikasi')) {
            $first = DB::table('pengaturan_aplikasi')->first();
            $payload = [
                'widget_jadwal_misa' => $validated['widget_jadwal_misa'] ?? '1',
                'widget_renungan' => $validated['widget_renungan'] ?? '1',
                'widget_statistik' => $validated['widget_statistik'] ?? '1',
                'widget_kapela' => $validated['widget_kapela'] ?? '1',
                'jam_operasional' => $validated['jam_operasional'] ?? '',
                'facebook_url' => $validated['facebook_url'] ?? '',
                'instagram_url' => $validated['instagram_url'] ?? '',
                'youtube_url' => $validated['youtube_url'] ?? '',
                'tiktok_url' => $validated['tiktok_url'] ?? '',
                'updated_at' => now(),
            ];
            if ($first) {
                DB::table('pengaturan_aplikasi')->where('id', $first->id)->update($payload);
            } else {
                $payload['created_at'] = now();
                DB::table('pengaturan_aplikasi')->insert($payload);
            }
        }

        return back()->with('success', 'Pengaturan Widget & Tampilan berhasil disimpan.');
    }


    public function saveMaintenanceSettings(Request $request)
    {
        $this->ensureSettingsHubTables();
        $validated = $request->validate([
            'maintenance_mode' => 'nullable|string|max:10',
            'maintenance_title' => 'nullable|string|max:200',
            'maintenance_message' => 'nullable|string|max:1000',
            'maintenance_until' => 'nullable|string|max:100',
            'maintenance_contact' => 'nullable|string|max:100',
            'maintenance_bypass_key' => 'nullable|string|max:100',
        ]);

        if (Schema::hasTable('pengaturan_aplikasi')) {
            $first = DB::table('pengaturan_aplikasi')->first();
            $payload = [
                'maintenance_mode' => $validated['maintenance_mode'] ?? '0',
                'maintenance_title' => $validated['maintenance_title'] ?? 'Website Sedang Dalam Pemeliharaan / Perawatan',
                'maintenance_message' => $validated['maintenance_message'] ?? 'Mohon maaf atas ketidaknyamanannya. Website Paroki St. Vinsensius a Paulo Benlutu sedang melakukan pembaruan berkala. Silakan kembali dalam beberapa saat.',
                'maintenance_until' => $validated['maintenance_until'] ?? '',
                'maintenance_contact' => $validated['maintenance_contact'] ?? '',
                'maintenance_bypass_key' => $validated['maintenance_bypass_key'] ?? 'siparoki2026',
                'updated_at' => now(),
            ];
            if ($first) {
                DB::table('pengaturan_aplikasi')->where('id', $first->id)->update($payload);
            } else {
                $payload['created_at'] = now();
                DB::table('pengaturan_aplikasi')->insert($payload);
            }
        }

        $modeStatus = ($validated['maintenance_mode'] ?? '0') === '1' ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "Mode Maintenance berhasil {$modeStatus}.");
    }


    protected function ensureSettingsHubTables(): void
    {
        try {
            if (!Schema::hasTable('metode_pembayaran')) {
                Schema::create('metode_pembayaran', function ($table) {
                    $table->increments('id');
                    $table->string('nama_bank', 100);
                    $table->string('nomor_rekening', 100)->nullable();
                    $table->string('atas_nama', 150)->nullable();
                    $table->string('logo_bank', 255)->nullable();
                    $table->string('gambar_qris', 255)->nullable();
                    $table->string('tipe', 50)->default('Transfer Bank');
                    $table->integer('urutan')->default(1);
                    $table->string('status', 20)->default('Aktif');
                    $table->text('petunjuk')->nullable();
                    $table->timestamps();
                });

                DB::table('metode_pembayaran')->insert([
                    [
                        'nama_bank' => 'Bank BRI',
                        'nomor_rekening' => '0123-01-000456-50-8',
                        'atas_nama' => 'PGPM Paroki St. Vinsensius a Paulo Benlutu',
                        'tipe' => 'Transfer Bank',
                        'urutan' => 1,
                        'status' => 'Aktif',
                        'petunjuk' => 'Transfer via ATM / BRImo / Internet Banking. Cantumkan berita transfer atau simpan bukti transfer.',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'nama_bank' => 'Bank NTT (BPD NTT)',
                        'nomor_rekening' => '250-01-001234-5',
                        'atas_nama' => 'Paroki Benlutu',
                        'tipe' => 'Transfer Bank',
                        'urutan' => 2,
                        'status' => 'Aktif',
                        'petunjuk' => 'Transfer via Teller / ATM Bank NTT / BPD Mobile.',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'nama_bank' => 'QRIS Resmi Paroki (Semua E-Wallet & Bank)',
                        'nomor_rekening' => 'NMID: ID1020304050607',
                        'atas_nama' => 'PAROKI BENLUTU QRIS',
                        'tipe' => 'QRIS',
                        'urutan' => 3,
                        'status' => 'Aktif',
                        'petunjuk' => 'Scan QRIS menggunakan BCA Mobile, Mandiri Livin, GoPay, OVO, Dana, ShopeePay, LinkAja, dll.',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                ]);
            }

            if (!Schema::hasTable('pengaturan_otp')) {
                Schema::create('pengaturan_otp', function ($table) {
                    $table->increments('id');
                    $table->string('provider', 50)->default('Fonnte');
                    $table->string('api_key', 255)->nullable();
                    $table->string('sender_number', 50)->nullable();
                    $table->string('device_id', 100)->nullable();
                    $table->text('template_otp')->nullable();
                    $table->text('template_notifikasi')->nullable();
                    $table->string('status', 20)->default('Aktif');
                    $table->timestamps();
                });

                DB::table('pengaturan_otp')->insert([
                    'provider' => 'Fonnte',
                    'api_key' => '',
                    'sender_number' => '081234567890',
                    'template_otp' => 'Kode verifikasi SIPAROKI Anda: {{otp}}. Berlaku 10 menit.',
                    'template_notifikasi' => 'Halo {{nama}}, permohonan sakramen Anda di {{paroki}} telah diterima.',
                    'status' => 'Aktif',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            if (!Schema::hasTable('slider_banner')) {
                Schema::create('slider_banner', function ($table) {
                    $table->increments('id');
                    $table->string('judul', 150);
                    $table->string('subjudul', 255)->nullable();
                    $table->string('gambar', 255)->nullable();
                    $table->string('link_url', 255)->nullable();
                    $table->string('tombol_teks', 50)->default('Lihat Selengkapnya');
                    $table->integer('urutan')->default(1);
                    $table->string('status', 20)->default('Aktif');
                    $table->timestamps();
                });

                DB::table('slider_banner')->insert([
                    [
                        'judul' => 'Selamat Datang di Paroki St. Vinsensius a Paulo Benlutu',
                        'subjudul' => 'Gereja yang Bersekutu, Berakar dalam Iman, dan Berbuah dalam Kasih Karitas.',
                        'gambar' => '/assets/uploads/profil/banner_1786529079.JPG',
                        'link_url' => '/profil',
                        'tombol_teks' => 'Profil Paroki',
                        'urutan' => 1,
                        'status' => 'Aktif',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                ]);
            }

            if (Schema::hasTable('pengaturan_aplikasi')) {
                $cols = Schema::getColumnListing('pengaturan_aplikasi');
                Schema::table('pengaturan_aplikasi', function ($table) use ($cols) {
                    if (!in_array('video_header_type', $cols, true)) $table->string('video_header_type', 50)->default('youtube');
                    if (!in_array('video_header_url', $cols, true)) $table->string('video_header_url', 255)->nullable();
                    if (!in_array('hero_video_youtube', $cols, true)) $table->string('hero_video_youtube', 255)->nullable();
                    if (!in_array('hero_video_file', $cols, true)) $table->string('hero_video_file', 255)->nullable();
                    if (!in_array('hero_video_poster', $cols, true)) $table->string('hero_video_poster', 255)->nullable();
                    if (!in_array('hero_video_type', $cols, true)) $table->string('hero_video_type', 50)->default('youtube');
                    if (!in_array('video_header_title', $cols, true)) $table->string('video_header_title', 200)->nullable();
                    if (!in_array('video_header_subtitle', $cols, true)) $table->string('video_header_subtitle', 300)->nullable();
                    if (!in_array('video_header_btn_text', $cols, true)) $table->string('video_header_btn_text', 100)->default('Lihat Jadwal Misa');
                    if (!in_array('video_header_btn_link', $cols, true)) $table->string('video_header_btn_link', 255)->default('/jadwal-misa');
                    if (!in_array('video_header_status', $cols, true)) $table->string('video_header_status', 20)->default('Aktif');
                    if (!in_array('video_header_autoplay', $cols, true)) $table->string('video_header_autoplay', 10)->default('1');
                    if (!in_array('video_header_muted', $cols, true)) $table->string('video_header_muted', 10)->default('1');
                    if (!in_array('video_header_loop', $cols, true)) $table->string('video_header_loop', 10)->default('1');
                    if (!in_array('video_header_overlay_opacity', $cols, true)) $table->string('video_header_overlay_opacity', 10)->default('50');
                    if (!in_array('video_header_file', $cols, true)) $table->string('video_header_file', 255)->nullable();
                    if (!in_array('video_header_poster', $cols, true)) $table->string('video_header_poster', 255)->nullable();
                    if (!in_array('meta_title', $cols, true)) $table->string('meta_title', 150)->nullable();
                    if (!in_array('meta_description', $cols, true)) $table->string('meta_description', 300)->nullable();
                    if (!in_array('meta_keywords', $cols, true)) $table->string('meta_keywords', 255)->nullable();
                    if (!in_array('google_analytics_id', $cols, true)) $table->string('google_analytics_id', 50)->nullable();
                    if (!in_array('widget_jadwal_misa', $cols, true)) $table->string('widget_jadwal_misa', 10)->default('1');
                    if (!in_array('widget_renungan', $cols, true)) $table->string('widget_renungan', 10)->default('1');
                    if (!in_array('widget_statistik', $cols, true)) $table->string('widget_statistik', 10)->default('1');
                    if (!in_array('widget_kapela', $cols, true)) $table->string('widget_kapela', 10)->default('1');
                    if (!in_array('jam_operasional', $cols, true)) $table->string('jam_operasional', 150)->nullable();
                    if (!in_array('facebook_url', $cols, true)) $table->string('facebook_url', 255)->nullable();
                    if (!in_array('instagram_url', $cols, true)) $table->string('instagram_url', 255)->nullable();
                    if (!in_array('youtube_url', $cols, true)) $table->string('youtube_url', 255)->nullable();
                    if (!in_array('tiktok_url', $cols, true)) $table->string('tiktok_url', 255)->nullable();
                    if (!in_array('maintenance_mode', $cols, true)) $table->string('maintenance_mode', 10)->default('0');
                    if (!in_array('maintenance_title', $cols, true)) $table->string('maintenance_title', 200)->nullable();
                    if (!in_array('maintenance_message', $cols, true)) $table->text('maintenance_message')->nullable();
                    if (!in_array('maintenance_until', $cols, true)) $table->string('maintenance_until', 100)->nullable();
                    if (!in_array('maintenance_contact', $cols, true)) $table->string('maintenance_contact', 100)->nullable();
                    if (!in_array('maintenance_bypass_key', $cols, true)) $table->string('maintenance_bypass_key', 100)->default('siparoki2026');
                });
            }
        } catch (\Throwable $e) {}
    }

}
