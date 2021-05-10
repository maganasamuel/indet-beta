<?php
  ob_start();
  require 'PHPMailer/PHPMailerAutoload.php';
  require "database.php";

  include "create_bulk_email_pdf_for_email.php";

  
  $emails = explode(",", $receipients_emails);

    for($i = 0; $i < count($emails); $i++){
        $name2 = $name;


        if ($name2 == "") {
            $substitute_name = explode(",", $receipients_names)[$i];
            $name2 = (!empty($substitute_name)) ? $substitute_name : "Receipient";
        }
        
        $email = explode(",", $receipients_emails)[$i];
        $path = bulkBatch($email, $name2, $date, $subject, $body, $user["full_name"], $bulk_email_id);
        
        $link=$path;
        SendBulkMail($email, $subject, $link);
        echo "<hr>Email: $email"; 
    }

    function SendBulkMail($email, $subject, $attachment){
        $mail = new PHPMailer;

        $mail->setFrom('admin@eliteinsure.co.nz', 'EliteInsure Team');
      
        $mail->addAddress($email);        
        $mail->addReplyTo('admin@eliteinsure.co.nz', 'EliteInsure Team');
        $mail->Subject = $subject;
        $mail->Body    = 'Please see attached PDF for more information.';  
            
        $mail->addAttachment($attachment);     
      if(!$mail->send()) {
          echo 'Message could not be sent.<br>';
          echo 'Mailer Error: ' . $mail->ErrorInfo;
      }
        else{
            echo "Message sent to $email";
        }
    }
ob_end_flush();
