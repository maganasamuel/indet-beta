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

				$('#me').dataTable({
					"order": [
						[0, "asc"]
					],
					"columnDefs": [{
						"targets": [3, 4],
						"orderable": true
					}]
				});

			});
		</script>
	</head>

	<body>
		<?php require "database.php";
			$team_id = $_GET["id"];

			$con = mysqli_connect($host, $username, $password, $db) or die("could not connect to sql");
			if (!$con) {
				echo "<div>";
				echo "Failed to connect to MySQL: " . mysqli_connect_error();
				echo "</div>";
			}

			$query = "SELECT *, t.name as team_name, a.name as leader_name FROM steams t LEFT JOIN adviser_tbl a ON t.leader = a.id WHERE t.id = $team_id";
			$displayquery = mysqli_query($con, $query) or die('Could not look up user information; ' . mysqli_error($con));
			$team = mysqli_fetch_assoc($displayquery);
			if ($team["team_name"] == "EliteInsure Team") {
				$query = "SELECT * FROM adviser_tbl";
			} else {
				$query = "SELECT * FROM adviser_tbl WHERE steam_id = $team_id ORDER BY name";
			}
			$displayquery = mysqli_query($con, $query) or die('Could not look up user information; ' . mysqli_error($con));
			?>
		<div align="center">
			<div class="jumbotron">
				<h2 class="slide">Team <?php echo $team["team_name"] ?> Members</h2>
			</div>
			<div class="margined table-responsive">
				<h4>Leader <?php echo $team["leader_name"] ?></h4>
				<table id='me' data-toggle="table" id='example' class="table table-striped " cellpadding="5px" cellspacing="5px" width='95%'>
					<thead>

						<td>Adviser Name</td>
						<td>Adviser FSP number</td>
						<td>Adviser Address</td>
						<!--td>IRD number</td-->
						<td>Email Address</td>
						<td>Leads Charge</td>
						<td>Issued Charge</td>

						<!--
		<td><a  id="deleteall" class="a" href="delete_adviser.php?del_id=all">
		<img src="delete.png" />
		</a>
		</td>
	-->
						<td></td>
						<td></td>


					</thead>
					<tbody>
						<?php

							while ($rows = mysqli_fetch_array($displayquery)) :
								$id = $rows["id"];
								$name = $rows["name"];
								$fsp_num = $rows["fsp_num"];
								$address = $rows["address"];
								$ird_num = $rows["ird_num"];
								$email = $rows["email"];
								$leads = $rows["leads"];
								$bonus = $rows["bonus"];


								echo "
<tr cellpadding='5px' cellspacing='5px'>
	<td>$name</td>
	<td>$fsp_num</td>
	<td>$address</td>
	<td>$email</td>
	<td>$leads</td>
	<td>$bonus</td>

	";

								?>



							<!--<td><a class="a_single" href="delete_adviser.php<?php echo "?del_id=$id" ?>" ><img src="delete.png" /></a></td>-->
							<td><a href="edit_adviser.php<?php echo "?edit_id=$id" ?>"><img src="edit.png"></a> </td>
							<td><a href="adviser_profile.php<?php echo "?id=$id" ?>" class="btn btn-primary"><i class="fas fa-search"></i></a> </td>

						<?php
								echo "</tr>";

							endwhile;
							?>
					</tbody>
				</table>
			</div>
	</body>

	</html>

<?php

}
?>