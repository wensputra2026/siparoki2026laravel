<script setup>
import { ref, computed } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    role: { type: String, default: 'Super Admin' },
    prefix: { type: String, default: 'superadmin' },
    orphans: { type: Array, default: () => [] },
    totalFiles: { type: Number, default: 0 },
    totalSize: { type: Number, default: 0 },
    scanned: { type: Boolean, default: false },
    cacheInfo: { type: Object, default: () => ({ cache: 0, views: 0, sessions: 0, logs: 0 }) },
});

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
    if (!q) return props.orphans;
    return props.orphans.filter((o) => String(o.path || '').toLowerCase().includes(q));
});
const visibleOrphans = computed(() => filteredOrphans.value.slice(0, visibleLimit.value));
const hiddenOrphansCount = computed(() => Math.max(filteredOrphans.value.length - visibleOrphans.value.length, 0));
const selectedCount = computed(() => Object.values(selected.value).filter(Boolean).length);
const selectedPaths = computed(() => props.orphans.filter(o => selected.value[o.path]).map(o => o.path));

const toggleAll = (event) => {
    const checked = event.target.checked;
    const next = {};
    visibleOrphans.value.forEach(o => { next[o.path] = checked; });
    selected.value = next;
};

const clearing = ref(false);
const clearingCache = () => {
    if (confirm('Bersihkan semua cache Laravel (config, route, view, cache, optimize)?')) {
        clearing.value = true;
        router.post(`/${props.prefix}/pembersih-sistem/aksi`, { action: 'clear_cache' }, {
            preserveScroll: true,
            onFinish: () => { clearing.value = false; },
        });
    }
};

const deleteForm = useForm({ action: 'delete_orphans', paths: [] });
const deleting = ref(false);
const deleteSelected = () => {
    if (selectedCount.value === 0) return;
    if (confirm(`Hapus ${selectedCount.value} file yatim yang terpilih? Tindakan ini tidak dapat dibatalkan.`)) {
        deleting.value = true;
        deleteForm.paths = selectedPaths.value;
        deleteForm.post(`/${props.prefix}/pembersih-sistem/aksi`, {
            preserveScroll: true,
            onSuccess: () => { selected.value = {}; },
            onFinish: () => { deleting.value = false; },
        });
    }
};
</script>

<template>
    <AppLayout>
        <Head title="Pembersih Sistem" />

        <div class="px-4 sm:px-6 py-6 max-w-6xl mx-auto space-y-5 pb-24">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-rose-500 to-orange-500 flex items-center justify-center text-white shadow-lg">
                    <i class="fa-solid fa-broom"></i>
                </div>
                <div>
                    <h1 class="text-lg font-extrabold text-slate-900 dark:text-white">Pembersih Sistem</h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Bersihkan cache dan hapus file unggahan yang tidak terpakai (orphan).</p>
                </div>
                </div>
                <div class="grid grid-cols-3 gap-2 text-center w-full sm:w-auto">
                    <div class="rounded-lg border border-slate-200 bg-white px-3 py-2">
                        <p class="text-[10px] uppercase font-bold text-slate-400">Total</p>
                        <p class="text-sm font-black text-slate-900">{{ totalFiles }}</p>
                    </div>
                    <div class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2">
                        <p class="text-[10px] uppercase font-bold text-rose-400">Yatim</p>
                        <p class="text-sm font-black text-rose-700">{{ orphans.length }}</p>
                    </div>
                    <div class="rounded-lg border border-slate-200 bg-white px-3 py-2">
                        <p class="text-[10px] uppercase font-bold text-slate-400">Ukuran</p>
                        <p class="text-sm font-black text-slate-900">{{ formatBytes(totalSize) }}</p>
                    </div>
                </div>
            </div>

            <!-- Cache & Storage -->
            <div class="bg-white dark:bg-slate-800 rounded-xl p-5 border border-slate-200/80 dark:border-slate-700/80 shadow-xs space-y-4">
                <div>
                    <h3 class="font-extrabold text-slate-900 dark:text-white text-base">Cache &amp; Penyimpanan</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Hapus cache aplikasi untuk memastikan perubahan tampilan &amp; data terbaru langsung tampil.</p>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <div class="rounded-lg bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 p-3 text-center">
                        <p class="text-[10px] uppercase font-bold text-slate-400">Cache</p>
                        <p class="text-sm font-bold text-slate-800 dark:text-white">{{ formatBytes(cacheInfo.cache) }}</p>
                    </div>
                    <div class="rounded-lg bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 p-3 text-center">
                        <p class="text-[10px] uppercase font-bold text-slate-400">Views</p>
                        <p class="text-sm font-bold text-slate-800 dark:text-white">{{ formatBytes(cacheInfo.views) }}</p>
                    </div>
                    <div class="rounded-lg bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 p-3 text-center">
                        <p class="text-[10px] uppercase font-bold text-slate-400">Sessions</p>
                        <p class="text-sm font-bold text-slate-800 dark:text-white">{{ formatBytes(cacheInfo.sessions) }}</p>
                    </div>
                    <div class="rounded-lg bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 p-3 text-center">
                        <p class="text-[10px] uppercase font-bold text-slate-400">Logs</p>
                        <p class="text-sm font-bold text-slate-800 dark:text-white">{{ formatBytes(cacheInfo.logs) }}</p>
                    </div>
                </div>

                <div class="flex justify-end pt-2 border-t border-slate-100 dark:border-slate-700">
                    <button @click="clearingCache" :disabled="clearing"
                        class="px-5 py-2.5 rounded-lg bg-rose-600 hover:bg-rose-700 disabled:opacity-50 text-white font-bold text-xs shadow-lg shadow-rose-600/30 transition cursor-pointer">
                        <i class="fa-solid fa-broom me-1"></i> {{ clearing ? 'Membersihkan...' : 'Bersihkan Cache' }}
                    </button>
                </div>
            </div>

            <!-- Orphan Uploads -->
            <div class="bg-white dark:bg-slate-800 rounded-xl p-5 border border-slate-200/80 dark:border-slate-700/80 shadow-xs space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                    <div>
                        <h3 class="font-extrabold text-slate-900 dark:text-white text-base">Pemindai File Yatim (Orphan)</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                            File di <code class="text-[11px]">public/uploads</code> yang tidak dirujuk oleh data manapun di database.
                        </p>
                    </div>
                    <div class="text-left sm:text-right text-xs shrink-0">
                        <p class="text-slate-500 dark:text-slate-400">Total file: <b class="text-slate-800 dark:text-white">{{ totalFiles }}</b> ({{ formatBytes(totalSize) }})</p>
                        <p class="text-rose-600 dark:text-rose-400 font-bold">File yatim: {{ orphans.length }}</p>
                    </div>
                </div>

                <div v-if="!scanned" class="text-center text-xs text-slate-500 py-6">
                    Direktori <code>public/uploads</code> tidak ditemukan.
                </div>

                <div v-else-if="orphans.length === 0" class="text-center text-xs text-emerald-600 dark:text-emerald-400 py-6">
                    <i class="fa-solid fa-circle-check me-1"></i> Tidak ada file yatim. Unggahan sudah rapi!
                </div>

                <div v-else class="space-y-3">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3 rounded-lg bg-slate-50 border border-slate-200 p-3">
                        <div class="relative w-full lg:max-w-md">
                            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                            <input
                                v-model="orphanSearch"
                                type="text"
                                placeholder="Cari path file yatim..."
                                class="w-full pl-8 pr-3 py-2 rounded-lg bg-white border border-slate-200 text-xs text-slate-800 focus:outline-none focus:border-rose-500"
                            />
                        </div>
                        <p class="text-[11px] text-slate-500">
                            Menampilkan <b class="text-slate-900">{{ visibleOrphans.length }}</b> dari <b class="text-slate-900">{{ filteredOrphans.length }}</b> file sesuai filter.
                        </p>
                    </div>

                    <div class="overflow-auto custom-scrollbar rounded-xl border border-slate-200 dark:border-slate-700 max-h-[52vh]">
                        <table class="w-full text-xs">
                            <thead class="bg-slate-50 dark:bg-slate-900/60 text-slate-500 dark:text-slate-400 sticky top-0 z-10">
                                <tr>
                                    <th class="w-10 px-3 py-2 text-left">
                                        <input type="checkbox" :checked="selectedCount === visibleOrphans.length && visibleOrphans.length > 0" @change="toggleAll" class="w-4 h-4 rounded text-rose-600" />
                                    </th>
                                    <th class="px-3 py-2 text-left font-bold">Path File</th>
                                    <th class="px-3 py-2 text-left font-bold w-28">Ukuran</th>
                                    <th class="px-3 py-2 text-left font-bold w-40">Terakhir Diubah</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="o in visibleOrphans" :key="o.path" class="border-t border-slate-100 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-900/40">
                                    <td class="px-3 py-2">
                                        <input type="checkbox" v-model="selected[o.path]" class="w-4 h-4 rounded text-rose-600" />
                                    </td>
                                    <td class="px-3 py-2 font-mono text-slate-700 dark:text-slate-200 break-all">{{ o.path }}</td>
                                    <td class="px-3 py-2 text-slate-600 dark:text-slate-300">{{ formatBytes(o.size) }}</td>
                                    <td class="px-3 py-2 text-slate-600 dark:text-slate-300">{{ formatDate(o.mtime) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-if="hiddenOrphansCount > 0" class="flex justify-center">
                        <button
                            type="button"
                            @click="visibleLimit += 150"
                            class="px-4 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition cursor-pointer"
                        >
                            Tampilkan 150 file lagi (tersisa {{ hiddenOrphansCount }})
                        </button>
                    </div>

                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <span class="text-xs text-slate-500 dark:text-slate-400">{{ selectedCount }} file dipilih</span>
                        <button @click="deleteSelected" :disabled="selectedCount === 0 || deleting"
                            class="px-5 py-2.5 rounded-lg bg-orange-600 hover:bg-orange-700 disabled:opacity-50 text-white font-bold text-xs shadow-lg shadow-orange-600/30 transition cursor-pointer">
                            <i class="fa-solid fa-trash-can me-1"></i> {{ deleting ? 'Menghapus...' : 'Hapus Terpilih' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
