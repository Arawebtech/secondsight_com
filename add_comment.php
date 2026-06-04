<?php
session_start();
include('admin/include/db_config.php');

header('Content-Type: application/json');
$response = [
    'success' => false,
    'message' => 'An unknown error occurred.',
    'comment' => []
];

/*
IMPORTANT: Before this script can work, you need to add a 'rating' and 'course_id' column to your 'course_comment' table.
You can use the following SQL queries to alter your table:

ALTER TABLE `course_comment` ADD `rating` TINYINT(1) NULL DEFAULT NULL AFTER `comment`;
ALTER TABLE `course_comment` ADD `course_id` INT(11) NULL DEFAULT NULL AFTER `user_id`;

Make sure to adjust the data types and positions if needed.
*/

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'Authentication required.';
    echo json_encode($response);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // --- Get form data ---
    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
    $course_id = isset($_POST['course_id']) ? intval($_POST['course_id']) : 0;
    $comment_text = isset($_POST['comment']) ? trim($_POST['comment']) : '';
    $rating = isset($_POST['rating']) ? intval($_POST['rating']) : null;

    // --- Basic validation ---
    if (empty($user_id) || empty($course_id)) {
        $response['message'] = 'Invalid user or course data.';
        echo json_encode($response);
        exit();
    }
    
    if (empty($comment_text) && is_null($rating)) {
        $response['message'] = 'Please provide a rating or a comment.';
        echo json_encode($response);
        exit();
    }

    if (!is_null($rating) && ($rating < 1 || $rating > 5)) {
        $response['message'] = 'Invalid rating value provided.';
        echo json_encode($response);
        exit();
    }

    // --- Prepare and execute insert statement ---
    $query = "INSERT INTO course_comment (user_id, course_id, course_name, comment, rating, created_date) VALUES (?, ?, (SELECT s_name FROM courses WHERE id = ?), ?, ?, NOW())";
    
    $stmt = $conn->prepare($query);

    if ($stmt) {
        $stmt->bind_param("iiisi", $user_id, $course_id, $course_id, $comment_text, $rating);

        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = 'Review submitted successfully!';
            $response['comment'] = [
                'name' => $_SESSION['user_name'],
                'created_date' => date('F j, Y'),
                'rating' => $rating,
                'comment' => nl2br(htmlspecialchars($comment_text))
            ];
        } else {
            $response['message'] = 'Database error: Could not execute statement.';
        }
        $stmt->close();
    } else {
        $response['message'] = 'Database error: Could not prepare statement.';
    }

    $conn->close();

} else {
    $response['message'] = 'Invalid request method.';
}

echo json_encode($response);
exit();
?> 