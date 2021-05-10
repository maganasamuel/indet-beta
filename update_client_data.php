<?php
session_start();
require "database.php";
header('Content-Type: application/json');
if(!isset($_POST["client_id"])){
	header("Location: submission_client_profiles.php");
}




extract($_POST);

if($_SESSION["myusertype"]=="Admin"){
	$sql="Select * from leadgen_tbl where id=$leadgen"; 
    $result = mysqli_query($con,$sql);
    $row = mysqli_fetch_assoc($result);
    $lead_by = $row['type'];
    $appt_date = DateTimeToNZEntry($appt_date);
    $date_generated = DateTimeToNZEntry($date_generated);

    $concats = "";
    if(isset($name))
    	$concats .= addToQuery("name='$name'", $concats);
    if(isset($address))
    	$concats .= addToQuery("address='$address'", $concats);
    if(isset($appt_date))
    	$concats .= addToQuery("appt_date='$appt_date'", $concats);
    if(isset($date_generated))
    	$concats .= addToQuery("date_submitted='$date_generated'", $concats);
	if(isset($city))
    	$concats .= addToQuery("city='$city'", $concats);
    if(isset($zipcode))
    	$concats .= addToQuery("zipcode='$zipcode'", $concats);
    if(isset($phone))
    	$concats .= addToQuery("appt_time='$phone'", $concats);
    if(isset($leadgen))
    	$concats .= addToQuery("leadgen='$leadgen'", $concats);
    if(isset($lead_by))
    	$concats .= addToQuery("lead_by='$lead_by'", $concats);
    if(isset($adviser))
    	$concats .= addToQuery("assigned_to='$adviser'", $concats);

    $query = "UPDATE clients_tbl set $concats WHERE id=$client_id";

	if ($result = mysqli_query($con, $query)) {
	   	echo json_encode($result);
	}else{
	    echo "<br>Error: " . mysqli_error($con);
	}
}

function addToQuery($string_to_add, $query_concat){
	if(!empty($query_concat))
		$string_to_add = ", " . $string_to_add;
	return $string_to_add;
}

function DateTimeToNZEntry($date_submitted){
  return substr($date_submitted,6,4).substr($date_submitted,3,2).substr($date_submitted,0,2);
}

function NZEntryToDateTime($NZEntry){
    return substr($NZEntry,6,2) . "/" . substr($NZEntry,4,2) . "/" . substr($NZEntry, 0, 4);
}
?>
