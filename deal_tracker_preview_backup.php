<?php
date_default_timezone_set('Pacific/Auckland');
ob_start();
session_start();
require("fpdf/mc_table.php");






require("database.php");
/*
session_start();
*/
//post

class PDF extends PDF_MC_Table
{


	function Footer()
	{
		global $fsp_num;
		global $name;
		$this->SetY(-15);
		$this->SetFillColor(0,0,0);
		$this->Rect(5,342,206.5,.5,"FD");
		$this->SetFont('Helvetica','',10);
		$this->SetTextColor(0,0,0);
		$this->Cell(0,10,'Deal Tracker '. ''.' '.preg_replace("/\([^)]+\)/","",''),0,0,'L');
		$this->AliasNbPages('{totalPages}');	
		$this->Cell(0,10,'Page '.$this->PageNo() . " of " . "{totalPages}",0,1,'R');
	}

	function Header()
	{	
		$this->SetFillColor(0,0,0);
		$this->Image('logo_vertical.png',93,5,30);
		$this->Rect(5,25,206.5,.5,"FD");
		$this->SetFont('Helvetica','B',18);
		$this->SetTextColor(0,0,0);
		$this->Cell(0,20,'',"0","1","C");
		$this->SetTextColor(0,0,0);
		$this->SetFont('Helvetica','B',10);
		$this->SetFillColor(224,224,224);
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
$adv_name=isset($_POST['adv_name'])?$_POST['adv_name']:'';				//Adviser name
$adviser_id=isset($_POST['adviser_id'])?$_POST['adviser_id']:'';		//Adviser id
$date_from=isset($_POST['date_from'])?$_POST['date_from']:'';			//Date from
$date_created=isset($_POST['date_created'])?$_POST['date_created']:'';	//Invoice Date
$due_date=isset($_POST['due_date'])?$_POST['due_date']:'';				//Due date
$pay_date=isset($_POST['pay_date'])?$_POST['pay_date']:'';				//Due date
$until=isset($_POST['until'])?$_POST['until']:'';				//Due date
$note_entries=isset($_POST['notes'])?$_POST['notes']:'';						//Date until
//Production Desc
//Test Desc
//$desc=$_POST['desc'];		
$date_created = date("d/m/Y");

//Fetch Adviser Data
$searchadv="SELECT * FROM adviser_tbl WHERE id='$adviser_id'";
$search=mysqli_query($con,$searchadv) or die('Could not look up user information; ' . mysqli_error($con));
$rows = mysqli_fetch_array($search);

//Extract Data
$address=$rows["address"];
$leads=$rows["leads"];
$issued=$rows["bonus"];
$fsp_num=$rows['fsp_num'];
$email=$rows['email'];
$adviser_name = $rows["name"];
$adv_name = $adviser_name;

$date_from=substr($date_from,6,4).substr($date_from,3,2).substr($date_from,0,2);
$until=substr($until,6,4).substr($until,3,2).substr($until,0,2);

$d1 = new DateTime($date_from); // Y-m-d
$d2 = new DateTime($until);

$period_covered_title = $d1->format('d/m/Y') . "-" . $d2->format('d/m/Y');

//fetch deals
$search_leads="SELECT *, c.name as client_name, l.name as source FROM clients_tbl c LEFT JOIN submission_clients s ON c.id = s.client_id LEFT JOIN leadgen_tbl l ON l.id = c.leadgen WHERE assigned_to='$adviser_id' AND c.status!='Cancelled'";
//echo $search_leads . "<hr>";
$leads_exec =mysqli_query($con,$search_leads) or die('Could not look up user information; ' . mysqli_error($con));
$report_data = new stdClass();
$report_data->total_pending_api = 0;
$report_data->total_issued_api = 0;
$report_data->total_cancelled_api = 0;
$report_data->pending_deals = [];
$report_data->issued_deals = [];
$report_data->cancelled_deals = [];

 while($row = mysqli_fetch_array($leads_exec)){
 	if(!isset($row["deals"]))
 		continue;
 	$source = $adv_name;
 	if(!empty($row["source"]))
 		$source = $row["source"];
 	
 	$deals = json_decode($row["deals"]);

 	foreach($deals as $deal){
 		$life_insured = $row["client_name"];
 		if(!empty($deal->life_insured))
 			$life_insured .= ", " . $deal->life_insured;

 		
 		if($deal->status=="Issued"){
 			if($deal->date_issued>=$date_from && $deal->date_issued<=$until){

 				$report_data->issued_deals[] = array(
							"date" => $deal->date_issued,
							"life_insured" => $life_insured,
							"policy_number" => $deal->policy_number,
							"company" => $deal->company,
							"source" => $source,
							"api" => $deal->issued_api,
							"compliance_status" => $deal->compliance_status,
							"notes" => $deal->notes,
							"deal" => $deal,
						);
 				$report_data->total_issued_api += $deal->issued_api;
 			}
 			
 			//Add to Cancelled Deals
 			if(isset($deal->clawback_status)){
	 			if($deal->clawback_status!="None"){
 					if($deal->clawback_date>=$date_from && $deal->clawback_date<=$until){
	 					$report_data->cancelled_deals[] = array(
								"date" => $deal->clawback_date,		
								"life_insured" => $life_insured,
								"date" => $deal->clawback_date,
								"policy_number" => $deal->policy_number,
								"company" => $deal->company,
								"api" => $deal->clawback_api,
								"clawback_status" => $deal->clawback_status,
								"notes" => $deal->notes,
								"deal" => $deal,					
							);

	 					$report_data->total_cancelled_api += $deal->clawback_api;
	 				}
 				}
 			}
 		}
 	}
 }


//Fetch payables
$query = "SELECT * FROM transactions WHERE adviser_id = $adviser_id ORDER BY date DESC";
$displayquery=mysqli_query($con,$query) or die('Could not look up user information; ' . mysqli_error($con));
$report_data->total_leads_payable = 0;
$report_data->total_issued_payable = 0;
$report_data->total_balance = 0;
while($row=mysqli_fetch_assoc($displayquery)){
	extract($row);
	$status = CheckTransactionStatus($status);
		switch($status){
			case "Manual Billed Assigned Leads":
				$report_data->total_leads_payable+= $number_of_leads;
				break;
			case "Manual Billed Issued Leads":
				$report_data->total_issued_payable+= $number_of_leads;
				break;
			case "Billed Assigned Leads":
				$report_data->total_leads_payable+= $number_of_leads;
				break;
			case "Billed Issued Leads":
				$report_data->total_issued_payable+= $number_of_leads;
				break;
			case "Paid Issued Leads":
				$report_data->total_issued_payable-= $number_of_leads;
				break;
			default:
				$report_data->total_leads_payable-= $number_of_leads;
				break;
		}

	$report_data->total_balance += $amount;
}

$query = "SELECT * FROM clients_tbl WHERE assigned_to = $adviser_id AND date_submitted>=$date_from AND date_submitted<=$until";
$displayquery=mysqli_query($con,$query) or die('Could not look up user information; ' . mysqli_error($con));
$report_data->assigned_leads_for_period = mysqli_num_rows($displayquery);

$query = "SELECT * FROM issued_clients_tbl WHERE assigned_to = $adviser_id AND date_issued>=$date_from AND date_issued<=$until";
$displayquery=mysqli_query($con,$query) or die('Could not look up user information; ' . mysqli_error($con));
$report_data->issued_leads_for_period = mysqli_num_rows($displayquery);



function CheckTransactionStatus($status){
	$issued = stripos($status, 'Billed Issued Leads') !== false;
	$assigned = stripos($status, 'Billed Assigned Leads') !== false;
	$op = $status;
	if($issued){
		$op = "Billed Issued Leads";
	}
	elseif($assigned){
		$op = "Billed Assigned Leads";
	}

	return $op;
}

//Report Data

function addToDeals($deal,$status){
	$add_to = "";
	switch($status){
		case "Issued":

		break;
	}
}

$search_issued="SELECT * FROM issued_clients_tbl i LEFT JOIN clients_tbl c ON i.name = c.id WHERE c.assigned_to='$adviser_id' AND  i.date_issued<='$until' AND i.date_issued>=$date_from AND c.lead_by!='Telemarketer'";
//Remove c.lead_by!='Telemarketer' to include leads from telemarketers
$issued_exec=mysqli_query($con,$search_issued) or die('Could not look up user information; ' . mysqli_error($con));
$count_issued = mysqli_num_rows($issued_exec);


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

$pdf->SetFillColor(224,224,224);
$pdf->SetFont('Helvetica','B',20);
$pdf->Cell(200,10,'Deal Tracker',"0","1","C",'true');

$pdf->SetFont('Helvetica','B',15);
$pdf->SetFont('Helvetica','B',15);
$pdf->Cell(10,10,'Name:',"0","0","L");
$pdf->SetFont('Helvetica','',14);
$pdf->Cell(85,10,$adviser_name,"0","0","R");
$pdf->Cell(5,10,'',"0","0","R");
$pdf->SetFont('Helvetica','B',15);
$pdf->Cell(50,10,"Period Covered:","0","0","L");
$pdf->SetFont('Helvetica','',14);
$pdf->Cell(50,10,$period_covered_title,"0","1","R");


$pdf->SetFont('Helvetica','B',15);
$pdf->SetFont('Helvetica','B',15);
$pdf->Cell(10,10,'Address:',"0","0","L");
$pdf->SetFont('Helvetica','',14);
$pdf->Cell(85,10,$address,"0","0","R");
$pdf->Cell(5,10,'',"0","0","R");
$pdf->SetFont('Helvetica','B',15);
$pdf->Cell(50,10,"Pay Date:","0","0","L");
$pdf->SetFont('Helvetica','',14);
$pdf->Cell(50,10,$pay_date,"0","1","R");


$pdf->SetFont('Helvetica','B',15);
$pdf->SetFont('Helvetica','B',15);
$pdf->Cell(10,10,'Email:',"0","0","L");
$pdf->SetFont('Helvetica','',14);
$pdf->Cell(85,10,$email,"0","0","R");
$pdf->Cell(5,10,'',"0","0","R");
$pdf->SetFont('Helvetica','B',15);
$pdf->Cell(50,10,"Report By:","0","0","L");
$pdf->SetFont('Helvetica','',14);
$pdf->Cell(50,10,$_SESSION["myusername"],"0","1","R");


$pdf->SetFont('Helvetica','B',15);
$pdf->SetFont('Helvetica','B',15);
$pdf->Cell(10,10,'FSP Number:',"0","0","L");
$pdf->SetFont('Helvetica','',14);
$pdf->Cell(85,10,$fsp_num,"0","0","R");
$pdf->Cell(5,10,'',"0","0","R");
$pdf->SetFont('Helvetica','B',15);
$pdf->Cell(50,10,"","0","0","L");
$pdf->SetFont('Helvetica','',14);
$pdf->Cell(50,10,"","0","1","R");


//Space
AddLineSpace($pdf);

$pdf->SetFont('Helvetica','B',14);
$pdf->SetFillColor(224,224,224);
$pdf->Cell(200,10,'NOTES', 0, 1,'C','true');

$show_desc='';


//formula


if (count($note_entries)>0) {
	if(!empty($note_entries[0])){			
		$pdf->SetFont('Helvetica','B',12);
		$ctr = 1;
		foreach($note_entries as $note){
			$pdf->SetFillColor(255,255,255);
			$fill = (($ctr%2)===0) ? true : false;
			if(($ctr%2)===0){
				$pdf->SetFillColor(235,235,235);
			}
			$pdf->MultiCell(200,10,$ctr . ". " . $note, 0, 'L',$fill);
			$ctr++;
		}
	}
	else{
		$pdf->Cell(200,10,"No Entries Recorded.", 0, 1,'C');
	}
}
else{
	$pdf->Cell(200,10,"No Entries Recorded.", 0, 1,'C');
}

//Space
AddLineSpace($pdf);

$pdf->SetFont('Helvetica','B',14);
$pdf->SetFillColor(224,224,224);
$pdf->Cell(200,10,'LEADS', 0, 1,'C','true');
$pdf->SetFont('Helvetica','B',15);
$pdf->Cell(10,10,'Rate Per Lead:',"0","0","L");
$pdf->SetFont('Helvetica','',14);
$pdf->Cell(85,10,"$" . $leads,"0","0","R");
$pdf->Cell(5,10,'',"0","0","R");
$pdf->SetFont('Helvetica','B',15);
$pdf->Cell(50,10,"Rate Per Issued Lead:","0","0","L");
$pdf->SetFont('Helvetica','',14);
$pdf->Cell(50,10,"$" . $issued,"0","1","R");

$pdf->SetFont('Helvetica','B',15);
$pdf->Cell(10,10,'Total Balance:',"0","0","L");
$pdf->SetFont('Helvetica','',14);
$pdf->Cell(85,10,"$" . number_format($report_data->total_balance,2),"0","0","R");
$pdf->Cell(5,10,'',"0","0","R");
$pdf->SetFont('Helvetica','B',15);
$pdf->Cell(50,10,"Assigned Leads for the Period:","0","0","L");
$pdf->SetFont('Helvetica','',14);
$pdf->Cell(50,10,$report_data->assigned_leads_for_period ,"0","1","R");

$pdf->SetFont('Helvetica','B',15);
$pdf->Cell(10,10,'Leads Payable:',"0","0","L");
$pdf->SetFont('Helvetica','',14);
$pdf->Cell(85,10,$report_data->total_leads_payable,"0","0","R");
$pdf->Cell(5,10,'',"0","0","R");
$pdf->SetFont('Helvetica','B',15);
$pdf->Cell(50,10,"Leads Issued for the Period:","0","0","L");
$pdf->SetFont('Helvetica','',14);
$pdf->Cell(50,10,$report_data->issued_leads_for_period,"0","1","R");

$pdf->SetFont('Helvetica','B',15);
$pdf->Cell(10,10,'Issued Leads Payable:',"0","0","L");
$pdf->SetFont('Helvetica','',14);
$pdf->Cell(85,10,$report_data->total_issued_payable,"0","0","R");

$pdf->Cell(5,10,'',"0","0","R");
$pdf->SetFont('Helvetica','B',15);
$pdf->Cell(100,10,"","0","1","L");

//Space
AddLineSpace($pdf);

//Production
$pdf->SetFont('Helvetica','B',14);
$pdf->SetFillColor(224,224,224);
$pdf->SetTextColor(0,0,0);
$pdf->Cell(200,10,'PRODUCTION', 0, 1,'C','true');

$pdf->SetFont('Helvetica','U',12);
$pdf->SetFillColor(0,0,0);
$pdf->SetTextColor(0,0,0);
$pdf->Cell(30,10,'Life Insured', 0, 0,'C');
$pdf->Cell(20,10,'Policy #', 0, 0,'C');
$pdf->Cell(20,10,'Company', 0, 0,'C');
$pdf->Cell(20,10,'Source', 0, 0,'C');
$pdf->Cell(30,10,'Payable API', 0, 0,'C');
$pdf->Cell(30,10,'Compliance', 0, 0,'C');
$pdf->Cell(50,10,'Notes', 0, 1,'C');

$pdf->SetFont('Helvetica','',9);
$ctr=0;
$pdf->SetWidths(array(30,20,20,20,30,30,50));
usort($report_data->issued_deals, "sortFunction");
foreach($report_data->issued_deals as $deal){
	extract($deal);
	$pdf->SetFillColor(224,224,224);
	$fill = (($ctr%2)===0) ? true : false;
	$pdf->Row(array($life_insured,$policy_number,$company,$source,"$" . number_format($api,2),$compliance_status,$notes),$fill,array(224,224,224));
	$ctr++;
}

//Space
AddLineSpace($pdf);

//Clawbacks
$pdf->SetFont('Helvetica','B',14);
$pdf->SetFillColor(224,224,224);
$pdf->SetTextColor(0,0,0);
$pdf->Cell(200,10,'CLAWBACKS', 0, 1,'C','true');

$pdf->SetFont('Helvetica','U',12);
$pdf->SetFillColor(0,0,0);
$pdf->SetTextColor(0,0,0);
$pdf->Cell(40,10,'Life Insured', 0, 0,'C');
$pdf->Cell(20,10,'Policy #', 0, 0,'C');
$pdf->Cell(20,10,'Company', 0, 0,'C');
$pdf->Cell(30,10,'Paid API', 0, 0,'C');
$pdf->Cell(40,10,'Status', 0, 0,'C');
$pdf->Cell(50,10,'Notes', 0, 1,'C');

$pdf->SetFont('Helvetica','',9);
$ctr=0;
$pdf->SetWidths(array(40,20,20,30,40,50));
usort($report_data->cancelled_deals, "sortFunction");
foreach($report_data->cancelled_deals as $deal){
	extract($deal);
	$pdf->SetFillColor(224,224,224);
	$fill = (($ctr%2)===0) ? true : false;
	$pdf->Row(array($life_insured,$policy_number,$company,"$" . number_format($api,2),$clawback_status,$notes),$fill,array(224,224,224));
	$ctr++;
}

$mix = "";
//$mix="$mydateorig"."-$name-"."BCTI".-date("ymd");
//$path="files/".$mix.".pdf";

$path="files/preview.pdf";
$pdf->Output($path,'F');
//$pdf->Output();

//OUTPUT 
$file=array();
$file['adviser_id']=$adviser_id;
$file['link']=$path;
$file['filename']=$mix;
$file['report_data'] = json_encode($report_data);
$file['notes'] = json_encode($note_entries);
$file['from'] = $date_from;
$file['pay_date'] = DateTimeToNZEntry($pay_date);
$file['to'] = $until;
//$file['amount'] = $total_payable;
//$file['payable_leads'] = $payable_leads;
//$file['payable_issued_leads'] = $payable_issued_leads;
	
echo json_encode($file);
//db add end
//}


function DateTimeToNZEntry($date_submitted){
  return substr($date_submitted,6,4).substr($date_submitted,3,2).substr($date_submitted,0,2);
}

function NZEntryToDateTime($NZEntry){
    return substr($NZEntry,6,2) . "/" . substr($NZEntry,4,2) . "/" . substr($NZEntry, 0, 4);
}

function sortFunction( $a, $b ) {
    return strtotime($a["date"]) - strtotime($b["date"]);
}

function AddLineSpace($pdf, $linespace = 10){
	$pdf->SetFillColor(255,255,255);
	$pdf->SetTextColor(0,0,0);
	$pdf->Cell(200,$linespace,'', 0, 1,'C','true');
}

?>