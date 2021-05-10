<?php
date_default_timezone_set('Pacific/Auckland');
ob_start();
session_start();
require_once("fpdf/mc_table.php");






require_once("database.php");
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
        global $curPage;
        global $NumPages;

		$this->SetY(-15);
		$this->SetFillColor(0,0,0);
		$this->Rect(5,342,206.5,.5,"FD");
		$this->SetFont('Helvetica','',10);
		$this->SetTextColor(0,0,0);
		$this->Cell(100,10,'Deal Tracker-' . $name,0,0,'L');
        $this->Cell(90,10,'Page '.$curPage . " of " . "$NumPages",0,1,'R');
        
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

$tracker_id = $_GET["id"];
//Fetch Adviser Data
$searchadv="SELECT * FROM deal_tracker_summary WHERE id='$tracker_id'";
$search=mysqli_query($con,$searchadv) or die('Could not look up user information; ' . mysqli_error($con));
$deal_tracker_report = mysqli_fetch_assoc($search);

$created_by = $deal_tracker_report["created_by"];


//retrieving
$adviser_id= $deal_tracker_report["adviser_id"];		//Adviser id
$date_from=$deal_tracker_report['date_from'];			//Date from
$date_created=$deal_tracker_report['date_created'];			//Date from
$until=$deal_tracker_report['date_to'];	
$report_data=json_decode($deal_tracker_report['report_data']);	

$creator_query = "Select * from users where id = $created_by";
$creator_result = mysqli_query($con,$creator_query) or die('Could not look up user information; ' . mysqli_error($con));
$creator_row = mysqli_fetch_array($creator_result);


$created_by = $creator_row["username"];
//Production Desc
//Test Desc
//$desc=$_POST['desc'];		
$date_created = date("d/m/Y");
$advisers = json_decode($adviser_id, true);
$information = $report_data->information;

$d1 = new DateTime($date_from); // Y-m-d
$d2 = new DateTime($until);

$period_covered_title = $d1->format('d/m/Y') . "-" . $d2->format('d/m/Y');

$pdf = new PDF('P', 'mm', 'Legal');
foreach($report_data->reports as $report_data2){

    $pdf->AddPage('P', 'Legal');
  
    $name = $report_data2->adviser_name;
    $curPage = 1;
    $NumPages = 1;

    //fetch deals


    $search_issued="SELECT * FROM issued_clients_tbl i LEFT JOIN clients_tbl c ON i.name = c.id WHERE c.assigned_to='$report_data2->adviser_id' AND  i.date_issued<='$until' AND i.date_issued>=$date_from AND c.lead_by!='Telemarketer'";
    //Remove c.lead_by!='Telemarketer' to include leads from telemarketers
    $issued_exec=mysqli_query($con,$search_issued) or die('Could not look up user information; ' . mysqli_error($con));
    $count_issued = mysqli_num_rows($issued_exec);


    $x = $pdf->GetX();
    $y = $pdf->GetY();

    //page 1

    $pdf->SetFillColor(224,224,224);
    $pdf->SetFont('Helvetica','B',20);
    $pdf->Cell(200,10,'Deal Tracker',"0","1","C",'true');

    $pdf->SetFont('Helvetica','B',14);
    $pdf->Cell(16,10,"Name:","0","0","L");
    $pdf->SetFont('Helvetica','',14);
    $pdf->Cell(79,10,"$report_data2->adviser_name","0","0","L");
    $pdf->Cell(5,10,'',"0","0","R");
    $pdf->SetFont('Helvetica','B',14);
    $pdf->Cell(16,10,"Team:","0","0","L");
    $pdf->SetFont('Helvetica','',14);
    $pdf->Cell(84,10,"$report_data2->adviser_team","0","1","L");


    $pdf->SetFont('Helvetica','B',14);
    $pdf->Cell(32,10,"FSP Number:","0","0","L");
    $pdf->SetFont('Helvetica','',14);
    $pdf->Cell(63,10,"$report_data2->fsp_num","0","0","L");

    $pdf->Cell(5,10,'',"0","0","R");
    $pdf->SetFont('Helvetica','B',14);
    $pdf->Cell(39,10,"Period Covered:","0","0","L");
    $pdf->SetFont('Helvetica','',14);
    $pdf->Cell(61,10,"$period_covered_title","0","1","L");


    $pdf->SetFont('Helvetica','B',14);
    $pdf->Cell(16,10,"Email:","0","0","L");
    $pdf->SetFont('Helvetica','',14);
    $pdf->Cell(79,10,"$report_data2->email","0","0","L");
    $pdf->Cell(5,10,'',"0","0","R");
    $pdf->SetFont('Helvetica','B',14);
    $pdf->Cell(26.5,10,"Report By:","0","0","L");
    $pdf->SetFont('Helvetica','',14);
    $pdf->Cell(73.5,10,"$created_by","0","1","L");
    /*
    $pdf->Cell(25,10,"Pay Date:","0","0","L");
    $pdf->SetFont('Helvetica','',15);
    $pdf->Cell(75,10,"$pay_date","0","1","L");
    */


    $x=$pdf->GetX();
    $y=$pdf->GetY();
    
    $pdf->SetFont('Helvetica','B',14);
    $pdf->Cell(22,10,"Address:","0","0","L");
    $pdf->SetFont('Helvetica','',14);
    $pdf->MultiCell(178,10,"$report_data2->address","0","L", false);

   


    //Space
    AddLineSpace($pdf);

    $pdf->SetFont('Helvetica','B',15);
    $pdf->SetFillColor(224,224,224);
    $pdf->Cell(200,10,'LEADS', 0, 1,'C','true');


    $pdf->SetFont('Helvetica','B',13);
    $pdf->Cell(34,10,"Rate Per Lead:","0","0","L");
    $pdf->SetFont('Helvetica','',13);
    $pdf->Cell(61,10,"$" . $report_data2->leads,"0","0","L");
    $pdf->Cell(5,10,'',"0","0","R");

    $pdf->SetFont('Helvetica','B',13);
    $pdf->Cell(50,10,"Rate Per Issued Lead:","0","","L");
    $pdf->SetFont('Helvetica','',13);
    $pdf->Cell(50,10,"$" . $report_data2->issued,"0","1","L");


    $pdf->SetFont('Helvetica','B',13);
    $pdf->Cell(33,10,'Total Balance:',"0","0","L");
    $pdf->SetFont('Helvetica','',13);
    $pdf->Cell(62,10,'$' . number_format($report_data2->total_balance,2),"0","0","L");
    $pdf->Cell(5,10,'',"0","0","R");

    $pdf->SetFont('Helvetica','B',13);
    $pdf->Cell(70,10,"Assigned Leads for the Period:","0","0","L");
    $pdf->SetFont('Helvetica','',13);
    $pdf->Cell(30,10,"$report_data2->assigned_leads_for_period","0","1","L");

    $pdf->SetFont('Helvetica','B',13);
    $pdf->Cell(47,10,"Total Leads Payable:","0","0","L");
    $pdf->SetFont('Helvetica','',13);
    $pdf->Cell(48,10,"$report_data2->total_leads_payable","0","0","L");
    $pdf->Cell(5,10,'',"0","0","R");
    $pdf->SetFont('Helvetica','B',13);
    $pdf->Cell(63,10,"Leads Issued for the Period:","0","0","L");
    $pdf->SetFont('Helvetica','',13);
    $pdf->Cell(37,10,"$report_data2->issued_leads_for_period","0","1","L");

    $pdf->SetFont('Helvetica','B',13);
    $pdf->Cell(48,10,"Total Issued Payable:","0","0","L");
    $pdf->SetFont('Helvetica','',13);
    $pdf->Cell(47,10,"$report_data2->total_issued_payable","0","0","L");
    $pdf->Cell(5,10,'',"0","0","R");
    $pdf->Cell(100,10,"","0","1","L");

    //Space
    AddLineSpace($pdf);

    if(in_array("Production",$report_data->information)){            
        //Production
        $pdf->SetFont('Helvetica','B',14);
        $pdf->SetFillColor(224,224,224);
        $pdf->SetTextColor(0,0,0);
        $pdf->Cell(200,10,'PRODUCTION', 0, 1,'C','true');

        $pdf->SetFont('Helvetica','U',12);
        $pdf->SetFillColor(0,0,0);
        $pdf->SetTextColor(0,0,0);

        $pdf->SetWidths(array(30,20,20,20,20,20,20,50));
        $pdf->Row(array("Life Insured", "Policy #", "Co.", "Source","Date Issued", "API", "Comp. Status", "Notes"),false,array(224,224,224));
            
        $pdf->SetFont('Helvetica','',9);
        $ctr=0;
        $rep_data = json_encode($report_data2->issued_deals);
        $report_data2->issued_deals = json_decode($rep_data, true);
        usort($report_data2->issued_deals, "sortFunction");
        foreach($report_data2->issued_deals as $deal){
            extract($deal);
            $pdf->SetFillColor(224,224,224);
            $fill = (($ctr%2)===0) ? true : false;
            $pdf->Row(array($life_insured,$policy_number,$company,$source,NZEntryToDateTime($date),"$" . number_format($api,2),$compliance_status,str_replace("<br>","\r\n",$notes)),$fill,array(224,224,224));
            $ctr++;
        }

        $pdf->SetDrawColor(0,0,0);

        $pdf->SetFont('Helvetica','B',11);
        $pdf->Cell(105,10,'Total Payable API', "T", 0,'L');
        $pdf->Cell(30,10,"$" . number_format($report_data2->total_issued_api,2),"T", 0,'C');
        $pdf->Cell(65,10,'', "T", 1,'C');
        //Space
        AddLineSpace($pdf);
    }

    if(in_array("Clawback", $report_data->information )){
        //Clawbacks
        $pdf->SetFont('Helvetica','B',14);
        $pdf->SetFillColor(224,224,224);
        $pdf->SetTextColor(0,0,0);
        $pdf->Cell(200,10,'CLAWBACKS', 0, 1,'C','true');

        $pdf->SetWidths(array(40,20,20,20,20,30,50));

        $pdf->SetFont('Helvetica','U',12);
        $pdf->SetFillColor(0,0,0);
        $pdf->SetTextColor(0,0,0);



        $pdf->Row(array("Life Insured", "Policy #", "Co.", "Date Issued", "API", "Status", "Notes"),false,array(224,224,224));

        $pdf->SetFont('Helvetica','',9);
        $ctr=0;
        $rep_data = json_encode($report_data2->cancelled_deals);
        $report_data2->cancelled_deals = json_decode($rep_data, true);
        usort($report_data2->cancelled_deals, "sortFunction");
        foreach($report_data2->cancelled_deals as $deal){
            extract($deal);
            $pdf->SetFillColor(224,224,224);
            $fill = (($ctr%2)===0) ? true : false;
            $pdf->Row(array($life_insured,$policy_number,$company,NZEntryToDateTime($date),"$" . number_format($api,2),$clawback_status,str_replace("<br>","\r\n",$notes)),$fill,array(224,224,224));
            $ctr++;
        }

        $pdf->SetDrawColor(0,0,0);
        $pdf->SetFont('Helvetica','B',11);
        $pdf->Cell(95,10,'Total Paid API', "T", 0,'L');
        $pdf->Cell(30,10,"$" . number_format($report_data2->total_cancelled_api,2),"T", 0,'C');
        $pdf->Cell(75,10,'', "T", 1,'C');

    }
}

$mix = "";
//$mix="$mydateorig"."-$name-"."BCTI".-date("ymd");
//$path="files/".$mix.".pdf";

//$pdf->Output();
ob_end_clean();
$pdf->Output();
//$file['amount'] = $total_payable;
//$file['payable_leads'] = $payable_leads;
//$file['payable_issued_leads'] = $payable_issued_leads;
	
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
?>