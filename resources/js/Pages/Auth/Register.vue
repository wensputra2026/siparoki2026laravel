<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import SearchableSelect from '@/Components/SearchableSelect.vue';

const props = defineProps({
    wilayahs: {
        type: Array,
        default: () => [],
    },
    kapelas: {
        type: Array,
        default: () => [],
    },
    kubs: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();
const showPassword = ref(false);
const showPasswordConfirmation = ref(false);
const appName = computed(() => page.props.app?.name || 'SIPAROKI');
const parokiName = computed(() => page.props.app?.nama_paroki || page.props.app?.paroki || 'Paroki St. Vinsensius a Paulo - Benlutu');
const logoUrl = computed(() => {
    const logo = page.props.app?.logo;
    if (!logo) return '';
    if (/^(https?:)?\/\//.test(logo) || logo.startsWith('/') || logo.startsWith('data:')) return logo;
    return `/${String(logo).replace(/^public\//, '')}`;
});

const form = useForm({
    nama_lengkap: '',
    email: '',
    username: '',
    nik: '',
    no_hp: '',
    wilayah_id: '',
    kapela_id: '',
    kub_id: '',
    password: '',
    password_confirmation: '',
});

const handleWilayahChange = (val) => {
    if (val) {
        form.kapela_id = '';
    }
    form.kub_id = '';
};

const handleKapelaChange = (val) => {
    if (val) {
        form.wilayah_id = '';
    }
    form.kub_id = '';
};

const isWilayahDisabled = computed(() => !!form.kapela_id);
const isKapelaDisabled = computed(() => !!form.wilayah_id);

const filteredKubs = computed(() => {
    if (form.wilayah_id) {
        return props.kubs.filter((k) => k.wilayah_id == form.wilayah_id);
    }
    if (form.kapela_id) {
        return props.kubs.filter((k) => k.kapela_id == form.kapela_id);
    }
    return props.kubs;
});

const submit = () => {
    form.post('/register', {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head :title="`Pendaftaran Akun - ${appName}`" />

    <div class="min-h-screen bg-slate-50 flex flex-col justify-center py-12 px-4 sm:px-6 lg:px-8 font-sans selection:bg-amber-500 selection:text-white">
        <!-- Brand Header -->
        <div class="sm:mx-auto sm:w-full sm:max-w-xl text-center">
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
                Pendaftaran Akun
            </h2>
            <p class="mt-1 text-xs text-slate-500 max-w-md mx-auto">
                Daftarkan akun jemaat untuk mengakses layanan sakramen mandiri, KKK Digital, dan warta paroki.
            </p>
        </div>

        <!-- Card Container -->
        <div class="mt-6 sm:mx-auto sm:w-full sm:max-w-xl">
            <div class="bg-white py-8 px-6 sm:px-8 shadow-sm border border-slate-200/80 rounded-2xl">
                <form @submit.prevent="submit" class="space-y-4">
                    <!-- Row 1: Nama & NIK -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                Nama Lengkap <span class="text-rose-500">*</span>
                            </label>
                            <input
                                v-model="form.nama_lengkap"
                                type="text"
                                required
                                placeholder="Nama lengkap sesuai KTP"
                                class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 placeholder-slate-400 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition"
                            />
                            <p v-if="form.errors.nama_lengkap" class="mt-1 text-xs font-semibold text-rose-600">
                                {{ form.errors.nama_lengkap }}
                            </p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                NIK KTP (16 Digit)
                            </label>
                            <input
                                v-model="form.nik"
                                type="text"
                                maxlength="16"
                                placeholder="Nomor Induk Kependudukan"
                                class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 placeholder-slate-400 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition font-mono"
                            />
                            <p v-if="form.errors.nik" class="mt-1 text-xs font-semibold text-rose-600">
                                {{ form.errors.nik }}
                            </p>
                        </div>
                    </div>

                    <!-- Row 2: Email & WhatsApp -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                Alamat Email <span class="text-rose-500">*</span>
                            </label>
                            <input
                                v-model="form.email"
                                type="email"
                                required
                                placeholder="nama@email.com"
                                class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 placeholder-slate-400 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition"
                            />
                            <p v-if="form.errors.email" class="mt-1 text-xs font-semibold text-rose-600">
                                {{ form.errors.email }}
                            </p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                Nomor WhatsApp / HP
                            </label>
                            <input
                                v-model="form.no_hp"
                                type="text"
                                placeholder="08xxxxxxxxxx"
                                class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 placeholder-slate-400 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition font-mono"
                            />
                            <p v-if="form.errors.no_hp" class="mt-1 text-xs font-semibold text-rose-600">
                                {{ form.errors.no_hp }}
                            </p>
                        </div>
                    </div>

                    <!-- Row 3: Domisili Gerejawi (Wilayah, Stasi / Kapela, KUB) -->
                    <div class="p-3.5 rounded-xl bg-slate-50/80 border border-slate-200/80 space-y-3">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 block">
                            <i class="fa-solid fa-church text-amber-500 mr-1"></i> Data Wilayah / Stasi / Komunitas Basis
                        </span>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1 flex items-center justify-between">
                                    <span>Wilayah</span>
                                    <span v-if="isWilayahDisabled" class="text-[9px] text-slate-400 font-normal italic">(Stasi aktif)</span>
                                </label>
                                <SearchableSelect
                                    v-model="form.wilayah_id"
                                    :options="wilayahs"
                                    :disabled="isWilayahDisabled"
                                    value-key="id"
                                    label-key="nama_wilayah"
                                    placeholder="-- Pilih Wilayah --"
                                    search-placeholder="Cari wilayah..."
                                    icon="fa-location-dot"
                                    @change="handleWilayahChange"
                                />
                            </div>

                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1 flex items-center justify-between">
                                    <span>Stasi / Kapela</span>
                                    <span v-if="isKapelaDisabled" class="text-[9px] text-slate-400 font-normal italic">(Wilayah aktif)</span>
                                </label>
                                <SearchableSelect
                                    v-model="form.kapela_id"
                                    :options="kapelas"
                                    :disabled="isKapelaDisabled"
                                    value-key="id"
                                    label-key="nama_kapela"
                                    placeholder="-- Pilih Stasi / Kapela --"
                                    search-placeholder="Cari stasi..."
                                    icon="fa-church"
                                    @change="handleKapelaChange"
                                />
                            </div>

                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">KUB (Basis)</label>
                                <SearchableSelect
                                    v-model="form.kub_id"
                                    :options="filteredKubs"
                                    value-key="id"
                                    label-key="nama_kub"
                                    placeholder="-- Pilih KUB --"
                                    search-placeholder="Cari nama KUB..."
                                    icon="fa-users"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Row 4: Password & Confirm -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                Kata Sandi <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <input
                                    v-model="form.password"
                                    :type="showPassword ? 'text' : 'password'"
                                    required
                                    placeholder="Minimal 6 karakter"
                                    class="w-full pl-3.5 pr-10 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 placeholder-slate-400 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition"
                                />
                                <button
                                    type="button"
                                    @click="showPassword = !showPassword"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-amber-600 text-xs cursor-pointer focus:outline-none transition"
                                    title="Tampilkan/Sembunyikan Kata Sandi"
                                >
                                    <i :class="showPassword ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye'"></i>
                                </button>
                            </div>
                            <p v-if="form.errors.password" class="mt-1 text-xs font-semibold text-rose-600">
                                {{ form.errors.password }}
                            </p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                Konfirmasi Kata Sandi <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <input
                                    v-model="form.password_confirmation"
                                    :type="showPasswordConfirmation ? 'text' : 'password'"
                                    required
                                    placeholder="Ulangi kata sandi"
                                    class="w-full pl-3.5 pr-10 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 placeholder-slate-400 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition"
                                />
                                <button
                                    type="button"
                                    @click="showPasswordConfirmation = !showPasswordConfirmation"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-amber-600 text-xs cursor-pointer focus:outline-none transition"
                                    title="Tampilkan/Sembunyikan Kata Sandi"
                                >
                                    <i :class="showPasswordConfirmation ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye'"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-3">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="w-full py-2.5 px-4 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs shadow-sm shadow-amber-500/25 transition-all flex items-center justify-center gap-2 disabled:opacity-50"
                        >
                            <i v-if="form.processing" class="fa-solid fa-circle-notch animate-spin"></i>
                            <i v-else class="fa-solid fa-user-plus"></i>
                            <span>{{ form.processing ? 'Mendaftarkan Akun...' : 'Daftar Sekarang' }}</span>
                        </button>
                    </div>
                </form>

                <!-- Bottom Icon Actions -->
                <div class="mt-6 pt-5 border-t border-slate-100 flex items-center justify-center gap-4">
                    <Link
                        href="/login"
                        title="Sudah Memiliki Akun? Masuk"
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
                        href="/lupa-password"
                        title="Lupa Kata Sandi?"
                        aria-label="Lupa Kata Sandi"
                        class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-slate-50 border border-slate-200 text-slate-500 hover:text-amber-600 hover:border-amber-300 hover:bg-amber-50 shadow-xs transition-all duration-200"
                    >
                        <i class="fa-solid fa-key text-sm"></i>
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>
