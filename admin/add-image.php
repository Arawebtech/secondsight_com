<?php
error_reporting(0);
session_start();
if(empty($_SESSION['name'])){
    header('Location:index.php');
}
include('include/db_config.php');
/*include("Classes/users.class.php");
include("Classes/event.class.php");
$eventdata = new event();*/

global $conn;
if(isset($_POST['submit']) AND $_POST['submit'] == 'Save')
{
   
    $path = $_FILES['small_image']['name'];
    $path_tmp = $_FILES['small_image']['tmp_name'];

    if ($path != '') {
        $ext = pathinfo($path, PATHINFO_EXTENSION);
        $file_name = basename($path, '.' . $ext);
        if (!in_array($ext, ['jpg', 'png', 'jpeg', 'gif', 'webp'])) {
            $valid = 0;
            $error_message .= 'You must upload a jpg, jpeg, gif, or png file.<br>';
        }
    } else {
        $valid = 0;
        $error_message .= 'You must select a featured photo.<br>';
    }
     move_uploaded_file($path_tmp, '../assets/img/gallery' . $path);



    $image_alt=$_POST['image_alt'];
   
    $created_date=date("Y-m-d H:i:s");
  
    $query = "INSERT INTO image (small_image, image_alt, created_date, updated_date) VALUES ('$path', '$image_alt', '$created_date', '$updated_date')";


    $result_product = mysqli_query($conn, $query);

    if($result_product>0){
       exit("<script>window.location.href='view-image.php?id=Added';</script>");
    	
    }else{
    	$error="There is some problem in inserting record";
    }
}

if(isset($_POST['submit']) AND $_POST['submit'] == 'Update')
{
    $id=$_GET['id'];
    $objusers=new stdclass();
    

    $path = $_FILES['small_image']['name'];
    $path_tmp = $_FILES['small_image']['tmp_name'];

    if ($path != '') {
        $ext = pathinfo($path, PATHINFO_EXTENSION);
        $file_name = basename($path, '.' . $ext);
        if (!in_array($ext, ['jpg', 'png', 'jpeg', 'gif', 'webp'])) {
            $valid = 0;
            $error_message .= 'You must upload a jpg, jpeg, gif, or png file.<br>';
        }
    } else {
        $valid = 0;
        $error_message .= 'You must select a featured photo.<br>';
    }
     move_uploaded_file($path_tmp, '../assets/img/gallery/' . $path);

    
      $image_alt=$_POST['image_alt'];
    //$eventdata->editEvent($objusers,$id);

    $upd_query = "UPDATE image SET small_image = '$path', image_alt = '$image_alt'  WHERE id = '$id'";

    $eventdata = mysqli_query($conn, $upd_query);
    
    if($eventdata)
    {
	   exit("<script>window.location.href='view-image.php?id=Update';</script>");
		
	}else{
		$error= "There is some problem in updating record";
	}
} 

if(isset($_GET['id']))
{
	$image_id = isset($_GET['id'])?$_GET['id']:''; 
	//$result_page = $eventdata->getEventinfo($service_id);

	$sql_ser = "SELECT * FROM image WHERE id = '$image_id'";
	$result_page = mysqli_query($conn, $sql_ser);
	$info_page = mysqli_fetch_object($result_page);
}			

?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Add Page</title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <!-- Bootstrap 3.3.7 -->
    <link rel="stylesheet" href="bower_components/bootstrap/dist/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="bower_components/font-awesome/css/font-awesome.min.css">
       <link rel="icon" href="<?=$base_url;?>assets/img/logo-fav.png" type="image/png">
    <!-- Ionicons -->
    <link rel="stylesheet" href="bower_components/Ionicons/css/ionicons.min.css">
    <!-- DataTables -->
    <link rel="stylesheet" href="bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="dist/css/AdminLTE.min.css">

    <link rel="stylesheet" href="dist/css/skins/_all-skins.min.css">

    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">
</head>

<body class="hold-transition skin-blue sidebar-mini">
    <div class="wrapper">

        <?php include('include/header.php');?>
        <?php include('include/side-bar.php');?>

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <!-- Content Header (Page header) -->

            <!-- Main content -->
            <section class="content" style="margin-top:35px;padding-left:30px;">
                <div class="row">
                    <div class="col-xs-12">
                        <span style="color:red;">
                            <!-- /.box -->
                            <?php if(!empty($error)){ echo $error;} ?>
                        </span>
                        <div class="box">

                            <!-- /.box-header -->
                            <div class="box-body">
                                <div class="box box-primary">
                                    <div class="box-header with-border">
                                        <h3 class="box-title">
                                            <?php if(isset($_GET['id'])) echo 'Update Image'; else echo 'Add Image';  ?>
                                        </h3>

                                        <!--<div class="box-tools pull-right">
						<a href="view-service.php" class="btn btn-primary">Events List</a>						
					  </div>-->
                                    </div>
                                    <!-- /.box-header -->
                                    <div class="box-body">
                                        <div class="row">
                                        
                                            <form action="" method="post"
                                                enctype="multipart/form-data">
                                        

                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="reporttitle">Image: <span style="color:red;">
                                                                * </span></label>
                                                        <div class="input-group">
                                                            <div class="input-group-addon">
                                                                <i class="fa fa-tasks"></i>
                                                            </div>

                                                            <input type="file" name="small_image" id="small_image"
                                                                class="form-control">
                                                            <?php if(!empty($info_page->small_image)){?>
                                                            <img width="50px"
                                                                src="../assets/img/gallery/<?php echo $info_page->small_image; ?>">
                                                            <input type="hidden" name="banner_image2"
                                                                value="<?php echo $info_page->small_image; ?>">
                                                            <?php } ?>
                                                        </div>
                                                        <span class="errorMSG" id="msgEmail"></span>
                                                    </div>
                                                </div>


                                                <!-- <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="reporttitle">Large Image: <span style="color:red;">
                                                                * </span></label>
                                                        <div class="input-group">
                                                            <div class="input-group-addon">
                                                                <i class="fa fa-tasks"></i>
                                                            </div>

                                                            <input type="file" name="large_image" id="large_image"
                                                                class="form-control">
                                                            <?php if(!empty($info_page->large_image)){?>
                                                            <img width="50px"
                                                                src="../assets/img/gallery/<?php echo $info_page->large_image; ?>">
                                                            <input type="hidden" name="banner_image2"
                                                                value="<?php echo $info_page->large_image; ?>">
                                                            <?php } ?>
                                                        </div>
                                                        <span class="errorMSG" id="msgEmail"></span>
                                                    </div>
                                                </div> -->

                                                
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="reporttitle">Image Alt : <span style="color:red;"> *
                                                            </span></label>
                                                        <div class="input-group">
                                                            <div class="input-group-addon">
                                                                <i class="fa fa-tasks"></i>
                                                            </div>
                                                            <input type='text' name='image_alt' id="image_alt"
                                                                value="<?php echo $info_page->image_alt;?>"
                                                                class='form-control reporttitle' required>
                                                        </div>
                                                        <span class="errorMSG" id="msgNAME"></span>
                                                    </div>
                                                </div>
                                               
                                               
                                                
                                               
                                                <div class="box-footer">
                                                    <input type="submit" name="submit"
                                                        value="<?php if(isset($_GET['id'])) echo 'Update'; else echo 'Save';  ?>"
                                                        class="btn btn-primary pull-right">
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <!-- /.box-body -->
                            </div>
                            <!-- /.box -->
                        </div>
                        <!-- /.col -->
                    </div>
                    <!-- /.row -->
            </section>
            <!-- /.content -->
        </div>
        <!-- /.content-wrapper -->

        <?php include('include/footer.php');?>

    </div>
    <!-- ./wrapper -->

    <!-- jQuery 3 -->
    <script src="bower_components/jquery/dist/jquery.min.js"></script>
    <!-- Bootstrap 3.3.7 -->
    <script src="bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
    <!-- DataTables -->
    <script src="bower_components/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>
    <!-- SlimScroll -->
    <script src="bower_components/jquery-slimscroll/jquery.slimscroll.min.js"></script>
    <!-- FastClick -->
    <script src="bower_components/fastclick/lib/fastclick.js"></script>
    <!-- AdminLTE App -->
    <script src="dist/js/adminlte.min.js"></script>
    <!-- AdminLTE for demo purposes -->
    <script src="dist/js/demo.js"></script>
    <!-- page script -->
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