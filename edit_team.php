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
 $( "#datepicker" ).datepicker({ dateFormat: 'dd/mm/yy' });

 $(document).on("keyup change", ".api", function(){
        var api = $(this).val();
        api = api.replace(/[^0-9.]/g, "");
        console.log(api);
        $(this).val(api);
    });
 //$( "#datepicker" ).datepicker();
});
</script>
<!--header-->
<div align="center">

<!--header end-->
<!--nav bar-->
<!--nav bar end-->
<!--label-->
  <div class="jumbotron">

    <h2 class="slide">Edit Team</h2>
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
$query = "SELECT * FROM teams WHERE id='$edit_id'";
$displayquery=mysqli_query($con,$query) or die('Could not look up user information; ' . mysqli_error($con));
$rows = mysqli_fetch_array($displayquery);

$id=$rows["id"];
$name=$rows["name"];
$leader=$rows["leader"];
?>
<div>

<form method="POST" class="margined">
<div class='row'>
    <div class='col-sm-3'></div>
    
    <div class='col-sm-3'>
        <label>Team Name	
        <div class="input-group">
            <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
            <input id="team_name" type="text" class="form-control" name="team_name" value ="<?php echo $name ?>" required>
        </div>
        </label>
    </div>

    <div class='col-sm-3'>
        <label>Leader
        <div class="input-group">
          <span class="input-group-addon"><i class="glyphicon glyphicon-star"></i></span>
          <select id="leader" class="form-control" name="leader" required />
            <option value="0" >None</option>                        
                <?php 

                    $query = "SELECT * from adviser_tbl ORDER BY name ASC";
                    $displayquery=mysqli_query($con,$query) or die('Could not look up user information; ' . mysqli_error($con));

                    WHILE($rows = mysqli_fetch_array($displayquery)){
                      $t_id=$rows["id"];
                      $name=$rows["name"];
                      $selected = ($t_id==$leader) ? "selected" : "";
                    //echo "<option value='".$id."'>".$name."</option>";
                    echo "<option $selected value='".$t_id."'>".$name."</option>";
                    }
                ?>
          </select>
        </div>
        </label>
    </div>
		<!--div class='col-sm-2'>
<label>IRD number
 <div class="input-group">
    <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
    <input id="email" type="text" class="form-control" value="<?php echo $ird_num;?>" name="ird_num" required>
  </div>
</label>
		</div-->


        </div>
</div>

<div class="row" style="padding-top: 30px;">

  <div class="row">
          <div class="col-sm-2 center" >
	<input name="enter" class="btn btn-info center" style="width: 100%;" type="submit" value="Update Team" />
</div>
</div>
</div>

</form>
<?php
if(isset($_POST["enter"])){
$name= addslashes($_POST["team_name"]);
$leader=$_POST["leader"];


require "database.php";
$con=mysqli_connect($host,$username,$password,$db)or die("could not connect to sql");
 if (!$con) {
        echo "<div>";
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
	    echo "</div>";	
}

$sql="UPDATE teams SET name='$name', leader='$leader' WHERE id='$edit_id'"; 
/*$sql_adv="UPDATE pdf_tbl SET email='$email',name='$name'
WHERE adviser_id='$edit_id'"; 
if(mysqli_query($con,$sql)&&mysqli_query($con,$sql_adv)){
	ob_clean();
		echo "<script>alert('Adviser successfully updated!');</script>";
	header("Refresh:0; url=adviser_profiles.php");

}*/

if(mysqli_query($con,$sql)){
  ob_clean();
    echo "<script>alert('Team successfully updated!');</script>";
  header("Refresh:0; url=teams.php");

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