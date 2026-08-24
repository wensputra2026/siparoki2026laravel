(function () {
    var html = document.documentElement;

    function setThemeIcon() {
        var darkToggle = document.getElementById('darkToggle');
        if (!darkToggle) return;
        var icon = darkToggle.querySelector('i');
        if (!icon) return;
        icon.className = html.getAttribute('data-theme') === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
    }

    function initCounters() {
        var counters = document.querySelectorAll('.counter[data-target], [data-counter-target]');
        if (!counters.length) return;

        function animate(counter) {
            var raw = counter.getAttribute('data-target') || counter.getAttribute('data-counter-target') || '0';
            var target = parseInt(raw, 10);
            if (!Number.isFinite(target)) return;
            var duration = 1800;
            var startTime = null;

            function tick(now) {
                if (!startTime) startTime = now;
                var progress = Math.min((now - startTime) / duration, 1);
                counter.textContent = Math.floor(target * progress).toLocaleString('id-ID');
                if (progress < 1) window.requestAnimationFrame(tick);
            }

            window.requestAnimationFrame(tick);
        }

        if ('IntersectionObserver' in window) {
            var observer = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (!entry.isIntersecting) return;
                    animate(entry.target);
                    observer.unobserve(entry.target);
                });
            }, { threshold: 0.5 });

            counters.forEach(function (counter) {
                observer.observe(counter);
            });
        } else {
            counters.forEach(animate);
        }
    }

    function initLightbox() {
        var modal = document.getElementById('lightboxModal');
        var modalImg = document.getElementById('modalImage');
        var close = document.querySelector('.lightbox-close');
        if (!modal || !modalImg || !close) return;

        document.querySelectorAll('[data-lightbox]').forEach(function (item) {
            item.addEventListener('click', function (event) {
                event.preventDefault();
                modal.classList.add('active');
                modalImg.src = item.getAttribute('href') || item.getAttribute('data-src') || '';
                document.body.style.overflow = 'hidden';
            });
        });

        function closeModal() {
            modal.classList.remove('active');
            document.body.style.overflow = '';
            modalImg.removeAttribute('src');
        }

        close.addEventListener('click', closeModal);
        modal.addEventListener('click', function (event) {
            if (event.target === modal) closeModal();
        });
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && modal.classList.contains('active')) closeModal();
        });
    }

    function initReveal() {
        var revealEls = document.querySelectorAll([
            '.programs-card',
            '.news-card',
            '.gallery-item',
            '.counter-card',
            '.about-grid',
            '.contact-grid',
            '.hero-card',
            '.carousel-wrap',
            '.achievement-card',
            '.extracurricular-card',
            '.testimonial-card',
            '.event-card',
            '.faq-item',
            '.jm-card',
            '.cbt-card',
            'main > section'
        ].join(','));

        if (!revealEls.length) return;

        revealEls.forEach(function (el, index) {
            if (el.classList.contains('portal-reveal')) return;
            el.style.opacity = '0';
            el.style.transform = 'translateY(24px)';
            el.style.transition = 'opacity 0.5s ease ' + (index % 3 * 0.08) + 's, transform 0.5s ease ' + (index % 3 * 0.08) + 's';
        });

        if ('IntersectionObserver' in window) {
            var observer = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (!entry.isIntersecting) return;
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                    observer.unobserve(entry.target);
                });
            }, { threshold: 0.06 });

            revealEls.forEach(function (el) {
                observer.observe(el);
            });
        } else {
            revealEls.forEach(function (el) {
                el.style.opacity = '1';
                el.style.transform = 'translateY(0)';
            });
        }
    }

    function initBackToTop() {
        var button = document.getElementById('backToTop');
        if (!button) return;

        function update() {
            button.classList.toggle('show', (window.scrollY || document.documentElement.scrollTop || 0) > 400);
        }

        button.addEventListener('click', function () {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
        window.addEventListener('scroll', update, { passive: true });
        update();
    }

    function init() {
        setThemeIcon();
        initCounters();
        initLightbox();
        initReveal();
        initBackToTop();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    document.addEventListener('livewire:navigated', init);
})();
