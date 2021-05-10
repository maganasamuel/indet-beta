<?php

session_start();

//Restrict access to admin only
include "partials/admin_only.php";

$_SESSION["x"]=1;
unset($_SESSION['adviser_id']);
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

$(document).ready(function(){
	$( ".datepicker" ).datepicker({ dateFormat: 'dd/mm/yy' });

$("#datepicker").datepicker(
    {dateFormat: 'dd/mm/yy',
    beforeShowDay: function (date) {

    if (date.getDate() == 16 || date.getDate() == 1) {

        return [true, '',"Highlighted"];
    }
    return [false, ''];
   }
});


$('#datepicker').datepicker('disable');
	$('#datepicker2').datepicker('disable');
	
	
	$("#report_type").on('change',function(e){
		var type = $(this).val();
		var sched_type = $("#report_schedule_type").val();
		
		ResetInputs();
		ShowInput(type, sched_type);
	});

	$("#report_schedule_type").on('change',function(e){
		var type = $("#report_type").val();
		var sched_type = $(this).val();
		ResetInputs();
		ShowInput(type, sched_type);		
	});

	function ShowInput(type, sched_type){
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
			if(sched_type=="Regular"){
				$("#non_quarterly").slideDown();
				$("#weekly_div").slideDown();
			}
			else{
				$("#sumit_weekly").slideDown();
				$("#non_quarterly").slideDown();
			}
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
			if(sched_type=="Regular"){
				$("#non_quarterly").slideDown();
				$("#monthly_div").slideDown();
			}
			else{
				$("#sumit_monthly").slideDown();
				$("#non_quarterly").slideDown();
			}
		}
		else if(type=="Annual"){
			$("#annual_div").slideDown();
			
			$("#datepicker").prop("readonly", true);
			$("#datepicker2").prop("readonly", true);
			$('#datepicker').datepicker('disable');
			$('#datepicker2').datepicker('disable');
			$("#non_quarterly").slideDown();
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
	}
	
	function ResetInputs(){
		//animate
		$("#specified-start").slideUp();
		$("#specified-end").slideUp();
		$("#quarter_div").slideUp();

		$("#quarterly").slideUp();
		$("#non_quarterly").slideUp();

		$("#sumit_monthly").slideUp();
		$("#sumit_monthly_month_div").slideUp();

		$("#sumit_weekly").slideUp();
		$("#sumit_weekly_week_div").slideUp();

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
        var monday = new Date(w_date.valueOf());
				var sunday = new Date(monday.valueOf());
				sunday.setDate(sunday.getDate() + 6);

        $("#datepicker").val( getTwoDigitDateFormat(monday.getDate()) + "/" + getTwoDigitDateFormat(monday.getMonth() + 1) + "/" + monday.getFullYear());
        $("#datepicker2").val( getTwoDigitDateFormat(sunday.getDate()) + "/" + getTwoDigitDateFormat(sunday.getMonth() + 1) + "/" + sunday.getFullYear());
	});

	/*

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

	*/

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
		
		$("#monthly_sumit_month_index").on("keyup change", function(e){
			var month_index = $(this).val();
			var year = $("#sumit_monthly_year").val();
			

			//get first day of the year
			var first_day_of_year = "01/01/" + year;
			var date_input = first_day_of_year.split("/");
			var first_day = new Date(date_input[1] + "/" + date_input[0] + "/" + date_input[2]);
			var first_monday = getMonday(first_day);
			var last_day = new Date(first_monday);


			last_day.setDate(first_monday.getDate() + 27);

			var days_to_add = 0;
			var is_a_third_month = false;

			if(month_index>1){
				for(i = 2; i <= month_index; i++){
					days_to_add = 28;

					first_monday.setDate(first_monday.getDate() + days_to_add);
					last_day.setDate(last_day.getDate() + days_to_add);

					if(i%3==0){
						last_day.setDate(last_day.getDate() + 7);
						if(i!=month_index){
							first_monday.setDate(first_monday.getDate() + 7);
						}
					}
				}				
			}

			if(last_day.getMonth() == 11 && last_day.getDate() <= 24){
				last_day.setDate(last_day.getDate() + 6);						
			}

			$("#datepicker").val(getTwoDigitDateFormat(first_monday.getDate()) + "/" + getTwoDigitDateFormat(parseInt(first_monday.getMonth()) + 1) + "/" + first_monday.getFullYear());
			$("#datepicker2").val(getTwoDigitDateFormat(last_day.getDate()) + "/" + getTwoDigitDateFormat(parseInt(last_day.getMonth()) + 1) + "/" + last_day.getFullYear());
		});
			
		$("#sumit_monthly_year").on("keyup change", function(e){
					var year = $(this).val();
					year = year.replace(/[^0-9]/g, "");
					$(this).val(year);

					if(year.length>3){
						
						$("#sumit_monthly_month_div").slideDown();
						
						//get first day of the year
						var first_day_of_year = "01/01/" + year;
						var date_input = first_day_of_year.split("/");
						var first_day = new Date(date_input[1] + "/" + date_input[0] + "/" + date_input[2]);
						var first_monday = getMonday(first_day);
						var last_day = new Date(first_monday);


						last_day.setDate(first_monday.getDate() + 27);

							$("#monthly_sumit_month_index").val("1");					
							$("#datepicker").val(getTwoDigitDateFormat(first_monday.getDate()) + "/" + getTwoDigitDateFormat(parseInt(first_monday.getMonth()) + 1) + "/" + first_monday.getFullYear());
							$("#datepicker2").val(getTwoDigitDateFormat(last_day.getDate()) + "/" + getTwoDigitDateFormat(parseInt(last_day.getMonth()) + 1) + "/" + last_day.getFullYear());
					}
					else{
						$("#sumit_monthly_month_div").slideUp();
						$("#datepicker").val("");
						$("#datepicker2").val("");
					}
		});

		
		$("#sumit_weekly_week_index").on("keyup change", function(e){
			var week_index = $(this).val();
			var year = $("#sumit_weekly_year").val();
		

			//get first day of the year
			var first_day_of_year = "01/01/" + year;
			var date_input = first_day_of_year.split("/");
			var first_day = new Date(date_input[1] + "/" + date_input[0] + "/" + date_input[2]);
			var first_monday = getMonday(first_day);
			var last_day = new Date(first_monday);


			last_day.setDate(first_monday.getDate() + 6);

			if(week_index>1){
				for(i = 2; i <= week_index; i++){
					first_monday.setDate(first_monday.getDate() + 7);
					last_day.setDate(last_day.getDate() + 7);
				}				
			}

			$("#datepicker").val(getTwoDigitDateFormat(first_monday.getDate()) + "/" + getTwoDigitDateFormat(parseInt(first_monday.getMonth()) + 1) + "/" + first_monday.getFullYear());
			$("#datepicker2").val(getTwoDigitDateFormat(last_day.getDate()) + "/" + getTwoDigitDateFormat(parseInt(last_day.getMonth()) + 1) + "/" + last_day.getFullYear());
		});
			
		$("#sumit_weekly_year").on("keyup change", function(e){
					var year = $(this).val();
					year = year.replace(/[^0-9]/g, "");
					$(this).val(year);

					if(year.length>3){
						
						$("#sumit_weekly_week_div").slideDown();
						
						//get first day of the year
						var first_day_of_year = "01/01/" + year;
						var date_input = first_day_of_year.split("/");
						var first_day = new Date(date_input[1] + "/" + date_input[0] + "/" + date_input[2]);
						var first_monday = getMonday(first_day);
						var last_day = new Date(first_monday);


						last_day.setDate(first_monday.getDate() + 6);

							$("#sumit_weekly_week_index").val("1");					
							$("#datepicker").val(getTwoDigitDateFormat(first_monday.getDate()) + "/" + getTwoDigitDateFormat(parseInt(first_monday.getMonth()) + 1) + "/" + first_monday.getFullYear());
							$("#datepicker2").val(getTwoDigitDateFormat(last_day.getDate()) + "/" + getTwoDigitDateFormat(parseInt(last_day.getMonth()) + 1) + "/" + last_day.getFullYear());
					}
					else{
						$("#sumit_weekly_week_div").slideUp();
						$("#datepicker").val("");
						$("#datepicker2").val("");
					}
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
				var sched_type = $("#report_schedule_type").val();
        year = year.replace(/[^0-9]/g, "");
        $(this).val(year);
				
        if(year.length>3){
					if(sched_type=="Regular"){
						$("#datepicker").val("01/01/" + year);
						$("#datepicker2").val("31/12/" + year);
					}
					else{
						var first_day_of_year = "01/01/" + year;

						var date_input = first_day_of_year.split("/");
						var first_day = new Date(date_input[1] + "/" + date_input[0] + "/" + date_input[2]);
						var first_monday = getMonday(first_day);
						var last_day = new Date(first_monday);
						last_day.setDate(first_monday.getDate() + 363);		

						if(last_day.getMonth() == 11 && last_day.getDate() <= 24){
							last_day.setDate(last_day.getDate() + 6);						
						}

						$("#datepicker").val(getTwoDigitDateFormat(first_monday.getDate()) + "/" + getTwoDigitDateFormat(parseInt(first_monday.getMonth()) + 1) + "/" + first_monday.getFullYear());
						$("#datepicker2").val(getTwoDigitDateFormat(last_day.getDate()) + "/" + getTwoDigitDateFormat(parseInt(last_day.getMonth()) + 1) + "/" + last_day.getFullYear());
					}
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
			last_day.setDate(last_day.getDate() + 6);						
		}
		
		console.log("Month:" + last_day.getMonth());
		console.log("Day:" + last_day.getDate());
		//console.log(first_monday);
		//console.log(last_day);
		$("#datepicker").val(getTwoDigitDateFormat(first_monday.getDate()) + "/" + getTwoDigitDateFormat(parseInt(first_monday.getMonth()) + 1) + "/" + first_monday.getFullYear());
		$("#datepicker2").val(getTwoDigitDateFormat(last_day.getDate()) + "/" + getTwoDigitDateFormat(parseInt(last_day.getMonth()) + 1) + "/" + last_day.getFullYear());
			
	});


$("#datepicker").on('change',function(){
	if($("#report_type").val()!="Bi-Monthly")
		return;
		
var $this=$(this).val();
var m=$this.substr(3,2);
var month=m-1;	
var year=$this.substr(6,4);
var ifday=$this.substr(0,2);

var lastday = function(y,m){
return new Date(y, m +1, 0).getDate();
}
if(ifday==16){
var ld=lastday(year,month)+'/'+m+'/'+year;
$('#datepicker2').val(ld);
}
else if(ifday==1){
var ld='15'+'/'+m+'/'+year;	
$('#datepicker2').val(ld);
}
else{

}



}); 


$('#mydate').prop('disabled',true);
	var x=1;
	var client="#client";
	var sel="#sel";
	var txt="#txt";
	var myadviser="#myadviser";
	var openingbal="#openingbal";
	var mymonth="#mymonth";
	var mydate="#mydate";
	var myyear="#myyear";
$('#desc').select2();
$('#desc').change(function(){
var arr=[];
$(this).parent().find('.select2-selection__choice').each(function(){
          //  console.log($(this).attr('title'));
            arr.push($(this).attr('title'));

        });



if(jQuery.inArray("others",arr)!=-1){
//$('#other_text').show();
$('#other_value').show();
}
else{
//$('#other_text').hide().val('');
$('#other_value').hide().val('');
}

});

$('#other_text').keyup(function(){
	$("#others").val($(this).val());
});

$('#type').change(function(){
	type = $(this).val();
	if(type=="Team")
		$("#adviser_div").slideUp();
	else
		$("#adviser_div").slideDown();
});




$('#create').prop('disabled',false);

$('#create').on('click',function(e){
e.preventDefault();

var adv_name=$("#adv_name").val();
var type=$("#type").val();
var adviser_id=$("#myadviser").val();
var date_from=$("#datepicker").val();
var invoice_date=$("#invoice_date").val();
var desc=JSON.stringify($("#desc").val());
var until=$("#datepicker2").val();
var report_type=$("#report_type").val();
		var report_schedule_type = $("#report_schedule_type").val();
	
	if(type=="Individual"){
		$.ajax({
			dataType:'json',
			type:'POST',
			data:{adv_name:adv_name,
				adviser_id:adviser_id,
				date_from:date_from,
				invoice_date:invoice_date, 
				type:type,
				report_type:report_type,
				report_schedule_type:report_schedule_type,
				desc:desc, 
				until:until},
			url:"deal_report_preview.php",
			beforeSend: function(){
				$("#create_report_text").text("Generating Report");
				$("#create_report_spinner").show();
			},
			success:function(e){
				$("#create_report_text").text("Create Report");
				$("#create_report_spinner").hide();
				console.log(e);
				console.log(desc);
				var mydata=JSON.stringify(e);
				var link=e['link'];
				var htm= '<iframe src="'+link+'" style="width: 100%;height: 75%;"></iframe>';
				$('#myModal').modal('show');
				$('.modal-body').html(htm);
				$('#save_pdf').unbind( "click");
				$('#save_pdf').on('click',function(){
					$.ajax({
						//dataType:'JSON',
						data:{mydata:mydata},
						type:'POST',
						url:"save_deal_report.php",
					success:function(x){
						console.log(x);
						$.confirm({
						    title: 'Success!',
						    content: 'You have successfully created a deal report.',
						    buttons: {
						        Ok: function () {
							        	console.log(x);
							   			window.location='create_production_report.php';
							        },	 
						    	}
							});
					}
					});
				});
			},
			error: function (x) {
				x=JSON.stringify(x);
			        console.log("Data:" + x);
			      }
		});
	}
	else{
		$.ajax({
			dataType:'json',
			type:'POST',
			data:{
				date_from:date_from,
				invoice_date:invoice_date, 
				type:type,
				report_schedule_type:report_schedule_type,
				report_type:report_type,
				desc:desc, 
				until:until},
			url:"team_production_report_preview.php",
			beforeSend: function (){
				$("#create_report_text").text("Generating Report");
				$("#create_report_spinner").show();
			},
			success:function(e){
				$("#create_report_text").text("Create Report");
				$("#create_report_spinner").hide();
				console.log(desc);
				var mydata=JSON.stringify(e);
				var link=e['link'];
				var htm= '<iframe src="'+link+'" style="width: 100%;height: 75%;"></iframe>';
				$('#myModal').modal('show');
				$('.modal-body').html(htm);
				$('#save_pdf').unbind( "click");
				$('#save_pdf').on('click',function(){
					$.ajax({
						//dataType:'JSON',
						data:{mydata:mydata},
						type:'POST',
						url:"save_deal_report.php",
					beforeSend:function(){

					},
					success:function(x){
						console.log(x);
						$.confirm({
						    title: 'Success!',
						    content: 'You have successfully created a deal report.',
						    buttons: {
						        Ok: function () {
							        	console.log(x);
							   			window.location='create_production_report.php';
							        },	 
						    	}
							});
					}
					});
				});
			},
			error: function (x) {
				x=JSON.stringify(x);
			        console.log("Data:" + x);
			      }
		});
	}

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
    <h2 class="slide">Create Production Report</h2>
</div>
<!--label end-->

<?php require "database.php";
 $con=mysqli_connect($host,$username,$password,$db)or die("could not connect to sql");
 if (!$con) {
        echo "<div>";
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
	    echo "</div>";	
}

?>

<form method="POST" action="team_production_report_preview.php" autocomplete="off" class="margined">
<table align="center" bgcolor="ededed" cellpadding="5px" onload="form1.reset();">
	<div class='row'>

   <div class='col-sm-3'></div>
	<div class='col-sm-2'>

		<label>Report Type
		 <div class="input-group">
		    <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
		    <select name="type" class="form-control" id="type" required />
			  <option selected>Team</option>
			  <option>Individual</option>
			</select>
				<input type='hidden' name='adv_name' id='adv_name' value=''>
		  	</div>
		</label>

		<label id="adviser_div" style="display:none;">Adviser
		 <div class="input-group">
		    <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
		    <select name="adviser_id" class="form-control" id="myadviser" />
			  <option value="" disabled selected hidden>Select Adviser</option>
			  <option value="all">Select All</option>
				<?php 
					$query = "SELECT id,name FROM adviser_tbl ORDER BY name asc";
					$displayquery=mysqli_query($con,$query) or die('Could not look up user information; ' . mysqli_error($con));
					WHILE($rows = mysqli_fetch_array($displayquery)){
						$id=$rows["id"];
						$name=$rows["name"];
						echo "<option value='$id'>".$name."</option>";
					}
				?>
				<!--<td><input class="addadviser" type="text" id="datepicker" name="mydate" required></td>-->
				</select>
				<input type='hidden' name='adv_name' id='adv_name' value=''>
		  	</div>
		</label>

	</div>
	<div class='col-sm-2'>
				<label style="width: 100%";>Report Schedule Type: 
					<select name="report_schedule_type" class="form-control adviser_selectpicker" id="report_schedule_type" data-actions-box="true">
						<option>Regular</option>
						<option>Business</option>
					</select>
				</label>
			</div>
<div class='col-sm-2' style="display:none;">
	<label>Filter
	<div class="input-group">
		<span class="input-group-addon">
		<i class="fa fa-file" aria-hidden="divue"></i></span>
	    <select class="form-control" id="desc" name="desc" multiple="multiple" required />
			<option selected>Submission</option>
			<option selected>Issued</option>
			<option selected>Cancelled</option>
			<option selected>Pending</option>
			<option selected>KiwiSaver</option>
		</select>
	</div>
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

		<div class="row" id="sumit_monthly" style="display:none;">
			<div class="col-sm-4">
			</div>
			<div class='col-sm-2'>Input Year: 
				<input name="sumit_monthly_year" class="form-control" autocomplete="off" type="text" id="sumit_monthly_year" placeholder="Ex: <?php echo date('Y') ?>" required/>
			</div>

			<div class='col-sm-2' id="sumit_monthly_month_div" style="display:none;">Month: 
				<select name="monthly_sumit_month_index" class="form-control" id="monthly_sumit_month_index" data-actions-box="true">
						<option value="1">Month 1</option>
						<option value="2">Month 2</option>
						<option value="3">Month 3</option>
						<option value="4">Month 4</option>
						<option value="5">Month 5</option>
						<option value="6">Month 6</option>
						<option value="7">Month 7</option>
						<option value="8">Month 8</option>
						<option value="9">Month 9</option>
						<option value="10">Month 10</option>
						<option value="11">Month 11</option>
						<option value="12">Month 12</option>
				</select>
			</div>
		</div>

		<div class="row" id="sumit_weekly" style="display:none;">
			<div class="col-sm-4">
			</div>
			<div class='col-sm-2'>Input Year: 
				<input name="sumit_weekly_year" class="form-control" autocomplete="off" type="text" id="sumit_weekly_year" placeholder="Ex: <?php echo date('Y') ?>" required/>
			</div>

			<div class='col-sm-2' id="sumit_weekly_week_div" style="display:none;">Week: 
				<input name="sumit_weekly_week_index" class="form-control" autocomplete="off" type="text" id="sumit_weekly_week_index" placeholder="1" required/>
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
          <div class="col-sm-2 center" >
	<button name="enter" type="button" id='create' style='margin-top: 30px;width: 100%;' class="btn btn-danger center" /><i id="create_report_spinner" style="display:none;" class="fas fa-spinner fa-spin"></i> <span id="create_report_text">Create Report</span></button>
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
          <h2 class="modal-title" style="float: left;">Adviser Report Preview</h2>
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