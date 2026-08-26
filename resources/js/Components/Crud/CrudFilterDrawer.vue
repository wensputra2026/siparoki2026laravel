<script setup>
defineProps({
    isOpen: {
        type: Boolean,
        default: false,
    },
    title: {
        type: String,
        default: 'Filter Pencarian Data',
    },
});

defineEmits(['close', 'apply', 'reset']);
</script>

<template>
    <div v-if="isOpen" class="fixed inset-0 z-50 overflow-hidden">
        <!-- Backdrop -->
        <div 
            class="absolute inset-0 bg-slate-950/60 backdrop-blur-sm transition-opacity" 
            @click="$emit('close')"
        ></div>

        <div class="fixed inset-y-0 right-0 max-w-full flex pl-10">
            <div class="w-screen max-w-md bg-white dark:bg-slate-900 border-l border-slate-200 dark:border-slate-800 shadow-2xl flex flex-col justify-between">
                <!-- Header -->
                <div class="p-5 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-teal-500/10 text-teal-600 dark:text-teal-400 flex items-center justify-center text-sm">
                            <i class="fa-solid fa-sliders"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100">{{ title }}</h3>
                            <p class="text-[11px] text-slate-500">Saring data berdasarkan kriteria spesifik</p>
                        </div>
                    </div>
                    <button 
                        type="button" 
                        @click="$emit('close')" 
                        class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-500 text-xs flex items-center justify-center transition"
                    >
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <!-- Drawer Body (Slots for Filter Inputs) -->
                <div class="p-5 overflow-y-auto space-y-4 flex-1 custom-scrollbar">
                    <slot></slot>
                </div>

                <!-- Footer Actions -->
                <div class="p-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 flex items-center justify-between gap-3">
                    <button 
                        type="button" 
                        @click="$emit('reset')" 
                        class="px-4 py-2 rounded-xl bg-slate-200 dark:bg-slate-800 hover:bg-slate-300 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold transition"
                    >
                        <i class="fa-solid fa-rotate-left mr-1"></i> Reset
                    </button>
                    <button 
                        type="button" 
                        @click="$emit('apply')" 
                        class="flex-1 px-5 py-2.5 rounded-xl bg-gradient-to-r from-teal-600 to-teal-500 hover:from-teal-500 hover:to-teal-400 text-white text-xs font-bold shadow-lg shadow-teal-600/20 transition flex items-center justify-center gap-2"
                    >
                        <i class="fa-solid fa-check"></i>
                        <span>Terapkan Filter</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
