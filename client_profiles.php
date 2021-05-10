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

$query = "SELECT clients_tbl.lead_by as lead_by, clients_tbl.city as city, clients_tbl.zipcode as zipcode, clients_tbl.date_submitted,clients_tbl.id,clients_tbl.name,adviser_tbl.name as x,leadgen_tbl.name as y,clients_tbl.appt_date,clients_tbl.appt_time,clients_tbl.address,clients_tbl.leadgen,clients_tbl.assigned_to,clients_tbl.assigned_date,clients_tbl.type_of_lead,clients_tbl.issued,clients_tbl.date_issued,clients_tbl.notes 
 FROM clients_tbl LEFT JOIN adviser_tbl ON clients_tbl.assigned_to = adviser_tbl.id LEFT JOIN leadgen_tbl ON clients_tbl.leadgen = leadgen_tbl.id WHERE binned=0 order by clients_tbl.date_issued desc;

";
$displayquery=mysqli_query($con,$query) or die('Could not look up user information; ' . mysqli_error($con));
?>

<div class="margined table-responsive">

		<div class="row">
			<div class="col-sm-10">
			</div>
			<div class="col-sm-2">
				<button type="button"  id="btn-add" name="btn-add" data-toggle="modal" data-target="#myModal" class="btn btn-primary form-control"><i class="fas fa-plus"></i> Add Client</button>
			</div>				
		</div>

		<table id='me' data-toggle="table" class="table table-striped " cellpadding="5px" cellspacing="5px" width='95%' style=" display: block; overflow-x: auto; white-space: nowrap;">
			<thead>
				<td>Client Name</td>
				<td>Appt Date</td>
				<td>Phone Number</td>
				<td>Address</td>
				<td>City</td>
				<td>Zip Code</td>
				<td>Source</td>
				<td>Lead Generator</td>
				<td>Assigned to</td>
				<td>Assigned Date</td>
				<!--td>Type of Lead</td>
				<td>Issued</td>-->
				<td>Date Submitted</td>
				<td>Notes</td>
				<td><a id="deleteall" class="a" href="delete_client.php?del_id=all"><img src="delete.png"></a></td>
				<td></td>


			</thead>
			<tbody>
			<?php

			WHILE($rows = mysqli_fetch_array($displayquery)):
			$id=$rows["id"];
			$name=$rows["name"];
			$x=$rows["x"]; //advisername
			$y=$rows["y"]; //leadgenname
				$search_lead_gen = "";
			$appt_date=$rows["appt_date"];
			$appt_time=$rows["appt_time"];
			$address=$rows["address"];
			$city=$rows["city"];
			$zipcode=$rows["zipcode"];
			$lead_by=$rows["lead_by"];
			$leadgen=$rows["leadgen"];
			$assigned_to=$rows["assigned_to"];
			$assigned_date=$rows["assigned_date"];
			$type_of_lead=$rows["type_of_lead"];
			$issued=$rows["issued"];
			$date_issued=$rows["date_issued"];
			$notes=$rows["notes"];
			$date_submitted=$rows["date_submitted"];
			$date_submitted_sort = $date_submitted;
			$date_submitted = substr($date_submitted, 6,2) . "/" . substr($date_submitted, 4,2) . "/" . substr($date_submitted, 0,4);

			$appt_date=date('d/m/Y',strtotime($appt_date));
			$assigned_date=date('d/m/Y',strtotime($assigned_date));

			/*$entrydate=$rows["entrydate"];
			$startingdate=$rows["startingdate"];
			$entrydate=substr($entrydate,0,4)."/".substr($entrydate,4,2)."/".substr($entrydate,6,2);

			$startingdate=substr($startingdate,0,4)."/".substr($startingdate,4,2)."/".substr($startingdate,6,2);


			$convertdate=substr($rows["entrydate"],6,2)."/".substr($rows["entrydate"],4,2)."/".substr($rows["entrydate"],0,4);

			$convertstartingdate=substr($rows["startingdate"],6,2)."/".substr($rows["startingdate"],4,2)."/".substr($rows["startingdate"],0,4);
			*/
			$lg = "";
			if($lead_by=="Self-Generated"){
				$lg = $x;
			}
			else{
				$lg = $y;
			}
			echo "
			<tr cellpadding='5px' cellspacing='5px'>

				<td>$name</td>
				<td>$appt_date</td>
				<td>$appt_time</td> 
				<td>$address</td>
				<td>$city</td>
				<td>$zipcode</td>
				<td>$lead_by</td>
				<td>$lg</td>
				<td>$x</td>
				<td>$assigned_date</td>
				<td data-order=".$date_submitted_sort.">$date_submitted</td>
				<td>$notes</td>
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



				<td><a class="a_single" href="delete_client.php<?php echo "?del_id=$id"?>" ><img src="delete.png" /></a></td>

			<td><a href="editclient.php<?php echo "?edit_id=$id"?>"><img src="edit.png"</a>
				</td>

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

	<!--
	Modals
	Editor
	-->
	<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header" style="background-color: #286090; ">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span style="color:white;" aria-hidden="true">×</span></button>
                    <h4 class="modal-title" id="myModalLabel" style="color:white;">Script Group Editor</h4>
                </div>
                <div class="modal-body">
                    <form id="frmGroup" name="frmGroup" class="form-horizontal" novalidate="">
                   
                        <div class="form-group error">
                            <label for="inputTask" class="col-sm-3 control-label">Priority</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control has-error" id="priority" name="priority" placeholder="Priority" value="" required>
                                <small class="error-label" for="caption">Least priority shows up first in the list. </small>
                            </div>                        
                        </div>
                        <div class="form-group error">
                            <label for="inputTask" class="col-sm-3 control-label">Name</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control has-error" id="name" name="name" placeholder="Name" value="" required>
                            </div>
                        </div>
                            
                        <input type="hidden" id="script_group_id" name="script_group_id" value="0">
                        <input type="hidden" id="formtype" name="formtype" value="0">
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="btn-save" value="add">Save changes</button>
                </div>
            </div>
        </div>
    </div>
    <!--
	End of Editor
	-->

	<!--
		Confirm Delete
	-->
	<div class="modal fade" id="confirmModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                    <h4 class="modal-title" id="myModalLabel">Confirm Deletion</h4>
                </div>
                <div class="modal-body">
                    <form id="frmDelUser" name="frmDelUser" class="form-horizontal" novalidate="">
                        <div class="form-group error">
                        	<label for="inputTask" class="col-sm-12 control-label">Are you sure you want to delete this User?
                          	</label>

                        </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" id="btn-delete-confirm" value="Yes">Confirm Delete</button>
                    <button type="button" class="btn btn-primary" id="btn-delete-cancel" value="No">Cancel</button>
                    <input name="_method" id="_method" type="hidden" value="delete" />
                    <input type="hidden" id="delete-script_group" value="0">
                </div>
            </div>
        </div>
    </div>
    <!--
		End of Confirm Delete
   	-->

</html>

<?php

}
?>