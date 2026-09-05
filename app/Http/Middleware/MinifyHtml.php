<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MinifyHtml
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Skip if explicitly disabled via query or config
        if ($request->has('no_minify') || config('app.minify_html') === false || env('MINIFY_HTML') === false) {
            return $response;
        }

        // Skip binary, streaming, AJAX, and Inertia responses
        if (
            $response instanceof BinaryFileResponse ||
            $response instanceof StreamedResponse ||
            $request->ajax() ||
            $request->header('X-Inertia')
        ) {
            return $response;
        }

        $contentType = $response->headers->get('Content-Type');
        if ($contentType && !str_contains(strtolower($contentType), 'text/html')) {
            return $response;
        }

        $content = $response->getContent();
        if (!is_string($content) || empty($content) || (!str_contains($content, '<html') && !str_contains($content, '<!DOCTYPE'))) {
            return $response;
        }

        // Minify HTML output down to exactly 1 continuous line (Next.js / SatuSehat style)
        $response->setContent($this->minify($content));

        return $response;
    }

    /**
     * Minify raw HTML string down to ultra-compact source code.
     */
    protected function minify(string $html): string
    {
        // 1. Preserve <pre> and <textarea>
        $placeholders = [];
        $html = preg_replace_callback('/<(pre|textarea)(?:[^>]+)?>.*?<\/\\1>/si', function ($matches) use (&$placeholders) {
            $key = '___PRESERVED_BLOCK_' . count($placeholders) . '___';
            $placeholders[$key] = $matches[0];
            return $key;
        }, $html);

        // 2. Minify inline <style> blocks
        $html = preg_replace_callback('/<style(?:[^>]+)?>.*?<\/style>/si', function ($matches) {
            $css = $matches[0];
            $css = preg_replace('!/\*.*?\*/!s', '', $css);
            $css = preg_replace('/\s+/', ' ', $css);
            return str_replace([': ', ' {', '{ ', '; ', ' }', '} '], [':', '{', '{', ';', '}', '}'], $css);
        }, $html);

        // 3. Minify inline <script> blocks (safe trim lines, clean spacing)
        $html = preg_replace_callback('/<script(?:[^>]+)?>.*?<\/script>/si', function ($matches) {
            $script = $matches[0];
            if (stripos($script, 'application/ld+json') !== false || stripos($script, 'application/json') !== false) {
                return preg_replace('/\s+/', ' ', $script);
            }
            $lines = explode("\n", $script);
            $cleanLines = [];
            foreach ($lines as $line) {
                $trimmed = trim($line);
                if ($trimmed !== '') {
                    // If line starts with single-line comment //, remove it to prevent commenting out code on merge
                    if (str_starts_with($trimmed, '//')) {
                        continue;
                    }
                    $cleanLines[] = $trimmed;
                }
            }
            return implode(' ', $cleanLines);
        }, $html);

        // 4. Remove HTML comments (except conditional comments)
        $html = preg_replace('/<!--(?!\s*(?:\[if [^\]]+]|<!|>))(?:(?!-->).)*-->/s', '', $html);

        // 5. Remove spaces & linebreaks between tags
        $html = preg_replace('/>\s+</', '><', $html);

        // 6. Collapse remaining whitespace and strip all newlines into 1 continuous line (Next.js style)
        $html = preg_replace('/[\r\n]+/', ' ', $html);
        $html = preg_replace('/>\s+</', '><', $html);

        // 7. Restore preserved blocks (<pre>, <textarea>)
        if (!empty($placeholders)) {
            $html = strtr($html, $placeholders);
        }

        return trim($html);
    }
}
