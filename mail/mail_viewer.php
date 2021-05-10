<?php
$subject = "KiwiSaver Update";
$banner_img = "images/nzfunds.jpg";
$message = "
<h1>Greetings</h1>
<p>Attached pleased find the latest KiwiSaver Update for clients, relating to our scheme as 31 December 2019.
If you have any questions, please don’t hesitate to contact Sid or Jonathan to discuss.
<br>
<br>
If you have any questions, please don’t hesitate to contact Sid or Jonathan to discuss.</p>";

$subject = (isset($_POST["subject"])) ? $_POST["subject"] : $subject;
$banner_img = (isset($_POST["banner_img"])) ? $_POST["banner_img"] : $banner_img;
$message = (isset($_POST["message"])) ? $_POST["message"] : $message;

include 'templates/eliteinsure_mail_template2.php';