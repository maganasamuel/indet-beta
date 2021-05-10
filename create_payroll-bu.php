 <html>
<head>
<link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">
<link rel="stylesheet" href="styles.css">
<link id="favicon" rel="icon" href="Logo_ImageOnly.png" type="image/png" sizes="16x16">
<title>INDET</title> 
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script>
$(document).ready(function(){
	$( "#datepicker" ).datepicker();
	var x=1;
	var client="#client";
	var sel="#sel";
	var txt="#txt";
	var myadviser="#myadviser";
	var openingbal="#openingbal";
	
 	var txtconcat = txt.concat(x);

	$("#addclient").click(function(){
    x++;
    txtconcat = txt.concat(x)
    clientconcat = client.concat(x);
    $(clientconcat).show();
    alert(selconcat);
   	});

	$(".remove").click(function(){

    clientconcat = client.concat(x);

    $(clientconcat).hide();
    $(txtconcat).val("");
    x-=1;
    txtconcat = txt.concat(x)
    clientconcat = client.concat(x);

	});

	$(myadviser).change(function(){
	var selected=$(myadviser).find(":selected").text();
	
	$.ajax({
	url:'getclosing.php',
	data:'adviser='+selected,
	success:function(data){
   $(openingbal).val(data);
	}

	})



	});




	$(sel).change(function(){
	var conceptName = $(sel).find(":selected").text();
   $(txtconcat).val(conceptName);
	});


});
</script>

</head>
<body>


<?php
date_default_timezone_set('Pacific/Auckland');
session_start();
unset($_SESSION['adviser_id']);
if(!isset($_SESSION["myusername"])){
session_destroy();
header("Refresh:0; url=index.php");
}

else{
?>
<!--header-->
<div align="center">

<img src="logo.png">

<!--header end-->

<!--nav bar-->
<?php include "partials/nav_bar.html";?>
<!--nav bar end-->

<!--label-->
<div>
	<p><strong>Create</strong> Payroll</p>
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

<form method="POST" action="output.php">
<table align="center" bgcolor="ededed" cellpadding="5px">
<tr class="headers">
	<td>Adviser:</td>
<td><select name="adviser_id" id="myadviser">
  <option value="" disabled selected>Select Adviser</option>
<?php 

$query = "SELECT id,name FROM adviser_tbl";
$displayquery=mysqli_query($con,$query) or die('Could not look up user information; ' . mysqli_error($con));

WHILE($rows = mysqli_fetch_array($displayquery)){
$id=$rows["id"];
$name=$rows["name"];


echo "

	<option value=$id>$name</option>";

}
?>
<?php
$query = "SELECT name,closing_bal FROM summary_tbl";
$displayquery=mysqli_query($con,$query) or die('Could not look up user information; ' . mysqli_error($con));

WHILE($rows = mysqli_fetch_array($displayquery)){
$id=$rows["id"];

}





?><!--<td><input class="addadviser" type="text" id="datepicker" name="mydate" required></td>-->
		</select>
		<td>Date Period: </td>

<td>
<select name="mymonth" required>
<option value="1">January</option>
<option value="2">February</option>
<option value="3">March</option>
<option value="4">April</option>
<option value="5">May</option>
<option value="6">June</option>
<option value="7">July</option>
<option value="8">August</option>
<option value="9">September</option>
<option value="10">October</option>
<option value="11">November</option>
<option value="12">December</option>
</select>

<select name="mydate" required>
<option value="1"><?php echo " 1-15";?></option>
<option value="15"><?php echo " 15-31";?></option>
</select>

<?php
$already_selected_value = date("Y");
$earliest_year = 1980;

print '<select name="myyear" required>';
foreach (range(date('Y'), $earliest_year) as $x) {
    print '<option value="'.$x.'"'.($x === $already_selected_value ? ' selected="selected"' : '').'>'.$x.'</option>';
}
print '</select>';


?>

</td>

</tr>
<tr class="headers">
		<td>Opening Balance: </td>
	<td><input class="addadviser" id="openingbal" type="number" name="openbal" step="any" required/></td><!--new-->

<td>Sundries: </td>
		<td><input class="addadviser" type="number" name="sundries" step="any" required/></td>
<!--here-->
</tr>
<tr class="headers">
		<td>Bonuses: </td>
		<td><input class="addadviser" type="number" name="bonuses" step="any" required/></td><!--new-->

<td>Agency Release: </td>

	<td><input class="addadviser" type="number" name="agencyrelease" step="any"/></td><!--new-->




	</tr>


<tr class="headers">

<td colspan="2">Add Existing Client:</td>

<td>

 <select name='existing_client' id="sel">
  <option value="" disabled selected>Select existing client</option>
<?php
$query = "SELECT DISTINCT name FROM clients_tbl";
$displayquery=mysqli_query($con,$query) or die('Could not look up user information; ' . mysqli_error($con));
WHILE($rows = mysqli_fetch_array($displayquery)){
$name=$rows["name"];
echo "
	<option value=$id >$name</option>";
}
?>
		</select>
</td>
</tr>





</td></tr>
</table>


<table>
	
	<tr class="headers"><td><a class="a" id="addclient" style="float:left;" /><img src="plus.png"></a>
	Client Name</td>

	
		<td>
    	
    	<input id="txt1" type="text" class='addadviser' name="client_name[]" />
 </td>
 </tr>


	<tr class="headers">
		<td>Eliteinsure Commissions</td>
		<td><input class="addadviser" type="text" name="ei_com[]" required/></td>
	</tr>
	
	<tr class="headers">
		<td>Eliteinsure GST Amount</td>
		<td><input class="addadviser" type="number" name="ei_gst[]" step="any" required/></td>
	</tr>
	<tr class="headers">

		<td>Eliteinsure Renewal Commissions</td>
		<td><input class="addadviser" type="number" name="ei_rencom[]" step="any" required/></td><!--new-->
	</tr>


	<tr class="headers">
		<td>Eliteinsure Renewal GST</td>
		<td><input class="addadviser" type="number" name="ei_rengst[]" step="any" required/></td><!--new-->
	</tr>
	
	<tr class="headers">
		<td>Eliteinsure Cancellation</td>
		<td><input class="addadviser" type="number" name="ei_cancel_amt[]" step="any" required/></td>
	</tr>

	<tr class="headers">
		<td>Eliteinsure GST Cancellation</td>
		<td><input class="addadviser" type="number" name="ei_gstcan[]" step="any" required/></td><!--new-->
	</tr>
		<tr class="headers">
		<td>Eliteinsure Renewal Cancellation</td>
		<td><input class="addadviser" type="number" name="ei_rencan[]" step="any" required/></td><!--new-->
	</tr>

<!--new-->
	<tr class="headers">
		<td>Eliteinsure Renewal GST Cancellation</td>
		<td><input class="addadviser" type="number" name="ei_rencangst[]" step="any"/></td><!--new-->
	</tr>
<!--new-->


	<tr class="headers">
		<td>Annual Premium</td>
		<td><input class="addadviser" type="number" name="annual_prem[]" step="any" required/></td><!--new-->
	</tr><!--new-->


	<tr class="headers">
	<td>Client Cancellation</td>
	<td>
		<input type='checkbox' name='cancel[]' value='1' /><br>
	</td>
	</tr>

</table>


<?php 
for($z=2;$z<20;$z++){

echo "	
<table id='client".$z."' style='display: none;'>
<tr class='headers'>
		<td><a class='remove' style='float:left;' /><img class='a' src='minus.png'></a>Client Name</td>
		<td>
    	<input id='txt".$z."' type='text' class='addadviser' name='client_name[]' />

 </td></tr>
 ";
?>
	
	<tr class="headers" >
		<td>Eliteinsure Commissions</td>
		<td><input class="addadviser" type="text" name="ei_com[]"/></td>
	</tr>
	
	<tr class="headers">
		<td>Eliteinsure GST Amount</td>
		<td><input class="addadviser" type="number" name="ei_gst[]" step="any"/></td>
	</tr>
	<tr class="headers">

		<td>Eliteinsure Renewal Commissions</td>
		<td><input class="addadviser" type="number" name="ei_rencom[]" step="any"/></td><!--new-->
	</tr>
	<tr class="headers">
		<td>Eliteinsure Renewal GST</td>
		<td><input class="addadviser" type="number" name="ei_rengst[]" step="any"/></td><!--new-->
	</tr>
	
	<tr class="headers">
		<td>Eliteinsure Cancellation</td>
		<td><input class="addadviser" type="number" name="ei_cancel_amt[]" step="any"/></td>
	</tr>
	<tr class="headers">
		<td>Eliteinsure GST Cancellation</td>
		<td><input class="addadviser" type="number" name="ei_gstcan[]" step="any"/></td><!--new-->
	</tr>
	<tr class="headers">
		<td>Eliteinsure Renewal Cancellation</td>
		<td><input class="addadviser" type="number" name="ei_rencan[]" step="any"/></td><!--new-->
	</tr>
<!--new-->
	<tr class="headers">
		<td>Eliteinsure Renewal GST Cancellation</td>
		<td><input class="addadviser" type="number" name="ei_rencangst[]" step="any"/></td><!--new-->
	</tr>
<!--new-->

	<tr class="headers">
		<td>Annual Premium</td>
		<td><input class="addadviser" type="number" name="annual_prem[]" step="any"/></td><!--new-->
	</tr><!--new-->
	<tr class="headers">
	<td>Client Cancellation</td>
	<td>
		<input type='checkbox' name='cancel[]' value='1' /><br>
	</td>
	</tr>
</table>
<?php
};
?>




	<input name="enter" type="submit" value="Create Payroll" />
</form>


</body>


</html>

<?php

}
