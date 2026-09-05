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

                <!-- Detailed Table of Category Items -->
                <div v-if="activeRefCategory" class="overflow-x-auto rounded-2xl border border-slate-200/80">
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
    </AppLayout>
</template>
