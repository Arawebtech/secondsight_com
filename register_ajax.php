<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include(__DIR__ . '/admin/include/db_config.php');

header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

if(!$data){
    echo json_encode(["status"=>"error","message"=>"No data"]);
    exit();
}

$first_name = mysqli_real_escape_string($conn, $data['first_name'] ?? '');
$email = mysqli_real_escape_string($conn, $data['email'] ?? '');
$mobile = mysqli_real_escape_string($conn, $data['mobile'] ?? '');
$password = mysqli_real_escape_string($conn, $data['password'] ?? '');


if(empty($first_name) || empty($email) || empty($password)){
    echo json_encode(["status"=>"error","message"=>"Missing fields"]);
    exit();
}

$check = mysqli_query($conn,"SELECT id FROM users WHERE email='$email'");

if(mysqli_num_rows($check)>0){
    echo json_encode(["status"=>"exists"]);
    exit();
}

$insert = mysqli_query($conn,
"INSERT INTO users (name,email,password,mobile,is_verify,is_active) 
VALUES ('$first_name','$email','$password','$mobile',1,1)");

if(!$insert){
    echo json_encode(["status"=>"error","message"=>mysqli_error($conn)]);
    exit();
}

$user_id = mysqli_insert_id($conn);

/* ===== AUTO LOGIN START ===== */

// unique session id generate karo
$session_id = uniqid() . '_' . time() . '_' . rand(1000,9999);

// database me store karo (agar column exist karta ho)
mysqli_query($conn,"UPDATE users SET user_session_id='$session_id' WHERE id='$user_id'");

// session variables set karo
$_SESSION['user_id'] = $user_id;
$_SESSION['user_name'] = $first_name;
$_SESSION['user_session_id'] = $session_id;


/* ===== AUTO LOGIN END ===== */

echo json_encode(["status"=>"success"]);
exit();