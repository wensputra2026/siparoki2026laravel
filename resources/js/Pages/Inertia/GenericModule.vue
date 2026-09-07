<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import SearchableSelect from '@/Components/SearchableSelect.vue';
import RichTextEditor from '@/Components/RichTextEditor.vue';
import DateInput from '@/Components/DateInput.vue';
import { formatDateId, formatDateTimeId } from '@/utils/date';
import { triggerToast } from '@/composables/useRoleMenu';
import { getDefaultAvatar } from '@/utils/avatar';

const page = usePage();

// Compute base URL prefix (e.g. /superadmin) from current URL
const basePrefix = computed(() => {
    const parts = page.url.split('?')[0].split('/').filter(Boolean);
    return parts.length > 0 ? '/' + parts[0] : '';
});

const getModuleLink = (moduleSlug) => `${basePrefix.value}/${moduleSlug}`;

const props = defineProps({
    title: {
        type: String,
        required: true,
    },
    role: {
        type: String,
        default: '',
    },
    hasImport: {
        type: Boolean,
        default: false,
    },
    hasExport: {
        type: Boolean,
        default: true,
    },
    hasPdf: {
        type: Boolean,
        default: true,
    },
    hasCreate: {
        type: Boolean,
        default: true,
    },
    moduleKey: {
        type: String,
        required: true,
    },
    items: {
        type: Object,
        default: () => ({ data: [], links: [] }),
    },
    columns: {
        type: Array,
        default: () => [],
    },
    keuskupanList: {
        type: Array,
        default: () => [],
    },
    dekenatList: {
        type: Array,
        default: () => [],
    },
    parokiList: {
        type: Array,
        default: () => [],
    },
    defaultParokiId: {
        type: [String, Number],
        default: '',
    },
    defaultParoki: {
        type: Object,
        default: null,
    },
    provinsiList: {
        type: Array,
        default: () => [],
    },
    kabupatenList: {
        type: Array,
        default: () => [],
    },
    kecamatanList: {
        type: Array,
        default: () => [],
    },
    desaList: {
        type: Array,
        default: () => [],
    },
    pastorList: {
        type: Array,
        default: () => [],
    },
    roleList: {
        type: Array,
        default: () => [],
    },
    wilayahList: {
        type: Array,
        default: () => [],
    },
    kapelaList: {
        type: Array,
        default: () => [],
    },
    kubList: {
        type: Array,
        default: () => [],
    },
    kategoriKontenList: {
        type: Array,
        default: () => [],
    },
    penulisList: {
        type: Array,
        default: () => [],
    },
    umatList: {
        type: Array,
        default: () => [],
    },
    kkList: {
        type: Array,
        default: () => [],
    },
    jenisIuranList: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({ search: '' }),
    },
    isKubLocked: {
        type: Boolean,
        default: false,
    },
    currentKub: {
        type: Object,
        default: null,
    },
});

const search = ref(props.filters.search || '');
const perPage = ref(props.filters.per_page || 10);
const keuskupanFilter = ref(props.filters.keuskupan_id || '');
const dekenatFilter = ref(props.filters.dekenat_id || '');
const provinsiFilter = ref(props.filters.provinsi_id || '');
const kabupatenFilter = ref(props.filters.kabupaten_id || '');
const kecamatanFilter = ref(props.filters.kecamatan_id || '');
const wilayahFilter = ref(props.filters.wilayah_id || '');
const kubFilter = ref(props.filters.kub_id || '');
const kapelaFilter = ref(props.filters.kapela_id || '');
const statusFilter = ref(props.filters.status || props.filters.status_verifikasi || '');
const tipeFilter = ref(props.filters.tipe || '');
const roleFilter = ref(props.filters.role_id || '');
const pastorRekanSearch = ref('');
const isCustomKategori = ref(false);
const importFileInput = ref(null);
let debounce = null;

const isKubReadOnlyScope = computed(() => {
    const path = window.location.pathname;
    const prefix = path.split('/').filter(Boolean)[0] || '';
    const roleSlug = String(props.role?.slug || props.role?.nama_role || props.role || '').toLowerCase();
    return props.isKubLocked || prefix === 'kub' || roleSlug.includes('kub');
});

const resolvedKub = computed(() => {
    if (props.currentKub) return props.currentKub;
    if (kubFilter.value) {
        return (props.kubList || []).find(k => String(k.id) === String(kubFilter.value)) || null;
    }
    const userKubId = page.props.auth?.user?.kub_id;
    if (userKubId) {
        return (props.kubList || []).find(k => String(k.id) === String(userKubId)) || null;
    }
    return (props.kubList || [])[0] || null;
});

const resolvedWilayahName = computed(() => {
    if (resolvedKub.value?.wilayah?.nama_wilayah) return resolvedKub.value.wilayah.nama_wilayah;
    const wId = resolvedKub.value?.wilayah_id || wilayahFilter.value;
    const w = (props.wilayahList || []).find(item => String(item.id || item.id_wilayah) === String(wId));
    return w?.nama_wilayah || '-';
});

const resolvedKapelaName = computed(() => {
    if (resolvedKub.value?.kapela?.nama_kapela) return resolvedKub.value.kapela.nama_kapela;
    const kId = resolvedKub.value?.kapela_id || kapelaFilter.value;
    const kp = (props.kapelaList || []).find(item => String(item.id || item.id_kapela) === String(kId));
    return kp?.nama_kapela || 'Pusat Paroki';
});

const resolvedKubName = computed(() => {
    return resolvedKub.value?.nama_kub || '-';
});

const selectedKuasiParokiName = computed(() => {
    if (!formData.value?.paroki_id) return '';
    const p = (props.parokiList || []).find(x => String(x.id_paroki || x.id) === String(formData.value.paroki_id));
    return p?.nama_paroki || '';
});

const selectedKuasiDekenatName = computed(() => {
    if (!formData.value?.dekenat_id) return '';
    const d = (props.dekenatList || []).find(x => String(x.id_dekenat || x.id_kevikepan || x.id) === String(formData.value.dekenat_id));
    return d?.nama_dekenat || d?.nama_kevikepan || d?.name || '';
});

watch(() => formData.value?.paroki_id, (newParokiId) => {
    if (props.moduleKey === 'kuasi-paroki' && newParokiId) {
        const found = (props.parokiList || []).find(p => String(p.id_paroki || p.id) === String(newParokiId));
        if (found && found.dekenat_id) {
            formData.value.dekenat_id = found.dekenat_id;
        }
    }
});

const statusOptions = [
    { value: '', label: 'Semua Status' },
    { value: 'Aktif', label: 'Aktif / Terverifikasi' },
    { value: 'Nonaktif', label: 'Nonaktif / Diblokir' },
    { value: 'Pending', label: 'Pending Verifikasi' },
];

const filteredWilayahsForFilter = computed(() => {
    let list = props.wilayahList || [];
    if (kapelaFilter.value) {
        const relevantWilayahIds = (props.kubList || [])
            .filter(k => String(k.kapela_id || k.id_kapela) === String(kapelaFilter.value) && (k.wilayah_id || k.id_wilayah))
            .map(k => String(k.wilayah_id || k.id_wilayah));
        if (relevantWilayahIds.length > 0) {
            const filtered = list.filter(w => relevantWilayahIds.includes(String(w.id || w.id_wilayah)) || String(w.kapela_id) === String(kapelaFilter.value));
            if (filtered.length > 0) return filtered;
        }
    }
    return list;
});

const filteredKapelasForFilter = computed(() => {
    let list = props.kapelaList || [];
    if (wilayahFilter.value) {
        const relevantKapelaIds = (props.kubList || [])
            .filter(k => String(k.wilayah_id || k.id_wilayah) === String(wilayahFilter.value) && (k.kapela_id || k.id_kapela))
            .map(k => String(k.kapela_id || k.id_kapela));
        if (relevantKapelaIds.length > 0) {
            const filtered = list.filter(kp => relevantKapelaIds.includes(String(kp.id || kp.id_kapela)));
            if (filtered.length > 0) return filtered;
        }
    }
    return list;
});

const filteredKubsForFilter = computed(() => {
    let list = props.kubList || [];
    if (wilayahFilter.value && kapelaFilter.value) {
        const matchBoth = list.filter(k => 
            String(k.wilayah_id || k.id_wilayah) === String(wilayahFilter.value) && 
            String(k.kapela_id || k.id_kapela) === String(kapelaFilter.value)
        );
        if (matchBoth.length > 0) return matchBoth;
        return list.filter(k => 
            String(k.wilayah_id || k.id_wilayah) === String(wilayahFilter.value) || 
            String(k.kapela_id || k.id_kapela) === String(kapelaFilter.value)
        );
    }
    if (wilayahFilter.value) {
        return list.filter(k => String(k.wilayah_id || k.id_wilayah) === String(wilayahFilter.value));
    }
    if (kapelaFilter.value) {
        return list.filter(k => String(k.kapela_id || k.id_kapela) === String(kapelaFilter.value));
    }
    return list;
});

const accountStatusOptions = [
    { value: 1, label: 'Aktif' },
    { value: 0, label: 'Nonaktif' },
];

const maintenanceOptions = [
    { value: 'Tidak', label: 'Tidak' },
    { value: 'Ya', label: 'Ya (Akses Maintenance Penuh)' },
];

// Modal & Form state must exist before computed/watchers that read formData.
const showFormModal = ref(false);
const showDetailModal = ref(false);
const showDeleteModal = ref(false);
const selectedItem = ref(null);
const modalMode = ref('create'); // 'create' | 'edit'
const formData = ref({});
const uploadedFile = ref(null);
const previewImage = ref(null);
const isSubmitting = ref(false);
const showPassword = ref(false);

const filteredFormKubs = computed(() => {
    let list = props.kubList || [];
    if (formData.value?.wilayah_id) {
        list = list.filter(k => String(k.wilayah_id || k.id_wilayah) === String(formData.value.wilayah_id));
    } else if (formData.value?.kapela_id) {
        list = list.filter(k => String(k.kapela_id || k.id_kapela) === String(formData.value.kapela_id));
    }
    return list.map(k => {
        let asal = '';
        const kapId = k.kapela_id || k.id_kapela;
        const wilId = k.wilayah_id || k.id_wilayah;
        if (kapId) {
            const kap = (props.kapelaList || []).find(kp => String(kp.id || kp.id_kapela) === String(kapId));
            if (kap) asal = `Stasi ${kap.nama_kapela}`;
        }
        if (!asal && wilId) {
            const wil = (props.wilayahList || []).find(w => String(w.id || w.id_wilayah) === String(wilId));
            if (wil) asal = `Wilayah ${wil.nama_wilayah}`;
        }
        return {
            ...k,
            nama_kub_with_asal: asal ? `${k.nama_kub} (${asal})` : k.nama_kub,
        };
    });
});

const onMutasiUmatSelected = (umatId) => {
    formData.value.umat_id = umatId;
    const selected = (props.umatList || []).find(u => String(u.id) === String(umatId));
    if (selected) {
        formData.value.kub_asal_id = selected.kub_id || '';
        formData.value.wilayah_asal_id = selected.wilayah_id || '';
        formData.value.kapela_asal_id = selected.kapela_id || '';
        formData.value.kk_id = selected.kk_id || '';
    }
};

const activeProfileParoki = computed(() => {
    if (props.defaultParoki) return props.defaultParoki;
    if (!props.defaultParokiId) return props.parokiList?.[0] || null;
    return props.parokiList.find((paroki) => String(paroki.id_paroki || paroki.id) === String(props.defaultParokiId)) || props.parokiList?.[0] || null;
});

const activeProfileParokiId = computed(() => {
    const paroki = activeProfileParoki.value;
    return paroki ? (paroki.id_paroki || paroki.id) : '';
});

const tipeKontenList = computed(() => {
    const fromKategori = (props.kategoriKontenList || []).map(item => item.tipe).filter(Boolean);
    return Array.from(new Set(['Berita', 'Artikel', 'Renungan', 'Pengumuman', ...fromKategori]));
});

const permissionGroups = [
    {
        title: 'Dashboard & Panduan',
        icon: 'fa-gauge-high',
        items: [
            { key: 'dashboard', label: 'Dashboard' },
            { key: 'panduan', label: 'Panduan' },
        ]
    },
    {
        title: 'Wilayah & Referensi (Gerejawi)',
        icon: 'fa-church',
        items: [
            { key: 'keuskupan', label: 'Keuskupan' },
            { key: 'dekenat', label: 'Dekenat' },
            { key: 'paroki', label: 'Paroki' },
            { key: 'kuasi_paroki', label: 'Kuasi Paroki' },
            { key: 'kapela', label: 'Stasi / Kapela' },
            { key: 'wilayah', label: 'Wilayah' },
            { key: 'lingkungan', label: 'Lingkungan' },
            { key: 'kub', label: 'KUB' },
            { key: 'master_referensi', label: 'Data Referensi' },
        ]
    },
    {
        title: 'Wilayah Sipil',
        icon: 'fa-map-location-dot',
        items: [
            { key: 'provinsi', label: 'Provinsi' },
            { key: 'kabupaten', label: 'Kabupaten / Kota' },
            { key: 'kecamatan', label: 'Kecamatan' },
            { key: 'desa', label: 'Desa / Kelurahan' },
        ]
    },
    {
        title: 'Umat & Pelayanan Paroki',
        icon: 'fa-users',
        items: [
            { key: 'direktori_dpp', label: 'Direktori DPP' },
            { key: 'direktori_katekis', label: 'Direktori Katekis' },
            { key: 'direktori_misdinar', label: 'Direktori Misdinar' },
            { key: 'master_pastor', label: 'Riwayat Pastor' },
            { key: 'kronik', label: 'Kronik Paroki' },
            { key: 'peran_kategorial', label: 'Peran Kategorial' },
            { key: 'anggota_kategorial', label: 'Anggota Kategorial' },
            { key: 'jadwal_misa', label: 'Jadwal Misa' },
            { key: 'kk_katolik', label: 'KK Katolik' },
            { key: 'data_keluarga', label: 'Data Keluarga' },
            { key: 'data_umat', label: 'Data Umat / Jiwa' },
            { key: 'statistik', label: 'Demografi & Statistik' },
            { key: 'sakramen', label: 'Buku Besar Sakramen' },
            { key: 'pemeriksaan_kanonikal', label: 'Pemeriksaan Kanonikal' },
            { key: 'pengajuan_sakramen', label: 'Pengajuan Sakramen' },
            { key: 'katekumen', label: 'Katekumen & Pembinaan' },
            { key: 'lapak', label: 'Lapak & Toko Umat' },
        ]
    },
    {
        title: 'Sekretariat Paroki',
        icon: 'fa-file-signature',
        items: [
            { key: 'surat_masuk', label: 'Surat Masuk' },
            { key: 'surat_keluar', label: 'Surat Keluar' },
            { key: 'arsip_digital', label: 'Arsip Digital Paroki' },
            { key: 'rapat_notulen', label: 'Rapat & Notulen' },
        ]
    },
    {
        title: 'Keuangan & Aset',
        icon: 'fa-coins',
        items: [
            { key: 'jenis_iuran', label: 'Jenis Iuran' },
            { key: 'iuran_umat', label: 'Iuran Umat' },
            { key: 'kolekte_misa', label: 'Kolekte Misa' },
            { key: 'intensi_misa', label: 'Intensi Misa' },
            { key: 'keuangan', label: 'Keuangan Paroki' },
            { key: 'aset', label: 'Aset & Inventaris' },
        ]
    },
    {
        title: 'Website Paroki',
        icon: 'fa-globe',
        items: [
            { key: 'kategori_konten', label: 'Kategori Konten' },
            { key: 'berita_artikel', label: 'Berita & Artikel' },
            { key: 'agenda_kegiatan', label: 'Agenda Kegiatan' },
            { key: 'galeri_album', label: 'Galeri Album' },
            { key: 'sambutan_pastor', label: 'Sambutan Pastor' },
            { key: 'pusat_unduhan', label: 'Pusat Unduhan' },
            { key: 'slider_banner', label: 'Slider / Banner' },
            { key: 'menu_frontend', label: 'Menu Frontend' },
            { key: 'seo_halaman', label: 'SEO Halaman' },
            { key: 'widget_frontend', label: 'Widget Frontend' },
            { key: 'pengaturan_web', label: 'Pengaturan Web' },
        ]
    },
    {
        title: 'Sistem & Keamanan',
        icon: 'fa-shield-halved',
        items: [
            { key: 'profil_paroki', label: 'Profil Paroki' },
            { key: 'view_users', label: 'View Users' },
            { key: 'create_users', label: 'Create Users' },
            { key: 'edit_users', label: 'Edit Users' },
            { key: 'delete_users', label: 'Delete Users' },
            { key: 'view_roles', label: 'View Roles' },
            { key: 'create_roles', label: 'Create Roles' },
            { key: 'edit_roles', label: 'Edit Roles' },
            { key: 'delete_roles', label: 'Delete Roles' },
            { key: 'security_center', label: 'Security Center' },
            { key: 'backup_restore', label: 'Backup & Restore' },
            { key: 'profil_saya', label: 'Profil Saya' },
        ]
    }
];

const toggleGroupPermissions = (group) => {
    if (!Array.isArray(formData.value.permissions)) {
        formData.value.permissions = [];
    }
    const groupKeys = group.items.map(i => i.key);
    const allSelected = groupKeys.every(k => formData.value.permissions.includes(k));
    if (allSelected) {
        formData.value.permissions = formData.value.permissions.filter(k => !groupKeys.includes(k));
    } else {
        formData.value.permissions = Array.from(new Set([...formData.value.permissions, ...groupKeys]));
    }
};

const isGroupAllSelected = (group) => {
    if (!Array.isArray(formData.value.permissions)) return false;
    return group.items.every(i => formData.value.permissions.includes(i.key));
};

const selectAllAllPermissions = () => {
    const all = [];
    permissionGroups.forEach(g => g.items.forEach(i => all.push(i.key)));
    formData.value.permissions = all;
};

const clearAllPermissions = () => {
    formData.value.permissions = [];
};

const applyFilters = () => {
    router.get(
        window.location.pathname,
        {
            search: search.value || undefined,
            per_page: perPage.value,
            keuskupan_id: keuskupanFilter.value || undefined,
            dekenat_id: dekenatFilter.value || undefined,
            provinsi_id: provinsiFilter.value || undefined,
            kabupaten_id: kabupatenFilter.value || undefined,
            kecamatan_id: kecamatanFilter.value || undefined,
            wilayah_id: wilayahFilter.value || undefined,
            kub_id: kubFilter.value || undefined,
            kapela_id: kapelaFilter.value || undefined,
            role_id: roleFilter.value || undefined,
            status: statusFilter.value || undefined,
            tipe: tipeFilter.value || undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
            only: ['items', 'filters'],
        }
    );
};

watch(search, () => {
    clearTimeout(debounce);
    debounce = setTimeout(applyFilters, 500);
});

    const isReloadingData = ref(false);
    const reloadModuleData = () => {
        refreshData();
    };

watch(kabupatenFilter, () => {
    if (kecamatanFilter.value && !filteredKecamatansForFilter.value.some(k => String(k.id_kecamatan || k.id) === String(kecamatanFilter.value))) {
        kecamatanFilter.value = '';
    }
});

watch(wilayahFilter, (newWilayah) => {
    if (newWilayah) {
        kapelaFilter.value = '';
    }
    if (kubFilter.value && !filteredKubsForFilter.value.some(k => String(k.id || k.id_kub) === String(kubFilter.value))) {
        kubFilter.value = '';
    }
});

watch(kapelaFilter, (newKapela) => {
    if (newKapela) {
        wilayahFilter.value = '';
    }
    if (kubFilter.value && !filteredKubsForFilter.value.some(k => String(k.id || k.id_kub) === String(kubFilter.value))) {
        kubFilter.value = '';
    }
});

watch(() => formData.value?.wilayah_id, (newWilayah) => {
    if (props.moduleKey === 'wilayah') return;
    if (newWilayah && formData.value) {
        formData.value.kapela_id = '';
    }
    if (formData.value?.kub_id && !filteredFormKubs.value.some(k => String(k.id || k.id_kub) === String(formData.value.kub_id))) {
        formData.value.kub_id = '';
    }
});

watch(() => formData.value?.kapela_id, (newKapela) => {
    if (props.moduleKey === 'wilayah') return;
    if (newKapela && formData.value) {
        formData.value.wilayah_id = '';
    }
    if (formData.value?.kub_id && !filteredFormKubs.value.some(k => String(k.id || k.id_kub) === String(formData.value.kub_id))) {
        formData.value.kub_id = '';
    }
});

watch([perPage, keuskupanFilter, dekenatFilter, provinsiFilter, kabupatenFilter, kecamatanFilter, wilayahFilter, kubFilter, kapelaFilter, roleFilter, statusFilter], () => {
    applyFilters();
});

const getRelationHref = (col, item) => {
    const base = getModuleLink(col.linkTo || col.relation);
    if (col.filterParam) {
        const val = item.id_provinsi || item.id_kabupaten || item.id_kecamatan || item.id_keuskupan || item.id_dekenat || item.id_paroki || item.id;
        return `${base}?${col.filterParam}=${val}`;
    }
    return base;
};

const importExportModuleKeys = [
    'keuskupan',
    'dekenat',
    'kevikepan',
    'paroki',
    'kuasi-paroki',
    'kapela',
    'stasi',
    'wilayah',
    'kub',
    'provinsi',
    'kabupaten',
    'kecamatan',
    'desa-kelurahan',
    'kk-katolik',
    'kk',
    'keluarga',
    'umat',
    'data-umat',
    'data_umat',
];

const hasImportExportActions = computed(() => importExportModuleKeys.includes(props.moduleKey));
const currentUserId = computed(() => page.props.auth?.user?.id || null);

const isSelfUser = (item) => props.moduleKey === 'user' && currentUserId.value && Number(item?.id) === Number(currentUserId.value);

const roleBadgeClass = (role) => {
    const slug = String(role?.slug || role?.nama_role || '').toLowerCase();
    if (slug.includes('super')) return 'bg-rose-50 text-rose-700 border-rose-200';
    if (slug.includes('pastor')) return 'bg-amber-50 text-amber-700 border-amber-200';
    if (slug.includes('penulis') || slug.includes('redaksi')) return 'bg-purple-50 text-purple-700 border-purple-200';
    if (slug.includes('wilayah')) return 'bg-blue-50 text-blue-700 border-blue-200';
    if (slug.includes('kapela') || slug.includes('stasi')) return 'bg-slate-50 text-slate-700 border-slate-200';
    if (slug.includes('kub')) return 'bg-cyan-50 text-cyan-700 border-cyan-200';
    return 'bg-emerald-50 text-emerald-700 border-emerald-200';
};

const isUmatReadOnlyRole = computed(() => {
    return false;
});

const isKkReadOnlyForRole = computed(() => {
    const p = (props.prefix || basePrefix.value || (typeof window !== 'undefined' ? window.location.pathname : '') || '').toLowerCase();
    const r = String(props.role?.slug || props.role?.nama_role || props.role || page.props.role || '').toLowerCase();
    const isWilayahOrKapela = p.includes('wilayah') || p.includes('kapela') || p.includes('stasi') || r.includes('wilayah') || r.includes('kapela') || r.includes('stasi');
    if (['kk-katolik', 'kk', 'keluarga'].includes(props.moduleKey) && isWilayahOrKapela) {
        return true;
    }
    return false;
});

const isViewAndEditOnlyRole = computed(() => {
    return false;
});

const generatePassword = () => {
    const suffix = Math.random().toString(36).slice(2, 8).toUpperCase();
    formData.value.password = `SIP-${suffix}`;
    showPassword.value = true;
};

// Cascading computed lists
const availableDekenats = computed(() => {
    if (!formData.value.keuskupan_id) return props.dekenatList;
    return props.dekenatList.filter(d => String(d.keuskupan_id) === String(formData.value.keuskupan_id));
});

const availableKabupatens = computed(() => {
    if (!formData.value.provinsi_id) return props.kabupatenList;
    return props.kabupatenList.filter(k => String(k.provinsi_id) === String(formData.value.provinsi_id));
});

const availableKecamatans = computed(() => {
    if (!formData.value.kabupaten_id) return props.kecamatanList;
    return props.kecamatanList.filter(k => String(k.kabupaten_id) === String(formData.value.kabupaten_id));
});

const filteredKecamatansForFilter = computed(() => {
    if (!kabupatenFilter.value) return props.kecamatanList;
    return props.kecamatanList.filter(k => String(k.kabupaten_id) === String(kabupatenFilter.value));
});

const availableDesas = computed(() => {
    if (!formData.value.kecamatan_id) return props.desaList || [];
    return (props.desaList || []).filter(d => String(d.kecamatan_id) === String(formData.value.kecamatan_id));
});

const filteredPastorsForRekan = computed(() => {
    if (!pastorRekanSearch.value.trim()) return props.pastorList;
    const q = pastorRekanSearch.value.toLowerCase().trim();
    return props.pastorList.filter(p => {
        const name = typeof p === 'string' ? p : (p.nama_pastor || '');
        return name.toLowerCase().includes(q);
    });
});

const pastorParokiOptions = computed(() => {
    return (props.pastorList || []).map((p) => {
        const name = typeof p === 'string' ? p : (p.nama_pastor || '');
        return {
            id: name,
            name: name,
            value: name,
            label: name,
        };
    });
});

const tempatPelayananOptions = computed(() => {
    const list = [
        { id: 'Pusat Paroki (Gereja Utama)', name: 'Pusat Paroki (Gereja Utama)', value: 'Pusat Paroki (Gereja Utama)', label: 'Pusat Paroki (Gereja Utama)' },
    ];

    if (props.kapelaList && props.kapelaList.length) {
        props.kapelaList.forEach(k => {
            const name = k.nama_kapela || k.nama;
            if (name) {
                const labelStr = `Kapela / Stasi: ${name}`;
                list.push({
                    id: name,
                    name: labelStr,
                    value: name,
                    label: labelStr,
                });
            }
        });
    }

    if (props.wilayahList && props.wilayahList.length) {
        props.wilayahList.forEach(w => {
            const name = w.nama_wilayah || w.nama;
            if (name) {
                const labelStr = `Wilayah: ${name}`;
                list.push({
                    id: name,
                    name: labelStr,
                    value: name,
                    label: labelStr,
                });
            }
        });
    }

    if (props.kubList && props.kubList.length) {
        props.kubList.forEach(kub => {
            const name = kub.nama_kub || kub.nama;
            if (name) {
                const labelStr = `KUB: ${name}`;
                list.push({
                    id: name,
                    name: labelStr,
                    value: name,
                    label: labelStr,
                });
            }
        });
    }

    if (formData.value.wilayah_pelayanan && !list.some(o => o.value === formData.value.wilayah_pelayanan)) {
        list.unshift({
            id: formData.value.wilayah_pelayanan,
            name: formData.value.wilayah_pelayanan,
            value: formData.value.wilayah_pelayanan,
            label: formData.value.wilayah_pelayanan,
        });
    }

    return list;
});

const kapelaOptionsForMisdinar = computed(() => {
    const list = [
        { id: '', nama_kapela: 'Pusat Paroki (Gereja Utama)', label: 'Pusat Paroki (Gereja Utama)' },
    ];
    if (props.kapelaList && props.kapelaList.length) {
        props.kapelaList.forEach(k => {
            list.push({
                id: k.id || k.id_kapela,
                nama_kapela: k.nama_kapela || k.nama,
                label: `Kapela / Stasi: ${k.nama_kapela || k.nama}`,
            });
        });
    }
    return list;
});

const kapelaOptionsForWilayah = computed(() => {
    const list = [
        { id: '', nama_kapela: 'Pusat Paroki (Gereja Paroki Induk)', label: 'Pusat Paroki (Gereja Paroki Induk)' },
    ];
    if (props.kapelaList && props.kapelaList.length) {
        props.kapelaList.forEach(k => {
            const kId = k.id || k.id_kapela;
            const kName = k.nama_kapela || k.nama || `Stasi #${kId}`;
            list.push({
                id: kId,
                nama_kapela: kName,
                label: `Stasi / Kapela ${kName}`,
            });
        });
    }
    return list;
});

const tipeSakramenOptions = [
    { value: 'Baptis', label: 'Sakramen Baptis (Sacramentum Baptismi)' },
    { value: 'Komuni Pertama', label: 'Sakramen Ekaristi / Komuni Suci (Sacramentum Eucharistiae)' },
    { value: 'Krisma', label: 'Sakramen Krisma / Penguatan (Sacramentum Confirmationis)' },
    { value: 'Pengakuan Dosa', label: 'Sakramen Tobat / Rekonsiliasi (Sacramentum Paenitentiae)' },
    { value: 'Pengurapan Orang Sakit', label: 'Sakramen Pengurapan Orang Sakit / Perminyakan Suci (Sacramentum Unctionis Infirmorum)' },
    { value: 'Perkawinan', label: 'Sakramen Perkawinan / Matrimoni (Sacramentum Matrimonii)' },
    { value: 'Tahbisan', label: 'Sakramen Tahbisan Suci / Imamat (Sacramentum Ordinis)' },
    { value: 'Lainnya', label: 'Lainnya' },
];

const umatSelectOptions = computed(() => {
    return (props.umatList || []).map(u => ({
        id: u.id,
        name: `${u.nama_lengkap}${u.nik ? ' (NIK: ' + u.nik + ')' : ''}`,
        nama_lengkap: u.nama_lengkap,
        handphone: u.handphone || '',
    }));
});

// Endpoint pencarian umat lazy (tenant-scoped) untuk SearchableSelect.
const umatSearchUrl = '/umat-options';

const onIuranKkChange = (selectedKkId) => {
    const found = (props.kkList || []).find(k => String(k.id) === String(selectedKkId));
    if (found) {
        formData.value.id_kk = found.id;
        formData.value.no_kk = found.no_kk;
        formData.value.nama_kepala = found.nama_kepala;
    }
};

const onIuranJenisChange = (selectedJenisId) => {
    const found = (props.jenisIuranList || []).find(j => String(j.id) === String(selectedJenisId));
    if (found) {
        formData.value.jenis_iuran_id = found.id;
        formData.value.nama_iuran = found.nama_iuran;
        if (found.nominal_default) {
            formData.value.total_jumlah = found.nominal_default;
            formData.value.jumlah = found.nominal_default;
        }
    }
};

const bulanOptions = [
    { value: '01', label: 'Januari' },
    { value: '02', label: 'Februari' },
    { value: '03', label: 'Maret' },
    { value: '04', label: 'April' },
    { value: '05', label: 'Mei' },
    { value: '06', label: 'Juni' },
    { value: '07', label: 'Juli' },
    { value: '08', label: 'Agustus' },
    { value: '09', label: 'September' },
    { value: '10', label: 'Oktober' },
    { value: '11', label: 'November' },
    { value: '12', label: 'Desember' },
];

const tahunOptions = [
    { value: 2024, label: '2024' },
    { value: 2025, label: '2025' },
    { value: 2026, label: '2026' },
    { value: 2027, label: '2027' },
    { value: 2028, label: '2028' },
];

const statusBayarOptions = [
    { value: 'Lunas', label: 'Lunas' },
    { value: 'Belum Lunas', label: 'Belum Lunas' },
    { value: 'Cicilan', label: 'Cicilan / Sebagian' },
    { value: 'Pending', label: 'Menunggu Verifikasi' },
];

const metodeBayarOptions = [
    { value: 'Tunai', label: 'Tunai / Cash' },
    { value: 'Transfer Bank', label: 'Transfer Bank' },
    { value: 'QRIS', label: 'QRIS Paroki' },
    { value: 'Kolektor KUB', label: 'Setoran Melalui Kolektor KUB' },
];

const kategoriMisaOptions = [
    { value: 'Misa Hari Minggu I (Pagi)', label: 'Misa Hari Minggu I (Pagi)' },
    { value: 'Misa Hari Minggu II (Sore)', label: 'Misa Hari Minggu II (Sore)' },
    { value: 'Misa Harian (Pagi / Sore)', label: 'Misa Harian (Pagi / Sore)' },
    { value: 'Misa Jumat Pertama (Jumper)', label: 'Misa Jumat Pertama (Jumper)' },
    { value: 'Misa Hari Raya Natal', label: 'Misa Hari Raya Natal' },
    { value: 'Misa Hari Raya Paskah / Trihari Suci', label: 'Misa Hari Raya Paskah / Trihari Suci' },
    { value: 'Misa Hari Raya Pentakosta', label: 'Misa Hari Raya Pentakosta' },
    { value: 'Misa Stasi / Kapela', label: 'Misa Stasi / Kapela' },
    { value: 'Misa Lingkungan / KUB', label: 'Misa Lingkungan / KUB' },
    { value: 'Misa Sakramen Krisma / Komuni Pertama', label: 'Misa Sakramen Krisma / Komuni Pertama' },
    { value: 'Misa Perkawinan (Matrimonium)', label: 'Misa Perkawinan (Matrimonium)' },
    { value: 'Misa Arwah / Requiem', label: 'Misa Arwah / Requiem' },
    { value: 'Misa Syukur / Pesta Pelindung', label: 'Misa Syukur / Pesta Pelindung' },
];

const lokasiMisaOptions = computed(() => {
    const list = [
        { value: 'Gereja Paroki Benlutu', label: 'Gereja Paroki Benlutu (Pusat Paroki)' },
    ];
    if (props.kapelaList && props.kapelaList.length > 0) {
        props.kapelaList.forEach(k => {
            const name = k.nama_kapela || k.name || '';
            if (name) {
                list.push({
                    value: name,
                    label: `Kapela / Stasi: ${name}`,
                });
            }
        });
    }
    if (props.wilayahList && props.wilayahList.length > 0) {
        props.wilayahList.forEach(w => {
            const name = w.nama_wilayah || w.name || '';
            if (name) {
                list.push({
                    value: `Wilayah: ${name}`,
                    label: `Wilayah: ${name}`,
                });
            }
        });
    }
    return list;
});

const togglePastorRekan = (name) => {
    if (!Array.isArray(formData.value.selected_pastor_rekan)) {
        formData.value.selected_pastor_rekan = [];
    }
    const idx = formData.value.selected_pastor_rekan.indexOf(name);
    if (idx > -1) {
        formData.value.selected_pastor_rekan.splice(idx, 1);
    } else {
        formData.value.selected_pastor_rekan.push(name);
    }
};

const isPastorRekanSelected = (name) => {
    return Array.isArray(formData.value.selected_pastor_rekan) && formData.value.selected_pastor_rekan.includes(name);
};

const removePastorRekan = (name) => {
    if (Array.isArray(formData.value.selected_pastor_rekan)) {
        const idx = formData.value.selected_pastor_rekan.indexOf(name);
        if (idx > -1) formData.value.selected_pastor_rekan.splice(idx, 1);
    }
};

const formatPaginationLabel = (label) => {
    if (!label) return '';
    const str = String(label).trim();
    if (str.toLowerCase().includes('prev') || str.toLowerCase().includes('pagination.previous')) {
        return '&laquo; Sebelum';
    }
    if (str.toLowerCase().includes('next') || str.toLowerCase().includes('pagination.next')) {
        return 'Sesudah &raquo;';
    }
    return str;
};

// Cascading watchers
watch(() => formData.value.keuskupan_id, (newVal) => {
    if (props.moduleKey === 'paroki') {
        if (modalMode.value === 'create' && newVal) {
            const keuskupan = props.keuskupanList.find(k => (k.id || k.id_keuskupan) == newVal);
            const prefix = keuskupan ? (keuskupan.kode_keuskupan || 'PRK') : 'PRK';
            const num = String(Math.floor(100 + Math.random() * 900));
            formData.value.kode_paroki = `${prefix}-${num}`;
        }
        if (formData.value.dekenat_id) {
            const isValid = availableDekenats.value.some(d => (d.id || d.id_dekenat || d.id_kevikepan) == formData.value.dekenat_id);
            if (!isValid) formData.value.dekenat_id = '';
        }
    }
});

watch(() => formData.value.provinsi_id, (newVal) => {
    const prov = props.provinsiList.find(p => (p.id || p.id_provinsi) == newVal);
    if (prov) formData.value.provinsi = prov.nama_provinsi;
    if (formData.value.kabupaten_id && !availableKabupatens.value.some(k => (k.id || k.id_kabupaten) == formData.value.kabupaten_id)) {
        formData.value.kabupaten_id = '';
        formData.value.kecamatan_id = '';
        formData.value.desa_id = '';
        formData.value.kabupaten = '';
        formData.value.kecamatan = '';
        formData.value.desa = '';
    }
});

watch(() => formData.value.kabupaten_id, (newVal) => {
    const kab = props.kabupatenList.find(k => (k.id || k.id_kabupaten) == newVal);
    if (kab) formData.value.kabupaten = kab.nama_kabupaten;
    if (formData.value.kecamatan_id && !availableKecamatans.value.some(k => (k.id || k.id_kecamatan) == formData.value.kecamatan_id)) {
        formData.value.kecamatan_id = '';
        formData.value.desa_id = '';
        formData.value.kecamatan = '';
        formData.value.desa = '';
    }
});

watch(() => formData.value.umat_id, (newVal) => {
    if (props.moduleKey === 'pengajuan-sakramen' && newVal) {
        fetch(`/umat-options?id=${encodeURIComponent(newVal)}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
        })
            .then((r) => r.json())
            .then((data) => {
                const found = Array.isArray(data) ? data[0] : null;
                if (found) {
                    formData.value.nama_lengkap = found.nama_lengkap || found.name || '';
                    if (found.handphone) {
                        formData.value.whatsapp = found.handphone;
                    }
                }
            })
            .catch(() => {});
    }
});

watch(() => formData.value.kecamatan_id, (newVal) => {
    const kec = props.kecamatanList.find(k => (k.id || k.id_kecamatan) == newVal);
    if (kec) formData.value.kecamatan = kec.nama_kecamatan;
    if (formData.value.desa_id && !availableDesas.value.some(d => (d.id || d.id_desa) == formData.value.desa_id)) {
        formData.value.desa_id = '';
        formData.value.desa = '';
    }
});

watch(() => formData.value.desa_id, (newVal) => {
    const desa = (props.desaList || []).find(d => (d.id || d.id_desa) == newVal);
    if (desa) {
        formData.value.desa = desa.nama_desa;
        if (props.moduleKey === 'keuskupan' && modalMode.value === 'create' && (!formData.value.alamat || formData.value.alamat.includes('Mgr. Sugiyopranoto'))) {
            const desaName = desa.nama_desa;
            const kecName = formData.value.kecamatan || 'Oebobo';
            const kabName = formData.value.kabupaten || 'Kota Kupang';
            const provName = formData.value.provinsi || 'Nusa Tenggara Timur';
            formData.value.alamat = `Jl. Mgr. Sugiyopranoto No. 1, Kel. ${desaName}, Kec. ${kecName}, ${kabName}, ${provName}`;
        }
    }
});

const handleFileUpload = (event, key) => {
    const file = event.target.files[0];
    if (file) {
        uploadedFile.value = file;
        formData.value[key] = file;
        const reader = new FileReader();
        reader.onload = (e) => {
            previewImage.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};

const slugifyText = (value) => String(value || '')
    .toLowerCase()
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .replace(/[^\w\s-]/g, '')
    .trim()
    .replace(/[\s_-]+/g, '-')
    .replace(/^-+|-+$/g, '');

const updateKontenSlug = () => {
    if (props.moduleKey !== 'konten') return;
    formData.value.slug = slugifyText(formData.value.judul);
};

const stripHtmlText = (value) => {
    const div = document.createElement('div');
    div.innerHTML = String(value || '');
    return (div.textContent || div.innerText || '')
        .replace(/\[[^\]]+\]/g, '')
        .replace(/\s+/g, ' ')
        .trim();
};

const generateKontenExcerpt = () => {
    const cleanText = stripHtmlText(formData.value.isi);
    if (!cleanText) return;
    const limit = 180;
    const trimmed = cleanText.length > limit ? cleanText.slice(0, limit) : cleanText;
    const lastSpace = trimmed.lastIndexOf(' ');
    formData.value.excerpt = cleanText.length > limit && lastSpace > 50
        ? `${trimmed.slice(0, lastSpace)}...`
        : (cleanText.length > limit ? `${trimmed}...` : trimmed);
};

const syncKontenKategori = () => {
    const kategori = (props.kategoriKontenList || []).find(item => String(item.id) === String(formData.value.kategori_id));
    if (kategori) {
        formData.value.kategori = kategori.nama_kategori || '';
        if (!formData.value.tipe && kategori.tipe) {
            formData.value.tipe = kategori.tipe;
        }
    }
};

const addKontenTag = (tag) => {
    const current = String(formData.value.tags || '')
        .split(',')
        .map(item => item.trim())
        .filter(Boolean);
    if (!current.includes(tag)) {
        current.push(tag);
        formData.value.tags = current.join(', ');
    }
};

const openCreateModal = () => {
    if (props.moduleKey === 'role' || props.moduleKey === 'roles') {
        const prefix = window.location.pathname.split('/')[1] || 'superadmin';
        router.visit(`/${prefix}/role/create`);
        return;
    }
    if (props.moduleKey === 'umat' || props.moduleKey === 'data-umat') {
        const prefix = window.location.pathname.split('/')[1] || 'superadmin';
        router.visit(`/${prefix}/umat/create`);
        return;
    }
    if (['master-pastor', 'pastor', 'master_pastor'].includes(props.moduleKey)) {
        const prefix = window.location.pathname.split('/')[1] || 'superadmin';
        router.visit(`/${prefix}/master-pastor/create`);
        return;
    }
    if (['kk-katolik', 'kk', 'keluarga'].includes(props.moduleKey)) {
        const prefix = window.location.pathname.split('/')[1] || 'superadmin';
        router.visit(`/${prefix}/kk-katolik/create`);
        return;
    }
    if (props.moduleKey === 'konten') {
        const prefix = window.location.pathname.split('/')[1] || 'superadmin';
        router.visit(`/${prefix}/konten/create`);
        return;
    }
    if (props.moduleKey === 'galeri') {
        const prefix = window.location.pathname.split('/')[1] || 'superadmin';
        router.visit(`/${prefix}/galeri/create`);
        return;
    }

    modalMode.value = 'create';
    pastorRekanSearch.value = '';
    const initialKeuskupanId = props.keuskupanList?.[0]?.id_keuskupan || props.keuskupanList?.[0]?.id || '';
    const initialKeuskupan = props.keuskupanList?.[0];
    const initialKode = initialKeuskupan ? `${initialKeuskupan.kode_keuskupan || 'PRK'}-${Math.floor(100 + Math.random() * 900)}` : '';

    let initProvId = '';
    let initKabId = '';
    let initKecId = '';
    let initDesaId = '';
    let initProvName = '';
    let initKabName = '';
    let initKecName = '';
    let initDesaName = '';
    let initAlamat = '';

    if (props.moduleKey === 'keuskupan') {
        const ntt = props.provinsiList.find(p => p.nama_provinsi?.toLowerCase().includes('nusa tenggara timur') || p.nama_provinsi?.toLowerCase().includes('ntt')) || props.provinsiList?.[0];
        initProvId = ntt ? (ntt.id_provinsi || ntt.id) : '';
        initProvName = ntt ? ntt.nama_provinsi : 'Nusa Tenggara Timur';

        const kupang = props.kabupatenList.find(k => (String(k.provinsi_id) === String(initProvId)) && (k.nama_kabupaten?.toLowerCase().includes('kota kupang') || k.nama_kabupaten?.toLowerCase().includes('kupang'))) || props.kabupatenList.find(k => String(k.provinsi_id) === String(initProvId));
        initKabId = kupang ? (kupang.id_kabupaten || kupang.id) : '';
        initKabName = kupang ? kupang.nama_kabupaten : 'Kota Kupang';

        const oebobo = props.kecamatanList.find(kc => (String(kc.kabupaten_id) === String(initKabId)) && kc.nama_kecamatan?.toLowerCase().includes('oebobo')) || props.kecamatanList.find(kc => String(kc.kabupaten_id) === String(initKabId));
        initKecId = oebobo ? (oebobo.id_kecamatan || oebobo.id) : '';
        initKecName = oebobo ? oebobo.nama_kecamatan : 'Oebobo';

        const defDesa = (props.desaList || []).find(d => String(d.kecamatan_id) === String(initKecId));
        initDesaId = defDesa ? (defDesa.id_desa || defDesa.id) : '';
        initDesaName = defDesa ? defDesa.nama_desa : 'Oebobo';

        initAlamat = `Jl. Mgr. Sugiyopranoto No. 1, Kel. ${initDesaName}, Kec. ${initKecName}, ${initKabName}, ${initProvName}`;
    } else {
        const defaultProv = props.provinsiList?.[0];
        initProvId = defaultProv ? (defaultProv.id_provinsi || defaultProv.id) : '';
        initProvName = defaultProv ? defaultProv.nama_provinsi : '';
    }

    formData.value = {
        status: 'Aktif',
        status_paroki: 'Mandiri',
        keuskupan_id: initialKeuskupanId,
        dekenat_id: '',
        kode_keuskupan: '',
        nama_keuskupan: '',
        nama_latin: '',
        uskup: '',
        kode_kevikepan: '',
        nama_kevikepan: '',
        vikep: '',
        kode_paroki: initialKode,
        nama_paroki: '',
        pelindung_paroki: '',
        nama_pastor_paroki_aktif: '',
        selected_pastor_rekan: [],
        tanggal_berdiri: '',
        alamat: initAlamat,
        provinsi_id: initProvId,
        kabupaten_id: initKabId,
        kecamatan_id: initKecId,
        desa_id: initDesaId,
        provinsi: initProvName,
        kabupaten: initKabName,
        kecamatan: initKecName,
        desa: initDesaName,
        telepon: '',
        no_telp: '',
        whatsapp: '',
        email: '',
        website: '',
        keterangan: '',
        maps_url: '',
        latitude: '',
        longitude: '',
        maps_embed: '',
        logo: '',
    };

    if (props.moduleKey === 'kapela' || props.moduleKey === 'stasi') {
        const profileParoki = activeProfileParoki.value;
        const parokiId = activeProfileParokiId.value;
        const parokiKode = profileParoki ? (profileParoki.kode_paroki || '012.014') : '012.014';
        const randNum = String(Math.floor(1 + Math.random() * 99)).padStart(2, '0');

        formData.value.paroki_id = parokiId;
        formData.value.tipe = 'Stasi';
        formData.value.tipe_kapela = 'Stasi';
        formData.value.status = 'Aktif';
        formData.value.kode_kapela = `ST-${parokiKode}-${randNum}`;
        formData.value.nama_kapela = '';
        formData.value.pelindung = '';
        formData.value.pelindung_kapela = '';
        formData.value.penanggung_jawab = '';
        formData.value.alamat = initAlamat || 'Benlutu';
        formData.value.lokasi = initAlamat || 'Benlutu';
        formData.value.latitude = '-9.850000';
        formData.value.longitude = '124.300000';
        formData.value.warna_area = '#007bff';
        formData.value.maps_url = 'https://maps.google.com/?q=-9.850000,124.300000';
        formData.value.geojson = '';
        formData.value.keterangan = '';
        formData.value.sejarah = '';
        formData.value.visi = '';
        formData.value.misi = '';
    }

    if (props.moduleKey === 'wilayah') {
        const profileParoki = activeProfileParoki.value;
        const parokiId = activeProfileParokiId.value;
        const parokiKode = profileParoki ? (profileParoki.kode_paroki || '012.014') : '012.014';
        const randNum = String(Math.floor(1 + Math.random() * 99)).padStart(2, '0');

        formData.value.paroki_id = parokiId;
        formData.value.kapela_id = '';
        formData.value.status = 'Aktif';
        formData.value.kode_wilayah = `WIL-${parokiKode}-${randNum}`;
        formData.value.nama_wilayah = '';
        formData.value.ketua_wilayah = '';
        formData.value.no_hp = '';
        formData.value.alamat = initAlamat || 'Benlutu';
        formData.value.provinsi_id = initProvId;
        formData.value.kabupaten_id = initKabId;
        formData.value.kecamatan_id = initKecId;
        formData.value.desa_id = initDesaId;
        formData.value.keterangan = '';
        formData.value.deskripsi = '';
    }

    if (props.moduleKey === 'kub') {
        const profileParoki = activeProfileParoki.value;
        const parokiId = activeProfileParokiId.value;
        const parokiKode = profileParoki ? (profileParoki.kode_paroki || '012.014') : '012.014';
        const randNum = String(Math.floor(1 + Math.random() * 99)).padStart(2, '0');

        formData.value.paroki_id = parokiId;
        formData.value.kapela_id = '';
        formData.value.wilayah_id = '';
        formData.value.status = 'Aktif';
        formData.value.kode_kub = `KUB-${parokiKode}-${randNum}`;
        formData.value.nama_kub = '';
        formData.value.pelindung = '';
        formData.value.nama_pelindung = '';
        formData.value.ketua_kub = '';
        formData.value.no_hp = '';
        formData.value.alamat = initAlamat || 'Benlutu';
        formData.value.lokasi = '';
        formData.value.jadwal_ibadat = '';
        formData.value.provinsi_id = initProvId;
        formData.value.kabupaten_id = initKabId;
        formData.value.kecamatan_id = initKecId;
        formData.value.desa_id = initDesaId;
        formData.value.keterangan = '';
        formData.value.deskripsi = '';
    }

    if (props.moduleKey === 'direktori-dpp') {
        formData.value.nama_lengkap = '';
        formData.value.jabatan = 'Anggota Pleno';
        formData.value.seksi = 'Bidang Liturgi & Peribadatan';
        formData.value.periode = '2024 - 2027';
        formData.value.no_hp = '';
        formData.value.status = 'Aktif';
        formData.value.urutan = 1;
        formData.value.keterangan = '';
        formData.value.foto = '';
    }

    if (props.moduleKey === 'kuasi-paroki') {
        const firstParoki = props.parokiList?.[0];
        const randCode = `KP-${String(Math.floor(1 + Math.random() * 99)).padStart(2, '0')}`;
        formData.value.nama_kuasi = '';
        formData.value.NamaKuasiParoki = '';
        formData.value.kode_kuasi = randCode;
        formData.value.KodeKuasiParoki = randCode;
        formData.value.paroki_id = firstParoki ? (firstParoki.id_paroki || firstParoki.id) : '';
        formData.value.dekenat_id = '';
        formData.value.pastor_administrator = '';
        formData.value.PastorKuasiParoki = '';
        formData.value.pelindung = '';
        formData.value.status = 'Aktif';
        formData.value.lokasi = '';
        formData.value.AlamatKuasiParoki = '';
        formData.value.keterangan = '';
    }

    if (props.moduleKey === 'kolekte') {
        formData.value = {
            tanggal: new Date().toISOString().split('T')[0],
            kategori_misa: 'Misa Hari Minggu I (Pagi)',
            lokasi_misa: 'Gereja Paroki Benlutu',
            nominal: '',
            petugas_penghitung: '',
            keterangan: '',
        };
    }

    if (props.moduleKey === 'rapat' || props.moduleKey === 'rapat-notulen') {
        const today = new Date().toISOString().split('T')[0];
        formData.value = {
            agenda: '',
            tanggal: today,
            waktu: '19:00',
            lokasi: 'Aula Paroki',
            notulen: '',
            status: 'Aktif',
        };
    }

    if (props.moduleKey === 'iuran' || props.moduleKey === 'iuran-umat') {
        const today = new Date().toISOString().split('T')[0];
        const currentYear = new Date().getFullYear();
        const currentMonth = String(new Date().getMonth() + 1).padStart(2, '0');
        
        formData.value = {
            id_kk: '',
            no_kk: '',
            nama_kepala: '',
            jenis_iuran_id: props.jenisIuranList?.[0]?.id || '',
            nama_iuran: props.jenisIuranList?.[0]?.nama_iuran || '',
            tahun: currentYear,
            bulan: currentMonth,
            bulan_lunas: currentMonth,
            total_jumlah: props.jenisIuranList?.[0]?.nominal_default || '',
            jumlah: props.jenisIuranList?.[0]?.nominal_default || '',
            status_bayar: 'Lunas',
            status: 'Lunas',
            tanggal_bayar: today,
            metode_bayar: 'Tunai',
            kolektor: '',
            keterangan: '',
        };
    }

    if (props.moduleKey === 'kegiatan') {
        const today = new Date().toISOString().split('T')[0];
        formData.value = {
            nama_kegiatan: '',
            judul: '',
            kategori: 'Liturgi & Ibadah',
            tanggal_mulai: today,
            tanggal_selesai: today,
            waktu: '09:00',
            lokasi: 'Gereja Paroki',
            penyelenggara: 'DPP Paroki',
            status: 'Akan Datang',
            deskripsi: '',
            gambar: '',
            foto: '',
        };
    }

    if (props.moduleKey === 'lapak-produk') {
        formData.value.nama_produk = '';
        formData.value.kategori = 'Makanan & Minuman Olahan';
        formData.value.harga = '';
        formData.value.stok = 1;
        formData.value.satuan = 'Pcs';
        formData.value.penjual = '';
        formData.value.no_wa = '';
        formData.value.status_approval = 'Disetujui';
        formData.value.deskripsi = '';
        formData.value.foto = '';
    }

    if (props.moduleKey === 'konten') {
        const now = new Date();
        now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
        formData.value = {
            judul: '',
            slug: '',
            excerpt: '',
            isi: '',
            tipe: 'Berita',
            kategori_id: '',
            kategori: '',
            gambar: '',
            file_pdf: '',
            arsip_id: '',
            tags: '',
            status_publish: 'Publish',
            tanggal_publish: now.toISOString().slice(0, 16),
            is_featured: 0,
            embed_pdf: 1,
            penulis: props.penulisList?.[0] || page.props.auth?.user?.nama_lengkap || page.props.auth?.user?.name || 'Administrator',
        };
    }

    if (props.moduleKey === 'pengumuman') {
        const today = new Date().toISOString().split('T')[0];
        formData.value = {
            judul: '',
            tgl_tayang: today,
            status: 'Aktif',
            isi: '',
        };
    }

    if (props.moduleKey === 'renungan') {
        const today = new Date().toISOString().split('T')[0];
        formData.value = {
            judul: '',
            tanggal: today,
            bacaan_kitab_suci: '',
            isi: '',
        };
    }

    if (props.moduleKey === 'kronik') {
        const today = new Date().toISOString().split('T')[0];
        formData.value = {
            judul_kronik: '',
            tanggal_peristiwa: today,
            kategori_kronik: 'Pastoral',
            lokasi_peristiwa: 'Gereja Paroki',
            penulis: page.props.auth?.user?.nama_lengkap || page.props.auth?.user?.name || 'Sekretariat Paroki',
            status_publish: 'Publish',
            deskripsi: '',
        };
    }

    if (props.moduleKey === 'user') {
        formData.value = {
            nama_lengkap: '',
            username: '',
            email: '',
            no_hp: '',
            password: '',
            role_id: props.roleList?.[0]?.id || '',
            wilayah_id: '',
            kapela_id: '',
            kub_id: '',
            umat_id: '',
            status: 1,
            maintenance_access: 'Tidak',
            foto: '',
        };
        showPassword.value = false;
    }

    if (props.moduleKey === 'role' || props.moduleKey === 'roles') {
        formData.value.nama_role = '';
        formData.value.slug = '';
        formData.value.deskripsi = '';
        formData.value.status = 1;
        formData.value.permissions = ['lihat_umat', 'lihat_sakramen', 'lihat_keuangan'];
    }

    if (props.moduleKey === 'kabupaten') {
        const defaultProv = props.provinsiList?.find(p => p.nama_provinsi?.toLowerCase().includes('nusa tenggara timur') || p.nama_provinsi?.toLowerCase().includes('ntt')) || props.provinsiList?.[0];
        formData.value = {
            provinsi_id: defaultProv ? (defaultProv.id_provinsi || defaultProv.id) : '',
            nama_kabupaten: '',
            tipe: 'Kabupaten',
            kode_kabupaten: '',
            status: 'Aktif',
        };
    }

    if (props.moduleKey === 'kecamatan') {
        const defaultProv = props.provinsiList?.find(p => p.nama_provinsi?.toLowerCase().includes('nusa tenggara timur') || p.nama_provinsi?.toLowerCase().includes('ntt')) || props.provinsiList?.[0];
        const defaultProvId = defaultProv ? (defaultProv.id_provinsi || defaultProv.id) : '';
        const defaultKab = props.kabupatenList?.find(k => String(k.provinsi_id) === String(defaultProvId)) || props.kabupatenList?.[0];
        formData.value = {
            provinsi_id: defaultProvId,
            kabupaten_id: defaultKab ? (defaultKab.id_kabupaten || defaultKab.id) : '',
            nama_kecamatan: '',
            kode_kecamatan: '',
            status: 'Aktif',
        };
    }

    if (props.moduleKey === 'desa-kelurahan' || props.moduleKey === 'desa' || props.moduleKey === 'kelurahan') {
        const defaultProv = props.provinsiList?.find(p => p.nama_provinsi?.toLowerCase().includes('nusa tenggara timur') || p.nama_provinsi?.toLowerCase().includes('ntt')) || props.provinsiList?.[0];
        const defaultProvId = defaultProv ? (defaultProv.id_provinsi || defaultProv.id) : '';
        const defaultKab = props.kabupatenList?.find(k => String(k.provinsi_id) === String(defaultProvId)) || props.kabupatenList?.[0];
        const defaultKabId = defaultKab ? (defaultKab.id_kabupaten || defaultKab.id) : '';
        const defaultKec = (props.kecamatanList || []).find(kc => String(kc.kabupaten_id) === String(defaultKabId)) || props.kecamatanList?.[0];
        formData.value = {
            provinsi_id: defaultProvId,
            kabupaten_id: defaultKabId,
            kecamatan_id: defaultKec ? (defaultKec.id_kecamatan || defaultKec.id) : '',
            nama_desa: '',
            tipe: 'Desa',
            kode_desa: '',
            kode_pos: '',
            status: 'Aktif',
        };
    }

    if (props.moduleKey === 'direktori-dpp') {
        formData.value = {
            nama_lengkap: '',
            nama: '',
            jabatan: 'Anggota Pleno',
            bidang: 'Bidang Liturgi & Peribadatan',
            seksi: 'Bidang Liturgi & Peribadatan',
            periode: '2024 - 2027',
            no_hp: '',
            status: 'Aktif',
            status_aktif: 'Aktif',
            urutan: 1,
            foto: '',
            keterangan: '',
        };
    }

    if (props.moduleKey === 'direktori-katekis') {
        formData.value = {
            nama_lengkap: '',
            nama: '',
            jenis_katekis: 'Katekis Paroki',
            wilayah_pelayanan: '',
            sertifikasi: '',
            nomor_sk: '',
            no_hp: '',
            kontak: '',
            email: '',
            status_aktif: 'Aktif',
            status: 'Aktif',
            foto: '',
            keterangan: '',
        };
    }

    if (props.moduleKey === 'direktori-misdinar') {
        formData.value = {
            nama_lengkap: '',
            nama: '',
            tingkat: 'Junior',
            stasi_kapela_id: '',
            nama_orang_tua: '',
            no_hp_orang_tua: '',
            no_hp: '',
            kontak: '',
            tanggal_bergabung: new Date().toISOString().substring(0, 10),
            status_aktif: 'Aktif',
            status: 'Aktif',
            foto: '',
            catatan: '',
        };
    }

    if (props.moduleKey === 'provinsi') {
        formData.value = {
            nama_provinsi: '',
            kode_provinsi: '',
            status: 'Aktif',
        };
    }

    if (props.moduleKey === 'riwayat-mutasi-umat' || props.moduleKey === 'mutasi-umat' || props.moduleKey === 'riwayat-mutasi') {
        const today = new Date().toISOString().split('T')[0];
        formData.value = {
            umat_id: props.umatList?.[0]?.id || '',
            kk_id: props.umatList?.[0]?.kk_id || '',
            jenis_mutasi: isKubLevel.value ? 'Meninggal Dunia' : 'Mutasi Antar KUB (Dalam Paroki)',
            kub_asal_id: props.umatList?.[0]?.kub_id || '',
            kub_tujuan_id: '',
            wilayah_asal_id: props.umatList?.[0]?.wilayah_id || '',
            wilayah_tujuan_id: '',
            tgl_mutasi: today,
            no_surat_pindah: '',
            alasan: '',
        };
    }

    uploadedFile.value = null;
    previewImage.value = null;
    props.columns.forEach((col) => {
        if (formData.value[col.key] === undefined) {
            formData.value[col.key] = '';
        }
    });
    showFormModal.value = true;
};

const getCurrentLocation = () => {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            (pos) => {
                formData.value.latitude = pos.coords.latitude.toFixed(6);
                formData.value.longitude = pos.coords.longitude.toFixed(6);
                formData.value.maps_url = `https://maps.google.com/?q=${pos.coords.latitude.toFixed(6)},${pos.coords.longitude.toFixed(6)}`;
            },
            () => {}
        );
    }
};

const openEditModal = (item) => {
    if (props.moduleKey === 'role' || props.moduleKey === 'roles') {
        const prefix = window.location.pathname.split('/')[1] || 'superadmin';
        const roleId = item.id || item.id_role;
        router.visit(`/${prefix}/role/${roleId}/edit`);
        return;
    }
    if (props.moduleKey === 'umat' || props.moduleKey === 'data-umat') {
        const prefix = window.location.pathname.split('/')[1] || 'superadmin';
        const umatId = item.id || item.id_umat;
        router.visit(`/${prefix}/umat/${umatId}/edit`);
        return;
    }
    if (['master-pastor', 'pastor', 'master_pastor'].includes(props.moduleKey)) {
        const prefix = window.location.pathname.split('/')[1] || 'superadmin';
        const pastorId = item.id || item.id_pastor;
        router.visit(`/${prefix}/master-pastor/${pastorId}/edit`);
        return;
    }
    if (['kk-katolik', 'kk', 'keluarga'].includes(props.moduleKey)) {
        const prefix = window.location.pathname.split('/')[1] || 'superadmin';
        const kkId = item.id || item.id_keluarga;
        router.visit(`/${prefix}/kk-katolik/${kkId}/edit`);
        return;
    }
    if (props.moduleKey === 'konten') {
        const prefix = window.location.pathname.split('/')[1] || 'superadmin';
        const kontenId = item.id || item.id_konten;
        router.visit(`/${prefix}/konten/${kontenId}/edit`);
        return;
    }
    if (props.moduleKey === 'galeri') {
        const prefix = window.location.pathname.split('/')[1] || 'superadmin';
        const galeriId = item.id || item.id_galeri;
        router.visit(`/${prefix}/galeri/${galeriId}/edit`);
        return;
    }

    modalMode.value = 'edit';
    pastorRekanSearch.value = '';
    selectedItem.value = item;

    let rekanList = [];
    if (Array.isArray(item.nama_pastor_rekan)) {
        rekanList = item.nama_pastor_rekan;
    } else if (typeof item.nama_pastor_rekan === 'string' && item.nama_pastor_rekan.trim()) {
        rekanList = item.nama_pastor_rekan.split(',').map(s => s.trim()).filter(Boolean);
    }

    let provId = item.provinsi_id || (item.provinsi ? (item.provinsi.id_provinsi || item.provinsi.id) : '');
    if (!provId && item.provinsi && typeof item.provinsi === 'string') {
        const found = props.provinsiList.find(p => p.nama_provinsi?.toLowerCase() === item.provinsi.toLowerCase());
        if (found) provId = found.id_provinsi || found.id;
    }

    let kabId = item.kabupaten_id || (item.kabupaten ? (item.kabupaten.id_kabupaten || item.kabupaten.id) : '');
    if (!kabId && item.kabupaten && typeof item.kabupaten === 'string') {
        const found = props.kabupatenList.find(k => k.nama_kabupaten?.toLowerCase() === item.kabupaten.toLowerCase());
        if (found) kabId = found.id_kabupaten || found.id;
    }

    let kecId = item.kecamatan_id || (item.kecamatan ? (item.kecamatan.id_kecamatan || item.kecamatan.id) : '');
    if (!kecId && item.kecamatan && typeof item.kecamatan === 'string') {
        const found = props.kecamatanList.find(k => k.nama_kecamatan?.toLowerCase() === item.kecamatan.toLowerCase());
        if (found) kecId = found.id_kecamatan || found.id;
    }

    let desaId = item.desa_id || (item.desa ? (item.desa.id_desa || item.desa.id) : '');
    if (!desaId && item.desa && typeof item.desa === 'string') {
        const found = (props.desaList || []).find(d => d.nama_desa?.toLowerCase() === item.desa.toLowerCase());
        if (found) desaId = found.id_desa || found.id;
    }

    if (props.moduleKey === 'direktori-dpp') {
        const seksiVal = item.bidang || item.seksi || 'Bidang Liturgi & Peribadatan';
        formData.value = {
            id_dpp: item.id_dpp || item.id,
            id: item.id_dpp || item.id,
            nama_lengkap: item.nama_lengkap || item.nama || '',
            nama: item.nama_lengkap || item.nama || '',
            jabatan: item.jabatan || 'Anggota Pleno',
            bidang: seksiVal,
            seksi: seksiVal,
            periode: item.periode || (item.periode_mulai && item.periode_selesai ? `${item.periode_mulai} - ${item.periode_selesai}` : '2024 - 2027'),
            no_hp: item.no_hp || item.kontak || '',
            status: item.status || item.status_aktif || 'Aktif',
            status_aktif: item.status_aktif || item.status || 'Aktif',
            urutan: item.urutan !== undefined ? item.urutan : 1,
            foto: item.foto || '',
            keterangan: item.keterangan || '',
        };
        showFormModal.value = true;
        return;
    }

    if (props.moduleKey === 'direktori-katekis') {
        formData.value = {
            id_katekis: item.id_katekis || item.id,
            id: item.id_katekis || item.id,
            nama_lengkap: item.nama_lengkap || item.nama || '',
            nama: item.nama_lengkap || item.nama || '',
            jenis_katekis: item.jenis_katekis || 'Katekis Paroki',
            wilayah_pelayanan: item.wilayah_pelayanan || '',
            sertifikasi: item.sertifikasi || '',
            nomor_sk: item.nomor_sk || '',
            no_hp: item.no_hp || item.kontak || '',
            kontak: item.no_hp || item.kontak || '',
            email: item.email || '',
            status_aktif: (item.status_aktif == 1 || item.status_aktif === '1' || item.status_aktif === 'Aktif' || item.status === 'Aktif') ? 'Aktif' : 'Nonaktif',
            status: (item.status_aktif == 1 || item.status_aktif === '1' || item.status_aktif === 'Aktif' || item.status === 'Aktif') ? 'Aktif' : 'Nonaktif',
            foto: item.foto || '',
            keterangan: item.keterangan || '',
        };
        showFormModal.value = true;
        return;
    }

    if (props.moduleKey === 'direktori-misdinar') {
        formData.value = {
            id_misdinar: item.id_misdinar || item.id,
            id: item.id_misdinar || item.id,
            nama_lengkap: item.nama_lengkap || item.nama || '',
            nama: item.nama_lengkap || item.nama || '',
            tingkat: item.tingkat || 'Junior',
            stasi_kapela_id: item.stasi_kapela_id || '',
            nama_orang_tua: item.nama_orang_tua || '',
            no_hp_orang_tua: item.no_hp_orang_tua || '',
            no_hp: item.no_hp || item.kontak || '',
            kontak: item.no_hp || item.kontak || '',
            tanggal_bergabung: item.tanggal_bergabung ? String(item.tanggal_bergabung).substring(0, 10) : '',
            status_aktif: (item.status_aktif == 1 || item.status_aktif === '1' || item.status_aktif === 'Aktif' || item.status === 'Aktif') ? 'Aktif' : 'Nonaktif',
            status: (item.status_aktif == 1 || item.status_aktif === '1' || item.status_aktif === 'Aktif' || item.status === 'Aktif') ? 'Aktif' : 'Nonaktif',
            foto: item.foto || '',
            catatan: item.catatan || item.keterangan || '',
        };
        showFormModal.value = true;
        return;
    }

    if (props.moduleKey === 'wilayah') {
        formData.value = {
            id: item.id || item.id_wilayah,
            id_wilayah: item.id || item.id_wilayah,
            paroki_id: item.paroki_id || activeProfileParokiId.value,
            kapela_id: item.kapela_id || '',
            kode_wilayah: item.kode_wilayah || item.kode || '',
            nama_wilayah: item.nama_wilayah || item.nama || '',
            ketua_wilayah: item.ketua_wilayah || item.nama_ketua || item.penanggung_jawab || '',
            no_hp: item.no_hp || item.kontak || item.telepon || '',
            alamat: item.alamat || item.deskripsi || '',
            provinsi_id: provId,
            kabupaten_id: kabId,
            kecamatan_id: kecId,
            desa_id: desaId,
            status: item.status || 'Aktif',
            keterangan: item.keterangan || item.deskripsi || '',
        };
        showFormModal.value = true;
        return;
    }

    if (props.moduleKey === 'kub') {
        formData.value = {
            id: item.id,
            paroki_id: item.paroki_id || activeProfileParokiId.value,
            kapela_id: item.kapela_id || '',
            wilayah_id: item.wilayah_id || '',
            kode_kub: item.kode_kub || item.kode || '',
            nama_kub: item.nama_kub || item.nama || '',
            pelindung: item.pelindung || item.nama_pelindung || '',
            nama_pelindung: item.pelindung || item.nama_pelindung || '',
            ketua_kub: item.ketua_kub || item.penanggung_jawab || item.nama_ketua || '',
            no_hp: item.no_hp || item.kontak || item.telepon || '',
            alamat: item.alamat || '',
            lokasi: item.lokasi || '',
            jadwal_ibadat: item.jadwal_ibadat || '',
            provinsi_id: provId,
            kabupaten_id: kabId,
            kecamatan_id: kecId,
            desa_id: desaId,
            status: item.status || 'Aktif',
            keterangan: item.keterangan || item.deskripsi || '',
        };
        showFormModal.value = true;
        return;
    }

    if (props.moduleKey === 'kabupaten') {
        formData.value = {
            id_kabupaten: item.id_kabupaten || item.id,
            provinsi_id: provId,
            nama_kabupaten: item.nama_kabupaten || item.nama || '',
            tipe: item.tipe || 'Kabupaten',
            kode_kabupaten: item.kode_kabupaten || item.kode || '',
            status: item.status || 'Aktif',
        };
        showFormModal.value = true;
        return;
    }

    if (props.moduleKey === 'kecamatan') {
        const kabObj = props.kabupatenList?.find(k => String(k.id_kabupaten || k.id) === String(kabId));
        const effectiveProvId = provId || (kabObj ? kabObj.provinsi_id : '');
        formData.value = {
            id_kecamatan: item.id_kecamatan || item.id,
            provinsi_id: effectiveProvId,
            kabupaten_id: kabId,
            nama_kecamatan: item.nama_kecamatan || item.nama || '',
            kode_kecamatan: item.kode_kecamatan || item.kode || '',
            status: item.status || 'Aktif',
        };
        showFormModal.value = true;
        return;
    }

    if (props.moduleKey === 'desa-kelurahan' || props.moduleKey === 'desa' || props.moduleKey === 'kelurahan') {
        const kecObj = (props.kecamatanList || []).find(k => String(k.id_kecamatan || k.id) === String(kecId));
        const effectiveKabId = kabId || (kecObj ? kecObj.kabupaten_id : '');
        const kabObj = props.kabupatenList?.find(k => String(k.id_kabupaten || k.id) === String(effectiveKabId));
        const effectiveProvId = provId || (kabObj ? kabObj.provinsi_id : '');
        formData.value = {
            id_desa: item.id_desa || item.id,
            provinsi_id: effectiveProvId,
            kabupaten_id: effectiveKabId,
            kecamatan_id: kecId,
            nama_desa: item.nama_desa || item.nama || '',
            tipe: item.tipe || 'Desa',
            kode_desa: item.kode_desa || item.kode || '',
            kode_pos: item.kode_pos || '',
            status: item.status || 'Aktif',
        };
        showFormModal.value = true;
        return;
    }

    if (props.moduleKey === 'provinsi') {
        formData.value = {
            id_provinsi: item.id_provinsi || item.id,
            nama_provinsi: item.nama_provinsi || item.nama || '',
            kode_provinsi: item.kode_provinsi || item.kode || '',
            status: item.status || 'Aktif',
        };
        showFormModal.value = true;
        return;
    }

    if (props.moduleKey === 'riwayat-mutasi-umat' || props.moduleKey === 'mutasi-umat' || props.moduleKey === 'riwayat-mutasi') {
        formData.value = {
            id: item.id,
            umat_id: item.umat_id || (item.umat ? item.umat.id : ''),
            kk_id: item.kk_id || '',
            jenis_mutasi: item.jenis_mutasi || 'Mutasi Antar KUB (Dalam Paroki)',
            kub_asal_id: item.kub_asal_id || '',
            kub_tujuan_id: item.kub_tujuan_id || '',
            wilayah_asal_id: item.wilayah_asal_id || '',
            wilayah_tujuan_id: item.wilayah_tujuan_id || '',
            tgl_mutasi: item.tgl_mutasi ? String(item.tgl_mutasi).substring(0, 10) : '',
            no_surat_pindah: item.no_surat_pindah || '',
            alasan: item.alasan || item.keterangan || '',
        };
        showFormModal.value = true;
        return;
    }

    formData.value = {
        ...item,
        status: item.status || 'Aktif',
        status_paroki: item.status_paroki || 'Mandiri',
        keuskupan_id: item.keuskupan_id || (item.keuskupan ? (item.keuskupan.id_keuskupan || item.keuskupan.id) : (props.keuskupanList?.[0]?.id_keuskupan || '')),
        dekenat_id: item.dekenat_id || (item.dekenat ? (item.dekenat.id_kevikepan || item.dekenat.id) : ''),
        kode_keuskupan: item.kode_keuskupan || item.kode || '',
        nama_keuskupan: item.nama_keuskupan || item.nama || '',
        nama_latin: item.nama_latin || item.nama_keuskupan_latin || '',
        uskup: item.uskup || item.nama_uskup || '',
        kode_kevikepan: item.kode_kevikepan || item.kode_dekenat || item.kode || '',
        nama_kevikepan: item.nama_kevikepan || item.nama_dekenat || item.nama || '',
        vikep: item.vikep || item.nama_vikep || item.nama_deken || item.deken || '',
        kode_paroki: item.kode_paroki || item.kode || '',
        nama_paroki: item.nama_paroki || item.nama || '',
        pelindung_paroki: item.pelindung_paroki || item.pelindung || '',
        nama_pastor_paroki_aktif: item.nama_pastor_paroki_aktif || item.pastor_paroki || '',
        selected_pastor_rekan: rekanList,
        tanggal_berdiri: item.tanggal_berdiri ? String(item.tanggal_berdiri).substring(0, 10) : '',
        provinsi_id: provId,
        kabupaten_id: kabId,
        kecamatan_id: kecId,
        desa_id: desaId,
        provinsi: typeof item.provinsi === 'string' ? item.provinsi : (item.provinsi?.nama_provinsi || ''),
        kabupaten: typeof item.kabupaten === 'string' ? item.kabupaten : (item.kabupaten?.nama_kabupaten || ''),
        kecamatan: typeof item.kecamatan === 'string' ? item.kecamatan : (item.kecamatan?.nama_kecamatan || ''),
        desa: typeof item.desa === 'string' ? item.desa : (item.desa?.nama_desa || ''),
        telepon: item.telepon || item.no_telp || '',
        no_telp: item.no_telp || item.telepon || '',
        whatsapp: item.whatsapp || '',
        email: item.email || '',
        website: item.website || '',
        keterangan: item.keterangan || '',
        maps_url: item.maps_url || '',
        latitude: item.latitude || '-9.850000',
        longitude: item.longitude || '124.300000',
        maps_embed: item.maps_embed || '',
        paroki_id: item.paroki_id || '',
        tipe: item.tipe || item.tipe_kapela || 'Stasi',
        tipe_kapela: item.tipe_kapela || item.tipe || 'Stasi',
        kode_kapela: item.kode_kapela || item.kode || '',
        nama_kapela: item.nama_kapela || item.nama || '',
        pelindung_kapela: item.pelindung_kapela || item.pelindung || '',
        lokasi: item.lokasi || item.alamat || '',
        penanggung_jawab: item.penanggung_jawab || '',
        warna_area: item.warna_area || '#007bff',
        geojson: item.geojson || item.polygon || '',
        sejarah: item.sejarah || item.deskripsi || '',
        visi: item.visi || '',
        misi: item.misi || '',
        seksi: item.seksi || item.bidang || 'Bidang Liturgi & Peribadatan',
        jabatan: item.jabatan || 'Anggota Pleno',
        periode: item.periode || '2024 - 2027',
        urutan: item.urutan !== undefined ? item.urutan : 1,
        foto: item.foto || '',
        nama_kuasi: item.nama_kuasi || item.NamaKuasiParoki || '',
        NamaKuasiParoki: item.NamaKuasiParoki || item.nama_kuasi || '',
        kode_kuasi: item.kode_kuasi || item.KodeKuasiParoki || '',
        KodeKuasiParoki: item.KodeKuasiParoki || item.kode_kuasi || '',
        pastor_administrator: item.pastor_administrator || item.PastorKuasiParoki || '',
        PastorKuasiParoki: item.PastorKuasiParoki || item.pastor_administrator || '',
        AlamatKuasiParoki: item.AlamatKuasiParoki || item.lokasi || item.alamat || '',
        nama_produk: item.nama_produk || '',
        kategori: item.kategori || 'Makanan & Minuman Olahan',
        harga: item.harga || '',
        stok: item.stok !== undefined ? item.stok : 1,
        satuan: item.satuan || 'Pcs',
        penjual: item.penjual || '',
        no_wa: item.no_wa || item.kontak || '',
        status_approval: item.status_approval || 'Disetujui',
        deskripsi: item.deskripsi || '',
        nama_role: item.nama_role || '',
        slug: item.slug || '',
        permissions: Array.isArray(item.permissions) ? item.permissions : (typeof item.permissions === 'string' ? JSON.parse(item.permissions || '[]') : []),
    };
    if (props.moduleKey === 'kapela' || props.moduleKey === 'stasi') {
        formData.value.paroki_id = activeProfileParokiId.value;
    }
    if (props.moduleKey === 'rapat' || props.moduleKey === 'rapat-notulen') {
        let tgl = item.tanggal || '';
        if (tgl && tgl.includes('T')) {
            tgl = tgl.split('T')[0];
        }
        formData.value = {
            id: item.id || item.id_rapat,
            agenda: item.agenda || '',
            tanggal: tgl,
            waktu: item.waktu || '19:00',
            lokasi: item.lokasi || '',
            notulen: item.notulen || '',
            status: item.status || 'Aktif',
        };
    }
    if (props.moduleKey === 'kegiatan') {
        let tglMulai = item.tanggal_mulai || item.tanggal || '';
        if (tglMulai && tglMulai.includes('T')) tglMulai = tglMulai.split('T')[0];
        let tglSelesai = item.tanggal_selesai || '';
        if (tglSelesai && tglSelesai.includes('T')) tglSelesai = tglSelesai.split('T')[0];
        formData.value = {
            id: item.id || item.id_kegiatan,
            nama_kegiatan: item.nama_kegiatan || item.judul || '',
            judul: item.judul || item.nama_kegiatan || '',
            kategori: item.kategori || 'Liturgi & Ibadah',
            tanggal_mulai: tglMulai,
            tanggal_selesai: tglSelesai || tglMulai,
            waktu: item.waktu || item.jam || '09:00',
            lokasi: item.lokasi || '',
            penyelenggara: item.penyelenggara || item.penanggung_jawab || '',
            status: item.status || 'Akan Datang',
            deskripsi: item.deskripsi || item.keterangan || '',
            gambar: item.gambar || item.foto || item.poster || '',
            foto: item.foto || item.gambar || '',
        };
        previewImage.value = item.gambar ? getImageUrl(item.gambar) : (item.foto ? getImageUrl(item.foto) : null);
    }
    if (props.moduleKey === 'kolekte') {
        let tgl = item.tanggal || '';
        if (tgl && tgl.includes('T')) tgl = tgl.split('T')[0];
        formData.value = {
            id: item.id,
            tanggal: tgl,
            kategori_misa: item.kategori_misa || 'Misa Hari Minggu I (Pagi)',
            lokasi_misa: item.lokasi_misa || 'Gereja Paroki Benlutu',
            nominal: item.nominal || '',
            petugas_penghitung: item.petugas_penghitung || '',
            keterangan: item.keterangan || '',
        };
    }
    if (props.moduleKey === 'konten') {
        const publishDate = item.tanggal_publish
            ? new Date(item.tanggal_publish)
            : (item.created_at ? new Date(item.created_at) : null);
        if (publishDate && !Number.isNaN(publishDate.getTime())) {
            publishDate.setMinutes(publishDate.getMinutes() - publishDate.getTimezoneOffset());
        }
        formData.value = {
            id: item.id,
            judul: item.judul || '',
            slug: item.slug || '',
            excerpt: item.excerpt || '',
            isi: item.isi || item.konten || '',
            tipe: item.tipe || 'Berita',
            kategori_id: item.kategori_id || '',
            kategori: item.kategori || '',
            gambar: item.gambar || '',
            file_pdf: item.file_pdf || '',
            arsip_id: item.arsip_id || item.arsip_digital_id || '',
            tags: item.tags || '',
            status_publish: item.status_publish || item.status || 'Publish',
            tanggal_publish: publishDate && !Number.isNaN(publishDate.getTime()) ? publishDate.toISOString().slice(0, 16) : '',
            is_featured: item.is_featured ? 1 : 0,
            embed_pdf: item.embed_pdf === 0 || item.embed_pdf === false ? 0 : 1,
            penulis: item.penulis || props.penulisList?.[0] || 'Administrator',
        };
    }
    if (props.moduleKey === 'pengumuman') {
        let tgl = item.tgl_tayang || item.tanggal || '';
        if (tgl && tgl.includes('T')) tgl = tgl.split('T')[0];
        formData.value = {
            id: item.id || item.id_pengumuman,
            judul: item.judul || item.judul_pengumuman || '',
            tgl_tayang: tgl,
            status: item.status || 'Aktif',
            isi: item.isi || item.deskripsi || item.konten || '',
        };
    }
    if (props.moduleKey === 'renungan') {
        let tgl = item.tanggal || '';
        if (tgl && tgl.includes('T')) tgl = tgl.split('T')[0];
        formData.value = {
            id: item.id || item.id_renungan,
            judul: item.judul || '',
            tanggal: tgl,
            bacaan_kitab_suci: item.bacaan_kitab_suci || item.bacaan || '',
            isi: item.isi || item.isi_renungan || item.renungan || '',
        };
    }
    if (props.moduleKey === 'kronik') {
        let tgl = item.tanggal_peristiwa || item.tanggal || '';
        if (tgl && tgl.includes('T')) tgl = tgl.split('T')[0];
        formData.value = {
            id: item.id || item.id_kronik,
            judul_kronik: item.judul_kronik || item.judul || '',
            tanggal_peristiwa: tgl,
            kategori_kronik: item.kategori_kronik || item.kategori || 'Pastoral',
            lokasi_peristiwa: item.lokasi_peristiwa || item.lokasi || 'Gereja Paroki',
            penulis: item.penulis || 'Sekretariat Paroki',
            status_publish: item.status_publish || item.status || 'Publish',
            deskripsi: item.deskripsi || item.isi || item.uraian || '',
        };
    }
    if (props.moduleKey === 'iuran' || props.moduleKey === 'iuran-umat') {
        let tgl = item.tanggal_bayar || item.tanggal || '';
        if (tgl && tgl.includes('T')) tgl = tgl.split('T')[0];

        let rawB = String(item.bulan || item.bulan_lunas || '01');
        const monthNames = {
            'januari': '01', 'februari': '02', 'maret': '03', 'april': '04',
            'mei': '05', 'juni': '06', 'juli': '07', 'agustus': '08',
            'september': '09', 'oktober': '10', 'november': '11', 'desember': '12'
        };
        const cleanB = monthNames[rawB.toLowerCase()] || (rawB.length <= 2 ? rawB.padStart(2, '0') : '01');

        formData.value = {
            id: item.id,
            id_kk: item.id_kk || item.kk_id || '',
            no_kk: item.no_kk || item.no_kk_kw || '',
            nama_kepala: item.nama_kepala || '',
            jenis_iuran_id: item.jenis_iuran_id || '',
            nama_iuran: item.nama_iuran || '',
            tahun: item.tahun || 2026,
            bulan: cleanB,
            bulan_lunas: cleanB,
            total_jumlah: item.total_jumlah || item.jumlah || '',
            jumlah: item.jumlah || item.total_jumlah || '',
            status_bayar: (item.status_bayar === 'lunas' || item.status === 'lunas') ? 'Lunas' : (item.status_bayar || item.status || 'Lunas'),
            status: item.status || 'lunas',
            tanggal_bayar: tgl,
            metode_bayar: (item.metode_bayar === 'tunai' ? 'Tunai' : (item.metode_bayar === 'transfer' ? 'Transfer Bank' : (item.metode_bayar || 'Tunai'))),
            kolektor: item.kolektor || item.petugas || '',
            keterangan: item.keterangan || '',
        };
    }
    if (props.moduleKey === 'user') {
        formData.value = {
            id: item.id,
            nama_lengkap: item.nama_lengkap || item.name || '',
            username: item.username || '',
            email: item.email || '',
            no_hp: item.no_hp || '',
            password: '',
            role_id: item.role_id || item.role?.id || '',
            wilayah_id: item.wilayah_id || '',
            kapela_id: item.kapela_id || '',
            kub_id: item.kub_id || '',
            umat_id: item.umat_id || '',
            status: item.status ? 1 : 0,
            maintenance_access: item.maintenance_access || 'Tidak',
            foto: item.foto || '',
        };
        showPassword.value = false;
    }
    uploadedFile.value = null;
    previewImage.value = item.logo ? getImageUrl(item.logo) : (item.foto ? getImageUrl(item.foto) : (item.gambar ? getImageUrl(item.gambar) : null));
    showFormModal.value = true;
};

onMounted(() => {
    const urlParams = new URLSearchParams(window.location.search);
    const editId = urlParams.get('edit_id') || urlParams.get('edit');
    if (editId && props.items && props.items.data) {
        const itemToEdit = props.items.data.find(
            (i) => String(i.id_paroki || i.id || i.id_dpp || i.id_keuskupan || i.id_dekenat) === String(editId)
        );
        if (itemToEdit) {
            openEditModal(itemToEdit);
        }
    }
});

const resolveEntityId = (item) => {
    if (!item) return '';
    if (item.uuid) return item.uuid;
    if (item.id !== undefined && item.id !== null && item.id !== '') return item.id;
    if (item.id_desa !== undefined && item.id_desa !== null && item.id_desa !== '') return item.id_desa;
    if (item.id_kecamatan !== undefined && item.id_kecamatan !== null && item.id_kecamatan !== '') return item.id_kecamatan;
    if (item.id_kabupaten !== undefined && item.id_kabupaten !== null && item.id_kabupaten !== '') return item.id_kabupaten;
    if (item.id_provinsi !== undefined && item.id_provinsi !== null && item.id_provinsi !== '') return item.id_provinsi;
    if (item.id_kub !== undefined && item.id_kub !== null && item.id_kub !== '') return item.id_kub;
    if (item.id_wilayah !== undefined && item.id_wilayah !== null && item.id_wilayah !== '') return item.id_wilayah;
    if (item.id_lingkungan !== undefined && item.id_lingkungan !== null && item.id_lingkungan !== '') return item.id_lingkungan;
    if (item.id_kapela !== undefined && item.id_kapela !== null && item.id_kapela !== '') return item.id_kapela;
    if (item.id_stasi !== undefined && item.id_stasi !== null && item.id_stasi !== '') return item.id_stasi;
    if (item.id_paroki !== undefined && item.id_paroki !== null && item.id_paroki !== '') return item.id_paroki;
    if (item.id_kuasi !== undefined && item.id_kuasi !== null && item.id_kuasi !== '') return item.id_kuasi;
    if (item.id_kuasi_paroki !== undefined && item.id_kuasi_paroki !== null && item.id_kuasi_paroki !== '') return item.id_kuasi_paroki;
    if (item.IdKuasiParoki !== undefined && item.IdKuasiParoki !== null && item.IdKuasiParoki !== '') return item.IdKuasiParoki;
    if (item.id_dekenat !== undefined && item.id_dekenat !== null && item.id_dekenat !== '') return item.id_dekenat;
    if (item.id_kevikepan !== undefined && item.id_kevikepan !== null && item.id_kevikepan !== '') return item.id_kevikepan;
    if (item.id_keuskupan !== undefined && item.id_keuskupan !== null && item.id_keuskupan !== '') return item.id_keuskupan;
    if (item.id_dpp !== undefined && item.id_dpp !== null && item.id_dpp !== '') return item.id_dpp;
    if (item.id_role !== undefined && item.id_role !== null && item.id_role !== '') return item.id_role;
    if (item.slug !== undefined && item.slug !== null && item.slug !== '') return item.slug;
    for (const k of Object.keys(item)) {
        if (k.startsWith('id_') || k.startsWith('Id') || k.endsWith('_id')) {
            if (item[k] !== undefined && item[k] !== null && item[k] !== '') return item[k];
        }
    }
    return '';
};

const submitForm = () => {
    isSubmitting.value = true;
    const currentPath = window.location.pathname.replace(/\/$/, '');
    const itemId = resolveEntityId(selectedItem.value);
    const url = modalMode.value === 'create' ? currentPath : `${currentPath}/${itemId}`;

    if (props.moduleKey === 'iuran' || props.moduleKey === 'iuran-umat') {
        if (formData.value.total_jumlah !== undefined && formData.value.total_jumlah !== null && formData.value.total_jumlah !== '') {
            formData.value.jumlah = formData.value.total_jumlah;
        }
    }

    if (props.moduleKey === 'direktori-dpp') {
        const sVal = formData.value.seksi || formData.value.bidang;
        if (sVal) {
            formData.value.seksi = sVal;
            formData.value.bidang = sVal;
        }
        if (formData.value.nama_lengkap) {
            formData.value.nama = formData.value.nama_lengkap;
        }
        if (formData.value.status) {
            formData.value.status_aktif = formData.value.status;
        }
    }

    const payload = new FormData();
    Object.keys(formData.value).forEach((key) => {
        if (formData.value[key] !== null && formData.value[key] !== undefined) {
            if (key === 'selected_pastor_rekan') {
                payload.append('nama_pastor_rekan', formData.value[key].join(', '));
            } else if (Array.isArray(formData.value[key])) {
                payload.append(key, formData.value[key].join(', '));
            } else {
                payload.append(key, formData.value[key]);
            }
        }
    });

    if (modalMode.value === 'edit') {
        payload.append('_method', 'PUT');
    }

    router.post(url, payload, {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            showFormModal.value = false;
            isSubmitting.value = false;
            const isKkOrUmat = ['kk-katolik', 'kk', 'keluarga', 'umat', 'data-umat', 'jiwa'].includes(props.moduleKey);
            const actionText = modalMode.value === 'edit' ? 'berhasil diperbarui' : 'berhasil disimpan';
            const labelText = isKkOrUmat 
                ? (['kk-katolik', 'kk', 'keluarga'].includes(props.moduleKey) ? 'Data Kartu Keluarga dan Anggota Keluarga' : 'Data Anggota Keluarga / Umat')
                : (props.title || 'Data');
            triggerToast(`${labelText} ${actionText}!`, 'success');
        },
        onError: () => {
            isSubmitting.value = false;
        }
    });
};

const moduleBasePath = computed(() => window.location.pathname.replace(/\/$/, ''));

const exportModuleUrl = (format) => {
    const params = new URLSearchParams();
    if (search.value) params.set('search', search.value);
    if (isKubReadOnlyScope.value) {
        const kubId = resolvedKub.value?.id || kubFilter.value;
        if (kubId) params.set('kub_id', kubId);
    } else {
        if (wilayahFilter.value) params.set('wilayah_id', wilayahFilter.value);
        if (kapelaFilter.value) params.set('kapela_id', kapelaFilter.value);
        if (kubFilter.value) params.set('kub_id', kubFilter.value);
    }
    return `${moduleBasePath.value}/export/${format}${params.toString() ? `?${params.toString()}` : ''}`;
};

const isImporting = ref(false);

const triggerImportFile = () => {
    importFileInput.value?.click();
};

const handleImportFile = (event) => {
    const file = event.target.files?.[0];
    if (!file) return;

    isImporting.value = true;
    const payload = new FormData();
    payload.append('file', file);

    router.post(`${moduleBasePath.value}/import`, payload, {
        forceFormData: true,
        preserveScroll: true,
        onFinish: () => {
            isImporting.value = false;
            event.target.value = '';
        },
    });
};

const resetUserPassword = (item) => {
    const newPassword = `SIP-${Math.random().toString(36).slice(2, 8).toUpperCase()}`;
    router.post(`${moduleBasePath.value}/${item.id}/reset-password`, { password: newPassword }, {
        preserveScroll: true,
    });
};

const toggleUserStatus = (item) => {
    if (isSelfUser(item)) return;
    router.post(`${moduleBasePath.value}/${item.id}/toggle-status`, {}, {
        preserveScroll: true,
    });
};

const impersonateUser = (item) => {
    if (isSelfUser(item)) return;
    const name = item.nama_lengkap || item.name || item.username;
    if (confirm(`Apakah Anda ingin masuk dan melihat sistem langsung sebagai ${name} (${item.role?.nama_role || 'Pengguna'})?`)) {
        router.post(`${moduleBasePath.value}/${item.id}/impersonate`);
    }
};

// Bulk Selection State
const selectedIds = ref([]);
const showBulkDeleteModal = ref(false);
const isBulkDeleting = ref(false);

const selectableItems = computed(() => {
    return (props.items?.data || []).filter(item => !isSelfUser(item));
});

const isAllSelected = computed(() => {
    if (selectableItems.value.length === 0) return false;
    return selectableItems.value.every(item => selectedIds.value.includes(resolveEntityId(item)));
});

const isPartiallySelected = computed(() => {
    return selectedIds.value.length > 0 && !isAllSelected.value;
});

const toggleSelectAll = () => {
    if (isAllSelected.value) {
        selectedIds.value = [];
    } else {
        selectedIds.value = selectableItems.value.map(item => resolveEntityId(item)).filter(Boolean);
    }
};

const toggleSelectItem = (id) => {
    if (!id) return;
    const idx = selectedIds.value.indexOf(id);
    if (idx > -1) {
        selectedIds.value.splice(idx, 1);
    } else {
        selectedIds.value.push(id);
    }
};

const confirmBulkDelete = () => {
    if (selectedIds.value.length === 0) return;
    isBulkDeleting.value = true;
    const currentPath = window.location.pathname.replace(/\/$/, '');
    router.post(`${currentPath}/bulk-delete`, {
        ids: selectedIds.value,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            showBulkDeleteModal.value = false;
            selectedIds.value = [];
            isBulkDeleting.value = false;
        },
        onError: () => {
            isBulkDeleting.value = false;
        },
        onFinish: () => {
            isBulkDeleting.value = false;
        }
    });
};

const confirmDelete = () => {
    if (!selectedItem.value) return;
    if (isSelfUser(selectedItem.value)) {
        showDeleteModal.value = false;
        return;
    }
    const currentPath = window.location.pathname.replace(/\/$/, '');
    const itemId = resolveEntityId(selectedItem.value);
    router.delete(`${currentPath}/${itemId}`, {
        preserveScroll: true,
        onSuccess: () => {
            showDeleteModal.value = false;
        }
    });
};

const openDetailModal = (item) => {
    selectedItem.value = item;
    showDetailModal.value = true;
};

const openDeleteModal = (item) => {
    selectedItem.value = item;
    showDeleteModal.value = true;
};

const refreshData = () => {
    isReloadingData.value = true;
    router.reload({
        preserveScroll: true,
        preserveState: false,
        onFinish: () => {
            setTimeout(() => {
                isReloadingData.value = false;
            }, 300);
        },
    });
};

const getImageUrl = (path) => {
    if (!path || typeof path !== 'string') return '';
    if (path.startsWith('http://') || path.startsWith('https://')) return path;
    const clean = path.replace(/^\/?(public\/)?/, '').replace(/^\//, '');
    // Bare filename (no directory, e.g. CI3 hashed photo like 195b6ba...jpg)
    if (!clean.includes('/')) {
        if (['konten', 'artikel', 'berita'].includes(props.moduleKey)) {
            return `/uploads/konten/${clean}`;
        }
        if (['pengumuman'].includes(props.moduleKey)) {
            return `/uploads/pengumuman/${clean}`;
        }
        if (['galeri'].includes(props.moduleKey)) {
            return `/uploads/galeri/${clean}`;
        }
        if (['umat', 'data-umat', 'jiwa'].includes(props.moduleKey)) {
            return `/uploads/umat/${clean}`;
        }
        if (['kk-katolik', 'kk', 'keluarga'].includes(props.moduleKey)) {
            return `/uploads/kk_katolik/${clean}`;
        }
        return `/foto-pastor/${clean}`;
    }
    return `/${clean}`;
};

const getDefaultAvatarByGender = (gender, ageOrBirthDate = null) => {
    return getDefaultAvatar(gender, ageOrBirthDate);
};

const getAvatarUrl = (item, colKey = 'foto') => {
    if (!item) return '/images/laki-laki.jpg';

    // 1. Keuskupan / Paroki logos
    if (props.moduleKey === 'keuskupan') {
        return item.logo ? getImageUrl(item.logo) : (item.logo_url || '/images/logo-keuskupan.png');
    }
    if (props.moduleKey === 'paroki') {
        return item.logo ? getImageUrl(item.logo) : (item.logo_url || '/images/logo-paroki.png');
    }
    if (colKey === 'logo' && item.logo_url) {
        return item.logo_url;
    }

    // 2. Pastor modules
    if (['riwayat-pastor', 'riwayat_pastor_paroki', 'master-pastor', 'pastor'].includes(props.moduleKey)) {
        const val = item[colKey] || item.foto;
        return val ? getImageUrl(val) : '/assets/frontend/siparoki/images/default-pastor.jpg';
    }

    // 3. Umat, Jiwa, KK Katolik or gender-based records
    const isUmatOrKkModule = ['umat', 'data-umat', 'jiwa', 'kk-katolik', 'kk', 'keluarga'].includes(props.moduleKey);
    const photoVal = (colKey && item[colKey] !== undefined) ? item[colKey] : (item.foto || item.gambar);

    if (photoVal && typeof photoVal === 'string' && photoVal.trim() !== '' && photoVal !== 'null' && photoVal !== 'undefined' && photoVal !== '—') {
        return getImageUrl(photoVal);
    }

    if (isUmatOrKkModule || item.jenis_kelamin || colKey === 'foto') {
        const ageOrBirthDate = item?.usia ?? item?.umur ?? item?.tanggal_lahir ?? item?.tgl_lahir;
        return getDefaultAvatar(item?.jenis_kelamin, ageOrBirthDate);
    }

    const fieldVal = getFieldValue(item, { key: colKey });
    return fieldVal && fieldVal !== '—' ? getImageUrl(fieldVal) : '';
};

const handleImageError = (e, item, colKey = 'foto') => {
    if (!e || !e.target) return;
    if (props.moduleKey === 'keuskupan') {
        e.target.src = '/images/logo-keuskupan.png';
    } else if (props.moduleKey === 'paroki' || colKey === 'logo') {
        e.target.src = '/images/logo-paroki.png';
    } else if (['riwayat-pastor', 'riwayat_pastor_paroki', 'master-pastor', 'pastor'].includes(props.moduleKey)) {
        e.target.src = '/assets/frontend/siparoki/images/default-pastor.jpg';
    } else if (['umat', 'data-umat', 'jiwa', 'kk-katolik', 'kk', 'keluarga'].includes(props.moduleKey) || item?.jenis_kelamin || colKey === 'foto') {
        const ageOrBirthDate = item?.usia ?? item?.umur ?? item?.tanggal_lahir ?? item?.tgl_lahir;
        const fallback = getDefaultAvatar(item?.jenis_kelamin, ageOrBirthDate);
        if (!e.target.dataset.fallbackApplied) {
            e.target.dataset.fallbackApplied = 'true';
            e.target.src = fallback;
        }
    } else {
        e.target.style.display = 'none';
        if (e.target.nextElementSibling) {
            e.target.nextElementSibling.style.display = 'flex';
        }
    }
};

const isImageField = (col, val) => {
    if (col.isImage || col.key === 'logo' || col.key === 'foto' || col.key === 'gambar' || col.key === 'banner' || col.key === 'poster') return true;
    if (typeof val === 'string' && (val.endsWith('.png') || val.endsWith('.jpg') || val.endsWith('.jpeg') || val.endsWith('.svg') || val.endsWith('.webp') || val.includes('uploads/'))) {
        return true;
    }
    return false;
};

const formatIndonesianDate = (val) => {
    if (!val || typeof val !== 'string') return val;
    try {
        const clean = val.includes('T') ? val.split('T')[0] : val;
        if (/^\d{4}-\d{2}-\d{2}/.test(clean)) {
            const [y, m, d] = clean.split('-');
            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
            const monthName = months[parseInt(m, 10) - 1] || m;
            return `${parseInt(d, 10)} ${monthName} ${y}`;
        }
    } catch {
        return val;
    }
    return val;
};

const getFieldValue = (item, col) => {
    if (!item) return '—';
    let val = '—';

    // 1. Resolve relation (supports camelCase e.g. kubAsal or snake_case e.g. kub_asal)
    const relKey = col.relation;
    const relSnakeKey = relKey ? relKey.replace(/([A-Z])/g, '_$1').toLowerCase() : null;
    const relObj = (relKey && item[relKey]) || (relSnakeKey && item[relSnakeKey]) || (typeof item[col.key] === 'object' && item[col.key] !== null ? item[col.key] : null);

    if (relObj && typeof relObj === 'object') {
        val = (col.relationKey && relObj[col.relationKey]) 
            || (col.altRelationKey && relObj[col.altRelationKey]) 
            || relObj.nama_lengkap 
            || relObj.nama_kub 
            || relObj.nama_wilayah 
            || relObj.nama_kapela 
            || relObj.nama_stasi 
            || relObj.nama_paroki 
            || relObj.nama 
            || relObj.label 
            || '—';
    } else if (item[col.key] !== null && item[col.key] !== undefined && item[col.key] !== '') {
        val = item[col.key];
    } else if (col.altKey && item[col.altKey] !== null && item[col.altKey] !== undefined && item[col.altKey] !== '') {
        val = item[col.altKey];
    }

    // Safety: if val is still an object (e.g. raw Eloquent model attribute), extract readable property
    if (typeof val === 'object' && val !== null) {
        val = val.nama_lengkap || val.nama_kub || val.nama_wilayah || val.nama_kapela || val.nama_stasi || val.nama_paroki || val.nama || val.label || '—';
    }

    if (val !== '—' && (col.isDate || col.key.includes('tanggal') || col.key.includes('tgl'))) {
        return formatIndonesianDate(String(val));
    }
    if (val !== '—' && (col.isCurrency || col.key.includes('nominal') || col.key.includes('jumlah') || col.key.includes('nilai_perolehan') || col.key.includes('total_jumlah') || col.key.includes('harga'))) {
        if (!isNaN(val) && val !== '' && val !== null) {
            return 'Rp ' + Number(val).toLocaleString('id-ID');
        }
    }
    if (val !== '—' && col.key === 'wajib') {
        return (val == 1 || val === '1' || val === true || val === 'Ya') ? 'Wajib' : 'Sukarela';
    }
    if (val !== '—' && col.key === 'status') {
        return (val == 1 || val === '1' || val === true || val === 'Aktif') ? 'Aktif' : 'Non-Aktif';
    }
    return val;
};
const isPromotedKuasi = (item) => {
    return props.moduleKey === 'kuasi-paroki' && String(item?.status || '').toLowerCase().includes('paroki');
};

const isInactiveStatus = (item) => {
    return item?.status === 'Nonaktif' || item?.status === 'Tidak Aktif' || item?.status === 0 || item?.status === '0' || item?.status === false;
};

const statusBadgeClass = (item) => {
    if (isPromotedKuasi(item)) return 'bg-blue-50 text-blue-700 border-blue-200';
    return isInactiveStatus(item)
        ? 'bg-rose-50 text-rose-700 border-rose-200'
        : 'bg-emerald-50 text-emerald-700 border-emerald-200';
};

const statusDotClass = (item) => {
    if (isPromotedKuasi(item)) return 'bg-blue-500';
    return isInactiveStatus(item) ? 'bg-rose-500' : 'bg-emerald-500';
};
const statusLabel = (item) => {
    if (isPromotedKuasi(item)) return 'Menjadi Paroki';
    return isInactiveStatus(item) ? 'Nonaktif' : 'Aktif';
};

const showRoleFilter = computed(() => {
    return ['user', 'users'].includes(props.moduleKey);
});

const sanitizedRoleList = computed(() => {
    return (props.roleList || []).filter(r => {
        const slug = String(r.slug || '').toLowerCase();
        const name = String(r.nama_role || r.name || '').toLowerCase();
        return !slug.includes('umat') && !name.includes('umat');
    });
});

const showWilayahFilter = computed(() => {
    return ['wilayah', 'kapela', 'stasi', 'kub', 'umat', 'data-umat', 'kk-katolik', 'kk', 'keluarga', 'user', 'users', 'iuran', 'sakramen', 'buku-sakramen'].includes(props.moduleKey);
});

const showKapelaFilter = computed(() => {
    return ['kapela', 'stasi', 'kub', 'umat', 'data-umat', 'kk-katolik', 'kk', 'keluarga', 'user', 'users', 'iuran', 'sakramen', 'buku-sakramen'].includes(props.moduleKey);
});

const showKubFilter = computed(() => {
    return ['kub', 'umat', 'data-umat', 'kk-katolik', 'kk', 'keluarga', 'user', 'users', 'iuran'].includes(props.moduleKey);
});

    const showStatusFilter = computed(() => {
        return ['user', 'users', 'kategori-konten', 'kategori_konten', 'konten', 'agenda', 'pengumuman', 'renungan'].includes(props.moduleKey) || props.columns?.some(c => c.key === 'status');
    });

    const showKeuskupanFilter = computed(() => {
        return ['dekenat', 'kevikepan', 'paroki', 'kuasi-paroki'].includes(props.moduleKey);
    });

    // Daftar pesan error validasi dari server (Inertia error bag)
    const validationErrors = computed(() => {
        const errs = page.props.errors || {};
        const list = [];
        for (const key in errs) {
            if (Array.isArray(errs[key])) {
                list.push(...errs[key]);
            } else if (errs[key]) {
                list.push(errs[key]);
            }
        }
        return { map: errs, list };
    });

    const fieldError = (name) => {
        const v = validationErrors.value.map[name];
        return Array.isArray(v) ? v[0] : (v || '');
    };

    const isSuperOrParokiAdmin = computed(() => {
        const p = basePrefix.value.toLowerCase();
        const r = String(props.role || page.props.role || '').toLowerCase();
        return p === '/superadmin' || p === '/paroki' || p === '/admin' || p === '/v2' ||
               r.includes('super') || r.includes('admin paroki') || r.includes('sekretariat') || r.includes('pastor');
    });

    const canDeleteCurrentModule = computed(() => {
        if (['umat', 'data-umat', 'kk-katolik', 'kk', 'keluarga'].includes(props.moduleKey)) {
            return isSuperOrParokiAdmin.value;
        }
        return true;
    });

    const isKubLevel = computed(() => {
        const p = basePrefix.value.toLowerCase();
        const r = String(props.role || page.props.role || '').toLowerCase();
        return p === '/kub' || r.includes('kub') || r.includes('ketua kub');
    });

    const mutasiTujuanType = ref('wilayah'); // 'wilayah' | 'kapela'

    const filteredKubTujuanList = computed(() => {
        const list = props.kubList || [];
        if (formData.value.wilayah_tujuan_id) {
            return list.filter((k) => String(k.wilayah_id) === String(formData.value.wilayah_tujuan_id));
        }
        if (formData.value.kapela_tujuan_id) {
            return list.filter((k) => String(k.kapela_id) === String(formData.value.kapela_tujuan_id));
        }
        return list;
    });

    const onWilayahTujuanChanged = (val) => {
        formData.value.wilayah_tujuan_id = val;
        formData.value.kapela_tujuan_id = '';
        if (formData.value.kub_tujuan_id) {
            const kub = (props.kubList || []).find((k) => String(k.id) === String(formData.value.kub_tujuan_id));
            if (kub && String(kub.wilayah_id) !== String(val)) {
                formData.value.kub_tujuan_id = '';
            }
        }
    };

    const onKapelaTujuanChanged = (val) => {
        formData.value.kapela_tujuan_id = val;
        formData.value.wilayah_tujuan_id = '';
        if (formData.value.kub_tujuan_id) {
            const kub = (props.kubList || []).find((k) => String(k.id) === String(formData.value.kub_tujuan_id));
            if (kub && String(kub.kapela_id) !== String(val)) {
                formData.value.kub_tujuan_id = '';
            }
        }
    };

    const onKubTujuanChanged = (val) => {
        formData.value.kub_tujuan_id = val;
        if (val) {
            const kub = (props.kubList || []).find((k) => String(k.id) === String(val));
            if (kub) {
                if (kub.wilayah_id) {
                    formData.value.wilayah_tujuan_id = kub.wilayah_id;
                    formData.value.kapela_tujuan_id = '';
                    mutasiTujuanType.value = 'wilayah';
                } else if (kub.kapela_id) {
                    formData.value.kapela_tujuan_id = kub.kapela_id;
                    formData.value.wilayah_tujuan_id = '';
                    mutasiTujuanType.value = 'kapela';
                }
            }
        }
    };

    const setQuickAlasan = (text) => {
        formData.value.alasan = text;
    };

</script>

<template>
    <AppLayout :title="title" :role="role" :fullWidth="true">
        <Head :title="`${title} - SIPAROKI`" />

        <!-- Responsive layout: natural flow on mobile/tablet, full-height pinned on desktop -->
        <div class="flex flex-col h-auto min-h-0 lg:h-full gap-3 pb-8 lg:pb-4">

        <!-- Header Card with Actions -->
        <div class="rounded-xl bg-white border border-slate-200/80 p-3 sm:p-4 shadow-2xs shrink-0 space-y-3.5">
            <!-- Top Row: Title + Action Buttons -->
            <div class="flex flex-col xl:flex-row xl:items-start xl:justify-between gap-3 pb-3 border-b border-slate-100">
                <div class="flex items-start gap-2.5 min-w-0">
                    <div class="w-9 h-9 rounded-lg bg-blue-50 text-blue-600 border border-blue-200 flex items-center justify-center font-bold text-sm shrink-0">
                        <i v-if="moduleKey === 'kk-katolik' || moduleKey === 'kk' || moduleKey === 'keluarga'" class="fa-regular fa-folder-open"></i>
                        <i v-else-if="moduleKey === 'umat' || moduleKey === 'data-umat'" class="fa-solid fa-users"></i>
                        <i v-else class="fa-solid fa-layer-group"></i>
                    </div>
                    <div class="min-w-0">
                        <div class="flex flex-col sm:flex-row sm:items-center gap-1.5 sm:gap-2">
                            <h2 class="text-base sm:text-lg font-black text-slate-900 leading-tight">{{ title }}</h2>
                            <span class="w-fit px-2 py-0.5 rounded-md bg-blue-50 text-blue-700 text-[11px] font-bold border border-blue-200">
                                {{ items.total || 0 }} Data
                            </span>
                        </div>
                        <p v-if="moduleKey !== 'direktori-dpp'" class="text-[11px] text-slate-500 leading-relaxed max-w-xl">
                            Kelola dan pantau data master {{ title.toLowerCase() }} paroki secara terintegrasi.
                        </p>
                    </div>
                </div>

                <!-- Right: Action Buttons Group -->
                <div class="grid grid-cols-2 sm:flex sm:flex-wrap xl:justify-end gap-2 w-full xl:w-auto">
                    <!-- 0. Read-Only Indicator for Wilayah / Kapela on KK & Umat Data -->
                    <div
                        v-if="isKkReadOnlyForRole || isUmatReadOnlyRole"
                        class="col-span-2 px-3 py-2 rounded-lg bg-slate-100 border border-slate-200 text-slate-700 text-xs font-semibold flex items-center gap-1.5 shrink-0"
                    >
                        <i class="fa-solid fa-eye text-blue-600 text-[11px]"></i>
                        <span>{{ isKkReadOnlyForRole ? 'Mode Lihat Saja (Read-Only) - Tidak Dapat Edit & Cetak' : (['wilayah', 'kub', 'sakramen', 'buku-sakramen'].includes(moduleKey) ? 'Mode Lihat Saja (Kelola di Paroki)' : 'Mode Lihat Saja (CRUD di KUB)') }}</span>
                    </div>

                    <!-- 0.1 View & Edit Only Indicator for Wilayah / Kapela on Sakramen Data -->
                    <div
                        v-else-if="isViewAndEditOnlyRole"
                        class="col-span-2 px-3 py-2 rounded-lg bg-amber-50 border border-amber-200 text-amber-800 text-xs font-semibold flex items-center gap-1.5 shrink-0"
                    >
                        <i class="fa-solid fa-pen-to-square text-amber-600 text-[11px]"></i>
                        <span>Mode Lihat & Ubah (Tambah/Hapus di Paroki)</span>
                    </div>

                    <!-- 1. Tambah Button (Conditional based on hasCreate & Role) -->
                    <template v-if="hasCreate && !isKkReadOnlyForRole && !isUmatReadOnlyRole && !isViewAndEditOnlyRole">
                        <Link
                            v-if="['role', 'roles', 'konten', 'kk-katolik', 'kk', 'keluarga', 'galeri', 'umat', 'data-umat'].includes(moduleKey)"
                            :href="moduleKey === 'konten' ? `${basePrefix}/konten/create` : (['kk-katolik', 'kk', 'keluarga'].includes(moduleKey) ? `${basePrefix}/kk-katolik/create` : (['umat', 'data-umat'].includes(moduleKey) ? `${basePrefix}/umat/create` : (moduleKey === 'galeri' ? `${basePrefix}/galeri/create` : `${basePrefix}/role/create`)))"
                            class="px-3.5 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-sm shadow-blue-500/25 transition-all flex items-center justify-center gap-1.5 cursor-pointer shrink-0 whitespace-nowrap"
                        >
                            <i class="fa-solid fa-plus text-[11px]"></i>
                            <span>Tambah {{ (moduleKey === 'kk-katolik' || moduleKey === 'kk' || moduleKey === 'keluarga') ? 'KK' : (['umat', 'data-umat'].includes(moduleKey) ? 'Umat' : (moduleKey === 'galeri' ? 'Album Galeri' : title)) }}</span>
                        </Link>
                        <button
                            v-else
                            type="button"
                            @click="openCreateModal"
                            class="px-3.5 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-sm shadow-blue-500/25 transition-all flex items-center justify-center gap-1.5 cursor-pointer shrink-0 whitespace-nowrap"
                        >
                            <i class="fa-solid fa-plus text-[11px]"></i>
                            <span>Tambah {{ (moduleKey === 'kk-katolik' || moduleKey === 'kk' || moduleKey === 'keluarga') ? 'KK' : (['riwayat-mutasi-umat', 'riwayat-mutasi', 'mutasi-umat', 'mutasi_umat'].includes(moduleKey) ? 'Mutasi Umat' : title) }}</span>
                        </button>
                    </template>

                    <!-- 2. Import Excel & Template Download Buttons (Only for whitelisted data-master modules) -->
                    <template v-if="hasImportExportActions && hasImport && !isKkReadOnlyForRole && !isUmatReadOnlyRole && !isViewAndEditOnlyRole">
                        <input
                            ref="importFileInput"
                            type="file"
                            accept=".xlsx,.xls,.csv,.txt"
                            class="hidden"
                            @change="handleImportFile"
                        />
                        <button
                            type="button"
                            :disabled="isImporting"
                            @click="triggerImportFile"
                            class="px-3.5 py-2 rounded-lg bg-emerald-50 hover:bg-emerald-100 border border-emerald-300 text-emerald-700 text-xs font-bold transition-all flex items-center justify-center gap-1.5 cursor-pointer shrink-0 whitespace-nowrap disabled:opacity-60"
                        >
                            <i v-if="isImporting" class="fa-solid fa-circle-notch fa-spin text-[11px]"></i>
                            <i v-else class="fa-solid fa-arrow-up-from-bracket text-[11px]"></i>
                            <span>{{ isImporting ? 'Mengimpor...' : 'Impor Excel' }}</span>
                        </button>

                        <a
                            :href="exportModuleUrl('template')"
                            class="px-3.5 py-2 rounded-lg bg-cyan-50 hover:bg-cyan-100 border border-cyan-300 text-cyan-700 text-xs font-bold transition-all flex items-center justify-center gap-1.5 shrink-0 whitespace-nowrap"
                        >
                            <i class="fa-solid fa-file-excel text-[11px]"></i>
                            <span>Template</span>
                        </a>
                    </template>

                    <!-- 3. Export Excel Button (Only for whitelisted data-master modules) -->
                    <a
                        v-if="hasImportExportActions && hasExport && !isKkReadOnlyForRole"
                        :href="exportModuleUrl('excel')"
                        class="px-3.5 py-2 rounded-lg bg-teal-50 hover:bg-teal-100 border border-teal-300 text-teal-700 text-xs font-bold transition-all flex items-center justify-center gap-1.5 shrink-0 whitespace-nowrap"
                    >
                        <i class="fa-solid fa-arrow-right-from-bracket text-[11px]"></i>
                        <span>Ekspor Excel</span>
                    </a>

                    <!-- 4. Print / PDF Button (Only active for modules with hasPdf = true) -->
                    <a
                        v-if="hasPdf && !isKkReadOnlyForRole"
                        :href="exportModuleUrl('print')"
                        target="_blank"
                        class="px-3.5 py-2 rounded-lg bg-purple-50 hover:bg-purple-100 border border-purple-300 text-purple-700 text-xs font-bold transition-all flex items-center justify-center gap-1.5 shrink-0 whitespace-nowrap"
                    >
                        <i class="fa-solid fa-print text-[11px]"></i>
                        <span>Cetak / PDF</span>
                    </a>

                    <!-- 5. Reload Data dari Database -->
                    <button
                        @click="refreshData"
                        :disabled="isReloadingData"
                        title="Reload data tabel dari database (tanpa reload browser)"
                        class="px-3.5 py-2 rounded-lg bg-slate-50 hover:bg-slate-100 hover:text-slate-900 border border-slate-300 text-slate-700 text-xs font-bold transition-all flex items-center justify-center gap-1.5 cursor-pointer shrink-0 whitespace-nowrap disabled:opacity-60 shadow-2xs"
                    >
                        <i :class="['fa-solid fa-arrows-rotate text-[11px]', isReloadingData ? 'fa-spin text-blue-600' : 'text-slate-600']"></i>
                        <span>{{ isReloadingData ? 'Memuat...' : 'Reload' }}</span>
                    </button>
                </div>
            </div>

            <!-- Bottom Row: Filter Bar (Role, Wilayah, Kapela, KUB, Status, Search) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-5 gap-3 text-xs">
                <!-- 0. Filter Level / Role -->
                <div v-if="showRoleFilter" class="w-full">
                    <label class="block text-[11px] sm:text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Filter Level</label>
                    <SearchableSelect
                        v-model="roleFilter"
                        :options="sanitizedRoleList"
                        valueKey="id"
                        labelKey="nama_role"
                        placeholder="Semua Level"
                        searchPlaceholder="Cari level..."
                        icon="fa-shield-halved"
                        iconColor="text-amber-600"
                    />
                </div>

                <!-- 1. Filter Wilayah -->
                <div v-if="showWilayahFilter" class="w-full">
                    <label class="block text-[11px] sm:text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5 flex items-center justify-between">
                        <span>Filter Wilayah</span>
                        <span v-if="isKubReadOnlyScope" class="text-amber-600 font-semibold lowercase text-[10px] flex items-center gap-1">
                            <i class="fa-solid fa-lock text-[9px]"></i> Wilayah KUB
                        </span>
                        <span v-else-if="kapelaFilter" class="text-rose-500 text-[10px] font-normal lowercase">(stasi aktif)</span>
                    </label>
                    <div v-if="isKubReadOnlyScope" class="w-full px-3 py-2 rounded-lg bg-slate-100 border border-slate-200 text-slate-700 text-xs font-bold flex items-center gap-2 min-h-[40px] cursor-not-allowed select-none shadow-2xs">
                        <i class="fa-solid fa-church text-blue-600 text-xs"></i>
                        <span class="truncate">{{ resolvedWilayahName }}</span>
                    </div>
                    <SearchableSelect
                        v-else
                        v-model="wilayahFilter"
                        :options="filteredWilayahsForFilter"
                        :disabled="Boolean(kapelaFilter)"
                        valueKey="id"
                        labelKey="nama_wilayah"
                        :placeholder="kapelaFilter ? '— Nonaktif —' : 'Semua Wilayah'"
                        searchPlaceholder="Cari wilayah..."
                        icon="fa-church"
                        iconColor="text-blue-600"
                    />
                </div>

                <!-- 2. Filter Kapela / Stasi -->
                <div v-if="showKapelaFilter" class="w-full">
                    <label class="block text-[11px] sm:text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5 flex items-center justify-between">
                        <span>Filter Kapela / Stasi</span>
                        <span v-if="isKubReadOnlyScope" class="text-amber-600 font-semibold lowercase text-[10px] flex items-center gap-1">
                            <i class="fa-solid fa-lock text-[9px]"></i> Stasi KUB
                        </span>
                        <span v-else-if="wilayahFilter" class="text-rose-500 text-[10px] font-normal lowercase">(wilayah aktif)</span>
                    </label>
                    <div v-if="isKubReadOnlyScope" class="w-full px-3 py-2 rounded-lg bg-slate-100 border border-slate-200 text-slate-700 text-xs font-bold flex items-center gap-2 min-h-[40px] cursor-not-allowed select-none shadow-2xs">
                        <i class="fa-solid fa-place-of-worship text-indigo-600 text-xs"></i>
                        <span class="truncate">{{ resolvedKapelaName }}</span>
                    </div>
                    <SearchableSelect
                        v-else
                        v-model="kapelaFilter"
                        :options="filteredKapelasForFilter"
                        :disabled="Boolean(wilayahFilter)"
                        valueKey="id"
                        labelKey="nama_kapela"
                        :placeholder="wilayahFilter ? '— Nonaktif —' : 'Semua Kapela / Stasi'"
                        searchPlaceholder="Cari stasi/kapela..."
                        icon="fa-place-of-worship"
                        iconColor="text-indigo-600"
                    />
                </div>

                <!-- 3. Filter KUB -->
                <div v-if="showKubFilter" class="w-full">
                    <label class="block text-[11px] sm:text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5 flex items-center justify-between">
                        <span>Filter KUB</span>
                        <span v-if="isKubReadOnlyScope" class="text-amber-600 font-semibold lowercase text-[10px] flex items-center gap-1">
                            <i class="fa-solid fa-lock text-[9px]"></i> Terkunci
                        </span>
                    </label>
                    <div v-if="isKubReadOnlyScope" class="w-full px-3 py-2 rounded-lg bg-slate-100 border border-slate-200 text-slate-700 text-xs font-bold flex items-center gap-2 min-h-[40px] cursor-not-allowed select-none shadow-2xs">
                        <i class="fa-solid fa-users text-teal-600 text-xs"></i>
                        <span class="truncate">{{ resolvedKubName }}</span>
                    </div>
                    <SearchableSelect
                        v-else
                        v-model="kubFilter"
                        :options="filteredKubsForFilter"
                        valueKey="id"
                        labelKey="nama_kub"
                        placeholder="Semua KUB"
                        searchPlaceholder="Cari KUB..."
                        icon="fa-users"
                        iconColor="text-teal-600"
                    />
                </div>

                <!-- Status Verifikasi / Status Data -->
                <div v-if="showStatusFilter" class="w-full">
                    <label class="block text-[11px] sm:text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Status</label>
                    <SearchableSelect
                        v-model="statusFilter"
                        :options="statusOptions"
                        valueKey="value"
                        labelKey="label"
                        placeholder="Semua Status"
                        searchPlaceholder="Cari status..."
                        icon="fa-circle-check"
                        iconColor="text-emerald-600"
                    />
                </div>

                <!-- Filter Keuskupan (untuk Dekenat/Kevikepan/Paroki) -->
                <div v-if="showKeuskupanFilter" class="w-full">
                    <label class="block text-[11px] sm:text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Filter Keuskupan</label>
                    <SearchableSelect
                        v-model="keuskupanFilter"
                        :options="keuskupanList"
                        valueKey="id_keuskupan"
                        labelKey="nama_keuskupan"
                        placeholder="Semua Keuskupan"
                        searchPlaceholder="Cari keuskupan..."
                        icon="fa-church"
                        iconColor="text-amber-600"
                    />
                </div>

                <!-- Cari Data -->
                <div class="w-full sm:col-span-2 lg:col-span-2 xl:col-span-1">
                    <label class="block text-[11px] sm:text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Cari Data</label>
                    <div class="relative">
                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Kata kunci..."
                            class="w-full pl-8 pr-3 py-2 rounded-lg bg-slate-50 border border-slate-200 text-xs sm:text-[12.5px] text-slate-900 placeholder-slate-400 focus:outline-none focus:border-blue-500 min-h-[40px]"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- Floating Bulk Action Toolbar -->
        <transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="transform opacity-0 -translate-y-2"
            enter-to-class="transform opacity-100 translate-y-0"
            leave-active-class="transition ease-in duration-150"
            leave-from-class="transform opacity-100 translate-y-0"
            leave-to-class="transform opacity-0 -translate-y-2"
        >
            <div
                v-if="canDeleteCurrentModule && selectedIds.length > 0"
                class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 bg-slate-900 text-white px-4 py-2.5 rounded-xl shadow-xl border border-slate-800 shrink-0"
            >
                <div class="flex items-center gap-2.5">
                    <span class="w-6 h-6 rounded-full bg-rose-500/20 text-rose-400 font-black text-xs flex items-center justify-center border border-rose-500/40">
                        {{ selectedIds.length }}
                    </span>
                    <span class="text-xs font-bold text-slate-200">
                        {{ selectedIds.length }} data {{ title.toLowerCase() }} dipilih
                    </span>
                </div>
                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        @click="selectedIds = []"
                        class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white text-xs font-semibold transition cursor-pointer"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        @click="showBulkDeleteModal = true"
                        class="px-3.5 py-1.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold shadow-sm shadow-rose-600/30 flex items-center gap-1.5 transition cursor-pointer"
                    >
                        <i class="fa-solid fa-trash-can"></i>
                        <span>Hapus Terpilih (Bulk Delete)</span>
                    </button>
                </div>
            </div>
        </transition>

        <!-- Table Container: flex-1 so it fills remaining height, with internal scroll on desktop, natural scroll on mobile -->
        <div class="flex-1 min-h-[420px] lg:min-h-0 rounded-xl bg-white border border-slate-200/80 overflow-hidden shadow-2xs flex flex-col">
            <div class="flex-1 overflow-auto custom-scrollbar">
                <table class="w-full min-w-[980px] text-left text-xs sm:text-[13px] whitespace-nowrap">
                    <thead class="bg-slate-100/90 text-slate-700 uppercase tracking-wider text-xs sm:text-[12px] font-bold border-b border-slate-200/90 sticky top-0 z-10">
                        <tr>
                            <th v-if="canDeleteCurrentModule" class="px-3 py-3 w-10 text-center whitespace-nowrap">
                                <input
                                    type="checkbox"
                                    :checked="isAllSelected"
                                    :indeterminate="isPartiallySelected"
                                    @change="toggleSelectAll"
                                    class="w-4 h-4 rounded border-slate-300 text-rose-600 focus:ring-rose-500 cursor-pointer"
                                    title="Pilih Semua di Halaman Ini"
                                />
                            </th>
                            <th class="px-3 py-3 w-10 text-center whitespace-nowrap">#</th>
                            <th v-for="col in columns" :key="col.key" class="px-4 py-3 whitespace-nowrap">
                                {{ col.label }}
                            </th>
                            <th class="px-3.5 py-3 text-center whitespace-nowrap">Status</th>
                            <th class="px-4 py-3 text-right w-28 whitespace-nowrap">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-800 text-xs sm:text-[13px]">
                        <tr
                            v-for="(item, idx) in items.data"
                            :key="item.id || item.id_keuskupan || item.id_dekenat || item.id_paroki || idx"
                            :class="['hover:bg-slate-50/80 transition-colors group', selectedIds.includes(resolveEntityId(item)) ? 'bg-rose-50/30' : '']"
                        >
                            <!-- Checkbox Column -->
                            <td v-if="canDeleteCurrentModule" class="px-3 py-3 text-center whitespace-nowrap">
                                <input
                                    type="checkbox"
                                    :value="resolveEntityId(item)"
                                    :checked="selectedIds.includes(resolveEntityId(item))"
                                    :disabled="isSelfUser(item)"
                                    @change="toggleSelectItem(resolveEntityId(item))"
                                    class="w-4 h-4 rounded border-slate-300 text-rose-600 focus:ring-rose-500 cursor-pointer"
                                />
                            </td>

                            <!-- Row Number -->
                            <td class="px-3 py-3 text-center font-bold text-slate-400 text-xs whitespace-nowrap">
                                {{ (items.from || 1) + idx }}
                            </td>

                            <!-- Columns Data -->
                            <td
                                v-for="col in columns"
                                :key="col.key"
                                class="px-4 py-3 whitespace-nowrap"
                            >
                                <!-- Image / Logo Column -->
                                <div v-if="col.isImage || col.key === 'logo' || col.key === 'foto' || isImageField(col, getFieldValue(item, col))" class="w-8.5 h-8.5 rounded-lg overflow-hidden bg-white border border-slate-200/90 shadow-2xs flex items-center justify-center p-0.5 whitespace-nowrap">
                                    <img
                                        :src="getAvatarUrl(item, col.key)"
                                        :alt="item.nama_pastor || item.nama_lengkap || item.nama_paroki || item.nama_keuskupan || 'Foto'"
                                        :class="['w-full h-full rounded-md', col.key === 'logo' || moduleKey === 'keuskupan' || moduleKey === 'paroki' ? 'object-contain' : 'object-cover']"
                                        @error="(e) => handleImageError(e, item, col.key)"
                                    />
                                </div>

                                <!-- Dedicated Icon Column (isIcon) -->
                                <div v-else-if="col.isIcon" :class="[
                                    'w-8.5 h-8.5 rounded-lg border flex items-center justify-center text-sm shadow-2xs',
                                    col.iconColor || (
                                        (moduleKey === 'provinsi') ? 'bg-indigo-50 text-indigo-700 border-indigo-200' :
                                        (moduleKey === 'kabupaten') ? 'bg-sky-50 text-sky-700 border-sky-200' :
                                        (moduleKey === 'kecamatan') ? 'bg-teal-50 text-teal-700 border-teal-200' :
                                        (moduleKey === 'desa-kelurahan') ? 'bg-emerald-50 text-emerald-700 border-emerald-200' :
                                        (moduleKey === 'dekenat' || moduleKey === 'kevikepan') ? 'bg-blue-50 text-blue-600 border-blue-200' :
                                        (moduleKey === 'paroki') ? 'bg-emerald-50 text-emerald-700 border-emerald-200' :
                                        (moduleKey === 'kuasi-paroki') ? 'bg-purple-50 text-purple-700 border-purple-200' :
                                        (moduleKey === 'kapela' || moduleKey === 'stasi') ? 'bg-rose-50 text-rose-600 border-rose-200' :
                                        (moduleKey === 'wilayah') ? 'bg-sky-50 text-sky-700 border-sky-200' :
                                        (moduleKey === 'kub') ? 'bg-teal-50 text-teal-700 border-teal-200' :
                                        'bg-amber-50 text-amber-600 border-amber-200'
                                    )
                                ]">
                                    <i :class="col.iconClass || (
                                        (moduleKey === 'provinsi') ? 'fa-solid fa-map-location-dot' :
                                        (moduleKey === 'kabupaten') ? 'fa-solid fa-city' :
                                        (moduleKey === 'kecamatan') ? 'fa-solid fa-building-columns' :
                                        (moduleKey === 'desa-kelurahan') ? 'fa-solid fa-tree-city' :
                                        (moduleKey === 'keuskupan') ? 'fa-solid fa-church' :
                                        (moduleKey === 'dekenat' || moduleKey === 'kevikepan') ? 'fa-solid fa-layer-group' :
                                        (moduleKey === 'paroki') ? 'fa-solid fa-place-of-worship' :
                                        (moduleKey === 'kuasi-paroki') ? 'fa-solid fa-location-dot' :
                                        (moduleKey === 'kapela' || moduleKey === 'stasi') ? 'fa-solid fa-gopuram' :
                                        (moduleKey === 'wilayah') ? 'fa-solid fa-map-location-dot' :
                                        (moduleKey === 'kub') ? 'fa-solid fa-people-group' :
                                        'fa-solid fa-landmark'
                                    )"></i>
                                </div>

                                <!-- Relation Link Column (isRelationLink): clickable badge showing count with filter query -->
                                <div v-else-if="col.isRelationLink">
                                    <template v-if="item[col.relation] && item[col.relation].length > 0">
                                        <Link
                                            :href="getRelationHref(col, item)"
                                            :class="[
                                                'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold border transition hover:opacity-80 cursor-pointer',
                                                col.color === 'blue' ? 'bg-blue-50 text-blue-700 border-blue-200 hover:bg-blue-100' :
                                                col.color === 'emerald' ? 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100' :
                                                col.color === 'purple' ? 'bg-purple-50 text-purple-700 border-purple-200 hover:bg-purple-100' :
                                                col.color === 'rose' ? 'bg-rose-50 text-rose-700 border-rose-200 hover:bg-rose-100' :
                                                col.color === 'amber' ? 'bg-amber-50 text-amber-700 border-amber-200 hover:bg-amber-100' :
                                                'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100'
                                            ]"
                                        >
                                            <i v-if="col.icon" :class="`fa-solid ${col.icon} text-[10px]`"></i>
                                            <span>{{ item[col.relation].length }}</span>
                                            <span>{{ col.label }}</span>
                                            <i class="fa-solid fa-arrow-up-right-from-square text-[9px] opacity-60"></i>
                                        </Link>
                                    </template>
                                    <span v-else class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-medium bg-slate-50 text-slate-400 border border-slate-200">
                                        <i v-if="col.icon" :class="`fa-solid ${col.icon} text-[9px]`"></i>
                                        <span>0 {{ col.label }}</span>
                                    </span>
                                </div>

                                <div v-else-if="col.isPrimary" class="flex items-center gap-2.5">
                                    <!-- Only show inline icon for ecclesiastical hierarchy if there's no dedicated logo/image column -->
                                    <div v-if="!columns.some(c => c.key === 'logo' || c.isImage || c.isIcon) && ['keuskupan', 'dekenat', 'kevikepan', 'paroki', 'kuasi-paroki', 'kapela', 'stasi', 'wilayah', 'kub', 'lingkungan'].includes(moduleKey)" :class="[
                                        'w-7 h-7 rounded-lg border flex items-center justify-center text-xs font-bold shrink-0',
                                        (moduleKey === 'dekenat' || moduleKey === 'kevikepan') ? 'bg-blue-50 text-blue-600 border-blue-200' :
                                        (moduleKey === 'paroki') ? 'bg-emerald-50 text-emerald-700 border-emerald-200' :
                                        (moduleKey === 'kuasi-paroki') ? 'bg-purple-50 text-purple-700 border-purple-200' :
                                        (moduleKey === 'kapela' || moduleKey === 'stasi') ? 'bg-rose-50 text-rose-600 border-rose-200' :
                                        (moduleKey === 'wilayah') ? 'bg-sky-50 text-sky-700 border-sky-200' :
                                        (moduleKey === 'kub') ? 'bg-teal-50 text-teal-700 border-teal-200' :
                                        'bg-amber-50 text-amber-700 border-amber-200'
                                    ]">
                                        <i :class="
                                            (moduleKey === 'keuskupan') ? 'fa-solid fa-church text-[11px]' :
                                            (moduleKey === 'dekenat' || moduleKey === 'kevikepan') ? 'fa-solid fa-layer-group text-[11px]' :
                                            (moduleKey === 'paroki') ? 'fa-solid fa-place-of-worship text-[11px]' :
                                            (moduleKey === 'kuasi-paroki') ? 'fa-solid fa-location-dot text-[11px]' :
                                            (moduleKey === 'kapela' || moduleKey === 'stasi') ? 'fa-solid fa-gopuram text-[11px]' :
                                            (moduleKey === 'wilayah') ? 'fa-solid fa-map-location-dot text-[11px]' :
                                            (moduleKey === 'kub') ? 'fa-solid fa-people-group text-[11px]' :
                                            'fa-solid fa-landmark text-[11px]'
                                        "></i>
                                    </div>
                                    <div class="min-w-0">
                                        <span class="font-bold text-slate-900 group-hover:text-amber-600 transition block leading-snug text-xs sm:text-[13px]">
                                            {{ getFieldValue(item, col) }}
                                        </span>
                                        <!-- Sub-info line for dekenat: show keuskupan name -->
                                        <span v-if="(moduleKey === 'dekenat' || moduleKey === 'kevikepan') && item.keuskupan" class="text-[11px] text-amber-700 font-semibold flex items-center gap-1 mt-0.5">
                                            <i class="fa-solid fa-church text-[10px]"></i>
                                            {{ item.keuskupan.nama_keuskupan }}
                                        </span>
                                        <!-- Sub-info for kapela/stasi: show paroki name -->
                                        <span v-if="(moduleKey === 'kapela' || moduleKey === 'stasi') && item.paroki" class="text-[11px] text-emerald-700 font-semibold flex items-center gap-1 mt-0.5">
                                            <i class="fa-solid fa-place-of-worship text-[10px]"></i>
                                            {{ item.paroki.nama_paroki }}
                                        </span>
                                        <!-- Sub-info for wilayah: show paroki name -->
                                        <span v-if="moduleKey === 'wilayah' && item.paroki" class="text-[11px] text-sky-700 font-semibold flex items-center gap-1 mt-0.5">
                                            <i class="fa-solid fa-place-of-worship text-[10px]"></i>
                                            {{ item.paroki.nama_paroki }}
                                        </span>
                                        <!-- Sub-info for kub: show wilayah name -->
                                        <span v-if="moduleKey === 'kub' && item.wilayah" class="text-[11px] text-teal-700 font-semibold flex items-center gap-1 mt-0.5">
                                            <i class="fa-solid fa-map-location-dot text-[10px]"></i>
                                            {{ item.wilayah.nama_wilayah }}
                                        </span>
                                        <span v-if="moduleKey === 'user'" class="text-[11px] text-slate-500 font-semibold flex items-center gap-1 mt-0.5">
                                            <i class="fa-solid fa-at text-[10px]"></i>
                                            {{ item.username || 'username' }}
                                            <span v-if="isSelfUser(item)" class="ml-1 px-1.5 py-0.5 rounded-full bg-blue-50 text-blue-700 border border-blue-200 text-[10px]">Anda</span>
                                        </span>
                                    </div>
                                </div>

                                <!-- Code Badge Column -->
                                <span v-else-if="col.key === 'kode_keuskupan' || col.key === 'kode_kevikepan' || col.key === 'kode_dekenat' || col.key === 'kode_paroki' || col.key === 'kode_kapela' || col.key === 'kode_wilayah' || col.key === 'kode_kub' || col.key === 'kode'" class="inline-block px-2 py-0.5 rounded bg-slate-100 text-slate-700 font-mono text-[11px] font-semibold border border-slate-200">
                                    {{ getFieldValue(item, col) }}
                                </span>

                                <!-- Latin Name Column -->
                                <span v-else-if="col.key === 'nama_latin' || col.key === 'nama_keuskupan_latin'" class="italic text-slate-600 font-serif text-xs">
                                    {{ getFieldValue(item, col) }}
                                </span>

                                <!-- General Text Column -->
                                <span v-else-if="moduleKey === 'user' && col.relation === 'role'" :class="[
                                    'inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full border text-[11px] font-bold',
                                    roleBadgeClass(item.role)
                                ]">
                                    <i class="fa-solid fa-shield-halved text-[10px]"></i>
                                    {{ getFieldValue(item, col) }}
                                </span>

                                <!-- Kategori Konten: Jumlah Konten Badge -->
                                <template v-else-if="col.key === 'total_konten'">
                                    <Link
                                        v-if="item.total_konten > 0"
                                        :href="`${basePrefix}/konten?category=${encodeURIComponent(item.nama_kategori || item.kategori || '')}`"
                                        class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11.5px] font-bold border shadow-2xs bg-emerald-50 text-emerald-800 border-emerald-200 hover:bg-emerald-100 hover:border-emerald-300 transition cursor-pointer"
                                        :title="`Lihat ${item.total_konten} artikel dalam kategori ini`"
                                    >
                                        <i class="fa-solid fa-newspaper text-emerald-600"></i>
                                        <span>{{ item.total_konten }} Artikel</span>
                                        <i class="fa-solid fa-arrow-up-right-from-square text-[9px] text-emerald-600/70 ml-0.5"></i>
                                    </Link>
                                    <span
                                        v-else
                                        class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11.5px] font-semibold border bg-slate-50 text-slate-400 border-slate-200"
                                    >
                                        <i class="fa-solid fa-folder-open text-slate-300"></i>
                                        <span>0 Artikel</span>
                                    </span>
                                </template>

                                <!-- Dekenat / Kevikepan Column (Responsive & Clean) -->
                                <template v-else-if="col.key === 'dekenat_nama' || col.key === 'nama_dekenat' || col.key === 'nama_kevikepan'">
                                    <div v-if="getFieldValue(item, col) && getFieldValue(item, col) !== '—'" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-cyan-50/80 text-cyan-900 border border-cyan-200/80 font-semibold text-xs whitespace-normal break-words max-w-[240px] text-left leading-snug shadow-2xs">
                                        <i class="fa-solid fa-layer-group text-cyan-600 text-[11px] shrink-0"></i>
                                        <span>{{ getFieldValue(item, col) }}</span>
                                    </div>
                                    <span v-else class="text-slate-400 italic text-xs">—</span>
                                </template>

                                <!-- Paroki Induk Column (Responsive & Clean) -->
                                <template v-else-if="col.key === 'paroki_nama' || col.key === 'nama_paroki'">
                                    <div v-if="getFieldValue(item, col) && getFieldValue(item, col) !== '—'" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-amber-50/80 text-amber-900 border border-amber-200/80 font-semibold text-xs whitespace-normal break-words max-w-[260px] text-left leading-snug shadow-2xs">
                                        <i class="fa-solid fa-church text-amber-600 text-[11px] shrink-0"></i>
                                        <span>{{ getFieldValue(item, col) }}</span>
                                    </div>
                                    <span v-else class="text-slate-400 italic text-xs">—</span>
                                </template>

                                <!-- General Text Column -->
                                <span v-else class="text-slate-700 font-medium text-xs sm:text-[12.5px]">
                                    {{ getFieldValue(item, col) }}
                                </span>
                            </td>

                            <!-- Status Badge -->
                            <td class="px-3.5 py-3 text-center whitespace-nowrap">
                                <!-- Dekenat: show paroki count badge -->
                                <template v-if="moduleKey === 'dekenat' || moduleKey === 'kevikepan'">
                                    <div class="flex flex-col items-center gap-1 whitespace-nowrap">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200 whitespace-nowrap">
                                            <i class="fa-solid fa-church text-[9px]"></i>
                                            <span>{{ (item.parokis && item.parokis.length) ? item.parokis.length : 0 }} Paroki</span>
                                        </span>
                                        <span :class="[
                                            'inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold border whitespace-nowrap',
                                            statusBadgeClass(item)
                                        ]">
                                            <span :class="[
                                                'w-1.5 h-1.5 rounded-full shrink-0',
                                                statusDotClass(item)
                                            ]"></span>
                                            <span>{{ statusLabel(item) }}</span>
                                        </span>
                                    </div>
                                </template>
                                <template v-else>
                                    <span :class="[
                                        'inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold border whitespace-nowrap',
                                        statusBadgeClass(item)
                                    ]">
                                        <span :class="[
                                            'w-1.5 h-1.5 rounded-full shrink-0',
                                            statusDotClass(item)
                                        ]"></span>
                                        <span>{{ statusLabel(item) }}</span>
                                    </span>
                                </template>
                            </td>

                            <!-- Row Actions -->
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1.5 whitespace-nowrap">
                                    <!-- 1. Detail / Preview Button (Exactly ONE view icon per row) -->
                                    <Link
                                        v-if="moduleKey === 'konten'"
                                        :href="`${basePrefix}/konten/${item.uuid || item.id || item.slug}/preview`"
                                        title="Preview Konten"
                                        class="w-7.5 h-7.5 rounded-lg bg-slate-50 hover:bg-amber-50 hover:text-amber-700 text-slate-500 border border-slate-200 flex items-center justify-center transition cursor-pointer shadow-2xs"
                                    >
                                        <i class="fa-solid fa-eye text-xs"></i>
                                    </Link>
                                    <Link
                                        v-else-if="['kk-katolik', 'kk', 'keluarga'].includes(moduleKey)"
                                        :href="`${basePrefix}/kk-katolik/${item.uuid || item.id || item.no_kk_kw}/view`"
                                        title="Lihat Detail Kartu Keluarga"
                                        class="w-7.5 h-7.5 rounded-lg bg-slate-50 hover:bg-amber-50 hover:text-amber-700 text-slate-500 border border-slate-200 flex items-center justify-center transition cursor-pointer shadow-2xs"
                                    >
                                        <i class="fa-solid fa-eye text-xs"></i>
                                    </Link>
                                    <button
                                        v-else
                                        type="button"
                                        @click="openDetailModal(item)"
                                        title="Lihat Detail"
                                        class="w-7.5 h-7.5 rounded-lg bg-slate-50 hover:bg-amber-50 hover:text-amber-700 text-slate-500 border border-slate-200 flex items-center justify-center transition cursor-pointer shadow-2xs"
                                    >
                                        <i class="fa-solid fa-eye text-xs"></i>
                                    </button>

                                    <!-- 2. Cetak Button (Only for KK, and only for non-read-only roles) -->
                                    <a
                                        v-if="['kk-katolik', 'kk', 'keluarga'].includes(moduleKey) && !isKkReadOnlyForRole && !isUmatReadOnlyRole"
                                        :href="`${basePrefix}/kk-katolik/${item.uuid || item.id || item.no_kk_kw}/cetak`"
                                        target="_blank"
                                        title="Cetak Kartu Keluarga (PDF / Print)"
                                        class="w-7.5 h-7.5 rounded-lg bg-slate-50 hover:bg-emerald-50 hover:text-emerald-700 text-slate-500 border border-slate-200 flex items-center justify-center transition cursor-pointer shadow-2xs"
                                    >
                                        <i class="fa-solid fa-print text-xs"></i>
                                    </a>
                                    <!-- Mutasi KUB, Pisah KK, & Riwayat Buttons (For Umat) -->
                                    <template v-if="['umat', 'data-umat'].includes(moduleKey)">
                                        <!-- Cetak Profil Umat / Jiwa -->
                                        <a
                                            :href="`${basePrefix}/umat/${item.uuid || item.id}/cetak`"
                                            target="_blank"
                                            title="Cetak Profil / Biodata Jiwa Umat (PDF / Print)"
                                            class="w-7.5 h-7.5 rounded-lg bg-slate-50 hover:bg-emerald-50 hover:text-emerald-700 text-slate-500 border border-slate-200 flex items-center justify-center transition cursor-pointer shadow-2xs"
                                        >
                                            <i class="fa-solid fa-print text-xs"></i>
                                        </a>
                                        <Link
                                            v-if="!isUmatReadOnlyRole"
                                            :href="`${basePrefix}/umat/${item.uuid || item.id}/mutasi`"
                                            title="Mutasi / Pindah KUB"
                                            class="w-7.5 h-7.5 rounded-lg bg-slate-50 hover:bg-teal-50 hover:text-teal-700 text-slate-500 border border-slate-200 flex items-center justify-center transition cursor-pointer shadow-2xs"
                                        >
                                            <i class="fa-solid fa-arrows-split-up-and-left text-xs"></i>
                                        </Link>
                                        <Link
                                            v-if="!isUmatReadOnlyRole"
                                            :href="`${basePrefix}/umat/${item.uuid || item.id}/pisah-kk`"
                                            title="Pisah KK (Menikah / Bentuk Keluarga Baru)"
                                            class="w-7.5 h-7.5 rounded-lg bg-slate-50 hover:bg-purple-50 hover:text-purple-700 text-slate-500 border border-slate-200 flex items-center justify-center transition cursor-pointer shadow-2xs"
                                        >
                                            <i class="fa-solid fa-people-roof text-xs"></i>
                                        </Link>
                                        <Link
                                            :href="`${basePrefix}/umat/${item.uuid || item.id}/riwayat`"
                                            title="Riwayat Mutasi & Pergerakan Umat"
                                            class="w-7.5 h-7.5 rounded-lg bg-slate-50 hover:bg-amber-50 hover:text-amber-700 text-slate-500 border border-slate-200 flex items-center justify-center transition cursor-pointer shadow-2xs"
                                        >
                                            <i class="fa-solid fa-clock-rotate-left text-xs"></i>
                                        </Link>
                                    </template>

                                    <!-- Edit Button (Hidden for Read-Only KK on Wilayah/Kapela & Read-Only Umat) -->
                                    <template v-if="!isKkReadOnlyForRole && !isUmatReadOnlyRole">
                                        <Link
                                            v-if="['role', 'roles', 'konten', 'kk-katolik', 'kk', 'keluarga', 'galeri', 'umat', 'data-umat'].includes(moduleKey)"
                                            :href="moduleKey === 'konten' ? `${basePrefix}/konten/${item.uuid || item.id || item.slug}/edit` : (['kk-katolik', 'kk', 'keluarga'].includes(moduleKey) ? `${basePrefix}/${moduleKey}/${item.uuid || item.id || item.slug || item.no_kk_kw}/edit` : (['umat', 'data-umat'].includes(moduleKey) ? `${basePrefix}/umat/${item.uuid || item.id}/edit` : (moduleKey === 'galeri' ? `${basePrefix}/galeri/${item.uuid || item.id}/edit` : `${basePrefix}/role/${item.uuid || item.id || item.id_role || item.slug}/edit`)))"
                                            title="Ubah Data"
                                            class="w-7.5 h-7.5 rounded-lg bg-slate-50 hover:bg-blue-50 hover:text-blue-700 text-slate-500 border border-slate-200 flex items-center justify-center transition cursor-pointer shadow-2xs"
                                        >
                                            <i class="fa-solid fa-pen-to-square text-xs"></i>
                                        </Link>
                                        <button
                                            v-else
                                            type="button"
                                            @click="openEditModal(item)"
                                            title="Ubah Data"
                                            class="w-7.5 h-7.5 rounded-lg bg-slate-50 hover:bg-blue-50 hover:text-blue-700 text-slate-500 border border-slate-200 flex items-center justify-center transition cursor-pointer shadow-2xs"
                                        >
                                            <i class="fa-solid fa-pen-to-square text-xs"></i>
                                        </button>
                                    </template>

                                    <button
                                        v-if="moduleKey === 'user'"
                                        type="button"
                                        @click="resetUserPassword(item)"
                                        title="Reset Password"
                                        class="w-7.5 h-7.5 rounded-lg bg-slate-50 hover:bg-amber-50 hover:text-amber-700 text-slate-500 border border-slate-200 flex items-center justify-center transition cursor-pointer shadow-2xs"
                                    >
                                        <i class="fa-solid fa-key text-xs"></i>
                                    </button>
                                    <button
                                        v-if="moduleKey === 'user'"
                                        type="button"
                                        @click="toggleUserStatus(item)"
                                        :disabled="isSelfUser(item)"
                                        title="Aktifkan / Nonaktifkan Akun Pengguna"
                                        :class="[
                                            'w-7.5 h-7.5 rounded-lg bg-slate-50 text-slate-500 border border-slate-200 flex items-center justify-center transition shadow-2xs',
                                            isSelfUser(item) ? 'opacity-40 cursor-not-allowed' : 'hover:bg-cyan-50 hover:text-cyan-700 cursor-pointer'
                                        ]"
                                    >
                                        <i class="fa-solid fa-toggle-on text-xs"></i>
                                    </button>
                                    <button
                                        v-if="moduleKey === 'user' && isSuperAdmin && !isSelfUser(item)"
                                        type="button"
                                        @click="impersonateUser(item)"
                                        :title="`Login Langsung Sebagai ${item.nama_lengkap || item.name || item.username} (Simulasi Nyata Akun)`"
                                        class="w-7.5 h-7.5 rounded-lg bg-slate-50 hover:bg-emerald-50 hover:text-emerald-700 text-slate-500 border border-slate-200 flex items-center justify-center transition cursor-pointer shadow-2xs"
                                    >
                                        <i class="fa-solid fa-right-to-bracket text-xs"></i>
                                    </button>

                                    <!-- Delete Button (Only visible if authorized to delete) -->
                                    <button
                                        v-if="canDeleteCurrentModule"
                                        type="button"
                                        @click="openDeleteModal(item)"
                                        :disabled="isSelfUser(item)"
                                        title="Hapus Data"
                                        :class="[
                                            'w-7.5 h-7.5 rounded-lg bg-slate-50 text-slate-500 border border-slate-200 flex items-center justify-center transition shadow-2xs',
                                            isSelfUser(item) ? 'opacity-40 cursor-not-allowed' : 'hover:bg-rose-50 hover:text-rose-700 cursor-pointer'
                                        ]"
                                    >
                                        <i class="fa-solid fa-trash-can text-xs"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <!-- Empty State -->
                        <tr v-if="!items.data || !items.data.length">
                            <td :colspan="columns.length + (canDeleteCurrentModule ? 3 : 2)" class="px-5 py-8 text-center text-slate-400">
                                <div class="w-10 h-10 rounded-xl bg-slate-50 text-slate-300 border border-slate-200 mx-auto flex items-center justify-center text-lg mb-2">
                                    <i class="fa-solid fa-folder-open"></i>
                                </div>
                                <p class="text-xs font-bold text-slate-700">Belum Ada Data Ditemukan</p>
                                <p class="text-[11px] text-slate-400 mt-0.5">Tidak ada record pada modul {{ title.toLowerCase() }} saat ini.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination Bar: outside scroll area, always visible at bottom -->
            <div
                v-if="items.links && items.links.length > 3"
                class="shrink-0 px-4 py-2.5 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-2 bg-slate-50/50"
            >
                <span class="text-xs sm:text-[12.5px] text-slate-600 font-medium">
                    Menampilkan <b class="text-slate-900">{{ items.from || 0 }}</b> - <b class="text-slate-900">{{ items.to || 0 }}</b> dari <b class="text-slate-900">{{ items.total || 0 }}</b> total data
                </span>

                <div class="flex items-center gap-1">
                    <Link
                        v-for="(link, idx) in items.links"
                        :key="idx"
                        :href="link.url || '#'"
                        :only="['items', 'filters']"
                        preserve-state
                        preserve-scroll
                        :disabled="!link.url"
                        :class="[
                            'px-3 py-1.5 rounded-lg text-xs font-bold transition',
                            link.active
                                ? 'bg-amber-500 text-white shadow-xs shadow-amber-500/30'
                                : 'bg-white border border-slate-200 text-slate-700 hover:bg-slate-100 hover:text-slate-900',
                            !link.url ? 'opacity-40 cursor-not-allowed pointer-events-none' : ''
                        ]"
                        v-html="formatPaginationLabel(link.label)"
                    />
                </div>
            </div>
        </div>
        <!-- end Table Container -->
                <!-- DETAIL MODAL -->
        <div
            v-if="showDetailModal && selectedItem"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/40 backdrop-blur-xs overflow-y-auto"
        >
            <div :class="['bg-white rounded-3xl w-full shadow-2xl border border-slate-200 overflow-hidden animate-in fade-in zoom-in-95 my-8', (moduleKey === 'umat' || moduleKey === 'data-umat') ? 'max-w-4xl' : 'max-w-3xl']">
                <!-- Amber / Gold Header Banner -->
                <div class="bg-gradient-to-r from-amber-500 to-amber-600 px-6 py-4 flex items-center justify-between text-white shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-white/20 border border-white/30 flex items-center justify-center text-base">
                            <i :class="(moduleKey === 'umat' || moduleKey === 'data-umat') ? 'fa-solid fa-user-circle' : (moduleKey === 'keuskupan' ? 'fa-solid fa-church' : (moduleKey === 'dekenat' || moduleKey === 'kevikepan' ? 'fa-solid fa-layer-group' : (moduleKey === 'wilayah' ? 'fa-solid fa-map-location-dot' : (moduleKey === 'kub' ? 'fa-solid fa-people-roof' : (moduleKey === 'provinsi' ? 'fa-solid fa-earth-asia' : (moduleKey === 'kabupaten' ? 'fa-solid fa-city' : (moduleKey === 'kecamatan' ? 'fa-solid fa-building-columns' : (moduleKey === 'desa' || moduleKey === 'desa-kelurahan' ? 'fa-solid fa-tree-city' : 'fa-solid fa-place-of-worship'))))))))"></i>
                        </div>
                        <h3 class="text-sm font-bold tracking-tight">
                            <template v-if="moduleKey === 'umat' || moduleKey === 'data-umat'">
                                Detail Data Umat: {{ selectedItem.nama_lengkap || selectedItem.nama_baptis || selectedItem.nama_lahir || 'Umat Paroki' }}
                            </template>
                            <template v-else-if="moduleKey === 'kapela' || moduleKey === 'stasi-kapela' || moduleKey === 'stasi_kapela'">
                                Detail Data Stasi / Kapela: {{ selectedItem.nama_kapela || selectedItem.nama_stasi || selectedItem.nama || 'Stasi / Kapela' }}
                            </template>
                            <template v-else-if="moduleKey === 'wilayah'">
                                Detail Wilayah Pastoral: {{ selectedItem.nama_wilayah || selectedItem.nama || 'Wilayah' }}
                            </template>
                            <template v-else-if="moduleKey === 'kub'">
                                Detail KUB / KBG: {{ selectedItem.nama_kub || selectedItem.nama || 'KUB' }}
                            </template>
                            <template v-else-if="moduleKey === 'provinsi'">
                                Detail Provinsi: {{ selectedItem.nama_provinsi || selectedItem.nama || 'Provinsi' }}
                            </template>
                            <template v-else-if="moduleKey === 'kabupaten'">
                                Detail Kabupaten / Kota: {{ selectedItem.nama_kabupaten || selectedItem.nama || 'Kabupaten' }}
                            </template>
                            <template v-else-if="moduleKey === 'kecamatan'">
                                Detail Kecamatan: {{ selectedItem.nama_kecamatan || selectedItem.nama || 'Kecamatan' }}
                            </template>
                            <template v-else-if="moduleKey === 'desa' || moduleKey === 'desa-kelurahan'">
                                Detail Desa / Kelurahan: {{ selectedItem.nama_desa || selectedItem.nama_kelurahan || selectedItem.nama || 'Desa / Kelurahan' }}
                            </template>
                            <template v-else>
                                Detail {{ title }}: {{ selectedItem.nama_keuskupan || selectedItem.nama_kevikepan || selectedItem.nama_dekenat || selectedItem.nama_paroki || selectedItem.nama_kapela || selectedItem.nama_stasi || selectedItem.nama_wilayah || selectedItem.nama_kub || selectedItem.nama || title }}
                            </template>
                        </h3>
                    </div>
                    <button
                        @click="showDetailModal = false"
                        class="text-white/80 hover:text-white p-1 text-sm cursor-pointer transition"
                    >
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-6 space-y-6 max-h-[75vh] overflow-y-auto custom-scrollbar">
                    <!-- 0. DETAIL DATA UMAT / JIWA LENGKAP -->
                    <template v-if="moduleKey === 'umat' || moduleKey === 'data-umat' || moduleKey === 'jiwa'">
                        <!-- Profile Header Card -->
                        <div class="p-5 rounded-2xl bg-gradient-to-r from-amber-50 via-white to-amber-50/50 border border-amber-200/80 flex flex-col sm:flex-row items-center sm:items-start gap-5">
                            <div class="w-24 h-24 rounded-2xl bg-amber-500/10 text-amber-600 border border-amber-200 flex items-center justify-center text-4xl shrink-0 overflow-hidden shadow-xs">
                                <img
                                    :src="getAvatarUrl(selectedItem, 'foto')"
                                    :alt="selectedItem.nama_lengkap || selectedItem.nama_lahir || 'Foto'"
                                    class="w-full h-full object-cover"
                                    @error="(e) => handleImageError(e, selectedItem, 'foto')"
                                />
                            </div>
                            <div class="flex-1 text-center sm:text-left space-y-2">
                                <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2">
                                    <h4 class="text-base sm:text-lg font-black text-slate-900">
                                        {{ selectedItem.nama_lengkap || selectedItem.nama_lahir || '—' }}
                                    </h4>
                                    <span v-if="selectedItem.nama_baptis" class="text-xs sm:text-sm font-semibold text-amber-800 bg-amber-100/80 px-2.5 py-0.5 rounded-full border border-amber-200">
                                        ({{ selectedItem.nama_baptis }})
                                    </span>
                                </div>
                                <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2 text-xs">
                                    <span v-if="selectedItem.usia || selectedItem.tanggal_lahir" class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-blue-50 text-blue-800 font-bold border border-blue-200">
                                        <i class="fa-solid fa-cake-candles text-[10px]"></i>
                                        <span>{{ selectedItem.usia !== undefined ? `${selectedItem.usia} Tahun` : (selectedItem.tanggal_lahir ? `${new Date().getFullYear() - new Date(selectedItem.tanggal_lahir).getFullYear()} Thn` : '') }}</span>
                                    </span>
                                    <span :class="[
                                        'inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full font-bold border text-[11px]',
                                        (selectedItem.status_panggilan && selectedItem.status_panggilan !== 'Awam')
                                            ? 'bg-purple-50 text-purple-800 border-purple-200'
                                            : 'bg-slate-100 text-slate-700 border-slate-200'
                                    ]">
                                        <i class="fa-solid fa-hands-praying text-[10px]"></i>
                                        <span>{{ selectedItem.status_panggilan || 'Awam' }}</span>
                                    </span>
                                    <span :class="[
                                        'inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full font-bold border text-[11px]',
                                        (selectedItem.status_umat === 'Aktif' || selectedItem.status_aktif == 1 || !selectedItem.status_umat)
                                            ? 'bg-emerald-50 text-emerald-800 border-emerald-200'
                                            : 'bg-rose-50 text-rose-800 border-rose-200'
                                    ]">
                                        <span class="w-1.5 h-1.5 rounded-full" :class="(selectedItem.status_umat === 'Aktif' || selectedItem.status_aktif == 1 || !selectedItem.status_umat) ? 'bg-emerald-500' : 'bg-rose-500'"></span>
                                        <span>{{ selectedItem.status_umat || 'Aktif' }}</span>
                                    </span>
                                </div>
                                <p class="text-xs text-slate-500 flex flex-wrap items-center justify-center sm:justify-start gap-3 pt-1">
                                    <span v-if="selectedItem.no_kk_kw || selectedItem.kk?.no_kk_kw">
                                        <i class="fa-solid fa-folder-open text-amber-600 mr-1"></i> No KK: <b>{{ selectedItem.no_kk_kw || selectedItem.kk?.no_kk_kw }}</b>
                                    </span>
                                    <span v-if="selectedItem.nik">
                                        <i class="fa-solid fa-id-card text-blue-600 mr-1"></i> NIK: <b>{{ selectedItem.nik }}</b>
                                    </span>
                                </p>
                            </div>
                        </div>

                        <!-- 1. IDENTITAS & KEPENDUDUKAN -->
                        <div class="space-y-3">
                            <h5 class="text-xs font-black text-slate-900 uppercase tracking-wider flex items-center gap-2 pb-1.5 border-b border-slate-100">
                                <i class="fa-solid fa-id-card text-amber-600"></i>
                                <span>1. Identitas Sipil & Kependudukan</span>
                            </h5>
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 text-xs">
                                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/80">
                                    <span class="text-[10px] text-slate-400 font-bold uppercase block mb-1">Nama Lahir / Marga</span>
                                    <span class="font-bold text-slate-900">{{ selectedItem.nama_lahir || selectedItem.nama_marga || selectedItem.nama_lengkap || '—' }}</span>
                                </div>
                                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/80">
                                    <span class="text-[10px] text-slate-400 font-bold uppercase block mb-1">Jenis Kelamin</span>
                                    <span class="font-bold text-slate-900">{{ selectedItem.jenis_kelamin === 'L' || selectedItem.jenis_kelamin === 'Laki-Laki' ? 'Laki-Laki (Pria)' : (selectedItem.jenis_kelamin === 'P' || selectedItem.jenis_kelamin === 'Perempuan' ? 'Perempuan (Wanita)' : (selectedItem.jenis_kelamin || '—')) }}</span>
                                </div>
                                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/80">
                                    <span class="text-[10px] text-slate-400 font-bold uppercase block mb-1">Tempat & Tanggal Lahir</span>
                                    <span class="font-bold text-slate-900">{{ selectedItem.tempat_lahir || '—' }}, {{ formatDateId(selectedItem.tanggal_lahir) }}</span>
                                </div>
                                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/80">
                                    <span class="text-[10px] text-slate-400 font-bold uppercase block mb-1">Kedudukan dalam Keluarga</span>
                                    <span class="font-bold text-slate-900">{{ selectedItem.hubungan_keluarga || '—' }}</span>
                                </div>
                                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/80">
                                    <span class="text-[10px] text-slate-400 font-bold uppercase block mb-1">Golongan Darah</span>
                                    <span class="font-bold text-slate-900">{{ selectedItem.golongan_darah || 'Tidak Tahu' }}</span>
                                </div>
                                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/80">
                                    <span class="text-[10px] text-slate-400 font-bold uppercase block mb-1">Agama Asal</span>
                                    <span class="font-bold text-slate-900">{{ selectedItem.agama_asal || 'Katolik sejak lahir' }}</span>
                                </div>
                                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/80">
                                    <span class="text-[10px] text-slate-400 font-bold uppercase block mb-1">Status Perkawinan Sipil</span>
                                    <span class="font-bold text-slate-900">{{ selectedItem.status_perkawinan || selectedItem.status_menikah || 'Belum Menikah' }}</span>
                                </div>
                                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/80">
                                    <span class="text-[10px] text-slate-400 font-bold uppercase block mb-1">Status Perkawinan Kanonik</span>
                                    <span class="font-bold text-slate-900">{{ selectedItem.status_perkawinan_kanonik || 'Katolik Organik' }}</span>
                                </div>
                                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/80" v-if="selectedItem.nama_pasangan">
                                    <span class="text-[10px] text-slate-400 font-bold uppercase block mb-1">Nama Pasangan</span>
                                    <span class="font-bold text-slate-900">{{ selectedItem.nama_pasangan }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- 2. WILAYAH GEREJANI & DOMISILI -->
                        <div class="space-y-3">
                            <h5 class="text-xs font-black text-slate-900 uppercase tracking-wider flex items-center gap-2 pb-1.5 border-b border-slate-100">
                                <i class="fa-solid fa-church text-amber-600"></i>
                                <span>2. Wilayah Pastoral & Domisili</span>
                            </h5>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                                <div class="p-3 bg-blue-50/60 rounded-xl border border-blue-200/80">
                                    <span class="text-[10px] text-blue-700 font-bold uppercase block mb-1">Wilayah Pastoral</span>
                                    <span class="font-bold text-slate-900">{{ selectedItem.kk?.wilayah?.nama_wilayah || selectedItem.wilayah?.nama_wilayah || '—' }}</span>
                                </div>
                                <div class="p-3 bg-indigo-50/60 rounded-xl border border-indigo-200/80">
                                    <span class="text-[10px] text-indigo-700 font-bold uppercase block mb-1">Stasi / Kapela</span>
                                    <span class="font-bold text-slate-900">{{ selectedItem.kk?.kapela?.nama_kapela || selectedItem.kapela?.nama_kapela || 'Pusat Paroki' }}</span>
                                </div>
                                <div class="p-3 bg-teal-50/60 rounded-xl border border-teal-200/80">
                                    <span class="text-[10px] text-teal-700 font-bold uppercase block mb-1">KUB / KBG</span>
                                    <span class="font-bold text-slate-900">{{ selectedItem.kk?.kub?.nama_kub || selectedItem.kub?.nama_kub || '—' }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- 3. STATUS PANGGILAN & VOKASI KHUSUS -->
                        <div class="space-y-3" v-if="selectedItem.status_panggilan && selectedItem.status_panggilan !== 'Awam'">
                            <h5 class="text-xs font-black text-purple-900 uppercase tracking-wider flex items-center gap-2 pb-1.5 border-b border-purple-100">
                                <i class="fa-solid fa-hands-praying text-purple-600"></i>
                                <span>3. Status Panggilan & Tarekat Hidup Bakti</span>
                            </h5>
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 text-xs">
                                <div class="p-3 bg-purple-50/60 rounded-xl border border-purple-200/80">
                                    <span class="text-[10px] text-purple-700 font-bold uppercase block mb-1">Status Panggilan</span>
                                    <span class="font-bold text-slate-900">{{ selectedItem.status_panggilan }}</span>
                                </div>
                                <div class="p-3 bg-purple-50/60 rounded-xl border border-purple-200/80">
                                    <span class="text-[10px] text-purple-700 font-bold uppercase block mb-1">Ordo / Kongregasi</span>
                                    <span class="font-bold text-slate-900">{{ selectedItem.nama_ordo_kongregasi || '—' }}</span>
                                </div>
                                <div class="p-3 bg-purple-50/60 rounded-xl border border-purple-200/80">
                                    <span class="text-[10px] text-purple-700 font-bold uppercase block mb-1">Tahap Panggilan</span>
                                    <span class="font-bold text-slate-900">{{ selectedItem.tahap_panggilan || '—' }}</span>
                                </div>
                                <div class="sm:col-span-2 p-3 bg-purple-50/60 rounded-xl border border-purple-200/80">
                                    <span class="text-[10px] text-purple-700 font-bold uppercase block mb-1">Tempat Tugas / Komunitas Biara</span>
                                    <span class="font-bold text-slate-900">{{ selectedItem.tempat_tugas_biara || '—' }}</span>
                                </div>
                                <div class="p-3 bg-purple-50/60 rounded-xl border border-purple-200/80" v-if="selectedItem.tgl_tahbisan_kaul">
                                    <span class="text-[10px] text-purple-700 font-bold uppercase block mb-1">Tgl Tahbisan / Kaul</span>
                                    <span class="font-bold text-slate-900">{{ formatDateId(selectedItem.tgl_tahbisan_kaul) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- 4. CATATAN SAKRAMEN GEREJA -->
                        <div class="space-y-3">
                            <h5 class="text-xs font-black text-slate-900 uppercase tracking-wider flex items-center gap-2 pb-1.5 border-b border-slate-100">
                                <i class="fa-solid fa-cross text-amber-600"></i>
                                <span>4. Penerimaan Sakramen Gereja Katolik</span>
                            </h5>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                                <!-- Baptis -->
                                <div class="p-3.5 bg-amber-50/60 rounded-xl border border-amber-200/80 space-y-1.5">
                                    <div class="flex items-center gap-1.5 font-bold text-amber-950">
                                        <i class="fa-solid fa-droplet text-amber-600"></i>
                                        <span>Sakramen Baptis</span>
                                    </div>
                                    <p class="text-slate-700 text-[11px] leading-relaxed">
                                        Tgl: <b>{{ formatDateId(selectedItem.tgl_baptis) }}</b> &bull; Paroki: <b>{{ selectedItem.paroki_baptis || '—' }}</b><br />
                                        Pastor: <b>{{ selectedItem.pastor_baptis || '—' }}</b> &bull; Wali: <b>{{ selectedItem.wali_baptis || '—' }}</b><br />
                                        Buku Baptis: <b>Vol {{ selectedItem.buku_baptis_vol || '-' }} / Hal {{ selectedItem.buku_baptis_hal || '-' }} / No {{ selectedItem.buku_baptis_no || '-' }}</b>
                                    </p>
                                </div>

                                <!-- Komuni 1 & Krisma -->
                                <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200/80 space-y-2">
                                    <div>
                                        <div class="flex items-center gap-1.5 font-bold text-slate-900">
                                            <i class="fa-solid fa-bread-slice text-amber-600"></i>
                                            <span>Komuni Pertama (Ekaristi)</span>
                                        </div>
                                        <p class="text-slate-700 text-[11px]">
                                            Tgl: <b>{{ formatDateId(selectedItem.tgl_komuni_1) }}</b> &bull; Paroki: <b>{{ selectedItem.paroki_komuni_1 || '—' }}</b>
                                        </p>
                                    </div>
                                    <div class="pt-1.5 border-t border-slate-200">
                                        <div class="flex items-center gap-1.5 font-bold text-slate-900">
                                            <i class="fa-solid fa-fire-flame-curved text-amber-600"></i>
                                            <span>Sakramen Krisma (Penguatan)</span>
                                        </div>
                                        <p class="text-slate-700 text-[11px]">
                                            Tgl: <b>{{ formatDateId(selectedItem.tgl_krisma) }}</b> &bull; Paroki: <b>{{ selectedItem.paroki_krisma || '—' }}</b>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 5. SOSIAL, PROFESI -->
                        <div class="space-y-3">
                            <h5 class="text-xs font-black text-slate-900 uppercase tracking-wider flex items-center gap-2 pb-1.5 border-b border-slate-100">
                                <i class="fa-solid fa-graduation-cap text-amber-600"></i>
                                <span>5. Sosial, Profesi</span>
                            </h5>
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 text-xs">
                                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/80">
                                    <span class="text-[10px] text-slate-400 font-bold uppercase block mb-1">Pendidikan Terakhir</span>
                                    <span class="font-bold text-slate-900">{{ selectedItem.pendidikan || '—' }}</span>
                                </div>
                                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/80">
                                    <span class="text-[10px] text-slate-400 font-bold uppercase block mb-1">Profesi / Pekerjaan</span>
                                    <span class="font-bold text-slate-900">{{ selectedItem.pekerjaan || '—' }}</span>
                                </div>
                                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/80">
                                    <span class="text-[10px] text-slate-400 font-bold uppercase block mb-1">Kebutuhan Khusus / Disabilitas</span>
                                    <span class="font-bold text-slate-900">{{ selectedItem.disabilitas || 'Tidak Ada' }}</span>
                                </div>
                                <div class="sm:col-span-2 md:col-span-3 p-3 bg-slate-50 rounded-xl border border-slate-200/80" v-if="selectedItem.talenta">
                                    <span class="text-[10px] text-slate-400 font-bold uppercase block mb-1">Bidang Keahlian / Pelayanan Paroki</span>
                                    <span class="font-bold text-slate-900">{{ selectedItem.talenta }}</span>
                                </div>
                                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/80" v-if="selectedItem.handphone">
                                    <span class="text-[10px] text-slate-400 font-bold uppercase block mb-1">No. Handphone / WhatsApp</span>
                                    <span class="font-bold text-slate-900">{{ selectedItem.handphone }}</span>
                                </div>
                                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/80" v-if="selectedItem.email">
                                    <span class="text-[10px] text-slate-400 font-bold uppercase block mb-1">Alamat Email</span>
                                    <span class="font-bold text-slate-900">{{ selectedItem.email }}</span>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- 1. DETAIL KEUSKUPAN -->
                    <template v-else-if="moduleKey === 'keuskupan'">
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-start">
                            <div class="md:col-span-4 flex flex-col items-center text-center p-5 bg-slate-50/80 rounded-2xl border border-slate-200/80">
                                <div class="w-28 h-28 rounded-2xl overflow-hidden bg-white border border-slate-200 p-2 shadow-xs mb-3 flex items-center justify-center">
                                    <img
                                        :src="selectedItem?.logo ? getImageUrl(selectedItem.logo) : (selectedItem?.logo_url || '/images/logo-keuskupan.png')"
                                        :alt="selectedItem?.nama_keuskupan || 'Logo Keuskupan'"
                                        class="w-full h-full object-contain"
                                        @error="(e) => { e.target.onerror = null; e.target.src = '/images/logo-keuskupan.png'; }"
                                    />
                                </div>
                                <h4 class="font-black text-slate-900 text-sm leading-snug">
                                    {{ selectedItem.nama_keuskupan || '—' }}
                                </h4>
                                <p v-if="selectedItem.nama_latin || selectedItem.nama_keuskupan_latin" class="text-xs text-slate-500 italic mt-0.5 font-serif">
                                    {{ selectedItem.nama_latin || selectedItem.nama_keuskupan_latin }}
                                </p>
                                <span class="mt-3 inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    <span>AKTIF</span>
                                </span>
                            </div>

                            <div class="md:col-span-8 grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                                <div class="p-3 bg-slate-50/80 rounded-xl border border-slate-200/80">
                                    <span class="text-[10px] text-slate-400 font-bold uppercase block mb-1 flex items-center gap-1.5">
                                        <i class="fa-solid fa-barcode text-amber-500"></i> Kode Keuskupan
                                    </span>
                                    <span class="px-2.5 py-0.5 rounded bg-amber-50 text-amber-800 font-mono font-bold text-xs border border-amber-200 inline-block">
                                        {{ selectedItem.kode_keuskupan || selectedItem.kode || '—' }}
                                    </span>
                                </div>

                                <div class="p-3 bg-slate-50/80 rounded-xl border border-slate-200/80">
                                    <span class="text-[10px] text-slate-400 font-bold uppercase block mb-1 flex items-center gap-1.5">
                                        <i class="fa-solid fa-cross text-emerald-600"></i> Uskup
                                    </span>
                                    <span class="font-bold text-slate-900">
                                        {{ selectedItem.uskup || selectedItem.nama_uskup || '—' }}
                                    </span>
                                </div>

                                <div class="sm:col-span-2 p-3 bg-slate-50/80 rounded-xl border border-slate-200/80">
                                    <span class="text-[10px] text-slate-400 font-bold uppercase block mb-1 flex items-center gap-1.5">
                                        <i class="fa-solid fa-location-dot text-rose-500"></i> Alamat Kantor
                                    </span>
                                    <span class="text-slate-800 leading-relaxed font-medium">
                                        {{ selectedItem.alamat || '—' }}
                                    </span>
                                </div>

                                <div class="p-3 bg-slate-50/80 rounded-xl border border-slate-200/80">
                                    <span class="text-[10px] text-slate-400 font-bold uppercase block mb-1 flex items-center gap-1.5">
                                        <i class="fa-solid fa-phone text-cyan-600"></i> Telepon
                                    </span>
                                    <span class="font-medium text-slate-800">
                                        {{ selectedItem.no_telp || selectedItem.telepon || '—' }}
                                    </span>
                                </div>

                                <div class="p-3 bg-slate-50/80 rounded-xl border border-slate-200/80">
                                    <span class="text-[10px] text-slate-400 font-bold uppercase block mb-1 flex items-center gap-1.5">
                                        <i class="fa-solid fa-envelope text-amber-500"></i> Email
                                    </span>
                                    <span class="font-medium text-slate-800">
                                        {{ selectedItem.email || '—' }}
                                    </span>
                                </div>

                                <div class="p-3 bg-slate-50/80 rounded-xl border border-slate-200/80">
                                    <span class="text-[10px] text-slate-400 font-bold uppercase block mb-1 flex items-center gap-1.5">
                                        <i class="fa-solid fa-globe text-amber-600"></i> Website
                                    </span>
                                    <a
                                        v-if="selectedItem.website"
                                        :href="selectedItem.website.startsWith('http') ? selectedItem.website : `https://${selectedItem.website}`"
                                        target="_blank"
                                        class="text-amber-700 hover:underline font-semibold flex items-center gap-1 truncate"
                                    >
                                        <span>{{ selectedItem.website }}</span>
                                        <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                                    </a>
                                    <span v-else class="text-slate-400">—</span>
                                </div>

                                <div class="p-3 bg-slate-50/80 rounded-xl border border-slate-200/80">
                                    <span class="text-[10px] text-slate-400 font-bold uppercase block mb-1 flex items-center gap-1.5">
                                        <i class="fa-solid fa-circle-info text-purple-500"></i> Keterangan
                                    </span>
                                    <span class="text-slate-600">
                                        {{ selectedItem.keterangan || '—' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Daftar Kevikepan -->
                        <div v-if="selectedItem.dekenats" class="space-y-3 pt-3 border-t border-slate-200">
                            <div class="flex items-center justify-between">
                                <h4 class="text-xs font-black text-slate-900 flex items-center gap-2">
                                    <i class="fa-solid fa-layer-group text-amber-600"></i>
                                    <span>Daftar Kevikepan / Dekenat Terdaftar</span>
                                </h4>
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                    {{ selectedItem.dekenats.length }} Kevikepan
                                </span>
                            </div>

                            <div class="rounded-2xl border border-slate-200 overflow-hidden">
                                <table class="w-full text-left text-xs">
                                    <thead class="bg-slate-50 text-slate-600 font-bold text-[10px] uppercase border-b border-slate-200">
                                        <tr>
                                            <th class="px-3.5 py-2.5 text-center w-10">NO</th>
                                            <th class="px-4 py-2.5">NAMA KEVIKEPAN / DEKENAT</th>
                                            <th class="px-4 py-2.5">VIKEP (DEKEN)</th>
                                            <th class="px-4 py-2.5 text-center">PAROKI</th>
                                            <th class="px-4 py-2.5 text-center">STATUS</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 text-slate-700">
                                        <tr v-for="(dek, dIdx) in selectedItem.dekenats" :key="dek.id_kevikepan || dek.id_dekenat || dek.id || dIdx" class="hover:bg-slate-50/70">
                                            <td class="px-3.5 py-2.5 text-center text-slate-400 font-bold text-[11px]">{{ dIdx + 1 }}</td>
                                            <td class="px-4 py-2.5 font-bold text-slate-900">
                                                {{ dek.nama_kevikepan || dek.nama_dekenat || dek.nama || '—' }}
                                            </td>
                                            <td class="px-4 py-2.5">
                                                <span v-if="dek.vikep || dek.nama_vikep || dek.nama_deken || dek.deken" class="inline-flex items-center gap-1.5 text-slate-800">
                                                    <i class="fa-solid fa-user-tie text-emerald-600 text-xs"></i>
                                                    <span>{{ dek.vikep || dek.nama_vikep || dek.nama_deken || dek.deken }}</span>
                                                </span>
                                                <span v-else class="text-slate-400">—</span>
                                            </td>
                                            <td class="px-4 py-2.5 text-center">
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                                    <i class="fa-solid fa-church text-[9px]"></i>
                                                    <span>{{ (dek.parokis && dek.parokis.length) ? dek.parokis.length : (dek.jumlah_paroki || dek.parokis_count || 0) }} Paroki</span>
                                                </span>
                                            </td>
                                            <td class="px-4 py-2.5 text-center">
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[9px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                    <span>Aktif</span>
                                                </span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </template>

                    <!-- 2. DETAIL KEVIKEPAN / DEKENAT -->
                    <template v-else-if="moduleKey === 'dekenat' || moduleKey === 'kevikepan'">
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-start">
                            <div class="md:col-span-4 flex flex-col items-center text-center p-5 bg-slate-50/80 rounded-2xl border border-slate-200/80">
                                <div class="w-24 h-24 rounded-2xl bg-amber-500/10 text-amber-600 border border-amber-200/80 flex items-center justify-center text-4xl mb-3 shadow-2xs">
                                    <i class="fa-solid fa-layer-group"></i>
                                </div>
                                <h4 class="font-black text-slate-900 text-sm leading-snug">
                                    {{ selectedItem.nama_kevikepan || selectedItem.nama_dekenat || selectedItem.nama || '—' }}
                                </h4>
                                <p class="text-xs text-amber-800 font-semibold mt-1">
                                    {{ selectedItem.keuskupan ? selectedItem.keuskupan.nama_keuskupan : 'Keuskupan' }}
                                </p>
                                <span class="mt-3 inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    <span>AKTIF</span>
                                </span>
                            </div>

                            <div class="md:col-span-8 grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                                <div class="p-3 bg-slate-50/80 rounded-xl border border-slate-200/80">
                                    <span class="text-[10px] text-slate-400 font-bold uppercase block mb-1 flex items-center gap-1.5">
                                        <i class="fa-solid fa-barcode text-amber-500"></i> Kode Kevikepan
                                    </span>
                                    <span class="px-2.5 py-0.5 rounded bg-amber-50 text-amber-800 font-mono font-bold text-xs border border-amber-200 inline-block">
                                        {{ selectedItem.kode_kevikepan || selectedItem.kode_dekenat || selectedItem.kode || '—' }}
                                    </span>
                                </div>

                                <div class="p-3 bg-slate-50/80 rounded-xl border border-slate-200/80">
                                    <span class="text-[10px] text-slate-400 font-bold uppercase block mb-1 flex items-center gap-1.5">
                                        <i class="fa-solid fa-user-tie text-emerald-600"></i> Vikep (Deken)
                                    </span>
                                    <span class="font-bold text-slate-900">
                                        {{ selectedItem.vikep || selectedItem.nama_vikep || selectedItem.nama_deken || selectedItem.deken || '—' }}
                                    </span>
                                </div>

                                <div class="sm:col-span-2 p-3 bg-slate-50/80 rounded-xl border border-slate-200/80">
                                    <span class="text-[10px] text-slate-400 font-bold uppercase block mb-1 flex items-center gap-1.5">
                                        <i class="fa-solid fa-church text-amber-600"></i> Keuskupan Naungan
                                    </span>
                                    <span class="font-bold text-slate-900">
                                        {{ selectedItem.keuskupan ? selectedItem.keuskupan.nama_keuskupan : '—' }}
                                    </span>
                                </div>

                                <div class="sm:col-span-2 p-3 bg-slate-50/80 rounded-xl border border-slate-200/80">
                                    <span class="text-[10px] text-slate-400 font-bold uppercase block mb-1 flex items-center gap-1.5">
                                        <i class="fa-solid fa-location-dot text-rose-500"></i> Alamat Kantor
                                    </span>
                                    <span class="text-slate-800 leading-relaxed font-medium">
                                        {{ selectedItem.alamat || '—' }}
                                    </span>
                                </div>

                                <div class="p-3 bg-slate-50/80 rounded-xl border border-slate-200/80">
                                    <span class="text-[10px] text-slate-400 font-bold uppercase block mb-1 flex items-center gap-1.5">
                                        <i class="fa-solid fa-phone text-cyan-600"></i> Telepon / Kontak
                                    </span>
                                    <span class="font-medium text-slate-800">
                                        {{ selectedItem.telepon || selectedItem.no_telp || '—' }}
                                    </span>
                                </div>

                                <div class="p-3 bg-slate-50/80 rounded-xl border border-slate-200/80">
                                    <span class="text-[10px] text-slate-400 font-bold uppercase block mb-1 flex items-center gap-1.5">
                                        <i class="fa-solid fa-envelope text-amber-500"></i> Email
                                    </span>
                                    <span class="font-medium text-slate-800">
                                        {{ selectedItem.email || '—' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Embedded Table: Paroki di Bawah Kevikepan Ini -->
                        <div class="space-y-3 pt-3 border-t border-slate-200">
                            <div class="flex items-center justify-between">
                                <h4 class="text-xs font-black text-slate-900 flex items-center gap-2">
                                    <i class="fa-solid fa-church text-amber-600"></i>
                                    <span>Daftar Paroki di Kevikepan Ini</span>
                                </h4>
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                    {{ (selectedItem.parokis && selectedItem.parokis.length) ? selectedItem.parokis.length : 0 }} Paroki
                                </span>
                            </div>

                            <div class="rounded-2xl border border-slate-200 overflow-hidden">
                                <table class="w-full text-left text-xs">
                                    <thead class="bg-slate-50 text-slate-600 font-bold text-[10px] uppercase border-b border-slate-200">
                                        <tr>
                                            <th class="px-3.5 py-2.5 text-center w-10">NO</th>
                                            <th class="px-4 py-2.5">NAMA PAROKI</th>
                                            <th class="px-4 py-2.5">PASTOR PAROKI</th>
                                            <th class="px-4 py-2.5">KONTAK</th>
                                            <th class="px-4 py-2.5 text-center">STATUS</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 text-slate-700">
                                        <tr v-for="(p, pIdx) in selectedItem.parokis" :key="p.id_paroki || pIdx" class="hover:bg-slate-50/70">
                                            <td class="px-3.5 py-2.5 text-center text-slate-400 font-bold text-[11px]">{{ pIdx + 1 }}</td>
                                            <td class="px-4 py-2.5 font-bold text-slate-900">
                                                {{ p.nama_paroki || p.nama || '—' }}
                                            </td>
                                            <td class="px-4 py-2.5 text-slate-800">
                                                <span v-if="p.nama_pastor_paroki_aktif || p.pastor_paroki" class="inline-flex items-center gap-1.5">
                                                    <i class="fa-solid fa-user-tie text-emerald-600 text-xs"></i>
                                                    <span>{{ p.nama_pastor_paroki_aktif || p.pastor_paroki }}</span>
                                                </span>
                                                <span v-else class="text-slate-400">—</span>
                                            </td>
                                            <td class="px-4 py-2.5 text-slate-600">{{ p.telepon || p.whatsapp || '—' }}</td>
                                            <td class="px-4 py-2.5 text-center">
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[9px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                    <span>Aktif</span>
                                                </span>
                                            </td>
                                        </tr>
                                        <tr v-if="!selectedItem.parokis || !selectedItem.parokis.length">
                                            <td colspan="5" class="px-4 py-6 text-center text-slate-400 text-xs">
                                                Belum ada data paroki terdaftar di kevikepan ini.
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </template>

                    <!-- 3. DETAIL PAROKI -->
                    <template v-else-if="moduleKey === 'paroki'">
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-start">
                            <div class="md:col-span-4 flex flex-col items-center text-center p-5 bg-slate-50/80 rounded-2xl border border-slate-200/80">
                                <div class="w-28 h-28 rounded-2xl overflow-hidden bg-white border border-slate-200 p-2 shadow-xs mb-3 flex items-center justify-center">
                                    <img
                                        :src="selectedItem?.logo ? getImageUrl(selectedItem.logo) : (selectedItem?.logo_url || '/images/logo-paroki.png')"
                                        :alt="selectedItem?.nama_paroki || 'Logo Paroki'"
                                        class="w-full h-full object-contain"
                                        @error="(e) => { e.target.onerror = null; e.target.src = '/images/logo-paroki.png'; }"
                                    />
                                </div>
                                <h4 class="font-black text-slate-900 text-sm leading-snug">
                                    {{ selectedItem.nama_paroki || '—' }}
                                </h4>
                                <p v-if="selectedItem.pelindung_paroki" class="text-xs text-amber-800 font-semibold mt-0.5">
                                    Pelindung: {{ selectedItem.pelindung_paroki }}
                                </p>
                                <span :class="[
                                    'mt-3 inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-bold border',
                                    (selectedItem.status === 'Aktif' || !selectedItem.status)
                                        ? 'bg-emerald-50 text-emerald-700 border-emerald-200'
                                        : 'bg-rose-50 text-rose-700 border-rose-200'
                                ]">
                                    <span :class="['w-1.5 h-1.5 rounded-full', (selectedItem.status === 'Aktif' || !selectedItem.status) ? 'bg-emerald-500' : 'bg-rose-500']"></span>
                                    <span>{{ selectedItem.status || 'Aktif' }}</span>
                                </span>
                            </div>

                            <div class="md:col-span-8 grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                                <div class="p-3 bg-slate-50/80 rounded-xl border border-slate-200/80">
                                    <span class="text-[10px] text-slate-400 font-bold uppercase block mb-1 flex items-center gap-1.5">
                                        <i class="fa-solid fa-barcode text-amber-500"></i> Kode Paroki
                                    </span>
                                    <span class="px-2.5 py-0.5 rounded bg-amber-50 text-amber-800 font-mono font-bold text-xs border border-amber-200 inline-block">
                                        {{ selectedItem.kode_paroki || selectedItem.kode || '—' }}
                                    </span>
                                </div>

                                <div class="p-3 bg-slate-50/80 rounded-xl border border-slate-200/80">
                                    <span class="text-[10px] text-slate-400 font-bold uppercase block mb-1 flex items-center gap-1.5">
                                        <i class="fa-solid fa-church text-amber-600"></i> Keuskupan Induk
                                    </span>
                                    <span class="font-bold text-slate-900">
                                        {{ selectedItem.keuskupan ? selectedItem.keuskupan.nama_keuskupan : '—' }}
                                    </span>
                                </div>

                                <div class="p-3 bg-slate-50/80 rounded-xl border border-slate-200/80">
                                    <span class="text-[10px] text-slate-400 font-bold uppercase block mb-1 flex items-center gap-1.5">
                                        <i class="fa-solid fa-layer-group text-cyan-600"></i> Kevikepan / Dekenat
                                    </span>
                                    <span class="font-bold text-slate-900">
                                        {{ selectedItem.dekenat ? (selectedItem.dekenat.nama_kevikepan || selectedItem.dekenat.nama_dekenat) : '—' }}
                                    </span>
                                </div>

                                <div class="p-3 bg-slate-50/80 rounded-xl border border-slate-200/80">
                                    <span class="text-[10px] text-slate-400 font-bold uppercase block mb-1 flex items-center gap-1.5">
                                        <i class="fa-solid fa-user-tie text-emerald-600"></i> Pastor Paroki
                                    </span>
                                    <span class="font-bold text-slate-900">
                                        {{ selectedItem.nama_pastor_paroki_aktif || selectedItem.pastor_paroki || '—' }}
                                    </span>
                                </div>

                                <div class="sm:col-span-2 p-3 bg-slate-50/80 rounded-xl border border-slate-200/80" v-if="selectedItem.nama_pastor_rekan">
                                    <span class="text-[10px] text-slate-400 font-bold uppercase block mb-1 flex items-center gap-1.5">
                                        <i class="fa-solid fa-users text-amber-600"></i> Pastor Rekan
                                    </span>
                                    <div class="flex flex-wrap gap-1.5 mt-1">
                                        <span
                                            v-for="(pr, prIdx) in (selectedItem.nama_pastor_rekan.split ? selectedItem.nama_pastor_rekan.split(',').map(s=>s.trim()).filter(Boolean) : [selectedItem.nama_pastor_rekan])"
                                            :key="prIdx"
                                            class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg bg-amber-50 border border-amber-200 text-xs font-semibold text-amber-900"
                                        >
                                            <i class="fa-solid fa-user-tie text-[10px] text-amber-600"></i>
                                            <span>{{ pr }}</span>
                                        </span>
                                    </div>
                                </div>

                                <div class="sm:col-span-2 p-3 bg-slate-50/80 rounded-xl border border-slate-200/80">
                                    <span class="text-[10px] text-slate-400 font-bold uppercase block mb-1 flex items-center gap-1.5">
                                        <i class="fa-solid fa-location-dot text-rose-500"></i> Alamat & Wilayah Administratif
                                    </span>
                                    <p class="text-slate-800 leading-relaxed font-medium mb-1.5">
                                        {{ selectedItem.alamat || '—' }}
                                    </p>
                                    <div class="flex flex-wrap gap-2 text-[11px] text-slate-600 font-medium pt-1 border-t border-slate-200/60">
                                        <span v-if="selectedItem.desa || selectedItem.kelurahan">Desa/Kel: <b>{{ selectedItem.desa?.nama_desa || selectedItem.kelurahan }}</b></span>
                                        <span v-if="selectedItem.kecamatan">• Kec: <b>{{ selectedItem.kecamatan?.nama_kecamatan }}</b></span>
                                        <span v-if="selectedItem.kabupaten">• Kab/Kota: <b>{{ selectedItem.kabupaten?.nama_kabupaten }}</b></span>
                                        <span v-if="selectedItem.provinsi">• Prov: <b>{{ selectedItem.provinsi?.nama_provinsi }}</b></span>
                                    </div>
                                </div>

                                <div class="p-3 bg-slate-50/80 rounded-xl border border-slate-200/80">
                                    <span class="text-[10px] text-slate-400 font-bold uppercase block mb-1 flex items-center gap-1.5">
                                        <i class="fa-solid fa-phone text-cyan-600"></i> Telepon / WA
                                    </span>
                                    <span class="font-medium text-slate-800">
                                        {{ selectedItem.telepon || selectedItem.whatsapp || '—' }}
                                    </span>
                                </div>

                                <div class="p-3 bg-slate-50/80 rounded-xl border border-slate-200/80">
                                    <span class="text-[10px] text-slate-400 font-bold uppercase block mb-1 flex items-center gap-1.5">
                                        <i class="fa-solid fa-envelope text-amber-500"></i> Email
                                    </span>
                                    <span class="font-medium text-slate-800">
                                        {{ selectedItem.email || '—' }}
                                    </span>
                                </div>

                                <div class="p-3 bg-slate-50/80 rounded-xl border border-slate-200/80">
                                    <span class="text-[10px] text-slate-400 font-bold uppercase block mb-1 flex items-center gap-1.5">
                                        <i class="fa-solid fa-globe text-amber-600"></i> Website
                                    </span>
                                    <a
                                        v-if="selectedItem.website"
                                        :href="selectedItem.website.startsWith('http') ? selectedItem.website : `https://${selectedItem.website}`"
                                        target="_blank"
                                        class="text-amber-700 hover:underline font-semibold flex items-center gap-1 truncate"
                                    >
                                        <span>{{ selectedItem.website }}</span>
                                        <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                                    </a>
                                    <span v-else class="text-slate-400">—</span>
                                </div>

                                <div class="p-3 bg-slate-50/80 rounded-xl border border-slate-200/80">
                                    <span class="text-[10px] text-slate-400 font-bold uppercase block mb-1 flex items-center gap-1.5">
                                        <i class="fa-solid fa-circle-info text-purple-500"></i> Keterangan
                                    </span>
                                    <span class="text-slate-700 font-medium">
                                        {{ selectedItem.keterangan || '—' }}
                                    </span>
                                </div>

                                <!-- Maps Coordinates & Link -->
                                <div class="sm:col-span-2 p-3 bg-slate-50/80 rounded-xl border border-slate-200/80 space-y-2" v-if="selectedItem.maps_url || selectedItem.latitude || selectedItem.maps_embed">
                                    <div class="flex items-center justify-between">
                                        <span class="text-[10px] text-slate-400 font-bold uppercase flex items-center gap-1.5">
                                            <i class="fa-solid fa-map-location-dot text-emerald-600"></i> Lokasi & Peta Google Maps
                                        </span>
                                        <a
                                            v-if="selectedItem.maps_url"
                                            :href="selectedItem.maps_url"
                                            target="_blank"
                                            class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-700 hover:underline"
                                        >
                                            <span>Buka di Google Maps</span>
                                            <i class="fa-solid fa-arrow-up-right-from-square text-[9px]"></i>
                                        </a>
                                    </div>
                                    <div class="flex gap-4 text-xs font-mono text-slate-700" v-if="selectedItem.latitude || selectedItem.longitude">
                                        <span>Lat: <b>{{ selectedItem.latitude || '—' }}</b></span>
                                        <span>Long: <b>{{ selectedItem.longitude || '—' }}</b></span>
                                    </div>
                                    <!-- Render Embed Iframe if provided -->
                                    <div v-if="selectedItem.maps_embed" class="rounded-xl overflow-hidden border border-slate-200 aspect-video w-full [&_iframe]:w-full [&_iframe]:h-full" v-html="selectedItem.maps_embed"></div>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- 3B. DETAIL STASI / KAPELA LENGKAP -->
                    <template v-else-if="moduleKey === 'kapela' || moduleKey === 'stasi-kapela' || moduleKey === 'stasi_kapela'">
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-start">
                            <div class="md:col-span-4 flex flex-col items-center text-center p-5 bg-slate-50/80 rounded-2xl border border-slate-200/80">
                                <div class="w-24 h-24 rounded-2xl overflow-hidden bg-white border border-slate-200 p-2 shadow-xs mb-3 flex items-center justify-center">
                                    <img
                                        v-if="selectedItem.foto || selectedItem.logo || selectedItem.ikon || selectedItem.gambar"
                                        :src="getImageUrl(selectedItem.foto || selectedItem.logo || selectedItem.ikon || selectedItem.gambar)"
                                        :alt="selectedItem.nama_kapela || selectedItem.nama_stasi || 'Kapela'"
                                        class="w-full h-full object-cover rounded-xl"
                                        @error="(e) => { e.target.onerror = null; e.target.parentElement.innerHTML = '<i class=\'fa-solid fa-place-of-worship text-4xl text-amber-600\'></i>'; }"
                                    />
                                    <i v-else class="fa-solid fa-place-of-worship text-4xl text-amber-600"></i>
                                </div>
                                <h4 class="font-black text-slate-900 text-sm leading-snug">
                                    {{ selectedItem.nama_kapela || selectedItem.nama_stasi || selectedItem.nama || 'Stasi / Kapela' }}
                                </h4>
                                <p v-if="selectedItem.pelindung || selectedItem.nama_pelindung" class="text-xs text-amber-800 font-semibold mt-1">
                                    Pelindung: {{ selectedItem.pelindung || selectedItem.nama_pelindung }}
                                </p>
                                <span class="mt-3 inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    <span>{{ selectedItem.status || selectedItem.status_aktif || 'Aktif' }}</span>
                                </span>
                            </div>

                            <div class="md:col-span-8 grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                                <div class="p-3 bg-slate-50/80 rounded-xl border border-slate-200/80">
                                    <span class="text-[10px] text-slate-400 font-bold uppercase block mb-1 flex items-center gap-1.5">
                                        <i class="fa-solid fa-barcode text-amber-500"></i> Kode Stasi / Kapela
                                    </span>
                                    <span class="px-2.5 py-0.5 rounded bg-amber-50 text-amber-800 font-mono font-bold text-xs border border-amber-200 inline-block">
                                        {{ selectedItem.kode_kapela || selectedItem.kode_stasi || selectedItem.kode || '—' }}
                                    </span>
                                </div>

                                <div class="p-3 bg-slate-50/80 rounded-xl border border-slate-200/80">
                                    <span class="text-[10px] text-slate-400 font-bold uppercase block mb-1 flex items-center gap-1.5">
                                        <i class="fa-solid fa-church text-emerald-600"></i> Paroki Induk
                                    </span>
                                    <span class="font-bold text-slate-900">
                                        {{ selectedItem.paroki?.nama_paroki || selectedItem.nama_paroki || 'Paroki St. Vinsensius a Paulo - Benlutu' }}
                                    </span>
                                </div>

                                <div class="p-3 bg-slate-50/80 rounded-xl border border-slate-200/80">
                                    <span class="text-[10px] text-slate-400 font-bold uppercase block mb-1 flex items-center gap-1.5">
                                        <i class="fa-solid fa-user-tie text-blue-600"></i> Penanggung Jawab / Ketua Stasi
                                    </span>
                                    <span class="font-bold text-slate-900">
                                        {{ selectedItem.penanggung_jawab || selectedItem.ketua_stasi || selectedItem.ketua_dps || selectedItem.nama_ketua || '—' }}
                                    </span>
                                </div>

                                <div class="p-3 bg-slate-50/80 rounded-xl border border-slate-200/80">
                                    <span class="text-[10px] text-slate-400 font-bold uppercase block mb-1 flex items-center gap-1.5">
                                        <i class="fa-solid fa-phone text-cyan-600"></i> Telepon / Kontak WA
                                    </span>
                                    <span class="font-medium text-slate-800">
                                        {{ selectedItem.telepon || selectedItem.kontak || selectedItem.no_telp || selectedItem.whatsapp || '—' }}
                                    </span>
                                </div>

                                <div class="p-3 bg-slate-50/80 rounded-xl border border-slate-200/80">
                                    <span class="text-[10px] text-slate-400 font-bold uppercase block mb-1 flex items-center gap-1.5">
                                        <i class="fa-solid fa-calendar text-purple-600"></i> Tahun Berdiri / Diresmikan
                                    </span>
                                    <span class="font-medium text-slate-800">
                                        {{ selectedItem.tahun_berdiri || selectedItem.tgl_peresmian || selectedItem.tahun || '—' }}
                                    </span>
                                </div>

                                <div class="p-3 bg-slate-50/80 rounded-xl border border-slate-200/80">
                                    <span class="text-[10px] text-slate-400 font-bold uppercase block mb-1 flex items-center gap-1.5">
                                        <i class="fa-solid fa-people-roof text-indigo-600"></i> Jumlah KUB / KBG
                                    </span>
                                    <span class="font-bold text-slate-900">
                                        {{ selectedItem.jumlah_kub || (selectedItem.kubs ? selectedItem.kubs.length : (selectedItem.total_kub || '—')) }} KUB
                                    </span>
                                </div>

                                <div class="sm:col-span-2 p-3 bg-slate-50/80 rounded-xl border border-slate-200/80">
                                    <span class="text-[10px] text-slate-400 font-bold uppercase block mb-1 flex items-center gap-1.5">
                                        <i class="fa-solid fa-location-dot text-rose-500"></i> Alamat & Lokasi
                                    </span>
                                    <p class="text-slate-800 leading-relaxed font-medium mb-1">
                                        {{ selectedItem.alamat || selectedItem.lokasi || '—' }}
                                    </p>
                                    <div class="flex flex-wrap gap-2 text-[11px] text-slate-600 font-medium pt-1 border-t border-slate-200/60" v-if="selectedItem.desa || selectedItem.kecamatan">
                                        <span v-if="selectedItem.desa || selectedItem.kelurahan">Desa/Kel: <b>{{ selectedItem.desa?.nama_desa || selectedItem.kelurahan || selectedItem.desa }}</b></span>
                                        <span v-if="selectedItem.kecamatan">• Kec: <b>{{ selectedItem.kecamatan?.nama_kecamatan || selectedItem.kecamatan }}</b></span>
                                    </div>
                                </div>

                                <div class="sm:col-span-2 p-3 bg-slate-50/80 rounded-xl border border-slate-200/80" v-if="selectedItem.keterangan || selectedItem.deskripsi || selectedItem.catatan">
                                    <span class="text-[10px] text-slate-400 font-bold uppercase block mb-1 flex items-center gap-1.5">
                                        <i class="fa-solid fa-circle-info text-amber-500"></i> Keterangan Pastoral & Sejarah
                                    </span>
                                    <p class="text-slate-700 leading-relaxed font-medium">
                                        {{ selectedItem.keterangan || selectedItem.deskripsi || selectedItem.catatan }}
                                    </p>
                                </div>

                                <div class="sm:col-span-2 p-3 bg-slate-50/80 rounded-xl border border-slate-200/80 space-y-2" v-if="selectedItem.maps_url || selectedItem.latitude || selectedItem.longitude">
                                    <div class="flex items-center justify-between">
                                        <span class="text-[10px] text-slate-400 font-bold uppercase flex items-center gap-1.5">
                                            <i class="fa-solid fa-map-location-dot text-emerald-600"></i> Koordinat & Peta Google Maps
                                        </span>
                                        <a
                                            v-if="selectedItem.maps_url || (selectedItem.latitude && selectedItem.longitude)"
                                            :href="selectedItem.maps_url || `https://www.google.com/maps?q=${selectedItem.latitude},${selectedItem.longitude}`"
                                            target="_blank"
                                            class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-700 hover:underline"
                                        >
                                            <span>Buka di Google Maps</span>
                                            <i class="fa-solid fa-arrow-up-right-from-square text-[9px]"></i>
                                        </a>
                                    </div>
                                    <div class="flex gap-4 text-xs font-mono text-slate-700" v-if="selectedItem.latitude || selectedItem.longitude">
                                        <span>Latitude: <b>{{ selectedItem.latitude || '—' }}</b></span>
                                        <span>Longitude: <b>{{ selectedItem.longitude || '—' }}</b></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- 3C. DETAIL WILAYAH PASTORAL -->
                    <template v-else-if="moduleKey === 'wilayah'">
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-start">
                            <div class="md:col-span-4 flex flex-col items-center text-center p-5 bg-slate-50/80 rounded-2xl border border-slate-200/80">
                                <div class="w-24 h-24 rounded-2xl bg-amber-500/10 text-amber-600 border border-amber-200/80 flex items-center justify-center text-4xl mb-3 shadow-2xs">
                                    <i class="fa-solid fa-map-location-dot"></i>
                                </div>
                                <h4 class="font-black text-slate-900 text-sm leading-snug">
                                    {{ selectedItem.nama_wilayah || selectedItem.nama || 'Wilayah Pastoral' }}
                                </h4>
                                <p class="text-xs text-amber-800 font-semibold mt-1">
                                    {{ selectedItem.paroki?.nama_paroki || 'Paroki St. Vinsensius a Paulo - Benlutu' }}
                                </p>
                                <span class="mt-3 inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    <span>{{ selectedItem.status || selectedItem.status_aktif || 'Aktif' }}</span>
                                </span>
                            </div>

                            <div class="md:col-span-8 grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                                <div class="p-3 bg-slate-50/80 rounded-xl border border-slate-200/80">
                                    <span class="text-[10px] text-slate-400 font-bold uppercase block mb-1 flex items-center gap-1.5">
                                        <i class="fa-solid fa-barcode text-amber-500"></i> Kode Wilayah
                                    </span>
                                    <span class="px-2.5 py-0.5 rounded bg-amber-50 text-amber-800 font-mono font-bold text-xs border border-amber-200 inline-block">
                                        {{ selectedItem.kode_wilayah || selectedItem.kode || '—' }}
                                    </span>
                                </div>

                                <div class="p-3 bg-slate-50/80 rounded-xl border border-slate-200/80">
                                    <span class="text-[10px] text-slate-400 font-bold uppercase block mb-1 flex items-center gap-1.5">
                                        <i class="fa-solid fa-church text-emerald-600"></i> Paroki Induk
                                    </span>
                                    <span class="font-bold text-slate-900">
                                        {{ selectedItem.paroki?.nama_paroki || 'Paroki St. Vinsensius a Paulo - Benlutu' }}
                                    </span>
                                </div>

                                <div class="p-3 bg-slate-50/80 rounded-xl border border-slate-200/80">
                                    <span class="text-[10px] text-slate-400 font-bold uppercase block mb-1 flex items-center gap-1.5">
                                        <i class="fa-solid fa-user-tie text-blue-600"></i> Ketua / Koordinator Wilayah
                                    </span>
                                    <span class="font-bold text-slate-900">
                                        {{ selectedItem.ketua_wilayah || selectedItem.koordinator || selectedItem.penanggung_jawab || selectedItem.nama_ketua || '—' }}
                                    </span>
                                </div>

                                <div class="p-3 bg-slate-50/80 rounded-xl border border-slate-200/80">
                                    <span class="text-[10px] text-slate-400 font-bold uppercase block mb-1 flex items-center gap-1.5">
                                        <i class="fa-solid fa-phone text-cyan-600"></i> Kontak / Telepon
                                    </span>
                                    <span class="font-medium text-slate-800">
                                        {{ selectedItem.telepon || selectedItem.kontak || selectedItem.whatsapp || selectedItem.no_telp || '—' }}
                                    </span>
                                </div>

                                <div class="p-3 bg-slate-50/80 rounded-xl border border-slate-200/80">
                                    <span class="text-[10px] text-slate-400 font-bold uppercase block mb-1 flex items-center gap-1.5">
                                        <i class="fa-solid fa-people-roof text-indigo-600"></i> Jumlah KUB / KBG
                                    </span>
                                    <span class="font-bold text-slate-900">
                                        {{ selectedItem.jumlah_kub || (selectedItem.kubs ? selectedItem.kubs.length : '—') }} KUB
                                    </span>
                                </div>

                                <div class="p-3 bg-slate-50/80 rounded-xl border border-slate-200/80">
                                    <span class="text-[10px] text-slate-400 font-bold uppercase block mb-1 flex items-center gap-1.5">
                                        <i class="fa-solid fa-place-of-worship text-teal-600"></i> Stasi / Kapela Terkait
                                    </span>
                                    <span class="font-medium text-slate-800">
                                        {{ selectedItem.kapela?.nama_kapela || selectedItem.nama_kapela || '—' }}
                                    </span>
                                </div>

                                <div class="sm:col-span-2 p-3 bg-slate-50/80 rounded-xl border border-slate-200/80" v-if="selectedItem.provinsi || selectedItem.kabupaten || selectedItem.kecamatan || selectedItem.desa">
                                    <span class="text-[10px] text-slate-400 font-bold uppercase block mb-1 flex items-center gap-1.5">
                                        <i class="fa-solid fa-map text-rose-500"></i> Wilayah Administratif Sipil
                                    </span>
                                    <p class="text-slate-800 leading-relaxed font-medium">
                                        <span v-if="selectedItem.desa">Desa/Kel. {{ selectedItem.desa?.nama_desa || selectedItem.desa }}, </span>
                                        <span v-if="selectedItem.kecamatan">Kec. {{ selectedItem.kecamatan?.nama_kecamatan || selectedItem.kecamatan }}, </span>
                                        <span v-if="selectedItem.kabupaten">{{ selectedItem.kabupaten?.nama_kabupaten || selectedItem.kabupaten }}, </span>
                                        <span v-if="selectedItem.provinsi">{{ selectedItem.provinsi?.nama_provinsi || selectedItem.provinsi }}</span>
                                    </p>
                                </div>

                                <div class="sm:col-span-2 p-3 bg-slate-50/80 rounded-xl border border-slate-200/80" v-if="selectedItem.keterangan || selectedItem.deskripsi || selectedItem.alamat">
                                    <span class="text-[10px] text-slate-400 font-bold uppercase block mb-1 flex items-center gap-1.5">
                                        <i class="fa-solid fa-circle-info text-amber-500"></i> Batas & Keterangan Wilayah
                                    </span>
                                    <p class="text-slate-700 leading-relaxed font-medium">
                                        {{ selectedItem.keterangan || selectedItem.deskripsi || selectedItem.alamat }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- 3D. DETAIL KUB / KBG -->
                    <template v-else-if="moduleKey === 'kub'">
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-start">
                            <div class="md:col-span-4 flex flex-col items-center text-center p-5 bg-slate-50/80 rounded-2xl border border-slate-200/80">
                                <div class="w-24 h-24 rounded-2xl bg-amber-500/10 text-amber-600 border border-amber-200/80 flex items-center justify-center text-4xl mb-3 shadow-2xs">
                                    <i class="fa-solid fa-people-roof"></i>
                                </div>
                                <h4 class="font-black text-slate-900 text-sm leading-snug">
                                    {{ selectedItem.nama_kub || selectedItem.nama || 'KUB' }}
                                </h4>
                                <p v-if="selectedItem.pelindung || selectedItem.nama_pelindung" class="text-xs text-amber-800 font-semibold mt-1">
                                    Pelindung: {{ selectedItem.pelindung || selectedItem.nama_pelindung }}
                                </p>
                                <span class="mt-3 inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    <span>{{ selectedItem.status || selectedItem.status_aktif || 'Aktif' }}</span>
                                </span>
                            </div>

                            <div class="md:col-span-8 grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                                <div class="p-3 bg-slate-50/80 rounded-xl border border-slate-200/80">
                                    <span class="text-[10px] text-slate-400 font-bold uppercase block mb-1 flex items-center gap-1.5">
                                        <i class="fa-solid fa-barcode text-amber-500"></i> Kode KUB / KBG
                                    </span>
                                    <span class="px-2.5 py-0.5 rounded bg-amber-50 text-amber-800 font-mono font-bold text-xs border border-amber-200 inline-block">
                                        {{ selectedItem.kode_kub || selectedItem.kode || '—' }}
                                    </span>
                                </div>

                                <div class="p-3 bg-slate-50/80 rounded-xl border border-slate-200/80">
                                    <span class="text-[10px] text-slate-400 font-bold uppercase block mb-1 flex items-center gap-1.5">
                                        <i class="fa-solid fa-map-location-dot text-emerald-600"></i> Wilayah Pastoral
                                    </span>
                                    <span class="font-bold text-slate-900">
                                        {{ selectedItem.wilayah?.nama_wilayah || selectedItem.nama_wilayah || '—' }}
                                    </span>
                                </div>

                                <div class="p-3 bg-slate-50/80 rounded-xl border border-slate-200/80">
                                    <span class="text-[10px] text-slate-400 font-bold uppercase block mb-1 flex items-center gap-1.5">
                                        <i class="fa-solid fa-place-of-worship text-teal-600"></i> Stasi / Kapela Naungan
                                    </span>
                                    <span class="font-bold text-slate-900">
                                        {{ selectedItem.kapela?.nama_kapela || selectedItem.nama_kapela || 'Pusat Paroki' }}
                                    </span>
                                </div>

                                <div class="p-3 bg-slate-50/80 rounded-xl border border-slate-200/80">
                                    <span class="text-[10px] text-slate-400 font-bold uppercase block mb-1 flex items-center gap-1.5">
                                        <i class="fa-solid fa-user-tie text-blue-600"></i> Admin KUB / Pengurus
                                    </span>
                                    <span class="font-bold text-slate-900">
                                        {{ selectedItem.ketua_kub || selectedItem.penanggung_jawab || selectedItem.nama_ketua || '—' }}
                                    </span>
                                </div>

                                <div class="p-3 bg-slate-50/80 rounded-xl border border-slate-200/80">
                                    <span class="text-[10px] text-slate-400 font-bold uppercase block mb-1 flex items-center gap-1.5">
                                        <i class="fa-solid fa-phone text-cyan-600"></i> Kontak WA / Telepon
                                    </span>
                                    <span class="font-medium text-slate-800">
                                        {{ selectedItem.telepon || selectedItem.kontak || selectedItem.whatsapp || selectedItem.no_telp || '—' }}
                                    </span>
                                </div>

                                <div class="p-3 bg-slate-50/80 rounded-xl border border-slate-200/80">
                                    <span class="text-[10px] text-slate-400 font-bold uppercase block mb-1 flex items-center gap-1.5">
                                        <i class="fa-solid fa-house-chimney-user text-indigo-600"></i> Jumlah KK Terdata
                                    </span>
                                    <span class="font-bold text-slate-900">
                                        {{ (selectedItem.jumlah_kk !== undefined && selectedItem.jumlah_kk !== null) ? selectedItem.jumlah_kk : (selectedItem.kks ? selectedItem.kks.length : 0) }} KK
                                    </span>
                                </div>

                                <div class="sm:col-span-2 p-3 bg-slate-50/80 rounded-xl border border-slate-200/80" v-if="selectedItem.lokasi || selectedItem.alamat || selectedItem.jadwal_ibadat">
                                    <span class="text-[10px] text-slate-400 font-bold uppercase block mb-1 flex items-center gap-1.5">
                                        <i class="fa-solid fa-location-dot text-rose-500"></i> Lokasi & Jadwal Ibadat
                                    </span>
                                    <p class="text-slate-800 leading-relaxed font-medium mb-1">
                                        {{ selectedItem.alamat || selectedItem.lokasi || '—' }}
                                    </p>
                                    <p v-if="selectedItem.jadwal_ibadat" class="text-amber-800 text-xs font-semibold">
                                        Jadwal Ibadat: {{ selectedItem.jadwal_ibadat }}
                                    </p>
                                </div>

                                <div class="sm:col-span-2 p-3 bg-slate-50/80 rounded-xl border border-slate-200/80" v-if="selectedItem.desa || selectedItem.kecamatan || selectedItem.kabupaten">
                                    <span class="text-[10px] text-slate-400 font-bold uppercase block mb-1 flex items-center gap-1.5">
                                        <i class="fa-solid fa-map-location-dot text-rose-500"></i> Wilayah Administratif Sipil
                                    </span>
                                    <p class="text-slate-800 leading-relaxed font-semibold">
                                        {{ [selectedItem.desa?.nama_desa, selectedItem.kecamatan?.nama_kecamatan, selectedItem.kabupaten?.nama_kabupaten, selectedItem.provinsi?.nama_provinsi].filter(Boolean).join(', ') || '—' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- 3E. DETAIL WILAYAH SIPIL: PROVINSI -->
                    <template v-else-if="moduleKey === 'provinsi'">
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-start">
                            <div class="md:col-span-4 flex flex-col items-center text-center p-5 bg-slate-50/80 rounded-2xl border border-slate-200/80">
                                <div class="w-24 h-24 rounded-2xl bg-amber-500/10 text-amber-600 border border-amber-200/80 flex items-center justify-center text-4xl mb-3 shadow-2xs">
                                    <i class="fa-solid fa-earth-asia"></i>
                                </div>
                                <h4 class="font-black text-slate-900 text-sm leading-snug">
                                    {{ selectedItem.nama_provinsi || selectedItem.nama || 'Provinsi' }}
                                </h4>
                                <span class="mt-3 inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    <span>WILAYAH SIPIL RI</span>
                                </span>
                            </div>

                            <div class="md:col-span-8 grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                                <div class="p-3 bg-slate-50/80 rounded-xl border border-slate-200/80">
                                    <span class="text-[10px] text-slate-400 font-bold uppercase block mb-1 flex items-center gap-1.5">
                                        <i class="fa-solid fa-barcode text-amber-500"></i> Kode / ID Provinsi (BPS)
                                    </span>
                                    <span class="px-2.5 py-0.5 rounded bg-amber-50 text-amber-800 font-mono font-bold text-xs border border-amber-200 inline-block">
                                        {{ selectedItem.id_provinsi || selectedItem.kode_provinsi || selectedItem.kode || '—' }}
                                    </span>
                                </div>

                                <div class="p-3 bg-slate-50/80 rounded-xl border border-slate-200/80">
                                    <span class="text-[10px] text-slate-400 font-bold uppercase block mb-1 flex items-center gap-1.5">
                                        <i class="fa-solid fa-city text-blue-600"></i> Ibu Kota Provinsi
                                    </span>
                                    <span class="font-bold text-slate-900">
                                        {{ selectedItem.ibu_kota || 'Kupang' }}
                                    </span>
                                </div>

                                <div class="sm:col-span-2 p-3 bg-slate-50/80 rounded-xl border border-slate-200/80">
                                    <span class="text-[10px] text-slate-400 font-bold uppercase block mb-1 flex items-center gap-1.5">
                                        <i class="fa-solid fa-circle-info text-emerald-600"></i> Cakupan Wilayah Pelayanan
                                    </span>
                                    <p class="text-slate-700 leading-relaxed font-medium">
                                        Provinsi {{ selectedItem.nama_provinsi || 'ini' }} merupakan cakupan wilayah administratif pelayanan paroki dan keuskupan.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- 3F. DETAIL WILAYAH SIPIL: KABUPATEN / KOTA -->
                    <template v-else-if="moduleKey === 'kabupaten'">
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-start">
                            <div class="md:col-span-4 flex flex-col items-center text-center p-5 bg-slate-50/80 rounded-2xl border border-slate-200/80">
                                <div class="w-24 h-24 rounded-2xl bg-amber-500/10 text-amber-600 border border-amber-200/80 flex items-center justify-center text-4xl mb-3 shadow-2xs">
                                    <i class="fa-solid fa-city"></i>
                                </div>
                                <h4 class="font-black text-slate-900 text-sm leading-snug">
                                    {{ selectedItem.nama_kabupaten || selectedItem.nama || 'Kabupaten / Kota' }}
                                </h4>
                                <p class="text-xs text-amber-800 font-semibold mt-1">
                                    {{ selectedItem.provinsi?.nama_provinsi || 'Nusa Tenggara Timur' }}
                                </p>
                                <span class="mt-3 inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    <span>WILAYAH TINGKAT II</span>
                                </span>
                            </div>

                            <div class="md:col-span-8 grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                                <div class="p-3 bg-slate-50/80 rounded-xl border border-slate-200/80">
                                    <span class="text-[10px] text-slate-400 font-bold uppercase block mb-1 flex items-center gap-1.5">
                                        <i class="fa-solid fa-barcode text-amber-500"></i> Kode / ID Kabupaten (BPS)
                                    </span>
                                    <span class="px-2.5 py-0.5 rounded bg-amber-50 text-amber-800 font-mono font-bold text-xs border border-amber-200 inline-block">
                                        {{ selectedItem.id_kabupaten || selectedItem.kode_kabupaten || selectedItem.kode || '—' }}
                                    </span>
                                </div>

                                <div class="p-3 bg-slate-50/80 rounded-xl border border-slate-200/80">
                                    <span class="text-[10px] text-slate-400 font-bold uppercase block mb-1 flex items-center gap-1.5">
                                        <i class="fa-solid fa-earth-asia text-emerald-600"></i> Provinsi Induk
                                    </span>
                                    <span class="font-bold text-slate-900">
                                        {{ selectedItem.provinsi?.nama_provinsi || 'Nusa Tenggara Timur (NTT)' }}
                                    </span>
                                </div>

                                <div class="sm:col-span-2 p-3 bg-slate-50/80 rounded-xl border border-slate-200/80">
                                    <span class="text-[10px] text-slate-400 font-bold uppercase block mb-1 flex items-center gap-1.5">
                                        <i class="fa-solid fa-building-columns text-blue-600"></i> Keterangan Wilayah Sipil
                                    </span>
                                    <p class="text-slate-700 leading-relaxed font-medium">
                                        {{ selectedItem.keterangan || `Kabupaten ${selectedItem.nama_kabupaten || ''} menaungi wilayah administrasi sipil kecamatan dan desa di paroki.` }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- 3G. DETAIL WILAYAH SIPIL: KECAMATAN -->
                    <template v-else-if="moduleKey === 'kecamatan'">
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-start">
                            <div class="md:col-span-4 flex flex-col items-center text-center p-5 bg-slate-50/80 rounded-2xl border border-slate-200/80">
                                <div class="w-24 h-24 rounded-2xl bg-amber-500/10 text-amber-600 border border-amber-200/80 flex items-center justify-center text-4xl mb-3 shadow-2xs">
                                    <i class="fa-solid fa-building-columns"></i>
                                </div>
                                <h4 class="font-black text-slate-900 text-sm leading-snug">
                                    {{ selectedItem.nama_kecamatan || selectedItem.nama || 'Kecamatan' }}
                                </h4>
                                <p class="text-xs text-amber-800 font-semibold mt-1">
                                    {{ selectedItem.kabupaten?.nama_kabupaten || 'Kabupaten Timor Tengah Selatan' }}
                                </p>
                                <span class="mt-3 inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    <span>KECAMATAN</span>
                                </span>
                            </div>

                            <div class="md:col-span-8 grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                                <div class="p-3 bg-slate-50/80 rounded-xl border border-slate-200/80">
                                    <span class="text-[10px] text-slate-400 font-bold uppercase block mb-1 flex items-center gap-1.5">
                                        <i class="fa-solid fa-barcode text-amber-500"></i> Kode / ID Kecamatan
                                    </span>
                                    <span class="px-2.5 py-0.5 rounded bg-amber-50 text-amber-800 font-mono font-bold text-xs border border-amber-200 inline-block">
                                        {{ selectedItem.id_kecamatan || selectedItem.kode_kecamatan || selectedItem.kode || '—' }}
                                    </span>
                                </div>

                                <div class="p-3 bg-slate-50/80 rounded-xl border border-slate-200/80">
                                    <span class="text-[10px] text-slate-400 font-bold uppercase block mb-1 flex items-center gap-1.5">
                                        <i class="fa-solid fa-city text-emerald-600"></i> Kabupaten Induk
                                    </span>
                                    <span class="font-bold text-slate-900">
                                        {{ selectedItem.kabupaten?.nama_kabupaten || 'Kabupaten Timor Tengah Selatan' }}
                                    </span>
                                </div>

                                <div class="sm:col-span-2 p-3 bg-slate-50/80 rounded-xl border border-slate-200/80">
                                    <span class="text-[10px] text-slate-400 font-bold uppercase block mb-1 flex items-center gap-1.5">
                                        <i class="fa-solid fa-tree-city text-blue-600"></i> Wilayah Pelayanan Paroki
                                    </span>
                                    <p class="text-slate-700 leading-relaxed font-medium">
                                        Kecamatan {{ selectedItem.nama_kecamatan || '' }} mencakup desa/kelurahan tempat domisili umat dan stasi/kapela paroki.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- 3H. DETAIL WILAYAH SIPIL: DESA / KELURAHAN -->
                    <template v-else-if="moduleKey === 'desa' || moduleKey === 'desa-kelurahan'">
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-start">
                            <div class="md:col-span-4 flex flex-col items-center text-center p-5 bg-slate-50/80 rounded-2xl border border-slate-200/80">
                                <div class="w-24 h-24 rounded-2xl bg-amber-500/10 text-amber-600 border border-amber-200/80 flex items-center justify-center text-4xl mb-3 shadow-2xs">
                                    <i class="fa-solid fa-tree-city"></i>
                                </div>
                                <h4 class="font-black text-slate-900 text-sm leading-snug">
                                    {{ selectedItem.nama_desa || selectedItem.nama_kelurahan || selectedItem.nama || 'Desa / Kelurahan' }}
                                </h4>
                                <p class="text-xs text-amber-800 font-semibold mt-1">
                                    Kec. {{ selectedItem.kecamatan?.nama_kecamatan || selectedItem.nama_kecamatan || '—' }}
                                </p>
                                <span class="mt-3 inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    <span>DESA / KELURAHAN</span>
                                </span>
                            </div>

                            <div class="md:col-span-8 grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                                <div class="p-3 bg-slate-50/80 rounded-xl border border-slate-200/80">
                                    <span class="text-[10px] text-slate-400 font-bold uppercase block mb-1 flex items-center gap-1.5">
                                        <i class="fa-solid fa-barcode text-amber-500"></i> Kode / ID Desa (BPS)
                                    </span>
                                    <span class="px-2.5 py-0.5 rounded bg-amber-50 text-amber-800 font-mono font-bold text-xs border border-amber-200 inline-block">
                                        {{ selectedItem.id_desa || selectedItem.kode_desa || selectedItem.kode || '—' }}
                                    </span>
                                </div>

                                <div class="p-3 bg-slate-50/80 rounded-xl border border-slate-200/80">
                                    <span class="text-[10px] text-slate-400 font-bold uppercase block mb-1 flex items-center gap-1.5">
                                        <i class="fa-solid fa-building-columns text-emerald-600"></i> Kecamatan
                                    </span>
                                    <span class="font-bold text-slate-900">
                                        {{ selectedItem.kecamatan?.nama_kecamatan || selectedItem.nama_kecamatan || '—' }}
                                    </span>
                                </div>

                                <div class="p-3 bg-slate-50/80 rounded-xl border border-slate-200/80">
                                    <span class="text-[10px] text-slate-400 font-bold uppercase block mb-1 flex items-center gap-1.5">
                                        <i class="fa-solid fa-city text-blue-600"></i> Kabupaten / Kota
                                    </span>
                                    <span class="font-bold text-slate-900">
                                        {{ selectedItem.kecamatan?.kabupaten?.nama_kabupaten || 'Kabupaten Timor Tengah Selatan' }}
                                    </span>
                                </div>

                                <div class="p-3 bg-slate-50/80 rounded-xl border border-slate-200/80">
                                    <span class="text-[10px] text-slate-400 font-bold uppercase block mb-1 flex items-center gap-1.5">
                                        <i class="fa-solid fa-mail-bulk text-cyan-600"></i> Kode Pos
                                    </span>
                                    <span class="font-medium text-slate-800 font-mono">
                                        {{ selectedItem.kode_pos || '85561' }}
                                    </span>
                                </div>

                                <div class="sm:col-span-2 p-3 bg-slate-50/80 rounded-xl border border-slate-200/80">
                                    <span class="text-[10px] text-slate-400 font-bold uppercase block mb-1 flex items-center gap-1.5">
                                        <i class="fa-solid fa-location-dot text-rose-500"></i> Domisili & Wilayah Sipil
                                    </span>
                                    <p class="text-slate-700 leading-relaxed font-medium">
                                        Desa {{ selectedItem.nama_desa || selectedItem.nama_kelurahan || '' }} menjadi basis domisili kependudukan umat dalam pengelompokan KUB dan KK Katolik.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- 4. GENERIC DETAIL FOR OTHER MODULES -->
                    <template v-else>
                        <!-- Photo header for pastor modules -->
                        <div v-if="['riwayat-pastor','riwayat_pastor_paroki','master-pastor','direktori-dpp','direktori-katekis','direktori-misdinar'].includes(moduleKey) && (selectedItem.foto || selectedItem.logo)"
                             class="flex flex-col items-center mb-4">
                            <div class="w-28 h-28 rounded-2xl overflow-hidden bg-slate-100 border-2 border-amber-200 shadow-md flex items-center justify-center">
                                <img
                                    :src="getImageUrl(selectedItem.foto || selectedItem.logo)"
                                    :alt="selectedItem.nama_pastor || selectedItem.nama || 'Foto'"
                                    class="w-full h-full object-cover"
                                    @error="(e) => { e.target.onerror = null; e.target.src=''; e.target.parentElement.innerHTML='<i class=\'fa-solid fa-user-tie text-4xl text-slate-300\'></i>'; }"
                                />
                            </div>
                            <p class="mt-2 text-sm font-bold text-slate-800">{{ selectedItem.nama_pastor || selectedItem.nama || '' }}</p>
                            <p class="text-xs text-slate-500">{{ selectedItem.jabatan || '' }}</p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                            <template v-for="col in columns" :key="col.key">
                                <!-- Skip foto/logo if already shown in photo header above -->
                                <div v-if="!((col.key === 'foto' || col.key === 'logo') && ['riwayat-pastor','riwayat_pastor_paroki','master-pastor','direktori-dpp','direktori-katekis','direktori-misdinar'].includes(moduleKey) && (selectedItem.foto || selectedItem.logo))"
                                     class="p-3 bg-slate-50/80 rounded-xl border border-slate-200/80">
                                    <span class="text-[10px] text-slate-400 font-bold uppercase block mb-1">{{ col.label }}</span>
                                    <!-- Image field -->
                                    <div v-if="col.key === 'foto' || col.key === 'logo' || col.isImage" class="flex items-center gap-2">
                                        <div v-if="getFieldValue(selectedItem, col) && getFieldValue(selectedItem, col) !== '—'"
                                             class="w-16 h-16 rounded-xl overflow-hidden bg-slate-100 border border-slate-200 flex items-center justify-center">
                                            <img
                                                :src="getImageUrl(getFieldValue(selectedItem, col))"
                                                :alt="col.label"
                                                class="w-full h-full object-cover"
                                                @error="(e) => { e.target.onerror = null; e.target.parentElement.innerHTML = '<i class=\'fa-solid fa-user-tie text-2xl text-slate-300\'></i>'; }"
                                            />
                                        </div>
                                        <span v-else class="text-slate-400 italic text-[11px]">Belum ada foto</span>
                                    </div>
                                    <!-- Regular field -->
                                    <span v-else class="font-medium text-slate-900">{{ getFieldValue(selectedItem, col) }}</span>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>

                <!-- Modal Footer -->
                <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-3">
                    <div class="flex items-center gap-2">
                        <a
                            v-if="moduleKey === 'keuskupan'"
                            href="/superadmin/dekenat"
                            class="px-3.5 py-1.5 rounded-xl bg-white border border-slate-200 text-slate-700 text-xs font-bold hover:bg-slate-100 transition flex items-center gap-1.5 shadow-2xs"
                        >
                            <i class="fa-solid fa-layer-group text-amber-600 text-xs"></i>
                            <span>Lihat Daftar Kevikepan</span>
                        </a>
                        <a
                            v-if="moduleKey === 'dekenat' || moduleKey === 'kevikepan'"
                            href="/superadmin/paroki"
                            class="px-3.5 py-1.5 rounded-xl bg-white border border-slate-200 text-slate-700 text-xs font-bold hover:bg-slate-100 transition flex items-center gap-1.5 shadow-2xs"
                        >
                            <i class="fa-solid fa-place-of-worship text-amber-600 text-xs"></i>
                            <span>Lihat Daftar Paroki</span>
                        </a>
                    </div>

                    <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
                        <button
                            type="button"
                            @click="openEditModal(selectedItem)"
                            class="px-4 py-2 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white text-xs font-bold transition flex items-center gap-1.5 shadow-sm shadow-amber-500/25 cursor-pointer"
                        >
                            <i class="fa-solid fa-pen-to-square text-xs"></i>
                            <span>{{ (moduleKey === 'umat' || moduleKey === 'data-umat') ? 'Edit Data Umat' : `Edit ${title}` }}</span>
                        </button>
                        <button
                            type="button"
                            @click="showDetailModal = false"
                            class="px-4 py-2 rounded-xl bg-slate-200 hover:bg-slate-300 text-slate-700 text-xs font-bold transition cursor-pointer"
                        >
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- CREATE / EDIT MODAL -->
        <div
            v-if="showFormModal"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/40 backdrop-blur-xs overflow-y-auto"
        >
            <div
                :class="[
                    'bg-white rounded-3xl p-6 sm:p-7 w-full shadow-2xl border border-slate-200 space-y-5 animate-in fade-in zoom-in-95 my-8 transition-all',
                    (moduleKey === 'keuskupan' || moduleKey === 'dekenat' || moduleKey === 'kevikepan' || moduleKey === 'paroki' || moduleKey === 'kuasi-paroki' || moduleKey === 'kapela' || moduleKey === 'stasi' || moduleKey === 'wilayah' || moduleKey === 'kub' || moduleKey === 'user' || moduleKey === 'role' || moduleKey === 'roles' || moduleKey === 'kk-katolik' || moduleKey === 'kk' || moduleKey === 'keluarga' || moduleKey === 'umat' || moduleKey === 'data-umat' || moduleKey === 'riwayat-mutasi-umat' || moduleKey === 'riwayat-mutasi' || moduleKey === 'mutasi-umat' || moduleKey === 'mutasi_umat') ? 'max-w-4xl' : (['rapat', 'rapat-notulen', 'kegiatan', 'surat-masuk', 'surat-keluar', 'arsip-digital'].includes(moduleKey) ? 'max-w-2xl' : 'max-w-lg')
                ]"
            >
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 border border-amber-200 flex items-center justify-center text-lg">
                            <i :class="modalMode === 'create' ? 'fa-solid fa-plus' : 'fa-solid fa-pen-to-square'"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-900">
                                {{ modalMode === 'create' ? `Form Tambah ${title} Baru` : `Form Edit ${title}: ${selectedItem?.nama_kuasi || selectedItem?.NamaKuasiParoki || selectedItem?.nama_keuskupan || selectedItem?.nama_kevikepan || selectedItem?.nama_dekenat || selectedItem?.nama_paroki || selectedItem?.nama || title}` }}
                            </h3>
                            <p class="text-[11px] text-slate-400">Silakan lengkapi formulir data master berikut ini</p>
                        </div>
                    </div>
                    <button @click="showFormModal = false" class="text-slate-400 hover:text-slate-700 p-1 text-sm cursor-pointer">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <!-- Banner error validasi server -->
                <div v-if="validationErrors.list.length" class="mx-1 mb-1 p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs space-y-1">
                    <div class="font-bold flex items-center gap-2">
                        <i class="fa-solid fa-circle-exclamation"></i>
                        <span>Terdapat {{ validationErrors.list.length }} kesalahan pada input:</span>
                    </div>
                    <ul class="list-disc list-inside space-y-0.5">
                        <li v-for="(msg, idx) in validationErrors.list" :key="idx">{{ msg }}</li>
                    </ul>
                </div>

                <form @submit.prevent="submitForm" class="space-y-5 max-h-[75vh] overflow-y-auto custom-scrollbar pr-1">
                    <!-- 1. KEUSKUPAN FORM -->
                    <template v-if="moduleKey === 'keuskupan'">
                        <!-- Section 1: Informasi Utama -->
                        <div class="space-y-3">
                            <h4 class="text-xs font-bold text-slate-900 flex items-center gap-2">
                                <i class="fa-solid fa-church text-amber-600"></i>
                                <span>Informasi Utama Keuskupan</span>
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Kode Keuskupan *</label>
                                    <input
                                        v-model="formData.kode_keuskupan"
                                        type="text"
                                        placeholder="Contoh: 012"
                                        required
                                        class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 font-mono transition"
                                    />
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Status</label>
                                    <select
                                        v-model="formData.status"
                                        class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition"
                                    >
                                        <option value="Aktif">Aktif</option>
                                        <option value="Nonaktif">Nonaktif</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Nama Keuskupan *</label>
                                    <input
                                        v-model="formData.nama_keuskupan"
                                        type="text"
                                        placeholder="Contoh: Keuskupan Agung Kupang"
                                        required
                                        :class="['w-full px-3.5 py-2 rounded-xl bg-slate-50 border text-xs text-slate-900 focus:outline-none focus:ring-1 transition', fieldError('nama_keuskupan') ? 'border-rose-400 focus:border-rose-500 focus:ring-rose-500' : 'border-slate-200 focus:border-amber-500 focus:ring-amber-500']"
                                    />
                                    <p v-if="fieldError('nama_keuskupan')" class="text-[10px] text-rose-600 mt-1">{{ fieldError('nama_keuskupan') }}</p>
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Nama Latin Keuskupan</label>
                                    <input
                                        v-model="formData.nama_latin"
                                        type="text"
                                        placeholder="Contoh: Archidioecesis Kupangensis"
                                        class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 italic transition"
                                    />
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Nama Uskup</label>
                                    <input
                                        v-model="formData.uskup"
                                        type="text"
                                        placeholder="Contoh: Mgr. Hironimus Pakaenoni"
                                        class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Section 2: Alamat & Wilayah Sipil -->
                        <div class="space-y-3 pt-3 border-t border-slate-100">
                            <h4 class="text-xs font-bold text-slate-900 flex items-center gap-2">
                                <i class="fa-solid fa-map-location-dot text-rose-500"></i>
                                <span>Alamat & Wilayah Sipil</span>
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                                <div class="md:col-span-2">
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Alamat Lengkap</label>
                                    <textarea
                                        v-model="formData.alamat"
                                        rows="2"
                                        placeholder="Alamat kantor keuskupan (terisi otomatis sesuai wilayah sipil)..."
                                        class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition"
                                    ></textarea>
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Provinsi</label>
                                    <select
                                        v-model="formData.provinsi_id"
                                        class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition font-medium"
                                    >
                                        <option value="">-- Pilih Provinsi --</option>
                                        <option v-for="prov in provinsiList" :key="prov.id || prov.id_provinsi" :value="prov.id || prov.id_provinsi">
                                            {{ prov.nama_provinsi }}
                                        </option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Kabupaten / Kota</label>
                                    <select
                                        v-model="formData.kabupaten_id"
                                        class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition font-medium"
                                    >
                                        <option value="">-- Pilih Kabupaten --</option>
                                        <option v-for="kab in availableKabupatens" :key="kab.id || kab.id_kabupaten" :value="kab.id || kab.id_kabupaten">
                                            {{ kab.nama_kabupaten }}
                                        </option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Kecamatan</label>
                                    <select
                                        v-model="formData.kecamatan_id"
                                        class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition font-medium"
                                    >
                                        <option value="">-- Pilih Kecamatan --</option>
                                        <option v-for="kec in availableKecamatans" :key="kec.id || kec.id_kecamatan" :value="kec.id || kec.id_kecamatan">
                                            {{ kec.nama_kecamatan }}
                                        </option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Kelurahan / Desa</label>
                                    <select
                                        v-model="formData.desa_id"
                                        class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition font-medium"
                                    >
                                        <option value="">-- Pilih Kelurahan/Desa --</option>
                                        <option v-for="desa in availableDesas" :key="desa.id || desa.id_desa" :value="desa.id || desa.id_desa">
                                            {{ desa.nama_desa }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Section 3: Kontak & Informasi Tambahan -->
                        <div class="space-y-3 pt-3 border-t border-slate-100">
                            <h4 class="text-xs font-bold text-slate-900 flex items-center gap-2">
                                <i class="fa-solid fa-address-book text-cyan-600"></i>
                                <span>Kontak & Informasi Tambahan</span>
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Telepon</label>
                                    <input
                                        v-model="formData.no_telp"
                                        type="text"
                                        placeholder="Nomor telepon kantor..."
                                        class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition"
                                    />
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Email</label>
                                    <input
                                        v-model="formData.email"
                                        type="email"
                                        placeholder="Email keuskupan..."
                                        class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition"
                                    />
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Website</label>
                                    <input
                                        v-model="formData.website"
                                        type="text"
                                        placeholder="https://keuskupanagungkupang.org/"
                                        class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition"
                                    />
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Keterangan</label>
                                    <input
                                        v-model="formData.keterangan"
                                        type="text"
                                        placeholder="Keterangan tambahan..."
                                        class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Section 4: Logo Keuskupan -->
                        <div class="space-y-3 pt-3 border-t border-slate-100">
                            <h4 class="text-xs font-bold text-slate-900 flex items-center gap-2">
                                <i class="fa-solid fa-image text-amber-600"></i>
                                <span>Logo Keuskupan</span>
                            </h4>
                            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 flex items-center gap-4">
                                <div class="w-20 h-20 rounded-2xl bg-white border border-slate-200 p-1.5 shadow-2xs flex items-center justify-center overflow-hidden shrink-0">
                                    <img
                                        :src="previewImage || (formData.logo && typeof formData.logo === 'string' && formData.logo.length > 0 ? getImageUrl(formData.logo) : (selectedItem?.logo_url || '/images/logo-keuskupan.png'))"
                                        :alt="formData.nama_keuskupan || 'Logo'"
                                        class="w-full h-full object-contain"
                                        @error="(e) => { e.target.onerror = null; e.target.src = '/images/logo-keuskupan.png'; }"
                                    />
                                </div>
                                <div class="space-y-1.5 flex-1">
                                    <input
                                        type="file"
                                        id="upload-logo-keuskupan"
                                        accept="image/png, image/jpeg, image/jpg, image/svg+xml, image/webp"
                                        @change="handleFileUpload($event, 'logo')"
                                        class="hidden"
                                    />
                                    <label
                                        for="upload-logo-keuskupan"
                                        class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 text-xs font-bold transition cursor-pointer shadow-2xs"
                                    >
                                        <i class="fa-solid fa-cloud-arrow-up text-amber-600 text-sm"></i>
                                        <span>{{ (previewImage || formData.logo) ? 'Ganti Logo Keuskupan' : 'Pilih Logo...' }}</span>
                                    </label>
                                    <p class="text-[10px] text-slate-400">
                                        Format didukung: PNG, JPG, JPEG, SVG, WebP. Maks 2MB.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- 2. KEVIKEPAN / DEKENAT FORM -->
                    <template v-else-if="moduleKey === 'dekenat' || moduleKey === 'kevikepan'">
                        <div class="space-y-3">
                            <h4 class="text-xs font-bold text-slate-900 flex items-center gap-2">
                                <i class="fa-solid fa-layer-group text-amber-600"></i>
                                <span>Informasi Kevikepan / Dekenat</span>
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Keuskupan Naungan *</label>
                                    <select
                                        v-model="formData.keuskupan_id"
                                        required
                                        :class="['w-full px-3.5 py-2 rounded-xl bg-slate-50 border text-xs text-slate-900 focus:outline-none focus:ring-1 focus:ring-amber-500 transition font-medium', fieldError('keuskupan_id') ? 'border-rose-400 focus:border-rose-500 focus:ring-rose-500' : 'border-slate-200 focus:border-amber-500']"
                                    >
                                        <option value="" disabled>Pilih Keuskupan...</option>
                                        <option v-for="k in keuskupanList" :key="k.id || k.id_keuskupan" :value="k.id || k.id_keuskupan">
                                            {{ k.nama_keuskupan }}
                                        </option>
                                    </select>
                                    <p v-if="fieldError('keuskupan_id')" class="text-[10px] text-rose-600 mt-1">{{ fieldError('keuskupan_id') }}</p>
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Status</label>
                                    <select
                                        v-model="formData.status"
                                        class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition"
                                    >
                                        <option value="Aktif">Aktif</option>
                                        <option value="Nonaktif">Nonaktif</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Kode Kevikepan / Dekenat *</label>
                                    <input
                                        v-model="formData.kode_kevikepan"
                                        type="text"
                                        placeholder="Contoh: KEV-01"
                                        required
                                        :class="['w-full px-3.5 py-2 rounded-xl bg-slate-50 border text-xs text-slate-900 focus:outline-none focus:ring-1 transition font-mono', fieldError('kode_kevikepan') ? 'border-rose-400 focus:border-rose-500 focus:ring-rose-500' : 'border-slate-200 focus:border-amber-500 focus:ring-amber-500']"
                                    />
                                    <p v-if="fieldError('kode_kevikepan')" class="text-[10px] text-rose-600 mt-1">{{ fieldError('kode_kevikepan') }}</p>
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Nama Kevikepan / Dekenat *</label>
                                    <input
                                        v-model="formData.nama_kevikepan"
                                        type="text"
                                        placeholder="Contoh: Kevikepan/Dekenat Kota Kupang"
                                        required
                                        :class="['w-full px-3.5 py-2 rounded-xl bg-slate-50 border text-xs text-slate-900 focus:outline-none focus:ring-1 transition', fieldError('nama_kevikepan') ? 'border-rose-400 focus:border-rose-500 focus:ring-rose-500' : 'border-slate-200 focus:border-amber-500 focus:ring-amber-500']"
                                    />
                                    <p v-if="fieldError('nama_kevikepan')" class="text-[10px] text-rose-600 mt-1">{{ fieldError('nama_kevikepan') }}</p>
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Nama Vikep (Deken)</label>
                                    <input
                                        v-model="formData.vikep"
                                        type="text"
                                        placeholder="Contoh: RD. Ambros Ladjar"
                                        class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition"
                                    />
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Alamat Kantor Kevikepan</label>
                                    <textarea
                                        v-model="formData.alamat"
                                        rows="2"
                                        placeholder="Alamat kantor kevikepan..."
                                        class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition"
                                    ></textarea>
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Telepon / Kontak</label>
                                    <input
                                        v-model="formData.telepon"
                                        type="text"
                                        placeholder="Nomor kontak kantor..."
                                        class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition"
                                    />
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Email</label>
                                    <input
                                        v-model="formData.email"
                                        type="email"
                                        placeholder="Email kevikepan..."
                                        class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition"
                                    />
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- 3. PAROKI FORM -->
                    <template v-else-if="moduleKey === 'paroki'">
                        <!-- Section 1: Informasi Induk & Identitas Paroki -->
                        <div class="space-y-3">
                            <h4 class="text-xs font-bold text-slate-900 flex items-center gap-2">
                                <i class="fa-solid fa-place-of-worship text-amber-600"></i>
                                <span>Informasi Induk & Identitas Paroki</span>
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Keuskupan Induk *</label>
                                    <SearchableSelect
                                        v-model="formData.keuskupan_id"
                                        :options="keuskupanList"
                                        value-key="id_keuskupan"
                                        label-key="nama_keuskupan"
                                        placeholder="-- Pilih Keuskupan --"
                                        search-placeholder="Ketik cari keuskupan..."
                                        icon="fa-church"
                                        icon-color="text-amber-600"
                                    />
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Dekenat / Kevikepan *</label>
                                    <SearchableSelect
                                        v-model="formData.dekenat_id"
                                        :options="availableDekenats"
                                        value-key="id"
                                        label-key="nama_kevikepan"
                                        placeholder="-- Pilih Dekenat --"
                                        search-placeholder="Ketik cari dekenat..."
                                        icon="fa-layer-group"
                                        icon-color="text-amber-600"
                                    />
                                </div>

                                <div>
                                    <div class="flex items-center justify-between mb-1">
                                        <label class="block text-[11px] font-bold text-slate-700">Kode Paroki</label>
                                        <span class="text-[9px] font-bold text-amber-700 bg-amber-50 border border-amber-200 px-1.5 py-0.5 rounded">Otomatis / Kustom</span>
                                    </div>
                                    <input
                                        v-model="formData.kode_paroki"
                                        type="text"
                                        placeholder="Contoh: 012.014"
                                        class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 font-mono font-bold focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition"
                                    />
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Nama Paroki *</label>
                                    <input
                                        v-model="formData.nama_paroki"
                                        type="text"
                                        placeholder="Contoh: Paroki St. Petrus dan Paulus Aileu"
                                        required
                                        class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition font-semibold"
                                    />
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Nama Pelindung / Santo</label>
                                    <input
                                        v-model="formData.pelindung_paroki"
                                        type="text"
                                        placeholder="Contoh: St. Vinsensius a Paulo"
                                        class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition"
                                    />
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Status</label>
                                    <select
                                        v-model="formData.status"
                                        class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition"
                                    >
                                        <option value="Aktif">Aktif</option>
                                        <option value="Nonaktif">Nonaktif</option>
                                    </select>
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Pastor Paroki</label>
                                    <SearchableSelect
                                        v-model="formData.nama_pastor_paroki_aktif"
                                        :options="pastorParokiOptions"
                                        value-key="value"
                                        label-key="label"
                                        placeholder="-- Pilih Pastor Paroki --"
                                        search-placeholder="Ketik cari nama pastor..."
                                        icon="fa-user-tie"
                                        icon-color="text-amber-600"
                                    />
                                    <p class="text-[10px] text-slate-400 mt-1">Pilih pastor yang bertugas sebagai Pastor Paroki</p>
                                </div>

                                <div class="md:col-span-2 space-y-2">
                                    <label class="block text-[11px] font-bold text-slate-700">Pastor Rekan (Bisa pilih lebih dari 1)</label>
                                    
                                    <!-- Selected Pastors Tags -->
                                    <div v-if="formData.selected_pastor_rekan && formData.selected_pastor_rekan.length" class="flex flex-wrap gap-1.5 p-2.5 bg-amber-50/60 rounded-xl border border-amber-200/80">
                                        <span
                                            v-for="rekan in formData.selected_pastor_rekan"
                                            :key="rekan"
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-white border border-amber-300 text-[11px] font-bold text-amber-900 shadow-2xs"
                                        >
                                            <i class="fa-solid fa-user-tie text-[10px] text-amber-600"></i>
                                            <span>{{ rekan }}</span>
                                            <button
                                                type="button"
                                                @click="removePastorRekan(rekan)"
                                                class="text-slate-400 hover:text-rose-600 transition ml-0.5 cursor-pointer"
                                                title="Hapus pastor"
                                            >
                                                <i class="fa-solid fa-xmark text-[9px]"></i>
                                            </button>
                                        </span>
                                    </div>

                                    <!-- Search box for Pastors -->
                                    <div class="relative">
                                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                                        <input
                                            v-model="pastorRekanSearch"
                                            type="text"
                                            placeholder="Cari nama pastor rekan..."
                                            class="w-full pl-8.5 pr-3 py-1.5 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 placeholder-slate-400 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500"
                                        />
                                    </div>

                                    <!-- Scrollable Checkbox List -->
                                    <div class="max-h-44 overflow-y-auto rounded-xl border border-slate-200 bg-slate-50/60 p-2 space-y-1 custom-scrollbar">
                                        <label
                                            v-for="p in filteredPastorsForRekan"
                                            :key="typeof p === 'string' ? p : p.nama_pastor"
                                            :class="[
                                                'flex items-center gap-2.5 px-2.5 py-1.5 rounded-lg text-xs transition cursor-pointer select-none',
                                                isPastorRekanSelected(typeof p === 'string' ? p : p.nama_pastor)
                                                    ? 'bg-amber-100/80 text-amber-900 font-bold border border-amber-200'
                                                    : 'hover:bg-white text-slate-700'
                                            ]"
                                        >
                                            <input
                                                type="checkbox"
                                                :checked="isPastorRekanSelected(typeof p === 'string' ? p : p.nama_pastor)"
                                                @change="togglePastorRekan(typeof p === 'string' ? p : p.nama_pastor)"
                                                class="rounded border-slate-300 text-amber-600 focus:ring-amber-500"
                                            />
                                            <span>{{ typeof p === 'string' ? p : p.nama_pastor }}</span>
                                        </label>
                                        <div v-if="!filteredPastorsForRekan.length" class="text-center py-3 text-slate-400 text-xs">
                                            Tidak ada pastor ditemukan dengan kata kunci tersebut.
                                        </div>
                                    </div>
                                    <p class="text-[10px] text-slate-400">Pilih satu atau beberapa pastor yang bertugas sebagai Pastor Rekan</p>
                                </div>

                                <div class="md:col-span-2 space-y-2 pt-1">
                                    <label class="block text-[11px] font-bold text-slate-700">Logo / Lambang Paroki</label>
                                    <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200/80 flex items-center gap-4">
                                        <div class="w-18 h-18 rounded-2xl bg-white border border-slate-200 p-1 shadow-2xs flex items-center justify-center overflow-hidden shrink-0">
                                            <img
                                                :src="previewImage || (formData.logo && typeof formData.logo === 'string' && formData.logo.length > 0 ? getImageUrl(formData.logo) : (selectedItem?.logo_url || '/images/logo-paroki.png'))"
                                                :alt="formData.nama_paroki || 'Logo'"
                                                class="w-full h-full object-contain"
                                                @error="(e) => { e.target.onerror = null; e.target.src = '/images/logo-paroki.png'; }"
                                            />
                                        </div>
                                        <div class="space-y-1.5 flex-1">
                                            <input
                                                type="file"
                                                id="upload-logo-paroki"
                                                accept="image/png, image/jpeg, image/jpg, image/gif, image/svg+xml, image/webp"
                                                @change="handleFileUpload($event, 'logo')"
                                                class="hidden"
                                            />
                                            <label
                                                for="upload-logo-paroki"
                                                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 text-xs font-bold transition cursor-pointer shadow-2xs"
                                            >
                                                <i class="fa-solid fa-cloud-arrow-up text-amber-600 text-sm"></i>
                                                <span>{{ (previewImage || formData.logo) ? 'Ganti Foto / Logo Paroki' : 'Pilih Foto / Logo Paroki' }}</span>
                                            </label>
                                            <p class="text-[10px] text-slate-400">
                                                Format: JPG, PNG, GIF, SVG, WEBP. Maksimal 2MB. Default: logo-paroki.png
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Alamat Lengkap</label>
                                    <textarea
                                        v-model="formData.alamat"
                                        rows="2"
                                        placeholder="Alamat kantor paroki"
                                        class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition"
                                    ></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Section 2: Wilayah Administratif -->
                        <div class="space-y-3 pt-3 border-t border-slate-100">
                            <h4 class="text-xs font-bold text-slate-900 flex items-center gap-2">
                                <i class="fa-solid fa-map-location-dot text-rose-500"></i>
                                <span>Wilayah Administratif</span>
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Provinsi</label>
                                    <select
                                        v-model="formData.provinsi_id"
                                        class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition font-medium"
                                    >
                                        <option value="">-- Pilih Provinsi --</option>
                                        <option v-for="prov in provinsiList" :key="prov.id || prov.id_provinsi" :value="prov.id || prov.id_provinsi">
                                            {{ prov.nama_provinsi }}
                                        </option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Kabupaten / Kota</label>
                                    <select
                                        v-model="formData.kabupaten_id"
                                        class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition font-medium"
                                    >
                                        <option value="">-- Pilih Kabupaten --</option>
                                        <option v-for="kab in availableKabupatens" :key="kab.id || kab.id_kabupaten" :value="kab.id || kab.id_kabupaten">
                                            {{ kab.nama_kabupaten }}
                                        </option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Kecamatan</label>
                                    <select
                                        v-model="formData.kecamatan_id"
                                        class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition font-medium"
                                    >
                                        <option value="">-- Pilih Kecamatan --</option>
                                        <option v-for="kec in availableKecamatans" :key="kec.id || kec.id_kecamatan" :value="kec.id || kec.id_kecamatan">
                                            {{ kec.nama_kecamatan }}
                                        </option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Kelurahan / Desa</label>
                                    <select
                                        v-model="formData.desa_id"
                                        class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition font-medium"
                                    >
                                        <option value="">-- Pilih Kelurahan/Desa --</option>
                                        <option v-for="desa in availableDesas" :key="desa.id || desa.id_desa" :value="desa.id || desa.id_desa">
                                            {{ desa.nama_desa }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Section 3: Kontak & Informasi Tambahan -->
                        <div class="space-y-3 pt-3 border-t border-slate-100">
                            <h4 class="text-xs font-bold text-slate-900 flex items-center gap-2">
                                <i class="fa-solid fa-address-book text-cyan-600"></i>
                                <span>Kontak & Informasi Tambahan</span>
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Telepon</label>
                                    <input
                                        v-model="formData.telepon"
                                        type="text"
                                        placeholder="Nomor telepon paroki"
                                        class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition"
                                    />
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Email</label>
                                    <input
                                        v-model="formData.email"
                                        type="email"
                                        placeholder="Email paroki"
                                        class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition"
                                    />
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Website</label>
                                    <input
                                        v-model="formData.website"
                                        type="text"
                                        placeholder="Contoh: https://parokibenlutu.id"
                                        class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition"
                                    />
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Keterangan</label>
                                    <textarea
                                        v-model="formData.keterangan"
                                        rows="2"
                                        placeholder="Keterangan tambahan..."
                                        class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition"
                                    ></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Section 4: Google Maps & Peta Lokasi -->
                        <div class="space-y-3 pt-3 border-t border-slate-100">
                            <h4 class="text-xs font-bold text-slate-900 flex items-center gap-2">
                                <i class="fa-solid fa-map-location-dot text-emerald-600"></i>
                                <span>Google Maps & Peta Lokasi</span>
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                                <div class="md:col-span-2">
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Link Google Maps (URL)</label>
                                    <input
                                        v-model="formData.maps_url"
                                        type="text"
                                        placeholder="https://maps.google.com/?q=..."
                                        class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition font-mono text-[11px]"
                                    />
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Latitude</label>
                                    <input
                                        v-model="formData.latitude"
                                        type="text"
                                        placeholder="Contoh: -9.812345"
                                        class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition font-mono"
                                    />
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Longitude</label>
                                    <input
                                        v-model="formData.longitude"
                                        type="text"
                                        placeholder="Contoh: 124.123456"
                                        class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition font-mono"
                                    />
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Embed Google Maps (Iframe)</label>
                                    <textarea
                                        v-model="formData.maps_embed"
                                        rows="2"
                                        placeholder="Paste kode embed <iframe> Google Maps di sini..."
                                        class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition font-mono text-[11px]"
                                    ></textarea>
                                    <p class="text-[10px] text-slate-400 mt-1">
                                        Pastekan tag &lt;iframe src="..."&gt;&lt;/iframe&gt; dari Google Maps (Bagikan / Share -&gt; Sematkan peta / Embed a map).
                                    </p>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- 4. KAPELA / STASI FORM (100% Matches http://localhost/katedral/admin/kapela/create) -->
                    <template v-else-if="moduleKey === 'kapela' || moduleKey === 'stasi'">
                        <!-- Section 1: Informasi Induk & Identitas Stasi / Kapela -->
                        <div class="space-y-3.5">
                            <h4 class="text-xs font-bold text-slate-900 flex items-center gap-2 pb-2 border-b border-slate-100">
                                <i class="fa-solid fa-place-of-worship text-amber-600"></i>
                                <span>Informasi Induk & Identitas Stasi / Kapela</span>
                            </h4>
                            
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3.5">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Paroki Induk *</label>
                                    <SearchableSelect
                                        v-model="formData.paroki_id"
                                        :options="parokiList"
                                        value-key="id_paroki"
                                        label-key="nama_paroki"
                                        placeholder="Mengikuti Profil Paroki"
                                        search-placeholder="Ketik cari paroki..."
                                        icon="fa-church"
                                        icon-color="text-amber-600"
                                        :clearable="false"
                                        disabled
                                    />
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Tipe</label>
                                    <select
                                        v-model="formData.tipe"
                                        class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition font-semibold"
                                    >
                                        <option value="Stasi">Stasi</option>
                                        <option value="Kapela">Kapela</option>
                                        <option value="Gereja Pusat">Gereja Pusat</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Status</label>
                                    <select
                                        v-model="formData.status"
                                        class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition font-semibold"
                                    >
                                        <option value="Aktif">Aktif</option>
                                        <option value="Nonaktif">Nonaktif</option>
                                        <option value="Pembangunan">Pembangunan</option>
                                    </select>
                                </div>

                                <div>
                                    <div class="flex items-center justify-between mb-1">
                                        <label class="block text-[11px] font-bold text-slate-700">Kode Stasi/Kapela</label>
                                        <span class="text-[9px] font-bold text-amber-700 bg-amber-50 border border-amber-200 px-1.5 py-0.5 rounded">Otomatis</span>
                                    </div>
                                    <input
                                        v-model="formData.kode_kapela"
                                        type="text"
                                        placeholder="(Otomatis dari Sistem)"
                                        readonly
                                        class="w-full px-3.5 py-2 rounded-xl bg-slate-100/90 border border-slate-200 text-xs text-slate-700 font-mono font-bold cursor-not-allowed transition"
                                    />
                                    <p class="text-[10px] text-slate-400 mt-1">Dibuat otomatis berdasarkan Paroki</p>
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Nama Stasi/Kapela *</label>
                                    <input
                                        v-model="formData.nama_kapela"
                                        type="text"
                                        placeholder="Contoh: Stasi Santo Yosef"
                                        required
                                        class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition font-semibold"
                                    />
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Pelindung (Nama Kudus)</label>
                                    <input
                                        v-model="formData.pelindung_kapela"
                                        type="text"
                                        placeholder="Contoh: Maria Bintang Laut"
                                        class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition"
                                    />
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Penanggung Jawab</label>
                                    <input
                                        v-model="formData.penanggung_jawab"
                                        type="text"
                                        placeholder="Nama koordinator stasi/kapela"
                                        class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition"
                                    />
                                </div>

                                <div class="md:col-span-3">
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Alamat Lengkap</label>
                                    <textarea
                                        v-model="formData.lokasi"
                                        rows="2"
                                        placeholder="Alamat lengkap stasi/kapela..."
                                        class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition"
                                    ></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Section 2: Wilayah Administratif -->
                        <div class="space-y-3 pt-3 border-t border-slate-100">
                            <h4 class="text-xs font-bold text-slate-900 flex items-center gap-2">
                                <i class="fa-solid fa-map-location-dot text-rose-500"></i>
                                <span>Wilayah Administratif</span>
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Provinsi</label>
                                    <SearchableSelect
                                        v-model="formData.provinsi_id"
                                        :options="provinsiList"
                                        value-key="id_provinsi"
                                        label-key="nama_provinsi"
                                        placeholder="-- Pilih Provinsi --"
                                        search-placeholder="Ketik cari provinsi..."
                                        icon="fa-map"
                                        icon-color="text-rose-500"
                                    />
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Kabupaten / Kota</label>
                                    <SearchableSelect
                                        v-model="formData.kabupaten_id"
                                        :options="availableKabupatens"
                                        value-key="id_kabupaten"
                                        label-key="nama_kabupaten"
                                        placeholder="-- Pilih Kabupaten --"
                                        search-placeholder="Ketik cari kabupaten..."
                                        icon="fa-city"
                                        icon-color="text-rose-500"
                                    />
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Kecamatan</label>
                                    <SearchableSelect
                                        v-model="formData.kecamatan_id"
                                        :options="availableKecamatans"
                                        value-key="id_kecamatan"
                                        label-key="nama_kecamatan"
                                        placeholder="-- Pilih Kecamatan --"
                                        search-placeholder="Ketik cari kecamatan..."
                                        icon="fa-building-columns"
                                        icon-color="text-rose-500"
                                    />
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Kelurahan / Desa</label>
                                    <SearchableSelect
                                        v-model="formData.desa_id"
                                        :options="availableDesas"
                                        value-key="id_desa"
                                        label-key="nama_desa"
                                        placeholder="-- Pilih Kelurahan/Desa --"
                                        search-placeholder="Ketik cari kelurahan/desa..."
                                        icon="fa-tree-city"
                                        icon-color="text-rose-500"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Section 3: Peta & Geolocation -->
                        <div class="space-y-3.5 pt-3 border-t border-slate-100">
                            <div class="flex items-center justify-between">
                                <h4 class="text-xs font-bold text-slate-900 flex items-center gap-2">
                                    <i class="fa-solid fa-map text-blue-600"></i>
                                    <span>Peta & Geolocation</span>
                                </h4>
                                <button
                                    type="button"
                                    @click="getCurrentLocation"
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-purple-50 text-purple-700 border border-purple-200 text-[11px] font-bold hover:bg-purple-100 transition cursor-pointer"
                                >
                                    <i class="fa-solid fa-location-crosshairs text-[10px]"></i>
                                    <span>Gunakan Lokasi Saya</span>
                                </button>
                            </div>

                            <!-- Interactive Map Preview Box -->
                            <div class="w-full h-44 rounded-2xl bg-slate-100 border border-slate-200 overflow-hidden relative shadow-inner">
                                <iframe
                                    :src="`https://maps.google.com/maps?q=${formData.latitude || '-9.850000'},${formData.longitude || '124.300000'}&z=14&output=embed`"
                                    class="w-full h-full border-0 pointer-events-none"
                                    loading="lazy"
                                ></iframe>
                                <div class="absolute bottom-2 left-2 bg-white/90 backdrop-blur-md px-2.5 py-1 rounded-lg text-[10px] font-bold text-slate-700 border border-slate-200 shadow-xs flex items-center gap-1.5">
                                    <i class="fa-solid fa-location-dot text-rose-600"></i>
                                    <span>Lat: {{ formData.latitude || '-9.850000' }}, Long: {{ formData.longitude || '124.300000' }}</span>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-4 gap-3.5">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Latitude</label>
                                    <input
                                        v-model="formData.latitude"
                                        type="text"
                                        placeholder="Contoh: -9.850000"
                                        class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition font-mono"
                                    />
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Longitude</label>
                                    <input
                                        v-model="formData.longitude"
                                        type="text"
                                        placeholder="Contoh: 124.300000"
                                        class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition font-mono"
                                    />
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Warna Area Peta</label>
                                    <div class="flex items-center gap-2">
                                        <input
                                            v-model="formData.warna_area"
                                            type="color"
                                            class="w-10 h-8.5 rounded-xl border border-slate-200 bg-white p-1 cursor-pointer"
                                        />
                                        <input
                                            v-model="formData.warna_area"
                                            type="text"
                                            placeholder="#007bff"
                                            class="flex-1 px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs font-mono"
                                        />
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Google Maps URL</label>
                                    <input
                                        v-model="formData.maps_url"
                                        type="text"
                                        placeholder="https://maps.google.com/?q=..."
                                        class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition font-mono text-[11px]"
                                    />
                                </div>

                                <div class="md:col-span-4">
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Data Poligon Batas Area (GeoJSON) <span class="text-slate-400 font-normal">(Opsional / Otomatis)</span></label>
                                    <textarea
                                        v-model="formData.geojson"
                                        rows="2"
                                        placeholder='Format GeoJSON polygon, contoh: {"type":"Polygon","coordinates":[[[124.28,-9.84],[124.32,-9.84],[124.32,-9.87],[124.28,-9.87],[124.28,-9.84]]]}'
                                        class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition font-mono text-[11px]"
                                    ></textarea>
                                    <p class="text-[10px] text-slate-400 mt-1">Jika dikosongkan, sistem di peta frontend akan otomatis menandai dengan Pin Ikon Gereja.</p>
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Keterangan</label>
                                    <textarea
                                        v-model="formData.keterangan"
                                        rows="3"
                                        placeholder="Keterangan tambahan tentang stasi/kapela..."
                                        class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition"
                                    ></textarea>
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Foto Unggulan / Cover</label>
                                    <div class="flex items-center gap-3">
                                        <div class="w-16 h-16 rounded-xl bg-slate-100 border border-slate-200 flex items-center justify-center overflow-hidden shrink-0 shadow-2xs">
                                            <img
                                                v-if="previewImage || (formData.logo && typeof formData.logo === 'string')"
                                                :src="previewImage || getImageUrl(formData.logo)"
                                                class="w-full h-full object-cover"
                                            />
                                            <i v-else class="fa-solid fa-image text-slate-400 text-lg"></i>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <input
                                                type="file"
                                                accept="image/*"
                                                @change="handleFileUpload($event, 'logo')"
                                                class="w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-amber-50 file:text-amber-700 hover:file:bg-amber-100 file:cursor-pointer"
                                            />
                                            <p class="text-[10px] text-slate-400 mt-1">Format: JPG, PNG, WEBP. Maksimal 2MB</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Section 4: Profil, Sejarah, & Visi Misi -->
                        <div class="space-y-3.5 pt-3 border-t border-slate-100">
                            <h4 class="text-xs font-bold text-slate-900 flex items-center gap-2">
                                <i class="fa-solid fa-book-open text-purple-600"></i>
                                <span>Profil, Sejarah, & Visi Misi</span>
                            </h4>

                            <div class="space-y-3">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Sejarah Stasi/Kapela</label>
                                    <textarea
                                        v-model="formData.sejarah"
                                        rows="3"
                                        placeholder="Tuliskan sejarah berdirinya stasi/kapela..."
                                        class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition"
                                    ></textarea>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Visi Stasi/Kapela</label>
                                        <textarea
                                            v-model="formData.visi"
                                            rows="2"
                                            placeholder="Visi stasi/kapela..."
                                            class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition"
                                        ></textarea>
                                    </div>

                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Misi Stasi/Kapela</label>
                                        <textarea
                                            v-model="formData.misi"
                                            rows="2"
                                            placeholder="Misi stasi/kapela..."
                                            class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition"
                                        ></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- 4B. WILAYAH PELAYANAN FORM (Lengkap dengan Paroki, Stasi Naungan, & Wilayah Administratif) -->
                    <template v-else-if="moduleKey === 'wilayah'">
                        <!-- Section 1: Informasi Induk & Identitas Wilayah Pelayanan -->
                        <div class="space-y-3.5">
                            <h4 class="text-xs font-bold text-slate-900 flex items-center gap-2 pb-2 border-b border-slate-100">
                                <i class="fa-solid fa-map-location-dot text-amber-600"></i>
                                <span>Informasi Induk & Identitas Wilayah Pelayanan</span>
                            </h4>
                            
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3.5">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Paroki Induk *</label>
                                    <SearchableSelect
                                        v-model="formData.paroki_id"
                                        :options="parokiList"
                                        value-key="id_paroki"
                                        label-key="nama_paroki"
                                        placeholder="Mengikuti Profil Paroki"
                                        search-placeholder="Ketik cari paroki..."
                                        icon="fa-church"
                                        icon-color="text-amber-600"
                                        :clearable="false"
                                        disabled
                                    />
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Stasi / Kapela Naungan</label>
                                    <SearchableSelect
                                        v-model="formData.kapela_id"
                                        :options="kapelaOptionsForWilayah"
                                        value-key="id"
                                        label-key="label"
                                        placeholder="Pusat Paroki (Gereja Paroki Induk)"
                                        search-placeholder="Ketik cari stasi/kapela..."
                                        icon="fa-place-of-worship"
                                        icon-color="text-teal-600"
                                        :clearable="true"
                                    />
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Status</label>
                                    <select
                                        v-model="formData.status"
                                        class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition font-semibold"
                                    >
                                        <option value="Aktif">Aktif</option>
                                        <option value="Nonaktif">Nonaktif</option>
                                    </select>
                                </div>

                                <div>
                                    <div class="flex items-center justify-between mb-1">
                                        <label class="block text-[11px] font-bold text-slate-700">Kode Wilayah</label>
                                        <span class="text-[9px] font-bold text-amber-700 bg-amber-50 border border-amber-200 px-1.5 py-0.5 rounded">Otomatis</span>
                                    </div>
                                    <input
                                        v-model="formData.kode_wilayah"
                                        type="text"
                                        placeholder="(Otomatis dari Sistem)"
                                        readonly
                                        class="w-full px-3.5 py-2 rounded-xl bg-slate-100/90 border border-slate-200 text-xs text-slate-700 font-mono font-bold cursor-not-allowed transition"
                                    />
                                    <p class="text-[10px] text-slate-400 mt-1">Dibuat otomatis berdasarkan Paroki</p>
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Nama Wilayah Pelayanan *</label>
                                    <input
                                        v-model="formData.nama_wilayah"
                                        type="text"
                                        placeholder="Contoh: Wilayah I - St. Petrus"
                                        required
                                        class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition font-semibold"
                                    />
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Ketua / Koordinator Wilayah</label>
                                    <input
                                        v-model="formData.ketua_wilayah"
                                        type="text"
                                        placeholder="Nama ketua wilayah..."
                                        class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition"
                                    />
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Kontak / WhatsApp</label>
                                    <input
                                        v-model="formData.no_hp"
                                        type="text"
                                        placeholder="Contoh: 081234567890"
                                        class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition font-mono"
                                    />
                                </div>

                                <div class="md:col-span-3">
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Batas & Cakupan Wilayah</label>
                                    <textarea
                                        v-model="formData.alamat"
                                        rows="2"
                                        placeholder="Cakupan batas teritorial / dusun wilayah pelayanan..."
                                        class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition"
                                    ></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Section 2: Wilayah Administratif -->
                        <div class="space-y-3 pt-3 border-t border-slate-100">
                            <h4 class="text-xs font-bold text-slate-900 flex items-center gap-2">
                                <i class="fa-solid fa-map-location-dot text-rose-500"></i>
                                <span>Wilayah Administratif</span>
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Provinsi</label>
                                    <SearchableSelect
                                        v-model="formData.provinsi_id"
                                        :options="provinsiList"
                                        value-key="id_provinsi"
                                        label-key="nama_provinsi"
                                        placeholder="-- Pilih Provinsi --"
                                        search-placeholder="Ketik cari provinsi..."
                                        icon="fa-map"
                                        icon-color="text-rose-500"
                                    />
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Kabupaten / Kota</label>
                                    <SearchableSelect
                                        v-model="formData.kabupaten_id"
                                        :options="availableKabupatens"
                                        value-key="id_kabupaten"
                                        label-key="nama_kabupaten"
                                        placeholder="-- Pilih Kabupaten --"
                                        search-placeholder="Ketik cari kabupaten..."
                                        icon="fa-city"
                                        icon-color="text-rose-500"
                                    />
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Kecamatan</label>
                                    <SearchableSelect
                                        v-model="formData.kecamatan_id"
                                        :options="availableKecamatans"
                                        value-key="id_kecamatan"
                                        label-key="nama_kecamatan"
                                        placeholder="-- Pilih Kecamatan --"
                                        search-placeholder="Ketik cari kecamatan..."
                                        icon="fa-building-columns"
                                        icon-color="text-rose-500"
                                    />
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Kelurahan / Desa</label>
                                    <SearchableSelect
                                        v-model="formData.desa_id"
                                        :options="availableDesas"
                                        value-key="id_desa"
                                        label-key="nama_desa"
                                        placeholder="-- Pilih Kelurahan/Desa --"
                                        search-placeholder="Ketik cari kelurahan/desa..."
                                        icon="fa-tree-city"
                                        icon-color="text-rose-500"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Section 3: Keterangan & Catatan -->
                        <div class="space-y-3 pt-3 border-t border-slate-100">
                            <h4 class="text-xs font-bold text-slate-900 flex items-center gap-2">
                                <i class="fa-solid fa-circle-info text-amber-600"></i>
                                <span>Keterangan & Catatan</span>
                            </h4>
                            <div>
                                <textarea
                                    v-model="formData.keterangan"
                                    rows="3"
                                    placeholder="Keterangan tambahan tentang wilayah pelayanan..."
                                    class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition"
                                ></textarea>
                            </div>
                        </div>
                    </template>

                    <!-- KUB FORM (100% Matches Kapela / Stasi Layout & Aesthetic) -->
                    <template v-else-if="moduleKey === 'kub'">
                        <!-- Section 1: Informasi Induk & Identitas KUB -->
                        <div class="space-y-3.5">
                            <h4 class="text-xs font-bold text-slate-900 flex items-center gap-2 pb-2 border-b border-slate-100">
                                <i class="fa-solid fa-people-roof text-teal-600"></i>
                                <span>Informasi Induk & Identitas KUB</span>
                            </h4>
                            
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3.5">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Paroki Induk *</label>
                                    <SearchableSelect
                                        v-model="formData.paroki_id"
                                        :options="parokiList"
                                        value-key="id_paroki"
                                        label-key="nama_paroki"
                                        placeholder="Mengikuti Profil Paroki"
                                        search-placeholder="Ketik cari paroki..."
                                        icon="fa-church"
                                        icon-color="text-amber-600"
                                        :clearable="false"
                                        disabled
                                    />
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Stasi / Kapela Naungan</label>
                                    <SearchableSelect
                                        v-model="formData.kapela_id"
                                        :options="kapelaList"
                                        value-key="id"
                                        label-key="nama_kapela"
                                        placeholder="-- Pusat Paroki / Pilih Stasi --"
                                        search-placeholder="Ketik cari stasi/kapela..."
                                        icon="fa-place-of-worship"
                                        icon-color="text-teal-600"
                                    />
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Wilayah Pastoral Naungan</label>
                                    <SearchableSelect
                                        v-model="formData.wilayah_id"
                                        :options="wilayahList"
                                        value-key="id"
                                        label-key="nama_wilayah"
                                        placeholder="-- Pilih Wilayah Pastoral --"
                                        search-placeholder="Ketik cari wilayah..."
                                        icon="fa-compass"
                                        icon-color="text-blue-600"
                                    />
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Status KUB</label>
                                    <select
                                        v-model="formData.status"
                                        class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 transition font-semibold"
                                    >
                                        <option value="Aktif">Aktif</option>
                                        <option value="Nonaktif">Nonaktif</option>
                                    </select>
                                </div>

                                <div>
                                    <div class="flex items-center justify-between mb-1">
                                        <label class="block text-[11px] font-bold text-slate-700">Kode KUB</label>
                                        <span class="text-[9px] font-bold text-teal-700 bg-teal-50 border border-teal-200 px-1.5 py-0.5 rounded">Otomatis</span>
                                    </div>
                                    <input
                                        v-model="formData.kode_kub"
                                        type="text"
                                        placeholder="(Otomatis dari Sistem)"
                                        readonly
                                        class="w-full px-3.5 py-2 rounded-xl bg-slate-100/90 border border-slate-200 text-xs text-slate-700 font-mono font-bold cursor-not-allowed transition"
                                    />
                                    <p class="text-[10px] text-slate-400 mt-1">Dibuat otomatis berdasarkan format KUB</p>
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Nama KUB *</label>
                                    <input
                                        v-model="formData.nama_kub"
                                        type="text"
                                        placeholder="Contoh: KUB Santo Petrus"
                                        required
                                        class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 transition font-semibold"
                                    />
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Pelindung / Nama Kudus</label>
                                    <input
                                        v-model="formData.pelindung"
                                        type="text"
                                        placeholder="Contoh: Santo Vinsensius a Paulo"
                                        class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 transition"
                                    />
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Admin KUB / Pengurus</label>
                                    <input
                                        v-model="formData.ketua_kub"
                                        type="text"
                                        placeholder="Nama Admin / Pengurus KUB..."
                                        class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 transition"
                                    />
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Kontak / WhatsApp</label>
                                    <input
                                        v-model="formData.no_hp"
                                        type="text"
                                        placeholder="Contoh: 081234567890"
                                        class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 transition font-mono"
                                    />
                                </div>

                                <div class="md:col-span-3">
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Lokasi / Jadwal Ibadat / Pertemuan</label>
                                    <textarea
                                        v-model="formData.lokasi"
                                        rows="2"
                                        placeholder="Alamat / lokasi pertemuan bergilir dan jadwal ibadat sabda KUB..."
                                        class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 transition"
                                    ></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Section 2: Wilayah Administratif -->
                        <div class="space-y-3 pt-3 border-t border-slate-100">
                            <h4 class="text-xs font-bold text-slate-900 flex items-center gap-2">
                                <i class="fa-solid fa-map-location-dot text-rose-500"></i>
                                <span>Wilayah Administratif (Kemendagri / BPS)</span>
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Provinsi</label>
                                    <SearchableSelect
                                        v-model="formData.provinsi_id"
                                        :options="provinsiList"
                                        value-key="id_provinsi"
                                        label-key="nama_provinsi"
                                        placeholder="-- Pilih Provinsi --"
                                        search-placeholder="Ketik cari provinsi..."
                                        icon="fa-map"
                                        icon-color="text-rose-500"
                                    />
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Kabupaten / Kota</label>
                                    <SearchableSelect
                                        v-model="formData.kabupaten_id"
                                        :options="availableKabupatens"
                                        value-key="id_kabupaten"
                                        label-key="nama_kabupaten"
                                        placeholder="-- Pilih Kabupaten --"
                                        search-placeholder="Ketik cari kabupaten..."
                                        icon="fa-city"
                                        icon-color="text-rose-500"
                                    />
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Kecamatan</label>
                                    <SearchableSelect
                                        v-model="formData.kecamatan_id"
                                        :options="availableKecamatans"
                                        value-key="id_kecamatan"
                                        label-key="nama_kecamatan"
                                        placeholder="-- Pilih Kecamatan --"
                                        search-placeholder="Ketik cari kecamatan..."
                                        icon="fa-building-columns"
                                        icon-color="text-rose-500"
                                    />
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Kelurahan / Desa</label>
                                    <SearchableSelect
                                        v-model="formData.desa_id"
                                        :options="availableDesas"
                                        value-key="id_desa"
                                        label-key="nama_desa"
                                        placeholder="-- Pilih Kelurahan/Desa --"
                                        search-placeholder="Ketik cari kelurahan/desa..."
                                        icon="fa-tree-city"
                                        icon-color="text-rose-500"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Section 3: Keterangan & Catatan -->
                        <div class="space-y-3 pt-3 border-t border-slate-100">
                            <h4 class="text-xs font-bold text-slate-900 flex items-center gap-2">
                                <i class="fa-solid fa-circle-info text-amber-600"></i>
                                <span>Keterangan & Catatan Pastoral</span>
                            </h4>
                            <div>
                                <textarea
                                    v-model="formData.keterangan"
                                    rows="3"
                                    placeholder="Catatan tambahan pastoral mengenai Komunitas Umat Basis ini..."
                                    class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 transition"
                                ></textarea>
                            </div>
                        </div>
                    </template>

                    <!-- 5. DIREKTORI DPP FORM (100% Matches http://localhost/katedral/admin/direktori-dpp) -->
                    <template v-else-if="moduleKey === 'direktori-dpp'">
                        <div class="space-y-4">
                            <h4 class="text-xs font-bold text-slate-900 flex items-center gap-2 pb-2 border-b border-slate-100">
                                <i class="fa-solid fa-users-line text-amber-600"></i>
                                <span>Informasi Pengurus & Anggota DPP</span>
                            </h4>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                                <div class="md:col-span-2">
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Nama Lengkap & Gelar *</label>
                                    <input
                                        v-model="formData.nama_lengkap"
                                        type="text"
                                        placeholder="Contoh: Drs. Petrus Fernandez, M.Si"
                                        required
                                        class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition font-semibold"
                                    />
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Jabatan DPP *</label>
                                    <select
                                        v-model="formData.jabatan"
                                        required
                                        class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition font-semibold"
                                    >
                                        <option value="Ketua Umum (Pastor Paroki)">Ketua Umum (Pastor Paroki)</option>
                                        <option value="Wakil Ketua DPP">Wakil Ketua DPP</option>
                                        <option value="Sekretaris I">Sekretaris I</option>
                                        <option value="Sekretaris II">Sekretaris II</option>
                                        <option value="Bendahara I">Bendahara I</option>
                                        <option value="Bendahara II">Bendahara II</option>
                                        <option value="Ketua Bidang">Ketua Bidang</option>
                                        <option value="Ketua Seksi">Ketua Seksi</option>
                                        <option value="Anggota Pleno">Anggota Pleno</option>
                                        <option value="Penasihat DPP">Penasihat DPP</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Bidang / Seksi Pastoral *</label>
                                    <select
                                        v-model="formData.seksi"
                                        required
                                        class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition font-semibold"
                                    >
                                        <option value="Pengurus Inti DPP">Pengurus Inti DPP</option>
                                        <option value="Bidang Liturgi & Peribadatan">Bidang Liturgi & Peribadatan</option>
                                        <option value="Bidang Pewartaan & Katekese">Bidang Pewartaan & Katekese</option>
                                        <option value="Bidang Pelayanan Kemasyarakatan (PSE)">Bidang Pelayanan Kemasyarakatan (PSE)</option>
                                        <option value="Bidang Paguyuban & Persaudaraan">Bidang Paguyuban & Persaudaraan</option>
                                        <option value="Dewan Keuangan Paroki (DKP)">Dewan Keuangan Paroki (DKP)</option>
                                        <option value="Seksi Kepemudaan (OMK)">Seksi Kepemudaan (OMK)</option>
                                        <option value="Seksi Kerasulan Keluarga">Seksi Kerasulan Keluarga</option>
                                        <option value="Seksi Komunikasi Sosial (KOMSOS)">Seksi Komunikasi Sosial (KOMSOS)</option>
                                        <option value="Seksi Sarana & Prasarana">Seksi Sarana & Prasarana</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Periode Kepengurusan</label>
                                    <input
                                        v-model="formData.periode"
                                        type="text"
                                        placeholder="Contoh: 2024 - 2027"
                                        class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition"
                                    />
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">No. Kontak / WhatsApp</label>
                                    <input
                                        v-model="formData.no_hp"
                                        type="text"
                                        placeholder="Contoh: 081234567890"
                                        class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition font-mono"
                                    />
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Status Kepengurusan</label>
                                    <select
                                        v-model="formData.status"
                                        class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition font-semibold"
                                    >
                                        <option value="Aktif">Aktif</option>
                                        <option value="Demisioner">Demisioner</option>
                                        <option value="Nonaktif">Nonaktif</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Urutan Tampil (Prioritas)</label>
                                    <input
                                        v-model.number="formData.urutan"
                                        type="number"
                                        min="1"
                                        placeholder="1, 2, 3..."
                                        class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition font-bold"
                                    />
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Foto Profil Pengurus</label>
                                    <div class="flex items-center gap-3">
                                        <div class="w-16 h-16 rounded-full bg-slate-100 border border-slate-200 flex items-center justify-center overflow-hidden shrink-0 shadow-2xs">
                                            <img
                                                v-if="previewImage || (formData.foto && typeof formData.foto === 'string')"
                                                :src="previewImage || getImageUrl(formData.foto)"
                                                class="w-full h-full object-cover"
                                            />
                                            <i v-else class="fa-solid fa-user text-slate-400 text-lg"></i>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <input
                                                type="file"
                                                accept="image/*"
                                                @change="handleFileUpload($event, 'foto')"
                                                class="w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-amber-50 file:text-amber-700 hover:file:bg-amber-100 file:cursor-pointer"
                                            />
                                            <p class="text-[10px] text-slate-400 mt-1">Format: JPG, PNG, WEBP. Maksimal 2MB</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Keterangan / Tugas Pokok</label>
                                    <textarea
                                        v-model="formData.keterangan"
                                        rows="2"
                                        placeholder="Keterangan tugas atau catatan tambahan..."
                                        class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition"
                                    ></textarea>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- 5B. DIREKTORI KATEKIS FORM -->
                    <template v-else-if="moduleKey === 'direktori-katekis'">
                        <div class="space-y-4">
                            <h4 class="text-xs font-bold text-slate-900 flex items-center gap-2 pb-2 border-b border-slate-100">
                                <i class="fa-solid fa-book-bible text-emerald-600"></i>
                                <span>Informasi Katekis & Tenaga Pengajar</span>
                            </h4>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                                <div class="md:col-span-2">
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Nama Lengkap & Gelar Katekis *</label>
                                    <input
                                        v-model="formData.nama_lengkap"
                                        type="text"
                                        placeholder="Contoh: Petrus Paulus, S.Ag"
                                        required
                                        class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition font-semibold"
                                    />
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Jenis / Kategori Katekis *</label>
                                    <select
                                        v-model="formData.jenis_katekis"
                                        required
                                        class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition font-semibold"
                                    >
                                        <option value="Katekis Paroki">Katekis Paroki</option>
                                        <option value="Katekis Paroki Resmi">Katekis Paroki Resmi</option>
                                        <option value="Katekis Wilayah">Katekis Wilayah</option>
                                        <option value="Katekis Stasi">Katekis Stasi / Kapela</option>
                                        <option value="Katekis KUB">Katekis KUB</option>
                                        <option value="Pendamping Komuni Pertama">Pendamping Komuni Pertama</option>
                                        <option value="Pendamping Krisma">Pendamping Krisma</option>
                                        <option value="Pendamping Baptis">Pendamping Baptis</option>
                                        <option value="Guru Agama / Pembina Katekese">Guru Agama / Pembina Katekese</option>
                                        <option value="Lainnya">Lainnya</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Wilayah / Tempat Pelayanan *</label>
                                    <SearchableSelect
                                        v-model="formData.wilayah_pelayanan"
                                        :options="tempatPelayananOptions"
                                        valueKey="value"
                                        labelKey="label"
                                        placeholder="Pilih Tempat / Wilayah Pelayanan..."
                                        searchPlaceholder="Cari kapela, wilayah, KUB..."
                                        icon="fa-location-dot"
                                        iconColor="text-emerald-600"
                                    />
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Sertifikasi / Pelatihan</label>
                                    <input
                                        v-model="formData.sertifikasi"
                                        type="text"
                                        placeholder="Contoh: Kursus Katekese Keuskupan 2023"
                                        class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition"
                                    />
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Nomor SK Penugasan</label>
                                    <input
                                        v-model="formData.nomor_sk"
                                        type="text"
                                        placeholder="Contoh: SK-PAR/KAT/2024/01"
                                        class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition font-mono"
                                    />
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">No. Kontak / WhatsApp</label>
                                    <input
                                        v-model="formData.no_hp"
                                        type="text"
                                        placeholder="Contoh: 081234567890"
                                        class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition font-mono"
                                    />
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Status Keaktifan</label>
                                    <select
                                        v-model="formData.status_aktif"
                                        class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition font-semibold"
                                    >
                                        <option value="Aktif">Aktif</option>
                                        <option value="Nonaktif">Nonaktif</option>
                                    </select>
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Foto Katekis</label>
                                    <div class="flex items-center gap-3">
                                        <div class="w-16 h-16 rounded-full bg-slate-100 border border-slate-200 flex items-center justify-center overflow-hidden shrink-0 shadow-2xs">
                                            <img
                                                v-if="previewImage || (formData.foto && typeof formData.foto === 'string')"
                                                :src="previewImage || getImageUrl(formData.foto)"
                                                class="w-full h-full object-cover"
                                            />
                                            <i v-else class="fa-solid fa-user text-slate-400 text-lg"></i>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <input
                                                type="file"
                                                accept="image/*"
                                                @change="handleFileUpload($event, 'foto')"
                                                class="w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 file:cursor-pointer"
                                            />
                                            <p class="text-[10px] text-slate-400 mt-1">Format: JPG, PNG, WEBP. Maksimal 2MB</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Keterangan / Catatan</label>
                                    <textarea
                                        v-model="formData.keterangan"
                                        rows="2"
                                        placeholder="Catatan tambahan mengenai tugas pelayanan katekese..."
                                        class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition"
                                    ></textarea>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- 5C. DIREKTORI MISDINAR FORM -->
                    <template v-else-if="moduleKey === 'direktori-misdinar'">
                        <div class="space-y-4">
                            <h4 class="text-xs font-bold text-slate-900 flex items-center gap-2 pb-2 border-b border-slate-100">
                                <i class="fa-solid fa-hands-praying text-sky-600"></i>
                                <span>Informasi Anggota Putra / Putri Altar (Misdinar)</span>
                            </h4>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                                <div class="md:col-span-2">
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Nama Lengkap Misdinar *</label>
                                    <input
                                        v-model="formData.nama_lengkap"
                                        type="text"
                                        placeholder="Contoh: Fransiskus Xaverius"
                                        required
                                        class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-sky-500 focus:ring-1 focus:ring-sky-500 transition font-semibold"
                                    />
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Tingkat / Jenjang *</label>
                                    <select
                                        v-model="formData.tingkat"
                                        required
                                        class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-sky-500 focus:ring-1 focus:ring-sky-500 transition font-semibold"
                                    >
                                        <option value="Junior">Junior (Pemula)</option>
                                        <option value="Senior">Senior</option>
                                        <option value="Koordinator">Koordinator / Ketua</option>
                                        <option value="Pembina">Pembina / Pendamping</option>
                                        <option value="Pengurus">Pengurus Misdinar</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Tempat / Kapela Pelayanan</label>
                                    <SearchableSelect
                                        v-model="formData.stasi_kapela_id"
                                        :options="kapelaOptionsForMisdinar"
                                        valueKey="id"
                                        labelKey="label"
                                        placeholder="Pusat Paroki / Pilih Kapela..."
                                        searchPlaceholder="Cari kapela/stasi..."
                                        icon="fa-place-of-worship"
                                        iconColor="text-sky-600"
                                    />
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Nama Orang Tua / Wali</label>
                                    <input
                                        v-model="formData.nama_orang_tua"
                                        type="text"
                                        placeholder="Contoh: Bapak Antonius"
                                        class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-sky-500 focus:ring-1 focus:ring-sky-500 transition"
                                    />
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">No. Kontak Misdinar / WA</label>
                                    <input
                                        v-model="formData.no_hp"
                                        type="text"
                                        placeholder="Contoh: 081234567890"
                                        class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-sky-500 focus:ring-1 focus:ring-sky-500 transition font-mono"
                                    />
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">No. Kontak Orang Tua</label>
                                    <input
                                        v-model="formData.no_hp_orang_tua"
                                        type="text"
                                        placeholder="Contoh: 081298765432"
                                        class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-sky-500 focus:ring-1 focus:ring-sky-500 transition font-mono"
                                    />
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Tanggal Bergabung</label>
                                    <DateInput
                                        v-model="formData.tanggal_bergabung"
                                        placeholder="dd/mm/yyyy"
                                        iconColor="text-sky-600"
                                        inputClass="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-sky-500 focus:ring-1 focus:ring-sky-500 transition font-medium"
                                    />
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Status Keaktifan</label>
                                    <select
                                        v-model="formData.status_aktif"
                                        class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-sky-500 focus:ring-1 focus:ring-sky-500 transition font-semibold"
                                    >
                                        <option value="Aktif">Aktif Bertugas</option>
                                        <option value="Purna Tugas">Purna Tugas / Alumni</option>
                                        <option value="Nonaktif">Nonaktif</option>
                                    </select>
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Foto Misdinar</label>
                                    <div class="flex items-center gap-3">
                                        <div class="w-16 h-16 rounded-full bg-slate-100 border border-slate-200 flex items-center justify-center overflow-hidden shrink-0 shadow-2xs">
                                            <img
                                                v-if="previewImage || (formData.foto && typeof formData.foto === 'string')"
                                                :src="previewImage || getImageUrl(formData.foto)"
                                                class="w-full h-full object-cover"
                                            />
                                            <i v-else class="fa-solid fa-user text-slate-400 text-lg"></i>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <input
                                                type="file"
                                                accept="image/*"
                                                @change="handleFileUpload($event, 'foto')"
                                                class="w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-sky-50 file:text-sky-700 hover:file:bg-sky-100 file:cursor-pointer"
                                            />
                                            <p class="text-[10px] text-slate-400 mt-1">Format: JPG, PNG, WEBP. Maksimal 2MB</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Catatan Tambahan</label>
                                    <textarea
                                        v-model="formData.catatan"
                                        rows="2"
                                        placeholder="Catatan jadwal tugas atau keterangan lainnya..."
                                        class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-sky-500 focus:ring-1 focus:ring-sky-500 transition"
                                    ></textarea>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- 6. KUASI PAROKI FORM -->
                    <template v-else-if="moduleKey === 'kuasi-paroki'">
                        <div class="space-y-4">
                            <h4 class="text-xs font-bold text-slate-900 flex items-center gap-2 pb-2 border-b border-slate-100">
                                <i class="fa-solid fa-place-of-worship text-amber-600"></i>
                                <span>Informasi & Identitas Kuasi Paroki</span>
                            </h4>

                            <!-- Notice jika status telah berubah menjadi Paroki -->
                            <div v-if="formData.status === 'Ditingkatkan Menjadi Paroki (Definitif)' || formData.status === 'Definitif'" class="p-3.5 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-start gap-3">
                                <i class="fa-solid fa-circle-check text-emerald-600 text-base mt-0.5 shrink-0"></i>
                                <div class="space-y-1">
                                    <p class="font-bold">Status: Ditingkatkan Menjadi Paroki Definitif</p>
                                    <p class="text-[11px] text-emerald-700 leading-relaxed">
                                        Kuasi Paroki ini telah berstatus sebagai Paroki Mandiri. Anda dapat langsung mengelola profil lengkap, wilayah, dan stasinya di menu
                                        <a :href="`/superadmin/paroki?search=${encodeURIComponent(formData.nama_kuasi || '')}`" class="underline font-bold text-emerald-900 hover:text-emerald-950">
                                            Data Paroki
                                        </a>.
                                    </p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Nama Kuasi Paroki *</label>
                                    <input
                                        v-model="formData.nama_kuasi"
                                        type="text"
                                        placeholder="Contoh: Kuasi Paroki St. Petrus"
                                        required
                                        class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition font-semibold"
                                    />
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Kode Kuasi Paroki</label>
                                    <input
                                        v-model="formData.kode_kuasi"
                                        type="text"
                                        placeholder="Contoh: KP-001"
                                        class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition font-mono font-bold"
                                    />
                                </div>

                                <div class="col-span-1 md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4 bg-slate-50/80 p-4 rounded-2xl border border-slate-200/80">
                                    <div class="space-y-1.5">
                                        <label class="block text-[11.5px] font-bold text-slate-800 flex items-center justify-between">
                                            <span class="flex items-center gap-1.5">
                                                <i class="fa-solid fa-church text-amber-600"></i>
                                                <span>Paroki Induk (Asal Pemekaran) *</span>
                                            </span>
                                        </label>
                                        <SearchableSelect
                                            v-model="formData.paroki_id"
                                            :options="parokiList"
                                            value-key="id_paroki"
                                            label-key="nama_paroki"
                                            placeholder="-- Pilih Paroki Induk --"
                                            search-placeholder="Ketik cari paroki..."
                                            icon="fa-church"
                                            icon-color="text-amber-600"
                                            :wrap-text="true"
                                        />
                                        <div v-if="selectedKuasiParokiName" class="mt-1 flex items-start gap-1.5 text-[11px] text-amber-800 bg-amber-50/90 px-2.5 py-1.5 rounded-xl border border-amber-200 leading-snug">
                                            <i class="fa-solid fa-circle-info text-amber-600 mt-0.5 shrink-0 text-[10px]"></i>
                                            <span class="font-bold">Paroki Induk: <span class="font-semibold">{{ selectedKuasiParokiName }}</span></span>
                                        </div>
                                    </div>

                                    <div class="space-y-1.5">
                                        <label class="block text-[11.5px] font-bold text-slate-800 flex items-center justify-between">
                                            <span class="flex items-center gap-1.5">
                                                <i class="fa-solid fa-layer-group text-cyan-600"></i>
                                                <span>Dekenat / Kevikepan</span>
                                            </span>
                                            <span v-if="formData.paroki_id" class="text-[10px] text-cyan-700 bg-cyan-100/70 px-2 py-0.5 rounded-full font-bold">Otomatis Terhubung</span>
                                        </label>
                                        <SearchableSelect
                                            v-model="formData.dekenat_id"
                                            :options="dekenatList"
                                            value-key="id_dekenat"
                                            label-key="nama_dekenat"
                                            placeholder="-- Pilih Kevikepan --"
                                            search-placeholder="Ketik cari kevikepan..."
                                            icon="fa-layer-group"
                                            icon-color="text-cyan-600"
                                            :wrap-text="true"
                                        />
                                        <div v-if="selectedKuasiDekenatName" class="mt-1 flex items-start gap-1.5 text-[11px] text-cyan-800 bg-cyan-50/90 px-2.5 py-1.5 rounded-xl border border-cyan-200 leading-snug">
                                            <i class="fa-solid fa-circle-check text-cyan-600 mt-0.5 shrink-0 text-[10px]"></i>
                                            <span class="font-bold">Kevikepan: <span class="font-semibold">{{ selectedKuasiDekenatName }}</span></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-span-1 md:col-span-2">
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Pastor Administrator Kuasi</label>
                                    <SearchableSelect
                                        v-model="formData.pastor_administrator"
                                        :options="pastorParokiOptions"
                                        value-key="id"
                                        label-key="nama_pastor"
                                        placeholder="-- Pilih Pastor Administrator --"
                                        search-placeholder="Ketik cari nama pastor..."
                                        icon="fa-user-tie"
                                        icon-color="text-amber-600"
                                        :wrap-text="true"
                                    />
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Status Kuasi Paroki *</label>
                                    <select
                                        v-model="formData.status"
                                        required
                                        class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition font-semibold"
                                    >
                                        <option value="Aktif">Aktif (Kuasi Paroki)</option>
                                        <option value="Ditingkatkan Menjadi Paroki (Definitif)">Ditingkatkan Menjadi Paroki (Definitif)</option>
                                        <option value="Nonaktif">Nonaktif</option>
                                    </select>
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Pelindung Kuasi Paroki</label>
                                    <input
                                        v-model="formData.pelindung"
                                        type="text"
                                        placeholder="Contoh: Santo Fransiskus Xaverius"
                                        class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition"
                                    />
                                </div>

                                <!-- SEKSI ELEVASI OTOMATIS KE DATA PAROKI -->
                                <div v-if="formData.status === 'Ditingkatkan Menjadi Paroki (Definitif)' || formData.status === 'Definitif'" class="md:col-span-2 p-4 rounded-2xl bg-amber-50/80 border border-amber-200/80 space-y-3">
                                    <div class="flex items-center gap-2 text-amber-900 font-bold text-xs">
                                        <i class="fa-solid fa-wand-magic-sparkles text-amber-600"></i>
                                        <span>Pengaturan Elevasi & Pendaftaran Paroki Baru</span>
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <div>
                                            <label class="block text-[10.5px] font-bold text-slate-700 mb-1">Kode Paroki Baru (Resmi Keuskupan)</label>
                                            <input
                                                v-model="formData.kode_paroki_baru"
                                                type="text"
                                                placeholder="Contoh: 012.016"
                                                class="w-full px-3 py-2 rounded-xl bg-white border border-slate-200 text-xs font-mono font-bold text-slate-900"
                                            />
                                        </div>
                                        <div>
                                            <label class="block text-[10.5px] font-bold text-slate-700 mb-1">Nomor SK / Dekret Uskup Diosesan</label>
                                            <input
                                                v-model="formData.no_sk_elevasi"
                                                type="text"
                                                placeholder="Contoh: 045/SK/KA-KPG/2026"
                                                class="w-full px-3 py-2 rounded-xl bg-white border border-slate-200 text-xs text-slate-900"
                                            />
                                        </div>
                                    </div>
                                    <p class="text-[10px] text-amber-800">
                                        💡 Sistem akan otomatis mendaftarkan entitas <b>Paroki {{ (formData.nama_kuasi || '').replace(/^Kuasi\s+Paroki\s+/i, '') }}</b> ke tabel <b>Data Paroki</b> dan menyimpan riwayat historis ini secara permanen.
                                    </p>
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Lokasi / Alamat Lengkap</label>
                                    <textarea
                                        v-model="formData.lokasi"
                                        rows="2"
                                        placeholder="Alamat lengkap lokasi gereja kuasi paroki..."
                                        class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition"
                                    ></textarea>
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Riwayat Lengkap & Keterangan Historis Kuasi Paroki</label>
                                    <textarea
                                        v-model="formData.keterangan"
                                        rows="3"
                                        placeholder="Tuliskan catatan riwayat: Tanggal pendirian kuasi, pastor administrator yang pernah bertugas, stasi asal, nomor dekret, hingga proses elevasi..."
                                        class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition"
                                    ></textarea>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- 7. LAPAK & TOKO UMKM UMAT FORM (100% Matches Reference) -->
                    <template v-else-if="moduleKey === 'lapak-produk'">
                        <div class="space-y-4">
                            <h4 class="text-xs font-bold text-slate-900 flex items-center gap-2 pb-2 border-b border-slate-100">
                                <i class="fa-solid fa-shop text-amber-600"></i>
                                <span>Informasi Produk & Usaha UMKM Umat</span>
                            </h4>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                                <div class="md:col-span-2">
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Nama Produk *</label>
                                    <input
                                        v-model="formData.nama_produk"
                                        type="text"
                                        placeholder="Contoh: Buku Doa Harian"
                                        required
                                        class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition font-semibold"
                                    />
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Harga (Rupiah) *</label>
                                    <div class="relative">
                                        <span class="absolute left-3.5 top-2 text-xs font-bold text-slate-400">Rp</span>
                                        <input
                                            v-model="formData.harga"
                                            type="number"
                                            min="0"
                                            placeholder="0"
                                            required
                                            class="w-full pl-10 pr-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition font-mono font-bold"
                                        />
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Status Tampil</label>
                                    <select
                                        v-model="formData.status"
                                        class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition font-semibold"
                                    >
                                        <option value="Aktif (Tampil di Lapak)">Aktif (Tampil di Lapak)</option>
                                        <option value="Nonaktif (Sembunyikan)">Nonaktif (Sembunyikan)</option>
                                    </select>
                                </div>

                                <div>
                                    <div class="flex items-center justify-between mb-1">
                                        <label class="block text-[11px] font-bold text-slate-700">Kategori Produk</label>
                                        <button
                                            type="button"
                                            @click="isCustomKategori = !isCustomKategori"
                                            class="text-[10.5px] font-bold text-amber-600 hover:text-amber-700 underline cursor-pointer"
                                        >
                                            {{ isCustomKategori ? 'Pilih dari Daftar' : '+ Ketik Kategori Baru' }}
                                        </button>
                                    </div>
                                    <input
                                        v-if="isCustomKategori"
                                        v-model="formData.kategori"
                                        type="text"
                                        placeholder="Ketik nama kategori baru..."
                                        class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition font-semibold"
                                    />
                                    <select
                                        v-else
                                        v-model="formData.kategori"
                                        class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition font-semibold"
                                    >
                                        <option value="Benda Rohani & Perlengkapan Doa">Benda Rohani & Perlengkapan Doa</option>
                                        <option value="Makanan & Minuman Olahan">Makanan & Minuman Olahan</option>
                                        <option value="Kerajinan & Seni Tangan">Kerajinan & Seni Tangan</option>
                                        <option value="Kain Tenun & Busana Adat">Kain Tenun & Busana Adat</option>
                                        <option value="Hasil Tani & Kebun Organik">Hasil Tani & Kebun Organik</option>
                                        <option value="Jasa & Keterampilan">Jasa & Keterampilan</option>
                                        <option value="Pakaian & Busana Gerejawi">Pakaian & Busana Gerejawi</option>
                                        <option value="Peternakan & Hasil Hewan">Peternakan & Hasil Hewan</option>
                                        <option value="Lain-lain">Lain-lain</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Jumlah Stok</label>
                                    <input
                                        v-model.number="formData.stok"
                                        type="number"
                                        min="0"
                                        placeholder="1"
                                        class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition font-bold"
                                    />
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Nama Penjual / Pemilik Usaha</label>
                                    <input
                                        v-model="formData.penjual"
                                        type="text"
                                        placeholder="Contoh: Ibu Maria Fernandez"
                                        class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition font-semibold"
                                    />
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">No. WhatsApp Pemesanan</label>
                                    <div class="relative">
                                        <span class="absolute left-3.5 top-2 text-xs font-bold text-emerald-600"><i class="fa-brands fa-whatsapp"></i></span>
                                        <input
                                            v-model="formData.no_wa"
                                            type="text"
                                            placeholder="081234567890"
                                            class="w-full pl-9 pr-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition font-mono font-semibold"
                                        />
                                    </div>
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Foto Produk</label>
                                    <div class="flex items-center gap-3">
                                        <div class="w-16 h-16 rounded-xl bg-slate-100 border border-slate-200 flex items-center justify-center overflow-hidden shrink-0 shadow-2xs">
                                            <img
                                                v-if="previewImage || (formData.foto && typeof formData.foto === 'string')"
                                                :src="previewImage || getImageUrl(formData.foto)"
                                                class="w-full h-full object-cover"
                                            />
                                            <i v-else class="fa-solid fa-bag-shopping text-slate-400 text-lg"></i>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <input
                                                type="file"
                                                accept="image/*"
                                                @change="handleFileUpload($event, 'foto')"
                                                class="w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-amber-50 file:text-amber-700 hover:file:bg-amber-100 file:cursor-pointer"
                                            />
                                            <p class="text-[10px] text-slate-400 mt-1">Format: JPG, JPEG, PNG, WEBP. Maksimal 2MB</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Deskripsi Produk</label>
                                    <textarea
                                        v-model="formData.deskripsi"
                                        rows="3"
                                        placeholder="Tuliskan deskripsi singkat produk..."
                                        class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition"
                                    ></textarea>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- 8. RIWAYAT MUTASI UMAT FORM (100% Menggunakan Dropdown Database Gerejawi) -->
                    <template v-else-if="moduleKey === 'riwayat-mutasi-umat' || moduleKey === 'mutasi-umat' || moduleKey === 'riwayat-mutasi'">
                        <div class="space-y-4">
                            <div class="p-3.5 bg-amber-50/80 rounded-2xl border border-amber-200/80 flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-amber-500 text-white flex items-center justify-center text-base shrink-0 shadow-xs">
                                    <i class="fa-solid fa-arrows-split-up-and-left"></i>
                                </div>
                                <div>
                                    <h4 class="text-xs font-black text-amber-950">
                                        {{ isKubLevel ? 'Pencatatan Mutasi Umat Meninggal Dunia (KUB)' : 'Form Mutasi Umat & Wilayah Gerejawi' }}
                                    </h4>
                                    <p class="text-[11px] text-amber-800">
                                        {{ isKubLevel ? 'Catat data umat yang telah berpulang (meninggal dunia) untuk dilaporkan ke database Paroki dan dicatat ke buku Defunctorum.' : 'Pilih data Umat, KUB, dan Wilayah yang terhubung langsung ke database paroki.' }}
                                    </p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                                <!-- 1. Pilih Umat -->
                                <div class="md:col-span-2">
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Pilih Umat (Dari Database) *</label>
                                    <SearchableSelect
                                        v-model="formData.umat_id"
                                        :options="umatList"
                                        valueKey="id"
                                        labelKey="label"
                                        placeholder="-- Cari Nama atau NIK Umat --"
                                        searchPlaceholder="Ketik nama umat atau NIK..."
                                        icon="fa-user"
                                        iconColor="text-blue-600"
                                        @update:modelValue="onMutasiUmatSelected"
                                    />
                                </div>

                                <!-- 2. Jenis Mutasi -->
                                <div class="md:col-span-2">
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Jenis Mutasi *</label>
                                    <select
                                        v-if="!isKubLevel"
                                        v-model="formData.jenis_mutasi"
                                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-bold text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition cursor-pointer"
                                    >
                                        <option value="Meninggal Dunia">Meninggal Dunia (Kematian)</option>
                                        <option value="Mutasi Antar KUB (Dalam Paroki)">Mutasi Antar KUB (Dalam Paroki)</option>
                                        <option value="Pindah Wilayah Pastoral">Pindah Wilayah Pastoral</option>
                                        <option value="Pecah KK / Bentuk Keluarga Baru">Pecah KK / Bentuk Keluarga Baru</option>
                                        <option value="Pindah Paroki (Keluar)">Pindah Paroki (Keluar)</option>
                                        <option value="Pindah Masuk dari Paroki Lain">Pindah Masuk dari Paroki Lain</option>
                                        <option value="Lainnya">Lainnya</option>
                                    </select>
                                    <div v-else class="px-3.5 py-2.5 rounded-xl bg-rose-50 border border-rose-200 text-xs font-bold text-rose-800 flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <i class="fa-solid fa-book-skull text-rose-600 text-sm"></i>
                                            <span>Meninggal Dunia (Laporan Kematian Umat KUB)</span>
                                        </div>
                                        <span class="text-[10px] bg-rose-200/70 text-rose-900 px-2 py-0.5 rounded-full uppercase tracking-wider font-extrabold">Khusus KUB</span>
                                    </div>
                                </div>

                                <!-- Territory Fields (Hidden for Meninggal Dunia or KUB Level) -->
                                <template v-if="!isKubLevel && formData.jenis_mutasi !== 'Meninggal Dunia'">
                                    <div class="md:col-span-2 grid grid-cols-1 lg:grid-cols-2 gap-4">
                                        <!-- Panel Asal Umat -->
                                        <div class="p-4 rounded-2xl bg-rose-50/70 border border-rose-200/90 space-y-3 shadow-2xs flex flex-col justify-between">
                                            <div class="flex items-center gap-2 text-rose-800 font-bold text-xs pb-1 border-b border-rose-200/60">
                                                <i class="fa-solid fa-arrow-right-from-bracket text-rose-500"></i>
                                                <span>Asal Umat (Otomatis dari Database)</span>
                                            </div>
                                            
                                            <div class="space-y-3">
                                                <div>
                                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Wilayah Pastoral Asal</label>
                                                    <SearchableSelect
                                                        v-model="formData.wilayah_asal_id"
                                                        :options="wilayahList"
                                                        valueKey="id"
                                                        labelKey="nama_wilayah"
                                                        placeholder="-- Wilayah Asal --"
                                                        searchPlaceholder="Cari wilayah asal..."
                                                        icon="fa-map-location-dot"
                                                        iconColor="text-rose-500"
                                                    />
                                                </div>
                                                <div>
                                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Stasi / Kapela Asal</label>
                                                    <SearchableSelect
                                                        v-model="formData.kapela_asal_id"
                                                        :options="kapelaList"
                                                        valueKey="id"
                                                        labelKey="nama_kapela"
                                                        placeholder="-- Stasi / Kapela Asal --"
                                                        searchPlaceholder="Cari stasi / kapela asal..."
                                                        icon="fa-church"
                                                        iconColor="text-rose-500"
                                                    />
                                                </div>
                                                <div>
                                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">KUB Asal</label>
                                                    <SearchableSelect
                                                        v-model="formData.kub_asal_id"
                                                        :options="kubList"
                                                        valueKey="id"
                                                        labelKey="nama_kub"
                                                        placeholder="-- KUB Asal Terisi Otomatis --"
                                                        searchPlaceholder="Cari KUB asal..."
                                                        icon="fa-people-roof"
                                                        iconColor="text-rose-500"
                                                    />
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Panel Tujuan Mutasi (Wilayah vs Stasi/Kapela) -->
                                        <div class="p-4 rounded-2xl bg-emerald-50/70 border border-emerald-200/90 space-y-3 shadow-2xs flex flex-col justify-between">
                                            <div class="flex items-center justify-between gap-2 pb-1 border-b border-emerald-200/60">
                                                <div class="flex items-center gap-2 text-emerald-800 font-bold text-xs">
                                                    <i class="fa-solid fa-arrow-right-to-bracket text-emerald-600"></i>
                                                    <span>Tujuan Mutasi (Pindah Ke)</span>
                                                </div>

                                                <!-- Switcher Tipe Tujuan -->
                                                <div class="flex items-center bg-white rounded-lg p-0.5 border border-emerald-300 text-[10px] font-bold shadow-2xs">
                                                    <button
                                                        type="button"
                                                        @click="mutasiTujuanType = 'wilayah'; formData.kapela_tujuan_id = ''"
                                                        class="px-2.5 py-1 rounded-md transition cursor-pointer"
                                                        :class="mutasiTujuanType === 'wilayah' ? 'bg-emerald-600 text-white shadow-xs' : 'text-emerald-800 hover:bg-emerald-50'"
                                                    >
                                                        Ke Wilayah
                                                    </button>
                                                    <button
                                                        type="button"
                                                        @click="mutasiTujuanType = 'kapela'; formData.wilayah_tujuan_id = ''"
                                                        class="px-2.5 py-1 rounded-md transition cursor-pointer"
                                                        :class="mutasiTujuanType === 'kapela' ? 'bg-emerald-600 text-white shadow-xs' : 'text-emerald-800 hover:bg-emerald-50'"
                                                    >
                                                        Ke Stasi/Kapela
                                                    </button>
                                                </div>
                                            </div>

                                            <div class="space-y-3">
                                                <!-- Option 1: Ke Wilayah Pastoral -->
                                                <div v-if="mutasiTujuanType === 'wilayah'">
                                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">1. Wilayah Pastoral Tujuan *</label>
                                                    <SearchableSelect
                                                        :modelValue="formData.wilayah_tujuan_id"
                                                        @update:modelValue="onWilayahTujuanChanged"
                                                        :options="wilayahList"
                                                        valueKey="id"
                                                        labelKey="nama_wilayah"
                                                        placeholder="-- Pilih Wilayah Tujuan --"
                                                        searchPlaceholder="Cari wilayah tujuan..."
                                                        icon="fa-map-location-dot"
                                                        iconColor="text-emerald-600"
                                                    />
                                                </div>

                                                <!-- Option 2: Ke Stasi / Kapela -->
                                                <div v-else>
                                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">1. Stasi / Kapela Tujuan *</label>
                                                    <SearchableSelect
                                                        :modelValue="formData.kapela_tujuan_id"
                                                        @update:modelValue="onKapelaTujuanChanged"
                                                        :options="kapelaList"
                                                        valueKey="id"
                                                        labelKey="nama_kapela"
                                                        placeholder="-- Pilih Stasi / Kapela Tujuan --"
                                                        searchPlaceholder="Cari stasi / kapela tujuan..."
                                                        icon="fa-church"
                                                        iconColor="text-emerald-600"
                                                    />
                                                </div>

                                                <!-- KUB Tujuan -->
                                                <div>
                                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">2. KUB Tujuan *</label>
                                                    <SearchableSelect
                                                        :modelValue="formData.kub_tujuan_id"
                                                        @update:modelValue="onKubTujuanChanged"
                                                        :options="filteredKubTujuanList"
                                                        valueKey="id"
                                                        labelKey="nama_kub"
                                                        :placeholder="(formData.wilayah_tujuan_id || formData.kapela_tujuan_id) ? '-- Pilih KUB Tujuan --' : '-- Pilih Wilayah / Kapela Dahulu --'"
                                                        searchPlaceholder="Cari KUB tujuan..."
                                                        icon="fa-people-roof"
                                                        iconColor="text-emerald-600"
                                                    />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                <!-- 7. Tanggal Mutasi / Meninggal -->
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">
                                        {{ (isKubLevel || formData.jenis_mutasi === 'Meninggal Dunia') ? 'Tanggal Meninggal Dunia *' : 'Tanggal Mutasi *' }}
                                    </label>
                                    <DateInput
                                        v-model="formData.tgl_mutasi"
                                        placeholder="dd/mm/yyyy"
                                        required
                                        inputClass="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-semibold text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition"
                                    />
                                </div>

                                <!-- 8. No. Surat Pindah / Pengantar / Kematian -->
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">
                                        {{ (isKubLevel || formData.jenis_mutasi === 'Meninggal Dunia') ? 'No. Surat Kematian / Akta (Opsional)' : 'No. Surat Pengantar / Pindah' }}
                                    </label>
                                    <input
                                        v-model="formData.no_surat_pindah"
                                        type="text"
                                        :placeholder="(isKubLevel || formData.jenis_mutasi === 'Meninggal Dunia') ? 'Contoh: SKM/001/VIII/2026' : 'Contoh: SP/001/VIII/2026'"
                                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition font-mono"
                                    />
                                </div>

                                <!-- 9. Alasan / Keterangan (Wajib) -->
                                <div class="md:col-span-2 space-y-2">
                                    <div class="flex items-center justify-between">
                                        <label class="block text-[11px] font-bold text-slate-700">
                                            {{ (isKubLevel || formData.jenis_mutasi === 'Meninggal Dunia') ? 'Keterangan Kematian / Tempat Pemakaman *' : 'Alasan Pindah KUB / Catatan Pastoral *' }}
                                        </label>
                                        <span class="text-[10px] text-amber-600 font-bold">* Wajib Diisi</span>
                                    </div>

                                    <!-- Quick Alasan Chips (Khusus Mutasi) -->
                                    <div v-if="formData.jenis_mutasi !== 'Meninggal Dunia'" class="flex flex-wrap gap-1.5 pt-0.5">
                                        <button
                                            type="button"
                                            @click="setQuickAlasan('Pindah domisili / tempat tinggal')"
                                            class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-amber-100 hover:text-amber-900 text-slate-700 text-[10px] font-bold transition cursor-pointer border border-slate-200"
                                        >
                                            🏠 Pindah Domisili
                                        </button>
                                        <button
                                            type="button"
                                            @click="setQuickAlasan('Menikah & membentuk keluarga baru')"
                                            class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-amber-100 hover:text-amber-900 text-slate-700 text-[10px] font-bold transition cursor-pointer border border-slate-200"
                                        >
                                            💍 Menikah / Keluarga Baru
                                        </button>
                                        <button
                                            type="button"
                                            @click="setQuickAlasan('Pekerjaan / tugas dinas')"
                                            class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-amber-100 hover:text-amber-900 text-slate-700 text-[10px] font-bold transition cursor-pointer border border-slate-200"
                                        >
                                            💼 Pekerjaan / Dinas
                                        </button>
                                        <button
                                            type="button"
                                            @click="setQuickAlasan('Pemekaran lingkungan KUB baru')"
                                            class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-amber-100 hover:text-amber-900 text-slate-700 text-[10px] font-bold transition cursor-pointer border border-slate-200"
                                        >
                                            🌱 Pemekaran KUB
                                        </button>
                                        <button
                                            type="button"
                                            @click="setQuickAlasan('Pindah ke Stasi / Kapela')"
                                            class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-amber-100 hover:text-amber-900 text-slate-700 text-[10px] font-bold transition cursor-pointer border border-slate-200"
                                        >
                                            ⛪ Ke Stasi / Kapela
                                        </button>
                                    </div>

                                    <textarea
                                        v-model="formData.alasan"
                                        rows="2.5"
                                        required
                                        :placeholder="(isKubLevel || formData.jenis_mutasi === 'Meninggal Dunia') ? 'Tuliskan keterangan kematian (misal: Sakit, usia lanjut, dimakamkan di TPU Paroki)...' : 'Tuliskan alasan kepindahan KUB secara lengkap (misal: Pindah rumah ke wilayah stasi, menikah, dinas pekerjaan)...'"
                                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition leading-relaxed"
                                    ></textarea>
                                </div>
                            </div>
                        </div>
                    </template>

                    <template v-else-if="moduleKey === 'user'">
                        <div class="grid grid-cols-1 lg:grid-cols-[250px_1fr] gap-4">
                            <div class="rounded-2xl bg-slate-50 border border-slate-200/80 p-4 space-y-3">
                                <h4 class="text-xs font-black text-slate-900 flex items-center gap-2">
                                    <i class="fa-solid fa-camera text-blue-600"></i>
                                    <span>Foto Profil</span>
                                </h4>
                                <div class="w-28 h-28 rounded-full bg-white border border-slate-200 mx-auto overflow-hidden shadow-sm flex items-center justify-center">
                                    <img v-if="previewImage || formData.foto" :src="previewImage || getImageUrl(formData.foto)" class="w-full h-full object-cover" />
                                    <i v-else class="fa-solid fa-user text-4xl text-slate-300"></i>
                                </div>
                                <input type="file" accept="image/*" @change="handleFileUpload($event, 'foto')" class="w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 file:cursor-pointer" />
                                <p class="text-[10px] text-slate-400 text-center">Format JPG, PNG, WebP. Maks. 2MB.</p>
                            </div>

                            <div class="space-y-4">
                                <div class="rounded-2xl bg-white border border-slate-200/80 p-4">
                                    <h4 class="text-xs font-black text-slate-900 flex items-center gap-2 pb-2 border-b border-slate-100 mb-3">
                                        <i class="fa-solid fa-user-shield text-blue-600"></i>
                                        <span>Rincian Akun Pengguna</span>
                                    </h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                        <div>
                                            <label class="block text-[11px] font-bold text-slate-700 mb-1">Nama Lengkap *</label>
                                            <input v-model="formData.nama_lengkap" type="text" placeholder="Nama lengkap pengguna" class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs focus:outline-none focus:border-amber-500" />
                                        </div>
                                        <div>
                                            <label class="block text-[11px] font-bold text-slate-700 mb-1">Username *</label>
                                            <input v-model="formData.username" type="text" autocomplete="new-password" placeholder="username login" class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs font-mono focus:outline-none focus:border-amber-500" />
                                        </div>
                                        <div>
                                            <label class="block text-[11px] font-bold text-slate-700 mb-1">Email</label>
                                            <input v-model="formData.email" type="email" placeholder="alamat@email.com (opsional)" class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs focus:outline-none focus:border-amber-500" />
                                        </div>
                                        <div>
                                            <label class="block text-[11px] font-bold text-slate-700 mb-1">No. HP / WhatsApp</label>
                                            <input v-model="formData.no_hp" type="text" placeholder="08123456789" class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs focus:outline-none focus:border-amber-500" />
                                        </div>
                                        <div class="md:col-span-2">
                                            <label class="block text-[11px] font-bold text-slate-700 mb-1">
                                                Password <span v-if="modalMode === 'create'">*</span>
                                                <span v-else class="font-medium text-slate-400">(kosongkan jika tidak diubah)</span>
                                            </label>
                                            <div class="flex gap-2">
                                                <input v-model="formData.password" :type="showPassword ? 'text' : 'password'" autocomplete="new-password" placeholder="Ketik password atau generate" class="flex-1 px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs focus:outline-none focus:border-amber-500" />
                                                <button type="button" @click="showPassword = !showPassword" class="w-9 rounded-xl bg-slate-50 border border-slate-200 text-slate-600 hover:text-blue-700">
                                                    <i :class="showPassword ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye'"></i>
                                                </button>
                                                <button type="button" @click="generatePassword" class="px-3 rounded-xl bg-blue-50 border border-blue-200 text-blue-700 text-xs font-bold hover:bg-blue-100">Generate</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="rounded-2xl bg-white border border-slate-200/80 p-4">
                                    <h4 class="text-xs font-black text-slate-900 flex items-center gap-2 pb-2 border-b border-slate-100 mb-3">
                                        <i class="fa-solid fa-shield-halved text-emerald-600"></i>
                                        <span>Peran & Relasi Wilayah</span>
                                    </h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                        <!-- 1. Peran / Role -->
                                        <div>
                                            <label class="block text-[11px] font-bold text-slate-700 mb-1">Peran / Role *</label>
                                            <SearchableSelect
                                                v-model="formData.role_id"
                                                :options="sanitizedRoleList"
                                                valueKey="id"
                                                labelKey="nama_role"
                                                placeholder="-- Pilih Peran / Role --"
                                                searchPlaceholder="Cari peran..."
                                                icon="fa-shield-halved"
                                                iconColor="text-amber-600"
                                            />
                                        </div>

                                        <!-- 2. Status Akun -->
                                        <div>
                                            <label class="block text-[11px] font-bold text-slate-700 mb-1">Status Akun</label>
                                            <SearchableSelect
                                                v-model="formData.status"
                                                :options="accountStatusOptions"
                                                valueKey="value"
                                                labelKey="label"
                                                placeholder="Pilih Status"
                                                searchPlaceholder="Cari status..."
                                                icon="fa-circle-check"
                                                iconColor="text-emerald-600"
                                            />
                                        </div>

                                        <!-- 3. Wilayah Terkait -->
                                        <div>
                                            <label class="block text-[11px] font-bold text-slate-700 mb-1">
                                                Wilayah Terkait
                                                <span v-if="formData.kapela_id" class="text-rose-500 text-[9px] font-normal lowercase">(stasi aktif)</span>
                                            </label>
                                            <SearchableSelect
                                                v-model="formData.wilayah_id"
                                                :options="wilayahList"
                                                :disabled="Boolean(formData.kapela_id)"
                                                valueKey="id"
                                                labelKey="nama_wilayah"
                                                :placeholder="formData.kapela_id ? '— Nonaktif (Stasi Dipilih) —' : '-- Tidak Terikat Wilayah --'"
                                                searchPlaceholder="Cari wilayah..."
                                                icon="fa-church"
                                                iconColor="text-blue-600"
                                            />
                                        </div>

                                        <!-- 4. Stasi / Kapela Terkait -->
                                        <div>
                                            <label class="block text-[11px] font-bold text-slate-700 mb-1">
                                                Stasi / Kapela Terkait
                                                <span v-if="formData.wilayah_id" class="text-rose-500 text-[9px] font-normal lowercase">(wilayah aktif)</span>
                                            </label>
                                            <SearchableSelect
                                                v-model="formData.kapela_id"
                                                :options="kapelaList"
                                                :disabled="Boolean(formData.wilayah_id)"
                                                valueKey="id"
                                                labelKey="nama_kapela"
                                                :placeholder="formData.wilayah_id ? '— Nonaktif (Wilayah Dipilih) —' : '-- Tidak Terikat Stasi/Kapela --'"
                                                searchPlaceholder="Cari stasi/kapela..."
                                                icon="fa-place-of-worship"
                                                iconColor="text-indigo-600"
                                            />
                                        </div>

                                        <!-- 5. KUB Terkait -->
                                        <div>
                                            <label class="block text-[11px] font-bold text-slate-700 mb-1">KUB Terkait</label>
                                            <SearchableSelect
                                                v-model="formData.kub_id"
                                                :options="filteredFormKubs"
                                                valueKey="id"
                                                labelKey="nama_kub_with_asal"
                                                placeholder="-- Tidak Terikat KUB --"
                                                searchPlaceholder="Cari KUB..."
                                                icon="fa-users"
                                                iconColor="text-teal-600"
                                            />
                                        </div>

                                        <!-- 6. Akses Maintenance -->
                                        <div>
                                            <label class="block text-[11px] font-bold text-slate-700 mb-1">Akses Maintenance</label>
                                            <SearchableSelect
                                                v-model="formData.maintenance_access"
                                                :options="maintenanceOptions"
                                                valueKey="value"
                                                labelKey="label"
                                                placeholder="Pilih Akses Maintenance"
                                                searchPlaceholder="Cari opsi..."
                                                icon="fa-screwdriver-wrench"
                                                iconColor="text-slate-600"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- 8. ROLE & HAK AKSES FORM (100% Matches http://localhost/katedral/admin/roles) -->
                    <template v-else-if="moduleKey === 'role' || moduleKey === 'roles'">
                        <div class="space-y-4">
                            <h4 class="text-xs font-bold text-slate-900 flex items-center gap-2 pb-2 border-b border-slate-100">
                                <i class="fa-solid fa-shield-halved text-amber-600"></i>
                                <span>Informasi Peran & Hak Akses (RBAC)</span>
                            </h4>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Nama Role / Peran *</label>
                                    <input
                                        v-model="formData.nama_role"
                                        type="text"
                                        placeholder="Contoh: Sekretariat Paroki"
                                        required
                                        class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition font-bold"
                                    />
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Slug / Kode Sistem *</label>
                                    <input
                                        v-model="formData.slug"
                                        type="text"
                                        placeholder="Contoh: sekretariat"
                                        required
                                        class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition font-mono font-bold"
                                    />
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Deskripsi Wewenang & Tanggung Jawab</label>
                                    <textarea
                                        v-model="formData.deskripsi"
                                        rows="2"
                                        placeholder="Jelaskan wewenang, batasan tugas, dan tanggung jawab peran ini..."
                                        class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition"
                                    ></textarea>
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Status Role</label>
                                    <select
                                        v-model="formData.status"
                                        class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition font-semibold"
                                    >
                                        <option :value="1">Aktif (Dapat Digunakan)</option>
                                        <option :value="0">Nonaktif</option>
                                    </select>
                                </div>

                                <!-- HAK AKSES & IZIN MENU (PERMISSIONS) 100% Matches Katedral Admin -->
                                <div class="md:col-span-2 p-4 sm:p-5 rounded-2xl bg-slate-50/80 border border-slate-200/90 space-y-4">
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-3 border-b border-slate-200">
                                        <div>
                                            <h5 class="text-xs sm:text-sm font-black text-slate-900 flex items-center gap-2">
                                                <i class="fa-solid fa-shield-halved text-amber-600"></i>
                                                <span>Hak Akses & Izin Menu (Permissions)</span>
                                            </h5>
                                            <p class="text-[11px] text-slate-500 font-medium">Centang menu-menu yang dapat diakses oleh role ini.</p>
                                        </div>
                                        <div class="flex items-center gap-2 shrink-0">
                                            <button
                                                type="button"
                                                @click="selectAllAllPermissions"
                                                class="px-2.5 py-1 rounded-lg bg-amber-100 hover:bg-amber-200 text-amber-800 text-[10.5px] font-bold transition cursor-pointer"
                                            >
                                                Pilih Semua
                                            </button>
                                            <button
                                                type="button"
                                                @click="clearAllPermissions"
                                                class="px-2.5 py-1 rounded-lg bg-slate-200 hover:bg-slate-300 text-slate-700 text-[10.5px] font-bold transition cursor-pointer"
                                            >
                                                Kosongkan
                                            </button>
                                        </div>
                                    </div>

                                    <!-- 8 CATEGORY GROUPS -->
                                    <div class="space-y-4">
                                        <div
                                            v-for="group in permissionGroups"
                                            :key="group.title"
                                            class="rounded-xl bg-white border border-slate-200/90 p-3.5 shadow-2xs space-y-2.5"
                                        >
                                            <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                                                <span class="text-xs font-bold text-slate-900 flex items-center gap-2">
                                                    <i :class="['fa-solid text-amber-600 text-[11px]', group.icon]"></i>
                                                    <span>{{ group.title }}</span>
                                                </span>
                                                <button
                                                    type="button"
                                                    @click="toggleGroupPermissions(group)"
                                                    class="text-[10.5px] font-bold text-amber-600 hover:text-amber-700 underline cursor-pointer"
                                                >
                                                    {{ isGroupAllSelected(group) ? 'Batal Pilih Semua' : 'Pilih Semua' }}
                                                </button>
                                            </div>

                                            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2 pt-1">
                                                <label
                                                    v-for="item in group.items"
                                                    :key="item.key"
                                                    class="flex items-center gap-2 p-2 rounded-lg border text-xs cursor-pointer transition select-none"
                                                    :class="Array.isArray(formData.permissions) && formData.permissions.includes(item.key)
                                                        ? 'bg-amber-50/60 border-amber-300 font-bold text-amber-900'
                                                        : 'bg-slate-50/50 border-slate-200/80 text-slate-700 hover:border-slate-300'"
                                                >
                                                    <input
                                                        type="checkbox"
                                                        :value="item.key"
                                                        v-model="formData.permissions"
                                                        class="w-3.5 h-3.5 rounded text-amber-600 focus:ring-amber-500 border-slate-300"
                                                    />
                                                    <span class="truncate">{{ item.label }}</span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- 9. RIWAYAT PASTOR PAROKI FORM (100% Matches Katedral) -->
                    <template v-else-if="moduleKey === 'riwayat-pastor' || moduleKey === 'riwayat_pastor_paroki'">
                        <div class="space-y-4">
                            <!-- Foto Pastor Upload -->
                            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-2.5">
                                <label class="block text-xs font-bold text-slate-700">Foto Pastor / Gembala</label>
                                <div class="flex items-center gap-4">
                                    <div class="w-18 h-18 rounded-2xl bg-white border border-slate-200 p-1.5 shadow-2xs flex items-center justify-center overflow-hidden shrink-0">
                                        <img
                                            v-if="previewImage || (formData.foto && typeof formData.foto === 'string')"
                                            :src="previewImage || getImageUrl(formData.foto)"
                                            alt="Foto Pastor"
                                            class="w-full h-full object-cover rounded-xl"
                                        />
                                        <i v-else class="fa-solid fa-user-tie text-2xl text-slate-300"></i>
                                    </div>
                                    <div class="space-y-1.5 flex-1">
                                        <input
                                            type="file"
                                            id="upload-foto-pastor"
                                            accept="image/png, image/jpeg, image/jpg, image/webp"
                                            @change="handleFileUpload($event, 'foto')"
                                            class="hidden"
                                        />
                                        <label
                                            for="upload-foto-pastor"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white hover:bg-slate-100 border border-slate-200 text-xs font-bold text-slate-700 transition cursor-pointer shadow-2xs"
                                        >
                                            <i class="fa-solid fa-arrow-up-from-bracket text-[11px] text-amber-600"></i>
                                            <span>Unggah Foto Pastor</span>
                                        </label>
                                        <p class="text-[10px] text-slate-400">Format: JPG, JPEG, PNG, WEBP. Maks 3MB</p>
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                                <div class="md:col-span-2">
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Nama Lengkap Pastor & Gelar *</label>
                                    <input
                                        v-model="formData.nama_pastor"
                                        type="text"
                                        placeholder="Contoh: RD. Fransiskus Xaverius, Pr"
                                        required
                                        class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 font-bold"
                                    />
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Jabatan di Paroki *</label>
                                    <select
                                        v-model="formData.jabatan"
                                        class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 font-semibold"
                                    >
                                        <option value="Pastor Paroki">Pastor Paroki</option>
                                        <option value="Pastor Rekan">Pastor Rekan</option>
                                        <option value="Pastor Vikaris">Pastor Vikaris</option>
                                        <option value="Pastor Administrator">Pastor Administrator</option>
                                        <option value="Pastor Tamu">Pastor Tamu</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Status Pelayanan</label>
                                    <select
                                        v-model="formData.status_pelayanan"
                                        class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 font-semibold"
                                    >
                                        <option value="Sedang Menjabat">Sedang Menjabat (Aktif)</option>
                                        <option value="Selesai Bertugas">Selesai Bertugas / Mutasi</option>
                                        <option value="Emeritus">Emeritus / Purna Bakti</option>
                                        <option value="Meninggal">Meninggal Dunia</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Periode / Tahun Mulai</label>
                                    <input
                                        v-model="formData.periode_mulai"
                                        type="text"
                                        placeholder="Contoh: 2018 atau 15 Agustus 2018"
                                        class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 font-medium"
                                    />
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Periode / Tahun Selesai</label>
                                    <input
                                        v-model="formData.periode_selesai"
                                        type="text"
                                        placeholder="Contoh: 2023 atau Sekarang"
                                        class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 font-medium"
                                    />
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Urutan Gembala</label>
                                    <input
                                        v-model="formData.urutan"
                                        type="number"
                                        placeholder="Contoh: 1"
                                        class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 font-mono font-bold"
                                    />
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Catatan Karya & Sejarah Pelayanan Paroki</label>
                                    <textarea
                                        v-model="formData.keterangan"
                                        rows="3"
                                        placeholder="Tuliskan karya-karya pembangunan, penggembalaan, atau catatan sejarah selama bertugas di paroki..."
                                        class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 transition"
                                    ></textarea>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- 9B. DIREKTORI MISDINAR FORM -->
                    <template v-else-if="moduleKey === 'direktori-misdinar' || moduleKey === 'direktori_misdinar'">
                        <div class="space-y-4 text-xs">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 mb-1">
                                    Nama Lengkap Anggota Misdinar <span class="text-rose-500">*</span>
                                </label>
                                <input
                                    v-model="formData.nama_lengkap"
                                    type="text"
                                    placeholder="Contoh: Antonius Maria Goretti"
                                    required
                                    class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 font-bold"
                                />
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">
                                        Stasi / Kapela (Data Gerejawi) <span class="text-rose-500">*</span>
                                    </label>
                                    <SearchableSelect
                                        v-model="formData.stasi"
                                        :options="kapelaList"
                                        value-key="nama_kapela"
                                        label-key="nama_kapela"
                                        placeholder="-- Pilih Stasi / Kapela --"
                                        search-placeholder="Ketik cari stasi / kapela..."
                                        icon="fa-place-of-worship"
                                        icon-color="text-amber-600"
                                    />
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">
                                        Status Keanggotaan <span class="text-rose-500">*</span>
                                    </label>
                                    <select
                                        v-model="formData.status_aktif"
                                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 font-semibold"
                                    >
                                        <option value="Aktif">Aktif (Bertugas)</option>
                                        <option value="Purna Bakti">Purna Bakti (Senior)</option>
                                        <option value="Nonaktif">Nonaktif</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- 9C. DIREKTORI KATEKIS FORM -->
                    <template v-else-if="moduleKey === 'direktori-katekis' || moduleKey === 'direktori_katekis'">
                        <div class="space-y-4 text-xs">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                                <div class="sm:col-span-2">
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">
                                        Nama Lengkap Katekis <span class="text-rose-500">*</span>
                                    </label>
                                    <input
                                        v-model="formData.nama_lengkap"
                                        type="text"
                                        placeholder="Contoh: Bpk. Petrus Taena, S.Ag"
                                        required
                                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 font-bold"
                                    />
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Jenis Katekis</label>
                                    <select
                                        v-model="formData.jenis_katekis"
                                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 font-semibold"
                                    >
                                        <option value="Katekis Sukarela (Umat)">Katekis Sukarela (Umat)</option>
                                        <option value="Katekis Paroki Resmi">Katekis Paroki Resmi</option>
                                        <option value="Guru Agama Katolik">Guru Agama Katolik</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Wilayah / Stasi Pelayanan</label>
                                    <SearchableSelect
                                        v-model="formData.wilayah_pelayanan"
                                        :options="kapelaList"
                                        value-key="nama_kapela"
                                        label-key="nama_kapela"
                                        placeholder="-- Pilih Stasi / Kapela --"
                                        search-placeholder="Ketik cari stasi / kapela..."
                                        icon="fa-place-of-worship"
                                        icon-color="text-amber-600"
                                    />
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">No. Kontak / WhatsApp</label>
                                    <input
                                        v-model="formData.no_hp"
                                        type="text"
                                        placeholder="081234567890"
                                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500"
                                    />
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Status Katekis</label>
                                    <select
                                        v-model="formData.status_aktif"
                                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 font-semibold"
                                    >
                                        <option value="Aktif">Aktif</option>
                                        <option value="Nonaktif">Nonaktif</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- 10. KONTEN WEBSITE FORM -->
                    <template v-else-if="moduleKey === 'konten'">
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 text-xs">
                            <div class="lg:col-span-2 space-y-4">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">
                                        Judul Postingan <span class="text-rose-500">*</span>
                                    </label>
                                    <input
                                        v-model="formData.judul"
                                        type="text"
                                        required
                                        placeholder="Masukkan judul berita atau artikel..."
                                        @input="updateKontenSlug"
                                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 font-bold"
                                    />
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Slug SEO / URL</label>
                                    <div class="flex rounded-xl overflow-hidden border border-slate-200 bg-slate-50 focus-within:border-amber-500">
                                        <span class="px-3 py-2 bg-white border-r border-slate-200 text-[11px] text-slate-400 font-mono">/berita/</span>
                                        <input
                                            v-model="formData.slug"
                                            type="text"
                                            placeholder="otomatis-dari-judul"
                                            class="flex-1 px-3 py-2 bg-transparent text-xs text-amber-700 font-mono font-bold focus:outline-none"
                                        />
                                    </div>
                                </div>

                                <div>
                                    <div class="flex items-center justify-between gap-2 mb-1">
                                        <label class="block text-[11px] font-bold text-slate-700">
                                            Ringkasan / Excerpt
                                        </label>
                                        <button
                                            type="button"
                                            @click="generateKontenExcerpt"
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-amber-50 text-amber-800 border border-amber-200 text-[11px] font-bold"
                                        >
                                            <i class="fa-solid fa-wand-magic-sparkles"></i>
                                            <span>Generate</span>
                                        </button>
                                    </div>
                                    <textarea
                                        v-model="formData.excerpt"
                                        rows="3"
                                        placeholder="Ringkasan singkat yang tampil di daftar berita/artikel..."
                                        class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500"
                                    ></textarea>
                                    <p class="mt-1 text-[10px] text-slate-400">{{ (formData.excerpt || '').length }} karakter</p>
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">
                                        Isi Konten Postingan <span class="text-rose-500">*</span>
                                    </label>
                                    <RichTextEditor
                                        v-model="formData.isi"
                                        placeholder="Tuliskan isi berita, artikel, atau pengumuman secara lengkap dan rapi..."
                                        min-height="280"
                                    />
                                </div>
                            </div>

                            <div class="space-y-4">
                                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-3">
                                    <div class="grid grid-cols-1 gap-3">
                                        <div>
                                            <label class="block text-[11px] font-bold text-slate-700 mb-1">Status Publikasi</label>
                                            <select v-model="formData.status_publish" class="w-full px-3.5 py-2 rounded-xl bg-white border border-slate-200 text-xs font-bold text-slate-900 focus:outline-none focus:border-amber-500">
                                                <option value="Publish">Diterbitkan (Publish)</option>
                                                <option value="Draft">Draft</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-[11px] font-bold text-slate-700 mb-1">Tanggal Publikasi</label>
                                            <input v-model="formData.tanggal_publish" type="datetime-local" class="w-full px-3.5 py-2 rounded-xl bg-white border border-slate-200 text-xs font-semibold text-slate-900 focus:outline-none focus:border-amber-500" />
                                        </div>
                                        <label class="flex items-center gap-2 text-[11px] font-bold text-slate-700">
                                            <input v-model="formData.is_featured" type="checkbox" true-value="1" false-value="0" class="rounded border-slate-300 text-amber-600 focus:ring-amber-500" />
                                            <span>Jadikan Postingan Utama</span>
                                        </label>
                                    </div>
                                </div>

                                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-3">
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Tipe Konten <span class="text-rose-500">*</span></label>
                                        <select v-model="formData.tipe" required class="w-full px-3.5 py-2 rounded-xl bg-white border border-slate-200 text-xs font-semibold text-slate-900 focus:outline-none focus:border-amber-500">
                                            <option v-for="tipe in tipeKontenList" :key="tipe" :value="tipe">{{ tipe }}</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Kategori Konten</label>
                                        <select v-model="formData.kategori_id" @change="syncKontenKategori" class="w-full px-3.5 py-2 rounded-xl bg-white border border-slate-200 text-xs font-semibold text-slate-900 focus:outline-none focus:border-amber-500">
                                            <option value="">-- Tanpa Kategori / Umum --</option>
                                            <option v-for="kategori in kategoriKontenList" :key="kategori.id" :value="kategori.id">{{ kategori.nama_kategori }}</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-3">
                                    <label class="block text-[11px] font-bold text-slate-700">Gambar Sampul</label>
                                    <div class="aspect-video rounded-xl bg-white border border-slate-200 overflow-hidden flex items-center justify-center">
                                        <img v-if="previewImage || formData.gambar" :src="previewImage || getImageUrl(formData.gambar)" class="w-full h-full object-cover" />
                                        <i v-else class="fa-solid fa-image text-3xl text-slate-300"></i>
                                    </div>
                                    <input type="file" accept="image/*" @change="handleFileUpload($event, 'gambar')" class="w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-amber-50 file:text-amber-800 hover:file:bg-amber-100 file:cursor-pointer" />
                                </div>

                                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-3">
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Dokumen Lampiran PDF</label>
                                        <input v-model="formData.file_pdf" type="text" placeholder="Nama file PDF / path lampiran" class="w-full px-3.5 py-2 rounded-xl bg-white border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500" />
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-700 mb-1">ID Arsip Digital</label>
                                        <input v-model="formData.arsip_id" type="text" placeholder="Opsional" class="w-full px-3.5 py-2 rounded-xl bg-white border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500" />
                                    </div>
                                </div>

                                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-3">
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Penulis / Kontributor</label>
                                        <input v-model="formData.penulis" list="konten-penulis-options" type="text" class="w-full px-3.5 py-2 rounded-xl bg-white border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500" />
                                        <datalist id="konten-penulis-options">
                                            <option v-for="penulis in penulisList" :key="penulis" :value="penulis" />
                                        </datalist>
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Tags / Label</label>
                                        <input v-model="formData.tags" type="text" placeholder="Paroki, Liturgi, OMK" class="w-full px-3.5 py-2 rounded-xl bg-white border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500" />
                                    </div>
                                    <div class="flex flex-wrap gap-1.5">
                                        <button v-for="tag in ['Paroki', 'Liturgi', 'OMK', 'Misa', 'Katekese', 'Sosial', 'Pengumuman', 'Renungan']" :key="tag" type="button" @click="addKontenTag(tag)" class="px-2 py-1 rounded-full bg-white border border-slate-200 text-[10px] font-bold text-slate-600 hover:text-amber-800 hover:border-amber-300">
                                            + {{ tag }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- 11. RAPAT & NOTULEN FORM -->
                    <template v-else-if="moduleKey === 'rapat' || moduleKey === 'rapat-notulen'">
                        <div class="space-y-4 text-xs">
                            <div class="space-y-3.5">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">
                                        Agenda / Judul Rapat <span class="text-rose-500">*</span>
                                    </label>
                                    <input
                                        v-model="formData.agenda"
                                        type="text"
                                        placeholder="Contoh: Rapat Pleno DPP Inti Paroki"
                                        required
                                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 font-bold"
                                    />
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-700 mb-1">
                                            Tanggal Pelaksanaan <span class="text-rose-500">*</span>
                                        </label>
                                        <DateInput
                                            v-model="formData.tanggal"
                                            placeholder="dd/mm/yyyy"
                                            required
                                            inputClass="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs font-semibold text-slate-900 focus:outline-none focus:border-amber-500 transition"
                                        />
                                    </div>

                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-700 mb-1">
                                            Waktu / Jam Mulai
                                        </label>
                                        <div class="relative">
                                            <input
                                                v-model="formData.waktu"
                                                type="time"
                                                placeholder="Contoh: 19:00"
                                                class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs font-semibold text-slate-900 focus:outline-none focus:border-amber-500 transition"
                                            />
                                        </div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-700 mb-1">
                                            Lokasi / Tempat Rapat
                                        </label>
                                        <input
                                            v-model="formData.lokasi"
                                            type="text"
                                            placeholder="Contoh: Aula Paroki / Ruang Pastoral"
                                            class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 font-medium"
                                        />
                                    </div>

                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-700 mb-1">
                                            Status Rapat
                                        </label>
                                        <select
                                            v-model="formData.status"
                                            class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 font-semibold"
                                        >
                                            <option value="Aktif">Aktif (Dijadwalkan)</option>
                                            <option value="Terlaksana">Terlaksana</option>
                                            <option value="Ditunda">Ditunda</option>
                                            <option value="Dibatalkan">Dibatalkan</option>
                                        </select>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">
                                        Notulen & Hasil Pembahasan Rapat
                                    </label>
                                    <RichTextEditor
                                        v-model="formData.notulen"
                                        placeholder="Tuliskan ringkasan hasil rapat, poin-poin keputusan penting, dan tindak lanjut..."
                                        min-height="180"
                                    />
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- 12. AGENDA KEGIATAN PAROKI FORM -->
                    <template v-else-if="moduleKey === 'kegiatan'">
                        <div class="space-y-4 text-xs">
                            <!-- Poster / Foto Kegiatan -->
                            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-2.5">
                                <label class="block text-xs font-bold text-slate-700">Pamflet / Poster / Foto Kegiatan</label>
                                <div class="flex items-center gap-4">
                                    <div class="w-20 h-20 rounded-2xl bg-white border border-slate-200 p-1 shadow-2xs flex items-center justify-center overflow-hidden shrink-0">
                                        <img
                                            v-if="previewImage || (formData.gambar && typeof formData.gambar === 'string') || (formData.foto && typeof formData.foto === 'string')"
                                            :src="previewImage || getImageUrl(formData.gambar || formData.foto)"
                                            alt="Poster Kegiatan"
                                            class="w-full h-full object-cover rounded-xl"
                                        />
                                        <i v-else class="fa-solid fa-calendar-days text-2xl text-slate-300"></i>
                                    </div>
                                    <div class="space-y-1.5 flex-1">
                                        <input
                                            type="file"
                                            id="upload-kegiatan-poster"
                                            accept="image/png, image/jpeg, image/jpg, image/webp"
                                            @change="handleFileUpload($event, 'gambar')"
                                            class="hidden"
                                        />
                                        <label
                                            for="upload-kegiatan-poster"
                                            class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 text-xs font-bold transition cursor-pointer shadow-2xs"
                                        >
                                            <i class="fa-solid fa-cloud-arrow-up text-amber-600 text-sm"></i>
                                            <span>{{ (previewImage || formData.gambar || formData.foto) ? 'Ganti Poster / Foto' : 'Unggah Poster Kegiatan' }}</span>
                                        </label>
                                        <p class="text-[10px] text-slate-400">PNG, JPG, WEBP (Maks. 3MB)</p>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-3.5">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">
                                        Nama / Judul Kegiatan <span class="text-rose-500">*</span>
                                    </label>
                                    <input
                                        v-model="formData.nama_kegiatan"
                                        type="text"
                                        placeholder="Contoh: Rekoleksi OMK Se-Paroki 2026"
                                        required
                                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 font-bold"
                                    />
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Kategori Kegiatan</label>
                                        <select
                                            v-model="formData.kategori"
                                            class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 font-semibold"
                                        >
                                            <option value="Liturgi & Ibadah">Liturgi & Ibadah</option>
                                            <option value="Pastoral & Pembinaan">Pastoral & Pembinaan</option>
                                            <option value="Sosial & Kemasyarakatan">Sosial & Kemasyarakatan</option>
                                            <option value="OMK & Kepemudaan">OMK & Kepemudaan</option>
                                            <option value="Rapat & Pertemuan">Rapat & Pertemuan</option>
                                            <option value="Kategorial">Kategorial</option>
                                            <option value="Umum & Lainnya">Umum & Lainnya</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Status Kegiatan</label>
                                        <select
                                            v-model="formData.status"
                                            class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 font-semibold"
                                        >
                                            <option value="Akan Datang">Akan Datang (Dijadwalkan)</option>
                                            <option value="Berlangsung">Sedang Berlangsung</option>
                                            <option value="Selesai">Selesai</option>
                                            <option value="Ditunda">Ditunda</option>
                                            <option value="Dibatalkan">Dibatalkan</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-700 mb-1">
                                            Tanggal Mulai <span class="text-rose-500">*</span>
                                        </label>
                                        <DateInput
                                            v-model="formData.tanggal_mulai"
                                            placeholder="dd/mm/yyyy"
                                            required
                                            inputClass="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs font-semibold text-slate-900 focus:outline-none focus:border-amber-500 transition"
                                        />
                                    </div>

                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Tanggal Selesai</label>
                                        <DateInput
                                            v-model="formData.tanggal_selesai"
                                            placeholder="dd/mm/yyyy"
                                            inputClass="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs font-semibold text-slate-900 focus:outline-none focus:border-amber-500 transition"
                                        />
                                    </div>

                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Waktu / Jam Mulai</label>
                                        <input
                                            v-model="formData.waktu"
                                            type="time"
                                            class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs font-semibold text-slate-900 focus:outline-none focus:border-amber-500 transition"
                                        />
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Lokasi / Tempat Pelaksanaan</label>
                                        <input
                                            v-model="formData.lokasi"
                                            type="text"
                                            placeholder="Contoh: Gereja Pusat / Aula Paroki"
                                            class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500"
                                        />
                                    </div>

                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Penyelenggara / Panitia</label>
                                        <input
                                            v-model="formData.penyelenggara"
                                            type="text"
                                            placeholder="Contoh: DPP Bidang Kepemudaan / Panitia Paskah"
                                            class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500"
                                        />
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Deskripsi & Rincian Kegiatan</label>
                                    <RichTextEditor
                                        v-model="formData.deskripsi"
                                        placeholder="Tuliskan keterangan lengkap tentang kegiatan, susunan acara, persyaratan peserta, dll..."
                                        min-height="180"
                                    />
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- 13. PENGUMUMAN PAROKI FORM -->
                    <template v-else-if="moduleKey === 'pengumuman'">
                        <div class="space-y-4 text-xs">
                            <div class="space-y-3.5">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">
                                        Judul Pengumuman <span class="text-rose-500">*</span>
                                    </label>
                                    <input
                                        v-model="formData.judul"
                                        type="text"
                                        placeholder="Contoh: Pengumuman Pendaftaran Calon Baptis Baru"
                                        required
                                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 font-bold"
                                    />
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Tanggal Tayang / Berlaku</label>
                                        <DateInput
                                            v-model="formData.tgl_tayang"
                                            placeholder="dd/mm/yyyy"
                                            inputClass="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs font-semibold text-slate-900 focus:outline-none focus:border-amber-500"
                                        />
                                    </div>

                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Status Pengumuman</label>
                                        <select
                                            v-model="formData.status"
                                            class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 font-semibold"
                                        >
                                            <option value="Aktif">Aktif (Ditampilkan)</option>
                                            <option value="Nonaktif">Nonaktif / Diarsipkan</option>
                                        </select>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">
                                        Isi Lengkap Pengumuman <span class="text-rose-500">*</span>
                                    </label>
                                    <RichTextEditor
                                        v-model="formData.isi"
                                        placeholder="Tuliskan naskah pengumuman paroki secara lengkap..."
                                        min-height="240"
                                    />
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- 14. RENUNGAN HARIAN & ROHANI FORM -->
                    <template v-else-if="moduleKey === 'renungan'">
                        <div class="space-y-4 text-xs">
                            <div class="space-y-3.5">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">
                                        Tema / Judul Renungan <span class="text-rose-500">*</span>
                                    </label>
                                    <input
                                        v-model="formData.judul"
                                        type="text"
                                        placeholder="Contoh: Menjadi Garam dan Terang Dunia"
                                        required
                                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 font-bold"
                                    />
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Tanggal Renungan <span class="text-rose-500">*</span></label>
                                        <DateInput
                                            v-model="formData.tanggal"
                                            placeholder="dd/mm/yyyy"
                                            required
                                            inputClass="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs font-semibold text-slate-900 focus:outline-none focus:border-amber-500"
                                        />
                                    </div>

                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Bacaan Kitab Suci / Perikop</label>
                                        <input
                                            v-model="formData.bacaan_kitab_suci"
                                            type="text"
                                            placeholder="Contoh: Matius 5:13-16"
                                            class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 font-semibold"
                                        />
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">
                                        Naskah Renungan & Refleksi Rohani <span class="text-rose-500">*</span>
                                    </label>
                                    <RichTextEditor
                                        v-model="formData.isi"
                                        placeholder="Tuliskan renungan firman, refleksi rohani, dan doa..."
                                        min-height="240"
                                    />
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- 15. KRONIK PAROKI FORM -->
                    <template v-else-if="moduleKey === 'kronik'">
                        <div class="space-y-4 text-xs">
                            <div class="space-y-3.5">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">
                                        Judul Catatan Kronik <span class="text-rose-500">*</span>
                                    </label>
                                    <input
                                        v-model="formData.judul_kronik"
                                        type="text"
                                        placeholder="Contoh: Kunjungan Kanonik Bapak Uskup ke Paroki Benlutu"
                                        required
                                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 font-bold"
                                    />
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Tanggal Peristiwa</label>
                                        <DateInput
                                            v-model="formData.tanggal_peristiwa"
                                            placeholder="dd/mm/yyyy"
                                            inputClass="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs font-semibold text-slate-900 focus:outline-none focus:border-amber-500"
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Kategori Kronik</label>
                                        <input
                                            v-model="formData.kategori_kronik"
                                            type="text"
                                            placeholder="Contoh: Pastoral / Sejarah"
                                            class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500"
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Lokasi Peristiwa</label>
                                        <input
                                            v-model="formData.lokasi_peristiwa"
                                            type="text"
                                            placeholder="Contoh: Gereja Paroki"
                                            class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500"
                                        />
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">
                                        Narasi Sejarah & Uraian Kronik <span class="text-rose-500">*</span>
                                    </label>
                                    <RichTextEditor
                                        v-model="formData.deskripsi"
                                        placeholder="Tuliskan catatan peristiwa, urutan kronologi, dan dokumentasi sejarah..."
                                        min-height="240"
                                    />
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- 16. BUKU SAKRAMEN FORM -->
                    <template v-else-if="moduleKey === 'sakramen'">
                        <div class="space-y-4 text-xs">
                            <div class="space-y-3.5">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">
                                        Tipe Sakramen Suci <span class="text-rose-500">*</span>
                                    </label>
                                    <SearchableSelect
                                        v-model="formData.tipe_sakramen"
                                        :options="tipeSakramenOptions"
                                        valueKey="value"
                                        labelKey="label"
                                        placeholder="-- Pilih Tipe Sakramen Suci --"
                                        searchPlaceholder="Ketik cari tipe sakramen..."
                                        icon="fa-cross"
                                        iconColor="text-amber-600"
                                    />
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-700 mb-1">
                                            Tanggal Penerimaan Sakramen <span class="text-rose-500">*</span>
                                        </label>
                                        <DateInput
                                            v-model="formData.tanggal"
                                            placeholder="dd/mm/yyyy"
                                            required
                                            inputClass="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs font-semibold text-slate-900 focus:outline-none focus:border-amber-500"
                                        />
                                    </div>

                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-700 mb-1">
                                            Gereja / Tempat Penerimaan <span class="text-rose-500">*</span>
                                        </label>
                                        <input
                                            v-model="formData.tempat"
                                            type="text"
                                            placeholder="Contoh: Gereja Paroki Benlutu / Kapela St. Mikael"
                                            required
                                            class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 font-semibold"
                                        />
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Pastor / Pelayan Sakramen</label>
                                        <SearchableSelect
                                            v-model="formData.pastor"
                                            :options="pastorParokiOptions"
                                            valueKey="name"
                                            labelKey="name"
                                            placeholder="-- Pilih Pastor Pelayan --"
                                            searchPlaceholder="Ketik cari pastor..."
                                            icon="fa-user-tie"
                                            iconColor="text-amber-600"
                                        />
                                    </div>

                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-700 mb-1">No. Surat / Nomor Akta Sakramen</label>
                                        <input
                                            v-model="formData.no_surat"
                                            type="text"
                                            placeholder="Contoh: 012/BAP/PRK/2026"
                                            class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 font-mono text-[11px]"
                                        />
                                    </div>
                                </div>

                                <div v-if="formData.tipe_sakramen === 'Baptis' || formData.tipe_sakramen === 'Sakramen Baptis'" class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Wali Baptis (Pria / Wanita)</label>
                                        <input
                                            v-model="formData.wali_baptis"
                                            type="text"
                                            placeholder="Nama lengkap wali baptis"
                                            class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500"
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Nama Baptis / Santo Pelindung</label>
                                        <input
                                            v-model="formData.nama_pendamping"
                                            type="text"
                                            placeholder="Contoh: Fransiskus Xaverius"
                                            class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500"
                                        />
                                    </div>
                                </div>

                                <div v-if="formData.tipe_sakramen === 'Pernikahan' || formData.tipe_sakramen === 'Sakramen Pernikahan'" class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Nama Pasangan</label>
                                        <input
                                            v-model="formData.nama_pasangan"
                                            type="text"
                                            placeholder="Nama lengkap pasangan suami/istri"
                                            class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500"
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Saksi Perkawinan</label>
                                        <input
                                            v-model="formData.saksi_1"
                                            type="text"
                                            placeholder="Nama saksi pernikahan"
                                            class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500"
                                        />
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5 p-3 bg-slate-50 rounded-xl border border-slate-200/80">
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-600 uppercase mb-1">Buku Liber (Volume)</label>
                                        <input
                                            v-model="formData.liber_vol"
                                            type="text"
                                            placeholder="Contoh: Vol. IV"
                                            class="w-full px-3 py-1.5 rounded-lg bg-white border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 font-mono text-[11px]"
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-600 uppercase mb-1">Halaman Buku (Hal)</label>
                                        <input
                                            v-model="formData.liber_hal"
                                            type="text"
                                            placeholder="Contoh: Hal. 88"
                                            class="w-full px-3 py-1.5 rounded-lg bg-white border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 font-mono text-[11px]"
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-600 uppercase mb-1">Nomor Akta (No)</label>
                                        <input
                                            v-model="formData.liber_no"
                                            type="text"
                                            placeholder="Contoh: No. 124"
                                            class="w-full px-3 py-1.5 rounded-lg bg-white border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 font-mono text-[11px]"
                                        />
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Catatan Tambahan / Keterangan</label>
                                    <textarea
                                        v-model="formData.keterangan"
                                        rows="2"
                                        placeholder="Catatan tambahan sakramen..."
                                        class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500"
                                    ></textarea>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- 17. PENGAJUAN SAKRAMEN FORM (100% Matches http://localhost/katedral/admin/pengajuan-sakramen/create) -->
                    <template v-else-if="moduleKey === 'pengajuan-sakramen'">
                        <div class="space-y-4 text-xs">
                            <!-- Row 1: Pilih Umat & Tipe Sakramen -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                                <div>
                                    <div class="flex items-center justify-between mb-1">
                                        <label class="block text-[11px] font-bold text-slate-700">Pilih Umat Terdaftar (Opsional)</label>
                                        <span class="text-[9px] font-semibold text-amber-700 bg-amber-50 border border-amber-200 px-1.5 py-0.5 rounded">Autofill</span>
                                    </div>
                                    <SearchableSelect
                                        v-model="formData.umat_id"
                                        :options="[]"
                                        :search-url="umatSearchUrl"
                                        value-key="id"
                                        label-key="name"
                                        placeholder="-- Cari nama / NIK umat --"
                                        searchPlaceholder="Ketik cari nama / NIK..."
                                        icon="fa-user-check"
                                        iconColor="text-teal-600"
                                    />
                                    <p class="text-[10px] text-slate-400 mt-1">Pilih dari database untuk mengisi nama & WhatsApp otomatis.</p>
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">
                                        Tipe Sakramen Suci <span class="text-rose-500">*</span>
                                    </label>
                                    <SearchableSelect
                                        v-model="formData.tipe_sakramen"
                                        :options="tipeSakramenOptions"
                                        valueKey="value"
                                        labelKey="label"
                                        placeholder="-- Pilih Tipe Sakramen --"
                                        searchPlaceholder="Ketik cari tipe sakramen..."
                                        icon="fa-cross"
                                        iconColor="text-amber-600"
                                    />
                                </div>
                            </div>

                            <!-- Row 2: Nama Lengkap & WhatsApp -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">
                                        Nama Lengkap Penerima / Pemohon <span class="text-rose-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <i class="fa-solid fa-user absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                                        <input
                                            v-model="formData.nama_lengkap"
                                            type="text"
                                            required
                                            placeholder="Masukkan nama lengkap calon penerima..."
                                            class="w-full pl-9 pr-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 font-semibold"
                                        />
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">
                                        No. WhatsApp / Handphone <span class="text-rose-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <i class="fa-brands fa-whatsapp absolute left-3.5 top-1/2 -translate-y-1/2 text-emerald-500 text-sm"></i>
                                        <input
                                            v-model="formData.whatsapp"
                                            type="text"
                                            required
                                            placeholder="Contoh: 081234567890"
                                            class="w-full pl-9 pr-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 font-mono text-[11px]"
                                        />
                                    </div>
                                </div>
                            </div>

                            <!-- Row 3: Tanggal & Biaya Administrasi -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">
                                        Tanggal Rencana Pelaksanaan
                                    </label>
                                    <DateInput
                                        v-model="formData.tanggal_pelaksanaan"
                                        placeholder="dd/mm/yyyy"
                                        inputClass="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-semibold text-slate-900 focus:outline-none focus:border-amber-500"
                                    />
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">
                                        Biaya Administrasi (Rupiah)
                                    </label>
                                    <div class="relative">
                                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 font-bold text-xs">Rp</span>
                                        <input
                                            v-model="formData.biaya_administrasi"
                                            type="number"
                                            class="w-full pl-10 pr-3.5 py-2.5 rounded-xl bg-slate-100 border border-slate-200 text-xs font-bold text-slate-800 focus:outline-none"
                                        />
                                    </div>
                                    <p class="text-[10px] text-slate-400 mt-1">Biaya standar administrasi pengajuan sakramen.</p>
                                </div>
                            </div>

                            <!-- Row 4: Status Pembayaran & Status Pengajuan -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Status Pembayaran</label>
                                    <select
                                        v-model="formData.status_pembayaran"
                                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 font-semibold focus:outline-none focus:border-amber-500"
                                    >
                                        <option value="Menunggu Verifikasi">Menunggu Verifikasi</option>
                                        <option value="Lunas">Lunas / Terverifikasi</option>
                                        <option value="Ditolak">Ditolak</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Status Pengajuan</label>
                                    <select
                                        v-model="formData.status_pengajuan"
                                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 font-semibold focus:outline-none focus:border-amber-500"
                                    >
                                        <option value="Pending">Pending</option>
                                        <option value="Diproses">Diproses</option>
                                        <option value="Selesai">Selesai / Terbit</option>
                                        <option value="Ditolak">Ditolak</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Row 5: Keterangan / Catatan -->
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 mb-1">
                                    Keterangan / Catatan Tambahan
                                </label>
                                <textarea
                                    v-model="formData.keterangan"
                                    rows="3"
                                    placeholder="Tuliskan nama Wali Baptis (Godparents), nama saksi nikah, atau catatan penting lainnya..."
                                    class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500"
                                ></textarea>
                            </div>

                            <!-- Row 6: QRIS Info Box -->
                            <div class="p-3.5 bg-amber-50/70 rounded-2xl border border-amber-200/80 flex flex-col sm:flex-row items-center gap-4">
                                <div class="w-20 h-20 rounded-xl bg-white border border-amber-200 shadow-2xs flex flex-col items-center justify-center p-2 shrink-0">
                                    <i class="fa-solid fa-qrcode text-3xl text-amber-800"></i>
                                    <span class="text-[9px] font-black text-amber-700 uppercase mt-0.5">QRIS</span>
                                </div>
                                <div class="text-xs text-slate-700 space-y-1">
                                    <h5 class="font-bold text-amber-900 flex items-center gap-1.5 text-xs">
                                        <i class="fa-solid fa-circle-info text-amber-600"></i>
                                        <span>Biaya Administrasi: Rp 25.000</span>
                                    </h5>
                                    <p class="text-[11px] text-slate-600 leading-relaxed">
                                        Umat dapat melakukan transfer administrasi via barcode QRIS Paroki atau rekening kas paroki, lalu mengunggah struk/bukti transfer di bawah.
                                    </p>
                                </div>
                            </div>

                            <!-- Row 7: Upload Bukti Transfer / Berkas -->
                            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-2.5">
                                <label class="block text-xs font-bold text-slate-700">Foto Bukti Transfer QRIS / Berkas Persyaratan (Opsional)</label>
                                <div class="flex items-center gap-4">
                                    <div class="w-18 h-18 rounded-2xl bg-white border border-slate-200 p-1.5 shadow-2xs flex items-center justify-center overflow-hidden shrink-0">
                                        <img
                                            v-if="previewImage || (formData.bukti_pembayaran && typeof formData.bukti_pembayaran === 'string')"
                                            :src="previewImage || getImageUrl(formData.bukti_pembayaran)"
                                            alt="Bukti Transfer"
                                            class="w-full h-full object-contain"
                                        />
                                        <i v-else class="fa-solid fa-receipt text-2xl text-slate-300"></i>
                                    </div>
                                    <div class="space-y-1.5 flex-1">
                                        <input
                                            type="file"
                                            id="upload-bukti-pembayaran"
                                            accept="image/png, image/jpeg, image/jpg, application/pdf"
                                            @change="handleFileUpload($event, 'bukti_pembayaran')"
                                            class="hidden"
                                        />
                                        <label
                                            for="upload-bukti-pembayaran"
                                            class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 text-xs font-bold transition cursor-pointer shadow-2xs"
                                        >
                                            <i class="fa-solid fa-cloud-arrow-up text-amber-600 text-sm"></i>
                                            <span>{{ (previewImage || formData.bukti_pembayaran) ? 'Ganti Bukti Pembayaran' : 'Unggah Bukti Transfer QRIS' }}</span>
                                        </label>
                                        <p class="text-[10px] text-slate-400">
                                            Format: JPG, PNG, PDF (Maks. 2MB)
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- 17. IURAN UMAT FORM (100% Select from Database: KK, Jenis Iuran, Tahun, Bulan, Status) -->
                    <template v-else-if="moduleKey === 'iuran' || moduleKey === 'iuran-umat'">
                        <div class="space-y-4 text-xs">
                            <!-- Card Info -->
                            <div class="p-3.5 bg-amber-50/70 rounded-2xl border border-amber-200/80 flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center text-lg shrink-0 shadow-xs">
                                    <i class="fa-solid fa-hand-holding-dollar"></i>
                                </div>
                                <div class="text-xs text-slate-700">
                                    <h5 class="font-bold text-amber-900">Formulir Pencatatan Iuran Umat</h5>
                                    <p class="text-[11px] text-slate-600">Pilih Kartu Keluarga dan Jenis Iuran dari database untuk mencatat setoran iuran.</p>
                                </div>
                            </div>

                            <div class="space-y-3.5">
                                <!-- 1. Pilih No KK / Kepala Keluarga -->
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">
                                        Pilih Kartu Keluarga (KK) Terdaftar <span class="text-rose-500">*</span>
                                    </label>
                                    <SearchableSelect
                                        v-model="formData.id_kk"
                                        :options="kkList"
                                        valueKey="id"
                                        labelKey="label"
                                        placeholder="-- Cari / Pilih No KK atau Kepala Keluarga --"
                                        searchPlaceholder="Ketik No KK atau nama kepala keluarga..."
                                        icon="fa-address-card"
                                        iconColor="text-amber-600"
                                        @update:modelValue="onIuranKkChange"
                                    />
                                    <input type="hidden" v-model="formData.no_kk" />
                                    <input type="hidden" v-model="formData.nama_kepala" />
                                </div>

                                <!-- 2. Jenis Iuran -->
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">
                                        Jenis Iuran Gerejawi <span class="text-rose-500">*</span>
                                    </label>
                                    <SearchableSelect
                                        v-model="formData.jenis_iuran_id"
                                        :options="jenisIuranList"
                                        valueKey="id"
                                        labelKey="nama_iuran"
                                        placeholder="-- Pilih Jenis Iuran dari Database --"
                                        searchPlaceholder="Ketik cari nama jenis iuran..."
                                        icon="fa-coins"
                                        iconColor="text-emerald-600"
                                        @update:modelValue="onIuranJenisChange"
                                    />
                                    <input type="hidden" v-model="formData.nama_iuran" />
                                </div>

                                <!-- 3. Tahun & Bulan -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-700 mb-1">
                                            Tahun <span class="text-rose-500">*</span>
                                        </label>
                                        <select
                                            v-model="formData.tahun"
                                            class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-bold text-slate-900 focus:outline-none focus:border-amber-500"
                                        >
                                            <option v-for="t in tahunOptions" :key="t.value" :value="t.value">{{ t.label }}</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-700 mb-1">
                                            Bulan Lunas <span class="text-rose-500">*</span>
                                        </label>
                                        <select
                                            v-model="formData.bulan_lunas"
                                            @change="formData.bulan = formData.bulan_lunas"
                                            class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-semibold text-slate-900 focus:outline-none focus:border-amber-500"
                                        >
                                            <option value="">-- Pilih Bulan --</option>
                                            <option v-for="b in bulanOptions" :key="b.value" :value="b.value">{{ b.label }}</option>
                                        </select>
                                        <input type="hidden" v-model="formData.bulan" />
                                    </div>
                                </div>

                                <!-- 4. Total Bayar & Status -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-700 mb-1">
                                            Total Bayar (Rp) <span class="text-rose-500">*</span>
                                        </label>
                                        <div class="relative">
                                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 font-bold text-xs">Rp</span>
                                            <input
                                                v-model="formData.total_jumlah"
                                                type="number"
                                                placeholder="Contoh: 50000"
                                                required
                                                class="w-full pl-10 pr-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-bold text-slate-900 focus:outline-none focus:border-amber-500"
                                            />
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-700 mb-1">
                                            Status Pembayaran <span class="text-rose-500">*</span>
                                        </label>
                                        <select
                                            v-model="formData.status_bayar"
                                            class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-bold text-slate-900 focus:outline-none focus:border-amber-500"
                                        >
                                            <option v-for="st in statusBayarOptions" :key="st.value" :value="st.value">{{ st.label }}</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- 5. Tgl Bayar & Metode Bayar -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-700 mb-1">
                                            Tanggal Bayar <span class="text-rose-500">*</span>
                                        </label>
                                        <DateInput
                                            v-model="formData.tanggal_bayar"
                                            placeholder="dd/mm/yyyy"
                                            required
                                            inputClass="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs font-semibold text-slate-900 focus:outline-none focus:border-amber-500"
                                        />
                                    </div>

                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-700 mb-1">
                                            Metode Pembayaran
                                        </label>
                                        <select
                                            v-model="formData.metode_bayar"
                                            class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs font-semibold text-slate-900 focus:outline-none focus:border-amber-500"
                                        >
                                            <option v-for="m in metodeBayarOptions" :key="m.value" :value="m.value">{{ m.label }}</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- 6. Petugas / Kolektor & Catatan -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-700 mb-1">
                                            Petugas / Kolektor Penerima
                                        </label>
                                        <input
                                            v-model="formData.kolektor"
                                            type="text"
                                            placeholder="Nama petugas / kolektor KUB"
                                            class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500"
                                        />
                                    </div>

                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-700 mb-1">
                                            Keterangan / Catatan
                                        </label>
                                        <input
                                            v-model="formData.keterangan"
                                            type="text"
                                            placeholder="Catatan tambahan (opsional)"
                                            class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- 18. KOLEKTE MISA FORM (Dropdowns from Database: Kategori Misa & Lokasi Gereja) -->
                    <template v-else-if="moduleKey === 'kolekte'">
                        <div class="space-y-4 text-xs">
                            <!-- Header Info Card -->
                            <div class="p-3.5 bg-amber-50/70 rounded-2xl border border-amber-200/80 flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-amber-500 to-amber-600 text-white flex items-center justify-center text-lg shrink-0 shadow-xs">
                                    <i class="fa-solid fa-hand-holding-dollar"></i>
                                </div>
                                <div class="text-xs text-slate-700">
                                    <h5 class="font-bold text-amber-900">Formulir Pencatatan Kolekte Persembahan Misa</h5>
                                    <p class="text-[11px] text-slate-600">Pilih kategori perayaan misa dan lokasi gereja/stasi dari database untuk mencatat perolehan kolekte.</p>
                                </div>
                            </div>

                            <div class="space-y-3.5">
                                <!-- Row 1: Tanggal Misa & Kategori Misa -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-700 mb-1">
                                            Tanggal Misa <span class="text-rose-500">*</span>
                                        </label>
                                        <DateInput
                                            v-model="formData.tanggal"
                                            placeholder="dd/mm/yyyy"
                                            required
                                            inputClass="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-semibold text-slate-900 focus:outline-none focus:border-amber-500"
                                        />
                                    </div>

                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-700 mb-1">
                                            Kategori / Perayaan Misa <span class="text-rose-500">*</span>
                                        </label>
                                        <SearchableSelect
                                            v-model="formData.kategori_misa"
                                            :options="kategoriMisaOptions"
                                            valueKey="value"
                                            labelKey="label"
                                            placeholder="-- Pilih / Cari Kategori Misa --"
                                            searchPlaceholder="Ketik cari perayaan misa..."
                                            icon="fa-cross"
                                            iconColor="text-amber-600"
                                        />
                                    </div>
                                </div>

                                <!-- Row 2: Jumlah Kolekte (Rp) & Lokasi Gereja / Tempat -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-700 mb-1">
                                            Jumlah Kolekte (Rp) <span class="text-rose-500">*</span>
                                        </label>
                                        <div class="relative">
                                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-500 font-bold text-xs">Rp</span>
                                            <input
                                                v-model="formData.nominal"
                                                type="number"
                                                required
                                                min="0"
                                                step="1000"
                                                placeholder="Contoh: 1500000"
                                                class="w-full pl-10 pr-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-bold text-slate-900 focus:outline-none focus:border-amber-500 font-mono"
                                            />
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-700 mb-1">
                                            Gereja / Tempat Misa <span class="text-rose-500">*</span>
                                        </label>
                                        <SearchableSelect
                                            v-model="formData.lokasi_misa"
                                            :options="lokasiMisaOptions"
                                            valueKey="value"
                                            labelKey="label"
                                            placeholder="-- Pilih Gereja / Kapela / Stasi --"
                                            searchPlaceholder="Ketik cari nama gereja/stasi..."
                                            icon="fa-church"
                                            iconColor="text-blue-600"
                                        />
                                    </div>
                                </div>

                                <!-- Row 3: Petugas Penghitung -->
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">
                                        Petugas Penghitung / Kolektor
                                    </label>
                                    <div class="relative">
                                        <i class="fa-solid fa-user-shield absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                                        <input
                                            v-model="formData.petugas_penghitung"
                                            type="text"
                                            placeholder="Contoh: Tim Kolekte DPP, Misdinar, atau Bendahara Stasi..."
                                            class="w-full pl-9 pr-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 font-medium"
                                        />
                                    </div>
                                </div>

                                <!-- Row 4: Keterangan / Catatan Tambahan -->
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">
                                        Keterangan Tambahan
                                    </label>
                                    <textarea
                                        v-model="formData.keterangan"
                                        rows="2"
                                        placeholder="Contoh: Kolekte kantong pertama untuk operasional paroki / persembahan khusus stasi..."
                                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500"
                                    ></textarea>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- 19. KABUPATEN / KOTA FORM -->
                    <template v-else-if="moduleKey === 'kabupaten'">
                        <div class="space-y-4">
                            <div class="p-3.5 bg-blue-50/70 rounded-2xl border border-blue-200/80 flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-blue-500 to-blue-600 text-white flex items-center justify-center text-lg shrink-0 shadow-xs">
                                    <i class="fa-solid fa-city"></i>
                                </div>
                                <div class="text-xs text-slate-700">
                                    <h5 class="font-bold text-blue-900">Formulir Data Kabupaten / Kota</h5>
                                    <p class="text-[11px] text-slate-600">Pilih provinsi terlebih dahulu, lalu masukkan nama dan kode kabupaten/kota.</p>
                                </div>
                            </div>

                            <div class="space-y-3.5">
                                <!-- 1. Pilih Provinsi (Wajib) -->
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">
                                        Provinsi Induk <span class="text-rose-500">*</span>
                                    </label>
                                    <SearchableSelect
                                        v-model="formData.provinsi_id"
                                        :options="provinsiList"
                                        value-key="id_provinsi"
                                        label-key="nama_provinsi"
                                        placeholder="-- Pilih Provinsi --"
                                        search-placeholder="Ketik cari nama provinsi..."
                                        icon="fa-map"
                                        icon-color="text-blue-600"
                                        required
                                    />
                                </div>

                                <!-- 2. Nama Kabupaten & Tipe -->
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-3.5">
                                    <div class="md:col-span-2">
                                        <label class="block text-[11px] font-bold text-slate-700 mb-1">
                                            Nama Kabupaten / Kota <span class="text-rose-500">*</span>
                                        </label>
                                        <input
                                            v-model="formData.nama_kabupaten"
                                            type="text"
                                            required
                                            placeholder="Contoh: Timor Tengah Selatan"
                                            class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-semibold text-slate-900 focus:outline-none focus:border-amber-500"
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-700 mb-1">
                                            Tipe Administrasi
                                        </label>
                                        <select
                                            v-model="formData.tipe"
                                            class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-semibold text-slate-900 focus:outline-none focus:border-amber-500"
                                        >
                                            <option value="Kabupaten">Kabupaten</option>
                                            <option value="Kota">Kota</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- 3. Kode & Status -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-700 mb-1">
                                            Kode Kabupaten / Kemendagri
                                        </label>
                                        <input
                                            v-model="formData.kode_kabupaten"
                                            type="text"
                                            placeholder="Contoh: 53.02"
                                            class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-semibold text-slate-900 focus:outline-none focus:border-amber-500"
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-700 mb-1">
                                            Status Data
                                        </label>
                                        <select
                                            v-model="formData.status"
                                            class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-semibold text-slate-900 focus:outline-none focus:border-amber-500"
                                        >
                                            <option value="Aktif">Aktif</option>
                                            <option value="Nonaktif">Nonaktif</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- 20. KECAMATAN FORM -->
                    <template v-else-if="moduleKey === 'kecamatan'">
                        <div class="space-y-4">
                            <div class="p-3.5 bg-teal-50/70 rounded-2xl border border-teal-200/80 flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-teal-500 to-teal-600 text-white flex items-center justify-center text-lg shrink-0 shadow-xs">
                                    <i class="fa-solid fa-building-columns"></i>
                                </div>
                                <div class="text-xs text-slate-700">
                                    <h5 class="font-bold text-teal-900">Formulir Data Kecamatan</h5>
                                    <p class="text-[11px] text-slate-600">Pilih provinsi & kabupaten/kota induk terlebih dahulu, lalu masukkan nama kecamatan.</p>
                                </div>
                            </div>

                            <div class="space-y-3.5">
                                <!-- 1. Pilih Provinsi (Filter Pembantu) -->
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">
                                        Provinsi (Filter)
                                    </label>
                                    <SearchableSelect
                                        v-model="formData.provinsi_id"
                                        :options="provinsiList"
                                        value-key="id_provinsi"
                                        label-key="nama_provinsi"
                                        placeholder="-- Pilih Provinsi --"
                                        search-placeholder="Ketik cari nama provinsi..."
                                        icon="fa-map"
                                        icon-color="text-blue-600"
                                    />
                                </div>

                                <!-- 2. Pilih Kabupaten / Kota (Wajib) -->
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">
                                        Kabupaten / Kota Induk <span class="text-rose-500">*</span>
                                    </label>
                                    <SearchableSelect
                                        v-model="formData.kabupaten_id"
                                        :options="availableKabupatens"
                                        value-key="id_kabupaten"
                                        label-key="nama_kabupaten"
                                        placeholder="-- Pilih Kabupaten / Kota --"
                                        search-placeholder="Ketik cari kabupaten..."
                                        icon="fa-city"
                                        icon-color="text-emerald-600"
                                        required
                                    />
                                </div>

                                <!-- 3. Nama Kecamatan & Kode -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-700 mb-1">
                                            Nama Kecamatan <span class="text-rose-500">*</span>
                                        </label>
                                        <input
                                            v-model="formData.nama_kecamatan"
                                            type="text"
                                            required
                                            placeholder="Contoh: Batu Putih"
                                            class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-semibold text-slate-900 focus:outline-none focus:border-amber-500"
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-700 mb-1">
                                            Kode Kecamatan / Kemendagri
                                        </label>
                                        <input
                                            v-model="formData.kode_kecamatan"
                                            type="text"
                                            placeholder="Contoh: 53.02.04"
                                            class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-semibold text-slate-900 focus:outline-none focus:border-amber-500"
                                        />
                                    </div>
                                </div>

                                <!-- 4. Status Data -->
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">
                                        Status Data
                                    </label>
                                    <select
                                        v-model="formData.status"
                                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-semibold text-slate-900 focus:outline-none focus:border-amber-500"
                                    >
                                        <option value="Aktif">Aktif</option>
                                        <option value="Nonaktif">Nonaktif</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- 21. DESA / KELURAHAN FORM -->
                    <template v-else-if="moduleKey === 'desa-kelurahan' || moduleKey === 'desa' || moduleKey === 'kelurahan'">
                        <div class="space-y-4">
                            <div class="p-3.5 bg-purple-50/70 rounded-2xl border border-purple-200/80 flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-purple-500 to-purple-600 text-white flex items-center justify-center text-lg shrink-0 shadow-xs">
                                    <i class="fa-solid fa-tree-city"></i>
                                </div>
                                <div class="text-xs text-slate-700">
                                    <h5 class="font-bold text-purple-900">Formulir Data Desa / Kelurahan</h5>
                                    <p class="text-[11px] text-slate-600">Pilih kecamatan induk terlebih dahulu, lalu masukkan nama desa/kelurahan.</p>
                                </div>
                            </div>

                            <div class="space-y-3.5">
                                <!-- 1. Filter Provinsi & Kabupaten -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-700 mb-1">
                                            Provinsi (Filter)
                                        </label>
                                        <SearchableSelect
                                            v-model="formData.provinsi_id"
                                            :options="provinsiList"
                                            value-key="id_provinsi"
                                            label-key="nama_provinsi"
                                            placeholder="-- Pilih Provinsi --"
                                            search-placeholder="Ketik cari provinsi..."
                                            icon="fa-map"
                                            icon-color="text-blue-600"
                                        />
                                    </div>

                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-700 mb-1">
                                            Kabupaten / Kota (Filter)
                                        </label>
                                        <SearchableSelect
                                            v-model="formData.kabupaten_id"
                                            :options="availableKabupatens"
                                            value-key="id_kabupaten"
                                            label-key="nama_kabupaten"
                                            placeholder="-- Pilih Kabupaten / Kota --"
                                            search-placeholder="Ketik cari kabupaten..."
                                            icon="fa-city"
                                            icon-color="text-emerald-600"
                                        />
                                    </div>
                                </div>

                                <!-- 2. Pilih Kecamatan (Wajib) -->
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">
                                        Kecamatan Induk <span class="text-rose-500">*</span>
                                    </label>
                                    <SearchableSelect
                                        v-model="formData.kecamatan_id"
                                        :options="availableKecamatans"
                                        value-key="id_kecamatan"
                                        label-key="nama_kecamatan"
                                        placeholder="-- Pilih Kecamatan --"
                                        search-placeholder="Ketik cari kecamatan..."
                                        icon="fa-building-columns"
                                        icon-color="text-teal-600"
                                        required
                                    />
                                </div>

                                <!-- 3. Nama Desa & Tipe -->
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-3.5">
                                    <div class="md:col-span-2">
                                        <label class="block text-[11px] font-bold text-slate-700 mb-1">
                                            Nama Desa / Kelurahan <span class="text-rose-500">*</span>
                                        </label>
                                        <input
                                            v-model="formData.nama_desa"
                                            type="text"
                                            required
                                            placeholder="Contoh: Benlutu"
                                            class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-semibold text-slate-900 focus:outline-none focus:border-amber-500"
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-700 mb-1">
                                            Tipe Wilayah
                                        </label>
                                        <select
                                            v-model="formData.tipe"
                                            class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-semibold text-slate-900 focus:outline-none focus:border-amber-500"
                                        >
                                            <option value="Desa">Desa</option>
                                            <option value="Kelurahan">Kelurahan</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- 4. Kode & Kode Pos -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-700 mb-1">
                                            Kode Desa / Kemendagri
                                        </label>
                                        <input
                                            v-model="formData.kode_desa"
                                            type="text"
                                            placeholder="Contoh: 53.02.04.2001"
                                            class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-semibold text-slate-900 focus:outline-none focus:border-amber-500"
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-700 mb-1">
                                            Kode Pos
                                        </label>
                                        <input
                                            v-model="formData.kode_pos"
                                            type="text"
                                            placeholder="Contoh: 85561"
                                            class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-semibold text-slate-900 focus:outline-none focus:border-amber-500"
                                        />
                                    </div>
                                </div>

                                <!-- 5. Status Data -->
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">
                                        Status Data
                                    </label>
                                    <select
                                        v-model="formData.status"
                                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-semibold text-slate-900 focus:outline-none focus:border-amber-500"
                                    >
                                        <option value="Aktif">Aktif</option>
                                        <option value="Nonaktif">Nonaktif</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- 22. PROVINSI FORM -->
                    <template v-else-if="moduleKey === 'provinsi'">
                        <div class="space-y-4">
                            <div class="p-3.5 bg-blue-50/70 rounded-2xl border border-blue-200/80 flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-blue-500 to-blue-600 text-white flex items-center justify-center text-lg shrink-0 shadow-xs">
                                    <i class="fa-solid fa-map"></i>
                                </div>
                                <div class="text-xs text-slate-700">
                                    <h5 class="font-bold text-blue-900">Formulir Data Provinsi</h5>
                                    <p class="text-[11px] text-slate-600">Masukkan nama provinsi dan kode wilayah provinsi.</p>
                                </div>
                            </div>

                            <div class="space-y-3.5">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">
                                        Nama Provinsi <span class="text-rose-500">*</span>
                                    </label>
                                    <input
                                        v-model="formData.nama_provinsi"
                                        type="text"
                                        required
                                        placeholder="Contoh: Nusa Tenggara Timur"
                                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-semibold text-slate-900 focus:outline-none focus:border-amber-500"
                                    />
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-700 mb-1">
                                            Kode Provinsi / Kemendagri
                                        </label>
                                        <input
                                            v-model="formData.kode_provinsi"
                                            type="text"
                                            placeholder="Contoh: 53"
                                            class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-semibold text-slate-900 focus:outline-none focus:border-amber-500"
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-700 mb-1">
                                            Status Data
                                        </label>
                                        <select
                                            v-model="formData.status"
                                            class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-semibold text-slate-900 focus:outline-none focus:border-amber-500"
                                        >
                                            <option value="Aktif">Aktif</option>
                                            <option value="Nonaktif">Nonaktif</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- 23. GENERIC FORM FOR OTHER MODULES -->
                    <template v-else>
                        <template v-for="col in columns" :key="col.key">
                            <div v-if="col.isRelationLink || col.key === 'desas' || col.key === 'kecamatans' || col.key === 'kabupatens' || col.key === 'dekenats' || col.key === 'parokis' || col.key === 'kubs' || col.key === 'wilayahs'" class="hidden">
                                <!-- Hide relation link columns from form inputs -->
                            </div>
                            <div v-else-if="col.isImage || col.key === 'logo' || col.key === 'foto'" class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-2.5">
                                <label class="block text-xs font-bold text-slate-700">{{ col.label }}</label>
                                <div class="flex items-center gap-4">
                                    <div class="w-18 h-18 rounded-2xl bg-white border border-slate-200 p-1.5 shadow-2xs flex items-center justify-center overflow-hidden shrink-0">
                                        <img
                                            v-if="previewImage || (formData[col.key] && typeof formData[col.key] === 'string') || ['umat', 'data-umat', 'jiwa', 'kk-katolik', 'kk', 'keluarga'].includes(moduleKey) || col.key === 'foto'"
                                            :src="previewImage || (formData[col.key] ? getImageUrl(formData[col.key]) : getDefaultAvatarByGender(formData.jenis_kelamin, formData.usia ?? formData.umur ?? formData.tanggal_lahir ?? formData.tgl_lahir))"
                                            :alt="col.label"
                                            class="w-full h-full object-cover"
                                            @error="(e) => handleImageError(e, formData, col.key)"
                                        />
                                        <i v-else class="fa-solid fa-cloud-arrow-up text-2xl text-slate-300"></i>
                                    </div>
                                    <div class="space-y-1.5 flex-1">
                                        <input
                                            type="file"
                                            :id="`upload-${col.key}`"
                                            accept="image/png, image/jpeg, image/jpg, image/svg+xml, image/webp"
                                            @change="handleFileUpload($event, col.key)"
                                            class="hidden"
                                        />
                                        <label
                                            :for="`upload-${col.key}`"
                                            class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 text-xs font-bold transition cursor-pointer shadow-2xs"
                                        >
                                            <i class="fa-solid fa-cloud-arrow-up text-amber-600 text-sm"></i>
                                            <span>{{ (previewImage || formData[col.key]) ? 'Ganti Logo / Gambar' : 'Pilih File Logo' }}</span>
                                        </label>
                                        <p class="text-[10px] text-slate-400">
                                            Format didukung: PNG, JPG, JPEG, SVG, WebP (Maks. 2MB)
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div v-else>
                                <label class="block text-xs font-bold text-slate-700 mb-1">{{ col.label }}</label>
                                <SearchableSelect
                                    v-if="col.key === 'tipe_sakramen' || col.key === 'sakramen' || col.key === 'kategori_sakramen'"
                                    v-model="formData[col.key]"
                                    :options="tipeSakramenOptions"
                                    valueKey="value"
                                    labelKey="label"
                                    placeholder="-- Pilih Tipe Sakramen Suci --"
                                    searchPlaceholder="Ketik cari tipe sakramen..."
                                    icon="fa-cross"
                                    iconColor="text-amber-600"
                                />
                                <SearchableSelect
                                    v-else-if="col.relation === 'provinsi' || col.key === 'provinsi_id' || col.key === 'provinsi_nama'"
                                    v-model="formData.provinsi_id"
                                    :options="provinsiList"
                                    value-key="id_provinsi"
                                    label-key="nama_provinsi"
                                    placeholder="-- Pilih Provinsi --"
                                    search-placeholder="Ketik cari provinsi..."
                                    icon="fa-map"
                                    icon-color="text-blue-600"
                                />
                                <SearchableSelect
                                    v-else-if="col.relation === 'kabupaten' || col.key === 'kabupaten_id' || col.key === 'kabupaten_nama'"
                                    v-model="formData.kabupaten_id"
                                    :options="availableKabupatens"
                                    value-key="id_kabupaten"
                                    label-key="nama_kabupaten"
                                    placeholder="-- Pilih Kabupaten / Kota --"
                                    search-placeholder="Ketik cari kabupaten..."
                                    icon="fa-city"
                                    icon-color="text-emerald-600"
                                />
                                <SearchableSelect
                                    v-else-if="col.relation === 'kecamatan' || col.key === 'kecamatan_id' || col.key === 'kecamatan_nama'"
                                    v-model="formData.kecamatan_id"
                                    :options="availableKecamatans"
                                    value-key="id_kecamatan"
                                    label-key="nama_kecamatan"
                                    placeholder="-- Pilih Kecamatan --"
                                    search-placeholder="Ketik cari kecamatan..."
                                    icon="fa-building-columns"
                                    icon-color="text-teal-600"
                                />
                                <SearchableSelect
                                    v-else-if="col.relation === 'desa' || col.key === 'desa_id' || col.key === 'desa_nama'"
                                    v-model="formData.desa_id"
                                    :options="availableDesas"
                                    value-key="id_desa"
                                    label-key="nama_desa"
                                    placeholder="-- Pilih Desa / Kelurahan --"
                                    search-placeholder="Ketik cari desa..."
                                    icon="fa-tree-city"
                                    icon-color="text-purple-600"
                                />
                                <SearchableSelect
                                    v-else-if="col.relation === 'dekenat' || col.relation === 'kevikepan' || col.key === 'dekenat_id' || col.key === 'dekenat_nama'"
                                    v-model="formData.dekenat_id"
                                    :options="availableDekenats"
                                    value-key="id_dekenat"
                                    label-key="nama_dekenat"
                                    placeholder="-- Pilih Kevikepan / Dekenat --"
                                    search-placeholder="Ketik cari dekenat..."
                                    icon="fa-layer-group"
                                    icon-color="text-indigo-600"
                                />
                                <SearchableSelect
                                    v-else-if="col.relation === 'keuskupan' || col.key === 'keuskupan_id' || col.key === 'keuskupan_nama'"
                                    v-model="formData.keuskupan_id"
                                    :options="keuskupanList"
                                    value-key="id_keuskupan"
                                    label-key="nama_keuskupan"
                                    placeholder="-- Pilih Keuskupan --"
                                    search-placeholder="Ketik cari keuskupan..."
                                    icon="fa-church"
                                    icon-color="text-amber-600"
                                />
                                <!-- Stasi / Kapela Dropdown from database -->
                                <SearchableSelect
                                    v-else-if="col.key === 'stasi' || col.key === 'kapela' || col.key === 'kapela_id' || col.key === 'stasi_kapela' || col.key === 'wilayah_pelayanan'"
                                    v-model="formData[col.key]"
                                    :options="kapelaList"
                                    value-key="nama_kapela"
                                    label-key="nama_kapela"
                                    placeholder="-- Pilih Stasi / Kapela --"
                                    search-placeholder="Ketik cari stasi / kapela..."
                                    icon="fa-place-of-worship"
                                    icon-color="text-amber-600"
                                />
                                <!-- Wilayah Dropdown from database -->
                                <SearchableSelect
                                    v-else-if="col.key === 'wilayah' || col.key === 'wilayah_id'"
                                    v-model="formData[col.key]"
                                    :options="wilayahList"
                                    value-key="nama_wilayah"
                                    label-key="nama_wilayah"
                                    placeholder="-- Pilih Wilayah Pastoral --"
                                    search-placeholder="Ketik cari wilayah..."
                                    icon="fa-map-location-dot"
                                    icon-color="text-emerald-600"
                                />
                                <!-- KUB Dropdown from database -->
                                <SearchableSelect
                                    v-else-if="col.key === 'kub' || col.key === 'kub_id'"
                                    v-model="formData[col.key]"
                                    :options="kubList"
                                    value-key="nama_kub"
                                    label-key="nama_kub"
                                    placeholder="-- Pilih KUB / KBG --"
                                    search-placeholder="Ketik cari KUB..."
                                    icon="fa-people-roof"
                                    icon-color="text-indigo-600"
                                />
                                <!-- Paroki Dropdown from database -->
                                <SearchableSelect
                                    v-else-if="col.key === 'paroki' || col.key === 'paroki_id'"
                                    v-model="formData[col.key]"
                                    :options="parokiList"
                                    value-key="id_paroki"
                                    label-key="nama_paroki"
                                    placeholder="-- Pilih Paroki --"
                                    search-placeholder="Ketik cari paroki..."
                                    icon="fa-church"
                                    icon-color="text-blue-600"
                                />
                                <!-- Enum dropdown detection (e.g. jenis_tugas) -->
                                <select
                                    v-else-if="col.enumOptions && col.enumOptions.length"
                                    v-model="formData[col.key]"
                                    class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs font-semibold text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition"
                                >
                                    <option value="" disabled>Pilih {{ col.label }}...</option>
                                    <option v-for="opt in col.enumOptions" :key="opt" :value="opt">{{ opt }}</option>
                                </select>
                                <!-- Date input detection -->
                                <DateInput
                                    v-else-if="col.isDate || col.key.includes('tanggal') || col.key.includes('tgl') || col.key.endsWith('_at')"
                                    v-model="formData[col.key]"
                                    placeholder="dd/mm/yyyy"
                                    inputClass="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs font-semibold text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition font-medium"
                                />
                                <!-- Time input detection -->
                                <input
                                    v-else-if="col.isTime || col.key.includes('waktu') || col.key.includes('jam')"
                                    v-model="formData[col.key]"
                                    type="time"
                                    class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs font-semibold text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition"
                                />
                                <!-- Status dropdown detection -->
                                <select
                                    v-else-if="col.key === 'status' || col.key === 'status_aktif'"
                                    v-model="formData[col.key]"
                                    class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition font-semibold"
                                >
                                    <option value="Aktif">Aktif</option>
                                    <option value="Nonaktif">Nonaktif</option>
                                    <option value="Terlaksana">Terlaksana</option>
                                    <option value="Ditunda">Ditunda</option>
                                </select>
                                <!-- Rich Text Editor for long content -->
                                <RichTextEditor
                                    v-else-if="col.key === 'isi' || col.key === 'konten' || col.key === 'deskripsi' || col.key === 'keterangan' || col.key === 'notulen' || col.key === 'catatan'"
                                    v-model="formData[col.key]"
                                    :placeholder="`Tuliskan ${col.label.toLowerCase()} secara lengkap...`"
                                    min-height="180"
                                />
                                <input
                                    v-else
                                    v-model="formData[col.key]"
                                    type="text"
                                    :placeholder="`Masukkan ${col.label.toLowerCase()}...`"
                                    class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition"
                                />
                            </div>
                        </template>
                    </template>

                    <!-- Form Buttons -->
                    <div class="pt-3 flex items-center justify-end gap-2.5 sticky bottom-0 bg-white py-2 border-t border-slate-100">
                        <button
                            type="button"
                            @click="showFormModal = false"
                            class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition cursor-pointer"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            :disabled="isSubmitting"
                            class="px-5 py-2 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white text-xs font-bold shadow-sm shadow-amber-500/25 transition cursor-pointer flex items-center gap-2"
                        >
                            <i v-if="isSubmitting" class="fa-solid fa-circle-notch fa-spin text-xs"></i>
                            <span>{{ modalMode === 'create' ? 'Simpan Data' : 'Simpan Perubahan' }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- DELETE CONFIRMATION MODAL -->
        <div
            v-if="showDeleteModal && selectedItem"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/40 backdrop-blur-xs"
        >
            <div class="bg-white rounded-3xl p-6 max-w-md w-full shadow-2xl border border-slate-200 text-center space-y-4 animate-in fade-in zoom-in-95">
                <div :class="['w-12 h-12 rounded-2xl mx-auto flex items-center justify-center text-xl border', (['kategori-konten', 'kategori_konten'].includes(moduleKey) && Number(selectedItem.total_konten) > 0) ? 'bg-amber-50 text-amber-600 border-amber-200' : 'bg-rose-50 text-rose-600 border-rose-200']">
                    <i :class="['fa-solid', (['kategori-konten', 'kategori_konten'].includes(moduleKey) && Number(selectedItem.total_konten) > 0) ? 'fa-shield-halved' : 'fa-triangle-exclamation']"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900">
                        {{ (['kategori-konten', 'kategori_konten'].includes(moduleKey) && Number(selectedItem.total_konten) > 0) ? 'Kategori Memiliki Berita Terkait' : 'Konfirmasi Hapus Data' }}
                    </h3>
                    <div v-if="['kategori-konten', 'kategori_konten'].includes(moduleKey) && Number(selectedItem.total_konten) > 0" class="text-xs text-amber-900 bg-amber-50/90 p-3.5 rounded-2xl border border-amber-200 mt-2.5 text-left leading-relaxed space-y-1.5">
                        <div class="font-bold flex items-center gap-1.5 text-amber-800">
                            <i class="fa-solid fa-circle-exclamation text-amber-600"></i>
                            <span>Tidak Dapat Dihapus Langsung</span>
                        </div>
                        <p class="text-[11.5px] text-amber-800/90">
                            Kategori <b>"{{ selectedItem.nama_kategori || selectedItem.kategori }}"</b> masih memuat <b>{{ selectedItem.total_konten }}</b> artikel/berita aktif. Silakan hapus atau pindahkan konten di dalamnya ke kategori lain terlebih dahulu.
                        </p>
                    </div>
                    <p v-else class="text-xs text-slate-500 mt-1">
                        Apakah Anda yakin ingin menghapus data <b>{{ selectedItem[columns[0]?.key] || 'ini' }}</b>? Tindakan ini tidak dapat dibatalkan.
                    </p>
                </div>
                <div class="flex items-center justify-center gap-2.5 pt-2">
                    <button
                        type="button"
                        @click="showDeleteModal = false"
                        class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition cursor-pointer"
                    >
                        {{ (['kategori-konten', 'kategori_konten'].includes(moduleKey) && Number(selectedItem.total_konten) > 0) ? 'Tutup' : 'Batal' }}
                    </button>
                    <button
                        v-if="!(['kategori-konten', 'kategori_konten'].includes(moduleKey) && Number(selectedItem.total_konten) > 0)"
                        type="button"
                        @click="confirmDelete"
                        class="px-5 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold shadow-sm shadow-rose-600/30 transition cursor-pointer"
                    >
                        Ya, Hapus Data
                    </button>
                </div>
            </div>
        </div>
        <!-- end delete modal -->

        <!-- BULK DELETE CONFIRMATION MODAL -->
        <div
            v-if="showBulkDeleteModal && selectedIds.length > 0"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/40 backdrop-blur-xs"
        >
            <div class="bg-white rounded-3xl p-6 max-w-md w-full shadow-2xl border border-slate-200 text-center space-y-4 animate-in fade-in zoom-in-95">
                <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 border border-rose-200 mx-auto flex items-center justify-center text-xl">
                    <i class="fa-solid fa-trash-can"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Konfirmasi Hapus Massal (Bulk Delete)</h3>
                    <p class="text-xs text-slate-500 mt-1">
                        Apakah Anda yakin ingin menghapus <b>{{ selectedIds.length }}</b> data {{ title.toLowerCase() }} yang dipilih secara massal?
                    </p>
                    <p v-if="['kategori-konten', 'kategori_konten'].includes(moduleKey)" class="text-[11px] text-amber-700 bg-amber-50 p-2.5 rounded-xl border border-amber-200 mt-2 text-left">
                        <i class="fa-solid fa-circle-info me-1"></i> Catatan: Kategori yang masih memiliki berita/konten aktif akan otomatis dilindungi dari penghapusan.
                    </p>
                </div>
                <div class="flex items-center justify-center gap-2.5 pt-2">
                    <button
                        type="button"
                        @click="showBulkDeleteModal = false"
                        class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition cursor-pointer"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        :disabled="isBulkDeleting"
                        @click="confirmBulkDelete"
                        class="px-5 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold shadow-sm shadow-rose-600/30 transition cursor-pointer flex items-center gap-2"
                    >
                        <i v-if="isBulkDeleting" class="fa-solid fa-circle-notch fa-spin text-xs"></i>
                        <span>Ya, Hapus Semua Terpilih</span>
                    </button>
                </div>
            </div>
        </div>
        <!-- end bulk delete modal -->
        </div>
        <!-- end flex full-height wrapper -->
    </AppLayout>
</template>
