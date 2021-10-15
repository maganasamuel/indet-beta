<?php

session_start();

//Restrict access to admin only
include "partials/admin_only.php";

$_SESSION["x"] = 1;
unset($_SESSION['adviser_id']);
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
		<style>
			div#myModal {
				padding-top: 0px !important;
			}

			.modal-dialog {
				min-width: 100%;
				min-height: 100%;
				height: auto;
				width: auto;
				margin: 0;
				padding: 0;
			}

			.modal-content {

				width: 100% !important;
				height: auto;
				min-height: 100%;
				border-radius: 0;
			}
		</style>

		<script>
			$(document).ready(function() {

				var advisers = [];

				$("#datefrom").datepicker({
					dateFormat: 'dd/mm/yy',
					beforeShowDay: function(date) {
						if (date.getDate() == 16 || date.getDate() == 1) {
							return [true, '', "Available"];
						} else {
							return [false, '', "Unavailable"];
						}
					}
				});

				$("#datefrom").on('change', function() {
					var $this = $(this).val();
					var m = parseInt($this.substr(3, 2));
					var month = m - 1;
					var year = parseInt($this.substr(6, 4));
					var ifday = parseInt($this.substr(0, 2));
					var pay_day = 0;

					var lastday = function(y, m) {
						return new Date(y, m + 1, 0).getDate();
					}

					console.log(ifday);

					if (ifday == 16) {
						//First Half
						var ld = lastday(year, month) + '/' + n(m) + '/' + year;
						var next_month = m + 1;
						var next_year = year;

						if (next_month > 12) {
							console.log("Next Month:" + next_month);
							next_month = 1;
							next_year = year + 1;
						}

						var pd = "07" + '/' + n(next_month) + '/' + next_year;
						$('#dateuntil').val(ld);
						$('#pay_date').val(pd);
					} else {
						//Second Half
						var ld = '15' + '/' + n(m) + '/' + year;


						var pd = '21' + '/' + n(m) + '/' + year;
						$('#dateuntil').val(ld);
						$('#pay_date').val(pd);
					}


				});

				function n(n) {
					return n > 9 ? "" + n : "0" + n;
				}

				$('#create').prop('disabled', false);

				$('#team').on('change', function() {
					var team_id = $(this).val();

					$.get('team_data?id=' + team_id, function(data) {
						console.log(data);
						advisers = data.adviser_ids;
					});
				});

				$('#create').on('click', function(e) {
					e.preventDefault();

					var team = $("#team").val();
					var date_from = $("#datefrom").val();
					var pay_date = $("#pay_date").val();
					var until = $("#dateuntil").val();

					if (team == "" || team == null) {
						alert("Please select a team");
						return false;
					}

					console.log(date_from + ":" + until);
					$.ajax({
						dataType: 'json',
						type: 'POST',
						data: {
							team: team,
							advisers: JSON.stringify(advisers),
							date_from: date_from,
							pay_date: pay_date,
							until: until
						},
						url: "team_deal_tracker_preview.php",
						beforeSend: function(){
							$("#create_spinner").show();
						},
						success: function(e) {
							$("#create_spinner").hide();
							var mydata = JSON.stringify(e);
							var link = e['link'];
							var htm = '<iframe src="' + link + '" style="width: 100%;height: 75%;"></iframe>';
							$('#myModal').modal('show');
							$('.modal-body').html(htm);
							$('#save_pdf').unbind("click");
							$('#save_pdf').on('click', function() {
								$.ajax({
									//dataType:'JSON',
									data: {
										mydata: mydata
									},
									type: 'POST',
									url: "save_team_deal_tracker_report.php",
									beforeSend: function() {
										$("#save_pdf_spinner").show();
									},
									success: function(x) {
										$("#save_pdf_spinner").hide();
										console.log(x);
										$.confirm({
											title: 'Success!',
											content: 'You have successfully created a deal report.',
											buttons: {
												Ok: function() {
													console.log(x);
													window.location = 'create_team_deal_tracker_report.php';
												},
											}
										});
									}
								});
							});
						},
						error: function(x) {
							x = JSON.stringify(x);
							console.log("Data:" + x);
						}
					});

				});

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
				<h2 class="slide">Create Team Policy Tracker Report</h2>
			</div>
			<!--label end-->

			<?php require "database.php";
				$con = mysqli_connect($host, $username, $password, $db) or die("could not connect to sql");
				if (!$con) {
					echo "<div>";
					echo "Failed to connect to MySQL: " . mysqli_connect_error();
					echo "</div>";
				}

				?>
			<form method="POST" action="deal_report_preview.php" autocomplete="off" class="margined">
				<table align="center" bgcolor="ededed" cellpadding="5px" onload="form1.reset();">
					<div class='row'>

						<div class='col-sm-2'></div>
						<div class='col-sm-2'>
							<label>Team
								<div class="input-group">
									<span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
									<select name="team" class="form-control" id="team" required />
									<option value="" disabled selected>Select Team</option>
									<?php
										$query = "SELECT id,name FROM teams ORDER BY name asc";
										$displayquery = mysqli_query($con, $query) or die('Could not look up user information; ' . mysqli_error($con));
										while ($rows = mysqli_fetch_array($displayquery)) {
											$id = $rows["id"];
											$name = $rows["name"];
											echo "<option value='$id'>" . $name . "</option>";
										}
										?>
									<!--<td><input class="addadviser" type="text" id="datepicker" name="mydate" required></td>-->
									</select>
									<input type='hidden' name='adv_name' id='adv_name' value=''>
									<input type='hidden' name='advisers' id='advisers' value=''>
								</div>
							</label>
						</div>
						<div class='col-sm-2'>
							<label style="width: 100% !important;">Date From
								<div class="input-group">
									<span class="input-group-addon">
										<i class="fa fa-calendar" aria-hidden="divue"></i></span>
									<input class="form-control datepicker" autocomplete="off" type="text" id="datefrom" name="date_from" /></div>
							</label>

						</div>
						<div class='col-sm-2'>
							<label>Until
								<div class="input-group">
									<span class="input-group-addon">
										<i class="fa fa-calendar" aria-hidden="divue"></i></span>
									<input class="form-control" autocomplete="off" type="text" id="dateuntil" name="until" readonly /></div>
							</label>
						</div>
						<div class='col-sm-2'>
							<label>Pay Date
								<div class="input-group">
									<span class="input-group-addon">
										<i class="fa fa-calendar" aria-hidden="divue"></i></span>
									<input class="form-control" autocomplete="off" type="text" id="pay_date" name="pay_date" readonly />
								</div>
							</label>

						</div>

					</div>



					<div class="row">
						<div class="col-sm-2 center">

							<button type="button" class="btn btn-danger center form-control" id='create' style="margin-top:30px;"> <i id="create_spinner" class="fas fa-spinner fa-spin" style="display:none;"></i> Create Report</button>

						</div>
					</div>
			</form>


			<div class="container">

				<!-- Modal -->
				<div class="modal fade" id="myModal" role="dialog" style="z-index:10000;width: 100%;">
					<div class="modal-dialog modal-lg">
						<div class="modal-content">
							<div class="modal-header">
								<button type="button" class="close" data-dismiss="modal">&times;</button>
								<h2 class="modal-title" style="float: left;">Policy Report Preview</h2>
							</div>
							<div class="modal-body">

							</div>
							<div class="modal-footer">
								<button type="button" class="btn btn-info" id='save_pdf'><i id="save_pdf_spinner" class="fas fa-spinner fa-spin" style="display:none;"></i> Save</button>
								<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
							</div>
						</div>
					</div>
				</div>
			</div>



	</body>


	</html>

<?php

}

?>
