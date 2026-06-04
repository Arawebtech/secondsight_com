<?php
// session_start();
// $data = json_decode(file_get_contents('php://input'), true);
// $otp = $data['otp'] ?? null;
// $email = $data['email'] ?? null;

// if ($otp && $email && $_SESSION['otp'] == $otp) {
   
//     echo json_encode(['valid' => true, 'message' => 'OTP verified successfully!']);
// } else {
   
//     echo json_encode(['valid' => false, 'message' => 'Invalid OTP']);
// }




// Assuming you've already connected to your database

    $data = json_decode(file_get_contents('php://input'), true);
    
    $email = $data['email'];
    $otp = $data['otp'];
    
    // Verify OTP (you need to validate the OTP sent to the email)
    $is_otp_valid = verify_otp($email, $otp); // Function to check OTP validity
    
    if ($is_otp_valid) {
        // Check if email exists in the database
        $query = "SELECT * FROM users WHERE email = :email";
        $stmt = $pdo->prepare($query);
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if ($user) {
            // If user exists, return the user details
            echo json_encode(['valid' => true, 'user_exists' => true, 'user' => $user]);
        } else {
            // If user does not exist, return a message for registration
            echo json_encode(['valid' => true, 'user_exists' => false]);
        }
    } else {
        echo json_encode(['valid' => false, 'message' => 'Invalid OTP']);
    }
    
    function verify_otp($email, $otp) {
       session_start();
    $data = json_decode(file_get_contents('php://input'), true);
    $otp = $data['otp'] ?? null;
    $email = $data['email'] ?? null;
    
    if ($otp && $email && $_SESSION['otp'] == $otp) {
       $_SESSION['email'] = $email;
        echo json_encode(['valid' => true, 'message' => 'OTP verified successfully!']);
    } else {
       
        echo json_encode(['valid' => false, 'message' => 'Invalid OTP']);
    }
    
        return true; // Assuming OTP verification logic is implemented
    }
    

?>
