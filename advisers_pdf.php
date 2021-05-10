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
		"order": [[ 1, "desc" ]],
		"columnDefs": 
		[ {
		"targets": [3,4],
		"orderable": false
		} ]
	});

	$('.note').on('change',function(e){
		e.preventDefault();
		var note=$(this).val();
		var me=$(this);
		var prev=$(this).data('prev');
		var id=$(this).data('id');
		$.confirm({
			title:'Confirm',
			content:'Are you sure you want to save this?',
			buttons:{
				confirm:function(){
					$.ajax({
							url:'save_note.php',
							data:{id:id,note:note},
							type:'POST',
							success:function(e){
								$.confirm(e);
							}
						});
				},
				cancel:function(){
					me.val(prev);
				}	
			}
		});
	});

		$('.desc').on('change',function(e){
		e.preventDefault();
		var desc=$(this).val();
		var me=$(this);
		var prev=$(this).data('prev');
		var id=$(this).data('id');
		$.confirm({
				title:'Confirm',
				content:'Are you sure you want to save this?',
				buttons:{
					confirm:function(){

					$.ajax({
							url:'save_desc.php',
							data:{id:id,desc:desc},
							type:'POST',
							success:function(e){
								$.confirm(e);
							}
						});
					},
					cancel:function(){
						me.val(prev);
					}	
				}

			});


		});


});

</script>
<!--nav bar end-->

<div align="center">

  <div class="jumbotron">
    <h2 class="slide">INVOICE PDF</h2>
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

	$query = "SELECT * FROM pdf_tbl WHERE type='invoice' ORDER BY entrydate DESC";
	$displayquery=mysqli_query($con,$query) or die('Could not look up user information; ' . mysqli_error($con));
?>
<div class="margined table-responsive">
<table id='me' data-toggle="table" id='example' class="table table-striped " cellpadding="5px" cellspacing="5px" width='95%'>
<thead>
<tr>
	<th class='text-center'>Adviser Name</th>
	<th class='text-center'>Filename</th>
	<th class='text-center'>Date Created</th>
	<th class='text-center'>Status</th>
	<th class='text-center'>Notes</th>
	<th class='text-center'><a class="a" id="deleteall" href="delete_pdf.php<?php echo "?id=all&type=bcti"?>"><img src="delete.png" /></a></td>
	<!--td></td-->
	<th>
	<!--	<a class="a_single_email" href="email_pdf.php<?php echo "?id=$id&"?>" ><img src="email.png">-->
		<!--<a class="a_email" href="#" ><img src="email.png">-->
</th>

</tr>
</thead>
<tbody>
<?php

 WHILE($rows = mysqli_fetch_array($displayquery)):
	extract($rows);
	$starting_date=substr($filename,0,8);
	//$entrydate=substr($entrydate,0,4)."/".substr($entrydate,4,2)."/".substr($entrydate,6,2);
	$convertdate=substr($entrydate,6,2)."/".substr($entrydate,4,2)."/".substr($entrydate,0,4);
	$desc=$rows["description"];
	if($entrydate==""){
		$entrydate="N/A";
		$convertdate="N/A";
	}
	echo "
	<tr>
		<td>$name</td>
		<td><a href='$link' class=btn btn-link' target='_blank'>$filename</a></td>
		<td data-order=".$entrydate.">".$convertdate."</td>
		";
?>

	<td>
	<select class='form-control desc' data-id='<?=$id;?>' data-prev='<?=$desc;?>' name='desc' >
		<option value='0' <?php if($desc==0){echo 'selected';}?>>Pending</option>
		<option value='1' <?php if($desc==1){echo 'selected';}?>>Paid</option>
		<option value='2' <?php if($desc==2){echo 'selected';}?>>Contested</option>
		<option value='3' <?php if($desc==3){echo 'selected';}?>>Cancelled</option>
		<option value='4' <?php if($desc==4){echo 'selected';}?>>Waived</option>
		<option value='5' <?php if($desc==5){echo 'selected';}?>>Others</option>
	</select>
	</td>
	<td>
	<input  class='form-control note' data-id='<?=$id;?>' data-prev='<?=$note;?>' type="text" name="note" value="<?php echo $note; ?>">
	</td>

	<td><a class="a_single" href="delete_pdf.php<?php echo "?id=$id&del_pdf=$link"?>" ><img src="delete.png" /></a></td>


<!--td><a href="editbcti.php<?php echo "?adviser_id=$adviser_id&starting_date=$starting_date"?>"><img src="edit.png"></a> </td>
	 </td-->


	<td><a class="a_single_email" href="email_pdf.php<?php echo "?id=$id&"?>" ><img src="email.png"></a>
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