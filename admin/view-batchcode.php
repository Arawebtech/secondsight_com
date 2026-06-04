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

    // Check if batchcode has been used
    $check_query = "SELECT current_usage FROM batchcode WHERE id = ?";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bind_param("i", $id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    $check_data = $check_result->fetch_assoc();
    $check_stmt->close();

    if ($check_data['current_usage'] > 0) {
        $msg = "<p style='color:red;padding-left:20px;'>Cannot delete batchcode. It has been used by students.</p>";
    } else {
        $query = "DELETE FROM batchcode WHERE id = ?";
        $delete_stmt = $conn->prepare($query);
        $delete_stmt->bind_param("i", $id);
        $result = $delete_stmt->execute();

        if ($result) {
            $msg = "<p style='color:green;padding-left:20px;'>Batchcode has been deleted successfully</p>";
        } else {
            $msg = "<p style='color:red;padding-left:20px;'>There is some problem in deleting the record</p>";
        }
        $delete_stmt->close();
    }
}

?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Admin - BatchCode</title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <link rel="stylesheet" href="bower_components/bootstrap/dist/css/bootstrap.min.css">
    <link rel="icon" href="<?=$base_url;?>assets/img/logo-fav.png" type="image/png">
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

        <div class="content-wrapper" style="margin-top:35px; padding-left:30px;">
            <!-- Main content -->
            <section class="content">
                <div class="row">
                    <div class="col-xs-12">
                        <div class="box">
                            <div class="box-header with-border">
                                <h3 class="box-title">BatchCode List</h3>
                                <div class="box-tools pull-right">
                                    <a href="add-batchcode.php" class="btn btn-primary">Add Batchcode</a>
                                </div>
                            </div>
                            <div class="box-body">
                                <?php if (isset($_GET['id']) && $_GET['id'] == 'Added'): ?>
                                    <p style="color:green;padding-left:20px;">New Batchcode has been generated successfully</p>
                                <?php elseif (isset($_GET['id']) && $_GET['id'] == 'Updated'): ?>
                                    <p style="color:green;padding-left:20px;">Batchcode has been updated successfully</p>
                                <?php endif; ?>
                                
                                <?php if (isset($msg)) echo $msg; ?>

                                <div class="table-responsive">
                                    <table id="example1" class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>SR.NO</th>
                                                <th>BatchCode</th>
                                                <th>Batch Title</th>
                                                <th>Month-Year</th>
                                                <th>Lessons</th>
                                                <th>Usage</th>
                                                <th>Max Usage</th>
                                                <th>Expiry Date</th>
                                                <th>Status</th>
                                                <th>Created Date</th>
                                                <th width="15%">ACTION</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            // Query to get batchcode details with batch and lesson information
                                          $query = "SELECT bc.*, 
                b.batch_title,
                b.month_year,
                COUNT(DISTINCT lv.id) as lesson_count
         FROM batchcode bc
         JOIN batch b ON bc.batch_id = b.id
         LEFT JOIN lesson_video lv ON b.id = lv.batch_id
         GROUP BY bc.id
         ORDER BY bc.id DESC";
                                            $result_item = mysqli_query($conn, $query);
                                            $count = 1;
                                            while ($row = mysqli_fetch_object($result_item)) {
                                                $usagePercentage = ($row->current_usage / $row->max_usage) * 100;
                                                $isExpired = strtotime($row->expiry_date) < time();
                                                $statusClass = $isExpired ? 'danger' : ($row->status === 'Active' ? 'success' : 'warning');
                                            ?>
                                                <tr>
                                                    <td><?php echo $count++ ?></td>
                                                    <td>
                                                        <strong><?php echo htmlspecialchars($row->batchcode_name); ?></strong>
                                                        <button class="btn btn-xs btn-info" onclick="copyToClipboard('<?php echo $row->batchcode_name; ?>')" title="Copy to clipboard">
                                                            <i class="fa fa-copy"></i>
                                                        </button>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($row->batch_title); ?></td>
                                                    <td><?php echo date("M Y", strtotime($row->month_year . "-01")); ?></td>
                                                    <td>
                                                        <span class="badge bg-info"><?php echo $row->lesson_count; ?> lessons</span>
                                                    </td>
                                                    <td>
                                                        <div class="progress progress-xs">
                                                            <div class="progress-bar progress-bar-<?php echo $usagePercentage >= 90 ? 'danger' : ($usagePercentage >= 70 ? 'warning' : 'success'); ?>" 
                                                                 style="width: <?php echo $usagePercentage; ?>%"></div>
                                                        </div>
                                                        <span class="badge bg-<?php echo $usagePercentage >= 90 ? 'red' : ($usagePercentage >= 70 ? 'orange' : 'green'); ?>">
                                                            <?php echo $row->current_usage; ?>/<?php echo $row->max_usage; ?>
                                                        </span>
                                                    </td>
                                                    <td><?php echo $row->max_usage; ?></td>
                                                    <td>
                                                        <span class="label label-<?php echo $isExpired ? 'danger' : 'success'; ?>">
                                                            <?php echo date('d-m-Y', strtotime($row->expiry_date)); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="label label-<?php echo $statusClass; ?>">
                                                            <?php echo $row->status; ?>
                                                        </span>
                                                    </td>
                                                    <td><?php echo date('d-m-Y H:i:s', strtotime($row->created_date)); ?></td>
                                                    <td>
                                                        <a href="add-batchcode.php?id=<?php echo $row->id; ?>" class="btn btn-xs btn-warning" title="Edit">
                                                            <i class="fa fa-edit"></i> Edit
                                                        </a>
                                                        <?php if ($row->current_usage == 0): ?>
                                                            <a href="view-batchcode.php?del=<?php echo $row->id; ?>"
                                                                onclick="return confirm('Are you sure you want to delete this batchcode?');"
                                                                class="btn btn-xs btn-danger" title="Delete">
                                                                <i class="fa fa-trash"></i> Delete
                                                            </a>
                                                        <?php endif; ?>
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
            $('#example1').DataTable({
                'paging': true,
                'lengthChange': true,
                'searching': true,
                'ordering': true,
                'info': true,
                'autoWidth': false
            })
        })

        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(function() {
                alert('Batchcode copied to clipboard: ' + text);
            }, function(err) {
                console.error('Could not copy text: ', err);
                // Fallback for older browsers
                var textArea = document.createElement("textarea");
                textArea.value = text;
                document.body.appendChild(textArea);
                textArea.select();
                document.execCommand('copy');
                document.body.removeChild(textArea);
                alert('Batchcode copied to clipboard: ' + text);
            });
        }
    </script>
</body>

</html> 