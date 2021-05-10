<?php
session_start();
require "database.php";
header('Content-Type: application/json');
if(!isset($_GET["client_id"])){
	header("Location: client_profiles.php");
}




$client_id=$_GET["client_id"];

if($_SESSION["myusertype"]=="Admin" || $_SESSION["myusertype"]=="User"){
	$query="Select * from clients_tbl WHERE id = $client_id";	
	if ($result = mysqli_query($con, $query)) {
	   	$clientdata = mysqli_fetch_assoc($result);
	   	echo json_encode($clientdata);
	}else{
	    echo "<br>Error: " . mysqli_error($con);
	}
}
?>
