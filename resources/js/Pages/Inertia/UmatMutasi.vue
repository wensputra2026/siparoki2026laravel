<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import SearchableSelect from '@/Components/SearchableSelect.vue';
import DateInput from '@/Components/DateInput.vue';

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
  <AppLayout :title="mode === 'pisah' ? 'Pisah KK (Menikah)' : 'Mutasi Umat antar KUB'" :fullWidth="true">
    <Head :title="mode === 'pisah' ? `Pisah KK: ${umatItem.nama_lengkap} - SIPAROKI` : `Mutasi: ${umatItem.nama_lengkap} - SIPAROKI`" />

    <div class="w-full space-y-4 pb-6">
      <!-- Breadcrumb & Back Banner -->
      <div class="rounded-2xl bg-white border border-slate-200/80 p-4 sm:p-5 shadow-2xs">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
          <div class="flex items-center gap-3">
            <Link
              :href="backUrl"
              class="w-10 h-10 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center transition cursor-pointer shrink-0"
              title="Kembali ke Data Umat"
            >
              <i class="fa-solid fa-arrow-left text-sm"></i>
            </Link>
            <div>
              <div class="flex items-center gap-2 flex-wrap">
                <h1 class="text-lg font-black text-slate-900 tracking-tight">
                  {{ mode === 'pisah' ? 'Pisah KK (Bentuk Keluarga Baru / Menikah)' : 'Mutasi Umat antar KUB' }}
                </h1>
                <span
                  :class="mode === 'pisah' ? 'bg-sky-50 text-sky-800 border-sky-200' : 'bg-emerald-50 text-emerald-800 border-emerald-200'"
                  class="px-2.5 py-0.5 rounded-full text-xs font-bold border"
                >
                  {{ mode === 'pisah' ? 'Pisah Kartu Keluarga' : 'Mutasi KUB' }}
                </span>
              </div>
              <p class="text-xs text-slate-500 mt-0.5">
                {{ namaParoki || 'Paroki St. Vinsensius a Paulo Benlutu' }} &bull; Sensus Administrasi &amp; Mutasi Teritorial Pastoral
              </p>
            </div>
          </div>

          <div class="flex items-center gap-2 shrink-0">
            <Link
              :href="backUrl"
              class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition flex items-center gap-1.5 cursor-pointer"
            >
              <i class="fa-solid fa-xmark text-xs"></i>
              <span>Batal</span>
            </Link>
            <button
              type="button"
              :disabled="isSubmitting"
              @click="submit"
              :class="mode === 'pisah' ? 'bg-sky-600 hover:bg-sky-700 shadow-sky-500/25' : 'bg-emerald-600 hover:bg-emerald-700 shadow-emerald-500/25'"
              class="px-4 py-2 rounded-xl text-white text-xs font-bold shadow-sm transition flex items-center gap-1.5 cursor-pointer disabled:opacity-50"
            >
              <i v-if="isSubmitting" class="fa-solid fa-circle-notch fa-spin text-xs"></i>
              <i v-else class="fa-solid fa-floppy-disk text-xs"></i>
              <span>{{ isSubmitting ? 'Menyimpan…' : (mode === 'pisah' ? 'Simpan Pisah KK' : 'Simpan Mutasi KUB') }}</span>
            </button>
          </div>
        </div>
      </div>

      <!-- Main Form Container Full Width -->
      <div class="rounded-2xl bg-white border border-slate-200/80 p-5 sm:p-7 shadow-xs space-y-6">
        <!-- Info Umat Asal Card -->
        <div class="border-b border-slate-100 pb-5">
          <div class="flex items-center gap-2 mb-3">
            <i class="fa-solid fa-id-card text-blue-600 text-sm"></i>
            <h2 class="text-xs font-black uppercase tracking-wider text-slate-900">Data Umat &amp; Domisili Terdaftar Saat Ini</h2>
          </div>
          <div class="bg-slate-50/80 rounded-xl p-4 border border-slate-200/70 grid grid-cols-1 sm:grid-cols-4 gap-4 text-xs">
            <div>
              <span class="text-slate-400 block text-[11px] font-medium">Nama Lengkap Umat:</span>
              <span class="font-bold text-slate-900 text-sm">{{ umatItem.nama_lengkap }}</span>
            </div>
            <div>
              <span class="text-slate-400 block text-[11px] font-medium">Nomor KK Asal:</span>
              <span class="font-mono font-bold text-slate-800">{{ kkAsal?.no_kk_kw || '—' }}</span>
            </div>
            <div>
              <span class="text-slate-400 block text-[11px] font-medium">KUB / KBG Asal:</span>
              <span class="font-bold text-emerald-700">{{ kubAsal?.nama_kub || '—' }}</span>
            </div>
            <div>
              <span class="text-slate-400 block text-[11px] font-medium">Kepala Keluarga Asal:</span>
              <span class="font-semibold text-slate-800">{{ kkAsal?.nama_lahir_pemilik || '—' }}</span>
            </div>
          </div>
        </div>

        <!-- Form Body -->
        <form @submit.prevent="submit" class="space-y-5">
          <template v-if="mode === 'mutasi'">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
              <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5 flex items-center justify-between">
                  <span>KUB / KBG Tujuan</span>
                  <span class="text-rose-500 font-normal">* Wajib</span>
                </label>
                <SearchableSelect
                  v-model="form.kub_tujuan_id"
                  :options="kubOptions"
                  placeholder="-- Pilih KUB tujuan --"
                  searchPlaceholder="Cari KUB tujuan..."
                  icon="fa-solid fa-people-group"
                  iconColor="text-emerald-600"
                />
              </div>
              <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5 flex items-center justify-between">
                  <span>KK Tujuan (Kepala Keluarga Baru dalam KUB)</span>
                  <span class="text-rose-500 font-normal">* Wajib</span>
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
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
              <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">No. Surat Pindah (Opsional)</label>
                <input
                  v-model="form.no_surat_pindah"
                  placeholder="Nomor surat jalan / mutasi dari KUB/Paroki asal..."
                  class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none transition"
                />
              </div>
              <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Tanggal Mutasi Terhitung</label>
                <DateInput
                  v-model="form.tgl_mutasi"
                  placeholder="dd/mm/yyyy"
                  iconColor="text-emerald-600"
                  inputClass="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none transition font-medium"
                />
              </div>
            </div>
          </template>

          <template v-else>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
              <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5 flex items-center justify-between">
                  <span>KUB / KBG Tujuan (Keluarga Baru)</span>
                  <span class="text-rose-500 font-normal">* Wajib</span>
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
              <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5 flex items-center justify-between">
                  <span>No. KK Baru</span>
                  <span class="text-rose-500 font-normal">* Wajib</span>
                </label>
                <input
                  v-model="form.no_kk_kw"
                  placeholder="Contoh: K012014..."
                  class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs font-mono font-bold text-slate-900 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-sky-500 focus:outline-none transition"
                  required
                />
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
              <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5 flex items-center justify-between">
                  <span>Nama Kepala Keluarga Baru</span>
                  <span class="text-rose-500 font-normal">* Wajib</span>
                </label>
                <input
                  v-model="form.nama_pemilik_kk"
                  placeholder="Nama kepala keluarga..."
                  class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-sky-500 focus:outline-none font-semibold transition"
                  required
                />
              </div>
              <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Tanggal Perkawinan</label>
                <DateInput
                  v-model="form.tgl_perkawinan"
                  placeholder="dd/mm/yyyy"
                  iconColor="text-sky-600"
                  inputClass="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-sky-500 focus:outline-none transition font-medium"
                />
              </div>
              <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Nama Pasangan (Istri / Suami)</label>
                <input
                  v-model="form.nama_pasangan"
                  placeholder="Nama lengkap pasangan..."
                  class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-sky-500 focus:outline-none transition"
                />
              </div>
            </div>
          </template>

          <div>
            <label class="block text-xs font-bold text-slate-700 mb-1.5 flex items-center justify-between">
              <span>Alasan / Keterangan Mutasi</span>
              <span class="text-rose-500 font-normal">* Wajib</span>
            </label>
            <textarea
              v-model="form.alasan"
              rows="3"
              class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none resize-none transition"
              :placeholder="mode === 'pisah' ? 'Contoh: Membentuk keluarga baru setelah sakramen pernikahan gereja...' : 'Contoh: Pindah domisili tempat tinggal ke KUB lain...'"
              required
            ></textarea>
          </div>

          <!-- Action Footer Buttons -->
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
