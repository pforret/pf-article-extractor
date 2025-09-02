<?php

namespace Pforret\PfArticleExtractor\Helpers;

use DOMDocument;

class HtmlManipulator
{
    public static function parseImages(string $html): array
    {
        $doc = new DOMDocument;
        @$doc->loadHTML($html);
        $tags = $doc->getElementsByTagName('img');
        $images = [];
        foreach ($tags as $tag) {
            $url = $tag->getAttribute('src');
            if (! $url) {
                continue;
            }
            if (! str_starts_with($url, 'http')) {
                continue;
            }
            if (self::isIrrelevantPicture($url)) {
                continue;
            }
            $url = str_replace('&amp;', '&', $url);
            $images[] = $url;
        }

        return $images;
    }

    public static function isIrrelevantPicture(string $url): bool
    {
        $ignoreFileNames = [
            'blank.gif',
            'pixel.gif',
            'pixel.jpeg',
            'pixel.jpg',
            'pixel.png',
            'pixel.svg',
            'pixel.webp',
            'spacer.gif',
            'spacer.jpeg',
            'spacer.jpg',
            'spacer.png',
            'spacer.svg',
            'spacer.webp',
            'transparent.gif',
            'transparent.jpeg',
            'transparent.jpg',
            'transparent.png',
            'transparent.svg',
            'transparent.webp',
        ];
        $ignoreDomains = [
            'cdn.jsdelivr.net',
        ];

        return in_array(strtolower(basename($url)), $ignoreFileNames)
            || in_array(parse_url($url, PHP_URL_HOST), $ignoreDomains);
    }

    public static function parseLinks(string $html): array
    {
        $links = [];
        $doc = new DOMDocument;
        @$doc->loadHTML($html);
        $tags = $doc->getElementsByTagName('a');
        foreach ($tags as $tag) {
            $url = $tag->getAttribute('href');
            if (! $url) {
                continue;
            }
            if (! str_starts_with($url, 'http')) {
                continue;
            }
            $links[] = $tag->getAttribute('href');
        }

        return $links;
    }

    public static function cleanup(string $html): string
    {
        $html = preg_replace("|[\s\t\n\r]+|", ' ', $html); // make sure there are no multilines
        $html = preg_replace('/<(span)(.*?)>/', '', $html);
        $html = preg_replace('/<\/(span)>/', '', $html);
        $html = preg_replace('/<script(.*?)>(.*?)<\/script>/', '', $html);
        $html = preg_replace('/<style(.*?)>(.*?)<\/style>/', '', $html);
        $html = preg_replace('/<noscript(.*?)>(.*?)<\/noscript>/', '', $html);
        $html = preg_replace('/<svg(.*?)>(.*?)<\/svg>/', '', $html);
        $html = preg_replace('/<iframe(.*?)>(.*?)<\/iframe>/', '', $html);
        $html = preg_replace('/<form(.*?)>(.*?)<\/form>/', '', $html);
        $html = preg_replace('/<input(.*?)>/', '', $html);
        $html = preg_replace('/<button(.*?)>(.*?)<\/button>/', '', $html);
        $html = preg_replace('/<select(.*?)>(.*?)<\/select>/', '', $html);
        $html = preg_replace('/<textarea(.*?)>(.*?)<\/textarea>/', '', $html);
        $html = preg_replace('/<label(.*?)>(.*?)<\/label>/', '', $html);
        $html = preg_replace('/<option(.*?)>(.*?)<\/option>/', '', $html);
        $html = preg_replace('/<ul(.*?)>(.*?)<\/ul>/', '', $html);
        $html = preg_replace('/<ol(.*?)>(.*?)<\/ol>/', '', $html);
        $html = preg_replace('/<nav(.*?)>(.*?)<\/nav>/', '', $html);
        $html = preg_replace('/<footer(.*?)>(.*?)<\/footer>/', '', $html);
        $html = preg_replace('/<header(.*?)>(.*?)<\/header>/', '', $html);
        $html = preg_replace('/<aside(.*?)>(.*?)<\/aside>/', '', $html);
        $html = preg_replace('/<!--(.*?)-->/', '', $html);
        $html = preg_replace('/<div ([^>]+)>\s*<\/div>/', '', $html);

        return trim(preg_replace("|[\s\t\n\r]+|", ' ', $html));
    }

    public static function parseMeta(string $html): array
    {
        // parse the DOM html for <meta> tags
        $doc = new DOMDocument;
        @$doc->loadHTML($html);
        $tags = $doc->getElementsByTagName('meta');
        $meta = [];
        foreach ($tags as $tag) {
            $name = $tag->getAttribute('name');
            $content = $tag->getAttribute('content');
            if ($name && $content) {
                $meta[$name] = $content;
            }
        }

        return $meta;
    }

    public static function parseCanonical(string $html)
    {
        // parse <link rel="canonical" from HTML
        $doc = new DOMDocument;
        @$doc->loadHTML($html);
        $tags = $doc->getElementsByTagName('link');
        foreach ($tags as $tag) {
            if ($tag->getAttribute('rel') == 'canonical') {
                return $tag->getAttribute('href');
            }
        }

        return '';
    }
}
