<?php
// Database connection
session_start();

include('admin/include/db_config.php');



if (isset($_GET['comment_id'])) {
    $comment_id = intval($_GET['comment_id']); // Sanitize the comment_id

    // Query to get the existing comment
    $query = "SELECT comment FROM course_comment WHERE id = ?";
    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("i", $comment_id); // Bind the comment_id to the query
        $stmt->execute(); // Execute the query
        $stmt->bind_result($current_comment); // Bind the result to the $current_comment variable
        $stmt->fetch(); // Fetch the result
        $stmt->close(); // Close the statement

        // Check if comment exists
        if (empty($current_comment)) {
            echo "Comment not found.";
            exit;
        }
    } else {
        echo "Error preparing query: " . $conn->error;
        exit;
    }
} else {
    echo "No comment ID provided.";
    exit;
}


// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    // Get the updated comment from the form
    $updated_comment = $_POST['updated_comment'];

    // Update query
    $query = "UPDATE course_comment SET comment = ? WHERE id = ?";
    if ($stmt = $conn->prepare($query)) {
        // Bind parameters
        $stmt->bind_param("si", $updated_comment, $comment_id);

        // Execute the query
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                echo "Comment updated successfully!";
            } else {
                echo "No changes made.";
            }
        } else {
            echo "Error executing query: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Error preparing query: " . $conn->error;
    }
}
?>

<!-- HTML Form for editing the comment -->


