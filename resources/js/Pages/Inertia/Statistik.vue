<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const isReloading = ref(false);
const reloadStats = () => {
    isReloading.value = true;
    router.reload({
        preserveScroll: true,
        preserveState: false,
        onFinish: () => {
            setTimeout(() => {
                isReloading.value = false;
            }, 400);
        },
    });
};

const props = defineProps({
    role: { type: String, default: 'Super Admin' },
    prefix: { type: String, default: 'superadmin' },
    paroki: { type: Object, default: () => ({}) },
    activeKub: { type: Object, default: null },
    summary: { type: Object, default: () => ({}) },
    genderStats: { type: Object, default: () => ({ pria: 0, wanita: 0, total: 0 }) },
    usiaStats: { type: Array, default: () => [] },
    sakramenStats: { type: Object, default: () => ({}) },
    wilayahStats: { type: Array, default: () => [] },
    pekerjaanStats: { type: Array, default: () => [] },
    pendidikanStats: { type: Array, default: () => [] },
    masterReferensiStats: { type: Object, default: () => ({}) },
});

const activeTab = ref('ringkasan'); // 'ringkasan', 'usia', 'sakramen', 'wilayah', 'sosial'

const selectedRefCategory = ref('PROFESI');
const searchRefQuery = ref('');
const onlyShowFilled = ref(false);

const refViewMode = ref('both'); // 'both', 'charts', 'table'
const chartViewType = ref('all'); // 'all', 'bars', 'donut'
const barChartLimit = ref('top10'); // 'top10', 'all'
const hoveredDonutIndex = ref(null);

const chartPalette = [
    '#f59e0b', // amber-500
    '#0ea5e9', // sky-500
    '#10b981', // emerald-500
    '#ec4899', // pink-500
    '#8b5cf6', // violet-500
    '#f97316', // orange-500
    '#06b6d4', // cyan-500
    '#6366f1', // indigo-500
    '#14b8a6', // teal-500
    '#e11d48', // rose-600
    '#84cc16', // lime-500
    '#64748b', // slate-500
];

const categoryIcons = {
    PROFESI: 'fa-solid fa-user-tie',
    PEKERJAAN: 'fa-solid fa-briefcase',
    PENDIDIKAN: 'fa-solid fa-graduation-cap',
    GOLONGAN_DARAH: 'fa-solid fa-droplet',
    SUKU_ETNIS: 'fa-solid fa-people-group',
    DISABILITAS: 'fa-solid fa-wheelchair',
    CACAT_TUBUH: 'fa-solid fa-person-dots-from-line',
    DOMISILI_SEKARANG: 'fa-solid fa-house-user',
    KETERAMPILAN: 'fa-solid fa-screwdriver-wrench',
    KEL_PRASEJAHTERA: 'fa-solid fa-hand-holding-heart',
    LOKASI_RUMAH: 'fa-solid fa-location-dot',
    PENGHASILAN: 'fa-solid fa-money-bill-wave',
};

const activeRefCategory = computed(() => {
    return props.masterReferensiStats ? props.masterReferensiStats[selectedRefCategory.value] : null;
});

const filteredRefItems = computed(() => {
    if (!activeRefCategory.value || !activeRefCategory.value.items) return [];
    let items = activeRefCategory.value.items;

    if (onlyShowFilled.value) {
        items = items.filter(it => it.count > 0);
    }

    if (searchRefQuery.value.trim()) {
        const q = searchRefQuery.value.trim().toLowerCase();
        items = items.filter(it =>
            it.nama.toLowerCase().includes(q) ||
            (it.kode && it.kode.toLowerCase().includes(q))
        );
    }

    return items;
});

const dominantRefItem = computed(() => {
    if (!activeRefCategory.value || !activeRefCategory.value.items || !activeRefCategory.value.items.length) return null;
    const sorted = [...activeRefCategory.value.items].sort((a, b) => b.count - a.count);
    return sorted[0] && sorted[0].count > 0 ? sorted[0] : null;
});

const donutData = computed(() => {
    if (!activeRefCategory.value || !activeRefCategory.value.items) return { slices: [], total: 0, hasData: false };
    const items = [...activeRefCategory.value.items].filter(i => i.count > 0).sort((a, b) => b.count - a.count);
    const totalCounted = items.reduce((sum, it) => sum + it.count, 0);
    if (totalCounted === 0) {
        return { slices: [], total: 0, hasData: false };
    }

    const topItems = items.slice(0, 6);
    const otherItems = items.slice(6);
    const otherCount = otherItems.reduce((sum, it) => sum + it.count, 0);

    const slicesData = topItems.map((item, idx) => ({
        name: item.nama,
        count: item.count,
        percent: Number(((item.count / totalCounted) * 100).toFixed(1)),
        color: chartPalette[idx % chartPalette.length],
        isOther: false,
    }));

    if (otherCount > 0) {
        slicesData.push({
            name: `Lainnya (${otherItems.length} item)`,
            count: otherCount,
            percent: Number(((otherCount / totalCounted) * 100).toFixed(1)),
            color: '#94a3b8',
            isOther: true,
        });
    }

    const radius = 68;
    const circumference = 2 * Math.PI * radius;
    let accumulatedPercent = 0;

    const slices = slicesData.map((slice, idx) => {
        const dashLength = (slice.percent / 100) * circumference;
        const strokeDasharray = `${dashLength} ${circumference}`;
        const strokeDashoffset = -((accumulatedPercent / 100) * circumference);
        accumulatedPercent += slice.percent;

        return {
            ...slice,
            index: idx,
            radius,
            circumference,
            strokeDasharray,
            strokeDashoffset,
        };
    });

    return {
        slices,
        total: totalCounted,
        hasData: true,
    };
});

const barChartItems = computed(() => {
    if (!activeRefCategory.value || !activeRefCategory.value.items) return [];
    let list = [...activeRefCategory.value.items];
    if (onlyShowFilled.value) {
        list = list.filter(it => it.count > 0);
    }
    list.sort((a, b) => b.count - a.count);
    if (barChartLimit.value === 'top10') {
        return list.slice(0, 10);
    }
    return list;
});

const maxBarCount = computed(() => {
    const items = barChartItems.value;
    if (!items.length) return 1;
    return Math.max(1, ...items.map(it => it.count));
});

const overview12Categories = computed(() => {
    if (!props.masterReferensiStats) return [];
    return Object.entries(props.masterReferensiStats).map(([key, cat]) => {
        const filledCount = (cat.items || []).filter(i => i.count > 0).length;
        const fillRatio = cat.total_items > 0 ? Math.round((filledCount / cat.total_items) * 100) : 0;
        return {
            key,
            label: cat.label,
            icon: categoryIcons[key] || 'fa-solid fa-tag',
            total_items: cat.total_items,
            filled_items: filledCount,
            total_counted: cat.total_counted,
            unit: cat.unit,
            fillRatio,
            isActive: selectedRefCategory.value === key,
        };
    });
});

const formatNumber = (num) => {
    return new Intl.NumberFormat('id-ID').format(num || 0);
};

const priaPercentage = computed(() => {
    if (!props.genderStats.total) return 50;
    return Math.round((props.genderStats.pria / props.genderStats.total) * 100);
});

const wanitaPercentage = computed(() => {
    if (!props.genderStats.total) return 50;
    return 100 - priaPercentage.value;
});

const printDemografi = () => {
    window.print();
};
</script>

<template>
    <AppLayout :fullWidth="true">
        <Head :title="activeKub ? `Demografi & Statistik ${activeKub.nama_kub} - SIPAROKI` : 'Demografi & Statistik Paroki - SIPAROKI'" />

        <div class="w-full space-y-6 pb-12">
            <!-- 1. EXECUTIVE HEADER BANNER -->
            <div class="bg-gradient-to-r from-amber-600 via-amber-500 to-amber-600 rounded-3xl p-6 sm:p-8 text-white shadow-xl shadow-amber-500/15 relative overflow-hidden">
                <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
                <div class="absolute right-20 top-0 w-32 h-32 bg-amber-300/20 rounded-full blur-xl pointer-events-none"></div>

                <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div class="space-y-2">
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/15 backdrop-blur-md text-amber-100 text-xs font-semibold border border-white/20">
                            <i class="fa-solid fa-chart-pie"></i>
                            <span>{{ activeKub ? `Statistik Komunitas Umat Basis: ${activeKub.nama_kub}` : 'Sistem Informasi Statistik Pastoral & Demografi Umat' }}</span>
                        </div>
                        <h1 class="text-2xl sm:text-3xl font-black tracking-tight">
                            {{ activeKub ? `Demografi & Statistik KUB - ${activeKub.nama_kub}` : 'Demografi & Statistik Paroki' }}
                        </h1>
                        <p class="text-amber-100 text-xs sm:text-sm max-w-2xl leading-relaxed">
                            <template v-if="activeKub">
                                KUB: <strong>{{ activeKub.nama_kub }}</strong> • Wilayah: {{ activeKub.wilayah?.nama_wilayah || '-' }} • Stasi / Kapela: {{ activeKub.kapela?.nama_kapela || 'Pusat Paroki' }} • {{ paroki.nama_paroki || 'Paroki St. Vinsensius a Paulo Benlutu' }}
                            </template>
                            <template v-else>
                                {{ paroki.nama_paroki || 'Paroki St. Vinsensius a Paulo Benlutu' }} • Monitoring sebaran umat, piramida usia, statistik sakramen, dan profil sosial ekonomi secara real-time.
                            </template>
                        </p>
                    </div>

                    <div class="flex items-center gap-2.5 flex-wrap shrink-0">
                        <!-- Reload Data (Database Reload) -->
                        <button
                            type="button"
                            :disabled="isReloading"
                            @click="reloadStats"
                            class="px-4 py-2.5 rounded-2xl bg-white/20 hover:bg-white/30 backdrop-blur-md text-white text-xs font-bold border border-white/30 shadow-sm transition flex items-center gap-2 cursor-pointer disabled:opacity-60"
                            title="Reload data statistik dan grafik langsung dari database"
                        >
                            <i :class="['fa-solid fa-arrows-rotate', isReloading ? 'fa-spin' : '']"></i>
                            <span>{{ isReloading ? 'Memuat...' : 'Reload' }}</span>
                        </button>

                        <!-- Ekspor Excel -->
                        <a
                            :href="`/${prefix}/statistik/export/excel${activeKub ? '?kub_id=' + activeKub.id : ''}`"
                            class="px-4 py-2.5 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-md shadow-emerald-900/20 transition flex items-center gap-2 cursor-pointer"
                        >
                            <i class="fa-solid fa-file-excel"></i>
                            <span>Ekspor Excel</span>
                        </a>

                        <!-- Cetak / PDF -->
                        <a
                            :href="`/${prefix}/statistik/export/print${activeKub ? '?kub_id=' + activeKub.id : ''}`"
                            target="_blank"
                            class="px-4 py-2.5 rounded-2xl bg-white/20 hover:bg-white/30 backdrop-blur-md text-white text-xs font-bold border border-white/30 shadow-sm transition flex items-center gap-2 cursor-pointer"
                        >
                            <i class="fa-solid fa-print"></i>
                            <span>Cetak / PDF</span>
                        </a>

                        <!-- Data Umat Link -->
                        <Link
                            :href="`/${prefix}/umat`"
                            class="px-4 py-2.5 rounded-2xl bg-white hover:bg-amber-50 text-amber-900 text-xs font-black shadow-lg shadow-black/5 transition flex items-center gap-2"
                        >
                            <i class="fa-solid fa-users"></i>
                            <span>Data Umat / Jiwa</span>
                        </Link>
                    </div>
                </div>
            </div>

            <!-- 2. SUMMARY COUNTER METRICS -->
            <div class="grid grid-cols-2 lg:grid-cols-5 gap-3.5 sm:gap-4">
                <!-- Total Umat -->
                <div class="col-span-2 lg:col-span-1 bg-white rounded-3xl p-5 border border-slate-200/80 shadow-xs hover:shadow-md transition">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-500">Total Umat (Jiwa)</span>
                        <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-sm shadow-2xs">
                            <i class="fa-solid fa-users"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <span class="text-2xl sm:text-3xl font-black text-slate-900">{{ formatNumber(summary.totalUmat) }}</span>
                        <span class="text-xs font-semibold text-slate-400 ml-1">Jiwa</span>
                    </div>
                    <div class="mt-2 flex items-center gap-1.5 text-[11px] text-emerald-600 font-bold">
                        <i class="fa-solid fa-circle-check text-[10px]"></i>
                        <span>Umat Aktif Terdaftar</span>
                    </div>
                </div>

                <!-- Total KK -->
                <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-xs hover:shadow-md transition">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-500">Kepala Keluarga</span>
                        <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-sm shadow-2xs">
                            <i class="fa-solid fa-house-chimney-user"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <span class="text-2xl font-black text-slate-900">{{ formatNumber(summary.totalKK) }}</span>
                        <span class="text-xs font-semibold text-slate-400 ml-1">KK</span>
                    </div>
                    <div class="mt-2 text-[11px] text-slate-400">
                        Buku Kartu Keluarga Katolik
                    </div>
                </div>

                <!-- Total KUB -->
                <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-xs hover:shadow-md transition flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-slate-500">Komunitas Umat (KUB)</span>
                            <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm shadow-2xs shrink-0">
                                <i class="fa-solid fa-people-group"></i>
                            </div>
                        </div>
                        <div class="mt-3">
                            <span :class="activeKub ? 'text-xs sm:text-sm md:text-base leading-snug' : 'text-2xl'" class="font-black text-slate-900 break-words block" :title="activeKub ? activeKub.nama_kub : (formatNumber(summary.totalKUB) + ' KUB')">
                                {{ activeKub ? activeKub.nama_kub : (formatNumber(summary.totalKUB) + ' KUB') }}
                            </span>
                        </div>
                    </div>
                    <div class="mt-2 text-[11px] text-slate-400">
                        {{ activeKub ? 'Basis Komunitas Aktif' : 'Basis Komunitas Umat' }}
                    </div>
                </div>

                <!-- Total Wilayah -->
                <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-xs hover:shadow-md transition flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-slate-500">Wilayah Rohani</span>
                            <div class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-sm shadow-2xs shrink-0">
                                <i class="fa-solid fa-map-location-dot"></i>
                            </div>
                        </div>
                        <div class="mt-3">
                            <span :class="activeKub ? 'text-xs sm:text-sm md:text-base leading-snug' : 'text-2xl'" class="font-black text-slate-900 break-words block" :title="activeKub?.wilayah?.nama_wilayah ? activeKub.wilayah.nama_wilayah : (formatNumber(summary.totalWilayah) + ' Wilayah')">
                                {{ activeKub?.wilayah?.nama_wilayah ? activeKub.wilayah.nama_wilayah : (formatNumber(summary.totalWilayah) + ' Wilayah') }}
                            </span>
                        </div>
                    </div>
                    <div class="mt-2 text-[11px] text-slate-400">
                        Wilayah Pelayanan Paroki
                    </div>
                </div>

                <!-- Total Kapela / Stasi -->
                <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-xs hover:shadow-md transition flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-slate-500">Stasi / Kapela</span>
                            <div class="w-9 h-9 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-sm shadow-2xs shrink-0">
                                <i class="fa-solid fa-place-of-worship"></i>
                            </div>
                        </div>
                        <div class="mt-3">
                            <span :class="activeKub ? 'text-xs sm:text-sm md:text-base leading-snug' : 'text-2xl'" class="font-black text-slate-900 break-words block" :title="activeKub?.kapela?.nama_kapela ? activeKub.kapela.nama_kapela : (formatNumber(summary.totalKapela) + ' Stasi')">
                                {{ activeKub?.kapela?.nama_kapela ? activeKub.kapela.nama_kapela : (formatNumber(summary.totalKapela) + ' Stasi') }}
                            </span>
                        </div>
                    </div>
                    <div class="mt-2 text-[11px] text-slate-400">
                        Pos Pelayanan Ekaristi
                    </div>
                </div>
            </div>

            <!-- 3. DEMOGRAFI UTAMA: GENDER & PIRAMIDA USIA -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                <!-- GENDER RATIO CARD -->
                <div class="lg:col-span-5 bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs space-y-5">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                            <i class="fa-solid fa-venus-mars text-amber-600"></i>
                            <span>Komposisi Jenis Kelamin</span>
                        </h3>
                        <span class="text-[11px] font-bold text-slate-400">Rasio Umat</span>
                    </div>

                    <!-- Progress Bar Ratio -->
                    <div class="space-y-2">
                        <div class="h-4 w-full rounded-full bg-slate-100 flex overflow-hidden p-0.5">
                            <div
                                :style="{ width: `${priaPercentage}%` }"
                                class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-l-full transition-all duration-500"
                            ></div>
                            <div
                                :style="{ width: `${wanitaPercentage}%` }"
                                class="bg-gradient-to-r from-pink-500 to-rose-500 rounded-r-full transition-all duration-500"
                            ></div>
                        </div>
                        <div class="flex items-center justify-between text-xs font-bold">
                            <span class="text-blue-600 flex items-center gap-1.5">
                                <i class="fa-solid fa-mars"></i> Pria: {{ priaPercentage }}%
                            </span>
                            <span class="text-rose-600 flex items-center gap-1.5">
                                <i class="fa-solid fa-venus"></i> Wanita: {{ wanitaPercentage }}%
                            </span>
                        </div>
                    </div>

                    <!-- Counter Boxes -->
                    <div class="grid grid-cols-2 gap-3 pt-2">
                        <div class="p-4 rounded-2xl bg-blue-50/70 border border-blue-100 space-y-1">
                            <span class="text-[11px] font-bold text-blue-700">Laki-laki (Pria)</span>
                            <p class="text-xl font-black text-blue-950">{{ formatNumber(genderStats.pria) }} <span class="text-xs font-semibold text-blue-600">Jiwa</span></p>
                        </div>
                        <div class="p-4 rounded-2xl bg-rose-50/70 border border-rose-100 space-y-1">
                            <span class="text-[11px] font-bold text-rose-700">Perempuan (Wanita)</span>
                            <p class="text-xl font-black text-rose-950">{{ formatNumber(genderStats.wanita) }} <span class="text-xs font-semibold text-rose-600">Jiwa</span></p>
                        </div>
                    </div>
                </div>

                <!-- PIRAMIDA KELOMPOK USIA -->
                <div class="lg:col-span-7 bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs space-y-5">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                            <i class="fa-solid fa-chart-simple text-amber-600"></i>
                            <span>Distribusi Kelompok Usia & Generasi</span>
                        </h3>
                        <span class="text-[11px] font-bold text-slate-400">Rentang Usia</span>
                    </div>

                    <div class="space-y-3.5">
                        <div v-for="u in usiaStats" :key="u.label" class="space-y-1">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-bold text-slate-700 flex items-center gap-2">
                                    <i :class="u.icon || 'fa-solid fa-user'" class="text-[11px] text-amber-600 w-4 text-center"></i>
                                    <span>{{ u.label }}</span>
                                    <span class="text-[10.5px] font-normal text-slate-400">({{ u.range }})</span>
                                </span>
                                <span class="font-bold text-slate-900">
                                    {{ formatNumber(u.count) }} Jiwa <span class="text-slate-400 font-normal">({{ u.percentage }}%)</span>
                                </span>
                            </div>
                            <div class="h-2.5 w-full rounded-full bg-slate-100 overflow-hidden">
                                <div
                                    :class="u.color || 'bg-amber-500'"
                                    :style="{ width: `${u.percentage}%` }"
                                    class="h-full rounded-full transition-all duration-500"
                                ></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4. STATISTIK SAKRAMEN GEREJA -->
            <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs space-y-5">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div class="space-y-0.5">
                        <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                            <i class="fa-solid fa-book-bible text-amber-600"></i>
                            <span>{{ activeKub ? `Penerimaan Sakramen Umat ${activeKub.nama_kub}` : 'Penerimaan Sakramen Umat Paroki' }}</span>
                        </h3>
                        <p class="text-[11px] text-slate-400">Persentase dan status inisiasi kristiani umat {{ activeKub ? 'di KUB bersangkutan' : 'paroki' }}</p>
                    </div>
                    <Link
                        :href="`/${prefix}/sakramen`"
                        class="text-xs font-bold text-amber-600 hover:text-amber-700 underline"
                    >
                        Kelola Buku Sakramen &rarr;
                    </Link>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Sakramen Baptis -->
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/70 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-slate-700">Sakramen Baptis</span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800">Inisiasi</span>
                        </div>
                        <div class="text-2xl font-black text-slate-900">{{ formatNumber(sakramenStats.baptis || summary.totalUmat) }}</div>
                        <p class="text-[11px] text-slate-500">Tercatat dalam Buku Baptis</p>
                    </div>

                    <!-- Sakramen Ekaristi / Komuni -->
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/70 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-slate-700">Komuni Pertama</span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">Ekaristi</span>
                        </div>
                        <div class="text-2xl font-black text-slate-900">{{ formatNumber(sakramenStats.komuni) }}</div>
                        <p class="text-[11px] text-slate-500">Umat yang telah menyambut Komuni</p>
                    </div>

                    <!-- Sakramen Krisma -->
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/70 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-slate-700">Sakramen Krisma</span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-100 text-purple-800">Penguatan</span>
                        </div>
                        <div class="text-2xl font-black text-slate-900">{{ formatNumber(sakramenStats.krisma) }}</div>
                        <p class="text-[11px] text-slate-500">Kedewasaan iman kristiani</p>
                    </div>

                    <!-- Sakramen Pernikahan -->
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/70 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-slate-700">Pernikahan Katolik</span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">Keluarga</span>
                        </div>
                        <div class="text-2xl font-black text-slate-900">{{ formatNumber(sakramenStats.nikah) }}</div>
                        <p class="text-[11px] text-slate-500">Pasangan terberkati sakramen</p>
                    </div>
                </div>
            </div>

            <!-- 5. SEBARAN UMAT PER WILAYAH & KUB ATAU DAFTAR KK KUB -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                <!-- WILAYAH / KK BREAKDOWN TABLE -->
                <div class="lg:col-span-7 bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                            <i :class="activeKub ? 'fa-solid fa-house-chimney-user' : 'fa-solid fa-map-location-dot'" class="text-amber-600"></i>
                            <span>{{ activeKub ? `Daftar Keluarga (KK) di ${activeKub.nama_kub}` : 'Distribusi Umat per Wilayah & KUB' }}</span>
                        </h3>
                        <Link :href="activeKub ? `/${prefix}/kk-katolik` : `/${prefix}/wilayah`" class="text-xs font-bold text-amber-600 hover:text-amber-700 underline">
                            {{ activeKub ? 'Kelola Data KK &rarr;' : 'Data Wilayah &rarr;' }}
                        </Link>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 text-slate-600 uppercase text-[10px] font-bold tracking-wider">
                                <tr v-if="activeKub">
                                    <th class="py-2.5 px-3 rounded-l-xl">Nama Kepala Keluarga</th>
                                    <th class="py-2.5 px-3 text-center">No. KK Katolik</th>
                                    <th class="py-2.5 px-3">Alamat Domisili</th>
                                    <th class="py-2.5 px-3 text-right rounded-r-xl">Anggota (Jiwa)</th>
                                </tr>
                                <tr v-else>
                                    <th class="py-2.5 px-3 rounded-l-xl">Nama Wilayah</th>
                                    <th class="py-2.5 px-3 text-center">Jumlah KUB</th>
                                    <th class="py-2.5 px-3 text-right">Jumlah KK</th>
                                    <th class="py-2.5 px-3 text-right rounded-r-xl">Total Jiwa</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="w in wilayahStats" :key="w.id || w.nama_wilayah" class="hover:bg-slate-50/60 transition">
                                    <td class="py-3 px-3 font-bold text-slate-900 flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-lg bg-amber-50 text-amber-700 flex items-center justify-center text-[10px] shrink-0">
                                            <i :class="activeKub ? 'fa-solid fa-house-user' : 'fa-solid fa-cross'"></i>
                                        </div>
                                        <span class="truncate max-w-[200px]">{{ w.nama_wilayah }}</span>
                                    </td>
                                    <td v-if="activeKub" class="py-3 px-3 text-center font-bold text-slate-600 font-mono text-[11px]">{{ w.no_kk || '-' }}</td>
                                    <td v-if="activeKub" class="py-3 px-3 text-slate-500 truncate max-w-[150px]">{{ w.alamat || '-' }}</td>
                                    <td v-if="!activeKub" class="py-3 px-3 text-center font-bold text-slate-600">{{ w.kub_count || 0 }} KUB</td>
                                    <td v-if="!activeKub" class="py-3 px-3 text-right font-bold text-slate-700">{{ formatNumber(w.kk_count) }}</td>
                                    <td class="py-3 px-3 text-right font-black text-amber-700">{{ formatNumber(w.umat_count) }}</td>
                                </tr>
                                <tr v-if="!wilayahStats.length">
                                    <td :colspan="activeKub ? 4 : 4" class="py-6 text-center text-slate-400">
                                        {{ activeKub ? 'Belum ada data KK terdaftar di KUB ini' : 'Belum ada data wilayah tersinkronisasi' }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- SOSIAL & PROFESI UMAT -->
                <div class="lg:col-span-5 bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                            <i class="fa-solid fa-briefcase text-amber-600"></i>
                            <span>Profil Profesi & Pekerjaan Umat</span>
                        </h3>
                        <span class="text-[11px] font-bold text-slate-400">Top Bidang</span>
                    </div>

                    <div class="space-y-3">
                        <div v-for="p in pekerjaanStats" :key="p.nama" class="space-y-1">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-bold text-slate-700">{{ p.nama }}</span>
                                <span class="font-bold text-slate-900">{{ formatNumber(p.count) }} Jiwa <span class="text-slate-400 font-normal">({{ p.percentage }}%)</span></span>
                            </div>
                            <div class="h-2 w-full rounded-full bg-slate-100 overflow-hidden">
                                <div :style="{ width: `${p.percentage}%` }" class="h-full bg-amber-500 rounded-full"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 5. STATISTIK DETAIL 12 KATEGORI MASTER REFERENSI -->
            <div id="master-referensi-stats" class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-xs space-y-6">
                <!-- Section Header -->
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-100 pb-5">
                    <div class="space-y-1">
                        <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-full bg-amber-50 text-amber-800 text-[11px] font-bold border border-amber-200/60">
                            <i class="fa-solid fa-database text-amber-600"></i>
                            <span>Master Referensi Paroki Terpadu</span>
                        </div>
                        <h2 class="text-lg sm:text-xl font-black text-slate-900 flex items-center gap-2.5">
                            <i class="fa-solid fa-layer-group text-amber-600"></i>
                            <span>Statistik Detail 12 Kategori Master Referensi</span>
                        </h2>
                        <p class="text-xs text-slate-500 leading-relaxed">
                            Perhitungan terperinci seluruh butir master referensi paroki yang tercatat pada data Umat dan Kartu Keluarga (KK).
                        </p>
                    </div>

                    <!-- Search & Filter Bar -->
                    <div class="flex items-center gap-3 flex-wrap">
                        <div class="relative min-w-[240px]">
                            <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                            <input
                                v-model="searchRefQuery"
                                type="text"
                                placeholder="Cari item (Guru, Dokter, AB, dll)..."
                                class="w-full pl-9 pr-8 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition"
                            />
                            <button
                                v-if="searchRefQuery"
                                @click="searchRefQuery = ''"
                                class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 text-xs p-1"
                            >
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>

                        <label class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-700 cursor-pointer select-none hover:bg-slate-100 transition">
                            <input
                                v-model="onlyShowFilled"
                                type="checkbox"
                                class="rounded text-amber-600 focus:ring-amber-500 h-3.5 w-3.5 border-slate-300"
                            />
                            <span class="font-semibold text-[11px]">Hanya yang terisi (&gt; 0)</span>
                        </label>
                    </div>
                </div>

                <!-- 12 Category Selector Pills -->
                <div>
                    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2.5">
                        Pilih Kategori Referensi (12 Kategori):
                    </div>
                    <div class="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-thin">
                        <button
                            v-for="(catData, catKey) in masterReferensiStats"
                            :key="catKey"
                            type="button"
                            @click="selectedRefCategory = catKey; searchRefQuery = ''"
                            :class="[
                                'shrink-0 px-3.5 py-2.5 rounded-2xl text-xs font-bold transition-all flex items-center gap-2 border cursor-pointer',
                                selectedRefCategory === catKey
                                    ? 'bg-amber-600 text-white border-amber-600 shadow-md shadow-amber-600/25 ring-2 ring-amber-600/20'
                                    : 'bg-slate-50 hover:bg-slate-100/80 text-slate-700 border-slate-200/80 hover:border-slate-300'
                            ]"
                        >
                            <i :class="[categoryIcons[catKey] || 'fa-solid fa-tag', selectedRefCategory === catKey ? 'text-white' : 'text-amber-600']"></i>
                            <span>{{ catData.label }}</span>
                            <span
                                :class="[
                                    'px-1.5 py-0.5 rounded-full text-[10px] font-extrabold',
                                    selectedRefCategory === catKey ? 'bg-white/25 text-white' : 'bg-slate-200/70 text-slate-600'
                                ]"
                            >
                                {{ catData.total_items }}
                            </span>
                        </button>
                    </div>
                </div>

                <!-- Active Category Key Metrics -->
                <div v-if="activeRefCategory" class="grid grid-cols-2 md:grid-cols-4 gap-3.5 sm:gap-4">
                    <div class="bg-gradient-to-br from-amber-500/10 via-amber-500/5 to-transparent rounded-2xl p-4 border border-amber-500/20">
                        <div class="text-[11px] font-bold text-amber-800 uppercase tracking-wider">Kategori Terpilih</div>
                        <div class="mt-1 text-lg font-black text-slate-900 truncate">{{ activeRefCategory.label }}</div>
                        <div class="mt-1 text-[11px] text-amber-700 font-semibold flex items-center gap-1.5">
                            <i class="fa-solid fa-database text-[10px]"></i>
                            <span>Basis: {{ activeRefCategory.entity_label || (activeRefCategory.entity === 'kk' ? 'Kepala Keluarga (KK)' : 'Data Umat (Jiwa)') }}</span>
                        </div>
                    </div>

                    <div class="bg-slate-50 rounded-2xl p-4 border border-slate-200/70">
                        <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Varian Terdaftar</div>
                        <div class="mt-1 text-lg font-black text-slate-900">{{ activeRefCategory.total_items }} <span class="text-xs font-semibold text-slate-500">Butir Item</span></div>
                        <div class="mt-1 text-[11px] text-slate-400">Master referensi aktif</div>
                    </div>

                    <div class="bg-slate-50 rounded-2xl p-4 border border-slate-200/70">
                        <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Total Populasi Terdata</div>
                        <div class="mt-1 text-lg font-black text-slate-900">
                            {{ formatNumber(activeRefCategory.total_counted) }}
                            <span class="text-xs font-semibold text-slate-500">{{ activeRefCategory.unit }}</span>
                        </div>
                        <div class="mt-1 text-[11px] text-emerald-600 font-bold">
                            {{ activeRefCategory.total_population ? ((activeRefCategory.total_counted / activeRefCategory.total_population) * 100).toFixed(1) : 0 }}% dari seluruh {{ activeRefCategory.entity_label || activeRefCategory.entity }}
                        </div>
                    </div>

                    <div class="bg-slate-50 rounded-2xl p-4 border border-slate-200/70">
                        <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Item Terbanyak / Dominan</div>
                        <div class="mt-1 text-lg font-black text-amber-700 truncate" :title="dominantRefItem ? dominantRefItem.nama : '-'">
                            {{ dominantRefItem ? dominantRefItem.nama : '-' }}
                        </div>
                        <div class="mt-1 text-[11px] text-slate-500 font-semibold truncate">
                            {{ dominantRefItem ? `${formatNumber(dominantRefItem.count)} ${activeRefCategory.unit} (${dominantRefItem.percentage}%)` : 'Belum ada data terisi' }}
                        </div>
                    </div>
                </div>

                <!-- Display Mode & Chart Controls -->
                <div v-if="activeRefCategory" class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-2 border-t border-slate-100">
                    <!-- View Mode Pills -->
                    <div class="flex items-center gap-1.5 p-1 rounded-2xl bg-slate-100 border border-slate-200/80 shrink-0">
                        <button
                            type="button"
                            @click="refViewMode = 'both'"
                            :class="[
                                'px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 cursor-pointer',
                                refViewMode === 'both' ? 'bg-white text-amber-700 shadow-2xs font-extrabold' : 'text-slate-500 hover:text-slate-800'
                            ]"
                        >
                            <i class="fa-solid fa-table-columns text-[11px]"></i>
                            <span>Grafik & Tabel</span>
                        </button>
                        <button
                            type="button"
                            @click="refViewMode = 'charts'"
                            :class="[
                                'px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 cursor-pointer',
                                refViewMode === 'charts' ? 'bg-white text-amber-700 shadow-2xs font-extrabold' : 'text-slate-500 hover:text-slate-800'
                            ]"
                        >
                            <i class="fa-solid fa-chart-pie text-[11px]"></i>
                            <span>Fokus Grafik</span>
                        </button>
                        <button
                            type="button"
                            @click="refViewMode = 'table'"
                            :class="[
                                'px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 cursor-pointer',
                                refViewMode === 'table' ? 'bg-white text-amber-700 shadow-2xs font-extrabold' : 'text-slate-500 hover:text-slate-800'
                            ]"
                        >
                            <i class="fa-solid fa-table-list text-[11px]"></i>
                            <span>Fokus Tabel</span>
                        </button>
                    </div>

                    <!-- Chart Style Toggle (When charts are shown) -->
                    <div v-if="refViewMode !== 'table'" class="flex items-center gap-1.5 text-xs">
                        <span class="text-[11px] font-bold text-slate-400">Pilihan Diagram:</span>
                        <div class="inline-flex rounded-xl bg-slate-100 p-0.5 border border-slate-200/80">
                            <button
                                type="button"
                                @click="chartViewType = 'all'"
                                :class="['px-2.5 py-1 rounded-lg text-xs font-bold transition cursor-pointer', chartViewType === 'all' ? 'bg-white text-amber-700 shadow-2xs' : 'text-slate-600 hover:text-slate-900']"
                            >
                                Semua Diagram
                            </button>
                            <button
                                type="button"
                                @click="chartViewType = 'bars'"
                                :class="['px-2.5 py-1 rounded-lg text-xs font-bold transition cursor-pointer', chartViewType === 'bars' ? 'bg-white text-amber-700 shadow-2xs' : 'text-slate-600 hover:text-slate-900']"
                            >
                                Grafik Batang
                            </button>
                            <button
                                type="button"
                                @click="chartViewType = 'donut'"
                                :class="['px-2.5 py-1 rounded-lg text-xs font-bold transition cursor-pointer', chartViewType === 'donut' ? 'bg-white text-amber-700 shadow-2xs' : 'text-slate-600 hover:text-slate-900']"
                            >
                                Diagram Donat
                            </button>
                        </div>
                    </div>
                </div>

                <!-- CHARTS CONTAINER -->
                <div v-if="refViewMode !== 'table' && activeRefCategory" class="space-y-6">
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                        <!-- DIAGRAM DONAT PROPORSI (LEFT) -->
                        <div
                            v-if="chartViewType === 'all' || chartViewType === 'donut'"
                            :class="chartViewType === 'donut' ? 'lg:col-span-12' : 'lg:col-span-5'"
                            class="bg-slate-50/70 rounded-3xl p-5 sm:p-6 border border-slate-200/80 shadow-xs flex flex-col justify-between space-y-5"
                        >
                            <div class="flex items-center justify-between border-b border-slate-200/60 pb-3">
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center text-xs">
                                        <i class="fa-solid fa-chart-pie"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-xs font-black text-slate-900 uppercase tracking-wider">Proporsi Distribusi</h3>
                                        <p class="text-[11px] text-slate-500">Komposisi 6 butir teratas & lainnya</p>
                                    </div>
                                </div>
                                <span class="text-[10.5px] font-bold px-2 py-0.5 rounded-full bg-amber-50 text-amber-700 border border-amber-200/70">
                                    {{ donutData.slices.length }} Segmen
                                </span>
                            </div>

                            <!-- SVG Donut Chart Visual -->
                            <div class="relative flex items-center justify-center my-2">
                                <template v-if="donutData.hasData">
                                    <svg viewBox="0 0 200 200" class="w-48 h-48 sm:w-56 sm:h-56 -rotate-90 transform">
                                        <!-- Background ring -->
                                        <circle
                                            cx="100"
                                            cy="100"
                                            r="68"
                                            fill="transparent"
                                            stroke="#f1f5f9"
                                            stroke-width="26"
                                        />
                                        <!-- Slices -->
                                        <circle
                                            v-for="(slice, idx) in donutData.slices"
                                            :key="slice.name"
                                            cx="100"
                                            cy="100"
                                            r="68"
                                            fill="transparent"
                                            :stroke="slice.color"
                                            :stroke-width="hoveredDonutIndex === idx ? 32 : 26"
                                            :stroke-dasharray="slice.strokeDasharray"
                                            :stroke-dashoffset="slice.strokeDashoffset"
                                            class="transition-all duration-300 cursor-pointer"
                                            @mouseenter="hoveredDonutIndex = idx"
                                            @mouseleave="hoveredDonutIndex = null"
                                        />
                                    </svg>

                                    <!-- Center Label / Tooltip Info -->
                                    <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none text-center px-4">
                                        <template v-if="hoveredDonutIndex !== null && donutData.slices[hoveredDonutIndex]">
                                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">
                                                {{ donutData.slices[hoveredDonutIndex].isOther ? 'Kelompok' : 'Butir Pilihan' }}
                                            </span>
                                            <span class="text-xs font-black text-slate-800 line-clamp-1 max-w-[130px]" :title="donutData.slices[hoveredDonutIndex].name">
                                                {{ donutData.slices[hoveredDonutIndex].name }}
                                            </span>
                                            <span class="text-sm font-black text-amber-600 mt-0.5">
                                                {{ formatNumber(donutData.slices[hoveredDonutIndex].count) }} {{ activeRefCategory.unit }}
                                            </span>
                                            <span class="text-[10px] font-bold text-slate-500">
                                                ({{ donutData.slices[hoveredDonutIndex].percent }}%)
                                            </span>
                                        </template>
                                        <template v-else>
                                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Terdata</span>
                                            <span class="text-lg sm:text-xl font-black text-slate-900 mt-0.5">
                                                {{ formatNumber(donutData.total) }}
                                            </span>
                                            <span class="text-[10px] font-semibold text-slate-500">
                                                {{ activeRefCategory.unit }}
                                            </span>
                                            <span v-if="dominantRefItem" class="text-[9.5px] font-bold text-amber-700 bg-amber-50 border border-amber-200/60 rounded-full px-2 py-0.5 mt-1 max-w-[120px] truncate">
                                                Dominan: {{ dominantRefItem.nama }}
                                            </span>
                                        </template>
                                    </div>
                                </template>
                                <div v-else class="py-12 flex flex-col items-center justify-center text-center text-slate-400 space-y-2">
                                    <i class="fa-solid fa-chart-pie text-3xl text-slate-300"></i>
                                    <p class="text-xs font-bold text-slate-500">Belum ada data terisi pada kategori ini</p>
                                </div>
                            </div>

                            <!-- Interactive Legend List -->
                            <div v-if="donutData.hasData" class="space-y-1.5 pt-2 border-t border-slate-200/60">
                                <div
                                    v-for="(slice, idx) in donutData.slices"
                                    :key="slice.name"
                                    @mouseenter="hoveredDonutIndex = idx"
                                    @mouseleave="hoveredDonutIndex = null"
                                    :class="[
                                        'flex items-center justify-between p-1.5 rounded-xl text-xs transition cursor-pointer select-none',
                                        hoveredDonutIndex === idx ? 'bg-amber-100/60 font-bold' : 'hover:bg-slate-100/80 text-slate-700'
                                    ]"
                                >
                                    <div class="flex items-center gap-2 min-w-0 pr-2">
                                        <span
                                            class="w-2.5 h-2.5 rounded-full shrink-0 shadow-2xs"
                                            :style="{ backgroundColor: slice.color }"
                                        ></span>
                                        <span class="truncate text-[11.5px] font-medium" :title="slice.name">{{ slice.name }}</span>
                                    </div>
                                    <div class="flex items-center gap-2 shrink-0 font-bold text-[11px]">
                                        <span class="text-slate-900 font-extrabold">{{ formatNumber(slice.count) }}</span>
                                        <span class="text-slate-400 font-semibold w-12 text-right">({{ slice.percent }}%)</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- GRAFIK BATANG PERINGKAT (RIGHT) -->
                        <div
                            v-if="chartViewType === 'all' || chartViewType === 'bars'"
                            :class="chartViewType === 'bars' ? 'lg:col-span-12' : 'lg:col-span-7'"
                            class="bg-white rounded-3xl p-5 sm:p-6 border border-slate-200/80 shadow-xs flex flex-col justify-between space-y-4"
                        >
                            <div class="flex items-center justify-between border-b border-slate-100 pb-3 flex-wrap gap-2">
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center text-xs">
                                        <i class="fa-solid fa-chart-simple"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-xs font-black text-slate-900 uppercase tracking-wider">Peringkat Distribusi Terbanyak</h3>
                                        <p class="text-[11px] text-slate-500">Urutan butir referensi dari volume terbesar</p>
                                    </div>
                                </div>

                                <!-- Limit Toggle -->
                                <div class="flex items-center gap-1 p-0.5 rounded-xl bg-slate-100 border border-slate-200/80 text-xs">
                                    <button
                                        type="button"
                                        @click="barChartLimit = 'top10'"
                                        :class="['px-2.5 py-1 rounded-lg font-bold text-[11px] transition cursor-pointer', barChartLimit === 'top10' ? 'bg-white text-amber-700 shadow-2xs' : 'text-slate-500 hover:text-slate-800']"
                                    >
                                        Top 10
                                    </button>
                                    <button
                                        type="button"
                                        @click="barChartLimit = 'all'"
                                        :class="['px-2.5 py-1 rounded-lg font-bold text-[11px] transition cursor-pointer', barChartLimit === 'all' ? 'bg-white text-amber-700 shadow-2xs' : 'text-slate-500 hover:text-slate-800']"
                                    >
                                        Semua ({{ activeRefCategory.total_items }})
                                    </button>
                                </div>
                            </div>

                            <!-- Horizontal Bars List -->
                            <div v-if="barChartItems.length > 0" class="space-y-3 pt-1">
                                <div
                                    v-for="(item, idx) in barChartItems"
                                    :key="item.id || item.nama || idx"
                                    class="group p-2 rounded-2xl hover:bg-slate-50/80 transition space-y-1.5"
                                >
                                    <div class="flex items-center justify-between text-xs gap-2">
                                        <div class="flex items-center gap-2 min-w-0">
                                            <!-- Rank Badge -->
                                            <span
                                                :class="[
                                                    'w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-black shrink-0',
                                                    idx === 0 ? 'bg-amber-500 text-white shadow-xs' :
                                                    idx === 1 ? 'bg-slate-300 text-slate-700' :
                                                    idx === 2 ? 'bg-amber-700 text-amber-100' : 'bg-slate-100 text-slate-500'
                                                ]"
                                            >
                                                {{ idx + 1 }}
                                            </span>
                                            <span class="font-bold text-slate-800 text-[12px] truncate group-hover:text-amber-700 transition" :title="item.nama">
                                                {{ item.nama }}
                                            </span>
                                        </div>
                                        <div class="flex items-center gap-2 shrink-0">
                                            <span class="font-black text-slate-900 text-xs">
                                                {{ formatNumber(item.count) }} <span class="text-[10px] font-semibold text-slate-400">{{ activeRefCategory.unit }}</span>
                                            </span>
                                            <span class="text-[10.5px] font-bold px-1.5 py-0.5 rounded-md bg-amber-50 text-amber-800 border border-amber-200/60 min-w-[42px] text-right">
                                                {{ item.percentage }}%
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Progress Bar with gradient & animated width -->
                                    <div class="h-3 w-full rounded-full bg-slate-100 overflow-hidden relative p-0.5">
                                        <div
                                            :style="{ width: `${(item.count / maxBarCount) * 100}%` }"
                                            :class="[
                                                'h-full rounded-full transition-all duration-700',
                                                idx === 0 ? 'bg-gradient-to-r from-amber-500 to-amber-600 shadow-xs' :
                                                idx === 1 ? 'bg-gradient-to-r from-sky-500 to-blue-600' :
                                                idx === 2 ? 'bg-gradient-to-r from-emerald-500 to-teal-600' :
                                                idx === 3 ? 'bg-gradient-to-r from-violet-500 to-purple-600' :
                                                'bg-gradient-to-r from-slate-400 to-slate-500'
                                            ]"
                                        ></div>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="py-12 text-center text-slate-400 space-y-2">
                                <i class="fa-solid fa-chart-simple text-3xl text-slate-300"></i>
                                <p class="text-xs font-bold text-slate-500">Tidak ada data untuk ditampilkan</p>
                            </div>
                        </div>
                    </div>

                    <!-- PANORAMA 12 KATEGORI OVERVIEW CARDS -->
                    <div class="bg-gradient-to-br from-slate-50 via-white to-amber-50/20 rounded-3xl p-5 sm:p-6 border border-slate-200/80 shadow-xs space-y-4">
                        <div class="flex items-center justify-between flex-wrap gap-2">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-lg bg-amber-500/15 text-amber-700 flex items-center justify-center text-xs">
                                    <i class="fa-solid fa-shapes"></i>
                                </div>
                                <div>
                                    <h3 class="text-xs font-black text-slate-900 uppercase tracking-wider">Komparasi Panorama 12 Kategori Referensi</h3>
                                    <p class="text-[11px] text-slate-500">Klik salah satu kartu di bawah ini untuk berpindah kategori secara instan</p>
                                </div>
                            </div>
                            <span class="text-[10px] font-bold text-slate-400 bg-white px-2.5 py-1 rounded-full border border-slate-200">
                                12 Kategori Lengkap
                            </span>
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
                            <button
                                v-for="cat in overview12Categories"
                                :key="cat.key"
                                type="button"
                                @click="selectedRefCategory = cat.key; searchRefQuery = ''"
                                :class="[
                                    'p-3 rounded-2xl border text-left transition flex flex-col justify-between space-y-2 cursor-pointer',
                                    cat.isActive
                                        ? 'bg-amber-500/10 border-amber-500/60 ring-2 ring-amber-500/20 shadow-xs'
                                        : 'bg-white hover:bg-slate-50 border-slate-200/70 hover:border-slate-300'
                                ]"
                            >
                                <div class="flex items-center justify-between">
                                    <div
                                        :class="[
                                            'w-7 h-7 rounded-xl flex items-center justify-center text-xs',
                                            cat.isActive ? 'bg-amber-600 text-white shadow-2xs' : 'bg-slate-100 text-slate-600'
                                        ]"
                                    >
                                        <i :class="cat.icon"></i>
                                    </div>
                                    <span
                                        :class="[
                                            'text-[10px] font-black px-1.5 py-0.5 rounded-full',
                                            cat.isActive ? 'bg-amber-600 text-white' : 'bg-slate-100 text-slate-600'
                                        ]"
                                    >
                                        {{ cat.total_items }}
                                    </span>
                                </div>

                                <div>
                                    <div class="text-[11px] font-bold text-slate-800 line-clamp-1" :title="cat.label">{{ cat.label }}</div>
                                    <div class="text-[10px] font-semibold text-slate-400 mt-0.5">
                                        {{ formatNumber(cat.total_counted) }} {{ cat.unit }}
                                    </div>
                                </div>

                                <!-- Mini fill progress bar -->
                                <div class="w-full bg-slate-100 h-1.5 rounded-full overflow-hidden">
                                    <div
                                        :style="{ width: `${cat.fillRatio}%` }"
                                        :class="cat.isActive ? 'bg-amber-600' : 'bg-slate-400'"
                                        class="h-full rounded-full transition-all duration-500"
                                    ></div>
                                </div>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Detailed Table of Category Items -->
                <div v-if="activeRefCategory && (refViewMode === 'both' || refViewMode === 'table')" class="space-y-3 pt-2">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xs font-black text-slate-900 uppercase tracking-wider flex items-center gap-2">
                            <i class="fa-solid fa-table-list text-amber-600"></i>
                            <span>Tabel Rincian Seluruh Butir Referensi</span>
                        </h3>
                        <span class="text-[11px] font-bold text-slate-400">
                            Menampilkan {{ filteredRefItems.length }} dari {{ activeRefCategory.total_items }} butir
                        </span>
                    </div>

                    <div class="overflow-x-auto rounded-2xl border border-slate-200/80">
                        <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50/90 text-slate-600 uppercase text-[10px] font-bold tracking-wider border-b border-slate-200">
                            <tr>
                                <th class="py-3 px-3.5 text-center w-12">No.</th>
                                <th class="py-3 px-3.5">Nama Item Referensi</th>
                                <th class="py-3 px-3.5 text-right w-32">Jumlah ({{ activeRefCategory.unit }})</th>
                                <th class="py-3 px-3.5 text-right w-24">Persentase</th>
                                <th class="py-3 px-3.5 w-48 sm:w-64">Distribusi Visual</th>
                                <th class="py-3 px-3.5 text-center w-24">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr
                                v-for="(item, idx) in filteredRefItems"
                                :key="item.id || item.kode || idx"
                                :class="['transition hover:bg-amber-50/40', item.count > 0 ? 'bg-white' : 'bg-slate-50/30']"
                            >
                                <td class="py-3 px-3.5 text-center font-bold text-slate-400">
                                    {{ idx + 1 }}
                                </td>
                                <td class="py-3 px-3.5 font-bold text-slate-900">
                                    <div class="flex items-center gap-2">
                                        <div
                                            :class="[
                                                'w-2 h-2 rounded-full shrink-0',
                                                item.count > 0 ? 'bg-amber-500' : 'bg-slate-300'
                                            ]"
                                        ></div>
                                        <span>{{ item.nama }}</span>
                                    </div>
                                </td>
                                <td class="py-3 px-3.5 text-right font-black" :class="item.count > 0 ? 'text-amber-700 text-sm' : 'text-slate-400'">
                                    {{ formatNumber(item.count) }}
                                    <span class="text-[10px] font-medium text-slate-400 ml-0.5">{{ activeRefCategory.unit }}</span>
                                </td>
                                <td class="py-3 px-3.5 text-right font-bold" :class="item.count > 0 ? 'text-slate-800' : 'text-slate-400'">
                                    {{ item.percentage }}%
                                </td>
                                <td class="py-3 px-3.5">
                                    <div class="flex items-center gap-2">
                                        <div class="h-2.5 w-full rounded-full bg-slate-100 overflow-hidden relative">
                                            <div
                                                :style="{ width: `${item.percentage}%` }"
                                                :class="[
                                                    'h-full rounded-full transition-all duration-500',
                                                    item.percentage > 20 ? 'bg-gradient-to-r from-amber-500 to-amber-600' : 'bg-amber-500'
                                                ]"
                                            ></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 px-3.5 text-center">
                                    <span
                                        v-if="item.count > 0"
                                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200"
                                    >
                                        <i class="fa-solid fa-check text-[9px]"></i>
                                        <span>Terisi</span>
                                    </span>
                                    <span
                                        v-else
                                        class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-400 border border-slate-200"
                                    >
                                        Nol (0)
                                    </span>
                                </td>
                            </tr>
                            <tr v-if="filteredRefItems.length === 0">
                                <td colspan="6" class="py-10 text-center">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <div class="w-10 h-10 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center text-base">
                                            <i class="fa-solid fa-magnifying-glass"></i>
                                        </div>
                                        <div class="text-xs font-bold text-slate-600">Tidak ada butir referensi yang cocok</div>
                                        <div class="text-[11px] text-slate-400 max-w-sm">
                                            Coba sesuaikan kata kunci pencarian atau hilangkan centang "Hanya yang terisi".
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</AppLayout>
</template>
