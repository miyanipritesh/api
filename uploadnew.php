<?php
header("Content-Type: application/json");

// JSON file path
$jsonFile = __DIR__ . "/data_app_list.json";

// Upload directory
$uploadDir = __DIR__ . "/uploads/";

// Ensure directory exists
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Set timezone to India
date_default_timezone_set("Asia/Kolkata");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['id']) && isset($_FILES['file'])) {
        $id = intval($_POST['id']);
        $file = $_FILES['file'];

        // Load JSON first
        if (file_exists($jsonFile)) {
            $json = file_get_contents($jsonFile);
            $data = json_decode($json, true);
        } else {
            echo json_encode(["message" => "JSON file not found", "file" => null]);
            exit;
        }

        // Check if app with given ID exists
        $appIndex = null;
        foreach ($data["app_list"] as $index => $app) {
            if ($app["id"] == $id) {
                $appIndex = $index;
                break;
            }
        }

        if ($appIndex === null) {
            echo json_encode(["message" => "App ID not found", "file" => null]);
            exit;
        }

        // Generate unique filename => rec_{id}_{date_time}_{uniqid}.{ext}
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $timestamp = date("d-m-Y_h-i-s");
        $uniqueHash = substr(uniqid(), -8);
        $fileName = "rec_" . $id . "_" . $timestamp . "_" . $uniqueHash . "." . $ext;
        $filePath = $uploadDir . $fileName;

        if (move_uploaded_file($file['tmp_name'], $filePath)) {

            // Ensure the "recording_files" array exists
            if (!isset($data["app_list"][$appIndex]["recording_files"])) {
                $data["app_list"][$appIndex]["recording_files"] = [];
            }

            // Append the new filename to the list
            $data["app_list"][$appIndex]["recording_files"][] = $fileName;

            // Save updated JSON
            file_put_contents($jsonFile, json_encode($data, JSON_PRETTY_PRINT));

            echo json_encode([
                "message" => "File uploaded successfully",
                "file" => $fileName,
                "total_files" => count($data["app_list"][$appIndex]["recording_files"]),
                "recording_files" => $data["app_list"][$appIndex]["recording_files"]
            ]);
        } else {
            echo json_encode(["message" => "File upload failed", "file" => null]);
        }

    } else {
        echo json_encode(["message" => "Invalid request", "file" => null]);
    }
} else {
    echo json_encode(["message" => "Only POST method allowed", "file" => null]);
}
?>
