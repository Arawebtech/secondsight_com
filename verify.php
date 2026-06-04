<?php
include('admin/include/db_config.php');

if(isset($_GET['token'])){
    $token = $_GET['token'];

    // Find the user by token
    $query = "SELECT * FROM users WHERE token = '$token'";
    $result = mysqli_query($conn, $query);

    if(mysqli_num_rows($result) > 0){
        $user = mysqli_fetch_assoc($result);

        if($user['is_verify'] == 0){
            // Mark the user as verified
            $update_query = "UPDATE users SET is_verify = 1  WHERE id = " . $user['id'];
            if(mysqli_query($conn, $update_query)){
                echo "<script>
                        alert('Your email has been verified successfully!');
                        window.location.href = 'login.php';
                      </script>";
            } else {
                echo "<script>alert('Verification failed. Please try again.');</script>";
            }
        } else {
            echo "<script>alert('Your account is already verified.');window.location.href = 'login.php';</script>";
        }
    } else {
        echo "<script>alert('Invalid or expired verification link.');window.location.href = 'login.php';window.location.href = 'register.php';</script>";
    }
} else {
    echo "<script>alert('No verification token provided.');window.location.href = 'register.php';</script>";
}
?>
