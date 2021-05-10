<?php
ob_start();
require 'PHPMailer/PHPMailerAutoload.php';
require "database.php";


include "create_customized_invoice_pdf_for_email.php";
$invoice_number = $customized_invoice_data->invoice_number;
$link = $path;
$emails = $_POST["emails"];
$emails = explode(",", $emails);

foreach ($emails as $email) {
    EmailInvoice($email, $invoice_number, $link);
}

function EmailInvoice($email, $invoice_number, $attachment_link)
{
    $mail = new PHPMailer;
    $mail->setFrom('admin@eliteinsure.co.nz', 'Admin Team');

    $mail->addAddress($email);
    //$mail->addAddress("admin@eliteinsure.co.nz", "Admin Team");
    //$mail->addAddress("programmingwhilesleeping@gmail.com", "Test Team");
    $mail->addBCC('jesse@eliteinsure.co.nz', 'Jesse');
    $mail->addBCC('executive.admin@eliteinsure.co.nz','Eliteinsure');
    $mail->addBCC('admin@eliteinsure.co.nz','Admin Team');
    //programmingwhilesleeping@gmail.com,jesse@eliteinsure.co.nz

    $mail->addReplyTo('admin@eliteinsure.co.nz', 'Admin Team');
    $mail->Subject = "Invoice - $invoice_number";
    $mail->Body    = 'Please see attached Invoice.';

    $mail->addAttachment($attachment_link);
    if (!$mail->send()) {
        echo 'Message could not be sent.<br>';
        echo 'Mailer Error: ' . $mail->ErrorInfo;
    } else {
        header("Location:annual_review?message=success");
    }
}
ob_end_flush();
