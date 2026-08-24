<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    role: { type: String, default: 'Super Admin' },
    prefix: { type: String, default: 'superadmin' },
    roleItem: { type: Object, default: () => null },
    isEdit: { type: Boolean, default: false },
});

const form = useForm({
    nama_role: props.roleItem?.nama_role || '',
    slug: props.roleItem?.slug || '',
    deskripsi: props.roleItem?.deskripsi || '',
    status: props.roleItem ? (props.roleItem.status ? 1 : 0) : 1,
    permissions: Array.isArray(props.roleItem?.permissions)
        ? props.roleItem.permissions
        : (typeof props.roleItem?.permissions === 'string'
            ? JSON.parse(props.roleItem.permissions || '[]')
            : ['dashboard', 'panduan']),
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
    const groupKeys = group.items.map(i => i.key);
    const allSelected = groupKeys.every(k => form.permissions.includes(k));
    if (allSelected) {
        form.permissions = form.permissions.filter(k => !groupKeys.includes(k));
    } else {
        form.permissions = Array.from(new Set([...form.permissions, ...groupKeys]));
    }
};

const isGroupAllSelected = (group) => {
    return group.items.every(i => form.permissions.includes(i.key));
};

const selectAllAllPermissions = () => {
    const all = [];
    permissionGroups.forEach(g => g.items.forEach(i => all.push(i.key)));
    form.permissions = all;
};

const clearAllPermissions = () => {
    form.permissions = [];
};

const totalPermissionsCount = computed(() => {
    return form.permissions?.length || 0;
});

const basePrefix = computed(() => {
    if (props.prefix) return '/' + props.prefix.replace(/^\//, '');
    const parts = window.location.pathname.split('/').filter(Boolean);
    return parts.length > 0 ? '/' + parts[0] : '/superadmin';
});

const submit = () => {
    if (props.isEdit && props.roleItem) {
        const id = props.roleItem.id || props.roleItem.id_role || props.roleItem.slug;
        form.put(`${basePrefix.value}/role/${id}`);
    } else {
        form.post(`${basePrefix.value}/role`);
    }
};
</script>

<template>
    <AppLayout>
        <Head :title="`${isEdit ? 'Edit Role: ' + form.nama_role : 'Tambah Role Baru'} - SIPAROKI`" />

        <div class="w-full pb-20 pt-1">
            <form @submit.prevent="submit" class="space-y-6">
                <!-- 1. TOP SECTION: NAMA ROLE & DESKRIPSI (2 Columns like Katedral) -->
                <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-2xs">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-2">
                                Nama Role <span class="text-rose-500">*</span>
                            </label>
                            <input
                                v-model="form.nama_role"
                                type="text"
                                placeholder="Admin Paroki"
                                required
                                class="w-full px-4 py-2.5 rounded-xl bg-slate-50/70 border border-slate-200 text-xs sm:text-sm text-slate-900 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition font-bold"
                            />
                            <p class="text-[11px] text-slate-400 mt-1.5 font-medium">
                                Gunakan huruf kecil dan underscore. Contoh: <code>admin_paroki</code>
                            </p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-2">
                                Deskripsi <span class="text-rose-500">*</span>
                            </label>
                            <textarea
                                v-model="form.deskripsi"
                                rows="2"
                                placeholder="Hak akses untuk operasional paroki"
                                required
                                class="w-full px-4 py-2 rounded-xl bg-slate-50/70 border border-slate-200 text-xs sm:text-sm text-slate-900 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition font-medium"
                            ></textarea>
                        </div>
                    </div>
                </div>

                <!-- 2. PERMISSIONS SECTION (2 Columns Grid Cards like Katedral) -->
                <div class="space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 px-1">
                        <div>
                            <h3 class="text-sm sm:text-base font-bold text-slate-900">
                                Hak Akses & Izin Menu (Permissions)
                            </h3>
                            <p class="text-xs text-slate-400 mt-0.5">
                                Centang menu menu yang dapat diakses oleh role ini.
                            </p>
                        </div>

                        <div class="flex items-center gap-2 shrink-0">
                            <button
                                type="button"
                                @click="selectAllAllPermissions"
                                class="px-3.5 py-1.5 rounded-lg border border-blue-500 text-blue-600 bg-white hover:bg-blue-50 text-xs font-bold transition flex items-center gap-1.5 cursor-pointer"
                            >
                                <i class="fa-solid fa-check text-[11px]"></i>
                                <span>Pilih Semua</span>
                            </button>
                            <button
                                type="button"
                                @click="clearAllPermissions"
                                class="px-3.5 py-1.5 rounded-lg border border-purple-400 text-purple-600 bg-white hover:bg-purple-50 text-xs font-bold transition flex items-center gap-1.5 cursor-pointer"
                            >
                                <i class="fa-solid fa-xmark text-[11px]"></i>
                                <span>Kosongkan</span>
                            </button>
                        </div>
                    </div>

                    <!-- 8 CATEGORY GROUPS (2-Column Grid matching Katedral exactly) -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                        <div
                            v-for="group in permissionGroups"
                            :key="group.title"
                            class="rounded-2xl bg-white border border-slate-200/90 p-5 shadow-2xs space-y-4"
                        >
                            <!-- Group Header: Icon + Title + Pilih / Batal Link -->
                            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                                <span class="text-xs sm:text-sm font-bold text-slate-800 flex items-center gap-2">
                                    <i :class="['fa-solid text-sky-500 text-xs', group.icon]"></i>
                                    <span>{{ group.title }}</span>
                                </span>
                                <button
                                    type="button"
                                    @click="toggleGroupPermissions(group)"
                                    class="text-xs font-semibold text-blue-600 hover:text-blue-700 underline cursor-pointer"
                                >
                                    Pilih / Batal
                                </button>
                            </div>

                            <!-- Group Checkboxes: 2 Columns inside each card -->
                            <div class="grid grid-cols-2 gap-x-4 gap-y-3.5 pt-1">
                                <label
                                    v-for="item in group.items"
                                    :key="item.key"
                                    class="flex items-center gap-2.5 text-xs text-slate-800 cursor-pointer select-none font-medium hover:text-slate-950 transition"
                                >
                                    <input
                                        type="checkbox"
                                        :value="item.key"
                                        v-model="form.permissions"
                                        class="w-4 h-4 rounded text-blue-600 focus:ring-blue-500 border-slate-300"
                                    />
                                    <span class="truncate">{{ item.label }}</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3. FOOTER ACTION BUTTONS (Bottom Right like Katedral) -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200">
                    <Link
                        :href="`${basePrefix}/role`"
                        class="px-5 py-2.5 rounded-xl border border-purple-400 text-purple-600 bg-white hover:bg-purple-50 text-xs font-bold transition flex items-center gap-1.5 cursor-pointer shadow-2xs"
                    >
                        <i class="fa-solid fa-xmark"></i>
                        <span>Batal</span>
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-md shadow-blue-600/20 transition cursor-pointer flex items-center gap-2"
                    >
                        <i v-if="form.processing" class="fa-solid fa-spinner fa-spin"></i>
                        <i v-else class="fa-solid fa-floppy-disk"></i>
                        <span>Simpan Perubahan</span>
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
