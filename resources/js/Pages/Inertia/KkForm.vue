<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import SearchableSelect from '@/Components/SearchableSelect.vue';
import { triggerToast } from '@/composables/useRoleMenu';

const props = defineProps({
    role: { type: String, default: 'Super Admin' },
    prefix: { type: String, default: 'superadmin' },
    kkItem: { type: Object, default: () => null },
    isEdit: { type: Boolean, default: false },
    defaultNoKk: { type: String, default: '' },
    kodeKeuskupan: { type: String, default: '012' },
    kodeParoki: { type: String, default: '001' },
    nextKkNumber: { type: String, default: '001' },
    namaParoki: { type: String, default: 'Paroki St. Vinsensius a Paulo Benlutu' },
    namaKeuskupan: { type: String, default: 'Keuskupan Agung Kupang' },
    wilayahList: { type: Array, default: () => [] },
    lingkunganList: { type: Array, default: () => [] },
    kubList: { type: Array, default: () => [] },
    kapelaList: { type: Array, default: () => [] },
    parokiList: { type: Array, default: () => [] },
    pastorList: { type: Array, default: () => [] },
    provinsiList: { type: Array, default: () => [] },
    kabupatenList: { type: Array, default: () => [] },
    kecamatanList: { type: Array, default: () => [] },
    desaList: { type: Array, default: () => [] },
    civilRegion: { type: Object, default: () => ({}) },
});

const pekerjaanOptions = [
    { id: 'PNS / ASN', name: 'PNS / ASN' },
    { id: 'TNI / Polri', name: 'TNI / Polri' },
    { id: 'Karyawan Swasta', name: 'Karyawan Swasta' },
    { id: 'Wiraswasta / Pedagang', name: 'Wiraswasta / Pedagang' },
    { id: 'Petani / Pekebun', name: 'Petani / Pekebun' },
    { id: 'Peternak', name: 'Peternak' },
    { id: 'Nelayan', name: 'Nelayan' },
    { id: 'Guru / Dosen', name: 'Guru / Dosen' },
    { id: 'Tenaga Medis / Perawat / Dokter', name: 'Tenaga Medis / Perawat / Dokter' },
    { id: 'Tukang / Buruh Bangunan', name: 'Tukang / Buruh Bangunan' },
    { id: 'Pelajar / Mahasiswa', name: 'Pelajar / Mahasiswa' },
    { id: 'Ibu Rumah Tangga', name: 'Ibu Rumah Tangga' },
    { id: 'Pensiunan', name: 'Pensiunan' },
    { id: 'Belum / Tidak Bekerja', name: 'Belum / Tidak Bekerja' },
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

const golonganDarahOptions = [
    { id: 'A', name: 'A' },
    { id: 'B', name: 'B' },
    { id: 'AB', name: 'AB' },
    { id: 'O', name: 'O' },
    { id: 'Tidak Tahu', name: 'Tidak Tahu' },
];

const penghasilanOptions = [
    { id: '< Rp 1.000.000', name: '< Rp 1.000.000' },
    { id: 'Rp 1.000.000 - Rp 2.500.000', name: 'Rp 1.000.000 - Rp 2.500.000' },
    { id: 'Rp 2.500.000 - Rp 5.000.000', name: 'Rp 2.500.000 - Rp 5.000.000' },
    { id: 'Rp 5.000.000 - Rp 10.000.000', name: 'Rp 5.000.000 - Rp 10.000.000' },
    { id: '> Rp 10.000.000', name: '> Rp 10.000.000' },
];

const kepemilikanRumahOptions = [
    { id: 'Milik Sendiri', name: 'Milik Sendiri' },
    { id: 'Sewa / Kontrak', name: 'Sewa / Kontrak' },
    { id: 'Ikut Orang Tua / Menumpang', name: 'Ikut Orang Tua / Menumpang' },
    { id: 'Rumah Dinas / Pastoran / Biara', name: 'Rumah Dinas / Pastoran / Biara' },
];

const kategoriEkonomiOptions = [
    { id: 'Sejahtera / Mandiri', name: 'Sejahtera / Mandiri' },
    { id: 'Pra-Sejahtera / Membutuhkan Bantuan', name: 'Pra-Sejahtera / Membutuhkan Bantuan' },
    { id: 'Rentan Ekonomi', name: 'Rentan Ekonomi' },
    { id: 'Mampu', name: 'Mampu' },
];

const statusBaptisOptions = [
    { id: 'Sudah', name: 'Sudah Baptis' },
    { id: 'Belum', name: 'Belum / Katekumen' },
];

const jenisPenerimaanBaptisOptions = [
    { id: 'Baptis Bayi (Infantis)', name: 'Baptis Bayi (Infantis)' },
    { id: 'Baptis Dewasa (Adultus)', name: 'Baptis Dewasa (Adultus)' },
    { id: 'Receptio (Penerimaan)', name: 'Receptio (Penerimaan)' },
];

const disabilitasOptions = [
    { id: 'Tidak Ada', name: 'Tidak Ada' },
    { id: 'Rungu / Wicara', name: 'Rungu / Wicara' },
    { id: 'Netra', name: 'Netra' },
    { id: 'Daksa / Fisik', name: 'Daksa / Fisik' },
    { id: 'Mental', name: 'Mental' },
    { id: 'Lansia Perawatan', name: 'Lansia Perawatan' },
    { id: 'Lainnya', name: 'Lainnya' },
];

const hubunganKeluargaOptions = [
    { id: 'Kepala Keluarga', name: 'Kepala Keluarga' },
    { id: 'Istri', name: 'Istri' },
    { id: 'Suami', name: 'Suami' },
    { id: 'Anak Kandung', name: 'Anak Kandung' },
    { id: 'Anak Angkat', name: 'Anak Angkat' },
    { id: 'Orang Tua', name: 'Orang Tua' },
    { id: 'Mertua', name: 'Mertua' },
    { id: 'Menantu', name: 'Menantu' },
    { id: 'Cucu', name: 'Cucu' },
    { id: 'Lainnya', name: 'Lainnya' },
];

const sukuEtnisOptions = [
    { id: 'Timor / Dawan', name: 'Timor / Dawan' },
    { id: 'Rote', name: 'Rote' },
    { id: 'Sabu', name: 'Sabu' },
    { id: 'Flores / Manggarai', name: 'Flores / Manggarai' },
    { id: 'Sumba', name: 'Sumba' },
    { id: 'Jawa', name: 'Jawa' },
    { id: 'Tionghoa', name: 'Tionghoa' },
    { id: 'Lainnya', name: 'Lainnya' },
];

const agamaAsalOptions = [
    { id: 'Katolik sejak lahir', name: 'Katolik sejak lahir' },
    { id: 'Katekumen', name: 'Katekumen' },
    { id: 'Protestan', name: 'Protestan' },
    { id: 'Islam', name: 'Islam' },
    { id: 'Hindu', name: 'Hindu' },
    { id: 'Budha', name: 'Budha' },
    { id: 'Lainnya', name: 'Lainnya' },
];

const parokiSelectOptions = computed(() => {
    const list = (props.parokiList || []).map(p => ({
        id: p.nama_paroki || p.nama || p.id_paroki,
        name: p.nama_paroki || p.nama || p.id_paroki,
    }));
    if (props.namaParoki && !list.some(p => p.id === props.namaParoki)) {
        list.unshift({ id: props.namaParoki, name: props.namaParoki });
    }
    return list;
});

const pastorSelectOptions = computed(() => {
    return (props.pastorList || []).map(p => ({
        id: p.nama_pastor || p.name || p.id,
        name: p.nama_pastor ? `${p.nama_pastor} (${p.jabatan || 'Pastor'})` : (p.name || p.id),
    }));
});

const basePrefix = computed(() => `/${props.prefix}`);
const listUrl = computed(() => `${basePrefix.value}/kk-katolik`);

const activeTab = ref('identitas');

const normalizeText = (value) => String(value || '').trim().toLowerCase();

const calculateAge = (d) => {
    if (!d) return null;
    try {
        const birthDate = new Date(d);
        if (isNaN(birthDate.getTime())) return null;
        const today = new Date();
        let age = today.getFullYear() - birthDate.getFullYear();
        const m = today.getMonth() - birthDate.getMonth();
        if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
            age--;
        }
        return age >= 0 ? age : null;
    } catch {
        return null;
    }
};

const findByLabel = (items, labelKey, value) => {
    const text = normalizeText(value);
    if (!text) return null;
    return (items || []).find((item) => normalizeText(item?.[labelKey]) === text) || null;
};

const initialDesa = findByLabel(props.desaList, 'nama_desa', props.kkItem?.desa_kelurahan);
const initialKecamatan = findByLabel(props.kecamatanList, 'nama_kecamatan', props.kkItem?.kecamatan)
    || (initialDesa ? props.kecamatanList.find(k => String(k.id_kecamatan) === String(initialDesa.kecamatan_id)) : null);
const initialKabupaten = findByLabel(props.kabupatenList, 'nama_kabupaten', props.kkItem?.kota_kabupaten)
    || (initialKecamatan ? props.kabupatenList.find(k => String(k.id_kabupaten) === String(initialKecamatan.kabupaten_id)) : null);
const initialProvinsi = findByLabel(props.provinsiList, 'nama_provinsi', props.kkItem?.provinsi)
    || (initialKabupaten ? props.provinsiList.find(p => String(p.id_provinsi) === String(initialKabupaten.provinsi_id)) : null);

const initialProvinsiId = props.civilRegion?.provinsi_id || props.kkItem?.provinsi_id || initialProvinsi?.id_provinsi || '';
const initialKabupatenId = props.civilRegion?.kabupaten_id || props.kkItem?.kabupaten_id || initialKabupaten?.id_kabupaten || '';
const initialKecamatanId = props.civilRegion?.kecamatan_id || props.kkItem?.kecamatan_id || initialKecamatan?.id_kecamatan || '';
const initialDesaId = props.civilRegion?.desa_id || props.kkItem?.desa_id || initialDesa?.id_desa || '';

const form = useForm({
    no_kk_kw: props.kkItem?.no_kk_kw || props.defaultNoKk || '',
    no_kk_dukcapil: props.kkItem?.no_kk_dukcapil || '',
    nik_pemilik: props.kkItem?.nik_pemilik || '',
    nama_baptis_pemilik: props.kkItem?.nama_baptis_pemilik || '',
    nama_lahir_pemilik: props.kkItem?.nama_lahir_pemilik || '',
    nama_pasangan: props.kkItem?.nama_pasangan || '',
    wilayah_id: props.kkItem?.wilayah_id || '',
    lingkungan_id: props.kkItem?.lingkungan_id || '',
    kub_id: props.kkItem?.kub_id || '',
    kapela_id: props.kkItem?.kapela_id || '',
    paroki_id: props.kkItem?.paroki_id || '',
    gereja_paroki: props.kkItem?.gereja_paroki || 'Paroki St. Vinsensius a Paulo Benlutu',
    lokasi_gereja: props.kkItem?.lokasi_gereja || '',
    alamat_sekarang: props.kkItem?.alamat_sekarang || '',
    rt: props.kkItem?.rt || '',
    rw: props.kkItem?.rw || '',
    provinsi_id: initialProvinsiId,
    kabupaten_id: initialKabupatenId,
    kecamatan_id: initialKecamatanId,
    desa_id: initialDesaId,
    provinsi: props.civilRegion?.provinsi || props.kkItem?.provinsi || '',
    desa_kelurahan: props.civilRegion?.desa_kelurahan || props.kkItem?.desa_kelurahan || '',
    kecamatan: props.civilRegion?.kecamatan || props.kkItem?.kecamatan || '',
    kota_kabupaten: props.civilRegion?.kota_kabupaten || props.kkItem?.kota_kabupaten || '',
    handphone: props.kkItem?.handphone || '',
    email: props.kkItem?.email || '',
    status_kepemilikan_rumah: props.kkItem?.status_kepemilikan_rumah || 'Milik Sendiri',
    kategori_ekonomi: props.kkItem?.kategori_ekonomi || 'Sejahtera / Mandiri',
    bantuan_pastoral: props.kkItem?.bantuan_pastoral || '',
    pekerjaan: props.kkItem?.pekerjaan || '',
    pendidikan: props.kkItem?.pendidikan || '',
    golongan_darah: props.kkItem?.golongan_darah || '',
    penghasilan: props.kkItem?.penghasilan || '',
    status_kk: props.kkItem?.status_kk || 'Aktif',
    status_verifikasi: props.kkItem?.status_verifikasi || 'Terverifikasi',
    anggota: Array.isArray(props.kkItem?.anggota) ? props.kkItem.anggota.map(a => ({ ...a })) : [],
});

const localDesaList = ref([...(props.desaList || [])]);
let desaRequestSeq = 0;

// Cascading KUB options based on Wilayah
const filteredKubList = computed(() => {
    if (!form.wilayah_id) return props.kubList;
    return props.kubList.filter(k => String(k.wilayah_id) === String(form.wilayah_id));
});

const filteredKabupatenList = computed(() => {
    if (!form.provinsi_id) return props.kabupatenList;
    return props.kabupatenList.filter(k => String(k.provinsi_id) === String(form.provinsi_id));
});

const filteredKecamatanList = computed(() => {
    if (!form.kabupaten_id) return props.kecamatanList;
    return props.kecamatanList.filter(k => String(k.kabupaten_id) === String(form.kabupaten_id));
});

const filteredDesaList = computed(() => {
    if (!form.kecamatan_id) return [];
    return localDesaList.value.filter(d => String(d.kecamatan_id) === String(form.kecamatan_id));
});

const selectedProvinsi = computed(() => props.provinsiList.find(p => String(p.id_provinsi) === String(form.provinsi_id)) || null);
const selectedKabupaten = computed(() => props.kabupatenList.find(k => String(k.id_kabupaten) === String(form.kabupaten_id)) || null);
const selectedKecamatan = computed(() => props.kecamatanList.find(k => String(k.id_kecamatan) === String(form.kecamatan_id)) || null);
const selectedDesa = computed(() => localDesaList.value.find(d => String(d.id_desa) === String(form.desa_id)) || null);

const fetchDesaOptions = async (kecamatanId) => {
    const seq = ++desaRequestSeq;
    if (!kecamatanId) {
        localDesaList.value = [];
        return;
    }

    try {
        const response = await fetch(`${basePrefix.value}/wilayah-sipil/desa-kelurahan?kecamatan_id=${encodeURIComponent(kecamatanId)}`, {
            headers: {
                Accept: 'application/json',
            },
        });
        if (!response.ok) return;
        const payload = await response.json();
        if (seq !== desaRequestSeq) return;
        localDesaList.value = Array.isArray(payload.data) ? payload.data : [];
        if (form.desa_id && !localDesaList.value.some(d => String(d.id_desa) === String(form.desa_id))) {
            form.desa_id = '';
        }
        syncWilayahSipilText();
    } catch (error) {
        console.error('Gagal memuat desa/kelurahan:', error);
    }
};

const syncWilayahSipilText = () => {
    form.provinsi = selectedProvinsi.value?.nama_provinsi || '';
    form.kota_kabupaten = selectedKabupaten.value?.nama_kabupaten || '';
    form.kecamatan = selectedKecamatan.value?.nama_kecamatan || '';
    form.desa_kelurahan = selectedDesa.value?.nama_desa || '';
};

// Auto-generate No KK Paroki otomatis standar: K + Kode Keuskupan (012) + Kode Paroki (001) + Nomor Urut (001)
const generateNoKkParoki = (force = false) => {
    if (props.isEdit && !force) return;
    if (form.no_kk_kw && !force) return;

    if (props.defaultNoKk) {
        form.no_kk_kw = props.defaultNoKk;
        return;
    }
    const keuskupan = String(props.kodeKeuskupan || '012').padStart(3, '0');
    const paroki = String(props.kodeParoki || '001').padStart(3, '0');
    const seq = String(props.nextKkNumber || '001').padStart(3, '0');
    form.no_kk_kw = `K${keuskupan}${paroki}${seq}`;
};

onMounted(() => {
    if (!props.isEdit && !form.no_kk_kw) {
        generateNoKkParoki(true);
    }
});

// Mutual exclusion: Memilih Stasi menonaktifkan Wilayah, dan sebaliknya
watch(() => form.kapela_id, (newVal) => {
    if (newVal) {
        form.wilayah_id = '';
    }
});

watch(() => form.wilayah_id, (newVal) => {
    if (newVal) {
        form.kapela_id = '';
    }
    if (newVal && form.kub_id) {
        const isValid = filteredKubList.value.some(k => String(k.id) === String(form.kub_id));
        if (!isValid) form.kub_id = '';
    }
    if (!props.isEdit || !form.no_kk_kw) {
        generateNoKkParoki(false);
    }
});

watch(() => form.kub_id, () => {
    if (!props.isEdit || !form.no_kk_kw) {
        generateNoKkParoki(false);
    }
});

watch(() => form.provinsi_id, () => {
    if (!form.provinsi_id) {
        form.kabupaten_id = '';
        form.kecamatan_id = '';
        form.desa_id = '';
    } else if (form.kabupaten_id && !filteredKabupatenList.value.some(k => String(k.id_kabupaten) === String(form.kabupaten_id))) {
        form.kabupaten_id = '';
    }
    syncWilayahSipilText();
});

watch(() => form.kabupaten_id, () => {
    if (!form.kabupaten_id) {
        form.kecamatan_id = '';
        form.desa_id = '';
    } else if (form.kecamatan_id && !filteredKecamatanList.value.some(k => String(k.id_kecamatan) === String(form.kecamatan_id))) {
        form.kecamatan_id = '';
    }
    syncWilayahSipilText();
});

watch(() => form.kecamatan_id, (newVal) => {
    if (!form.kecamatan_id) {
        form.desa_id = '';
        localDesaList.value = [];
    } else if (form.desa_id && !filteredDesaList.value.some(d => String(d.id_desa) === String(form.desa_id))) {
        form.desa_id = '';
    }
    syncWilayahSipilText();
    fetchDesaOptions(newVal);
});

watch(() => form.desa_id, syncWilayahSipilText);

onMounted(() => {
    if (!props.isEdit && !form.no_kk_kw) {
        generateNoKkParoki(false);
    }
    if (form.kecamatan_id && localDesaList.value.length === 0) {
        fetchDesaOptions(form.kecamatan_id);
    }
    syncWilayahSipilText();
});

// Add dynamic family member
const addAnggota = () => {
    form.anggota.push({
        kode_anggota: '',
        nama_baptis: '',
        nama_lengkap: '',
        nik: '',
        hubungan_keluarga: form.anggota.length === 0 ? 'Kepala Keluarga' : (form.anggota.length === 1 ? 'Istri' : 'Anak Kandung'),
        jenis_kelamin: 'Laki-Laki',
        tempat_lahir: '',
        tanggal_lahir: '',
        suku_etnis: 'Timor / Dawan',
        golongan_darah: 'Tidak Tahu',
        agama_asal: 'Katolik sejak lahir',
        pekerjaan: 'Petani / Peternak',
        pendidikan: 'SMA / SMK',
        talenta: '',
        disabilitas: 'Tidak Ada',
        status_baptis: 'Sudah',
        jenis_penerimaan_baptis: 'Baptis Bayi (Infantis)',
        tgl_baptis: '',
        paroki_baptis: 'Paroki St. Vinsensius a Paulo Benlutu',
        pastor_baptis: '',
        wali_baptis: '',
        buku_baptis_vol: '',
        buku_baptis_hal: '',
        buku_baptis_no: '',
        tgl_komuni_1: '',
        paroki_komuni_1: '',
        tgl_krisma: '',
        paroki_krisma: '',
        tgl_perkawinan: '',
        paroki_perkawinan: '',
        nama_pasangan: '',
        status_perkawinan: 'Katolik Organik',
        peristiwa_lain: '',
        no_surat_peristiwa: '',
        status_panggilan: 'Awam',
        nama_ordo_kongregasi: '',
        tahap_panggilan: '',
        tempat_tugas_biara: '',
        tgl_tahbisan_kaul: '',
    });
};

const removeAnggota = (index) => {
    form.anggota.splice(index, 1);
};

const submitForm = () => {
    if (!form.no_kk_kw) {
        activeTab.value = 'identitas';
        triggerToast('Nomor KK Paroki wajib diisi.', 'error');
        return;
    }
    if (!form.nik_pemilik || !form.nama_baptis_pemilik || !form.nama_lahir_pemilik || !form.handphone) {
        activeTab.value = 'kepala';
        triggerToast('Silakan lengkapi data NIK, Nama Baptis, Nama Lahir, dan Kontak Kepala Keluarga.', 'error');
        return;
    }
    if (!form.alamat_sekarang) {
        activeTab.value = 'domisili';
        triggerToast('Alamat domisili lengkap wajib diisi.', 'error');
        return;
    }

    if (props.isEdit && props.kkItem?.id) {
        form.put(`${basePrefix.value}/kk-katolik/${props.kkItem.id}`);
    } else {
        form.post(`${basePrefix.value}/kk-katolik`);
    }
};
</script>

<template>
    <AppLayout :title="isEdit ? 'Edit Data KK Katolik' : 'Tambah Kartu Keluarga (KK) Katolik'" :fullWidth="true">
        <Head :title="`${isEdit ? 'Edit Data KK Katolik' : 'Form Tambah KK Katolik Baru'} - SIPAROKI`" />

        <div class="w-full space-y-3 pb-2">
            <!-- Header Banner -->
            <div class="rounded-2xl bg-white border border-slate-200/80 p-4 sm:p-5 shadow-2xs">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <Link
                            :href="`${basePrefix}/kk-katolik`"
                            class="w-10 h-10 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center transition cursor-pointer shrink-0"
                            title="Kembali ke Daftar KK"
                        >
                            <i class="fa-solid fa-arrow-left text-sm"></i>
                        </Link>
                        <div>
                            <div class="flex items-center gap-2">
                                <h1 class="text-lg font-black text-slate-900 tracking-tight">
                                    {{ isEdit ? 'Edit Data Kartu Keluarga (KK) Katolik' : 'Form Tambah Kartu Keluarga (KK) Katolik Baru' }}
                                </h1>
                                <span
                                    class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border"
                                    :class="isEdit ? 'bg-amber-50 text-amber-800 border-amber-200' : 'bg-blue-50 text-blue-800 border-blue-200'"
                                >
                                    {{ isEdit ? 'Mode Edit' : 'Data Master Baru' }}
                                </span>
                            </div>
                            <p class="text-xs text-slate-500 mt-0.5">
                                Formulir Pendataan Keluarga & Umat Katolik (SIPAROKI) Paroki St. Vinsensius a Paulo - Benlutu
                            </p>
                        </div>
                    </div>

                    <!-- Top Action Buttons -->
                    <div class="flex items-center gap-2 shrink-0">
                        <Link
                            :href="`${basePrefix}/kk-katolik`"
                            class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition flex items-center gap-1.5"
                        >
                            <i class="fa-solid fa-xmark text-xs"></i>
                            <span>Batal</span>
                        </Link>
                        <button
                            type="button"
                            :disabled="form.processing"
                            @click="submitForm"
                            class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-sm shadow-blue-500/25 transition flex items-center gap-1.5 cursor-pointer disabled:opacity-60"
                        >
                            <i v-if="form.processing" class="fa-solid fa-circle-notch fa-spin text-xs"></i>
                            <i v-else class="fa-solid fa-floppy-disk text-xs"></i>
                            <span>{{ isEdit ? 'Simpan Perubahan' : 'Simpan Data KK' }}</span>
                        </button>
                    </div>
                </div>

                <!-- Navigation Tabs -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 pt-4 mt-4 border-t border-slate-100">
                    <button
                        type="button"
                        @click="activeTab = 'identitas'"
                        class="px-3.5 py-2 rounded-xl text-xs font-bold transition flex items-center justify-center gap-2 whitespace-nowrap cursor-pointer"
                        :class="activeTab === 'identitas' ? 'bg-blue-600 text-white shadow-xs' : 'bg-slate-50 hover:bg-slate-100 text-slate-700 border border-slate-200/80'"
                    >
                        <i class="fa-solid fa-church"></i>
                        <span>A. Hirarki & Dokumen KK</span>
                    </button>
                    <button
                        type="button"
                        @click="activeTab = 'kepala'"
                        class="px-3.5 py-2 rounded-xl text-xs font-bold transition flex items-center justify-center gap-2 whitespace-nowrap cursor-pointer"
                        :class="activeTab === 'kepala' ? 'bg-blue-600 text-white shadow-xs' : 'bg-slate-50 hover:bg-slate-100 text-slate-700 border border-slate-200/80'"
                    >
                        <i class="fa-solid fa-user-tie"></i>
                        <span>B. Kepala Keluarga & Domisili</span>
                    </button>
                    <button
                        type="button"
                        @click="activeTab = 'domisili'"
                        class="px-3.5 py-2 rounded-xl text-xs font-bold transition flex items-center justify-center gap-2 whitespace-nowrap cursor-pointer"
                        :class="activeTab === 'domisili' ? 'bg-blue-600 text-white shadow-xs' : 'bg-slate-50 hover:bg-slate-100 text-slate-700 border border-slate-200/80'"
                    >
                        <i class="fa-solid fa-map-location-dot"></i>
                        <span>C. Alamat & Wilayah Sipil</span>
                    </button>
                    <button
                        type="button"
                        @click="activeTab = 'anggota'"
                        class="px-3.5 py-2 rounded-xl text-xs font-bold transition flex items-center justify-center gap-2 whitespace-nowrap cursor-pointer"
                        :class="activeTab === 'anggota' ? 'bg-blue-600 text-white shadow-xs' : 'bg-slate-50 hover:bg-slate-100 text-slate-700 border border-slate-200/80'"
                    >
                        <i class="fa-solid fa-users"></i>
                        <span>D. Anggota Jiwa ({{ form.anggota.length }})</span>
                    </button>
                </div>
            </div>

            <!-- Form Container -->
            <form @submit.prevent="submitForm" novalidate class="space-y-3">
                <!-- TAB 1: HIRARKI PASTORAL & DOKUMEN KK -->
                <div v-show="activeTab === 'identitas'" class="rounded-2xl bg-white border border-slate-200/80 p-5 shadow-2xs space-y-5">
                    <div class="border-b border-slate-100 pb-3">
                        <h2 class="text-sm font-black text-slate-900 flex items-center gap-2">
                            <i class="fa-solid fa-sitemap text-blue-600"></i>
                            <span>A. Hirarki Pastoral, Teritorial & Dokumen Kartu Keluarga</span>
                        </h2>
                        <p class="text-xs text-slate-500">Penetapan wilayah gerejani paroki dan nomor registrasi kartu keluarga.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <!-- Stasi / Kapela -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5 flex items-center justify-between">
                                <span>Stasi / Kapela</span>
                                <span v-if="form.wilayah_id" class="text-[10px] text-amber-600 font-semibold bg-amber-50 px-1.5 py-0.5 rounded">Nonaktif</span>
                            </label>
                            <SearchableSelect
                                v-model="form.kapela_id"
                                :options="kapelaList"
                                :disabled="!!form.wilayah_id"
                                valueKey="id"
                                labelKey="nama_kapela"
                                :placeholder="form.wilayah_id ? '-- Nonaktif (Wilayah Dipilih) --' : '-- Pilih Stasi / Kapela --'"
                                searchPlaceholder="Cari stasi / kapela..."
                                icon="fa-solid fa-church"
                                iconColor="text-blue-600"
                            />
                        </div>

                        <!-- Wilayah Pastoral -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5 flex items-center justify-between">
                                <span>Wilayah Pastoral</span>
                                <span v-if="form.kapela_id" class="text-[10px] text-blue-600 font-semibold bg-blue-50 px-1.5 py-0.5 rounded">Nonaktif</span>
                            </label>
                            <SearchableSelect
                                v-model="form.wilayah_id"
                                :options="wilayahList"
                                :disabled="!!form.kapela_id"
                                valueKey="id"
                                labelKey="nama_wilayah"
                                :placeholder="form.kapela_id ? '-- Nonaktif (Stasi Dipilih) --' : '-- Pilih Wilayah Pastoral --'"
                                searchPlaceholder="Cari wilayah pastoral..."
                                icon="fa-solid fa-map"
                                iconColor="text-amber-600"
                            />
                            <span v-if="form.errors.wilayah_id" class="text-rose-500 text-xs font-semibold mt-0.5 block">{{ form.errors.wilayah_id }}</span>
                        </div>

                        <!-- KUB / KBG -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                KUB / KBG
                            </label>
                            <SearchableSelect
                                v-model="form.kub_id"
                                :options="filteredKubList"
                                valueKey="id"
                                labelKey="nama_kub"
                                placeholder="-- Pilih KUB / KBG --"
                                searchPlaceholder="Cari KUB / KBG..."
                                icon="fa-solid fa-people-group"
                                iconColor="text-emerald-600"
                            />
                        </div>

                        <!-- No KK Paroki (K + Kode Keuskupan + Kode Paroki + No Urut) -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                No. KK Paroki <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <input
                                    v-model="form.no_kk_kw"
                                    type="text"
                                    placeholder="Contoh: K012001001"
                                    class="w-full pl-8 pr-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:bg-white font-mono font-bold transition tracking-wider"
                                />
                                <i class="fa-solid fa-fingerprint absolute left-2.5 top-1/2 -translate-y-1/2 text-blue-500 text-xs pointer-events-none"></i>
                            </div>
                            <span v-if="form.errors.no_kk_kw" class="text-rose-500 text-xs font-semibold mt-0.5 block">{{ form.errors.no_kk_kw }}</span>
                        </div>

                        <!-- No KK Sipil Dukcapil -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                No. KK Sipil (16 Digit Dukcapil)
                            </label>
                            <input
                                v-model="form.no_kk_dukcapil"
                                type="text"
                                maxlength="16"
                                placeholder="16 digit nomor KK Dukcapil..."
                                class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:bg-white font-medium transition"
                            />
                            <span v-if="form.errors.no_kk_dukcapil" class="text-rose-500 text-xs font-semibold mt-0.5 block">{{ form.errors.no_kk_dukcapil }}</span>
                        </div>

                        <!-- Status KK -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                Status KK
                            </label>
                            <select
                                v-model="form.status_kk"
                                class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-blue-500 focus:bg-white font-medium transition cursor-pointer"
                            >
                                <option value="Aktif">Aktif (Tinggal & Terdaftar)</option>
                                <option value="Pindah KUB">Pindah KUB</option>
                                <option value="Pindah Wilayah">Pindah Wilayah</option>
                                <option value="Pindah Paroki">Pindah Paroki</option>
                                <option value="Pecah KK">Pecah KK</option>
                                <option value="Tidak Aktif">Tidak Aktif</option>
                            </select>
                        </div>

                        <!-- Status Verifikasi -->
                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                Status Verifikasi Data
                            </label>
                            <select
                                v-model="form.status_verifikasi"
                                class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-blue-500 focus:bg-white font-medium transition cursor-pointer"
                            >
                                <option value="Terverifikasi">Terverifikasi (Valid oleh Paroki / KUB)</option>
                                <option value="Belum">Belum Diverifikasi (Draft / Pengajuan)</option>
                                <option value="Ditolak">Ditolak / Data Tidak Sesuai</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- TAB 2: KEPALA KELUARGA & DOMISILI -->
                <div v-show="activeTab === 'kepala'" class="rounded-2xl bg-white border border-slate-200/80 p-5 shadow-2xs space-y-4">
                    <div class="border-b border-slate-100 pb-3">
                        <h2 class="text-sm font-black text-slate-900 flex items-center gap-2">
                            <i class="fa-solid fa-user-tie text-blue-600"></i>
                            <span>Data Kepala Keluarga & Pasangan</span>
                        </h2>
                        <p class="text-xs text-slate-500">Nama lengkap baptis dan sipil penanggung jawab keluarga.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- NIK Kepala Keluarga -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                NIK Kepala Keluarga <span class="text-rose-500">*</span>
                            </label>
                            <input
                                v-model="form.nik_pemilik"
                                type="text"
                                maxlength="16"
                                placeholder="Masukkan 16 digit NIK kepala keluarga..."
                                class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:bg-white font-medium transition"
                            />
                            <span v-if="form.errors.nik_pemilik" class="text-rose-500 text-xs font-semibold mt-0.5 block">{{ form.errors.nik_pemilik }}</span>
                        </div>

                        <!-- Kontak / No HP -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                Kontak / No. HP / WhatsApp <span class="text-rose-500">*</span>
                            </label>
                            <input
                                v-model="form.handphone"
                                type="text"
                                placeholder="Masukkan kontak / hp... (contoh: 081234567890)"
                                class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:bg-white font-medium transition"
                            />
                        </div>

                        <!-- Nama Baptis Kepala Keluarga -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                Nama Baptis Kepala Keluarga <span class="text-rose-500">*</span>
                            </label>
                            <input
                                v-model="form.nama_baptis_pemilik"
                                type="text"
                                placeholder="Masukkan nama baptis... (contoh: Yohanes, Fransiskus)"
                                class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:bg-white font-medium transition"
                            />
                        </div>

                        <!-- Nama Lahir Kepala Keluarga -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                Nama Lahir Kepala Keluarga <span class="text-rose-500">*</span>
                            </label>
                            <input
                                v-model="form.nama_lahir_pemilik"
                                type="text"
                                placeholder="Masukkan nama lahir kepala keluarga..."
                                class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:bg-white font-medium transition"
                            />
                        </div>

                        <!-- Nama Pasangan -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                Nama Pasangan (Istri / Suami)
                            </label>
                            <input
                                v-model="form.nama_pasangan"
                                type="text"
                                placeholder="Masukkan nama lengkap pasangan..."
                                class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:bg-white font-medium transition"
                            />
                        </div>

                        <!-- Email Keluarga -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                Email Keluarga
                            </label>
                            <input
                                v-model="form.email"
                                type="email"
                                placeholder="contoh: keluarga@gmail.com"
                                class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:bg-white font-medium transition"
                            />
                        </div>

                        <!-- Pekerjaan Kepala Keluarga -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                Pekerjaan / Profesi Kepala Keluarga
                            </label>
                            <SearchableSelect
                                v-model="form.pekerjaan"
                                :options="pekerjaanOptions"
                                value-key="id"
                                label-key="name"
                                placeholder="-- Pilih Pekerjaan --"
                                search-placeholder="Ketik cari pekerjaan..."
                                icon="fa-briefcase"
                                icon-color="text-emerald-600"
                            />
                        </div>

                        <!-- Pendidikan Terakhir -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                Pendidikan Terakhir
                            </label>
                            <SearchableSelect
                                v-model="form.pendidikan"
                                :options="pendidikanOptions"
                                value-key="id"
                                label-key="name"
                                placeholder="-- Pilih Pendidikan --"
                                search-placeholder="Ketik cari jenjang pendidikan..."
                                icon="fa-graduation-cap"
                                icon-color="text-blue-600"
                            />
                        </div>

                        <!-- Golongan Darah -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                Golongan Darah
                            </label>
                            <SearchableSelect
                                v-model="form.golongan_darah"
                                :options="golonganDarahOptions"
                                value-key="id"
                                label-key="name"
                                placeholder="-- Pilih Golongan Darah --"
                                search-placeholder="Ketik cari golongan darah..."
                                icon="fa-droplet"
                                icon-color="text-rose-600"
                            />
                        </div>

                        <!-- Penghasilan / Ekonomi -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                Penghasilan / Ekonomi
                            </label>
                            <SearchableSelect
                                v-model="form.penghasilan"
                                :options="penghasilanOptions"
                                value-key="id"
                                label-key="name"
                                placeholder="-- Pilih Rentang Penghasilan --"
                                search-placeholder="Ketik cari rentang penghasilan..."
                                icon="fa-money-bill-wave"
                                icon-color="text-amber-600"
                            />
                        </div>

                        <!-- Status Kepemilikan Rumah -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                Status Kepemilikan Rumah
                            </label>
                            <SearchableSelect
                                v-model="form.status_kepemilikan_rumah"
                                :options="kepemilikanRumahOptions"
                                value-key="id"
                                label-key="name"
                                placeholder="-- Pilih Status Kepemilikan Rumah --"
                                search-placeholder="Ketik cari status rumah..."
                                icon="fa-house"
                                icon-color="text-indigo-600"
                            />
                        </div>

                        <!-- Kategori Ekonomi Pastoral -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                Kategori Ekonomi Pastoral
                            </label>
                            <SearchableSelect
                                v-model="form.kategori_ekonomi"
                                :options="kategoriEkonomiOptions"
                                value-key="id"
                                label-key="name"
                                placeholder="-- Pilih Kategori Ekonomi --"
                                search-placeholder="Ketik cari kategori ekonomi..."
                                icon="fa-hand-holding-heart"
                                icon-color="text-teal-600"
                            />
                        </div>

                        <!-- Bantuan Pastoral Khusus -->
                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                Bantuan Pastoral Khusus (Opsional)
                            </label>
                            <input
                                v-model="form.bantuan_pastoral"
                                type="text"
                                placeholder="Catatan program bantuan pastoral atau kebutuhan asistensi khusus keluarga..."
                                class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:bg-white font-medium transition"
                            />
                        </div>
                    </div>
                </div>

                <!-- TAB 4: DOMISILI & WILAYAH SIPIL -->
                <div v-show="activeTab === 'domisili'" class="rounded-2xl bg-white border border-slate-200/80 p-5 shadow-2xs space-y-4">
                    <div class="border-b border-slate-100 pb-3">
                        <h2 class="text-sm font-black text-slate-900 flex items-center gap-2">
                            <i class="fa-solid fa-map-location-dot text-blue-600"></i>
                            <span>Alamat Domisili & Wilayah Sipil</span>
                        </h2>
                        <p class="text-xs text-slate-500">Lokasi tempat tinggal tetap kartu keluarga saat ini.</p>
                    </div>

                    <div class="space-y-4">
                        <!-- Alamat Lengkap -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                Alamat Domisili Lengkap <span class="text-rose-500">*</span>
                            </label>
                            <textarea
                                v-model="form.alamat_sekarang"
                                rows="2"
                                placeholder="Masukkan alamat domisili lengkap... (nama jalan, nomor rumah, patokan)"
                                class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:bg-white font-medium transition resize-none"
                            ></textarea>
                        </div>

                        <div class="grid grid-cols-2 md:grid-cols-6 gap-3">
                            <!-- RT -->
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">RT</label>
                                <input
                                    v-model="form.rt"
                                    type="text"
                                    placeholder="001"
                                    class="w-full px-3 py-1.5 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-blue-500 font-medium"
                                />
                            </div>

                            <!-- RW -->
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">RW</label>
                                <input
                                    v-model="form.rw"
                                    type="text"
                                    placeholder="002"
                                    class="w-full px-3 py-1.5 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-blue-500 font-medium"
                                />
                            </div>

                            <!-- Desa / Kelurahan -->
                            <div class="col-span-2">
                                <label class="block text-xs font-bold text-slate-700 mb-1">Desa / Kelurahan</label>
                                <SearchableSelect
                                    v-model="form.desa_id"
                                    :options="filteredDesaList"
                                    value-key="id_desa"
                                    label-key="nama_desa"
                                    placeholder="Pilih desa / kelurahan..."
                                    search-placeholder="Ketik cari desa / kelurahan..."
                                    icon="fa-map-pin"
                                    icon-color="text-emerald-600"
                                    :disabled="!form.kecamatan_id"
                                />
                            </div>

                            <!-- Kecamatan -->
                            <div class="col-span-2">
                                <label class="block text-xs font-bold text-slate-700 mb-1">Kecamatan</label>
                                <SearchableSelect
                                    v-model="form.kecamatan_id"
                                    :options="filteredKecamatanList"
                                    value-key="id_kecamatan"
                                    label-key="nama_kecamatan"
                                    placeholder="Pilih kecamatan..."
                                    search-placeholder="Ketik cari kecamatan..."
                                    icon="fa-location-dot"
                                    icon-color="text-teal-600"
                                    :disabled="!form.kabupaten_id"
                                />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Kabupaten / Kota -->
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Kabupaten / Kota</label>
                                <SearchableSelect
                                    v-model="form.kabupaten_id"
                                    :options="filteredKabupatenList"
                                    value-key="id_kabupaten"
                                    label-key="nama_kabupaten"
                                    placeholder="Pilih kabupaten / kota..."
                                    search-placeholder="Ketik cari kabupaten / kota..."
                                    icon="fa-city"
                                    icon-color="text-sky-600"
                                    :disabled="!form.provinsi_id"
                                />
                            </div>

                            <!-- Provinsi -->
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Provinsi</label>
                                <SearchableSelect
                                    v-model="form.provinsi_id"
                                    :options="provinsiList"
                                    value-key="id_provinsi"
                                    label-key="nama_provinsi"
                                    placeholder="Pilih provinsi..."
                                    search-placeholder="Ketik cari provinsi..."
                                    icon="fa-map-location-dot"
                                    icon-color="text-indigo-600"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB 5: ANGGOTA KELUARGA -->
                <div v-show="activeTab === 'anggota'" class="rounded-2xl bg-white border border-slate-200/80 p-5 shadow-2xs space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <div>
                            <h2 class="text-sm font-black text-slate-900 flex items-center gap-2">
                                <i class="fa-solid fa-users text-blue-600"></i>
                                <span>Daftar Anggota Keluarga (Umat / Jiwa)</span>
                            </h2>
                            <p class="text-xs text-slate-500">Tambahkan seluruh anggota yang tercantum dalam Kartu Keluarga ini.</p>
                        </div>
                        <button
                            type="button"
                            @click="addAnggota"
                            class="px-3 py-1.5 rounded-xl bg-emerald-50 hover:bg-emerald-100 border border-emerald-300 text-emerald-700 text-xs font-bold transition flex items-center gap-1.5 cursor-pointer shrink-0"
                        >
                            <i class="fa-solid fa-user-plus text-xs"></i>
                            <span>+ Tambah Anggota</span>
                        </button>
                    </div>

                    <div v-if="form.anggota.length === 0" class="py-8 text-center bg-slate-50/60 rounded-xl border border-dashed border-slate-200">
                        <i class="fa-solid fa-users text-slate-300 text-2xl mb-2 block"></i>
                        <p class="text-xs font-semibold text-slate-600">Belum ada anggota keluarga ditambahkan.</p>
                        <p class="text-[11px] text-slate-400">Klik tombol "+ Tambah Anggota" untuk mendaftarkan jiwa dalam KK ini.</p>
                    </div>

                    <!-- Anggota List Cards -->
                    <div v-else class="space-y-3">
                        <div
                            v-for="(member, idx) in form.anggota"
                            :key="idx"
                            class="p-4 rounded-xl bg-slate-50 border border-slate-200/90 relative space-y-3"
                        >
                            <div class="flex items-center justify-between border-b border-slate-200/60 pb-2">
                                <span class="text-xs font-extrabold text-blue-700">
                                    Anggota #{{ idx + 1 }} &bull; {{ member.hubungan_keluarga || 'Anggota' }}
                                </span>
                                <button
                                    type="button"
                                    @click="removeAnggota(idx)"
                                    class="text-rose-500 hover:text-rose-700 text-xs font-bold flex items-center gap-1 cursor-pointer"
                                >
                                    <i class="fa-solid fa-trash-can text-xs"></i>
                                    <span>Hapus</span>
                                </button>
                            </div>

                            <!-- Section 1: Sipil & Demografi -->
                            <div class="space-y-2">
                                <span class="text-[11px] font-black text-slate-800 flex items-center gap-1.5">
                                    <i class="fa-solid fa-id-card text-blue-600"></i>
                                    1. Sipil & Demografi (Civilis et Demographia)
                                </span>
                                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3 text-xs">
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-600 mb-1">No. Registrasi / Kode Anggota</label>
                                        <input
                                            v-model="member.kode_anggota"
                                            type="text"
                                            placeholder="Contoh: A0023226310"
                                            class="w-full px-3 py-1.5 rounded-lg bg-white border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-blue-500 font-mono"
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Hubungan Keluarga</label>
                                        <SearchableSelect
                                            v-model="member.hubungan_keluarga"
                                            :options="hubunganKeluargaOptions"
                                            value-key="id"
                                            label-key="name"
                                            placeholder="Pilih hubungan..."
                                            search-placeholder="Cari kedudukan..."
                                            icon="fa-people-roof"
                                            icon-color="text-blue-600"
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-600 mb-1">NIK (16 Digit)</label>
                                        <input
                                            v-model="member.nik"
                                            type="text"
                                            maxlength="16"
                                            placeholder="16 digit NIK..."
                                            class="w-full px-3 py-1.5 rounded-lg bg-white border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-blue-500"
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Nama Lengkap Sipil</label>
                                        <input
                                            v-model="member.nama_lengkap"
                                            type="text"
                                            placeholder="Nama lengkap lahir/sipil..."
                                            class="w-full px-3 py-1.5 rounded-lg bg-white border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-blue-500"
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Nama Baptis Santo/Santa</label>
                                        <input
                                            v-model="member.nama_baptis"
                                            type="text"
                                            placeholder="Nama baptis..."
                                            class="w-full px-3 py-1.5 rounded-lg bg-white border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-blue-500"
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Jenis Kelamin</label>
                                        <select
                                            v-model="member.jenis_kelamin"
                                            class="w-full px-3 py-1.5 rounded-lg bg-white border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-blue-500"
                                        >
                                            <option value="Laki-Laki">Laki-laki</option>
                                            <option value="Perempuan">Perempuan</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Suku / Etnis</label>
                                        <SearchableSelect
                                            v-model="member.suku_etnis"
                                            :options="sukuEtnisOptions"
                                            value-key="id"
                                            label-key="name"
                                            placeholder="Pilih suku / etnis..."
                                            search-placeholder="Cari suku..."
                                            icon="fa-users-line"
                                            icon-color="text-indigo-600"
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Tempat Lahir</label>
                                        <input
                                            v-model="member.tempat_lahir"
                                            type="text"
                                            placeholder="Tempat lahir..."
                                            class="w-full px-3 py-1.5 rounded-lg bg-white border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-blue-500"
                                        />
                                    </div>
                                    <div>
                                        <div class="flex items-center justify-between mb-1">
                                            <label class="block text-[11px] font-bold text-slate-600">Tanggal Lahir</label>
                                            <span v-if="calculateAge(member.tanggal_lahir) !== null" class="text-[10px] font-bold text-blue-600 bg-blue-50 px-1.5 py-0.5 rounded border border-blue-200">
                                                {{ calculateAge(member.tanggal_lahir) }} Thn
                                            </span>
                                        </div>
                                        <input
                                            v-model="member.tanggal_lahir"
                                            type="date"
                                            class="w-full px-3 py-1.5 rounded-lg bg-white border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-blue-500"
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Agama Asal</label>
                                        <SearchableSelect
                                            v-model="member.agama_asal"
                                            :options="agamaAsalOptions"
                                            value-key="id"
                                            label-key="name"
                                            placeholder="Pilih agama asal..."
                                            search-placeholder="Cari agama..."
                                            icon="fa-hands-praying"
                                            icon-color="text-amber-600"
                                        />
                                    </div>
                                </div>
                            </div>

                            <!-- Section 2: Pekerjaan & Sosial -->
                            <div class="space-y-2 pt-2 border-t border-slate-200/60">
                                <span class="text-[11px] font-black text-slate-800 flex items-center gap-1.5">
                                    <i class="fa-solid fa-briefcase text-emerald-600"></i>
                                    2. Pekerjaan & Sosial (Occupatio et Socialis)
                                </span>
                                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3 text-xs">
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Pendidikan</label>
                                        <SearchableSelect
                                            v-model="member.pendidikan"
                                            :options="pendidikanOptions"
                                            value-key="id"
                                            label-key="name"
                                            placeholder="-- Pilih Pendidikan --"
                                            search-placeholder="Cari jenjang pendidikan..."
                                            icon="fa-graduation-cap"
                                            icon-color="text-blue-600"
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Pekerjaan</label>
                                        <SearchableSelect
                                            v-model="member.pekerjaan"
                                            :options="pekerjaanOptions"
                                            value-key="id"
                                            label-key="name"
                                            placeholder="-- Pilih Pekerjaan --"
                                            search-placeholder="Cari profesi / pekerjaan..."
                                            icon="fa-briefcase"
                                            icon-color="text-emerald-600"
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Talenta / Keahlian Paroki</label>
                                        <input
                                            v-model="member.talenta"
                                            type="text"
                                            placeholder="Contoh: Organis, Lektor, Paduan Suara, Tukang..."
                                            class="w-full px-3 py-2 rounded-xl bg-white border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-blue-500 font-medium"
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Disabilitas / Khusus</label>
                                        <SearchableSelect
                                            v-model="member.disabilitas"
                                            :options="disabilitasOptions"
                                            value-key="id"
                                            label-key="name"
                                            placeholder="-- Pilih Kebutuhan Khusus --"
                                            search-placeholder="Cari opsi..."
                                            icon="fa-wheelchair"
                                            icon-color="text-teal-600"
                                        />
                                    </div>
                                </div>
                            </div>

                            <!-- Section 3: Riwayat Sakramen (KHK Can. 849) -->
                            <div class="space-y-2 pt-2 border-t border-slate-200/60">
                                <span class="text-[11px] font-black text-slate-800 flex items-center gap-1.5">
                                    <i class="fa-solid fa-water text-sky-600"></i>
                                    3. Riwayat Sakramen (Inisiasi - KHK Can. 842 & 849)
                                </span>
                                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3 text-xs">
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Status Baptis</label>
                                        <SearchableSelect
                                            v-model="member.status_baptis"
                                            :options="statusBaptisOptions"
                                            value-key="id"
                                            label-key="name"
                                            placeholder="-- Pilih Status Baptis --"
                                            icon="fa-water"
                                            icon-color="text-sky-600"
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Jenis Penerimaan</label>
                                        <SearchableSelect
                                            v-model="member.jenis_penerimaan_baptis"
                                            :options="jenisPenerimaanBaptisOptions"
                                            value-key="id"
                                            label-key="name"
                                            placeholder="-- Pilih Jenis Penerimaan --"
                                            icon="fa-certificate"
                                            icon-color="text-indigo-600"
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Tanggal Baptis</label>
                                        <input
                                            v-model="member.tgl_baptis"
                                            type="date"
                                            class="w-full px-3 py-2 rounded-xl bg-white border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-blue-500 font-medium"
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Paroki Tempat Baptis</label>
                                        <SearchableSelect
                                            v-model="member.paroki_baptis"
                                            :options="parokiSelectOptions"
                                            value-key="id"
                                            label-key="name"
                                            placeholder="Pilih / ketik nama paroki..."
                                            search-placeholder="Cari nama paroki..."
                                            icon="fa-church"
                                            icon-color="text-blue-600"
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Tanggal Komuni Pertama</label>
                                        <input
                                            v-model="member.tgl_komuni_1"
                                            type="date"
                                            class="w-full px-3 py-2 rounded-xl bg-white border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-blue-500 font-medium"
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Paroki Komuni Pertama</label>
                                        <SearchableSelect
                                            v-model="member.paroki_komuni_1"
                                            :options="parokiSelectOptions"
                                            value-key="id"
                                            label-key="name"
                                            placeholder="Paroki Komuni 1..."
                                            search-placeholder="Cari nama paroki..."
                                            icon="fa-bread-slice"
                                            icon-color="text-amber-600"
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Pastor Pembaptis</label>
                                        <SearchableSelect
                                            v-model="member.pastor_baptis"
                                            :options="pastorSelectOptions"
                                            value-key="id"
                                            label-key="name"
                                            placeholder="Rm. Pembaptis..."
                                            search-placeholder="Cari nama pastor..."
                                            icon="fa-user-tie"
                                            icon-color="text-purple-600"
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Wali Baptis / Patrinus (KHK Can. 874)</label>
                                        <input
                                            v-model="member.wali_baptis"
                                            type="text"
                                            placeholder="Nama wali baptis..."
                                            class="w-full px-3 py-1.5 rounded-lg bg-white border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-blue-500"
                                        />
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Buku Besar Baptis (Liber Baptizatorum)</label>
                                        <div class="grid grid-cols-3 gap-2">
                                            <input v-model="member.buku_baptis_vol" type="text" placeholder="Vol..." class="w-full px-2 py-1.5 rounded-lg bg-white border border-slate-200 text-xs text-center" />
                                            <input v-model="member.buku_baptis_hal" type="text" placeholder="Hal..." class="w-full px-2 py-1.5 rounded-lg bg-white border border-slate-200 text-xs text-center" />
                                            <input v-model="member.buku_baptis_no" type="text" placeholder="No..." class="w-full px-2 py-1.5 rounded-lg bg-white border border-slate-200 text-xs text-center" />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Section 4: Catatan Pinggir (KHK Can. 535 par. 2) -->
                            <div class="space-y-2 pt-2 border-t border-slate-200/60">
                                <span class="text-[11px] font-black text-slate-800 flex items-center gap-1.5">
                                    <i class="fa-solid fa-book-bookmark text-amber-600"></i>
                                    4. Catatan Pinggir (Adnotationes Margo - KHK Can. 535 par. 2)
                                </span>
                                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3 text-xs">
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Tanggal Krisma</label>
                                        <input
                                            v-model="member.tgl_krisma"
                                            type="date"
                                            class="w-full px-3 py-1.5 rounded-lg bg-white border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-blue-500"
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Paroki Tempat Krisma</label>
                                        <input
                                            v-model="member.paroki_krisma"
                                            type="text"
                                            placeholder="Paroki krisma..."
                                            class="w-full px-3 py-1.5 rounded-lg bg-white border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-blue-500"
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Tanggal Perkawinan</label>
                                        <input
                                            v-model="member.tgl_perkawinan"
                                            type="date"
                                            class="w-full px-3 py-1.5 rounded-lg bg-white border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-blue-500"
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Status Perkawinan Kanonik</label>
                                        <select
                                            v-model="member.status_perkawinan"
                                            class="w-full px-3 py-1.5 rounded-lg bg-white border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-blue-500"
                                        >
                                            <option value="Belum Menikah">Belum Menikah</option>
                                            <option value="Katolik Organik">Katolik Organik</option>
                                            <option value="Beda Agama (Dispensasi)">Beda Agama (Dispensasi)</option>
                                            <option value="Beda Gereja (Izin)">Beda Gereja (Izin)</option>
                                        </select>
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Catatan Kanonik Lainnya (Anulasi / Dispensasi)</label>
                                        <input
                                            v-model="member.peristiwa_lain"
                                            type="text"
                                            placeholder="Catatan kanonik gerejawi..."
                                            class="w-full px-3 py-1.5 rounded-lg bg-white border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-blue-500"
                                        />
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Nomor Surat / SK Peristiwa</label>
                                        <input
                                            v-model="member.no_surat_peristiwa"
                                            type="text"
                                            placeholder="No. surat keputusan gerejawi..."
                                            class="w-full px-3 py-1.5 rounded-lg bg-white border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-blue-500"
                                        />
                                    </div>
                                </div>
                            </div>

                            <!-- Section 5: Panggilan Hidup Bakti / Vokasi (Imam, Suster, Frater, Biarawan/wati) -->
                            <div class="space-y-2 pt-2 border-t border-purple-200/80 bg-purple-50/40 p-3 rounded-xl border">
                                <div class="flex items-center justify-between flex-wrap gap-2">
                                    <span class="text-[11px] font-black text-purple-900 flex items-center gap-1.5">
                                        <i class="fa-solid fa-cross text-purple-600"></i>
                                        5. Panggilan Hidup Bakti / Vokasi (Khusus Imam, Suster, Frater, Bruder, Biarawan/wati)
                                    </span>
                                    <span v-if="member.status_panggilan && member.status_panggilan !== 'Awam'" class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-100 text-purple-800 border border-purple-300">
                                        {{ member.status_panggilan }}
                                    </span>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3 text-xs">
                                    <div>
                                        <label class="block text-[11px] font-bold text-purple-950 mb-1">Status Panggilan</label>
                                        <select
                                            v-model="member.status_panggilan"
                                            class="w-full px-3 py-1.5 rounded-lg bg-white border border-purple-200 text-xs font-semibold text-slate-900 focus:outline-none focus:border-purple-500"
                                        >
                                            <option value="Awam">Bukan Biarawan (Awam / Umat Biasa)</option>
                                            <option value="Imam Diosesan">Imam Projo / Diosesan (Keuskupan)</option>
                                            <option value="Imam Religius">Imam Religius (Ordo / Kongregasi)</option>
                                            <option value="Suster">Suster / Biarawati</option>
                                            <option value="Frater Diosesan">Frater Diosesan (Calon Imam Keuskupan)</option>
                                            <option value="Frater Kongregasi">Frater Kongregasi / Ordo</option>
                                            <option value="Bruder">Bruder</option>
                                            <option value="Novis / Postulan">Novis / Postulan / Aspiran</option>
                                        </select>
                                    </div>

                                    <template v-if="member.status_panggilan && member.status_panggilan !== 'Awam'">
                                        <div>
                                            <label class="block text-[11px] font-bold text-purple-950 mb-1">Ordo / Kongregasi / Keuskupan</label>
                                            <input
                                                v-model="member.nama_ordo_kongregasi"
                                                type="text"
                                                placeholder="Contoh: Keuskupan Agung Kupang, CMF, SVD, PRR, CIJ, SSpS..."
                                                class="w-full px-3 py-1.5 rounded-lg bg-white border border-purple-200 text-xs text-slate-900 focus:outline-none focus:border-purple-500"
                                            />
                                        </div>
                                        <div>
                                            <label class="block text-[11px] font-bold text-purple-950 mb-1">Tahap Panggilan / Kaul</label>
                                            <select
                                                v-model="member.tahap_panggilan"
                                                class="w-full px-3 py-1.5 rounded-lg bg-white border border-purple-200 text-xs text-slate-900 focus:outline-none focus:border-purple-500"
                                            >
                                                <option value="">-- Pilih Tahap --</option>
                                                <option value="Tahbisan Imamat">Tahbisan Imamat (Imam)</option>
                                                <option value="Tahbisan Diakonat">Tahbisan Diakonat (Diakon)</option>
                                                <option value="Kaul Kekal">Kaul Kekal (Profesi Kekal)</option>
                                                <option value="Kaul Sementara">Kaul Sementara (Profesi Pertama)</option>
                                                <option value="Tahun Orientasi Pastoral (TOP)">Tahun Orientasi Pastoral (TOP)</option>
                                                <option value="Seminari Tinggi (Teologi / Filsafat)">Seminari Tinggi (Teologi / Filsafat)</option>
                                                <option value="Novisiat">Novisiat (Novis)</option>
                                                <option value="Postulat / Aspiran">Postulat / Aspiran</option>
                                                <option value="Seminari Menengah / KPA">Seminari Menengah / KPA</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-[11px] font-bold text-purple-950 mb-1">Tgl Tahbisan / Kaul</label>
                                            <input
                                                v-model="member.tgl_tahbisan_kaul"
                                                type="date"
                                                class="w-full px-3 py-1.5 rounded-lg bg-white border border-purple-200 text-xs text-slate-900 focus:outline-none focus:border-purple-500"
                                            />
                                        </div>
                                        <div class="md:col-span-4">
                                            <label class="block text-[11px] font-bold text-purple-950 mb-1">Tempat Tugas / Biara / Seminari Saat Ini</label>
                                            <input
                                                v-model="member.tempat_tugas_biara"
                                                type="text"
                                                placeholder="Contoh: Seminari Tinggi St. Mikhael Penfui Kupang, Biara Susteran PRR Lebao, Paroki St. Yosef Naikoten..."
                                                class="w-full px-3 py-1.5 rounded-lg bg-white border border-purple-200 text-xs text-slate-900 focus:outline-none focus:border-purple-500"
                                            />
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bottom Sticky Action Bar -->
                <div class="rounded-2xl bg-white border border-slate-200/80 p-4 shadow-2xs flex items-center justify-between">
                    <div class="text-xs text-slate-500">
                        Pastikan seluruh data yang bertanda bintang <span class="text-rose-500 font-bold">*</span> terisi dengan benar.
                    </div>
                    <div class="flex items-center gap-2">
                        <Link
                            :href="`${basePrefix}/kk-katolik`"
                            class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition flex items-center gap-1.5"
                        >
                            <i class="fa-solid fa-xmark text-xs"></i>
                            <span>Batal</span>
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-sm shadow-blue-500/25 transition flex items-center gap-1.5 cursor-pointer disabled:opacity-60"
                        >
                            <i v-if="form.processing" class="fa-solid fa-circle-notch fa-spin text-xs"></i>
                            <i v-else class="fa-solid fa-floppy-disk text-xs"></i>
                            <span>{{ isEdit ? 'Simpan Perubahan' : 'Simpan Data KK' }}</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
