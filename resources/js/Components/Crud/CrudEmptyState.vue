<script setup>
defineProps({
    title: {
        type: String,
        default: 'Belum Ada Data Tersedia',
    },
    description: {
        type: String,
        default: 'Belum ada data pada modul ini atau tidak ada data yang cocok dengan kriteria filter pencarian Anda.',
    },
    actionLabel: {
        type: String,
        default: '',
    },
    icon: {
        type: String,
        default: 'fa-folder-open',
    },
});

defineEmits(['action', 'resetFilter']);
</script>

<template>
    <div class="flex flex-col items-center justify-center py-16 px-6 text-center bg-slate-50/50 dark:bg-slate-900/40 border border-dashed border-slate-200 dark:border-slate-800 rounded-3xl my-4 transition-all">
        <!-- Liturgical / Pastoral Illustration Icon -->
        <div class="w-16 h-16 rounded-2xl bg-amber-500/10 dark:bg-amber-500/20 border border-amber-500/30 flex items-center justify-center text-2xl text-amber-600 dark:text-amber-400 shadow-lg shadow-amber-500/10 mb-4 animate-bounce">
            <i class="fa-solid" :class="icon"></i>
        </div>

        <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 tracking-tight mb-1">
            {{ title }}
        </h3>
        <p class="text-xs text-slate-500 dark:text-slate-400 max-w-md leading-relaxed mb-6">
            {{ description }}
        </p>

        <div class="flex items-center gap-3">
            <button 
                type="button" 
                @click="$emit('resetFilter')" 
                class="px-4 py-2 rounded-xl bg-slate-200 dark:bg-slate-800 hover:bg-slate-300 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold transition"
            >
                <i class="fa-solid fa-rotate-left mr-1.5 text-slate-400"></i> Reset Filter
            </button>
            <button 
                v-if="actionLabel" 
                type="button" 
                @click="$emit('action')" 
                class="px-5 py-2 rounded-xl bg-gradient-to-r from-teal-600 to-teal-500 hover:from-teal-500 hover:to-teal-400 text-white text-xs font-bold shadow-lg shadow-teal-600/20 transition flex items-center gap-2"
            >
                <i class="fa-solid fa-plus"></i>
                <span>{{ actionLabel }}</span>
            </button>
        </div>
    </div>
</template>
