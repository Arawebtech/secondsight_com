<?php

session_start();

include('admin/include/db_config.php');

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if ($_POST['action'] === 'submit_comment') {
            
              
            // Validate input and session variables
            $comment = $conn->real_escape_string($_POST['comment']);
            $course_id =  $conn->real_escape_string($_POST['course_id']);
            $course_name = $conn->real_escape_string($_POST['course_name']);
            $user_id =  $conn->real_escape_string($_POST['user_id']);
            
          
            // Check for missing required fields
         
                $query = "INSERT INTO course_comment (comment, course_id, course_name, user_id) 
                          VALUES ('$comment', '$course_id', '$course_name', '$user_id')";
                if ($conn->query($query)) {
                    echo 'Comment submitted successfully!';
                } else {
                    echo 'Database error: ' . $conn->error;
                }
        }
    }
    if (isset($_POST['action'])) {
        $action = $_POST['action'];

        if ($action === 'edit') {
            // Handle the edit action
            $comment_id = intval($_POST['comment_id']); // Get the comment ID
            $updated_comment = $conn->real_escape_string($_POST['updated_comment']); // Get the updated comment

            // Prepare the SQL query to update the comment
            $query = "UPDATE course_comment SET comment = ? WHERE id = ?";
            if ($stmt = $conn->prepare($query)) {
                $stmt->bind_param("si", $updated_comment, $comment_id); // Bind parameters
                if ($stmt->execute()) {
                    if ($stmt->affected_rows > 0) {
                        // Successfully updated the comment
                        echo "Comment updated successfully!";
                    } else {
                        // No changes made (comment text was the same)
                        echo "No changes made.";
                    }
                } else {
                    // Error executing the query
                    echo "Error executing query: " . $stmt->error;
                }
                $stmt->close(); // Close the statement
            } else {
                // Error preparing the query
                echo "Error preparing query: " . $conn->error;
            }
        }
        // Other actions (like delete) can be handled here as well
    }
    
    if (isset($_POST['action'])) {
        $action = $_POST['action'];

        if ($action === 'delete') {
            // Handle the delete action
            $comment_id = intval($_POST['comment_id']);

            $query = "DELETE FROM course_comment WHERE id = ?";
            if ($stmt = $conn->prepare($query)) {
                $stmt->bind_param("i", $comment_id);
                if ($stmt->execute()) {
                    // Return a JSON response
                    echo json_encode(['status' => 'success', 'message' => 'Comment deleted successfully!']);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Error executing query: ' . $stmt->error]);
                }
                $stmt->close();
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Error preparing query: ' . $conn->error]);
            }
        }
    }

?>