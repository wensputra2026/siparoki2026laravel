<script setup>
import { computed, ref, watch } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import RichTextEditor from '@/Components/RichTextEditor.vue';

const page = usePage();

const props = defineProps({
    title: {
        type: String,
        default: 'Form Konten Website',
    },
    role: {
        type: String,
        default: 'Super Admin',
    },
    prefix: {
        type: String,
        default: 'superadmin',
    },
    mode: {
        type: String,
        default: 'create',
    },
    item: {
        type: Object,
        default: null,
    },
    kategoriKontenList: {
        type: Array,
        default: () => [],
    },
    penulisList: {
        type: Array,
        default: () => [],
    },
    arsipPdfList: {
        type: Array,
        default: () => [],
    },
    galeriImageList: {
        type: Array,
        default: () => [],
    },
});

const basePrefix = computed(() => `/${props.prefix || 'superadmin'}`);
const listUrl = computed(() => `${basePrefix.value}/konten`);
const isEdit = computed(() => props.mode === 'edit');
const itemId = computed(() => props.item?.id || props.item?.slug || '');
const submitUrl = computed(() => isEdit.value ? `${basePrefix.value}/konten/${itemId.value}` : `${basePrefix.value}/konten`);
const previewUrl = computed(() => isEdit.value && itemId.value ? `${basePrefix.value}/konten/${itemId.value}/preview` : '');
const pageTitle = computed(() => isEdit.value ? 'Edit Konten Website' : 'Tambah Konten Website');

const toDatetimeLocal = (value) => {
    if (!value) return '';
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return String(value).slice(0, 16);
    date.setMinutes(date.getMinutes() - date.getTimezoneOffset());
    return date.toISOString().slice(0, 16);
};

const nowForInput = () => {
    const now = new Date();
    now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
    return now.toISOString().slice(0, 16);
};

const currentUserName = computed(() => page.props.auth?.user?.nama_lengkap || page.props.auth?.user?.name || 'Administrator');

const form = ref({
    judul: props.item?.judul || '',
    slug: props.item?.slug || '',
    excerpt: props.item?.excerpt || '',
    isi: props.item?.isi || props.item?.konten || '',
    tipe: props.item?.tipe || (typeof window !== 'undefined' && new URLSearchParams(window.location.search).get('tipe') ? new URLSearchParams(window.location.search).get('tipe') : 'Berita'),
    kategori_id: props.item?.kategori_id || '',
    kategori: props.item?.kategori || '',
    gambar: '',
    file_pdf: '',
    arsip_id: props.item?.arsip_id || props.item?.arsip_digital_id || '',
    tags: props.item?.tags || '',
    status_publish: props.item?.status_publish || props.item?.status || 'Publish',
    tanggal_publish: toDatetimeLocal(props.item?.tanggal_publish || props.item?.created_at) || nowForInput(),
    is_featured: props.item?.is_featured ? 1 : 0,
    embed_pdf: props.item?.embed_pdf === 0 || props.item?.embed_pdf === false ? 0 : 1,
    penulis: props.item?.penulis || props.penulisList?.[0] || currentUserName.value,
});

const isSubmitting = ref(false);
const imagePreview = ref(props.item?.gambar ? `/${String(props.item.gambar).replace(/^\/+/, '')}` : '');
const selectedImageName = ref('');
const selectedPdfName = ref(props.item?.file_pdf || '');
const tipeOptions = ['Berita', 'Artikel', 'Halaman', 'Pengumuman', 'Renungan'];
const popularTags = ['Paroki', 'Liturgi', 'OMK', 'Misa', 'Krisma', 'Katekese', 'Sosial', 'Pengumuman', 'Renungan'];
const tagChips = computed(() => String(form.value.tags || '').split(',').map((tag) => tag.trim()).filter(Boolean));
const excerptCharCount = computed(() => String(form.value.excerpt || '').length);

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
};

const stripHtmlText = (value) => {
    const div = document.createElement('div');
    div.innerHTML = String(value || '');
    return (div.textContent || div.innerText || '')
        .replace(/\[[^\]]+\]/g, '')
        .replace(/\s+/g, ' ')
        .trim();
};

const generateExcerpt = () => {
    const cleanText = stripHtmlText(form.value.isi);
    if (!cleanText) return;
    const limit = 180;
    const trimmed = cleanText.length > limit ? cleanText.slice(0, limit) : cleanText;
    const lastSpace = trimmed.lastIndexOf(' ');
    form.value.excerpt = cleanText.length > limit && lastSpace > 50
        ? `${trimmed.slice(0, lastSpace)}...`
        : (cleanText.length > limit ? `${trimmed}...` : trimmed);
};

const syncKategori = () => {
    const kategori = props.kategoriKontenList.find((item) => String(item.id) === String(form.value.kategori_id));
    if (!kategori) return;
    form.value.kategori = kategori.nama_kategori || '';
    if (kategori.tipe) {
        form.value.tipe = kategori.tipe;
    }
};

const addTag = (tag) => {
    const current = String(form.value.tags || '')
        .split(',')
        .map((item) => item.trim())
        .filter(Boolean);
    if (!current.includes(tag)) {
        current.push(tag);
        form.value.tags = current.join(', ');
    }
};

const removeTag = (tag) => {
    form.value.tags = tagChips.value.filter((item) => item !== tag).join(', ');
};

const handleImageUpload = (event) => {
    const file = event.target.files?.[0];
    if (!file) return;
    form.value.gambar = file;
    selectedImageName.value = file.name;
    const reader = new FileReader();
    reader.onload = (e) => {
        imagePreview.value = e.target.result;
    };
    reader.readAsDataURL(file);
};

const handlePdfUpload = (event) => {
    const file = event.target.files?.[0];
    if (!file) return;
    form.value.file_pdf = file;
    selectedPdfName.value = file.name;
};

const submitForm = (action = null) => {
    if (action === 'draft') {
        form.value.status_publish = 'Draft';
    } else if (action === 'publish') {
        form.value.status_publish = 'Publish';
    }

    isSubmitting.value = true;
    const payload = new FormData();

    Object.entries(form.value).forEach(([key, value]) => {
        if (value !== null && value !== undefined) {
            payload.append(key, value);
        }
    });
    if (action) {
        payload.append('submit_action', action);
        payload.append('status', action === 'draft' ? 'Draft' : 'Publish');
    }

    if (isEdit.value) {
        payload.append('_method', 'PUT');
    }

    router.post(submitUrl.value, payload, {
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
};

watch(() => form.value.kategori_id, syncKategori);
</script>

<template>
    <Head :title="pageTitle" />

    <AppLayout :title="pageTitle">
        <div class="h-full min-h-0 flex flex-col bg-slate-50">
            <div class="shrink-0 bg-white border-b border-slate-200">
                <div class="px-4 sm:px-6 py-4 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 border border-amber-200 flex items-center justify-center shrink-0">
                            <i class="fa-solid fa-newspaper text-sm"></i>
                        </div>
                        <div>
                            <h1 class="text-lg font-black text-slate-900 leading-tight">{{ pageTitle }}</h1>
                            <p class="text-xs text-slate-500 mt-0.5">Konten Website SIPAROKI</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <Link
                            :href="listUrl"
                            class="px-3 py-2 rounded-xl bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 text-xs font-bold transition flex items-center gap-2"
                        >
                            <i class="fa-solid fa-arrow-left text-[11px]"></i>
                            <span>Kembali</span>
                        </Link>
                        <Link
                            v-if="previewUrl"
                            :href="previewUrl"
                            class="px-3 py-2 rounded-xl bg-white hover:bg-amber-50 hover:text-amber-700 border border-slate-200 text-slate-700 text-xs font-bold transition flex items-center gap-2"
                        >
                            <i class="fa-solid fa-eye text-[11px]"></i>
                            <span>Preview</span>
                        </Link>
                        <button
                            type="button"
                            @click="submitForm()"
                            :disabled="isSubmitting"
                            class="px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 disabled:opacity-60 text-white text-xs font-black shadow-sm shadow-amber-500/25 transition flex items-center gap-2"
                        >
                            <i :class="isSubmitting ? 'fa-solid fa-spinner fa-spin' : 'fa-solid fa-floppy-disk'" class="text-[11px]"></i>
                            <span>{{ isEdit ? 'Simpan Perubahan' : 'Simpan Konten' }}</span>
                        </button>
                    </div>
                </div>
            </div>

            <form @submit.prevent="submitForm" class="flex-1 min-h-0 overflow-y-auto custom-scrollbar">
                <div class="max-w-7xl mx-auto p-4 sm:p-6 grid grid-cols-1 xl:grid-cols-12 gap-5">
                    <section class="xl:col-span-8 space-y-5">
                        <div class="bg-white border border-slate-200 rounded-2xl p-4 sm:p-5 shadow-2xs space-y-4">
                            <div>
                                <label class="block text-xs font-black text-slate-700 mb-1.5">Judul Konten <span class="text-rose-500">*</span></label>
                                <input
                                    v-model="form.judul"
                                    type="text"
                                    required
                                    @blur="!form.slug && updateSlug()"
                                    placeholder="Judul berita atau artikel"
                                    class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-sm text-slate-900 focus:outline-none focus:border-amber-500 focus:bg-white"
                                />
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-black text-slate-700 mb-1.5">Slug URL</label>
                                    <div class="flex gap-2">
                                        <input
                                            v-model="form.slug"
                                            type="text"
                                            placeholder="slug-konten"
                                            class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 focus:bg-white"
                                        />
                                        <button
                                            type="button"
                                            @click="updateSlug"
                                            class="px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold border border-slate-200"
                                        >
                                            <i class="fa-solid fa-wand-magic-sparkles"></i>
                                        </button>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-black text-slate-700 mb-1.5">Penulis / Kontributor</label>
                                    <input
                                        v-model="form.penulis"
                                        list="konten-penulis-options"
                                        type="text"
                                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 focus:bg-white"
                                    />
                                    <datalist id="konten-penulis-options">
                                        <option v-for="penulis in penulisList" :key="penulis" :value="penulis" />
                                    </datalist>
                                </div>
                            </div>

                            <div>
                                <div class="flex items-center justify-between gap-3 mb-1.5">
                                    <label class="block text-xs font-black text-slate-700">Isi Konten Postingan <span class="text-rose-500">*</span></label>
                                    <div class="flex items-center gap-2">
                                        <button
                                            type="button"
                                            @click="generateExcerpt"
                                            class="px-2.5 py-1.5 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 text-[11px] font-black transition cursor-pointer"
                                        >
                                            <i class="fa-solid fa-wand-magic-sparkles mr-1"></i>
                                            Generate Excerpt
                                        </button>
                                    </div>
                                </div>

                                <RichTextEditor
                                    v-model="form.isi"
                                    :min-height="420"
                                    placeholder="Tuliskan isi berita atau artikel lengkap di sini..."
                                />
                            </div>
                        </div>

                        <div class="bg-white border border-slate-200 rounded-2xl p-4 sm:p-5 shadow-2xs space-y-3">
                            <label class="block text-xs font-black text-slate-700">Ringkasan / Excerpt</label>
                            <textarea
                                v-model="form.excerpt"
                                rows="4"
                                placeholder="Ringkasan singkat untuk tampilan daftar dan SEO"
                                class="w-full px-3.5 py-3 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-amber-500 focus:bg-white resize-y"
                            ></textarea>
                            <div class="flex items-center justify-between text-[11px] text-slate-500">
                                <span>Cuplikan tampil pada halaman daftar berita/artikel.</span>
                                <span class="font-black">{{ excerptCharCount }} karakter</span>
                            </div>
                        </div>
                    </section>

                    <aside class="xl:col-span-4 space-y-5">
                        <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-2xs space-y-4">
                            <h2 class="text-sm font-black text-slate-900">Publikasi</h2>

                            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-1 gap-3">
                                <div>
                                    <label class="block text-xs font-black text-slate-700 mb-1.5">Status</label>
                                    <select v-model="form.status_publish" class="w-full px-3 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs focus:outline-none focus:border-amber-500">
                                        <option value="Publish">Publish</option>
                                        <option value="Draft">Draft</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-xs font-black text-slate-700 mb-1.5">Tanggal Publish</label>
                                    <input v-model="form.tanggal_publish" type="datetime-local" class="w-full px-3 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs focus:outline-none focus:border-amber-500" />
                                </div>
                            </div>

                            <label class="flex items-center gap-2 text-xs font-bold text-slate-700">
                                <input v-model="form.is_featured" type="checkbox" true-value="1" false-value="0" class="rounded border-slate-300 text-amber-600 focus:ring-amber-500" />
                                Jadikan konten unggulan
                            </label>
                        </div>

                        <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-2xs space-y-4">
                            <h2 class="text-sm font-black text-slate-900">Kategori</h2>

                            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-1 gap-3">
                                <div>
                                    <label class="block text-xs font-black text-slate-700 mb-1.5">Tipe Konten</label>
                                    <select v-model="form.tipe" class="w-full px-3 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs focus:outline-none focus:border-amber-500">
                                        <option v-for="tipe in tipeOptions" :key="tipe" :value="tipe">{{ tipe }}</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-xs font-black text-slate-700 mb-1.5">Kategori Konten</label>
                                    <select v-model="form.kategori_id" class="w-full px-3 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs focus:outline-none focus:border-amber-500">
                                        <option value="">-- Tanpa Kategori --</option>
                                        <option v-for="kategori in kategoriKontenList" :key="kategori.id" :value="kategori.id">
                                            {{ kategori.nama_kategori }}
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-black text-slate-700 mb-1.5">Tags</label>
                                <input v-model="form.tags" type="text" placeholder="tag1, tag2, tag3" class="w-full px-3 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs focus:outline-none focus:border-amber-500" />
                                <div v-if="tagChips.length" class="flex flex-wrap gap-1.5 mt-2">
                                    <button
                                        v-for="tag in tagChips"
                                        :key="tag"
                                        type="button"
                                        @click="removeTag(tag)"
                                        class="px-2 py-1 rounded-full bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 text-[10px] font-bold"
                                    >
                                        {{ tag }}
                                        <i class="fa-solid fa-xmark ml-1 text-rose-500"></i>
                                    </button>
                                </div>
                                <div class="flex flex-wrap gap-1.5 mt-2">
                                    <button
                                        v-for="tag in popularTags"
                                        :key="tag"
                                        type="button"
                                        @click="addTag(tag)"
                                        class="px-2 py-1 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 text-[10px] font-bold"
                                    >
                                        {{ tag }}
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-2xs space-y-4">
                            <h2 class="text-sm font-black text-slate-900">Media</h2>

                            <div>
                                <label class="block text-xs font-black text-slate-700 mb-1.5">Gambar Sampul</label>
                                <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-3">
                                    <div class="aspect-video rounded-lg bg-white border border-slate-200 flex items-center justify-center overflow-hidden">
                                        <img v-if="imagePreview" :src="imagePreview" alt="Preview gambar sampul" class="w-full h-full object-cover" />
                                        <i v-else class="fa-solid fa-image text-3xl text-slate-300"></i>
                                    </div>
                                    <label class="mt-3 inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 text-xs font-black cursor-pointer">
                                        <i class="fa-solid fa-upload text-[11px]"></i>
                                        <span>Pilih Gambar</span>
                                        <input type="file" accept="image/*" class="hidden" @change="handleImageUpload" />
                                    </label>
                                    <p v-if="selectedImageName" class="mt-2 text-[11px] text-slate-500 truncate">{{ selectedImageName }}</p>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-black text-slate-700 mb-1.5">Dokumen Lampiran PDF</label>
                                <div v-if="selectedPdfName" class="mb-2 flex items-center gap-2 rounded-xl bg-rose-50 border border-rose-100 p-2">
                                    <i class="fa-solid fa-file-pdf text-rose-500"></i>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-xs font-black text-slate-800 truncate">Dokumen Terlampir</p>
                                        <p class="text-[11px] text-slate-500 truncate">{{ selectedPdfName }}</p>
                                    </div>
                                    <button type="button" @click="form.file_pdf = ''; form.arsip_id = ''; selectedPdfName = ''" class="text-rose-600 hover:text-rose-800">
                                        <i class="fa-solid fa-xmark"></i>
                                    </button>
                                </div>
                                <label class="w-full flex items-center justify-between gap-2 px-3 py-2.5 rounded-xl bg-slate-50 hover:bg-slate-100 border border-slate-200 text-xs cursor-pointer">
                                    <span class="truncate text-slate-600">{{ selectedPdfName || 'Pilih file PDF' }}</span>
                                    <i class="fa-solid fa-file-pdf text-rose-500"></i>
                                    <input type="file" accept="application/pdf,.pdf" class="hidden" @change="handlePdfUpload" />
                                </label>
                            </div>

                            <div>
                                <label class="block text-xs font-black text-slate-700 mb-1.5">ID Arsip Digital</label>
                                <input v-model="form.arsip_id" type="text" placeholder="Opsional" class="w-full px-3 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs focus:outline-none focus:border-amber-500" />
                            </div>

                            <label class="flex items-center gap-2 text-xs font-bold text-slate-700">
                                <input v-model="form.embed_pdf" type="checkbox" true-value="1" false-value="0" class="rounded border-slate-300 text-amber-600 focus:ring-amber-500" />
                                Tampilkan PDF tertanam
                            </label>
                        </div>

                        <div class="flex items-center justify-end gap-2 sticky bottom-0 bg-slate-50/95 py-3">
                            <Link :href="listUrl" class="px-4 py-2.5 rounded-xl bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 text-xs font-black">
                                Batal
                            </Link>
                            <button
                                type="button"
                                @click="submitForm('draft')"
                                :disabled="isSubmitting"
                                class="px-4 py-2.5 rounded-xl bg-white hover:bg-slate-50 disabled:opacity-60 text-slate-700 border border-slate-200 text-xs font-black transition inline-flex items-center gap-2"
                            >
                                <i :class="isSubmitting ? 'fa-solid fa-spinner fa-spin' : 'fa-solid fa-save'" class="text-[11px]"></i>
                                <span>Simpan Draft</span>
                            </button>
                            <button
                                type="button"
                                @click="submitForm('publish')"
                                :disabled="isSubmitting"
                                class="px-5 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 disabled:opacity-60 text-white text-xs font-black shadow-sm shadow-amber-500/25 transition inline-flex items-center gap-2"
                            >
                                <i :class="isSubmitting ? 'fa-solid fa-spinner fa-spin' : 'fa-solid fa-floppy-disk'" class="text-[11px]"></i>
                                <span>Publikasikan Sekarang</span>
                            </button>
                        </div>
                    </aside>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
