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
		$this->Cell(100, 10, 'Adviser Production Report ' . '' . ' ' . preg_replace("/\([^)]+\)/", "", ''), 0, 0, 'L');
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
$adv_name = isset($_POST['adv_name']) ? $_POST['adv_name'] : '';				//Adviser name
$adviser_id = isset($_POST['adviser_id']) ? $_POST['adviser_id'] : '';		//Adviser id
$date_from = isset($_POST['date_from']) ? $_POST['date_from'] : '';			//Date from
$date_created = isset($_POST['date_created']) ? $_POST['date_created'] : '';	//Invoice Date
$due_date = isset($_POST['due_date']) ? $_POST['due_date'] : '';				//Due date
$until = isset($_POST['until']) ? $_POST['until'] : '';						//Date until
$report_schedule_type = $_POST['report_schedule_type'];
//Production Desc
$desc = json_decode($_POST['desc'], true);
//Test Desc
//$desc=$_POST['desc'];		
$date_created = date("d/m/Y");
$statementweek = date("d/m/Y");											//Statement Week
$other_value = isset($_POST['other_value']) ? $_POST['other_value'] : 0;		//Other

if ($other_value == '') {
	$other_value = 0;
}

//Fetch Adviser Data
$searchadv = "SELECT *, a.name as name, t.name as team_name FROM adviser_tbl a LEFT JOIN teams t ON a.team_id = t.id WHERE a.id='$adviser_id'";
$search = mysqli_query($con, $searchadv) or die('Could not look up user information; ' . mysqli_error($con));
$rows = mysqli_fetch_array($search);

//Extract Data
$fsp = $rows["fsp_num"];
$advisor_address = $rows["address"];
$leads = $rows["leads"];
$issued = $rows["bonus"];
$fsp_num = $rows['fsp_num'];
$email = $rows['email'];
$adviser_name = $rows["name"];
$team = $rows["team_name"];
if (empty($team))
	$team = "Not Assigned";

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
		if ($report_schedule_type == "Regular") {
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
		} else {
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


				$months[] = $bm;
				$d3->modify('+ 1 day');

				if (!checkIfContinuing($d1, $d2, $d3))
					$dateExceeded = true;
			}
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
		if ($report_schedule_type == "Regular") {
			while ($dateExceeded == false) {
				$bm = $date_helper->getMonth($d3->format('m'), $d3->format('Y'));
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
				$sm = $date_helper->getSumitMonth($d_annual, $is_third);
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
		break;
}

if ($_POST['report_type'] == "Quarterly") {
	$period_covered_title .= " of " . $d2->format('Y');
}


function checkIfContinuing($from, $to, $next_date)
{
	return (($next_date >= $from) && ($next_date <= $to));
}
$report_data = new stdClass();
//fetch kiwisaver deals
$report_data->total_kiwisaver_commission = 0;
$report_data->total_kiwisaver_gst = 0;
$report_data->total_kiwisaver_balance = 0;
$report_data->kiwisaver_deals = [];

$kiwisaver_deals = $dealController->GetKiwiSaversIssuedByAdviserInDateRange($adviser_id, $date_from, $until);

while ($row = $kiwisaver_deals->fetch_assoc()) {
	$report_data->kiwisaver_deals[] = array(
		"client" => $row["client_name"],
		"insured" => $row["insured_name"],
		"date" => $row["issue_date"],
		"deal" => $row["commission"],
		"api" => $row["commission"],
		"commission" => $row["commission"],
		"gst" => $row["gst"],
		"balance" => $row["balance"],
	);
	$report_data->total_kiwisaver_commission += $row["commission"];
	$report_data->total_kiwisaver_gst += $row["gst"];
	$report_data->total_kiwisaver_balance += $row["balance"];
}

$kiwisaver_deals = $report_data->kiwisaver_deals;

//fetch deals
$search_leads = "SELECT *, c.id as cl_id, c.name as cl_name FROM clients_tbl c LEFT JOIN submission_clients s ON c.id = s.client_id WHERE assigned_to='$adviser_id' AND c.status!='Cancelled'";
//echo $search_leads . "<hr>";
$leads_exec = mysqli_query($con, $search_leads) or die('Could not look up user information; ' . mysqli_error($con));

$report_data->total_pending_api = 0;
$report_data->total_issued_api = 0;
$report_data->total_cancelled_api = 0;
$report_data->total_submission_api = 0;
$report_data->pending_deals = [];
$report_data->issued_deals = [];
$report_data->cancelled_deals = [];
$report_data->submissions = [];

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

$report_data->assigned_bdm_leads = [];
$report_data->assigned_telemarketer_leads = [];
$report_data->assigned_self_generated_leads = [];

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

$report_data->report_type = $_POST['report_type'];

while ($row = mysqli_fetch_array($leads_exec)) {
	//Add Lead to list if inside date range
	if ($row["assigned_date"] >= $date_from && $row["assigned_date"] <= $until) {
		$lead = array(
			"id" => $row["cl_id"],
			"name" => $row["cl_name"],
			"date" => $row["assigned_date"],
		);

		if ($row["lead_by"] == "Face-to-Face Marketer") {
			$report_data->assigned_bdm_leads[] = $lead;
		} elseif ($row["lead_by"] == "Telemarketer") {
			$report_data->assigned_telemarketer_leads[] = $lead;
		} elseif ($row["lead_by"] == "Self-Generated") {
			$report_data->assigned_self_generated_leads[] = $lead;
		}
	}

	if (!isset($row["deals"]))
		continue;

	$deals = json_decode($row["deals"]);

	foreach ($deals as $deal) {
		$life_insured = $row["name"];
		if (!empty($deal->life_insured))
			$life_insured .= ", " . $deal->life_insured;

		//Add To Submissions
		if ($deal->submission_date >= $date_from && $deal->submission_date <= $until) {
			$report_data->submissions[] = array(
				"client" => $life_insured,
				"date" => $deal->submission_date,
				"deal" => $deal,
				"api" => $deal->original_api,
			);

			$report_data->total_submission_api += $deal->original_api;

			if (!in_array($row["cl_id"], $report_data->submitted_leads_percentages->leads)) {
				$report_data->submitted_leads_percentages->leads[] = $row["cl_id"];
				switch ($row["lead_by"]) {
					case "Face-to-Face Marketer":
						$report_data->submitted_leads_percentages->bdm_leads[] = $row["cl_id"];
						break;
					case "Telemarketer":
						$report_data->submitted_leads_percentages->telemarketer_leads[] = $row["cl_id"];
						break;
					case "Self-Generated":
						$report_data->submitted_leads_percentages->self_generated_leads[] = $row["cl_id"];
						break;
				}
			}
		}

		//Add all pending deals
		if ($deal->status == "Pending") {
			if ($deal->submission_date <= $until) {
				$report_data->pending_deals[] = array(
					"client" => $life_insured,
					"date" => $deal->submission_date,
					"deal" => $deal,
					"api" => $deal->original_api,
				);

				$report_data->total_pending_api += $deal->original_api;
			}
		}
		//Add to Issued Deals
		elseif ($deal->status == "Issued") {
			if ($deal->date_issued >= $date_from && $deal->date_issued <= $until) {
				$report_data->issued_deals[] = array(
					"client" => $life_insured,
					"date" => $deal->date_issued,
					"deal" => $deal,
					"api" => $deal->issued_api,
				);
				if (!in_array($row["cl_id"], $report_data->issued_leads_percentages->leads)) {
					$report_data->issued_leads_percentages->leads[] = $row["cl_id"];
					switch ($row["lead_by"]) {
						case "Face-to-Face Marketer":
							$report_data->issued_leads_percentages->bdm_leads[] = $row["cl_id"];
							break;
						case "Telemarketer":
							$report_data->issued_leads_percentages->telemarketer_leads[] = $row["cl_id"];
							break;
						case "Self-Generated":
							$report_data->issued_leads_percentages->self_generated_leads[] = $row["cl_id"];
							break;
					}
				}
				$report_data->total_issued_api += $deal->issued_api;
			}

			//Add to Cancelled Deals
			if (isset($deal->clawback_status)) {
				if ($deal->clawback_status == "Cancelled") {
					if ($deal->clawback_date >= $date_from && $deal->clawback_date <= $until) {
						$report_data->cancelled_deals[] = array(
							"client" => $life_insured,
							"date" => $deal->clawback_date,
							"deal" => $deal,
							"api" => $deal->clawback_api,
						);
						$report_data->total_cancelled_api += $deal->clawback_api;
					}
				}
			}
		}
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

$x_labels_array = array();
$ctr = 1;
//var_dump($$pool[1]);
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

	$bm_clients_query = "SELECT * FROM clients_tbl c LEFT JOIN submission_clients s ON c.id = s.client_id WHERE assigned_to='$adviser_id' AND c.status!='Cancelled'";
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
	//$bm_date_to = "$term$ctr" . $bm->from->format('m/d/Y') . "-" . $bm->to->format('m/d/Y');
	$x_labels_array[] = $bm_date_to;

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
	
		$kiwi_bm = $dealController->GetKiwiSaversIssuedByAdviserInDateRange($adviser_id, $bm_from, $bm_to);
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

//echo"Submissions:";
//var_dump($report_data->submissions_in_pool);
//deals
$report_data->deals_graph = array(
	'Issued Policies' => $report_data->issued_in_pool,
	'Submissions' => $report_data->submissions_in_pool,
	'KiwiSavers' => $report_data->kiwisavers_in_pool,
	'Cancellations' => $report_data->cancellations_in_pool
);

//apis
$report_data->api_graph = array(
	'Issued Policies' => $report_data->issued_apis_in_pool,
	'Submissions' => $report_data->submission_apis_in_pool,
	'KiwiSavers' => $report_data->kiwisavers_apis_in_pool,
	'Cancellations' => $report_data->cancellation_apis_in_pool
);

$search_issued = "SELECT * FROM issued_clients_tbl i LEFT JOIN clients_tbl c ON i.name = c.id WHERE c.assigned_to='$adviser_id' AND  i.date_issued<='$until' AND i.date_issued>='$date_from' AND c.lead_by!='Telemarketer'";
//Remove c.lead_by!='Telemarketer' to include leads from telemarketers
$issued_exec = mysqli_query($con, $search_issued) or die('Could not look up user information; ' . mysqli_error($con));
$count_issued = mysqli_num_rows($issued_exec);


$pdf = new PDF('P', 'mm', 'Legal');
$x = $pdf->GetX();
$y = $pdf->GetY();

//page 1
$pdf->AddPage('P', 'Legal');

$pdf->SetFillColor(224, 224, 224);
$pdf->SetFont('Helvetica', 'B', 20);
$pdf->Cell(200, 8, 'Adviser ' . $report_data->report_type . ' Production Report', "0", "1", "C", 'true');
$pdf->SetFont('Helvetica', 'B', 13);
$pdf->Cell(17, 8, 'Name:', "0", "0", "L");
$pdf->SetFont('Helvetica', '', 12);
$pdf->Cell(78, 8, $adviser_name, "0", "0", "L");
$pdf->Cell(5, 8, '', "0", "0", "R");
$pdf->SetFont('Helvetica', 'B', 13);
$pdf->Cell(41, 8, "Period Covered:", "0", "0", "L");
$pdf->SetFont('Helvetica', '', 12);
$pdf->Cell(59, 8, $period_covered_title, "0", "1", "L");
$pdf->SetFont('Helvetica', 'B', 13);
$pdf->Cell(17, 8, 'Team:', "0", "0", "L");
$pdf->SetFont('Helvetica', '', 12);
$pdf->Cell(78, 8, $team, "0", "0", "L");
$pdf->Cell(5, 8, '', "0", "0", "R");
$pdf->SetFont('Helvetica', 'B', 13);
$pdf->Cell(41, 8, "", "0", "0", "L");
$pdf->SetFont('Helvetica', '', 12);
$pdf->Cell(59, 8, "", "0", "1", "L");

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
$pdf->Row(array("Number of Deals", count($report_data->submissions), count($report_data->issued_deals), count($report_data->cancelled_deals), count($report_data->kiwisaver_deals)), true, array(242, 242, 242));

//Total API
$pdf->Row(array("Total API",  "$" . number_format($report_data->total_submission_api, 2),  "$" . number_format($report_data->total_issued_api, 2),  "$" . number_format($report_data->total_cancelled_api, 2),  "$" . number_format($report_data->total_kiwisaver_commission, 2)), false, array(242, 242, 242));


$show_desc = '';


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
$pdf->SetFont('Helvetica', 'B', 12);

$chartHelper = new ChartHelper();

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
	"Submitted Deals" => array(count($report_data->submissions)),
	"Issued Deals" => array(count($report_data->issued_deals)),
	"Cancelled Deals" => array(count($report_data->cancelled_deals)),
	"KiwiSaver Deals" => array(count($report_data->kiwisaver_deals))
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

$rba = 0;

foreach($report_data->issued_deals as $data){
	if($data["deal"]->replacement_business == "1"){
		$rba++;
	}
}

$rba_percent = ($rba/count($report_data->issued_deals))*100;

$pdf->Cell(200, 10, "$performance_title Performance", "0", "1", "C", 'true');



//Headers

$pdf->SetMCFonts(array(
	array('Helvetica', 'U', 13),
	array('Helvetica', 'U', 13)
));
$pdf->setWidths(array(10, 27, 27, 27, 27, 15, 40, 27));
$pdf->setAligns("C", "C", "C");
$pdf->SetFontSize(10);
$pdf->Row(array("","Submission API", "Issued API", "Cancellation API", "KiwiSaver API", "RBA", "Proficiency", "Cancellation Rate"), false, array(200, 200, 200));
	
$pdf->SetMCFonts(array(
	array('Helvetica', 'B', 10),
	array('Helvetica', '', 10)
));

$period_array = array_keys($report_data->proficiency);

foreach ($period_array as $index => $period) {
	$fill = (($index % 2) === 0) ? true : false;
	$pdf->Row(array($period, Currency($report_data->submission_apis_in_pool[$period]), Currency($report_data->issued_apis_in_pool[$period]), Currency($report_data->cancellation_apis_in_pool[$period]), Currency($report_data->kiwisavers_apis_in_pool[$period]), number_format($rba_percent, 2).'%', Currency($report_data->proficiency[$period]),  $report_data->cancellation_rate[$period] . "%"), $fill, array(200, 200, 200));
}

$pdf->SetDrawColor(0, 0, 0);
$pdf->SetFont('Helvetica', 'U', 10);
$pdf->Cell(10, 8, "T", "T", 0, 'C');
$pdf->Cell(27, 8, Currency($report_data->total_submission_api), "T", 0, 'C');
$pdf->Cell(27, 8, Currency($report_data->total_issued_api), "T", 0, 'C');
$pdf->Cell(27, 8, Currency($report_data->total_cancelled_api), "T", 0, 'C');
$pdf->Cell(27, 8, Currency($report_data->total_kiwisaver_commission), "T", 0, 'C');
$pdf->Cell(15, 8, number_format($rba_percent, 2)."%", "T", 0, 'C');
$pdf->Cell(40, 8, Currency(GetProficiency($report_data->total_issued_api, count($report_data->issued_in_pool))), "T", 0, 'C');
$pdf->Cell(27, 8, GetCancellationRate($report_data->total_cancelled_api, $report_data->total_issued_api) . "%", "T", 1, 'C');

$pdf->SetMCFonts(array(
	array('Helvetica', '', 10),
	array('Helvetica', '', 10)
));
$pdf->setWidths(array(100, 40, 60));
$pdf->setAligns("C", "C", "C");

if (in_array('Submission', $desc) && count($report_data->submissions) > 0) {
	$pdf->SetFont('Helvetica', '', 14);
	$pdf->SetFillColor(224, 224, 224);
	$pdf->SetTextColor(0, 0, 0);
	$pdf->Cell(200, 10, 'Submissions', 0, 1, 'L', 'true');

	$pdf->SetFont('Helvetica', 'U', 13);
	$pdf->SetFillColor(0, 0, 0);
	$pdf->SetTextColor(0, 0, 0);
	$pdf->Cell(100, 8, 'Life Insured', 0, 0, 'C');
	$pdf->Cell(40, 8, 'Submission Date', 0, 0, 'C');
	$pdf->Cell(60, 8, 'Original API', 0, 1, 'C');

	$pdf->SetFont('Helvetica', '', 12);
	$pdf->SetFillColor(242, 242, 242);

	usort($report_data->submissions, "sortFunction");
	foreach ($report_data->submissions as $index => $deal) {
		extract($deal);
		$fill = (($index % 2) === 0) ? true : false;
		$pdf->Row(array($client, $date_helper->NZEntryToDateTime($date), "$" . number_format($api, 2)), $fill, array(242, 242, 242));
	}
	
	$pdf->SetDrawColor(0, 0, 0);
	$pdf->SetFont('Helvetica', 'U', 10);
	$pdf->Cell(100, 8, "Total", "T", 0, 'C');
	$pdf->Cell(40, 8, "", "T", 0, 'C');
	$pdf->Cell(60, 8, "$" . number_format($report_data->total_submission_api, 2), "T", 1, 'C');
}


if (in_array('Issued', $desc) && count($report_data->issued_deals) > 0) {
	$pdf->SetFont('Helvetica', '', 14);
	$pdf->SetFillColor(224, 224, 224);
	$pdf->SetTextColor(0, 0, 0);
	$pdf->Cell(200, 10, 'Issued Deals', 0, 1, 'L', 'true');

	$pdf->SetFillColor(0, 0, 0);
	$pdf->SetTextColor(0, 0, 0);

	$pdf->SetFont('Helvetica', 'U', 13);

	$pdf->Cell(100, 8, 'Life Insured', 0, 0, 'C');
	$pdf->Cell(40, 8, 'Date Issued', 0, 0, 'C');
	$pdf->Cell(60, 8, 'Issued API', 0, 1, 'C');

	$pdf->SetFont('Helvetica', '', 12);
	$pdf->SetFillColor(242, 242, 242);

	usort($report_data->issued_deals, "sortFunction");
	foreach ($report_data->issued_deals as $index => $deal) {
		extract($deal);
		$fill = (($index % 2) === 0) ? true : false;
		$pdf->Row(array($client, $date_helper->NZEntryToDateTime($date), "$" . number_format($api, 2)), $fill, array(242, 242, 242));
	}
	$pdf->SetDrawColor(0, 0, 0);
	$pdf->SetFont('Helvetica', 'U', 10);
	$pdf->Cell(100, 8, "Total", "T", 0, 'C');
	$pdf->Cell(40, 8, "", "T", 0, 'C');
	$pdf->Cell(60, 8, "$" . number_format($report_data->total_issued_api, 2), "T", 1, 'C');
}


if (in_array('Cancelled', $desc) && count($report_data->cancelled_deals) > 0) {
	$pdf->SetFont('Helvetica', '', 14);
	$pdf->SetFillColor(224, 224, 224);
	$pdf->SetTextColor(0, 0, 0);
	$pdf->Cell(200, 10, 'Cancelled Deals', 0, 1, 'L', 'true');

	$pdf->SetFont('Helvetica', 'U', 13);
	$pdf->SetFillColor(0, 0, 0);
	$pdf->SetTextColor(0, 0, 0);
	$pdf->Cell(100, 8, 'Life Insured', 0, 0, 'C');
	$pdf->Cell(40, 8, 'Cancellation Date', 0, 0, 'C');
	$pdf->Cell(60, 8, 'Cancellation API', 0, 1, 'C');

	$pdf->SetFont('Helvetica', '', 12);
	$pdf->SetFillColor(242, 242, 242);

	usort($report_data->cancelled_deals, "sortFunction");
	foreach ($report_data->cancelled_deals  as $index =>  $deal) {
		extract($deal);
		$fill = (($index % 2) === 0) ? true : false;
		$pdf->Row(array($client, $date_helper->NZEntryToDateTime($date), "$" . number_format($api, 2)), $fill, array(242, 242, 242));
	}
	$pdf->SetDrawColor(0, 0, 0);
	$pdf->SetFont('Helvetica', 'U', 10);
	$pdf->Cell(100, 8, "Total", "T", 0, 'C');
	$pdf->Cell(40, 8, "", "T", 0, 'C');
	$pdf->Cell(60, 8, "$" . number_format($report_data->total_cancelled_api, 2), "T", 1, 'C');
}

if (in_array('Pending', $desc) && count($report_data->pending_deals) > 0) {
	$pdf->SetFont('Helvetica', '', 14);
	$pdf->SetFillColor(224, 224, 224);
	$pdf->SetTextColor(0, 0, 0);
	$pdf->Cell(200, 10, 'Pending Deals', 0, 1, 'L', 'true');

	$pdf->SetFont('Helvetica', 'U', 13);
	$pdf->SetFillColor(0, 0, 0);
	$pdf->SetTextColor(0, 0, 0);
	$pdf->Cell(100, 8, 'Life Insured', 0, 0, 'C');
	$pdf->Cell(40, 8, 'Submission Date', 0, 0, 'C');
	$pdf->Cell(60, 8, 'Original API', 0, 1, 'C');

	$pdf->SetFont('Helvetica', '', 12);
	$pdf->SetFillColor(242, 242, 242);


	usort($report_data->pending_deals, "sortFunction");
	foreach ($report_data->pending_deals  as $index =>  $deal) {
		extract($deal);
		$fill = (($index % 2) === 0) ? true : false;
		$pdf->Row(array($client, $date_helper->NZEntryToDateTime($date), "$" . number_format($api, 2)), $fill, array(242, 242, 242));
	}
	$pdf->SetDrawColor(0, 0, 0);
	$pdf->SetFont('Helvetica', 'U', 10);
	$pdf->Cell(100, 8, "Total", "T", 0, 'C');
	$pdf->Cell(40, 8, "", "T", 0, 'C');
	$pdf->Cell(60, 8, "$" . number_format($report_data->total_pending_api, 2), "T", 1, 'C');
}

if (in_array('KiwiSaver', $desc) && count($report_data->kiwisaver_deals) > 0) {
	$pdf->setAligns("C", "C", "C", "C", "C");
	$pdf->SetFont('Helvetica', '', 14);
	$pdf->SetFillColor(224, 224, 224);
	$pdf->SetTextColor(0, 0, 0);
	$pdf->Cell(200, 10, 'KiwiSaver Deals', 0, 1, 'L', 'true');

	$pdf->SetFont('Helvetica', 'U', 13);
	$pdf->SetFillColor(0, 0, 0);
	$pdf->SetTextColor(0, 0, 0);
	$pdf->Cell(65, 8, 'Name', 0, 0, 'C');
	$pdf->Cell(30, 8, 'Issue Date', 0, 0, 'C');
	$pdf->Cell(35, 8, 'Commission', 0, 0, 'C');
	$pdf->Cell(35, 8, 'GST', 0, 0, 'C');
	$pdf->Cell(35, 8, 'Balance', 0, 1, 'C');

	$pdf->SetFont('Helvetica', '', 12);
	$pdf->SetFillColor(242, 242, 242);

	$pdf->setWidths(array(65, 30, 35, 35, 35));

	usort($report_data->kiwisaver_deals, "sortFunction");
	foreach ($report_data->kiwisaver_deals  as $index =>  $deal) {
		extract($deal);
		$fill = (($index % 2) === 0) ? true : false;
		$pdf->Row(array($insured, $date_helper->NZEntryToDateTime($date), "$" . number_format($commission, 2), "$" . number_format($gst, 2), "$" . number_format($balance, 2)), $fill, array(242, 242, 242));
	}
	$pdf->SetDrawColor(0, 0, 0);
	$pdf->SetFont('Helvetica', 'U', 10);
	$pdf->Cell(65, 8, "Total", "T", 0, 'C');
	$pdf->Cell(30, 8, "", "T", 0, 'C');
	$pdf->Cell(35, 8, "$" . number_format($report_data->total_kiwisaver_commission, 2), "T", 0, 'C');
	$pdf->Cell(35, 8, "$" . number_format($report_data->total_kiwisaver_gst, 2), "T", 0, 'C');
	$pdf->Cell(35, 8, "$" . number_format($report_data->total_kiwisaver_balance, 2), "T", 1, 'C');
}

$pdf->setWidths(array(140, 60));
if (count($report_data->assigned_bdm_leads) > 0) {
	$pdf->SetFont('Helvetica', '', 14);
	$pdf->SetFillColor(224, 224, 224);
	$pdf->SetTextColor(0, 0, 0);
	$pdf->Cell(200, 10, 'Assigned Face-to-Face Marketer Leads', 0, 1, 'L', 'true');

	$pdf->SetFont('Helvetica', 'U', 13);
	$pdf->SetFillColor(0, 0, 0);
	$pdf->SetTextColor(0, 0, 0);
	$pdf->Cell(140, 10, 'Client', 0, 0, 'C');
	$pdf->Cell(60, 10, 'Assigned Date', 0, 1, 'C');

	$pdf->SetFont('Helvetica', '', 12);
	$pdf->SetFillColor(242, 242, 242);


	usort($report_data->assigned_bdm_leads, "sortFunction");

	foreach ($report_data->assigned_bdm_leads  as $index =>  $lead) {
		extract($lead);
		$fill = (($index % 2) === 0) ? true : false;
		$pdf->Row(array($name, $date_helper->NZEntryToDateTime($date)), $fill, array(242, 242, 242));
	}

	$pdf->SetDrawColor(0, 0, 0);
	$pdf->SetFont('Helvetica', 'U', 10);
	$pdf->Cell(140, 8, "Total", "T", 0, 'C');
	$pdf->Cell(60, 8, count($report_data->assigned_bdm_leads) . " leads", "T", 1, 'C');
}

if (count($report_data->assigned_telemarketer_leads) > 0) {
	$pdf->SetFont('Helvetica', '', 14);
	$pdf->SetFillColor(224, 224, 224);
	$pdf->SetTextColor(0, 0, 0);
	$pdf->Cell(200, 10, 'Assigned Telemarketer Leads', 0, 1, 'L', 'true');

	$pdf->SetFont('Helvetica', 'U', 13);
	$pdf->SetFillColor(0, 0, 0);
	$pdf->SetTextColor(0, 0, 0);
	$pdf->Cell(140, 10, 'Client', 0, 0, 'C');
	$pdf->Cell(60, 10, 'Assigned Date', 0, 1, 'C');

	$pdf->SetFont('Helvetica', '', 12);
	$pdf->SetFillColor(242, 242, 242);


	usort($report_data->assigned_telemarketer_leads, "sortFunction");

	foreach ($report_data->assigned_telemarketer_leads  as $index => $lead) {
		extract($lead);
		$fill = (($index % 2) === 0) ? true : false;
		$pdf->Row(array($name, $date_helper->NZEntryToDateTime($date)), $fill, array(242, 242, 242));
	}

	$pdf->SetDrawColor(0, 0, 0);
	$pdf->SetFont('Helvetica', 'U', 10);
	$pdf->Cell(140, 8, "Total", "T", 0, 'C');
	$pdf->Cell(60, 8, count($report_data->assigned_telemarketer_leads) . " leads", "T", 1, 'C');
}

if (count($report_data->assigned_self_generated_leads) > 0) {
	$pdf->SetFont('Helvetica', '', 14);
	$pdf->SetFillColor(224, 224, 224);
	$pdf->SetTextColor(0, 0, 0);
	$pdf->Cell(200, 10, 'Self-Generated Leads', 0, 1, 'L', 'true');

	$pdf->SetFont('Helvetica', 'U', 13);
	$pdf->SetFillColor(0, 0, 0);
	$pdf->SetTextColor(0, 0, 0);
	$pdf->Cell(140, 10, 'Client', 0, 0, 'C');
	$pdf->Cell(60, 10, 'Assigned Date', 0, 1, 'C');

	$pdf->SetFont('Helvetica', '', 12);
	$pdf->SetFillColor(242, 242, 242);


	usort($report_data->assigned_self_generated_leads, "sortFunction");

	foreach ($report_data->assigned_self_generated_leads as $index => $lead) {
		extract($lead);
		$fill = (($index % 2) === 0) ? true : false;
		$pdf->Row(array($name, $date_helper->NZEntryToDateTime($date)), $fill, array(242, 242, 242));
	}

	$pdf->SetDrawColor(0, 0, 0);
	$pdf->SetFont('Helvetica', 'U', 10);
	$pdf->Cell(140, 8, "Total", "T", 0, 'C');
	$pdf->Cell(60, 8,  count($report_data->assigned_self_generated_leads) . " leads", "T", 1, 'C');
}

$mix = "";
//$mix="$mydateorig"."-$name-"."BCTI".-date("ymd");
//$path="files/".$mix.".pdf";


$preview = "adviser_report_" . md5(uniqid());
$path = "files/$preview" . "_preview.pdf";
$pdf->Output($path, 'F');
//$pdf->Output();

ob_end_clean();
//OUTPUT 
$file = array();
$file['test'] = $kiwisaver_deals;
$file['adviser_id'] = $adviser_id;
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

