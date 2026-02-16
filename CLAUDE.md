# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

PHP package for boilerplate removal and fulltext extraction from HTML pages. Rewrite of `dotpack/php-boiler-pipe` for PHP 8.2+. Namespace: `Pforret\PfArticleExtractor`.

## Commands

```bash
composer install          # Install dependencies
composer test             # Run tests (phpunit --testdox)
composer format           # Run Laravel Pint code formatter
vendor/bin/phpunit --filter=MethodName  # Run a single test
```

Always run `composer format` before committing.

## Architecture

### Two extraction approaches

**ArticleExtractor** (`src/ArticleExtractor.php`) - Static facade. Uses fivefilters/Readability.php for initial HTML parsing, then applies a chain of filters to classify text blocks as content vs boilerplate. Fallback: searches for `.post-body` class via XPath.

**BlogPostExtractor** (`src/BlogPostExtractor.php`) - Instance-based. Prioritizes structured metadata (og:*, twitter:*, article:* meta tags) for title/author/date/image/summary. Falls back to Readability for content extraction.

Both return `ArticleContentsDTO` with: canonical, title, author, date, summary, content, image, images, links, meta.

### Filter pipeline (ArticleExtractor::process)

Filters implement `IFilter::process(TextDocument $doc): bool` and are applied in sequence:
1. TerminatingBlocksFinder → NumWordsRulesClassifier → IgnoreBlocksAfterContent (English/)
2. BlockProximityFusion → BoilerplateBlockFilter → KeepLargestBlock → ExpandTitle (Heuristics/)
3. Each filter reads/modifies `TextBlock::isContent` flags on the document's block list

### Key classes

- `Formats/TextDocument` - Container of TextBlock objects, aggregates content/images/links
- `Formats/TextBlock` - Single text unit with word count, link density, content flag, labels
- `Formats/HtmlContent` - Walks DOM tree from `<body>`, creates TextBlocks with labels (H1/H2/H3/LI)
- `Helpers/HtmlManipulator` - Static methods: cleanup (strip scripts/nav/forms), parseImages, parseLinks, parseMeta, parseCanonical
- `Helpers/TextManipulator` - findDate (regex-based multi-format date extraction)
- `Helpers/Cleanup` - extractText (strip tags, filter short lines), relativeToAbsoluteUrl
- `Naming/TextLabels` - Constants for block classification (ARTICLE_TITLE, HEADING, INDICATES_END_OF_TEXT, etc.)

### Dependencies

- `fivefilters/readability.php` ^3.2 - Mozilla readability algorithm (production)
- `phpunit/phpunit` ^11.1, `laravel/pint` ^1.16 (dev)
- Required PHP extensions: dom, libxml, mbstring (+ curl for tests)

## Tests

Tests in `tests/` use example HTML files from `tests/examples/` (Blogger, Drupal, Jekyll, Mkdocs, Wix, WordPress, Hugo, Astro, Docusaurus, MOZ). Some tests fetch live URLs and require ext-curl. Several tests are currently failing (date extraction, content selection edge cases, title suffixes).
