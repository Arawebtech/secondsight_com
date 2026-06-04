<?php
// Include error reporting and session management
error_reporting(0); 
session_start();
if(empty($_SESSION['name'])){
    header('Location:index.php');
    exit();
}

include("include/db_config.php");

// Initialize variables
$username = "";
$calendly_link = "";
$msg = "";

// Event Handling Function
function handleDoctorEvent($conn, $event, $data = []) {
    if ($event === 'edit') {
        $id = $data['id'];
        $sql = "SELECT * FROM doctors WHERE id = '$id'";
        $result = mysqli_query($conn, $sql);
        return mysqli_fetch_object($result);
    } elseif ($event === 'update') {
        $id = $data['id'];
        $username = $data['username'];
        $calendly_link = $data['calendly_link'];
        $sql = "UPDATE doctors SET username='$username', calendly_link='$calendly_link' WHERE id='$id'";
        return mysqli_query($conn, $sql);
    } elseif ($event === 'add') {
        $username = $data['username'];
        $calendly_link = $data['calendly_link'];
        $sql = "INSERT INTO doctors (username, calendly_link) VALUES ('$username', '$calendly_link')";
        return mysqli_query($conn, $sql);
    } else {
        return false;
    }
}

// Check if editing an existing doctor
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $doctor = handleDoctorEvent($conn, 'edit', ['id' => $id]);
    
    if ($doctor) {
        $username = $doctor->username;
        $calendly_link = $doctor->calendly_link;
    }
}

// Handle form submission for adding or updating doctor
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $calendly_link = $_POST['calendly_link'];

    if (isset($_POST['id']) && !empty($_POST['id'])) {
        // Update existing doctor
        if (handleDoctorEvent($conn, 'update', ['id' => $_POST['id'], 'username' => $username, 'calendly_link' => $calendly_link])) {
            $msg = "<p style='color:green;'>Doctor record has been updated successfully</p>";
            header('Location:view-credentials.php?status=Update');
            exit;
        } else {
            $msg = "<p style='color:red;'>Error updating the record: " . mysqli_error($conn) . "</p>";
        }
    } else {
        // Add new doctor
        if (handleDoctorEvent($conn, 'add', ['username' => $username, 'calendly_link' => $calendly_link])) {
            $msg = "<p style='color:green;'>Doctor record has been added successfully</p>";
            header('Location:view-credentials.php?status=Added');
            exit;
        } else {
            $msg = "<p style='color:red;'>Error adding the record: " . mysqli_error($conn) . "</p>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title><?php echo isset($_GET['id']) ? 'Edit Doctor' : 'Add Doctor'; ?></title>
       <link rel="icon" href="<?=$base_url;?>assets/img/logo-fav.png" type="image/png">
    <!-- Include Bootstrap, FontAwesome, and AdminLTE CSS -->
    <link rel="stylesheet" href="bower_components/bootstrap/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="bower_components/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="dist/css/AdminLTE.min.css">
    <link rel="stylesheet" href="dist/css/skins/_all-skins.min.css">
</head>

<body class="hold-transition skin-blue sidebar-mini">
    <div class="wrapper">
        <?php include('include/header.php'); ?>
        <?php include('include/side-bar.php'); ?>

        <div class="content-wrapper" style="margin-top: 44px;
    margin-left: 250px;">
            <section class="content-header">
                <h1><?php echo isset($_GET['id']) ? 'Edit Doctor' : 'Add Doctor'; ?></h1>
            </section>

            <section class="content">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><?php echo isset($_GET['id']) ? 'Edit Doctor' : 'Add Doctor'; ?></h3>
                    </div>

                    <div class="box-body">
                        <?php echo $msg; ?>
                        <form action="" method="POST">
                            <input type="hidden" name="id" value="<?php echo isset($_GET['id']) ? $id : ''; ?>">

                            <!-- Doctor Name Input -->
                            <div class="form-group">
                                <label for="username">Doctor Name</label>
                                <input type="text" class="form-control" id="username" name="username" value="<?php echo $username; ?>" placeholder="Enter doctor's name">
                            </div>

                            <!-- Calendly Link Input -->
                            <div class="form-group">
                                <label for="calendly_link">Calendly Link</label>
                                <input type="url" class="form-control" id="calendly_link" name="calendly_link" value="<?php echo $calendly_link; ?>" placeholder="Enter Calendly link" >
                            </div>

                            <!-- Submit Button -->
                            <div class="box-footer">
                                <button type="submit" class="btn btn-primary pull-right"><?php echo isset($_GET['id']) ? 'Update' : 'Save'; ?></button>
                            </div>
                        </form>
                    </div>
                </div>
            </section>
        </div>

        <?php include('include/footer.php'); ?>
    </div>

    <!-- Include JS dependencies -->
    <script src="bower_components/jquery/dist/jquery.min.js"></script>
    <script src="bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
    <script src="dist/js/adminlte.min.js"></script>
</body>
</html>
