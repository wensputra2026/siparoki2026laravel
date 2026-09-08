<script setup>
import { computed } from 'vue';

const props = defineProps({
    show: { type: Boolean, default: false },
    title: { type: String, default: 'Konfirmasi Tindakan' },
    message: { type: String, default: 'Apakah Anda yakin ingin melanjutkan tindakan ini?' },
    confirmText: { type: String, default: 'Ya, Lanjutkan' },
    cancelText: { type: String, default: 'Batal' },
    type: { type: String, default: 'danger' }, // 'danger' | 'warning' | 'info' | 'success'
    loading: { type: Boolean, default: false },
    icon: { type: String, default: '' },
});

const emit = defineEmits(['confirm', 'cancel', 'close']);

const iconClass = computed(() => {
    if (props.icon) return props.icon;
    switch (props.type) {
        case 'danger':
            return 'fa-trash-can';
        case 'warning':
            return 'fa-triangle-exclamation';
        case 'success':
            return 'fa-check';
        case 'info':
        default:
            return 'fa-circle-question';
    }
});

const badgeClasses = computed(() => {
    switch (props.type) {
        case 'danger':
            return 'bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 border-rose-200 dark:border-rose-900/50';
        case 'warning':
            return 'bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 border-amber-200 dark:border-amber-900/50';
        case 'success':
            return 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 border-emerald-200 dark:border-emerald-900/50';
        case 'info':
        default:
            return 'bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 border-blue-200 dark:border-blue-900/50';
    }
});

const confirmBtnClasses = computed(() => {
    switch (props.type) {
        case 'danger':
            return 'bg-rose-600 hover:bg-rose-700 text-white shadow-rose-600/30';
        case 'warning':
            return 'bg-amber-500 hover:bg-amber-600 text-white shadow-amber-500/30';
        case 'success':
            return 'bg-emerald-600 hover:bg-emerald-700 text-white shadow-emerald-600/30';
        case 'info':
        default:
            return 'bg-blue-600 hover:bg-blue-700 text-white shadow-blue-600/30';
    }
});
</script>

<template>
    <Transition
        enter-active-class="ease-out duration-200"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="ease-in duration-150"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div
            v-if="show"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/40 backdrop-blur-xs"
            @click.self="!loading && emit('cancel')"
        >
            <div
                @click.stop
                class="bg-white dark:bg-slate-900 rounded-3xl p-6 sm:p-7 max-w-md w-full shadow-2xl border border-slate-200/80 dark:border-slate-800 text-center space-y-5 animate-in fade-in zoom-in-95 duration-200"
            >
                <!-- Badge Icon -->
                <div :class="['w-14 h-14 rounded-2xl border mx-auto flex items-center justify-center text-2xl shadow-sm', badgeClasses]">
                    <i :class="['fa-solid', iconClass]"></i>
                </div>

                <!-- Title & Content -->
                <div class="space-y-1.5">
                    <h3 class="text-base sm:text-lg font-black text-slate-900 dark:text-white tracking-tight">{{ title }}</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed max-w-sm mx-auto" v-html="message"></p>
                    <slot name="extra" />
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-center gap-3 pt-2">
                    <button
                        type="button"
                        :disabled="loading"
                        @click="emit('cancel')"
                        class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold transition cursor-pointer disabled:opacity-50"
                    >
                        {{ cancelText }}
                    </button>
                    <button
                        type="button"
                        :disabled="loading"
                        @click="emit('confirm')"
                        :class="['px-5 py-2.5 rounded-xl text-xs font-bold shadow-sm transition flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50', confirmBtnClasses]"
                    >
                        <i v-if="loading" class="fa-solid fa-spinner fa-spin text-xs"></i>
                        <i v-else-if="props.type === 'danger'" class="fa-solid fa-trash-can text-xs"></i>
                        <i v-else-if="props.type === 'warning'" class="fa-solid fa-triangle-exclamation text-xs"></i>
                        <i v-else-if="props.type === 'success'" class="fa-solid fa-check text-xs"></i>
                        <span>{{ confirmText }}</span>
                    </button>
                </div>
            </div>
        </div>
    </Transition>
</template>
