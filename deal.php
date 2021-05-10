<?php
date_default_timezone_set('Pacific/Auckland');
ob_start();
require("fpdf/mc_table.php");






require("database.php");
require_once "libs/indet_dates_helper.php";
require_once "libs/indet_alphanumeric_helper.php";

$date_helper = new INDET_DATES_HELPER();
require_once 'libs/Chart.helper.php';
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
$report_id = $_GET["id"];
$query="SELECT * FROM deals_report WHERE id='$report_id'";
$result=mysqli_query($con,$query) or die('Could not look up user information; ' . mysqli_error($con));
$report = mysqli_fetch_assoc($result);
extract($report);

$desc=json_decode($filterdata);		
$until = $date_to;
//Fetch Adviser Data
$searchadv="SELECT *, a.name as name, t.name as team_name FROM adviser_tbl a LEFT JOIN teams t ON a.team_id = t.id WHERE a.id='$adviser_id'";
$search=mysqli_query($con,$searchadv) or die('Could not look up user information; ' . mysqli_error($con));
$rows = mysqli_fetch_array($search);

$adviser_name = $rows["name"];
$adv_name = $adviser_name;

$team = $rows["team_name"];
if (empty($team))
	$team = "Not Assigned";


$d1 = new DateTime($date_from); // Y-m-d
$d2 = new DateTime($until);

$period_covered_title = $d1->format('d/m/Y') . "-" . $d2->format('d/m/Y');

$report_data = json_decode($report_data);

$search_issued="SELECT * FROM issued_clients_tbl i LEFT JOIN clients_tbl c ON i.name = c.id WHERE c.assigned_to='$adviser_id' AND  i.date_issued<='$until' AND i.date_issued>=$date_from AND c.lead_by!='Telemarketer'";
//Remove c.lead_by!='Telemarketer' to include leads from telemarketers
$issued_exec=mysqli_query($con,$search_issued) or die('Could not look up user information; ' . mysqli_error($con));
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

$show_desc='';


//set the coordinates x1,y1,x2,y2 of the gradient (see linear_gradient_coords.jpg)
$coords=array(0, 0,1,1);

$report_data->deals_graph = json_decode(json_encode($report_data->deals_graph),true);
$report_data->api_graph = json_decode(json_encode($report_data->api_graph),true);
$report_data->colors = json_decode(json_encode($report_data->colors),true);
$report_data->dash_values = json_decode(json_encode($report_data->dash_values),true);
$report_data->dash_indexes = json_decode(json_encode($report_data->dash_indexes),true);

//paint a linear gradient
$pdf->SetFont('Helvetica','B',15);
$pdf->SetFillColor(224,224,224);

$chartHelper = new ChartHelper();
$x_labels_array = array();
foreach($report_data->submissions_in_pool as $key => $value){
	$x_labels_array[] = $key;
}

$maroon = array(150, 50, 50);
$blue_green = array(0, 75, 0);
$dark_blue = array(0, 0, 150);
$violet = array(150, 50, 150);
$gray = array(100, 100, 100);

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

$report_data->cancellation_rate = StdClassToArray($report_data->cancellation_rate);
$report_data->proficiency = StdClassToArray($report_data->proficiency);


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

$report_data->submission_apis_in_pool = StdClassToArray($report_data->submission_apis_in_pool);
$report_data->issued_apis_in_pool = StdClassToArray($report_data->issued_apis_in_pool);
$report_data->cancellation_apis_in_pool = StdClassToArray($report_data->cancellation_apis_in_pool);
$report_data->kiwisavers_apis_in_pool = StdClassToArray($report_data->kiwisavers_apis_in_pool);
$report_data->issued_in_pool = StdClassToArray($report_data->issued_in_pool);
$report_data->proficiency = StdClassToArray($report_data->proficiency);
$report_data->cancellation_rate = StdClassToArray($report_data->cancellation_rate);

foreach ($period_array as $index => $period) {
	$fill = (($index % 2) === 0) ? true : false;
	$pdf->Row(array($period, Currency(
		$report_data->submission_apis_in_pool[$period]), 
		Currency($report_data->issued_apis_in_pool[$period]), 
		Currency($report_data->cancellation_apis_in_pool[$period]), 
		Currency($report_data->kiwisavers_apis_in_pool[$period]), 
		Currency($report_data->proficiency[$period]),  
		$report_data->cancellation_rate[$period] . "%"), $fill, array(242, 242, 242));
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
	$report_data->submissions = StdClassToArray($report_data->submissions);
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

	$report_data->issued_deals = StdClassToArray($report_data->issued_deals);
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

	$report_data->cancelled_deals = StdClassToArray($report_data->cancelled_deals);
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


	$report_data->pending_deals = StdClassToArray($report_data->pending_deals);
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

	$report_data->kiwisaver_deals = StdClassToArray($report_data->kiwisaver_deals);
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


	$report_data->assigned_bdm_leads = StdClassToArray($report_data->assigned_bdm_leads);
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


	$report_data->assigned_telemarketer_leads = StdClassToArray($report_data->assigned_telemarketer_leads);
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


	$report_data->assigned_self_generated_leads = StdClassToArray($report_data->assigned_self_generated_leads);
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

$path="files/preview.pdf";
//$pdf->Output($path,'F');
$pdf->Output("I", 'Adviser ' . $report_data->report_type . ' Production Report ' . $period_covered_title . '.pdf');




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

function StdClassToArray($class){
	$class = json_encode($class);
	return json_decode($class,true);
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


?>