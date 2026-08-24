<script setup>
import { Head, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const page = usePage();
const currentUserRole = page.props.auth?.user?.role || 'Super Admin';

const roleLevels = [
    {
        level: 'Level 1 - Sistem',
        name: 'Super Admin',
        badge: 'Akses Penuh',
        badgeColor: 'bg-rose-100 text-rose-800 border-rose-200',
        subtitle: 'Pengelola teknis tertinggi, konfigurasi server, database, dan proteksi keamanan aplikasi.',
        icon: 'fa-shield-halved',
        color: 'rose',
        scope: 'Tim IT / Webmaster',
        tasks: [
            'Manajemen akun user & reset login',
            'Backup & restore database paroki',
            'Security Center & blokir IP penyerang',
            'Master hierarki gerejawi & wilayah sipil',
        ],
    },
    {
        level: 'Pimpinan Pastoral',
        name: 'Pastor Paroki',
        badge: 'Otoritas Kanonik',
        badgeColor: 'bg-purple-100 text-purple-800 border-purple-200',
        subtitle: 'Pemimpin reksa pastoral, otoritas validasi sakramen, dan persetujuan perubahan Buku Liber.',
        icon: 'fa-user-tie',
        color: 'purple',
        scope: 'Pastor Rekan & Romo',
        tasks: [
            'Validasi & persetujuan permohonan sakramen',
            'Otorisasi perubahan data Buku Liber (Approval)',
            'Monitoring statistik demografi paroki real-time',
            'Jadwal Misa & penugasan pelayan liturgi',
        ],
    },
    {
        level: 'Sekretariat',
        name: 'Admin Paroki',
        badge: 'Operator Induk',
        badgeColor: 'bg-amber-100 text-amber-800 border-amber-200',
        subtitle: 'Operator utama kantor paroki untuk tata usaha, KK Katolik, pencatatan sakramen, dan surat resmi.',
        icon: 'fa-folder-tree',
        color: 'amber',
        scope: 'Sekretariat Paroki',
        tasks: [
            'Buku Induk KK Katolik & Data Jiwa Umat',
            'Pencatatan Liber Baptis, Komuni, Krisma, Nikah',
            'Cetak Surat Permandian & QR-Code Sertifikat',
            'Agenda Surat Masuk, Keluar & Notulen Rapat',
        ],
    },
    {
        level: 'Keuangan',
        name: 'Bendahara Paroki',
        badge: 'Akses Keuangan',
        badgeColor: 'bg-emerald-100 text-emerald-800 border-emerald-200',
        subtitle: 'Mengelola tata buku keuangan paroki, kolekte mingguan, iuran umat, dan aset gerejawi.',
        icon: 'fa-money-bill-wave',
        color: 'emerald',
        scope: 'Tim Ekonomi DPP',
        tasks: [
            'Pencatatan perolehan kolekte misa & intensi',
            'Pemantauan setoran iuran dari KUB & Stasi',
            'Buku Kas Masuk, Kas Keluar & Saldo Kas',
            'Laporan Keuangan Bulanan/Tahunan siap cetak',
        ],
    },
    {
        level: 'Koordinator',
        name: 'Admin Wilayah',
        badge: 'Scope Wilayah',
        badgeColor: 'bg-blue-100 text-blue-800 border-blue-200',
        subtitle: 'Koordinator kewilayahan paroki untuk membina dan memantau KUB di bawah naungan wilayahnya.',
        icon: 'fa-compass',
        color: 'blue',
        scope: 'Pengurus Wilayah Rohani',
        tasks: [
            'Akses data KK & Umat khusus wilayahnya',
            'Verifikasi awal permohonan sakramen dari KUB',
            'Rekapitulasi setoran iuran KUB di wilayahnya',
            'Monitoring demografi & statistik wilayah',
        ],
    },
    {
        level: 'Pengurus Stasi',
        name: 'Admin Stasi / Kapela',
        badge: 'Scope Stasi',
        badgeColor: 'bg-cyan-100 text-cyan-800 border-cyan-200',
        subtitle: 'Pengelola administrasi stasi/kapela di luar pusat paroki beserta KUB binaannya.',
        icon: 'fa-place-of-worship',
        color: 'cyan',
        scope: 'Pengurus Dewan Stasi',
        tasks: [
            'Profil stasi, pengurus, & data KUB stasi',
            'Akses data KK & Umat di lingkup stasi',
            'Data inventaris & aset gedung gereja stasi',
            'Koordinasi pelayanan sakramen & jadwal misa stasi',
        ],
    },
    {
        level: 'Ujung Tombak',
        name: 'Ketua / Pengurus KUB',
        badge: 'Scope KUB',
        badgeColor: 'bg-teal-100 text-teal-800 border-teal-200',
        subtitle: 'Pelayan langsung di tingkat basis yang berhadapan langsung dengan keluarga-keluarga umat.',
        icon: 'fa-people-group',
        color: 'teal',
        scope: 'Pengurus Komunitas Basis',
        tasks: [
            'Pendaftaran & update KK warga KUB',
            'Pencatatan kas internal KUB & iuran wajib',
            'Pengajuan pendaftaran sakramen warga basis',
            'Promosi produk UMKM warga di Lapak KUB',
        ],
    },
    {
        level: 'Media & Redaksi',
        name: 'Penulis / Komsos',
        badge: 'Redaksi Konten',
        badgeColor: 'bg-indigo-100 text-indigo-800 border-indigo-200',
        subtitle: 'Tim publikasi, warta jemaat, warta paroki, berita foto, dan liputan kegiatan gerejawi.',
        icon: 'fa-newspaper',
        color: 'indigo',
        scope: 'Tim Komsos Paroki',
        tasks: [
            'Penerbitan warta berita & artikel rohani',
            'Pengumuman misa & bann pernikahan',
            'Galeri foto & video dokumentasi kegiatan',
            'Catatan peristiwa sejarah / Kronik Paroki',
        ],
    },
    {
        level: 'Portal Warga',
        name: 'Umat / Kepala Keluarga',
        badge: 'Portal Mandiri',
        badgeColor: 'bg-slate-100 text-slate-800 border-slate-200',
        subtitle: 'Akses digital mandiri untuk kepala keluarga dan seluruh umat paroki dari smartphone.',
        icon: 'fa-house-user',
        color: 'slate',
        scope: 'Keluarga Umat Paroki',
        tasks: [
            'Kartu Keluarga Katolik Digital (KKK)',
            'Pengajuan pendaftaran sakramen online dari rumah',
            'Histori sakramen seluruh anggota keluarga',
            'Belanja & jualan di etalase Lapak Umat',
        ],
    },
];

const matrixRows = [
    {
        modul: 'Konfigurasi & Backup Sistem',
        super_admin: 'Full',
        pastor: '—',
        sekretariat: '—',
        bendahara: '—',
        wilayah: '—',
        kub: '—',
        komsos: '—',
        umat: '—',
    },
    {
        modul: 'Buku Induk KK & Umat',
        super_admin: 'Full',
        pastor: 'Lihat',
        sekretariat: 'Kelola',
        bendahara: '—',
        wilayah: 'Wilayah',
        kub: 'KUB',
        komsos: '—',
        umat: 'Sendiri',
    },
    {
        modul: 'Pencatatan Buku Liber & Cetak Surat',
        super_admin: 'Full',
        pastor: 'Validasi',
        sekretariat: 'Input/Cetak',
        bendahara: '—',
        wilayah: 'Pengajuan',
        kub: 'Pengajuan',
        komsos: '—',
        umat: 'Pengajuan',
    },
    {
        modul: 'Buku Kas, Kolekte & Keuangan',
        super_admin: 'Full',
        pastor: 'Pantau',
        sekretariat: 'Pantau',
        bendahara: 'Kelola Full',
        wilayah: 'Iuran Wil',
        kub: 'Kas KUB',
        komsos: '—',
        umat: 'Riwayat',
    },
    {
        modul: 'Warta Berita, Artikel & Galeri',
        super_admin: 'Full',
        pastor: 'Approval',
        sekretariat: 'Full',
        bendahara: '—',
        wilayah: '—',
        kub: '—',
        komsos: 'Redaksi',
        umat: 'Pembaca',
    },
    {
        modul: 'Lapak Komunitas & UMKM Umat',
        super_admin: 'Full',
        pastor: 'Pantau',
        sekretariat: 'Moderasi',
        bendahara: '—',
        wilayah: '—',
        kub: 'Kelola KUB',
        komsos: '—',
        umat: 'Jual / Beli',
    },
];
</script>

<template>
    <AppLayout title="Panduan Peran & Hak Akses (RBAC)">
        <Head title="Panduan Peran & Hak Akses (RBAC) - SIPAROKI" />

        <!-- Header Hero Banner -->
        <div class="rounded-2xl bg-gradient-to-r from-amber-500 via-amber-600 to-amber-700 p-6 sm:p-8 text-white shadow-md shadow-amber-500/15 mb-8">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div>
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/20 backdrop-blur-md text-xs font-bold text-white border border-white/30 mb-3">
                        <i class="fa-solid fa-users-gear"></i>
                        <span>Role-Based Access Control (RBAC)</span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-black tracking-tight">
                        SIPAROKI — Panduan Peran & Hak Akses Pengguna
                    </h1>
                    <p class="mt-2 text-sm text-amber-50 max-w-3xl leading-relaxed">
                        Sistem SIPAROKI dirancang berbasis Hierarki Pastoral Gereja Katolik untuk mempermudah tata kelola data umat, pencatatan sakramen (Buku Liber), keuangan, serta alur pelayanan pastoral yang rapi dan aman.
                    </p>
                </div>

                <!-- Current Role Box -->
                <div class="p-4 rounded-xl bg-white/10 backdrop-blur-md border border-white/20 text-center shrink-0 min-w-48">
                    <span class="text-[11px] uppercase tracking-wider text-amber-100 font-semibold block">Peran Anda Saat Ini</span>
                    <span class="text-lg font-extrabold text-white block mt-0.5">{{ currentUserRole }}</span>
                    <span class="text-[10px] px-2 py-0.5 rounded-full bg-emerald-400/20 text-emerald-100 font-semibold inline-block mt-2 border border-emerald-300/30">
                        Otoritas Aktif
                    </span>
                </div>
            </div>
        </div>

        <!-- 3 Core Values -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-6 mb-8">
            <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl shrink-0 border border-emerald-100">
                    <i class="fa-solid fa-user-shield"></i>
                </div>
                <div>
                    <h4 class="font-bold text-sm text-slate-900">Privasi Terjaga</h4>
                    <p class="text-xs text-slate-500 mt-0.5">Akses data terisolasi per wilayah & KUB binaan.</p>
                </div>
            </div>

            <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-xl shrink-0 border border-purple-100">
                    <i class="fa-solid fa-book-bible"></i>
                </div>
                <div>
                    <h4 class="font-bold text-sm text-slate-900">Buku Liber Aman</h4>
                    <p class="text-xs text-slate-500 mt-0.5">Catatan permandian & pernikahan terlindungi digital.</p>
                </div>
            </div>

            <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl shrink-0 border border-amber-100">
                    <i class="fa-solid fa-qrcode"></i>
                </div>
                <div>
                    <h4 class="font-bold text-sm text-slate-900">Surat Sah Ber-QR Code</h4>
                    <p class="text-xs text-slate-500 mt-0.5">Sertifikat terverifikasi anti-pemalsuan instan.</p>
                </div>
            </div>
        </div>

        <!-- Role Hierarchy Cards -->
        <div class="mb-10">
            <div class="flex items-center justify-between mb-5">
                <div>
                    <h3 class="text-lg font-black text-slate-900">Tingkat Peran & Wewenang Pelayanan</h3>
                    <p class="text-xs text-slate-500">Sistem otomatis menyesuaikan modul dan privasi data sesuai tugas</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div
                    v-for="role in roleLevels"
                    :key="role.name"
                    class="rounded-2xl bg-white border border-slate-200/80 p-5 shadow-xs hover:shadow-md transition-all duration-200 flex flex-col justify-between"
                >
                    <div>
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ role.level }}</span>
                            <span :class="['px-2 py-0.5 rounded-full text-[10px] font-bold border', role.badgeColor]">
                                {{ role.badge }}
                            </span>
                        </div>

                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 border border-amber-200 flex items-center justify-center text-lg shrink-0">
                                <i :class="['fa-solid', role.icon]"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-slate-900 text-base leading-tight">{{ role.name }}</h4>
                                <span class="text-[11px] text-slate-500 font-medium">{{ role.scope }}</span>
                            </div>
                        </div>

                        <p class="text-xs text-slate-600 mb-4 leading-relaxed">
                            {{ role.subtitle }}
                        </p>

                        <div class="space-y-2 pt-3 border-t border-slate-100">
                            <div
                                v-for="task in role.tasks"
                                :key="task"
                                class="flex items-start gap-2 text-xs text-slate-700"
                            >
                                <i class="fa-solid fa-check text-emerald-600 text-[10px] mt-0.5 shrink-0"></i>
                                <span>{{ task }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Matrix Table -->
        <div class="rounded-2xl bg-white border border-slate-200/80 p-6 shadow-xs overflow-hidden">
            <div class="mb-5">
                <h3 class="text-base font-bold text-slate-900">Matriks Perbandingan Hak Akses & Wewenang Fitur</h3>
                <p class="text-xs text-slate-500">Tabel matriks wewenang setiap modul per peran pelayanan</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-600 uppercase tracking-wider text-[10px] font-bold border-b border-slate-200">
                        <tr>
                            <th class="px-4 py-3 min-w-48">Modul / Fitur</th>
                            <th class="px-3 py-3 text-center">Super Admin</th>
                            <th class="px-3 py-3 text-center">Pastor Paroki</th>
                            <th class="px-3 py-3 text-center">Sekretariat</th>
                            <th class="px-3 py-3 text-center">Bendahara</th>
                            <th class="px-3 py-3 text-center">Wilayah / Stasi</th>
                            <th class="px-3 py-3 text-center">Ketua KUB</th>
                            <th class="px-3 py-3 text-center">Komsos</th>
                            <th class="px-3 py-3 text-center">Umat</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        <tr
                            v-for="row in matrixRows"
                            :key="row.modul"
                            class="hover:bg-slate-50 transition-colors"
                        >
                            <td class="px-4 py-3.5 font-bold text-slate-900">{{ row.modul }}</td>
                            <td class="px-3 py-3.5 text-center">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                    {{ row.super_admin }}
                                </span>
                            </td>
                            <td class="px-3 py-3.5 text-center font-medium text-slate-700">{{ row.pastor }}</td>
                            <td class="px-3 py-3.5 text-center font-medium text-slate-700">{{ row.sekretariat }}</td>
                            <td class="px-3 py-3.5 text-center font-medium text-slate-700">{{ row.bendahara }}</td>
                            <td class="px-3 py-3.5 text-center font-medium text-slate-700">{{ row.wilayah }}</td>
                            <td class="px-3 py-3.5 text-center font-medium text-slate-700">{{ row.kub }}</td>
                            <td class="px-3 py-3.5 text-center font-medium text-slate-700">{{ row.komsos }}</td>
                            <td class="px-3 py-3.5 text-center font-medium text-slate-700">{{ row.umat }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>
