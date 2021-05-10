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
	

$('#me').dataTable({
"order": [[ 2, "desc" ]],
"columnDefs": [ {
"targets": [3,4],
"orderable": false
} ]
});


});

</script>
<!--nav bar end-->

<div align="center">

  <div class="jumbotron">
    <h2 class="slide">Invoice Summary</h2>
</div>
<!--label end-->


<!--modal-->


<!--modal end-->
<!--search-->
<div>



<!--search end-->
<?php require "database.php";

  $con=mysqli_connect($host,$username,$password,$db)or die("could not connect to sql");
 if (!$con) {
        echo "<div>";
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
	    echo "</div>";	
}

$query = "SELECT * FROM pdf_tbl WHERE type='summary' ORDER BY entrydate DESC";
$displayquery=mysqli_query($con,$query) or die('Could not look up user information; ' . mysqli_error($con));
?>
<div class="margined table-responsive">
<table id='me' data-toggle="table" id='example' class="table table-striped " cellpadding="5px" cellspacing="5px" width='95%'>
<thead>
<tr>
	<td>Adviser Name</td>
	<td>Filename</td>
	<td>Date Created</td>
		<td><a class="a" id="deleteall" href="delete_pdf.php<?php echo "?id=all&type=sum"?>"><img src="delete.png" </a></td>
<td></td>

</tr>
</thead>
<tbody>
<?php

 WHILE($rows = mysqli_fetch_array($displayquery)):
$id=$rows["id"];
$adviser_id=$rows["adviser_id"];
$name=$rows["name"];
$email=$rows["email"];
$link=$rows["link"];
$filename=$rows["filename"];
$entrydate=$rows["entrydate"];
$entrydate=substr($entrydate,0,4)."/".substr($entrydate,4,2)."/".substr($entrydate,6,2);

$convertdate=substr($rows["entrydate"],6,2)."/".substr($rows["entrydate"],4,2)."/".substr($rows["entrydate"],0,4);

if($entrydate==""){
$entrydate="N/A";
$convertdate="N/A";
}

echo "
<tr>
	";


if($name=='All Advisers'){
echo "<td style='font-weight:500;color:#2793E6;'>$name</td>";
}
else{
	echo "<td>$name</td>";
}

echo "
	<td><a href='$link' class=btn btn-link'>$filename</a></td>
	<td data-order=".$entrydate.">".$convertdate."</td>

	";

?>



	<td><a class="a_single" href="delete_pdf.php<?php echo "?id=$id&del_pdf=$link&type=sum"?>" ><img src="delete.png" /></a></td>
<td><a class="a_single_email" href="email_pdf.php<?php echo "?id=$id"?>"><img src="email.png"></a>
	 </td>

 <?php 
 echo "</tr>";	

 endwhile;




 ?>
</tbody>
</table>
</div>
</div>

</html>

<?php

}
?>