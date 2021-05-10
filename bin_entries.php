 <?php
session_start();
if(!isset($_SESSION["myusername"])){
session_destroy();
header("Refresh:0; url=index.php");
}
else{
?>
 <html>
<head>
<!--nav bar-->
<?php include "partials/nav_bar.html";?>
<!--nav bar end-->
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">
<link rel="stylesheet" href="styles.css">
<link id="favicon" rel="icon" href="Logo_ImageOnly.png" type="image/png" sizes="16x16">
<title>INDET</title>
<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css" integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">
<script>
$(function(){
$('.checkme').on('click',function(){
var id=$(this).attr('data-id');
var com=$(this).attr('data-com');
var me=$(this);

})




$('#me').dataTable();


});

</script>
<!--header-->
<div align="center">


<!--header end-->

<!--nav bar-->


<!--nav bar end-->


<!--label-->

  <div class="jumbotron">
    <h2 class="slide">Client Summary</h2>
</div>
<!--label end-->

<!--modal-->

<div id="myModal" class="modal">


  <div class="modal-content" >
    <span class="close">&times;</span>
    <p>Please confirm to delete all</p>

   	<input type="password" id="confirmpassword" class="addadviser"  placeholder="Password" autocomplete="new-password"/><br style="height:50px;">
   	<br style=" display: block;margin: 10px 0;">
    <input type="button" id="confirmbutton" value="Delete All" style="width: 20%;" />
  </div>

</div>




<?php


 require "database.php";


function convertNum($x){

return number_format($x, 2, '.', ',');
}


  $con=mysqli_connect($host,$username,$password,$db)or die("could not connect to sql");
 if (!$con) {
        echo "<div>";
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
	    echo "</div>";	
}

$query = "SELECT *, u.username as user from bin_entries b INNER JOIN users u on u.id=b.binned_by order by timestamp desc;

";
$displayquery=mysqli_query($con,$query) or die('Could not look up user information; ' . mysqli_error($con));
?>

<div class="margined table-responsive">
<table id='me' data-toggle="table" class="table table-striped " cellpadding="5px" cellspacing="5px" >

<thead>
	<td>Timestamp</td>
	<td>Table</td>
	<td>Binned ID</td>
	<td>Deleted By</td>
</thead>
<tbody>
<?php

 WHILE($rows = mysqli_fetch_array($displayquery)):
extract($rows);
$timestamp = date('dS F, Y h:i',strtotime($timestamp));
/*$entrydate=$rows["entrydate"];
$startingdate=$rows["startingdate"];
$entrydate=substr($entrydate,0,4)."/".substr($entrydate,4,2)."/".substr($entrydate,6,2);

$startingdate=substr($startingdate,0,4)."/".substr($startingdate,4,2)."/".substr($startingdate,6,2);


$convertdate=substr($rows["entrydate"],6,2)."/".substr($rows["entrydate"],4,2)."/".substr($rows["entrydate"],0,4);

$convertstartingdate=substr($rows["startingdate"],6,2)."/".substr($rows["startingdate"],4,2)."/".substr($rows["startingdate"],0,4);
*/
echo "
<tr cellpadding='5px' cellspacing='5px'>

	<td>$timestamp</td>
	<td>$table_name</td>
	<td>$table_id</td> 
	<td>$user</td>
	";

/*	echo "
<tr cellpadding='5px' cellspacing='5px'>

	<td>$name</td>
	<td>$appt_date</td>
	<td>$appt_time</td> 
	<td>$address</td>
	<td>$y</td>
	<td>$x</td>
	<td>$assigned_date</td>
		<td>$notes</td>
	";
*/
    /*<td data-order=".$entrydate.">".$convertdate."</td>
    <td data-order=".$startingdate.">".$convertstartingdate."</td>
	*/

?>



	<!--
<td><a class="a" href="edit_adviser.php<?php echo "?edit_id=$id"?>"><img src="edit.png"></a>
	 </td>
-->
 <?php 
 echo "</tr>";	

 endwhile;




 ?>
</tbody>
</table>
</div>
</div>


</html>

<?php

}
?>