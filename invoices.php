
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
		"orderable": true
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


		$(".email_invoice").on("click", function(){
			var invoice_id = $(this).data("id");
			var invoice_num = $(this).data("number");
			var emailurl = "email_invoice.php?id=" + invoice_id;
			$(this).prop('disabled', true);
			$.confirm({
				title: 'Confirm Sending Invoice',
				content: "Are you sure that you want to send invoice " + invoice_num + "?",
				buttons: {
						confirm: function () {    											
							$.ajax({
												type: "get",
												url: emailurl,
												success: function(data){
														console.log("Feedback: ", data);
														//alert("Invoice " + invoice_num + " Sent");
														$(this).prop('disabled', false);
												},
												error: function(data){
													console.log("Error Sending Mail", data);
													//alert("Invoice " + invoice_num + " Sent");
													//alert("An error occurred, please contact the IT Support.");
													$(this).prop('disabled', false);
												}
										});
						},
						cancel: function () {
				
						},

				}
			});
		})

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

	$query = "SELECT *, i.id as invoice_id, a.id as adviser_id FROM invoices i LEFT JOIN adviser_tbl a ON i.adviser_id = a.id ORDER BY date_created DESC";
	$displayquery=mysqli_query($con,$query) or die('Could not look up user information; ' . mysqli_error($con));
?>
<div class="margined table-responsive">
<table id='me' data-toggle="table" id='example' class="table table-striped " cellpadding="5px" cellspacing="5px" width='95%'>
<thead>
<tr>
	<th class='text-center'>Invoice Number</th>
	<th class='text-center'>Date Created</th>
	<th class='text-center'>Adviser Name</th>
	<th class='text-center'>Amount</th>
	<!--<th class='text-center'>Status</th>-->
	<th class='text-center'>Notes</th>
	<th></th>
	<th class='text-center'></th>
	<!--td></td-->
	<th></th>
</tr>
</thead>
<tbody>
<?php

 WHILE($rows = mysqli_fetch_array($displayquery)):
	extract($rows);


	$convertdate=substr($date_created,6,2)."/".substr($date_created,4,2)."/".substr($date_created,0,4);
	$desc=$status;

	if($date_created==""){
		$date_created="N/A";
		$convertdate="N/A";
	}
	echo "
	<tr>
		<td>$number</td>
		<td data-order=".$date_created.">".$convertdate."</td>
		<td>$name</td>
		<td>$" . number_format($amount,2,".",",") . "</td>
		";
?>
	<!--
	<td>
	<select class='form-control desc' data-id='<?=$invoice_id;?>' data-prev='<?=$desc;?>' name='desc'>
		<option <?php if($desc=="Pending"){ echo "selected"; } ?> >Pending</option>
		<option <?php if($desc=="Paid"){ echo "selected"; } ?> >Paid</option>
		<option <?php if($desc=="Contested"){ echo "selected"; } ?> >Contested</option>
		<option <?php if($desc=="Cancelled"){ echo "selected"; } ?> >Cancelled</option>
		<option <?php if($desc=="Waived"){ echo "selected"; } ?> >Waived</option>
		<option <?php if($desc=="Others"){ echo "selected"; } ?> >Others</option>
	</select>
	</td>
	-->
	<td>
		<input  class='form-control note' data-id='<?=$invoice_id;?>' data-prev='<?=$note;?>' type="text" name="note" value="<?php echo $note; ?>">
	</td>
	<td><a class="a_single_view btn btn-primary" target="_blank" href="invoice<?php echo "?id=$invoice_id"?>" ><span class="glyphicon glyphicon-search" style="font-size:15px;"></span>
	</a></td>

	<td><a class="a_single" href="delete_pdf.php<?php echo "?invoice_id=$invoice_id"?>" ><img src="delete.png" /></a></td>


<!--td><a href="editbcti.php<?php echo "?adviser_id=$adviser_id&starting_date=$starting_date"?>"><img src="edit.png"></a> </td>
	 </td-->


	 <td><a class="email_invoice" href="#" data-id="<?php echo "$invoice_id"?>" data-number="<?php echo "$number"?>" data><img src="email.png"></a>
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