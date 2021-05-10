<?php
ob_start();
date_default_timezone_set('Pacific/Auckland');
require 'PHPMailer/PHPMailerAutoload.php';

$restrict_session_check = true;
include("client_data_pdf.php");

$clientController = new ClientController();
$generalController = new General();


$send_client_data_id = $_GET["id"];
$send_client_data_request = $clientController->getSendClientDataRequest($send_client_data_id)->fetch_assoc();
$client_id = json_decode($send_client_data_request["client_ids"], true);

$reference_no = $send_client_data_request["reference_number"];

$receipient_name = $send_client_data_request["name"];
$receipient_email = $send_client_data_request["email"];

//Fetch user
$userController = new UserController();
$user = $userController->getUserWithData($send_client_data_request["user_id"]);
$sender_name = $user["full_name"];

$date = date("d/m/Y", strtotime($send_client_data_request["date_sent"]));

$files = [];
$clients = [];

if (is_array($client_id)) {
    foreach ($client_id as $id) {
        $client = $clientController->getClient($id)->fetch_assoc();
var_dump($client);
        $files[] = ($client["lead_by"] == "Telemarketer") ?  CreateLeadDataPDF($id, $reference_no, false) : CreateClientDataPDF($id, $reference_no, false);
        
        $clients[] = $client;
    }
} else {
    $client = $clientController->getClient($client_id)->fetch_assoc();
    $files[] = ($client["lead_by"] == "Telemarketer") ?  CreateLeadDataPDF($client_id, $reference_no, false) : CreateClientDataPDF($client_id, $reference_no, false);
    $clients[] = $client;
}
SendClientDataEmail($receipient_email, $receipient_name, $date, $sender_name, $files, $clients, $reference_no);



function SendClientDataEmail($email, $name, $date, $sender_name, $files, $clients, $reference_no)
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

    $mail->addAddress($email, $name);

    foreach ($files as $file) {
        $mail->addAttachment($file);
    }

    //F
    $mail->setFrom('admin@eliteinsure.co.nz', 'EliteInsure Admin Team');      //Set From EliteInsure    
    $mail->addBCC('executive.admin@eliteinsure.co.nz', 'Admin Leif');    //CC Admin Leif
    $mail->addBCC('admin@eliteinsure.co.nz', 'Admin Team');    //CC Admin Leif
    
    //For Testing
    $mail->addBCC('jesse@eliteinsure.co.nz', 'IT Support');              //CC Me for testing
    
    $mail->addReplyTo('admin@eliteinsure.co.nz', 'EliteInsure');        //Add Reply to
    $mail->isHTML(true);

    $mail->Subject  = "Leads for $name | $date | Reference Number $reference_no";

    $message = '<html><body>';
    $message .= '
            <p>Hi ' . $name . ',</p>
            <p>Please see attached lead(s) assigned to you today, ' . $date . '
            <br>Pipedrive access will be given to you shortly where you can see the original lead sheet.</p>';

    foreach ($clients as $index => $client) {
        $message .= '
        <p>Client #' . ($index + 1) . '<br>
        Name: ' . $client["name"] . '<br>
        Address: ' . $client["address"] . '<br>
        Contact #: ' . $client["appt_time"] . '<br>
        Email: ' . $client["email"] . '<br>
        Date: ' . date("d/m/Y", strtotime($client["appt_date"])) . '<br>
        Time: ' . $date_helper->MilitaryTimeToCommonTime($client["time"]) . '<br>
        BDM: ' . $client["leadgen_name"] . '</p><hr>';
    }

    $message .= '
            <p>Kind Regards,<br>
            ' . $sender_name . '<br>
            -EliteInsure Admin  Team</p>
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
