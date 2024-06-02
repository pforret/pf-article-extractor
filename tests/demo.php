<?php

// Usage: php demo.php <url>
// Example: php demo.php https://www.bbc.com/news/world-asia-58480487

use Pforret\PfArticleExtractor\ArticleExtractor;

include __DIR__.'/../vendor/autoload.php';
if ($argc < 2) {
    echo "Usage: php demo.php <url>\n";
    echo "Example: php demo.php https://www.bbc.com/news/world-asia-58480487\n";
    exit;
}
$url = $argv[1];
$html = httpGet1_1($url);
print_r(ArticleExtractor::getArticle($html));
exit();

function httpGet1_1(string $url): string
{
    // use curl to get html for a URL
    // allow forcing to HTTP 1.1 to avoid problems with HTTP2
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.3');
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
    $html = curl_exec($ch);
    curl_close($ch);

    return $html;
}
