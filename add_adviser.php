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
  
  $(document).ready(function() {
     $(document).on("keyup change", ".api", function(){
        var api = $(this).val();
        api = api.replace(/[^0-9.]/g, "");
        console.log(api);
        $(this).val(api);
    });

  });

</script>
<!--header-->
<div align="center">
<!--header end-->

<!--nav bar-->

<!--nav bar end-->

  <div class="jumbotron">
    <h2 class="slide">Add Adviser</h2>
</div>

<div>

<form method="POST" class="margined">

<div class='row'>
    <div class='col-sm-2'></div>
    <div class='col-sm-2'>
        <label>Team
        <div class="input-group">
          <span class="input-group-addon"><i class="glyphicon glyphicon-star"></i></span>
          <select id="team" class="form-control" name="team" required />
            <option value="0"  selected>None</option>                        
                <?php 

                    $query = "SELECT * from teams ORDER BY name ASC";
                    $displayquery=mysqli_query($con,$query) or die('Could not look up user information; ' . mysqli_error($con));

                    WHILE($rows = mysqli_fetch_array($displayquery)){
                      $id=$rows["id"];
                      $name=$rows["name"];
                      //echo "<option value='".$id."'>".$name."</option>";
                      if($name!="EliteInsure Team")
                        echo "<option value='".$id."'>".$name."</option>";
                    }
                ?>
          </select>
        </div>
        </label>
    </div>
		<div class='col-sm-2'>
<label>Adviser Name	
 <div class="input-group">
    <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
    <input id="email" type="text" class="form-control" name="name" required>
  </div>
</label>
		</div>

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
		<!--div class='col-sm-2'>
<label>IRD number
 <div class="input-group">
    <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
    <input id="email" type="text" class="form-control" name="ird_num" required>
  </div>
</label>
		</div-->


</div>


<div class='row'>
   <div class='col-sm-2'></div>
    <div class='col-sm-2'>
      <label>Company Name
      <div class="input-group">
          <span class="input-group-addon"><i class="glyphicon glyphicon-briefcase"></i></span>
      <input class="form-control" type="text" name="company_name" />
        </div>
      </label>
    </div>

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
 <input class="form-control api" type="text" name="leads" step="any" required/>
  </div>
</label>
    </div>

  <div class='col-sm-2'>
<label>Issue Charge
 <div class="input-group">
    <span class="input-group-addon"><i class="fa fa-usd" aria-hidden="true"></i></span>
 <input class="form-control api" type="text" name="bonus" step="any" required/>
  </div>
</label>
    </div>
   


<div class="row" style="padding-top: 30px;">

  <div class="row">
          <div class="col-sm-2 center" >
	<input name="enter" class="btn btn-info center" type="submit" value="Add Adviser" style="width: 100%;"/>
</div>
</div>

</div>

</form>
<?php
if(isset($_POST["enter"])){
  $team=$_POST["team"];
  $name=$_POST["name"];
  $fsp_num=$_POST["fsp_num"];
  $address=$_POST["address"];
  $ird_num=(isset($_POST["ird_num"])) ? $_POST["ird_num"] : "";
  $myemail=$_POST["myemail"];
  $leads=$_POST["leads"];
  $bonus=$_POST["bonus"];
  $company_name = $_POST["company_name"];


$sql="INSERT INTO adviser_tbl (name,company_name,team_id,fsp_num,address,ird_num,email,leads,bonus) 
VALUES ('$name','$company_name','$team','$fsp_num','$address','$ird_num','$myemail','$leads','$bonus')"; 


if(mysqli_query($con,$sql)){
  
  echo "<script>alert('Adviser successfully added!');</script>";
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