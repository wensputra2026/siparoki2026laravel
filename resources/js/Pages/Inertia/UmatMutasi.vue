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

const isSubmitting = ref(false);
const form = ref({
    kub_tujuan_id: '',
    kk_tujuan_id: '',
    alasan: '',
    no_surat_pindah: '',
    tgl_mutasi: '',
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
    ? route(`panel.${props.prefix}.umat.pisah.store`, [props.umatItem.id])
    : route(`panel.${props.prefix}.umat.mutasi.store`, [props.umatItem.id]));

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
  <Head :title="mode === 'pisah' ? 'Pisah KK' : 'Mutasi Umat'" />
  <div class="max-w-3xl mx-auto py-6 px-4">
    <div class="bg-white rounded-xl shadow p-6">
      <h1 class="text-lg font-bold text-slate-800">
        {{ mode === 'pisah' ? 'Pisah KK – Bentuk Keluarga Baru (Menikah)' : 'Mutasi Umat antar KUB' }}
      </h1>
      <p class="text-sm text-slate-500 mb-4">
        Umat: <b>{{ umatItem.nama_lengkap }}</b> —
        KK saat ini: {{ kkAsal?.no_kk_kw || '—' }}
        (KUB: {{ kubAsal?.nama_kub || '—' }})
      </p>

      <form @submit.prevent="submit" class="space-y-4">
        <template v-if="mode === 'mutasi'">
          <div>
            <label class="block text-sm font-semibold text-slate-700">KUB Tujuan</label>
            <SearchableSelect v-model="form.kub_tujuan_id" :options="kubOptions" placeholder="Pilih KUB tujuan" />
          </div>
          <div>
            <label class="block text-sm font-semibold text-slate-700 mt-3">KK Tujuan (dalam KUB tujuan)</label>
            <SearchableSelect v-model="form.kk_tujuan_id" :options="kkOptions" placeholder="Pilih KK tujuan" />
          </div>
        </template>

        <template v-else>
          <div>
            <label class="block text-sm font-semibold text-slate-700">KUB Tujuan (Keluarga Baru)</label>
            <SearchableSelect v-model="form.kub_tujuan_id" :options="kubOptions" placeholder="Pilih KUB" />
          </div>
          <div>
            <label class="block text-sm font-semibold text-slate-700 mt-3">No. KK Kw</label>
            <input v-model="form.no_kk_kw" class="w-full border rounded px-3 py-2" />
          </div>
          <div>
            <label class="block text-sm font-semibold text-slate-700 mt-3">Nama Pemilik KK</label>
            <input v-model="form.nama_pemilik_kk" class="w-full border rounded px-3 py-2" />
          </div>
          <div>
            <label class="block text-sm font-semibold text-slate-700 mt-3">Tgl Perkawinan</label>
            <input type="date" v-model="form.tgl_perkawinan" class="w-full border rounded px-3 py-2" />
          </div>
          <div>
            <label class="block text-sm font-semibold text-slate-700 mt-3">Nama Pasangan</label>
            <input v-model="form.nama_pasangan" class="w-full border rounded px-3 py-2" />
          </div>
        </template>

        <div>
          <label class="block text-sm font-semibold text-slate-700">Alasan / Keterangan</label>
          <textarea v-model="form.alasan" rows="3" class="w-full border rounded px-3 py-2"></textarea>
        </div>

        <template v-if="mode === 'mutasi'">
          <div>
            <label class="block text-sm font-semibold text-slate-700">No. Surat Pindah</label>
            <input v-model="form.no_surat_pindah" class="w-full border rounded px-3 py-2" />
          </div>
          <div>
            <label class="block text-sm font-semibold text-slate-700 mt-3">Tgl Mutasi</label>
            <input type="date" v-model="form.tgl_mutasi" class="w-full border rounded px-3 py-2" />
          </div>
        </template>

        <div class="flex gap-2 pt-2">
          <button type="submit" :disabled="isSubmitting"
            class="px-4 py-2 rounded-lg bg-emerald-600 text-white font-semibold disabled:opacity-50">
            {{ isSubmitting ? 'Menyimpan…' : 'Simpan & Catat Riwayat' }}
          </button>
          <Link :href="route(`panel.${prefix}.umat`)" class="px-4 py-2 rounded-lg bg-slate-200 text-slate-700 font-semibold">
            Batal
          </Link>
        </div>
      </form>
    </div>
  </div>
</AppLayout>
</template>
