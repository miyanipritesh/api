<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: PUT, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === "OPTIONS") {
    exit(0);
}

$dataFile = __DIR__ . "/data_app_counts.json";

if (!file_exists($dataFile)) {
    file_put_contents($dataFile, json_encode(["counts" => []], JSON_PRETTY_PRINT));
}

$data = json_decode(file_get_contents($dataFile), true);
$input = json_decode(file_get_contents("php://input"), true);

if (!isset($_GET['id'])) {
    echo json_encode(["message" => "Missing id"]);
    exit;
}

$id = (int)$_GET['id'];
$found = false;

foreach ($data['counts'] as &$c) {
    if ($c['id'] == $id) {
        foreach ($input as $k => $v) {
            if ($k != "id") $c[$k] = $v;
        }
        $found = true;
        break;
    }
}

if (!$found) {
    echo json_encode(["message" => "ID not found"]);
} else {
    file_put_contents($dataFile, json_encode($data, JSON_PRETTY_PRINT));
    echo json_encode(["message" => "Updated"]);
}