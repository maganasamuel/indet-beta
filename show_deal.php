 <?php
session_start();

 require "database.php";

if(!isset($_SESSION["myusername"])){
session_destroy();
header("Refresh:0; url=index.php");
}
else{

if(!isset($_GET["submission_id"])){
	header("Refresh:0; url=submission_client_profiles.php");
}
else{
	$submission_id=$_GET["submission_id"];
	$query = "SELECT s.id as id,s.deals as deals, s.timestamp, c.id as client_id, c.address, c.appt_time,c.appt_date, c.date_submitted, c.city, c.zipcode, c.name as client_name, l.name as leadgen_name, a.name as adviser_name FROM submission_clients s LEFT JOIN clients_tbl c ON s.client_id = c.id LEFT JOIN leadgen_tbl l ON c.leadgen = l.id LEFT JOIN adviser_tbl a ON c.assigned_to = a.id WHERE s.id = $submission_id order by s.timestamp desc;";
	$result=mysqli_query($con,$query) or die('Could not look up user information; ' . mysqli_error($con));
	$data = mysqli_fetch_assoc($result);
	extract($data);
}
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
    <h2 class="slide">
    	Client Profiles
	</h2>
</div>
<!--label end-->

<!--modal-->
<?php echo'
	<div class="row">
		<div class="col-sm-3">
	    	<h4> Client Name: ' . $client_name . ' </h4>
	    </div>
		<div class="col-sm-3">
	    	<h4> Phone: ' . $appt_time . ' </h4>
	    </div>
	    <div class="col-sm-3">
	    	<h4> Lead Generator: ' . $leadgen_name . ' </h4>
	    </div>
	    <div class="col-sm-3">
	    	<h4> Adviser: ' . $adviser_name . ' </h4>
	    </div>
	</div>
	<div class="row">
		<div class="col-sm-3"></div>
		<div class="col-sm-3"><h4>Date Generated:' . NZEntryToDateTime($date_submitted) . '</h4></div>
		<div class="col-sm-3"><h4>Appointment Date:' . NZEntryToDateTime($appt_date) . '</h4></div>
	</div>
	<div class="row">
		<div class="col-sm-2"></div>
		<div class="col-sm-3">
	    	<h4> Address: ' . $address . ' </h4>
	    </div>
	    <div class="col-sm-3">
	    	<h4> City: ' . $city . ' </h4>
	    </div>
	    <div class="col-sm-3">
	    	<h4> Zip Code: ' . $zipcode . ' </h4>
	    </div>
	</div>
	<hr>
    ';
    	?>
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



function convertNum($x){

return number_format($x, 2, '.', ',');
}




?>

<div class="margined table-responsive">
<table id='me' data-toggle="table"  class="table table-striped " cellpadding="5px" cellspacing="5px" width='95%'>

<thead>

	<td>Client Name, Life Insured</td>
	<td>Date of Submission</td>
	<td>Insurer</td>
	<td>Policy Number</td>
	<td>Original API</td>
	<td>Status</td>



</thead>
<tbody>
<?php

$timestamp=date('d/m/Y',strtotime($timestamp));

$deals = json_decode($deals);
$deals_count = 0;
$deals_count = count($deals);

$total_api = 0;

$unique_client_names = [];
$unique_policy_numbers = [];
$unique_insurers = [];
$unique_client_names[] = $client_name;

foreach($deals as $deal){
	$submission_date = $deal->submission_date;
	$submission_date = substr($submission_date,6,2)."/".substr($submission_date,4,2)."/".substr($submission_date,0,4);
	if($deal->status=="Pending")
		$total_api += $deal->original_api;

echo "
<tr cellpadding='5px' cellspacing='5px'>
	<td>$client_name";
	if(!empty($deal->life_insured))
	echo ", $deal->life_insured";

	echo "</td>
	<td>$submission_date</td>
	<td>";

	//Show Company Name if others
	if($deal->company!="Others")
		echo "$deal->company";
	else
		echo "$deal->specific_company";
	
	echo "</td>

	<td>$deal->policy_number</td>
	<td>$" . number_format($deal->original_api,2)  . "</td>
	<td>$deal->status</td>
";

}

/*$entrydate=$rows["entrydate"];
$startingdate=$rows["startingdate"];
$entrydate=substr($entrydate,0,4)."/".substr($entrydate,4,2)."/".substr($entrydate,6,2);

$startingdate=substr($startingdate,0,4)."/".substr($startingdate,4,2)."/".substr($startingdate,6,2);
$convertdate=substr($rows["entrydate"],6,2)."/".substr($rows["entrydate"],4,2)."/".substr($rows["entrydate"],0,4);

$convertstartingdate=substr($rows["startingdate"],6,2)."/".substr($rows["startingdate"],4,2)."/".substr($rows["startingdate"],0,4);
*/
echo "<h4>Total API: $" . number_format($total_api,2)  . "</h4>";
?>
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





 ?>
</tbody>
</table>
<br>
<div class="row">
	<div class="col text-right">
		<button class="btn btn-primary" onclick="window.location = 'add_issued_client?client_id=<?php echo $client_id ?>'">Issue Policy</button>
	</div>
</div>
<br>
</div>
</div>


</html>

<?php



}
function debuggingLog($header="Logged Data",$variable){
  //SET TO TRUE WHEN DEBUGGING SET TO FALSE WHEN NOT
  $isDebuggerActive= false;

  	if(!$isDebuggerActive)
    return;

	  $op = "<br>";
	  $op .=  $header;
	  echo $op . "<hr>" . "<pre>";
	  var_dump($variable);
	  echo "</pre>" . "<hr>";
	}



function NZEntryToDateTime($NZEntry){
    return substr($NZEntry,6,2) . "/" . substr($NZEntry,4,2) . "/" . substr($NZEntry, 0, 4);
}

function DateTimeToNZEntry($date_submitted){
  return substr($date_submitted,6,4).substr($date_submitted,3,2).substr($date_submitted,0,2);
}
?>