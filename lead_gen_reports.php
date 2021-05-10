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
<?php 
	include "partials/nav_bar.html";
	require "database.php";
?>
<!--nav bar end-->
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">
<link rel="stylesheet" href="styles.css">
<link id="favicon" rel="icon" href="Logo_ImageOnly.png" type="image/png" sizes="16x16">
<title>INDET</title>
<script>
$(function(){
	

$('#me').dataTable({
"order": [[ 0, "desc" ]],
"columnDefs": [ {
"targets": [3,4],
"orderable": false
} ]
});


});

</script>
<!--nav bar end-->

<div align="center">

  <div class="jumbotron">
    <h2 class="slide">Lead Generator Report</h2>
</div>
<!--label end-->


<!--modal-->


<!--modal end-->
<!--search-->
<div>



<!--search end-->
<?php 

  $con=mysqli_connect($host,$username,$password,$db)or die("could not connect to sql");
 if (!$con) {
        echo "<div>";
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
	    echo "</div>";	
}

$query = "SELECT *, l.type as r_type, l.id as lr_id FROM lead_gen_report l LEFT JOIN leadgen_tbl a on l.lead_gen_id = a.id ORDER BY created_at DESC";
$displayquery=mysqli_query($con,$query) or die('Could not look up user information; ' . mysqli_error($con));


?>
<div class="margined table-responsive">
<table id='me' data-toggle="table" id='example' class="table table-striped " cellpadding="5px" cellspacing="5px" width='95%'>
<thead>
<tr>
	<th class="text-center">Reference Number</th>
	<th class="text-center">Report Type</th>
	<th class="text-center">Schedule Type</th>
	<th class="text-center">Lead Gen Name</th>
	<th class="text-center">Period Covered</th>
	<th class="text-center">Leads Generated</th>
	<th class="text-center">Issued Leads</th>
	<th class="text-center">API Generated</th>
	<th class="text-center">Date Created</th>
	<th class="text-center"></th>
		<th><a class="a" id="deleteall" href="delete_pdf.php<?php echo "?summary_id=all"?>><img src="delete.png"> </a></th>


</tr>
</thead>
<tbody>
<?php
 WHILE($rows = mysqli_fetch_array($displayquery)):
	extract($rows);

	$entrydate=substr($created_at,0,4)."/".substr($created_at,4,2)."/".substr($created_at,6,2);
	
	$convertdate=substr($rows["created_at"],6,2)."/".substr($rows["created_at"],4,2)."/".substr($rows["created_at"],0,4);
	$span  =substr($date_from,6,2)."/".substr($date_from,4,2)."/".substr($date_from,0,4)."-";
	$span .=substr($date_to,6,2)."/".substr($date_to,4,2)."/".substr($date_to,0,4);

	if($entrydate==""){
		$entrydate="N/A";
		$convertdate="N/A";
	}

	echo "<tr>";

	echo "
	<td>$reference_number</td>
	<td>$r_type</td>
	<td>$schedule_type</td>
	<td>$name</td>
	<td>$span</td>
	<td>$leads_generated</td>
	<td>$leads_issued</td>
	<td>$" . number_format($api_generated,2) . "</td>
	";

	echo "
		<td data-order=".$entrydate.">".$convertdate."</td>

		";

	?>


	<td><a class="a_single_view btn btn-primary" target="_blank" href="lead_gen_report_view<?php echo "?id=$lr_id"?>" ><span class="glyphicon glyphicon-search" style="font-size:15px;"></span>
	</a></td>
		<td><a class="a_single" href="delete_lead_gen_report.php<?php echo "?id=$lr_id"?>" ><img src="delete.png" /></a></td>
	<!--<td><a class="a_single_email" href="email_lead_gen_report.php<?php echo "?id=$lr_id"?>"><img src="email.png"></a>
		 </td>--!>

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