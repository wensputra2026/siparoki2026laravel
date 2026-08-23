@extends('layouts.app')
@section('title', 'Peta Wilayah Stasi & Kapela - Kristus Raja Katedral / Bonipoi')
@section('content')
<div class="py-8 bg-slate-100 dark:bg-[#090e1a]">
    <div class="max-w-7xl mx-auto px-4">
        <div class="flex items-center justify-between mb-6">
            <div>
                <span class="st-badge mb-1"><i class="fas fa-map-marked-alt me-1"></i> WebGIS</span>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Peta Wilayah Stasi &amp; Kapela (Layar Penuh)</h1>
            </div>
            <a href="/" wire:navigate class="text-sm font-semibold text-sky-600 hover:text-sky-700">&larr; Kembali ke Beranda</a>
        </div>
        <div class="bg-white dark:bg-[#101d31] rounded-3xl overflow-hidden border border-slate-200 dark:border-[#263a55] shadow-lg">
            <div id="full-map-kapela" style="height: 650px; width: 100%;"></div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    if (document.getElementById('full-map-kapela')) {
        var map = L.map('full-map-kapela', { center: [-10.1626, 123.5796], zoom: 15 });
        L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; CartoDB &copy; OpenStreetMap',
            maxZoom: 19
        }).addTo(map);

        fetch('/api/kapela-geojson')
            .then(function(res) { return res.json(); })
            .then(function(data) {
                if (data && data.features && data.features.length > 0) {
                    var l = L.geoJSON(data, {
                        onEachFeature: function(f, layer) {
                            var p = f.properties;
                            layer.bindPopup('<strong>' + p.nama_stasi_kapela + '</strong><br>' + p.alamat);
                        }
                    }).addTo(map);
                    if (l.getBounds().isValid()) map.fitBounds(l.getBounds(), { padding: [40, 40] });
                } else {
                    L.marker([-10.1626, 123.5796]).addTo(map).bindPopup('<strong>Gereja Katedral Kristus Raja</strong>').openPopup();
                }
            })
            .catch(function() {
                L.marker([-10.1626, 123.5796]).addTo(map).bindPopup('<strong>Gereja Katedral Kristus Raja</strong>').openPopup();
            });
    }
});
</script>
@endsection
