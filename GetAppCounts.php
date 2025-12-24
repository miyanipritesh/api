<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");

$dataFile = __DIR__ . "/data_app_counts.json";

if (!file_exists($dataFile)) {
    file_put_contents($dataFile, json_encode(["counts" => []], JSON_PRETTY_PRINT));
}

$data = json_decode(file_get_contents($dataFile), true);

if (!isset($_GET['id'])) {
    echo json_encode(["message" => "Missing id"]);
    exit;
}

$id = (int)$_GET['id'];
foreach ($data['counts'] as $c) {
    if ($c['id'] == $id) {
        echo json_encode($c);
        exit;
    }
}
echo json_encode(["message" => "Count not found"]);