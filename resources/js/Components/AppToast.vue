<script setup>
defineProps({
    show: { type: Boolean, default: false },
    message: { type: String, default: '' },
    type: { type: String, default: 'success' },
});
defineEmits(['close']);
</script>

<template>
    <Transition
        enter-active-class="transform ease-out duration-300 transition"
        enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
        enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
        leave-active-class="transition ease-in duration-200"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div
            v-if="show"
            class="fixed top-20 right-4 sm:right-6 z-50 max-w-sm w-full shadow-lg rounded-2xl p-4 flex items-start gap-3 border backdrop-blur-md"
            :class="[
                type === 'success'
                    ? 'bg-emerald-50/95 border-emerald-200 text-emerald-900 shadow-emerald-500/10'
                    : 'bg-rose-50/95 border-rose-200 text-rose-900 shadow-rose-500/10'
            ]"
        >
            <div
                class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 text-sm font-bold"
                :class="[
                    type === 'success'
                        ? 'bg-emerald-500 text-white shadow-xs'
                        : 'bg-rose-500 text-white shadow-xs'
                ]"
            >
                <i :class="type === 'success' ? 'fa-solid fa-check' : 'fa-solid fa-triangle-exclamation'"></i>
            </div>
            <div class="flex-1 min-w-0 pt-0.5">
                <h4 class="text-xs font-bold capitalize">
                    {{ type === 'success' ? 'Pemberitahuan Sistem' : 'Peringatan' }}
                </h4>
                <p class="text-xs mt-0.5 leading-relaxed opacity-90">
                    {{ message }}
                </p>
            </div>
            <button
                @click="$emit('close')"
                class="text-slate-400 hover:text-slate-700 p-1 text-xs cursor-pointer"
            >
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
    </Transition>
</template>
