<?php
/**
 * This example shows sending a message using PHP's mail() function.
 */

require '../PHPMailerAutoload.php';

//Create a new PHPMailer instance
$mail = new PHPMailer;
//Set who the message is to be sent from
$mail->setFrom('jesse@eliteinsure.co.nz', 'Jesse');
//Set an alternative reply-to address
$mail->addReplyTo('jesse@eliteinsure.co.nz', 'Jesse');
//Set who the message is to be sent to
$mail->addAddress('programmingwhilesleeping@gmail.com', 'Jesse');
$mail->addAddress('jesse@eliteinsure.co.nz', 'Jesse');
//$mail->addAddress('executive.admin@eliteinsure.co.nz', 'Jesse');
//Set the subject line
$subject = "KiwiSaver Update";
$banner_img = "images/nzfunds.jpg";
$message = "Attached pleased find the latest KiwiSaver Update for clients, relating to our scheme as 31 December 2019.
If you have any questions, please don't hesitate to contact Sid or Jonathan to discuss.
<br>
<br>
If you have any questions, please don't hesitate to contact Sid or Jonathan to discuss.";
$mail->Subject = $subject;

//Read an HTML message body from an external file, convert referenced images to embedded,
//convert HTML into a basic plain-text alternative body
//$mail->msgHTML(file_get_contents('contents.html'), dirname(__FILE__));

ob_start();
//include 'contents.php';
include 'templates/eliteinsure_mail_template.php';
$body = ob_get_clean();
//$mail->msgHTML(file_get_contents('contents.html'), dirname(__FILE__));
$mail->msgHTML($body, dirname(__FILE__));

//Replace the plain text body with one created manually
$mail->AltBody = 'This is a plain-text message body';

//Attach an image file
//$mail->addAttachment('images/phpmailer_mini.png');

//send the message, check for errors
if (!$mail->send()) {
    echo "Mailer Error: " . $mail->ErrorInfo;
} else {
    echo "Message sent!";
}
