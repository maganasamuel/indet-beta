<?php
date_default_timezone_set('Pacific/Auckland');
ob_start();
require("fpdf/fpdf.php");






require("database.php");

require("libs/indet_generic_helper.php");
require("libs/indet_dates_helper.php");
require("libs/api/controllers/Adviser.controller.php");
require("libs/api/controllers/Client.controller.php");

/*
session_start();
*/
//post

class PDF extends FPDF
{


	function Footer()
	{
		global $fsp_num;
		global $name;
		$this->SetY(-15);
		$this->SetFont('Helvetica', '', 10);
		$this->SetTextColor(0, 0, 0);
		$this->Cell(0, 10, 'Invoice Summary ' . '' . ' ' . preg_replace("/\([^)]+\)/", "", ''), 0, 0, 'L');
		$this->Cell(0, 10, 'Page ' . $this->PageNo(), 0, 1, 'R');
	}

	function getPage()
	{
		return $this->PageNo();
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

//retrieving
extract($_POST);
//var_dump($_POST);
$statuses = json_decode($statuses);										//Desc
$generic_helper = new INDET_GENERIC_HELPER();
$date_helper = new INDET_DATES_HELPER();
$clientController = new ClientController();
$adviserController = new AdviserController();

$summary_data = new stdClass();

$pdf = new PDF('P', 'mm', 'Legal');
$x = $pdf->GetX();
$y = $pdf->GetY();

$period_covered =  $date_from . "-" . $until;

$date_from = $date_helper->DateTimeToNZEntry($date_from);
$until = $date_helper->DateTimeToNZEntry($until);

$summary_data->date_from = $date_from;
$summary_data->date_to = $until;
$summary_data->invoices = array();
$summary_data->invoice_numbers = array();
$summary_data->invoices_in_range = array();
$summary_data->payable_invoices = array();
$summary_data->invoice_transaction_histories = array();
$summary_data->data = new stdClass();
$summary_data->data->leads_payable = 0;
$summary_data->data->issued_payable = 0;
$summary_data->valid_invoices = array();
$summary_data->total_paid_assigned_leads = 0;
$summary_data->total_paid_issued_leads = 0;
$summary_data->total_billed_assigned_leads = 0;
$summary_data->total_billed_issued_leads = 0;
$summary_data->leads = array();
$summary_data->issued = array();
$summary_data->invoice_numbers_list = array();

//Amendents
$summary_data->clients_amended = array();
$summary_data->issued_clients_amended = array();

//amendments variables
$summary_data->issued_clients = array();
$summary_data->amendments = 0;
$summary_data->issued_amendments = 0;
$summary_data->amendments_amount = 0;
$summary_data->issued_amendments_amount = 0;

//Search all issued clients and store them in a string
$dataset = $clientController->getIssuedClientsAssignedTo($adviser_id);
while ($row = $dataset->fetch_assoc()) {
	$summary_data->issued_clients[] = $row["name"];
}
$issued_clients_list = implode(",", $summary_data->issued_clients);

//Fetch Invoices Data
$invoices_id_list = "";
$invoices_array = array();
$total_due = 0;


$summary_data->adviser = $adviserController->getAdviser($adviser_id);

$name = $summary_data->adviser["name"];
$fsp_num = $summary_data->adviser["fsp_num"];
$adviser_address = $summary_data->adviser["address"];
$adviser_id = $summary_data->adviser["id"];

//fetch all valid invoices
$summary_data->valid_invoices = $adviserController->getInvoiceNumbersFromTransactions($adviser_id);
$valid_invoices = implode("','", $summary_data->valid_invoices);

//Get all invoice transaction history and get all paid data
$dataset = $adviserController->getTransactionsInDateRange($adviser_id, $date_from, $until);

//Load billing and payment info
while ($row = $dataset->fetch_assoc()) {
	$summary_data->invoice_transaction_histories[] = $row;

	if (strpos($row["status"], 'Billed') !== false) {
		if (strpos($row["status"], 'Issued') !== false) {
			//Manual Issued Leads
			if (strpos($row["status"], 'Manual') !== false) {
				$summary_data->total_paid_issued_leads -= $row["number_of_leads"];
			} else {
				$summary_data->total_billed_issued_leads += $row["number_of_leads"];
			}
		} else {
			//Manual Assigned Leads
			if (strpos($row["status"], 'Manual') !== false) {
				$summary_data->total_paid_assigned_leads -= $row["number_of_leads"];
			} else {
				$summary_data->total_billed_assigned_leads += $row["number_of_leads"];
			}
		}
	} else {
		switch ($row["status"]) {
			case "Paid Assigned Leads":
				$summary_data->total_paid_assigned_leads += $row["number_of_leads"];
				break;
			case "Paid Issued Leads":
				$summary_data->total_paid_issued_leads += $row["number_of_leads"];
				break;
			case "Cancelled Leads":
			case "Waived Leads":
				$summary_data->total_billed_assigned_leads -= $row["number_of_leads"];
				$clients_in_transaction = explode(",", $row["clients_list"]);
				$summary_data->clients_amended = array_merge($summary_data->clients_amended, $clients_in_transaction);
				break;
			case "Cancelled Issued Leads":
			case "Waived Issued Leads":
				$summary_data->total_billed_issued_leads -= $row["number_of_leads"];
				$clients_in_transaction = explode(",", $row["clients_list"]);
				$summary_data->issued_clients_amended = array_merge($summary_data->clients_amended, $clients_in_transaction);
				break;
		}
	}
}



//Get Ammendments
foreach ($summary_data->invoice_transaction_histories as $transaction) {
	if ($transaction["status"] == "Waived Leads" || $transaction["status"] == "Cancelled Leads") {
		$summary_data->amendments += $transaction["number_of_leads"];
		$summary_data->amendments_amount += $transaction["amount"];
	} elseif ($transaction["status"] == "Waived Issued Leads" || $transaction["status"] == "Cancelled Issued Leads") {
		$summary_data->issued_amendments += $transaction["number_of_leads"];
		$summary_data->issued_amendments_amount += $transaction["amount"];
	}
}

$paid_assigned_wallet = $summary_data->total_paid_assigned_leads + $summary_data->amendments;
$paid_issued_wallet = $summary_data->total_paid_issued_leads + $summary_data->issued_amendments;

$dataset = $adviserController->getAdviserValidInvoices($adviser_id, $valid_invoices);
while ($row = $dataset->fetch_assoc()) {
	$rowleads = json_decode($row['client_leads']);
	$rowissued = json_decode($row['issued']);

	$inv = new stdClass();
	$inv->invoice_no = $row['number'];
	$inv->date_created = $row['date_created'];
	$inv->amount = $row['amount'];
	$inv->status = $row['status'];
	$inv->leads = count($rowleads);
	$inv->issued = count($rowissued);

	$inv->remaining_amount = $row['amount'];

	//check if wallet is empty and if not reduce assigned amount
	if ($paid_assigned_wallet > 0 && $inv->leads > 0) {
		$payment = 0;
		//Check if wallet is lesser than remaining leads
		if ($paid_assigned_wallet < $inv->leads) {
			$payment = $summary_data->adviser["leads"] * $paid_assigned_wallet;
			$paid_assigned_wallet = 0;
		}
		//If wallet is greater than or equal to remaining leads
		else {
			$payment = $summary_data->adviser["leads"] * $inv->leads;
			$paid_assigned_wallet -= $inv->leads;
		}
		$payment += ($payment * .15);
		$inv->remaining_amount -= $payment;
	}

	//check if wallet is empty and if not reduce billed amount
	if ($paid_issued_wallet > 0 && $inv->issued > 0) {
		$payment = 0;

		//Check if wallet is lesser than remaining leads
		if ($paid_issued_wallet < $inv->issued) {
			$payment = $summary_data->adviser["bonus"] * $paid_issued_wallet;
			$paid_issued_wallet = 0;
		}
		//If wallet is greater than or equal to remaining leads
		else {
			$payment = $summary_data->adviser["bonus"] * $inv->issued;
			$paid_issued_wallet -= $inv->issued;
		}
		$payment += ($payment * .15);
		$inv->remaining_amount -= $payment;
	}


	//$total_due += $row['amount'];

	if ($inv->remaining_amount > 0) {
		$summary_data->invoices[] = $inv;
		$summary_data->invoice_numbers_list[] = $inv->invoice_no;
	}
}

//Get numbers
$summary_data->invoice_numbers_list = implode(", ", $summary_data->invoice_numbers_list);

//fetch all valid invoice numbers within date range
$dataset = $adviserController->getAdviserValidInvoicesInRange($adviser_id, $date_from, $until, $valid_invoices);
while ($row = $dataset->fetch_assoc()) {
	array_push($invoices_array, $row['number']);

	$rowleads = json_decode($row['client_leads']);
	$rowissued = json_decode($row['issued']);
	$summary_data->invoice_numbers[] = $row['number'];

	//Remove clients in ammendments
	foreach ($rowleads as $lead) {
		if (!in_array($lead, $summary_data->clients_amended)) {
			$summary_data->leads[] = $lead;
		}
	}

	foreach ($rowissued as $issued) {
		if (!in_array($issued, $summary_data->issued_clients_amended)) {
			$summary_data->issued[] = $issued;
		}
	}
}

//Get all valid invoices and place it in the invoices in range pool 
foreach ($summary_data->invoices as $inv) {
	if (in_array($inv->invoice_no, $summary_data->invoice_numbers)) {
		$summary_data->invoices_in_range[] = $inv;
	}
}

$invoices_id_list = implode(", ", $summary_data->invoice_numbers);

$words = explode(" ", $name);
$adviser_initials = "";
$ctr = 0;

foreach ($words as $w) {
	$adviser_initials .= $w[0];
	$ctr++;
	if ($ctr >= 2)
		break;
}

$first_letter = mb_substr($adviser_initials, 0, 1, 'utf-8');
$second_letter = mb_substr($adviser_initials, 1, 1, 'utf-8');
$adviser_index = 0;

//GET ALL ADVISERS WITH SAME INITIALS
$adviser_initial_query = "SELECT * FROM adviser_tbl where LEFT(SUBSTRING_INDEX(name,' ',1),1) = '$first_letter' AND LEFT(SUBSTRING_INDEX(SUBSTRING_INDEX(name,' ',2),' ',-1),1) = '$second_letter'";
$adviser_initial_result = mysqli_query($con, $adviser_initial_query) or die('Could not look up user information; ' . mysqli_error($con));

while ($adviser_rows = mysqli_fetch_array($adviser_initial_result)) {
	$adviser_index++;
	if ($adviser_rows['id'] == $adviser_id) {
		break;
	}
}

if ($adviser_index != 1)
	$adviser_initials .= $adviser_index;

$invoice_summary_query = "SELECT COUNT(*) as total FROM summary WHERE number LIKE '$adviser_initials-%'";
$invoice_summary_result = mysqli_query($con, $invoice_summary_query) or die('Could not look up user information; ' . mysqli_error($con));
$invoice_summary_count = mysqli_fetch_assoc($invoice_summary_result);

function convertToFourDigits($num = 0)
{
	$op = "";
	if ($num < 10) {
		$op = "000" . $num;
	} elseif ($num < 100) {
		$op = "00" . $num;
	} elseif ($num < 1000) {
		$op = "0" . $num;
	} elseif ($num < 10000) {
		$op = "" . $num;
	}
	return $op;
}

$invoice_summary_number = $adviser_initials . "-" . convertToFourDigits(($invoice_summary_count['total'] + 1)) . date("Ymd");

$summary_data->payable_assigned_leads = $summary_data->total_billed_assigned_leads - $summary_data->total_paid_assigned_leads;
//Remove leads depending on the number of leads paid
$summary_data->leads = array_slice($summary_data->leads, 0, $summary_data->payable_assigned_leads);

$summary_data->payable_issued_leads = $summary_data->total_billed_issued_leads - $summary_data->total_paid_issued_leads;
//Remove leads depending on the number of leads paid
$summary_data->issued = array_slice($summary_data->issued, 0, $summary_data->payable_issued_leads);
$raw_lead_payable = count($summary_data->leads) * $summary_data->adviser["leads"];
$raw_issued_payable = count($summary_data->issued) * $summary_data->adviser["bonus"];

$total_due = (($raw_lead_payable) + ($raw_lead_payable * .15)) + (($raw_issued_payable) + ($raw_issued_payable * .15));
//page 1
$pdf->AddPage('P', 'Legal');

$pdf->SetFillColor(224, 224, 224);
$pdf->Image('logo.png', 10, 10, -160);
$pdf->SetFont('Helvetica', 'B', 18);
$pdf->SetTextColor(0, 0, 0);
$pdf->Cell(0, 20, '', "0", "1", "C");
$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('Helvetica', 'B', 10);
$pdf->SetFillColor(224, 224, 224);


$pdf->SetFont('Helvetica', '', 12);
$pdf->MultiCell(55, 6, "3G/39 Mackelvie Street Grey Lynn 1021 Auckland New Zealand 0508 123 467", 0, "L", false);

$pdf->SetTextColor(44, 44, 44);
$pdf->SetXY($x + 100, $y + 35); // position of text1, numerical, of course, not x1 and y1
$pdf->SetFont('Helvetica', 'B', 12);
$pdf->Write(0, 'Phone');
$pdf->SetTextColor(0, 0, 0);

$pdf->SetXY($x + 120, $y + 35); // position of text1, numerical, of course, not x1 and y1
$pdf->SetFont('Helvetica', '', 12);
$pdf->Write(0, '0508 123 467');


$pdf->SetXY($x + 100, $y + 40); // position of text1, numerical, of course, not x1 and y1
$pdf->SetFont('Helvetica', 'B', 12);
$pdf->SetTextColor(44, 44, 44);
$pdf->Write(0, 'Website');
$pdf->SetTextColor(0, 0, 0);

$pdf->SetXY($x + 120, $y + 40); // position of text1, numerical, of course, not x1 and y1
$pdf->SetFont('Helvetica', '', 12);
$pdf->Write(0, 'www.eliteinsure.co.nz');

$pdf->SetTextColor(44, 44, 44);
$pdf->SetXY($x + 100, $y + 45); // position of text1, numerical, of course, not x1 and y1
$pdf->SetFont('Helvetica', 'B', 12);
$pdf->Write(0, 'Email');
$pdf->SetTextColor(0, 0, 0);

$pdf->SetXY($x + 120, $y + 45); // position of text1, numerical, of course, not x1 and y1
$pdf->SetFont('Helvetica', '', 12);
$pdf->Write(0, 'admin@eliteinsure.co.nz');

$pdf->SetTextColor(12, 31, 69);
$pdf->SetXY($x + 100, $y + 60); // position of text1, numerical, of course, not x1 and y1
$pdf->SetFont('Helvetica', 'B', 12);
$pdf->Write(0, '');

$pdf->SetTextColor(0, 0, 0);

$pdf->SetXY($x + 150, $y + 60); // position of text1, numerical, of course, not x1 and y1
$pdf->SetFont('Helvetica', '', 12);
$pdf->Write(0, "");

$pdf->SetTextColor(12, 31, 69);
$pdf->SetXY($x + 100, $y + 67); // position of text1, numerical, of course, not x1 and y1
$pdf->SetFont('Helvetica', 'B', 12);
$pdf->Write(0, 'Period Covered');
$pdf->SetTextColor(0, 0, 0);

$pdf->SetXY($x + 150, $y + 67); // position of text1, numerical, of course, not x1 and y1
$pdf->SetFont('Helvetica', '', 12);
$pdf->Write(0, $period_covered);

$pdf->SetTextColor(12, 31, 69);
$pdf->SetXY($x + 100, $y + 74); // position of text1, numerical, of course, not x1 and y1
$pdf->SetFont('Helvetica', 'B', 12);
$pdf->Write(0, 'Summary Number');
$pdf->SetTextColor(0, 0, 0);


$pdf->SetXY($x + 150, $y + 74); // position of text1, numerical, of course, not x1 and y1
$pdf->SetFont('Helvetica', '', 12);
$pdf->Write(0, $invoice_summary_number);

$pdf->SetTextColor(12, 31, 69);
$pdf->SetXY($x + 100, $y + 81); // position of text1, numerical, of course, not x1 and y1
$pdf->SetFont('Helvetica', 'B', 12);
$pdf->Write(0, 'GST Number');
$pdf->SetTextColor(0, 0, 0);

$pdf->SetXY($x + 150, $y + 81); // position of text1, numerical, of course, not x1 and y1
$pdf->SetFont('Helvetica', '', 12);
$pdf->Write(0, '119-074-304');


$pdf->SetTextColor(12, 31, 69);
$pdf->SetXY($x + 100, $y + 88); // position of text1, numerical, of course, not x1 and y1
$pdf->SetFont('Helvetica', 'B', 12);
$pdf->Write(0, 'Client Code');

$pdf->SetTextColor(0, 0, 0);
$pdf->SetXY($x + 150, $y + 88); // position of text1, numerical, of course, not x1 and y1
$pdf->SetFont('Helvetica', '', 12);
$pdf->Write(0, $fsp_num);

$pdf->SetXY($x + 10, $y + 60); // position of text1, numerical, of course, not x1 and y1
$pdf->SetFont('Helvetica', '', 18);
$pdf->Cell(0, 10, 'Adviser\'s Invoice Summary', "0", "1", "L");

$pdf->SetXY($x + 10, $y + 75); // position of text1, numerical, of course, not x1 and y1
$pdf->SetFont('Helvetica', '', 12);
$pdf->Write(0, 'To: ');

$pdf->SetXY($x + 20, $y + 75); // position of text1, numerical, of course, not x1 and y1

$pdf->Write(0, $name);

$pdf->SetXY($x + 20, $y + 80); // position of text1, numerical, of course, not x1 and y1

$pdf->MultiCell(50, 5, "$adviser_address", 0, "L", false);


//DESCRIPTION
//$pdf->SetXY($x+10, $y+60); // position of text1, numerical, of course, not x1 and y1
$pdf->SetXY($x + 10, $y + 118);

$pdf->SetFont('Helvetica', '', 14);
$pdf->SetFillColor(224, 224, 224);
$pdf->Cell(100, 10, 'DESCRIPTION', 0, 0, 'L', 'true');
$pdf->Cell(43, 10, ' ', 0, 0, 'R', 'true');
$pdf->Cell(60, 10, '  Total', 0, 1, 'C', 'true');

$pdf->SetXY($x + 10, $y + 127);
$pdf->SetFont('Helvetica', 'B', 12);
$pdf->Cell(100, 10, 'Payable Assigned Leads', 0, 0, 'L');
$pdf->Cell(43, 10, ' ', 0, 0, 'R');
$pdf->SetFont('Helvetica', '', 12);
$pdf->Cell(60, 10, $summary_data->payable_assigned_leads, 0, 1, 'C');
//$pdf->Cell(55,10,'$'.desc_val($desc[0]), 0, 1,'R');

$pdf->SetXY($x + 10, $y + 137);
$pdf->SetFont('Helvetica', 'B', 12);
$pdf->Cell(100, 10, 'Payable Issued Leads', 0, 0, 'L');
$pdf->Cell(43, 10, ' ', 0, 0, 'R');
$pdf->SetFont('Helvetica', '', 12);
$pdf->Cell(60, 10, $summary_data->payable_issued_leads, 0, 1, 'C');

$pdf->SetXY($x + 10, $y + 161); // position of text1, numerical, of course, not x1 and y1
$pdf->SetFont('Helvetica', '', 14);
$pdf->SetFillColor(224, 224, 224);
$pdf->Cell(100, 10, 'PAYMENT ADVICE', "0", "0", "L", 'true');
$pdf->Cell(40, 10, '', "0", "0", "L", 'true');
$pdf->Cell(60, 10, '', "0", "1", "C", 'true');

$pdf->SetXY($x + 10, $y + 180); // position of text1, numerical, of course, not x1 and y1
$pdf->SetFont('Helvetica', '', 12);
$pdf->SetFillColor(0, 0, 0);
$pdf->SetFont('Helvetica', 'B', 12);
$pdf->Cell(40, 0, 'Client', "0", "0", "L");
$pdf->SetFont('Helvetica', '', 12);
$pdf->Cell(40, 0, $name, "0", "0", "L");
$pdf->Cell(60, 0, '', "0", "1", "C");

$pdf->SetXY($x + 10, $y + 187); // position of text1, numerical, of course, not x1 and y1
$pdf->SetFont('Helvetica', '', 12);
$pdf->SetFillColor(0, 0, 0);
$pdf->SetFont('Helvetica', 'B', 12);

$pdf->Cell(40, 0, 'Summary Number', "0", "0", "L");
$pdf->SetFont('Helvetica', '', 12);
$pdf->Cell(40, 0, $invoice_summary_number, "0", "0", "L");
$pdf->Cell(60, 0, '', "0", "1", "C");


$pdf->SetXY($x + 10, $y + 194); // position of text1, numerical, of course, not x1 and y1
$pdf->SetFont('Helvetica', '', 12);
$pdf->SetFillColor(0, 0, 0);
$pdf->SetFont('Helvetica', 'B', 12);
$pdf->Cell(40, 0, 'Total Due', "0", "0", "L");
$pdf->SetFont('Helvetica', '', 12);
$pdf->Cell(40, 0, '$' . number_format($total_due, 2), "0", "0", "L");
$pdf->Cell(60, 0, '', "0", "1", "C");
$pdf->SetXY($x + 10, $y + 205); // position of text1, numerical, of course, not x1 and y1
$pdf->SetFont('Helvetica', '', 12);
$pdf->SetFillColor(0, 0, 0);
$pdf->SetFont('Helvetica', 'B', 12);
$pdf->Cell(40, 11, 'Invoice Number(s)', "0", "0", "L");

$pdf->SetFont('Helvetica', '', 12);



//$invoices_id_list = var_dump($summary_data->invoices_in_range);
//Stress Test
//$pdf->MultiCell(48,11,$invoices_id_list . "," . $invoices_id_list."," . $invoices_id_list."," . $invoices_id_list."," . $invoices_id_list,0,"L",false);
$pdf->MultiCell(100, 11, $summary_data->invoice_numbers_list, 0, "L", false);
$pdf->Cell(60, 0, '', "0", "1", "C");

$pdf->SetXY($x + 120, $y + 178); // position of text1, numerical, of course, not x1 and y1

$pdf->SetFont('Helvetica', '', 12);
$pdf->MultiCell(85, 5, "Direct Credit
Please make payment into the following account: Eliteinsure Ltd, ANZ Bank, 06-0254-0426124-00. Please use the reference " . $fsp_num . ". ", 0, "L", false);


//Start Displaying Invoices
//Initialize page 2
$pdf->AddPage('P', 'Legal');
$pdf->Image('logo.png', 10, 10, -160);
$pdf->SetFont('Helvetica', 'B', 14);
$pdf->SetTextColor(0, 42, 160);
$pdf->SetXY($x + 10, $y + 30);

//echo count($paid_invoices) . "<br>";
//var_dump($statuses);
//PENDING INVOICES

usort($summary_data->invoices_in_range, "sortFunction");

if (count($summary_data->invoices_in_range) > 0) :
	//Heading
	$pdf->SetFont('Helvetica', 'B', 14);
	$pdf->SetFillColor(224, 224, 224);
	$pdf->SetTextColor(0, 0, 0);
	$pdf->Cell(100, 10, 'Invoices', 0, 0, 'L', 'true');
	$pdf->Cell(43, 10, ' ', 0, 0, 'R', 'true');
	$pdf->Cell(43, 10, ' ', 0, 0, 'R', 'true');
	$pdf->Cell(43, 10, ' ', 0, 0, 'R', 'true');
	$pdf->Cell(60, 10, '  ', 0, 1, 'C', 'true');

	//Set Style
	$pdf->SetFont('Helvetica', 'U', 12);
	$pdf->SetFillColor(0, 0, 0);
	$pdf->SetTextColor(0, 0, 0);

	//HEADERS
	$pdf->Cell(40, 7, '', 0, 0, 'C');
	$pdf->Cell(35, 7, '', 0, 0, 'C');
	$pdf->Cell(30, 7, 'Leads', 0, 0, 'C');
	$pdf->Cell(30, 7, 'Leads', 0, 0, 'C');
	$pdf->Cell(30, 7, 'Original', 0, 0, 'C');
	$pdf->Cell(30, 7, 'Remaining', 0, 1, 'C');

	$pdf->Cell(40, 7, 'Invoice Number', 0, 0, 'C');
	$pdf->Cell(35, 7, 'Date Created', 0, 0, 'C');
	$pdf->Cell(30, 7, 'Assigned', 0, 0, 'C');
	$pdf->Cell(30, 7, 'Issued', 0, 0, 'C');
	$pdf->Cell(30, 7, 'Amount', 0, 0, 'C');
	$pdf->Cell(30, 7, 'Amount', 0, 1, 'C');

	//Set Style
	$pdf->SetFont('Helvetica', '', 12);
	$leadstotal = 0;
	$issuedtotal = 0;
	$amounttotal = 0;
	$remainingtotal = 0;
	foreach ($summary_data->invoices_in_range as $inv) {
		$date_created = date("d/m/Y", strtotime($inv->date_created));

		$leadstotal += $inv->leads;
		$issuedtotal += $inv->issued;
		$amounttotal += $inv->amount;
		$remainingtotal += $inv->remaining_amount;

		$pdf->Cell(40, 6, $inv->invoice_no, 0, 0, 'C');
		$pdf->Cell(35, 6, $date_created, 0, 0, 'C');
		$pdf->Cell(30, 6, $inv->leads, 0, 0, 'C');
		$pdf->Cell(30, 6, $inv->issued, 0, 0, 'C');
		$pdf->Cell(30, 6, "$" . number_format($inv->amount, 2), 0, 0, 'C');
		$pdf->Cell(30, 6, "$" . number_format($inv->remaining_amount, 2), 0, 1, 'C');
	}

	$pdf->SetFont('Helvetica', 'B', 12);
	$pdf->Cell(40, 6, "Total", "T", 0, 'C');
	$pdf->Cell(35, 6, "", "T", 0, 'C');
	$pdf->Cell(30, 6, $summary_data->payable_assigned_leads, "T", 0, 'C');
	$pdf->Cell(30, 6, $summary_data->payable_issued_leads, "T", 0, 'C');
	$pdf->Cell(30, 6, "$" . number_format($amounttotal, 2), "T", 0, 'C');
	$pdf->Cell(30, 6, "$" . number_format($remainingtotal, 2), "T", 1, 'C');
	$pdf->Ln();
endif;

if (count($summary_data->invoice_transaction_histories) > 0) :
	//Heading
	$pdf->SetFont('Helvetica', 'B', 14);
	$pdf->SetFillColor(224, 224, 224);
	$pdf->SetTextColor(0, 0, 0);
	$pdf->Cell(100, 10, 'Transactions', 0, 0, 'L', 'true');
	$pdf->Cell(43, 10, ' ', 0, 0, 'R', 'true');
	$pdf->Cell(43, 10, ' ', 0, 0, 'R', 'true');
	$pdf->Cell(43, 10, ' ', 0, 0, 'R', 'true');
	$pdf->Cell(60, 10, '  ', 0, 1, 'C', 'true');

	//Set Style
	$pdf->SetFont('Helvetica', 'U', 12);
	$pdf->SetFillColor(0, 0, 0);
	$pdf->SetTextColor(0, 0, 0);

	//HEADERS
	$pdf->Cell(35, 7, 'Date Created', 0, 0, 'L');
	$pdf->Cell(100, 7, 'Description', 0, 0, 'L');
	$pdf->Cell(30, 7, 'Quantity', 0, 0, 'L');
	$pdf->Cell(45, 7, 'Amount', 0, 1, 'L');

	//Set Style
	$pdf->SetFont('Helvetica', '', 12);
	$leadstotal = 0;
	$issuedtotal = 0;
	$amounttotal = 0;
	$remainingtotal = 0;
	$transactions_limit = 0;

	foreach ($summary_data->invoice_transaction_histories as $transaction) {
		$date_created = date("d/m/Y", strtotime($transaction["date"]));

		$pdf->Cell(35, 6, $date_created, 0, 0, 'L');
		$pdf->Cell(100, 6, $transaction["status"], 0, 0, 'L');
		$pdf->Cell(30, 6, $transaction["number_of_leads"], 0, 0, 'L');
		$pdf->Cell(45, 6, "$" . number_format($transaction["amount"], 2), 0, 1, 'L');

		$transactions_limit++;

		if ($transactions_limit >= 20) {
			break;
		}
	}

	$pdf->Ln();
endif;


if (count($summary_data->leads) > 0) :
	//Heading
	$pdf->SetFont('Helvetica', 'B', 14);
	$pdf->SetFillColor(224, 224, 224);
	$pdf->SetTextColor(0, 0, 0);
	$pdf->Cell(100, 10, 'Leads Payable', 0, 0, 'L', 'true');
	$pdf->Cell(43, 10, ' ', 0, 0, 'R', 'true');
	$pdf->Cell(43, 10, ' ', 0, 0, 'R', 'true');
	$pdf->Cell(43, 10, ' ', 0, 0, 'R', 'true');
	$pdf->Cell(60, 10, '  ', 0, 1, 'C', 'true');

	//Set Style
	$pdf->SetFont('Helvetica', 'U', 12);
	$pdf->SetFillColor(0, 0, 0);
	$pdf->SetTextColor(0, 0, 0);

	//HEADERS
	$pdf->Cell(160, 7, 'Name', 0, 0, 'L');
	$pdf->Cell(35, 7, 'Date Assigned', 0, 1, 'L');

	//Set Style
	$pdf->SetFont('Helvetica', '', 12);

	$dataset = $clientController->getClientsInArray($summary_data->leads);
	while ($rows = $dataset->fetch_assoc()) {
		$date_assigned = date("d/m/Y", strtotime($rows["assigned_date"]));

		$pdf->Cell(160, 6, $rows["name"], 0, 0, 'L');
		$pdf->Cell(35, 6, $date_assigned, 0, 1, 'L');
	}

	$pdf->SetFont('Helvetica', 'B', 12);
	$pdf->Cell(160, 7, 'Total Leads Payable', "T", 0, 'L');
	$pdf->Cell(35, 7, count($summary_data->leads), "T", 1, 'L');
	$pdf->Ln();
endif;

//reverse array first


if (count($summary_data->issued) > 0) :
	//Heading
	$pdf->SetFont('Helvetica', 'B', 14);
	$pdf->SetFillColor(224, 224, 224);
	$pdf->SetTextColor(0, 0, 0);
	$pdf->Cell(100, 10, 'Issued Leads Payable', 0, 0, 'L', 'true');
	$pdf->Cell(43, 10, ' ', 0, 0, 'R', 'true');
	$pdf->Cell(43, 10, ' ', 0, 0, 'R', 'true');
	$pdf->Cell(43, 10, ' ', 0, 0, 'R', 'true');
	$pdf->Cell(60, 10, '  ', 0, 1, 'C', 'true');


	//Set Style
	$pdf->SetFont('Helvetica', 'U', 12);
	$pdf->SetFillColor(0, 0, 0);
	$pdf->SetTextColor(0, 0, 0);

	//HEADERS
	$pdf->Cell(160, 7, 'Name', 0, 0, 'L');
	$pdf->Cell(35, 7, 'Date Assigned', 0, 1, 'L');

	//Set Style
	$pdf->SetFont('Helvetica', '', 12);

	$imploded_array = implode(",", $summary_data->issued);

	$dataset = $clientController->getClientsInArray($summary_data->issued);
	while ($rows = $dataset->fetch_assoc()) {
		$date_assigned = date("d/m/Y", strtotime($rows["assigned_date"]));

		$pdf->Cell(160, 6, $rows["name"], 0, 0, 'L');
		$pdf->Cell(35, 6, $date_assigned, 0, 1, 'L');
	}

	$pdf->SetFont('Helvetica', 'B', 12);
	$pdf->Cell(160, 7, 'Total Issued Leads Payable', "T", 0, 'L');
	$pdf->Cell(35, 7, $rowcount, "T", 1, 'L');
	$pdf->Ln();
endif;

$preview = "invoice_summary_" . md5(uniqid());
$path = "files/$preview" . "_preview.pdf";
$pdf->Output($path, 'F');

//OUTPUT 
$file = array();
$file['number'] = $invoice_summary_number;
$file['link'] = $path;
$file['data'] = json_encode($summary_data);
$file['adviser_id'] = $adviser_id;
$file['from'] = $date_from;
$file['to'] = $until;


echo json_encode($file);
//db add end
//}

function sortFunction($a, $b)
{
	if (isset($a->date_created) && isset($b->date_created)) {
		return strtotime($a->date_created) - strtotime($b->date_created);
	}
	if (isset($a["date_created"]) && isset($b["date_created"])) {
		return strtotime($a["date_assigned"]) - strtotime($b["date_assigned"]);
	}
}
