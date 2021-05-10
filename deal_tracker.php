<?php
date_default_timezone_set('Pacific/Auckland');
ob_start();
session_start();
require("fpdf/mc_table.php");






require("database.php");
/*
session_start();
*/
//post

class PDF extends PDF_MC_Table
{
	var $adviser = "";

	function Footer()
	{
		global $fsp_num;
		global $name;
		$this->SetY(-15);
		$this->SetFillColor(0, 0, 0);
		$this->Rect(5, 342, 206.5, .5, "FD");
		$this->SetFont('Helvetica', '', 10);
		$this->SetTextColor(0, 0, 0);
		$this->Cell(0, 10, 'Deal Tracker - ' . $this->adviser . '' . ' ' . preg_replace("/\([^)]+\)/", "", ''), 0, 0, 'L');
		$this->AliasNbPages('{totalPages}');
		$this->Cell(0, 10, 'Page ' . $this->PageNo() . " of " . "{totalPages}", 0, 1, 'R');
	}

	function Header()
	{
		$this->SetFillColor(0, 0, 0);
		$this->Image('logo_vertical.png', 93, 5, 30);
		$this->Rect(5, 25, 206.5, .5, "FD");
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
}

$tracker_id = $_GET["id"];
//Fetch Adviser Data
$searchadv = "SELECT * FROM deal_tracker_reports WHERE id='$tracker_id'";
$search = mysqli_query($con, $searchadv) or die('Could not look up user information; ' . mysqli_error($con));
$deal_tracker_report = mysqli_fetch_assoc($search);

$created_by = $deal_tracker_report["created_by"];

$creator_query = "Select u.*, p.full_name from users u LEFT JOIN personal_data p ON u.linked_id = p.id where u.id = $created_by";
$creator_result = mysqli_query($con, $creator_query) or die('Could not look up user information; ' . mysqli_error($con));
$creator_row = mysqli_fetch_array($creator_result);


$created_by = $creator_row["full_name"];

//retrieving
$adviser_id = $deal_tracker_report["adviser_id"];		//Adviser id
$date_from = $deal_tracker_report['date_from'];			//Date from
$date_created = $deal_tracker_report['date_created'];			//Date from
$until = $deal_tracker_report['date_to'];
$report_data = json_decode($deal_tracker_report['report_data']);
$pay_date = NZEntryToDateTime($deal_tracker_report['pay_date']);
$note_entries = json_decode($deal_tracker_report['notes']);
$searchadv = "SELECT *, a.name as adviser_name, t.name as team_name FROM adviser_tbl a LEFT JOIN teams t ON a.team_id = t.id WHERE a.id='$adviser_id'";
$search = mysqli_query($con, $searchadv) or die('Could not look up user information; ' . mysqli_error($con));
$rows = mysqli_fetch_array($search);

//Extract Data

$d1 = new DateTime($date_from); // Y-m-d
$d2 = new DateTime($until);

$period_covered_title = $d1->format('d/m/Y') . "-" . $d2->format('d/m/Y');


function CheckTransactionStatus($status)
{
	$issued = stripos($status, 'Billed Issued Leads') !== false;
	$assigned = stripos($status, 'Billed Assigned Leads') !== false;
	$op = $status;
	if ($issued) {
		$op = "Billed Issued Leads";
	} elseif ($assigned) {
		$op = "Billed Assigned Leads";
	}

	return $op;
}

//Report Data

function addToDeals($deal, $status)
{
	$add_to = "";
	switch ($status) {
		case "Issued":

			break;
	}
}

$search_issued = "SELECT * FROM issued_clients_tbl i LEFT JOIN clients_tbl c ON i.name = c.id WHERE c.assigned_to='$adviser_id' AND  i.date_issued<='$until' AND i.date_issued>=$date_from AND c.lead_by!='Telemarketer'";
//Remove c.lead_by!='Telemarketer' to include leads from telemarketers
$issued_exec = mysqli_query($con, $search_issued) or die('Could not look up user information; ' . mysqli_error($con));
$count_issued = mysqli_num_rows($issued_exec);


$pdf = new PDF('P', 'mm', 'Legal');
$x = $pdf->GetX();
$y = $pdf->GetY();
$pdf->adviser = $report_data->adviser_data->name;

$adviser_team = (!empty($report_data->adviser_data->team_name)) ? $report_data->adviser_data->team_name : "Not Assigned";

//page 1
$pdf->AddPage('P', 'Legal');

//Title
$pdf->SetFillColor(224, 224, 224);
$pdf->SetFont('Helvetica', 'B', 20);
$pdf->Cell(200, 10, 'Deal Tracker', "0", "1", "C", 'true');

$pdf->SetFont('Helvetica', 'B', 15);
$pdf->Cell(17, 10, "Name:", "0", "0", "L");
$pdf->SetFont('Helvetica', '', 15);
$pdf->Cell(78, 10, $report_data->adviser_data->name, "0", "0", "L");
$pdf->Cell(5, 10, '', "0", "0", "R");
$pdf->SetFont('Helvetica', 'B', 15);
$pdf->Cell(16, 10, "Team:", "0", "0", "L");
$pdf->SetFont('Helvetica', '', 15);
$pdf->Cell(84, 10, $adviser_team, "0", "1", "L");


$pdf->SetFont('Helvetica', 'B', 15);
$pdf->Cell(35, 10, "FSP Number:", "0", "0", "L");
$pdf->SetFont('Helvetica', '', 15);
$pdf->Cell(60, 10, $report_data->adviser_data->fsp_num, "0", "0", "L");

$pdf->Cell(5, 10, '', "0", "0", "R");
$pdf->SetFont('Helvetica', 'B', 15);
$pdf->Cell(42, 10, "Period Covered:", "0", "0", "L");
$pdf->SetFont('Helvetica', '', 15);
$pdf->Cell(58, 10, "$period_covered_title", "0", "1", "L");


$pdf->SetFont('Helvetica', 'B', 15);
$pdf->Cell(17, 10, "Email:", "0", "0", "L");
$pdf->SetFont('Helvetica', '', 15);
$pdf->Cell(78, 10, $report_data->adviser_data->email, "0", "0", "L");
$pdf->Cell(5, 10, '', "0", "0", "R");
$pdf->SetFont('Helvetica', 'B', 15);
$pdf->Cell(25, 10, "Pay Date:", "0", "0", "L");
$pdf->SetFont('Helvetica', '', 15);
$pdf->Cell(75, 10, "$pay_date", "0", "1", "L");

$pdf->SetFont('Helvetica', 'B', 14);
$pdf->Cell(25, 10, "Company:", "0", "0", "L");
$pdf->SetFont('Helvetica', '', 14);
$pdf->Cell(70, 10, $report_data->adviser_data->company_name, "0", "0", "L");

$pdf->Cell(5, 10, '', "0", "0", "R");
$pdf->SetFont('Helvetica', 'B', 14);
$pdf->Cell(27, 10, "Report By:", "0", "0", "L");
$pdf->SetFont('Helvetica', '', 14);
$pdf->Cell(73, 10, "$created_by", "0", "1", "L");

//Space
AddLineSpace($pdf);

$pdf->SetFont('Helvetica', 'B', 14);
$pdf->SetFillColor(224, 224, 224);
$pdf->Cell(200, 10, 'NOTES', 0, 1, 'C', 'true');

$show_desc = '';


//formula

if (is_array($note_entries)) {
	if (count($note_entries) > 0) {
		if (!empty($note_entries[0])) {
			$pdf->SetFont('Helvetica', '', 13);
			$ctr = 1;
			foreach ($note_entries as $note) {
				$note = str_replace("u0027", "'", $note);
				$note = str_replace("<br>", "\r\n", $note);
				$pdf->SetFillColor(255, 255, 255);
				$fill = (($ctr % 2) === 0) ? true : false;
				if (($ctr % 2) === 0) {
					$pdf->SetFillColor(235, 235, 235);
				}
				$pdf->MultiCell(200, 10, $ctr . ". " . $note, 0, 'L', $fill);
				$ctr++;
			}
		} else {
			$pdf->Cell(200, 10, "No Entries Recorded.", 0, 1, 'C');
		}
	} else {
		$pdf->Cell(200, 10, "No Entries Recorded.", 0, 1, 'C');
	}
} else {
	$pdf->Cell(200, 10, "No Entries Recorded.", 0, 1, 'C');
}

//Space
AddLineSpace($pdf);

$pdf->SetFont('Helvetica', 'B', 15);
$pdf->SetFillColor(224, 224, 224);
$pdf->Cell(200, 10, 'LEADS', 0, 1, 'C', 'true');

$pdf->SetFont('Helvetica', 'B', 13);
$pdf->Cell(34, 10, "Rate Per Lead:", "0", "0", "L");
$pdf->SetFont('Helvetica', '', 13);
$pdf->Cell(61, 10, "$" . $report_data->adviser_data->leads, "0", "0", "L");
$pdf->Cell(5, 10, '', "0", "0", "R");

$pdf->SetFont('Helvetica', 'B', 13);
$pdf->Cell(50, 10, "Rate Per Issued Lead:", "0", "", "L");
$pdf->SetFont('Helvetica', '', 13);
$pdf->Cell(50, 10, "$" . $report_data->adviser_data->issued, "0", "1", "L");


$pdf->SetFont('Helvetica', 'B', 13);
$pdf->Cell(33, 10, 'Total Balance:', "0", "0", "L");
$pdf->SetFont('Helvetica', '', 13);
$pdf->Cell(62, 10, '$' . number_format($report_data->total_balance, 2), "0", "0", "L");
$pdf->Cell(5, 10, '', "0", "0", "R");

$pdf->SetFont('Helvetica', 'B', 13);
$pdf->Cell(70, 10, "Assigned Leads for the Period:", "0", "0", "L");
$pdf->SetFont('Helvetica', '', 13);
$pdf->Cell(30, 10, "$report_data->assigned_leads_for_period", "0", "1", "L");

$pdf->SetFont('Helvetica', 'B', 13);
$pdf->Cell(47, 10, "Total Leads Payable:", "0", "0", "L");
$pdf->SetFont('Helvetica', '', 13);
$pdf->Cell(48, 10, "$report_data->total_leads_payable", "0", "0", "L");
$pdf->Cell(5, 10, '', "0", "0", "R");
$pdf->SetFont('Helvetica', 'B', 13);
$pdf->Cell(63, 10, "Leads Issued for the Period:", "0", "0", "L");
$pdf->SetFont('Helvetica', '', 13);
$pdf->Cell(37, 10, "$report_data->issued_leads_for_period", "0", "1", "L");

$pdf->SetFont('Helvetica', 'B', 13);
$pdf->Cell(48, 10, "Total Issued Payable:", "0", "0", "L");
$pdf->SetFont('Helvetica', '', 13);
$pdf->Cell(47, 10, "$report_data->total_issued_payable", "0", "0", "L");
$pdf->Cell(5, 10, '', "0", "0", "R");
$pdf->SetFont('Helvetica', 'B', 13);
$pdf->Cell(82, 10, "KiwiSaver Enrolments for the Period:", "0", "0", "L");
$pdf->SetFont('Helvetica', '', 13);
$pdf->Cell(18, 10, count($report_data->kiwisaver_deals), "0", "1", "L");

if(count($report_data->issued_deals) > 0){
		
	//Space
	AddLineSpace($pdf);

	//Production
	$pdf->SetFont('Helvetica', 'B', 14);
	$pdf->SetFillColor(224, 224, 224);
	$pdf->SetTextColor(0, 0, 0);
	$pdf->Cell(200, 10, 'PRODUCTION', 0, 1, 'C', 'true');

	$pdf->SetFont('Helvetica', 'U', 12);
	$pdf->SetFillColor(0, 0, 0);
	$pdf->SetTextColor(0, 0, 0);

	$pdf->SetWidths(array(30, 20, 20, 20, 20, 20, 20, 50));
	$pdf->Row(array("Life Insured", "Policy #", "Co.", "Source", "Issue Date", "API", "Comp. Status", "Notes"), false, array(224, 224, 224));

	$pdf->SetFont('Helvetica', '', 9);
	$ctr = 0;
	$rep_data = json_encode($report_data->issued_deals);
	$report_data->issued_deals = json_decode($rep_data, true);
	usort($report_data->issued_deals, "sortFunction");
	foreach ($report_data->issued_deals as $deal) {
		extract($deal);

		if ($source == "")
			$source = $report_data->adviser_data->name;

		$pdf->SetFillColor(224, 224, 224);
		$fill = (($ctr % 2) === 0) ? true : false;
		$pdf->Row(array($life_insured, $policy_number, $company, $source, NZEntryToDateTime($date), "$" . number_format($api, 2), $compliance_status, str_replace("<br>", "\r\n", $notes)), $fill, array(224, 224, 224));
		$ctr++;
	}

	$pdf->SetDrawColor(0, 0, 0);

	$pdf->SetFont('Helvetica', 'B', 11);
	$pdf->Cell(105, 10, 'Total Payable API', "T", 0, 'L');
	$pdf->Cell(30, 10, "$" . number_format($report_data->total_issued_api, 2), "T", 0, 'C');
	$pdf->Cell(65, 10, '', "T", 1, 'C');
}

if(count($report_data->cancelled_deals) > 0) {
	//Space
	AddLineSpace($pdf);

	//Clawbacks
	$pdf->SetFont('Helvetica', 'B', 14);
	$pdf->SetFillColor(224, 224, 224);
	$pdf->SetTextColor(0, 0, 0);
	$pdf->Cell(200, 10, 'CLAWBACKS', 0, 1, 'C', 'true');

	$pdf->SetWidths(array(40, 20, 20, 20, 20, 30, 50));

	$pdf->SetFont('Helvetica', 'U', 12);
	$pdf->SetFillColor(0, 0, 0);
	$pdf->SetTextColor(0, 0, 0);



	$pdf->Row(array("Life Insured", "Policy #", "Co.", "Issue Date", "API", "Status", "Notes"), false, array(224, 224, 224));

	$pdf->SetFont('Helvetica', '', 9);
	$ctr = 0;
	$rep_data = json_encode($report_data->cancelled_deals);
	$report_data->cancelled_deals = json_decode($rep_data, true);
	usort($report_data->cancelled_deals, "sortFunction");
	foreach ($report_data->cancelled_deals as $deal) {
		extract($deal);
		$pdf->SetFillColor(224, 224, 224);
		$fill = (($ctr % 2) === 0) ? true : false;
		$pdf->Row(array($life_insured, $policy_number, $company, NZEntryToDateTime($issued_date), "$" . number_format($api, 2), $clawback_status, str_replace("<br>", "\r\n", $notes)), $fill, array(224, 224, 224));
		$ctr++;
	}

	$pdf->SetDrawColor(0, 0, 0);
	$pdf->SetFont('Helvetica', 'B', 11);
	$pdf->Cell(95, 10, 'Total Paid API', "T", 0, 'L');
	$pdf->Cell(30, 10, "$" . number_format($report_data->total_cancelled_api, 2), "T", 0, 'C');
	$pdf->Cell(75, 10, '', "T", 1, 'C');

}
/*
if (isset($report_data->kiwisaver_deals)) {
	if(count($report_data->kiwisaver_deals) > 0){

		//Space
		AddLineSpace($pdf);

		//KiwiSaver
		$pdf->SetFont('Helvetica', 'B', 14);
		$pdf->SetFillColor(224, 224, 224);
		$pdf->SetTextColor(0, 0, 0);
		$pdf->Cell(200, 10, 'KIWISAVER', 0, 1, 'C', 'true');
		$pdf->SetFont('Helvetica', 'U', 12);
		$pdf->SetFillColor(0, 0, 0);
		$pdf->SetTextColor(0, 0, 0);

		//$pdf->SetWidths(array(40, 35, 30, 40, 25, 30));
		$pdf->SetWidths(array(55, 35, 45, 30, 35));
		$pdf->Row(array("Insured Name", "Date Applied", "Commission", "GST", "Balance"), false, array(224, 224, 224));

		$pdf->SetFont('Helvetica', '', 9);
		$ctr = 0;
		$rep_data = json_encode($report_data->kiwisaver_deals);
		$report_data->kiwisaver_deals = json_decode($rep_data, true);
		usort($report_data->kiwisaver_deals, "sortFunction");
		foreach ($report_data->kiwisaver_deals as $deal) {
			extract($deal);
			$pdf->SetFillColor(224, 224, 224);
			$fill = (($ctr % 2) === 0) ? true : false;
			//$pdf->Row(array($insured_name, $source_adviser, $date, "$" . number_format($commission, 2), "$" . number_format($gst, 2), "$" . number_format($balance, 2)), $fill, array(224, 224, 224));
			$pdf->Row(array($insured_name, $date, "$" . number_format($commission, 2), "$" . number_format($gst, 2), "$" . number_format($balance, 2)), $fill, array(224, 224, 224));
			$ctr++;
		}

		$pdf->SetDrawColor(0, 0, 0);

		$pdf->SetFont('Helvetica', 'B', 11);
		$pdf->Cell(55, 10, 'Total Payable API', "T", 0, 'L');
		$pdf->Cell(35, 10, '', "T", 0, 'C');
		$pdf->Cell(45, 10, "$" . number_format($report_data->total_kiwisaver_commission, 2), "T", 0, 'C');
		$pdf->Cell(30, 10, "$" . number_format($report_data->total_kiwisaver_gst, 2), "T", 0, 'C');
		$pdf->Cell(35, 10, "$" . number_format($report_data->total_kiwisaver_balance, 2), "T", 1, 'C');
	}
}
*/

$mix = "";
//$mix="$mydateorig"."-$name-"."BCTI".-date("ymd");
//$path="files/".$mix.".pdf";

$path = "files/preview.pdf";
//$pdf->Output($path,'F');
$pdf->Output("I", $report_data->adviser_data->name . ' Deal Tracker Report ' . $period_covered_title);

//OUTPUT 
$file = array();
$file['adviser_id'] = $adviser_id;
$file['link'] = $path;
$file['filename'] = $mix;
$file['report_data'] = json_encode($report_data);
$file['notes'] = json_encode($note_entries);
$file['from'] = $date_from;
$file['pay_date'] = DateTimeToNZEntry($pay_date);
$file['to'] = $until;
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

function AddLineSpace($pdf, $linespace = 10)
{
	$pdf->SetFillColor(255, 255, 255);
	$pdf->SetTextColor(0, 0, 0);
	$pdf->Cell(200, $linespace, '', 0, 1, 'C', 'true');
}
