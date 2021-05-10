<?php
date_default_timezone_set('Pacific/Auckland');
ob_start();
require("fpdf/mc_table.php");

require("database.php");

require_once "libs/indet_dates_helper.php";
require_once "libs/indet_alphanumeric_helper.php";
require_once "libs/api/classes/general.class.php";
require_once "libs/api/controllers/LeadGenerator.controller.php";
require_once "libs/api/controllers/Client.controller.php";
require_once "libs/api/controllers/Deal.controller.php";
require_once "libs/api/controllers/Product.controller.php";
require_once "libs/api/controllers/User.controller.php";

class PDF extends PDF_MC_Table
{
    var $adviser = "";

    function Footer()
    {
        global $fsp_num;
        global $reference_no;
        global $timestamp;
        global $agent_name;

        $this->SetY(-15);
        $this->SetFillColor(0, 0, 0);
        $this->Rect(5, 342, 206.5, .5, "FD");
        $this->SetFont('Helvetica', '', 10);
        $this->SetTextColor(0, 0, 0);
        $this->Cell(50, 10, 'Reference No: '. $this->reference_no, 0, 0, 'L');
        $this->Cell(100, 10, "", 0, 0, 'C');
        $this->AliasNbPages('{totalPages}');
        $this->Cell(50, 10, 'Page ' . $this->PageNo() . " of " . "{totalPages}", 0, 1, 'R');
    }

    function Header()
    {
        $this->SetFillColor(0, 100, 150);
        $this->Image('logo_vertical.png', 10, 5, 30);
        $this->Rect(5, 25, 206.5, .5, "F");
        $this->Rect(44, 1, 7, 24.1, "F");
        $this->SetFont('Helvetica', 'B', 12);
        $this->SetTextColor(0, 0, 0);
        $this->Image('images/Home.png', 45, 2.5, 5);
        $this->Image('images/Phone.png', 45, 8.75, 5);
        $this->Image('images/Mail.png', 45, 14.75, 5);
        $this->Image('images/WWW.png', 45, 20.25, 5);
        $this->SetY(1.75);

        //Address
        $this->Cell(41, 6, '', "0", "0", "L");
        $this->Cell(55, 6, '3G/39 Mackelvie Street Grey Lynn 1021 Auckland New Zealand', "0", "1", "L");

        //Contact Number
        $this->Cell(41, 6, '', "0", "0", "L");
        $this->Cell(50, 6, '0508 123 467', "0", "1", "L");

        //Email
        $this->Cell(41, 6, '', "0", "0", "L");
        $this->Cell(50, 6, 'admin@eliteinsure.co.nz', "0", "1", "L");

        //Email
        $this->Cell(41, 6, '', "0", "0", "L");
        $this->Cell(50, 6, 'www.eliteinsure.co.nz', "0", "1", "L");

        $this->SetY(28);
        $this->SetTextColor(0, 0, 0);
        $this->SetFont('Helvetica', 'B', 10);
        $this->SetFillColor(224, 224, 224);
    }

    function getPage()
    {
        return $this->PageNo();
    }

    function NLines($w, $txt)
    {
        return $this->NbLines($w, $txt);
    }
}


function CreateIssuedClientProfilePDF($id, $reference_no, $preview = true)
{
    $clientController = new ClientController();
    $userController = new UserController();
    $generalController = new General();
    $date_helper = new INDET_DATES_HELPER();
    $alphanumeric_helper = new INDET_ALPHANUMERIC_HELPER();

    $client_id = $id;

    $clientController = new ClientController();
    $client = (object) $clientController->getClient($client_id)->fetch_assoc();

    $dealController = new DealController();
    $deals = json_decode($dealController->getIssuedClientProfile($client_id)["deals_data"], true);

    //Fetch user
    $userController = new UserController();
    $user = $userController->getUserWithData($_SESSION["myuserid"]);

    $indet_dates_helper = new INDET_DATES_HELPER();

    $created_by = $user["full_name"];

    $pdf_data = new stdClass();

    $client->notes = str_replace("<br>", "\r\n", $client->notes);

    //$client->notes = str_replace("\r\n", "<br>", $client->notes);
    $pdf = new PDF('P', 'mm', 'Legal');
    $pdf->reference_no = $reference_no;

    $x = $pdf->GetX();
    $y = $pdf->GetY();

    //page 1
    $pdf->AddPage();

    //Title
    $pdf->SetFillColor(224, 224, 224);
    $pdf->SetFont('Arial', '', 13);


    $pdf->Rect($pdf->GetX() + 98, $pdf->GetY() + 5, 37, 0.2);

    $column_number = "1";

    $pdf->SetFillColor(209, 226, 243);
    $pdf->SetDrawColor(157, 195, 230);
    $pdf->Rect(8, $pdf->GetY(), 201, 10, "FD");
    $pdf->SetFont('Helvetica', 'B', 15);
    $pdf->Cell(197, 10, numberToRomanRepresentation($column_number) . ". CLIENT INFORMATION", "0", "1", "L", true);

    $pdf->Ln(1);
    $column_number++;
    $pdf->SetFillColor(190, 215, 239);
    $pdf->Rect(10, $pdf->GetY(), 100, 8, "F");
    $pdf->Rect(111, $pdf->GetY(), 96, 8, "F");
    $pdf->SetFont('Helvetica', '', 13);
    $pdf->Cell(101, 8, "Name: " . $client->name, "0", "0", "L", false);
    $pdf->Cell(96, 8, "Email: " . $client->email, "0", "1", "L", false);
    $pdf->Ln(1);

    $pdf->SetFillColor(223, 235, 247);
    $pdf->Rect(10, $pdf->GetY(), 100, 8, "F");
    $pdf->Rect(111, $pdf->GetY(), 96, 8, "F");
    $pdf->Cell(101, 8, "Phone: " . $client->appt_time, "0", "0", "L", false);
    $pdf->Cell(96, 8,  "Date Generated: " . $indet_dates_helper->NZEntryToDateTime($client->date_submitted), "0", "1", "L", false);
    $pdf->Ln(1);




    $pdf->SetFillColor(190, 215, 239);
    $pdf->Rect(10, $pdf->GetY(), 197, 8, "F");
    $pdf->MultiCell(197, 8, "Address: " . $client->address, "0", "1", "L", false);
    $pdf->Ln(1);

    $pdf->SetFillColor(223, 235, 247);
    $pdf->Rect(10, $pdf->GetY(), 100, 8, "F");
    $pdf->Rect(111, $pdf->GetY(), 96, 8, "F");
    $pdf->Cell(101, 8, "Source : " . $client->lead_by, "0", "0", "L", false);
    $pdf->Cell(96, 8, "Lead Gen Name: " . $client->leadgen_name, "0", "1", "L", false);
    $pdf->Ln(1);

    $pdf->SetFillColor(209, 226, 243);
    $pdf->SetDrawColor(157, 195, 230);
    $pdf->Rect(8, $pdf->GetY(), 201, 10, "FD");
    $pdf->SetFont('Helvetica', 'B', 15);
    $pdf->Cell(17, 10, numberToRomanRepresentation($column_number) . ". DEALS DATA", "0", "1", "L", true);
    $pdf->Ln(1);
    $column_number++;

    //Deals
    if (isset($deals)) {
        if (is_array($deals)) {
            if (count($deals) > 0) {

                $pdf->SetFillColor(190, 215, 239);
                $pdf->SetFont('Helvetica', 'B', 11);
                $pdf->SetAligns(array("C", "C", "C", "C", "C", "C", "C"));
                $pdf->SetWidths(array(30, 30, 30, 22, 30, 30, 25));
                $pdf->Row(array("Insurer", "Policy Number", "Life Insured", "Issue Date", "Submission Date", "Issued API", "Current Status"), true, array(190, 215, 239));
                $pdf->Rect(10, $pdf->GetY(), 197, 1, "FD");
                $pdf->Ln(1);

                $border_color = array();

                $pdf->SetFont('Helvetica', '', 10);
                foreach ($deals as $i => $deal) {
                    $deal = (object) $deal;
                    $left_content = "";
                    if ($i == 0) {
                        $left_content = "Interested In:";
                    }
                    if ($i % 2 == 0) {
                        $pdf->SetFillColor(223, 235, 247);
                        $border_color = array(190, 215, 239);
                    } else {
                        $pdf->SetFillColor(190, 215, 239);
                        $border_color = array(223, 235, 247);
                    }

                    $life_insured = (!empty($deal->life_insured)) ? $client->name . ", " . $deal->life_insured : $client->name;
                    //Get Actual Status
                    $actual_status = $deal->status;
                    $issue_date = "N/A";
                    $issued_api = "N/A";

                    if ($actual_status == "Issued") {
                        //if status is issued, check clawback status if not none
                        if (isset($deal->clawback_status)) {
                            if ($deal->clawback_status != "None") {
                                $actual_status = $deal->clawback_status;
                            }
                        }
                        $issue_date =  $indet_dates_helper->NZEntryToDateTime($deal->date_issued);
                        $issued_api = $generalController->ConvertToCurrency($deal->issued_api);
                    }

                    $row_height = $pdf->GetY();
                    $first_y = $pdf->GetY();
                    $pdf->Row(array($deal->company, $deal->policy_number, $life_insured, $issue_date, $indet_dates_helper->NZEntryToDateTime($deal->submission_date), $issued_api, $actual_status), true, $border_color);
                    $row_height = $pdf->GetY() - $row_height;
                    //$pdf->Rect(10, $first_y + 1, 197, $row_height, "FD");

                    if ($pdf->GetY() >= 312) {
                        $pdf->AddPage();
                    }
                }
            }
        }
    }
    //$mix="$mydateorig"."-$name-"."BCTI".-date("ymd");
    //$path="files/".$mix.".pdf";
    $path = "";
    //$pdf->Output($path,'F');
    if ($preview) {
        $pdf->Output("I", $client->name . " Issued Client Profile.pdf");
    } else {
        $path = "files/" . $client->name . " Issued Client Profile.pdf";
        $pdf->Output($path, 'F');
        return $path;
    }
    //db add end
    //}

}



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

function AddLineSpace($pdf, $linespace = 10)
{
    $pdf->SetFillColor(255, 255, 255);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell(200, $linespace, '', 0, 1, 'C', 'true');
}

/**
 * @param int $number
 * @return string
 */
function numberToRomanRepresentation($number)
{
    $map = array('M' => 1000, 'CM' => 900, 'D' => 500, 'CD' => 400, 'C' => 100, 'XC' => 90, 'L' => 50, 'XL' => 40, 'X' => 10, 'IX' => 9, 'V' => 5, 'IV' => 4, 'I' => 1);
    $returnValue = '';
    while ($number > 0) {
        foreach ($map as $roman => $int) {
            if ($number >= $int) {
                $number -= $int;
                $returnValue .= $roman;
                break;
            }
        }
    }
    return $returnValue;
}


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
