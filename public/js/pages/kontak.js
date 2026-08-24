document.addEventListener("DOMContentLoaded", function() {
    var el = document.getElementById('contact-map');
    if (!el || !window.L) return;
    var title = el.getAttribute('data-title') || 'Paroki';
    var address = el.getAttribute('data-address') || '';
    var lat = parseFloat(el.getAttribute('data-lat'));
    var lng = parseFloat(el.getAttribute('data-lng'));
    var center = Number.isFinite(lat) && Number.isFinite(lng) ? [lat, lng] : [-10.1626, 123.5796];
    var map = L.map(el, { center: center, zoom: 16 });
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);
    map.attributionControl.setPrefix('SIPAROKI');
    L.marker(center).addTo(map).bindPopup('<strong>' + title + '</strong><br>' + address).openPopup();
});
