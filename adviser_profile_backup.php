 <?php
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
 <html>
<head>

<!--nav bar-->
<?php include "partials/nav_bar.html";?>
<!--nav bar end-->
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">
<link rel="stylesheet" href="styles.css">
<link id="favicon" rel="icon" href="Logo_ImageOnly.png" type="image/png" sizes="16x16">
<title>INDET</title>
<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.2/css/all.css" integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">
<script>
$(function(){
	
/*
$('#me').dataTable({
"columnDefs": [ {
"targets": [14,15],
"orderable": false
} ]
});
*/
});

</script>
</head>

<body>
<div align="center">
  <div class="jumbotron">
    <h2 class="slide">Adviser Profile</h2>
</div>

<?php require "database.php";

$con=mysqli_connect($host,$username,$password,$db)or die("could not connect to sql");
if (!$con) {
        echo "<div>";
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
	    echo "</div>";	
}
$adviser_id = $_GET["id"];

$adviser = array();
$all_deals = array();
$clients = new stdClass();

//Total
$clients->total_leads = 0;
$clients->total_issued = 0;
$clients->total_issued_api = 0;

//For Period
$clients->leads_assigned_for_period = 0;
$clients->leads_submitted_for_period = 0;
$clients->leads_issued_for_period = 0;
$clients->issued_api_for_period = 0;

//Fetch date span today
$now = $initial = $end = date("Ymd");
$due=date('d/m/Y', strtotime('+7 days'));

if($now>15){
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
			if($deal->status=="Pending"){
				$total_pending_deals++;
				$total_pending_deals_api += $deal->original_api;
				$life_insured = $client_name;
				if($deal->status=="Pending"){
					if(!empty($deal->life_insured))
						$life_insured .= ", " . $deal->life_insured;

					$clients->submitted[] = array(
					"Client" => $life_insured,
					"Date" => NZEntryToDateTime($date_to_compare),
					"Deals" => $deals,
					"SubmissionAPI" => $deal->original_api,
									);
				}
			}
		}
		if($date_to_compare<=$end && $date_to_compare >= $initial){
			/*
				foreach($sub_deals as $deal){
					$life_insured = $client_name;
					if($deal->status=="Pending"){
						if(!empty($deal->life_insured))
							$life_insured .= ", " . $deal->life_insured;

						$clients->submitted[] = array(
						"Client" => $life_insured,
						"Date" => NZEntryToDateTime($date_to_compare),
						"Deals" => $deals,
						"SubmissionAPI" => $deal->original_api,
										);
					}
					
				}
			*/

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
	$clients->total_leads++;
	$ctr++;
}

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
?>
<div id="client_labels">
	
	<!--
	<div class="row">
	  <div class="col-sm-3"><h4>Adviser: <?php echo $adviser->name ?></h4></div>
	  <div class="col-sm-3" style=" min-height: 70px;"><h4>Email Address: <?php echo $adviser->email ?></h4></div>
	  <div class="col-sm-3" style=" min-height: 70px;"><h4>Address: <?php echo $adviser->address ?></h4></div>
	  <div class="col-sm-3" style=" min-height: 70px;"><h4>Rate per Lead: $<?php echo $adviser->leads  ?></h4></div>
	</div>
	<hr>
	<div class="row">
	  <div class="col-sm-3"><h4>Leads Assigned for The Period: <?php echo $clients->leads_assigned_for_period ?></h4></div>
	  <div class="col-sm-3" style=" min-height: 70px;"><h4>Total Submission for the Period: <?php echo $clients->leads_submitted_for_period ?></h4></div>
	  <div class="col-sm-3" style=" min-height: 70px;"><h4>Issued Leads for the Period: <?php echo $clients->leads_issued_for_period  ?></h4></div>
	  <div class="col-sm-3" style=" min-height: 70px;"><h4>Issued API for The Period: <?php echo $clients->issued_api_for_period ?></h4></div>
	</div>
	<hr>
	<div class="row">  	
	  <div class="col-sm-1"></div>
	  <div class="col-sm-3"><h4>Acummulative Leads Assigned: <?php echo $clients->total_leads ?></h4></div>
	  <div class="col-sm-3" style=" min-height: 70px;"><h4>Acummulative Leads Issued: <?php echo $clients->total_issued ?></h4></div>
	  <div class="col-sm-3" style=" min-height: 70px;"><h4>Acummulative Issued API: $<?php echo number_format($clients->total_issued_api,2) ?></h4></div>
	</div>
	<hr>
  	<div class="row">
	    <div class="col-sm-3"><h4>Total Pending Deals: <?php echo $total_pending_deals ?></h4></div>
	    <div class="col-sm-3" style=" min-height: 70px;"><h4>Total Leads Payable: <span id="total_leads_payable_header"><?php echo $total_leads_payable  ?></span></h4></div>
	    <div class="col-sm-3" style=" min-height: 70px;"><h4>Total Issued Leads Payable: <span id="total_issued_leads_payable_header"><?php echo $total_issued_payable  ?></span></h4></div>
	    <div class="col-sm-3" style=" min-height: 70px;"><h4>Total Outstanding Payable Amount: $<span id="total_outstanding_payable_amount_header">
	    	<?php echo $total_outstanding_payable_amount_header  ?>
	    	</span></h4></div>
	    
  	</div>

	-->
	<div class="row">
	  	<div class="col-sm-3">
		  	<h4>
		  		Adviser:<br>
		  	</h4>
		</div>
	  	<div class="col-sm-3" >
	  		<h4>
	  			<?php echo $adviser->name ?>	  			
	  		</h4>
	  	</div>
	  	<div class="col-sm-3" >
	  		<h4>
	  			Email Address:
	  		</h4>
	  	</div>
	  	<div class="col-sm-3" >
	  		<h4>
	  			<?php echo $adviser->email ?>	  				
  			</h4>
  		</div>
	</div>
	<div class="row">
	  	<div class="col-sm-3" >
	  		<h4>
	  			Rate per Lead:
	  		</h4>
	  	</div>
	  	<div class="col-sm-3" >
	  		<h4>
	  			$<?php echo number_format($adviser->leads,2)  ?>	  				
  			</h4>
  		</div>
	  	<div class="col-sm-3">
		  	<h4>
		  		Rate per Issued:<br>
		  	</h4>
		</div>
	  	<div class="col-sm-3" >
	  		<h4>
	  			<?php echo number_format($adviser->bonus,2) ?>	  			
	  		</h4>
	  	</div>
	</div>

	<div class="row">
	  	<div class="col-sm-3">
		  	<h4>
		  		Address:<br>
		  	</h4>
		</div>
	  	<div class="col-sm-3" >
	  		<h4>
	  			<?php echo $adviser->address ?>	  			
	  		</h4>
	  	</div>
	  	<div class="col-sm-3" >
	  		<h4>
	  			FSP Number:
	  		</h4>
	  	</div>
	  	<div class="col-sm-3" >
	  		<h4>
	  			<?php echo $adviser->fsp_num ?>	  				
  			</h4>
  		</div>
	</div>

	<div class="row">
	  	<div class="col-sm-3">
		  	<h4>
		  		Leads Assigned for The Period:<br>
		  	</h4>
		</div>
	  	<div class="col-sm-3" >
	  		<h4>
	  			<?php echo $clients->leads_assigned_for_period ?>	  			
	  		</h4>
	  	</div>
	  	<div class="col-sm-3" >
	  		<h4>
	  			Total Submission for the Period:
	  		</h4>
	  	</div>
	  	<div class="col-sm-3" >
	  		<h4>
	  			<?php echo $clients->leads_submitted_for_period ?>	  				
  			</h4>
  		</div>
	</div>
	<div class="row">
	  	<div class="col-sm-3">
		  	<h4>
		  		Issued Leads for the Period:<br>
		  	</h4>
		</div>
	  	<div class="col-sm-3" >
	  		<h4>
	  			<?php echo $clients->leads_issued_for_period ?>	  			
	  		</h4>
	  	</div>
	  	<div class="col-sm-3" >
	  		<h4>
	  			Issued API for The Period:
	  		</h4>
	  	</div>
	  	<div class="col-sm-3" >
	  		<h4>
	  			$<?php echo number_format($clients->issued_api_for_period,2) ?>	  				
  			</h4>
  		</div>
	</div>

	<div class="row">
	  	<div class="col-sm-3">
		  	<h4>
		  		Acummulative Leads Assigned:<br>
		  	</h4>
		</div>
	  	<div class="col-sm-3" >
	  		<h4>
	  			<?php echo $clients->total_leads ?>	  			
	  		</h4>
	  	</div>
	  	<div class="col-sm-3" >
	  		<h4>
	  			Acummulative Leads Issued:
	  		</h4>
	  	</div>
	  	<div class="col-sm-3" >
	  		<h4>
	  			<?php echo $clients->total_issued ?>	  				
  			</h4>
  		</div>
	</div>
	<div class="row">
	  	<div class="col-sm-3">
		  	<h4>
		  		Acummulative Issued API:<br>
		  	</h4>
		</div>
	  	<div class="col-sm-3" >
	  		<h4>
	  			$<?php echo number_format($clients->total_issued_api,2) ?>	  			
	  		</h4>
	  	</div>
	</div>

	<hr>
	<div class="row">
		<div class="col-sm-10"></div>
		<div class="col-sm-1"><button type="button"class="btn btn-primary" style="margin-top:-50px;" id="create" ><span style="font-size:15px;" class="glyphicon glyphicon-print"></span></button> <a href="edit_adviser.php?edit_id=<?php echo $adviser_id ?>" class="btn btn-warning" style="margin-top:-50px;" ><span style="font-size:15px;" class="glyphicon glyphicon-pencil"></span></a></div>
	</div>
</div>

<div class="col-xs-6" style=" min-height: 500px;">
	<div class="row">
	  	<div class="col-sm-1"></div>
	  	<div class="col-sm-5 text-center">
		  	<h4>
		  		Total Pending Deals: <br><?php echo $total_pending_deals ?><br>
		  	</h4>
		</div>
		<div class="col-sm-5 text-center">
		  	<h4>
		  		Total Pending Submission API: <br>$<?php echo number_format($total_pending_deals_api,2) ?><br>
		  	</h4>
		</div>
	  	<div class="col-sm-1"></div>
	</div>
  <h2 class="sub-header">Pending Deals</h2>
  <div class="table-responsive">
	<table id='me' data-toggle="table" id='example' class="table table-striped " cellpadding="5px" cellspacing="5px" style="width:90%;">
	<thead>
		<td>Life Insured</td>
		<td>Date of Submission</td>
		<td>Submission API</td>
	</thead>
	<tbody>
	<?php
	if(isset($clients->submitted)){
		if(count($clients->submitted)>0){

		 foreach($clients->submitted as $submission_client){
		 	$name = $submission_client["Client"];
		 	$sub_date = $submission_client["Date"];
		 	$sub_api = $submission_client["SubmissionAPI"];
			echo "
			<tr cellpadding='5px' cellspacing='5px'>
				<td>$name</td>
				<td>$sub_date</td>
				<td>$sub_api</td>
			</tr>
				";
		 }

		}
	}
	?>
	</tbody>
	</table>
  </div>
</div>

<div class="col-xs-6" style="border-left: 2px solid black; min-height: 500px;">

	<div class="row">
	  	<div class="col-sm-1" ></div>
	  	<div class="col-sm-3" >
	  		<h4>
	  			Total Leads Payable: <span id="total_leads_payable_header"><?php echo $total_leads_payable ?></span>  				
  			</h4>
  		</div>
	  	<div class="col-sm-4">
		  	<h4>
		  		Total Issued Leads Payable: <span id="total_issued_leads_payable_header"><?php echo $total_issued_payable ?></span>	  			
	  		</h4>
	  	</div>
	  	<div class="col-sm-4" >
	  		<h4>
	  			Total Outstanding Payable Amount: $<span id="total_outstanding_payable_amount_header"><?php echo $total_outstanding_payable_amount_header ?></span>	  				
  			</h4>
  		</div>
	</div>

  <h2 class="sub-header">Invoice Transaction History  <button type="button"class="btn btn-success" style="text-align: right;" id="add_invoice_transaction" ><span style="font-size:15px;" class="glyphicon glyphicon-plus"></span></button> </h2>
  <div class="table-responsive">
	<table id='me' data-toggle="table" id='example' class="table table-striped " cellpadding="5px" cellspacing="5px" style="width:90%; overflow-x: auto; white-space: nowrap;">
	<thead>
		<td>Status</td>
		<td>Date</td>
		<td>No. of Leads</td>
		<td>Amount</td>
		<td>Notes</td>
		<td colspan="2">Controls</td>
	</thead>
	<tbody id="transactions-list">
	<?php

	$query = "SELECT * FROM transactions WHERE adviser_id = $adviser_id ORDER BY date DESC, id DESC";
	//echo $query;
	//echo $query . "<hr>";
	$displayquery=mysqli_query($con,$query) or die('Could not look up user information; ' . mysqli_error($con));

	WHILE($rows = mysqli_fetch_array($displayquery)):
	$id=$rows["id"];
	$status=$rows["status"];
	$date=NZEntryToDateTime($rows["date"]);
	$number_of_leads=$rows["number_of_leads"];
	$amount=$rows["amount"];	
	$notes=$rows["notes"];

	echo "
	<tr id='transaction$id' cellpadding='5px' cellspacing='5px'>
		<td>$status</td>
		<td>$date</td>
		<td>$number_of_leads</td>
		<td>$" . number_format($amount,2) . "</td>
		<td>$notes</td>
	";
		echo "
		<td><input data-toggle='modal' data-target='#myModal' type='image' class='open-modal'  src='edit.png' value='$id'></td>
	    <td><input type='image' class='delete-transaction'  src='delete.png'  value='$id'></td>
	    ";
	?>


	 <?php 
	 echo "</tr>";	

	 endwhile;
	 ?>
	</tbody>
	</table>
  </div>
</div>









	<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header" style="background-color: #286090; ">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span style="color:white;" aria-hidden="true">×</span></button>
                    <h4 class="modal-title" id="myModalLabel" style="color:white;">Transaction Editor</h4>
                </div>
                <div class="modal-body">
                    <form id="frmTransaction" name="frmTransaction" class="form-horizontal" novalidate="">
                        <div class="form-group error">
                            <label for="inputTask" class="col-sm-3 control-label">Status</label>
                            <div class="col-sm-9">
                            	<select class="form-control has-error" id="status" name="status" required>
                            		<option>Paid Assigned Leads</option>
                            		<option>Paid Issued Leads</option>
                            		<option>Manual Billed Assigned Leads</option>
                            		<option>Manual Billed Issued Leads</option>
                            		<option>Waived Leads</option>
                            		<option>Cancelled Leads</option>
                            		<option>Amendment</option>
                            	</select>
                            </div>
                            <input type="hidden" name="method" id="method" value ="">
                            <input type="hidden" name="id" id="id" value ="">
                            <input type="hidden" name="adviser_id" id="adviser_id" value ="<?php echo $adviser_id; ?>">
                            <input type="hidden" name="rate_per_lead" id="rate_per_lead" value ="<?php echo $adviser->leads; ?>">
                            <input type="hidden" name="rate_per_issue" id="rate_per_issue" value ="<?php echo $adviser->bonus; ?>">
                        </div>
                      <div class="form-group error">
                            <label for="inputTask" class="col-sm-3 control-label">Date</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control datepicker" id="date" name="date" value="<?php echo date('d/m/Y') ?>" required>
                            </div>
                        </div>
                        <div class="form-group error">
                            <label for="inputTask" class="col-sm-3 control-label">Number of Leads</label>
                            <div class="col-sm-9">
                                <input type="number" class="form-control has-error" id="number_of_leads" name="number_of_leads" placeholder="Number of Leads" value="" required>
                                <label id="password_label" for="number_of_leads" style="color:red;"></label>
                            </div>
                        </div>
                        <div class="form-group error">
                            <label for="inputTask" class="col-sm-3 control-label">Amount</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control has-error" id="amount" name="amount" placeholder="Amount" value="" readonly>
                                <label id="amount" for="amount" style="color:red;"></label>
                            </div>
                        </div>
                        <div class="form-group error">
                            <label for="inputTask" class="col-sm-3 control-label">Notes</label>
                            <div class="col-sm-9">
                                <textarea type="number" class="form-control has-error" id="notes" name="notes" placeholder="Notes" value=""></textarea>
                                <label id="password_label" for="number_of_leads" style="color:red;"></label>
                            </div>
                        </div>
                        <input type="hidden" id="formtype" name="formtype" value="0">
                        <input type="hidden" id="user_id" name="user_id" value="0">
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="btn-save" value="add">Save changes</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="confirmIModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                    <h4 class="modal-title" id="myModalLabel">Invoice Preview</h4>
                </div>
                <div id="modal-body" class="modal-body">
                    <form id="frmDelUser" name="frmDelUser" class="form-horizontal" novalidate="">
                        <div class="form-group error">
                        	<label for="inputTask" class="col-sm-12 control-label">Are you sure you want to delete this Transaction?
                          	</label>

                        </div>
                    </form>
                </div>
                <div class="modal-footer">
		        	<button type="button" class="btn btn-info" id='save_pdf'>Save</button>
		          	<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="confirmModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                    <h4 class="modal-title" id="myModalLabel">Confirm Deletion</h4>
                </div>
                <div class="modal-body">
                    <form id="frmDelUser" name="frmDelUser" class="form-horizontal" novalidate="">
                        <div class="form-group error">
                        	<label for="inputTask" class="col-sm-12 control-label">Are you sure you want to delete this Transaction?
                          	</label>

                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" id="btn-delete-confirm" value="Yes">Confirm Delete</button>
                    <button type="button" class="btn btn-primary" id="btn-delete-cancel" value="No">Cancel</button>
                    <input name="_method" id="_method" type="hidden" value="DELETE" />
                    <input name="_status" id="_status" type="hidden" value="" />
                    <input type="hidden" id="delete-transaction" value="0">
                </div>
            </div>
        </div>
    </div>
<footer style="min-height: 200px;">
	  &nbsp;<br>
</footer>
<?php

//GET INVOICE NUM
$invoice_num='EIL';

$count_query = "SELECT id FROM invoices ORDER BY id DESC";
$searchsum=mysqli_query($con,$count_query) or die('Could not look up user information; ' . mysqli_error($con));

$rows = mysqli_fetch_array($searchsum);
$rows_count=isset($rows['id'])?$rows['id']:1;
switch ($rows_count) {
	case ($rows_count<10):
		$invoice_num.='00'.$rows_count;

		break;
	case ($rows_count<100 && $rows_count>=10):
		$invoice_num.='0'.$rows_count;

		break;

	case ($rows_count>=100):
		$invoice_num.=$rows_count;

		break;
	
	default:

		break;
}


?>
<script>
$(document).ready(function(){

	function showAmount(){
		var rate_lead = $("#rate_per_lead").val();
		var rate_issue = $("#rate_per_issue").val();
		var leads = $("#number_of_leads").val();
		var selected_status = $("#status").val();
		var amount = 0;
		if(leads!=null){
			if(selected_status=="Paid Issued Leads"){
				amount = rate_issue * leads * -1;
			}
			else if(selected_status=="Manual Billed Assigned Leads"){
				amount = rate_lead * leads;
			}
			else if(selected_status=="Manual Billed Issued Leads"){
				amount = rate_issue * leads;
			}
			else{
				amount = rate_lead * leads * -1;
			}
			$("#amount").val(amount);
		}
	}

	$( ".datepicker" ).datepicker({ dateFormat: 'dd/mm/yy' });
	
	$("#number_of_leads").on("keyup", function(){
		showAmount();
	});

	$("#status").on("change", function(){
		showAmount();
	});

	$("#add_invoice_transaction").on("click", function(){
		$('#method').val('POST');
       	$("#status").show();
		$('#myModal').modal('show');
	});

	$(document).on("click", ".open-modal", function(e){
        e.preventDefault();
        var mat_id = $(this).val();
       	$("#status").hide();
        $.get('crud/transactions-crud.php/?id=' + mat_id, function (data) {            
            console.log(data);
            $("#status").val(data.status);
            $("#number_of_leads").val(data.number_of_leads);
            $("#amount").val(data.amount);
            $("#date").val(data.date);
            $("#notes").val(data.notes);
            $('#id').val(data.id);
            $('#method').val("PUT");
			$('#myModal').modal('show');
        });
    });

	$(document).on('click','#btn-save',function(e) {
        var data = $("#frmTransaction").serialize();
        method = $("#method").val();
        console.log(data);
        $.ajax({
             data: data,
             type: "post",
             url: "crud/transactions-crud.php",
             success: function(data){
                var total_leads_payable = $("#total_leads_payable_header").html();
                var total_issued_leads_payable = $("#total_issued_leads_payable_header").html();
                var total_outstanding_payable_amount = $("#total_outstanding_payable_amount_header").html();

                if(data.status=="Paid Issued Leads"){
                	total_issued_leads_payable -= data.number_of_leads;
                	$("#total_issued_leads_payable_header").html(total_issued_leads_payable);
                }
                else if(data.status=="Manual Billed Assigned Leads"){
                	total_leads_payable += data.number_of_leads;
                	$("#total_leads_payable_header").html(total_leads_payable);
                } 
                else if(data.status=="Manual Billed Issued Leads"){
                	total_issued_leads_payable -= data.number_of_leads;
                	$("#total_issued_leads_payable_header").html(total_issued_leads_payable);
                }
                else{
                	total_leads_payable-= data.number_of_leads;
                	$("#total_leads_payable_header").html(total_leads_payable);
                }

                total_outstanding_payable_amount = parseFloat(data.amount) + parseFloat(total_outstanding_payable_amount);
                $("#total_outstanding_payable_amount_header").html(total_outstanding_payable_amount);

                var transaction = "<tr id='transaction"+data.id+"' cellpadding='5px' cellspacing='5px'><td>"+data.status+"</td><td>"+data.date+"</td>\
                <td>"+data.number_of_leads+"</td><td>"+data.amount+"</td><td>"+data.notes+"</td>\
                <td><input data-toggle='modal' data-target='#myModal' type='image' class='open-modal'  src='edit.png' value='"+data.id+"'></td>\
                <td><input type='image' class='delete-transaction'  src='delete.png' value='"+ data.id +"'></td></tr>";

                if (method == "POST"){ //if user added a new record
                    $('#transactions-list').prepend(transaction);
                }else{ //if user updated an existing record
                    $("#transactions" + data.id).replaceWith(transaction);
                }
                console.log(data);
                $("#report_text").html("User Credentials saved.");
                $('#myModal').modal('hide');
                $('#frmTransaction').trigger("reset");
             },
             error: function(data){
                $("#report_text").val(data.reason);
                console.log(data);
             }
        });
        
    });

	$('#transactions-list').on("click", ".delete-transaction", function(){
        var mat_id = $(this).val();
        $('#confirmModal').modal('show');
        $('#delete-transaction').val(mat_id);
    });

    $('#btn-delete-cancel').on("click", function(){
        $('#confirmModal').modal('hide');
    });

    $('#btn-delete-confirm').on("click", function(){
        var mat_id = $('#delete-transaction').val();
        var data = {
            method: $('#_method').val(),
            id: mat_id,
        }
        console.log(data);
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        })
        $.ajax({
            data: data,
            type: "post",
            url: "crud/transactions-crud.php",
            success: function (data) {
                console.log(data);
                $("#transaction" + mat_id).remove();
                $('#confirmModal').modal('hide');
            },
            error: function (data) {
                console.log(data);
                console.log('Error:', data);
            }
        });
       
    });



$('#create').on('click',function(e){
e.preventDefault();

var adv_name=  "<?php echo $adviser->name ?>";
var adviser_id= "<?php echo $adviser_id ?>";
var date_from=  "<?php echo NZEntryToDateTime($initial)?>";
var invoice_date= "<?php echo NZEntryToDateTime($now)?>";
var desc= '["charged","issued"]';

var until= "<?php echo NZEntryToDateTime($end)?>";
var due_date= "<?php echo $due ?>";
var invoice_num= "<?php echo $invoice_num ?>";
var other_value= 0;
console.log(date_from + ":" + until);
	$.ajax({
		dataType:'json',
		type:'POST',
		data:{adv_name:adv_name,
			adviser_id,adviser_id,date_from:date_from, invoice_date:invoice_date, desc:desc, until:until, due_date:due_date, invoice_num:invoice_num,other_value:other_value},
		url:"output.php",
		success:function(e){
			console.log(desc);
			var mydata=JSON.stringify(e);
			var link=e['link'];
			var htm= '<iframe src="'+link+'" style="width: 100%;height: 75%;"></iframe>';
			$('#confirmIModal').modal('show');
			$('#modal-body').html(htm);
			$('#save_pdf').unbind( "click");
			$('#save_pdf').on('click',function(){
				$.ajax({
					//dataType:'JSON',
					data:{mydata:mydata},
					type:'POST',
					url:"save_invoice.php",
				beforeSend:function(){

				},
				success:function(x){
					console.log(x);
					$.confirm({
					    title: 'Success!',
					    content: 'You successfully created an invoice.',
					    buttons: {
					        Ok: function () {
					        	console.log(x);
					   			window.location='adviser_profile.php?id=<?php echo $adviser_id ?>';
						        },	 
					    	}
						});
				}
				});
			});
		},
		error: function (x) {
			x=JSON.stringify(x);
		        console.log("Data:" + x);
		      }
	});

});

});
</script>

</body>

</html>

<?php
}


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