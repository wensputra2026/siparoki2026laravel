document.addEventListener('DOMContentLoaded', function() {
            const selWilayah = document.getElementById('select_wilayah');
            const selLingkungan = document.getElementById('select_lingkungan');
            const selKub = document.getElementById('select_kub');

            if (selWilayah && selLingkungan) {
                selWilayah.addEventListener('change', function() {
                    const wId = this.value;
                    Array.from(selLingkungan.options).forEach(opt => {
                        if (!opt.value) return;
                        opt.style.display = (!wId || opt.dataset.wilayah == wId) ? 'block' : 'none';
                    });
                });
            }

            if (selLingkungan && selKub) {
                selLingkungan.addEventListener('change', function() {
                    const lId = this.value;
                    Array.from(selKub.options).forEach(opt => {
                        if (!opt.value) return;
                        opt.style.display = (!lId || opt.dataset.lingkungan == lId) ? 'block' : 'none';
                    });
                });
            }
        });
