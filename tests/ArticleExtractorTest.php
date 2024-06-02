<?php

use Pforret\PhpArticleExtractor\ArticleExtractor;
use PHPUnit\Framework\TestCase;

final class ArticleExtractorTest extends TestCase
{
    public function testWordpress1Html(): void
    {
        $html = file_get_contents(__DIR__.'/examples/blog_wordpress1.html');
        $article = ArticleExtractor::getArticle($html);
        $this->assertEquals('Before she heads back to a galaxy far, f', substr($article->content, 0, 40));
        $this->assertEquals('How to Watch The Young Woman and the Sea\': Is It on Disney+?', $article->title);
        $this->assertEquals(1734, strlen($article->content));

    }

    public function testWordpress2Html(): void
    {
        $html = file_get_contents(__DIR__.'/examples/blog_wordpress2.html');
        $article = ArticleExtractor::getArticle($html);
        $this->assertEquals('A United Launch Alliance Atlas V rocket ', substr($article->content, 0, 40));
        $this->assertEquals('NASA, Mission Partners to Update Media on Starliner Crew Flight Test', $article->title);
        $this->assertEquals(1313, strlen($article->content));

    }

    public function testWixHtml(): void
    {
        $html = file_get_contents(__DIR__.'/examples/blog_wix.html');
        $article = ArticleExtractor::getArticle($html);
        $this->assertEquals('UK Film Club was back in March with a ne', substr($article->content, 0, 40));
        $this->assertEquals('Film Podcast: Wicked Little Letters Named Film of the Month', $article->title);
        $this->assertEquals(3228, strlen($article->content));

    }

    public function testDrupalHtml(): void
    {
        $html = file_get_contents(__DIR__.'/examples/blog_drupal2.html');
        $article = ArticleExtractor::getArticle($html);
        $this->assertEquals('Humanitarian aid in the Middle East | London City Hall', $article->title);
        $this->assertEquals('Humanitarian aid in the Middle East Mess', substr($article->content, 0, 40));
        $this->assertEquals(562, strlen($article->content));

    }

    public function testJekyllHtml(): void
    {
        $html = file_get_contents(__DIR__.'/examples/blog_jekyll.html');
        $article = ArticleExtractor::getArticle($html);
        $this->assertEquals('Mirroring a website with Bashew and GitHub Actions · Peter Forret', $article->title);
        $this->assertEquals('I recently upgraded my Ubiquiti Wi-Fi in', substr($article->content, 0, 40));
        $this->assertStringContainsString('Cloud Key Gen 2', $article->content);
        $this->assertEquals(4235, strlen($article->content));

    }

    public function testMkdocsHtml(): void
    {
        $html = file_get_contents(__DIR__.'/examples/blog_mkdocs.html');
        $article = ArticleExtractor::getArticle($html);
        $this->assertEquals('What\'s new in asciinema - part II: the recorder · asciinema blog', $article->title);
        $this->assertEquals('This is part 2 in the “what’s new in', substr($article->content, 0, 40));
        $this->assertStringContainsString('override the terminal size', $article->content);
        $this->assertEquals(4983, strlen($article->content));

    }

    public function testBloggerHtml(): void
    {
        $html = file_get_contents(__DIR__.'/examples/blog_blogger.html');
        $article = ArticleExtractor::getArticle($html);
        $this->assertEquals('Official Blogger Blog: You can do some amazing things with Blogger', $article->title);
        $this->assertEquals('I hope the examples above have opened yo', substr($article->content, 0, 40));
        $this->assertStringContainsString('Google Docs, AdSense', $article->content);
        $this->assertEquals(2600, strlen($article->content));

    }
}
