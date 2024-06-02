<?php

namespace Pforret\PfArticleExtractor;

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
use Pforret\PfArticleExtractor\Formats\ArticleContents;
use Pforret\PfArticleExtractor\Formats\HtmlContent;
use Pforret\PfArticleExtractor\Formats\TextDocument;
use Pforret\PfArticleExtractor\Naming\TextLabels;

final class ArticleExtractor
{
    public static function process(TextDocument $doc): bool
    {
        return (new TerminatingBlocksFinder())->process($doc)
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

    public static function getContent(string $html): string
    {
        $content = new HtmlContent($html);
        $document = $content->getTextDocument();

        self::process($document);

        return $document->getContent();
    }

    public static function getArticle(string $html): ArticleContents
    {
        $content = new HtmlContent($html);
        $document = $content->getTextDocument();

        self::process($document);

        $article = new ArticleContents();
        $article->title = $document->getTitle();
        $article->content = $document->getContent();
        $article->images = $document->getImages();
        $article->links = $document->getLinks();

        return $article;
    }
}
