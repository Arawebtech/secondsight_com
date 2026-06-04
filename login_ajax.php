<?php

session_start();
include('admin/include/db_config.php');

$data = json_decode(file_get_contents("php://input"), true);

$email = mysqli_real_escape_string($conn, $data['email']);
$password = mysqli_real_escape_string($conn, $data['password']);

$query = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");

if(mysqli_num_rows($query) > 0){

    $user = mysqli_fetch_assoc($query);

    if($password === $user['password']){

        // 🔥 SAME SESSION VARIABLES JO login.php ME HAIN

        $session_id = uniqid() . '_' . time() . '_' . rand(1000, 9999);

        $update = $conn->prepare("UPDATE users SET user_session_id=? WHERE id=?");
        $update->bind_param("si", $session_id, $user['id']);
        $update->execute();

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['profile_photo'] = $user['profile_photo'];
        $_SESSION['user_session_id'] = $session_id;

        echo json_encode(["status"=>"success"]);
        exit();

    } else {
        echo json_encode(["status"=>"wrong_password"]);
        exit();
    }

} else {
    echo json_encode(["status"=>"not_found"]);
    exit();
}