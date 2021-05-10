 <?php
session_start();
ob_start();
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
<!--nav bar end-->
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">
<link rel="stylesheet" href="styles.css">
<link id="favicon" rel="icon" href="Logo_ImageOnly.png" type="image/png" sizes="16x16">
<title>INDET</title>
<script>
$(document).ready(function(){

//$('#mymonth').prop('disabled',true);
//$('#mydate').prop('disabled',true);
//$('#myyear').prop('disabled',true);
var d=$('#sumday').val();


$('#mydate').val(d);

	var x=1;
	var client="#client";
	var sel="#sel";
	var txt="#txt";
	var myadviser="#myadviser";
	var openingbal="#openingbal";
	var mymonth="#mymonth";
	var mydate="#mydate";
	var myyear="#myyear";

	function canx(x){
	var chk="#chk".concat(x);
	var can="#can".concat(x);

	var com="#com".concat(x);
	var gst="#gst".concat(x);

	var gstcan="#gstcan".concat(x);
	var rencan="#rencan".concat(x);
	var rencangst="#rencangst".concat(x);
	var rencom="#rencom".concat(x);
	var rengst="#rengst".concat(x);
	var txtconcat = txt.concat(x);
	var selconcat = sel.concat(x);
	var viewcancel="#viewcancel".concat(x);

	var del='#del'.concat(x);

$(chk).click(function(){

	if($(chk).is(':checked')) {
    $(can).show();
	$(gstcan).show();
	$(rencan).show();
	$(rencangst).show();
	
	$(rencom).hide();
	$(rengst).hide();
	$(com).hide();
	$(gst).hide();

	$(rencom).find('input').val('');
	$(rengst).find('input').val('');
	$(com).find('input').val('');
	$(gst).find('input').val('');
	}
	else{
	$(can).hide().find('input').val('');
	$(gstcan).hide().find('input').val('');
	$(rencan).hide().find('input').val('');
	$(rencangst).hide().find('input').val('');

  	$(can).hide();
	$(gstcan).hide();
	$(rencan).hide();
	$(rencangst).hide();
	$(rencom).show();
	$(rengst).show();


	$(com).show();
	$(gst).show();
	}
	});


$(del).click(function(e){
e.preventDefault();
var aaa=$(this);
$.confirm({
    title: 'Confirm!',
    content: 'Are you sure you want to clear this field? ',
    buttons: {
        confirm: function () {
            $(selconcat).val('').trigger('change');

	aaa.parent().parent().find('input').val('');
	        },
        cancel: function () {
 
        },
 
    }
});
           

})
	

	$(selconcat).change(function(){
	var conceptName = $(selconcat).find(":selected").text();
   	if(conceptName=="New Client"){
   	$(rencom).hide();
  	$(rengst).hide();
    $(viewcancel).hide();
  	$(txtconcat).prop('readOnly',false);
  	$(txtconcat).val("");
   	}
   	else{
   	$(txtconcat).val(conceptName);
   	$(rencom).show();
  	$(rengst).show();
  	$(viewcancel).show();
	$(txtconcat).prop('readOnly',true);
	}
  	});
	};


$("#numclient").keyup(function(){

test=$("#numclient").val();
for(x=0;x<20;x++){
rencom="#rencom".concat(x);
rengst="#rengst".concat(x);


    txtconcat = txt.concat(x);
    clientconcat = client.concat(x);
    $(clientconcat).hide();

if(test<x){
var gettr=$(txtconcat).closest("tr");
$(gettr).find(':text').val("");
$(gettr).find('select').val("");


 	$(rencom).hide();
  	$(rengst).hide();
}else{

}


}

for(x=1;x<=test;x++){
    txtconcat = txt.concat(x)
    clientconcat = client.concat(x);
    $(clientconcat).show();
  }
});

for(y=1;y<20;y++){
	canx(y);
};

	$(myadviser).change(function(){
	var selected=$(myadviser).find(":selected").text();
	
	$.ajax({
	url:'getclosing.php',
	data:'adviser='+selected,
	dataType:'json',
	success:function(data){
   $(openingbal).val(data);
	}
	})

	});



	$(mymonth).change(function(){
		console.log();
		$('#mydate').prop('disabled',false);
	$(mydate).show();
	var month=$(mymonth).val();
	var date=$(mydate).val();
	var year=$(myyear).val();
	var adviserid=$(myadviser).val();

	$.ajax({
	url:'getendday.php',
	data:{month: month, date:date, year: year,adviserid:adviserid},
	success:function(data){
		if(data=='wrong'){
	$('#mydate').prop('disabled',true);
	
		    $.alert({
        title: 'Alert!',
        content: 'Existing Date in Summary.',
    });
		$(mymonth).val('');
		$(mydate).val('');

		}else{

 	$("#nextdate").html(data);
 	}
	}

	})


	$.ajax({
	url:'getclosing.php',
	data:{adviser:adviserid,month:month,date:date,year:year},
	dataType:'json',
	success:function(data){
	//	alert('yawa');
   $(openingbal).val(data);
	}

	})


	});

	$(myyear).change(function(){
	$(mydate).show();
	var month=$(mymonth).val();
	var date=$(mydate).val();
	var year=$(myyear).val();
	var adviserid=$(myadviser).val();


	$.ajax({
	url:'getendday.php',
	data:{month: month, date:date, year: year,adviserid:adviserid},
	success:function(data){
		if(data=='wrong'){
$('#mydate').prop('disabled',true);
		    $.alert({
        title: 'Alert!',
        content: 'Existing Date in Summary.',
    });
		$(mymonth).val('');
		$(mydate).val('');

		}else{

 	$("#nextdate").html(data);
 	}
	}

	})


	$.ajax({
	url:'getclosing.php',
	data:{adviser:adviserid,month:month,date:date,year:year},
	dataType:'json',
	success:function(data){

   $(openingbal).val(data);
	}

	})



	});


	$(mydate).change(function(){
	$(mydate).show();
	var month=$(mymonth).val();
	var date=$(mydate).val();
	var year=$(myyear).val();
	var adviserid=$(myadviser).val();

	$.ajax({
	url:'getendday.php',
	data:{month: month, date:date, year: year,adviserid:adviserid},
	success:function(data){
		if(data=='wrong'){
$('#mydate').prop('disabled',true);
			    $.alert({
        title: 'Alert!',
        content: 'Existing Date in Summary.',
    });
		$(mymonth).val('');
		$(mydate).val('');

		}else{

 	$("#nextdate").html(data);
 	}
	}



	})



	$.ajax({
	url:'getclosing.php',
	data:{adviser:adviserid,month:month,date:date,year:year},
	dataType:'json',
	success:function(data){

   $(openingbal).val(data);
	}

	})








	});



var cc=$('#get_num').val();
if(cc==0){
cc='';
}
$('#numclient').val(cc).keyup();







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
    <h2 class="slide">Edit Payroll</h2>
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




<?php 
$adviser_id=$_GET["adviser_id"];
$starting_date=$_GET["starting_date"];

$summonth=substr($starting_date,4,2);
$sumday=substr($starting_date,6,2);
$sumyear=substr($starting_date,0,4);



$query = "SELECT id,name FROM adviser_tbl where id='$adviser_id'";
$displayquery=mysqli_query($con,$query) or die('Could not look up user information; ' . mysqli_error($con));



?>
<?php
$query = "SELECT name,openingbal,bonuses,sundries,agencyrelease,startingdate,leads_qty FROM summary_tbl WHERE startingdate='$starting_date' AND adviser_id='$adviser_id'";
$displayquery=mysqli_query($con,$query) or die('Could not look up user information; ' . mysqli_error($con));

$rows = mysqli_fetch_array($displayquery);

$bonuses=$rows["bonuses"];
$sundries=$rows["sundries"];
$agencyrelease=$rows["agencyrelease"];
$startingdate=$rows["startingdate"];
$openingbal=$rows["openingbal"];
$leads_qty=$rows["leads_qty"];



?>


<?php 


//$query = "SELECT * FROM pdf_tbl WHERE filename LIKE '$startingdate%' AND adviser_id='$adviser_id'";

$query = "SELECT * FROM clients_tbl WHERE startingdate='$startingdate' AND adviser='$adviser_id'";
$displayquery=mysqli_query($con,$query) or die('Could not look up user information; ' . mysqli_error($con));
$num_client = mysqli_num_rows($displayquery);
$clients=array();
WHILE($rows = mysqli_fetch_array($displayquery)){
array_push($clients, $rows);
}
//print_r($clients);

?>

<input type="hidden" id='get_num' value='<?php echo $num_client; ?>' >

<input type='hidden' id='sumday' value="<?=$sumday;?>" >

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
$adviser_id=$_GET["adviser_id"];
$starting_date=$_GET["starting_date"];

$summonth=substr($starting_date,4,2);
$sumday=substr($starting_date,6,2);
$sumyear=substr($starting_date,0,4);

$query = "SELECT id,name FROM adviser_tbl where id='$adviser_id'";
$displayquery=mysqli_query($con,$query) or die('Could not look up user information; ' . mysqli_error($con));

WHILE($rows = mysqli_fetch_array($displayquery)){
$id=$rows["id"];
$name=$rows["name"];


echo "

	<option value='$id' selected>$name</option>";

}
?>
<?php
$query = "SELECT name,closing_bal FROM summary_tbl";
$displayquery=mysqli_query($con,$query) or die('Could not look up user information; ' . mysqli_error($con));

WHILE($rows = mysqli_fetch_array($displayquery)){
$id=$rows["id"];

}





?><!--<td><input class="form-control" type="text" id="datepicker" name="mydate" required></td>-->
		</select>
  </div>
</label>
</div>
 		<div class='col-sm-4'>
<label>Date Period
	<div class="form-inline">
<select name="mymonth" id="mymonth">
<option value="1" <?php if($summonth=="1") { echo 'selected="selected"'; } else { echo ''; } ?>>January</option>
<option value="2" <?php if($summonth=="2") { echo 'selected="selected"'; } else { echo ''; } ?>>February</option>
<option value="3" <?php if($summonth=="3") { echo 'selected="selected"'; } else { echo ''; } ?>>March</option>
<option value="4" <?php if($summonth=="4") { echo 'selected="selected"'; } else { echo ''; } ?>>April</option>
<option value="5" <?php if($summonth=="5") { echo 'selected="selected"'; } else { echo ''; } ?>>May</option>
<option value="6" <?php if($summonth=="6") { echo 'selected="selected"'; } else { echo ''; } ?>>June</option>
<option value="7" <?php if($summonth=="7") { echo 'selected="selected"'; } else { echo ''; } ?>>July</option>
<option value="8" <?php if($summonth=="8") { echo 'selected="selected"'; } else { echo ''; } ?>>August</option>
<option value="9" <?php if($summonth=="9") { echo 'selected="selected"'; } else { echo ''; } ?>>September</option>
<option value="10" <?php if($summonth=="10") { echo 'selected="selected"'; } else { echo ''; } ?>>October</option>
<option value="11" <?php if($summonth=="11") { echo 'selected="selected"'; } else { echo ''; } ?>>November</option>
<option value="12" <?php if($summonth=="12") { echo 'selected="selected"'; } else { echo ''; } ?>>December</option>
</select>

<select name="mydate" class='form-control' id="mydate" >
<option value="" disabled selected="">Select period</option>
<option value="01"><?php echo " 1-15";?></option>
<option id="nextdate" value="15"><?php echo " 15-31";?></option>
</select>




<?php
$already_selected_value = date("Y");
$earliest_year = 1980;

print '<select name="myyear" id="myyear"  >';

foreach (range(date('Y'), $earliest_year) as $x) {
if($sumyear==$x){
    print '<option value="'.$x.'" selected/>'.$x.'</option>';
}
else{
	   print '<option value="'.$x.'">'.$x.'</option>';
}
}
print '</select>';


?>

</div>
</label>

</div>

	<div class='col-sm-2'>
		<label>Opening Balance
 <div class="input-group">
    <span class="input-group-addon"><i class="fa fa-usd"></i></span>
<input class="form-control" placeholder="<?php echo $openingbal;?>" value="<?php echo $openingbal;?>" id="openingbal" type="number" step="any" name="openbal" step="any" required/>

</div>
</label>
</div>

		</div>



	<div class='row'>

   <div class='col-sm-2'></div>
	<div class='col-sm-2'>
	<label>Leads Purchased
 <div class="input-group">
    <span class="input-group-addon"><i class="fa fa-envelope"></i></span>
<input class="form-control"placeholder="<?php echo $leads_qty;?>" value="<?php echo $leads_qty;?>" number" step="any" name="leads" step="any" required/>
</div>
</label>
</div>

	<div class='col-sm-2'>
	<label>Sundries
 <div class="input-group">
    <span class="input-group-addon"><i class="fa fa-usd"></i></span>
<input class="form-control" placeholder="<?php echo $sundries;?>" value="<?php echo $sundries;?>" type="number" step="any" name="sundries" step="any" required/>
</div>
</label>
</div>
<input type="hidden" name="edit" value="true">

	<div class='col-sm-2'>
	<label>Bonuses
 <div class="input-group">
    <span class="input-group-addon"><i class="fa fa-usd"></i></span>
<input class="form-control" placeholder="<?php echo $bonuses;?>" value="<?php echo $bonuses;?>" type="number" step="any" name="bonuses" step="any" required/>

</div>
</label>
</div>

<div class='col-sm-2'>
	<label>Agency Release: 
 <div class="input-group">
    <span class="input-group-addon"><i class="fa fa-usd"></i></span>
<input class="form-control" placeholder="<?php echo $agencyrelease;?>" value="<?php echo $agencyrelease;?>" type="number" name="agencyrelease" step="any"/>

</div>
</label>
</div>

<div class='col-sm-2'>
	<label>Number of Clients: 
 <div class="input-group">
    <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
<input class="form-control" type="number" step="any"  id="numclient" name="numclient" max="10"/>

</div>
</label>
</div>
</div>









<?php 
$query = "SELECT DISTINCT name FROM clients_tbl";
for($z=1;$z<100;$z++){
$clients[$z]['name']=isset($clients[$z]['name'])?$clients[$z]['name']:'';
	?>


<div id="<?='client'.$z;?>" style='display: none;margin-top: 50px;'>
<div class='row'>
		<div class="col-sm-2 center">
				

			<label>Client Name: 
				<div class="input-group">    <span class="input-group-addon">
		<i class="glyphicon glyphicon-user" aria-hidden="divue"></i></span>

<?php


$cn=isset($clients[$z-1]["name"])?$clients[$z-1]["name"]:'';


?>

<input id="<?='txt'.$z;?>" type='text' <?php if($cn!=''){echo 'readonly';} ;?> class='form-control' value='<?=$cn;?>' name='client_name[]' />
    	</div>
    </label>

</div>
 </div>


	


<div class='row'>
<div class="col-sm-2 center">
<label>If Existing 
	<div class="input-group">    <span class="input-group-addon">
		<i style="color:#d9534f" class="glyphicon glyphicon-user" aria-hidden="divue"></i></span>

 <select name='existing_client' class='form-control' id="<?php echo 'sel'.$z;?>">
       <option style="color:#d9534f;font-weight: bold;" value="" selected />New Client</option>
  <option value="" disabled>Select existing client</option>

<?php

$displayquery=mysqli_query($con,$query) or die('Could not look up user information; ' . mysqli_error($con));
WHILE($rows = mysqli_fetch_array($displayquery)){
$name=$rows["name"];
if($name==$clients[$z-1]['name']){
echo "<option value='$id' selected>$name</option>";
}else{
echo "
	<option value=$id >$name</option>";
	}
}
?>
		</select>
	</div>
</label>
		</div>
</div>


	<div class="row" id="<?php echo 'com'.$z;?>" >
		<div class="col-sm-2 center">
		<label>Eliteinsure Commissions: 	 <div class="input-group">
    <span class="input-group-addon">
		<i class="fa fa-usd" aria-hidden="divue"></i></span><input value='<?php echo $clients[$z-1]['com']; ?>' class="form-control" type="number" step="any" name="ei_com[]"/>
	</div>
		</label>
	</div>
	</div>
	
	<div class="row" id="<?php echo 'gst'.$z;?>" >
		<div class="col-sm-2 center"><label>Eliteinsure GST Amount: 
		<div class="input-group">
    <span class="input-group-addon">	
		<i class="fa fa-usd" aria-hidden="divue"></i></span><input value='<?php echo $clients[$z-1]['gst']; ?>' class="form-control" type="number" name="ei_gst[]" step="any"/></div></label>
		</div>
	</div>

	<div class="row" id="<?php echo 'rencom'.$z;?>" >
<div class="col-sm-2 center">

		<label>Eliteinsure Renewal Commissions: 
				<div class="input-group">
    <span class="input-group-addon">
		<i class="fa fa-usd" aria-hidden="divue"></i></span><input value='<?php echo $clients[$z-1]['rencom']; ?>' class="form-control" type="number" name="ei_rencom[]" step="any"/><!--new-->
		</div></label>
	</div>
	</div>



	<div class="row" id="<?php echo 'rengst'.$z;?>">
		<div class="col-sm-2 center">
		<label>Eliteinsure Renewal GST:
			 <div class="input-group">
    <span class="input-group-addon">
		<i class="fa fa-usd" aria-hidden="divue"></i></span><input class="form-control" value='<?php echo $clients[$z-1]['rengst']; ?>' type="number" name="ei_rengst[]" step="any"/>
</div>
	</label><!--new-->
	</div>
	</div>
	
	<div class="row" id="<?php echo 'can'.$z;?>" style="display:none";>
		<div class="col-sm-2 center"><label>Eliteinsure Cancellation:
			<div class="input-group">
    <span class="input-group-addon">	
		<i class="fa fa-usd" aria-hidden="divue"></i></span><input class="form-control" type="number" value='<?php echo $clients[$z-1]['cancel_amt']; ?>' name="ei_cancel_amt[]" step="any" /></div></label>
		 </div>
	</div>

	<div class="row" id="<?php echo 'gstcan'.$z;?>" style="display:none";>
		<div class="col-sm-2 center"><label>Eliteinsure GST Cancellation:
				<div class="input-group">
    <span class="input-group-addon">
		<i class="fa fa-usd" aria-hidden="divue"></i></span><input class="form-control" type="number" value='<?php echo $clients[$z-1]['gstcan']; ?>' name="ei_gstcan[]" step="any" /><!--new-->
		</div> </label>
		 </div>
	</div>
		<div class="row" id="<?php echo 'rencan'.$z;?>" style="display:none";>
			<div class="col-sm-2 center">
		<label>Eliteinsure Renewal Cancellation: 	<div class="input-group">
    <span class="input-group-addon">
		<i class="fa fa-usd" aria-hidden="divue"></i></span><input class="form-control" type="number" value='<?php echo $clients[$z-1]['rencan']; ?>' name="ei_rencan[]" step="any" /></div></label></div><!--new-->
	</div>

<!--new-->
	<div class="row" id="rencangst<?php echo $z;?>" style="display:none";>
			<div class="col-sm-2 center">
		<label>Eliteinsure Renewal GST Cancellation: 
				<div class="input-group">
    <span class="input-group-addon">
		<i class="fa fa-usd" aria-hidden="divue"></i></span><input class="form-control" type="number" value='<?php echo $clients[$z-1]['rencangst']; ?>' name="ei_rencangst[]" step="any"/></div></label></div><!--new-->
	</div>
<!--new-->

<!--new-->

	<div class="row" >
		<div class="col-sm-2 center"><label>Annual Premium: 
				<div class="input-group">
    <span class="input-group-addon">
		<i class="fa fa-usd" aria-hidden="divue"></i></span><input class="form-control" type="number"  value='<?php echo $clients[$z-1]['annual_prem']; ?>' name="annual_prem[]" step="any"/></div></label><!--new-->
</div>
	</div><!--new-->


	<div id="viewcancel<?php echo $z;?>" class="row">
	<div class='col-sm-2 center'><label>Client Cancellation:	
	<input id="chk<?php echo $z;?>" class='form-check-input' type='checkbox' name='cancel[]' value='1' /></label>
	</div>
	</div>

<div class="row">
<button class='btn btn-info' id="del<?php echo $z;?>">Clear</button>
</div>

</div>
<?php

};
?>



  <div class="row">
          <div class="col-sm-2 center" >
	<input name="enter" type="submit" value="Update Payroll" style='margin-top: 30px;width: 100%;' class="btn btn-danger center" />
</div>
</div>
</form>


</body>




</body>


</html>

<?php
ob_end_flush();
}
