<?php

namespace Pforret\PfArticleExtractor\Filters;

use Pforret\PfArticleExtractor\Formats\TextDocument;

interface IFilter
{
    public function process(TextDocument $doc): bool;
}
