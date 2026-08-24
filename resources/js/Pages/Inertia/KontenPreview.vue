<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    title: {
        type: String,
        default: 'Preview Konten Website',
    },
    role: {
        type: String,
        default: 'Super Admin',
    },
    prefix: {
        type: String,
        default: 'superadmin',
    },
    item: {
        type: Object,
        required: true,
    },
    category: {
        type: Object,
        default: null,
    },
});

const basePrefix = computed(() => `/${props.prefix || 'superadmin'}`);
const itemKey = computed(() => props.item?.id || props.item?.slug || '');
const listUrl = computed(() => `${basePrefix.value}/konten`);
const editUrl = computed(() => `${basePrefix.value}/konten/${itemKey.value}/edit`);
const publicUrl = computed(() => props.item?.slug ? `/artikel/${props.item.slug}` : '');

const assetUrl = (path) => {
    if (!path || typeof path !== 'string') return '';
    if (path.startsWith('http://') || path.startsWith('https://')) return path;
    const clean = path.replace(/^\/?(public\/)?/, '').replace(/^\//, '');
    return clean ? `/${clean}` : '';
};

const imageUrl = computed(() => assetUrl(props.item?.gambar));
const categoryName = computed(() => props.category?.nama_kategori || props.item?.kategori || '-');
const articleHtml = computed(() => props.item?.isi || props.item?.konten || '<p>Belum ada isi konten.</p>');
const tags = computed(() => String(props.item?.tags || '')
    .split(',')
    .map((tag) => tag.trim())
    .filter(Boolean));

const isPublished = computed(() => String(props.item?.status_publish || props.item?.status || '')
    .toLowerCase()
    .includes('publish'));

const pdfUrl = computed(() => {
    const file = props.item?.file_pdf;
    if (!file || typeof file !== 'string') return '';
    if (file.startsWith('http://') || file.startsWith('https://')) return file;
    if (file.includes('/')) return assetUrl(file);
    return props.item?.arsip_id ? `/assets/uploads/arsip/${file}` : `/uploads/konten/${file}`;
});

const formatDate = (value) => {
    if (!value) return '-';
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return String(value);
    return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    }).format(date);
};
</script>

<template>
    <Head :title="`${props.title} - SIPAROKI`" />

    <AppLayout :title="props.title">
        <div class="h-full min-h-0 flex flex-col bg-slate-50">
            <div class="shrink-0 bg-white border-b border-slate-200">
                <div class="px-4 sm:px-6 py-4 flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
                    <div class="flex items-start gap-3 min-w-0">
                        <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 border border-amber-200 flex items-center justify-center shrink-0">
                            <i class="fa-solid fa-eye text-sm"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-[11px] font-black uppercase tracking-wide text-amber-600">Preview Konten Website</p>
                            <h1 class="text-lg font-black text-slate-900 leading-tight truncate">{{ item.judul || 'Tanpa Judul' }}</h1>
                            <p class="text-xs text-slate-500 mt-0.5">{{ props.role }} / Konten Website</p>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <Link
                            :href="listUrl"
                            class="px-3 py-2 rounded-xl bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 text-xs font-bold transition flex items-center gap-2"
                        >
                            <i class="fa-solid fa-arrow-left text-[11px]"></i>
                            <span>Kembali</span>
                        </Link>
                        <a
                            v-if="publicUrl"
                            :href="publicUrl"
                            target="_blank"
                            rel="noopener"
                            class="px-3 py-2 rounded-xl bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 text-xs font-bold transition flex items-center gap-2"
                        >
                            <i class="fa-solid fa-up-right-from-square text-[11px]"></i>
                            <span>Buka Publik</span>
                        </a>
                        <Link
                            :href="editUrl"
                            class="px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-xs font-black shadow-sm shadow-amber-500/25 transition flex items-center gap-2"
                        >
                            <i class="fa-solid fa-pen-to-square text-[11px]"></i>
                            <span>Edit Konten</span>
                        </Link>
                    </div>
                </div>
            </div>

            <main class="flex-1 min-h-0 overflow-y-auto custom-scrollbar">
                <div class="max-w-6xl mx-auto p-4 sm:p-6 grid grid-cols-1 xl:grid-cols-12 gap-5">
                    <article class="xl:col-span-8 bg-white border border-slate-200 rounded-2xl shadow-2xs overflow-hidden">
                        <div v-if="imageUrl" class="bg-slate-100 border-b border-slate-200">
                            <img :src="imageUrl" :alt="item.judul || 'Gambar konten'" class="w-full aspect-[16/8] object-cover" />
                        </div>
                        <div class="p-5 sm:p-7">
                            <div class="flex flex-wrap items-center gap-2 mb-4">
                                <span
                                    :class="[
                                        'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-black border',
                                        isPublished ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-amber-50 text-amber-700 border-amber-200'
                                    ]"
                                >
                                    <span :class="['w-1.5 h-1.5 rounded-full', isPublished ? 'bg-emerald-500' : 'bg-amber-500']"></span>
                                    {{ isPublished ? 'Publish' : 'Draft' }}
                                </span>
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                    {{ item.tipe || 'Berita' }}
                                </span>
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                    {{ categoryName }}
                                </span>
                            </div>

                            <h2 class="text-2xl sm:text-3xl font-black text-slate-950 leading-tight">{{ item.judul || 'Tanpa Judul' }}</h2>
                            <div class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs font-semibold text-slate-500">
                                <span class="inline-flex items-center gap-1.5">
                                    <i class="fa-regular fa-user"></i>
                                    {{ item.penulis || 'Administrator' }}
                                </span>
                                <span class="inline-flex items-center gap-1.5">
                                    <i class="fa-regular fa-calendar"></i>
                                    {{ formatDate(item.tanggal_publish || item.created_at) }}
                                </span>
                                <span v-if="item.views !== undefined && item.views !== null" class="inline-flex items-center gap-1.5">
                                    <i class="fa-regular fa-eye"></i>
                                    {{ item.views }} dilihat
                                </span>
                            </div>

                            <p v-if="item.excerpt" class="mt-5 text-sm leading-7 text-slate-600 bg-slate-50 border-l-4 border-amber-400 px-4 py-3 rounded-r-xl">
                                {{ item.excerpt }}
                            </p>

                            <div class="konten-preview-body mt-7 text-slate-700" v-html="articleHtml"></div>
                        </div>
                    </article>

                    <aside class="xl:col-span-4 space-y-5">
                        <section class="bg-white border border-slate-200 rounded-2xl p-5 shadow-2xs">
                            <h3 class="text-sm font-black text-slate-900 mb-4">Informasi Konten</h3>
                            <dl class="space-y-3 text-xs">
                                <div class="flex justify-between gap-4">
                                    <dt class="font-bold text-slate-500">Slug</dt>
                                    <dd class="font-semibold text-slate-800 text-right break-all">{{ item.slug || '-' }}</dd>
                                </div>
                                <div class="flex justify-between gap-4">
                                    <dt class="font-bold text-slate-500">Kategori</dt>
                                    <dd class="font-semibold text-slate-800 text-right">{{ categoryName }}</dd>
                                </div>
                                <div class="flex justify-between gap-4">
                                    <dt class="font-bold text-slate-500">Featured</dt>
                                    <dd class="font-semibold text-slate-800 text-right">{{ item.is_featured ? 'Ya' : 'Tidak' }}</dd>
                                </div>
                                <div class="flex justify-between gap-4">
                                    <dt class="font-bold text-slate-500">Dibuat</dt>
                                    <dd class="font-semibold text-slate-800 text-right">{{ formatDate(item.created_at) }}</dd>
                                </div>
                            </dl>
                        </section>

                        <section v-if="tags.length" class="bg-white border border-slate-200 rounded-2xl p-5 shadow-2xs">
                            <h3 class="text-sm font-black text-slate-900 mb-3">Tag</h3>
                            <div class="flex flex-wrap gap-2">
                                <span
                                    v-for="tag in tags"
                                    :key="tag"
                                    class="px-2.5 py-1 rounded-full bg-slate-100 text-slate-700 text-[11px] font-bold border border-slate-200"
                                >
                                    {{ tag }}
                                </span>
                            </div>
                        </section>

                        <section v-if="pdfUrl" class="bg-white border border-slate-200 rounded-2xl p-5 shadow-2xs">
                            <div class="flex items-start justify-between gap-3 mb-4">
                                <div>
                                    <h3 class="text-sm font-black text-slate-900">Lampiran PDF</h3>
                                    <p class="text-xs text-slate-500 mt-0.5 break-all">{{ item.file_pdf }}</p>
                                </div>
                                <a
                                    :href="pdfUrl"
                                    target="_blank"
                                    rel="noopener"
                                    class="w-9 h-9 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 flex items-center justify-center transition"
                                    title="Buka PDF"
                                >
                                    <i class="fa-solid fa-file-pdf text-sm"></i>
                                </a>
                            </div>
                            <iframe
                                v-if="item.embed_pdf !== 0 && item.embed_pdf !== false"
                                :src="pdfUrl"
                                class="w-full h-96 rounded-xl border border-slate-200 bg-slate-50"
                            ></iframe>
                        </section>
                    </aside>
                </div>
            </main>
        </div>
    </AppLayout>
</template>

<style scoped>
.konten-preview-body :deep(*) {
    max-width: 100%;
}

.konten-preview-body :deep(p) {
    margin: 0 0 1rem;
    line-height: 1.85;
}

.konten-preview-body :deep(h1),
.konten-preview-body :deep(h2),
.konten-preview-body :deep(h3) {
    margin: 1.5rem 0 0.75rem;
    font-weight: 900;
    color: #0f172a;
    line-height: 1.25;
}

.konten-preview-body :deep(h1) {
    font-size: 1.75rem;
}

.konten-preview-body :deep(h2) {
    font-size: 1.45rem;
}

.konten-preview-body :deep(h3) {
    font-size: 1.2rem;
}

.konten-preview-body :deep(a) {
    color: #b45309;
    font-weight: 700;
    text-decoration: underline;
}

.konten-preview-body :deep(img) {
    border-radius: 0.75rem;
    border: 1px solid #e2e8f0;
    margin: 1rem 0;
    height: auto;
}

.konten-preview-body :deep(iframe) {
    width: 100%;
    min-height: 32rem;
    border: 1px solid #e2e8f0;
    border-radius: 0.75rem;
    background: #f8fafc;
}

.konten-preview-body :deep(ul),
.konten-preview-body :deep(ol) {
    margin: 0 0 1rem 1.25rem;
    line-height: 1.8;
}

.konten-preview-body :deep(blockquote) {
    margin: 1rem 0;
    padding: 0.75rem 1rem;
    border-left: 4px solid #f59e0b;
    background: #f8fafc;
    border-radius: 0 0.75rem 0.75rem 0;
}
</style>
