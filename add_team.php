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
    <h2 class="slide">Add Team</h2>
</div>

<div>

<form method="POST" class="margined">

<div class='row'>
    <div class='col-sm-3'></div>

    <div class='col-sm-3'>
        <label>Team Name	
        <div class="input-group">
            <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
            <input id="team_name" type="text" class="form-control" name="team_name" required>
        </div>
        </label>
    </div>

    <div class='col-sm-3'>
        <label>Team Leader
        <div class="input-group">
          <span class="input-group-addon"><i class="glyphicon glyphicon-star"></i></span>
          <select id="leader" class="form-control" name="leader" required />
            <option value="" disabled hidden selected>Select Adviser</option>                        
                <?php 

                    $query = "SELECT * from adviser_tbl ORDER BY name ASC";
                    $displayquery=mysqli_query($con,$query) or die('Could not look up user information; ' . mysqli_error($con));

                    WHILE($rows = mysqli_fetch_array($displayquery)){
                    $id=$rows["id"];
                    $name=$rows["name"];
                    //echo "<option value='".$id."'>".$name."</option>";
                    echo "<option value='".$id."'>".$name."</option>";
                    }
                ?>
          </select>
        </div>
        </label>
    </div>
</div>


<div class="row" style="padding-top: 30px;">

  <div class="row">
          <div class="col-sm-2 center" >
	<input name="enter" class="btn btn-info center" type="submit" value="Create Team" style="width: 100%;"/>
</div>
</div>

</div>

</form>
<?php
if(isset($_POST["enter"])){
$name= addslashes($_POST["team_name"]);
$leader=$_POST["leader"];



$sql="INSERT INTO teams (name,leader) VALUES ('$name','$leader')"; 


if(mysqli_query($con,$sql)){
  
  echo "<script>alert('Team successfully added!');</script>";
  header("Refresh:0");
  ob_end_flush();
  
}
else{
 echo "SQL Query: " . $sql . "<hr>";
 echo("Error description: " . mysqli_error($con));
}
}



?>
</div>




</html>

<?php

}
?>