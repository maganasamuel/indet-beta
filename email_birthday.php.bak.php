<?php
ob_start();
date_default_timezone_set('Pacific/Auckland');
require 'PHPMailer/PHPMailerAutoload.php';
$restrict_session_check = true;
require "database.php";
$today = date("md");

$query = "SELECT s.id as sub_id, s.deals, c.id as client_id, c.lead_by, i.id,c.name,a.name as x, a.email as adviser_email, l.name as y,i.appt_date,i.appt_time,i.address,i.leadgen,i.assigned_to,i.assigned_date,i.type_of_lead,i.issued,i.date_issued,i.notes FROM submission_clients s LEFT JOIN issued_clients_tbl i ON s.client_id = i.name LEFT JOIN adviser_tbl a ON i.assigned_to = a.id LEFT JOIN leadgen_tbl l ON i.leadgen = l.id LEFT JOIN clients_tbl c ON i.name = c.id order by i.date_issued desc";
//echo $query;
$result = mysqli_query($con, $query);

while ($rows = mysqli_fetch_array($result)) :

    $deals = json_decode($rows["deals"]);
    $index = 0;
    //Go through deals and check if there is a cancelled deal.
    foreach ($deals as $deal) {
        if ($deal->status = "Issued") {

            //Check if deal is cancelled
            if (isset($deal->clawback_status)) {
                if ($deal->clawback_status == "Cancelled") {
                    continue;
                }
            }

            //First condition check if there is birthday and email
            if (!isset($deal->unsubscribed)) {
                if (isset($deal->birthday) && isset($deal->email)) {
                    if ((!empty($deal->birthday) && !empty($deal->email))) {
                        $bday = substr($deal->birthday, 4);
                        if ($today == $bday) {
                            echo "<hr>Birthday Celebrant: " .  $rows['name'];


                            var_dump($deal->birthday);
                            echo "<hr>";
                            BirthdayEmail($deal->email, $rows["name"], $rows, $deal, true, $con, $index);
                        }
                    }
                }
            }


            if (!isset($deal->unsubscribed2)) {
                if (isset($deal->secondary_birthday) && isset($deal->secondary_email)) {
                    if ((!empty($deal->secondary_birthday) && !empty($deal->secondary_email))) {
                        $bday = substr($deal->secondary_birthday, 4);
                        if ($today == $bday) {
                            echo "<hr>Birthday Celebrant: " .  $rows['name'];
                            var_dump($deal->secondary_birthday);
                            echo "<hr>";
                            BirthdayEmail($deal->secondary_email, $deal->life_insured, $rows, $deal, false, $con, $index);
                        }
                    }
                }
            }
        }
        $index++;
    }

endwhile;

function BirthdayEmail($email, $name, $adviser, $deal, $primary, $con, $index)
{
    $current_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://";
    $current_link .= "{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
    $current_link = str_replace("/email_birthday", "/unsubscribe", $current_link);

    $mail = new PHPMailer;

    $mail->IsSMTP();                                      // Set mailer to use SMTP
    $mail->Host = 'eliteinsure.co.nz';                 // Specify main and backup server
    $mail->Port = 587;                                    // Set the SMTP port
    $mail->SMTPAuth = true;                               // Enable SMTP authentication
    $mail->Username = 'jesse@eliteinsure.co.nz';                // SMTP username
    $mail->Password = 'assistance5000';                  // SMTP password
    $mail->SMTPSecure = 'tls';

    $mail->addAddress($email, $name);
    $mail->setFrom('admin@eliteinsure.co.nz', 'EliteInsure Team');      //Set From EliteInsure
    //$mail->addBCC($adviser["adviser_email"],$adviser["x"]);                   //CC the adviser
    $mail->addBCC('admin@eliteinsure.co.nz','Lead Admin');       //CC Admin Team
    $mail->addBCC('executive.admin@eliteinsure.co.nz', 'Admin Leif');    //CC Admin Leif

    //$mail->addBCC('lblagrimas@gmail.com', 'Admin Leif Gmail');    //CC Admin Leif
    $mail->addBCC('jesse@eliteinsure.co.nz', 'IT Support');              //CC Me for testing
    //$mail->addBCC('programmingwhilesleeping@gmail.com', 'IT Support');              //CC Me for testing
    $mail->addReplyTo('admin@eliteinsure.co.nz', 'EliteInsure');        //Add Reply to
    $mail->isHTML(true);

    $mail->Subject  = "Happy Birthday";

    $submissions_id = $adviser["sub_id"];
    $unsub_key = "";

    if ((!isset($deal->unsub_key))) {
        $unsub_key = md5(uniqid());
        CreateUnsubKey($unsub_key, $submissions_id, $index, $con, $primary);
    } else {
        $unsub_key = $deal->unsub_key;
    }


    $message = '<html><body>';
    $message .= '
            <img style="width:600px;" src="http://onlineinsure.co.nz/images_stash/bday.jpg"><br>
            <p>May this day be just the start of a year filled with good luck, good health, and much happiness. <br> We wish that you will have many joyous years ahead.</p>
            <p><strong>Happy Birthday!</strong></p>
            <p>-EliteInsure Ltd. Team</p>
            <br>
            <br>

            <p>Always remember that our team is just one call or email away and that we will always be happy to serve you.</p><br>
            <p>
                <strong>Contact Number: </strong> 0508 123 467 <br>
                <strong>Email Address: </strong> admin@eliteinsure.co.nz
            </p>
        ';

    $message .= '
        <hr>
        <small>
            Don\'t like receiving our emails? Click this <a href="' . $current_link . '?id=' . $submissions_id . "&key=" . $unsub_key . '&primary=true">link</a> to unsubscribe.
        </small>
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

function CreateUnsubKey($key, $id, $index, $con, $primary)
{
    $query = "SELECT * FROM submission_clients WHERE id = $id";
    $result = mysqli_query($con, $query);
    $row = mysqli_fetch_assoc($result);

    $deals = json_decode($row["deals"]);
    $ctr = 0;

    foreach ($deals as $deal) {
        //This is the deal that needs the key
        if ($ctr == $index) {
            if ($primary) {
                $deal->unsub_key = $key;
            } else {
                $deal->unsub_key2 = $key;
            }
            break;
        }
        $ctr++;
    }

    $deals = json_encode($deals);

    $query = "UPDATE submission_clients SET deals = '$deals' WHERE id = $id";
    $result = mysqli_query($con, $query);
}
ob_end_flush();
