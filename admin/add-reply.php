<?php
session_start();
include('include/db_config.php');

if (empty($_SESSION['name'])) {
    header('Location:index.php');
    exit;
}

// fetch comments details
if (isset($_GET['id'])) {
	$cmt_id = isset($_GET['id']) ? $_GET['id'] : '';
	$query = "SELECT * FROM course_comment WHERE id ='$cmt_id'";
	$result_blog = mysqli_query($conn, $query);
	$info_blog = mysqli_fetch_object($result_blog);
}


// submit admin comment

// Admin reply to a comment
if (isset($_POST['submit'])) {
    $course_name = mysqli_real_escape_string($conn, $_POST['course_name']);
    $comment_id = intval($_POST['comment_id']);
    $user_id = intval($_POST['user_id']);
    $user_comment = mysqli_real_escape_string($conn, $_POST['user_comment']);
    $reply = mysqli_real_escape_string($conn, $_POST['reply']);

    // Insert the admin's reply into the admin_comment table
    $sql = "INSERT INTO admin_comment (course_name, comment_id, user_id, reply, user_comment) 
            VALUES (?, ?, ?, ?, ?)";

    if ($stmt = mysqli_prepare($conn, $sql)) {
        // Bind the parameters
        mysqli_stmt_bind_param($stmt, "siiss", $course_name, $comment_id, $user_id, $reply, $user_comment);

        // Execute the statement
        if (mysqli_stmt_execute($stmt)) {
            // After the reply is added, send a notification to the user

            $notification_title = "New Reply to Your Comment";
            $notification_message = " comment on the course '$course_name'  reply from the admin: '$reply'.";
            $notification_status = 'unread';
            $created_at = date('Y-m-d H:i:s'); // Set current timestamp for notification creation

            // Insert the notification into the notifications table
            $notification_query = "INSERT INTO notifications (user_id, title, message, status, created_at) 
                                   VALUES (?, ?, ?, ?, ?)";
            $stmt_notification = $conn->prepare($notification_query);
            $stmt_notification->bind_param("issss", $user_id, $notification_title, $notification_message, $notification_status, $created_at);
            $stmt_notification->execute();

            // Optionally, you can redirect after successful notification insert
            exit("<script>window.location.href='view-course-comment.php?id=Added';</script>");
        } else {
            echo "<p>Error submitting the reply: " . mysqli_error($conn) . "</p>";
        }

        // Close the statement
        mysqli_stmt_close($stmt);
    } else {
        echo "<p>Error preparing query: " . mysqli_error($conn) . "</p>";
    }
}



?>

<?php
    include("include/db_config.php");
    
    // Check if request is POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reply_id'])) {
        $reply_id = intval($_POST['reply_id']); // Sanitize input
    
        // Perform deletion
        $query = "DELETE FROM admin_comment WHERE id = $reply_id";
        $result = mysqli_query($conn, $query);
        if (!$result) {
            error_log('MySQL Error: ' . mysqli_error($conn));
        }
        if ($result) {
            echo json_encode(['status'=>'success']);
            exit;
        } else {
            echo json_encode(['status'=>'error', 'message' => 'Failed to delete the comment.']);
        }
    } 
    ?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Add Reply on comment</title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <link rel="stylesheet" href="bower_components/bootstrap/dist/css/bootstrap.min.css">
       <link rel="icon" href="<?=$base_url;?>assets/img/logo-fav.png" type="image/png">
    <link rel="stylesheet" href="bower_components/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="dist/css/AdminLTE.min.css">
    <link rel="stylesheet" href="dist/css/skins/_all-skins.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css" rel="stylesheet" />
</head>
<body class="hold-transition skin-blue sidebar-mini">
    <div class="wrapper">
        <?php include('include/header.php');?>
        <?php include('include/side-bar.php');?>

        <div class="content-wrapper" style="margin-top:35px;padding-left:28px">
            
            <section class="content">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Add Reply on comment</h3>
                    </div>
                    <form method="post">
                        <div class="box-body">
                          
                            <div class="form-group">
                                <label for="title">Course Name:</label>
                                <input type="text" name="course_name" id="course_name" class="form-control"  value="<?= $info_blog->course_name; ?>" readonly>
                            </div>
                            <div class="form-group">
                                <label for="title">User Id:</label>
                                <input type="text" name="user_id" id="user_id" class="form-control"  value="<?= $info_blog->user_id; ?>" readonly>
                            </div>
                            <div class="form-group">
                                <label for="title">User Comment:</label>
                                <input type="text" name="user_comment" id="user_comment" class="form-control" disable value="<?= $info_blog->comment; ?>" readonly>
                            </div>
                                <input type="hidden" name="comment_id" id="comment_id" class="form-control" disable value="<?= $info_blog->id; ?>" readonly>
                           <?php
                           
                           include('include/db_config.php');
                           // fetch admin reply comments details
                            if (isset($_GET['id'])) {
                            	$amt_id = isset($_GET['id']) ? $_GET['id'] : '';
                            	$query = "SELECT * FROM admin_comment WHERE comment_id ='$amt_id'";
                            	$result_admin = mysqli_query($conn, $query);
                              if ($result_admin && mysqli_num_rows($result_admin) > 0) {
                                // Loop through the results
                                echo '<h5>Your Replies on this comment</h5><hr> <ul class="list-group">';
                                while ($info_admin = mysqli_fetch_object($result_admin)) {
                                ?>
                               
                                <li class="list-group-item" id="comment-row-<?= $info_admin->id?>"><?= htmlspecialchars($info_admin->reply);?>
                                <button type="button" style="border:none;float:right;" 
                                        onclick="deleteAdminReply(<?= $info_admin->id; ?>)">
                                    <i class="fa fa-trash" style="color:red;" title="Delete"></i>
                                </button>

                                </li>
                               
                                
                            <?php
                                }
                                echo ' </ul>';
                              }
                            }
                           ?>
                            
                            <div class="form-group">
                                <label for="message">Reply:</label>
                                <textarea name="reply" id="reply" class="form-control" rows="4" required></textarea>
                            </div>
                        </div>
                        <div class="box-footer">
                            <input type="submit" name="submit" value="Submit" class="btn btn-primary">
                        </div>
                    </form>
                </div>
            </section>
        </div>
    </div>

    <script src="bower_components/jquery/dist/jquery.min.js"></script>
    <script src="bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js"></script>
    <script>
      function deleteAdminReply(reply_id) {
    if (confirm('Are you sure you want to delete this comment?')) {
        fetch('add-reply.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'reply_id=' + encodeURIComponent(reply_id)
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                alert('Comment deleted successfully.');
                // Optionally, remove the deleted row from the UI
                document.getElementById('comment-row-' + reply_id).remove();
            } else {
                alert('Failed to delete the comment.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while deleting the comment.');
        });
    }
}

    </script>
</body>
</html>
