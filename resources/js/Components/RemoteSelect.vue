<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue';

const props = defineProps({
    modelValue: { type: [String, Number], default: '' },
    relTable: { type: String, required: true },
    label: { type: String, default: '' },
    placeholder: { type: String, default: 'Pilih...' },
});

const emit = defineEmits(['update:modelValue']);

const isOpen = ref(false);
const searchQuery = ref('');
const options = ref([]);
const loading = ref(false);
const selectedLabel = ref('');
const containerRef = ref(null);
const searchInputRef = ref(null);
let debounce = null;

const baseUrl = `/admin/master-referensi/options/${props.relTable}`;

const load = (q = '') => {
    loading.value = true;
    const params = new URLSearchParams();
    if (q) params.set('q', q);
    if (props.modelValue !== '' && props.modelValue !== null && props.modelValue !== undefined) {
        params.set('selected', props.modelValue);
    }
    fetch(`${baseUrl}?${params.toString()}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' },
    })
        .then((r) => r.json())
        .then((data) => {
            options.value = data.options || [];
            const found = options.value.find((o) => String(o.value) === String(props.modelValue));
            selectedLabel.value = found ? found.label : props.modelValue !== '' ? String(props.modelValue) : '';
        })
        .catch(() => {
            options.value = [];
        })
        .finally(() => {
            loading.value = false;
        });
};

const toggle = () => {
    isOpen.value = !isOpen.value;
    if (isOpen.value) {
        searchQuery.value = '';
        load('');
        setTimeout(() => searchInputRef.value?.focus(), 50);
    }
};

watch(searchQuery, (v) => {
    clearTimeout(debounce);
    debounce = setTimeout(() => load(v), 250);
});

const select = (opt) => {
    emit('update:modelValue', opt.value);
    selectedLabel.value = opt.label;
    isOpen.value = false;
    searchQuery.value = '';
};

const clear = (e) => {
    e.stopPropagation();
    emit('update:modelValue', '');
    selectedLabel.value = '';
    options.value = [];
    searchQuery.value = '';
};

watch(
    () => props.modelValue,
    (val) => {
        if (val === '' || val === null || val === undefined) {
            selectedLabel.value = '';
        } else {
            const found = options.value.find((o) => String(o.value) === String(val));
            if (found) {
                selectedLabel.value = found.label;
            } else {
                load('');
            }
        }
    },
    { immediate: true }
);

const handleClickOutside = (e) => {
    if (containerRef.value && !containerRef.value.contains(e.target)) {
        isOpen.value = false;
    }
};

onMounted(() => document.addEventListener('click', handleClickOutside));
onBeforeUnmount(() => document.removeEventListener('click', handleClickOutside));

const display = computed(() => (selectedLabel.value ? selectedLabel.value : (props.placeholder || 'Pilih...')));
</script>

<template>
    <div ref="containerRef" class="relative inline-block w-full text-left text-xs select-none">
        <button
            type="button"
            @click="toggle"
            :class="[
                'h-9 px-3 rounded-xl border flex items-center justify-between gap-1.5 transition-all shadow-2xs cursor-pointer text-left w-full',
                isOpen ? 'bg-white border-amber-500 ring-2 ring-amber-500/15' : 'bg-slate-50 hover:bg-white border-slate-200 text-slate-700'
            ]"
        >
            <span :class="['truncate font-semibold', selectedLabel ? 'text-slate-900' : 'text-slate-400 font-medium']">
                {{ display }}
            </span>
            <div class="flex items-center gap-1 shrink-0">
                <span v-if="loading" class="text-[10px] text-amber-500"><i class="fa-solid fa-spinner fa-spin"></i></span>
                <span
                    v-if="selectedLabel"
                    @click="clear"
                    class="text-slate-400 hover:text-rose-600 transition p-0.5 rounded-md hover:bg-slate-100 cursor-pointer"
                >
                    <i class="fa-solid fa-xmark text-[10px]"></i>
                </span>
                <i :class="['fa-solid fa-chevron-down text-[9px] text-slate-400 transition-transform duration-200', isOpen ? 'rotate-180 text-amber-600' : '']"></i>
            </div>
        </button>

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
                class="absolute left-0 top-full mt-1.5 z-50 w-full min-w-0 max-w-[calc(100vw-2rem)] rounded-2xl bg-white border border-slate-200/90 shadow-xl shadow-slate-900/10 overflow-hidden"
            >
                <div class="p-2 border-b border-slate-100 bg-slate-50/70">
                    <div class="relative">
                        <i class="fa-solid fa-magnifying-glass absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400 text-[10px]"></i>
                        <input
                            ref="searchInputRef"
                            v-model="searchQuery"
                            type="text"
                            placeholder="Ketik untuk mencari..."
                            class="w-full pl-7 pr-2.5 py-1.5 text-xs rounded-lg bg-white border border-slate-200 text-slate-900 placeholder-slate-400 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500"
                        />
                    </div>
                </div>
                <div class="max-h-60 overflow-y-auto p-1.5 space-y-0.5 custom-scrollbar">
                    <button
                        v-for="opt in options"
                        :key="opt.value"
                        type="button"
                        @click="select(opt)"
                        :class="[
                            'w-full text-left px-2.5 py-1.5 rounded-lg text-xs flex items-center justify-between transition cursor-pointer',
                            String(opt.value) === String(modelValue) ? 'bg-amber-50 text-amber-900 font-bold' : 'text-slate-700 hover:bg-slate-50 hover:text-slate-900 font-medium'
                        ]"
                    >
                        <span class="truncate">{{ opt.label }}</span>
                        <i v-if="String(opt.value) === String(modelValue)" class="fa-solid fa-check text-[10px] text-amber-600 shrink-0 ml-1.5"></i>
                    </button>
                    <div v-if="!loading && !options.length" class="py-4 text-center text-slate-400 text-xs">
                        <p class="font-medium">Tidak ada hasil</p>
                    </div>
                    <div v-if="loading" class="py-4 text-center text-slate-400 text-xs">
                        <i class="fa-solid fa-spinner fa-spin"></i>
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
</style>
