<?php
session_start();
date_default_timezone_set('Pacific/Auckland');
require("fpdf/fpdf.php");
require("database.php");




 $con=mysqli_connect($host,$username,$password,$db)or die("could not connect to sql");
 if (!$con) {
        echo "<div>";
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
	    echo "</div>";	
}
//post
if(!isset($_POST["adviser_id"])){
header("Refresh:0; url=summary.php");
}
else{

if(!isset($_SESSION["x"])){
header("Refresh:0; url=summary.php");
}
else{
unset($_SESSION["x"]);
$adviser_id=$_POST["adviser_id"];
$start_date=$_POST["start_date"];
$until_date=$_POST["until_date"];
$_SESSION["prevnum"]=0;

$mystart_date = substr($start_date,6,4).substr($start_date,3,2).substr($start_date,0,2);
$myuntil_date = substr($until_date,6,4).substr($until_date,3,2).substr($until_date,0,2);
$datenow=date("Ymd");



$name = "SELECT * FROM adviser_tbl where id='$adviser_id'";


$displayname=mysqli_query($con,$name) or die('Could not look up user information; ' . mysqli_error($con));

$rows = mysqli_fetch_array($displayname);
$name=$rows["name"];




$email=$rows["email"];
$agency_pct=$rows["agency_pct"]/100;
$taxrate=$rows["tax_rate"]/100;//edited
$fsp_num=$rows["fsp_num"];

class PDF extends FPDF
{

function Footer()
{
global $fsp_num;
global $name;




//$pdf->SetXY($x+24,$y+200);
  if ( $this->PageNo() == 1 ) {

        $this->SetY(-35);
    $this->SetX(30);

  $this->SetFont('Arial','',8);
$this->MultiCell(170,3.5,"As an Independant Contractor you are responsible for your own taxation arrangements. Eliteinsure Limited recommends that you keep this statement in a safe place with your business records to assist you with the completion of your GST, Tax Returns and other business related requirements. A fee will apply for the reprinting and distribution of duplicated statements.",0,"C",false);
$this->SetAutoPageBreak(false);
//$this->SetXY($x+30,$y+330);
$this->SetTextColor(0,42,160);

$x = $this->GetX();
$y = $this->GetY();
 
 $this->SetXY($x+26,$y+1);
$this->MultiCell(170,3.5,"Street Address: 3G/38 Mackelvie Street Grey Lynn 1021 Auckland New Zealand | Contact: +6493789676
Email: admin@eliteinsure.co.nz | Website: www.eliteinsure.co.nz",0,"C",false);
        }


        $this->SetY(-15);
    //$this->SetX(30);

 $this->SetFont('Arial','',8);
$this->SetTextColor(0,0,0);


   	$this->Cell(0,10,'Adviser: '. $fsp_num.' '.preg_replace("/\([^)]+\)/","",$name),0,0,'L');	
    $this->Cell(0,10,'Page '.$this->PageNo(),0,1,'R');

  //  $this->SetFont('Arial','',8);
   // $this->SetTextColor(0,0,0);
   //	$this->Cell(0,10,'Adviser: '. $fsp_num.' '.preg_replace("/\([^)]+\)/","",$name),0,0,'L');	
   // $this->Cell(0,10,'Page '.$this->PageNo(),0,1,'R');

}
}

$mix="$name".$mystart_date."to".$myuntil_date;
$path="summary/".$mix.".pdf";

//search if existing pdf
$query = "SELECT filename FROM pdf_tbl where filename='$mix'";
$searchsum=mysqli_query($con,$query) or die('Could not look up user information; ' . mysqli_error($con));
$find = mysqli_num_rows($searchsum);

if($find>0){	//if existing or not	

echo "Already existing commission pdf. You can view/delete it <a href=pdf.php>here</a>";

}
else{
//xxxxxx



if($adviser_id=='all'){
$query = "SELECT * FROM summary_tbl where entrydate<='$myuntil_date'AND entrydate>='$mystart_date' ORDER BY entrydate ASC ";

}else{
$query = "SELECT * FROM summary_tbl where adviser_id='$adviser_id' AND entrydate<='$myuntil_date'AND entrydate>='$mystart_date' ORDER BY entrydate ASC ";

}



$displayquery=mysqli_query($con,$query) or die('Could not look up user information; ' . mysqli_error($con));



$pdf = new PDF('P', 'mm', 'Legal');
$x = $pdf->GetX();
$y = $pdf->GetY();

//page 1
$pdf->AddPage('P', 'Legal');
$pdf->Image('logo.png',10,10,-300);
$pdf->SetFont('Arial','B',12);
$pdf->SetTextColor(0,42,160);
$pdf->Cell(0,20,'Adviser Commission Summary  '.$start_date.' - '.$until_date,"0","1","C");
$pdf->SetTextColor(0,0,0);


function convertNum($x){

return number_format($x, 2, '.', ',');
}




 $pdf->Ln(10);
$pdf->SetFont('Arial','B',10);

if($adviser_id=='all'){
$pdf->Write(5,'All Advisers');
$name='All Advisers';
$email='sumit@eliteinsure.co.nz';
$adviser_id=0;
$mix="-$name".$mystart_date."to".$myuntil_date;
$path="summary/".$mix.".pdf";

}else{
$pdf->Write(5,'Adviser '. $name);  
}


$y=57;
$pdf->SetXY($x+10,$y);

$pdf->SetFont('Arial','',9);
$pdf->SetFillColor(224,224,224);
$pdf->Cell(37,8,'  Nett',"1","0",'C',"true");
$pdf->Cell(20,8,' GST ',"1","0",'C',"true");
$pdf->Cell(25,8,' Withoding Tax ',"1","0",'C',"true");
$pdf->Cell(30,8,' Annual Premium ',"1","0",'C',"true");
$pdf->Cell(30,8,' Payment Amount ',"1","0",'C',"true");
$pdf->Cell(37,8,'Agency Closing Balance',"1","0",'C',"true");
$pdf->Cell(25,8,' Date ',"1","1",'C',"true");

$netsum=0;
$gstsum=0;
$withodingtaxsum=0;
$annual_premsum=0;
$payment_amountsum=0;
$closing_balsum=0;

$page=0;
$limit=30;
WHILE($rows = mysqli_fetch_array($displayquery)){
if($page==$limit){
$pdf->AddPage('P', 'Legal');
$x=0;
$y=0;
$pdf->SetAutoPageBreak(false);
$limit=40;
$page=0;
}



$y+=8;	
$id=$rows["id"];
$net=$rows["net"];
//$net=$net*(1-$agency_pct);



$multiplytowithoding=1;
if($net>0){
$multiplytowithoding=-1;
}


$gst=$rows["gst"];
$withodingtax=$rows["withodingtax"]*$multiplytowithoding;//kevin edit
$annual_prem=$rows["annual_prem"];
$payment_amount=$rows["payment_amount"];
$entrydate=$rows["entrydate"];
$entrydate=substr($entrydate,6,2)."/".substr($entrydate,4,2)."/".substr($entrydate,0,4);
$closing_bal=$rows["closing_bal"];
$startingdate=$rows["startingdate"];

if($withodingtax==0){
$net=$net-($net-$payment_amount+$gst);
}

else{
$net=($withodingtax/-.2); 
} 

if($rows["net"]<0){
  $net*=-1;
}
if($rows["withodingtax"]<0){
  $withodingtax*=-1;
}




//$net=$rows["net"];
//$net=$net*(1-$agency_pct);

$netsum+=$net;
$gstsum+=$gst;
$withodingtaxsum+=$withodingtax;
$annual_premsum+=$annual_prem;
$payment_amountsum+=$payment_amount;

$closing_balsum=$closing_bal;
$closing_bal_agency=$closing_balsum-$_SESSION["prevnum"];

//FORMULAS end
//convert to 2 decimal number

//convert to 2 decimal number end

$pdf->SetXY($x+10,$y);
$pdf->SetFont('Arial','',9);
$pdf->SetFillColor(224,224,224);
$pdf->Cell(37,8,'$'.convertNum($net),"0","0",'C');
$pdf->Cell(20,8,'$'.convertNum($gst),"0","0",'C');
$pdf->Cell(25,8,'$'.convertNum($withodingtax),"0","0",'C');
$pdf->Cell(30,8,'$'.convertNum($annual_prem),"0","0",'C');
$pdf->Cell(30,8,'$'.convertNum($payment_amount),"0","0",'C');
$pdf->Cell(37,8,'$'.convertNum($closing_balsum),"0","0",'C');
$pdf->Cell(25,8,$entrydate,"0","1",'C');

$_SESSION["prevnum"]=$closing_balsum;
$page++;
}

//total

$pdf->SetFont('Arial','B',9);
$pdf->SetXY($x+10,$y+8);
$pdf->Cell(37,8,'$'.convertNum($netsum),"0","0",'C');
$pdf->Cell(20,8,'$'.convertNum($gstsum),"0","0",'C');
$pdf->Cell(25,8,'$'.convertNum($withodingtaxsum),"0","0",'C');
$pdf->Cell(30,8,'$'.convertNum($annual_premsum),"0","0",'C');
$pdf->Cell(30,8,'$'.convertNum($payment_amountsum),"0","0",'C');
$pdf->Cell(37,8,'$'.convertNum($closing_balsum),"0","0",'C');
$pdf->Cell(25,8,'',"0","1",'C');

$pdf->SetXY($x+5, $y+12); // position of text1, numerical, of course, not x1 and y1

$pdf->Write(0, "Total");


$pdf->Output($path,'F');


$sql="INSERT INTO pdf_tbl (adviser_id,name,email,link,filename,entrydate,type) 
VALUES ('$adviser_id','$name','$email','$path','$mix','$datenow','summary')"; 

mysqli_query($con,$sql);

header("Content-type: application/pdf");
header("Content-Disposition: inline; filename=".$path);
@readfile($path);
//db add end
}
}
}
?>