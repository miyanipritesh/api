<?php
// get_links.php

header('Content-Type: application/json; charset=utf-8');

// Optional: enable CORS if frontend on different origin (adjust domain in production)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

// path to your text file
$linksFile = __DIR__ . '/links.txt';

if (!file_exists($linksFile)) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Links file not found.'
    ]);
    exit;
}

$contents = file($linksFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$links = [];

// parse lines; ignore comments starting with # and trim whitespace
foreach ($contents as $line) {
    $line = trim($line);
    if ($line === '' || strpos($line, '#') === 0) continue;

    // basic normalization: if it doesn't have scheme, add http://
    if (!preg_match('#^https?://#i', $line)) {
        $line = 'http://' . $line;
    }

    // validate URL
    if (filter_var($line, FILTER_VALIDATE_URL)) {
        $links[] = $line;
    }
}

// optional: support query param to filter domain or substring, e.g. ?q=example
if (isset($_GET['q']) && $_GET['q'] !== '') {
    $q = strtolower(trim($_GET['q']));
    $links = array_values(array_filter($links, function($u) use ($q) {
        return strpos(strtolower($u), $q) !== false;
    }));
}

echo json_encode([
    'success' => true,
    'count' => count($links),
    'links' => $links
]);
