<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$_ksm = ['host'=>'localhost', 'user'=>'root', 'pass'=>'', 'name'=>'ksm_database'];

function ksm_db() {
    global $_ksm;
    static $c = null;
    if ($c) return $c;
    $c = new mysqli($_ksm['host'], $_ksm['user'], $_ksm['pass'], $_ksm['name']);
    if ($c->connect_error) {
        http_response_code(500);
        die(json_encode(['success' => false, 'error' => $c->connect_error]));
    }
    $c->set_charset('utf8mb4');
    return $c;
}

$r = ksm_db()->query("SELECT * FROM events ORDER BY date ASC");
$rows = [];
if ($r) {
    while ($row = $r->fetch_assoc()) {
        $rows[] = $row;
    }
}
echo json_encode(['success' => true, 'data' => $rows]);
