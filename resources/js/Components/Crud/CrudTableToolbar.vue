<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
    search: {
        type: String,
        default: '',
    },
    perPage: {
        type: [String, Number],
        default: 15,
    },
    totalItems: {
        type: Number,
        default: 0,
    },
    isCardView: {
        type: Boolean,
        default: false,
    },
    hasActiveFilters: {
        type: Boolean,
        default: false,
    },
    activeFilterCount: {
        type: Number,
        default: 0,
    },
    createLabel: {
        type: String,
        default: 'Tambah Data',
    },
});

const emit = defineEmits([
    'update:search',
    'update:perPage',
    'update:isCardView',
    'openFilter',
    'openCreate',
    'openImport',
    'exportCsv',
    'refresh',
]);

const searchVal = ref(props.search);

let debounceTimer = null;
watch(searchVal, (newVal) => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        emit('update:search', newVal);
    }, 300);
});
</script>

<template>
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 mb-5 p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-sm">
        <!-- Search Input -->
        <div class="relative flex-1 max-w-md">
            <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>
            <input
                v-model="searchVal"
                type="text"
                placeholder="Cari data instan (nama, kode, nomor)..."
                class="w-full pl-9 pr-8 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-100 text-xs placeholder:text-slate-400 focus:border-teal-500 dark:focus:border-teal-400 focus:bg-white dark:focus:bg-slate-900 outline-none transition"
            />
            <button
                v-if="searchVal"
                type="button"
                @click="searchVal = ''"
                class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-xs"
            >
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <!-- Right Action Controls -->
        <div class="flex flex-wrap items-center gap-2">
            <!-- Filter Button with badge count -->
            <button
                type="button"
                @click="$emit('openFilter')"
                class="relative px-3.5 py-2 rounded-xl border text-xs font-semibold transition flex items-center gap-2"
                :class="hasActiveFilters 
                    ? 'border-amber-500/50 bg-amber-50 dark:bg-amber-950/30 text-amber-600 dark:text-amber-400' 
                    : 'border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700'"
            >
                <i class="fa-solid fa-filter text-[11px]"></i>
                <span>Filter</span>
                <span 
                    v-if="activeFilterCount > 0" 
                    class="w-4 h-4 rounded-full bg-amber-500 text-slate-950 font-extrabold text-[10px] flex items-center justify-center"
                >
                    {{ activeFilterCount }}
                </span>
            </button>

            <!-- Card View Toggle (Mobile / Responsive) -->
            <div class="flex items-center rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 p-0.5">
                <button
                    type="button"
                    @click="$emit('update:isCardView', false)"
                    class="px-2.5 py-1.5 rounded-lg text-xs transition"
                    :class="!isCardView 
                        ? 'bg-white dark:bg-slate-900 text-teal-600 dark:text-teal-400 shadow-sm font-bold' 
                        : 'text-slate-500 hover:text-slate-700 dark:hover:text-slate-300'"
                    title="Tampilan Tabel"
                >
                    <i class="fa-solid fa-table-list"></i>
                </button>
                <button
                    type="button"
                    @click="$emit('update:isCardView', true)"
                    class="px-2.5 py-1.5 rounded-lg text-xs transition"
                    :class="isCardView 
                        ? 'bg-white dark:bg-slate-900 text-teal-600 dark:text-teal-400 shadow-sm font-bold' 
                        : 'text-slate-500 hover:text-slate-700 dark:hover:text-slate-300'"
                    title="Tampilan Kartu"
                >
                    <i class="fa-solid fa-table-cells-large"></i>
                </button>
            </div>

            <!-- Refresh Button -->
            <button
                type="button"
                @click="$emit('refresh')"
                class="p-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 text-xs transition"
                title="Muat Ulang Data"
            >
                <i class="fa-solid fa-rotate-right"></i>
            </button>

            <!-- Export CSV -->
            <button
                type="button"
                @click="$emit('exportCsv')"
                class="px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 text-xs font-semibold transition flex items-center gap-1.5"
                title="Export Data CSV"
            >
                <i class="fa-solid fa-file-export text-slate-400"></i>
                <span>Export</span>
            </button>

            <!-- Create New Data Button -->
            <button
                type="button"
                @click="$emit('openCreate')"
                class="px-4 py-2 rounded-xl bg-gradient-to-r from-teal-600 to-teal-500 hover:from-teal-500 hover:to-teal-400 text-white text-xs font-bold shadow-lg shadow-teal-600/20 transition flex items-center gap-1.5"
            >
                <i class="fa-solid fa-plus text-[11px]"></i>
                <span>{{ createLabel }}</span>
            </button>
        </div>
    </div>
</template>
