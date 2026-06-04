<?php
error_reporting(0);
session_start();
if (empty($_SESSION['name'])) {
    header('Location:index.php');
}
include('include/db_config.php');

global $conn;

// Initialize variables
$batch_info = null;
$is_edit = false;

// Check if this is an edit operation
if (isset($_GET['id'])) {
    $is_edit = true;
    $batch_id = intval($_GET['id']);
    
    // Fetch batch data for editing
    $query = "SELECT * FROM batch WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $batch_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $batch_info = $result->fetch_object();
    $stmt->close();
}

// Handle form submission
if (isset($_POST['submit'])) {
    $batch_title = htmlspecialchars(trim($_POST['batch_title']));
    $description = htmlspecialchars(trim($_POST['description']));
    $month_yr = htmlspecialchars($_POST['month_year']);
    $max_students = intval($_POST['max_students']);

    if ($is_edit && isset($_POST['batch_id'])) {
        // Update existing batch
        $batch_id = intval($_POST['batch_id']);
        
        $sql_query = "UPDATE batch SET batch_title = ?, description = ?, month_year = ?, max_students = ? WHERE id = ?";
        $stmt = $conn->prepare($sql_query);
        $stmt->bind_param("sssii", $batch_title, $description, $month_yr, $max_students, $batch_id);
        
        if ($stmt->execute()) {
            exit("<script>window.location.href='view-batch.php?id=Update';</script>");
        } else {
            $error = "There was a problem updating the record: " . $stmt->error;
        }
        $stmt->close();
        
    } else {
        // Insert new batch
        $sql_query = "INSERT INTO batch (batch_title, description, month_year, max_students) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql_query);
        $stmt->bind_param("sssi", $batch_title, $description, $month_yr, $max_students);
        
        if ($stmt->execute()) {
            exit("<script>window.location.href='view-batch.php?id=Added';</script>");
        } else {
            $error = "There was a problem inserting the record: " . $stmt->error;
        }
        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?php echo $is_edit ? 'Update Batch' : 'Create Batch'; ?></title>
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

<style>
input[type="month"].placeholder::before {
    content: "Select Month & Year";
    color: gray;
}

.alert {
    margin: 15px 0;
}

.info-box {
    background-color: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 20px;
}

.info-box h5 {
    color: #495057;
    margin-bottom: 10px;
}

.info-box p {
    color: #6c757d;
    margin-bottom: 0;
}
</style>

<body class="hold-transition skin-blue sidebar-mini">
    <div class="wrapper">
        <?php include('include/header.php'); ?>
        <?php include('include/side-bar.php'); ?>

        <div class="content-wrapper" style="margin-top:35px; padding-left:30px;">
            <section class="content">
                <div class="row">
                    <div class="col-xs-12">
                        <div class="box box-primary">
                            <div class="box-header with-border">
                                <h3 class="box-title"><?php echo $is_edit ? 'Update Batch' : 'Create New Batch'; ?></h3>
                            </div>
                            <div class="box-body">
                                <?php if (isset($error)): ?>
                                    <div class="alert alert-danger"><?php echo $error; ?></div>
                                <?php endif; ?>

                                <div class="info-box">
                                    <h5><i class="fa fa-info-circle"></i> How it works:</h5>
                                    <p>Create a batch and then assign lessons to it. Users with batch codes can access all lessons in this batch.</p>
                                </div>

                                <form method="post" id="batchForm">
                                    <?php if ($is_edit): ?>
                                        <input type="hidden" name="batch_id" value="<?php echo $batch_info->id; ?>">
                                    <?php endif; ?>

                                    <div class="form-group">
                                        <label for="batch_title">Batch Title *</label>
                                        <input type="text" class="form-control" id="batch_title" name="batch_title" 
                                               value="<?php echo $is_edit ? htmlspecialchars($batch_info->batch_title) : ''; ?>" 
                                               required>
                                    </div>

                                    <div class="form-group">
                                        <label for="description">Description</label>
                                        <textarea class="form-control" id="description" name="description" rows="3" 
                                                  placeholder="Enter batch description"><?php echo $is_edit ? htmlspecialchars($batch_info->description) : ''; ?></textarea>
                                    </div>

                                    <div class="form-group">
                                        <label for="month_year">Month & Year *</label>
                                        <input type="month" class="form-control" id="month_year" name="month_year" 
                                               value="<?php echo $is_edit ? $batch_info->month_year : ''; ?>" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="max_students">Maximum Students</label>
                                        <input type="number" class="form-control" id="max_students" name="max_students" 
                                               value="<?php echo $is_edit ? $batch_info->max_students : '50'; ?>" 
                                               min="1" max="1000">
                                        <small class="help-block">Maximum number of students that can enroll in this batch</small>
                                    </div>

                                    <div class="box-footer">
                                        <button type="submit" name="submit" class="btn btn-primary">
                                            <?php echo $is_edit ? 'Update Batch' : 'Create Batch'; ?>
                                        </button>
                                        <a href="view-batch.php" class="btn btn-default">Cancel</a>
                                    </div>
                                </form>
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
        $(document).ready(function() {
            // Form validation
            $('#batchForm').submit(function(e) {
                var batchTitle = $('#batch_title').val().trim();
                if (batchTitle === '') {
                    alert('Please enter a batch title.');
                    e.preventDefault();
                    return false;
                }
            });
        });
    </script>
</body>

</html> 