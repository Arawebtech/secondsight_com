<?php
session_start();
include('include/cart_logic.php');
include('admin/include/db_config.php');


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
       .privacy-container{
            padding:2% 5%;
            margin-bottom:4rem;
            margin-top:2rem;
        }
            
          .privacy-container  h3{
                font-size:28px;
                text-align:center;
            }
         .privacy-container   p,li{
                max-width:85%;
                font-size:16px;
            }
        @media screen and (max-width:700px)
        {
            .contact-info-area .single-contact-info i {
                font-size: 23px;
            }
            .btn-contact{
                padding:10px;
                font-size:14px;
            }
            
            .privacy-container{
                padding:5% ;
                  margin-bottom:4rem;
                     margin-top:2rem;
            }
            .privacy-container h3{
                    font-size:22px;
                    text-align:center;
                    display:none;
                }
               .privacy-container  p,li{
                    max-width:100%;
                    font-size:14px;
                    text-align:justify;
                }
        }
     
    </style>

    <section class="privacy-container">
        <h3>
            Refund and Cancellation Policy
        </h3>
        <h5>Refund and Cancellation Policy</h5>
        <p>This policy explains the process for canceling or seeking a refund for products or services purchased through our platform:</p>
        <ol style="list-style-type:disc;">
            <li><b>Cancellation Requests:</b> Cancellations can be made within 10 days of placing the order. However, if the product has already been shipped or is out for delivery, cancellation may not be possible. In such cases, you can choose to refuse the product at the time of delivery.</li>
            <li><b>Non-cancellable Items:</b> Cancellations are not accepted for perishable items such as flowers or food. However, if the quality of the perishable item is proven to be substandard, a refund or replacement may be offered.</li>
            <li><b>Damaged or Defective Items:</b> If you receive a damaged or defective product, please report it to our customer service team within 10 days of receipt. The issue will be verified by the seller/merchant, and if confirmed, an appropriate action will be taken.</li>
            <li><b>Product Discrepancies: </b>If the product received does not match its description or your expectations, notify our customer service within 10 days of receiving the product. Our team will investigate and make a decision accordingly.</li>
            <li><b>Products with Manufacturer Warranty:</b> For items that come with a manufacturer’s warranty, please direct any issues to the manufacturer.</li>
            <li><b>Refund Processing:</b> If a refund is approved, it will be processed within 10 days from the approval date.</li>
        </ol>
        <br>
        <h5>Return Policy</h5>
        <p>We accept returns or exchanges within 10 days of purchase. After this period, no returns, exchanges, or refunds will be processed. To be eligible for a return or exchange:</p>
        <p>1. The product must be unused and in the same condition as received.<br>
2. The product must be returned in its original packaging.<br>
3. Items purchased during a sale are not eligible for return or exchange, unless they are defective or damaged.</p>
    
     <ol style="list-style-type:disc;">
         <li><b>Exemptions:</b> Certain items may be exempt from returns or refunds, and such exemptions will be clearly communicated at the time of purchase.</li>
         <li><b>Return/Exchange Process: </b>Once we receive and inspect the returned item, we will notify you via email. If approved, your return or exchange will be processed in accordance with our policy.</li>
      </ol>
      <hr>
      <p>These policies ensure a smooth experience for both our customers and sellers, while maintaining the quality and standards of Second Sight Foundation’s products and services.</p>
      <h4>Need help?</h4>
      <p>Contact us for questions related to refunds and returns.</p>

    
    </section>
    <?php

    include('include/footer.php');
    include('include/footer-script.php');
    ?>
</body>


</html>