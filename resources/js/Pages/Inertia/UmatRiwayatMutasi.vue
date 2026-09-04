<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    prefix: { type: String, default: 'superadmin' },
    umatItem: { type: Object, required: true },
    riwayatList: { type: Array, default: () => [] },
    namaParoki: { type: String, default: '' },
});

const basePrefix = computed(() => `/${props.prefix}`);
const backUrl = computed(() => `${basePrefix.value}/umat`);
const createUrl = computed(() => `${basePrefix.value}/riwayat-mutasi/tambah?umat_id=${props.umatItem.id}`);
const mutasiUrl = computed(() => `${basePrefix.value}/umat/${props.umatItem.id}/mutasi`);
const pisahUrl = computed(() => `${basePrefix.value}/umat/${props.umatItem.id}/pisah-kk`);

const formatDate = (d) => {
    if (!d) return '-';
    try {
        const date = new Date(d);
        return isNaN(date.getTime()) ? d : date.toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric' });
    } catch {
        return d;
    }
};
</script>

<template>
  <AppLayout :title="`Riwayat Mutasi – ${umatItem.nama_lengkap}`" :fullWidth="true">
    <Head :title="`Riwayat Mutasi: ${umatItem.nama_lengkap} - SIPAROKI`" />

    <div class="w-full space-y-4 pb-6">
      <!-- Breadcrumb & Back -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <Link
          :href="backUrl"
          class="inline-flex items-center gap-2 text-xs font-bold text-slate-600 hover:text-slate-900 bg-white border border-slate-200 px-3.5 py-2 rounded-xl shadow-2xs transition cursor-pointer self-start"
        >
          <i class="fa-solid fa-arrow-left text-xs"></i>
          <span>Kembali ke Data Umat</span>
        </Link>
        <div class="flex items-center gap-2 flex-wrap">
          <Link
            :href="mutasiUrl"
            class="px-3.5 py-2 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 text-xs font-bold transition flex items-center gap-1.5"
          >
            <i class="fa-solid fa-arrows-rotate text-xs"></i>
            <span>Mutasi KUB</span>
          </Link>
          <Link
            :href="pisahUrl"
            class="px-3.5 py-2 rounded-xl bg-sky-50 hover:bg-sky-100 text-sky-700 border border-sky-200 text-xs font-bold transition flex items-center gap-1.5"
          >
            <i class="fa-solid fa-ring text-xs"></i>
            <span>Pisah KK</span>
          </Link>
          <Link
            :href="createUrl"
            class="px-3.5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-sm shadow-blue-500/25 transition flex items-center gap-1.5"
          >
            <i class="fa-solid fa-plus text-xs"></i>
            <span>+ Tambah Riwayat Manual</span>
          </Link>
        </div>
      </div>

      <!-- Main Card -->
      <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5 sm:p-7 space-y-6">
        <div class="border-b border-slate-100 pb-4 flex items-center justify-between gap-4 flex-wrap">
          <div>
            <h1 class="text-base sm:text-lg font-black text-slate-900 flex items-center gap-2">
              <i class="fa-solid fa-clock-rotate-left text-fuchsia-600"></i>
              <span>Riwayat Mutasi & Pindah KUB</span>
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">
              Catatan perpindahan KUB, wilayah, dan pisah kartu keluarga untuk umat: <strong class="text-slate-800">{{ umatItem.nama_lengkap }}</strong>
            </p>
          </div>
          <span class="text-xs font-bold px-3 py-1 rounded-full bg-slate-100 text-slate-700 border border-slate-200">
            Total: {{ riwayatList.length }} Catatan
          </span>
        </div>

        <div v-if="riwayatList.length === 0" class="text-center py-12 bg-slate-50/60 rounded-xl border border-dashed border-slate-200 space-y-2">
          <i class="fa-solid fa-folder-open text-slate-300 text-3xl block"></i>
          <p class="text-xs font-bold text-slate-600">Belum ada riwayat mutasi atau pindah KUB untuk umat ini.</p>
          <p class="text-[11px] text-slate-400">Klik tombol "Mutasi KUB" atau "Pisah KK" untuk melakukan proses perpindahan.</p>
        </div>

        <div v-else class="overflow-x-auto rounded-xl border border-slate-200">
          <table class="w-full text-xs text-left border-collapse">
            <thead class="bg-slate-100 text-slate-700 font-bold border-b border-slate-200">
              <tr>
                <th class="px-3.5 py-2.5 text-center w-10">#</th>
                <th class="px-3.5 py-2.5">Jenis Mutasi</th>
                <th class="px-3.5 py-2.5">KUB Asal</th>
                <th class="px-3.5 py-2.5">KUB Tujuan</th>
                <th class="px-3.5 py-2.5 text-center">No. KK Baru</th>
                <th class="px-3.5 py-2.5 text-center">Tanggal</th>
                <th class="px-3.5 py-2.5">Alasan / Keterangan</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-for="(r, i) in riwayatList" :key="r.id" class="hover:bg-slate-50 transition">
                <td class="px-3.5 py-2.5 text-center font-bold text-slate-500">{{ riwayatList.length - i }}</td>
                <td class="px-3.5 py-2.5">
                  <span
                    :class="r.jenis_mutasi === 'Pisah KK (Menikah)'
                      ? 'bg-sky-50 text-sky-700 border-sky-200'
                      : 'bg-emerald-50 text-emerald-700 border-emerald-200'"
                    class="px-2 py-0.5 rounded-full text-[11px] font-bold border"
                  >
                    {{ r.jenis_mutasi }}
                  </span>
                </td>
                <td class="px-3.5 py-2.5 font-medium text-slate-700">{{ r.kub_asal_nama || '—' }}</td>
                <td class="px-3.5 py-2.5 font-semibold text-slate-900">{{ r.kub_tujuan_nama || '—' }}</td>
                <td class="px-3.5 py-2.5 text-center font-mono font-bold text-slate-800">{{ r.kk_tujuan_no || '—' }}</td>
                <td class="px-3.5 py-2.5 text-center text-slate-600 whitespace-nowrap">{{ formatDate(r.tgl_mutasi || r.created_at) }}</td>
                <td class="px-3.5 py-2.5 text-slate-600">{{ r.alasan || '—' }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
