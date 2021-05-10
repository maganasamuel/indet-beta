<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">
<link rel="stylesheet" href="styles.css">
<link id="favicon" rel="icon" href="Logo_ImageOnly.png" type="image/png" sizes="16x16">
<title>INDET</title> 
</head>
<?php
require_once "libs/indet_dates_helper.php";

$indet_dates_helper = new INDET_DATES_HELPER();

require "database.php";
if(!isset($_SESSION["myusername"])){
session_destroy();
header("Refresh:0; url=index.php");
}
else{
    
if($_SESSION['myusertype']=="Adviser"){
    header("Refresh:0; url=leads_assigned");
}

include "partials/nav_bar.html";



class QuarterData {
    public $quantity = 0;
    public $api = 0;
    public $deals_data = array();
}

class MonthData {
    public $deals = 0;
    public $api = 0;
    public $deals_data = array();
}

class YearData {
    public $months = array();
    public $quarters = array();
    public $quantity = 0;
    public $api = 0;

    function __construct($ms = 12, $qs = 4){
        
        for($i = 0; $i < $ms; $i++){
            $this->months[] = new MonthData();
        }

        for($i = 0; $i < $qs; $i++){
            $this->quarters[] = new QuarterData();
        }
    }
}


if($_SESSION['myusertype']=="User"){

}
elseif($_SESSION['myusertype']=="Telemarketer"){
    include("telemarketer_script_app.php");
}
else{

    

$adviser = array();
$all_deals = array();
$data = new stdClass();

$data->year = date("Y");
if(isset($_GET["year"]))
    $data->year = $_GET["year"];

$data->first_quarter = $indet_dates_helper->GetQuarter("First",$data->year);
$data->second_quarter = $indet_dates_helper->GetQuarter("Second",$data->year);
$data->third_quarter = $indet_dates_helper->GetQuarter("Third",$data->year);
$data->fourth_quarter = $indet_dates_helper->GetQuarter("Fourth",$data->year);

//Lead Gen
$data->total_leads = 0;

//Telemarketers Data
$data->telemarketers_data = new YearData();
$data->telemarketers_data_total = 0;

$data->bdms_data = new YearData();
$data->bdms_data_total = 0;

$data->self_gen_data = new YearData();
$data->self_gen_data_total = 0;

$data->total_issued = 0;
$data->total_issued_api = 0;
$data->total_submissions = 0;
$data->total_submission_api = 0;

//For Period
$data->leads_assigned_for_period = 0;
$data->leads_submitted_for_period = 0;
$data->submission_api_for_period = 0;
$data->leads_issued_for_period = 0;
$data->issued_api_for_period = 0;

$data->deal_cancellations_for_period = 0;
$data->deal_cancellations_api_for_period = 0;

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
    //Fetch all of Adviser's issued leads data
$query = "SELECT *, c.name as client_name, c.date_submitted as date_generated, s.timestamp as date_submitted, i.date_issued as date_issued, a.id as adviser_id, a.fsp_num as fsp_num, c.id as client_id FROM clients_tbl c LEFT JOIN submission_clients s ON c.id = s.client_id LEFT JOIN issued_clients_tbl i ON i.name = c.id LEFT JOIN adviser_tbl a ON a.id=c.assigned_to WHERE c.status!='Cancelled'";
//echo $query . "<hr>";
$displayquery=mysqli_query($con,$query) or die('Could not look up user information; ' . mysqli_error($con));
$ctr = 0;
$total_pending_deals = 0;
$total_pending_deals_api = 0;
$total_submissions = 0;
$total_submission_api = 0;
$total_issued_deals = 0;
$total_issued_deals_api = 0;
$total_cancelled_deals = 0;
$total_cancelled_deals_api = 0;


$data->issued_data = new YearData();

$data->submissions_data = new YearData();

$data->cancellations_data = new YearData();

$data->debug = new stdClass();
$data->debug->client = array();

while($row=mysqli_fetch_assoc($displayquery)){
    extract($row);

    $client_debug_data = new stdClass();
    
    //Reset Client API
    $total_client_api = 0;

    //Get Adviser Info
	if($ctr==0){
		$adviser = (object) $row;
    }
    
    //Get Deals
    $all_deals[] = json_encode($deals);
    
	if($date_issued!=null){		

        $data->total_issued++;

        $data->total_issued_api += (float)$issued;
        //echo "<h3>$client_name</h3><br>Issued: $issued <br> Original API: $deal->original_api <hr>";
		$date_to_compare = $date_issued;
		if($date_to_compare<=$end && $date_to_compare >= $initial){
			$data->issued_api_for_period += $issued;
			$data->leads_issued_for_period++;
			$data->issued[] = array(
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
            
            $data->total_submissions++;
            $data->total_submission_api += $deal->original_api;
            /*
                if($deal->status=="Pending"||$deal->status=="Issued"){ 
                    $data->total_submission_api += $deal->original_api;
                }
            */
                $d_to_compare = $deal->submission_date;
                //Quarterly Data
                if($d_to_compare<=$data->first_quarter->to->format("Ymd") && $d_to_compare>=$data->first_quarter->from->format("Ymd")){
                    $data->submissions_data->quarters[0]->quantity++;
                    $data->submissions_data->quarters[0]->api+= $deal->original_api;
                    $data->submissions_data->quarters[0]->deals_data[] = $deal;

                    $data->submissions_data->quantity++;
                    $data->submissions_data->api+= $deal->original_api;

                }
                elseif($d_to_compare<=$data->second_quarter->to->format("Ymd") && $d_to_compare>=$data->second_quarter->from->format("Ymd")){
                    $data->submissions_data->quarters[1]->quantity++;
                    $data->submissions_data->quarters[1]->api+= $deal->original_api; 
                    $data->submissions_data->quarters[1]->deals_data[] = $deal;

                    $data->submissions_data->quantity++;
                    $data->submissions_data->api+= $deal->original_api;
                }
                elseif($d_to_compare<=$data->third_quarter->to->format("Ymd") && $d_to_compare>=$data->third_quarter->from->format("Ymd")){
                    $data->submissions_data->quarters[2]->quantity++;
                    $data->submissions_data->quarters[2]->api+= $deal->original_api; 
                    $data->submissions_data->quarters[2]->deals_data[] = $deal;    
                    //echo "3 Q";     
                    $data->submissions_data->quantity++;
                    $data->submissions_data->api+= $deal->original_api;
                }
                elseif($d_to_compare<=$data->fourth_quarter->to->format("Ymd") && $d_to_compare>=$data->fourth_quarter->from->format("Ymd")){
                    $data->submissions_data->quarters[3]->quantity++;
                    $data->submissions_data->quarters[3]->api+= $deal->original_api;   
                    $data->submissions_data->quarters[3]->deals_data[] = $deal;      
                    //echo "4 Q";     
                    $data->submissions_data->quantity++;
                    $data->submissions_data->api+= $deal->original_api;
                }

                //Monthly Data
                $m1 = date('m',strtotime($d_to_compare));
                $y1 = date('Y', strtotime($d_to_compare));

                if($y1 == date('Y')){
                    $data->submissions_data->months[($m1 - 1)]->deals++;
                    $data->submissions_data->months[($m1 - 1)]->api+= $deal->original_api;
                    $data->submissions_data->months[($m1 - 1)]->deals_data[] = $deal;
                }
                
            
			if($deal->status=="Pending"){
				$total_pending_deals++;
				$total_pending_deals_api += $deal->original_api;
				$life_insured = $client_name;
				if(!empty($deal->life_insured))
					$life_insured .= ", " . $deal->life_insured;

				$data->submitted[] = array(
					"Client" => $life_insured,
					"Date" => $indet_dates_helper->NZEntryToDateTime($deal->submission_date),
					"Deal" => $deal,
					"SubmissionAPI" => $deal->original_api,
				);
            }
            
			elseif($deal->status=="Issued"){
                $total_issued_deals++;
                $total_issued_deals_api += $deal->issued_api;

                //Client Specific api
                $total_client_api += $deal->issued_api;

				$life_insured = $client_name;
				if(!empty($deal->life_insured))
					$life_insured .= ", " . $deal->life_insured;

				$data->issued_deals[] = array(
					"Client" => $life_insured,
					"Date" => $indet_dates_helper->NZEntryToDateTime($deal->date_issued),
					"Deal" => $deal,
					"IssuedAPI" => $deal->issued_api,
				);
                //echo "<hr>". NZEntryToDateTime($deal->date_issued);
                //Quarterly and Monthly 
                $d_to_compare = $deal->date_issued;
                
                if($d_to_compare<=$data->first_quarter->to->format("Ymd") && $d_to_compare>=$data->first_quarter->from->format("Ymd")){
                    $data->issued_data->quarters[0]->quantity++;
                    $data->issued_data->quarters[0]->api+= $deal->issued_api;
                    $data->issued_data->quarters[0]->deals_data[] = $deal;    

                    $data->issued_data->quantity++;
                    $data->issued_data->api+= $deal->issued_api;

                }
                elseif($d_to_compare<=$data->second_quarter->to->format("Ymd") && $d_to_compare>=$data->second_quarter->from->format("Ymd")){
                    $data->issued_data->quarters[1]->quantity++;
                    $data->issued_data->quarters[1]->api+= $deal->issued_api; 
                    $data->issued_data->quarters[1]->deals_data[] = $deal;    

                    $data->issued_data->quantity++;
                    $data->issued_data->api+= $deal->issued_api;
                }
                elseif($d_to_compare<=$data->third_quarter->to->format("Ymd") && $d_to_compare>=$data->third_quarter->from->format("Ymd")){
                    $data->issued_data->quarters[2]->quantity++;
                    $data->issued_data->quarters[2]->api+= $deal->issued_api; 
                    $data->issued_data->quarters[2]->deals_data[] = $deal;        
                    //echo "3 Q";     
                    $data->issued_data->quantity++;
                    $data->issued_data->api+= $deal->issued_api;
                }
                elseif($d_to_compare<=$data->fourth_quarter->to->format("Ymd") && $d_to_compare>=$data->fourth_quarter->from->format("Ymd")){
                    $data->issued_data->quarters[3]->quantity++;
                    $data->issued_data->quarters[3]->api+= $deal->issued_api; 
                    $data->issued_data->quarters[3]->deals_data[] = $deal;        
                    //echo "4 Q";     
                    $data->issued_data->quantity++;
                    $data->issued_data->api+= $deal->issued_api;
                }

                $m1 = date('m',strtotime($d_to_compare));
                $y1 = date('Y', strtotime($d_to_compare));

                if($y1 == date('Y')){
                    $data->issued_data->months[($m1 - 1)]->deals++;
                    $data->issued_data->months[($m1 - 1)]->api+= $deal->issued_api;
                    $data->issued_data->months[($m1 - 1)]->deals_data[] = $deal;
                }

				if(isset($deal->clawback_status)){
					if($deal->clawback_status=="Cancelled"){	
						$total_cancelled_deals++;
						$total_cancelled_deals_api += $deal->clawback_api;
						$life_insured = $client_name;
						if(!empty($deal->life_insured))
							$life_insured .= ", " . $deal->life_insured;

						$data->cancelled_deals[] = array(
							"Client" => $life_insured,
							"Date" => $indet_dates_helper->NZEntryToDateTime($deal->clawback_date),
							"Deal" => $deal,
							"CancelledAPI" => $deal->clawback_api,
                        );
                        
                        $d_to_compare = $deal->clawback_date;

                        //Quarterly Data
                        if($d_to_compare<=$data->first_quarter->to->format("Ymd") && $d_to_compare>=$data->first_quarter->from->format("Ymd")){
                            $data->cancellations_data->quarters[0]->quantity++;
                            $data->cancellations_data->quarters[0]->api+= $deal->clawback_api;
                            $data->cancellations_data->quarters[0]->deals_data[] = $deal;    

                            $data->cancellations_data->quantity++;
                            $data->cancellations_data->api+= $deal->clawback_api;

                        }
                        elseif($d_to_compare<=$data->second_quarter->to->format("Ymd") && $d_to_compare>=$data->second_quarter->from->format("Ymd")){
                            $data->cancellations_data->quarters[1]->quantity++;
                            $data->cancellations_data->quarters[1]->api+= $deal->clawback_api; 
                            $data->cancellations_data->quarters[1]->deals_data[] = $deal;    

                            $data->cancellations_data->quantity++;
                            $data->cancellations_data->api+= $deal->clawback_api;
                        }
                        elseif($d_to_compare<=$data->third_quarter->to->format("Ymd") && $d_to_compare>=$data->third_quarter->from->format("Ymd")){
                            $data->cancellations_data->quarters[2]->quantity++;
                            $data->cancellations_data->quarters[2]->api+= $deal->clawback_api;     
                            $data->cancellations_data->quarters[2]->deals_data[] = $deal;    
                            //echo "3 Q";     
                            $data->cancellations_data->quantity++;
                            $data->cancellations_data->api+= $deal->clawback_api;
                        }
                        elseif($d_to_compare<=$data->fourth_quarter->to->format("Ymd") && $d_to_compare>=$data->fourth_quarter->from->format("Ymd")){
                            $data->cancellations_data->quarters[3]->quantity++;
                            $data->cancellations_data->quarters[3]->api+= $deal->clawback_api;     
                            $data->cancellations_data->quarters[3]->deals_data[] = $deal;    
                            //echo "4 Q";     
                            $data->cancellations_data->quantity++;
                            $data->cancellations_data->api+= $deal->clawback_api;
                        }

                        //Monthly Data
                        $m1 = date('m',strtotime($d_to_compare));
                        $y1 = date('Y', strtotime($d_to_compare));

                        if($y1 == date('Y')){
                            $data->cancellations_data->months[($m1 - 1)]->deals++;
                            $data->cancellations_data->months[($m1 - 1)]->api+= $deal->clawback_api;
                            $data->cancellations_data->months[($m1 - 1)]->deals_data[] = $deal;
                        }

						if($deal->clawback_date<=$end && $deal->clawback_date >= $initial){
							$data->deal_cancellations_for_period++;
							$data->deal_cancellations_api_for_period += $deal->clawback_api;
						}
					}
				}
			}
		}
		if($date_to_compare<=$end && $date_to_compare >= $initial){
			$data->submission_api_for_period += $deal->original_api;
			$data->leads_submitted_for_period++;
		}
	}
    
	if($date_generated!=null){
		$date_to_compare = $date_generated;
		if($date_to_compare<=$end && $date_to_compare >= $initial){
			$data->leads_assigned_for_period++;
			$data->generated[] = array(
			"Client" => $client_name,
			"Date" => $date_to_compare,
							);
		}
    }
    
        
    $data->total_leads++;

    

    if($lead_by=="Telemarketer"){
        $data->telemarketers_data_total++;
        
            if($date_generated<=$data->first_quarter->to->format("Ymd") && $date_generated>=$data->first_quarter->from->format("Ymd")){
                $data->telemarketers_data->quarters[0]->quantity++;
                $data->telemarketers_data->quarters[0]->api+= $total_client_api;

                            
                //debug data
                $client_debug_data->name = $client_name;
                $client_debug_data->issued_api = $total_client_api;
                $client_debug_data->lead_gen = $lead_by;

                $data->debug->client[] = $client_debug_data;
                
            }
            elseif($date_generated<=$data->second_quarter->to->format("Ymd") && $date_generated>=$data->second_quarter->from->format("Ymd")){
                $data->telemarketers_data->quarters[1]->quantity++;
                $data->telemarketers_data->quarters[1]->api+= $total_client_api;
            }
            elseif($date_generated<=$data->third_quarter->to->format("Ymd") && $date_generated>=$data->third_quarter->from->format("Ymd")){
                $data->telemarketers_data->quarters[2]->quantity++;
                $data->telemarketers_data->quarters[2]->api+= $total_client_api;
            }
            elseif($date_generated<=$data->fourth_quarter->to->format("Ymd") && $date_generated>=$data->fourth_quarter->from->format("Ymd")){
                $data->telemarketers_data->quarters[3]->quantity++;
                $data->telemarketers_data->quarters[3]->api+= $total_client_api;
            }
            
    
        $year_submitted = date('Y',strtotime($date_generated));

        if($year_submitted<=$data->fourth_quarter->to->format("Y") && $year_submitted>=$data->fourth_quarter->from->format("Y")){             
            //Monthly Data
            $m1 = date('m',strtotime($date_generated));
            $data->telemarketers_data->months[($m1 - 1)]->deals++;
            $data->telemarketers_data->months[($m1 - 1)]->api+= $total_client_api;

            //Yearly Data   
            $data->telemarketers_data->quantity++;
            $data->telemarketers_data->api+= $total_client_api;
        }
        
    }
    elseif($lead_by=="Face-to-Face Marketer"){
        $data->bdms_data_total++;
        if($date_generated<=$data->first_quarter->to->format("Ymd") && $date_generated>=$data->first_quarter->from->format("Ymd")){
            $data->bdms_data->quarters[0]->quantity++;
            $data->bdms_data->quarters[0]->api+= $total_client_api;

                            
                //debug data
                $client_debug_data->name = $client_name;
                $client_debug_data->issued_api = $total_client_api;
                $client_debug_data->lead_gen = $lead_by;

                $data->debug->client[] = $client_debug_data;
        }
        elseif($date_generated<=$data->second_quarter->to->format("Ymd") && $date_generated>=$data->second_quarter->from->format("Ymd")){
            $data->bdms_data->quarters[1]->quantity++;
            $data->bdms_data->quarters[1]->api+= $total_client_api;
        }
        elseif($date_generated<=$data->third_quarter->to->format("Ymd") && $date_generated>=$data->third_quarter->from->format("Ymd")){
            $data->bdms_data->quarters[2]->quantity++;
            $data->bdms_data->quarters[2]->api+= $total_client_api;
        }
        elseif($date_generated<=$data->fourth_quarter->to->format("Ymd") && $date_generated>=$data->fourth_quarter->from->format("Ymd")){
            $data->bdms_data->quarters[3]->quantity++;
            $data->bdms_data->quarters[3]->api+= $total_client_api;
        }
        
        
        $year_submitted = date('Y',strtotime($date_generated));

        if($year_submitted<=$data->fourth_quarter->to->format("Y") && $year_submitted>=$data->fourth_quarter->from->format("Y")){    
            //Monthly Data
            $m1 = date('m',strtotime($date_generated));
            $data->bdms_data->months[($m1 - 1)]->deals++;
            $data->bdms_data->months[($m1 - 1)]->api+= $total_client_api;
        
            //Yearly Data   
            $data->bdms_data->quantity++;
            $data->bdms_data->api+= $total_client_api;
        }
    }
    elseif($lead_by=="Self-Generated"){
        $data->self_gen_data_total++;
        
        if($date_generated<=$data->first_quarter->to->format("Ymd") && $date_generated>=$data->first_quarter->from->format("Ymd")){
            $data->self_gen_data->quarters[0]->quantity++;
            $data->self_gen_data->quarters[0]->api+= $total_client_api;

                            
                //debug data
                $client_debug_data->name = $client_name;
                $client_debug_data->issued_api = $total_client_api;
                $client_debug_data->lead_gen = $lead_by;

                $data->debug->client[] = $client_debug_data;
        }
        elseif($date_generated<=$data->second_quarter->to->format("Ymd") && $date_generated>=$data->second_quarter->from->format("Ymd")){
            $data->self_gen_data->quarters[1]->quantity++;
            $data->self_gen_data->quarters[1]->api+= $total_client_api;
        }
        elseif($date_generated<=$data->third_quarter->to->format("Ymd") && $date_generated>=$data->third_quarter->from->format("Ymd")){
            $data->self_gen_data->quarters[2]->quantity++;
            $data->self_gen_data->quarters[2]->api+= $total_client_api;
        }
        elseif($date_generated<=$data->fourth_quarter->to->format("Ymd") && $date_generated>=$data->fourth_quarter->from->format("Ymd")){
            $data->self_gen_data->quarters[3]->quantity++;
            $data->self_gen_data->quarters[3]->api+= $total_client_api;
        }
        
        $year_submitted = date('Y',strtotime($date_generated));

        if($year_submitted<=$data->fourth_quarter->to->format("Y") && $year_submitted>=$data->fourth_quarter->from->format("Y")){
           //Monthly Data
            $m1 = date('m',strtotime($date_generated));
            $data->self_gen_data->months[($m1 - 1)]->deals++;
            $data->self_gen_data->months[($m1 - 1)]->api+= $total_client_api;

            //Yearly Data    
            $data->self_gen_data->quantity++;
            $data->self_gen_data->api+= $total_client_api;
        }        
    }

	$ctr++;
}
?>
<!--header-->
<div align="center">


<div id="client_labels">
<style type="text/css">

	.profile_header div:after {
	  content: '';
	  height: 6200%;
	  width: 1px;

	  position: absolute;
	  right: 0;
	  top: 0; 

	  background-color: #000000;
	}

	.profile_header div:last-child:after{
		content: '';
	  	height: 0%;
	  	width: 1px;
	}


</style>

<div class="jumbotron">
    <h2 class="slide">ELITEINSURE OVERALL PERFORMANCE DATA</h2>
</div>

<div class="row profile_header">
	  	<div class="col-sm-4">
		  	<h3>
		  		Lead Generation Data:
		  	</h3>
		</div>
	  	<div class="col-sm-4">
	  		<h3>
	  			Production:
	  		</h3>
	  	</div>
  		<div class="col-sm-4" >
	  		<h3>
	  			Company Performance:
	  		</h3>
	  	</div>
</div>
    
<div class="row">
    <div class="col-sm-4" >
        <!-- First Column -->
        
        <div class="row">
            <div class="col-sm-12" >
                <table class="table">
                    <thead>
                        <tr>
                            <th colspan="3" style="text-align:center;"><?php echo $data->year ?> Face-to-Face Marketer Leads Quarterly Data</th>
                        </tr>
                        <tr>
                            <th scope="col" style="text-align:center;">Quarter</th>
                            <th scope="col" style="text-align:center;">Generated</th>
                            <th scope="col" style="text-align:center;">Issued API</th>
                        </tr>
                    </thead>
                    
                    <tbody>
                        <tr>
                            <th scope="row" style="text-align:center;">First</th>
                            <td style="text-align:center;"><?php echo $data->bdms_data->quarters[0]->quantity ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->bdms_data->quarters[0]->api,2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row"  style="text-align:center;">Second</th>
                            
                            <td><?php echo $data->bdms_data->quarters[1]->quantity ?></td>
                            <td>$<?php echo number_format($data->bdms_data->quarters[1]->api,2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">Third</th>
                            <td><?php echo $data->bdms_data->quarters[2]->quantity ?></td>
                            <td>$<?php echo number_format($data->bdms_data->quarters[2]->api,2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">Fourth</th>
                            <td><?php echo $data->bdms_data->quarters[3]->quantity ?></td>
                            <td>$<?php echo number_format($data->bdms_data->quarters[3]->api,2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">Total</th>
                            <td><?php echo $data->bdms_data->quantity ?></td>
                            <td>$<?php echo number_format($data->bdms_data->api,2) ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="row">
            <div class="col-sm-12" >
                <table class="table">
                    <thead>
                        <tr>
                            <th colspan="3" style="text-align:center;"><?php echo $data->year ?> Telemarketer Leads Quarterly Data</th>
                        </tr>
                        <tr>
                            <th scope="col" style="text-align:center;">Quarter</th>
                            <th scope="col" style="text-align:center;">Generated</th>
                            <th scope="col" style="text-align:center;">Issued API</th>
                        </tr>
                    </thead>
                    
                    <tbody>
                        <tr>
                            <th scope="row" style="text-align:center;">First</th>
                            <td style="text-align:center;"><?php echo $data->telemarketers_data->quarters[0]->quantity ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->telemarketers_data->quarters[0]->api,2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row"  style="text-align:center;">Second</th>
                            
                            <td><?php echo $data->telemarketers_data->quarters[1]->quantity ?></td>
                            <td>$<?php echo number_format($data->telemarketers_data->quarters[1]->api,2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">Third</th>
                            <td><?php echo $data->telemarketers_data->quarters[2]->quantity ?></td>
                            <td>$<?php echo number_format($data->telemarketers_data->quarters[2]->api,2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">Fourth</th>
                            <td><?php echo $data->telemarketers_data->quarters[3]->quantity ?></td>
                            <td>$<?php echo number_format($data->telemarketers_data->quarters[3]->api,2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">Total</th>
                            <td><?php echo $data->telemarketers_data->quantity ?></td>
                            <td>$<?php echo number_format($data->telemarketers_data->api,2) ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="row">
            <div class="col-sm-12" >
                <table class="table">
                    <thead>
                        <tr>
                            <th colspan="3" style="text-align:center;"><?php echo $data->year ?> Self-Generated Leads Quarterly Data</th>
                        </tr>
                        <tr>
                            <th scope="col" style="text-align:center;">Quarter</th>
                            <th scope="col" style="text-align:center;">Generated</th>
                            <th scope="col" style="text-align:center;">Issued API</th>
                        </tr>
                    </thead>
                    
                    <tbody>
                        <tr>
                            <th scope="row" style="text-align:center;">First</th>
                            <td style="text-align:center;"><?php echo $data->self_gen_data->quarters[0]->quantity ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->self_gen_data->quarters[0]->api,2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row"  style="text-align:center;">Second</th>
                            
                            <td><?php echo $data->self_gen_data->quarters[1]->quantity ?></td>
                            <td>$<?php echo number_format($data->self_gen_data->quarters[1]->api,2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">Third</th>
                            <td><?php echo $data->self_gen_data->quarters[2]->quantity ?></td>
                            <td>$<?php echo number_format($data->self_gen_data->quarters[2]->api,2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">Fourth</th>
                            <td><?php echo $data->self_gen_data->quarters[3]->quantity ?></td>
                            <td>$<?php echo number_format($data->self_gen_data->quarters[3]->api,2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">Total</th>
                            <td><?php echo $data->self_gen_data->quantity ?></td>
                            <td>$<?php echo number_format($data->self_gen_data->api,2) ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        

    </div>
    <div class="col-sm-4">
        <!-- Second Column -->
        <div class="row">
            <div class="col-sm-12">
                <table class="table">
                    <thead>  
                        <tr>
                            <th colspan="3" style="text-align:center;"><?php echo $data->year ?> Submissions Quarterly Data</th>
                        </tr>
                        <tr>
                            <th scope="col" style="text-align:center;">Quarter</th>
                            <th scope="col" style="text-align:center;">Quantity</th>
                            <th scope="col" style="text-align:center;">API</th>
                        </tr>
                    </thead>
                    
                    <tbody>
                        <tr>
                            <th scope="row" style="text-align:center;">First</th>
                            <td style="text-align:center;"><?php echo $data->submissions_data->quarters[0]->quantity ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->submissions_data->quarters[0]->api,2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">Second</th>
                            <td style="text-align:center;"><?php echo $data->submissions_data->quarters[1]->quantity ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->submissions_data->quarters[1]->api,2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">Third</th>
                            <td style="text-align:center;"><?php echo $data->submissions_data->quarters[2]->quantity ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->submissions_data->quarters[2]->api,2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">Fourth</th>
                            <td style="text-align:center;"><?php echo $data->submissions_data->quarters[3]->quantity ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->submissions_data->quarters[3]->api,2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">Total</th>
                            <td style="text-align:center;"><?php echo ($data->submissions_data->quantity) ?></td>
                            <td style="text-align:center;">$<?php echo number_format(($data->submissions_data->api),2) ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="row">
        <div class="col-sm-12">
                <table class="table">
                    <thead>  
                        <tr>
                            <th colspan="3" style="text-align:center;"><?php echo $data->year ?> Submissions Monthly Data</th>
                        </tr>
                        <tr>
                            <th scope="col" style="text-align:center;">Month</th>
                            <th scope="col" style="text-align:center;">Quantity</th>
                            <th scope="col" style="text-align:center;">API</th>
                        </tr>
                    </thead>
                    
                    <tbody>
                        <tr>
                            <th scope="row" style="text-align:center;">January</th>
                            <td style="text-align:center;"><?php echo $data->submissions_data->months[0]->deals ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->submissions_data->months[0]->api,2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">February</th>
                            <td style="text-align:center;"><?php echo $data->submissions_data->months[1]->deals ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->submissions_data->months[1]->api,2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">March</th>
                            <td style="text-align:center;"><?php echo $data->submissions_data->months[2]->deals ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->submissions_data->months[2]->api,2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">April</th>
                            <td style="text-align:center;"><?php echo $data->submissions_data->months[3]->deals ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->submissions_data->months[3]->api,2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">May</th>
                            <td style="text-align:center;"><?php echo $data->submissions_data->months[4]->deals ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->submissions_data->months[4]->api,2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">June</th>
                            <td style="text-align:center;"><?php echo $data->submissions_data->months[5]->deals ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->submissions_data->months[5]->api,2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">July</th>
                            <td style="text-align:center;"><?php echo $data->submissions_data->months[6]->deals ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->submissions_data->months[6]->api,2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">August</th>
                            <td style="text-align:center;"><?php echo $data->submissions_data->months[7]->deals ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->submissions_data->months[7]->api,2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">September</th>
                            <td style="text-align:center;"><?php echo $data->submissions_data->months[8]->deals ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->submissions_data->months[8]->api,2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">October</th>
                            <td style="text-align:center;"><?php echo $data->submissions_data->months[9]->deals ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->submissions_data->months[9]->api,2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">November</th>
                            <td style="text-align:center;"><?php echo $data->submissions_data->months[10]->deals ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->submissions_data->months[10]->api,2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">December</th>
                            <td style="text-align:center;"><?php echo $data->submissions_data->months[11]->deals ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->submissions_data->months[11]->api,2) ?></td>
                        </tr>
                        
                    </tbody>
                </table>
            </div>
        </div>

        <div class="row">
                
            <div class="col-sm-12">
            <table class="table">
                    <thead>  
                        <tr>
                            <th colspan="3" style="text-align:center;"><?php echo $data->year ?> Issued Policies Quarterly Data</th>
                        </tr>
                        <tr>
                            <th scope="col" style="text-align:center;">Quarter</th>
                            <th scope="col" style="text-align:center;">Quantity</th>
                            <th scope="col" style="text-align:center;">API</th>
                        </tr>
                    </thead>
                    
                    <tbody>
                        <tr>
                            <th scope="row" style="text-align:center;">First</th>
                            <td style="text-align:center;"><?php echo $data->issued_data->quarters[0]->quantity ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->issued_data->quarters[0]->api,2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">Second</th>
                            <td style="text-align:center;"><?php echo $data->issued_data->quarters[1]->quantity ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->issued_data->quarters[1]->api,2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">Third</th>
                            <td style="text-align:center;"><?php echo $data->issued_data->quarters[2]->quantity ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->issued_data->quarters[2]->api,2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">Fourth</th>
                            <td style="text-align:center;"><?php echo $data->issued_data->quarters[3]->quantity ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->issued_data->quarters[3]->api,2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">Total</th>
                            <td style="text-align:center;"><?php echo ($data->issued_data->quantity) ?></td>
                            <td style="text-align:center;">$<?php echo number_format(($data->issued_data->api),2) ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="row">            
            <div class="col-sm-12">
                <table class="table">
                    <thead>  
                        <tr>
                            <th colspan="3" style="text-align:center;"><?php echo $data->year ?> Issued Policies Monthly Data</th>
                        </tr>
                        <tr>
                            <th scope="col" style="text-align:center;">Month</th>
                            <th scope="col" style="text-align:center;">Quantity</th>
                            <th scope="col" style="text-align:center;">API</th>
                        </tr>
                    </thead>
                    
                    <tbody>
                        <tr>
                            <th scope="row" style="text-align:center;">January</th>
                            <td style="text-align:center;"><?php echo $data->issued_data->months[0]->deals ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->issued_data->months[0]->api,2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">February</th>
                            <td style="text-align:center;"><?php echo $data->issued_data->months[1]->deals ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->issued_data->months[1]->api,2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">March</th>
                            <td style="text-align:center;"><?php echo $data->issued_data->months[2]->deals ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->issued_data->months[2]->api,2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">April</th>
                            <td style="text-align:center;"><?php echo $data->issued_data->months[3]->deals ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->issued_data->months[3]->api,2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">May</th>
                            <td style="text-align:center;"><?php echo $data->issued_data->months[4]->deals ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->issued_data->months[4]->api,2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">June</th>
                            <td style="text-align:center;"><?php echo $data->issued_data->months[5]->deals ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->issued_data->months[5]->api,2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">July</th>
                            <td style="text-align:center;"><?php echo $data->issued_data->months[6]->deals ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->issued_data->months[6]->api,2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">August</th>
                            <td style="text-align:center;"><?php echo $data->issued_data->months[7]->deals ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->issued_data->months[7]->api,2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">September</th>
                            <td style="text-align:center;"><?php echo $data->issued_data->months[8]->deals ?></td>
                            
                            <td style="text-align:center;">$<?php echo number_format($data->issued_data->months[8]->api,2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">October</th>
                            <td style="text-align:center;"><?php echo $data->issued_data->months[9]->deals ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->issued_data->months[9]->api,2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">November</th>
                            <td style="text-align:center;"><?php echo $data->issued_data->months[10]->deals ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->issued_data->months[10]->api,2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">December</th>
                            <td style="text-align:center;"><?php echo $data->issued_data->months[11]->deals ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->issued_data->months[11]->api,2) ?></td>
                        </tr>
                        
                    </tbody>
                </table>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12">
                <table class="table">
                    <thead>  
                        <tr>
                            <th colspan="3" style="text-align:center;"><?php echo $data->year ?> Cancellations Quarterly Data</th>
                        </tr>
                        <tr>
                            <th scope="col" style="text-align:center;">Quarter</th>
                            <th scope="col" style="text-align:center;">Quantity</th>
                            <th scope="col" style="text-align:center;">API</th>
                        </tr>
                    </thead>
                    
                    <tbody>
                        <tr>
                            <th scope="row" style="text-align:center;">First</th>
                            <td style="text-align:center;"><?php echo $data->cancellations_data->quarters[0]->quantity ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->cancellations_data->quarters[0]->api,2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">Second</th>
                            <td style="text-align:center;"><?php echo $data->cancellations_data->quarters[1]->quantity ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->cancellations_data->quarters[1]->api,2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">Third</th>
                            <td style="text-align:center;"><?php echo $data->cancellations_data->quarters[2]->quantity ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->cancellations_data->quarters[2]->api,2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">Fourth</th>
                            <td style="text-align:center;"><?php echo $data->cancellations_data->quarters[3]->quantity ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->cancellations_data->quarters[3]->api,2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">Total</th>
                            <td style="text-align:center;"><?php echo ($data->cancellations_data->quantity) ?></td>
                            <td style="text-align:center;">$<?php echo number_format(($data->cancellations_data->api),2) ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="row">            
            <div class="col-sm-12">
                <table class="table">
                    <thead>  
                        <tr>
                            <th colspan="3" style="text-align:center;"><?php echo $data->year ?> Cancellations Monthly Data</th>
                        </tr>
                        <tr>
                            <th scope="col" style="text-align:center;">Month</th>
                            <th scope="col" style="text-align:center;">Quantity</th>
                            <th scope="col" style="text-align:center;">API</th>
                        </tr>
                    </thead>
                    
                    <tbody>
                        <tr>
                            <th scope="row" style="text-align:center;">January</th>
                            <td style="text-align:center;"><?php echo $data->cancellations_data->months[0]->deals ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->cancellations_data->months[0]->api,2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">February</th>
                            <td style="text-align:center;"><?php echo $data->cancellations_data->months[1]->deals ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->cancellations_data->months[1]->api,2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">March</th>
                            <td style="text-align:center;"><?php echo $data->cancellations_data->months[2]->deals ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->cancellations_data->months[2]->api,2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">April</th>
                            <td style="text-align:center;"><?php echo $data->cancellations_data->months[3]->deals ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->cancellations_data->months[3]->api,2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">May</th>
                            <td style="text-align:center;"><?php echo $data->cancellations_data->months[4]->deals ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->cancellations_data->months[4]->api,2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">June</th>
                            <td style="text-align:center;"><?php echo $data->cancellations_data->months[5]->deals ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->cancellations_data->months[5]->api,2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">July</th>
                            <td style="text-align:center;"><?php echo $data->cancellations_data->months[6]->deals ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->cancellations_data->months[6]->api,2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">August</th>
                            <td style="text-align:center;"><?php echo $data->cancellations_data->months[7]->deals ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->cancellations_data->months[7]->api,2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">September</th>
                            <td style="text-align:center;"><?php echo $data->cancellations_data->months[8]->deals ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->cancellations_data->months[8]->api,2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">October</th>
                            <td style="text-align:center;"><?php echo $data->cancellations_data->months[9]->deals ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->cancellations_data->months[9]->api,2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">November</th>
                            <td style="text-align:center;"><?php echo $data->cancellations_data->months[10]->deals ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->cancellations_data->months[10]->api,2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">December</th>
                            <td style="text-align:center;"><?php echo $data->cancellations_data->months[11]->deals ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->cancellations_data->months[11]->api,2) ?></td>
                        </tr>
                        
                    </tbody>
                </table>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12">
                <table class="table">
                    <thead>  
                        <tr>
                            <th colspan="3" style="text-align:center;"><?php echo $data->year ?> Quarterly Performance Data</th>
                        </tr>
                        <tr>
                            <th scope="col" style="text-align:center;">Quarter</th>
                            <th scope="col" style="text-align:center;">Leads</th>
                            <th scope="col" style="text-align:center;">Net API</th>
                        </tr>
                    </thead>
                    
                    <tbody>
                        <tr>
                            <th scope="row" style="text-align:center;">First</th>
                            <td style="text-align:center;"><?php echo $data->bdms_data->quarters[0]->quantity + $data->telemarketers_data->quarters[0]->quantity + $data->self_gen_data->quarters[0]->quantity?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->issued_data->quarters[0]->api - $data->cancellations_data->quarters[0]->api ,2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">Second</th>
                            <td style="text-align:center;"><?php echo $data->bdms_data->quarters[1]->quantity + $data->telemarketers_data->quarters[1]->quantity + $data->self_gen_data->quarters[1]->quantity?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->issued_data->quarters[1]->api - $data->cancellations_data->quarters[1]->api,2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">Third</th>
                            <td style="text-align:center;"><?php echo $data->bdms_data->quarters[2]->quantity + $data->telemarketers_data->quarters[2]->quantity + $data->self_gen_data->quarters[2]->quantity?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->issued_data->quarters[2]->api - $data->cancellations_data->quarters[2]->api,2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">Fourth</th>
                            <td style="text-align:center;"><?php echo $data->bdms_data->quarters[3]->quantity + $data->telemarketers_data->quarters[3]->quantity + $data->self_gen_data->quarters[3]->quantity?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->issued_data->quarters[3]->api - $data->cancellations_data->quarters[3]->api,2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">Total</th>
                            <td style="text-align:center;"><?php echo $data->bdms_data->quantity + $data->telemarketers_data->quantity + $data->self_gen_data->quantity ?></td>
                            <td style="text-align:center;">$<?php echo number_format(($data->issued_data->api - $data->cancellations_data->api),2) ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="row">            
            <div class="col-sm-12">
            <table class="table">
                    <thead>  
                        <tr>
                            <th colspan="3" style="text-align:center;"><?php echo $data->year ?> Monthly Performance Data</th>
                        </tr>
                        <tr>
                            <th scope="col" style="text-align:center;">Month</th>
                            <th scope="col" style="text-align:center;">Leads</th>
                            <th scope="col" style="text-align:center;">Net API</th>
                        </tr>
                    </thead>
                    
                    <tbody>
                        <tr>
                            <th scope="row" style="text-align:center;">January</th>
                            <td style="text-align:center;"><?php echo $data->bdms_data->months[0]->deals + $data->telemarketers_data->months[0]->deals + $data->self_gen_data->months[0]->deals ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->issued_data->months[0]->api - $data->cancellations_data->months[0]->api,2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">February</th>
                            <td style="text-align:center;"><?php echo $data->bdms_data->months[1]->deals + $data->telemarketers_data->months[1]->deals + $data->self_gen_data->months[1]->deals ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->issued_data->months[1]->api - $data->cancellations_data->months[1]->api,2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">March</th>
                            <td style="text-align:center;"><?php echo $data->bdms_data->months[2]->deals + $data->telemarketers_data->months[2]->deals + $data->self_gen_data->months[2]->deals ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->issued_data->months[2]->api - $data->cancellations_data->months[2]->api,2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">April</th>
                            <td style="text-align:center;"><?php echo $data->bdms_data->months[3]->deals + $data->telemarketers_data->months[3]->deals + $data->self_gen_data->months[3]->deals ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->issued_data->months[3]->api - $data->cancellations_data->months[3]->api,2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">May</th>
                            <td style="text-align:center;"><?php echo $data->bdms_data->months[4]->deals + $data->telemarketers_data->months[4]->deals + $data->self_gen_data->months[4]->deals ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->issued_data->months[4]->api - $data->cancellations_data->months[4]->api,2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">June</th>
                            <td style="text-align:center;"><?php echo $data->bdms_data->months[5]->deals + $data->telemarketers_data->months[5]->deals + $data->self_gen_data->months[5]->deals ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->issued_data->months[5]->api - $data->cancellations_data->months[5]->api,2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">July</th>
                            <td style="text-align:center;"><?php echo $data->bdms_data->months[6]->deals + $data->telemarketers_data->months[6]->deals + $data->self_gen_data->months[6]->deals ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->issued_data->months[6]->api - $data->cancellations_data->months[6]->api,2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">August</th>
                            <td style="text-align:center;"><?php echo $data->bdms_data->months[7]->deals + $data->telemarketers_data->months[7]->deals + $data->self_gen_data->months[7]->deals ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->issued_data->months[7]->api - $data->cancellations_data->months[7]->api,2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">September</th>
                            <td style="text-align:center;"><?php echo $data->bdms_data->months[8]->deals + $data->telemarketers_data->months[8]->deals + $data->self_gen_data->months[8]->deals ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->issued_data->months[8]->api - $data->cancellations_data->months[8]->api,2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">October</th>
                            <td style="text-align:center;"><?php echo $data->bdms_data->months[9]->deals + $data->telemarketers_data->months[9]->deals + $data->self_gen_data->months[9]->deals ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->issued_data->months[9]->api - $data->cancellations_data->months[9]->api,2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">November</th>
                            <td style="text-align:center;"><?php echo $data->bdms_data->months[10]->deals + $data->telemarketers_data->months[10]->deals + $data->self_gen_data->months[10]->deals ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->issued_data->months[10]->api - $data->cancellations_data->months[10]->api,2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">December</th>
                            <td style="text-align:center;"><?php echo $data->bdms_data->months[11]->deals + $data->telemarketers_data->months[11]->deals + $data->self_gen_data->months[11]->deals ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->issued_data->months[11]->api - $data->cancellations_data->months[11]->api,2) ?></td>
                        </tr>
                        
                    </tbody>
                </table>
            </div>
        </div>

    </div>
    
    <div class="col-sm-4">
        <!-- Third Column -->
        
        <div class="row">
            <div class="col-sm-12" >
                <h4>
                    <strong style="text-decoration: underline;"> All-Time Performance </strong>
                </h4>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-6" >
                <h4>
                    <strong>Total Net API:</strong>				
                </h4>
            </div>
            <div class="col-sm-6">
                <h4>
                $<?php echo number_format($total_issued_deals_api - $total_cancelled_deals_api,2) ?>
                </h4>
            </div>
        </div>
        
        <div class="row">
            <div class="col-sm-6">
                <h4>
                    <strong>Total Submissions:</strong>	
                </h4>
            </div>
            <div class="col-sm-6">
                <h4>
                    <?php echo $data->total_submissions; ?>
                </h4>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-6">
                <h4>
                    <strong>Total Submission API:</strong>	
                </h4>
            </div>
            <div class="col-sm-6">
                <h4>
                    $<?php echo number_format($data->total_submission_api,2); ?>
                </h4>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-6">
                <h4>
                    <strong>Total Issued Policies:</strong>				
                </h4>
            </div>
            <div class="col-sm-6">
                <h4>
                    <?php echo $total_issued_deals; ?>
                </h4>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-6">
                <h4>
                    <strong>Total Issued API:</strong>	
                </h4>
            </div>
            <div class="col-sm-6">
                <h4>
                    $<?php echo number_format($total_issued_deals_api,2); ?>
                </h4>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-6">
                <h4>
                    <strong>Pending Applications:</strong>	
                </h4>
            </div>
            <div class="col-sm-6">
                <h4>
                <?php echo $total_pending_deals; ?>
                </h4>
            </div>
        </div>
        
        <div class="row">
            <div class="col-sm-6">
                <h4>
                    <strong>Total Pending API:</strong>	
                </h4>
            </div>
            <div class="col-sm-6">
                <h4>
                $<?php echo number_format($total_pending_deals_api,2); ?>
                </h4>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-6">
                <h4>
                    <strong>Total Cancellations:</strong>	
                </h4>
            </div>
            <div class="col-sm-6">
                <h4>
                <?php echo $total_cancelled_deals; ?>
                </h4>
            </div>
        </div>
        
        <div class="row">
            <div class="col-sm-6">
                <h4>
                    <strong>Total Cancellations API:</strong>	
                </h4>
            </div>
            <div class="col-sm-6">
                <h4>
                    $<?php echo number_format($total_cancelled_deals_api,2); ?>
                </h4>
            </div>
        </div>
        
        <div class="row">
            <div class="col-sm-6" >
                <h4>
                    <strong>Total Leads:</strong>				
                </h4>
            </div>
            <div class="col-sm-6">
                <h4>
                    <?php echo $data->total_leads ?>
                </h4>
            </div>
        </div>
        
        <div class="row">
            <div class="col-sm-6" >
                <h4>
                    <strong>Total Face-To-Face Marketer Leads:</strong>				
                </h4>
            </div>
            <div class="col-sm-6">
                <h4>
                    <?php echo $data->bdms_data_total ?>
                </h4>
            </div>
        </div>
        
        <div class="row">
            <div class="col-sm-6" >
                <h4>
                    <strong>Total Telemarketer Leads:</strong>				
                </h4>
            </div>
            <div class="col-sm-6">
                <h4>
                    <?php echo $data->telemarketers_data_total ?>
                </h4>
            </div>
        </div>
        
        <div class="row">
            <div class="col-sm-6" >
                <h4>
                    <strong>Total Self-Generated Leads:</strong>				
                </h4>
            </div>
            <div class="col-sm-6">
                <h4>
                    <?php echo $data->self_gen_data_total ?>
                </h4>
            </div>
        </div>
        
        <div class="row">
            <div class="col-sm-12" >
                <h4>
                    <strong style="text-decoration: underline;"> Current Annual Performance </strong>
                </h4>
            </div>
        </div>
        
        
        <div class="row">
            <div class="col-sm-6" >
                <h4>
                    <strong>Annual Net API:</strong>				
                </h4>
            </div>
            <div class="col-sm-6">
                <h4>
                $<?php echo number_format($data->issued_data->api - $data->cancellations_data->api,2) ?>
                </h4>
            </div>
        </div>
        
        <div class="row">
            <div class="col-sm-6">
                <h4>
                    <strong>Annual Submissions:</strong>	
                </h4>
            </div>
            <div class="col-sm-6">
                <h4>
                    <?php echo $data->submissions_data->quantity; ?>
                </h4>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-6">
                <h4>
                    <strong>Annual Submission API:</strong>	
                </h4>
            </div>
            <div class="col-sm-6">
                <h4>
                    $<?php echo number_format($data->submissions_data->api,2); ?>
                </h4>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-6">
                <h4>
                    <strong>Annual Issued Policies:</strong>				
                </h4>
            </div>
            <div class="col-sm-6">
                <h4>
                    <?php echo $data->issued_data->quantity; ?>
                </h4>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-6">
                <h4>
                    <strong>Annual Issued API:</strong>	
                </h4>
            </div>
            <div class="col-sm-6">
                <h4>
                    $<?php echo number_format($data->issued_data->api,2); ?>
                </h4>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-6">
                <h4>
                    <strong>Annual Cancellations:</strong>	
                </h4>
            </div>
            <div class="col-sm-6">
                <h4>
                <?php echo $data->cancellations_data->quantity; ?>
                </h4>
            </div>
        </div>
        
        <div class="row">
            <div class="col-sm-6">
                <h4>
                    <strong>Annual Cancellations API:</strong>	
                </h4>
            </div>
            <div class="col-sm-6">
                <h4>
                    $<?php echo number_format($data->cancellations_data->api,2); ?>
                </h4>
            </div>
        </div>
        
        <div class="row">
            <div class="col-sm-6" >
                <h4>
                    <strong>Annual Leads:</strong>				
                </h4>
            </div>
            <div class="col-sm-6">
                <h4>
                    <?php echo $data->bdms_data->quantity + $data->telemarketers_data->quantity + $data->self_gen_data->quantity ?>
                </h4>
            </div>
        </div>
        
        <div class="row">
            <div class="col-sm-6" >
                <h4>
                    <strong>Annual Face-To-Face Marketer Leads:</strong>				
                </h4>
            </div>
            <div class="col-sm-6">
                <h4>
                    <?php echo $data->bdms_data->quantity ?>
                </h4>
            </div>
        </div>
        
        <div class="row">
            <div class="col-sm-6" >
                <h4>
                    <strong>Annual Telemarketer Leads:</strong>				
                </h4>
            </div>
            <div class="col-sm-6">
                <h4>
                    <?php echo $data->telemarketers_data->quantity ?>
                </h4>
            </div>
        </div>
        
        <div class="row">
            <div class="col-sm-6" >
                <h4>
                    <strong>Annual Self-Generated Leads:</strong>				
                </h4>
            </div>
            <div class="col-sm-6">
                <h4>
                    <?php echo $data->self_gen_data->quantity ?>
                </h4>
            </div>
        </div>
        
        <div class="row">
            <div class="col-sm-12" >
                <h4>
                    <strong style="text-decoration: underline;"> Current Quarterly Performance </strong>
                </h4>
            </div>
        </div>
        <?php
            $today = date('Ymd');
            $current_quarter = 0; 

            if($today<=$data->second_quarter->to->format("Ymd") && $today>=$data->second_quarter->from->format("Ymd")){
                $current_quarter = 1;
            }
            elseif($today<=$data->third_quarter->to->format("Ymd") && $today>=$data->third_quarter->from->format("Ymd")){
                $current_quarter = 2;
            }
            elseif($today<=$data->fourth_quarter->to->format("Ymd") && $today>=$data->fourth_quarter->from->format("Ymd")){
                $current_quarter = 3;
            }

            $curr_month = date('m');            
        ?>

        
        
        <div class="row">
            <div class="col-sm-6" >
                <h4>
                    <strong>Quarterly Net API:</strong>				
                </h4>
            </div>
            <div class="col-sm-6">
                <h4>
                $<?php echo number_format($data->issued_data->quarters[$current_quarter]->api - $data->cancellations_data->quarters[$current_quarter]->api,2) ?>
                </h4>
            </div>
        </div>
        
        <div class="row">
            <div class="col-sm-6">
                <h4>
                    <strong>Quarterly Submissions:</strong>	
                </h4>
            </div>
            <div class="col-sm-6">
                <h4>
                    <?php echo $data->submissions_data->quarters[$current_quarter]->quantity; ?>
                </h4>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-6">
                <h4>
                    <strong>Quarterly Submission API:</strong>	
                </h4>
            </div>
            <div class="col-sm-6">
                <h4>
                    $<?php echo number_format($data->submissions_data->quarters[$current_quarter]->api,2); ?>
                </h4>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-6">
                <h4>
                    <strong>Quarterly Issued Policies:</strong>				
                </h4>
            </div>
            <div class="col-sm-6">
                <h4>
                    <?php echo $data->issued_data->quarters[$current_quarter]->quantity; ?>
                </h4>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-6">
                <h4>
                    <strong>Quarterly Issued API:</strong>	
                </h4>
            </div>
            <div class="col-sm-6">
                <h4>
                    $<?php echo number_format($data->issued_data->quarters[$current_quarter]->api,2); ?>
                </h4>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-6">
                <h4>
                    <strong>Quarterly Cancellations:</strong>	
                </h4>
            </div>
            <div class="col-sm-6">
                <h4>
                <?php echo $data->cancellations_data->quarters[$current_quarter]->quantity; ?>
                </h4>
            </div>
        </div>
        
        <div class="row">
            <div class="col-sm-6">
                <h4>
                    <strong>Quarterly Cancellations API:</strong>	
                </h4>
            </div>
            <div class="col-sm-6">
                <h4>
                    $<?php echo number_format($data->cancellations_data->quarters[$current_quarter]->api,2); ?>
                </h4>
            </div>
        </div>
        
        <div class="row">
            <div class="col-sm-6" >
                <h4>
                    <strong>Quarterly Leads:</strong>				
                </h4>
            </div>
            <div class="col-sm-6">
                <h4>
                    <?php echo $data->bdms_data->quarters[$current_quarter]->quantity + $data->telemarketers_data->quarters[$current_quarter]->quantity + $data->self_gen_data->quarters[$current_quarter]->quantity ?>
                </h4>
            </div>
        </div>
        
        <div class="row">
            <div class="col-sm-6" >
                <h4>
                    <strong>Quarterly Face-To-Face Marketer Leads:</strong>				
                </h4>
            </div>
            <div class="col-sm-6">
                <h4>
                    <?php echo $data->bdms_data->quarters[$current_quarter]->quantity ?>
                </h4>
            </div>
        </div>
        
        <div class="row">
            <div class="col-sm-6" >
                <h4>
                    <strong>Quarterly Telemarketer Leads:</strong>				
                </h4>
            </div>
            <div class="col-sm-6">
                <h4>
                    <?php echo $data->telemarketers_data->quarters[$current_quarter]->quantity ?>
                </h4>
            </div>
        </div>
        
        <div class="row">
            <div class="col-sm-6" >
                <h4>
                    <strong>Quarterly Self-Generated Leads:</strong>				
                </h4>
            </div>
            <div class="col-sm-6">
                <h4>
                    <?php echo $data->self_gen_data->quarters[$current_quarter]->quantity ?>
                </h4>
            </div>
        </div>
        
        <div class="row">
            <div class="col-sm-12" >
                <h4>
                    <strong style="text-decoration: underline;"> Current Monthly Performance </strong>
                </h4>
            </div>
        </div>
        
        
        
        <div class="row">
            <div class="col-sm-6" >
                <h4>
                    <strong>Monthly Net API:</strong>				
                </h4>
            </div>
            <div class="col-sm-6">
                <h4>
                $<?php echo number_format($data->issued_data->months[$curr_month - 1]->api - $data->cancellations_data->months[$curr_month - 1]->api,2) ?>
                </h4>
            </div>
        </div>
        
        <div class="row">
            <div class="col-sm-6">
                <h4>
                    <strong>Monthly Submissions:</strong>	
                </h4>
            </div>
            <div class="col-sm-6">
                <h4>
                    <?php echo $data->submissions_data->months[$curr_month - 1]->deals; ?>
                </h4>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-6">
                <h4>
                    <strong>Monthly Submission API:</strong>	
                </h4>
            </div>
            <div class="col-sm-6">
                <h4>
                    $<?php echo number_format($data->submissions_data->months[$curr_month - 1]->api,2); ?>
                </h4>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-6">
                <h4>
                    <strong>Monthly Issued Policies:</strong>				
                </h4>
            </div>
            <div class="col-sm-6">
                <h4>
                    <?php echo $data->issued_data->months[$curr_month - 1]->deals; ?>
                </h4>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-6">
                <h4>
                    <strong>Monthly Issued API:</strong>	
                </h4>
            </div>
            <div class="col-sm-6">
                <h4>
                    $<?php echo number_format($data->issued_data->months[$curr_month - 1]->api,2); ?>
                </h4>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-6">
                <h4>
                    <strong>Monthly Cancellations:</strong>	
                </h4>
            </div>
            <div class="col-sm-6">
                <h4>
                <?php echo $data->cancellations_data->months[$curr_month - 1]->deals; ?>
                </h4>
            </div>
        </div>
        
        <div class="row">
            <div class="col-sm-6">
                <h4>
                    <strong>Monthly Cancellations API:</strong>	
                </h4>
            </div>
            <div class="col-sm-6">
                <h4>
                    $<?php echo number_format($data->cancellations_data->months[$curr_month - 1]->api,2); ?>
                </h4>
            </div>
        </div>
        
        <div class="row">
            <div class="col-sm-6" >
                <h4>
                    <strong>Monthly Leads:</strong>				
                </h4>
            </div>
            <div class="col-sm-6">
                <h4>
                    <?php 
                        echo $data->bdms_data->months[$curr_month-1]->deals + $data->telemarketers_data->months[$curr_month-1]->deals + $data->self_gen_data->months[$curr_month - 1]->deals 
                    ?>
                </h4>
            </div>
        </div>
        
        <div class="row">
            <div class="col-sm-6" >
                <h4>
                    <strong>Monthly Face-To-Face Marketer Leads:</strong>				
                </h4>
            </div>
            <div class="col-sm-6">
                <h4>
                    <?php echo $data->bdms_data->months[$curr_month - 1]->deals ?>
                </h4>
            </div>
        </div>
        
        <div class="row">
            <div class="col-sm-6" >
                <h4>
                    <strong>Monthly Telemarketer Leads:</strong>				
                </h4>
            </div>
            <div class="col-sm-6">
                <h4>
                    <?php echo $data->telemarketers_data->months[$curr_month - 1]->deals ?>
                </h4>
            </div>
        </div>
        
        <div class="row">
            <div class="col-sm-6" >
                <h4>
                    <strong>Monthly Self-Generated Leads:</strong>				
                </h4>
            </div>
            <div class="col-sm-6">
                <h4>
                    <?php echo $data->self_gen_data->months[$curr_month - 1]->deals ?>
                </h4>
            </div>
        </div>
    </div>

</div>
<!--
<table class="table">
    <thead>
        <tr>
            <th>Client</th>
            <th>Lead By</th>
            <th>API</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $total = 0;
            foreach($data->debug->client as $client_debug){
                if($client_debug->issued_api>0){
                    $total += $client_debug->issued_api;
                    echo "
                        <tr>
                            <td>$client_debug->name</td>
                            <td>$client_debug->lead_gen</td>
                            <td>$client_debug->issued_api</td>
                        </tr>
                    ";
                }
            }
            echo "
            <tr>
                <td>Total</td>
                <td></td>
                <td>$total</td>
            </tr>
            ";
        ?>
    </tbody>
</table>
-->
</html>

<?php

}


}
  

 


?>