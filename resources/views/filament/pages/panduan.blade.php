<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Hero Banner -->
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-amber-600 via-amber-700 to-slate-900 p-6 md:p-8 text-white shadow-xl">
            <div class="relative z-10 max-w-3xl">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/20 backdrop-blur-md text-xs font-semibold uppercase tracking-wider mb-3">
                    <i class="fas fa-shield-alt"></i> RBAC &amp; Hierarki Pastoral
                </div>
                <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight">SIPAROKI — Sistem Informasi Pelayanan Umat Paroki</h1>
                <p class="mt-2 text-sm md:text-base text-amber-100/90 leading-relaxed">
                    Sistem SIPAROKI dirancang berbasis <strong>Role-Based Access Control (RBAC)</strong> dan <strong>Hierarki Pastoral Gereja Katolik</strong> untuk mempermudah tata kelola data umat, pencatatan sakramen (Buku Liber), keuangan, serta alur pelayanan pastoral yang rapi, transparan, dan aman.
                </p>
                <div class="mt-4 pt-4 border-t border-white/20 flex flex-wrap items-center gap-4 text-xs font-medium text-amber-100">
                    <div>
                        <span class="opacity-80">Peran Anda Saat Ini:</span> 
                        <span class="inline-block px-2.5 py-1 rounded-lg bg-white text-amber-900 font-bold ml-1">
                            {{ auth()->user()->role->nama_role ?? auth()->user()->role->slug ?? 'Pengguna' }}
                        </span>
                    </div>
                    <div>
                        <span class="opacity-80">Cakupan Wilayah:</span>
                        <span class="font-bold ml-1">
                            {{ auth()->user()->role_id == 1 ? 'Seluruh Paroki (Full Access)' : (auth()->user()->wilayah->nama_wilayah ?? auth()->user()->kapela->nama_kapela ?? auth()->user()->kub->nama_kub ?? 'Tingkat Paroki') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Matriks Hak Akses Table -->
        <div class="rounded-3xl bg-white dark:bg-gray-900 p-6 border border-gray-200 dark:border-gray-800 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">Matriks Perbandingan Hak Akses &amp; Wewenang Fitur</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Tabel wewenang akses per level pengguna dalam tata kelola gerejawi</p>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/50 text-gray-700 dark:text-gray-300 font-bold">
                            <th class="p-3">Modul / Fitur</th>
                            <th class="p-3 text-center">Super Admin</th>
                            <th class="p-3 text-center">Pastor Paroki</th>
                            <th class="p-3 text-center">Admin Paroki</th>
                            <th class="p-3 text-center">Bendahara</th>
                            <th class="p-3 text-center">Admin Wilayah/Stasi</th>
                            <th class="p-3 text-center">Ketua KUB</th>
                            <th class="p-3 text-center">Komsos</th>
                            <th class="p-3 text-center">Umat</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-gray-600 dark:text-gray-300">
                        <tr>
                            <td class="p-3 font-semibold text-gray-900 dark:text-white">⚙️ Konfigurasi &amp; Backup Sistem</td>
                            <td class="p-3 text-center"><span class="px-2 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300 font-bold">Full</span></td>
                            <td class="p-3 text-center text-gray-400">—</td>
                            <td class="p-3 text-center text-gray-400">—</td>
                            <td class="p-3 text-center text-gray-400">—</td>
                            <td class="p-3 text-center text-gray-400">—</td>
                            <td class="p-3 text-center text-gray-400">—</td>
                            <td class="p-3 text-center text-gray-400">—</td>
                            <td class="p-3 text-center text-gray-400">—</td>
                        </tr>
                        <tr>
                            <td class="p-3 font-semibold text-gray-900 dark:text-white">📖 Buku Induk KK &amp; Umat</td>
                            <td class="p-3 text-center"><span class="px-2 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300 font-bold">Full</span></td>
                            <td class="p-3 text-center text-sky-600 font-medium">Lihat Semua</td>
                            <td class="p-3 text-center text-emerald-600 font-bold">Kelola Full</td>
                            <td class="p-3 text-center text-gray-400">Lihat Saja</td>
                            <td class="p-3 text-center text-amber-600 font-medium">Scope Wilayah</td>
                            <td class="p-3 text-center text-amber-600 font-medium">Scope KUB</td>
                            <td class="p-3 text-center text-gray-400">—</td>
                            <td class="p-3 text-center text-purple-600 font-medium">KK Sendiri</td>
                        </tr>
                        <tr>
                            <td class="p-3 font-semibold text-gray-900 dark:text-white">📜 Buku Liber &amp; Cetak Surat</td>
                            <td class="p-3 text-center"><span class="px-2 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300 font-bold">Full</span></td>
                            <td class="p-3 text-center"><span class="px-2 py-0.5 rounded-full bg-amber-100 dark:bg-amber-950 text-amber-700 dark:text-amber-300 font-bold">Validasi / Sahkan</span></td>
                            <td class="p-3 text-center text-emerald-600 font-bold">Input &amp; Cetak QR</td>
                            <td class="p-3 text-center text-gray-400">—</td>
                            <td class="p-3 text-center text-sky-600 font-medium">Pengajuan Awal</td>
                            <td class="p-3 text-center text-sky-600 font-medium">Pengajuan Warga</td>
                            <td class="p-3 text-center text-gray-400">—</td>
                            <td class="p-3 text-center text-purple-600 font-medium">Pengajuan Online</td>
                        </tr>
                        <tr>
                            <td class="p-3 font-semibold text-gray-900 dark:text-white">💰 Buku Kas, Kolekte &amp; Keuangan</td>
                            <td class="p-3 text-center"><span class="px-2 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300 font-bold">Full</span></td>
                            <td class="p-3 text-center text-sky-600 font-medium">Pantau Laporan</td>
                            <td class="p-3 text-center text-sky-600 font-medium">Pantau Saja</td>
                            <td class="p-3 text-center"><span class="px-2 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300 font-bold">Kelola Penuh</span></td>
                            <td class="p-3 text-center text-amber-600 font-medium">Rekap Iuran Wil</td>
                            <td class="p-3 text-center text-amber-600 font-medium">Kas Internal KUB</td>
                            <td class="p-3 text-center text-gray-400">—</td>
                            <td class="p-3 text-center text-purple-600 font-medium">Riwayat Pribadi</td>
                        </tr>
                        <tr>
                            <td class="p-3 font-semibold text-gray-900 dark:text-white">🌐 Warta Berita, Artikel &amp; Galeri</td>
                            <td class="p-3 text-center"><span class="px-2 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300 font-bold">Full</span></td>
                            <td class="p-3 text-center text-amber-600 font-bold">Approval &amp; Sambutan</td>
                            <td class="p-3 text-center text-emerald-600 font-bold">Kelola Konten</td>
                            <td class="p-3 text-center text-gray-400">—</td>
                            <td class="p-3 text-center text-gray-400">—</td>
                            <td class="p-3 text-center text-gray-400">—</td>
                            <td class="p-3 text-center"><span class="px-2 py-0.5 rounded-full bg-sky-100 dark:bg-sky-950 text-sky-700 dark:text-sky-300 font-bold">Redaksi Konten</span></td>
                            <td class="p-3 text-center text-gray-400">Pembaca</td>
                        </tr>
                        <tr>
                            <td class="p-3 font-semibold text-gray-900 dark:text-white">🛍️ Lapak Komunitas &amp; UMKM Umat</td>
                            <td class="p-3 text-center"><span class="px-2 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300 font-bold">Full</span></td>
                            <td class="p-3 text-center text-sky-600 font-medium">Pantau</td>
                            <td class="p-3 text-center text-emerald-600 font-bold">Moderasi</td>
                            <td class="p-3 text-center text-gray-400">—</td>
                            <td class="p-3 text-center text-amber-600 font-medium">Lapak Wilayah</td>
                            <td class="p-3 text-center text-amber-600 font-medium">Lapak KUB</td>
                            <td class="p-3 text-center text-gray-400">—</td>
                            <td class="p-3 text-center text-purple-600 font-medium">Jual &amp; Beli</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Grid 9 Level Peran Detail -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            <!-- 1. Super Admin -->
            <div class="rounded-3xl p-5 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="px-2.5 py-0.5 text-[11px] font-bold rounded-full bg-red-100 dark:bg-red-950 text-red-700 dark:text-red-300">Level 1 - Sistem</span>
                        <span class="text-xs font-bold text-gray-400">ID 1 (super_admin)</span>
                    </div>
                    <h3 class="text-base font-bold text-gray-900 dark:text-white mb-1">Super Admin</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">Pengelola teknis tertinggi, konfigurasi server, database, dan proteksi keamanan aplikasi.</p>
                    <ul class="text-xs space-y-1.5 text-gray-600 dark:text-gray-300">
                        <li><span class="text-emerald-500 font-bold">✓</span> Manajemen akun user, role &amp; reset login</li>
                        <li><span class="text-emerald-500 font-bold">✓</span> Backup &amp; restore database paroki</li>
                        <li><span class="text-emerald-500 font-bold">✓</span> Security Center &amp; blokir IP penyerang</li>
                        <li><span class="text-emerald-500 font-bold">✓</span> Master hierarki gerejawi &amp; wilayah sipil</li>
                    </ul>
                </div>
                <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-800 text-[11px] font-bold text-red-600 dark:text-red-400">
                    Akses Penuh (Bypass Semua Izin)
                </div>
            </div>

            <!-- 2. Pastor Paroki -->
            <div class="rounded-3xl p-5 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="px-2.5 py-0.5 text-[11px] font-bold rounded-full bg-amber-100 dark:bg-amber-950 text-amber-700 dark:text-amber-300">Pimpinan Pastoral</span>
                        <span class="text-xs font-bold text-gray-400">ID 3 (pastor)</span>
                    </div>
                    <h3 class="text-base font-bold text-gray-900 dark:text-white mb-1">Pastor Paroki</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">Pemimpin reksa pastoral, otoritas validasi sakramen, dan persetujuan Buku Liber.</p>
                    <ul class="text-xs space-y-1.5 text-gray-600 dark:text-gray-300">
                        <li><span class="text-emerald-500 font-bold">✓</span> Validasi &amp; persetujuan permohonan sakramen</li>
                        <li><span class="text-emerald-500 font-bold">✓</span> Otorisasi perubahan data Buku Liber (Approval)</li>
                        <li><span class="text-emerald-500 font-bold">✓</span> Monitoring statistik demografi paroki real-time</li>
                        <li><span class="text-emerald-500 font-bold">✓</span> Jadwal Misa &amp; penugasan pelayan liturgi</li>
                    </ul>
                </div>
                <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-800 text-[11px] font-bold text-amber-600 dark:text-amber-400">
                    Otoritas Kanonik &amp; Pengesahan
                </div>
            </div>

            <!-- 3. Admin Paroki -->
            <div class="rounded-3xl p-5 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="px-2.5 py-0.5 text-[11px] font-bold rounded-full bg-blue-100 dark:bg-blue-950 text-blue-700 dark:text-blue-300">Sekretariat</span>
                        <span class="text-xs font-bold text-gray-400">ID 2 (admin_paroki)</span>
                    </div>
                    <h3 class="text-base font-bold text-gray-900 dark:text-white mb-1">Admin Paroki</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">Operator utama kantor paroki untuk tata usaha, KK Katolik, Buku Liber, dan surat resmi.</p>
                    <ul class="text-xs space-y-1.5 text-gray-600 dark:text-gray-300">
                        <li><span class="text-emerald-500 font-bold">✓</span> Buku Induk KK Katolik &amp; Data Jiwa Umat</li>
                        <li><span class="text-emerald-500 font-bold">✓</span> Pencatatan Liber Baptis, Komuni, Krisma, Nikah</li>
                        <li><span class="text-emerald-500 font-bold">✓</span> Cetak Surat Permandian &amp; QR-Code Sertifikat</li>
                        <li><span class="text-emerald-500 font-bold">✓</span> Agenda Surat Masuk, Keluar &amp; Notulen Rapat</li>
                    </ul>
                </div>
                <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-800 text-[11px] font-bold text-blue-600 dark:text-blue-400">
                    Operator Induk Paroki
                </div>
            </div>

            <!-- 4. Bendahara Paroki -->
            <div class="rounded-3xl p-5 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="px-2.5 py-0.5 text-[11px] font-bold rounded-full bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300">Keuangan</span>
                        <span class="text-xs font-bold text-gray-400">ID 9 (bendahara)</span>
                    </div>
                    <h3 class="text-base font-bold text-gray-900 dark:text-white mb-1">Bendahara Paroki</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">Pengelola tata buku keuangan, kolekte mingguan, iuran umat, dan aset gerejawi.</p>
                    <ul class="text-xs space-y-1.5 text-gray-600 dark:text-gray-300">
                        <li><span class="text-emerald-500 font-bold">✓</span> Pencatatan perolehan kolekte misa &amp; intensi</li>
                        <li><span class="text-emerald-500 font-bold">✓</span> Pemantauan setoran iuran dari KUB &amp; Stasi</li>
                        <li><span class="text-emerald-500 font-bold">✓</span> Buku Kas Masuk, Kas Keluar &amp; Saldo Kas</li>
                        <li><span class="text-emerald-500 font-bold">✓</span> Laporan Keuangan Bulanan/Tahunan siap cetak</li>
                    </ul>
                </div>
                <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-800 text-[11px] font-bold text-emerald-600 dark:text-emerald-400">
                    Otoritas Penuh Keuangan &amp; Aset
                </div>
            </div>

            <!-- 5. Admin Wilayah -->
            <div class="rounded-3xl p-5 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="px-2.5 py-0.5 text-[11px] font-bold rounded-full bg-indigo-100 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300">Koordinator</span>
                        <span class="text-xs font-bold text-gray-400">ID 4 (admin_wilayah)</span>
                    </div>
                    <h3 class="text-base font-bold text-gray-900 dark:text-white mb-1">Admin Wilayah</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">Koordinator kewilayahan paroki untuk membina dan memantau KUB dalam wilayahnya.</p>
                    <ul class="text-xs space-y-1.5 text-gray-600 dark:text-gray-300">
                        <li><span class="text-emerald-500 font-bold">✓</span> Akses data KK &amp; Umat khusus wilayahnya</li>
                        <li><span class="text-emerald-500 font-bold">✓</span> Verifikasi awal permohonan sakramen dari KUB</li>
                        <li><span class="text-emerald-500 font-bold">✓</span> Rekapitulasi setoran iuran KUB di wilayahnya</li>
                        <li><span class="text-emerald-500 font-bold">✓</span> Monitoring demografi &amp; statistik wilayah</li>
                    </ul>
                </div>
                <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-800 text-[11px] font-bold text-indigo-600 dark:text-indigo-400">
                    Scope Wilayah Terisolasi
                </div>
            </div>

            <!-- 6. Admin Kapela / Stasi -->
            <div class="rounded-3xl p-5 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="px-2.5 py-0.5 text-[11px] font-bold rounded-full bg-cyan-100 dark:bg-cyan-950 text-cyan-700 dark:text-cyan-300">Pengurus Stasi</span>
                        <span class="text-xs font-bold text-gray-400">ID 5 (admin_kapela)</span>
                    </div>
                    <h3 class="text-base font-bold text-gray-900 dark:text-white mb-1">Admin Stasi / Kapela</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">Pengelola administrasi stasi/kapela di luar pusat paroki beserta KUB binaannya.</p>
                    <ul class="text-xs space-y-1.5 text-gray-600 dark:text-gray-300">
                        <li><span class="text-emerald-500 font-bold">✓</span> Profil stasi, pengurus, &amp; data KUB stasi</li>
                        <li><span class="text-emerald-500 font-bold">✓</span> Akses data KK &amp; Umat di lingkup stasi</li>
                        <li><span class="text-emerald-500 font-bold">✓</span> Data inventaris &amp; aset gedung gereja stasi</li>
                        <li><span class="text-emerald-500 font-bold">✓</span> Koordinasi sakramen &amp; jadwal misa stasi</li>
                    </ul>
                </div>
                <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-800 text-[11px] font-bold text-cyan-600 dark:text-cyan-400">
                    Scope Stasi Terisolasi
                </div>
            </div>

            <!-- 7. Ketua / Pengurus KUB -->
            <div class="rounded-3xl p-5 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="px-2.5 py-0.5 text-[11px] font-bold rounded-full bg-orange-100 dark:bg-orange-950 text-orange-700 dark:text-orange-300">Ujung Tombak</span>
                        <span class="text-xs font-bold text-gray-400">ID 6 (ketua_kub)</span>
                    </div>
                    <h3 class="text-base font-bold text-gray-900 dark:text-white mb-1">Ketua / Pengurus KUB</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">Pelayan langsung di tingkat basis yang berhadapan langsung dengan keluarga-keluarga umat.</p>
                    <ul class="text-xs space-y-1.5 text-gray-600 dark:text-gray-300">
                        <li><span class="text-emerald-500 font-bold">✓</span> Pendaftaran &amp; update KK warga KUB</li>
                        <li><span class="text-emerald-500 font-bold">✓</span> Pencatatan kas internal KUB &amp; iuran wajib</li>
                        <li><span class="text-emerald-500 font-bold">✓</span> Pengajuan pendaftaran sakramen warga basis</li>
                        <li><span class="text-emerald-500 font-bold">✓</span> Promosi produk UMKM warga di Lapak KUB</li>
                    </ul>
                </div>
                <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-800 text-[11px] font-bold text-orange-600 dark:text-orange-400">
                    Scope Komunitas Basis KUB
                </div>
            </div>

            <!-- 8. Penulis / Komsos -->
            <div class="rounded-3xl p-5 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="px-2.5 py-0.5 text-[11px] font-bold rounded-full bg-pink-100 dark:bg-pink-950 text-pink-700 dark:text-pink-300">Media &amp; Redaksi</span>
                        <span class="text-xs font-bold text-gray-400">ID 8 (penulis)</span>
                    </div>
                    <h3 class="text-base font-bold text-gray-900 dark:text-white mb-1">Penulis / Tim Komsos</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">Tim publikasi, warta jemaat, warta paroki, berita foto, dan liputan kegiatan gerejawi.</p>
                    <ul class="text-xs space-y-1.5 text-gray-600 dark:text-gray-300">
                        <li><span class="text-emerald-500 font-bold">✓</span> Penerbitan warta berita &amp; artikel rohani</li>
                        <li><span class="text-emerald-500 font-bold">✓</span> Pengumuman misa &amp; bann pernikahan</li>
                        <li><span class="text-emerald-500 font-bold">✓</span> Galeri foto &amp; video dokumentasi kegiatan</li>
                        <li><span class="text-emerald-500 font-bold">✓</span> Catatan peristiwa sejarah / Kronik Paroki</li>
                    </ul>
                </div>
                <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-800 text-[11px] font-bold text-pink-600 dark:text-pink-400">
                    Publikasi Website &amp; Warta
                </div>
            </div>

            <!-- 9. Umat / Kepala Keluarga -->
            <div class="rounded-3xl p-5 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="px-2.5 py-0.5 text-[11px] font-bold rounded-full bg-purple-100 dark:bg-purple-950 text-purple-700 dark:text-purple-300">Portal Warga</span>
                        <span class="text-xs font-bold text-gray-400">ID 7 (umat)</span>
                    </div>
                    <h3 class="text-base font-bold text-gray-900 dark:text-white mb-1">Umat / Kepala Keluarga</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">Akses digital mandiri untuk kepala keluarga dan seluruh umat paroki dari smartphone.</p>
                    <ul class="text-xs space-y-1.5 text-gray-600 dark:text-gray-300">
                        <li><span class="text-emerald-500 font-bold">✓</span> Kartu Keluarga Katolik Digital (KKK)</li>
                        <li><span class="text-emerald-500 font-bold">✓</span> Pengajuan sakramen online dari rumah</li>
                        <li><span class="text-emerald-500 font-bold">✓</span> Histori sakramen seluruh anggota keluarga</li>
                        <li><span class="text-emerald-500 font-bold">✓</span> Belanja &amp; jualan di etalase Lapak Umat</li>
                    </ul>
                </div>
                <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-800 text-[11px] font-bold text-purple-600 dark:text-purple-400">
                    Portal Mandiri Keluarga
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
