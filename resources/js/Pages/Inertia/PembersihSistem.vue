<script setup>
import { ref, computed } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

import axios from 'axios';

const props = defineProps({
    role: { type: String, default: 'Super Admin' },
    prefix: { type: String, default: 'superadmin' },
    orphans: { type: Array, default: () => [] },
    totalFiles: { type: Number, default: 0 },
    totalSize: { type: Number, default: 0 },
    scanned: { type: Boolean, default: false },
    cacheInfo: { type: Object, default: () => ({ cache: 0, views: 0, sessions: 0, logs: 0 }) },
});

const localOrphans = ref([...props.orphans]);
const localTotalFiles = ref(props.totalFiles);
const localTotalSize = ref(props.totalSize);

const formatBytes = (bytes) => {
    if (!bytes || bytes <= 0) return '0 B';
    const units = ['B', 'KB', 'MB', 'GB', 'TB'];
    const i = Math.floor(Math.log(bytes) / Math.log(1024));
    return (bytes / Math.pow(1024, i)).toFixed(i === 0 ? 0 : 1) + ' ' + units[i];
};

const formatDate = (ts) => {
    if (!ts) return '-';
    return new Date(ts * 1000).toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' });
};

const orphanSearch = ref('');
const visibleLimit = ref(150);
const selected = ref({});
const filteredOrphans = computed(() => {
    const q = orphanSearch.value.trim().toLowerCase();
    if (!q) return localOrphans.value;
    return localOrphans.value.filter((o) => String(o.path || '').toLowerCase().includes(q));
});
const visibleOrphans = computed(() => filteredOrphans.value.slice(0, visibleLimit.value));
const hiddenOrphansCount = computed(() => Math.max(filteredOrphans.value.length - visibleOrphans.value.length, 0));
const selectedCount = computed(() => Object.values(selected.value).filter(Boolean).length);
const selectedPaths = computed(() => localOrphans.value.filter(o => selected.value[o.path]).map(o => o.path));

const toggleAll = (event) => {
    const checked = event.target.checked;
    const next = {};
    visibleOrphans.value.forEach(o => { next[o.path] = checked; });
    selected.value = next;
};

// Modern Confirmation Modal States
const showClearCacheModal = ref(false);
const showDeleteOrphansModal = ref(false);
const alertMessage = ref(null);

const clearing = ref(false);
const confirmClearCache = async () => {
    clearing.value = true;
    try {
        const res = await axios.post(`/${props.prefix}/pembersih-sistem/aksi`, { action: 'clear_cache' });
        showClearCacheModal.value = false;
        alertMessage.value = res.data.message || 'Cache sistem berhasil dibersihkan.';
        setTimeout(() => { alertMessage.value = null; }, 4000);
    } catch (e) {
        showClearCacheModal.value = false;
        alertMessage.value = 'Gagal membersihkan cache: ' + (e.response?.data?.message || e.message);
    } finally {
        clearing.value = false;
    }
};

const deleting = ref(false);
const confirmDeleteOrphans = async () => {
    if (selectedCount.value === 0) return;
    deleting.value = true;
    const pathsToDelete = [...selectedPaths.value];
    
    // Close modal immediately
    showDeleteOrphansModal.value = false;

    try {
        const res = await axios.post(`/${props.prefix}/pembersih-sistem/aksi`, {
            action: 'delete_orphans',
            paths: pathsToDelete,
        });
        
        // Remove deleted items locally in 0ms
        const toDeleteSet = new Set(pathsToDelete);
        const deletedBytes = localOrphans.value
            .filter(o => toDeleteSet.has(o.path))
            .reduce((acc, o) => acc + (o.size || 0), 0);

        localOrphans.value = localOrphans.value.filter(o => !toDeleteSet.has(o.path));
        localTotalFiles.value = Math.max(0, localTotalFiles.value - pathsToDelete.length);
        localTotalSize.value = Math.max(0, localTotalSize.value - deletedBytes);
        selected.value = {};

        alertMessage.value = res.data.message || `${pathsToDelete.length} file yatim berhasil dihapus.`;
        setTimeout(() => { alertMessage.value = null; }, 4000);
    } catch (e) {
        alertMessage.value = 'Gagal menghapus file: ' + (e.response?.data?.message || e.message);
    } finally {
        deleting.value = false;
    }
};
</script>

<template>
    <AppLayout>
        <Head title="Pembersih Sistem" />

        <div class="px-4 sm:px-6 py-6 w-full space-y-5 pb-24">
            <!-- Toast Notification -->
            <transition
                enter-active-class="transition duration-200 ease-out"
                enter-from-class="transform -translate-y-2 opacity-0"
                enter-to-class="transform translate-y-0 opacity-100"
                leave-active-class="transition duration-150 ease-in"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div v-if="alertMessage" class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold flex items-center justify-between shadow-xs">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-circle-check text-emerald-600 text-sm"></i>
                        <span>{{ alertMessage }}</span>
                    </div>
                    <button type="button" @click="alertMessage = null" class="text-emerald-500 hover:text-emerald-700 p-1 cursor-pointer">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            </transition>

            <!-- Header Bar -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 shadow-xs">
                <div class="flex items-center gap-3.5">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-rose-500 via-orange-500 to-amber-500 flex items-center justify-center text-white shadow-lg shadow-rose-500/25 shrink-0">
                        <i class="fa-solid fa-broom text-xl"></i>
                    </div>
                    <div>
                        <h1 class="text-lg sm:text-xl font-black text-slate-900 dark:text-white leading-tight">Pembersih Sistem</h1>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Bersihkan cache aplikasi dan kelola file unggahan yatim (orphan) agar performa tetap prima.</p>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-2.5 text-center shrink-0">
                    <div class="rounded-xl border border-slate-200 bg-slate-50/70 px-4 py-2 shadow-2xs">
                        <p class="text-[10px] uppercase font-bold text-slate-400">Total File</p>
                        <p class="text-sm sm:text-base font-black text-slate-900">{{ totalFiles }}</p>
                    </div>
                    <div class="rounded-xl border border-rose-200 bg-rose-50/70 px-4 py-2 shadow-2xs">
                        <p class="text-[10px] uppercase font-bold text-rose-500">File Yatim</p>
                        <p class="text-sm sm:text-base font-black text-rose-700">{{ orphans.length }}</p>
                    </div>
                    <div class="rounded-xl border border-amber-200 bg-amber-50/70 px-4 py-2 shadow-2xs">
                        <p class="text-[10px] uppercase font-bold text-amber-600">Ukuran</p>
                        <p class="text-sm sm:text-base font-black text-amber-900">{{ formatBytes(totalSize) }}</p>
                    </div>
                </div>
            </div>

            <!-- Cache & Storage Card -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200/80 dark:border-slate-700/80 shadow-xs space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="font-extrabold text-slate-900 dark:text-white text-base">Cache &amp; Penyimpanan Sistem</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Hapus cache Laravel (config, route, view, cache, optimize) untuk memastikan seluruh data mutakhir langsung tampil.</p>
                    </div>
                    <span class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-bold">
                        <i class="fa-solid fa-server text-[11px]"></i> Status Aktif
                    </span>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <div class="rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 p-3.5 text-center shadow-2xs">
                        <p class="text-[10px] uppercase font-bold text-slate-400 mb-1">Application Cache</p>
                        <p class="text-base font-black text-slate-800 dark:text-white">{{ formatBytes(cacheInfo.cache) }}</p>
                    </div>
                    <div class="rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 p-3.5 text-center shadow-2xs">
                        <p class="text-[10px] uppercase font-bold text-slate-400 mb-1">Compiled Views</p>
                        <p class="text-base font-black text-slate-800 dark:text-white">{{ formatBytes(cacheInfo.views) }}</p>
                    </div>
                    <div class="rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 p-3.5 text-center shadow-2xs">
                        <p class="text-[10px] uppercase font-bold text-slate-400 mb-1">Active Sessions</p>
                        <p class="text-base font-black text-slate-800 dark:text-white">{{ formatBytes(cacheInfo.sessions) }}</p>
                    </div>
                    <div class="rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 p-3.5 text-center shadow-2xs">
                        <p class="text-[10px] uppercase font-bold text-slate-400 mb-1">System Logs</p>
                        <p class="text-base font-black text-slate-800 dark:text-white">{{ formatBytes(cacheInfo.logs) }}</p>
                    </div>
                </div>

                <div class="flex justify-end pt-3 border-t border-slate-100 dark:border-slate-700">
                    <button
                        type="button"
                        @click="showClearCacheModal = true"
                        :disabled="clearing"
                        class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-rose-600 to-rose-700 hover:from-rose-700 hover:to-rose-800 disabled:opacity-50 text-white font-bold text-xs shadow-md shadow-rose-600/25 transition cursor-pointer flex items-center gap-2"
                    >
                        <i class="fa-solid fa-broom"></i>
                        <span>{{ clearing ? 'Membersihkan...' : 'Bersihkan Cache Sistem' }}</span>
                    </button>
                </div>
            </div>

            <!-- Orphan Uploads Card -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200/80 dark:border-slate-700/80 shadow-xs space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                    <div>
                        <h3 class="font-extrabold text-slate-900 dark:text-white text-base">Pemindai File Yatim (Orphan)</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                            File di <code class="text-[11px] bg-slate-100 px-1 py-0.5 rounded border border-slate-200 font-mono">public/uploads</code> yang tidak lagi dirujuk oleh data manapun di database.
                        </p>
                    </div>
                    <div class="text-left sm:text-right text-xs shrink-0">
                        <p class="text-slate-500 dark:text-slate-400">Total file: <b class="text-slate-800 dark:text-white">{{ totalFiles }}</b> ({{ formatBytes(totalSize) }})</p>
                        <p class="text-rose-600 dark:text-rose-400 font-bold">File yatim: {{ orphans.length }}</p>
                    </div>
                </div>

                <div v-if="!scanned" class="text-center text-xs text-slate-500 py-10">
                    Direktori <code>public/uploads</code> tidak ditemukan.
                </div>

                <div v-else-if="orphans.length === 0" class="text-center text-xs text-emerald-600 dark:text-emerald-400 py-10 space-y-2">
                    <div class="w-12 h-12 rounded-full bg-emerald-50 text-emerald-600 mx-auto flex items-center justify-center text-xl">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                    <p class="font-bold text-sm">Tidak ada file yatim ditemukan.</p>
                    <p class="text-slate-400">Semua file unggahan tertata rapi dan terhubung ke database!</p>
                </div>

                <div v-else class="space-y-3">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3 rounded-xl bg-slate-50 border border-slate-200 p-3">
                        <div class="relative w-full lg:max-w-md">
                            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                            <input
                                v-model="orphanSearch"
                                type="text"
                                placeholder="Cari path file yatim..."
                                class="w-full pl-8.5 pr-3 py-2 rounded-xl bg-white border border-slate-200 text-xs text-slate-800 focus:outline-none focus:border-rose-500 focus:ring-1 focus:ring-rose-500 shadow-2xs"
                            />
                        </div>
                        <p class="text-[11px] text-slate-500">
                            Menampilkan <b class="text-slate-900">{{ visibleOrphans.length }}</b> dari <b class="text-slate-900">{{ filteredOrphans.length }}</b> file sesuai filter.
                        </p>
                    </div>

                    <div class="overflow-auto custom-scrollbar rounded-xl border border-slate-200 dark:border-slate-700 max-h-[52vh]">
                        <table class="w-full text-xs">
                            <thead class="bg-slate-50 dark:bg-slate-900/60 text-slate-600 dark:text-slate-400 sticky top-0 z-10 border-b border-slate-200">
                                <tr>
                                    <th class="w-10 px-3.5 py-2.5 text-left">
                                        <input
                                            type="checkbox"
                                            :checked="selectedCount === visibleOrphans.length && visibleOrphans.length > 0"
                                            @change="toggleAll"
                                            class="w-4 h-4 rounded text-rose-600 cursor-pointer"
                                        />
                                    </th>
                                    <th class="px-3.5 py-2.5 text-left font-bold">Path File</th>
                                    <th class="px-3.5 py-2.5 text-left font-bold w-32">Ukuran</th>
                                    <th class="px-3.5 py-2.5 text-left font-bold w-44">Terakhir Diubah</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="o in visibleOrphans" :key="o.path" class="hover:bg-rose-50/40 transition">
                                    <td class="px-3.5 py-2.5">
                                        <input type="checkbox" v-model="selected[o.path]" class="w-4 h-4 rounded text-rose-600 cursor-pointer" />
                                    </td>
                                    <td class="px-3.5 py-2.5 font-mono text-slate-700 dark:text-slate-200 break-all">{{ o.path }}</td>
                                    <td class="px-3.5 py-2.5 text-slate-600 dark:text-slate-300 font-semibold">{{ formatBytes(o.size) }}</td>
                                    <td class="px-3.5 py-2.5 text-slate-500 dark:text-slate-400">{{ formatDate(o.mtime) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-if="hiddenOrphansCount > 0" class="flex justify-center pt-2">
                        <button
                            type="button"
                            @click="visibleLimit += 150"
                            class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition cursor-pointer shadow-2xs"
                        >
                            Tampilkan 150 file lagi (tersisa {{ hiddenOrphansCount }})
                        </button>
                    </div>

                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pt-3 border-t border-slate-100 dark:border-slate-700">
                        <span class="text-xs font-bold text-slate-600 dark:text-slate-400">{{ selectedCount }} file dipilih</span>
                        <button
                            type="button"
                            @click="showDeleteOrphansModal = true"
                            :disabled="selectedCount === 0 || deleting"
                            class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-orange-600 to-orange-700 hover:from-orange-700 hover:to-orange-800 disabled:opacity-50 text-white font-bold text-xs shadow-md shadow-orange-600/25 transition cursor-pointer flex items-center gap-2"
                        >
                            <i class="fa-solid fa-trash-can"></i>
                            <span>{{ deleting ? 'Menghapus...' : 'Hapus File Terpilih' }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- 1. MODAL KONFIRMASI BERSIHKAN CACHE -->
        <transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="showClearCacheModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
                <div class="w-full max-w-md bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-slate-200 overflow-hidden transform transition-all">
                    <div class="p-6 text-center">
                        <div class="w-14 h-14 mx-auto rounded-full bg-rose-50 text-rose-600 flex items-center justify-center text-2xl mb-4 border border-rose-100 shadow-xs">
                            <i class="fa-solid fa-broom"></i>
                        </div>
                        <h3 class="text-base font-extrabold text-slate-900 dark:text-white mb-2">
                            Bersihkan Cache Sistem?
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                            Tindakan ini akan menghapus cache konfigurasi, rute, template compiled views, dan cache aplikasi. Semua perubahan kode dan data terbaru akan langsung direfresh dari database.
                        </p>
                    </div>
                    <div class="px-6 py-4 bg-slate-50/80 dark:bg-slate-900/60 border-t border-slate-100 dark:border-slate-700 flex items-center justify-end gap-2.5">
                        <button
                            type="button"
                            @click="showClearCacheModal = false"
                            class="px-4 py-2 rounded-xl bg-white border border-slate-200 text-slate-700 hover:bg-slate-100 text-xs font-bold transition cursor-pointer shadow-2xs"
                        >
                            Batal
                        </button>
                        <button
                            type="button"
                            @click="confirmClearCache"
                            :disabled="clearing"
                            class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 disabled:opacity-50 text-white text-xs font-bold transition cursor-pointer shadow-md shadow-rose-600/25 flex items-center gap-1.5"
                        >
                            <i v-if="clearing" class="fa-solid fa-circle-notch fa-spin text-xs"></i>
                            <span>{{ clearing ? 'Membersihkan...' : 'Ya, Bersihkan Cache' }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </transition>

        <!-- 2. MODAL KONFIRMASI HAPUS FILE YATIM -->
        <transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="showDeleteOrphansModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
                <div class="w-full max-w-md bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-slate-200 overflow-hidden transform transition-all">
                    <div class="p-6 text-center">
                        <div class="w-14 h-14 mx-auto rounded-full bg-orange-50 text-orange-600 flex items-center justify-center text-2xl mb-4 border border-orange-100 shadow-xs">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                        </div>
                        <h3 class="text-base font-extrabold text-slate-900 dark:text-white mb-2">
                            Hapus {{ selectedCount }} File Yatim?
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                            File terpilih akan dihapus permanen dari server penyimpanan <code class="font-mono text-[11px]">public/uploads</code>. Tindakan ini tidak dapat dibatalkan.
                        </p>
                    </div>
                    <div class="px-6 py-4 bg-slate-50/80 dark:bg-slate-900/60 border-t border-slate-100 dark:border-slate-700 flex items-center justify-end gap-2.5">
                        <button
                            type="button"
                            @click="showDeleteOrphansModal = false"
                            class="px-4 py-2 rounded-xl bg-white border border-slate-200 text-slate-700 hover:bg-slate-100 text-xs font-bold transition cursor-pointer shadow-2xs"
                        >
                            Batal
                        </button>
                        <button
                            type="button"
                            @click="confirmDeleteOrphans"
                            :disabled="deleting"
                            class="px-4 py-2 rounded-xl bg-orange-600 hover:bg-orange-700 disabled:opacity-50 text-white text-xs font-bold transition cursor-pointer shadow-md shadow-orange-600/25 flex items-center gap-1.5"
                        >
                            <i v-if="deleting" class="fa-solid fa-circle-notch fa-spin text-xs"></i>
                            <span>{{ deleting ? 'Menghapus...' : 'Ya, Hapus File' }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </transition>
    </AppLayout>
</template>
