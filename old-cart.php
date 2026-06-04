<?php
session_start();
include('admin/include/db_config.php');
$user_id = $_SESSION['user_id'];
include('include/cart_logic.php');
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

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

<script>
    // JavaScript to check if the user is logged in
    document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('checkout-btn').addEventListener('click', function () {
            var isLoggedIn = <?= json_encode($isLoggedIn) ?>;
            if (!isLoggedIn) {
                alert('Please login or register first.');
                window.location.href = 'login.php';
            } else {
                window.location.href = 'checkout.php';
            }
        });
    });

</script>




<!DOCTYPE html>
<html lang="zxx">
<?php include('include/head.php'); ?>
<style>
    @media (max-width: 768px) {
   .cart-table-wrapper {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch; /* Smooth scrolling on iOS */
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
    <?php include('include/header1.php'); ?>

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

 <div class="container" style="width: 95%; margin: 0 auto; padding: 20px;min-height:80%;">
    <!-- Check if cart is empty -->
    <?php if (empty($courses)): ?>
        <div class="alert alert-warning my-5 text-center" role="alert" style="background-color: #fff3cd; color: #856404; padding: 15px; border-radius: 5px;">
            Your cart is empty! Go to buy courses 
            <a class="btn btn-warning" href="courses.php" style="background-color: #fcd20c; padding: 10px 20px; border-radius: 5px;">Courses</a>
        </div>
    <?php else: ?>
        <!-- Cart Content -->
        <div class="cart-content">
            <!-- Cart Items -->
            <div class="cart-items">
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Subtotal</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($courses as $course): ?>
                      
                            <tr>
                              
                                <td data-label="Product">  <p style="padding-top:1rem;display:inline-block;"><?= htmlspecialchars($course['s_name']) ?></p></td>
                                <td data-label="Price">₹<?= htmlspecialchars($course['price']) ?></td>
                                <td data-label="Quantity">
                                    <?= htmlspecialchars($_SESSION['quantities'][$course['id']]) ?>
                                </td>
                                <td data-label="Subtotal">₹<span class="subtotal" id="subtotal-<?= $course['id'] ?>">
                                        <?= ($_SESSION['quantities'][$course['id']] * $course['price']) ?>
                                    </span></td>
                                <td data-label="Action">
                                    <a href="?remove_id=<?= $course['id'] ?>" onclick="return confirm('Are you sure you want to remove this item?')">Remove</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Order Summary -->
            <div class="cart-totals">
                <h3>Order Summary</h3>
                <p><strong>Total:</strong> ₹<span id="order-total"><?= number_format($total_price, 2) ?></span></p>
                <a href="javascript:void(0)" class="btn" id="checkout-btn">Proceed to Checkout</a>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
    /* Default Styles */
    .cart-content {
        display: flex;
        justify-content: space-between;
        margin-top: 30px;
        font-family: Arial, sans-serif;
    }

    .cart-items, .cart-totals {
        background-color: #fff;
        padding: 20px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        border-radius: 8px;
    }

    .cart-items {
        width: 65%;
    }

    .cart-totals {
        width: 30%;
    }

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

        .cart-items, .cart-totals {
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



