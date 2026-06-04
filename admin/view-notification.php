<?php
error_reporting(0);
session_start();
if (empty($_SESSION['name'])) {
    header('Location:index.php');
}
include("include/db_config.php");

/* -----delete record------ */
if (isset($_GET['del'])) {
    $id = $_GET['del'];
    $query  = "DELETE FROM notifications WHERE id ='$id'";
    $result = mysqli_query($conn, $query);
    if ($result) {
        $msg = "<p style='color:green;'>Record has been deleted successfully</p>";
    } else {
        $msg = "<p style='color:red;'>There is some problem in deleting the record</p>";
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Admin - View Notifications</title>
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
        <?php include('include/header.php'); ?>
        <?php include('include/side-bar.php'); ?>

        <div class="content-wrapper">
            <section class="content-header">
                <h1>View Notifications</h1>
                <?php if (!empty($msg)) echo $msg; ?>
            </section>

            <section class="content">
                <div class="row">
                    <div class="col-xs-12">
                        <div class="box">
                            <div class="box-header with-border">
                                <h3 class="box-title">Notification List</h3>
                                <div class="box-tools pull-right">
                                    <a href="add-notification.php" class="btn btn-primary">Add Notification</a>
                                </div>
                            </div>
                            <div class="box-body">
                                <div class="table-responsive">
                                    <table id="example1" class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>SR.NO</th>
                                                <th>User Name</th>
                                                <th>Title</th>
                                                <th>Message</th>
                                                <th>Created Date</th>
                                                <th>Status</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $query = "
                                                SELECT 
                                                    notifications.id AS notification_id, 
                                                    notifications.title, 
                                                    notifications.message, 
                                                    notifications.created_at, 
                                                    notifications.status, 
                                                    users.name AS user_name 
                                                FROM notifications 
                                                LEFT JOIN users 
                                                ON notifications.user_id = users.id 
                                                ORDER BY notifications.id DESC";
                                            $result = mysqli_query($conn, $query);
                                            $count = 1;

                                            while ($row = mysqli_fetch_object($result)) { ?>
                                                <tr>
                                                    <td><?php echo $count++; ?></td>
                                                    <td><?php echo $row->user_name ? $row->user_name : 'N/A'; ?></td>
                                                    <td><?php echo $row->title; ?></td>
                                                    <td>
                                                         <?php
                                    // Trim the short description to remove any leading or trailing whitespace
                                    $shortDesc = trim( $row->message);
                                    if (strlen($shortDesc) > 30) {
                                       $shortDesc=  substr($shortDesc, 0, 30) . '...';
                                    } 
                                    ?>
                                    <span class="truncate" title="<?php echo $row->message; ?>"><?php echo $shortDesc; ?></span>
                                    <a href="#" class="read-more" data-message="<?php echo $row->message; ?>">Read More</a>
                                </td>

                                                    <td><?php echo $row->created_at; ?></td>
                                                    <td><?php echo $row->status == 'read' ? 'Read' : 'Unread'; ?></td>
                                                    <td>
                                                        <!--<a href="edit-notification.php?id=<?php echo $row->notification_id; ?>"><i class="fa fa-edit" style="font-size:20px;" title="Edit"></i></a> |-->
                                                        
                                                        <a href="view-notification.php?del=<?php echo $row->notification_id; ?>" onclick="return confirm('Are you sure you want to delete this notification?');"><i class="fa fa-trash" style="font-size:16px;color:#e40728;" title="Delete"></i></a>
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

        <?php include('include/footer.php'); ?>
    </div>

    <script src="bower_components/jquery/dist/jquery.min.js"></script>
    <script src="bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
    <script src="bower_components/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>
    <script src="dist/js/adminlte.min.js"></script>
    <script>
        $(function() {
            $('#example1').DataTable();
        });
    </script>
  
   <script>
    $(document).on('click', '.read-more', function(e) {
        e.preventDefault();
        var fullMessage = $(this).data('message');
        $('#modalMessageContent').text(fullMessage);
        $('#messageModal').modal('show');
    });


</script>

<div id="messageModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Full Message</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p id="modalMessageContent"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>


</body>

</html>
