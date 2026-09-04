<script setup>
import { ref, computed, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import SearchableSelect from '@/Components/SearchableSelect.vue';

const props = defineProps({
    prefix: { type: String, default: 'superadmin' },
    umatId: { type: Number, default: null },
    umatList: { type: Array, default: () => [] },
    kubList: { type: Array, default: () => [] },
    wilayahList: { type: Array, default: () => [] },
    jenisList: { type: Array, default: () => [] },
    namaParoki: { type: String, default: '' },
});

const isSubmitting = ref(false);
const form = ref({
    umat_id: props.umatId ?? '',
    jenis_mutasi: '',
    kub_asal_id: '',
    kub_tujuan_id: '',
    wilayah_asal_id: '',
    wilayah_tujuan_id: '',
    tgl_mutasi: '',
    alasan: '',
});

const umatOptions = computed(() => (props.umatList || []).map(u => ({
    id: u.id,
    name: u.nama_lengkap,
    kub_asal_id: u.kub_asal_id ?? null,
    wilayah_asal_id: u.wilayah_asal_id ?? null,
})));
const kubOptions = computed(() => (props.kubList || []).map(k => ({
    id: k.id,
    name: `${k.nama_kub} (${k.kode_kub || '-'})`,
})));
const wilayahOptions = computed(() => (props.wilayahList || []).map(w => ({
    id: w.id,
    name: w.nama_wilayah,
})));

// Auto-isi KUB Asal & Wilayah Asal dari KK umat yang dipilih.
const fillAsalFromUmat = (id) => {
    const u = (props.umatList || []).find(x => x.id === id);
    form.value.kub_asal_id = u && u.kub_asal_id ? u.kub_asal_id : '';
    form.value.wilayah_asal_id = u && u.wilayah_asal_id ? u.wilayah_asal_id : '';
};

if (props.umatId) {
    form.value.umat_id = props.umatId;
    fillAsalFromUmat(props.umatId);
}
watch(() => form.value.umat_id, (id) => {
    if (id) fillAsalFromUmat(id);
});

const basePrefix = computed(() => `/${props.prefix}`);

const submit = () => {
    isSubmitting.value = true;
    router.post(`${basePrefix.value}/riwayat-mutasi/tambah`, form.value, {
        preserveScroll: true,
        onFinish: () => (isSubmitting.value = false),
    });
};
</script>

<template>
<AppLayout :title="'Tambah Riwayat Mutasi Umat'">
  <Head :title="'Tambah Riwayat Mutasi Umat & KUB'" />
  <div class="max-w-3xl mx-auto py-6 px-4">
    <div class="bg-white rounded-xl shadow p-6">
      <h1 class="text-lg font-bold text-slate-800">Form Tambah Riwayat Mutasi Umat &amp; KUB</h1>
      <p class="text-sm text-slate-500 mb-4">{{ namaParoki }} — pilihan umat, KUB, dan wilayah diambil dari database.</p>

      <form @submit.prevent="submit" class="space-y-4">
        <div>
          <label class="block text-sm font-semibold text-slate-700">Nama Umat</label>
          <SearchableSelect v-model="form.umat_id" :options="umatOptions" icon="fa-user" placeholder="Pilih umat..." />
        </div>

        <div>
          <label class="block text-sm font-semibold text-slate-700">Jenis Mutasi</label>
          <select v-model="form.jenis_mutasi" class="w-full border rounded px-3 py-2">
            <option value="">— Pilih jenis mutasi —</option>
            <option v-for="j in jenisList" :key="j" :value="j">{{ j }}</option>
          </select>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
          <label class="block text-sm font-semibold text-slate-700">KUB Asal</label>
          <SearchableSelect v-model="form.kub_asal_id" :options="kubOptions" icon="fa-people-group" placeholder="Pilih KUB asal..." />
          </div>
          <div>
            <label class="block text-sm font-semibold text-slate-700">KUB Tujuan</label>
            <SearchableSelect v-model="form.kub_tujuan_id" :options="kubOptions" icon="fa-people-group" placeholder="Pilih KUB tujuan..." />
          </div>
        </div>
        <p class="text-xs text-slate-400 -mt-1">KUB Asal &amp; Wilayah Asal otomatis diisi dari KK umat yang dipilih (bisa diubah bila perlu).</p>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-semibold text-slate-700">Wilayah Asal</label>
            <SearchableSelect v-model="form.wilayah_asal_id" :options="wilayahOptions" icon="fa-church" placeholder="Pilih wilayah asal..." />
          </div>
          <div>
            <label class="block text-sm font-semibold text-slate-700">Wilayah Tujuan</label>
            <SearchableSelect v-model="form.wilayah_tujuan_id" :options="wilayahOptions" icon="fa-church" placeholder="Pilih wilayah tujuan..." />
          </div>
        </div>

        <div>
          <label class="block text-sm font-semibold text-slate-700">Tanggal Mutasi</label>
          <input type="date" v-model="form.tgl_mutasi" class="w-full border rounded px-3 py-2" />
        </div>

        <div>
          <label class="block text-sm font-semibold text-slate-700">Alasan / Keterangan</label>
          <textarea v-model="form.alasan" rows="3" class="w-full border rounded px-3 py-2" placeholder="Masukkan alasan / keterangan..."></textarea>
        </div>

        <div class="flex gap-2 pt-2">
          <button type="submit" :disabled="isSubmitting"
            class="px-4 py-2 rounded-lg bg-emerald-600 text-white font-semibold disabled:opacity-50">
            {{ isSubmitting ? 'Menyimpan…' : 'Simpan Riwayat' }}
          </button>
          <Link :href="`${basePrefix}/umat`" class="px-4 py-2 rounded-lg bg-slate-200 text-slate-700 font-semibold">
            Batal
          </Link>
        </div>
      </form>
    </div>
  </div>
</AppLayout>
</template>
