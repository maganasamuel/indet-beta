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
    <h2 class="slide">Deal Tracker Summaries</h2>
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

	$query = "SELECT * FROM deal_tracker_summary ORDER BY date_created DESC";
	$displayquery=mysqli_query($con,$query) or die('Could not look up user information; ' . mysqli_error($con));
?>
<div class="margined table-responsive">
<table id='me' data-toggle="table" id='example' class="table table-striped " cellpadding="5px" cellspacing="5px" width='95%'>
<thead>
<tr>
	<th class='text-center'>Adviser Name</th>
	<th class='text-center'>Period Covered</th>
	<th class='text-center'>Information</th>
	<th class='text-center'>Date Created</th>
	<th></th>
	<th class='text-center'><a class="a" id="deleteall" href="delete_deal_tracker_report.php<?php echo "?deal_id=all"?>"><img src="delete.png" /></a></th>
	<!--td></td-->

</tr>
</thead>
<tbody>
<?php

 WHILE($rows = mysqli_fetch_array($displayquery)):
    extract($rows);
    $adviser_id = json_decode($adviser_id, true);
    $name = "";
    $deal_id = $id;
    $report_data = json_decode($report_data);
    $information = $report_data->information;
    $information = implode(", ", $information);
    if(count($adviser_id)>3){
        $name = "Multiple Advisers";
    }
    else{
        $adviser_ids = implode(",", $adviser_id);
        $qry = "SELECT name FROM adviser_tbl WHERE id IN($adviser_ids)";
        $res = mysqli_query($con, $qry);
        $name = array();
        WHILE($row = mysqli_fetch_array($res)):
            $name[] = $row["name"];
        ENDWHILE;
        $name = implode(", ", $name);
    }

	$convertdate=NZEntryToDateTime($date_created);
	$period_covered = NZEntryToDateTime($date_from) . " - " . NZEntryToDateTime($date_to);

	echo "
	<tr>
		<td>$name</td>
		<td>$period_covered</td>
		<td>$information</td>
		<td data-order=".$date_created.">".$convertdate."</td>
		";
?>

	<td><a class="a_single_view btn btn-primary" target="_blank" href="deal_tracker_summary<?php echo "?id=$deal_id"?>" ><span class="glyphicon glyphicon-search" style="font-size:15px;"></span>
	</a></td>

	<td><a class="a_single" href="delete_deal_tracker_summary.php<?php echo "?id=$deal_id"?>" ><img src="delete.png" /></a></td>


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