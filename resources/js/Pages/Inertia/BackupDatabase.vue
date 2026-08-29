<script setup>
import { ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    role: { type: String, default: 'Super Admin' },
    prefix: { type: String, default: 'superadmin' },
    backups: { type: Array, default: () => [] },
    databaseName: { type: String, default: '' },
    tableCount: { type: Number, default: 0 },
    dbSizeMb: { type: Number, default: 0 },
    totalRows: { type: Number, default: 0 },
    lastBackupAt: { type: String, default: null },
});

const isBackingUp = ref(false);
const isRestoring = ref(false);
const selectedBackup = ref(null);
const showRestoreModal = ref(false);
const showUploadModal = ref(false);

const uploadForm = useForm({
    file_sql: null,
});

const formatBytes = (bytes, decimals = 2) => {
    if (!+bytes) return '0 Bytes';
    const k = 1024;
    const dm = decimals < 0 ? 0 : decimals;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return `${parseFloat((bytes / Math.pow(k, i)).toFixed(dm))} ${sizes[i]}`;
};

const handleGenerateBackup = () => {
    if (confirm('Apakah Anda yakin ingin mem-backup seluruh database sekarang?')) {
        isBackingUp.value = true;
        router.post(`/${props.prefix}/backup-database/generate`, {}, {
            preserveScroll: true,
            onFinish: () => {
                isBackingUp.value = false;
            }
        });
    }
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
    uploadForm.file_sql = e.target.files[0];
};

const submitUploadRestore = () => {
    if (!uploadForm.file_sql) {
        alert('Silakan pilih file .sql terlebih dahulu.');
        return;
    }
    if (confirm('PERINGATAN: Memulihkan database dari file SQL eksternal akan menimpa data yang ada. Lanjutkan?')) {
        isRestoring.value = true;
        uploadForm.post(`/${props.prefix}/backup-database/upload-restore`, {
            preserveScroll: true,
            onSuccess: () => {
                showUploadModal.value = false;
                uploadForm.reset();
            },
            onFinish: () => {
                isRestoring.value = false;
            }
        });
    }
};

const handleDeleteBackup = (backup) => {
    if (confirm(`Apakah Anda yakin ingin menghapus file backup "${backup.nama_file}"?`)) {
        router.delete(`/${props.prefix}/backup-database/${backup.id}`, {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <AppLayout :fullWidth="true">
        <Head title="Backup & Restore Database - SIPAROKI" />

        <div class="w-full space-y-6 pb-12">
            <!-- 1. EXECUTIVE HEADER BANNER -->
            <div class="bg-gradient-to-r from-blue-900 via-indigo-900 to-slate-900 rounded-3xl p-6 sm:p-8 text-white shadow-xl shadow-blue-900/15 relative overflow-hidden">
                <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
                <div class="absolute right-20 top-0 w-32 h-32 bg-blue-400/20 rounded-full blur-xl pointer-events-none"></div>

                <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div class="space-y-2">
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/15 backdrop-blur-md text-blue-100 text-xs font-semibold border border-white/20">
                            <i class="fa-solid fa-database"></i>
                            <span>Sistem Pemeliharaan &amp; Cadangan Database</span>
                        </div>
                        <h1 class="text-2xl sm:text-3xl font-black tracking-tight flex items-center gap-3">
                            Backup &amp; Restore Database
                            <span class="text-xs font-bold px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/40">
                                Online
                            </span>
                        </h1>
                        <p class="text-blue-100 text-xs sm:text-sm max-w-2xl leading-relaxed">
                            Cadangkan dan pulihkan seluruh data sensus umat, sakramen gereja, pembukuan kas paroki, dan aset digital secara aman dan otomatis.
                        </p>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center gap-2.5 flex-wrap shrink-0">
                        <button
                            type="button"
                            @click="showUploadModal = true"
                            class="px-4 py-2.5 rounded-2xl bg-white/20 hover:bg-white/30 backdrop-blur-md text-white text-xs font-bold border border-white/30 shadow-sm transition flex items-center gap-2 cursor-pointer"
                        >
                            <i class="fa-solid fa-upload"></i>
                            <span>Upload File .SQL</span>
                        </button>
                        <button
                            type="button"
                            @click="handleGenerateBackup"
                            :disabled="isBackingUp"
                            class="px-5 py-2.5 rounded-2xl bg-white hover:bg-blue-50 text-blue-950 text-xs font-black shadow-lg shadow-black/5 transition flex items-center gap-2 cursor-pointer disabled:opacity-50"
                        >
                            <i v-if="isBackingUp" class="fa-solid fa-spinner fa-spin text-blue-600"></i>
                            <i v-else class="fa-solid fa-wand-magic-sparkles text-blue-600"></i>
                            <span>{{ isBackingUp ? 'Memproses Backup...' : 'Backup Database Sekarang' }}</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- 2. DATABASE METRICS CARDS -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Card 1: Nama Database -->
                <div class="bg-white dark:bg-slate-800 rounded-3xl p-5 border border-slate-200/80 dark:border-slate-700/80 shadow-xs hover:shadow-md transition flex items-center gap-3.5">
                    <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg shrink-0 shadow-2xs">
                        <i class="fa-solid fa-server"></i>
                    </div>
                    <div class="min-w-0">
                        <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Database MySQL</div>
                        <div class="text-sm font-black text-slate-800 dark:text-white truncate font-mono">{{ databaseName || 'parokibenlutu' }}</div>
                    </div>
                </div>

                <!-- Card 2: Total Tabel -->
                <div class="bg-white dark:bg-slate-800 rounded-3xl p-5 border border-slate-200/80 dark:border-slate-700/80 shadow-xs hover:shadow-md transition flex items-center gap-3.5">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg shrink-0 shadow-2xs">
                        <i class="fa-solid fa-table-cells"></i>
                    </div>
                    <div>
                        <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Tabel</div>
                        <div class="text-sm font-black text-slate-800 dark:text-white">{{ tableCount }} Tabel Data</div>
                    </div>
                </div>

                <!-- Card 3: Ukuran Database -->
                <div class="bg-white dark:bg-slate-800 rounded-3xl p-5 border border-slate-200/80 dark:border-slate-700/80 shadow-xs hover:shadow-md transition flex items-center gap-3.5">
                    <div class="w-12 h-12 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center text-lg shrink-0 shadow-2xs">
                        <i class="fa-solid fa-hard-drive"></i>
                    </div>
                    <div>
                        <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Ukuran Database</div>
                        <div class="text-sm font-black text-slate-800 dark:text-white">{{ dbSizeMb }} MB</div>
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
                    </div>
                </div>
            </div>

            <!-- 3. BACKUP HISTORY TABLE -->
            <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200/80 dark:border-slate-700/80 shadow-xs overflow-hidden">
                <div class="p-5 sm:p-6 border-b border-slate-100 dark:border-slate-700/80 flex items-center justify-between">
                    <div>
                        <h2 class="text-sm font-black text-slate-900 dark:text-white flex items-center gap-2">
                            <i class="fa-solid fa-clock-rotate-left text-blue-600"></i>
                            <span>Daftar Arsip File Backup Database</span>
                        </h2>
                        <p class="text-xs text-slate-400 mt-0.5">Semua file tersimpan aman di direktori server dan siap di-download atau di-restore sewaktu-waktu.</p>
                    </div>
                    <span class="px-3 py-1 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 text-xs font-bold">
                        {{ backups.length }} File Tersedia
                    </span>
                </div>

                <!-- Table Content -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="bg-slate-50/80 border-b border-slate-200/80 text-slate-600 font-bold">
                                <th class="py-3 px-4 w-12 text-center">#</th>
                                <th class="py-3 px-4">Nama File SQL</th>
                                <th class="py-3 px-4">Ukuran</th>
                                <th class="py-3 px-4">Waktu Pembuatan</th>
                                <th class="py-3 px-4">Dibuat Oleh</th>
                                <th class="py-3 px-4 text-center w-48">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            <tr v-if="backups.length === 0">
                                <td colspan="6" class="py-12 text-center text-slate-400">
                                    <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-2 text-xl">
                                        <i class="fa-solid fa-box-open"></i>
                                    </div>
                                    <p class="font-bold text-sm text-slate-600">Belum Ada File Backup</p>
                                    <p class="text-xs text-slate-400 mt-0.5">Klik tombol "Backup Database Sekarang" di atas untuk membuat cadangan data pertama.</p>
                                </td>
                            </tr>
                            <tr
                                v-for="(b, idx) in backups"
                                :key="b.id"
                                class="hover:bg-slate-50/80 transition"
                            >
                                <td class="py-3 px-4 text-center font-bold text-slate-400">{{ idx + 1 }}</td>
                                <td class="py-3 px-4 font-mono font-bold text-slate-900 flex items-center gap-2">
                                    <i class="fa-solid fa-file-code text-blue-600 text-sm"></i>
                                    <span>{{ b.nama_file }}</span>
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
                                            title="Download File SQL"
                                        >
                                            <i class="fa-solid fa-download text-xs"></i>
                                        </a>

                                        <!-- Restore Button -->
                                        <button
                                            type="button"
                                            @click="handleConfirmRestore(b)"
                                            class="w-8 h-8 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-600 flex items-center justify-center transition cursor-pointer"
                                            title="Pulihkan (Restore) Database dari File Ini"
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
                    <h3 class="text-base font-black text-slate-900 dark:text-white">Konfirmasi Pemulihan Database</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                        Anda akan memulihkan database dari file:
                        <strong class="font-mono text-slate-800 dark:text-slate-200 block mt-1">{{ selectedBackup?.nama_file }}</strong>
                    </p>
                    <div class="mt-3 p-3.5 rounded-2xl bg-amber-50/80 dark:bg-amber-950/40 border border-amber-200/60 dark:border-amber-900/60 text-left text-[11px] text-amber-800 dark:text-amber-300 space-y-1">
                        <p class="font-bold flex items-center gap-1.5">
                            <i class="fa-solid fa-circle-exclamation text-amber-600 dark:text-amber-400"></i>
                            Peringatan Penting:
                        </p>
                        <p>Proses ini akan menimpa data yang saat ini ada di database dengan data yang tersimpan pada file backup tersebut. Pastikan Anda sudah membuat backup terbaru sebelum melanjutkan.</p>
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

        <!-- Modal Upload & Restore SQL File -->
        <div
            v-if="showUploadModal"
            class="fixed inset-0 z-50 bg-slate-950/70 backdrop-blur-sm flex items-center justify-center p-4"
        >
            <div class="bg-white dark:bg-slate-800 rounded-3xl max-w-md w-full p-6 sm:p-8 shadow-2xl space-y-4 border border-slate-200/80 dark:border-slate-700/80">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-700 pb-3">
                    <h3 class="text-sm font-black text-slate-900 dark:text-white flex items-center gap-2">
                        <i class="fa-solid fa-upload text-blue-600"></i>
                        <span>Upload &amp; Restore File SQL</span>
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
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1.5">Pilih File .SQL dari Komputer</label>
                        <input
                            type="file"
                            accept=".sql"
                            @change="handleUploadFile"
                            class="w-full text-xs text-slate-600 dark:text-slate-300 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer border border-slate-200 dark:border-slate-700 rounded-2xl p-1.5"
                        />
                    </div>

                    <p class="text-[11px] text-slate-400">
                        Format yang didukung: <strong>.sql</strong> (Maksimal 50MB). File akan langsung dieksekusi untuk merestore database.
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
                        :disabled="isRestoring || !uploadForm.file_sql"
                        class="flex-1 py-2.5 rounded-2xl bg-blue-600 hover:bg-blue-700 text-xs font-bold text-white transition flex items-center justify-center gap-2 cursor-pointer shadow-md shadow-blue-600/20 disabled:opacity-50"
                    >
                        <i v-if="isRestoring" class="fa-solid fa-spinner fa-spin"></i>
                        <span>{{ isRestoring ? 'Memproses...' : 'Upload & Restore' }}</span>
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
