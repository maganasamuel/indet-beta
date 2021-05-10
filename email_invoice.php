<?php
  ob_start();
  require 'PHPMailer/PHPMailerAutoload.php';
  require "database.php";

  $mail = new PHPMailer;

  include "create_invoice_for_email.php";

  $myid=$_GET["id"];

  $query = "SELECT * FROM invoices i INNER JOIN adviser_tbl a ON i.adviser_id = a.id where i.id = $myid";
  $displayquery=mysqli_query($con,$query) or die('Could not look up user information; ' . mysqli_error($con));
  $invoicedata = mysqli_fetch_assoc($displayquery);
  extract($invoicedata);

  $startingdate=isset($_GET["startingdate"])?$_GET["startingdate"]:'';

    $link=$path;

    $mail->setFrom('executive.admin@eliteinsure.co.nz', 'Invoice Statement');
  
    $mail->addAddress($email, $name);
    $mail->addBCC('executive.admin@eliteinsure.co.nz','Eliteinsure');
    
    $mail->addReplyTo('executive.admin@eliteinsure.co.nz', 'Invoice Statement');
    $mail->Subject = "Invoice statement for " . substr($date_created,6,2)."/".substr($date_created,4,2)."/".substr($date_created,0,4);
    $mail->Body    = 'Adviser '.$name.', please see your attached Invoice statement.';  
        
    $mail->addAttachment($link);     
  if(!$mail->send()) {
      echo 'Message could not be sent.<br>';
      echo 'Mailer Error: ' . $mail->ErrorInfo;
  }
    else{
        echo "Message sent";
    }
ob_end_flush();
