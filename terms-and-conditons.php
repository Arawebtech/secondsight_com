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
            Terms and conditons
        </h3>
        <h5>Terms and conditons</h5>
       
        <ol style="list-style-type:none">
         <li><b>1. Electronic Record </b><br>
        This document is an electronic record under the Information Technology Act, 2000, and its applicable rules. It is generated electronically and does not require physical or digital signatures.</li>
       <br> <li><b>2. Publication and Compliance</b><br>
This document is published in accordance with Rule 3 (1) of the Information Technology (Intermediaries Guidelines) Rules, 2011, which requires publishing the rules, privacy policy, and terms of use for accessing the domain https://secondsightfoundation.com, including its mobile site and app (collectively referred to as the “Platform”).</li>

<br><li><b>3. Ownership</b><br>
The Platform is owned by Second Sight Foundation, a company incorporated under the Companies Act, 1956, with its registered office at Near Tagore Garden Metro Station Gate Number 1 Exit, New Delhi, Delhi 110027 (“Platform Owner”, “we”, “us”, “our”).</li>
<br><li><b>4. Terms of Use</b><br>
Your use of the Platform, its services, and tools is governed by these terms and conditions (“Terms of Use”), along with applicable policies referenced herein. If you transact on the Platform, you are subject to the policies applicable for such transactions. By using the Platform, you enter into a binding contract with the Platform Owner, and these Terms of Use apply. Any conflicting terms proposed by you are rejected. These Terms may be modified at any time, and it is your responsibility to review them periodically.</li>

<br><li><b>5. User Definition</b><br>
For these Terms, “you”, “your”, or “user” refers to any person who agrees to become a user/buyer on the Platform.</li>
<br><li><b>6. Acceptance of Terms</b><br>
By accessing, browsing, or using the Platform, you agree to all the terms and conditions outlined here. Please read them carefully.</li>

<br><li><b>7. Use of Services</b><br>
a. To access and use our services, you agree to provide accurate and complete information during registration and remain responsible for actions under your registered account.
b. We and third parties do not guarantee the accuracy, timeliness, or suitability of the information on this Platform. We exclude liability for any inaccuracies to the fullest extent permitted by law.
c. Use of the Platform and Services is at your own risk. You are responsible for ensuring the Services meet your requirements.</li>

<li><b>8. Proprietary Rights</b><br>
The Platform content, including design, layout, and graphics, is proprietary and licensed to us. You do not have any rights over the intellectual property contained herein.</li>
<br><li><b>9. Unauthorized Use</b><br>
Unauthorized use of the Platform or Services may result in legal action under these Terms and applicable laws.</li>

<br><li><b>10. Service Charges</b><br>
You agree to pay the charges associated with availing our Services.</li>
<br><li><b>11. Lawful Use</b><br>
You agree not to use the Platform or Services for any illegal or prohibited activities under Indian or local laws.</li>
<br><li><b>12. Third-Party Links</b><br>
The Platform may contain links to third-party websites. By accessing those links, you will be governed by the terms and policies of those third-party websites.

</li>
<br><li><b>13. Legal Binding Agreement</b><br>
By initiating a transaction on the Platform, you agree to enter a legally binding contract with the Platform Owner for the Services.</li>

<br><li><b>14. Indemnity</b><br>
You agree to indemnify and hold harmless the Platform Owner, its affiliates, officers, directors, and employees from any claims or actions arising from your breach of these Terms, violation of law, or infringement of third-party rights.</li>
<br><li><b>15. Force Majeure</b><br>
Neither party will be held liable for failure to perform obligations under these Terms if performance is prevented or delayed due to a force majeure event.</li>
<br><li><b>16. Governing Law</b><br>
These Terms and related disputes will be governed by the laws of India.</li>
<br><li><b>17. Jurisdiction</b><br>
Any disputes will be subject to the exclusive jurisdiction of courts in Meerut, Uttar Pradesh.</li>
<br><li><b>18. Communication</b><br>
Any concerns regarding these Terms must be addressed through the contact information provided on the Platform.</li>
<br><li><b>19.Contact Us:</b><br>
Got questions? Reach out using the contact details on our website. We’re here to help!</li>


        </ol>


    
    </section>
    <?php

    include('include/footer.php');
    include('include/footer-script.php');
    ?>
</body>


</html>