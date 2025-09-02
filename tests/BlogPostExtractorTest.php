<?php

use fivefilters\Readability\ParseException;
use Pforret\PfArticleExtractor\BlogPostExtractor;
use PHPUnit\Framework\TestCase;

class BlogPostExtractorTest extends TestCase
{
    /**
     * @throws ParseException
     */
    public function test_get_article_astro()
    {
        $html = file_get_contents(__DIR__.'/examples/blog_astro.html');
        $extractor = new BlogPostExtractor($html);
        $article = $extractor->getArticle();
        // print_r($article);
        $this->assertEquals('https://astro.build/blog/2023-web-framework-performance-report/', $article->canonical);
        $this->assertEquals('2023 Web Framework Performance Report | Astro', $article->title);
        $this->assertEquals('', $article->author);
        $this->assertEquals('A look at how different web frameworks perform in the real world in 2023. Based on real-world, production data from HTTP Archive and Google Chrome.', $article->summary);
        $this->assertEquals('2023-03-07', $article->date);
        $this->assertEquals('The purpose of this report is to look at', substr($article->content, 0, 40));
        $this->assertStringContainsString('No performance data was measured directly by the Astro team', $article->content);
        $this->assertEquals('https://astro.build/_astro/social.FlPxeRsG.jpg', $article->image);
    }

    /**
     * @throws ParseException
     */
    public function test_get_article_blogger()
    {
        $html = file_get_contents(__DIR__.'/examples/blog_blogger.html');
        $extractor = new BlogPostExtractor($html);
        $article = $extractor->getArticle();
        print_r($article);
        $this->assertEquals('https://blogger.googleblog.com/2011/07/you-can-do-some-amazing-things-with.html', $article->canonical);
        $this->assertEquals('You can do some amazing things with Blogger', $article->title);
        $this->assertEquals('@blogger', $article->author);
        $this->assertEquals('2023-03-07', $article->date);
        $this->assertEquals('The purpose of this report is to look at', substr($article->content, 0, 40));
        $this->assertStringContainsString('No performance data was measured directly by the Astro team', $article->content);
        $this->assertEquals('https://astro.build/_astro/social.FlPxeRsG.jpg', $article->image);
    }
}
