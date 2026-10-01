<?php
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_samesite', 'Strict');
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        ini_set('session.cookie_secure', 1);
    }
    session_start();
}
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: strict-origin-when-cross-origin');
header("Content-Security-Policy: default-src 'self' 'unsafe-inline' 'unsafe-eval' data: blob: https:; img-src 'self' data: blob: http: https:;");
header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
header('Permissions-Policy: camera=(), microphone=(), geolocation=()');

function is_admin_logged_in() {
    return isset($_SESSION['ksm_admin_auth']) && $_SESSION['ksm_admin_auth'] === true;
}

function check_admin_auth() {
    if (!is_admin_logged_in()) {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false || isset($_GET['_api'])) {
            header('Content-Type: application/json');
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized access. Please login.']);
            exit;
        } else {
            header("Location: login.php");
            exit;
        }
    }
}

function is_student_logged_in() {
    return isset($_SESSION['ksm_student_auth']) && !empty($_SESSION['ksm_student_auth']);
}

function check_student_auth() {
    if (!is_student_logged_in()) {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false || isset($_GET['_api'])) {
            header('Content-Type: application/json');
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized access. Please login.']);
            exit;
        } else {
            header("Location: login.php");
            exit;
        }
    }
}

function is_staff_logged_in() {
    return isset($_SESSION['ksm_staff_auth']) && !empty($_SESSION['ksm_staff_auth']);
}

function check_staff_auth() {
    if (!is_staff_logged_in()) {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false || isset($_GET['_api'])) {
            header('Content-Type: application/json');
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized access. Please login.']);
            exit;
        } else {
            header("Location: login.php");
            exit;
        }
    }
}
?>
