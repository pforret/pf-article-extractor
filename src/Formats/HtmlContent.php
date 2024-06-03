<?php

namespace Pforret\PfArticleExtractor\Formats;

use DOMDocument;
use DOMNode;
use Pforret\PfArticleExtractor\Helpers\TextManipulator;
use Pforret\PfArticleExtractor\Naming\TextLabels;

class HtmlContent
{
    private ?TextDocument $textDocument = null;

    private ?TextBlock $textBlock = null;

    private bool $isBody = false;

    private bool $isTitle = false;

    private bool $isDate = false;

    private string $title = '';

    private string $date = '';

    private array $labels = [
        'li' => [TextLabels::LI],
        'h1' => [TextLabels::HEADING, TextLabels::H1],
        'h2' => [TextLabels::HEADING, TextLabels::H2],
        'h3' => [TextLabels::HEADING, TextLabels::H3],
    ];

    public function __construct(string $html)
    {
        $html = preg_replace('/<(span)(.*?)>/', '', $html);
        $html = preg_replace('/<\/(span)>/', '', $html);

        $this->textDocument = new TextDocument();

        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($html);
        libxml_clear_errors();
        $this->walkNodeTree($dom->documentElement);

        if ($this->textBlock) {
            $this->textDocument->addTextBlock($this->textBlock);
        }
        $this->textDocument->setTitle($this->title);
        $this->textDocument->setDate($this->date);
    }

    final public function getTextDocument(): TextDocument
    {
        return $this->textDocument;
    }

    private function walkNodeTree(DOMNode $element, int $level = 0, bool $isAnchor = false): void
    {
        $tag = null;
        if ($element->nodeType == XML_ELEMENT_NODE) {
            $tag = strtolower($element->tagName);
            if ($tag == 'body') {
                // from now on, we're in the <body> tag, where the content lives
                $this->isBody = true;
            }
            $this->isTitle = ($tag == 'title');
        }

        if ($this->isBody) {
            if ($element->nodeType == XML_ELEMENT_NODE) {
                if ($tag == 'a') {
                    $href = $element->attributes->getNamedItem('href');
                    $isAnchor = $href ? $href->nodeValue : false;
                } else {
                    if ($this->textBlock) {
                        $this->textDocument->addTextBlock($this->textBlock);
                        $this->textBlock->parseImages($element->ownerDocument->saveHTML($element));
                        $this->textBlock->parseLinks($element->ownerDocument->saveHTML($element));
                    }
                    $labels = $this->labels[$tag] ?? [];
                    $this->textBlock = new TextBlock($level, $labels);
                }
            } elseif ($element->nodeType == XML_TEXT_NODE) {
                $element->data = trim($element->data);
                if (str_ends_with($element->data, '.')
                    || str_ends_with($element->data, ',')
                    || str_ends_with($element->data, ';')
                    || str_ends_with($element->data, '?')
                    || str_ends_with($element->data, '!')) {
                    $element->data = $element->data.' ';
                }
                $textLine = TextManipulator::justTheText($element->data);
                $textTitle = TextManipulator::justTheText($this->title);
                if ($textLine != $textTitle &&
                    ! str_starts_with($textLine, $textTitle) &&
                    ! str_starts_with($textTitle, $textLine)
                ) {
                    $this->textBlock->addText($element->data, $isAnchor);
                }
                if (mb_strlen($element->data) < 100) {
                    $publishDate = TextManipulator::findDate($element->data);
                    if ($publishDate) {
                        $this->date = $publishDate;
                        $this->isDate = true;
                    }

                }
            }
        } elseif ($this->isTitle) {
            if ($element->nodeType == XML_TEXT_NODE) {
                $element->data = trim($element->data);
                if ($element->data && (TextManipulator::justTheText($element->data) != TextManipulator::justTheText($this->title))) {
                    // avoid double title (when it's both a <title> and a <h1>,<h2> for instance)
                    $this->title .= $element->data;
                }
            }
        }

        if ($element->hasChildNodes()) {
            foreach ($element->childNodes as $node) {
                $this->walkNodeTree($node, $level + 1, $isAnchor);
            }
        }
    }
}
