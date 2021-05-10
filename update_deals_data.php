<?php
session_start();
require "database.php";
header('Content-Type: application/json');
if(!isset($_POST["client_id"])){
	header("Location: submission_client_profiles.php");
}




extract($_POST);

if($_SESSION["myusertype"]=="Admin"){
  $deals_op = json_encode($deals);
  $sql = "UPDATE submission_clients SET deals='$deals_op' WHERE client_id=$client_id"; 
  if (mysqli_query($con, $query)) {
	   	echo "Success";
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
