<?php
error_reporting(0);
session_start();
if (empty($_SESSION['name'])) {
  header('Location:index.php');
}
include("include/db_config.php");

/* -----delete  record------*/
if (isset($_GET['del'])) {
  $id = $_GET['del'];
  $query  = "DELETE FROM image WHERE id ='$id' ";
  $result = mysqli_query($conn, $query);
  if ($result) {
    $msg = "<p style='color:green';>Record has been deleted successfully</p>";
  } else {
    $msg = "There is some problem in delete record";
  }
}

?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Admin- View Gallery</title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
       <link rel="icon" href="<?=$base_url;?>assets/img/logo-fav.png" type="image/png">
    <link rel="stylesheet" href="bower_components/bootstrap/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="bower_components/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="bower_components/Ionicons/css/ionicons.min.css">
    <link rel="stylesheet" href="bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css">
    <link rel="stylesheet" href="dist/css/AdminLTE.min.css">
    <link rel="stylesheet" href="dist/css/skins/_all-skins.min.css">
</head>

<body class="hold-transition skin-blue sidebar-mini">
    <div class="wrapper">

        <?php include('include/header.php'); ?>
        <?php include('include/side-bar.php'); ?>

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <section class="content-header" style="margin-top:35px;padding-left:30px;">
                <P>
                    <?PHP if (!empty($msg)) {
            echo $msg;
          }
          if (isset($_GET['id']) && $_GET['id'] == 'Added') {
            echo '<p style="color:green; margin-left: 24px;">New Blog has been added successfully</p>';
          }
          if (isset($_GET['id']) and $_GET['id'] == 'Update') {
            echo '<p style="color:green;">Record has been updated successfully</p>';
          }
          ?>
                </P>
            </section>

            <!-- Main content -->
            <section class="content">
                <div class="row">
                    <div class="col-xs-12">
                        <div class="box">
                            <div class="box-header with-border">
                                <h3 class="box-title">Image List</h3>
                                <div class="box-tools pull-right">
                                    <a href="add-image.php" class="btn btn-primary">Add Image</a>
                                </div>
                            </div>
                            <div class="box-body">
                                 <div class="table-responsive">
                                <table id="example1" class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>SR.NO</th>
                                            <th>Small Image</th>
                                            <!--<th>Large Image</th>-->
                                            <th>Image Alt</th>
                                            <th>CREATED DATE</th>
                                            <th width="10%">ACTION</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                    $query = "select * from image ORDER BY id desc";
                    $result_item = mysqli_query($conn, $query);
                    $count = 1;
                    while ($info_item = mysqli_fetch_object($result_item)) {  ?>
                                        <tr style="color:<?php if (!empty($color)) {
                                          echo $color;
                                        } ?>">
                                            <td><?php echo $count++ ?></td>
                                            <td><img src="../assets/img/gallery/<?php echo $info_item->small_image; ?>"
                                                    width="100px"></td>
                                            <!--<td><img src="../assets/img/gallery/<?php echo $info_item->large_image; ?>"-->
                                            <!--        width="100px"></td>-->
                                            <td><?php echo $info_item->image_alt; ?></td>
                                            <td><?php echo $info_item->created_date; ?></td>
                                            <td width="10%">
                                                <!--<a href="add-image.php?id=<?php echo $info_item->id ?>"><i-->
                                                <!--        class="fa fa-edit" style="font-size:16px;"-->
                                                <!--        title="Edit"></i></a> |-->
                                                <a href="view-image.php?del=<?php echo $info_item->id ?>"
                                                    onclick="return confirm('Are you sure want to delete record ?');"><i
                                                        class="fa fa-trash" style="font-size:16px;color:#e40728;"
                                                        title="Delete"></i></a>
                                            </td>
                                        </tr>
                                        <?php
                    } ?>
                                    </tbody>
                                </table>
                            </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

        </div>

        <?php include('include/footer.php'); ?>

    </div>
    <!-- ./wrapper -->

    <!-- Model of View Package Details Start -->
    <div class="modal fade" id="myModal" role="dialog">
        <div class="modal-dialog">
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