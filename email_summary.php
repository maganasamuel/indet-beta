<?php
  ob_start();
  require 'PHPMailer/PHPMailerAutoload.php';
  require "database.php";

  $mail = new PHPMailer;

  include "create_summary_for_email.php";

  $myid=$_GET["id"];

  $query = "SELECT * FROM invoices i INNER JOIN adviser_tbl a ON i.adviser_id = a.id where i.id = $myid";
  $displayquery=mysqli_query($con,$query) or die('Could not look up user information; ' . mysqli_error($con));
  $invoicedata = mysqli_fetch_assoc($displayquery);
  extract($invoicedata);

  $startingdate=isset($_GET["startingdate"])?$_GET["startingdate"]:'';

  $link="files/summary.pdf";
  /*
  $rows = mysqli_fetch_array($displayquery);
  $name=$rows["name"];
  $email=$rows["email"];
  $type=$rows["type"];
  $filename=$rows["filename"];
  */
  //$mail->SMTPDebug = 3;                               // Enable verbose debug output

  $mail->isSMTP();                                      // Set mailer to use SMTP
  $mail->Host = 'mail.au.syrahost.com';  // Specify main and backup SMTP servers
  $mail->SMTPAuth = true;                               // Enable SMTP authentication
  $mail->Username = 'accounts@eliteinsure.co.nz';                 // SMTP username
  $mail->Password = 'accounts_elite';                           // SMTP password
  //$mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
  //$mail->Port = 587;          //  	local host XAMMP only    		TCP port to connect to
  $mail->Port = 25;                  

  $mail->setFrom('accounts@eliteinsure.co.nz', 'Invoice Summary');
  $mail->addAddress($email, $name);     // Add a recipient
  //$mail->addAddress('ellen@example.com');               // Name is optional
  //$mail->addReplyTo('kevinjanbarluado2@gmail.com', 'Information');
  //$mail->addCC('cc@example.com');
  //$mail->addBCC('bcc@example.com');

  $mail->addAttachment($link);         // Add attachments
  //$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name
  $mail->isHTML(true);                                  // Set email format to HTML

  $mail->Subject = "Invoice summary for " . substr($date_created,6,2)."/".substr($date_created,4,2)."/".substr($date_created,0,4);
  $mail->Body    = 'Adviser '.$name.', please see your attached Invoice summary.';
  $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
  $mail->addCC('executive.admin@eliteinsure.co.nz','Eliteinsure');
  if(!$mail->send()) {
      echo 'Message could not be sent.<br>';
      echo 'Mailer Error: ' . $mail->ErrorInfo;
  } else {
   //   echo 'Message has been sent to '.$email;
    header("Location: summaries.php");
  }

ob_end_flush();
?>