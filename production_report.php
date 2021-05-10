<?php
date_default_timezone_set('Pacific/Auckland');
ob_start();
require("fpdf/mc_table.php");

require("database.php");
require_once "libs/indet_dates_helper.php";
require_once "libs/indet_alphanumeric_helper.php";
require_once 'libs/Chart.helper.php';

$date_helper = new INDET_DATES_HELPER();
$alphanumeric_helper = new INDET_ALPHANUMERIC_HELPER();

class PDF extends PDF_MC_TABLE
{
	function Footer()
	{
		global $fsp_num;
		global $name;
		$this->SetY(-15);
		$this->SetFont('Helvetica','',10);
		$this->SetTextColor(0,0,0);
		$this->Cell(100,10,'Team Production Report '. ''.' '.preg_replace("/\([^)]+\)/","",''),0,0,'L');	
		$this->AliasNbPages('{totalPages}');	
		$this->Cell(110,10,'Page '.$this->PageNo() . " of " . "{totalPages}",0,1,'R');
	}

	function Header(){
		$this->SetFillColor(224,224,224);
		$this->Image('logo.png',10,10,-160);
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

//retrieving
$report_id = $_GET["id"];
$query="SELECT * FROM deals_report WHERE id='$report_id'";
$result=mysqli_query($con,$query) or die('Could not look up user information; ' . mysqli_error($con));
$report = mysqli_fetch_assoc($result);
extract($report);


$desc=json_decode($filterdata);		
$until = $date_to;		
//Test Desc
//$desc=$_POST['desc'];		
$date_created = date("d/m/Y");
$statementweek=date("d/m/Y");											//Statement Week

$d1 = new DateTime($date_from); // Y-m-d
$d2 = new DateTime($until);

$period_covered_title = $d1->format('d/m/Y') . "-" . $d2->format('d/m/Y');
$report_data = json_decode($report_data);

//Fetch Adviser Data
$searchadv="SELECT * FROM adviser_tbl ORDER BY name";
$search=mysqli_query($con,$searchadv) or die('Could not look up user information; ' . mysqli_error($con));

//fetch deals



$pdf = new PDF('P', 'mm', 'Legal');
$x = $pdf->GetX();
$y = $pdf->GetY();

//page 1
$pdf->AddPage('P', 'Legal');

$pdf->SetFillColor(224,224,224);
$pdf->SetFont('Helvetica','B',20);
$pdf->Cell(200,10,'Team ' . $report_data->report_type . ' Production Report',"0","1","C",'true');

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

//$pdf->Row(array("Number of Deals", $report_data->submissions_count, "$" . number_format($api, 2)), $fill, array(242, 242, 242));

$report_data->deals_graph = json_decode(json_encode($report_data->deals_graph),true);
$report_data->api_graph = json_decode(json_encode($report_data->api_graph),true);
$report_data->colors = json_decode(json_encode($report_data->colors),true);
$report_data->dash_values = json_decode(json_encode($report_data->dash_values),true);
$report_data->dash_indexes = json_decode(json_encode($report_data->dash_indexes),true);

$grad1=array(129,129,184);
$grad2=array(225,225,225);

//set the coordinates x1,y1,x2,y2 of the gradient (see linear_gradient_coords.jpg)
$coords=array(0, 0,1,1);

//paint a linear gradient
$pdf->SetFillColor(224,224,224);
$pdf->SetFont('Helvetica','B',12);

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


$chartHelper = new ChartHelper();
$x_labels_array = array();
foreach ($report_data->submissions_in_pool as $key => $value) {
	$x_labels_array[] = $key;
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
$pdf->Cell(30, 8, Currency($report_data->total_kiwisavers_commission), "T", 0, 'C');
$pdf->Cell(30, 8, Currency(GetProficiency($report_data->total_issued_api, count($report_data->issued_in_pool))), "T", 0, 'C');
$pdf->Cell(40, 8, GetCancellationRate($report_data->total_cancelled_api, $report_data->total_issued_api) . "%", "T", 1, 'C');

$pdf->SetMCFonts(array(
	array('Helvetica', '', 10),
	array('Helvetica', '', 10)
));
$pdf->setWidths(array(100, 40, 60));
$pdf->setAligns("C", "C", "C");


if($report_data->submissions_count>0){
	$pdf->SetFont('Helvetica','B',14);
	$pdf->SetFillColor(224,224,224);
	$pdf->SetTextColor(0,0,0);
	$pdf->Cell(200,10,'Submissions', 0, 1,'C','true');

	$pdf->SetFont('Helvetica','U',12);
	$pdf->SetFillColor(0,0,0);
	$pdf->SetTextColor(0,0,0);
	$pdf->Cell(100,10,'Adviser', 0, 0,'L');
	$pdf->Cell(43,10,'No. of Deals', 0, 0,'L');
	$pdf->Cell(60,10,'API', 0, 1,'L');

	$pdf->SetFont('Helvetica','',12);

	foreach($report_data->advisers as $adviser){
		if(count($adviser->submissions)>0){
			$pdf->Cell(100,10,$adviser->name, "0", 0,'L');
			$pdf->Cell(43,10,count($adviser->submissions), 0, 0,'L');
			$pdf->Cell(60,10,"$" . number_format($adviser->total_submission_api,2), 0, 1,'L');
		}
	}
	$pdf->Cell(100,10,"", "T", 0,'L');
	$pdf->Cell(43,10,"", "T", 0,'L');
	$pdf->Cell(60,10,"$" . number_format($report_data->period_submission_api,2), "T", 1,'L');
}

if($report_data->issued_count>0){
	$pdf->SetFont('Helvetica','B',14);
	$pdf->SetFillColor(224,224,224);
	$pdf->SetTextColor(0,0,0);
	$pdf->Cell(200,10,'Policies Issued', 0, 1,'C','true');

	$pdf->SetFont('Helvetica','U',12);
	$pdf->SetFillColor(0,0,0);
	$pdf->SetTextColor(0,0,0);
	$pdf->Cell(100,10,'Adviser', 0, 0,'L');
	$pdf->Cell(43,10,'No. of Policies', 0, 0,'L');
	$pdf->Cell(60,10,'API', 0, 1,'L');

	$pdf->SetFont('Helvetica','',12);

	foreach($report_data->advisers as $adviser){
		if(count($adviser->issued_deals)>0){
			$pdf->Cell(100,10,$adviser->name, "0", 0,'L');
			$pdf->Cell(43,10,count($adviser->issued_deals), 0, 0,'L');
			$pdf->Cell(60,10,"$" . number_format($adviser->total_issued_api,2), 0, 1,'L');			
		}
	}
	$pdf->Cell(100,10,"", "T", 0,'L');
	$pdf->Cell(43,10,"", "T", 0,'L');
	$pdf->Cell(60,10,"$" . number_format($report_data->period_issued_api,2), "T", 1,'L');
}


if($report_data->cancellations_count>0){
	$pdf->SetFont('Helvetica','B',14);
	$pdf->SetFillColor(224,224,224);
	$pdf->SetTextColor(0,0,0);
	$pdf->Cell(200,10,'Cancellations', 0, 1,'C','true');

	$pdf->SetFont('Helvetica','U',12);
	$pdf->SetFillColor(0,0,0);
	$pdf->SetTextColor(0,0,0);
	$pdf->Cell(100,10,'Adviser', 0, 0,'L');
	$pdf->Cell(43,10,'No. of Policies', 0, 0,'L');
	$pdf->Cell(60,10,'API', 0, 1,'L');

	$pdf->SetFont('Helvetica','',12);

	
	foreach($report_data->advisers as $adviser){
		if(!isset($adviser->cancelled_deals))
		continue;

		if(count($adviser->cancelled_deals)>0){
			$pdf->Cell(100,10,$adviser->name, "0", 0,'L');
			$pdf->Cell(43,10,count($adviser->cancelled_deals), 0, 0,'L');
			$pdf->Cell(60,10,"$" . number_format($adviser->total_cancelled_api,2), 0, 1,'L');
		}
	}
	$pdf->Cell(100,10,"", "T", 0,'L');
	$pdf->Cell(43,10,"", "T", 0,'L');
	$pdf->Cell(60,10,"$" . number_format($report_data->period_cancelled_api,2), "T", 1,'L');
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
		if(!isset($adviser->kiwisaver_deals))
		continue;
		
		if(is_array($adviser->kiwisaver_deals)){
			if (count($adviser->kiwisaver_deals) > 0) {
				$pdf->Cell(65, 10, $adviser->name, "0", 0, 'L');
				$pdf->Cell(30, 10, count($adviser->kiwisaver_deals), 0, 0, 'L');
				$pdf->Cell(35, 10, "$" . number_format($adviser->total_kiwisavers_commission, 2), 0, 0, 'L');
				$pdf->Cell(35, 10, "$" . number_format($adviser->total_kiwisavers_gst, 2), 0, 0, 'L');
				$pdf->Cell(35, 10, "$" . number_format($adviser->total_kiwisavers_balance, 2), 0, 1, 'L');
			}
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

$path="files/preview.pdf";
//$pdf->Output($path,'F');
$pdf->Output("I", 'Team ' . $report_data->report_type . ' Production Report ' . $period_covered_title . '.pdf');

//OUTPUT 
$file=array();
$file['adviser_id']="";
$file['link']=$path;
$file['filename']=$mix;
$file['description'] = $_POST['desc'];
$file['report_data'] = json_encode($report_data);
$file['from'] = $date_from;
$file['type'] = $_POST['type'];
$file['to'] = $until;
//$file['amount'] = $total_payable;
//$file['payable_leads'] = $payable_leads;
//$file['payable_issued_leads'] = $payable_issued_leads;
	
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
