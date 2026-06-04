<?php
error_reporting(0);
session_start();
include('include/db_config.php');
include("Classes/users.class.php");

$userdata = new users();

if (isset($_POST['submit']) && $_POST['submit'] == 'Sign in') {
    $name = $_POST['name'];
    $password = $_POST['password'];

    $result_user = $userdata->userLogin($name, $password);

    if ($result_user === FALSE) {
        die("Error: " . $conn->error);
    }

    $count_user = $result_user->num_rows;

    if ($count_user > 0) {
        $row = mysqli_fetch_object($result_user);
        
        $_SESSION['name'] = $row->name;
        $_SESSION['is_admin'] =true;

        echo "<script>window.open('dashboard.php','_self')</script>";
    } else {
        $error = '<div class="alert alert-danger" role="alert">Username or password is incorrect.</div>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Second Sight Foundation Login</title>
       <link rel="icon" href="<?=$base_url;?>assets/img/logo-fav.png" type="image/png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            background: url('../assets/image/backgrounds/banner1.webp') no-repeat center center fixed;
            background-size: cover;
            font-family: 'Nunito', sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }
        .login-form {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 15px;
            padding: 40px;
            width: 350px;
            text-align: center;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }
        .login-form h2 {
            font-family: 'Playfair Display', serif;
            color: #4B4B4B;
            margin-bottom: 20px;
        }
        .input-field {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 2px solid #A1C45A;
            border-radius: 25px;
            outline: none;
            box-sizing: border-box;
            background-color: #F8F8F8;
        }
        .password-field {
            position: relative;
            width: 100%;
            margin-bottom: 10px;
        }
        .password-field input {
            width: 100%;
            padding: 12px;
            border: 2px solid #A1C45A;
            border-radius: 25px;
            outline: none;
            box-sizing: border-box;
            background-color: #F8F8F8;
            padding-right: 40px;
        }
        .password-field .toggle-password {
            position: absolute;
            top: 50%;
            right: 10px;
            transform: translateY(-50%);
            cursor: pointer;
            color: #4B4B4B;
        }
        .login-button {
            width: 100%;
            padding: 12px;
            margin-top: 20px;
            border: none;
            border-radius: 25px;
            background: linear-gradient(to right, #A1C45A, #F4A261);
            color: white;
            font-size: 18px;
            cursor: pointer;
        }
        .login-button:hover {
            background: linear-gradient(to right, #F4A261, #A1C45A);
        }
        .remember-me {
            display: flex;
            align-items: center;
            margin-top: 10px;
            justify-content: flex-start;
        }
        .remember-me input {
            margin-right: 5px;
        }
        
        
        @media only screen and (max-width: 767px) {
    .enroll-wrap {
        /*padding: 20px;*/
        margin-top: 35px;
    }
}
    </style>
</head>
<body>
    <div class="login-form">
        <h2>Welcome to Second Sight Foundation Admin Portal</h2>
        <?php if (isset($error)) { echo $error; } ?>
        <form method="post">
            <input type="text" name="name" class="input-field" placeholder="Username" required>
            <div class="password-field">
                <input type="password" name="password" id="password" class="input-field" placeholder="Password" required>
                <i class="fas fa-eye toggle-password" id="togglePassword"></i>
            </div>
            <div class="remember-me">
                <input type="checkbox" id="rememberMe">
                <label for="rememberMe">Remember Me</label>
            </div>
              <div class="col-12">
                                    <div class="login-action" style="float:left;">
                                        <span class="forgot-login">
                                            <a href="forgot_password.php" style="text-decoration:none;color:#0a2987;font-size:14px;margin-top:7px;">Forgot your password?</a>
                                        </span>
                                    </div>
                                </div>
            <button type="submit" name="submit" value="Sign in" class="login-button">Login</button>
        </form>
    </div>
    <script>
        const togglePassword = document.getElementById('togglePassword');
        const passwordField = document.getElementById('password');
        togglePassword.addEventListener('click', function () {
            const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordField.setAttribute('type', type);
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });
    </script>
</body>
</html>
