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
		$this->Cell(0,10,'Invoice '. ''.' '.preg_replace("/\([^)]+\)/","",''),0,0,'L');	
		$this->Cell(0,10,'Page '.$this->PageNo(),0,1,'R');
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

$desc=json_decode($description);
$invoice_date=$date_created;				//Due date
$until=$date_to;					

$invoice_date=$date_created;			
$invoice_num=$number;									//Statement Week
$other_value=isset($_POST['other_value'])?$_POST['other_value']:0;		//Other

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

$pdf = new PDF('P', 'mm', 'Legal');
$x = $pdf->GetX();
$y = $pdf->GetY();

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
$pdf->Write(0, 'Invoice Date');

$pdf->SetTextColor(0,0,0);

$pdf->SetXY($x+150, $y+60); // position of text1, numerical, of course, not x1 and y1
$pdf->SetFont('Helvetica','',12);
$pdf->Write(0, $invoice_date);

$pdf->SetTextColor(12,31,69);
$pdf->SetXY($x+100, $y+67); // position of text1, numerical, of course, not x1 and y1
$pdf->SetFont('Helvetica','B',12);
$pdf->Write(0, 'Due Date');
$pdf->SetTextColor(0,0,0);

$pdf->SetXY($x+150, $y+67); // position of text1, numerical, of course, not x1 and y1
$pdf->SetFont('Helvetica','',12);
$pdf->Write(0, $due_date);

$pdf->SetTextColor(12,31,69);
$pdf->SetXY($x+100, $y+74); // position of text1, numerical, of course, not x1 and y1
$pdf->SetFont('Helvetica','B',12);
$pdf->Write(0, 'Invoice Number');
$pdf->SetTextColor(0,0,0);


$pdf->SetXY($x+150, $y+74); // position of text1, numerical, of course, not x1 and y1
$pdf->SetFont('Helvetica','',12);
$pdf->Write(0, $invoice_num);

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

/*
$pdf->SetTextColor(12,31,69);
$pdf->SetXY($x+100, $y+95); // position of text1, numerical, of course, not x1 and y1
$pdf->SetFont('Helvetica','B',12);
$pdf->Write(0, 'Page');


$pdf->SetTextColor(0,0,0);
$pdf->SetXY($x+150, $y+95); // position of text1, numerical, of course, not x1 and y1
$pdf->SetFont('Helvetica','',12);
$pdf->Write(0,$pdf->getPage());
*/



$pdf->SetXY($x+10, $y+60); // position of text1, numerical, of course, not x1 and y1
$pdf->SetFont('Helvetica','',18);
$pdf->Cell(0,10,'Tax Invoice',"0","1","L");

$pdf->SetXY($x+10, $y+75); // position of text1, numerical, of course, not x1 and y1
$pdf->SetFont('Helvetica','',12);
$pdf->Write(0,'To: ');

$pdf->SetXY($x+20, $y+75); // position of text1, numerical, of course, not x1 and y1

$pdf->Write(0,$name);

$pdf->SetXY($x+20, $y+80); // position of text1, numerical, of course, not x1 and y1

$pdf->MultiCell(50,5,"$advisor_address",0,"L",false);


//$pdf->SetXY($x+10, $y+60); // position of text1, numerical, of course, not x1 and y1
$pdf->SetXY($x+10, $y+118); 


$pdf->SetFont('Helvetica','',14);
$pdf->SetFillColor(224,224,224);
$pdf->Cell(100,10,'DESCRIPTION', 0, 0,'L','true');
$pdf->Cell(43,10,' ', 0, 0,'R','true');
$pdf->Cell(60,10,'  Total', 0, 1,'C','true');

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

$count_leads = count($client_leads);
$count_issued = count($client_issued);



$sub_total=0;


$pdf->SetXY($x+10, $y+127); 
$pdf->SetFont('Helvetica','',12);
$pdf->Cell(100,10,desc($desc[0]), 0, 0,'L');
$pdf->Cell(30,10,' ', 0, 0,'R');
$pdf->Cell(55,10,'$'.number_format($leads_charged,2), 0, 1,'R');
$sub_total+=$leads_charged;
//$pdf->Cell(55,10,'$'.desc_val($desc[0]), 0, 1,'R');

if(count($desc)>1){
	$pdf->SetXY($x+10, $y+132); 
	$pdf->SetFont('Helvetica','',12);
	$pdf->Cell(100,10,desc($desc[1]), 0, 0,'L');
	$pdf->Cell(30,10,' ', 0, 0,'R');
	$pdf->Cell(55,10,'$'.number_format($leads_issued,2), 0, 1,'R');
	$sub_total+=$leads_issued;

	if(count($desc)>2){
			$pdf->SetXY($x+10, $y+137); 
			$pdf->SetFont('Helvetica','',13);
			$pdf->Cell(100,10,desc($desc[2]), 0, 0,'L');
			$pdf->Cell(30,10,' ', 0, 0,'R');
			$pdf->Cell(55,10,'$'.number_format($others,2), 0, 1,'R');
			$sub_total+=$others;
		}
}

//$sub_total=$display_leads+$display_issued+$other_value;
$if_page2=$leads_charged+$leads_issued;


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
$pdf->Cell(55,0,'$'.number_format($amount,2), 0, 1,'R');

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

$pdf->Cell(40,0,'Invoice Number',"0","0","L");
$pdf->SetFont('Helvetica','',12);
$pdf->Cell(40,0,$invoice_num,"0","0","L");
$pdf->Cell(60,0,'',"0","1","C");


$pdf->SetXY($x+10, $y+234); // position of text1, numerical, of course, not x1 and y1
$pdf->SetFont('Helvetica','',12);
$pdf->SetFillColor(0,0,0);
$pdf->SetFont('Helvetica','B',12);
$pdf->Cell(40,0,'Due Date',"0","0","L");
$pdf->SetFont('Helvetica','',12);
$pdf->Cell(40,0,$due_date,"0","0","L");
$pdf->Cell(60,0,'',"0","1","C");

$pdf->SetXY($x+10, $y+245); // position of text1, numerical, of course, not x1 and y1
$pdf->SetFont('Helvetica','',12);
$pdf->SetFillColor(0,0,0);
$pdf->SetFont('Helvetica','B',14);
$pdf->Cell(40,0,'Total Due',"0","0","L");
$pdf->SetFont('Helvetica','',14);
$pdf->Cell(40,0,'$'.number_format($total_payable,2),"0","0","L");
$pdf->Cell(60,0,'',"0","1","C");


$pdf->SetXY($x+120, $y+218); // position of text1, numerical, of course, not x1 and y1

$pdf->SetFont('Helvetica','',12);
$pdf->MultiCell(85,5,"Direct Credit
Please make payment into the following account: Eliteinsure Ltd, ANZ Bank, 06-0254-0426124-00. Please use the reference ".$fsp_num.". ",0,"L",false);

//Initialize array variable
  $leadsdata = array();
  $issueddata = array();

//Print array in JSON format
$leadsjson = json_encode($leadsdata);
if($if_page2>0):

	$pdf->AddPage('P', 'Legal');
	$pdf->Image('logo.png',10,10,-160);
	$pdf->SetFont('Helvetica','B',14);
	$pdf->SetTextColor(0,42,160);


	$pdf->SetXY($x+10, $y+30); 

	//LEADS CLIENTS
	if(in_array('charged', $desc)&&$leads_charged>0):
	$pdf->SetFont('Helvetica','',14);
	$pdf->SetFillColor(224,224,224);
	$pdf->SetTextColor(0,0,0);
	$pdf->Cell(100,10,'CLIENTS', 0, 0,'L','true');
	$pdf->Cell(43,10,' ', 0, 0,'R','true');
	$pdf->Cell(60,10,'  ', 0, 1,'C','true');



	$pdf->SetXY($x+10, $y+42); 
	$myx=$x+10;
	$myy=$y+42;

	$pdf->SetFont('Helvetica','U',12);
	$pdf->SetFillColor(0,0,0);
	$pdf->SetTextColor(0,0,0);
	$pdf->Cell(100,10,'Client Name', 0, 0,'L');
	$pdf->Cell(43,10,'Assigned Date', 0, 0,'R');
	$pdf->Cell(60,10,'', 0, 1,'C');

	$pdf->SetFont('Helvetica','',12);

	$wherestring = implode("','",$client_leads);
	$searchclient ="SELECT * FROM clients_tbl WHERE id IN ('" . $wherestring . "')";
	
	$search=mysqli_query($con,$searchclient) or die('Could not look up user information; ' . mysqli_error($con));
		while($rows = mysqli_fetch_array($search)):
		$client_name=$rows['name'];
		$assigned_date=$rows['assigned_date'];
		$view_assigned = date("d/m/Y",strtotime($assigned_date));

		$pdf->Cell(100,10,$client_name, 0, 0,'L');
		$pdf->Cell(43,10,$view_assigned, 0, 0,'R');
		$pdf->Cell(60,10,'', 0, 1,'C');
		endwhile;
	endif;

	//ISSUED CLIENTS PART
	if(in_array('issued', $desc)&&$leads_issued>0):
	$pdf->SetFont('Helvetica','',14);
	$pdf->SetFillColor(224,224,224);
	$pdf->SetTextColor(0,0,0);
	$pdf->Cell(100,10,'ISSUED CLIENTS', 0, 0,'L','true');
	$pdf->Cell(43,10,' ', 0, 0,'R','true');
	$pdf->Cell(60,10,'  ', 0, 1,'C','true');

	$pdf->SetFont('Helvetica','U',12);
	$pdf->SetFillColor(0,0,0);
	$pdf->SetTextColor(0,0,0);
	$pdf->Cell(100,10,'Client Name', 0, 0,'L');
	$pdf->Cell(43,10,'Period Issued', 0, 0,'R');
	$pdf->Cell(60,10,'', 0, 1,'C');

	$wherestring = implode("','",$client_issued);
	$searchclient ="SELECT c.name, i.date_issued FROM issued_clients_tbl i LEFT JOIN clients_tbl c ON i.name = c.id WHERE i.name IN ('" . $wherestring . "') ";
	$search=mysqli_query($con,$searchclient) or die('Could not look up user information; ' . mysqli_error($con));
	//echo $searchclient;
	$pdf->SetFont('Helvetica','',12);
	$pdf->SetFillColor(0,0,0);
	$pdf->SetTextColor(0,0,0);

		while($rows = mysqli_fetch_array($search)):
		$client_name=$rows['name'];
		$date_issued=$rows['date_issued'];

		$view_assigned = date("d/m/Y",strtotime($date_issued));
		$view_until=date("d/m/Y",strtotime($until));
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
$pdf->Output();
//db add end
//}

?>