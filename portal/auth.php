<?php
session_start();
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

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
