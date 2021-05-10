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

$("#datefrom").datepicker(
    {dateFormat: 'dd/mm/yy',
    beforeShowDay: function (date) {

    if (date.getDate() == 15 || date.getDate() == 1) {

        return [true, '',"Highlighted"];
    }
    return [false, ''];
   }
});

$("#datefrom").on('change',function(){
var $this=$(this).val();
var m=$this.substr(3,2);
var month=m-1;	
var year=$this.substr(6,4);
var ifday=$this.substr(0,2);

var lastday = function(y,m){
return new Date(y, m +1, 0).getDate();
}
if(ifday==15){
var ld=lastday(year,month)+'/'+m+'/'+year;
$('#dateuntil').val(ld);
}
else if(ifday==1){
var ld='15'+'/'+m+'/'+year;	
$('#dateuntil').val(ld);
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




$('#create').prop('disabled',false);

$('#create').on('click',function(e){
e.preventDefault();

var adv_name=$("#adv_name").val();
var adviser_id=$("#myadviser").val();
var date_from=$("#datefrom").val();
var invoice_date=$("#invoice_date").val();
var desc=JSON.stringify($("#desc").val());
var until=$("#dateuntil").val();

console.log(date_from + ":" + until);
	$.ajax({
		dataType:'json',
		type:'POST',
		data:{adv_name:adv_name,
			adviser_id:adviser_id,
			date_from:date_from,
			invoice_date:invoice_date, 
			desc:desc, 
			until:until},
		url:"deal_report_preview.php",
		success:function(e){
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
						   			window.location='create_cancellations_report.php';
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
    <h2 class="slide">Create Deal Cancellations Report</h2>
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

<form method="POST" action="deal_report_preview.php" autocomplete="off" class="margined">
<table align="center" bgcolor="ededed" cellpadding="5px" onload="form1.reset();">
	<div class='row'>

   <div class='col-sm-3'></div>
   		<div class='col-sm-2'>
	<label>Adviser
	 <div class="input-group">
	    <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
	    <select name="adviser_id" class="form-control" id="myadviser" required />
		  <option value="" disabled selected>Select Adviser</option>
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
<div class='col-sm-2' style="display:none;">
	<label>Description
	<div class="input-group">
		<span class="input-group-addon">
		<i class="fa fa-file" aria-hidden="divue"></i></span>
	    <select class="form-control" id="desc" name="desc" multiple="multiple" required />
			<option selected>Cancelled</option>
			<option>Issued</option>
			<option>Pending</option>
		</select>
	</div>
	</label>
</div>


		</div>




</div>

<div class="row">
          <div class="col-sm-2 center" >
	<input name="enter" type="submit" id='create' value="Create Report" style='margin-top: 30px;width: 100%;' class="btn btn-danger center" />
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
          <h2 class="modal-title" style="float: left;">Deals Report Preview</h2>
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