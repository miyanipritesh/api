<?php
header('Content-Type: application/json; charset=utf-8');

// ---------- CONFIG ----------
$IP_STORE_FILE     = __DIR__ . '/ips.txt';   // path to store IPs
$FILE_PERMISSIONS  = 0644;                   // file permission on create
$ALLOW_PRIVATE     = false;                  // true = private/reserved IPs bhi allow
// ----------------------------

// Validate IP according to $allow_private flag
function is_valid_ip($ip, $allow_private = false) {
    if (!filter_var($ip, FILTER_VALIDATE_IP)) {
        return false;
    }
    if ($allow_private) {
        return true;
    }
    return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false;
}

// Read `ip` parameter from POST or GET (frontend must send it)
if (!empty($_POST['ip'])) {
    $ipToUse = trim($_POST['ip']);
} elseif (!empty($_GET['ip'])) {
    $ipToUse = trim($_GET['ip']);
} else {
    http_response_code(400);
    echo json_encode([
        'error' => true,
        'message' => 'No IP provided in request'
    ]);
    exit;
}

// Validate the passed IP
// if (!is_valid_ip($ipToUse, $ALLOW_PRIVATE)) {
//     http_response_code(400);
//     echo json_encode([
//         'error' => true,
//         'message' => 'Invalid IP provided'
//     ]);
//     exit;
// }

// Ensure storage file exists
if (!file_exists($IP_STORE_FILE)) {
    file_put_contents($IP_STORE_FILE, "");
    @chmod($IP_STORE_FILE, $FILE_PERMISSIONS);
}

// Check & store logic
$existed = false;
$stored  = false;

$fp = fopen($IP_STORE_FILE, 'c+');
if (!$fp) {
    http_response_code(500);
    echo json_encode(['error' => true, 'message' => 'Unable to open storage file']);
    exit;
}

if (flock($fp, LOCK_EX)) {
    rewind($fp);
    $contents = stream_get_contents($fp);
    $lines = array_filter(
    array_map('trim', explode(PHP_EOL, $contents)),
    function ($l) {
        return $l !== '';
    }
);

    if (in_array($ipToUse, $lines, true)) {
        $existed = true;
    } else {
        // append IP
        fseek($fp, 0, SEEK_END);
        if (strlen($contents) > 0 && substr($contents, -1) !== PHP_EOL) {
            fwrite($fp, PHP_EOL);
        }
        fwrite($fp, $ipToUse . PHP_EOL);
        fflush($fp);
        $stored = true;
    }

    flock($fp, LOCK_UN);
} else {
    http_response_code(500);
    echo json_encode(['error' => true, 'message' => 'Could not lock storage file']);
    fclose($fp);
    exit;
}
fclose($fp);

// Response
echo json_encode([
    'error'  => false,
    'ip'     => $ipToUse,
    'existed'=> $existed,
    'stored' => $stored
]);
