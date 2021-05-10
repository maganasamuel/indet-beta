 <html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">
 <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
 <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
 <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>

<script  src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css">

<script>
$(function(){
	
$('#me').dataTable();


});

</script>
</head>

<body>

<?php require "database.php";

  $con=mysqli_connect($host,$username,$password,$db)or die("could not connect to sql");
 if (!$con) {
        echo "<div>";
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
	    echo "</div>";	
}

$query = "SELECT * FROM adviser_tbl";
$displayquery=mysqli_query($con,$query) or die('Could not look up user information; ' . mysqli_error($con));
?>

<table id='me' data-toggle="table" id='example' class="table table-striped " cellpadding="5px" cellspacing="5px" width='95%'>
<thead>

	<th>Adviser Name</th>
	<th>Adviser FSP number</th>
	<th>Adviser Address</th>
	<th>IRD number</th>
	<th>Commission Percentage</th>
	<th>Tax Rate</th>
	<th>Material/Software Fee</th>
	<th>Agency deduction Percentage</th>
	<th>Renewal Percentage</th>
	<th>GST Registered</th>
	<th>Email Address</th>
	<th>Termination Date</th>

	<th><a  id="deleteall" class="a" href="delete_adviser.php?del_id=all">
		<img src="delete.png" />
	</a></th>


</thead>
<tbody>
<?php

 WHILE($rows = mysqli_fetch_array($displayquery)):
$id=$rows["id"];
$name=$rows["name"];
$fsp_num=$rows["fsp_num"];
$address=$rows["address"];
$ird_num=$rows["ird_num"];
$com_pct=$rows["com_pct"];
$tax_rate=$rows["tax_rate"];
$mat_fee=$rows["mat_fee"];
$agency_pct=$rows["agency_pct"];
$ren_pct=$rows["ren_pct"];
$gst_reg=$rows["gst_reg"];
$term_date=$rows["term_date"];
$email=$rows["email"];
if($term_date==""){
$term_date="N/A";
}

echo "
<tr cellpadding='5px' cellspacing='5px'>
	<td>$name</td>
	<td>$fsp_num</td>
	<td>$address</td>
	<td>$ird_num</td>
	<td>$com_pct%</td>
	<td>$tax_rate%</td>
	<td>$mat_fee</td>
	<td>$agency_pct%</td>
	<td>$ren_pct%</td>
	<td>$gst_reg</td>
	<td>$email</td>
	<td>$term_date</td>
	";

?>



	<td><a class="a" href="delete_adviser.php<?php echo "?del_id=$id"?>" onclick="return confirm('Are you sure you want to delete <?php echo $name."?";?>')"><img src="delete.png" /></a></td>


 <?php 
 echo "</tr>";	

 endwhile;




 ?>
</tbody>
</table>

</body>

</html>