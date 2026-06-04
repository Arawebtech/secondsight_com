<?php
session_start();
include('admin/include/db_config.php');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $batchcode = trim($_POST['enterbatchcode']);
    
    if (empty($batchcode)) {
        echo json_encode(['status' => 'error', 'message' => 'Please enter a batch code']);
        exit;
    }
    
    // Check if batch code exists and is active
    $query = "
    SELECT bc.*, b.batch_title, b.max_students
    FROM batchcode bc
    JOIN batch b ON bc.batch_id = b.id
    WHERE bc.batchcode_name = ? AND bc.status = 'Active' AND bc.expiry_date >= CURDATE()
    ";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $batchcode);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid, inactive, or expired batch code']);
        exit;
    }
    
    $batch_data = $result->fetch_assoc();
    $batch_id = $batch_data['batch_id'];
    
    // Check if user is already enrolled in this batch
    $check_query = "SELECT * FROM user_batch_enrollments WHERE user_id = ? AND batch_id = ? AND status = 'Active'";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bind_param("ii", $user_id, $batch_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Enrolled in this batch.']);
        exit;
    }
    
    // Check if batch has reached maximum enrollment
    $enrollment_count_query = "SELECT COUNT(*) as enrolled_count FROM user_batch_enrollments WHERE batch_id = ? AND status = 'Active'";
    $enrollment_count_stmt = $conn->prepare($enrollment_count_query);
    $enrollment_count_stmt->bind_param("i", $batch_id);
    $enrollment_count_stmt->execute();
    $enrollment_count_result = $enrollment_count_stmt->get_result();
    $enrollment_count_data = $enrollment_count_result->fetch_assoc();
    
    if ($enrollment_count_data['enrolled_count'] >= $batch_data['max_students']) {
        echo json_encode(['status' => 'error', 'message' => 'This batch is full']);
        exit;
    }
    
    // Check if batchcode has reached maximum usage
    if ($batch_data['current_usage'] >= $batch_data['max_usage']) {
        echo json_encode(['status' => 'error', 'message' => 'This batch code has reached its maximum usage limit']);
        exit;
    }
    
    // Start transaction
    $conn->autocommit(FALSE);
    
    try {
        // Enroll user in the batch
        $enroll_query = "INSERT INTO user_batch_enrollments (user_id, batch_id, batchcode_id, status) VALUES (?, ?, ?, 'Active')";
        $enroll_stmt = $conn->prepare($enroll_query);
        $enroll_stmt->bind_param("iii", $user_id, $batch_id, $batch_data['id']);
        
        if ($enroll_stmt->execute()) {
            // Update the usage count in batchcode table
            $update_query = "UPDATE batchcode SET current_usage = current_usage + 1 WHERE id = ?";
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bind_param("i", $batch_data['id']);
            $update_stmt->execute();
            
            // Commit transaction
            $conn->commit();
            $conn->autocommit(TRUE);
            
            echo json_encode([
                'status' => 'success', 
                'message' => 'Successfully enrolled in the batch! You can now access all lessons in this batch.',
                'redirect' => 'profile.php'
            ]);
        } else {
            throw new Exception('Failed to enroll user');
        }
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        $conn->autocommit(TRUE);
        echo json_encode(['status' => 'error', 'message' => 'Failed to enroll. Please try again.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>