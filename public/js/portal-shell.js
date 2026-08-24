(function () {
    document.documentElement.classList.add('portal-shell-ready');

    var header = document.querySelector('.header');
    if (!header) return;

    var ticking = false;
    function updateHeaderState() {
        header.classList.toggle('is-scrolled', (window.scrollY || 0) > 8);
        ticking = false;
    }

    function requestUpdate() {
        if (ticking) return;
        ticking = true;
        window.requestAnimationFrame(updateHeaderState);
    }

    updateHeaderState();
    window.addEventListener('scroll', requestUpdate, { passive: true });

    var revealCandidates = document.querySelectorAll([
        'main section',
        '.cbt-card',
        '.counter-card',
        '.sambutan-pastor-card',
        'article',
        '[data-aos]'
    ].join(','));

    revealCandidates.forEach(function (node, index) {
        if (!node.classList.contains('portal-reveal')) {
            node.classList.add('portal-reveal');
        }
        node.style.transitionDelay = Math.min(index % 6, 5) * 45 + 'ms';
    });

    if ('IntersectionObserver' in window) {
        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting) return;
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            });
        }, { rootMargin: '0px 0px -8% 0px', threshold: 0.12 });

        revealCandidates.forEach(function (node) {
            observer.observe(node);
        });
    } else {
        revealCandidates.forEach(function (node) {
            node.classList.add('is-visible');
        });
    }
})();
