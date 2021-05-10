<?php
//Clients Only Query
$clients_query = "SELECT *,l.name as leadgen_name, c.name as client_name, c.id as client_id from leadgen_tbl l LEFT JOIN clients_tbl c ON c.leadgen = l.id WHERE l.id ='$leadgen_id' AND c.date_submitted <= '$until' AND c.date_submitted >= '$date_from'";
$clients_result = mysqli_query($con, $clients_query) or die('Could not look up user information; ' . mysqli_error($con));
$rowcount = mysqli_num_rows($clients_result);
$clients = [];
$totalclients = 0;
//echo "Clients Query:<br>". $clients_query . "<br>";
if ($rowcount == 0) {
	//print "No Records Found";
} else {
	while ($row  = mysqli_fetch_assoc($clients_result)) {
		//if all data is necessary;
		if ($fetchalldata) {
			extract($row);
			$cli = new stdClass();
			$cli->id = $client_id;
			$cli->name = $client_name;
			$cli->adviser = $assigned_to;
			$cli->date_submitted = $date_submitted;
			$clients[] = $cli;
		}

		$leadgen_name = $leadgen_name;
		$totalclients++;
	}
}

//Clients Only Query
$cclients_query = "SELECT *,l.name as leadgen_name, c.name as client_name, c.id as client_id from leadgen_tbl l LEFT JOIN clients_tbl c ON c.leadgen = l.id WHERE l.id ='$leadgen_id' AND c.date_status_updated <= '$until' AND c.date_status_updated >= '$date_from' AND status='Cancelled'";
$cclients_result = mysqli_query($con, $cclients_query) or die('Could not look up user information; ' . mysqli_error($con));
$crowcount = mysqli_num_rows($cclients_result);
$cclients = [];
$totalcclients = 0;
$totalsubmissions = 0;
$totalsubmissionamount = 0;
//echo "Clients Query:<br>". $cclients_query . "<br>";
if ($crowcount == 0) {
	//print "No Records Found";
} else {
	while ($row  = mysqli_fetch_assoc($cclients_result)) {
		//if all data is necessary;
		if ($fetchalldata) {
			extract($row);
			$cli = new stdClass();
			$cli->id = $client_id;
			$cli->name = $client_name;
			$cli->adviser = $assigned_to;
			$cli->date_submitted = $date_submitted;
			$cclients[] = $cli;
			if ($submission) {
				$totalsubmissions++;
				$totalsubmissionamount += $submission_amount;
			}
		}
		$totalcclients++;
	}
}

//Issued Clients Only Query
$issued_clients_query = "SELECT *,l.name as leadgen_name, i.name as issued_client_name, i.id as issued_client_id from leadgen_tbl l LEFT JOIN issued_clients_tbl i ON i.leadgen = l.id WHERE l.id ='$leadgen_id' AND i.date_issued <= '$until' AND i.date_issued >= '$date_from'";
$issued_clients_result = mysqli_query($con, $issued_clients_query) or die('Could not look up user information; ' . mysqli_error($con));
$rowcount = mysqli_num_rows($issued_clients_result);

$issued_clients = [];
$issued_ks = [];

$totalissuedclients = 0;
$totalissuedks = 0;

$totalissuedpremiums = 0;
$totalissuedkspremiums = 0;

$issuedLeadsPercent = 0;

//echo $issued_clients_query . "<br>";
if ($rowcount == 0) {
	//print "No Records Found";
} else {
	while ($row  = mysqli_fetch_assoc($issued_clients_result)) {
		extract($row);
		//if all data is necessary;
		if ($fetchalldata) {
			$icli = new stdClass();
			$icli->id = $client_id;
			$icli->name = $client_name;
			$icli->name = $client_name;
			$icli->adviser = $assigned_to;
			$icli->date_submitted = $date_submitted;
			$issued_clients[] = $icli;
		}
		////echo (float)$issued."<br>";
		//$leadgen_name = $leadgen_name;
		$totalissuedpremiums += (float) $issued;
		$totalissuedclients++;
	}
}



//KiwiSavers
$kiwisaver_deals = $dealController->GetKiwiSaversFromLeadGeneratorInRange($leadgen_id, $date_from, $until);

//$debug = explode(",", $kiwisaver_deals["client_ids"]);

//Add to issued clients if not already in there.
while ($row = $kiwisaver_deals->fetch_assoc()) {
	$totalissuedks++;
	$totalissuedkspremiums += $row["commission"];
	
	if ($fetchalldata) {
		$icli = new stdClass();
		$icli->id = $row["client_id"];
		$icli->name = $row["name"];
		$icli->adviser = $row["adviser_name"];
		$icli->date_issued = $row["issue_date"];
		$issued_ks[] = $icli;
	}
}

$proficiency = 0;
if ($totalissuedclients != 0 && $totalclients != 0) {
	$issuedLeadsPercent = (($totalissuedclients + $totalissuedks) / $totalclients) * 100;
	$proficiency = $totalissuedpremiums / $totalclients;
}

$proficiency = number_format($proficiency, 2);

$pdf = new PDF('P', 'mm', 'Legal');
$x = $pdf->GetX();
$y = $pdf->GetY();



//Fetch Invoices Data
$invoices_id_list = "";
$invoices_array = array();
$total_leads = 0;
$total_issued = 0;
$total_due = 0;

//page 1
$pdf->AddPage('P', 'Legal');

$pdf->SetFillColor(224, 224, 224);
$pdf->Image('logo.png', 10, 10, -160);
$pdf->SetFont('Helvetica', 'B', 18);
$pdf->SetTextColor(0, 0, 0);
$pdf->Cell(0, 20, '', "0", "1", "C");
$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('Helvetica', 'B', 10);
$pdf->SetFillColor(224, 224, 224);

$pdf->SetFillColor(224, 224, 224);
$pdf->SetFont('Helvetica', 'B', 20);
$pdf->Cell(200, 10, $_POST['type'] . ' Performance Report', "0", "1", "C", 'true');


$pdf->SetFont('Helvetica', 'B', 15);
$pdf->Cell(17, 10, "Name:", "0", "0", "L");
$pdf->SetFont('Helvetica', '', 15);
$pdf->Cell(78, 10, "$leadgen_name", "0", "0", "L");
$pdf->Cell(5, 10, '', "0", "0", "R");
$pdf->SetFont('Helvetica', 'B', 15);
$pdf->Cell(42, 10, "Period Covered:", "0", "0", "L");
$pdf->SetFont('Helvetica', '', 15);
$pdf->Cell(58, 10, "$period_covered_title", "0", "1", "L");


$pdf->SetFont('Helvetica', 'B', 15);
$pdf->Cell(15, 10, "Role:", "0", "0", "L");
$pdf->SetFont('Helvetica', '', 15);
$pdf->Cell(80, 10, "$lead_by", "0", "0", "L");

$pdf->Cell(5, 10, '', "0", "0", "R");
$pdf->SetFont('Helvetica', 'B', 15);
$pdf->Cell(50, 10, "Reference Number:", "0", "0", "L");
$pdf->SetFont('Helvetica', '', 15);
$pdf->Cell(35, 10, "$leadgen_refnum", "0", "1", "L");

$pdf->SetXY($x + 10, $y + 60);


$pdf->SetFillColor(224, 224, 224);
$pdf->SetFont('Helvetica', 'B', 14);

if($lead_by=="Face-to-Face Marketer"){
	include('output3_face_to_face_marketer.php');
}
elseif($lead_by=="Telemarketer"){
	include('output3_telemarketer.php');
}

if ($totalclients != 0 || $totalissuedclients != 0) {

	$grad1 = array(129, 129, 184);
	$grad2 = array(225, 225, 225);

	//set the coordinates x1,y1,x2,y2 of the gradient (see linear_gradient_coords.jpg)
	$coords = array(0, 0, 1, 1);

	//paint a linear gradient
	//$pdf->LinearGradient($x+10,$y+105,200,115,$grad1,$grad2,$coords);


	$pdf->SetFillColor(224, 224, 224);
	$pdf->SetFont('Helvetica', 'B', 14);
	$pdf->Cell(100, 10, 'Statistics', 0, 0, 'L', 'true');
	$pdf->Cell(40, 10, ' ', 0, 0, 'R', 'true');
	$pdf->Cell(60, 10, '', 0, 1, 'R', 'true');

	$leads_required = $required_leads;
	//var_dump($leads_required);

	$data = array('Non-Issued Leads' => $totalclients, 'Issued Leads' => $totalissuedclients);
	//var_dump($data);
	//Pie chart
	$valX = $pdf->GetX();
	$valY = $pdf->GetY();

	$pdf->SetXY($valX, $valY);
	$col1 = array(12, 70, 100);
	$col2 = array(0, 129, 184);

	//$pdf->PieChart(105, 60, $data, '%l(%p)', array($col1,$col2), 1);
	//Bar diagram
	$leads_required_bar = $leads_required[0];
	$pool = "";
	//echo $leads_required_bar;
	switch ($_POST['type']) {
		case "Weekly":
			if ($required_leads_type == "Bi-Monthly") {
				$leads_required_bar /= 2;
			} elseif ($required_leads_type == "Monthly") {
				$leads_required_bar /= 4;
			}

			$pool = "days";
			break;

		case "Bi-Monthly":
			if ($required_leads_type == "Weekly") {
				$leads_required_bar *= 2;
			} elseif ($required_leads_type == "Monthly") {
				$leads_required_bar /= 2;
			}
			$pool = "bi_months";
			break;

		case "Monthly":
			if ($required_leads_type == "Monthly") {
				$leads_required_bar /= count($$pool);
			} elseif ($required_leads_type == "Bi-Monthly") {
				$leads_required_bar *= 2;
			}
			$pool = "weeks";

			$leads_required_bar *= count($$pool);
			break;

		case "Specified":
			if ($required_leads_type == "Monthly") {
				$leads_required_bar /= 4;
			} elseif ($required_leads_type == "Bi-Monthly") {
				$leads_required_bar /= 2;
			}
			$pool = "weeks";

			$leads_required_bar *= count($$pool);
			break;

		case "Annual":
			if ($required_leads_type == "Bi-Monthly") {
				$leads_required_bar /= 15;
			} elseif ($required_leads_type == "Monthly") {
				$leads_required_bar /= 30;
			} elseif ($required_leads_type == "Weekly") {
				$leads_required_bar /= 7;
			}

			$days = explode("-", $period_covered_title);

			$days[0] = str_replace("/", "", $days[0]);
			$days[1] = str_replace("/", "", $days[1]);

			$days[0] = substr($days[0], 4, 4) . "-" . substr($days[0], 2, 2) . "-" . substr($days[0], 0, 2);
			$days[1] = substr($days[1], 4, 4) . "-" . substr($days[1], 2, 2) . "-" . substr($days[1], 0, 2);

			//var_dump($days);
			$d1 = date_create($days[0]);
			$d2 = date_create($days[1]);

			$day_diff = date_diff($d1, $d2);
			$d3 = $day_diff->format('%a');
			$d3++;

			$leads_required_bar *= $d3;
			break;
	}

	$data = array('Leads Required' => $leads_required_bar, 'Leads' => $totalclients);
	//$pdf->BarDiagram(100, 55, $data, '%v (%p)', array($col2,$col1),0,4, "vertical");

	$dash_indexes = array();
	$dash_values = array();
	$leads_required_type = $required_leads_type;
	//LINE GRAPH
	$leads_generated_bi_monthly = array();
	$leads_cancelled_bi_monthly = array();
	/*
	$deals_issued_bi_monthly = array();
	$issued_api_bi_monthly = array();
	$cancellation_api_bi_monthly = array();
	*/
	$issued_and_cancelled_deals_bi_monthly = array();
	$proficiency_bi_monthly = array();
	$cancellation_rate_bi_monthly = array();

	if ($output_bi_monthly) {
		$tier = 1;
		foreach ($leads_required as $lr) {
			$ctr = 1;
			$bm_lr = $lr;
			$pool = "";
			$term = "";

			switch ($_POST['type']) {
				case "Weekly":
					if ($leads_required_type == "Bi-Monthly") {
						$bm_lr /= 14;
					} elseif ($leads_required_type == "Monthly") {
						$bm_lr /= 28;
					} elseif ($leads_required_type == "Weekly") {
						$bm_lr /= 7;
					}

					$pool = "days";
					$term = "D";
					break;
				case "Bi-Monthly":

					if ($leads_required_type == "Monthly") {
						$bm_lr /= 2;
					}
					$pool = "weeks";
					$term = "W";
					break;
				case "Monthly":
					if ($leads_required_type == "Monthly") {
						$bm_lr /= count($weeks);
					} elseif ($leads_required_type == "Bi-Monthly") {
						$bm_lr /= 2;
					}
					$pool = "weeks";
					$term = "W";
					break;
				case "Specified":
					if ($leads_required_type == "Monthly") {
						$bm_lr /= 4;
					} elseif ($leads_required_type == "Bi-Monthly") {
						$bm_lr /= 2;
					}
					$pool = "weeks";
					$term = "W";
					break;
				case "Annual":
					if ($leads_required_type == "Weekly") {
						$bm_lr *= 4;
					} elseif ($leads_required_type == "Bi-Monthly") {
						$bm_lr *= 2;
					}
					$pool = "months";
					$term = "M";
					break;
			}
			$bm_lr = round($bm_lr);

			foreach ($$pool as $bm) {
				$bm_from = $bm->from->format('Ymd');
				$bm_to = $bm->to->format('Ymd');
				//echo "$bm_from - $bm_to <br>";
				$bm_clients_query = "SELECT c.id as client_id from leadgen_tbl l LEFT JOIN clients_tbl c ON c.leadgen = l.id WHERE l.id ='$leadgen_id' AND c.date_submitted <= '$bm_to' AND c.date_submitted >= '$bm_from'";
				$bm_clients_result = mysqli_query($con, $bm_clients_query) or die('Could not look up user information; ' . mysqli_error($con));
				$bm_rowcount = mysqli_num_rows($bm_clients_result);

				$bm_cclients_query = "SELECT c.id as client_id from leadgen_tbl l LEFT JOIN clients_tbl c ON c.leadgen = l.id WHERE l.id ='$leadgen_id' AND c.date_status_updated <= '$bm_to' AND c.date_status_updated >= '$bm_from' AND c.status ='Cancelled'";
				$bm_cclients_result = mysqli_query($con, $bm_cclients_query) or die('Could not look up user information; ' . mysqli_error($con));
				$bm_ccrowcount = mysqli_num_rows($bm_cclients_result);

				
				$actual_leads = $bm_rowcount;
				$canc_leads = $bm_ccrowcount;

				$bm_date_to = "$term$ctr";



				//$bm_date_to = $bm->to->format('d/m');
				//echo "BM Clients Query:<br>" . $bm_clients_query . "<br>";
				$leads_generated_bi_monthly[$bm_date_to] = $actual_leads;
                $leads_cancelled_bi_monthly[$bm_date_to] = $canc_leads;
                
                $bm_issued_and_cancelled_deals = $dealController->getLeadGeneratorIssuedAndCancelledDealsInPeriod($leadgen_id, $bm_from, $bm_to);

				$issued_and_cancelled_deals_bi_monthly[$bm_date_to] = $bm_issued_and_cancelled_deals;
				$proficiency_bi_monthly[$bm_date_to] = GetProficiency($bm_issued_and_cancelled_deals->issued_api, $actual_leads);
				$cancellation_rate_bi_monthly[$bm_date_to] = GetCancellationRate($canc_leads, $actual_leads);


				$dash_indexes[] = $tier + 1;
				$dash_values[$tier + 1] = array(2, 2);
				$leads_required_bi_monthly['Tier ' . $tier][$bm_date_to] = (int) $bm_lr;
				$ctr++;
			}
			$tier++;
		}
	}

	$data = array();

	$colors = array(
		'Leads Cancelled' => array(255, 20, 20),
		'Leads Generated' => $col1
	);

	$tier = 1;
	$ticks = array();
	foreach ($leads_required as $lr) {
		$data['Leads Required T' . $tier] = $leads_required_bi_monthly['Tier ' . $tier];
		$ticks['Leads Required T' . $tier] = 3;
		$colors['Leads Required T' . $tier] = array(255 - ($tier * (175 / count($leads_required))), 0, 0);
		$tier++;
	}

	$data['Leads Generated'] = $leads_generated_bi_monthly;
	$data['Leads Cancelled'] = $leads_cancelled_bi_monthly;

	//var_dump($data);
	//$pdf->LineGraph(180,50,$data,'VHvBdB',$colors,6,3,$dash_indexes,$dash_values);
	//END GRAPHS



	$chart_data = new stdClass();
	$chart_data->bar_chart = new stdClass();
	$chart_data->pie_chart = new stdClass();
	$chart_data->line_chart = new stdClass();
	$chart_data->line_chart2 = new stdClass();
	$chart_data->line_chart3 = new stdClass();

	//Bar Chart
	$chart_data->bar_chart->points_array = array(
		"Leads Required" => array($leads_required_bar),
		"Leads" => array($totalclients)
	);


	$chart_data->bar_chart->xLabels = array("Leads");
	$chart_data->bar_chart->xLabelName = "Leads";


	$chart_data->pie_chart->points_array = array(
		"Leads" => array($totalclients, $totalissuedclients)
	);

	$chart_data->pie_chart->xLabels = array("Non-Issued Leads", "Issued Leads");
	$chart_data->pie_chart->xLabelName = "Leads";

	$chart_data->pie_chart->colors_array = array(
		"Non-Issued Leads" => array(155, 50, 50),
		"Issued Leads" => array(100, 200, 0)
	);

	//Line Chart
	$chart_data->line_chart->points_array = $data;

	$chart_data->line_chart->ticks_array = $ticks;

	$chart_data->line_chart->colors_array = $colors;

	$chart_data->line_chart->axis_names_array = array(
		0 => "Leads"
	);
	$chart_labels = array();
	foreach ($data["Leads Generated"] as $key => $value) {
		$chart_labels[] = $key;
	}

	$chart_data->line_chart->xLabels = $chart_labels;
	$chart_data->line_chart->xLabelName = "Months";

	$data = array(
		'Percentage' => $cancellation_rate_bi_monthly
	);
	//Line Chart 2

	$chart_data->line_chart2->points_array = $data;

	$chart_data->line_chart2->weights_array = array(
		"Percentage" => 1.5
	);

	$chart_data->line_chart2->axis_names_array = array(
		0 => "Percentage"
	);

	$chart_labels = array();
	foreach ($data["Percentage"] as $key => $value) {
		$chart_labels[] = $key;
	}

	$chart_data->line_chart2->xLabels = $chart_labels;
	$chart_data->line_chart2->xLabelName = "Months";


	$data = array(
		'Proficiency' => $proficiency_bi_monthly
	);

	$chart_data->line_chart3->points_array = $data;

	$chart_data->line_chart3->weights_array = array(
		"Proficiency" => 1.5
	);

	$chart_data->line_chart3->axis_names_array = array(
		0 => "Proficiency"
	);

	$chart_labels = array();
	foreach ($data["Proficiency"] as $key => $value) {
		$chart_labels[] = $key;
	}

	$chart_data->line_chart3->xLabels = $chart_labels;
	$chart_data->line_chart3->xLabelName = "Proficiency";


	$filename = "files/adviser_report_deals_" . md5(uniqid()) . ".png";

	$chartHelper->GenerateCustomMixedChartForLeadGeneratorReport($chart_data, $filename);
	$pdf->Ln(3);
	$pdf->Image($filename, $pdf->GetX(), $pdf->GetY(), 200, 200);

	//BI MONTHLY REPORT
	$pdf->AddPage(); // position of text1, numerical, of course, not x1 and y1
}

$pdf->SetFont('Helvetica', 'B', 14);
$pdf->SetFillColor(224, 224, 224);
$performance_title = "";
$period_title = "";
switch ($_POST["type"]) {
	case "Weekly":
		$pool = "days";
		$performance_title = "Daily";
		$period_title = "Date";
		break;
	case "Bi-Monthly":
		$pool = "weeks";
		$performance_title = "Weekly";
		$period_title = "Period";
		break;
	case "Monthly":
		$pool = "weeks";
		$performance_title = "Weekly";
		$period_title = "Period";
		break;
	case "Specified":
		$pool = "weeks";
		$performance_title = "Weekly";
		$period_title = "Week";
		break;
	case "Annual":
		$pool = "months";
		$performance_title = "Monthly";
		$period_title = "Month";
		break;
}

$pdf->Cell(200, 10, "$performance_title Performance", "0", "1", "C", 'true');

//Headers

$pdf->SetMCFonts(array(
	array('Helvetica', 'U', 13),
	array('Helvetica', 'U', 13)
));

$pdf->setWidths(array(10, 25, 25, 25, 30, 25, 30, 30));
$pdf->setAligns(array("C", "C", "C"));
$pdf->Row(array("", "Leads Generated", "Leads Cancelled", "Leads Issued", "API Generated", "Issued Leads %", "Proficiency", "Cancellation Rate"), false, array(242, 242, 242));

$pdf->SetMCFonts(array(
	array('Helvetica', 'B', 10),
	array('Helvetica', '', 10)
));

$period_array = array_keys($leads_generated_bi_monthly);

$totals = new stdClass();
$totals->leads_generated = 0;
$totals->leads_cancelled = 0;
$totals->leads_issued = 0;
$totals->api_generated = 0;
$totals->issued_api = 0;
$totals->cancellation_api = 0;

foreach ($period_array as $index => $period) {
	$fill = (($index % 2) === 0) ? true : false;

	$totals->leads_generated += $leads_generated_bi_monthly[$period];
	$totals->leads_cancelled += $leads_cancelled_bi_monthly[$period];
	//echo "Total Leads Issued: $totals->leads_issued on $period <hr>";
	//var_dump($issued_and_cancelled_deals_bi_monthly[$period]->issued_deals);
	$totals->leads_issued +=  count($issued_and_cancelled_deals_bi_monthly[$period]->issued_deals);
	$totals->api_generated += $issued_and_cancelled_deals_bi_monthly[$period]->issued_api;

	$totals->issued_api += $issued_and_cancelled_deals_bi_monthly[$period]->issued_api;
	$totals->cancellation_api += $issued_and_cancelled_deals_bi_monthly[$period]->cancellation_api;

	$pdf->Row(array($period, $leads_generated_bi_monthly[$period], $leads_cancelled_bi_monthly[$period], count($issued_and_cancelled_deals_bi_monthly[$period]->issued_deals), Currency($issued_and_cancelled_deals_bi_monthly[$period]->issued_api), GetPercentage(count($issued_and_cancelled_deals_bi_monthly[$period]->issued_deals), $leads_generated_bi_monthly[$period])  . "%", Currency($proficiency_bi_monthly[$period]),  $cancellation_rate_bi_monthly[$period] . "%"), $fill, array(242, 242, 242));
}

$totals->issued_leads_percent = GetPercentage($totals->leads_issued, $totals->leads_generated);
$totals->proficiency = GetProficiency($totals->api_generated, $totals->leads_generated);
$totals->cancellation_rate = GetCancellationRate($totals->leads_cancelled, $totals->leads_generated);

//var_dump($totals);

$pdf->SetDrawColor(0, 0, 0);
$pdf->SetFont('Helvetica', 'U', 10);
$pdf->Cell(10, 8, "T", "T", 0, 'C');
$pdf->Cell(25, 8, $totals->leads_generated, "T", 0, 'C');
$pdf->Cell(25, 8, $totals->leads_cancelled, "T", 0, 'C');
$pdf->Cell(25, 8, $totals->leads_issued, "T", 0, 'C');
$pdf->Cell(30, 8, Currency($totals->api_generated), "T", 0, 'C');
$pdf->Cell(25, 8, $totals->issued_leads_percent . "%", "T", 0, 'C');
$pdf->Cell(30, 8, Currency($totals->proficiency), "T", 0, 'C');
$pdf->Cell(30, 8, $totals->cancellation_rate . "%", "T", 1, 'C');

/*
NOTE:
	Add KiwiSaver Premiums to API Generated and Leads Issued
*/

//echo "<br><br><br><br><br>";
//echo "<pre>" , var_dump($bi_months) , "</pre>";
//echo "<br><br><br><br><br>";

$pdf->Ln(10);

$performance_data = $dealController->getLeadGeneratorIssuedSubmittedAndCancelledDealsInPeriod($leadgen_id, $date_from, $until);

$kiwisavers_data = $dealController->GetKiwiSaversFromLeadGeneratorInRange($leadgen_id, $date_from, $until);

//Insurance Issued Policies
if(count($performance_data->issued_deals) > 0){
	$pdf->SetFont('Helvetica', 'B', 14);
	$pdf->SetFillColor(224, 224, 224);
	$pdf->Cell(200, 10, 'Insurance Issued Policies', "0", "1", "C", 'true');

	$pdf->SetMCFonts(array(
		array('Helvetica', 'U', 13),
		array('Helvetica', 'U', 13)
	));

	$pdf->setWidths(array(10, 45, 35, 45, 35, 35));
	$pdf->setAligns(array("R", "L", "C"));
	$pdf->Row(array("No.", "Name", "Policy Number", "Adviser", "Date Issued", "API"), false, array(242, 242, 242));

	$pdf->SetMCFonts(array(
		array('Helvetica', 'B', 12),
		array('Helvetica', '', 12)
	));

	foreach ($performance_data->issued_deals as $ctr => $deal) {
		$fill = (($ctr % 2) === 0) ? true : false;
		$pdf->Row(array(($ctr + 1) . ".", $deal->name, $deal->policy_number, $deal->adviser_name, date("d/m/Y", strtotime($deal->date_issued)), "$" . number_format($deal->issued_api)), $fill, array(242, 242, 242));
	}

	$pdf->Ln(10);
}

if(count($performance_data->submissions) > 0){
	//Insurance Submitted Policies
	$pdf->SetFont('Helvetica', 'B', 14);
	$pdf->SetFillColor(224, 224, 224);
	$pdf->Cell(200, 10, 'Insurance Submissions', "0", "1", "C", 'true');

	$pdf->SetMCFonts(array(
		array('Helvetica', 'U', 13),
		array('Helvetica', 'U', 13)
	));

	$pdf->setWidths(array(10, 45, 35, 45, 35, 35));
	$pdf->setAligns(array("R", "L", "C"));
	$pdf->Row(array("No.", "Name", "Policy Number", "Adviser", "Date Issued", "API"), false, array(242, 242, 242));

	$pdf->SetMCFonts(array(
		array('Helvetica', 'B', 12),
		array('Helvetica', '', 12)
	));

	foreach ($performance_data->submissions as $ctr => $deal) {
		$fill = (($ctr % 2) === 0) ? true : false;
		$pdf->Row(array(($ctr + 1) . ".", $deal->name, $deal->policy_number, $deal->adviser_name, date("d/m/Y", strtotime($deal->submission_date)), "$" . number_format($deal->original_api)), $fill, array(242, 242, 242));
	}

	$pdf->Ln(10);

}

if(count($performance_data->cancelled_deals) > 0){
	//Insurance Issued Policies
	$pdf->SetFont('Helvetica', 'B', 14);
	$pdf->SetFillColor(224, 224, 224);
	$pdf->Cell(200, 10, 'Insurance Cancellations', "0", "1", "C", 'true');

	$pdf->SetMCFonts(array(
		array('Helvetica', 'U', 13),
		array('Helvetica', 'U', 13)
	));

	$pdf->setWidths(array(10, 45, 35, 45, 35, 35));
	$pdf->setAligns(array("R", "L", "C"));
	$pdf->Row(array("No.", "Name", "Policy Number", "Adviser", "Date Issued", "API"), false, array(242, 242, 242));

	$pdf->SetMCFonts(array(
		array('Helvetica', 'B', 12),
		array('Helvetica', '', 12)
	));

	foreach ($performance_data->cancelled_deals as $ctr => $deal) {
		$fill = (($ctr % 2) === 0) ? true : false;
		$pdf->Row(array(($ctr + 1) . ".", $deal->name, $deal->policy_number, $deal->adviser_name, date("d/m/Y", strtotime($deal->clawback_date)), "$" . number_format($deal->clawback_api)), $fill, array(242, 242, 242));
	}

	$pdf->Ln(10);
}


if($kiwisavers_data->num_rows > 0){
	//Insurance Issued Policies
	$pdf->SetFont('Helvetica', 'B', 14);
	$pdf->SetFillColor(224, 224, 224);
	$pdf->Cell(200, 10, 'KiwiSavers Issued', "0", "1", "C", 'true');

	$pdf->SetMCFonts(array(
		array('Helvetica', 'U', 13),
		array('Helvetica', 'U', 13)
	));

	$pdf->setWidths(array(10, 80, 35, 75));
	$pdf->setAligns(array("R", "L", "C"));
	$pdf->Row(array("No.", "Name", "Date", "Adviser"), false, array(242, 242, 242));

	$pdf->SetMCFonts(array(
		array('Helvetica', 'B', 12),
		array('Helvetica', '', 12)
	));

	$ctr = 0;

	while ($rows = $kiwisavers_data->fetch_assoc()) {
		$fill = (($ctr % 2) === 0) ? true : false;
		$pdf->Row(array(($ctr + 1) . ".", $rows["name"], date("d/m/Y", strtotime($rows["issue_date"])), $rows["adviser_name"]), $fill, array(242, 242, 242));
		$ctr++;
	}

	$pdf->Ln(10);
}
//Weekly
$pdf->SetFont('Helvetica', 'B', 14);
$pdf->SetFillColor(224, 224, 224);
$pdf->Cell(200, 10, 'Leads Generated', "0", "1", "C", 'true');

//Headers
$pdf->SetMCFonts(array(
	array('Helvetica', 'U', 13),
	array('Helvetica', 'U', 13)
));

$pdf->setWidths(array(15, 150, 35));
$pdf->setAligns(array("R", "L", "C"));
$pdf->Row(array("No.", "Name", "Date Generated"), false, array(242, 242, 242));

$pdf->SetMCFonts(array(
	array('Helvetica', 'B', 12),
	array('Helvetica', '', 12)
));

$leads_generated = $clientController->getClientsGeneratedByLeadGeneratorInRange($leadgen_id, $date_from, $until);
foreach ($leads_generated as $ctr => $lead) {
	$fill = (($ctr % 2) === 0) ? true : false;
	$pdf->Row(array(($ctr + 1) . ".", $lead["name"], date("d/m/Y", strtotime($lead["date_submitted"]))), $fill, array(242, 242, 242));
}

$dnow_ = date('d/m/Y');
$invoice_date_final = substr($dnow_, 6, 4) . substr($dnow_, 3, 2) . substr($dnow_, 0, 2);
