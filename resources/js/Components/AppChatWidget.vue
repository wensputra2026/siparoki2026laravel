<script setup>
import { ref, onMounted, onUnmounted, computed, nextTick, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';

const page = usePage();
const authUser = computed(() => page.props.auth?.user || null);

const isOpen = ref(false);
const contacts = ref([]);
const totalUnread = ref(0);
const search = ref('');
const isLoadingContacts = ref(false);

const activeContact = ref(null);
const messages = ref([]);
const isLoadingMessages = ref(false);
const messageText = ref('');
const isSending = ref(false);
const messagesContainer = ref(null);

// Attachments & Screenshot Paste
const attachmentPreview = ref(null);
const attachmentBase64 = ref(null);
const attachmentFile = ref(null);
const fileInputRef = ref(null);
const zoomImageSrc = ref(null);

let pollTimer = null;
let latestMessageId = 0;
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

// Subtle 2-tone chime for incoming chat message
const playChatChime = () => {
    try {
        unlockAudio();
        if (!audioCtx) return;
        const now = audioCtx.currentTime;

        const osc = audioCtx.createOscillator();
        const gain = audioCtx.createGain();
        osc.type = 'sine';
        osc.frequency.setValueAtTime(523.25, now); // C5
        osc.frequency.setValueAtTime(659.25, now + 0.1); // E5
        gain.gain.setValueAtTime(0.2, now);
        gain.gain.exponentialRampToValueAtTime(0.0001, now + 0.35);
        osc.connect(gain);
        gain.connect(audioCtx.destination);
        osc.start(now);
        osc.stop(now + 0.35);
    } catch (e) {}
};

const scrollToBottom = () => {
    nextTick(() => {
        if (messagesContainer.value) {
            messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
        }
    });
};

const fetchContacts = async (showLoading = false) => {
    if (showLoading) isLoadingContacts.value = true;
    try {
        const res = await axios.get('/api/chat/contacts', {
            params: { search: search.value }
        });
        contacts.value = res.data.contacts || [];
        totalUnread.value = res.data.total_unread || 0;
    } catch (e) {
    } finally {
        if (showLoading) isLoadingContacts.value = false;
    }
};

const selectContact = async (contact) => {
    activeContact.value = contact;
    isLoadingMessages.value = true;
    messages.value = [];
    clearAttachment();
    try {
        const res = await axios.get(`/api/chat/messages/${contact.id}`);
        messages.value = res.data.messages || [];
        if (res.data.recipient) {
            activeContact.value = { ...contact, ...res.data.recipient };
        }
        totalUnread.value = Math.max(0, totalUnread.value - (contact.unread_count || 0));
        contact.unread_count = 0;
        scrollToBottom();
    } catch (e) {
    } finally {
        isLoadingMessages.value = false;
    }
};

const closeChat = () => {
    activeContact.value = null;
    messages.value = [];
    clearAttachment();
    fetchContacts();
};

const clearAttachment = () => {
    attachmentPreview.value = null;
    attachmentBase64.value = null;
    attachmentFile.value = null;
    if (fileInputRef.value) fileInputRef.value.value = '';
};

// High-speed client-side image/screenshot compressor (< 150KB, max 1280px)
const compressImage = (dataUrl, maxWidth = 1280, quality = 0.82) => {
    return new Promise((resolve) => {
        const img = new Image();
        img.onload = () => {
            let width = img.width;
            let height = img.height;

            if (width > maxWidth) {
                height = Math.round((height * maxWidth) / width);
                width = maxWidth;
            }

            const canvas = document.createElement('canvas');
            canvas.width = width;
            canvas.height = height;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(img, 0, 0, width, height);

            const compressed = canvas.toDataURL('image/jpeg', quality);
            resolve(compressed);
        };
        img.onerror = () => resolve(dataUrl);
        img.src = dataUrl;
    });
};

// Handle Clipboard Paste Screenshot (Ctrl + V)
const handlePaste = (e) => {
    const items = (e.clipboardData || e.originalEvent?.clipboardData)?.items;
    if (!items) return;
    for (let i = 0; i < items.length; i++) {
        if (items[i].type.indexOf('image') !== -1) {
            const blob = items[i].getAsFile();
            const reader = new FileReader();
            reader.onload = async (event) => {
                const compressed = await compressImage(event.target.result);
                attachmentPreview.value = compressed;
                attachmentBase64.value = compressed;
                attachmentFile.value = null;
            };
            reader.readAsDataURL(blob);
            e.preventDefault();
            break;
        }
    }
};

// Handle File Input Selection
const onFileSelected = (e) => {
    const file = e.target.files?.[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = async (event) => {
        const compressed = await compressImage(event.target.result);
        attachmentPreview.value = compressed;
        attachmentBase64.value = compressed;
        attachmentFile.value = null;
    };
    reader.readAsDataURL(file);
};

const sendMessage = async () => {
    const text = messageText.value.trim();
    const hasAttachment = Boolean(attachmentPreview.value);
    if ((!text && !hasAttachment) || !activeContact.value || isSending.value) return;

    const tempId = Date.now();
    const optimisticMsg = {
        id: tempId,
        pengirim_id: authUser.value?.id,
        penerima_id: activeContact.value.id,
        is_me: true,
        pesan: text,
        lampiran: attachmentPreview.value,
        is_read: false,
        time_formatted: new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }),
    };

    messages.value.push(optimisticMsg);
    
    // Save current attachment for payload then clear local inputs
    const payloadBase64 = attachmentBase64.value;
    const payloadFile = attachmentFile.value;
    messageText.value = '';
    clearAttachment();
    scrollToBottom();
    isSending.value = true;

    try {
        let res;
        if (payloadFile) {
            const formData = new FormData();
            formData.append('penerima_id', activeContact.value.id);
            formData.append('pesan', text);
            formData.append('lampiran_file', payloadFile);
            res = await axios.post('/api/chat/send', formData, {
                headers: { 'Content-Type': 'multipart/form-data' }
            });
        } else {
            res = await axios.post('/api/chat/send', {
                penerima_id: activeContact.value.id,
                pesan: text,
                lampiran_base64: payloadBase64,
            });
        }

        if (res.data.success && res.data.message) {
            const idx = messages.value.findIndex(m => m.id === tempId);
            if (idx !== -1) {
                messages.value[idx] = res.data.message;
            }
        }
        fetchContacts();
    } catch (e) {
        messages.value = messages.value.filter(m => m.id !== tempId);
    } finally {
        isSending.value = false;
    }
};

const handleKeyDown = (e) => {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        sendMessage();
    }
};

const pollNewChats = async () => {
    if (typeof document !== 'undefined' && document.visibilityState !== 'visible') return;
    try {
        const res = await axios.get('/api/chat/poll', {
            params: { last_id: latestMessageId }
        });

        if (res.data.latest_id) {
            latestMessageId = res.data.latest_id;
        }

        totalUnread.value = res.data.total_unread || 0;

        if (res.data.new_messages && res.data.new_messages.length > 0) {
            let hasIncomingForActive = false;

            res.data.new_messages.forEach((newMsg) => {
                if (activeContact.value && newMsg.pengirim_id === activeContact.value.id) {
                    if (!messages.value.some(m => m.id === newMsg.id)) {
                        messages.value.push({
                            id: newMsg.id,
                            pengirim_id: newMsg.pengirim_id,
                            penerima_id: newMsg.penerima_id,
                            is_me: false,
                            pesan: newMsg.pesan,
                            lampiran: newMsg.lampiran,
                            is_read: true,
                            time_formatted: newMsg.time_formatted,
                        });
                        hasIncomingForActive = true;
                    }
                }
            });

            playChatChime();
            fetchContacts();

            if (hasIncomingForActive) {
                scrollToBottom();
                if (activeContact.value) {
                    axios.post(`/api/chat/mark-read/${activeContact.value.id}`);
                }
            }
        }
    } catch (e) {}
};

let searchTimer = null;
watch(search, () => {
    if (searchTimer) clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
        fetchContacts();
    }, 300);
});

// Instant 0ms client-side filter
const filteredContacts = computed(() => {
    const q = (search.value || '').trim().toLowerCase();
    if (!q) return contacts.value;
    return contacts.value.filter(c => {
        const name = (c.name || '').toLowerCase();
        const username = (c.username || '').toLowerCase();
        const role = (c.role_name || '').toLowerCase();
        return name.includes(q) || username.includes(q) || role.includes(q);
    });
});

const getAvatarUrl = (photo) => {
    if (!photo) return null;
    if (photo.startsWith('http://') || photo.startsWith('https://') || photo.startsWith('data:')) return photo;
    const clean = photo.replace(/^\/?(public\/)?/, '').replace(/^\//, '');
    if (!clean.includes('/')) {
        return `/uploads/users/${clean}`;
    }
    return '/' + clean;
};

const getLampiranUrl = (path) => {
    if (!path) return '';
    if (path.startsWith('http://') || path.startsWith('https://') || path.startsWith('data:')) return path;
    const clean = path.replace(/^\/?(public\/)?/, '').replace(/^\//, '');
    return '/' + clean;
};

const getInitials = (name) => {
    if (!name) return 'U';
    const parts = name.trim().split(' ');
    if (parts.length >= 2) {
        return (parts[0][0] + parts[1][0]).toUpperCase();
    }
    return name.substring(0, 2).toUpperCase();
};

const getAvatarBg = (name) => {
    const colors = [
        'bg-blue-600', 'bg-emerald-600', 'bg-purple-600', 'bg-amber-600',
        'bg-rose-600', 'bg-teal-600', 'bg-indigo-600', 'bg-cyan-600'
    ];
    let hash = 0;
    for (let i = 0; i < (name || '').length; i++) {
        hash = name.charCodeAt(i) + ((hash << 5) - hash);
    }
    return colors[Math.abs(hash) % colors.length];
};

onMounted(() => {
    fetchContacts(true);
    pollTimer = setInterval(pollNewChats, 12000);
});

onUnmounted(() => {
    if (pollTimer) clearInterval(pollTimer);
});
</script>

<template>
    <div class="inline-block">
        <!-- Hidden file picker for image/screenshot -->
        <input
            type="file"
            ref="fileInputRef"
            accept="image/jpeg,image/png,image/webp,image/gif"
            class="hidden"
            @change="onFileSelected"
        />

        <!-- 1. Topbar Trigger Button -->
        <button
            type="button"
            @click="unlockAudio(); isOpen = !isOpen; if(isOpen && !activeContact) fetchContacts(true);"
            class="relative inline-flex items-center justify-center w-9 h-9 rounded-xl bg-slate-100 hover:bg-blue-50 hover:text-blue-800 text-slate-700 transition cursor-pointer border border-slate-200/80 shadow-2xs"
            title="Pesan & Obrolan Internal Paroki"
        >
            <i class="fa-solid fa-comments text-sm" :class="{ 'text-blue-600': totalUnread > 0 }"></i>

            <!-- Pulse Badge on Unread Messages -->
            <span
                v-if="totalUnread > 0"
                class="absolute -top-1 -right-1 flex h-4 min-w-[16px] px-1 items-center justify-center rounded-full bg-blue-600 text-[9px] font-black text-white shadow-xs animate-pulse"
            >
                {{ totalUnread > 9 ? '9+' : totalUnread }}
            </span>
        </button>

        <!-- 2. Bottom-Right Floating Action Button (FAB) -->
        <div v-if="!isOpen" class="fixed bottom-5 right-5 z-40">
            <button
                type="button"
                @click="unlockAudio(); isOpen = true; if(!activeContact) fetchContacts(true);"
                class="group relative flex items-center justify-center w-13 h-13 rounded-2xl bg-gradient-to-tr from-blue-700 via-indigo-700 to-slate-900 text-white shadow-xl shadow-blue-900/30 hover:scale-105 active:scale-95 transition-all duration-200 cursor-pointer border border-white/20"
                title="Buka Chat Paroki"
            >
                <i class="fa-solid fa-comments text-xl group-hover:rotate-6 transition-transform"></i>

                <!-- Pulse Badge on Unread Messages -->
                <span
                    v-if="totalUnread > 0"
                    class="absolute -top-1 -right-1 flex h-5 min-w-[20px] px-1.5 items-center justify-center rounded-full bg-rose-600 border-2 border-white text-[10px] font-black text-white shadow-md animate-bounce"
                >
                    {{ totalUnread > 9 ? '9+' : totalUnread }}
                </span>
            </button>
        </div>

        <!-- 3. Chat Floating Window (Docked at Bottom-Right Corner) -->
        <transition
            enter-active-class="transition duration-250 cubic-bezier(0.16, 1, 0.3, 1)"
            enter-from-class="transform translate-y-6 scale-95 opacity-0"
            enter-to-class="transform translate-y-0 scale-100 opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="transform translate-y-0 scale-100 opacity-100"
            leave-to-class="transform translate-y-6 scale-95 opacity-0"
        >
            <div
                v-if="isOpen"
                class="fixed bottom-4 right-4 sm:bottom-5 sm:right-5 w-[calc(100vw-32px)] sm:w-[380px] h-[550px] max-h-[85vh] rounded-3xl bg-white border border-slate-200/90 shadow-2xl z-50 overflow-hidden flex flex-col"
            >
                <!-- VIEW 1: DAFTAR KONTAK & PERCAKAPAN -->
                <template v-if="!activeContact">
                    <!-- Header -->
                    <div class="px-4 py-3 bg-gradient-to-r from-blue-900 via-indigo-900 to-slate-900 text-white flex items-center justify-between shadow-xs shrink-0">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-xl bg-white/15 border border-white/20 flex items-center justify-center text-xs">
                                <i class="fa-solid fa-comments text-blue-300"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-xs leading-tight">Pesan & Chat Paroki</h4>
                                <p class="text-[10px] text-blue-200 font-medium">Koordinasi Internal Pastoral</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <span
                                v-if="totalUnread > 0"
                                class="px-2 py-0.5 rounded-full bg-blue-500/40 border border-blue-400/30 text-white text-[10px] font-extrabold"
                            >
                                {{ totalUnread }} baru
                            </span>
                            <button
                                type="button"
                                @click="isOpen = false"
                                class="text-white/70 hover:text-white p-1 text-sm cursor-pointer transition rounded-lg hover:bg-white/10"
                                title="Tutup Chat"
                            >
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Search Contacts -->
                    <div class="p-2.5 border-b border-slate-100 bg-slate-50/70 shrink-0">
                        <div class="relative">
                            <span class="absolute left-3 top-2.5 text-slate-400 text-xs">
                                <i class="fa-solid fa-magnifying-glass"></i>
                            </span>
                            <input
                                v-model="search"
                                type="text"
                                placeholder="Cari Pastor, Admin, Ketua KUB..."
                                class="w-full pl-8.5 pr-3 py-1.5 rounded-xl bg-white border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition shadow-2xs"
                            />
                        </div>
                    </div>

                    <!-- Contacts List -->
                    <div class="flex-1 overflow-y-auto divide-y divide-slate-100 custom-scrollbar">
                        <div v-if="isLoadingContacts" class="p-8 text-center text-slate-400 space-y-2">
                            <i class="fa-solid fa-circle-notch fa-spin text-blue-600 text-lg"></i>
                            <p class="text-xs font-medium">Memuat kontak...</p>
                        </div>

                        <template v-else-if="filteredContacts.length > 0">
                            <div
                                v-for="c in filteredContacts"
                                :key="c.id"
                                @click="selectContact(c)"
                                class="p-3 hover:bg-blue-50/50 transition cursor-pointer flex items-center gap-3 group"
                                :class="{ 'bg-blue-50/30': c.unread_count > 0 }"
                            >
                                <!-- Avatar -->
                                <div class="relative shrink-0">
                                    <div
                                        v-if="c.foto"
                                        class="w-10 h-10 rounded-full overflow-hidden border border-slate-200 bg-white shadow-2xs"
                                    >
                                        <img :src="getAvatarUrl(c.foto)" :alt="c.name" class="w-full h-full object-cover" />
                                    </div>
                                    <div
                                        v-else
                                        :class="['w-10 h-10 rounded-full flex items-center justify-center text-white text-xs font-black shadow-2xs', getAvatarBg(c.name)]"
                                    >
                                        {{ getInitials(c.name) }}
                                    </div>
                                    <!-- Online Indicator -->
                                    <span class="absolute bottom-0 right-0 w-2.5 h-2.5 rounded-full bg-emerald-500 border-2 border-white"></span>
                                </div>

                                <!-- Info -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between gap-1 mb-0.5">
                                        <h5 class="text-xs font-bold text-slate-900 truncate group-hover:text-blue-600 transition">
                                            {{ c.name }}
                                        </h5>
                                        <span v-if="c.unread_count > 0" class="px-1.5 py-0.5 rounded-full bg-blue-600 text-white text-[9px] font-black shrink-0 animate-bounce">
                                            {{ c.unread_count }}
                                        </span>
                                    </div>
                                    <div class="flex items-center justify-between gap-2 text-[11px]">
                                        <span class="text-[10px] font-semibold text-blue-700 bg-blue-50 px-1.5 py-0.5 rounded border border-blue-100 shrink-0">
                                            {{ c.role_name }}
                                        </span>
                                        <p class="text-slate-400 truncate text-[11px]">
                                            {{ c.last_message || 'Mulai percakapan baru...' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <div v-else class="p-8 text-center text-slate-400 space-y-2">
                            <i class="fa-regular fa-comment-dots text-2xl text-slate-300"></i>
                            <p class="text-xs font-semibold text-slate-600">Tidak ada kontak ditemukan</p>
                            <p class="text-[10px] text-slate-400">Pastikan nama kontak sesuai dengan database paroki.</p>
                        </div>
                    </div>
                </template>

                <!-- VIEW 2: RUANG OBROLAN AKTIF (CHAT ROOM) -->
                <template v-else>
                    <!-- Active Chat Header -->
                    <div class="px-3.5 py-3 bg-gradient-to-r from-blue-900 via-indigo-900 to-slate-900 text-white flex items-center justify-between shadow-xs shrink-0">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <button
                                type="button"
                                @click="closeChat"
                                class="w-7 h-7 rounded-lg bg-white/15 hover:bg-white/25 text-white flex items-center justify-center text-xs transition cursor-pointer shrink-0"
                                title="Kembali ke daftar kontak"
                            >
                                <i class="fa-solid fa-chevron-left"></i>
                            </button>

                            <div class="w-8 h-8 rounded-full overflow-hidden border border-white/20 bg-white/10 flex items-center justify-center shrink-0">
                                <img v-if="activeContact.foto" :src="getAvatarUrl(activeContact.foto)" class="w-full h-full object-cover" />
                                <span v-else class="text-xs font-black text-white">{{ getInitials(activeContact.name) }}</span>
                            </div>

                            <div class="min-w-0">
                                <h4 class="font-bold text-xs truncate leading-tight">{{ activeContact.name }}</h4>
                                <p class="text-[10px] text-blue-200 font-medium truncate">{{ activeContact.role_name }}</p>
                            </div>
                        </div>

                        <button
                            type="button"
                            @click="isOpen = false"
                            class="text-white/70 hover:text-white p-1 text-sm cursor-pointer transition shrink-0"
                        >
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>

                    <!-- Messages Stream Feed -->
                    <div
                        ref="messagesContainer"
                        class="flex-1 p-3.5 space-y-3 overflow-y-auto custom-scrollbar bg-slate-50/50"
                    >
                        <div v-if="isLoadingMessages" class="py-12 text-center text-slate-400 space-y-2">
                            <i class="fa-solid fa-circle-notch fa-spin text-blue-600 text-lg"></i>
                            <p class="text-xs font-medium">Memuat percakapan...</p>
                        </div>

                        <template v-else-if="messages.length > 0">
                            <div
                                v-for="m in messages"
                                :key="m.id"
                                class="flex flex-col"
                                :class="m.is_me ? 'items-end' : 'items-start'"
                            >
                                <div
                                    class="max-w-[85%] px-3 py-2 text-xs shadow-2xs leading-relaxed"
                                    :class="m.is_me
                                        ? 'bg-blue-600 text-white rounded-2xl rounded-tr-xs font-normal'
                                        : 'bg-white border border-slate-200 text-slate-900 rounded-2xl rounded-tl-xs'"
                                >
                                    <!-- Image / Screenshot Attachment -->
                                    <div v-if="m.lampiran" class="mb-1.5 overflow-hidden rounded-xl bg-black/10 border border-white/20">
                                        <img
                                            :src="getLampiranUrl(m.lampiran)"
                                            alt="Lampiran Screenshot"
                                            class="max-h-48 w-full object-cover cursor-pointer hover:opacity-95 transition"
                                            @click="zoomImageSrc = getLampiranUrl(m.lampiran)"
                                        />
                                    </div>

                                    <!-- Message Text -->
                                    <p v-if="m.pesan" class="whitespace-pre-wrap break-words">{{ m.pesan }}</p>
                                </div>
                                <div class="flex items-center gap-1 mt-1 px-1 text-[10px] text-slate-400 font-mono">
                                    <span>{{ m.time_formatted }}</span>
                                    <span v-if="m.is_me">
                                        <i class="fa-solid fa-check-double text-[9px]" :class="m.is_read ? 'text-blue-500' : 'text-slate-300'"></i>
                                    </span>
                                </div>
                            </div>
                        </template>

                        <div v-else class="py-16 text-center text-slate-400 space-y-2">
                            <div class="w-12 h-12 rounded-full bg-blue-50 text-blue-600 mx-auto flex items-center justify-center text-lg">
                                <i class="fa-solid fa-paper-plane"></i>
                            </div>
                            <p class="text-xs font-bold text-slate-700">Belum ada percakapan</p>
                            <p class="text-[11px] text-slate-400">Ketik pesan atau tekan <b>Ctrl + V</b> untuk menempelkan tangkapan layar (screenshot).</p>
                        </div>
                    </div>

                    <!-- Selected Attachment Preview Chip -->
                    <div v-if="attachmentPreview" class="px-3 py-1.5 bg-blue-50 border-t border-blue-200 flex items-center justify-between shrink-0">
                        <div class="flex items-center gap-2 min-w-0">
                            <img :src="attachmentPreview" class="w-9 h-9 rounded-lg object-cover border border-blue-300 shadow-2xs shrink-0" />
                            <div class="min-w-0">
                                <p class="text-[11px] font-bold text-blue-900 truncate">Screenshot / Gambar siap dikirim</p>
                                <p class="text-[9px] text-blue-600">Tekan Enter atau Kirim</p>
                            </div>
                        </div>
                        <button
                            type="button"
                            @click="clearAttachment"
                            class="w-6 h-6 rounded-full bg-rose-100 hover:bg-rose-200 text-rose-600 flex items-center justify-center text-xs transition cursor-pointer"
                            title="Batalkan gambar"
                        >
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>

                    <!-- Input Bar (Supports Ctrl + V screenshot paste) -->
                    <div class="p-2.5 bg-white border-t border-slate-200 shrink-0">
                        <form @submit.prevent="sendMessage" class="flex items-center gap-2">
                            <!-- Attach File / Image Button -->
                            <button
                                type="button"
                                @click="fileInputRef?.click()"
                                class="w-8 h-8 rounded-xl bg-slate-100 hover:bg-blue-50 hover:text-blue-700 text-slate-500 flex items-center justify-center text-xs transition cursor-pointer border border-slate-200/80 shrink-0"
                                title="Lampirkan Gambar / Screenshot"
                            >
                                <i class="fa-solid fa-image"></i>
                            </button>

                            <input
                                v-model="messageText"
                                @keydown="handleKeyDown"
                                @paste="handlePaste"
                                type="text"
                                placeholder="Ketik pesan atau Ctrl+V screenshot..."
                                class="flex-1 px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition"
                            />
                            <button
                                type="submit"
                                :disabled="(!messageText.trim() && !attachmentPreview) || isSending"
                                class="w-9 h-9 rounded-xl bg-blue-600 hover:bg-blue-700 disabled:opacity-40 text-white flex items-center justify-center text-xs transition cursor-pointer shadow-xs shrink-0"
                                title="Kirim Pesan (Enter)"
                            >
                                <i v-if="!isSending" class="fa-solid fa-paper-plane"></i>
                                <i v-else class="fa-solid fa-circle-notch fa-spin text-xs"></i>
                            </button>
                        </form>
                    </div>
                </template>
            </div>
        </transition>

        <!-- Image Lightbox Modal Zoom -->
        <transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="zoomImageSrc"
                class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-xs"
                @click="zoomImageSrc = null"
            >
                <div class="relative max-w-3xl max-h-[90vh] bg-slate-900 rounded-2xl overflow-hidden shadow-2xl p-2">
                    <img :src="zoomImageSrc" alt="Screenshot Zoom" class="max-w-full max-h-[85vh] object-contain mx-auto rounded-lg" />
                    <button
                        type="button"
                        @click="zoomImageSrc = null"
                        class="absolute top-4 right-4 w-9 h-9 rounded-full bg-black/60 hover:bg-black/80 text-white flex items-center justify-center text-sm transition cursor-pointer"
                    >
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            </div>
        </transition>
    </div>
</template>
