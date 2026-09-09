<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { getDefaultAvatar } from '@/utils/avatar';

const props = defineProps({
    role: { type: String, default: 'Super Admin' },
    prefix: { type: String, default: 'superadmin' },
    kk: { type: Object, required: true },
    paroki: { type: Object, default: () => ({}) },
    keuskupan: { type: Object, default: () => ({}) },
});

const basePrefix = computed(() => `/${props.prefix}`);
const printUrl = computed(() => `${basePrefix.value}/kk-katolik/${props.kk.uuid || props.kk.id}/cetak`);
const editUrl = computed(() => `${basePrefix.value}/kk-katolik/${props.kk.uuid || props.kk.id}/edit`);
const listUrl = computed(() => `${basePrefix.value}/kk-katolik`);

const formatDate = (d) => {
    if (!d) return '-';
    try {
        const date = new Date(d);
        return isNaN(date.getTime()) ? d : date.toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric' });
    } catch {
        return d;
    }
};

const calculateAge = (d) => {
    if (!d) return null;
    try {
        const birthDate = new Date(d);
        if (isNaN(birthDate.getTime())) return null;
        const today = new Date();
        let age = today.getFullYear() - birthDate.getFullYear();
        const m = today.getMonth() - birthDate.getMonth();
        if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
            age--;
        }
        return age >= 0 ? age : null;
    } catch {
        return null;
    }
};

const isReadOnlyRole = computed(() => {
    const p = String(props.prefix || '').toLowerCase();
    const r = String(props.role || '').toLowerCase();
    return p.includes('wilayah') || p.includes('kapela') || p.includes('stasi') || r.includes('wilayah') || r.includes('kapela') || r.includes('stasi');
});

const formatLogoUrl = (url, fallback) => {
    if (!url) return fallback;
    if (typeof url === 'string') {
        if (url.startsWith('http://') || url.startsWith('https://')) return url;
        const clean = url.replace(/^\/+/, '');
        return `/${clean}`;
    }
    return fallback;
};

const keuskupanLogoUrl = computed(() => {
    const raw = props.keuskupan?.logo || props.keuskupan?.logo_url;
    return formatLogoUrl(raw, '/images/logo-keuskupan.png');
});

const parokiLogoUrl = computed(() => {
    const raw = props.paroki?.logo || props.paroki?.logo_url;
    return formatLogoUrl(raw, '/uploads/paroki/1787494152_6a8aff08b47a5.webp');
});

const keuskupanNama = computed(() => {
    return props.keuskupan?.nama_keuskupan || 'KEUSKUPAN AGUNG KUPANG';
});

const parokiNama = computed(() => {
    return props.paroki?.nama_paroki || 'PAROKI ST. VINSENSIUS A PAULO BENLUTU';
});

const parokiAlamat = computed(() => {
    return props.paroki?.alamat || 'Jl. Timor Raya, Desa Benlutu, Kec. Batu Putih, Kab. Timor Tengah Selatan, NTT';
});
</script>

<template>
    <AppLayout title="Detail Kartu Keluarga (KK) Katolik" :fullWidth="true">
        <Head :title="`KK ${kk.no_kk_kw} - ${kk.nama_lahir_pemilik} - SIPAROKI`" />

        <div class="w-full space-y-4 pb-6">
            <!-- Action Header Banner -->
            <div class="rounded-2xl bg-white border border-slate-200/80 p-4 sm:p-6 shadow-2xs">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-3.5">
                        <Link
                            :href="listUrl"
                            class="w-10 h-10 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center transition cursor-pointer shrink-0"
                            title="Kembali ke Daftar KK"
                        >
                            <i class="fa-solid fa-arrow-left text-sm"></i>
                        </Link>
                        <div class="flex items-center gap-3">
                            <img
                                :src="kk.foto ? (kk.foto.startsWith('http') ? kk.foto : '/' + String(kk.foto).replace(/^\/+/, '')) : getDefaultAvatar(kk.jenis_kelamin, kk.usia || kk.tanggal_lahir)"
                                :alt="kk.nama_lahir_pemilik"
                                class="w-12 h-12 rounded-2xl object-cover border border-slate-200 shadow-xs shrink-0"
                                @error="(e) => { e.target.src = getDefaultAvatar(kk.jenis_kelamin, kk.usia || kk.tanggal_lahir); }"
                            />
                            <div>
                                <div class="flex items-center gap-2.5 flex-wrap">
                                    <h1 class="text-xl font-black text-slate-900 tracking-tight">
                                        {{ kk.nama_lahir_pemilik }}
                                    </h1>
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-blue-50 text-blue-800 border border-blue-200">
                                        {{ kk.no_kk_kw }}
                                    </span>
                                    <span
                                        class="px-2.5 py-0.5 rounded-full text-xs font-bold border"
                                        :class="kk.status_kk === 'Aktif' ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : 'bg-slate-50 text-slate-700 border-slate-200'"
                                    >
                                        {{ kk.status_kk || 'Aktif' }}
                                    </span>
                                </div>
                                <p class="text-xs text-slate-500 mt-1 flex items-center gap-2 flex-wrap">
                                    <span><i class="fa-solid fa-church text-blue-600 me-1"></i> {{ kk.wilayah?.nama_wilayah || kk.kapela?.nama_kapela || 'Pusat Paroki' }}</span>
                                    <span>•</span>
                                    <span><i class="fa-solid fa-people-group text-emerald-600 me-1"></i> KUB: {{ kk.kub?.nama_kub || '-' }}</span>
                                    <span>•</span>
                                    <span><i class="fa-solid fa-location-dot text-amber-600 me-1"></i> {{ kk.desa_kelurahan || 'Benlutu' }}</span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-stretch sm:items-center gap-2 flex-col sm:flex-row shrink-0 w-full sm:w-auto">
                        <template v-if="!isReadOnlyRole">
                            <a
                                :href="printUrl"
                                target="_blank"
                                class="px-4 py-2.5 sm:py-2 rounded-xl bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-xs font-bold shadow-sm shadow-blue-500/25 transition flex items-center justify-center gap-2 cursor-pointer order-first sm:order-last"
                            >
                                <i class="fa-solid fa-print text-xs"></i>
                                <span>Cetak KK (PDF / Print)</span>
                            </a>
                            <Link
                                :href="editUrl"
                                class="px-4 py-2.5 sm:py-2 rounded-xl bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 text-xs font-bold transition flex items-center justify-center gap-1.5 cursor-pointer"
                            >
                                <i class="fa-solid fa-pen-to-square text-xs"></i>
                                <span>Edit Data KK</span>
                            </Link>
                        </template>
                        <div
                            v-else
                            class="px-3.5 py-2 rounded-xl bg-slate-100 border border-slate-200 text-slate-700 text-xs font-semibold flex items-center gap-1.5"
                        >
                            <i class="fa-solid fa-eye text-blue-600 text-xs"></i>
                            <span>Mode Lihat Saja (Cetak di KUB / Paroki)</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kartu Keluarga Document View -->
            <div class="rounded-2xl bg-white border border-slate-200/80 p-6 sm:p-8 shadow-xs space-y-6">
                <!-- Kop Resmi Gerejawi: Kiri Logo Keuskupan, Kanan Logo Paroki -->
                <div class="border-b-2 border-slate-900 pb-4 text-center relative">
                    <div class="flex items-center justify-between gap-4">
                        <!-- Logo Keuskupan (Sebelah Kiri) -->
                        <div class="w-16 h-16 rounded-full bg-slate-50 border border-slate-200 flex items-center justify-center p-1 overflow-hidden shrink-0 shadow-2xs">
                            <img
                                :src="keuskupanLogoUrl"
                                :alt="keuskupanNama"
                                class="w-full h-full object-contain rounded-full"
                                @error="(e) => { e.target.src = '/images/logo-keuskupan.png'; }"
                            >
                        </div>

                        <!-- Teks Kop Surat Resmi (Tengah) -->
                        <div class="flex-1 px-2 text-center">
                            <h3 class="text-xs font-black tracking-widest text-slate-700 uppercase">{{ keuskupanNama }}</h3>
                            <h2 class="text-lg font-black tracking-wider text-slate-900 uppercase mt-0.5">{{ parokiNama }}</h2>
                            <p class="text-[11px] text-slate-500 mt-0.5">{{ parokiAlamat }}</p>
                        </div>

                        <!-- Logo Paroki (Sebelah Kanan) -->
                        <div class="w-16 h-16 rounded-full bg-slate-50 border border-slate-200 flex items-center justify-center p-1 overflow-hidden shrink-0 shadow-2xs">
                            <img
                                :src="parokiLogoUrl"
                                :alt="parokiNama"
                                class="w-full h-full object-contain rounded-full"
                                @error="(e) => { e.target.src = '/uploads/paroki/1787494152_6a8aff08b47a5.webp'; }"
                            >
                        </div>
                    </div>
                </div>

                <!-- Judul Dokumen -->
                <div class="text-center">
                    <h3 class="text-base font-black text-slate-900 tracking-wide underline uppercase">KARTU KELUARGA (KK) KATOLIK</h3>
                    <p class="text-xs font-mono font-bold text-slate-700 mt-1">NOMOR REGISTRASI: {{ kk.no_kk_kw }}</p>
                </div>

                <!-- Informasi Header Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-2 text-xs bg-slate-50 p-4 rounded-xl border border-slate-200/70">
                    <div class="space-y-1.5">
                        <div class="flex justify-between border-b border-slate-200/50 pb-1">
                            <span class="text-slate-500 font-medium">Nama Kepala Keluarga:</span>
                            <span class="font-bold text-slate-900">{{ kk.nama_lahir_pemilik }}</span>
                        </div>
                        <div class="flex justify-between border-b border-slate-200/50 pb-1">
                            <span class="text-slate-500 font-medium">No. KK Sipil (Dukcapil):</span>
                            <span class="font-mono font-semibold text-slate-900">{{ kk.no_kk_dukcapil || '-' }}</span>
                        </div>
                        <div class="flex justify-between border-b border-slate-200/50 pb-1">
                            <span class="text-slate-500 font-medium">Wilayah Pastoral:</span>
                            <span class="font-semibold text-slate-900">{{ kk.wilayah?.nama_wilayah || kk.kapela?.nama_kapela || 'Pusat Paroki' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500 font-medium">KUB / KBG:</span>
                            <span class="font-semibold text-slate-900">{{ kk.kub?.nama_kub || '-' }}</span>
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <div class="flex justify-between border-b border-slate-200/50 pb-1">
                            <span class="text-slate-500 font-medium">Alamat Domisili:</span>
                            <span class="font-semibold text-slate-900">{{ kk.alamat_sekarang || 'Benlutu' }}</span>
                        </div>
                        <div class="flex justify-between border-b border-slate-200/50 pb-1">
                            <span class="text-slate-500 font-medium">RT / RW / Desa:</span>
                            <span class="font-semibold text-slate-900">RT {{ kk.rt || '00' }} / RW {{ kk.rw || '00' }}, {{ kk.desa_kelurahan || 'Benlutu' }}</span>
                        </div>
                        <div class="flex justify-between border-b border-slate-200/50 pb-1">
                            <span class="text-slate-500 font-medium">Kecamatan / Kabupaten:</span>
                            <span class="font-semibold text-slate-900">{{ kk.kecamatan || 'Batu Putih' }}, {{ kk.kota_kabupaten || 'TTS' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500 font-medium">Status KK / Ekonomi:</span>
                            <span class="font-semibold text-slate-900">{{ kk.status_kk || 'Aktif' }} / {{ kk.kategori_ekonomi || 'Mandiri' }}</span>
                        </div>
                    </div>
                </div>

                <!-- TABEL I: ANGGOTA KELUARGA -->
                <div class="space-y-2">
                    <h4 class="text-xs font-black text-slate-900 uppercase tracking-wider flex items-center gap-1.5">
                        <i class="fa-solid fa-users text-blue-600"></i>
                        <span>I. Daftar Susunan Anggota Keluarga</span>
                    </h4>
                    <div class="overflow-x-auto rounded-xl border border-slate-200">
                        <table class="w-full text-[11px] text-left">
                            <thead class="bg-slate-100 text-slate-700 font-bold border-b border-slate-200">
                                <tr>
                                    <th class="p-2 text-center w-8">No</th>
                                    <th class="p-2">Nama Lengkap</th>
                                    <th class="p-2">Nama Baptis</th>
                                    <th class="p-2 text-center">NIK Dukcapil</th>
                                    <th class="p-2 text-center">Hubungan</th>
                                    <th class="p-2 text-center">L/P</th>
                                    <th class="p-2">Tempat, Tgl Lahir</th>
                                    <th class="p-2">Pekerjaan</th>
                                    <th class="p-2 text-center">Gol</th>
                                    <th class="p-2 text-center">Status Nikah</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="(a, idx) in kk.anggota" :key="a.id" class="hover:bg-slate-50">
                                    <td class="p-2 text-center font-bold">{{ idx + 1 }}</td>
                                    <td class="p-2 font-bold text-slate-900">
                                        <div class="flex items-center gap-2.5">
                                            <img
                                                :src="a.foto ? (a.foto.startsWith('http') ? a.foto : '/' + String(a.foto).replace(/^\/+/, '')) : getDefaultAvatar(a.jenis_kelamin, a.usia || a.tanggal_lahir)"
                                                :alt="a.nama_lahir || a.nama_lengkap"
                                                class="w-7 h-7 rounded-full object-cover border border-slate-200 shadow-2xs shrink-0"
                                                @error="(e) => { e.target.src = getDefaultAvatar(a.jenis_kelamin, a.usia || a.tanggal_lahir); }"
                                            />
                                            <div>
                                                <div>{{ a.nama_lahir || a.nama_lengkap }}</div>
                                                <div v-if="a.status_panggilan && a.status_panggilan !== 'Awam'" class="mt-0.5 inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[9px] font-bold bg-purple-50 text-purple-700 border border-purple-200">
                                                    <i class="fa-solid fa-cross text-[8px]"></i>
                                                    <span>{{ a.status_panggilan }} ({{ a.nama_ordo_kongregasi || 'Biarawan' }})</span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="p-2 text-slate-600">{{ a.nama_baptis || '-' }}</td>
                                    <td class="p-2 text-center font-mono">{{ a.nik || '-' }}</td>
                                    <td class="p-2 text-center">{{ a.hubungan_keluarga || '-' }}</td>
                                    <td class="p-2 text-center">{{ a.jenis_kelamin === 'Laki-Laki' ? 'L' : (a.jenis_kelamin === 'Perempuan' ? 'P' : '-') }}</td>
                                    <td class="p-2">
                                        <div>{{ a.tempat_lahir || '-' }}, {{ formatDate(a.tanggal_lahir) }}</div>
                                        <div v-if="calculateAge(a.tanggal_lahir) !== null" class="text-[10px] text-blue-600 font-bold mt-0.5">
                                            {{ calculateAge(a.tanggal_lahir) }} Tahun
                                        </div>
                                    </td>
                                    <td class="p-2">{{ a.pekerjaan || '-' }}</td>
                                    <td class="p-2 text-center">{{ a.golongan_darah || '-' }}</td>
                                    <td class="p-2 text-center">{{ a.status_perkawinan || a.status_menikah || '-' }}</td>
                                </tr>
                                <tr v-if="!kk.anggota?.length">
                                    <td colspan="10" class="p-4 text-center text-slate-400 italic">Belum ada anggota keluarga terdaftar.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- TABEL II: DATA SAKRAMEN -->
                <div class="space-y-2">
                    <h4 class="text-xs font-black text-slate-900 uppercase tracking-wider flex items-center gap-1.5">
                        <i class="fa-solid fa-certificate text-amber-600"></i>
                        <span>II. Riwayat Penerimaan Sakramen & Pencatatan Buku Liber</span>
                    </h4>
                    <div class="overflow-x-auto rounded-xl border border-slate-200">
                        <table class="w-full text-[11px] text-left">
                            <thead class="bg-slate-100 text-slate-700 font-bold border-b border-slate-200">
                                <tr>
                                    <th class="p-2 text-center w-8" rowspan="2">No</th>
                                    <th class="p-2" rowspan="2">Nama Anggota</th>
                                    <th class="p-2 text-center bg-blue-50/50" colspan="3">Sakramen Permandian / Baptis</th>
                                    <th class="p-2 text-center bg-amber-50/50" colspan="2">Komuni I</th>
                                    <th class="p-2 text-center bg-purple-50/50" colspan="2">Krisma</th>
                                    <th class="p-2 text-center bg-rose-50/50" colspan="2">Pernikahan Gereja</th>
                                </tr>
                                <tr class="border-t border-slate-200 text-[10px]">
                                    <th class="p-1.5 text-center">Tgl / Tempat</th>
                                    <th class="p-1.5 text-center">Buku Liber (V/H/N)</th>
                                    <th class="p-1.5">Wali Baptis</th>
                                    <th class="p-1.5 text-center">Tanggal</th>
                                    <th class="p-1.5">Paroki</th>
                                    <th class="p-1.5 text-center">Tanggal</th>
                                    <th class="p-1.5">Paroki</th>
                                    <th class="p-1.5 text-center">Tanggal</th>
                                    <th class="p-1.5">Pasangan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="(a, idx) in kk.anggota" :key="a.id" class="hover:bg-slate-50">
                                    <td class="p-2 text-center font-bold">{{ idx + 1 }}</td>
                                    <td class="p-2 font-bold text-slate-900">{{ a.nama_baptis || a.nama_lengkap }}</td>
                                    <td class="p-2 text-center">{{ formatDate(a.tgl_baptis) }}<br><span class="text-[10px] text-slate-500">{{ a.paroki_baptis || '-' }}</span></td>
                                    <td class="p-2 text-center font-mono font-bold">
                                        {{ a.buku_baptis_vol || '-' }}/{{ a.buku_baptis_hal || '-' }}/{{ a.buku_baptis_no || '-' }}
                                    </td>
                                    <td class="p-2">{{ a.wali_baptis || '-' }}</td>
                                    <td class="p-2 text-center">{{ a.tgl_komuni_1 || '-' }}</td>
                                    <td class="p-2">{{ a.paroki_komuni_1 || '-' }}</td>
                                    <td class="p-2 text-center">{{ a.tgl_krisma || '-' }}</td>
                                    <td class="p-2">{{ a.paroki_krisma || '-' }}</td>
                                    <td class="p-2 text-center">{{ a.tgl_perkawinan || '-' }}</td>
                                    <td class="p-2">{{ a.nama_pasangan || '-' }}</td>
                                </tr>
                                <tr v-if="!kk.anggota?.length">
                                    <td colspan="11" class="p-4 text-center text-slate-400 italic">Belum ada data sakramen.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Footer Signatures Preview -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 pt-6 border-t border-slate-200 text-center text-xs text-slate-600">
                    <div>
                        <p class="text-slate-500">Mengetahui,</p>
                        <p class="font-bold text-slate-800">Ketua KUB / KBG</p>
                        <div class="h-14"></div>
                        <p class="font-bold underline">({{ kk.kub?.ketua || '...........................' }})</p>
                    </div>
                    <div>
                        <p class="text-slate-500">Mengetahui,</p>
                        <p class="font-bold text-slate-800">Ketua Wilayah Pastoral</p>
                        <div class="h-14"></div>
                        <p class="font-bold underline">({{ kk.wilayah?.ketua_wilayah || '...........................' }})</p>
                    </div>
                    <div>
                        <p class="text-slate-500">Pemberi Data,</p>
                        <p class="font-bold text-slate-800">Kepala Keluarga</p>
                        <div class="h-14"></div>
                        <p class="font-bold underline">({{ kk.nama_lahir_pemilik }})</p>
                    </div>
                    <div>
                        <p class="text-slate-500">Sekretariat Paroki,</p>
                        <p class="font-bold text-slate-800">Pastor Paroki</p>
                        <div class="h-14"></div>
                        <p class="font-bold underline">({{ paroki?.nama_pastor_paroki_aktif || 'Pastor Paroki' }})</p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
