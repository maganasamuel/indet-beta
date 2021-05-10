<?php
session_start();
require "database.php";
header('Content-Type: application/json');
if(!isset($_GET["submission_id"]) && !isset($_GET["client_id"])){
	header("Location: submission_client_profiles.php");
}




extract($_GET);

if($_SESSION["myusertype"]=="Admin" || $_SESSION["myusertype"]=="User"){
	$query = "Select *, c.name as name, c.address as address, a.address as adviser_address, a.name as adviser_name, l.name as leadgen_name from submission_clients s LEFT JOIN clients_tbl c ON s.client_id = c.id LEFT JOIN adviser_tbl a ON a.id = c.assigned_to LEFT JOIN leadgen_tbl l ON l.id = c.leadgen ";

	if(isset($submission_id))
		$query.=" WHERE s.id = $submission_id";	
	elseif(isset($client_id))
		$query.=" WHERE s.client_id = $client_id";	

	if ($result = mysqli_query($con, $query)) {
	   	$data = mysqli_fetch_assoc($result);
	   	$data['deals'] = json_decode($data['deals']);
	   	echo json_encode($data);
	}else{
	    echo "<br>Error: " . mysqli_error($con);
	}
}
?>
