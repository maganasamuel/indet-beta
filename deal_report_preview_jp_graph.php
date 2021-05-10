<?php
date_default_timezone_set('Pacific/Auckland');
ob_start();
require("fpdf/pdf_with_graph.php");






require("database.php");

require_once "libs/indet_dates_helper.php";
require_once "libs/indet_alphanumeric_helper.php";

$date_helper = new INDET_DATES_HELPER();
$alphanumeric_helper = new INDET_ALPHANUMERIC_HELPER();

/*
session_start();
*/
//post

class PDF extends PDF_With_Graph
{

	function Footer()
	{
		global $fsp_num;
		global $name;
		$this->SetY(-15);
		$this->SetFont('Helvetica','',10);
		$this->SetTextColor(0,0,0);
		$this->Cell(100,10,'Adviser Production Report '. ''.' '.preg_replace("/\([^)]+\)/","",''),0,0,'L');	
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
$adv_name=isset($_POST['adv_name'])?$_POST['adv_name']:'';				//Adviser name
$adviser_id=isset($_POST['adviser_id'])?$_POST['adviser_id']:'';		//Adviser id
$date_from=isset($_POST['date_from'])?$_POST['date_from']:'';			//Date from
$date_created=isset($_POST['date_created'])?$_POST['date_created']:'';	//Invoice Date
$due_date=isset($_POST['due_date'])?$_POST['due_date']:'';				//Due date
$until=isset($_POST['until'])?$_POST['until']:'';						//Date until
$report_schedule_type = $_POST['report_schedule_type'];
//Production Desc
$desc=json_decode($_POST['desc'],true);		
//Test Desc
//$desc=$_POST['desc'];		
$date_created = date("d/m/Y");
$statementweek=date("d/m/Y");											//Statement Week
$other_value=isset($_POST['other_value'])?$_POST['other_value']:0;		//Other

if($other_value==''){
	$other_value=0;
}

//Fetch Adviser Data
$searchadv="SELECT *, a.name as name, t.name as team_name FROM adviser_tbl a LEFT JOIN teams t ON a.team_id = t.id WHERE a.id='$adviser_id'";
$search=mysqli_query($con,$searchadv) or die('Could not look up user information; ' . mysqli_error($con));
$rows = mysqli_fetch_array($search);

//Extract Data
$fsp=$rows["fsp_num"];
$advisor_address=$rows["address"];
$leads=$rows["leads"];
$issued=$rows["bonus"];
$fsp_num=$rows['fsp_num'];
$email=$rows['email'];
$adviser_name = $rows["name"];
$team = $rows["team_name"];
if(empty($team))
	$team = "Not Assigned";

//GET BI MONTHLY DATA
$d_from = substr($date_from,6,4). "-" . substr($date_from,3,2). "-" . substr($date_from,0,2);
$d_to =substr($until,6,4). "-" . substr($until,3,2). "-" . substr($until,0,2);

$date_from=substr($date_from,6,4).substr($date_from,3,2).substr($date_from,0,2);
$until=substr($until,6,4).substr($until,3,2).substr($until,0,2);

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
	switch ($_POST['report_type']){
		case "Weekly":
			while($dateExceeded==false){

			$day = $date_helper->getDay($d3,$d2);
			////echo $bm->from->format('F, d Y') . "-" . $bm->to->format('F, d Y')."<br>";
			$days[] = $day;
			//$date_helper->get next day
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
			//$date_helper->get next day
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
			if($report_schedule_type == "Regular"){					
				while($dateExceeded==false){					
					$week = $date_helper->getWeek($d3,$d2);
					////echo $bm->from->format('F, d Y') . "-" . $bm->to->format('F, d Y')."<br>";
					$weeks[] = $week;
					//$date_helper->get next day
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
			}
			else{
				while($dateExceeded==false){					
					$week = $date_helper->getWeek($d3,$d2);
					////echo $bm->from->format('F, d Y') . "-" . $bm->to->format('F, d Y')."<br>";
					$weeks[] = $week;
					//$date_helper->get next day
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
					
					
					$months[] = $bm;
					$d3->modify('+ 1 day');

					if(!checkIfContinuing($d1,$d2,$d3))
						$dateExceeded = true;
				}
			}
		break;
		case "Specified":
			while($dateExceeded==false){
				
				$week = $date_helper->getWeek($d3,$d2);
				////echo $bm->from->format('F, d Y') . "-" . $bm->to->format('F, d Y')."<br>";
				$weeks[] = $week;
				//$date_helper->get next day
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
			if($report_schedule_type == "Regular"){
				while($dateExceeded==false){
					$bm = $date_helper->getMonth($d3->format('m'), $d3->format('Y'));
					////echo $bm->from->format('F, d Y') . "-" . $bm->to->format('F, d Y')."<br>";
					$months[] = $bm;
					$d3->modify('+ 1 month');
					if(!checkIfContinuing($d1,$d2,$d3))
						$dateExceeded = true;
				}		
			}
			else{
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
			}
		break;
		
		case "Quarterly":
						
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
			while($dateExceeded==false){
				$week = $date_helper->getWeek($d3,$d2);
				////echo $bm->from->format('F, d Y') . "-" . $bm->to->format('F, d Y')."<br>";
				$bi_months[] = $week;
				$weeks[] = $week;
				//$date_helper->get next day
				$d3 = clone $week->to;
				$d3 = $d3->modify('+1 day');

				if(!checkIfContinuing($d1,$d2,$d3))
					$dateExceeded = true;
			}
		break;

	}

	if($_POST['report_type']=="Quarterly"){
		$period_covered_title .= " of " . $d2->format('Y');
	}


function checkIfContinuing($from,$to,$next_date){
  return (($next_date >= $from) && ($next_date <= $to));
}


//fetch deals
$search_leads="SELECT *, c.id as cl_id, c.name as cl_name FROM clients_tbl c LEFT JOIN submission_clients s ON c.id = s.client_id WHERE assigned_to='$adviser_id' AND c.status!='Cancelled'";
//echo $search_leads . "<hr>";
$leads_exec =mysqli_query($con,$search_leads) or die('Could not look up user information; ' . mysqli_error($con));
$report_data = new stdClass();
$report_data->total_pending_api = 0;
$report_data->total_issued_api = 0;
$report_data->total_cancelled_api = 0;
$report_data->total_submission_api = 0;
$report_data->pending_deals = [];
$report_data->issued_deals = [];
$report_data->cancelled_deals = [];
$report_data->submissions = [];

$report_data->assigned_bdm_leads = [];
$report_data->assigned_telemarketer_leads = [];
$report_data->assigned_self_generated_leads = [];

$report_data->dash_indexes = array();
$report_data->dash_values = array();
$report_data->colors = array();

//LINE GRAPH
$report_data->submissions_in_pool = array();
$report_data->issued_in_pool = array();
$report_data->cancellations_in_pool = array();

$report_data->submission_apis_in_pool = array();
$report_data->issued_apis_in_pool = array();
$report_data->cancellation_apis_in_pool = array();

$report_data->report_type = $_POST['report_type'];
	
 while($row = mysqli_fetch_array($leads_exec)){

	 //Add Lead to list if inside date range
	 if($row["assigned_date"]>=$date_from && $row["assigned_date"]<=$until){
			$lead = array(
				"id" => $row["cl_id"],
				"name" => $row["cl_name"],
				"date" => $row["assigned_date"],
			);

			if($row["lead_by"]=="Face-to-Face Marketer"){
				$report_data->assigned_bdm_leads[] = $lead;
			}
			elseif($row["lead_by"]=="Telemarketer"){
				$report_data->assigned_telemarketer_leads[] = $lead;
			}
			elseif($row["lead_by"]=="Self-Generated"){
				$report_data->assigned_self_generated_leads[] = $lead;
			}
	 }

 	if(!isset($row["deals"]))
 		continue;
	
 	$deals = json_decode($row["deals"]);

 	foreach($deals as $deal){
 		$life_insured = $row["name"];
 		if(!empty($deal->life_insured))
 			$life_insured .= ", " . $deal->life_insured;

 		//Add To Submissions
		 if($deal->submission_date>=$date_from && $deal->submission_date<=$until){
			$report_data->submissions[] = array(
						"client" => $life_insured,
						"date" => $deal->submission_date,
						"deal" => $deal,
						"api" => $deal->original_api,
					);
			$report_data->total_submission_api += $deal->original_api;
		}

 		//Add all pending deals
 		if($deal->status=="Pending"){
 			if($deal->submission_date<=$until){
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
		 elseif($deal->status=="Issued"){
				if($deal->date_issued>=$date_from && $deal->date_issued<=$until){
					$report_data->issued_deals[] = array(
									"client" => $life_insured,
									"date" => $deal->date_issued,
									"deal" => $deal,
									"api" => $deal->issued_api,
								);
					$report_data->total_issued_api += $deal->issued_api;
				}

				//Add to Cancelled Deals
				if(isset($deal->clawback_status)){
					if($deal->clawback_status=="Cancelled"){
						if($deal->clawback_date>=$date_from && $deal->clawback_date<=$until){
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

 switch($report_data->report_type){
	 case "Weekly":		

		 $pool = "days";
		 $term = "D";
	 break;
	 case "Bi-Monthly":
		 $pool = "weeks";
		 $term = "W";
	 break;
	 case "Monthly":
		 $pool = "weeks";
		 $term = "W";
	 break;
	 case "Specified":
		 $pool = "months";
		 $term = "M";
	 break;
	 case "Annual":
		 $pool = "months";
		 $term = "M";
	 break;
	 case "Quarterly":
		 $pool = "months";
		 $term = "M";
	 break;
 }

$ctr = 1;
//var_dump($$pool[1]);
foreach($$pool as $bm){
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
	 $bm_clients_result = mysqli_query($con,$bm_clients_query) or die('Could not look up user information; ' . mysqli_error($con));
	 
	 while($row = mysqli_fetch_array($bm_clients_result)){
		 if(!isset($row["deals"]))
			 continue;
	
		 $deals = json_decode($row["deals"]);

			foreach($deals as $deal){
			 $life_insured = $row["name"];
			 if(!empty($deal->life_insured))
				 $life_insured .= ", " . $deal->life_insured;
	
			 //Add To Submissions
			 if($deal->submission_date>=$bm_from && $deal->submission_date<=$bm_to){
					$bm_submissions++;
					$bm_submissions_api+=$deal->original_api;					 
				}
				
				//Add to Issued Deals
			 if($deal->status=="Issued"){
					if($deal->date_issued>=$bm_from && $deal->date_issued<=$bm_to){
						$bm_issued++;
						$bm_issued_api+=$deal->issued_api;					 
					}
					
					//Add to Cancelled Deals
					if(isset($deal->clawback_status)){
						if($deal->clawback_status=="Cancelled"){
											
							if($deal->clawback_date>=$bm_from && $deal->clawback_date<=$bm_to){
								$bm_cancellations++;
								$bm_cancellations_api+=$deal->clawback_api;					 
							}
						}
					}
				 
			 }
			 
		 }
	 }

	 $bm_date_to = "$term$ctr";
	 //$bm_date_to = "$term$ctr" . $bm->from->format('m/d/Y') . "-" . $bm->to->format('m/d/Y');

	 $report_data->submissions_in_pool[$bm_date_to] = $bm_submissions;
	 $report_data->issued_in_pool[$bm_date_to] = $bm_issued;
	 $report_data->cancellations_in_pool[$bm_date_to] = $bm_cancellations;

	 $report_data->submission_apis_in_pool[$bm_date_to] = $bm_submissions_api;
	 $report_data->issued_apis_in_pool[$bm_date_to] = $bm_issued_api;
	 $report_data->cancellation_apis_in_pool[$bm_date_to] = $bm_cancellations_api;
	 $ctr++;
 }	

//echo"Submissions:";
//var_dump($report_data->submissions_in_pool);
//deals
$report_data->deals_graph = array(
	'Submissions' => $report_data->submissions_in_pool,
	'Issued Policies' => $report_data->issued_in_pool,
	'Cancellations' => $report_data->cancellations_in_pool
 );

 //apis
 $report_data->api_graph = array(
	'Submissions' => $report_data->submission_apis_in_pool,
	'Issued Policies' => $report_data->issued_apis_in_pool,
	'Cancellations' => $report_data->cancellation_apis_in_pool
 );

$search_issued="SELECT * FROM issued_clients_tbl i LEFT JOIN clients_tbl c ON i.name = c.id WHERE c.assigned_to='$adviser_id' AND  i.date_issued<='$until' AND i.date_issued>=$date_from AND c.lead_by!='Telemarketer'";
//Remove c.lead_by!='Telemarketer' to include leads from telemarketers
$issued_exec=mysqli_query($con,$search_issued) or die('Could not look up user information; ' . mysqli_error($con));
$count_issued = mysqli_num_rows($issued_exec);


$pdf = new PDF('P', 'mm', 'Legal');
$x = $pdf->GetX();
$y = $pdf->GetY();

//page 1
$pdf->AddPage('P', 'Legal');

$pdf->SetFillColor(224,224,224);
$pdf->SetFont('Helvetica','B',20);
$pdf->Cell(200,10,'Adviser ' . $report_data->report_type . ' Production Report',"0","1","C",'true');
$pdf->SetFont('Helvetica','B',15);
$pdf->Cell(17,10,'Name:',"0","0","L");
$pdf->SetFont('Helvetica','',14);
$pdf->Cell(78,10,$adviser_name,"0","0","L");
$pdf->Cell(5,10,'',"0","0","R");
$pdf->SetFont('Helvetica','B',15);
$pdf->Cell(41,10,"Period Covered:","0","0","L");
$pdf->SetFont('Helvetica','',14);
$pdf->Cell(59,10,$period_covered_title,"0","1","L");
$pdf->SetFont('Helvetica','B',15);
$pdf->Cell(17,10,'Team:',"0","0","L");
$pdf->SetFont('Helvetica','',14);
$pdf->Cell(78,10,$team,"0","0","L");
$pdf->Cell(5,10,'',"0","0","R");
$pdf->SetFont('Helvetica','B',15);
$pdf->Cell(41,10,"","0","0","L");
$pdf->SetFont('Helvetica','',14);
$pdf->Cell(59,10,"","0","1","L");



$pdf->SetFont('Helvetica','',14);
$pdf->SetFillColor(224,224,224);
$pdf->Cell(100,10,'DESCRIPTION', 0, 0,'L','true');
$pdf->Cell(43,10,'TOTAL DEALS', 0, 0,'C','true');
$pdf->Cell(60,10,'TOTAL API', 0, 1,'C','true');
//formula


$total = 0;
if (in_array("Submission", $desc)) {
	$pdf->SetFont('Helvetica','',12);
	$pdf->Cell(100,10,"Submissions", 0, 0,'L');
	$pdf->Cell(43,10,count($report_data->submissions), 0, 0,'C');
	$pdf->Cell(60,10,'$'.number_format($report_data->total_submission_api,2), 0, 1,'C');
	$total += $report_data->total_submission_api;
}

if (in_array("Issued", $desc)) {
	$pdf->SetFont('Helvetica','',12);
	$pdf->Cell(100,10,"Issued Policies", 0, 0,'L');
	$pdf->Cell(43,10,count($report_data->issued_deals), 0, 0,'C');
	$pdf->Cell(60,10,'$'.number_format($report_data->total_issued_api,2), 0, 1,'C');
	$total += $report_data->total_issued_api;
}

if (in_array("Cancelled", $desc)) {
	$pdf->SetFont('Helvetica','',12);
	$pdf->Cell(100,10,"Cancellations", 0, 0,'L');
	$pdf->Cell(43,10,count($report_data->cancelled_deals), 0, 0,'C');
	$pdf->Cell(60,10,'$-'.number_format($report_data->total_cancelled_api,2), 0, 1,'C');
	$total -=  $report_data->total_cancelled_api;
}

if (in_array("Pending", $desc)) {
	$pdf->SetFont('Helvetica','',12);
	$pdf->Cell(100,10,"Pending Deals", 0, 0,'L');
	$pdf->Cell(43,10,count($report_data->pending_deals), 0, 0,'C');
	$pdf->Cell(60,10,'$'.number_format($report_data->total_pending_api,2), 0, 1,'C');
	$total += $report_data->total_pending_api;
}

//Space

$show_desc='';


$report_data->dash_indexes[] = 2;
$report_data->dash_values[2] = array(2,2);

$report_data->colors = array(
	'Submissions' => array(50,225,50),
	'Issued Policies' => array(50,50,225),
	'Cancellations' => array(255,50,50)
);

$grad1=array(129,129,184);
$grad2=array(225,225,225);

//set the coordinates x1,y1,x2,y2 of the gradient (see linear_gradient_coords.jpg)
$coords=array(0, 0,1,1);

//paint a linear gradient
$pdf->LinearGradient($x+10,$y+125,200,135,$grad1,$grad2,$coords);
$pdf->SetFont('Helvetica','B',15);
$pdf->SetFillColor(224,224,224);
$pdf->Cell(200,5,"",0, 1,'C',false);
$pdf->SetXY($x+10, $y+111);
$pdf->Cell(200,10,"Statistics",0, 1,'C',true);

$pdf->SetFont('Helvetica','B',12);

$pdf->Cell(200,5,"",0, 1,'C',false);
$pdf->Cell(200,10,"Deals Data",0, 1,'L',false);
$pdf->Cell(5,10,"",0, 0,'L',false);
$pdf->LineGraph(180,50,$report_data->deals_graph,'VHvBdB',$report_data->colors,6,3,$report_data->dash_indexes,$report_data->dash_values);

$pdf->Cell(1,10,"",0, 1,'L',false);
$pdf->SetXY($x+10, $y+190);
$pdf->SetFont('Helvetica','B',12);
$pdf->Cell(200,10,"API Data",0, 1,'L',false);
$pdf->Cell(5,10,"",0, 0,'L',false);
$pdf->LineGraph(180,50,$report_data->api_graph,'VHvBdB',$report_data->colors,6,3,$report_data->dash_indexes,$report_data->dash_values);


$pdf->SetXY($x+10, $y+265);


if(in_array('Submission', $desc) && count($report_data->submissions)>0){
	$pdf->SetFont('Helvetica','',14);
	$pdf->SetFillColor(224,224,224);
	$pdf->SetTextColor(0,0,0);
	$pdf->Cell(200,10,'Submissions', 0, 1,'L','true');

	$pdf->SetFont('Helvetica','U',12);
	$pdf->SetFillColor(0,0,0);
	$pdf->SetTextColor(0,0,0);
	$pdf->Cell(100,10,'Life Insured', 0, 0,'L');
	$pdf->Cell(40,10,'Submission Date', 0, 0,'L');
	$pdf->Cell(60,10,'Original API', 0, 1,'L');

	$pdf->SetFont('Helvetica','',12);

	usort($report_data->submissions, "sortFunction");
	foreach($report_data->submissions as $deal){
		extract($deal);
		$pdf->Cell(100,10,$client, "0", 0,'L');
		$pdf->Cell(40,10,$date_helper->NZEntryToDateTime($date), 0, 0,'L');
		$pdf->Cell(60,10,"$" . number_format($api,2), 0, 1,'L');
	}
	$pdf->Cell(100,10,"", "T", 0,'L');
	$pdf->Cell(40,10,"", "T", 0,'L');
	$pdf->Cell(60,10,"$" . number_format($report_data->total_submission_api,2), "T", 1,'L');
}


if(in_array('Issued', $desc) && count($report_data->issued_deals)>0){
	$pdf->SetFont('Helvetica','',14);
	$pdf->SetFillColor(224,224,224);
	$pdf->SetTextColor(0,0,0);
	$pdf->Cell(200,10,'Issued Deals', 0, 1,'L','true');

	$pdf->SetFont('Helvetica','U',12);
	$pdf->SetFillColor(0,0,0);
	$pdf->SetTextColor(0,0,0);
	$pdf->Cell(100,10,'Life Insured', 0, 0,'L');
	$pdf->Cell(40,10,'Date Issued', 0, 0,'L');
	$pdf->Cell(60,10,'Issued API', 0, 1,'L');

	$pdf->SetFont('Helvetica','',12);

	
	usort($report_data->issued_deals, "sortFunction");
	foreach($report_data->issued_deals as $deal){
		extract($deal);
		$pdf->Cell(100,10,$client, "0", 0,'L');
		$pdf->Cell(40,10,$date_helper->NZEntryToDateTime($date), 0, 0,'L');
		$pdf->Cell(60,10,"$" . number_format($api,2), 0, 1,'L');
	}
	$pdf->Cell(100,10,"", "T", 0,'L');
	$pdf->Cell(40,10,"", "T", 0,'L');
	$pdf->Cell(60,10,"$" . number_format($report_data->total_issued_api,2), "T", 1,'L');
}


if(in_array('Cancelled', $desc) && count($report_data->cancelled_deals)>0){
	$pdf->SetFont('Helvetica','',14);
	$pdf->SetFillColor(224,224,224);
	$pdf->SetTextColor(0,0,0);
	$pdf->Cell(200,10,'Cancelled Deals', 0, 1,'L','true');

	$pdf->SetFont('Helvetica','U',12);
	$pdf->SetFillColor(0,0,0);
	$pdf->SetTextColor(0,0,0);
	$pdf->Cell(100,10,'Life Insured', 0, 0,'L');
	$pdf->Cell(40,10,'Cancellation Date', 0, 0,'L');
	$pdf->Cell(60,10,'Cancellation API', 0, 1,'L');

	$pdf->SetFont('Helvetica','',12);

	
	usort($report_data->cancelled_deals, "sortFunction");
	foreach($report_data->cancelled_deals as $deal){
		extract($deal);
		$pdf->Cell(100,10,$client, "0", 0,'L');
		$pdf->Cell(40,10,$date_helper->NZEntryToDateTime($date), 0, 0,'L');
		$pdf->Cell(60,10,"$" . number_format($api,2), 0, 1,'L');
	}
	$pdf->Cell(100,10,"", "T", 0,'L');
	$pdf->Cell(40,10,"", "T", 0,'L');
	$pdf->Cell(60,10,"$" . number_format($report_data->total_cancelled_api,2), "T", 1,'L');
}

if(in_array('Pending', $desc) && count($report_data->pending_deals)>0){
	$pdf->SetFont('Helvetica','',14);
	$pdf->SetFillColor(224,224,224);
	$pdf->SetTextColor(0,0,0);
	$pdf->Cell(200,10,'Pending Deals', 0, 1,'L','true');

	$pdf->SetFont('Helvetica','U',12);
	$pdf->SetFillColor(0,0,0);
	$pdf->SetTextColor(0,0,0);
	$pdf->Cell(100,10,'Life Insured', 0, 0,'L');
	$pdf->Cell(40,10,'Submission Date', 0, 0,'L');
	$pdf->Cell(60,10,'Original API', 0, 1,'L');

	$pdf->SetFont('Helvetica','',12);

	
	usort($report_data->pending_deals, "sortFunction");
	foreach($report_data->pending_deals as $deal){
		extract($deal);
		$pdf->Cell(100,10,$client, "0", 0,'L');
		$pdf->Cell(40,10,$date_helper->NZEntryToDateTime($date), 0, 0,'L');
		$pdf->Cell(60,10,"$" . number_format($api,2), 0, 1,'L');
	}
	$pdf->Cell(100,10,"", "T", 0,'L');
	$pdf->Cell(40,10,"", "T", 0,'L');
	$pdf->Cell(60,10,"$" . number_format($report_data->total_pending_api,2), "T", 1,'L');
}

if(count($report_data->assigned_bdm_leads) > 0)
{
	$pdf->SetFont('Helvetica','',14);
	$pdf->SetFillColor(224,224,224);
	$pdf->SetTextColor(0,0,0);
	$pdf->Cell(200,10,'Assigned Face-to-Face Marketer Leads', 0, 1,'L','true');

	$pdf->SetFont('Helvetica','U',12);
	$pdf->SetFillColor(0,0,0);
	$pdf->SetTextColor(0,0,0);
	$pdf->Cell(100,10,'Client', 0, 0,'L');
	$pdf->Cell(40,10,'', 0, 0,'L');
	$pdf->Cell(60,10,'Assigned Date', 0, 1,'L');

	$pdf->SetFont('Helvetica','',12);

	
	usort($report_data->assigned_bdm_leads, "sortFunction");

	foreach($report_data->assigned_bdm_leads as $lead){
		extract($lead);
		$pdf->Cell(100,10,$name, "0", 0,'L');
		$pdf->Cell(40,10,"", 0, 0,'L');
		$pdf->Cell(60,10,$date_helper->NZEntryToDateTime($date), 0, 1,'L');
	}

	$pdf->Cell(140,10,"", "T", 0,'L');
	$pdf->Cell(60,10,count($report_data->assigned_bdm_leads) . " leads", "T", 1,'L');
}

if(count($report_data->assigned_telemarketer_leads) > 0)
{
	$pdf->SetFont('Helvetica','',14);
	$pdf->SetFillColor(224,224,224);
	$pdf->SetTextColor(0,0,0);
	$pdf->Cell(200,10,'Assigned Telemarketer Leads', 0, 1,'L','true');

	$pdf->SetFont('Helvetica','U',12);
	$pdf->SetFillColor(0,0,0);
	$pdf->SetTextColor(0,0,0);
	$pdf->Cell(100,10,'Client', 0, 0,'L');
	$pdf->Cell(40,10,'', 0, 0,'L');
	$pdf->Cell(60,10,'Assigned Date', 0, 1,'L');

	$pdf->SetFont('Helvetica','',12);

	
	usort($report_data->assigned_telemarketer_leads, "sortFunction");

	foreach($report_data->assigned_telemarketer_leads as $lead){
		extract($lead);
		$pdf->Cell(100,10,$name, "0", 0,'L');
		$pdf->Cell(40,10,"", 0, 0,'L');
		$pdf->Cell(60,10,$date_helper->NZEntryToDateTime($date), 0, 1,'L');
	}

	$pdf->Cell(140,10,"", "T", 0,'L');
	$pdf->Cell(60,10,count($report_data->assigned_telemarketer_leads) . " leads", "T", 1,'L');
}

if(count($report_data->assigned_self_generated_leads) > 0)
{
	$pdf->SetFont('Helvetica','',14);
	$pdf->SetFillColor(224,224,224);
	$pdf->SetTextColor(0,0,0);
	$pdf->Cell(200,10,'Self-Generated Leads', 0, 1,'L','true');

	$pdf->SetFont('Helvetica','U',12);
	$pdf->SetFillColor(0,0,0);
	$pdf->SetTextColor(0,0,0);
	$pdf->Cell(100,10,'Client', 0, 0,'L');
	$pdf->Cell(40,10,'', 0, 0,'L');
	$pdf->Cell(60,10,'Assigned Date', 0, 1,'L');

	$pdf->SetFont('Helvetica','',12);

	
	usort($report_data->assigned_self_generated_leads, "sortFunction");

	foreach($report_data->assigned_self_generated_leads as $lead){
		extract($lead);
		$pdf->Cell(100,10,$name, "0", 0,'L');
		$pdf->Cell(40,10,"", 0, 0,'L');
		$pdf->Cell(60,10,$date_helper->NZEntryToDateTime($date), 0, 1,'L');
	}

	$pdf->Cell(140,10,"", "T", 0,'L');
	$pdf->Cell(60,10,count($report_data->assigned_self_generated_leads) . " leads", "T", 1,'L');
}

$mix = "";
//$mix="$mydateorig"."-$name-"."BCTI".-date("ymd");
//$path="files/".$mix.".pdf";


$preview = "adviser_report_" . md5(uniqid());
$path="files/$preview" . "_preview.pdf";
$pdf->Output($path,'F');
//$pdf->Output();

ob_end_clean();
//OUTPUT 
$file=array();
$file['adviser_id']=$adviser_id;
$file['link']=$path;
$file['filename']=$mix;
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

function sortFunction( $a, $b ) {
    return strtotime($a["date"]) - strtotime($b["date"]);
}

function AddLineSpace($pdf, $linespace = 10){
	$pdf->SetFillColor(255,255,255);
	$pdf->SetTextColor(0,0,0);
	$pdf->Cell(200,$linespace,'', 0, 1,'C','true');
}

?>
