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
	var $adviser = "";

	function Footer()
	{
		global $fsp_num;
		global $timestamp;
		global $agent_name;
		$this->SetY(-15);
		$this->SetFillColor(0,0,0);
		$this->Rect(5,342,206.5,.5,"FD");
		$this->SetFont('Helvetica','',10);
		$this->SetTextColor(0,0,0);
		$this->Cell(50,10,"Date Filled Out: " . date("d/m/Y",strtotime($timestamp)),0,0,'L');
		$this->Cell(100,10,"Agent: $agent_name",0,0,'C');
		$this->AliasNbPages('{totalPages}');
		$this->Cell(0,10,'Page '.$this->PageNo() . " of " . "{totalPages}",0,1,'R');
	}

	function Header()
	{	
		$this->SetFillColor(0,100,150);
		$this->Image('logo_vertical.png',10,5,30);
		$this->Rect(5,25,206.5,.5,"F");
		$this->Rect(44,1,7,24.1,"F");
		$this->SetFont('Helvetica','B',12);
		$this->SetTextColor(0,0,0);
		$this->Image('images/Home.png',45,2.5,5);
		$this->Image('images/Phone.png',45,8.75,5);
		$this->Image('images/Mail.png',45,14.75,5);
		$this->Image('images/WWW.png',45,20.25,5);
		$this->SetY(1.75);

		//Address
		$this->Cell(41,6,'',"0","0","L");
		$this->Cell(55,6,'3G/39 Mackelvie Street Grey Lynn 1021 Auckland New Zealand',"0","1","L");

		//Contact Number
		$this->Cell(41,6,'',"0","0","L");
		$this->Cell(50,6,'0508 123 467',"0","1","L");

		//Email
		$this->Cell(41,6,'',"0","0","L");
		$this->Cell(50,6,'admin@eliteinsure.co.nz',"0","1","L");

		//Email
		$this->Cell(41,6,'',"0","0","L");
		$this->Cell(50,6,'www.eliteinsure.co.nz',"0","1","L");

		$this->SetY(28);
		$this->SetTextColor(0,0,0);
		$this->SetFont('Helvetica','B',10);
		$this->SetFillColor(224,224,224);
	}

	function getPage(){
		return $this->PageNo();
	}

    function NLines($w, $txt){
        return $this->NbLines($w,$txt);
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

$data_id = $_GET["id"];
//Fetch Adviser Data
$searchadv="SELECT * FROM leads_data WHERE id='$data_id'";
$search=mysqli_query($con,$searchadv) or die('Could not look up user information; ' . mysqli_error($con));
$leads_data = mysqli_fetch_assoc($search);
$lead_data = json_decode($leads_data["data"]);
$timestamp = $leads_data["timestamp"];
//var_dump($lead_data);

$aquery = "SELECT * FROM leadgen_tbl WHERE id=$lead_data->agent_id";
$aresult = mysqli_query($con, $aquery);
$arow = mysqli_fetch_assoc($aresult);
$agent_name = $arow["name"];
//Translate Client Age
if($lead_data->client_age=="<25"){
		$lead_data->client_age = "Under 25";
}
elseif($lead_data->client_age==">60"){
	$lead_data->client_age = "Above 60";
}

$lead_data->client_interests = array();

$lead_data->smoker = isset($lead_data->client_is_smoking) ? "Yes" : "No";
$lead_data->smoker = "Smoker: $lead_data->smoker";
 
if(isset($lead_data->has_partner)){
	$lead_data->partner_interests = array();
	if($lead_data->partner_age=="<25"){
			$lead_data->partner_age = "Under 25";
	}
	elseif($lead_data->partner_age==">60"){
		$lead_data->partner_age = "Above 60";
	}
		
	$lead_data->partner_smoker = isset($lead_data->partner_is_smoking) ? "Yes" : "No";
	$lead_data->partner_smoker = "Smoker: $lead_data->partner_smoker";
 
}

$query = "SELECT * FROM products";
$result = mysqli_query($con, $query);
while($row=mysqli_fetch_assoc($result)){
	$product_acronym = $row["acronym"];

	if($product_acronym==""){
		$product_acronym = $row["name"];
	}

	$product_safe_name =  preg_replace("/[^a-zA-Z0-9\s]/", "", $product_acronym);
	$product_safe_name = str_replace(' ', '_', strtolower($product_safe_name));
	$client_interested_in = 'client_interested_in_' . $product_safe_name;
	$partner_interested_in = 'partner_interested_in_' . $product_safe_name;

	if(isset($lead_data->{$client_interested_in})){
		//echo $client_interested_in . "<hr>";
		$lead_data->client_interests[] = $product_acronym;
	}
	
	
	if(isset($lead_data->{$partner_interested_in})){
		//echo $client_interested_in . "<hr>";
		$lead_data->partner_interests[] = $product_acronym;
	}
}

//Translate Citizenship
if($lead_data->client_citizenship == "Working VISA"){
	$years = $lead_data->working_visa_years;
	if($years == "<1"){
		$years = " <1 Year";
	}
	elseif($years==">2"){
		$years = " >2 Years";
	}
	elseif($years=="1"){
		$years =  " " . $years . " Year";
	}
	else{
		$years =  " " . $years . " Years";
	}

	$lead_data->client_citizenship .= $years;
}

$lead_data->grade = (isset($lead_data->grade)) ? $lead_data->grade : "B";

$created_by = $lead_data->agent;

$lead_data->notes = str_replace("<br>", "\r\n", $lead_data->notes);

$pdf = new PDF('P', 'mm', 'Legal');
$x = $pdf->GetX();
$y = $pdf->GetY();

//page 1
$pdf->AddPage();

//Title
$pdf->SetFillColor(224,224,224);
$pdf->SetFont('Arial','',13);


$pdf->Rect($pdf->GetX()+98,$pdf->GetY()+5,37,0.2);

$pdf->SetFont('Arial','',13);
$pdf->Write(5,'INFORMATION COLLECTED IS FOR A FREE-');

$pdf->SetFont('Arial','B',13);
$pdf->Write(5,'NO-OBLIGATION ');

$pdf->SetFont('Arial','',13);
$pdf->Write(5,'INSURANCE REVIEW');

$pdf->Ln();
$pdf->SetFont('Arial','',10);
$pdf->Cell(150,5,'Adviser will not review home, content and car insurance. Review is only for risk products.',"0","1","L",false);

$column_number = "1";

$pdf->SetFillColor(209,226,243);
$pdf->SetDrawColor(157,195,230);
$pdf->Rect(8,$pdf->GetY(),201,10,"FD");
$pdf->SetFont('Helvetica','B',15);
$pdf->Cell(197,10, numberToRomanRepresentation($column_number) . ". CUSTOMER INFORMATION","0","1","L",true);

$pdf->Ln(1);
$column_number++;
$pdf->SetFillColor(190,215,239);
$pdf->Rect(10,$pdf->GetY(),100,8,"F");
$pdf->Rect(111,$pdf->GetY(),96,8,"F");
$pdf->SetFont('Helvetica','',13);
$pdf->Cell(101,8,"Name: " . $lead_data->client_name,"0","0","L",false);
$pdf->Cell(96,8,"Company: " . $lead_data->company_name,"0","1","L",false);
$pdf->Ln(1);

$pdf->SetFillColor(223,235,247);
$pdf->Rect(10,$pdf->GetY(),100,8,"F");
$pdf->Rect(111,$pdf->GetY(),96,8,"F");
$pdf->Cell(101,8,"Occupation: " . $lead_data->client_occupation,"0","0","L",false);
$pdf->Cell(96,8,"Income: " . $lead_data->client_income,"0","1","L",false);
$pdf->Ln(1);

$pdf->SetFillColor(190,215,239);
$pdf->Rect(10,$pdf->GetY(),40,8,"F");
$pdf->Rect(51,$pdf->GetY(),40,8,"F");
$pdf->Rect(92,$pdf->GetY(),40,8,"F");
$pdf->Rect(133,$pdf->GetY(),74,8,"F");
$pdf->Cell(41,8,"Gender: " . ucfirst($lead_data->client_gender),"0","0","L",false);
$pdf->Cell(41,8,"Age: " . $lead_data->client_age,"0","0","L",false);
$pdf->Cell(41,8,"Status: " . $lead_data->client_civil_status,"0","0","L",false);
$pdf->Cell(74,8,"Number of Dependents: " . $lead_data->client_dependents,"0","1","L",false);
$pdf->Ln(1);

$pdf->SetFillColor(223,235,247);
$pdf->Rect(10,$pdf->GetY(),197,8,"F");
$pdf->Cell(197,8,"Address: " . $lead_data->client_address,"0","1","L",false);
$pdf->Ln(1);

$pdf->SetFillColor(190,215,239);
$pdf->Rect(10,$pdf->GetY(),197,8,"F");
$pdf->Cell(197,8,"Company Address: " . $lead_data->company_address,"0","1","L",false);
$pdf->Ln(1);

$pdf->SetFillColor(223,235,247);
$pdf->Rect(10,$pdf->GetY(),197,8,"F");
$pdf->Cell(197,8,"City: " . $lead_data->client_city,"0","1","L",false);
$pdf->Ln(1);

$pdf->SetFillColor(190,215,239);
$pdf->Rect(10,$pdf->GetY(),60,8,"F");
$pdf->Rect(71,$pdf->GetY(),80,8,"F");
$pdf->Rect(152,$pdf->GetY(),55,8,"F");
$pdf->Cell(61,8,"Zipcode: " . $lead_data->client_zipcode,"0","0","L",false);
$pdf->Cell(81,8,"Citizenship: " . $lead_data->client_citizenship,"0","0","L",false);
$pdf->Cell(55,8,$lead_data->smoker,"0","1","L",false);
$pdf->Ln(1);

$pdf->SetFillColor(223,235,247);
$pdf->Rect(10,$pdf->GetY(),39,8,"F");
$pdf->Rect(50,$pdf->GetY(),157,8,"F");
$pdf->Cell(40,8,"Health Concern 1: ","0","0","L",false);
$pdf->Cell(157,8,(isset($lead_data->client_health_concerns[0])) ? $lead_data->client_health_concerns[0] : "","0","1","L",false);
$pdf->Ln(1);

$pdf->SetFillColor(190,215,239);
$pdf->Rect(10,$pdf->GetY(),39,8,"F");
$pdf->Rect(50,$pdf->GetY(),157,8,"F");
$pdf->Cell(40,8,"Health Concern 2: ","0","0","L",false);
$pdf->Cell(157,8,(isset($lead_data->client_health_concerns[1])) ? $lead_data->client_health_concerns[1] : "","0","1","L",false);
$pdf->Ln(1);

$pdf->SetFillColor(223,235,247);
$pdf->Rect(10,$pdf->GetY(),39,8,"F");
$pdf->Rect(50,$pdf->GetY(),157,8,"F");
$pdf->Cell(40,8,"Health Concern 3: ","0","0","L",false);
$pdf->Cell(157,8,(isset($lead_data->client_health_concerns[2])) ? $lead_data->client_health_concerns[2] : "","0","1","L",false);
$pdf->Ln(1);

$pdf->SetFillColor(190,215,239);

$last_index=0;
//Insurances
if(isset($lead_data->client_current_insurance_type)){
	if(is_array($lead_data->client_current_insurance_type)){
		$ctr = 0;
		for($i=0; $i<count($lead_data->client_current_insurance_type); $i++){
			$left_content = "";
			if($i==0){
				$left_content = "Current Insurances:";
			}
			if($i%2==0){
				$pdf->SetFillColor(190,215,239);
			}
			else{
				$pdf->SetFillColor(223,235,247);
			}
			$pdf->Rect(10,$pdf->GetY(),42,8,"F");
			$pdf->Rect(53,$pdf->GetY(),154,8,"F");
			$pdf->SetFont('Helvetica','',13);
			$pdf->Cell(43,8,$left_content);
			$pdf->Cell(155,8,$lead_data->client_current_insurance_type[$i] . " from " . $lead_data->client_current_insurance_company[$i],"0","1","L",false);
		
			if($pdf->GetY() >= 312){
			    $pdf->AddPage();
			}
			else{
			    $pdf->Ln(1);
			}
			$last_index = $i;
		}
	}
	else{
		$pdf->Rect(10,$pdf->GetY(),40,8,"F");
		$pdf->Rect(51,$pdf->GetY(),156,8,"F");
		$pdf->SetFont('Helvetica','',13);
		$pdf->Cell(41,8,"Current Insurance:");
		$pdf->Cell(157,8,$lead_data->client_current_insurance_type . " from " . $lead_data->client_current_insurance_company,"0","1","L",false);
		$pdf->Ln(1);
		$last_index=0;
	}
}
else{
	$pdf->Rect(10,$pdf->GetY(),40,8,"F");
	$pdf->Rect(51,$pdf->GetY(),156,8,"F");
	$pdf->SetFont('Helvetica','',13);
	$pdf->Cell(41,8,"Current Insurance:");
	$pdf->Cell(157,8,"None","0","1","L",false);
	$pdf->Ln(1);
	$last_index=0;
}

//Interests
if(isset($lead_data->client_interests)){
	if(is_array($lead_data->client_interests)){
		$ctr = 0;
		for($i=0; $i<count($lead_data->client_interests); $i++){
			$left_content = "";
			if($i==0){
				$left_content = "Interested In:";
			}
			if(($i+$last_index)%2==0){
				$pdf->SetFillColor(223,235,247);
			}
			else{
				$pdf->SetFillColor(190,215,239);
			}
			$pdf->Rect(10,$pdf->GetY(),30,8,"F");
			$pdf->Rect(41,$pdf->GetY(),166,8,"F");
			$pdf->SetFont('Helvetica','',13);
			$pdf->Cell(31,8,$left_content);
			$pdf->Cell(167,8,$lead_data->client_interests[$i],"0","1","L",false);
			
			if($pdf->GetY() >= 312){
			    $pdf->AddPage();
			}
			else{
			    $pdf->Ln(1);
			}
		}
	}
}

if($pdf->GetY() >= 312){
				    $pdf->AddPage();
				}
				
if(isset($lead_data->has_partner)){
		
	$pdf->SetFillColor(209,226,243);
	$pdf->SetDrawColor(157,195,230);
	$pdf->Rect(8,$pdf->GetY(),201,10,"FD");
	$pdf->SetFont('Helvetica','B',15);
	$pdf->Cell(17,10, numberToRomanRepresentation($column_number) . ". PARTNER'S INFORMATION","0","1","L",true);
	$pdf->Ln(1);
	$column_number++;

	$pdf->SetFillColor(190,215,239);
	$pdf->Rect(10,$pdf->GetY(),100,8,"F");
	$pdf->Rect(111,$pdf->GetY(),96,8,"F");
	$pdf->SetFont('Helvetica','',13);
	$pdf->Cell(101,8,"Name: " . $lead_data->partner_name,"0","0","L",false);
	$pdf->Cell(96,8,"Occupation: " . $lead_data->partner_occupation,"0","1","L",false);
	$pdf->Ln(1);

	$pdf->SetFillColor(223,235,247);
	$pdf->Rect(10,$pdf->GetY(),100,8,"F");
	$pdf->Rect(111,$pdf->GetY(),96,8,"F");
	$pdf->SetFont('Helvetica','',13);
	$pdf->Cell(101,8,"Income: " . $lead_data->partner_income,"0","0","L",false);
	$pdf->Cell(96,8,"Gender: " . ucfirst($lead_data->partner_gender),"0","1","L",false);
	$pdf->Ln(1);

	$pdf->SetFillColor(190,215,239);
	$pdf->Rect(10,$pdf->GetY(),60,8,"F");
	$pdf->Rect(71,$pdf->GetY(),136,8,"F");
	$pdf->Cell(61,8,"Age: " . $lead_data->partner_age,"0","0","L",false);
	$pdf->Cell(136,8,"Email: " . $lead_data->partner_email,"0","1","L",false);
	$pdf->Ln(1);

	$pdf->SetFillColor(223,235,247);
	$pdf->Rect(10,$pdf->GetY(),70,8,"F");
	$pdf->Rect(81,$pdf->GetY(),70,8,"F");
	$pdf->Rect(152,$pdf->GetY(),55,8,"F");
	$pdf->SetFont('Helvetica','',13);
	$pdf->Cell(71,8,"Telephone: " . $lead_data->partner_telephone,"0","0","L",false);
	$pdf->Cell(71,8,"Mobile: " . $lead_data->partner_mobile,"0","0","L",false);
	$pdf->Cell(55,8,$lead_data->partner_smoker,"0","1","L",false);
	$pdf->Ln(1);

	$pdf->SetFillColor(190,215,239);
	$pdf->Rect(10,$pdf->GetY(),39,8,"F");
	$pdf->Rect(50,$pdf->GetY(),157,8,"F");
	$pdf->Cell(40,8,"Health Concern 1: ","0","0","L",false);
	$pdf->Cell(157,8,(isset($lead_data->partner_health_concerns[0])) ? $lead_data->partner_health_concerns[0] : "","0","1","L",false);
	$pdf->Ln(1);

	$pdf->SetFillColor(223,235,247);
	$pdf->Rect(10,$pdf->GetY(),39,8,"F");
	$pdf->Rect(50,$pdf->GetY(),157,8,"F");
	$pdf->Cell(40,8,"Health Concern 2: ","0","0","L",false);
	$pdf->Cell(157,8,(isset($lead_data->partner_health_concerns[1])) ? $lead_data->partner_health_concerns[1] : "","0","1","L",false);
	$pdf->Ln(1);

	$pdf->SetFillColor(190,215,239);
	$pdf->Rect(10,$pdf->GetY(),39,8,"F");
	$pdf->Rect(50,$pdf->GetY(),157,8,"F");
	$pdf->Cell(40,8,"Health Concern 3: ","0","0","L",false);
	$pdf->Cell(157,8,(isset($lead_data->partner_health_concerns[2])) ? $lead_data->partner_health_concerns[2] : "","0","1","L",false);
	$pdf->Ln(1);

	
	if($pdf->GetY() >= 312){
				    $pdf->AddPage();
				}
$last_index=0;
	//Insurances
	if(isset($lead_data->partner_current_insurance_type)){
		if(is_array($lead_data->partner_current_insurance_type)){
			$ctr = 0;
			for($i=0; $i<count($lead_data->partner_current_insurance_type); $i++){
				$left_content = "";
				if($i==0){
					$left_content = "Current Insurances:";
				}
				if($i%2==0){
					$pdf->SetFillColor(190,215,239);
				}
				else{
					$pdf->SetFillColor(223,235,247);
				}
				$pdf->Rect(10,$pdf->GetY(),42,8,"F");
				$pdf->Rect(53,$pdf->GetY(),154,8,"F");
				$pdf->SetFont('Helvetica','',13);
				$pdf->Cell(43,8,$left_content);
				$pdf->Cell(155,8,$lead_data->partner_current_insurance_type[$i] . " from " . $lead_data->partner_current_insurance_company[$i],"0","1","L",false);
				
				if($pdf->GetY() >= 312){
				    $pdf->AddPage();
				}
				else{
				    $pdf->Ln(1);
				}
				
                
				$last_index++;
			}
		}
		else{
			$pdf->SetFillColor(223,235,247);
			$pdf->Rect(10,$pdf->GetY(),40,8,"F");
			$pdf->Rect(51,$pdf->GetY(),156,8,"F");
			$pdf->SetFont('Helvetica','',13);
			$pdf->Cell(41,8,"Current Insurance:");
			$pdf->Cell(157,8,$lead_data->partner_current_insurance_type . " from " . $lead_data->partner_current_insurance_company,"0","1","L",false);
			
				if($pdf->GetY() >= 312){
				    $pdf->AddPage();
				}
				else{
				    $pdf->Ln(1);
				}
		}
	}
	else{
		$pdf->SetFillColor(223,235,247);
		$pdf->Rect(10,$pdf->GetY(),40,8,"F");
		$pdf->Rect(51,$pdf->GetY(),156,8,"F");
		$pdf->SetFont('Helvetica','',13);
		$pdf->Cell(41,8,"Current Insurance:");
		$pdf->Cell(157,8,"None","0","1","L",false);

		if($pdf->GetY() >= 312){
		    $pdf->AddPage();
		}
		else{
		    $pdf->Ln(1);
		}
		$last_index=0;
	}

		
	//Interests
	if(isset($lead_data->partner_interests)){
		if(is_array($lead_data->partner_interests)){
			$ctr = 0;
			for($i=0; $i<count($lead_data->partner_interests); $i++){
				$left_content = "";
				if($i==0){
					$left_content = "Interested In:";
				}
				if(($i+$last_index)%2==0){
					$pdf->SetFillColor(190,215,239);
				}
				else{
					$pdf->SetFillColor(223,235,247);
				}
				$pdf->Rect(10,$pdf->GetY(),30,8,"F");
				$pdf->Rect(41,$pdf->GetY(),166,8,"F");
				$pdf->SetFont('Helvetica','',13);
				$pdf->Cell(31,8,$left_content);
				$pdf->Cell(167,8,$lead_data->partner_interests[$i],"0","1","L",false);
				
				if($pdf->GetY() >= 312){
				    $pdf->AddPage();
				}
				else{
				    $pdf->Ln(1);
				}
			}
		}
	}
}

	if($pdf->GetY() >= 312){
	    $pdf->AddPage();
	}
			
				
	$pdf->SetFillColor(209,226,243);
	$pdf->SetDrawColor(157,195,230);
	$pdf->Rect(8,$pdf->GetY(),201,10,"FD");
	$pdf->SetFont('Helvetica','B',15);
	$pdf->Cell(17,10, numberToRomanRepresentation($column_number) . ". CONTACT INFORMATION","0","1","L",true);
	$pdf->Ln(1);
	$column_number++;

	$last_index=0;
	
	if(isset($lead_data->client_landline)){
		if(is_array($lead_data->client_landline)){
			$ctr = 0;
			for($i=0; $i<count($lead_data->client_landline); $i++){
				$left_content = "";
				if($i==0){
					$left_content = "Landline(s):";
				}
				if(($last_index)%2==0){
					$pdf->SetFillColor(190,215,239);
				}
				else{
					$pdf->SetFillColor(223,235,247);
				}
				$pdf->Rect(10,$pdf->GetY(),30,8,"F");
				$pdf->Rect(41,$pdf->GetY(),166,8,"F");
				$pdf->SetFont('Helvetica','',13);
				$pdf->Cell(31,8,$left_content);
				$pdf->Cell(167,8,$lead_data->client_landline[$i],"0","1","L",false);
				
				if($pdf->GetY() >= 312){
				    $pdf->AddPage();
				}
				else{
				    $pdf->Ln(1);
				}
				$last_index++;
			}
		}
		else{
			if(($last_index)%2==0){
				$pdf->SetFillColor(190,215,239);
			}
			else{
				$pdf->SetFillColor(223,235,247);
			}
			$pdf->Rect(10,$pdf->GetY(),30,8,"F");
			$pdf->Rect(41,$pdf->GetY(),166,8,"F");
			$pdf->SetFont('Helvetica','',13);
			$pdf->Cell(31,8,"Landline(s):");
			$pdf->Cell(167,8,$lead_data->client_landline,"0","1","L",false);

				if($pdf->GetY() >= 312){
				    $pdf->AddPage();
				}
				else{
				    $pdf->Ln(1);
				}
				$last_index++;
		}
	}

	if(isset($lead_data->client_mobile)){
		if(is_array($lead_data->client_mobile)){
			$ctr = 0;
			for($i=0; $i<count($lead_data->client_mobile); $i++){
				$left_content = "";
				if($i==0){
					$left_content = "Mobile(s):";
				}
				if(($last_index)%2==0){
					$pdf->SetFillColor(190,215,239);
				}
				else{
					$pdf->SetFillColor(223,235,247);
				}
				$pdf->Rect(10,$pdf->GetY(),30,8,"F");
				$pdf->Rect(41,$pdf->GetY(),166,8,"F");
				$pdf->SetFont('Helvetica','',13);
				$pdf->Cell(31,8,$left_content);
				$pdf->Cell(167,8,$lead_data->client_mobile[$i],"0","1","L",false);
				
				if($pdf->GetY() >= 312){
				    $pdf->AddPage();
				}
				else{
				    $pdf->Ln(1);
				}
				$last_index++;
			}
		}
		else{
			if(($last_index)%2==0){
				$pdf->SetFillColor(190,215,239);
			}
			else{
				$pdf->SetFillColor(223,235,247);
			}
			$pdf->Rect(10,$pdf->GetY(),30,8,"F");
			$pdf->Rect(41,$pdf->GetY(),166,8,"F");
			$pdf->SetFont('Helvetica','',13);
			$pdf->Cell(31,8,"Mobile(s):");
			$pdf->Cell(167,8,$lead_data->client_mobile,"0","1","L",false);

				if($pdf->GetY() >= 312){
				    $pdf->AddPage();
				}
				else{
				    $pdf->Ln(1);
				}
				$last_index++;
		}
	}
	
	if(isset($lead_data->client_email)){
		if(is_array($lead_data->client_email)){
			$ctr = 0;
			for($i=0; $i<count($lead_data->client_email); $i++){
				$left_content = "";
				if($i==0){
					$left_content = "Email(s):";
				}
				if(($last_index)%2==0){
					$pdf->SetFillColor(190,215,239);
				}
				else{
					$pdf->SetFillColor(223,235,247);
				}
				$pdf->Rect(10,$pdf->GetY(),30,8,"F");
				$pdf->Rect(41,$pdf->GetY(),166,8,"F");
				$pdf->SetFont('Helvetica','',13);
				$pdf->Cell(31,8,$left_content);
				$pdf->Cell(167,8,$lead_data->client_email[$i],"0","1","L",false);
				
				if($pdf->GetY() >= 312){
				    $pdf->AddPage();
				}
				else{
				    $pdf->Ln(1);
				}
				$last_index++;
			}
		}
		else{
			if(($last_index)%2==0){
				$pdf->SetFillColor(190,215,239);
			}
			else{
				$pdf->SetFillColor(223,235,247);
			}
			$pdf->Rect(10,$pdf->GetY(),30,8,"F");
			$pdf->Rect(41,$pdf->GetY(),166,8,"F");
			$pdf->SetFont('Helvetica','',13);
			$pdf->Cell(31,8,"Email(s):");
			$pdf->Cell(167,8,$lead_data->client_email,"0","1","L",false);
		
			if($pdf->GetY() >= 312){
				$pdf->AddPage();
			}
			else{
				$pdf->Ln(1);
			}
				$last_index++;
		}
	}


	if($pdf->GetY() >= 312){
	    $pdf->AddPage();
	}	

	$pdf->SetFillColor(209,226,243);
	$pdf->SetDrawColor(157,195,230);
	$pdf->Rect(8,$pdf->GetY(),201,10,"FD");
	$pdf->SetFont('Helvetica','B',15);
	$pdf->Cell(17,10, numberToRomanRepresentation($column_number) . ". APPOINTMENT INFORMATION","0","1","L",true);
	$pdf->Ln(1);
	$column_number++;

		
	$pdf->SetFillColor(190,215,239);
	$pdf->Rect(10,$pdf->GetY(),100,8,"F");
	$pdf->Rect(111,$pdf->GetY(),96,8,"F");
	$pdf->SetFont('Helvetica','',13);
	$pdf->Cell(101,8,"Date: " . $lead_data->appointment_date,"0","0","L",false);
	$pdf->Cell(96,8,"Time: " . $lead_data->appointment_hour . ":" . $lead_data->appointment_minute . " $lead_data->appointment_period","0","1","L",false);
	$pdf->Ln(1);

	$pdf->SetFillColor(223,235,247);
	$pdf->Rect(10,$pdf->GetY(),197,8,"F");
	$pdf->Cell(197,8,"Venue: " . $lead_data->venue,"0","1","L",false);
	$pdf->Ln(1);

	if($pdf->GetY() >= 312){
		$pdf->AddPage();
	}		
	$pdf->SetFillColor(209,226,243);
	$pdf->SetDrawColor(157,195,230);
	$pdf->Rect(8,$pdf->GetY(),201,10,"FD");
	$pdf->SetFont('Helvetica','B',15);
	$pdf->Cell(17,10, numberToRomanRepresentation($column_number) . ". NOTES","0","1","L",true);
	$pdf->Ln(1);
	$column_number++;

	$pdf->SetFillColor(190,215,239);
	$pdf->SetFont('Helvetica','',13);
	$pdf->SetAligns(array("L","L","L"));
	$pdf->SetWidths(array(1,195,1));
	$pdf->Rect(10,$pdf->GetY(),197,1,"FD");
	$pdf->Ln(1);
	$pdf->Row(array("",$lead_data->notes,""),true,array(190,215,239));
	$pdf->Rect(10,$pdf->GetY(),197,1,"FD");
	$pdf->Ln(1);

    $filename = str_replace("/"," or ", $lead_data->client_name) . " Data.pdf";
	$path="files/" .  $filename;
	$pdf->Output($path, 'F');

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

	/**
	 * @param int $number
	 * @return string
	 */
	function numberToRomanRepresentation($number) {
		$map = array('M' => 1000, 'CM' => 900, 'D' => 500, 'CD' => 400, 'C' => 100, 'XC' => 90, 'L' => 50, 'XL' => 40, 'X' => 10, 'IX' => 9, 'V' => 5, 'IV' => 4, 'I' => 1);
		$returnValue = '';
		while ($number > 0) {
				foreach ($map as $roman => $int) {
						if($number >= $int) {
								$number -= $int;
								$returnValue .= $roman;
								break;
						}
				}
		}
		return $returnValue;
	}

?>