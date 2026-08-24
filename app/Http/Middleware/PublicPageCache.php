<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class PublicPageCache
{
    private const TTL_SECONDS = 300;

    private array $excludedPrefixes = [
        'admin',
        'superadmin',
        'paroki',
        'pastor',
        'wilayah',
        'kapela',
        'kub',
        'bendahara',
        'penulis',
        'umat',
        'v2',
        'api',
        'login',
        'masuk',
        'register',
        'daftar',
        'lupa-password',
        'forgot-password',
        'logout',
        'kontak',
        'sakramen',
        'pengajuan-sakramen',
        'downloads/*/unduh',
        'downloads/arsip/*/unduh',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if (!$this->shouldCache($request)) {
            return $next($request);
        }

        $key = 'public_page_cache:' . sha1($request->fullUrl());
        $cached = Cache::get($key);

        if (is_array($cached) && isset($cached['content'])) {
            return response($cached['content'], (int) ($cached['status'] ?? 200))
                ->header('Content-Type', $cached['content_type'] ?? 'text/html; charset=UTF-8')
                ->header('Cache-Control', 'public, max-age=60, stale-while-revalidate=300')
                ->header('X-Public-Cache', 'HIT');
        }

        $response = $next($request);

        if ($this->isCacheableResponse($response)) {
            $response->setContent($this->compactHtml((string) $response->getContent()));

            Cache::put($key, [
                'status' => $response->getStatusCode(),
                'content' => $response->getContent(),
                'content_type' => $response->headers->get('Content-Type', 'text/html; charset=UTF-8'),
            ], self::TTL_SECONDS);

            $response->headers->set('Cache-Control', 'public, max-age=60, stale-while-revalidate=300');
            $response->headers->set('X-Public-Cache', 'MISS');
        }

        return $response;
    }

    private function shouldCache(Request $request): bool
    {
        if (!$request->isMethod('GET') || $request->ajax() || $request->expectsJson() || Auth::check()) {
            return false;
        }

        foreach ($this->excludedPrefixes as $pattern) {
            if ($request->is($pattern)) {
                return false;
            }
        }

        return true;
    }

    private function isCacheableResponse(Response $response): bool
    {
        if ($response->getStatusCode() !== 200 || !method_exists($response, 'getContent')) {
            return false;
        }

        $contentType = (string) $response->headers->get('Content-Type', '');

        return $contentType === '' || str_contains($contentType, 'text/html');
    }

    private function compactHtml(string $html): string
    {
        if ($html === '') {
            return $html;
        }

        $blocks = [];
        $html = preg_replace_callback(
            '#<(script|style|pre|textarea)\b[^>]*>.*?</\1>#is',
            function (array $matches) use (&$blocks): string {
                $key = '___SIPAROKI_HTML_BLOCK_' . count($blocks) . '___';
                $blocks[$key] = str_contains(strtolower($matches[0]), 'application/ld+json')
                    ? preg_replace('/\s+/', ' ', trim($matches[0]))
                    : trim($matches[0]);

                return $key;
            },
            $html
        );

        $html = preg_replace('/<!--(?!\[if).*?-->/s', '', $html);
        $html = preg_replace('/>\s+</', '><', $html);
        $html = preg_replace('/[ \t\r\n]+/', ' ', $html);
        $html = trim($html);

        foreach ($blocks as $key => $block) {
            $html = str_replace($key, $block, $html);
        }

        return $html;
    }
}
