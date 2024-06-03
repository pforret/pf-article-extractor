<?php

use Pforret\PfArticleExtractor\ArticleExtractor;
use PHPUnit\Framework\TestCase;

final class ArticleExtractorTest extends TestCase
{
    public function testWordpress1Html(): void
    {
        $html = file_get_contents(__DIR__.'/examples/blog_wordpress1.html');
        $article = ArticleExtractor::getArticle($html);
        $this->assertEquals('Before she heads back to a galaxy far, f', substr($article->content, 0, 40));
        $this->assertEquals('How to Watch ‘Young Woman and the Sea’: Is Daisy Ridley’s Historical Drama Streaming or in Theaters?', $article->title);
        $this->assertStringContainsString('also boasts Jerry Bruckheimer', $article->content);
        $this->assertEquals('', $article->date);
        $this->assertEquals(1443, strlen($article->content));

    }

    public function testWordpress2Html(): void
    {
        $html = file_get_contents(__DIR__.'/examples/blog_wordpress2.html');
        $article = ArticleExtractor::getArticle($html);
        $this->assertEquals('A United Launch Alliance Atlas V rocket ', substr($article->content, 0, 40));
        $this->assertEquals('NASA, Mission Partners to Update Media on Starliner Crew Flight Test', $article->title);
        $this->assertStringContainsString('NASA will provide news conference coverage', $article->content);
        $this->assertEquals('', $article->date);
        $this->assertEquals(1319, strlen($article->content));

    }

    public function testWixHtml(): void
    {
        $html = file_get_contents(__DIR__.'/examples/blog_wix.html');
        $article = ArticleExtractor::getArticle($html);
        $this->assertEquals('UK Film Club was back in March with a ne', substr($article->content, 0, 40));
        $this->assertEquals('Film Podcast: Wicked Little Letters Named Film of the Month', $article->title);
        $this->assertStringContainsString('However, trouble is afoot', $article->content);
        $this->assertEquals('', $article->date);
        $this->assertEquals(3326, strlen($article->content));

    }

    public function testDrupalHtml(): void
    {
        $html = file_get_contents(__DIR__.'/examples/blog_drupal2.html');
        $article = ArticleExtractor::getArticle($html);
        $this->assertEquals('Humanitarian aid in the Middle East', $article->title);
        $this->assertEquals('In recent days, the Mayor has shared his', substr($article->content, 0, 40));
        $this->assertStringContainsString('The Mayor knows that many Londoners', $article->content);
        $this->assertEquals('', $article->date);
        $this->assertEquals(820, strlen($article->content));

    }

    public function testJekyllHtml(): void
    {
        $html = file_get_contents(__DIR__.'/examples/blog_jekyll.html');
        $article = ArticleExtractor::getArticle($html);
        $this->assertEquals('Mirroring a website with Bashew and GitHub Actions', $article->title);
        $this->assertEquals('TL;DR: I decided to mirror the installat', substr($article->content, 0, 40));
        $this->assertStringContainsString('Cloud Key Gen 2', $article->content);
        $this->assertEquals('2022-10-15', $article->date);
        $this->assertEquals(4767, strlen($article->content));

    }

    public function testMkdocsHtml(): void
    {
        $html = file_get_contents(__DIR__.'/examples/blog_mkdocs.html');
        $article = ArticleExtractor::getArticle($html);
        $this->assertEquals('What\'s new in asciinema - part II: the recorder · asciinema blog', $article->title);
        $this->assertEquals('This is part 2 in the “what’s new in', substr($article->content, 0, 40));
        $this->assertStringContainsString('override the terminal size', $article->content);
        $this->assertEquals('', $article->date);
        $this->assertEquals(4485, strlen($article->content));

    }

    public function testBloggerHtml(): void
    {
        $html = file_get_contents(__DIR__.'/examples/blog_blogger.html');
        $article = ArticleExtractor::getArticle($html);
        $this->assertEquals('You can do some amazing things with Blogger', $article->title);
        $this->assertEquals('Guest post by David Kutcher Editor&#8217', substr($article->content, 0, 40));
        $this->assertStringContainsString('We invited David Kutcher', $article->content);
        $this->assertEquals('', $article->date);
        $this->assertEquals(2752, strlen($article->content));

    }
}
