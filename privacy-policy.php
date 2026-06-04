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
         .privacy-container   p{
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
                    font-size:28px;
                    text-align:center;
                }
               .privacy-container  p{
                    max-width:100%;
                    font-size:14px;
                    text-align:justify;
                }
        }
     
    </style>

    <section class="privacy-container">
        <h3>
            Privacy Policy
        </h3>
        <h5>Introduction</h5>
        <p>This Privacy Policy outlines how Second Sight Foundation, along with its affiliates (collectively referred to as “Second Sight Foundation,” “we,” “our,” or “us”), 
        collects, uses, shares, protects, or otherwise processes your personal information through our website [https://secondsightfoundation.com](https://secondsightfoundation.com) (hereinafter referred to as the “Platform”). Certain sections of the Platform may be accessible without registration. We do not provide products or services outside India, and your personal data will primarily be stored and processed within India. By using the Platform or providing your information, you agree to be bound by the terms of this Privacy Policy, the Terms of Use, and other applicable service terms and conditions. You are also subject to Indian laws, including those related to data protection and privacy. If you do not agree with these terms, please refrain from using our Platform.</p>
        <h5>Data Collection</h5>
        <p>We collect personal data when you use our Platform, access services, or interact with us during our business relationship. This information may include, but is not limited to, your name, date of birth, address, phone number, email address, and identity or address proofs provided during registration. In certain cases, sensitive personal data (e.g., bank account, credit/debit card information, or biometric data) may be collected with your consent, in accordance with applicable laws. You may choose not to provide certain information by opting out of specific features or services on the Platform. We may also track your behavior and preferences on the Platform, which will be compiled and analyzed on an aggregate basis.</p>
    <p>We collect transaction-related information on the Platform and third-party partner platforms. If personal data is collected by a third-party partner, their privacy policy will apply. We are not responsible for third-party privacy practices and advise reviewing their policies before providing personal data. In case you receive communication from someone claiming to be from Second Sight Foundation asking for sensitive data (e.g., PIN, net-banking, or mobile banking passwords), please do not share such information. Report any such incidents to law enforcement immediately.</p>
    <h5>Usage of Data</h5>
    <p>Your personal data is used to provide the services you request, manage transactions, and improve your overall experience on the Platform. We may also use your data for marketing purposes, customer service, dispute resolution, fraud prevention, troubleshooting, and research. You will be given the option to opt-out of marketing communications. In case you do not provide consent for certain data usage, your access to specific services may be limited.</p>
    <h5>Data Sharing</h5>
    <p>We may share your personal data with group entities, affiliates, sellers, business partners, logistics providers, payment processors, and third-party service providers to fulfill services and comply with legal obligations. Additionally, personal and sensitive data may be disclosed to government or law enforcement agencies when required by law or necessary to respond to legal processes or protect the safety and rights of users and the public. Sharing may also occur for marketing or advertising purposes, unless you opt-out.</p>
    <h5>Security Measures</h5>
    <p>We adopt reasonable security practices to safeguard your data from unauthorized access, misuse, or disclosure. While we use secure servers and security protocols to protect your data, transmission over the internet is not entirely secure, and we cannot guarantee full protection. You are responsible for safeguarding your account credentials.</p>
    <h5>Data Retention and Deletion</h5>
    <p>You can delete your account by navigating to your profile settings on the Platform. Deleting your account will result in the loss of all associated information. In cases where there are unresolved claims or pending services, deletion may be delayed. Once the account is deleted, access to the account is permanently lost. We retain personal data only as long as it is necessary for the purposes it was collected or as required by law. Anonymized data may be retained for research purposes.</p>
    <h5>Your Rights</h5>
    <p>You have the right to access, rectify, and update your personal data through the Platform’s functionalities.</p>
    <h5>Consent</h5>
    <p>By using the Platform and providing your information, you consent to the collection, use, storage, sharing, and processing of your personal data in accordance with this Privacy Policy. If you provide personal data related to others, you confirm that you have the authority to share this information. You also consent to being contacted via SMS, email, or call by our partners for purposes specified in this policy. To withdraw your consent, please contact our Grievance Officer with the subject line “Withdrawal of consent for processing personal data.” Withdrawal will not be retrospective and is subject to applicable laws.</p>
    <h5>Policy Changes</h5>
    <p>Please review our Privacy Policy periodically, as it may be updated to reflect changes in our practices. We will notify you of significant changes as required by law.</p>
    <h5>Grievance Officer</h5>
    <p>Name: Sunil Gupta<br>
Designation: Grievance Officer</p>
    <h5>Contact Us</h5>
    <p style="text-align:left;">Phone: <a href="tel:+91-9716517463"> +91-9716517463</a> <br>
Time: Monday – Friday (9:00 AM – 6:00 PM)</p>
    
    </section>
    <?php

    include('include/footer.php');
    include('include/footer-script.php');
    ?>
</body>


</html>