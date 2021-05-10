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
  
  $mail->SMTPDebug = 0;                               // Enable verbose debug output
  $mail->isSMTP();                                      // Set mailer to use SMTP
  
  $mail->Host = 'eliteinsure.co.nz';  // Specify main and backup SMTP servers
  $mail->SMTPAuth = true;                               // Enable SMTP authentication
  $mail->Username = 'accounts@eliteinsure.co.nz';                 // SMTP username
  $mail->Password = 'accounts_elite';                           // SMTP password
  $mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
  $mail->Port = 465;          //    local host XAMMP only       TCP port to connect to

  $mail->setFrom('accounts@eliteinsure.co.nz', 'Invoice Statement');
  $mail->addAddress($email, $name);     // Add a recipient

  $mail->addAttachment($link);         // Add attachments
  $mail->isHTML(true);                // Set email format to HTML

  $mail->Subject = "Invoice statement for " . substr($date_created,6,2)."/".substr($date_created,4,2)."/".substr($date_created,0,4);
  $mail->Body    = 'Adviser '.$name.', please see your attached Invoice statement.';
  $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
  $mail->addCC('executive.admin@eliteinsure.co.nz','Eliteinsure');
  if(!$mail->send()) {
      echo 'Message could not be sent.<br>';
      echo 'Mailer Error: ' . $mail->ErrorInfo;
  } else {
   //   echo 'Message has been sent to '.$email;
    header("Location: invoices.php");
  }

ob_end_flush();
?>