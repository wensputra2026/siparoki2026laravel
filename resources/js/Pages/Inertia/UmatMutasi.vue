<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import SearchableSelect from '@/Components/SearchableSelect.vue';

const props = defineProps({
    mode: { type: String, default: 'mutasi' },
    prefix: { type: String, default: 'superadmin' },
    umatItem: { type: Object, required: true },
    kubAsal: { type: Object, default: null },
    kkAsal: { type: Object, default: null },
    kubList: { type: Array, default: () => [] },
    kkList: { type: Array, default: () => [] },
    wilayahList: { type: Array, default: () => [] },
    namaParoki: { type: String, default: '' },
});

const basePrefix = computed(() => `/${props.prefix}`);
const isSubmitting = ref(false);

const form = ref({
    kub_tujuan_id: '',
    kk_tujuan_id: '',
    alasan: '',
    no_surat_pindah: '',
    tgl_mutasi: new Date().toISOString().split('T')[0],
    no_kk_kw: '',
    nama_pemilik_kk: props.umatItem?.nama_lengkap || '',
    tgl_perkawinan: '',
    nama_pasangan: '',
});

const kubOptions = computed(() => (props.kubList || []).map(k => ({
    id: k.id,
    name: `${k.nama_kub} (${k.kode_kub || '-'})`,
})));

const kkOptions = computed(() => (props.kkList || [])
    .filter(k => !form.value.kub_tujuan_id || String(k.kub_id) === String(form.value.kub_tujuan_id))
    .map(k => ({
        id: k.id,
        name: `${k.no_kk_kw || '-'} • ${k.nama_lahir_pemilik || ''} ${k.nama_baptis_pemilik ? '(' + k.nama_baptis_pemilik + ')' : ''}`,
    })));

const actionUrl = computed(() => props.mode === 'pisah'
    ? `${basePrefix.value}/umat/${props.umatItem.id}/pisah-kk`
    : `${basePrefix.value}/umat/${props.umatItem.id}/mutasi`);

const backUrl = computed(() => `${basePrefix.value}/umat`);

const submit = () => {
    isSubmitting.value = true;
    router.post(actionUrl.value, form.value, {
        preserveScroll: true,
        onFinish: () => (isSubmitting.value = false),
    });
};
</script>

<template>
  <AppLayout :title="mode === 'pisah' ? 'Pisah KK (Menikah)' : 'Mutasi Umat antar KUB'">
    <Head :title="mode === 'pisah' ? `Pisah KK: ${umatItem.nama_lengkap} - SIPAROKI` : `Mutasi: ${umatItem.nama_lengkap} - SIPAROKI`" />

    <div class="max-w-3xl mx-auto py-4 sm:py-6 px-4 space-y-5">
      <!-- Breadcrumb & Back -->
      <div class="flex items-center justify-between gap-4">
        <Link
          :href="backUrl"
          class="inline-flex items-center gap-2 text-xs font-bold text-slate-600 hover:text-slate-900 bg-white border border-slate-200 px-3.5 py-2 rounded-xl shadow-2xs transition cursor-pointer"
        >
          <i class="fa-solid fa-arrow-left text-xs"></i>
          <span>Kembali ke Data Umat</span>
        </Link>
        <div class="text-xs text-slate-500 font-medium">
          {{ namaParoki || 'Paroki St. Vinsensius a Paulo Benlutu' }}
        </div>
      </div>

      <!-- Main Card -->
      <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5 sm:p-7 space-y-6">
        <!-- Header Section -->
        <div class="border-b border-slate-100 pb-4">
          <div class="flex items-center gap-3">
            <div
              :class="mode === 'pisah' ? 'bg-sky-50 text-sky-600 border-sky-200' : 'bg-emerald-50 text-emerald-600 border-emerald-200'"
              class="w-12 h-12 rounded-2xl border flex items-center justify-center text-xl shrink-0"
            >
              <i :class="mode === 'pisah' ? 'fa-solid fa-ring' : 'fa-solid fa-arrows-rotate'"></i>
            </div>
            <div>
              <h1 class="text-base sm:text-lg font-black text-slate-900">
                {{ mode === 'pisah' ? 'Form Pisah KK (Menikah / Bentuk Keluarga Baru)' : 'Form Mutasi Umat antar KUB' }}
              </h1>
              <p class="text-xs text-slate-500 mt-0.5">
                Proses pencatatan administrasi pastoral dan pembaharuan riwayat kartu keluarga.
              </p>
            </div>
          </div>
        </div>

        <!-- Info Umat Saat Ini -->
        <div class="bg-slate-50 rounded-xl p-4 border border-slate-200/70 grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
          <div>
            <span class="text-slate-400 block text-[11px] font-medium">Nama Umat:</span>
            <span class="font-bold text-slate-900 text-sm">{{ umatItem.nama_lengkap }}</span>
          </div>
          <div>
            <span class="text-slate-400 block text-[11px] font-medium">No. KK Asal:</span>
            <span class="font-mono font-bold text-slate-800">{{ kkAsal?.no_kk_kw || '—' }}</span>
          </div>
          <div>
            <span class="text-slate-400 block text-[11px] font-medium">KUB Asal:</span>
            <span class="font-semibold text-emerald-700">{{ kubAsal?.nama_kub || '—' }}</span>
          </div>
        </div>

        <!-- Form -->
        <form @submit.prevent="submit" class="space-y-4">
          <template v-if="mode === 'mutasi'">
            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1.5">
                KUB Tujuan <span class="text-rose-500">*</span>
              </label>
              <SearchableSelect
                v-model="form.kub_tujuan_id"
                :options="kubOptions"
                placeholder="-- Pilih KUB tujuan --"
                searchPlaceholder="Cari KUB..."
                icon="fa-solid fa-people-group"
                iconColor="text-emerald-600"
              />
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1.5">
                KK Tujuan (Kepala Keluarga Baru dalam KUB) <span class="text-rose-500">*</span>
              </label>
              <SearchableSelect
                v-model="form.kk_tujuan_id"
                :options="kkOptions"
                placeholder="-- Pilih KK tujuan --"
                searchPlaceholder="Cari KK / Kepala Keluarga..."
                icon="fa-solid fa-house-user"
                iconColor="text-blue-600"
              />
            </div>
          </template>

          <template v-else>
            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1.5">
                KUB Tujuan (Keluarga Baru) <span class="text-rose-500">*</span>
              </label>
              <SearchableSelect
                v-model="form.kub_tujuan_id"
                :options="kubOptions"
                placeholder="-- Pilih KUB keluarga baru --"
                searchPlaceholder="Cari KUB..."
                icon="fa-solid fa-people-group"
                iconColor="text-sky-600"
              />
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">
                  No. KK Baru <span class="text-rose-500">*</span>
                </label>
                <input
                  v-model="form.no_kk_kw"
                  placeholder="Contoh: K012014..."
                  class="w-full border border-slate-200 rounded-xl px-3.5 py-2 text-xs font-mono font-bold text-slate-900 focus:ring-2 focus:ring-sky-500 focus:outline-none"
                  required
                />
              </div>
              <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">
                  Nama Kepala Keluarga Baru <span class="text-rose-500">*</span>
                </label>
                <input
                  v-model="form.nama_pemilik_kk"
                  placeholder="Nama kepala keluarga..."
                  class="w-full border border-slate-200 rounded-xl px-3.5 py-2 text-xs text-slate-900 focus:ring-2 focus:ring-sky-500 focus:outline-none font-semibold"
                  required
                />
              </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Tanggal Perkawinan</label>
                <input
                  type="date"
                  v-model="form.tgl_perkawinan"
                  class="w-full border border-slate-200 rounded-xl px-3.5 py-2 text-xs text-slate-900 focus:ring-2 focus:ring-sky-500 focus:outline-none"
                />
              </div>
              <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Nama Pasangan (Istri / Suami)</label>
                <input
                  v-model="form.nama_pasangan"
                  placeholder="Nama lengkap pasangan..."
                  class="w-full border border-slate-200 rounded-xl px-3.5 py-2 text-xs text-slate-900 focus:ring-2 focus:ring-sky-500 focus:outline-none"
                />
              </div>
            </div>
          </template>

          <div>
            <label class="block text-xs font-bold text-slate-700 mb-1.5">
              Alasan / Keterangan Mutasi <span class="text-rose-500">*</span>
            </label>
            <textarea
              v-model="form.alasan"
              rows="3"
              class="w-full border border-slate-200 rounded-xl px-3.5 py-2 text-xs text-slate-900 focus:ring-2 focus:ring-blue-500 focus:outline-none resize-none"
              :placeholder="mode === 'pisah' ? 'Contoh: Membentuk keluarga baru setelah sakramen pernikahan gereja...' : 'Contoh: Pindah domisili tempat tinggal ke KUB lain...'"
              required
            ></textarea>
          </div>

          <template v-if="mode === 'mutasi'">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">No. Surat Pindah (Opsional)</label>
                <input
                  v-model="form.no_surat_pindah"
                  placeholder="Nomor surat jalan / pindah..."
                  class="w-full border border-slate-200 rounded-xl px-3.5 py-2 text-xs text-slate-900 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                />
              </div>
              <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Tanggal Mutasi</label>
                <input
                  type="date"
                  v-model="form.tgl_mutasi"
                  class="w-full border border-slate-200 rounded-xl px-3.5 py-2 text-xs text-slate-900 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                />
              </div>
            </div>
          </template>

          <!-- Buttons -->
          <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-slate-100">
            <Link
              :href="backUrl"
              class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition cursor-pointer"
            >
              Batal
            </Link>
            <button
              type="submit"
              :disabled="isSubmitting"
              :class="mode === 'pisah' ? 'bg-sky-600 hover:bg-sky-700 shadow-sky-500/25' : 'bg-emerald-600 hover:bg-emerald-700 shadow-emerald-500/25'"
              class="px-5 py-2.5 rounded-xl text-white text-xs font-bold shadow-sm transition flex items-center gap-2 cursor-pointer disabled:opacity-50"
            >
              <i v-if="isSubmitting" class="fa-solid fa-circle-notch fa-spin text-xs"></i>
              <i v-else class="fa-solid fa-floppy-disk text-xs"></i>
              <span>{{ isSubmitting ? 'Menyimpan…' : (mode === 'pisah' ? 'Simpan Pisah KK' : 'Simpan Mutasi KUB') }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>
