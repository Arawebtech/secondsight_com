<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
if (empty($_SESSION['name'])) {
    header('Location:index.php');
    exit;
}
include('include/db_config.php');

$msg = "";

// Handle Delete
if (isset($_GET['del'])) {
    $id = intval($_GET['del']);
    
    // get old file
    $stmt = $conn->prepare("SELECT schedule_file FROM batch WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if($row = $result->fetch_assoc()) {
        if(!empty($row['schedule_file']) && file_exists('uploads/schedules/' . $row['schedule_file'])) {
            unlink('uploads/schedules/' . $row['schedule_file']);
        }
    }
    $stmt->close();
    
    $stmt = $conn->prepare("UPDATE batch SET schedule_file=NULL WHERE id=?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        header("Location: schedule-batch.php?msg=deleted");
        exit;
    }
    $stmt->close();
}

// Handle Upload
if (isset($_POST['submit'])) {
    $batch_id = intval($_POST['batch_id']);
    
    if (empty($batch_id)) {
        $msg = "<div class='alert alert-danger'>Please select a batch.</div>";
    } elseif (empty($_FILES['schedule_file']['name'])) {
        $msg = "<div class='alert alert-danger'>Please select a file to upload.</div>";
    } else {
        $upload_dir = 'uploads/schedules/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file_name = time() . '_' . basename($_FILES['schedule_file']['name']);
        $file_name = preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $file_name);
        $target_file = $upload_dir . $file_name;
        $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        
        $allowed_types = ['pdf', 'jpg', 'jpeg', 'png', 'gif'];
        
        if (!in_array($file_type, $allowed_types)) {
            $msg = "<div class='alert alert-danger'>Invalid file type. Only PDF and Image files are allowed.</div>";
        } else {
            // Delete old file if exists
            $stmt = $conn->prepare("SELECT schedule_file FROM batch WHERE id=?");
            $stmt->bind_param("i", $batch_id);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($row = $res->fetch_assoc()) {
                if (!empty($row['schedule_file']) && file_exists($upload_dir . $row['schedule_file'])) {
                    unlink($upload_dir . $row['schedule_file']);
                }
            }
            $stmt->close();

            if (move_uploaded_file($_FILES['schedule_file']['tmp_name'], $target_file)) {
                $stmt = $conn->prepare("UPDATE batch SET schedule_file=? WHERE id=?");
                $stmt->bind_param("si", $file_name, $batch_id);
                if ($stmt->execute()) {
                    $msg = "<div class='alert alert-success'>Schedule uploaded successfully.</div>";
                } else {
                    $msg = "<div class='alert alert-danger'>Database error.</div>";
                }
                $stmt->close();
            } else {
                $msg = "<div class='alert alert-danger'>Failed to upload file.</div>";
            }
        }
    }
}

if(isset($_GET['msg']) && $_GET['msg']=='deleted') {
    $msg = "<div class='alert alert-success'>Schedule deleted successfully.</div>";
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Admin - Schedule Batch</title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <link rel="stylesheet" href="bower_components/bootstrap/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="bower_components/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="bower_components/Ionicons/css/ionicons.min.css">
    <link rel="stylesheet" href="dist/css/AdminLTE.min.css">
    <link rel="stylesheet" href="dist/css/skins/_all-skins.min.css">
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
    <?php include('include/header.php'); ?>
    <?php include('include/side-bar.php'); ?>

    <div class="content-wrapper">
        <section class="content-header">
            <h1>Schedule Batch</h1>
        </section>

        <section class="content">
            <?= $msg ?>
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Upload Batch Schedule (PDF or Image)</h3>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="box-body">
                        <div class="form-group">
                            <label>Select Batch *</label>
                            <select name="batch_id" class="form-control" required>
                                <option value="">-- Select Batch --</option>
                                <?php
                                $batches = $conn->query("SELECT id, batch_title FROM batch ORDER BY batch_title ASC");
                                while($b = $batches->fetch_assoc()) {
                                    echo "<option value='".$b['id']."'>".htmlspecialchars($b['batch_title'])."</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Upload File (PDF / Image) *</label>
                            <input type="file" name="schedule_file" class="form-control" accept=".pdf,image/*" required>
                            <small class="text-muted">Will overwrite any existing schedule for the selected batch.</small>
                        </div>
                    </div>
                    <div class="box-footer">
                        <button type="submit" name="submit" class="btn btn-primary">Upload Schedule</button>
                    </div>
                </form>
            </div>

            <!-- Existing Schedules -->
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title">Batches with Uploaded Schedules</h3>
                </div>
                <div class="box-body table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Batch Name</th>
                                <th>File Name</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmt = $conn->query("SELECT id, batch_title, schedule_file FROM batch WHERE schedule_file IS NOT NULL ORDER BY id DESC");
                            $i = 1;
                            while ($row = $stmt->fetch_assoc()) {
                                $ext = strtolower(pathinfo($row['schedule_file'], PATHINFO_EXTENSION));
                                $icon = ($ext == 'pdf') ? 'fa-file-pdf-o text-danger' : 'fa-file-image-o text-primary';
                                ?>
                                <tr>
                                    <td><?= $i++ ?></td>
                                    <td><?= htmlspecialchars($row['batch_title']) ?></td>
                                    <td><i class="fa <?= $icon ?>"></i> <?= htmlspecialchars($row['schedule_file']) ?></td>
                                    <td>
                                        <a href="uploads/schedules/<?= htmlspecialchars($row['schedule_file']) ?>" target="_blank" class="btn btn-sm btn-info">View File</a>
                                        <a href="schedule-batch.php?del=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to remove the schedule for this batch?');">Remove</a>
                                    </td>
                                </tr>
                                <?php
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>
    <?php include('include/footer.php'); ?>
</div>

<script src="bower_components/jquery/dist/jquery.min.js"></script>
<script src="bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
<script src="dist/js/adminlte.min.js"></script>
</body>
</html>
