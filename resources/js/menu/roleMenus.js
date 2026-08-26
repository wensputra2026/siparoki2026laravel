// Menu navigasi berbasis peran untuk panel backend SIPAROKI.
// Dipisah dari AppLayout.vue agar data menu mudah dirawat saat jumlah
// peran / fitur KUB, Wilayah, dan Kapela bertambah.

export const roleMenus = {
    'Super Admin': [
        {
            section: 'Dashboard',
            menus: [
                { name: 'Dashboard', href: '/superadmin/dashboard', icon: 'fa-gauge-high' },
                { name: 'Panduan', href: '/superadmin/panduan-hak-akses', icon: 'fa-book-open' },
            ],
        },
        {
            section: 'Wilayah & Referensi',
            menus: [
                {
                    name: 'Data Gerejawi',
                    icon: 'fa-church',
                    submenus: [
                        { name: 'Keuskupan', href: '/superadmin/keuskupan', icon: 'fa-building-columns' },
                        { name: 'Dekenat', href: '/superadmin/dekenat', icon: 'fa-building' },
                        { name: 'Paroki', href: '/superadmin/paroki', icon: 'fa-place-of-worship' },
                        { name: 'Kuasi Paroki', href: '/superadmin/kuasi-paroki', icon: 'fa-cross' },
                        { name: 'Stasi / Kapela', href: '/superadmin/kapela', icon: 'fa-map-location-dot' },
                        { name: 'Wilayah', href: '/superadmin/wilayah', icon: 'fa-compass' },
                        { name: 'Komunitas Umat Basis (KUB)', href: '/superadmin/kub', icon: 'fa-people-group' },
                    ],
                },
                {
                    name: 'Wilayah Sipil',
                    icon: 'fa-map',
                    submenus: [
                        { name: 'Provinsi', href: '/superadmin/provinsi', icon: 'fa-map' },
                        { name: 'Kabupaten / Kota', href: '/superadmin/kabupaten', icon: 'fa-city' },
                        { name: 'Kecamatan', href: '/superadmin/kecamatan', icon: 'fa-map-pin' },
                        { name: 'Desa / Kelurahan', href: '/superadmin/desa-kelurahan', icon: 'fa-location-dot' },
                    ],
                },
                {
                    name: 'Master Referensi',
                    icon: 'fa-tags',
                    href: '/admin/master-referensi',
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
                        { name: 'Direktori DPP', href: '/superadmin/direktori-dpp', icon: 'fa-users-gear' },
                        { name: 'Direktori Katekis', href: '/superadmin/direktori-katekis', icon: 'fa-book-open-reader' },
                        { name: 'Direktori Misdinar', href: '/superadmin/direktori-misdinar', icon: 'fa-hands-praying' },
                        { name: 'Riwayat Pastor', href: '/superadmin/riwayat-pastor', icon: 'fa-user-tie' },
                        { name: 'Kronik Paroki', href: '/superadmin/kronik-paroki', icon: 'fa-timeline' },
                        { name: 'Peran Kategorial', href: '/superadmin/peran-kategorial', icon: 'fa-layer-group' },
                        { name: 'Anggota Kategorial', href: '/superadmin/anggota-kategorial', icon: 'fa-user-check' },
                    ],
                },
                {
                    name: 'Jadwal Misa',
                    icon: 'fa-calendar-check',
                    submenus: [
                        { name: 'Daftar Jadwal Misa', href: '/superadmin/jadwal-misa', icon: 'fa-calendar-day' },
                        { name: 'Petugas Liturgi', href: '/superadmin/jadwal-petugas-liturgi', icon: 'fa-hands-praying' },
                        { name: 'Master Pastor & Kontak', href: '/superadmin/master-pastor', icon: 'fa-address-book' },
                    ],
                },
                {
                    name: 'Data KK & Umat',
                    icon: 'fa-house-chimney-user',
                    submenus: [
                        { name: 'KK Katolik', href: '/superadmin/kk-katolik', icon: 'fa-house-chimney-user' },
                        { name: 'Data Umat / Jiwa', href: '/superadmin/umat', icon: 'fa-user' },
                        { name: 'Data Umat Meninggal', href: '/superadmin/defunctorum', icon: 'fa-book-skull' },
                        { name: 'Demografi & Statistik', href: '/superadmin/statistik', icon: 'fa-chart-pie' },
                    ],
                },
                {
                    name: 'Data Sakramen',
                    icon: 'fa-book-bible',
                    submenus: [
                        { name: 'Buku Sakramen', href: '/superadmin/sakramen', icon: 'fa-book-bookmark' },
                        { name: 'Pengajuan Sakramen', href: '/superadmin/pengajuan-sakramen', icon: 'fa-file-signature' },
                    ],
                },
                {
                    name: 'Lapak & Toko',
                    icon: 'fa-shop',
                    href: '/superadmin/lapak-produk',
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
                        { name: 'Surat Masuk', href: '/superadmin/surat-masuk', icon: 'fa-inbox' },
                        { name: 'Surat Keluar', href: '/superadmin/surat-keluar', icon: 'fa-paper-plane' },
                    ],
                },
                {
                    name: 'Arsip Digital Paroki',
                    icon: 'fa-folder-tree',
                    href: '/superadmin/arsip-digital',
                },
                {
                    name: 'Rapat & Notulen',
                    icon: 'fa-handshake',
                    href: '/superadmin/rapat',
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
                        { name: 'Jenis Iuran', href: '/superadmin/jenis-iuran', icon: 'fa-tags' },
                        { name: 'Iuran Umat', href: '/superadmin/iuran', icon: 'fa-receipt' },
                        { name: 'Kolekte Misa', href: '/superadmin/kolekte', icon: 'fa-hand-holding-dollar' },
                        { name: 'Intensi Misa', href: '/superadmin/intensi-misa', icon: 'fa-heart' },
                        { name: 'Keuangan Paroki', href: '/superadmin/keuangan', icon: 'fa-vault' },
                    ],
                },
                {
                    name: 'Aset & Inventaris',
                    icon: 'fa-boxes-stacked',
                    href: '/superadmin/aset',
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
                        { name: 'Kategori Konten', href: '/superadmin/kategori-konten', icon: 'fa-folder-open' },
                        { name: 'Berita / Artikel', href: '/superadmin/konten', icon: 'fa-newspaper' },
                        { name: 'Agenda Kegiatan', href: '/superadmin/kegiatan', icon: 'fa-calendar-check' },
                        { name: 'Galeri Foto', href: '/superadmin/galeri', icon: 'fa-images' },
                        { name: 'Pengumuman Paroki', href: '/superadmin/pengumuman', icon: 'fa-bullhorn' },
                        { name: 'Sambutan Pastor', href: '/superadmin/sambutan-pastor', icon: 'fa-quote-right' },
                        { name: 'Pusat Unduhan', href: '/superadmin/download', icon: 'fa-cloud-arrow-down' },
                    ],
                },
                {
                    name: 'Pengaturan Web',
                    icon: 'fa-globe',
                    submenus: [
                        { name: 'Pengaturan Aplikasi', href: '/superadmin/pengaturan-aplikasi', icon: 'fa-sliders' },
                        { name: 'Widget Web', href: '/superadmin/widget', icon: 'fa-table-cells-large' },
                        { name: 'Menu Website', href: '/superadmin/menu', icon: 'fa-bars' },
                        { name: 'Banner Slider', href: '/superadmin/slider', icon: 'fa-panorama' },
                        { name: 'Video Background', href: '/superadmin/video-header', icon: 'fa-video' },
                        { name: 'Mode Maintenance', href: '/superadmin/maintenance', icon: 'fa-screwdriver-wrench' },
                        { name: 'Notifikasi OTP WA', href: '/superadmin/pengaturan-otp', icon: 'fa-comments' },
                        { name: 'Pembayaran & QRIS', href: '/superadmin/metode-pembayaran', icon: 'fa-qrcode' },
                    ],
                },
            ],
        },
        {
            section: 'Sistem & Aplikasi',
            menus: [
                { name: 'Profil Paroki', href: '/superadmin/profil-paroki', icon: 'fa-church' },
                { name: 'Manajemen User', href: '/superadmin/user', icon: 'fa-user-gear' },
                { name: 'Role & Permission', href: '/superadmin/role', icon: 'fa-lock' },
                { name: 'Security Center', href: '/superadmin/security-settings', icon: 'fa-shield-halved' },
                { name: 'Backup & Restore', href: '/superadmin/backup-database', icon: 'fa-database' },
                { name: 'Pembersih Sistem', href: '/superadmin/pembersih-sistem', icon: 'fa-broom' },
                { name: 'Profil Saya', href: '/superadmin/profil-saya', icon: 'fa-circle-user' },
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
                        { name: 'Petugas Liturgi', href: '/pastor/jadwal-petugas-liturgi', icon: 'fa-hands-praying' },
                        { name: 'Peran & Anggota Kategorial', href: '/pastor/anggota-kategorial', icon: 'fa-user-check' },
                    ],
                },
                { name: 'KK Katolik', href: '/pastor/kk-katolik', icon: 'fa-house-chimney-user' },
                { name: 'Data Umat / Jiwa', href: '/pastor/umat', icon: 'fa-user' },
                { name: 'Data Umat Meninggal', href: '/pastor/defunctorum', icon: 'fa-book-skull' },
                { name: 'Demografi & Statistik', href: '/pastor/statistik', icon: 'fa-chart-pie' },
                { name: 'Data Sakramen', href: '/pastor/sakramen', icon: 'fa-book-bible' },
                { name: 'Pengajuan Sakramen', href: '/pastor/pengajuan-sakramen', icon: 'fa-file-signature' },
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
                { name: 'Pengumuman Paroki', href: '/pastor/pengumuman', icon: 'fa-bullhorn' },
                { name: 'Sambutan Pastor', href: '/pastor/sambutan-pastor', icon: 'fa-quote-right' },
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
                        { name: 'Petugas Liturgi', href: '/paroki/jadwal-petugas-liturgi', icon: 'fa-hands-praying' },
                        { name: 'Master Pastor', href: '/paroki/master-pastor', icon: 'fa-address-book' },
                        { name: 'Notifikasi WA', href: '/paroki/pengaturan-otp', icon: 'fa-comment-sms' },
                        { name: 'Pengaturan Jadwal', href: '/paroki/pengaturan-aplikasi', icon: 'fa-gear' },
                    ],
                },
                {
                    name: 'Data KK & Umat',
                    icon: 'fa-house-chimney-user',
                    submenus: [
                        { name: 'KK Katolik', href: '/paroki/kk-katolik', icon: 'fa-house-chimney-user' },
                        { name: 'Data Umat / Jiwa', href: '/paroki/umat', icon: 'fa-user' },
                        { name: 'Data Umat Meninggal', href: '/paroki/defunctorum', icon: 'fa-book-skull' },
                        { name: 'Demografi & Statistik', href: '/paroki/statistik', icon: 'fa-chart-pie' },
                    ],
                },
                {
                    name: 'Data Sakramen',
                    icon: 'fa-book-bible',
                    submenus: [
                        { name: 'Buku Besar Sakramen', href: '/paroki/sakramen', icon: 'fa-book-bookmark' },
                        { name: 'Pengajuan Sakramen', href: '/paroki/pengajuan-sakramen', icon: 'fa-file-signature' },
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
                        { name: 'Pengumuman Paroki', href: '/paroki/pengumuman', icon: 'fa-bullhorn' },
                        { name: 'Sambutan Pastor', href: '/paroki/sambutan-pastor', icon: 'fa-quote-right' },
                        { name: 'Video Background', href: '/paroki/video-header', icon: 'fa-video' },
                        { name: 'Pusat Unduhan', href: '/paroki/download', icon: 'fa-cloud-arrow-down' },
                    ],
                },
            ],
        },
        {
            section: 'Sistem & Aplikasi',
            menus: [
                { name: 'Manajemen User', href: '/paroki/user', icon: 'fa-user-gear' },
                { name: 'Pembersih Sistem', href: '/paroki/pembersih-sistem', icon: 'fa-broom' },
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
                { name: 'KK Katolik', href: '/bendahara/kk-katolik', icon: 'fa-house-chimney-user' },
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
                { name: 'KK Katolik', href: '/wilayah/kk-katolik', icon: 'fa-house-chimney-user' },
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
                { name: 'KK Katolik', href: '/kapela/kk-katolik', icon: 'fa-house-chimney-user' },
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
                { name: 'KK Katolik', href: '/kub/kk-katolik', icon: 'fa-house-chimney-user' },
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
                { name: 'Pengumuman Paroki', href: '/penulis/pengumuman', icon: 'fa-bullhorn' },
                { name: 'Sambutan Pastor', href: '/penulis/sambutan-pastor', icon: 'fa-quote-right' },
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

// Prefiks URL per peran — dipakai untuk navigasi dinamis (role switcher &
// pembuatan href berdasarkan peran aktif).
export const rolePrefixMap = {
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
