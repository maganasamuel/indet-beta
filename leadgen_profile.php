 <?php
	session_start();
	include_once("libs/api/classes/general.class.php");
	include_once("libs/api/controllers/LeadGenerator.controller.php");
	require_once "libs/indet_dates_helper.php";
	require_once "libs/indet_alphanumeric_helper.php";


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

 	<script src="https://cdn.jsdelivr.net/npm/chart.js@2.8.0/dist/Chart.min.js"></script>
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
 			<h2 class="slide">Lead Generator Profile</h2>
 		</div>

 		<?php require "database.php";

				$leadGeneratorController = new LeadGeneratorController();
				$indet_dates_helper = new INDET_DATES_HELPER();
				$indet_alphanumeric_helper = new INDET_ALPHANUMERIC_HELPER();

				$leadGenerator_id = $_GET["id"];
				$year_input = (isset($_GET["year"])) ? $_GET["year"] : date("Y");

				$leadGenerator = $leadGeneratorController->getLeadGenerator($leadGenerator_id);
				$leadGenerator = (object) $leadGenerator->fetch_assoc();

				class Data
				{
					public $generated = array();
					public $cancelled = array();
					public $submission_api = array();
					public $issued_api = array();
				}

				//Performance
				$leadGenerator->performance = new stdClass();
				$leadGenerator->quarterly = new Data();
				$leadGenerator->monthly = new Data();

				//Fetch quarters
				$q = array();
				$q[] = $indet_dates_helper->GetQuarter("First", $year_input);
				$q[] = $indet_dates_helper->GetQuarter("Second", $year_input);
				$q[] = $indet_dates_helper->GetQuarter("Third", $year_input);
				$q[] = $indet_dates_helper->GetQuarter("Fourth", $year_input);

				$m = array();
				//Fetch Months
				for ($i = 1; $i <= 12; $i++) {
					$m[] = $indet_dates_helper->getMonth($indet_alphanumeric_helper->convertToTwoDigits($i), $year_input);
				}

				//Fetch Quarterly Generated Data
				for ($i = 0; $i <= 3; $i++) {
					$leadGenerator->quarterly->generated[] = $leadGeneratorController->getLeadsGeneratedInDateRange($leadGenerator_id, $q[$i]->from->format("Ymd"), $q[$i]->to->format("Ymd"))->num_rows;
				}

				//Fetch Quarterly Cancelled Data
				for ($i = 0; $i <= 3; $i++) {
					$leadGenerator->quarterly->cancelled[] = $leadGeneratorController->getLeadsCancelledInDateRange($leadGenerator_id, $q[$i]->from->format("Ymd"), $q[$i]->to->format("Ymd"))->num_rows;
				}
				//Fetch Quarterly Submission Data
				for ($i = 0; $i <= 3; $i++) {
					$leadGenerator->quarterly->submission_api[] = $leadGeneratorController->getLeadGeneratorSubmissionsAPIInDateRange($leadGenerator_id, $q[$i]->from->format("Ymd"), $q[$i]->to->format("Ymd"));
				}

				//Fetch Quarterly Issued Data
				for ($i = 0; $i <= 3; $i++) {
					$leadGenerator->quarterly->issued_api[] = $leadGeneratorController->getLeadGeneratorIssuedAPIInDateRange($leadGenerator_id, $q[$i]->from->format("Ymd"), $q[$i]->to->format("Ymd"));
				}

				//Fetch monthly generated data
				for ($i = 0; $i <= 11; $i++) {
					$leadGenerator->monthly->generated[] = $leadGeneratorController->getLeadsGeneratedInDateRange($leadGenerator_id, $m[$i]->from->format("Ymd"), $m[$i]->to->format("Ymd"))->num_rows;
				}

				//Fetch monthly cancelled data
				for ($i = 0; $i <= 11; $i++) {
					$leadGenerator->monthly->cancelled[] = $leadGeneratorController->getLeadsCancelledInDateRange($leadGenerator_id, $m[$i]->from->format("Ymd"), $m[$i]->to->format("Ymd"))->num_rows;
				}

				//Fetch monthly Submission data
				for ($i = 0; $i <= 11; $i++) {
					$leadGenerator->monthly->submission_api[] = $leadGeneratorController->getLeadGeneratorSubmissionsAPIInDateRange($leadGenerator_id, $m[$i]->from->format("Ymd"), $m[$i]->to->format("Ymd"));
				}

				//Fetch monthly Issued data
				for ($i = 0; $i <= 11; $i++) {
					$leadGenerator->monthly->issued_api[] = $leadGeneratorController->getLeadGeneratorIssuedAPIInDateRange($leadGenerator_id, $m[$i]->from->format("Ymd"), $m[$i]->to->format("Ymd"));
				}

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
				$query = "SELECT * , a.name as name, t.name as team_name FROM adviser_tbl a LEFT JOIN teams t ON a.team_id = t.id where a.id = $adviser_id";
				$displayquery = mysqli_query($con, $query) or die('Could not look up user information; ' . mysqli_error($con));
				$row = mysqli_fetch_assoc($displayquery);
				$adviser = (object) $row;

				//echo $query . "<hr>";


				//Fetch all of Adviser's issued leads data
				$query = "SELECT *, a.name as name, t.name as team_name, c.name as client_name, c.date_submitted as date_generated, s.timestamp as date_submitted, i.date_issued as date_issued, a.id as adviser_id, a.fsp_num as fsp_num, c.id as client_id, i.id as issued_client_id, s.id as submission_client_id FROM clients_tbl c LEFT JOIN submission_clients s ON c.id = s.client_id LEFT JOIN issued_clients_tbl i ON i.name = c.id LEFT JOIN adviser_tbl a ON a.id=c.assigned_to LEFT JOIN teams t ON a.team_id = t.id WHERE a.id = $adviser_id AND c.status!='Cancelled'";
				//echo $query . "<hr>";
				$displayquery = mysqli_query($con, $query) or die('Could not look up user information; ' . mysqli_error($con));
				$ctr = 0;
				$total_pending_deals = 0;
				$total_pending_deals_api = 0;
				$total_issued_deals = 0;
				$total_issued_deals_api = 0;
				$total_cancelled_deals = 0;
				$total_cancelled_deals_api = 0;

				while ($row = mysqli_fetch_assoc($displayquery)) {
					extract($row);

					//var_dump($row);

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
 			<div>

 				<div class="row">
 					<div class="col-sm-4">

 						<div class="row">
 							<div class="col-sm-6">
 								<h4>
 									Name:
 								</h4>
 							</div>
 							<div class="col-sm-6">
 								<h4>
 									<?php echo $leadGenerator->name ?>
 								</h4>
 							</div>
 						</div>
				 <br>
 						<div class="row">
 							<div class="col-sm-6">
 								<h4>
 									Leads Generated:
 								</h4>
 							</div>
 							<div class="col-sm-6">
 								<h4>
 									<?php echo $leadGenerator->leads_generated; ?>
 								</h4>
 							</div>
 						</div>
 					</div>
 					<div class="col-sm-4">

 						<div class="row">
 							<div class="col-sm-6">
 								<h4>
 									Email Address:
 								</h4>
 							</div>
 							<div class="col-sm-6">
 								<h4>
 									<?php
											$email = (!empty($leadGenerator->email)) ? $leadGenerator->email : "N/A";
											if (strlen($adviser->email) > 25) {
												$first_part = substr($email, 0, 25);
												$email = $first_part . "<br>" . substr($email, 25);
											}
											echo $email;

											?>
 								</h4>
 							</div>
 						</div>
				 <br>
 						<div class="row">
 							<div class="col-sm-6">
 								<h4>
 									Leads Cancelled:
 								</h4>
 							</div>
 							<div class="col-sm-6">
 								<h4>
 									<?php echo $leadGenerator->leads_cancelled; ?>
 								</h4>
 							</div>
 						</div>
 					</div>
 					<div class="col-sm-4">
 						<div class="row">
 							<div class="col-sm-6">
 								<h4>
 									Birthday:
 								</h4>
 							</div>
 							<div class="col-sm-6">
 								<h4>
 									<?php echo (!empty($leadGenerator->birthday)) ? date("F d, Y", $leadGenerator->birthday) : "N/A"; ?>
 								</h4>
 							</div>
 						</div>
 					</div>
 				</div>

 				<hr>
 				<div class="row">
 					<div class="col-sm-6">
 						<h3>
 							Monthly Data:
 							<ul class="nav nav-tabs">
 								<li class="active"><a data-toggle="tab" href="#month_data">Data</a></li>
 								<li><a data-toggle="tab" href="#month_graphs">Graphs</a></li>
 							</ul>
 						</h3>
 					</div>
 					<div class="col-sm-6">
 						<h3>
 							Quarterly Data:
 							<ul class="nav nav-tabs">
 								<li class="active"><a data-toggle="tab" href="#quarter_data">Data</a></li>
 								<li><a data-toggle="tab" href="#quarter_graphs">Graphs</a></li>
 							</ul>
 						</h3>
 					</div>
 				</div>

 				<div class="row profile_header">
 					<div class="col-sm-6">
 						<!--First Column-->

 						<div class="tab-content">
 							<div id="month_data" class="tab-pane fade in active">
 								<table class="table">
 									<thead>
 										<tr>
 											<th colspan="5" style="text-align:center;"><?php echo $year_input ?> Monthly Lead Generation Data</th>
 										</tr>
 										<tr>
 											<th scope="col" style="text-align:center;">Month</th>
 											<th scope="col" style="text-align:center;">Generated</th>
 											<th scope="col" style="text-align:center;">Cancelled</th>
 											<th scope="col" style="text-align:center;">Submission API</th>
 											<th scope="col" style="text-align:center;">Issued API</th>
 										</tr>
 									</thead>

 									<tbody>
 										<?php
												for ($i = 0; $i <= 11; $i++) {
													echo "
												<tr>
													<th scope='row'  data-order='$i' style='text-align:center;'>" . date("F", strtotime($m[$i]->from->format("Ymd"))) . "</th>
													<td style='text-align:center;'>" . $leadGenerator->monthly->generated[$i] . "</td>
													<td style='text-align:center;'>" . $leadGenerator->monthly->cancelled[$i] . "</td>
													<td style='text-align:center;'>" . '$' . number_format($leadGenerator->monthly->submission_api[$i], 2) . "</td>
													<td style='text-align:center;'>" . '$' . number_format($leadGenerator->monthly->issued_api[$i], 2) . "</td>
												</tr>";
												}
												?>

 										<tr>
 											<th scope='row' data-order='12' style='text-align:center;'>Total </th>
 											<td style='text-align:center;'><?php echo array_sum($leadGenerator->monthly->generated) ?></td>
 											<td style='text-align:center;'><?php echo array_sum($leadGenerator->monthly->cancelled) ?></td>
 											<td style='text-align:center;'>$ <?php echo number_format(array_sum($leadGenerator->monthly->submission_api), 2) ?></td>
 											<td style='text-align:center;'><?php echo number_format(array_sum($leadGenerator->monthly->issued_api), 2) ?></td>
 										</tr>
 									</tbody>
 								</table>
 							</div>
 							<div id="month_graphs" class="tab-pane fade">
 								<canvas id="monthly-generation-graph" width="800" height="450"></canvas>
 								<canvas id="monthly-api-graph" width="800" height="450"></canvas>
 							</div>
 						</div>
 					</div>
 					<div class="col-sm-6">
 						<!--Second Column-->
 						<div class="tab-content">
 							<div id="quarter_data" class="tab-pane fade in active">
 								<table class="table">
 									<thead>
 										<tr>
 											<th colspan="5" style="text-align:center;"><?php echo $year_input ?> Quarterly Lead Generation Data</th>
 										</tr>
 										<tr>
 											<th scope="col" style="text-align:center;">Quarter</th>
 											<th scope="col" style="text-align:center;">Generated</th>
 											<th scope="col" style="text-align:center;">Cancelled</th>
 											<th scope="col" style="text-align:center;">Submission API</th>
 											<th scope="col" style="text-align:center;">Issued API</th>
 										</tr>
 									</thead>

 									<tbody>
 										<tr>
 											<th scope="row" data-order="1" style="text-align:center;">First</th>
 											<td style="text-align:center;"><?php echo $leadGenerator->quarterly->generated[0] ?></td>
 											<td style="text-align:center;"><?php echo $leadGenerator->quarterly->cancelled[0] ?></td>
 											<td style="text-align:center;"><?php echo "$" . number_format($leadGenerator->quarterly->submission_api[0], 2) ?></td>
 											<td style="text-align:center;"><?php echo "$" . number_format($leadGenerator->quarterly->issued_api[0], 2) ?></td>
 										</tr>
 										<tr>
 											<th scope="row" data-order="2" style="text-align:center;">Second</th>

 											<td style="text-align:center;"><?php echo $leadGenerator->quarterly->generated[1] ?></td>
 											<td style="text-align:center;"><?php echo $leadGenerator->quarterly->cancelled[1] ?></td>
 											<td style="text-align:center;"><?php echo "$" . number_format($leadGenerator->quarterly->submission_api[1], 2) ?></td>
 											<td style="text-align:center;"><?php echo "$" . number_format($leadGenerator->quarterly->issued_api[1], 2) ?></td>
 										</tr>
 										<tr>
 											<th scope="row" data-order="3" style="text-align:center;">Third</th>
 											<td style="text-align:center;"><?php echo $leadGenerator->quarterly->generated[2] ?></td>
 											<td style="text-align:center;"><?php echo $leadGenerator->quarterly->cancelled[2] ?></td>
 											<td style="text-align:center;"><?php echo "$" . number_format($leadGenerator->quarterly->submission_api[2], 2) ?></td>
 											<td style="text-align:center;"><?php echo "$" . number_format($leadGenerator->quarterly->issued_api[2], 2) ?></td>
 										</tr>
 										<tr>
 											<th scope="row" data-order="4" style="text-align:center;">Fourth</th>
 											<td style="text-align:center;"><?php echo $leadGenerator->quarterly->generated[3] ?></td>
 											<td style="text-align:center;"><?php echo $leadGenerator->quarterly->cancelled[3] ?></td>
 											<td style="text-align:center;"><?php echo "$" . number_format($leadGenerator->quarterly->submission_api[3], 2) ?></td>
 											<td style="text-align:center;"><?php echo "$" . number_format($leadGenerator->quarterly->issued_api[3], 2) ?></td>
 										</tr>
 										<tr>
 											<th scope="row" data-order="5" style="text-align:center;">Total</th>
 											<td style="text-align:center;"><?php echo array_sum($leadGenerator->quarterly->generated) ?></td>
 											<td style="text-align:center;"><?php echo array_sum($leadGenerator->quarterly->cancelled) ?></td>
 											<td style="text-align:center;"><?php echo "$" . number_format(array_sum($leadGenerator->quarterly->submission_api), 2) ?></td>
 											<td style="text-align:center;"><?php echo "$" . number_format(array_sum($leadGenerator->quarterly->issued_api), 2) ?></td>
 										</tr>
 									</tbody>
 								</table>
 							</div>
 							<div id="quarter_graphs" class="tab-pane fade">
 								<canvas id="quarterly-generation-graph" width="800" height="450"></canvas>
 								<canvas id="quarterly-api-graph" width="800" height="450"></canvas>
 							</div>
 						</div>
 					</div>
 				</div>
 			</div>
 			<!--END OF LAYOUT-->
 		</div>
 		<hr>
 		<!--

 		<div class="row">
 			<div class="col-sm-10"></div>
 			<div class="col-sm-1"><button type="button" class="btn btn-primary" style="margin-top:-50px;" id="create"><span style="font-size:15px;" class="glyphicon glyphicon-print"></span></button> <a href="edit_adviser.php?edit_id=<?php echo $adviser_id ?>" class="btn btn-warning" style="margin-top:-50px;"><span style="font-size:15px;" class="glyphicon glyphicon-pencil"></span></a></div>
 		</div>
											-->
	 </div>
	 
 	<script>
 		//Lead Generation
 		//Generated Lead Quarterly
 		new Chart(document.getElementById("quarterly-generation-graph"), {
 			type: 'line',
 			data: {
 				labels: ["First Quarter", "Second Quarter", "Third Quarter", "Fourth Quarter"],
 				datasets: [{
 					data: <?php echo json_encode(array($leadGenerator->quarterly->generated[0], $leadGenerator->quarterly->generated[1], $leadGenerator->quarterly->generated[2], $leadGenerator->quarterly->generated[3])); ?>,
 					label: "Leads Generated",
 					borderColor: "#3cba9f",
 					fill: false
 				}, {
 					data: <?php echo json_encode(array($leadGenerator->quarterly->cancelled[0], $leadGenerator->quarterly->cancelled[1], $leadGenerator->quarterly->cancelled[2], $leadGenerator->quarterly->cancelled[3])); ?>,
 					label: "Leads Cancelled",
 					borderColor: "#ff5555",
 					fill: false
 				}]
 			},
 			options: {
 				title: {
 					display: true,
 					text: 'Leads Generated Quarterly'
 				}
 			}
 		});

 		//Generated API Quarterly
 		new Chart(document.getElementById("quarterly-api-graph"), {
 			type: 'line',
 			data: {
 				labels: ["First Quarter", "Second Quarter", "Third Quarter", "Fourth Quarter"],
 				datasets: [{
 					data: <?php echo json_encode(array($leadGenerator->quarterly->submission_api[0], $leadGenerator->quarterly->submission_api[1], $leadGenerator->quarterly->submission_api[2], $leadGenerator->quarterly->submission_api[3])); ?>,
 					label: "Submission API",
 					borderColor: "#3e95cd",
 					fill: false
 				}, {
 					data: <?php echo json_encode(array($leadGenerator->quarterly->issued_api[0], $leadGenerator->quarterly->issued_api[1], $leadGenerator->quarterly->issued_api[2], $leadGenerator->quarterly->issued_api[3])); ?>,
 					label: "Issued API",
 					borderColor: "#3cba9f",
 					fill: false
 				}]
 			},
 			options: {
 				title: {
 					display: true,
 					text: 'API Generated Quarterly'
 				}
 			}
 		});


 		//Generated Lead Quarterly
 		new Chart(document.getElementById("monthly-generation-graph"), {
 			type: 'line',
 			data: {
 				labels: ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"],
 				datasets: [{
 					data: <?php echo json_encode($leadGenerator->monthly->generated); ?>,
 					label: "Leads Generated",
 					borderColor: "#3cba9f",
 					fill: false
 				}, {
 					data: <?php echo json_encode($leadGenerator->monthly->cancelled); ?>,
 					label: "Leads Cancelled",
 					borderColor: "#ff5555",
 					fill: false
 				}]
 			},
 			options: {
 				title: {
 					display: true,
 					text: 'Leads Generated Monthly'
 				}
 			}
 		});

 		//Generated API Quarterly
 		new Chart(document.getElementById("monthly-api-graph"), {
 			type: 'line',
 			data: {
 				labels: ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"],
 				datasets: [{
 					data: <?php echo json_encode($leadGenerator->monthly->submission_api); ?>,
 					label: "Submission API",
 					borderColor: "#3e95cd",
 					fill: false
 				}, {
 					data: <?php echo json_encode($leadGenerator->monthly->issued_api); ?>,
 					label: "Issued API",
 					borderColor: "#3cba9f",
 					fill: false
 				}]
 			},
 			options: {
 				title: {
 					display: true,
 					text: 'API Generated Monthly'
 				}
 			}
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