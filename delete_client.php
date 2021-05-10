<?php
session_start();
echo "<script>alert('Record deleted successfully')</script>";
require "database.php";
if(!isset($_GET["del_id"])){

}



$con=mysqli_connect($host,$username,$password,$db)or die("could not connect to sql");
 if (!$con) {
        echo "<div>";
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
	    echo "</div>";	
}

$del_id=$_GET["del_id"];
if($_SESSION["myusertype"]=="Admin")
{
	$query="DELETE FROM clients_tbl WHERE id=$del_id";
	if($del_id=="all"){
	$query="TRUNCATE clients_tbl";
	}

	if (mysqli_query($con, $query)) {

	    header("Location: clients");

	}else{
	    echo "Error deleting record: " . mysqli_error($conn);

	}
}
elseif($_SESSION["myusertype"]=="User"){
	$query="INSERT INTO bin_entries (table_name,table_id,binned_by) VALUES('clients_tbl','" . $del_id . "','" . $_SESSION["myuserid"] . "')";
	mysqli_query($con, $query);
	$query="UPDATE clients_tbl SET binned=1 WHERE id=$del_id";
	if (mysqli_query($con, $query)) {
	    header("Location: clients");
	}else{
	    echo "Error deleting record: " . mysqli_error($conn);
	}
}
?>
