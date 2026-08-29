<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50 text-slate-800">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title inertia>{{ config('app.name', 'SIPAROKI') }}</title>

        <!-- Dynamic Favicon -->
        @if(!empty($globalLogo))
            <link rel="icon" type="image/webp" href="{{ $globalLogo }}">
            <link rel="shortcut icon" href="{{ $globalLogo }}">
        @endif

        <!-- Offline Poppins Fonts Direct Embed -->
        <style>
            @font-face {
                font-family: 'Poppins';
                font-style: normal;
                font-weight: 300;
                font-display: optional;
                src: url('{{ asset('fonts/poppins/poppins-latin-400-normal.woff2') }}') format('woff2');
            }
            @font-face {
                font-family: 'Poppins';
                font-style: normal;
                font-weight: 400;
                font-display: optional;
                src: url('{{ asset('fonts/poppins/poppins-latin-400-normal.woff2') }}') format('woff2');
            }
            @font-face {
                font-family: 'Poppins';
                font-style: normal;
                font-weight: 500;
                font-display: optional;
                src: url('{{ asset('fonts/poppins/poppins-latin-500-normal.woff2') }}') format('woff2');
            }
            @font-face {
                font-family: 'Poppins';
                font-style: normal;
                font-weight: 600;
                font-display: optional;
                src: url('{{ asset('fonts/poppins/poppins-latin-600-normal.woff2') }}') format('woff2');
            }
            @font-face {
                font-family: 'Poppins';
                font-style: normal;
                font-weight: 700;
                font-display: optional;
                src: url('{{ asset('fonts/poppins/poppins-latin-700-normal.woff2') }}') format('woff2');
            }
            @font-face {
                font-family: 'Poppins';
                font-style: normal;
                font-weight: 800;
                font-display: optional;
                src: url('{{ asset('fonts/poppins/poppins-latin-800-normal.woff2') }}') format('woff2');
            }
            @font-face {
                font-family: 'Poppins';
                font-style: normal;
                font-weight: 900;
                font-display: optional;
                src: url('{{ asset('fonts/poppins/poppins-latin-900-normal.woff2') }}') format('woff2');
            }

            :root {
                --font-sans: 'Poppins', sans-serif !important;
                --default-font-family: 'Poppins', sans-serif !important;
            }

            body, html, .font-sans, p, h1, h2, h3, h4, h5, h6, span, a, button, input, select, textarea, div, label, td, th {
                font-family: 'Poppins', sans-serif !important;
            }

            /* Icon fonts MUST NEVER be overridden by Poppins */
            .fa, .fas, .far, .fab, .fa-solid, .fa-regular, .fa-brands, [class^="fa-"], [class*=" fa-"], i.fa, i.fas, i.far, i.fab, i.fa-solid, i.fa-regular, i.fa-brands {
                font-family: "Font Awesome 7 Free" !important;
            }
            .fab, .fa-brands, i.fab, i.fa-brands {
                font-family: "Font Awesome 7 Brands" !important;
            }
            .bi, [class^="bi-"], [class*=" bi-"] {
                font-family: "bootstrap-icons" !important;
            }

            /* Inertia Modern Centered Spinner & Top Loading Bar */
            #nprogress {
                pointer-events: none;
            }
            #nprogress .bar {
                background: linear-gradient(90deg, #f59e0b, #0ea5e9, #10b981) !important;
                position: fixed;
                z-index: 99999;
                top: 0;
                left: 0;
                width: 100%;
                height: 3px !important;
                box-shadow: 0 0 10px #f59e0b, 0 0 5px #0ea5e9;
            }
            #nprogress .peg {
                display: block;
                position: absolute;
                right: 0px;
                width: 100px;
                height: 100%;
                box-shadow: 0 0 12px #f59e0b, 0 0 6px #f59e0b;
                opacity: 1.0;
                transform: rotate(3deg) translate(0px, -4px);
            }
            #nprogress .spinner {
                display: flex !important;
                align-items: center;
                justify-content: center;
                position: fixed;
                z-index: 99999;
                top: 50% !important;
                left: 50% !important;
                right: auto !important;
                bottom: auto !important;
                transform: translate(-50%, -50%) !important;
                width: 58px;
                height: 58px;
                background: rgba(255, 255, 255, 0.94);
                backdrop-filter: blur(8px);
                -webkit-backdrop-filter: blur(8px);
                border: 1px solid rgba(226, 232, 240, 0.95);
                border-radius: 18px;
                box-shadow: 0 12px 30px -4px rgba(0, 0, 0, 0.14), 0 6px 12px -4px rgba(0, 0, 0, 0.08);
                pointer-events: none;
            }
            #nprogress .spinner-icon {
                width: 28px !important;
                height: 28px !important;
                box-sizing: border-box;
                border: solid 3px transparent !important;
                border-top-color: #f59e0b !important;
                border-right-color: #0ea5e9 !important;
                border-bottom-color: #10b981 !important;
                border-radius: 50% !important;
                animation: nprogress-spinner 600ms linear infinite !important;
            }
            @keyframes nprogress-spinner {
                0%   { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
        </style>

        <!-- Local Font Awesome -->
        <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @inertiaHead
    </head>
    <body class="h-full font-sans antialiased bg-slate-50 text-slate-800 selection:bg-amber-500 selection:text-white">
        @inertia
    </body>
</html>
