<?php
require_once __DIR__ . '/../auth.php';
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['ksm_student_auth']) && !isset($_SESSION['ksm_staff_auth'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

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
if (strlen($base64_string) > 6 * 1024 * 1024) { // Max 6MB base64 string
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Image exceeds 3MB limit.']);
    exit;
}

$split = explode(',', $base64_string);
if (count($split) !== 2) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid image format.']);
    exit;
}

$data = base64_decode($split[1], true);
if ($data === false || strlen($data) > 3 * 1024 * 1024) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid image payload or exceeds 3MB.']);
    exit;
}

// Validate binary image integrity
$img_info = @getimagesizefromstring($data);
if (!$img_info) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Corrupt or unsupported image binary.']);
    exit;
}

$extension = 'jpg';
switch ($img_info[2]) {
    case IMAGETYPE_JPEG:
        $extension = 'jpg';
        break;
    case IMAGETYPE_PNG:
        $extension = 'png';
        break;
    case IMAGETYPE_GIF:
        $extension = 'gif';
        break;
    case IMAGETYPE_WEBP:
        $extension = 'webp';
        break;
    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Only JPG, PNG, GIF, and WEBP formats are allowed.']);
        exit;
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
