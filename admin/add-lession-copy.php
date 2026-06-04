<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (empty($_SESSION['name'])) {
    header('Location:index.php');
    exit;
}

include('include/db_config.php');
global $conn;

if (isset($_POST['submit']) && $_POST['submit'] === 'Save') {
    $course_id = htmlspecialchars($_POST['course_id']);
    $batch_ids = isset($_POST['batch_ids']) ? $_POST['batch_ids'] : [];
    $course_name = "testing"; // You can modify if you want real course name here
    $lesson_title = htmlspecialchars($_POST['lesson_title']);
    $lesson_desc = htmlspecialchars($_POST['lesson_desc']);
    $video_alt = htmlspecialchars($_POST['video_alt']);
    $meta_keyword = htmlspecialchars($_POST['meta_keyword']);
    $meta_description = htmlspecialchars($_POST['meta_description']);
    $video_url = isset($_POST['uploaded_video_path']) ? $_POST['uploaded_video_path'] : '';
    $created_date = date("Y-m-d H:i:s");

    $video_thumbnail = ''; // or some logic to generate thumbnail later

    // Start transaction
    $conn->autocommit(FALSE);
    
    try {
        // Insert the lesson video
        $sql_query = "INSERT INTO lesson_video 
        (course_id, course_name, video_url, video_thumbnail, lesson_title, lesson_desc, video_alt, meta_keyword, meta_description, created_date) 
        VALUES 
        (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql_query);
        $stmt->bind_param("ssssssssss", $course_id, $course_name, $video_url, $video_thumbnail, $lesson_title, $lesson_desc, $video_alt, $meta_keyword, $meta_description, $created_date);
        $stmt->execute();
        $lesson_id = $conn->insert_id;
        $stmt->close();

        // Insert batch associations
        if (!empty($batch_ids)) {
            $batch_insert_query = "INSERT INTO lesson_batch (lesson_id, batch_id) VALUES (?, ?)";
            $batch_stmt = $conn->prepare($batch_insert_query);
            foreach ($batch_ids as $batch_id) {
                $batch_id = intval($batch_id);
                $batch_stmt->bind_param("ii", $lesson_id, $batch_id);
                $batch_stmt->execute();
            }
            $batch_stmt->close();
        }

        // Commit transaction
        $conn->commit();
        $conn->autocommit(TRUE);
        
        exit("<script>window.location.href='view-lesson.php?id=Added';</script>");
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        $conn->autocommit(TRUE);
        echo "There was a problem inserting the record: " . $e->getMessage();
    }
}

// Fetch courses for dropdown
$sql = "SELECT id as course_id, s_name as course_name FROM courses";
$result = $conn->query($sql);
$courses = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $courses[$row['course_id']] = $row['course_name'];
    }
}

// Fetch batches for multiselect
$sql = "SELECT id, batch_title FROM batch WHERE status = 'Active' ORDER BY batch_title";
$result = $conn->query($sql);
$batches = [];
if ($result->num_rows > 0) {
    while ($rowb = $result->fetch_assoc()) {
        $batches[$rowb['id']] = $rowb['batch_title'];
    }
}



if (isset($_GET['id']) && is_numeric($_GET['id'])) {

    $lession_id = intval($_GET['id']);

    $query = "SELECT * FROM lesson_video WHERE id = $lession_id";
    $result_lession = mysqli_query($conn, $query);

    if ($result_lession && mysqli_num_rows($result_lession) > 0) {

        $info_lession = mysqli_fetch_object($result_lession);

      

    } else {
        echo "No Record Found";
        exit;
    }
}


$conn->close();



?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Add Lesson Video</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="bower_components/bootstrap/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="bower_components/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="dist/css/AdminLTE.min.css">
    <link rel="stylesheet" href="dist/css/skins/_all-skins.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css" rel="stylesheet" />
     <link rel="icon" href="https://www.secondsightfoundation.com/assets/img/logo-fav.png" type="image/png">
</head>

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
                                <h3 class="box-title">Add Lesson</h3>
                            </div>
                            <div class="box-body">
                                <form name="addLessonForm" method="POST" id="addLessonForm">
                                    <input type="hidden" name="uploaded_video_path" id="uploadedVideoPath">

                                  <div class="form-group col-md-6">
    <label for="course_id">Select Course:</label>
    <select class="form-control" name="course_id" required>
        <option value="">Select Course</option>
        <?php foreach ($courses as $c_id => $name): ?>
            <option value="<?= htmlspecialchars($c_id) ?>"<?= (isset($info_lession) && $info_lession->course_id == $c_id) ? 'selected' : '' ?>>
                <?= htmlspecialchars($name) ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>

                                    <div class="form-group col-md-6">
                                        <!--<label for="batch_ids">Select Batches (Optional):</label>-->
                                        <!--<select class="form-control" name="batch_ids[]" id="batch_ids" multiple>-->
                                        <!--    <?php foreach ($batches as $b_id => $batch_name): ?>-->
                                        <!--        <option value="<?= htmlspecialchars($b_id) ?>"><?= htmlspecialchars($batch_name) ?></option>-->
                                        <!--    <?php endforeach; ?>-->
                                        <!--</select>-->
                                        
                                         <select class="form-control" name="course_id" required>
        <option value="">Select Batches</option>
       <?php foreach ($batches as $b_id => $batch_name): ?>
            <option value="<?= htmlspecialchars($b_id) ?>" <?= (isset($info_lession) && $info_lession->course_id == $b_id) ? 'selected' : '' ?>>
                <?= htmlspecialchars($batch_name) ?>
            </option>
        <?php endforeach; ?>
    </select>
                                        
                                        
                                        <small class="help-block">Hold Ctrl/Cmd to select multiple batches. This lesson will be available to users enrolled in any of these batches.</small>
                                    </div>

                                    <div class="form-group col-md-6">
    <label>Lesson Title:</label>
    <input type="text" 
           name="lesson_title"  
           value="<?php echo isset($info_lession->lesson_title) ? $info_lession->lesson_title : ''; ?>" 
           class="form-control" 
           required>
</div>


                                    <div class="form-group col-md-6">
                                        <label>Short Description:</label>
                                        <input type="text" value="<?php echo isset($info_lession->lesson_desc) ? $info_lession->lesson_desc : ''; ?>"  name="lesson_desc" class="form-control" required>
                                    </div>

                                    <!--<div class="form-group col-md-12">-->
                                    <!--    <label>Upload Video:</label>-->
                                    <!--    <input type="file" name="video_display" id="videoDisplay" class="form-control" accept="video/*" required>-->
                                    <!--    <small id="uploadStatus" class="text-info"></small>-->
                                    <!--</div>-->
                                    
                                    
                                    <div class="form-group col-md-12">
    <label>Upload Video:</label>
    <input type="file" 
           name="video_display" 
           id="videoDisplay" 
           class="form-control" 
           accept="video/*" 
           <?= isset($info_lession) ? '' : 'required' ?>>
    
    <!-- Show current video if editing -->
    <?php if (isset($info_lession) && !empty($info_lession->video_url)): ?>
        <div style="margin-top:10px;">
            <label>Current Video Preview:</label><br>
            <video width="300" height="200" controls>
                <source src="<?= $info_lession->video_url ?>" type="video/mp4">
                Your browser does not support the video tag.
            </video>
        </div>
    <?php endif; ?>

    <small id="uploadStatus" class="text-info"></small>
</div>


                                    <div class="form-group col-md-6">
                                        <label>Video Alt Text:</label>
                                        <input type="text" name="video_alt"  value="<?php echo isset($info_lession->video_alt) ? $info_lession->video_alt : ''; ?>"  class="form-control" required>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label>Meta Keyword:</label>
                                        <input type="text" name="meta_keyword" value="<?php echo isset($info_lession->meta_keyword) ? $info_lession->meta_keyword : ''; ?>" class="form-control" required>
                                    </div>

                                    <div class="form-group col-md-12">
                                        <label>Meta Description:</label>
                                        <input type="text" name="meta_description" value="<?php echo isset($info_lession->meta_description) ? $info_lession->meta_description : ''; ?>" class="form-control" required>
                                    </div>

                                    <div class="box-footer col-md-12">
                                        <input type="submit" name="submit" value="Save" class="btn btn-primary pull-right" id="submitBtn" disabled>
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
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize Select2 for batch multiselect
            $('#batch_ids').select2({
                placeholder: "Select batches (optional)",
                allowClear: true,
                multiple: true
            });

            $('#submitBtn').prop('disabled', true);

            $('#videoDisplay').on('change', function() {
                var file = this.files[0];
                if (!file) return;

                let formData = new FormData();
                formData.append('video', file);

                $('#uploadStatus').text("Uploading video...");
                $('#submitBtn').prop('disabled', true);

                $.ajax({
                    url: 'upload-handler.php',
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(res) {
                        // If res is already parsed JSON by jQuery, use it directly
                        var response = (typeof res === 'string') ? JSON.parse(res) : res;

                        if (response.status === 'success') {
                            $('#uploadedVideoPath').val(response.path);
                            $('#uploadStatus').text("Video uploaded successfully.");
                            $('#submitBtn').prop('disabled', false);
                        } else {
                            $('#uploadStatus').text("Upload failed: " + response.message);
                        }
                    },
                    error: function() {
                        $('#uploadStatus').text("Error uploading video.");
                    }
                });
            });
        });
    </script>
</body>

</html>