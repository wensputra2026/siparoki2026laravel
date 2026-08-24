<script setup>
import { ref, computed } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    user: {
        type: Object,
        default: () => ({}),
    },
    role: {
        type: String,
        default: 'Super Admin',
    },
    prefix: {
        type: String,
        default: 'superadmin',
    },
});

const page = usePage();
const activeTab = ref('profile'); // 'profile' | 'security'

// Form for Profile Information
const profileForm = useForm({
    nama_lengkap: props.user?.nama_lengkap || props.user?.name || '',
    username: props.user?.username || '',
    email: props.user?.email || '',
    no_hp: props.user?.no_hp || '',
    foto: null,
});

// Form for Password Update
const passwordForm = useForm({
    current_password: '',
    new_password: '',
    new_password_confirmation: '',
});

const showCurrentPassword = ref(false);
const showNewPassword = ref(false);
const showConfirmPassword = ref(false);

const photoPreview = ref(null);
const selectedPhotoName = ref('');
const isUploadingPhoto = ref(false);

const handlePhotoChange = (e) => {
    const file = e.target.files[0];
    if (!file) return;

    profileForm.foto = file;
    selectedPhotoName.value = file.name;

    const reader = new FileReader();
    reader.onload = (event) => {
        photoPreview.value = event.target.result;
    };
    reader.readAsDataURL(file);

    // Instant auto-upload to server
    const base = props.prefix ? `/${props.prefix.replace(/^\//, '')}` : '/superadmin';
    const uploadForm = useForm({
        nama_lengkap: profileForm.nama_lengkap,
        username: profileForm.username,
        email: profileForm.email,
        no_hp: profileForm.no_hp,
        foto: file,
    });

    isUploadingPhoto.value = true;
    uploadForm.post(`${base}/profil-saya`, {
        preserveScroll: true,
        forceFormData: true,
        onFinish: () => {
            isUploadingPhoto.value = false;
        },
    });
};

const submitProfile = () => {
    const base = props.prefix ? `/${props.prefix.replace(/^\//, '')}` : '/superadmin';
    profileForm.post(`${base}/profil-saya`, {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            profileForm.foto = null;
            photoPreview.value = null;
            selectedPhotoName.value = '';
        },
    });
};

const submitPassword = () => {
    const base = props.prefix ? `/${props.prefix.replace(/^\//, '')}` : '/superadmin';
    passwordForm.post(`${base}/profil-saya/password`, {
        preserveScroll: true,
        onSuccess: () => {
            passwordForm.reset();
        },
    });
};

const displayAvatar = computed(() => {
    if (photoPreview.value) return photoPreview.value;
    if (props.user?.foto) {
        if (props.user.foto.startsWith('http') || props.user.foto.startsWith('/')) {
            return props.user.foto;
        }

        const cleanPath = props.user.foto.replace(/^public\//, '');
        if (!cleanPath.includes('/')) {
            return `/uploads/users/${cleanPath}`;
        }

        return `/${cleanPath}`;
    }
    return null;
});

const formattedLastLogin = computed(() => {
    if (!props.user?.last_login) return 'Hari ini';
    try {
        const d = new Date(props.user.last_login);
        return new Intl.DateTimeFormat('id-ID', {
            dateStyle: 'medium',
            timeStyle: 'short',
        }).format(d) + ' WITA';
    } catch {
        return props.user.last_login;
    }
});

const formattedCreatedAt = computed(() => {
    if (!props.user?.created_at) return '2024';
    try {
        const d = new Date(props.user.created_at);
        return new Intl.DateTimeFormat('id-ID', {
            dateStyle: 'long',
        }).format(d);
    } catch {
        return props.user.created_at;
    }
});
</script>

<template>
    <AppLayout title="Profil Saya">
        <Head title="Profil Saya - SIPAROKI" />

        <div class="w-full pb-20 pt-1 space-y-6">
            <!-- 1. HERO HEADER PROFILE CARD -->
            <div class="rounded-3xl bg-gradient-to-r from-amber-500 via-amber-600 to-amber-700 p-6 sm:p-8 text-white shadow-md shadow-amber-500/15 relative overflow-hidden">
                <!-- Background Decoration Shapes -->
                <div class="absolute -right-10 -bottom-10 w-64 h-64 rounded-full bg-white/10 blur-2xl pointer-events-none"></div>
                <div class="absolute right-1/3 -top-10 w-48 h-48 rounded-full bg-amber-400/20 blur-xl pointer-events-none"></div>

                <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div class="flex items-center gap-5">
                        <!-- Avatar Container with Upload Trigger -->
                        <div class="relative group shrink-0">
                            <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl bg-white/20 backdrop-blur-md border-2 border-white/40 overflow-hidden shadow-lg flex items-center justify-center text-white font-black text-2xl sm:text-3xl relative">
                                <img
                                    v-if="displayAvatar"
                                    :src="displayAvatar"
                                    :alt="user?.nama_lengkap || 'Avatar'"
                                    class="w-full h-full object-cover"
                                />
                                <span v-else>
                                    {{ (user?.nama_lengkap || user?.username || 'U').charAt(0).toUpperCase() }}
                                </span>
                                <div v-if="isUploadingPhoto" class="absolute inset-0 bg-black/50 backdrop-blur-xs flex items-center justify-center text-white text-lg">
                                    <i class="fa-solid fa-spinner fa-spin"></i>
                                </div>
                            </div>
                            <label
                                for="header-photo-input"
                                class="absolute -bottom-2 -right-2 w-8 h-8 rounded-xl bg-white text-amber-800 shadow-md flex items-center justify-center text-xs hover:bg-amber-50 transition cursor-pointer border border-amber-200"
                                title="Ubah Foto Profil"
                            >
                                <i v-if="isUploadingPhoto" class="fa-solid fa-spinner fa-spin text-amber-600"></i>
                                <i v-else class="fa-solid fa-camera"></i>
                            </label>
                            <input
                                id="header-photo-input"
                                type="file"
                                accept="image/*"
                                class="hidden"
                                @change="handlePhotoChange"
                            />
                        </div>

                        <!-- Name & Basic Badges -->
                        <div class="space-y-1.5 min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <h1 class="text-xl sm:text-2xl font-black tracking-tight text-white truncate">
                                    {{ user?.nama_lengkap || user?.name || 'Administrator' }}
                                </h1>
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-extrabold bg-white/20 backdrop-blur-md text-amber-50 border border-white/30">
                                    {{ user?.role?.nama_role || role }}
                                </span>
                            </div>
                            <p class="text-xs text-amber-100/90 flex flex-wrap items-center gap-3">
                                <span class="flex items-center gap-1.5">
                                    <i class="fa-solid fa-at text-[11px]"></i>
                                    <span>{{ user?.username || 'admin' }}</span>
                                </span>
                                <span class="flex items-center gap-1.5">
                                    <i class="fa-solid fa-envelope text-[11px]"></i>
                                    <span>{{ user?.email || 'admin@parokibenlutu.org' }}</span>
                                </span>
                                <span v-if="user?.no_hp" class="flex items-center gap-1.5">
                                    <i class="fa-brands fa-whatsapp text-[11px]"></i>
                                    <span>{{ user?.no_hp }}</span>
                                </span>
                            </p>
                        </div>
                    </div>

                    <!-- Right Side Status Badges -->
                    <div class="flex flex-row md:flex-col items-start md:items-end justify-between gap-2 shrink-0 border-t md:border-t-0 border-white/20 pt-3 md:pt-0">
                        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-500/20 backdrop-blur-md border border-emerald-300/40 text-emerald-100 text-xs font-bold">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                            <span>Akun Aktif</span>
                        </div>
                        <p class="text-[11px] text-amber-100/80">
                            Sesi Aktif: {{ formattedLastLogin }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- 2. NAVIGATION TABS (Data Profil vs Keamanan Password) -->
            <div class="flex items-center gap-2 border-b border-slate-200 pb-2">
                <button
                    type="button"
                    @click="activeTab = 'profile'"
                    :class="[
                        'px-4 py-2 rounded-xl text-xs sm:text-sm font-bold transition-all flex items-center gap-2 cursor-pointer',
                        activeTab === 'profile'
                            ? 'bg-amber-500 text-white shadow-sm shadow-amber-500/25'
                            : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200/80'
                    ]"
                >
                    <i class="fa-solid fa-user-pen text-xs"></i>
                    <span>Informasi Data Diri</span>
                </button>
                <button
                    type="button"
                    @click="activeTab = 'security'"
                    :class="[
                        'px-4 py-2 rounded-xl text-xs sm:text-sm font-bold transition-all flex items-center gap-2 cursor-pointer',
                        activeTab === 'security'
                            ? 'bg-amber-500 text-white shadow-sm shadow-amber-500/25'
                            : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200/80'
                    ]"
                >
                    <i class="fa-solid fa-lock text-xs"></i>
                    <span>Keamanan & Password</span>
                </button>
            </div>

            <!-- 3. TAB 1: INFORMASI DATA DIRI -->
            <div v-show="activeTab === 'profile'" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left: Account Details & Assignment Summary -->
                <div class="lg:col-span-1 space-y-5">
                    <div class="rounded-2xl bg-white border border-slate-200/90 p-5 shadow-2xs space-y-4">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 flex items-center gap-2">
                            <i class="fa-solid fa-id-card text-amber-600"></i>
                            <span>Wewenang & Penugasan</span>
                        </h3>

                        <div class="space-y-3 text-xs">
                            <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                                <span class="text-slate-400 block text-[10px] uppercase font-bold">Peran (Role)</span>
                                <span class="font-bold text-slate-800 mt-0.5 block text-sm">
                                    {{ user?.role?.nama_role || role }}
                                </span>
                            </div>

                            <div v-if="user?.wilayah" class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                                <span class="text-slate-400 block text-[10px] uppercase font-bold">Wilayah Binaan</span>
                                <span class="font-bold text-slate-800 mt-0.5 block">
                                    {{ user.wilayah.nama_wilayah }}
                                </span>
                            </div>

                            <div v-if="user?.kapela" class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                                <span class="text-slate-400 block text-[10px] uppercase font-bold">Stasi / Kapela</span>
                                <span class="font-bold text-slate-800 mt-0.5 block">
                                    {{ user.kapela.nama_kapela }}
                                </span>
                            </div>

                            <div v-if="user?.kub" class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                                <span class="text-slate-400 block text-[10px] uppercase font-bold">KUB Binaan</span>
                                <span class="font-bold text-slate-800 mt-0.5 block">
                                    {{ user.kub.nama_kub }}
                                </span>
                            </div>

                            <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                                <span class="text-slate-400 block text-[10px] uppercase font-bold">Terdaftar Sejak</span>
                                <span class="font-bold text-slate-800 mt-0.5 block">
                                    {{ formattedCreatedAt }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Photo Upload Box -->
                    <div class="rounded-2xl bg-white border border-slate-200/90 p-5 shadow-2xs text-center space-y-3">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">
                            Foto Profil Akun
                        </h3>
                        <div class="w-24 h-24 rounded-2xl bg-amber-50 border border-amber-200 mx-auto overflow-hidden flex items-center justify-center text-amber-700 text-2xl font-black shadow-xs relative">
                            <img
                                v-if="displayAvatar"
                                :src="displayAvatar"
                                :alt="user?.nama_lengkap || 'Avatar'"
                                class="w-full h-full object-cover"
                            />
                            <span v-else>
                                {{ (user?.nama_lengkap || 'U').charAt(0).toUpperCase() }}
                            </span>
                            <div v-if="isUploadingPhoto" class="absolute inset-0 bg-black/50 backdrop-blur-xs flex items-center justify-center text-white text-lg">
                                <i class="fa-solid fa-spinner fa-spin"></i>
                            </div>
                        </div>
                        <label
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition cursor-pointer"
                        >
                            <i v-if="isUploadingPhoto" class="fa-solid fa-spinner fa-spin text-amber-600"></i>
                            <i v-else class="fa-solid fa-cloud-arrow-up text-amber-600"></i>
                            <span>{{ isUploadingPhoto ? 'Mengupload...' : 'Pilih Foto Baru' }}</span>
                            <input
                                type="file"
                                accept="image/*"
                                class="hidden"
                                :disabled="isUploadingPhoto"
                                @change="handlePhotoChange"
                            />
                        </label>
                        <p class="text-[10px] text-slate-400">
                            Format JPG, PNG, atau WEBP. Maksimal 2MB.
                        </p>
                        <p v-if="isUploadingPhoto" class="text-[11px] text-amber-600 font-bold animate-pulse">
                            Sedang mengupload & memperbarui foto profil...
                        </p>
                    </div>
                </div>

                <!-- Right: Form Edit Profil -->
                <div class="lg:col-span-2">
                    <form @submit.prevent="submitProfile" class="rounded-2xl bg-white border border-slate-200/90 p-6 sm:p-7 shadow-2xs space-y-6">
                        <div class="border-b border-slate-100 pb-4">
                            <h3 class="text-base font-bold text-slate-900">Perbarui Informasi Profil</h3>
                            <p class="text-xs text-slate-500 mt-0.5">
                                Pastikan nama dan kontak Anda selalu terbarui untuk kelancaran administrasi paroki.
                            </p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <!-- Nama Lengkap -->
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                    Nama Lengkap <span class="text-rose-500">*</span>
                                </label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 text-xs">
                                        <i class="fa-solid fa-user"></i>
                                    </span>
                                    <input
                                        v-model="profileForm.nama_lengkap"
                                        type="text"
                                        required
                                        placeholder="Nama lengkap Anda"
                                        class="w-full pl-9 pr-4 py-2.5 rounded-xl bg-slate-50/80 border border-slate-200 text-xs sm:text-sm font-semibold text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition"
                                    />
                                </div>
                                <p v-if="profileForm.errors.nama_lengkap" class="text-xs text-rose-500 mt-1">
                                    {{ profileForm.errors.nama_lengkap }}
                                </p>
                            </div>

                            <!-- Username -->
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                    Username Login <span class="text-rose-500">*</span>
                                </label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 text-xs">
                                        <i class="fa-solid fa-at"></i>
                                    </span>
                                    <input
                                        v-model="profileForm.username"
                                        type="text"
                                        required
                                        placeholder="Username"
                                        class="w-full pl-9 pr-4 py-2.5 rounded-xl bg-slate-50/80 border border-slate-200 text-xs sm:text-sm font-semibold text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition"
                                    />
                                </div>
                                <p v-if="profileForm.errors.username" class="text-xs text-rose-500 mt-1">
                                    {{ profileForm.errors.username }}
                                </p>
                            </div>

                            <!-- Email -->
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                    Alamat Email <span class="text-rose-500">*</span>
                                </label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 text-xs">
                                        <i class="fa-solid fa-envelope"></i>
                                    </span>
                                    <input
                                        v-model="profileForm.email"
                                        type="email"
                                        required
                                        placeholder="email@paroki.org"
                                        class="w-full pl-9 pr-4 py-2.5 rounded-xl bg-slate-50/80 border border-slate-200 text-xs sm:text-sm font-semibold text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition"
                                    />
                                </div>
                                <p v-if="profileForm.errors.email" class="text-xs text-rose-500 mt-1">
                                    {{ profileForm.errors.email }}
                                </p>
                            </div>

                            <!-- No HP / WA -->
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                    Nomor WhatsApp / HP
                                </label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 text-xs">
                                        <i class="fa-brands fa-whatsapp"></i>
                                    </span>
                                    <input
                                        v-model="profileForm.no_hp"
                                        type="text"
                                        placeholder="0812xxxxxxxx"
                                        class="w-full pl-9 pr-4 py-2.5 rounded-xl bg-slate-50/80 border border-slate-200 text-xs sm:text-sm font-semibold text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition"
                                    />
                                </div>
                                <p v-if="profileForm.errors.no_hp" class="text-xs text-rose-500 mt-1">
                                    {{ profileForm.errors.no_hp }}
                                </p>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                            <button
                                type="submit"
                                :disabled="profileForm.processing"
                                class="px-6 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs sm:text-sm shadow-md shadow-amber-500/25 transition flex items-center gap-2 cursor-pointer disabled:opacity-50"
                            >
                                <i v-if="profileForm.processing" class="fa-solid fa-spinner fa-spin"></i>
                                <i v-else class="fa-solid fa-floppy-disk"></i>
                                <span>Simpan Perubahan Profil</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- 4. TAB 2: KEAMANAN & UBAH PASSWORD -->
            <div v-show="activeTab === 'security'" class="grid grid-cols-1 lg:grid-cols-3 gap-6 w-full">
                <!-- Left: Password Guidelines & Security Tips -->
                <div class="lg:col-span-1 space-y-4">
                    <div class="rounded-2xl bg-white border border-slate-200/90 p-5 shadow-2xs space-y-4">
                        <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl shadow-xs">
                            <i class="fa-solid fa-shield-halved"></i>
                        </div>
                        <div class="space-y-1">
                            <h3 class="font-bold text-sm text-slate-900">Tips Kata Sandi Kuat</h3>
                            <p class="text-xs text-slate-500 leading-relaxed">
                                Gunakan kata sandi yang aman untuk melindungi data umat dan reksa pastoral paroki.
                            </p>
                        </div>

                        <ul class="space-y-2 text-xs text-slate-600">
                            <li class="flex items-start gap-2">
                                <i class="fa-solid fa-check text-emerald-500 mt-0.5 text-[11px]"></i>
                                <span>Minimal 6 karakter atau lebih</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <i class="fa-solid fa-check text-emerald-500 mt-0.5 text-[11px]"></i>
                                <span>Kombinasi huruf besar, kecil & angka</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <i class="fa-solid fa-check text-emerald-500 mt-0.5 text-[11px]"></i>
                                <span>Jangan gunakan tanggal lahir yang mudah ditebak</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Right: Form Ubah Password (Full Width) -->
                <div class="lg:col-span-2 w-full">
                    <form @submit.prevent="submitPassword" class="rounded-2xl bg-white border border-slate-200/90 p-6 sm:p-7 shadow-2xs space-y-6 w-full">
                        <div class="border-b border-slate-100 pb-4">
                            <h3 class="text-base font-bold text-slate-900">Ganti Kata Sandi Akun</h3>
                            <p class="text-xs text-slate-500 mt-0.5">
                                Masukkan kata sandi baru Anda dan simpan perubahannya.
                            </p>
                        </div>

                        <div class="space-y-5 w-full">
                            <!-- Password Saat Ini -->
                            <div class="w-full">
                                <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                    Kata Sandi Saat Ini (Opsional / Jika Diperlukan)
                                </label>
                                <div class="relative w-full">
                                    <input
                                        v-model="passwordForm.current_password"
                                        :type="showCurrentPassword ? 'text' : 'password'"
                                        placeholder="Masukkan kata sandi lama"
                                        class="w-full pl-4 pr-10 py-2.5 rounded-xl bg-slate-50/80 border border-slate-200 text-xs sm:text-sm font-semibold text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition"
                                    />
                                    <button
                                        type="button"
                                        @click="showCurrentPassword = !showCurrentPassword"
                                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-700 text-xs"
                                    >
                                        <i :class="showCurrentPassword ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye'"></i>
                                    </button>
                                </div>
                                <p v-if="passwordForm.errors.current_password" class="text-xs text-rose-500 mt-1">
                                    {{ passwordForm.errors.current_password }}
                                </p>
                            </div>

                            <!-- Password Baru -->
                            <div class="w-full">
                                <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                    Kata Sandi Baru <span class="text-rose-500">*</span>
                                </label>
                                <div class="relative w-full">
                                    <input
                                        v-model="passwordForm.new_password"
                                        :type="showNewPassword ? 'text' : 'password'"
                                        required
                                        placeholder="Minimal 6 karakter"
                                        class="w-full pl-4 pr-10 py-2.5 rounded-xl bg-slate-50/80 border border-slate-200 text-xs sm:text-sm font-semibold text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition"
                                    />
                                    <button
                                        type="button"
                                        @click="showNewPassword = !showNewPassword"
                                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-700 text-xs"
                                    >
                                        <i :class="showNewPassword ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye'"></i>
                                    </button>
                                </div>
                                <p v-if="passwordForm.errors.new_password" class="text-xs text-rose-500 mt-1">
                                    {{ passwordForm.errors.new_password }}
                                </p>
                            </div>

                            <!-- Konfirmasi Password Baru -->
                            <div class="w-full">
                                <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                    Konfirmasi Kata Sandi Baru <span class="text-rose-500">*</span>
                                </label>
                                <div class="relative w-full">
                                    <input
                                        v-model="passwordForm.new_password_confirmation"
                                        :type="showConfirmPassword ? 'text' : 'password'"
                                        required
                                        placeholder="Ketik ulang kata sandi baru"
                                        class="w-full pl-4 pr-10 py-2.5 rounded-xl bg-slate-50/80 border border-slate-200 text-xs sm:text-sm font-semibold text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition"
                                    />
                                    <button
                                        type="button"
                                        @click="showConfirmPassword = !showConfirmPassword"
                                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-700 text-xs"
                                    >
                                        <i :class="showConfirmPassword ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye'"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                            <button
                                type="submit"
                                :disabled="passwordForm.processing"
                                class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs sm:text-sm shadow-md shadow-emerald-600/25 transition flex items-center gap-2 cursor-pointer disabled:opacity-50"
                            >
                                <i v-if="passwordForm.processing" class="fa-solid fa-spinner fa-spin"></i>
                                <i v-else class="fa-solid fa-key"></i>
                                <span>Perbarui Kata Sandi</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
