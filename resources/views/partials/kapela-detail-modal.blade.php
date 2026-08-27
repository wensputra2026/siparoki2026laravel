<!-- Universal Kapela Detail Modal (Single Dynamic Modal, Clean & Sharp, Direct Body Mount) -->
<style>
#kapelaDetailModal {
    z-index: 1060 !important;
}
.modal-backdrop {
    z-index: 1050 !important;
    background-color: #0f172a !important;
}
.modal-backdrop.show {
    opacity: 0.45 !important;
}
#kapelaDetailModal .modal-dialog {
    max-width: 680px;
    margin: 1.75rem auto;
}
@media (max-width: 768px) {
    #kapelaDetailModal .modal-dialog {
        margin: 0.75rem auto;
        max-width: calc(100% - 1.5rem);
    }
    #kapelaDetailModal .modal-header {
        padding: 20px 16px 16px !important;
    }
    #kapelaDetailModal .modal-body {
        padding: 16px !important;
    }
}
#kapelaDetailModal .modal-content {
    border: none;
    border-radius: 22px;
    overflow: hidden;
    box-shadow: 0 25px 60px rgba(0, 0, 0, 0.35);
    background: #ffffff;
}
#kapelaDetailModal .info-block {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 10px 14px;
}
#kapelaDetailModal .info-label {
    font-size: 0.72rem;
    font-weight: 700;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    margin-bottom: 2px;
    display: block;
}
#kapelaDetailModal .info-value {
    font-size: 0.88rem;
    font-weight: 700;
    color: #0f172a;
    line-height: 1.35;
}
</style>

<div class="modal fade" id="kapelaDetailModal" tabindex="-1" aria-labelledby="kapelaDetailModalLabel" aria-hidden="true" data-bs-backdrop="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            
            <!-- Modal Header -->
            <div class="modal-header border-0 text-white position-relative" style="background: linear-gradient(135deg, #00897b 0%, #00695c 60%, #004d40 100%); padding: 22px 24px 18px;">
                <button type="button" class="btn-close btn-close-white position-absolute" data-bs-dismiss="modal" aria-label="Close" style="top: 18px; right: 18px; opacity: 0.9; cursor: pointer;"></button>

                <div class="d-flex align-items-center gap-3 w-100 pe-4">
                    <div id="modalKapelaIconWrap" style="width: 64px; height: 64px; border-radius: 16px; overflow: hidden; border: 3px solid rgba(255,255,255,0.9); box-shadow: 0 6px 14px rgba(0,0,0,0.18); background: #ffffff; flex-shrink: 0; display: flex; align-items: center; justify-content: center; color: #00897b; font-size: 1.6rem;">
                        <i class="fas fa-church"></i>
                    </div>
                    <div style="flex: 1; min-width: 0;">
                        <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                            <span id="modalKapelaTipeBadge" class="badge" style="background: rgba(255, 255, 255, 0.25); backdrop-filter: blur(4px); font-size: 0.72rem; font-weight: 700; text-transform: uppercase; padding: 4px 10px; border-radius: 20px; letter-spacing: 0.5px;">
                                Stasi / Kapela
                            </span>
                            <span id="modalKapelaStatusBadge" class="badge" style="background: #10b981; color: #ffffff; font-size: 0.7rem; font-weight: 700; padding: 4px 10px; border-radius: 20px;">
                                Aktif
                            </span>
                            <span id="modalKapelaKodeBadge" class="badge bg-white-50 text-white" style="font-size: 0.7rem; display: none;">
                                -
                            </span>
                        </div>
                        <h4 id="modalKapelaNama" class="modal-title mb-1 text-white text-truncate" style="font-size: 1.25rem; font-weight: 800; line-height: 1.3;">
                            Nama Stasi / Kapela
                        </h4>
                        <p id="modalKapelaSub" class="mb-0 text-white-50 text-truncate" style="font-size: 0.8rem;">
                            Paroki St. Vinsensius a Paulo - Benlutu
                        </p>
                    </div>
                </div>
            </div>

            <!-- Modal Body (Scrollable) -->
            <div class="modal-body p-4" style="background: #fdfdfd;">
                <div class="d-flex flex-column gap-3">
                    
                    <!-- Foto Banner (Optional) -->
                    <div id="modalKapelaFotoWrap" style="display: none; border-radius: 14px; overflow: hidden; max-height: 240px; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
                        <img id="modalKapelaFotoImg" src="" alt="Foto Kapela" style="width: 100%; height: 220px; object-fit: cover; display: block;">
                    </div>

                    <!-- Informasi Pokok -->
                    <div class="bg-white border rounded-3 p-3 shadow-2xs">
                        <h6 class="text-uppercase fw-bold mb-2.5 d-flex align-items-center gap-2" style="color: #00897b; font-size: 0.8rem; letter-spacing: 0.5px;">
                            <i class="fa-solid fa-cross"></i> Informasi Utama &amp; Pelindung
                        </h6>
                        <div class="row g-2">
                            <div class="col-sm-6 col-12">
                                <div class="info-block">
                                    <span class="info-label">Santo / Santa Pelindung</span>
                                    <span id="modalInfoPelindung" class="info-value" style="color: #00897b;">-</span>
                                </div>
                            </div>
                            <div class="col-sm-6 col-12">
                                <div class="info-block">
                                    <span class="info-label">Penanggung Jawab / Ketua</span>
                                    <span id="modalInfoPJ" class="info-value">-</span>
                                </div>
                            </div>
                            <div class="col-sm-6 col-12">
                                <div class="info-block">
                                    <span class="info-label">Jenis / Status Teritori</span>
                                    <span id="modalInfoTipe" class="info-value">Stasi / Kapela</span>
                                </div>
                            </div>
                            <div class="col-sm-6 col-12">
                                <div class="info-block">
                                    <span class="info-label">Status Pelayanan</span>
                                    <span id="modalInfoStatus" class="info-value text-success">Aktif</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Lokasi & Wilayah Administratif -->
                    <div class="bg-white border rounded-3 p-3 shadow-2xs">
                        <h6 class="text-uppercase fw-bold mb-2.5 d-flex align-items-center gap-2" style="color: #00897b; font-size: 0.8rem; letter-spacing: 0.5px;">
                            <i class="fa-solid fa-map-location-dot"></i> Lokasi &amp; Wilayah Pelayanan
                        </h6>
                        <div class="row g-2">
                            <div class="col-12" id="modalAlamatWrap">
                                <div class="info-block">
                                    <span class="info-label">Alamat / Lokasi Gereja</span>
                                    <span id="modalInfoAlamat" class="info-value fw-normal text-secondary">-</span>
                                </div>
                            </div>
                            <div class="col-sm-6 col-md-3 col-6">
                                <div class="info-block">
                                    <span class="info-label">Desa / Kelurahan</span>
                                    <span id="modalInfoDesa" class="info-value">-</span>
                                </div>
                            </div>
                            <div class="col-sm-6 col-md-3 col-6">
                                <div class="info-block">
                                    <span class="info-label">Kecamatan</span>
                                    <span id="modalInfoKecamatan" class="info-value">-</span>
                                </div>
                            </div>
                            <div class="col-sm-6 col-md-3 col-6">
                                <div class="info-block">
                                    <span class="info-label">Kabupaten</span>
                                    <span id="modalInfoKabupaten" class="info-value">-</span>
                                </div>
                            </div>
                            <div class="col-sm-6 col-md-3 col-6">
                                <div class="info-block">
                                    <span class="info-label">Provinsi</span>
                                    <span id="modalInfoProvinsi" class="info-value">-</span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-2.5 pt-2 border-top d-flex gap-2 align-items-center flex-wrap">
                            <a id="modalMapsLink" href="#" target="_blank" rel="noopener noreferrer" class="btn btn-sm text-white px-3 py-1.5 fw-bold d-inline-flex align-items-center gap-2" style="background: #00897b; border-radius: 10px; font-size: 0.8rem; display: none;">
                                <i class="fas fa-location-arrow"></i> Petunjuk Arah (Google Maps)
                            </a>
                            <a href="/peta-kapela" class="btn btn-sm btn-outline-secondary px-3 py-1.5 fw-bold d-inline-flex align-items-center gap-2" style="border-radius: 10px; font-size: 0.8rem;">
                                <i class="fas fa-map-marked-alt"></i> Peta Interaktif Paroki
                            </a>
                        </div>
                    </div>

                    <!-- Sejarah & Latar Belakang (Conditional) -->
                    <div id="modalSejarahSection" style="display: none;" class="bg-white border rounded-3 p-3 shadow-2xs">
                        <h6 class="text-uppercase fw-bold mb-2 d-flex align-items-center gap-2" style="color: #00897b; font-size: 0.8rem; letter-spacing: 0.5px;">
                            <i class="fa-solid fa-book-bookmark"></i> Sejarah &amp; Latar Belakang
                        </h6>
                        <p id="modalInfoSejarah" class="mb-0 text-secondary" style="font-size: 0.86rem; line-height: 1.6; white-space: pre-line;"></p>
                    </div>

                    <!-- Visi & Misi (Conditional) -->
                    <div id="modalVisiMisiSection" style="display: none;" class="bg-white border rounded-3 p-3 shadow-2xs">
                        <h6 class="text-uppercase fw-bold mb-2.5 d-flex align-items-center gap-2" style="color: #00897b; font-size: 0.8rem; letter-spacing: 0.5px;">
                            <i class="fa-solid fa-bullseye"></i> Visi &amp; Misi Stasi
                        </h6>
                        <div class="row g-2">
                            <div class="col-md-6 col-12" id="modalVisiWrap" style="display: none;">
                                <div class="info-block h-100">
                                    <span class="info-label text-teal" style="color: #00897b;">Visi</span>
                                    <p id="modalInfoVisi" class="mb-0 text-secondary small" style="line-height: 1.5; white-space: pre-line;"></p>
                                </div>
                            </div>
                            <div class="col-md-6 col-12" id="modalMisiWrap" style="display: none;">
                                <div class="info-block h-100">
                                    <span class="info-label text-teal" style="color: #00897b;">Misi</span>
                                    <p id="modalInfoMisi" class="mb-0 text-secondary small" style="line-height: 1.5; white-space: pre-line;"></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Informasi Tambahan (Conditional) -->
                    <div id="modalKetSection" style="display: none;" class="p-3 rounded-3" style="background: #f0fdfa; border: 1px solid #ccfbf1;">
                        <span class="d-block fw-bold mb-1" style="color: #0f766e; font-size: 0.78rem;">
                            <i class="fas fa-circle-info me-1"></i> Informasi Tambahan
                        </span>
                        <p id="modalInfoKeterangan" class="mb-0 text-secondary small" style="line-height: 1.5;"></p>
                    </div>

                </div>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer bg-light border-top px-4 py-2.5 d-flex align-items-center justify-content-between">
                <span class="text-muted fw-semibold" style="font-size: 0.76rem;">
                    <i class="fa-solid fa-circle-check text-success me-1"></i> Data Resmi Stasi Paroki
                </span>
                <button type="button" class="btn text-white px-4 py-2 fw-bold" data-bs-dismiss="modal" style="background: #00897b; border-radius: 12px; font-size: 0.82rem;">
                    Tutup
                </button>
            </div>

        </div>
    </div>
</div>

<script>
// Pastikan modal terpasang langsung di document.body agar tidak tertutup backdrop
document.addEventListener('DOMContentLoaded', function() {
    var modalEl = document.getElementById('kapelaDetailModal');
    if (modalEl && modalEl.parentNode !== document.body) {
        document.body.appendChild(modalEl);
    }
});

function openKapelaDetailModal(data) {
    if (!data) return;
    
    var modalEl = document.getElementById('kapelaDetailModal');
    if (!modalEl) return;

    if (modalEl.parentNode !== document.body) {
        document.body.appendChild(modalEl);
    }

    // Nama & Tipe
    var nama = data.nama_stasi_kapela || data.nama_kapela || data.nama_stasi || data.nama || 'Gereja Stasi / Kapela';
    var tipe = data.tipe || 'Stasi / Kapela';
    var kode = data.kode_stasi_kapela || data.kode_kapela || null;

    document.getElementById('modalKapelaNama').textContent = nama;
    document.getElementById('modalKapelaTipeBadge').textContent = tipe;
    document.getElementById('modalInfoTipe').textContent = tipe;

    // Status Badge
    var rawStatus = data.status;
    var statusStr = 'Aktif';
    if (rawStatus !== null && rawStatus !== undefined) {
        if (rawStatus === 1 || rawStatus === '1' || String(rawStatus).toLowerCase().indexOf('aktif') !== -1) {
            statusStr = 'Aktif';
        } else if (rawStatus === 0 || rawStatus === '0') {
            statusStr = 'Non-Aktif';
        } else {
            statusStr = String(rawStatus);
        }
    }
    var isAktif = statusStr === 'Aktif' || String(statusStr).toLowerCase().indexOf('aktif') !== -1;
    var statusBadge = document.getElementById('modalKapelaStatusBadge');
    statusBadge.textContent = statusStr;
    statusBadge.style.background = isAktif ? '#10b981' : '#64748b';
    document.getElementById('modalInfoStatus').textContent = statusStr;
    document.getElementById('modalInfoStatus').className = 'info-value ' + (isAktif ? 'text-success' : 'text-secondary');

    // Kode Badge
    var kodeBadge = document.getElementById('modalKapelaKodeBadge');
    if (kode) {
        kodeBadge.style.display = 'inline-block';
        kodeBadge.textContent = 'Kode: ' + kode;
    } else {
        kodeBadge.style.display = 'none';
    }

    // Pelindung & PJ
    var pelindung = data.nama_pelindung || data.pelindung || '-';
    document.getElementById('modalInfoPelindung').textContent = pelindung;

    var pj = data.penanggung_jawab || '-';
    document.getElementById('modalInfoPJ').textContent = pj;

    // Alamat & Wilayah
    var alamat = data.alamat || data.lokasi || '-';
    document.getElementById('modalInfoAlamat').textContent = alamat;
    document.getElementById('modalInfoDesa').textContent = data.nama_desa || '-';
    document.getElementById('modalInfoKecamatan').textContent = data.nama_kecamatan || '-';
    document.getElementById('modalInfoKabupaten').textContent = data.nama_kabupaten || '-';
    document.getElementById('modalInfoProvinsi').textContent = data.nama_provinsi || '-';

    // Google Maps Link
    var mapsLink = document.getElementById('modalMapsLink');
    var mapsUrl = data.maps_url;
    if (!mapsUrl && data.latitude && data.longitude) {
        mapsUrl = 'https://www.google.com/maps?q=' + data.latitude + ',' + data.longitude;
    }
    if (mapsUrl) {
        mapsLink.style.display = 'inline-flex';
        mapsLink.href = mapsUrl;
    } else {
        mapsLink.style.display = 'none';
    }

    // Foto
    var fotoWrap = document.getElementById('modalKapelaFotoWrap');
    var fotoImg = document.getElementById('modalKapelaFotoImg');
    var foto = data.foto;
    if (foto) {
        var fotoUrl = foto;
        if (!foto.startsWith('http') && !foto.startsWith('/')) {
            fotoUrl = '/' + foto;
        }
        fotoImg.src = fotoUrl;
        fotoWrap.style.display = 'block';
    } else {
        fotoWrap.style.display = 'none';
    }

    // Sejarah
    var sejSec = document.getElementById('modalSejarahSection');
    var sejTxt = document.getElementById('modalInfoSejarah');
    var sejarahStr = String(data.sejarah || '').trim();
    if (sejarahStr !== '') {
        sejSec.style.display = 'block';
        sejTxt.textContent = sejarahStr;
    } else {
        sejSec.style.display = 'none';
    }

    // Visi & Misi
    var vmSec = document.getElementById('modalVisiMisiSection');
    var vWrap = document.getElementById('modalVisiWrap');
    var mWrap = document.getElementById('modalMisiWrap');
    var hasVm = false;

    var visiStr = String(data.visi || '').trim();
    if (visiStr !== '') {
        vWrap.style.display = 'block';
        document.getElementById('modalInfoVisi').textContent = visiStr;
        hasVm = true;
    } else {
        vWrap.style.display = 'none';
    }

    var misiStr = String(data.misi || '').trim();
    if (misiStr !== '') {
        mWrap.style.display = 'block';
        document.getElementById('modalInfoMisi').textContent = misiStr;
        hasVm = true;
    } else {
        mWrap.style.display = 'none';
    }
    vmSec.style.display = hasVm ? 'block' : 'none';

    // Keterangan
    var ketSec = document.getElementById('modalKetSection');
    var ketStr = String(data.keterangan || '').trim();
    if (ketStr !== '') {
        ketSec.style.display = 'block';
        document.getElementById('modalInfoKeterangan').textContent = ketStr;
    } else {
        ketSec.style.display = 'none';
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
