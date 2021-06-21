<?php
session_start();
date_default_timezone_set('Pacific/Auckland');
$_SESSION['x'] = 1;

if (! isset($_SESSION['myusername'])) {
    session_destroy();
    header('Refresh:0; url=index.php');
} else {
    require_once('libs/api/classes/database.class.php');
    $db = new Database();

    $due = date('d/m/Y', strtotime('+7 days'));
    $now_ = date('Ymd');

    $leadGens = $db->execute($db->prepare('SELECT * FROM leadgen_tbl ORDER BY name'));
    $advisers = $db->execute($db->prepare('SELECT * FROM adviser_tbl ORDER BY name')); ?>
	<html>
	<head>
		<!--nav bar-->
		<?php include 'partials/nav_bar.html'; ?>
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
				var lp = $('.leadgen_selectpicker').selectpicker();
				var sp = $('.adviser_selectpicker').selectpicker();
				var ss = $('.source_selectpicker').selectpicker();

				lp.on("changed.bs.select", function(e, clickedIndex, newValue, oldValue) {
					var selectedD = $(this).find('option').eq(clickedIndex).val();
					var arr = lp.val();
					if (arr.length == 0) {
						$('.leadgen_selectpicker').parent().find('.filter-option-inner-inner').html('No lead generator selected');
					} else {
						$('.leadgen_selectpicker').parent().find('.filter-option-inner-inner').html('Selected ' + arr.length + ' lead generators');
					}
				});

				ss.on("changed.bs.select", function(e, clickedIndex, newValue, oldValue) {
					var leadby = $(this).val();
					var formData = {
						leadby: leadby,
					}

					$.ajax({
						dataType: 'json',
						type: 'POST',
						data: formData,
						url: "fetch_leadgen.php",
						success: function(e) {
							var rows = $.parseJSON(JSON.stringify(e));
							$('#leadgens option').remove();
							$('#leadgens').append('<option value="" disabled hidden selected> Select Lead Generator </option>');

							$.each(rows, function(i, d) {
								$('#leadgens').append('<option value="' + d.id + '"> ' + d.name + ' </option>');
							});

							$('#leadgens').selectpicker("refresh");
						},
						error: function(x) {
							console.log(x);
						}
					});
				});

				sp.on("changed.bs.select", function(e, clickedIndex, newValue, oldValue) {
					var selectedD = $(this).find('option').eq(clickedIndex).val();
					var arr = sp.val();

					if (arr.length == 0) {
						$('.adviser_selectpicker').parent().find('.filter-option-inner-inner').html('No adviser selected');
					} else {
						$('.adviser_selectpicker').parent().find('.filter-option-inner-inner').html('Selected ' + arr.length + ' advisers');
					}
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

				$('#filterby').on('change', function(e) {
					var filter = $(this).val();

					if (filter != "none") {
						document.getElementById("filterDiv").style.display = "block";

						if (filter == "city"){
							document.getElementById("filterVariable").innerHTML = "cities";
						}else if (filter == "zipcode"){
							document.getElementById("filterVariable").innerHTML = "zipcodes";
						}
					} else {
						document.getElementById("filterDiv").style.display = "none";
					}
				});

				$('#filterDate').on('change', function(e){
					var val = $(this).children('option:selected').val();

					switch(val) {
						case 'From - To':
							$('#filteredDate').empty();
							$('#filteredDate').append($(`
								<div class="row">
									<div class='col-sm-4'></div>
									<div class='col-sm-2 '>Date From:
										<input name="date_from" class="form-control" autocomplete="off" type="text" id="datepicker" />
									</div>
									<div class='col-sm-2'>Date To:
										<input name="date_to" class="form-control" autocomplete="off" type="text" id="datepicker2" />
									</div>
								</div>`).hide().fadeIn(500));

							$("#datepicker").datepicker({
								dateFormat: 'dd/mm/yy'
							});

							$("#datepicker2").datepicker({
								dateFormat: 'dd/mm/yy'
							});

							break;
						case 'Specific Month':
							$('#filteredDate').empty();

							$('#filteredDate').append($(`
								<div class="row">
									<div class='col-sm-3'></div>
									<div class='col-sm-2 '>Specific Month:
										<input name="specific_month" class="form-control" autocomplete="off" type="text" id="specificMonth" />
									</div>
									<div class='col-sm-2 '>Year From:
										<input name="year_from" class="form-control" autocomplete="off" type="text" id="yearFrom" />
									</div>
									<div class='col-sm-2'>Year To:
										<input name="year_to" class="form-control" autocomplete="off" type="text" id="yearTo" />
									</div>
								</div>`).hide().fadeIn(500));

							datepicker_initializer_year("#yearFrom");
							datepicker_initializer_year("#yearTo");
							datepicker_initializer_month("#specificMonth");

							break;
						default:
							break;
					}
				});

				//Create Button
				$('#create').on('click', function(e) {
					e.preventDefault();

					var customErrorLog = "";
					var date_from = $("#datepicker").val();
					var date_to = $("#datepicker2").val();
					var year_from = $("#yearFrom").val();
					var year_to = $("#yearTo").val();
					var specific_month = $("#specificMonth").val();
					var date_now = $("#source_date_now").val();
					var clienttype = $("#clienttype").val();
					var leadgens = JSON.stringify($("#leadgens").val());
					var advisers = JSON.stringify($("#advisers").val());
					var filterby = $("#filterby").val();
					var filterdata = $("#filterdata").val();
					var source = $("#source").val();

					if (clienttype == "") {
						customErrorLog = "Please pick a Client Type.";
					}

					var formData = {
						clienttype: clienttype,
						lead_gens: leadgens,
						advisers: advisers,
						filterby: filterby,
						source: source,
						filterdata: filterdata,
						date_from: date_from,
						date_to: date_to,
						date_now: date_now,
						year_from,
						year_to,
						specific_month,
					};

					$.ajax({
						dataType: 'json',
						type: 'POST',
						data: formData,
						url: "save_client_database_report.php",
						success: function(e) {
							$.confirm({
								title: 'Report Created Successfully',
								content: "Press \'ok\' to download the file and close this window.",
								buttons: {
									Ok: function() {
										console.log(e);
										window.open('client_database?reference_number=' + e.reference_number, '_blank');
										//window.location='create_client_data_report.php';
									},
								}
							});
						},
						error: function(x) {
							$.confirm({
								title: 'Creating Report Unsuccessful',
								content: customErrorLog,
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

			function datepicker_initializer_year($id) {
				$($id).datepicker({
					dateFormat: 'yy',
	        		changeYear: true,
	        		showButtonPanel: true,
	        		onClose: function(dateText, inst) {
						var year = $("#ui-datepicker-div .ui-datepicker-year :selected").val();

						$(this).val($.datepicker.formatDate('yy', new Date(year)));
	        		}
				});

				$($id).focus(function () {
					$(".ui-datepicker-calendar").hide();
					$(".ui-datepicker-prev").hide();
					$(".ui-datepicker-next").hide();
					$(".ui-datepicker-month").hide();
					$("#ui-datepicker-div").position({
						my: "center top",
						at: "center bottom",
						of: $(this)
					});
				});
			}

			function datepicker_initializer_month($id){
				$($id).datepicker({
					dateFormat: 'MM',
					changeMonth: true,
					showButtonPanel: true,
					onClose: function(dateText, inst) {
						var month = $("#ui-datepicker-div .ui-datepicker-month :selected").val();

						$(this).val($.datepicker.formatDate('MM', new Date(1, month, 1)));
					}
				});

				$($id).focus(function () {
					$(".ui-datepicker-calendar").hide();
					$(".ui-datepicker-prev").hide();
					$(".ui-datepicker-next").hide();
					$(".ui-datepicker-year").hide();
					$("#ui-datepicker-div").position({
						my: "center top",
						at: "center bottom",
						of: $(this)
					});
				});
			}

			function filter_array(test_array) {
				var index = -1,
					arr_length = test_array ? test_array.length : 0,
					resIndex = -1,
					result = [];

				while (++index < arr_length) {
					var value = test_array[index];

					if (value) {
						result[++resIndex] = value;
					}
				}

				return result;
			}
		</script>
	</head>
	<body>
		<div align="center">
			<div class="jumbotron">
				<h2 class="slide">Generate Client Data</h2>
			</div>
			<form method="POST" action="excelOP1.php" autocomplete="off" class="margined" id="reportForm">
				<div>
					<div class="row">
						<div class='col-sm-2 center'>
							<label style="width: 100%" ;>Type of Clients: *
								<select name="clienttype" class="form-control" id="clienttype" data-actions-box="true" required>
									<option value="" hidden>Choose one</option>
									<option>Unassigned Clients</option>
									<option>Clients with No Policies</option>
									<option>Clients with Submissions</option>
									<option>Clients with Enforced Policies</option>
									<option>Cancellations List</option>
									<option>All Clients</option>
								</select>
							</label>
						</div>
					</div>
					<div class="row">
						<div class="col-sm-3"></div>
						<div class='col-sm-2'>
							<label style="width: 100%" ;>Source:
								<input class="form-control" value="<?php echo $now_; ?>" readonly='' autocomplete="off" type="hidden" name="date_now" id="source_date_now" />
								<select name="source" class="form-control source_selectpicker" id="source" data-actions-box="true">
									<option value="">Don't Filter</option>
									<option>Telemarketer</option>
									<option>Face-to-Face Marketer</option>
								</select>
							</label>
						</div>
						<div class='col-sm-2'>
							<label style="width: 100%" ;>Lead Generator(s):
								<select name="leadgens" class="form-control leadgen_selectpicker" id="leadgens" data-actions-box="true" multiple="multiple">
									<?php
                                    while ($leadGen = $leadGens->fetch_assoc()) {
                                        ?>
										<option value="<?php echo $leadGen['id']; ?>"><?php echo $leadGen['name']; ?></option>
										<?php
                                    } ?>
								</select>
							</label>
						</div>
						<div class='col-sm-2'>
							<label style="width: 100%" ;>Adviser(s):
								<input class="form-control" value="<?php echo $now_; ?>" readonly='' autocomplete="off" type="hidden" name="date_now" id="adviser_date_now" />
								<select name="advisers" class="form-control adviser_selectpicker" id="advisers" data-actions-box="true" multiple="multiple">
									<optgroup label="Lead Generators">
										<?php
                                        while ($adviser = $advisers->fetch_assoc()) {
                                            ?>
											<option value="<?php echo $adviser['id']; ?>"><?php echo $adviser['name']; ?></option>
											<?php
                                        } ?>
									</optgroup>
								</select>
							</label>
						</div>
					</div>
					<div class="row">
						<div class="col-lg-12">&nbsp;</div>
					</div>
					<div class="row">
						<div class="col-sm-4"></div>
						<div class="col-sm-4 my-3">Filter Date By:
							<select id="filterDate" class="form-control">
								<option value="" selected disabled>Choose an option</option>
								<option value="From - To">From - To</option>
								<option value="Specific Month">Specific Month</option>
							</select>
						</div>
					</div>
					<div id="filteredDate"></div>
					<div class="row">
						<div class='col-sm-2 center'>
							<label style="width: 100%" ;>Filter By:
								<select name="filterby" class="form-control" id="filterby" data-actions-box="true">
									<option value="none">None selected</option>
									<option value="specificmonth">Specific Month</option>
									<option value="city">City</option>
									<option value="zipcode">Zip Code</option>
								</select>
							</label>
						</div>
						<div class='col-sm-8 center' style="display: none;" id="filterDiv"><br>
							<label style="width: 100%" ;>Filter Data:
								<span style="color:#999999">
									Enter each of the <span id='filterVariable'>Data</span> that you want to be used as filters separated by a comma. <br>
									Ex.<span style="color:red; margin-top: 0px;"> " Filter1, Filter2, Filter3 " <br> </span>
								</span>
								<div class="col-sm-4 center">
									<textarea class="form-control" rows="3" id="filterdata" name="filterdata" /></textarea>
								</div>
							</label>
						</div>
					</div>
					<div class="row">
						<div class='col-sm-2 center'>
							<input name="enter" type="submit" id='create' value="Create Report" style='margin-top: 30px;width: 100%;' class="btn btn-danger center" />
						</div>
					</div>
				</div>
			</form>
		</div>
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