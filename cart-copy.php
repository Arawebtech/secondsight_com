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
                                            // $bannerImagePath = $base_url . "/assets/img/course-img/{$course['banner_image']}";
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
     <?php if ($isLoggedIn): ?>

<a href="checkout.php" class="btn"
   style="background: linear-gradient(to bottom, #ffb607 0%, #d76d0a 100%);
   color: #fff; border-radius: 5px; font-weight: bold;">
    Proceed to Checkout
</a>

<?php else: ?>

<a href="javascript:void(0)" class="btn"
   data-bs-toggle="modal"
   data-bs-target="#loginModal"
   style="background: linear-gradient(to bottom, #ffb607 0%, #d76d0a 100%);
   color: #fff; border-radius: 5px; font-weight: bold;">
    Proceed to Checkout
</a>

<?php endif; ?>
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


<!-- REGISTER MODAL -->
<!-- Register Modal -->
<div class="modal fade" id="registerModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">Register</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">

        <form id="register-form">

            <input type="text" id="reg-first-name" class="form-control mb-3"
                placeholder="First Name *" required>

            <input type="text" id="reg-middle-name" class="form-control mb-3"
                placeholder="Middle Name (Optional)">

            <textarea id="reg-address" class="form-control mb-3"
                placeholder="Full Address *" required></textarea>

            <input type="email" id="reg-email" class="form-control mb-3"
                placeholder="Email Address *" required>

            <input type="tel" id="reg-mobile" class="form-control mb-3"
                placeholder="Mobile No. *" required>

            <input type="password" id="reg-password" class="form-control mb-3"
                placeholder="Password *" required>

            <button type="submit" class="btn btn-success w-100">
                Register Now
            </button>

        </form>

      </div>

    </div>
  </div>
</div>

<!-- LOGIN MODAL -->
<div class="modal fade" id="loginModal">
  <div class="modal-dialog">
    <div class="modal-content p-4">

      <h4>Login</h4>

      <form id="login-form">
        <input type="email" id="login-email" class="form-control mb-3" placeholder="Email" required>

        <input type="password" id="login-password" class="form-control mb-3" placeholder="Password" required>

        <button type="submit" class="btn btn-primary w-100">
          Login
        </button>

        <p class="mt-3 text-center">
          New user?
<a href="javascript:void(0)" 
   class="btn btn-success"
   data-bs-toggle="modal" 
   data-bs-target="#registerModal">
   Register
</a>
        </p>
      </form>

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





document.getElementById("register-form")?.addEventListener("submit", function(e){
    e.preventDefault();

    fetch("register_ajax.php", {
        method:"POST",
        headers:{'Content-Type':'application/json'},
        body: JSON.stringify({
            first_name: document.getElementById("reg-first-name").value,
            middle_name: document.getElementById("reg-middle-name").value,
            address: document.getElementById("reg-address").value,
            email: document.getElementById("reg-email").value,
            mobile: document.getElementById("reg-mobile").value,
            password: document.getElementById("reg-password").value
        })
    })
    .then(res => res.text())
    .then(text => {
        try{
            return JSON.parse(text);
        }catch(e){
            alert("Server Error: " + text);
            return;
        }
    })
    .then(data=>{
        if(!data) return;

        if(data.status=="exists"){
            alert("Email already registered");
        }

        if(data.status=="success"){
            alert("Registration successful");
            location.reload();
        }

        if(data.status=="error"){
            alert(data.message);
        }
    })
    .catch(err=>{
        alert("Network error");
        console.log(err);
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
    
    
    <script>

// LOGIN
document.getElementById("login-form").addEventListener("submit", function(e){
    e.preventDefault();

    fetch("login_ajax.php", {
        method:"POST",
        headers:{'Content-Type':'application/json'},
        body: JSON.stringify({
            email: document.getElementById("login-email").value,
            password: document.getElementById("login-password").value
        })
    })
    .then(res=>res.json())
    .then(data=>{

        if(data.status == "not_found"){
            alert("Account not registered. Please create an account first.");
        }

        if(data.status == "wrong_password"){
            alert("Wrong password.");
        }

        if(data.status == "success"){
            location.reload();
        }

    });
});


// REGISTER
document.getElementById("register-form").addEventListener("submit", function(e){
    e.preventDefault();

    fetch("register_ajax.php", {
        method:"POST",
        headers:{'Content-Type':'application/json'},
        body: JSON.stringify({
            first_name: document.getElementById("reg-first-name").value,
            middle_name: document.getElementById("reg-middle-name").value,
            address: document.getElementById("reg-address").value,
            email: document.getElementById("reg-email").value,
            mobile: document.getElementById("reg-mobile").value,
            password: document.getElementById("reg-password").value
        })
    })
    .then(res=>res.json())
    .then(data=>{

        if(data.status == "exists"){
            alert("Email already registered. Please login.");
        }

        if(data.status == "success"){
            location.reload();
        }

    });
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



