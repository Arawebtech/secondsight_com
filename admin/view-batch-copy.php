<?php
error_reporting(0);
session_start();

// ----------------- LOGIN CHECK -----------------
if (empty($_SESSION['name'])) {
    header('Location:index.php');
    exit();
}

include("include/db_config.php");

// ----------------- DELETE BATCH -----------------
if (isset($_GET['del'])) {
    $id = intval($_GET['del']);

    // Check if batch has enrollments
    $check_query = "SELECT COUNT(*) as enrollment_count FROM user_batch_enrollments WHERE batch_id = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $check_result = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($check_result['enrollment_count'] > 0) {
        $msg = "<p style='color:red;padding-left:20px;'>Cannot delete batch. It has enrolled students.</p>";
    } else {
        // Check batch codes
        $check_batchcode_query = "SELECT COUNT(*) as batchcode_count FROM batchcode WHERE batch_id = ?";
        $stmt = $conn->prepare($check_batchcode_query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $batchcode_result = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($batchcode_result['batchcode_count'] > 0) {
            $msg = "<p style='color:red;padding-left:20px;'>Cannot delete batch. It has associated batch codes.</p>";
        } else {
            // Check lessons
            $check_lesson_query = "SELECT COUNT(*) as lesson_count FROM lesson_batch WHERE batch_id = ?";
            $stmt = $conn->prepare($check_lesson_query);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $lesson_result = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($lesson_result['lesson_count'] > 0) {
                $msg = "<p style='color:red;padding-left:20px;'>Cannot delete batch. It has associated lessons.</p>";
            } else {
                // Delete batch
                $delete_query = "DELETE FROM batch WHERE id = ?";
                $stmt = $conn->prepare($delete_query);
                $stmt->bind_param("i", $id);
                $result = $stmt->execute();
                $stmt->close();

                if ($result) {
                    $msg = "<p style='color:green;padding-left:20px;'>Batch has been deleted successfully</p>";
                } else {
                    $msg = "<p style='color:red;padding-left:20px;'>There was a problem deleting the batch</p>";
                }
            }
        }
    }
}

// ----------------- TOGGLE STATUS -----------------
if (isset($_GET['togglebatch'])) {
    $id = intval($_GET['togglebatch']);

    // Fetch current status
    $stmt = $conn->prepare("SELECT status FROM batch WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $status = $stmt->get_result()->fetch_assoc()['status'] ?? '';
    $stmt->close();

    $newStatus = (trim($status) === 'Active') ? 'Inactive' : 'Active';

    $stmt = $conn->prepare("UPDATE batch SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $newStatus, $id);
    $stmt->execute();
    $stmt->close();

    header("Location: view-batch.php?msg=Status updated successfully");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Admin - Batch</title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <link rel="stylesheet" href="bower_components/bootstrap/dist/css/bootstrap.min.css">
    <link rel="icon" href="<?=$base_url;?>assets/img/logo-fav.png" type="image/png">
    <link rel="stylesheet" href="bower_components/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="bower_components/Ionicons/css/ionicons.min.css">
    <link rel="stylesheet" href="bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css">
    <link rel="stylesheet" href="dist/css/AdminLTE.min.css">
    <link rel="stylesheet" href="dist/css/skins/_all-skins.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700">
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
    <?php include('include/header.php'); ?>
    <?php include('include/side-bar.php'); ?>

    <div class="content-wrapper" style="margin-top:35px; padding-left:30px;">
        <section class="content">
            <div class="row">
                <div class="col-xs-12">
                    <div class="box">
                        <div class="box-header with-border">
                            <h3 class="box-title">Batch List</h3>
                            <div class="box-tools pull-right">
                                <a href="add-batch.php" class="btn btn-primary">Add Batch</a>
                            </div>
                        </div>
                        <div class="box-body">
                            <?php if(isset($_GET['msg'])): ?>
                                <p style="color:green;padding-left:20px;"><?=htmlspecialchars($_GET['msg']);?></p>
                            <?php endif; ?>
                            <?php if(isset($msg)) echo $msg; ?>

                            <div class="table-responsive">
                                <table id="example1" class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>SR.NO</th>
                                            <th>Batch Title</th>
                                            <th>Description</th>
                                            <th>Month-Year</th>
                                            <th>Lessons</th>
                                            <th>Enrolled Students</th>
                                            <th>Max Students</th>
                                            <th>Status</th>
                                            <th>Created Date</th>
                                            <th width="15%">ACTION</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $query = "SELECT b.*, 
                                                        (SELECT COUNT(*) FROM lesson_batch lb WHERE lb.batch_id = b.id) as lesson_count,
                                                        COUNT(DISTINCT ube.user_id) as enrolled_students
                                                  FROM batch b 
                                                  LEFT JOIN user_batch_enrollments ube ON b.id = ube.batch_id AND ube.status='Active'
                                                  GROUP BY b.id
                                                  ORDER BY b.id DESC";
                                        $result = mysqli_query($conn, $query);
                                        $count = 1;
                                        while($row = mysqli_fetch_object($result)):
                                            $enrollPercentage = ($row->max_students > 0) ? ($row->enrolled_students/$row->max_students)*100 : 0;
                                        ?>
                                        <tr>
                                            <td><?=$count++;?></td>
                                            <td><strong><?=htmlspecialchars($row->batch_title);?></strong></td>
                                            <td><?=htmlspecialchars($row->description ?: 'No description');?></td>
                                            <td><?=date("M Y", strtotime($row->month_year . "-01"));?></td>
                                            <td><span class="badge bg-info"><?=$row->lesson_count;?> lessons</span></td>
                                            <td><span class="badge bg-<?php echo $enrollPercentage>=90?'red':($enrollPercentage>=70?'orange':'green');?>"><?=$row->enrolled_students;?></span></td>
                                            <td><?=$row->max_students;?></td>
                                            <td>
                                                <a href="view-batch.php?togglebatch=<?=$row->id;?>">
                                                    <i class="fa <?=trim($row->status)==='Active'?'fa-toggle-on':'fa-toggle-off';?>" style="font-size:20px;color:<?=trim($row->status)==='Active'?'green':'red';?>" title="<?=trim($row->status)==='Active'?'Deactivate':'Activate';?>"></i>
                                                </a>
                                            </td>
                                            <td><?=date('d-m-Y H:i:s', strtotime($row->created_date));?></td>
                                            <td>
                                                <a href="add-batch.php?id=<?=$row->id;?>" class="btn btn-xs btn-warning"><i class="fa fa-edit"></i> Edit</a>
                                                <a href="view-batch.php?del=<?=$row->id;?>" onclick="return confirm('Are you sure you want to delete this batch?');" class="btn btn-xs btn-danger"><i class="fa fa-trash"></i> Delete</a>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
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
    });
});
</script>
</body>
</html>
