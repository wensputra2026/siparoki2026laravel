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

const deleteItem = (item) => {
    if (confirm(`Yakin ingin menghapus item "${item.nilai}"?`)) {
        router.delete(`/admin/master-referensi/master_referensi_item/${item.id}`, {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <AppLayout title="Master Referensi">
        <Head title="Master Referensi - SIPAROKI" />

        <div class="w-full space-y-6 pb-12 overflow-y-auto">
            <!-- Header Card -->
            <div class="rounded-2xl bg-white border border-slate-200/80 p-5 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-3.5">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-amber-500 to-amber-400 flex items-center justify-center text-white shadow-md shadow-amber-500/25 shrink-0">
                        <i class="fa-solid fa-tags text-xl"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-xl font-black text-slate-900 tracking-tight">Master Referensi</h1>
                            <span class="px-2.5 py-0.5 rounded-full bg-amber-50 text-amber-800 text-[10px] font-bold border border-amber-200">
                                Pusat Parameter Sistem
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">Kelola data referensi khusus, grup parameter, dan seluruh item referensi aplikasi.</p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <Link
                        href="/admin/master-referensi/master_referensi"
                        class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold shadow-xs transition"
                    >
                        <i class="fa-solid fa-folder-plus text-xs"></i>
                        <span>Kelola Grup Referensi</span>
                    </Link>
                </div>
            </div>

            <!-- SECTION 1: MODUL REFERENSI KHUSUS -->
            <div class="rounded-2xl bg-white border border-slate-200/80 shadow-2xs overflow-hidden">
                <div class="px-5 py-3.5 bg-slate-50/80 border-b border-slate-200 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <i class="fa-solid fa-church text-amber-600 text-sm"></i>
                        <h2 class="text-xs font-black text-slate-800 uppercase tracking-wider">MODUL REFERENSI KHUSUS</h2>
                    </div>
                    <span class="text-[11px] font-bold text-slate-400">{{ specialModules.length }} Modul</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50/50 text-slate-500 uppercase tracking-wider text-[10px] font-bold border-b border-slate-200">
                            <tr>
                                <th class="px-5 py-2.5">MODUL REFERENSI KHUSUS</th>
                                <th class="px-5 py-2.5 text-center">JUMLAH</th>
                                <th class="px-5 py-2.5 text-right">AKSI</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            <tr v-for="m in specialModules" :key="m.slug" class="hover:bg-amber-50/40 transition">
                                <td class="px-5 py-3 font-bold text-slate-900 flex items-center gap-2.5">
                                    <div class="w-7 h-7 rounded-lg bg-amber-50 text-amber-700 border border-amber-200/60 flex items-center justify-center text-xs">
                                        <i class="fa-solid" :class="m.icon"></i>
                                    </div>
                                    <span>{{ m.label }}</span>
                                </td>
                                <td class="px-5 py-3 text-center">
                                    <span class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 font-bold text-xs border border-slate-200/60">
                                        {{ m.count }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-right">
                                    <Link
                                        :href="m.url"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-amber-50 hover:bg-amber-100 text-amber-800 text-xs font-bold border border-amber-200/80 transition"
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
            <div class="rounded-2xl bg-white border border-slate-200/80 shadow-2xs overflow-hidden space-y-4 p-5">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 pb-3 border-b border-slate-100">
                    <div>
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-list-check text-amber-600 text-sm"></i>
                            <h2 class="text-sm font-black text-slate-900 tracking-tight">Daftar Item Referensi</h2>
                            <span class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-700 text-[11px] font-bold">{{ totalItem }} Item</span>
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
                            class="w-full pl-8 pr-3 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-800 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition shadow-2xs"
                        />
                    </div>
                </div>

                <!-- Grouped Items Loop -->
                <div class="space-y-6">
                    <div
                        v-for="(gItems, gName) in groupedItems"
                        :key="gName"
                        class="rounded-xl border border-slate-200/90 overflow-hidden shadow-2xs"
                    >
                        <!-- Group Header -->
                        <div class="bg-gradient-to-r from-slate-100 to-slate-50 px-4 py-2.5 border-b border-slate-200 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                                <h3 class="text-xs font-black text-slate-900 tracking-wide uppercase">{{ gName }}</h3>
                                <span class="px-2 py-0.5 rounded-md bg-white text-slate-600 border border-slate-200 text-[10px] font-bold">
                                    {{ gItems.length }} Item
                                </span>
                            </div>
                            <Link
                                :href="`/admin/master-referensi/master_referensi_item?grup=${gItems[0]?.referensi_id}`"
                                class="inline-flex items-center gap-1 text-[11px] font-bold text-amber-700 hover:text-amber-800 hover:underline"
                            >
                                <i class="fa-solid fa-plus text-[10px]"></i>
                                <span>Tambah Item {{ gName }}</span>
                            </Link>
                        </div>

                        <!-- Table Items for this Group -->
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs">
                                <thead class="bg-white text-slate-500 uppercase tracking-wider text-[10px] font-bold border-b border-slate-200">
                                    <tr>
                                        <th class="w-16 px-4 py-2 text-center">URUT</th>
                                        <th class="px-4 py-2">NILAI / NAMA ITEM</th>
                                        <th class="px-4 py-2">KODE</th>
                                        <th class="px-4 py-2">GRUP REFERENSI</th>
                                        <th class="px-4 py-2 text-center">STATUS</th>
                                        <th class="px-4 py-2 text-right">AKSI</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 text-slate-700 bg-white">
                                    <tr v-for="item in gItems" :key="item.id" class="hover:bg-slate-50/70 transition">
                                        <td class="px-4 py-2.5 text-center font-bold text-slate-500">{{ item.urutan }}</td>
                                        <td class="px-4 py-2.5 font-bold text-slate-900">{{ item.nilai }}</td>
                                        <td class="px-4 py-2.5 font-mono text-[11px] text-slate-500">{{ item.kode || '-' }}</td>
                                        <td class="px-4 py-2.5 text-slate-600">{{ item.nama_grup }}</td>
                                        <td class="px-4 py-2.5 text-center">
                                            <span :class="[
                                                'inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold border',
                                                (item.status === 1 || item.status === 'Aktif')
                                                    ? 'bg-emerald-50 text-emerald-700 border-emerald-200'
                                                    : 'bg-rose-50 text-rose-700 border-rose-200'
                                            ]">
                                                <span class="w-1.5 h-1.5 rounded-full" :class="(item.status === 1 || item.status === 'Aktif') ? 'bg-emerald-500' : 'bg-rose-500'"></span>
                                                {{ (item.status === 1 || item.status === 'Aktif') ? 'Aktif' : 'Nonaktif' }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-2.5 text-right space-x-1.5 whitespace-nowrap">
                                            <button
                                                type="button"
                                                @click="openEditItem(item)"
                                                class="w-7 h-7 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 text-xs inline-flex items-center justify-center transition cursor-pointer"
                                                title="Edit Item"
                                            >
                                                <i class="fa-solid fa-pen-to-square text-[11px]"></i>
                                            </button>
                                            <button
                                                type="button"
                                                @click="deleteItem(item)"
                                                class="w-7 h-7 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 text-xs inline-flex items-center justify-center transition cursor-pointer"
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
            <div class="rounded-2xl bg-white border border-slate-200/80 shadow-2xs overflow-hidden">
                <div class="px-5 py-3.5 bg-slate-50/80 border-b border-slate-200 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <i class="fa-solid fa-folder-tree text-amber-600 text-sm"></i>
                        <h2 class="text-xs font-black text-slate-800 uppercase tracking-wider">GRUP REFERENSI</h2>
                    </div>
                    <span class="text-[11px] font-bold text-slate-400">{{ grups.length }} Grup</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50/50 text-slate-500 uppercase tracking-wider text-[10px] font-bold border-b border-slate-200">
                            <tr>
                                <th class="px-5 py-2.5">KODE</th>
                                <th class="px-5 py-2.5">NAMA GRUP</th>
                                <th class="px-5 py-2.5 text-center">ITEM</th>
                                <th class="px-5 py-2.5 text-center">STATUS</th>
                                <th class="px-5 py-2.5 text-right">AKSI</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            <tr v-for="g in grups" :key="g.id" class="hover:bg-amber-50/40 transition">
                                <td class="px-5 py-3 font-mono text-[11px] font-bold text-slate-600">{{ g.kode_grup }}</td>
                                <td class="px-5 py-3 font-bold text-slate-900">{{ g.nama_grup }}</td>
                                <td class="px-5 py-3 text-center">
                                    <span class="px-2.5 py-0.5 rounded-lg bg-amber-50 text-amber-800 border border-amber-200 font-bold text-xs">
                                        {{ g.count }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-center">
                                    <span :class="[
                                        'inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold border',
                                        (g.status === 1 || g.status === 'Aktif')
                                            ? 'bg-emerald-50 text-emerald-700 border-emerald-200'
                                            : 'bg-rose-50 text-rose-700 border-rose-200'
                                    ]">
                                        <span class="w-1.5 h-1.5 rounded-full" :class="(g.status === 1 || g.status === 'Aktif') ? 'bg-emerald-500' : 'bg-rose-500'"></span>
                                        {{ (g.status === 1 || g.status === 'Aktif') ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-right">
                                    <Link
                                        :href="`/admin/master-referensi/master_referensi_item?grup=${g.id}`"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-amber-50 hover:bg-amber-100 text-amber-800 text-xs font-bold border border-amber-200 transition"
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
    </AppLayout>
</template>
