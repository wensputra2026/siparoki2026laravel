<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const isReloading = ref(false);
const reloadStats = () => {
    isReloading.value = true;
    router.reload({
        preserveScroll: true,
        preserveState: true,
        onFinish: () => {
            isReloading.value = false;
        },
    });
};

const props = defineProps({
    role: { type: String, default: 'Super Admin' },
    prefix: { type: String, default: 'superadmin' },
    paroki: { type: Object, default: () => ({}) },
    summary: { type: Object, default: () => ({}) },
    genderStats: { type: Object, default: () => ({ pria: 0, wanita: 0, total: 0 }) },
    usiaStats: { type: Array, default: () => [] },
    sakramenStats: { type: Object, default: () => ({}) },
    wilayahStats: { type: Array, default: () => [] },
    pekerjaanStats: { type: Array, default: () => [] },
    pendidikanStats: { type: Array, default: () => [] },
    statusKawinStats: { type: Array, default: () => [] },
});

const activeTab = ref('ringkasan'); // 'ringkasan', 'usia', 'sakramen', 'wilayah', 'sosial'

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
    <AppLayout>
        <Head title="Demografi & Statistik Paroki - SIPAROKI" />

        <div class="w-full space-y-6 pb-12">
            <!-- 1. EXECUTIVE HEADER BANNER -->
            <div class="bg-gradient-to-r from-amber-600 via-amber-500 to-amber-600 rounded-3xl p-6 sm:p-8 text-white shadow-xl shadow-amber-500/15 relative overflow-hidden">
                <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
                <div class="absolute right-20 top-0 w-32 h-32 bg-amber-300/20 rounded-full blur-xl pointer-events-none"></div>

                <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div class="space-y-2">
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/15 backdrop-blur-md text-amber-100 text-xs font-semibold border border-white/20">
                            <i class="fa-solid fa-chart-pie"></i>
                            <span>Sistem Informasi Statistik Pastoral & Demografi Umat</span>
                        </div>
                        <h1 class="text-2xl sm:text-3xl font-black tracking-tight">
                            Demografi & Statistik Paroki
                        </h1>
                        <p class="text-amber-100 text-xs sm:text-sm max-w-2xl leading-relaxed">
                            {{ paroki.nama_paroki || 'Paroki St. Vinsensius a Paulo Benlutu' }} • Monitoring sebaran umat, piramida usia, statistik sakramen, dan profil sosial ekonomi secara real-time.
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
                            :href="`/${prefix}/statistik/export/excel`"
                            class="px-4 py-2.5 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-md shadow-emerald-900/20 transition flex items-center gap-2 cursor-pointer"
                        >
                            <i class="fa-solid fa-file-excel"></i>
                            <span>Ekspor Excel</span>
                        </a>

                        <!-- Cetak / PDF -->
                        <a
                            :href="`/${prefix}/statistik/export/print`"
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
                <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-xs hover:shadow-md transition">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-500">Komunitas Umat (KUB)</span>
                        <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm shadow-2xs">
                            <i class="fa-solid fa-people-group"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <span class="text-2xl font-black text-slate-900">{{ formatNumber(summary.totalKUB) }}</span>
                        <span class="text-xs font-semibold text-slate-400 ml-1">KUB</span>
                    </div>
                    <div class="mt-2 text-[11px] text-slate-400">
                        Basis Komunitas Umat
                    </div>
                </div>

                <!-- Total Wilayah -->
                <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-xs hover:shadow-md transition">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-500">Wilayah Rohani</span>
                        <div class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-sm shadow-2xs">
                            <i class="fa-solid fa-map-location-dot"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <span class="text-2xl font-black text-slate-900">{{ formatNumber(summary.totalWilayah) }}</span>
                        <span class="text-xs font-semibold text-slate-400 ml-1">Wilayah</span>
                    </div>
                    <div class="mt-2 text-[11px] text-slate-400">
                        Wilayah Pelayanan Paroki
                    </div>
                </div>

                <!-- Total Kapela / Stasi -->
                <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-xs hover:shadow-md transition">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-500">Stasi / Kapela</span>
                        <div class="w-9 h-9 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-sm shadow-2xs">
                            <i class="fa-solid fa-place-of-worship"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <span class="text-2xl font-black text-slate-900">{{ formatNumber(summary.totalKapela) }}</span>
                        <span class="text-xs font-semibold text-slate-400 ml-1">Stasi</span>
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
                            <span>Penerimaan Sakramen Umat Paroki</span>
                        </h3>
                        <p class="text-[11px] text-slate-400">Persentase dan status inisiasi kristiani umat paroki</p>
                    </div>
                    <Link :href="`/${prefix}/sakramen`" class="text-xs font-bold text-amber-600 hover:text-amber-700 underline">
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
                        <p class="text-[11px] text-slate-500">Tercatat dalam Buku Baptis Paroki</p>
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

            <!-- 5. SEBARAN UMAT PER WILAYAH & KUB -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                <!-- WILAYAH BREAKDOWN TABLE -->
                <div class="lg:col-span-7 bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                            <i class="fa-solid fa-map-location-dot text-amber-600"></i>
                            <span>Distribusi Umat per Wilayah & KUB</span>
                        </h3>
                        <Link :href="`/${prefix}/wilayah`" class="text-xs font-bold text-amber-600 hover:text-amber-700 underline">
                            Data Wilayah &rarr;
                        </Link>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 text-slate-600 uppercase text-[10px] font-bold tracking-wider">
                                <tr>
                                    <th class="py-2.5 px-3 rounded-l-xl">Nama Wilayah</th>
                                    <th class="py-2.5 px-3 text-center">Jumlah KUB</th>
                                    <th class="py-2.5 px-3 text-right">Jumlah KK</th>
                                    <th class="py-2.5 px-3 text-right rounded-r-xl">Total Jiwa</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="w in wilayahStats" :key="w.id || w.nama_wilayah" class="hover:bg-slate-50/60 transition">
                                    <td class="py-3 px-3 font-bold text-slate-900 flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-lg bg-amber-50 text-amber-700 flex items-center justify-center text-[10px]">
                                            <i class="fa-solid fa-cross"></i>
                                        </div>
                                        <span>{{ w.nama_wilayah }}</span>
                                    </td>
                                    <td class="py-3 px-3 text-center font-bold text-slate-600">{{ w.kub_count || 0 }} KUB</td>
                                    <td class="py-3 px-3 text-right font-bold text-slate-700">{{ formatNumber(w.kk_count) }}</td>
                                    <td class="py-3 px-3 text-right font-black text-amber-700">{{ formatNumber(w.umat_count) }}</td>
                                </tr>
                                <tr v-if="!wilayahStats.length">
                                    <td colspan="4" class="py-6 text-center text-slate-400">Belum ada data wilayah tersinkronisasi</td>
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
        </div>
    </AppLayout>
</template>
