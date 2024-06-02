<?php

namespace Pforret\PfArticleExtractor\Formats;

use DOMDocument;

final class TextBlock
{
    private int $level = 0;

    private bool $isContent = false;

    private string $text = '';
    private array $texts = [];

    private array $images = [];

    private array $links = [];

    private array $labels = [];

    private int $startOffset = 0;

    private int $endOffset = 0;

    private int $linkCount = 0;

    private int $linkWordCount = 0;

    private int $wordCount = 0;


    public function __construct(int $level = 0, array $labels = [])
    {
        $this->level = $level;
        if ($labels) {
            foreach ($labels as $label) {
                $this->labels[$label] = true;
            }
        }
    }

    public function parseImages(string $html): self
    {
        $doc = new DOMDocument();
        @$doc->loadHTML($html);
        $tags = $doc->getElementsByTagName('img');
        foreach ($tags as $tag) {
            $url = $tag->getAttribute('src');
            if (! $url) {
                continue;
            }
            if (strpos($url, 'http') !== 0) {
                continue;
            }
            if($this->isIrrelevantPicture($url)){
                continue;
            }
            $url = str_replace("&amp;","&",$url);
            $this->images[] = $url;
        }

        return $this;
    }

    public function parseLinks(string $html): self
    {
        $doc = new DOMDocument();
        @$doc->loadHTML($html);
        $tags = $doc->getElementsByTagName('a');
        foreach ($tags as $tag) {
            $url = $tag->getAttribute('href');
            if (! $url) {
                continue;
            }
            if (!str_starts_with($url, 'http')) {
                continue;
            }
            $this->links[] = $tag->getAttribute('href');
        }

        return $this;
    }

    public function addText(string $text, string $link = ''): self
    {
        if(str_contains($text, 'xxxxxxxxxxxxxxtribune')){
           print($text);
        }
        if (trim($text)) {
            $this->text .= "$text ";
            $this->texts[] = $link ? '<a href="'.$link.'">'.$text.'</a>' : $text;

            $wordCount = $this->calcWordCount($text);
            $this->wordCount += $wordCount;
            if ($link) {
                $this->linkCount++;
                $this->linkWordCount += $wordCount;
            }
        }

        return $this;
    }

    private function calcWordCount(string $text): int
    {
        $words = $text;
        $words = preg_replace('/\s[.\-]+\s/u', ' ', $words);
        $words = preg_replace('/[^.\-\p{L}\p{Nd}\p{Nl}\p{No}]+/u', ' ', $words);
        $words = preg_replace('/\s+/', ' ', $words);
        $words = trim($words, " \t\n\r\0\x0B.-");
        $words = explode(' ', $words);

        return count($words);
    }

    public function mergeNext(TextBlock $block): self
    {
        $this->text .= ' '.trim($block->getText());
        $this->texts = $this->texts + $this->getTexts();

        $this->wordCount += $block->getWordCount();
        $this->linkCount += $block->getLinkCount();
        $this->linkWordCount = $block->getLinkWordCount();

        $this->startOffset = min($this->startOffset, $block->getStartOffset());
        $this->endOffset = max($this->endOffset, $block->getEndOffset());

        $this->isContent = $this->isContent || $block->isContent();
        $this->labels = $this->labels + $block->getLabels();

        $this->level = min($this->level, $block->getLevel());

        return $this;
    }

    public function getText(): string
    {
        $text = $this->text;
        $text = str_replace("\n", ' ', $text);
        $text = preg_replace('/\s\s+/', ' ', $text);

        return trim($text);
    }

    public function getTexts(): array
    {
        return $this->texts;
    }

    public function getWordCount(): int
    {
        return $this->wordCount;
    }

    public function getLinkCount(): int
    {
        return $this->linkCount;
    }

    public function getLinkWordCount(): int
    {
        return $this->linkWordCount;
    }

    public function getStartOffset(): int
    {
        return $this->startOffset;
    }

    public function setStartOffset(int $startOffset): self
    {
        $this->startOffset = $startOffset;

        return $this;
    }

    public function getEndOffset(): int
    {
        return $this->endOffset;
    }

    public function setEndOffset(int $endOffset): self
    {
        $this->endOffset = $endOffset;

        return $this;
    }

    public function isContent(): bool
    {
        return $this->isContent;
    }

    public function setIsContent(string $value): string
    {
        $result = ($this->isContent != $value);
        $this->isContent = $value;

        return $result;
    }

    public function getLabels(): array
    {
        return $this->labels;
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    public function addLabel(string $label): self
    {
        $this->labels[$label] = true;

        return $this;
    }

    public function hasLabel(string $label): bool
    {
        return isset($this->labels[$label]);
    }

    public function getWrappedLineCount(): int
    {
        return count($this->getWrappedLines());
    }

    private function getWrappedLines(): array
    {
        return explode(PHP_EOL, wordwrap($this->text, 80, PHP_EOL));
    }

    public function isEmpty(): bool
    {
        return ! $this->texts;
    }

    public function getImages(): array
    {
        return array_unique($this->images);
    }

    public function getLinks(): array
    {
        return array_unique($this->links);
    }

    public function __toString(): string
    {
        return
            '['.$this->startOffset.'-'.$this->endOffset.
            ';tl='.$this->getLevel().
            ';nw='.$this->getWordCount().
            ';td='.$this->getTextDensity().
            ';nwl='.count($this->getWrappedLines()).
            ';ld='.$this->getLinkDensity().']'.
            "\t".($this->isContent ? 'CONTENT' : 'boilerplate').','.json_encode($this->labels).
            "\n".$this->getText();
    }

    public function getTextDensity(): float
    {
        $wrappedLines = $this->getWrappedLines();
        $numWrappedLines = count($wrappedLines);
        $numWordsInWrappedLines = $this->getWordCount();
        if ($numWrappedLines > 1) {
            $numWordsInWrappedLines = $numWordsInWrappedLines - $this->calcWordCount($wrappedLines[$numWrappedLines - 1]);
        }

        return $numWordsInWrappedLines / $this->getLineCount();
    }

    public function getLineCount(): int
    {
        $lineCount = count($this->getWrappedLines()) - 1;

        return max($lineCount, 1);
    }

    public function getLinkDensity(): float
    {
        return $this->linkWordCount ? $this->linkWordCount / $this->wordCount : 0;
    }

    private function isIrrelevantPicture(string $url): bool
    {
        $isIrrelevant = false;
        $detectBasenames = [
            'blank.gif',
            'pixel.gif',
            'pixel.jpeg',
            'pixel.jpg',
            'pixel.png',
            'pixel.svg',
            'pixel.webp',
            'spacer.gif',
            'spacer.jpeg',
            'spacer.jpg',
            'spacer.png',
            'spacer.svg',
            'spacer.webp',
            'transparent.gif',
            'transparent.jpeg',
            'transparent.jpg',
            'transparent.png',
            'transparent.svg',
            'transparent.webp',
        ];

        $isIrrelevant = $isIrrelevant || in_array(strtolower(basename($url)), $detectBasenames);
        $detectDomains = [
            "cdn.jsdelivr.net"
        ];
        $isIrrelevant = $isIrrelevant || in_array(parse_url($url,PHP_URL_HOST), $detectDomains);

        return $isIrrelevant;
    }
}
