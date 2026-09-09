<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    special: { type: Array, default: () => [] },
    specialModules: { type: Array, default: () => [] },
    grups: { type: Array, default: () => [] },
    items: { type: Array, default: () => [] },
    totalItem: { type: Number, default: 0 },
});

const searchQuery = ref('');
const activeTab = ref('all'); // 'all', 'special', 'items', 'groups'

// Search filter for items
const filteredItems = computed(() => {
    if (!searchQuery.value.trim()) return props.items;
    const q = searchQuery.value.toLowerCase();
    return props.items.filter(
        (i) =>
            (i.nilai && i.nilai.toLowerCase().includes(q)) ||
            (i.kode && i.kode.toLowerCase().includes(q)) ||
            (i.nama_grup && i.nama_grup.toLowerCase().includes(q))
    );
});

// Group filtered items by nama_grup
const groupedItems = computed(() => {
    const map = {};
    filteredItems.value.forEach((item) => {
        const gName = item.nama_grup || 'Lainnya';
        if (!map[gName]) {
            map[gName] = [];
        }
        map[gName].push(item);
    });
    return map;
});

// Modal state for editing item
const showEditModal = ref(false);
const editingItem = ref(null);
const formItem = ref({ id: null, referensi_id: null, urutan: 1, nilai: '', kode: '', status: 1 });

const openEditItem = (item) => {
    editingItem.value = item;
    formItem.value = { ...item };
    showEditModal.value = true;
};

const saveItem = () => {
    if (!formItem.value.id) return;
    router.put(`/admin/master-referensi/master_referensi_item/${formItem.value.id}`, formItem.value, {
        preserveScroll: true,
        onSuccess: () => {
            showEditModal.value = false;
        },
    });
};

// Modal state for deleting item
const showDeleteModal = ref(false);
const itemToDelete = ref(null);
const isDeleting = ref(false);

const deleteItem = (item) => {
    itemToDelete.value = item;
    showDeleteModal.value = true;
};

const confirmDelete = () => {
    if (!itemToDelete.value?.id) return;
    isDeleting.value = true;
    router.delete(`/admin/master-referensi/master_referensi_item/${itemToDelete.value.id}`, {
        preserveScroll: true,
        onFinish: () => {
            isDeleting.value = false;
            showDeleteModal.value = false;
            itemToDelete.value = null;
        },
    });
};

const isReloading = ref(false);
const reloadMaster = () => {
    isReloading.value = true;
    router.reload({
        preserveScroll: true,
        preserveState: false,
        onFinish: () => {
            setTimeout(() => {
                isReloading.value = false;
            }, 400);
        },
    });
};
</script>

<template>
    <AppLayout title="Master Referensi" :fullWidth="true">
        <Head title="Master Referensi - SIPAROKI" />

        <div class="w-full space-y-5 pb-24 overflow-y-auto">
            <!-- Header Card -->
            <div class="rounded-xl bg-white border border-slate-200/80 p-4 sm:p-5 shadow-xs flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                <div class="flex items-start gap-3.5 min-w-0">
                    <div class="w-11 h-11 rounded-xl bg-gradient-to-tr from-amber-500 to-amber-400 flex items-center justify-center text-white shadow-md shadow-amber-500/25 shrink-0">
                        <i class="fa-solid fa-tags text-xl"></i>
                    </div>
                    <div class="min-w-0">
                        <div class="flex flex-col sm:flex-row sm:items-center gap-1.5 sm:gap-2">
                            <h1 class="text-xl font-black text-slate-900 tracking-tight">Master Referensi</h1>
                            <span class="w-fit px-2.5 py-0.5 rounded-lg bg-amber-50 text-amber-800 text-[10px] font-bold border border-amber-200">
                                Pusat Parameter Sistem
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">Kelola data referensi khusus, grup parameter, dan seluruh item referensi aplikasi.</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 sm:flex sm:items-center gap-2">
                    <Link
                        href="/admin/master-referensi/master_referensi"
                        class="col-span-2 sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-2.5 sm:py-2 rounded-xl bg-amber-500 hover:bg-amber-600 active:bg-amber-700 text-white text-xs font-bold shadow-xs transition order-first sm:order-last"
                    >
                        <i class="fa-solid fa-folder-plus text-xs"></i>
                        <span>Kelola Grup Referensi</span>
                    </Link>

                    <button
                        type="button"
                        :disabled="isReloading"
                        @click="reloadMaster"
                        class="col-span-2 sm:col-span-1 sm:w-auto inline-flex items-center justify-center gap-2 px-3.5 py-2.5 sm:py-2 rounded-xl bg-slate-50 hover:bg-slate-100 border border-slate-200 text-slate-700 text-xs font-bold shadow-xs transition cursor-pointer disabled:opacity-60"
                        title="Reload data referensi langsung dari database"
                    >
                        <i :class="['fa-solid fa-arrows-rotate text-amber-600', isReloading ? 'fa-spin' : '']"></i>
                        <span>{{ isReloading ? 'Memuat...' : 'Reload' }}</span>
                    </button>
                </div>
            </div>

            <div class="rounded-xl bg-white border border-slate-200/80 p-2 shadow-2xs overflow-x-auto">
                <div class="flex items-center gap-2 min-w-max">
                    <button
                        type="button"
                        @click="activeTab = 'all'"
                        :class="['px-3.5 py-2 rounded-lg text-xs font-bold transition flex items-center gap-1.5', activeTab === 'all' ? 'bg-amber-500 text-white shadow-sm shadow-amber-500/20' : 'text-slate-600 hover:bg-slate-50']"
                    >
                        <i class="fa-solid fa-layer-group"></i>
                        Semua
                    </button>
                    <button
                        type="button"
                        @click="activeTab = 'special'"
                        :class="['px-3.5 py-2 rounded-lg text-xs font-bold transition flex items-center gap-1.5', activeTab === 'special' ? 'bg-amber-500 text-white shadow-sm shadow-amber-500/20' : 'text-slate-600 hover:bg-slate-50']"
                    >
                        <i class="fa-solid fa-church"></i>
                        Modul Khusus
                        <span class="px-1.5 py-0.5 rounded bg-white/20">{{ specialModules.length }}</span>
                    </button>
                    <button
                        type="button"
                        @click="activeTab = 'items'"
                        :class="['px-3.5 py-2 rounded-lg text-xs font-bold transition flex items-center gap-1.5', activeTab === 'items' ? 'bg-amber-500 text-white shadow-sm shadow-amber-500/20' : 'text-slate-600 hover:bg-slate-50']"
                    >
                        <i class="fa-solid fa-list-check"></i>
                        Item Referensi
                        <span class="px-1.5 py-0.5 rounded bg-white/20">{{ totalItem }}</span>
                    </button>
                    <button
                        type="button"
                        @click="activeTab = 'groups'"
                        :class="['px-3.5 py-2 rounded-lg text-xs font-bold transition flex items-center gap-1.5', activeTab === 'groups' ? 'bg-amber-500 text-white shadow-sm shadow-amber-500/20' : 'text-slate-600 hover:bg-slate-50']"
                    >
                        <i class="fa-solid fa-folder-tree"></i>
                        Grup
                        <span class="px-1.5 py-0.5 rounded bg-white/20">{{ grups.length }}</span>
                    </button>
                </div>
            </div>

            <!-- SECTION 1: MODUL REFERENSI KHUSUS -->
            <div v-if="activeTab === 'all' || activeTab === 'special'" class="rounded-xl bg-white border border-slate-200/80 shadow-2xs overflow-hidden">
                <div class="px-5 py-3.5 bg-slate-50/80 border-b border-slate-200 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <i class="fa-solid fa-church text-amber-600 text-sm"></i>
                        <h2 class="text-xs font-black text-slate-800 uppercase tracking-wider whitespace-nowrap">MODUL REFERENSI KHUSUS</h2>
                    </div>
                    <span class="text-[11px] font-bold text-slate-400 whitespace-nowrap">{{ specialModules.length }} Modul</span>
                </div>

                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left text-xs whitespace-nowrap">
                        <thead class="bg-slate-50/50 text-slate-500 uppercase tracking-wider text-[10px] font-bold border-b border-slate-200">
                            <tr>
                                <th class="px-5 py-2.5 whitespace-nowrap">MODUL REFERENSI KHUSUS</th>
                                <th class="px-5 py-2.5 text-center whitespace-nowrap">JUMLAH</th>
                                <th class="px-5 py-2.5 text-right whitespace-nowrap">AKSI</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            <tr v-for="m in specialModules" :key="m.slug" class="hover:bg-amber-50/40 transition">
                                <td class="px-5 py-3 font-bold text-slate-900 flex items-center gap-2.5 whitespace-nowrap">
                                    <div class="w-7 h-7 rounded-lg bg-amber-50 text-amber-700 border border-amber-200/60 flex items-center justify-center text-xs shrink-0">
                                        <i class="fa-solid" :class="m.icon"></i>
                                    </div>
                                    <span class="whitespace-nowrap">{{ m.label }}</span>
                                </td>
                                <td class="px-5 py-3 text-center whitespace-nowrap">
                                    <span class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 font-bold text-xs border border-slate-200/60 whitespace-nowrap">
                                        {{ m.count }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-right whitespace-nowrap">
                                    <Link
                                        :href="m.url"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-800 text-xs font-bold border border-amber-200/80 transition whitespace-nowrap"
                                    >
                                        <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                                        <span>Kelola</span>
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- SECTION 2: DAFTAR ITEM REFERENSI (GROUPED) -->
            <div v-if="activeTab === 'all' || activeTab === 'items'" class="rounded-xl bg-white border border-slate-200/80 shadow-2xs overflow-hidden space-y-4 p-4 sm:p-5">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 pb-3 border-b border-slate-100">
                    <div>
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-list-check text-amber-600 text-sm"></i>
                            <h2 class="text-sm font-black text-slate-900 tracking-tight whitespace-nowrap">Daftar Item Referensi</h2>
                            <span class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-700 text-[11px] font-bold whitespace-nowrap">{{ totalItem }} Item</span>
                        </div>
                        <p class="text-[11px] text-slate-500 mt-0.5">
                            Data dikelompokkan berdasarkan Grup Referensi. Pilih item untuk hapus atau edit per item.
                        </p>
                    </div>

                    <!-- Search Input -->
                    <div class="relative w-full md:w-80">
                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Cari item referensi..."
                            class="w-full pl-8 pr-3 py-2 rounded-lg bg-slate-50 border border-slate-200 text-xs text-slate-800 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition shadow-2xs"
                        />
                    </div>
                </div>

                <!-- Grouped Items Loop -->
                <div class="space-y-4 max-h-[70vh] overflow-y-auto pr-1 custom-scrollbar">
                    <div
                        v-for="(gItems, gName) in groupedItems"
                        :key="gName"
                        class="rounded-xl border border-slate-200/90 overflow-hidden shadow-2xs"
                    >
                        <!-- Group Header -->
                        <div class="bg-gradient-to-r from-slate-100 to-slate-50 px-4 py-2.5 border-b border-slate-200 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                                <h3 class="text-xs font-black text-slate-900 tracking-wide uppercase whitespace-nowrap">{{ gName }}</h3>
                                <span class="px-2 py-0.5 rounded-md bg-white text-slate-600 border border-slate-200 text-[10px] font-bold whitespace-nowrap">
                                    {{ gItems.length }} Item
                                </span>
                            </div>
                            <Link
                                :href="`/admin/master-referensi/master_referensi_item?grup=${gItems[0]?.referensi_id}`"
                                class="inline-flex items-center gap-1 text-[11px] font-bold text-amber-700 hover:text-amber-800 hover:underline whitespace-nowrap"
                            >
                                <i class="fa-solid fa-plus text-[10px]"></i>
                                <span>Tambah Item {{ gName }}</span>
                            </Link>
                        </div>

                        <!-- Table Items for this Group -->
                        <div class="overflow-x-auto custom-scrollbar">
                            <table class="w-full text-left text-xs whitespace-nowrap">
                                <thead class="bg-white text-slate-500 uppercase tracking-wider text-[10px] font-bold border-b border-slate-200">
                                    <tr>
                                        <th class="w-16 px-4 py-2 text-center whitespace-nowrap">URUT</th>
                                        <th class="px-4 py-2 whitespace-nowrap">NILAI / NAMA ITEM</th>
                                        <th class="px-4 py-2 whitespace-nowrap">KODE</th>
                                        <th class="px-4 py-2 whitespace-nowrap">GRUP REFERENSI</th>
                                        <th class="px-4 py-2 text-center whitespace-nowrap">STATUS</th>
                                        <th class="px-4 py-2 text-right whitespace-nowrap">AKSI</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 text-slate-700 bg-white">
                                    <tr v-for="item in gItems" :key="item.id" class="hover:bg-slate-50/70 transition">
                                        <td class="px-4 py-2.5 text-center font-bold text-slate-500 whitespace-nowrap">{{ item.urutan }}</td>
                                        <td class="px-4 py-2.5 font-bold text-slate-900 whitespace-nowrap">{{ item.nilai }}</td>
                                        <td class="px-4 py-2.5 font-mono text-[11px] text-slate-500 whitespace-nowrap">{{ item.kode || '-' }}</td>
                                        <td class="px-4 py-2.5 text-slate-600 whitespace-nowrap">{{ item.nama_grup }}</td>
                                        <td class="px-4 py-2.5 text-center whitespace-nowrap">
                                            <span :class="[
                                                'inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold border whitespace-nowrap',
                                                (item.status === 1 || item.status === 'Aktif')
                                                    ? 'bg-emerald-50 text-emerald-700 border-emerald-200'
                                                    : 'bg-rose-50 text-rose-700 border-rose-200'
                                            ]">
                                                <span class="w-1.5 h-1.5 rounded-full shrink-0" :class="(item.status === 1 || item.status === 'Aktif') ? 'bg-emerald-500' : 'bg-rose-500'"></span>
                                                {{ (item.status === 1 || item.status === 'Aktif') ? 'Aktif' : 'Nonaktif' }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-2.5 text-right space-x-1.5 whitespace-nowrap">
                                            <button
                                                type="button"
                                                @click="openEditItem(item)"
                                                class="w-7 h-7 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 text-xs inline-flex items-center justify-center transition cursor-pointer shrink-0"
                                                title="Edit Item"
                                            >
                                                <i class="fa-solid fa-pen-to-square text-[11px]"></i>
                                            </button>
                                            <button
                                                type="button"
                                                @click="deleteItem(item)"
                                                class="w-7 h-7 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 text-xs inline-flex items-center justify-center transition cursor-pointer shrink-0"
                                                title="Hapus Item"
                                            >
                                                <i class="fa-solid fa-trash text-[11px]"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div v-if="!Object.keys(groupedItems).length" class="text-center py-10 text-slate-400">
                        <i class="fa-solid fa-folder-open text-3xl mb-2 text-slate-300"></i>
                        <p class="text-xs font-bold text-slate-600">Tidak ada item referensi yang sesuai pencarian.</p>
                    </div>
                </div>
            </div>

            <!-- SECTION 3: TABEL GRUP REFERENSI -->
            <div v-if="activeTab === 'all' || activeTab === 'groups'" class="rounded-xl bg-white border border-slate-200/80 shadow-2xs overflow-hidden">
                <div class="px-5 py-3.5 bg-slate-50/80 border-b border-slate-200 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <i class="fa-solid fa-folder-tree text-amber-600 text-sm"></i>
                        <h2 class="text-xs font-black text-slate-800 uppercase tracking-wider whitespace-nowrap">GRUP REFERENSI</h2>
                    </div>
                    <span class="text-[11px] font-bold text-slate-400 whitespace-nowrap">{{ grups.length }} Grup</span>
                </div>

                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left text-xs whitespace-nowrap">
                        <thead class="bg-slate-50/50 text-slate-500 uppercase tracking-wider text-[10px] font-bold border-b border-slate-200">
                            <tr>
                                <th class="px-5 py-2.5 whitespace-nowrap">KODE</th>
                                <th class="px-5 py-2.5 whitespace-nowrap">NAMA GRUP</th>
                                <th class="px-5 py-2.5 text-center whitespace-nowrap">ITEM</th>
                                <th class="px-5 py-2.5 text-center whitespace-nowrap">STATUS</th>
                                <th class="px-5 py-2.5 text-right whitespace-nowrap">AKSI</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            <tr v-for="g in grups" :key="g.id" class="hover:bg-amber-50/40 transition">
                                <td class="px-5 py-3 font-mono text-[11px] font-bold text-slate-600 whitespace-nowrap">{{ g.kode_grup }}</td>
                                <td class="px-5 py-3 font-bold text-slate-900 whitespace-nowrap">{{ g.nama_grup }}</td>
                                <td class="px-5 py-3 text-center whitespace-nowrap">
                                    <span class="px-2.5 py-0.5 rounded-lg bg-amber-50 text-amber-800 border border-amber-200 font-bold text-xs whitespace-nowrap">
                                        {{ g.count }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-center whitespace-nowrap">
                                    <span :class="[
                                        'inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold border whitespace-nowrap',
                                        (g.status === 1 || g.status === 'Aktif')
                                            ? 'bg-emerald-50 text-emerald-700 border-emerald-200'
                                            : 'bg-rose-50 text-rose-700 border-rose-200'
                                    ]">
                                        <span class="w-1.5 h-1.5 rounded-full shrink-0" :class="(g.status === 1 || g.status === 'Aktif') ? 'bg-emerald-500' : 'bg-rose-500'"></span>
                                        {{ (g.status === 1 || g.status === 'Aktif') ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-right whitespace-nowrap">
                                    <Link
                                        :href="`/admin/master-referensi/master_referensi_item?grup=${g.id}`"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-800 text-xs font-bold border border-amber-200 transition whitespace-nowrap"
                                    >
                                        <i class="fa-solid fa-list text-xs"></i>
                                        <span>Kelola Item</span>
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- EDIT ITEM MODAL -->
        <div
            v-if="showEditModal"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/40 backdrop-blur-xs"
        >
            <div class="bg-white rounded-3xl p-6 max-w-md w-full shadow-2xl border border-slate-200 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h3 class="text-sm font-black text-slate-900">Edit Item Referensi</h3>
                    <button @click="showEditModal = false" class="text-slate-400 hover:text-slate-700 text-xs">
                        <i class="fa-solid fa-xmark text-sm"></i>
                    </button>
                </div>

                <div class="space-y-3">
                    <div>
                        <label class="text-[11px] font-bold text-slate-600 block mb-1">Grup Referensi</label>
                        <input
                            :value="formItem.nama_grup"
                            disabled
                            class="w-full px-3 py-2 rounded-xl bg-slate-100 border border-slate-200 text-xs text-slate-500 font-bold"
                        />
                    </div>
                    <div>
                        <label class="text-[11px] font-bold text-slate-600 block mb-1">Nilai / Nama Item *</label>
                        <input
                            v-model="formItem.nilai"
                            type="text"
                            class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-amber-500"
                        />
                    </div>
                    <div>
                        <label class="text-[11px] font-bold text-slate-600 block mb-1">Kode</label>
                        <input
                            v-model="formItem.kode"
                            type="text"
                            class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-amber-500"
                        />
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-[11px] font-bold text-slate-600 block mb-1">Urutan</label>
                            <input
                                v-model="formItem.urutan"
                                type="number"
                                class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-amber-500"
                            />
                        </div>
                        <div>
                            <label class="text-[11px] font-bold text-slate-600 block mb-1">Status</label>
                            <select
                                v-model="formItem.status"
                                class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-amber-500"
                            >
                                <option :value="1">Aktif</option>
                                <option :value="0">Nonaktif</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button
                        type="button"
                        @click="showEditModal = false"
                        class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        @click="saveItem"
                        class="px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold shadow-xs transition"
                    >
                        Simpan Perubahan
                    </button>
                </div>
            </div>
        </div>

        <!-- DELETE CONFIRMATION MODAL -->
        <div
            v-if="showDeleteModal && itemToDelete"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/40 backdrop-blur-xs"
        >
            <div class="bg-white rounded-3xl p-6 max-w-md w-full shadow-2xl border border-slate-200 text-center space-y-4 animate-in fade-in zoom-in-95">
                <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 border border-rose-200 mx-auto flex items-center justify-center text-xl">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Konfirmasi Hapus Item</h3>
                    <p class="text-xs text-slate-500 mt-1">
                        Apakah Anda yakin ingin menghapus item referensi <b>"{{ itemToDelete.nilai }}"</b> (Grup: {{ itemToDelete.nama_grup }})?
                    </p>
                </div>
                <div class="flex items-center justify-center gap-2.5 pt-2">
                    <button
                        type="button"
                        @click="showDeleteModal = false"
                        class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition cursor-pointer"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        :disabled="isDeleting"
                        @click="confirmDelete"
                        class="px-5 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold shadow-sm shadow-rose-600/30 transition cursor-pointer flex items-center gap-1.5"
                    >
                        <i v-if="isDeleting" class="fa-solid fa-circle-notch fa-spin text-xs"></i>
                        <span>{{ isDeleting ? 'Menghapus...' : 'Ya, Hapus Item' }}</span>
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
