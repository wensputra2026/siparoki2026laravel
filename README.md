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

SIPAROKI 2026 telah dilengkapi dengan **Interactive Web Installer Wizard** yang langsung aktif secara otomatis saat aplikasi pertama kali dijalankan oleh paroki baru.

---

### 🌟 Metode 1: Web Installer Wizard Otomatis (Plug & Play - Sangat Mudah)

Cocok untuk pengguna **Laragon, XAMPP, Shared Hosting (cPanel), maupun Localhost**:

1. **Unduh / Klon Repositori**:
   ```bash
   git clone https://github.com/wensputra2026/siparoki2026laravel.git
   ```
   *(Atau klik tombol **Code > Download ZIP** di GitHub dan ekstrak ke folder web server Anda, contoh: `C:/laragon/www/siparoki` atau `public_html`).*

2. **Buka Aplikasi di Browser**:
   - Jika menggunakan Laragon / PHP built-in server: Buka `http://127.0.0.1:8000` atau `http://localhost/siparoki/public`
   - Jika menggunakan Domain Hosting: Buka `https://namadomainparoki.org`
   - *Sistem secara otomatis mendeteksi instalasi baru dan langsung mengarahkan Anda ke antarmuka **Web Installer Wizard (`/installer` atau `/install.php`)**.*

3. **Ikuti 5 Langkah Mudah Installer Wizard**:
   - 🔍 **Langkah 1 (Pemeriksaan Server)**: Sistem memeriksa otomatis versi PHP (`>= 8.2`), 12 ekstensi PHP penting, serta izin tulis folder. Klik **"Lanjut"**.
   - 🗄️ **Langkah 2 (Konfigurasi Database)**: Masukkan Host (`127.0.0.1` atau `localhost`), Port (`3306`), Nama Database, Username, dan Password MySQL Anda. Klik tombol **"Uji Koneksi Database"** (Database akan dibuatkan otomatis jika belum ada).
   - ⛪ **Langkah 3 (Pilih Keuskupan, Dekenat & Paroki)**: 
     - Pilih **Keuskupan** Anda dari daftar master 39 Keuskupan KWI se-Indonesia.
     - Pilih **Dekenat / Kevikepan** (otomatis memfilter paroki sesuai wilayah gerejawi).
     - Pilih **Paroki Terdaftar** (nama & alamat langsung terisi) atau pilih opsi *"+ Paroki Baru"* jika paroki Anda belum terdaftar.
     - Masukkan nama **Pastor Paroki Aktif** dan alamat sekretariat.
   - 🛡️ **Langkah 4 (Akun Super Admin Pertama)**: Masukkan Nama Lengkap, Username, Email, dan Password untuk akun Super Administrator utama Anda.
   - 🚀 **Langkah 5 (Mulai Instalasi)**: Klik **"Mulai Instalasi Sekarang"**. Sistem akan otomatis menyusun file konfigurasi `.env`, menjalankan seluruh migrasi database, menetapkan identitas paroki Anda, membuat akun Super Admin, dan mengunci file installer demi keamanan.

4. **Selesai & Siap Digunakan**:
   Klik tombol **"Masuk ke Panel SIPAROKI"** untuk langsung login ke dashboard administrator paroki Anda!

---

### 🌐 Metode 2: Panduan Lengkap Instalasi di Shared Hosting (cPanel / DirectAdmin / Plesk)

Bagi paroki yang menggunakan layanan web hosting bersama (*Shared Hosting cPanel*):

#### Langkah 1: Persiapan Versi PHP & Ekstensi di cPanel
1. Masuk ke **cPanel Hosting** Anda.
2. Cari dan buka menu **Select PHP Version** (atau *MultiPHP Manager*).
3. Atur versi PHP ke **PHP 8.2** atau **PHP 8.3**.
4. Masuk ke tab **Extensions**, pastikan ekstensi berikut dicentang aktif:
   - `pdo_mysql`, `openssl`, `mbstring`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `fileinfo`, `gd`, `curl`, `zip`.

#### Langkah 2: Unggah Source Code SIPAROKI
Pilih salah satu cara berikut:
- **Cara A (Menggunakan Git cPanel - Disarankan)**:
  1. Buka menu **Git™ Version Control** di cPanel.
  2. Klik **Create**.
  3. Masukkan Clone URL: `https://github.com/wensputra2026/siparoki2026laravel.git`
  4. Tentukan Repository Path: `repositories/siparoki` atau langsung di `public_html`.
  5. Klik **Create**.
- **Cara B (Upload Berkas ZIP)**:
  1. Download ZIP dari repository: [Download ZIP](https://github.com/wensputra2026/siparoki2026laravel/archive/refs/heads/main.zip).
  2. Buka **File Manager** cPanel &rarr; Masuk ke direktori `public_html` (atau folder subdomain Anda).
  3. Upload file ZIP dan ekstrak semua berkasnya.

#### Langkah 3: Penataan Folder Root Web
SIPAROKI sudah dilengkapi file `.htaccess` dan `index.php` di folder utama yang otomatis meneruskan permintaan ke folder `public/`, sehingga Anda **tidak perlu memindahkan berkas secara manual**.
- Jika Anda ingin keamanan maksimal, arahkan *Document Root* domain paroki Anda di menu cPanel **Domains / Subdomains** langsung ke folder:
  `public_html/public`

#### Langkah 4: Buat Database MySQL
1. Buka menu **MySQL&reg; Database Wizard** di cPanel.
2. Buat nama database baru (contoh: `u1234_siparoki`).
3. Buat pengguna database & password baru (contoh: `u1234_adminparoki`).
4. Berikan hak akses penuh (**ALL PRIVILEGES**), lalu klik *Make Changes*.

#### Langkah 5: Jalankan Web Installer
1. Buka browser dan akses domain paroki Anda:
   `https://namaparoki-anda.org/install.php` (atau `https://namaparoki-anda.org/installer`)
2. Masukkan nama database, username database, dan password yang baru saja dibuat di Langkah 4.
3. Pilih Keuskupan, Dekenat, dan Paroki Anda.
4. Klik **Mulai Instalasi Sekarang**. Sistem paroki Anda langsung aktif dan siap melayani umat!

---

### 🔄 Panduan Update Otomatis dari GitHub (Ketika Programmer Menambah Fitur / Perbaikan)

Ketika tim programmer merilis perbaikan bug, penambahan fitur sakramen baru, atau pembaruan modul di repository [https://github.com/wensputra2026/siparoki2026laravel](https://github.com/wensputra2026/siparoki2026laravel), paroki dapat memperbarui sistem secara instan tanpa kehilangan data database maupun berkas konfigurasi paroki yang sudah berjalan.

```mermaid
flowchart LR
    A["Programmer Push Update ke GitHub"] --> B["cPanel / Server Paroki"]
    B --> C["Tarik Kode Terbaru (Git Pull)"]
    C --> D["Jalankan Migrasi Database Baru"]
    D --> E["SIPAROKI Paroki Terupdate & Data Aman!"]
```

Pilih salah satu metode pembaruan berikut sesuai kebutuhan hosting Anda:

---

#### 🌟 Pilihan 1: Update 1-Klik via cPanel Git™ Version Control (Paling Praktis untuk Shared Hosting)

Jika instalasi di cPanel menggunakan Git Version Control:
1. Masuk ke **cPanel Hosting** &rarr; Buka menu **Git™ Version Control**.
2. Klik tombol **Manage** pada repository SIPAROKI Anda.
3. Klik tab **Pull or Deploy**.
4. Klik tombol biru **"Update from Remote"** (atau *Pull from Remote*).
5. cPanel akan otomatis mengunduh seluruh penambahan fitur dan pembaruan kode terbaru dari GitHub.
6. *(Opsional)* Jika ada penambahan tabel/struktur data baru dari programmer, buka menu **Terminal cPanel** (atau Cron Job 1x) dan jalankan:
   ```bash
   php artisan migrate --force
   php artisan optimize:clear
   ```
7. Selesai! Seluruh fitur baru langsung aktif dan data umat Anda tetap 100% aman.

---

#### ⚡ Pilihan 2: Update Otomatis Tanpa Sentuh via GitHub Webhook (Zero-Click Auto Deploy)

Anda dapat membuat server hosting paroki otomatis terupdate setiap kali programmer melakukan `push` ke GitHub:

1. Di hosting paroki, buat file script updater sederhana di `public/deploy-webhook.php`:
   ```php
   <?php
   // Secret token pengaman
   $secret = 'KUNCI_RAHASIA_PAROKI_ANDA_123';
   if (($_GET['token'] ?? '') !== $secret) {
       http_response_code(403);
       die('Access Denied');
   }

   $output = shell_exec('cd .. && git pull origin main 2>&1 && php artisan migrate --force 2>&1 && php artisan optimize:clear 2>&1');
   echo "<pre>$output</pre>";
   ```
2. Buka repository GitHub: [https://github.com/wensputra2026/siparoki2026laravel](https://github.com/wensputra2026/siparoki2026laravel) &rarr; **Settings** &rarr; **Webhooks** &rarr; **Add Webhook**.
3. Masukkan **Payload URL**: `https://namaparoki-anda.org/deploy-webhook.php?token=KUNCI_RAHASIA_PAROKI_ANDA_123`
4. Pilih Content type: `application/json` &rarr; Centang *Just the push event* &rarr; Klik **Add Webhook**.
5. **Hasil**: Setiap kali tim pengembang merilis pembaruan, server paroki otomatis terupdate secara *real-time*.

---

#### 🖥️ Pilihan 3: Update via SSH Terminal (Untuk VPS / Cloud Server)

Jika menggunakan VPS Linux (Ubuntu/Debian) atau hosting dengan akses SSH:
```bash
# 1. Masuk ke direktori aplikasi
cd /var/www/siparoki

# 2. Tarik update terbaru dari GitHub
git pull origin main

# 3. Jalankan migrasi database jika ada skema baru
php artisan migrate --force

# 4. Bersihkan cache aplikasi agar fitur baru langsung terbaca
php artisan optimize:clear
```

---

#### 📦 Pilihan 4: Update Manual (Bagi Hosting Tanpa Fitur Git)

Bagi paroki yang menggunakan shared hosting biasa tanpa akses Git/SSH:
1. Download berkas rilis terbaru berupa ZIP dari GitHub: [Download Update ZIP](https://github.com/wensputra2026/siparoki2026laravel/archive/refs/heads/main.zip).
2. Ekstrak ZIP di komputer Anda.
3. Unggah dan timpa (*overwrite*) folder-folder sistem berikut ke File Manager cPanel:
   - `app/`
   - `resources/`
   - `routes/`
   - `public/build/`
   - `database/migrations/`
4. > [!IMPORTANT]
   > **JANGAN PERNAH MENIMPA / MENGHAPUS**:
   > - Berkas `.env` (berisi password database paroki Anda)
   > - Direktori `storage/` (berisi log, sesi, dan status instalasi)
   > - Direktori `public/uploads/` (berisi foto umat, logo paroki, surat baptis, dan dokumen arsip)
5. Akses URL: `https://namaparoki-anda.org/` &rarr; sistem otomatis berjalan dengan versi terbaru!

---

### 💻 Metode 3: Instalasi Manual via Terminal / VPS Linux (Untuk Pengembang)

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
   Akses `http://paroki-anda.org/installer` untuk menyelesaikan penyesuaian paroki Anda.

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
│   │   └── Layouts/           # Layout utama (AppLayout, Dashboard layout)
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
