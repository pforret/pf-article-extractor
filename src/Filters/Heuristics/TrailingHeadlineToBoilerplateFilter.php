<?php

namespace Pforret\PfArticleExtractor\Filters\Heuristics;

use Pforret\PfArticleExtractor\Filters\IFilter;
use Pforret\PfArticleExtractor\Formats\TextBlock;
use Pforret\PfArticleExtractor\Formats\TextDocument;
use Pforret\PfArticleExtractor\Naming\TextLabels;

final class TrailingHeadlineToBoilerplateFilter implements IFilter
{
    public function process(TextDocument $doc): bool
    {
        $hasChanges = false;

        /**
         * @var TextBlock[] $textBlocks
         */
        $textBlocks = $doc->getTextBlocks();
        $textBlocks = array_reverse($textBlocks);

        foreach ($textBlocks as $tb) {
            if ($tb->isContent()) {
                if ($tb->hasLabel(TextLabels::HEADING)) {
                    $tb->setIsContent(false);
                    $hasChanges = true;
                } else {
                    break;
                }
            }
        }

        return $hasChanges;
    }
}
