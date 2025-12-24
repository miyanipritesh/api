<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");

$dataFile = __DIR__ . "/data_app_list.json";

if (!file_exists($dataFile)) {
    file_put_contents($dataFile, json_encode(["app_list" => []], JSON_PRETTY_PRINT));
}

$data = json_decode(file_get_contents($dataFile), true);

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    foreach ($data['app_list'] as $app) {
        if ($app['id'] == $id) {
            echo json_encode($app);
            exit;
        }
    }
    echo json_encode(["message" => "App not found"]);
} else {
    echo json_encode([
        "app_list" => $data['app_list'],
        "message" => "Success"
    ]);
}