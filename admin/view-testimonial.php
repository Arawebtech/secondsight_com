<?php
error_reporting(0);
session_start();

// Check if user is logged in
if (empty($_SESSION['name'])) {
  header('Location: index.php');
  exit();
}

include("include/db_config.php");

/* ----- Delete record ----- */
if (isset($_GET['del'])) {
  $id = intval($_GET['del']); // Sanitize input
  $stmt = $conn->prepare("DELETE FROM testimonials WHERE id = ?");
  $stmt->bind_param("i", $id);
  if ($stmt->execute()) {
    $msg = "<p style='color:green;'>Record has been deleted successfully</p>";
  } else {
    $msg = "<p style='color:red;'>There is some problem in deleting the record</p>";
  }
  $stmt->close();
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Admin - View Testimonial</title>
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
                <p>
                    <?php
                    if (!empty($msg)) {
                        echo $msg;
                    }
                    if (isset($_GET['id']) && $_GET['id'] == 'Added') {
                        echo '<p style="color:green;">New testimonials has been added successfully</p>';
                    }
                    if (isset($_GET['id']) && $_GET['id'] == 'Update') {
                        echo '<p style="color:green;">Record has been updated successfully</p>';
                    }
                    ?>
                </p>
            </section>

            <!-- Main content -->
            <section class="content">
                <div class="row">
                    <div class="col-xs-12">
                        <div class="box">
                            <div class="box-header with-border">
                                <h3 class="box-title">Testimonials List</h3>
                                <div class="box-tools pull-right">
                                    <a href="add-testimonial.php" class="btn btn-primary">Add Testimonials</a>
                                </div>
                            </div>
                            <div class="box-body">
                                <div class="table-responsive">
                                    <table id="example1" class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>SR.NO</th>
                                                <th>TITLE</th>
                                                <th>Short Description</th>
                                                <th>YouTube URL</th>
                                                <th>CREATED DATE</th>
                                                <th width="10%">ACTION</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $query = "SELECT * FROM testimonials ORDER BY id DESC";
                                            $result_item = mysqli_query($conn, $query);
                                            $count = 1;
                                            while ($info_item = mysqli_fetch_object($result_item)) {
                                                ?>
                                                <tr>
                                                    <td><?php echo $count++; ?></td>
                                                    <td><?php echo htmlspecialchars($info_item->t_name); ?></td>
                                                    <td><?php echo htmlspecialchars($info_item->t_title); ?></td>
                                                    
                                                    <td>
                                                      <?php if (!empty($info_item->youtube_video_url)) { ?>
        <a href="<?php echo htmlspecialchars($info_item->youtube_video_url); ?>" 
           target="_blank" 
           style="font-size:12px; color:#d00000;">
            ▶ View YouTube Video
        </a>
    <?php } ?>
                                                    </td>
                                                    <!--<td>-->
                                                    <!--    <img src="../assets/img/single-blog/<?php echo htmlspecialchars($info_item->banner_image); ?>"-->
                                                    <!--        width="100px" alt="Image">-->
                                                    <!--</td>-->
                                                    <td><?php echo htmlspecialchars($info_item->created_date); ?></td>
                                                    <td>
                                                        <a href="add-testimonial.php?id=<?php echo $info_item->id; ?>">
                                                            <i class="fa fa-edit" style="font-size:16px;" title="Edit"></i>
                                                        </a> |
                                                        <a href="view-testimonial.php?del=<?php echo $info_item->id; ?>"
                                                            onclick="return confirm('Are you sure you want to delete this record?');">
                                                            <i class="fa fa-trash" style="font-size:16px;color:#e40728;" title="Delete"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                                <?php
                                            }
                                            ?>
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

    <!-- Modal for View Details -->
    <div class="modal fade" id="myModal" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Details</h4>
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
        $(function () {
            $('#example1').DataTable();
        });
    </script>
</body>

</html>

<!-- Additional CSS for Mobile Responsiveness -->
<style>
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
