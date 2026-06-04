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

    // Step 1: Retrieve the video URL from the database
    // $selectSql = "SELECT video_url FROM lesson_video WHERE id = ?";
    // $selectStmt = $conn->prepare($selectSql);
    // $selectStmt->bind_param("i", $id);
    // $selectStmt->execute();
    // $selectStmt->bind_result($video_url);
    // $selectStmt->fetch();
    // $selectStmt->close();

    // Step 2: Delete the video file from the storage if it exists
    // if ($video_url && file_exists($video_url)) {
    //     unlink($video_url); // Delete the video file from storage
    // }

    // Step 3: Delete the record from the database
    $query = "DELETE FROM lesson_video WHERE id = ?";
    $deleteStmt = $conn->prepare($query);
    $deleteStmt->bind_param("i", $id);
    $result = $deleteStmt->execute();

    if ($result) {
        $msg = "<p style='color:green;padding-left:20px;'>Lesson has been deleted successfully</p>";
    } else {
        $msg = "There is some problem in deleting the record";
    }
    $deleteStmt->close();
}

?>



<?php
session_start();
include("include/db_config.php");

if (isset($_GET['togglelesson'])) {

    $id = intval($_GET['togglelesson']);

    $sql = "SELECT status FROM lesson_video WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($status);
    $stmt->fetch();
    $stmt->close();

    $status = trim($status);

    $newStatus = ($status == 'Active') ? 'Inactive' : 'Active';

    $update = "UPDATE lesson_video SET status = ? WHERE id = ?";
    $updateStmt = $conn->prepare($update);
    $updateStmt->bind_param("si", $newStatus, $id);
    $updateStmt->execute();
    $updateStmt->close();

    header("Location: view-lesson.php");
    exit();
}
?>



<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Admin- View Lesson</title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <link rel="stylesheet" href="bower_components/bootstrap/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="bower_components/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="bower_components/Ionicons/css/ionicons.min.css">
    <link rel="stylesheet" href="bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css">
    <link rel="icon" href="<?= $base_url; ?>assets/img/logo-fav.png" type="image/png">
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
                        echo '<p style="color:green;padding-left:20px;">New Lesson has been added successfully</p>';
                    }
                    if (isset($_GET['id']) and $_GET['id'] == 'Update') {
                        echo '<p style="color:green;padding-left:20px;">Record has been updated successfully</p>';
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
                                <h3 class="box-title">Lesson List</h3>
                                <div class="box-tools pull-right">
                                    <a href="add-lesson.php" class="btn btn-primary">Add Lesson</a>
                                </div>
                            </div>
                            <div class="box-body">
                                <div class="table-responsive">
                                    <table id="example1" class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>SR.NO</th>
                                                <th>Lesson Title</th>
                                                <th>Batch Name</th>
                                                <th>Video</th>
                                                <th>Video Thumbnail</th>
                                                <th>Course Name</th>
                                                <th>Status</th>
                                                <th width="10%">ACTION</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $userName = "John Doe";          // dynamically get from session or DB
                                            $userEmail = "john@example.com"; // dynamically get from session or DB
                                            $userUniqueId = "ABC123XYZ";     // dynamically get from session or DB

                                            // Default video URL to replace all videos
                                            $defaultVideoUrl = "https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4";
                                            ?>
                                            <?php
                                    $query = "
SELECT 
    lv.id, 
    lv.lesson_desc, 
    lv.lesson_title, 
    lv.video_url,
    lv.video_thumbnail,
    lv.video_alt,
    lv.status, 
    c.s_name,
    b.batch_title
FROM lesson_video lv
JOIN courses c ON lv.course_id = c.id 
LEFT JOIN batch b ON lv.batch_id = b.id
ORDER BY lv.video_alt ASC
";
                                     $result_item = mysqli_query($conn, $query);
                                            $count = 1;
                                            while ($info_item = mysqli_fetch_object($result_item)) {
                                                $videoUrl = !empty($info_item->video_url) ? $info_item->video_url : $defaultVideoUrl;
                                            ?>

                                                <tr style="color:<?php if (!empty($color)) {
                                                                        echo $color;
                                                                    } ?>">
                                                    <td><?php echo $count++ ?></td>
                                                    <td><?php echo $info_item->lesson_title; ?></td>
                                                 <td><?php echo $info_item->batch_title ?? 'No Batch'; ?></td>

<td style="width:30%; position: relative;">

<?php
$videoSizeMB = "0";

if(!empty($videoUrl)){

    // video file name nikalna
    $videoFile = basename($videoUrl);

    // correct server path
    $videoPath = $_SERVER['DOCUMENT_ROOT']."/demo/admin/uploads/videos/".$videoFile;

    if(file_exists($videoPath)){
        $videoSizeMB = round(filesize($videoPath)/(1024*1024),2);
    }
}
?>

<div class="video-container" style="position: relative; display: inline-block; width:150px;height:100px;">

<video
id="video_<?php echo $info_item->id; ?>"
width="150"
height="100"
controls
style="border-radius:4px;"
preload="metadata"
onloadedmetadata="showDuration(this, <?php echo $info_item->id; ?>)">

<source src="<?php echo $videoUrl; ?>" type="video/mp4">

</video>

<div style="font-size:12px;margin-top:3px;">
Video Size: <?php echo $info_item->video_alt; ?>MB
</div>

<div id="duration_<?php echo $info_item->id; ?>" style="font-size:12px;color:#555;">
Duration: Loading...
</div>

</div>

</td>

<td>
<?php if(!empty($info_item->video_thumbnail)) { ?>

<img src="https://www.secondsightfoundation.com/admin/uploads/thumbnails/<?php echo htmlspecialchars($info_item->video_thumbnail); ?>" 
width="120" height="80"
style="object-fit:cover;border-radius:5px;">

<?php } else { ?>

<span style="color:red;">No Thumbnail</span>

<?php } ?>
</td>                               <td>
                                                    <a href="view-lesson.php?togglelesson=<?php echo $info_item->id; ?>">
                                                    
                                                            <i class="fa <?php echo (trim($info_item->status) == 'Active' ? 'fa-toggle-on' : 'fa-toggle-off'); ?>"
                                                               style="font-size:20px; color:<?php echo (trim($info_item->status) == 'Active' ? 'green' : 'red'); ?>;"
                                                               title="<?php echo (trim($info_item->status) == 'Active' ? 'Deactivate' : 'Activate'); ?>">
                                                            </i>
                                                        </a>
                                                    </td>

                                                    <td width="10%">
                                                        <a href="add-lesson.php?id=<?php echo $info_item->id ?>"><i
                                                                class="fa fa-edit" style="font-size:16px;"
                                                                title="Edit"></i></a> |
                                                        <a href="view-lesson.php?del=<?php echo $info_item->id ?>"
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
    
    
    
    
    <script>
function showDuration(video,id){

let duration = video.duration;

let minutes = Math.floor(duration / 60);
let seconds = Math.floor(duration % 60);

document.getElementById("duration_"+id).innerHTML =
"Duration: "+minutes+"m "+seconds+"s";

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

        table th,
        table td {
            font-size: 12px;
            padding: 8px;
        }

        .btn-primary {
            padding: 6px 12px;
            font-size: 14px;
        }

        /* Reduce padding/margins for a cleaner look */
        .content-wrapper {
            padding: 10px;
        }

        /* Mobile video adjustments */
        .video-container {
            width: 120px !important;
            height: 80px !important;
        }

        .video-container video {
            width: 120px !important;
            height: 80px !important;
        }

        .watermark-overlay {
            font-size: 7px !important;
            max-width: 110px !important;
        }
    }

    /* Additional media query for very small screens */
    @media only screen and (max-width: 480px) {

        table th,
        table td {
            font-size: 10px;
            padding: 6px;
        }

        .btn-primary {
            padding: 4px 10px;
            font-size: 12px;
        }

        /* Very small screen video adjustments */
        .video-container {
            width: 100px !important;
            height: 70px !important;
        }

        .video-container video {
            width: 100px !important;
            height: 70px !important;
        }

        .watermark-overlay {
            font-size: 6px !important;
            max-width: 90px !important;
            padding: 1px 2px !important;
        }
    }

    /* Video container hover effects */
    .video-container:hover .watermark-overlay {
        background-color: rgba(0, 0, 0, 0.9);
        transition: background-color 0.3s ease;
    }

    /* Ensure video controls are accessible */
    .video-container video::-webkit-media-controls-panel {
        background-color: rgba(0, 0, 0, 0.8);
    }
</style>