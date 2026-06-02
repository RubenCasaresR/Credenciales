<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$url = $_GET['url'] ?? '';

if (empty($url)) {
    echo json_encode(['success' => false, 'error' => 'URL is required']);
    exit;
}

if (!filter_var($url, FILTER_VALIDATE_URL)) {
    echo json_encode(['success' => false, 'error' => 'Invalid URL']);
    exit;
}

$scheme = parse_url($url, PHP_URL_SCHEME);
if (!in_array($scheme, ['http', 'https'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid scheme']);
    exit;
}

$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'timeout' => 5,
        'header' => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36\r\n"
    ],
    'ssl' => [
        'verify_peer' => false,
        'verify_peer_name' => false
    ]
]);

$html = @file_get_contents($url, false, $context);

if ($html === false) {
    echo json_encode(['success' => false, 'error' => 'Could not fetch URL']);
    exit;
}

libxml_use_internal_errors(true);
$doc = new DOMDocument();
$doc->loadHTML($html);
libxml_clear_errors();

$metas = $doc->getElementsByTagName('meta');
$ogImage = '';
$twitterImage = '';

foreach ($metas as $meta) {
    $property = $meta->getAttribute('property');
    $name = $meta->getAttribute('name');
    $content = $meta->getAttribute('content');

    if (strtolower(trim($property)) === 'og:image' && !empty($content)) {
        $ogImage = $content;
    }
    if (strtolower(trim($name)) === 'twitter:image' && !empty($content)) {
        $twitterImage = $content;
    }
}

$result = !empty($ogImage) ? $ogImage : $twitterImage;

if (!empty($result)) {
    if (strpos($result, '//') === 0) {
        $result = parse_url($url, PHP_URL_SCHEME) . ':' . $result;
    } elseif (strpos($result, '/') === 0) {
        $parsed = parse_url($url);
        $result = $parsed['scheme'] . '://' . $parsed['host'] . $result;
    } elseif (stripos($result, 'http') !== 0) {
        $base = rtrim($url, '/');
        $result = $base . '/' . ltrim($result, '/');
    }
}

if (!empty($result)) {
    echo json_encode(['success' => true, 'og_image' => $result]);
} else {
    echo json_encode(['success' => false, 'error' => 'No OG image found']);
}
?>
