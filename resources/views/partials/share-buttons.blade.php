@php
    $currentShareUrl = url()->current();
    $currentShareTitle = $title ?? ($item->judul ?? ($globalNamaParoki ?? 'SIPAROKI'));
    $waText = rawurlencode($currentShareTitle . "\n" . $currentShareUrl);
    $fbUrl = 'https://www.facebook.com/sharer/sharer.php?u=' . urlencode($currentShareUrl);
    $twUrl = 'https://twitter.com/intent/tweet?text=' . urlencode($currentShareTitle) . '&url=' . urlencode($currentShareUrl);
    $tgUrl = 'https://t.me/share/url?url=' . urlencode($currentShareUrl) . '&text=' . urlencode($currentShareTitle);
    $waUrl = 'https://api.whatsapp.com/send?text=' . $waText;
@endphp

<div class="share-box-container my-4 p-3 rounded-4 shadow-xs" style="background: #ffffff; border: 1px solid #e2e8f0;">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
            <span class="d-inline-flex align-items-center justify-content-center text-white rounded-circle shadow-xs" style="width: 32px; height: 32px; background: linear-gradient(135deg, var(--primary-teal, #00897b), #00695c); font-size: 0.85rem;">
                <i class="fa-solid fa-share-nodes"></i>
            </span>
            <span class="fw-bold" style="color: #334155; font-size: 0.88rem;">Bagikan:</span>
        </div>

        <div class="d-flex align-items-center gap-2">
            <!-- WhatsApp -->
            <a href="{{ $waUrl }}" target="_blank" rel="noopener noreferrer" class="share-icon-btn" style="width: 36px; height: 36px; border-radius: 50%; background: #25D366; color: #ffffff; display: inline-flex; align-items: center; justify-content: center; font-size: 1rem; text-decoration: none; box-shadow: 0 2px 6px rgba(37, 211, 102, 0.25); transition: transform 0.2s, box-shadow 0.2s;" title="Bagikan ke WhatsApp" aria-label="WhatsApp" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                <i class="fa-brands fa-whatsapp"></i>
            </a>

            <!-- Facebook -->
            <a href="{{ $fbUrl }}" target="_blank" rel="noopener noreferrer" class="share-icon-btn" style="width: 36px; height: 36px; border-radius: 50%; background: #1877F2; color: #ffffff; display: inline-flex; align-items: center; justify-content: center; font-size: 0.95rem; text-decoration: none; box-shadow: 0 2px 6px rgba(24, 119, 242, 0.25); transition: transform 0.2s, box-shadow 0.2s;" title="Bagikan ke Facebook" aria-label="Facebook" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                <i class="fa-brands fa-facebook-f"></i>
            </a>

            <!-- Telegram -->
            <a href="{{ $tgUrl }}" target="_blank" rel="noopener noreferrer" class="share-icon-btn" style="width: 36px; height: 36px; border-radius: 50%; background: #229ED9; color: #ffffff; display: inline-flex; align-items: center; justify-content: center; font-size: 0.95rem; text-decoration: none; box-shadow: 0 2px 6px rgba(34, 158, 217, 0.25); transition: transform 0.2s, box-shadow 0.2s;" title="Bagikan ke Telegram" aria-label="Telegram" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                <i class="fa-brands fa-telegram"></i>
            </a>

            <!-- X / Twitter -->
            <a href="{{ $twUrl }}" target="_blank" rel="noopener noreferrer" class="share-icon-btn" style="width: 36px; height: 36px; border-radius: 50%; background: #0f172a; color: #ffffff; display: inline-flex; align-items: center; justify-content: center; font-size: 0.9rem; text-decoration: none; box-shadow: 0 2px 6px rgba(15, 23, 42, 0.25); transition: transform 0.2s, box-shadow 0.2s;" title="Bagikan ke X (Twitter)" aria-label="X (Twitter)" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                <i class="fa-brands fa-x-twitter"></i>
            </a>

            <!-- Salin Tautan -->
            <button type="button" onclick="copyShareLink(this)" data-url="{{ $currentShareUrl }}" class="share-icon-btn" style="width: 36px; height: 36px; border-radius: 50%; background: #f1f5f9; color: #334155; border: 1px solid #cbd5e1; display: inline-flex; align-items: center; justify-content: center; font-size: 0.9rem; cursor: pointer; transition: all 0.2s;" title="Salin Tautan" aria-label="Salin Tautan" onmouseover="this.style.transform='translateY(-2px)'; this.style.borderColor='var(--primary-teal, #00897b)';" onmouseout="this.style.transform='translateY(0)'; if(!this.classList.contains('copied')) this.style.borderColor='#cbd5e1';">
                <i class="fa-regular fa-copy"></i>
            </button>
        </div>
    </div>
</div>

<script>
if (typeof copyShareLink === 'undefined') {
    function copyShareLink(btn) {
        const url = btn.getAttribute('data-url') || window.location.href;
        navigator.clipboard.writeText(url).then(() => {
            btn.classList.add('copied');
            btn.style.background = '#059669';
            btn.style.color = '#ffffff';
            btn.style.borderColor = '#059669';
            btn.innerHTML = '<i class="fa-solid fa-check"></i>';
            btn.setAttribute('title', 'Tersalin!');
            setTimeout(() => {
                btn.classList.remove('copied');
                btn.innerHTML = '<i class="fa-regular fa-copy"></i>';
                btn.style.background = '#f1f5f9';
                btn.style.color = '#334155';
                btn.style.borderColor = '#cbd5e1';
                btn.setAttribute('title', 'Salin Tautan');
            }, 2000);
        }).catch(() => {
            prompt('Salin tautan ini:', url);
        });
    }
}
</script>
