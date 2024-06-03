<?php

namespace Pforret\PfArticleExtractor\Helpers;

class TextManipulator
{
    public static function justTheText(string $text): string
    {
        return mb_strtolower(preg_replace('/\W+/', '', $text));
    }

    public static function findDate(string $data): string
    {
        $formats = [
            'dd-mm-yyyy',
            'yyyy-mm-dd',
            'dd/mm/yyyy',
            'dd\.mm\.yyyy',
            'mm-dd-yyyy',
            'mm/dd/yyyy',
            'yyyy/mm/dd',
            'yyyy\.mm\.dd',
        ];
        foreach ($formats as $format) {
            $date = self::extractDate($data, $format);
            if ($date) {
                return $date;
            }
        }

        return '';
    }

    private static function extractDate(string $text, string $format): string
    {
        // $format is like 'yyyy-mm-dd'
        // $text could be like 'Written by John on 2024-02-02'
        if (mb_strlen($text) < 8) {
            return '';
        }
        if (! preg_match("|\d|", $text)) {
            return '';
        }
        if (! str_contains($text, date('Y'))) {
            return '';
        }
        $date = '';
        $pattern = $format;
        $pattern = str_replace('yyyy', '(\d{4})', $pattern);
        $pattern = str_replace('mm', '(\d{2})', $pattern);
        $pattern = str_replace('dd', '(\d{2})', $pattern);
        $pattern = "|$pattern|";
        if (preg_match($pattern, $text, $matches)) {
            $date = $matches[0];
            $timestamp = strtotime($date);
            if ($timestamp === false) {
                return '';
            }

            return date('Y-m-d', $timestamp);
        }

        return '';
    }
}
