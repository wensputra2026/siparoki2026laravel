<script setup>
import { computed } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';

defineProps({
    status: {
        type: String,
        default: null,
    },
});

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
    identitas: '',
});

const submit = () => {
    form.post('/lupa-password');
};
</script>

<template>
    <Head :title="`Pemulihan Kata Sandi - ${appName}`" />

    <div class="min-h-screen bg-slate-50 flex flex-col justify-center py-12 px-4 sm:px-6 lg:px-8 font-sans selection:bg-amber-500 selection:text-white">
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
                Pemulihan Kata Sandi
            </h2>
            <p class="mt-1 text-xs text-slate-500">
                Masukkan Email, Username, atau WhatsApp Anda untuk memverifikasi akun jemaat.
            </p>
        </div>

        <!-- Card Container -->
        <div class="mt-6 sm:mx-auto sm:w-full sm:max-w-md">
            <div class="bg-white py-8 px-6 sm:px-8 shadow-sm border border-slate-200/80 rounded-2xl">
                <!-- Status Alert -->
                <div
                    v-if="status"
                    class="mb-5 p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-start gap-2.5"
                >
                    <i class="fa-solid fa-circle-check text-emerald-600 text-sm mt-0.5 shrink-0"></i>
                    <span>{{ status }}</span>
                </div>

                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">
                            Email, Username, atau WhatsApp Terdaftar
                        </label>
                        <div class="relative">
                            <i class="fa-solid fa-id-card absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                            <input
                                v-model="form.identitas"
                                type="text"
                                required
                                autofocus
                                placeholder="Masukkan email, username, atau no HP..."
                                class="w-full pl-9 pr-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 placeholder-slate-400 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition"
                            />
                        </div>
                        <p v-if="form.errors.identitas" class="mt-1 text-xs font-semibold text-rose-600">
                            {{ form.errors.identitas }}
                        </p>
                    </div>

                    <div class="pt-2">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="w-full py-2.5 px-4 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs shadow-sm shadow-amber-500/25 transition-all flex items-center justify-center gap-2 disabled:opacity-50"
                        >
                            <i v-if="form.processing" class="fa-solid fa-circle-notch animate-spin"></i>
                            <i v-else class="fa-solid fa-paper-plane"></i>
                            <span>{{ form.processing ? 'Mengirim Permintaan...' : 'Kirim Instruksi Reset' }}</span>
                        </button>
                    </div>
                </form>

                <!-- Bottom Icon Actions -->
                <div class="mt-6 pt-5 border-t border-slate-100 flex items-center justify-center gap-4">
                    <Link
                        href="/login"
                        title="Sudah Ingat Sandi? Masuk ke Akun"
                        aria-label="Masuk ke Akun"
                        class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-slate-50 border border-slate-200 text-slate-500 hover:text-amber-600 hover:border-amber-300 hover:bg-amber-50 shadow-xs transition-all duration-200"
                    >
                        <i class="fa-solid fa-arrow-right-to-bracket text-sm"></i>
                    </Link>
                    <a
                        href="/"
                        title="Kembali ke Beranda"
                        aria-label="Kembali ke Beranda"
                        class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-slate-50 border border-slate-200 text-slate-500 hover:text-amber-600 hover:border-amber-300 hover:bg-amber-50 shadow-xs transition-all duration-200"
                    >
                        <i class="fa-solid fa-house text-sm"></i>
                    </a>
                    <Link
                        href="/register"
                        title="Daftar Akun Umat Baru"
                        aria-label="Daftar Akun Baru"
                        class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-slate-50 border border-slate-200 text-slate-500 hover:text-amber-600 hover:border-amber-300 hover:bg-amber-50 shadow-xs transition-all duration-200"
                    >
                        <i class="fa-solid fa-user-plus text-sm"></i>
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>
