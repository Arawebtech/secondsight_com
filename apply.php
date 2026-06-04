<?php
session_start();
include('include/cart_logic.php'); 
include('admin/include/db_config.php');
$user_id = $_SESSION['user_id'];
?>

<?php
if (isset($_POST['submit'])) {
    $to = $_POST['email'];
    $subject = "Application Form Submission";

    $message = "Dear {$_POST['first_name']} {$_POST['last_name']},\n\n";
    $message .= "Thank you for submitting your application. Here is the information you provided:\n\n";
    $message .= "First Name: {$_POST['first_name']}\n";
    $message .= "Last Name: {$_POST['last_name']}\n";
    $message .= "Email: {$_POST['email']}\n";
    $message .= "Gender: {$_POST['gender']}\n";
    $message .= "\nRegards,\nUniversity Admin";

    $photo = $_FILES['student_photo'];
    $document = $_FILES['passport_document'];

    $boundary = md5(uniqid(time()));

    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "From: info@secondsightfoundation.com\r\n";
    $headers .= "Content-Type: multipart/mixed; boundary=\"$boundary\"\r\n";

    $body = "--$boundary\r\n";
    $body .= "Content-Type: text/plain; charset=\"UTF-8\"\r\n";
    $body .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
    $body .= $message . "\r\n";

    if ($photo['error'] == 0) {
        $photoContent = chunk_split(base64_encode(file_get_contents($photo['tmp_name'])));
        $body .= "--$boundary\r\n";
        $body .= "Content-Type: {$photo['type']}; name=\"{$photo['name']}\"\r\n";
        $body .= "Content-Disposition: attachment; filename=\"{$photo['name']}\"\r\n";
        $body .= "Content-Transfer-Encoding: base64\r\n\r\n";
        $body .= $photoContent . "\r\n";
    }

    if ($document['error'] == 0) {
        $documentContent = chunk_split(base64_encode(file_get_contents($document['tmp_name'])));
        $body .= "--$boundary\r\n";
        $body .= "Content-Type: {$document['type']}; name=\"{$document['name']}\"\r\n";
        $body .= "Content-Disposition: attachment; filename=\"{$document['name']}\"\r\n";
        $body .= "Content-Transfer-Encoding: base64\r\n\r\n";
        $body .= $documentContent . "\r\n";
    }

    $body .= "--$boundary--";

    if (mail($to, $subject, $body, $headers)) {
        echo "Application submitted successfully!";
    } else {
        echo "Failed to submit the application. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="zxx">

<?php
include('include/head.php');
?>

<body>

    <?php
include('include/header1.php');
?>

    <style>
        h2,
        h3 {
            text-align: center;
            color: #333;
        }

        .form-group {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
        }

        .form-group div {
            flex: 1;
            margin-right: 10px;
        }

        .form-group div:last-child {
            margin-right: 0;
        }

        label {
            display: block;
            margin-bottom: 5px;
            color: #555;
        }

        input,
        select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 14px;
        }

        input[type="file"] {
            padding: 5px;
        }

        .form-section {
            border: 1px solid #ddd;
            padding: 20px;
            border-radius: 6px;
            margin-bottom: 20px;
            background-color: #fafafa;
        }

        .form-section h3 {
            margin-bottom: 15px;
            color: #333;
        }

        .submit-btn {
            display: block;
            width: 100%;
            padding: 15px;
            background-color: #ffb607;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            text-align: center;
        }

        .submit-btn:hover {
            background-color: #ffb607;
        }

        @media (max-width: 768px) {
            .form-group {
                flex-direction: column;
            }

            .form-group div {
                margin-right: 0;
                margin-bottom: 15px;
            }

            h2 {
                font-size: 24px;
            }

            h3 {
                font-size: 20px;
            }

            input,
            select {
                font-size: 16px;
            }

            .submit-btn {
                padding: 12px;
                font-size: 18px;
            }
        }

        @media (max-width: 480px) {
            .form-section {
                padding: 15px;
            }

            input,
            select {
                font-size: 14px;
                padding: 8px;
            }

            h2 {
                font-size: 22px;
            }

            h3 {
                font-size: 18px;
            }

            .submit-btn {
                font-size: 16px;
                padding: 12px;
            }
        }
    </style>

    <div class="container">
        <h2 style="margin-top: 38px;">Application Form</h2>

        <form method="post" enctype="multipart/form-data">

            <div class="form-section">
                <h3>Basic Information</h3>
                <div class="form-group">
                    <div>
                        <label for="first_name">First Name</label>
                        <input type="text" id="first_name" placeholder="Your First Name">
                    </div>
                    <div>
                        <label for="last_name">Last Name</label>
                        <input type="text" id="last_name" placeholder="Your Last Name">
                    </div>
                </div>

                <div class="form-group">
                    <div>
                        <label for="program_type">Program Type</label>
                        <input type="text" id="program_type" name="program_type" placeholder="Your program_type">
                    </div>
                    <div>
                        <label for="degree_level">Degree Level</label>
                        <input type="text" id="degree_level" name="degree_level" placeholder="Your degree_level">
                    </div>
                </div>

                <div class="form-group">
                    <div>
                        <label for="student_photo">Student Photo</label>
                        <input type="file" id="student_photo">
                        <small>Your photo must be in passport size. Max upload size 1MB.</small>
                    </div>
                    <div>
                        <label for="passport_document">Upload Passport or Birth Document</label>
                        <input type="file" id="passport_document">
                        <small>Upload file must be ZIP. Max upload size 1MB.</small>
                    </div>
                </div>

                <h3>Personal Information</h3>
                <div class="form-group">
                    <div>
                        <label for="dob">Date of Birth</label>
                        <input type="date" id="dob" placeholder="mm/dd/yyyy">
                    </div>
                    <div>
                        <label for="id_number">National ID Or Passport ID</label>
                        <input type="text" id="id_number" placeholder="Your ID Number">
                    </div>
                </div>

                <div class="form-group">
                    <div>
                        <label for="gender">Gender</label>
                        <select name="gender" id="gender">
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div>
                        <label for="student_type">Student Type</label>
                        <input type="text" id="id_number" name="student_type" placeholder="Your student_type">
                    </div>
                </div>

                <h3>Academic Information</h3>
                <div class="form-group">
                    <div>
                        <label for="school">School</label>
                        <input type="text" id="school" placeholder="Your School">
                    </div>
                    <div>
                        <label for="completion_year">Year Of Completion</label>
                        <input type="text" id="completion_year" placeholder="Completion Year">
                    </div>
                </div>

                <div class="form-group">
                    <div>
                        <label for="qualification">Highest Qualification</label>
                        <input type="text" id="qualification" placeholder="Your Qualification">
                    </div>
                    <div>
                        <label for="current_status">Current Status</label>
                        <input type="text" id="qualification" name="current_status" placeholder="Your Qualification">
                    </div>
                </div>

                <div class="form-group">
                    <div>
                        <label for="study_area">Study Area</label>
                        <input type="text" id="study_area" name="study_area" placeholder="Your Study Area">
                    </div>
                    <div>
                        <label for="degree_level_academic">Degree Level</label>
                        <input type="text" id="degree_level_academic" name="degree_level_academic"
                            placeholder="Your Degree Level">
                    </div>
                </div>

                <button class="submit-btn" type="submit" name="submit">Submit Application</button>
            </div>
        </form>
    </div>

</body>

</html>