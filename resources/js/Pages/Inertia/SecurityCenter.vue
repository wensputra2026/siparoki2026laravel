<script setup>
import { ref, computed } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    role: { type: String, default: 'Super Admin' },
    prefix: { type: String, default: 'superadmin' },
    settings: { type: Object, default: () => ({}) },
    blockedIps: { type: Array, default: () => [] },
    logs: { type: Array, default: () => [] },
    auditChecks: { type: Array, default: () => [] },
    securityScore: { type: Number, default: 95 },
    todayFailed: { type: Number, default: 0 },
    todaySuccess: { type: Number, default: 0 },
    totalBlocked: { type: Number, default: 0 },
    currentIp: { type: String, default: '127.0.0.1' },
    phpVersion: { type: String, default: '' },
    laravelVersion: { type: String, default: '' },
});

const isReloading = ref(false);
const reloadSecurity = () => {
    isReloading.value = true;
    router.reload({
        preserveScroll: true,
        preserveState: true,
        onFinish: () => {
            isReloading.value = false;
        },
    });
};

const activeTab = ref('audit'); // 'audit' | 'firewall' | 'logs' | 'policies'

// Forms
const blockIpModal = ref(false);
const blockIpForm = useForm({
    ip_address: '',
    reason: '',
    blocked_duration: 24, // hours
});

const policyForm = useForm({
    max_login_attempts: props.settings.max_login_attempts ?? 5,
    lockout_minutes: props.settings.lockout_minutes ?? 60,
    session_timeout_minutes: props.settings.session_timeout_minutes ?? 120,
    force_strong_password: props.settings.force_strong_password ?? true,
    enable_brute_force_protection: props.settings.enable_brute_force_protection ?? true,
    block_untrusted_ip: props.settings.block_untrusted_ip ?? false,
});

// CAPTCHA Form
const showCaptchaSecret = ref(false);
const captchaForm = useForm({
    captcha_enabled: Boolean(props.settings.captcha_enabled),
    captcha_provider: props.settings.captcha_provider || 'Simple CAPTCHA',
    captcha_site_key: props.settings.captcha_site_key || '',
    captcha_secret_key: props.settings.captcha_secret_key || '',
    captcha_show_after_failed_attempts: props.settings.captcha_show_after_failed_attempts ?? 3,
    captcha_required_backend_login: props.settings.captcha_required_backend_login !== false,
    captcha_required_umat_login: Boolean(props.settings.captcha_required_umat_login),
    captcha_required_forgot_password: props.settings.captcha_required_forgot_password !== false,
    captcha_required_public_forms: Boolean(props.settings.captcha_required_public_forms),
});

// 2FA Form
const twoFactorForm = useForm({
    two_factor_enabled: Boolean(props.settings.two_factor_enabled),
    two_factor_method: props.settings.two_factor_method || 'whatsapp_otp',
    two_factor_roles: props.settings.two_factor_roles || 'all_admins',
    two_factor_grace_period_days: props.settings.two_factor_grace_period_days ?? 0,
    two_factor_otp_expiry_minutes: props.settings.two_factor_otp_expiry_minutes ?? 5,
});

const isProcessing = ref(false);
const ipSearch = ref('');
const logSearch = ref('');
const logFilter = ref('ALL'); // 'ALL' | 'SUCCESS' | 'FAILED'

// Filtered Blocked IPs
const filteredBlockedIps = computed(() => {
    if (!ipSearch.value) return props.blockedIps;
    const q = ipSearch.value.toLowerCase();
    return props.blockedIps.filter(
        item =>
            (item.ip_address && item.ip_address.toLowerCase().includes(q)) ||
            (item.reason && item.reason.toLowerCase().includes(q)) ||
            (item.blocked_by && item.blocked_by.toLowerCase().includes(q))
    );
});

// Filtered Security Logs
const filteredLogs = computed(() => {
    let list = props.logs;
    if (logFilter.value !== 'ALL') {
        list = list.filter(l => l.status === logFilter.value);
    }
    if (logSearch.value) {
        const q = logSearch.value.toLowerCase();
        list = list.filter(
            l =>
                (l.ip_address && l.ip_address.toLowerCase().includes(q)) ||
                (l.username && l.username.toLowerCase().includes(q)) ||
                (l.details && l.details.toLowerCase().includes(q)) ||
                (l.user_agent && l.user_agent.toLowerCase().includes(q))
        );
    }
    return list;
});

// Actions
const submitBlockIp = () => {
    if (!blockIpForm.ip_address) {
        alert('Silakan masukkan alamat IP.');
        return;
    }
    isProcessing.value = true;
    blockIpForm.post(`/${props.prefix}/security/block-ip`, {
        preserveScroll: true,
        onSuccess: () => {
            blockIpModal.value = false;
            blockIpForm.reset();
        },
        onFinish: () => {
            isProcessing.value = false;
        },
    });
};

const handleUnblock = (item) => {
    if (confirm(`Lepaskan blokir untuk alamat IP ${item.ip_address}?`)) {
        router.post(`/${props.prefix}/security/unblock-ip/${item.id}`, {}, {
            preserveScroll: true,
        });
    }
};

const submitPolicies = () => {
    isProcessing.value = true;
    policyForm.post(`/${props.prefix}/security/settings`, {
        preserveScroll: true,
        onFinish: () => {
            isProcessing.value = false;
        },
    });
};

const submitCaptcha = () => {
    isProcessing.value = true;
    captchaForm.post(`/${props.prefix}/security/settings`, {
        preserveScroll: true,
        onFinish: () => {
            isProcessing.value = false;
        },
    });
};

const submitTwoFactor = () => {
    isProcessing.value = true;
    twoFactorForm.post(`/${props.prefix}/security/settings`, {
        preserveScroll: true,
        onFinish: () => {
            isProcessing.value = false;
        },
    });
};

const handleClearLogs = () => {
    if (confirm('PERINGATAN: Apakah Anda yakin ingin membersihkan seluruh riwayat log keamanan?')) {
        router.post(`/${props.prefix}/security/clear-logs`, {}, {
            preserveScroll: true,
        });
    }
};

const handleClearCache = () => {
    if (confirm('Reload seluruh cache sistem, konfigurasi, dan sesi keamanan?')) {
        router.post(`/${props.prefix}/security/clear-cache`, {}, {
            preserveScroll: true,
        });
    }
};

const formatDate = (dateStr) => {
    if (!dateStr) return '-';
    try {
        const d = new Date(dateStr);
        return d.toLocaleString('id-ID', {
            day: '2-digit',
            month: 'short',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        });
    } catch {
        return dateStr;
    }
};
</script>

<template>
    <AppLayout>
        <Head title="Security Center & Keamanan - SIPAROKI" />

        <div class="w-full space-y-6 pb-12">

            <!-- 1. EXECUTIVE HEADER BANNER -->
            <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 rounded-3xl p-6 sm:p-8 text-white shadow-xl shadow-indigo-950/20 relative overflow-hidden">
                <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
                <div class="absolute right-20 top-0 w-32 h-32 bg-indigo-500/20 rounded-full blur-xl pointer-events-none"></div>

                <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div class="space-y-2">
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/15 backdrop-blur-md text-emerald-300 text-xs font-semibold border border-white/20">
                            <i class="fa-solid fa-shield-check"></i>
                            <span>Sistem Pusat Keamanan &amp; Firewall Paroki</span>
                        </div>
                        <h1 class="text-2xl sm:text-3xl font-black tracking-tight flex items-center gap-3">
                            Security Center &amp; Keamanan
                            <span class="text-xs font-bold px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/40">
                                Active Protection
                            </span>
                        </h1>
                        <p class="text-slate-300 text-xs sm:text-sm max-w-2xl leading-relaxed">
                            Audit kerentanan server, proteksi brute-force login, pemantauan log audit real-time, dan manajemen firewall blokir IP penyerang.
                        </p>
                    </div>

                    <div class="flex items-center gap-2.5 flex-wrap shrink-0">
                        <button
                            type="button"
                            :disabled="isReloading"
                            @click="reloadSecurity"
                            class="px-4 py-2.5 rounded-2xl bg-white/20 hover:bg-white/30 backdrop-blur-md text-white text-xs font-bold border border-white/30 shadow-sm transition flex items-center gap-2 cursor-pointer disabled:opacity-60"
                            title="Reload Log & Data Keamanan dari Database"
                        >
                            <i :class="['fa-solid fa-arrows-rotate', isReloading ? 'fa-spin' : '']"></i>
                            <span>{{ isReloading ? 'Memuat...' : 'Reload' }}</span>
                        </button>
                        <button
                            @click="handleClearCache"
                            class="px-4 py-2.5 rounded-2xl bg-white/20 hover:bg-white/30 backdrop-blur-md text-white text-xs font-bold border border-white/30 shadow-sm transition flex items-center gap-2 cursor-pointer"
                            title="Bersihkan Cache & Sesi Keamanan Sistem"
                        >
                            <i class="fa-solid fa-broom"></i>
                            <span>Flush Cache</span>
                        </button>
                        <button
                            @click="blockIpModal = true"
                            class="px-4 py-2.5 rounded-2xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-black shadow-lg shadow-rose-600/30 transition flex items-center gap-2 cursor-pointer"
                        >
                            <i class="fa-solid fa-ban"></i>
                            <span>Blokir IP Baru</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- 2. SUMMARY METRIC CARDS -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                
                <!-- Security Score Card -->
                <div class="bg-white dark:bg-slate-800 rounded-3xl p-5 border border-slate-200/80 dark:border-slate-700/80 shadow-xs hover:shadow-md transition flex items-center justify-between">
                    <div>
                        <span class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Security Score</span>
                        <div class="flex items-baseline gap-2 mt-1">
                            <span class="text-3xl font-black text-emerald-600 dark:text-emerald-400">{{ securityScore }}%</span>
                            <span class="text-xs font-bold text-emerald-700 dark:text-emerald-300 bg-emerald-100 dark:bg-emerald-950/60 px-2 py-0.5 rounded-md">Grade A+</span>
                        </div>
                        <p class="text-[11px] text-slate-400 mt-1">Sistem terlindungi optimal</p>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-xl shadow-2xs">
                        <i class="fa-solid fa-shield-virus"></i>
                    </div>
                </div>

                <!-- Total Blocked IP -->
                <div class="bg-white dark:bg-slate-800 rounded-3xl p-5 border border-slate-200/80 dark:border-slate-700/80 shadow-xs hover:shadow-md transition flex items-center justify-between">
                    <div>
                        <span class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">IP Terblokir (Firewall)</span>
                        <div class="flex items-baseline gap-2 mt-1">
                            <span class="text-3xl font-black text-rose-600 dark:text-rose-400">{{ totalBlocked }}</span>
                            <span class="text-xs font-bold text-rose-700 dark:text-rose-300 bg-rose-100 dark:bg-rose-950/60 px-2 py-0.5 rounded-md">Blacklist</span>
                        </div>
                        <p class="text-[11px] text-slate-400 mt-1">IP Penyerang dicekal</p>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-rose-50 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400 flex items-center justify-center text-xl shadow-2xs">
                        <i class="fa-solid fa-ban"></i>
                    </div>
                </div>

                <!-- Failed Attempts Today -->
                <div class="bg-white dark:bg-slate-800 rounded-3xl p-5 border border-slate-200/80 dark:border-slate-700/80 shadow-xs hover:shadow-md transition flex items-center justify-between">
                    <div>
                        <span class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Gagal Login Hari Ini</span>
                        <div class="flex items-baseline gap-2 mt-1">
                            <span class="text-3xl font-black text-amber-600 dark:text-amber-400">{{ todayFailed }}</span>
                            <span class="text-xs font-bold text-amber-700 dark:text-amber-300 bg-amber-100 dark:bg-amber-950/60 px-2 py-0.5 rounded-md">Attempts</span>
                        </div>
                        <p class="text-[11px] text-slate-400 mt-1">Dipantau anti-bruteforce</p>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 flex items-center justify-center text-xl shadow-2xs">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                </div>

                <!-- Success Logins Today -->
                <div class="bg-white dark:bg-slate-800 rounded-3xl p-5 border border-slate-200/80 dark:border-slate-700/80 shadow-xs hover:shadow-md transition flex items-center justify-between">
                    <div>
                        <span class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Login Sukses Hari Ini</span>
                        <div class="flex items-baseline gap-2 mt-1">
                            <span class="text-3xl font-black text-sky-600 dark:text-sky-400">{{ todaySuccess }}</span>
                            <span class="text-xs font-bold text-sky-700 dark:text-sky-300 bg-sky-100 dark:bg-sky-950/60 px-2 py-0.5 rounded-md">Otorisasi</span>
                        </div>
                        <p class="text-[11px] text-slate-400 mt-1">Sesi user aktif &amp; sah</p>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-sky-50 dark:bg-sky-950/50 text-sky-600 dark:text-sky-400 flex items-center justify-center text-xl shadow-2xs">
                        <i class="fa-solid fa-user-check"></i>
                    </div>
                </div>

            </div>

            <!-- Current IP & Environment Banner -->
            <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-3.5 bg-slate-100 dark:bg-slate-800/80 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl text-xs text-slate-600 dark:text-slate-300">
                <div class="flex items-center gap-4 flex-wrap">
                    <span class="inline-flex items-center gap-1.5 font-medium">
                        <i class="fa-solid fa-network-wired text-indigo-500"></i>
                        IP Anda Saat Ini: <strong class="text-slate-900 dark:text-white">{{ currentIp }}</strong>
                    </span>
                    <span class="inline-flex items-center gap-1.5 font-medium">
                        <i class="fa-brands fa-php text-blue-500"></i>
                        PHP: <strong class="text-slate-900 dark:text-white">{{ phpVersion }}</strong>
                    </span>
                    <span class="inline-flex items-center gap-1.5 font-medium">
                        <i class="fa-brands fa-laravel text-rose-500"></i>
                        Laravel: <strong class="text-slate-900 dark:text-white">{{ laravelVersion }}</strong>
                    </span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/50 px-2.5 py-1 rounded-full border border-emerald-200 dark:border-emerald-800">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Monolith Secured
                    </span>
                </div>
            </div>

            <!-- 3. TAB NAVIGATION (Consistent with Statistik & GenericModule) -->
            <div class="flex items-center gap-2 overflow-x-auto pb-1 border-b border-slate-200/80">
                <button
                    @click="activeTab = 'audit'"
                    :class="[
                        'px-5 py-2.5 rounded-2xl text-xs font-bold transition flex items-center gap-2 shrink-0 cursor-pointer',
                        activeTab === 'audit'
                            ? 'bg-slate-900 text-white shadow-md shadow-slate-900/20 dark:bg-slate-100 dark:text-slate-900'
                            : 'bg-white hover:bg-slate-50 text-slate-600 border border-slate-200/80'
                    ]"
                >
                    <i class="fa-solid fa-stethoscope text-indigo-500"></i>
                    <span>Audit Keamanan Sistem</span>
                </button>

                <button
                    @click="activeTab = 'firewall'"
                    :class="[
                        'px-5 py-2.5 rounded-2xl text-xs font-bold transition flex items-center gap-2 shrink-0 cursor-pointer',
                        activeTab === 'firewall'
                            ? 'bg-slate-900 text-white shadow-md shadow-slate-900/20 dark:bg-slate-100 dark:text-slate-900'
                            : 'bg-white hover:bg-slate-50 text-slate-600 border border-slate-200/80'
                    ]"
                >
                    <i class="fa-solid fa-shield-halved text-rose-500"></i>
                    <span>Firewall &amp; Blokir IP</span>
                    <span v-if="totalBlocked > 0" class="text-[10px] bg-rose-500 text-white font-extrabold px-1.5 py-0.2 rounded-full">
                        {{ totalBlocked }}
                    </span>
                </button>

                <button
                    @click="activeTab = 'logs'"
                    :class="[
                        'px-5 py-2.5 rounded-2xl text-xs font-bold transition flex items-center gap-2 shrink-0 cursor-pointer',
                        activeTab === 'logs'
                            ? 'bg-slate-900 text-white shadow-md shadow-slate-900/20 dark:bg-slate-100 dark:text-slate-900'
                            : 'bg-white hover:bg-slate-50 text-slate-600 border border-slate-200/80'
                    ]"
                >
                    <i class="fa-solid fa-list-check text-amber-500"></i>
                    <span>Log Aktivitas &amp; Login</span>
                </button>

                <button
                    @click="activeTab = 'captcha'"
                    :class="[
                        'px-5 py-2.5 rounded-2xl text-xs font-bold transition flex items-center gap-2 shrink-0 cursor-pointer',
                        activeTab === 'captcha'
                            ? 'bg-slate-900 text-white shadow-md shadow-slate-900/20 dark:bg-slate-100 dark:text-slate-900'
                            : 'bg-white hover:bg-slate-50 text-slate-600 border border-slate-200/80'
                    ]"
                >
                    <i class="fa-solid fa-robot text-sky-500"></i>
                    <span>Proteksi Bot &amp; CAPTCHA</span>
                    <span v-if="captchaForm.captcha_enabled" class="text-[10px] bg-emerald-500 text-white font-extrabold px-1.5 py-0.2 rounded-full">
                        ON
                    </span>
                </button>

                <button
                    @click="activeTab = '2fa'"
                    :class="[
                        'px-5 py-2.5 rounded-2xl text-xs font-bold transition flex items-center gap-2 shrink-0 cursor-pointer',
                        activeTab === '2fa'
                            ? 'bg-slate-900 text-white shadow-md shadow-slate-900/20 dark:bg-slate-100 dark:text-slate-900'
                            : 'bg-white hover:bg-slate-50 text-slate-600 border border-slate-200/80'
                    ]"
                >
                    <i class="fa-solid fa-key text-purple-500"></i>
                    <span>Autentikasi 2FA</span>
                    <span v-if="twoFactorForm.two_factor_enabled" class="text-[10px] bg-purple-500 text-white font-extrabold px-1.5 py-0.2 rounded-full">
                        ON
                    </span>
                </button>

                <button
                    @click="activeTab = 'policies'"
                    :class="[
                        'px-5 py-2.5 rounded-2xl text-xs font-bold transition flex items-center gap-2 shrink-0 cursor-pointer',
                        activeTab === 'policies'
                            ? 'bg-slate-900 text-white shadow-md shadow-slate-900/20 dark:bg-slate-100 dark:text-slate-900'
                            : 'bg-white hover:bg-slate-50 text-slate-600 border border-slate-200/80'
                    ]"
                >
                    <i class="fa-solid fa-sliders text-teal-500"></i>
                    <span>Kebijakan Keamanan</span>
                </button>
            </div>

            <!-- Tab 1: Audit & Kesehatan Keamanan -->
            <div v-show="activeTab === 'audit'" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div
                        v-for="(check, idx) in auditChecks"
                        :key="idx"
                        class="bg-white dark:bg-slate-800 rounded-3xl p-6 border border-slate-200/80 dark:border-slate-700/80 shadow-xs flex flex-col justify-between"
                    >
                        <div>
                            <div class="flex items-center justify-between gap-2 mb-3">
                                <div class="w-10 h-10 rounded-2xl bg-slate-100 dark:bg-slate-700 flex items-center justify-center text-slate-700 dark:text-slate-200 shadow-2xs">
                                    <i :class="['fa-solid', check.icon]"></i>
                                </div>
                                <span
                                    :class="[
                                        'text-xs font-bold px-3 py-1 rounded-full border',
                                        check.status === 'PASS'
                                            ? 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-800'
                                            : (check.status === 'WARNING' || check.status === 'INFO'
                                                ? 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/60 dark:text-amber-300 dark:border-amber-800'
                                                : 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/60 dark:text-rose-300 dark:border-rose-800')
                                    ]"
                                >
                                    {{ check.badge }}
                                </span>
                            </div>
                            <h4 class="font-bold text-slate-900 dark:text-white text-sm mb-1.5">
                                {{ check.title }}
                            </h4>
                            <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                                {{ check.description }}
                            </p>
                        </div>

                        <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-700/60 flex items-center justify-between text-[11px]">
                            <span class="text-slate-400 font-medium">Status</span>
                            <span
                                :class="[
                                    'font-bold flex items-center gap-1',
                                    check.status === 'PASS' ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400'
                                ]"
                            >
                                <i :class="['fa-solid', check.status === 'PASS' ? 'fa-circle-check' : 'fa-triangle-exclamation']"></i>
                                {{ check.status === 'PASS' ? 'Lolos Pemeriksaan' : 'Pemberitahuan' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Rekomendasi Keamanan -->
                <div class="bg-gradient-to-br from-indigo-50 to-sky-50 dark:from-slate-800 dark:to-indigo-950/40 rounded-3xl p-6 sm:p-8 border border-indigo-100 dark:border-indigo-900/40 shadow-xs">
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-2xl bg-indigo-600 text-white flex items-center justify-center shrink-0 shadow-md">
                            <i class="fa-solid fa-lightbulb"></i>
                        </div>
                        <div class="space-y-1">
                            <h3 class="font-bold text-slate-900 dark:text-white text-sm">
                                Rekomendasi Pengamanan Sistem SIPAROKI
                            </h3>
                            <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed">
                                1. Pastikan selalu membuat cadangan rutin di menu <strong>Backup Database</strong> sebelum melakukan pembaruan besar.<br>
                                2. Atur batas toleransi gagal login maksimal <strong>5x</strong> untuk meminimalisir risiko serangan <i>Brute Force Dictionary Attack</i>.<br>
                                3. Periksa secara berkala tab <strong>Log Aktivitas</strong> untuk mendeteksi upaya login mencurigakan dari IP asing.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab 2: Firewall & Blokir IP -->
            <div v-show="activeTab === 'firewall'" class="space-y-4">
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
                    <div class="relative flex-1 max-w-md">
                        <i class="fa-solid fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                        <input
                            v-model="ipSearch"
                            type="text"
                            placeholder="Cari Alamat IP atau alasan blokir..."
                            class="w-full pl-9 pr-4 py-2.5 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-rose-500 focus:outline-none"
                        />
                    </div>
                    <button
                        @click="blockIpModal = true"
                        class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-2xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold shadow-md shadow-rose-600/20 transition cursor-pointer"
                    >
                        <i class="fa-solid fa-ban"></i>
                        <span>Tambah Blokir IP Manual</span>
                    </button>
                </div>

                <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200/80 dark:border-slate-700/80 shadow-xs overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 dark:bg-slate-900/60 text-slate-500 dark:text-slate-400 uppercase font-bold border-b border-slate-200 dark:border-slate-700">
                                <tr>
                                    <th class="py-3 px-4">Alamat IP</th>
                                    <th class="py-3 px-4">Alasan Pemblokiran</th>
                                    <th class="py-3 px-4">Diblokir Oleh</th>
                                    <th class="py-3 px-4">Waktu Blokir</th>
                                    <th class="py-3 px-4">Berlaku Sampai</th>
                                    <th class="py-3 px-4 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                                <tr v-if="filteredBlockedIps.length === 0">
                                    <td colspan="6" class="py-10 text-center text-slate-400">
                                        <i class="fa-solid fa-shield-check text-4xl text-emerald-400 mb-2 block"></i>
                                        Tidak ada alamat IP yang sedang diblokir saat ini.
                                    </td>
                                </tr>
                                <tr
                                    v-for="item in filteredBlockedIps"
                                    :key="item.id"
                                    class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition"
                                >
                                    <td class="py-3 px-4 font-mono font-bold text-rose-600 dark:text-rose-400">
                                        {{ item.ip_address }}
                                    </td>
                                    <td class="py-3 px-4 text-slate-700 dark:text-slate-300">
                                        {{ item.reason || 'Aktivitas mencurigakan' }}
                                    </td>
                                    <td class="py-3 px-4 text-slate-500 dark:text-slate-400">
                                        <span class="inline-flex items-center gap-1 font-semibold">
                                            <i class="fa-solid fa-user-shield text-[10px]"></i>
                                            {{ item.blocked_by || 'Auto-Firewall' }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-slate-500 dark:text-slate-400">
                                        {{ formatDate(item.created_at) }}
                                    </td>
                                    <td class="py-3 px-4">
                                        <span v-if="item.blocked_until" class="text-amber-600 dark:text-amber-400 font-semibold">
                                            {{ formatDate(item.blocked_until) }}
                                        </span>
                                        <span v-else class="text-rose-600 dark:text-rose-400 font-bold">
                                            Permanen
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <button
                                            @click="handleUnblock(item)"
                                            class="inline-flex items-center gap-1 px-3 py-1 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:hover:bg-emerald-900/80 dark:text-emerald-300 text-xs font-bold border border-emerald-200 dark:border-emerald-800 transition cursor-pointer"
                                            title="Lepas Blokir"
                                        >
                                            <i class="fa-solid fa-unlock text-[10px]"></i>
                                            <span>Lepas Blokir</span>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Tab 3: Log Aktivitas & Percobaan Login -->
            <div v-show="activeTab === 'logs'" class="space-y-4">
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
                    <div class="flex items-center gap-2 flex-1 max-w-xl">
                        <div class="relative flex-1">
                            <i class="fa-solid fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                            <input
                                v-model="logSearch"
                                type="text"
                                placeholder="Cari IP, username, browser..."
                                class="w-full pl-9 pr-4 py-2.5 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-amber-500 focus:outline-none"
                            />
                        </div>
                        <select
                            v-model="logFilter"
                            class="px-4 py-2.5 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs text-slate-900 dark:text-white font-semibold focus:outline-none"
                        >
                            <option value="ALL">Semua Status</option>
                            <option value="SUCCESS">Hanya Sukses</option>
                            <option value="FAILED">Hanya Gagal</option>
                        </select>
                    </div>

                    <button
                        @click="handleClearLogs"
                        class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-2xl bg-slate-200 hover:bg-slate-300 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 text-xs font-bold transition cursor-pointer"
                    >
                        <i class="fa-solid fa-trash-can text-rose-500"></i>
                        <span>Bersihkan Seluruh Log</span>
                    </button>
                </div>

                <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200/80 dark:border-slate-700/80 shadow-xs overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 dark:bg-slate-900/60 text-slate-500 dark:text-slate-400 uppercase font-bold border-b border-slate-200 dark:border-slate-700">
                                <tr>
                                    <th class="py-3 px-4">Waktu</th>
                                    <th class="py-3 px-4">Username / Email</th>
                                    <th class="py-3 px-4">Alamat IP</th>
                                    <th class="py-3 px-4">Status</th>
                                    <th class="py-3 px-4">Keterangan / Kejadian</th>
                                    <th class="py-3 px-4">User Agent / Browser</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                                <tr v-if="filteredLogs.length === 0">
                                    <td colspan="6" class="py-10 text-center text-slate-400">
                                        <i class="fa-solid fa-inbox text-4xl text-slate-300 mb-2 block"></i>
                                        Tidak ada catatan log aktivitas yang sesuai.
                                    </td>
                                </tr>
                                <tr
                                    v-for="log in filteredLogs"
                                    :key="log.id"
                                    class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition"
                                >
                                    <td class="py-3 px-4 text-slate-500 dark:text-slate-400 whitespace-nowrap">
                                        {{ formatDate(log.created_at) }}
                                    </td>
                                    <td class="py-3 px-4 font-bold text-slate-900 dark:text-white">
                                        {{ log.username || '-' }}
                                    </td>
                                    <td class="py-3 px-4 font-mono text-slate-700 dark:text-slate-300 whitespace-nowrap">
                                        {{ log.ip_address }}
                                    </td>
                                    <td class="py-3 px-4 whitespace-nowrap">
                                        <span
                                            :class="[
                                                'inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full font-bold text-[11px]',
                                                log.status === 'SUCCESS'
                                                    ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300'
                                                    : 'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300'
                                            ]"
                                        >
                                            <i :class="['fa-solid', log.status === 'SUCCESS' ? 'fa-check' : 'fa-xmark']"></i>
                                            {{ log.status }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-slate-600 dark:text-slate-300 max-w-xs truncate">
                                        {{ log.details || '-' }}
                                    </td>
                                    <td class="py-3 px-4 text-slate-400 max-w-xs truncate text-[11px]" :title="log.user_agent">
                                        {{ log.user_agent || '-' }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Tab: Proteksi Bot & CAPTCHA -->
            <div v-show="activeTab === 'captcha'" class="max-w-3xl space-y-6">
                <form @submit.prevent="submitCaptcha" class="bg-white dark:bg-slate-800 rounded-3xl p-6 sm:p-8 border border-slate-200/80 dark:border-slate-700/80 shadow-xs space-y-6">
                    <div class="flex items-start justify-between gap-4 pb-4 border-b border-slate-100 dark:border-slate-700">
                        <div>
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-sky-50 dark:bg-sky-950/60 text-sky-700 dark:text-sky-300 text-xs font-bold mb-2">
                                <i class="fa-solid fa-robot"></i>
                                <span>Anti-Bot &amp; Form Protection</span>
                            </div>
                            <h3 class="font-extrabold text-slate-900 dark:text-white text-base">
                                Konfigurasi CAPTCHA &amp; Pencegahan Spam
                            </h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                Lindungi formulir login dan registrasi dari serangan robot brute-force, scraping, dan spam otomatis.
                            </p>
                        </div>
                        <div class="flex items-center">
                            <span :class="['text-xs font-black px-3 py-1 rounded-full border', captchaForm.captcha_enabled ? 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-slate-100 text-slate-500 border-slate-200']">
                                {{ captchaForm.captcha_enabled ? 'CAPTCHA AKTIF' : 'NONAKTIF' }}
                            </span>
                        </div>
                    </div>

                    <!-- Master Toggle CAPTCHA -->
                    <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200/80 dark:border-slate-700/80 flex items-center justify-between gap-4">
                        <div>
                            <span class="text-xs font-bold text-slate-900 dark:text-white block">Aktifkan Proteksi CAPTCHA</span>
                            <span class="text-[11px] text-slate-500 dark:text-slate-400">Tampilkan tantangan verifikasi manusia pada formulir yang ditentukan.</span>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input
                                v-model="captchaForm.captcha_enabled"
                                type="checkbox"
                                class="sr-only peer"
                            />
                            <div class="w-11 h-6 bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-sky-600"></div>
                        </label>
                    </div>

                    <!-- Provider Selection -->
                    <div class="space-y-3">
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-200">
                            Pilih Layanan Provider CAPTCHA <span class="text-rose-500">*</span>
                        </label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <label :class="['p-4 rounded-2xl border cursor-pointer transition flex items-start gap-3', captchaForm.captcha_provider === 'Simple CAPTCHA' ? 'border-sky-500 bg-sky-50/50 dark:bg-sky-950/30' : 'border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-900/40']">
                                <input
                                    v-model="captchaForm.captcha_provider"
                                    type="radio"
                                    value="Simple CAPTCHA"
                                    class="mt-1 text-sky-600 focus:ring-sky-500"
                                />
                                <div>
                                    <span class="text-xs font-bold text-slate-900 dark:text-white block">Simple CAPTCHA (Lokal)</span>
                                    <span class="text-[11px] text-slate-500 dark:text-slate-400 block mt-0.5">Tantangan matematika &amp; teks sederhana langsung dari server tanpa API Key pihak ketiga.</span>
                                </div>
                            </label>

                            <label :class="['p-4 rounded-2xl border cursor-pointer transition flex items-start gap-3', captchaForm.captcha_provider === 'Google reCAPTCHA v2 Checkbox' ? 'border-sky-500 bg-sky-50/50 dark:bg-sky-950/30' : 'border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-900/40']">
                                <input
                                    v-model="captchaForm.captcha_provider"
                                    type="radio"
                                    value="Google reCAPTCHA v2 Checkbox"
                                    class="mt-1 text-sky-600 focus:ring-sky-500"
                                />
                                <div>
                                    <span class="text-xs font-bold text-slate-900 dark:text-white block">Google reCAPTCHA v2</span>
                                    <span class="text-[11px] text-slate-500 dark:text-slate-400 block mt-0.5">Kotak centang "I'm not a robot" standar resmi Google.</span>
                                </div>
                            </label>

                            <label :class="['p-4 rounded-2xl border cursor-pointer transition flex items-start gap-3', captchaForm.captcha_provider === 'Google reCAPTCHA v3 Invisible' ? 'border-sky-500 bg-sky-50/50 dark:bg-sky-950/30' : 'border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-900/40']">
                                <input
                                    v-model="captchaForm.captcha_provider"
                                    type="radio"
                                    value="Google reCAPTCHA v3 Invisible"
                                    class="mt-1 text-sky-600 focus:ring-sky-500"
                                />
                                <div>
                                    <span class="text-xs font-bold text-slate-900 dark:text-white block">Google reCAPTCHA v3 (Invisible)</span>
                                    <span class="text-[11px] text-slate-500 dark:text-slate-400 block mt-0.5">Penilaian skor latar belakang tanpa mengganggu kenyamanan pengguna.</span>
                                </div>
                            </label>

                            <label :class="['p-4 rounded-2xl border cursor-pointer transition flex items-start gap-3', captchaForm.captcha_provider === 'Cloudflare Turnstile' ? 'border-sky-500 bg-sky-50/50 dark:bg-sky-950/30' : 'border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-900/40']">
                                <input
                                    v-model="captchaForm.captcha_provider"
                                    type="radio"
                                    value="Cloudflare Turnstile"
                                    class="mt-1 text-sky-600 focus:ring-sky-500"
                                />
                                <div>
                                    <span class="text-xs font-bold text-slate-900 dark:text-white block">Cloudflare Turnstile</span>
                                    <span class="text-[11px] text-slate-500 dark:text-slate-400 block mt-0.5">Alternatif modern reCAPTCHA yang cepat, ringan, dan menjaga privasi pengunjung.</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- API Keys (Google / Cloudflare) -->
                    <div v-if="captchaForm.captcha_provider !== 'Simple CAPTCHA'" class="p-5 rounded-2xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                                <i class="fa-solid fa-key text-sky-500"></i>
                                Kredensial API {{ captchaForm.captcha_provider }}
                            </span>
                            <button
                                type="button"
                                @click="showCaptchaSecret = !showCaptchaSecret"
                                class="text-[11px] text-sky-600 dark:text-sky-400 font-bold hover:underline cursor-pointer"
                            >
                                {{ showCaptchaSecret ? 'Sembunyikan Secret Key' : 'Tampilkan Secret Key' }}
                            </button>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1">
                                    Site Key (Public Key) <span class="text-rose-500">*</span>
                                </label>
                                <input
                                    v-model="captchaForm.captcha_site_key"
                                    type="text"
                                    placeholder="Contoh: 6Ld... atau 0x4AAAAAA..."
                                    class="w-full px-4 py-2.5 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-mono text-slate-900 dark:text-white focus:ring-2 focus:ring-sky-500 focus:outline-none"
                                />
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1">
                                    Secret Key <span class="text-rose-500">*</span>
                                </label>
                                <input
                                    v-model="captchaForm.captcha_secret_key"
                                    :type="showCaptchaSecret ? 'text' : 'password'"
                                    placeholder="Kunci rahasia dari penyedia layanan"
                                    class="w-full px-4 py-2.5 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-mono text-slate-900 dark:text-white focus:ring-2 focus:ring-sky-500 focus:outline-none"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Target Form Triggers -->
                    <div class="space-y-3 pt-2">
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-200">
                            Penerapan Wajib CAPTCHA Pada Halaman:
                        </label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <label class="flex items-center gap-3 p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200/80 dark:border-slate-700 cursor-pointer">
                                <input
                                    v-model="captchaForm.captcha_required_backend_login"
                                    type="checkbox"
                                    class="w-4 h-4 rounded text-sky-600 focus:ring-sky-500 border-slate-300"
                                />
                                <div>
                                    <span class="text-xs font-bold text-slate-900 dark:text-white block">Login Superadmin &amp; Pengelola</span>
                                    <span class="text-[11px] text-slate-400 block">Wajib saat admin/pastor masuk ke panel.</span>
                                </div>
                            </label>

                            <label class="flex items-center gap-3 p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200/80 dark:border-slate-700 cursor-pointer">
                                <input
                                    v-model="captchaForm.captcha_required_umat_login"
                                    type="checkbox"
                                    class="w-4 h-4 rounded text-sky-600 focus:ring-sky-500 border-slate-300"
                                />
                                <div>
                                    <span class="text-xs font-bold text-slate-900 dark:text-white block">Login &amp; Registrasi Portal Umat</span>
                                    <span class="text-[11px] text-slate-400 block">Cegah pembuatan akun fiktif massal.</span>
                                </div>
                            </label>

                            <label class="flex items-center gap-3 p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200/80 dark:border-slate-700 cursor-pointer">
                                <input
                                    v-model="captchaForm.captcha_required_forgot_password"
                                    type="checkbox"
                                    class="w-4 h-4 rounded text-sky-600 focus:ring-sky-500 border-slate-300"
                                />
                                <div>
                                    <span class="text-xs font-bold text-slate-900 dark:text-white block">Formulir Lupa Password</span>
                                    <span class="text-[11px] text-slate-400 block">Cegah spam permintaan reset email.</span>
                                </div>
                            </label>

                            <label class="flex items-center gap-3 p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200/80 dark:border-slate-700 cursor-pointer">
                                <input
                                    v-model="captchaForm.captcha_required_public_forms"
                                    type="checkbox"
                                    class="w-4 h-4 rounded text-sky-600 focus:ring-sky-500 border-slate-300"
                                />
                                <div>
                                    <span class="text-xs font-bold text-slate-900 dark:text-white block">Formulir Kontak &amp; Publik</span>
                                    <span class="text-[11px] text-slate-400 block">Cegah pesan spam dari halaman website.</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Trigger Threshold -->
                    <div class="pt-2">
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1.5">
                            Ambang Batas Tampil (Failed Attempts Threshold)
                        </label>
                        <div class="relative max-w-xs">
                            <input
                                v-model="captchaForm.captcha_show_after_failed_attempts"
                                type="number"
                                min="0"
                                max="10"
                                class="w-full px-4 py-2.5 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs font-semibold text-slate-900 dark:text-white focus:ring-2 focus:ring-sky-500 focus:outline-none"
                            />
                            <span class="absolute right-3.5 top-1/2 -translate-y-1/2 text-xs text-slate-400">kali gagal</span>
                        </div>
                        <p class="text-[11px] text-slate-400 mt-1">Isi <strong>0</strong> untuk selalu menampilkan CAPTCHA setiap saat, atau <strong>3</strong> agar hanya muncul setelah 3x salah password.</p>
                    </div>

                    <div class="pt-4 border-t border-slate-100 dark:border-slate-700 flex justify-end">
                        <button
                            type="submit"
                            :disabled="isProcessing"
                            class="inline-flex items-center gap-2 px-6 py-2.5 rounded-2xl bg-sky-600 hover:bg-sky-700 text-white font-bold text-xs shadow-lg shadow-sky-600/30 transition disabled:opacity-50 cursor-pointer"
                        >
                            <i class="fa-solid fa-save"></i>
                            <span>Simpan Pengaturan CAPTCHA</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Tab: Autentikasi Dua Faktor (2FA) -->
            <div v-show="activeTab === '2fa'" class="max-w-3xl space-y-6">
                <form @submit.prevent="submitTwoFactor" class="bg-white dark:bg-slate-800 rounded-3xl p-6 sm:p-8 border border-slate-200/80 dark:border-slate-700/80 shadow-xs space-y-6">
                    <div class="flex items-start justify-between gap-4 pb-4 border-b border-slate-100 dark:border-slate-700">
                        <div>
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 text-xs font-bold mb-2">
                                <i class="fa-solid fa-key"></i>
                                <span>Multi-Factor Authentication</span>
                            </div>
                            <h3 class="font-extrabold text-slate-900 dark:text-white text-base">
                                Autentikasi Dua Faktor (2FA)
                            </h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                Lapisan keamanan ganda untuk memverifikasi identitas pengguna melalui kode OTP atau aplikasi otentikasi saat login.
                            </p>
                        </div>
                        <div class="flex items-center">
                            <span :class="['text-xs font-black px-3 py-1 rounded-full border', twoFactorForm.two_factor_enabled ? 'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-950 dark:text-purple-300' : 'bg-slate-100 text-slate-500 border-slate-200']">
                                {{ twoFactorForm.two_factor_enabled ? '2FA AKTIF' : 'NONAKTIF' }}
                            </span>
                        </div>
                    </div>

                    <!-- Master Toggle 2FA -->
                    <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200/80 dark:border-slate-700/80 flex items-center justify-between gap-4">
                        <div>
                            <span class="text-xs font-bold text-slate-900 dark:text-white block">Aktifkan Kewajiban 2FA</span>
                            <span class="text-[11px] text-slate-500 dark:text-slate-400">Pengguna wajib memasukkan kode verifikasi setelah memasukkan password yang benar.</span>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input
                                v-model="twoFactorForm.two_factor_enabled"
                                type="checkbox"
                                class="sr-only peer"
                            />
                            <div class="w-11 h-6 bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-600"></div>
                        </label>
                    </div>

                    <!-- 2FA Method Selector -->
                    <div class="space-y-3">
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-200">
                            Metode Pengiriman / Verifikasi 2FA <span class="text-rose-500">*</span>
                        </label>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <label :class="['p-4 rounded-2xl border cursor-pointer transition flex flex-col justify-between', twoFactorForm.two_factor_method === 'whatsapp_otp' ? 'border-purple-500 bg-purple-50/50 dark:bg-purple-950/30' : 'border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-900/40']">
                                <div class="flex items-center gap-2 mb-2">
                                    <input
                                        v-model="twoFactorForm.two_factor_method"
                                        type="radio"
                                        value="whatsapp_otp"
                                        class="text-purple-600 focus:ring-purple-500"
                                    />
                                    <i class="fa-brands fa-whatsapp text-emerald-500 text-base"></i>
                                    <span class="text-xs font-bold text-slate-900 dark:text-white">WhatsApp OTP</span>
                                </div>
                                <span class="text-[11px] text-slate-500 dark:text-slate-400 leading-relaxed">
                                    Kode OTP 6 digit dikirimkan langsung ke nomor WhatsApp terdaftar melalui gateway resmi paroki.
                                </span>
                            </label>

                            <label :class="['p-4 rounded-2xl border cursor-pointer transition flex flex-col justify-between', twoFactorForm.two_factor_method === 'authenticator_app' ? 'border-purple-500 bg-purple-50/50 dark:bg-purple-950/30' : 'border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-900/40']">
                                <div class="flex items-center gap-2 mb-2">
                                    <input
                                        v-model="twoFactorForm.two_factor_method"
                                        type="radio"
                                        value="authenticator_app"
                                        class="text-purple-600 focus:ring-purple-500"
                                    />
                                    <i class="fa-solid fa-mobile-screen-button text-purple-500 text-base"></i>
                                    <span class="text-xs font-bold text-slate-900 dark:text-white">Aplikasi Authenticator</span>
                                </div>
                                <span class="text-[11px] text-slate-500 dark:text-slate-400 leading-relaxed">
                                    Menggunakan kode TOTP waktu nyata via Google Authenticator atau Microsoft Authenticator.
                                </span>
                            </label>

                            <label :class="['p-4 rounded-2xl border cursor-pointer transition flex flex-col justify-between', twoFactorForm.two_factor_method === 'email_otp' ? 'border-purple-500 bg-purple-50/50 dark:bg-purple-950/30' : 'border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-900/40']">
                                <div class="flex items-center gap-2 mb-2">
                                    <input
                                        v-model="twoFactorForm.two_factor_method"
                                        type="radio"
                                        value="email_otp"
                                        class="text-purple-600 focus:ring-purple-500"
                                    />
                                    <i class="fa-solid fa-envelope text-sky-500 text-base"></i>
                                    <span class="text-xs font-bold text-slate-900 dark:text-white">Email OTP</span>
                                </div>
                                <span class="text-[11px] text-slate-500 dark:text-slate-400 leading-relaxed">
                                    Kode verifikasi dikirimkan ke alamat email terdaftar akun pengguna.
                                </span>
                            </label>
                        </div>
                    </div>

                    <!-- Target Roles & Scope -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 pt-2">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1.5">
                                Sasaran Peran Wajib 2FA <span class="text-rose-500">*</span>
                            </label>
                            <select
                                v-model="twoFactorForm.two_factor_roles"
                                class="w-full px-4 py-2.5 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs font-semibold text-slate-900 dark:text-white focus:ring-2 focus:ring-purple-500 focus:outline-none"
                            >
                                <option value="all_admins">Seluruh Administrator &amp; Pengelola (Rekomendasi)</option>
                                <option value="superadmin_only">Hanya Super Administrator</option>
                                <option value="all_users">Seluruh Pengguna Termasuk Akun Umat</option>
                            </select>
                            <p class="text-[11px] text-slate-400 mt-1">Peran yang diwajibkan melewati tahap otentikasi kedua.</p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1.5">
                                Masa Berlaku Kode OTP
                            </label>
                            <div class="relative">
                                <input
                                    v-model="twoFactorForm.two_factor_otp_expiry_minutes"
                                    type="number"
                                    min="1"
                                    max="60"
                                    class="w-full px-4 py-2.5 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs font-semibold text-slate-900 dark:text-white focus:ring-2 focus:ring-purple-500 focus:outline-none"
                                />
                                <span class="absolute right-3.5 top-1/2 -translate-y-1/2 text-xs text-slate-400">menit</span>
                            </div>
                            <p class="text-[11px] text-slate-400 mt-1">Batas waktu sebelum kode kedaluwarsa.</p>
                        </div>
                    </div>

                    <!-- Grace Period / Remember Device -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1.5">
                            Masa Ingat Perangkat Terpercaya (Remember Device)
                        </label>
                        <div class="relative max-w-xs">
                            <input
                                v-model="twoFactorForm.two_factor_grace_period_days"
                                type="number"
                                min="0"
                                max="90"
                                class="w-full px-4 py-2.5 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs font-semibold text-slate-900 dark:text-white focus:ring-2 focus:ring-purple-500 focus:outline-none"
                            />
                            <span class="absolute right-3.5 top-1/2 -translate-y-1/2 text-xs text-slate-400">hari</span>
                        </div>
                        <p class="text-[11px] text-slate-400 mt-1">Isi <strong>0</strong> untuk mewajibkan verifikasi OTP di setiap kali login, atau misal <strong>30</strong> hari untuk browser yang sama.</p>
                    </div>

                    <div class="pt-4 border-t border-slate-100 dark:border-slate-700 flex justify-end">
                        <button
                            type="submit"
                            :disabled="isProcessing"
                            class="inline-flex items-center gap-2 px-6 py-2.5 rounded-2xl bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs shadow-lg shadow-purple-600/30 transition disabled:opacity-50 cursor-pointer"
                        >
                            <i class="fa-solid fa-save"></i>
                            <span>Simpan Pengaturan 2FA</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Tab 4: Kebijakan Keamanan (Security Policies) -->
            <div v-show="activeTab === 'policies'" class="max-w-3xl">
                <form @submit.prevent="submitPolicies" class="bg-white dark:bg-slate-800 rounded-3xl p-6 sm:p-8 border border-slate-200/80 dark:border-slate-700/80 shadow-xs space-y-6">
                    <div>
                        <h3 class="font-extrabold text-slate-900 dark:text-white text-base">
                            Konfigurasi Kebijakan Keamanan &amp; Proteksi Login
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                            Atur ambang batas keamanan sistem untuk mencegah serangan brute force dan kebocoran sesi.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 pt-2">
                        
                        <!-- Max Login Attempts -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1.5">
                                Batas Maksimal Gagal Login
                            </label>
                            <div class="relative">
                                <input
                                    v-model="policyForm.max_login_attempts"
                                    type="number"
                                    min="1"
                                    max="50"
                                    class="w-full px-4 py-2.5 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs font-semibold text-slate-900 dark:text-white focus:ring-2 focus:ring-teal-500 focus:outline-none"
                                />
                                <span class="absolute right-3.5 top-1/2 -translate-y-1/2 text-xs text-slate-400">kali</span>
                            </div>
                            <p class="text-[11px] text-slate-400 mt-1">IP akan otomatis diblokir setelah gagal sejumlah ini.</p>
                        </div>

                        <!-- Lockout Duration -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1.5">
                                Durasi Penguncian (Lockout)
                            </label>
                            <div class="relative">
                                <input
                                    v-model="policyForm.lockout_minutes"
                                    type="number"
                                    min="1"
                                    max="1440"
                                    class="w-full px-4 py-2.5 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs font-semibold text-slate-900 dark:text-white focus:ring-2 focus:ring-teal-500 focus:outline-none"
                                />
                                <span class="absolute right-3.5 top-1/2 -translate-y-1/2 text-xs text-slate-400">menit</span>
                            </div>
                            <p class="text-[11px] text-slate-400 mt-1">Lama durasi blokir IP penyerang sebelum dilepas otomatis.</p>
                        </div>

                        <!-- Session Timeout -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1.5">
                                Masa Aktif Sesi Pengguna
                            </label>
                            <div class="relative">
                                <input
                                    v-model="policyForm.session_timeout_minutes"
                                    type="number"
                                    min="5"
                                    max="1440"
                                    class="w-full px-4 py-2.5 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs font-semibold text-slate-900 dark:text-white focus:ring-2 focus:ring-teal-500 focus:outline-none"
                                />
                                <span class="absolute right-3.5 top-1/2 -translate-y-1/2 text-xs text-slate-400">menit</span>
                            </div>
                            <p class="text-[11px] text-slate-400 mt-1">Sesi akan kedaluwarsa jika tidak ada aktivitas.</p>
                        </div>

                    </div>

                    <!-- Toggles -->
                    <div class="pt-4 border-t border-slate-100 dark:border-slate-700 space-y-3.5">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input
                                v-model="policyForm.enable_brute_force_protection"
                                type="checkbox"
                                class="w-4 h-4 rounded text-teal-600 focus:ring-teal-500 border-slate-300"
                            />
                            <div>
                                <span class="text-xs font-bold text-slate-900 dark:text-white block">Aktifkan Firewall Anti-Brute Force Otomatis</span>
                                <span class="text-[11px] text-slate-400 block">Sistem otomatis mencekal IP yang mencoba menebak password berulang kali.</span>
                            </div>
                        </label>

                        <label class="flex items-center gap-3 cursor-pointer">
                            <input
                                v-model="policyForm.force_strong_password"
                                type="checkbox"
                                class="w-4 h-4 rounded text-teal-600 focus:ring-teal-500 border-slate-300"
                            />
                            <div>
                                <span class="text-xs font-bold text-slate-900 dark:text-white block">Wajibkan Kebijakan Password Kuat</span>
                                <span class="text-[11px] text-slate-400 block">Mengharuskan kombinasi huruf besar, angka, dan minimal 8 karakter saat user membuat kata sandi.</span>
                            </div>
                        </label>
                    </div>

                    <div class="pt-4 border-t border-slate-100 dark:border-slate-700 flex justify-end">
                        <button
                            type="submit"
                            :disabled="isProcessing"
                            class="inline-flex items-center gap-2 px-6 py-2.5 rounded-2xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs shadow-lg shadow-teal-600/30 transition disabled:opacity-50 cursor-pointer"
                        >
                            <i class="fa-solid fa-save"></i>
                            <span>Simpan Kebijakan Keamanan</span>
                        </button>
                    </div>
                </form>
            </div>

        </div>

        <!-- Modal Tambah Blokir IP -->
        <div v-if="blockIpModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-sm">
            <div class="bg-white dark:bg-slate-800 rounded-3xl max-w-md w-full p-6 sm:p-8 border border-slate-200/80 dark:border-slate-700/80 shadow-2xl space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="font-extrabold text-slate-900 dark:text-white text-base flex items-center gap-2">
                        <i class="fa-solid fa-ban text-rose-500"></i>
                        Blokir Alamat IP Manual
                    </h3>
                    <button @click="blockIpModal = false" class="text-slate-400 hover:text-slate-600 cursor-pointer">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                <form @submit.prevent="submitBlockIp" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1">
                            Alamat IP Target <span class="text-rose-500">*</span>
                        </label>
                        <input
                            v-model="blockIpForm.ip_address"
                            type="text"
                            placeholder="Contoh: 192.168.1.100 atau 103.25.10.5"
                            required
                            class="w-full px-4 py-2.5 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs font-mono text-slate-900 dark:text-white focus:ring-2 focus:ring-rose-500 focus:outline-none"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1">
                            Durasi Pemblokiran
                        </label>
                        <select
                            v-model="blockIpForm.blocked_duration"
                            class="w-full px-4 py-2.5 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs text-slate-900 dark:text-white focus:ring-2 focus:ring-rose-500 focus:outline-none"
                        >
                            <option :value="1">1 Jam</option>
                            <option :value="6">6 Jam</option>
                            <option :value="24">24 Jam (1 Hari)</option>
                            <option :value="168">7 Hari (1 Minggu)</option>
                            <option :value="720">30 Hari (1 Bulan)</option>
                            <option :value="null">Permanen</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1">
                            Alasan Pemblokiran
                        </label>
                        <textarea
                            v-model="blockIpForm.reason"
                            rows="2"
                            placeholder="Contoh: Terdeteksi scraping berlebihan / serangan spam"
                            class="w-full px-4 py-2.5 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs text-slate-900 dark:text-white focus:ring-2 focus:ring-rose-500 focus:outline-none"
                        ></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button
                            type="button"
                            @click="blockIpModal = false"
                            class="px-4 py-2 rounded-2xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 text-xs font-bold transition cursor-pointer"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            :disabled="isProcessing"
                            class="px-5 py-2 rounded-2xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold shadow-lg shadow-rose-600/30 transition disabled:opacity-50 cursor-pointer"
                        >
                            Terapkan Blokir
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </AppLayout>
</template>
