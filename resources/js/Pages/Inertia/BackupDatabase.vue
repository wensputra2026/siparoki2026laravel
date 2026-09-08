<script setup>
import { ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';

const props = defineProps({
    role: { type: String, default: 'Super Admin' },
    prefix: { type: String, default: 'superadmin' },
    backups: { type: Array, default: () => [] },
    databaseName: { type: String, default: '' },
    tableCount: { type: Number, default: 0 },
    dbSizeMb: { type: Number, default: 0 },
    totalRows: { type: Number, default: 0 },
    mediaSizeMb: { type: Number, default: 0 },
    mediaFileCount: { type: Number, default: 0 },
    lastBackupAt: { type: String, default: null },
});

const isBackingUp = ref(false);
const isBackingUpMedia = ref(false);
const isRestoring = ref(false);
const selectedBackup = ref(null);
const showRestoreModal = ref(false);
const showUploadModal = ref(false);

const confirmModal = ref({
    show: false,
    title: '',
    message: '',
    confirmText: 'Ya, Lanjutkan',
    type: 'danger',
    action: null,
    loading: false,
});

const uploadForm = useForm({
    file_sql: null,
    file_backup: null,
});

const formatBytes = (bytes, decimals = 2) => {
    if (!+bytes) return '0 Bytes';
    const k = 1024;
    const dm = decimals < 0 ? 0 : decimals;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return `${parseFloat((bytes / Math.pow(k, i)).toFixed(dm))} ${sizes[i]}`;
};

const handleDirectDownloadSql = () => {
    window.location.href = `/${props.prefix}/backup-database/download-direct?type=sql`;
};

const handleDirectDownloadMedia = () => {
    window.location.href = `/${props.prefix}/backup-database/download-direct?type=media`;
};

const handleClearServerBackups = () => {
    confirmModal.value = {
        show: true,
        title: 'Kosongkan Seluruh Arsip Server',
        message: 'Apakah Anda yakin ingin menghapus semua file backup yang tersimpan di server? Tindakan ini akan mengosongkan riwayat di server. File yang sudah Anda unduh ke komputer tetap aman.',
        confirmText: 'Ya, Kosongkan Arsip',
        type: 'danger',
        loading: false,
        action: () => {
            confirmModal.value.loading = true;
            router.post(`/${props.prefix}/backup-database/clear-server-backups`, {}, {
                preserveScroll: true,
                onFinish: () => {
                    confirmModal.value.loading = false;
                    confirmModal.value.show = false;
                }
            });
        }
    };
};

const handleGenerateBackup = () => {
    confirmModal.value = {
        show: true,
        title: 'Simpan Salinan Database di Server',
        message: 'Sistem akan membuat file cadangan database MySQL (.sql) dan menyimpannya ke folder arsip server SIPAROKI.',
        confirmText: 'Buat & Simpan Cadangan',
        type: 'info',
        loading: false,
        action: () => {
            isBackingUp.value = true;
            confirmModal.value.loading = true;
            router.post(`/${props.prefix}/backup-database/generate`, {}, {
                preserveScroll: true,
                onFinish: () => {
                    isBackingUp.value = false;
                    confirmModal.value.loading = false;
                    confirmModal.value.show = false;
                }
            });
        }
    };
};

const handleGenerateMediaBackup = () => {
    confirmModal.value = {
        show: true,
        title: 'Simpan Salinan Media di Server',
        message: 'Sistem akan mengompresi seluruh file media, foto, dan logo (.zip) dan menyimpannya ke folder arsip server.',
        confirmText: 'Buat & Simpan Arsip Media',
        type: 'info',
        loading: false,
        action: () => {
            isBackingUpMedia.value = true;
            confirmModal.value.loading = true;
            router.post(`/${props.prefix}/backup-database/generate-media`, {}, {
                preserveScroll: true,
                onFinish: () => {
                    isBackingUpMedia.value = false;
                    confirmModal.value.loading = false;
                    confirmModal.value.show = false;
                }
            });
        }
    };
};

const handleConfirmRestore = (backup) => {
    selectedBackup.value = backup;
    showRestoreModal.value = true;
};

const executeRestore = () => {
    if (!selectedBackup.value) return;
    isRestoring.value = true;
    router.post(`/${props.prefix}/backup-database/${selectedBackup.value.id}/restore`, {}, {
        preserveScroll: true,
        onFinish: () => {
            isRestoring.value = false;
            showRestoreModal.value = false;
            selectedBackup.value = null;
        }
    });
};

const handleUploadFile = (e) => {
    const file = e.target.files[0];
    uploadForm.file_sql = file;
    uploadForm.file_backup = file;
};

const submitUploadRestore = () => {
    if (!uploadForm.file_backup && !uploadForm.file_sql) {
        return;
    }
    const isZip = uploadForm.file_backup?.name?.toLowerCase().endsWith('.zip');
    const title = isZip ? 'Konfirmasi Restore Media' : 'Konfirmasi Restore Database';
    const msg = isZip
        ? '<strong>PERINGATAN:</strong> Memulihkan file media dari file ZIP akan menimpa file gambar/foto yang ada di server. Apakah Anda yakin ingin melanjutkan?'
        : '<strong>PERINGATAN:</strong> Memulihkan database dari file SQL akan menimpa seluruh data tabel yang ada di database saat ini. Apakah Anda yakin ingin melanjutkan?';

    confirmModal.value = {
        show: true,
        title: title,
        message: msg,
        confirmText: 'Ya, Lanjutkan Restore',
        type: 'warning',
        loading: false,
        action: () => {
            isRestoring.value = true;
            confirmModal.value.loading = true;
            uploadForm.post(`/${props.prefix}/backup-database/upload-restore`, {
                preserveScroll: true,
                onSuccess: () => {
                    showUploadModal.value = false;
                    uploadForm.reset();
                },
                onFinish: () => {
                    isRestoring.value = false;
                    confirmModal.value.loading = false;
                    confirmModal.value.show = false;
                }
            });
        }
    };
};

const handleDeleteBackup = (backup) => {
    confirmModal.value = {
        show: true,
        title: 'Hapus File Backup',
        message: `Apakah Anda yakin ingin menghapus file backup <strong class="text-slate-800 font-mono">"${backup.nama_file}"</strong> dari server? Tindakan ini tidak dapat dibatalkan.`,
        confirmText: 'Ya, Hapus File',
        type: 'danger',
        loading: false,
        action: () => {
            confirmModal.value.loading = true;
            router.delete(`/${props.prefix}/backup-database/${backup.id}`, {
                preserveScroll: true,
                onFinish: () => {
                    confirmModal.value.loading = false;
                    confirmModal.value.show = false;
                }
            });
        }
    };
};
</script>

<template>
    <AppLayout :fullWidth="true">
        <Head title="Backup & Restore Database & Media - SIPAROKI" />

        <div class="w-full space-y-6 pb-12">
            <!-- 1. EXECUTIVE HEADER BANNER -->
            <div class="bg-gradient-to-r from-blue-900 via-indigo-900 to-slate-900 rounded-3xl p-6 sm:p-8 text-white shadow-xl shadow-blue-900/15 relative overflow-hidden">
                <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
                <div class="absolute right-20 top-0 w-32 h-32 bg-blue-400/20 rounded-full blur-xl pointer-events-none"></div>

                <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div class="space-y-2">
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/15 backdrop-blur-md text-blue-100 text-xs font-semibold border border-white/20">
                            <i class="fa-solid fa-cloud-arrow-down"></i>
                            <span>Unduh Langsung ke Perangkat &amp; Tanpa Beban Server</span>
                        </div>
                        <h1 class="text-2xl sm:text-3xl font-black tracking-tight flex items-center gap-3">
                            Backup &amp; Restore Terpadu
                            <span class="text-xs font-bold px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/40">
                                Download Langsung
                            </span>
                        </h1>
                        <p class="text-blue-100 text-xs sm:text-sm max-w-2xl leading-relaxed">
                            Unduh langsung database sensus umat &amp; sakramen (.SQL) serta seluruh arsip media gambar, logo, foto pastor, banner, dan dokumen (.ZIP) ke komputer Anda secara instan tanpa tersimpan di server.
                        </p>
                    </div>

                    <!-- Action Buttons: Direct Downloads prominent -->
                    <div class="flex items-center gap-2.5 flex-wrap shrink-0">
                        <button
                            type="button"
                            @click="showUploadModal = true"
                            class="px-4 py-2.5 rounded-2xl bg-white/15 hover:bg-white/25 backdrop-blur-md text-white text-xs font-bold border border-white/25 shadow-sm transition flex items-center gap-2 cursor-pointer"
                            title="Unggah file cadangan untuk memulihkan sistem"
                        >
                            <i class="fa-solid fa-upload"></i>
                            <span>Upload Restore</span>
                        </button>
                        <button
                            type="button"
                            @click="handleDirectDownloadMedia"
                            class="px-4 py-2.5 rounded-2xl bg-purple-600 hover:bg-purple-700 text-white text-xs font-bold shadow-lg shadow-purple-900/20 transition flex items-center gap-2 cursor-pointer"
                            title="Unduh langsung semua foto dan dokumen ke komputer tanpa tersimpan di server"
                        >
                            <i class="fa-solid fa-file-zipper"></i>
                            <span>Download Media (.ZIP) Langsung</span>
                        </button>
                        <button
                            type="button"
                            @click="handleDirectDownloadSql"
                            class="px-5 py-2.5 rounded-2xl bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-black shadow-lg shadow-emerald-900/20 transition flex items-center gap-2 cursor-pointer"
                            title="Unduh langsung database SQL ke komputer tanpa tersimpan di server"
                        >
                            <i class="fa-solid fa-download"></i>
                            <span>Download SQL Langsung</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- 2. DATABASE & MEDIA METRICS CARDS -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Card 1: Nama Database -->
                <div class="bg-white dark:bg-slate-800 rounded-3xl p-5 border border-slate-200/80 dark:border-slate-700/80 shadow-xs hover:shadow-md transition flex items-center gap-3.5">
                    <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg shrink-0 shadow-2xs">
                        <i class="fa-solid fa-server"></i>
                    </div>
                    <div class="min-w-0">
                        <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Database MySQL</div>
                        <div class="text-sm font-black text-slate-800 dark:text-white truncate font-mono">{{ databaseName || 'parokibenlutu' }}</div>
                        <div class="text-[11px] text-slate-400 font-medium">{{ tableCount }} Tabel Data</div>
                    </div>
                </div>

                <!-- Card 2: Ukuran Database -->
                <div class="bg-white dark:bg-slate-800 rounded-3xl p-5 border border-slate-200/80 dark:border-slate-700/80 shadow-xs hover:shadow-md transition flex items-center gap-3.5">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg shrink-0 shadow-2xs">
                        <i class="fa-solid fa-hard-drive"></i>
                    </div>
                    <div>
                        <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Ukuran Database</div>
                        <div class="text-sm font-black text-slate-800 dark:text-white">{{ dbSizeMb }} MB</div>
                        <div class="text-[11px] text-slate-400 font-medium">{{ totalRows.toLocaleString('id-ID') }} Baris Data</div>
                    </div>
                </div>

                <!-- Card 3: Media & Gambar Uploads -->
                <div class="bg-white dark:bg-slate-800 rounded-3xl p-5 border border-slate-200/80 dark:border-slate-700/80 shadow-xs hover:shadow-md transition flex items-center gap-3.5">
                    <div class="w-12 h-12 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center text-lg shrink-0 shadow-2xs">
                        <i class="fa-solid fa-photo-film"></i>
                    </div>
                    <div>
                        <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Media &amp; Gambar Uploads</div>
                        <div class="text-sm font-black text-slate-800 dark:text-white">{{ mediaSizeMb }} MB</div>
                        <div class="text-[11px] text-slate-400 font-medium">{{ mediaFileCount.toLocaleString('id-ID') }} File Foto &amp; Dokumen</div>
                    </div>
                </div>

                <!-- Card 4: Backup Terakhir -->
                <div class="bg-white dark:bg-slate-800 rounded-3xl p-5 border border-slate-200/80 dark:border-slate-700/80 shadow-xs hover:shadow-md transition flex items-center gap-3.5">
                    <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg shrink-0 shadow-2xs">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                    </div>
                    <div class="min-w-0">
                        <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Backup Terakhir</div>
                        <div class="text-xs font-black text-slate-800 dark:text-white truncate">
                            {{ lastBackupAt ? new Date(lastBackupAt).toLocaleString('id-ID') : 'Belum Ada' }}
                        </div>
                        <div class="text-[11px] text-slate-400 font-medium">{{ backups.length }} File Tersimpan</div>
                    </div>
                </div>
            </div>

            <!-- 3. BACKUP HISTORY TABLE -->
            <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200/80 dark:border-slate-700/80 shadow-xs overflow-hidden">
                <div class="p-5 sm:p-6 border-b border-slate-100 dark:border-slate-700/80 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h2 class="text-sm font-black text-slate-900 dark:text-white flex items-center gap-2">
                            <i class="fa-solid fa-clock-rotate-left text-blue-600"></i>
                            <span>Arsip Cadangan di Server</span>
                            <span class="px-2.5 py-0.5 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 text-[11px] font-bold">
                                {{ backups.length }} File
                            </span>
                        </h2>
                        <p class="text-xs text-slate-400 mt-0.5">Catatan arsip cadangan yang disimpan di server. Anda dapat mengunduh ulang, memulihkan (restore), atau membersihkannya agar server tetap ringan.</p>
                    </div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <button
                            v-if="backups.length > 0"
                            type="button"
                            @click="handleClearServerBackups"
                            class="px-3 py-1.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 text-xs font-bold transition flex items-center gap-1.5 cursor-pointer"
                            title="Hapus semua arsip backup dari disk server"
                        >
                            <i class="fa-solid fa-trash-can"></i>
                            <span>Kosongkan Arsip Server</span>
                        </button>
                        <button
                            type="button"
                            @click="handleGenerateBackup"
                            :disabled="isBackingUp"
                            class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition flex items-center gap-1.5 cursor-pointer disabled:opacity-50"
                            title="Simpan satu salinan arsip SQL di server"
                        >
                            <i v-if="isBackingUp" class="fa-solid fa-spinner fa-spin"></i>
                            <i v-else class="fa-solid fa-hard-drive"></i>
                            <span>{{ isBackingUp ? 'Menyimpan...' : 'Simpan Salinan di Server' }}</span>
                        </button>
                    </div>
                </div>

                <!-- Table Content -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="bg-slate-50/80 border-b border-slate-200/80 text-slate-600 font-bold">
                                <th class="py-3 px-4 w-12 text-center">#</th>
                                <th class="py-3 px-4">Nama File Backup</th>
                                <th class="py-3 px-4">Jenis Arsip</th>
                                <th class="py-3 px-4">Ukuran</th>
                                <th class="py-3 px-4">Waktu Pembuatan</th>
                                <th class="py-3 px-4">Dibuat Oleh</th>
                                <th class="py-3 px-4 text-center w-48">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            <tr v-if="backups.length === 0">
                                <td colspan="7" class="py-12 text-center text-slate-400">
                                    <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-2 text-xl">
                                        <i class="fa-solid fa-box-open"></i>
                                    </div>
                                    <p class="font-bold text-sm text-slate-600">Belum Ada File Backup</p>
                                    <p class="text-xs text-slate-400 mt-0.5">Gunakan tombol "Backup Database (.SQL)" atau "Backup Media &amp; Foto (.ZIP)" di atas.</p>
                                </td>
                            </tr>
                            <tr
                                v-for="(b, idx) in backups"
                                :key="b.id"
                                class="hover:bg-slate-50/80 transition"
                            >
                                <td class="py-3 px-4 text-center font-bold text-slate-400">{{ idx + 1 }}</td>
                                <td class="py-3 px-4 font-mono font-bold text-slate-900 flex items-center gap-2">
                                    <i v-if="b.is_media" class="fa-solid fa-file-zipper text-purple-600 text-sm"></i>
                                    <i v-else class="fa-solid fa-file-code text-blue-600 text-sm"></i>
                                    <span>{{ b.nama_file }}</span>
                                </td>
                                <td class="py-3 px-4">
                                    <span
                                        v-if="b.is_media"
                                        class="px-2.5 py-0.5 rounded-full bg-purple-50 text-purple-700 border border-purple-200 text-[10px] font-bold inline-flex items-center gap-1"
                                    >
                                        <i class="fa-solid fa-images text-[9px]"></i> Media (ZIP)
                                    </span>
                                    <span
                                        v-else
                                        class="px-2.5 py-0.5 rounded-full bg-blue-50 text-blue-700 border border-blue-200 text-[10px] font-bold inline-flex items-center gap-1"
                                    >
                                        <i class="fa-solid fa-database text-[9px]"></i> Database (SQL)
                                    </span>
                                </td>
                                <td class="py-3 px-4 font-medium text-slate-600">
                                    <span class="px-2 py-0.5 rounded-md bg-slate-100 font-mono text-[11px]">
                                        {{ formatBytes(b.ukuran) }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 font-medium text-slate-600">
                                    {{ new Date(b.created_at).toLocaleString('id-ID') }}
                                </td>
                                <td class="py-3 px-4 font-medium text-slate-600">
                                    <span class="flex items-center gap-1.5">
                                        <i class="fa-solid fa-user-shield text-slate-400 text-xs"></i>
                                        {{ b.dibuat_oleh || 'Administrator' }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <!-- Download Button -->
                                        <a
                                            :href="`/${prefix}/backup-database/${b.id}/download`"
                                            class="w-8 h-8 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-600 flex items-center justify-center transition"
                                            :title="b.is_media ? 'Download File ZIP Media' : 'Download File SQL Database'"
                                        >
                                            <i class="fa-solid fa-download text-xs"></i>
                                        </a>

                                        <!-- Restore Button -->
                                        <button
                                            type="button"
                                            @click="handleConfirmRestore(b)"
                                            class="w-8 h-8 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-600 flex items-center justify-center transition cursor-pointer"
                                            :title="b.is_media ? 'Pulihkan (Restore) Seluruh Foto & Gambar dari File Ini' : 'Pulihkan (Restore) Database dari File Ini'"
                                        >
                                            <i class="fa-solid fa-rotate text-xs"></i>
                                        </button>

                                        <!-- Delete Button -->
                                        <button
                                            type="button"
                                            @click="handleDeleteBackup(b)"
                                            class="w-8 h-8 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 flex items-center justify-center transition cursor-pointer"
                                            title="Hapus File Backup"
                                        >
                                            <i class="fa-solid fa-trash-can text-xs"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal Confirm Restore -->
        <div
            v-if="showRestoreModal"
            class="fixed inset-0 z-50 bg-slate-950/70 backdrop-blur-sm flex items-center justify-center p-4"
        >
            <div class="bg-white dark:bg-slate-800 rounded-3xl max-w-md w-full p-6 sm:p-8 shadow-2xl space-y-4 border border-slate-200/80 dark:border-slate-700/80">
                <div class="w-12 h-12 rounded-2xl bg-amber-50 dark:bg-amber-950 text-amber-600 dark:text-amber-400 flex items-center justify-center text-xl mx-auto shadow-2xs">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <div class="text-center">
                    <h3 class="text-base font-black text-slate-900 dark:text-white">
                        {{ selectedBackup?.is_media ? 'Konfirmasi Pemulihan File Media & Foto' : 'Konfirmasi Pemulihan Database' }}
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                        Anda akan memulihkan data dari file:
                        <strong class="font-mono text-slate-800 dark:text-slate-200 block mt-1">{{ selectedBackup?.nama_file }}</strong>
                    </p>
                    <div class="mt-3 p-3.5 rounded-2xl bg-amber-50/80 dark:bg-amber-950/40 border border-amber-200/60 dark:border-amber-900/60 text-left text-[11px] text-amber-800 dark:text-amber-300 space-y-1">
                        <p class="font-bold flex items-center gap-1.5">
                            <i class="fa-solid fa-circle-exclamation text-amber-600 dark:text-amber-400"></i>
                            Peringatan Penting:
                        </p>
                        <p v-if="selectedBackup?.is_media">
                            Proses ini akan mengekstrak file arsip ZIP dan menimpa foto pastor, logo, gambar galeri, banner, dan dokumen arsip yang ada di folder <code>public/uploads/</code> dengan isi arsip ini.
                        </p>
                        <p v-else>
                            Proses ini akan menimpa seluruh data tabel yang saat ini ada di database dengan data yang tersimpan pada file backup SQL tersebut.
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2.5 pt-2">
                    <button
                        type="button"
                        @click="showRestoreModal = false"
                        :disabled="isRestoring"
                        class="flex-1 py-2.5 rounded-2xl border border-slate-300 dark:border-slate-600 hover:bg-slate-50 dark:hover:bg-slate-700 text-xs font-bold text-slate-700 dark:text-slate-200 transition cursor-pointer"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        @click="executeRestore"
                        :disabled="isRestoring"
                        class="flex-1 py-2.5 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-xs font-bold text-white transition flex items-center justify-center gap-2 cursor-pointer shadow-md shadow-emerald-600/20 disabled:opacity-50"
                    >
                        <i v-if="isRestoring" class="fa-solid fa-spinner fa-spin"></i>
                        <span>{{ isRestoring ? 'Memulihkan...' : 'Ya, Restore Sekarang' }}</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal Upload & Restore File -->
        <div
            v-if="showUploadModal"
            class="fixed inset-0 z-50 bg-slate-950/70 backdrop-blur-sm flex items-center justify-center p-4"
        >
            <div class="bg-white dark:bg-slate-800 rounded-3xl max-w-md w-full p-6 sm:p-8 shadow-2xl space-y-4 border border-slate-200/80 dark:border-slate-700/80">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-700 pb-3">
                    <h3 class="text-sm font-black text-slate-900 dark:text-white flex items-center gap-2">
                        <i class="fa-solid fa-upload text-blue-600"></i>
                        <span>Upload &amp; Restore Backup</span>
                    </h3>
                    <button
                        type="button"
                        @click="showUploadModal = false"
                        class="w-8 h-8 rounded-xl text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 flex items-center justify-center cursor-pointer"
                    >
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1.5">Pilih File .SQL atau .ZIP dari Komputer</label>
                        <input
                            type="file"
                            accept=".sql,.zip"
                            @change="handleUploadFile"
                            class="w-full text-xs text-slate-600 dark:text-slate-300 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer border border-slate-200 dark:border-slate-700 rounded-2xl p-1.5"
                        />
                    </div>

                    <p class="text-[11px] text-slate-400 leading-relaxed">
                        Format didukung: <strong>.sql</strong> (Database MySQL) atau <strong>.zip</strong> (Media, Foto &amp; Logo Uploads). Maksimal 100MB.
                    </p>
                </div>

                <div class="flex items-center gap-2.5 pt-2">
                    <button
                        type="button"
                        @click="showUploadModal = false"
                        :disabled="isRestoring"
                        class="flex-1 py-2.5 rounded-2xl border border-slate-300 dark:border-slate-600 hover:bg-slate-50 dark:hover:bg-slate-700 text-xs font-bold text-slate-700 dark:text-slate-200 transition cursor-pointer"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        @click="submitUploadRestore"
                        :disabled="isRestoring || (!uploadForm.file_backup && !uploadForm.file_sql)"
                        class="flex-1 py-2.5 rounded-2xl bg-blue-600 hover:bg-blue-700 text-xs font-bold text-white transition flex items-center justify-center gap-2 cursor-pointer shadow-md shadow-blue-600/20 disabled:opacity-50"
                    >
                        <i v-if="isRestoring" class="fa-solid fa-spinner fa-spin"></i>
                        <span>{{ isRestoring ? 'Memproses...' : 'Upload & Restore' }}</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Uniform Confirmation Modal -->
        <ConfirmationModal
            :show="confirmModal.show"
            :title="confirmModal.title"
            :message="confirmModal.message"
            :confirm-text="confirmModal.confirmText"
            :type="confirmModal.type"
            :loading="confirmModal.loading"
            @confirm="confirmModal.action && confirmModal.action()"
            @cancel="confirmModal.show = false"
        />
    </AppLayout>
</template>
