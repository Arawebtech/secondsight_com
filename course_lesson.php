<?php
session_start();
// error_reporting(E_ALL);
// ini_set('display_errors', 1);


if (!isset($_SESSION['user_id']) & !isset($_SESSION['user_name'])) { 
    // Redirect to the login page
    header("Location: /login.php");
    exit; 
}



include('admin/include/db_config.php');

$courseId = $_GET['courseId'];
$user_id = $_SESSION['user_id'];

// check user are eligible for course -- user can see only own purchase course
// Input values to check
$user_id = $_SESSION['user_id'];
$course_id = $_GET['courseId'];
$purchase_date = "";
 $profile_user="";
// SQL query
$query = "
        SELECT o.id AS order_id, o.user_id, o.created_at, od.course_id
        FROM orders o
        JOIN order_details od ON o.id = od.order_id
        WHERE o.user_id = ? AND od.course_id = ?
    ";

// Prepare the statement
$stmt = $conn->prepare($query);
if ($stmt === false) {
    die("Failed to prepare query: " . $conn->error);
}

// Bind parameters
$stmt->bind_param('ii', $user_id, $course_id);

// Execute the query
$stmt->execute();

// Get the result
$result = $stmt->get_result();

// Check if any row is returned
if ($result->num_rows > 0) {
    // Fetch the data
    $row = $result->fetch_assoc();
    $purchase_date = $row['created_at'];
    // echo "Order found: Order ID - " . $row['order_id'] . ", User ID - " . $row['user_id'] . ", Course ID - " . $row['course_id'];

} else {
    // echo "No matching order found for the given user_id and course_id.";
    header("Location: /profile.php");
    exit();
}
// end of code  --eligible----



if ($courseId > 0) {
    // SQL query to fetch video URL
    $sql = "
      SELECT 
                lv.lesson_title, 
                lv.lesson_desc,
                lv.video_url, 
                lv.status, 
                lv.batch_id,
                c.banner_image,
                c.s_name,
                c.instructor_name,
                c.inst_img,
                c.duration_time,
                c.validity
            FROM 
                lesson_video lv
            JOIN 
                courses c ON lv.course_id = c.id
            JOIN 
                order_details od ON od.course_id = c.id
            JOIN 
                `orders` o ON o.id = od.order_id
            WHERE 
                o.user_id = $user_id
                AND lv.course_id = $courseId
                AND lv.status = 'Active';";

    // Execute the query
    $result = mysqli_query($conn, $sql);
    $lesson = [];
    if (mysqli_num_rows($result) > 0) {


        // Fetch and display video URLs
        while ($row = mysqli_fetch_assoc($result)) {

            $lesson[$row['lesson_title']] = [
                'video_url' => $row['video_url'],
                'banner_img' => $row['banner_image'],
                'lesson_desc' => $row['lesson_desc']

            ];
            $course_name = $row['s_name'];
            $course_duration = $row['duration_time'];
            $instructor_img = $row['inst_img'];
            $instructor_name = $row['instructor_name'];
            $validity_months = $row['validity'];

        }

        // echo "</ul>";
    } else {
        // echo "No videos found for Course ID $courseID.";
    }
}

// calculated left days in expiry

// Convert the purchase date to a DateTime object
$purchase_date_obj = new DateTime($purchase_date);

// Add the validity months to calculate the expiration date
$expiration_date_obj = clone $purchase_date_obj;
$expiration_date_obj->modify("+$validity_months months");

// Get the current date
$current_date_obj = new DateTime();

// Calculate the number of days left
$interval = $current_date_obj->diff($expiration_date_obj);
$days_left = $interval->days;

// Display results
$expiry_date = $expiration_date_obj->format('d-m-Y');

if ($current_date_obj > $expiration_date_obj) {
    // check expiry

    // echo "The item has expired.\n";
    // echo $days_left ;
    //  header("Location: /profile.php"); 
} else {
    // echo "Days Left: $days_left days\n $expiry_date";
}
// end

// Sql query to fetch user profile photo
$sql = "SELECT profile_photo FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id); // Bind the user ID as an integer
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $profile_photo = $row['profile_photo'];
} else {
    die("User not found.");
}

//-------------- Delete course--comment----------------------------
if (isset($_SESSION['delete_comment_success'])) {
    echo "<script>alert('Comment deleted successfully!');</script>";
    unset($_SESSION['delete_comment_success']); // Unset the session variable
}

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
    
    <link rel="icon" href="<?=$base_url;?>../assets/img/logo-fav.png" type="image/png">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">




    <title><?php echo $_SESSION['user_name']; ?> | Courses</title>
    <style>
        .header-lesson {
            background: -webkit-linear-gradient(top, #ffb607 0%, #d76d0a 100%);
            background: linear-gradient(to bottom, #ffb607 0%, #d76d0a 100%);
            align-items: center;

        }

        .header-lesson h3 a {
            vertical-align: top;
            background-color: #4a3f32;
            border: none;
        }

        .header-lesson h3 a:hover {

            background-color: #645c56;
            border: none;
        }

        .video-item {
            width: 100%;
            aspect-ratio: 16 / 10;
            display: block;
            object-fit: cover;
        }

        @media only screen and (max-width:576px) {
            .box-title-video {
                display: flex;
                flex-direction: column-reverse;
            }
        }

        .list-group-item.active {
            background-color: #bd7f41;
            border: none;
        }

        body {
            background-color: #F5F5F5;
        }

        .card,
        .list-group {
            background-color: #fff;
            background: linear-gradient(133.21deg, #f7f7f7 -2.44%, #f9f9f9 135.62%);
            box-shadow: -6px -6px 8px rgba(255, 255, 255, 0.8), -2px -1px 8px #ffffff,
                2px 2px 10px rgba(255, 255, 255, 0.25),
                -4px -4px 20px rgba(255, 255, 255, 0.8),
                1px 1px 5px rgba(185, 185, 185, 0.6), 4px 4px 15px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            box-sizing: border-box;
            border: none;
        }

        .course-comment {
            display: flex;
            flex-direction: row;
            align-items: center;
            justify-content: center;
        }

        /* video css*/
        .custom-video-player {
            position: relative;
            width: 100%;

            /*max-width: 800px;*/
            /*margin: auto;*/
        }

        iframe {
            width: 100%;
            aspect-ratio: 16 / 10;
        }

        .video-item {
            width: 100%;
            display: block;
        }

        .custom-controls {
            z-index: 2147483647;
            position: absolute;
            bottom: 25px;
            left: 127px;
            transform: translateX(-50%);
            display: flex;
            align-items: center;
            gap: 10px;
            background: #000;
            padding: 5px 15px;
            border-radius: 5px;
        }

        .control-btn {
            background: #ff5722;
            color: #fff;
            border: none;
            font-size: 16px;
            cursor: pointer;
            padding: 10px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            transition: background 0.3s ease, transform 0.2s ease;
        }

        .control-btn:hover {
            background: #e64a19;
            transform: scale(1.1);
        }

        @media only screen and (max-width:576px) {
            .control-btn {
                font-size: 12px;
                padding: 6px;
            }

            .custom-controls {
                padding: 3px 10px;
                left: 108px;
            }
        }

        .video-time {
            font-size: 14px;
            color: #fff;
            padding: 5px 10px;
            background: rgba(0, 0, 0, 0.5);
            border-radius: 5px;
            min-width: 60px;
            text-align: center;
        }

     

        /* Prevent native fullscreen controls overlap */
        video:fullscreen {
            z-index: 1;
        }
    </style>
</head>

<body>

    <div class="header-lesson border d-flex" style="height:100px;position:relative;">
        <h3 class="container text-white">
            <a class="btn btn-primary" href="<?php $base_url; ?>/profile.php">My Courses</a>
        </h3>
            
        <?php
        $delete_comment="";
        if ($delete_comment == true) {
            ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert"
                style="position:absolute;top;25px;right:25px;">
                <strong>Success!</strong> You comment deleted succefully!.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php
            $delete_comment = false;
            // Redirect to the same URL
            header("Location: $current_url");
            // exit();
        
        }
        ?>
    </div>
    <?php
    if (empty($lesson)) {
        echo '<div style="margin:auto;text-align:center;">No Lesson Video are available for this course.As soon as video will updated</div>';
    }
    ?>
    <?php
    if (!empty($lesson)) {
        ?>
        <div class="container mt-4">
            <div class="row box-title-video">
                <div class="sidebar-lesson col-md-4">
                    <ul class="list-group" role="tablist" style="margin-bottom:120px;">
                        <li class="list-group-item"><b style="font-size:1.2rem;"><?php echo $course_name; ?></b> <span
                                style="float:right;"><?php echo $course_duration; ?></span></li>

                        <?php
                        $index = 0;
                        foreach ($lesson as $title => $details) {
                            ?>
                            <li class="list-group-item lesson-item" style="cursor:pointer;" data-bs-toggle="list"
                                data-bs-target="#video-<?php echo $index; ?>" role="tab"
                                aria-controls="video-<?php echo $index; ?>"
                                aria-selected="<?php echo $index === 0 ? 'true' : 'false'; ?>">

                                #<?php echo $title; ?>
                            </li>
                            <?php
                            $index++;
                        }
                        ?>
                    </ul>
                </div>

                <div class="video-lesson col-md-8 tab-content">
                    <?php
                    $index = 0;
                    foreach ($lesson as $title => $details) {
                        // $cleaned_url = str_replace('..', '', $details['video_url']);
                        $videoUrl = $details['video_url'];
                        // Convert YouTube URL to embed format
                        if (strpos($videoUrl, 'youtube.com/shorts/') !== false) {
                            $videoId = substr($videoUrl, strrpos($videoUrl, '/') + 1);
                            $videoUrl = "https://www.youtube.com/embed/{$videoId}";
                        } elseif (strpos($videoUrl, 'youtube.com/watch?v=') !== false) {
                            parse_str(parse_url($videoUrl, PHP_URL_QUERY), $queryParams);
                            if (isset($queryParams['v'])) {
                                $videoId = $queryParams['v'];
                                $videoUrl = "https://www.youtube.com/embed/{$videoId}";
                            }
                        }

                        ?>
                        <div id="video-<?php echo $index; ?>"
                            class="tab-pane fade <?php echo $index === 0 ? 'show active' : ''; ?> video-container"
                            role="tabpanel" aria-labelledby="lesson-<?php echo $index; ?>">
                            <div class="custom-video-player">
                                <iframe src="<?php echo $videoUrl; ?>" frameborder="0"
                                    allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture"
                                    allowfullscreen></iframe>
                            </div>
                            <div>
                                <h5 class="mt-2">#<?php echo $title; ?></h5>
                                <div>
                                    <img src="<?= $base_url; ?>/assets/img/instructors/<?php echo $instructor_img; ?>"
                                        alt="instructor-img" style="max-width:65px;display:inline-block;">
                                    <h6 style="line-height:55px; display:inline-block;"><?= $instructor_name; ?></h6>
                                    <div style="display:inline-block; float:right;font-size:0.9rem;">
                                        <span>Course Expire on <?php echo $expiry_date; ?></span>
                                        <br>
                                        <span><?php echo $days_left; ?> Days left</span>
                                    </div>
                                    <div class="card mb-4 mt-3" style="clear:both;">
                                        <div class="card-body">
                                            <b>Lesson Overview</b><br>
                                            <?php echo $details['lesson_desc']; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php
                        $index++;
                    }
                    ?>

                    <!--Display user commments-->
                    <div class="comment-list">
                        <?php
                        include('admin/include/db_config.php');
                        //   check comment exist or not 
                        $sqlcheck = "SELECT * FROM course_comment WHERE user_id = '$user_id' AND course_id = '$courseId'";

                        $result = mysqli_query($conn, $sqlcheck);

                        // Check if the query was successful
                        if ($result) {
                            $row_count = mysqli_num_rows($result); // Count the rows
                    
                            if ($row_count > 0) {
                                echo '<h4>Your Comments</h4><hr>';
                            }
                        }

                        // SQL query to fetch user comments and details
                        $sql = "
                                    SELECT 
                                        course_comment.id AS comment_id, 
                                        course_comment.comment, 
                                        course_comment.course_id, 
                                        course_comment.created_date, 
                                        users.name, 
                                        users.profile_photo
                                    FROM 
                                        course_comment
                                    INNER JOIN 
                                        users 
                                    ON 
                                        course_comment.user_id = users.id
                                    WHERE 
                                        course_comment.user_id = $user_id AND course_comment.course_id = $course_id
                                ";

                        $result = mysqli_query($conn, $sql);

                        if ($result) {
                            // Loop through user comments
                            while ($row = mysqli_fetch_assoc($result)) {
                                $comment_id = $row['comment_id'];
                                $comment = $row['comment'];
                                $created_date = $row['created_date'];
                                $name = $row['name'];
                                $profile_photo = $row['profile_photo'];
                                $profile_user = $row['profile_photo']; 
                                $formatted_date = date("d-m-Y", strtotime($created_date));

                                ?>
                                <!-- User Comment -->
                                <div>
                                    <img src="<?= $base_url ?>/<?= $profile_photo !== null ? $profile_photo : 'assets/img/profile/dpf.png'; ?>"
                                        alt="Profile Image"
                                        style="height:40px; width:40px; object-fit: cover; border-radius:50%; vertical-align: sub;"
                                        class="img-fluid">
                                    <p style="display:inline-block; padding-left:1rem;">
                                        <small><b><?= htmlspecialchars($name) ?></b></small> <br>
                                        <span><?= htmlspecialchars($comment) ?></span>
                                    </p>
                                    <span style="float:right; font-size:0.85rem;"><?= htmlspecialchars($formatted_date) ?>
                                    <form method="POST" action="">
                                        <input type="hidden" name="comment_id" value="<?= $comment_id; ?>">
                                         <input type="hidden" name="comment_name_edit" value="<?= $comment; ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <button type="submit" style="border:none;background-color: transparent;"
                                            onclick="return confirm('Are you sure you want to delete this comment?')">
                                            <i class="fa fa-trash" style="color:red;" title="Delete"></i>
                                        </button>
                                         <button type="button" style="border:none;background-color: transparent;" data-comment="<?= htmlspecialchars($comment) ?>">
                                            <i class="fa fa-edit" style="color:blue;" title="Edit"></i>
                                        </button>
                                    </form>

                                    

                                      <!-- Edit Comment Modal -->
                                    <div class="modal fade" id="editCommentModal" tabindex="-1" aria-labelledby="editCommentModalLabel" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="editCommentModalLabel">Edit Comment</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <textarea id="editCommentText" class="form-control" rows="3"></textarea>
                                                        <input type="hidden" id="editCommentId" value="">
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                        <button type="submit" class="btn btn-primary" id="saveEditComment">Save changes</button>
                                                    </div>
                                                </div>
                                            </div>
                                    </div> 
                                    
                                </div>

                                    <!-- Admin Replies -->
                                    <?php
                                    // SQL query to fetch admin replies for the current comment
                                    $admin_sql = "SELECT reply, created_date FROM admin_comment WHERE comment_id = $comment_id";
                                    $admin_result = mysqli_query($conn, $admin_sql);
    
                                    if ($admin_result) {
                                        // Loop through admin replies
                                        while ($admin_row = mysqli_fetch_assoc($admin_result)) {
                                            $reply = $admin_row['reply'];
                                            $admin_created_date = $admin_row['created_date'];
                                            $formatted_admin_date = date("d-m-Y", strtotime($admin_created_date));
                                            ?>
                                            <div class="ps-5">
                                                <img src="<?= $base_url; ?>/assets/img/logo-fav.png" alt="Profile Image"
                                                    style="height:40px; width:40px; object-fit: cover; border-radius:50%; vertical-align: sub;"
                                                    class="img-fluid">
                                                <p style="display:inline-block; padding-left:1rem;">
                                                    <small><b>Admin</b></small> <br>
                                                    <span><?= htmlspecialchars($reply) ?></span>
                                                </p>
                                                <span
                                                    style="float:right; font-size:0.85rem;"><?= htmlspecialchars($formatted_admin_date) ?></span>
                                            </div>
                                            <?php
                                        }
                                    } else {
                                        echo "<p>Error executing the admin replies query: " . mysqli_error($conn) . "</p>";
                                    }
                                    ?>
                                    <?php
                                    }
                                } else {
                                    echo "<p>Error executing the user comments query: " . mysqli_error($conn) . "</p>";
                                }
                                ?>

                          
                        </div>

                    <!--comment box code-->
                    <form id="commentForm" method="POST">
                        <div class="course-comment" style="margin-bottom:60px;">
                            <div class="col-md-1 col-2">

                                <img src="<?= $base_url ?>/<?= $profile_photo !== null ? $profile_photo : 'assets/img/profile/dpf.png'; ?>"
                                    alt="Profile Image"
                                    style="height:50px; width:50px; object-fit: cover; border-radius:50%" class="img-fluid">
                            </div>
                            <div class="col-md-10 col-8">
                                <input type="hidden" id="user_id" name="user_id" value="<?php echo $user_id; ?>">
                                <input type="hidden" id="course_id" name="course_id" value="<?php echo $courseId; ?>">
                                <input type="hidden" id="course_name" name="course_name"
                                    value="<?php echo $course_name; ?>">
                                <textarea class="form-control" style="width:100%;" id="comment" rows="1" placeholder="Your Comment"
                                    name="comment" required></textarea>
                            </div>
                            <div class="col-md-1 col-2">
                                <button style="inline-block" class="btn btn-success" type="submit">Submit</button>

                            </div>

                        </div>
                    </form>
                </div>
            </div>
        </div>

        <?php

    }
    ?>

</body>

 

<!--<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>-->
<!-- Bootstrap JS and Popper.js (needed for dropdowns, tooltips, etc.) -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>

<script src="https://www.secondsight.arawebtechnologies.com/assets/js/jquery.min.js"></script>


<script>
    // Get all video elements
    const videos = document.querySelectorAll('.video-item');

    // Listen for tab switches
    document.querySelectorAll('.lesson-item').forEach(item => {
        item.addEventListener('click', () => {
            // Pause all videos
            videos.forEach(video => {
                video.pause();
            });
        });
        var tabElements = document.querySelectorAll('[data-bs-toggle=list]');
        var tabInstances = [...tabElements].map(tab => new bootstrap.Tab(tab));
        tabInstances[0].show();
    });
    document.querySelectorAll('.lesson-item').forEach((item) => {
        item.addEventListener('click', () => {
            activeVideo = document.querySelector('.video-container.show .video-item');
        });
    });

   


    //------- submit comment to db
    $(document).ready(function () {

        // Handle form submission
        $('#commentForm').on('submit', function (e) {
            e.preventDefault(); // Prevent form from reloading the page

            const formData = {
                user_id: $('#user_id').val(),
                course_id: $('#course_id').val(),
                course_name: $('#course_name').val(),
                comment: $('#comment').val(),

                action: 'submit_comment'
            };

            $.ajax({
                url: '/comment-form.php', // Empty to send to the same PHP page
                method: 'POST',
                data: formData,
                success: function (response) {
                    $('#comment').val('');
                    alert(response);
                    location.reload();
                    // Display success alert

                },
                error: function (xhr, status, error) {
                    console.error(error);
                    alert('Error submitting the comment.');
                }
            });
        });
    });


// edit comment

// JavaScript to handle edit comment
$(document).on('click', '.fa-edit', function() {
    // Get the comment ID from the closest form
    const commentId = $(this).closest('form').find('input[name="comment_id"]').val(); // Get the comment ID
    const commentText = $(this).closest('form').find('input[name="comment_name_edit"]').val();
  
    // Set the values in the modal
    $('#editCommentId').val(commentId);
    $('#editCommentText').val(commentText);
    
    // Show the modal
    $('#editCommentModal').modal('show');
});

// Save changes button click
$('#saveEditComment').on('click', function() {
    const commentId = $('#editCommentId').val(); // Get the comment ID from the hidden input
    const updatedComment = $('#editCommentText').val(); // Get the updated comment text

    $.ajax({
        url: '/comment-form.php', // Your PHP file to handle the edit
        method: 'POST',
        data: {
            action: 'edit',
            comment_id: commentId,
            updated_comment: updatedComment
        },
        success: function(response) {
            alert('Comment updated successfully!');
            location.reload(); // Reload the page to see the updated comment
        },
        error: function(xhr, status, error) {
            console.error(error);
            alert('Error updating the comment.');
        }
    });
});

// delete comment js
// JavaScript to handle delete comment
$(document).on('click', '.fa-trash', function(e) {
    e.preventDefault(); // Prevent the default form submission

    const commentId = $(this).closest('form').find('input[name="comment_id"]').val(); // Get the comment ID

    
        $.ajax({
            url: '/comment-form.php', // Your PHP file to handle the delete
            method: 'POST',
            data: {
                action: 'delete',
                comment_id: commentId
            },
            success: function(response) {
                const result = JSON.parse(response); // Parse the JSON response
                if (result.status === 'success') {
                   
                    location.reload(); 
                } else {
                    // alert(result.message); // Show error message
                }
            },
            error: function(xhr, status, error) {
                console.error(error);
                alert('Error deleting the comment.');
            }
        });
    
});
</script>

</html>