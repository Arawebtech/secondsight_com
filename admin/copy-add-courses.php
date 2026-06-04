<?php
error_reporting(0);
session_start();
if (empty($_SESSION['name'])) {
	header('Location:index.php');
}
include('include/db_config.php');

global $conn;
if (isset($_POST['submit']) and $_POST['submit'] == 'Save') {
	$s_name = addslashes($_POST['s_name']);
	
	$s_title = addslashes($_POST['s_title']);
	$url = preg_replace('/[^A-Za-z0-9-]+/', '-', strtolower(trim($_POST['url'])));
	$short_name = addslashes($_POST['short_name']); 
	$rating = addslashes($_POST['rating']);
	$price = addslashes($_POST['price']);
	$mrp = addslashes($_POST['mrp']); // Changed from 'duration' to 'mrp'
    $discount_percentage = 0;
	$duration_time = addslashes($_POST['duration_time']); // This is the actual time duration
	$short_description = addslashes($_POST['short_description']);
				
	$description = addslashes($_POST['description']);
	$validity = addslashes($_POST['validity']);
	// $duration_time was already correctly handled above
	$meta_keyword = addslashes($_POST['meta_keyword']);
	$meta_description = addslashes($_POST['meta_description']);
	$appl_univrep1 = $_FILES["banner_image"]["name"];
	
	
	if (!empty($appl_univrep1)) {
		$appl_univrep1s = trim($appl_univrep1);
		$file_type = $_FILES["banner_image"]["type"];
		$file_size = ($_FILES["banner_image"]["size"] / 1024);
		$file_locu1 = $_FILES["banner_image"]["tmp_name"];
		$foldervc1 = "../assets/img/course-img/";
		move_uploaded_file($file_locu1, $foldervc1 . $appl_univrep1s);
		$banner_image = $appl_univrep1s;
	}
	$appl_univrep1 = $_FILES["thumbnail_image"]["name"];
	if (!empty($appl_univrep1)) {
		$appl_univrep1s = trim($appl_univrep1);
		$file_type = $_FILES["thumbnail_image"]["type"];
		$file_size = ($_FILES["thumbnail_image"]["size"] / 1024);
		$file_locu1 = $_FILES["thumbnail_image"]["tmp_name"];
		$foldervc1 = "../assets/videothumbnail/";
		move_uploaded_file($file_locu1, $foldervc1 . $appl_univrep1s);
		$thumbnail_image = $appl_univrep1s;
	}

	
	// New fields
	$instructor_name = addslashes($_POST['instructor_name']);
	$inst_about = addslashes($_POST['inst_about']);
	$inst_img = $_FILES["inst_img"]["name"];
	if (!empty($inst_img)) {
		$inst_img_s = trim($inst_img);
		$inst_img_loc = $_FILES["inst_img"]["tmp_name"];
		$inst_img_folder = "../assets/img/instructors/";
		move_uploaded_file($inst_img_loc, $inst_img_folder . $inst_img_s);
		$inst_img = $inst_img_s;
	}

    $community_links = '';
    if (!empty($_POST['community_links'])) {
        $community_data = array();
        for ($i = 0; $i < count($_POST['community_links']['name']); $i++) {
            if (!empty($_POST['community_links']['name'][$i]) && !empty($_POST['community_links']['url'][$i])) {
                $community_data[] = array(
                    'name' => addslashes($_POST['community_links']['name'][$i]),
                    'url' => addslashes($_POST['community_links']['url'][$i])
                );
            }
        }
        $community_links = json_encode($community_data);
    }

	$created_date = date("Y-m-d H:i:s");

	// Initialize missing variables with defaults
	$alt = '';
	$s_schema = '';
	$status = 'Active';

    // SQL query updated to use $mrp for the 'duration' column (which stores MRP)
	$sql_query = "INSERT INTO courses (s_name, s_title, url, short_name, rating, price, discount_percentage, duration, short_description, description, validity, duration_time, banner_image, thumbnail_image, alt, meta_keyword, meta_description, s_schema, status, instructor_name, inst_about, inst_img, community_links, created_date, created_by, updated_date) 
    VALUES ('$s_name', '$s_title', '$url', '$short_name', '$rating', '$price', '$discount_percentage', '$mrp', '$short_description', '$description', '$validity', '$duration_time', '$banner_image', '$thumbnail_image', '$alt', '$meta_keyword', '$meta_description', '$s_schema', '$status', '$instructor_name', '$inst_about', '$inst_img', '$community_links', '$created_date', '', '')";

	$res_query = mysqli_query($conn, $sql_query);

	if ($res_query > 0) {
		// Step 1: Fetch all users
		$user_query = "SELECT id FROM users";
		$user_result = mysqli_query($conn, $user_query);

		if ($user_result && mysqli_num_rows($user_result) > 0) {
			// Step 2: Insert notification for each user
			while ($user_row = mysqli_fetch_assoc($user_result)) {
				$user_id = $user_row['id']; // Get user ID
				
				$notification_title = "New Course Added";
				$notification_message = "A new course titled '$s_name' has been added please visit.";
				$notification_status = 'unread';
				
				$notification_query = "INSERT INTO notifications (user_id, title, message, status, created_at) VALUES (?, ?, ?, ?, NOW())";
				$stmt_notification = $conn->prepare($notification_query);
				$stmt_notification->bind_param("isss", $user_id, $notification_title, $notification_message, $notification_status);
				$stmt_notification->execute();
			}
		}
		
		// Redirect to the view courses page
		exit("<script>window.location.href='view-courses.php?id=Added';</script>");
	} else {
		$error = "There is some problem in inserting record";
	}
}

if (isset($_POST['submit']) and $_POST['submit'] == 'Update') {
	$id = $_GET['id'];
	$s_name = addslashes($_POST['s_name']);
	$s_title = addslashes($_POST['s_title']);
	$url = preg_replace('/[^A-Za-z0-9-]+/', '-', strtolower(trim($_POST['url'])));
	$short_name = addslashes($_POST['short_name']);
	$rating = addslashes($_POST['rating']);
	$price = addslashes($_POST['price']);
	$mrp = addslashes($_POST['mrp']); // Changed from 'duration' to 'mrp'
    $discount_percentage = 0;
	$duration_time = addslashes($_POST['duration_time']); // This is the actual time duration
	$short_description = addslashes($_POST['short_description']);
	
	
	$description = addslashes($_POST['description']);
	$validity = addslashes($_POST['validity']);
	// $duration_time was already correctly handled above
	
	$meta_keyword = addslashes($_POST['meta_keyword']);
	$meta_description = addslashes($_POST['meta_description']);
	$appl_univrep1 = $_FILES["banner_image"]["name"];
	if (!empty($appl_univrep1)) {
		$appl_univrep1s = trim($appl_univrep1);
		$file_type = $_FILES["banner_image"]["type"];
		$file_size = ($_FILES["banner_image"]["size"] / 1024);
		$file_locu1 = $_FILES["banner_image"]["tmp_name"];
		$foldervc1 = "../assets/img/course-img/";
		move_uploaded_file($file_locu1, $foldervc1 . $appl_univrep1s);
		$banner_image = $appl_univrep1s;
	} else {
		$banner_image = $_POST['banner_image2'];
	}

	$appl_univrep1 = $_FILES["thumbnail_image"]["name"];
	if (!empty($appl_univrep1)) {
		$appl_univrep1s = trim($appl_univrep1);
		$file_type = $_FILES["thumbnail_image"]["type"];
		$file_size = ($_FILES["thumbnail_image"]["size"] / 1024);
		$file_locu1 = $_FILES["thumbnail_image"]["tmp_name"];
		$foldervc1 = "../assets/videothumbnail/";
		move_uploaded_file($file_locu1, $foldervc1 . $appl_univrep1s);
		$thumbnail_image = $appl_univrep1s;
	} else {
		$thumbnail_image = $_POST['thumbnail_image2'];
	}

	$alt = $_POST['alt'];
	$s_schema = $_POST['s_schema'];
	$status = $_POST['status'];
	
	$instructor_name = addslashes($_POST['instructor_name']);
	$inst_about = addslashes($_POST['inst_about']);
	$inst_img = $_FILES["inst_img"]["name"];
	if (!empty($inst_img)) {
		$inst_img_s = trim($inst_img);
		$inst_img_loc = $_FILES["inst_img"]["tmp_name"];
		$inst_img_folder = "../assets/img/instructors/";
		move_uploaded_file($inst_img_loc, $inst_img_folder . $inst_img_s);
		$inst_img = $inst_img_s;
	}
	else {
		// If no new image is uploaded, keep the old one
		$inst_img = $_POST['inst_img2'];
	}

    $community_links = '';
    if (!empty($_POST['community_links'])) {
        $community_data = array();
        for ($i = 0; $i < count($_POST['community_links']['name']); $i++) {
            if (!empty($_POST['community_links']['name'][$i]) && !empty($_POST['community_links']['url'][$i])) {
                $community_data[] = array(
                    'name' => addslashes($_POST['community_links']['name'][$i]),
                    'url' => addslashes($_POST['community_links']['url'][$i])
                );
            }
        }
        $community_links = json_encode($community_data);
    }

    // SQL query updated to use $mrp for the 'duration' column (which stores MRP)
	$sql = "UPDATE courses SET s_name = '$s_name', s_title = '$s_title', url = '$url', short_name = '$short_name', rating = '$rating', price = '$price', 
            duration = '$mrp', validity = '$validity', duration_time ='$duration_time', short_description = '$short_description', description = '$description', 
            banner_image = '$banner_image', thumbnail_image = '$thumbnail_image', alt = '$alt', meta_keyword = '$meta_keyword', meta_description = '$meta_description',
            s_schema = '$s_schema', status = '$status', instructor_name = '$instructor_name', inst_about = '$inst_about', inst_img = '$inst_img', community_links = '$community_links', discount_percentage = '$discount_percentage' WHERE id = '$id'";

	$resq = mysqli_query($conn, $sql);

	if ($resq) {
		exit("<script>window.location.href='view-courses.php?id=Update';</script>");
	} else {
		$error = "There is some problem in updating record";
	}
}

if (isset($_GET['id'])) {
	$blog_id = isset($_GET['id']) ? $_GET['id'] : '';
	$query = "SELECT * FROM courses WHERE id ='$blog_id'";
	$result_blog = mysqli_query($conn, $query);
	$info_blog = mysqli_fetch_object($result_blog);
}

?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Add courses</title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
       <link rel="icon" href="<?=$base_url;?>assets/img/logo-fav.png" type="image/png">
    <link rel="stylesheet" href="bower_components/bootstrap/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="bower_components/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="bower_components/Ionicons/css/ionicons.min.css">
    <link rel="stylesheet" href="bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css">
    <link rel="stylesheet" href="dist/css/AdminLTE.min.css">
    <link rel="stylesheet" href="dist/css/skins/_all-skins.min.css">
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">
    <style>
    @media only screen and (max-width: 768px) {
        .content-wrapper {
            padding: 10px !important;
        }
        .form-group, .input-group, .box, .btn {
            width: 100% !important;
            max-width: 100% !important;
        }
        .btn {
            margin-bottom: 10px;
        }
        .box {
            padding: 5px;
            overflow-x: auto;
        }
        label, input, select, textarea {
            font-size: 14px !important;
        }
    }
    @media only screen and (max-width: 480px) {
        .content-wrapper {
            padding: 4px !important;
        }
        label, input, select, textarea {
            font-size: 12px !important;
        }
    }
    </style>
</head>

<body class="hold-transition skin-blue sidebar-mini">
    <div class="wrapper">

        <?php include('include/header.php'); ?>
        <?php include('include/side-bar.php'); ?>

        <div class="content-wrapper">

            <section class="content" style="margin-top:35px;padding-left:30px;">
                <div class="row">
                    <div class="col-xs-12">
                        <span style="color:red;">
                            <?php if (!empty($error)) {
								echo $error;
							} ?> </span>
                        <div class="box">
                            <div class="box-body">
                                <div class="box box-primary">
                                    <div class="box-header with-border">
                                        <h3 class="box-title"><?php if (isset($_GET['id'])) echo 'Update Course';
																else echo 'Add courses';  ?></h3>
                                    </div>
                                    <div class="box-body">
                                        <div class="row">
                                            <form name="addusersform" Method="POST" id="addusersform"
                                                enctype="multipart/form-data">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="reporttitle">Courses Name : <span style="color:red;"> *
                                                            </span></label>
                                                        <div class="input-group">
                                                            <div class="input-group-addon">
                                                                <i class="fa fa-tasks"></i>
                                                            </div>
                                                            <input id="s_name" type='text' name='s_name'
                                                                value="<?php echo isset($info_blog->s_name) ? $info_blog->s_name : ''; ?>"
                                                                class='form-control reporttitle' required>
                                                        </div>
                                                        <span class="errorMSG" id="msgNAME"></span>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="reportURL">URL : <span style="color:red;"> *
                                                            </span></label>
                                                        <div class="input-group">
                                                            <div class="input-group-addon">
                                                                <i class="fa fa-tasks"></i>
                                                            </div>
                                                            <input type='text' id="url" name='url'
                                                                value="<?php echo isset($info_blog->url) ? $info_blog->url : ''; ?>"
                                                                class='form-control reporttitle' required>
                                                        </div>
                                                        <span class="errorMSG" id="msgNAME"></span>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="reporttitle">Title : <span style="color:red;"> *
                                                            </span></label>
                                                        <div class="input-group">
                                                            <div class="input-group-addon">
                                                                <i class="fa fa-tasks"></i>
                                                            </div>
                                                            <input type='text' id="s_title" name='s_title'
                                                                value="<?php echo isset($info_blog->s_title) ? $info_blog->s_title : ''; ?>"
                                                                class='form-control reporttitle' required>
                                                        </div>
                                                        <span class="errorMSG" id="msgNAME"></span>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="reporttitle">Status : <span style="color:red;"> *
                                                            </span></label>
                                                        <div class="input-group">
                                                            <div class="input-group-addon">
                                                                <i class="fa fa-tasks"></i>
                                                            </div>
                                                            <select name="status" class="form-control" required>
                                                                <option value="Active" <?php if (isset($info_blog->status) && $info_blog->status == 'Active') {
																							echo 'selected';
																						} ?>>Active</option>
                                                                <option value="De Active" <?php if (isset($info_blog->status) && $info_blog->status == 'De Active') {
																								echo 'selected';
																							} ?>>De Active</option>
                                                            </select>
                                                        </div>
                                                        <span class="errorMSG" id="msgNAME"></span>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="reporttitle">Banner Image: <span style="color:red;">
                                                                * </span></label>
                                                        <div class="input-group">
                                                            <div class="input-group-addon">
                                                                <i class="fa fa-tasks"></i>
                                                            </div>
                                                            <input type="file" name="banner_image" id="profile_image"
                                                                class="form-control" onchange="previewBannerImage(this)">
                                                        </div>
                                                        <div style="margin-top: 10px;">
                                                            <?php if (isset($info_blog->banner_image) && !empty($info_blog->banner_image)) { ?>
                                                            <img id="bannerPreview" width="150px" height="100px" style="border: 2px solid #ddd; border-radius: 5px; object-fit: cover;"
                                                                src="../assets/img/course-img/<?php echo htmlspecialchars($info_blog->banner_image); ?>" alt="Banner Preview">
                                                            <input type="hidden" name="banner_image2"
                                                                value="<?php echo htmlspecialchars($info_blog->banner_image); ?>">
                                                            <?php } else { ?>
                                                            <img id="bannerPreview" width="150px" height="100px" style="border: 2px solid #ddd; border-radius: 5px; display: none;" alt="Banner Preview">
                                                            <?php } ?>
                                                        </div>
                                                        <span class="errorMSG" id="msgEmail"></span>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="reporttitle">Thumbnail Image: <span
                                                                style="color:red;"> * </span></label>
                                                        <div class="input-group">
                                                            <div class="input-group-addon">
                                                                <i class="fa fa-tasks"></i>
                                                            </div>
                                                            <input type="file" name="thumbnail_image" id="thumbnail_image"
                                                                class="form-control" onchange="previewThumbnailImage(this)">
                                                        </div>
                                                        <div style="margin-top: 10px;">
                                                            <?php if (isset($info_blog->thumbnail_image) && !empty($info_blog->thumbnail_image)) { ?>
                                                            <img id="thumbnailPreview" width="150px" height="100px" style="border: 2px solid #ddd; border-radius: 5px; object-fit: cover;"
                                                                src="../assets/videothumbnail/<?php echo htmlspecialchars($info_blog->thumbnail_image); ?>" alt="Thumbnail Preview">
                                                            <input type="hidden" name="thumbnail_image2"
                                                                value="<?php echo htmlspecialchars($info_blog->thumbnail_image); ?>">
                                                            <?php } else { ?>
                                                            <img id="thumbnailPreview" width="150px" height="100px" style="border: 2px solid #ddd; border-radius: 5px; display: none;" alt="Thumbnail Preview">
                                                            <?php } ?>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="reporttitle">Image Alt : <span style="color:red;"> *
                                                            </span></label>
                                                        <div class="input-group">
                                                            <div class="input-group-addon">
                                                                <i class="fa fa-tasks"></i>
                                                            </div>
                                                            <input id="alt" type='text' name='alt'
                                                                value="<?php echo isset($info_blog->alt) ? $info_blog->alt : ''; ?>"
                                                                class='form-control reporttitle' required>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="reporttitle">Short Name : <span style="color:red;">
                                                                * </span></label>
                                                        <div class="input-group">
                                                            <div class="input-group-addon">
                                                                <i class="fa fa-tasks"></i>
                                                            </div>
                                                            <input type='text' id="short_name" name='short_name'
                                                                value="<?php echo isset($info_blog->short_name) ? $info_blog->short_name : ''; ?>"
                                                                class='form-control reporttitle' required>
                                                        </div>
                                                    </div>
                                                </div>
                                                 <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="reporttitle">Rating : <span style="color:red;">
                                                                * </span></label>
                                                        <div class="input-group">
                                                            <div class="input-group-addon">
                                                                <i class="fa fa-tasks"></i>
                                                            </div>
                                                            <input type='text' id="rating" name='rating'
                                                                value="<?php echo isset($info_blog->rating) ? $info_blog->rating : ''; ?>"
                                                                class='form-control reporttitle' required>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                 <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="reporttitle">Price : <span style="color:red;">
                                                                * </span></label>
                                                        <div class="input-group">
                                                            <div class="input-group-addon">
                                                                <i class="fa fa-tasks"></i>
                                                            </div>
                                                            <input type='number' id="price" name='price' step="0.01"
                                                                value="<?php echo isset($info_blog->price) ? $info_blog->price : ''; ?>"
                                                                class='form-control reporttitle' required onchange="calculateDiscount()">
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                 <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="mrp">Original Price (MRP) : <span style="color:red;">* </span></label>
                                                        <div class="input-group">
                                                            <div class="input-group-addon">
                                                                <i class="fa fa-tags"></i>
                                                            </div>
                                                            <input type='number' id="mrp" name='mrp' step="0.01"
                                                                value="<?php echo isset($info_blog->duration) ? $info_blog->duration : ''; ?>"
                                                                class='form-control reporttitle' required onchange="calculateDiscount()">
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>Discount Preview:</label>
                                                        <div style="background-color: #f0f8ff; padding: 12px; border-radius: 5px; border-left: 4px solid #007bff;">
                                                            <div id="discount-display" style="font-weight: bold; color: #007bff; font-size: 1.1em;">
                                                                Enter Price and MRP to see discount
                                                            </div>
                                                            <small style="color: #666; display: block; margin-top: 5px;">
                                                                MRP: ₹<span id="mrp-display">0</span> | Price: ₹<span id="price-display">0</span> | Discount: <span id="discount-percent-display">0%</span>
                                                            </small>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="discount_percentage">Discount Percentage : <span style="color:red;">* </span></label>
                                                        <div class="input-group">
                                                            <div class="input-group-addon">
                                                                <i class="fa fa-tasks"></i>
                                                            </div>
                                                            <input type='number' id="discount_percentage" name='discount_percentage'
                                                                value="<?php echo isset($info_blog->discount_percentage) ? $info_blog->discount_percentage : ''; ?>"
                                                                class='form-control reporttitle' required>
                                                        </div>
                                                    </div>
                                                </div> -->

                                                 <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="reporttitle">Validity(Enter number of months for validity) : <span style="color:red;">
                                                                * </span></label>
                                                        <div class="input-group">
                                                            <div class="input-group-addon">
                                                                <i class="fa fa-tasks"></i>
                                                            </div>
                                                            <input type='number' id="validity" name='validity'
                                                                value="<?php echo isset($info_blog->validity) ? $info_blog->validity : ''; ?>"
                                                                class='form-control reporttitle' required placeholder="eg. 14 ">
                                                        </div>
                                                    </div>
                                                </div>
                                                 <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="reporttitle">Duration(How many hours course) : <span style="color:red;">
                                                                * </span></label>
                                                        <div class="input-group">
                                                            <div class="input-group-addon">
                                                                <i class="fa fa-tasks"></i>
                                                            </div>
                                                            <input type='text' id="duration_time" name='duration_time'
                                                                value="<?php echo isset($info_blog->duration_time) ? $info_blog->duration_time : ''; ?>"
                                                                class='form-control reporttitle' required placeholder="eg. 25 Hours">
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                
                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <label for="reporttitle"> Description : <span style="color:red;">
                                                                * </span></label>
                                                        <div class="">
                                                            <textarea cols="500" rows="20" class="form-control"
                                                                id="description"
                                                                name='short_description'><?php echo isset($info_blog->short_description) ? $info_blog->short_description : ''; ?></textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                 <div class="col-md-12">
                                                    <div class="form-group">
                                                        <label for="reporttitle"> Short Description : <span style="color:red;">
                                                                * </span></label>
                                                        <div class="">
                                                            <textarea cols="200" rows="6" class="form-control"
                                                                id="short_description"
                                                                name='description'><?php echo isset($info_blog->description) ? $info_blog->description : ''; ?></textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                 <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="instructor_name">Instructor Name : <span style="color:red;"> * </span></label>
                                                    <div class="input-group">
                                                        <div class="input-group-addon">
                                                            <i class="fa fa-user"></i>
                                                        </div>
                                                        <input id="instructor_name" type='text' name='instructor_name' value="<?php echo isset($info_blog->instructor_name) ? $info_blog->instructor_name : ''; ?>" class='form-control reporttitle' required>
                                                    </div>
                                                    <span class="errorMSG" id="msgInstructorName"></span>
                                                </div>
                                            </div>
                                        
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="inst_img">Instructor Image: <span style="color:red;"> * </span></label>
                                                    <div class="input-group">
                                                        <div class="input-group-addon">
                                                            <i class="fa fa-image"></i>
                                                        </div>
                                                        <input type="file" name="inst_img" id="inst_img" class="form-control" >
                                                        <?php if (isset($info_blog->inst_img) && !empty($info_blog->inst_img)) { ?>
                                                            <img width="50px" src="../assets/img/instructors/<?php echo $info_blog->inst_img; ?>">
                                                            <input type="hidden" name="inst_img2" value="<?php echo $info_blog->inst_img; ?>">
                                                        <?php } ?>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label for="inst_about">Instructor About : <span style="color:red;"> * </span></label>
                                                    <div class="">
                                                        <textarea cols="200" rows="6" class="form-control" id="inst_about" name='inst_about'><?php echo isset($info_blog->inst_about) ? $info_blog->inst_about : ''; ?></textarea>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-12">
    <div class="form-group">
        <label for="community_links">Community/Group Links:</label>
        <div id="community-links-container">
            <?php 
            $existing_links = array();
            if (isset($info_blog->community_links) && !empty($info_blog->community_links)) {
                $existing_links = json_decode($info_blog->community_links, true);
                if (!is_array($existing_links)) { // Basic check if json_decode failed or returned non-array
                    $existing_links = array();
                }
            }
            
            // Ensure there's at least one row for new entries or if existing_links is empty/invalid
            if (empty($existing_links)) {
                $existing_links = array(array('name' => '', 'url' => '')); 
            }
            
            foreach ($existing_links as $index => $link): ?>
                <div class="community-link-row" style="border: 1px solid #ddd; padding: 15px; margin-bottom: 10px; border-radius: 5px;">
                    <div class="row">
                        <div class="col-md-5">
                            <div class="form-group">
                                <label>Link Name:</label>
                                <input type="text" name="community_links[name][]" class="form-control" 
                                       placeholder="e.g., WhatsApp Group, Telegram Channel" 
                                       value="<?php echo isset($link['name']) ? htmlspecialchars($link['name']) : ''; ?>">
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="form-group">
                                <label>Link URL:</label>
                                <input type="url" name="community_links[url][]" class="form-control" 
                                       placeholder="https://..." 
                                       value="<?php echo isset($link['url']) ? htmlspecialchars($link['url']) : ''; ?>">
                            </div>
                        </div>
                        <div class="col-md-2" style="padding-top: 25px;">
                            <button type="button" class="btn btn-danger btn-sm remove-link" 
                                    <?php echo count($existing_links) <= 1 && empty(array_filter($link)) ? 'style="display:none;"' : ''; ?>>
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <button type="button" class="btn btn-success btn-sm" id="add-community-link">
            <i class="fa fa-plus"></i> Add Another Link
        </button>
    </div>
</div>



                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <label for="reporttitle">Meta Keyword : <span
                                                                style="color:red;"> * </span></label>
                                                        <div class="input-group">
                                                            <div class="input-group-addon">
                                                                <i class="fa fa-tasks"></i>
                                                            </div>
                                                            <input id="meta_keyword" type='text' name='meta_keyword'
                                                                value="<?php echo isset($info_blog->meta_keyword) ? $info_blog->meta_keyword : ''; ?>"
                                                                class='form-control reporttitle' required>
                                                        </div>
                                                        <span class="errorMSG" id="msgNAME"></span>
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <label for="reporttitle">Meta Description : <span
                                                                style="color:red;"> * </span></label>
                                                        <div class="input-group">
                                                            <div class="input-group-addon">
                                                                <i class="fa fa-tasks"></i>
                                                            </div>
                                                            <input id="meta_description" type='text'
                                                                name='meta_description'
                                                                value="<?php echo isset($info_blog->meta_description) ? $info_blog->meta_description : ''; ?>"
                                                                class='form-control reporttitle' required>
                                                        </div>
                                                        <span class="errorMSG" id="msgNAME"></span>
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <label for="reporttitle">Schema : </label>
                                                        <div class="input-group">
                                                            <div class="input-group-addon">
                                                                <i class="fa fa-tasks"></i>
                                                            </div>
                                                            <input type='text' id="s_schema" name='s_schema'
                                                                value='<?php echo isset($info_blog->s_schema) ? $info_blog->s_schema : ''; ?>'
                                                                class='form-control reporttitle'>
                                                        </div>
                                                        <span class="errorMSG" id="msgNAME"></span>
                                                    </div>
                                                </div>
                                                <div class="box-footer">
                                                    <input type="submit" name="submit" value="<?php if (isset($_GET['id'])) echo 'Update';
																								else echo 'Save';  ?>" class="btn btn-primary pull-right">
                                                </div>
                                            </form>
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
    <script src="dist/js/demo.js"></script>

    <script>
$(document).ready(function() {
    // Add new community link row
    $('#add-community-link').click(function() {
        var newRow = `
            <div class="community-link-row" style="border: 1px solid #ddd; padding: 15px; margin-bottom: 10px; border-radius: 5px;">
                <div class="row">
                    <div class="col-md-5">
                        <div class="form-group">
                            <label>Link Name:</label>
                            <input type="text" name="community_links[name][]" class="form-control" 
                                   placeholder="e.g., WhatsApp Group, Telegram Channel" value="">
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="form-group">
                            <label>Link URL:</label>
                            <input type="url" name="community_links[url][]" class="form-control" 
                                   placeholder="https://..." value="">
                        </div>
                    </div>
                    <div class="col-md-2" style="padding-top: 25px;">
                        <button type="button" class="btn btn-danger btn-sm remove-link">
                            <i class="fa fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
        $('#community-links-container').append(newRow);
        updateRemoveButtons();
    });

    // Remove community link row
    $(document).on('click', '.remove-link', function() {
        $(this).closest('.community-link-row').remove();
        updateRemoveButtons();
    });

    // Update visibility of remove buttons
    function updateRemoveButtons() {
        var totalRows = $('.community-link-row').length;
        $('.community-link-row').each(function(index) {
            if (totalRows <= 1 && 
                $(this).find('input[name="community_links[name][]"]').val() === '' &&
                $(this).find('input[name="community_links[url][]"]').val() === '') {
                $(this).find('.remove-link').hide();
            } else {
                 $(this).find('.remove-link').show();
            }
        });
         if (totalRows <= 1 && 
            $('.community-link-row').first().find('input[name="community_links[name][]"]').val() === '' &&
            $('.community-link-row').first().find('input[name="community_links[url][]"]').val() === '') {
            $('.community-link-row').first().find('.remove-link').hide();
        }
    }


    // Initial call to set button visibility
    updateRemoveButtons();
});
</script>

    <script>
    // Function to calculate and display discount
    function calculateDiscount() {
        var price = parseFloat(document.getElementById('price').value) || 0;
        var mrp = parseFloat(document.getElementById('mrp').value) || 0;
        
        document.getElementById('price-display').textContent = price.toFixed(2);
        document.getElementById('mrp-display').textContent = mrp.toFixed(2);
        
        if (mrp > 0 && price <= mrp) {
            var discountAmount = mrp - price;
            var discountPercent = ((discountAmount / mrp) * 100).toFixed(1);
            document.getElementById('discount-percent-display').textContent = discountPercent + '%';
            document.getElementById('discount-display').innerHTML = '<strong style="color: #28a745;">₹' + discountAmount.toFixed(2) + ' OFF</strong>';
        } else if (price > mrp) {
            document.getElementById('discount-display').innerHTML = '<strong style="color: #dc3545;">Price cannot exceed MRP!</strong>';
            document.getElementById('discount-percent-display').textContent = '0%';
        } else {
            document.getElementById('discount-display').innerHTML = 'Enter valid Price and MRP to see discount';
            document.getElementById('discount-percent-display').textContent = '0%';
        }
    }

    // Call calculateDiscount on page load if values exist
    window.addEventListener('load', function() {
        calculateDiscount();
    });
    </script>

    <script>
    $(function() {
        $('#example1').DataTable()
        $('#example2').DataTable({
            'paging': true,
            'lengthChange': false,
            'searching': false,
            'ordering': true,
            'info': true,
            'autoWidth': false
        })
    })
    </script>

    <script>
    // Function to preview banner image
    function previewBannerImage(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                var preview = document.getElementById('bannerPreview');
                preview.src = e.target.result;
                preview.style.display = 'block';
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Function to preview thumbnail image
    function previewThumbnailImage(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                var preview = document.getElementById('thumbnailPreview');
                preview.src = e.target.result;
                preview.style.display = 'block';
            };
            reader.readAsDataURL(input.files[0]);
        }
    }
    </script>

    <script src="https://cdn.ckeditor.com/4.20.1/standard/ckeditor.js"></script>
    <script>
    CKEDITOR.replace('description'); // This targets the textarea with id="description" (labeled as Short Description in form)
    CKEDITOR.replace('short_description'); // This targets the textarea with id="short_description" (labeled as Description in form)
    // If you intended CKEditor for inst_about:
    // CKEDITOR.replace('inst_about'); 
    </script>
</body>

</html>