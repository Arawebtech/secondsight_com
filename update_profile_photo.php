<?php
session_start();
include('admin/include/db_config.php');

header('Content-Type: application/json');

$response = [
    'success' => false,
    'message' => 'An unknown error occurred.',
    'filePath' => ''
];

if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'Authentication required. Please log in.';
    echo json_encode($response);
    exit;
}

$user_id = $_SESSION['user_id'];

if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
    
    $file_tmp_path = $_FILES['profile_photo']['tmp_name'];
    $new_file_name = 'user_' . $user_id . '_' . time() . '.jpg';
    
    // Make sure the path is relative to the web root for the URL
    $upload_dir_for_url = '/assets/img/profile/';
    // Use __DIR__ to build a reliable absolute path for the file system operation
    $upload_dir_for_fs = __DIR__ . $upload_dir_for_url;

    if (!is_dir($upload_dir_for_fs)) {
        mkdir($upload_dir_for_fs, 0755, true);
    }

    $dest_path_for_fs = $upload_dir_for_fs . $new_file_name;
    $dest_path_for_url = $upload_dir_for_url . $new_file_name;

    // Move the uploaded file
    if (move_uploaded_file($file_tmp_path, $dest_path_for_fs)) {
        // Update the user's profile photo path in the database
        $query_update = "UPDATE users SET profile_photo = ? WHERE id = ?";
        $stmt_update = $conn->prepare($query_update);
        
        if ($stmt_update) {
            $stmt_update->bind_param("si", $dest_path_for_url, $user_id);
            if ($stmt_update->execute()) {
                $_SESSION['profile_photo'] = $dest_path_for_url;
                $response['success'] = true;
                $response['message'] = 'Profile photo updated successfully!';
                // Add a cache-busting query string to the file path to avoid browser caching issues
                $response['filePath'] = $dest_path_for_url . '?v=' . time(); 
            } else {
                $response['message'] = 'Database update failed.';
            }
            $stmt_update->close();
        } else {
            $response['message'] = 'Failed to prepare database statement.';
        }
    } else {
        $response['message'] = 'Failed to move uploaded file.';
    }
} else {
    $response['message'] = 'No file uploaded or an upload error occurred.';
    if(isset($_FILES['profile_photo']['error'])) {
        $response['message'] .= ' Error code: ' . $_FILES['profile_photo']['error'];
    }
}

echo json_encode($response);
exit; 