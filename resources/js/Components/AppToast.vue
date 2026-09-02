<script setup>
import { computed } from 'vue';

const props = defineProps({
    show: { type: Boolean, default: false },
    message: { type: String, default: '' },
    type: { type: String, default: 'success' },
    persistent: { type: Boolean, default: false },
});

defineEmits(['close']);

const isRelationNotice = computed(() => {
    const text = String(props.message || '').toLowerCase();
    return (
        text.includes('terhubung dengan') ||
        text.includes('data terkait') ||
        text.includes('data turunan') ||
        text.includes('relasi data') ||
        text.includes('anggota keluarga') ||
        text.includes('tidak dapat dihapus') ||
        text.includes('dilewati karena') ||
        text.includes('konten aktif') ||
        text.includes('masih memiliki') ||
        text.includes('pindahkan atau') ||
        text.includes('dibatalkan karena') ||
        text.includes('foreign key') ||
        text.includes('integritas data')
    );
});

const isStayOpen = computed(() => {
    return props.persistent || isRelationNotice.value || props.type === 'warning' || props.type === 'error' || (props.message && props.message.length > 110);
});

const headerTitle = computed(() => {
    if (isRelationNotice.value) {
        return 'Pemberitahuan Relasi & Integritas Data';
    }
    if (props.type === 'warning') return 'Peringatan Sistem';
    if (props.type === 'error') return 'Pemberitahuan Kesalahan';
    if (props.type === 'info') return 'Informasi Sistem';
    return 'Pemberitahuan Sistem';
});

const containerStyle = computed(() => {
    if (props.type === 'warning' || (props.type === 'success' && isRelationNotice.value)) {
        return 'bg-amber-50/98 border-amber-300 text-amber-950 shadow-amber-500/15 ring-1 ring-amber-400/30';
    }
    if (props.type === 'error') {
        return 'bg-rose-50/98 border-rose-300 text-rose-950 shadow-rose-500/15 ring-1 ring-rose-400/30';
    }
    if (props.type === 'info') {
        return 'bg-cyan-50/98 border-cyan-300 text-cyan-950 shadow-cyan-500/15 ring-1 ring-cyan-400/30';
    }
    return 'bg-emerald-50/98 border-emerald-300 text-emerald-950 shadow-emerald-500/15 ring-1 ring-emerald-400/30';
});

const iconStyle = computed(() => {
    if (props.type === 'warning' || (props.type === 'success' && isRelationNotice.value)) {
        return 'bg-amber-500 text-white shadow-xs';
    }
    if (props.type === 'error') {
        return 'bg-rose-500 text-white shadow-xs';
    }
    if (props.type === 'info') {
        return 'bg-cyan-500 text-white shadow-xs';
    }
    return 'bg-emerald-500 text-white shadow-xs';
});

const iconClass = computed(() => {
    if (isRelationNotice.value) return 'fa-solid fa-shield-halved';
    if (props.type === 'warning') return 'fa-solid fa-triangle-exclamation';
    if (props.type === 'error') return 'fa-solid fa-circle-xmark';
    if (props.type === 'info') return 'fa-solid fa-circle-info';
    return 'fa-solid fa-circle-check';
});
</script>

<template>
    <Transition
        enter-active-class="transform ease-out duration-300 transition"
        enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-3"
        enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
        leave-active-class="transition ease-in duration-200"
        leave-from-class="opacity-100 scale-100"
        leave-to-class="opacity-0 scale-95"
    >
        <div
            v-if="show"
            class="fixed top-16 sm:top-20 right-4 sm:right-6 z-[99999] max-w-md sm:max-w-lg w-[calc(100vw-2rem)] shadow-2xl rounded-2xl p-4 sm:p-4.5 border backdrop-blur-md transition-all duration-200"
            :class="containerStyle"
            role="alert"
        >
            <div class="flex items-start gap-3">
                <!-- Icon -->
                <div
                    class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 text-base font-bold transition-transform"
                    :class="iconStyle"
                >
                    <i :class="iconClass"></i>
                </div>

                <!-- Content Area -->
                <div class="flex-1 min-w-0 pt-0.5 space-y-1.5">
                    <div class="flex items-center justify-between gap-2 flex-wrap">
                        <h4 class="text-xs sm:text-sm font-black tracking-tight flex items-center gap-1.5">
                            <span>{{ headerTitle }}</span>
                        </h4>

                        <!-- Persistent badge -->
                        <span
                            v-if="isStayOpen"
                            class="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded-md bg-white/90 border border-slate-200/90 text-slate-700 shadow-2xs"
                            title="Pemberitahuan ini tidak otomatis menghilang agar dapat dibaca dengan lengkap"
                        >
                            <i class="fa-solid fa-thumbtack text-[9px] text-amber-600"></i>
                            <span>Tetap Tampil</span>
                        </span>
                    </div>

                    <p class="text-xs sm:text-[12.5px] leading-relaxed opacity-95 text-slate-800 font-medium whitespace-pre-line break-words">
                        {{ message }}
                    </p>

                    <!-- Bottom Dismiss Button for Persistent/Long Messages -->
                    <div v-if="isStayOpen" class="pt-2 flex items-center justify-end gap-2">
                        <button
                            type="button"
                            @click="$emit('close')"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition shadow-xs cursor-pointer active:scale-95"
                        >
                            <i class="fa-solid fa-check text-[11px]"></i>
                            <span>Saya Mengerti / Tutup</span>
                        </button>
                    </div>
                </div>

                <!-- Top Right Close X Button -->
                <button
                    type="button"
                    @click="$emit('close')"
                    class="w-7 h-7 rounded-lg bg-black/5 hover:bg-black/10 text-slate-500 hover:text-slate-800 flex items-center justify-center text-xs transition cursor-pointer shrink-0 -mr-1 -mt-1"
                    title="Tutup pemberitahuan"
                >
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>
        </div>
    </Transition>
</template>
