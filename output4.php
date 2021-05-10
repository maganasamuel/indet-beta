<?php
date_default_timezone_set('Pacific/Auckland');
ob_start();
require("fpdf/fpdf.php");

//CLIENT DATA REPORT PDF
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

$date_from = "";
$date_to = "";

//retrieving
extract($_POST);

if(isset($leadgens))
	$leadgens=json_decode($leadgens);	

if(isset($advisers))
	$advisers=json_decode($advisers);	

//var_dump($_POST);
$d_from = "";
$d_to = "";

//Set dates
if($date_from!="" && $date_to!=""){
	
	//echo $date_from . "-" . $date_to;
	
}
else{
	$date_to_query = "SELECT * FROM clients_tbl ORDER BY date_submitted DESC LIMIT 1";
	$date_to_result = mysqli_query($con,$date_to_query) or die('Could not look up user information; ' . mysqli_error($con));
	$date_to_fetch = mysqli_fetch_assoc($date_to_result);
	$date_to = $date_to_fetch["date_submitted"];
	$date_from_query = "SELECT * FROM clients_tbl ORDER BY date_submitted ASC LIMIT 1";
	$date_from_result = mysqli_query($con,$date_from_query) or die('Could not look up user information; ' . mysqli_error($con));
	$date_from_fetch = mysqli_fetch_assoc($date_from_result);
	$date_from = $date_from_fetch["date_submitted"];
}

$until = $date_to;

//CREATE REFERENCE NUMBER
$refnum_query = "SELECT COUNT(*) as total FROM client_data_reports WHERE reference_number LIKE '%$date_now'";
//echo $refnum_query;
$refnum_result = mysqli_query($con,$refnum_query) or die('Could not look up user information; ' . mysqli_error($con));
$refnum_count = mysqli_fetch_assoc($refnum_result);

$leadgen_refnum = "CD-" .  convertToFourDigits(($refnum_count['total'] + 1)) . $date_now;

	//echo "<br><br><hr><pre> , ";
	//var_dump($_POST);
	//echo " , <pre><hr><br><br>";
	//echo $leadgens;
	//echo $advisers;

//QUERY BUILDER
	$select = "SELECT *, c.name as client_name, c.id as client_id";
	$from = " from clients_tbl c ";
	$where = "";
	$sort = " ORDER BY c.leadgen, c.date_submitted DESC, c.name ";


	//if Leadgen is present
	if(!empty($leadgens)){
		$wherestring = implode("','",explode(",",$leadgens));

		$select.=", l.name as leadgen_name ";
		$from .= " LEFT JOIN leadgen_tbl l ON c.leadgen = l.id ";
		$where = " WHERE l.id IN ('" . $wherestring . "') ";
	}

	if(!empty($advisers)){
		$wherestring = implode("','",explode(",",$advisers));
		if($where==""){
			$where = " WHERE ";
		}
		else{ 
			$where .= " AND ";
		}
		$where .= "  a.id IN ('" . $wherestring . "') ";
	}

	$select.=", a.name as adviser_name ";
	$from .= " LEFT JOIN adviser_tbl a ON c.assigned_to = a.id ";

	if($date_from!="" && $date_to!=""){
		if($where==""){
			$where = " WHERE ";
		}
		else{ 
			$where .= " AND ";
		}
		$where .= " c.date_submitted <= '$until' AND c.date_submitted >= '$date_from' ";
	}
//END OF QUERY BUILDER

	$leadGenerator = array();

$clients_query = "$select$from$where$sort";

//		echo "<br><br><br><br><hr>" . $clients_query . "<hr><br><br><br><br>";
$totalclients = 0;
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
		extract($row);		
		$cli = new stdClass();
		$cli->id = $client_id;
		$cli->name = $client_name;
		$cli->appt_date =  substr($appt_date,6,2) . "/" . substr($appt_date,4,2) . "/" .substr($appt_date,0,4);
		$cli->phone = $appt_time;
		$cli->address = $address;
		$cli->adviser = $adviser_name;
		if(!empty($leadgens)){
				$cli->leadgen = $leadgen_name;
			}
		$cli->date_submitted = substr($date_submitted,6,2) . "/" . substr($date_submitted,4,2) . "/" .substr($date_submitted,0,4);
		$clients[] = $cli;
		$totalclients++;
	}
}
/*

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
			$leadgen_name = $leadgen_name;
			$totalissuedpremiums += (float)$issued;
			$totalissuedclients++;
		}
		$issuedLeadsPercent = ($totalissuedclients / $totalclients) * 100;
	}


*/


$pdf = new PDF('P', 'mm', 'Legal');
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
$pdf->Cell(200,10,'Client Database',"0","1","C",'true');
/*
$pdf->SetFont('Helvetica','B',15);
$pdf->Cell(100,10,'Lead Generator:',"0","0","L");
$pdf->SetFont('Helvetica','',14);
$pdf->Cell(90,10,$leadgen_name,"0","1","R");
*/
$pdf->SetFont('Helvetica','B',15);
$pdf->Cell(100,10,"Period Covered:","0","0","L");
$pdf->SetFont('Helvetica','',14);
$pdf->Cell(90,10,$period_covered,"0","1","R");
$pdf->SetFont('Helvetica','B',15);
$pdf->Cell(100,10,"Reference Number:","0","0","L");
$pdf->SetFont('Helvetica','',14);
$pdf->Cell(90,10,$leadgen_refnum,"0","1","R");
$pdf->SetFont('Helvetica','B',15);
$pdf->Cell(100,10,"Clients Found:","0","0","L");
$pdf->SetFont('Helvetica','',14);
$pdf->Cell(90,10,$totalclients,"0","1","R");
$pdf->SetFont('Helvetica','B',15);
$pdf->Cell(100,10,"Issued Clients Found:","0","0","L");
$pdf->SetFont('Helvetica','',14);
$pdf->Cell(90,10,$totalclients,"0","1","R");


/*
$pdf->SetFillColor(224,224,224);
$pdf->SetFont('Helvetica','B',14);
$pdf->Cell(100,10,'Overall Performance', 0, 0,'L','true');
$pdf->Cell(40,10,' ', 0, 0,'R','true');
$pdf->Cell(60,10,'Period Covered:' . $period_covered, 0, 1,'R','true');

$pdf->SetXY($x+10, $y+90); 
$pdf->SetFont('Helvetica','B',12);
$pdf->Cell(50,10,'Leads Generated', 0, 0,'C');
$pdf->Cell(50,10,'Leads Issued', 0, 0,'C');
$pdf->Cell(50,10,'API Generated', 0, 0,'C');
$pdf->Cell(50,10,'Issued Leads %', 0, 0,'C');

//$pdf->Cell(55,10,'$'.desc_val($desc[0]), 0, 1,'R');

$pdf->SetXY($x+10, $y+100); 
$pdf->SetFont('Helvetica','',12);
$pdf->Cell(50,10,$totalclients, 0, 0,'C');
$pdf->Cell(50,10,$totalissuedclients, 0, 0,'C');
$pdf->Cell(50,10,"$" . number_format($totalissuedpremiums,2), 0, 0,'C');
$pdf->Cell(50,10,number_format($issuedLeadsPercent,2) . "%", 0, 1,'C');

*/

//BI MONTHLY REPORT
$pdf->SetXY($x+10, $y+80); // position of text1, numerical, of course, not x1 and y1
$pdf->SetFont('Helvetica','B',14);
$pdf->SetFillColor(224,224,224);
$pdf->Cell(100,10,'Lead Generator',"0","0","L",'true');
$pdf->Cell(40,10,'',"0","0","L",'true');
$pdf->Cell(60,10,'LEAD GEN Name',"0","1","R",'true'); 
$pdf->SetFont('Helvetica','B',10);
$pdf->Cell(35,10,'Client Name', 0, 0,'C');
$pdf->Cell(20,10,'Appt Date', 0, 0,'C');
$pdf->Cell(40,10,'Phone Number', 0, 0,'C');
$pdf->Cell(35,10,'Adviser', 0, 0,'C');
$pdf->Cell(20,10,'Date Sub', 0, 0,'C');
$pdf->Cell(50,10,'Address', 0, 1,'C');

//echo "<br><br><br><br><br>";
//echo "<pre>" , var_dump($bi_months) , "</pre>";
//echo "<br><br><br><br><br>";

$pdf->SetFont('Helvetica','',9);
$border = 1;
$pdf->SetFillColor(230,230,230);
$alternating = false;
$rownum = 0;
$fill = 0;
if($clienttype!="issued_clients_only"){
	foreach($clients as $c){
		//fetch
		$rownum++;
		$rowheight = 10;
		//echo "Address: " . ($wrappedAdd->text) . " Length " . $wrappedAdd->count . "<br>";
		if($alternating){
			$fill = ($rownum % 2 == 0) ? 1 : 0;
		}

		if($c->address!=""){
				$rowheight = $pdf->NbLines(50,$c->address) * 10;
			}


		$pdf->Cell(35,$rowheight,$c->name, $border, 0,'C', $fill);
		$pdf->Cell(20,$rowheight,$c->appt_date, $border, 0,'C', $fill);
		$pdf->Cell(40,$rowheight,$c->phone, $border, 0,'C', $fill);
		$pdf->Cell(35,$rowheight,$c->adviser, $border, 0,'C', $fill);
		$pdf->Cell(20,$rowheight,$c->date_submitted, $border, 0,'C', $fill);
		$pdf->MultiCell(50,10,$c->address, $border,'C', $fill);
	}	
}

if($clienttype!="clients_only"){
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

		
		//fetch

		$pdf->Cell(35,10,$bm_totalclients, 0, 0,'C');
		$pdf->Cell(35,10,$bm_totalissuedclients, 0, 0,'C');
		$pdf->Cell(40,10,"$" . number_format($bm_totalissuedpremiums,2), 0, 0,'C');
		$pdf->Cell(30,10,number_format($bm_issuedLeadsPercent,2) . "%", 0, 0,'C');
		$pdf->Cell(60,10,$bm->from->format('d/m/Y') . "-" . $bm->to->format('d/m/y'), 0, 1,'C');
	}	
}
$dnow_=date('d/m/Y');
$invoice_date_final=substr($dnow_,6,4).substr($dnow_,3,2).substr($dnow_	,0,2);
		


//For Production
/*
$path="files/preview.pdf";
$pdf->Output($path,'F');
*/
//For Testing
$pdf->Output();

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

echo json_encode($file);
//db add end
//}

?>