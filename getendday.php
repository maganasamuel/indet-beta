<?php
if(isset($_GET["month"])||isset($_GET["year"])){
$month=$_GET["month"];
$date=$_GET["date"];
$year=$_GET["year"];
$adviserid=$_GET["adviserid"];


if(isset($month)){
$querydate=$year."-01-".$month;	
$lastday = date("M-d-Y",mktime(0, 0, 0, $month, 1, $year));
if($month<10){
$condate=$year."0".$month.$date;
}
else{
	$condate=$year.$month.$date;
}
require "database.php";
 $con=mysqli_connect($host,$username,$password,$db)or die("could not connect to sql");
 if (!$con) {
        echo "<div>";
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
	    echo "</div>";	
}
$query = "SELECT id FROM summary_tbl where startingdate='$condate' AND adviser_id='$adviserid'";
$searchsum=mysqli_query($con,$query) or die('Could not look up user information; ' . mysqli_error($con));
$find = mysqli_num_rows($searchsum);
if($find>0){	//if existing or not	
echo "wrong";
}
else{
echo "15-".date("t", strtotime($lastday));
}
}
}
?>