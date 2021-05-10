<?php
date_default_timezone_set('Pacific/Auckland');
ob_start();
session_start();
require("fpdf/mc_table.php");

require("database.php");
require("libs/indet_alphanumeric_helper.php");
require("libs/api/controllers/Adviser.controller.php");
require("libs/api/controllers/Client.controller.php");
require("libs/api/controllers/User.controller.php");

/*
session_start();
*/
//post

class PDF extends PDF_MC_Table
{
    function Header()
    {
        $this->Image('logo_vertical.png', 93, 5, 30);
        $this->SetFillColor(0, 0, 0);
        $this->Rect($this->GetX(), 25, 195, .5, "FD");
        $this->SetFont('Helvetica', 'B', 18);
        $this->SetTextColor(0, 0, 0);
        $this->Cell(0, 20, '', "0", "1", "C");
        $this->SetTextColor(0, 0, 0);
        $this->SetFont('Helvetica', 'B', 10);
        $this->SetFillColor(224, 224, 224);
    }

    function getPage()
    {
        return $this->PageNo();
    }

    function Footer()
    {
        $this->SetTextColor(100, 100, 100);
        $this->SetFont('Helvetica', '', 11);
        $this->SetY(-30);
        $this->Cell(200, 5, "ELITEINSURE LIMITED", "B", "1", "C", 'true');
        $this->Cell(200, 5, "Street Address: 3G/39 Mackelvie Street Grey Lynn 1021 Auckland New Zealand | Contact: 0508 123 467", "0", "1", "C", 'true');
        $this->Cell(200, 5, "Email: admin@eliteinsure.co.nz | Website: www.eliteinsure.co.nz", "0", "1", "C", 'true');
    }
}



//ifs

//FORMULAS end

//convert to 2 decimal number
function convertNum($x)
{
    return number_format($x, 2, '.', ',');
}

function convertNegNum($x)
{
    $x = $x * -1;
    return number_format($x, 2, '.', ',');
}

function removeparent($x)
{
    return preg_replace("/\([^)]+\)/", "", $x); // 'ABC ';
}

$alpha_helper = new INDET_ALPHANUMERIC_HELPER();
$adviserController = new AdviserController();
$clientController = new ClientController();
$userController = new UserController();
$current_user = $_SESSION['myuserid'];

$user = $userController->getUserWithData($current_user);

//retrieving
extract($_POST);
$date = date('Ymd');
$body = translateForDatabase($body);
$body = translateForPDF($body);

$receipients = "";
$receipients_emails = "";
$receipients_names = "";
$row_count = 0;
switch ($receipients_type) {
    case "All Issued Clients":
        $receipients = "EliteInsure Clients";
        $clients = $clientController->getAllIssuedClientsWithIssuedDeals();
        $receipients = fetchReceipientsData($clients);
        $receipients_emails = implode(", ", $receipients->emails);
        $receipients_names = implode(", ", $receipients->names);
        break;
    case "All Submission Clients":
        $receipients = "EliteInsure Clients";
        $clients = $clientController->getAllSubmissionClients();
        $receipients = fetchReceipientsData($clients);
        $receipients_emails = implode(", ", $receipients->emails);
        $receipients_names = implode(", ", $receipients->names);
        break;
    case "All Cancelled Clients":
        $receipients = "EliteInsure Clients";
        $clients = $clientController->getAllCancelledClients();
        $receipients = fetchReceipientsData($clients);
        $receipients_emails = implode(", ", $receipients->emails);
        $receipients_names = implode(", ", $receipients->names);
        break;
    case "Adviser Issued Clients":
        $adviser = $adviserController->getAdviser($adviser_id);


        $clients = $clientController->getAllIssuedClientsWithIssuedDealsAssignedTo($adviser_id);
        $receipients = fetchReceipientsData($clients);
        $receipients_emails = implode(", ", $receipients->emails);
        $receipients_names = implode(", ", $receipients->names);
        break;
    case "Adviser Submission Clients":
        $adviser = $adviserController->getAdviser($adviser_id);


        $clients = $clientController->getAllSubmissionClientsAssignedTo($adviser_id);
        $receipients = fetchReceipientsData($clients);
        $receipients_emails = implode(", ", $receipients->emails);
        $receipients_names = implode(", ", $receipients->names);
        break;
    case "Adviser Cancelled Clients":
        $adviser = $adviserController->getAdviser($adviser_id);

        $clients = $clientController->getAllCancelledClientsAssignedTo($adviser_id);
        $receipients = fetchReceipientsData($clients);
        $receipients_emails = implode(", ", $receipients->emails);
        $receipients_names = implode(", ", $receipients->names);
        break;
    case "Active Advisers":
        $advisers = $adviserController->getActiveAdvisers($adviser_id);
        $receipients = fetchReceipientsData($advisers);
        $receipients_emails = implode(", ", $receipients->emails);
        $receipients_names = implode(", ", $receipients->names);
        break;
    case "Specify Adviser":
        $adviser = $adviserController->getAdviser($adviser_id);
        $receipients = new stdClass();
        $receipients->emails = array();
        $receipients->names = array();
        $receipients->emails[] = $adviser["email"];
        $receipients->names[] = $adviser["name"];
        $receipients_emails = implode(", ", $receipients->emails);
        $receipients_names = implode(", ", $receipients->names);
        break;
    case "Specify":
        $receipients = "Selected Few";
        $receipients = new stdClass();
        $receipients->emails = array();
        $receipients->names = array();
        $emails_pool = explode(",", $emails);

        foreach($emails_pool as $email){
            $possible_client = $clientController->getClientByEmail($email);
            $possible_adviser = $adviserController->getAdviserByEmail($email);

            if($possible_client->num_rows > 0){
                $possible_client = $possible_client->fetch_assoc();
                $receipients->names[] = $possible_client["name"];
            }
            elseif($possible_adviser->num_rows > 0){
                $possible_adviser = $possible_adviser->fetch_assoc();
                $receipients->names[] = $possible_adviser["name"];
            }
            else{
                $receipients->names[] = $name;
            }
        }
        
        $receipients_emails = $emails;
        $receipients_names = implode(", ", $receipients->names);
        break;
}

if ($name == "") {
    $substitute_name = explode(",", $receipients_names)[0];
    $name = (!empty($substitute_name)) ? $substitute_name : "Receipient";
}


$path = bulkBatch(explode(",", $receipients_emails)[0], $name, $date, $subject, $body, $user["full_name"]);
//explode(",", $receipients_emails)[0]
ob_end_clean();
//OUTPUT 
$file = array();
$file['link'] = $path;
$file['subject'] = $subject;
$file['body'] = translateForDatabase($body);
$receipients_data = new stdClass();
$receipients_data->emails = $receipients_emails;
$receipients_data->type = $receipients_type;

switch ($receipients_type) {
    case "Adviser Issued Clients":
    case "Adviser Submission Clients":
    case "Adviser Cancelled Clients":
    case "Specify Adviser":
        if (strpos($receipients_data->type, 'Adviser') !== false) {
            $receipients_data->adviser = $adviser_id;
        }
        break;
}

$file['receipients'] = json_encode($receipients_data);

//$file['amount'] = $total_payable;
//$file['payable_leads'] = $payable_leads;
//$file['payable_issued_leads'] = $payable_issued_leads;

echo json_encode($file);
//db add end
//}


function DateTimeToNZEntry($date_submitted)
{
    return substr($date_submitted, 6, 4) . substr($date_submitted, 3, 2) . substr($date_submitted, 0, 2);
}

function NZEntryToDateTime($NZEntry)
{
    return substr($NZEntry, 6, 2) . "/" . substr($NZEntry, 4, 2) . "/" . substr($NZEntry, 0, 4);
}

function sortFunction($a, $b)
{
    return strtotime($a["date"]) - strtotime($b["date"]);
}

function translateForPDF($input)
{
    $input = str_replace("{indent}", "          ", $input);
    $input = str_replace("<br>", "\r\n", $input);
    return $input;
}

function translateForDatabase($input)
{
    $input = str_replace("\r\n", "<br>", $input);
    $input = str_replace("          ", "{indent}", $input);
    return $input;
}


function bulkBatch($email, $name, $date, $subject, $body, $sender)
{

    $pdf = new PDF('P', 'mm', 'Legal');

    $header_font_size = 13;
    $greeting_font_size = 13;
    $body_font_size = 12;
    $disclaimer_font_size = 10;
    $subject_font_size = 13;
    $regards_font_size = 13;
    //page 1
    $pdf->AddPage();

    $pdf->SetFillColor(255, 255, 255);
    $pdf->SetFont('Helvetica', '', $header_font_size);
    $pdf->Cell(200, 10, date("dS M Y", strtotime($date)), "0", "1", "L", 'true');
    $pdf->Ln(5);

    $pdf->SetFont('Helvetica', 'B', $header_font_size);
    $pdf->Cell(200, 5, "$name", "0", "1", "L", 'true');
    $pdf->Ln(5);

    $pdf->SetFont('Helvetica', '', $header_font_size);
    $pdf->Cell(200, 5, "Email: $email", "0", "1", "L", 'true');
    $pdf->Ln(5);

    $pdf->SetFont('Helvetica', '', $disclaimer_font_size);
    $pdf->SetTextColor(100, 100, 100);

    $pdf->MultiCell(195, 5, "This communication contains information which is confidential and may also be subject to legal privileged. It is for the exclusive use of the intended recipient(s). If you are not the intended recipient(s) please note that any distribution, copying or use of this communication or the information in it is strictly prohibited. If you have received this communication in error please notify us by email (admin@eliteinsure.co.nz) and then delete the email from your system together with any copies of it. All communication sent to and from the firm are subject to monitoring of content. By using this method of communication you give consent to the monitoring of such communications. Any views or opinions are solely those of the author and do not necessarily represent those of the firm unless specifically stated", 0, 'L');
    $pdf->SetFillColor(0, 0, 0);
    $pdf->Rect($pdf->GetX(), $pdf->GetY(), 195, .5, "FD");
    $pdf->SetFillColor(255, 255, 255);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Ln(3);

    $pdf->Ln(5);

    //$pdf->MultiCell(200,5,"Emails: $receipients_emails",0,'L');

    $pdf->SetFont('Helvetica', '', $greeting_font_size);
    $pdf->Cell(200, 5, "Dear $name,", "0", "1", "L", 'true');

    $pdf->Ln(5);
    $pdf->SetFont('Helvetica', 'BU', $subject_font_size);
    $pdf->Cell(200, 5, "$subject", "0", "1", "L", 'true');

    $pdf->Ln(5);
    $pdf->SetFont('Helvetica', '', $body_font_size);
    $pdf->MultiCell(195, 5, "$body", 0, 'L');

    $pdf->SetFont('Helvetica', '', $regards_font_size);
    $pdf->Ln(10);
    $pdf->Cell(200, 5, "Sincerely,", "0", "1", "L", 'true');

    $pdf->Ln(10);
    $pdf->Cell(200, 5, "$sender,", "0", "1", "L", 'true');
    $pdf->Cell(200, 5, "EliteInsure Team", "0", "1", "L", 'true');


    $preview = "bulk_email_" . md5(uniqid());
    $path = "files/$preview" . "_preview.pdf";
    $pdf->Output($path, 'F');
    return $path;
}

function fetchReceipientsData($receipients_pool)
{
    $data = new stdClass();
    $data->emails = array();
    $data->names = array();

    foreach ($receipients_pool as $receipient) {
        if (isset($receipient["email"]))
            if (!empty($receipient["email"])) {
                $data->emails[] = $receipient["email"];

                if (!empty($receipient["name"]))
                    $data->names[] = $receipient["name"];
            }
    }

    return $data;
}

function fetchFromPool($key, $pool_to_search, $required_key = "")
{
    if ($required_key  == "") {
        $required = $key;
    }
    $pool = array();
    foreach ($pool_to_search as $drop) {
        if (isset($drop[$required]))
            if (!empty($drop[$required]))
                if (!empty($drop[$key]))
                    $pool[] = $drop[$key];
    }

    return $pool;
}
