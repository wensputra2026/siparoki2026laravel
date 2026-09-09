document.addEventListener("DOMContentLoaded", function() {
    var typingText = document.querySelector('.hero-typing-text');
    if (typingText) {
        var fullText = typingText.getAttribute('data-typing-text') || typingText.textContent || '';
        var index = 0;
        typingText.textContent = '';
        function typeNextChar() {
            typingText.textContent = fullText.slice(0, index);
            index += 1;
            if (index <= fullText.length) setTimeout(typeNextChar, 45);
        }
        setTimeout(typeNextChar, 500);
    }

    // Autoplay trigger & keepalive for background YouTube iframe
    var ytIframe = document.querySelector('.hero-youtube-iframe');
    if (ytIframe) {
        function triggerYtPlay() {
            if (ytIframe && ytIframe.contentWindow) {
                try {
                    ytIframe.contentWindow.postMessage('{"event":"command","func":"playVideo","args":""}', '*');
                } catch (e) {}
            }
        }
        ytIframe.addEventListener('load', function() {
            triggerYtPlay();
            setTimeout(triggerYtPlay, 800);
            setTimeout(triggerYtPlay, 2000);
        });
        ['click', 'touchstart', 'scroll'].forEach(function(evt) {
            window.addEventListener(evt, triggerYtPlay, { once: true, passive: true });
        });
    }

    if (document.getElementById('home-map-kapela') && window.L) {
        var homeMap = L.map('home-map-kapela', {
            center: [-10.1626, 123.5796],
            zoom: 14,
            scrollWheelZoom: false
        });
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(homeMap);
        homeMap.attributionControl.setPrefix('SIPAROKI');
        fetch('/api/kapela-geojson')
            .then(function(res) { return res.json(); })
            .then(function(data) {
                if (data && data.features && data.features.length > 0) {
                    var geoLayer = L.geoJSON(data, {
                        onEachFeature: function(feature, layer) {
                            var p = feature.properties || {};
                            layer.bindPopup('<strong>' + (p.nama_stasi_kapela || 'Kapela') + '</strong><br>' + (p.alamat || ''));
                        }
                    }).addTo(homeMap);
                    var bounds = geoLayer.getBounds();
                    if (bounds.isValid()) homeMap.fitBounds(bounds, { padding: [30, 30] });
                } else {
                    L.marker([-10.1626, 123.5796]).addTo(homeMap)
                        .bindPopup('<strong>Gereja Paroki</strong><br>Fontein, Kota Kupang')
                        .openPopup();
                }
                setTimeout(function() { homeMap.invalidateSize(); }, 300);
            })
            .catch(function() {
                L.marker([-10.1626, 123.5796]).addTo(homeMap)
                    .bindPopup('<strong>Gereja Paroki</strong><br>Fontein, Kota Kupang');
                setTimeout(function() { homeMap.invalidateSize(); }, 300);
            });
    }

    // Inisialisasi Swiper Slider Pelayan Pastoral
    function initPelayanSwiper() {
        var sliderEl = document.querySelector('.swiper-pelayan-pastoral');
        if (!sliderEl) return;
        if (typeof Swiper === 'undefined') {
            setTimeout(initPelayanSwiper, 150);
            return;
        }
        var slides = sliderEl.querySelectorAll('.swiper-slide');
        var slideCount = slides.length;
        if (slideCount === 0) return;

        new Swiper('.swiper-pelayan-pastoral', {
            slidesPerView: 1,
            spaceBetween: 20,
            speed: 600,
            grabCursor: true,
            loop: slideCount > 3,
            autoplay: slideCount > 1 ? {
                delay: 4500,
                disableOnInteraction: false,
                pauseOnMouseEnter: true,
            } : false,
            pagination: {
                el: '.swiper-pagination-pelayan',
                clickable: true,
                dynamicBullets: slideCount > 4,
            },
            navigation: {
                prevEl: '.pelayan-slider-prev',
                nextEl: '.pelayan-slider-next',
            },
            breakpoints: {
                640: {
                    slidesPerView: Math.min(slideCount, 2),
                    spaceBetween: 24,
                },
                1024: {
                    slidesPerView: Math.min(slideCount, 3),
                    spaceBetween: 28,
                }
            }
        });
    }
    initPelayanSwiper();
});

