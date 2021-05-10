<?php
include_once("libs/api/classes/general.class.php");
include_once("libs/api/classes/dateHelper.class.php");
include_once("libs/api/controllers/AnnualReview.controller.php");
include_once("fpdf/mc_table.php");

$app = new General();
$dateHelper = new DateHelper();
$reviewController = new AnnualReviewController();

$review_id = $app->param($_GET, "id");
$review = $reviewController->getReview($review_id);
$review = $review->fetch_assoc();


class PDF extends PDF_MC_Table
{
    var $name = "";
    
	function Footer()
	{
		global $fsp_num;
		$this->SetY(-15);
		$this->SetFont('Helvetica','',10);
		$this->SetTextColor(0,0,0);
		$this->Cell(0,10,'Annual Review Form: '. $this->name .  ''.' '.preg_replace("/\([^)]+\)/","",''),0,0,'L');	
		$this->Cell(0,10,'Page '.$this->PageNo(),0,1,'R');
	}

	function Header()
	{	
		$this->SetFillColor(0,0,0);
		$this->Image('images/EliteInsure Header.png',0,0,216);
		$this->SetFont('Helvetica','B',18);
		$this->SetTextColor(0,0,0);
		$this->Cell(0,10,'',"0","1","C");
		$this->SetTextColor(0,0,0);
		$this->SetFont('Helvetica','B',10);
		$this->SetFillColor(224,224,224);
	}

	function getPage(){
		return $this->PageNo();
    }
    
    function question($question, $answer1){
        $answer1 = $this->convertHTMLAnswer($answer1);
        $this->Ln(5);
        $this->SetFont('Helvetica','',13);
        $this->SetWidths(array(200));
        $this->Row(array("$question"),false,array(255,255,255));
        $this->SetFont('Helvetica','U',13);
        $this->SetX(20);
        $this->SetWidths(array(180));
        $this->Row(array("$answer1")  ,false,array(255,255,255));

    }

    function questionWithExtra($question, $answer1, $answer2, $answerToUnlock2 = "Yes"){      
        $answer1 = $this->convertHTMLAnswer($answer1);
        $answer2 = $this->convertHTMLAnswer($answer2);
        $this->Ln(5);
        if($answer1== $answerToUnlock2){
            $answer1 = "$answer1, $answer2";
        }
        $this->SetFont('Helvetica','',13);
        $this->SetWidths(array(200));
        $this->Row(array($question),false,array(255,255,255));
        $this->SetFont('Helvetica','U',13);
        $this->SetX(20);
        $this->SetWidths(array(180));
        $this->Row(array("$answer1")  ,false,array(255,255,255));
    }
    
    function convertHTMLAnswer($answer){
        $answer = str_replace("<br>","\r\n",$answer);
        $answer = str_replace("u0027","'",$answer);
        return $answer;
    }
}
extract($review);
$data = json_decode($data);

$pdf = new PDF('P','mm','Legal');
$pdf->SetTitle('Annual Review');
$x = $pdf->GetX();
$y = $pdf->GetY();
$pdf->name = $name;
$date_reviewed_label = $dateHelper->DateToNZFormat2($date_reviewed);

//page 1
$pdf->AddPage('P', 'Legal');

//Title
$pdf->SetTextColor(0,123,255);
$pdf->SetDrawColor(0,123,255);
$pdf->SetLineWidth(1);
$pdf->SetFont('Helvetica','B',20);
$pdf->Cell(27,10,"","0","0","L");
$pdf->Cell(150,30,'EliteInsure Annual Review Checklist',"1","1","C",false);
$pdf->Ln(10);
$pdf->SetTextColor(0,0,0);
$pdf->SetFont('Helvetica','B',15);
$pdf->Cell(17,10,"Name:","0","0","L");
$pdf->SetFont('Helvetica','',15);
$pdf->Cell(188,10,$name,"0","1","L");

$pdf->SetFont('Helvetica','B',15);
$pdf->Cell(17,10,"Date:","0","0","L");
$pdf->SetFont('Helvetica','',15);
$pdf->Cell(188,10,$date_reviewed_label,"0","1","L");

$pdf->Ln(5);
$pdf->SetFont('Helvetica','BU',15);
$pdf->Cell(205,10,"In the last 12 months or since your last review:","0","1","L");



$pdf->SetFillColor(255,255,255);
$pdf->SetDrawColor(0,0,0);
$pdf->SetTextColor(0,0,0);


$pdf->SetAligns("L");

$question = "1. Have any of your contact details changed, i.e. phone numbers, address details, etc.? If yes, please specify.";
$answer1 = $data->contact_info_changed;
$answer2 = $data->new_contact_info;
$pdf->questionWithExtra($question,$answer1,$answer2);

$question = "2. Have your financial circumstances changed?";
$answer1 = $data->financial_circumstances;
$pdf->question($question,$answer1);

$question = "3. Have you had any financial issues and/or concerns? If yes, please specify.";
$answer1 = $data->financial_issues_and_concerns;
$answer2 = $data->financial_issues_and_concerns_details;
$pdf->questionWithExtra($question,$answer1,$answer2);

$question = "4. Have your goals changed in any way? If yes, please specify.";
$answer1 = $data->new_goals;
$answer2 = $data->new_goals_details;
$pdf->questionWithExtra($question,$answer1,$answer2);

$question = "5. Are you 'on track' to achieve those goals? Please give further details.";
$answer1 = $data->on_track_with_goals;
$answer2 = $data->on_track_with_goals_details;
$pdf->questionWithExtra($question,$answer1,$answer2);

$question = "6. Has your surplus income changed? (This is your net household income less
your total routine expenditure)?";
$answer1 = $data->surplus_income_changed;
$answer2 = $data->surplus_income_changed_value;
$pdf->questionWithExtra($question,$answer1,$answer2);

$question = "7. Has the value of your property or any other significant assets changed?";
$answer1 = $data->asset_value_changed;
$answer2 = $data->asset_value_changed_value;
$pdf->questionWithExtra($question,$answer1,$answer2);

$question = "8. Has the amount of your borrowing changed?";
$answer1 = $data->borrowing_amount_changed;
$answer2 = $data->borrowing_amount_changed_value;
$pdf->questionWithExtra($question,$answer1,$answer2);

$question = "9. Is your mortgage suitably structured and is the rate competitive?";
$answer1 = $data->mortgage_structured_suitably;
$pdf->question($question,$answer1);

$question = "10. Is your family trust operating correctly?";
$answer1 = $data->family_trust_operating_correctly;
$pdf->question($question,$answer1);

$question = "11. Is your Will up to date?";
$answer1 = $data->will_is_updated;
$pdf->question($question,$answer1);

$question = "12. Are Enduring Powers of Attorneys in place?";
$answer1 = $data->enduring_powers_of_attorneys;
$pdf->question($question,$answer1);

$question = "13. Are you maximising taxation benefits?";
$answer1 = $data->maximising_taxation_benefits;
$pdf->question($question,$answer1);

$question = "14. Are you aware of the changes to taxation law and what impact these changes
may have on you (if any)?";
$answer1 = $data->maximising_taxation_benefits;
$pdf->question($question,$answer1);

$question = "15. Are there any health issues that might impact on your insurances and ongoing
finances?";
$answer1 = $data->has_health_issues;
$pdf->question($question,$answer1);

$question = "16. Is your insurance cover appropriate for your needs?";
$answer1 = $data->insurance_appropriate_for_needs;
$pdf->question($question,$answer1);

$question = "17. Are your premiums affordable and do you feel you are receiving good value for
your money?";
$answer1 = $data->premiums_are_affordable;
$pdf->question($question,$answer1);

$question = "18. Have any changes occurred that may mean your insurance cover needs to be
reconsidered?";
$answer1 = $data->reconsidering_insurance_cover;
$pdf->question($question,$answer1);

$question = "19. Are you happy with your current job(s) or are you considering a job change?";
$answer1 = $data->contented_with_job;
$pdf->question($question,$answer1);

$question = "20. Has your marital status or family situation changed (i.e. married, divorced,
separated, had children, death of family member?) If yes, please specify.";
$answer1 = $data->marital_status_changed;
$answer2 = $data->marital_status_changed_details;
$pdf->questionWithExtra($question,$answer1, $answer2);

$question = "21. Do you have an appropriate level of fire and general insurance cover?";
$answer1 = $data->contented_with_job;
$pdf->question($question,$answer1);

$question = "22. Have you been bequeathed any inheritances or received any additional money
since our last review? If yes, please specify.";
$answer1 = $data->inherited_money;
$answer2 = $data->inherited_money_details;
$pdf->questionWithExtra($question,$answer1, $answer2);

$question = "23. Are you aware of any additional money you might receive in the coming 12
months? If yes, please specify.";
$answer1 = $data->expecting_money;
$answer2 = $data->expecting_money_details;
$pdf->questionWithExtra($question,$answer1, $answer2);

$question = "24. Is there anything else you wish to advise that may affect your insurance cover
that has not been covered here?";
$answer1 = $data->extra_information;
$pdf->question($question,$answer1);

$mix = "";
//$mix="$mydateorig"."-$name-"."BCTI".-date("ymd");
//$path="files/".$mix.".pdf";

$path="files/preview.pdf";
//$pdf->Output($path,'F');
$pdf->Output("I","$name Annual Review Form " . $date_reviewed . ".pdf");

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