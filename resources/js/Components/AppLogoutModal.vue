<script setup>
defineProps({
    show: { type: Boolean, default: false },
});
defineEmits(['confirm', 'cancel']);
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
        >
            <div
                @click.stop
                class="bg-white rounded-3xl p-6 sm:p-7 max-w-md w-full shadow-2xl border border-slate-200/80 text-center space-y-5 animate-in fade-in zoom-in-95 duration-200"
            >
                <div class="w-14 h-14 rounded-2xl bg-amber-50 text-amber-600 border border-amber-200 mx-auto flex items-center justify-center text-2xl shadow-sm">
                    <i class="fa-solid fa-door-open"></i>
                </div>

                <div class="space-y-1.5">
                    <h3 class="text-lg font-black text-slate-900 tracking-tight">Konfirmasi Keluar Aplikasi</h3>
                    <p class="text-xs text-slate-500 leading-relaxed max-w-sm mx-auto">
                        Apakah Anda yakin ingin mengakhiri sesi Anda saat ini di sistem SIPAROKI?
                    </p>
                </div>

                <div class="flex items-center justify-center gap-3 pt-2">
                    <button
                        type="button"
                        @click="$emit('cancel')"
                        class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition cursor-pointer"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        @click="$emit('confirm')"
                        class="px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold shadow-sm shadow-rose-600/30 transition flex items-center gap-2 cursor-pointer"
                    >
                        <i class="fa-solid fa-arrow-right-from-bracket text-xs"></i>
                        <span>Ya, Keluar Sekarang</span>
                    </button>
                </div>
            </div>
        </div>
    </Transition>
</template>
