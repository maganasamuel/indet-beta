<?php
  ob_start();
  require 'PHPMailer/PHPMailerAutoload.php';
  require "database.php";

  $mail = new PHPMailer;

  include "create_annual_review_pdf_for_email.php";

  $myid=$_GET["id"];

  $link=$path;

    $mail->setFrom('admin@eliteinsure.co.nz', 'Admin Team');
  
    $mail->addAddress("admin@eliteinsure.co.nz", "Admin Team");
    //$mail->addAddress("programmingwhilesleeping@gmail.com", "Test Team");
    $mail->addBCC('jesse@eliteinsure.co.nz','Jesse');
    $mail->addBCC('executive.admin@eliteinsure.co.nz','Eliteinsure');
    
    $mail->addReplyTo('accounts@eliteinsure.co.nz', 'Admin Team');
    $mail->Subject = "EliteInsure Annual Review Form - $name";
    $mail->Body    = 'Please see attached Annual Review Form.';  
        
    $mail->addAttachment($link);     
  if(!$mail->send()) {
      echo 'Message could not be sent.<br>';
      echo 'Mailer Error: ' . $mail->ErrorInfo;
  }
    else{
        header("Location:annual_review?message=success");
    }
ob_end_flush();
