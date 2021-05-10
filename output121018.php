<?php
date_default_timezone_set('Pacific/Auckland');
require("fpdf/fpdf.php");






require("database.php");
session_start();

 $con=mysqli_connect($host,$username,$password,$db)or die("could not connect to sql");
 if (!$con) {
        echo "<div>";
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
	    echo "</div>";	
}
//post
if(!isset($_SESSION["x"])){
//header("Refresh:0; url=create_payroll.php");
}

else{
unset($_SESSION['x']);
$adviser_id=$_POST["adviser_id"];
$ei_com=$_POST["ei_com"];
$ei_gst=$_POST["ei_gst"];
$sundries=$_POST["sundries"];
$ei_cancel_amt=$_POST["ei_cancel_amt"];
$ei_rencom=$_POST["ei_rencom"];
$bonuses=$_POST["bonuses"];
$ei_rencan=$_POST["ei_rencan"];
$ei_rengst=$_POST["ei_rengst"];
$openbal=$_POST["openbal"];
$client_name=array_filter($_POST["client_name"]);//array
$ei_gstcan=$_POST["ei_gstcan"];
$annual_prem=$_POST["annual_prem"];
$mydate=$_POST["mydate"];
$mymonth=$_POST["mymonth"];
$myyear=$_POST["myyear"];
$ei_cancel_amt=$_POST["ei_cancel_amt"];
$ei_rencangst=$_POST["ei_rencangst"];
$agencyrelease=$_POST["agencyrelease"];
$leads_qty=$_POST["leads"];


//arrays
$ei_com_arr=$ei_com;
$ei_rencom_arr=$ei_rencom;
$ei_gst_arr=$ei_gst;
$ei_rengst_arr=$ei_rengst;
$ei_gstcan_arr=$ei_gstcan;
$ei_cancel_amt_arr=$ei_cancel_amt;
$ei_rencan_arr=$ei_rencan;
$annual_prem_arr=$annual_prem;

$ei_rencangst_arr=$ei_rencangst;




//end arrays

//array sum
$ei_com=array_sum($ei_com);
$ei_rencom=array_sum($ei_rencom);
$ei_gst=array_sum($ei_gst);
$ei_rengst=array_sum($ei_rengst);
$ei_gstcan=array_sum($ei_gstcan);
$ei_cancel_amt=array_sum($ei_cancel_amt);
$ei_rencan=array_sum($ei_rencan);
$annual_prem=array_sum($annual_prem);
$ei_rencangst=array_sum($ei_rencangst);

//array sum end

if(!isset($adviser_id)){
	    header("Location: create_payroll.php");
}

  $con=mysqli_connect($host,$username,$password,$db)or die("could not connect to sql");
 if (!$con) {
        echo "<div>";
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
	    echo "</div>";	
}

//post end

$query = "SELECT * FROM adviser_tbl where id='$adviser_id'";
$displayquery=mysqli_query($con,$query) or die('Could not look up user information; ' . mysqli_error($con));
$rows = mysqli_fetch_array($displayquery);
$id=$rows["id"];
$name=$rows["name"];
$fsp_num=$rows["fsp_num"];
$address=$rows["address"];
$ird_num=$rows["ird_num"];
$com_pct=$rows["com_pct"];
$tax_rate=$rows["tax_rate"];
//$gst_pct=$rows["gst_pct"];
$mat_fee=$rows["mat_fee"];
$agency_pct=$rows["agency_pct"];

$gst_reg=$rows["gst_reg"];//new
$ren_pct=$rows["ren_pct"];//new
$term_date=$rows["term_date"];//new

$email=$rows["email"];//new

$manager_id=$rows["manager_id"];//manager
$manager_pct=$rows["manager_pct"];//manager


$leads_price=$rows["leads"];
//$manager_id_jr=$rows["manager_id_jr"];//manager jr
//$manager_pct_jr=$rows["manager_pct_jr"];//manager

class PDF extends FPDF
{

function Footer()
{
global $fsp_num;
global $name;
    $this->SetY(-15);
    $this->SetFont('Arial','',8);
    $this->SetTextColor(0,0,0);
   	$this->Cell(0,10,'Adviser: '. $fsp_num.' '.preg_replace("/\([^)]+\)/","",$name),0,0,'L');	
    $this->Cell(0,10,'Page '.$this->PageNo(),0,1,'R');

}
}



//ifs
if($gst_reg=="yes"){
}
else{
$ei_gst=0;
$ei_gstcan=0;
$ei_rencangst=0;
$ei_rencangst=0;
}

//ifs ends
//convertion
$com_pct=$com_pct/100;
$ei_com=$ei_com*$com_pct;
$ei_cancel_amt=$ei_cancel_amt*$com_pct;
$tax_rate=$tax_rate/100;
//$gst_pct=$gst_pct/100;

$ren_pct=$ren_pct/100;//new



$leads=$leads_qty*$leads_price;
$leads_gst=$leads*.15;
//convertion end




$statementweek=date("YW");

$datenow=date("d/m/Y");

$newmonth=$mymonth;
if($newmonth<10){
$newmonth="0".$newmonth;
}

$mydateorig=$myyear.$newmonth.$mydate;

if($mydate=="1"){

$mydate=$newmonth."/1/".$myyear;
$mydate = date("d/m/Y",strtotime($mydate));
$newdate=$newmonth."/15/".$myyear;
$date2weeks = date("d/m/Y",strtotime($newdate));
}
else{

$mydate=$newmonth."/15/".$myyear;
$mydate = date("d/m/Y",strtotime($mydate));
$newdate=$newmonth."/".date('t')."/".$myyear;
$date2weeks = date("d/m/Y",strtotime($newdate));
}

//taxes
//$query_tax = "SELECT A.prod,A.canc,B.name as man FROM com_and_canc as A LEFT JOIN  adviser_tbl as B ON a.adviser_id = B.id where a.manager_id='$adviser_id' and a.startingdate='$mydateorig'  ";



$query_tax = "SELECT A.adviser_id,B.name as man FROM summary_tbl as A LEFT JOIN adviser_tbl as B ON A.adviser_id = B.id where B.manager_id='$adviser_id' and A.startingdate='$mydateorig'  ";
$displaytax=mysqli_query($con,$query_tax) or die('Could not look up user information; ' . mysqli_error($con));
$rows_tax = mysqli_fetch_array($displaytax);
//taxes









//search if existing summary
$query = "SELECT adviser_id,entrydate FROM summary_tbl where adviser_id='$adviser_id' AND startingdate='$mydateorig'";
$searchsum=mysqli_query($con,$query) or die('Could not look up user information; ' . mysqli_error($con));
$find = mysqli_num_rows($searchsum);

if($find>0 && $_POST["edit"]!='true'){	//if existing or not	

echo "Already existing in summary. You can view/delete it <a href=summary.php>here</a>";

}
else{
//xxxxxx

if($_POST["edit"]=='true'){


$delsum="DELETE FROM summary_tbl WHERE startingdate='$mydateorig' AND adviser_id='$adviser_id'";
mysqli_query($con, $delsum);
$delbcti="DELETE FROM pdf_tbl WHERE filename LIKE '$mydateorig%' AND adviser_id='$adviser_id'";
mysqli_query($con, $delbcti);
$delclients="DELETE FROM clients_tbl WHERE startingdate='$mydateorig' AND adviser='$adviser_id'";
mysqli_query($con, $delclients);
}


$tot_over=0;
$tot_balo=0;
$tot_newgst=0;
$tot_ap=0;

$tot_over_can=0;
$tot_balo_can=0;
$tot_newgst_can=0;
$tot_ap_can=0;


if($rows_tax){
$hey=$rows_tax['adviser_id'];
$totalbal=0;
$totalap=0;
$totalap+=$annual_prem_arr[$x];
$ai=$rows_tax['adviser_id'];
//searchsql="SELECT * FROM clients_tbl WHERE id='$ai' AND startingdate='$mydateorig'";
$searchsql="SELECT A.* , B.manager_pct, B.gst_reg, B.name as adname FROM clients_tbl as A LEFT JOIN adviser_tbl as B ON A.adviser=B.id WHERE A.startingdate='$mydateorig' AND (A.com!=0 OR A.gst!=0) AND B.manager_id='$adviser_id'";




$search=mysqli_query($con,$searchsql) or die('Could not look up user information; ' . mysqli_error($con));
$num_rows = mysqli_num_rows($search);


if($num_rows>0):
 WHILE($rows = mysqli_fetch_array($search)):
/*if($rows["gst_reg"]=="no"){
$rows["manager_pct"]=0;
}*/
$over=$rows["com"]*($rows["manager_pct"]/100);
$newgst=$rows["gst"]*($rows["manager_pct"]/100);
$balance=$over+$newgst;
$ap=$rows["annual_prem"];
$tot_over+=$over;
$tot_newgst+=$newgst;
$tot_balo+=$balance;
$tot_ap+=$ap;
endwhile;
endif;


$searchsql="SELECT A.* , B.manager_pct, B.gst_reg,B.name as adname FROM clients_tbl as A LEFT JOIN adviser_tbl as B ON A.adviser=B.id  WHERE startingdate='$mydateorig' AND (A.gstcan!=0 OR A.cancel_amt!=0) AND B.manager_id='$adviser_id'";;
$search=mysqli_query($con,$searchsql) or die('Could not look up user information; ' . mysqli_error($con));
$num_rows = mysqli_num_rows($search);
if($num_rows>0):
 WHILE($rows = mysqli_fetch_array($search)):
/*if($rows["gst_reg"]=="no"){
$rows["manager_pct"]=0;
}*/
$over_can=$rows["cancel_amt"]*($rows["manager_pct"]/100);
$newgst_can=$rows["gstcan"]*($rows["manager_pct"]/100);
$balance_can=$over+$newgst;
$ap_can=$rows["annual_prem"];
$tot_over_can+=$over_can;
$tot_newgst_can+=$newgst_can;
$tot_balo_can+=$balance_can;
$tot_ap_can+=$ap_can;
endwhile;
endif;

}



$adviser=strtoupper($name);
$adviser_number=$fsp_num;
$advisor_address=$address;
$comission=$ei_com;
$material_fee=$mat_fee;
$material_gst=$material_fee*.15;
//FORMULAS start
$cancellation=$ei_cancel_amt;//here
//$cancellation=$ei_cancel_amt*$com_pct;//here
$cancel_gst=$ei_gstcan*$com_pct;
$ei_rencom=$ei_rencom*$ren_pct;//new
$ei_rencangst=$ei_rencangst*$ren_pct;
$credit=$comission+$ei_rencom+$bonuses+$tot_over;		//total //with manager's commission
$debit=$cancellation+$material_fee+$sundries+$ei_rencan+$tot_over_can+$tot_over_can+$leads;
$ei_rencan=$ei_rencan*$ren_pct;
//$net=$comission-$cancellation-$material_fee;		OLD FORMULA
$net=$credit-$debit;
$agencymovement=$net*($agency_pct/100);

$opening_bal=$openbal;
$agencymovment_in_aa=$agencymovement;//NOT SET must be add with database


$gst=$ei_gst;
$ei_gst=$ei_gst*$com_pct;
$ei_gstcan=$ei_gstcan*$com_pct;


$ei_rengst=$ei_rengst*$ren_pct;

if($gst_reg=="yes"){
$bonuses_gst=$bonuses*.15;
}else{
$bonuses_gst=0;
}



//$agencymovementout=(($net*$tax_rate*-1)+$net-$cancel_gst*1)*-1;


if($ei_gst==0){
	//$bonuses_gst=0;
	$ei_rengst=0;
}


$sundries_gst=$sundries*.15;

$credit_gst=$ei_gst+$ei_rengst+$bonuses_gst+$tot_newgst;	//total //with manager's commission


$debit_gst=$material_gst+$sundries_gst+$ei_rencangst+$ei_gstcan+$tot_newgst_can+$leads_gst;
$gst_total=$credit_gst-$debit_gst;




if($net>0){


$agencymovement_sum=$agencymovment_in_aa+$opening_bal-$agencyrelease;


$closing_bal=$opening_bal+$agencymovment_in_aa-$agencyrelease;
$withodingtax=($net-$agencymovement)*$tax_rate;
$payment_amount=$net-$withodingtax-$agencymovement+$gst_total+$agencyrelease;



}
else{

$net=$agencyrelease-$debit;

//12/10/18
if($cancellation!=0&&$comission>0){
  $withodingtax=0;
}
else{
    $withodingtax=$net*$tax_rate;
}
//12/10/18

//$withodingtax=$net*$tax_rate;


$agencymovementout=($net-($gst_total*-1))-$withodingtax;
$payment_amount=($net-($gst_total*-1))-$agencymovementout-$withodingtax;
if($agencyrelease>0){
$payment_amount=$agencyrelease-$withodingtax;
}
$agencymovment_in_aa=0;
$agencymovement_sum=$opening_bal-$agencyrelease-($agencymovementout*-1);



$closing_bal=$agencymovement_sum;

if($net<=0 && $agencymovement_sum>0){
$net=$agencyrelease-$debit+$tot_over;
$withodingtax=$net*$tax_rate;
$agencymovementout=($net-($gst_total*-1))-$withodingtax;
$payment_amount=($net-($gst_total*-1))-$agencymovementout-$withodingtax;
$agencymovment_in_aa=0;
$agencymovement_sum=$opening_bal+$agencymovementout;

$closing_bal=$opening_bal+$agencymovementout;
}
//$payment_amount=0;
}



$agencyreleasesum=$opening_bal-$agencyrelease;
//orig
$net_orig=$net;
$gst_total_orig=$gst_total;
$withodingtax_orig=$withodingtax;
$annual_prem_orig=$annual_prem;
$payment_amount_orig=$payment_amount;
$closing_bal_orig=$closing_bal;

$ei_com_orig=$ei_com_arr;
$ei_rencom_orig=$ei_rencom_arr;
$ei_gst_orig=$ei_gst_arr;
$ei_rengst_orig=$ei_rengst_arr;
$ei_gstcan_orig=$ei_gstcan_arr;
$ei_cancel_amt_orig=$ei_cancel_amt_arr;
$ei_rencan_orig=$ei_rencan_arr;
$annual_prem_orig=$annual_prem_arr;

$ei_rencangst_orig=$ei_rencangst_arr;
$cancel_arr=$cancel;

//orig end

//if with tax





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

//convert to 2 decimal number end

$pdf = new PDF('P', 'mm', 'Legal');
$x = $pdf->GetX();
$y = $pdf->GetY();

//page 1
$pdf->AddPage('P', 'Legal');
$pdf->Image('logo.png',10,10,-300);
$pdf->SetFont('Arial','B',14);
$pdf->SetTextColor(0,42,160);
$pdf->Cell(0,20,'Buyer Created Tax Invoice',"0","1","C");
$pdf->SetTextColor(0,0,0);
$pdf->SetFont('Arial','B',10);
$pdf->SetFillColor(224,224,224);

$pdf->Cell(150,10,'   Adviser No: '.$adviser_number.'   '.removeparent($adviser),"0","0",'',"true");
$pdf->Cell(0,10,$mydate.'-'.$date2weeks,"0","1",'',"true");
$pdf->SetFont('Arial','BU',9);
$pdf->Cell(50,20,'Produced on :',0,0,"L");
$pdf->Cell(80,20,'Produced by :',0,0,"C");
$pdf->Cell(40,20,'Statement Week :',0,0,"C");	



$pdf->SetFont('Arial','',9);
$pdf->Cell(30,20,$statementweek,0,1,"L");
$pdf->SetFont('Arial','',9);
$pdf->SetXY($x+33,$y+57);
$pdf->MultiCell(40,5,$advisor_address,0,"L",false);
$pdf->SetXY($x+72,$y+54);
$pdf->SetFont('Arial','',9);
$pdf->MultiCell(55,4,"Eliteinsure limited
3G/39 Mackelvie Street Grey Lynn 1021 Auckland New Zealand +6493789676
www.eliteinsure.co.nz",0,"C",false);

$pdf->SetXY($x+146, $y+55); // position of text1, numerical, of course, not x1 and y1
$pdf->SetFont('Arial','BU',9);
$pdf->Write(0, 'Statement Date :');

$pdf->SetXY($x+179, $y+55); // position of text1, numerical, of course, not x1 and y1
$pdf->SetFont('Arial','',9);
$pdf->Write(0, $date2weeks);


$pdf->SetXY($x+146, $y+60); // position of text1, numerical, of course, not x1 and y1
$pdf->SetFont('Arial','BU',9);
$pdf->Write(0, 'Payment Type :');

$pdf->SetXY($x+179, $y+60); // position of text1, numerical, of course, not x1 and y1
$pdf->SetFont('Arial','',9);
$pdf->Write(0,'Direct Credit');

$pdf->SetXY($x+146, $y+65); // position of text1, numerical, of course, not x1 and y1
$pdf->SetFont('Arial','BU',9);
$pdf->Write(0,'Termination Date :');

$pdf->SetXY($x+146, $y+70); // position of text1, numerical, of course, not x1 and y1
$pdf->SetFont('Arial','BU',9);
$pdf->Write(0,'IRD :');

$pdf->SetXY($x+179, $y+70); // position of text1, numerical, of course, not x1 and y1
$pdf->SetFont('Arial','',9);
$pdf->Write(0,$ird_num);

$pdf->SetXY($x+33, $y+50); // position of text1, numerical, of course, not x1 and y1
$pdf->SetFont('Arial','',9);
$pdf->Write(0,$datenow);

$pdf->SetXY($x+33, $y+55); // position of text1, numerical, of course, not x1 and y1
$pdf->SetFont('Arial','',9);
$pdf->Write(0,removeparent($name));

$pdf->SetXY($x+179, $y+65); // position of text1, numerical, of course, not x1 and y1
$pdf->SetFont('Arial','',9);
$pdf->Write(0,substr($term_date,0,2));



$pdf->SetXY($x+10,$y+80);




$pdf->SetFont('Arial','B',11);

$pdf->Cell(0,15,' Buyer Created Tax Invoice ',"0","1",'L');
$pdf->SetFont('Arial','',9);
$pdf->SetFillColor(224,224,224);
$pdf->Cell(30,8,' Date ',"1","0",'C',"true");
$pdf->Cell(60,8,'  Description ',"1","0",'L',"true");
$pdf->Cell(30,8,' Debit ',"1","0",'C',"true");
$pdf->Cell(30,8,' Credit ',"1","0",'C',"true");
$pdf->Cell(40,8,' GST ',"1","1",'C',"true");

if($comission==0){

}
else{
$pdf->Cell(30,8,$datenow,"1","0",'C');
$pdf->Cell(60,8,'  Commissions ',"1","0",'L');
$pdf->Cell(30,8,'',"1","0",'C');
$pdf->Cell(30,8,'$'.convertNum($comission),"1","0",'C');
$pdf->Cell(40,8,'$'.convertNum($ei_gst) ,"1","1",'C');
}




if($rows_tax>0&&$tot_over>0){
$pdf->Cell(30,8,$datenow,"1","0",'C');
$pdf->Cell(60,8,"  Overrides","1","0",'L');
$pdf->Cell(30,8,'',"1","0",'C');
$pdf->Cell(30,8,'$'.convertNum($tot_over),"1","0",'C');
$pdf->Cell(40,8,'$'.convertNum($tot_newgst) ,"1","1",'C');
}
//here

    

if($ei_rencom==0){

}
else{
$pdf->Cell(30,8,$datenow,"1","0",'C');
$pdf->Cell(60,8,'  Renewal Commissions ',"1","0",'L');
$pdf->Cell(30,8,'',"1","0",'C');
$pdf->Cell(30,8,'$'.convertNum($ei_rencom),"1","0",'C');
$pdf->Cell(40,8,'$'.convertNum($ei_rengst) ,"1","1",'C');
}

if($bonuses==0){

}
else{
$pdf->Cell(30,8,$datenow,"1","0",'C');
$pdf->Cell(60,8,'  Bonuses ',"1","0",'L');
$pdf->Cell(30,8,'',"1","0",'C');
$pdf->Cell(30,8,'$'.convertNum($bonuses),"1","0",'C');
$pdf->Cell(40,8,'$'.convertNum($bonuses_gst) ,"1","1",'C');
}


$pdf->Cell(30,8,'',"1","0",'C');
$pdf->Cell(60,8,'  ',"1","0",'L');
$pdf->Cell(30,8,'$0.00',"1","0",'C');
$pdf->Cell(30,8,'$'.convertNum($credit),"1","0",'C');
$pdf->Cell(40,8,'$'.convertNum($credit_gst) ,"1","1",'C');


$pdf->SetFont('Arial','B',11);

$pdf->Cell(0,15,' Buyer Created Adjusted Note ',"0","1",'L');
$pdf->SetFont('Arial','',9);
$pdf->SetFillColor(224,224,224);
$pdf->Cell(30,8,' Date ',"1","0",'C',"true");
$pdf->Cell(60,8,'  Description ',"1","0",'L',"true");
$pdf->Cell(30,8,' Debit ',"1","0",'C',"true");
$pdf->Cell(30,8,' Credit ',"1","0",'C',"true");
$pdf->Cell(40,8,' GST ',"1","1",'C',"true");


//xxxxx
if($cancellation==0){

}
else{
$pdf->Cell(30,8,$datenow,"1","0",'C');
$pdf->Cell(60,8,'  Cancellations ',"1","0",'L');
$pdf->Cell(30,8,'$'.convertNegNum($cancellation),"1","0",'C');
$pdf->Cell(30,8,'',"1","0",'C');
$pdf->Cell(40,8,'$'.convertNegNum($ei_gstcan) ,"1","1",'C');
}

if($rows_tax>0&&$tot_over_can>0){
$pdf->Cell(30,8,$datenow,"1","0",'C');
$pdf->Cell(60,8,"  Overrides","1","0",'L');
$pdf->Cell(30,8,'$'.convertNegNum($tot_over_can),"1","0",'C');
$pdf->Cell(30,8,'',"1","0",'C');
$pdf->Cell(40,8,'$'.convertNegNum($tot_newgst_can) ,"1","1",'C');
}

if($ei_rencan==0){

}
else{
$pdf->Cell(30,8,$datenow,"1","0",'C');
$pdf->Cell(60,8,'  Renewal Cancellations ',"1","0",'L');
$pdf->Cell(30,8,'$'.convertNegNum($ei_rencan),"1","0",'C');
$pdf->Cell(30,8,'',"1","0",'C');
$pdf->Cell(40,8,'$'.convertNegNum($ei_rencangst) ,"1","1",'C');
}

if($material_fee==0){

}

else{
$pdf->Cell(30,8,$datenow,"1","0",'C');
$pdf->Cell(60,8,'  Material & Software Fee',"1","0",'L');
$pdf->Cell(30,8,'$'.convertNegNum($material_fee),"1","0",'C');
$pdf->Cell(30,8,'',"1","0",'C');
$pdf->Cell(40,8,'$'.convertNegNum($material_gst) ,"1","1",'C');
}

if($sundries==0){

}

else{
$pdf->Cell(30,8,$datenow,"1","0",'C');
$pdf->Cell(60,8,'  Sundries',"1","0",'L');
$pdf->Cell(30,8,'$'.convertNegNum($sundries),"1","0",'C');
$pdf->Cell(30,8,'',"1","0",'C');
$pdf->Cell(40,8,'$'.convertNegNum($sundries_gst) ,"1","1",'C');
}


if($leads==0){

}

else{
$pdf->Cell(30,8,$datenow,"1","0",'C');
$pdf->Cell(60,8,'  Leads',"1","0",'L');
$pdf->Cell(30,8,'$'.convertNegNum($leads),"1","0",'C');
$pdf->Cell(30,8,'',"1","0",'C');
$pdf->Cell(40,8,'$'.convertNegNum($leads_gst) ,"1","1",'C');
}




$pdf->Cell(30,8,'',"1","0",'C');
$pdf->Cell(60,8,'',"1","0",'L');
$pdf->Cell(30,8,'$'.convertNegNum($debit),"1","0",'C');
$pdf->Cell(30,8,'$0.00',"1","0",'C');
$pdf->Cell(40,8,'$'.convertNegNum($debit_gst),"1","1",'C');
$pdf->SetFont('Arial','B',9);

$pdf->Cell(30,8,'',"0","0",'C');
$pdf->Cell(60,8,'  Totals  ',"0","0",'R');
$pdf->Cell(30,8,'$'.convertNum($debit),"0","0",'C');
$pdf->Cell(30,8,'$'.convertNum($credit),"0","0",'C');
$pdf->Cell(40,8,'   Nett   $'.convertNum($net) ,"0","1",'C');

$pdf->Cell(30,8,'',"0","0",'C');	
$pdf->Cell(60,8,'',"0","0",'R');
$pdf->Cell(30,8,'',"0","0",'C');
$pdf->Cell(30,8,'',"0","0",'C');
$pdf->Cell(40,8,'   GST   $'.convertNum($gst_total) ,"0","1",'C');


if(isset($agencyrelease)){
$pdf->Cell(30,8,'',"0","0",'C');	
$pdf->Cell(60,8,'',"0","0",'R');
$pdf->Cell(30,8,'',"0","0",'C');
$pdf->Cell(32,8,'',"0","0",'C');
$pdf->Cell(22,8,'   Agency Release   $'.convertNum($agencyrelease) ,"0","1",'C');
}


$pdf->Cell(30,8,'',"0","0",'C');	
$pdf->Cell(60,8,'',"0","0",'R');
$pdf->Cell(10,8,'',"0","0",'C');
$pdf->Cell(15,8,'',"0","0",'C');



if($net>=0){

if($agencyrelease==0){
$pdf->Cell(44,8,'   Agency Movement in Payment Details '.convertNum($agency_pct).'%          $'.convertNegNum($agencymovement) ,"0","1",'C');



$pdf->Cell(30,8,'',"0","0",'C');	
$pdf->Cell(60,8,'',"0","0",'R');
$pdf->Cell(10,8,'',"0","0",'C');
$pdf->Cell(37,8,'',"0","0",'C');
$pdf->Cell(50,8,'   Withoding tax        $'. convertNegNum($withodingtax),"0","1",'C');
}
else{
$pdf->Cell(95,8,'   Withoding tax        $'. convertNegNum($withodingtax),"0","1",'C');
}
}
elseif($net<0){
if($agencyrelease==0){
$pdf->Cell(44,8,'                    Agency Movement Out Payment Details    $'.convertNum($agencymovementout*-1) ,"0","1",'C');

$pdf->Cell(30,8,'',"0","0",'C');	
$pdf->Cell(60,8,'',"0","0",'R');
$pdf->Cell(10,8,'',"0","0",'C');
$pdf->Cell(37,8,'',"0","0",'C');
$pdf->Cell(50,8,'   Withoding tax        $'. convertNegNum($withodingtax),"0","1",'C');
}
else{
$pdf->Cell(95,8,'   Withoding tax        $'. convertNum($withodingtax),"0","1",'C');
}
}

$pdf->Cell(30,8,'',"0","0",'C');	
$pdf->Cell(60,8,'',"0","0",'R');
$pdf->Cell(40,8,'',"0","0",'C');
$pdf->Cell(40,8,' Payment Amount',"0","0",'C');
$pdf->SetTextColor(247,9,9);
$pdf->Cell(8,8,'         $'. convertNum($payment_amount),"0","1",'C');

$pdf->SetFont('Arial','B',11);
$pdf->SetTextColor(0,0,0);
$pdf->Cell(0,15,' Agency Account Details ',"0","1",'L');
$pdf->SetFont('Arial','',9);
$pdf->SetFillColor(224,224,224);
$pdf->Cell(30,8,' ',"1","0",'C',"true");
$pdf->Cell(60,8,'  Description ',"1","0",'L',"true");
$pdf->Cell(30,8,' Debit ',"1","0",'C',"true");
$pdf->Cell(30,8,' Credit ',"1","0",'C',"true");
$pdf->Cell(40,8,' Balance ',"1","1",'C',"true");

$pdf->Cell(30,8,'',"1","0",'C');
$pdf->Cell(60,8,'  Opening Balance ',"1","0",'L');
$pdf->Cell(30,8,'',"1","0",'C');
$pdf->Cell(30,8,'',"1","0",'C');
$pdf->Cell(40,8,'$'.convertNum($opening_bal) ,"1","1",'C');
if($agencyrelease==0){


}else{
$pdf->Cell(30,8,'',"1","0",'C');
$pdf->Cell(60,8,'  Agency Release ',"1","0",'L');
$pdf->Cell(30,8,'$'.convertNum($agencyrelease*-1),"1","0",'C');
$pdf->Cell(30,8,'',"1","0",'C');
$pdf->Cell(40,8, '$'.convertNum($agencyreleasesum),"1","1",'C');
}




	if($net>=0){
if($agencymovment_in_aa!=0){
    $pdf->Cell(30,8,'',"1","0",'C');
$pdf->Cell(60,8,'  Agency Movement In Agency Account',"1","0",'L');
$pdf->Cell(30,8,'',"1","0",'C');
$pdf->Cell(30,8,'$'.convertNum($agencymovment_in_aa),"1","0",'C');
$pdf->Cell(40,8,'$'.convertNum($agencymovement_sum),"1","1",'C');
}

}
else{
$pdf->Cell(30,8,'',"1","0",'C');
$pdf->Cell(60,8,'  Agency Movement Out Agency Account',"1","0",'L');
$pdf->Cell(30,8,'$'.convertNum($agencymovementout),"1","0",'C');
$pdf->Cell(30,8,'',"1","0",'C');
$pdf->Cell(40,8,'$'.convertNum($agencymovement_sum),"1","1",'C');
}







$pdf->SetFont('Arial','B',9);
$pdf->Cell(30,8,'',"1","0",'C');
$pdf->Cell(60,8,'  Closing Balance',"1","0",'L');
$pdf->Cell(30,8,'',"1","0",'C');
$pdf->Cell(30,8,'',"1","0",'C');
$pdf->Cell(40,8,'$'.convertNum($closing_bal) ,"1","1",'C');

$pdf->SetXY($x+24,$y+318);
$pdf->SetFont('Arial','',8);
$pdf->MultiCell(170,3.5,"As an Independant Contractor you are responsible for your own taxation arrangements. Eliteinsure Limited recommends that you keep this statement in a safe place with your business records to assist you with the completion of your GST, Tax Returns and other business related requirements. A fee will apply for the reprinting and distribution of duplicated statements.",0,"C",false);
$pdf->SetAutoPageBreak(false);
$pdf->SetXY($x+30,$y+330);
$pdf->SetTextColor(0,42,160);
$pdf->MultiCell(170,3.5,"Street Address: 3G/38 Mackelvie Street Grey Lynn 1021 Auckland New Zealand | Contact: +6493789676
Email: admin@eliteinsure.co.nz | Website: www.eliteinsure.co.nz",0,"C",false);
//page 2
$pdf->AddPage('P', 'Legal');



$pdf->Image('logo.png',10,10,-300);
$pdf->SetFont('Arial','B',14);
$pdf->SetTextColor(0,42,160);
$pdf->Cell(0,20,'Detail Commission Statement',"0","1","C");

$pdf->SetTextColor(0,0,0);
$pdf->SetFont('Arial','B',10);


$pdf->Cell(150,10,'   Adviser No: '.$adviser_number.'   '.removeparent($adviser),"0","0",'',"true");




$pdf->Cell(0,10,$mydate.'-'.$date2weeks,"0","1",'',"true");
$pdf->Cell(0,1,'',"0","1");//space



if($comission!=0||$ei_gst!=0){
$totalbal=0;
$totalap=0;
$pdf->Cell(195.9,8,'   Production ',"0","1",'',"true");

$pdf->SetFont('Arial','BU',9);

$pdf->Cell(60,10,'Client name',0,0,"C");
$pdf->Cell(30,10,'Annual Premium',0,0,"C");
$pdf->Cell(30,10,'Commission',0,0,"C");	
$pdf->Cell(30,10,'G.S.T',0,0,"C");	
$pdf->Cell(30,10,'Balance',0,1,"C");

$pdf->SetFont('Arial','',9);


foreach ($client_name as $x=>$y) {
if($ei_com_arr[$x]>0){




$ei_com_arr[$x]=$ei_com_arr[$x]*$com_pct;
$ei_gst_arr[$x]=$ei_gst_arr[$x]*$com_pct;

$totalap+=$annual_prem_arr[$x];
if($gst_reg=="no"){
	$ei_gst_arr[$x]=0;
}
$totalbal+=$ei_com_arr[$x]+$ei_gst_arr[$x];

$balancecom=$ei_com_arr[$x]+$ei_gst_arr[$x];


$pdf->Cell(60,5,$y,0,0,"C");
$pdf->Cell(30,5,'$'.convertNum($annual_prem_arr[$x]) ,0,0,"C");
$pdf->Cell(30,5,'$'.convertNum($ei_com_arr[$x]),0,0,"C");	
$pdf->Cell(30,5,'$'.convertNum($ei_gst_arr[$x]),0,0,"C");	
$pdf->Cell(30,5,'$'.convertNum($balancecom),0,1,"C");
}
}//end foreach


$pdf->SetFont('Arial','B',10);
$pdf->Cell(60,15,'Total',0,0,"C");
$pdf->Cell(30,15,'$'.convertNum($totalap),0,0,"C");
$pdf->Cell(30,15,'$'.convertNum($ei_com),0,0,"C");	
$pdf->Cell(30,15,'$'.convertNum($ei_gst),0,0,"C");	
$pdf->Cell(30,15,'$'.convertNum($totalbal),0,1,"C");

}




//override commission
if($rows_tax){
$hey=$rows_tax['adviser_id'];
$totalbal=0;
$totalap=0;

$totalap+=$annual_prem_arr[$x];
$ai=$rows_tax['adviser_id'];
//searchsql="SELECT * FROM clients_tbl WHERE id='$ai' AND startingdate='$mydateorig'";
$searchsql="SELECT A.* , B.manager_pct, B.gst_reg, B.name as adname FROM clients_tbl as A LEFT JOIN adviser_tbl as B ON A.adviser=B.id WHERE A.startingdate='$mydateorig' AND (A.com!=0 OR A.gst!=0) AND B.manager_id='$adviser_id'";
$search=mysqli_query($con,$searchsql) or die('Could not look up user information; ' . mysqli_error($con));
$num_rows = mysqli_num_rows($search);


$tot_over=0;
$tot_balo=0;
$tot_newgst=0;
$tot_ap=0;
if($num_rows>0):
$pdf->SetTextColor(0,0,0);
$pdf->SetFont('Arial','B',10);
$pdf->Cell(195.9,8,'   Overrides (Commission)',"0","1",'',"true");
$pdf->SetFont('Arial','BU',9);
//$pdf->Cell(20,10,'Week',0,0,"C");
$pdf->Cell(40,10,'Adviser',0,0,"C");
$pdf->Cell(40,10,'Client Name',0,0,"C");	
$pdf->Cell(40,10,'Annual Premium',0,0,"C");	
$pdf->Cell(20,10,'Overrides',0,0,"C");
$pdf->Cell(20,10,'GST',0,0,"C");
$pdf->Cell(20,10,'Balance',0,1,"C");
$pdf->SetFont('Arial','',9);

 WHILE($rows = mysqli_fetch_array($search)):
/*if($rows["gst_reg"]=="no"){
$rows["manager_pct"]=0;
}*/

$ln=5*((strlen($rows["adname"])/19));

$over=$rows["com"]*($rows["manager_pct"]/100);
$newgst=$rows["gst"]*($rows["manager_pct"]/100);
$balance=$over+$newgst;
$ap=$rows["annual_prem"];
$xpos=$pdf->GetX();
$ypos=$pdf->GetY();
$pdf->MultiCell(40,$ln,$rows['adname'],0,"C",false);
$pdf->SetXY($xpos+40,$ypos);
$pdf->Cell(40,$ln*2,$rows["name"],0,0,"C");	
$pdf->Cell(40,$ln*2,'$'.convertNum($ap),0,0,"C");
$pdf->Cell(20,$ln*2,'$'.convertNum($over),0,0,"C");	
$pdf->Cell(20,$ln*2,'$'.convertNum($newgst),0,0,"C");	
$pdf->Cell(20,$ln*2,'$'.convertNum($balance),0,1,"C");
$tot_over+=$over;
$tot_newgst+=$newgst;
$tot_balo+=$balance;
$tot_ap+=$ap;
endwhile;
$pdf->SetFont('Arial','B',10);
//$pdf->Cell(20,10,'Total',0,0,"C");
$pdf->Cell(40,10,'Total',0,0,"C");
$pdf->Cell(40,10,'',0,0,"C");	
$pdf->Cell(40,10,'$'.convertNum($tot_ap),0,0,"C");	
$pdf->Cell(20,10,'$'.convertNum($tot_over),0,0,"C");
$pdf->Cell(20,10,'$'.convertNum($tot_newgst),0,0,"C");
$pdf->Cell(20,10,'$'.convertNum($tot_balo),0,1,"C");
endif;
}
//override commission





$prod=$totalbal*($manager_pct/100);


$balanceren=$ei_rencom+$ei_rengst;

if($ei_rencom!=0||$ei_rengst!=0){
$totalbal=0;
$totalap=0;
	$pdf->SetTextColor(0,0,0);
$pdf->SetFont('Arial','B',10);
$pdf->Cell(195.9,8,'   Renewals ',"0","1",'',"true");
$pdf->SetFont('Arial','BU',9);

$pdf->Cell(60,10,'Client name',0,0,"C");
$pdf->Cell(30,10,'Annual Premium',0,0,"C");
$pdf->Cell(40,10,'Renewal Commission',0,0,"C");	
$pdf->Cell(30,10,'G.S.T',0,0,"C");	
$pdf->Cell(30,10,'Balance',0,1,"C");


$pdf->SetFont('Arial','',9);

foreach ($client_name as $x=>$y) {

$searchsql="SELECT name FROM clients_tbl WHERE name LIKE'$y'";
$search=mysqli_query($con,$searchsql);
$num_rows = mysqli_num_rows($search);





if($ei_rencom_arr[$x]>0){
if($gst_reg=="no"){
	$ei_rengst_arr[$x]=0;
}

$ei_rencom_arr[$x]=$ei_rencom_arr[$x]*$ren_pct;
$ei_rengst_arr[$x]=$ei_rengst_arr[$x]*$ren_pct;
$balanceren	=$ei_rencom_arr[$x]+$ei_rengst_arr[$x];
$totalbal+=$balanceren;
$totalap+=$annual_prem_arr[$x];
if($num_rows>0){	//if existing or not	
$pdf->Cell(60,5,$y,0,0,"C");
$pdf->Cell(30,5,'$'.convertNum($annual_prem_arr[$x]),0,0,"C");
$pdf->Cell(40,5,'$'.convertNum($ei_rencom_arr[$x]),0,0,"C");	
$pdf->Cell(30,5,'$'.convertNum($ei_rengst_arr[$x]),0,0,"C");	
$pdf->Cell(30,5,'$'.convertNum($balanceren),0,1,"C");
}
}
}
$pdf->SetFont('Arial','B',10);
$pdf->Cell(60,15,'Total',0,0,"C");
$pdf->Cell(30,15,'$'.convertNum($totalap),0,0,"C");
$pdf->Cell(40,15,'$'.convertNum($ei_rencom),0,0,"C");	
$pdf->Cell(30,15,'$'.convertNum($ei_rengst),0,0,"C");	
$pdf->Cell(30,15,'$'.convertNum($totalbal),0,1,"C");

}//end if


$totalbal=0;
$totalap=0;
if($ei_cancel_amt!=0||$ei_gstcan!=0){

$pdf->SetTextColor(0,0,0);
$pdf->SetFont('Arial','B',10);

$pdf->Cell(195.9,8,'   Cancellations ',"0","1",'',"true");
$pdf->SetFont('Arial','BU',9);

$pdf->Cell(60,10,'Client name',0,0,"C");
$pdf->Cell(30,10,'Annual Premium',0,0,"C");
$pdf->Cell(40,10,'Cancellations',0,0,"C");	
$pdf->Cell(30,10,'G.S.T',0,0,"C");	
$pdf->Cell(30,10,'Balance',0,1,"C");
$pdf->SetFont('Arial','',9);

foreach ($client_name as $x=>$y) {
$new=$ei_cancel_amt_arr[$x];
if($new>0){
$new=$new*$com_pct;
$ei_gstcan_arr[$x]=$ei_gstcan_arr[$x]*$com_pct;

if($gst_reg=="no"){
	$ei_gstcan_arr[$x]=0;
	
}

$balancecom=$ei_gstcan_arr[$x]+$new;
$totalap+=$annual_prem_arr[$x];
$totalbal+=$balancecom;
$pdf->Cell(60,5,$y,0,0,"C");
$pdf->Cell(30,5,'$'.convertNum($annual_prem_arr[$x]),0,0,"C");
$pdf->Cell(40,5,'$'.convertNum($new),0,0,"C");	
$pdf->Cell(30,5,'$'.convertNum($ei_gstcan_arr[$x]),0,0,"C");	
$pdf->Cell(30,5,'$'.convertNum($balancecom),0,1,"C");
}
}//end foreach
$pdf->SetFont('Arial','B',10);
$pdf->Cell(60,15,'Total',0,0,"C");
$pdf->Cell(30,15,'$'.convertNum($totalap),0,0,"C");
$pdf->Cell(40,15,'$'.convertNum($ei_cancel_amt),0,0,"C");	
$pdf->Cell(30,15,'$'.convertNum($ei_gstcan),0,0,"C");	
$pdf->Cell(30,15,'$'.convertNum($totalbal),0,1,"C");
}

//override cancellation
if($rows_tax){
$hey=$rows_tax['adviser_id'];
$totalbal=0;
$totalap=0;
//searchsql="SELECT * FROM clients_tbl WHERE id='$ai' AND startingdate='$mydateorig'";
$searchsql="SELECT A.* , B.manager_pct, B.gst_reg,B.name as adname FROM clients_tbl as A LEFT JOIN adviser_tbl as B ON A.adviser=B.id  WHERE startingdate='$mydateorig' AND (A.gstcan!=0 OR A.cancel_amt!=0) AND B.manager_id='$adviser_id'";
$search=mysqli_query($con,$searchsql) or die('Could not look up user information; ' . mysqli_error($con));
$num_rows = mysqli_num_rows($search);

$tot_over=0;
$tot_balo=0;
$tot_newgst=0;
$tot_ap=0;


if($num_rows>0):
$pdf->SetTextColor(0,0,0);
$pdf->SetFont('Arial','B',10);
$pdf->Cell(195.9,8,'   Overrides (Cancellations)',"0","1",'',"true");
$pdf->SetFont('Arial','BU',9);
//$pdf->Cell(20,10,'Week',0,0,"C");
$pdf->Cell(30,10,'Adviser',0,0,"C");
$pdf->Cell(40,10,'Client Name',0,0,"C");	
$pdf->Cell(40,10,'Annual Premium',0,0,"C");	
$pdf->Cell(20,10,'Overrides',0,0,"C");
$pdf->Cell(20,10,'GST',0,0,"C");
$pdf->Cell(20,10,'Balance',0,1,"C");
$pdf->SetFont('Arial','',9);
$totalap+=$annual_prem_arr[$x];
$ai=$rows_tax['adviser_id'];

 WHILE($rows = mysqli_fetch_array($search)):
/*if($rows["gst_reg"]=="no"){
$rows["manager_pct"]=0;
}
*/
$over=$rows["cancel_amt"]*($rows["manager_pct"]/100);
$newgst=$rows["gstcan"]*($rows["manager_pct"]/100);
$balance=$over+$newgst;
$ap=$rows["annual_prem"];
$pdf->Cell(30,10,$rows['adname'],0,0,"C");
$pdf->Cell(40,10,$rows["name"],0,0,"C");	
$pdf->Cell(40,10,'$'.convertNum($ap),0,0,"C");
$pdf->Cell(20,10,'$'.convertNum($over),0,0,"C");	
$pdf->Cell(20,10,'$'.convertNum($newgst),0,0,"C");	
$pdf->Cell(20,10,'$'.convertNum($balance),0,1,"C");
$tot_over+=$over;
$tot_newgst+=$newgst;
$tot_balo+=$balance;
$tot_ap+=$ap;
endwhile;
$pdf->SetFont('Arial','B',10);
//$pdf->Cell(20,10,'Total',0,0,"C");
$pdf->Cell(30,10,'Total',0,0,"C");
$pdf->Cell(40,10,'',0,0,"C");	
$pdf->Cell(40,10,'$'.convertNum($tot_ap),0,0,"C");	
$pdf->Cell(20,10,'$'.convertNum($tot_over),0,0,"C");
$pdf->Cell(20,10,'$'.convertNum($tot_newgst),0,0,"C");
$pdf->Cell(20,10,'$'.convertNum($tot_balo),0,1,"C");
endif;
}
//override cancellation

$canc=$totalbal*($manager_pct/100);

if($ei_rencan!=0||$ei_rencangst!=0){
	$totalbal=0;
$totalap=0;
$pdf->SetTextColor(0,0,0);
$pdf->SetFont('Arial','B',10);

$pdf->Cell(195.9,8,'   Renewals Cancellations',"0","1",'',"true");
$pdf->SetFont('Arial','BU',9);

$pdf->Cell(60,10,'Client name',0,0,"C");
$pdf->Cell(30,10,'Annual Premium',0,0,"C");
$pdf->Cell(40,10,'Renewal Cancellations',0,0,"C");	
$pdf->Cell(30,10,'G.S.T',0,0,"C");	
$pdf->Cell(30,10,'Balance',0,1,"C");

$pdf->SetFont('Arial','',9);

foreach ($client_name as $x=>$y) {

$totalap+=$annual_prem_arr[$x];


$searchsql="SELECT name FROM clients_tbl WHERE name LIKE'$y'";
$search=mysqli_query($con,$searchsql);
$num_rows = mysqli_num_rows($search);


if($ei_rencan_arr[$x]>0){
if($gst_reg=="no"){
	$ei_rencangst_arr[$x]=0;
}
$balanceren=$ei_rencan_arr[$x]+$ei_rencangst[$x];
$ei_rencangst_arr[$x]=$ei_rencangst_arr[$x]*$ren_pct;
$ei_rencan_arr[$x]=$ei_rencan_arr[$x]*$ren_pct;
$balanceren=$ei_rencan_arr[$x]+$ei_rencangst_arr[$x];
$totalbal+=$balanceren;

if($num_rows>0){	//if existing or not	
$pdf->Cell(60,5,$y,0,0,"C");
$pdf->Cell(30,5,'$'.convertNum($annual_prem_arr[$x]),0,0,"C");
$pdf->Cell(40,5,'$'.convertNum($ei_rencan_arr[$x]),0,0,"C");	
$pdf->Cell(30,5,'$'.convertNum($ei_rencangst_arr[$x]),0,0,"C");	
$pdf->Cell(30,5,'$'.convertNum($balanceren),0,1,"C");
}
}
}
$pdf->SetFont('Arial','B',10);
$pdf->Cell(60,15,'Total',0,0,"C");
$pdf->Cell(30,15,'$'.convertNum($totalap),0,0,"C");
$pdf->Cell(40,15,'$'.convertNum($ei_rencan),0,0,"C");	
$pdf->Cell(30,15,'$'.convertNum($ei_rencangst),0,0,"C");	
$pdf->Cell(30,15,'$'.convertNum($totalbal),0,1,"C");



}












$newDate = substr($datenow,6,4).substr($datenow,3,2).substr($datenow,0,2);


$mix="$mydateorig"."-$name-"."BCTI";
//$mix="$mydateorig"."-$name-"."BCTI".-date("ymd");
$path="files/".$mix.".pdf";
$pdf->Output($path,'F');
//insert pdf_tbl
$sql="INSERT INTO pdf_tbl (adviser_id,name,email,link,filename,entrydate,type) 
VALUES ('$adviser_id','$name','$email','$path','$mix','$newDate','bcti')"; 

mysqli_query($con,$sql);




$newDate = substr($datenow,6,4).substr($datenow,3,2).substr($datenow,0,2);
//db add
foreach ($client_name as $x=>$y){

/*if($gst_reg=="no"){
	$ei_rencangst_orig[$x]=0;
	$ei_gst_orig[$x]=0;
	$ei_gstcan_orig[$x]=0;
	$ei_rengst_orig[$x]=0;
}
*/
$searchsql="SELECT name FROM clients_tbl WHERE name LIKE'$y'";
$search=mysqli_query($con,$searchsql);
$num_rows = mysqli_num_rows($search);
if(isset($cancel_arr[$x])){
$status="Cancelled";
}
else{
$status="Existing";
}


//if($num_rows==0){
$sql="INSERT INTO clients_tbl (name,adviser,com,rencom,gst,rengst,gstcan,cancel_amt,rencan,rencangst,annual_prem,status,entrydate,startingdate) 
VALUES ('$y','$adviser_id','$ei_com_orig[$x]','$ei_rencom_orig[$x]','$ei_gst_orig[$x]','$ei_rengst_orig[$x]','$ei_gstcan_orig[$x]','$ei_cancel_amt_orig[$x]','$ei_rencan_orig[$x]','$ei_rencangst_orig[$x]','$annual_prem_orig[$x]','$status','$newDate','$mydateorig')"; 

mysqli_query($con,$sql);
//}
//else{
//}
}


//insert summary_tbl
$sql="INSERT INTO summary_tbl(adviser_id,name,openingbal,bonuses,sundries,agencyrelease,net,gst,withodingtax,annual_prem,payment_amount,closing_bal,entrydate,startingdate,leads_qty) 
VALUES ('$adviser_id','$name','$openbal','$bonuses','$sundries','$agencyrelease','$net_orig','$gst_total','$withodingtax_orig','$annual_prem_orig','$payment_amount_orig','$closing_bal_orig','$newDate','$mydateorig','$leads_qty')"; 
mysqli_query($con,$sql); 

//insert commission and cancellation


//$search=mysqli_query($con,$searchsql);

/*
if($manager_id>0){
$addsql="INSERT INTO com_and_canc(adviser_id,startingdate,manager_id,prod,canc) 
VALUES ('$adviser_id','$mydateorig','$manager_id','$prod','$canc')"; 
mysqli_query($con,$addsql); 
}*/


header("Content-type: application/pdf");
header("Content-Disposition: inline; filename=".$path);
@readfile($path);





//db add end
}
}
?>