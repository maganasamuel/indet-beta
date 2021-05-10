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
		$totalclients++;
	}
}

//Clients Only Query
$cclients_query = "SELECT *,l.name as leadgen_name, c.name as client_name, c.id as client_id from leadgen_tbl l LEFT JOIN clients_tbl c ON c.leadgen = l.id WHERE l.id ='$leadgen_id' AND c.date_status_updated <= '$until' AND c.date_status_updated >= '$date_from' AND status='Cancelled'";
$cclients_result = mysqli_query($con, $cclients_query) or die('Could not look up user information; ' . mysqli_error($con));
$crowcount = mysqli_num_rows($cclients_result);
$cclients = [];
$totalsubmissions = 0;

$totalsubmissionamount = 0;
//echo "Clients Query:<br>". $clients_query . "<br>";
if($rowcount==0){
	//print "No Records Found";
}
else{	

	while($row  = mysqli_fetch_assoc($clients_result)){
		//if all data is necessary;
			extract($row);	
			$cli = new stdClass();
			$cli->id = $client_id;
			$cli->name = $client_name;
			$cli->adviser = $assigned_to;
			$cli->date_submitted = $date_submitted;
			$cli->submissions = $submission;
			$cli->submission_amount = $submission_amount;
			$clients[] = $cli;
			if($submission){
				$totalsubmissions++;
				$totalsubmissionamount+= $submission_amount;
			}
		

		$totalclients++;
	}
}

//CClients Only Query
$cclients_query = "SELECT *,l.name as leadgen_name, c.name as client_name, c.id as client_id from leadgen_tbl l LEFT JOIN clients_tbl c ON c.leadgen = l.id WHERE l.id ='$leadgen_id' AND c.date_status_updated <= '$until' AND c.date_status_updated >= '$date_from' AND status='Cancelled'";
$cclients_result = mysqli_query($con,$cclients_query) or die('Could not look up user information; ' . mysqli_error($con));
$crowcount = mysqli_num_rows($cclients_result);
$cclients = [];
$totalcclients = 0;
//echo "Clients Query:<br>". $cclients_query . "<br>";
if($crowcount==0){
	//print "No Records Found";
}
else{	
	while($row  = mysqli_fetch_assoc($cclients_result)){
		//if all data is necessary;
		if($fetchalldata){
			extract($row);		
			$cli = new stdClass();
			$cli->id = $client_id;
			$cli->name = $client_name;
			$cli->adviser = $assigned_to;
			$cli->date_submitted = $date_submitted;
			$cclients[] = $cli;
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

$date_from = substr($date_from, 6, 4) . substr($date_from, 3, 2) . substr($date_from, 0, 2);
$until = substr($until, 6, 4) . substr($until, 3, 2) . substr($until, 0, 2);
////echo $date_from . "<br>" . $until . "<br>";


//Fetch Invoices Data
$invoices_id_list = "";
$invoices_array = array();
$total_leads = 0;
$total_issued = 0;
$total_due = 0;
//Convert Date
function convertToFourDigits($num = 0)
{
	$op = "";
	if ($num < 10) {
		$op = "000" . $num;
	} elseif ($num < 100) {
		$op = "00" . $num;
	} elseif ($num < 1000) {
		$op = "0" . $num;
	} elseif ($num < 10000) {
		$op = "" . $num;
	}
	return $op;
}


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
$pdf->Cell(200, 10, 'Quarterly Performance Report', "0", "1", "C", 'true');


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


if ($lead_by == "Face-to-Face Marketer") {
    include('leadgen_report_face_to_face_marketer_quarterly.php');
} elseif ($lead_by == "Telemarketer") {
    include('leadgen_report_telemarketer_quarterly.php');
}

$pdf->Ln(3);

//$pdf->Cell(55,10,'$'.desc_val($desc[0]), 0, 1,'R');


if ($totalclients != 0 || $totalissuedclients != 0) {

	//GRAPHS
	$grad1 = array(129, 129, 184);
	$grad2 = array(225, 225, 225);

	//set the coordinates x1,y1,x2,y2 of the gradient (see linear_gradient_coords.jpg)
	$coords = array(0, 0, 1, 1);

	//paint a linear gradient
	$pdf->SetFillColor(224, 224, 224);
	$pdf->SetFont('Helvetica', 'B', 14);
	$pdf->Cell(200, 10, 'Statistics', 0, 1, 'C', 'true');

	$leads_required = json_decode($leads_required, true);

	//var_dump($leads_required);
	$data = array('Non-Issued Leads' => $totalclients, 'Issued Leads' => $totalissuedclients);

	//Pie chart
	$valX = $pdf->GetX();
	$valY = $pdf->GetY();

	$col1 = array(12, 70, 100);
	$col2 = array(0, 129, 184);
	//var_dump($data);
	//Bar diagram
	$leads_required_bar = $leads_required[0];
	switch ($leads_required_type) {
		case "Weekly":
			$leads_required_bar *= 12;
			break;
		case "Bi-Monthly":
			$leads_required_bar *= 6;
			break;
		case "Monthly":
			$leads_required_bar *= 3;
			break;
	}

	$data = array('Leads Required' => $leads_required_bar, 'Leads' => $totalclients);

	$dash_indexes = array();
	$dash_values = array();
	//LINE GRAPH
	$month_ctr = 0;

	$leads_generated_bi_monthly = array();
	$leads_cancelled_bi_monthly = array();

	$issued_and_cancelled_deals_bi_monthly = array();
	$proficiency_bi_monthly = array();
	$cancellation_rate_bi_monthly = array();

	if ($output_bi_monthly) {

		$tier = 1;
		foreach ($leads_required as $lr) {
			$bm_lr = $lr;
			if ($leads_required_type == "Weekly") {
				$bm_lr *= 4;
			} elseif ($leads_required_type == "Bi-Monthly") {
				$bm_lr *= 2;
			}

			$week_ctr1 = 1;
			foreach ($months as $bm) {
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

				//$bm_date_to = $bm->from->format('F, Y');
				//$bm_date_to = "W". ($week_offset + $week_ctr1);
				$bm_date_to = "M" . $week_ctr1;

				$leads_generated_bi_monthly[$bm_date_to] = $actual_leads;
				$leads_cancelled_bi_monthly[$bm_date_to] = $canc_leads;

				$bm_issued_and_cancelled_deals = $dealController->getLeadGeneratorIssuedAndCancelledDealsInPeriod($leadgen_id, $bm_from, $bm_to);

				$issued_and_cancelled_deals_bi_monthly[$bm_date_to] = $bm_issued_and_cancelled_deals;
				$proficiency_bi_monthly[$bm_date_to] = GetProficiency($bm_issued_and_cancelled_deals->issued_api, $actual_leads);
				$cancellation_rate_bi_monthly[$bm_date_to] = GetCancellationRate($canc_leads, $actual_leads);

				$dash_indexes[] = $tier + 1;
				$dash_values[$tier + 1] = array(2, 2);
				$leads_required_bi_monthly['Tier ' . $tier][$bm_date_to] = (int) $bm_lr;
				$week_ctr1++;
			}

			$tier++;
		}
	}


	$data = array(
		'Leads Cancelled' => $leads_cancelled_bi_monthly,
		'Leads Generated' => $leads_generated_bi_monthly
	);

	$colors = array(
		'Leads Cancelled' => array(255, 20, 20),
		'Leads Generated' => $col1,
		'Leads Required T' => array(255, 0, 0)
	);

	$tier = 1;

	$ticks = array();
	foreach ($leads_required as $lr) {
		$data['Leads Required T' . $tier] = $leads_required_bi_monthly['Tier ' . $tier];
		$ticks['Leads Required T' . $tier][] = 4;
		$colors['Leads Required T' . $tier] = array(255 - ($tier * (175 / count($leads_required))), 0, 0);
		$tier++;
	}


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

//Weekly
$pdf->SetFont('Helvetica', 'B', 14);
$pdf->SetFillColor(224, 224, 224);
$pdf->Cell(200, 10, 'Monthly Performance', "0", "1", "C", 'true');

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

	$totals->leads_generated += $leads_generated_bi_monthly[$period];
	$totals->leads_cancelled += $leads_cancelled_bi_monthly[$period];
	$totals->leads_issued +=  count($issued_and_cancelled_deals_bi_monthly[$period]->issued_deals);
	$totals->api_generated += $issued_and_cancelled_deals_bi_monthly[$period]->issued_api;

	$totals->issued_api += $issued_and_cancelled_deals_bi_monthly[$period]->issued_api;
	$totals->cancellation_api += $issued_and_cancelled_deals_bi_monthly[$period]->cancellation_api;
	$fill = (($index % 2) === 0) ? true : false;

	$pdf->Row(array($period, $leads_generated_bi_monthly[$period], $leads_cancelled_bi_monthly[$period], count($issued_and_cancelled_deals_bi_monthly[$period]->issued_deals), Currency($issued_and_cancelled_deals_bi_monthly[$period]->issued_api), GetPercentage(count($issued_and_cancelled_deals_bi_monthly[$period]->issued_deals), $leads_generated_bi_monthly[$period])  . "%", Currency($proficiency_bi_monthly[$period]),  $cancellation_rate_bi_monthly[$period] . "%"), $fill, array(242, 242, 242));
}

$totals->issued_leads_percent = GetPercentage($totals->leads_issued, $totals->leads_generated);
$totals->proficiency = GetProficiency($totals->api_generated, $totals->leads_generated);
$totals->cancellation_rate = GetCancellationRate($totals->leads_cancelled, $totals->leads_generated);

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
$pdf->Ln();

//Weekly
$pdf->SetFont('Helvetica', 'B', 14);
$pdf->SetFillColor(224, 224, 224);
$pdf->Cell(200, 10, 'Weekly Performance', "0", "1", "C", 'true');
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
//echo "<br><br><br><br><br>";
//echo "<pre>" , var_dump($bi_months) , "</pre>";
//echo "<br><br><br><br><br>";


$pdf->SetMCFonts(array(
	array('Helvetica', 'B', 10),
	array('Helvetica', '', 10)
));

$total_leads = 0;
$total_cancelled = 0;
$total_issued = 0;
$total_api_generated = 0;
$total_issued_percentage = 0;
$week_ctr = 1;
$ctr = 0;
if ($output_bi_monthly) {
	foreach ($bi_months as $bm) {

		$bm_from = $bm->from->format('Ymd');
		$bm_to = $bm->to->format('Ymd');
		//echo "$bm_from - $bm_to <br>";
		$bm_clients_query = "SELECT *,l.name as leadgen_name, c.name as client_name, c.id as client_id from leadgen_tbl l LEFT JOIN clients_tbl c ON c.leadgen = l.id WHERE l.id ='$leadgen_id' AND c.date_submitted <= '$bm_to' AND c.date_submitted >= '$bm_from'";
		$bm_clients_result = mysqli_query($con, $bm_clients_query) or die('Could not look up user information; ' . mysqli_error($con));
		$bm_rowcount = mysqli_num_rows($bm_clients_result);
		$bm_totalclients = 0;
		//echo "BM Clients Query:<br>" . $bm_clients_query . "<br>";
		if ($bm_rowcount == 0) {
			//print "No Records Found";
		} else {
			while ($bm_row  = mysqli_fetch_assoc($bm_clients_result)) {
				$bm_totalclients++;
			}
		}

		//Issued Clients Only Query
		$bm_issued_clients_query = "SELECT *,l.name as leadgen_name, i.name as issued_client_name, i.id as issued_client_id from leadgen_tbl l LEFT JOIN issued_clients_tbl i ON i.leadgen = l.id WHERE l.id ='$leadgen_id' AND i.date_issued <= '$bm_to' AND i.date_issued >= '$bm_from'";
		$bm_issued_clients_result = mysqli_query($con, $bm_issued_clients_query) or die('Could not look up user information; ' . mysqli_error($con));
		$bm_rowcount = mysqli_num_rows($bm_issued_clients_result);
		$bm_client_ids = [];
		$bm_totalissuedclients = 0;
		$bm_totalissuedpremiums = 0;
		$bm_issuedLeadsPercent = 0;
		//echo "BM Issued Clients Query:<br>" . $bm_issued_clients_query . "<br>";
		if ($bm_rowcount == 0) {
			//print "No Records Found";
		} else {
			while ($row  = mysqli_fetch_assoc($bm_issued_clients_result)) {
				extract($row);
				//echo $issued;
				$bm_totalissuedpremiums += (float) $issued;
				$bm_totalissuedclients++;
				$bm_client_ids[] = $issued_client_name;
			}
			$bm_issuedLeadsPercent = 0;
			if ($bm_totalissuedclients > 0 && $bm_totalclients > 0)
				$bm_issuedLeadsPercent = ($bm_totalissuedclients / $bm_totalclients) * 100;
		}

		//KiwiSavers
		$kiwisaver_deals = $dealController->GetKiwiSaverTotalsFromLeadGeneratorInRange($leadgen_id, $bm_from, $bm_to);
		$kiwisaver_clients = explode(",", $kiwisaver_deals["client_ids"]);


		$bm_totalissuedpremiums += $kiwisaver_deals["total_commission"];

		//Add to issued clients if not already in there.
		foreach ($kiwisaver_clients as $kiwi_client) {
			if (!empty($kiwi_client))
				if (!in_array($kiwi_client, $bm_client_ids))
					$bm_totalissuedclients++;
		}

		//Cancelled Clients Only Query
		$bm_cancelled_clients_query = "SELECT *,l.name as leadgen_name, c.name as client_name, c.id as client_id from leadgen_tbl l LEFT JOIN clients_tbl c ON c.leadgen = l.id WHERE l.id ='$leadgen_id' AND c.date_status_updated <= '$bm_to' AND c.date_status_updated >= '$bm_from' AND c.status='Cancelled' ";
		$bm_cancelled_clients_result = mysqli_query($con, $bm_cancelled_clients_query) or die('Could not look up user information; ' . mysqli_error($con));
		$bm_cancelled_rowcount = mysqli_num_rows($bm_cancelled_clients_result);
		$bm_total_cancelled_clients = 0;
		//echo "BM Clients Query:<br>" . $bm_clients_query . "<br>";
		if ($bm_cancelled_rowcount == 0) {
			//print "No Records Found";
		} else {
			while ($bm_cancelled_row  = mysqli_fetch_assoc($bm_cancelled_clients_result)) {
				$bm_total_cancelled_clients++;
			}
		}

		//fetch
		$total_leads += $bm_totalclients;
		$total_cancelled += $bm_total_cancelled_clients;
		$total_issued += $bm_totalissuedclients;
		$total_api_generated += $bm_totalissuedpremiums;

		$fill = (($ctr % 2) === 0) ? true : false;

		$pdf->Row(array("W" . ($week_offset + $week_ctr), $bm_totalclients, $bm_total_cancelled_clients, $bm_totalissuedclients, "$" . number_format($bm_totalissuedpremiums, 2), GetPercentage($bm_totalissuedclients, $bm_totalclients)  . "%", Currency(GetProficiency($bm_totalissuedpremiums, $bm_totalclients)),  GetCancellationRate($bm_total_cancelled_clients, $bm_totalclients) . "%"), $fill, array(242, 242, 242));

		$ctr++;
		$week_ctr++;
	}
}
$issuedpercent = ($total_issued != 00 || ($total_leads - $total_cancelled) != 0) ? ($total_issued / ($total_leads - $total_cancelled)) * 100 : 0;

$pdf->SetDrawColor(0, 0, 0);
$pdf->SetFont('Helvetica', 'U', 10);
$pdf->Cell(10, 8, "T", "T", 0, 'C');
$pdf->Cell(25, 10, $total_leads, 'T', 0, 'C');
$pdf->Cell(25, 10, $total_cancelled, 'T', 0, 'C');
$pdf->Cell(25, 10, $total_issued, 'T', 0, 'C');
$pdf->Cell(30, 10, "$" . number_format($total_api_generated, 2), 'T', 0, 'C');
$pdf->Cell(25, 10, number_format($issuedLeadsPercent, 2) . "%", 'T', 0, 'C');
$pdf->Cell(30, 10, "$" . GetProficiency($bm_totalissuedpremiums, $total_leads), 'T', 0, 'C');
$pdf->Cell(30, 10, GetCancellationRate($total_cancelled, $total_leads) . "%", 'T', 1, 'C');

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



$pdf->Ln(10);
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
