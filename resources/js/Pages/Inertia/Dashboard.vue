<script setup>
import { ref, computed } from 'vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const page = usePage();
const isReloading = ref(false);

const reloadDashboard = () => {
    isReloading.value = true;
    router.reload({
        only: ['stats', 'latestUmat', 'sakramenCount'],
        preserveScroll: true,
        preserveState: true,
        onFinish: () => {
            isReloading.value = false;
        },
    });
};

defineProps({
    stats: {
        type: Array,
        default: () => [],
    },
    latestUmat: {
        type: Array,
        default: () => [],
    },
    sakramenCount: {
        type: Object,
        default: () => ({}),
    },
    role: {
        type: String,
        default: 'Super Admin',
    },
});

const todayDate = computed(() => {
    return new Intl.DateTimeFormat('id-ID', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    }).format(new Date());
});

const activeParokiName = computed(() => {
    return page.props.app?.nama_paroki || 'Paroki St. Vinsensius a Paulo - Benlutu';
});

const activeRoleName = computed(() => {
    return page.props.role || 'Super Admin';
});

const basePrefix = computed(() => {
    const parts = page.url.split('?')[0].split('/').filter(Boolean);
    return parts.length > 0 ? '/' + parts[0] : '/superadmin';
});

const isSuperAdminRole = computed(() => {
    const r = String(props.role || page.props.role || '').toLowerCase();
    const p = basePrefix.value.toLowerCase();
    return r.includes('superadmin') || r.includes('super admin') || p === '/superadmin' || p === '/v2' || p === '/admin';
});
</script>

<template>
    <AppLayout title="Dashboard" :role="role">
        <Head title="Dashboard - SIPAROKI" />

        <!-- EXECUTIVE GREETING HEADER CARD -->
        <div class="rounded-3xl bg-white border border-slate-200/90 p-6 sm:p-7 shadow-xs mb-6 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <!-- Left Side: Icon + Title + Info Badges -->
            <div class="flex items-start sm:items-center gap-4 min-w-0">
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-amber-500 to-amber-400 text-white flex items-center justify-center text-2xl font-black shadow-md shadow-amber-500/20 shrink-0">
                    <i class="fa-solid fa-church"></i>
                </div>
                <div class="min-w-0 space-y-1">
                    <div class="flex flex-wrap items-center gap-2.5">
                        <h1 class="text-xl sm:text-2xl font-black tracking-tight text-slate-900 leading-tight">
                            Selamat Datang, {{ activeRoleName }}
                        </h1>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-[10.5px] font-bold">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span>Online</span>
                        </span>
                    </div>
                    <p class="text-xs sm:text-sm text-slate-600 font-medium truncate">
                        Sistem Informasi Manajemen Pastoral &bull; <b class="text-amber-900 font-bold">{{ activeParokiName }}</b>
                    </p>
                    <div class="flex items-center gap-2 text-[11px] text-slate-400 pt-0.5 font-medium">
                        <i class="fa-regular fa-calendar text-amber-600"></i>
                        <span>{{ todayDate }}</span>
                    </div>
                </div>
            </div>

            <!-- Right Side: Quick Action Buttons -->
            <div class="flex flex-wrap items-center gap-2.5 shrink-0">
                <button
                    type="button"
                    :disabled="isReloading"
                    @click="reloadDashboard"
                    title="Reload data statistik dan ringkasan dashboard"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-50 hover:bg-slate-100 border border-slate-200 text-slate-700 font-bold text-xs transition shadow-2xs cursor-pointer disabled:opacity-60"
                >
                    <i :class="['fa-solid fa-arrows-rotate text-blue-600', isReloading ? 'fa-spin' : '']"></i>
                    <span>{{ isReloading ? 'Memuat...' : 'Reload' }}</span>
                </button>

                <Link
                    v-if="isSuperAdminRole"
                    :href="`${basePrefix}/profil-paroki`"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-50 hover:bg-slate-100 border border-slate-200 text-slate-700 font-bold text-xs transition shadow-2xs cursor-pointer"
                >
                    <i class="fa-solid fa-church text-amber-600"></i>
                    <span>Profil Paroki</span>
                </Link>
                <Link
                    :href="`${basePrefix}/umat`"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white font-bold text-xs transition shadow-sm shadow-amber-500/25 cursor-pointer"
                >
                    <i class="fa-solid fa-users"></i>
                    <span>Data Umat</span>
                </Link>
            </div>
        </div>

        <!-- Metric Stat Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-8">
            <div
                v-for="stat in stats"
                :key="stat.title"
                class="rounded-2xl bg-white border border-slate-200/80 p-5 shadow-xs hover:shadow-md hover:border-slate-300 transition-all duration-200 group"
            >
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500">{{ stat.title }}</span>
                    <div class="w-10 h-10 rounded-xl bg-amber-50 border border-amber-200/60 flex items-center justify-center text-amber-600 group-hover:scale-110 transition-transform">
                        <i :class="['fa-solid text-base', stat.icon]"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
                        {{ Number(stat.value).toLocaleString() }}
                    </div>
                    <div class="mt-1 flex items-center gap-1.5 text-xs text-slate-500">
                        <span class="text-emerald-600 font-semibold">{{ stat.change }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Two Column Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Latest Umat List -->
            <div class="lg:col-span-2 rounded-2xl bg-white border border-slate-200/80 p-6 shadow-xs">
                <div class="flex items-center justify-between mb-5">
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Umat Terbaru Terdaftar</h3>
                        <p class="text-xs text-slate-500">6 data umat terakhir yang masuk ke dalam sistem</p>
                    </div>
                    <Link
                        :href="`${basePrefix}/umat`"
                        prefetch
                        class="text-xs font-bold text-amber-600 hover:text-amber-700 transition"
                    >
                        Lihat Semua &rarr;
                    </Link>
                </div>

                <div class="divide-y divide-slate-100">
                    <div
                        v-for="umat in latestUmat"
                        :key="umat.id"
                        class="py-3.5 flex items-center justify-between gap-3 group"
                    >
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-9 h-9 rounded-full bg-slate-100 border border-slate-200 flex items-center justify-center text-amber-700 font-bold text-xs shrink-0 group-hover:border-amber-400 transition">
                                {{ umat.nama_lengkap.charAt(0).toUpperCase() }}
                            </div>
                            <div class="min-w-0">
                                <h4 class="text-sm font-semibold text-slate-800 truncate group-hover:text-amber-600 transition">
                                    {{ umat.nama_lengkap }}
                                </h4>
                                <p class="text-xs text-slate-500 truncate">
                                    {{ umat.jenis_kelamin }} · Terdaftar {{ umat.created_at ? new Date(umat.created_at).toLocaleDateString('id-ID') : '-' }}
                                </p>
                            </div>
                        </div>
                        <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 shrink-0">
                            {{ umat.status_umat || 'Aktif' }}
                        </span>
                    </div>

                    <div v-if="!latestUmat.length" class="py-8 text-center text-sm text-slate-400">
                        Belum ada data umat.
                    </div>
                </div>
            </div>

            <!-- Sakramen Overview Card -->
            <div class="rounded-2xl bg-white border border-slate-200/80 p-6 shadow-xs space-y-6">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Ringkasan Sakramen</h3>
                    <p class="text-xs text-slate-500">Data sakramen yang tercatat di paroki</p>
                </div>

                <div class="space-y-3">
                    <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-100">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center text-sm">
                                <i class="fa-solid fa-water"></i>
                            </div>
                            <span class="text-xs font-semibold text-slate-700">Baptis</span>
                        </div>
                        <span class="font-bold text-sm text-slate-900">{{ sakramenCount.baptis || 0 }}</span>
                    </div>

                    <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-100">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center text-sm">
                                <i class="fa-solid fa-bread-slice"></i>
                            </div>
                            <span class="text-xs font-semibold text-slate-700">Komuni Pertama</span>
                        </div>
                        <span class="font-bold text-sm text-slate-900">{{ sakramenCount.komuni || 0 }}</span>
                    </div>

                    <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-100">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-rose-100 text-rose-700 flex items-center justify-center text-sm">
                                <i class="fa-solid fa-fire"></i>
                            </div>
                            <span class="text-xs font-semibold text-slate-700">Krisma</span>
                        </div>
                        <span class="font-bold text-sm text-slate-900">{{ sakramenCount.krisma || 0 }}</span>
                    </div>

                    <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-100">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-pink-100 text-pink-700 flex items-center justify-center text-sm">
                                <i class="fa-solid fa-heart"></i>
                            </div>
                            <span class="text-xs font-semibold text-slate-700">Pernikahan</span>
                        </div>
                        <span class="font-bold text-sm text-slate-900">{{ sakramenCount.perkawinan || 0 }}</span>
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-100">
                    <a
                        href="/pelayanan"
                        class="w-full flex items-center justify-center gap-2 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-xs font-bold text-slate-700 transition"
                    >
                        <i class="fa-solid fa-arrow-right text-[10px]"></i>
                        <span>Informasi Pelayanan Sakramen</span>
                    </a>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
