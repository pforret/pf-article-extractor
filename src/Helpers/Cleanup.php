<?php

namespace Pforret\PfArticleExtractor\Helpers;

class Cleanup
{
    private static function cleanupTitle(?string $title): string
    {
        return str_replace([
            "'",
        ], [
            '’',
        ], $title);
    }

    public static function extractText(?string $content): string
    {
        $content = strip_tags($content);
        $lines = explode("\n", $content);
        $selected = [];
        $previous = '';
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) {
                continue;
            }
            if ($line == $previous) {
                continue;
            }
            if (strlen($line) < 50) {
                continue;
            }

            $selected[] = $line;
            $previous = $line;
        }

        return implode("\n", $selected);
    }

    public static function relativeToAbsoluteUrl(string $relative, string $currentUrl): string
    {
        if (str_starts_with($relative, 'https://')) {
            return $relative;
        }
        if (str_starts_with($relative, 'http://')) {
            return $relative;
        }
        if (! $currentUrl) {
            return '';
        }
        $url = parse_url($currentUrl);
        $scheme = $url['scheme'] ?? 'http';
        $host = $url['host'] ?? '';
        if (str_starts_with($relative, '/')) {
            return $scheme.'://'.$host.$relative;
        }
        $path = $url['path'] ?? '';
        $path = substr($path, 0, strrpos($path, '/') + 1);

        return $scheme.'://'.$host.$path.$relative;
    }
}
