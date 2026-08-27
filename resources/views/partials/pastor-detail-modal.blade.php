<!-- Universal Pastor Detail Modal (Bootstrap 5 Standard, Direct Body Mount & Perfectly Responsive) -->
<style>
#pastorDetailModal {
    z-index: 1060 !important;
}
.modal-backdrop {
    z-index: 1050 !important;
}
#pastorDetailModal .modal-dialog {
    max-width: 660px;
    margin: 1.75rem auto;
}
@media (max-width: 768px) {
    #pastorDetailModal .modal-dialog {
        margin: 0.75rem auto;
        max-width: calc(100% - 1.5rem);
    }
    #pastorDetailModal .modal-header {
        padding: 20px 16px 16px !important;
    }
    #pastorDetailModal .modal-body {
        padding: 16px !important;
    }
}
#pastorDetailModal .modal-content {
    border: none;
    border-radius: 22px;
    overflow: hidden;
    box-shadow: 0 25px 60px rgba(0, 0, 0, 0.35);
    background: #ffffff;
}
#pastorDetailModal .info-block {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 10px 14px;
}
#pastorDetailModal .info-label {
    font-size: 0.72rem;
    font-weight: 700;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    margin-bottom: 2px;
    display: block;
}
#pastorDetailModal .info-value {
    font-size: 0.86rem;
    font-weight: 700;
    color: #0f172a;
    line-height: 1.35;
}
</style>

<div class="modal fade" id="pastorDetailModal" tabindex="-1" aria-labelledby="pastorDetailModalLabel" aria-hidden="true" data-bs-backdrop="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            
            <!-- Modal Header -->
            <div class="modal-header border-0 text-white position-relative" style="background: linear-gradient(135deg, #0d9488 0%, #0f766e 60%, #042f2e 100%); padding: 24px 24px 18px;">
                <button type="button" class="btn-close btn-close-white position-absolute" data-bs-dismiss="modal" aria-label="Close" style="top: 18px; right: 18px; opacity: 0.9; cursor: pointer;"></button>

                <div class="d-flex align-items-center gap-3 w-100 pe-4">
                    <div style="width: 74px; height: 74px; border-radius: 18px; overflow: hidden; border: 3px solid rgba(255,255,255,0.9); box-shadow: 0 6px 14px rgba(0,0,0,0.18); background: #ffffff; flex-shrink: 0;">
                        <img id="modalPastorFoto" src="{{ asset('assets/frontend/siparoki/images/default-pastor.jpg') }}" alt="Pastor" style="width: 100%; height: 100%; object-fit: cover; display: block;" onerror="this.onerror=null; this.src='{{ asset('assets/frontend/siparoki/images/default-pastor.jpg') }}';">
                    </div>
                    <div style="flex: 1; min-width: 0;">
                        <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                            <span id="modalPastorJabatanBadge" class="badge" style="background: rgba(255, 255, 255, 0.25); backdrop-filter: blur(4px); font-size: 0.72rem; font-weight: 700; text-transform: uppercase; padding: 4px 10px; border-radius: 20px; letter-spacing: 0.5px;">
                                Pastor
                            </span>
                            <span id="modalPastorStatusBadge" class="badge" style="background: #10b981; color: #ffffff; font-size: 0.7rem; font-weight: 700; padding: 4px 10px; border-radius: 20px;">
                                Aktif
                            </span>
                        </div>
                        <h4 id="modalPastorNama" class="modal-title mb-1 text-white text-truncate" style="font-size: 1.25rem; font-weight: 800; line-height: 1.3;">
                            Nama Pastor
                        </h4>
                        <p id="modalPastorSub" class="mb-0 text-white-50 text-truncate" style="font-size: 0.8rem;">
                            Keuskupan Agung Kupang
                        </p>
                    </div>
                </div>
            </div>

            <!-- Modal Body (Scrollable) -->
            <div class="modal-body p-4" style="background: #fdfdfd;">
                <div class="d-flex flex-column gap-3">
                    
                    <!-- Motto Box (Conditional) -->
                    <div id="modalMottoBox" style="display: none; background: #fffbeb; border-left: 4px solid #f59e0b; border-radius: 12px; padding: 12px 16px;">
                        <span style="font-size: 0.72rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; color: #b45309; display: block; margin-bottom: 2px;">
                            <i class="fa-solid fa-quote-left me-1"></i> Motto Imamat / Pelayanan:
                        </span>
                        <p id="modalPastorMotto" style="font-size: 0.92rem; font-style: italic; font-weight: 600; color: #78350f; margin: 0; line-height: 1.5;">
                            ""
                        </p>
                    </div>

                    <!-- Informasi Tugas Pastoral -->
                    <div class="bg-white border rounded-3 p-3 shadow-2xs">
                        <h6 class="text-uppercase fw-bold mb-2.5 d-flex align-items-center gap-2" style="color: #0d9488; font-size: 0.8rem; letter-spacing: 0.5px;">
                            <i class="fa-solid fa-church"></i> Informasi Tugas Pastoral
                        </h6>
                        <div class="row g-2">
                            <div class="col-sm-6 col-12">
                                <div class="info-block">
                                    <span class="info-label">Jabatan Pelayanan</span>
                                    <span id="modalInfoJabatan" class="info-value">-</span>
                                </div>
                            </div>
                            <div class="col-sm-6 col-12">
                                <div class="info-block">
                                    <span class="info-label">Tempat / Paroki Tugas</span>
                                    <span id="modalInfoParoki" class="info-value" style="color: #0d9488;">-</span>
                                </div>
                            </div>
                            <div class="col-sm-6 col-12">
                                <div class="info-block">
                                    <span class="info-label">Periode Masa Tugas</span>
                                    <span id="modalInfoPeriode" class="info-value">-</span>
                                </div>
                            </div>
                            <div class="col-sm-6 col-12">
                                <div class="info-block">
                                    <span class="info-label">Jenis Imam / Tarekat</span>
                                    <span id="modalInfoJenisImam" class="info-value">-</span>
                                </div>
                            </div>
                            <div class="col-12" id="modalCatatanWrap">
                                <div class="info-block">
                                    <span class="info-label">Catatan Pelayanan Pastoral</span>
                                    <p id="modalInfoCatatan" class="mb-0 fw-normal text-secondary mt-1" style="font-size: 0.84rem; line-height: 1.5;">
                                        Melayani perayaan sakramen, ekaristi, dan reksa pastoral penggembalaan umat.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Identitas & Tahbisan Imamat -->
                    <div class="bg-white border rounded-3 p-3 shadow-2xs" id="modalIdentitasSection">
                        <h6 class="text-uppercase fw-bold mb-2.5 d-flex align-items-center gap-2" style="color: #0d9488; font-size: 0.8rem; letter-spacing: 0.5px;">
                            <i class="fa-solid fa-address-card"></i> Identitas &amp; Tahbisan
                        </h6>
                        <div class="row g-2">
                            <div class="col-sm-6 col-12">
                                <div class="info-block">
                                    <span class="info-label">Keuskupan Asal</span>
                                    <span id="modalInfoKeuskupan" class="info-value">-</span>
                                </div>
                            </div>
                            <div class="col-sm-6 col-12" id="modalTTLWrap">
                                <div class="info-block">
                                    <span class="info-label">Tempat &amp; Tanggal Lahir</span>
                                    <span id="modalInfoTTL" class="info-value">-</span>
                                </div>
                            </div>
                            <div class="col-sm-6 col-12" id="modalNamaBaptisWrap">
                                <div class="info-block">
                                    <span class="info-label">Nama Baptis / Lahir</span>
                                    <span id="modalInfoNamaBaptis" class="info-value">-</span>
                                </div>
                            </div>
                            <div class="col-sm-6 col-12" id="modalTahbisanWrap">
                                <div class="info-block">
                                    <span class="info-label">Tahbisan Imamat</span>
                                    <span id="modalInfoTahbisan" class="info-value">-</span>
                                </div>
                            </div>
                            <div class="col-sm-6 col-12" id="modalUskupWrap">
                                <div class="info-block">
                                    <span class="info-label">Uskup Penahbis</span>
                                    <span id="modalInfoUskup" class="info-value">-</span>
                                </div>
                            </div>
                            <div class="col-sm-6 col-12" id="modalPendidikanWrap">
                                <div class="info-block">
                                    <span class="info-label">Pendidikan / Seminari</span>
                                    <span id="modalInfoPendidikan" class="info-value">-</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Riwayat Tambahan List (Conditional) -->
                    <div id="modalRiwayatTambahanBox" style="display: none;" class="bg-white border rounded-3 p-3 shadow-2xs">
                        <h6 class="text-uppercase fw-bold mb-2 d-flex align-items-center gap-2" style="color: #0d9488; font-size: 0.8rem; letter-spacing: 0.5px;">
                            <i class="fa-solid fa-clock-rotate-left"></i> Riwayat Tugas Sebelumnya
                        </h6>
                        <div id="modalRiwayatTambahanList" class="d-flex flex-column gap-2 mt-2">
                            <!-- Injected dynamically -->
                        </div>
                    </div>

                </div>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer bg-light border-top px-4 py-2.5 d-flex align-items-center justify-content-between">
                <span class="text-muted fw-semibold" style="font-size: 0.76rem;">
                    <i class="fa-solid fa-circle-check text-success me-1"></i> Data Resmi Terverifikasi Paroki
                </span>
                <button type="button" class="btn text-white px-4 py-2 fw-bold" data-bs-dismiss="modal" style="background: #0d9488; border-radius: 12px; font-size: 0.82rem;">
                    Tutup
                </button>
            </div>

        </div>
    </div>
</div>

<script>
// Pastikan modal terpasang langsung di document.body agar tidak tertutup backdrop / navbar
document.addEventListener('DOMContentLoaded', function() {
    var modalEl = document.getElementById('pastorDetailModal');
    if (modalEl && modalEl.parentNode !== document.body) {
        document.body.appendChild(modalEl);
    }
});

function openPastorDetailModal(data) {
    if (!data) return;
    
    var modalEl = document.getElementById('pastorDetailModal');
    if (!modalEl) return;

    // Pastikan berada di root body
    if (modalEl.parentNode !== document.body) {
        document.body.appendChild(modalEl);
    }

    // Foto
    var defaultFoto = '{{ asset("assets/frontend/siparoki/images/default-pastor.jpg") }}';
    var fotoImg = document.getElementById('modalPastorFoto');
    var rawFoto = data.foto || (data.master_pastor ? data.master_pastor.foto : null);
    if (rawFoto) {
        if (rawFoto.startsWith('http')) {
            fotoImg.src = rawFoto;
        } else if (rawFoto.startsWith('/')) {
            fotoImg.src = rawFoto;
        } else {
            fotoImg.src = '/' + rawFoto;
        }
    } else {
        fotoImg.src = defaultFoto;
    }

    // Master Pastor or Raw data
    var mp = data.master_pastor || data;

    // Nama & Gelar
    var nama = data.nama_formatted || data.nama_lengkap_gelar || data.nama_pastor || 'Pastor';
    if (mp && mp.nama_pastor && !data.nama_formatted && !data.nama_lengkap_gelar) {
        var gDepan = mp.gelar_depan ? mp.gelar_depan.trim() + ' ' : '';
        var gBelakang = mp.gelar_belakang ? ', ' + mp.gelar_belakang.trim() : '';
        nama = gDepan + mp.nama_pastor + gBelakang;
    }
    document.getElementById('modalPastorNama').textContent = nama;

    // Jabatan & Status Badge
    var jabatan = data.jabatan || mp.jabatan || 'Pastor Paroki';
    document.getElementById('modalPastorJabatanBadge').textContent = jabatan;
    document.getElementById('modalInfoJabatan').textContent = jabatan;

    var statusStr = data.status_pelayanan || data.status || mp.status || 'Aktif';
    var isAktif = statusStr.toLowerCase().indexOf('aktif') !== -1;
    var statusBadge = document.getElementById('modalPastorStatusBadge');
    statusBadge.textContent = statusStr;
    statusBadge.style.background = isAktif ? '#10b981' : '#64748b';

    // Subtitle
    var parokiTugas = mp.paroki_tugas || data.nama_tempat_tugas || 'St. Vinsensius a Paulo - Benlutu';
    var keuskupan = mp.keuskupan || 'Keuskupan Agung Kupang';
    document.getElementById('modalPastorSub').textContent = parokiTugas + ' • ' + keuskupan;
    document.getElementById('modalInfoParoki').textContent = parokiTugas;
    document.getElementById('modalInfoKeuskupan').textContent = keuskupan;

    // Motto
    var motto = mp.motto_tahbisan || mp.motto || data.motto || '';
    var mottoBox = document.getElementById('modalMottoBox');
    if (motto && motto.trim() !== '') {
        mottoBox.style.display = 'block';
        document.getElementById('modalPastorMotto').textContent = '"' + motto.trim() + '"';
    } else {
        mottoBox.style.display = 'none';
    }

    // Jenis Imam
    var jenisImam = mp.jenis_imam || '-';
    if (mp.ordo) {
        jenisImam += ' (' + mp.ordo + ')';
    }
    document.getElementById('modalInfoJenisImam').textContent = jenisImam;

    // Periode
    var pMulai = data.periode_mulai || data.tahun_mulai || mp.periode_mulai || '-';
    var pSelesai = data.periode_selesai || data.tahun_selesai || mp.periode_selesai || (isAktif ? 'Sekarang' : '-');
    document.getElementById('modalInfoPeriode').textContent = pMulai + ' — ' + pSelesai;

    // Catatan
    var catatan = data.catatan_pelayanan || data.biografi_singkat || mp.catatan_pelayanan || mp.keterangan;
    document.getElementById('modalInfoCatatan').textContent = catatan && catatan.trim() !== '' ? catatan : 'Melayani perayaan sakramen, ekaristi, dan reksa pastoral penggembalaan umat.';

    // TTL
    var ttl = [];
    if (mp.tempat_lahir) ttl.push(mp.tempat_lahir);
    if (mp.tanggal_lahir) {
        try {
            var d = new Date(mp.tanggal_lahir);
            ttl.push(d.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }));
        } catch(e) {
            ttl.push(mp.tanggal_lahir);
        }
    }
    document.getElementById('modalInfoTTL').textContent = ttl.length ? ttl.join(', ') : '-';
    document.getElementById('modalInfoNamaBaptis').textContent = mp.nama_baptis || mp.nama_lahir || '-';

    // Tahbisan
    var tahbisan = [];
    if (mp.tempat_tahbisan) tahbisan.push(mp.tempat_tahbisan);
    if (mp.tgl_tahbisan || mp.tanggal_tahbisan) {
        try {
            var dt = new Date(mp.tgl_tahbisan || mp.tanggal_tahbisan);
            tahbisan.push(dt.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }));
        } catch(e) {
            tahbisan.push(mp.tgl_tahbisan || mp.tanggal_tahbisan);
        }
    }
    document.getElementById('modalInfoTahbisan').textContent = tahbisan.length ? tahbisan.join(', ') : '-';
    document.getElementById('modalInfoUskup').textContent = mp.uskup_penahbis || '-';

    var pend = [];
    if (mp.pendidikan_terakhir) pend.push(mp.pendidikan_terakhir);
    if (mp.seminari_tinggi) pend.push(mp.seminari_tinggi);
    document.getElementById('modalInfoPendidikan').textContent = pend.length ? pend.join(' / ') : '-';

    // Riwayat Tambahan List
    var rwBox = document.getElementById('modalRiwayatTambahanBox');
    var rwList = document.getElementById('modalRiwayatTambahanList');
    rwList.innerHTML = '';

    var rawRw = mp.riwayat_tambahan || data.riwayat_tambahan;
    var parsedRw = [];
    if (rawRw) {
        if (typeof rawRw === 'string') {
            try { parsedRw = JSON.parse(rawRw); } catch(e) { parsedRw = []; }
        } else if (Array.isArray(rawRw)) {
            parsedRw = rawRw;
        }
    }

    if (parsedRw && parsedRw.length) {
        rwBox.style.display = 'block';
        parsedRw.forEach(function(item) {
            var row = document.createElement('div');
            row.className = 'p-2 rounded bg-light border d-flex align-items-center justify-content-between gap-2';
            row.innerHTML = '<div><b class="text-dark">' + (item.tempat_tugas || item.paroki_tugas || '-') + '</b> <span class="text-muted">(' + (item.jabatan || '-') + ')</span></div>' +
                            '<span class="badge bg-secondary text-white">' + (item.periode_mulai || '-') + ' - ' + (item.periode_selesai || 'Sekarang') + '</span>';
            rwList.appendChild(row);
        });
    } else {
        rwBox.style.display = 'none';
    }

    // Trigger Bootstrap 5 Modal
    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
        var myModal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
        myModal.show();
    } else if (typeof $ !== 'undefined' && $(modalEl).modal) {
        $(modalEl).modal('show');
    }
}
</script>
