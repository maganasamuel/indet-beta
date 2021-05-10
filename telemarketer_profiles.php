 <?php
session_start();

//Restrict access to admin only
include "partials/admin_only.php";

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
$(function(){
	
/*
$('#me').dataTable({
"columnDefs": [ {
"targets": [14,15],
"orderable": false
} ]
});

*/
});

</script>
</head>

<body>
<div align="center">
  <div class="jumbotron">
    <h2 class="slide">Telemarketer Profiles</h2>
</div>
<?php require "database.php";

  $con=mysqli_connect($host,$username,$password,$db)or die("could not connect to sql");
 if (!$con) {
        echo "<div>";
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
	    echo "</div>";	
}

$query = "SELECT * FROM leadgen_tbl where type='Telemarketer'";
$displayquery=mysqli_query($con,$query) or die('Could not look up user information; ' . mysqli_error($con));
?>
<div class="margined table-responsive">
<table id='me' data-toggle="table" id='example' class="table table-striped " cellpadding="5px" cellspacing="5px" width='95%'>
<thead>
	<td>Lead Generator</td>


	<th><a id="deleteall" class="a" href="delete_leadgen.php?del_id=all">
	<img src="delete.png" />
	</a></th>
	<th></th>


</thead>
<tbody>
<?php

 WHILE($rows = mysqli_fetch_array($displayquery)):
$id=$rows["id"];
$name=$rows["name"];



echo "
<tr cellpadding='5px' cellspacing='5px'>
<td>$name</td>

	";

?>



	<th><a class="a_single" href="delete_telemarketer.php<?php echo "?del_id=$id"?>" ><img src="delete.png" /></a></th>
<th><a href="edit_telemarketer.php<?php echo "?edit_id=$id"?>"><img src="edit.png"></a>	 </th>

 <?php 
 echo "</tr>";	

 endwhile;
 ?>
</tbody>
</table>
</div>
</body>

</html>

<?php

}
?>