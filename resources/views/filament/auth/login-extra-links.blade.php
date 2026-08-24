<div class="mt-6 pt-5 border-t border-slate-200/80 dark:border-slate-800 text-xs">
    <div class="flex items-center justify-between gap-3">
        <a href="{{ url('/lupa-password') }}" class="text-slate-500 hover:text-amber-600 dark:text-slate-400 dark:hover:text-amber-400 font-medium transition-colors flex items-center gap-1.5 py-1" title="Lupa Kata Sandi">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="7.5" cy="15.5" r="5.5"/>
                <path d="m21 2-9.6 9.6"/>
                <path d="m15.5 7.5 3 3L22 7l-3-3"/>
            </svg>
            <span>Lupa Kata Sandi?</span>
        </a>

        <a href="{{ url('/') }}" title="Kembali ke Beranda Website" aria-label="Kembali ke Beranda Website" class="inline-flex items-center justify-center w-8 h-8 rounded-full text-slate-400 hover:text-amber-600 dark:text-slate-500 dark:hover:text-amber-400 hover:bg-slate-100 dark:hover:bg-slate-800/80 transition-all">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                <polyline points="9 22 9 12 15 12 15 22"/>
            </svg>
        </a>

        <a href="{{ url('/register') }}" class="text-amber-600 hover:text-amber-700 dark:text-amber-400 dark:hover:text-amber-300 font-semibold hover:underline transition-colors flex items-center gap-1.5 py-1">
            <span>Daftar Akun Umat</span>
            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M5 12h14"/>
                <path d="m12 5 7 7-7 7"/>
            </svg>
        </a>
    </div>
</div>
