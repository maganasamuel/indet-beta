<?php
date_default_timezone_set('Pacific/Auckland');
require 'PHPMailer/PHPMailerAutoload.php';
$restrict_session_check = true;
require "database.php";
$today = date("md");

$query = "SELECT * FROM users u LEFT JOIN personal_data p ON u.linked_id = p.id WHERE RIGHT (birthday, 4) = '$today' AND email != ''";
//echo $query;
$result = mysqli_query($con, $query);

while ($rows = mysqli_fetch_array($result)) :
    extract($rows);
    if (isset($email)) {
        if ((!empty($email))) {
            $bday = substr($birthday, 4);
            if ($today == $bday) {
                echo "<hr>User/Admin: " .  $full_name;


                var_dump($birthday);
                echo "<hr>";
                BirthdayEmail($email, $full_name);
            }
        }
    }

endwhile;

$query = "SELECT * FROM adviser_tbl WHERE RIGHT (birthday, 4) = '$today' AND email != ''";
//echo $query;
$result = mysqli_query($con, $query);

while ($rows = mysqli_fetch_array($result)) :
    extract($rows);
    if (isset($birthday) && isset($email)) {
        if ((!empty($birthday) && !empty($email))) {
            $bday = substr($birthday, 4);
            if ($today == $bday) {
                echo "<hr>Adviser: " .  $name;


                var_dump($birthday);
                echo "<hr>";
                BirthdayEmail($email, $name);
            }
        }
    }

endwhile;

$query = "SELECT * FROM leadgen_tbl WHERE RIGHT (birthday, 4) = '$today' AND email != ''";
//echo $query;
$result = mysqli_query($con, $query);

while ($rows = mysqli_fetch_array($result)) :
    extract($rows);
    if (isset($birthday) && isset($email)) {
        if ((!empty($birthday) && !empty($email))) {
            $bday = substr($birthday, 4);
            if ($today == $bday) {
                echo "<hr>Lead Generator: " .  $name;


                var_dump($birthday);
                echo "<hr>";
                BirthdayEmail($email, $name);
            }
        }
    }

endwhile;

function BirthdayEmail($email, $name)
{
    $current_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://";
    $current_link .= "{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
    $current_link = str_replace("/email_birthday", "/unsubscribe", $current_link);

    $mail = new PHPMailer;

    $mail->IsSMTP();                                      // Set mailer to use SMTP
    $mail->Host = 'eliteinsure.co.nz';                 // Specify main and backup server
    $mail->Port = 587;                                    // Set the SMTP port
    $mail->SMTPAuth = true;                               // Enable SMTP authentication
    $mail->Username = 'wilfred@eliteinsure.co.nz';                // SMTP username
    $mail->Password = 'wilfred2000';                  // SMTP password
    $mail->SMTPSecure = 'tls';

    $mail->addAddress($email, $name);
    $mail->addBCC("jesse@eliteinsure.co.nz", "Jesse");
    $mail->addBCC("programmingwhilesleeping@gmail.com", "Jesse");
    $mail->setFrom('executive.admin@eliteinsure.co.nz', 'Admin Leif');      //Admin Leif
    $mail->isHTML(true);

    $mail->Subject  = "Happy Birthday";


    $message = '<html><body>';
    $message .= '
            <img style="width:600px;" src="http://onlineinsure.co.nz/images_stash/bday.jpg"><br>
            <p>May this day be the start of a year filled with good luck, health, and happiness. <br> We wish that you will have many joyous years ahead.</p>
            <p><strong>Happy Birthday!</strong></p>
            <p>-Admin Leif</p>
            <br>
            <br>
        ';

    $message .= '</body></html>';

    $mail->Body = $message;

    if (!$mail->send()) {
        echo 'Message could not be sent.<br>';
        echo 'Mailer Error: ' . $mail->ErrorInfo;
    } else {
        echo "<hr>Message sent to " . $name;
    }
}

