       <option style="color:#d9534f;font-weight: bold;" value="New Client" selected>New Client</option>
 <option value="" disabled>Select existing client</option>
<?php require "database.php";
 $con=mysqli_connect($host,$username,$password,$db)or die("could not connect to sql");
 if (!$con) {
        echo "<div>";
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
	    echo "</div>";	
}

?>	

<?php
$id=$_POST['adviser'];
$query = "SELECT DISTINCT name FROM clients_tbl WHERE adviser='$id'";

$displayquery=mysqli_query($con,$query) or die('Could not look up user information; ' . mysqli_error($con));

WHILE($rows = mysqli_fetch_array($displayquery)){
$name=$rows["name"];

echo "
	<option>$name</option>";
}
?>