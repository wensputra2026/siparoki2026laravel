<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import SearchableSelect from '@/Components/SearchableSelect.vue';
import { triggerToast } from '@/composables/useRoleMenu';

const props = defineProps({
    role: { type: String, default: 'Super Admin' },
    prefix: { type: String, default: 'superadmin' },
    isEdit: { type: Boolean, default: false },
    umatItem: { type: Object, default: null },
    kkList: { type: Array, default: () => [] },
    wilayahList: { type: Array, default: () => [] },
    kapelaList: { type: Array, default: () => [] },
    kubList: { type: Array, default: () => [] },
    pastorList: { type: Array, default: () => [] },
    parokiList: { type: Array, default: () => [] },
    namaParoki: { type: String, default: 'Paroki St. Vinsensius a Paulo Benlutu' },
    namaKeuskupan: { type: String, default: 'Keuskupan Agung Kupang' },
});

const isSubmitting = ref(false);
const activeTab = ref('identitas'); // 'identitas', 'sosial', 'sakramen', 'panggilan', 'gerejani'

// Helper kalkulasi usia otomatis
const calculateAge = (dateString) => {
    if (!dateString) return null;
    const birthDate = new Date(dateString);
    if (isNaN(birthDate.getTime())) return null;
    const today = new Date();
    let age = today.getFullYear() - birthDate.getFullYear();
    const monthDiff = today.getMonth() - birthDate.getMonth();
    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
        age--;
    }
    return age >= 0 ? age : null;
};

// State Form
const form = ref({
    id: props.umatItem?.id || null,
    kk_id: props.umatItem?.kk_id || '',
    nik: props.umatItem?.nik || '',
    nama_lengkap: props.umatItem?.nama_lengkap || props.umatItem?.nama_lahir || '',
    nama_baptis: props.umatItem?.nama_baptis || '',
    nama_lahir: props.umatItem?.nama_lahir || '',
    nama_marga: props.umatItem?.nama_marga || '',
    jenis_kelamin: props.umatItem?.jenis_kelamin || 'L',
    tempat_lahir: props.umatItem?.tempat_lahir || '',
    tanggal_lahir: props.umatItem?.tanggal_lahir ? String(props.umatItem.tanggal_lahir).substring(0, 10) : '',
    golongan_darah: props.umatItem?.golongan_darah || 'Tidak Tahu',
    hubungan_keluarga: props.umatItem?.hubungan_keluarga || 'Kepala Keluarga',
    anak_ke: props.umatItem?.anak_ke || 1,
    agama_asal: props.umatItem?.agama_asal || 'Katolik sejak lahir',
    status_perkawinan: props.umatItem?.status_perkawinan || 'Belum Menikah',
    status_perkawinan_kanonik: props.umatItem?.status_perkawinan_kanonik || 'Katolik Organik',
    nama_pasangan: props.umatItem?.nama_pasangan || '',
    pendidikan: props.umatItem?.pendidikan || 'SMA / SMK / Sederajat',
    pekerjaan: props.umatItem?.pekerjaan || 'Petani / Pekebun',
    talenta: props.umatItem?.talenta || '',
    disabilitas: props.umatItem?.disabilitas || 'Tidak Ada',
    handphone: props.umatItem?.handphone || '',
    email: props.umatItem?.email || '',
    
    // Sakramen
    status_baptis: props.umatItem?.status_baptis ?? 1,
    jenis_penerimaan_baptis: props.umatItem?.jenis_penerimaan_baptis || 'Baptis Bayi (Infantis)',
    tgl_baptis: props.umatItem?.tgl_baptis ? String(props.umatItem.tgl_baptis).substring(0, 10) : '',
    paroki_baptis: props.umatItem?.paroki_baptis || props.namaParoki,
    pastor_baptis: props.umatItem?.pastor_baptis || '',
    wali_baptis: props.umatItem?.wali_baptis || '',
    buku_baptis_vol: props.umatItem?.buku_baptis_vol || '',
    buku_baptis_hal: props.umatItem?.buku_baptis_hal || '',
    buku_baptis_no: props.umatItem?.buku_baptis_no || '',
    tgl_komuni_1: props.umatItem?.tgl_komuni_1 ? String(props.umatItem.tgl_komuni_1).substring(0, 10) : '',
    paroki_komuni_1: props.umatItem?.paroki_komuni_1 || props.namaParoki,
    tgl_krisma: props.umatItem?.tgl_krisma ? String(props.umatItem.tgl_krisma).substring(0, 10) : '',
    paroki_krisma: props.umatItem?.paroki_krisma || props.namaParoki,
    tgl_perkawinan: props.umatItem?.tgl_perkawinan ? String(props.umatItem.tgl_perkawinan).substring(0, 10) : '',
    paroki_perkawinan: props.umatItem?.paroki_perkawinan || props.namaParoki,
    peristiwa_lain: props.umatItem?.peristiwa_lain || '',
    no_surat_peristiwa: props.umatItem?.no_surat_peristiwa || '',

    // Status Panggilan
    status_panggilan: props.umatItem?.status_panggilan || 'Awam',
    nama_ordo_kongregasi: props.umatItem?.nama_ordo_kongregasi || '',
    tahap_panggilan: props.umatItem?.tahap_panggilan || '',
    tempat_tugas_biara: props.umatItem?.tempat_tugas_biara || '',
    tgl_tahbisan_kaul: props.umatItem?.tgl_tahbisan_kaul ? String(props.umatItem.tgl_tahbisan_kaul).substring(0, 10) : '',

    // Wilayah
    wilayah_id: props.umatItem?.wilayah_id || '',
    kapela_id: props.umatItem?.kapela_id || '',
    kub_id: props.umatItem?.kub_id || '',
    status_umat: props.umatItem?.status_umat || 'Aktif',
});

// Computed live usia
const calculatedAge = computed(() => calculateAge(form.value.tanggal_lahir));

// Opsi Terformat untuk SearchableSelect
const kkOptions = computed(() => {
    return (props.kkList || []).map(kk => ({
        id: kk.id,
        name: `${kk.no_kk_kw || 'No KK -'} • ${kk.nama_lahir_pemilik || ''} (${kk.nama_baptis_pemilik || '-'})`,
        wilayah_id: kk.wilayah_id,
        kapela_id: kk.kapela_id,
        kub_id: kk.kub_id,
    }));
});

// Filter KUB bertingkat
const filteredKubs = computed(() => {
    let list = props.kubList || [];
    if (form.value.wilayah_id) {
        list = list.filter(k => String(k.wilayah_id) === String(form.value.wilayah_id));
    }
    return list;
});

// Opsi Paroki dari database
const parokiOptions = computed(() => {
    const defaultList = (props.parokiList || []).map(p => ({
        id: p.nama_paroki,
        name: p.nama_paroki,
    }));
    if (props.namaParoki && !defaultList.some(p => p.id === props.namaParoki)) {
        defaultList.unshift({ id: props.namaParoki, name: props.namaParoki });
    }
    return defaultList;
});

// Opsi Pastor dari database master_pastor
const pastorOptions = computed(() => {
    return (props.pastorList || []).map(p => ({
        id: p.name || p.nama_pastor || p.id,
        name: p.name || p.nama_pastor,
        jabatan: p.jabatan || 'Pastor',
    }));
});

// Opsi Standar Referensi
const hubunganKeluargaOptions = [
    { id: 'Kepala Keluarga', name: 'Kepala Keluarga' },
    { id: 'Istri', name: 'Istri' },
    { id: 'Anak', name: 'Anak' },
    { id: 'Orang Tua', name: 'Orang Tua' },
    { id: 'Mertua', name: 'Mertua' },
    { id: 'Menantu', name: 'Menantu' },
    { id: 'Cucu', name: 'Cucu' },
    { id: 'Famili Lain', name: 'Famili Lain' },
];

const golonganDarahOptions = [
    { id: 'Tidak Tahu', name: 'Tidak Tahu' },
    { id: 'A', name: 'Golongan Darah A' },
    { id: 'B', name: 'Golongan Darah B' },
    { id: 'AB', name: 'Golongan Darah AB' },
    { id: 'O', name: 'Golongan Darah O' },
];

const agamaAsalOptions = [
    { id: 'Katolik sejak lahir', name: 'Katolik sejak lahir' },
    { id: 'Katekumen', name: 'Katekumen (Penerimaan Dewasa)' },
    { id: 'Protestan', name: 'Protestan (Receptio)' },
    { id: 'Islam', name: 'Islam' },
    { id: 'Hindu', name: 'Hindu' },
    { id: 'Budha', name: 'Budha' },
    { id: 'Lainnya', name: 'Lainnya' },
];

const pendidikanOptions = [
    { id: 'Tidak / Belum Sekolah', name: 'Tidak / Belum Sekolah' },
    { id: 'SD / Sederajat', name: 'SD / Sederajat' },
    { id: 'SMP / Sederajat', name: 'SMP / Sederajat' },
    { id: 'SMA / SMK / Sederajat', name: 'SMA / SMK / Sederajat' },
    { id: 'Diploma (D1-D3)', name: 'Diploma (D1-D3)' },
    { id: 'Sarjana (S1)', name: 'Sarjana (S1)' },
    { id: 'Magister (S2)', name: 'Magister (S2)' },
    { id: 'Doktoral (S3)', name: 'Doktoral (S3)' },
];

const pekerjaanOptions = [
    { id: 'Petani / Pekebun', name: 'Petani / Pekebun' },
    { id: 'PNS / ASN', name: 'PNS / ASN' },
    { id: 'Guru / Dosen', name: 'Guru / Dosen' },
    { id: 'Karyawan Swasta', name: 'Karyawan Swasta' },
    { id: 'Wiraswasta / Pedagang', name: 'Wiraswasta / Pedagang' },
    { id: 'TNI / Polri', name: 'TNI / Polri' },
    { id: 'Tenaga Medis / Perawat / Dokter', name: 'Tenaga Medis / Perawat / Dokter' },
    { id: 'Tukang / Buruh Bangunan', name: 'Tukang / Buruh Bangunan' },
    { id: 'Pelajar / Mahasiswa', name: 'Pelajar / Mahasiswa' },
    { id: 'Ibu Rumah Tangga', name: 'Ibu Rumah Tangga' },
    { id: 'Pensiunan', name: 'Pensiunan' },
    { id: 'Belum / Tidak Bekerja', name: 'Belum / Tidak Bekerja' },
    { id: 'Lainnya', name: 'Lainnya' },
];

const disabilitasOptions = [
    { id: 'Tidak Ada', name: 'Tidak Ada (Non-Disabilitas)' },
    { id: 'Rungu / Wicara', name: 'Tuna Rungu / Wicara' },
    { id: 'Netra', name: 'Tuna Netra' },
    { id: 'Daksa / Fisik', name: 'Tuna Daksa / Fisik' },
    { id: 'Mental', name: 'Mental / Intelektual' },
    { id: 'Lansia Perawatan', name: 'Lansia Perawatan Khusus' },
];

const statusBaptisOptions = [
    { id: 1, name: 'Sudah Baptis' },
    { id: 0, name: 'Belum / Katekumen' },
];

const jenisPenerimaanBaptisOptions = [
    { id: 'Baptis Bayi (Infantis)', name: 'Baptis Bayi (Infantis)' },
    { id: 'Baptis Dewasa (Adultus)', name: 'Baptis Dewasa (Adultus)' },
    { id: 'Receptio (Penerimaan ke Katolik)', name: 'Receptio (Penerimaan ke Katolik)' },
];

const statusPanggilanOptions = [
    { id: 'Awam', name: 'Awam (Umat Biasa)' },
    { id: 'Imam Projo / Diosesan (RD)', name: 'Imam Projo / Diosesan (RD)' },
    { id: 'Imam Religius / Ordo (RP)', name: 'Imam Religius / Ordo (RP)' },
    { id: 'Suster / Biarawati (Sr)', name: 'Suster / Biarawati (Sr)' },
    { id: 'Frater (Fr)', name: 'Frater (Fr)' },
    { id: 'Bruder (Br)', name: 'Bruder (Br)' },
    { id: 'Kongregasi / Ordo', name: 'Anggota Kongregasi Lainnya' },
];

const tahapPanggilanOptions = [
    { id: 'Aspiran', name: 'Aspiran' },
    { id: 'Postulan', name: 'Postulan' },
    { id: 'Novis', name: 'Novis' },
    { id: 'Kaul Sementara / Pertama', name: 'Kaul Sementara / Pertama' },
    { id: 'Kaul Kekal / Profesi Khusus', name: 'Kaul Kekal / Profesi Khusus' },
    { id: 'Diakon', name: 'Diakon' },
    { id: 'Tahbisan Imamat', name: 'Tahbisan Imamat' },
];

const statusUmatOptions = [
    { id: 'Aktif', name: 'Aktif (Terdaftar & Hadir)' },
    { id: 'Pindah KUB', name: 'Pindah KUB' },
    { id: 'Pindah Paroki', name: 'Pindah Paroki (Keluar)' },
    { id: 'Meninggal', name: 'Meninggal Dunia' },
    { id: 'Tidak Aktif', name: 'Tidak Aktif' },
];

// Auto-fill KK data jika KK dipilih
const onKkChange = (kkId) => {
    form.value.kk_id = kkId;
    const selectedKk = props.kkList.find(k => String(k.id) === String(kkId));
    if (selectedKk) {
        if (selectedKk.wilayah_id) form.value.wilayah_id = selectedKk.wilayah_id;
        if (selectedKk.kapela_id) form.value.kapela_id = selectedKk.kapela_id;
        if (selectedKk.kub_id) form.value.kub_id = selectedKk.kub_id;
    }
};

const submitForm = () => {
    if (!form.value.nama_lengkap) {
        triggerToast('Nama Lengkap Umat wajib diisi.', 'error');
        return;
    }

    isSubmitting.value = true;
    const targetUrl = props.isEdit
        ? `/${props.prefix}/umat/${props.umatItem.uuid || props.umatItem.id}/update`
        : `/${props.prefix}/umat/store`;

    router.post(targetUrl, form.value, {
        onFinish: () => {
            isSubmitting.value = false;
        },
        onError: () => {
            isSubmitting.value = false;
        },
    });
};
</script>

<template>
    <AppLayout :title="isEdit ? 'Edit Data Umat' : 'Tambah Data Umat Baru'">
        <Head :title="`${isEdit ? 'Edit' : 'Tambah'} Data Umat - SIPAROKI`" />

        <div class="w-full space-y-6 pb-16">
            <!-- 1. HEADER BANNER EKSEKUTIF -->
            <div class="rounded-3xl bg-gradient-to-r from-amber-600 via-amber-500 to-amber-600 p-6 sm:p-8 text-white shadow-xl shadow-amber-500/15 relative overflow-hidden">
                <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
                <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div class="space-y-2">
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/15 backdrop-blur-md text-amber-100 text-xs font-semibold border border-white/20">
                            <i class="fa-solid fa-users"></i>
                            <span>Direktori Master Data Umat / Jiwa Paroki</span>
                        </div>
                        <h1 class="text-2xl sm:text-3xl font-black tracking-tight">
                            {{ isEdit ? 'Perbarui Data Umat' : 'Form Tambah Data Umat / Jiwa Baru' }}
                        </h1>
                        <p class="text-amber-100 text-xs sm:text-sm max-w-2xl leading-relaxed">
                            {{ namaParoki }} &bull; Lengkapi formulir sensus jiwa, status sakramen inisiasi, panggilan hidup bakti, dan wilayah pastoral.
                        </p>
                    </div>

                    <div class="flex items-center gap-3 shrink-0">
                        <Link
                            :href="`/${prefix}/umat`"
                            class="px-4 py-2.5 rounded-2xl bg-white/20 hover:bg-white/30 backdrop-blur-md text-white text-xs font-bold border border-white/30 shadow-sm transition flex items-center gap-2"
                        >
                            <i class="fa-solid fa-arrow-left"></i>
                            <span>Kembali ke Data Umat</span>
                        </Link>
                        <Link
                            v-if="isEdit && umatItem?.id"
                            :href="`/${prefix}/umat/${umatItem.id}/mutasi`"
                            class="px-4 py-2.5 rounded-2xl bg-emerald-500/90 hover:bg-emerald-500 backdrop-blur-md text-white text-xs font-bold border border-white/30 shadow-sm transition flex items-center gap-2"
                        >
                            <i class="fa-solid fa-arrows-rotate"></i>
                            <span>Mutasi KUB</span>
                        </Link>
                        <Link
                            v-if="isEdit && umatItem?.id"
                            :href="`/${prefix}/umat/${umatItem.id}/pisah-kk`"
                            class="px-4 py-2.5 rounded-2xl bg-sky-500/90 hover:bg-sky-500 backdrop-blur-md text-white text-xs font-bold border border-white/30 shadow-sm transition flex items-center gap-2"
                        >
                            <i class="fa-solid fa-ring"></i>
                            <span>Pisah KK (Menikah)</span>
                        </Link>
                        <Link
                            v-if="isEdit && umatItem?.id"
                            :href="`/${prefix}/umat/${umatItem.id}/riwayat`"
                            class="px-4 py-2.5 rounded-2xl bg-fuchsia-600/90 hover:bg-fuchsia-600 backdrop-blur-md text-white text-xs font-bold border border-white/30 shadow-sm transition flex items-center gap-2"
                        >
                            <i class="fa-solid fa-clock-rotate-left"></i>
                            <span>Riwayat Mutasi</span>
                        </Link>
                    </div>
                </div>
            </div>

            <!-- 2. TAB NAVIGASI FORMULIR -->
            <div class="flex items-center gap-2 overflow-x-auto pb-2 border-b border-slate-200">
                <button
                    type="button"
                    @click="activeTab = 'identitas'"
                    :class="[
                        'px-4 py-2.5 rounded-2xl text-xs font-bold transition flex items-center gap-2 shrink-0 cursor-pointer',
                        activeTab === 'identitas'
                            ? 'bg-amber-500 text-white shadow-md shadow-amber-500/25'
                            : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200'
                    ]"
                >
                    <i class="fa-solid fa-id-card"></i>
                    <span>1. Identitas Sipil & Lahir</span>
                </button>
                <button
                    type="button"
                    @click="activeTab = 'sosial'"
                    :class="[
                        'px-4 py-2.5 rounded-2xl text-xs font-bold transition flex items-center gap-2 shrink-0 cursor-pointer',
                        activeTab === 'sosial'
                            ? 'bg-amber-500 text-white shadow-md shadow-amber-500/25'
                            : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200'
                    ]"
                >
                    <i class="fa-solid fa-graduation-cap"></i>
                    <span>2. Sosial, Profesi</span>
                </button>
                <button
                    type="button"
                    @click="activeTab = 'sakramen'"
                    :class="[
                        'px-4 py-2.5 rounded-2xl text-xs font-bold transition flex items-center gap-2 shrink-0 cursor-pointer',
                        activeTab === 'sakramen'
                            ? 'bg-amber-500 text-white shadow-md shadow-amber-500/25'
                            : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200'
                    ]"
                >
                    <i class="fa-solid fa-cross"></i>
                    <span>3. Sakramen Gereja</span>
                </button>
                <button
                    type="button"
                    @click="activeTab = 'panggilan'"
                    :class="[
                        'px-4 py-2.5 rounded-2xl text-xs font-bold transition flex items-center gap-2 shrink-0 cursor-pointer',
                        activeTab === 'panggilan'
                            ? 'bg-purple-600 text-white shadow-md shadow-purple-600/25'
                            : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200'
                    ]"
                >
                    <i class="fa-solid fa-hands-praying"></i>
                    <span>4. Status Panggilan & Vokasi</span>
                </button>
                <button
                    type="button"
                    @click="activeTab = 'gerejani'"
                    :class="[
                        'px-4 py-2.5 rounded-2xl text-xs font-bold transition flex items-center gap-2 shrink-0 cursor-pointer',
                        activeTab === 'gerejani'
                            ? 'bg-amber-500 text-white shadow-md shadow-amber-500/25'
                            : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200'
                    ]"
                >
                    <i class="fa-solid fa-church"></i>
                    <span>5. Wilayah Pastoral & KUB</span>
                </button>
            </div>

            <!-- 3. FORM BODY -->
            <form @submit.prevent="submitForm" class="space-y-6">
                <!-- TAB 1: IDENTITAS SIPIL -->
                <div v-show="activeTab === 'identitas'" class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-xs space-y-6">
                    <div class="border-b border-slate-100 pb-4">
                        <h3 class="text-base font-black text-slate-900 flex items-center gap-2">
                            <i class="fa-solid fa-id-card text-amber-600"></i>
                            <span>Identitas Pokok & Kependudukan</span>
                        </h3>
                        <p class="text-xs text-slate-500">Data kependudukan sipil dan data kelahiran umat</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
                        <!-- Pilihan Kartu Keluarga dengan Select2 Searchable -->
                        <div class="sm:col-span-2 lg:col-span-3 xl:col-span-4">
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                Terdaftar di Kartu Keluarga (KK Katolik)
                            </label>
                            <SearchableSelect
                                v-model="form.kk_id"
                                :options="kkOptions"
                                valueKey="id"
                                labelKey="name"
                                placeholder="-- Cari No KK / Nama Kepala Keluarga --"
                                searchPlaceholder="Ketik No KK atau nama kepala keluarga..."
                                icon="fa-folder-open"
                                iconColor="text-amber-600"
                                @change="onKkChange"
                            />
                        </div>

                        <!-- NIK -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                NIK (Nomor Induk Kependudukan)
                            </label>
                            <input
                                v-model="form.nik"
                                type="text"
                                maxlength="16"
                                placeholder="16 digit angka NIK"
                                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs text-slate-800 focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                            />
                        </div>

                        <!-- Nama Lengkap Sipil -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                Nama Lengkap Sipil <span class="text-rose-500">*</span>
                            </label>
                            <input
                                v-model="form.nama_lengkap"
                                type="text"
                                required
                                placeholder="Nama lengkap sesuai KTP/Akta"
                                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs text-slate-800 focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                            />
                        </div>

                        <!-- Nama Baptis Santo/Santa -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                Nama Baptis (Santo / Santa Pelindung)
                            </label>
                            <input
                                v-model="form.nama_baptis"
                                type="text"
                                placeholder="Contoh: Fransiskus Xaverius, Maria Goretti"
                                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs text-slate-800 focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                            />
                        </div>

                        <!-- Jenis Kelamin -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                Jenis Kelamin <span class="text-rose-500">*</span>
                            </label>
                            <div class="flex items-center gap-4 pt-2">
                                <label class="inline-flex items-center gap-2 text-xs font-semibold text-slate-700 cursor-pointer">
                                    <input type="radio" v-model="form.jenis_kelamin" value="L" class="text-amber-600 focus:ring-amber-500" />
                                    <span>Laki-laki (Pria)</span>
                                </label>
                                <label class="inline-flex items-center gap-2 text-xs font-semibold text-slate-700 cursor-pointer">
                                    <input type="radio" v-model="form.jenis_kelamin" value="P" class="text-amber-600 focus:ring-amber-500" />
                                    <span>Perempuan (Wanita)</span>
                                </label>
                            </div>
                        </div>

                        <!-- Tempat Lahir -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                Tempat Lahir
                            </label>
                            <input
                                v-model="form.tempat_lahir"
                                type="text"
                                placeholder="Kota / Kabupaten Kelahiran"
                                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs text-slate-800 focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                            />
                        </div>

                        <!-- Tanggal Lahir + Live Age Badge -->
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="block text-xs font-bold text-slate-700">
                                    Tanggal Lahir
                                </label>
                                <span v-if="calculatedAge !== null" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 text-[11px] font-extrabold border border-amber-300">
                                    <i class="fa-solid fa-cake-candles text-[10px]"></i>
                                    <span>{{ calculatedAge }} Tahun</span>
                                </span>
                            </div>
                            <input
                                v-model="form.tanggal_lahir"
                                type="date"
                                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs text-slate-800 focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                            />
                        </div>

                        <!-- Hubungan Keluarga (Select2) -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                Hubungan dalam Keluarga
                            </label>
                            <SearchableSelect
                                v-model="form.hubungan_keluarga"
                                :options="hubunganKeluargaOptions"
                                valueKey="id"
                                labelKey="name"
                                placeholder="Pilih hubungan..."
                                searchPlaceholder="Cari kedudukan..."
                                icon="fa-people-roof"
                                iconColor="text-blue-600"
                            />
                        </div>

                        <!-- Golongan Darah (Select2) -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                Golongan Darah
                            </label>
                            <SearchableSelect
                                v-model="form.golongan_darah"
                                :options="golonganDarahOptions"
                                valueKey="id"
                                labelKey="name"
                                placeholder="Pilih golongan darah..."
                                searchPlaceholder="Cari gol. darah..."
                                icon="fa-droplet"
                                iconColor="text-rose-600"
                            />
                        </div>

                        <!-- Agama Asal (Select2) -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                Agama Asal
                            </label>
                            <SearchableSelect
                                v-model="form.agama_asal"
                                :options="agamaAsalOptions"
                                valueKey="id"
                                labelKey="name"
                                placeholder="Pilih agama asal..."
                                searchPlaceholder="Cari agama..."
                                icon="fa-hands-praying"
                                iconColor="text-amber-600"
                            />
                        </div>
                    </div>
                </div>

                <!-- TAB 2: SOSIAL, PROFESI -->
                <div v-show="activeTab === 'sosial'" class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-xs space-y-6">
                    <div class="border-b border-slate-100 pb-4">
                        <h3 class="text-base font-black text-slate-900 flex items-center gap-2">
                            <i class="fa-solid fa-graduation-cap text-amber-600"></i>
                            <span>Sosial, Profesi</span>
                        </h3>
                        <p class="text-xs text-slate-500">Latar belakang pendidikan, profesi kerja, dan bidang pelayanan di gereja</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
                        <!-- Pendidikan Terakhir (Select2) -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                Pendidikan Terakhir
                            </label>
                            <SearchableSelect
                                v-model="form.pendidikan"
                                :options="pendidikanOptions"
                                valueKey="id"
                                labelKey="name"
                                placeholder="Pilih pendidikan..."
                                searchPlaceholder="Cari jenjang..."
                                icon="fa-user-graduate"
                                iconColor="text-indigo-600"
                            />
                        </div>

                        <!-- Pekerjaan (Select2) -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                Pekerjaan / Profesi
                            </label>
                            <SearchableSelect
                                v-model="form.pekerjaan"
                                :options="pekerjaanOptions"
                                valueKey="id"
                                labelKey="name"
                                placeholder="Pilih pekerjaan..."
                                searchPlaceholder="Cari profesi..."
                                icon="fa-briefcase"
                                iconColor="text-amber-600"
                            />
                        </div>

                        <!-- Disabilitas (Select2) -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                Kebutuhan Khusus / Disabilitas
                            </label>
                            <SearchableSelect
                                v-model="form.disabilitas"
                                :options="disabilitasOptions"
                                valueKey="id"
                                labelKey="name"
                                placeholder="Pilih kebutuhan khusus..."
                                searchPlaceholder="Cari opsi..."
                                icon="fa-wheelchair"
                                iconColor="text-teal-600"
                            />
                        </div>

                        <!-- Bidang Keahlian / Pelayanan Paroki -->
                        <div class="sm:col-span-2 lg:col-span-3 xl:col-span-4">
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                Bidang Keahlian / Pelayanan Paroki
                            </label>
                            <input
                                v-model="form.talenta"
                                type="text"
                                placeholder="Contoh: Organis, Lektor, Dirigen Koor, Pemazmur, Guru Katekis, Pengurus KUB, Tim Medsos"
                                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs text-slate-800 focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                            />
                        </div>

                        <!-- No HP / WA -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                Nomor Handphone / WhatsApp
                            </label>
                            <input
                                v-model="form.handphone"
                                type="text"
                                placeholder="08xxxxxxxxxx"
                                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs text-slate-800 focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                            />
                        </div>

                        <!-- Email -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                Alamat Email
                            </label>
                            <input
                                v-model="form.email"
                                type="email"
                                placeholder="nama@email.com"
                                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs text-slate-800 focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                            />
                        </div>
                    </div>
                </div>

                <!-- TAB 3: SAKRAMEN GEREJA -->
                <div v-show="activeTab === 'sakramen'" class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-xs space-y-6">
                    <div class="border-b border-slate-100 pb-4">
                        <h3 class="text-base font-black text-slate-900 flex items-center gap-2">
                            <i class="fa-solid fa-cross text-amber-600"></i>
                            <span>Catatan Penerimaan Sakramen Gereja Katolik</span>
                        </h3>
                        <p class="text-xs text-slate-500">Rekapitulasi nomor registrasi buku sakramen (Liber Baptizatorum / Confirmatorum / Matrimoniorum)</p>
                    </div>

                    <!-- 3.1 BAPTIS -->
                    <div class="p-5 rounded-2xl bg-amber-50/50 border border-amber-200/60 space-y-4">
                        <div class="flex items-center gap-2 text-sm font-black text-amber-950">
                            <i class="fa-solid fa-droplet text-amber-600"></i>
                            <span>Sakramen Baptis (Inisiasi Kristen Pertama)</span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 mb-1">Tanggal Baptis</label>
                                <input v-model="form.tgl_baptis" type="date" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs bg-white" />
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 mb-1">Paroki Baptis (Select2)</label>
                                <SearchableSelect
                                    v-model="form.paroki_baptis"
                                    :options="parokiOptions"
                                    valueKey="id"
                                    labelKey="name"
                                    placeholder="Pilih paroki baptis..."
                                    searchPlaceholder="Cari nama paroki..."
                                    icon="fa-church"
                                    iconColor="text-amber-600"
                                />
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 mb-1">Pastor Pembaptis (Select2)</label>
                                <SearchableSelect
                                    v-model="form.pastor_baptis"
                                    :options="pastorOptions"
                                    valueKey="id"
                                    labelKey="name"
                                    placeholder="Pilih pastor pembaptis..."
                                    searchPlaceholder="Cari nama pastor (RD/RP)..."
                                    icon="fa-user-tie"
                                    iconColor="text-purple-600"
                                />
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 mb-1">Nama Wali Baptis</label>
                                <input v-model="form.wali_baptis" type="text" placeholder="Nama Wali Baptis" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs bg-white" />
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 mb-1">Buku Baptis (Vol)</label>
                                <input v-model="form.buku_baptis_vol" type="text" placeholder="Vol / Jilid" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs bg-white" />
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 mb-1">Buku Baptis (Hal)</label>
                                <input v-model="form.buku_baptis_hal" type="text" placeholder="Halaman" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs bg-white" />
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 mb-1">Buku Baptis (No)</label>
                                <input v-model="form.buku_baptis_no" type="text" placeholder="Nomor Akta" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs bg-white" />
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 mb-1">Jenis Penerimaan</label>
                                <SearchableSelect
                                    v-model="form.jenis_penerimaan_baptis"
                                    :options="jenisPenerimaanBaptisOptions"
                                    valueKey="id"
                                    labelKey="name"
                                    placeholder="Pilih jenis penerimaan..."
                                    searchPlaceholder="Cari jenis..."
                                    icon="fa-certificate"
                                    iconColor="text-indigo-600"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- 3.2 KOMUNI & KRISMA -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <!-- Komuni Pertama -->
                        <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200 space-y-4">
                            <div class="flex items-center gap-2 text-sm font-black text-slate-900">
                                <i class="fa-solid fa-bread-slice text-amber-600"></i>
                                <span>Sakramen Ekaristi (Komuni Pertama)</span>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Tanggal Komuni 1</label>
                                    <input v-model="form.tgl_komuni_1" type="date" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs bg-white" />
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Paroki Komuni 1 (Select2)</label>
                                    <SearchableSelect
                                        v-model="form.paroki_komuni_1"
                                        :options="parokiOptions"
                                        valueKey="id"
                                        labelKey="name"
                                        placeholder="Pilih paroki..."
                                        searchPlaceholder="Cari paroki..."
                                        icon="fa-church"
                                        iconColor="text-amber-600"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Krisma / Penguatan -->
                        <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200 space-y-4">
                            <div class="flex items-center gap-2 text-sm font-black text-slate-900">
                                <i class="fa-solid fa-fire-flame-curved text-amber-600"></i>
                                <span>Sakramen Krisma (Penguatan)</span>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Tanggal Krisma</label>
                                    <input v-model="form.tgl_krisma" type="date" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs bg-white" />
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Paroki Krisma (Select2)</label>
                                    <SearchableSelect
                                        v-model="form.paroki_krisma"
                                        :options="parokiOptions"
                                        valueKey="id"
                                        labelKey="name"
                                        placeholder="Pilih paroki..."
                                        searchPlaceholder="Cari paroki..."
                                        icon="fa-church"
                                        iconColor="text-amber-600"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 3.3 SAKRAMEN PERKAWINAN -->
                    <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200 space-y-4">
                        <div class="flex items-center gap-2 text-sm font-black text-slate-900">
                            <i class="fa-solid fa-ring text-amber-600"></i>
                            <span>Sakramen Perkawinan Katolik (KHK 1055)</span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 mb-1">Tanggal Perkawinan</label>
                                <input v-model="form.tgl_perkawinan" type="date" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs bg-white" />
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 mb-1">Paroki Perkawinan (Select2)</label>
                                <SearchableSelect
                                    v-model="form.paroki_perkawinan"
                                    :options="parokiOptions"
                                    valueKey="id"
                                    labelKey="name"
                                    placeholder="Pilih paroki..."
                                    searchPlaceholder="Cari paroki..."
                                    icon="fa-church"
                                    iconColor="text-amber-600"
                                />
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 mb-1">Nama Pasangan</label>
                                <input v-model="form.nama_pasangan" type="text" placeholder="Nama Suami / Istri" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs bg-white" />
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 mb-1">Status Kanonik</label>
                                <select v-model="form.status_perkawinan_kanonik" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs bg-white">
                                    <option value="Katolik Organik">Katolik Organik</option>
                                    <option value="Beda Agama (Dispensasi)">Beda Agama (Dispensasi)</option>
                                    <option value="Beda Gereja (Izin)">Beda Gereja (Izin)</option>
                                    <option value="Belum Menikah">Belum Menikah</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB 4: STATUS PANGGILAN & HIDUP BAKTI -->
                <div v-show="activeTab === 'panggilan'" class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-xs space-y-6">
                    <div class="border-b border-slate-100 pb-4">
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-purple-100 text-purple-800 text-xs font-bold mb-2">
                            <i class="fa-solid fa-hands-praying"></i>
                            <span>Vokasi Khusus & Tarekat Religius</span>
                        </div>
                        <h3 class="text-base font-black text-slate-900">
                            Status Panggilan / Hidup Bakti (Imam, Biarawan, Biarawati)
                        </h3>
                        <p class="text-xs text-slate-500">Khusus bagi umat yang terpanggil menjalani hidup bakti dalam Ordo / Kongregasi / Dioses</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
                        <!-- Status Panggilan (Select2) -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                Status Panggilan
                            </label>
                            <SearchableSelect
                                v-model="form.status_panggilan"
                                :options="statusPanggilanOptions"
                                valueKey="id"
                                labelKey="name"
                                placeholder="Pilih status panggilan..."
                                searchPlaceholder="Cari status..."
                                icon="fa-hands-praying"
                                iconColor="text-purple-600"
                            />
                        </div>

                        <!-- Ordo / Kongregasi -->
                        <div v-if="form.status_panggilan !== 'Awam'">
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                Nama Ordo / Kongregasi / Dioses
                            </label>
                            <input
                                v-model="form.nama_ordo_kongregasi"
                                type="text"
                                placeholder="Contoh: SVD, CMF, OCD, CB, SDB, Keuskupan Agung Kupang"
                                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs text-slate-800 focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                            />
                        </div>

                        <!-- Tahap Panggilan (Select2) -->
                        <div v-if="form.status_panggilan !== 'Awam'">
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                Tahap Panggilan
                            </label>
                            <SearchableSelect
                                v-model="form.tahap_panggilan"
                                :options="tahapPanggilanOptions"
                                valueKey="id"
                                labelKey="name"
                                placeholder="Pilih tahap panggilan..."
                                searchPlaceholder="Cari tahap..."
                                icon="fa-layer-group"
                                iconColor="text-purple-600"
                            />
                        </div>

                        <!-- Tempat Tugas / Biara -->
                        <div v-if="form.status_panggilan !== 'Awam'" class="sm:col-span-2 lg:col-span-3 xl:col-span-4">
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                Tempat Tugas / Komunitas Biara Saat Ini
                            </label>
                            <input
                                v-model="form.tempat_tugas_biara"
                                type="text"
                                placeholder="Contoh: Komunitas Biara Claretian Kupang, Paroki St. Vinsensius Benlutu"
                                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs text-slate-800 focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                            />
                        </div>

                        <!-- Tanggal Tahbisan / Kaul -->
                        <div v-if="form.status_panggilan !== 'Awam'">
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                Tanggal Tahbisan / Pengikraran Kaul
                            </label>
                            <input
                                v-model="form.tgl_tahbisan_kaul"
                                type="date"
                                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs text-slate-800 focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                            />
                        </div>
                    </div>
                </div>

                <!-- TAB 5: WILAYAH GEREJANI & KUB -->
                <div v-show="activeTab === 'gerejani'" class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-xs space-y-6">
                    <div class="border-b border-slate-100 pb-4">
                        <h3 class="text-base font-black text-slate-900 flex items-center gap-2">
                            <i class="fa-solid fa-church text-amber-600"></i>
                            <span>Wilayah Pastoral & Komunitas Umat Basis</span>
                        </h3>
                        <p class="text-xs text-slate-500">Penetapan wilayah gerejani, stasi/kapela, dan kelompok KUB tempat umat berhimpun</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
                        <!-- Wilayah Pastoral (Select2) -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                Wilayah Pastoral
                            </label>
                            <SearchableSelect
                                v-model="form.wilayah_id"
                                :options="wilayahList"
                                valueKey="id"
                                labelKey="nama_wilayah"
                                placeholder="-- Pilih Wilayah Pastoral --"
                                searchPlaceholder="Cari wilayah..."
                                icon="fa-church"
                                iconColor="text-blue-600"
                            />
                        </div>

                        <!-- Stasi / Kapela (Select2) -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                Stasi / Kapela
                            </label>
                            <SearchableSelect
                                v-model="form.kapela_id"
                                :options="kapelaList"
                                valueKey="id"
                                labelKey="nama_kapela"
                                placeholder="-- Pusat Paroki / Tanpa Stasi --"
                                searchPlaceholder="Cari stasi/kapela..."
                                icon="fa-place-of-worship"
                                iconColor="text-indigo-600"
                            />
                        </div>

                        <!-- KUB (Select2) -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                Komunitas Umat Basis (KUB / KBG)
                            </label>
                            <SearchableSelect
                                v-model="form.kub_id"
                                :options="filteredKubs"
                                valueKey="id"
                                labelKey="nama_kub"
                                placeholder="-- Pilih KUB / KBG --"
                                searchPlaceholder="Cari KUB..."
                                icon="fa-people-group"
                                iconColor="text-teal-600"
                            />
                        </div>

                        <!-- Status Keaktifan Umat (Select2) -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                Status Keaktifan Umat
                            </label>
                            <SearchableSelect
                                v-model="form.status_umat"
                                :options="statusUmatOptions"
                                valueKey="id"
                                labelKey="name"
                                placeholder="Pilih status umat..."
                                searchPlaceholder="Cari status..."
                                icon="fa-circle-check"
                                iconColor="text-emerald-600"
                            />
                        </div>
                    </div>
                </div>

                <!-- 4. BOTTOM ACTION BUTTONS -->
                <div class="rounded-2xl bg-white border border-slate-200 p-5 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="text-xs text-slate-500">
                        Pastikan seluruh data identitas, sakramen, dan domisili umat telah terisi dengan benar.
                    </div>
                    <div class="flex items-center gap-3">
                        <Link
                            :href="`/${prefix}/umat`"
                            class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition"
                        >
                            Batal
                        </Link>
                        <button
                            type="submit"
                            :disabled="isSubmitting"
                            class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white text-xs font-black shadow-lg shadow-amber-500/25 transition flex items-center gap-2 cursor-pointer disabled:opacity-50"
                        >
                            <i class="fa-solid fa-floppy-disk"></i>
                            <span>{{ isSubmitting ? 'Menyimpan...' : (isEdit ? 'Simpan Perbarui' : 'Simpan Data Umat') }}</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
