<?php

namespace Pforret\PfArticleExtractor\Filters\Simple;

use Pforret\PfArticleExtractor\Filters\IFilter;
use Pforret\PfArticleExtractor\Formats\TextDocument;

final class BoilerplateBlockFilter implements IFilter
{
    private string $labelToKeep;

    public function __construct(?string $labelToKeep = null)
    {
        $this->labelToKeep = $labelToKeep;
    }

    public function process(TextDocument $doc): bool
    {
        $hasChanges = false;
        $textBlocks = $doc->getTextBlocks();
        foreach ($textBlocks as $tb) {
            if (! $tb->isContent() && ($this->labelToKeep == null || ! $tb->hasLabel($this->labelToKeep))) {
                $doc->removeTextBlock($tb);
                $hasChanges = true;
            }
        }

        return $hasChanges;
    }
}
