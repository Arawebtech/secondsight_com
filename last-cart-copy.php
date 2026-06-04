<?php
session_start();
include('admin/include/db_config.php');
$user_id = $_SESSION['user_id'];
include('include/cart_logic.php');
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;


// Check if the quantity for this course is already set in session
if (!isset($_SESSION['quantities'][$course['id']])) {
    // Set default quantity to 1 if not set
    $_SESSION['quantities'][$course['id']] = 1;
}

?>
<?php
$_SESSION['order_summary'] = [
    'subtotal' => $subtotal,
    'gst' => $gst,
    'total' => $total_price,
];
?>
<?php
// Check if the user is logged in
$isLoggedIn = isset($_SESSION['user_id']) ? true : false;
?>

<?php

$email_prefill = $_SESSION['email'] ?? ''; // Session se email fetch karo

?>




<!DOCTYPE html>
<html lang="zxx">
<?php include('include/head.php'); ?>
<style>
    @media (max-width: 768px) {
   .cart-table-wrapper {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch; /* Smooth scrolling on iOS 
}

.cart-table {
    width: 100%; /* Ensure the table takes full width */
    border-collapse: collapse;
    table-layout: auto; /* Adjust column widths automatically */
}

.cart-table th,
.cart-table td {
    white-space: nowrap; /* Prevent content wrapping */
    padding: 10px;
    text-align: left;
}

}

</style>
<body>
    <?php 
    include('include/header1.php');
    ?>

    <!--<div class="page-title-area bg-4">-->
    <!--    <div class="container">-->
    <!--        <div class="page-title-content">-->
    <!--            <h2 style="color: #fcd20c;">Your Cart</h2>-->
                <!--<ul>-->
                <!--    <li><a href="index.php">Home</a></li>-->
                <!--    <li class="active">Your Cart</li>-->
                <!--</ul>-->
    <!--        </div>-->
    <!--    </div>-->
    <!--</div>-->

 <div class="container" style="width: 95%; margin: 0 auto; padding: 20px; min-height:80%;">
        <?php if (empty($courses)): ?>
            <div class="alert alert-warning my-5 text-center" role="alert" style="background-color: #fff3cd; color: #856404; padding: 15px; border-radius: 5px;">
                Your cart is empty! Go to buy courses
                <a class="btn btn-warning" href="courses.php" style="background-color: #fcd20c; padding: 10px 20px; border-radius: 5px;">Courses</a>
            </div>
        <?php else: ?>
            <div class="cart-content" style="background: #f8f9fa; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.07); padding: 30px 20px;">
                <!-- Cart Items -->
                <div class="cart-itemss" style="background: #fff; border-radius: 10px; box-shadow: 0 1px 6px rgba(0,0,0,0.04); padding: 20px;">
                    <table class="cart-table" style="width:100%; border-collapse: separate; border-spacing: 0 10px;">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Image</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Subtotal</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($courses as $course): ?>
                                <tr style="background: #f9f9f9;">
                                    <td data-label="Product" style="vertical-align: middle;">
                                        <span style="font-weight:600;"><?= htmlspecialchars($course['s_name']) ?></span>
                                    </td>
                                    <td data-label="Image">
                                        <?php
                                            // $bannerImagePath = $base_url . "assets/img/course-img/{$course['banner_image']}";
                                            $bannerImagePath = "/assets/img/course-img/" . rawurlencode($course['banner_image']);
                                        ?>
                                        <img src="<?= $bannerImagePath; ?>" alt="Product Image" style="width:60px; height:60px; object-fit:cover; border-radius:8px; border:1px solid #eee;">
                                    </td>
                                    <td data-label="Price" style="vertical-align: middle;">₹<?= htmlspecialchars($course['price']) ?></td>
                                    <td data-label="Quantity">
                                        <?= htmlspecialchars($_SESSION['quantities'][$course['id']] ?? 1) ?>
                                    </td>
                                    <td data-label="Subtotal" style="vertical-align: middle;">₹<span class="subtotal" id="subtotal-<?= $course['id'] ?>">
                                            <?= ($_SESSION['quantities'][$course['id']] * $course['price']) ?>
                                        </span>
                                    </td>
                                    <td data-label="Action" style="vertical-align: middle;">
                                        <a href="?remove_id=<?= $course['id'] ?>" class="text-danger" onclick="return confirm('Are you sure you want to remove this item?')" style="color:#e74c3c; font-weight:600; text-decoration:none;">Remove</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="cart-totals" style="background: #fff; border-radius: 10px; box-shadow: 0 1px 6px rgba(0,0,0,0.04); padding: 30px 20px; margin-left: 30px;">
                    <h3 style="font-weight:700; color:#222; margin-bottom:18px;">Order Summary</h3>
                    <div style="font-size:18px; margin-bottom:10px;">
                        <span style="font-weight:600;">Total:</span>
                        <span style="float:right; color:#fcd20c; font-weight:700;">₹<span id="order-total"><?= number_format($total_price, 2) ?></span></span>
                    </div>
                    <div style="margin-top:30px; display:flex; flex-direction:column; gap:12px;">
                        <a href="javascript:history.back()" class="btn"
                            style="background: -webkit-linear-gradient(top, #ffb607 0%, #d76d0a 100%);
                background: linear-gradient(to bottom, #ffb607 0%, #d76d0a 100%); color: #fff; border-radius: 5px; font-weight: bold;">
                            ⬅ Back
                        </a>
                        <a href="javascript:void(0)" class="btn" id="checkout-btn" data-bs-toggle="modal" data-bs-target="#registerModal" style="background: -webkit-linear-gradient(top, #ffb607 0%, #d76d0a 100%);
                background: linear-gradient(to bottom, #ffb607 0%, #d76d0a 100%);color: #fff; border-radius: 5px; font-weight: bold;">
                            Proceed to Checkout
                        </a>
                    </div>
                </div>
            </div>
            <style>
            
            .cart-table th {
                background: -webkit-linear-gradient(top, #ffb607 0%, #d76d0a 100%);
                background: linear-gradient(to bottom, #ffb607 0%, #d76d0a 100%);
                color:white;
                border: 1px solid var(--main-color);
            }
                @media (max-width: 900px) {
                    .cart-content {
                        flex-direction: column !important;
                    }
                    .cart-totals {
                        margin-left: 0 !important;
                        margin-top: 20px;
                    }
                }
                @media (max-width: 600px) {
                    .cart-itemss, .cart-totals {
                        padding: 10px !important;
                    }
                    .cart-table th, .cart-table td {
                        padding: 8px !important;
                    }
                    .cart-totals h3 {
                        font-size: 20px !important;
                    }
                }
            </style>
        <?php endif; ?>
    </div>

<!-- Registration Modal -->
<div class="modal fade" id="registerModal" tabindex="-1" aria-labelledby="registerModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="registerModalLabel">Add Or Review details</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
           <?php if(!isset($_SESSION['email'])  && !isset($_SERVER['HTTP_REFERER']) || strpos($_SERVER['HTTP_REFERER'], 'profile.php') === false){ ?>
                <form id="registration-form" method="post">
             
                    <!-- Email Section -->
                    <div class="mb-3" id="email-section">
                        <label for="email" class="form-label">Email</label>
                     <input type="email" class="form-control" id="email" name="email" required>
                          <br>
                        <button type="button" class="btn btn-primary" id="verify-email-btn">Verify Email</button>
                    </div>

                    <!-- OTP Section -->
                    <div class="mb-3" id="otp-section" style="display:none;">
                        <label for="otp" class="form-label">OTP</label>
                        <input type="text" class="form-control" id="otp" name="otp" required>
                        <br>
                        <button type="button" class="btn btn-primary" id="verify-otp-btn">Verify OTP</button>
                    </div>
          
                    <div id="other-fields" style="display:none;">
                        
                        
                         <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <!--<input type="email" class="form-control" id="email-other" name="email" required>-->
                            <input type="email" class="form-control" id="email-other" name="email" value="<?= htmlspecialchars($email_prefill); ?>" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="mobile" class="form-label">Mobile</label>
                            <input type="tel" class="form-control" id="mobile" name="mobile" pattern="[0-9]{10}" title="Enter 10 digit mobile number" required>
                        </div>
                        <div class="mb-3 form-group password-field" style="top: 68%;" >
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                             <i class="fas fa-eye toggle-password" id="togglePassword"></i>
                        </div>
                      <div class="mb-3">
    <label for="confirm-password" class="form-label">Confirm Password</label>
    <input type="password" class="form-control" id="confirm-password" name="confirm_password" required>
</div>

                        <button type="submit" class="btn btn-primary">Next</button>
                    </div>
                </form>
  <?php }else{ ?>
            <script>
                          window.location.href = 'checkout.php';
                  </script>
           <?php } ?>
            </div>
        </div>
    </div>
</div>



<script>
    document.getElementById('checkout-btn').addEventListener('click', function () {
    // Clear previous form data
    document.getElementById('email').value = '';
    document.getElementById('otp').value = '';
    document.getElementById('other-fields').style.display = 'none';
    document.getElementById('otp-section').style.display = 'none';
    document.getElementById('email-section').style.display = 'block';
});



document.getElementById('verify-email-btn').addEventListener('click', function () {
    const email = document.getElementById('email').value;

    if (email) {
        fetch('send_otp.php', {
            method: 'POST',
            body: JSON.stringify({ email: email }),
            headers: {
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('OTP sent to your email.');
                document.getElementById('otp-section').style.display = 'block';
                document.getElementById('email-section').style.display = 'none';
            } else {
                alert(data.message);
            }
        });
    }
});


const emailOtherField = document.getElementById('email-other');
document.getElementById('verify-otp-btn').addEventListener('click', function () {
    const otp = document.getElementById('otp').value;
    const email = document.getElementById('email').value;

    if (otp) {
        fetch('verify_otp.php', {
            method: 'POST',
            body: JSON.stringify({ otp: otp, email: email }),
            headers: {
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.valid) {
                alert('OTP verified successfully!');
                
                document.getElementById('email-other').value = email;

                // Check if the user exists after OTP verification
                fetch('check_email_exists_after_otp.php', {
                    method: 'POST',
                    body: JSON.stringify({ email: email }),
                    headers: {
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.exists) {
                        emailOtherField.value = data.user.email;
                        // If email exists, prefill the form with existing user data
                        document.getElementById('name').value = data.user.name;
                        document.getElementById('password').value = data.user.password;
                        document.getElementById('mobile').value = data.user.mobile;
                        document.getElementById('email').value = data.user.email;
                        document.getElementById('confirm-password').value = data.user.password;
                        
                        sessionStorage.setItem('user_id', data.user.id);
                        alert('Email already exists.');
                        
                         document.getElementById('name').readOnly = true;
                        document.getElementById('mobile').readOnly = true;
                        document.getElementById('password').readOnly = true;
                        document.getElementById('confirm-password').readOnly = true;
                        
                        
                        
                        // Show the form and keep it until the "Next" button is clicked
                        document.getElementById('other-fields').style.display = 'block';
                        document.getElementById('otp-section').style.display = 'none';
                    } else {
                        // If email doesn't exist, proceed with registration
                        document.getElementById('other-fields').style.display = 'block';
                        document.getElementById('otp-section').style.display = 'none';
                    }
                });
            } else {
                alert(data.message);
            }
        });
    }
});





document.getElementById('registration-form').addEventListener('submit', function(e) {
    e.preventDefault(); // Prevent default form submission

    // Get form data
    const email = document.getElementById('email').value;
    const name = document.getElementById('name').value;
    const mobile = document.getElementById('mobile').value;
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm-password').value;

   if (password !== confirmPassword) {
        alert('Passwords do not match. Please re-enter.');
        return; // Stop form submission
    }
    // Check if the user already exists before sending the data
    fetch('check_email_exists_after_otp.php', {
        method: 'POST',
        body: JSON.stringify({ email: email }),
        headers: {
            'Content-Type': 'application/json'
        }
    })
   
   
    .then(response => response.json())
    .then(data => {
      if (data.exists) {
                        // Prefill form fields with existing user data
                        document.getElementById('name').value = data.user.name;
                        document.getElementById('password').value = data.user.password;
                        document.getElementById('mobile').value = data.user.mobile;

                        // Store user ID in the session and redirect to checkout
                        sessionStorage.setItem('user_id', data.user.id); // Using sessionStorage for session continuity
                        alert('Email already exists. Redirecting to checkout.');
                        window.location.href = 'checkout.php';
                        // document.getElementById('other-fields').style.display = 'block';
        } else {
            // If email doesn't exist, proceed with registration
            const formData = new FormData();
            formData.append('email', email);
            formData.append('name', name);
            formData.append('mobile', mobile);
            formData.append('password', password);
            // formData.append('confirmPassword', confirmPassword);

            // Send the data to the server to register the user
            fetch('cartdemo2.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Server returned an error: ' + response.statusText);
                }
                return response.json();
            })
            .then(data => {
                // Handle the response from the server
                if (data.success) {
                    alert(data.message);
                    // Redirect to checkout if registration is successful
                    window.location.href = data.redirect;
                } else {
                    alert(data.message || 'An error occurred.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('A network error occurred. Please try again.');
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while checking the email.');
    });
});


// Handling next button click event (if you want to manually handle checkout redirection)
document.getElementById('next-button').addEventListener('click', function () {
    // Redirect to the checkout page
    window.location.href = 'checkout.php';
});
</script>
<style>
    /* Default Styles */
    .cart-content {
        display: flex;
        justify-content: space-between;
        margin-top: 30px;
        font-family: Arial, sans-serif;
    }

    .cart-itemss, .cart-totals {
        background-color: #fff;
        padding: 20px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        border-radius: 8px;
    }

    /*.cart-items {*/
    /*    width: 65%;*/
    /*}*/

    /*.cart-totals {*/
    /*    width: 30%;*/
    /*}*/

    .cart-table {
        width: 100%;
        border-collapse: collapse;
    }

    .cart-table thead tr {
        background-color: #fcd20c;
        color: #fff;
        font-weight: bold;
    }

    .cart-table th, .cart-table td {
        padding: 15px;
        border-bottom: 1px solid #ddd;
    }

    .cart-table tr:nth-child(even) {
        background-color: #f9f9f9;
    }

    .btn {
        background-color: #fcd20c;
        padding: 10px 20px;
        color: #fff;
        border-radius: 5px;
        text-decoration: none;
        font-size: 16px;
        display: inline-block;
        text-align: center;
    }

    /* Responsive Design for Mobile */
    @media (max-width: 768px) {
        .cart-content {
            flex-direction: column;
        }

        .cart-itemss, .cart-totals {
            width: 100%;
            margin-bottom: 20px;
            padding: 1px;
        }

        .cart-table thead {
            display: none;
        }

        .cart-table tr {
            display: block;
            margin-bottom: 15px;
        }

        .cart-table td {
            display: block;
            text-align: right;
            padding: 10px 5px;
            border-bottom: 1px solid #ddd;
        }

        .cart-table td:before {
            content: attr(data-label);
            font-weight: bold;
            text-transform: uppercase;
            display: inline-block;
            text-align: left;
            float: left;
            color: #333;
        }

        .btn {
            width: 100%;
            text-align: center;
        }

        .cart-totals h3, .cart-totals p {
            text-align: center;
        }
    }
</style>
    <?php include('include/footer.php'); ?>
    <?php include('include/footer-script.php'); ?>
</body>

</html>
<script>
    function changeQuantity(courseId, increment) {
        const currentQuantity = parseInt(document.getElementById(`quantity-${courseId}`).value);
        let newQuantity = currentQuantity + increment;

        if (newQuantity < 1 || newQuantity > 10) return; // Avoid invalid quantities

        // Redirect to the same page with quantity change
        window.location.href = `?course_id=${courseId}&change_quantity=${increment}`;
    }
</script>
 <script>
        const togglePassword = document.getElementById('togglePassword');
        const passwordField = document.getElementById('password');
        togglePassword.addEventListener('click', function () {
            const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordField.setAttribute('type', type);
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });
    </script>
    <style>
        @media screen and (max-width:600px) {
            .login-btn-mobile {
                margin-left: 0;
                padding: 10px;
            }
        }
         .password-field {
            position: relative;
            width: 100%;
            margin-bottom: 10px;
        }
           .password-field .toggle-password {
            position: absolute;
            top: 72%;
            right: 10px;
            transform: translateY(-50%);
            cursor: pointer;
            color: #4B4B4B;
        }
    </style>



