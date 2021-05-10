<?php
date_default_timezone_set('Pacific/Auckland');
require("database.php");
$data=json_decode($_POST["mydata"], true);

$number=$data["number"];
$adviser_id=$data["adviser_id"];
$date_from= $data["from"];
$date_to = $data["to"];
$data=$data["data"];
$entrydate=date("Ymd");
$user = $_SESSION['myuserid'];

//Insert into invoice table
$sql="INSERT INTO summary (number,adviser_id,data,date_from,date_to,created_at,created_by) 
VALUES ('$number',$adviser_id,'$data',$date_from,$date_to,$entrydate,'$user')"; 
mysqli_query($con,$sql);

echo $sql;
?>