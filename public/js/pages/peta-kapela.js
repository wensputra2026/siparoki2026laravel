document.addEventListener("DOMContentLoaded", function() {
    if (!document.getElementById('full-map-kapela') || !window.L) return;
    var map = L.map('full-map-kapela', { center: [-10.1626, 123.5796], zoom: 15 });
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);
    map.attributionControl.setPrefix('SIPAROKI');

    fetch('/api/kapela-geojson')
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (data && data.features && data.features.length > 0) {
                var layer = L.geoJSON(data, {
                    onEachFeature: function(feature, itemLayer) {
                        var p = feature.properties || {};
                        itemLayer.bindPopup('<strong>' + (p.nama_stasi_kapela || 'Kapela') + '</strong><br>' + (p.alamat || ''));
                    }
                }).addTo(map);
                if (layer.getBounds().isValid()) map.fitBounds(layer.getBounds(), { padding: [40, 40] });
            } else {
                L.marker([-10.1626, 123.5796]).addTo(map).bindPopup('<strong>Gereja Paroki</strong>').openPopup();
            }
        })
        .catch(function() {
            L.marker([-10.1626, 123.5796]).addTo(map).bindPopup('<strong>Gereja Paroki</strong>').openPopup();
        });
});
