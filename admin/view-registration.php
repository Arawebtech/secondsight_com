<?php
error_reporting(0); 
session_start();
if(empty($_SESSION['name'])){
	header('Location:index.php');
}
include("include/db_config.php");
/*include("Classes/users.class.php");
include("Classes/event.class.php");
$userdata = new users();
$eventdata = new event();*/

/* -----delete  record------*/
if (isset($_GET['del'])) {
    $id = $_GET['del'];

    // Delete from order_detail first (dependent on orders)
    $sql_order_detail = "DELETE FROM order_details WHERE order_id IN (SELECT id FROM orders WHERE user_id = '$id')";
    mysqli_query($conn, $sql_order_detail);

    // Delete from orders (dependent on users)
    $sql_orders = "DELETE FROM orders WHERE user_id = '$id'";
    mysqli_query($conn, $sql_orders);

//delete notification
 $sql_query= "DELETE FROM notifications WHERE user_id = '$id'";
 mysqli_query($conn, $sql_orders);

    // Delete from users
    $sql_users = "DELETE FROM users WHERE id = '$id'";
    $result_users = mysqli_query($conn, $sql_users);

    if ($result_users) {
        $msg = "<p style='color:green;'>Record has been deleted successfully</p>";
    } else {
        $msg = "<p style='color:red;'>There is some problem in deleting the record</p>";
    }
}

?>
<?php
if (isset($_GET['toggle'])) {
    $id = $_GET['toggle'];
    // Get current status
    $query = "SELECT is_active FROM users WHERE id = '$id'";
    $result = mysqli_query($conn, $query);
    $user = mysqli_fetch_assoc($result);
    $new_status = $user['is_active'] ? 0 : 1;

    // Update the status
    $update_query = "UPDATE users SET is_active = '$new_status' WHERE id = '$id'";
    if (mysqli_query($conn, $update_query)) {
        $msg = "<p style='color:green;'>User status updated successfully</p>";
    } else {
        $msg = "<p style='color:red;'>Failed to update user status</p>";
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Admin- View Users List</title>
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
    <!-- AdminLTE Skins. Choose a skin from the css/skins
       folder instead of downloading all of them to reduce the load. -->
    <link rel="stylesheet" href="dist/css/skins/_all-skins.min.css">
   <link rel="icon" href="<?=$base_url;?>assets/img/logo-fav.png" type="image/png">

</head>

<body class="hold-transition skin-blue sidebar-mini">
    <div class="wrapper">

        <?php include('include/header.php');?>
        <?php include('include/side-bar.php');?>

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <!-- Content Header (Page header) -->
            <section class="content-header" style="margin-top:35px;padding-left:28px" >
                <P>
                    <?PHP 
        if(!empty($msg))
        {
		    echo $msg;
		} 
		if(isset($_GET['id']) && $_GET['id']=='Added')
		{
			 echo '<p style="color:green; margin-left: 20px;">New event has been added successfully</p>';
		}
		if(isset($_GET['id']) AND $_GET['id']=='Update')
		{
			echo '<p style="color:green;">Record has been updated successfully</p>';
		}	
		?>
                </P>
            </section>

            <!-- Main content -->
            <section class="content">
                <div class="row">
                    <div class="col-xs-12">

                        <!-- /.box -->

                        <div class="box">
                            <div class="box-header with-border">
                                <h3 class="box-title">Users List</h3>
                                <div class="box-tools pull-right">
                                <form action="userdata.php" method="post">
                                    <button type="submit" class="btn btn-success">Download Data <i class="fa-solid fa-download fa-sm"></i></button>
                                  </form>
                                </div>
                            </div>
                            <!-- /.box-header -->
                            <div class="box-body">
                                <div class="table-responsive">
                                    <table id="example1" class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>Sr.no</th>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Mobile</th>
                                                <!--<th>Password</th>-->
                                                <th>Created Date</th>
                                                <!-- <th>ADD DETAIL</th> -->
                                                <th width="10%">Action</th>
                                                 <th width="10%">Status</th>

                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
  				      //$result_item = $eventdata->getEvent();
                $sql = "SELECT * FROM users ORDER BY id DESC";
                $result = mysqli_query($conn, $sql);
        				$count=1;
      			   	 while($info_item= mysqli_fetch_object($result))
      			     {
      						
      				    ?>
                                            <tr style="color:<?php  if(!empty($color)){echo $color;} ?>">
                                                <td>
                                                    <?php echo $count++ ?>
                                                </td>
                                                <td>
                                                    <?php echo $info_item->name;?>
                                                </td>
                                                
                                                <td>
                                                    <?php echo $info_item->email;?>
                                                </td>
                                                
                                                <td>
                                                    <?php echo $info_item->mobile;?>
                                                <!--<td>-->
                                                    <?php
                                                    // echo $info_item->password;
                                                    ?>
                                                    <!--<td><img src="../assets/image/therapy/banner/<?php echo $info_item->banner_image;?>"-->
                                                    <!--        width="100px"></td>-->
                                                <td>
                                                    <?php echo $info_item->created_date;?>
                                                </td>
                                                <!-- <td><a href="add-sub-service.php?event_id=<?php echo $info_item->id ?>"><i
                                                        class="fa fa-plus" style="font-size:16px;" title="Edit"></i></a>
                                            </td> -->
                                                <td width="10%">
                                                    <!--<a href="add-users.php?id=<?php echo $info_item->id ?>"><i-->
                                                    <!--        class="fas fa-edit" style="font-size:24px;"-->
                                                    <!--        title="Edit"></i></a> |-->
                                                    <a href="view-registration.php?del=<?php echo $info_item->id ?>"
                                                        onclick="return confirm('Are you sure want to delete record ?');"><i
                                                            class="fa fa-trash" style="font-size:20px;color:#e40728;"
                                                            title="  Delete"></i></a>
                                                </td>
                                                              <td>
<a href="view-registration.php?toggle=<?php echo $info_item->id; ?>">
    <i class="fa <?php echo ($info_item->is_active == 1 ? 'fa-toggle-on' : 'fa-toggle-off'); ?>" 
       style="font-size:24px; color:<?php echo ($info_item->is_active == 1 ? 'green' : 'red'); ?>;">
    </i>
</a>
</td>
                                            </tr>

                                            <?php } ?>
                                        </tbody>
                                        <!--  <tfoot>
                <tr>
                  <th>Rendering engine</th>
                  <th>Browser</th>
                  <th>Platform(s)</th>
                  <th>Engine version</th>
                  <th>CSS grade</th>
                </tr>
                </tfoot>-->
                                    </table>
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
    <!-- Model of View Package Details Start -->
    <div class="modal fade" id="myModal" role="dialog">
        <div class="modal-dialog">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Student Details</h4>
                </div>

                <div class="modal-body">
                    <div id="packageDetails"></div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>

        </div>
    </div>
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
        $(function () {
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

        function ViewHotelDetails(hotel_id) {
            var dataString = "action=ViewHotelDetails&hotel_id=" + hotel_id;
            // alert(dataString);		
            $.ajax({
                type: "POST",
                dataType: "text",
                url: "view-teacher.php",
                data: dataString,
                success: function (result) {
                    // alert('result'+result);				
                    $('#packageDetails').empty().html(result);
                }
            });
        }
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
            width: 100%;
            margin-bottom: 8px;
        }
        .content-wrapper {
            padding: 10px !important;
        }
        .box {
            padding: 5px;
            overflow-x: auto;
        }
        .table-responsive {
            border: 0;
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
        .content-wrapper {
            padding: 4px !important;
        }
    }
</style>