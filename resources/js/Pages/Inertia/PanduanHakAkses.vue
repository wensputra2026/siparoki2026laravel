<script setup>
import { ref, computed } from 'vue';
import { Head, usePage, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    role: {
        type: String,
        default: '',
    },
    prefix: {
        type: String,
        default: '',
    },
});

const page = usePage();

// Role resolution
const pathPrefix = computed(() => {
    if (props.prefix) return props.prefix.toLowerCase();
    const segment = window.location.pathname.split('/').filter(Boolean)[0] || '';
    return segment.toLowerCase();
});

const resolvedRoleName = computed(() => {
    if (props.role) return props.role;
    const authRole = page.props.auth?.user?.role;
    return typeof authRole === 'string' ? authRole : (authRole?.nama_role || authRole?.slug || 'Super Admin');
});

const roleKey = computed(() => {
    const p = pathPrefix.value;
    const r = resolvedRoleName.value.toLowerCase();
    if (p === 'superadmin' || r.includes('super')) return 'superadmin';
    if (p === 'pastor' || r.includes('pastor') || r.includes('romo')) return 'pastor';
    if (p === 'paroki' || r.includes('sekretariat') || r.includes('admin paroki')) return 'paroki';
    if (p === 'bendahara' || r.includes('bendahara') || r.includes('keuangan')) return 'bendahara';
    if (p === 'wilayah' || r.includes('wilayah')) return 'wilayah';
    if (p === 'kapela' || p === 'stasi' || r.includes('kapela') || r.includes('stasi')) return 'kapela';
    if (p === 'kub' || r.includes('kub')) return 'kub';
    if (p === 'penulis' || r.includes('penulis') || r.includes('komsos') || r.includes('redaksi')) return 'penulis';
    if (p === 'umat' || r.includes('umat')) return 'umat';
    return 'superadmin';
});

// For Superadmin & Paroki, allow switching tabs to preview other roles
const isSuperOrParoki = computed(() => ['superadmin', 'paroki'].includes(roleKey.value));
const selectedRoleTab = ref(roleKey.value);

const currentViewingKey = computed(() => {
    return isSuperOrParoki.value ? selectedRoleTab.value : roleKey.value;
});

// Role details repository
const roleDetails = {
    wilayah: {
        key: 'wilayah',
        level: 'Tingkat Koordinator',
        name: 'Admin Wilayah',
        scope: 'Pengurus Wilayah Rohani',
        badge: 'Scope Wilayah',
        badgeColor: 'bg-blue-50 text-blue-700 border-blue-200',
        icon: 'fa-compass',
        headerGradient: 'from-blue-600 via-indigo-600 to-blue-800',
        summary: 'Koordinator kewilayahan paroki yang bertugas membina, memantau data demografi, serta merekapitulasi iuran dan administrasi KUB-KUB di wilayah binaannya.',
        permissions: [
            {
                category: 'Data KK & Umat',
                status: 'view_only',
                statusText: 'Lihat Saja (View-Only)',
                badgeColor: 'bg-slate-100 text-slate-700 border-slate-200',
                icon: 'fa-users',
                details: 'Melihat seluruh daftar KK Katolik dan profil jiwa umat yang terdaftar di wilayah binaan Anda beserta fitur pencarian dan cetak.',
            },
            {
                category: 'Buku Sakramen',
                status: 'view_edit',
                statusText: 'Lihat & Ubah Catatan',
                badgeColor: 'bg-amber-50 text-amber-800 border-amber-200',
                icon: 'fa-cross',
                details: 'Melihat riwayat sakramen umat dan mengedit/melengkapi catatan sakramen untuk keperluan verifikasi.',
            },
            {
                category: 'Iuran Umat & Keuangan',
                status: 'manage',
                statusText: 'Kelola Iuran Wilayah',
                badgeColor: 'bg-emerald-50 text-emerald-800 border-emerald-200',
                icon: 'fa-money-bill-wave',
                details: 'Mencatat, memverifikasi, dan merekapitulasi setoran iuran umat dari masing-masing KUB di wilayah Anda.',
            },
            {
                category: 'Statistik & Demografi',
                status: 'view_only',
                statusText: 'Monitoring Real-Time',
                badgeColor: 'bg-indigo-50 text-indigo-700 border-indigo-200',
                icon: 'fa-chart-pie',
                details: 'Memantau statistik total jiwa, KK, jumlah KUB, perbandingan gender, dan grafik pertumbuhan umat di wilayah Anda.',
            },
            {
                category: 'Data KUB Binaan',
                status: 'view_only',
                statusText: 'Pantau KUB',
                badgeColor: 'bg-cyan-50 text-cyan-700 border-cyan-200',
                icon: 'fa-people-group',
                details: 'Melihat daftar nama KUB, pengurus KUB, dan kontak ketua KUB di bawah naungan wilayah Anda.',
            },
        ],
        dos: [
            'Memantau kelengkapan data keluarga dan umat di KUB-KUB binaan Anda.',
            'Mencatat dan memverifikasi setoran iuran umat yang disetorkan oleh kolektor KUB.',
            'Melakukan verifikasi awal catatan sakramen sebelum diajukan ke Sekretariat Paroki.',
            'Memanfaatkan data statistik wilayah untuk perencanaan pastoral dan kegiatan sosial gerejawi.',
        ],
        donts: [
            'Menambah, mengedit, atau menghapus data KK & Umat (CRUD dilakukan langsung oleh Ketua KUB atau Sekretariat Paroki).',
            'Menambah atau menghapus pendaftaran sakramen baru secara mandiri (wewenang Sekretariat Paroki & Pastor).',
            'Mengakses atau mengubah data milik KUB dan Wilayah lain (data isolation berlaku otomatis).',
            'Membagikan akun login pengurus wilayah kepada pihak lain demi keamanan data umat.',
        ],
    },
    kub: {
        key: 'kub',
        level: 'Tingkat Basis',
        name: 'Ketua / Pengurus KUB',
        scope: 'Pengurus Komunitas Umat Basis',
        badge: 'Scope KUB (Ujung Tombak)',
        badgeColor: 'bg-cyan-50 text-cyan-700 border-cyan-200',
        icon: 'fa-people-group',
        headerGradient: 'from-cyan-600 via-teal-600 to-cyan-800',
        summary: 'Pelayan langsung di tingkat basis yang mendampingi keluarga-keluarga umat secara langsung, mengelola Kartu Keluarga, iuran, kas internal, dan pengajuan sakramen.',
        permissions: [
            {
                category: 'Kartu Keluarga (KK)',
                status: 'crud',
                statusText: 'Full Kelola (CRUD)',
                badgeColor: 'bg-emerald-50 text-emerald-800 border-emerald-200',
                icon: 'fa-address-card',
                details: 'Mendaftarkan KK baru warga KUB, memperbarui anggota keluarga, dan mencetak lembar KK Katolik.',
            },
            {
                category: 'Data Umat / Jiwa',
                status: 'crud',
                statusText: 'Full Kelola (CRUD)',
                badgeColor: 'bg-emerald-50 text-emerald-800 border-emerald-200',
                icon: 'fa-user-group',
                details: 'Mendaftarkan dan memperbarui biodata warga basis, tanggal lahir, dan status sakramen.',
            },
            {
                category: 'Iuran & Kas KUB',
                status: 'crud',
                statusText: 'Pencatatan Kas & Iuran',
                badgeColor: 'bg-emerald-50 text-emerald-800 border-emerald-200',
                icon: 'fa-wallet',
                details: 'Mencatat kas masuk/keluar KUB dan menginput setoran iuran umat warga basis.',
            },
            {
                category: 'Pengajuan Sakramen',
                status: 'create',
                statusText: 'Ajukan ke Paroki',
                badgeColor: 'bg-amber-50 text-amber-800 border-amber-200',
                icon: 'fa-file-signature',
                details: 'Mendaftarkan warga basis yang akan menerima Sakramen Baptis, Komuni, Krisma, atau Pernikahan.',
            },
        ],
        dos: [
            'Memastikan seluruh keluarga Katolik di basis terdaftar dalam Kartu Keluarga (KK).',
            'Rutin mencatat iuran wajib dan kas KUB secara transparan.',
            'Membantu warga basis saat memerlukan surat pengantar atau pendaftaran sakramen.',
        ],
        donts: [
            'Mengubah atau mengedit data warga dari KUB lain.',
            'Menerbitkan sertifikat sakramen resmi (sertifikat hanya diterbitkan oleh Sekretariat Paroki).',
        ],
    },
    kapela: {
        key: 'kapela',
        level: 'Tingkat Stasi',
        name: 'Admin Stasi / Kapela',
        scope: 'Pengurus Dewan Stasi',
        badge: 'Scope Stasi',
        badgeColor: 'bg-teal-50 text-teal-700 border-teal-200',
        icon: 'fa-place-of-worship',
        headerGradient: 'from-teal-600 via-emerald-600 to-teal-800',
        summary: 'Pengelola administrasi stasi / kapela di luar pusat paroki, mengoordinasikan KUB binaan, inventaris gedung gereja, dan pelayanan liturgi stasi.',
        permissions: [
            {
                category: 'Data KK & Umat Stasi',
                status: 'view_only',
                statusText: 'Lihat Saja (View-Only)',
                badgeColor: 'bg-slate-100 text-slate-700 border-slate-200',
                icon: 'fa-users',
                details: 'Melihat seluruh KK dan data jiwa umat yang berdomisili di wilayah stasi / kapela.',
            },
            {
                category: 'Inventaris & Aset Stasi',
                status: 'crud',
                statusText: 'Kelola Aset Stasi',
                badgeColor: 'bg-emerald-50 text-emerald-800 border-emerald-200',
                icon: 'fa-boxes-stacked',
                details: 'Mencatat dan menginventarisir peralatan liturgi, gedung gereja, dan aset stasi.',
            },
            {
                category: 'Jadwal Misa & Liturgi',
                status: 'crud',
                statusText: 'Kelola Jadwal Stasi',
                badgeColor: 'bg-emerald-50 text-emerald-800 border-emerald-200',
                icon: 'fa-calendar-days',
                details: 'Menyusun jadwal perayaan ekaristi dan penugasan petugas liturgi stasi.',
            },
        ],
        dos: [
            'Memantau koordinasi pelayanan pastoral dan misa di tingkat stasi.',
            'Mendata inventaris dan kondisi sarana peribadatan stasi secara berkala.',
        ],
        donts: [
            'Menghapus data KK atau umat stasi (dilakukan oleh KUB / Paroki).',
            'Mengubah konfigurasi paroki pusat tanpa koordinasi.',
        ],
    },
    pastor: {
        key: 'pastor',
        level: 'Pimpinan Pastoral',
        name: 'Pastor Paroki / Pastor Rekan',
        scope: 'Pastor Paroki & Romo Rekan',
        badge: 'Otoritas Kanonik',
        badgeColor: 'bg-purple-50 text-purple-700 border-purple-200',
        icon: 'fa-user-tie',
        headerGradient: 'from-purple-700 via-indigo-700 to-purple-900',
        summary: 'Pemimpin tertinggi reksa pastoral paroki, memiliki otoritas kanonik untuk validasi sakramen, persetujuan perubahan Buku Liber, dan arahan kebijakan paroki.',
        permissions: [
            {
                category: 'Validasi Sakramen & Liber',
                status: 'approval',
                statusText: 'Otoritas Penuh & Approval',
                badgeColor: 'bg-purple-50 text-purple-700 border-purple-200',
                icon: 'fa-book-bible',
                details: 'Menyetujui pendaftaran sakramen, memeriksa kelayakan kanonik, dan mengesahkan pencatatan Liber.',
            },
            {
                category: 'Statistik Demografi Paroki',
                status: 'view_only',
                statusText: 'Monitoring Menyeluruh',
                badgeColor: 'bg-blue-50 text-blue-700 border-blue-200',
                icon: 'fa-chart-column',
                details: 'Memantau perkembangan jumlah umat, perkawinan, baptisan, dan persebaran wilayah paroki.',
            },
            {
                category: 'Keuangan & Aset Paroki',
                status: 'view_only',
                statusText: 'Pengawasan Keuangan',
                badgeColor: 'bg-emerald-50 text-emerald-700 border-emerald-200',
                icon: 'fa-scale-balanced',
                details: 'Memeriksa laporan kas bulanan, perolehan kolekte, dan saldo kas paroki.',
            },
        ],
        dos: [
            'Memeriksa dan menyetujui berkas permohonan sakramen perkawinan dan inisiasi.',
            'Memberikan disposisi dan catatan pastoral pada dokumen umat.',
        ],
        donts: [
            'Membiarkan perubahan Buku Liber dilakukan tanpa verifikasi dokumen kanonik.',
        ],
    },
    bendahara: {
        key: 'bendahara',
        level: 'Pengelola Keuangan',
        name: 'Bendahara Paroki',
        scope: 'Tim Ekonomi DPP',
        badge: 'Akses Keuangan Penuh',
        badgeColor: 'bg-emerald-50 text-emerald-700 border-emerald-200',
        icon: 'fa-money-bill-wave',
        headerGradient: 'from-emerald-600 via-teal-700 to-emerald-800',
        summary: 'Mengelola tata buku keuangan paroki secara transparan dan akuntabel, termasuk kas masuk/keluar, kolekte misa, setoran iuran, dan laporan keuangan paroki.',
        permissions: [
            {
                category: 'Buku Kas & Transaksi',
                status: 'crud',
                statusText: 'Kelola Penuh Kas',
                badgeColor: 'bg-emerald-50 text-emerald-700 border-emerald-200',
                icon: 'fa-book-open-reader',
                details: 'Mencatat seluruh arus kas masuk, kas keluar, mutasi bank, dan bukti nota.',
            },
            {
                category: 'Kolekte Misa & Persembahan',
                status: 'crud',
                statusText: 'Pencatatan Kolekte',
                badgeColor: 'bg-emerald-50 text-emerald-700 border-emerald-200',
                icon: 'fa-hand-holding-dollar',
                details: 'Menginput perolehan kolekte I, kolekte II, dan amplop persembahan misa.',
            },
            {
                category: 'Laporan Keuangan',
                status: 'export',
                statusText: 'Cetak Laporan',
                badgeColor: 'bg-teal-50 text-teal-700 border-teal-200',
                icon: 'fa-print',
                details: 'Mencetak laporan kas bulanan, rekap tahunan, dan neraca keuangan paroki.',
            },
        ],
        dos: [
            'Rutin merekonsiliasi saldo kas fisik dengan mutasi rekening bank paroki.',
            'Menyimpan kuitansi dan bukti pengeluaran dengan rapi.',
        ],
        donts: [
            'Mengeluarkan dana tanpa bukti kas keluar yang disetujui Pastor Paroki / DPP.',
        ],
    },
    paroki: {
        key: 'paroki',
        level: 'Sekretariat Utama',
        name: 'Admin Paroki',
        scope: 'Sekretariat Kantor Paroki',
        badge: 'Operator Induk',
        badgeColor: 'bg-amber-50 text-amber-700 border-amber-200',
        icon: 'fa-folder-tree',
        headerGradient: 'from-amber-600 via-orange-600 to-amber-800',
        summary: 'Operator utama tata usaha kantor paroki untuk pengelolaan data induk KK Katolik, pencatatan Buku Liber Sakramen, surat menyurat resmi ber-QR Code, dan agenda paroki.',
        permissions: [
            {
                category: 'Buku Induk KK & Umat',
                status: 'crud',
                statusText: 'Kelola Induk Paroki',
                badgeColor: 'bg-emerald-50 text-emerald-700 border-emerald-200',
                icon: 'fa-address-book',
                details: 'Mendaftarkan, memverifikasi, dan mencetak Buku Induk KK Katolik se-paroki.',
            },
            {
                category: 'Buku Liber & Surat Sakramen',
                status: 'crud',
                statusText: 'Pencatatan & Cetak Surat',
                badgeColor: 'bg-emerald-50 text-emerald-700 border-emerald-200',
                icon: 'fa-certificate',
                details: 'Mencatat nomor liber permandian, krisma, pernikahan, dan mencetak sertifikat ber-QR Code.',
            },
            {
                category: 'Agenda & Surat Resmi',
                status: 'crud',
                statusText: 'Tata Usaha Surat',
                badgeColor: 'bg-blue-50 text-blue-700 border-blue-200',
                icon: 'fa-envelope-open-text',
                details: 'Mengelola penomoran surat masuk, surat keluar, surat pengantar, dan arsip paroki.',
            },
        ],
        dos: [
            'Memverifikasi keaslian dokumen pendukung sebelum menerbitkan akta atau surat sakramen.',
            'Menjaga kerahasiaan data pribadi umat paroki.',
        ],
        donts: [
            'Mengubah catatan Buku Liber tanpa otorisasi/persetujuan Pastor Paroki.',
        ],
    },
    penulis: {
        key: 'penulis',
        level: 'Media & Publikasi',
        name: 'Penulis / Tim Komsos',
        scope: 'Tim Komsos & Publikasi Paroki',
        badge: 'Redaksi Konten',
        badgeColor: 'bg-indigo-50 text-indigo-700 border-indigo-200',
        icon: 'fa-newspaper',
        headerGradient: 'from-indigo-600 via-purple-600 to-indigo-800',
        summary: 'Tim publikasi, warta jemaat, berita online, galeri foto liputan, dan artikel rohani di website paroki.',
        permissions: [
            {
                category: 'Warta Berita & Artikel',
                status: 'crud',
                statusText: 'Tulis & Terbitkan',
                badgeColor: 'bg-emerald-50 text-emerald-700 border-emerald-200',
                icon: 'fa-pen-nib',
                details: 'Menulis berita kegiatan paroki, artikel renungan, pengumuman misa, dan warta jemaat.',
            },
            {
                category: 'Galeri Foto & Video',
                status: 'crud',
                statusText: 'Kelola Dokumentasi',
                badgeColor: 'bg-indigo-50 text-indigo-700 border-indigo-200',
                icon: 'fa-images',
                details: 'Mengunggah dokumentasi perayaan liturgi, foto sakramen, dan video kegiatan gereja.',
            },
        ],
        dos: [
            'Menyajikan berita dan informasi paroki yang akurat, santun, dan membangun iman umat.',
        ],
        donts: [
            'Mengubah atau mengakses data sensitif umat, keuangan, dan Buku Liber.',
        ],
    },
    umat: {
        key: 'umat',
        level: 'Portal Warga',
        name: 'Umat / Kepala Keluarga',
        scope: 'Keluarga Umat Paroki',
        badge: 'Portal Mandiri',
        badgeColor: 'bg-slate-100 text-slate-700 border-slate-200',
        icon: 'fa-house-user',
        headerGradient: 'from-slate-700 via-slate-800 to-slate-900',
        summary: 'Akses digital mandiri untuk kepala keluarga dan seluruh umat paroki untuk melihat KK digital, mengajukan pendaftaran sakramen, dan memantau riwayat pelayanan.',
        permissions: [
            {
                category: 'KK Katolik Digital (KKK)',
                status: 'view_only',
                statusText: 'Lihat KK Keluarga',
                badgeColor: 'bg-blue-50 text-blue-700 border-blue-200',
                icon: 'fa-id-card',
                details: 'Melihat lembar Kartu Keluarga Katolik keluarga sendiri secara digital kapan saja.',
            },
            {
                category: 'Pendaftaran Sakramen',
                status: 'create',
                statusText: 'Ajukan dari Rumah',
                badgeColor: 'bg-emerald-50 text-emerald-700 border-emerald-200',
                icon: 'fa-file-circle-plus',
                details: 'Mendaftarkan anggota keluarga untuk menerima Sakramen Baptis, Komuni Pertama, Krisma, atau Pernikahan.',
            },
        ],
        dos: [
            'Memeriksa kebenaran data anggota keluarga di KK digital.',
        ],
        donts: [
            'Mencoba mengakses data keluarga atau KUB lain.',
        ],
    },
    superadmin: {
        key: 'superadmin',
        level: 'Level 1 - Sistem',
        name: 'Super Admin',
        scope: 'Tim IT / Webmaster',
        badge: 'Akses Penuh',
        badgeColor: 'bg-rose-50 text-rose-700 border-rose-200',
        icon: 'fa-shield-halved',
        headerGradient: 'from-rose-600 via-amber-600 to-rose-800',
        summary: 'Pengelola teknis tertinggi, konfigurasi database, pemeliharaan keamanan aplikasi, backup/restore, dan manajemen akun pengguna.',
        permissions: [
            {
                category: 'Konfigurasi Sistem',
                status: 'full',
                statusText: 'Akses Tertinggi',
                badgeColor: 'bg-rose-50 text-rose-700 border-rose-200',
                icon: 'fa-gears',
                details: 'Mengatur parameter aplikasi, modul, keamanan, dan integrasi server.',
            },
            {
                category: 'Backup & Security',
                status: 'full',
                statusText: 'Proteksi Penuh',
                badgeColor: 'bg-rose-50 text-rose-700 border-rose-200',
                icon: 'fa-database',
                details: 'Membuat backup database, restore, dan memantau log keamanan.',
            },
        ],
        dos: [
            'Melakukan backup database secara rutin.',
            'Memantau keamanan sistem dari akses tidak sah.',
        ],
        donts: [
            'Menggunakan password yang mudah ditebak.',
        ],
    },
};

const activeRoleData = computed(() => {
    return roleDetails[currentViewingKey.value] || roleDetails.wilayah;
});

const tabList = [
    { key: 'wilayah', label: 'Admin Wilayah', icon: 'fa-compass' },
    { key: 'kub', label: 'Ketua KUB', icon: 'fa-people-group' },
    { key: 'kapela', label: 'Admin Kapela / Stasi', icon: 'fa-place-of-worship' },
    { key: 'pastor', label: 'Pastor Paroki', icon: 'fa-user-tie' },
    { key: 'bendahara', label: 'Bendahara Paroki', icon: 'fa-money-bill-wave' },
    { key: 'paroki', label: 'Admin Paroki', icon: 'fa-folder-tree' },
    { key: 'penulis', label: 'Penulis / Komsos', icon: 'fa-newspaper' },
    { key: 'umat', label: 'Umat', icon: 'fa-house-user' },
    { key: 'superadmin', label: 'Super Admin', icon: 'fa-shield-halved' },
];
</script>

<template>
    <AppLayout title="Panduan Peran & Hak Akses">
        <Head :title="`Panduan Hak Akses ${activeRoleData.name} - SIPAROKI`" />

        <!-- 1. HERO HEADER: KHUSUS SESUAI PERAN AKTIF -->
        <div :class="['rounded-3xl bg-gradient-to-r p-6 sm:p-8 text-white shadow-lg mb-6 transition-all duration-300', activeRoleData.headerGradient]">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="space-y-3">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/20 backdrop-blur-md text-xs font-bold text-white border border-white/30">
                        <i :class="['fa-solid', activeRoleData.icon]"></i>
                        <span>{{ activeRoleData.level }} &bull; {{ activeRoleData.badge }}</span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl lg:text-4xl font-black tracking-tight leading-tight">
                        Panduan Wewenang: {{ activeRoleData.name }}
                    </h1>
                    <p class="text-sm text-white/90 max-w-3xl leading-relaxed">
                        {{ activeRoleData.summary }}
                    </p>
                </div>

                <!-- Role Status Card -->
                <div class="p-5 rounded-2xl bg-white/10 backdrop-blur-md border border-white/20 text-center shrink-0 min-w-56 shadow-xs">
                    <span class="text-[11px] uppercase tracking-wider text-white/80 font-bold block">Status Peran Anda</span>
                    <span class="text-xl font-black text-white block mt-1">{{ activeRoleData.name }}</span>
                    <span class="text-[11px] font-medium text-white/80 block mt-0.5">{{ activeRoleData.scope }}</span>
                    <div class="mt-3 inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-200 border border-emerald-300/30 text-xs font-bold">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span>Otoritas Aktif</span>
                    </div>
                </div>
            </div>

            <!-- Tab Switcher (Only visible for Superadmin / Admin Paroki to preview roles) -->
            <div v-if="isSuperOrParoki" class="mt-6 pt-5 border-t border-white/20">
                <span class="text-xs text-white/80 font-semibold block mb-2">Pratinjau Panduan Peran Lain (Khusus Superadmin/Paroki):</span>
                <div class="flex items-center gap-2 overflow-x-auto pb-1 custom-scrollbar">
                    <button
                        v-for="tab in tabList"
                        :key="tab.key"
                        type="button"
                        @click="selectedRoleTab = tab.key"
                        :class="[
                            'px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 shrink-0 cursor-pointer',
                            selectedRoleTab === tab.key
                                ? 'bg-white text-slate-900 shadow-sm'
                                : 'bg-white/10 hover:bg-white/20 text-white border border-white/20'
                        ]"
                    >
                        <i :class="['fa-solid', tab.icon, 'text-[11px]']"></i>
                        <span>{{ tab.label }}</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- 2. DAFTAR MODUL & WEWENANG KHUSUS PERAN INI -->
        <div class="mb-8">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-base sm:text-lg font-black text-slate-900 flex items-center gap-2">
                        <i class="fa-solid fa-list-check text-blue-600"></i>
                        <span>Cakupan Modul & Hak Akses Fitur</span>
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">Berikut adalah modul yang dapat Anda akses sesuai dengan batas wewenang {{ activeRoleData.name }}:</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div
                    v-for="perm in activeRoleData.permissions"
                    :key="perm.category"
                    class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs hover:shadow-md transition flex flex-col justify-between"
                >
                    <div>
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 border border-blue-200 flex items-center justify-center text-lg shrink-0">
                                <i :class="['fa-solid', perm.icon]"></i>
                            </div>
                            <span :class="['px-2.5 py-1 rounded-full text-[11px] font-bold border', perm.badgeColor]">
                                {{ perm.statusText }}
                            </span>
                        </div>
                        <h3 class="font-bold text-slate-900 text-sm mb-1.5">{{ perm.category }}</h3>
                        <p class="text-xs text-slate-600 leading-relaxed">{{ perm.details }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. PANDUAN PELAKSANAAN: APA YANG BOLEH & TIDAK BOLEH -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- DO'S -->
            <div class="p-6 rounded-3xl bg-white border border-emerald-200/80 shadow-xs">
                <div class="flex items-center gap-3 mb-4 pb-3 border-b border-emerald-100">
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg shrink-0 border border-emerald-200">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                    <div>
                        <h3 class="font-black text-slate-900 text-sm">Wewenang & Tugas Pelayanan</h3>
                        <p class="text-xs text-slate-500">Hal yang dapat dan dianjurkan Anda lakukan</p>
                    </div>
                </div>

                <ul class="space-y-3">
                    <li
                        v-for="(item, idx) in activeRoleData.dos"
                        :key="idx"
                        class="flex items-start gap-2.5 text-xs text-slate-700 leading-relaxed"
                    >
                        <i class="fa-solid fa-check text-emerald-600 font-bold mt-0.5 shrink-0 text-sm"></i>
                        <span>{{ item }}</span>
                    </li>
                </ul>
            </div>

            <!-- DONT'S -->
            <div class="p-6 rounded-3xl bg-white border border-rose-200/80 shadow-xs">
                <div class="flex items-center gap-3 mb-4 pb-3 border-b border-rose-100">
                    <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-lg shrink-0 border border-rose-200">
                        <i class="fa-solid fa-circle-xmark"></i>
                    </div>
                    <div>
                        <h3 class="font-black text-slate-900 text-sm">Batasan Wewenang & Privasi</h3>
                        <p class="text-xs text-slate-500">Hal yang dibatasi untuk menjaga integritas data</p>
                    </div>
                </div>

                <ul class="space-y-3">
                    <li
                        v-for="(item, idx) in activeRoleData.donts"
                        :key="idx"
                        class="flex items-start gap-2.5 text-xs text-slate-700 leading-relaxed"
                    >
                        <i class="fa-solid fa-xmark text-rose-600 font-bold mt-0.5 shrink-0 text-sm"></i>
                        <span>{{ item }}</span>
                    </li>
                </ul>
            </div>
        </div>

        <!-- 4. FOOTER INFO & ALUR KOORDINASI -->
        <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-600">
            <div class="flex items-center gap-3">
                <i class="fa-solid fa-shield-halved text-blue-600 text-lg"></i>
                <span>Sistem isolasi data otomatis menjamin kerahasiaan dan privasi data umat antar-wilayah.</span>
            </div>
            <Link
                :href="`/${pathPrefix}/dashboard`"
                class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs transition shadow-xs flex items-center gap-1.5 shrink-0"
            >
                <i class="fa-solid fa-arrow-left"></i>
                <span>Kembali ke Dashboard</span>
            </Link>
        </div>
    </AppLayout>
</template>
