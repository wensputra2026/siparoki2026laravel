<script setup>
import { ref, computed, watch } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import RemoteSelect from '@/Components/RemoteSelect.vue';

const props = defineProps({
    type: { type: String, required: true },
    label: { type: String, required: true },
    fields: { type: Array, default: () => [] },
    tableColumns: { type: Array, default: () => [] },
    fkOptions: { type: Object, default: () => ({}) },
    rows: { type: Object, default: () => ({ data: [] }) },
    parentFk: { type: String, default: null },
    grup: { type: [String, Number], default: null },
});

const search = ref('');
const perPage = ref(15);
let debounce = null;

const applyFilters = () => {
    const q = { search: search.value || undefined, per_page: perPage.value };
    if (props.parentFk && props.grup) {
        q.grup = props.grup;
    }
    router.get(window.location.pathname, q, { preserveState: true, preserveScroll: true, replace: true });
};

watch(search, () => {
    clearTimeout(debounce);
    debounce = setTimeout(applyFilters, 250);
});
watch(perPage, () => applyFilters());

const isReloading = ref(false);
const reloadCrudTable = () => {
    isReloading.value = true;
    router.reload({
        only: ['rows'],
        preserveScroll: true,
        preserveState: true,
        onFinish: () => {
            isReloading.value = false;
        },
    });
};

const showFormModal = ref(false);
const showDeleteModal = ref(false);
const modalMode = ref('create');
const selectedItem = ref(null);
const formData = ref({});
const filePreviews = ref({});
const isSubmitting = ref(false);

const optionsForField = (field) => {
    return field.options || [];
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

const handleFileChange = (field, e) => {
    const file = e.target.files[0];
    if (file) {
        formData.value[field.name] = file;
        filePreviews.value[field.name] = URL.createObjectURL(file);
    }
};

const removeFile = (field) => {
    formData.value[field.name] = '';
    filePreviews.value[field.name] = '';
};

const openCreateModal = () => {
    if (props.type === 'pastor' || props.type === 'master_pastor' || props.type === 'master-pastor') {
        const isMasterRef = window.location.pathname.includes('/master-referensi');
        const prefix = window.location.pathname.split('/')[1] || 'superadmin';
        if (isMasterRef) {
            router.visit('/admin/master-referensi/pastor/create');
        } else {
            router.visit(`/${prefix}/master-pastor/create`);
        }
        return;
    }
    modalMode.value = 'create';
    selectedItem.value = null;
    filePreviews.value = {};
    const init = {};
    props.fields.forEach((f) => {
        if (f.type === 'select') {
            if (f.relTable) {
                init[f.name] = '';
            } else {
                const opts = optionsForField(f);
                init[f.name] = opts.length ? opts[0].value : '';
            }
        } else {
            init[f.name] = '';
        }
        if (f.name === 'status') {
            const opts = optionsForField(f);
            init[f.name] = opts.length ? opts[0].value : 1;
        }
    });
    if (props.parentFk && props.grup) {
        init[props.parentFk] = props.grup;
    }
    formData.value = init;
    showFormModal.value = true;
};

const openEditModal = (item) => {
    if (props.type === 'pastor' || props.type === 'master_pastor' || props.type === 'master-pastor') {
        const isMasterRef = window.location.pathname.includes('/master-referensi');
        const prefix = window.location.pathname.split('/')[1] || 'superadmin';
        const pastorId = item._pk || item.id;
        if (isMasterRef) {
            router.visit(`/admin/master-referensi/pastor/edit/${pastorId}`);
        } else {
            router.visit(`/${prefix}/master-pastor/${pastorId}/edit`);
        }
        return;
    }
    modalMode.value = 'edit';
    selectedItem.value = item;
    filePreviews.value = {};
    const init = {};
    props.fields.forEach((f) => {
        let v = item[f.name];
        if (f.type === 'select' && v !== null && v !== undefined && v !== '') {
            v = v;
        }
        if (f.type === 'file' && v && typeof v === 'string') {
            filePreviews.value[f.name] = v;
        }
        init[f.name] = v ?? '';
    });
    formData.value = init;
    showFormModal.value = true;
};

const submitForm = () => {
    isSubmitting.value = true;
    const currentPath = window.location.pathname.replace(/\/$/, '');
    const itemId = selectedItem.value ? selectedItem.value._pk : null;
    const url = modalMode.value === 'create' ? currentPath : `${currentPath}/${itemId}`;

    const payload = new FormData();
    Object.keys(formData.value).forEach((key) => {
        const val = formData.value[key];
        if (val !== null && val !== undefined && val !== '') {
            payload.append(key, val);
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
        },
        onError: () => {
            isSubmitting.value = false;
        },
    });
};

const openDeleteModal = (item) => {
    selectedItem.value = item;
    showDeleteModal.value = true;
};

const confirmDelete = () => {
    if (!selectedItem.value) return;
    const currentPath = window.location.pathname.replace(/\/$/, '');
    router.delete(`${currentPath}/${selectedItem.value._pk}`, {
        preserveScroll: true,
        onSuccess: () => {
            showDeleteModal.value = false;
        },
    });
};

const statusText = (val) => {
    if (val === 0 || val === 'Nonaktif' || val === false) return 'Nonaktif';
    if (val === 1 || val === 'Aktif' || val === true) return 'Aktif';
    return val ?? '—';
};
const statusIsActive = (val) => !(val === 0 || val === 'Nonaktif' || val === false);

const cellValue = (item, col) => {
    if (col.fk) {
        return item[col.name + '_label'] ?? item[col.name] ?? '—';
    }
    const v = item[col.name];
    if (v === null || v === undefined || v === '') return '—';
    return v;
};

const displayedColumns = computed(() => {
    return (props.tableColumns || []).filter(c => c.name !== 'status' && c.name !== 'uuid');
});
</script>

<template>
    <div class="flex flex-col h-full min-h-0 gap-2.5">
        <!-- Header Card -->
        <div class="rounded-2xl bg-white border border-slate-200/80 px-4 py-2.5 shadow-2xs shrink-0">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-2.5">
                <div class="space-y-0.5 shrink-0">
                    <div class="flex items-center gap-2.5">
                        <Link
                            href="/admin/master-referensi"
                            class="w-8 h-8 rounded-xl bg-slate-100 hover:bg-amber-50 hover:text-amber-700 text-slate-600 flex items-center justify-center text-xs transition border border-slate-200 shadow-2xs"
                            title="Kembali ke Master Referensi"
                        >
                            <i class="fa-solid fa-arrow-left"></i>
                        </Link>
                        <h1 class="text-base font-black text-slate-900 tracking-tight">{{ label }}</h1>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200">
                            {{ rows.total || 0 }} Data
                        </span>
                    </div>
                    <p class="text-[11px] text-slate-500 hidden sm:block">Kelola data master {{ label.toLowerCase() }} secara terintegrasi.</p>
                </div>

                <div class="flex items-center gap-2 flex-wrap sm:flex-nowrap justify-end">
                    <Link
                        href="/admin/master-referensi"
                        class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition flex items-center gap-1.5 border border-slate-200 shadow-2xs shrink-0"
                    >
                        <i class="fa-solid fa-arrow-left text-xs"></i>
                        <span>Kembali</span>
                    </Link>

                    <div class="relative w-40 sm:w-48 shrink-0">
                        <i class="fa-solid fa-magnifying-glass absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Cari..."
                            class="w-full pl-7 pr-3 py-1.5 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 placeholder-slate-400 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition shadow-2xs"
                        />
                    </div>

                    <select
                        v-model="perPage"
                        class="px-2 py-1.5 rounded-xl bg-slate-50 hover:bg-slate-100 border border-slate-200 text-slate-700 text-xs font-semibold focus:outline-none focus:border-amber-500 transition cursor-pointer shadow-2xs shrink-0"
                    >
                        <option :value="10">10 / hal</option>
                        <option :value="15">15 / hal</option>
                        <option :value="25">25 / hal</option>
                        <option :value="50">50 / hal</option>
                    </select>

                    <button
                        type="button"
                        :disabled="isReloading"
                        @click="reloadCrudTable"
                        title="Reload data tabel dari database"
                        class="px-3 py-1.5 rounded-xl bg-slate-50 hover:bg-slate-100 border border-slate-200 text-slate-700 text-xs font-bold transition flex items-center gap-1.5 cursor-pointer shadow-2xs shrink-0 disabled:opacity-60"
                    >
                        <i :class="['fa-solid fa-arrows-rotate text-xs', isReloading ? 'fa-spin text-amber-600' : 'text-slate-600']"></i>
                        <span>{{ isReloading ? 'Memuat...' : 'Reload' }}</span>
                    </button>

                    <button
                        type="button"
                        @click="openCreateModal"
                        class="px-3 py-1.5 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white text-xs font-bold shadow-sm shadow-amber-500/25 transition-all flex items-center gap-1.5 cursor-pointer shrink-0 whitespace-nowrap"
                    >
                        <i class="fa-solid fa-plus text-[11px]"></i>
                        <span>Tambah {{ label }}</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="flex-1 min-h-0 rounded-2xl bg-white border border-slate-200/80 overflow-hidden shadow-2xs flex flex-col">
            <div class="flex-1 overflow-auto custom-scrollbar">
                <table class="w-full text-left text-xs sm:text-[12.5px] whitespace-nowrap">
                    <thead class="bg-slate-100/90 text-slate-700 uppercase tracking-wider text-[11px] sm:text-[11.5px] font-bold border-b border-slate-200/90 sticky top-0 z-10">
                        <tr>
                            <th class="px-3.5 py-3 w-12 text-center whitespace-nowrap">#</th>
                            <th v-for="col in displayedColumns" :key="col.name" class="px-4 py-3 whitespace-nowrap">
                                {{ col.label }}
                            </th>
                            <th class="px-3.5 py-3 text-center whitespace-nowrap">Status</th>
                            <th class="px-4 py-3 text-right w-28 whitespace-nowrap">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700 text-xs sm:text-[12.5px]">
                        <tr
                            v-for="(item, idx) in rows.data"
                            :key="item._pk || idx"
                            class="hover:bg-slate-50/80 transition-colors group"
                        >
                            <td class="px-3.5 py-3 text-center font-bold text-slate-400 text-xs whitespace-nowrap">
                                {{ (rows.from || 1) + idx }}
                            </td>
                            <td v-for="col in displayedColumns" :key="col.name" class="px-4 py-3 whitespace-nowrap">
                                <div v-if="['foto', 'logo', 'gambar', 'avatar'].includes(col.name) && item[col.name]" class="flex items-center gap-2 whitespace-nowrap">
                                    <img :src="item[col.name]" class="w-8.5 h-8.5 rounded-xl object-cover border border-slate-200 shrink-0 shadow-2xs" />
                                </div>
                                <span v-else-if="col.fk" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200 whitespace-nowrap">
                                    {{ cellValue(item, col) }}
                                </span>
                                <span v-else class="text-slate-700 font-medium text-xs sm:text-[12.5px] whitespace-nowrap">{{ cellValue(item, col) }}</span>
                            </td>
                            <td class="px-3.5 py-3 text-center whitespace-nowrap">
                                <span :class="[
                                    'inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold border whitespace-nowrap',
                                    statusIsActive(item.status)
                                        ? 'bg-emerald-50 text-emerald-700 border-emerald-200'
                                        : 'bg-rose-50 text-rose-700 border-rose-200'
                                ]">
                                    <span :class="['w-1.5 h-1.5 rounded-full shrink-0', statusIsActive(item.status) ? 'bg-emerald-500' : 'bg-rose-500']"></span>
                                    {{ statusText(item.status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1.5 whitespace-nowrap">
                                    <button type="button" @click="openEditModal(item)" title="Ubah"
                                        class="w-7.5 h-7.5 rounded-lg bg-slate-50 hover:bg-blue-50 hover:text-blue-700 text-slate-500 border border-slate-200 flex items-center justify-center transition cursor-pointer shadow-2xs shrink-0">
                                        <i class="fa-solid fa-pen-to-square text-xs"></i>
                                    </button>
                                    <button type="button" @click="openDeleteModal(item)" title="Hapus"
                                        class="w-7.5 h-7.5 rounded-lg bg-slate-50 hover:bg-rose-50 hover:text-rose-700 text-slate-500 border border-slate-200 flex items-center justify-center transition cursor-pointer shadow-2xs shrink-0">
                                        <i class="fa-solid fa-trash-can text-xs"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!rows.data || !rows.data.length">
                            <td :colspan="tableColumns.length + 3" class="px-5 py-8 text-center text-slate-400 whitespace-nowrap">
                                <div class="w-10 h-10 rounded-xl bg-slate-50 text-slate-300 border border-slate-200 mx-auto flex items-center justify-center text-lg mb-2">
                                    <i class="fa-solid fa-folder-open"></i>
                                </div>
                                <p class="text-xs font-bold text-slate-700">Belum Ada Data</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="rows.links && rows.links.length > 3"
                class="shrink-0 px-4 py-2.5 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-2 bg-slate-50/50">
                <span class="text-xs sm:text-[12.5px] text-slate-600 font-medium">
                    Menampilkan <b class="text-slate-900">{{ rows.from || 0 }}</b> - <b class="text-slate-900">{{ rows.to || 0 }}</b> dari <b class="text-slate-900">{{ rows.total || 0 }}</b>
                </span>
                <div class="flex items-center gap-1">
                    <Link
                        v-for="(link, idx) in rows.links"
                        :key="idx"
                        :href="link.url || '#'"
                        :disabled="!link.url"
                        :class="[
                            'px-3 py-1.5 rounded-lg text-xs font-bold transition',
                            link.active ? 'bg-amber-500 text-white shadow-xs shadow-amber-500/30' : 'bg-white border border-slate-200 text-slate-700 hover:bg-slate-100 hover:text-slate-900',
                            !link.url ? 'opacity-40 cursor-not-allowed pointer-events-none' : ''
                        ]"
                        v-html="formatPaginationLabel(link.label)"
                    />
                </div>
            </div>
        </div>

        <!-- Form Modal -->
        <div v-if="showFormModal"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/40 backdrop-blur-xs overflow-y-auto">
            <div class="bg-white rounded-3xl max-w-2xl w-full shadow-2xl border border-slate-200 overflow-hidden my-8">
                <div class="bg-gradient-to-r from-amber-500 to-amber-600 px-6 py-4 flex items-center justify-between text-white">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center text-sm">
                            <i class="fa-solid" :class="modalMode === 'create' ? 'fa-plus' : 'fa-pen-to-square'"></i>
                        </div>
                        <h3 class="text-base font-bold">{{ modalMode === 'create' ? 'Tambah' : 'Ubah' }} {{ label }}</h3>
                    </div>
                    <button @click="showFormModal = false" class="w-8 h-8 rounded-lg bg-white/10 hover:bg-white/20 flex items-center justify-center transition cursor-pointer">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-4 max-h-[60vh] overflow-y-auto custom-scrollbar">
                    <div v-for="f in fields" :key="f.name" :class="['space-y-1', (f.type === 'textarea' || f.type === 'file') ? 'sm:col-span-2' : '']">
                        <label class="text-[11px] font-bold text-slate-600 uppercase tracking-wide">{{ f.label }}</label>

                        <textarea
                            v-if="f.type === 'textarea'"
                            v-model="formData[f.name]"
                            rows="3"
                            class="w-full px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500"
                        ></textarea>

                        <!-- File / Foto Upload -->
                        <div v-else-if="f.type === 'file'" class="space-y-2">
                            <div class="flex items-center gap-3 p-3 rounded-2xl border border-slate-200 bg-slate-50/50">
                                <div class="w-14 h-14 rounded-2xl border-2 border-dashed border-amber-300 bg-amber-50/60 overflow-hidden flex items-center justify-center shrink-0 shadow-2xs">
                                    <img
                                        v-if="filePreviews[f.name] || (typeof formData[f.name] === 'string' && formData[f.name])"
                                        :src="filePreviews[f.name] || formData[f.name]"
                                        class="w-full h-full object-cover"
                                    />
                                    <i v-else class="fa-solid fa-camera text-amber-500 text-base"></i>
                                </div>
                                <div class="flex-1 min-w-0 space-y-1">
                                    <div class="flex items-center gap-2">
                                        <label class="cursor-pointer inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold transition shadow-2xs">
                                            <i class="fa-solid fa-upload text-[11px]"></i>
                                            <span>Pilih {{ f.label }}</span>
                                            <input
                                                type="file"
                                                :accept="f.accept || 'image/*'"
                                                class="hidden"
                                                @change="handleFileChange(f, $event)"
                                            />
                                        </label>
                                        <button
                                            v-if="filePreviews[f.name] || formData[f.name]"
                                            type="button"
                                            @click="removeFile(f)"
                                            class="w-7 h-7 rounded-xl bg-slate-100 hover:bg-rose-50 hover:text-rose-700 text-slate-500 text-xs font-bold transition flex items-center justify-center cursor-pointer"
                                            title="Hapus / Reset Foto"
                                        >
                                            <i class="fa-solid fa-xmark"></i>
                                        </button>
                                    </div>
                                    <p class="text-[10px] text-slate-400">Format: JPG, PNG, WEBP (Maksimal 2MB).</p>
                                </div>
                            </div>
                        </div>

                        <RemoteSelect
                            v-else-if="f.type === 'select' && f.relTable"
                            v-model="formData[f.name]"
                            :rel-table="f.relTable"
                            :label="f.label"
                            :placeholder="`Pilih ${f.label}`"
                        />
                        <select
                            v-else-if="f.type === 'select'"
                            v-model="formData[f.name]"
                            class="w-full px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 cursor-pointer"
                        >
                            <option value="">— Pilih —</option>
                            <option v-for="opt in optionsForField(f)" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                        </select>

                        <input
                            v-else
                            v-model="formData[f.name]"
                            :type="f.type === 'date' ? 'date' : (f.type === 'number' ? 'number' : 'text')"
                            class="w-full px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500"
                        />
                    </div>
                </div>

                <div class="px-6 py-4 border-t border-slate-100 flex items-center justify-end gap-3 bg-slate-50/50">
                    <button @click="showFormModal = false" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition cursor-pointer">Batal</button>
                    <button @click="submitForm" :disabled="isSubmitting"
                        class="px-4 py-2 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white text-xs font-bold shadow-sm shadow-amber-500/25 transition flex items-center gap-1.5 disabled:opacity-60 cursor-pointer">
                        <i class="fa-solid fa-check"></i>
                        <span>{{ isSubmitting ? 'Menyimpan...' : 'Simpan' }}</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Delete Modal -->
        <div v-if="showDeleteModal"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/40 backdrop-blur-xs">
            <div class="bg-white rounded-3xl p-6 max-w-md w-full shadow-2xl border border-slate-200/80 text-center space-y-5">
                <div class="w-14 h-14 rounded-2xl bg-rose-50 text-rose-600 border border-rose-200 mx-auto flex items-center justify-center text-2xl shadow-sm">
                    <i class="fa-solid fa-trash-can"></i>
                </div>
                <div class="space-y-1.5">
                    <h3 class="text-lg font-black text-slate-900">Konfirmasi Hapus</h3>
                    <p class="text-xs text-slate-500">Apakah Anda yakin ingin menghapus data ini? Tindakan tidak dapat dibatalkan.</p>
                </div>
                <div class="flex items-center justify-center gap-3 pt-2">
                    <button @click="showDeleteModal = false" class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition cursor-pointer">Batal</button>
                    <button @click="confirmDelete" class="px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold shadow-sm shadow-rose-600/30 transition cursor-pointer">Ya, Hapus</button>
                </div>
            </div>
        </div>
    </div>
</template>
