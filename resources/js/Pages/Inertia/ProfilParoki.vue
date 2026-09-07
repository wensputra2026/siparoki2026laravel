<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import RichTextEditor from '@/Components/RichTextEditor.vue';

const props = defineProps({
    paroki: { type: Object, required: true },
    allParokis: { type: Array, default: () => [] },
    defaultParokiId: { type: [Number, String], default: null },
    totalWilayah: { type: Number, default: 0 },
    totalLingkungan: { type: Number, default: 0 },
    totalKapela: { type: Number, default: 0 },
    totalKub: { type: Number, default: 0 },
    totalUmat: { type: Number, default: 0 },
    totalKk: { type: Number, default: 0 },
    role: { type: String, default: 'Super Admin' },
    prefix: { type: String, default: 'superadmin' },
    keuskupanList: { type: Array, default: () => [] },
    dekenatList: { type: Array, default: () => [] },
    provinsiList: { type: Array, default: () => [] },
    kabupatenList: { type: Array, default: () => [] },
    kecamatanList: { type: Array, default: () => [] },
    desaList: { type: Array, default: () => [] },
    pastors: { type: Array, default: () => [] },
});

const selectedParokiId = ref(props.paroki?.id_paroki || props.defaultParokiId || '');
const showEditModal = ref(false);
const showImageModal = ref(false);
const showBannerModal = ref(false);
const isSubmitting = ref(false);
const logoPreview = ref(props.paroki?.logo || '');
const bannerPreview = ref(props.paroki?.banner || '');

const isDropdownOpen = ref(false);
const parokiSearchQuery = ref('');
const dropdownRef = ref(null);

const filteredParokis = computed(() => {
    if (!parokiSearchQuery.value.trim()) return props.allParokis;
    const q = parokiSearchQuery.value.toLowerCase();
    return props.allParokis.filter(
        (p) =>
            (p.nama_paroki && p.nama_paroki.toLowerCase().includes(q)) ||
            (p.keuskupan && p.keuskupan.nama_keuskupan && p.keuskupan.nama_keuskupan.toLowerCase().includes(q)) ||
            (p.kode_paroki && p.kode_paroki.toLowerCase().includes(q))
    );
});

const selectedParokiName = computed(() => {
    const found = props.allParokis.find((p) => String(p.id_paroki) === String(selectedParokiId.value));
    if (!found) return props.paroki?.nama_paroki || 'Pilih Paroki...';
    const keuskupanName = found.keuskupan ? found.keuskupan.nama_keuskupan : 'Keuskupan Agung Kupang';
    return `${found.nama_paroki} (${keuskupanName})`;
});

const selectParokiItem = (p) => {
    selectedParokiId.value = p.id_paroki;
    isDropdownOpen.value = false;
    parokiSearchQuery.value = '';
};

// Filter pastors assigned to this paroki from master_pastor
const pastorsBertugas = computed(() => {
    const curParokiId = props.paroki?.id_paroki;
    const curParokiName = (props.paroki?.nama_paroki || '').trim().toLowerCase();

    return (props.pastors || []).filter((p) => {
        // 1. Check exact paroki_id match
        if (curParokiId && p.paroki_id && String(p.paroki_id) === String(curParokiId)) {
            return true;
        }
        // 2. Check paroki_tugas string match
        const pTugas = (p.paroki_tugas || p.paroki || '').trim().toLowerCase();
        if (curParokiName && pTugas && (pTugas.includes(curParokiName) || curParokiName.includes(pTugas))) {
            return true;
        }
        return false;
    });
});

const handleDropdownClickOutside = (e) => {
    if (dropdownRef.value && !dropdownRef.value.contains(e.target)) {
        isDropdownOpen.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', handleDropdownClickOutside);
});

onBeforeUnmount(() => {
    document.removeEventListener('click', handleDropdownClickOutside);
});

const form = ref({
    nama_paroki: props.paroki?.nama_paroki || '',
    kode_paroki: props.paroki?.kode_paroki || '',
    pelindung_paroki: props.paroki?.pelindung_paroki || '',
    status: props.paroki?.status || 'Aktif',
    status_paroki: props.paroki?.status_paroki || 'Paroki',
    tanggal_berdiri: props.paroki?.tanggal_berdiri ? String(props.paroki.tanggal_berdiri).substring(0, 10) : '',
    nama_pastor_paroki_aktif: props.paroki?.nama_pastor_paroki_aktif || '',
    nama_pastor_rekan: props.paroki?.nama_pastor_rekan || '',
    pastor_rekan_array: props.paroki?.nama_pastor_rekan
        ? String(props.paroki.nama_pastor_rekan).split(',').map((s) => s.trim()).filter(Boolean)
        : [],
    keuskupan_id: props.paroki?.keuskupan_id || '',
    dekenat_id: props.paroki?.dekenat_id || '',
    provinsi_id: props.paroki?.provinsi_id || '',
    kabupaten_id: props.paroki?.kabupaten_id || '',
    kecamatan_id: props.paroki?.kecamatan_id || '',
    desa_id: props.paroki?.desa_id || '',
    alamat: props.paroki?.alamat || '',
    telepon: props.paroki?.telepon || '',
    whatsapp: props.paroki?.whatsapp || '',
    email: props.paroki?.email || '',
    website: props.paroki?.website || '',
    maps_url: props.paroki?.maps_url || '',
    maps_embed: props.paroki?.maps_embed || '',
    latitude: props.paroki?.latitude || '',
    longitude: props.paroki?.longitude || '',
    keterangan: props.paroki?.keterangan || '',
    logo: null,
});

// Cascading filters for Civil Administration
const filteredKabupaten = computed(() => {
    if (!form.value.provinsi_id) return props.kabupatenList;
    return props.kabupatenList.filter((k) => String(k.provinsi_id) === String(form.value.provinsi_id));
});

const filteredKecamatan = computed(() => {
    if (!form.value.kabupaten_id) return props.kecamatanList;
    return props.kecamatanList.filter((k) => String(k.kabupaten_id) === String(form.value.kabupaten_id));
});

const filteredDesa = computed(() => {
    if (!form.value.kecamatan_id) return props.desaList;
    return props.desaList.filter((d) => String(d.kecamatan_id) === String(form.value.kecamatan_id));
});

const filteredDekenat = computed(() => {
    if (!form.value.keuskupan_id) return props.dekenatList;
    return props.dekenatList.filter((d) => String(d.keuskupan_id) === String(form.value.keuskupan_id));
});

const applyAndSync = () => {
    if (selectedParokiId.value) {
        isSubmitting.value = true;
        router.post(
            `/${props.prefix}/profil-paroki/set-default`,
            { paroki_id: selectedParokiId.value },
            {
                preserveScroll: true,
                preserveState: false,
                onFinish: () => {
                    isSubmitting.value = false;
                }
            }
        );
    }
};

const openEditModal = () => {
    form.value = {
        nama_paroki: props.paroki?.nama_paroki || '',
        kode_paroki: props.paroki?.kode_paroki || '',
        pelindung_paroki: props.paroki?.pelindung_paroki || '',
        status: props.paroki?.status || 'Aktif',
        status_paroki: props.paroki?.status_paroki || 'Paroki',
        tanggal_berdiri: props.paroki?.tanggal_berdiri ? String(props.paroki.tanggal_berdiri).substring(0, 10) : '',
        nama_pastor_paroki_aktif: props.paroki?.nama_pastor_paroki_aktif || '',
        nama_pastor_rekan: props.paroki?.nama_pastor_rekan || '',
        pastor_rekan_array: props.paroki?.nama_pastor_rekan
            ? String(props.paroki.nama_pastor_rekan).split(',').map((s) => s.trim()).filter(Boolean)
            : [],
        keuskupan_id: props.paroki?.keuskupan_id || '',
        dekenat_id: props.paroki?.dekenat_id || '',
        provinsi_id: props.paroki?.provinsi_id || '',
        kabupaten_id: props.paroki?.kabupaten_id || '',
        kecamatan_id: props.paroki?.kecamatan_id || '',
        desa_id: props.paroki?.desa_id || '',
        alamat: props.paroki?.alamat || '',
        telepon: props.paroki?.telepon || '',
        whatsapp: props.paroki?.whatsapp || '',
        email: props.paroki?.email || '',
        website: props.paroki?.website || '',
        maps_url: props.paroki?.maps_url || '',
        maps_embed: props.paroki?.maps_embed || '',
        latitude: props.paroki?.latitude || '',
        longitude: props.paroki?.longitude || '',
        keterangan: props.paroki?.keterangan || '',
        logo: null,
        banner: null,
    };
    logoPreview.value = props.paroki?.logo || '';
    bannerPreview.value = props.paroki?.banner || '';
    showEditModal.value = true;
};

const handleLogoChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        form.value.logo = file;
        logoPreview.value = URL.createObjectURL(file);
    }
};

const openBannerModal = () => {
    form.value = {
        paroki_id: props.paroki?.id_paroki || props.paroki?.id || '',
        banner: null,
    };
    bannerPreview.value = props.paroki?.banner || '';
    showBannerModal.value = true;
};

const handleBannerChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        form.value.banner = file;
        bannerPreview.value = URL.createObjectURL(file);
    }
};

const pastorRekanInput = ref('');
const addPastorRekan = () => {
    if (pastorRekanInput.value.trim()) {
        if (!form.value.pastor_rekan_array.includes(pastorRekanInput.value.trim())) {
            form.value.pastor_rekan_array.push(pastorRekanInput.value.trim());
        }
        pastorRekanInput.value = '';
    }
};

const removePastorRekan = (index) => {
    form.value.pastor_rekan_array.splice(index, 1);
};

const saveParoki = () => {
    isSubmitting.value = true;
    const payload = new FormData();

    // Sync pastor rekan array back to comma string
    if (form.value.pastor_rekan_array && form.value.pastor_rekan_array.length) {
        form.value.nama_pastor_rekan = form.value.pastor_rekan_array.join(', ');
    }

    Object.keys(form.value).forEach((k) => {
        if (k !== 'pastor_rekan_array' && form.value[k] !== null && form.value[k] !== undefined && form.value[k] !== '') {
            payload.append(k, form.value[k]);
        }
    });
    payload.append('_method', 'PUT');

    const updateUrl = `/${props.prefix}/profil-paroki`;

    router.post(updateUrl, payload, {
        forceFormData: true,
        preserveScroll: true,
        preserveState: false,
        onSuccess: () => {
            showEditModal.value = false;
            showImageModal.value = false;
            showBannerModal.value = false;
            isSubmitting.value = false;
        },
        onError: () => {
            isSubmitting.value = false;
        },
    });
};
</script>

<template>
    <AppLayout title="Profil Paroki" :fullWidth="true">
        <Head title="Profil Paroki - SIPAROKI" />

        <div class="w-full space-y-5 pb-24 overflow-y-auto">
            <!-- 6 QUICK STAT CARDS -->
            <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-3">
                <div class="p-3 bg-white rounded-xl border border-slate-200 shadow-2xs flex items-center gap-3 min-w-0">
                    <div class="w-9 h-9 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-sm font-black shrink-0">
                        <i class="fa-solid fa-map-location-dot"></i>
                    </div>
                    <div class="min-w-0">
                        <span class="text-[10px] uppercase tracking-wider text-slate-400 font-bold block truncate">Wilayah</span>
                        <span class="text-sm font-black text-slate-900">{{ totalWilayah }}</span>
                    </div>
                </div>

                <div class="p-3 bg-white rounded-xl border border-slate-200 shadow-2xs flex items-center gap-3 min-w-0">
                    <div class="w-9 h-9 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-sm font-black shrink-0">
                        <i class="fa-solid fa-tree-city"></i>
                    </div>
                    <div class="min-w-0">
                        <span class="text-[10px] uppercase tracking-wider text-slate-400 font-bold block truncate">Lingkungan</span>
                        <span class="text-sm font-black text-slate-900">{{ totalLingkungan }}</span>
                    </div>
                </div>

                <div class="p-3 bg-white rounded-xl border border-slate-200 shadow-2xs flex items-center gap-3 min-w-0">
                    <div class="w-9 h-9 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm font-black shrink-0">
                        <i class="fa-solid fa-church"></i>
                    </div>
                    <div class="min-w-0">
                        <span class="text-[10px] uppercase tracking-wider text-slate-400 font-bold block truncate">Kapela / Stasi</span>
                        <span class="text-sm font-black text-slate-900">{{ totalKapela }}</span>
                    </div>
                </div>

                <div class="p-3 bg-white rounded-xl border border-slate-200 shadow-2xs flex items-center gap-3 min-w-0">
                    <div class="w-9 h-9 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center text-sm font-black shrink-0">
                        <i class="fa-solid fa-users-rectangle"></i>
                    </div>
                    <div class="min-w-0">
                        <span class="text-[10px] uppercase tracking-wider text-slate-400 font-bold block truncate">KUB</span>
                        <span class="text-sm font-black text-slate-900">{{ totalKub }}</span>
                    </div>
                </div>

                <div class="p-3 bg-white rounded-xl border border-slate-200 shadow-2xs flex items-center gap-3 min-w-0">
                    <div class="w-9 h-9 rounded-lg bg-cyan-50 text-cyan-600 flex items-center justify-center text-sm font-black shrink-0">
                        <i class="fa-solid fa-users"></i>
                    </div>
                    <div class="min-w-0">
                        <span class="text-[10px] uppercase tracking-wider text-slate-400 font-bold block truncate">Total Umat</span>
                        <span class="text-sm font-black text-slate-900">{{ totalUmat.toLocaleString('id-ID') }}</span>
                    </div>
                </div>

                <div class="p-3 bg-white rounded-xl border border-slate-200 shadow-2xs flex items-center gap-3 min-w-0">
                    <div class="w-9 h-9 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center text-sm font-black shrink-0">
                        <i class="fa-solid fa-people-roof"></i>
                    </div>
                    <div class="min-w-0">
                        <span class="text-[10px] uppercase tracking-wider text-slate-400 font-bold block truncate">Kepala Keluarga</span>
                        <span class="text-sm font-black text-slate-900">{{ totalKk.toLocaleString('id-ID') }}</span>
                    </div>
                </div>
            </div>

            <!-- 2-COLUMN MAIN LAYOUT MATCHING KATEDRAL -->
            <div class="grid grid-cols-1 xl:grid-cols-12 gap-5 items-start">
                
                <!-- LEFT COLUMN: DETAIL PROFIL PAROKI (8/12 = ~67%) -->
                <div class="xl:col-span-8 space-y-4 min-w-0">
                    <!-- Top Action Card -->
                    <div class="rounded-xl bg-white border border-slate-200/90 p-4 sm:p-5 shadow-2xs space-y-4">
                        <div class="flex flex-col gap-3 pb-4 border-b border-slate-100">
                            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 min-w-0">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <span class="w-9 h-9 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                                        <i class="fa-solid fa-house-chimney-window text-sm"></i>
                                    </span>
                                    <div class="min-w-0">
                                        <h1 class="text-base font-black text-slate-900 tracking-tight">Detail Profil Paroki</h1>
                                        <p class="text-[11px] text-slate-500 truncate">Data utama, wilayah, kontak, pastor, dan lokasi</p>
                                    </div>
                                </div>
                                <span class="w-fit max-w-full px-2.5 py-1 rounded-lg bg-blue-50 border border-blue-200 text-blue-700 text-[11px] font-bold truncate">
                                    {{ paroki?.nama_paroki }}
                                </span>
                            </div>

                            <!-- Buttons -->
                            <div class="grid grid-cols-1 sm:grid-cols-4 gap-2">
                                <Link
                                    href="/setup-paroki"
                                    class="inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-lg bg-purple-600 hover:bg-purple-700 text-white text-xs font-bold transition shadow-2xs cursor-pointer"
                                    title="Pilih atau inisialisasi ulang Keuskupan & Paroki Default"
                                >
                                    <i class="fa-solid fa-sliders text-xs"></i>
                                    <span class="truncate">Setup Default</span>
                                </Link>
                                <button
                                    type="button"
                                    @click="openBannerModal"
                                    class="inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition shadow-2xs cursor-pointer"
                                    title="Upload Banner Latar Belakang Header Website Publik"
                                >
                                    <i class="fa-solid fa-panorama text-xs"></i>
                                    <span class="truncate">Upload Banner</span>
                                </button>
                                <button
                                    type="button"
                                    @click="showImageModal = true"
                                    class="inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition shadow-2xs cursor-pointer"
                                >
                                    <i class="fa-regular fa-image text-xs"></i>
                                    <span class="truncate">Gambar & Sejarah</span>
                                </button>
                                <button
                                    type="button"
                                    @click="openEditModal"
                                    class="inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-lg bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold transition shadow-2xs cursor-pointer"
                                >
                                    <i class="fa-solid fa-pen-to-square text-xs"></i>
                                    <span class="truncate">Edit Data Profil</span>
                                </button>
                            </div>
                        </div>

                        <!-- Read-Only Sync Notice -->
                        <div class="p-3 rounded-lg bg-cyan-50/80 border border-cyan-200/90 flex items-start gap-2.5 text-xs text-cyan-950">
                            <i class="fa-solid fa-rotate text-cyan-600 text-xs mt-0.5 shrink-0"></i>
                            <div class="leading-relaxed text-[11.5px]">
                                <span class="font-bold text-cyan-900">Sinkronisasi Otomatis Terintegrasi:</span>
                                Paroki yang dipilih di halaman ini otomatis menjadi <b>Paroki Default</b> aplikasi. Seluruh perubahan profil, logo, kontak, maupun penggantian paroki utama akan <b>langsung otomatis mengubah tampilan Frontend dan Backend</b> secara seketika.
                            </div>
                        </div>

                        <!-- Paroki Header Title & Badges -->
                        <div class="flex flex-col sm:flex-row sm:items-center gap-4 pt-1 min-w-0">
                            <div class="w-16 h-16 rounded-xl border border-slate-200 bg-slate-50 overflow-hidden flex items-center justify-center shrink-0 shadow-2xs">
                                <img
                                    v-if="paroki?.logo"
                                    :src="paroki.logo"
                                    :alt="paroki.nama_paroki"
                                    class="w-full h-full object-cover"
                                />
                                <i v-else class="fa-solid fa-church text-2xl text-amber-500"></i>
                            </div>
                            <div class="min-w-0">
                                <h2 class="text-lg font-black text-slate-900 leading-snug">{{ paroki?.nama_paroki }}</h2>
                                <div class="flex flex-wrap items-center gap-2 mt-1">
                                    <span class="px-2 py-0.5 rounded-md bg-emerald-500 text-white text-[10px] font-bold">{{ paroki?.status || 'Aktif' }}</span>
                                    <span class="px-2 py-0.5 rounded-md bg-cyan-600 text-white text-[10px] font-bold">{{ paroki?.status_paroki || 'Paroki' }}</span>
                                    <span class="px-2 py-0.5 rounded-md bg-slate-800 text-white text-[10px] font-mono font-bold">Kode: {{ paroki?.kode_paroki || '012.009' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- STRUCTURED SECTIONS TABLE -->
                    <div class="rounded-xl bg-white border border-slate-200/90 shadow-2xs overflow-hidden divide-y divide-slate-100">
                        
                        <!-- SECTION 1: IDENTITAS PAROKI -->
                        <div>
                            <div class="bg-slate-50/90 px-4 py-2.5 border-b border-slate-200 flex items-center gap-2 text-xs font-black text-slate-800 uppercase">
                                <i class="fa-solid fa-bars-staggered text-amber-600"></i>
                                <span>IDENTITAS PAROKI</span>
                            </div>
                            <div class="divide-y divide-slate-100 text-xs">
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-1 sm:gap-3 px-4 py-2.5">
                                    <span class="text-slate-500 font-semibold">Keuskupan Induk</span>
                                    <span class="sm:col-span-2 font-bold text-slate-900">{{ paroki?.keuskupan ? paroki.keuskupan.nama_keuskupan : 'Keuskupan Agung Kupang' }}</span>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-1 sm:gap-3 px-4 py-2.5">
                                    <span class="text-slate-500 font-semibold">Dekenat / Kevikepan</span>
                                    <span class="sm:col-span-2 font-bold text-slate-900">{{ paroki?.dekenat ? paroki.dekenat.nama_kevikepan : 'Kevikepan/Dekenat Kota Kupang' }}</span>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-1 sm:gap-3 px-4 py-2.5">
                                    <span class="text-slate-500 font-semibold">Kode Paroki</span>
                                    <span class="sm:col-span-2 font-mono font-bold text-rose-600">{{ paroki?.kode_paroki || '012.009' }}</span>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-1 sm:gap-3 px-4 py-2.5">
                                    <span class="text-slate-500 font-semibold">Nama Paroki</span>
                                    <span class="sm:col-span-2 font-bold text-slate-900">{{ paroki?.nama_paroki }}</span>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-1 sm:gap-3 px-4 py-2.5">
                                    <span class="text-slate-500 font-semibold">Nama Pelindung / Santo</span>
                                    <span class="sm:col-span-2 font-bold text-slate-700">{{ paroki?.pelindung_paroki || '-' }}</span>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-1 sm:gap-3 px-4 py-2.5">
                                    <span class="text-slate-500 font-semibold">Status</span>
                                    <span class="sm:col-span-2">
                                        <span class="px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-[10px] font-bold">
                                             {{ paroki?.status || 'Aktif' }}
                                        </span>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- SECTION 2: PELAYAN PASTORAL -->
                        <div>
                            <div class="bg-slate-50/90 px-4 py-2.5 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 text-xs font-black text-slate-800 uppercase">
                                <div class="flex items-center gap-2 min-w-0">
                                    <i class="fa-solid fa-user-tie text-amber-600"></i>
                                    <span class="truncate">PELAYAN PASTORAL</span>
                                </div>
                                <Link
                                    href="/admin/master-referensi/pastor"
                                    class="text-[11px] font-bold text-amber-700 hover:text-amber-900 flex items-center gap-1.5 normal-case shrink-0"
                                >
                                    <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                                    <span>Buka Master Pastor</span>
                                </Link>
                            </div>
                            
                            <!-- If pastorsBertugas exists in database -->
                            <div v-if="pastorsBertugas && pastorsBertugas.length" class="divide-y divide-slate-100 text-xs">
                                <div
                                    v-for="p in pastorsBertugas"
                                    :key="p.id"
                                    class="p-4 flex flex-col md:flex-row md:items-center justify-between gap-3 hover:bg-slate-50/70 transition"
                                >
                                    <div class="flex items-center gap-3.5 min-w-0">
                                        <div class="w-11 h-11 rounded-xl bg-amber-50 border border-amber-200 overflow-hidden flex items-center justify-center shrink-0">
                                            <img
                                                v-if="p.foto"
                                                :src="p.foto.startsWith('http') || p.foto.startsWith('/') ? p.foto : '/' + p.foto"
                                                :alt="p.nama_formatted || p.nama_pastor"
                                                class="w-full h-full object-cover"
                                            />
                                            <i v-else class="fa-solid fa-user-tie text-amber-600 text-lg"></i>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <h4 class="font-black text-slate-900 text-[13px]">{{ p.nama_formatted || p.nama_pastor }}</h4>
                                                <span
                                                    :class="[
                                                        'px-2 py-0.5 rounded-md text-[10px] font-bold',
                                                        (p.jabatan && p.jabatan.includes('Paroki') && !p.jabatan.includes('Rekan'))
                                                            ? 'bg-amber-100 text-amber-800 border border-amber-200'
                                                            : 'bg-blue-100 text-blue-800 border border-blue-200'
                                                    ]"
                                                >
                                                    {{ p.jabatan || 'Pastor Rekan' }}
                                                </span>
                                                <span class="px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 border border-emerald-200 text-[10px] font-bold">
                                                    {{ p.status === '1' || p.status === 1 || p.status === 'Aktif' ? 'Aktif' : (p.status === '0' || p.status === 0 || p.status === 'Nonaktif' ? 'Nonaktif' : (p.status || 'Aktif')) }}
                                                </span>
                                            </div>
                                            <p class="text-[11px] text-slate-500 mt-0.5 leading-relaxed">
                                                <span>Bertugas di: <b>{{ p.paroki_tugas || paroki?.nama_paroki }}</b></span>
                                                <span v-if="p.ordo" class="ml-2">• Tarekat/Ordo: <b>{{ p.ordo }}</b></span>
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-center gap-2 shrink-0 md:self-center">
                                        <Link
                                            :href="`/admin/master-referensi/pastor/edit/${p.id}`"
                                            class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-amber-500 hover:text-white text-slate-700 text-xs font-bold transition flex items-center gap-1 cursor-pointer"
                                            title="Edit data pastor ini di master pastor"
                                        >
                                            <i class="fa-solid fa-pen-to-square text-[10px]"></i>
                                            <span>Edit Data</span>
                                        </Link>
                                    </div>
                                </div>
                            </div>

                            <!-- Fallback standard fields if no records -->
                            <div v-else class="divide-y divide-slate-100 text-xs">
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-1 sm:gap-3 px-4 py-2.5 items-center">
                                    <span class="text-slate-500 font-semibold">Pastor Paroki</span>
                                    <div class="sm:col-span-2 flex items-center gap-2">
                                        <span class="font-bold text-slate-900">{{ paroki?.nama_pastor_paroki_aktif || 'Belum diatur' }}</span>
                                        <span class="px-2 py-0.5 rounded-md bg-amber-50 text-amber-700 border border-amber-200 text-[10px] font-bold">Pastor Paroki</span>
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-1 sm:gap-3 px-4 py-2.5 items-center">
                                    <span class="text-slate-500 font-semibold">Pastor Rekan</span>
                                    <div class="sm:col-span-2 flex items-center gap-2">
                                        <span class="font-bold text-slate-700">{{ paroki?.nama_pastor_rekan || 'RD. Patrisius Tampani' }}</span>
                                        <span class="px-2 py-0.5 rounded-md bg-blue-50 text-blue-700 border border-blue-200 text-[10px] font-bold">Pastor Rekan</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- SECTION 3: LOKASI & WILAYAH ADMINISTRATIF -->
                        <div>
                            <div class="bg-slate-50/90 px-4 py-2.5 border-b border-slate-200 flex items-center gap-2 text-xs font-black text-slate-800 uppercase">
                                <i class="fa-solid fa-location-dot text-amber-600"></i>
                                <span>LOKASI & WILAYAH ADMINISTRATIF</span>
                            </div>
                            <div class="divide-y divide-slate-100 text-xs">
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-1 sm:gap-3 px-4 py-2.5">
                                    <span class="text-slate-500 font-semibold">Alamat Lengkap</span>
                                    <span class="sm:col-span-2 font-medium text-slate-800">{{ paroki?.alamat || '-' }}</span>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-1 sm:gap-3 px-4 py-2.5">
                                    <span class="text-slate-500 font-semibold">Kelurahan / Desa</span>
                                    <span class="sm:col-span-2 font-bold text-slate-800">{{ paroki?.desa ? paroki.desa.nama_desa : '-' }}</span>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-1 sm:gap-3 px-4 py-2.5">
                                    <span class="text-slate-500 font-semibold">Kecamatan</span>
                                    <span class="sm:col-span-2 font-bold text-slate-800">{{ paroki?.kecamatan ? paroki.kecamatan.nama_kecamatan : '-' }}</span>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-1 sm:gap-3 px-4 py-2.5">
                                    <span class="text-slate-500 font-semibold">Kabupaten / Kota</span>
                                    <span class="sm:col-span-2 font-bold text-slate-800">{{ paroki?.kabupaten ? paroki.kabupaten.nama_kabupaten : '-' }}</span>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-1 sm:gap-3 px-4 py-2.5">
                                    <span class="text-slate-500 font-semibold">Provinsi</span>
                                    <span class="sm:col-span-2 font-bold text-slate-800">{{ paroki?.provinsi ? paroki.provinsi.nama_provinsi : '-' }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- SECTION 4: KONTAK & INFORMASI -->
                        <div>
                            <div class="bg-slate-50/90 px-4 py-2.5 border-b border-slate-200 flex items-center gap-2 text-xs font-black text-slate-800 uppercase">
                                <i class="fa-solid fa-phone text-amber-600"></i>
                                <span>KONTAK & INFORMASI</span>
                            </div>
                            <div class="divide-y divide-slate-100 text-xs">
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-1 sm:gap-3 px-4 py-2.5">
                                    <span class="text-slate-500 font-semibold">No. Telepon / HP</span>
                                    <span class="sm:col-span-2 font-bold text-slate-800">{{ paroki?.telepon || paroki?.whatsapp || '-' }}</span>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-1 sm:gap-3 px-4 py-2.5">
                                    <span class="text-slate-500 font-semibold">Email Resmi</span>
                                    <span class="sm:col-span-2 font-bold text-slate-800">{{ paroki?.email || '-' }}</span>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-1 sm:gap-3 px-4 py-2.5">
                                    <span class="text-slate-500 font-semibold">Website</span>
                                    <span class="sm:col-span-2 font-bold text-blue-600">{{ paroki?.website || '-' }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- SECTION 5: GOOGLE MAPS & PETA LOKASI -->
                        <div v-if="paroki?.maps_embed || paroki?.maps_url">
                            <div class="bg-slate-50/90 px-4 py-2.5 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 text-xs font-black text-slate-800 uppercase">
                                <div class="flex items-center gap-2">
                                    <i class="fa-solid fa-map-location-dot text-amber-600"></i>
                                    <span>PETA & LOKASI GOOGLE MAPS</span>
                                </div>
                                <a
                                    v-if="paroki?.maps_url"
                                    :href="paroki.maps_url"
                                    target="_blank"
                                    class="text-[11px] font-bold text-blue-600 hover:text-blue-800 normal-case flex items-center gap-1 shrink-0"
                                >
                                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                    <span>Buka di Google Maps</span>
                                </a>
                            </div>
                            <div class="p-4 space-y-3">
                                <div
                                    v-if="paroki?.maps_embed"
                                    class="w-full h-64 rounded-xl overflow-hidden border border-slate-200 shadow-2xs"
                                    v-html="paroki.maps_embed"
                                ></div>
                                <div v-if="paroki?.latitude && paroki?.longitude" class="text-xs text-slate-500 flex items-center gap-4">
                                    <span><b>Latitude:</b> {{ paroki.latitude }}</span>
                                    <span><b>Longitude:</b> {{ paroki.longitude }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- RIGHT COLUMN: PAROKI UTAMA (DEFAULT) WIDGETS (4/12 = ~33%) -->
                <div class="xl:col-span-4 space-y-4 xl:sticky xl:top-4 min-w-0">
                    
                    <!-- WIDGET 1: PAROKI UTAMA (DEFAULT) SELECTOR -->
                    <div class="rounded-xl bg-white border border-slate-200/90 p-4 shadow-2xs space-y-4">
                        <div class="flex items-center gap-2 pb-2 border-b border-slate-100">
                            <i class="fa-solid fa-gear text-amber-500 text-sm"></i>
                            <h3 class="text-xs font-black text-slate-900 uppercase tracking-wider">Paroki Utama (Default)</h3>
                        </div>

                        <div class="space-y-3">
                            <!-- Searchable Custom Dropdown -->
                            <div ref="dropdownRef" class="relative">
                                <label class="text-[11px] font-bold text-slate-600 block mb-1">Pilih Paroki Utama</label>
                                <button
                                    type="button"
                                    @click="isDropdownOpen = !isDropdownOpen"
                                    class="w-full px-3.5 py-2.5 rounded-lg bg-slate-50 hover:bg-white border border-slate-200 text-xs font-bold text-slate-800 focus:outline-none focus:border-amber-500 flex items-center justify-between gap-2 cursor-pointer shadow-2xs text-left transition"
                                >
                                    <span class="truncate">{{ selectedParokiName }}</span>
                                    <i :class="['fa-solid fa-chevron-down text-[10px] text-slate-400 transition-transform duration-200 shrink-0', isDropdownOpen ? 'rotate-180 text-amber-600' : '']"></i>
                                </button>

                                <!-- Searchable Popover Menu -->
                                <div
                                    v-if="isDropdownOpen"
                                    class="absolute left-0 right-0 top-full mt-1.5 z-40 bg-white rounded-xl border border-slate-200 shadow-xl overflow-hidden space-y-2 p-2"
                                >
                                    <div class="relative">
                                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                                        <input
                                            v-model="parokiSearchQuery"
                                            type="text"
                                            placeholder="Ketik nama paroki / keuskupan..."
                                            class="w-full pl-8 pr-3 py-1.5 rounded-lg bg-slate-50 border border-slate-200 text-xs focus:outline-none focus:border-amber-500"
                                            @click.stop
                                        />
                                    </div>

                                    <div class="max-h-60 overflow-y-auto custom-scrollbar divide-y divide-slate-100">
                                        <button
                                            v-for="p in filteredParokis"
                                            :key="p.id_paroki"
                                            type="button"
                                            @click="selectParokiItem(p)"
                                            :class="[
                                                'w-full text-left px-3 py-2 rounded-lg text-xs transition flex flex-col cursor-pointer',
                                                String(p.id_paroki) === String(selectedParokiId)
                                                    ? 'bg-amber-50 text-amber-900 font-bold'
                                                    : 'hover:bg-slate-50 text-slate-700'
                                            ]"
                                        >
                                            <span class="font-bold">{{ p.nama_paroki }}</span>
                                            <span class="text-[10px] text-slate-400">
                                                {{ p.keuskupan ? p.keuskupan.nama_keuskupan : 'Keuskupan Agung Kupang' }} • {{ p.kode_paroki || '-' }}
                                            </span>
                                        </button>
                                        <div v-if="!filteredParokis.length" class="py-4 text-center text-xs text-slate-400">
                                            Tidak ada paroki yang sesuai.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <button
                                type="button"
                                @click="applyAndSync"
                                class="w-full flex items-center justify-center gap-2 py-2.5 rounded-lg bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold shadow-xs transition cursor-pointer"
                            >
                                <i class="fa-solid fa-rotate text-xs"></i>
                                <span>Terapkan & Sinkronkan</span>
                            </button>
                        </div>
                    </div>

                    <!-- WIDGET 2: AUTO-SINKRONISASI AKTIF (GREEN BOX) -->
                    <div class="rounded-xl bg-emerald-50/70 border border-emerald-200/90 p-4 space-y-2 text-xs text-emerald-950 shadow-2xs">
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-circle-check text-emerald-600 text-sm"></i>
                            <h4 class="font-bold text-emerald-900 text-xs">Auto-Sinkronisasi Aktif:</h4>
                        </div>
                        <ul class="space-y-1.5 text-[11px] text-emerald-800 pl-1 leading-relaxed">
                            <li class="flex items-start gap-1.5">
                                <span class="text-emerald-600 font-bold">•</span>
                                <span>Menyetel paroki default akan langsung menampilkan seluruh detail profil paroki terpilih.</span>
                            </li>
                            <li class="flex items-start gap-1.5">
                                <span class="text-emerald-600 font-bold">•</span>
                                <span>Setiap pengeditan data di menu <b>Manajemen Paroki</b> otomatis memperbarui halaman profil ini.</span>
                            </li>
                        </ul>
                    </div>

                    <!-- WIDGET 3: PAROKI BADGE SUMMARY CARD (BLUE/GRAY) -->
                    <div class="rounded-xl bg-slate-50 border border-slate-200/90 p-4 space-y-3 shadow-2xs text-xs">
                        <h4 class="font-black text-slate-900 text-sm leading-tight">{{ paroki?.nama_paroki }}</h4>
                        <div class="space-y-1 text-[11.5px] text-slate-600">
                            <p class="flex items-center gap-2">
                                <i class="fa-solid fa-church text-slate-400 text-xs w-4"></i>
                                <span>{{ paroki?.keuskupan ? paroki.keuskupan.nama_keuskupan : 'Keuskupan Agung Kupang' }}</span>
                            </p>
                            <p class="flex items-center gap-2">
                                <i class="fa-solid fa-layer-group text-slate-400 text-xs w-4"></i>
                                <span>{{ paroki?.dekenat ? paroki.dekenat.nama_kevikepan : 'Kevikepan/Dekenat Kota Kupang' }}</span>
                            </p>
                            <p class="flex items-center gap-2">
                                <i class="fa-solid fa-tag text-slate-400 text-xs w-4"></i>
                                <span>Kode: <b class="font-mono text-rose-600">{{ paroki?.kode_paroki || '012.009' }}</b></span>
                            </p>
                        </div>
                        <div class="pt-1">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-bold bg-emerald-500 text-white">
                                <span class="w-1.5 h-1.5 rounded-full bg-white"></span>
                                Aktif
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL 1: FORM EDIT DATA PAROKI LENGKAP -->
        <div
            v-if="showEditModal"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/40 backdrop-blur-xs overflow-y-auto"
        >
            <div class="bg-white rounded-3xl max-w-4xl w-full shadow-2xl border border-slate-200 overflow-hidden my-8">
                <!-- Header Modal -->
                <div class="bg-gradient-to-r from-amber-500 to-amber-600 px-6 py-4 flex items-center justify-between text-white">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center text-base font-black">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-black">Form Edit Data Paroki: {{ form.nama_paroki || paroki?.nama_paroki }}</h3>
                            <p class="text-xs text-amber-100">Silakan lengkapi formulir data master berikut ini</p>
                        </div>
                    </div>
                    <button @click="showEditModal = false" class="w-8 h-8 rounded-lg bg-white/10 hover:bg-white/20 flex items-center justify-center transition cursor-pointer">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div class="p-6 space-y-6 max-h-[70vh] overflow-y-auto custom-scrollbar">
                    
                    <!-- 1. INFORMASI INDUK & IDENTITAS PAROKI -->
                    <div class="space-y-3">
                        <h4 class="text-xs font-black text-slate-800 uppercase tracking-wider flex items-center gap-2 border-b border-slate-100 pb-2">
                            <i class="fa-solid fa-church text-amber-600"></i>
                            <span>Informasi Induk & Identitas Paroki</span>
                        </h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="space-y-1">
                                <label class="text-[11px] font-bold text-slate-600">Keuskupan Induk *</label>
                                <select v-model="form.keuskupan_id" class="w-full px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs focus:outline-none focus:border-amber-500 cursor-pointer">
                                    <option value="">— Pilih Keuskupan —</option>
                                    <option v-for="k in keuskupanList" :key="k.id_keuskupan" :value="k.id_keuskupan">{{ k.nama_keuskupan }}</option>
                                </select>
                            </div>
                            <div class="space-y-1">
                                <label class="text-[11px] font-bold text-slate-600">Dekenat / Kevikepan *</label>
                                <select v-model="form.dekenat_id" class="w-full px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs focus:outline-none focus:border-amber-500 cursor-pointer">
                                    <option value="">— Pilih Dekenat —</option>
                                    <option v-for="d in filteredDekenat" :key="d.id" :value="d.id">{{ d.nama_kevikepan }}</option>
                                </select>
                            </div>
                            <div class="space-y-1">
                                <label class="text-[11px] font-bold text-slate-600">Kode Paroki <span class="text-slate-400 font-normal">(Readonly Otomatis)</span></label>
                                <input v-model="form.kode_paroki" type="text" readonly class="w-full px-3 py-2 rounded-xl bg-slate-100 border border-slate-200 text-xs font-mono font-bold text-slate-600 cursor-not-allowed" />
                            </div>
                            <div class="space-y-1">
                                <label class="text-[11px] font-bold text-slate-600">Nama Paroki *</label>
                                <input v-model="form.nama_paroki" type="text" class="w-full px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs font-bold text-slate-800 focus:outline-none focus:border-amber-500" />
                            </div>
                            <div class="space-y-1">
                                <label class="text-[11px] font-bold text-slate-600">Nama Pelindung / Santo</label>
                                <input v-model="form.pelindung_paroki" type="text" placeholder="Contoh: St. Vinsensius a Paulo" class="w-full px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs focus:outline-none focus:border-amber-500" />
                            </div>
                            <div class="space-y-1">
                                <label class="text-[11px] font-bold text-slate-600">Status</label>
                                <select v-model="form.status" class="w-full px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs focus:outline-none focus:border-amber-500 cursor-pointer">
                                    <option value="Aktif">Aktif</option>
                                    <option value="Nonaktif">Nonaktif</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- 2. PELAYAN PASTORAL -->
                    <div class="space-y-3">
                        <h4 class="text-xs font-black text-slate-800 uppercase tracking-wider flex items-center gap-2 border-b border-slate-100 pb-2">
                            <i class="fa-solid fa-user-tie text-amber-600"></i>
                            <span>Pelayan Pastoral</span>
                        </h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Pastor Paroki -->
                            <div class="space-y-1">
                                <label class="text-[11px] font-bold text-slate-600">Pastor Paroki</label>
                                <input
                                    v-model="form.nama_pastor_paroki_aktif"
                                    type="text"
                                    placeholder="Contoh: RD. Herman Hilers Penga"
                                    class="w-full px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs font-bold text-slate-800 focus:outline-none focus:border-amber-500"
                                />
                                <p class="text-[10px] text-slate-400">Pilih pastor yang bertugas sebagai Pastor Paroki</p>
                            </div>

                            <!-- Pastor Rekan Multi Select -->
                            <div class="space-y-1">
                                <label class="text-[11px] font-bold text-slate-600">Pastor Rekan (Bisa lebih dari 1)</label>
                                <div class="flex items-center gap-2">
                                    <input
                                        v-model="pastorRekanInput"
                                        @keydown.enter.prevent="addPastorRekan"
                                        type="text"
                                        placeholder="Ketik nama pastor rekan lalu tekan Enter / Tambah..."
                                        class="w-full px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs focus:outline-none focus:border-amber-500"
                                    />
                                    <button
                                        type="button"
                                        @click="addPastorRekan"
                                        class="px-3 py-2 rounded-xl bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold shrink-0 transition"
                                    >
                                        + Tambah
                                    </button>
                                </div>
                                <div v-if="form.pastor_rekan_array.length" class="flex flex-wrap gap-1.5 pt-1.5">
                                    <span
                                        v-for="(pr, idx) in form.pastor_rekan_array"
                                        :key="idx"
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-amber-50 border border-amber-200 text-amber-900 text-xs font-bold"
                                    >
                                        <span>{{ pr }}</span>
                                        <button type="button" @click="removePastorRekan(idx)" class="text-amber-700 hover:text-rose-600 font-black">
                                            ×
                                        </button>
                                    </span>
                                </div>
                                <p class="text-[10px] text-slate-400">Pilih satu atau beberapa pastor yang bertugas sebagai Pastor Rekan</p>
                            </div>
                        </div>
                    </div>

                    <!-- 3. LOGO / FOTO PAROKI -->
                    <div class="space-y-3">
                        <h4 class="text-xs font-black text-slate-800 uppercase tracking-wider flex items-center gap-2 border-b border-slate-100 pb-2">
                            <i class="fa-regular fa-image text-amber-600"></i>
                            <span>Logo / Foto Paroki</span>
                        </h4>
                        <div class="p-4 rounded-2xl bg-amber-50/50 border border-amber-200/80 flex items-center gap-5">
                            <div class="w-20 h-20 rounded-2xl border-2 border-dashed border-amber-300 bg-white overflow-hidden flex items-center justify-center shrink-0 shadow-xs">
                                <img v-if="logoPreview" :src="logoPreview" class="w-full h-full object-cover" />
                                <i v-else class="fa-solid fa-church text-amber-500 text-2xl"></i>
                            </div>
                            <div class="space-y-1.5">
                                <label class="cursor-pointer inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold transition shadow-xs">
                                    <i class="fa-solid fa-upload"></i>
                                    <span>Ganti Foto / Logo Paroki</span>
                                    <input type="file" accept="image/*" class="hidden" @change="handleLogoChange" />
                                </label>
                                <p class="text-[11px] text-slate-600">Format: JPG, PNG, GIF, SVG, WEBP. Maksimal 2MB (Kosongkan jika tidak ada).</p>
                            </div>
                        </div>
                    </div>

                    <!-- 4. WILAYAH ADMINISTRATIF & ALAMAT -->
                    <div class="space-y-3">
                        <h4 class="text-xs font-black text-slate-800 uppercase tracking-wider flex items-center gap-2 border-b border-slate-100 pb-2">
                            <i class="fa-solid fa-location-dot text-amber-600"></i>
                            <span>Wilayah Administratif & Alamat Lengkap</span>
                        </h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="space-y-1 sm:col-span-2">
                                <label class="text-[11px] font-bold text-slate-600">Alamat Lengkap</label>
                                <textarea v-model="form.alamat" rows="2" placeholder="Alamat lengkap kantor sekretariat paroki..." class="w-full px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs focus:outline-none focus:border-amber-500"></textarea>
                            </div>
                            <div class="space-y-1">
                                <label class="text-[11px] font-bold text-slate-600">Provinsi</label>
                                <select v-model="form.provinsi_id" class="w-full px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs focus:outline-none focus:border-amber-500 cursor-pointer">
                                    <option value="">— Pilih Provinsi —</option>
                                    <option v-for="p in provinsiList" :key="p.id_provinsi" :value="p.id_provinsi">{{ p.nama_provinsi }}</option>
                                </select>
                            </div>
                            <div class="space-y-1">
                                <label class="text-[11px] font-bold text-slate-600">Kabupaten / Kota</label>
                                <select v-model="form.kabupaten_id" class="w-full px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs focus:outline-none focus:border-amber-500 cursor-pointer">
                                    <option value="">— Pilih Kabupaten —</option>
                                    <option v-for="k in filteredKabupaten" :key="k.id_kabupaten" :value="k.id_kabupaten">{{ k.nama_kabupaten }}</option>
                                </select>
                            </div>
                            <div class="space-y-1">
                                <label class="text-[11px] font-bold text-slate-600">Kecamatan</label>
                                <select v-model="form.kecamatan_id" class="w-full px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs focus:outline-none focus:border-amber-500 cursor-pointer">
                                    <option value="">— Pilih Kecamatan —</option>
                                    <option v-for="kc in filteredKecamatan" :key="kc.id_kecamatan" :value="kc.id_kecamatan">{{ kc.nama_kecamatan }}</option>
                                </select>
                            </div>
                            <div class="space-y-1">
                                <label class="text-[11px] font-bold text-slate-600">Kelurahan / Desa</label>
                                <select v-model="form.desa_id" class="w-full px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs focus:outline-none focus:border-amber-500 cursor-pointer">
                                    <option value="">— Pilih Desa —</option>
                                    <option v-for="d in filteredDesa" :key="d.id_desa" :value="d.id_desa">{{ d.nama_desa }}</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- 5. KONTAK & INFORMASI TAMBAHAN -->
                    <div class="space-y-3">
                        <h4 class="text-xs font-black text-slate-800 uppercase tracking-wider flex items-center gap-2 border-b border-slate-100 pb-2">
                            <i class="fa-solid fa-phone text-amber-600"></i>
                            <span>Kontak & Informasi Tambahan</span>
                        </h4>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div class="space-y-1">
                                <label class="text-[11px] font-bold text-slate-600">Telepon / HP</label>
                                <input v-model="form.telepon" type="text" placeholder="Nomor telepon paroki" class="w-full px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs focus:outline-none focus:border-amber-500" />
                            </div>
                            <div class="space-y-1">
                                <label class="text-[11px] font-bold text-slate-600">Email</label>
                                <input v-model="form.email" type="email" placeholder="Email resmi paroki" class="w-full px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs focus:outline-none focus:border-amber-500" />
                            </div>
                            <div class="space-y-1">
                                <label class="text-[11px] font-bold text-slate-600">Website</label>
                                <input v-model="form.website" type="text" placeholder="Contoh: https://parokibenlutu.id" class="w-full px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs focus:outline-none focus:border-amber-500" />
                            </div>
                            <div class="space-y-1 sm:col-span-3">
                                <label class="text-[11px] font-bold text-slate-600">Keterangan</label>
                                <textarea v-model="form.keterangan" rows="2" placeholder="Keterangan tambahan..." class="w-full px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs focus:outline-none focus:border-amber-500"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- 6. GOOGLE MAPS & PETA LOKASI -->
                    <div class="space-y-3">
                        <h4 class="text-xs font-black text-slate-800 uppercase tracking-wider flex items-center gap-2 border-b border-slate-100 pb-2">
                            <i class="fa-solid fa-map-location-dot text-amber-600"></i>
                            <span>Google Maps & Peta Lokasi</span>
                        </h4>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div class="space-y-1 sm:col-span-3">
                                <label class="text-[11px] font-bold text-slate-600">Link Google Maps (URL)</label>
                                <input v-model="form.maps_url" type="text" placeholder="https://www.google.com/maps/dir/?api=1&destination=..." class="w-full px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs focus:outline-none focus:border-amber-500" />
                            </div>
                            <div class="space-y-1">
                                <label class="text-[11px] font-bold text-slate-600">Latitude</label>
                                <input v-model="form.latitude" type="text" placeholder="-9.895699" class="w-full px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs focus:outline-none focus:border-amber-500" />
                            </div>
                            <div class="space-y-1">
                                <label class="text-[11px] font-bold text-slate-600">Longitude</label>
                                <input v-model="form.longitude" type="text" placeholder="124.223603" class="w-full px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs focus:outline-none focus:border-amber-500" />
                            </div>
                            <div class="space-y-1 sm:col-span-3">
                                <label class="text-[11px] font-bold text-slate-600">Embed Google Maps (Iframe)</label>
                                <textarea v-model="form.maps_embed" rows="3" placeholder='<iframe src="https://www.google.com/maps/embed?..." width="600" height="450"></iframe>' class="w-full px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs font-mono focus:outline-none focus:border-amber-500"></textarea>
                                <p class="text-[10px] text-slate-400">Pastekan tag &lt;iframe src="..."&gt;&lt;/iframe&gt; dari Google Maps (Bagikan / Share -&gt; Sematkan peta / Embed a map).</p>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Footer Modal -->
                <div class="px-6 py-4 border-t border-slate-100 flex items-center justify-end gap-3 bg-slate-50/50">
                    <button type="button" @click="showEditModal = false" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition cursor-pointer">Batal</button>
                    <button type="button" @click="saveParoki" :disabled="isSubmitting" class="px-5 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold shadow-xs transition flex items-center gap-2 disabled:opacity-60 cursor-pointer">
                        <i class="fa-solid fa-check"></i>
                        <span>{{ isSubmitting ? 'Menyimpan...' : 'Simpan Perubahan Paroki' }}</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- MODAL 2: EDIT GAMBAR & SEJARAH -->
        <div
            v-if="showImageModal"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/40 backdrop-blur-xs overflow-y-auto"
        >
            <div class="bg-white rounded-3xl max-w-2xl w-full shadow-2xl border border-slate-200 overflow-hidden my-8">
                <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4 flex items-center justify-between text-white">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center text-sm font-black">
                            <i class="fa-regular fa-image"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-black">Edit Gambar & Sejarah Paroki</h3>
                            <p class="text-[11px] text-blue-100">Upload logo paroki (kompresi otomatis) dan narasi sejarah.</p>
                        </div>
                    </div>
                    <button @click="showImageModal = false" class="w-8 h-8 rounded-lg bg-white/10 hover:bg-white/20 flex items-center justify-center transition cursor-pointer">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div class="p-6 space-y-4 max-h-[65vh] overflow-y-auto custom-scrollbar">
                    <!-- Logo Upload with Auto Compress -->
                    <div class="space-y-2 p-3.5 rounded-2xl bg-blue-50/50 border border-blue-200/80">
                        <label class="text-[11px] font-bold text-blue-900 uppercase tracking-wide block">Logo / Lambang Paroki</label>
                        <div class="flex items-center gap-4">
                            <div class="w-16 h-16 rounded-full border-2 border-dashed border-blue-300 bg-white overflow-hidden flex items-center justify-center shrink-0 shadow-xs">
                                <img v-if="logoPreview" :src="logoPreview" class="w-full h-full object-cover" />
                                <i v-else class="fa-solid fa-church text-blue-500 text-xl"></i>
                            </div>
                            <div class="space-y-1">
                                <label class="cursor-pointer inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition shadow-xs">
                                    <i class="fa-solid fa-upload text-[11px]"></i>
                                    <span>Pilih Logo Baru</span>
                                    <input type="file" accept="image/*" class="hidden" @change="handleLogoChange" />
                                </label>
                                <p class="text-[10px] text-blue-700">Gambar akan otomatis dikompresi (Maks 2MB, WebP/JPG/PNG).</p>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-1">
                        <label class="text-[11px] font-bold text-slate-600 uppercase tracking-wide block mb-1">Sejarah & Keterangan Paroki</label>
                        <RichTextEditor
                            v-model="form.keterangan"
                            placeholder="Tuliskan sejarah, latar belakang pendirian, dan profil lengkap paroki..."
                            min-height="220"
                        />
                    </div>
                </div>

                <div class="px-6 py-4 border-t border-slate-100 flex items-center justify-end gap-3 bg-slate-50/50">
                    <button type="button" @click="showImageModal = false" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition cursor-pointer">Batal</button>
                    <button type="button" @click="saveParoki" :disabled="isSubmitting" class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-xs transition flex items-center gap-1.5 disabled:opacity-60 cursor-pointer">
                        <i class="fa-solid fa-check"></i>
                        <span>{{ isSubmitting ? 'Menyimpan...' : 'Simpan Gambar & Sejarah' }}</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- MODAL 3: UPLOAD BANNER HEADER -->
        <div
            v-if="showBannerModal"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/50 backdrop-blur-xs overflow-y-auto"
        >
            <div class="bg-white rounded-3xl max-w-2xl w-full shadow-2xl border border-slate-200 overflow-hidden my-8">
                <div class="bg-gradient-to-r from-emerald-600 via-teal-600 to-emerald-700 px-6 py-4 flex items-center justify-between text-white">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center text-lg font-black">
                            <i class="fa-solid fa-panorama"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-black">Upload Banner Header Website Publik</h3>
                            <p class="text-[11px] text-emerald-100">Ganti latar belakang header halaman Statistik, Jadwal Misa, Kontak, dll.</p>
                        </div>
                    </div>
                    <button @click="showBannerModal = false" class="w-8 h-8 rounded-lg bg-white/10 hover:bg-white/20 flex items-center justify-center transition cursor-pointer">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div class="p-6 space-y-5">
                    <!-- Info Alert -->
                    <div class="p-3.5 rounded-2xl bg-emerald-50 border border-emerald-200/80 flex items-start gap-3 text-emerald-950 text-xs">
                        <i class="fa-solid fa-circle-info text-emerald-600 text-sm mt-0.5 shrink-0"></i>
                        <div class="leading-relaxed text-[11.5px]">
                            Foto yang Anda upload di sini akan langsung otomatis menjadi <b>latar belakang header atas</b> di seluruh sub-halaman publik, termasuk halaman <b>Statistik &amp; Demografi Umat</b>.
                        </div>
                    </div>

                    <!-- Banner Preview Area -->
                    <div class="space-y-2">
                        <label class="text-[11px] font-bold text-slate-700 uppercase tracking-wide block">Preview Banner Latar Belakang</label>
                        <div class="w-full h-48 sm:h-56 rounded-2xl border-2 border-dashed border-emerald-300 bg-slate-900 overflow-hidden relative shadow-inner flex items-center justify-center group">
                            <img
                                v-if="bannerPreview"
                                :src="bannerPreview"
                                class="w-full h-full object-cover transition duration-300 group-hover:scale-105"
                            />
                            <div v-else class="flex flex-col items-center gap-2 text-slate-400">
                                <i class="fa-solid fa-mountain-sun text-4xl text-emerald-500/60"></i>
                                <span class="text-xs font-semibold">Belum ada gambar banner yang dipilih</span>
                                <span class="text-[10px] text-slate-500">Klik tombol di bawah untuk memilih foto dari komputer/HP</span>
                            </div>

                            <!-- Header Simulation Overlay (Green gradient on top of preview) -->
                            <div class="absolute inset-0 bg-gradient-to-r from-emerald-950/75 via-teal-900/60 to-emerald-950/80 pointer-events-none flex flex-col justify-end p-4">
                                <span class="text-white font-black text-sm drop-shadow-md">Simulasi Tampilan Header</span>
                                <span class="text-emerald-200/90 text-[10px] drop-shadow-sm">Statistik &amp; Demografi Umat &bull; Transparansi Data Jemaat</span>
                            </div>
                        </div>
                    </div>

                    <!-- Upload Controls -->
                    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 p-4 rounded-2xl bg-slate-50 border border-slate-200">
                        <label class="cursor-pointer inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition shadow-sm cursor-pointer shrink-0">
                            <i class="fa-solid fa-cloud-arrow-up text-sm"></i>
                            <span>Pilih Foto Banner (Komputer / HP)</span>
                            <input type="file" accept="image/*" class="hidden" @change="handleBannerChange" />
                        </label>
                        <p class="text-[11px] text-slate-500 text-center sm:text-right">Format: JPG, PNG, atau WebP. Rekomendasi foto lanskap melebar (16:9 / 21:9), maks 5MB.</p>
                    </div>
                </div>

                <div class="px-6 py-4 border-t border-slate-100 flex items-center justify-end gap-3 bg-slate-50/75">
                    <button type="button" @click="showBannerModal = false" class="px-4 py-2 rounded-xl bg-slate-200 hover:bg-slate-300 text-slate-700 text-xs font-bold transition cursor-pointer">Batal</button>
                    <button
                        type="button"
                        @click="saveParoki"
                        :disabled="isSubmitting || !form.banner"
                        class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-md transition flex items-center gap-2 disabled:opacity-60 cursor-pointer"
                    >
                        <i class="fa-solid fa-check"></i>
                        <span>{{ isSubmitting ? 'Mengunggah &amp; Menyimpan...' : 'Simpan Banner Header' }}</span>
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
