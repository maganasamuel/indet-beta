<?php
date_default_timezone_set('Pacific/Auckland');
ob_start();
require("fpdf/mc_table.php");

//CONFIGURATION
$fetchalldata = false;
$output_bi_monthly = true;


require("database.php");
require_once "libs/indet_dates_helper.php";
require_once "libs/indet_alphanumeric_helper.php";
require_once 'libs/Chart.helper.php';
include_once("libs/api/classes/general.class.php");
include_once("libs/api/controllers/Deal.controller.php");
include_once("libs/api/controllers/Client.controller.php");

$dealController = new DealController();
$clientController = new ClientController();
$generalController = new General();
$dates_helper = new INDET_DATES_HELPER();
$alphanumeric_helper = new INDET_ALPHANUMERIC_HELPER();

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
		$this->SetFont('Helvetica','',10);
		$this->SetTextColor(0,0,0);
		$this->Cell(200,10,"",0,0,'C');	
		$this->AliasNbPages('{totalPages}');	
		$this->Cell(0,10,'Page '.$this->PageNo() . " of " . "{totalPages}",0,1,'R');

	}

	function getPage(){
		return $this->PageNo();
	}
}

//retrieving
extract($_POST);

//Get Lead Gen Name
$leadgen_query = "SELECT * from leadgen_tbl where id='" . $leadgen_id . "'";
$leadgen_result = mysqli_query($con,$leadgen_query);
$leadgen_fetch = mysqli_fetch_assoc($leadgen_result);
$leadgen_name = $leadgen_fetch['name'];		
$lead_by = $leadgen_fetch['type'];

//GET BI MONTHLY DATA
$d_from = substr($date_from,6,4). "-" . substr($date_from,3,2). "-" . substr($date_from,0,2);
$d_to =substr($until,6,4). "-" . substr($until,3,2). "-" . substr($until,0,2);

$d1 = new DateTime($d_from); // Y-m-d
$d2 = new DateTime($d_to);
$period_covered_title = $d1->format('d/m/Y') . "-" . $d2->format('d/m/Y');
$bi_months = [];
$months = [];
$weeks = [];
$d3 = clone $d1; //d3 = date we'll use to loop the dates
$week_offset = 0;
$year = $d1->format('Y');
$first_month = $d1->format('m');

switch($first_month){
	case "12":	
	case "01":
		$period_covered_title = "First Quarter";
		break;
	case "04":
		$period_covered_title = "Second Quarter";
		$week_offset = 13;
	break;
	case "07":
		$period_covered_title = "Third Quarter";
		$week_offset = 26;
	break;
	case "09":
	case "10":
		$period_covered_title = "Fourth Quarter";
		$week_offset = 39;
	break;
}			

$months[] = $dates_helper->getQuarterMonth($d1->format('Ymd'), $d2->format('Ymd'), 1);	
$next_date = clone $months[0]->to;
$next_date->modify('+ 1 day');
$next_date = $next_date->format('Ymd');
//echo "Next Date: $next_date";
$months[] = $dates_helper->getQuarterMonth($next_date, $d2->format('Ymd'), 2);	
$next_date = clone $months[1]->to;
$next_date->modify('+ 1 day');

$next_date = $next_date->format('Ymd');
//echo "Next Date: $next_date";
$months[] = $dates_helper->getQuarterMonth($next_date, $d2->format('Ymd'), 3);	

$months[0]->month_index = 1;
$months[1]->month_index = 2; 
$months[2]->month_index = 3; 

$period_covered_title .= " of " . $year;
$dateExceeded = false;
while($dateExceeded==false){
	$week = $dates_helper->getWeek($d3,$d2);
	////echo $bm->from->format('F, d Y') . "-" . $bm->to->format('F, d Y')."<br>";
	$bi_months[] = $week;
	$weeks[] = $week;
	//get next day
	$d3 = clone $week->to;
	$d3 = $d3->modify('+1 day');

	if(!checkIfContinuing($d1,$d2,$d3))
		$dateExceeded = true;
}

function checkIfContinuing($from,$to,$next_date){
  return (($next_date >= $from) && ($next_date <= $to));
}


$period_covered =  substr($date_from,0,2) . "/" . substr($date_from,3,2) . "/" .substr($date_from,6,4) . "-"  . substr($until,0,2) . "/" .substr($until,3,2). "/". substr($until,6,4) ;
$date_from=substr($date_from,6,4).substr($date_from,3,2).substr($date_from,0,2);
$until=substr($until,6,4).substr($until,3,2).substr($until,0,2);
$date_now = substr($date_now,6,4).substr($date_now,3,2).substr($date_now,0,2);
//CREATE REFERENCE NUMBER

$refnum_query = "SELECT * FROM lead_gen_report WHERE reference_number LIKE '%$date_now' ORDER BY reference_number DESC LIMIT 1";
//echo $refnum_query;
$refnum_result = mysqli_query($con,$refnum_query) or die('Could not look up user information; ' . mysqli_error($con));
$refnum_count = mysqli_fetch_assoc($refnum_result);
$refnum_count = $refnum_count['reference_number'];
$latest_number = substr($refnum_count, 3, 4);
$latest_number += 1;

$leadgen_refnum = "LG-" .  $alphanumeric_helper->convertToFourDigits($latest_number) . $date_now;

$chartHelper = new ChartHelper();

include("quarterly_preview_leadgen_report.php");

$preview = "leadgen_report_" . md5(uniqid());
$path="files/$preview" . "_preview.pdf";
$pdf->Output($path,'F');

//For Testing
//$pdf->Output();

ob_end_clean();
//OUTPUT 
$file=array();
$file['number']=$leadgen_refnum;
$file['link']=$path;
$file['entrydate']=$invoice_date_final;
$file['leadgen_id'] = $leadgen_id;
$file['totalclients'] = $totalclients;
$file['totalissuedclients'] = $totalissuedclients;
$file['totalissuedpremiums'] = $totalissuedpremiums;
$file['from'] = $date_from;
$file['type'] = $_POST['type'];
$file['schedule_type'] = $report_schedule_type;
$file['to'] = $until;
$file['required_leads'] = $required_leads;
$file['required_leads_type'] = $required_leads_type;
echo json_encode($file);
//db add end
//}

function GetCancellationRate($cancellation_api, $issued_api)
{
	$cancellation_rate = 0;

	if ($cancellation_api > 0 && $issued_api > 0) {
		$cancellation_rate = number_format(($cancellation_api / $issued_api) * 100);
	} elseif ($cancellation_api > 0 && $issued_api == 0) {
		$cancellation_rate = 100;
	}

	return $cancellation_rate;
}

function GetProficiency($issued_api, $total_issued_clients)
{
	$proficiency = 0;

	if ($issued_api > 0 && $total_issued_clients > 0) {
		$proficiency = number_format(($issued_api / $total_issued_clients), 2, '.', '');
	}

	return $proficiency;
}

function Currency($value, $currency = "$"){
	return $currency . number_format($value,2);
}

function GetPercentage($num1, $num2){
	
	$op = 0;

	if ($num1 > 0 && $num2 > 0) {
		$op = number_format(($num1 / $num2) * 100, 2);
	} elseif ($num1 > 0 && $num2 == 0) {
		$op = 100;
	} 

	return $op;
}

?>

