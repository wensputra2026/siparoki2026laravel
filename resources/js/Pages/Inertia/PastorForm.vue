<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import SearchableSelect from '@/Components/SearchableSelect.vue';
import DateInput from '@/Components/DateInput.vue';

const props = defineProps({
    role: { type: String, default: 'Super Admin' },
    prefix: { type: String, default: 'superadmin' },
    isEdit: { type: Boolean, default: false },
    pastorItem: { type: Object, default: null },
    riwayatList: { type: Array, default: () => [] },
    keuskupanList: { type: Array, default: () => [] },
    dekenatList: { type: Array, default: () => [] },
    parokiList: { type: Array, default: () => [] },
    ordoList: { type: Array, default: () => [] },
    jabatanList: { type: Array, default: () => [] },
});

// Calculate age helper
const calculateAge = (birthDate) => {
    if (!birthDate) return null;
    const today = new Date();
    const birth = new Date(birthDate);
    if (isNaN(birth.getTime())) return null;
    let age = today.getFullYear() - birth.getFullYear();
    const m = today.getMonth() - birth.getMonth();
    if (m < 0 || (m === 0 && today.getDate() < birth.getDate())) {
        age--;
    }
    return age >= 0 ? age : null;
};

// Calculate priesthood anniversary
const calculateAnniversary = (ordinationDate) => {
    if (!ordinationDate) return null;
    const today = new Date();
    const ord = new Date(ordinationDate);
    if (isNaN(ord.getTime())) return null;
    let years = today.getFullYear() - ord.getFullYear();
    const m = today.getMonth() - ord.getMonth();
    if (m < 0 || (m === 0 && today.getDate() < ord.getDate())) {
        years--;
    }
    return years >= 0 ? years : null;
};

// Parse existing riwayat tambahan if any
const parseInitialRiwayat = () => {
    if (props.riwayatList && props.riwayatList.length) {
        return props.riwayatList;
    }
    if (props.pastorItem?.riwayat_tambahan) {
        try {
            return typeof props.pastorItem.riwayat_tambahan === 'string'
                ? JSON.parse(props.pastorItem.riwayat_tambahan)
                : props.pastorItem.riwayat_tambahan;
        } catch (e) {
            return [];
        }
    }
    return [];
};

const riwayatTambahan = ref(parseInitialRiwayat());

// Form state
const form = useForm({
    nama_pastor: props.pastorItem?.nama_pastor || props.pastorItem?.nama || '',
    nama_singkat: props.pastorItem?.nama_singkat || '',
    nama_baptis: props.pastorItem?.nama_baptis || '',
    nama_lahir: props.pastorItem?.nama_lahir || '',
    gelar_depan: props.pastorItem?.gelar_depan || 'RD.',
    gelar_belakang: props.pastorItem?.gelar_belakang || props.pastorItem?.gelar || '',
    jenis_imam: props.pastorItem?.jenis_imam || 'Diosesan / Projo',
    ordo: props.pastorItem?.ordo || '',
    jabatan: props.pastorItem?.jabatan || 'Pastor Paroki',
    tempat_lahir: props.pastorItem?.tempat_lahir || '',
    tanggal_lahir: props.pastorItem?.tanggal_lahir ? String(props.pastorItem.tanggal_lahir).substring(0, 10) : '',
    tgl_tahbisan_diakon: props.pastorItem?.tgl_tahbisan_diakon ? String(props.pastorItem.tgl_tahbisan_diakon).substring(0, 10) : '',
    tgl_tahbisan: props.pastorItem?.tgl_tahbisan ? String(props.pastorItem.tgl_tahbisan).substring(0, 10) : (props.pastorItem?.tgl_tahbisan_imam ? String(props.pastorItem.tgl_tahbisan_imam).substring(0, 10) : ''),
    uskup_penahbis: props.pastorItem?.uskup_penahbis || '',
    tempat_tahbisan: props.pastorItem?.tempat_tahbisan || '',
    motto_tahbisan: props.pastorItem?.motto_tahbisan || props.pastorItem?.motto || '',
    keuskupan_id: props.pastorItem?.keuskupan_id || '',
    dekenat_id: props.pastorItem?.dekenat_id || '',
    paroki_id: props.pastorItem?.paroki_id || '',
    keuskupan: props.pastorItem?.keuskupan || 'Keuskupan Agung Kupang',
    
    // Riwayat Tugas Utama
    jenis_tempat_tugas: props.pastorItem?.jenis_tempat_tugas || 'Paroki',
    paroki_tugas: props.pastorItem?.paroki_tugas || props.pastorItem?.paroki || '',
    periode_mulai: props.pastorItem?.periode_mulai || props.pastorItem?.tgl_mulai_tugas || '2026',
    periode_selesai: props.pastorItem?.periode_selesai || props.pastorItem?.tgl_selesai_tugas || '',
    tampil_frontend: props.pastorItem?.tampil_frontend || 'Tidak',
    urutan: props.pastorItem?.urutan !== undefined ? props.pastorItem.urutan : 0,
    catatan_pelayanan: props.pastorItem?.catatan_pelayanan || props.pastorItem?.biografi_singkat || props.pastorItem?.karya_pelayanan || '',
    
    no_hp: props.pastorItem?.no_hp || props.pastorItem?.telepon || props.pastorItem?.whatsapp || '',
    email: props.pastorItem?.email || '',
    pendidikan_terakhir: props.pastorItem?.pendidikan_terakhir || props.pastorItem?.pendidikan || '',
    seminari_tinggi: props.pastorItem?.seminari_tinggi || '',
    status: props.pastorItem?.status === '1' || props.pastorItem?.status === 1 || !props.pastorItem?.status || props.pastorItem?.status === 'Aktif' ? 'Aktif' : (props.pastorItem?.status === '0' || props.pastorItem?.status === 0 ? 'Nonaktif' : props.pastorItem?.status),
    foto: props.pastorItem?.foto || '',
    foto_file: null,
    riwayat_tambahan: [],
});

// Auto-resolve keuskupan_id if missing but keuskupan name is known
if (!form.keuskupan_id && form.keuskupan) {
    const kMatch = (props.keuskupanList || []).find(k => k.nama_keuskupan && k.nama_keuskupan.toLowerCase() === form.keuskupan.toLowerCase());
    if (kMatch) {
        form.keuskupan_id = kMatch.id_keuskupan;
    }
}

// Auto-resolve paroki_id if missing but paroki_tugas is known
if (!form.paroki_id && form.paroki_tugas) {
    const pMatch = (props.parokiList || []).find(p => p.nama_paroki && (p.nama_paroki.toLowerCase().includes(form.paroki_tugas.toLowerCase()) || form.paroki_tugas.toLowerCase().includes(p.nama_paroki.toLowerCase())));
    if (pMatch) {
        form.paroki_id = pMatch.id_paroki;
        if (pMatch.dekenat_id && !form.dekenat_id) form.dekenat_id = pMatch.dekenat_id;
        if (pMatch.keuskupan_id && !form.keuskupan_id) form.keuskupan_id = pMatch.keuskupan_id;
    }
}

// Live image preview
const imagePreview = ref(props.pastorItem?.foto ? (props.pastorItem.foto.startsWith('http') || props.pastorItem.foto.startsWith('/') ? props.pastorItem.foto : `/${props.pastorItem.foto}`) : null);

const handleFileUpload = (e) => {
    const file = e.target.files[0];
    if (file) {
        form.foto_file = file;
        const reader = new FileReader();
        reader.onload = (re) => {
            imagePreview.value = re.target.result;
        };
        reader.readAsDataURL(file);
    }
};

// Add & remove riwayat tugas tambahan
const addRiwayatTambahan = () => {
    riwayatTambahan.value.push({
        id: Date.now(),
        jenis_tempat_tugas: 'Paroki',
        tempat_tugas: '',
        jabatan: 'Pastor Paroki',
        periode_mulai: '',
        periode_selesai: '',
        urutan: 0,
        status: 'Mantan',
        tampil_frontend: 'Tidak',
        catatan_pelayanan: '',
    });
};

const removeRiwayatTambahan = (index) => {
    riwayatTambahan.value.splice(index, 1);
};

// Computed age & ordination anniversary
const calculatedAge = computed(() => calculateAge(form.tanggal_lahir));
const calculatedOrdinationYears = computed(() => calculateAnniversary(form.tgl_tahbisan));

// Formatted Official Catholic Display Name
const formattedOfficialName = computed(() => {
    let prefix = form.gelar_depan ? form.gelar_depan.trim() : '';
    let name = form.nama_pastor ? form.nama_pastor.trim() : '';
    let suffix = form.gelar_belakang ? form.gelar_belakang.trim() : '';
    let ordo = form.ordo ? form.ordo.trim().toUpperCase() : '';

    if (!name) return 'Nama Belum Diisi';

    let res = prefix ? `${prefix} ${name}` : name;
    if (ordo && ordo !== 'PROJO' && ordo !== 'PR' && !res.includes(ordo)) {
        res += `, ${ordo}`;
    }
    if (suffix && !res.includes(suffix)) {
        res += `, ${suffix}`;
    }
    return res;
});

// Dropdown Options
const jenisImamOptions = [
    { id: 'Diosesan / Projo', name: 'Imam Diosesan / Projo (RD)' },
    { id: 'Religius / Kongregasi', name: 'Imam Religius / Tarekat Hidup Bakti (RP)' },
    { id: 'Uskup / Episkopal', name: 'Uskup / Hierarki Episkopal (Mgr)' },
];

const jenisTempatTugasOptions = [
    { id: 'Paroki', name: 'Paroki' },
    { id: 'Seminari', name: 'Seminari' },
    { id: 'Ordo/Kongregasi', name: 'Ordo/Kongregasi' },
    { id: 'Keuskupan', name: 'Keuskupan' },
    { id: 'Yayasan / Sekolah', name: 'Yayasan / Sekolah' },
    { id: 'Lembaga / Komisi', name: 'Lembaga / Komisi' },
    { id: 'Rumah Sakit / Sosial', name: 'Rumah Sakit / Sosial' },
    { id: 'Lainnya', name: 'Lainnya' },
];

const gelarDepanOptions = [
    { id: 'RD.', name: 'RD. (Reverendus Dominus - Imam Projo)' },
    { id: 'RP.', name: 'RP. (Reverendus Pater - Imam Ordo)' },
    { id: 'P.', name: 'P. (Pater)' },
    { id: 'Romo', name: 'Romo' },
    { id: 'Mgr.', name: 'Mgr. (Monseigneur - Uskup)' },
    { id: 'Fr.', name: 'Fr. (Frater / Calon Imam)' },
];

// Ordo Options from Database (http://127.0.0.1:8000/admin/master-referensi/master_ordo)
const ordoOptions = computed(() => {
    if (props.ordoList && props.ordoList.length) {
        return props.ordoList;
    }
    return [
        { id: '', name: 'Diosesan / Projo (Non-Ordo)' },
        { id: 'CMF', name: 'CMF - Misionaris Claretian (Cordis Mariae Filii)' },
        { id: 'SVD', name: 'SVD - Serikat Sabda Allah (Societas Verbi Divini)' },
        { id: 'OFM', name: 'OFM - Ordo Saudara Dina (Fransiskan)' },
        { id: 'OFMConv', name: 'OFMConv - Ordo Fransiskan Konventual' },
        { id: 'OFMCap', name: 'OFMCap - Ordo Saudara Dina Kapusin' },
        { id: 'OCD', name: 'OCD - Karmelit Tak Berkasut (Karmel)' },
        { id: 'OCarm', name: 'OCarm - Ordo Karmelit' },
        { id: 'SJ', name: 'SJ - Serikat Yesus (Yesuit)' },
        { id: 'SDB', name: 'SDB - Salesian Don Bosco' },
        { id: 'CSsR', name: 'CSsR - Kongregasi Sang Penebus Mahakudus' },
        { id: 'SCJ', name: 'SCJ - Imam-Imam Hati Kudus Yesus (Dehonian)' },
        { id: 'MSF', name: 'MSF - Misionaris Keluarga Kudus' },
        { id: 'CP', name: 'CP - Kongregasi Pasionis' },
        { id: 'OSB', name: 'OSB - Ordo Santo Benediktus (Benediktin)' },
        { id: 'SX', name: 'SX - Serikat Misi Xaverian' },
        { id: 'OMI', name: 'OMI - Oblat Maria Imakulata' },
        { id: 'CICM', name: 'CICM - Misi Scheut' },
        { id: 'CDD', name: 'CDD - Kongregasi Murid-Murid Tuhan' },
        { id: 'CM', name: 'CM - Kongregasi Misi (Vinsensian)' },
    ];
});

const jabatanOptions = computed(() => {
    if (props.jabatanList && props.jabatanList.length) {
        return props.jabatanList;
    }
    return [
        { id: 'Pastor Paroki', name: 'Pastor Paroki' },
        { id: 'Pastor Rekan', name: 'Pastor Rekan' },
        { id: 'Pastor Administrator', name: 'Pastor Administrator' },
        { id: 'Vikaris Jenderal', name: 'Vikaris Jenderal' },
        { id: 'Vikaris Episkopal', name: 'Vikaris Episkopal' },
        { id: 'Pastor Kapelan', name: 'Pastor Kapelan' },
        { id: 'Formator/Pembina Seminari', name: 'Formator/Pembina Seminari' },
        { id: 'Pastor Emeritus (Pensiun)', name: 'Pastor Emeritus (Pensiun)' },
    ];
});

const statusRiwayatOptions = [
    { id: 'Mantan', name: 'Mantan' },
    { id: 'Aktif', name: 'Aktif' },
    { id: 'Purna Tugas', name: 'Purna Tugas' },
    { id: 'Pensiun', name: 'Pensiun' },
];

const statusOptions = [
    { id: 'Aktif', name: 'Aktif' },
    { id: 'Tugas Belajar', name: 'Tugas Belajar (Studi Lanjut S2/S3)' },
    { id: 'Tugas Luar Dioses / Misi', name: 'Tugas Luar Dioses / Misi Luar Negeri' },
    { id: 'Pastor Emeritus (Pensiun)', name: 'Pastor Emeritus (Pensiun)' },
    { id: 'Cuti Kesehatan', name: 'Cuti Pemulihan Kesehatan' },
    { id: 'Meninggal Dunia', name: 'Meninggal Dunia (In Memoriam)' },
];

const keuskupanOptions = computed(() => {
    const list = (props.keuskupanList || []).map(k => ({
        id: k.nama_keuskupan,
        name: k.nama_keuskupan,
    }));
    if (!list.some(k => k.id === 'Keuskupan Agung Kupang')) {
        list.unshift({ id: 'Keuskupan Agung Kupang', name: 'Keuskupan Agung Kupang' });
    }
    return list;
});

// Paroki Options from Database (http://127.0.0.1:8000/superadmin/paroki)
const parokiOptions = computed(() => {
    return (props.parokiList || []).map(p => ({
        id: p.nama_paroki,
        name: p.nama_paroki,
    }));
});

// Dekenat / Kevikepan Options
const dekenatOptions = computed(() => {
    const list = props.dekenatList || [];
    if (!form.keuskupan_id) return list;
    return list.filter(d => String(d.keuskupan_id) === String(form.keuskupan_id));
});

// Filtered Paroki by dekenat_id OR keuskupan_id
const filteredParokiById = computed(() => {
    const list = props.parokiList || [];
    if (form.dekenat_id) {
        return list.filter(p => String(p.dekenat_id) === String(form.dekenat_id));
    }
    if (form.keuskupan_id) {
        return list.filter(p => String(p.keuskupan_id) === String(form.keuskupan_id));
    }
    return list;
});

// Keuskupan options with id as id_keuskupan (for cascading)
const keuskupanById = computed(() => {
    return (props.keuskupanList || []).map(k => ({
        id: k.id_keuskupan,
        name: k.nama_keuskupan,
    }));
});

// When keuskupan_id changes, auto-fill keuskupan name and filter
const onKeuskupanIdChange = (val) => {
    const k = (props.keuskupanList || []).find(item => String(item.id_keuskupan) === String(val));
    if (k) {
        form.keuskupan = k.nama_keuskupan;
    }
    // reset dekenat and paroki if not matching
    if (form.dekenat_id) {
        const dek = (props.dekenatList || []).find(d => String(d.id_dekenat) === String(form.dekenat_id));
        if (dek && String(dek.keuskupan_id) !== String(val)) {
            form.dekenat_id = '';
            form.paroki_id = '';
        }
    }
};

// When paroki_id changes, auto-fill dekenat_id and keuskupan_id
const onParokiIdChange = (val) => {
    const paroki = (props.parokiList || []).find(p => String(p.id_paroki) === String(val));
    if (paroki) {
        if (paroki.dekenat_id) form.dekenat_id = paroki.dekenat_id;
        if (paroki.keuskupan_id) {
            form.keuskupan_id = paroki.keuskupan_id;
            const k = (props.keuskupanList || []).find(item => String(item.id_keuskupan) === String(paroki.keuskupan_id));
            if (k) form.keuskupan = k.nama_keuskupan;
        }
        form.paroki_tugas = paroki.nama_paroki || form.paroki_tugas;
    }
};

// When dekenat_id changes, auto-fill keuskupan_id
const onDekenatIdChange = (val) => {
    const dekenat = (props.dekenatList || []).find(d => String(d.id_dekenat) === String(val));
    if (dekenat?.keuskupan_id) {
        form.keuskupan_id = dekenat.keuskupan_id;
        const k = (props.keuskupanList || []).find(item => String(item.id_keuskupan) === String(dekenat.keuskupan_id));
        if (k) form.keuskupan = k.nama_keuskupan;
    }
    // reset paroki_id if the paroki does not belong to this dekenat
    if (form.paroki_id) {
        const paroki = (props.parokiList || []).find(p => String(p.id_paroki) === String(form.paroki_id));
        if (paroki && String(paroki.dekenat_id) !== String(val)) {
            form.paroki_id = '';
        }
    }
};

// Compute whether Ordo is disabled based on jenis_imam
const isOrdoDisabled = computed(() => {
    return form.jenis_imam === 'Diosesan / Projo' || form.jenis_imam === 'Uskup / Episkopal';
});

// Auto adjust prefix and clear ordo if user changes jenis imam
const onJenisImamChange = (val) => {
    if (val === 'Religius / Kongregasi') {
        form.gelar_depan = 'RP.';
    } else if (val === 'Uskup / Episkopal') {
        form.gelar_depan = 'Mgr.';
        form.ordo = '';
    } else {
        form.gelar_depan = 'RD.';
        form.ordo = '';
    }
};

// Auto sync jenis_imam and gelar_depan if user selects or clears ordo
const onOrdoChange = (val) => {
    if (val && String(val).trim() !== '') {
        form.jenis_imam = 'Religius / Kongregasi';
        if (form.gelar_depan === 'RD.') {
            form.gelar_depan = 'RP.';
        }
    } else {
        if (form.jenis_imam === 'Religius / Kongregasi') {
            form.jenis_imam = 'Diosesan / Projo';
            if (form.gelar_depan === 'RP.') {
                form.gelar_depan = 'RD.';
            }
        }
    }
};

// Back destination link
const backUrl = computed(() => {
    return window.location.pathname.includes('/master-referensi')
        ? '/admin/master-referensi/pastor'
        : `/${props.prefix}/master-pastor`;
});

// Form submission
const submit = () => {
    form.riwayat_tambahan = riwayatTambahan.value;

    // Ensure paroki_tugas is synced from selected paroki
    if (form.paroki_id) {
        const pObj = (props.parokiList || []).find(p => String(p.id_paroki) === String(form.paroki_id));
        if (pObj) {
            form.paroki_tugas = pObj.nama_paroki;
        }
    }
    // Ensure keuskupan is synced from selected keuskupan
    if (form.keuskupan_id) {
        const kObj = (props.keuskupanList || []).find(k => String(k.id_keuskupan) === String(form.keuskupan_id));
        if (kObj) {
            form.keuskupan = kObj.nama_keuskupan;
        }
    }
    // Ensure status is valid string
    if (form.status === '1' || form.status === 1) {
        form.status = 'Aktif';
    }

    const isRefer = typeof window !== 'undefined' && window.location.pathname.includes('/master-referensi');
    const pastorId = props.pastorItem?.id || props.pastorItem?.id_pastor;
    const targetUrl = props.isEdit
        ? (isRefer ? `/admin/master-referensi/pastor/${pastorId}/update` : `/${props.prefix}/master-pastor/${pastorId}/update`)
        : (isRefer ? `/admin/master-referensi/pastor/store` : `/${props.prefix}/master-pastor/store`);

    form.post(targetUrl, {
        forceFormData: true,
        preserveScroll: true,
        onError: (err) => {
            console.error('Error saving pastor:', err);
        },
    });
};
</script>

<template>
    <Head :title="isEdit ? 'Edit Data Pastor / Imam' : 'Tambah Pastor / Imam Baru'" />

    <AppLayout :role="role">
        <div class="w-full space-y-6 pb-16">
            <!-- HEADER / BREADCRUMB -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white rounded-3xl p-6 border border-slate-200 shadow-xs">
                <div>
                    <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                        <Link :href="`/${prefix}/dashboard`" class="hover:text-amber-600 transition">Dashboard</Link>
                        <i class="fa-solid fa-chevron-right text-[10px] text-slate-400"></i>
                        <Link :href="backUrl" class="hover:text-amber-600 transition">Daftar Pastor / Imam</Link>
                        <i class="fa-solid fa-chevron-right text-[10px] text-slate-400"></i>
                        <span class="text-amber-700 font-semibold">{{ isEdit ? 'Edit Pastor' : 'Tambah Pastor Baru' }}</span>
                    </div>
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2.5">
                        <span class="w-9 h-9 rounded-2xl bg-amber-500/10 text-amber-600 flex items-center justify-center text-lg border border-amber-200">
                            <i class="fa-solid fa-user-tie"></i>
                        </span>
                        <span>{{ isEdit ? 'Formulir Edit Data Pastor / Imam' : 'Formulir Tambah Pastor / Imam Baru' }}</span>
                    </h1>
                    <p class="text-xs text-slate-500 mt-1">
                        Master data rohaniwan tertahbis (Imam Diosesan Projo & Imam Religius Ordo) melayani karya pastoral Gereja Katolik.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <Link
                        :href="backUrl"
                        class="px-4 py-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition flex items-center gap-2 cursor-pointer shadow-2xs"
                    >
                        <i class="fa-solid fa-arrow-left"></i>
                        <span>Kembali ke Daftar</span>
                    </Link>
                </div>
            </div>

            <!-- FORM CONTAINER -->
            <form @submit.prevent="submit" class="space-y-6">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                    
                    <!-- LEFT COLUMN: MAIN FIELDS & RIWAYAT TUGAS -->
                    <div class="lg:col-span-8 space-y-6">
                        
                        <!-- SECTION 1: IDENTITAS POKOK ROHANIWAN -->
                        <div class="bg-white rounded-3xl p-6 sm:p-7 border border-slate-200 shadow-xs space-y-5">
                            <div class="border-b border-slate-100 pb-3 flex items-center justify-between">
                                <h3 class="text-sm font-black text-slate-900 flex items-center gap-2">
                                    <i class="fa-solid fa-address-card text-amber-600"></i>
                                    <span>Identitas Pokok Rohaniwan & Gelar</span>
                                </h3>
                                <span class="text-[11px] font-semibold text-amber-700 bg-amber-50 px-2.5 py-0.5 rounded-full border border-amber-200">
                                    Data Master
                                </span>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <!-- Gelar Depan -->
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                        Gelar Depan <span class="text-rose-500">*</span>
                                    </label>
                                    <SearchableSelect
                                        v-model="form.gelar_depan"
                                        :options="gelarDepanOptions"
                                        valueKey="id"
                                        labelKey="name"
                                        placeholder="Pilih gelar..."
                                        searchPlaceholder="Cari gelar..."
                                        icon="fa-cross"
                                        iconColor="text-amber-600"
                                    />
                                </div>

                                <!-- Nama Lengkap / Pastor -->
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                        Nama Pastor <span class="text-rose-500">*</span>
                                    </label>
                                    <input
                                        v-model="form.nama_pastor"
                                        type="text"
                                        required
                                        placeholder="Nama Lengkap Pastor"
                                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs text-slate-800 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 font-semibold"
                                    />
                                </div>

                                <!-- Nama Singkat / Panggilan -->
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                        Nama Singkat / Panggilan
                                    </label>
                                    <input
                                        v-model="form.nama_singkat"
                                        type="text"
                                        placeholder="Contoh: Romo Kris / Pater Frans"
                                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs text-slate-800 focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                                    />
                                </div>

                                <!-- Gelar Belakang / Akademik -->
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                        Gelar Belakang / Akademik
                                    </label>
                                    <input
                                        v-model="form.gelar_belakang"
                                        type="text"
                                        placeholder="Contoh: S.Fil., Lic.Th., M.Th."
                                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs text-slate-800 focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                                    />
                                </div>

                                <!-- Jenis Imam -->
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                        Jenis Imam <span class="text-rose-500">*</span>
                                    </label>
                                    <SearchableSelect
                                        v-model="form.jenis_imam"
                                        :options="jenisImamOptions"
                                        valueKey="id"
                                        labelKey="name"
                                        placeholder="Pilih jenis imam..."
                                        searchPlaceholder="Cari jenis..."
                                        icon="fa-hands-praying"
                                        iconColor="text-purple-600"
                                        @update:modelValue="onJenisImamChange"
                                    />
                                </div>

                                <!-- Ordo / Kongregasi (dari Master Ordo) -->
                                <div>
                                    <div class="flex items-center justify-between mb-1.5">
                                        <label class="block text-xs font-bold text-slate-700">
                                            Ordo / Kongregasi
                                        </label>
                                        <span v-if="isOrdoDisabled" class="text-[10px] font-bold text-slate-400 bg-slate-100 px-2 py-0.5 rounded-md border border-slate-200">
                                            Nonaktif (Imam Projo)
                                        </span>
                                        <span v-else class="text-[10px] font-bold text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded-md border border-indigo-200">
                                            Khusus Religius
                                        </span>
                                    </div>
                                    <SearchableSelect
                                        v-model="form.ordo"
                                        :options="ordoOptions"
                                        valueKey="id"
                                        labelKey="name"
                                        :disabled="isOrdoDisabled"
                                        :placeholder="isOrdoDisabled ? 'Nonaktif untuk Imam Diosesan (Projo)' : 'Pilih ordo / tarekat...'"
                                        searchPlaceholder="Cari ordo dari master ordo..."
                                        icon="fa-shield-halved"
                                        iconColor="text-indigo-600"
                                        @update:modelValue="onOrdoChange"
                                    />
                                </div>

                                <!-- Jabatan -->
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                        Jabatan Gerejani <span class="text-rose-500">*</span>
                                    </label>
                                    <SearchableSelect
                                        v-model="form.jabatan"
                                        :options="jabatanOptions"
                                        valueKey="id"
                                        labelKey="name"
                                        placeholder="Pilih jabatan..."
                                        searchPlaceholder="Cari jabatan..."
                                        icon="fa-briefcase"
                                        iconColor="text-blue-600"
                                    />
                                </div>

                                <!-- Keuskupan (by ID, cascade) -->
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1.5 flex items-center justify-between">
                                        <span>Keuskupan <span class="text-rose-500">*</span></span>
                                        <span v-if="form.keuskupan_id" class="text-[10px] text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded">Terhubung</span>
                                    </label>
                                    <SearchableSelect
                                        v-model="form.keuskupan_id"
                                        :options="keuskupanById"
                                        valueKey="id"
                                        labelKey="name"
                                        placeholder="Pilih keuskupan..."
                                        searchPlaceholder="Cari keuskupan..."
                                        icon="fa-church"
                                        iconColor="text-amber-600"
                                        @update:modelValue="onKeuskupanIdChange"
                                    />
                                </div>

                                <!-- Kevikepan / Dekenat (cascade dari Keuskupan) -->
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1.5 flex items-center justify-between">
                                        <span>Kevikepan / Dekenat</span>
                                        <span v-if="!form.keuskupan_id" class="text-[10px] text-amber-600 bg-amber-50 px-1.5 py-0.5 rounded">Pilih Keuskupan dulu</span>
                                    </label>
                                    <SearchableSelect
                                        v-model="form.dekenat_id"
                                        :options="dekenatOptions"
                                        :disabled="!form.keuskupan_id"
                                        valueKey="id_dekenat"
                                        labelKey="nama_dekenat"
                                        :placeholder="form.keuskupan_id ? 'Pilih kevikepan / dekenat...' : '— Pilih Keuskupan dahulu —'"
                                        searchPlaceholder="Cari kevikepan..."
                                        icon="fa-layer-group"
                                        iconColor="text-indigo-600"
                                        @update:modelValue="onDekenatIdChange"
                                    />
                                </div>

                                <!-- Paroki Tugas (by ID, cascade dari Dekenat) -->
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1.5 flex items-center justify-between">
                                        <span>Paroki Tugas</span>
                                        <span v-if="form.paroki_id" class="text-[10px] text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded">Terhubung ID</span>
                                    </label>
                                    <SearchableSelect
                                        v-model="form.paroki_id"
                                        :options="filteredParokiById"
                                        valueKey="id_paroki"
                                        labelKey="nama_paroki"
                                        placeholder="Pilih paroki tugas..."
                                        searchPlaceholder="Cari paroki..."
                                        icon="fa-place-of-worship"
                                        iconColor="text-blue-600"
                                        @update:modelValue="onParokiIdChange"
                                    />
                                </div>

                                <!-- No HP / WA -->
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                        No. Handphone / WhatsApp
                                    </label>
                                    <input
                                        v-model="form.no_hp"
                                        type="text"
                                        placeholder="08xxxxxxxxxx"
                                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs text-slate-800 focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                                    />
                                </div>

                                <!-- Email -->
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                        Email
                                    </label>
                                    <input
                                        v-model="form.email"
                                        type="email"
                                        placeholder="pastor@keuskupan.org"
                                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs text-slate-800 focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                                    />
                                </div>

                                <!-- Tanggal Lahir -->
                                <div>
                                    <div class="flex items-center justify-between mb-1.5">
                                        <label class="text-xs font-bold text-slate-700">Tanggal Lahir</label>
                                        <span v-if="calculatedAge !== null" class="text-[10px] font-bold text-amber-700 bg-amber-50 px-2 py-0.5 rounded-md border border-amber-200">
                                            {{ calculatedAge }} Tahun
                                        </span>
                                    </div>
                                    <DateInput
                                        v-model="form.tanggal_lahir"
                                        placeholder="dd/mm/yyyy"
                                    />
                                </div>

                                <!-- Tanggal Tahbisan Imamat -->
                                <div>
                                    <div class="flex items-center justify-between mb-1.5">
                                        <label class="text-xs font-bold text-slate-700">Tanggal Tahbisan Imamat</label>
                                        <span v-if="calculatedOrdinationYears !== null" class="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200">
                                            {{ calculatedOrdinationYears }} Thn Imamat
                                        </span>
                                    </div>
                                    <DateInput
                                        v-model="form.tgl_tahbisan"
                                        placeholder="dd/mm/yyyy"
                                        iconColor="text-emerald-600"
                                    />
                                </div>

                                <!-- Motto Tahbisan -->
                                <div class="sm:col-span-2">
                                    <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                        Motto Tahbisan Imamat
                                    </label>
                                    <input
                                        v-model="form.motto_tahbisan"
                                        type="text"
                                        placeholder="Kutipan Sabda Kitab Suci / Motto Tahbisan"
                                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs text-slate-800 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 italic font-serif"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- SECTION 2: RIWAYAT TUGAS UTAMA (MATCHING REFERENSI KATEDRAL) -->
                        <div class="bg-white rounded-3xl p-6 sm:p-7 border border-slate-200 shadow-xs space-y-5">
                            <div class="border-b border-slate-100 pb-3 flex items-center justify-between">
                                <h3 class="text-sm font-black text-slate-900 flex items-center gap-2">
                                    <i class="fa-solid fa-clock-rotate-left text-blue-600"></i>
                                    <span>Riwayat Tugas Utama</span>
                                </h3>
                                <span class="text-[11px] font-semibold text-slate-400">
                                    Otomatis tersimpan saat form disimpan
                                </span>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <!-- Jenis Tempat Tugas -->
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                        Jenis Tempat Tugas
                                    </label>
                                    <select
                                        v-model="form.jenis_tempat_tugas"
                                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs text-slate-800 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 bg-white font-medium"
                                    >
                                        <option v-for="opt in jenisTempatTugasOptions" :key="opt.id" :value="opt.id">
                                            {{ opt.name }}
                                        </option>
                                    </select>
                                </div>

                                <!-- Paroki / Tempat Tugas Dinamis -->
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                        {{ form.jenis_tempat_tugas === 'Paroki' ? 'Paroki Tugas' : (form.jenis_tempat_tugas === 'Ordo/Kongregasi' ? 'Pilih Ordo / Kongregasi' : 'Nama Tempat Tugas') }}
                                    </label>

                                    <!-- Jika Paroki: Dropdown dari database Paroki -->
                                    <SearchableSelect
                                        v-if="form.jenis_tempat_tugas === 'Paroki'"
                                        v-model="form.paroki_tugas"
                                        :options="parokiOptions"
                                        valueKey="id"
                                        labelKey="name"
                                        placeholder="-- Pilih Paroki Tugas --"
                                        searchPlaceholder="Cari paroki dari database..."
                                        icon="fa-place-of-worship"
                                        iconColor="text-emerald-600"
                                    />

                                    <!-- Jika Ordo/Kongregasi: Dropdown dari Master Ordo -->
                                    <SearchableSelect
                                        v-else-if="form.jenis_tempat_tugas === 'Ordo/Kongregasi'"
                                        v-model="form.paroki_tugas"
                                        :options="ordoOptions"
                                        valueKey="id"
                                        labelKey="name"
                                        placeholder="-- Pilih Ordo / Kongregasi --"
                                        searchPlaceholder="Cari ordo dari master ordo..."
                                        icon="fa-shield-halved"
                                        iconColor="text-indigo-600"
                                    />

                                    <!-- Jika Seminari / Keuskupan / Lainnya: Input Text Biasa -->
                                    <input
                                        v-else
                                        v-model="form.paroki_tugas"
                                        type="text"
                                        placeholder="Contoh: Seminari Tinggi, Komisi, Yayasan..."
                                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs text-slate-800 focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                                    />
                                </div>

                                <!-- Mulai & Selesai & Frontend & Urut -->
                                <div class="sm:col-span-2 grid grid-cols-2 sm:grid-cols-4 gap-3">
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Mulai</label>
                                        <input
                                            v-model="form.periode_mulai"
                                            type="text"
                                            placeholder="2026"
                                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs text-slate-800 focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Selesai</label>
                                        <input
                                            v-model="form.periode_selesai"
                                            type="text"
                                            placeholder="Kosong / Sekarang"
                                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs text-slate-800 focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Tampil di Frontend</label>
                                        <select
                                            v-model="form.tampil_frontend"
                                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs text-slate-800 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 bg-white"
                                        >
                                            <option value="Ya">Ya</option>
                                            <option value="Tidak">Tidak</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Urut</label>
                                        <input
                                            v-model="form.urutan"
                                            type="number"
                                            placeholder="0"
                                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs text-slate-800 focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                                        />
                                    </div>
                                </div>

                                <!-- Catatan Pelayanan -->
                                <div class="sm:col-span-2">
                                    <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                        Catatan Pelayanan
                                    </label>
                                    <textarea
                                        v-model="form.catatan_pelayanan"
                                        rows="2.5"
                                        placeholder="Catatan karya dan perutusan pastoral..."
                                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs text-slate-800 focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                                    ></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- SECTION 3: RIWAYAT TUGAS TAMBAHAN (MATCHING REFERENSI KATEDRAL) -->
                        <div class="bg-white rounded-3xl p-6 sm:p-7 border border-slate-200 shadow-xs space-y-5">
                            <div class="border-b border-slate-100 pb-3 flex items-center justify-between">
                                <h3 class="text-sm font-black text-slate-900 flex items-center gap-2">
                                    <i class="fa-solid fa-list-check text-purple-600"></i>
                                    <span>Riwayat Tugas Tambahan</span>
                                </h3>
                                <button
                                    type="button"
                                    @click="addRiwayatTambahan"
                                    class="px-3.5 py-1.5 rounded-xl bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 text-xs font-bold transition flex items-center gap-1.5 cursor-pointer shadow-2xs"
                                >
                                    <i class="fa-solid fa-plus text-[11px]"></i>
                                    <span>Tambah Riwayat</span>
                                </button>
                            </div>

                            <!-- Alert Empty State -->
                            <div
                                v-if="!riwayatTambahan || !riwayatTambahan.length"
                                class="p-4 rounded-2xl bg-blue-50/70 border border-blue-100 text-xs text-blue-900 leading-relaxed flex items-center gap-3"
                            >
                                <i class="fa-solid fa-circle-info text-blue-600 text-base shrink-0"></i>
                                <span>Belum ada riwayat tambahan. Tambahkan jika pastor pernah bertugas di lebih dari satu paroki, seminari, lembaga, keuskupan, atau tempat lainnya.</span>
                            </div>

                            <!-- Dynamic Additional History Cards -->
                            <div v-else class="space-y-4">
                                <div
                                    v-for="(rw, rIdx) in riwayatTambahan"
                                    :key="rw.id || rIdx"
                                    class="p-5 rounded-2xl bg-white border border-slate-200 shadow-xs relative group space-y-4"
                                >
                                    <!-- Header Card -->
                                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                                        <span class="text-xs font-black text-slate-800 flex items-center gap-2">
                                            <i class="fa-solid fa-clock-rotate-left text-blue-600"></i>
                                            <span>RIWAYAT TAMBAHAN</span>
                                        </span>
                                        <button
                                            type="button"
                                            @click="removeRiwayatTambahan(rIdx)"
                                            class="px-3 py-1 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 text-xs font-bold cursor-pointer transition flex items-center gap-1.5"
                                            title="Hapus baris riwayat ini"
                                        >
                                            <i class="fa-solid fa-trash-can text-[11px]"></i>
                                            <span>Hapus</span>
                                        </button>
                                    </div>

                                    <!-- Grid Baris 1: Jenis Tempat Tugas & Tempat Tugas -->
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Jenis Tempat Tugas</label>
                                            <select
                                                v-model="rw.jenis_tempat_tugas"
                                                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs bg-white focus:ring-2 focus:ring-amber-500 focus:border-amber-500 font-medium"
                                            >
                                                <option v-for="opt in jenisTempatTugasOptions" :key="opt.id" :value="opt.id">
                                                    {{ opt.name }}
                                                </option>
                                            </select>
                                        </div>

                                        <div>
                                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                                {{ rw.jenis_tempat_tugas === 'Paroki' ? 'Paroki Tugas' : (rw.jenis_tempat_tugas === 'Ordo/Kongregasi' ? 'Pilih Ordo / Kongregasi' : 'Nama Tempat Tugas') }}
                                            </label>

                                            <!-- Dropdown Paroki (jika Jenis = Paroki) -->
                                            <select
                                                v-if="rw.jenis_tempat_tugas === 'Paroki'"
                                                v-model="rw.tempat_tugas"
                                                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs bg-white focus:ring-2 focus:ring-amber-500 focus:border-amber-500 font-medium"
                                            >
                                                <option value="">-- Pilih Paroki --</option>
                                                <option v-for="p in parokiOptions" :key="p.id" :value="p.id">
                                                    {{ p.name }}
                                                </option>
                                            </select>

                                            <!-- Dropdown Ordo (jika Jenis = Ordo/Kongregasi) -->
                                            <select
                                                v-else-if="rw.jenis_tempat_tugas === 'Ordo/Kongregasi'"
                                                v-model="rw.tempat_tugas"
                                                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs bg-white focus:ring-2 focus:ring-amber-500 focus:border-amber-500 font-medium"
                                            >
                                                <option value="">-- Pilih Ordo / Kongregasi --</option>
                                                <option v-for="o in ordoOptions" :key="o.id" :value="o.id">
                                                    {{ o.name }}
                                                </option>
                                            </select>

                                            <!-- Input Text Biasa (jika Seminari / Lembaga / Lainnya) -->
                                            <input
                                                v-else
                                                v-model="rw.tempat_tugas"
                                                type="text"
                                                placeholder="Contoh: Seminari Tinggi, Komisi, Yayasan..."
                                                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs bg-white focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                                            />
                                        </div>
                                    </div>

                                    <!-- Grid Baris 2: Jabatan, Mulai, Selesai, Urut, Status -->
                                    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 text-xs">
                                        <div class="col-span-2 sm:col-span-1">
                                            <label class="block text-[11px] font-bold text-slate-700 mb-1">Jabatan</label>
                                            <select
                                                v-model="rw.jabatan"
                                                class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs bg-white focus:ring-2 focus:ring-amber-500"
                                            >
                                                <option v-for="j in jabatanOptions" :key="j.id" :value="j.id">
                                                    {{ j.name }}
                                                </option>
                                            </select>
                                        </div>

                                        <div>
                                            <label class="block text-[11px] font-bold text-slate-700 mb-1">Mulai</label>
                                            <input
                                                v-model="rw.periode_mulai"
                                                type="text"
                                                placeholder="Contoh: 2018"
                                                class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs bg-white focus:ring-2 focus:ring-amber-500"
                                            />
                                        </div>

                                        <div>
                                            <label class="block text-[11px] font-bold text-slate-700 mb-1">Selesai</label>
                                            <input
                                                v-model="rw.periode_selesai"
                                                type="text"
                                                placeholder="Kosong"
                                                class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs bg-white focus:ring-2 focus:ring-amber-500"
                                            />
                                        </div>

                                        <div>
                                            <label class="block text-[11px] font-bold text-slate-700 mb-1">Urut</label>
                                            <input
                                                v-model="rw.urutan"
                                                type="number"
                                                placeholder="0"
                                                class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs bg-white focus:ring-2 focus:ring-amber-500"
                                            />
                                        </div>

                                        <div>
                                            <label class="block text-[11px] font-bold text-slate-700 mb-1">Status</label>
                                            <select
                                                v-model="rw.status"
                                                class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs bg-white focus:ring-2 focus:ring-amber-500"
                                            >
                                                <option v-for="s in statusRiwayatOptions" :key="s.id" :value="s.id">
                                                    {{ s.name }}
                                                </option>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Grid Baris 3: Tampil di Frontend & Catatan Pelayanan -->
                                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-3 text-xs">
                                        <div>
                                            <label class="block text-[11px] font-bold text-slate-700 mb-1">Tampil di Frontend</label>
                                            <select
                                                v-model="rw.tampil_frontend"
                                                class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs bg-white focus:ring-2 focus:ring-amber-500"
                                            >
                                                <option value="Ya">Ya</option>
                                                <option value="Tidak">Tidak</option>
                                            </select>
                                        </div>

                                        <div class="sm:col-span-3">
                                            <label class="block text-[11px] font-bold text-slate-700 mb-1">Catatan Pelayanan</label>
                                            <input
                                                v-model="rw.catatan_pelayanan"
                                                type="text"
                                                placeholder="Catatan karya pastoral di tempat ini..."
                                                class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs bg-white focus:ring-2 focus:ring-amber-500"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- RIGHT COLUMN: FOTO PROFIL & ACTION BOX (MATCHING KATEDRAL) -->
                    <div class="lg:col-span-4 space-y-6">
                        <!-- FOTO CARD -->
                        <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-xs flex flex-col items-center text-center space-y-4">
                            <div class="w-36 h-36 rounded-3xl overflow-hidden bg-slate-100 border-2 border-amber-200 shadow-md flex items-center justify-center relative">
                                <img
                                    v-if="imagePreview"
                                    :src="imagePreview"
                                    :alt="form.nama_pastor || 'Foto Pastor'"
                                    class="w-full h-full object-cover"
                                    @error="(e) => { e.target.onerror = null; imagePreview = null; }"
                                />
                                <i v-else class="fa-solid fa-user-tie text-6xl text-slate-300"></i>
                            </div>

                            <div class="w-full">
                                <label class="block text-xs font-bold text-slate-700 mb-2">Foto Pastor / Imam</label>
                                <input
                                    type="file"
                                    accept="image/*"
                                    @change="handleFileUpload"
                                    class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-amber-50 file:text-amber-700 hover:file:bg-amber-100 border border-slate-200 rounded-xl cursor-pointer"
                                />
                                <p class="text-[10px] text-slate-400 mt-1.5">Format JPG/PNG. Maksimal 2MB.</p>
                            </div>
                        </div>

                        <!-- LIVE PREVIEW NAME -->
                        <div class="bg-gradient-to-br from-amber-500 to-amber-600 rounded-3xl p-6 text-white shadow-md space-y-2 text-center">
                            <span class="text-[10px] uppercase font-bold tracking-widest text-amber-200">Format Gelar Resmi</span>
                            <h4 class="text-base font-black leading-snug">
                                {{ formattedOfficialName }}
                            </h4>
                            <p class="text-xs text-amber-100 font-medium pt-1 border-t border-white/20">
                                {{ form.jabatan }} &bull; {{ form.keuskupan }}
                            </p>
                        </div>

                        <!-- SIMPAN DATA CARD -->
                        <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-xs space-y-4">
                            <div class="border-b border-slate-100 pb-3">
                                <h4 class="text-xs font-black text-slate-900 uppercase tracking-wider flex items-center gap-2">
                                    <i class="fa-solid fa-floppy-disk text-blue-600"></i>
                                    <span>Simpan Data</span>
                                </h4>
                            </div>

                            <div class="space-y-2.5">
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="w-full py-3 rounded-2xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-black transition flex items-center justify-center gap-2 shadow-md shadow-blue-500/25 cursor-pointer disabled:opacity-50"
                                >
                                    <i v-if="form.processing" class="fa-solid fa-spinner fa-spin"></i>
                                    <i v-else class="fa-solid fa-floppy-disk"></i>
                                    <span>{{ form.processing ? 'Menyimpan...' : 'Simpan Pastor' }}</span>
                                </button>

                                <Link
                                    :href="backUrl"
                                    class="w-full py-2.5 rounded-2xl bg-white hover:bg-slate-100 text-slate-700 border border-slate-200 text-xs font-bold transition flex items-center justify-center gap-2 cursor-pointer shadow-2xs"
                                >
                                    <i class="fa-solid fa-xmark text-rose-500"></i>
                                    <span>Batal / Kembali</span>
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
