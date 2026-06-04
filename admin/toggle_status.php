<?php
include("include/db_config.php");

if (isset($_GET['toggle'])) {
    $id = intval($_GET['toggle']);
    
    // Fetch the current status
    $query = "SELECT is_active FROM users WHERE id = $id";
    $result = mysqli_query($conn, $query);
    $user = mysqli_fetch_assoc($result);

    if ($user) {
        $new_status = $user['is_active'] ? 0 : 1; // Toggle status
        $update_query = "UPDATE users SET is_active = $new_status WHERE id = $id";

        if (mysqli_query($conn, $update_query)) {
            header("Location: view-registration.php?msg=Status updated successfully");
        } else {
            echo "Error updating status: " . mysqli_error($conn);
        }
    } else {
        echo "User not found.";
    }
} 

if (isset($_GET['togglecourse'])) {
    $id = intval($_GET['togglecourse']);
    
    // Fetch the current status
    $query = "SELECT status FROM courses WHERE id = $id";
    $result = mysqli_query($conn, $query);
    $user = mysqli_fetch_assoc($result);

    if ($user) {
       if ($user['status']=='Active') {
            $new_status = 'De Active';
        } else {
            $new_status = 'Active';
        }
        $update_query = "UPDATE courses SET status = '$new_status' WHERE id = $id";

        if (mysqli_query($conn, $update_query)) {
            header("Location: view-courses.php?msg=Status updated successfully");
        } else {
            echo "Error updating status: " . mysqli_error($conn);
        }
    } else {
        echo "User not found.";
    }
} 
if (isset($_GET['toggleteam'])) {
    $id = intval($_GET['toggleteam']);
    
    // Fetch the current status
    $query = "SELECT status FROM team WHERE id = $id";
    $result = mysqli_query($conn, $query);
    $user = mysqli_fetch_assoc($result);

    if ($user) {
       if ($user['status']=='Active') {
            $new_status = 'De Active';
        } else {
            $new_status = 'Active';
        }
        $update_query = "UPDATE team SET status = '$new_status' WHERE id = $id";

        if (mysqli_query($conn, $update_query)) {
            header("Location: view-team.php?msg=Status updated successfully");
        } else {
            echo "Error updating status: " . mysqli_error($conn);
        }
    } else {
        echo "User not found.";
    }
} 
if (isset($_GET['toggleblog'])) {
    $id = intval($_GET['toggleblog']);
    
    // Fetch the current status
    $query = "SELECT status FROM blog WHERE id = $id";
    $result = mysqli_query($conn, $query);
    $user = mysqli_fetch_assoc($result);

    if ($user) {
       if ($user['status']=='Active') {
            $new_status = 'De Active';
        } else {
            $new_status = 'Active';
        }
        $update_query = "UPDATE blog SET status = '$new_status' WHERE id = $id";

        if (mysqli_query($conn, $update_query)) {
            header("Location: view-blog.php?msg=Status updated successfully");
        } else {
            echo "Error updating status: " . mysqli_error($conn);
        }
    } else {
        echo "User not found.";
    }
} 
if (isset($_GET['togglelesson'])) {
    $id = intval($_GET['togglelesson']);
    
    // Fetch the current status
    $query = "SELECT status FROM lesson_video WHERE id = $id";
    $result = mysqli_query($conn, $query);
    $user = mysqli_fetch_assoc($result);

    if ($user) {
       if ($user['status']=='Active') {
            $new_status = 'De Active';
        } else {
            $new_status = 'Active';
        }
        $update_query = "UPDATE lesson_video SET status = '$new_status' WHERE id = $id";

        if (mysqli_query($conn, $update_query)) {
            header("Location: view-lesson.php?msg=Status updated successfully");
        } else {
            echo "Error updating status: " . mysqli_error($conn);
        }
    } else {
        echo "User not found.";
    }
} 

if (isset($_GET['togglebatch'])) {
    $id = intval($_GET['togglebatch']);
    
    // Fetch the current status
    $query = "SELECT status FROM batch WHERE id = $id";
    $result = mysqli_query($conn, $query);
    $batch = mysqli_fetch_assoc($result);

    if ($batch) {
       if ($batch['status']=='Active') {
            $new_status = 'Inactive';
        } else {
            $new_status = 'Active';
        }
        $update_query = "UPDATE batch SET status = '$new_status' WHERE id = $id";

        if (mysqli_query($conn, $update_query)) {
            header("Location: view-batch.php?msg=Status updated successfully");
        } else {
            echo "Error updating status: " . mysqli_error($conn);
        }
    } else {
        echo "Batch not found.";
    }
} 

if (isset($_GET['togglebatchcode'])) {
    $id = intval($_GET['togglebatchcode']);
    
    // Fetch the current status
    $query = "SELECT status FROM batchcode WHERE id = $id";
    $result = mysqli_query($conn, $query);
    $batchcode = mysqli_fetch_assoc($result);

    if ($batchcode) {
       if ($batchcode['status']=='Active') {
            $new_status = 'Inactive';
        } else {
            $new_status = 'Active';
        }
        $update_query = "UPDATE batchcode SET status = '$new_status' WHERE id = $id";

        if (mysqli_query($conn, $update_query)) {
            header("Location: view-batchcode.php?msg=Status updated successfully");
        } else {
            echo "Error updating status: " . mysqli_error($conn);
        }
    } else {
        echo "Batchcode not found.";
    }
} 
?>
