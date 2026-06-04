    <?php
error_reporting(0);
session_start();
if (empty($_SESSION['name'])) {
	header('Location:index.php');
}
include('include/db_config.php');

global $conn;
if (isset($_POST['submit']) and $_POST['submit'] == 'Save') {
	$b_name = addslashes($_POST['b_name']);
	$b_title = addslashes($_POST['b_title']);
	$url = preg_replace('/[^A-Za-z0-9-]+/', '-', strtolower(trim($_POST['url'])));
	$short_name = addslashes($_POST['short_name']);
	$description = addslashes($_POST['description']);
	$meta_keyword = addslashes($_POST['meta_keyword']);
	$meta_description = addslashes($_POST['meta_description']);
	$appl_univrep1 = $_FILES["banner_image"]["name"];
	if (!empty($appl_univrep1)) {
		$appl_univrep1s = trim($appl_univrep1);
		$file_type = $_FILES["banner_image"]["type"];
		$file_size = ($_FILES["banner_image"]["size"] / 1024);
		$file_locu1 = $_FILES["banner_image"]["tmp_name"];
		$foldervc1 = "../assets/img/single-blog/";
		move_uploaded_file($file_locu1, $foldervc1 . $appl_univrep1s);
		$banner_image = $appl_univrep1s;
	}
	$appl_univrep1 = $_FILES["thumbnail_image"]["name"];
	if (!empty($appl_univrep1)) {
		$appl_univrep1s = trim($appl_univrep1);
		$file_type = $_FILES["thumbnail_image"]["type"];
		$file_size = ($_FILES["thumbnail_image"]["size"] / 1024);
		$file_locu1 = $_FILES["thumbnail_image"]["tmp_name"];
		$foldervc1 = "../assets/img/single-blog/";
		move_uploaded_file($file_locu1, $foldervc1 . $appl_univrep1s);
		$thumbnail_image = $appl_univrep1s;
	}
	$alt = $_POST['alt'];
	$b_schema = $_POST['b_schema'];
	$status = $_POST['status'];
	$created_date = date("Y-m-d H:i:s");

	$sql_query = "INSERT INTO blog (b_name, b_title, url, short_name, description, banner_image, thumbnail_image, alt, meta_keyword, meta_description, b_schema, status, created_date, created_by, updated_date) VALUES ('$b_name', '$b_title', '$url', '$short_name', '$description', '$banner_image', '$thumbnail_image', '$alt', '$meta_keyword', '$meta_description', '$b_schema', '$status', '$created_date', '', '')";

	$res_query = mysqli_query($conn, $sql_query);

	if ($res_query > 0) {
		exit("<script>window.location.href='view-blog.php?id=Added';</script>");
	} else {
		$error = "There is some problem in inserting record";
	}
}


if (isset($_POST['submit']) and $_POST['submit'] == 'Update') {
	$id = $_GET['id'];
	$b_name = addslashes($_POST['b_name']);
	$b_title = addslashes($_POST['b_title']);
	$url = preg_replace('/[^A-Za-z0-9-]+/', '-', strtolower(trim($_POST['url'])));
	$short_name = addslashes($_POST['short_name']);
	$description = addslashes($_POST['description']);
	$meta_keyword = addslashes($_POST['meta_keyword']);
	$meta_description = addslashes($_POST['meta_description']);
	$appl_univrep1 = $_FILES["banner_image"]["name"];
	if (!empty($appl_univrep1)) {
		$appl_univrep1s = trim($appl_univrep1);
		$file_type = $_FILES["banner_image"]["type"];
		$file_size = ($_FILES["banner_image"]["size"] / 1024);
		$file_locu1 = $_FILES["banner_image"]["tmp_name"];
		$foldervc1 = "../assets/img/single-blog/";
		move_uploaded_file($file_locu1, $foldervc1 . $appl_univrep1s);
		$banner_image = $appl_univrep1s;
	}
	if ($banner_image == "")
		$banner_image = $_POST['banner_image2'];

	$appl_univrep1 = $_FILES["thumbnail_image"]["name"];
	if (!empty($appl_univrep1)) {
		$appl_univrep1s = trim($appl_univrep1);
		$file_type = $_FILES["thumbnail_image"]["type"];
		$file_size = ($_FILES["thumbnail_image"]["size"] / 1024);
		$file_locu1 = $_FILES["thumbnail_image"]["tmp_name"];
		$foldervc1 = "../assets/img/single-blog/";
		move_uploaded_file($file_locu1, $foldervc1 . $appl_univrep1s);
		$thumbnail_image = $appl_univrep1s;
	}
	if ($banner_image == "")
		$banner_image = $_POST['banner_image2'];

	$alt = $_POST['alt'];
	$b_schema = $_POST['b_schema'];
	$status = $_POST['status'];

	$sql = "UPDATE blog SET b_name = '$b_name', b_title = '$b_title', url = '$url', short_name = '$short_name', description = '$description', banner_image = '$banner_image', thumbnail_image = '$thumbnail_image', alt = '$alt', meta_keyword = '$meta_keyword', meta_description = '$meta_description', b_schema = '$blog_schema', status = '$status' WHERE id = '$id'";

	$resq = mysqli_query($conn, $sql);

	if ($resq) {
		exit("<script>window.location.href='view-blog.php?id=Update';</script>");
	} else {
		$error = "There is some problem in updating record";
	}
}

if (isset($_GET['id'])) {
	$blog_id = isset($_GET['id']) ? $_GET['id'] : '';
	$query = "SELECT * FROM blog WHERE id ='$blog_id'";
	$result_blog = mysqli_query($conn, $query);
	$info_blog = mysqli_fetch_object($result_blog);
}

?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Add Blog</title>
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
</head>

<body class="hold-transition skin-blue sidebar-mini">
    <div class="wrapper">

        <?php include('include/header.php'); ?>
        <?php include('include/side-bar.php'); ?>

        <!-- Content Wrapper. Contains Blog content -->
        <div class="content-wrapper">

            <!-- Main content -->
            <section class="content" style="margin-top:35px;padding-left:28px;">
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
                                        <h3 class="box-title"><?php if (isset($_GET['id'])) echo 'Update Blog';
																else echo 'Add Blog';  ?></h3>
                                    </div>
                                    <div class="box-body">
                                        <div class="row">
                                            <form name="addusersform" Method="POST" id="addusersform"
                                                enctype="multipart/form-data">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="reporttitle">Title : <span style="color:red;"> *
                                                            </span></label>
                                                        <div class="input-group">
                                                            <div class="input-group-addon">
                                                                <i class="fa fa-tasks"></i>
                                                            </div>
                                                            <input id="b_name" type='text' name='b_name'
                                                                value="<?php echo $info_blog->b_name; ?>"
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
                                                                value="<?php echo $info_blog->url; ?>"
                                                                class='form-control reporttitle' required>
                                                        </div>
                                                        <span class="errorMSG" id="msgNAME"></span>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="reporttitle">Short description : <span style="color:red;"> *
                                                            </span></label>
                                                        <div class="input-group">
                                                            <div class="input-group-addon">
                                                                <i class="fa fa-tasks"></i>
                                                            </div>
                                                            <input type='text' id="b_title" name='b_title'
                                                                value="<?php echo $info_blog->b_title; ?>"
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
                                                                <option value="Active" <?php if ($info_blog->status == 'Active') {
																							echo 'selected';
																						} ?>>Active</option>
                                                                <option value="De Active" <?php if ($info_blog->status == 'De Active') {
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
                                                                class="form-control">
                                                            <?php if (!empty($info_blog->banner_image)) { ?>
                                                            <img width="50px"
                                                                src="../assets/img/single-blog/<?php echo $info_blog->banner_image; ?>">
                                                            <input type="hidden" name="banner_image2"
                                                                value="<?php echo $info_blog->banner_image; ?>">
                                                            <?php } ?>
                                                        </div>
                                                        <span class="errorMSG" id="msgEmail"></span>
                                                    </div>
                                                </div>
                                                <!--<div class="col-md-6">-->
                                                <!--    <div class="form-group">-->
                                                <!--        <label for="reporttitle">Thumbnail Image: <span-->
                                                <!--                style="color:red;"> * </span></label>-->
                                                <!--        <div class="input-group">-->
                                                <!--            <div class="input-group-addon">-->
                                                <!--                <i class="fa fa-tasks"></i>-->
                                                <!--            </div>-->
                                                <!--            <input type="file" name="thumbnail_image" id="profile_image"-->
                                                <!--                class="form-control">-->
                                                <!--            <?php if (!empty($info_blog->thumbnail_image)) { ?>-->
                                                <!--            <img width="50px"-->
                                                <!--                src="../assets/img/single-blog/<?php echo $info_blog->thumbnail_image; ?>">-->
                                                <!--            <input type="text" name="thumbnail_image2"-->
                                                <!--                value="<?php echo $info_blog->banner_image; ?>">-->
                                                <!--            <?php } ?>-->
                                                <!--        </div>-->
                                                <!--    </div>-->
                                                <!--</div>-->
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="reporttitle">Image Alt : <span style="color:red;"> *
                                                            </span></label>
                                                        <div class="input-group">
                                                            <div class="input-group-addon">
                                                                <i class="fa fa-tasks"></i>
                                                            </div>
                                                            <input id="alt" type='text' name='alt'
                                                                value="<?php echo $info_blog->alt; ?>"
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
                                                                value="<?php echo $info_blog->short_name; ?>"
                                                                class='form-control reporttitle' required>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <label for="reporttitle">Description : <span style="color:red;">
                                                                * </span></label>
                                                        <div class="">
                                                            <textarea cols="200" rows="6" class="form-control"
                                                                id="description"
                                                                name='description'><?php echo $info_blog->description; ?></textarea>
                                                        </div>
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
                                                                value="<?php echo $info_blog->meta_keyword; ?>"
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
                                                                value="<?php echo $info_blog->meta_description; ?>"
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
                                                            <input type='text' id="b_schema" name='b_schema'
                                                                value='<?php echo $info_blog->b_schema; ?>'
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

    <script src="https://cdn.ckeditor.com/4.20.1/standard/ckeditor.js"></script>
    <script>
    CKEDITOR.replace('description');
    </script>
</body>

</html>

<!-- 
ALTER TABLE blog MODIFY COLUMN description TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE blog MODIFY COLUMN b_title TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE blog MODIFY COLUMN b_name TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-->