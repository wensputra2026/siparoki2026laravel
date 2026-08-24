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

        <!-- Offline Poppins Fonts Preload -->
        <link rel="preload" href="/fonts/poppins/poppins-latin-400-normal.woff2" as="font" type="font/woff2" crossorigin>
        <link rel="preload" href="/fonts/poppins/poppins-latin-600-normal.woff2" as="font" type="font/woff2" crossorigin>
        <link rel="preload" href="/fonts/poppins/poppins-latin-700-normal.woff2" as="font" type="font/woff2" crossorigin>

        <!-- Offline Poppins Fonts Direct Embed -->
        <style>
            @font-face {
                font-family: 'Poppins';
                font-style: normal;
                font-weight: 300;
                font-display: swap;
                src: url('/fonts/poppins/poppins-latin-400-normal.woff2') format('woff2');
            }
            @font-face {
                font-family: 'Poppins';
                font-style: normal;
                font-weight: 400;
                font-display: swap;
                src: url('/fonts/poppins/poppins-latin-400-normal.woff2') format('woff2');
            }
            @font-face {
                font-family: 'Poppins';
                font-style: normal;
                font-weight: 500;
                font-display: swap;
                src: url('/fonts/poppins/poppins-latin-500-normal.woff2') format('woff2');
            }
            @font-face {
                font-family: 'Poppins';
                font-style: normal;
                font-weight: 600;
                font-display: swap;
                src: url('/fonts/poppins/poppins-latin-600-normal.woff2') format('woff2');
            }
            @font-face {
                font-family: 'Poppins';
                font-style: normal;
                font-weight: 700;
                font-display: swap;
                src: url('/fonts/poppins/poppins-latin-700-normal.woff2') format('woff2');
            }
            @font-face {
                font-family: 'Poppins';
                font-style: normal;
                font-weight: 800;
                font-display: swap;
                src: url('/fonts/poppins/poppins-latin-800-normal.woff2') format('woff2');
            }
            @font-face {
                font-family: 'Poppins';
                font-style: normal;
                font-weight: 900;
                font-display: swap;
                src: url('/fonts/poppins/poppins-latin-900-normal.woff2') format('woff2');
            }

            body, html {
                font-family: 'Poppins', ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
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
