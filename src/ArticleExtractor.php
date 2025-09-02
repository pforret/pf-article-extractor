<?php

namespace Pforret\PfArticleExtractor;

use fivefilters\Readability\Configuration;
use fivefilters\Readability\ParseException;
use fivefilters\Readability\Readability;
use Pforret\PfArticleExtractor\Filters\English\IgnoreBlocksAfterContentFilter;
use Pforret\PfArticleExtractor\Filters\English\NumWordsRulesClassifier;
use Pforret\PfArticleExtractor\Filters\English\TerminatingBlocksFinder;
use Pforret\PfArticleExtractor\Filters\Heuristics\BlockProximityFusion;
use Pforret\PfArticleExtractor\Filters\Heuristics\DocumentTitleMatchClassifier;
use Pforret\PfArticleExtractor\Filters\Heuristics\ExpandTitleToContentFilter;
use Pforret\PfArticleExtractor\Filters\Heuristics\KeepLargestBlockFilter;
use Pforret\PfArticleExtractor\Filters\Heuristics\LargeBlockSameTagLevelToContentFilter;
use Pforret\PfArticleExtractor\Filters\Heuristics\ListAtEndFilter;
use Pforret\PfArticleExtractor\Filters\Heuristics\TrailingHeadlineToBoilerplateFilter;
use Pforret\PfArticleExtractor\Filters\Simple\BoilerplateBlockFilter;
use Pforret\PfArticleExtractor\Formats\ArticleContentsDTO;
use Pforret\PfArticleExtractor\Formats\TextDocument;
use Pforret\PfArticleExtractor\Helpers\HtmlManipulator;
use Pforret\PfArticleExtractor\Helpers\TextManipulator;
use Pforret\PfArticleExtractor\Naming\TextLabels;

final class ArticleExtractor
{
    public static function process(TextDocument $doc): bool
    {
        return (new TerminatingBlocksFinder)->process($doc)
        | (new DocumentTitleMatchClassifier)->process($doc)
        | (new NumWordsRulesClassifier)->process($doc)
        | (new IgnoreBlocksAfterContentFilter(60))->process($doc)
        | (new TrailingHeadlineToBoilerplateFilter)->process($doc)
        | (new BlockProximityFusion(1))->process($doc)
        | (new BoilerplateBlockFilter(TextLabels::TITLE))->process($doc)
        | (new BlockProximityFusion(1, true, true))->process($doc)
        | (new KeepLargestBlockFilter(true, 150))->process($doc)
        | (new ExpandTitleToContentFilter)->process($doc)
        | (new LargeBlockSameTagLevelToContentFilter)->process($doc)
        | (new ListAtEndFilter)->process($doc);
    }

    /**
     * @throws ParseException
     */
    public static function getArticle(string $html): ArticleContentsDTO
    {
        $configuration = new Configuration;
        $document = new Readability($configuration);
        $html = HtmlManipulator::cleanup($html);
        $document->parse($html);
        $content = self::extractText($document->getContent());
        if (! $content) {
            $content = self::lookForClass($html);
        }
        if (str_starts_with(strtolower($content), 'skip to content')) {
            $content = trim(substr($content, 15));
        }

        $article = new ArticleContentsDTO;
        $article->meta = HtmlManipulator::parseMeta($html);
        $article->canonical = HtmlManipulator::parseCanonical($html);
        $article->title = self::cleanupTitle($document->getTitle());
        $article->content = self::cleanupBody($content);
        $article->images = $document->getImages() ?? [];
        $article->links = self::getLinks($document->getContent()) ?? [];
        $article->date = self::getDate($document->getContent()) ?? '';
        $article->author = $document->getAuthor() ?? '';

        print_r($article);

        return $article;
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

    private static function getLinks(?string $content): array
    {
        if (! trim($content)) {
            return [];
        }
        preg_match_all('| href="(http[^"]+)"]|', $content, $matches);

        return $matches[1];
    }

    private static function getDate(?string $content): string
    {
        if (! trim($content)) {
            return '';
        }

        return TextManipulator::findDate($content);

    }

    private static function cleanupHtml(string $html): string
    {
        $html = preg_replace('/<span[^>]*>/', '', $html);

        return preg_replace('/<\/span>/', '', $html);
    }

    private static function lookForClass(string $html): string
    {
        // look for <div class='post-body'> via DomObject
        $doc = new \DOMDocument;
        @$doc->loadHTML($html);
        $xpath = new \DOMXPath($doc);
        $elements = $xpath->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' post-body ')]");
        if ($elements->length > 0) {
            return preg_replace("|[\s\t\n\r]+|", ' ', trim(strip_tags($elements->item(0)->textContent)));
        }

        return '';

    }

    private static function cleanupTitle(?string $getTitle): string
    {
        return str_replace([
            "'",
        ], [
            '’',
        ], $getTitle);
    }

    private static function cleanupBody(string $content): string
    {
        if (str_starts_with(strtolower($content), 'skip to content')) {
            $content = trim(substr($content, 15));
        }
        $content = preg_replace("|(\s[\t\s]++)|", ' ', $content);
        $content = preg_replace("|([\n\r]+)|", "\n", $content);

        return str_replace(
            ['&nbsp;'],
            [' '],
            $content
        );
    }
}
