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
</head>
<?php 
require "database.php";
 

if(!isset($_SESSION["myusername"])){
session_destroy();
header("Refresh:0; url=index.php");
}

else{
?>
<script type="text/javascript">
  
  $(function() {


  });

</script>
<!--header-->
<div align="center">
<!--header end-->

<!--nav bar-->

<!--nav bar end-->

  <div class="jumbotron">
    <h2 class="slide">Add Lead Generator</h2>
</div>

<div>

<form method="POST" class="margined">

<div class='row'>
    <div class='col-sm-2'></div>
		<div class='col-sm-2'>
<label>Lead Generator Name	
 <div class="input-group">
    <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
    <input id="text" type="text" class="form-control" name="name" required>
  </div>
</label>
		</div>
<!--
<div class='col-sm-2'>
<label>Adviser FSP number
 <div class="input-group">
    <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
    <input id="email" type="text" class="form-control" name="fsp_num" required>
  </div>
</label>
		</div>
	<div class='col-sm-2'>
<label>Adviser address
 <div class="input-group">
    <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
    <input id="email" type="text" class="form-control" name="address" required>
  </div>
</label>
		</div>
		<div class='col-sm-2'>
<label>IRD number
 <div class="input-group">
    <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
    <input id="email" type="text" class="form-control" name="ird_num" required>
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
 <input class="form-control" type="email" name="myemail" />
  </div>
</label>
    </div>

  <div class='col-sm-2'>
<label>Leads
 <div class="input-group">
    <span class="input-group-addon"><i class="fa fa-usd" aria-hidden="true"></i></span>
 <input class="form-control" type="number" name="leads" step="any" required/>
  </div>
</label>
    </div>

  <div class='col-sm-2'>
<label>Issue Bonus
 <div class="input-group">
    <span class="input-group-addon"><i class="fa fa-usd" aria-hidden="true"></i></span>
 <input class="form-control" type="number" name="bonus" step="any" required/>
  </div>
</label>
    </div>
   

-->
<div class="row" style="padding-top: 30px;">

  <div class="row">
          <div class="col-sm-2 center" >
	<input name="enter" class="btn btn-info center" type="submit" value="Add Lead Generator" style="width: 100%;"/>
</div>
</div>

</div>

</form>
<?php
if(isset($_POST["enter"])){
$name=$_POST["name"];



$sql="INSERT INTO leadgen_tbl (name,type) 
VALUES ('$name','Face-to-Face Marketer')"; 


if(mysqli_query($con,$sql)){
  
  echo "<script>alert('Lead Generator successfully added!');</script>";
  header("Refresh:0");
  ob_end_flush();
  
}
else{
 echo("Error description: " . mysqli_error($con));
}
}



?>
</div>




</html>

<?php

}
?>