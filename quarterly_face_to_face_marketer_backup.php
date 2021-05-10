<?php
//Clients Only Query
$clients_query = "SELECT *,l.name as leadgen_name, c.name as client_name, c.id as client_id from leadgen_tbl l LEFT JOIN clients_tbl c ON c.leadgen = l.id WHERE l.id ='$leadgen_id' AND c.date_submitted <= '$until' AND c.date_submitted >= '$date_from'";
$clients_result = mysqli_query($con,$clients_query) or die('Could not look up user information; ' . mysqli_error($con));
$rowcount = mysqli_num_rows($clients_result);
$clients = [];
$totalclients = 0;
//echo "Clients Query:<br>". $clients_query . "<br>";
if($rowcount==0){
	//print "No Records Found";
}
else{	
	while($row  = mysqli_fetch_assoc($clients_result)){
		//if all data is necessary;
		if($fetchalldata){
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
$issued_clients_result = mysqli_query($con,$issued_clients_query) or die('Could not look up user information; ' . mysqli_error($con));
$rowcount = mysqli_num_rows($issued_clients_result);
$issued_clients = [];
$totalissuedclients = 0;
$totalissuedpremiums = 0;
$issuedLeadsPercent = 0;
//echo $issued_clients_query . "<br>";
if($rowcount==0){
	//print "No Records Found";
}
else{	
	while($row  = mysqli_fetch_assoc($issued_clients_result)){
		extract($row);	
		//if all data is necessary;
		if($fetchalldata){
			$icli = new stdClass();
			$icli->id = $client_id;
			$icli->name = $client_name;
			$icli->adviser = $assigned_to;
			$icli->date_submitted = $date_submitted;
			$issued_clients[] = $icli;
		}
		////echo (float)$issued."<br>";
		//$leadgen_name = $leadgen_name;
		$totalissuedpremiums += (float)$issued;
		$totalissuedclients++;
	}
	
	if($totalissuedclients!=0 && $totalclients!=0)
	$issuedLeadsPercent = ($totalissuedclients / $totalclients) * 100;
}




$pdf = new PDF_With_Graph();
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

$pdf->SetFillColor(224,224,224);
$pdf->Image('logo.png',10,10,-160);
$pdf->SetFont('Helvetica','B',18);
$pdf->SetTextColor(0,0,0);
$pdf->Cell(0,20,'',"0","1","C");
$pdf->SetTextColor(0,0,0);
$pdf->SetFont('Helvetica','B',10);
$pdf->SetFillColor(224,224,224);

$pdf->SetFillColor(224,224,224);
$pdf->SetFont('Helvetica','B',20);
$pdf->Cell(200,10,'Quarterly Performance Report',"0","1","C",'true');



$pdf->SetFont('Helvetica','B',15);
$pdf->Cell(17,10,"Name:","0","0","L");
$pdf->SetFont('Helvetica','',15);
$pdf->Cell(78,10,"$leadgen_name","0","0","L");
$pdf->Cell(5,10,'',"0","0","R");
$pdf->SetFont('Helvetica','B',15);
$pdf->Cell(42,10,"Period Covered:","0","0","L");
$pdf->SetFont('Helvetica','',15);
$pdf->Cell(58,10,"$period_covered_title","0","1","L");


$pdf->SetFont('Helvetica','B',15);
$pdf->Cell(15,10,"Role:","0","0","L");
$pdf->SetFont('Helvetica','',15);
$pdf->Cell(80,10,"$lead_by","0","0","L");

$pdf->Cell(5,10,'',"0","0","R");
$pdf->SetFont('Helvetica','B',15);
$pdf->Cell(50,10,"Reference Number:","0","0","L");
$pdf->SetFont('Helvetica','',15);
$pdf->Cell(35,10,"$leadgen_refnum","0","1","L");

$pdf->SetXY($x+10, $y+60); 


$pdf->SetFillColor(224,224,224);
$pdf->SetFont('Helvetica','B',14);
$pdf->Cell(200,10,'Overall Performance', 0, 1,'C','true');
$pdf->SetFont('Helvetica','B',12);

$pdf->Cell(40,10,'Leads Generated', 0, 0,'C');
$pdf->Cell(40,10,'Leads Cancelled', 0, 0,'C');
$pdf->Cell(40,10,'Leads Issued', 0, 0,'C');
$pdf->Cell(40,10,'API Generated', 0, 0,'C');
$pdf->Cell(40,10,'Issued Leads %', 0, 1,'C');
$pdf->SetFont('Helvetica','',12);
$pdf->Cell(40,10,$totalclients, 0, 0,'C');
$pdf->Cell(40,10,$totalcclients, 0, 0,'C');
$pdf->Cell(40,10,$totalissuedclients, 0, 0,'C');
$pdf->Cell(40,10,"$" . number_format($totalissuedpremiums,2), 0, 0,'C');
$pdf->Cell(40,10,number_format($issuedLeadsPercent,2) . "%", 0, 1,'C');
//$pdf->Cell(55,10,'$'.desc_val($desc[0]), 0, 1,'R');



//GRAPHS
$pdf->SetXY($x+10, $y+93); 

if($totalclients!=0 || $totalissuedclients!=0){

$grad1=array(129,129,184);
$grad2=array(225,225,225);

//set the coordinates x1,y1,x2,y2 of the gradient (see linear_gradient_coords.jpg)
$coords=array(0, 0,1,1);

//paint a linear gradient
$pdf->LinearGradient($x+10,$y+105,200,118,$grad1,$grad2,$coords);

$pdf->SetFillColor(224,224,224);
$pdf->SetFont('Helvetica','B',14);
$pdf->Cell(100,10,'Statistics', 0, 0,'L','true');
$pdf->Cell(40,10,' ', 0, 0,'R','true');
$pdf->Cell(60,10,'', 0, 1,'R','true');

$leads_required = $required_leads;
//var_dump($leads_required);

$data = array('Clients' => $totalclients, 'Issued Clients' => $totalissuedclients);
//var_dump($data);
//Pie chart
$valX = $pdf->GetX();
$valY = $pdf->GetY();

$pdf->SetXY($valX, $valY+5);
$col1=array(12,70,100);
$col2=array(0,129,184);

$pdf->PieChart(105, 60, $data, '%l(%p)', array($col1,$col2), 1);
//Bar diagram

$leads_required_bar = $leads_required[0];

	switch($required_leads_type){
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

$pdf->SetXY($valX + 100, $valY +5);
$pdf->BarDiagram(100, 60, $data, '%v (%p)', array($col2,$col1),0,4, "vertical");
$pdf->SetXY($x+10, $y+170);

$dash_indexes= array();
$dash_values = array();
$leads_required_type = $required_leads_type;
//LINE GRAPH
$month_ctr = 0;
$leads_generated_bi_monthly = array();
if($output_bi_monthly){

	$tier = 1;
	foreach($leads_required as $lr){	
		$bm_lr = $lr;
		if($leads_required_type=="Weekly"){
			$bm_lr *= 12;
		}
		elseif($leads_required_type=="Bi-Monthly"){
			$bm_lr *= 6;
		}
		elseif($leads_required_type=="Monthly"){
			$bm_lr *= 3;
		}

		foreach($months as $bm){
			$bm_from = $bm->from->format('Ymd');
			$bm_to = $bm->to->format('Ymd');
			//echo "$bm_from - $bm_to <br>";
			$bm_clients_query = "SELECT c.id as client_id from leadgen_tbl l LEFT JOIN clients_tbl c ON c.leadgen = l.id WHERE l.id ='$leadgen_id' AND c.date_submitted <= '$bm_to' AND c.date_submitted >= '$bm_from'";
			$bm_clients_result = mysqli_query($con,$bm_clients_query) or die('Could not look up user information; ' . mysqli_error($con));
			$bm_rowcount = mysqli_num_rows($bm_clients_result);

			$bm_cclients_query = "SELECT c.id as client_id from leadgen_tbl l LEFT JOIN clients_tbl c ON c.leadgen = l.id WHERE l.id ='$leadgen_id' AND c.date_status_updated <= '$bm_to' AND c.date_status_updated >= '$bm_from' AND c.status ='Cancelled'";
			$bm_cclients_result = mysqli_query($con,$bm_cclients_query) or die('Could not look up user information; ' . mysqli_error($con));
			$bm_ccrowcount = mysqli_num_rows($bm_cclients_result);

			$actual_leads = $bm_rowcount - $bm_ccrowcount;
			//$bm_date_to = $bm->from->format('F, Y');
			$bm_date_to = "M " . $bm->month_index;
			$leads_generated_bi_monthly[$bm_date_to] = $actual_leads;
			$dash_indexes[] = $tier;
			$dash_values[$tier] = array(2,2);
			$leads_required_bi_monthly['Tier ' . $tier][$bm_date_to]= (int)$bm_lr;
		}	

		$tier++;
	}
}



$data = array(
    'Leads' => $leads_generated_bi_monthly,
);
	


$colors = array(
    'Leads' => $col1,
    'Leads Required T' => array(255,0,0),
);
$tier = 1;
	foreach($leads_required as $lr){			
		$data['Leads Required T' . $tier] = $leads_required_bi_monthly['Tier ' . $tier];	
		$colors['Leads Required T' . $tier] = array(255 - ($tier * (175 / count($leads_required))), 0, 0);	
		$tier++;
	}

//var_dump($data);
$pdf->LineGraph(180,50,$data,'VHvBdB',$colors,6,3,$dash_indexes,$dash_values);
//END GRAPHS


//BI MONTHLY REPORT
$pdf->SetXY($x+10, $y+225); // position of text1, numerical, of course, not x1 and y1
}
$pdf->SetFont('Helvetica','B',14);
$pdf->SetFillColor(224,224,224);
$pdf->Cell(100,10,'Monthly Performance',"0","0","L",'true');
$pdf->Cell(40,10,'',"0","0","L",'true');
$pdf->Cell(60,10,'',"0","1","C",'true');

//Headers
$pdf->SetFont('Helvetica','B',12);
$pdf->Cell(30,10,'Leads', 0, 0,'C');
$pdf->Cell(30,10,'Leads', 0, 0,'C');
$pdf->Cell(25,10,'Leads', 0, 0,'C');
$pdf->Cell(35,10,'API', 0, 0,'C');
$pdf->Cell(30,10,'Issued', 0, 0,'C');
$pdf->Cell(50,17,'Period', 0, 1,'C');
$pdf->SetXY($pdf->GetX(), $pdf->GetY() - 10); 
$pdf->Cell(30,10,'Generated', 0, 0,'C');
$pdf->Cell(30,10,'Cancelled', 0, 0,'C');
$pdf->Cell(25,10,'Issued', 0, 0,'C');
$pdf->Cell(35,10,'Generated', 0, 0,'C');
$pdf->Cell(30,10,'Leads %', 0, 0,'C');
$pdf->Cell(50,10,'', 0, 1,'C');
//echo "<br><br><br><br><br>";
//echo "<pre>" , var_dump($bi_months) , "</pre>";
//echo "<br><br><br><br><br>";

$pdf->SetFont('Helvetica','',12);

$total_leads = 0;
$total_cancelled = 0;
$total_issued = 0;
$total_api_generated = 0;
$total_issued_percentage = 0;
$week_ctr = 1;
if($output_bi_monthly){
	foreach($months as $bm){

		$bm_from = $bm->from->format('Ymd');
		$bm_to = $bm->to->format('Ymd');
		//echo "$bm_from - $bm_to <br>";
		$bm_clients_query = "SELECT *,l.name as leadgen_name, c.name as client_name, c.id as client_id from leadgen_tbl l LEFT JOIN clients_tbl c ON c.leadgen = l.id WHERE l.id ='$leadgen_id' AND c.date_submitted <= '$bm_to' AND c.date_submitted >= '$bm_from'";
		$bm_clients_result = mysqli_query($con,$bm_clients_query) or die('Could not look up user information; ' . mysqli_error($con));
		$bm_rowcount = mysqli_num_rows($bm_clients_result);
		$bm_totalclients = 0;
		//echo "BM Clients Query:<br>" . $bm_clients_query . "<br>";
		if($bm_rowcount==0){
			//print "No Records Found";
		}
		else{	
			while($bm_row  = mysqli_fetch_assoc($bm_clients_result)){
				$bm_totalclients++;
			}
		}

		//Issued Clients Only Query
		$bm_issued_clients_query = "SELECT *,l.name as leadgen_name, i.name as issued_client_name, i.id as issued_client_id from leadgen_tbl l LEFT JOIN issued_clients_tbl i ON i.leadgen = l.id WHERE l.id ='$leadgen_id' AND i.date_issued <= '$bm_to' AND i.date_issued >= '$bm_from'";
		$bm_issued_clients_result = mysqli_query($con,$bm_issued_clients_query) or die('Could not look up user information; ' . mysqli_error($con));
		$bm_rowcount = mysqli_num_rows($bm_issued_clients_result);
		$bm_totalissuedclients = 0;
		$bm_totalissuedpremiums = 0;
		$bm_issuedLeadsPercent = 0;
		//echo "BM Issued Clients Query:<br>" . $bm_issued_clients_query . "<br>";
		if($bm_rowcount==0){
			//print "No Records Found";
		}
		else{	
			while($row  = mysqli_fetch_assoc($bm_issued_clients_result)){
				extract($row);
				//echo $issued;
				$bm_totalissuedpremiums += (float)$issued;
				$bm_totalissuedclients++;
			}
			$bm_issuedLeadsPercent = 0;
			if($bm_totalissuedclients>0 && $bm_totalclients>0)
					$bm_issuedLeadsPercent = ($bm_totalissuedclients / $bm_totalclients) * 100;
		}

		//Cancelled Clients Only Query
		$bm_cancelled_clients_query = "SELECT *,l.name as leadgen_name, c.name as client_name, c.id as client_id from leadgen_tbl l LEFT JOIN clients_tbl c ON c.leadgen = l.id WHERE l.id ='$leadgen_id' AND c.date_status_updated <= '$bm_to' AND c.date_status_updated >= '$bm_from' AND c.status='Cancelled' ";
		$bm_cancelled_clients_result = mysqli_query($con,$bm_cancelled_clients_query) or die('Could not look up user information; ' . mysqli_error($con));
		$bm_cancelled_rowcount = mysqli_num_rows($bm_cancelled_clients_result);
		$bm_total_cancelled_clients = 0;
		//echo "BM Clients Query:<br>" . $bm_clients_query . "<br>";
		if($bm_cancelled_rowcount==0){
			//print "No Records Found";
		}
		else{	
			while($bm_cancelled_row  = mysqli_fetch_assoc($bm_cancelled_clients_result)){
				$bm_total_cancelled_clients++;
			}
		}
		
		//fetch
		$pdf->Cell(30,10,$bm_totalclients, 0, 0,'C');
		$pdf->Cell(30,10,$bm_total_cancelled_clients, 0, 0,'C');
		$pdf->Cell(25,10,$bm_totalissuedclients, 0, 0,'C');
		$pdf->Cell(35,10,"$" . number_format($bm_totalissuedpremiums,2), 0, 0,'C');
		$pdf->Cell(30,10,number_format($bm_issuedLeadsPercent,2) . "%", 0, 0,'C');
		$pdf->Cell(50,10, "Month $week_ctr", '0', 1,'C');

		$total_leads += $bm_totalclients;
		$total_cancelled += $bm_total_cancelled_clients;
		$total_issued += $bm_totalissuedclients;
		$total_api_generated += $bm_totalissuedpremiums;
		$week_ctr++;
	}	
}
$issuedpercent = ($total_issued!=00||($total_leads - $total_cancelled)!=0) ? ($total_issued / ($total_leads - $total_cancelled)) * 100 : 0;

$pdf->SetFont('Helvetica','B',12);
$pdf->Cell(30,10,$total_leads, 'T', 0,'C');
$pdf->Cell(30,10,$total_cancelled, 'T', 0,'C');
$pdf->Cell(25,10,$total_issued, 'T', 0,'C');
$pdf->Cell(35,10,"$" . number_format($total_api_generated,2), 'T', 0,'C');
$pdf->Cell(30,10,number_format($issuedpercent ,2) . "%", 'T', 0,'C');
$pdf->Cell(50,10,"", 'T', 1,'C');
$pdf->Ln();
//Weekly

$pdf->SetFont('Helvetica','B',14);
$pdf->SetFillColor(224,224,224);
$pdf->Cell(100,10,'Weekly Performance',"0","0","L",'true');
$pdf->Cell(40,10,'',"0","0","L",'true');
$pdf->Cell(60,10,'',"0","1","C",'true');

//Headers
$pdf->SetFont('Helvetica','B',12);
$pdf->Cell(30,10,'Leads', 0, 0,'C');
$pdf->Cell(30,10,'Leads', 0, 0,'C');
$pdf->Cell(25,10,'Leads', 0, 0,'C');
$pdf->Cell(35,10,'API', 0, 0,'C');
$pdf->Cell(30,10,'Issued', 0, 0,'C');
$pdf->Cell(50,17,'Period', 0, 1,'C');
$pdf->SetXY($pdf->GetX(), $pdf->GetY() - 10); 
$pdf->Cell(30,10,'Generated', 0, 0,'C');
$pdf->Cell(30,10,'Cancelled', 0, 0,'C');
$pdf->Cell(25,10,'Issued', 0, 0,'C');
$pdf->Cell(35,10,'Generated', 0, 0,'C');
$pdf->Cell(30,10,'Leads %', 0, 0,'C');
$pdf->Cell(50,10,'', 0, 1,'C');
//echo "<br><br><br><br><br>";
//echo "<pre>" , var_dump($bi_months) , "</pre>";
//echo "<br><br><br><br><br>";

$pdf->SetFont('Helvetica','',12);

$total_leads = 0;
$total_cancelled = 0;
$total_issued = 0;
$total_api_generated = 0;
$total_issued_percentage = 0;
$week_ctr = 1;
if($output_bi_monthly){
	foreach($bi_months as $bm){

		$bm_from = $bm->from->format('Ymd');
		$bm_to = $bm->to->format('Ymd');
		//echo "$bm_from - $bm_to <br>";
		$bm_clients_query = "SELECT *,l.name as leadgen_name, c.name as client_name, c.id as client_id from leadgen_tbl l LEFT JOIN clients_tbl c ON c.leadgen = l.id WHERE l.id ='$leadgen_id' AND c.date_submitted <= '$bm_to' AND c.date_submitted >= '$bm_from'";
		$bm_clients_result = mysqli_query($con,$bm_clients_query) or die('Could not look up user information; ' . mysqli_error($con));
		$bm_rowcount = mysqli_num_rows($bm_clients_result);
		$bm_totalclients = 0;
		//echo "BM Clients Query:<br>" . $bm_clients_query . "<br>";
		if($bm_rowcount==0){
			//print "No Records Found";
		}
		else{	
			while($bm_row  = mysqli_fetch_assoc($bm_clients_result)){
				$bm_totalclients++;
			}
		}

		//Issued Clients Only Query
		$bm_issued_clients_query = "SELECT *,l.name as leadgen_name, i.name as issued_client_name, i.id as issued_client_id from leadgen_tbl l LEFT JOIN issued_clients_tbl i ON i.leadgen = l.id WHERE l.id ='$leadgen_id' AND i.date_issued <= '$bm_to' AND i.date_issued >= '$bm_from'";
		$bm_issued_clients_result = mysqli_query($con,$bm_issued_clients_query) or die('Could not look up user information; ' . mysqli_error($con));
		$bm_rowcount = mysqli_num_rows($bm_issued_clients_result);
		$bm_totalissuedclients = 0;
		$bm_totalissuedpremiums = 0;
		$bm_issuedLeadsPercent = 0;
		//echo "BM Issued Clients Query:<br>" . $bm_issued_clients_query . "<br>";
		if($bm_rowcount==0){
			//print "No Records Found";
		}
		else{	
			while($row  = mysqli_fetch_assoc($bm_issued_clients_result)){
				extract($row);
				//echo $issued;
				$bm_totalissuedpremiums += (float)$issued;
				$bm_totalissuedclients++;
			}
			$bm_issuedLeadsPercent = 0;
			if($bm_totalissuedclients>0 && $bm_totalclients>0)
					$bm_issuedLeadsPercent = ($bm_totalissuedclients / $bm_totalclients) * 100;
		}

		//Cancelled Clients Only Query
		$bm_cancelled_clients_query = "SELECT *,l.name as leadgen_name, c.name as client_name, c.id as client_id from leadgen_tbl l LEFT JOIN clients_tbl c ON c.leadgen = l.id WHERE l.id ='$leadgen_id' AND c.date_status_updated <= '$bm_to' AND c.date_status_updated >= '$bm_from' AND c.status='Cancelled' ";
		$bm_cancelled_clients_result = mysqli_query($con,$bm_cancelled_clients_query) or die('Could not look up user information; ' . mysqli_error($con));
		$bm_cancelled_rowcount = mysqli_num_rows($bm_cancelled_clients_result);
		$bm_total_cancelled_clients = 0;
		//echo "BM Clients Query:<br>" . $bm_clients_query . "<br>";
		if($bm_cancelled_rowcount==0){
			//print "No Records Found";
		}
		else{	
			while($bm_cancelled_row  = mysqli_fetch_assoc($bm_cancelled_clients_result)){
				$bm_total_cancelled_clients++;
			}
		}
		
		//fetch
		$pdf->Cell(30,10,$bm_totalclients, 0, 0,'C');
		$pdf->Cell(30,10,$bm_total_cancelled_clients, 0, 0,'C');
		$pdf->Cell(25,10,$bm_totalissuedclients, 0, 0,'C');
		$pdf->Cell(35,10,"$" . number_format($bm_totalissuedpremiums,2), 0, 0,'C');
		$pdf->Cell(30,10,number_format($bm_issuedLeadsPercent,2) . "%", 0, 0,'C');
		$pdf->Cell(50,10, "Week $week_ctr", '0', 1,'C');

		$total_leads += $bm_totalclients;
		$total_cancelled += $bm_total_cancelled_clients;
		$total_issued += $bm_totalissuedclients;
		$total_api_generated += $bm_totalissuedpremiums;
		$week_ctr++;
	}	
}
$issuedpercent = ($total_issued!=00||($total_leads - $total_cancelled)!=0) ? ($total_issued / ($total_leads - $total_cancelled)) * 100 : 0;

$pdf->SetFont('Helvetica','B',12);
$pdf->Cell(30,10,$total_leads, 'T', 0,'C');
$pdf->Cell(30,10,$total_cancelled, 'T', 0,'C');
$pdf->Cell(25,10,$total_issued, 'T', 0,'C');
$pdf->Cell(35,10,"$" . number_format($total_api_generated,2), 'T', 0,'C');
$pdf->Cell(30,10,number_format($issuedpercent ,2) . "%", 'T', 0,'C');
$pdf->Cell(50,10,"", 'T', 1,'C');


$dnow_=date('d/m/Y');
$invoice_date_final=substr($dnow_,6,4).substr($dnow_,3,2).substr($dnow_	,0,2);
?>