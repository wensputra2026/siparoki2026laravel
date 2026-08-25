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

const selected = ref({});
const selectedCount = computed(() => Object.values(selected.value).filter(Boolean).length);
const selectedPaths = computed(() => props.orphans.filter(o => selected.value[o.path]).map(o => o.path));

const toggleAll = (event) => {
    const checked = event.target.checked;
    const next = {};
    props.orphans.forEach(o => { next[o.path] = checked; });
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

        <div class="px-4 sm:px-6 py-6 max-w-6xl mx-auto space-y-6">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-rose-500 to-orange-500 flex items-center justify-center text-white shadow-lg">
                    <i class="fa-solid fa-broom"></i>
                </div>
                <div>
                    <h1 class="text-lg font-extrabold text-slate-900 dark:text-white">Pembersih Sistem</h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Bersihkan cache dan hapus file unggahan yang tidak terpakai (orphan).</p>
                </div>
            </div>

            <!-- Cache & Storage -->
            <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 border border-slate-200/80 dark:border-slate-700/80 shadow-xs space-y-5">
                <div>
                    <h3 class="font-extrabold text-slate-900 dark:text-white text-base">Cache &amp; Penyimpanan</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Hapus cache aplikasi untuk memastikan perubahan tampilan &amp; data terbaru langsung tampil.</p>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <div class="rounded-2xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 p-3 text-center">
                        <p class="text-[10px] uppercase font-bold text-slate-400">Cache</p>
                        <p class="text-sm font-bold text-slate-800 dark:text-white">{{ formatBytes(cacheInfo.cache) }}</p>
                    </div>
                    <div class="rounded-2xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 p-3 text-center">
                        <p class="text-[10px] uppercase font-bold text-slate-400">Views</p>
                        <p class="text-sm font-bold text-slate-800 dark:text-white">{{ formatBytes(cacheInfo.views) }}</p>
                    </div>
                    <div class="rounded-2xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 p-3 text-center">
                        <p class="text-[10px] uppercase font-bold text-slate-400">Sessions</p>
                        <p class="text-sm font-bold text-slate-800 dark:text-white">{{ formatBytes(cacheInfo.sessions) }}</p>
                    </div>
                    <div class="rounded-2xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 p-3 text-center">
                        <p class="text-[10px] uppercase font-bold text-slate-400">Logs</p>
                        <p class="text-sm font-bold text-slate-800 dark:text-white">{{ formatBytes(cacheInfo.logs) }}</p>
                    </div>
                </div>

                <div class="flex justify-end pt-2 border-t border-slate-100 dark:border-slate-700">
                    <button @click="clearingCache" :disabled="clearing"
                        class="px-6 py-2.5 rounded-2xl bg-rose-600 hover:bg-rose-700 disabled:opacity-50 text-white font-bold text-xs shadow-lg shadow-rose-600/30 transition cursor-pointer">
                        <i class="fa-solid fa-broom me-1"></i> {{ clearing ? 'Membersihkan...' : 'Bersihkan Cache' }}
                    </button>
                </div>
            </div>

            <!-- Orphan Uploads -->
            <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 border border-slate-200/80 dark:border-slate-700/80 shadow-xs space-y-5">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                    <div>
                        <h3 class="font-extrabold text-slate-900 dark:text-white text-base">Pemindai File Yatim (Orphan)</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                            File di <code class="text-[11px]">public/uploads</code> yang tidak dirujuk oleh data manapun di database.
                        </p>
                    </div>
                    <div class="text-right text-xs">
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
                    <div class="overflow-x-auto custom-scrollbar rounded-2xl border border-slate-200 dark:border-slate-700">
                        <table class="w-full text-xs">
                            <thead class="bg-slate-50 dark:bg-slate-900/60 text-slate-500 dark:text-slate-400">
                                <tr>
                                    <th class="w-10 px-3 py-2 text-left">
                                        <input type="checkbox" :checked="selectedCount === orphans.length && orphans.length > 0" @change="toggleAll" class="w-4 h-4 rounded text-rose-600" />
                                    </th>
                                    <th class="px-3 py-2 text-left font-bold">Path File</th>
                                    <th class="px-3 py-2 text-left font-bold w-28">Ukuran</th>
                                    <th class="px-3 py-2 text-left font-bold w-40">Terakhir Diubah</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="o in orphans" :key="o.path" class="border-t border-slate-100 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-900/40">
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

                    <div class="flex items-center justify-between gap-3">
                        <span class="text-xs text-slate-500 dark:text-slate-400">{{ selectedCount }} file dipilih</span>
                        <button @click="deleteSelected" :disabled="selectedCount === 0 || deleting"
                            class="px-6 py-2.5 rounded-2xl bg-orange-600 hover:bg-orange-700 disabled:opacity-50 text-white font-bold text-xs shadow-lg shadow-orange-600/30 transition cursor-pointer">
                            <i class="fa-solid fa-trash-can me-1"></i> {{ deleting ? 'Menghapus...' : 'Hapus Terpilih' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
