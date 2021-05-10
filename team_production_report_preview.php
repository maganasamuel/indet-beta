<?php
date_default_timezone_set('Pacific/Auckland');
ob_start();
require("fpdf/mc_table.php");






require("database.php");
require_once "libs/indet_dates_helper.php";
require_once "libs/indet_alphanumeric_helper.php";
require_once 'libs/Chart.helper.php';
include_once("libs/api/classes/general.class.php");
include_once("libs/api/controllers/Deal.controller.php");

$dealController = new DealController();
$generalController = new General();
$date_helper = new INDET_DATES_HELPER();
$alphanumeric_helper = new INDET_ALPHANUMERIC_HELPER();
/*
session_start();
*/
//post

class PDF extends PDF_MC_TABLE
{


	function Footer()
	{
		global $fsp_num;
		global $name;
		$this->SetY(-15);
		$this->SetFont('Helvetica', '', 10);
		$this->SetTextColor(0, 0, 0);
		$this->Cell(100, 10, 'Team Production Report ' . '' . ' ' . preg_replace("/\([^)]+\)/", "", ''), 0, 0, 'L');
		$this->AliasNbPages('{totalPages}');
		$this->Cell(110, 10, 'Page ' . $this->PageNo() . " of " . "{totalPages}", 0, 1, 'R');
	}


	function Header()
	{
		$this->SetFillColor(224, 224, 224);
		$this->Image('logo.png', 10, 10, -160);
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
$date_from = isset($_POST['date_from']) ? $_POST['date_from'] : '';			//Date from
$date_created = isset($_POST['date_created']) ? $_POST['date_created'] : '';	//Invoice Date
$due_date = isset($_POST['due_date']) ? $_POST['due_date'] : '';				//Due date
$until = isset($_POST['until']) ? $_POST['until'] : '';						//Date until
$report_schedule_type = $_POST['report_schedule_type'];
//Production Desc
$desc = json_decode($_POST['desc']);
//Test Desc
//$desc=$_POST['desc'];		
$date_created = date("d/m/Y");
$statementweek = date("d/m/Y");											//Statement Week

//GET BI MONTHLY DATA
$d_from = substr($date_from, 6, 4) . "-" . substr($date_from, 3, 2) . "-" . substr($date_from, 0, 2);
$d_to = substr($until, 6, 4) . "-" . substr($until, 3, 2) . "-" . substr($until, 0, 2);

$date_from = substr($date_from, 6, 4) . substr($date_from, 3, 2) . substr($date_from, 0, 2);
$until = substr($until, 6, 4) . substr($until, 3, 2) . substr($until, 0, 2);

$d1 = new DateTime($d_from); // Y-m-d
$d2 = new DateTime($d_to);
$period_covered_title = $d1->format('d/m/Y') . "-" . $d2->format('d/m/Y');

$months = [];
$bi_months = [];
$weeks = [];
$days = [];
$d3 = $d1; //d3 = date we'll use to loop the dates

$period_covered_title = $d1->format('d/m/Y') . "-" . $d2->format('d/m/Y');

$dateExceeded = false;
switch ($_POST['report_type']) {
	case "Weekly":
		while ($dateExceeded == false) {

			$day = getDay($d3, $d2);
			////echo $bm->from->format('F, d Y') . "-" . $bm->to->format('F, d Y')."<br>";
			$days[] = $day;
			//get next day
			$d3 = clone $day->to;
			$d3 = $d3->modify('+1 day');

			if (!checkIfContinuing($d1, $d2, $d3))
				$dateExceeded = true;
		}
		break;

	case "Bi-Monthly":
		while ($dateExceeded == false) {

			$week = getWeek($d3, $d2);
			////echo $bm->from->format('F, d Y') . "-" . $bm->to->format('F, d Y')."<br>";
			$weeks[] = $week;
			//get next day
			$d3 = clone $week->to;
			$d3 = $d3->modify('+1 day');

			if (!checkIfContinuing($d1, $d2, $d3))
				$dateExceeded = true;
		}

		$dateExceeded = false;
		$d3 = $d1;
		while ($dateExceeded == false) {

			$bm = getBiMonth($d3);
			////echo $bm->from->format('F, d Y') . "-" . $bm->to->format('F, d Y')."<br>";
			$bi_months[] = $bm;
			$d3 = getNextDate(getBiMonth($d3));
			if (!checkIfContinuing($d1, $d2, $d3))
				$dateExceeded = true;
		}
		break;
	case "Monthly":
		if ($report_schedule_type == "Regular") {
			while ($dateExceeded == false) {
				$week = getWeek($d3, $d2);
				////echo $bm->from->format('F, d Y') . "-" . $bm->to->format('F, d Y')."<br>";
				$weeks[] = $week;
				//get next day
				$d3 = clone $week->to;
				$d3 = $d3->modify('+1 day');

				if (!checkIfContinuing($d1, $d2, $d3))
					$dateExceeded = true;
			}

			$dateExceeded = false;
			$d3 = $d1;

			while ($dateExceeded == false) {
				$bm = getMonth($d3->format('m'), $d3->format('Y'));
				////echo $bm->from->format('F, d Y') . "-" . $bm->to->format('F, d Y')."<br>";
				$months[] = $bm;
				$d3->modify('+ 1 month');
				if (!checkIfContinuing($d1, $d2, $d3))
					$dateExceeded = true;
			}
		} else {
			while ($dateExceeded == false) {
				$week = getWeek($d3, $d2);
				////echo $bm->from->format('F, d Y') . "-" . $bm->to->format('F, d Y')."<br>";
				$weeks[] = $week;
				//get next day
				$d3 = clone $week->to;
				$d3 = $d3->modify('+1 day');

				if (!checkIfContinuing($d1, $d2, $d3))
					$dateExceeded = true;
			}

			$dateExceeded = false;
			$d3 = clone $d1;

			while ($dateExceeded == false) {
				$thirdMonth = ($d3->format("Y") % 3 === 0) ? true : false;
				$bm = getSumitMonth($d3, $d2, $thirdMonth);


				$months[] = $bm;
				$d3->modify('+ 1 day');

				if (!checkIfContinuing($d1, $d2, $d3))
					$dateExceeded = true;
			}
		}
		break;
	case "Specified":
		while ($dateExceeded == false) {

			$week = getWeek($d3, $d2);
			////echo $bm->from->format('F, d Y') . "-" . $bm->to->format('F, d Y')."<br>";
			$weeks[] = $week;
			//get next day
			$d3 = clone $week->to;
			$d3 = $d3->modify('+1 day');

			if (!checkIfContinuing($d1, $d2, $d3))
				$dateExceeded = true;
		}

		$dateExceeded = false;
		$d3 = $d1;

		while ($dateExceeded == false) {
			$bm = getMonth($d3->format('m'), $d3->format('Y'));
			////echo $bm->from->format('F, d Y') . "-" . $bm->to->format('F, d Y')."<br>";
			$months[] = $bm;
			$d3->modify('+ 1 month');
			if (!checkIfContinuing($d1, $d2, $d3))
				$dateExceeded = true;
		}
		break;
	case "Annual":
		if ($report_schedule_type == "Regular") {
			while ($dateExceeded == false) {
				$bm = getMonth($d3->format('m'), $d3->format('Y'));
				////echo $bm->from->format('F, d Y') . "-" . $bm->to->format('F, d Y')."<br>";
				$months[] = $bm;
				$d3->modify('+ 1 month');
				if (!checkIfContinuing($d1, $d2, $d3))
					$dateExceeded = true;
			}
		} else {
			while ($dateExceeded == false) {
				$is_third = ((count($months) + 1) % 3 == 0) ? true : false;
				$d_annual = new DateTime($d3->format('Ymd'));
				$sm = getSumitMonth($d_annual, $is_third);
				////echo $bm->from->format('F, d Y') . "-" . $bm->to->format('F, d Y')."<br>";
				$months[] = $sm;

				$d3 = clone $sm->to;
				$d3 = $d3->modify('+1 day');

				if (!checkIfContinuing($d1, $d2, $d3))
					$dateExceeded = true;
			}
		}
		break;

	case "Quarterly":
		$week_offset = 0;
		$year = $d1->format('Y');
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

		$months[] = getQuarterMonth($d1->format('Ymd'), $d2->format('Ymd'), 1);
		$next_date = clone $months[0]->to;
		$next_date->modify('+ 1 day');
		$next_date = $next_date->format('Ymd');
		//echo "Next Date: $next_date";
		$months[] = getQuarterMonth($next_date, $d2->format('Ymd'), 2);
		$next_date = clone $months[1]->to;
		$next_date->modify('+ 1 day');

		$next_date = $next_date->format('Ymd');
		//echo "Next Date: $next_date";
		$months[] = getQuarterMonth($next_date, $d2->format('Ymd'), 3);

		$months[0]->month_index = 1;
		$months[1]->month_index = 2;
		$months[2]->month_index = 3;


		$dateExceeded = false;
		while ($dateExceeded == false) {
			$week = getWeek($d3, $d2);
			////echo $bm->from->format('F, d Y') . "-" . $bm->to->format('F, d Y')."<br>";
			$bi_months[] = $week;
			$weeks[] = $week;
			//get next day
			$d3 = clone $week->to;
			$d3 = $d3->modify('+1 day');

			if (!checkIfContinuing($d1, $d2, $d3))
				$dateExceeded = true;
		}
		break;
}

if ($_POST['report_type'] == "Quarterly") {
	$period_covered_title .= " of " . $d2->format('Y');
}

function getMonth($month, $year)
{
	$output = new stdClass();
	$dateString = "$year-$month-01";
	$day1 = date("Ymd", strtotime($dateString));
	$day2 = date("Ymt", strtotime($dateString));
	$output->from = new DateTime($day1);
	$output->to = new DateTime($day2);
	$output->string = $dateString;
	return clone $output;
}

function getSumitMonth($day, $thirdMonth = false)
{
	$output = new stdClass();

	$daysToAdd = 27;

	if ($thirdMonth)
		$daysToAdd += 7;

	$day1 = clone $day;
	$day2 = clone $day;
	$output->from = $day1;

	$day2 = $day2->modify('+ ' . $daysToAdd . ' days');

	$last_day = clone $day2;
	if ($last_day->format("m") == 12 && $last_day->format("d") <= 24) {
		$day2 = $day2->modify('+ 7 days');
	}

	$output->to = $day2;

	return clone $output;
}

function getWeek($day, $d2)
{
	$output = new stdClass();
	$day1 = clone $day;
	$day2 = clone $day;
	$output->from = $day1;
	$day2 = $day2->modify('+ 6 days');
	if ($d2 > $day2)
		$output->to = $day2;
	else
		$output->to = $d2;

	return clone $output;
}


function getDay($day, $d2)
{
	$output = new stdClass();
	$day1 = clone $day;
	$output->from = $day1;
	$output->to = $day1;

	return clone $output;
}

function getBiMonth($day)
{
	$output = new stdClass();
	$day1 = clone $day;
	$day2 = clone $day;
	if ($day->format('d') <= 15) {
		$output->note = "First half";
		$output->from = $day1->modify('first day of this month');
		$to = $day2->modify('first day of this month');
		$output->to = $to->modify('+ 14 days');
	} else {
		$output->note = "Second half";
		$output->from = $day1->modify('first day of this month');
		$output->from = $output->from->modify('+ 15 days');
		$output->to = $day2->modify('last day of this month');
	}
	//echo "<br><br><br>Output:" . $output->from->format('Ymd') . "-" . $output->to->format('Ymd') . "<br><br><br>";
	return clone $output;
}

function getFlexibleMonth($month, $year, $d_from, $d_until)
{
	$output = new stdClass();
	$dateString = "$year-$month-01";
	$day1 = date("Y-m-d", strtotime($dateString));
	$day2 = date("Y-m-t", strtotime($dateString));
	$d_1 = new DateTime($day1);
	$d_2 = new DateTime($day2);
	//var_dump($d_from);

	if ($d_from < $d_1)
		$output->from = clone $d_1;
	else
		$output->from = clone $d_from;

	if ($d_until > $d_2)
		$output->to = clone $d_2;
	else
		$output->to = clone $d_until;

	return clone $output;
}

function getQuarterMonth($d_from, $d_until, $month_index)
{
	$output = new stdClass();
	$day_offset = 27;

	$day1 = date("Y-m-d", strtotime($d_from));
	$day2 = date("Y-m-d", strtotime($d_until));
	$d_1 = new DateTime($day1);
	$d_2 = clone $d_1;
	$d_2->modify('+' . $day_offset . ' days');
	$d_3 = new DateTime($day2);
	//var_dump($d_from);

	//echo "First Day: " . $d_from;
	$output->from = clone $d_1;

	if ($month_index == 3)
		$output->to = clone $d_3;
	else
		$output->to = clone $d_2;

	//echo " to: " . $output->to->format('Ymd');
	return clone $output;
}

function getNextDate($input)
{
	$output;

	if ($input->note == "First half") {
		$output = $input->from->modify('first day of this month');
		$output = $input->from->modify('+15 days');
	} else {
		$output = $input->from->modify('first day of next month');
	}
	return $output;
}

function checkIfContinuing($from, $to, $next_date)
{
	return (($next_date >= $from) && ($next_date <= $to));
}

//Fetch Adviser Data
$searchadv = "SELECT * FROM adviser_tbl ORDER BY name";
$search = mysqli_query($con, $searchadv) or die('Could not look up user information; ' . mysqli_error($con));

$report_data = new stdClass();
//fetch kiwisaver deals
$report_data->kiwisavers_count = 0;
$report_data->total_kiwisaver_commission = 0;
$report_data->total_kiwisaver_gst = 0;
$report_data->total_kiwisaver_balance = 0;
$report_data->period_kiwisavers_commission = 0;
$report_data->period_kiwisavers_gst = 0;
$report_data->period_kiwisavers_balance = 0;
$report_data->period_kiwisavers_count = 0;

$report_data->period_covered_title = $period_covered_title;
$report_data->advisers = [];
$report_data->total_pending_api = 0;
$report_data->total_issued_api = 0;
$report_data->total_cancelled_api = 0;
$report_data->total_submission_api = 0;

$report_data->issued_leads_percentages = new stdClass();
$report_data->issued_leads_percentages->leads = [];
$report_data->issued_leads_percentages->bdm_leads = [];
$report_data->issued_leads_percentages->telemarketer_leads = [];
$report_data->issued_leads_percentages->self_generated_leads = [];

$report_data->submitted_leads_percentages = new stdClass();
$report_data->submitted_leads_percentages->leads = [];
$report_data->submitted_leads_percentages->bdm_leads = [];
$report_data->submitted_leads_percentages->telemarketer_leads = [];
$report_data->submitted_leads_percentages->self_generated_leads = [];

$report_data->period_pending_api = 0;
$report_data->period_issued_api = 0;
$report_data->period_cancelled_api = 0;
$report_data->period_submission_api = 0;

$report_data->submissions_count = 0;
$report_data->issued_count = 0;
$report_data->cancellations_count = 0;
$report_data->pending_count = 0;

$report_data->assigned_bdm_leads = array();
$report_data->assigned_telemarketer_leads = array();
$report_data->assigned_self_generated_leads = array();

$report_data->dash_indexes = array();
$report_data->dash_values = array();
$report_data->colors = array();

//LINE GRAPH
$report_data->kiwisavers_in_pool = array();
$report_data->submissions_in_pool = array();
$report_data->issued_in_pool = array();
$report_data->cancellations_in_pool = array();

$report_data->submission_apis_in_pool = array();
$report_data->issued_apis_in_pool = array();
$report_data->cancellation_apis_in_pool = array();

$report_data->cancellation_rate = array();
$report_data->proficiency = array();

$kiwisaver_cumulative = $dealController->GetCompanyCumulativeKiwiSaver();

$report_data->total_kiwisavers_commission = $kiwisaver_cumulative["total_commission"];
$report_data->total_kiwisavers_gst =  $kiwisaver_cumulative["total_gst"];
$report_data->total_kiwisavers_balance =  $kiwisaver_cumulative["total_balance"];


$report_data->report_type = $_POST['report_type'];

while ($row = mysqli_fetch_assoc($search)) {
	$adviser = new stdClass();
	$adviser->id = $row["id"];
	$adviser->name = $row["name"];
	$adviser->submissions = [];
	$adviser->pending_deals = [];
	$adviser->issued_deals = [];
	$adviser->cancelled_deals = [];
	$adviser->total_pending_api = 0;
	$adviser->total_submission_api = 0;
	$adviser->total_issued_api = 0;
	$adviser->total_cancelled_api = 0;

	$adviser->kiwisavers = [];
	$adviser->total_kiwisavers_api = 0;
	$adviser->total_kiwisavers_commission = 0;
	$adviser->total_kiwisavers_gst = 0;
	$adviser->total_kiwisavers_balance = 0;
	$kiwisaver_deals = $dealController->GetKiwiSaversIssuedByAdviserInDateRange($row["id"], $date_from, $until);


	while ($row2 = $kiwisaver_deals->fetch_assoc()) {
		$adviser->kiwisaver_deals[] = array(
			"client" => $row2["client_name"],
			"date" => $row2["issue_date"],
			"deal" => $row2["commission"],
			"api" => $row2["commission"],
			"commission" => $row2["commission"],
			"gst" => $row2["gst"],
			"balance" => $row2["balance"],
		);
		$report_data->kiwisavers_count++;
		$report_data->period_kiwisavers_count++;

		$adviser->total_kiwisavers_commission += $row2["commission"];
		$adviser->total_kiwisavers_gst += $row2["gst"];
		$adviser->total_kiwisavers_balance += $row2["balance"];
	}

	$report_data->period_kiwisavers_commission += $adviser->total_kiwisavers_commission;
	$report_data->period_kiwisavers_gst += $adviser->total_kiwisavers_gst;
	$report_data->period_kiwisavers_balance += $adviser->total_kiwisavers_balance;
	
	$search_leads = "SELECT * FROM clients_tbl c LEFT JOIN submission_clients s ON c.id = s.client_id WHERE assigned_to='" . $row['id'] . "' AND c.status!='Cancelled'";
	//echo $search_leads . "<hr>";
	$leads_exec = mysqli_query($con, $search_leads) or die('Could not look up user information; ' . mysqli_error($con));

	while ($row = mysqli_fetch_array($leads_exec)) {
		if (!isset($row["deals"]))
			continue;

		$deals = json_decode($row["deals"]);

		foreach ($deals as $deal) {
			$life_insured = $row["name"];
			if (!empty($deal->life_insured))
				$life_insured .= ", " . $deal->life_insured;

			$report_data->total_submission_api += $deal->original_api;
			//Add To List of Deals

			//register submission
			if ($deal->submission_date >= $date_from && $deal->submission_date <= $until) {
				$adviser->submissions[] = array(
					"client" => $life_insured,
					"date" => $deal->submission_date,
					"deal" => $deal,
					"api" => $deal->original_api,
				);

				if (!in_array($row["client_id"], $report_data->submitted_leads_percentages->leads)) {
					$report_data->submitted_leads_percentages->leads[] = $row["client_id"];
					switch ($row["lead_by"]) {
						case "Face-to-Face Marketer":
							$report_data->submitted_leads_percentages->bdm_leads[] = $row["client_id"];
							break;
						case "Telemarketer":
							$report_data->submitted_leads_percentages->telemarketer_leads[] = $row["client_id"];
							break;
						case "Self-Generated":
							$report_data->submitted_leads_percentages->self_generated_leads[] = $row["client_id"];
							break;
					}
				}
				$report_data->submissions_count++;

				$adviser->total_submission_api += $deal->original_api;
			}


			if ($deal->status == "Issued") {
				if ($deal->date_issued >= $date_from && $deal->date_issued <= $until) {
					$adviser->issued_deals[] = array(
						"client" => $life_insured,
						"date" => $deal->date_issued,
						"deal" => $deal,
						"api" => $deal->issued_api,
					);

					if (!in_array($row["client_id"], $report_data->issued_leads_percentages->leads)) {
						$report_data->issued_leads_percentages->leads[] = $row["client_id"];
						switch ($row["lead_by"]) {
							case "Face-to-Face Marketer":
								$report_data->issued_leads_percentages->bdm_leads[] = $row["client_id"];
								break;
							case "Telemarketer":
								$report_data->issued_leads_percentages->telemarketer_leads[] = $row["client_id"];
								break;
							case "Self-Generated":
								$report_data->issued_leads_percentages->self_generated_leads[] = $row["client_id"];
								break;
						}
					}
					$report_data->issued_count++;
					$adviser->total_issued_api += $deal->issued_api;
				}
				$report_data->total_issued_api += $deal->issued_api;
				//Add to Cancelled Deals
				if (isset($deal->clawback_status)) {
					if ($deal->clawback_status == "Cancelled") {
						if ($deal->clawback_date >= $date_from && $deal->clawback_date <= $until) {
							$adviser->cancelled_deals[] = array(
								"client" => $life_insured,
								"date" => $deal->clawback_date,
								"deal" => $deal,
								"api" => $deal->clawback_api,
							);
							$report_data->cancellations_count++;
							$adviser->total_cancelled_api += $deal->clawback_api;
						}
						$report_data->total_cancelled_api += $deal->clawback_api;
					}
				}
			}
		}
	}

	$report_data->period_submission_api += $adviser->total_submission_api;
	$report_data->period_issued_api += $adviser->total_issued_api;
	$report_data->period_cancelled_api += $adviser->total_cancelled_api;

	$report_data->advisers[] = $adviser;
}

//Fetch Leads assigned to team
$search_assigned_leads = "SELECT * FROM clients_tbl WHERE date_submitted>='$date_from' AND date_submitted<='$until'";

$assigned_leads_exec = mysqli_query($con, $search_assigned_leads) or die('Could not look up user information; ' . mysqli_error($con));
while ($assigned_lead = mysqli_fetch_array($assigned_leads_exec)) {

	switch ($assigned_lead["lead_by"]) {
		case "Face-to-Face Marketer":
			$report_data->assigned_bdm_leads[] = $assigned_leads_exec;
			break;
		case "Telemarketer":
			$report_data->assigned_telemarketer_leads[] = $assigned_leads_exec;
			break;
		case "Self-Generated":
			$report_data->assigned_self_generated_leads[] = $assigned_leads_exec;
			break;
	}
}

$pool = "";
$term = "";
$pool_origin = "";
switch ($report_data->report_type) {
	case "Weekly":
		$pool_origin = "Week";
		$pool = "days";
		$term = "D";
		break;
	case "Bi-Monthly":
		$pool_origin = "Bi-Month";
		$pool = "weeks";
		$term = "W";
		break;
	case "Monthly":
		$pool_origin = "Month";
		$pool = "weeks";
		$term = "W";
		break;
	case "Specified":
		$pool_origin = "Specified Months";
		$pool = "months";
		$term = "M";
		break;
	case "Annual":
		$pool_origin = "Year";
		$pool = "months";
		$term = "M";
		break;
	case "Quarterly":
		$pool_origin = "Quarter";
		$pool = "months";
		$term = "M";
		break;
}

$ctr = 1;

foreach ($$pool as $bm) {
	$bm_from = $bm->from->format('Ymd');
	$bm_to = $bm->to->format('Ymd');
	//echo "$bm_from - $bm_to <br>";
	$bm_submissions = 0;
	$bm_issued = 0;
	$bm_cancellations = 0;

	$bm_submissions_api = 0;
	$bm_issued_api = 0;
	$bm_cancellations_api = 0;

	$bm_clients_query = "SELECT * FROM clients_tbl c LEFT JOIN submission_clients s ON c.id = s.client_id WHERE c.status!='Cancelled'";
	$bm_clients_result = mysqli_query($con, $bm_clients_query) or die('Could not look up user information; ' . mysqli_error($con));

	while ($row = mysqli_fetch_array($bm_clients_result)) {
		if (!isset($row["deals"]))
			continue;

		$deals = json_decode($row["deals"]);

		foreach ($deals as $deal) {
			$life_insured = $row["name"];
			if (!empty($deal->life_insured))
				$life_insured .= ", " . $deal->life_insured;

			//Add To Submissions
			if ($deal->submission_date >= $bm_from && $deal->submission_date <= $bm_to) {
				$bm_submissions++;
				$bm_submissions_api += $deal->original_api;
			}

			//Add to Issued Deals
			if ($deal->status == "Issued") {
				if ($deal->date_issued >= $bm_from && $deal->date_issued <= $bm_to) {
					$bm_issued++;
					$bm_issued_api += $deal->issued_api;
				}

				//Add to Cancelled Deals
				if (isset($deal->clawback_status)) {
					if ($deal->clawback_status == "Cancelled") {

						if ($deal->clawback_date >= $bm_from && $deal->clawback_date <= $bm_to) {
							$bm_cancellations++;
							$bm_cancellations_api += $deal->clawback_api;
						}
					}
				}
			}
		}
	}

	$bm_date_to = "$term$ctr";

	$report_data->submissions_in_pool[$bm_date_to] = $bm_submissions;
	$report_data->issued_in_pool[$bm_date_to] = $bm_issued;
	$report_data->cancellations_in_pool[$bm_date_to] = $bm_cancellations;

	$report_data->submission_apis_in_pool[$bm_date_to] = $bm_submissions_api;
	$report_data->issued_apis_in_pool[$bm_date_to] = $bm_issued_api;
	$report_data->cancellation_apis_in_pool[$bm_date_to] = $bm_cancellations_api;


	$report_data->cancellation_rate[$bm_date_to] = GetCancellationRate($bm_cancellations_api, $bm_issued_api);

	$report_data->proficiency[$bm_date_to] = GetProficiency($bm_issued_api, $bm_issued);

	$ctr++;
}

$ctr = 1;
foreach ($$pool as $bm) {
	$bm_from = $bm->from->format('Ymd');
	$bm_to = $bm->to->format('Ymd');
	//echo "$bm_from - $bm_to <br>";
	$bm_kiwisavers = 0;

	$bm_kiwisavers_api = 0;
	$kiwi_bm = $dealController->GetKiwiSaversIssuedByTeamInDateRange($bm_from, $bm_to);

	while ($row = $kiwi_bm->fetch_assoc()) {
		$bm_kiwisavers++;
		$bm_kiwisavers_api += $row["commission"];
	}

	$bm_date_to = "$term$ctr";
	//$bm_date_to = "$term$ctr" . $bm->from->format('m/d/Y') . "-" . $bm->to->format('m/d/Y');

	$report_data->kiwisavers_in_pool[$bm_date_to] = $bm_kiwisavers;

	$report_data->kiwisavers_apis_in_pool[$bm_date_to] = $bm_kiwisavers_api;
	$ctr++;
}


$report_data->deals_graph = array(
	'Submissions' => $report_data->submissions_in_pool,
	'Issued Policies' => $report_data->issued_in_pool,
	'Cancellations' => $report_data->cancellations_in_pool,
	'KiwiSavers' => $report_data->kiwisavers_in_pool
);

//apis
$report_data->api_graph = array(
	'Submissions' => $report_data->submission_apis_in_pool,
	'Issued Policies' => $report_data->issued_apis_in_pool,
	'Cancellations' => $report_data->cancellation_apis_in_pool,
	'KiwiSavers' => $report_data->kiwisavers_apis_in_pool
);

//fetch deals



$pdf = new PDF('P', 'mm', 'Legal');
$x = $pdf->GetX();
$y = $pdf->GetY();

//page 1
$pdf->AddPage('P', 'Legal');

$pdf->SetFillColor(224, 224, 224);
$pdf->SetFont('Helvetica', 'B', 20);
$pdf->Cell(200, 8, 'Team ' . $report_data->report_type . ' Production Report', "0", "1", "C", 'true');

$pdf->SetFont('Helvetica', 'B', 10);
$pdf->Cell(13, 8, 'Period:', "0", "0", "L");
$pdf->SetFont('Helvetica', '', 10);
$pdf->Cell(35, 8, $report_data->period_covered_title, "0", "0", "L");
$pdf->Cell(52, 8, "", "0", "0", "L");
$pdf->SetFont('Helvetica', 'B', 10);
$pdf->Cell(13, 8, '', "0", "0", "L");
$pdf->SetFont('Helvetica', '', 10);
$pdf->Cell(35, 8, '', "0", "1", "L");

$pdf->SetFillColor(242, 242, 242);
$pdf->setWidths(array(40, 40, 40, 40, 40));
$pdf->setAligns("C", "C", "C", "C", "C");

$pdf->SetMCFonts(array(
	array('Helvetica', '', 10),
	array('Helvetica', 'B', 10)
));

//Head Row
$pdf->Row(array("", "Submissions", "Issued Policies", "Cancellations", "KiwiSavers"), false, array(242, 242, 242));


$pdf->SetMCFonts(array(
	array('Helvetica', 'B', 10),
	array('Helvetica', '', 10)
));

//Number of Deals
$pdf->Row(array("Number of Deals", $report_data->submissions_count, $report_data->issued_count, $report_data->cancellations_count, $report_data->kiwisavers_count), true, array(242, 242, 242));

//API in Date Range
$pdf->Row(array("Total API",  "$" . number_format($report_data->period_submission_api, 2),  "$" . number_format($report_data->period_issued_api, 2),  "$" . number_format($report_data->period_cancelled_api, 2),  "$" . number_format($report_data->period_kiwisavers_commission, 2)), false, array(242, 242, 242));

//Cumulative API
$pdf->Row(array("Cumulative API",  "$" . number_format($report_data->total_submission_api, 2),  "$" . number_format($report_data->total_issued_api, 2),  "$" . number_format($report_data->total_cancelled_api, 2),  "$" . number_format($report_data->total_kiwisavers_commission, 2)), true, array(242, 242, 242));

$pdf->SetFillColor(224, 224, 224);

$report_data->dash_indexes[] = 2;
$report_data->dash_values[2] = array(2, 2);

$maroon = array(150, 50, 50);
$blue_green = array(0, 75, 0);
$dark_blue = array(0, 0, 150);
$violet = array(150, 50, 150);
$gray = array(100, 100, 100);

$report_data->colors = array(
	"Submissions" => $dark_blue,
	"Issued Policies" => $blue_green,
	"Cancellations" => $maroon,
	"KiwiSavers" => $gray
);


$grad1 = array(129, 129, 184);
$grad2 = array(225, 225, 225);

//set the coordinates x1,y1,x2,y2 of the gradient (see linear_gradient_coords.jpg)
$coords = array(0, 0, 1, 1);

//paint a linear gradient
$pdf->SetFont('Helvetica', 'B', 15);
$pdf->SetFillColor(224, 224, 224);

$chartHelper = new ChartHelper();
$x_labels_array = array();
foreach ($report_data->submissions_in_pool as $key => $value) {
	$x_labels_array[] = $key;
}

$chart_data = new stdClass();
$chart_data->bar_chart = new stdClass();
$chart_data->pie_chart = new stdClass();
$chart_data->pie_chart2 = new stdClass();
$chart_data->line_chart = new stdClass();
$chart_data->line_chart2 = new stdClass();
$chart_data->line_chart3 = new stdClass();
$chart_data->line_chart4 = new stdClass();

//Bar Chart
$chart_data->bar_chart->points_array = array(
	"Assigned Leads" => array(count($report_data->assigned_bdm_leads) + count($report_data->assigned_telemarketer_leads) + count($report_data->assigned_self_generated_leads)),
	"Submitted Deals" => array($report_data->submissions_count),
	"Issued Deals" => array($report_data->issued_count),
	"Cancelled Deals" => array($report_data->cancellations_count),
	"KiwiSaver Deals" => array($report_data->kiwisavers_count)
);

$chart_data->bar_chart->xLabels = array("Number of Leads");
$chart_data->bar_chart->xLabelName = "Leads per Lead Generator";

$chart_data->bar_chart->colors_array = array(
	"Assigned Leads" => $violet,
	"Issued Deals" => $blue_green,
	"Submitted Deals" => $dark_blue,
	"Cancelled Deals" => $maroon,
	"KiwiSaver Deals" => $gray
);

//Pie Chart
$chart_data->pie_chart->points_array = array(
	"BDM Leads" => array(
		count($report_data->assigned_bdm_leads),
		count($report_data->assigned_telemarketer_leads),
		count($report_data->assigned_self_generated_leads)
	)
);

//Pie Chart
$chart_data->pie_chart->points_array = array(
	"BDM Leads" => array(
		count($report_data->submitted_leads_percentages->bdm_leads),
		count($report_data->submitted_leads_percentages->telemarketer_leads),
		count($report_data->submitted_leads_percentages->self_generated_leads)
	)
);

$chart_data->pie_chart->xLabels = array("BDM Leads", "Tele Leads", "Self-Gen Leads");
$chart_data->pie_chart->xLabelName = "BDM Leads";
$chart_data->pie_chart->graphTitle = "Submissions %";

//Pie Chart
$chart_data->pie_chart2->points_array = array(
	"BDM Leads" => array(
		count($report_data->issued_leads_percentages->bdm_leads),
		count($report_data->issued_leads_percentages->telemarketer_leads),
		count($report_data->issued_leads_percentages->self_generated_leads)
	)
);
$chart_data->pie_chart2->graphTitle = "Issued %";

$chart_data->pie_chart->colors_array = array(
	"BDM Leads" => $maroon,
	"Tele Leads" => $dark_blue,
	"Self-Gen Leads" => $blue_green
);

$chart_data->line_chart->points_array = $report_data->deals_graph;

$chart_data->line_chart->ticks_array = array(
	"Cancellations" => 4
);

$chart_data->line_chart->weights_array = array(
	"Submissions" => 2,
	"Issued Policies" => 3,
	"Cancellations" => 1,
	"KiwiSavers" => 1.5
);

$chart_data->line_chart->colors_array = $report_data->colors;

$chart_data->line_chart->axis_names_array = array(
	0 => "Deals per Month"
);

$chart_data->line_chart->xLabels = $x_labels_array;
$chart_data->line_chart->xLabelName = "Period";
$chart_data->line_chart->graphTitle = "Number of deals per " . substr($pool, 0, -1);

$chart_data->line_chart2->points_array = $report_data->api_graph;
$chart_data->line_chart2->colors_array = $report_data->colors;

$chart_data->line_chart2->ticks_array = array(
	"Cancellations" => 4
);

$chart_data->line_chart2->weights_array = array(
	"Submissions" => 2,
	"Issued Policies" => 3,
	"Cancellations" => 1,
	"KiwiSavers" => 1.5
);
$chart_data->line_chart2->axis_names_array = array(
	0 => "API per Month"
);
$chart_data->line_chart2->xLabelName = "Period";
$chart_data->line_chart2->graphTitle = "API Per " . substr($pool, 0, -1);

//Line Chart 3
$chart_data->line_chart3->points_array = array(
	"Percentages" => $report_data->cancellation_rate
);
$chart_data->line_chart3->colors_array = array(
	"Percentages" => $maroon
);

$chart_data->line_chart3->weights_array = array(
	"Percentages" => 2
);

$chart_data->line_chart3->axis_names_array = array(
	0 => "Percentages"
);

$chart_data->line_chart3->xLabelName = "Period";
$chart_data->line_chart3->graphTitle = "Cancellation Rate Per " . substr($pool, 0, -1);

//var_dump($report_data->proficiency);
//Line Chart 4
$chart_data->line_chart4->points_array = array(
	"Proficiency" => $report_data->proficiency
);
$chart_data->line_chart4->colors_array = array(
	"Proficiency" => $blue_green
);

$chart_data->line_chart4->weights_array = array(
	"Proficiency" => 3
);

$chart_data->line_chart4->axis_names_array = array(
	0 => "Adviser Proficiency"
);

$chart_data->line_chart4->xLabelName = "Period";
$chart_data->line_chart4->graphTitle = "Adviser Proficiency Per " . substr($pool, 0, -1);

$filename = "files/adviser_report_api_" . md5(uniqid()) . ".png";

$chartHelper->GenerateCustomMixedChartForAdviserReport($chart_data, $filename);
$pdf->Ln(5);
$pdf->Image($filename, $pdf->GetX(), $pdf->GetY(), 200, 260);
$pdf->AddPage();

$pdf->SetFont('Helvetica', 'B', 14);
$pdf->SetFillColor(224, 224, 224);
$performance_title = "";
$period_title = "";
switch ($report_data->report_type) {
	case "Weekly":
		$pool = "days";
		$performance_title = "Daily";
		$period_title = "Date";
		break;
	case "Bi-Monthly":
		$pool = "weeks";
		$performance_title = "Weekly";
		$period_title = "Period";
		break;
	case "Monthly":
		$pool = "weeks";
		$performance_title = "Weekly";
		$period_title = "Period";
		break;
	case "Specified":
		$pool = "months";
		$performance_title = "Monthly";
		$period_title = "Month";
		break;
	case "Annual":
		$pool = "months";
		$performance_title = "Monthly";
		$period_title = "Month";
		break;
}

$pdf->Cell(200, 10, "$performance_title Performance", "0", "1", "C", 'true');

//Headers

$pdf->SetMCFonts(array(
	array('Helvetica', 'U', 13),
	array('Helvetica', 'U', 13)
));
$pdf->setWidths(array(10, 30, 30, 30, 30, 30, 40));
$pdf->setAligns("C", "C", "C");
$pdf->Row(array("","Submission API", "Issued API", "Cancellation API", "KiwiSaver API", "Proficiency", "Cancellation Rate"), false, array(242, 242, 242));
	
$pdf->SetMCFonts(array(
	array('Helvetica', 'B', 10),
	array('Helvetica', '', 10)
));

$period_array = array_keys($report_data->proficiency);

foreach ($period_array as $index => $period) {
	$fill = (($index % 2) === 0) ? true : false;
	$pdf->Row(array($period, Currency($report_data->submission_apis_in_pool[$period]), Currency($report_data->issued_apis_in_pool[$period]), Currency($report_data->cancellation_apis_in_pool[$period]), Currency($report_data->kiwisavers_apis_in_pool[$period]), Currency($report_data->proficiency[$period]),  $report_data->cancellation_rate[$period] . "%"), $fill, array(242, 242, 242));
}

$pdf->SetDrawColor(0, 0, 0);
$pdf->SetFont('Helvetica', 'U', 10);
$pdf->Cell(10, 8, "T", "T", 0, 'C');
$pdf->Cell(30, 8, Currency($report_data->total_submission_api), "T", 0, 'C');
$pdf->Cell(30, 8, Currency($report_data->total_issued_api), "T", 0, 'C');
$pdf->Cell(30, 8, Currency($report_data->total_cancelled_api), "T", 0, 'C');
$pdf->Cell(30, 8, Currency($report_data->total_kiwisaver_commission), "T", 0, 'C');
$pdf->Cell(30, 8, Currency(GetProficiency($report_data->total_issued_api, count($report_data->issued_in_pool))), "T", 0, 'C');
$pdf->Cell(40, 8, GetCancellationRate($report_data->total_cancelled_api, $report_data->total_issued_api) . "%", "T", 1, 'C');

$pdf->SetMCFonts(array(
	array('Helvetica', '', 10),
	array('Helvetica', '', 10)
));
$pdf->setWidths(array(100, 40, 60));
$pdf->setAligns("C", "C", "C");


if ($report_data->submissions_count > 0) {
	$pdf->SetFont('Helvetica', 'B', 14);
	$pdf->SetFillColor(224, 224, 224);
	$pdf->SetTextColor(0, 0, 0);
	$pdf->Cell(200, 10, 'Submissions ', 0, 1, 'C', 'true');

	$pdf->SetFont('Helvetica', 'U', 12);
	$pdf->SetFillColor(0, 0, 0);
	$pdf->SetTextColor(0, 0, 0);
	$pdf->Cell(100, 10, 'Adviser', 0, 0, 'L');
	$pdf->Cell(43, 10, 'No. of Deals', 0, 0, 'L');
	$pdf->Cell(60, 10, 'API', 0, 1, 'L');

	$pdf->SetFont('Helvetica', '', 12);

	foreach ($report_data->advisers as $adviser) {
		if (count($adviser->submissions) > 0) {
			$pdf->Cell(100, 10, $adviser->name, "0", 0, 'L');
			$pdf->Cell(43, 10, count($adviser->submissions), 0, 0, 'L');
			$pdf->Cell(60, 10, "$" . number_format($adviser->total_submission_api, 2), 0, 1, 'L');
		}
	}
	$pdf->Cell(100, 10, "Total", "T", 0, 'L');
	$pdf->Cell(43, 10, "", "T", 0, 'L');
	$pdf->Cell(60, 10, "$" . number_format($report_data->period_submission_api, 2), "T", 1, 'L');
}

if ($report_data->issued_count > 0) {
	$pdf->SetFont('Helvetica', 'B', 14);
	$pdf->SetFillColor(224, 224, 224);
	$pdf->SetTextColor(0, 0, 0);
	$pdf->Cell(200, 10, 'Policies Issued', 0, 1, 'C', 'true');

	$pdf->SetFont('Helvetica', 'U', 12);
	$pdf->SetFillColor(0, 0, 0);
	$pdf->SetTextColor(0, 0, 0);
	$pdf->Cell(100, 10, 'Adviser', 0, 0, 'L');
	$pdf->Cell(43, 10, 'No. of Policies', 0, 0, 'L');
	$pdf->Cell(60, 10, 'API', 0, 1, 'L');

	$pdf->SetFont('Helvetica', '', 12);

	foreach ($report_data->advisers as $adviser) {
		if (count($adviser->issued_deals) > 0) {
			$pdf->Cell(100, 10, $adviser->name, "0", 0, 'L');
			$pdf->Cell(43, 10, count($adviser->issued_deals), 0, 0, 'L');
			$pdf->Cell(60, 10, "$" . number_format($adviser->total_issued_api, 2), 0, 1, 'L');
		}
	}
	$pdf->Cell(100, 10, "Total", "T", 0, 'L');
	$pdf->Cell(43, 10, "", "T", 0, 'L');
	$pdf->Cell(60, 10, "$" . number_format($report_data->period_issued_api, 2), "T", 1, 'L');
}


if ($report_data->cancellations_count > 0) {
	$pdf->SetFont('Helvetica', 'B', 14);
	$pdf->SetFillColor(224, 224, 224);
	$pdf->SetTextColor(0, 0, 0);
	$pdf->Cell(200, 10, 'Cancellations', 0, 1, 'C', 'true');

	$pdf->SetFont('Helvetica', 'U', 12);
	$pdf->SetFillColor(0, 0, 0);
	$pdf->SetTextColor(0, 0, 0);
	$pdf->Cell(100, 10, 'Adviser', 0, 0, 'L');
	$pdf->Cell(43, 10, 'No. of Policies', 0, 0, 'L');
	$pdf->Cell(60, 10, 'API', 0, 1, 'L');

	$pdf->SetFont('Helvetica', '', 12);


	foreach ($report_data->advisers as $adviser) {
		if (count($adviser->cancelled_deals) > 0) {
			$pdf->Cell(100, 10, $adviser->name, "0", 0, 'L');
			$pdf->Cell(43, 10, count($adviser->cancelled_deals), 0, 0, 'L');
			$pdf->Cell(60, 10, "$" . number_format($adviser->total_cancelled_api, 2), 0, 1, 'L');
		}
	}
	$pdf->Cell(100, 10, "Total", "T", 0, 'L');
	$pdf->Cell(43, 10, "", "T", 0, 'L');
	$pdf->Cell(60, 10, "$" . number_format($report_data->period_cancelled_api, 2), "T", 1, 'L');
}

if ($report_data->kiwisavers_count > 0) {
	$pdf->SetFont('Helvetica', 'B', 14);
	$pdf->SetFillColor(224, 224, 224);
	$pdf->SetTextColor(0, 0, 0);
	$pdf->Cell(200, 10, 'KiwiSavers', 0, 1, 'C', 'true');

	$pdf->SetFont('Helvetica', 'U', 12);
	$pdf->SetFillColor(0, 0, 0);
	$pdf->SetTextColor(0, 0, 0);
	$pdf->Cell(65, 10, 'Adviser', 0, 0, 'L');
	$pdf->Cell(30, 10, 'Deals', 0, 0, 'L');
	$pdf->Cell(35, 10, 'Commission', 0, 0, 'L');
	$pdf->Cell(35, 10, 'GST', 0, 0, 'L');
	$pdf->Cell(35, 10, 'Balance', 0, 1, 'L');

	$pdf->SetFont('Helvetica', '', 12);


	foreach ($report_data->advisers as $adviser) {
		if (count($adviser->kiwisaver_deals) > 0) {
			$pdf->Cell(65, 10, $adviser->name, "0", 0, 'L');
			$pdf->Cell(30, 10, count($adviser->kiwisaver_deals), 0, 0, 'L');
			$pdf->Cell(35, 10, "$" . number_format($adviser->total_kiwisavers_commission, 2), 0, 0, 'L');
			$pdf->Cell(35, 10, "$" . number_format($adviser->total_kiwisavers_gst, 2), 0, 0, 'L');
			$pdf->Cell(35, 10, "$" . number_format($adviser->total_kiwisavers_balance, 2), 0, 1, 'L');
		}
	}
	$pdf->Cell(65, 10, "Total", "T", 0, 'L');
	$pdf->Cell(30, 10, $report_data->period_kiwisavers_count, "T", 0, 'L');
	$pdf->Cell(35, 10, "$" . number_format($report_data->period_kiwisavers_commission, 2), "T", 0, 'L');
	$pdf->Cell(35, 10, "$" . number_format($report_data->period_kiwisavers_gst, 2), "T", 0, 'L');
	$pdf->Cell(35, 10, "$" . number_format($report_data->period_kiwisavers_balance, 2), "T", 1, 'L');
}


/*

if(in_array('Pending', $desc) && count($report_data->pending_count)>0){
	$pdf->SetFont('Helvetica','',14);
	$pdf->SetFillColor(224,224,224);
	$pdf->SetTextColor(0,0,0);
	$pdf->Cell(100,10,'Pending Deals', 0, 0,'L','true');
	$pdf->Cell(43,10,' ', 0, 0,'R','true');
	$pdf->Cell(60,10,'  ', 0, 1,'C','true');

	$pdf->SetFont('Helvetica','U',12);
	$pdf->SetFillColor(0,0,0);
	$pdf->SetTextColor(0,0,0);
	$pdf->Cell(100,10,'Life Insured', 0, 0,'L');
	$pdf->Cell(43,10,'Submission Date', 0, 0,'L');
	$pdf->Cell(60,10,'Original API', 0, 1,'L');

	$pdf->SetFont('Helvetica','',12);

	
	usort($report_data->pending_deals, "sortFunction");
	foreach($report_data->pending_deals as $deal){
		extract($deal);
		$pdf->Cell(100,10,$client, "0", 0,'L');
		$pdf->Cell(43,10,NZEntryToDateTime($date), 0, 0,'L');
		$pdf->Cell(60,10,"$" . number_format($api,2), 0, 1,'L');
	}
	$pdf->Cell(100,10,"", "T", 0,'L');
	$pdf->Cell(43,10,"", "T", 0,'L');
	$pdf->Cell(60,10,"$" . number_format($report_data->total_pending_api,2), "T", 1,'L');
}
*/

$mix = "";
//$mix="$mydateorig"."-$name-"."BCTI".-date("ymd");
//$path="files/".$mix.".pdf";

$preview = "team_report_" . md5(uniqid());
$path = "files/$preview" . "_preview.pdf";
$pdf->Output($path, 'F');
//$pdf->Output();

ob_end_clean();
//OUTPUT 
$file = array();
$file['adviser_id'] = "";
$file['link'] = $path;
$file['filename'] = $mix;
$file['description'] = $_POST['desc'];
$file['report_data'] = json_encode($report_data);
$file['from'] = $date_from;
$file['type'] = $_POST['type'];
$file['schedule_type'] = $report_schedule_type;
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

function GetProficiency($issued_api, $issued_policies)
{
	$proficiency = 0;

	if ($issued_api > 0 && $issued_policies > 0) {
		$proficiency = number_format(($issued_api / $issued_policies), 2, '.', '');
	}

	return $proficiency;
}

function Currency($value, $currency = "$"){
	return $currency . number_format($value,2);
}
