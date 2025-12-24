<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");

$dataFile = __DIR__ . "/data_gestures.json";

if (!file_exists($dataFile)) {
    file_put_contents($dataFile, json_encode(["gestures" => []], JSON_PRETTY_PRINT));
}

echo file_get_contents($dataFile);