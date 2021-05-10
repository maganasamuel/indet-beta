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





});

</script>
</head>
<body>
<!--header-->
<div align="center">


<!--header end-->

<!--nav bar-->


<!--nav bar end-->


<!--label-->

  <div class="jumbotron">
    <h2 class="slide">DO NOT CALL LIST</h2>
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

$agent = new stdClass();
$agent->name = $_SESSION['myusername'];
$linked_id = $_SESSION['myuserid'];

$query = "SELECT * FROM datamined_numbers";
$displayquery=mysqli_query($con,$query) or die('Could not look up user information; ' . mysqli_error($con));

?>
	<div class="row">
		<div class="col-sm-12"> 
				<form id="frmLead" name="frmLead" novalidate="">

					<div class="form-group" style="display:none;">
							<label for="exampleInputEmail1">Agent</label>
							<input type="text" readonly="" class="form-control" id="agent" name="agent" aria-describedby="agent" value="<?php echo $agent->name ?>">
							<input type="hidden" readonly="" class="form-control" id="agent_id" name="agent_id" aria-describedby="agent_id" value="<?php echo $linked_id ?>">
					</div>
					<h3 id="appointment_information">Lead Information</h3>

					<div class="row">
							<div class="form-group">
									<div class="col-sm-4"></div>
									<div class="col-sm-2">
											<label for="name" class="pull-left">Name</label>
										<input type="text" class="form-control" id="name" name="name" aria-describedby="name" placeholder="Name">
										<input type="hidden" class="form-control" id="data_id" name="data_id">
										<input type="hidden" class="form-control" id="formtype" name="formtype" value="add">
									</div>
									<div class="col-sm-2">
											<label for="number" class="pull-left">Number</label>
											<input type="text" class="form-control" id="number" name="number" aria-describedby="number" placeholder="Number">
									</div>
									<div style="display:none;">
										<div class="col-sm-1">
												<label for="status">Status</label>
										</div>
										<div class="col-sm-2">
											<select class="form-control" id="status" name="status" aria-describedby="status">
													<option>Do Not Call</option>
													<option>Callable</option>
													<option>Called</option>
											</select>
										</div>
									</div>						
									
							</div>
					</div>

					<div class="row" id="updating_notification" style="display:none;">
							<div class="form-group">
									<div class="col-sm-12">
											<small style="color:green;">Updating Number</small>
									</div>
							</div>
					</div>
					<p></p>
					
					<div class="row">
							<div class="form-group">								
								<div class="col-sm-4"></div>
								<div class="col-sm-2">
											<button id="btn-save" class="form-control btn btn-success" type="button"><i class="fas fa-save"></i> Save</label>
									</div>
									<div class="col-sm-2">
											<button id="reset" class="form-control btn btn-danger" type="reset"><i class="fas fa-eraser"></i> Clear</label>
									</div>
							</div>
					</div>
					
					
				</form>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-sm-12"> 
				<table id='me' data-toggle="table" class="table table-striped" cellpadding="5px" cellspacing="5px" width='100%' style=" display: block; ">
					<thead>
						<td>Name</td>
						<td>Phone Number</td>
						<td>Actions</td>
					</thead>
					<tbody id="data-list">
					<?php

					WHILE($rows = mysqli_fetch_array($displayquery)):
					extract($rows);

					$actions = "<input type='image' class='edit'  src='edit.png' value='$id'> &nbsp;";

					if($status=="Callable"){
						$actions .= "<a href='' class='btn-success' data-number='$number'><i class='fas fa-phone' style='font-size:30px;'></i></a>";
						$actions .= "<a href='' class='btn-primary' data-id='$id'><i class='fas fa-check' style='font-size:30px;'></i></a>";
						$actions .= "<a href='' class='btn-danger' data-id='$id'><i class='fas fa-cancel' style='font-size:30px;'></i></a>";
					}

					$actions .= "&nbsp; <input type='image' class='delete-data'  src='delete.png' value='$id'>";

					echo "
						<tr id='data$id' cellpadding='5px' cellspacing='5px'>
							<td>$name</td>
							<td>$number</td>
							<td>$actions</td>
						";
					?>

					<?php 
						echo "</tr>";
						endwhile;
					?>

					<!--
						<tr cellpadding='5px' cellspacing='5px'>
								<td><a href="skype:+639465271297?call">Call</a></td>
						</tr>
					-->

					</tbody>
				</table>
		</div>
		
</div>


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
                        	<label for="inputTask" class="col-sm-12 control-label">Are you sure you want to delete this Number?
                          	</label>
												</div>
								</div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" id="btn-delete-confirm" value="Yes">Confirm Delete</button>
                    <button type="button" class="btn btn-primary" id="btn-delete-cancel" value="No">Cancel</button>
                    <input name="_method" id="_method" type="hidden" value="delete" />
                    <input type="hidden" id="delete-data" value="0">
                </div>
            </div>
        </div>
    </div>
    <!--
		End of Confirm Delete
		 -->
		 
	<script src="js/dataminer-crud.js"></script>
	<script>
			var table = null;
			$(function(){
				$('#me').dataTable({
						"columns": [
							{ "width": "1%" },
							{ "width": "1%" },
							{ "width": "1%" }
						],
						"order": [[ 0, "asc" ]],
						"columnDefs": 
						[ {
						"targets": [1,2],
						"orderable": true
						} ]
				});

				table = $("#me").DataTable();
				var counter = 1;            
		});
	</script>
</body>


</html>

<?php

}
?>