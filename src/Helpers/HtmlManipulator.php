<?php

namespace Pforret\PfArticleExtractor\Helpers;

use DOMDocument;

class HtmlManipulator
{
    public function parseImages(string $html): array
    {
        $doc = new DOMDocument();
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
            if ($this->isIrrelevantPicture($url)) {
                continue;
            }
            $url = str_replace('&amp;', '&', $url);
            $images[] = $url;
        }

        return $images;
    }

    private function isIrrelevantPicture(string $url): bool
    {
        $detectBasenames = [
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
        $detectDomains = [
            'cdn.jsdelivr.net',
        ];

        return in_array(strtolower(basename($url)), $detectBasenames)
            || in_array(parse_url($url, PHP_URL_HOST), $detectDomains);
    }
}
