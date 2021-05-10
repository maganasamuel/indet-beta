 <?php

header('Content-Type: application/json');
session_start();
date_default_timezone_set('Pacific/Auckland');
//Restrict access to admin only
include "partials/admin_only.php";

if(!isset($_SESSION["myusername"])){
session_destroy();
header("Refresh:0; url=index.php");
}

else{
?>
<?php require "database.php";

$adviser_id = $_GET["id"];

$adviser = array();
$all_deals = array();
$clients = new stdClass();

//Total
$clients->total_leads = 0;
$clients->total_issued = 0;
$clients->total_issued_api = 0;
$clients->total_submission_api = 0;

//For Period
$clients->leads_assigned_for_period = 0;
$clients->leads_submitted_for_period = 0;
$clients->submission_api_for_period = 0;
$clients->leads_issued_for_period = 0;
$clients->issued_api_for_period = 0;

$clients->deal_cancellations_for_period = 0;
$clients->deal_cancellations_api_for_period = 0;

//Fetch date span today
$now = $initial = $end = date("Ymd");
$due=date('d/m/Y', strtotime('+7 days'));

if(date("d")>15){
	$initial = date("Ym") . "16";
	$end = date("Ymt");
	//Second Date Range
}
else{
	$initial = date("Ym") . "01";
	$end = date("Ym") . "15";
}
//echo $end;

//Fetch all of Adviser's issued leads data
$query = "SELECT *, c.name as client_name, c.date_submitted as date_generated, s.timestamp as date_submitted, i.date_issued as date_issued, a.id as adviser_id, a.fsp_num as fsp_num, c.id as client_id FROM clients_tbl c LEFT JOIN submission_clients s ON c.id = s.client_id LEFT JOIN issued_clients_tbl i ON i.name = c.id LEFT JOIN adviser_tbl a ON a.id=c.assigned_to WHERE a.id = $adviser_id AND c.status!='Cancelled'";
//echo $query . "<hr>";
$displayquery=mysqli_query($con,$query) or die('Could not look up user information; ' . mysqli_error($con));
$ctr = 0;
$total_pending_deals = 0;
$total_pending_deals_api = 0;
$total_issued_deals = 0;
$total_issued_deals_api = 0;
$total_cancelled_deals = 0;
$total_cancelled_deals_api = 0;
while($row=mysqli_fetch_assoc($displayquery)){
	extract($row);
	if($ctr==0){
		$adviser = (object) $row;
	}
	$all_deals[] = json_encode($deals);
	if($date_issued!=null){		
		$clients->total_issued++;
		$clients->total_issued_api += $issued;

		$date_to_compare = $date_issued;
		if($date_to_compare<=$end && $date_to_compare >= $initial){
			$clients->issued_api_for_period += $issued;
			$clients->leads_issued_for_period++;
			$clients->issued[] = array(
				"Client" => $client_name,
				"Amount" => $issued,
				"Date" => $date_to_compare,
				"Deals" => $deals,
			);
		}
	}
	
	if($date_submitted!=null){
		$submission_date = date("Ymd", strtotime($date_submitted));
		$date_to_compare = $submission_date;
		$sub_deals = json_decode($deals);
		foreach($sub_deals as $deal){
			if($deal->status=="Pending"||$deal->status=="Issued"){ 
				$clients->total_submission_api += $deal->original_api;
			}
			
			if($deal->status=="Pending"){
				$total_pending_deals++;
				$total_pending_deals_api += $deal->original_api;
				$life_insured = $client_name;
				if(!empty($deal->life_insured))
					$life_insured .= ", " . $deal->life_insured;

				$clients->submitted[] = array(
					"Client" => $life_insured,
					"Date" => NZEntryToDateTime($deal->submission_date),
					"Deal" => $deal,
					"SubmissionAPI" => $deal->original_api,
				);
			
			}
			elseif($deal->status=="Issued"){
				$total_issued_deals++;
				$total_issued_deals_api += $deal->issued_api;
				$life_insured = $client_name;
				if(!empty($deal->life_insured))
					$life_insured .= ", " . $deal->life_insured;

				$clients->issued_deals[] = array(
					"Client" => $life_insured,
					"Date" => NZEntryToDateTime($deal->date_issued),
					"Deal" => $deal,
					"IssuedAPI" => $deal->issued_api,
				);

				if(isset($deal->clawback_status)){
					if($deal->clawback_status=="Cancelled"){	
						$total_cancelled_deals++;
						$total_cancelled_deals_api += $deal->clawback_api;
						$life_insured = $client_name;
						if(!empty($deal->life_insured))
							$life_insured .= ", " . $deal->life_insured;

						$clients->cancelled_deals[] = array(
							"Client" => $life_insured,
							"Date" => NZEntryToDateTime($deal->clawback_date),
							"Deal" => $deal,
							"CancelledAPI" => $deal->clawback_api,
						);

						if($deal->clawback_date<=$end && $deal->clawback_date >= $initial){
							$clients->deal_cancellations_for_period++;
							$clients->deal_cancellations_api_for_period += $deal->clawback_api;
						}
					}
				}
			}
		}
		if($date_to_compare<=$end && $date_to_compare >= $initial){
			$clients->submission_api_for_period += $deal->original_api;
			$clients->leads_submitted_for_period++;
		}
	}
	
	if($date_generated!=null){

		//var_dump($row);
		//echo "<hr>";
		$date_to_compare = $date_generated;
		if($date_to_compare<=$end && $date_to_compare >= $initial){
			$clients->leads_assigned_for_period++;
			$clients->generated[] = array(
			"Client" => $client_name,
			"Date" => $date_to_compare,
							);
		}
	}
	if($lead_by!="Telemarketer")
		$clients->total_leads++;

	$ctr++;
}
$net_api = $clients->total_issued_api - $total_cancelled_deals_api;
//Fetch payables
$query = "SELECT * FROM transactions WHERE adviser_id = $adviser_id ORDER BY date DESC";
$displayquery=mysqli_query($con,$query) or die('Could not look up user information; ' . mysqli_error($con));
$total_leads_payable = 0;
$total_issued_payable = 0;
$total_outstanding_payable_amount_header = 0;
while($row=mysqli_fetch_assoc($displayquery)){
	extract($row);
	$status = CheckTransactionStatus($status);
		switch($status){
			case "Manual Billed Assigned Leads":
				$total_leads_payable+= $number_of_leads;
				break;
			case "Manual Billed Issued Leads":
				$total_issued_payable+= $number_of_leads;
				break;
			case "Billed Assigned Leads":
				$total_leads_payable+= $number_of_leads;
				break;
			case "Billed Issued Leads":
				$total_issued_payable+= $number_of_leads;
				break;
			case "Paid Issued Leads":
				$total_issued_payable-= $number_of_leads;
				break;
			default:
				$total_leads_payable-= $number_of_leads;
				break;
		}

	$total_outstanding_payable_amount_header += $amount;
	}
}

print json_encode($clients);
function DateTimeToNZEntry($date_submitted){
  return substr($date_submitted,6,4).substr($date_submitted,3,2).substr($date_submitted,0,2);
}

function NZEntryToDateTime($NZEntry){
    return substr($NZEntry,6,2) . "/" . substr($NZEntry,4,2) . "/" . substr($NZEntry, 0, 4);
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
?>