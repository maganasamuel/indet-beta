<?php
date_default_timezone_set('Pacific/Auckland');
ob_start();
require("fpdf/mc_table.php");

//CONFIGURATION
$fetchalldata = false;
$output_bi_monthly = true;


require("database.php");
/*
session_start();
*/
//post

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

class PDF extends PDF_MC_TABLE
{
	function Footer()
	{
		global $fsp_num;
		global $name;
		$this->SetY(-15);
		$this->SetFont('Helvetica', '', 10);
		$this->SetTextColor(0, 0, 0);
		$this->Cell(200, 10, "", 0, 0, 'C');
	}

	function getPage()
	{
		return $this->PageNo();
	}
}

//retrieving
$report_id = $_GET['id'];
$lead_by = "";
$searchadv = "SELECT *, type as r_type FROM `lead_gen_report` where `id` =" . $report_id;
$search = mysqli_query($con, $searchadv) or die('Could not look up user information; ' . mysqli_error($con));
$report_data = mysqli_fetch_assoc($search);
extract($report_data);

//Get Lead Gen Name
$leadgen_query = "SELECT * from leadgen_tbl where id = " . $lead_gen_id;
$leadgen_result = mysqli_query($con, $leadgen_query);
$leadgen_fetch = mysqli_fetch_assoc($leadgen_result);
$leadgen_name = $leadgen_fetch['name'];
$lead_by = $leadgen_fetch['type'];

//var_dump($_POST);

$until = $date_to;
//GET BI MONTHLY DATA
$d_from = $date_from;
$d_to = $until;

$d1 = new DateTime($d_from); // Y-m-d
$d2 = new DateTime($d_to);
$period_covered_title = $d1->format('d/m/Y') . "-" . $d2->format('d/m/Y');
$months = [];
$bi_months = [];
$weeks = [];

$d3 = $d1; //d3 = date we'll use to loop the dates
$week_offset = 0;
if ($type == "Quarterly") {

	$year = $d1->format('Y');
	$first_month = $d1->format('m');
	$first_month = $d1->format('m');
	switch ($first_month) {
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

	$months[] = $date_helper->getQuarterMonth($d1->format('Ymd'), $d2->format('Ymd'), 1);
	$next_date = clone $months[0]->to;
	$next_date->modify('+ 1 day');
	$next_date = $next_date->format('Ymd');
	//echo "Next Date: $next_date";
	$months[] = $date_helper->getQuarterMonth($next_date, $d2->format('Ymd'), 2);
	$next_date = clone $months[1]->to;
	$next_date->modify('+ 1 day');

	$next_date = $next_date->format('Ymd');
	//echo "Next Date: $next_date";
	$months[] = $date_helper->getQuarterMonth($next_date, $d2->format('Ymd'), 3);

	$months[0]->month_index = 1;
	$months[1]->month_index = 2;
	$months[2]->month_index = 3;

	$dateExceeded = false;
	while ($dateExceeded == false) {
		$week = $date_helper->getWeek($d3, $d2);
		////echo $bm->from->format('F, d Y') . "-" . $bm->to->format('F, d Y')."<br>";
		$bi_months[] = $week;
		$weeks[] = $week;
		//$date_helper->get next day
		$d3 = clone $week->to;
		$d3 = $d3->modify('+1 day');

		if (!checkIfContinuing($d1, $d2, $d3))
			$dateExceeded = true;
	}
} else {
	$dateExceeded = false;

	if ($schedule_type == "Regular") {
		switch ($type) {
			case "Weekly":
				while ($dateExceeded == false) {
					$day = $date_helper->getDay($d3, $d2);
					////echo $bm->from->format('F, d Y') . "-" . $bm->to->format('F, d Y')."<br>";
					$days[] = $day;
					//$date_helper->get next day
					$d3 = clone $day->to;
					$d3 = $d3->modify('+1 day');

					if (!checkIfContinuing($d1, $d2, $d3))
						$dateExceeded = true;
				}
				break;

			case "Bi-Monthly":
				while ($dateExceeded == false) {

					$week = $date_helper->getWeek($d3, $d2);
					////echo $bm->from->format('F, d Y') . "-" . $bm->to->format('F, d Y')."<br>";
					$weeks[] = $week;
					//$date_helper->get next day
					$d3 = clone $week->to;
					$d3 = $d3->modify('+1 day');

					if (!checkIfContinuing($d1, $d2, $d3))
						$dateExceeded = true;
				}

				$dateExceeded = false;
				$d3 = $d1;
				while ($dateExceeded == false) {

					$bm = $date_helper->getBiMonth($d3);
					////echo $bm->from->format('F, d Y') . "-" . $bm->to->format('F, d Y')."<br>";
					$bi_months[] = $bm;
					$d3 = $date_helper->getNextDate($date_helper->getBiMonth($d3));
					if (!checkIfContinuing($d1, $d2, $d3))
						$dateExceeded = true;
				}
				break;
			case "Monthly":
				while ($dateExceeded == false) {

					$week = $date_helper->getWeek($d3, $d2);
					////echo $bm->from->format('F, d Y') . "-" . $bm->to->format('F, d Y')."<br>";
					$weeks[] = $week;
					//$date_helper->get next day
					$d3 = clone $week->to;
					$d3 = $d3->modify('+1 day');

					if (!checkIfContinuing($d1, $d2, $d3))
						$dateExceeded = true;
				}

				$dateExceeded = false;
				$d3 = $d1;

				while ($dateExceeded == false) {
					$bm = $date_helper->getMonth($d3->format('m'), $d3->format('Y'));
					////echo $bm->from->format('F, d Y') . "-" . $bm->to->format('F, d Y')."<br>";
					$months[] = $bm;
					$d3->modify('+ 1 month');
					if (!checkIfContinuing($d1, $d2, $d3))
						$dateExceeded = true;
				}
				break;
			case "Specified":
				while ($dateExceeded == false) {

					$week = $date_helper->getWeek($d3, $d2);
					////echo $bm->from->format('F, d Y') . "-" . $bm->to->format('F, d Y')."<br>";
					$weeks[] = $week;
					//$date_helper->get next day
					$d3 = clone $week->to;
					$d3 = $d3->modify('+1 day');

					if (!checkIfContinuing($d1, $d2, $d3))
						$dateExceeded = true;
				}

				$dateExceeded = false;
				$d3 = $d1;

				while ($dateExceeded == false) {
					$bm = $date_helper->getMonth($d3->format('m'), $d3->format('Y'));
					////echo $bm->from->format('F, d Y') . "-" . $bm->to->format('F, d Y')."<br>";
					$months[] = $bm;
					$d3->modify('+ 1 month');
					if (!checkIfContinuing($d1, $d2, $d3))
						$dateExceeded = true;
				}
				break;
			case "Annual":
				while ($dateExceeded == false) {
					$bm = $date_helper->getMonth($d3->format('m'), $d3->format('Y'));

					////echo $bm->from->format('F, d Y') . "-" . $bm->to->format('F, d Y')."<br>";

					$months[] = $bm;
					$d3->modify('+ 1 month');
					if (!checkIfContinuing($d1, $d2, $d3))
						$dateExceeded = true;
				}
				break;
		}
	} else {
		switch ($type) {
			case "Weekly":
				while ($dateExceeded == false) {

					$day = $date_helper->getDay($d3, $d2);
					////echo $bm->from->format('F, d Y') . "-" . $bm->to->format('F, d Y')."<br>";
					$days[] = $day;
					//$date_helper->get next day
					$d3 = clone $day->to;
					$d3 = $d3->modify('+1 day');

					if (!checkIfContinuing($d1, $d2, $d3))
						$dateExceeded = true;
				}
				break;

			case "Bi-Monthly":
				while ($dateExceeded == false) {

					$week = $date_helper->getWeek($d3, $d2);
					////echo $bm->from->format('F, d Y') . "-" . $bm->to->format('F, d Y')."<br>";
					$weeks[] = $week;
					//$date_helper->get next day
					$d3 = clone $week->to;
					$d3 = $d3->modify('+1 day');

					if (!checkIfContinuing($d1, $d2, $d3))
						$dateExceeded = true;
				}

				$dateExceeded = false;
				$d3 = $d1;
				while ($dateExceeded == false) {

					$bm = $date_helper->getBiMonth($d3);
					////echo $bm->from->format('F, d Y') . "-" . $bm->to->format('F, d Y')."<br>";
					$bi_months[] = $bm;
					$d3 = $date_helper->getNextDate($date_helper->getBiMonth($d3));
					if (!checkIfContinuing($d1, $d2, $d3))
						$dateExceeded = true;
				}
				break;
			case "Monthly":
				while ($dateExceeded == false) {

					$week = $date_helper->getWeek($d3, $d2);
					////echo $bm->from->format('F, d Y') . "-" . $bm->to->format('F, d Y')."<br>";
					$weeks[] = $week;
					//$date_helper->get next day
					$d3 = clone $week->to;
					$d3 = $d3->modify('+1 day');

					if (!checkIfContinuing($d1, $d2, $d3))
						$dateExceeded = true;
				}

				$dateExceeded = false;
				$d3 = clone $d1;
				while ($dateExceeded == false) {
					$thirdMonth = ($d3->format("Y") % 3 === 0) ? true : false;

					$bm = $date_helper->getSumitMonth($d3, $d2, $thirdMonth);

					////echo $bm->from->format('F, d Y') . "-" . $bm->to->format('F, d Y')."<br>";
					$months[] = $bm;
					$d3->modify('+ 1 day');

					if (!checkIfContinuing($d1, $d2, $d3))
						$dateExceeded = true;
				}
				break;
			case "Specified":
				while ($dateExceeded == false) {

					$week = $date_helper->getWeek($d3, $d2);
					////echo $bm->from->format('F, d Y') . "-" . $bm->to->format('F, d Y')."<br>";
					$weeks[] = $week;
					//$date_helper->get next day
					$d3 = clone $week->to;
					$d3 = $d3->modify('+1 day');

					if (!checkIfContinuing($d1, $d2, $d3))
						$dateExceeded = true;
				}

				$dateExceeded = false;
				$d3 = $d1;

				while ($dateExceeded == false) {
					$bm = $date_helper->getMonth($d3->format('m'), $d3->format('Y'));
					////echo $bm->from->format('F, d Y') . "-" . $bm->to->format('F, d Y')."<br>";
					$months[] = $bm;
					$d3->modify('+ 1 month');
					if (!checkIfContinuing($d1, $d2, $d3))
						$dateExceeded = true;
				}
				break;
			case "Annual":
				while ($dateExceeded == false) {
					$is_third = ((count($months) + 1) % 3 == 0) ? true : false;
					$d_annual = new DateTime($d3->format('Ymd'));
					$sm = $date_helper->getSumitMonth($d_annual, $is_third);
					////echo $bm->from->format('F, d Y') . "-" . $bm->to->format('F, d Y')."<br>";
					$months[] = $sm;

					$d3 = clone $sm->to;
					$d3 = $d3->modify('+1 day');

					if (!checkIfContinuing($d1, $d2, $d3))
						$dateExceeded = true;
				}
				break;
		}
	}
}

function checkIfContinuing($from, $to, $next_date)
{
	return (($next_date >= $from) && ($next_date <= $to));
}

$leadgen_id = $lead_gen_id;

debuggingLog("LEAD GENERATOR", $leadgen_name);

$period_covered =  substr($date_from, 6, 2) . "/" . substr($date_from, 4, 2) . "/" . substr($date_from, 0, 4) . "-"  . substr($until, 6, 2) . "/" . substr($until, 4, 2) . "/" . substr($until, 0, 4);

$leadgen_refnum = $reference_number;

$chartHelper = new ChartHelper();

$leads_generated = array();

if ($type == "Quarterly") {
	include('leadgen_report_quarterly.php');
} else {
	include('leadgen_report_non_quarterly.php');
}

//For Testing
$pdf->Output("I", "$leadgen_name " . $r_type . ' Performance Report ' . $period_covered_title . ".pdf");

function debuggingLog($header, $variable)
{
	$isDebuggerActive = false;

	if (!$isDebuggerActive)
		return;

	$op = "<br>";
	$op .=  $header;
	echo $op . "<hr>" . "<pre>";
	var_dump($variable);
	echo "</pre>" . "<hr>";
}


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