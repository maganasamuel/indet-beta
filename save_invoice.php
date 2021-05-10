<?php
require("database.php");
date_default_timezone_set('Pacific/Auckland');

$data=json_decode($_POST["mydata"], true);
$adviser_id=$data["adviser_id"];
$name=$data["name"];
$email=$data["email"];
$link=$data["link"];
$filename=$data["filename"];
$entrydate=$data["entrydate"];
$type=$data["type"];
$number=$data["number"];
$due_date=$data["due_date"];
$description=$data["description"];
$leads=$data["leads"];
$issued=$data["issued"];
$date_from= $data["from"];
$date_to = $data["to"];
$amount = $data["amount"];
$user = $_SESSION['myuserid'];
$leads_charged=$data["leads_charged"];
$leads_issued=$data["leads_issued"];
$gst=$data["gst"];
$others=$data["others"];

//Insert into pdf table
/*
$sql="INSERT INTO pdf_tbl (adviser_id,name,email,link,filename,entrydate,type) 
VALUES ('$adviser_id','$name','$email','$link','$filename','$entrydate','$type')"; 
mysqli_query($con,$sql);
*/
$sql="SELECT * FROM adviser_tbl where id=$adviser_id";
$result = mysqli_query($con,$sql);
$adviser = mysqli_fetch_assoc($result);

$rate_per_lead = $adviser["leads"];
$rate_per_issued = $adviser["bonus"];

$total_leads = $data["payable_leads"];
$total_issued = $data["payable_issued_leads"];
$gst_rate = .15;
$leads_amount = $rate_per_lead * $total_leads;
$leads_amount += $leads_amount * $gst_rate; 
$issued_leads_amount = $rate_per_issued * $total_issued;
$issued_leads_amount += $issued_leads_amount * $gst_rate;

$now = date("Ymd");

//INSERT LEADS
if($total_leads>0){
	$sql="INSERT INTO transactions (adviser_id,status,date,number_of_leads,amount) VALUES ('$adviser_id','" . $number . "-Billed Assigned Leads','$now','$total_leads','$leads_amount')"; 
	mysqli_query($con,$sql);
}

//INSERT ISSUED
if($total_issued>0){
	$sql="INSERT INTO transactions (adviser_id,status,date,number_of_leads,amount) VALUES ('$adviser_id','" . $number . "-Billed Issued Leads','$now','$total_issued','$issued_leads_amount')"; 
	mysqli_query($con,$sql);
}
//Insert into invoice table
$sql="INSERT INTO invoices (adviser_id,number,description,due_date,date_created,leads,issued,leads_charged,leads_issued,gst,others,amount,date_from,date_to,created_by) 
VALUES ('$adviser_id','$number','$description','$due_date','$entrydate','$leads','$issued',$leads_charged,$leads_issued,$gst,$others,$amount,$date_from,$date_to,'$user')"; 
mysqli_query($con,$sql);

echo $sql;



/*
INSERT INTO invoices (
	adviser_id,		//ADVISER REF
	number,			//INVOICE REF
	description,	//INVOICE DESCRIPTIONS
	due_date,		//DUE DATE
	date_created,	//DATE CREATED
	leads,			//CLIENT LEADS 
	issued,			//CLIENT LEADS ISSUED
	leads_charged,	//LEADS CHARGED
	leads_issued,	//LEADS ISSUED
	gst,			//GST
	others,			//OTHERS
	issued,			//ISSUED
	amount,			//AMOUNT
	date_from,		//DATE FROM
	date_to,		//DATE TO
	created_by		//CREATED BY ADMIN
) 
VALUES (
	'15',												//ADVISER	
	'EIL001',											//INVOICE NUMBER
	'["charged","issued","Others"]',					//INVOICE DESC
	'30/01/2019',										//DUE DATE
	'20190123',											//DATE CREATED
	'["974","975","983","985","986"]',					//CLIENT LEADS
	'[]',												//CLIENT LEADS ISSUED
	100,												//LEADS CHARGED
	0,													//LEADS ISSUED
	15,													//GST
	0,													//OTHERS
	115,												//AMOUNT
	20190101,											//DATE FROM
	20190115,											//DATE TO
	'3'													//CREATED BY
)
*/

?>