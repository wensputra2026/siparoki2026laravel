// Dark Mode & Mobile Nav Logic
(function() {
            var navToggle = document.getElementById('navToggle');
            var darkToggle = document.getElementById('darkToggle');
            var html = document.documentElement;
            var savedTheme = localStorage.getItem('theme') || 'light';
            html.setAttribute('data-theme', savedTheme);

            if (darkToggle) {
                var icon = darkToggle.querySelector('i');
                if (icon) icon.className = savedTheme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
                darkToggle.addEventListener('click', function() {
                    var current = html.getAttribute('data-theme') || 'light';
                    var next = current === 'dark' ? 'light' : 'dark';
                    html.setAttribute('data-theme', next);
                    localStorage.setItem('theme', next);
                    if (icon) icon.className = next === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
                });
            }

            if (navToggle) {
                navToggle.addEventListener('click', function() {
                    var nav = document.getElementById('navMenu');
                    if (!nav) return;
                    nav.classList.toggle('active');
                    var icon = navToggle.querySelector('i');
                    if (icon) icon.className = nav.classList.contains('active') ? 'fas fa-times' : 'fas fa-bars';
                });
            }

            // Dropdown Click & Outside Click Management
            document.querySelectorAll('.header .nav-dropdown').forEach(function(dd) {
                var btn = dd.querySelector('.nav-dd-btn, .nav-login-btn');
                if (btn) {
                    btn.addEventListener('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        var wasActive = dd.classList.contains('active');
                        document.querySelectorAll('.header .nav-dropdown').forEach(function(other) {
                            other.classList.remove('active');
                        });
                        if (!wasActive) {
                            dd.classList.add('active');
                        }
                    });
                }
            });

            // Close dropdowns when clicking outside
            document.addEventListener('click', function(e) {
                if (!e.target.closest('.header .nav-dropdown')) {
                    document.querySelectorAll('.header .nav-dropdown').forEach(function(dd) {
                        dd.classList.remove('active');
                    });
                }
            });

            // Close mobile menu on link click
            document.querySelectorAll('.header .nav a').forEach(function(link) {
                link.addEventListener('click', function() {
                    var nav = document.getElementById('navMenu');
                    if (nav && window.innerWidth <= 991) {
                        nav.classList.remove('active');
                        var navToggle = document.getElementById('navToggle');
                        if (navToggle) {
                            var icon = navToggle.querySelector('i');
                            if (icon) icon.className = 'fas fa-bars';
                        }
                    }
                    document.querySelectorAll('.header .nav-dropdown').forEach(function(dd) {
                        dd.classList.remove('active');
                    });
                });
            });
        })();

// Back to top visibility
(function() {
            function updateBackToTop() {
                var btn = document.getElementById('backToTop');
                if (!btn) return;
                var scrolled = window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop || 0;
                if (scrolled > 200) {
                    btn.classList.add('show');
                } else {
                    btn.classList.remove('show');
                }
            }

            window.addEventListener('scroll', updateBackToTop, { passive: true });
            window.addEventListener('DOMContentLoaded', updateBackToTop);
            document.addEventListener('livewire:navigated', updateBackToTop);

            var backToTop = document.getElementById('backToTop');
            if (backToTop) {
                backToTop.addEventListener('click', function() {
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                });
            }
        })();
