<?php
require_once __DIR__ . '/../auth.php';
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['ksm_student_auth']) && !isset($_SESSION['ksm_staff_auth'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
    exit;
}

$body = json_decode(file_get_contents('php://input'), true);
if (!$body || empty($body['image'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No image data provided.']);
    exit;
}

$base64_string = $body['image'];
$split = explode(',', $base64_string);
if (count($split) !== 2) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid image format.']);
    exit;
}

$data = base64_decode($split[1]);
if ($data === false) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Base64 decode failed.']);
    exit;
}

$extension = 'jpg';
if (strpos($split[0], 'image/png') !== false) {
    $extension = 'png';
} else if (strpos($split[0], 'image/gif') !== false) {
    $extension = 'gif';
} else if (strpos($split[0], 'image/webp') !== false) {
    $extension = 'webp';
}

$filename = 'profile_' . time() . '_' . rand(1000, 9999) . '.' . $extension;
$upload_dir = '../../assets/uploads/profile_pics/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

$filepath = $upload_dir . $filename;
if (file_put_contents($filepath, $data)) {
    // Return relative URL from the perspective of portal/{staff,student}/profile.php
    $publicUrl = '../../assets/uploads/profile_pics/' . $filename;
    echo json_encode(['success' => true, 'data' => ['url' => $publicUrl], 'message' => 'Image uploaded successfully.']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to save image to disk.']);
}
