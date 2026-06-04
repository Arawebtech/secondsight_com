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
*{
    box-sizing:border-box;
}

body{
    font-family: Arial, sans-serif;
    background:#f5f5f5;
    margin:0;
}

/* CONTAINER */

.container{
    max-width:1200px;
    margin:auto;
    display:flex;
    gap:25px;
    padding:20px;
}

/* SECTIONS */

.cart-section,
.checkout-section{
    background:#fff;
    padding:20px;
    border-radius:10px;
    box-shadow:0 3px 10px rgba(0,0,0,0.05);
}

.cart-section{
    flex:2;
}

.checkout-section{
    flex:1;
    position:sticky;
    top:20px;
    height:fit-content;
}

/* TABLE */

.table-responsive{
    width:100%;
    overflow-x:auto;
}

table{
    width:100%;
    border-collapse:collapse;
    min-width:520px;
}

table th{
    background:#f8f8f8;
    font-weight:600;
}

table th,
table td{
    padding:12px;
    border-bottom:1px solid #ddd;
    text-align:left;
}

/* INPUT */

input[type="text"]{
    width:100%;
    padding:12px;
    border:1px solid #ccc;
    border-radius:6px;
    font-size:15px;
    margin-top:10px;
}

/* BUTTON */

.btn{
    background:#27ae60;
    color:white;
    padding:14px;
    border:none;
    width:100%;
    cursor:pointer;
    border-radius:6px;
    font-size:16px;
    margin-top:15px;
    font-weight:600;
}

.btn:hover{
    background:#2ecc71;
}

.coupon-btn,
.remove-coupon-btn{
    padding:11px;
    width:100%;
    border:none;
    color:white;
    cursor:pointer;
    border-radius:6px;
    margin-top:10px;
    font-size:15px;
}

.coupon-btn{
    background:#3498db;
}

.remove-coupon-btn{
    background:#c5043c;
}

.coupon-btn:hover{
    background:#2980b9;
}

.coupon-btn:disabled{
    opacity:0.6;
    cursor:not-allowed;
}

/* FREE NOTE */

.free-note{
    background:#d4edda;
    padding:12px;
    border-radius:6px;
    color:#155724;
    margin-top:10px;
    display:none;
}

/* HEADINGS */

h2{
    text-align:center;
    margin-top:15px;
}

h3{
    margin-top:0;
}

/* TABLET */

@media(max-width:992px){

.container{
    flex-direction:column;
}

.checkout-section{
    position:relative;
    top:0;
}

}

/* MOBILE */

@media(max-width:768px){

.container{
    padding:15px;
}

.cart-section,
.checkout-section{
    padding:16px;
}

table th,
table td{
    padding:9px;
    font-size:14px;
}

input[type="text"]{
    padding:10px;
    font-size:14px;
}

.btn,
.coupon-btn,
.remove-coupon-btn{
    padding:12px;
    font-size:14px;
}

}

/* SMALL MOBILE */

@media(max-width:480px){

.container{
    padding:10px;
}

table{
    min-width:100%;
}

h2{
    font-size:20px;
}

}
</style>
</head>

<body>

<?php include("include/header1.php"); ?>

<h2 style="text-align:center; padding-top:30px">Checkout</h2>

<div class="container">

<!-- CART -->

<div class="cart-section">

<h3>Your Cart</h3>

<table>

<tr>
<th>Course</th>
<th>Price</th>
<th>Qty</th>
<th>Subtotal</th>
</tr>

<?php

$total=0;

if(!empty($_SESSION['cart'])){

foreach($_SESSION['cart'] as $course_id){

$qty=$_SESSION['quantities'][$course_id];

$stmt=$conn->prepare("SELECT s_name,price FROM courses WHERE id=?");
$stmt->bind_param("i",$course_id);
$stmt->execute();

$course=$stmt->get_result()->fetch_assoc();

$subtotal=$course['price']*$qty;

$total+=$subtotal;

?>

<tr>

<td><?=htmlspecialchars($course['s_name'])?></td>

<td>₹<?=number_format($course['price'],2)?></td>

<td><?=$qty?></td>

<td>₹<?=number_format($subtotal,2)?></td>

</tr>

<?php }} ?>

</table>

<h3>Total : ₹<?=number_format($total1,2)?></h3>

</div>


<!-- CHECKOUT -->

<div class="checkout-section">

<h3>Payment Details</h3>

<input type="text" id="coupon" placeholder="Enter Coupon Code">

<button type="button" id="apply-coupon" class="coupon-btn">
Apply Coupon
</button>

<button type="button" id="remove-coupon" class="remove-coupon-btn" style="display:none; background-color:#c5043c">
Remove Coupon
</button>

<p id="coupon-message"></p>

<h4>Total : ₹<span id="total-price"><?=$total1?></span></h4>

<h4 id="discount-section" style="display:none;color:green">
Discount (<span id="discount-percent">0</span>%) : -₹<span id="discount-amount">0</span>
</h4>

<h4 id="payable-section" style="display:none;color:red">
Payable : ₹<span id="payable-amount">0</span>
</h4>

<div id="free-note" class="free-note">
🎉 This course is FREE for you
</div>

<form method="POST" action="payment.php" id="checkout-form">

<input type="hidden" id="discount-value" name="discount-value" value="0">
<input type="hidden" id="discount-type" name="discount-type" value="percent">
<input type="hidden" id="coupon-code" name="coupon-code">
<input type="hidden" id="paid_amount" name="paid_amount" value="<?=$total1?>">
<input type="hidden" name="user_id" value="<?=$user_id?>">
<input type="hidden" name="name" value="<?=$name?>">
<input type="hidden" name="email" value="<?=$email?>">
<input type="hidden" name="phone" value="<?=$mobile?>">
<input type="hidden" id="amount" name="amount" value="<?=$total1?>">

<?php
foreach($_SESSION['cart'] as $course_id){
echo '<input type="hidden" name="course_id[]" value="'.$course_id.'">';
}
?>

<button class="btn" id="order-btn">Proceed To Payment</button>

</form>

</div>

</div>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>

// Apply & Remove Coupon
$(document).ready(function(){

    var originalTotal = parseFloat(<?=$total1?>);

    function updateTotal(discount, discountType){
        var discountAmt = 0;
        if(discountType === 'flat'){
            discountAmt = parseFloat(discount);
        } else {
            discountAmt = (originalTotal * discount) / 100;
        }
        var newTotal = originalTotal - discountAmt;
        if(newTotal < 0) newTotal = 0;

        if(discount > 0){
            $("#discount-section").show();
            $("#payable-section").show();
            
            if(discountType === 'flat'){
                $("#discount-section").html("Discount (Flat ₹" + discount + ") : -₹<span id='discount-amount'>" + discountAmt.toFixed(2) + "</span>");
            } else {
                $("#discount-section").html("Discount (<span id='discount-percent'>" + discount + "</span>%) : -₹<span id='discount-amount'>" + discountAmt.toFixed(2) + "</span>");
            }
            
            $("#payable-amount").text(newTotal.toFixed(2));
            $("#paid_amount").val(newTotal.toFixed(2));
            $("#amount").val(newTotal.toFixed(2));

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
            $("#paid_amount").val(originalTotal.toFixed(2));
            $("#amount").val(originalTotal.toFixed(2));
            $("#free-note").hide();
            $("#order-btn").text("Proceed To Payment");
        }
    }

    // Apply Coupon
    $("#apply-coupon").click(function(){

        var code=$("#coupon").val();

        if(code==""){
            $("#coupon-message").html("<span style='color:red'>Enter Coupon</span>");
            return;
        }

        var courseIds=[];
        <?php
        foreach($_SESSION['cart'] as $cid){
            echo "courseIds.push($cid);";
        }
        ?>

        $.ajax({
            url:"validate-coupon.php",
            type:"POST",
            dataType:"json",
            data:{
                coupon:code,
                course_ids:JSON.stringify(courseIds)
            },
            success:function(res){
                if(res.status=="success"){
                    var successText = res.type === 'flat' ? "₹" + res.discount + " off" : res.discount + "% off";
                    $("#coupon-message").html("<span style='color:green'>Coupon Applied: "+successText+"</span>");

                    $("#discount-value").val(res.discount);
                    $("#discount-type").val(res.type);
                    $("#coupon-code").val(res.couponcode);

                    updateTotal(res.discount, res.type);

                    // Disable Apply button and dim it
                    $("#apply-coupon").prop('disabled', true).text("Coupon Applied");
                    $("#coupon").prop('disabled', true);

                    // Show Remove button
                    $("#remove-coupon").show();

                } else {
                    $("#coupon-message").html("<span style='color:red'>"+res.message+"</span>");
                }
            }
        });
    });

    // Remove Coupon
    $("#remove-coupon").click(function(){
        $("#discount-value").val(0);
        $("#discount-type").val('percent');
        $("#coupon-code").val("");
        $("#coupon").val("").prop('disabled', false);
        $("#apply-coupon").prop('disabled', false).text("Apply Coupon");
        $("#remove-coupon").hide();
        $("#coupon-message").html("");
        updateTotal(0, 'percent');
    });

});

</script>

</body>
</html>