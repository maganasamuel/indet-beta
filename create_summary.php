<?php
session_start();
date_default_timezone_set('Pacific/Auckland');
$_SESSION["x"] = 1;
if (!isset($_SESSION["myusername"])) {
	session_destroy();
	header("Refresh:0; url=index.php");
} else {
	?>
	<html>


	<head>
		<!--nav bar-->
		<?php include "partials/nav_bar.html"; ?>



		<?php require "database.php"; ?>
		<!--nav bar end-->
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">
		<link rel="stylesheet" href="styles.css">
<link id="favicon" rel="icon" href="Logo_ImageOnly.png" type="image/png" sizes="16x16">
<title>INDET</title>
		<style>
			.bootstrap-select .dropdown-toggle .filter-option {
				position: relative;

			}

			.bootstrap-select li a {
				color: #333 !important;
			}
		</style>




		<script>
			$(function() {
				var sp = $('.selectpicker').selectpicker();
				sp.on("changed.bs.select", function(e, clickedIndex, newValue, oldValue) {
					var selectedD = $(this).find('option').eq(clickedIndex).val();
					// console.log('selectedD: ' + selectedD + '  newValue: ' + newValue + ' oldValue: ' + oldValue);
					var arr = sp.val();

					if (arr.length == 0) {
						$('.filter-option-inner-inner').html('No status selected');
					} else if (arr.length == 6) {
						$('.filter-option-inner-inner').html('Selected all statuses');
					} else {
						$('.filter-option-inner-inner').html('selected ' + arr.length + ' statuses');
					}
					console.log(arr);
				});

				$('#me').dataTable({
					"order": [
						[7, "desc"]
					],
					"columnDefs": [{
						"targets": [9, 10],
						"orderable": false
					}]
				});

				$("#adviser_id").on("change", function(){
					var adviser_id = $(this).val();

					$.ajax({
						data: {
							action : "fetch_oldest_assigned_client",
							adviser_id : adviser_id
						},
						type: 'GET',
						url: "libs/adviser_helper",
						beforeSend: function() {},
						success: function(x) {
							var today = new Date();
							var dd = today.getDate();
							var mm = today.getMonth() + 1; //January is 0!

							var yyyy = today.getFullYear();
							if (dd < 10) {
							dd = '0' + dd;
							} 
							if (mm < 10) {
							mm = '0' + mm;
							} 
							var today = dd + '/' + mm + '/' + yyyy;
							console.log(x);
							$("#datepicker").val(x.translated_assigned_date);
							$("#datepicker2").val(today);
						},
						error: function(x) {
							var res = x.responseText.split("\n");
							$.confirm({
								title: 'Creating Invoice Unsuccessful',
								content: x,
								buttons: {
									Ok: function() {
										console.log(x);
									},
								}
							});
						}
					});
				})


				//Create Button
				$('#create').on('click', function(e) {
					console.log("Submitting");
					e.preventDefault();

					console.log("Denied Submitting");
					var errorMessage = "";
					var adviser_id = $("#adviser_id").val();
					var date_from = $("#datepicker").val();
					var date_to = $("#datepicker2").val();
					var summary_date = $("#summary_date").val();
					var status = JSON.stringify($("#statuses").val());
					var data = {
							adviser_id: adviser_id,
							date_from: date_from,
							until: date_to,
							summary_date: summary_date,
							statuses: status,
						};
					console.log(data);

					$.ajax({
						dataType: 'json',
						type: 'POST',
						data: data,
						url: "output2.php",
						success: function(e) {
							console.log(e);
							if (e.status == "error") {
								errorMessage = e.responseText;
								$.confirm({
									title: 'Summary Creation Failed.',
									content: errorMessage,
									buttons: {
										Ok: function() {
											return;
										},
									}
								});
							} else {
								var mydata = JSON.stringify(e);
								var link = e['link'];
								var htm = '<iframe src="' + link + '" style="width: 100%;height: 75%;"></iframe>';
								$('#myModal').modal('show');
								$('.modal-body').html(htm);
								$('#save_pdf').unbind("click");
								$('#save_pdf').on('click', function() {
									$.ajax({
										data: {
											mydata: mydata
										},
										type: 'POST',
										url: "save_summary.php",
										beforeSend: function() {},
										success: function(x) {
											$.confirm({
												title: 'Success!',
												content: 'You have successfully created an invoice summary!',
												buttons: {
													Ok: function() {
														console.log(x);
														window.location = 'create_summary.php';
													},
												}
											});
										},
										error: function(x) {
											var res = x.responseText.split("\n");
											$.confirm({
												title: 'Creating Invoice Unsuccessful',
												content: x,
												buttons: {
													Ok: function() {
														console.log(x);
													},
												}
											});
										}
									});
								});
							}


						},
						error: function(x) {

							$.confirm({
								title: 'Creating Invoice Unsuccessful',
								content: x,
								buttons: {
									Ok: function() {
										console.log(x);
									},
								}
							});
						}
					});
				});
			});
		</script>
	</head>

	<body>
		<!--nav bar end-->

		<!--label-->
		<div align="center">
			<div class="jumbotron">
				<h2 class="slide">Create Invoice Summary</h2>
			</div>


			<?php require "database.php";
			?>


			<form method="POST" action="output2.php" autocomplete="off" class="margined">
				<?php
				date_default_timezone_set('Pacific/Auckland');
				$due = date('d/m/Y', strtotime('+7 days'));
				$now_ = date('d/m/Y');

				?>

				<div>
					<div class="row">
						<div class='col-sm-2 center'>
							<label style="width: 100%" ;>Summary of:
								<input class="form-control" value="<?= $now_; ?>" readonly='' autocomplete="off" type="hidden" name="summary_date" id="summary_date" />

								<select name="adviser_id" class="form-control adviser_selectpicker" id="adviser_id" data-actions-box="true">
									<optgroup label="Advisers">
										<option hidden disabled selected>Select Adviser</option>
										<?php
										$query = "SELECT * FROM adviser_tbl ORDER BY name";
										$displayquery = mysqli_query($con, $query) or die('Could not look up user information; ' . mysqli_error($con));

										while ($rows = mysqli_fetch_array($displayquery)) {
											$id = $rows["id"];
											$name = $rows["name"];
											echo "<option value=$id>$name</option>";
										}
										?>
									</optgroup>
								</select>
							</label>
						</div>
					</div>


					<div class="row">
						<div class='col-sm-2 center'>Starting Date:
							<input name="date_from" class="form-control" autocomplete="off" type="text" id="datepicker" value="" required />
						</div>
					</div>

					<div class="row">
						<div class='col-sm-2 center'>Until:
							<input name="until" class="form-control" autocomplete="off" type="text" id="datepicker2" required /></label>
						</div>
					</div>
					<div class="row" style="display:none;">
						<div class='col-sm-2 center'>
							<label style="width: 100%" ;>Status:
								<select name="statuses[]" class="form-control selectpicker" id="statuses" multiple data-actions-box="true" multiple>
									<optgroup label="Status">
										<option selected>Pending</option>
										<option>Paid</option>
										<option>Contested</option>
										<option>Cancelled</option>
										<option>Waived</option>
										<option>Others</option>
									</optgroup>
								</select>
							</label>
						</div>
					</div>
					<div class="row">
						<div class='col-sm-2 center'>
							<input name="enter" type="submit" id='create' value="Create Summary" style='margin-top: 30px;width: 100%;' class="btn btn-danger center" />
						</div>
					</div>

				</div>







		</div>
		</form>
		<hr>




		<div class="container">

			<!-- Modal -->
			<div class="modal fade" id="myModal" role="dialog" style="z-index:10000;width: 100%;">
				<div class="modal-dialog modal-lg">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal">&times;</button>
							<h2 class="modal-title" style="float: left;">Summary Preview</h2>
						</div>
						<div class="modal-body">

						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-info" id='save_pdf'>Save</button>
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