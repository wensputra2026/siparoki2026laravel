<script setup>
import { ref, watch } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    role: { type: String, default: 'Super Admin' },
    prefix: { type: String, default: 'superadmin' },
    initialTab: { type: String, default: 'pembayaran' },
    metodePembayaran: { type: Array, default: () => [] },
    pengaturanOtp: { type: Object, default: () => ({}) },
    sliders: { type: Array, default: () => [] },
    pengaturanAplikasi: { type: Object, default: () => ({}) },
});

const activeTab = ref(props.initialTab || 'pembayaran');

// Payment Form & Modal
const paymentModal = ref(false);
const editingPayment = ref(null);
const paymentForm = useForm({
    id: null,
    nama_bank: '',
    nomor_rekening: '',
    atas_nama: '',
    tipe: 'Transfer Bank',
    urutan: 1,
    status: 'Aktif',
    petunjuk: '',
    logo_bank: null,
    gambar_qris: null,
});

const openPaymentModal = (item = null) => {
    editingPayment.value = item;
    if (item) {
        paymentForm.id = item.id;
        paymentForm.nama_bank = item.nama_bank;
        paymentForm.nomor_rekening = item.nomor_rekening || '';
        paymentForm.atas_nama = item.atas_nama || '';
        paymentForm.tipe = item.tipe || 'Transfer Bank';
        paymentForm.urutan = item.urutan || 1;
        paymentForm.status = item.status || 'Aktif';
        paymentForm.petunjuk = item.petunjuk || '';
        paymentForm.logo_bank = null;
        paymentForm.gambar_qris = null;
    } else {
        paymentForm.reset();
        paymentForm.id = null;
        paymentForm.tipe = 'Transfer Bank';
        paymentForm.status = 'Aktif';
        paymentForm.urutan = props.metodePembayaran.length + 1;
    }
    paymentModal.value = true;
};

const submitPayment = () => {
    paymentForm.post(`/${props.prefix}/pengaturan/pembayaran/save`, {
        preserveScroll: true,
        onSuccess: () => {
            paymentModal.value = false;
            paymentForm.reset();
        },
    });
};

const deletePayment = (item) => {
    if (confirm(`Apakah Anda yakin ingin menghapus metode pembayaran "${item.nama_bank}"?`)) {
        router.post(`/${props.prefix}/pengaturan/pembayaran/${item.id}/delete`, {}, {
            preserveScroll: true,
        });
    }
};

// OTP Form
const otpForm = useForm({
    provider: props.pengaturanOtp?.provider || 'Fonnte',
    api_key: props.pengaturanOtp?.api_key || '',
    sender_number: props.pengaturanOtp?.sender_number || '',
    device_id: props.pengaturanOtp?.device_id || '',
    template_otp: props.pengaturanOtp?.template_otp || 'Kode verifikasi SIPAROKI Anda: {{otp}}. Berlaku 10 menit.',
    template_notifikasi: props.pengaturanOtp?.template_notifikasi || 'Halo {{nama}}, permohonan sakramen Anda di {{paroki}} telah diterima.',
    status: props.pengaturanOtp?.status || 'Aktif',
});

const submitOtp = () => {
    otpForm.post(`/${props.prefix}/pengaturan/otp/save`, {
        preserveScroll: true,
    });
};

const testWaForm = useForm({
    target_phone: '',
    test_message: 'Halo! Ini adalah pesan uji coba integrasi Gateway WhatsApp Paroki Benlutu.',
});

const submitTestWa = () => {
    if (!testWaForm.target_phone) {
        alert('Silakan masukkan nomor WhatsApp tujuan.');
        return;
    }
    testWaForm.post(`/${props.prefix}/pengaturan/otp/test`, {
        preserveScroll: true,
    });
};

// Video Header Form
const videoForm = useForm({
    video_header_type: props.pengaturanAplikasi?.video_header_type || 'youtube',
    video_header_url: props.pengaturanAplikasi?.video_header_url || props.pengaturanAplikasi?.hero_video_youtube || '',
    video_header_title: props.pengaturanAplikasi?.video_header_title || 'Selamat Datang di Paroki St. Vinsensius a Paulo Benlutu',
    video_header_subtitle: props.pengaturanAplikasi?.video_header_subtitle || 'Gereja yang Bersekutu, Berakar dalam Iman, dan Berbuah dalam Kasih Karitas.',
    video_header_btn_text: props.pengaturanAplikasi?.video_header_btn_text || 'Lihat Jadwal Misa',
    video_header_btn_link: props.pengaturanAplikasi?.video_header_btn_link || '/jadwal-misa',
    video_header_status: props.pengaturanAplikasi?.video_header_status || 'Aktif',
    video_header_autoplay: props.pengaturanAplikasi?.video_header_autoplay ?? '1',
    video_header_muted: props.pengaturanAplikasi?.video_header_muted ?? '1',
    video_header_loop: props.pengaturanAplikasi?.video_header_loop ?? '1',
    video_header_overlay_opacity: props.pengaturanAplikasi?.video_header_overlay_opacity || '50',
    video_header_file: null,
    video_header_poster: null,
});

const submitVideo = () => {
    videoForm.post(`/${props.prefix}/pengaturan/video/save`, {
        preserveScroll: true,
    });
};

// Slider Form & Modal
const sliderModal = ref(false);
const editingSlider = ref(null);
const sliderForm = useForm({
    id: null,
    judul: '',
    subjudul: '',
    link_url: '',
    tombol_teks: 'Lihat Selengkapnya',
    urutan: 1,
    status: 'Aktif',
    gambar: null,
});

const openSliderModal = (item = null) => {
    editingSlider.value = item;
    if (item) {
        sliderForm.id = item.id;
        sliderForm.judul = item.judul;
        sliderForm.subjudul = item.subjudul || '';
        sliderForm.link_url = item.link_url || '';
        sliderForm.tombol_teks = item.tombol_teks || 'Lihat Selengkapnya';
        sliderForm.urutan = item.urutan || 1;
        sliderForm.status = item.status || 'Aktif';
        sliderForm.gambar = null;
    } else {
        sliderForm.reset();
        sliderForm.id = null;
        sliderForm.tombol_teks = 'Lihat Selengkapnya';
        sliderForm.status = 'Aktif';
        sliderForm.urutan = props.sliders.length + 1;
    }
    sliderModal.value = true;
};

const submitSlider = () => {
    sliderForm.post(`/${props.prefix}/pengaturan/slider/save`, {
        preserveScroll: true,
        onSuccess: () => {
            sliderModal.value = false;
            sliderForm.reset();
        },
    });
};

const deleteSlider = (item) => {
    if (confirm(`Hapus slide banner "${item.judul}"?`)) {
        router.post(`/${props.prefix}/pengaturan/slider/${item.id}/delete`, {}, {
            preserveScroll: true,
        });
    }
};

// SEO Form
const seoForm = useForm({
    meta_title: props.pengaturanAplikasi?.meta_title || 'Paroki St. Vinsensius a Paulo Benlutu',
    meta_description: props.pengaturanAplikasi?.meta_description || 'Website resmi Paroki Santo Vinsensius a Paulo Benlutu, Keuskupan Agung Kupang.',
    meta_keywords: props.pengaturanAplikasi?.meta_keywords || 'paroki benlutu, katolik, keuskupan agung kupang, jadwal misa, sakramen',
    google_analytics_id: props.pengaturanAplikasi?.google_analytics_id || '',
});

const submitSeo = () => {
    seoForm.post(`/${props.prefix}/pengaturan/seo/save`, {
        preserveScroll: true,
    });
};

// Widget Form
const widgetForm = useForm({
    widget_jadwal_misa: props.pengaturanAplikasi?.widget_jadwal_misa ?? '1',
    widget_renungan: props.pengaturanAplikasi?.widget_renungan ?? '1',
    widget_statistik: props.pengaturanAplikasi?.widget_statistik ?? '1',
    widget_kapela: props.pengaturanAplikasi?.widget_kapela ?? '1',
    jam_operasional: props.pengaturanAplikasi?.jam_operasional || 'Senin - Sabtu: 08.00 - 14.00 WITA',
    facebook_url: props.pengaturanAplikasi?.facebook_url || '',
    instagram_url: props.pengaturanAplikasi?.instagram_url || '',
    youtube_url: props.pengaturanAplikasi?.youtube_url || '',
    tiktok_url: props.pengaturanAplikasi?.tiktok_url || '',
});

const submitWidget = () => {
    widgetForm.post(`/${props.prefix}/pengaturan/widget/save`, {
        preserveScroll: true,
    });
};

// Maintenance Form
const maintenanceForm = useForm({
    maintenance_mode: props.pengaturanAplikasi?.maintenance_mode ?? '0',
    maintenance_title: props.pengaturanAplikasi?.maintenance_title || 'Website Sedang Dalam Pemeliharaan / Perawatan',
    maintenance_message: props.pengaturanAplikasi?.maintenance_message || 'Mohon maaf atas ketidaknyamanannya. Website Paroki St. Vinsensius a Paulo Benlutu sedang melakukan pembaruan berkala. Silakan kembali dalam beberapa saat.',
    maintenance_until: props.pengaturanAplikasi?.maintenance_until || '25 Agustus 2026, 17:00 WITA',
    maintenance_contact: props.pengaturanAplikasi?.maintenance_contact || '0812-3456-7890',
    maintenance_bypass_key: props.pengaturanAplikasi?.maintenance_bypass_key || 'siparoki2026',
});

const submitMaintenance = () => {
    maintenanceForm.post(`/${props.prefix}/pengaturan/maintenance/save`, {
        preserveScroll: true,
    });
};

const getYoutubeEmbed = (url) => {
    if (!url) return '';
    const regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=)([^#\&\?]*).*/;
    const match = url.match(regExp);
    return (match && match[2].length === 11) ? `https://www.youtube.com/embed/${match[2]}` : url;
};
</script>

<template>
    <AppLayout>
        <Head title="Pengaturan Web & Integrasi - SIPAROKI" />

        <div class="w-full space-y-6 pb-12">

            <!-- 1. EXECUTIVE HEADER BANNER -->
            <div class="bg-gradient-to-r from-teal-800 via-teal-700 to-slate-900 rounded-3xl p-6 sm:p-8 text-white shadow-xl shadow-teal-900/15 relative overflow-hidden">
                <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
                <div class="absolute right-20 top-0 w-32 h-32 bg-teal-300/20 rounded-full blur-xl pointer-events-none"></div>

                <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div class="space-y-2">
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/15 backdrop-blur-md text-teal-100 text-xs font-semibold border border-white/20">
                            <i class="fa-solid fa-sliders"></i>
                            <span>Sistem Pengaturan Web &amp; Integrasi Terpadu Paroki</span>
                        </div>
                        <h1 class="text-2xl sm:text-3xl font-black tracking-tight">
                            Pengaturan Web &amp; Sistem
                        </h1>
                        <p class="text-teal-100 text-xs sm:text-sm max-w-2xl leading-relaxed">
                            Kelola metode pembayaran rekening &amp; QRIS, WhatsApp Gateway OTP, video header beranda, banner slider, SEO Google, widget medsos, dan mode pemeliharaan secara real-time.
                        </p>
                    </div>

                    <div class="flex items-center gap-2.5 flex-wrap shrink-0">
                        <a
                            href="/"
                            target="_blank"
                            class="px-4 py-2.5 rounded-2xl bg-white/20 hover:bg-white/30 backdrop-blur-md text-white text-xs font-bold border border-white/30 shadow-sm transition flex items-center gap-2 cursor-pointer"
                        >
                            <i class="fa-solid fa-earth-americas"></i>
                            <span>Situs Publik</span>
                        </a>

                        <Link
                            :href="`/${prefix}/security-settings`"
                            class="px-4 py-2.5 rounded-2xl bg-white hover:bg-teal-50 text-teal-950 text-xs font-black shadow-lg shadow-black/5 transition flex items-center gap-2"
                        >
                            <i class="fa-solid fa-shield-halved text-teal-600"></i>
                            <span>Security Center</span>
                        </Link>
                    </div>
                </div>
            </div>

            <!-- 2. TAB NAVIGATION (Consistent with Statistik & GenericModule) -->
            <div class="flex items-center gap-2 overflow-x-auto pb-1 border-b border-slate-200/80">
                <button
                    @click="activeTab = 'pembayaran'"
                    :class="[
                        'px-5 py-2.5 rounded-2xl text-xs font-bold transition flex items-center gap-2 shrink-0 cursor-pointer',
                        activeTab === 'pembayaran'
                            ? 'bg-teal-700 text-white shadow-md shadow-teal-700/20'
                            : 'bg-white hover:bg-slate-50 text-slate-600 border border-slate-200/80'
                    ]"
                >
                    <i class="fa-solid fa-credit-card"></i>
                    <span>Pembayaran &amp; QRIS</span>
                </button>

                <button
                    @click="activeTab = 'otp'"
                    :class="[
                        'px-5 py-2.5 rounded-2xl text-xs font-bold transition flex items-center gap-2 shrink-0 cursor-pointer',
                        activeTab === 'otp'
                            ? 'bg-teal-700 text-white shadow-md shadow-teal-700/20'
                            : 'bg-white hover:bg-slate-50 text-slate-600 border border-slate-200/80'
                    ]"
                >
                    <i class="fa-brands fa-whatsapp text-emerald-500"></i>
                    <span>WhatsApp Gateway &amp; OTP</span>
                </button>

                <button
                    @click="activeTab = 'video'"
                    :class="[
                        'px-5 py-2.5 rounded-2xl text-xs font-bold transition flex items-center gap-2 shrink-0 cursor-pointer',
                        activeTab === 'video'
                            ? 'bg-teal-700 text-white shadow-md shadow-teal-700/20'
                            : 'bg-white hover:bg-slate-50 text-slate-600 border border-slate-200/80'
                    ]"
                >
                    <i class="fa-brands fa-youtube text-rose-500"></i>
                    <span>Video Header</span>
                </button>

                <button
                    @click="activeTab = 'slider'"
                    :class="[
                        'px-5 py-2.5 rounded-2xl text-xs font-bold transition flex items-center gap-2 shrink-0 cursor-pointer',
                        activeTab === 'slider'
                            ? 'bg-teal-700 text-white shadow-md shadow-teal-700/20'
                            : 'bg-white hover:bg-slate-50 text-slate-600 border border-slate-200/80'
                    ]"
                >
                    <i class="fa-solid fa-images text-amber-500"></i>
                    <span>Banner Slider</span>
                </button>

                <button
                    @click="activeTab = 'seo'"
                    :class="[
                        'px-5 py-2.5 rounded-2xl text-xs font-bold transition flex items-center gap-2 shrink-0 cursor-pointer',
                        activeTab === 'seo'
                            ? 'bg-teal-700 text-white shadow-md shadow-teal-700/20'
                            : 'bg-white hover:bg-slate-50 text-slate-600 border border-slate-200/80'
                    ]"
                >
                    <i class="fa-solid fa-magnifying-glass text-sky-500"></i>
                    <span>SEO &amp; Meta Tags</span>
                </button>

                <button
                    @click="activeTab = 'widget'"
                    :class="[
                        'px-5 py-2.5 rounded-2xl text-xs font-bold transition flex items-center gap-2 shrink-0 cursor-pointer',
                        activeTab === 'widget'
                            ? 'bg-teal-700 text-white shadow-md shadow-teal-700/20'
                            : 'bg-white hover:bg-slate-50 text-slate-600 border border-slate-200/80'
                    ]"
                >
                    <i class="fa-solid fa-puzzle-piece text-indigo-500"></i>
                    <span>Widget &amp; Medsos</span>
                </button>

                <button
                    @click="activeTab = 'maintenance'"
                    :class="[
                        'px-5 py-2.5 rounded-2xl text-xs font-bold transition flex items-center gap-2 shrink-0 cursor-pointer',
                        activeTab === 'maintenance'
                            ? 'bg-teal-700 text-white shadow-md shadow-teal-700/20'
                            : 'bg-white hover:bg-slate-50 text-slate-600 border border-slate-200/80'
                    ]"
                >
                    <i class="fa-solid fa-screwdriver-wrench text-orange-500"></i>
                    <span>Mode Maintenance</span>
                    <span v-if="maintenanceForm.maintenance_mode === '1'" class="px-1.5 py-0.5 rounded-full bg-rose-500 text-white text-[9px] font-black uppercase tracking-wider animate-pulse">Aktif</span>
                </button>

                <Link
                    href="/setup-paroki"
                    class="px-5 py-2.5 rounded-2xl text-xs font-bold transition flex items-center gap-2 shrink-0 cursor-pointer bg-purple-600 hover:bg-purple-700 text-white shadow-md shadow-purple-600/20"
                    title="Buka Wizard Setup & Inisialisasi Paroki Default"
                >
                    <i class="fa-solid fa-sliders text-amber-300"></i>
                    <span>Setup Paroki Wizard</span>
                </Link>
            </div>

            <!-- Tab 1: Pembayaran & QRIS -->
            <div v-show="activeTab === 'pembayaran'" class="space-y-6">
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
                    <div>
                        <h3 class="font-extrabold text-slate-900 dark:text-white text-base">
                            Rekening Bank &amp; QRIS Paroki
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            Metode pembayaran ini akan otomatis muncul pada formulir pendaftaran sakramen &amp; intensi misa umat.
                        </p>
                    </div>
                    <button
                        @click="openPaymentModal()"
                        class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold shadow transition"
                    >
                        <i class="fa-solid fa-plus"></i>
                        <span>Tambah Metode Pembayaran</span>
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                    <div
                        v-for="item in metodePembayaran"
                        :key="item.id"
                        class="bg-white dark:bg-slate-800 rounded-3xl p-6 border border-slate-200/80 dark:border-slate-700/80 shadow-xs flex flex-col justify-between relative overflow-hidden"
                    >
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-extrabold px-3 py-1 rounded-full bg-teal-50 text-teal-700 dark:bg-teal-950 dark:text-teal-300 border border-teal-200 dark:border-teal-800">
                                    {{ item.tipe }}
                                </span>
                                <span :class="['text-[11px] font-bold', item.status === 'Aktif' ? 'text-emerald-600' : 'text-slate-400']">
                                    <i class="fa-solid fa-circle text-[7px]"></i> {{ item.status }}
                                </span>
                            </div>

                            <div v-if="item.gambar_qris" class="w-full h-40 bg-slate-50 dark:bg-slate-900 rounded-2xl flex items-center justify-center overflow-hidden border border-slate-200/80 dark:border-slate-700/80 p-2">
                                <img :src="'/' + item.gambar_qris.replace(/^\//, '')" alt="QRIS" class="h-full object-contain" />
                            </div>

                            <div>
                                <h4 class="font-extrabold text-slate-900 dark:text-white text-base">
                                    {{ item.nama_bank }}
                                </h4>
                                <p v-if="item.nomor_rekening" class="font-mono font-bold text-teal-600 dark:text-teal-400 text-sm mt-0.5">
                                    {{ item.nomor_rekening }}
                                </p>
                                <p v-if="item.atas_nama" class="text-xs text-slate-600 dark:text-slate-300 font-semibold mt-0.5">
                                    a.n. {{ item.atas_nama }}
                                </p>
                                <p v-if="item.petunjuk" class="text-xs text-slate-400 mt-2 line-clamp-2">
                                    {{ item.petunjuk }}
                                </p>
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-700 flex items-center justify-end gap-2">
                            <button
                                @click="openPaymentModal(item)"
                                class="px-3.5 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 text-xs font-bold transition cursor-pointer"
                            >
                                <i class="fa-solid fa-pen-to-square text-amber-500 me-1"></i> Edit
                            </button>
                            <button
                                @click="deletePayment(item)"
                                class="px-3.5 py-1.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 dark:bg-rose-950 dark:hover:bg-rose-900 text-xs font-bold transition cursor-pointer"
                            >
                                <i class="fa-solid fa-trash me-1"></i> Hapus
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab 2: WhatsApp Gateway & OTP -->
            <div v-show="activeTab === 'otp'" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2">
                    <form @submit.prevent="submitOtp" class="bg-white dark:bg-slate-800 rounded-3xl p-6 sm:p-8 border border-slate-200/80 dark:border-slate-700/80 shadow-xs space-y-5">
                        <div>
                            <h3 class="font-extrabold text-slate-900 dark:text-white text-base">
                                Konfigurasi Gateway WhatsApp &amp; OTP
                            </h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                Digunakan untuk mengirim notifikasi sakramen, konfirmasi intensi, dan kode OTP ke nomor jemaat.
                            </p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1">Provider Gateway</label>
                                <select v-model="otpForm.provider" class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs font-semibold focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                                    <option value="Fonnte">Fonnte (Rekomendasi Indonesia)</option>
                                    <option value="Wablas">Wablas WhatsApp API</option>
                                    <option value="Twilio">Twilio WhatsApp</option>
                                    <option value="UltraMsg">UltraMsg API</option>
                                    <option value="Custom">Custom HTTP Webhook</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1">Nomor Pengirim (Sender)</label>
                                <input v-model="otpForm.sender_number" type="text" placeholder="081234567890" class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs font-semibold focus:ring-2 focus:ring-emerald-500 focus:outline-none" />
                            </div>

                            <div class="sm:col-span-2">
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1">API Key / Token</label>
                                <input v-model="otpForm.api_key" type="password" placeholder="Masukkan token API gateway..." class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs font-mono focus:ring-2 focus:ring-emerald-500 focus:outline-none" />
                            </div>

                            <div class="sm:col-span-2">
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1">Template Pesan OTP</label>
                                <textarea v-model="otpForm.template_otp" rows="2" class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none"></textarea>
                                <span class="text-[11px] text-slate-400">Gunakan tag <code>&#123;&#123;otp&#125;&#125;</code> untuk kode OTP.</span>
                            </div>

                            <div class="sm:col-span-2">
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1">Template Notifikasi Sakramen</label>
                                <textarea v-model="otpForm.template_notifikasi" rows="2" class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none"></textarea>
                            </div>
                        </div>

                        <div class="flex justify-end pt-3 border-t border-slate-100 dark:border-slate-700">
                            <button type="submit" class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-lg shadow-emerald-600/30 transition">
                                <i class="fa-solid fa-save me-1"></i> Simpan Gateway OTP
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Test Kirim WhatsApp -->
                <div>
                    <form @submit.prevent="submitTestWa" class="bg-white dark:bg-slate-800 rounded-3xl p-6 sm:p-8 border border-slate-200/80 dark:border-slate-700/80 shadow-xs space-y-4">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-2xl bg-emerald-600 text-white flex items-center justify-center text-lg shadow-sm">
                                <i class="fa-brands fa-whatsapp"></i>
                            </div>
                            <h3 class="font-extrabold text-slate-900 dark:text-white text-sm">
                                Uji Coba Kirim Pesan
                            </h3>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1">Nomor WhatsApp Tujuan</label>
                            <input v-model="testWaForm.target_phone" type="text" placeholder="08xxxxxxxxxx" class="w-full px-4 py-2.5 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs font-semibold focus:ring-2 focus:ring-emerald-500 focus:outline-none" />
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1">Pesan Uji Coba</label>
                            <textarea v-model="testWaForm.test_message" rows="3" class="w-full px-4 py-2.5 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none"></textarea>
                        </div>

                        <button type="submit" class="w-full py-2.5 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-600/20 transition cursor-pointer">
                            <i class="fa-solid fa-paper-plane me-1"></i> Kirim Pesan Uji Coba
                        </button>
                    </form>
                </div>
            </div>

            <!-- Tab 3: Video Header -->
            <div v-show="activeTab === 'video'" class="max-w-4xl space-y-6">
                <form @submit.prevent="submitVideo" class="bg-white dark:bg-slate-800 rounded-3xl p-6 sm:p-8 border border-slate-200/80 dark:border-slate-700/80 shadow-xs space-y-6">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3 class="font-extrabold text-slate-900 dark:text-white text-base flex items-center gap-2">
                                <i class="fa-brands fa-youtube text-rose-500 text-lg"></i>
                                Pengaturan Video Header Beranda
                            </h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                Tampilkan video profil paroki atau sambutan pastor di latar belakang bagian atas (Hero Banner) website publik.
                            </p>
                        </div>
                        <span
                            :class="[
                                'px-3 py-1 rounded-full text-xs font-extrabold tracking-wide uppercase',
                                videoForm.video_header_status === 'Aktif'
                                    ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300 border border-emerald-300'
                                    : 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300 border border-slate-300'
                            ]"
                        >
                            {{ videoForm.video_header_status === 'Aktif' ? 'Video Aktif' : 'Video Nonaktif' }}
                        </span>
                    </div>

                    <!-- Status Toggle & Tipe Sumber -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="p-4 rounded-2xl border bg-slate-50 dark:bg-slate-900 border-slate-200 dark:border-slate-700">
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1">Status Penayangan Video</label>
                            <select v-model="videoForm.video_header_status" class="w-full px-3.5 py-2 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-bold text-slate-800 dark:text-white focus:ring-2 focus:ring-rose-500 focus:outline-none">
                                <option value="Aktif">Aktif (Tampilkan Video di Beranda)</option>
                                <option value="Nonaktif">Nonaktif (Gunakan Banner Statis)</option>
                            </select>
                        </div>

                        <div class="p-4 rounded-2xl border bg-slate-50 dark:bg-slate-900 border-slate-200 dark:border-slate-700">
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1">Sumber Video (Video Source)</label>
                            <select v-model="videoForm.video_header_type" class="w-full px-3.5 py-2 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-bold text-slate-800 dark:text-white focus:ring-2 focus:ring-rose-500 focus:outline-none">
                                <option value="youtube">YouTube URL (Rekomendasi Cepat &amp; Hemat Kuota)</option>
                                <option value="mp4">Upload Berkas Video MP4 Langsung</option>
                            </select>
                        </div>
                    </div>

                    <!-- Input Berdasarkan Tipe -->
                    <div v-if="videoForm.video_header_type === 'youtube'" class="space-y-2">
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-200">
                            URL Video YouTube
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-rose-500">
                                <i class="fa-brands fa-youtube"></i>
                            </div>
                            <input v-model="videoForm.video_header_url" type="text" placeholder="https://www.youtube.com/watch?v=..." class="w-full pl-9 pr-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs font-mono focus:ring-2 focus:ring-rose-500 focus:outline-none" />
                        </div>
                        <span class="text-[11px] text-slate-400">Mendukung format tautan: <code>https://www.youtube.com/watch?v=XXXX</code> atau <code>https://youtu.be/XXXX</code></span>
                    </div>

                    <div v-if="videoForm.video_header_type === 'mp4'" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1">Upload Berkas Video MP4</label>
                            <input type="file" @change="e => videoForm.video_header_file = e.target.files[0]" accept="video/mp4,video/webm" class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3.5 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-rose-50 file:text-rose-700 hover:file:bg-rose-100" />
                            <span class="text-[10px] text-slate-400 mt-1 block">Format: MP4 / WebM (Maksimal 50MB)</span>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1">Upload Poster Preview (Gambar Thumbnail)</label>
                            <input type="file" @change="e => videoForm.video_header_poster = e.target.files[0]" accept="image/*" class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3.5 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200" />
                        </div>
                    </div>

                    <!-- Teks Overlay Header -->
                    <div class="pt-3 border-t border-slate-100 dark:border-slate-700 space-y-4">
                        <h4 class="text-xs font-black uppercase tracking-wider text-slate-400">Konten Teks di Atas Video (Hero Overlay)</h4>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1">Judul Utama Video Header</label>
                                <input v-model="videoForm.video_header_title" type="text" placeholder="Contoh: Profil Paroki St. Vinsensius a Paulo Benlutu" class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs font-semibold focus:ring-2 focus:ring-rose-500 focus:outline-none" />
                            </div>

                            <div class="sm:col-span-2">
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1">Subjudul / Slogan Paroki</label>
                                <textarea v-model="videoForm.video_header_subtitle" rows="2" placeholder="Contoh: Gereja yang Bersekutu, Berakar dalam Iman, dan Berbuah dalam Kasih Karitas." class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs focus:ring-2 focus:ring-rose-500 focus:outline-none"></textarea>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1">Teks Tombol Aksi (CTA)</label>
                                <input v-model="videoForm.video_header_btn_text" type="text" placeholder="Contoh: Lihat Jadwal Misa / Profil Paroki" class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs font-semibold focus:ring-2 focus:ring-rose-500 focus:outline-none" />
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1">Tautan Tombol (Link URL)</label>
                                <input v-model="videoForm.video_header_btn_link" type="text" placeholder="/jadwal-misa atau /profil" class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs focus:ring-2 focus:ring-rose-500 focus:outline-none" />
                            </div>
                        </div>
                    </div>

                    <!-- Pengaturan Player & Overlay -->
                    <div class="pt-3 border-t border-slate-100 dark:border-slate-700 space-y-3">
                        <h4 class="text-xs font-black uppercase tracking-wider text-slate-400">Opsi Pemutar Video &amp; Lapisan Gelap</h4>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <label class="flex items-center gap-2.5 p-3 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 cursor-pointer">
                                <input v-model="videoForm.video_header_autoplay" true-value="1" false-value="0" type="checkbox" class="w-4 h-4 rounded text-rose-600 focus:ring-rose-500" />
                                <span class="text-xs font-bold text-slate-800 dark:text-white">Putar Otomatis (Autoplay)</span>
                            </label>

                            <label class="flex items-center gap-2.5 p-3 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 cursor-pointer">
                                <input v-model="videoForm.video_header_muted" true-value="1" false-value="0" type="checkbox" class="w-4 h-4 rounded text-rose-600 focus:ring-rose-500" />
                                <span class="text-xs font-bold text-slate-800 dark:text-white">Bisukan Suara (Muted)</span>
                            </label>

                            <label class="flex items-center gap-2.5 p-3 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 cursor-pointer">
                                <input v-model="videoForm.video_header_loop" true-value="1" false-value="0" type="checkbox" class="w-4 h-4 rounded text-rose-600 focus:ring-rose-500" />
                                <span class="text-xs font-bold text-slate-800 dark:text-white">Putar Terus (Loop)</span>
                            </label>
                        </div>
                    </div>

                    <!-- Live Simulator Preview Box -->
                    <div class="space-y-2">
                        <span class="text-[10px] uppercase font-black text-rose-600 dark:text-rose-400 tracking-wider">Live Preview Simulator Header Beranda</span>
                        <div class="relative rounded-2xl overflow-hidden aspect-video bg-slate-950 border border-slate-200 dark:border-slate-700 shadow-inner flex items-center justify-center text-center p-6">
                            <!-- Background Video / Embed -->
                            <div class="absolute inset-0 pointer-events-none opacity-60">
                                <iframe v-if="videoForm.video_header_type === 'youtube' && videoForm.video_header_url" :src="getYoutubeEmbed(videoForm.video_header_url) + '?autoplay=1&mute=1&loop=1&controls=0'" class="w-full h-full object-cover pointer-events-none scale-125" frameborder="0"></iframe>
                                <div v-else class="w-full h-full bg-gradient-to-tr from-slate-900 via-slate-800 to-indigo-950 flex items-center justify-center">
                                    <i class="fa-solid fa-film text-6xl text-slate-700"></i>
                                </div>
                            </div>

                            <!-- Overlay Gradient -->
                            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/40 to-black/60 pointer-events-none"></div>

                            <!-- Text Overlay Live Content -->
                            <div class="relative z-10 space-y-3 max-w-lg text-white">
                                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-rose-600/80 backdrop-blur-md text-white text-[10px] font-extrabold uppercase tracking-wider">
                                    <i class="fa-solid fa-play text-[8px]"></i> Video Header
                                </div>
                                <h2 class="text-lg sm:text-2xl font-black tracking-tight leading-snug drop-shadow-md">
                                    {{ videoForm.video_header_title || 'Judul Video Header' }}
                                </h2>
                                <p class="text-xs text-slate-200 drop-shadow line-clamp-2">
                                    {{ videoForm.video_header_subtitle || 'Subjudul dan deskripsi singkat video paroki.' }}
                                </p>
                                <div class="pt-2">
                                    <span class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-amber-500 text-slate-950 font-extrabold text-xs shadow-lg">
                                        {{ videoForm.video_header_btn_text || 'Lihat Jadwal Misa' }}
                                        <i class="fa-solid fa-arrow-right text-[10px]"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end pt-3 border-t border-slate-100 dark:border-slate-700">
                        <button type="submit" class="px-6 py-2.5 rounded-2xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-lg shadow-rose-600/30 transition cursor-pointer">
                            <i class="fa-solid fa-save me-1.5"></i> Simpan Video Header
                        </button>
                    </div>
                </form>
            </div>

            <!-- Tab 4: Banner Slider -->
            <div v-show="activeTab === 'slider'" class="space-y-6">
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
                    <div>
                        <h3 class="font-extrabold text-slate-900 dark:text-white text-base">
                            Banner Slider Beranda
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            Kelola slide gambar dan teks promosi yang tampil berputar di halaman utama.
                        </p>
                    </div>
                    <button
                        @click="openSliderModal()"
                        class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-2xl bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold shadow-md shadow-amber-600/20 transition cursor-pointer"
                    >
                        <i class="fa-solid fa-plus"></i>
                        <span>Tambah Slide Banner</span>
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                    <div
                        v-for="slide in sliders"
                        :key="slide.id"
                        class="bg-white dark:bg-slate-800 rounded-3xl p-5 border border-slate-200/80 dark:border-slate-700/80 shadow-xs flex flex-col justify-between"
                    >
                        <div class="space-y-3">
                            <div class="w-full h-36 bg-slate-100 dark:bg-slate-900 rounded-2xl overflow-hidden border border-slate-200/80 dark:border-slate-700/80 relative">
                                <img v-if="slide.gambar" :src="'/' + slide.gambar.replace(/^\//, '')" alt="Slide" class="w-full h-full object-cover" />
                                <span class="absolute top-2 right-2 text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-900/80 text-white">
                                    Urutan: {{ slide.urutan }}
                                </span>
                            </div>

                            <div>
                                <h4 class="font-extrabold text-slate-900 dark:text-white text-sm line-clamp-1">
                                    {{ slide.judul }}
                                </h4>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 line-clamp-2">
                                    {{ slide.subjudul }}
                                </p>
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-700 flex items-center justify-end gap-2">
                            <button
                                @click="openSliderModal(slide)"
                                class="px-3.5 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold transition cursor-pointer"
                            >
                                <i class="fa-solid fa-pen-to-square text-amber-500 me-1"></i> Edit
                            </button>
                            <button
                                @click="deleteSlider(slide)"
                                class="px-3.5 py-1.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 dark:bg-rose-950 text-xs font-bold transition cursor-pointer"
                            >
                                <i class="fa-solid fa-trash me-1"></i> Hapus
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab 5: SEO & Meta Tags -->
            <div v-show="activeTab === 'seo'" class="max-w-3xl space-y-6">
                <form @submit.prevent="submitSeo" class="bg-white dark:bg-slate-800 rounded-3xl p-6 sm:p-8 border border-slate-200/80 dark:border-slate-700/80 shadow-xs space-y-5">
                    <div>
                        <h3 class="font-extrabold text-slate-900 dark:text-white text-base">
                            Optimasi Mesin Pencari (SEO Google) &amp; Meta Tags
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                            Kustomisasi bagaimana website paroki muncul saat dicari di Google dan dibagikan di WhatsApp/Facebook.
                        </p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1">Meta Title (Judul di Google)</label>
                        <input v-model="seoForm.meta_title" type="text" class="w-full px-4 py-2.5 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs font-semibold focus:ring-2 focus:ring-sky-500 focus:outline-none" />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1">Meta Description (Deskripsi Ringkasan)</label>
                        <textarea v-model="seoForm.meta_description" rows="3" class="w-full px-4 py-2.5 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs focus:ring-2 focus:ring-sky-500 focus:outline-none"></textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1">Keywords (Kata Kunci)</label>
                        <input v-model="seoForm.meta_keywords" type="text" class="w-full px-4 py-2.5 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs focus:ring-2 focus:ring-sky-500 focus:outline-none" />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1">Google Analytics ID / Tag Manager</label>
                        <input v-model="seoForm.google_analytics_id" type="text" placeholder="G-XXXXXXXXXX" class="w-full px-4 py-2.5 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs font-mono focus:ring-2 focus:ring-sky-500 focus:outline-none" />
                    </div>

                    <!-- Google Search Preview -->
                    <div class="p-4 bg-slate-50 dark:bg-slate-900/60 rounded-2xl border border-slate-200 dark:border-slate-700 space-y-1">
                        <span class="text-[10px] uppercase font-bold text-slate-400 block mb-1">Preview di Pencarian Google</span>
                        <h4 class="text-sm font-semibold text-blue-700 dark:text-blue-400 hover:underline cursor-pointer">
                            {{ seoForm.meta_title }}
                        </h4>
                        <p class="text-xs text-emerald-700 dark:text-emerald-500 font-mono">https://parokibenlutu.org</p>
                        <p class="text-xs text-slate-600 dark:text-slate-300">{{ seoForm.meta_description }}</p>
                    </div>

                    <div class="flex justify-end pt-3 border-t border-slate-100 dark:border-slate-700">
                        <button type="submit" class="px-6 py-2.5 rounded-2xl bg-sky-600 hover:bg-sky-700 text-white font-bold text-xs shadow-lg shadow-sky-600/30 transition cursor-pointer">
                            <i class="fa-solid fa-save me-1"></i> Simpan Pengaturan SEO
                        </button>
                    </div>
                </form>
            </div>

            <!-- Tab 6: Widget & Medsos -->
            <div v-show="activeTab === 'widget'" class="max-w-3xl space-y-6">
                <form @submit.prevent="submitWidget" class="bg-white dark:bg-slate-800 rounded-3xl p-6 sm:p-8 border border-slate-200/80 dark:border-slate-700/80 shadow-xs space-y-5">
                    <div>
                        <h3 class="font-extrabold text-slate-900 dark:text-white text-base">
                            Widget Tampilan &amp; Media Sosial
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                            Atur widget yang aktif di beranda dan tautan akun media sosial paroki.
                        </p>
                    </div>

                    <div class="space-y-3 pt-2">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input v-model="widgetForm.widget_jadwal_misa" true-value="1" false-value="0" type="checkbox" class="w-4 h-4 rounded text-indigo-600" />
                            <span class="text-xs font-bold text-slate-900 dark:text-white">Tampilkan Widget Jadwal Misa di Beranda</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input v-model="widgetForm.widget_renungan" true-value="1" false-value="0" type="checkbox" class="w-4 h-4 rounded text-indigo-600" />
                            <span class="text-xs font-bold text-slate-900 dark:text-white">Tampilkan Widget Renungan Rohani di Beranda</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input v-model="widgetForm.widget_statistik" true-value="1" false-value="0" type="checkbox" class="w-4 h-4 rounded text-indigo-600" />
                            <span class="text-xs font-bold text-slate-900 dark:text-white">Tampilkan Widget Statistik &amp; Demografi Umat</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input v-model="widgetForm.widget_kapela" true-value="1" false-value="0" type="checkbox" class="w-4 h-4 rounded text-indigo-600" />
                            <span class="text-xs font-bold text-slate-900 dark:text-white">Tampilkan Widget Peta Stasi / Kapela Paroki</span>
                        </label>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-4 border-t border-slate-100 dark:border-slate-700">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1">Jam Operasional Sekretariat Paroki</label>
                            <input v-model="widgetForm.jam_operasional" type="text" placeholder="Senin - Sabtu: 08.00 - 14.00 WITA" class="w-full px-4 py-2.5 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs font-semibold focus:ring-2 focus:ring-indigo-500 focus:outline-none" />
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1">Facebook URL</label>
                            <input v-model="widgetForm.facebook_url" type="text" placeholder="https://facebook.com/..." class="w-full px-4 py-2.5 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none" />
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1">Instagram URL</label>
                            <input v-model="widgetForm.instagram_url" type="text" placeholder="https://instagram.com/..." class="w-full px-4 py-2.5 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none" />
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1">YouTube Channel URL</label>
                            <input v-model="widgetForm.youtube_url" type="text" placeholder="https://youtube.com/..." class="w-full px-4 py-2.5 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none" />
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1">TikTok URL</label>
                            <input v-model="widgetForm.tiktok_url" type="text" placeholder="https://tiktok.com/..." class="w-full px-4 py-2.5 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none" />
                        </div>
                    </div>

                    <div class="flex justify-end pt-3 border-t border-slate-100 dark:border-slate-700">
                        <button type="submit" class="px-6 py-2.5 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-lg shadow-indigo-600/30 transition cursor-pointer">
                            <i class="fa-solid fa-save me-1"></i> Simpan Widget &amp; Medsos
                        </button>
                    </div>
                </form>
            </div>

            <!-- Tab 7: Mode Maintenance -->
            <div v-show="activeTab === 'maintenance'" class="max-w-3xl space-y-6">
                <form @submit.prevent="submitMaintenance" class="bg-white dark:bg-slate-800 rounded-3xl p-6 sm:p-8 border border-slate-200/80 dark:border-slate-700/80 shadow-xs space-y-5">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3 class="font-extrabold text-slate-900 dark:text-white text-base flex items-center gap-2">
                                <i class="fa-solid fa-screwdriver-wrench text-orange-500"></i>
                                Pengaturan Mode Maintenance (Pemeliharaan Sistem)
                            </h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                Aktifkan halaman pemeliharaan saat sistem sedang diperbarui. Admin yang login tetap dapat mengakses panel admin seperti biasa.
                            </p>
                        </div>
                        <span
                            :class="[
                                'px-3 py-1 rounded-full text-xs font-extrabold tracking-wide uppercase',
                                maintenanceForm.maintenance_mode === '1'
                                    ? 'bg-rose-100 text-rose-700 dark:bg-rose-950 dark:text-rose-300 border border-rose-300'
                                    : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300 border border-emerald-300'
                            ]"
                        >
                            {{ maintenanceForm.maintenance_mode === '1' ? 'Perawatan Aktif' : 'Website Online' }}
                        </span>
                    </div>

                    <!-- Status Toggle Card -->
                    <div class="p-5 rounded-2xl border" :class="maintenanceForm.maintenance_mode === '1' ? 'bg-rose-50/50 border-rose-200 dark:bg-rose-950/20 dark:border-rose-900/40' : 'bg-slate-50 border-slate-200 dark:bg-slate-900 dark:border-slate-700'">
                        <label class="flex items-center justify-between cursor-pointer">
                            <div class="space-y-0.5">
                                <span class="text-xs font-extrabold text-slate-900 dark:text-white">
                                    Aktifkan Mode Maintenance Website Publik
                                </span>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400">
                                    Pengunjung umum akan melihat halaman pemeliharaan, sementara Admin tetap dapat login ke panel.
                                </p>
                            </div>
                            <input v-model="maintenanceForm.maintenance_mode" true-value="1" false-value="0" type="checkbox" class="w-5 h-5 rounded text-orange-600 focus:ring-orange-500 cursor-pointer" />
                        </label>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1">Judul Halaman Pemeliharaan</label>
                        <input v-model="maintenanceForm.maintenance_title" type="text" placeholder="Contoh: Website Sedang Dalam Pemeliharaan / Perawatan" class="w-full px-4 py-2.5 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs font-semibold focus:ring-2 focus:ring-orange-500 focus:outline-none" />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1">Pesan / Keterangan untuk Jemaat</label>
                        <textarea v-model="maintenanceForm.maintenance_message" rows="3" placeholder="Tuliskan alasan pemeliharaan dan permohonan maaf..." class="w-full px-4 py-2.5 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs focus:ring-2 focus:ring-orange-500 focus:outline-none"></textarea>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1">Estimasi Waktu Selesai (WITA)</label>
                            <input v-model="maintenanceForm.maintenance_until" type="text" placeholder="Contoh: 25 Agustus 2026, 17:00 WITA" class="w-full px-4 py-2.5 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs font-semibold focus:ring-2 focus:ring-orange-500 focus:outline-none" />
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1">Kontak Darurat Sekretariat</label>
                            <input v-model="maintenanceForm.maintenance_contact" type="text" placeholder="Contoh: 0812-3456-7890 (Sekretariat Paroki)" class="w-full px-4 py-2.5 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs font-semibold focus:ring-2 focus:ring-orange-500 focus:outline-none" />
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1">Kunci Bypass URL Rahasia (Secret Key)</label>
                            <div class="flex items-center gap-2">
                                <input v-model="maintenanceForm.maintenance_bypass_key" type="text" placeholder="siparoki2026" class="w-full px-4 py-2.5 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs font-mono focus:ring-2 focus:ring-orange-500 focus:outline-none" />
                            </div>
                            <span class="text-[11px] text-slate-400 mt-1 block">Akses darurat langsung tanpa login: <code>http://127.0.0.1:8000/?bypass={{ maintenanceForm.maintenance_bypass_key }}</code></span>
                        </div>
                    </div>

                    <!-- Live Preview Box -->
                    <div class="p-6 rounded-3xl bg-gradient-to-br from-amber-500/10 via-orange-500/5 to-slate-900/10 border border-orange-200 dark:border-orange-900/40 space-y-3">
                        <span class="text-[10px] uppercase font-black text-orange-600 dark:text-orange-400 tracking-wider">Preview Halaman Maintenance Publik</span>
                        <div class="text-center py-4 space-y-2">
                            <div class="w-12 h-12 mx-auto rounded-2xl bg-orange-500 text-white flex items-center justify-center text-xl shadow-lg shadow-orange-500/30">
                                <i class="fa-solid fa-screwdriver-wrench"></i>
                            </div>
                            <h4 class="font-black text-slate-900 dark:text-white text-base">
                                {{ maintenanceForm.maintenance_title }}
                            </h4>
                            <p class="text-xs text-slate-600 dark:text-slate-300 max-w-md mx-auto">
                                {{ maintenanceForm.maintenance_message }}
                            </p>
                            <div v-if="maintenanceForm.maintenance_until" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-orange-100 dark:bg-orange-950 text-orange-800 dark:text-orange-300 text-[11px] font-bold">
                                <i class="fa-solid fa-clock"></i> Estimasi Selesai: {{ maintenanceForm.maintenance_until }}
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end pt-3 border-t border-slate-100 dark:border-slate-700">
                        <button type="submit" class="px-6 py-2.5 rounded-2xl bg-orange-600 hover:bg-orange-700 text-white font-bold text-xs shadow-lg shadow-orange-600/30 transition cursor-pointer">
                            <i class="fa-solid fa-save me-1"></i> Simpan Mode Maintenance
                        </button>
                    </div>
                </form>
            </div>

        </div>

        <!-- Modal Tambah/Edit Metode Pembayaran -->
        <div v-if="paymentModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-sm">
            <div class="bg-white dark:bg-slate-800 rounded-3xl max-w-md w-full p-6 sm:p-8 border border-slate-200/80 dark:border-slate-700/80 shadow-2xl space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="font-extrabold text-slate-900 dark:text-white text-base">
                        {{ editingPayment ? 'Edit Metode Pembayaran' : 'Tambah Metode Pembayaran' }}
                    </h3>
                    <button @click="paymentModal = false" class="text-slate-400 hover:text-slate-600 cursor-pointer">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                <form @submit.prevent="submitPayment" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1">Tipe Metode</label>
                        <select v-model="paymentForm.tipe" class="w-full px-4 py-2.5 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs font-semibold focus:ring-2 focus:ring-teal-500 focus:outline-none">
                            <option value="Transfer Bank">Transfer Bank</option>
                            <option value="QRIS">QRIS (Semua E-Wallet / Bank)</option>
                            <option value="Tunai">Tunai / Sekretariat</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1">Nama Bank / Pembayaran</label>
                        <input v-model="paymentForm.nama_bank" type="text" placeholder="Contoh: Bank BRI, Bank NTT, QRIS Paroki" required class="w-full px-4 py-2.5 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs font-semibold focus:ring-2 focus:ring-teal-500 focus:outline-none" />
                    </div>

                    <div v-if="paymentForm.tipe !== 'QRIS'">
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1">Nomor Rekening</label>
                        <input v-model="paymentForm.nomor_rekening" type="text" placeholder="Contoh: 0123-01-000456-50-8" class="w-full px-4 py-2.5 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs font-mono focus:ring-2 focus:ring-teal-500 focus:outline-none" />
                    </div>

                    <div v-if="paymentForm.tipe !== 'QRIS'">
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1">Atas Nama Rekening</label>
                        <input v-model="paymentForm.atas_nama" type="text" placeholder="Contoh: PGPM Paroki Benlutu" class="w-full px-4 py-2.5 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs font-semibold focus:ring-2 focus:ring-teal-500 focus:outline-none" />
                    </div>

                    <div v-if="paymentForm.tipe === 'QRIS'">
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1">Upload Gambar QRIS (JPG / PNG)</label>
                        <input type="file" @change="e => paymentForm.gambar_qris = e.target.files[0]" accept="image/*" class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-2xl file:border-0 file:text-xs file:font-semibold file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100" />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1">Petunjuk Pembayaran</label>
                        <textarea v-model="paymentForm.petunjuk" rows="2" placeholder="Panduan transfer bagi umat..." class="w-full px-4 py-2.5 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs focus:ring-2 focus:ring-teal-500 focus:outline-none"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button type="button" @click="paymentModal = false" class="px-4 py-2 rounded-2xl bg-slate-100 dark:bg-slate-700 text-xs font-bold cursor-pointer">Batal</button>
                        <button type="submit" class="px-5 py-2 rounded-2xl bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold shadow-md shadow-teal-600/20 cursor-pointer">Simpan</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Tambah/Edit Slide Banner -->
        <div v-if="sliderModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-sm">
            <div class="bg-white dark:bg-slate-800 rounded-3xl max-w-md w-full p-6 sm:p-8 border border-slate-200/80 dark:border-slate-700/80 shadow-2xl space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="font-extrabold text-slate-900 dark:text-white text-base">
                        {{ editingSlider ? 'Edit Slide Banner' : 'Tambah Slide Banner' }}
                    </h3>
                    <button @click="sliderModal = false" class="text-slate-400 hover:text-slate-600 cursor-pointer">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                <form @submit.prevent="submitSlider" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1">Judul Slide</label>
                        <input v-model="sliderForm.judul" type="text" placeholder="Judul besar pada banner" required class="w-full px-4 py-2.5 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs font-semibold focus:ring-2 focus:ring-amber-500 focus:outline-none" />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1">Subjudul / Deskripsi</label>
                        <textarea v-model="sliderForm.subjudul" rows="2" placeholder="Deskripsi singkat slide" class="w-full px-4 py-2.5 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs focus:ring-2 focus:ring-amber-500 focus:outline-none"></textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1">Upload Gambar Banner (JPG / PNG / WebP)</label>
                        <input type="file" @change="e => sliderForm.gambar = e.target.files[0]" accept="image/*" class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-2xl file:border-0 file:text-xs file:font-semibold file:bg-amber-50 file:text-amber-700 hover:file:bg-amber-100" />
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1">Teks Tombol</label>
                            <input v-model="sliderForm.tombol_teks" type="text" class="w-full px-4 py-2.5 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs font-semibold focus:outline-none" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1">Link URL</label>
                            <input v-model="sliderForm.link_url" type="text" placeholder="/profil" class="w-full px-4 py-2.5 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs focus:outline-none" />
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button type="button" @click="sliderModal = false" class="px-4 py-2 rounded-2xl bg-slate-100 dark:bg-slate-700 text-xs font-bold cursor-pointer">Batal</button>
                        <button type="submit" class="px-5 py-2 rounded-2xl bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold shadow-md shadow-amber-600/20 cursor-pointer">Simpan</button>
                    </div>
                </form>
            </div>
        </div>

    </AppLayout>
</template>
