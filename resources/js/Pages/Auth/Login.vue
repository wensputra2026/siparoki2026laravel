<script setup>
import { computed, ref } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';

defineProps({
    status: {
        type: String,
        default: null,
    },
});

const showPassword = ref(false);
const page = usePage();

const appName = computed(() => page.props.app?.name || 'SIPAROKI');
const parokiName = computed(() => page.props.app?.nama_paroki || page.props.app?.paroki || 'Paroki St. Vinsensius a Paulo - Benlutu');
const logoUrl = computed(() => {
    const logo = page.props.app?.logo;
    if (!logo) return '';
    if (/^(https?:)?\/\//.test(logo) || logo.startsWith('/') || logo.startsWith('data:')) return logo;
    return `/${String(logo).replace(/^public\//, '')}`;
});

const form = useForm({
    login: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post('/login', {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head :title="`Masuk ke Akun - ${appName}`" />

    <div class="min-h-screen bg-slate-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8 font-sans selection:bg-amber-500 selection:text-white">
        <!-- Brand Header -->
        <div class="sm:mx-auto sm:w-full sm:max-w-md text-center">
            <a href="/" class="inline-flex items-center gap-3 group">
                <img
                    v-if="logoUrl"
                    :src="logoUrl"
                    :alt="parokiName"
                    class="w-12 h-12 rounded-full object-cover bg-white p-1 ring-1 ring-amber-200 shadow-md shadow-amber-500/20 group-hover:scale-105 transition-transform"
                />
                <div v-else class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-amber-500 to-amber-400 flex items-center justify-center text-white text-2xl font-black shadow-md shadow-amber-500/25 group-hover:scale-105 transition-transform">
                    <i class="fa-solid fa-church"></i>
                </div>
                <div class="text-left">
                    <h1 class="text-xl font-black text-slate-900 tracking-tight leading-tight">
                        {{ appName }}
                    </h1>
                    <p class="text-xs text-slate-500 max-w-[280px] leading-snug">{{ parokiName }}</p>
                </div>
            </a>
            <h2 class="mt-6 text-xl sm:text-2xl font-black text-slate-900 tracking-tight">
                Selamat Datang di Portal Paroki
            </h2>
            <p class="mt-1 text-xs text-slate-500">
                Silakan masuk untuk mengakses layanan pastoral, data umat, atau panel administrasi.
            </p>
        </div>

        <!-- Card Container -->
        <div class="mt-6 sm:mx-auto sm:w-full sm:max-w-md px-4 sm:px-0">
            <div class="bg-white py-8 px-6 sm:px-8 shadow-sm border border-slate-200/80 rounded-2xl">
                <!-- Status Alert -->
                <div
                    v-if="status"
                    class="mb-5 p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center gap-2"
                >
                    <i class="fa-solid fa-circle-check text-emerald-600 text-sm"></i>
                    <span>{{ status }}</span>
                </div>

                <form @submit.prevent="submit" class="space-y-4">
                    <!-- Login Input (Email or Username) -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">
                            Email atau Username
                        </label>
                        <div class="relative">
                            <i class="fa-solid fa-user absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                            <input
                                v-model="form.login"
                                name="login"
                                type="text"
                                required
                                autofocus
                                placeholder="nama@email.com atau username"
                                class="w-full pl-9 pr-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 placeholder-slate-400 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition"
                            />
                        </div>
                        <p v-if="form.errors.login" class="mt-1 text-xs font-semibold text-rose-600">
                            {{ form.errors.login }}
                        </p>
                    </div>

                    <!-- Password Input -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-xs font-bold text-slate-700">
                                Kata Sandi
                            </label>
                            <Link
                                href="/lupa-password"
                                class="text-xs font-semibold text-amber-600 hover:text-amber-700 transition"
                            >
                                Lupa kata sandi?
                            </Link>
                        </div>
                        <div class="relative">
                            <i class="fa-solid fa-lock absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                            <input
                                v-model="form.password"
                                name="password"
                                :type="showPassword ? 'text' : 'password'"
                                required
                                placeholder="••••••••"
                                class="w-full pl-9 pr-10 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 placeholder-slate-400 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition"
                            />
                            <button
                                type="button"
                                @click="showPassword = !showPassword"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-700 text-xs"
                            >
                                <i :class="showPassword ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye'"></i>
                            </button>
                        </div>
                        <p v-if="form.errors.password" class="mt-1 text-xs font-semibold text-rose-600">
                            {{ form.errors.password }}
                        </p>
                    </div>

                    <!-- Remember Me -->
                    <div class="flex items-center justify-between pt-1">
                        <label class="flex items-center gap-2 cursor-pointer select-none">
                            <input
                                v-model="form.remember"
                                type="checkbox"
                                class="w-4 h-4 rounded text-amber-600 border-slate-300 focus:ring-amber-500"
                            />
                            <span class="text-xs font-medium text-slate-600">Ingat saya di perangkat ini</span>
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-2">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="w-full py-2.5 px-4 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs shadow-sm shadow-amber-500/25 transition-all flex items-center justify-center gap-2 disabled:opacity-50"
                        >
                            <i v-if="form.processing" class="fa-solid fa-circle-notch animate-spin"></i>
                            <i v-else class="fa-solid fa-arrow-right-to-bracket"></i>
                            <span>{{ form.processing ? 'Memproses...' : 'Masuk Sekarang' }}</span>
                        </button>
                    </div>
                </form>

                <!-- Divider & Register Link -->
                <div class="mt-6 pt-5 border-t border-slate-100 text-center">
                    <p class="text-xs text-slate-500">
                        Belum memiliki akun jemaat?
                        <Link
                            href="/register"
                            class="font-bold text-amber-600 hover:text-amber-700 transition ml-1"
                        >
                            Daftar Akun Baru
                        </Link>
                    </p>
                </div>
            </div>

            <!-- Back to Public Site -->
            <div class="mt-6 text-center">
                <a
                    href="/"
                    title="Kembali ke Beranda"
                    class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-white border border-slate-200 text-slate-500 hover:text-amber-600 hover:border-amber-300 hover:bg-amber-50 shadow-xs transition-all duration-200"
                >
                    <i class="fa-solid fa-house text-sm"></i>
                </a>
            </div>
        </div>
    </div>
</template>
