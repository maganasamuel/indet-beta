<?php
date_default_timezone_set('Pacific/Auckland');
require("fpdf/mc_table.php");
$restrict_session_check = true;
require("database.php");
require("libs/indet_alphanumeric_helper.php");
class PDF extends PDF_MC_Table
{


	function Footer()
	{
		global $invoice_num;
		global $name;
		$this->SetY(-15);
		$this->SetFont('Helvetica','',10);
		$this->SetTextColor(0,0,0);
		$this->Cell(0,10,"Customized Invoice $invoice_num". ''.' '.preg_replace("/\([^)]+\)/","",''),0,0,'L');	
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

$alpha_helper = new INDET_ALPHANUMERIC_HELPER();
//convert post data into an object

$invoice_id = $_GET["id"];

$query = "Select * from customized_invoices WHERE id = $invoice_id";
$result = mysqli_query($con, $query);
$data = mysqli_fetch_assoc($result);
$customized_invoice_data = json_decode($data["data"]);

$invoice_num = $customized_invoice_data->invoice_number;

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
$pdf->Write(0, 'Wesbite');
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
$pdf->Write(0, $customized_invoice_data->invoice_date);

$pdf->SetTextColor(12,31,69);
$pdf->SetXY($x+100, $y+67); // position of text1, numerical, of course, not x1 and y1
$pdf->SetFont('Helvetica','B',12);
$pdf->Write(0, 'Invoice Number');
$pdf->SetTextColor(0,0,0);

$pdf->SetXY($x+150, $y+67); // position of text1, numerical, of course, not x1 and y1
$pdf->SetFont('Helvetica','',12);
$pdf->Write(0, $customized_invoice_data->invoice_number);

$pdf->SetTextColor(12,31,69);
$pdf->SetXY($x+100, $y+74); // position of text1, numerical, of course, not x1 and y1
$pdf->SetFont('Helvetica','B',12);
$pdf->Write(0, 'GST Number');
$pdf->SetTextColor(0,0,0);


$pdf->SetXY($x+150, $y+74); // position of text1, numerical, of course, not x1 and y1
$pdf->SetFont('Helvetica','',12);
$pdf->Write(0, '119-074-304');



$pdf->SetXY($x+10, $y+60); // position of text1, numerical, of course, not x1 and y1
$pdf->SetFont('Helvetica','',18);
$pdf->Cell(0,10,'TAX INVOICE',"0","1","L");

$pdf->SetXY($x+10, $y+75); // position of text1, numerical, of course, not x1 and y1
$pdf->SetFont('Helvetica','B',12);
$pdf->Write(0,'To: ');

$pdf->SetXY($x+20, $y+75); // position of text1, numerical, of course, not x1 and y1

$pdf->SetFont('Helvetica','B',12);
$pdf->Write(0,$customized_invoice_data->company_name);

$pdf->SetXY($x+20, $y+80); // position of text1, numerical, of course, not x1 and y1

$pdf->SetFont('Helvetica','B',12);
$pdf->Write(0,$customized_invoice_data->name);

$pdf->SetFont('Helvetica','',12);
$pdf->SetXY($x+20, $y+85);
$pdf->MultiCell(50,5,"$customized_invoice_data->address",0,"L",false);

//$pdf->SetXY($x+10, $y+60); // position of text1, numerical, of course, not x1 and y1
$pdf->SetXY($x+10, $y+118); 


$pdf->SetFont('Helvetica','B',12);
$pdf->SetTextColor(255,255,255);
$pdf->SetDrawColor(91,155,213);
$pdf->SetFillColor(68,117,161);
$pdf->Cell(70,10,'ITEM', 1, 0,'C','true');
$pdf->Cell(100,10,'DESCRIPTION', 1, 0,'C','true');
$pdf->Cell(30,10,'AMOUNT', 1, 1,'C','true');

$pdf->SetFont('Helvetica','',10);
$pdf->SetFillColor(223,235,247);
$customized_invoice_data->subtotal = 0;

$pdf->SetWidths(array(70,100,30));

$pdf->SetTextColor(0,0,0);
foreach($customized_invoice_data->items as $item){
    $pdf->Row(array($item->name,$item->description, "$" . number_format($item->total_amount,2)),true,array(91,155,213));
    $customized_invoice_data->subtotal += $item->total_amount;
}
$pdf->Ln(4);
$pdf->SetFillColor(255,255,255);
//Subtotal
$pdf->SetFont('Helvetica','',12);
$pdf->Cell(70,10,'', 0, 0,'C','true');
$pdf->Cell(97,10,'Invoice Subtotal', 0, 0,'R','true');
$pdf->Cell(3,10,'', 0, 0,'R','true');
$pdf->SetFont('Helvetica','',12);
$pdf->Cell(30,10,"$" . number_format($customized_invoice_data->subtotal,2), 0, 1,'L','true');

//GST
$pdf->SetFont('Helvetica','',12);
$pdf->Cell(70,10,'', 0, 0,'C','true');
$pdf->Cell(97,10,'Incl. Tax (GST):', 0, 0,'R','true');
$pdf->Cell(3,10,'', 0, 0,'R','true');
$pdf->SetFont('Helvetica','',12);
$pdf->Cell(30,10,"$" . number_format($customized_invoice_data->gst,2), 0, 1,'L','true');


$customized_invoice_data->total_amount = $customized_invoice_data->subtotal + $customized_invoice_data->gst;
//Total
$pdf->SetFont('Helvetica','B',12);
$pdf->Cell(70,10,'', 0, 0,'C','true');
$pdf->Cell(97,10,'TOTAL:', 0, 0,'R','true');
$pdf->Cell(3,10,'', "T", 0,'R','true');
$pdf->SetFont('Helvetica','BU',12);
$pdf->SetTextColor(45,78,107);
$pdf->Cell(30,10,"$" . number_format($customized_invoice_data->total_amount,2), "T", 1,'L','true');

$pdf->Ln(10);

$pdf->SetFont('Helvetica','B',12);
$pdf->SetTextColor(255,255,255);
$pdf->SetDrawColor(91,155,213);
$pdf->SetFillColor(68,117,161);
$pdf->Cell(200,10,'Payment Advice', 1, 1,'L','true');

$pdf->Ln(8);
$pdf->SetTextColor(0,0,0);
$pdf->SetFont('Helvetica','B',12);
$pdf->Cell(40,0,'Client',"0","0","L");
$pdf->SetFont('Helvetica','',12);
$pdf->Cell(40,0,$customized_invoice_data->name,"0",0,"L");
$pdf->Cell(60,0,'',"0",1,"C");

$pdf->Ln(8);
$pdf->SetFont('Helvetica','B',12);
$pdf->Cell(40,0,'Invoice Number',"0","0","L");
$pdf->SetFont('Helvetica','',12);
$pdf->Cell(40,0,$customized_invoice_data->invoice_number,"0",0,"L");
$pdf->Cell(60,0,'',"0",1,"C");

$pdf->Ln(8);
$pdf->SetFont('Helvetica','',12);
$pdf->SetFillColor(0,0,0);
$pdf->SetFont('Helvetica','B',14);
$pdf->Cell(40,0,'Total Due',"0","0","L");
$pdf->SetFont('Helvetica','',14);
$pdf->Cell(40,0,'$'.number_format($customized_invoice_data->total_amount,2),"0",0,"L");
$pdf->Cell(60,0,'',"0",1,"C");


$pdf->SetXY($x+100, $pdf->getY() - 18); // position of text1, numerical, of course, not x1 and y1

$pdf->SetFont('Helvetica','',12);
$pdf->MultiCell(105,5,"Direct Credit
Please make payment into the following account:",0,"L",false);

$pdf->SetXY($x+100, $pdf->getY());
$pdf->SetFont('Helvetica','B',12);
$pdf->MultiCell(105,5,"Eliteinsure Ltd, ANZ Bank, 06-0254-0426124-00.",0,"L",false);

$mix=$customized_invoice_data->invoice_number;
//$mix="$mydateorig"."-$name-"."BCTI".-date("ymd");
//$path="files/".$mix.".pdf";

$pdf->Output("I","Customized Invoice $customized_invoice_data->invoice_number" . ".pdf");
?>