<script setup>
import { ref, computed, watch } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import DateInput from '@/Components/DateInput.vue';

const page = usePage();

const props = defineProps({
    title: { type: String, default: 'Tambah Album Galeri Baru' },
    role: { type: String, default: 'Super Admin' },
    prefix: { type: String, default: 'superadmin' },
    isEdit: { type: Boolean, default: false },
    item: { type: Object, default: () => null },
    kategoriList: {
        type: Array,
        default: () => [
            'Liturgi & Misa Kudus',
            'Penerimaan Sakramen',
            'Kegiatan OMK & Remaja',
            'Kegiatan Sekami & Misdinar',
            'Pastoral & Dewan Paroki',
            'Kunjungan Uskup & Keuskupan',
            'Pembangunan & Fasilitas Gereja',
            'Sosial & Kemasyarakatan',
            'Perayaan Hari Raya',
            'Lainnya',
        ],
    },
});

const basePrefix = computed(() => `/${props.prefix || 'superadmin'}`);
const listUrl = computed(() => `${basePrefix.value}/galeri`);
const isSubmitting = ref(false);

const form = ref({
    judul: props.item?.judul || '',
    slug: props.item?.slug || '',
    album: props.item?.album || props.item?.kategori || '',
    tipe: props.item?.tipe || 'Foto',
    deskripsi: props.item?.deskripsi || '',
    tanggal: props.item?.tanggal ? String(props.item.tanggal).slice(0, 10) : new Date().toISOString().slice(0, 10),
    lokasi: props.item?.lokasi || '',
    youtube_url: props.item?.youtube_url || '',
    gambar: '',
    og_image: '',
    status_publish: props.item?.status_publish || (props.item?.status === 0 || props.item?.status === false ? 'Draft' : 'Publish'),
    urutan: props.item?.urutan ?? 0,
    meta_title: props.item?.meta_title || '',
    meta_description: props.item?.meta_description || '',
    meta_keywords: props.item?.meta_keywords || '',
});

const coverPreview = ref(props.item?.gambar ? (String(props.item.gambar).startsWith('http') ? props.item.gambar : `/${String(props.item.gambar).replace(/^\/+/, '')}`) : '');
const ogPreview = ref(props.item?.og_image ? (String(props.item.og_image).startsWith('http') ? props.item.og_image : `/${String(props.item.og_image).replace(/^\/+/, '')}`) : '');

// Slug generator
const slugifyText = (value) => String(value || '')
    .toLowerCase()
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .replace(/[^\w\s-]/g, '')
    .trim()
    .replace(/[\s_-]+/g, '-')
    .replace(/^-+|-+$/g, '');

const updateSlug = () => {
    form.value.slug = slugifyText(form.value.judul);
    if (!form.value.meta_title) {
        form.value.meta_title = form.value.judul ? `${form.value.judul} - Paroki Benlutu` : '';
    }
};

// Handle Cover Image Upload
const handleCoverFile = (e) => {
    const file = e.target.files?.[0];
    if (!file) return;
    form.value.gambar = file;
    const reader = new FileReader();
    reader.onload = (event) => {
        coverPreview.value = event.target.result;
    };
    reader.readAsDataURL(file);
};

// Handle OG Image Upload
const handleOgFile = (e) => {
    const file = e.target.files?.[0];
    if (!file) return;
    form.value.og_image = file;
    const reader = new FileReader();
    reader.onload = (event) => {
        ogPreview.value = event.target.result;
    };
    reader.readAsDataURL(file);
};

// Auto extract YouTube ID for preview
const youtubeVideoId = computed(() => {
    const url = form.value.youtube_url;
    if (!url) return '';
    const match = url.match(/(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))([\w-]{11})/);
    return match ? match[1] : '';
});

// Auto-fill thumbnail & set type when YouTube URL is pasted
watch(() => form.value.youtube_url, (newUrl) => {
    if (newUrl) {
        const id = youtubeVideoId.value;
        if (id) {
            if (form.value.tipe === 'Foto') {
                form.value.tipe = 'Video';
            }
            if (!coverPreview.value) {
                coverPreview.value = `https://img.youtube.com/vi/${id}/hqdefault.jpg`;
            }
        }
    }
});

// Character counts for SEO
const metaTitleCount = computed(() => (form.value.meta_title || '').length);
const metaDescCount = computed(() => (form.value.meta_description || '').length);

const submitForm = () => {
    isSubmitting.value = true;
    const payload = new FormData();

    // Auto append youtube fields
    if (youtubeVideoId.value) {
        payload.append('youtube_id', youtubeVideoId.value);
        payload.append('youtube_thumbnail', `https://img.youtube.com/vi/${youtubeVideoId.value}/hqdefault.jpg`);
    }

    Object.entries(form.value).forEach(([key, value]) => {
        if (value !== null && value !== undefined && value !== '') {
            payload.append(key, value);
        }
    });

    if (props.isEdit && props.item?.id) {
        payload.append('_method', 'PUT');
        router.post(`${basePrefix.value}/galeri/${props.item.id}`, payload, {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => {
                router.visit(listUrl.value);
            },
            onError: () => {
                isSubmitting.value = false;
            },
            onFinish: () => {
                isSubmitting.value = false;
            },
        });
    } else {
        router.post(`${basePrefix.value}/galeri`, payload, {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => {
                router.visit(listUrl.value);
            },
            onError: () => {
                isSubmitting.value = false;
            },
            onFinish: () => {
                isSubmitting.value = false;
            },
        });
    }
};
</script>

<template>
    <AppLayout :title="isEdit ? 'Edit Album Galeri' : 'Tambah Album Galeri Baru'">
        <Head :title="`${isEdit ? 'Edit Album Galeri' : 'Tambah Album Galeri Baru'} - SIPAROKI`" />

        <div class="max-w-6xl mx-auto space-y-4 pb-12">
            <!-- Header Card -->
            <div class="rounded-2xl bg-white border border-slate-200/80 p-4 sm:p-5 shadow-2xs">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <Link
                            :href="listUrl"
                            class="w-10 h-10 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center transition cursor-pointer shrink-0"
                            title="Kembali ke Daftar Galeri"
                        >
                            <i class="fa-solid fa-arrow-left text-sm"></i>
                        </Link>
                        <div>
                            <div class="flex items-center gap-2">
                                <h1 class="text-lg font-black text-slate-900 tracking-tight">
                                    {{ isEdit ? 'Edit Album Galeri' : 'Tambah Album Galeri Baru' }}
                                </h1>
                                <span
                                    class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border"
                                    :class="isEdit ? 'bg-amber-50 text-amber-800 border-amber-200' : 'bg-blue-50 text-blue-800 border-blue-200'"
                                >
                                    {{ isEdit ? 'Mode Edit' : 'Album Baru' }}
                                </span>
                            </div>
                            <p class="text-xs text-slate-500 mt-0.5">
                                Kelola dokumentasi foto, album kegiatan, dan video paroki secara profesional.
                            </p>
                        </div>
                    </div>

                    <!-- Top Actions -->
                    <div class="flex items-center gap-2 shrink-0">
                        <Link
                            :href="listUrl"
                            class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition flex items-center gap-1.5"
                        >
                            <i class="fa-solid fa-xmark text-xs"></i>
                            <span>Batal</span>
                        </Link>
                        <button
                            type="button"
                            :disabled="isSubmitting"
                            @click="submitForm"
                            class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-sm shadow-blue-500/25 transition flex items-center gap-1.5 cursor-pointer disabled:opacity-60"
                        >
                            <i v-if="isSubmitting" class="fa-solid fa-circle-notch fa-spin text-xs"></i>
                            <i v-else class="fa-solid fa-floppy-disk text-xs"></i>
                            <span>{{ isEdit ? 'Simpan Perubahan' : 'Simpan Album' }}</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Form Body -->
            <form @submit.prevent="submitForm" class="grid grid-cols-1 lg:grid-cols-12 gap-5">
                <!-- Left Column (Main Details) -->
                <div class="lg:col-span-8 space-y-4">
                    <!-- Album Info Card -->
                    <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-2xs space-y-4">
                        <div class="border-b border-slate-100 pb-3">
                            <h2 class="text-sm font-black text-slate-900 flex items-center gap-2">
                                <i class="fa-solid fa-images text-blue-600"></i>
                                <span>Informasi Utama Album Kegiatan</span>
                            </h2>
                            <p class="text-xs text-slate-500">Isi judul dan kategori dokumentasi kegiatan paroki.</p>
                        </div>

                        <!-- Judul Album -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                Judul Album <span class="text-rose-500">*</span>
                            </label>
                            <input
                                v-model="form.judul"
                                type="text"
                                required
                                @blur="!form.slug && updateSlug()"
                                placeholder="Contoh: Perayaan Misa Natal 2024 Paroki Benlutu"
                                class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:bg-white font-medium transition"
                            />
                        </div>

                        <!-- Slug URL -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                Slug URL
                            </label>
                            <div class="flex gap-2">
                                <input
                                    v-model="form.slug"
                                    type="text"
                                    placeholder="perayaan-misa-natal-2024-paroki-benlutu"
                                    class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:bg-white font-medium transition"
                                />
                                <button
                                    type="button"
                                    @click="updateSlug"
                                    title="Generate Slug Otomatis"
                                    class="px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold border border-slate-200 cursor-pointer"
                                >
                                    <i class="fa-solid fa-wand-magic-sparkles"></i>
                                </button>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Kategori Album -->
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                    Kategori Album <span class="text-rose-500">*</span>
                                </label>
                                <select
                                    v-model="form.album"
                                    required
                                    class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-blue-500 focus:bg-white font-medium transition cursor-pointer"
                                >
                                    <option value="">-- Pilih Kategori --</option>
                                    <option v-for="kat in kategoriList" :key="kat" :value="kat">
                                        {{ kat }}
                                    </option>
                                </select>
                            </div>

                            <!-- Tipe Album -->
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                    Tipe Album <span class="text-rose-500">*</span>
                                </label>
                                <select
                                    v-model="form.tipe"
                                    class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-blue-500 focus:bg-white font-medium transition cursor-pointer"
                                >
                                    <option value="Foto">Foto</option>
                                    <option value="Video">Video YouTube</option>
                                    <option value="Foto & Video">Foto & Video</option>
                                </select>
                            </div>
                        </div>

                        <!-- YouTube URL Field (if Video) -->
                        <div v-if="form.tipe === 'Video' || form.tipe === 'Foto & Video'">
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                Tautan / URL Video YouTube
                            </label>
                            <input
                                v-model="form.youtube_url"
                                type="url"
                                placeholder="https://www.youtube.com/watch?v=..."
                                class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:bg-white font-medium transition"
                            />
                            <!-- YouTube Embed Preview -->
                            <div v-if="youtubeVideoId" class="mt-2 rounded-xl overflow-hidden aspect-video max-w-md bg-black">
                                <iframe
                                    :src="`https://www.youtube.com/embed/${youtubeVideoId}`"
                                    class="w-full h-full border-0"
                                    allowfullscreen
                                ></iframe>
                            </div>
                        </div>

                        <!-- Deskripsi Album -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                Deskripsi Album
                            </label>
                            <textarea
                                v-model="form.deskripsi"
                                rows="3"
                                placeholder="Keterangan singkat tentang album kegiatan paroki ini..."
                                class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:bg-white font-medium transition resize-none"
                            ></textarea>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Tanggal Kegiatan -->
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                    Tanggal Kegiatan
                                </label>
                                <DateInput
                                    v-model="form.tanggal"
                                    placeholder="dd/mm/yyyy"
                                    inputClass="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-blue-500 focus:bg-white font-medium transition"
                                />
                            </div>

                            <!-- Lokasi Kegiatan -->
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                    Lokasi Kegiatan
                                </label>
                                <input
                                    v-model="form.lokasi"
                                    type="text"
                                    placeholder="e.g. Gereja Pusat Paroki Benlutu"
                                    class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:bg-white font-medium transition"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Pengaturan SEO Album -->
                    <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-2xs space-y-4">
                        <div class="border-b border-slate-100 pb-3">
                            <h2 class="text-sm font-black text-slate-900 flex items-center gap-2">
                                <i class="fa-brands fa-google text-amber-500"></i>
                                <span>Pengaturan SEO Album</span>
                            </h2>
                            <p class="text-xs text-slate-500">Optimasi visibilitas album galeri pada mesin pencari Google dan media sosial.</p>
                        </div>

                        <!-- Meta Title -->
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="block text-xs font-bold text-slate-700">Meta Title</label>
                                <span class="text-[10px]" :class="metaTitleCount > 60 ? 'text-rose-500 font-bold' : 'text-slate-400'">
                                    {{ metaTitleCount }} / 60 karakter
                                </span>
                            </div>
                            <input
                                v-model="form.meta_title"
                                type="text"
                                maxlength="80"
                                placeholder="Judul untuk mesin pencari Google (maks 60 karakter)"
                                class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:bg-white font-medium transition"
                            />
                        </div>

                        <!-- Meta Description -->
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="block text-xs font-bold text-slate-700">Meta Description</label>
                                <span class="text-[10px]" :class="metaDescCount > 160 ? 'text-rose-500 font-bold' : 'text-slate-400'">
                                    {{ metaDescCount }} / 160 karakter
                                </span>
                            </div>
                            <textarea
                                v-model="form.meta_description"
                                rows="2"
                                maxlength="200"
                                placeholder="Deskripsi singkat untuk mesin pencari Google (maks 160 karakter)..."
                                class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:bg-white font-medium transition resize-none"
                            ></textarea>
                        </div>

                        <!-- Meta Keywords -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                Meta Keywords
                            </label>
                            <input
                                v-model="form.meta_keywords"
                                type="text"
                                placeholder="Kata kunci pencarian, pisahkan dengan koma (contoh: galeri, misa natal, benlutu, gereja)"
                                class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:bg-white font-medium transition"
                            />
                        </div>
                    </div>
                </div>

                <!-- Right Column (Media & Status) -->
                <div class="lg:col-span-4 space-y-4">
                    <!-- Status & Urutan Card -->
                    <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-2xs space-y-4">
                        <h2 class="text-sm font-black text-slate-900 border-b border-slate-100 pb-3">
                            Status & Publikasi
                        </h2>

                        <!-- Status Publish -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                Status Publish
                            </label>
                            <select
                                v-model="form.status_publish"
                                class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-blue-500 focus:bg-white font-medium transition cursor-pointer"
                            >
                                <option value="Publish">Publish (Tampil Publik)</option>
                                <option value="Draft">Draft (Simpan Sementara)</option>
                            </select>
                        </div>

                        <!-- Urutan Tampil -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                Urutan Tampil
                            </label>
                            <input
                                v-model.number="form.urutan"
                                type="number"
                                min="0"
                                placeholder="0"
                                class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-blue-500 focus:bg-white font-medium transition"
                            />
                            <p class="text-[10.5px] text-slate-400 mt-1">Nilai lebih kecil tampil lebih awal.</p>
                        </div>
                    </div>

                    <!-- Cover Album Card -->
                    <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-2xs space-y-3">
                        <h2 class="text-sm font-black text-slate-900 border-b border-slate-100 pb-2">
                            Cover Album (Upload File Manual)
                        </h2>
                        
                        <div v-if="coverPreview" class="rounded-xl overflow-hidden border border-slate-200 bg-slate-100 aspect-video relative group">
                            <img :src="coverPreview" alt="Cover Preview" class="w-full h-full object-cover" />
                            <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition flex items-center justify-center">
                                <label class="px-3 py-1.5 rounded-lg bg-white/90 hover:bg-white text-slate-900 text-xs font-bold transition cursor-pointer">
                                    Ganti Cover
                                    <input type="file" accept="image/*" class="hidden" @change="handleCoverFile" />
                                </label>
                            </div>
                        </div>

                        <label v-else class="border-2 border-dashed border-slate-200 hover:border-blue-400 rounded-xl p-4 flex flex-col items-center justify-center text-center cursor-pointer transition bg-slate-50/50 hover:bg-blue-50/30">
                            <i class="fa-solid fa-cloud-arrow-up text-slate-400 text-2xl mb-1.5"></i>
                            <span class="text-xs font-bold text-slate-700">Pilih gambar cover manual...</span>
                            <span class="text-[10.5px] text-slate-400 mt-0.5">JPG, PNG, WebP — maks 2MB (Bisa dikosongkan jika pakai YouTube)</span>
                            <input type="file" accept="image/*" class="hidden" @change="handleCoverFile" />
                        </label>
                    </div>

                    <!-- OG Image Card -->
                    <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-2xs space-y-3">
                        <h2 class="text-sm font-black text-slate-900 border-b border-slate-100 pb-2">
                            OG Image (Share Social Media)
                        </h2>
                        
                        <div v-if="ogPreview" class="rounded-xl overflow-hidden border border-slate-200 bg-slate-100 aspect-video relative group">
                            <img :src="ogPreview" alt="OG Preview" class="w-full h-full object-cover" />
                            <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition flex items-center justify-center">
                                <label class="px-3 py-1.5 rounded-lg bg-white/90 hover:bg-white text-slate-900 text-xs font-bold transition cursor-pointer">
                                    Ganti OG Image
                                    <input type="file" accept="image/*" class="hidden" @change="handleOgFile" />
                                </label>
                            </div>
                        </div>

                        <label v-else class="border-2 border-dashed border-slate-200 hover:border-blue-400 rounded-xl p-4 flex flex-col items-center justify-center text-center cursor-pointer transition bg-slate-50/50 hover:bg-blue-50/30">
                            <i class="fa-solid fa-share-nodes text-slate-400 text-2xl mb-1.5"></i>
                            <span class="text-xs font-bold text-slate-700">Pilih gambar OG...</span>
                            <span class="text-[10.5px] text-slate-400 mt-0.5">Rekomendasi 1200x630px</span>
                            <input type="file" accept="image/*" class="hidden" @change="handleOgFile" />
                        </label>
                    </div>
                </div>

                <!-- Bottom Sticky Bar -->
                <div class="lg:col-span-12 rounded-2xl bg-white border border-slate-200/80 p-4 shadow-2xs flex items-center justify-between">
                    <div class="text-xs text-slate-500">
                        Pastikan seluruh data yang bertanda bintang <span class="text-rose-500 font-bold">*</span> terisi dengan benar.
                    </div>
                    <div class="flex items-center gap-2">
                        <Link
                            :href="listUrl"
                            class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition flex items-center gap-1.5"
                        >
                            <i class="fa-solid fa-xmark text-xs"></i>
                            <span>Batal</span>
                        </Link>
                        <button
                            type="submit"
                            :disabled="isSubmitting"
                            class="px-5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-sm shadow-blue-500/25 transition flex items-center gap-1.5 cursor-pointer disabled:opacity-60"
                        >
                            <i v-if="isSubmitting" class="fa-solid fa-circle-notch fa-spin text-xs"></i>
                            <i v-else class="fa-solid fa-floppy-disk text-xs"></i>
                            <span>{{ isEdit ? 'Simpan Perubahan' : 'Simpan Album Galeri' }}</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
