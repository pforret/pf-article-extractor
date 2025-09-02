<?php

namespace Helpers;

use Pforret\PfArticleExtractor\Helpers\HtmlManipulator;
use PHPUnit\Framework\TestCase;

class HtmlManipulatorTest extends TestCase
{
    public function test_is_irrelevant_picture() {}

    public function test_parse_links() {}

    public function test_cleanup1()
    {
        $html = file_get_contents(__DIR__.'/../examples/blog_wordpress1.html');
        $html2 = HtmlManipulator::cleanup($html);
        $this->assertEquals(319731, strlen($html));
        $this->assertEquals(13806, strlen($html2));
        $this->assertStringContainsString('Ederle is played by Daisy Ridley', $html2);
    }

    public function test_cleanup2()
    {
        $html = file_get_contents(__DIR__.'/../examples/blog_wix.html');
        $html2 = HtmlManipulator::cleanup($html);
        $this->assertEquals(3381844, strlen($html));
        $this->assertEquals(49015, strlen($html2));
        $this->assertStringContainsString('It will make you think about all manner of humanity', $html2);
    }

    public function test_cleanup3()
    {
        $html = file_get_contents(__DIR__.'/../examples/blog_huffpost.html');
        $html2 = HtmlManipulator::cleanup($html);
        $this->assertEquals(513448, strlen($html));
        $this->assertEquals(51170, strlen($html2));
        $this->assertStringContainsString('But the saga had a happy ending.', $html2);
    }

    public function test_cleanup4()
    {
        $html = file_get_contents(__DIR__.'/../examples/blog_techcrunch.html');
        $html2 = HtmlManipulator::cleanup($html);
        $this->assertEquals(311454, strlen($html));
        $this->assertEquals(56155, strlen($html2));
        $this->assertStringContainsString('Stripe is laying off 300 people', $html2);
    }

    public function test_parse_images() {}
}
