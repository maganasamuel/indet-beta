<?php
session_start();
date_default_timezone_set('Pacific/Auckland');
$_SESSION['x'] = 1;

if (! isset($_SESSION['myusername'])) {
    session_destroy();
    header('Refresh:0; url=index.php');

    return;
}

require_once('libs/api/classes/database.class.php');
$db = new Database();
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<?php include 'partials/nav_bar.html'; ?>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>INDENT - Issued Policies List</title>

	<link id="favicon" rel="icon" href="Logo_ImageOnly.png" type="image/png" sizes="16x16">
	<link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">

	<link rel="stylesheet" href="styles.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.13.3/css/selectize.bootstrap3.min.css" integrity="sha512-MNbWZRRuTPBahfBZBeihNr9vTJJnggW3yw+/wC3Ev1w6Z8ioesQYMS1MtlHgjSOEKBpIlx43GeyLM2QGSIzBDg==" crossorigin="anonymous" referrerpolicy="no-referrer" />

	<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js" integrity="sha512-qTXRIMyZIFb8iQcfjXWCO8+M5Tbc38Qi5WzdPOYZHIlZpzBHG3L3by84BBBOiRGiEb7KKtAOAs5qYdUiZiQNNQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.13.3/js/standalone/selectize.min.js" integrity="sha512-pF+DNRwavWMukUv/LyzDyDMn8U2uvqYQdJN0Zvilr6DDo/56xPDZdDoyPDYZRSL4aOKO/FGKXTpzDyQJ8je8Qw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

	<style>
		.mx-auto{
			margin-left: auto;
			margin-right: auto;
		}
	</style>
</head>
<body>
	<div class="jumbotron">
		<h2 class="slide text-center">Issued Policies List</h2>
	</div>
	<div class="margined">
		<div class="row">
			<div class="col-xs-4 col-xs-offset-4">
				<form method="POST" id="frmIssuedPoliciesList">
					<div class="form-group col-xs-12">
						<label for="filter_by">Period</label>
						<select id="filter_by" name="filter_by" class="form-control">
							<option value="">-</option>
							<option value="date">Date</option>
							<option value="month">Month</option>
							<option value="year">Year</option>
						</select>
						<p class="input-error text-danger small hidden">&nbsp;</p>
					</div>

					<div class="dateFilter hidden">
						<div class="form-group col-xs-6">
							<label for="date_from">Date From</label>
							<input type="text" id="date_from" name="date_from" class="form-control" />
							<p class="input-error text-danger small hidden">&nbsp;</p>
						</div>
						<div class="form-group col-xs-6">
							<label for="date_to">Date To</label>
							<input type="text" id="date_to" name="date_to" class="form-control" />
							<p class="input-error text-danger small hidden">&nbsp;</p>
						</div>
					</div>

					<div class="form-group col-xs-12 monthFilter hidden">
						<label for="month">Month</label>
						<select name="month" id="month" class="form-control">
							<option value="">-</option>
							<?php
                            foreach (range(1, 12) as $month) {
                                ?>
								<option value="<?php echo $month; ?>">
									<?php
                                        echo date('F', strtotime(date('Y-') . str_pad($month, 2, '0', STR_PAD_LEFT) . '-01')); ?>
								</option>
								<?php
                            }
                            ?>
						</select>
						<p class="input-error text-danger small hidden">&nbsp;</p>
					</div>

					<div class="form-group col-xs-12 yearFilter hidden">
						<label for="year">Year</label>
						<input type="number" id="year" name="year" min="1000" max="9999" class="form-control" />
						<p class="input-error text-danger small hidden">&nbsp;</p>
					</div>

					<div class="form-group col-xs-12">
						<label for="adviser">Adviser</label>
						<select type="text" id="adviser" name="adviser" class="form-control" multiple></select>
					</div>

					<div class="col-xs-12">
						<button type="submit" class="btn btn-danger">Create Report</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	<script type="text/javascript">
		$(function(){
			function hideFilters(){
				['.dateFilter', '.monthFilter', '.yearFilter'].forEach((selector) => {
					$(selector).addClass('hidden');
				});
			}

			function inputError(selector, message){
				$(selector).parent().find('.input-error').removeClass('hidden').text(message);

				$(selector).focus();
			}

			$('#date_from').datepicker({
				dateFormat: 'dd/mm/yy'
			});

			$('#date_to').datepicker({
				dateFormat: 'dd/mm/yy'
			});

			$.ajax({
				url: '/libs/api/adviser_api.php',
				type: 'POST',
				data: {
					action: 'get_advisers'
				},	
				error: function(){
					inputError('#adviser', 'Could not fetch advisers. Please try again.');
				},
				success: function(response){
					let advisers = JSON.parse(response);
					
					advisers.forEach((adviser) => {
						$('#adviser').append(
							$('<option>', {
								value: adviser.id,
								text: adviser.name
							})
						);
					});

					$('#adviser').selectize();
				}
			});

			

			$('#filter_by').focus();

			$('#filter_by').on('input', function(){
				let filters = {
					date: '.dateFilter',
					month: '.monthFilter',
					year: '.yearFilter',
				};

				hideFilters();

				$(filters[$(this).val()]).removeClass('hidden');
			});


			$('#frmIssuedPoliciesList').on('submit', function(event){
				event.preventDefault();

				$('.input-error').text('').addClass('hidden');

				if(!$('#filter_by').val().trim()){
					inputError('#filter_by', 'Please choose a period.');

					return false;
				}

				if($('#filter_by').val() == 'date'){
					if(!$('#date_from').val().trim()){
						inputError('#date_from', 'Please provide a value.');

						return false;
					}

					let dateFrom = moment($('#date_from').val(), 'DD/MM/YYYY', true);

					if(!dateFrom.isValid()){
						inputError('#date_from', 'Please provide a valid date value.');

						return false;
					}

					if(!$('#date_to').val().trim()){
						inputError('#date_to', 'Please provide a value.');

						return false;
					}

					let dateTo = moment($('#date_to').val(), 'DD/MM/YYYY', true);

					if(!dateTo.isValid()){
						inputError('#date_to', 'Please provide a valid date value.');

						return false;
					}

					if(dateFrom > dateTo){
						inputError('#date_from', 'Please make sure that value is less than or equal to Date To.');

						return false;
					}
				}

				if($('#filter_by').val() == 'month' && !$('#month').val()){
					inputError('#month', 'Please provide a value.');

					return false;
				}

				if($('#filter_by').val() == 'year' && !$('#year').val()){
					inputError('#year', 'Please provide a value.');

					return false;
				}

				let url = 'issued_policies_report_spreadsheet?filter_by=';

				let filterBy = $('#filter_by').val();

				url += filterBy + '&value=';

				let value = '';

				if(filterBy == 'date'){
					dateFrom = moment($('#date_from').val(), 'DD/MM/YYYY', true);
					dateTo = moment($('#date_to').val(), 'DD/MM/YYYY', true);

					value = dateFrom.format('YYYYMMDD') + '|' + dateTo.format('YYYYMMDD');
				}else if(filterBy == 'month'){
					value = $('#month').val();
				}else if(filterBy == 'year'){
					value = $('#year').val();
				}

				let advisers = $('#adviser').val().join(',');
				
				url += value + '&advisers=' + advisers;

				window.open(url, '_blank');
			});
		});
	</script>
</body>
</html>
