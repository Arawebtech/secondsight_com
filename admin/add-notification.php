<?php
session_start();
include('include/db_config.php');

if (empty($_SESSION['name'])) {
    header('Location:index.php');
    exit;
}



if (isset($_POST['submit']) && $_POST['submit'] == 'Send') {
    $user_ids = $_POST['user_ids']; // Array of selected student IDs
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);

    if (in_array('all', $user_ids)) {
        // 'All Students' selected, fetch all student IDs
        $users_query = mysqli_query($conn, "SELECT id FROM users");
        $user_ids = [];
        while ($user = mysqli_fetch_assoc($users_query)) {
            $user_ids[] = $user['id'];
        }
    }

    // Insert notification for each selected user
    foreach ($user_ids as $user_id) {
        $query = "INSERT INTO notifications (user_id, title, message) VALUES ('$user_id', '$title', '$message')";
        $result = mysqli_query($conn, $query);
    }

    if ($result) {
        echo "<script>alert('Notification added successfully!');</script>";
        
    } else {
        echo "<script>alert('Error adding notification.');</script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Add Notification</title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
       <link rel="icon" href="<?=$base_url;?>assets/img/logo-fav.png" type="image/png">
    <link rel="stylesheet" href="bower_components/bootstrap/dist/css/bootstrap.min.css">
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
                        <h3 class="box-title">Add Notification</h3>
                    </div>
                    <form method="post">
                        <div class="box-body">
                            <!--<div class="form-group">-->
                            <!--    <label for="search_students">Search Students:</label>-->
                            <!--    <input type="text" id="search_students" class="form-control" placeholder="Type to search...">-->
                            <!--</div>-->
                            <div class="form-group">
                                <label for="user_ids">Select Students:</label>
                                <select name="user_ids[]" id="user_ids" class="form-control" multiple>
                                    <option value="all">All Students</option>
                                    <?php
                                    $users_query = mysqli_query($conn, "SELECT id, name FROM users");
                                    while ($user = mysqli_fetch_assoc($users_query)) {
                                        echo "<option value='{$user['id']}'>{$user['name']}</option>";
                                    }
                                    ?>
                                </select>
                               
                            </div>
                            <div class="form-group">
                                <label for="title">Notification Title:</label>
                                <input type="text" name="title" id="title" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="message">Message:</label>
                                <textarea name="message" id="message" class="form-control" rows="4" required></textarea>
                            </div>
                        </div>
                        <div class="box-footer">
                            <a href="view-notification.php" type="button" class="btn btn-warning">Back</a>
                            <input type="submit" name="submit" value="Send" class="btn btn-primary">
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
        $(document).ready(function() {
            $('#user_ids').select2();

            // Attach a keyup event listener to the search input
            $('#search_students').on('keyup', function() {
                var searchValue = $(this).val();

                // Make an AJAX request to fetch filtered students
                $.ajax({
                    url: 'fetch_students.php', // A separate PHP script to handle search
                    type: 'GET',
                    data: { search: searchValue },
                    success: function(response) {
                        // Update the select options dynamically
                        $('#user_ids').html(response).trigger('change');
                    }
                });
            });
        });
    </script>
</body>
</html>
