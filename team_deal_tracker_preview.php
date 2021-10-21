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
		$this->Cell(100,10,'Policy Tracker-' . $name,0,0,'L');
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
$team=isset($_POST['team'])?$_POST['team']:'';				//Adviser name

$advisers=isset($_POST['advisers'])?$_POST['advisers']:'';		//Adviser id
$date_from=isset($_POST['date_from'])?$_POST['date_from']:'';			//Date from
$date_created=isset($_POST['date_created'])?$_POST['date_created']:'';	//Invoice Date
$pay_date=isset($_POST['pay_date'])?$_POST['pay_date']:'';				//Due date
$until=isset($_POST['until'])?$_POST['until']:'';				//Due date
$created_by = $_SESSION["myuserid"];

$advisers = json_decode($advisers, true);
$adviser_ids = implode(",", $advisers);

$team_query = "Select * from teams where id = $team";
$team_result = mysqli_query($con,$team_query) or die('Could not look up user information; ' . mysqli_error($con));
$team_row = mysqli_fetch_array($team_result);

$creator_query = "Select * from users where id = $created_by";
$creator_result = mysqli_query($con,$creator_query) or die('Could not look up user information; ' . mysqli_error($con));
$creator_row = mysqli_fetch_array($creator_result);


$created_by = $userController->getUserWithData($created_by)["full_name"];
var_dump($created_by);
//Production Desc
//Test Desc
//$desc=$_POST['desc'];
$date_created = date("d/m/Y");

$name = $team_row["name"];

$report_data = new stdClass();
$report_data->advisers = array();
$report_data->team = $team_row;
$report_data->adviser_ids = $adviser_ids;
$date_from=substr($date_from,6,4).substr($date_from,3,2).substr($date_from,0,2);
$until=substr($until,6,4).substr($until,3,2).substr($until,0,2);


$d1 = new DateTime($date_from); // Y-m-d
$d2 = new DateTime($until);

$period_covered_title = $d1->format('d/m/Y') . "-" . $d2->format('d/m/Y');


//fetch deals

$report_data->total_kiwisaver_commission = 0;
$report_data->total_kiwisaver_gst = 0;
$report_data->total_kiwisaver_balance = 0;
$report_data->kiwisaver_deals = [];

$dealController = new DealController();

if($name == "EliteInsure Team"){
	$kiwisaver_deals = $dealController->GetKiwiSaversIssuedInDateRange($date_from, $until);
}
else{
	$kiwisaver_deals = $dealController->GetKiwiSaversIssuedByTeamInDateRange($team, $date_from, $until);
}

$report_data->kiwisaver_adviser_deals = [];

while ($row = $kiwisaver_deals->fetch_assoc()) {
	if(!isset($report_data->kiwisaver_deals[$row["adviser_name"]])){
		$report_data->kiwisaver_deals[$row["adviser_name"]] = array(
			"adviser_name" => $row["adviser_name"],
			"enrolments" => 1,
			"commission" => $row["commission"],
			"gst" => $row["gst"],
			"balance" => $row["balance"],
			"date" => date("d/m/Y", strtotime($row["issue_date"]))
		);
	}
	else{
		$report_data->kiwisaver_deals[$row["adviser_name"]]["enrolments"] ++;
		$report_data->kiwisaver_deals[$row["adviser_name"]]["commission"] += $row["commission"];
		$report_data->kiwisaver_deals[$row["adviser_name"]]["gst"] += $row["gst"];
		$report_data->kiwisaver_deals[$row["adviser_name"]]["balance"] += $row["balance"];
	}

	$report_data->total_kiwisaver_commission += $row["commission"];
	$report_data->total_kiwisaver_gst += $row["gst"];
	$report_data->total_kiwisaver_balance += $row["balance"];
}



$report_data->period_covered = $period_covered_title;

//fetch deals
$search_leads="SELECT *, c.name as client_name, a.name as adv_name, l.name as source FROM clients_tbl c LEFT JOIN submission_clients s ON c.id = s.client_id LEFT JOIN leadgen_tbl l ON l.id = c.leadgen LEFT JOIN adviser_tbl a ON a.id = c.assigned_to WHERE c.assigned_to IN ($report_data->adviser_ids) AND c.status!='Cancelled' ORDER BY adv_name";
//echo $search_leads . "<hr>";
$leads_exec =mysqli_query($con,$search_leads) or die('Could not look up user information; ' . mysqli_error($con));

$report_data->total_pending_api = 0;
$report_data->total_issued_api = 0;
$report_data->total_cancelled_api = 0;
$report_data->pending_deals = [];
$report_data->issued_deals = [];
$report_data->cancelled_deals = [];

 while($row = mysqli_fetch_array($leads_exec)){
 	if(!isset($row["deals"]))
 		continue;
 	$source = $row["adv_name"];

 	$deals = json_decode($row["deals"]);

 	foreach($deals as $deal){
		 $life_insured = $row["client_name"];
		 if(!isset($deal->refund_status))
			 $deal->refund_status = "No";

 		if(!empty($deal->life_insured))
 			$life_insured .= ", " . $deal->life_insured;


 		if($deal->status=="Issued"){
			if($deal->commission_status=="Not Paid"){
				if($deal->date_issued <= $until){
					$report_data->issued_deals[] = array(
							 "date" => $deal->date_issued,
							 "life_insured" => $life_insured,
							 "policy_number" => $deal->policy_number,
							 "company" => $deal->company,
							 "source" => $source,
							 "api" => $deal->issued_api,
							 "compliance_status" => $deal->compliance_status,
							 "notes" => $deal->notes,
							 "deal" => $deal,
						 );
					$report_data->total_issued_api += $deal->issued_api;
				}
			}

 			//Add to Cancelled Deals
 			if(isset($deal->clawback_status)){
	 			if($deal->clawback_status!="None"){
					if($deal->refund_status=="No"){
						if($deal->clawback_date<=$until){
							$report_data->cancelled_deals[] = array(
								 "date" => $deal->clawback_date,
								 "life_insured" => $life_insured,
								 "issued_date" => $deal->date_issued,
								 "policy_number" => $deal->policy_number,
								 "company" => $deal->company,
								 "source" => $source,
								 "api" => $deal->clawback_api,
								 "clawback_status" => $deal->clawback_status,
								 "notes" => $deal->clawback_notes,
								 "deal" => $deal,
							 );

							$report_data->total_cancelled_api += $deal->clawback_api;
						}
					}
 				}
 			}
 		}
 	}
 }

$query = "SELECT * FROM clients_tbl WHERE assigned_to IN ('$report_data->adviser_ids') AND date_submitted>=$date_from AND date_submitted<=$until";
$displayquery=mysqli_query($con,$query) or die('Could not look up user information; ' . mysqli_error($con));
$report_data->assigned_leads_for_period = mysqli_num_rows($displayquery);

$query = "SELECT * FROM issued_clients_tbl WHERE assigned_to IN ('$report_data->adviser_ids') AND date_issued>=$date_from AND date_issued<=$until";
$displayquery=mysqli_query($con,$query) or die('Could not look up user information; ' . mysqli_error($con));
$report_data->issued_leads_for_period = mysqli_num_rows($displayquery);



$search_issued="SELECT * FROM issued_clients_tbl i LEFT JOIN clients_tbl c ON i.name = c.id WHERE c.assigned_to IN ('$report_data->adviser_ids') AND  i.date_issued<='$until' AND i.date_issued>=$date_from AND c.lead_by!='Telemarketer'";
//Remove c.lead_by!='Telemarketer' to include leads from telemarketers
$issued_exec=mysqli_query($con,$search_issued) or die('Could not look up user information; ' . mysqli_error($con));
$count_issued = mysqli_num_rows($issued_exec);


$pdf = new PDF('P','mm','Legal');
$x = $pdf->GetX();
$y = $pdf->GetY();

//page 1
$pdf->AddPage('P', 'Legal');


$pdf->SetFillColor(224,224,224);
$pdf->SetFont('Helvetica','B',20);
$report_title = ($report_data->team["name"]!="EliteInsure Team") ? 'Team ' . $report_data->team["name"] : $report_data->team["name"];
$pdf->Cell(200,10,$report_title . ' Policy Tracker Summary',"0","1","C",'true');

$pdf->SetFont('Helvetica','B',15);
$pdf->Cell(42,10,"Period Covered:","0","0","L");
$pdf->SetFont('Helvetica','',15);
$pdf->Cell(53,10,"$period_covered_title","0","0","L");

$pdf->Cell(5,10,'',"0","0","R");
$pdf->SetFont('Helvetica','B',15);
$pdf->Cell(25,10,"Pay Date:","0","0","L");
$pdf->SetFont('Helvetica','',15);
$pdf->Cell(75,10,"$pay_date","0","1","L");


$pdf->SetFont('Helvetica','B',15);
$pdf->Cell(28,10,"Report By:","0","0","L");
$pdf->SetFont('Helvetica','',15);
$pdf->Cell(67,10,"$created_by","0","0","L");
$pdf->Cell(5,10,'',"0","0","R");
$pdf->SetFont('Helvetica','B',15);
$pdf->Cell(35,10,"Date Created:","0","0","L");
$pdf->SetFont('Helvetica','',15);
$pdf->Cell(65,10,"$date_created","0","1","L");

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

if(count($report_data->cancelled_deals) > 0)
{
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
	if(count($report_data->kiwisaver_deals) > 0){

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
		$pdf->Row(array("Adviser Name", "Enrolments","Commission", "GST", "Balance"), false, array(224, 224, 224));

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

//page 1
$preview = "deal_tracker_" . md5(uniqid());
$path="files/$preview" . "_preview.pdf";
$pdf->Output($path,'F');
//$pdf->Output();

ob_end_clean();
//OUTPUT
$file=array();
$file['team_id']=$team;
$file['link']=$path;
$file['filename']=$mix;
$file['report_data'] = json_encode($report_data);
$file['from'] = $date_from;
$file['pay_date'] = DateTimeToNZEntry($pay_date);
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
