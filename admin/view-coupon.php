<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
if (empty($_SESSION['name'])) {
    header('Location:index.php');
}
include("include/db_config.php");

/* -----delete record------ */
if (isset($_GET['del'])) {
    $id = $_GET['del'];
    $query  = "DELETE FROM coupon WHERE id ='$id'";
    $result = mysqli_query($conn, $query);
    if ($result) {
        $msg = "<p style='color:green;'>Coupon has been deleted successfully</p>";
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
    <title>Admin - Coupon Code</title>
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
            <section class="content-header mt-5" style="margin-top:45px;margin-left:25px;">
        
                <?php if (!empty($msg)) echo $msg;
                
                   if (isset($_GET['id']) && $_GET['id'] == 'Added') {
                        echo '<p style="color:green;padding-left:20px;">New Coupon has been generated successfully</p>';
                    }
                 
                ?>
            </section>

            <section class="content">
                <div class="row">
                    <div class="col-xs-12">
                        <div class="box">
                            <div class="box-header with-border">
                                <h3 class="box-title">Coupon Code List</h3>
                                <div class="box-tools pull-right">
                                    <a href="add-coupon.php" class="btn btn-primary">Add Coupon</a>
                                </div>
                            </div>
                            <div class="box-body">
                                <div class="table-responsive">
                                    <table id="example1" class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>SR.NO</th>
                                                <th>Coupon Code</th>
                                                <th>Discount/Amount</th>
                                                <!--<th>Type</th>-->
                                                <th>Applicant to</th>
                                                <th>No of times can used</th>
                                                <th>Already used</th>
                                                <th>Created Date</th>
                                                <th>Expiry Date</th>
                                                <th>Status</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $query = "
                                                        SELECT c.*, co.s_name AS course_name
                                                        FROM coupon c
                                                        LEFT JOIN courses co ON c.course_id = co.id
                                                        ORDER BY c.id DESC";
                                                        
                                            $result = mysqli_query($conn, $query);
                                            $count = 1;

                                            while ($row = mysqli_fetch_object($result)) { ?>
                                                <tr>
                                                    <td><?php echo $count++; ?></td>
                                                     <td><?php echo $row->code; ?></td>
                                                    <td><?php 
                                                        if (isset($row->type) && $row->type == 'flat') {
                                                            echo "₹" . number_format($row->discount, 2);
                                                        } else {
                                                            echo $row->discount . "%";
                                                        }
                                                     ?></td>
                                                    <!--<td><?php echo isset($row->type) ? ucfirst($row->type) : 'Percent'; ?></td>-->
                                                    
                                                <td>

<?php 

if ($row->applicable_to_all == 1 || empty($row->course_id)) {

    echo '<span class="badge bg-success">
    All Courses
    </span>';

} elseif (!empty($row->course_name)) {

    echo '<span class="badge bg-info">'
    . htmlspecialchars($row->course_name) .
    '</span>';

} else {

    echo '<span class="badge bg-danger">
    Course Not Found
    </span>';

}

?>

</td>
                                                    
                                                    <td><?php echo $row->no_of_times; ?></td>
                                                    <td><?php echo $row->used_count; ?></td>
                                                   
                                           <td><?php echo date("d-m-Y", strtotime($row->created_at)); ?></td>
                                           <td><?php echo date("d-m-Y", strtotime($row->expiry_date)); ?></td>


                                                    <td><?php if(strtotime($row->expiry_date) > strtotime(date("Y-m-d")) && $row->used_count < $row->no_of_times){ echo "<span style='color: green; font-weight: bold;'>Active</span>";
                                                      }else{ echo "<span style='color: red; font-weight: bold;'>Expired</span>";} ?></td>
                                                    
                                                    <td>
                                                      
                                                      
                                                   <?php 
                                                    $couponCode = $row->code; 
                                                    echo '<span id="coupon_'.$row->id.'" style="display:none;">'.$couponCode.'</span>'; 
                                                    ?>
                                                    <button class="copy-btn me-5" onclick="copyToClipboard('coupon_<?php echo $row->id; ?>')">
                                                        Copy
                                                    </button>
                                                        <a href="view-coupon.php?del=<?php echo $row->id; ?>" onclick="return confirm('Are you sure you want to delete this coupon?');"><i class="fa fa-trash" style="font-size:16px;color:#e40728;" title="Delete"></i></a>
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
<script>
function copyToClipboard(elementId) {
    var couponText = document.getElementById(elementId).innerText;
    navigator.clipboard.writeText(couponText).then(function() {
        alert("Coupon Code copied: " + couponText);
    }).catch(function(err) {
        console.error("Error copying text: ", err);
    });
}
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
