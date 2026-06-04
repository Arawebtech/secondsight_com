<?php
session_start();
include('include/db_config.php');

if (empty($_SESSION['name'])) {
    header('Location:index.php');
    exit;
}

// Fetch batches with their lesson information for display
function fetchBatchesWithLessons($conn, $selected = '') {
    $sql = "SELECT b.id, b.batch_title, b.month_year, b.max_students,
                   (SELECT COUNT(*) FROM lesson_batch lb WHERE lb.batch_id = b.id) as lesson_count,
                   COUNT(DISTINCT ube.user_id) as enrolled_students
            FROM batch b
            LEFT JOIN user_batch_enrollments ube ON b.id = ube.batch_id AND ube.status = 'Active'
            WHERE b.status = 'Active'
            GROUP BY b.id, b.batch_title, b.month_year, b.max_students
            ORDER BY b.batch_title";
    
    $result = $conn->query($sql);

    $options = '<option value="">Select Batch</option>';
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $selectedAttr = ($selected == $row['id']) ? 'selected' : '';
            $availableSlots = $row['max_students'] - $row['enrolled_students'];
            $displayText = $row['batch_title'] . ' (' . $row['month_year'] . ') - Lessons: ' . $row['lesson_count'] . ' - Available: ' . $availableSlots . '/' . $row['max_students'];
            $options .= '<option value="' . $row['id'] . '" ' . $selectedAttr . '>' . htmlspecialchars($displayText) . '</option>';
        }
    }
    return $options;
}

// Function to generate a unique random alphanumeric string
function generateUniqueBatchCode($conn, $length = 6) {
    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $batchcode = '';
    $isUnique = false;
    $maxAttempts = 100; // Limit attempts to prevent infinite loops

    while (!$isUnique && $maxAttempts > 0) {
        $batchcode = '';
        for ($i = 0; $i < $length; $i++) {
            $batchcode .= $characters[rand(0, $charactersLength - 1)];
        }

        // Check if the generated batch code already exists in the database
        $checkSql = "SELECT COUNT(*) AS count FROM batchcode WHERE batchcode_name = ?";
        $stmt = $conn->prepare($checkSql);
        $stmt->bind_param("s", $batchcode);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        if ($row['count'] == 0) {
            $isUnique = true;
        } else {
            $maxAttempts--;
            // If length 6 is exhausted, increase length for next attempts
            if ($maxAttempts == 0 && $length < 10) {
                $length++;
                $maxAttempts = 100; // Reset attempts for new length
            } elseif ($maxAttempts == 0 && $length >= 10) {
                error_log("Failed to generate a unique batch code after many attempts and increasing length.");
                return false; // Indicate failure
            }
        }
    }
    return $batchcode;
}

// Fetch batchcode data through id for editing
$info_batchcode = null;
if (isset($_GET['id'])) {
    $batchcode_id = $_GET['id'];
    $query = "SELECT * FROM batchcode WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $batchcode_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $info_batchcode = $result->fetch_object();
    $stmt->close();
}

// Form Submit
if (isset($_POST['submit'])) {
    $batchId = intval($_POST['batch_id']);
    $max_usage = intval($_POST['max_usage']);
    $expiry_date = $_POST['expiry_date'];

    // Fetch batch max_students to validate
    $batch_query = "SELECT max_students, 
                    (SELECT COUNT(*) FROM user_batch_enrollments ube WHERE ube.batch_id = b.id AND ube.status = 'Active') as current_enrolled
                    FROM batch b WHERE id = ?";
    $batch_stmt = $conn->prepare($batch_query);
    $batch_stmt->bind_param("i", $batchId);
    $batch_stmt->execute();
    $batch_result = $batch_stmt->get_result();
    $batch_data = $batch_result->fetch_assoc();
    $batch_stmt->close();

    if ($batch_data) {
        $max_students = $batch_data['max_students'];
        $current_enrolled = $batch_data['current_enrolled'];
        $available_slots = $max_students - $current_enrolled;

        // Warn if max_usage exceeds available slots
        if ($max_usage > $available_slots) {
            echo "<script>alert('Warning: Max usage ($max_usage) exceeds available batch slots ($available_slots).');</script>";
        }
    }

    if (!empty($_POST['edit_id'])) {
        // UPDATE Mode
        $edit_id = intval($_POST['edit_id']);
        $update = "UPDATE batchcode SET max_usage = ?, expiry_date = ? WHERE id = ?";
        $stmt = $conn->prepare($update);
        $stmt->bind_param("isi", $max_usage, $expiry_date, $edit_id);
        
        if ($stmt->execute()) {
            exit("<script>window.location.href='view-batchcode.php?id=Updated';</script>");
        } else {
            echo "<script>alert('Error updating batchcode.');</script>";
        }
        $stmt->close();
    } else {
        // INSERT Mode
        // Generate a unique random batch code
        $batchcodeName = generateUniqueBatchCode($conn);

        if ($batchcodeName === false) {
            echo "<script>alert('Error: Could not generate a unique batch code. Please try again or contact support.');</script>";
        } else {
            $insert = "INSERT INTO batchcode (batch_id, batchcode_name, max_usage, expiry_date) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($insert);
            $stmt->bind_param("isis", $batchId, $batchcodeName, $max_usage, $expiry_date);

            if ($stmt->execute()) {
                exit("<script>window.location.href='view-batchcode.php?id=Added';</script>");
            } else {
                echo "<script>alert('Error creating batchcode.');</script>";
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?php echo isset($info_batchcode) ? 'Update Batch Code' : 'Generate Batch Code'; ?></title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <link rel="icon" href="<?=$base_url;?>assets/img/logo-fav.png" type="image/png">
    <link rel="stylesheet" href="bower_components/bootstrap/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="bower_components/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="dist/css/AdminLTE.min.css">
    <link rel="stylesheet" href="dist/css/skins/_all-skins.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css" rel="stylesheet" />
</head>
<body class="hold-transition skin-blue sidebar-mini">
    <div class="wrapper">
        <?php include('include/header.php');?>
        <?php include('include/side-bar.php');?>

        <div class="content-wrapper" style="margin-top:35px;padding-left:28px">
            <section class="content">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><?php echo isset($info_batchcode) ? 'Update Batch Code' : 'Generate Batch Code'; ?></h3>
                    </div>
                    <form method="post">
                        <div class="box-body" style="display:flex;flex-direction:row;flex:wrap;gap:25px;align-items:center;">
                         <?php if (isset($info_batchcode)) { ?>
                            <input type="hidden" name="edit_id" value="<?php echo $info_batchcode->id; ?>">
                        <?php } ?>
                           
                            <div class="form-group" style="width:40%;">
                                <label for="batch_id">Select Batch:</label>
                                <select id="batch_id" name="batch_id" class="form-control" <?php echo (isset($info_batchcode)) ? 'readonly disabled' : 'required'; ?>>
                                    <?php
                                    echo fetchBatchesWithLessons($conn, isset($info_batchcode) ? $info_batchcode->batch_id : '');
                                    ?>
                                </select>
                                <small class="help-block">Shows lesson count and available slots for each batch</small>
                            </div>

                            <div class="form-group col-6" style="width:25%;">
                                <label for="max_usage">Maximum Usage Count</label>
                                <input type="number" min="1" name="max_usage" id="max_usage" class="form-control" 
                                       value="<?php echo isset($info_batchcode) ? $info_batchcode->max_usage : '1'; ?>" required>
                            </div>
                            
                            <div class="form-group col-6" style="width:25%;">
                                <label for="expiry_date">Expiry Date:</label>
                                <input type="date" name="expiry_date" id="expiry_date" class="form-control" 
                                       value="<?php echo isset($info_batchcode) ? $info_batchcode->expiry_date : ''; ?>" required>
                            </div>
                          
                        </div>
                        <div class="box-footer form-group col-6" style="width:25%;padding-top: 32px;margin-left: 25px;">
                            <a href="view-batchcode.php" type="button" class="btn btn-warning">Back</a>
                            <button type="submit" name="submit" value="generate" class="btn btn-primary">
                                <?php echo (isset($info_batchcode)) ? 'Update' : 'Generate'; ?>
                            </button>
                        </div>
                    </form>
                </div>
            </section>
        </div>
    </div>

    <script src="bower_components/jquery/dist/jquery.min.js"></script>
    <script src="bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize Select2 for better dropdown experience
            $('#batch_id').select2({
                placeholder: "Select a batch",
                allowClear: true
            });

            // Set minimum date to today for expiry date
            var today = new Date().toISOString().split('T')[0];
            $('#expiry_date').attr('min', today);
        });
    </script>
</body>
</html> 