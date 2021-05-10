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

/*
$updateTL="UPDATE leadgen_tbl SET manager_id='0',manager_pct='0' WHERE manager_id=$del_id";
if (mysqli_query($con, $updateTL)) {

}

$updateTL_JR="UPDATE adviser_tbl SET manager_id_jr='0',manager_pct_jr='0' WHERE manager_id_jr=$del_id";
if (mysqli_query($con, $updateTL_JR)) {

}

$queryclient="DELETE FROM clients_tbl WHERE adviser=$del_id";
if (mysqli_query($con, $queryclient)) {

}

$querysummary="DELETE FROM summary_tbl WHERE adviser_id=$del_id";
if (mysqli_query($con, $querysummary)) {

}*/

$querypdf="DELETE FROM leadgen_tbl WHERE id=$del_id";
if (mysqli_query($con, $querypdf)) {

}






$query="DELETE FROM leadgen_tbl WHERE id=$del_id";
if($del_id=="all"){
$query="TRUNCATE leadgen_tbl";




	//mysqli_query($con, 'TRUNCATE adviser_tbl');
//	mysqli_query($con, 'TRUNCATE pdf_tbl');
	//mysqli_query($con, 'TRUNCATE client_tbl');
	mysqli_query($con, 'TRUNCATE leadgen');


}

if (mysqli_query($con, $query)) {



    header("Location: leadgen_profiles.php");






}else{
    echo "Error deleting record: " . mysqli_error($conn);

}


?>
