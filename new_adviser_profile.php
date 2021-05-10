 <?php
	session_start();
	date_default_timezone_set('Pacific/Auckland');
	//Restrict access to admin only
	include "partials/admin_only.php";

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
 	<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.2/css/all.css" integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">

 	<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"></script>
 	<script>
 		$(function() {

 			$('#number_of_leads').on("keyup change", function() {
 				var api = $(this).val();
 				api = api.replace(/[^0-9]/g, "");
 				console.log(api);
 				$(this).val(api);
 			});
 		});
 	</script>
 </head>

 <body>
 	<div align="center">
 		<div class="jumbotron">
 			<h2 class="slide">Adviser Profile</h2>
 			<a href="create_production_report" class="padded" style="float:right;">Create Adviser Report</a>
 		</div>

         <?php require "database.php";
         
				include_once("libs/api/controllers/Adviser.controller.php");
				$adviserController = new AdviserController();

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
				$due = date('d/m/Y', strtotime('+7 days'));
				if (date("d") > 15) {
					$initial = date("Ym") . "16";
					$end = date("Ymt");
					//Second Date Range
				} else {
					$initial = date("Ym") . "01";
					$end = date("Ym") . "15";
				}

				$period_tooltip = "Current Period: " . date("F d, Y", strtotime($initial)) . " to " . date("F d, Y", strtotime($end));
                //Fetch adviser data;
                
                $adviser = (object) $adviserController->getAdviserWithTeamData($adviser_id);

				//Fetch all of Adviser's issued leads data
                $adviser_deals = $adviserController->getAdviserDealsData($adviser_id);
                
                $adviser_stats = $adviserController->getAdviserDealsData($adviser_id);
                
                $ctr = 0;
				$total_pending_deals = 0;
				$total_pending_deals_api = 0;
				$total_issued_deals = 0;
				$total_issued_deals_api = 0;
				$total_cancelled_deals = 0;
				$total_cancelled_deals_api = 0;

				while ($row = $adviser_deals->fetch_assoc()) {
					extract($row);

					$all_deals[] = json_encode($deals);
					if ($date_issued != null) {
						$clients->total_issued++;
						$clients->total_issued_api += $issued;

						$date_to_compare = $date_issued;
						if ($date_to_compare <= $end && $date_to_compare >= $initial) {
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

					if ($date_submitted != null) {
						$submission_date = date("Ymd", strtotime($date_submitted));
						$date_to_compare = $submission_date;
						$sub_deals = json_decode($deals);
						foreach ($sub_deals as $deal) {
							if ($deal->status == "Pending" || $deal->status == "Issued") {
								$clients->total_submission_api += $deal->original_api;
							}

							if ($deal->status == "Pending") {
								$total_pending_deals++;
								$total_pending_deals_api += $deal->original_api;
								$life_insured = $client_name;
								if (!empty($deal->life_insured))
									$life_insured .= ", " . $deal->life_insured;

								$clients->submitted[] = array(
									"ID" => $submission_client_id,
									"Client" => $life_insured,
									"Date" => NZEntryToDateTime($deal->submission_date),
									"Deal" => $deal,
									"SubmissionAPI" => $deal->original_api,
								);
							} elseif ($deal->status == "Issued") {
								$total_issued_deals++;
								$total_issued_deals_api += $deal->issued_api;
								$life_insured = $client_name;
								if (!empty($deal->life_insured))
									$life_insured .= ", " . $deal->life_insured;

								$clients->issued_deals[] = array(
									"ID" => $issued_client_id,
									"Client" => $life_insured,
									"Date" => NZEntryToDateTime($deal->date_issued),
									"Deal" => $deal,
									"IssuedAPI" => $deal->issued_api,
								);

								if (isset($deal->clawback_status)) {
									if ($deal->clawback_status == "Cancelled") {
										$total_cancelled_deals++;
										$total_cancelled_deals_api += $deal->clawback_api;
										$life_insured = $client_name;
										if (!empty($deal->life_insured))
											$life_insured .= ", " . $deal->life_insured;

										$clients->cancelled_deals[] = array(
											"ID" => $issued_client_id,
											"Client" => $life_insured,
											"Date" => NZEntryToDateTime($deal->clawback_date),
											"Deal" => $deal,
											"CancelledAPI" => $deal->clawback_api,
										);

										if ($deal->clawback_date <= $end && $deal->clawback_date >= $initial) {
											$clients->deal_cancellations_for_period++;
											$clients->deal_cancellations_api_for_period += $deal->clawback_api;
										}
									}
								}
							}
						}
						if ($date_to_compare <= $end && $date_to_compare >= $initial) {
							$clients->submission_api_for_period += $deal->original_api;
							$clients->leads_submitted_for_period++;
						}
					}

					if ($date_generated != null) {

						//var_dump($row);
						//echo "<hr>";
						$date_to_compare = $date_generated;
						if ($date_to_compare <= $end && $date_to_compare >= $initial) {
							$clients->leads_assigned_for_period++;
							$clients->generated[] = array(
								"Client" => $client_name,
								"Date" => $date_to_compare,
							);
						}
					}
					if ($lead_by != "Telemarketer")
						$clients->total_leads++;

					$ctr++;
				}
                $net_api = $clients->total_issued_api - $total_cancelled_deals_api;
                
				//Fetch payables
				$query = "SELECT * FROM transactions WHERE adviser_id = $adviser_id ORDER BY date DESC";
				$displayquery = mysqli_query($con, $query) or die('Could not look up user information; ' . mysqli_error($con));
				$total_leads_payable = 0;
				$total_issued_payable = 0;
				$total_outstanding_payable_amount_header = 0;
				while ($row = mysqli_fetch_assoc($displayquery)) {
					extract($row);
					$status = CheckTransactionStatus($status);
					switch ($status) {
						case "Manual Billed Assigned Leads":
						case "Billed Assigned Leads":
							$total_leads_payable += $number_of_leads;
							break;
						case "Manual Billed Issued Leads":
						case "Billed Issued Leads":
							$total_issued_payable += $number_of_leads;
							break;
						case "Paid Issued Leads":
						case "Waived Issued Leads":
						case "Cancelled Issued Leads":
							$total_issued_payable -= $number_of_leads;
							break;
						default:
							$total_leads_payable -= $number_of_leads;
							break;
					}
					//echo $status . " from " . $amount . "<hr>";
					$total_outstanding_payable_amount_header += $amount;
				}
				?>
 		<div id="client_labels">
 			<style type="text/css">
 				.profile_header div:nth-child(even):after {
 					content: '';
 					height: 320px;
 					width: 1px;

 					position: absolute;
 					right: 0;
 					top: 0;

 					background-color: #000000;
 				}

 				.profile_header div:last-child:after {
 					content: '';
 					height: 0%;
 					width: 1px;
 				}
 			</style>
 			<div>
 				<div class="row">
 					<div class="col-sm-4">
 						<h3>
 							Adviser Details:
 						</h3>
 					</div>
 					<div class="col-sm-4">
 						<h3>
 							Production:

 						</h3>
 					</div>
 					<div class="col-sm-4">
 						<h3>
 							Cancellations:
 						</h3>
 					</div>
 				</div>

 				<div class="row profile_header">
 					<div class="col-sm-4">
 						<!--First Column-->
 						<div class="row">
 							<div class="col-sm-6">
 								<h4>
 									Adviser:
 								</h4>
 							</div>
 							<div class="col-sm-6">
 								<h4>
 									<?php echo $adviser->name ?>
 								</h4>
 							</div>
 						</div>
 						<div class="row">
 							<div class="col-sm-6">
 								<h4>
 									Team:
 								</h4>
 							</div>
 							<div class="col-sm-6">
 								<h4>
 									<?php
											echo (!empty($adviser->team_name)) ? $adviser->team_name : "Not Assigned";
											?>
 								</h4>
 							</div>
 						</div>
 						<div class="row">
 							<div class="col-sm-6">
 								<h4>
 									Email Address:
 								</h4>
 							</div>
 							<div class="col-sm-6">
 								<h4>
 									<?php
											$email = $adviser->email;
											if (strlen($adviser->email) > 25) {
												$first_part = substr($email, 0, 25);
												$email = $first_part . "<br>" . substr($email, 25);
											}
											echo $email;

											?>
 								</h4>
 							</div>
 						</div>
 						<div class="row">
 							<div class="col-sm-6">
 								<h4>
 									Address:
 								</h4>
 							</div>
 							<div class="col-sm-6">
 								<h4>
 									<?php echo $adviser->address ?>
 								</h4>
 							</div>
 						</div>
 						<div class="row">
 							<div class="col-sm-6">
 								<h4>
 									FSP Number:
 								</h4>
 							</div>
 							<div class="col-sm-6">
 								<h4>
 									<?php echo $adviser->fsp_num ?>
 								</h4>
 							</div>
 						</div>
 						<div class="row">
 							<div class="col-sm-6">
 								<h4>
 									Rate per Lead:
 								</h4>
 							</div>
 							<div class="col-sm-6">
 								<h4>
 									$<?php echo number_format($adviser->leads, 2)  ?>
 								</h4>
 							</div>
 						</div>
 						<div class="row">
 							<div class="col-sm-6">
 								<h4>
 									Rate per Issued Policy:
 								</h4>
 							</div>
 							<div class="col-sm-6">
 								<h4>
 									$<?php echo number_format($adviser->bonus, 2)  ?>
 								</h4>
 							</div>
 						</div>
 						<div class="row">
 							<div class="col-sm-6">
 								<h4>
 									Accumulative Leads Assigned:
 								</h4>
 							</div>
 							<div class="col-sm-6">
 								<h4>
 									<?php echo $clients->total_leads  ?>
 								</h4>
 							</div>
 						</div>
 						<div class="row">

 						</div>
 					</div>
 					<div class="col-sm-4">
 						<!--Second Column-->
 						<div class="row">
 							<div class="col-sm-6">
 								<h4>
 									NET API:
 								</h4>
 							</div>
 							<div class="col-sm-6">
 								<h4>
 									$<?php echo number_format($net_api, 2) ?>
 								</h4>
 							</div>
 						</div>

 						<div class="row">
 							<div class="col-sm-6">
 								<h4>
 									Total Submissions for the Period <i data-toggle="tooltip" data-placement="top" title="<?php echo $period_tooltip ?>" class="fas fa-question-circle"></i>:

 								</h4>
 							</div>
 							<div class="col-sm-6">
 								<h4>
 									<?php echo $clients->leads_submitted_for_period ?>
 								</h4>
 							</div>
 						</div>
 						<br>
 						<br>
 						<br>
 						<div class="row">
 							<div class="col-sm-6">
 								<h4>
 									Total Submission API for the Period:
 								</h4>
 							</div>
 							<div class="col-sm-6">
 								<h4>
 									$<?php echo number_format($clients->submission_api_for_period, 2) ?>
 								</h4>
 							</div>
 						</div>
 						<br>
 						<br>
 						<br>
 						<div class="row">
 							<div class="col-sm-6">
 								<h4>
 									Accumulative Submission API:
 								</h4>
 							</div>
 							<div class="col-sm-6">
 								<h4>
 									$<?php echo number_format($clients->total_submission_api, 2) ?>
 								</h4>
 							</div>
 						</div>
 						<br>
 						<br>
						 <br>
						 
 						<div class="row">
 							<div class="col-sm-6">
 								<h4>
 									Total Issued Policies for the Period:
 								</h4>
 							</div>
 							<div class="col-sm-6">
 								<h4>
 									<?php echo $clients->leads_issued_for_period ?>
 								</h4>
 							</div>
 						</div>
 						<br>
 						<br>
						 <br>
 						<div class="row">

 							<div class="col-sm-6">
 								<h4>
 									Issued API for the Period:
 								</h4>
 							</div>
 							<div class="col-sm-6">
 								<h4>
 									$<?php echo number_format($clients->issued_api_for_period, 2) ?>
 								</h4>
 							</div>
 						</div>
 						<div class="row">

 							<div class="col-sm-6">
 								<h4>
 									Accumulative Policies Issued:
 								</h4>
 							</div>
 							<div class="col-sm-6">
 								<h4>
 									<?php echo $clients->total_issued ?>
 								</h4>
 							</div>
 						</div>

 					</div>
 					<div class="col-sm-4">
 						<!--Third Column-->
 						<div class="row">
 							<div class="col-sm-6">
 								<h4>
 									Total Cancellations for the Period:
 								</h4>
 							</div>
 							<div class="col-sm-6">
 								<h4>
 									<?php echo $clients->deal_cancellations_for_period  ?>
 								</h4>
 							</div>
 						</div>
 						<div class="row">
 							<div class="col-sm-6">
 								<h4>
 									Total Cancellation API for the Period:
 								</h4>
 							</div>
 							<div class="col-sm-6">
 								<h4>
 									$<?php echo number_format($clients->deal_cancellations_api_for_period, 2)  ?>
 								</h4>
 							</div>
 						</div>
 						<br>
 						<br>
 						<br>
 						<div class="row">
 							<div class="col-sm-6">
 								<h4>
 									Total Accumulative Cancellations:
 								</h4>
 							</div>
 							<div class="col-sm-6">
 								<h4>
 									<?php echo $total_cancelled_deals  ?>
 								</h4>
 							</div>
 						</div>
 						<br>
 						<br>
 						<br>
 						<div class="row">
 							<div class="col-sm-6">
 								<h4>
 									Total Accumulative Cancellation API:
 								</h4>
 							</div>
 							<div class="col-sm-6">
 								<h4>
 									$<?php echo number_format($total_cancelled_deals_api, 2)  ?>
 								</h4>
 							</div>
 						</div>
 						<br>
 						<br>
 						<br>

 					</div>
 				</div>
 			</div>
 			<!--END OF LAYOUT-->
 		</div>
 		<hr>
 		<div class="row">
 			<div class="col-sm-10"></div>
 			<div class="col-sm-1"><button type="button" class="btn btn-primary" style="margin-top:-50px;" id="create"><span style="font-size:15px;" class="glyphicon glyphicon-print"></span></button> <a href="edit_adviser.php?edit_id=<?php echo $adviser_id ?>" class="btn btn-warning" style="margin-top:-50px;"><span style="font-size:15px;" class="glyphicon glyphicon-pencil"></span></a></div>
 		</div>
 	</div>

 	<div class="col-xs-6" style=" min-height: 500px; border-right: 2px solid black; min-height: 500px;">
 		<div class="row">
 			<div class="col-sm-1"></div>
 			<div class="col-sm-5 text-center">
 				<h4>
 					Total Pending Deals: <br><?php echo $total_pending_deals ?><br>
 				</h4>
 			</div>
 			<div class="col-sm-5 text-center">
 				<h4>
 					Total Pending Submission API: <br>$<?php echo number_format($total_pending_deals_api, 2) ?><br>
 				</h4>
 			</div>
 			<div class="col-sm-1"></div>
 		</div>
 		<h2 class="sub-header" style="text-align:center;">Pending Deals</h2>
 		<div class="table-responsive">
 			<table data-toggle="table" class="table table-striped " cellpadding="5px" cellspacing="5px" style="width:90%;">
 				<thead>
 					<td>View</td>
 					<td>Edit</td>
 					<td>Life Insured</td>
 					<td>Date of Submission</td>
 					<td>Submission API</td>
 				</thead>
 				<tbody>
 					<?php
							if (isset($clients->submitted)) {
								if (count($clients->submitted) > 0) {

									foreach ($clients->submitted as $submission_client) {
										$name = $submission_client["Client"];
										$sub_date = $submission_client["Date"];
										$cid = $submission_client["ID"];
										$sub_api = number_format($submission_client["SubmissionAPI"], 2);
										echo "
			<tr cellpadding='5px' cellspacing='5px'>
				<td><a target='_blank' href='show_deal?submission_id=$cid'><i class='fa fa-search'></i></a></td>
				<td><a target='_blank' href='edit_submission_client?submission_id=$cid'><i class='fa fa-pencil-alt text-warning'></i></a></td>
				<td>$name</td>
				<td>$sub_date</td>
				<td>$" . "$sub_api</td>
			</tr>
				";
									}
								}
							}
							?>
 				</tbody>
 			</table>
 		</div>
 		<hr>

 		<!-- Issued Deals -->
 		<div class="row">
 			<div class="col-sm-1"></div>
 			<div class="col-sm-5 text-center">
 				<h4>
 					Total Issued Deals: <br><?php echo $total_issued_deals ?><br>
 				</h4>
 			</div>
 			<div class="col-sm-5 text-center">
 				<h4>
 					Total Issued API: <br>$<?php echo number_format($total_issued_deals_api, 2) ?><br>
 				</h4>
 			</div>
 			<div class="col-sm-1"></div>
 		</div>
 		<h2 class="sub-header" style="text-align:center;">Issued Deals</h2>
 		<div class="table-responsive">
 			<table data-toggle="table" class="table table-striped " cellpadding="5px" cellspacing="5px" style="width:90%;">
 				<thead>
 					<td>View</td>
 					<td>Edit</td>
 					<td>Life Insured</td>
 					<td>Issued Date</td>
 					<td>Issued API</td>
 				</thead>
 				<tbody>
 					<?php
							if (isset($clients->issued_deals)) {
								if (count($clients->issued_deals) > 0) {

									foreach ($clients->issued_deals as $deal) {
										$name = $deal["Client"];
										$date = $deal["Date"];
										$cid = $deal["ID"];
										$api = number_format($deal["IssuedAPI"], 2);
										echo "
			<tr cellpadding='5px' cellspacing='5px'>
				<td><a target='_blank' href='view_issued_client_profile?id=$cid'><i class='fa fa-search'></i></a></td>
				<td><a target='_blank' href='edit_issued_client_profile?id=$cid'><i class='fa fa-pencil-alt text-warning'></i></a></td>
				<td>$name</td>
				<td>$date</td>
				<td>$" . "$api</td>
			</tr>
				";
									}
								}
							}
							?>
 				</tbody>
 			</table>
 		</div>
 		<hr>
 		<!-- Cancelled Deals -->
 		<div class="row">
 			<div class="col-sm-1"></div>
 			<div class="col-sm-5 text-center">
 				<h4>
 					Total Cancelled Deals: <br><?php echo $total_cancelled_deals ?><br>
 				</h4>
 			</div>
 			<div class="col-sm-5 text-center">
 				<h4>
 					Total Cancelled API: <br>$<?php echo number_format($total_cancelled_deals_api, 2) ?><br>
 				</h4>
 			</div>
 			<div class="col-sm-1"></div>
 		</div>
 		<h2 class="sub-header" style="text-align:center;">Cancelled Deals</h2>
 		<div class="table-responsive">
 			<table data-toggle="table" class="table table-striped " cellpadding="5px" cellspacing="5px" style="width:90%;">
 				<thead>
 					<td>View</td>
 					<td>Edit</td>
 					<td>Life Insured</td>
 					<td>Date of Submission</td>
 					<td>Cancellation API</td>
 				</thead>
 				<tbody>
 					<?php
							if (isset($clients->cancelled_deals)) {
								if (count($clients->cancelled_deals) > 0) {

									foreach ($clients->cancelled_deals as $deal) {
										$name = $deal["Client"];
										$date = $deal["Date"];
										$api = number_format($deal["CancelledAPI"], 2);
										$cid = $deal["ID"];
										echo "
				<tr cellpadding='5px' cellspacing='5px'>
					<td><a target='_blank' href='view_issued_client_profile?id=$cid'><i class='fa fa-search'></i></a></td>
					<td><a target='_blank' href='edit_issued_client_profile?id=$cid'><i class='fa fa-pencil-alt text-warning'></i></a></td>
					<td>$name</td>
					<td>$date</td>
					<td>$" . "$api</td>
				</tr>
					";
									}
								}
							}
							?>
 				</tbody>
 			</table>
 		</div>
 		<hr>


 	</div>


 	<div class="col-xs-6">

 		<div class="row">
 			<div class="col-sm-1"></div>
 			<div class="col-sm-3">
 				<h4>
 					Total Leads Payable: <span id="total_leads_payable_header"><?php echo $total_leads_payable ?></span>
 				</h4>
 			</div>
 			<div class="col-sm-4">
 				<h4>
 					Total Issued Leads Payable: <span id="total_issued_leads_payable_header"><?php echo $total_issued_payable ?></span>
 				</h4>
 			</div>
 			<div class="col-sm-4">
 				<h4>
 					Total Outstanding Payable Amount: $<span id="total_outstanding_payable_amount_header"><?php echo number_format($total_outstanding_payable_amount_header, 2) ?></span>
 				</h4>
 			</div>
 		</div>

 		<h2 class="sub-header" style="text-align:center;">
 			<!--<a href="javascript:location.reload()"><i class="fas fa-sync-alt"></i></a>-->Invoice Transaction History <button type="button" class="btn btn-success" style="text-align: right;" id="add_invoice_transaction"><span style="font-size:15px;" class="glyphicon glyphicon-plus"></span></button> </h2>
 		<div class="table-responsive">
 			<table id="transaction-history-table" data-toggle="table" class="table table-striped " cellpadding="5px" cellspacing="5px" style="width:90%; overflow-x: auto; white-space: nowrap;">
 				<thead>
 					<td>Status</td>
 					<td>Date</td>
 					<td>No. of Leads</td>
 					<td>Amount</td>
 					<td>Notes</td>
 					<td>Controls</td>
 				</thead>
 				<tbody id="transactions-list">
 					<?php

							$query = "SELECT * FROM transactions WHERE adviser_id = $adviser_id ORDER BY date DESC, id DESC";
							//echo $query;
							//echo $query . "<hr>";
							$displayquery = mysqli_query($con, $query) or die('Could not look up user information; ' . mysqli_error($con));

							while ($rows = mysqli_fetch_array($displayquery)) :
								$id = $rows["id"];
								$status = $rows["status"];
								$date_sort = $rows["date"];
								$date = NZEntryToDateTime($rows["date"]);
								$number_of_leads = $rows["number_of_leads"];
								$amount = $rows["amount"];
								$notes = $rows["notes"];

								echo "
	<tr id='transaction$id' cellpadding='5px' cellspacing='5px'>
		<td>$status</td>
		<td data-order='$date_sort'>$date</td>
		<td>$number_of_leads</td>
		<td>$" . number_format($amount, 2) . "</td>
		<td>$notes</td>
	";
								echo "
		<td>
			<input data-toggle='modal' data-target='#myModal' type='image' class='open-modal'  src='edit.png' value='$id'>
			<input type='image' class='delete-transaction'  src='delete.png'  value='$id'>
		</td>
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


 	<div id="original_area" style="display:none;">

 		<select class="form-control has-error clients_list" id="clients_list" name="clients_list" required>
 			<option selected disabled hidden>Select client</option>
 			<?php
					$query = "SELECT * FROM clients_tbl WHERE id NOT IN (SELECT name FROM issued_clients_tbl WHERE assigned_to = $adviser_id)  AND assigned_to = $adviser_id ORDER BY TRIM(name) ASC";
					$displayquery = mysqli_query($con, $query) or die('Could not look up user information; ' . mysqli_error($con));
					while ($row = mysqli_fetch_assoc($displayquery)) {
						extract($row);
						echo "<option value='$id'>$name</option>";
					}
					?>
 		</select>

 		<select class="form-control has-error clients_list2" id="clients_list2" name="clients_list" required>
 			<option selected disabled hidden>Select client</option>
 			<?php
					$query = "SELECT * FROM clients_tbl WHERE id IN (SELECT name FROM issued_clients_tbl WHERE assigned_to = $adviser_id) ORDER BY TRIM(name) ASC";
					$displayquery = mysqli_query($con, $query) or die('Could not look up user information; ' . mysqli_error($con));
					while ($row = mysqli_fetch_assoc($displayquery)) {
						extract($row);
						echo "<option value='$id'>$name</option>";
					}
					?>
 		</select>

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
 						<div class="form-group error" id="status_div">
 							<label for="inputTask" class="col-sm-3 control-label">Status</label>
 							<div class="col-sm-9">
 								<select class="form-control has-error" id="status" name="status" required>
 									<option>Paid Assigned Leads</option>
 									<option>Paid Issued Leads</option>
 									<option>Manual Billed Assigned Leads</option>
 									<option>Manual Billed Issued Leads</option>
 									<option>Waived Leads</option>
 									<option>Waived Issued Leads</option>
 									<option>Cancelled Leads</option>
 									<option>Cancelled Issued Leads</option>
 									<option>Assigned Amendment</option>
 									<option>Issued Amendment</option>
 									<option hidden>Billed Assigned Leads</option>
 									<option hidden>Billed Issued Leads</option>
 								</select>
 							</div>
 							<input type="hidden" name="method" id="method" value="">
 							<input type="hidden" name="transaction_id" id="transaction_id" value="">
 							<input type="hidden" name="adviser_id" id="adviser_id" value="<?php echo $adviser_id; ?>">
 							<input type="hidden" name="rate_per_lead" id="rate_per_lead" value="<?php echo $adviser->leads; ?>">
 							<input type="hidden" name="rate_per_issue" id="rate_per_issue" value="<?php echo $adviser->bonus; ?>">
 						</div>
 						<div class="form-group error" id="clients_list_holder" style="display:none;">
 							<label for="inputTask" class="col-sm-3 control-label">Client</label>
 							<div class="col-sm-9" id="clients_list_div">
 							</div>
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
			$invoice_num = 'EIL';

			$count_query = "SELECT id FROM invoices ORDER BY id DESC";
			$searchsum = mysqli_query($con, $count_query) or die('Could not look up user information; ' . mysqli_error($con));

			$rows = mysqli_fetch_array($searchsum);
			$rows_count = isset($rows['id']) ? $rows['id'] : 1;
			switch ($rows_count) {
				case ($rows_count < 10):
					$invoice_num .= '00' . $rows_count;

					break;
				case ($rows_count < 100 && $rows_count >= 10):
					$invoice_num .= '0' . $rows_count;

					break;

				case ($rows_count >= 100):
					$invoice_num .= $rows_count;

					break;

				default:

					break;
			}


			?>
 	<script>
 		$(document).ready(function() {
 			$('[data-toggle="tooltip"]').tooltip();

 			$('.table').dataTable({
 				"columnDefs": [{
 					"targets": [2, 3],
 					"orderable": false
 				}]
 			});

 			//Reset Data Table 
 			$("#transaction-history-table").DataTable().destroy();
 			$("#transaction-history-table").dataTable({
 				"aaSorting": [
 					[1, 'asc']
 				]
 			});


 			$.fn.serializeObject = function() {
 				var o = {};
 				var a = this.serializeArray();
 				$.each(a, function() {
 					if (o[this.name]) {
 						if (!o[this.name].push) {
 							o[this.name] = [o[this.name]];
 						}
 						o[this.name].push(this.value || '');
 					} else {
 						o[this.name] = this.value || '';
 					}
 				});
 				return o;
 			};

 			function showAmount() {
 				var rate_lead = $("#rate_per_lead").val();
 				var rate_issue = $("#rate_per_issue").val();
 				var leads = $("#number_of_leads").val();
 				var selected_status = $("#status").val();
 				var amount = leads;

 				//Empty Clients List Div
 				$("#clients_list_div").html("");

 				//Get Clients List
 				if (selected_status == "Cancelled Leads" || selected_status == "Waived Leads") {
 					console.log("Amendment");
 					for (var i = 0; i < amount; i++) {
 						$('#clients_list').clone().appendTo('#clients_list_div');
 					}
 					$("#clients_list_holder").slideDown();
 				} else if (selected_status == "Cancelled Issued Leads" || selected_status == "Waived Issued Leads") {
 					console.log("Issued Amendment");
 					for (var i = 0; i < amount; i++) {
 						$('#clients_list2').clone().appendTo('#clients_list_div');
 					}

 					$("#clients_list_holder").slideDown();
 				} else {
 					$("#clients_list_holder").slideUp();
 				}

 				if (leads != null) {
 					//Check if Manual and if it isn't make value negative
 					if (!selected_status.includes("Manual") && !selected_status.includes("Billed") && !selected_status.includes("Amendment")) {
 						amount *= -1;
 					}

 					//Assign rate to the amount
 					if (selected_status.includes("Issued")) {
 						amount *= rate_issue;
 					} else {
 						amount *= rate_lead;
 					}

 					//GST
 					amount += amount * .15;

 					$("#amount").val(amount);
 				}
 			}

 			$(".datepicker").datepicker({
 				dateFormat: 'dd/mm/yy'
 			});

 			$("#number_of_leads").on("keyup change", function() {
 				showAmount();
 			});

 			$("#status").on("change", function() {
 				showAmount();
 			});

 			$("#add_invoice_transaction").on("click", function() {
 				$("#frmTransaction").trigger("reset");
 				$('#method').val('POST');
 				$("#status_div").show();
 				showAmount();
 				$('#myModal').modal('show');
 			});

 			$(document).on("click", ".open-modal", function(e) {
 				e.preventDefault();
 				var mat_id = $(this).val();
 				$("#status_div").hide();
 				$.get('crud/transactions-crud.php/?id=' + mat_id, function(data) {
 					console.log(data);
 					$("#number_of_leads").val(data.number_of_leads);
 					var status = data.status;
 					if (status.includes("-")) {
 						var res = status.split("-");
 						status = res[1];
 					}
 					var clients_list = data.clients_list;
 					var clients = null;

 					//console.log("status:" + status);
 					$("#status").val(status);
 					$("#amount").val(data.amount);
 					$("#date").val(data.date);
 					showAmount();

 					if (status == "Waived Leads" || status == "Cancelled Leads") {
 						clients = clients_list.split(",");
 						var looper = -1;

 						$(".clients_list").each(function() {
 							if (looper >= 0) {
 								console.log("Assigning Value" + clients[looper]);
 								$(this).val(clients[looper]);
 								console.log(clients[looper]);
 							}
 							looper++;
 						});
 					}

 					if (status == "Waived Issued Leads" || status == "Cancelled Issued Leads") {
 						clients = clients_list.split(",");
 						var looper = -1;

 						$(".clients_list2").each(function() {
 							if (looper >= 0) {
 								console.log("Assigning Value" + clients[looper]);
 								$(this).val(clients[looper]);
 								console.log(clients[looper]);
 							}
 							looper++;
 						});
 					}

 					$("#notes").val(data.notes);
 					$('#transaction_id').val(data.id);
 					$('#method').val("PUT");

 					$('#myModal').modal('show');
 				});
 			});

 			//Save Transaction
 			$(document).on('click', '#btn-save', function(e) {
 				var data = $("#frmTransaction").serializeObject();
 				method = $("#method").val();
 				console.log(data);
 				$.ajax({
 					data: data,
 					type: "post",
 					url: "crud/transactions-crud.php",
 					success: function(data) {
 						$('#myModal').modal('hide');
 						$('#frmTransaction').trigger("reset");
 						window.location.reload();
 					},
 					error: function(data) {
 						$("#report_text").val(data.reason);
 						console.log(data);
 					}
 				});
 			});

 			//Delete Transaction Confirmation
 			$('#transactions-list').on("click", ".delete-transaction", function() {
 				var mat_id = $(this).val();
 				$('#confirmModal').modal('show');
 				$('#delete-transaction').val(mat_id);
 			});


 			$('#btn-delete-cancel').on("click", function() {
 				$('#confirmModal').modal('hide');
 			});

 			//Delete Transaction
 			$('#btn-delete-confirm').on("click", function() {
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
 					success: function(data) {
 						console.log(data);
 						$("#transaction" + mat_id).remove();
 						$('#confirmModal').modal('hide');
 						$.confirm({
 							title: 'Success!',
 							content: 'You have successfully deleted the transaction.',
 							buttons: {
 								Ok: function() {
 									window.location.reload();
 								},
 							}
 						});
 					},
 					error: function(data) {
 						console.log(data);
 						console.log('Error:', data);
 					}
 				});

 			});



 			$('#create').on('click', function(e) {
 				e.preventDefault();

 				var adv_name = "<?php echo $adviser->name ?>";
 				var adviser_id = "<?php echo $adviser_id ?>";
 				var date_from = "<?php echo NZEntryToDateTime($initial) ?>";
 				var invoice_date = "<?php echo NZEntryToDateTime($now) ?>";
 				var desc = '["charged","issued"]';

 				var until = "<?php echo NZEntryToDateTime($end) ?>";
 				var due_date = "<?php echo $due ?>";
 				var invoice_num = "<?php echo $invoice_num ?>";
 				var other_value = 0;
 				console.log(date_from + ":" + until);
 				$.ajax({
 					dataType: 'json',
 					type: 'POST',
 					data: {
 						adv_name: adv_name,
 						adviser_id,
 						adviser_id,
 						date_from: date_from,
 						invoice_date: invoice_date,
 						desc: desc,
 						until: until,
 						due_date: due_date,
 						invoice_num: invoice_num,
 						other_value: other_value
 					},
 					url: "output.php",
 					success: function(e) {
 						console.log(desc);
 						var mydata = JSON.stringify(e);
 						var link = e['link'];
 						var htm = '<iframe src="' + link + '" style="width: 100%;height: 75%;"></iframe>';
 						$('#confirmIModal').modal('show');
 						$('#modal-body').html(htm);
 						$('#save_pdf').unbind("click");
 						$('#save_pdf').on('click', function() {
 							$.ajax({
 								//dataType:'JSON',
 								data: {
 									mydata: mydata
 								},
 								type: 'POST',
 								url: "save_invoice.php",
 								beforeSend: function() {

 								},
 								success: function(x) {
 									console.log(x);
 									$.confirm({
 										title: 'Success!',
 										content: 'You successfully created an invoice.',
 										buttons: {
 											Ok: function() {
 												console.log(x);
 												window.location = 'adviser_profile.php?id=<?php echo $adviser_id ?>';
 											},
 										}
 									});
 								}
 							});
 						});
 					},
 					error: function(x) {
 						x = JSON.stringify(x);
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


	function DateTimeToNZEntry($date_submitted)
	{
		return substr($date_submitted, 6, 4) . substr($date_submitted, 3, 2) . substr($date_submitted, 0, 2);
	}

	function NZEntryToDateTime($NZEntry)
	{
		return substr($NZEntry, 6, 2) . "/" . substr($NZEntry, 4, 2) . "/" . substr($NZEntry, 0, 4);
	}

	function CheckTransactionStatus($status)
	{
		$issued = stripos($status, 'Billed Issued Leads') !== false;
		$assigned = stripos($status, 'Billed Assigned Leads') !== false;
		$op = $status;
		if ($issued) {
			$op = "Billed Issued Leads";
		} elseif ($assigned) {
			$op = "Billed Assigned Leads";
		}

		return $op;
	}
	?>