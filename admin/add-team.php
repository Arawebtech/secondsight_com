<?php
error_reporting(0);
session_start();
if (empty($_SESSION['name'])) {
    header('Location:index.php');
}
include('include/db_config.php');


global $conn;

if (isset($_POST['submit']) && $_POST['submit'] == 'Save') {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $specialisation = mysqli_real_escape_string($conn, $_POST['specialisation']);
    $about = mysqli_real_escape_string($conn, $_POST['about']);
    
     $url = mysqli_real_escape_string($conn, $_POST['url']);
    
    
    $path = $_FILES['image']['name'];
    $path_tmp = $_FILES['image']['tmp_name'];
    $valid = 1;
    $error_message = '';

    if (empty($name)) {
        $valid = 0;
        $error_message .= 'Name is required.<br>';
    }

    if (empty($specialisation)) {
        $valid = 0;
        $error_message .= 'Specialisation is required.<br>';
    }

    if ($path != '') {
        $ext = pathinfo($path, PATHINFO_EXTENSION);
        if (!in_array($ext, ['jpg', 'png', 'jpeg', 'gif', 'webp'])) {
            $valid = 0;
            $error_message .= 'You must upload a jpg, jpeg, gif, or png file.<br>';
        }
    } else {
        $valid = 0;
        $error_message .= 'You must select an image.<br>';
    }

    if ($valid) {
        move_uploaded_file($path_tmp, '../assets/img/team/' . $path);
        $created_date = date("Y-m-d H:i:s");

        $query = "INSERT INTO team (name, specialisation, about, url, image, created_date) 
                  VALUES ('$name', '$specialisation', '$about', '$url', '$path', '$created_date')";
        $result_product = mysqli_query($conn, $query);

        if ($result_product > 0) {
            exit("<script>window.location.href='view-team.php?id=Added';</script>");
        } else {
            $error = "There is some problem in inserting the record.";
        }
    }
}

if (isset($_POST['submit']) && $_POST['submit'] == 'Update') {
    $id = $_GET['id'];
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $specialisation = mysqli_real_escape_string($conn, $_POST['specialisation']);
    $about = mysqli_real_escape_string($conn, $_POST['about']);
    $url = mysqli_real_escape_string($conn, $_POST['url']);

    // Handling image upload
    $path = $_FILES['image']['name'];
    $path_tmp = $_FILES['image']['tmp_name'];
    $error_message = '';
    $valid = 1;
    $updated_image = '';

    if (empty($name)) {
        $valid = 0;
        $error_message .= 'Name is required.<br>';
    }

    if (empty($specialisation)) {
        $valid = 0;
        $error_message .= 'Specialisation is required.<br>';
    }

    // Fetch the existing image from the database if not updating it
    if (isset($_GET['id'])) {
        $image_id = $_GET['id'];
        $sql_ser = "SELECT * FROM team WHERE id = '$image_id'";
        $result_page = mysqli_query($conn, $sql_ser);
        $info_page = mysqli_fetch_object($result_page);

        if ($info_page) {
            // If no new image is uploaded, keep the existing one
            if ($path == '') {
                $updated_image = $info_page->image;
            }
        } else {
            $valid = 0;
            $error_message .= "Error fetching image details.<br>";
        }
    }

    // Check if a new image has been uploaded
    if ($path != '') {
        $ext = pathinfo($path, PATHINFO_EXTENSION);
        if (!in_array($ext, ['jpg', 'png', 'jpeg', 'gif', 'webp'])) {
            $valid = 0;
            $error_message .= 'You must upload a jpg, jpeg, gif, or png file.<br>';
        } else {
            $updated_image = $path;
            move_uploaded_file($path_tmp, '../assets/img/team/' . $path);
        }
    }

    if ($valid) {
        $upd_query = "UPDATE team SET 
                        name = '$name', 
                        specialisation = '$specialisation', 
                        about = '$about', 
                        url = '$url', 
                        image = '$updated_image' 
                      WHERE id = '$id'";

        $eventdata = mysqli_query($conn, $upd_query);

        if ($eventdata) {
            exit("<script>window.location.href='view-team.php?id=Update';</script>");
        } else {
            $error = "There is some problem in updating the record.";
        }
    } else {
        echo "<span style='color:red;'>$error_message</span>";
    }
}



if (isset($_GET['id'])) {
    $image_id = $_GET['id'];
    $sql_ser = "SELECT * FROM team WHERE id = '$image_id'";
    $result_page = mysqli_query($conn, $sql_ser);
    $info_page = mysqli_fetch_object($result_page);
}
?>


<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Add Team Member</title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
       <link rel="icon" href="<?=$base_url;?>assets/img/logo-fav.png" type="image/png">
    <link rel="stylesheet" href="bower_components/bootstrap/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="bower_components/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="bower_components/Ionicons/css/ionicons.min.css">
    <link rel="stylesheet" href="bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css">
    <link rel="stylesheet" href="dist/css/AdminLTE.min.css">
    <link rel="stylesheet" href="dist/css/skins/_all-skins.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">
</head>

<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
    <?php include('include/header.php'); ?>
    <?php include('include/side-bar.php'); ?>

    <div class="content-wrapper" style="margin-top:35px;padding-left:28px">
        <section class="content">
            <div class="row">
                <div class="col-xs-12">
                    <span style="color:red;">
                        <?php if (!empty($error)) { echo $error; } ?>
                    </span>
                    <div class="box">
                        <div class="box-body">
                            <div class="box box-primary">
                                <div class="box-header with-border">
                                    <h3 class="box-title">
                                        <?php if (isset($_GET['id'])) echo 'Update Member'; else echo 'Add Team Membaer'; ?>
                                    </h3>
                                </div>
                                <div class="box-body">
                                    <div class="row">
                                       <form action="" method="post" enctype="multipart/form-data">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="name">Name: <span style="color:red;">*</span></label>
                                                    <input type="text" name="name" id="name" value="<?php echo $info_page->name; ?>" class="form-control" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="specialisation">Specialisation: <span style="color:red;">*</span></label>
                                                    <input type="text" name="specialisation" id="specialisation" value="<?php echo $info_page->specialisation; ?>" class="form-control" required>
                                                </div>
                                            </div>
                                            
                      
                                            <div class="col-md-12">
                                                    <div class="form-group">
                                                        <label for="reporttitle">About: <span style="color:red;">
                                                                * </span></label>
                                                        <div class="">
                                                            <textarea cols="200" rows="6" class="form-control"
                                                                id="about"
                                                                name='about'><?php echo $info_page->about; ?></textarea>
                                                        </div>
                                                    </div>
                                                </div>
<script src="https://cdn.ckeditor.com/4.20.1/standard/ckeditor.js"></script>
    <script>
    CKEDITOR.replace('about');
    </script>
    <!--<div class="col-md-12">-->
    <!--    <div class="form-group">-->
    <!--        <label for="about">About: <span style="color:red;">*</span></label>-->
    <!--        <textarea name="about" id="about" class="form-control" rows="4" required><?php echo $info_page->about; ?></textarea>-->
    <!--    </div>-->
    <!--</div>-->
    
                                                 <div class="col-md-12"> 
                                                    <div class="form-group">
                                                        <label for="url">Url: <span style="color:red;">*</span></label>
                                                        <textarea name="url" id="url" class="form-control" rows="4" required><?php echo $info_page->url; ?></textarea>
                                                    </div>
                                                </div>
                                            
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="image">Image: <span style="color:red;">*</span></label>
                                                        <input type="file" name="image" id="image" class="form-control">
                                                        <?php if (!empty($info_page->image)) { ?>
                                                            <img width="50px" src="../assets/img/team/<?php echo $info_page->image; ?>">
                                                        <?php } ?>
                                                    </div>
                                                </div>
                                            
                                                <div class="box-footer">
                                                    <input type="submit" name="submit" value="<?php if (isset($_GET['id'])) echo 'Update'; else echo 'Save'; ?>" class="btn btn-primary pull-right">
                                                </div>
                                            </form>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
    <?php include('include/footer.php'); ?>
</div>

<script src="bower_components/jquery/dist/jquery.min.js"></script>
<script src="bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
<script src="bower_components/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>
<script src="bower_components/jquery-slimscroll/jquery.slimscroll.min.js"></script>
<script src="bower_components/fastclick/lib/fastclick.js"></script>
<script src="dist/js/adminlte.min.js"></script>
</body>
</html>
