<?php
date_default_timezone_set('Pacific/Auckland');
ob_start();
session_start();
require("fpdf/mc_table.php");




include_once("libs/api/classes/general.class.php");
include_once("libs/api/controllers/Deal.controller.php");
include_once("libs/api/controllers/User.controller.php");

$dealController = new DealController();
$userController = new UserController();
$generalController = new General();


require("database.php");
/*
session_start();
*/
//post

class PDF extends PDF_MC_Table
{


	function Footer()
	{
		global $fsp_num;
		global $name;

		$this->SetY(-15);
		$this->SetFillColor(0,0,0);
		$this->Rect(5,342,206.5,.5,"FD");
		$this->SetFont('Helvetica','',10);
		$this->SetTextColor(0,0,0);
		$this->Cell(100,10,'Deal Tracker-' . $name,0,0,'L');
		$this->AliasNbPages('{totalPages}');	
		$this->Cell(110,10,'Page '.$this->PageNo() . " of " . "{totalPages}",0,1,'R');
	}

	function Header()
	{	
		$this->SetFillColor(0,0,0);
		$this->Image('logo_vertical.png',93,5,30);
		$this->Rect(5,25,206.5,.5,"FD");
		$this->SetFont('Helvetica','B',18);
		$this->SetTextColor(0,0,0);
		$this->Cell(0,20,'',"0","1","C");
		$this->SetTextColor(0,0,0);
		$this->SetFont('Helvetica','B',10);
		$this->SetFillColor(224,224,224);
	}

	function getPage(){
		return $this->PageNo();
	}


}



//ifs

//FORMULAS end

//convert to 2 decimal number
function convertNum($x){
	return number_format($x, 2, '.', ',');
}

function convertNegNum($x){
	$x=$x*-1;
	return number_format($x, 2, '.', ',');
}

function removeparent($x){
	return preg_replace("/\([^)]+\)/","",$x); // 'ABC ';
}

//retrieving

$tracker_id = $_GET["id"];
//Fetch Adviser Data
$searchadv="SELECT * FROM team_deal_tracker_reports WHERE id='$tracker_id'";
$search=mysqli_query($con,$searchadv) or die('Could not look up user information; ' . mysqli_error($con));
$deal_tracker_report = mysqli_fetch_assoc($search);

$report_data = json_decode($deal_tracker_report["report_data"]);
$team=$tracker_id;				//Adviser name

$date_from=$deal_tracker_report["date_from"];             //Date from
$date_created=$deal_tracker_report["date_created"];	//Invoice Date
$pay_date=$deal_tracker_report["pay_date"];				//Due date
$until=$deal_tracker_report["date_to"];    	//Due date
$created_by = $deal_tracker_report["created_by"];

$report_data->advisers = json_encode($report_data->advisers);
$advisers = json_decode($report_data->advisers, true);
$adviser_ids = implode(",", $advisers);

$team_query = "Select * from teams where id = $team";
$team_result = mysqli_query($con,$team_query) or die('Could not look up user information; ' . mysqli_error($con));
$team_row = mysqli_fetch_array($team_result);

$creator_query = "Select * from users where id = $created_by";
$creator_result = mysqli_query($con,$creator_query) or die('Could not look up user information; ' . mysqli_error($con));
$creator_row = mysqli_fetch_array($creator_result);


$created_by = $userController->getUserWithData($created_by)["full_name"];
//Production Desc
//Test Desc
//$desc=$_POST['desc'];		

$name = $team_row["name"];


$d1 = new DateTime($date_from); // Y-m-d
$d2 = new DateTime($until);

$period_covered_title = $d1->format('d/m/Y') . "-" . $d2->format('d/m/Y');

$dealController = new DealController();



$report_data->period_covered = $period_covered_title;

$pdf = new PDF('P', 'mm', 'Legal');
$x = $pdf->GetX();
$y = $pdf->GetY();

//page 1
$pdf->AddPage('P', 'Legal');


$pdf->SetFillColor(224,224,224);
$pdf->SetFont('Helvetica','B',20);
$pdf->Cell(200,10,'Team ' . $report_data->team->name . ' Deal Tracker Summary',"0","1","C",'true');

$pdf->SetFont('Helvetica','B',15);
$pdf->Cell(42,10,"Period Covered:","0","0","L");
$pdf->SetFont('Helvetica','',15);
$pdf->Cell(53,10,"$period_covered_title","0","0","L");

$pdf->Cell(5,10,'',"0","0","R");
$pdf->SetFont('Helvetica','B',15);
$pdf->Cell(25,10,"Pay Date:","0","0","L");
$pdf->SetFont('Helvetica','',15);
$pdf->Cell(75,10,NZEntryToDateTime($pay_date),"0","1","L");



$pdf->SetFont('Helvetica','B',15);
$pdf->Cell(28,10,"Report By:","0","0","L");
$pdf->SetFont('Helvetica','',15);
$pdf->Cell(67,10,"$created_by","0","0","L");
$pdf->Cell(5,10,'',"0","0","R");
$pdf->SetFont('Helvetica','B',15);
$pdf->Cell(35,10,"Date Created:","0","0","L");
$pdf->SetFont('Helvetica','',15);
$pdf->Cell(65,10,NZEntryToDateTime($date_created),"0","1","L");

if(count($report_data->issued_deals) > 0){
		
	//Space
	AddLineSpace($pdf);

	//Production
	$pdf->SetFont('Helvetica','B',14);
	$pdf->SetFillColor(224,224,224);
	$pdf->SetTextColor(0,0,0);
	$pdf->Cell(200,10,'PRODUCTION', 0, 1,'C','true');

	$pdf->Ln(2);
	$pdf->SetFont('Helvetica','U',12);
	$pdf->SetFillColor(0,0,0);
	$pdf->SetTextColor(0,0,0);

	$pdf->SetWidths(array(40,30,30,40,30,30));
	$pdf->Row(array("Life Insured", "Policy #", "Company", "Adviser","Issue Date", "API"),false,array(224,224,224));
		
	$pdf->Ln(2);
	$pdf->SetFont('Helvetica','',9);
	$ctr=0;
	$rep_data = json_encode($report_data->issued_deals);
	$report_data->issued_deals = json_decode($rep_data, true);

	//usort($report_data->issued_deals, "sortFunction");
	foreach($report_data->issued_deals as $deal){
		extract($deal);
		$pdf->SetFillColor(224,224,224);
		$fill = (($ctr%2)===0) ? true : false;
		$pdf->Row(array($life_insured,$policy_number,$company,$source,NZEntryToDateTime($date),"$" . number_format($api,2)),$fill,array(224,224,224));
		$ctr++;
	}

	$pdf->SetDrawColor(0,0,0);

	$pdf->SetFont('Helvetica','B',11);
	$pdf->Cell(170,10,'Total Payable API', "T", 0,'L');
	$pdf->Cell(30,10,"$" . number_format($report_data->total_issued_api,2),"T", 1,'C');

}

if(count($report_data->cancelled_deals) > 0) {
	//Space
	AddLineSpace($pdf);

	//Clawbacks
	$pdf->SetFont('Helvetica','B',14);
	$pdf->SetFillColor(224,224,224);
	$pdf->SetTextColor(0,0,0);
	$pdf->Cell(200,10,'CLAWBACKS', 0, 1,'C','true');

	$pdf->Ln(2);

	$pdf->SetFont('Helvetica','U',12);
	$pdf->SetFillColor(0,0,0);
	$pdf->SetTextColor(0,0,0);



	$pdf->SetWidths(array(30,30,30,30,20,30, 30));
	$pdf->Row(array("Life Insured", "Policy #", "Company", "Adviser", "Issue Date", "Status", "API"),false,array(224,224,224));

	$pdf->Ln(2);
	$pdf->SetFont('Helvetica','',9);
	$ctr=0;
	$rep_data = json_encode($report_data->cancelled_deals);
	$report_data->cancelled_deals = json_decode($rep_data, true);
	//usort($report_data->cancelled_deals, "sortFunction");
	foreach($report_data->cancelled_deals as $deal){
		extract($deal);
		$pdf->SetFillColor(224,224,224);
		$fill = (($ctr%2)===0) ? true : false;
		$pdf->Row(array($life_insured,$policy_number,$company,$source,NZEntryToDateTime($issued_date),$clawback_status,"$" . number_format($api,2)),$fill,array(224,224,224));
		$ctr++;
	}

	$pdf->SetDrawColor(0,0,0);
	$pdf->SetFont('Helvetica','B',11);
	$pdf->Cell(170,10,'Total Paid API', "T", 0,'L');
	$pdf->Cell(30,10,"$" . number_format($report_data->total_cancelled_api,2),"T", 1,'C');

}

if (isset($report_data->kiwisaver_deals)) {
	if(count($report_data->kiwisaver_deals) > 0) {
			
		//Space
		AddLineSpace($pdf);

		//KiwiSaver
		$pdf->SetFont('Helvetica', 'B', 14);
		$pdf->SetFillColor(224, 224, 224);
		$pdf->SetTextColor(0, 0, 0);
		$pdf->Cell(200, 10, 'KIWISAVER', 0, 1, 'C', 'true');
		/*
			$pdf->SetFont('Helvetica','',9);
			$pdf->SetFillColor(224,224,224);
			$pdf->SetTextColor(0,0,0);
			$pdf->MultiCell(200,10,$search_leads, 0, 1,'C','true');
		*/

		$pdf->SetFont('Helvetica', 'U', 12);
		$pdf->SetFillColor(0, 0, 0);
		$pdf->SetTextColor(0, 0, 0);

		$pdf->SetWidths(array(70, 35, 40, 25, 30));
		$pdf->Row(array("Name", "Source Adviser", "Commission", "GST", "Balance"), false, array(224, 224, 224));

		$pdf->SetFont('Helvetica', '', 9);
		$ctr = 0;
		$rep_data = json_encode($report_data->kiwisaver_deals);
		$report_data->kiwisaver_deals = json_decode($rep_data, true);
		usort($report_data->kiwisaver_deals, "sortFunction");
		foreach ($report_data->kiwisaver_deals as $deal) {
			extract($deal);

			$pdf->SetFillColor(224, 224, 224);
			$fill = (($ctr % 2) === 0) ? true : false;
			$pdf->Row(array($adviser_name, $enrolments, "$" . number_format($commission, 2), "$" . number_format($gst, 2), "$" . number_format($balance, 2)), $fill, array(224, 224, 224));
			$ctr++;
		}

		$pdf->SetDrawColor(0, 0, 0);

		$pdf->SetFont('Helvetica', 'B', 11);
		$pdf->Cell(105, 10, 'Total Payable API', "T", 0, 'L');
		$pdf->Cell(40, 10, "$" . number_format($report_data->total_kiwisaver_commission, 2), "T", 0, 'C');
		$pdf->Cell(25, 10, "$" . number_format($report_data->total_kiwisaver_gst, 2), "T", 0, 'C');
		$pdf->Cell(30, 10, "$" . number_format($report_data->total_kiwisaver_balance, 2), "T", 1, 'C');
	}
}



$mix = "";
//$mix="$mydateorig"."-$name-"."BCTI".-date("ymd");
//$path="files/".$mix.".pdf";

//$pdf->Output();

ob_end_clean();
$pdf->Output("I", 'Team ' . $report_data->team->name . ' Deal Tracker Summary' . $period_covered_title);
//OUTPUT 
echo json_encode($file);
//db add end
//}


function DateTimeToNZEntry($date_submitted){
  return substr($date_submitted,6,4).substr($date_submitted,3,2).substr($date_submitted,0,2);
}

function NZEntryToDateTime($NZEntry){
    return substr($NZEntry,6,2) . "/" . substr($NZEntry,4,2) . "/" . substr($NZEntry, 0, 4);
}

function sortFunction( $a, $b ) {
    return strtotime($a["date"]) - strtotime($b["date"]);
}

function AddLineSpace($pdf, $linespace = 10){
	$pdf->SetFillColor(255,255,255);
	$pdf->SetTextColor(0,0,0);
	$pdf->Cell(200,$linespace,'', 0, 1,'C','true');
}

function CheckTransactionStatus($status){
	$issued = stripos($status, 'Billed Issued Leads') !== false;
	$assigned = stripos($status, 'Billed Assigned Leads') !== false;
	$op = $status;
	if($issued){
		$op = "Billed Issued Leads";
	}
	elseif($assigned){
		$op = "Billed Assigned Leads";
	}

	return $op;
}

//Report Data

function addToDeals($deal,$status){
	$add_to = "";
	switch($status){
		case "Issued":

		break;
	}
}
?>