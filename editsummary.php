<?php
ob_start();
date_default_timezone_set('Pacific/Auckland');
session_start();
if(!isset($_SESSION["myusername"])){
session_destroy();
header("Refresh:0; url=index.php");
}

if(!isset($_GET["edit_id"])){

header("Refresh:0; url=client_profiles.php");
}
else{
?>

 <html>
<head>
<?php include "partials/nav_bar.html";?>
<!--nav bar end-->
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">
<link rel="stylesheet" href="styles.css">
<link id="favicon" rel="icon" href="Logo_ImageOnly.png" type="image/png" sizes="16x16">
<title>INDET</title>
<script>
$(document).ready(function(){
	$( "#datepicker" ).datepicker({ dateFormat: 'yymmdd' });
	$( "#datepicker2" ).datepicker({ dateFormat: 'yymmdd' });






});
</script>
</head>

<!--header-->
<div align="center">



<!--header end-->

<!--nav bar-->


<!--nav bar end-->


<!--label-->


  <div class="jumbotron">
    <h2 class="slide">Edit Summary</h2>
</div>
<!--label end-->

<?php require "database.php";

  $con=mysqli_connect($host,$username,$password,$db)or die("could not connect to sql");
 if (!$con) {
        echo "<div>";
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
	    echo "</div>";	
}
$edit_id=$_GET["edit_id"];
$query = "SELECT * FROM summary_tbl WHERE id='$edit_id'";
$displayquery=mysqli_query($con,$query) or die('Could not look up user information; ' . mysqli_error($con));
$rows = mysqli_fetch_array($displayquery);

$id=$rows["id"];
$adviser_id=$rows["adviser_id"];
$name=$rows["name"];
$net=$rows["net"];
$gst=$rows["gst"];
$withodingtax=$rows["withodingtax"];
$annual_prem=$rows["annual_prem"];
$payment_amount=$rows["payment_amount"];
$closing_bal=$rows["closing_bal"];
$startingdate=$rows["startingdate"];
$entrydate=$rows["entrydate"];

	?>

<form method="POST">
<div class="center">

		<div class="row">
		<div class="col-sm-2 center"><label style="width: 100%;">Adviser Name

<select name="adviser_id" id="myadviser" class='form-control' required />
<?php 
$query = "SELECT id,name FROM adviser_tbl";
$displayquery=mysqli_query($con,$query) or die('Could not look up user information; ' . mysqli_error($con));

WHILE($rows = mysqli_fetch_array($displayquery)){
$thisid=$rows["id"];
$name=$rows["name"];

if($thisid==$adviser_id){
echo "<option value='$thisid' selected>$name</option>";
}else{

	echo "<option value='$thisid'>$name</option>";
}
	}
	?>
	</select>
</label>
</div>
	</div>


	<div class="row">
		<div class="col-sm-2 center"><label style="width: 100%;">Nett Commissions Paid <input class="form-control" name="net" type="number" step="any" value="<?php echo $net;?>" placeholder="<?php echo $net;?>" required/> </label>
	</div>

	</div>
	<div class="row">
				<div class="col-sm-2 center"><label style="width: 100%;">GST

	<input class="form-control" type="number" type="number" step="any" name="gst" value="<?php echo $gst;?>" placeholder="<?php echo $gst;?>" required/>
		</label>
	</div>
	</div>
		<div class="row">
				<div class="col-sm-2 center"><label style="width: 100%;">Withoding Tax 	

<input class="form-control" type="number" step="any" name="withodingtax" value="<?php echo $withodingtax;?>" placeholder="<?php echo $withodingtax;?>" required/>
		</label>

	</div>
		</div>
	<div class="row">
			<div class="col-sm-2 center"><label style="width: 100%;">Annual Premium
			<input class="form-control" type="number" step="any" name="annual_prem" value="<?php echo $annual_prem;?>" placeholder="<?php echo $annual_prem;?>" required/>
			</label>
			</div>
	</div>
	<div class="row">
				<div class="col-sm-2 center"><label style="width: 100%;">Payment Amount		<input class="form-control" type="number" step="any" name="payment_amount" value="<?php echo $payment_amount;?>" placeholder="<?php echo $payment_amount;?>" required/> </label></div>

	</div>
	<div class="row">
			<div class="col-sm-2 center"><label style="width: 100%;">Closing Balance <input class="form-control" type="number" step="any" name="closing_bal" value="<?php echo $closing_bal;?>" placeholder="<?php echo $closing_bal;?>" required/></label>
	


		</div>
	</div>

	<div class="row">
				<div class="col-sm-2 center"><label style="width: 100%;">Entry Date<input class="form-control" value="<?php echo $entrydate; ?>" type="text" id="datepicker" name="entrydate"></label></div>
	

	</div>
	<div class="row">
					<div class="col-sm-2 center"><label style="width: 100%;">Starting Date<input class="form-control" value="<?php echo $startingdate; ?>" type="text" id="datepicker2" name="startingdate"></label></div>
	</div>


	<div class="row">
					<div class="col-sm-2 center" >
	<input name="enter" type="submit" class="btn btn-info center col-sm-2" value="Update" style="width: 100%;"/>
</div>
</div>

</form>
<?php
if(isset($_POST["enter"])){



$adviser_id=$_POST["adviser_id"];
$net=$_POST["net"];
$gst=$_POST["gst"];
$withodingtax=$_POST["withodingtax"];
$annual_prem=$_POST["annual_prem"];
$payment_amount=$_POST["payment_amount"];
$closing_bal=$_POST["closing_bal"];
$startingdate=$_POST["startingdate"];
$entrydate=$_POST["entrydate"];



$query = "SELECT name FROM adviser_tbl WHERE id='$adviser_id'";
$displayquery=mysqli_query($con,$query) or die('Could not look up user information; ' . mysqli_error($con));
$rows = mysqli_fetch_array($displayquery);
$name=$rows["name"];



require "database.php";

  $con=mysqli_connect($host,$username,$password,$db)or die("could not connect to sql");
 if (!$con) {
        echo "<div>";
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
	    echo "</div>";	
}

$sql="UPDATE summary_tbl SET adviser_id='$adviser_id',name='$name',net='$net',gst='$gst',withodingtax='$withodingtax',annual_prem='$annual_prem',payment_amount='$payment_amount',closing_bal='$closing_bal',entrydate='$entrydate',startingdate='$startingdate'
WHERE id='$id'"; 

/*$ssql="UPDATE pdf_tbl SET name='$name',email='$email'
WHERE adviser_id='$edit_id'"; */


if(mysqli_query($con,$sql)){

	echo "<script>alert('Summary successfully updated!');</script>";
		header("Refresh:0; url=summary.php");
}
}



?>









</div>



</html>

<?php

}
?>