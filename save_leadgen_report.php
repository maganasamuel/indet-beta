<?php
require("database.php");
$data=json_decode($_POST["mydata"], true);

$number=$data["number"];
$leadgen_id=$data["leadgen_id"];
$totalclients=$data["totalclients"];
$totalissuedclients=$data["totalissuedclients"];
$totalissuedpremiums=$data["totalissuedpremiums"];
$date_from= $data["from"];
$date_to = $data["to"];
$entrydate=$data["entrydate"];
$type=$data["type"];
$schedule_type=$data["schedule_type"];
$required_leads=json_encode($data["required_leads"]);
$required_leads_Type=$data["required_leads_type"];
$user = $_SESSION['myuserid'];


//Insert into invoice table
$sql="INSERT INTO lead_gen_report (reference_number,type,schedule_type,leads_required,leads_required_type,lead_gen_id,leads_generated,leads_issued,api_generated,date_from,date_to,created_at,created_by) 
VALUES ('$number','$type','$schedule_type','$required_leads','$required_leads_Type',$leadgen_id,$totalclients,$totalissuedclients,$totalissuedpremiums,$date_from,$date_to,$entrydate,'$user')"; 

$result = mysqli_query($con,$sql);
print $sql;
?>