<?php
session_start();
echo "<script>alert('Record deleted successfully')</script>";
require "database.php";
if(!isset($_GET["restore_id"])){
 	header("Location: client_profiles.php");
}



$con=mysqli_connect($host,$username,$password,$db)or die("could not connect to sql");
 if (!$con) {
        echo "<div>";
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
	    echo "</div>";	
}

$restore_id=$_GET["restore_id"];

if($_SESSION["myusertype"]=="Admin"){
	$query="UPDATE clients_tbl SET binned=0 WHERE id=$restore_id";
	if (mysqli_query($con, $query)) {
	    header("Location: client_profiles.php");
	}else{
	    echo "Error deleting record: " . mysqli_error($conn);
	}
}
?>
