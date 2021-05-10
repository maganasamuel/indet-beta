<?php
require("database.php");
date_default_timezone_set('Pacific/Auckland');
$data=json_decode($_POST["mydata"], true);
$adviser_id=$data["adviser_id"];
$link=$data["link"];
$report_data = json_encode($data["report_data"]);
$notes= $data["notes"];
$date_from= $data["from"];
$date_to = $data["to"];
$pay_date = $data["pay_date"];
$now = date("Ymd");
$created_by = $_SESSION["myuserid"];

//Insert into record
$sql="INSERT INTO deal_tracker_reports (adviser_id,report_data,pay_date,date_from,date_to,date_created,created_by,notes) 
VALUES ('$adviser_id',$report_data,$pay_date,$date_from,$date_to,$now,'$created_by','$notes')"; 

if (mysqli_query($con, $sql)) {
	echo mysqli_insert_id($con);
}else{
	echo $sql;
    //echo "Error deleting record: " . mysqli_error($con);

}



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