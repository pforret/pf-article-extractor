# pforret/pf-article-extractor

[![Tests](https://github.com/pforret/pf-article-extractor/actions/workflows/run-tests.yml/badge.svg)](https://github.com/pforret/pf-article-extractor/actions)
![GitHub Release](https://img.shields.io/github/v/release/pforret/pf-article-extractor)
[![Latest Stable Version](https://poser.pugx.org/pforret/pf-article-extractor/version.png)](https://packagist.org/packages/pforret/pf-article-extractor)
[![Total Downloads](https://poser.pugx.org/pforret/pf-article-extractor/d/total.png)](https://packagist.org/packages/pforret/pf-article-extractor)
![GitHub commit activity](https://img.shields.io/github/commit-activity/y/pforret/pf-article-extractor)
![GitHub License](https://img.shields.io/github/license/pforret/pf-article-extractor)

![](assets/unsplash.squeeze.jpg)

Boilerplate Removal and Fulltext Extraction from HTML pages.

Rewrite of `dotpack/php-boiler-pipe` for PHP8.2 and up, with tests.

## Installation

```bash
composer require pforret/pf-article-extractor
```

## Usage

```php
use Pforret\PfArticleExtractor\ArticleExtractor;

$articleData = ArticleExtractor::getArticle($html);
/*
 * $articleData = Pforret\PfArticleExtractor\Formats\ArticleContents Object
(
    [title] => Film Podcast: Wicked Little Letters Named Film of the Month
    [content] => UK Film Club was back in March with a new episode of their film podcast. (...)
    [date] =>
    [images] => Array
        (
            [0] => https://static.wixstatic.com/media/.../b19cd0_dde0d59546f84127865267f43994f39b~mv2.jpg
        )

    [links] => Array
        (
            [0] => https://www.chrisolson.co.uk/
            (...)
        )

)

 */
```

## Under the hood

* package accepts a full HTML page as input
* it will walk the DOM tree and try to find the main article content
* it will remove boilerplate content (like headers, footers, sidebars, ...)
* it will try to extract the main article content
* it will try to extract the title, date, images and links from the article

Rights now it's tested with example pages for
* Blogger
* Drupal
* Jekyll
* Mkdocs
* Wix
* WordPress