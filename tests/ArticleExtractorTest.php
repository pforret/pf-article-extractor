<?php

use fivefilters\Readability\ParseException;
use Pforret\PfArticleExtractor\ArticleExtractor;
use PHPUnit\Framework\TestCase;

final class ArticleExtractorTest extends TestCase
{
    /**
     * @throws ParseException
     */
    public function test_wordpress1_html(): void
    {
        $html = file_get_contents(__DIR__.'/examples/blog_wordpress1.html');
        $article = ArticleExtractor::getArticle($html);
        $this->assertEquals('Before she heads back to a galaxy far, f', substr($article->content, 0, 40));
        $this->assertEquals('How to Watch The Young Woman and the Sea’: Is It on Disney+?', $article->title);
        $this->assertStringContainsString('also boasts Jerry Bruckheimer', $article->content);
        $this->assertEquals('', $article->date);
        //       $this->assertEquals(1443, strlen($article->content));

    }

    /**
     * @throws ParseException
     */
    public function test_wordpress2_html(): void
    {
        $html = file_get_contents(__DIR__.'/examples/blog_wordpress2.html');
        $article = ArticleExtractor::getArticle($html);
        $this->compareTexts($article->content, 'A United Launch Alliance Atlas V rocket ');
        $this->assertEquals('NASA, Mission Partners to Update Media on Starliner Crew Flight Test', $article->title);
        $this->assertStringContainsString('NASA will provide news conference coverage', $article->content);
        $this->assertEquals('', $article->date);
        //        $this->assertEquals(1319, strlen($article->content));

    }

    /**
     * @throws ParseException
     */
    public function test_wix_html(): void
    {
        $html = file_get_contents(__DIR__.'/examples/blog_wix.html');
        $article = ArticleExtractor::getArticle($html);
        $this->assertEquals('UK Film Club was back in March with a ne', substr($article->content, 0, 40));
        $this->assertEquals('Film Podcast: Wicked Little Letters Named Film of the Month', $article->title);
        $this->assertStringContainsString('However, trouble is afoot', $article->content);
        $this->assertEquals('', $article->date);
        //        $this->assertEquals(3326, strlen($article->content));

    }

    /**
     * @throws ParseException
     */
    public function test_drupal_html(): void
    {
        $html = file_get_contents(__DIR__.'/examples/blog_drupal2.html');
        $article = ArticleExtractor::getArticle($html);
        $this->assertEquals('Humanitarian aid in the Middle East', $article->title);
        $this->assertEquals('In recent days, the Mayor has shared his', substr($article->content, 0, 40));
        $this->assertStringContainsString('The Mayor knows that many Londoners', $article->content);
        $this->assertEquals('', $article->date);
        //        $this->assertEquals(820, strlen($article->content));

    }

    /**
     * @throws ParseException
     */
    public function test_jekyll_html(): void
    {
        $html = file_get_contents(__DIR__.'/examples/blog_jekyll.html');
        $article = ArticleExtractor::getArticle($html);
        $this->assertEquals('Mirroring a website with Bashew and GitHub Actions', $article->title);
        $this->assertEquals('TL;DR: I decided to mirror the installat', substr($article->content, 0, 40));
        $this->assertStringContainsString('Cloud Key Gen 2', $article->content);
        $this->assertEquals('2024-05-19', $article->date);
        //        $this->assertEquals(4767, strlen($article->content));

    }

    /**
     * @throws ParseException
     */
    public function test_mkdocs_html(): void
    {
        $html = file_get_contents(__DIR__.'/examples/blog_mkdocs.html');
        $article = ArticleExtractor::getArticle($html);
        $this->assertEquals('What’s new in asciinema - part II: the recorder · asciinema blog', $article->title);
        $this->compareTexts($article->content, 'This is part 2 in the “what’s new in');
        $this->assertStringContainsString('override the terminal size', $article->content);
        $this->assertEquals('', $article->date);
        //        $this->assertEquals(4485, strlen($article->content));

    }

    /**
     * @throws ParseException
     */
    public function test_blogger_html(): void
    {
        $html = file_get_contents(__DIR__.'/examples/blog_blogger.html');
        $article = ArticleExtractor::getArticle($html);
        $this->assertEquals('You can do some amazing things with Blogger', $article->title);
        $this->compareTexts($article->content, 'Guest post by David Kutcher Editor');
        $this->assertStringContainsString('We invited David Kutcher', $article->content);
        $this->assertEquals('', $article->date);
        //        $this->assertEquals(2752, strlen($article->content));

    }

    /**
     * @throws ParseException
     */
    public function test_blog_moz(): void
    {
        $html = file_get_contents(__DIR__.'/examples/blog_moz.html');
        $article = ArticleExtractor::getArticle($html);
        $this->assertEquals('8 Ways To Automate SEO and Content Tasks With LLMs', $article->title);
        $this->compareTexts($article->content, 'The author\'s views are entirely their own (excluding the unlikely event of hypnosis');
        $this->assertStringContainsString('SEO and content tasks can feel like monotonous work after a while', $article->content);
        $this->assertEquals('', $article->date);
        //        $this->assertEquals(11786, strlen($article->content));

    }

    /**
     * @throws ParseException
     */
    public function test_blog_hugo(): void
    {
        $html = file_get_contents(__DIR__.'/examples/blog_hugo.html');
        $article = ArticleExtractor::getArticle($html);
        $this->assertEquals('Release v0.137.0 · gohugoio/hugo', $article->title);
        $this->compareTexts($article->content, 'Note that we have no longer build the deploy feature');
        $this->assertStringContainsString('but for one, it shaves off about 40%', $article->content);
        $this->assertEquals('2024-11-04', $article->date);

    }

    /**
     * @throws ParseException
     */
    public function test_blog_docusaurus(): void
    {
        $html = file_get_contents(__DIR__.'/examples/blog_docusaurus.html');
        $article = ArticleExtractor::getArticle($html);
        $this->assertEquals('Docusaurus 3.7', $article->title);
        $this->compareTexts($article->content, 'Versioning. Minor versions do not include any breaking changes');
        $this->assertStringContainsString('From now on, all newly initialized sites will run on React 19 by default', $article->content);
        $this->assertEquals('2025-01-03', $article->date);

    }

    private function compareTexts(string $expected, string $actual, int $length = 50): void
    {
        // $expected = preg_replace('/\s+/', ' ', $expected);
        // $actual = preg_replace('/\s+/', ' ', $actual);
        $length = min(strlen($expected), strlen($actual), $length);
        $this->assertEquals(substr($expected, 0, $length), substr($actual, 0, $length));
    }
}
