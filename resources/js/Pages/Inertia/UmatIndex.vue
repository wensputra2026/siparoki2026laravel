<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    umats: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({ search: '' }),
    },
});

const page = usePage();
const basePrefix = computed(() => {
    const parts = page.url.split('?')[0].split('/').filter(Boolean);
    return parts.length > 0 ? '/' + parts[0] : '/superadmin';
});

const search = ref(props.filters.search || '');
let searchDebounceTimeout = null;

const isReloadingData = ref(false);
const reloadData = () => {
    isReloadingData.value = true;
    router.reload({
        preserveScroll: true,
        preserveState: false,
        onFinish: () => {
            setTimeout(() => {
                isReloadingData.value = false;
            }, 400);
        },
    });
};

watch(search, (val) => {
    clearTimeout(searchDebounceTimeout);
    searchDebounceTimeout = setTimeout(() => {
        router.get(
            `${basePrefix.value}/umat`,
            { search: val },
            {
                preserveState: true,
                preserveScroll: true,
                replace: true,
            }
        );
    }, 250);
});

const formatPaginationLabel = (label) => {
    if (!label) return '';
    const str = String(label).trim();
    if (str.toLowerCase().includes('prev') || str.toLowerCase().includes('pagination.previous')) {
        return '&laquo; Sebelum';
    }
    if (str.toLowerCase().includes('next') || str.toLowerCase().includes('pagination.next')) {
        return 'Sesudah &raquo;';
    }
    return str;
};
</script>

<template>
    <AppLayout title="Data Umat Paroki">
        <Head title="Data Umat - SIPAROKI" />

        <!-- Header Card -->
        <div class="rounded-2xl bg-white border border-slate-200/80 p-6 mb-6 shadow-xs">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-xl font-black text-slate-900">Eksplorasi Data Umat</h1>
                    <p class="text-xs text-slate-500">Pencarian data dan manajemen direktori umat paroki secara terintegrasi</p>
                </div>

                <!-- Instant Search + Reload -->
                <div class="flex items-center gap-2 w-full sm:w-auto">
                <!-- Instant Search Input -->
                <div class="relative w-full sm:w-80">
                    <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Ketik nama, NIK, tempat lahir..."
                        class="w-full pl-9 pr-4 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 placeholder-slate-400 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition"
                    />
                    <button
                        v-if="search"
                        @click="search = ''"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-700 text-xs"
                    >
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <!-- Reload Data Button -->
                <button
                    @click="reloadData"
                    :disabled="isReloadingData"
                    title="Muat ulang data dari database tanpa menyegarkan halaman"
                    class="px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 border border-slate-300 text-slate-700 text-xs font-bold transition-all flex items-center gap-1.5 cursor-pointer shrink-0 whitespace-nowrap disabled:opacity-60"
                >
                    <i class="fa-solid fa-rotate" :class="{ 'fa-spin': isReloadingData }"></i>
                    <span>{{ isReloadingData ? 'Memuat...' : 'Reload' }}</span>
                </button>
            </div>
        </div>
    </div>

        <!-- Table Container -->
        <div class="rounded-2xl bg-white border border-slate-200/80 overflow-hidden shadow-xs">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50/80 text-slate-500 uppercase tracking-wider text-[10px] font-bold border-b border-slate-200/80">
                        <tr>
                            <th class="px-5 py-3.5">Nama Lengkap</th>
                            <th class="px-5 py-3.5">NIK</th>
                            <th class="px-5 py-3.5">Jenis Kelamin</th>
                            <th class="px-5 py-3.5">Tempat, Tgl Lahir</th>
                            <th class="px-5 py-3.5">Status</th>
                            <th class="px-5 py-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        <tr
                            v-for="umat in umats.data"
                            :key="umat.id"
                            class="hover:bg-slate-50 transition-colors group"
                        >
                            <td class="px-5 py-4 font-bold text-slate-900">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-7 h-7 rounded-lg bg-amber-50 text-amber-700 border border-amber-200 flex items-center justify-center font-bold text-[11px] shrink-0">
                                        {{ umat.nama_lengkap.charAt(0).toUpperCase() }}
                                    </div>
                                    <span class="group-hover:text-amber-600 transition">{{ umat.nama_lengkap }}</span>
                                </div>
                            </td>
                            <td class="px-5 py-4 font-mono text-slate-500">{{ umat.nik || '—' }}</td>
                            <td class="px-5 py-4">
                                <span :class="[
                                    'px-2 py-0.5 rounded-md text-[10px] font-semibold',
                                    umat.jenis_kelamin === 'Laki-Laki'
                                        ? 'bg-blue-50 text-blue-700 border border-blue-200'
                                        : 'bg-pink-50 text-pink-700 border border-pink-200'
                                ]">
                                    {{ umat.jenis_kelamin || '-' }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-slate-500">
                                {{ umat.tempat_lahir || '—' }}{{ umat.tanggal_lahir ? ', ' + umat.tanggal_lahir : '' }}
                            </td>
                            <td class="px-5 py-4">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    {{ umat.status_umat || 'Aktif' }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-100 text-slate-600 font-semibold text-[11px]">
                                    <i class="fa-solid fa-check text-[10px] text-emerald-600"></i>
                                    <span>Terdata</span>
                                </span>
                            </td>
                        </tr>

                        <tr v-if="!umats.data.length">
                            <td colspan="6" class="px-5 py-12 text-center text-slate-400">
                                <i class="fa-solid fa-magnifying-glass text-2xl mb-2 text-slate-300"></i>
                                <p>Tidak ditemukan data umat yang sesuai.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination Bar -->
            <div
                v-if="umats.links && umats.links.length > 3"
                class="p-4 border-t border-slate-100 flex items-center justify-between gap-2 bg-slate-50/50"
            >
                <span class="text-xs text-slate-500">
                    Menampilkan <b class="text-slate-800">{{ umats.from || 0 }}</b> - <b class="text-slate-800">{{ umats.to || 0 }}</b> dari <b class="text-slate-800">{{ umats.total }}</b> umat
                </span>

                <div class="flex items-center gap-1">
                    <Link
                        v-for="(link, idx) in umats.links"
                        :key="idx"
                        :href="link.url || '#'"
                        prefetch
                        :disabled="!link.url"
                        :class="[
                            'px-3 py-1.5 rounded-lg text-xs font-bold transition',
                            link.active
                                ? 'bg-amber-500 text-white shadow-xs shadow-amber-500/30'
                                : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-100 hover:text-slate-900',
                            !link.url ? 'opacity-40 cursor-not-allowed pointer-events-none' : ''
                        ]"
                        v-html="formatPaginationLabel(link.label)"
                    />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
