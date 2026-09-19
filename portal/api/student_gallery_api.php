<?php
$_ksm = ['host' => 'localhost', 'user' => 'root', 'pass' => '', 'name' => 'ksm_database'];
function ksm_db(){global $_ksm;static $c=null;if($c)return $c;$c=new mysqli($_ksm['host'],$_ksm['user'],$_ksm['pass'],$_ksm['name']);if($c->connect_error){http_response_code(500);die(json_encode(['error'=>$c->connect_error]));}$c->set_charset('utf8mb4');return $c;}
function ksm_json($d,$m='OK',$code=200){header('Content-Type: application/json');http_response_code($code);echo json_encode(['success'=>$code<400,'message'=>$m,'data'=>$d]);exit;}
function ksm_err($m,$code=400){ksm_json(null,$m,$code);}
function ksm_esc($v){return ksm_db()->real_escape_string(trim($v??''));}
header('Access-Control-Allow-Origin: *');header('Access-Control-Allow-Methods: GET,POST,PUT,DELETE,OPTIONS');header('Access-Control-Allow-Headers: Content-Type,X-Requested-With');
if(($_SERVER['REQUEST_METHOD']??'')==='OPTIONS')exit;

$method=$_SERVER['REQUEST_METHOD']??'GET';
$body=json_decode(file_get_contents('php://input'),true)??[];

if($method==='GET'){
  $student_id = intval($_GET['student_id']??0);
  if(!$student_id) ksm_err('student_id required.');
  $r = ksm_db()->query("SELECT * FROM student_gallery WHERE student_id=$student_id ORDER BY id ASC");
  $rows=[];
  while($row=$r->fetch_assoc()) $rows[]=$row;
  ksm_json($rows);
}
if($method==='POST'){
  $student_id = intval($body['student_id']??0);
  $url = ksm_esc($body['url']??'');
  $cap = ksm_esc($body['caption']??'');
  if(!$student_id || !$url) ksm_err('student_id and URL required.');
  ksm_db()->query("INSERT INTO student_gallery(student_id, url, caption) VALUES($student_id, '$url', '$cap')");
  ksm_json(['id'=>ksm_db()->insert_id], 'Photo added to personal gallery.');
}
if($method==='DELETE'){
  $id = intval($_GET['id']??0);
  if(!$id) ksm_err('Invalid ID.');
  ksm_db()->query("DELETE FROM student_gallery WHERE id=$id");
  ksm_json(null,'Deleted.');
}
?>
