<?php
ob_start();
date_default_timezone_set('Pacific/Auckland');
require 'PHPMailer/PHPMailerAutoload.php';

$restrict_session_check = true;
$magazine_id = $_GET["id"];
include("generate_magazine.php");

$magazineController = new MagazineController();
$receipients = $magazineController->GetReceipients();

    /*
        $allowed_receipients = [
            "jesse@eliteinsure.co.nz"
        ];
    $allowed_receipients = [
        "jesse@eliteinsure.co.nz",
        "admin@eliteinsure.co.nz",    "junior.admin@eliteinsure.co.nz",
        "qualitycontrol@eliteinsure.co.nz",
        "jing@eliteinsure.co.nz"
    ];


    echo "<hr>";
    echo "Magazine Location: $magazine_filepath";
    echo "<br>Period: $period";
    echo "<hr>";
    */

foreach($receipients as $receipient){

    /*
        //For test sending
        if(in_array($receipient["email"], $allowed_receipients)){
            SendMagazineEmail($receipient["email"], $receipient["name"], $period,  $magazine_filepath);
        }
    */

    //Enable sending to everyone
    SendMagazineEmail($receipient["email"], $receipient["name"], $period,  $magazine_filepath);
}



function SendMagazineEmail($email, $name, $period, $file)
{
    $mail = new PHPMailer;
    $date_helper = new INDET_DATES_HELPER();

    $mail->IsSMTP();                                      // Set mailer to use SMTP
    $mail->Host = 'eliteinsure.co.nz';                 // Specify main and backup server
    $mail->Port = 587;                                    // Set the SMTP port
    $mail->SMTPAuth = true;                               // Enable SMTP authentication
    $mail->Username = 'wilfred@eliteinsure.co.nz';                // SMTP username
    $mail->Password = 'wilfred2000';                  // SMTP password
    $mail->SMTPSecure = 'tls';
    $mail->DebugOutput = 'echo';
    $mail->Timeout = 60;
    
    $mail->addAddress($email, $name);

    $mail->addAttachment($file);

    //F
    $mail->setFrom('executive.admin@eliteinsure.co.nz', 'Elite Admin Leif');      //Set From EliteInsure    
    //$mail->setFrom('jesse@eliteinsure.co.nz', 'EliteInsure Admin Team');      //Set From EliteInsure    
    $mail->addReplyTo('admin@eliteinsure.co.nz', 'EliteInsure');        //Add Reply to
    $mail->isHTML(true);

    $mail->Subject  = "EliteInsure Magazine ";

    $message = '<html><body>';
    $message .= '
            <p>Dear ' . $name . ',</p>
            <p>Good day.
            <br>Please see attached company magazine for the period ' . $period . '
            <br>Thanks.</p>';

    $message .= '
            <p>Kind Regards,<br>
            <strong>Mr. Leif Leewin Lagrimas</strong><br>
            Executive Administrator</p>
        ';

    $message .= '</body></html>';

    $mail->Body = $message;

    if (!$mail->send()) {
        echo 'Message could not be sent.<br>';
        echo 'Mailer Error: ' . $mail->ErrorInfo;
    } else {
        echo "Sent to " . $name . "/" . $email . "<hr>";
    }
}
