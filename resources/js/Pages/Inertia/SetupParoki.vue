<script setup>
import { ref, computed, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    keuskupanList: { type: Array, default: () => [] },
    currentPengaturan: { type: Object, default: () => ({}) },
    currentProfil: { type: Object, default: () => ({}) },
    currentParoki: { type: Object, default: null },
    isSetupCompleted: { type: Boolean, default: false },
    auth: { type: Object, default: () => ({ user: null }) },
});

// Wizard Steps
const currentStep = ref(1);

// Search Query for Keuskupan
const searchKeuskupan = ref('');

// Form State
const form = ref({
    mode: 'pilih_ada', // 'pilih_ada' | 'buat_baru'
    keuskupan_id: props.currentParoki?.keuskupan_id || props.currentProfil?.keuskupan_id || '',
    dekenat_id: props.currentParoki?.dekenat_id || props.currentProfil?.dekenat_id || '',
    paroki_id: props.currentParoki?.id_paroki || props.currentProfil?.paroki_id || '',
    nama_paroki: props.currentParoki?.nama_paroki || props.currentProfil?.nama_paroki || props.currentPengaturan?.nama_paroki || '',
    pelindung_paroki: props.currentParoki?.pelindung_paroki || props.currentProfil?.pelindung || '',
    nama_pastor_paroki_aktif: props.currentParoki?.nama_pastor_paroki_aktif || props.currentProfil?.pastor_paroki || '',
    alamat: props.currentParoki?.alamat || props.currentProfil?.alamat || props.currentPengaturan?.alamat_paroki || '',
    telepon: props.currentParoki?.telepon || props.currentProfil?.telepon || props.currentPengaturan?.telepon_paroki || '',
    whatsapp: props.currentParoki?.whatsapp || '',
    email: props.currentParoki?.email || props.currentProfil?.email || props.currentPengaturan?.email_paroki || '',
    website: props.currentParoki?.website || props.currentProfil?.website || '',
    logo: null,
    banner: null,
});

const logoPreview = ref(props.currentPengaturan?.logo || props.currentProfil?.logo || props.currentParoki?.logo || null);
const isSubmitting = ref(false);
const errors = ref({});

// Selected Keuskupan Object
const selectedKeuskupan = computed(() => {
    return props.keuskupanList.find(k => String(k.id_keuskupan) === String(form.value.keuskupan_id)) || null;
});

// Filtered Keuskupans by search query
const filteredKeuskupans = computed(() => {
    if (!searchKeuskupan.value.trim()) return props.keuskupanList;
    const q = searchKeuskupan.value.toLowerCase();
    return props.keuskupanList.filter(k => 
        (k.nama_keuskupan && k.nama_keuskupan.toLowerCase().includes(q)) ||
        (k.nama_latin && k.nama_latin.toLowerCase().includes(q)) ||
        (k.uskup && k.uskup.toLowerCase().includes(q))
    );
});

// Dekenat list for selected Keuskupan
const availableDekenats = computed(() => {
    return selectedKeuskupan.value?.dekenats || [];
});

// Parokis list for selected Keuskupan & Dekenat
const availableParokis = computed(() => {
    if (!selectedKeuskupan.value) return [];
    if (form.value.dekenat_id) {
        const dekenat = availableDekenats.value.find(d => String(d.id) === String(form.value.dekenat_id));
        return dekenat?.parokis || [];
    }
    // All parokis in this keuskupan
    const all = [];
    if (selectedKeuskupan.value.parokis) {
        all.push(...selectedKeuskupan.value.parokis);
    }
    if (selectedKeuskupan.value.dekenats) {
        selectedKeuskupan.value.dekenats.forEach(d => {
            if (d.parokis) {
                d.parokis.forEach(p => {
                    if (!all.some(item => item.id_paroki === p.id_paroki)) {
                        all.push(p);
                    }
                });
            }
        });
    }
    return all;
});

// When user selects a keuskupan
const chooseKeuskupan = (k) => {
    form.value.keuskupan_id = k.id_keuskupan;
    form.value.dekenat_id = '';
    form.value.paroki_id = '';
    currentStep.value = 2;
};

// When user selects an existing parish
const chooseParoki = (p) => {
    form.value.mode = 'pilih_ada';
    form.value.paroki_id = p.id_paroki;
    form.value.dekenat_id = p.dekenat_id || form.value.dekenat_id;
    form.value.nama_paroki = p.nama_paroki || '';
    form.value.pelindung_paroki = p.pelindung_paroki || '';
    form.value.nama_pastor_paroki_aktif = p.nama_pastor_paroki_aktif || '';
    form.value.alamat = p.alamat || '';
    form.value.telepon = p.telepon || '';
    form.value.whatsapp = p.whatsapp || '';
    form.value.email = p.email || '';
    form.value.website = p.website || '';
    if (p.logo) logoPreview.value = '/' + p.logo.replace(/^\//, '');
    currentStep.value = 3;
};

// When user chooses to create a new parish
const createNewParoki = () => {
    form.value.mode = 'buat_baru';
    form.value.paroki_id = '';
    form.value.nama_paroki = '';
    form.value.pelindung_paroki = '';
    form.value.nama_pastor_paroki_aktif = '';
    form.value.alamat = '';
    form.value.telepon = '';
    form.value.whatsapp = '';
    form.value.email = '';
    currentStep.value = 3;
};

// Handle Logo Upload
const handleLogoChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        form.value.logo = file;
        logoPreview.value = URL.createObjectURL(file);
    }
};

// Submit Setup
const submitSetup = () => {
    errors.value = {};
    if (!form.value.keuskupan_id) {
        errors.value.keuskupan_id = 'Silakan pilih Keuskupan terlebih dahulu.';
        currentStep.value = 1;
        return;
    }
    if (!form.value.nama_paroki.trim()) {
        errors.value.nama_paroki = 'Nama Paroki wajib diisi.';
        currentStep.value = 3;
        return;
    }

    isSubmitting.value = true;

    router.post('/setup-paroki', form.value, {
        forceFormData: true,
        onError: (err) => {
            errors.value = err;
            isSubmitting.value = false;
        },
        onFinish: () => {
            isSubmitting.value = false;
        }
    });
};
</script>

<template>
    <Head title="Inisialisasi & Setup Paroki Default - SIPAROKI" />

    <div class="min-h-screen bg-slate-900 text-slate-100 flex flex-col justify-between selection:bg-amber-500 selection:text-white relative overflow-x-hidden font-sans">
        <!-- Background Ambient Glow -->
        <div class="absolute -top-40 -left-40 w-96 h-96 bg-amber-500/15 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-40 -right-40 w-96 h-96 bg-amber-600/15 rounded-full blur-3xl pointer-events-none"></div>

        <!-- Top Header Navigation -->
        <header class="w-full border-b border-slate-800 bg-slate-900/80 backdrop-blur-md sticky top-0 z-30 px-4 sm:px-8 py-3.5 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-amber-500 to-amber-400 flex items-center justify-center text-white shadow-lg shadow-amber-500/20 font-black text-xl border border-amber-300/40">
                    <i class="fa-solid fa-church"></i>
                </div>
                <div>
                    <h1 class="text-base sm:text-lg font-black tracking-tight text-white flex items-center gap-2">
                        SIPAROKI <span class="text-xs px-2 py-0.5 rounded-full bg-amber-500/20 text-amber-300 font-semibold border border-amber-500/30">Setup Wizard</span>
                    </h1>
                    <p class="text-[11px] text-slate-400">Inisialisasi Paroki & Keuskupan Default Sistem</p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <template v-if="isSetupCompleted">
                    <a href="/superadmin" class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold border border-slate-700 transition">
                        <i class="fa-solid fa-gauge-high text-amber-400"></i>
                        <span>Ke Dashboard</span>
                    </a>
                </template>
                <a href="/" class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl bg-amber-500/10 hover:bg-amber-500/20 text-amber-300 text-xs font-semibold border border-amber-500/30 transition">
                    <i class="fa-solid fa-house"></i>
                    <span>Situs Paroki</span>
                </a>
            </div>
        </header>

        <!-- Main Content Wizard Body -->
        <main class="flex-1 max-w-5xl w-full mx-auto p-4 sm:p-6 lg:p-8 flex flex-col justify-center">
            
            <!-- Step Indicators -->
            <div class="mb-8">
                <div class="grid grid-cols-3 gap-2 sm:gap-4 max-w-2xl mx-auto">
                    <!-- Step 1 Indicator -->
                    <button 
                        type="button" 
                        @click="currentStep = 1"
                        :class="[
                            'flex items-center gap-3 p-3 rounded-2xl border transition-all text-left',
                            currentStep === 1 ? 'bg-amber-500/15 border-amber-500 text-white shadow-lg shadow-amber-500/10' : 
                            currentStep > 1 ? 'bg-slate-800/80 border-slate-700 text-emerald-400' : 'bg-slate-800/40 border-slate-800 text-slate-500'
                        ]"
                    >
                        <div :class="[
                            'w-7 h-7 rounded-xl flex items-center justify-center text-xs font-bold shrink-0',
                            currentStep === 1 ? 'bg-amber-500 text-slate-950 font-black' : 
                            currentStep > 1 ? 'bg-emerald-500/20 text-emerald-400' : 'bg-slate-700 text-slate-400'
                        ]">
                            <i v-if="currentStep > 1" class="fa-solid fa-check text-xs"></i>
                            <span v-else>1</span>
                        </div>
                        <div class="hidden sm:block min-w-0">
                            <p class="text-xs font-bold leading-tight truncate">Keuskupan</p>
                            <p class="text-[10px] text-slate-400 truncate">Pilih Keuskupan</p>
                        </div>
                    </button>

                    <!-- Step 2 Indicator -->
                    <button 
                        type="button" 
                        @click="form.keuskupan_id ? (currentStep = 2) : null"
                        :disabled="!form.keuskupan_id"
                        :class="[
                            'flex items-center gap-3 p-3 rounded-2xl border transition-all text-left',
                            currentStep === 2 ? 'bg-amber-500/15 border-amber-500 text-white shadow-lg shadow-amber-500/10' : 
                            currentStep > 2 ? 'bg-slate-800/80 border-slate-700 text-emerald-400' : 'bg-slate-800/40 border-slate-800 text-slate-500',
                            !form.keuskupan_id ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'
                        ]"
                    >
                        <div :class="[
                            'w-7 h-7 rounded-xl flex items-center justify-center text-xs font-bold shrink-0',
                            currentStep === 2 ? 'bg-amber-500 text-slate-950 font-black' : 
                            currentStep > 2 ? 'bg-emerald-500/20 text-emerald-400' : 'bg-slate-700 text-slate-400'
                        ]">
                            <i v-if="currentStep > 2" class="fa-solid fa-check text-xs"></i>
                            <span v-else>2</span>
                        </div>
                        <div class="hidden sm:block min-w-0">
                            <p class="text-xs font-bold leading-tight truncate">Pilih Paroki</p>
                            <p class="text-[10px] text-slate-400 truncate">Pilih / Buat Baru</p>
                        </div>
                    </button>

                    <!-- Step 3 Indicator -->
                    <button 
                        type="button" 
                        @click="form.nama_paroki ? (currentStep = 3) : null"
                        :disabled="!form.keuskupan_id"
                        :class="[
                            'flex items-center gap-3 p-3 rounded-2xl border transition-all text-left',
                            currentStep === 3 ? 'bg-amber-500/15 border-amber-500 text-white shadow-lg shadow-amber-500/10' : 'bg-slate-800/40 border-slate-800 text-slate-500',
                            !form.keuskupan_id ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'
                        ]"
                    >
                        <div :class="[
                            'w-7 h-7 rounded-xl flex items-center justify-center text-xs font-bold shrink-0',
                            currentStep === 3 ? 'bg-amber-500 text-slate-950 font-black' : 'bg-slate-700 text-slate-400'
                        ]">
                            <span>3</span>
                        </div>
                        <div class="hidden sm:block min-w-0">
                            <p class="text-xs font-bold leading-tight truncate">Identitas & Simpan</p>
                            <p class="text-[10px] text-slate-400 truncate">Lengkapi & Aktifkan</p>
                        </div>
                    </button>
                </div>
            </div>

            <!-- Wizard Card Container -->
            <div class="bg-slate-800/90 border border-slate-700 rounded-3xl p-6 sm:p-8 lg:p-10 shadow-2xl backdrop-blur-md relative">
                
                <!-- STEP 1: PILIH KEUSKUPAN -->
                <div v-if="currentStep === 1" class="space-y-6 animate-in fade-in duration-300">
                    <div class="text-center max-w-xl mx-auto space-y-2">
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-500/10 text-amber-300 text-xs font-semibold border border-amber-500/20">
                            <i class="fa-solid fa-building-columns"></i>
                            <span>Langkah 1 dari 3</span>
                        </div>
                        <h2 class="text-xl sm:text-2xl font-black text-white tracking-tight">Pilih Keuskupan Paroki Anda</h2>
                        <p class="text-xs sm:text-sm text-slate-400 leading-relaxed">
                            Silakan pilih Keuskupan tempat paroki Anda bernaung. Master data ini akan menjadi acuan hierarki gerejawi di dalam sistem.
                        </p>
                    </div>

                    <!-- Search Input -->
                    <div class="max-w-md mx-auto relative">
                        <i class="fa-solid fa-magnifying-glass absolute left-4 top-3.5 text-slate-400 text-sm"></i>
                        <input 
                            v-model="searchKeuskupan" 
                            type="text" 
                            placeholder="Cari nama keuskupan atau uskup..." 
                            class="w-full pl-11 pr-4 py-3 bg-slate-900/90 border border-slate-700 rounded-2xl text-sm text-white placeholder-slate-500 focus:outline-none focus:border-amber-500 transition shadow-inner"
                        />
                    </div>

                    <!-- Keuskupan Grid List -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3.5 max-h-[380px] overflow-y-auto pr-1 custom-scrollbar">
                        <div 
                            v-for="k in filteredKeuskupans" 
                            :key="k.id_keuskupan"
                            @click="chooseKeuskupan(k)"
                            :class="[
                                'p-4 rounded-2xl border transition-all cursor-pointer group hover:border-amber-500 hover:bg-slate-700/60 flex flex-col justify-between space-y-3',
                                String(form.keuskupan_id) === String(k.id_keuskupan) 
                                    ? 'bg-amber-500/15 border-amber-500 shadow-md shadow-amber-500/10' 
                                    : 'bg-slate-900/60 border-slate-700/80'
                            ]"
                        >
                            <div class="flex items-start gap-3">
                                <div class="w-10 h-10 rounded-xl bg-amber-500/10 border border-amber-500/30 flex items-center justify-center text-amber-400 text-lg group-hover:scale-105 transition-transform shrink-0">
                                    <i class="fa-solid fa-building-columns"></i>
                                </div>
                                <div class="min-w-0">
                                    <h3 class="font-bold text-sm text-white group-hover:text-amber-300 transition-colors leading-tight truncate">
                                        {{ k.nama_keuskupan }}
                                    </h3>
                                    <p class="text-[11px] text-slate-400 truncate mt-0.5">{{ k.uskup ? 'Uskup: ' + k.uskup : (k.nama_latin || 'Keuskupan di Indonesia') }}</p>
                                </div>
                            </div>

                            <div class="flex items-center justify-between text-[11px] text-slate-400 pt-2 border-t border-slate-700/50">
                                <span>{{ (k.dekenats?.length || 0) }} Dekenat</span>
                                <span class="text-amber-400 font-semibold flex items-center gap-1 group-hover:translate-x-0.5 transition-transform">
                                    Pilih <i class="fa-solid fa-arrow-right text-[10px]"></i>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div v-if="filteredKeuskupans.length === 0" class="text-center py-8 text-slate-400">
                        <i class="fa-solid fa-circle-question text-3xl text-slate-600 mb-2"></i>
                        <p class="text-xs">Keuskupan tidak ditemukan untuk pencarian "{{ searchKeuskupan }}".</p>
                    </div>
                </div>

                <!-- STEP 2: PILIH / BUAT PAROKI -->
                <div v-if="currentStep === 2" class="space-y-6 animate-in fade-in duration-300">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 pb-4 border-b border-slate-700">
                        <div>
                            <div class="flex items-center gap-2 text-xs font-bold text-amber-400 mb-1">
                                <i class="fa-solid fa-building-columns"></i>
                                <span>{{ selectedKeuskupan?.nama_keuskupan }}</span>
                            </div>
                            <h2 class="text-xl font-black text-white tracking-tight">Pilih Paroki Anda</h2>
                            <p class="text-xs text-slate-400">Pilih dari paroki yang sudah terdaftar atau buat paroki baru jika belum ada.</p>
                        </div>

                        <!-- Action buttons -->
                        <div class="flex items-center gap-2.5 w-full sm:w-auto">
                            <button 
                                type="button" 
                                @click="currentStep = 1" 
                                class="px-3.5 py-2 rounded-xl bg-slate-700 hover:bg-slate-600 text-slate-200 text-xs font-bold transition flex items-center gap-2"
                            >
                                <i class="fa-solid fa-arrow-left"></i> Ganti Keuskupan
                            </button>
                            <button 
                                type="button" 
                                @click="createNewParoki" 
                                class="px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-black text-xs shadow-md shadow-amber-500/20 transition flex items-center gap-2"
                            >
                                <i class="fa-solid fa-plus"></i> Buat Paroki Baru
                            </button>
                        </div>
                    </div>

                    <!-- Dekenat Filter Tabs if available -->
                    <div v-if="availableDekenats.length > 0" class="flex items-center gap-2 overflow-x-auto pb-2 custom-scrollbar">
                        <button 
                            type="button" 
                            @click="form.dekenat_id = ''"
                            :class="[
                                'px-3 py-1.5 rounded-xl text-xs font-bold shrink-0 transition',
                                form.dekenat_id === '' ? 'bg-amber-500 text-slate-950' : 'bg-slate-900/80 text-slate-400 hover:text-white'
                            ]"
                        >
                            Semua Dekenat ({{ availableDekenats.length }})
                        </button>
                        <button 
                            v-for="d in availableDekenats" 
                            :key="d.id"
                            type="button" 
                            @click="form.dekenat_id = d.id"
                            :class="[
                                'px-3 py-1.5 rounded-xl text-xs font-bold shrink-0 transition',
                                String(form.dekenat_id) === String(d.id) ? 'bg-amber-500 text-slate-950' : 'bg-slate-900/80 text-slate-400 hover:text-white'
                            ]"
                        >
                            {{ d.nama_dekenat }}
                        </button>
                    </div>

                    <!-- Paroki List Grid -->
                    <div v-if="availableParokis.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3.5 max-h-[340px] overflow-y-auto pr-1 custom-scrollbar">
                        <div 
                            v-for="p in availableParokis" 
                            :key="p.id_paroki"
                            @click="chooseParoki(p)"
                            :class="[
                                'p-4 rounded-2xl border transition-all cursor-pointer group hover:border-amber-500 hover:bg-slate-700/60 flex flex-col justify-between space-y-3',
                                String(form.paroki_id) === String(p.id_paroki) 
                                    ? 'bg-amber-500/15 border-amber-500 shadow-md shadow-amber-500/10' 
                                    : 'bg-slate-900/60 border-slate-700/80'
                            ]"
                        >
                            <div class="flex items-start gap-3">
                                <div class="w-10 h-10 rounded-xl bg-amber-500/10 border border-amber-500/30 flex items-center justify-center text-amber-400 text-lg group-hover:scale-105 transition-transform shrink-0">
                                    <i class="fa-solid fa-church"></i>
                                </div>
                                <div class="min-w-0">
                                    <h3 class="font-bold text-sm text-white group-hover:text-amber-300 transition-colors leading-tight truncate">
                                        {{ p.nama_paroki }}
                                    </h3>
                                    <p class="text-[11px] text-slate-400 truncate mt-0.5">
                                        {{ p.pelindung_paroki || p.nama_pastor_paroki_aktif || 'Paroki Terdaftar' }}
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-center justify-between text-[11px] text-slate-400 pt-2 border-t border-slate-700/50">
                                <span class="truncate max-w-[140px]">{{ p.alamat || 'Alamat belum diisi' }}</span>
                                <span class="text-amber-400 font-semibold flex items-center gap-1 shrink-0 group-hover:translate-x-0.5 transition-transform">
                                    Pilih <i class="fa-solid fa-arrow-right text-[10px]"></i>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Empty State for Parish -->
                    <div v-else class="text-center py-10 bg-slate-900/40 border border-dashed border-slate-700 rounded-2xl space-y-3 p-6">
                        <div class="w-12 h-12 rounded-2xl bg-amber-500/10 text-amber-400 flex items-center justify-center text-xl mx-auto">
                            <i class="fa-solid fa-church"></i>
                        </div>
                        <div class="space-y-1">
                            <h4 class="font-bold text-white text-sm">Belum ada data Paroki di Keuskupan/Dekenat ini</h4>
                            <p class="text-xs text-slate-400 max-w-sm mx-auto">Anda dapat mendaftarkan nama paroki Anda secara langsung untuk memulai.</p>
                        </div>
                        <button 
                            type="button" 
                            @click="createNewParoki" 
                            class="px-5 py-2 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs shadow-md transition"
                        >
                            + Daftarkan Paroki Saya Sekarang
                        </button>
                    </div>
                </div>

                <!-- STEP 3: LENGKAPI IDENTITAS & SIMPAN -->
                <div v-if="currentStep === 3" class="space-y-6 animate-in fade-in duration-300">
                    <div class="flex items-center justify-between pb-4 border-b border-slate-700">
                        <div>
                            <div class="flex items-center gap-2 text-xs font-bold text-amber-400 mb-1">
                                <span>{{ selectedKeuskupan?.nama_keuskupan }}</span>
                                <span>&bull;</span>
                                <span class="px-2 py-0.5 rounded-full bg-slate-700 text-slate-300 text-[10px]">
                                    {{ form.mode === 'buat_baru' ? 'Paroki Baru' : 'Paroki Terpilih' }}
                                </span>
                            </div>
                            <h2 class="text-xl font-black text-white tracking-tight">Konfirmasi Identitas Paroki</h2>
                            <p class="text-xs text-slate-400">Pastikan detail informasi paroki Anda telah sesuai sebelum mengaktifkan sistem.</p>
                        </div>

                        <button 
                            type="button" 
                            @click="currentStep = 2" 
                            class="px-3.5 py-2 rounded-xl bg-slate-700 hover:bg-slate-600 text-slate-200 text-xs font-bold transition flex items-center gap-2"
                        >
                            <i class="fa-solid fa-arrow-left"></i> Kembali
                        </button>
                    </div>

                    <form @submit.prevent="submitSetup" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            
                            <!-- Nama Paroki -->
                            <div class="space-y-1.5 md:col-span-2">
                                <label class="text-xs font-bold text-slate-300 flex items-center justify-between">
                                    <span>Nama Paroki <span class="text-rose-400">*</span></span>
                                    <span class="text-[10px] text-slate-500">Contoh: Paroki St. Vinsensius a Paulo - Benlutu</span>
                                </label>
                                <input 
                                    v-model="form.nama_paroki" 
                                    type="text" 
                                    required 
                                    placeholder="Masukkan nama resmi paroki..." 
                                    class="w-full px-4 py-2.5 bg-slate-900 border border-slate-700 rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:border-amber-500 transition"
                                />
                                <p v-if="errors.nama_paroki" class="text-xs text-rose-400">{{ errors.nama_paroki }}</p>
                            </div>

                            <!-- Pelindung Paroki -->
                            <div class="space-y-1.5">
                                <label class="text-xs font-bold text-slate-300">Santo / Santa Pelindung</label>
                                <input 
                                    v-model="form.pelindung_paroki" 
                                    type="text" 
                                    placeholder="Contoh: Santo Vinsensius a Paulo" 
                                    class="w-full px-4 py-2.5 bg-slate-900 border border-slate-700 rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:border-amber-500 transition"
                                />
                            </div>

                            <!-- Pastor Paroki Aktif -->
                            <div class="space-y-1.5">
                                <label class="text-xs font-bold text-slate-300">Nama Pastor Paroki Aktif</label>
                                <input 
                                    v-model="form.nama_pastor_paroki_aktif" 
                                    type="text" 
                                    placeholder="Contoh: RD. Herman Hilers Penga" 
                                    class="w-full px-4 py-2.5 bg-slate-900 border border-slate-700 rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:border-amber-500 transition"
                                />
                            </div>

                            <!-- Alamat Lengkap -->
                            <div class="space-y-1.5 md:col-span-2">
                                <label class="text-xs font-bold text-slate-300">Alamat Lengkap Paroki / Sekretariat</label>
                                <textarea 
                                    v-model="form.alamat" 
                                    rows="2" 
                                    placeholder="Jl. Raya Utama No. X, Desa/Kelurahan, Kecamatan, Kabupaten..." 
                                    class="w-full px-4 py-2.5 bg-slate-900 border border-slate-700 rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:border-amber-500 transition"
                                ></textarea>
                            </div>

                            <!-- Telepon / WhatsApp -->
                            <div class="space-y-1.5">
                                <label class="text-xs font-bold text-slate-300">Nomor Telepon / WhatsApp Sekretariat</label>
                                <input 
                                    v-model="form.telepon" 
                                    type="text" 
                                    placeholder="Contoh: 0812-3456-7890" 
                                    class="w-full px-4 py-2.5 bg-slate-900 border border-slate-700 rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:border-amber-500 transition"
                                />
                            </div>

                            <!-- Email Paroki -->
                            <div class="space-y-1.5">
                                <label class="text-xs font-bold text-slate-300">Email Resmi Paroki</label>
                                <input 
                                    v-model="form.email" 
                                    type="email" 
                                    placeholder="Contoh: sekretariat@paroki.org" 
                                    class="w-full px-4 py-2.5 bg-slate-900 border border-slate-700 rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:border-amber-500 transition"
                                />
                            </div>

                            <!-- Upload Logo Paroki -->
                            <div class="space-y-1.5 md:col-span-2">
                                <label class="text-xs font-bold text-slate-300">Logo Paroki / Gereja (Opsional)</label>
                                <div class="flex items-center gap-4 p-4 bg-slate-900/60 border border-slate-700 rounded-2xl">
                                    <div class="w-16 h-16 rounded-2xl bg-slate-800 border border-slate-700 overflow-hidden flex items-center justify-center shrink-0">
                                        <img v-if="logoPreview" :src="logoPreview" alt="Logo Preview" class="w-full h-full object-cover" />
                                        <i v-else class="fa-solid fa-church text-2xl text-slate-500"></i>
                                    </div>
                                    <div class="space-y-1.5 flex-1">
                                        <input 
                                            type="file" 
                                            accept="image/*" 
                                            @change="handleLogoChange" 
                                            class="block w-full text-xs text-slate-400 file:mr-4 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-amber-500/20 file:text-amber-300 hover:file:bg-amber-500/30 cursor-pointer"
                                        />
                                        <p class="text-[11px] text-slate-500">Format: JPG, PNG, WEBP (Maks. 3 MB). Logo akan ditampilkan di header & dokumen resmi.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Confirmation Alert Box -->
                        <div class="p-4 rounded-2xl bg-amber-500/10 border border-amber-500/30 flex items-start gap-3.5">
                            <i class="fa-solid fa-circle-info text-amber-400 text-lg mt-0.5 shrink-0"></i>
                            <div class="text-xs text-slate-300 leading-relaxed">
                                <span class="font-bold text-amber-300">Pemberitahuan:</span> 
                                Setelah Anda menekan tombol di bawah, sistem SIPAROKI akan secara otomatis memperbarui seluruh identitas website, panel administrasi, dan sistem pelaporan menjadi atas nama <strong class="text-white">{{ form.nama_paroki || 'Paroki Anda' }}</strong>.
                            </div>
                        </div>

                        <!-- Action Submit Button -->
                        <div class="pt-2 flex items-center justify-end gap-3">
                            <button 
                                type="submit" 
                                :disabled="isSubmitting"
                                class="w-full sm:w-auto px-8 py-3.5 rounded-2xl bg-gradient-to-r from-amber-500 to-amber-400 hover:from-amber-400 hover:to-amber-300 text-slate-950 font-black text-sm shadow-xl shadow-amber-500/25 transition-all transform active:scale-98 flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50"
                            >
                                <i v-if="isSubmitting" class="fa-solid fa-spinner fa-spin"></i>
                                <i v-else class="fa-solid fa-circle-check text-base"></i>
                                <span>Simpan & Terapkan Sebagai Paroki Default</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="w-full border-t border-slate-800/80 py-4 px-4 sm:px-8 text-center text-xs text-slate-500">
            <p>SIPAROKI &copy; {{ new Date().getFullYear() }} &bull; Sistem Informasi Manajemen Pastoral Paroki</p>
        </footer>
    </div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 5px;
    height: 5px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: rgba(15, 23, 42, 0.4);
    border-radius: 9999px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(245, 158, 11, 0.3);
    border-radius: 9999px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: rgba(245, 158, 11, 0.6);
}
</style>
