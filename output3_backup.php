<?php
date_default_timezone_set('Pacific/Auckland');
ob_start();
require("fpdf/pdf_with_graph.php");

//CONFIGURATION
$fetchalldata = false;
$output_bi_monthly = true;


require("database.php");
/*
session_start();
*/
//post

class PDF extends FPDF
{


	function Footer()
	{
		global $fsp_num;
		global $name;
		$this->SetY(-15);
		$this->SetFont('Helvetica','',10);
		$this->SetTextColor(0,0,0);
		$this->Cell(200,10,"",0,0,'C');	

	}

	function getPage(){
		return $this->PageNo();
	}
}



//ifs

//FORMULAS end

//convert to 2 decimal number
function convertNum($x){
	return number_format($x, 2, '.', ',');
}

function convertNegNum($x){
	$x=$x*-1;
	return number_format($x, 2, '.', ',');
}



function removeparent($x){
	return preg_replace("/\([^)]+\)/","",$x); // 'ABC ';
}

//retrieving
extract($_POST);
//var_dump($_POST);

//GET BI MONTHLY DATA
$d_from = substr($date_from,6,4). "-" . substr($date_from,3,2). "-" . substr($date_from,0,2);
$d_to =substr($until,6,4). "-" . substr($until,3,2). "-" . substr($until,0,2);

$d1 = new DateTime($d_from); // Y-m-d
$d2 = new DateTime($d_to);
$period_covered_title = $d1->format('d F Y') . "-" . $d2->format('d F Y');
$bi_months = [];

$d3 = $d1; //d3 = date we'll use to loop the dates

$dateExceeded = false;
while($dateExceeded==false){
	$bm = getBiMonth($d3);
	////echo $bm->from->format('F, d Y') . "-" . $bm->to->format('F, d Y')."<br>";
	$bi_months[] = $bm;
	$d3 = getNextDate(getBiMonth($d3));
	if(!checkIfContinuing($d1,$d2,$d3))
		$dateExceeded = true;
}


function getBiMonth($day){
	$output = new stdClass();
	$day1 = clone $day;
	$day2 = clone $day;
	if($day->format('d')<=15){
		$output->note = "First half";
		$output->from = $day1->modify('first day of this month');
		$to = $day2->modify('first day of this month');
		$output->to = $to->modify('+ 14 days');
	}
	else{
		$output->note = "Second half";
		$output->from = $day1->modify('first day of this month');
		$output->from = $output->from->modify('+ 15 days');
		$output->to = $day2->modify('last day of this month');
	}
	//echo "<br><br><br>Output:" . $output->from->format('Ymd') . "-" . $output->to->format('Ymd') . "<br><br><br>";
	return clone $output;
}

function getNextDate($input){
	$output;

	if($input->note=="First half"){
		$output = $input->from->modify('first day of this month');
		$output = $input->from->modify('+15 days');
	}
	else{
		$output = $input->from->modify('first day of next month');
	}
	return $output;
}

function checkIfContinuing($from,$to,$next_date){
  return (($next_date >= $from) && ($next_date <= $to));
}

//Get Lead Gen Name
$leadgen_query = "SELECT * from leadgen_tbl where id='" . $leadgen_id . "'";
$leadgen_result = mysqli_query($con,$leadgen_query);
$leadgen_fetch = mysqli_fetch_assoc($leadgen_result);
$leadgen_name = $leadgen_fetch['name'];		
$lead_by = $leadgen_fetch['type'];

$period_covered =  substr($date_from,0,2) . "/" . substr($date_from,3,2) . "/" .substr($date_from,6,4) . "-"  . substr($until,0,2) . "/" .substr($until,3,2). "/". substr($until,6,4) ;
$date_from=substr($date_from,6,4).substr($date_from,3,2).substr($date_from,0,2);
$until=substr($until,6,4).substr($until,3,2).substr($until,0,2);
$date_now = substr($date_now,6,4).substr($date_now,3,2).substr($date_now,0,2);
//CREATE REFERENCE NUMBER

$refnum_query = "SELECT * FROM lead_gen_report WHERE reference_number LIKE '%$date_now' ORDER BY reference_number DESC LIMIT 1";
//echo $refnum_query;
$refnum_result = mysqli_query($con,$refnum_query) or die('Could not look up user information; ' . mysqli_error($con));
$refnum_count = mysqli_fetch_assoc($refnum_result);
$refnum_count = $refnum_count['reference_number'];
$latest_number = substr($refnum_count, 3, 4);
$latest_number += 1;

$leadgen_refnum = "LG-" .  convertToFourDigits($latest_number) . $date_now;


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
//Convert Date
function convertToFourDigits($num = 0){
	$op = "";
	if($num < 10){
		$op = "000" . $num;
	}
	elseif($num < 100){
		$op = "00" . $num;
	}
	elseif($num < 1000){
		$op = "0" . $num;
	}
	elseif($num < 10000){
		$op = "" . $num;
	}
	return $op;
}


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
$pdf->Cell(200,10,'Performance Report',"0","1","C",'true');
$pdf->SetFont('Helvetica','B',15);
$pdf->Cell(100,10,'Name:',"0","0","L");
$pdf->SetFont('Helvetica','',14);
$pdf->Cell(90,10,$leadgen_name,"0","1","R");
$pdf->SetFont('Helvetica','B',15);
$pdf->Cell(100,10,'Role:',"0","0","L");
$pdf->SetFont('Helvetica','',14);
$pdf->Cell(90,10,$lead_by,"0","1","R");
$pdf->SetFont('Helvetica','B',15);
$pdf->Cell(100,10,"Period Covered:","0","0","L");
$pdf->SetFont('Helvetica','',14);
$pdf->Cell(90,10,$period_covered_title,"0","1","R");
$pdf->SetFont('Helvetica','B',15);
$pdf->Cell(100,10,"Reference Number:","0","0","L");
$pdf->SetFont('Helvetica','',14);
$pdf->Cell(90,10,$leadgen_refnum,"0","1","R");

$pdf->SetXY($x+10, $y+85); 


$pdf->SetFillColor(224,224,224);
$pdf->SetFont('Helvetica','B',14);
$pdf->Cell(100,10,'Overall Performance', 0, 0,'L','true');
$pdf->Cell(40,10,' ', 0, 0,'R','true');
$pdf->Cell(60,10,'Period Covered:' . $period_covered, 0, 1,'R','true');

$pdf->SetXY($x+10, $y+95); 
$pdf->SetFont('Helvetica','B',12);
$pdf->Cell(40,10,'Leads Generated', 0, 0,'C');
$pdf->Cell(40,10,'Leads Cancelled', 0, 0,'C');
$pdf->Cell(40,10,'Leads Issued', 0, 0,'C');
$pdf->Cell(40,10,'API Generated', 0, 0,'C');
$pdf->Cell(40,10,'Issued Leads %', 0, 0,'C');

//$pdf->Cell(55,10,'$'.desc_val($desc[0]), 0, 1,'R');

$pdf->SetXY($x+10, $y+105); 
$pdf->SetFont('Helvetica','',12);
$pdf->Cell(40,10,$totalclients, 0, 0,'C');
$pdf->Cell(40,10,$totalcclients, 0, 0,'C');
$pdf->Cell(40,10,$totalissuedclients, 0, 0,'C');
$pdf->Cell(40,10,"$" . number_format($totalissuedpremiums,2), 0, 0,'C');
$pdf->Cell(40,10,number_format($issuedLeadsPercent,2) . "%", 0, 1,'C');
//$pdf->Cell(55,10,'$'.desc_val($desc[0]), 0, 1,'R');



//GRAPHS
$pdf->SetXY($x+10, $y+120); 

if($totalclients!=0 || $totalissuedclients!=0){

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
$data = array('Leads Required' => $leads_required[0], 'Leads' => $totalclients);
$pdf->SetXY($valX + 100, $valY +5);
$pdf->BarDiagram(100, 60, $data, '%v (%p)', array($col2,$col1),0,4, "vertical");
$pdf->SetXY($x+10, $y+198);

$dash_indexes= array();
$dash_values = array();
$leads_required_type = $required_leads_type;
//LINE GRAPH
$leads_generated_bi_monthly = array();
if($output_bi_monthly){
	$tier = 1;
	foreach($leads_required as $lr){	
			$bm_lr = $lr;
			if($leads_required_type=="Weekly"){
				$bm_lr /= 2;
			}
			elseif($leads_required_type=="Monthly"){
				$bm_lr *= 2;
			}

		foreach($bi_months as $bm){
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
			$bm_date_to = $bm->to->format('d/m');
			//echo "BM Clients Query:<br>" . $bm_clients_query . "<br>";
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
$pdf->LineGraph(190,50,$data,'VHvBdB',$colors,6,3,$dash_indexes,$dash_values);
//END GRAPHS


//BI MONTHLY REPORT
$pdf->SetXY($x+10, $y+250); // position of text1, numerical, of course, not x1 and y1
}
$pdf->SetFont('Helvetica','B',14);
$pdf->SetFillColor(224,224,224);
$pdf->Cell(100,10,'Bi-Monthly Performance',"0","0","L",'true');
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
		$pdf->Cell(50,10,$bm->from->format('d/m/Y') . "-" . $bm->to->format('d/m/y'), '0', 1,'C');

		$total_leads += $bm_totalclients;
		$total_cancelled += $bm_total_cancelled_clients;
		$total_issued += $bm_totalissuedclients;
		$total_api_generated += $bm_totalissuedpremiums;
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


//For Production
$path="files/preview.pdf";
$pdf->Output($path,'F');

//For Testing
//$pdf->Output();

//OUTPUT 
$file=array();
$file['number']=$leadgen_refnum;
$file['link']=$path;
$file['entrydate']=$invoice_date_final;
$file['leadgen_id'] = $leadgen_id;
$file['totalclients'] = $totalclients;
$file['totalissuedclients'] = $totalissuedclients;
$file['totalissuedpremiums'] = $totalissuedpremiums;
$file['from'] = $date_from;
$file['to'] = $until;
$file['required_leads'] = $required_leads;
$file['required_leads_type'] = $required_leads_type;
echo json_encode($file);
//db add end
//}

?>