<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    prefix: { type: String, default: 'superadmin' },
    umatItem: { type: Object, required: true },
    riwayatList: { type: Array, default: () => [] },
    namaParoki: { type: String, default: '' },
});
</script>

<template>
<AppLayout :title="`Riwayat Mutasi – ${umatItem.nama_lengkap}`">
  <Head :title="`Riwayat Mutasi – ${umatItem.nama_lengkap}`" />
  <div class="max-w-4xl mx-auto py-6 px-4">
    <div class="bg-white rounded-xl shadow p-6">
      <div class="flex items-center justify-between mb-4">
        <div>
          <h1 class="text-lg font-bold text-slate-800">Riwayat Mutasi & Pindah KUB</h1>
          <p class="text-sm text-slate-500">
            Umat: <b>{{ umatItem.nama_lengkap }}</b> — {{ namaParoki }}
          </p>
        </div>
        <Link
          :href="route(`panel.${prefix}.riwayat-mutasi.create`) + `?umat_id=${umatItem.id}`"
          class="px-4 py-2 rounded-lg bg-emerald-600 text-white font-semibold"
        >
          + Tambah Riwayat
        </Link>
        <Link :href="route(`panel.${prefix}.umat`)" class="px-4 py-2 rounded-lg bg-slate-200 text-slate-700 font-semibold">
          Kembali ke Data Umat
        </Link>
      </div>

      <div v-if="riwayatList.length === 0" class="text-center text-slate-400 py-10">
        Belum ada riwayat mutasi / pindah KUB untuk umat ini.
      </div>

      <div v-else class="overflow-x-auto">
        <table class="w-full text-sm border-collapse">
          <thead>
            <tr class="bg-slate-50 text-slate-600 text-left">
              <th class="px-3 py-2 border">#</th>
              <th class="px-3 py-2 border">Jenis</th>
              <th class="px-3 py-2 border">KUB Asal</th>
              <th class="px-3 py-2 border">KUB Tujuan</th>
              <th class="px-3 py-2 border">KK Tujuan</th>
              <th class="px-3 py-2 border">Tgl</th>
              <th class="px-3 py-2 border">Alasan</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(r, i) in riwayatList" :key="r.id" class="hover:bg-slate-50">
              <td class="px-3 py-2 border">{{ riwayatList.length - i }}</td>
              <td class="px-3 py-2 border">
                <span
                  :class="r.jenis_mutasi === 'Pisah KK (Menikah)'
                    ? 'bg-sky-100 text-sky-700'
                    : 'bg-emerald-100 text-emerald-700'"
                  class="px-2 py-0.5 rounded text-xs font-semibold"
                >{{ r.jenis_mutasi }}</span>
              </td>
              <td class="px-3 py-2 border">{{ r.kub_asal_nama || '—' }}</td>
              <td class="px-3 py-2 border">{{ r.kub_tujuan_nama || '—' }}</td>
              <td class="px-3 py-2 border">{{ r.kk_tujuan_no || '—' }}</td>
              <td class="px-3 py-2 border">{{ r.tgl_mutasi || r.created_at }}</td>
              <td class="px-3 py-2 border">{{ r.alasan || '—' }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</AppLayout>
</template>
