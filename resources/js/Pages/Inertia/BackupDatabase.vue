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
    <AppLayout title="Backup & Restore Database" :fullWidth="true">
        <Head title="Backup & Restore Database - SIPAROKI" />

        <div class="w-full space-y-5 pb-8">
            <!-- Header Banner -->
            <div class="rounded-2xl bg-white border border-slate-200/80 p-5 sm:p-6 shadow-2xs">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="flex items-center gap-3.5">
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-blue-600 to-indigo-700 text-white flex items-center justify-center shadow-md shadow-blue-500/20 shrink-0">
                            <i class="fa-solid fa-database text-xl"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h1 class="text-xl font-black text-slate-900 tracking-tight">Backup & Restore Database</h1>
                                <span class="px-2 py-0.5 text-[11px] font-bold rounded-md bg-emerald-50 text-emerald-700 border border-emerald-200/60">
                                    <i class="fa-solid fa-circle-check text-[9px] mr-1"></i>Online
                                </span>
                            </div>
                            <p class="text-xs text-slate-500 mt-0.5">
                                Cadangkan dan pulihkan seluruh data umat, sakramen, keuangan, dan aset Paroki St. Vinsensius a Paulo Benlutu secara aman.
                            </p>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex flex-wrap items-center gap-2.5">
                        <button
                            type="button"
                            @click="showUploadModal = true"
                            class="px-4 py-2.5 rounded-xl border border-slate-300 hover:bg-slate-50 text-slate-700 text-xs font-bold transition flex items-center gap-2 cursor-pointer shadow-2xs"
                        >
                            <i class="fa-solid fa-upload text-slate-500"></i>
                            <span>Upload File .SQL</span>
                        </button>
                        <button
                            type="button"
                            @click="handleGenerateBackup"
                            :disabled="isBackingUp"
                            class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white text-xs font-bold transition flex items-center gap-2 cursor-pointer shadow-sm shadow-blue-500/25 disabled:opacity-50"
                        >
                            <i v-if="isBackingUp" class="fa-solid fa-spinner fa-spin"></i>
                            <i v-else class="fa-solid fa-wand-magic-sparkles"></i>
                            <span>{{ isBackingUp ? 'Memproses Backup...' : 'Backup Database Sekarang' }}</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Database Metrics Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Card 1: Nama Database -->
                <div class="rounded-2xl bg-white border border-slate-200/80 p-4 shadow-2xs flex items-center gap-3.5">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-server text-base"></i>
                    </div>
                    <div class="min-w-0">
                        <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Database MySQL</div>
                        <div class="text-sm font-black text-slate-800 truncate font-mono">{{ databaseName || 'parokibenlutu' }}</div>
                    </div>
                </div>

                <!-- Card 2: Total Tabel -->
                <div class="rounded-2xl bg-white border border-slate-200/80 p-4 shadow-2xs flex items-center gap-3.5">
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-table-cells text-base"></i>
                    </div>
                    <div>
                        <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Tabel</div>
                        <div class="text-sm font-black text-slate-800">{{ tableCount }} Tabel Data</div>
                    </div>
                </div>

                <!-- Card 3: Ukuran Database -->
                <div class="rounded-2xl bg-white border border-slate-200/80 p-4 shadow-2xs flex items-center gap-3.5">
                    <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-hard-drive text-base"></i>
                    </div>
                    <div>
                        <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Ukuran Database</div>
                        <div class="text-sm font-black text-slate-800">{{ dbSizeMb }} MB</div>
                    </div>
                </div>

                <!-- Card 4: Backup Terakhir -->
                <div class="rounded-2xl bg-white border border-slate-200/80 p-4 shadow-2xs flex items-center gap-3.5">
                    <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-clock-rotate-left text-base"></i>
                    </div>
                    <div class="min-w-0">
                        <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Backup Terakhir</div>
                        <div class="text-xs font-black text-slate-800 truncate">
                            {{ lastBackupAt ? new Date(lastBackupAt).toLocaleString('id-ID') : 'Belum Ada' }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Backup History Table -->
            <div class="rounded-2xl bg-white border border-slate-200/80 overflow-hidden shadow-2xs">
                <div class="p-4 sm:p-5 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h2 class="text-sm font-black text-slate-900 flex items-center gap-2">
                            <i class="fa-solid fa-clock-rotate-left text-blue-600"></i>
                            <span>Daftar Arsip File Backup Database</span>
                        </h2>
                        <p class="text-xs text-slate-400 mt-0.5">Semua file tersimpan aman di direktori server dan siap di-download atau di-restore sewaktu-waktu.</p>
                    </div>
                    <span class="px-2.5 py-1 rounded-full bg-slate-100 text-slate-600 text-xs font-bold">
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
            class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
        >
            <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4 border border-slate-200">
                <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl mx-auto">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <div class="text-center">
                    <h3 class="text-base font-black text-slate-900">Konfirmasi Pemulihan Database</h3>
                    <p class="text-xs text-slate-500 mt-1">
                        Anda akan memulihkan database dari file:
                        <strong class="font-mono text-slate-800 block mt-1">{{ selectedBackup?.nama_file }}</strong>
                    </p>
                    <div class="mt-3 p-3 rounded-xl bg-amber-50/80 border border-amber-200/60 text-left text-[11px] text-amber-800 space-y-1">
                        <p class="font-bold flex items-center gap-1.5">
                            <i class="fa-solid fa-circle-exclamation text-amber-600"></i>
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
                        class="flex-1 py-2.5 rounded-xl border border-slate-300 hover:bg-slate-50 text-xs font-bold text-slate-700 transition cursor-pointer"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        @click="executeRestore"
                        :disabled="isRestoring"
                        class="flex-1 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-xs font-bold text-white transition flex items-center justify-center gap-2 cursor-pointer shadow-sm disabled:opacity-50"
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
            class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
        >
            <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4 border border-slate-200">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="text-sm font-black text-slate-900 flex items-center gap-2">
                        <i class="fa-solid fa-upload text-blue-600"></i>
                        <span>Upload & Restore File SQL</span>
                    </h3>
                    <button
                        type="button"
                        @click="showUploadModal = false"
                        class="w-7 h-7 rounded-lg text-slate-400 hover:bg-slate-100 flex items-center justify-center"
                    >
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Pilih File .SQL dari Komputer</label>
                        <input
                            type="file"
                            accept=".sql"
                            @change="handleUploadFile"
                            class="w-full text-xs text-slate-600 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer border border-slate-200 rounded-xl p-1"
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
                        class="flex-1 py-2.5 rounded-xl border border-slate-300 hover:bg-slate-50 text-xs font-bold text-slate-700 transition cursor-pointer"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        @click="submitUploadRestore"
                        :disabled="isRestoring || !uploadForm.file_sql"
                        class="flex-1 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-xs font-bold text-white transition flex items-center justify-center gap-2 cursor-pointer shadow-sm disabled:opacity-50"
                    >
                        <i v-if="isRestoring" class="fa-solid fa-spinner fa-spin"></i>
                        <span>{{ isRestoring ? 'Memproses...' : 'Upload & Restore' }}</span>
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
