<?php
date_default_timezone_set('Pacific/Auckland');
ob_start();
require("fpdf/fpdf.php");






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
		$this->Cell(0,10,'Invoice Summary '. ''.' '.preg_replace("/\([^)]+\)/","",''),0,0,'L');	
		$this->Cell(0,10,'Page '.$this->PageNo(),0,1,'R');
	}

	function getPage(){
		return $this->PageNo();
	}
}

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

$summary_id = $_GET['summary_id'];
$searchadv="SELECT * FROM `summary` where `id` =" . $summary_id;
$search=mysqli_query($con,$searchadv) or die('Could not look up user information; ' . mysqli_error($con));
$summary_data = mysqli_fetch_assoc($search);
extract($summary_data);
$statuses=json_decode($statuses);										//Desc
$until = $date_to;

$pdf = new PDF('P', 'mm', 'Legal');
$x = $pdf->GetX();
$y = $pdf->GetY();
//echo $date_from . "<br>" . $until . "<br>";

$period_covered = substr($date_from,6,2)."/".substr($date_from,4,2)."/".substr($date_from,0,4)."-".substr($date_to,6,2)."/".substr($date_to,4,2)."/".substr($date_to,0,4);

//Fetch Invoices Data
$invoices_id_list = "";
$invoices_array = array();
$total_leads = 0;
$total_issued = 0;
$total_due = 0;

//Convert Date
$pending_invoices = [];
$paid_invoices = [];
$contested_invoices = [];
$cancelled_invoices = [];
$waived_invoices = [];
$others_invoices = [];

$name = "";
$fsp_num = "";
$adviser_address = "";	

//Search Invoices
$wherestring = implode("','",$statuses);
$searchadv="SELECT *, a.leads as payperlead, i.leads as client_leads FROM invoices i LEFT JOIN adviser_tbl a ON i.adviser_id=a.id WHERE i.adviser_id='$adviser_id' AND i.date_created <= '$until' AND i.date_created>='$date_from' AND i.status IN ('" . $wherestring . "')";
$search=mysqli_query($con,$searchadv) or die('Could not look up user information; ' . mysqli_error($con));
$rowcount = mysqli_num_rows($search);
if($rowcount==0){
	print "No Records Found";
}
//echo $searchadv;
while ($row = mysqli_fetch_array($search)) {
	array_push($invoices_array, $row['number']); 
    $invoices_id_list .= $row['number'] . ",";
    $total_due += $row['amount'];
    $name = $row['name'];
    $fsp_num = $row['fsp_num'];
    $adviser_address = $row['address'];
    $rowleads = json_decode($row['client_leads']);
    $rowissued = json_decode($row['issued']);
    //echo "Leads:" . $rowleads . "<br>Issued:" . $rowissued;
    $total_leads += count($rowleads);
    $total_issued += count($rowissued);
    $inv = new stdClass();
    $inv->invoice_no = $row['number'];
    $inv->date_created = $row['date_created'];
    $inv->amount = $row['amount'];
	$inv->status = $row['status'];
	$inv->leads = count($rowleads);
	$inv->issued = count($rowissued);
    switch($row['status']){
    	case "Pending":    		
    		$pending_invoices[] = $inv;
    		break;
    	case "Paid":
    		$paid_invoices[] = $inv;
    		break;
    	case "Contested":
    		$contested_invoices[] = $inv;
    		break;
		case "Cancelled":
    		$cancelled_invoices[] = $inv;
    		break;
		case "Waived":
    		$waived_invoices[] = $inv;
    		break;
		case "Others":
    		$others_invoices[] = $inv;
    		break;
    }
}


$invoices_id_list = substr($invoices_id_list, 0, -1);

$words = explode(" ", $name);
$adviser_initials = "";
$ctr = 0;

foreach($words as $w) {
  $adviser_initials .= $w[0];
  $ctr++;
  if($ctr>=2)
		break;
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


$pdf->SetFont('Helvetica','',12);
$pdf->MultiCell(55,6,"3G/39 Mackelvie Street Grey Lynn 1021 Auckland New Zealand 0508 123 467",0,"L",false);

$pdf->SetTextColor(44,44,44);
$pdf->SetXY($x+100, $y+35); // position of text1, numerical, of course, not x1 and y1
$pdf->SetFont('Helvetica','B',12);
$pdf->Write(0, 'Phone');
$pdf->SetTextColor(0,0,0);

$pdf->SetXY($x+120, $y+35); // position of text1, numerical, of course, not x1 and y1
$pdf->SetFont('Helvetica','',12);
$pdf->Write(0, '0508 123 467');


$pdf->SetXY($x+100, $y+40); // position of text1, numerical, of course, not x1 and y1
$pdf->SetFont('Helvetica','B',12);
$pdf->SetTextColor(44,44,44);
$pdf->Write(0, 'Website');
$pdf->SetTextColor(0,0,0);

$pdf->SetXY($x+120, $y+40); // position of text1, numerical, of course, not x1 and y1
$pdf->SetFont('Helvetica','',12);
$pdf->Write(0, 'www.eliteinsure.co.nz');

$pdf->SetTextColor(44,44,44);
$pdf->SetXY($x+100, $y+45); // position of text1, numerical, of course, not x1 and y1
$pdf->SetFont('Helvetica','B',12);
$pdf->Write(0, 'Email');
$pdf->SetTextColor(0,0,0);

$pdf->SetXY($x+120, $y+45); // position of text1, numerical, of course, not x1 and y1
$pdf->SetFont('Helvetica','',12);
$pdf->Write(0, 'admin@eliteinsure.co.nz');

$pdf->SetTextColor(12,31,69);
$pdf->SetXY($x+100, $y+60); // position of text1, numerical, of course, not x1 and y1
$pdf->SetFont('Helvetica','B',12);
$pdf->Write(0, '');

$pdf->SetTextColor(0,0,0);

$pdf->SetXY($x+150, $y+60); // position of text1, numerical, of course, not x1 and y1
$pdf->SetFont('Helvetica','',12);
$pdf->Write(0, "");

$pdf->SetTextColor(12,31,69);
$pdf->SetXY($x+100, $y+67); // position of text1, numerical, of course, not x1 and y1
$pdf->SetFont('Helvetica','B',12);
$pdf->Write(0, 'Period Covered');
$pdf->SetTextColor(0,0,0);

$pdf->SetXY($x+150, $y+67); // position of text1, numerical, of course, not x1 and y1
$pdf->SetFont('Helvetica','',12);
$pdf->Write(0, $period_covered);

$pdf->SetTextColor(12,31,69);
$pdf->SetXY($x+100, $y+74); // position of text1, numerical, of course, not x1 and y1
$pdf->SetFont('Helvetica','B',12);
$pdf->Write(0, 'Summary Number');
$pdf->SetTextColor(0,0,0);


$pdf->SetXY($x+150, $y+74); // position of text1, numerical, of course, not x1 and y1
$pdf->SetFont('Helvetica','',12);
$pdf->Write(0, $number);

$pdf->SetTextColor(12,31,69);
$pdf->SetXY($x+100, $y+81); // position of text1, numerical, of course, not x1 and y1
$pdf->SetFont('Helvetica','B',12);
$pdf->Write(0, 'GST Number');
$pdf->SetTextColor(0,0,0);

$pdf->SetXY($x+150, $y+81); // position of text1, numerical, of course, not x1 and y1
$pdf->SetFont('Helvetica','',12);
$pdf->Write(0,'119-074-304');


$pdf->SetTextColor(12,31,69);
$pdf->SetXY($x+100, $y+88); // position of text1, numerical, of course, not x1 and y1
$pdf->SetFont('Helvetica','B',12);
$pdf->Write(0, 'Client Code');

$pdf->SetTextColor(0,0,0);
$pdf->SetXY($x+150, $y+88); // position of text1, numerical, of course, not x1 and y1
$pdf->SetFont('Helvetica','',12);
$pdf->Write(0,$fsp_num);

$pdf->SetXY($x+10, $y+60); // position of text1, numerical, of course, not x1 and y1
$pdf->SetFont('Helvetica','',18);
$pdf->Cell(0,10,'Adviser\'s Invoice Summary',"0","1","L");

$pdf->SetXY($x+10, $y+75); // position of text1, numerical, of course, not x1 and y1
$pdf->SetFont('Helvetica','',12);
$pdf->Write(0,'To: ');

$pdf->SetXY($x+20, $y+75); // position of text1, numerical, of course, not x1 and y1

$pdf->Write(0,$name);

$pdf->SetXY($x+20, $y+80); // position of text1, numerical, of course, not x1 and y1

$pdf->MultiCell(50,5,"$adviser_address",0,"L",false);


//DESCRIPTION
//$pdf->SetXY($x+10, $y+60); // position of text1, numerical, of course, not x1 and y1
$pdf->SetXY($x+10, $y+118); 


$pdf->SetFont('Helvetica','',14);
$pdf->SetFillColor(224,224,224);
$pdf->Cell(100,10,'DESCRIPTION', 0, 0,'L','true');
$pdf->Cell(43,10,' ', 0, 0,'R','true');
$pdf->Cell(60,10,'  Total', 0, 1,'C','true');

$pdf->SetXY($x+10, $y+127); 
$pdf->SetFont('Helvetica','B',12);
$pdf->Cell(100,10,'Leads Assigned', 0, 0,'L');
$pdf->Cell(43,10,' ', 0, 0,'R');
$pdf->SetFont('Helvetica','',12);
$pdf->Cell(60,10,$total_leads, 0, 1,'C');
//$pdf->Cell(55,10,'$'.desc_val($desc[0]), 0, 1,'R');

$pdf->SetXY($x+10, $y+137); 
$pdf->SetFont('Helvetica','B',12);
$pdf->Cell(100,10,'Leads Issued', 0, 0,'L');
$pdf->Cell(43,10,' ', 0, 0,'R');
$pdf->SetFont('Helvetica','',12);
$pdf->Cell(60,10,$total_issued, 0, 1,'C');

/*

$pdf->SetXY($x+10, $y+145); 

$pdf->Cell(100,0,' ', 0, 0,'L');
$pdf->Cell(30,0,'', 0, 0,'R');
$pdf->Cell(55,0,'','T', 0, 1,'R');



$pdf->SetXY($x+10, $y+160); 
$pdf->SetFont('Helvetica','',13);
$pdf->Cell(100,0,' ', 0, 0,'L');
$pdf->Cell(30,0,'Sub Total', 0, 0,'R');
$pdf->Cell(55,0,'$'.number_format($sub_total,2), 0, 1,'R');

$pdf->SetXY($x+10, $y+167); 
$pdf->SetFont('Helvetica','',13);
$pdf->Cell(100,0,' ', 0, 0,'L');
$pdf->Cell(30,0,'Total GST 15%', 0, 0,'R');
$pdf->Cell(55,0,'$'.number_format($sub_total*.15,2), 0, 1,'R');

$pdf->SetXY($x+10, $y+172); 
$pdf->Cell(100,0,' ', 0, 0,'L');
$pdf->Cell(30,0,'', 0, 0,'R');
$pdf->Cell(55,0,'','T', 0, 1,'R');


$total_payable=$sub_total+($sub_total*.15);

$pdf->SetXY($x+10, $y+179); 
$pdf->SetFont('Helvetica','B',14);
$pdf->Cell(100,0,' ', 0, 0,'L');
$pdf->Cell(30,0,'TOTAL PAYABLE', 0, 0,'R');
$pdf->SetTextColor(255,0,0);
$pdf->Cell(55,0,'$'.number_format($total_payable,2), 0, 1,'R');

$pdf->SetTextColor(12,31,69);
$pdf->SetXY($x+10, $y+190); // position of text1, numerical, of course, not x1 and y1
$pdf->SetFont('Helvetica','I',10);
$pdf->Cell(0,10,'If payment is not made by due date, interest may be charged on outstanding balance',"0","1","L");

$pdf->SetTextColor(0,0,0);
$pdf->SetXY($x+10, $y+200); 
$pdf->Cell(0,0,' ',0, 0, 0,'C');


$pdf->SetXY($x+10, $y+201); // position of text1, numerical, of course, not x1 and y1
$pdf->SetFont('Helvetica','',14);
$pdf->SetFillColor(224,224,224);
$pdf->Cell(100,10,'PAYMENT ADVICE',"0","0","L",'true');
$pdf->Cell(40,10,'',"0","0","L",'true');
$pdf->Cell(60,10,'',"0","1","C",'true');

$pdf->SetXY($x+10, $y+220); // position of text1, numerical, of course, not x1 and y1
$pdf->SetFont('Helvetica','',12);
$pdf->SetFillColor(0,0,0);
$pdf->SetFont('Helvetica','B',12);
$pdf->Cell(40,0,'Client',"0","0","L");
$pdf->SetFont('Helvetica','',12);
$pdf->Cell(40,0,$name,"0","0","L");
$pdf->Cell(60,0,'',"0","1","C");

$pdf->SetXY($x+10, $y+227); // position of text1, numerical, of course, not x1 and y1
$pdf->SetFont('Helvetica','',12);
$pdf->SetFillColor(0,0,0);
$pdf->SetFont('Helvetica','B',12);

$pdf->Cell(40,0,'Invoice Number(s)',"0","0","L");
$pdf->SetFont('Helvetica','',12);
$pdf->Cell(40,0,$invoices_id_list,"0","0","L");
$pdf->Cell(60,0,'',"0","1","C");


$pdf->SetXY($x+10, $y+234); // position of text1, numerical, of course, not x1 and y1
$pdf->SetFont('Helvetica','',12);
$pdf->SetFillColor(0,0,0);
$pdf->SetFont('Helvetica','B',12);
$pdf->Cell(40,0,'Total Due',"0","0","L");
$pdf->SetFont('Helvetica','',12);
$pdf->Cell(40,0,'$'.number_format($total_due,2),"0","0","L");
$pdf->Cell(60,0,'',"0","1","C");

$pdf->SetXY($x+10, $y+245); // position of text1, numerical, of course, not x1 and y1
$pdf->SetFont('Helvetica','',12);
$pdf->SetFillColor(0,0,0);
$pdf->SetFont('Helvetica','B',12);
$pdf->Cell(40,0,'Summary Number',"0","0","L");
$pdf->SetFont('Helvetica','',12);
$pdf->Cell(40,0,$invoice_number,"0","0","L");
$pdf->Cell(60,0,'',"0","1","C");


$pdf->SetXY($x+120, $y+218); // position of text1, numerical, of course, not x1 and y1

$pdf->SetFont('Helvetica','',12);
$pdf->MultiCell(85,5,"Direct Credit
Please make payment into the following account: Eliteinsure Ltd, ANZ Bank, 06-0254-0426124-00. Please use the reference ".$fsp_num.". ",0,"L",false);

*/
//END OF DESCRIPTION



$pdf->SetXY($x+10, $y+161); // position of text1, numerical, of course, not x1 and y1
$pdf->SetFont('Helvetica','',14);
$pdf->SetFillColor(224,224,224);
$pdf->Cell(100,10,'PAYMENT ADVICE',"0","0","L",'true');
$pdf->Cell(40,10,'',"0","0","L",'true');
$pdf->Cell(60,10,'',"0","1","C",'true');

$pdf->SetXY($x+10, $y+180); // position of text1, numerical, of course, not x1 and y1
$pdf->SetFont('Helvetica','',12);
$pdf->SetFillColor(0,0,0);
$pdf->SetFont('Helvetica','B',12);
$pdf->Cell(40,0,'Client',"0","0","L");
$pdf->SetFont('Helvetica','',12);
$pdf->Cell(40,0,$name,"0","0","L");
$pdf->Cell(60,0,'',"0","1","C");

$pdf->SetXY($x+10, $y+187); // position of text1, numerical, of course, not x1 and y1
$pdf->SetFont('Helvetica','',12);
$pdf->SetFillColor(0,0,0);
$pdf->SetFont('Helvetica','B',12);

$pdf->Cell(40,0,'Summary Number',"0","0","L");
$pdf->SetFont('Helvetica','',12);
$pdf->Cell(40,0,$number,"0","0","L");
$pdf->Cell(60,0,'',"0","1","C");


$pdf->SetXY($x+10, $y+194); // position of text1, numerical, of course, not x1 and y1
$pdf->SetFont('Helvetica','',12);
$pdf->SetFillColor(0,0,0);
$pdf->SetFont('Helvetica','B',12);
$pdf->Cell(40,0,'Total Due',"0","0","L");
$pdf->SetFont('Helvetica','',12);
$pdf->Cell(40,0,'$'.number_format($total_due,2),"0","0","L");
$pdf->Cell(60,0,'',"0","1","C");

$pdf->SetXY($x+10, $y+205); // position of text1, numerical, of course, not x1 and y1
$pdf->SetFont('Helvetica','',12);
$pdf->SetFillColor(0,0,0);
$pdf->SetFont('Helvetica','B',12);
$pdf->Cell(40,11,'Invoice Number(s)',"0","0","L");

$pdf->SetFont('Helvetica','',12);
//Stress Test
//$pdf->MultiCell(48,11,$invoices_id_list . "," . $invoices_id_list."," . $invoices_id_list."," . $invoices_id_list."," . $invoices_id_list,0,"L",false);
$pdf->MultiCell(48,11,$invoices_id_list,0,"L",false);
$pdf->Cell(60,0,'',"0","1","C");


$pdf->SetXY($x+120, $y+178); // position of text1, numerical, of course, not x1 and y1

$pdf->SetFont('Helvetica','',12);
$pdf->MultiCell(85,5,"Direct Credit
Please make payment into the following account: Eliteinsure Ltd, ANZ Bank, 06-0254-0426124-00. Please use the reference ".$fsp_num.". ",0,"L",false);


//Start Displaying Invoices
//Initialize page 2
	$pdf->AddPage('P', 'Legal');
	$pdf->Image('logo.png',10,10,-160);
	$pdf->SetFont('Helvetica','B',14);
	$pdf->SetTextColor(0,42,160);
	$pdf->SetXY($x+10, $y+30); 

//echo count($paid_invoices) . "<br>";
//var_dump($statuses);
//PENDING INVOICES
if(in_array('Pending', $statuses)&&count($pending_invoices)>0):
	//Heading
	$pdf->SetFont('Helvetica','',14);
	$pdf->SetFillColor(224,224,224);
	$pdf->SetTextColor(0,0,0);
	$pdf->Cell(100,10,'Pending Invoices', 0, 0,'L','true');
	$pdf->Cell(43,10,' ', 0, 0,'R','true');
	$pdf->Cell(43,10,' ', 0, 0,'R','true');
	$pdf->Cell(43,10,' ', 0, 0,'R','true');
	$pdf->Cell(60,10,'  ', 0, 1,'C','true');

	//Adjust XY
	$pdf->SetXY($x+10, $y+42); 
	$myx=$x+10;
	$myy=$y+42;

	//Set Style
	$pdf->SetFont('Helvetica','U',12);
	$pdf->SetFillColor(0,0,0);
	$pdf->SetTextColor(0,0,0);

	//HEADERS
	$pdf->Cell(40,7,'Invoice Number', 0,0,'C');
	$pdf->Cell(40,7,'Date Created', 0,0,'C');
	$pdf->Cell(40,7,'Leads Assigned', 0,0,'C');
	$pdf->Cell(40,7,'Leads Issued', 0,0,'C');
	$pdf->Cell(40,7,'Amount', 0,1,'C');

	//Set Style
	$pdf->SetFont('Helvetica','',12);
	$leadstotal = 0;
	$issuedtotal = 0;
	$amounttotal = 0;
	foreach($pending_invoices as $inv){
		$view_assigned = date("d/m/Y",strtotime($inv->date_created));
		
		$leadstotal += $inv->leads;
		$issuedtotal += $inv->issued;
		$amounttotal += $inv->amount;

		$pdf->Cell(40,6,$inv->invoice_no,0,0,'C');
		$pdf->Cell(40,6,$view_assigned,0,0,'C');
		$pdf->Cell(40,6,$inv->leads,0,0,'C');
		$pdf->Cell(40,6,$inv->issued,0,0,'C');
		$pdf->Cell(40,6,"$" . $inv->amount,0,1,'C');
	}

	$pdf->SetFont('Helvetica','B',12);
	$pdf->Cell(40,6,"Total","T",0,'C');
	$pdf->Cell(40,6,"","T",0,'C');
	$pdf->Cell(40,6,$leadstotal,"T",0,'C');
	$pdf->Cell(40,6,$issuedtotal,"T",0,'C');
	$pdf->Cell(40,6,"$" . $amounttotal,"T",1,'C');
	$pdf->Ln();
endif;

//PAID INVOICES
if(in_array('Paid', $statuses)&&count($paid_invoices)>0):
	$pdf->SetFont('Helvetica','',14);
	$pdf->SetFillColor(224,224,224);
	$pdf->SetTextColor(0,0,0);
	$pdf->Cell(100,10,'Paid Invoices', 0, 0,'L','true');
	$pdf->Cell(43,10,' ', 0, 0,'R','true');
	$pdf->Cell(43,10,' ', 0, 0,'R','true');
	$pdf->Cell(43,10,' ', 0, 0,'R','true');
	$pdf->Cell(60,10,'  ', 0, 1,'C','true');

	
	$pdf->SetFont('Helvetica','U',12);
	$pdf->SetFillColor(0,0,0);
	$pdf->SetTextColor(0,0,0);
	$pdf->Cell(40,7,'Invoice Number', 0,0,'C');
	$pdf->Cell(40,7,'Date Created', 0,0,'C');
	$pdf->Cell(40,7,'Leads Assigned', 0,0,'C');
	$pdf->Cell(40,7,'Leads Issued', 0,0,'C');
	$pdf->Cell(40,7,'Amount', 0,1,'C');


	$pdf->SetFont('Helvetica','',12);
	$leadstotal = 0;
	$issuedtotal = 0;
	$amounttotal = 0;
	foreach($paid_invoices as $inv){
		$view_assigned = date("d/m/Y",strtotime($inv->date_created));
		
		$leadstotal += $inv->leads;
		$issuedtotal += $inv->issued;
		$amounttotal += $inv->amount;

		$pdf->Cell(40,6,$inv->invoice_no,0,0,'C');
		$pdf->Cell(40,6,$view_assigned,0,0,'C');
		$pdf->Cell(40,6,$inv->leads,0,0,'C');
		$pdf->Cell(40,6,$inv->issued,0,0,'C');
		$pdf->Cell(40,6,"$" . $inv->amount,0,1,'C');
		
	}

	
	$pdf->SetFont('Helvetica','B',12);
	$pdf->Cell(40,6,"Total","T",0,'C');
	$pdf->Cell(40,6,"","T",0,'C');
	$pdf->Cell(40,6,$leadstotal,"T",0,'C');
	$pdf->Cell(40,6,$issuedtotal,"T",0,'C');
	$pdf->Cell(40,6,"$" . $amounttotal,"T",1,'C');
	$pdf->Ln();
endif;


//CONTESTED INVOICES
if(in_array('Contested', $statuses)&&count($contested_invoices)>0):
	$pdf->SetFont('Helvetica','',14);
	$pdf->SetFillColor(224,224,224);
	$pdf->SetTextColor(0,0,0);
	$pdf->Cell(100,10,'Contested Invoices', 0, 0,'L','true');
	$pdf->Cell(43,10,' ', 0, 0,'R','true');
	$pdf->Cell(43,10,' ', 0, 0,'R','true');
	$pdf->Cell(43,10,' ', 0, 0,'R','true');
	$pdf->Cell(60,10,'  ', 0, 1,'C','true');


	$pdf->SetFont('Helvetica','U',12);
	$pdf->SetFillColor(0,0,0);
	$pdf->SetTextColor(0,0,0);
	$pdf->Cell(40,7,'Invoice Number', 0,0,'C');
	$pdf->Cell(40,7,'Date Created', 0,0,'C');
	$pdf->Cell(40,7,'Leads Assigned', 0,0,'C');
	$pdf->Cell(40,7,'Leads Issued', 0,0,'C');
	$pdf->Cell(40,7,'Amount', 0,1,'C');


	$pdf->SetFont('Helvetica','',12);
	$leadstotal = 0;
	$issuedtotal = 0;
	$amounttotal = 0;
	foreach($contested_invoices as $inv){
		$view_assigned = date("d/m/Y",strtotime($inv->date_created));
		
		$leadstotal += $inv->leads;
		$issuedtotal += $inv->issued;
		$amounttotal += $inv->amount;

		$pdf->Cell(40,6,$inv->invoice_no,0,0,'C');
		$pdf->Cell(40,6,$view_assigned,0,0,'C');
		$pdf->Cell(40,6,$inv->leads,0,0,'C');
		$pdf->Cell(40,6,$inv->issued,0,0,'C');
		$pdf->Cell(40,6,"$" . $inv->amount,0,1,'C');
		
	}

	
	$pdf->SetFont('Helvetica','B',12);
	$pdf->Cell(40,6,"Total","T",0,'C');
	$pdf->Cell(40,6,"","T",0,'C');
	$pdf->Cell(40,6,$leadstotal,"T",0,'C');
	$pdf->Cell(40,6,$issuedtotal,"T",0,'C');
	$pdf->Cell(40,6,"$" . $amounttotal,"T",1,'C');
	$pdf->Ln();
endif;


//CANCELLED INVOICES
//var_dump($cancelled_invoices);
if(in_array('Cancelled', $statuses)&&count($cancelled_invoices)>0):
	$pdf->SetFont('Helvetica','',14);
	$pdf->SetFillColor(224,224,224);
	$pdf->SetTextColor(0,0,0);
	$pdf->Cell(100,10,'Cancelled Invoices', 0, 0,'L','true');
	$pdf->Cell(43,10,' ', 0, 0,'R','true');
	$pdf->Cell(43,10,' ', 0, 0,'R','true');
	$pdf->Cell(43,10,' ', 0, 0,'R','true');
	$pdf->Cell(60,10,'  ', 0, 1,'C','true');


	$pdf->SetFont('Helvetica','U',12);
	$pdf->SetFillColor(0,0,0);
	$pdf->SetTextColor(0,0,0);
	$pdf->Cell(40,7,'Invoice Number', 0,0,'C');
	$pdf->Cell(40,7,'Date Created', 0,0,'C');
	$pdf->Cell(40,7,'Leads Assigned', 0,0,'C');
	$pdf->Cell(40,7,'Leads Issued', 0,0,'C');
	$pdf->Cell(40,7,'Amount', 0,1,'C');


	$pdf->SetFont('Helvetica','',12);

	$leadstotal = 0;
	$issuedtotal = 0;
	$amounttotal = 0;
	foreach($cancelled_invoices as $inv){
		$view_assigned = date("d/m/Y",strtotime($inv->date_created));
		
		$leadstotal += $inv->leads;
		$issuedtotal += $inv->issued;
		$amounttotal += $inv->amount;

		$pdf->Cell(40,6,$inv->invoice_no,0,0,'C');
		$pdf->Cell(40,6,$view_assigned,0,0,'C');
		$pdf->Cell(40,6,$inv->leads,0,0,'C');
		$pdf->Cell(40,6,$inv->issued,0,0,'C');
		$pdf->Cell(40,6,"$" . $inv->amount,0,1,'C');
	}

	
	$pdf->SetFont('Helvetica','B',12);
	$pdf->Cell(40,6,"Total","T",0,'C');
	$pdf->Cell(40,6,"","T",0,'C');
	$pdf->Cell(40,6,$leadstotal,"T",0,'C');
	$pdf->Cell(40,6,$issuedtotal,"T",0,'C');
	$pdf->Cell(40,6,"$" . $amounttotal,"T",1,'C');
	$pdf->Ln();
endif;


//WAIVED INVOICES
if(in_array('Waived', $statuses)&&count($waived_invoices)>0):
	$pdf->SetFont('Helvetica','',14);
	$pdf->SetFillColor(224,224,224);
	$pdf->SetTextColor(0,0,0);
	$pdf->Cell(100,10,'Waived Invoices', 0, 0,'L','true');
	$pdf->Cell(43,10,' ', 0, 0,'R','true');
	$pdf->Cell(43,10,' ', 0, 0,'R','true');
	$pdf->Cell(43,10,' ', 0, 0,'R','true');
	$pdf->Cell(60,10,'  ', 0, 1,'C','true');


	$pdf->SetFont('Helvetica','U',12);
	$pdf->SetFillColor(0,0,0);
	$pdf->SetTextColor(0,0,0);
	$pdf->Cell(40,7,'Invoice Number', 0,0,'C');
	$pdf->Cell(40,7,'Date Created', 0,0,'C');
	$pdf->Cell(40,7,'Leads Assigned', 0,0,'C');
	$pdf->Cell(40,7,'Leads Issued', 0,0,'C');
	$pdf->Cell(40,7,'Amount', 0,1,'C');

	$pdf->SetFont('Helvetica','',12);
	$leadstotal = 0;
	$issuedtotal = 0;
	$amounttotal = 0;
	foreach($waived_invoices as $inv){
		$view_assigned = date("d/m/Y",strtotime($inv->date_created));
		
		$leadstotal += $inv->leads;
		$issuedtotal += $inv->issued;
		$amounttotal += $inv->amount;

		$pdf->Cell(40,6,$inv->invoice_no,0,0,'C');
		$pdf->Cell(40,6,$view_assigned,0,0,'C');
		$pdf->Cell(40,6,$inv->leads,0,0,'C');
		$pdf->Cell(40,6,$inv->issued,0,0,'C');
		$pdf->Cell(40,6,"$" . $inv->amount,0,1,'C');
	}
	
	$pdf->SetFont('Helvetica','B',12);
	$pdf->Cell(40,6,"Total","T",0,'C');
	$pdf->Cell(40,6,"","T",0,'C');
	$pdf->Cell(40,6,$leadstotal,"T",0,'C');
	$pdf->Cell(40,6,$issuedtotal,"T",0,'C');
	$pdf->Cell(40,6,"$" . $amounttotal,"T",1,'C');
	$pdf->Ln();
endif;


//OTHERS INVOICES
if(in_array('Others', $statuses)&&count($others_invoices)>0):
	$pdf->SetFont('Helvetica','',14);
	$pdf->SetFillColor(224,224,224);
	$pdf->SetTextColor(0,0,0);
	$pdf->Cell(100,10,'Others Invoices', 0, 0,'L','true');
	$pdf->Cell(43,10,' ', 0, 0,'R','true');
	$pdf->Cell(43,10,' ', 0, 0,'R','true');
	$pdf->Cell(43,10,' ', 0, 0,'R','true');
	$pdf->Cell(60,10,'  ', 0, 1,'C','true');

	$pdf->SetFont('Helvetica','U',12);
	$pdf->SetFillColor(0,0,0);
	$pdf->SetTextColor(0,0,0);
	$pdf->Cell(40,7,'Invoice Number', 0,0,'C');
	$pdf->Cell(40,7,'Date Created', 0,0,'C');
	$pdf->Cell(40,7,'Leads Assigned', 0,0,'C');
	$pdf->Cell(40,7,'Leads Issued', 0,0,'C');
	$pdf->Cell(40,7,'Amount', 0,1,'C');

	$pdf->SetFont('Helvetica','',12);

	$leadstotal = 0;
	$issuedtotal = 0;
	$amounttotal = 0;
	foreach($others_invoices as $inv){
		$view_assigned = date("d/m/Y",strtotime($inv->date_created));
		
		$leadstotal += $inv->leads;
		$issuedtotal += $inv->issued;
		$amounttotal += $inv->amount;

		$pdf->Cell(40,6,$inv->invoice_no,0,0,'C');
		$pdf->Cell(40,6,$view_assigned,0,0,'C');
		$pdf->Cell(40,6,$inv->leads,0,0,'C');
		$pdf->Cell(40,6,$inv->issued,0,0,'C');
		$pdf->Cell(40,6,"$" . $inv->amount,0,1,'C');
	}

	$pdf->SetFont('Helvetica','B',12);
	$pdf->Cell(40,6,"Total","T",0,'C');
	$pdf->Cell(40,6,"","T",0,'C');
	$pdf->Cell(40,6,$leadstotal,"T",0,'C');
	$pdf->Cell(40,6,$issuedtotal,"T",0,'C');
	$pdf->Cell(40,6,"$" . $amounttotal,"T",1,'C');
	$pdf->Ln();
endif;


$path="files/summary.pdf";
$pdf->Output($path,'F');

//db add end
//}

?>