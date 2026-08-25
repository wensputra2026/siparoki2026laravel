<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';

const props = defineProps({
    title: {
        type: String,
        default: 'Dashboard',
    },
    role: {
        type: String,
        default: '',
    },
    prefix: {
        type: String,
        default: '',
    },
    fullWidth: {
        type: Boolean,
        default: false,
    },
});

const page = usePage();
const isSidebarOpen = ref(true);
// On narrow viewports (tablet / small laptop) the icon-only rail is unusable,
// so we force the expanded, labeled sidebar regardless of the user's toggle.
const isNarrow = ref(false);
const sidebarExpanded = computed(() => isNarrow.value ? true : isSidebarOpen.value);
const syncNarrow = () => {
    if (typeof window === 'undefined' || !window.matchMedia) return;
    isNarrow.value = window.matchMedia('(max-width: 1279px)').matches;
    if (isNarrow.value) isSidebarOpen.value = true;
};
const isMobileOpen = ref(false);
const showLogoutModal = ref(false);
const layoutStorageKey = 'siparoki.backend.layout';

const toggleSidebar = () => {
    isSidebarOpen.value = !isSidebarOpen.value;
};

// Toast notification state
const showToast = ref(false);
const toastMessage = ref('');
const toastType = ref('success');
let toastTimer = null;

const triggerToast = (msg, type = 'success') => {
    if (!msg) return;
    toastMessage.value = msg;
    toastType.value = type;
    showToast.value = true;
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => {
        showToast.value = false;
    }, 4000);
};

// Watch for incoming flash messages from Laravel
watch(
    () => page.props.flash,
    (flash) => {
        if (flash?.success) triggerToast(flash.success, 'success');
        else if (flash?.status) triggerToast(flash.status, 'success');
        else if (flash?.error) triggerToast(flash.error, 'error');
    },
    { deep: true, immediate: true }
);

const confirmLogout = () => {
    showLogoutModal.value = false;
    window.location.href = '/logout';
};

const userAvatar = computed(() => {
    const foto = page.props.auth?.user?.foto;
    if (!foto) return null;
    if (foto.startsWith('http://') || foto.startsWith('https://') || foto.startsWith('data:')) {
        return foto;
    }
    if (foto.startsWith('/')) {
        return foto;
    }
    return '/' + foto;
});

const userName = computed(() => {
    return page.props.auth?.user?.name || 'Profil';
});

// Scope / Target Context Selection State
const selectedPastorId = ref('');
const selectedWilayahId = ref('');
const selectedKapelaId = ref('');
const selectedKubId = ref('');

const pastorsList = computed(() => page.props.scopeOptions?.pastors || []);
const wilayahList = computed(() => page.props.scopeOptions?.wilayah || []);
const kapelaList = computed(() => page.props.scopeOptions?.kapela || []);
const kubList = computed(() => page.props.scopeOptions?.kub || []);

// Current authenticated user role or active preview role
const userActualRole = computed(() => {
    return page.props.auth?.user?.role || '';
});

const isSuperAdmin = computed(() => {
    const r = (userActualRole.value || '').toLowerCase();
    return r.includes('super');
});

const resolveRoleFromPath = () => {
    if (typeof window === 'undefined') return '';
    const path = window.location.pathname.toLowerCase();
    if (path.startsWith('/wilayah')) return 'Admin Wilayah';
    if (path.startsWith('/kapela') || path.startsWith('/stasi')) return 'Admin Kapela / Stasi';
    if (path.startsWith('/kub')) return 'Ketua KUB';
    if (path.startsWith('/bendahara')) return 'Bendahara';
    if (path.startsWith('/penulis')) return 'Penulis';
    if (path.startsWith('/umat')) return 'Umat';
    if (path.startsWith('/pastor')) return 'Pastor';
    if (path.startsWith('/paroki')) return 'Admin Paroki';
    if (path.startsWith('/superadmin')) return 'Super Admin';
    return '';
};

const activeRole = ref(props.role || page.props.role || resolveRoleFromPath() || page.props.auth?.user?.role || 'Super Admin');

// Watch for prop role and URL changes
watch(
    () => [props.role, page.props.role, page.url],
    ([newPropRole, newPageRole]) => {
        const fromPath = resolveRoleFromPath();
        const candidate = newPropRole || newPageRole || fromPath || page.props.auth?.user?.role;
        if (candidate && roleMenus[candidate]) {
            activeRole.value = candidate;
        }
    },
    { immediate: true }
);

const onRoleChange = () => {
    const map = {
        'Super Admin': '/superadmin',
        'Admin Paroki': '/paroki',
        'Pastor': '/pastor',
        'Admin Wilayah': '/wilayah',
        'Admin Kapela / Stasi': '/kapela',
        'Ketua KUB': '/kub',
        'Bendahara': '/bendahara',
        'Penulis': '/penulis',
        'Umat': '/umat',
    };
    const targetUrl = map[activeRole.value] || '/superadmin';
    router.visit(targetUrl);
};

// Exact 9-Role Sidebar Hierarchies (Bagian -> Menu Utama -> Submenu)
const roleMenus = {
    'Super Admin': [
        {
            section: 'Dashboard',
            menus: [
                { name: 'Dashboard', href: '/v2/dashboard', icon: 'fa-gauge-high' },
                { name: 'Panduan', href: '/v2/panduan-hak-akses', icon: 'fa-book-open' },
            ],
        },
        {
            section: 'Wilayah & Referensi',
            menus: [
                {
                    name: 'Data Gerejawi',
                    icon: 'fa-church',
                    submenus: [
                        { name: 'Keuskupan', href: '/v2/keuskupan', icon: 'fa-building-columns' },
                        { name: 'Dekenat', href: '/v2/dekenat', icon: 'fa-building' },
                        { name: 'Paroki', href: '/v2/paroki', icon: 'fa-place-of-worship' },
                        { name: 'Kuasi Paroki', href: '/v2/kuasi-paroki', icon: 'fa-cross' },
                        { name: 'Stasi / Kapela', href: '/v2/kapela', icon: 'fa-map-location-dot' },
                        { name: 'Wilayah', href: '/v2/wilayah', icon: 'fa-compass' },
                        { name: 'KUB', href: '/v2/kub', icon: 'fa-people-group' },
                    ],
                },
                {
                    name: 'Wilayah Sipil',
                    icon: 'fa-map',
                    submenus: [
                        { name: 'Provinsi', href: '/v2/provinsi', icon: 'fa-map' },
                        { name: 'Kabupaten / Kota', href: '/v2/kabupaten', icon: 'fa-city' },
                        { name: 'Kecamatan', href: '/v2/kecamatan', icon: 'fa-map-pin' },
                        { name: 'Desa / Kelurahan', href: '/v2/desa-kelurahan', icon: 'fa-location-dot' },
                    ],
                },
                {
                    name: 'Data Referensi',
                    icon: 'fa-tags',
                    href: '/v2/wilayah',
                },
            ],
        },
        {
            section: 'Umat & Pelayanan',
            menus: [
                {
                    name: 'Pelayanan Paroki',
                    icon: 'fa-hands-holding-child',
                    submenus: [
                        { name: 'Direktori DPP', href: '/v2/direktori-dpp', icon: 'fa-users-gear' },
                        { name: 'Direktori Katekis', href: '/v2/direktori-katekis', icon: 'fa-book-open-reader' },
                        { name: 'Direktori Misdinar', href: '/v2/direktori-misdinar', icon: 'fa-hands-praying' },
                        { name: 'Riwayat Pastor', href: '/v2/riwayat-pastor', icon: 'fa-user-tie' },
                        { name: 'Kronik Paroki', href: '/v2/kronik-paroki', icon: 'fa-timeline' },
                        { name: 'Peran Kategorial', href: '/v2/peran-kategorial', icon: 'fa-layer-group' },
                        { name: 'Anggota Kategorial', href: '/v2/anggota-kategorial', icon: 'fa-user-check' },
                    ],
                },
                {
                    name: 'Jadwal Misa',
                    icon: 'fa-calendar-check',
                    submenus: [
                        { name: 'Daftar Jadwal Misa', href: '/v2/jadwal-misa', icon: 'fa-calendar-day' },
                        { name: 'Master Pastor & Kontak', href: '/v2/master-pastor', icon: 'fa-address-book' },
                        { name: 'Notifikasi WhatsApp OTP', href: '/v2/pengaturan-otp', icon: 'fa-comment-sms' },
                        { name: 'Pengaturan Jadwal', href: '/v2/pengaturan-aplikasi', icon: 'fa-gear' },
                    ],
                },
                {
                    name: 'Data KK',
                    icon: 'fa-house-chimney-user',
                    submenus: [
                        { name: 'KK Katolik', href: '/v2/kk-katolik', icon: 'fa-credit-card' },
                        { name: 'Mutasi Massal KUB', href: '/v2/kk-katolik', icon: 'fa-arrows-split-up-and-left' },
                        { name: 'Data Keluarga', href: '/v2/kk-katolik', icon: 'fa-users-rectangle' },
                        { name: 'Data Umat / Jiwa', href: '/v2/umat', icon: 'fa-user' },
                        { name: 'Demografi & Statistik', href: '/v2/statistik', icon: 'fa-chart-pie' },
                    ],
                },
                {
                    name: 'Data Sakramen',
                    icon: 'fa-book-bible',
                    submenus: [
                        { name: 'Buku Besar Sakramen', href: '/v2/sakramen', icon: 'fa-book-bookmark' },
                        { name: 'Pemeriksaan Kanonikal', href: '/v2/sakramen', icon: 'fa-stamp' },
                        { name: 'Verifikasi Cetak Surat', href: '/v2/sakramen', icon: 'fa-print' },
                        { name: 'Pengajuan Sakramen', href: '/v2/pengajuan-sakramen', icon: 'fa-file-signature' },
                        { name: 'Katekumen & Pembinaan', href: '/v2/pengajuan-sakramen', icon: 'fa-graduation-cap' },
                        { name: 'Pengaturan Pembayaran', href: '/v2/metode-pembayaran', icon: 'fa-qrcode' },
                    ],
                },
                {
                    name: 'Lapak & Toko',
                    icon: 'fa-shop',
                    href: '/v2/lapak-produk',
                },
            ],
        },
        {
            section: 'Sekretariat Paroki',
            menus: [
                {
                    name: 'Registrasi Surat',
                    icon: 'fa-envelope-open-text',
                    submenus: [
                        { name: 'Surat Masuk', href: '/v2/surat-masuk', icon: 'fa-inbox' },
                        { name: 'Surat Keluar', href: '/v2/surat-keluar', icon: 'fa-paper-plane' },
                    ],
                },
                {
                    name: 'Arsip Digital Paroki',
                    icon: 'fa-folder-tree',
                    href: '/v2/arsip-digital',
                },
                {
                    name: 'Rapat & Notulen',
                    icon: 'fa-handshake',
                    href: '/v2/rapat',
                },
            ],
        },
        {
            section: 'Keuangan & Aset',
            menus: [
                {
                    name: 'Keuangan & Iuran',
                    icon: 'fa-money-bill-transfer',
                    submenus: [
                        { name: 'Jenis Iuran', href: '/v2/jenis-iuran', icon: 'fa-tags' },
                        { name: 'Iuran Umat', href: '/v2/iuran', icon: 'fa-receipt' },
                        { name: 'Kolekte Misa', href: '/v2/kolekte', icon: 'fa-hand-holding-dollar' },
                        { name: 'Intensi Misa', href: '/v2/intensi-misa', icon: 'fa-heart' },
                        { name: 'Keuangan Paroki', href: '/v2/keuangan', icon: 'fa-vault' },
                    ],
                },
                {
                    name: 'Aset & Inventaris',
                    icon: 'fa-boxes-stacked',
                    href: '/v2/aset',
                },
            ],
        },
        {
            section: 'Website Paroki',
            menus: [
                {
                    name: 'Konten Website',
                    icon: 'fa-newspaper',
                    submenus: [
                        { name: 'Kategori Konten', href: '/v2/kategori-konten', icon: 'fa-folder-open' },
                        { name: 'Berita / Artikel', href: '/v2/konten', icon: 'fa-newspaper' },
                        { name: 'Agenda Kegiatan', href: '/v2/kegiatan', icon: 'fa-calendar-check' },
                        { name: 'Galeri Foto', href: '/v2/galeri', icon: 'fa-images' },
                        { name: 'Sambutan Pastor', href: '/v2/profil-paroki', icon: 'fa-comment-dots' },
                        { name: 'Pusat Unduhan', href: '/v2/download', icon: 'fa-cloud-arrow-down' },
                    ],
                },
                {
                    name: 'Pengaturan Web',
                    icon: 'fa-globe',
                    submenus: [
                        { name: 'Identitas Paroki', href: '/v2/pengaturan-aplikasi', icon: 'fa-sliders' },
                        { name: 'Widget Web', href: '/v2/widget', icon: 'fa-table-cells-large' },
                        { name: 'Meta Tag & SEO', href: '/v2/pengaturan-aplikasi', icon: 'fa-magnifying-glass' },
                        { name: 'Menu Website', href: '/v2/menu', icon: 'fa-bars' },
                        { name: 'Banner Slider', href: '/v2/slider', icon: 'fa-panorama' },
                        { name: 'Video Background', href: '/v2/video-header', icon: 'fa-video' },
                        { name: 'Mode Maintenance', href: '/v2/maintenance', icon: 'fa-screwdriver-wrench' },
                        { name: 'Pengaturan OTP WA', href: '/v2/pengaturan-otp', icon: 'fa-comments' },
                        { name: 'Pembayaran / QRIS', href: '/v2/metode-pembayaran', icon: 'fa-qrcode' },
                    ],
                },
            ],
        },
        {
            section: 'Sistem & Aplikasi',
            menus: [
                { name: 'Profil Paroki', href: '/v2/profil-paroki', icon: 'fa-church' },
                { name: 'Manajemen User', href: '/v2/user', icon: 'fa-user-gear' },
                { name: 'Role & Permission', href: '/v2/role', icon: 'fa-lock' },
                { name: 'Security Center', href: '/v2/security-settings', icon: 'fa-shield-halved' },
                { name: 'Backup & Restore', href: '/v2/backup-database', icon: 'fa-database' },
                { name: 'Profil Saya', href: '/v2/profil-saya', icon: 'fa-circle-user' },
                { name: 'Keluar', action: 'logout', icon: 'fa-arrow-right-from-bracket' },
            ],
        },
    ],
    'Pastor': [
        {
            section: 'Dashboard',
            menus: [
                { name: 'Dashboard', href: '/pastor/dashboard', icon: 'fa-gauge-high' },
                { name: 'Panduan', href: '/pastor/panduan-hak-akses', icon: 'fa-book-open' },
            ],
        },
        {
            section: 'Pelayanan Pastoral',
            menus: [
                {
                    name: 'Pelayanan Paroki',
                    icon: 'fa-hands-holding-child',
                    submenus: [
                        { name: 'Direktori DPP', href: '/pastor/direktori-dpp', icon: 'fa-users-gear' },
                        { name: 'Direktori Katekis', href: '/pastor/direktori-katekis', icon: 'fa-book-open-reader' },
                        { name: 'Direktori Misdinar', href: '/pastor/direktori-misdinar', icon: 'fa-hands-praying' },
                        { name: 'Riwayat Pastor', href: '/pastor/riwayat-pastor', icon: 'fa-user-tie' },
                        { name: 'Kronik Paroki', href: '/pastor/kronik-paroki', icon: 'fa-timeline' },
                        { name: 'Jadwal Misa', href: '/pastor/jadwal-misa', icon: 'fa-calendar-check' },
                        { name: 'Peran & Anggota Kategorial', href: '/pastor/anggota-kategorial', icon: 'fa-user-check' },
                    ],
                },
                { name: 'KK Katolik', href: '/pastor/kk-katolik', icon: 'fa-credit-card' },
                { name: 'Data Keluarga', href: '/pastor/kk-katolik', icon: 'fa-users-rectangle' },
                { name: 'Data Umat / Jiwa', href: '/pastor/umat', icon: 'fa-user' },
                { name: 'Demografi & Statistik', href: '/pastor/statistik', icon: 'fa-chart-pie' },
                { name: 'Data Sakramen', href: '/pastor/sakramen', icon: 'fa-book-bible' },
                { name: 'Verifikasi Cetak Sakramen', href: '/pastor/sakramen', icon: 'fa-print' },
                { name: 'Pengajuan Sakramen', href: '/pastor/pengajuan-sakramen', icon: 'fa-file-signature' },
                { name: 'Katekumen & Pembinaan', href: '/pastor/pengajuan-sakramen', icon: 'fa-graduation-cap' },
            ],
        },
        {
            section: 'Keuangan & Aset',
            menus: [
                {
                    name: 'Keuangan & Iuran',
                    icon: 'fa-money-bill-transfer',
                    submenus: [
                        { name: 'Iuran Umat', href: '/pastor/iuran', icon: 'fa-receipt' },
                        { name: 'Kolekte', href: '/pastor/kolekte', icon: 'fa-hand-holding-dollar' },
                        { name: 'Intensi', href: '/pastor/intensi-misa', icon: 'fa-heart' },
                        { name: 'Keuangan Paroki', href: '/pastor/keuangan', icon: 'fa-vault' },
                    ],
                },
                { name: 'Aset & Inventaris', href: '/pastor/aset', icon: 'fa-boxes-stacked' },
            ],
        },
        {
            section: 'Warta & Konten',
            menus: [
                { name: 'Berita & Artikel', href: '/pastor/konten', icon: 'fa-newspaper' },
                { name: 'Sambutan Pastor', href: '/pastor/profil-paroki', icon: 'fa-comment-dots' },
                { name: 'Agenda Kegiatan', href: '/pastor/kegiatan', icon: 'fa-calendar-check' },
            ],
        },
        {
            section: 'Akun Saya',
            menus: [
                { name: 'Profil Saya', href: '/pastor/profil-saya', icon: 'fa-circle-user' },
                { name: 'Keluar', action: 'logout', icon: 'fa-arrow-right-from-bracket' },
            ],
        },
    ],
    'Admin Paroki': [
        {
            section: 'Dashboard',
            menus: [
                { name: 'Dashboard', href: '/paroki/dashboard', icon: 'fa-gauge-high' },
                { name: 'Panduan', href: '/paroki/panduan-hak-akses', icon: 'fa-book-open' },
            ],
        },
        {
            section: 'Wilayah & Referensi',
            menus: [
                {
                    name: 'Data Gerejawi',
                    icon: 'fa-church',
                    submenus: [
                        { name: 'Keuskupan', href: '/paroki/keuskupan', icon: 'fa-building-columns' },
                        { name: 'Dekenat', href: '/paroki/dekenat', icon: 'fa-building' },
                        { name: 'Paroki', href: '/paroki/paroki', icon: 'fa-place-of-worship' },
                        { name: 'Kuasi Paroki', href: '/paroki/kuasi-paroki', icon: 'fa-cross' },
                        { name: 'Stasi / Kapela', href: '/paroki/kapela', icon: 'fa-map-location-dot' },
                        { name: 'Wilayah', href: '/paroki/wilayah', icon: 'fa-compass' },
                        { name: 'KUB', href: '/paroki/kub', icon: 'fa-people-group' },
                    ],
                },
                {
                    name: 'Wilayah Sipil',
                    icon: 'fa-map',
                    submenus: [
                        { name: 'Provinsi', href: '/paroki/provinsi', icon: 'fa-map' },
                        { name: 'Kabupaten / Kota', href: '/paroki/kabupaten', icon: 'fa-city' },
                        { name: 'Kecamatan', href: '/paroki/kecamatan', icon: 'fa-map-pin' },
                        { name: 'Desa / Kelurahan', href: '/paroki/desa-kelurahan', icon: 'fa-location-dot' },
                    ],
                },
                { name: 'Data Referensi', href: '/paroki/wilayah', icon: 'fa-tags' },
            ],
        },
        {
            section: 'Umat & Pelayanan',
            menus: [
                {
                    name: 'Pelayanan Paroki',
                    icon: 'fa-hands-holding-child',
                    submenus: [
                        { name: 'Direktori DPP', href: '/paroki/direktori-dpp', icon: 'fa-users-gear' },
                        { name: 'Direktori Katekis', href: '/paroki/direktori-katekis', icon: 'fa-book-open-reader' },
                        { name: 'Direktori Misdinar', href: '/paroki/direktori-misdinar', icon: 'fa-hands-praying' },
                        { name: 'Riwayat Pastor', href: '/paroki/riwayat-pastor', icon: 'fa-user-tie' },
                        { name: 'Kronik Paroki', href: '/paroki/kronik-paroki', icon: 'fa-timeline' },
                        { name: 'Peran & Anggota Kategorial', href: '/paroki/anggota-kategorial', icon: 'fa-user-check' },
                    ],
                },
                {
                    name: 'Jadwal Misa',
                    icon: 'fa-calendar-check',
                    submenus: [
                        { name: 'Daftar Jadwal Misa', href: '/paroki/jadwal-misa', icon: 'fa-calendar-day' },
                        { name: 'Master Pastor', href: '/paroki/master-pastor', icon: 'fa-address-book' },
                        { name: 'Notifikasi WA', href: '/paroki/pengaturan-otp', icon: 'fa-comment-sms' },
                        { name: 'Pengaturan Jadwal', href: '/paroki/pengaturan-aplikasi', icon: 'fa-gear' },
                    ],
                },
                {
                    name: 'Data KK',
                    icon: 'fa-house-chimney-user',
                    submenus: [
                        { name: 'KK Katolik', href: '/paroki/kk-katolik', icon: 'fa-credit-card' },
                        { name: 'Mutasi Massal KUB', href: '/paroki/kk-katolik', icon: 'fa-arrows-split-up-and-left' },
                        { name: 'Data Keluarga', href: '/paroki/kk-katolik', icon: 'fa-users-rectangle' },
                        { name: 'Data Umat / Jiwa', href: '/paroki/umat', icon: 'fa-user' },
                        { name: 'Demografi & Statistik', href: '/paroki/statistik', icon: 'fa-chart-pie' },
                    ],
                },
                {
                    name: 'Data Sakramen',
                    icon: 'fa-book-bible',
                    submenus: [
                        { name: 'Buku Besar Sakramen', href: '/paroki/sakramen', icon: 'fa-book-bookmark' },
                        { name: 'Pemeriksaan Kanonikal', href: '/paroki/sakramen', icon: 'fa-stamp' },
                        { name: 'Verifikasi Cetak', href: '/paroki/sakramen', icon: 'fa-print' },
                        { name: 'Pengajuan Sakramen', href: '/paroki/pengajuan-sakramen', icon: 'fa-file-signature' },
                        { name: 'Katekumen & Pembinaan', href: '/paroki/pengajuan-sakramen', icon: 'fa-graduation-cap' },
                        { name: 'Pengaturan Pembayaran', href: '/paroki/metode-pembayaran', icon: 'fa-qrcode' },
                    ],
                },
                { name: 'Lapak & Toko', href: '/paroki/lapak-produk', icon: 'fa-shop' },
            ],
        },
        {
            section: 'Sekretariat Paroki',
            menus: [
                {
                    name: 'Registrasi Surat',
                    icon: 'fa-envelope-open-text',
                    submenus: [
                        { name: 'Surat Masuk', href: '/paroki/surat-masuk', icon: 'fa-inbox' },
                        { name: 'Surat Keluar', href: '/paroki/surat-keluar', icon: 'fa-paper-plane' },
                    ],
                },
                { name: 'Arsip Digital Paroki', href: '/paroki/arsip-digital', icon: 'fa-folder-tree' },
                { name: 'Rapat & Notulen', href: '/paroki/rapat', icon: 'fa-handshake' },
            ],
        },
        {
            section: 'Keuangan & Aset',
            menus: [
                {
                    name: 'Keuangan & Iuran',
                    icon: 'fa-money-bill-transfer',
                    submenus: [
                        { name: 'Jenis Iuran', href: '/paroki/jenis-iuran', icon: 'fa-tags' },
                        { name: 'Iuran Umat', href: '/paroki/iuran', icon: 'fa-receipt' },
                        { name: 'Kolekte Misa', href: '/paroki/kolekte', icon: 'fa-hand-holding-dollar' },
                        { name: 'Intensi Misa', href: '/paroki/intensi-misa', icon: 'fa-heart' },
                        { name: 'Keuangan Paroki', href: '/paroki/keuangan', icon: 'fa-vault' },
                    ],
                },
                { name: 'Aset & Inventaris', href: '/paroki/aset', icon: 'fa-boxes-stacked' },
            ],
        },
        {
            section: 'Website Paroki',
            menus: [
                {
                    name: 'Konten Website',
                    icon: 'fa-newspaper',
                    submenus: [
                        { name: 'Kategori Konten', href: '/paroki/kategori-konten', icon: 'fa-folder-open' },
                        { name: 'Berita / Artikel', href: '/paroki/konten', icon: 'fa-newspaper' },
                        { name: 'Agenda Kegiatan', href: '/paroki/kegiatan', icon: 'fa-calendar-check' },
                        { name: 'Galeri Foto', href: '/paroki/galeri', icon: 'fa-images' },
                        { name: 'Video Background', href: '/paroki/video-header', icon: 'fa-video' },
                        { name: 'Sambutan Pastor', href: '/paroki/profil-paroki', icon: 'fa-comment-dots' },
                        { name: 'Pusat Unduhan', href: '/paroki/download', icon: 'fa-cloud-arrow-down' },
                    ],
                },
            ],
        },
        {
            section: 'Sistem & Aplikasi',
            menus: [
                { name: 'Profil Paroki', href: '/paroki/profil-paroki', icon: 'fa-church' },
                { name: 'Manajemen User', href: '/paroki/user', icon: 'fa-user-gear' },
                { name: 'Profil Saya', href: '/paroki/profil-saya', icon: 'fa-circle-user' },
                { name: 'Keluar', action: 'logout', icon: 'fa-arrow-right-from-bracket' },
            ],
        },
    ],
    'Bendahara': [
        {
            section: 'Dashboard',
            menus: [
                { name: 'Dashboard', href: '/bendahara/dashboard', icon: 'fa-gauge-high' },
                { name: 'Panduan', href: '/bendahara/panduan-hak-akses', icon: 'fa-book-open' },
            ],
        },
        {
            section: 'Keuangan & Transaksi',
            menus: [
                { name: 'Jenis Iuran', href: '/bendahara/jenis-iuran', icon: 'fa-tags' },
                { name: 'Iuran Umat', href: '/bendahara/iuran', icon: 'fa-receipt' },
                { name: 'Kolekte Misa', href: '/bendahara/kolekte', icon: 'fa-hand-holding-dollar' },
                { name: 'Intensi Misa', href: '/bendahara/intensi-misa', icon: 'fa-heart' },
                { name: 'Keuangan & Kas Paroki', href: '/bendahara/keuangan', icon: 'fa-vault' },
                { name: 'Aset & Inventaris', href: '/bendahara/aset', icon: 'fa-boxes-stacked' },
            ],
        },
        {
            section: 'Referensi Umat',
            menus: [
                { name: 'Data Umat', href: '/bendahara/umat', icon: 'fa-users' },
                { name: 'Data Keluarga (KK)', href: '/bendahara/kk-katolik', icon: 'fa-house-chimney-user' },
            ],
        },
        {
            section: 'Akun Saya',
            menus: [
                { name: 'Profil Saya', href: '/bendahara/profil-saya', icon: 'fa-circle-user' },
                { name: 'Keluar', action: 'logout', icon: 'fa-arrow-right-from-bracket' },
            ],
        },
    ],
    'Admin Wilayah': [
        {
            section: 'Dashboard',
            menus: [
                { name: 'Dashboard', href: '/wilayah/dashboard', icon: 'fa-gauge-high' },
                { name: 'Panduan', href: '/wilayah/panduan-hak-akses', icon: 'fa-book-open' },
            ],
        },
        {
            section: 'Data Wilayah',
            menus: [
                { name: 'Wilayah Saya', href: '/wilayah/wilayah', icon: 'fa-map-pin' },
                { name: 'KUB', href: '/wilayah/kub', icon: 'fa-people-group' },
            ],
        },
        {
            section: 'Data Umat & Keluarga',
            menus: [
                { name: 'KK Katolik', href: '/wilayah/kk-katolik', icon: 'fa-credit-card' },
                { name: 'Data Keluarga', href: '/wilayah/kk-katolik', icon: 'fa-users-rectangle' },
                { name: 'Data Umat / Jiwa', href: '/wilayah/umat', icon: 'fa-user' },
                { name: 'Demografi & Statistik', href: '/wilayah/statistik', icon: 'fa-chart-pie' },
                { name: 'Data Sakramen', href: '/wilayah/sakramen', icon: 'fa-feather' },
            ],
        },
        {
            section: 'Keuangan & Aset',
            menus: [
                {
                    name: 'Keuangan',
                    icon: 'fa-hand-holding-dollar',
                    submenus: [
                        { name: 'Iuran Umat', href: '/wilayah/iuran', icon: 'fa-dollar-sign' },
                        { name: 'Kolekte Misa', href: '/wilayah/kolekte', icon: 'fa-gift' },
                    ],
                },
                { name: 'Aset & Inventaris', href: '/wilayah/aset', icon: 'fa-box-open' },
            ],
        },
        {
            section: 'Akun Saya',
            menus: [
                { name: 'Manajemen User', href: '/wilayah/user', icon: 'fa-user-plus' },
                { name: 'Profil Saya', href: '/wilayah/profil-saya', icon: 'fa-circle-user' },
                { name: 'Keluar', action: 'logout', icon: 'fa-arrow-right-from-bracket' },
            ],
        },
    ],
    'Admin Kapela / Stasi': [
        {
            section: 'Dashboard',
            menus: [
                { name: 'Dashboard', href: '/kapela/dashboard', icon: 'fa-gauge-high' },
                { name: 'Panduan', href: '/kapela/panduan-hak-akses', icon: 'fa-book-open' },
            ],
        },
        {
            section: 'Data Kapela / Stasi',
            menus: [
                { name: 'Stasi / Kapela Saya', href: '/kapela/kapela', icon: 'fa-church' },
                { name: 'KUB', href: '/kapela/kub', icon: 'fa-people-group' },
            ],
        },
        {
            section: 'Data Umat',
            menus: [
                { name: 'KK Katolik', href: '/kapela/kk-katolik', icon: 'fa-credit-card' },
                { name: 'Data Keluarga', href: '/kapela/kk-katolik', icon: 'fa-users-rectangle' },
                { name: 'Data Umat / Jiwa', href: '/kapela/umat', icon: 'fa-user' },
                { name: 'Demografi & Grafik', href: '/kapela/statistik', icon: 'fa-chart-pie' },
                { name: 'Data Sakramen', href: '/kapela/sakramen', icon: 'fa-feather' },
                { name: 'Pengajuan Sakramen', href: '/kapela/pengajuan-sakramen', icon: 'fa-envelope-open-text' },
            ],
        },
        {
            section: 'Aset & Inventaris',
            menus: [
                { name: 'Aset & Inventaris', href: '/kapela/aset', icon: 'fa-box-open' },
            ],
        },
        {
            section: 'Akun Saya',
            menus: [
                { name: 'Profil Saya', href: '/kapela/profil-saya', icon: 'fa-circle-user' },
                { name: 'Keluar', action: 'logout', icon: 'fa-arrow-right-from-bracket' },
            ],
        },
    ],
    'Ketua KUB': [
        {
            section: 'Dashboard',
            menus: [
                { name: 'Dashboard', href: '/kub/dashboard', icon: 'fa-gauge-high' },
                { name: 'Panduan', href: '/kub/panduan-hak-akses', icon: 'fa-book-open' },
            ],
        },
        {
            section: 'Data KUB Saya',
            menus: [
                { name: 'KUB Saya', href: '/kub/kub', icon: 'fa-people-group' },
                { name: 'KK Katolik', href: '/kub/kk-katolik', icon: 'fa-credit-card' },
                { name: 'Data Keluarga', href: '/kub/kk-katolik', icon: 'fa-users-rectangle' },
                { name: 'Data Umat / Jiwa', href: '/kub/umat', icon: 'fa-user' },
                { name: 'Demografi & Statistik', href: '/kub/statistik', icon: 'fa-chart-pie' },
                { name: 'Data Sakramen', href: '/kub/sakramen', icon: 'fa-feather' },
                { name: 'Pengajuan Sakramen', href: '/kub/pengajuan-sakramen', icon: 'fa-envelope-open-text' },
            ],
        },
        {
            section: 'Iuran & Aset KUB',
            menus: [
                { name: 'Iuran KUB', href: '/kub/iuran', icon: 'fa-dollar-sign' },
                { name: 'Lapak & Usaha Umat', href: '/kub/lapak-produk', icon: 'fa-shop' },
                { name: 'Aset & Inventaris', href: '/kub/aset', icon: 'fa-box-open' },
            ],
        },
        {
            section: 'Akun Saya',
            menus: [
                { name: 'Profil Saya', href: '/kub/profil-saya', icon: 'fa-circle-user' },
                { name: 'Keluar', action: 'logout', icon: 'fa-arrow-right-from-bracket' },
            ],
        },
    ],
    'Penulis': [
        {
            section: 'Dashboard',
            menus: [
                { name: 'Dashboard', href: '/penulis/dashboard', icon: 'fa-gauge-high' },
                { name: 'Panduan', href: '/penulis/panduan-hak-akses', icon: 'fa-book-open' },
            ],
        },
        {
            section: 'Media & Publikasi',
            menus: [
                { name: 'Kategori Konten', href: '/penulis/kategori-konten', icon: 'fa-folder-open' },
                { name: 'Berita & Artikel', href: '/penulis/konten', icon: 'fa-newspaper' },
            ],
        },
        {
            section: 'Akun Saya',
            menus: [
                { name: 'Profil Saya', href: '/penulis/profil-saya', icon: 'fa-circle-user' },
                { name: 'Keluar', action: 'logout', icon: 'fa-arrow-right-from-bracket' },
            ],
        },
    ],
    'Umat': [
        {
            section: 'Beranda',
            menus: [
                { name: 'Dashboard', href: '/umat/dashboard', icon: 'fa-gauge-high' },
                { name: 'Panduan', href: '/umat/panduan-hak-akses', icon: 'fa-book-open' },
            ],
        },
        {
            section: 'Data Saya',
            menus: [
                { name: 'Keluarga Saya', href: '/umat/kk-katolik', icon: 'fa-house-chimney-user' },
                { name: 'Pengajuan Sakramen', href: '/umat/pengajuan-sakramen', icon: 'fa-file-signature' },
                { name: 'Lapak & Toko', href: '/umat/lapak-produk', icon: 'fa-shop' },
            ],
        },
        {
            section: 'Informasi Paroki',
            menus: [
                { name: 'Website Paroki', href: '/', icon: 'fa-globe', isExternal: true },
            ],
        },
        {
            section: 'Akun Saya',
            menus: [
                { name: 'Profil Saya', href: '/umat/profil-saya', icon: 'fa-circle-user' },
                { name: 'Keluar', action: 'logout', icon: 'fa-arrow-right-from-bracket' },
            ],
        },
    ],
};

// Compute current active menu tree based on selected role
const currentMenuTree = computed(() => {
    return roleMenus[activeRole.value] || roleMenus['Super Admin'];
});

// Reactive state for open accordion groups — closed by default, auto-opens for active page
const openGroups = ref({
    'Data Gerejawi': false,
    'Wilayah Sipil': false,
    'Data Referensi': false,
    'Pelayanan Paroki': false,
    'Pelayanan Pastoral': false,
    'Data Sakramen': false,
    'Lapak & Toko': false,
    'Sekretariat Paroki': false,
    'Registrasi Surat': false,
    'Keuangan & Aset': false,
    'Iuran & Persembahan': false,
    'Website Paroki': false,
    'Warta & Konten': false,
    'Pengaturan Web': false,
    'Wilayah Paroki': false,
    'Wilayah & KUB': false,
});

const toggleGroup = (groupName) => {
    openGroups.value[groupName] = openGroups.value[groupName] === false ? true : false;
};

// Dynamic Role-based URL Prefix
const rolePrefix = computed(() => {
    const map = {
        'Super Admin': '/superadmin',
        'Admin Paroki': '/paroki',
        'Pastor': '/pastor',
        'Admin Wilayah': '/wilayah',
        'Admin Kapela / Stasi': '/kapela',
        'Ketua KUB': '/kub',
        'Bendahara': '/bendahara',
        'Penulis': '/penulis',
        'Umat': '/umat',
    };
    return map[activeRole.value] || '/superadmin';
});

const getHref = (rawHref) => {
    if (!rawHref) return '#';
    if (rawHref.startsWith('http') || rawHref === '/') return rawHref;
    if (rawHref.startsWith('/admin')) return rawHref;

    const clean = rawHref.replace(/^\/(v2|superadmin|paroki|pastor|wilayah|kapela|kub|bendahara|penulis|umat)/, '');

    if (clean === '/dashboard' || clean === '') {
        return `${rolePrefix.value}/dashboard`;
    }

    return `${rolePrefix.value}${clean.startsWith('/') ? clean : '/' + clean}`;
};

const isItemActive = (rawHref) => {
    if (!rawHref) return false;
    const targetHref = getHref(rawHref);
    const current = page.url.split('?')[0].replace(/\/$/, '') || '/';
    const target = targetHref.split('?')[0].replace(/\/$/, '') || '/';
    const currentQuery = new URLSearchParams(page.url.split('?')[1] || '');
    const targetQuery = new URLSearchParams(targetHref.split('?')[1] || '');

    if (targetQuery.size > 0) {
        if (current !== target) return false;
        return Array.from(targetQuery.entries()).every(([key, value]) => currentQuery.get(key) === value);
    }

    if (target.endsWith('/konten') && currentQuery.has('tipe')) {
        return false;
    }

    if (current === target) return true;

    if (target !== '/' && target !== rolePrefix.value) {
        return current.startsWith(target + '/');
    }

    return false;
};

const isGroupActive = (menu) => {
    if (!menu.submenus) return isItemActive(menu.href);
    return menu.submenus.some((sub) => isItemActive(sub.href));
};

const closeAllGroups = () => {
    Object.keys(openGroups.value).forEach((key) => {
        openGroups.value[key] = false;
    });
};

const isDashboardPage = () => {
    const current = page.url.split('?')[0].replace(/\/$/, '') || '/';
    return current === rolePrefix.value;
};

const scrollToActiveItem = () => {
    if (isDashboardPage()) {
        closeAllGroups();
    }

    currentMenuTree.value.forEach((section) => {
        section.menus.forEach((menu) => {
            if (menu.submenus) {
                if (isGroupActive(menu)) {
                    openGroups.value[menu.name] = true;
                }
            }
        });
    });

    setTimeout(() => {
        const activeLink = document.querySelector('aside a[data-active="true"]');
        if (activeLink) {
            activeLink.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
        }
    }, 150);
};

// Automatically expand and scroll to active menu on load & page transitions
onMounted(() => {
    try {
        const saved = JSON.parse(sessionStorage.getItem(layoutStorageKey) || '{}');
        if (typeof saved.isSidebarOpen === 'boolean') {
            isSidebarOpen.value = saved.isSidebarOpen;
        }
        if (saved.openGroups && typeof saved.openGroups === 'object') {
            openGroups.value = { ...openGroups.value, ...saved.openGroups };
        }
        if (saved.activeRole && roleMenus[saved.activeRole]) {
            activeRole.value = saved.activeRole;
        }
    } catch (error) {
        sessionStorage.removeItem(layoutStorageKey);
    }

    scrollToActiveItem();
});

watch(
    [isSidebarOpen, openGroups, activeRole],
    () => {
        try {
            sessionStorage.setItem(layoutStorageKey, JSON.stringify({
                isSidebarOpen: isSidebarOpen.value,
                openGroups: openGroups.value,
                activeRole: activeRole.value,
            }));
        } catch (error) {
            // Storage can fail in private mode; navigation still works normally.
        }
    },
    { deep: true }
);

watch(
    () => [page.url, activeRole.value],
    () => {
        scrollToActiveItem();
    }
);
</script>

<template>
    <!-- Fixed Full Height Shell -->
    <div class="h-screen w-screen overflow-hidden bg-slate-50 text-slate-800 flex flex-col font-sans selection:bg-amber-500 selection:text-white">
        <!-- 1. FIXED TOPBAR / HEADER -->
        <header class="h-16 w-full shrink-0 border-b border-slate-200/80 bg-white/95 backdrop-blur-md z-30 shadow-xs flex items-center justify-between px-4 sm:px-6">
            <!-- Left: Toggle & Brand -->
            <div class="flex items-center gap-3">
                <button
                    @click="toggleSidebar"
                    class="hidden lg:flex items-center justify-center w-9 h-9 rounded-lg text-slate-500 hover:text-slate-800 hover:bg-slate-100 transition-colors"
                >
                    <i class="fa-solid fa-bars text-sm"></i>
                </button>
                <button
                    @click="isMobileOpen = !isMobileOpen"
                    class="lg:hidden flex items-center justify-center w-9 h-9 rounded-lg text-slate-500 hover:text-slate-800 hover:bg-slate-100 transition-colors"
                >
                    <i class="fa-solid fa-bars text-sm"></i>
                </button>

                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-amber-500 to-amber-400 flex items-center justify-center text-white font-black text-lg shadow-md shadow-amber-500/25 shrink-0 overflow-hidden border border-amber-300/40">
                        <img
                            v-if="page.props.app?.logo"
                            :src="page.props.app.logo"
                            :alt="page.props.app?.nama_paroki || 'Logo'"
                            class="w-full h-full object-cover"
                        />
                        <i v-else class="fa-solid fa-church text-sm"></i>
                    </div>
                    <div>
                        <h1 class="font-bold text-sm sm:text-base tracking-tight text-slate-900 leading-none">
                            SIPAROKI
                        </h1>
                        <p class="text-[11px] text-slate-500 hidden sm:block mt-0.5 truncate max-w-[280px]">
                            {{ page.props.app?.nama_paroki || title }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Right: Role Switcher & Action Buttons -->
            <div class="flex items-center gap-2.5">
                <!-- Role Preview Selector (ONLY for Super Admin) -->
                <div v-if="isSuperAdmin" class="hidden md:flex items-center gap-1.5 bg-slate-100/90 p-1 rounded-xl border border-slate-200 text-xs">
                    <span class="text-[10px] font-bold uppercase text-slate-500 pl-1.5 pr-0.5">Peran:</span>
                    <select
                        v-model="activeRole"
                        @change="onRoleChange"
                        class="bg-white border border-slate-200 rounded-lg px-2 py-1 text-xs font-bold text-amber-800 focus:outline-none focus:border-amber-500 cursor-pointer shadow-2xs"
                    >
                        <option v-for="r in Object.keys(roleMenus)" :key="r" :value="r">
                            {{ r }}
                        </option>
                    </select>

                    <!-- Dynamic Pastor Selector -->
                    <template v-if="activeRole === 'Pastor' && pastorsList.length > 0">
                        <span class="text-slate-300">|</span>
                        <select
                            v-model="selectedPastorId"
                            class="bg-white border border-amber-300 rounded-lg px-2 py-1 text-xs font-bold text-slate-800 focus:outline-none focus:border-amber-500 cursor-pointer shadow-2xs max-w-[160px] truncate"
                        >
                            <option value="">-- Pilih Pastor --</option>
                            <option v-for="p in pastorsList" :key="p.id" :value="p.id">
                                {{ p.nama_pastor }} ({{ p.jabatan || 'Pastor' }})
                            </option>
                        </select>
                    </template>

                    <!-- Dynamic Wilayah Selector -->
                    <template v-if="activeRole === 'Admin Wilayah' && wilayahList.length > 0">
                        <span class="text-slate-300">|</span>
                        <select
                            v-model="selectedWilayahId"
                            class="bg-white border border-amber-300 rounded-lg px-2 py-1 text-xs font-bold text-slate-800 focus:outline-none focus:border-amber-500 cursor-pointer shadow-2xs max-w-[160px] truncate"
                        >
                            <option value="">-- Pilih Wilayah --</option>
                            <option v-for="w in wilayahList" :key="w.id" :value="w.id">
                                {{ w.nama_wilayah }}
                            </option>
                        </select>
                    </template>

                    <!-- Dynamic Kapela / Stasi Selector -->
                    <template v-if="activeRole === 'Admin Kapela / Stasi' && kapelaList.length > 0">
                        <span class="text-slate-300">|</span>
                        <select
                            v-model="selectedKapelaId"
                            class="bg-white border border-amber-300 rounded-lg px-2 py-1 text-xs font-bold text-slate-800 focus:outline-none focus:border-amber-500 cursor-pointer shadow-2xs max-w-[160px] truncate"
                        >
                            <option value="">-- Pilih Stasi / Kapela --</option>
                            <option v-for="k in kapelaList" :key="k.id" :value="k.id">
                                {{ k.nama_kapela }}
                            </option>
                        </select>
                    </template>

                    <!-- Dynamic KUB Selector -->
                    <template v-if="activeRole === 'Ketua KUB' && kubList.length > 0">
                        <span class="text-slate-300">|</span>
                        <select
                            v-model="selectedKubId"
                            class="bg-white border border-amber-300 rounded-lg px-2 py-1 text-xs font-bold text-slate-800 focus:outline-none focus:border-amber-500 cursor-pointer shadow-2xs max-w-[160px] truncate"
                        >
                            <option value="">-- Pilih KUB --</option>
                            <option v-for="kb in kubList" :key="kb.id" :value="kb.id">
                                {{ kb.nama_kub }}
                            </option>
                        </select>
                    </template>
                </div>

                <!-- Static Role Badge for Non-Super Admin (Admin Paroki, Pastor, Wilayah, etc.) -->
                <div v-else class="hidden md:flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-amber-50 border border-amber-200/80 text-xs font-bold text-amber-900 shadow-2xs">
                    <i class="fa-solid fa-user-shield text-amber-600 text-[11px]"></i>
                    <span>{{ userActualRole || activeRole }}</span>
                </div>

                <Link
                    :href="getHref('/panduan-hak-akses')"
                    class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-amber-50 border border-amber-200 text-amber-800 text-xs font-bold hover:bg-amber-100 transition"
                >
                    <i class="fa-solid fa-shield-halved text-amber-600"></i>
                    <span class="hidden xl:inline">Panduan RBAC</span>
                </Link>

                <a
                    href="/"
                    class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-lg bg-amber-500 hover:bg-amber-600 text-white font-semibold text-xs shadow-sm shadow-amber-500/20 transition-all"
                >
                    <i class="fa-solid fa-house"></i>
                    <span class="hidden sm:inline">Situs Paroki</span>
                </a>

                <Link
                    :href="getHref('/profil-saya')"
                    :class="[
                        'inline-flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-xs font-semibold transition cursor-pointer',
                        isItemActive('/v2/profil-saya')
                            ? 'bg-amber-100 text-amber-900 border border-amber-300'
                            : 'bg-slate-100 hover:bg-amber-50 hover:text-amber-800 text-slate-700'
                    ]"
                    title="Profil Saya"
                >
                    <img
                        v-if="userAvatar"
                        :src="userAvatar"
                        alt="Foto Profil"
                        class="w-5 h-5 rounded-full object-cover border border-amber-500/50 shadow-2xs shrink-0"
                    />
                    <i v-else class="fa-solid fa-circle-user text-amber-600 text-sm"></i>
                    <span class="hidden sm:inline font-bold truncate max-w-[120px]">{{ userName }}</span>
                </Link>

                <button
                    type="button"
                    @click="showLogoutModal = true"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-rose-50 hover:text-rose-700 text-slate-700 font-semibold text-xs transition cursor-pointer"
                >
                    <i class="fa-solid fa-arrow-right-from-bracket text-slate-500"></i>
                    <span class="hidden sm:inline">Keluar</span>
                </button>
            </div>
        </header>

        <!-- FLOATING TOAST NOTIFICATION -->
        <Transition
            enter-active-class="transform ease-out duration-300 transition"
            enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
            enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
            leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="showToast"
                class="fixed top-20 right-4 sm:right-6 z-50 max-w-sm w-full shadow-lg rounded-2xl p-4 flex items-start gap-3 border backdrop-blur-md"
                :class="[
                    toastType === 'success'
                        ? 'bg-emerald-50/95 border-emerald-200 text-emerald-900 shadow-emerald-500/10'
                        : 'bg-rose-50/95 border-rose-200 text-rose-900 shadow-rose-500/10'
                ]"
            >
                <div
                    class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 text-sm font-bold"
                    :class="[
                        toastType === 'success'
                            ? 'bg-emerald-500 text-white shadow-xs'
                            : 'bg-rose-500 text-white shadow-xs'
                    ]"
                >
                    <i :class="toastType === 'success' ? 'fa-solid fa-check' : 'fa-solid fa-triangle-exclamation'"></i>
                </div>
                <div class="flex-1 min-w-0 pt-0.5">
                    <h4 class="text-xs font-bold capitalize">
                        {{ toastType === 'success' ? 'Pemberitahuan Sistem' : 'Peringatan' }}
                    </h4>
                    <p class="text-xs mt-0.5 leading-relaxed opacity-90">
                        {{ toastMessage }}
                    </p>
                </div>
                <button
                    @click="showToast = false"
                    class="text-slate-400 hover:text-slate-700 p-1 text-xs cursor-pointer"
                >
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        </Transition>

        <!-- LOGOUT CONFIRMATION MODAL -->
        <Transition
            enter-active-class="ease-out duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="ease-in duration-150"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="showLogoutModal"
                class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/40 backdrop-blur-xs"
            >
                <div
                    @click.stop
                    class="bg-white rounded-3xl p-6 sm:p-7 max-w-md w-full shadow-2xl border border-slate-200/80 text-center space-y-5 animate-in fade-in zoom-in-95 duration-200"
                >
                    <div class="w-14 h-14 rounded-2xl bg-amber-50 text-amber-600 border border-amber-200 mx-auto flex items-center justify-center text-2xl shadow-sm">
                        <i class="fa-solid fa-door-open"></i>
                    </div>

                    <div class="space-y-1.5">
                        <h3 class="text-lg font-black text-slate-900 tracking-tight">Konfirmasi Keluar Aplikasi</h3>
                        <p class="text-xs text-slate-500 leading-relaxed max-w-sm mx-auto">
                            Apakah Anda yakin ingin mengakhiri sesi Anda saat ini di sistem SIPAROKI?
                        </p>
                    </div>

                    <div class="flex items-center justify-center gap-3 pt-2">
                        <button
                            type="button"
                            @click="showLogoutModal = false"
                            class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition cursor-pointer"
                        >
                            Batal
                        </button>
                        <button
                            type="button"
                            @click="confirmLogout"
                            class="px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold shadow-sm shadow-rose-600/30 transition flex items-center gap-2 cursor-pointer"
                        >
                            <i class="fa-solid fa-arrow-right-from-bracket text-xs"></i>
                            <span>Ya, Keluar Sekarang</span>
                        </button>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- MOBILE SIDEBAR DRAWER (OFF-CANVAS) -->
        <Transition
            enter-active-class="transition-opacity ease-linear duration-300"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity ease-linear duration-300"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="isMobileOpen"
                class="fixed inset-0 z-40 bg-slate-950/50 backdrop-blur-xs lg:hidden"
                @click="isMobileOpen = false"
            ></div>
        </Transition>

        <Transition
            enter-active-class="transition ease-in-out duration-300 transform"
            enter-from-class="-translate-x-full"
            enter-to-class="translate-x-0"
            leave-active-class="transition ease-in-out duration-300 transform"
            leave-from-class="translate-x-0"
            leave-to-class="-translate-x-full"
        >
            <aside
                v-if="isMobileOpen"
                class="fixed inset-y-0 left-0 z-50 w-72 bg-white shadow-2xl flex flex-col lg:hidden border-r border-slate-200"
            >
                <!-- Mobile Drawer Header -->
                <div class="h-16 px-4 border-b border-slate-200 flex items-center justify-between bg-slate-50/80">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-amber-500 to-amber-400 flex items-center justify-center text-white font-black text-lg shadow-md shadow-amber-500/25 shrink-0">
                            <i class="fa-solid fa-church text-sm"></i>
                        </div>
                        <div>
                            <h1 class="font-bold text-sm tracking-tight text-slate-900 leading-none">
                                SIPAROKI
                            </h1>
                            <p class="text-[11px] text-slate-500 mt-0.5">{{ activeRole }}</p>
                        </div>
                    </div>
                    <button
                        @click="isMobileOpen = false"
                        class="w-8 h-8 rounded-lg text-slate-400 hover:text-slate-700 flex items-center justify-center hover:bg-slate-100 transition cursor-pointer"
                    >
                        <i class="fa-solid fa-xmark text-sm"></i>
                    </button>
                </div>

                <!-- Mobile Role Switcher -->
                <div class="p-3 bg-slate-50 border-b border-slate-100">
                    <label class="text-[10px] font-bold uppercase text-slate-400 block mb-1">Ganti Peran Pengguna:</label>
                    <select
                        v-model="activeRole"
                        class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs font-bold text-amber-800 focus:outline-none focus:border-amber-500 cursor-pointer shadow-2xs"
                    >
                        <option v-for="r in Object.keys(roleMenus)" :key="r" :value="r">
                            {{ r }}
                        </option>
                    </select>
                </div>

                <!-- Nav Scrollable Menu Area -->
                <div class="flex-1 py-3 px-3 space-y-4 overflow-y-auto custom-scrollbar">
                    <div v-for="section in currentMenuTree" :key="section.section" class="space-y-1">
                        <div class="px-3 pt-2 pb-1 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                            {{ section.section }}
                        </div>

                        <div v-for="item in section.menus" :key="item.name">
                            <!-- Logout Action Item -->
                            <button
                                v-if="item.action === 'logout'"
                                type="button"
                                @click="showLogoutModal = true; isMobileOpen = false;"
                                class="w-full flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold text-rose-600 hover:bg-rose-50 transition-all duration-150 group text-left cursor-pointer"
                            >
                                <i class="fa-solid fa-arrow-right-from-bracket text-center w-4 text-sm text-rose-500 group-hover:scale-110 transition-transform"></i>
                                <span class="truncate">{{ item.name }}</span>
                            </button>

                            <!-- Single Menu Item (No Submenus) -->
                            <Link
                                v-else-if="!item.submenus"
                                :href="getHref(item.href)"
                                :data-active="isItemActive(item.href)"
                                @click="isMobileOpen = false"
                                :class="[
                                    'flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium transition-all duration-150 group',
                                    isItemActive(item.href)
                                        ? 'bg-amber-500 text-white font-bold shadow-sm shadow-amber-500/30'
                                        : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80',
                                ]"
                            >
                                <i :class="[
                                    'fa-solid text-center w-4 text-sm transition-transform group-hover:scale-110',
                                    isItemActive(item.href) ? 'text-white' : 'text-slate-400 group-hover:text-slate-600',
                                    item.icon
                                ]"></i>
                                <span class="truncate">{{ item.name }}</span>
                            </Link>

                            <!-- Menu Item with Submenus -->
                            <div v-else class="space-y-0.5">
                                <button
                                    type="button"
                                    @click="toggleGroup(item.name)"
                                    :class="[
                                        'w-full flex items-center justify-between px-3 py-2 rounded-xl text-xs font-medium transition-all duration-150 text-left group',
                                        isGroupActive(item)
                                            ? 'text-amber-900 font-bold bg-amber-50/60'
                                            : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80',
                                    ]"
                                >
                                    <div class="flex items-center gap-3 min-w-0">
                                        <i :class="[
                                            'fa-solid text-center w-4 text-sm transition-transform group-hover:scale-110',
                                            isGroupActive(item) ? 'text-amber-600' : 'text-slate-400 group-hover:text-slate-600',
                                            item.icon
                                        ]"></i>
                                        <span class="truncate">{{ item.name }}</span>
                                    </div>
                                    <i
                                        :class="[
                                            'fa-solid fa-chevron-right text-[10px] text-slate-400 transition-transform duration-200',
                                            openGroups[item.name] ? 'rotate-90 text-amber-600' : '',
                                        ]"
                                    ></i>
                                </button>

                                <div
                                    v-show="openGroups[item.name]"
                                    class="pl-7 pr-1 space-y-0.5 border-l-2 border-slate-100 ml-5 my-1"
                                >
                                    <Link
                                        v-for="sub in item.submenus"
                                        :key="sub.name"
                                        :href="getHref(sub.href)"
                                        :data-active="isItemActive(sub.href)"
                                        @click="isMobileOpen = false"
                                        :class="[
                                            'flex items-center gap-2.5 px-2.5 py-1.5 rounded-lg text-xs font-medium transition-all duration-150 group',
                                            isItemActive(sub.href)
                                                ? 'bg-amber-50 text-amber-800 font-bold border border-amber-200'
                                                : 'text-slate-500 hover:text-slate-900 hover:bg-slate-100/70',
                                        ]"
                                    >
                                        <i :class="[
                                            'fa-solid text-[11px] w-3.5 text-center',
                                            isItemActive(sub.href) ? 'text-amber-600' : 'text-slate-400 group-hover:text-slate-600',
                                            sub.icon
                                        ]"></i>
                                        <span class="truncate">{{ sub.name }}</span>
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Mobile Drawer Footer -->
                <div class="p-3 border-t border-slate-200/80 bg-slate-50/50 space-y-2">
                    <a
                        href="/"
                        class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition-colors"
                    >
                        <i class="fa-solid fa-house text-center w-4 text-amber-600"></i>
                        <span>Situs Paroki Publik</span>
                    </a>
                    <button
                        type="button"
                        @click="showLogoutModal = true; isMobileOpen = false;"
                        class="w-full flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium text-rose-600 hover:bg-rose-50 transition-colors cursor-pointer"
                    >
                        <i class="fa-solid fa-arrow-right-from-bracket text-center w-4"></i>
                        <span>Keluar Aplikasi</span>
                    </button>
                </div>
            </aside>
        </Transition>

        <!-- Body Area: Fixed Sidebar + Scrollable Content -->
        <div class="flex-1 flex overflow-hidden">
            <!-- 2. FIXED SIDEBAR DESKTOP -->
            <aside
                :class="[
                    'hidden lg:flex flex-col border-r border-slate-200 bg-white shrink-0 select-none shadow-xs transition-all duration-300 ease-in-out h-full overflow-hidden',
                    isSidebarOpen ? 'w-64' : 'w-20',
                ]"
            >
                <!-- Nav Scrollable Menu Area -->
                <div class="flex-1 py-3 px-3 space-y-4 overflow-y-auto custom-scrollbar">
                    <div v-for="section in currentMenuTree" :key="section.section" class="space-y-1">
                        <!-- Section Heading -->
                        <div
                            v-show="isSidebarOpen"
                            class="px-3 pt-2 pb-1 text-[10px] font-bold uppercase tracking-wider text-slate-400"
                        >
                            {{ section.section }}
                        </div>

                        <!-- Menu Items in Section -->
                        <div v-for="item in section.menus" :key="item.name" class="space-y-0.5">
                            <!-- Logout Action Item -->
                            <button
                                v-if="item.action === 'logout'"
                                type="button"
                                @click="showLogoutModal = true"
                                class="w-full flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold text-rose-600 hover:bg-rose-50 hover:text-rose-700 transition-all duration-150 group text-left cursor-pointer"
                                title="Keluar / Logout"
                            >
                                <i class="fa-solid fa-arrow-right-from-bracket text-center w-4 text-sm text-rose-500 group-hover:scale-110 transition-transform"></i>
                                <span v-show="isSidebarOpen" class="truncate">{{ item.name }}</span>
                            </button>

                            <!-- Single Link (No Submenu) -->
                            <Link
                                v-else-if="!item.submenus"
                                :href="getHref(item.href)"
                                :data-active="isItemActive(item.href)"
                                :class="[
                                    'flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium transition-all duration-150 group',
                                    isItemActive(item.href)
                                        ? 'bg-amber-50 text-amber-800 border border-amber-300/80 shadow-xs font-bold'
                                        : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80',
                                ]"
                            >
                                <i :class="[
                                    'fa-solid text-center w-4 text-sm transition-transform group-hover:scale-110',
                                    isItemActive(item.href) ? 'text-amber-600' : 'text-slate-400 group-hover:text-slate-600',
                                    item.icon
                                ]"></i>
                                <span v-show="isSidebarOpen" class="truncate">{{ item.name }}</span>
                            </Link>

                            <!-- Collapsible Parent with Submenus -->
                            <div v-else class="space-y-0.5">
                                <button
                                    @click="toggleGroup(item.name)"
                                    :class="[
                                        'w-full flex items-center justify-between px-3 py-2 rounded-xl text-xs font-medium transition-all duration-150 text-left group',
                                        isGroupActive(item)
                                            ? 'text-amber-900 font-bold bg-amber-50/60'
                                            : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80',
                                    ]"
                                >
                                    <div class="flex items-center gap-3 min-w-0">
                                        <i :class="[
                                            'fa-solid text-center w-4 text-sm transition-transform group-hover:scale-110',
                                            isGroupActive(item) ? 'text-amber-600' : 'text-slate-400 group-hover:text-slate-600',
                                            item.icon
                                        ]"></i>
                                        <span v-show="isSidebarOpen" class="truncate">{{ item.name }}</span>
                                    </div>
                                    <i
                                        v-show="isSidebarOpen"
                                        :class="[
                                            'fa-solid fa-chevron-right text-[10px] text-slate-400 transition-transform duration-200',
                                            openGroups[item.name] ? 'rotate-90 text-amber-600' : '',
                                        ]"
                                    ></i>
                                </button>

                                <!-- Submenu Items -->
                                <div
                                    v-show="isSidebarOpen && openGroups[item.name]"
                                    class="pl-7 pr-1 space-y-0.5 border-l-2 border-slate-100 ml-5 my-1"
                                >
                                    <Link
                                        v-for="sub in item.submenus"
                                        :key="sub.name"
                                        :href="getHref(sub.href)"
                                        :data-active="isItemActive(sub.href)"
                                        :class="[
                                            'flex items-center gap-2.5 px-2.5 py-1.5 rounded-lg text-xs font-medium transition-all duration-150 group',
                                            isItemActive(sub.href)
                                                ? 'bg-amber-50 text-amber-800 font-bold border border-amber-200'
                                                : 'text-slate-500 hover:text-slate-900 hover:bg-slate-100/70',
                                        ]"
                                    >
                                        <i :class="[
                                            'fa-solid text-[11px] w-3.5 text-center',
                                            isItemActive(sub.href) ? 'text-amber-600' : 'text-slate-400 group-hover:text-slate-600',
                                            sub.icon
                                        ]"></i>
                                        <span class="truncate">{{ sub.name }}</span>
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Fixed Sidebar Footer -->
                <div class="p-3 border-t border-slate-200/80 shrink-0 bg-slate-50/50 space-y-1">
                    <a
                        href="/"
                        class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition-colors"
                    >
                        <i class="fa-solid fa-globe text-center w-4 text-slate-400"></i>
                        <span v-show="isSidebarOpen">Lihat Website Publik</span>
                    </a>
                    <button
                        type="button"
                        @click="showLogoutModal = true"
                        class="w-full flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium text-rose-600 hover:bg-rose-50 hover:text-rose-700 transition-colors cursor-pointer"
                        title="Keluar Aplikasi"
                    >
                        <i class="fa-solid fa-arrow-right-from-bracket text-center w-4"></i>
                        <span v-show="isSidebarOpen">Keluar Aplikasi</span>
                    </button>
                </div>
            </aside>

            <!-- 3. FULL HEIGHT MAIN CONTENT AREA -->
            <div class="flex-1 flex flex-col min-w-0 overflow-hidden h-full">
                <!-- Page Full Body -->
                <main class="flex-1 overflow-y-auto flex flex-col min-h-0 custom-scrollbar">
                    <div :class="['flex-1 flex flex-col min-h-0', fullWidth ? 'p-2 sm:p-3' : 'px-3 sm:px-4 lg:px-5 py-3 sm:py-4']">
                        <slot />
                    </div>
                </main>

                <!-- 4. FIXED BACKEND FOOTER -->
                <footer class="h-10 w-full shrink-0 border-t border-slate-200/80 bg-white px-4 sm:px-6 flex items-center justify-between text-xs text-slate-500 z-20">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        <span>{{ page.props.app?.name || 'SIPAROKI' }} &copy; {{ new Date().getFullYear() }} {{ page.props.app?.nama_paroki || 'Paroki St. Vincentius a Paulo' }}</span>
                    </div>
                    <div class="hidden sm:flex items-center gap-4 text-[11px]">
                        <span class="font-medium text-slate-600">Peran Aktif: <b class="text-amber-800">{{ activeRole }}</b></span>
                    </div>
                </footer>
            </div>
        </div>
    </div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 5px;
    height: 5px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(148, 163, 184, 0.4);
    border-radius: 9999px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: rgba(148, 163, 184, 0.7);
}
</style>
