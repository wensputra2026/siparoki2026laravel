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
                src: url('/fonts/poppins/poppins-latin-400-normal.woff2') format('woff2');
            }
            @font-face {
                font-family: 'Poppins';
                font-style: normal;
                font-weight: 400;
                font-display: optional;
                src: url('/fonts/poppins/poppins-latin-400-normal.woff2') format('woff2');
            }
            @font-face {
                font-family: 'Poppins';
                font-style: normal;
                font-weight: 500;
                font-display: optional;
                src: url('/fonts/poppins/poppins-latin-500-normal.woff2') format('woff2');
            }
            @font-face {
                font-family: 'Poppins';
                font-style: normal;
                font-weight: 600;
                font-display: optional;
                src: url('/fonts/poppins/poppins-latin-600-normal.woff2') format('woff2');
            }
            @font-face {
                font-family: 'Poppins';
                font-style: normal;
                font-weight: 700;
                font-display: optional;
                src: url('/fonts/poppins/poppins-latin-700-normal.woff2') format('woff2');
            }
            @font-face {
                font-family: 'Poppins';
                font-style: normal;
                font-weight: 800;
                font-display: optional;
                src: url('/fonts/poppins/poppins-latin-800-normal.woff2') format('woff2');
            }
            @font-face {
                font-family: 'Poppins';
                font-style: normal;
                font-weight: 900;
                font-display: optional;
                src: url('/fonts/poppins/poppins-latin-900-normal.woff2') format('woff2');
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
