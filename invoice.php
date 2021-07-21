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
	function Header()
    {
        $this->AddFont('Calibri','','calibri.php'); 
        $this->SetFont('Calibri','',11);
        // $this->Cell(80);
        $this->SetXY(0,5);

        $this->setFillColor(68,84,106); 
        $this->Cell(17,15,'',0,0,'L',1);
        $this->SetX($this->GetX()+3);
        $this->Cell(30,15,$this->Image('Logo_ImageOnly.png',$this->GetX(),$this->GetY()-3,-200),0,0,'L');

        $this->AddFont('Calibri-Bold','B','calibrib.php'); 
        $this->SetFont('Calibri-Bold','B',18);
        $this->SetTextColor(69,90,115);
        $this->SetXY($this->GetX(),$this->GetY()+6);
        $this->Cell(147,15,'TAX INVOICE',0,0,'R');
        $this->AddFont('Calibri','','calibri.php'); 
        $this->SetFont('Calibri','',11);

        $this->SetXY($this->GetX()+3,$this->GetY()-6);
        $this->setFillColor(46,117,182); 
        $this->Cell(17,15,'',0,0,'L',1);
    }
    
	function Footer()
	{
		global $fsp_num;
		global $name;
		$this->SetY(-15);
		$this->AddFont('Calibri','','calibri.php'); 
		$this->SetFont('Calibri','',11);
		$this->SetTextColor(103,173,233);

		$this->Cell(0,10,$this->Image('logo.png',$this->GetX(),$this->GetY()-2,-200),0,0,'L');	
		$this->Cell(0,10,'www.eliteinsure.co.nz | Page '.$this->PageNo(),0,1,'R');
		$this->SetTextColor(0,0,0);
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

$invoice = $_GET['id'];
$invoice_query="SELECT * FROM invoices WHERE id='$invoice' Limit 1";
$invoice_result=mysqli_query($con,$invoice_query) or die('Could not look up user information; ' . mysqli_error($con));
$invoice_rows = mysqli_fetch_array($invoice_result);



extract($invoice_rows);

$client_leads = json_decode($leads);
$client_issued = json_decode($issued);

$adviser_query="SELECT * FROM adviser_tbl WHERE id='$adviser_id' Limit 1";
$adviser_result=mysqli_query($con,$adviser_query) or die('Could not look up user information; ' . mysqli_error($con));
$adviser_rows = mysqli_fetch_array($adviser_result);

extract($adviser_rows);
$adv_name = $name;
$desc=json_decode($description);
$invoice_date=$date_created;				//Due date
$until=$date_to;					

$invoice_date=$date_created;			
$invoice_num=$number;									//Statement Week
$other_value=isset($others)?$others:0;		//Other

if($other_value==''){
	$other_value=0;
}
//Fetch Adviser Data
$searchadv="SELECT * FROM adviser_tbl WHERE id='$adviser_id'";
$search=mysqli_query($con,$searchadv) or die('Could not look up user information; ' . mysqli_error($con));
$rows = mysqli_fetch_array($search);

//Extract Data
$fsp=$rows["fsp_num"];
$advisor_address=$rows["address"];
$leads=$rows["leads"];

$issued=$rows["bonus"];
$fsp_num=$rows['fsp_num'];
$email=$rows['email'];
$company_name=$rows['company_name'];
//convert to 2 decimal number end

$search_leads="SELECT * FROM clients_tbl WHERE assigned_to='$adviser_id' AND assigned_date<='$until' AND assigned_date>='$date_from' AND lead_by!='Telemarketer' AND lead_by!='Self-Generated' AND status!='Cancelled'";


$leads_exec =mysqli_query($con,$search_leads) or die('Could not look up user information; ' . mysqli_error($con));

$agreement_leads = array();
$seen_leads = array();
 while($row = mysqli_fetch_array($leads_exec)){
 	switch($row["status"]){
 		case "Seen":
 			$seen_leads[] = $row["id"];
 		break;
 		case "Agreement":
 			$agreement_leads[] = $row["id"];
 		break;
	}
 }
 $count_leads = count($seen_leads);
 $count_agreement = count($agreement_leads);



$search_issued="SELECT * FROM issued_clients_tbl i LEFT JOIN clients_tbl c ON i.name = c.id WHERE c.assigned_to='$adviser_id' AND  i.date_issued<='$until' AND i.date_issued>=$date_from AND c.lead_by!='Telemarketer' AND lead_by!='Self-Generated'";
//Remove c.lead_by!='Telemarketer' to include leads from telemarketers
$issued_exec=mysqli_query($con,$search_issued) or die('Could not look up user information; ' . mysqli_error($con));
$count_issued = mysqli_num_rows($issued_exec);


$pdf = new PDF('P', 'mm', 'Legal');
$x = $pdf->GetX();
$y = $pdf->GetY();

//page 1
$pdf->AddPage('P', 'Legal');

$pdf->SetFillColor(224,224,224);
// $pdf->Image('logo.png',10,10,-160);
$pdf->AddFont('Calibri-Bold','B','calibrib.php'); 
$pdf->SetFont('Calibri-Bold','B',14);
$pdf->SetTextColor(0,0,0);
$pdf->Cell(0,20,'',"0","1","C");
$pdf->SetTextColor(0,0,0);
$pdf->AddFont('Calibri-Bold','B','calibrib.php'); 
$pdf->SetFont('Calibri-Bold','B',11);
$pdf->SetFillColor(224,224,224);

$pdf->SetY($y+33);
$pdf->AddFont('Calibri','','calibri.php'); 
$pdf->SetFont('Calibri','',11);
$pdf->MultiCell(55,6,"3G/39 Mackelvie Street Grey Lynn 1021 Auckland New Zealand 0508 123 467",0,"L",false);

$pdf->SetTextColor(44,44,44);
$pdf->SetXY($x+100, $y+35); // position of text1, numerical, of course, not x1 and y1
$pdf->AddFont('Calibri-Bold','B','calibrib.php'); 
$pdf->SetFont('Calibri-Bold','B',11);
$pdf->Write(0, 'Phone');
$pdf->SetTextColor(0,0,0);

$pdf->SetXY($x+120, $y+35); // position of text1, numerical, of course, not x1 and y1
$pdf->AddFont('Calibri','','calibri.php'); 
$pdf->SetFont('Calibri','',11);
$pdf->Write(0, '0508 123 467');


$pdf->SetXY($x+100, $y+40); // position of text1, numerical, of course, not x1 and y1
$pdf->AddFont('Calibri-Bold','B','calibrib.php'); 
$pdf->SetFont('Calibri-Bold','B',11);
$pdf->SetTextColor(44,44,44);
$pdf->Write(0, 'Website');
$pdf->SetTextColor(0,0,0);

$pdf->SetXY($x+120, $y+40); // position of text1, numerical, of course, not x1 and y1
$pdf->AddFont('Calibri','','calibri.php'); 
$pdf->SetFont('Calibri','',11);
$pdf->Write(0, 'www.eliteinsure.co.nz');

$pdf->SetTextColor(44,44,44);
$pdf->SetXY($x+100, $y+45); // position of text1, numerical, of course, not x1 and y1
$pdf->AddFont('Calibri-Bold','B','calibrib.php'); 
$pdf->SetFont('Calibri-Bold','B',11);
$pdf->Write(0, 'Email');
$pdf->SetTextColor(0,0,0);

$pdf->SetXY($x+120, $y+45); // position of text1, numerical, of course, not x1 and y1
$pdf->AddFont('Calibri','','calibri.php'); 
$pdf->SetFont('Calibri','',11);
$pdf->Write(0, 'admin@eliteinsure.co.nz');

$pdf->SetTextColor(12,31,69);
$pdf->SetXY($x+100, $y+60); // position of text1, numerical, of course, not x1 and y1
$pdf->AddFont('Calibri-Bold','B','calibrib.php'); 
$pdf->SetFont('Calibri-Bold','B',11);
$pdf->Write(0, 'Invoice Date');

$pdf->SetTextColor(0,0,0);

$pdf->SetXY($x+150, $y+60); // position of text1, numerical, of course, not x1 and y1
$pdf->AddFont('Calibri','','calibri.php'); 
$pdf->SetFont('Calibri','',11);
$pdf->Write(0, $invoice_date);

$pdf->SetTextColor(12,31,69);
$pdf->SetXY($x+100, $y+67); // position of text1, numerical, of course, not x1 and y1
$pdf->AddFont('Calibri-Bold','B','calibrib.php'); 
$pdf->SetFont('Calibri-Bold','B',11);
$pdf->Write(0, 'Due Date');
$pdf->SetTextColor(0,0,0);

$pdf->SetXY($x+150, $y+67); // position of text1, numerical, of course, not x1 and y1
$pdf->AddFont('Calibri','','calibri.php'); 
$pdf->SetFont('Calibri','',11);
$pdf->Write(0, $due_date);

$pdf->SetTextColor(12,31,69);
$pdf->SetXY($x+100, $y+74); // position of text1, numerical, of course, not x1 and y1
$pdf->AddFont('Calibri-Bold','B','calibrib.php'); 
$pdf->SetFont('Calibri-Bold','B',11);
$pdf->Write(0, 'Invoice Number');
$pdf->SetTextColor(0,0,0);


$pdf->SetXY($x+150, $y+74); // position of text1, numerical, of course, not x1 and y1
$pdf->AddFont('Calibri','','calibri.php'); 
$pdf->SetFont('Calibri','',11);
$pdf->Write(0, $invoice_num);

$pdf->SetTextColor(12,31,69);
$pdf->SetXY($x+100, $y+81); // position of text1, numerical, of course, not x1 and y1
$pdf->AddFont('Calibri-Bold','B','calibrib.php'); 
$pdf->SetFont('Calibri-Bold','B',11);
$pdf->Write(0, 'GST Number');
$pdf->SetTextColor(0,0,0);

$pdf->SetXY($x+150, $y+81); // position of text1, numerical, of course, not x1 and y1
$pdf->AddFont('Calibri','','calibri.php'); 
$pdf->SetFont('Calibri','',11);
$pdf->Write(0,'119-074-304');


$pdf->SetTextColor(12,31,69);
$pdf->SetXY($x+100, $y+88); // position of text1, numerical, of course, not x1 and y1
$pdf->AddFont('Calibri-Bold','B','calibrib.php'); 
$pdf->SetFont('Calibri-Bold','B',11);
$pdf->Write(0, 'Client Code');

$pdf->SetTextColor(0,0,0);
$pdf->SetXY($x+150, $y+88); // position of text1, numerical, of course, not x1 and y1
$pdf->AddFont('Calibri','','calibri.php'); 
$pdf->SetFont('Calibri','',11);
$pdf->Write(0,$fsp_num);

/*
$pdf->SetTextColor(12,31,69);
$pdf->SetXY($x+100, $y+95); // position of text1, numerical, of course, not x1 and y1
$pdf->AddFont('Calibri-Bold','B','calibrib.php'); 
$pdf->SetFont('Calibri-Bold','B',11);
$pdf->Write(0, 'Page');


$pdf->SetTextColor(0,0,0);
$pdf->SetXY($x+150, $y+95); // position of text1, numerical, of course, not x1 and y1
$pdf->AddFont('Calibri','','calibri.php'); 
$pdf->SetFont('Calibri','',11);
$pdf->Write(0,$pdf->getPage());
*/



$pdf->SetXY($x+10, $y+60); // position of text1, numerical, of course, not x1 and y1
$pdf->AddFont('Calibri-Bold','B','calibrib.php'); 
$pdf->SetFont('Calibri-Bold','B',14);
$pdf->Cell(0,10,'TAX INVOICE',"0","1","L");

$pdf->SetXY($x+10, $y+75); // position of text1, numerical, of course, not x1 and y1
$pdf->AddFont('Calibri-Bold','B','calibrib.php'); 
$pdf->SetFont('Calibri-Bold','B',11);
$pdf->Cell(50,5,'To:',"0","0","L");
// $pdf->Write(0,'To: ');

$pdf->SetXY($x+20, $y+75); // position of text1, numerical, of course, not x1 and y1

$pdf->AddFont('Calibri-Bold','B','calibrib.php'); 
$pdf->SetFont('Calibri-Bold','B',11);
$pdf->MultiCell(50,5,"$adv_name",0,"L",false);
// $pdf->Write(0,$adv_name->name);

if($company_name != '' || $company_name != null) :
	$pdf->SetXY($x+20, $pdf->getY()+3); // position of text1, numerical, of course, not x1 and y1
	$pdf->AddFont('Calibri-Bold','B','calibrib.php'); 
	$pdf->SetFont('Calibri-Bold','B',11);
	$pdf->MultiCell(50,5,"$company_name",0,"L",false);
endif;

$pdf->AddFont('Calibri','','calibri.php'); 
$pdf->SetFont('Calibri','',11);
$pdf->SetXY($x+20, $pdf->getY()+3);
$pdf->MultiCell(50,5,"$advisor_address",0,"L",false);


//$pdf->SetXY($x+10, $y+60); // position of text1, numerical, of course, not x1 and y1
// $pdf->SetXY($x+10, $y+118); 
if($pdf->getY() <= 118)
    $pdf->SetXY($x+10, $y+118); 
else
    $pdf->SetXY($x+10, $pdf->getY()+5);

$pdf->AddFont('Calibri','','calibri.php'); 
$pdf->SetFont('Calibri','',14);
$pdf->SetTextColor(255,255,255);
$pdf->SetDrawColor(91,155,213);
$pdf->SetFillColor(68,117,161);
$pdf->Cell(100,10,'DESCRIPTION', 0, 0,'L','true');
$pdf->Cell(43,10,' ', 0, 0,'R','true');
$pdf->Cell(60,10,'  Total', 0, 1,'C','true');

$pdf->SetTextColor(0,0,0);
$show_desc='';


function desc($desc){
	switch ($desc) {
		case 'charged':
			$show_desc='Leads Charged';

			break;
		case 'issued':
			$show_desc='Leads Issued';
			break;

		default:
			$show_desc=$desc;
			break;
	}
	return $show_desc;

}

$display_leads=$count_leads*$leads;
$display_issued=$count_issued*$issued;

function desc_val($desc){
	global $display_leads;
	global $display_issued;
	global $other_value;
	switch ($desc) {
		case 'charged':
			$show_desc=$display_leads;
			
			break;
		case 'issued':
			$show_desc=$display_issued;
			break;

		default:
			$show_desc=$other_value;
			break;
	}
return $show_desc;

}
//formula


$sub_total=0;
$totalleadscharged = 0;
$totalleadsissued = 0;
$totalothers = 0;

$pdf->SetXY($x+10, $y+127); 
$pdf->AddFont('Calibri','','calibri.php'); 
$pdf->SetFont('Calibri','',11);
$pdf->Cell(100,10,desc($desc[0]), 0, 0,'L');
$pdf->Cell(30,10,' ', 0, 0,'R');
$pdf->Cell(55,10,'$'.number_format(desc_val($desc[0]),2), 0, 1,'R');
$sub_total+=desc_val($desc[0]);
$totalleadscharged=desc_val($desc[0]);
//$pdf->Cell(55,10,'$'.desc_val($desc[0]), 0, 1,'R');

if(count($desc)>1){
	$pdf->SetXY($x+10, $y+132); 
	$pdf->AddFont('Calibri','','calibri.php'); 
	$pdf->SetFont('Calibri','',11);
	$pdf->Cell(100,10,desc($desc[1]), 0, 0,'L');
	$pdf->Cell(30,10,' ', 0, 0,'R');
	$pdf->Cell(55,10,'$'.number_format(desc_val($desc[1]),2), 0, 1,'R');
	$sub_total+=desc_val($desc[1]);
	$totalleadsissued=desc_val($desc[1]);
	if(count($desc)>2){
			$pdf->SetXY($x+10, $y+137); 
			$pdf->AddFont('Calibri','','calibri.php'); 
			$pdf->SetFont('Calibri','',11);
			$pdf->Cell(100,10,desc($desc[2]), 0, 0,'L');
			$pdf->Cell(30,10,' ', 0, 0,'R');
			$pdf->Cell(55,10,'$'.number_format(desc_val($desc[2]),2), 0, 1,'R');
			$sub_total+=desc_val($desc[2]);
			$totalothers=desc_val($desc[2]);
		}
}

//$sub_total=$display_leads+$display_issued+$other_value;
$if_page2=$display_leads+$display_issued+$count_agreement;


$pdf->SetXY($x+10, $y+145); 

$pdf->Cell(100,0,' ', 0, 0,'L');
$pdf->Cell(30,0,'', 0, 0,'R');
$pdf->Cell(55,0,'','T', 0, 1,'R');



$pdf->SetXY($x+10, $y+160); 
$pdf->AddFont('Calibri','','calibri.php'); 
$pdf->SetFont('Calibri','',11);
$pdf->Cell(100,0,' ', 0, 0,'L');
$pdf->Cell(30,0,'Sub Total', 0, 0,'R');
$pdf->Cell(55,0,'$'.number_format($sub_total,2), 0, 1,'R');

$pdf->SetXY($x+10, $y+167); 
$pdf->AddFont('Calibri','','calibri.php'); 
$pdf->SetFont('Calibri','',11);
$pdf->Cell(100,0,' ', 0, 0,'L');
$pdf->Cell(30,0,'Total GST 15%', 0, 0,'R');
$pdf->Cell(55,0,'$'.number_format($sub_total*.15,2), 0, 1,'R');

$pdf->SetXY($x+10, $y+172); 
$pdf->Cell(100,0,' ', 0, 0,'L');
$pdf->Cell(30,0,'', 0, 0,'R');
$pdf->Cell(55,0,'','T', 0, 1,'R');


$total_payable=$sub_total+($sub_total*.15);

$pdf->SetXY($x+10, $y+179); 
$pdf->AddFont('Calibri-Bold','B','calibrib.php'); 
$pdf->SetFont('Calibri-Bold','B',11);
$pdf->Cell(100,0,' ', 0, 0,'L');
$pdf->Cell(30,0,'TOTAL PAYABLE', 0, 0,'R');
$pdf->SetTextColor(255,0,0);
$pdf->Cell(55,0,'$'.number_format($total_payable,2), 0, 1,'R');

$pdf->SetTextColor(12,31,69);
$pdf->SetXY($x+10, $y+190); // position of text1, numerical, of course, not x1 and y1
$pdf->AddFont('Calibri-Italic','I','calibrii.php'); 
$pdf->SetFont('Calibri-Italic','I',11);
$pdf->Cell(0,10,'If payment is not made by due date, interest may be charged on outstanding balance',"0","1","L");

$pdf->SetTextColor(0,0,0);
$pdf->SetXY($x+10, $y+200); 
$pdf->Cell(0,0,' ',0, 0, 0,'C');


$pdf->SetXY($x+10, $y+201); // position of text1, numerical, of course, not x1 and y1
$pdf->AddFont('Calibri-Bold','B','calibrib.php'); 
$pdf->SetFont('Calibri-Bold','B',14);
$pdf->SetTextColor(91,155,213);
// $pdf->SetTextColor(255,255,255);
$pdf->SetDrawColor(91,155,213);
$pdf->SetFillColor(68,117,161);
$pdf->Cell(200,10,'PAYMENT ADVICE', 'B', 1,'L');
// $pdf->Cell(40,10,'',"0","0","L",'true');
// $pdf->Cell(60,10,'',"0","1","C",'true');

$pdf->SetTextColor(0,0,0);
$pdf->SetXY($x+10, $y+220); // position of text1, numerical, of course, not x1 and y1
$pdf->AddFont('Calibri','','calibri.php'); 
$pdf->SetFont('Calibri','',11);
$pdf->SetFillColor(0,0,0);
$pdf->AddFont('Calibri-Bold','B','calibrib.php'); 
$pdf->SetFont('Calibri-Bold','B',11);
$pdf->Cell(40,5,'Name',"0","0","L");
$pdf->AddFont('Calibri','','calibri.php'); 
$pdf->SetFont('Calibri','',11);
$pdf->MultiCell(60,5,"$adv_name",0,"L",false);
// $pdf->Cell(40,0,$adv_name,"0",0,"L");
$pdf->Cell(60,0,'',"0","1","C");

$pdf->SetXY($x+10, $y+227); // position of text1, numerical, of course, not x1 and y1
$pdf->AddFont('Calibri','','calibri.php'); 
$pdf->SetFont('Calibri','',11);
$pdf->SetFillColor(0,0,0);
$pdf->AddFont('Calibri-Bold','B','calibrib.php'); 
$pdf->SetFont('Calibri-Bold','B',11);

$pdf->Cell(40,5,'Invoice Number',"0","0","L");
$pdf->AddFont('Calibri','','calibri.php'); 
$pdf->SetFont('Calibri','',11);
$pdf->Cell(40,5,$invoice_num,"0","0","L");
$pdf->Cell(60,0,'',"0","1","C");


$pdf->SetXY($x+10, $y+234); // position of text1, numerical, of course, not x1 and y1
$pdf->AddFont('Calibri','','calibri.php'); 
$pdf->SetFont('Calibri','',11);
$pdf->SetFillColor(0,0,0);
$pdf->AddFont('Calibri-Bold','B','calibrib.php'); 
$pdf->SetFont('Calibri-Bold','B',11);
$pdf->Cell(40,5,'Due Date',"0","0","L");
$pdf->AddFont('Calibri','','calibri.php'); 
$pdf->SetFont('Calibri','',11);
$pdf->Cell(40,5,$due_date,"0","0","L");
$pdf->Cell(60,0,'',"0","1","C");

$pdf->SetXY($x+10, $y+241); // position of text1, numerical, of course, not x1 and y1
$pdf->AddFont('Calibri','','calibri.php'); 
$pdf->SetFont('Calibri','',11);
$pdf->SetFillColor(0,0,0);
$pdf->AddFont('Calibri-Bold','B','calibrib.php'); 
$pdf->SetFont('Calibri-Bold','B',11);
$pdf->Cell(40,5,'Total Due',"0","0","L");
$pdf->AddFont('Calibri','','calibri.php'); 
$pdf->SetFont('Calibri','',11);
$pdf->Cell(40,5,'$'.number_format($total_payable,2),"0","0","L");
$pdf->Cell(60,0,'',"0","1","C");


$pdf->SetXY($x+120, $y+220); // position of text1, numerical, of course, not x1 and y1

$pdf->AddFont('Calibri','','calibri.php'); 
$pdf->SetFont('Calibri','',11);
$pdf->MultiCell(85,5,"Direct Credit
Please make payment into the following account: Eliteinsure Ltd, ANZ Bank, 06-0254-0426124-00. Please use the reference ".$fsp_num.". ",0,"L",false);

//Initialize array variable
  $leadsdata = array();
  $issueddata = array();

//Print array in JSON format
$payable_leads = 0;
$payable_issued_leads = 0;
$leadsjson = json_encode($leadsdata);
if($if_page2>0):

	$pdf->AddPage('P', 'Legal');
	// $pdf->Image('logo.png',10,10,-160);
	$pdf->AddFont('Calibri-Bold','B','calibrib.php'); 
$pdf->SetFont('Calibri-Bold','B',14);
	$pdf->SetTextColor(0,42,160);


	$pdf->SetXY($x+10, $y+30); 

	//LEADS CLIENTS
	if(in_array('charged', $desc)):
	$pdf->AddFont('Calibri-Bold','B','calibrib.php'); 
	$pdf->SetFont('Calibri-Bold','B',14);
	// $pdf->SetFillColor(224,224,224);
	// $pdf->SetTextColor(0,0,0);
	// $pdf->Cell(100,10,'PAYABLE LEADS', 0, 0,'L','true');
	// $pdf->Cell(43,10,' ', 0, 0,'R','true');
	// $pdf->Cell(60,10,'  ', 0, 1,'C','true');
	$pdf->SetTextColor(91,155,213);
	$pdf->SetDrawColor(91,155,213);
	$pdf->SetFillColor(68,117,161);
	$pdf->Cell(200,10,'PAYABLE LEADS', 'B', 1,'L');
	$pdf->SetTextColor(0,0,0);

	$pdf->SetXY($x+10, $y+42); 
	$myx=$x+10;
	$myy=$y+42;

	$pdf->AddFont('Calibri','U','calibri.php'); 
	$pdf->SetFont('Calibri','U',11);
	$pdf->SetFillColor(0,0,0);
	$pdf->SetTextColor(0,0,0);
	$pdf->Cell(100,10,'Client Name', 0, 0,'L');
	$pdf->Cell(43,10,'Assigned Date', 0, 0,'R');
	$pdf->Cell(60,10,'', 0, 1,'C');

	$pdf->AddFont('Calibri','','calibri.php'); 
	$pdf->SetFont('Calibri','',11);

	$searchclient="SELECT c.id as id, c.name as client_name,c.assigned_date, a.name as adv_name, l.name as lg_name FROM clients_tbl c LEFT JOIN adviser_tbl a ON c.assigned_to=a.id LEFT JOIN leadgen_tbl l ON c.leadgen=l.id WHERE c.assigned_to='$adviser_id' AND assigned_date<='$until' AND assigned_date>='$date_from'  AND c.lead_by!='Telemarketer' AND c.lead_by!='Self-Generated' AND c.status='Seen'";
	$search=mysqli_query($con,$searchclient) or die('Could not look up user information; ' . mysqli_error($con));
	$payable_leads = mysqli_num_rows($search);
	while($rows = mysqli_fetch_array($search)):
		$client_name=$rows['client_name'];
		$assigned_date=$rows['assigned_date'];
		$leadsdata[]=$rows['id'];

		$view_assigned = date("d/m/Y",strtotime($assigned_date));

		if($pdf->getY() > 322) {
		    $pdf->AddPage();
		    $pdf->setY(30);
		}

		$pdf->Cell(100,10,$client_name, 0, 0,'L');
		$pdf->Cell(43,10,$view_assigned, 0, 0,'R');
		$pdf->Cell(60,10,'', 0, 1,'C');
	endwhile;

	

		$searchclient="SELECT c.id as id, c.name as client_name,c.assigned_date, a.name as adv_name, l.name as lg_name FROM clients_tbl c LEFT JOIN adviser_tbl a ON c.assigned_to=a.id LEFT JOIN leadgen_tbl l ON c.leadgen=l.id WHERE c.assigned_to='$adviser_id' AND assigned_date<='$until' AND assigned_date>='$date_from'  AND c.lead_by!='Telemarketer' AND c.status='Agreement'";
		$search=mysqli_query($con,$searchclient) or die('Could not look up user information; ' . mysqli_error($con));
		if(mysqli_num_rows($search)>0){
			// $pdf->AddFont('Calibri','','calibri.php'); 
			// $pdf->SetFont('Calibri','',14);
			// $pdf->SetFillColor(224,224,224);
			// $pdf->SetTextColor(0,0,0);
			// $pdf->Cell(100,10,'LEADS UNDER AGREEMENT', 0, 0,'L','true');
			// $pdf->Cell(43,10,' ', 0, 0,'R','true');
			// $pdf->Cell(60,10,'  ', 0, 1,'C','true');

			$pdf->AddFont('Calibri-Bold','B','calibrib.php'); 
			$pdf->SetFont('Calibri-Bold','B',14);
			$pdf->SetTextColor(91,155,213);
			// $pdf->SetTextColor(255,255,255);
			$pdf->SetDrawColor(91,155,213);
			$pdf->SetFillColor(68,117,161);
			$pdf->Cell(200,10,'LEADS UNDER AGREEMENT', 'B', 1,'L');

			$pdf->AddFont('Calibri','U','calibri.php'); 
			$pdf->SetFont('Calibri','U',11);
			$pdf->SetFillColor(0,0,0);
			$pdf->SetTextColor(0,0,0);
			$pdf->Cell(100,10,'Client Name', 0, 0,'L');
			$pdf->Cell(43,10,'Assigned Date', 0, 0,'R');
			$pdf->Cell(60,10,'', 0, 1,'C');

			$pdf->AddFont('Calibri','','calibri.php'); 
			$pdf->SetFont('Calibri','',11);
		}

		while($rows = mysqli_fetch_array($search)):
		$client_name=$rows['client_name'];
		$assigned_date=$rows['assigned_date'];
		$leadsdata[]=$rows['id'];

		$view_assigned = date("d/m/Y",strtotime($assigned_date));

		if($pdf->getY() > 322) {
		    $pdf->AddPage();
		    $pdf->setY(30);
		}

		$pdf->Cell(100,10,$client_name, 0, 0,'L');
		$pdf->Cell(43,10,$view_assigned, 0, 0,'R');
		$pdf->Cell(60,10,'', 0, 1,'C');
		endwhile;

	endif;

	//ISSUED CLIENTS PART
	if(in_array('issued', $desc)&&$display_issued>0):
	// $pdf->AddFont('Calibri','','calibri.php'); 
	// $pdf->SetFont('Calibri','',14);
	// $pdf->SetFillColor(224,224,224);
	// $pdf->SetTextColor(0,0,0);
	// $pdf->Cell(100,10,'ISSUED CLIENTS', 0, 0,'L','true');
	// $pdf->Cell(43,10,' ', 0, 0,'R','true');
	// $pdf->Cell(60,10,'  ', 0, 1,'C','true');

	$pdf->AddFont('Calibri-Bold','B','calibrib.php'); 
	$pdf->SetFont('Calibri-Bold','B',14);
	$pdf->SetTextColor(91,155,213);
	// $pdf->SetTextColor(255,255,255);
	$pdf->SetDrawColor(91,155,213);
	$pdf->SetFillColor(68,117,161);
	$pdf->Cell(200,10,'ISSUED CLIENTS', 'B', 1,'L');

	$pdf->AddFont('Calibri','U','calibri.php'); 
	$pdf->SetFont('Calibri','U',11);
	$pdf->SetFillColor(0,0,0);
	$pdf->SetTextColor(0,0,0);
	$pdf->Cell(100,10,'Client Name', 0, 0,'L');
	$pdf->Cell(43,10,'Period Issued', 0, 0,'R');
	$pdf->Cell(60,10,'', 0, 1,'C');

	$searchclient="SELECT c.id as id, c.name as client_name,i.date_issued FROM issued_clients_tbl i LEFT JOIN clients_tbl c ON i.name=c.id WHERE i.assigned_to='$adviser_id' AND i.date_issued<='$until' AND i.date_issued>='$date_from'  AND c.lead_by!='Telemarketer' AND c.lead_by!='Self-Generated'";
	$search=mysqli_query($con,$searchclient) or die('Could not look up user information; ' . mysqli_error($con));
	$payable_issued_leads = mysqli_num_rows($search);
	$pdf->AddFont('Calibri','','calibri.php'); 
	$pdf->SetFont('Calibri','',11);
	$pdf->SetFillColor(0,0,0);
	$pdf->SetTextColor(0,0,0);

		while($rows = mysqli_fetch_array($search)):
		$client_name=$rows['client_name'];
		$date_issued=$rows['date_issued'];
		$issueddata[]=$rows['id'];
		$view_assigned = date("d/m/Y",strtotime($date_issued));
		$view_until=date("d/m/Y",strtotime($until));

		if($pdf->getY() > 322) {
		    $pdf->AddPage();
		    $pdf->setY(30);
		}
		$pdf->Cell(100,10,$client_name, 0, 0,'L');
		$pdf->Cell(43,10,$view_assigned.'-'.$view_until, 0, 0,'R');
		$pdf->Cell(60,10,'', 0, 1,'C');

		endwhile;
	endif;
endif;
$invoice_date_final=substr($invoice_date,6,4).substr($invoice_date,3,2).substr($invoice_date,0,2);

$date_pdf=substr($invoice_date,0,2).substr($invoice_date,3,2).substr($invoice_date,6,4);
$mix="$invoice_num"."- $name -"."$date_pdf";
//$mix="$mydateorig"."-$name-"."BCTI".-date("ymd");
//$path="files/".$mix.".pdf";
$pdf->Output("I","$invoice_num" . ".pdf");
?>