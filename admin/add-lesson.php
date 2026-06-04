// <?php
// session_start();
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

// if (empty($_SESSION['name'])) {
//     header('Location:index.php');
//     exit;
// }

// include('include/db_config.php');
// global $conn;

// if (isset($_POST['submit']) && $_POST['submit'] === 'Save') {
    
    
//     $course_id = htmlspecialchars($_POST['course_id']);
//     $batch_ids = isset($_POST['batch_ids']) ? $_POST['batch_ids'] : [];
//     $course_name = "testing"; // You can modify if you want real course name here
//     $lesson_title = htmlspecialchars($_POST['lesson_title']);
//     $lesson_desc = htmlspecialchars($_POST['lesson_desc']);
//     $video_alt = htmlspecialchars($_POST['video_alt']);
//     $meta_keyword = htmlspecialchars($_POST['meta_keyword']);
//     $meta_description = htmlspecialchars($_POST['meta_description']);
//     $video_url = isset($_POST['uploaded_video_path']) ? $_POST['uploaded_video_path'] : '';
//     $created_date = date("Y-m-d H:i:s");

//     $video_thumbnail = ''; // or some logic to generate thumbnail later

//     // Start transaction
//     $conn->autocommit(FALSE);
    
   
    
//     try {

//     if (empty($batch_ids)) {
//         die("Please select at least one batch.");
//     }

//     $sql_query = "INSERT INTO lesson_video 
//     (course_id, course_name, video_url, video_thumbnail, created_date, lesson_title, lesson_desc, status, video_alt, meta_keyword, meta_description, batch_id) 
//     VALUES 
//     (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

//     $stmt = $conn->prepare($sql_query);

//     foreach ($batch_ids as $batch_id) {

//         $batch_id = intval($batch_id);
//         $status = "Active";

//         $stmt->bind_param(
//             "issssssssssi",
//             $course_id,
//             $course_name,
//             $video_url,
//             $video_thumbnail,
//             $created_date,
//             $lesson_title,
//             $lesson_desc,
//             $status,
//             $video_alt,
//             $meta_keyword,
//             $meta_description,
//             $batch_id
//         );

//         $stmt->execute();
//     }

//     $stmt->close();

//     $conn->commit();
//     $conn->autocommit(TRUE);

//     exit("<script>window.location.href='view-lesson.php?id=Added';</script>");

// } catch (Exception $e) {

//     $conn->rollback();
//     $conn->autocommit(TRUE);
//     echo "Error: " . $e->getMessage();
// }

// }

// // Fetch courses for dropdown
// $sql = "SELECT id as course_id, s_name as course_name FROM courses";
// $result = $conn->query($sql);
// $courses = [];
// if ($result->num_rows > 0) {
//     while ($row = $result->fetch_assoc()) {
//         $courses[$row['course_id']] = $row['course_name'];
//     }
// }

// // Fetch batches for multiselect
// $sql = "SELECT id, batch_title FROM batch WHERE status = 'Active' ORDER BY batch_title";
// $result = $conn->query($sql);
// $batches = [];
// if ($result->num_rows > 0) {
//     while ($rowb = $result->fetch_assoc()) {
//         $batches[$rowb['id']] = $rowb['batch_title'];
//     }
// }
// $conn->close();
// ?>







<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (empty($_SESSION['name'])) {
    header('Location:index.php');
    exit;
}

include('include/db_config.php');

$edit_mode = false;
$edit_id = 0;
$lesson_data = [];

/* =========================
   CHECK EDIT MODE
========================= */
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $edit_mode = true;
    $edit_id = intval($_GET['id']);

    $stmt = $conn->prepare("SELECT * FROM lesson_video WHERE id=?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $lesson_data = $result->fetch_assoc();
    $stmt->close();
}

/* =========================
   ADD / UPDATE
========================= */
if (isset($_POST['submit'])) {

    $course_id = intval($_POST['course_id']);
    $batch_ids = $_POST['batch_ids'] ?? [];

    $lesson_title = trim($_POST['lesson_title']);
    $lesson_desc = trim($_POST['lesson_desc']);
    $video_alt = trim($_POST['video_alt']);
    $meta_keyword = trim($_POST['meta_keyword']);
    $meta_description = trim($_POST['meta_description']);

    $video_url = $_POST['uploaded_video_path'] ?? '';

    /* =========================
       THUMBNAIL UPLOAD
    ========================= */

 if (!empty($_FILES["video_thumbnail"]["name"])) {

    $video_thumbnail = time().'_'.$_FILES["video_thumbnail"]["name"];
    $tmp = $_FILES["video_thumbnail"]["tmp_name"];

    $folder = "uploads/thumbnails/";

    move_uploaded_file($tmp, $folder.$video_thumbnail);
}
    $status = "Active";
    $created_date = date("Y-m-d H:i:s");

    if (empty($batch_ids)) {
        die("Please select at least one batch.");
    }

    $conn->autocommit(FALSE);

    try {

        /* =========================
           UPDATE MODE
        ========================= */

        if ($edit_mode) {

            $batch_id = intval($batch_ids[0]);

            $update_sql = "UPDATE lesson_video SET
                course_id=?,
                lesson_title=?,
                lesson_desc=?,
                video_url=?,
                video_thumbnail=?,
                video_alt=?,
                meta_keyword=?,
                meta_description=?,
                batch_id=?
                WHERE id=?";

            $stmt = $conn->prepare($update_sql);

            $stmt->bind_param(
                "issssssssi",
                $course_id,
                $lesson_title,
                $lesson_desc,
                $video_url,
                $video_thumbnail,
                $video_alt,
                $meta_keyword,
                $meta_description,
                $batch_id,
                $edit_id
            );

            $stmt->execute();
            $stmt->close();

            $conn->commit();

            header("Location:view-lesson.php?id=Update");
            exit;

        }

        /* =========================
           INSERT MODE
        ========================= */

        else {

            $insert_sql = "INSERT INTO lesson_video
            (course_id, lesson_title, lesson_desc, video_url, video_thumbnail, status, video_alt, meta_keyword, meta_description, batch_id, created_date)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $conn->prepare($insert_sql);

            foreach ($batch_ids as $batch_id) {

                $batch_id = intval($batch_id);

                $stmt->bind_param(
                    "issssssssis",
                    $course_id,
                    $lesson_title,
                    $lesson_desc,
                    $video_url,
                    $video_thumbnail,
                    $status,
                    $video_alt,
                    $meta_keyword,
                    $meta_description,
                    $batch_id,
                    $created_date
                );

                $stmt->execute();
            }

            $stmt->close();
            $conn->commit();

            header("Location:view-lesson.php?id=Added");
            exit;

        }

    } catch (Exception $e) {

        $conn->rollback();
        echo "Error: " . $e->getMessage();

    }

}
/* =========================
   FETCH COURSES
========================= */
$courses = [];
$result = $conn->query("SELECT id, s_name FROM courses");
while ($row = $result->fetch_assoc()) {
    $courses[$row['id']] = $row['s_name'];
}

/* =========================
   FETCH BATCHES
========================= */
$batches = [];
$result = $conn->query("SELECT id, batch_title FROM batch WHERE status='Active'");
while ($row = $result->fetch_assoc()) {
    $batches[$row['id']] = $row['batch_title'];
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
      <link rel="icon" href="https://www.secondsightfoundation.com/assets/img/logo-fav.png" type="image/png">
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
                    <h3 class="box-title"><?= $edit_mode ? 'Edit Lesson' : 'Add Lesson' ?></h3>
                </div>
                <div class="box-body">
                    <form name="addLessonForm" method="POST" id="addLessonForm" enctype="multipart/form-data">
                        <input type="hidden" name="uploaded_video_path" id="uploadedVideoPath"
                               value="<?= $edit_mode ? htmlspecialchars($lesson_data['video_url']) : '' ?>">

                        <!-- Course Selection -->
                        <div class="form-group col-md-6">
                            <label for="course_id">Select Course:</label>
                            <select class="form-control" name="course_id" required>
                                <option value="">Select Course</option>
                                <?php foreach ($courses as $c_id => $name): ?>
                                    <option value="<?= htmlspecialchars($c_id) ?>"
                                        <?= ($edit_mode && $lesson_data['course_id'] == $c_id) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($name) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Batch Selection -->
                        <div class="form-group col-md-6">
                            <label for="batch_ids">Select Batches (Optional):</label>
                            <select class="form-control" name="batch_ids[]" id="batch_ids" multiple>
                                <?php foreach ($batches as $b_id => $batch_name): ?>
                                    <option value="<?= htmlspecialchars($b_id) ?>"
                                        <?= ($edit_mode && $lesson_data['batch_id'] == $b_id) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($batch_name) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="help-block">Hold Ctrl/Cmd to select multiple batches. This lesson will be available to users enrolled in any of these batches.</small>
                        </div>

                        <!-- Lesson Title -->
                        <div class="form-group col-md-6">
                            <label>Lesson Title:</label>
                            <input type="text" name="lesson_title" class="form-control" required
                                   value="<?= $edit_mode ? htmlspecialchars($lesson_data['lesson_title']) : '' ?>">
                        </div>

                        <!-- Short Description -->
                        <div class="form-group col-md-6">
                            <label>Short Description:</label>
                            <input type="text" name="lesson_desc" class="form-control" required
                                   value="<?= $edit_mode ? htmlspecialchars($lesson_data['lesson_desc']) : '' ?>">
                        </div>

                        <!-- Video Upload -->
                        <div class="form-group col-md-6">
                            <?php if ($edit_mode && !empty($lesson_data['video_url'])): ?>
                                <label>Current Video:</label>
                                <div style="margin-bottom:10px;">
                                    <video width="320" height="180" controls>
                                        <source src="<?= htmlspecialchars($lesson_data['video_url']) ?>" type="video/mp4">
                                        Your browser does not support the video tag.
                                    </video>
                                </div>
                                <label>Upload New Video (Optional):</label>
                                <input type="file" name="video_display" id="videoDisplay" class="form-control" accept="video/*">
                                <small id="uploadStatus" class="text-info"></small>
                            <?php else: ?>
                                <label>Upload Video:</label>
                                <input type="file" name="video_display" id="videoDisplay" class="form-control" accept="video/*" required>
                                <small id="uploadStatus" class="text-info"></small>
                            <?php endif; ?>
                        </div>
                         <!-- Video Alt -->
                        <div class="form-group col-md-6">
                            <label>Video Size : MB</label>
                            <input type="text" name="video_alt" class="form-control" placeholder="video size in mb" required
                                   value="<?= $edit_mode ? htmlspecialchars($lesson_data['video_alt']) : '' ?>">
                        </div>
<div class="form-group col-md-6">
<label>Video Thumbnail :</label>

<div class="input-group">
<div class="input-group-addon">
<i class="fa fa-image"></i>
</div>

<input type="file" name="video_thumbnail" class="form-control" onchange="previewVideoThumbnail(this)">
</div>

<div style="margin-top:10px;">

<?php if ($edit_mode && !empty($lesson_data['video_thumbnail'])) { ?>

<img id="videoThumbnailPreview"
src="https://www.secondsightfoundation.com/admin/uploads/thumbnails/<?php echo $lesson_data['video_thumbnail']; ?>"
width="150" height="100"
style="border:2px solid #ddd;border-radius:5px;object-fit:cover;">

<input type="hidden" name="video_thumbnail2"
value="<?php echo $lesson_data['video_thumbnail']; ?>">

<?php } else { ?>

<img id="videoThumbnailPreview"
style="display:none;border:2px solid #ddd;border-radius:5px;"
width="150" height="100">

<?php } ?>

</div>
</div>

                        

                        <!-- Meta Keyword -->
                        <div class="form-group col-md-6">
                            <label>Meta Keyword:</label>
                            <input type="text" name="meta_keyword" class="form-control" required
                                   value="<?= $edit_mode ? htmlspecialchars($lesson_data['meta_keyword']) : '' ?>">
                        </div>

                        <!-- Meta Description -->
                        <div class="form-group col-md-12">
                            <label>Meta Description:</label>
                            <input type="text" name="meta_description" class="form-control" required
                                   value="<?= $edit_mode ? htmlspecialchars($lesson_data['meta_description']) : '' ?>">
                        </div>

                        <div class="box-footer col-md-12">
                            <input type="submit" name="submit"
                                   value="<?= $edit_mode ? 'Update Lesson' : 'Save Lesson' ?>"
                                   class="btn btn-primary pull-right" id="submitBtn">
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
    
    
    function previewVideoThumbnail(input)
{
if (input.files && input.files[0]) {

var reader = new FileReader();

reader.onload = function(e)
{
var preview = document.getElementById('videoThumbnailPreview');
preview.src = e.target.result;
preview.style.display = 'block';
};

reader.readAsDataURL(input.files[0]);

}
}
    
    
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