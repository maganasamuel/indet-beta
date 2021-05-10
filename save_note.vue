<?php require "database.php";

  $con=mysqli_connect($host,$username,$password,$db)or die("could not connect to sql");
 if (!$con) {
        echo "<div>";
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
	    echo "</div>";	
}
$id=$_POST['id'];
$desc=$_POST['desc'];

$sql="UPDATE pdf_tbl SET description='$desc' WHERE id='$id'"; 

mysqli_query($con,$sql);



echo 'Succesfully saved';
?>