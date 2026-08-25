document.addEventListener('DOMContentLoaded', function() {
    const selWilayah = document.getElementById('select_wilayah');
    const selKapela = document.getElementById('select_kapela');
    const selKub = document.getElementById('select_kub');

    function filterKub() {
        if (!selKub) return;
        const wId = selWilayah ? selWilayah.value : '';
        const kId = selKapela ? selKapela.value : '';

        Array.from(selKub.options).forEach(opt => {
            if (!opt.value) return; // Keep the placeholder
            const optWilayah = opt.dataset.wilayah || '';
            const optKapela = opt.dataset.kapela || '';

            if (!wId && !kId) {
                opt.style.display = 'block';
            } else if (wId && optWilayah == wId) {
                opt.style.display = 'block';
            } else if (kId && optKapela == kId) {
                opt.style.display = 'block';
            } else if (!optWilayah && !optKapela) {
                opt.style.display = 'block';
            } else {
                opt.style.display = 'none';
            }
        });
    }

    if (selWilayah) {
        selWilayah.addEventListener('change', filterKub);
    }
    if (selKapela) {
        selKapela.addEventListener('change', filterKub);
    }
});
