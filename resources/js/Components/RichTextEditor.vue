<template>
    <div
        :class="[
            'border rounded-2xl overflow-hidden transition bg-white flex flex-col',
            isFocused ? 'border-amber-500 ring-2 ring-amber-500/20 shadow-md' : 'border-slate-200 shadow-2xs'
        ]"
    >
        <!-- TOOLBAR -->
        <div class="bg-slate-50 border-b border-slate-200 px-3 py-2 flex flex-wrap items-center gap-1 select-none">
            <!-- HISTORY -->
            <div class="flex items-center bg-white border border-slate-200 rounded-lg p-0.5 shadow-2xs mr-1">
                <button
                    type="button"
                    title="Undo (Ctrl+Z)"
                    @click="exec('undo')"
                    class="w-7 h-7 flex items-center justify-center rounded text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition cursor-pointer"
                >
                    <i class="fa-solid fa-rotate-left text-xs"></i>
                </button>
                <button
                    type="button"
                    title="Redo (Ctrl+Y)"
                    @click="exec('redo')"
                    class="w-7 h-7 flex items-center justify-center rounded text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition cursor-pointer"
                >
                    <i class="fa-solid fa-rotate-right text-xs"></i>
                </button>
            </div>

            <!-- FORMAT PARAGRAPH / HEADINGS -->
            <div class="flex items-center bg-white border border-slate-200 rounded-lg px-2 py-0.5 shadow-2xs mr-1">
                <select
                    @change="formatBlock($event.target.value)"
                    :value="currentBlock"
                    class="text-xs font-semibold text-slate-700 bg-transparent focus:outline-none cursor-pointer py-1"
                >
                    <option value="p">Normal (Paragraf)</option>
                    <option value="h2">Judul Besar (H2)</option>
                    <option value="h3">Sub Judul (H3)</option>
                    <option value="h4">Judul Kecil (H4)</option>
                    <option value="blockquote">Kutipan / Ayat</option>
                    <option value="pre">Kode / Preformat</option>
                </select>
            </div>

            <!-- TEXT FORMATTING -->
            <div class="flex items-center bg-white border border-slate-200 rounded-lg p-0.5 shadow-2xs mr-1">
                <button
                    type="button"
                    title="Tebal / Bold (Ctrl+B)"
                    @click="exec('bold')"
                    :class="['w-7 h-7 flex items-center justify-center rounded transition cursor-pointer', isActive('bold') ? 'bg-amber-500 text-white font-bold' : 'text-slate-700 hover:bg-slate-100']"
                >
                    <i class="fa-solid fa-bold text-xs"></i>
                </button>
                <button
                    type="button"
                    title="Miring / Italic (Ctrl+I)"
                    @click="exec('italic')"
                    :class="['w-7 h-7 flex items-center justify-center rounded transition cursor-pointer', isActive('italic') ? 'bg-amber-500 text-white font-bold' : 'text-slate-700 hover:bg-slate-100']"
                >
                    <i class="fa-solid fa-italic text-xs"></i>
                </button>
                <button
                    type="button"
                    title="Garis Bawah / Underline (Ctrl+U)"
                    @click="exec('underline')"
                    :class="['w-7 h-7 flex items-center justify-center rounded transition cursor-pointer', isActive('underline') ? 'bg-amber-500 text-white font-bold' : 'text-slate-700 hover:bg-slate-100']"
                >
                    <i class="fa-solid fa-underline text-xs"></i>
                </button>
                <button
                    type="button"
                    title="Coret / Strikethrough"
                    @click="exec('strikeThrough')"
                    :class="['w-7 h-7 flex items-center justify-center rounded transition cursor-pointer', isActive('strikeThrough') ? 'bg-amber-500 text-white font-bold' : 'text-slate-700 hover:bg-slate-100']"
                >
                    <i class="fa-solid fa-strikethrough text-xs"></i>
                </button>
            </div>

            <!-- ALIGNMENT -->
            <div class="flex items-center bg-white border border-slate-200 rounded-lg p-0.5 shadow-2xs mr-1">
                <button
                    type="button"
                    title="Rata Kiri"
                    @click="exec('justifyLeft')"
                    :class="['w-7 h-7 flex items-center justify-center rounded transition cursor-pointer', isActive('justifyLeft') ? 'bg-amber-500 text-white' : 'text-slate-700 hover:bg-slate-100']"
                >
                    <i class="fa-solid fa-align-left text-xs"></i>
                </button>
                <button
                    type="button"
                    title="Rata Tengah"
                    @click="exec('justifyCenter')"
                    :class="['w-7 h-7 flex items-center justify-center rounded transition cursor-pointer', isActive('justifyCenter') ? 'bg-amber-500 text-white' : 'text-slate-700 hover:bg-slate-100']"
                >
                    <i class="fa-solid fa-align-center text-xs"></i>
                </button>
                <button
                    type="button"
                    title="Rata Kanan"
                    @click="exec('justifyRight')"
                    :class="['w-7 h-7 flex items-center justify-center rounded transition cursor-pointer', isActive('justifyRight') ? 'bg-amber-500 text-white' : 'text-slate-700 hover:bg-slate-100']"
                >
                    <i class="fa-solid fa-align-right text-xs"></i>
                </button>
                <button
                    type="button"
                    title="Rata Kanan Kiri / Justify"
                    @click="exec('justifyFull')"
                    :class="['w-7 h-7 flex items-center justify-center rounded transition cursor-pointer', isActive('justifyFull') ? 'bg-amber-500 text-white' : 'text-slate-700 hover:bg-slate-100']"
                >
                    <i class="fa-solid fa-align-justify text-xs"></i>
                </button>
            </div>

            <!-- LISTS -->
            <div class="flex items-center bg-white border border-slate-200 rounded-lg p-0.5 shadow-2xs mr-1">
                <button
                    type="button"
                    title="Bullet List"
                    @click="exec('insertUnorderedList')"
                    :class="['w-7 h-7 flex items-center justify-center rounded transition cursor-pointer', isActive('insertUnorderedList') ? 'bg-amber-500 text-white' : 'text-slate-700 hover:bg-slate-100']"
                >
                    <i class="fa-solid fa-list-ul text-xs"></i>
                </button>
                <button
                    type="button"
                    title="Numbered List"
                    @click="exec('insertOrderedList')"
                    :class="['w-7 h-7 flex items-center justify-center rounded transition cursor-pointer', isActive('insertOrderedList') ? 'bg-amber-500 text-white' : 'text-slate-700 hover:bg-slate-100']"
                >
                    <i class="fa-solid fa-list-ol text-xs"></i>
                </button>
            </div>

            <!-- INSERT & TOOLS -->
            <div class="flex items-center bg-white border border-slate-200 rounded-lg p-0.5 shadow-2xs mr-1">
                <button
                    type="button"
                    title="Sisipkan Tautan / Link"
                    @click="insertLink"
                    class="w-7 h-7 flex items-center justify-center rounded text-slate-700 hover:bg-slate-100 transition cursor-pointer"
                >
                    <i class="fa-solid fa-link text-xs"></i>
                </button>
                <button
                    type="button"
                    title="Sisipkan Gambar dari Komputer / File"
                    @click="triggerImageUpload"
                    class="w-7 h-7 flex items-center justify-center rounded text-blue-600 hover:bg-blue-50 transition cursor-pointer"
                >
                    <i class="fa-solid fa-image text-xs"></i>
                </button>
                <input
                    ref="imageInputRef"
                    type="file"
                    accept="image/*"
                    class="hidden"
                    @change="handleImageFile"
                />
                <button
                    type="button"
                    title="Sisipkan File Dokumen PDF"
                    @click="triggerPdfUpload"
                    class="w-7 h-7 flex items-center justify-center rounded text-rose-600 hover:bg-rose-50 transition cursor-pointer"
                >
                    <i class="fa-solid fa-file-pdf text-xs"></i>
                </button>
                <input
                    ref="pdfInputRef"
                    type="file"
                    accept="application/pdf"
                    class="hidden"
                    @change="handlePdfFile"
                />
                <button
                    type="button"
                    title="Garis Pembatas (HR)"
                    @click="exec('insertHorizontalRule')"
                    class="w-7 h-7 flex items-center justify-center rounded text-slate-700 hover:bg-slate-100 transition cursor-pointer"
                >
                    <i class="fa-solid fa-minus text-xs"></i>
                </button>
                <button
                    type="button"
                    title="Hapus Format / Clear Formatting"
                    @click="exec('removeFormat')"
                    class="w-7 h-7 flex items-center justify-center rounded text-rose-600 hover:bg-rose-50 transition cursor-pointer"
                >
                    <i class="fa-solid fa-eraser text-xs"></i>
                </button>
            </div>

            <!-- TOGGLE SOURCE CODE -->
            <div class="ml-auto flex items-center">
                <button
                    type="button"
                    @click="toggleSourceCode"
                    :class="[
                        'px-2.5 py-1 rounded-lg text-[11px] font-bold transition flex items-center gap-1.5 cursor-pointer border',
                        showSource ? 'bg-slate-900 text-amber-400 border-slate-900' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-100'
                    ]"
                    title="Lihat Kode HTML"
                >
                    <i class="fa-solid fa-code text-xs"></i>
                    <span>{{ showSource ? 'Visual Editor' : 'HTML Code' }}</span>
                </button>
            </div>
        </div>

        <!-- EDITOR BODY -->
        <div class="relative min-h-[160px] flex-1 bg-white">
            <!-- ContentEditable Visual Area -->
            <div
                v-show="!showSource"
                ref="editorRef"
                contenteditable="true"
                :placeholder="placeholder"
                @input="handleInput"
                @focus="isFocused = true"
                @blur="handleBlur"
                @keyup="updateActiveStates"
                @mouseup="updateActiveStates"
                :style="{ minHeight: `${minHeight}px` }"
                class="p-4 text-xs sm:text-sm text-slate-800 focus:outline-none prose prose-sm max-w-none prose-p:my-1 prose-headings:my-2 prose-ul:my-1 prose-ol:my-1 prose-blockquote:border-amber-500 prose-blockquote:bg-amber-50/50 prose-blockquote:p-2 prose-blockquote:rounded-r-lg"
            ></div>

            <!-- HTML Source Code Mode -->
            <textarea
                v-show="showSource"
                :value="modelValue"
                @input="handleSourceInput"
                @focus="isFocused = true"
                @blur="isFocused = false"
                :style="{ minHeight: `${minHeight}px` }"
                placeholder="<!-- Tulis atau edit kode HTML di sini -->"
                class="w-full p-4 font-mono text-xs text-slate-800 bg-slate-950/5 focus:outline-none resize-y border-none"
            ></textarea>
        </div>

        <!-- FOOTER INFO -->
        <div class="bg-slate-50 border-t border-slate-100 px-3 py-1.5 flex items-center justify-between text-[10.5px] text-slate-400 select-none">
            <span class="flex items-center gap-1.5">
                <i class="fa-solid fa-pen-nib text-amber-500 text-[10px]"></i>
                <span class="font-medium text-slate-500">WYSIWYG Rich Editor</span>
            </span>
            <div class="flex items-center gap-3">
                <span><strong>{{ wordCount }}</strong> kata</span>
                <span><strong>{{ charCount }}</strong> karakter</span>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, watch, onMounted, computed, nextTick } from 'vue';

const props = defineProps({
    modelValue: {
        type: String,
        default: '',
    },
    placeholder: {
        type: String,
        default: 'Tuliskan isi atau konten lengkap di sini...',
    },
    minHeight: {
        type: [Number, String],
        default: 200,
    },
});

const emit = defineEmits(['update:modelValue', 'change', 'blur']);

const editorRef = ref(null);
const isFocused = ref(false);
const showSource = ref(false);
const activeStates = ref({});
const currentBlock = ref('p');

const charCount = computed(() => {
    const text = (props.modelValue || '').replace(/<[^>]*>/g, '').trim();
    return text.length;
});

const wordCount = computed(() => {
    const text = (props.modelValue || '').replace(/<[^>]*>/g, '').trim();
    return text ? text.split(/\s+/).filter(Boolean).length : 0;
});

const syncContent = (html) => {
    if (editorRef.value && editorRef.value.innerHTML !== html) {
        editorRef.value.innerHTML = html || '';
    }
};

onMounted(() => {
    syncContent(props.modelValue);
});

watch(() => props.modelValue, (newVal) => {
    if (editorRef.value && !isFocused.value) {
        syncContent(newVal);
    }
});

const handleInput = () => {
    if (!editorRef.value) return;
    const html = editorRef.value.innerHTML;
    emit('update:modelValue', html);
    emit('change', html);
    updateActiveStates();
};

const handleSourceInput = (e) => {
    const val = e.target.value;
    emit('update:modelValue', val);
    emit('change', val);
    if (editorRef.value) {
        editorRef.value.innerHTML = val;
    }
};

const handleBlur = () => {
    isFocused.value = false;
    emit('blur');
};

const exec = (command, value = null) => {
    if (showSource.value) return;
    if (editorRef.value) {
        editorRef.value.focus();
    }
    document.execCommand(command, false, value);
    handleInput();
};

const formatBlock = (tag) => {
    if (showSource.value) return;
    currentBlock.value = tag;
    exec('formatBlock', `<${tag}>`);
};

const isActive = (command) => {
    return !!activeStates.value[command];
};

const updateActiveStates = () => {
    if (showSource.value) return;
    try {
        activeStates.value = {
            bold: document.queryCommandState('bold'),
            italic: document.queryCommandState('italic'),
            underline: document.queryCommandState('underline'),
            strikeThrough: document.queryCommandState('strikeThrough'),
            justifyLeft: document.queryCommandState('justifyLeft'),
            justifyCenter: document.queryCommandState('justifyCenter'),
            justifyRight: document.queryCommandState('justifyRight'),
            justifyFull: document.queryCommandState('justifyFull'),
            insertUnorderedList: document.queryCommandState('insertUnorderedList'),
            insertOrderedList: document.queryCommandState('insertOrderedList'),
        };
    } catch {
        // Silently ignore state check exceptions
    }
};

const imageInputRef = ref(null);
const pdfInputRef = ref(null);

const triggerImageUpload = () => {
    imageInputRef.value?.click();
};

const handleImageFile = (e) => {
    const file = e.target.files?.[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = (event) => {
        const base64 = event.target.result;
        exec('insertHTML', `<p><img src="${base64}" alt="${file.name}" style="max-width:100%;height:auto;border-radius:10px;margin:10px 0;display:block;" /></p>`);
    };
    reader.readAsDataURL(file);
    e.target.value = '';
};

const triggerPdfUpload = () => {
    pdfInputRef.value?.click();
};

const handlePdfFile = (e) => {
    const file = e.target.files?.[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = (event) => {
        const base64 = event.target.result;
        exec('insertHTML', `<div style="padding:12px;margin:10px 0;background:#fff1f2;border:1px solid #fecdd3;border-radius:12px;display:flex;align-items:center;justify-content:space-between;"><div style="display:flex;align-items:center;gap:10px;"><i class="fa-solid fa-file-pdf" style="color:#e11d48;font-size:20px;"></i><div><strong style="font-size:13px;color:#1e293b;display:block;">${file.name}</strong><span style="font-size:11px;color:#64748b;">Dokumen PDF (${(file.size / 1024).toFixed(1)} KB)</span></div></div><a href="${base64}" download="${file.name}" style="padding:6px 14px;background:#e11d48;color:#fff;border-radius:8px;font-size:12px;font-weight:bold;text-decoration:none;">Unduh PDF</a></div>`);
    };
    reader.readAsDataURL(file);
    e.target.value = '';
};

const insertLink = () => {
    const url = prompt('Masukkan URL / Tautan Web:', 'https://');
    if (url && url !== 'https://') {
        exec('createLink', url);
    }
};

const toggleSourceCode = () => {
    showSource.value = !showSource.value;
    if (!showSource.value) {
        nextTick(() => {
            syncContent(props.modelValue);
        });
    }
};
</script>

<style scoped>
[contenteditable]:empty:before {
    content: attr(placeholder);
    color: #94a3b8;
    pointer-events: none;
    display: block;
}
</style>
