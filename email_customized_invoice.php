<?php
ob_start();
require 'PHPMailer/PHPMailerAutoload.php';
require "database.php";


include "create_customized_invoice_pdf_for_email.php";
$invoice_number = $customized_invoice_data->invoice_number;
$link = $path;

$myid = $_POST["id"];
$emails = $_POST["emails"];
$emails = explode(",", $emails);

$sent_status_sql = "UPDATE customized_invoices SET sent_status = 1 WHERE id = $myid"; 
mysqli_query($con,$sent_status_sql);

foreach ($emails as $email) {
    EmailInvoice($email, $invoice_number, $link);
}

function EmailInvoice($email, $invoice_number, $attachment_link)
{
    $mail = new PHPMailer(true);

    $config = parse_ini_file('libs/api/classes/configurations/config.ini');

    $production = true;

    if($production) {
      $mail->isSMTP();
      $mail->Host = $config['smtp_host'];
      $mail->SMTPAuth = $config['smtp_auth'];
      $mail->Username = $config['smtp_username'];
      $mail->Password = $config['smtp_password'];
      $mail->SMTPSecure = $config['smtp_secure'];
      $mail->Port = $config['smtp_port'];
    } else {
      $mail->isSMTP();
      $mail->Host = $config['smtp_test_host'];
      $mail->SMTPAuth = $config['smtp_test_auth'];
      $mail->Port = $config['smtp_test_port'];
      $mail->Username = $config['smtp_test_username'];
      $mail->Password = $config['smtp_test_password'];
    }
    
    // $mail->setFrom('freestyler.khay@gmail.com', 'Admin Team');
    // $mail->addAddress($email);
    // //$mail->addAddress("admin@eliteinsure.co.nz", "Admin Team");
    // //$mail->addAddress("programmingwhilesleeping@gmail.com", "Test Team");
    // // $mail->addBCC('jesse@eliteinsure.co.nz', 'Jesse');
    // // $mail->addBCC('executive.admin@eliteinsure.co.nz','Eliteinsure');
    // // $mail->addBCC('admin@eliteinsure.co.nz','Admin Team');
    // //programmingwhilesleeping@gmail.com,jesse@eliteinsure.co.nz

    // $mail->addReplyTo('freestyler.khay@gmail.com', 'Admin Team');
    // $mail->Subject = "Invoice - $invoice_number";
    // $mail->Body    = 'Please see attached Invoice.';

    $mail->setFrom('admin@eliteinsure.co.nz', 'Admin Team');
    $mail->addAddress($email);
    $mail->addBCC('executive.admin@eliteinsure.co.nz','Eliteinsure');
    $mail->addBCC('admin@eliteinsure.co.nz','Admin Team');

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
