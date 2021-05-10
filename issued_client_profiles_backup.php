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
<script>
$(function(){

$('.checkme').on('click',function(){
var id=$(this).attr('data-id');
var com=$(this).attr('data-com');
var me=$(this);
});

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
    <h2 class="slide">Issued Client Summary</h2>
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

$query = "SELECT c.lead_by, i.id,c.name,a.name as x,l.name as y,i.appt_date,i.appt_time,i.address,i.leadgen,i.assigned_to,i.assigned_date,i.type_of_lead,i.issued,i.date_issued,i.notes FROM issued_clients_tbl i LEFT JOIN adviser_tbl a ON i.assigned_to = a.id LEFT JOIN leadgen_tbl l ON i.leadgen = l.id LEFT JOIN clients_tbl c ON i.name = c.id order by i.date_issued desc;

";
$displayquery=mysqli_query($con,$query) or die('Could not look up user information; ' . mysqli_error($con));
?>

<div class="margined table-responsive">
<table id='me' data-toggle="table"  class="table table-striped " cellpadding="5px" cellspacing="5px" width='95%'>

<thead>

	<td>Client Name</td>
	<!--td>Appt Date</td>
	<td>Phone Number</td>
	<td>Address</td>
	<td>Notes</td>
	<td>Assigned to</td>
	<td>Assigned Date</td>
	<td>Type of Lead</td-->
	<td>Issued Premium</td>
	<td>Date Issued</td>
	<td>Lead By</td>
	<td>Lead Generator</td>
	<td></td>
	<td><a id="deleteall" class="a" href="delete_issued_client.php?del_id=all"><img src="delete.png"></a></td>




</thead>
<tbody>
<?php

 WHILE($rows = mysqli_fetch_array($displayquery)):
$id=$rows["id"];
$name=$rows["name"];
$x=$rows["x"]; //advisername
$y=$rows["y"]; //leadgenname
$lead_by=$rows["lead_by"];
$appt_date=$rows["appt_date"];
$appt_time=$rows["appt_time"];
$address=$rows["address"];
$leadgen=$rows["leadgen"];
$assigned_to=$rows["assigned_to"];
$assigned_date=$rows["assigned_date"];
$type_of_lead=$rows["type_of_lead"];
$issued=$rows["issued"];
$date_issued=$rows["date_issued"];
$notes=$rows["notes"];

$date_issued=date('d/m/Y',strtotime($date_issued));

/*$entrydate=$rows["entrydate"];
$startingdate=$rows["startingdate"];
$entrydate=substr($entrydate,0,4)."/".substr($entrydate,4,2)."/".substr($entrydate,6,2);

$startingdate=substr($startingdate,0,4)."/".substr($startingdate,4,2)."/".substr($startingdate,6,2);
$convertdate=substr($rows["entrydate"],6,2)."/".substr($rows["entrydate"],4,2)."/".substr($rows["entrydate"],0,4);

$convertstartingdate=substr($rows["startingdate"],6,2)."/".substr($rows["startingdate"],4,2)."/".substr($rows["startingdate"],0,4);
*/
$lg = $y;
if($lead_by=="Self-Generated"){
	$lg = $x;
}

echo "
<tr cellpadding='5px' cellspacing='5px'>

<td>$name</td>
		<td>$".number_format((float)$issued,2)."</td>
<td>$date_issued</td>

		<td>$lead_by</td>
		<td>$y</td>
	";
    /*<td data-order=".$entrydate.">".$convertdate."</td>
    <td data-order=".$startingdate.">".$convertstartingdate."</td>
	*/

?>



	<td><a class="a_single" href="delete_issued_client.php<?php echo "?del_id=$id"?>" ><img src="delete.png" /></a></td>
<!--
<td><a href="editclient.php<?php echo "?edit_id=$id"?>"><img src="edit.png"</a>
	 </td>
-->
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