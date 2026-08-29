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
    kub: {
        key: 'kub',
        level: 'Tingkat Basis',
        name: 'Ketua / Pengurus KUB',
        scope: 'Pengurus Komunitas Umat Basis',
        badge: 'Scope KUB (Ujung Tombak)',
        badgeColor: 'bg-cyan-50 text-cyan-700 border-cyan-200',
        icon: 'fa-people-group',
        headerGradient: 'from-cyan-600 via-teal-600 to-cyan-800',
        summary: 'Pelayan langsung di tingkat basis yang mendampingi keluarga umat, mengelola Kartu Keluarga Katolik, mutasi umat, iuran, kas internal, dan pengajuan sakramen.',
        permissions: [
            {
                category: 'Kartu Keluarga & Data Jiwa',
                status: 'crud',
                statusText: 'Full Kelola (CRUD)',
                badgeColor: 'bg-emerald-50 text-emerald-800 border-emerald-200',
                icon: 'fa-address-card',
                details: 'Mendaftarkan KK baru warga basis, memperbarui biodata umat, status sakramen, tanggal lahir, dan mencetak lembar KK Katolik.',
            },
            {
                category: 'Mutasi Umat Antar-KUB',
                status: 'crud',
                statusText: 'Mutasi & Riwayat Otomatis',
                badgeColor: 'bg-blue-50 text-blue-800 border-blue-200',
                icon: 'fa-right-left',
                details: 'Memproses mutasi umat pindah ke KUB lain dalam paroki atau keluar paroki dengan pencatatan alasan wajib dan notifikasi otomatis.',
            },
            {
                category: 'Notifikasi & Chat Internal',
                status: 'realtime',
                statusText: 'Interaktif Real-Time',
                badgeColor: 'bg-indigo-50 text-indigo-800 border-indigo-200',
                icon: 'fa-comments',
                details: 'Menerima notifikasi lonceng saat ada mutasi umat masuk dan berkirim pesan/screenshot langsung dengan Pastor dan Admin Paroki.',
            },
            {
                category: 'Iuran & Kas KUB',
                status: 'crud',
                statusText: 'Pencatatan Kas Basis',
                badgeColor: 'bg-emerald-50 text-emerald-800 border-emerald-200',
                icon: 'fa-wallet',
                details: 'Mencatat kas masuk/keluar internal KUB dan menginput setoran iuran wajib umat secara berkala.',
            },
            {
                category: 'Lapak & Usaha Umat',
                status: 'crud',
                statusText: 'Pemberdayaan Ekonomi',
                badgeColor: 'bg-amber-50 text-amber-800 border-amber-200',
                icon: 'fa-shop',
                details: 'Membantu mendaftarkan produk usaha atau jasa warga KUB ke dalam direktori Lapak Umat Paroki.',
            },
            {
                category: 'Pengajuan Sakramen',
                status: 'create',
                statusText: 'Ajukan ke Paroki',
                badgeColor: 'bg-purple-50 text-purple-800 border-purple-200',
                icon: 'fa-file-signature',
                details: 'Mendaftarkan warga basis yang akan menerima Sakramen Baptis, Komuni Pertama, Krisma, atau Pernikahan.',
            },
        ],
        dos: [
            'Memastikan seluruh keluarga Katolik di basis terdata lengkap dalam Kartu Keluarga (KK).',
            'Mencatat mutasi umat keluar/masuk dengan menyertakan alasan yang jelas dan akurat.',
            'Rutin mencatat iuran wajib dan kas KUB secara terbuka dan transparan.',
            'Menggunakan Chat Internal untuk koordinasi cepat pelayanan umat dengan sekretariat paroki.',
        ],
        donts: [
            'Mengubah atau mengedit data warga dari KUB lain di luar basis Anda.',
            'Menerbitkan sertifikat sakramen resmi (sertifikat resmi hanya diterbitkan oleh Sekretariat Paroki).',
            'Menghapus data KK tanpa koordinasi atau prosedur mutasi yang benar.',
        ],
    },
    wilayah: {
        key: 'wilayah',
        level: 'Tingkat Koordinator',
        name: 'Admin Wilayah',
        scope: 'Pengurus Wilayah Rohani',
        badge: 'Scope Wilayah',
        badgeColor: 'bg-blue-50 text-blue-700 border-blue-200',
        icon: 'fa-compass',
        headerGradient: 'from-blue-600 via-indigo-600 to-blue-800',
        summary: 'Koordinator kewilayahan paroki yang bertugas membina, memantau data demografi, pergerakan mutasi umat, serta merekapitulasi iuran dan administrasi KUB-KUB binaan.',
        permissions: [
            {
                category: 'Monitoring KK & Umat',
                status: 'view_only',
                statusText: 'Lihat Saja (View-Only)',
                badgeColor: 'bg-slate-100 text-slate-700 border-slate-200',
                icon: 'fa-users',
                details: 'Melihat seluruh daftar KK Katolik dan profil jiwa umat yang terdaftar di wilayah binaan Anda beserta fitur pencarian dan cetak.',
            },
            {
                category: 'Pemantauan Mutasi Umat',
                status: 'monitoring',
                statusText: 'Monitoring Teritorial',
                badgeColor: 'bg-blue-50 text-blue-700 border-blue-200',
                icon: 'fa-right-left',
                details: 'Memantau arus mutasi umat antar-KUB dalam wilayah binaan dan mutasi masuk dari wilayah/paroki lain.',
            },
            {
                category: 'Iuran & Rekap Wilayah',
                status: 'manage',
                statusText: 'Kelola Rekap Iuran',
                badgeColor: 'bg-emerald-50 text-emerald-800 border-emerald-200',
                icon: 'fa-money-bill-wave',
                details: 'Mencatat, memverifikasi, dan merekapitulasi setoran iuran umat dari masing-masing KUB di wilayah Anda.',
            },
            {
                category: 'Statistik & Demografi',
                status: 'view_only',
                statusText: 'Statistik Real-Time',
                badgeColor: 'bg-indigo-50 text-indigo-700 border-indigo-200',
                icon: 'fa-chart-pie',
                details: 'Memantau statistik total jiwa, KK, jumlah KUB, piramida usia, dan perbandingan gender di wilayah Anda.',
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
            'Mencatat dan merekapitulasi setoran iuran umat dari para ketua/kolektor KUB.',
            'Memanfaatkan data statistik wilayah untuk perencanaan pastoral dan program bina iman.',
        ],
        donts: [
            'Menambah, mengedit, atau menghapus data KK & Umat secara langsung (dilakukan oleh Ketua KUB atau Sekretariat Paroki).',
            'Mengakses atau mengubah data milik Wilayah lain (isolasi data teritorial berlaku otomatis).',
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
        summary: 'Pengelola administrasi stasi / kapela di luar pusat paroki, mengoordinasikan KUB binaan, inventaris gedung gereja stasi, dan pelayanan liturgi.',
        permissions: [
            {
                category: 'Data KK & Umat Stasi',
                status: 'view_only',
                statusText: 'Lihat Saja (View-Only)',
                badgeColor: 'bg-slate-100 text-slate-700 border-slate-200',
                icon: 'fa-users',
                details: 'Melihat seluruh KK dan data jiwa umat yang berdomisili di wilayah stasi / kapela binaan.',
            },
            {
                category: 'Inventaris & Aset Stasi',
                status: 'crud',
                statusText: 'Kelola Aset Stasi',
                badgeColor: 'bg-emerald-50 text-emerald-800 border-emerald-200',
                icon: 'fa-boxes-stacked',
                details: 'Mencatat dan menginventarisir sarana peribadatan, gedung kapela, dan aset milik stasi.',
            },
            {
                category: 'Jadwal Misa & Liturgi',
                status: 'crud',
                statusText: 'Kelola Jadwal Misa',
                badgeColor: 'bg-emerald-50 text-emerald-800 border-emerald-200',
                icon: 'fa-calendar-days',
                details: 'Menyusun jadwal perayaan ekaristi dan penugasan petugas liturgi di stasi.',
            },
        ],
        dos: [
            'Memantau koordinasi pelayanan pastoral dan jadwal perayaan ekaristi di tingkat stasi.',
            'Mendata kondisi inventaris dan fasilitas peribadatan stasi secara berkala.',
        ],
        donts: [
            'Menghapus data KK atau umat stasi (dilakukan oleh Ketua KUB atau Sekretariat Paroki).',
            'Mengubah konfigurasi induk paroki tanpa koordinasi.',
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
        summary: 'Pemimpin reksa pastoral paroki dengan otoritas kanonik untuk validasi sakramen, pengesahan Buku Liber, disposisi pelayanan umat, dan arahan kebijakan paroki.',
        permissions: [
            {
                category: 'Validasi Sakramen & Liber',
                status: 'approval',
                statusText: 'Otoritas & Approval',
                badgeColor: 'bg-purple-50 text-purple-700 border-purple-200',
                icon: 'fa-book-bible',
                details: 'Menyetujui permohonan sakramen, memeriksa kelayakan kanonik perkawinan/inisiasi, dan mengesahkan pencatatan Liber.',
            },
            {
                category: 'Statistik Demografi Paroki',
                status: 'view_only',
                statusText: 'Monitoring Menyeluruh',
                badgeColor: 'bg-blue-50 text-blue-700 border-blue-200',
                icon: 'fa-chart-column',
                details: 'Memantau perkembangan jumlah jiwa, perkawinan, baptisan, dan persebaran wilayah paroki secara real-time.',
            },
            {
                category: 'Keuangan & Aset Paroki',
                status: 'view_only',
                statusText: 'Pengawasan Keuangan',
                badgeColor: 'bg-emerald-50 text-emerald-700 border-emerald-200',
                icon: 'fa-scale-balanced',
                details: 'Memeriksa laporan kas bulanan, perolehan kolekte, dan neraca keuangan paroki.',
            },
            {
                category: 'Chat Pastoral & Koordinasi',
                status: 'realtime',
                statusText: 'Komunikasi Internal',
                badgeColor: 'bg-indigo-50 text-indigo-700 border-indigo-200',
                icon: 'fa-comments',
                details: 'Berkoordinasi langsung dengan para Ketua KUB, Admin Wilayah, dan Dewan Pastoral Paroki.',
            },
        ],
        dos: [
            'Memeriksa dan menyetujui berkas permohonan sakramen dan kanonika perkawinan.',
            'Memberikan disposisi dan catatan pastoral pada dokumen pelayanan umat.',
        ],
        donts: [
            'Membiarkan perubahan catatan Buku Liber dilakukan tanpa verifikasi dokumen kanonik.',
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
        summary: 'Mengelola tata buku keuangan paroki secara transparan dan akuntabel, termasuk kas masuk/keluar, kolekte misa, setoran iuran KUB, dan laporan neraca paroki.',
        permissions: [
            {
                category: 'Buku Kas & Transaksi',
                status: 'crud',
                statusText: 'Kelola Penuh Kas',
                badgeColor: 'bg-emerald-50 text-emerald-700 border-emerald-200',
                icon: 'fa-book-open-reader',
                details: 'Mencatat arus kas masuk, kas keluar, mutasi bank, dan bukti nota/kuitansi digital.',
            },
            {
                category: 'Kolekte Misa & Persembahan',
                status: 'crud',
                statusText: 'Pencatatan Kolekte',
                badgeColor: 'bg-emerald-50 text-emerald-700 border-emerald-200',
                icon: 'fa-hand-holding-dollar',
                details: 'Menginput perolehan kolekte I, kolekte II, dan persembahan misa mingguan maupun hari raya.',
            },
            {
                category: 'Laporan & Neraca Keuangan',
                status: 'export',
                statusText: 'Cetak & Ekspor',
                badgeColor: 'bg-teal-50 text-teal-700 border-teal-200',
                icon: 'fa-print',
                details: 'Mencetak laporan kas bulanan, rekapitulasi tahunan, dan neraca pertanggungjawaban keuangan.',
            },
        ],
        dos: [
            'Rutin merekonsiliasi saldo kas fisik dengan mutasi rekening bank paroki.',
            'Menyimpan kuitansi dan bukti transfer/pengeluaran secara digital di sistem.',
        ],
        donts: [
            'Mengeluarkan dana tanpa bukti kas keluar yang disetujui Pastor Paroki / Tim DPP.',
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
        summary: 'Operator utama tata usaha kantor paroki untuk pengelolaan data induk KK Katolik, pencatatan Buku Liber Sakramen, registrasi surat ber-QR Code, dan arsip digital.',
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
                statusText: 'Pencatatan & Cetak Sertifikat',
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
                details: 'Mengelola penomoran surat masuk, surat keluar, surat pengantar, dan arsip dokumen digital.',
            },
        ],
        dos: [
            'Memverifikasi kelengkapan dokumen persyaratan sebelum menerbitkan akta atau surat sakramen.',
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
        summary: 'Tim publikasi warta jemaat, berita liputan paroki, renungan harian, galeri foto dokumentasi, dan artikel rohani di website paroki.',
        permissions: [
            {
                category: 'Warta Berita & Artikel',
                status: 'crud',
                statusText: 'Tulis & Publikasikan',
                badgeColor: 'bg-emerald-50 text-emerald-700 border-emerald-200',
                icon: 'fa-pen-nib',
                details: 'Menulis berita kegiatan paroki, artikel rohani, renungan harian, dan pengumuman warta jemaat.',
            },
            {
                category: 'Galeri Foto & Dokumentasi',
                status: 'crud',
                statusText: 'Kelola Dokumentasi',
                badgeColor: 'bg-indigo-50 text-indigo-700 border-indigo-200',
                icon: 'fa-images',
                details: 'Mengunggah dokumentasi perayaan liturgi, foto sakramen, dan album kegiatan gereja.',
            },
        ],
        dos: [
            'Menyajikan berita dan informasi paroki yang akurat, santun, dan membangun iman jemaat.',
        ],
        donts: [
            'Mengubah atau mengakses data rahasia umat, keuangan, dan Buku Liber.',
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
        summary: 'Akses digital mandiri untuk kepala keluarga dan seluruh umat paroki guna melihat KK digital, mendaftarkan sakramen secara online, dan melihat jadwal misa.',
        permissions: [
            {
                category: 'KK Katolik Digital (KKK)',
                status: 'view_only',
                statusText: 'Lihat KK Mandiri',
                badgeColor: 'bg-blue-50 text-blue-700 border-blue-200',
                icon: 'fa-id-card',
                details: 'Melihat lembar Kartu Keluarga Katolik keluarga sendiri secara digital kapan saja.',
            },
            {
                category: 'Pendaftaran Sakramen Online',
                status: 'create',
                statusText: 'Ajukan dari Rumah',
                badgeColor: 'bg-emerald-50 text-emerald-700 border-emerald-200',
                icon: 'fa-file-circle-plus',
                details: 'Mendaftarkan anggota keluarga untuk menerima Sakramen Baptis, Komuni Pertama, Krisma, atau Pernikahan.',
            },
        ],
        dos: [
            'Memeriksa kebenaran data anggota keluarga di lembar KK Katolik digital.',
        ],
        donts: [
            'Mencoba mengakses data keluarga atau KUB lain.',
        ],
    },
    superadmin: {
        key: 'superadmin',
        level: 'Level 1 - Sistem',
        name: 'Super Admin',
        scope: 'Tim IT / Administrator',
        badge: 'Akses Penuh',
        badgeColor: 'bg-rose-50 text-rose-700 border-rose-200',
        icon: 'fa-shield-halved',
        headerGradient: 'from-rose-600 via-amber-600 to-rose-800',
        summary: 'Pengelola teknis tertinggi sistem: konfigurasi aplikasi, pembersihan cache & file sistem, backup database real-time, audit keamanan, dan manajemen akun.',
        permissions: [
            {
                category: 'Pembersih Sistem & Cache',
                status: 'full',
                statusText: 'Optimalisasi Server',
                badgeColor: 'bg-rose-50 text-rose-700 border-rose-200',
                icon: 'fa-broom',
                details: 'Membersihkan cache aplikasi, view, route, dan memindai serta membersihkan file unggahan yatim (orphan).',
            },
            {
                category: 'Backup & Security Center',
                status: 'full',
                statusText: 'Proteksi Maksimal',
                badgeColor: 'bg-rose-50 text-rose-700 border-rose-200',
                icon: 'fa-database',
                details: 'Membuat snapshot backup database, restore, dan memantau log aktivitas keamanan.',
            },
            {
                category: 'Manajemen Pengguna & RBAC',
                status: 'full',
                statusText: 'Kontrol Hak Akses',
                badgeColor: 'bg-rose-50 text-rose-700 border-rose-200',
                icon: 'fa-user-shield',
                details: 'Mengelola akun operator paroki, penetapan peran, wilayah, dan stasi binaan.',
            },
        ],
        dos: [
            'Rutin membuat backup database sebelum melakukan update besar.',
            'Memantau keamanan sistem dan kinerja cache server secara berkala.',
        ],
        donts: [
            'Membagikan kredensial Super Admin kepada pihak yang tidak berwenang.',
        ],
    },
};

const activeRoleData = computed(() => {
    return roleDetails[currentViewingKey.value] || roleDetails.kub;
});

const tabList = [
    { key: 'kub', label: 'Ketua KUB', icon: 'fa-people-group' },
    { key: 'wilayah', label: 'Admin Wilayah', icon: 'fa-compass' },
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

        <div class="px-4 sm:px-6 py-6 w-full space-y-6 pb-24">
            <!-- 1. HERO HEADER: KHUSUS SESUAI PERAN AKTIF -->
            <div :class="['rounded-3xl bg-gradient-to-r p-6 sm:p-8 text-white shadow-xl transition-all duration-300', activeRoleData.headerGradient]">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div class="space-y-3">
                        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white/20 backdrop-blur-md text-xs font-bold text-white border border-white/30">
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
                    <span class="text-xs text-white/80 font-semibold block mb-2">Pratinjau Panduan Peran Lain (Khusus Super Admin &amp; Admin Paroki):</span>
                    <div class="flex items-center gap-2 overflow-x-auto pb-1 custom-scrollbar">
                        <button
                            v-for="tab in tabList"
                            :key="tab.key"
                            type="button"
                            @click="selectedRoleTab = tab.key"
                            :class="[
                                'px-3.5 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 shrink-0 cursor-pointer shadow-2xs',
                                selectedRoleTab === tab.key
                                    ? 'bg-white text-slate-900 shadow-md font-black'
                                    : 'bg-white/15 hover:bg-white/25 text-white border border-white/20'
                            ]"
                        >
                            <i :class="['fa-solid', tab.icon, 'text-xs']"></i>
                            <span>{{ tab.label }}</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- 2. DAFTAR MODUL & WEWENANG KHUSUS PERAN INI -->
            <div>
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-base sm:text-lg font-black text-slate-900 dark:text-white flex items-center gap-2">
                            <i class="fa-solid fa-list-check text-blue-600"></i>
                            <span>Cakupan Modul &amp; Hak Akses Fitur</span>
                        </h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Berikut adalah modul yang dapat Anda akses sesuai dengan batas wewenang {{ activeRoleData.name }}:</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div
                        v-for="perm in activeRoleData.permissions"
                        :key="perm.category"
                        class="p-5 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 shadow-xs hover:shadow-md transition flex flex-col justify-between"
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
                            <h3 class="font-bold text-slate-900 dark:text-white text-sm mb-1.5">{{ perm.category }}</h3>
                            <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed">{{ perm.details }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. PANDUAN PELAKSANAAN: APA YANG BOLEH & TIDAK BOLEH -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- DO'S -->
                <div class="p-6 rounded-3xl bg-white dark:bg-slate-800 border border-emerald-200/80 dark:border-emerald-700/80 shadow-xs">
                    <div class="flex items-center gap-3 mb-4 pb-3 border-b border-emerald-100 dark:border-emerald-800/60">
                        <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg shrink-0 border border-emerald-200">
                            <i class="fa-solid fa-circle-check"></i>
                        </div>
                        <div>
                            <h3 class="font-black text-slate-900 dark:text-white text-sm">Wewenang &amp; Tugas Pelayanan</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Hal yang dapat dan dianjurkan Anda lakukan</p>
                        </div>
                    </div>

                    <ul class="space-y-3">
                        <li
                            v-for="(item, idx) in activeRoleData.dos"
                            :key="idx"
                            class="flex items-start gap-2.5 text-xs text-slate-700 dark:text-slate-300 leading-relaxed"
                        >
                            <i class="fa-solid fa-check text-emerald-600 font-bold mt-0.5 shrink-0 text-sm"></i>
                            <span>{{ item }}</span>
                        </li>
                    </ul>
                </div>

                <!-- DONT'S -->
                <div class="p-6 rounded-3xl bg-white dark:bg-slate-800 border border-rose-200/80 dark:border-rose-700/80 shadow-xs">
                    <div class="flex items-center gap-3 mb-4 pb-3 border-b border-rose-100 dark:border-rose-800/60">
                        <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-lg shrink-0 border border-rose-200">
                            <i class="fa-solid fa-circle-xmark"></i>
                        </div>
                        <div>
                            <h3 class="font-black text-slate-900 dark:text-white text-sm">Batasan Wewenang &amp; Privasi</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Hal yang dibatasi untuk menjaga integritas dan kerahasiaan data</p>
                        </div>
                    </div>

                    <ul class="space-y-3">
                        <li
                            v-for="(item, idx) in activeRoleData.donts"
                            :key="idx"
                            class="flex items-start gap-2.5 text-xs text-slate-700 dark:text-slate-300 leading-relaxed"
                        >
                            <i class="fa-solid fa-xmark text-rose-600 font-bold mt-0.5 shrink-0 text-sm"></i>
                            <span>{{ item }}</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- 4. FOOTER INFO & ALUR KOORDINASI -->
            <div class="p-5 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-600 dark:text-slate-400 shadow-2xs">
                <div class="flex items-center gap-3">
                    <i class="fa-solid fa-shield-halved text-blue-600 text-lg"></i>
                    <span>Sistem isolasi data otomatis menjamin kerahasiaan dan privasi data umat antar-wilayah &amp; KUB.</span>
                </div>
                <Link
                    :href="`/${pathPrefix}/dashboard`"
                    class="px-4.5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs transition shadow-xs flex items-center gap-1.5 shrink-0"
                >
                    <i class="fa-solid fa-arrow-left"></i>
                    <span>Kembali ke Dashboard</span>
                </Link>
            </div>
        </div>
    </AppLayout>
</template>
