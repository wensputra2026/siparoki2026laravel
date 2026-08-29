<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import axios from 'axios';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

const page = usePage();
const authUser = computed(() => page.props.auth?.user || null);

const isOpen = ref(false);
const notifications = ref([]);
const unreadCount = ref(0);
const latestId = ref(0);
const toastNotification = ref(null);
let toastTimer = null;
let pollTimer = null;
let echoInstance = null;

let audioCtx = null;

const unlockAudio = () => {
    try {
        if (!audioCtx) {
            audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        }
        if (audioCtx.state === 'suspended') {
            audioCtx.resume();
        }
    } catch (e) {}
};

// Rich 3-tone resonant bell chime for incoming notifications
const playChime = () => {
    try {
        unlockAudio();
        if (!audioCtx) return;

        const now = audioCtx.currentTime;

        const playTone = (freq, startTime, duration, vol = 0.25) => {
            const osc = audioCtx.createOscillator();
            const gain = audioCtx.createGain();
            osc.type = 'triangle'; // rich bell harmonic tone
            osc.frequency.setValueAtTime(freq, startTime);
            gain.gain.setValueAtTime(vol, startTime);
            gain.gain.exponentialRampToValueAtTime(0.0001, startTime + duration);
            osc.connect(gain);
            gain.connect(audioCtx.destination);
            osc.start(startTime);
            osc.stop(startTime + duration);
        };

        // 3-Tone church chime: E5 (659.25Hz) -> B5 (987.77Hz) -> E6 (1318.51Hz)
        playTone(659.25, now, 0.45, 0.22);
        playTone(987.77, now + 0.12, 0.55, 0.25);
        playTone(1318.51, now + 0.25, 0.75, 0.28);
    } catch (e) {
        // Audio playback error fallback
    }
};

const showIncomingToast = (notif) => {
    toastNotification.value = notif;
    playChime();
    if (toastTimer) clearTimeout(toastTimer);
    toastTimer = setTimeout(() => {
        toastNotification.value = null;
    }, 6500);
};

const fetchNotifications = async () => {
    try {
        const res = await axios.get('/api/notifikasi/list');
        notifications.value = res.data.notifications || [];
        unreadCount.value = res.data.unread_count || 0;
        if (notifications.value.length > 0) {
            latestId.value = Math.max(...notifications.value.map(n => n.id));
        }
    } catch (err) {
        // silent
    }
};

const pollNewNotifications = async () => {
    if (typeof document !== 'undefined' && document.visibilityState !== 'visible') return;
    try {
        const res = await axios.get('/api/notifikasi/poll', {
            params: { last_id: latestId.value }
        });
        if (res.data.new_items && res.data.new_items.length > 0) {
            res.data.new_items.forEach((item) => {
                if (!notifications.value.some(n => n.id === item.id)) {
                    notifications.value.unshift(item);
                    showIncomingToast(item);
                }
            });
            unreadCount.value = res.data.unread_count || 0;
            latestId.value = res.data.latest_id || latestId.value;
        }
    } catch (err) {
        // silent
    }
};

const markAsRead = async (notif) => {
    try {
        if (!notif.is_read) {
            await axios.post(`/api/notifikasi/mark-read/${notif.id}`);
            notif.is_read = true;
            unreadCount.value = Math.max(0, unreadCount.value - 1);
        }
        if (notif.link) {
            isOpen.value = false;
            router.visit(notif.link);
        }
    } catch (e) {}
};

const markAllAsRead = async () => {
    try {
        await axios.post('/api/notifikasi/mark-read/all');
        notifications.value.forEach(n => n.is_read = true);
        unreadCount.value = 0;
    } catch (e) {}
};

const formatTime = (dateStr) => {
    if (!dateStr) return '';
    try {
        const date = new Date(dateStr);
        const now = new Date();
        const diffSec = Math.floor((now - date) / 1000);
        if (diffSec < 60) return 'Baru saja';
        if (diffSec < 3600) return `${Math.floor(diffSec / 60)} mnt lalu`;
        if (diffSec < 86400) return `${Math.floor(diffSec / 3600)} jam lalu`;
        return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
    } catch (e) {
        return dateStr;
    }
};

// Initialize Laravel Reverb Echo connection (only if VITE_ENABLE_REVERB is true)
const initEcho = () => {
    if (import.meta.env.VITE_ENABLE_REVERB !== 'true') {
        return;
    }

    try {
        const key = import.meta.env.VITE_REVERB_APP_KEY || 'gw9ybkigi8nzivwwkcwi';
        const host = import.meta.env.VITE_REVERB_HOST || '127.0.0.1';
        const port = import.meta.env.VITE_REVERB_PORT || 8080;
        const scheme = import.meta.env.VITE_REVERB_SCHEME || 'http';

        Pusher.logToConsole = false;
        window.Pusher = Pusher;

        echoInstance = new Echo({
            broadcaster: 'reverb',
            key: key,
            wsHost: host,
            wsPort: port,
            wssPort: port,
            forceTLS: scheme === 'https',
            enabledTransports: ['ws', 'wss'],
            disableStats: true,
        });

        const user = authUser.value;
        const channel = echoInstance.channel('siparoki-notifications');
        channel.listen('.UmatMutasiEvent', (e) => {
            const payload = e.payload || e;
            const isMatch = !user || user.role_id === 1 || 
                (payload.kub_tujuan_id && user.kub_id == payload.kub_tujuan_id) ||
                (payload.wilayah_tujuan_id && user.wilayah_id == payload.wilayah_tujuan_id) ||
                (payload.kapela_tujuan_id && user.kapela_id == payload.kapela_tujuan_id);

            if (isMatch) {
                const newNotif = {
                    id: payload.id || Date.now(),
                    judul: payload.judul || 'Umat Baru Masuk ke KUB',
                    pesan: payload.pesan,
                    tipe: payload.tipe || 'mutasi_masuk',
                    link: '/kub/umat',
                    is_read: false,
                    created_at: payload.created_at || new Date().toISOString(),
                };
                if (!notifications.value.some(n => n.id === newNotif.id)) {
                    notifications.value.unshift(newNotif);
                    unreadCount.value++;
                    showIncomingToast(newNotif);
                }
            }
        });

        if (user?.kub_id) {
            echoInstance.channel(`kub.${user.kub_id}`).listen('.UmatMutasiEvent', (e) => {
                const payload = e.payload || e;
                const newNotif = {
                    id: payload.id || Date.now(),
                    judul: payload.judul || 'Umat Baru Masuk ke KUB Anda',
                    pesan: payload.pesan,
                    tipe: 'mutasi_masuk',
                    link: '/kub/umat',
                    is_read: false,
                    created_at: payload.created_at || new Date().toISOString(),
                };
                if (!notifications.value.some(n => n.id === newNotif.id)) {
                    notifications.value.unshift(newNotif);
                    unreadCount.value++;
                    showIncomingToast(newNotif);
                }
            });
        }
    } catch (e) {
        // Silently fallback to polling
    }
};

onMounted(() => {
    fetchNotifications();
    initEcho();
    
    // Unlock AudioContext on first user interaction anywhere on the page
    const handleFirstGesture = () => {
        unlockAudio();
        window.removeEventListener('click', handleFirstGesture);
        window.removeEventListener('touchstart', handleFirstGesture);
        window.removeEventListener('keydown', handleFirstGesture);
    };
    window.addEventListener('click', handleFirstGesture, { passive: true });
    window.addEventListener('touchstart', handleFirstGesture, { passive: true });
    window.addEventListener('keydown', handleFirstGesture, { passive: true });

    // Efficient polling every 15 seconds only when tab is active
    pollTimer = setInterval(pollNewNotifications, 15000);
});

onUnmounted(() => {
    if (pollTimer) clearInterval(pollTimer);
    if (echoInstance) {
        try { echoInstance.disconnect(); } catch (e) {}
    }
});
</script>

<template>
    <div class="relative">
        <!-- Bell Trigger Button -->
        <button
            type="button"
            @click="unlockAudio(); isOpen = !isOpen"
            class="relative inline-flex items-center justify-center w-9 h-9 rounded-xl bg-slate-100 hover:bg-amber-50 hover:text-amber-800 text-slate-700 transition cursor-pointer border border-slate-200/80 shadow-2xs"
            title="Notifikasi Real-time"
        >
            <i class="fa-solid fa-bell text-sm" :class="{ 'text-amber-600': unreadCount > 0 }"></i>
            
            <!-- Pulse Animation on Unread -->
            <span
                v-if="unreadCount > 0"
                class="absolute -top-1 -right-1 flex h-4 min-w-[16px] px-1 items-center justify-center rounded-full bg-rose-500 text-[9px] font-black text-white shadow-xs animate-pulse"
            >
                {{ unreadCount > 9 ? '9+' : unreadCount }}
            </span>
        </button>

        <!-- Notification Dropdown Panel -->
        <div
            v-if="isOpen"
            class="fixed inset-0 z-40"
            @click="isOpen = false"
        ></div>

        <transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="transform scale-95 opacity-0"
            enter-to-class="transform scale-100 opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="transform scale-100 opacity-100"
            leave-to-class="transform scale-95 opacity-0"
        >
            <div
                v-if="isOpen"
                class="fixed inset-x-2 top-16 sm:absolute sm:right-0 sm:top-auto sm:inset-x-auto sm:mt-2 w-auto sm:w-96 rounded-2xl bg-white border border-slate-200 shadow-2xl z-50 overflow-hidden flex flex-col max-h-[82vh] sm:max-h-[460px]"
            >
                <!-- Header -->
                <div class="px-4 py-3 bg-gradient-to-r from-slate-900 via-slate-800 to-amber-950 text-white flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-bell text-amber-400 text-xs"></i>
                        <span class="font-bold text-xs">Notifikasi Real-time</span>
                        <span
                            v-if="unreadCount > 0"
                            class="px-1.5 py-0.5 rounded-full bg-amber-500/30 text-amber-300 text-[10px] font-extrabold"
                        >
                            {{ unreadCount }} baru
                        </span>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <button
                            type="button"
                            @click="playChime"
                            class="text-[10px] text-amber-300 hover:text-white px-2 py-0.5 rounded-md bg-white/10 hover:bg-white/20 transition flex items-center gap-1 cursor-pointer font-semibold"
                            title="Klik untuk uji coba suara bel"
                        >
                            <i class="fa-solid fa-volume-high text-[9px]"></i>
                            <span>Tes Suara</span>
                        </button>
                        <button
                            v-if="unreadCount > 0"
                            type="button"
                            @click="markAllAsRead"
                            class="text-[10px] text-amber-300 hover:text-amber-100 underline font-medium cursor-pointer"
                        >
                            Tandai dibaca
                        </button>
                    </div>
                </div>

                <!-- Notification List -->
                <div class="flex-1 overflow-y-auto divide-y divide-slate-100 custom-scrollbar max-h-[380px]">
                    <template v-if="notifications.length > 0">
                        <div
                            v-for="notif in notifications"
                            :key="notif.id"
                            @click="markAsRead(notif)"
                            class="p-3.5 hover:bg-amber-50/50 transition cursor-pointer flex items-start gap-3"
                            :class="{ 'bg-amber-500/5 font-medium': !notif.is_read }"
                        >
                            <!-- Icon Badge -->
                            <div
                                class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 text-xs shadow-xs"
                                :class="[
                                    notif.tipe === 'mutasi_masuk' ? 'bg-emerald-500 text-white' :
                                    notif.tipe === 'kematian' ? 'bg-rose-500 text-white' : 'bg-amber-500 text-white'
                                ]"
                            >
                                <i
                                    class="fa-solid"
                                    :class="[
                                        notif.tipe === 'mutasi_masuk' ? 'fa-user-plus' :
                                        notif.tipe === 'kematian' ? 'fa-book-skull' : 'fa-bell'
                                    ]"
                                ></i>
                            </div>

                            <!-- Text Content -->
                            <div class="flex-1 min-w-0 text-xs">
                                <div class="flex items-center justify-between gap-1 mb-0.5">
                                    <h5 class="font-bold text-slate-900 truncate" :class="{ 'text-amber-900': !notif.is_read }">
                                        {{ notif.judul }}
                                    </h5>
                                    <span class="text-[10px] text-slate-400 shrink-0 font-normal">
                                        {{ formatTime(notif.created_at) }}
                                    </span>
                                </div>
                                <p class="text-slate-600 text-[11px] leading-relaxed line-clamp-2">
                                    {{ notif.pesan }}
                                </p>
                            </div>

                            <!-- Unread Indicator Dot -->
                            <div v-if="!notif.is_read" class="w-2 h-2 rounded-full bg-amber-500 shrink-0 mt-1"></div>
                        </div>
                    </template>

                    <!-- Empty State -->
                    <div v-else class="p-8 text-center text-slate-400">
                        <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-2 text-lg">
                            <i class="fa-regular fa-bell-slash"></i>
                        </div>
                        <p class="text-xs font-semibold text-slate-600">Belum ada notifikasi</p>
                        <p class="text-[11px] text-slate-400 mt-0.5">Pemberitahuan mutasi umat baru akan muncul di sini secara realtime.</p>
                    </div>
                </div>
            </div>
        </transition>

        <!-- Floating Real-time Toast Popup Alert -->
        <teleport to="body">
            <transition
                enter-active-class="transform transition ease-out duration-300"
                enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-4"
                enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
                leave-active-class="transition ease-in duration-200"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div
                    v-if="toastNotification"
                    class="fixed top-5 right-5 z-[9999] max-w-sm w-full bg-slate-900/95 backdrop-blur-md text-white rounded-2xl p-4 shadow-2xl border border-amber-500/30 flex items-start gap-3 cursor-pointer"
                    @click="markAsRead(toastNotification)"
                >
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-emerald-600 to-emerald-400 text-white flex items-center justify-center shrink-0 shadow-lg shadow-emerald-500/30 text-base">
                        <i class="fa-solid fa-user-plus"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between">
                            <h4 class="font-black text-xs text-amber-400 tracking-wide uppercase">
                                {{ toastNotification.judul }}
                            </h4>
                            <span class="text-[10px] text-slate-400">Baru saja</span>
                        </div>
                        <p class="text-xs text-slate-200 mt-1 leading-snug">
                            {{ toastNotification.pesan }}
                        </p>
                        <div class="mt-2 text-[11px] text-emerald-300 font-bold flex items-center gap-1.5">
                            <span>Buka Data Umat KUB</span>
                            <i class="fa-solid fa-arrow-right text-[10px]"></i>
                        </div>
                    </div>
                    <button
                        type="button"
                        @click.stop="toastNotification = null"
                        class="text-slate-400 hover:text-white text-xs p-1"
                    >
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            </transition>
        </teleport>
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
    background: #cbd5e1;
    border-radius: 4px;
}
</style>
