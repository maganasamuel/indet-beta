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
	<script>
		$(function() {


			$('#me').dataTable({
				"order": [
					[1, "desc"]
				],
				"columnDefs": [{
					"targets": [3, 4],
					"orderable": true
				}]
			});

			$('.note').on('change', function(e) {
				e.preventDefault();
				var note = $(this).val();
				var me = $(this);
				var prev = $(this).data('prev');
				var id = $(this).data('id');
				$.confirm({
					title: 'Confirm',
					content: 'Are you sure you want to save this?',
					buttons: {
						confirm: function() {
							$.ajax({
								url: 'save_note.php',
								data: {
									id: id,
									note: note
								},
								type: 'POST',
								success: function(e) {
									$.confirm(e);
								}
							});
						},
						cancel: function() {
							me.val(prev);
						}
					}
				});
			});

			$(".delete").on("click", function() {
				var id = $(this).data("id");
				data = {
					action: 'delete_customized_invoice',
					id: id
				};

				$.confirm({
					title: 'Confirm',
					content: 'Are you sure you want to delete this?',
					buttons: {
						confirm: function() {
							$.ajax({
								url: 'libs/api/common_api.php',
								data: data,
								type: 'POST',
								success: function(e) {
									console.log(e);
									$("#ci_" + id).remove();
								},
								error: function(e) {
									console.log(e);
								}
							});
						},
						cancel: function() {
							me.val(prev);
						}
					}
				});
			});

			$(document).on("click", ".email_invoice", function() {
				var invoice_id = $(this).data("id");
				var invoice_num = $(this).data("number");
				$(this).prop('disabled', true);
				var mat_id = $(this).val();
				$('#emailModal').modal('show');
				$("#id").val(invoice_id);
				$("#action").val("send_customized_invoice");
			});

			$("#confirm_email").on("click", function() {
				var data = $("#email_info").serializeArray();
				var url = "";
				$("#sending_spinner").show();
				var btn = $(this);
				var cancel_btn = $("#cancel_email");
				btn.prop("disabled", true);
				cancel_btn.prop("disabled", true);
				
				$.ajax({
					data: data,
					type: "post",
					url: 'email_customized_invoice.php',
					success: function(data) {
						console.log(data);
						btn.prop("disabled", false);
						cancel_btn.prop("disabled", false);
						$("#email_info").trigger("reset");
						$('#emailModal').modal('hide');
					},
					error: function(data) {
						btn.prop("disabled", false);
						cancel_btn.prop("disabled", false);
						console.log(data);
						console.log('Error:', data);
					}
				});
			});

			$("#cancel_email").on("click", function() {
				$('#emailModal').modal('hide');
				$("#email_info").trigger("reset");
			});
		});
	</script>
	<!--nav bar end-->

	<div align="center">

		<div class="jumbotron">
			<h2 class="slide">CUSTOMIZED INVOICES</h2>
		</div>
		<!--label end-->


		<!--modal-->


		<!--modal end-->
		<!--search-->
		<div>



			<!--search end-->
			<?php require "database.php";

				$con = mysqli_connect($host, $username, $password, $db) or die("could not connect to sql");
				if (!$con) {
					echo "<div>";
					echo "Failed to connect to MySQL: " . mysqli_connect_error();
					echo "</div>";
				}

				$query = "SELECT * FROM customized_invoices ORDER BY date_created DESC";
				$displayquery = mysqli_query($con, $query) or die('Could not look up user information; ' . mysqli_error($con));
				?>
			<div class="margined table-responsive">
				<table id='me' data-toggle="table" id='example' class="table table-striped " cellpadding="5px" cellspacing="5px" width='95%'>
					<thead>
						<tr>
							<th class='text-center'>Invoice Number</th>
							<th class='text-center'>Date Created</th>
							<th class='text-center'>Company</th>
							<th class='text-center'>Name</th>
							<th class='text-center'>Total Amount</th>
							<th></th>
							<th class='text-center'></th>
							<th class='text-center'></th>
							<!--td></td-->

						</tr>
					</thead>
					<tbody>
						<?php

							while ($rows = mysqli_fetch_array($displayquery)) :
								extract($rows);


								$convertdate = substr($date_created, 6, 2) . "/" . substr($date_created, 4, 2) . "/" . substr($date_created, 0, 4);

								if ($date_created == "") {
									$date_created = "N/A";
									$convertdate = "N/A";
								}
								echo "
	<tr id='ci_$id'>
		<td>$invoice_number</td>
		<td data-order=" . $date_created . ">" . $convertdate . "</td>
		<td>$company_name</td>
		<td>$name</td>
		<td>$" . number_format($total_amount, 2, ".", ",") . "</td>
		";
								?>
						<td><a class="a_single_view btn btn-primary" target="_blank" href="customized_invoice_view<?php echo "?id=$id" ?>"><span class="glyphicon glyphicon-search" style="font-size:15px;"></span>
							</a></td>

						<td><input type='image' class='delete' src='delete.png' data-id="<?php echo $id ?>"></td>
						<td><a class="email_invoice" href="#" data-id="<?php echo "$id" ?>" data-number="<?php echo "$invoice_number" ?>" data><img src="email.png"></a>
						</td>
						<?php
								echo "</tr>";
							endwhile;
							?>
					</tbody>
				</table>
			</div>

			<!--
		Confirm Delete
	-->
			<div class="modal fade" id="emailModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
							<h4 class="modal-title" id="myModalLabel">Email Invoice</h4>
						</div>
						<form id="email_info" name="email_info" class="form-horizontal" novalidate="">
							<div class="modal-body">
								<div class="form-group error">
									<label for="inputTask" class="col-sm-2 control-label">Email(s)</label>
									<div class="col-sm-10">
										<textarea class="form-control has-error" id="emails" name="emails" placeholder="Separate multiple emails by adding a comma in between." value="" required></textarea>
									</div>
								</div>
							</div>
							<div class="modal-footer">
								<button type="button" class="btn btn-danger" id="confirm_email" value="Yes"> <i id="sending_spinner" class="fas fa-spinner fa-spin" style="display:none;"></i> Send</button>
								<button type="button" class="btn btn-primary" id="cancel_email" value="No">Cancel</button>
								<input name="action" id="action" type="hidden" value="send_customized_invoice" />
								<input type="hidden" id="id" name="id" value="0">
							</div>
						</form>
					</div>
				</div>
			</div>
			<!--
		End of Confirm Delete
   	-->
		</div>

</html>

<?php

}
?>