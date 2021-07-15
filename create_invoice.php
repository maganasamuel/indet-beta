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
				$(".datepicker").datepicker({
					dateFormat: 'dd/mm/yy'
				});

				$("#datefrom").datepicker({
					dateFormat: 'dd/mm/yy',
					beforeShowDay: function(date) {

						if (date.getDate() == 15 || date.getDate() == 1) {

							return [true, '', "Highlighted"];
						}
						return [false, ''];
					}
				});

				$("#datefrom").on('change', function() {
					var $this = $(this).val();
					var m = $this.substr(3, 2);
					var month = m - 1;
					var year = $this.substr(6, 4);
					var ifday = $this.substr(0, 2);

					var lastday = function(y, m) {
						return new Date(y, m + 1, 0).getDate();
					}
					if (ifday == 16) {
						var ld = lastday(year, month) + '/' + m + '/' + year;
						$('#dateuntil').val(ld);
					} else if (ifday == 1) {
						var ld = '15' + '/' + m + '/' + year;
						$('#dateuntil').val(ld);
					} else {

					}



				});


				$('#mydate').prop('disabled', true);
				var x = 1;
				var client = "#client";
				var sel = "#sel";
				var txt = "#txt";
				var myadviser = "#myadviser";
				var openingbal = "#openingbal";
				var mymonth = "#mymonth";
				var mydate = "#mydate";
				var myyear = "#myyear";
				$('#desc').select2();
				$('#desc').change(function() {
					var arr = [];
					$(this).parent().find('.select2-selection__choice').each(function() {
						//  console.log($(this).attr('title'));
						arr.push($(this).attr('title'));

					});



					if (jQuery.inArray("others", arr) != -1) {
						//$('#other_text').show();
						$('#other_value').show();
					} else {
						//$('#other_text').hide().val('');
						$('#other_value').hide().val('');
					}

				});

				$('#other_text').keyup(function() {
					$("#others").val($(this).val());

				});




				$('#create').prop('disabled', false);

				$('#create').on('click', function(e) {
					e.preventDefault();

					var adv_name = $("#adv_name").val();
					var adviser_id = $("#myadviser").val();
					var date_from = $("#datefrom").val();
					var invoice_date = $("#invoice_date").val();
					var desc = JSON.stringify($("#desc").val());

					var until = $("#dateuntil").val();
					var due_date = $("#due_date").val();
					var invoice_num = $("#invoice_num").val();
					var other_value = $("#other_value").val();

					console.log(date_from + ":" + until);
					$.ajax({
						dataType: 'json',
						type: 'POST',
						data: {
							adv_name: adv_name,
							adviser_id,
							adviser_id,
							date_from: date_from,
							invoice_date: invoice_date,
							desc: desc,
							until: until,
							due_date: due_date,
							invoice_num: invoice_num,
							other_value: other_value
						},
						url: "output.php",
						success: function(e) {
							console.log(desc);
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
									url: "save_invoice.php",
									beforeSend: function() {

									},
									success: function(x) {
										console.log(x);
										$.confirm({
											title: 'Success!',
											content: 'You have successfully created an invoice ',
											buttons: {
												Ok: function() {
													console.log(x);
													window.location = 'create_invoice.php';
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

				$('#myadviser').change(function() {
					$('#adv_name').val($(this).children("option:selected").text());
					$('#create').prop('disabled', false);
				});


				$("#numclient").keyup(function() {
					test = $("#numclient").val();
					for (x = 0; x < 100; x++) {
						rencom = "#rencom".concat(x);
						rengst = "#rengst".concat(x);


						txtconcat = txt.concat(x);
						clientconcat = client.concat(x);
						$(clientconcat).hide();

						if (test < x) {
							var gettr = $(txtconcat).closest("tr");
							$(gettr).find(':text').val("");
							$(gettr).find('select').val("");


							$(rencom).hide();
							$(rengst).hide();
						} else {

						}


					}

					for (x = 1; x <= test; x++) {
						txtconcat = txt.concat(x)
						clientconcat = client.concat(x);
						$(clientconcat).show();
					}
				});



				$(myadviser).change(function() {

					var selected = $(myadviser).find(":selected").text();
					var ad_id = $(myadviser).val();
					$.ajax({
						url: 'getclosing.php',
						data: 'adviser=' + selected,
						dataType: 'json',
						success: function(data) {
							$(openingbal).val(data['closing_bal']);


						}
					})

					$.ajax({
						url: 'searchclient.php',
						data: 'adviser=' + ad_id,
						type: 'post',
						beforeSend: function() {
							$('.getclient').html('Loading...');
						},
						success: function(data) {

							$('.getclient').html(data);


						}
					})

				});





				$(myyear).change(function() {
					$(mydate).show();
					var month = $(mymonth).val();
					var date = $(mydate).val();
					var year = $(myyear).val();
					var adviserid = $(myadviser).val();


					$.ajax({
						url: 'getendday.php',
						data: {
							month: month,
							date: date,
							year: year,
							adviserid: adviserid
						},
						success: function(data) {
							if (data == 'wrong') {
								$('#mydate').prop('disabled', true);
								$.alert({
									title: 'Alert!',
									content: 'Existing Date in Summary.',
								});
								$(mymonth).val('');
								$(mydate).val('');

							} else {

								$("#nextdate").html(data);
							}
						}

					})


					$.ajax({
						url: 'getclosing.php',
						dataType: 'json',
						data: {
							adviser: adviserid,
							month: month,
							date: date,
							year: year
						},
						success: function(data) {

							$(openingbal).val(data['closing_bal']);
						}

					})



				});


				$(mydate).change(function() {
					$(mydate).show();
					var month = $(mymonth).val();
					var date = $(mydate).val();
					var year = $(myyear).val();
					var adviserid = $(myadviser).val();

					$.ajax({
						url: 'getendday.php',
						data: {
							month: month,
							date: date,
							year: year,
							adviserid: adviserid
						},
						success: function(data) {
							if (data == 'wrong') {
								$('#mydate').prop('disabled', true);
								$.alert({
									title: 'Alert!',
									content: 'Existing Date in Summary.',
								});
								$(mymonth).val('');
								$(mydate).val('');
							} else {
								$("#nextdate").html(data);
							}
						}
					})
					$.ajax({
						url: 'getclosing.php',
						dataType: 'json',
						data: {
							adviser: adviserid,
							month: month,
							date: date,
							year: year
						},
						success: function(data) {
							$(openingbal).val(data['closing_bal']);
						}
					})
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
				<h2 class="slide">Create Invoice</h2>
			</div>
			<!--label end-->

			<?php
			require "database.php";
			?>

			<form method="POST" action="output.php" autocomplete="off" class="margined">
				<table align="center" bgcolor="ededed" cellpadding="5px" onload="form1.reset();">

					<div class='row'>

						<div class='col-sm-2'></div>
						<div class='col-sm-2'>
							<label>Adviser
								<div class="input-group">
									<span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
									<select name="adviser_id" class="form-control" id="myadviser" required />
									<option value="" disabled selected>Select Adviser</option>
									<?php
									$query = "SELECT id,name FROM adviser_tbl ORDER BY name asc";
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
									<input class="form-control datepicker" autocomplete="off" type="text" id="dateuntil" name="until" /></div>
							</label>
						</div>
						<div class='col-sm-2'>
							<label>Description
								<div class="input-group">
									<span class="input-group-addon">
										<i class="fa fa-file" aria-hidden="divue"></i></span>
									<select name="desc[]" class="form-control" id="desc" multiple="multiple" required />
									<option value='charged'>leads charged</option>
									<option value="issued">leads issued</option>
									<option value="Others" id='others'>others</option>
									</select>
									<textarea placeholder="Enter Description" id='other_text' rows="3" style="display:none; "></textarea>
									<input class='form-control' type="number" id='other_value' value='0' name='other_value' placeholder="Enter value " style="display:none;">

								</div>
							</label>
						</div>


					</div>




		</div>

		<div class='row'>
			<?php
			date_default_timezone_set('Pacific/Auckland');
			$due = date('d/m/Y', strtotime('+7 days'));
			$now_ = date('d/m/Y');

			?>
			<div class='col-sm-2'></div>
			<div class='col-sm-2'>
				<label style="width: 100% !important;">Invoice Date
					<div class="input-group">
						<span class="input-group-addon">
							<i class="fa fa-calendar" aria-hidden="divue"></i></span>
						<input class="form-control" value="<?= $now_; ?>" readonly='' autocomplete="off" type="text" name="invoice_date" id="invoice_date" /></div>
				</label>
			</div>
			<div class='col-sm-2'>
				<label style="width: 100% !important;">Due Date
					<div class="input-group">
						<span class="input-group-addon">


							<i class="fa fa-calendar" aria-hidden="divue"></i></span>
						<input class="form-control" autocomplete="off" type="text" value="<?= $due; ?>" name="due_date" id='due_date' readonly="true" /></div>
				</label>
			</div>

			<?php
			$invoice_num = 'EI' . date('Y') . '0001';
			$init_check_flag_query = "SELECT EXISTS(SELECT 1 FROM (SELECT number AS invoice_number FROM invoices UNION (SELECT invoice_number FROM customized_invoices)) AS invoice_tbl WHERE invoice_number LIKE '$invoice_num%') AS flag";
			$init_check_flag = mysqli_query($con, $init_check_flag_query) or die('Could not look up user information; ' . mysqli_error($con));
			$init_check_flag = mysqli_fetch_array($init_check_flag);
			$init_check_flag = isset($init_check_flag['flag']) ? $init_check_flag['flag'] : 0;

			if($init_check_flag != '0') {
				$query = "SELECT * FROM (SELECT number AS invoice_number, CAST(date_created AS CHAR(50)) AS date_created  FROM invoices UNION (SELECT invoice_number, date_created FROM customized_invoices)) AS invoice_tbl WHERE invoice_number = '$invoice_num'";
			    $result = mysqli_query($con, $query);
			    $result = mysqli_fetch_array($result);

			    $result_date_created = isset($result['date_created']) ? $result['date_created'] : date("Ymd");

			    $invoice_num = 'EI' . date("Y");
			    $count_query = "SELECT * FROM (SELECT number AS invoice_number, CAST(date_created AS CHAR(50)) AS date_created FROM invoices UNION (SELECT invoice_number, date_created FROM customized_invoices)) AS invoice_tbl WHERE invoice_number LIKE '$invoice_num%' AND date_created >= '$result_date_created' ORDER BY invoice_number DESC";
			} else {
				$count_query = "SELECT * FROM (SELECT number AS invoice_number, CAST(date_created AS CHAR(50)) AS date_created FROM invoices UNION (SELECT invoice_number, date_created FROM customized_invoices)) AS invoice_tbl WHERE invoice_number LIKE '$invoice_num%' ORDER BY invoice_number DESC";
			}

			
			$searchsum = mysqli_query($con, $count_query) or die('Could not look up user information; ' . mysqli_error($con));
			//echo $count_query;
			$rows = mysqli_fetch_array($searchsum);
			$rows_count = isset($rows['invoice_number']) ? substr($rows['invoice_number'], -3) : 0;
			$rows_count++;

			if($init_check_flag == '0') {
				$invoice_num = 'EI' . date("Y");
				$rows_count = 1;
			} 
				

			switch ($rows_count) {
				case ($rows_count < 10):
					$invoice_num .= '000' . $rows_count;

					break;
				case ($rows_count < 100 && $rows_count >= 10):
					$invoice_num .= '00' . $rows_count;

					break;

				case ($rows_count < 1000 && $rows_count >= 10):
					$invoice_num .= '000' . $rows_count;

					break;
				case ($rows_count >= 1000):
					$invoice_num .= $rows_count;

					break;

				default:

					break;
			}


			?>
			<div class='col-sm-2'>
				<label>Invoice Number
					<div class="input-group">
						<span class="input-group-addon"><i class="fa fa-file"></i></span>
						<input class="form-control" id="invoice_num" value="<?= $invoice_num; ?>" type="text" name="invoice_num" readonly="true" />
					</div>
				</label>
			</div>
		</div>

		<div class="row">
			<div class="col-sm-2 center">
				<input name="enter" type="submit" id='create' value="Create Invoice" style='margin-top: 30px;width: 100%;' class="btn btn-danger center" />
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
							<h2 class="modal-title" style="float: left;">Invoice Preview</h2>
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