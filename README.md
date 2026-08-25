# SIPAROKI &bull; Sistem Informasi &amp; Manajemen Pastoral Paroki Terpadu

<p align="center">
  <img src="public/assets/frontend/siparoki/images/logo-benlutu.png" alt="SIPAROKI Logo" width="120" onerror="this.src='public/favicon.ico'" />
</p>

<p align="center">
  <strong>Platform Digital Universal Gereja Katolik Tingkat Paroki se-Indonesia</strong><br>
  <em>Solusi Manajemen Umat, Keluarga Katolik (KKK), Sakramen, Keuangan, Warta Paroki, dan Pelayanan Pastoral Berbasis Web Terintegrasi.</em>
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel 12" />
  <img src="https://img.shields.io/badge/Inertia.js-2.x-9553E9?style=for-the-badge&logo=inertia&logoColor=white" alt="Inertia.js" />
  <img src="https://img.shields.io/badge/Vue.js-3.x-4FC08D?style=for-the-badge&logo=vuedotjs&logoColor=white" alt="Vue 3" />
  <img src="https://img.shields.io/badge/Tailwind_CSS-3.x-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white" alt="Tailwind CSS" />
  <img src="https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.2+" />
  <img src="https://img.shields.io/badge/MySQL-5.7+-4479A1?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL" />
  <img src="https://img.shields.io/badge/License-MIT-green.svg?style=for-the-badge" alt="License MIT" />
</p>

---

## 🌟 Tentang SIPAROKI

**SIPAROKI (Sistem Informasi Paroki Terpadu)** adalah aplikasi sistem informasi manajemen gerejawi komprehensif yang dirancang untuk mendigitalkan dan mengoptimalkan seluruh alur administrasi pastoral gereja Katolik di Indonesia.

Aplikasi ini **Universal Multi-Parish Ready**, artinya dapat digunakan oleh **seluruh paroki di berbagai Keuskupan di Indonesia (KWI)** dengan identitas, logo, struktur hierarki, dan database mandiri tanpa perlu merombak kode sumber (*source code*).

---

## ✨ Fitur Unggulan Sistem

### 1. 🏛️ Universal Web Installation Wizard & Setup Paroki
- **Web Installer 4 Langkah**: Panduan instalasi grafis modern berbasis browser (`install.php`) untuk memeriksa kesiapan server, koneksi database, serta pemilihan identitas paroki default.
- **Master Referensi Nasional KWI**: Terintegrasi daftar master seluruh Keuskupan Agung & Sufragan se-Indonesia, Dekenat / Kevikepan, dan Paroki terdaftar.
- **Inisialisasi Paroki Otomatis**: Nama paroki, santo pelindung, alamat, kontak WhatsApp, email, nama pastor paroki, hingga logo gereja otomatis menyesuaikan di seluruh website dan panel admin.

### 2. 👨‍👩‍👧‍👦 Manajemen Umat & Kartu Keluarga Katolik (KKK Digital)
- **Registrasi Mandiri Umat**: Formulir pendaftaran akun umat (`/register`) dengan **Select2 Searchable** dan *live mutual filtering* antara **Wilayah**, **Stasi / Kapela**, dan **KUB (Komunitas Umat Basis)**.
- **Buku Induk Umat**: Data demografi lengkap (NIK, nama baptis, tanggal lahir, status perkawinan, golongan darah, pendidikan, pekerjaan, dll.).
- **Kartu Keluarga Katolik (KKK)**: Pengelompokan kepala keluarga, hubungan keluarga, status jemaat, dan pencetakan lembar KKK resmi.

### 3. 🕊️ Administrasi & Permohonan Sakramen
- **Pencatatan 7 Sakramen Gereja**: Baptis, Krisma, Komuni Pertama, Pernikahan, Imamat, Pengurapan Orang Sakit (Minyak Suci), dan Tobat.
- **Permohonan Sakramen Online**: Umat dapat mengajukan pendaftaran sakramen secara mandiri melalui portal umat, mengunggah berkas syarat, dan memantau status persetujuan sekretariat paroki.
- **Buku Induk Baptis & Cetak Sertifikat**: Pembuatan nomor surat baptis otomatis (Liber Baptizatorum) dan pencetakan surat permandian/baptis resmi berformat PDF.

### 4. 💰 Keuangan, Aset & Donasi QRIS
- **Pencatatan Kas Masuk & Keluar**: Rekapitulasi kas operasional, kolekte misa, dana pembangunan, dan pos kas per KUB/Stasi.
- **Metode Pembayaran & QRIS Dinamis**: Manajemen rekening bank paroki dan QRIS untuk kemudahan donasi, intensi misa, dan iuran umat.
- **Inventaris Aset Paroki**: Pendataan aset gereja, lokasi gedung, status kondisi, dan penanggung jawab aset.

### 5. 📰 Portal Informasi Publik & Warta Paroki
- **Jadwal Misa Interaktif**: Jadwal perayaan ekaristi harian, mingguan, hari raya, misa lingkungan/KUB, dan misa stasi luar.
- **Warta & Artikel Paroki**: Berita kegiatan, artikel rohani, renungan harian, dan pengumuman sekretariat.
- **Sistem Komentar Interaktif dengan Moderasi Sensor Kata Kasar**: Filter otomatis kata-kata makian/jelek, sensor kata kasar (`***`), serta panel moderasi komentar artikel bagi redaksi/admin.
- **Galeri Foto & Video Hero Banner**: Penayangan video profil gereja di header beranda serta dokumentasi foto kegiatan pastoral.

### 6. 🛡️ Security Center & Auto-Firewall
- **Brute Force Protection**: Pembatasan percobaan login salah dan pemblokiran otomatis IP mencurigakan ke daftar hitam (*blocked IPs*).
- **Audit Log Keamanan**: Pencatatan riwayat login, perubahan data penting, dan aktivitas operasional user.
- **Role-Based Access Control (RBAC)**: Pembatasan akses berbasis peran (*Least Privilege Access*) dengan enkripsi password standar Bcrypt.

---

## 👥 Struktur Peran & Hak Akses (9 Role Multi-Level)

| Role | Cakupan Akses |
|---|---|
| **Super Admin** | Akses penuh seluruh sistem, manajemen pengguna, konfigurasi paroki default, backup database, dan security center. |
| **Pastor Paroki** | Akses persetujuan sakramen, verifikasi data umat, laporan pastoral, jadwal misa, sambutan, dan buku kas paroki. |
| **Admin Paroki** | Pengelolaan sekretariat, pendaftaran sakramen, kartu keluarga, data umat, aset, dan surat-menyurat. |
| **Admin Wilayah** | Pengelolaan data umat, KKK, dan kegiatan pada tingkat Wilayah / Lingkungan terkait. |
| **Admin Kapela / Stasi** | Pengelolaan data umat, jadwal peribadatan, dan aset pada tingkat Stasi / Kapela luar. |
| **Pengurus KUB** | Pendataan warga basis KUB, iuran komunitas basis, dan verifikasi anggota KUB. |
| **Bendahara** | Pengelolaan transaksi keuangan, verifikasi bukti transfer/QRIS, buku kas, dan laporan keuangan. |
| **Redaksi / Komsos** | Publikasi berita, artikel renungan, pengumuman, galeri media, dan moderasi komentar publik. |
| **Umat Mandiri** | Akses portal umat untuk melihat data KKK digital, pengajuan sakramen, riwayat intensi misa, dan profil mandiri. |

---

## 📋 Persyaratan Sistem (System Requirements)

- **PHP**: Versi `8.2` atau `8.3+`
- **Database**: MySQL `5.7+` atau MariaDB `10.3+`
- **Ekstensi PHP Wajib**:
  - `pdo_mysql`
  - `openssl`
  - `mbstring`
  - `tokenizer`
  - `xml`
  - `ctype`
  - `json`
  - `bcmath`
  - `fileinfo`
  - `gd` (untuk pengolahan gambar & logo)
- **Web Server**: Apache (`mod_rewrite` aktif) atau Nginx
- **Node.js & NPM**: Versi `18+` (hanya diperlukan jika ingin mengompilasi ulang aset frontend).

---

## 🚀 Panduan Instalasi (Installation Guide)

### Metode 1: Web Installer Wizard (Sangat Mudah & Direkomendasikan)

Metode ini sangat cocok untuk pengguna **Localhost (Laragon/XAMPP)** maupun **Shared Hosting (cPanel)**:

1. **Unduh / Klon Repositori**:
   ```bash
   git clone https://github.com/wensputra2026/siparoki2026laravel.git
   ```
   *(Atau unduh file ZIP dari GitHub dan ekstrak ke folder web server Anda, misal `c:/laragon/www/siparoki` atau `public_html`).*

2. **Buka Web Installer di Browser**:
   - Jika di Localhost: Buka `http://localhost/siparoki/install.php` atau `http://127.0.0.1:8000/install.php`
   - Jika di Hosting/Domain: Buka `http://namadomainparoki.org/install.php`

3. **Ikuti 4 Langkah Setup Wizard**:
   - **Langkah 1 (Pemeriksaan Syarat)**: Installer memeriksa otomatis kesiapan PHP dan folder `storage/`.
   - **Langkah 2 (Koneksi Database)**: Masukkan Host, Port, Nama DB, Username, dan Password MySQL, lalu klik **"Uji Koneksi & Lanjut"**.
   - **Langkah 3 (Pilih Keuskupan & Paroki)**: Pilih Keuskupan Anda (se-Indonesia), pilih Dekenat & Paroki Anda, serta tentukan username/password Super Administrator.
   - **Langkah 4 (Selesai)**: Sistem secara otomatis mengimpor master database, menyusun konfigurasi `.env`, dan mengarahkan Anda ke halaman login.

---

### Metode 2: Instalasi Manual via Terminal / VPS Linux

Untuk instalasi di server VPS (Ubuntu / Debian / CentOS / Nginx / Apache):

1. **Klon Repositori**:
   ```bash
   cd /var/www
   git clone https://github.com/wensputra2026/siparoki2026laravel.git siparoki
   cd siparoki
   ```

2. **Install Dependensi Composer**:
   ```bash
   composer install --no-dev --optimize-autoloader
   ```

3. **Konfigurasi Environment File**:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   *Buka berkas `.env` dan sesuaikan kredensial database (`DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`), serta `APP_URL`.*

4. **Impor Skema Database Awal**:
   ```bash
   # Impor database baseline yang telah dilengkapi master Keuskupan & Paroki se-Indonesia
   mysql -u username_db -p nama_database < public/installer/database/siparoki.sql
   ```

5. **Atur Izin Folder (Permissions)**:
   ```bash
   chmod -R 775 storage bootstrap/cache public/uploads
   chown -R www-data:www-data storage bootstrap/cache public/uploads
   ```

6. **Konfigurasi Nginx Server Block (Contoh)**:
   ```nginx
   server {
       listen 80;
       server_name paroki-anda.org www.paroki-anda.org;
       root /var/www/siparoki/public;

       index index.php index.html;

       location / {
           try_files $uri $uri/ /index.php?$query_string;
       }

       location ~ \.php$ {
           include snippets/fastcgi-php.conf;
           fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
           fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
           include fastcgi_params;
       }

       location ~ /\.(?!well-known).* {
           deny all;
       }
   }
   ```

7. **Buka Aplikasi**:
   Akses `http://paroki-anda.org/setup-paroki` untuk memilih Keuskupan dan Paroki default Anda.

---

## 🛠️ Pengembangan Frontend (Build Assets)

Aplikasi ini menggunakan perpaduan **Inertia.js**, **Vue 3 (Composition API)**, dan **Tailwind CSS**. Jika Anda melakukan modifikasi pada file komponen di `resources/js/`:

```bash
# Install package Node.js
npm install

# Jalankan dev server lokal
npm run dev

# Kompilasi asset untuk server produksi
npm run build
```

---

## 📁 Struktur Direktori Utama Proyek

```plaintext
siparoki/
├── app/
│   ├── Http/
│   │   ├── Controllers/       # Controller (Auth, Panel, SetupParoki, PageController, dll.)
│   │   └── Middleware/        # Middleware (PanelAccess, EnsureParokiConfigured, dll.)
│   └── Models/                # Eloquent Models (Umat, KK, Sakramen, Paroki, Keuskupan, dll.)
├── bootstrap/                 # Konfigurasi bootstrap Laravel 12
├── database/
│   ├── data/                  # Master JSON Keuskupan & baseline SQL
│   ├── migrations/            # Berkas migrasi database
│   └── seeders/               # Database seeders
├── public/
│   ├── assets/                # Aset statis frontend & backend
│   ├── build/                 # Hasil kompilasi Vite production bundle
│   ├── installer/             # Database baseline & master JSON installer
│   ├── uploads/               # Direktori berkas unggahan publik (logo, gambar, dokumen)
│   ├── install.php            # Standalone Web Installer
│   └── index.php              # Front controller publik
├── resources/
│   ├── js/
│   │   ├── Components/        # Komponen UI Vue (SearchableSelect, RichTextEditor, dll.)
│   │   ├── Layouts/           # Layout utama (AppLayout, Dashboard layout)
│   │   └── Pages/             # Halaman Inertia Vue (Auth, ProfilParoki, SetupParoki, dll.)
│   └── views/                 # Blade templates (Halaman publik, register, login)
├── routes/
│   └── web.php                # Seluruh rute aplikasi & panel navigasi
└── install.php                # Root installer forwarder (untuk Shared Hosting)
```

---

## 🤝 Kontribusi & Dukungan

Aplikasi ini dikembangkan dan didedikasikan untuk kemajuan digitalisasi pastoral gereja Katolik di Indonesia. Kontribusi berupa saran, pelaporan bug (*issue*), maupun *pull request* sangat dipersilakan.

- **Repositori GitHub**: [https://github.com/wensputra2026/siparoki2026laravel](https://github.com/wensputra2026/siparoki2026laravel)
- **Author / Maintainer**: Tim Pengembang SIPAROKI

---

## 📄 Lisensi (License)

SIPAROKI dirilis di bawah lisensi terbuka [MIT License](LICENSE). Anda bebas menggunakan, memodifikasi, dan mendistribusikan aplikasi ini untuk kebutuhan paroki dan keuskupan Anda.

---
<p align="center">
  <em>"Melayani dengan Kasih dan Menata Pelayanan Pastoral Secara Terpadu."</em><br>
  <strong>SIPAROKI &copy; 2026</strong>
</p>
