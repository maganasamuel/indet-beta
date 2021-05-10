<?php
if(isset($_GET["adviser"])){
require("database.php");
$adviser=$_GET["adviser"];
$con=mysqli_connect($host,$username,$password,$db)or die("could not connect to sql");
if(!$con) {
        echo "<div>";
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
	    echo "</div>";	
}
$query = "SELECT closing_bal FROM summary_tbl WHERE name='$adviser' ORDER BY entrydate DESC, startingdate DESC";

$data=array();
$data['closing_bal']='';
$data['prevyear']='';

if(isset($_GET["date"])&&isset($_GET["month"])&&isset($_GET["year"])){

$month=$_GET["month"];
$year=$_GET["year"];
$date=$_GET["date"];

if($month<10){
$condate=$year."0".$month.$date;
$prev=($year-1)."0".$month.$date;
}
else{
	$condate=$year.$month.$date;
	$prev=($year-1).$month.$date;
}
$query = "SELECT closing_bal FROM summary_tbl WHERE adviser_id='$adviser' AND startingdate<='$condate' ORDER BY startingdate DESC";

//$query = "SELECT closing_bal FROM summary_tbl WHERE name='$adviser' ORDER BY entrydate DESC, startingdate DESC";

}
$displayquery=mysqli_query($con,$query) or die('Could not look up user information; ' . mysqli_error($con));

$rows = mysqli_fetch_array($displayquery);
$data['closing_bal']=$rows["closing_bal"];
$data['prevyear']=$prev;


$query = "SELECT closing_bal FROM summary_tbl WHERE adviser_id='$adviser' AND startingdate<='$condate' ORDER BY startingdate DESC";


echo json_encode($data);



}



?>