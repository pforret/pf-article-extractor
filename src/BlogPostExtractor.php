<?php

namespace Pforret\PfArticleExtractor;

use DOMDocument;
use fivefilters\Readability\Configuration;
use fivefilters\Readability\ParseException;
use fivefilters\Readability\Readability;
use Pforret\PfArticleExtractor\Formats\ArticleContentsDTO;
use Pforret\PfArticleExtractor\Helpers\Cleanup;
use Pforret\PfArticleExtractor\Helpers\HtmlManipulator;

class BlogPostExtractor
{
    private DOMDocument $doc;

    private string $html;

    private array $meta;
    private string $canonical;

    public function __construct(string $html)
    {
        $this->html = $html;
        $this->doc = new DOMDocument;
        @$this->doc->loadHTML($html);
        $this->meta = $this->getMeta();
        $this->canonical = $this->getCanonical();
    }

    /**
     * @throws ParseException
     */
    public function getArticle(): ArticleContentsDTO
    {
        $contents = new ArticleContentsDTO;

        $contents->meta = $this->meta;
        $contents->canonical = $this->canonical;
        $contents->title = $this->getTitle();
        $contents->author = $this->getAuthor();
        $contents->date = $this->getDate();
        $contents->summary = $this->getSummary();
        $contents->content = $this->getContent();
        $contents->image = $this->getMainImage();
        //$contents->images = $this->getImages();
        //$contents->links = $this->getLinks();

        return $contents;
    }

    private function getMeta(): array
    {
        $tags = $this->doc->getElementsByTagName('meta');
        $meta = [];
        foreach ($tags as $tag) {
            $name = $tag->getAttribute('name');
            $content = $tag->getAttribute('content');
            if ($name && $content) {
                $meta[$name] = $content;
            }
            $property = $tag->getAttribute('property');
            if ($property && $content) {
                $meta[$property] = $content;
            }

        }

        return $meta;
    }

    private function getCanonical(): string
    {
        //     <meta property="og:url" content="https://blog.forret.com/2024/05/19/unifi-mirror-bashew-github/"/>
        $tags = $this->doc->getElementsByTagName('link');
        foreach ($tags as $tag) {
            if ($tag->getAttribute('rel') === 'canonical') {
                return $tag->getAttribute('href');
            }
        }

        //     <meta property="og:url" content="https://blog.forret.com/2024/05/19/unifi-mirror-bashew-github/"/>
        if (isset($this->meta['og:url'])) {
            return $this->meta['og:url'];
        }

        //     <meta name="twitter:url"
        //          content="https://www.london.gov.uk/programmes-strategies/communities-and-social-justice/humanitarian-aid-middle-east"/>
        if (isset($this->meta['twitter:url'])) {
            return $this->meta['twitter:url'];
        }

        //     <meta property="pin:url"
        //          content="https://www.london.gov.uk/programmes-strategies/communities-and-social-justice/humanitarian-aid-middle-east"/>
        if (isset($this->meta['pin:url'])) {
            return $this->meta['pin:url'];
        }

        return '';
    }

    private function getMainImage(): string
    {
        //     <meta property="og:image"
        //          content="https://www.london.gov.uk/sites/default/files/styles/open_graph_image/public/2023-10/Make%20a%20change_Katt%20Yukawa_1920x1280unsplash.jpg?h=67023971&amp;itok=FLAmI1lF"/>
        if (isset($this->meta['og:image'])) {
            return $this->meta['og:image'];
        }

        //     <meta name="twitter:image"
        //          content="https://www.london.gov.uk/sites/default/files/styles/twitter_card_image/public/2023-10/Make%20a%20change_Katt%20Yukawa_1920x1280unsplash.jpg?h=67023971&amp;itok=Q-GffQ6i"/>
        if (isset($this->meta['twitter:image'])) {
            return $this->meta['twitter:image'];
        }

        //     <meta property="pin:media"
        //          content="https://www.london.gov.uk/sites/default/files/styles/open_graph_image/public/2023-10/Make%20a%20change_Katt%20Yukawa_1920x1280unsplash.jpg?h=67023971&amp;itok=FLAmI1lF"/>
        if (isset($this->meta['pin:media'])) {
            return $this->meta['pin:media'];
        }

        return '';
    }

    private function getTitle(): string
    {
        //     <meta property="og:title" content="Humanitarian aid in the Middle East"/>
        if (isset($this->meta['og:title'])) {
            return $this->meta['og:title'];
        }

        //     <meta property="twitter:title" content="Mirroring a website with Bashew and GitHub Actions"/>
        if (isset($this->meta['twitter:title'])) {
            return $this->meta['twitter:title'];
        }

        $title = $this->doc->getElementsByTagName('title');
        if ($title->length > 0) {
            return trim($title->item(0)->textContent);
        }

        return '';
    }

    private function getAuthor(): string
    {
        //     <meta name="author" content="Peter Forret"/>
        if (isset($this->meta['author'])) {
            return $this->meta['author'];
        }

        //     <meta property="article:author" content="UK Film Review"/>
        if (isset($this->meta['article:author'])) {
            return $this->meta['article:author'];
        }

        //     <meta name="twitter:creator" content="@ReallyAndi">
        if (isset($this->meta['twitter:creator'])) {
            return $this->meta['twitter:creator'];
        }


        return '';
    }

    private function getDate(): string
    {
        //     <meta property="article:published_time" content="2024-05-19"/>
        if (isset($this->meta['article:published_time'])) {
            return $this->meta['article:published_time'];
        }

        //     <meta property="article:published" content="2024-05-19"/>
        if (isset($this->meta['article:published'])) {
            return $this->meta['article:published'];
        }

        //     <meta name="date" content="2024-05-19"/>
        if (isset($this->meta['date'])) {
            return $this->meta['date'];
        }

        //                 <time class="code text-astro-gray-200" datetime="2023-03-07T00:00:00.000Z"> March 7, 2023</time>
        $tags = $this->doc->getElementsByTagName('time');
        foreach ($tags as $tag) {
            if ($tag->getAttribute('datetime')) {
                return date("Y-m-d",strtotime($tag->getAttribute('datetime')));
            }
        }

        return '';
    }

    private function getImages(): array
    {
        $tags = $this->doc->getElementsByTagName('img');
        $images = [];
        foreach ($tags as $tag) {
            $src = $tag->getAttribute('src');
            if(!$src){
                continue;
            }
            $src = Cleanup::relativeToAbsoluteUrl($src, $this->canonical);
            if(!$src){
                continue;
            }
            $images[$src] = $src;
        }

        sort($images);
        return $images;
    }

    private function getLinks(bool $externalOnly = false): array
    {
        $tags = $this->doc->getElementsByTagName('a');
        $links = [];
        $internalDomain = parse_url($this->canonical, PHP_URL_HOST);
        foreach ($tags as $tag) {
            $href = $tag->getAttribute('href');
            if(!$href) {
                continue;
            }
            $href = Cleanup::relativeToAbsoluteUrl($href, $this->canonical);
            if(!$href) {
                continue;
            }
            if($externalOnly && parse_url($href, PHP_URL_HOST) == $internalDomain) {
                continue;
            }
            $links[$href] = $href;
        }
        sort($links);
        return $links;
    }

    /**
     * @throws ParseException
     */
    private function getContent(): string
    {
        $configuration = new Configuration;
        $document = new Readability($configuration);
        $html = HtmlManipulator::cleanup($this->html);
        $document->parse($html);
        return Cleanup::extractText($document->getContent());
    }

    private function getSummary(): string
    {
        //     <meta name="twitter:description"
        //          content="A look at how different web frameworks perform in the real world in 2023. Based on real-world, production data from HTTP Archive and Google Chrome.">
        if (isset($this->meta['twitter:description'])) {
            return $this->meta['twitter:description'];
        }

        //   <meta property="og:description"
        //        content="A look at how different web frameworks perform in the real world in 2023. Based on real-world, production data from HTTP Archive and Google Chrome.">
        if (isset($this->meta['og:description'])) {
            return $this->meta['og:description'];
        }

        //     <meta name="description"
        //          content="A look at how different web frameworks perform in the real world in 2023. Based on real-world, production data from HTTP Archive and Google Chrome.">
        if (isset($this->meta['description'])) {
            return $this->meta['description'];
        }

        return '';

    }


}
