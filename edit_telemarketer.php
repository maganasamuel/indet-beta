<?php session_start();
ob_start(); ?>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">
<link rel="stylesheet" href="styles.css">
<link id="favicon" rel="icon" href="Logo_ImageOnly.png" type="image/png" sizes="16x16">
<title>INDET</title> 


<?php include "partials/nav_bar.html";?>
<head>
<?php

date_default_timezone_set('Pacific/Auckland');

if(!isset($_SESSION["myusername"])){
session_destroy();
header("Refresh:0; url=index.php");
}
else{
?>

</head>
<body>

<script>
$(document).ready(function(){
  //$( "#datepicker" ).datepicker({ dateFormat: 'dd/mm/yy' });
$( "#datepicker" ).datepicker();
});
</script>
<!--header-->
<div align="center">

<!--header end-->
<!--nav bar-->
<!--nav bar end-->
<!--label-->
  <div class="jumbotron">

    <h2 class="slide">Edit Telemarketer</h2>
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
$query = "SELECT * FROM leadgen_tbl WHERE id='$edit_id'";
$displayquery=mysqli_query($con,$query) or die('Could not look up user information; ' . mysqli_error($con));
$rows = mysqli_fetch_array($displayquery);

$id=$rows["id"];
$name=$rows["name"];
/*$fsp_num=$rows["fsp_num"];
$address=$rows["address"];
$ird_num=$rows["ird_num"];
$email=$rows["email"];
$leads=$rows["leads"];
$bonus=$rows["bonus"];*/
?>
<div>

<form method="POST" class="margined">
<div class='row'>
    <div class='col-sm-2'></div>
		<div class='col-sm-2'>
<label>Lead Generator Name	
 <div class="input-group">
    <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
    <input id="email" type="text" class="form-control" name="name" value="<?php echo $name;?>" required>
  </div>
</label>
		</div>
<!--
<div class='col-sm-2'>
<label>Adviser FSP number
 <div class="input-group">
    <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
    <input id="email" type="text" class="form-control" name="fsp_num" value="<?php echo $fsp_num;?>" required>
  </div>
</label>
		</div>
	<div class='col-sm-2'>
<label>Adviser address
 <div class="input-group">
    <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
    <input id="email" type="text" class="form-control" value="<?php echo $address;?>" name="address" required>
  </div>
</label>
		</div>
		<div class='col-sm-2'>
<label>IRD number
 <div class="input-group">
    <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
    <input id="email" type="text" class="form-control" value="<?php echo $ird_num;?>" name="ird_num" required>
  </div>
</label>
		</div>


</div>

<div class='row'>
   <div class='col-sm-2'></div>
    <div class='col-sm-2'>
<label>Email address
 <div class="input-group">
    <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
 <input class="form-control" type="email"  value="<?php echo $email;?>" name="myemail"   />
  </div>
</label>
    </div>

  <div class='col-sm-2'>
<label>Leads
 <div class="input-group">
    <span class="input-group-addon"><i class="fa fa-usd" aria-hidden="true"></i></span>
 <input class="form-control" type="number" name="leads"  value="<?php echo $leads;?>" step="any" required/>
  </div>
</label>
    </div>

  <div class='col-sm-2'>
<label>Issue Bonus
 <div class="input-group">
    <span class="input-group-addon"><i class="fa fa-usd" aria-hidden="true"></i></span>
 <input class="form-control" type="number" name="bonus" value="<?php echo $bonus;?>" step="any" required/>
  </div>
</label>
    </div>
<div class="row" style="padding-top: 30px;">
-->
  <div class="row">
          <div class="col-sm-2 center" >
	<input name="enter" class="btn btn-info center" style="width: 100%;" type="submit" value="Update Lead Generator" />
</div>
</div>


</div>

</form>
<?php
if(isset($_POST["enter"])){
$name=$_POST["name"];




require "database.php";
$con=mysqli_connect($host,$username,$password,$db)or die("could not connect to sql");
 if (!$con) {
        echo "<div>";
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
	    echo "</div>";	
}

$sql="UPDATE leadgen_tbl SET name='$name'
WHERE id='$edit_id'"; 
/*$sql_adv="UPDATE pdf_tbl SET email='$email',name='$name'
WHERE adviser_id='$edit_id'"; 
if(mysqli_query($con,$sql)&&mysqli_query($con,$sql_adv)){
	ob_clean();
		echo "<script>alert('Adviser successfully updated!');</script>";
	header("Refresh:0; url=adviser_profiles.php");

}*/

if(mysqli_query($con,$sql)){
  ob_clean();
    echo "<script>alert('Telemarketer successfully updated!');</script>";
  header("Refresh:0; url=telemarketer_profiles.php");

}


}



?>
</div>
</div>
</body>
</html>
<?php
}
?>