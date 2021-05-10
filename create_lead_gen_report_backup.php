<?php
session_start();
date_default_timezone_set('Pacific/Auckland');
$_SESSION["x"]=1;
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



<?php require "database.php";?>
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
	$(function(){


	$( ".datepicker" ).datepicker({ dateFormat: 'dd/mm/yy' });
		

	$('#required_leads_div').on("keyup change", ".tier", function(){
        var api = $(this).val();
        api = api.replace(/[^0-9]/g, "");
        console.log(api);
        $(this).val(api);
    });

	$('#me').dataTable({
		"order": [[ 7, "desc" ]],
		"columnDefs": [ {
			"targets": [9,10],
			"orderable": false
		} ]
	});


	$('#datepicker').datepicker('disable');
	$('#datepicker2').datepicker('disable');

	$("#report_type").on('change',function(e){
		var type = $(this).val();
		ResetInputs();
		if(type=="Quarterly"){
			$("#quarterly").slideDown();
			$("#non_quarterly").slideDown();
			
			$("#datepicker").prop("readonly", true);
			$("#datepicker2").prop("readonly", true);
			$('#datepicker').datepicker('disable');
			$('#datepicker2').datepicker('disable');
		}
		else if(type=="Weekly"){
			$("#datepicker").prop("readonly", true);
			$("#datepicker2").prop("readonly", true);
			$('#datepicker').datepicker('disable');
			$('#datepicker2').datepicker('disable');
			$("#non_quarterly").slideDown();
			$("#weekly_div").slideDown();
		}
		else if(type=="Bi-Monthly"){
			$("#datepicker").prop("readonly", true);
			$("#datepicker2").prop("readonly", true);
			$('#datepicker').datepicker('disable');
			$('#datepicker2').datepicker('disable');
			$("#non_quarterly").slideDown();
			$("#bi-monthly_div").slideDown();
		}
		else if(type=="Monthly"){
			$("#datepicker").prop("readonly", true);
			$("#datepicker2").prop("readonly", true);
			$('#datepicker').datepicker('disable');
			$('#datepicker2').datepicker('disable');
			$("#non_quarterly").slideDown();
			$("#monthly_div").slideDown();
		}
		else if(type=="Annual"){
			$("#annual_div").slideDown();
		}
		else if(type=="Specified"){
			$("#specified-start").slideDown();
			$("#specified-end").slideDown();

			
			$("#datepicker").prop("readonly", true);
			$("#datepicker2").prop("readonly", true);
			$('#datepicker').datepicker('disable');
			$('#datepicker2').datepicker('disable');
			$("#non_quarterly").slideDown();
		}
		else {
			$("#non_quarterly").slideDown();
		}
	});

	function ResetInputs(){
		//animate
		$("#specified-start").slideUp();
		$("#specified-end").slideUp();
		$("#quarter_div").slideUp();

		$("#quarterly").slideUp();
		$("#non_quarterly").slideUp();

		$("#start_month_div").slideUp();
		$("#end_month_div").slideUp();

		$("#weekly_div").slideUp();
		$("#bi-monthly_div").slideUp();
		$("#monthly_div").slideUp();

		$("#annual_div").slideUp();

		//inputs
		$("#datepicker").val("");
		$("#datepicker2").val("");

		$("#start_month").val("");
		$("#start-year").val("");

		$("#end_month").val("");
		$("#end-year").val("");
		$("#start_month").val("");
		$("#start-year").val("");


		$("#quarter-year").val("");
		$("#week_date").val("");
		$("#bi-month_date").val("");
		$("#annual-year").val("");

		//reset readonly
		$("#datepicker").prop("readonly", false);
		$("#datepicker2").prop("readonly", false);

		$('#datepicker').datepicker('enable');
		$('#datepicker2').datepicker('enable');
	}

	$("#week_date").on("keyup change", function(e){
        var week_date = $(this).val();
        var date_input = week_date.split("/");
        var w_date = new Date(date_input[1] + "/" + date_input[0] + "/" + date_input[2]);
        var monday = getMonday(w_date);
				var sunday = new Date(monday.valueOf());
				sunday.setDate(sunday.getDate() + 6);

        $("#datepicker").val( getTwoDigitDateFormat(monday.getDate()) + "/" + getTwoDigitDateFormat(monday.getMonth() + 1) + "/" + monday.getFullYear());
        $("#datepicker2").val( getTwoDigitDateFormat(sunday.getDate()) + "/" + getTwoDigitDateFormat(sunday.getMonth() + 1) + "/" + sunday.getFullYear());
	});

	$("#bi-month_date").on("keyup change", function(e){
        var bi_month_date = $(this).val();
        var date_input = bi_month_date.split("/");
				var day = date_input[0];
				var d1 = "";
				var d2 = "";
				if(day<=15){
					d1 = "01";
					d2 = "15";	
				}
				else{
					d1 = "16";
					
					var d = new Date(date_input[2], date_input[1] + 1, 0);
					d2 = getTwoDigitDateFormat(d.getDate());
				}
				$("#datepicker").val( String(d1) + "/" + date_input[1] + "/" + date_input[2]);
        $("#datepicker2").val( String(d2) + "/" + date_input[1] + "/" + date_input[2]);
      });

			
	$("#month_date").on("keyup change", function(e){
        var month_date = $(this).val();
        var date_input = month_date.split("/");
				var day = date_input[0];
				var d = new Date(date_input[2], date_input[1], 0);
				var lastDay = getTwoDigitDateFormat(d.getDate());				
				console.log("Last Day: " + date_input[1]);

				$("#datepicker").val( "01/" + date_input[1] + "/" + date_input[2]);
        $("#datepicker2").val( String(lastDay) + "/" + date_input[1] + "/" + date_input[2]);
		});
		
	$("#start-specified").on("keyup change", function(e){
				var input_date = $(this).val();  
				console.log(input_date);     
				$("#datepicker").val(input_date);
		});
		
	$("#end-specified").on("keyup change", function(e){
        var input_date = $(this).val();
        $("#datepicker2").val(input_date);
    });

	function getMonday( date ) {
	    var day = date.getDay() || 7;  
	    if( day !== 1 ) 
	        date.setHours(-24 * (day - 1)); 
	    return date;
	}

	$("#quarter-year").on("keyup change", function(e){
        var year = $(this).val();
        year = year.replace(/[^0-9]/g, "");
				$(this).val(year);


				

        if(year.length>3){
					
					$("#quarter_div").slideDown();
					$("#quarter").val("1st Quarter");
					//get first day of the year
					var first_day_of_year = "01/01/" + year;

					var date_input = first_day_of_year.split("/");
					var first_day = new Date(date_input[1] + "/" + date_input[0] + "/" + date_input[2]);
					var first_monday = getMonday(first_day);
					var last_day = new Date(first_monday);
					last_day.setDate(first_monday.getDate() + 90);					
						$("#datepicker").val(getTwoDigitDateFormat(first_monday.getDate()) + "/" + getTwoDigitDateFormat(parseInt(first_monday.getMonth()) + 1) + "/" + first_monday.getFullYear());
						$("#datepicker2").val(getTwoDigitDateFormat(last_day.getDate()) + "/" + getTwoDigitDateFormat(parseInt(last_day.getMonth()) + 1) + "/" + last_day.getFullYear());
        }
        else{
        	$("#quarter_div").slideUp();
					$("#datepicker").val("");
					$("#datepicker2").val("");
        }
	});

	$("#annual-year").on("keyup change", function(e){
        var year = $(this).val();
        year = year.replace(/[^0-9]/g, "");
        $(this).val(year);
        if(year.length>3){
			$("#datepicker").val("01/01/" + year);
			$("#datepicker2").val("31/12/" + year);
        }
        else{
			$("#datepicker").val("");
			$("#datepicker2").val("");
        }
	});


	$("#start_month").on('change',function(e){
		var month = $(this).val();
		var year = $("#start-year").val();
		$("#datepicker").val("01/" + month + "/" + year);
	});

	$("#end_month").on('change',function(e){
		var month = $(this).val();
		var year = $("#end-year").val();
		var lastDayOfMonth = new Date(year, month, 0);

		$("#datepicker2").val(getTwoDigitDateFormat(lastDayOfMonth.getDate()) + "/" + month + "/" + year);
	});
	
function getTwoDigitDateFormat(monthOrDate) {
  return (monthOrDate < 10) ? '0' + monthOrDate : '' + monthOrDate;
}

	$("#quarter").on('change',function(e){
		var quarter = $(this).val();
		var year = $("#quarter-year").val();

		//get first day of the year
		var first_day_of_year = "01/01/" + year;

		var date_input = first_day_of_year.split("/");
		var first_day = new Date(date_input[1] + "/" + date_input[0] + "/" + date_input[2]);
		var first_monday = getMonday(first_day);
		switch(quarter){
			case "1st Quarter":
				break;
			case "2nd Quarter":
				first_monday.setDate(first_monday.getDate() + 91);
				break;
			case "3rd Quarter":
				first_monday.setDate(first_monday.getDate() + 182);
			break;
			case "4th Quarter":
				first_monday.setDate(first_monday.getDate() + 273);
			break;
		}
		var last_day = new Date(first_monday);
		last_day.setDate(first_monday.getDate() + 90);	
		console.log("Month:" + last_day.getMonth());
		console.log("Day:" + last_day.getDate());
			
		if(last_day.getMonth() == 11 && last_day.getDate() <= 24){
			last_day.setDate(first_monday.getDate() + 6);						
		}
		
		console.log("Month:" + last_day.getMonth());
		console.log("Day:" + last_day.getDate());
		//console.log(first_monday);
		//console.log(last_day);
		$("#datepicker").val(getTwoDigitDateFormat(first_monday.getDate()) + "/" + getTwoDigitDateFormat(parseInt(first_monday.getMonth()) + 1) + "/" + first_monday.getFullYear());
		$("#datepicker2").val(getTwoDigitDateFormat(last_day.getDate()) + "/" + getTwoDigitDateFormat(parseInt(last_day.getMonth()) + 1) + "/" + last_day.getFullYear());
			
	});

	//Create Button
	$('#create').on('click',function(e){
		e.preventDefault();
		var leadgen_id =$("#leadgen_id").val();
		var date_from=$("#datepicker").val();
		var until=$("#datepicker2").val();
		var date_now=$("#date_now").val();		
		var required_leads_type=$("#leads_required_type").val();
		var tiers = document.getElementsByClassName('tier');
		var report_type = $("#report_type").val();
		
		var required_leads=[tiers.length];
		for (var i = 0; i < tiers.length; i++)
	        required_leads[i] = tiers[i].value;

		var formData = {
				leadgen_id:leadgen_id,
				date_from:date_from,
				until:until,
				date_now:date_now,
				required_leads_type:required_leads_type,
				required_leads:required_leads,
				type: report_type,
			};
		
		var preview = "";
		switch(report_type){
			case "Quarterly":
				preview = "quarterly_preview_leadgenerators.php";
			break;
			default:
				preview = "output3.php";
			break;

		}

		console.log(formData);
		$.ajax({
			dataType:'json',
			type:'POST',
			data:formData,
			url:preview,
			success:function(e){
				console.log(e);
				var mydata=JSON.stringify(e);
				var link=e['link'];
				var htm= '<iframe src="'+link+'" style="width: 100%;height: 75%;"></iframe>';
				$('#myModal').modal('show');
				$('.modal-body').html(htm);
				$('#save_pdf').unbind( "click");
				$('#save_pdf').on('click',function(){
					$.ajax({
						data:{mydata:mydata},
						type:'POST',
						url:"save_leadgen_report.php",
						beforeSend:function(){
						},
						success:function(x){
							$.confirm({
							    title: 'Success!',
							    content: 'You have successfully created a Report!',
							    buttons: {
							        Ok: function () {
								        	console.log(x);
								   			window.location='create_lead_gen_report.php';
								        },	 
							    	}
								});
						},
						error: function(x){
							var res = x.responseText.split("\n");
					        $.confirm({
							    title: 'Creating Report Unsuccessful',
							    content: res[0],
							    buttons: {
							        Ok: function () {
							        	console.log(x);
								        },	 
							    	}
								});
					      }
						});
				});
			},
			error: function (x) {
				var res = x.responseText.split("\n");
				$.confirm({
							    title: 'Creating Report Unsuccessful',
							    content: res[0],
							    buttons: {
							        Ok: function () {
							        	console.log(x);
								        },	 
							    	}
								});
					      }
		});
	});


	var tier=1;
	//Create Button
	$('#add_tier').on('click',function(e){
		e.preventDefault();
		tier++;
		var html = '<div class="form-group form-inline">\
					<label for="required_leads_' + tier + '"> Tier ' + tier + '\
						<input name="required_leads_' + tier + '" class="form-control tier" type="text" id="required_leads_' + tier + '" placeholder="Tier ' + tier + '" required/>\
					</label>\
				</div>'
		$('#required_leads_div').append(html);
		
	});
});


</script>
</head>

<body>
<!--nav bar end-->

<!--label-->
<div align="center">
  <div class="jumbotron">
    <h2 class="slide">Create Lead Generator Performance Report</h2>
	</div>


<?php require "database.php";
?>


<form method="POST" action="output3.php" autocomplete="off" class="margined">

	<?php 
date_default_timezone_set('Pacific/Auckland');
  $due=date('d/m/Y', strtotime('+7 days'));
  $now_=date('d/m/y');

  ?>

	<div>
		<div class="row">
			<div class='col-sm-4'></div>
			<div class='col-sm-2'>
				<label style="width: 100%";>Summary of: 
					<input class="form-control" value="<?=$now_;?>" readonly='' autocomplete="off" type="hidden" name="date_now" id="date_now" />

					<select name="leadgen_id" class="form-control adviser_selectpicker" id="leadgen_id" data-actions-box="true">
						<optgroup label="Face-to-Face Marketers">
							<?php
								$query = "SELECT * FROM leadgen_tbl where type='Face-to-Face Marketer'";
								$displayquery=mysqli_query($con,$query) or die('Could not look up user information; ' . mysqli_error($con));

								WHILE($rows = mysqli_fetch_array($displayquery)){
									$id=$rows["id"];
									$name=$rows["name"];
									echo "<option value=$id>$name</option>";
								}
							?>
						</optgroup>
						<optgroup label="Telemarketers">
							<?php
								$query = "SELECT * FROM leadgen_tbl where type='Telemarketer'";
								$displayquery=mysqli_query($con,$query) or die('Could not look up user information; ' . mysqli_error($con));

								WHILE($rows = mysqli_fetch_array($displayquery)){
									$id=$rows["id"];
									$name=$rows["name"];
									echo "<option value=$id>$name</option>";
								}
							?>
						</optgroup>
					</select>
				</label>
			</div>

			<div class='col-sm-2'>
				<label style="width: 100%";>Report Type: 
					<select name="report_type" class="form-control adviser_selectpicker" id="report_type" data-actions-box="true">
						<option>Weekly</option>
						<option>Bi-Monthly</option>
						<option>Monthly</option>
						<option>Quarterly</option>
						<option>Annual</option>
						<option>Specified</option>
					</select>
				</label>
			</div>
		</div>

		<div class="row">
			<div class="col-sm-3">

			</div>
			<div class="col-sm-2">Leads Required Type: 
				<select name="leads_required_type" class="form-control" id="leads_required_type" data-actions-box="true">
						<option>Weekly</option>
						<option>Bi-Monthly</option>
						<option>Monthly</option>
				</select>
			</div>

			<div class='col-sm-3' id="required_leads_div">Leads Required:
				<div class="form-group form-inline" >
					<label for="required_leads_1"> Tier 1
						<input name="required_leads_1" class="form-control tier" type="text" id="required_leads_1" placeholder="Tier 1" required/>
					</label>
				</div>
			</div>

			<div class='col-sm-2'>Add Tier:
				<button name="add_tier" class="btn btn-primary form-control" type="button" id="add_tier" required/> <i class="fa fa-plus"></i> Add Tier </button>
			</div>
		</div>

		<div class="row" id="annual_div" style="display:none;">
			<div class="col-sm-5">
			</div>
			<div class='col-sm-2'>Year: 
				<input name="annual-year" class="form-control" autocomplete="off" type="text" id="annual-year" placeholder="Ex: <?php echo date('Y') ?>" required/>
			</div>
		</div>

		<div class="row" id="specified-start" style="display:none;">
			<div class="col-sm-5">
			</div>
			<div class='col-sm-2'>Start Date: 
				<input name="start-specified" class="form-control datepicker" autocomplete="off" type="text" id="start-specified" placeholder="Ex: <?php echo date('d/m/Y') ?>" required/>
			</div>
		</div>


		<div class="row" id="specified-end" style="display:none;">
			<div class="col-sm-5">
			</div>
			<div class='col-sm-2'>End Date: 
				<input name="end-specified" class="form-control datepicker" autocomplete="off" type="text" id="end-specified" placeholder="Ex: <?php echo date('d/m/Y') ?>" required/>
			</div>
		</div>

		<div class="row" id="quarterly" style="display:none;">
			<div class="col-sm-4">
			</div>
			<div class='col-sm-2'>Input Year: 
				<input name="quarter-year" class="form-control" autocomplete="off" type="text" id="quarter-year" placeholder="Ex: <?php echo date('Y') ?>" required/>
			</div>
			<div class='col-sm-2' id="quarter_div" style="display:none;">Quarter: 
				<select name="quarter" class="form-control" id="quarter" data-actions-box="true">
						<option>1st Quarter</option>
						<option>2nd Quarter</option>
						<option>3rd Quarter</option>
						<option>4th Quarter</option>
				</select>
			</div>

		</div>

		<div id ="monthly_div" style="display:none;">
			<div class="row">
				<div class='col-sm-2 center'>Select Date: 
					<input name="month_date" class="form-control datepicker" autocomplete="off" type="text" id="month_date"/>
				</div>
			</div>
		</div>

		
		<div id ="bi-monthly_div" style="display:none;">
			<div class="row">
				<div class='col-sm-2 center'>Select Date: 
					<input name="bi-month_date" class="form-control datepicker" autocomplete="off" type="text" id="bi-month_date"/>
				</div>
			</div>
		</div>

		<div id ="weekly_div">
			<div class="row">
				<div class='col-sm-2 center'>Select Date Week: 
					<input name="week_date" class="form-control datepicker" autocomplete="off" type="text" id="week_date"/>
				</div>
			</div>
		</div>

		<div id ="non_quarterly">
			<div class="row">
				<div class='col-sm-2 center'>Starting Date: 
					<input name="date_from" class="form-control datepicker" autocomplete="off" type="text" id="datepicker" required/>
				</div>
			</div>

			<div class="row">
				<div class='col-sm-2 center'>Until: 
					<input name="until" class="form-control datepicker" autocomplete="off" type="text" id="datepicker2" required/></label>
				</div>
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
          <h2 class="modal-title" style="float: left;">Lead Generator Report Preview</h2>
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