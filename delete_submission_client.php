<?php
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
$query="DELETE FROM submission_clients WHERE id=$del_id";
if($del_id=="all"){
$query="TRUNCATE submission_clients";
}

if (mysqli_query($con, $query)) {

    header("Location: submission_client_profiles.php");

}else{
    echo "Error deleting record: " . mysqli_error($conn);

}


?>
