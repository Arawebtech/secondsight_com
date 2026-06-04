<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include("admin/include/db_config.php");

$user_id = $_SESSION['user_id'];

/* USER DETAILS */
$stmt = $conn->prepare("SELECT name,email,mobile FROM users WHERE id=?");
$stmt->bind_param("i",$user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$name = $user['name'];
$email = $user['email'];
$mobile = $user['mobile'];

/* ORDER SUMMARY */
if(!isset($_SESSION['order_summary'])){
    header("Location: cart.php");
    exit();
}

$total1 = $_SESSION['order_summary']['total'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Checkout</title>
<?php include("include/head.php"); ?>

<style>
body {
    font-family: Arial;
    background:#f5f5f5;
    margin:0;
    padding:0;
}

.container {
    max-width: 1200px;
    margin: auto;
    display: flex;
    gap: 30px;
    padding: 20px;
}

.cart-section {
    flex:2;
    background:white;
    padding:20px;
    border-radius:8px;
}

.checkout-section {
    flex:1;
    background:white;
    padding:20px;
    border-radius:8px;
}

.cart-items table {
    width: 100%;
    border-collapse: collapse;
}

.cart-items th, .cart-items td {
    border-bottom:1px solid #ddd;
    padding: 10px;
    text-align: left;
    font-size: 14px;
}

.btn {
    background:#27ae60;
    color:white;
    padding:12px;
    border:none;
    width:100%;
    cursor:pointer;
    border-radius:5px;
    font-size:16px;
}

.btn:hover {
    background:#2ecc71;
}

.coupon-btn, .remove-coupon-btn {
    background:#3498db;
    padding:8px;
    width:100%;
    border:none;
    color:white;
    cursor:pointer;
    border-radius:4px;
    margin-top:5px;
}

.coupon-btn:hover, .remove-coupon-btn:hover {
    background:#2980b9;
}

.coupon-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.free-note {
    background:#d4edda;
    padding:12px;
    border-radius:5px;
    color:#155724;
    display:none;
}

@media(max-width:768px){
    .container {
        flex-direction: column;
    }

    .cart-items table, 
    .cart-items thead, 
    .cart-items tbody, 
    .cart-items th, 
    .cart-items td, 
    .cart-items tr {
        display: block;
    }

    .cart-items tr {
        margin-bottom: 15px;
        border-bottom: 2px solid #eee;
        padding-bottom:10px;
    }

    .cart-items td {
        text-align: right;
        padding-left: 50%;
        position: relative;
        font-size: 13px;
    }

    .cart-items td:before {
        content: attr(data-label);
        position: absolute;
        left: 10px;
        width: 45%;
        padding-left:10px;
        font-weight: bold;
        text-align: left;
    }

    .cart-items th {
        display: none; /* hide table headers on mobile */
    }
}
</style>
</head>
<body>

<?php include("include/header1.php"); ?>

<h2 style="text-align:center">Checkout</h2>

<div class="container">

<!-- CART SECTION -->
<div class="cart-section">
    <h3>Your Cart</h3>
    <div class="cart-items">
        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $total = 0;
                foreach ($_SESSION['cart'] as $course_id) {
                    $quantity = $_SESSION['quantities'][$course_id];
                    $stmt = $conn->prepare("SELECT * FROM courses WHERE id = ?");
                    $stmt->bind_param("i", $course_id);
                    $stmt->execute();
                    $course = $stmt->get_result()->fetch_assoc();
                    $stmt->close();
                    $subtotal = $course['price'] * $quantity;
                    $total += $subtotal;
                ?>
                <tr>
                    <td data-label="Product"><?= htmlspecialchars($course['s_name']) ?></td>
                    <td data-label="Price">₹<?= number_format($course['price'],2) ?></td>
                    <td data-label="Quantity"><?= $quantity ?></td>
                    <td data-label="Subtotal">₹<?= number_format($subtotal,2) ?></td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
        <p><strong>Total : ₹<?= number_format($total1,2) ?></strong></p>
    </div>
</div>

<!-- CHECKOUT SECTION -->
<!-- CHECKOUT SECTION -->
<div class="checkout-section">
  <h3>Payment Details</h3>

  <!-- Coupon Input -->
  <div class="coupon-wrapper">
    <input type="text" id="coupon" placeholder="Enter Coupon Code">
    <button type="button" id="apply-coupon" class="coupon-btn">Apply Coupon</button>
    <button type="button" id="remove-coupon" class="remove-coupon-btn" style="display:none;">Remove Coupon</button>
    <p id="coupon-message"></p>
  </div>

  <!-- Summary -->
  <div class="checkout-summary">
    <p>Total: <span id="total-price">₹<?=$total1?></span></p>
    <p id="discount-section" style="display:none; color:green;">
      Discount (<span id="discount-percent">0</span>%): -₹<span id="discount-amount">0</span>
    </p>
    <p id="payable-section" style="display:none; color:red;">
      Payable: ₹<span id="payable-amount">0</span>
    </p>
    <div id="free-note" class="free-note">🎉 This course is FREE for you</div>
  </div>

  <!-- Checkout Form -->
  <form method="POST" action="payment.php" id="checkout-form">
    <input type="hidden" id="discount-value" name="discount-value" value="0">
    <input type="hidden" id="coupon-code" name="coupon-code">
    <input type="hidden" id="paid_amount" name="paid_amount" value="<?=$total1?>">
    <input type="hidden" name="user_id" value="<?=$user_id?>">
    <input type="hidden" name="name" value="<?=$name?>">
    <input type="hidden" name="email" value="<?=$email?>">
    <input type="hidden" name="phone" value="<?=$mobile?>">
    <input type="hidden" id="amount" name="amount" value="<?=$total1?>">
    <?php foreach($_SESSION['cart'] as $course_id){
        echo '<input type="hidden" name="course_id[]" value="'.$course_id.'">';
    } ?>
    <button class="btn" id="order-btn">Proceed To Payment</button>
  </form>
</div>

<style>
.checkout-section {
    background: #fff;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    max-width: 400px;
    margin: 0 auto;
    font-family: 'Arial', sans-serif;
}

/* Heading */
.checkout-section h3 {
    font-size: 22px;
    font-weight: 600;
    margin-bottom: 20px;
    color: #333;
}

/* Coupon Section */
.coupon-wrapper {
    display: flex;
    flex-direction: column;
    gap: 10px;
    margin-bottom: 20px;
}

.coupon-wrapper input[type="text"] {
    padding: 12px 15px;
    border-radius: 8px;
    border: 1px solid #ccc;
    font-size: 15px;
    width: 100%;
    transition: border-color 0.3s;
}

.coupon-wrapper input[type="text"]:focus {
    border-color: #3498db;
    outline: none;
}

/* Buttons */
.coupon-btn, .remove-coupon-btn, #order-btn {
    padding: 12px;
    font-size: 16px;
    font-weight: 600;
    border-radius: 8px;
    border: none;
    cursor: pointer;
    transition: all 0.3s ease;
}

.coupon-btn {
    background: #3498db;
    color: #fff;
}

.coupon-btn:hover:not(:disabled) {
    background: #2980b9;
}

.coupon-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.remove-coupon-btn {
    background: #e74c3c;
    color: #fff;
}

.remove-coupon-btn:hover {
    background: #c5043c;
}

#order-btn {
    background: #27ae60;
    color: #fff;
    width: 100%;
    margin-top: 15px;
}

#order-btn:hover {
    background: #2ecc71;
}

/* Summary Section */
.checkout-summary p {
    font-size: 16px;
    margin: 8px 0;
    color: #555;
}

#discount-section span, #payable-section span {
    font-weight: 600;
}

/* Free Note */
.free-note {
    background: #d4edda;
    color: #155724;
    padding: 12px;
    border-radius: 8px;
    margin-top: 10px;
    font-size: 15px;
    display: none;
}

/* Responsive */
@media(max-width: 480px){
    .checkout-section {
        padding: 20px;
    }
    .checkout-section h3 {
        font-size: 20px;
    }
    .coupon-wrapper input[type="text"] {
        font-size: 14px;
        padding: 10px;
    }
    .coupon-btn, .remove-coupon-btn, #order-btn {
        font-size: 15px;
        padding: 10px;
    }
    .checkout-summary p {
        font-size: 14px;
    }
    .free-note {
        font-size: 14px;
    }
}
</style>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
$(document).ready(function(){

    var originalTotal = parseFloat(<?=$total1?>);

    function updateTotal(discount){
        var discountAmt = (originalTotal * discount)/100;
        var newTotal = originalTotal - discountAmt;

        if(discount > 0){
            $("#discount-section").show();
            $("#payable-section").show();
            $("#discount-amount").text(discountAmt.toFixed(2));
            $("#discount-percent").text(discount);
            $("#payable-amount").text(newTotal.toFixed(2));
            $("#paid_amount").val(newTotal);
            $("#amount").val(newTotal);

            if(newTotal <= 0){
                $("#free-note").show();
                $("#order-btn").text("Complete Free Order");
            } else {
                $("#free-note").hide();
                $("#order-btn").text("Proceed To Payment");
            }
        } else {
            $("#discount-section").hide();
            $("#payable-section").hide();
            $("#discount-amount").text("0");
            $("#discount-percent").text("0");
            $("#payable-amount").text("0");
            $("#paid_amount").val(originalTotal);
            $("#amount").val(originalTotal);
            $("#free-note").hide();
            $("#order-btn").text("Proceed To Payment");
        }
    }

    // Apply Coupon
    $("#apply-coupon").click(function(){
        var code = $("#coupon").val();
        if(code==""){
            $("#coupon-message").html("<span style='color:red'>Enter Coupon</span>");
            return;
        }

        var courseIds=[];
        <?php foreach($_SESSION['cart'] as $cid){ echo "courseIds.push($cid);"; } ?>

        $.ajax({
            url:"validate-coupon.php",
            type:"POST",
            dataType:"json",
            data:{
                coupon: code,
                course_ids: JSON.stringify(courseIds)
            },
            success:function(res){
                if(res.status=="success"){
                    $("#coupon-message").html("<span style='color:green'>Coupon Applied: "+res.discount+"% off</span>");
                    $("#discount-value").val(res.discount);
                    $("#coupon-code").val(res.couponcode);
                    updateTotal(res.discount);

                    $("#apply-coupon").prop('disabled', true).text("Coupon Applied");
                    $("#coupon").prop('disabled', true);
                    $("#remove-coupon").show();
                } else {
    $("#coupon-message").html("<span style='color:red'>Invalid coupon</span>");
}
            }
        });
    });

    // Remove Coupon
    $("#remove-coupon").click(function(){
        $("#discount-value").val(0);
        $("#coupon-code").val("");
        $("#coupon").val("").prop('disabled', false);
        $("#apply-coupon").prop('disabled', false).text("Apply Coupon");
        $("#remove-coupon").hide();
        $("#coupon-message").html("");
        updateTotal(0);
    });
});
</script>

</body>
</html>