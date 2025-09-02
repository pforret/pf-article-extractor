<?php

namespace Pforret\PfArticleExtractor\Formats;

class ArticleContentsDTO
{
    public string $canonical = '';      // canonical URL

    public string $title = '';      // from <h1> or <title>

    public string $author = '';

    public string $date = '';       // publishing date

    public string $summary = '';    // short description

    public string $content = '';    // just the text of the article

    public string $image = '';      // main imag

    public array $images = [];      // list of <img tags

    public array $links = [];       // list of <a

    public array $meta = [];        // list of <meta
}
