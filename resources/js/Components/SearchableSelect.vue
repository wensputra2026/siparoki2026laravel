<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue';

const props = defineProps({
    modelValue: {
        type: [String, Number],
        default: '',
    },
    options: {
        type: Array,
        default: () => [],
    },
    valueKey: {
        type: String,
        default: 'id',
    },
    labelKey: {
        type: String,
        default: 'name',
    },
    placeholder: {
        type: String,
        default: 'Pilih opsi...',
    },
    searchPlaceholder: {
        type: String,
        default: 'Cari data...',
    },
    icon: {
        type: String,
        default: '',
    },
    iconColor: {
        type: String,
        default: 'text-amber-600',
    },
    clearable: {
        type: Boolean,
        default: true,
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    // Mode pencarian remote (lazy). Bila diisi, opsi diambil dari endpoint
    // berdasarkan query (searchParam) atau id (untuk resolve nilai terpilih).
    searchUrl: {
        type: String,
        default: '',
    },
    searchParam: {
        type: String,
        default: 'q',
    },
    searchDebounce: {
        type: Number,
        default: 250,
    },
    compact: {
        type: Boolean,
        default: false,
    },
    dropdownMinWidth: {
        type: String,
        default: '',
    },
});

const emit = defineEmits(['update:modelValue', 'change']);

const isOpen = ref(false);
const searchQuery = ref('');
const containerRef = ref(null);
const searchInputRef = ref(null);

// Remote search state
const remoteOptions = ref([]);
const isFetching = ref(false);
const selectedRemote = ref(null);
let debounceTimer = null;

const getItemValue = (item) => {
    if (typeof item !== 'object' || item === null) return item;
    return item[props.valueKey] !== undefined ? item[props.valueKey] : (item.id || item.id_dekenat || item.id_keuskupan || item.id_kevikepan || item.id_paroki || '');
};

const getItemLabel = (item) => {
    if (typeof item !== 'object' || item === null) return String(item);
    return item[props.labelKey] || item.name || item.nama_keuskupan || item.nama_kevikepan || item.nama_dekenat || item.nama_paroki || String(getItemValue(item));
};

const isSelected = (opt) => {
    if (props.modelValue === '' || props.modelValue === null || props.modelValue === undefined) {
        return false;
    }
    const val = getItemValue(opt);
    if (val === '' || val === null || val === undefined) {
        return false;
    }
    return String(val) === String(props.modelValue);
};

const fetchRemote = (q) => {
    if (!props.searchUrl) return;
    isFetching.value = true;
    let url;
    try {
        url = new URL(props.searchUrl, window.location.origin);
    } catch (e) {
        url = new URL(props.searchUrl, 'http://localhost');
    }
    if (q) url.searchParams.set(props.searchParam, q);
    fetch(url.toString(), { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
        .then((r) => r.json())
        .then((data) => { remoteOptions.value = Array.isArray(data) ? data : []; })
        .catch(() => { remoteOptions.value = []; })
        .finally(() => { isFetching.value = false; });
};

const scheduleFetch = (q) => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => fetchRemote(q), props.searchDebounce);
};

const resolveSelected = () => {
    if (!props.searchUrl || props.modelValue === '' || props.modelValue === null || props.modelValue === undefined) {
        selectedRemote.value = null;
        return;
    }
    if (remoteOptions.value.some((o) => isSelected(o))) {
        return;
    }
    let url;
    try {
        url = new URL(props.searchUrl, window.location.origin);
    } catch (e) {
        url = new URL(props.searchUrl, 'http://localhost');
    }
    url.searchParams.set('id', props.modelValue);
    fetch(url.toString(), { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
        .then((r) => r.json())
        .then((data) => { selectedRemote.value = Array.isArray(data) && data.length ? data[0] : null; })
        .catch(() => { selectedRemote.value = null; });
};

const selectedOption = computed(() => {
    if (props.modelValue === '' || props.modelValue === null || props.modelValue === undefined) return null;
    if (props.searchUrl) {
        if (selectedRemote.value && String(getItemValue(selectedRemote.value)) === String(props.modelValue)) {
            return selectedRemote.value;
        }
        return remoteOptions.value.find((opt) => isSelected(opt)) || null;
    }
    return props.options.find((opt) => isSelected(opt)) || null;
});

const filteredOptions = computed(() => {
    if (props.searchUrl) return remoteOptions.value;
    if (!searchQuery.value.trim()) return props.options;
    const q = searchQuery.value.toLowerCase().trim();
    return props.options.filter((opt) => {
        const label = getItemLabel(opt).toLowerCase();
        return label.includes(q);
    });
});

const formattedIcon = computed(() => {
    if (!props.icon) return '';
    if (props.icon.includes('fa-solid') || props.icon.includes('fa-regular') || props.icon.includes('fa-brands')) {
        return props.icon;
    }
    if (props.icon.startsWith('fa-')) {
        return `fa-solid ${props.icon}`;
    }
    return `fa-solid fa-${props.icon}`;
});

const toggleDropdown = () => {
    if (props.disabled) return;
    isOpen.value = !isOpen.value;
    if (isOpen.value) {
        searchQuery.value = '';
        if (props.searchUrl) {
            fetchRemote('');
        }
        setTimeout(() => {
            searchInputRef.value?.focus();
        }, 50);
    }
};

watch(searchQuery, (q) => {
    if (props.searchUrl && isOpen.value) {
        scheduleFetch(q);
    }
});

watch(() => props.modelValue, () => {
    resolveSelected();
});

const selectOption = (opt) => {
    const val = opt ? getItemValue(opt) : '';
    emit('update:modelValue', val);
    emit('change', val);
    isOpen.value = false;
    searchQuery.value = '';
};

const clearSelection = (e) => {
    e.stopPropagation();
    emit('update:modelValue', '');
    emit('change', '');
    searchQuery.value = '';
};

const handleClickOutside = (e) => {
    if (containerRef.value && !containerRef.value.contains(e.target)) {
        isOpen.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
    resolveSelected();
});

onBeforeUnmount(() => {
    document.removeEventListener('click', handleClickOutside);
});
</script>

<template>
    <div ref="containerRef" class="relative inline-block w-full text-left text-xs sm:text-[12.5px] select-none">
        <!-- Trigger Button -->
        <button
            type="button"
            @click="toggleDropdown"
            :disabled="disabled"
            :class="[
                compact
                    ? 'h-[32px] px-2.5 py-1 rounded-lg border flex items-center justify-between gap-1.5 transition-all shadow-2xs cursor-pointer text-left w-full text-xs font-bold'
                    : 'min-h-[40px] px-3.5 py-2 rounded-xl border flex items-center justify-between gap-1.5 transition-all shadow-2xs cursor-pointer text-left w-full text-xs sm:text-[12.5px]',
                isOpen
                    ? 'bg-white border-amber-500 ring-2 ring-amber-500/15'
                    : 'bg-white hover:bg-slate-50 border-amber-300 text-slate-800',
                disabled ? 'opacity-50 cursor-not-allowed' : ''
            ]"
        >
            <div class="flex items-center gap-2 min-w-0 flex-1">
                <i v-if="icon" :class="[formattedIcon, iconColor, 'text-xs shrink-0']"></i>
                <span :class="[
                    'truncate font-bold',
                    selectedOption ? 'text-slate-900' : 'text-slate-500 font-medium'
                ]">
                    {{ selectedOption ? getItemLabel(selectedOption) : placeholder }}
                </span>
            </div>

            <div class="flex items-center gap-1 shrink-0">
                <!-- Clear Button -->
                <span
                    v-if="clearable && !disabled && selectedOption"
                    @click="clearSelection"
                    class="text-slate-400 hover:text-rose-600 transition p-0.5 rounded-md hover:bg-slate-100 cursor-pointer"
                    title="Hapus pilihan"
                >
                    <i class="fa-solid fa-xmark text-xs"></i>
                </span>

                <!-- Caret Arrow -->
                <i
                    :class="[
                        'fa-solid fa-chevron-down text-[10px] text-slate-400 transition-transform duration-200',
                        isOpen ? 'rotate-180 text-amber-600' : ''
                    ]"
                ></i>
            </div>
        </button>

        <!-- Dropdown Popup (Select2 Modern Replacement) -->
        <Transition
            enter-active-class="transition duration-150 ease-out"
            enter-from-class="transform scale-95 opacity-0 -translate-y-1"
            enter-to-class="transform scale-100 opacity-100 translate-y-0"
            leave-active-class="transition duration-100 ease-in"
            leave-from-class="transform scale-100 opacity-100 translate-y-0"
            leave-to-class="transform scale-95 opacity-0 -translate-y-1"
        >
            <div
                v-if="isOpen"
                :class="[
                    'absolute left-0 top-full mt-1.5 z-[100] rounded-2xl bg-white border border-slate-200/90 shadow-2xl shadow-slate-900/15 overflow-hidden',
                    dropdownMinWidth ? dropdownMinWidth : 'w-full min-w-[220px] max-w-[calc(100vw-2rem)]'
                ]"
            >
                <!-- Search Box inside Dropdown -->
                <div class="p-2 border-b border-slate-100 bg-slate-50/70">
                    <div class="relative">
                        <i class="fa-solid fa-magnifying-glass absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400 text-[10px]"></i>
                        <input
                            ref="searchInputRef"
                            v-model="searchQuery"
                            type="text"
                            :placeholder="searchPlaceholder"
                            class="w-full pl-7 pr-2.5 py-1.5 text-xs rounded-lg bg-white border border-slate-200 text-slate-900 placeholder-slate-400 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500"
                        />
                    </div>
                </div>

                <!-- Options List -->
                <div class="max-h-60 overflow-y-auto p-1.5 space-y-0.5 custom-scrollbar">
                    <!-- Default / All Option -->
                    <button
                        type="button"
                        @click="selectOption(null)"
                        :class="[
                            'w-full text-left px-2.5 py-1.5 rounded-lg text-xs font-semibold flex items-center justify-between transition cursor-pointer',
                            (!modelValue && modelValue !== 0)
                                ? 'bg-amber-50 text-amber-900 font-bold'
                                : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'
                        ]"
                    >
                        <span class="flex items-center gap-2">
                            <i class="fa-solid fa-list-ul text-[10px] text-slate-400"></i>
                            <span>{{ placeholder }}</span>
                        </span>
                        <i v-if="!modelValue && modelValue !== 0" class="fa-solid fa-check text-[10px] text-amber-600"></i>
                    </button>

                    <!-- Filtered Options -->
                    <button
                        v-for="opt in filteredOptions"
                        :key="getItemValue(opt)"
                        type="button"
                        @click="selectOption(opt)"
                        :class="[
                            'w-full text-left px-2.5 py-1.5 rounded-lg text-xs flex items-center justify-between transition cursor-pointer',
                            isSelected(opt)
                                ? 'bg-amber-50 text-amber-900 font-bold'
                                : 'text-slate-700 hover:bg-slate-50 hover:text-slate-900 font-medium'
                        ]"
                    >
                        <span class="flex items-center gap-2 min-w-0">
                            <i v-if="icon" :class="[formattedIcon, iconColor, 'text-[10px] shrink-0']"></i>
                            <span class="truncate">{{ getItemLabel(opt) }}</span>
                        </span>
                        <i
                            v-if="isSelected(opt)"
                            class="fa-solid fa-check text-[10px] text-amber-600 shrink-0 ml-1.5"
                        ></i>
                    </button>

                    <!-- Loading State (remote) -->
                    <div
                        v-if="isFetching"
                        class="py-4 text-center text-slate-400 text-xs"
                    >
                        <i class="fa-solid fa-circle-notch fa-spin mr-1"></i> Mencari...
                    </div>

                    <!-- Empty State -->
                    <div
                        v-else-if="!filteredOptions.length"
                        class="py-4 text-center text-slate-400 text-xs"
                    >
                        <p class="font-medium">Tidak ada hasil ditemukan</p>
                    </div>
                </div>
            </div>
        </Transition>
    </div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(148, 163, 184, 0.4);
    border-radius: 9999px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: rgba(148, 163, 184, 0.7);
}
</style>
