<script setup>
import { computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';

const props = defineProps({
    status: {
        type: [Number, String],
        default: 404,
    },
    message: {
        type: String,
        default: '',
    },
});

const page = usePage();
const authUser = computed(() => page.props.auth?.user);

const userDashboardUrl = computed(() => {
    if (!authUser.value) return '/login';
    const roleSlug = authUser.value.role?.slug || authUser.value.role?.nama_role || 'superadmin';
    const clean = roleSlug.toLowerCase().replace(/[^a-z0-9]/g, '');
    const prefixMap = {
        superadmin: '/superadmin',
        adminparoki: '/paroki',
        paroki: '/paroki',
        pastor: '/pastor',
        pastorparoki: '/pastor',
        adminwilayah: '/wilayah',
        wilayah: '/wilayah',
        adminkapela: '/kapela',
        kapela: '/kapela',
        stasi: '/kapela',
        ketuakub: '/kub',
        kub: '/kub',
        bendahara: '/bendahara',
        penulis: '/penulis',
        umat: '/umat',
    };
    return prefixMap[clean] || '/superadmin';
});

const errorDetails = computed(() => {
    const code = Number(props.status) || 404;
    switch (code) {
        case 403:
            return {
                code: 403,
                title: '403 - Akses Ditolak',
                heading: 'Akses Dibatasi / Ditolak',
                description: props.message || 'Anda tidak memiliki hak akses atau wewenang yang memadai untuk membuka halaman ini. Pastikan Anda telah masuk dengan akun yang memiliki hak akses yang sesuai.',
                icon: 'fa-solid fa-shield-halved',
                iconColor: 'text-rose-400',
            };
        case 419:
            return {
                code: 419,
                title: '419 - Sesi Kedaluwarsa',
                heading: 'Sesi Keamanan Kedaluwarsa',
                description: props.message || 'Sesi keamanan halaman formulir Anda telah berakhir karena tidak ada aktivitas dalam beberapa waktu. Silakan muat ulang halaman ini atau login kembali.',
                icon: 'fa-solid fa-clock-rotate-left',
                iconColor: 'text-amber-400',
            };
        case 429:
            return {
                code: 429,
                title: '429 - Batas Permintaan Tercapai',
                heading: 'Terlalu Banyak Permintaan',
                description: props.message || 'Sistem mendeteksi terlalu banyak permintaan dalam waktu singkat dari perangkat Anda. Harap tunggu beberapa saat sebelum mencoba kembali.',
                icon: 'fa-solid fa-gauge-simple-high',
                iconColor: 'text-orange-400',
            };
        case 500:
            return {
                code: 500,
                title: '500 - Kesalahan Server',
                heading: 'Terjadi Kendala pada Server',
                description: props.message || 'Mohon maaf, sistem mengalami kendala pemrosesan internal pada permintaan Anda. Log aktivitas telah dicatat secara otomatis untuk ditinjau oleh administrator.',
                icon: 'fa-solid fa-triangle-exclamation',
                iconColor: 'text-rose-400',
            };
        case 503:
            return {
                code: 503,
                title: '503 - Layanan Pemeliharaan',
                heading: 'Sistem Dalam Pemeliharaan',
                description: props.message || 'Layanan SIPAROKI saat ini sedang dalam proses pemeliharaan berkala atau peningkatan kapasitas server paroki. Sistem akan segera kembali online.',
                icon: 'fa-solid fa-screwdriver-wrench',
                iconColor: 'text-teal-400',
            };
        case 404:
        default:
            return {
                code: 404,
                title: '404 - Halaman Tidak Ditemukan',
                heading: 'Halaman Tidak Ditemukan',
                description: props.message || 'Mohon maaf, halaman atau dokumen yang Anda cari tidak tersedia, telah dipindahkan, atau alamat URL yang Anda tuju kurang tepat. Silakan periksa kembali tautan Anda.',
                icon: 'fa-solid fa-compass-drafting',
                iconColor: 'text-amber-400',
            };
    }
});

const goBack = () => {
    if (window.history.length > 1) {
        window.history.back();
    } else {
        window.location.href = '/';
    }
};
</script>

<template>
    <div class="min-h-screen bg-gradient-to-br from-slate-950 via-slate-900 to-emerald-950 flex items-center justify-center p-6 relative overflow-hidden text-slate-100 font-sans">
        <Head :title="errorDetails.title" />

        <!-- Ambient Glow Elements -->
        <div class="absolute -top-32 -right-32 w-96 h-96 rounded-full bg-teal-500/10 blur-3xl pointer-events-none animate-pulse"></div>
        <div class="absolute -bottom-32 -left-32 w-96 h-96 rounded-full bg-amber-500/10 blur-3xl pointer-events-none animate-pulse" style="animation-delay: 1.5s;"></div>
        <div class="absolute inset-0 flex items-center justify-center pointer-events-none opacity-[0.02] text-[400px] font-serif select-none">
            ✝
        </div>

        <!-- Error Card -->
        <div class="relative z-10 w-full max-w-xl bg-slate-900/80 backdrop-blur-2xl border border-slate-700/60 rounded-3xl p-8 sm:p-12 text-center shadow-2xl shadow-black/60">
            <!-- Badge Header -->
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-teal-500/10 border border-teal-500/30 text-teal-400 text-xs font-bold uppercase tracking-wider mb-6">
                <i class="fa-solid fa-church"></i>
                <span>SIPAROKI Digital</span>
            </div>

            <!-- Error Code with Floating Badge -->
            <div class="relative inline-block mb-3">
                <h1 class="text-8xl sm:text-9xl font-black tracking-tight leading-none bg-gradient-to-r from-amber-400 via-amber-200 to-teal-400 bg-clip-text text-transparent drop-shadow-md">
                    {{ errorDetails.code }}
                </h1>
                <div class="absolute -top-2 -right-4 w-12 h-12 rounded-2xl bg-slate-800/90 border border-slate-600/60 flex items-center justify-center text-xl shadow-lg shadow-black/40 animate-bounce" style="animation-duration: 3s;">
                    <i :class="[errorDetails.icon, errorDetails.iconColor]"></i>
                </div>
            </div>

            <!-- Error Heading & Description -->
            <h2 class="text-2xl sm:text-3xl font-extrabold text-white mb-3">
                {{ errorDetails.heading }}
            </h2>
            <p class="text-sm text-slate-400 leading-relaxed max-w-md mx-auto mb-8">
                {{ errorDetails.description }}
            </p>

            <!-- Action Buttons Group -->
            <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
                <Link
                    href="/"
                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-slate-950 font-bold text-xs sm:text-sm shadow-lg shadow-amber-500/25 transition-all duration-200 cursor-pointer"
                >
                    <i class="fa-solid fa-house"></i>
                    <span>Kembali ke Beranda</span>
                </Link>

                <button
                    type="button"
                    @click="goBack"
                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-slate-800/80 hover:bg-slate-700/80 border border-slate-700 text-slate-200 font-bold text-xs sm:text-sm transition-all duration-200 cursor-pointer"
                >
                    <i class="fa-solid fa-arrow-left"></i>
                    <span>Halaman Sebelumnya</span>
                </button>

                <Link
                    v-if="authUser"
                    :href="userDashboardUrl"
                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-slate-800/80 hover:bg-slate-700/80 border border-slate-700 text-teal-300 font-bold text-xs sm:text-sm transition-all duration-200 cursor-pointer"
                >
                    <i class="fa-solid fa-gauge-high"></i>
                    <span>Dashboard</span>
                </Link>
                <Link
                    v-else
                    href="/login"
                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-slate-800/80 hover:bg-slate-700/80 border border-slate-700 text-teal-300 font-bold text-xs sm:text-sm transition-all duration-200 cursor-pointer"
                >
                    <i class="fa-solid fa-lock"></i>
                    <span>Login Petugas</span>
                </Link>
            </div>

            <!-- Footer Note -->
            <div class="mt-10 pt-5 border-t border-slate-800/80 flex flex-col sm:flex-row items-center justify-between gap-2 text-[11px] text-slate-500">
                <span>SIPAROKI &copy; {{ new Date().getFullYear() }} Sistem Informasi Manajemen Paroki</span>
                <span>Kode Status: <strong class="text-slate-400">HTTP-{{ errorDetails.code }}</strong></span>
            </div>
        </div>
    </div>
</template>
