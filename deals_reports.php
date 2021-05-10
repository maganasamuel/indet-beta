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
		"order": [[ 1, "desc" ]],
		"columnDefs": 
		[ {
		"targets": [3,4],
		"orderable": true
		} ]
	});

	$('.note').on('change',function(e){
		e.preventDefault();
		var note=$(this).val();
		var me=$(this);
		var prev=$(this).data('prev');
		var id=$(this).data('id');
		$.confirm({
			title:'Confirm',
			content:'Are you sure you want to save this?',
			buttons:{
				confirm:function(){
					$.ajax({
							url:'save_note.php',
							data:{id:id,note:note},
							type:'POST',
							success:function(e){
								$.confirm(e);
							}
						});
				},
				cancel:function(){
					me.val(prev);
				}	
			}
		});
	});

		$('.desc').on('change',function(e){
		e.preventDefault();
		var desc=$(this).val();
		var me=$(this);
		var prev=$(this).data('prev');
		var id=$(this).data('id');
		$.confirm({
				title:'Confirm',
				content:'Are you sure you want to save this?',
				buttons:{
					confirm:function(){

					$.ajax({
							url:'save_desc.php',
							data:{id:id,desc:desc},
							type:'POST',
							success:function(e){
								$.confirm(e);
							}
						});
					},
					cancel:function(){
						me.val(prev);
					}	
				}

			});


		});


});

</script>
<!--nav bar end-->

<div align="center">

  <div class="jumbotron">
    <h2 class="slide">Adviser Reports</h2>
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

	$query = "SELECT *, d.type as report_type, d.id as deal_id, a.id as adviser_id FROM deals_report d LEFT JOIN adviser_tbl a ON d.adviser_id = a.id ORDER BY date_created DESC";
	$displayquery=mysqli_query($con,$query) or die('Could not look up user information; ' . mysqli_error($con));
?>
<div class="margined table-responsive">
<table id='me' data-toggle="table" id='example' class="table table-striped " cellpadding="5px" cellspacing="5px" width='95%'>
<thead>
<tr>
	<th class='text-center'>Report Type</th>
	<th class='text-center'>Adviser Name</th>
	<th class='text-center'>Total Amount</th>
	<th class='text-center'>Date Created</th>
	<th></th>
	<th class='text-center'><a class="a" id="deleteall" href="delete_deal_report.php<?php echo "?deal_id=all"?>"><img src="delete.png" /></a></th>
	<!--td></td-->

</tr>
</thead>
<tbody>
<?php

 WHILE($rows = mysqli_fetch_array($displayquery)):
	extract($rows);
	$open_link = "";
	if($report_type=="Team"){
		$name = "Everyone";
		$open_link = "production_report?id=$deal_id";
	}
	else{
		$open_link = "deal?id=$deal_id";
	}
	$convertdate=NZEntryToDateTime($date_created);
	$filterdata = json_decode($filterdata);
	$report_data = json_decode($report_data);
	$total = 0;

	if(in_array("Submission", $filterdata)){
		$total += $report_data->total_submission_api;
	}

	if(in_array("Issued", $filterdata)){
		$total += $report_data->total_issued_api;
	}

	if(in_array("Cancelled", $filterdata)){
		$total -= $report_data->total_cancelled_api;
	}

	if($date_created==""){
		$date_created="N/A";
		$convertdate="N/A";
	}

	echo "
	<tr>
		<td>$report_type</td>
		<td>$name</td>
		<td>$" .  number_format($total,2) . "</td>
		<td data-order=".$date_created.">".$convertdate."</td>
		";
?>

	<td><a class="a_single_view btn btn-primary" target="_blank" href="<?php echo $open_link ?>" ><span class="glyphicon glyphicon-search" style="font-size:15px;"></span>
	</a></td>

	<td><a class="a_single" href="delete_deal_report.php<?php echo "?deal_id=$deal_id"?>" ><img src="delete.png" /></a></td>


<!--td><a href="editbcti.php<?php echo "?adviser_id=$adviser_id&starting_date=$starting_date"?>"><img src="edit.png"></a> </td>
	 </td-->


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


function DateTimeToNZEntry($date_submitted){
  return substr($date_submitted,6,4).substr($date_submitted,3,2).substr($date_submitted,0,2);
}

function NZEntryToDateTime($NZEntry){
    return substr($NZEntry,6,2) . "/" . substr($NZEntry,4,2) . "/" . substr($NZEntry, 0, 4);
}
?>