 <?php
	session_start();
	if (!isset($_SESSION["myusername"])) {
		session_destroy();
		header("Refresh:0; url=index.php");
	} else {
		?>
 	<html>

 	<head>
 		<!--nav bar-->
 		<!--nav bar end-->
 		<meta name="viewport" content="width=device-width, initial-scale=1">
 		<link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">
 		<link rel="stylesheet" href="styles.css">
 		<link id="favicon" rel="icon" href="Logo_ImageOnly.png" type="image/png" sizes="16x16">
 		<title>INDET</title>

 		<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css" integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">
 		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
 		<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js" type="text/javascript"></script>
 		<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.0.0/jquery.js"></script>
 		<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.7/css/select2.min.css" rel="stylesheet" />
 		<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.7/js/select2.min.js"></script>
 		<?php include "partials/nav_bar.html"; ?>

 		<script>
 			$(function() {

 				$('.checkme').on('click', function() {
 					var id = $(this).attr('data-id');
 					var com = $(this).attr('data-com');
 					var me = $(this);
 				});

 				$('#me').dataTable();





 			});
 		</script>

 	</head>

 	<body>
 		<!--header-->
 		<div align="center">


 			<!--header end-->

 			<!--nav bar-->


 			<!--nav bar end-->


 			<!--label-->

 			<div class="jumbotron">
 				<h2 class="slide">Submission Client Summary</h2>
 			</div>
 			<!--label end-->



 			<?php


					require "database.php";

					include_once("libs/api/classes/general.class.php");
					include_once("libs/api/controllers/Adviser.controller.php");
					include_once("libs/api/controllers/LeadGenerator.controller.php");
					include_once("libs/api/controllers/Deal.controller.php");
					include_once("libs/api/controllers/Client.controller.php");

					$leadGeneratorController = new LeadGeneratorController();
					$adviserController = new AdviserController();
					$clientController = new ClientController();
					$dealController = new DealController();
					$general = new General();


					function convertNum($x)
					{

						return number_format($x, 2, '.', ',');
					}


					$con = mysqli_connect($host, $username, $password, $db) or die("could not connect to sql");
					if (!$con) {
						echo "<div>";
						echo "Failed to connect to MySQL: " . mysqli_connect_error();
						echo "</div>";
					}

					$query = "SELECT s.id as id,s.deals as deals, s.timestamp, c.id as client_id, c.name as client_name, l.name as leadgen_name, a.name as adviser_name, s.deals FROM submission_clients s LEFT JOIN clients_tbl c ON s.client_id = c.id LEFT JOIN leadgen_tbl l ON c.leadgen = l.id LEFT JOIN adviser_tbl a ON c.assigned_to = a.id WHERE s.client_id NOT IN (Select name from issued_clients_tbl) order by s.timestamp desc";
					$displayquery = mysqli_query($con, $query) or die('Could not look up user information; ' . mysqli_error($con));
					?>

 			<div class="margined table-responsive">
 				<div class="row">
 					<div class="col-sm-9 text-center"></div>
 					<div class="col-sm-3 text-center"><button id="btn-add" name="btn-add" data-toggle="modal" data-target="#myModal" class="pull-right btn btn-primary btn-m"><i class="fa fa-plus"></i> Add Submission</button></div>
 				</div>
 				<br>
 				<div class="row">
 					<table id='me' data-toggle="table" class="table table-striped " cellpadding="5px" cellspacing="5px" width='95%'>

 						<thead>

 							<td>Client Name</td>
 							<td>Policy Number</td>
 							<td>Insurer</td>
 							<td>Adviser</td>
 							<td>Date Submitted</td>
 							<td>Total API</td>
 							<td>Deals</td>

 							<td></td>
 							<td></td>
 							<td></td>



 						</thead>
 						<tbody>
 							<?php

									while ($rows = mysqli_fetch_array($displayquery)) :
										extract($rows);
										$timestamp_order = $timestamp;
										$timestamp = date('d/m/Y', strtotime($timestamp));
										$deals = json_decode($deals);
										$deals_count = 0;
										$deals_count = count($deals);

										$total_api = 0;

										$unique_client_names = [];
										$unique_policy_numbers = [];
										$unique_insurers = [];
										$unique_client_names[] = $client_name;

										foreach ($deals as $deal) {
											debuggingLog("Deal: ", $deal);

											//push into array if not in there
											if (!in_array($deal->company, $unique_insurers)) {
												if ($deal->company != "Others")
													$unique_insurers[] = $deal->company;
												else
													$unique_insurers[] = $deal->specific_company;
											}

											if (!in_array($deal->life_insured, $unique_client_names)) {
												if (!empty($deal->life_insured))
													$unique_client_names[] = $deal->life_insured;
											}

											if (!in_array($deal->policy_number, $unique_policy_numbers)) {
												$unique_policy_numbers[] = $deal->policy_number;
											}

											if ($deal->status == "Pending")
												$total_api += $deal->original_api;
										}

										/*$entrydate=$rows["entrydate"];
$startingdate=$rows["startingdate"];
$entrydate=substr($entrydate,0,4)."/".substr($entrydate,4,2)."/".substr($entrydate,6,2);

$startingdate=substr($startingdate,0,4)."/".substr($startingdate,4,2)."/".substr($startingdate,6,2);
$convertdate=substr($rows["entrydate"],6,2)."/".substr($rows["entrydate"],4,2)."/".substr($rows["entrydate"],0,4);

$convertstartingdate=substr($rows["startingdate"],6,2)."/".substr($rows["startingdate"],4,2)."/".substr($rows["startingdate"],0,4);
*/

										echo "
		<tr id='client$client_id' cellpadding='5px' cellspacing='5px'>

			<td>" . implode(', ', $unique_client_names) . "</td>
			<td>" . implode(', ', $unique_policy_numbers) . "</td>
			<td>" . implode(', ', $unique_insurers) . "</td>

			<td>$adviser_name</td>
			<td data-order='$timestamp_order'>$timestamp</td>
			<td data-order='" . number_format($total_api, 2) . "'>$" . number_format($total_api, 2)  . "</td>

			<td>$deals_count</td>	";
										/*<td data-order=".$entrydate.">".$convertdate."</td>
    <td data-order=".$startingdate.">".$convertstartingdate."</td>
	*/

										?>


 								<td><a class="btn-view" id='btn-view-<?php echo "$client_id" ?>' data-toggle="tooltip" title="View Submission Profile" data-id='<?php echo "$client_id" ?>'><span class="btn btn-primary glyphicon glyphicon-search"></span></a>
 								</td>
 								<td><a class="btn-edit" id='btn-edit-<?php echo "$client_id" ?>' data-toggle="tooltip" title="Edit Submission Profile" data-id='<?php echo "$client_id" ?>'><span class="btn btn-warning glyphicon glyphicon-pencil"></span></a></td>
 								<td><a class="btn-delete" data-toggle="tooltip" title="Delete Submission Profile" data-id='<?php echo "$client_id" ?>' data-name='<?php echo "$client_name" ?>'><span class="btn btn-danger glyphicon glyphicon-trash"></span></a></td>

 								<!--
<td><a href="editclient.php<?php echo "?edit_id=$id" ?>"><img src="edit.png"</a>
	 </td>
-->
 								<!--
<td><a class="a" href="edit_adviser.php<?php echo "?edit_id=$id" ?>"><img src="edit.png"></a>
	 </td>
-->
 							<?php
										echo "</tr>";

									endwhile;




									?>
 						</tbody>
 					</table>
 				</div>
 			</div>
 		</div>

 		<style>
 			.datepicker_dynamic {
 				z-index: 2000 !important;
 			}

 			.full-screen {
 				width: 90%;
 				height: 90%;
 				margin: 0;
 				top: 5%;
 				left: 5%;
 			}

 			.nav-tabs>li {
 				float: none;
 				display: inline-block;
 				zoom: 1;
 			}

 			.nav-tabs {
 				text-align: center;
 			}

 			.nav-tabs li a {
 				color: #337ab7 !important;
 				border-bottom: 1px solid #ddd;
 			}

 			.nav-tabs li.active a {
 				color: black !important;
 				border-bottom: none;
 			}
 		</style>

 		<div class=" modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
 			<div id="myModalDialog" class="modal-dialog modal-lg full-screen">
 				<div class="modal-content">
 					<div class="modal-header" style="background-color: #286090; ">
 						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span style="color:white;" aria-hidden="true">×</span></button>
 						<h4 class="modal-title" id="myModalLabel" style="color:white;">Client Editor</h4>
 					</div>
 					<div class="modal-body">
 						<div class='row' style="padding-top: 30px;">
 							<div class='col-sm-4'></div>
 							<div class='col-sm-4 text-center'>
 								<h3>Existing Client</h3>
 							</div>
 						</div>
 						<div class='row'>
 							<div class='col-sm-4'></div>
 							<div class='col-sm-4 text-center'>
 								<select id="clients_list" name="clients_list" class="form-control" style="width:100% !important;">
 									<option value="" disabled selected>Select Client</option>
 									<?php
											$clients = $clientController->getAllClientsWithoutSubmissions();
											while ($rows = $clients->fetch_assoc()) {
												$id = $rows["id"];
												$name = $rows["name"];
												echo "<option value='" . $id . "'>" . $name . "</option>";
											}
											?>
 								</select>
 								<input type="hidden" id="client_id">
 							</div>
 						</div>

 						<div class="row" id="nav_tabs">
 							<div class="col">
 								<ul class="nav nav-tabs">
 									<li class="active" id="client_data_nav"><a data-toggle="tab" href="#client_data">Client Data</a></li>
 									<li id="deal_data_nav"><a data-toggle="tab" href="#deals_data">Deals Data</a></li>
 								</ul>
 							</div>
 						</div>

 						<div class="row" id="data_tabs" style="display:none;">
 							<div class="col-sm-12">
 								<div class="tab-content">
 									<div id="client_data" class="tab-pane fade in active">
 										<form id="frmClient" name="frmClient" class="form-horizontal" novalidate="">
 											<div class="form-group error">
 												<label for="inputTask" class="col-sm-2 control-label">Name</label>
 												<div class="col-sm-4">
 													<input type="text" class="form-control has-error" id="name" name="name" placeholder="Name" value="" required>
 												</div>
 												<label for="inputTask" class="col-sm-2 control-label">Email</label>
 												<div class="col-sm-4">
 													<input type="text" class="form-control has-error" id="email" name="email" placeholder="Email" value="" required>
 												</div>
 											</div>

 											<div class="form-group error">
 												<label for="inputTask" class="col-sm-2 control-label">Date Generated</label>
 												<div class="col-sm-4">
 													<input type="text" class="form-control has-error datepicker" id="date_submitted" name="date_submitted" placeholder="Date Generated" value="" required>
 												</div>
 												<label for="inputTask" class="col-sm-2 control-label">Appt Date</label>
 												<div class="col-sm-4">
 													<input type="text" class="form-control has-error datepicker" id="appt_date" name="appt_date" placeholder="Appointment Date" value="" required>
 												</div>
 											</div>

 											<div class="form-group error">
 												<label for="inputTask" class="col-sm-2 control-label">Assigned Date</label>
 												<div class="col-sm-4">
 													<input type="text" class="form-control has-error datepicker" id="assigned_date" name="assigned_date" placeholder="Assigned Date" value="" required>
 												</div>
 												<label for="inputTask" class="col-sm-2 control-label">Phone</label>
 												<div class="col-sm-4">
 													<input type="text" class="form-control has-error" id="phone_num" name="phone_num" placeholder="Phone" value="" required>
 												</div>
 											</div>

 											<div class="form-group error">
 												<label for="inputTask" class="col-sm-2 control-label">Address</label>
 												<div class="col-sm-10">
 													<textarea class="form-control has-error" id="address" name="address" placeholder="Address" required></textarea>
 												</div>
 											</div>

 											<div class="form-group error">
 												<label for="inputTask" class="col-sm-2 control-label">City</label>
 												<div class="col-sm-4">
 													<input type="text" class="form-control has-error" id="city" name="city" placeholder="City" value="">
 												</div>
 												<label for="inputTask" class="col-sm-2 control-label">Zipcode</label>
 												<div class="col-sm-4">
 													<input type="text" class="form-control has-error" id="zipcode" name="zipcode" placeholder="Zipcode" value="">
 												</div>
 											</div>

 											<div class="form-group error">
 												<label for="inputTask" class="col-sm-2 control-label">Source</label>
 												<div class="col-sm-4">
 													<select name="lead_by" id="lead_by" class="form-control" required />
 													<option value="" disabled hidden selected>Select Source</option>
 													<option>Self-Generated</option>
 													<option>Telemarketer</option>
 													<option>Face-to-Face Marketer</option>
 													</select>
 													<select name="leadgen_telemarketer" id="leadgen_telemarketer" class="form-control leadgen" style="display:none;" />
 													<option value="0" disabled hidden selected>Select Telemarketer</option>
 													<?php
															$tele_query = "Select * from leadgen_tbl where type='Telemarketer' ORDER BY name";
															$tele_result = mysqli_query($con, $tele_query);
															while ($tele_row = mysqli_fetch_assoc($tele_result)) {
																echo "<option value='" . $tele_row['id'] . "'>" . $tele_row['name'] . "</option>";
															}
															?>
 													</select>
 													<select name="leadgen_bdm" id="leadgen_bdm" class="form-control leadgen" style="display:none;" />
 													<option value="0" disabled hidden selected>Select Face-to-Face Marketer</option>
 													<?php
															$bdm_query = "Select * from leadgen_tbl where type='Face-to-Face Marketer' ORDER BY name";
															$bdm_result = mysqli_query($con, $bdm_query);
															while ($bdm_row = mysqli_fetch_assoc($bdm_result)) {
																echo "<option value='" . $bdm_row['id'] . "'>" . $bdm_row['name'] . "</option>";
															}
															?>
 													</select>
 													<input type="hidden" name="leadgen" id="leadgen">
 												</div>

 												<label for="inputTask" class="col-sm-2 control-label">Assigned To</label>
 												<div class="col-sm-4">
 													<select name="assigned_to" id="assigned_to" class="form-control" />
 													<option value="0" disabled hidden selected>Select Adviser</option>
 													<?php
															$adv_query = "Select * from adviser_tbl ORDER BY name";
															$adv_result = mysqli_query($con, $adv_query);
															while ($adv_row = mysqli_fetch_assoc($adv_result)) {
																echo "<option value='" . $adv_row['id'] . "'>" . $adv_row['name'] . "</option>";
															}
															?>
 													</select>
 												</div>
 											</div>

 											<div class="form-group error">
 												<label for="inputTask" class="col-sm-2 control-label">Notes</label>
 												<div class="col-sm-10">
 													<textarea class="form-control has-error" id="notes" name="notes" placeholder="Notes" required></textarea>
 												</div>
 											</div>

 											<div class="form-group error" id="status_div" style="display:none;">
 												<label for="inputTask" class="col-sm-2 control-label">Status</label>
 												<div class="col-sm-4">
 													<select name="status" id="status" class="form-control" required />
 													<option>Seen</option>
 													<option>Agreement</option>
 													<option>Cancelled</option>
 													</select>
 												</div>

 												<label for="inputTask" class="col-sm-1 control-label">Date</label>
 												<div class="col-sm-4">
 													<input type="text" class="form-control has-error datepicker" id="date_status_updated" name="date_status_updated" placeholder="Date" value="" required>
 												</div>
 											</div>

 											<input type="hidden" id="formtype" name="formtype" value="0">
 										</form>
 									</div>

 									<div id="deals_data" class="tab-pane fade in">
 										<br>
 										<div class="row">
 											<div class='col-sm-5'>
 											</div>
 											<div class='col-sm-2' id="add_deal_btn_div">
 												<button type="button" class="btn btn-info center form-control" id="add_deal_btn" style="width: 100%; "><i class="glyphicon glyphicon-plus"></i> Add Deal</button>
 											</div>
 										</div>
 										<form id="frmDeals" name="frmDeals" class="form-horizontal" novalidate="">

 											<input type='hidden' name='deals_count' id='deals_count' value="1">

 											<div id="deals_div">

 											</div>
 										</form>
 									</div>
 								</div>
 							</div>
 						</div>

 					</div>
 					<div class="modal-footer">
 						<div id="buttons_div">
 							<button type="button" class="btn btn-primary" id="btn-save-client_data"><i id="save_client_spinner" class="fas fa-spinner fa-spin" style="display:none;"></i>Save Client Data</button>
 							<button type="button" class="btn btn-primary" id="btn-save-deal_data" style="display:none;"><i id="save_deal_spinner" class="fas fa-spinner fa-spin" style="display:none;"></i>Save Submission Profile</button>
 						</div>
 					</div>
 				</div>
 			</div>
 		</div>
 		<!--
	End of Editor
	-->

 		<div class=" modal fade" id="clientDealsModal" tabindex="-1" role="dialog" aria-labelledby="clientDealsModalLabel" aria-hidden="true">
 			<div id="clientDealsModalDialog" class="modal-dialog modal-lg full-screen">
 				<div class="modal-content">
 					<div class="modal-header" style="background-color: #286090; ">
 						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span style="color:white;" aria-hidden="true">×</span></button>
 						<h4 class="modal-title" id="clientDealsModalLabel" style="color:white;">Client Deals</h4>
 					</div>
 					<div class="modal-body">
 						<div class="row">
 							<div class="col text-center">
 								<h3 id="total_api_header">Total API: $23000</h3>
 							</div>
 						</div>
 						<div class="row">
 							<div class="col-sm-12">
 								<table id='client_deals_table' data-toggle="table" class="table table-striped " cellpadding="5px" cellspacing="5px" width='95%'>
 									<thead>
 										<td>Client Name, Life Insured</td>
 										<td>Submission Date</td>
 										<td>Insurer</td>
 										<td>Policy Number</td>
 										<td>Original API</td>
 										<td>Status</td>
 									</thead>
 									<tbody id="client_deals_table_body">
 										<tr>
 											<td>Client Name, Life Insured</td>
 											<td data-order="0">01/01/1990</td>
 											<td>Insurer</td>
 											<td>Policy Number</td>
 											<td data-order="0">$20,000.00</td>
 											<td>Status</td>
 										</tr>
 									</tbody>
 								</table>
 							</div>
 						</div>
 					</div>
 					<div class="modal-footer">
 						<div id="buttons_div">
 							<button type="button" class="btn btn-primary" id="btn-issue_client" data-id="0">Issue Client</button>
 						</div>
 					</div>
 				</div>
 			</div>
 		</div>

 		<?php
				if (isset($_GET["edit"])) {
					?>

 			<script>
 				<?php echo "let edit_id = " . $_GET["edit"] . ";
							let name = '" . $_GET["name"] . "'
				 " ?>
 				$(function() {
 					let tbl = $('#me').DataTable();
 					tbl.search(name).draw();
 					setTimeout(function() {
 						$("#btn-edit-" + edit_id).trigger("click");
 						setTimeout(function() {
 							$("#data_tabs").show();
 							$("#nav_tabs").show();
 							$("#buttons_div").show();
 						}, 400);
 					}, 50);
 				});
 			</script>
 		<?php
				}
				if (isset($_GET["view"])) {

					?>

 			<script>
 				<?php
							echo "let edit_id = " . $_GET["view"] . ";
				 let name = '" . $_GET["name"] . "'";
							?>

 				$(function() {
 					let tbl = $('#me').DataTable();
 					tbl.search(name).draw();
 					setTimeout(function() {
 						$("#btn-view-" + edit_id).trigger("click");
 					}, 50);
 				});
 			</script>

 		<?php
				}
				?>


 		<script src="js/submissions-crud.js"></script>
 	</body>

 	</html>

 <?php



	}
	function debuggingLog($header = "Logged Data", $variable)
	{
		//SET TO TRUE WHEN DEBUGGING SET TO FALSE WHEN NOT
		$isDebuggerActive = false;

		if (!$isDebuggerActive)
			return;

		$op = "<br>";
		$op .=  $header;
		echo $op . "<hr>" . "<pre>";
		var_dump($variable);
		echo "</pre>" . "<hr>";
	}


	function DateTimeToNZEntry($date_submitted)
	{
		return substr($date_submitted, 6, 4) . substr($date_submitted, 3, 2) . substr($date_submitted, 0, 2);
	}

	function NZEntryToDateTime($NZEntry)
	{
		return substr($NZEntry, 6, 2) . "/" . substr($NZEntry, 4, 2) . "/" . substr($NZEntry, 0, 4);
	}
	?>