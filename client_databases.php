 <?php
session_start();

//Restrict access to admin only
include "partials/admin_only.php";

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
	

$('#me').dataTable({
"order": [[ 2, "desc" ]],
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
    <h2 class="slide">Client Database Report</h2>
</div>
<!--label end-->


<!--modal-->


<!--modal end-->
<!--search-->
<div>



<!--search end-->
<?php require "database.php";

  $con=mysqli_connect($host,$username,$password,$db)or die("could not connect to sql");
 if (!$con) {
        echo "<div>";
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
	    echo "</div>";	
}

$query = "SELECT *, c.id as id, c.created_at as date_created FROM client_data_reports c LEFT JOIN users u ON c.created_by = u.id ORDER BY date_created DESC";
$displayquery=mysqli_query($con,$query) or die('Could not look up user information; ' . mysqli_error($con));


?>
<div class="margined table-responsive">
<table id='me' data-toggle="table" id='example' class="table table-striped " cellpadding="5px" cellspacing="5px" width='95%'>
<thead>
<tr>
	<th class="text-center">Reference Number</th>
	<th class="text-center">Report Type</th>
	<th class="text-center">Sources</th>
	<th class="text-center">Lead Generators Selected</th>
	<th class="text-center">Advisers Selected</th>
	<th class="text-center">Filtered By</th>
	<th class="text-center">Filters</th>
	<th class="text-center">Period Covered</th>
	<th class="text-center">Date Created</th>
	<th class="text-center">Created By</th>
	<th class="text-center"></th>
		<th>
			
		</th>
</tr>
</thead>
<tbody>
<?php

 WHILE($rows = mysqli_fetch_array($displayquery)):
	extract($rows);
	$lead_gens = json_decode($lead_gens);
	$advisers = json_decode($advisers);
	$count_lead_gens = (count($lead_gens)==0) ? "All" : count($lead_gens);
	$count_advisers = (count($advisers)==0) ? "All" : count($advisers);
	$filterdata_array = json_decode($filterdata);
	$filterdata = str_replace('\'','',implode("','",$filterdata_array));
	$source = ($source=="") ? "All Lead Generators" : $source;
	if(empty($filterdata)){
		$filterdata = "None";
	}

	$entrydate=substr($created_at,0,4)."/".substr($created_at,4,2)."/".substr($created_at,6,2);
	
	$convertdate=substr($rows["date_created"],6,2)."/".substr($rows["date_created"],4,2)."/".substr($rows["date_created"],0,4);
	$span  =substr($date_from,6,2)."/".substr($date_from,4,2)."/".substr($date_from,0,4)." - ";
	$span .= substr($date_to,6,2)."/".substr($date_to,4,2)."/".substr($date_to,0,4);

	if($entrydate==""){
		$entrydate="N/A";
		$convertdate="N/A";
	}

	echo "<tr>";

	echo "
	<td>$reference_number</td>
	<td>$client_type</td>
	<td>$source</td>
	<td>$count_lead_gens</td>
	<td>$count_advisers</td>
	<td>" . ucfirst($filterby) . "</td>
	<td>$filterdata</td>
	<td>$span</td>
	";

	echo "
		<td data-order=".$entrydate.">".$convertdate."</td>
		<td>$username</td>
		";
	

	?>


	<td><a class="a_single_view btn btn-primary" target="_blank" href="client_database<?php echo "?reference_number=$reference_number"?>" ><span class="glyphicon glyphicon-download-alt" style="font-size:15px;"></span>
	</a></td>
		<td><a class="a_single" href="delete_client_database_report.php<?php echo "?id=$id"?>" ><img src="delete.png" /></a></td>

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