<?php
ob_start();
date_default_timezone_set('Pacific/Auckland');
require 'PHPMailer/PHPMailerAutoload.php';
$restrict_session_check = true;
require "database.php";
$today = date("md");
$month_before_today = date("md" , strtotime($today . " -30 days"));
$query = "SELECT c.id as client_id, e.email as email, c.name as client_name, s.deals from clients_tbl c LEFT JOIN submission_clients s ON s.client_id = c.id LEFT JOIN issued_clients_tbl i ON i.name = c.id WHERE i.date_issued LIKE '%$month_before_today'";
echo $query;
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
            if (isset($rows["email"])) {
                if ((!empty($rows["email"]))) {
                    echo "<hr>Annual Reviewee: " .  $rows['client_name'];
                    AnnualReviewEmail($rows["email"], $rows["client_name"], $rows["client_id"]);
                }
            }
        }
        $index++;
    }

endwhile;

function AnnualReviewEmail($email, $name, $id)
{
    $current_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://";
    $current_link .= "{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
    $current_link = str_replace("/email_annual_review", "/annual_review", $current_link);

    $mail = new PHPMailer;

    $mail->IsSMTP();                                      // Set mailer to use SMTP
    $mail->Host = 'eliteinsure.co.nz';                 // Specify main and backup server
    $mail->Port = 587;                                    // Set the SMTP port
    $mail->SMTPAuth = true;                               // Enable SMTP authentication
    $mail->Username = 'wilfred@eliteinsure.co.nz';                // SMTP username
    $mail->Password = 'wilfred2000';                  // SMTP password
    $mail->SMTPSecure = 'tls';

    $mail->addAddress($email, $name);
    $mail->setFrom('admin@eliteinsure.co.nz', 'EliteInsure Team');      //Set From EliteInsure
    //$mail->addBCC($adviser["adviser_email"],$adviser["x"]);                   //CC the adviser
    //$mail->addBCC('admin@eliteinsure.co.nz','Lead Admin');       //CC Admin Team
    $mail->addBCC('executive.admin@eliteinsure.co.nz', 'Admin Leif');    //CC Admin Leif

    //$mail->addBCC('lblagrimas@gmail.com', 'Admin Leif Gmail');    //CC Admin Leif
    $mail->addBCC('jesse@eliteinsure.co.nz', 'IT Support');              //CC Me for testing
    //$mail->addBCC('programmingwhilesleeping@gmail.com', 'IT Support');              //CC Me for testing
    $mail->addReplyTo('admin@eliteinsure.co.nz', 'EliteInsure');        //Add Reply to
    $mail->isHTML(true);

    $mail->Subject  = "Annual Insurance Review";

    $encrypted_key = urlencode(base64_encode($id));

    $message = '<html><body>';
    $message .= '

        <h3>Hello Dear Client,</h3>
            <p>Please click this <a href="' . $current_link . '?id=' . $encrypted_key . '">link</a> to take the annual review.</p>
                
            <p>Always remember that our team is just one call or email away and that we will always be happy to serve you.</p><br>
            <p>
                <strong>Contact Number: </strong> 0508 123 467 <br>
                <strong>Email Address: </strong> admin@eliteinsure.co.nz
            </p>
        ';

    $message .= '
        <hr>
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

ob_end_flush();
