<?php
error_reporting(0); 
session_start();
if(empty($_SESSION['name'])){
	header('Location:index.php');
}
include("include/db_config.php");

/* -----delete  record------*/
if(isset($_GET['del'])){
	$id=$_GET['del'];
	$sqld = "DELETE FROM doctors WHERE id = '$id'";
	$resultd = mysqli_query($conn, $sqld);
	if($resultd){
		$msg = "<p style='color:green;'>Doctor record has been deleted successfully</p>";
	}else {
		$msg = "There is some problem in deleting the record";
	}
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Admin- View Doctors</title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
       <link rel="icon" href="<?=$base_url;?>assets/img/logo-fav.png" type="image/png">
    <link rel="stylesheet" href="bower_components/bootstrap/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="bower_components/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css">
    <link rel="stylesheet" href="dist/css/AdminLTE.min.css">
    <link rel="stylesheet" href="dist/css/skins/_all-skins.min.css">
</head>

<body class="hold-transition skin-blue sidebar-mini">
    <div class="wrapper">
        <?php include('include/header.php');?>
        <?php include('include/side-bar.php');?>

        <div class="content-wrapper">
            <section class="content-header">
                <P>
                    <?PHP 
                        if(!empty($msg)) {
                            echo $msg;
                        } 
                        if(isset($_GET['id']) && $_GET['id']=='Added') {
                            echo '<p style="color:green;">New doctor has been added successfully</p>';
                        }
                        if(isset($_GET['id']) AND $_GET['id']=='Update') {
                            echo '<p style="color:green;">Record has been updated successfully</p>';
                        }
                    ?>
                </P>
            </section>

            <section class="content">
                <div class="row">
                    <div class="col-xs-12">
                        <div class="box">
                            <div class="box-header with-border">
                                <h3 class="box-title">Doctors List</h3>
                                <div class="box-tools pull-right">
                                    <a href="add-credentials.php" class="btn btn-primary">Add Doctor</a>
                                </div>
                            </div>

                            <div class="box-body">
                                 <div class="table-responsive">
                                <table id="example1" class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>SR.NO</th>
                                            <th>USERNAME</th>
                                            <!--<th>PASSWORD</th>-->
                                            <th>Calendly Link</th>
                                            <!--<th>CREATED DATE</th>-->
                                            <th width="10%">ACTION</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                            $sql = "SELECT * FROM doctors ORDER BY id DESC";
                                            $result = mysqli_query($conn, $sql);
                                            $count = 1;
                                            while($info_item = mysqli_fetch_object($result)) {
                                        ?>
                                        <tr>
                                            <td><?php echo $count++ ?></td>
                                            <td><?php echo $info_item->username;?></td>
                                            <!--<td><?php echo $info_item->password;?></td>-->
                                            <td><?php echo $info_item->calendly_link;?></td>
                                            <!--<td><?php echo $info_item->created_date;?></td>-->
                                            <td width="10%">
                                                <a href="add-credentials.php?id=<?php echo $info_item->id ?>"><i
                                                        class="fa fa-edit" style="font-size:24px;"
                                                        title="Edit"></i></a> |
                                                <a href="view-credentials.php?del=<?php echo $info_item->id ?>"
                                                    onclick="return confirm('Are you sure you want to delete this record?');"><i
                                                        class="fa fa-trash" style="font-size:24px;" title="Delete"></i></a>
                                            </td>
                                        </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
        <?php include('include/footer.php');?>
    </div>

    <script src="bower_components/jquery/dist/jquery.min.js"></script>
    <script src="bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
    <script src="bower_components/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>
    <script>
    $(function() {
        $('#example1').DataTable()
    });
    </script>
</body>

</html>


<style>

/* Media query for mobile screens */
@media only screen and (max-width: 768px) {
    h3.box-title {
        font-size: 18px;
    }

    table th, table td {
        font-size: 12px;
        padding: 8px;
    }

    .btn-primary {
        padding: 6px 12px;
        font-size: 14px;
    }

    /* Reduce padding/margins for a cleaner look */
    .content-wrapper {
        padding: 10px;
    }
}

/* Additional media query for very small screens */
@media only screen and (max-width: 480px) {
    table th, table td {
        font-size: 10px;
        padding: 6px;
    }

    .btn-primary {
        padding: 4px 10px;
        font-size: 12px;
    }
}


</style>
