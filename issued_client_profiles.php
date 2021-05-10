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
 		<?php include "partials/nav_bar.html"; ?>
 		<!--nav bar end-->
 		<meta name="viewport" content="width=device-width, initial-scale=1">
 		<link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">
 		<link rel="stylesheet" href="styles.css">
 		<link id="favicon" rel="icon" href="Logo_ImageOnly.png" type="image/png" sizes="16x16">
 		<title>INDET</title>
 	</head>
 	<!--header-->

 	<body>
 		<div align="center">


 			<!--header end-->

 			<!--nav bar-->


 			<!--nav bar end-->


 			<!--label-->

 			<div class="jumbotron">
 				<h2 class="slide">Issued Client Summary</h2>
 			</div>
 			<!--label end-->

 			<!--modal-->



 			<?php


				require "database.php";
				include_once("libs/api/classes/general.class.php");
				include_once("libs/api/controllers/Deal.controller.php");
				include_once("libs/api/controllers/Client.controller.php");


				function convertNum($x)
				{

					return number_format($x, 2, '.', ',');
				}


				$dealController = new DealController();
				$clientController = new ClientController();
				$generalController = new General();
				if (!$con) {
					echo "<div>";
					echo "Failed to connect to MySQL: " . mysqli_connect_error();
					echo "</div>";
				}

				$issued_clients = $clientController->getAllIssuedClientProfiles();

				?>

 			<div class="margined table-responsive">

 				<div class="row">
 					<div class="col-sm-9 text-center"></div>
 					<div class="col-sm-3 text-center"><button id="btn-add" name="btn-add" data-toggle="modal" data-target="#myModal" class="pull-right btn btn-primary btn-m"><i class="fa fa-plus"></i> Add Issued Policy</button></div>
 				</div>
 				<br>

 				<div class="row">
 					<table id='issued_clients_table' data-toggle="table" class="table table-striped " cellpadding="5px" cellspacing="5px" width='95%'>

 						<thead>

 							<td>Client Name</td>
 							<td>Status</td>
 							<!--td>Appt Date</td>
	<td>Phone Number</td>
	<td>Address</td>
	<td>Notes</td>
	<td>Assigned to</td>
	<td>Assigned Date</td>
	<td>Type of Lead</td-->
 							<td>Issued Premium</td>
 							<td>Date Issued</td>
 							<td>Adviser</td>
 							<td>Policy Number</td>
 							<td></td>
 							<td></td>
 							<td></td>
 							<td></td>
 							<td></td>




 						</thead>
 						<tbody>
 							<?php

								while ($rows = $issued_clients->fetch_assoc()) :
									if ($rows["name"] == null)
										continue;
									$client_id = $rows["client_id"];
									$id = $rows["id"];
									$name = $rows["name"];
									$x = $rows["x"]; //advisername
									$y = $rows["y"]; //leadgenname
									$lead_by = $rows["lead_by"];
									$appt_date = $rows["appt_date"];
									$appt_time = $rows["appt_time"];
									$address = $rows["address"];
									$leadgen = $rows["leadgen"];
									$assigned_to = $rows["assigned_to"];
									$assigned_date = $rows["assigned_date"];
									$type_of_lead = $rows["type_of_lead"];
									$issued = $rows["issued"];
									$date_issued = $rows["date_issued"];
									$date_issued_order = $date_issued;
									$notes = $rows["notes"];

									$date_issued = date('d/m/Y', strtotime($date_issued));

									$deals_data = json_decode($rows["deals_data"]);
									$policy_numbers = array();
									$statuses = array();
									$unique_client_names = array();
									$unique_client_names[] = $name;

									foreach ($deals_data as $deal) {
										$statuses[] = $deal->status;

										if (!in_array($deal->life_insured, $unique_client_names)) {
											if (!empty($deal->life_insured))
												$unique_client_names[] = $deal->life_insured;
										}

										if (!in_array($deal->policy_number, $policy_numbers)) {
											$policy_numbers[] = $deal->policy_number;
										}
									}

									$policy_number = implode(", ", $policy_numbers);
									$unique_client_names = implode(", ", $unique_client_names);
									$statuses = implode(", ", $statuses);

									$lg = $y;
									if ($lead_by == "Self-Generated") {
										$lg = $x;
									}

									echo "
<tr id='issued_client_$client_id' cellpadding='5px' cellspacing='5px'>

<td>$unique_client_names</td>
<td>$statuses</td>
		<td data-order='" . $issued . "'>$" . number_format((float) $issued, 2) . "</td>
<td data-order='$date_issued_order'>$date_issued</td>

		<td>$x</td>
		<td>$policy_number</td>
	";
									/*<td data-order=".$entrydate.">".$convertdate."</td>
	<td data-order=".$startingdate.">".$convertstartingdate."</td>
	 href="view_issued_client_profile<?php echo "?id=$id" ?>"
	*/

								?>


 								<td><a class="btn-pdf" id='btn-pdf-<?php echo "$client_id" ?>' ata-toggle="tooltip" title="View Issued Client Profile and Deals Data PDF" data-adviser_id='<?php echo $assigned_to ?>' data-id='<?php echo "$client_id" ?>'><i id="view_pdf_spinner_<?php echo $client_id ?>" class="fas fa-spinner fa-spin" style="display:none;"></i> <span class="btn btn-primary glyphicon glyphicon-file" id="view_pdf_icon_<?php echo $client_id ?>"></span></a></td>
 								<td><a class="btn-view" id='btn-view-<?php echo "$client_id" ?>' ata-toggle="tooltip" title="View Issued Client Profile" data-id='<?php echo "$client_id" ?>'><span class="btn btn-primary glyphicon glyphicon-search"></span></a></td>
 								<td><a class="btn-history" data-toggle="tooltip" title="View Follow-Up History" data-id='<?php echo "$client_id" ?>'><span class="btn btn-info glyphicon glyphicon-book"></span></i></a></td>
 								<td><a class="btn-edit" id='btn-edit-<?php echo "$client_id" ?>' data-toggle="tooltip" title="Edit Issued Client Profile" data-id='<?php echo "$client_id" ?>'><span class="btn btn-warning glyphicon glyphicon-pencil"></span></i></a></td>
 								<td><a class="unissue_client" data-toggle="tooltip" title="Unissue Client" data-id="<?php echo $client_id ?>"><span class="btn btn-danger glyphicon glyphicon-refresh"></span></td>

 							<?php
									echo "</tr>";

								endwhile;




								?>
 						</tbody>
 					</table>
 				</div>
 			</div>
 		</div>

 		<script>
 			var table = null;
 			$(function() {
 				$('.datepicker').datepicker({
 					dateFormat: 'dd/mm/yy'
 				});

 				$('#issued_clients_table').dataTable();
 				table = $("#issued_clients_table").DataTable();

 				$(document).on("click", ".unissue_client", function() {
 					var id = $(this).data("id");
 					console.log(id);

 					$.confirm({
 						title: 'Confirm Action',
 						content: 'You are about to un-issue this client.',
 						buttons: {
 							confirm: {
 								text: 'Proceed',
 								btnClass: 'btn-red',
 								keys: ['enter', 'shift'],
 								action: function() {
 									console.log("Unissuing client " + id);

 									var data = {
 										action: "unissue_client",
 										client_id: id
 									}

 									$.ajax({
 										data: data,
 										type: "post",
 										url: "libs/api/deal_api.php",
 										success: function(data) {
 											console.log(data);
 											$('#issued_client_' + id).remove();
 											table.row("#issued_client_" + id).remove().draw(false);
 											console.log("Client successfully unissued.");
 										},
 										error: function(data) {
 											console.log('Error:', data);
 										}
 									});
 								}
 							},
 							cancel: function() {

 							}
 						}
 					});
 				});
 			});
 		</script>


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
 						<button type="button" class="close" id="close_client_modal" data-dismiss="modal" aria-label="Close"><span style="color:white;" aria-hidden="true">×</span></button>
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
										$clients = $clientController->getAllSubmissionClients();
										foreach ($clients as $client) {
											$id = $client["id"];
											$name = $client["name"];
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
 							<button type="button" class="btn btn-primary" id="btn-save-deal_data" data-action="add" style="display:none;"><i id="save_deal_spinner" class="fas fa-spinner fa-spin" style="display:none;"></i>Save Issued Policy</button>
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
 										<td>Policy Number</td>
 										<td>Insurer</td>
 										<td>Status</td>
 										<td>Arrear Status</td>
 										<td>Client Name, Life Insured</td>
 										<td>Submission Date</td>
 										<td>Original API</td>
 										<td>Date Issued</td>
 										<td>Issued API</td>
 										<td>Notes</td>
 									</thead>
 									<tbody id="client_deals_table_body">
 										<tr>
 											<td>Policy Number</td>
 											<td>Insurer</td>
 											<td>Status</td>
 											<td>Arrear Status</td>
 											<td>Client Name, Life Insured</td>
 											<td data-order="0">01/01/1990</td>
 											<td data-order="0">$20,000.00</td>
 											<td data-order="0">01/01/1990</td>
 											<td data-order="0">$20,000.00</td>
 											<td>Notes</td>
 										</tr>
 									</tbody>
 								</table>
 							</div>
 						</div>
 					</div>
 					<div class="modal-footer">
 						<div id="buttons_div">
 						</div>
 					</div>
 				</div>
 			</div>
 		</div>

 		<!--Follow Up History-->
 		<div class=" modal fade" id="followUpHistoryModal" tabindex="-1" role="dialog" aria-labelledby="followUpHistoryModalLabel" aria-hidden="true">
 			<div id="followUpHistoryModalDialog" class="modal-dialog modal-lg full-screen">
 				<div class="modal-content">
 					<div class="modal-header" style="background-color: #286090; ">
 						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span style="color:white;" aria-hidden="true">×</span></button>
 						<h4 class="modal-title" id="followUpHistoryModalLabel" style="color:white;">Follow Up History</h4>
 					</div>
 					<div class="modal-body">
 						<div class="row">
 							<div class="col-sm-12">
 								<table id='follow_up_history_table' data-toggle="table" class="table table-striped " cellpadding="5px" cellspacing="5px" width='95%'>
 									<thead>
 										<td>Added By</td>
 										<td>Notes</td>
 										<td>Added On</td>
 										<td></td>
 										<td></td>
 									</thead>
 									<tbody>
 										<tr>
 											<td>Added By</td>
 											<td>Notes</td>
 											<td data-order="0">01/01/1990</td>
 											<td><a class="btn-edit-history" data-id='<?php echo "$client_id" ?>'><span class="btn btn-warning glyphicon glyphicon-pencil"></span></i></a></td>
 											<td><a class="btn-delete-history" data-id='<?php echo "$client_id" ?>'><span class="btn btn-warning glyphicon glyphicon-trash"></span></i></a></td>
 										</tr>
 									</tbody>
 								</table>
 							</div>
 						</div>
 					</div>
 					<div class="modal-footer">
 						<div id="buttons_div">
 							<button type="button" data-toggle="modal" data-target="#followUpHistoryEditorModal" class="btn btn-primary" id="btn-add_history" data-id="0">Add Follow Up History</button>
 						</div>
 					</div>
 				</div>
 			</div>
 		</div>


 		<div class="modal fade" id="followUpHistoryEditorModal" tabindex="-1" role="dialog" aria-labelledby="followUpHistoryEditorModalLabel" aria-hidden="true">
 			<div class="modal-dialog modal-lg">
 				<div class="modal-content">
 					<div class="modal-header" style="background-color: #286090; ">
 						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span style="color:white;" aria-hidden="true">×</span></button>
 						<h4 class="modal-title" id="followUpHistoryEditorModalLabel" style="color:white;">Follow Up History Editor</h4>
 					</div>
 					<div class="modal-body">
 						<form id="frmHistory" name="frmHistory" class="form-horizontal" novalidate="">
 							<div class="form-group error">
 								<label for="inputTask" class="col-sm-1 control-label">Notes</label>
 								<div class="col-sm-11">
 									<textarea class="form-control has-error" id="follow_up_history_notes" name="follow_up_history_notes" placeholder="Notes" rows="10" value=""></textarea>
 								</div>
 							</div>
 							<input type="hidden" id="history_id" name="history_id" value="0">
 						</form>
 					</div>
 					<div class="modal-footer">
 						<button type="button" class="btn btn-primary" id="btn-save_history" data-action="add">Save</button>
 					</div>
 				</div>
 			</div>
 		</div>

 		<!-- PDF Modal -->
 		<div class="modal fade" id="pdfModal" role="dialog" style="z-index:10000;width: 100%;">
 			<div class="modal-dialog modal-lg">
 				<div class="modal-content">
 					<div class="modal-header">
 						<button type="button" class="close" data-dismiss="modal">&times;</button>
 						<h2 class="modal-title" style="float: left;">Invoice Preview</h2>
 					</div>
 					<div class="modal-body" id="pdfModalBody">

 					</div>
 					<div class="modal-footer">
 						<button type="button" class="btn btn-primary" id='send_pdf'>Send</button>
 						<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
 					</div>
 				</div>
 			</div>
 		</div>

 		<!--
            Send Client Data Modal
        -->
 		<div class="modal fade" id="sendModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
 			<div class="modal-dialog">
 				<div class="modal-content">
 					<div class="modal-header">
 						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
 						<h4 class="modal-title" id="myModalLabel">Send Client Data</h4>
 					</div>
 					<form id="frmSendData" name="frmSendData" class="form-horizontal" novalidate="">
 						<div class="modal-body">
 							<div class="row form-group error">
 								<label for="inputTask" class="col-sm-2 control-label">Name</label>
 								<div class="col-sm-10">
 									<input type="text" class="form-control has-error" id="send_name" name="name" placeholder="Receipient Name" value="">
 								</div>
 							</div>
 							<br>
 							<div class="row form-group error">
 								<label for="inputTask" class="col-sm-2 control-label">Email</label>
 								<div class="col-sm-10">
 									<input type="text" class="form-control has-error" id="send_email" name="email" placeholder="Receipient Email" value="">
 									<input type="hidden" class="form-control has-error" id="send_client_id" name="client_id" placeholder="" value="">
 								</div>
 							</div>
 						</div>
 						<div class="modal-footer">
 							<button type="button" class="btn btn-danger" id="btn-data-send" value="Yes"><i id="send_spinner" class="fas fa-spinner fa-spin" style="display:none;"></i> Send Client Data</button>
 						</div>
 					</form>
 				</div>
 			</div>
 		</div>
 		<!--
            End of Send Client Data
        -->

		<style>
			<?php 
			if($_SESSION['myusertype']!="Admin") {?>
				.hide-non-admin {
					display:none;
				}
			<?php 
			}?>
		</style>
						

 		<script>
 			var current_user_id = <?php echo $_SESSION["myuserid"]; ?>;
 		</script>

 		<?php
			//Issue client if it came from submissions page
			if (isset($_GET["issue_client"])) {
				$issue_id = $_GET["issue_client"];
				$client_issued = $dealController->getIssuedClientProfile($issue_id);
				if (!isset($client_issued)) {
					echo "
							<script>
								$(function(){
									$('#clients_list').val('" . $issue_id . "');
									$('#clients_list').trigger('change');
									$('#myModal').modal('show');
								});
							</script>
						";
				}
			}
			?>


 		<?php
			if (isset($_GET["edit"])) {
			?>

 			<script>
 				<?php echo "let edit_id = " . $_GET["edit"] . ";
							let name = '" . $_GET["name"] . "'
				 " ?>
 				$(function() {
 					let tbl = $('#issued_clients_table').DataTable();
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
 					let tbl = $('#issued_clients_table').DataTable();
 					tbl.search(name).draw();
 					setTimeout(function() {
 						$("#btn-view-" + edit_id).trigger("click");
 					}, 50);
 				});
 			</script>

 		<?php
			}
			?>

 		<script src="js/issues-crud.js"></script>
 		<script src="js/loading.js"></script>
 	</body>

 	</html>

 <?php

	}
	?>