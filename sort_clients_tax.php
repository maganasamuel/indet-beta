 <?php
session_start();
if(!isset($_SESSION["myusername"])){
session_destroy();
header("Refresh:0; url=index.php");
}

else{
?>


<?php require "database.php";

$con=mysqli_connect($host,$username,$password,$db)or die("could not connect to sql");
if (!$con) {
        echo "<div>";
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
	    echo "</div>";	
}

$query="SELECT C.id as id,C.adviser as adv,C.name,A.manager_pct as mng,A.manager_pct_jr as mng_jt from clients_tbl C INNER JOIN adviser_tbl as A where A.id=C.adviser order by C.startingdate DESC ";
$displayquery=mysqli_query($con,$query) or die('Could not look up user information; ' . mysqli_error($con));
 
WHILE($rows = mysqli_fetch_array($displayquery)):
$id=$rows['id'];
$adv=$rows['adv'];
$name=$rows['name'];

$manager_pct=$rows['mng'];
$manager_pct_jr=$rows['mng_jt'];
echo $id."-".$adv."-".$name."-".$manager_pct."-".$manager_pct_jr."<br>";

$query="UPDATE clients_tbl SET tl_pct='$manager_pct',tl_jr_pct='$manager_pct_jr' WHERE id='$id'";
if(mysqli_query($con,$query)){
echo "success";
}
else{

}


endwhile;

?>






<?php

}
?>