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
    //$objusers=new stdclass();
    $e_name=addslashes($_POST['e_name']);
    $description=addslashes($_POST['description']);

    $date=addslashes($_POST['date']);
    $time=addslashes($_POST['time']);
    $address=addslashes($_POST['address']);





    $appl_univrep1= $_FILES["banner_image"]["name"];
    if(!empty($appl_univrep1)){
    	$appl_univrep1s= trim($appl_univrep1);
    	$file_type= $_FILES["banner_image"]["type"];
    	$file_size= ($_FILES["banner_image"]["size"]/1024);
    	$file_locu1= $_FILES["banner_image"]["tmp_name"];
    	$foldervc1="../assets/image/event/";
    	move_uploaded_file($file_locu1,$foldervc1.$appl_univrep1s);
    	$banner_image=$appl_univrep1s;
    }
    $appl_univrep2= $_FILES["thumbnail_image"]["name"];
    if(!empty($appl_univrep2)){
    	$appl_univreps2= trim($appl_univrep2);
    	$file_type= $_FILES["thumbnail_image"]["type"];
    	$file_size= ($_FILES["thumbnail_image"]["size"]/1024);
    	$file_locu2= $_FILES["thumbnail_image"]["tmp_name"];
    	$foldervc2="../assets/image/event/";
    	move_uploaded_file($file_locu2,$foldervc2.$appl_univreps2);
    	$thumbnail_image=$appl_univreps2;
    }
    $alt=$_POST['alt'];
    $url=preg_replace('/[^A-Za-z0-9-]+/', '-', strtolower(trim($_POST['url'])));
    $e_title=addslashes($_POST['e_title']);
    $short_name=addslashes($_POST['short_name']);
    $meta_keywords=addslashes($_POST['meta_keywords']);
    $meta_description=addslashes($_POST['meta_description']);
    $e_schema=addslashes($_POST['e_schema']);
    $status=$_POST['status'];
    $created_date=date("Y-m-d H:i:s");
    //$result_product=$eventdata->addEvent($objusers);

    $query = "INSERT INTO event (e_name, description, date, time, address, banner_image, thumbnail_image, alt, e_title, url, short_name, meta_keyword, meta_description, e_schema, status, created_date, created_by, updated_date) VALUES ('$e_name', '$description', '$date', '$time', '$address', '$banner_image', '$thumbnail_image', '$alt', '$e_title', '$url', '$short_name', '$meta_keyword', '$meta_description', '$e_schema', '$status', '$created_date', '', '')";

    $result_product = mysqli_query($conn, $query);

    if($result_product>0){
       exit("<script>window.location.href='view-event.php?id=Added';</script>");
    	
    }else{
    	$error="There is some problem in inserting record";
    }
}

if(isset($_POST['submit']) AND $_POST['submit'] == 'Update')
{
    $id=$_GET['id'];
    $objusers=new stdclass();
    $e_name=addslashes($_POST['e_name']);
    $description=addslashes($_POST['description']);
    $date=addslashes($_POST['date']);
    $time=addslashes($_POST['time']);
    $address=addslashes($_POST['address']);


    $appl_univrep1= $_FILES["banner_image"]["name"];
    if(!empty($appl_univrep1)){
    	$appl_univrep1s= trim($appl_univrep1);
    	$file_type= $_FILES["banner_image"]["type"];
    	$file_size= ($_FILES["banner_image"]["size"]/1024);
    	$file_locu1= $_FILES["banner_image"]["tmp_name"];
    	$foldervc1="../assets/image/event/";
    	move_uploaded_file($file_locu1,$foldervc1.$appl_univrep1s);
    	$banner_image=$appl_univrep1s;
    }
	if ($banner_image == "")
		$banner_image = $_POST['banner_image2'];
	
    $appl_univrep2= $_FILES["thumbnail_image"]["name"];
    if(!empty($appl_univrep2)){
    	$appl_univreps2= trim($appl_univrep2);
    	$file_type= $_FILES["thumbnail_image"]["type"];
    	$file_size= ($_FILES["thumbnail_image"]["size"]/1024);
    	$file_locu2= $_FILES["thumbnail_image"]["tmp_name"];
    	$foldervc2="../assets/image/event/";
    	move_uploaded_file($file_locu2,$foldervc2.$appl_univreps2);
    	$thumbnail_image=$appl_univreps2;
    }
	if ($thumbnail_image == "")
		$thumbnail_image = $_POST['thumbnail_image2'];
	
    $alt=$_POST['alt'];
    $url=preg_replace('/[^A-Za-z0-9-]+/', '-', strtolower(trim($_POST['url'])));
    $e_title=addslashes($_POST['e_title']);
    $short_name=addslashes($_POST['short_name']);
    $meta_keywords=addslashes($_POST['meta_keywords']);
    $meta_description=addslashes($_POST['meta_description']);
    $e_schema=addslashes($_POST['e_schema']);
    $status=$_POST['status'];
    //$eventdata->editEvent($objusers,$id);

    $upd_query = "UPDATE event SET e_name = '$e_name', description = '$description', date = '$date', time = '$time', address = '$address', banner_image = '$banner_image', thumbnail_image = '$thumbnail_image', alt = '$alt', e_title = '$e_title', url = '$url', short_name = '$short_name', meta_keyword = '$meta_keyword', meta_description = '$meta_description', e_schema = '$e_schema', status = '$status' WHERE id = '$id'";


    $eventdata = mysqli_query($conn, $upd_query);
    
    if($eventdata)
    {
        exit("<script>window.location.href='view-event.php?id=Update';</script>");
		
	}else{
		$error= "There is some problem in updating record";
	}
} 

if(isset($_GET['id']))
{
	$event_id = isset($_GET['id'])?$_GET['id']:''; 
	//$result_page = $eventdata->getEventinfo($service_id);

	$sql_ser = "SELECT * FROM event WHERE id = '$event_id'";
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
    <!-- Ionicons -->
    <link rel="stylesheet" href="bower_components/Ionicons/css/ionicons.min.css">
    <!-- DataTables -->
    <link rel="stylesheet" href="bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="dist/css/AdminLTE.min.css">
   <link rel="icon" href="<?=$base_url;?>assets/img/logo-fav.png" type="image/png">
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
            <section class="content">
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
                                            <?php if(isset($_GET['id'])) echo 'Update Event'; else echo 'Add Event';  ?>
                                        </h3>

                                        <!--<div class="box-tools pull-right">
						<a href="view-service.php" class="btn btn-primary">Events List</a>						
					  </div>-->
                                    </div>
                                    <!-- /.box-header -->
                                    <div class="box-body">
                                        <div class="row">
                                            <form name="addusersform" Method="POST" id="addusersform"
                                                enctype="multipart/form-data">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="reporttitle">Event Name : <span
                                                                style="color:red;"> * </span></label>
                                                        <div class="input-group">
                                                            <div class="input-group-addon">
                                                                <i class="fa fa-tasks"></i>
                                                            </div>
                                                            <input id="e_name" type='text' name='e_name'
                                                                value="<?php echo $info_page->e_name;?>"
                                                                class='form-control reporttitle' required>
                                                        </div>
                                                        <span class="errorMSG" id="msgNAME"></span>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="reporttitle">URL : <span style="color:red;"> *
                                                            </span></label>
                                                        <div class="input-group">
                                                            <div class="input-group-addon">
                                                                   <i class="fa fa-tasks"></i>
                                                            </div>
                                                            <input type='text' name='url' id="url"
                                                                value="<?php echo $info_page->url;?>"
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
                                                            <input type='text' name='e_title' id="e_title"
                                                                value="<?php echo $info_page->e_title;?>"
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
                                                                <option value="Active"
                                                                    <?php if($info_page->status=='Active'){ echo 'selected';}?>>
                                                                    Active</option>
                                                                <option value="De Active"
                                                                    <?php if($info_page->status=='De Active'){ echo 'selected';}?>>
                                                                    De Active</option>
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

                                                            <input type="file" name="banner_image" id="banner_image"
                                                                class="form-control">
                                                            <?php if(!empty($info_page->banner_image)){?>
                                                            <img width="50px"
                                                                src="../assets/image/event/<?php echo $info_page->banner_image; ?>">
                                                            <input type="hidden" name="banner_image2"
                                                                value="<?php echo $info_page->banner_image; ?>">
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
                                                            <input type="file" name="thumbnail_image"
                                                                id="thumbnail_image" class="form-control">
                                                            <?php if(!empty($info_page->thumbnail_image)){?>
                                                            <img width="50px"
                                                                src="../assets/image/event/<?php echo $info_page->thumbnail_image; ?>">
                                                            <input type="text" name="thumbnail_image2"
                                                                value="<?php echo $info_page->thumbnail_image; ?>">
                                                            <?php } ?>
                                                        </div>
                                                        <span class="errorMSG" id="msgEmail"></span>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="reporttitle">Alt: <span style="color:red;"> *
                                                            </span></label>
                                                        <div class="input-group">
                                                            <div class="input-group-addon">
                                                                <i class="fa fa-tasks"></i>
                                                            </div>
                                                            <input type='text' name='alt' id="alt"
                                                                value="<?php echo $info_page->alt;?>"
                                                                class='form-control reporttitle' required>
                                                        </div>
                                                        <span class="errorMSG" id="msgEmail"></span>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="reporttitle">Short Name: <span style="color:red;"> *
                                                            </span></label>
                                                        <div class="input-group">
                                                            <div class="input-group-addon">
                                                                <i class="fa fa-tasks"></i>
                                                            </div>
                                                            <input type='text' name='short_name' id="short_name"
                                                                value="<?php echo $info_page->short_name;?>"
                                                                class='form-control reporttitle' required>
                                                        </div>
                                                        <span class="errorMSG" id="msgEmail"></span>
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <label for="reporttitle">Description : <span style="color:red;">
                                                                * </span></label>
                                                        <div class="">
                                                            <textarea cols="200" rows="6" class="form-control"
                                                                id="description"
                                                                name='description'><?php echo $info_page->description;?></textarea>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <label for="reporttitle">Date : <span
                                                                style="color:red;"> * </span></label>
                                                        <div class="input-group">
                                                            <div class="input-group-addon">
                                                                <i class="fa fa-tasks"></i>
                                                            </div>
                                                            <input type='date' name='date' id="date"
                                                                value="<?php echo $info_page->date;?>"
                                                                class='form-control reporttitle' required>
                                                        </div>
                                                        <span class="errorMSG" id="msgEmail"></span>
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <label for="reporttitle">Time : <span
                                                                style="color:red;"> * </span></label>
                                                        <div class="input-group">
                                                            <div class="input-group-addon">
                                                                <i class="fa fa-tasks"></i>
                                                            </div>
                                                            <input type='text' name='time' id="time"
                                                                value="<?php echo $info_page->time;?>"
                                                                class='form-control reporttitle' required>
                                                        </div>
                                                        <span class="errorMSG" id="msgEmail"></span>
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <label for="reporttitle">Address : <span
                                                                style="color:red;"> * </span></label>
                                                        <div class="input-group">
                                                            <div class="input-group-addon">
                                                                <i class="fa fa-tasks"></i>
                                                            </div>
                                                            <input type='text' name='address' id="address"
                                                                value="<?php echo $info_page->address;?>"
                                                                class='form-control reporttitle' required>
                                                        </div>
                                                        <span class="errorMSG" id="msgEmail"></span>
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
                                                            <input type='text' name='meta_keyword' id="meta_keyword"
                                                                value="<?php echo $info_page->meta_keyword;?>"
                                                                class='form-control reporttitle' required>
                                                        </div>
                                                        <span class="errorMSG" id="msgEmail"></span>
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
                                                            <input type='text' name='meta_description'
                                                                id="meta_description"
                                                                value="<?php echo $info_page->meta_description;?>"
                                                                class='form-control reporttitle' required>
                                                        </div>
                                                        <span class="errorMSG" id="msgEmail"></span>
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <label for="reporttitle">Schema : </label>
                                                        <div class="input-group">
                                                            <div class="input-group-addon">
                                                                <i class="fa fa-tasks"></i>
                                                            </div>
                                                            <input type='text' name='e_schema' id="e_schema"
                                                                value='<?php echo $info_page->e_schema;?>'
                                                                class='form-control reporttitle'>
                                                        </div>
                                                        <span class="errorMSG" id="msgEmail"></span>
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