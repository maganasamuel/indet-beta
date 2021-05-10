<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">
<link rel="stylesheet" href="styles.css">
<link id="favicon" rel="icon" href="Logo_ImageOnly.png" type="image/png" sizes="16x16">
<title>INDET</title> 
</head>
<?php

session_start();
if(!isset($_SESSION["myusername"])){
session_destroy();
header("Refresh:0; url=index.php");
}

else{
    
include "partials/nav_bar.html";
require "database.php";

class QuarterData {
    public $quantity = 0;
    public $api = 0;
}

class MonthData {
    public $deals = 0;
    public $api = 0;
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

$adviser = array();
$all_deals = array();
$data = new stdClass();

$data->year = date("Y");
if(isset($_GET["year"]))
    $data->year = $_GET["year"];

$data->first_quarter = GetQuarter("First",$data->year);
$data->second_quarter = GetQuarter("Second",$data->year);
$data->third_quarter = GetQuarter("Third",$data->year);
$data->fourth_quarter = GetQuarter("Fourth",$data->year);

//Lead Gen
$data->total_leads = 0;

//Telemarketers Data
$data->telemarketers_data = new stdClass();
$data->telemarketers_data->total = 0;
$data->telemarketers_data->first_quarter = 0;
$data->telemarketers_data->second_quarter = 0;
$data->telemarketers_data->third_quarter = 0;
$data->telemarketers_data->fourth_quarter = 0;
$data->telemarketers_data->first_quarter_api = 0;
$data->telemarketers_data->second_quarter_api = 0;
$data->telemarketers_data->third_quarter_api = 0;
$data->telemarketers_data->fourth_quarter_api = 0;
$data->telemarketers_data->annual = 0;
$data->telemarketers_data->annual_api = 0;


$data->bdms_data = new stdClass();
$data->bdms_data->total = 0;
$data->bdms_data->first_quarter = 0;
$data->bdms_data->second_quarter = 0;
$data->bdms_data->third_quarter = 0;
$data->bdms_data->fourth_quarter = 0;
$data->bdms_data->first_quarter_api = 0;
$data->bdms_data->second_quarter_api = 0;
$data->bdms_data->third_quarter_api = 0;
$data->bdms_data->fourth_quarter_api = 0;
$data->bdms_data->annual = 0;
$data->bdms_data->annual_api = 0;


$data->self_gen_data = new stdClass();
$data->self_gen_data->total = 0;
$data->self_gen_data->first_quarter = 0;
$data->self_gen_data->second_quarter = 0;
$data->self_gen_data->third_quarter = 0;
$data->self_gen_data->fourth_quarter = 0;
$data->self_gen_data->first_quarter_api = 0;
$data->self_gen_data->second_quarter_api = 0;
$data->self_gen_data->third_quarter_api = 0;
$data->self_gen_data->fourth_quarter_api = 0;
$data->self_gen_data->annual = 0;
$data->self_gen_data->annual_api = 0;

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

//Define Quarter_Data
$quarter_data = new stdClass;
$quarter_data->deals = 0;
$quarter_data->api = 0;

//Define Month
$month = new stdClass;
$month->deals = 0;
$month->api = 0;


$data->issued_data = new YearData(12);

$data->submissions_data = new YearData();

$data->cancellations_data = new YearData();


while($row=mysqli_fetch_assoc($displayquery)){
    extract($row);
    
    $total_client_api = 0;

	if($ctr==0){
		$adviser = (object) $row;
	}
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
                
        $data->total_submissions++;
		$submission_date = date("Ymd", strtotime($date_submitted));
		$date_to_compare = $submission_date;
		$sub_deals = json_decode($deals);
		foreach($sub_deals as $deal){
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

                    $data->submissions_data->quantity++;
                    $data->submissions_data->api+= $deal->original_api;

                }
                elseif($d_to_compare<=$data->second_quarter->to->format("Ymd") && $d_to_compare>=$data->second_quarter->from->format("Ymd")){
                    $data->submissions_data->quarters[1]->quantity++;
                    $data->submissions_data->quarters[1]->api+= $deal->original_api; 

                    $data->submissions_data->quantity++;
                    $data->submissions_data->api+= $deal->original_api;
                }
                elseif($d_to_compare<=$data->third_quarter->to->format("Ymd") && $d_to_compare>=$data->third_quarter->from->format("Ymd")){
                    $data->submissions_data->quarters[2]->quantity++;
                    $data->submissions_data->quarters[2]->api+= $deal->original_api;     
                    //echo "3 Q";     
                    $data->submissions_data->quantity++;
                    $data->submissions_data->api+= $deal->original_api;
                }
                elseif($d_to_compare<=$data->fourth_quarter->to->format("Ymd") && $d_to_compare>=$data->fourth_quarter->from->format("Ymd")){
                    $data->submissions_data->quarters[3]->quantity++;
                    $data->submissions_data->quarters[3]->api+= $deal->original_api;     
                    //echo "4 Q";     
                    $data->submissions_data->quantity++;
                    $data->submissions_data->api+= $deal->original_api;
                }

                //Monthly Data
                $m1 = date('m',strtotime($d_to_compare));
                $data->submissions_data->months[($m1 - 1)]->deals++;
                $data->submissions_data->months[($m1 - 1)]->api+= $deal->original_api;
            
			if($deal->status=="Pending"){
				$total_pending_deals++;
				$total_pending_deals_api += $deal->original_api;
				$life_insured = $client_name;
				if(!empty($deal->life_insured))
					$life_insured .= ", " . $deal->life_insured;

				$data->submitted[] = array(
					"Client" => $life_insured,
					"Date" => NZEntryToDateTime($deal->submission_date),
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
					"Date" => NZEntryToDateTime($deal->date_issued),
					"Deal" => $deal,
					"IssuedAPI" => $deal->issued_api,
				);
                //echo "<hr>". NZEntryToDateTime($deal->date_issued);
                //Quarterly and Monthly 
                $d_to_compare = $deal->date_issued;
                
                if($d_to_compare<=$data->first_quarter->to->format("Ymd") && $d_to_compare>=$data->first_quarter->from->format("Ymd")){
                    $data->issued_data->quarters[0]->quantity++;
                    $data->issued_data->quarters[0]->api+= $deal->issued_api;

                    $data->issued_data->quantity++;
                    $data->issued_data->api+= $deal->issued_api;

                }
                elseif($d_to_compare<=$data->second_quarter->to->format("Ymd") && $d_to_compare>=$data->second_quarter->from->format("Ymd")){
                    $data->issued_data->quarters[1]->quantity++;
                    $data->issued_data->quarters[1]->api+= $deal->issued_api; 

                    $data->issued_data->quantity++;
                    $data->issued_data->api+= $deal->issued_api;
                }
                elseif($d_to_compare<=$data->third_quarter->to->format("Ymd") && $d_to_compare>=$data->third_quarter->from->format("Ymd")){
                    $data->issued_data->quarters[2]->quantity++;
                    $data->issued_data->quarters[2]->api+= $deal->issued_api;     
                    //echo "3 Q";     
                    $data->issued_data->quantity++;
                    $data->issued_data->api+= $deal->issued_api;
                }
                elseif($d_to_compare<=$data->fourth_quarter->to->format("Ymd") && $d_to_compare>=$data->fourth_quarter->from->format("Ymd")){
                    $data->issued_data->quarters[3]->quantity++;
                    $data->issued_data->quarters[3]->api+= $deal->issued_api;     
                    //echo "4 Q";     
                    $data->issued_data->quantity++;
                    $data->issued_data->api+= $deal->issued_api;
                }

                $m1 = date('m',strtotime($d_to_compare));
                $data->issued_data->months[($m1 - 1)]->deals++;
                $data->issued_data->months[($m1 - 1)]->api+= $deal->issued_api;

				if(isset($deal->clawback_status)){
					if($deal->clawback_status=="Cancelled"){	
						$total_cancelled_deals++;
						$total_cancelled_deals_api += $deal->clawback_api;
						$life_insured = $client_name;
						if(!empty($deal->life_insured))
							$life_insured .= ", " . $deal->life_insured;

						$data->cancelled_deals[] = array(
							"Client" => $life_insured,
							"Date" => NZEntryToDateTime($deal->clawback_date),
							"Deal" => $deal,
							"CancelledAPI" => $deal->clawback_api,
                        );
                        
                        $d_to_compare = $deal->clawback_date;

                        //Quarterly Data
                        if($d_to_compare<=$data->first_quarter->to->format("Ymd") && $d_to_compare>=$data->first_quarter->from->format("Ymd")){
                            $data->cancellations_data->quarters[0]->quantity++;
                            $data->cancellations_data->quarters[0]->api+= $deal->clawback_api;

                            $data->cancellations_data->quantity++;
                            $data->cancellations_data->api+= $deal->clawback_api;

                        }
                        elseif($d_to_compare<=$data->second_quarter->to->format("Ymd") && $d_to_compare>=$data->second_quarter->from->format("Ymd")){
                            $data->cancellations_data->quarters[1]->quantity++;
                            $data->cancellations_data->quarters[1]->api+= $deal->clawback_api; 

                            $data->cancellations_data->quantity++;
                            $data->cancellations_data->api+= $deal->clawback_api;
                        }
                        elseif($d_to_compare<=$data->third_quarter->to->format("Ymd") && $d_to_compare>=$data->third_quarter->from->format("Ymd")){
                            $data->cancellations_data->quarters[2]->quantity++;
                            $data->cancellations_data->quarters[2]->api+= $deal->clawback_api;     
                            //echo "3 Q";     
                            $data->cancellations_data->quantity++;
                            $data->cancellations_data->api+= $deal->clawback_api;
                        }
                        elseif($d_to_compare<=$data->fourth_quarter->to->format("Ymd") && $d_to_compare>=$data->fourth_quarter->from->format("Ymd")){
                            $data->cancellations_data->quarters[3]->quantity++;
                            $data->cancellations_data->quarters[3]->api+= $deal->clawback_api;     
                            //echo "4 Q";     
                            $data->cancellations_data->quantity++;
                            $data->cancellations_data->api+= $deal->clawback_api;
                        }

                        //Monthly Data
                        $m1 = date('m',strtotime($d_to_compare));
                        $data->cancellations_data->months[($m1 - 1)]->deals++;
                        $data->cancellations_data->months[($m1 - 1)]->api+= $deal->clawback_api;

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

		//var_dump($row);
		//echo "<hr>";
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
        $data->telemarketers_data->total++;
        
        //For First Quarter
        if($date_submitted<=$data->first_quarter->to->format("Ymd") && $date_submitted>=$data->first_quarter->from->format("Ymd")){
            $data->telemarketers_data->first_quarter++;
            $data->telemarketers_data->first_quarter_api+= $total_client_api;
            
            //Add Annual
            $data->telemarketers_data->annual++;
            $data->telemarketers_data->annual_api+= $total_client_api;            
        }

        //For Second Quarter
        if($date_submitted<=$data->second_quarter->to->format("Ymd") && $date_submitted>=$data->second_quarter->from->format("Ymd")){
            $data->telemarketers_data->second_quarter++;
            $data->telemarketers_data->second_quarter_api+= $total_client_api;
            
            //Add Annual
            $data->telemarketers_data->annual++;
            $data->telemarketers_data->annual_api+= $total_client_api;            
        }

        
        //For Third Quarter
        if($date_submitted<=$data->third_quarter->to->format("Ymd") && $date_submitted>=$data->third_quarter->from->format("Ymd")){
            $data->telemarketers_data->third_quarter++;
            $data->telemarketers_data->third_quarter_api+= $total_client_api;
            
            //Add Annual
            $data->telemarketers_data->annual++;
            $data->telemarketers_data->annual_api+= $total_client_api;            
        }
        
        //For Third Quarter
        if($date_submitted<=$data->fourth_quarter->to->format("Ymd") && $date_submitted>=$data->fourth_quarter->from->format("Ymd")){
            $data->telemarketers_data->fourth_quarter++;
            $data->telemarketers_data->fourth_quarter_api+= $total_client_api;
            
            //Add Annual
            $data->telemarketers_data->annual++;
            $data->telemarketers_data->annual_api+= $total_client_api;            
        }
    }
    elseif($lead_by=="Face-to-Face Marketer"){
        $data->bdms_data->total++;
        
        //For First Quarter
        if($date_submitted<=$data->first_quarter->to->format("Ymd") && $date_submitted>=$data->first_quarter->from->format("Ymd")){
            $data->bdms_data->first_quarter++;
            $data->bdms_data->first_quarter_api+= $total_client_api;
            
            //Add Annual
            $data->bdms_data->annual++;
            $data->bdms_data->annual_api+= $total_client_api;            
        }

        //For Second Quarter
        if($date_submitted<=$data->second_quarter->to->format("Ymd") && $date_submitted>=$data->second_quarter->from->format("Ymd")){
            $data->bdms_data->second_quarter++;
            $data->bdms_data->second_quarter_api+= $total_client_api;
            
            //Add Annual
            $data->bdms_data->annual++;
            $data->bdms_data->annual_api+= $total_client_api;            
        }

        
        //For Third Quarter
        if($date_submitted<=$data->third_quarter->to->format("Ymd") && $date_submitted>=$data->third_quarter->from->format("Ymd")){
            $data->bdms_data->third_quarter++;
            $data->bdms_data->third_quarter_api+= $total_client_api;
            
            //Add Annual
            $data->bdms_data->annual++;
            $data->bdms_data->annual_api+= $total_client_api;            
        }
        
        //For Third Quarter
        if($date_submitted<=$data->fourth_quarter->to->format("Ymd") && $date_submitted>=$data->fourth_quarter->from->format("Ymd")){
            $data->bdms_data->fourth_quarter++;
            $data->bdms_data->fourth_quarter_api+= $total_client_api;
            
            //Add Annual
            $data->bdms_data->annual++;
            $data->bdms_data->annual_api+= $total_client_api;            
        }
    }
    elseif($lead_by=="Self-Generated"){
        $data->self_gen_data->total++;
        
        //For First Quarter
        if($date_submitted<=$data->first_quarter->to->format("Ymd") && $date_submitted>=$data->first_quarter->from->format("Ymd")){
            $data->self_gen_data->first_quarter++;
            $data->self_gen_data->first_quarter_api+= $total_client_api;
            
            //Add Annual
            $data->self_gen_data->annual++;
            $data->self_gen_data->annual_api+= $total_client_api;            
        }

        //For Second Quarter
        if($date_submitted<=$data->second_quarter->to->format("Ymd") && $date_submitted>=$data->second_quarter->from->format("Ymd")){
            $data->self_gen_data->second_quarter++;
            $data->self_gen_data->second_quarter_api+= $total_client_api;
            
            //Add Annual
            $data->self_gen_data->annual++;
            $data->self_gen_data->annual_api+= $total_client_api;            
        }

        
        //For Third Quarter
        if($date_submitted<=$data->third_quarter->to->format("Ymd") && $date_submitted>=$data->third_quarter->from->format("Ymd")){
            $data->self_gen_data->third_quarter++;
            $data->self_gen_data->third_quarter_api+= $total_client_api;
            
            //Add Annual
            $data->self_gen_data->annual++;
            $data->self_gen_data->annual_api+= $total_client_api;            
        }
        
        //For Third Quarter
        if($date_submitted<=$data->fourth_quarter->to->format("Ymd") && $date_submitted>=$data->fourth_quarter->from->format("Ymd")){
            $data->self_gen_data->fourth_quarter++;
            $data->self_gen_data->fourth_quarter_api+= $total_client_api;
            
            //Add Annual
            $data->self_gen_data->annual++;
            $data->self_gen_data->annual_api+= $total_client_api;            
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
	  height: 2500%;
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
    <h2 class="slide">ELITEINSURE LTD. STATISTICS</h2>
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
    <div class="col-sm-2" >
        <h4>
            <strong>Total Cumulative Leads:	  </strong>				
        </h4>
    </div>
    <div class="col-sm-2">
        <h4>
            <?php echo $data->total_leads ?>
        </h4>
        </div>
    <div class="col-sm-2">
        <h4>
            <strong>Total Cumulative Submissions:</strong>	
        </h4>
    </div>
    <div class="col-sm-2">
        <h4>
            <?php echo $data->total_submissions; ?>
        </h4>
    </div>
    <div class="col-sm-2">
        <h4>
            <strong>Annual Total Leads:</strong>	
        </h4>
    </div>
    <div class="col-sm-2">
        <h4>
            <?php 
                echo ($data->telemarketers_data->annual + $data->bdms_data->annual + $data->self_gen_data->annual);
            ?>
        </h4>
    </div>
</div>

<div class="row">
            <div class="col-sm-2" >
                <h4>
                    <strong>Total Face-to-Face Marketer Leads:</strong>	  				
                </h4>
            </div>
            <div class="col-sm-2">
                <h4>
                <?php echo $data->bdms_data->total ?>
                </h4>
            </div>
            <div class="col-sm-2">
                <h4>
                    <strong>Total Submission API:</strong>	
                </h4>
            </div>
            <div class="col-sm-2">
                <h4>
                    $<?php echo number_format($data->total_submission_api,2); ?>
                </h4>
            </div>
            <div class="col-sm-2">
                <h4>
                   <strong>Total Cumulative Net API</strong>
                </h4>
            </div>
            <div class="col-sm-2">
                <h4>
                    $<?php echo number_format($total_issued_deals_api - $total_cancelled_deals_api,2) ?>
                </h4>
            </div>
            
        </div>
    </div>

    <div class="row">
        <div class="col-sm-2" >
	  		<h4>
              <strong>Total Telemarketer Leads:</strong>	  				
  			</h4>
  		</div>
	  	<div class="col-sm-2">
	  		<h4>
              <?php echo $data->telemarketers_data->total ?>
	  		</h4>
          </div>
        <div class="col-sm-2">
            <h4>
                <strong>Total Cumulative Issued Policies:</strong>				
            </h4>
        </div>
        <div class="col-sm-2">
            <h4>
                <?php echo $total_issued_deals; ?>
            </h4>
        </div>
	  	<div class="col-sm-2">
	  		<h4>
	  			
	  		</h4>
	  	</div>
	  	<div class="col-sm-2">
	  		<h4>
	  			
	  		</h4>
	  	</div>
	  	
    </div>

    
    <div class="row">
        <div class="col-sm-2" >
	  		<h4>
              <strong>Total Self-Generated Leads:</strong>	  				
  			</h4>
  		</div>
	  	<div class="col-sm-2">
	  		<h4>
              <?php echo $data->self_gen_data->total ?>
	  		</h4>
          </div>
        <div class="col-sm-2">
            <h4>
                <strong>Total Issued API:</strong>	
            </h4>
        </div>
        <div class="col-sm-2">
            <h4>
                $<?php echo number_format($total_issued_deals_api,2); ?>
            </h4>
        </div>
	  	<div class="col-sm-2">
	  		<h4>
	  			
	  		</h4>
	  	</div>
	  	<div class="col-sm-2">
	  		<h4>
	  			
	  		</h4>
	  	</div>
    </div>
    
    <div class="row">
        <div class="col-sm-4" >
        <table class="table">
            <thead>  
                <tr>
                    <th colspan="3" style="text-align:center;"><?php echo $data->year ?> Face-to-Face Marketer Quarterly Data</th>
                </tr>
                <tr>
                    <th scope="col" style="text-align:center;">Quarter</th>
                    <th scope="col" style="text-align:center;">Generated</th>
                    <th scope="col" style="text-align:center;">API</th>
                </tr>
            </thead>
            
            <tbody>
                <tr>
                    <th scope="row" style="text-align:center;">First</th>
                    <td style="text-align:center;"><?php echo $data->bdms_data->first_quarter ?></td>
                    <td style="text-align:center;">$<?php echo number_format($data->bdms_data->first_quarter_api,2) ?></td>
                </tr>
                <tr>
                    <th scope="row"  style="text-align:center;">Second</th>
                    
                    <td><?php echo $data->bdms_data->second_quarter ?></td>
                    <td>$<?php echo number_format($data->bdms_data->second_quarter_api,2) ?></td>
                </tr>
                <tr>
                    <th scope="row" style="text-align:center;">Third</th>
                    <td><?php echo $data->bdms_data->third_quarter ?></td>
                    <td>$<?php echo number_format($data->bdms_data->third_quarter_api,2) ?></td>
                </tr>
                <tr>
                    <th scope="row" style="text-align:center;">Fourth</th>
                    <td><?php echo $data->bdms_data->fourth_quarter ?></td>
                    <td>$<?php echo number_format($data->bdms_data->fourth_quarter_api,2) ?></td>
                </tr>
                <tr>
                    <th scope="row" style="text-align:center;">Total</th>
                    <td><?php echo $data->bdms_data->annual ?></td>
                    <td>$<?php echo number_format($data->bdms_data->annual_api,2) ?></td>
                </tr>
            </tbody>
            </table>
  		</div>
	  	
        <div class="col-sm-2">
            <h4>
                <strong>
                    Pending Applications:<br>
                    Total Pending API:<br>
                    Total Cumulative Cancellations:<br>
                    Total Cumulative Cancellations API:
                </strong>	
            </h4>
        </div>
        <div class="col-sm-2">
            <h4>
                <?php echo $total_pending_deals; ?><br>
                $<?php echo number_format($total_pending_deals_api,2); ?><br>
                <?php echo $total_cancelled_deals; ?><br><br><br>
                $<?php echo number_format($total_cancelled_deals_api,2); ?>
            </h4>
  		</div>
        <div class="col-sm-4">
        <table class="table">
                <thead>  
                    <tr>
                        <th colspan="3" style="text-align:center;"><?php echo $data->year ?> Net API Quarterly Data</th>
                    </tr>
                    <tr>
                        <th scope="col" style="text-align:center;">Quarter</th>
                        <th scope="col" style="text-align:center;">API</th>
                    </tr>
                </thead>
                
                <tbody>
                    <tr>
                        <th scope="row" style="text-align:center;">First</th>
                        <td style="text-align:center;">$<?php echo number_format($data->issued_data->quarters[0]->api - $data->cancellations_data->quarters[0]->api ,2) ?></td>
                    </tr>
                    <tr>
                        <th scope="row" style="text-align:center;">Second</th>
                        <td style="text-align:center;">$<?php echo number_format($data->issued_data->quarters[1]->api - $data->cancellations_data->quarters[1]->api,2) ?></td>
                    </tr>
                    <tr>
                        <th scope="row" style="text-align:center;">Third</th>
                        <td style="text-align:center;">$<?php echo number_format($data->issued_data->quarters[2]->api - $data->cancellations_data->quarters[2]->api,2) ?></td>
                    </tr>
                    <tr>
                        <th scope="row" style="text-align:center;">Fourth</th>
                        <td style="text-align:center;">$<?php echo number_format($data->issued_data->quarters[3]->api - $data->cancellations_data->quarters[3]->api,2) ?></td>
                    </tr>
                    <tr>
                        <th scope="row" style="text-align:center;">Total</th>
                        <td style="text-align:center;">$<?php echo number_format(($data->issued_data->api - $data->cancellations_data->api),2) ?></td>
                    </tr>
                </tbody>
            </table>
	  	</div>
	  	
    </div>

    

    <div class="row">
        <div class="col-sm-4" >
            <table class="table">
                <thead>  
                    <tr>
                        <th colspan="3" style="text-align:center;"><?php echo $data->year ?> Telemarketer Quarterly Data</th>
                    </tr>
                    <tr>
                        <th scope="col" style="text-align:center;">Quarter</th>
                        <th scope="col" style="text-align:center;">Generated</th>
                        <th scope="col" style="text-align:center;">API</th>
                    </tr>
                </thead>
                
                <tbody>
                    <tr>
                        <th scope="row" style="text-align:center;">First</th>
                        <td style="text-align:center;"><?php echo $data->telemarketers_data->first_quarter ?></td>
                        <td style="text-align:center;">$<?php echo number_format($data->telemarketers_data->first_quarter_api,2) ?></td>
                    </tr>
                    <tr>
                        <th scope="row"  style="text-align:center;">Second</th>
                        
                        <td><?php echo $data->telemarketers_data->second_quarter ?></td>
                        <td>$<?php echo number_format($data->telemarketers_data->second_quarter_api,2) ?></td>
                    </tr>
                    <tr>
                        <th scope="row" style="text-align:center;">Third</th>
                        <td><?php echo $data->telemarketers_data->third_quarter ?></td>
                        <td>$<?php echo number_format($data->telemarketers_data->third_quarter_api,2) ?></td>
                    </tr>
                    <tr>
                        <th scope="row" style="text-align:center;">Fourth</th>
                        <td><?php echo $data->telemarketers_data->fourth_quarter ?></td>
                        <td>$<?php echo number_format($data->telemarketers_data->fourth_quarter_api,2) ?></td>
                    </tr>
                    <tr>
                        <th scope="row" style="text-align:center;">Total</th>
                        <td><?php echo $data->telemarketers_data->annual ?></td>
                        <td>$<?php echo number_format($data->telemarketers_data->annual_api,2) ?></td>
                    </tr>
                </tbody>
            </table>

            <table class="table">
                <thead>  
                    <tr>
                        <th colspan="3" style="text-align:center;"><?php echo $data->year ?> Self-Generated Quarterly Data</th>
                    </tr>
                    <tr>
                        <th scope="col" style="text-align:center;">Quarter</th>
                        <th scope="col" style="text-align:center;">Generated</th>
                        <th scope="col" style="text-align:center;">API</th>
                    </tr>
                </thead>
                
                <tbody>
                    <tr>
                        <th scope="row" style="text-align:center;">First</th>
                        <td style="text-align:center;"><?php echo $data->self_gen_data->first_quarter ?></td>
                        <td style="text-align:center;">$<?php echo number_format($data->self_gen_data->first_quarter_api,2) ?></td>
                    </tr>
                    <tr>
                        <th scope="row"  style="text-align:center;">Second</th>
                        
                        <td><?php echo $data->self_gen_data->second_quarter ?></td>
                        <td>$<?php echo number_format($data->self_gen_data->second_quarter_api,2) ?></td>
                    </tr>
                    <tr>
                       
                        <th scope="row" style="text-align:center;">Third</th>
                        <td><?php echo $data->self_gen_data->third_quarter ?></td>
                        <td>$<?php echo number_format($data->self_gen_data->third_quarter_api,2) ?></td>
                    </tr>
                    <tr>
                        <th scope="row" style="text-align:center;">Fourth</th>
                        <td><?php echo $data->self_gen_data->fourth_quarter ?></td>
                        <td>$<?php echo number_format($data->self_gen_data->fourth_quarter_api,2) ?></td>
                    </tr>
                    <tr>
                        <th scope="row" style="text-align:center;">Total</th>
                        <td><?php echo $data->self_gen_data->annual ?></td>
                        <td>$<?php echo number_format($data->self_gen_data->annual_api,2) ?></td>
                    </tr>
                </tbody>
            </table>
  		</div>
	  	
        <div class="col-sm-4">
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
	  	<div class="col-sm-4">
          <table class="table">
                <thead>  
                    <tr>
                        <th colspan="3" style="text-align:center;"><?php echo $data->year ?> Net API Monthly Data</th>
                    </tr>
                    <tr>
                        <th scope="col" style="text-align:center;">Month</th>
                        <th scope="col" style="text-align:center;">API</th>
                    </tr>
                </thead>
                
                <tbody>
                    <tr>
                        <th scope="row" style="text-align:center;">January</th>
                        <td style="text-align:center;">$<?php echo number_format($data->issued_data->months[0]->api - $data->cancellations_data->months[0]->api,2) ?></td>
                    </tr>
                    <tr>
                        <th scope="row" style="text-align:center;">February</th>
                        <td style="text-align:center;">$<?php echo number_format($data->issued_data->months[1]->api - $data->cancellations_data->months[1]->api,2) ?></td>
                    </tr>
                    <tr>
                        <th scope="row" style="text-align:center;">March</th>
                        <td style="text-align:center;">$<?php echo number_format($data->issued_data->months[2]->api - $data->cancellations_data->months[2]->api,2) ?></td>
                    </tr>
                    <tr>
                        <th scope="row" style="text-align:center;">April</th>
                        <td style="text-align:center;">$<?php echo number_format($data->issued_data->months[3]->api - $data->cancellations_data->months[3]->api,2) ?></td>
                    </tr>
                    <tr>
                        <th scope="row" style="text-align:center;">May</th>
                        <td style="text-align:center;">$<?php echo number_format($data->issued_data->months[4]->api - $data->cancellations_data->months[4]->api,2) ?></td>
                    </tr>
                    <tr>
                        <th scope="row" style="text-align:center;">June</th>
                        <td style="text-align:center;">$<?php echo number_format($data->issued_data->months[5]->api - $data->cancellations_data->months[5]->api,2) ?></td>
                    </tr>
                    <tr>
                        <th scope="row" style="text-align:center;">July</th>
                        <td style="text-align:center;">$<?php echo number_format($data->issued_data->months[6]->api - $data->cancellations_data->months[6]->api,2) ?></td>
                    </tr>
                    <tr>
                        <th scope="row" style="text-align:center;">August</th>
                        <td style="text-align:center;">$<?php echo number_format($data->issued_data->months[7]->api - $data->cancellations_data->months[7]->api,2) ?></td>
                    </tr>
                    <tr>
                        <th scope="row" style="text-align:center;">September</th>
                        <td style="text-align:center;">$<?php echo number_format($data->issued_data->months[8]->api - $data->cancellations_data->months[8]->api,2) ?></td>
                    </tr>
                    <tr>
                        <th scope="row" style="text-align:center;">October</th>
                        <td style="text-align:center;">$<?php echo number_format($data->issued_data->months[9]->api - $data->cancellations_data->months[9]->api,2) ?></td>
                    </tr>
                    <tr>
                        <th scope="row" style="text-align:center;">November</th>
                        <td style="text-align:center;">$<?php echo number_format($data->issued_data->months[10]->api - $data->cancellations_data->months[10]->api,2) ?></td>
                    </tr>
                    <tr>
                        <th scope="row" style="text-align:center;">December</th>
                        <td style="text-align:center;">$<?php echo number_format($data->issued_data->months[11]->api - $data->cancellations_data->months[11]->api,2) ?></td>
                    </tr>
                    
                </tbody>
            </table>
	  	</div>
	  	
    </div>
    


    <div class="row">
        <div class="col-sm-4" >
	  		<h4>
              			
  			</h4>
  		</div>
	  	<div class="col-sm-4">
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
          <div class="col-sm-2">
		  	<h4>
		  		
		  	</h4>
        </div>
	  	<div class="col-sm-2">
	  		<h4>
	  			
	  		</h4>
	  	</div>
	  	
</div>

<div class="row">
        <div class="col-sm-4" >
  		</div>
	  	
        <div class="col-sm-4">
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
	  	<div class="col-sm-2">
	  	</div>
	  	<div class="col-sm-2">
	  		<h4>
	  			
	  		</h4>
	  	</div>
	  	
    </div>

    

<div class="row">
        <div class="col-sm-4" >
	  		<h4>
              			
  			</h4>
  		</div>
	  	<div class="col-sm-4">
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
          <div class="col-sm-2">
		  	<h4>
		  		
		  	</h4>
        </div>
	  	<div class="col-sm-2">
	  		<h4>
	  			
	  		</h4>
	  	</div>
	  	
</div>

<div class="row">
        <div class="col-sm-4" >
  		</div>
	  	
        <div class="col-sm-4">
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
	  	<div class="col-sm-2">
	  	</div>
	  	<div class="col-sm-2">
	  		<h4>
	  			
	  		</h4>
	  	</div>
	  	
    </div>





</html>

<?php

}


function DateTimeToNZEntry($date_submitted){
    return substr($date_submitted,6,4).substr($date_submitted,3,2).substr($date_submitted,0,2);
  }
  
  function NZEntryToDateTime($NZEntry){
      return substr($NZEntry,6,2) . "/" . substr($NZEntry,4,2) . "/" . substr($NZEntry, 0, 4);
  }

  
function GetQuarter($quarter, $year){
    $op = new stdClass();
    $firstDay = getMonday(new \DateTime("$year/01/01"));
    $op->from = clone $firstDay;

    switch($quarter){
        case "First":        
        break;
        case "Second":
            $op->from = $op->from->modify('+91 days');
        break;
        case "Third":
            $op->from = $op->from->modify('+182 days');
        break;
        case "Fourth":
            $op->from = $op->from->modify('+273 days');
        break;
    }
    
    $op->to = clone $op->from;
    $op->to = $op->to->modify('+90 days');
    if($quarter=="Fourth" && $op->to->format("d")<=24){
        $op->to = $op->to->modify('+7 days');
    }
    return $op;
}

function getMonday($date = null)
{
    if ($date instanceof \DateTime) {
        $date = clone $date;
    } else if (!$date) {
        $date = new \DateTime();
    } else {
        $date = new \DateTime($date);
    }
    
    $date->setTime(0, 0, 0);
    $Nday = $date->format('N'); 
    if ($Nday == 1) {
        // If the date is already a Monday, return it as-is
        return $date;
    } 
    elseif($Nday == 0) {
        // Otherwise, return the date of the nearest Monday in the past
        // This includes Sunday in the previous week instead of it being the start of a new week
        return $date->modify('last monday');
    }
    else {
        // Otherwise, return the date of the nearest Monday in the past
        // This includes Sunday in the previous week instead of it being the start of a new week
        return $date->modify('monday this week');
    }
}

function monthVariable($m){
    $op = "";
    switch($m){
        case "01":
            $op="jan";
        break;
        case "02":
            $op="feb";
        break;
        case "03":
            $op="mar";
        break;
        case "04":
            $op="apr";
        break;
        case "05":
            $op="may";
        break;
        case "06":
            $op="jun";
        break;
        case "07":
            $op="jul";
        break;
        case "08":
            $op="aug";
        break;
        case "09":
            $op="sep";
        break;
        case "10":
            $op="oct";
        break;
        case "11":
            $op="nov";
        break;
        case "12":
            $op="dec";
        break;
    }
    return $op;
}
?>