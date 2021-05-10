<?php
date_default_timezone_set('Pacific/Auckland');
ob_start();
require("fpdf/mc_table.php");

//CONFIGURATION
$fetchalldata = true;
$output_bi_monthly = true;

$restrict_session_check = true;


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
$date_helper = new INDET_DATES_HELPER();
$alphanumeric_helper = new INDET_ALPHANUMERIC_HELPER();

/*
session_start();
*/

class PDF extends PDF_MC_TABLE
{

	function Footer()
	{
		global $fsp_num;
		global $name;
		$this->SetY(-15);
		$this->SetFont('Helvetica','',10);
		$this->SetTextColor(0,0,0);
		$this->Cell(200,10,"",0,0,'C');	

	}

	function getPage(){
		return $this->PageNo();
	}
}



function removespecialchars ($x){
	return preg_replace("/\([^)]+\)/","",$x); // 'ABC ';
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

$months = [];
$bi_months = [];
$weeks = [];
$days = [];
$d3 = clone $d1; //d3 = date we'll use to loop the dates

	$dateExceeded = false;

	if($report_schedule_type=="Regular"){			
		switch ($type){
			case "Weekly":
				while($dateExceeded==false){
				$day = $date_helper->getDay($d3,$d2);
				////echo $bm->from->format('F, d Y') . "-" . $bm->to->format('F, d Y')."<br>";
				$days[] = $day;
				//get next day
				$d3 = clone $day->to;
				$d3 = $d3->modify('+1 day');

				if(!checkIfContinuing($d1,$d2,$d3))
					$dateExceeded = true;
				}
			break;

			case "Bi-Monthly":	
				while($dateExceeded==false){

				$week = $date_helper->getWeek($d3,$d2);
				////echo $bm->from->format('F, d Y') . "-" . $bm->to->format('F, d Y')."<br>";
				$weeks[] = $week;
				//get next day
				$d3 = clone $week->to;
				$d3 = $d3->modify('+1 day');

				if(!checkIfContinuing($d1,$d2,$d3))
					$dateExceeded = true;
				}

				$dateExceeded = false;
				$d3 = $d1;
				while($dateExceeded==false){

					$bm = $date_helper->getBiMonth($d3);
					////echo $bm->from->format('F, d Y') . "-" . $bm->to->format('F, d Y')."<br>";
					$bi_months[] = $bm;
					$d3 = $date_helper->getNextDate($date_helper->getBiMonth($d3));
					if(!checkIfContinuing($d1,$d2,$d3))
						$dateExceeded = true;
				}
			break;
			case "Monthly":
				while($dateExceeded==false){
					
					$week = $date_helper->getWeek($d3,$d2);
					////echo $bm->from->format('F, d Y') . "-" . $bm->to->format('F, d Y')."<br>";
					$weeks[] = $week;
					//get next day
					$d3 = clone $week->to;
					$d3 = $d3->modify('+1 day');

					if(!checkIfContinuing($d1,$d2,$d3))
						$dateExceeded = true;

				}

				$dateExceeded = false;
				$d3 = $d1;

				while($dateExceeded==false){
					$bm = $date_helper->getMonth($d3->format('m'), $d3->format('Y'));
					////echo $bm->from->format('F, d Y') . "-" . $bm->to->format('F, d Y')."<br>";
					$months[] = $bm;
					$d3->modify('+ 1 month');
					if(!checkIfContinuing($d1,$d2,$d3))
						$dateExceeded = true;
				}
			break;
			case "Specified":
				while($dateExceeded==false){
					
					$week = $date_helper->getWeek($d3,$d2);
					////echo $bm->from->format('F, d Y') . "-" . $bm->to->format('F, d Y')."<br>";
					$weeks[] = $week;
					//get next day
					$d3 = clone $week->to;
					$d3 = $d3->modify('+1 day');

					if(!checkIfContinuing($d1,$d2,$d3))
						$dateExceeded = true;

				}

				$dateExceeded = false;
				$d3 = $d1;

				while($dateExceeded==false){
					$bm = $date_helper->getMonth($d3->format('m'), $d3->format('Y'));
					////echo $bm->from->format('F, d Y') . "-" . $bm->to->format('F, d Y')."<br>";
					$months[] = $bm;
					$d3->modify('+ 1 month');
					if(!checkIfContinuing($d1,$d2,$d3))
						$dateExceeded = true;
				}
			break;
			case "Annual":
				while($dateExceeded==false){
					$bm = $date_helper->getMonth($d3->format('m'), $d3->format('Y'));

					////echo $bm->from->format('F, d Y') . "-" . $bm->to->format('F, d Y')."<br>";

					$months[] = $bm;
					$d3->modify('+ 1 month');
					if(!checkIfContinuing($d1,$d2,$d3))
						$dateExceeded = true;
				}
			break;

		}
	}
	else{			
		switch ($type){
			case "Weekly":
				while($dateExceeded==false){

				$day = $date_helper->getDay($d3,$d2);
				////echo $bm->from->format('F, d Y') . "-" . $bm->to->format('F, d Y')."<br>";
				$days[] = $day;
				//get next day
				$d3 = clone $day->to;
				$d3 = $d3->modify('+1 day');

				if(!checkIfContinuing($d1,$d2,$d3))
					$dateExceeded = true;
				}
			break;

			case "Bi-Monthly":	
				while($dateExceeded==false){

				$week = $date_helper->getWeek($d3,$d2);
				////echo $bm->from->format('F, d Y') . "-" . $bm->to->format('F, d Y')."<br>";
				$weeks[] = $week;
				//get next day
				$d3 = clone $week->to;
				$d3 = $d3->modify('+1 day');

				if(!checkIfContinuing($d1,$d2,$d3))
					$dateExceeded = true;
				}

				$dateExceeded = false;
				$d3 = $d1;
				while($dateExceeded==false){

					$bm = $date_helper->getBiMonth($d3);
					////echo $bm->from->format('F, d Y') . "-" . $bm->to->format('F, d Y')."<br>";
					$bi_months[] = $bm;
					$d3 = $date_helper->getNextDate($date_helper->getBiMonth($d3));
					if(!checkIfContinuing($d1,$d2,$d3))
						$dateExceeded = true;
				}
			break;
			case "Monthly":
				while($dateExceeded==false){
					
					$week = $date_helper->getWeek($d3,$d2);
					////echo $bm->from->format('F, d Y') . "-" . $bm->to->format('F, d Y')."<br>";
					$weeks[] = $week;
					//get next day
					$d3 = clone $week->to;
					$d3 = $d3->modify('+1 day');

					if(!checkIfContinuing($d1,$d2,$d3))
						$dateExceeded = true;

				}

				$dateExceeded = false;
				$d3 = clone $d1;
				while($dateExceeded==false){
					$thirdMonth = ($d3->format("Y") % 3 === 0) ? true : false;

					$bm = $date_helper->getSumitMonth($d3, $d2, $thirdMonth);
					
					////echo $bm->from->format('F, d Y') . "-" . $bm->to->format('F, d Y')."<br>";
					$months[] = $bm;
					$d3->modify('+ 1 day');

					if(!checkIfContinuing($d1,$d2,$d3))
						$dateExceeded = true;
				}
			break;
			case "Specified":
				while($dateExceeded==false){
					
					$week = $date_helper->getWeek($d3,$d2);
					////echo $bm->from->format('F, d Y') . "-" . $bm->to->format('F, d Y')."<br>";
					$weeks[] = $week;
					//get next day
					$d3 = clone $week->to;
					$d3 = $d3->modify('+1 day');

					if(!checkIfContinuing($d1,$d2,$d3))
						$dateExceeded = true;

				}

				$dateExceeded = false;
				$d3 = $d1;

				while($dateExceeded==false){
					$bm = $date_helper->getMonth($d3->format('m'), $d3->format('Y'));
					////echo $bm->from->format('F, d Y') . "-" . $bm->to->format('F, d Y')."<br>";
					$months[] = $bm;
					$d3->modify('+ 1 month');
					if(!checkIfContinuing($d1,$d2,$d3))
						$dateExceeded = true;
				}
			break;
			case "Annual":
				while($dateExceeded==false){
					$is_third = ((count($months) + 1) % 3 == 0) ? true : false;
					$d_annual = new DateTime($d3->format('Ymd'));
					$sm = $date_helper->getSumitMonth($d_annual, $is_third);
					////echo $bm->from->format('F, d Y') . "-" . $bm->to->format('F, d Y')."<br>";
					$months[] = $sm;

					$d3 = clone $sm->to;
					$d3 = $d3->modify('+1 day');

					if(!checkIfContinuing($d1,$d2,$d3))
						$dateExceeded = true;
				}
			break;

		}
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
$leads_generated = array();

include('output3_non_quarterly.php');

//For Production
$preview = "leadgen_report_" . md5(uniqid());
$path="files/$preview" . "_preview.pdf";
$pdf->Output($path,'F');
        //$pdf->Output("I",  "EliteInsure Magazine .pdf");

//For Testing
//$pdf->Output();

//OUTPUT 
$file=array();
$file["debug"] = $dealController->getLeadGeneratorIssuedAndCancelledDealsInPeriod($leadgen_id, $date_from, $until);
$file['number']= $leadgen_refnum;
$file['link']= $path;
$file['entrydate']=$invoice_date_final;
$file['leadgen_id'] = $leadgen_id;
$file['totalclients'] = $totalclients;
$file['totalissuedclients'] = $totalissuedclients;
$file['totalissuedpremiums'] = $totalissuedpremiums;
$file['from'] = $date_from;
$file['to'] = $until;
$file['type'] = $_POST['type'];
$file['schedule_type'] = $report_schedule_type;
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
