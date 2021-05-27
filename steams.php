 <?php
	session_start();

	//Restrict access to admin only
	include "partials/admin_only.php";

	if (!isset($_SESSION["myusername"])) {
		session_destroy();
		header("Refresh:0; url=index.php");
	} else {

		?>
 <html>

 <head>

 	<!--nav bar-->
 	<?php include "partials/nav_bar.html"; ?>
 	<!--nav bar end-->
 	<meta name="viewport" content="width=device-width, initial-scale=1">
 	<link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">
 	<link rel="stylesheet" href="styles.css">
 	<link id="favicon" rel="icon" href="Logo_ImageOnly.png" type="image/png" sizes="16x16">
 	<title>INDET</title>
 	<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.2/css/all.css" integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">
 	<script>
 		$(function() {

 			/*
 			$('#me').dataTable({
 			"columnDefs": [ {
 			"targets": [14,15],
 			"orderable": false
 			} ]
 			});

 			*/
 		});
 	</script>
 </head>

 <body>
 	<div align="center">
 		<div class="jumbotron">
 			<h2 class="slide">Teams (SADR)</h2>
 		</div>
 		<?php require "database.php";

				include_once("libs/api/controllers/Adviser.controller.php");
				$adviserController = new AdviserController();

				include_once("libs/api/controllers/STeam.controller.php");
				$teamController = new STeamController();
				$teams = $teamController->getAllTeams();

				?>
 		<div class="margined table-responsive">
 			<div class="row">
 				<div class="col-sm-9 text-center"></div>
 				<div class="col-sm-3 text-center"><button id="btn-add" name="btn-add" data-toggle="modal" data-target="#myModal" class="pull-right btn btn-primary btn-m"><i class="fa fa-plus"></i> Add New Team</button></div>
 			</div>
 			<br>
 			<table id='teams_table' data-toggle="table" id='example' class="table table-striped " cellpadding="5px" cellspacing="5px" width='95%'>
 				<thead>

 					<td>Team Name</td>
 					<td>Team Leader</td>
 					<td></td>
 					<td></td>


 				</thead>
 				<tbody>
 					<?php

							while ($rows = $teams->fetch_assoc()) :
								extract($rows);
								echo "
<tr id='team$id' cellpadding='5px' cellspacing='5px'>
	<td>$team_name</td>
	<td>$adviser_name</td>
	";

								?>

 					<td>
 						<input data-toggle="modal" data-target="#myModal" type="image" class="open-modal" src="edit.png" value='<?php echo "$id" ?>'>
 						<input type='image' class='delete' src='delete.png' data-toggle="tooltip" title="Delete Team" data-id="<?php echo $id ?>">
 					</td>
 					<td><a href="steam_members<?php echo "?id=$id" ?>" class="btn btn-primary"><i class="fas fa-search"></i></a></td>
 					<?php
								echo "</tr>";

							endwhile;
							?>
 				</tbody>
 			</table>
 		</div>
 		<!--
	Modals
	Editor
	-->
 		<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
 			<div class="modal-dialog modal-lg">
 				<div class="modal-content">
 					<div class="modal-header" style="background-color: #286090; ">
 						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span style="color:white;" aria-hidden="true">×</span></button>
 						<h4 class="modal-title" id="myModalLabel" style="color:white;">Team Editor</h4>
 					</div>
 					<div class="modal-body">
 						<form id="frmTeam" name="frmTeam" class="form-horizontal" novalidate="">
 							<div class="form-group error">
 								<label for="inputTask" class="col-sm-1 control-label">Name</label>
 								<div class="col-sm-5">
 									<input type="text" class="form-control has-error" id="name" name="name" placeholder="Name" value="" required>
 								</div>

 								<label for="inputTask" class="col-sm-1 control-label">Leader</label>
 								<div class="col-sm-5">
 									<select id="leader" class="form-control" name="leader" required />
 									<option value="0" selected>None</option>
 									<?php

											$advisers = $adviserController->getAllAdvisers();

											while ($rows = $advisers->fetch_assoc()) {
												$id = $rows["id"];
												$name = $rows["name"];
												//echo "<option value='".$id."'>".$name."</option>";
												if ($name != "EliteInsure Team")
													echo "<option value='" . $id . "'>" . $name . "</option>";
											}
											?>
 									</select>
 								</div>
 							</div>

 							<input type="hidden" id="team_id" name="team_id" value="0">
 							<input type="hidden" id="formtype" name="formtype" value="0">
 							<input type="hidden" id="action" name="action" value="0">
 						</form>
 					</div>
 					<div class="modal-footer">
 						<button type="button" class="btn btn-primary" id="btn-save" value="add">Save</button>
 					</div>
 				</div>
 			</div>
 		</div>
 		<!--
	End of Editor
	-->

 		<script src="js/steams-crud.js"></script>
 		<script>
 			var table = null;
 			$(function() {

 				$('.datepicker').datepicker({
 					dateFormat: 'dd/mm/yy'
 				});


 				$('#teams_table').dataTable({
 					"order": [
 						[0, "asc"]
 					],
 					"columnDefs": [{
 						"targets": [1, 2],
 						"orderable": true
 					}]
 				});

 				table = $("#teams_table").DataTable();
 				var counter = 1;
 			});
 		</script>
 </body>

 </html>

 <?php

	}
	?>