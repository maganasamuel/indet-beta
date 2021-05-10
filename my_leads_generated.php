<?php
session_start();
date_default_timezone_set('Pacific/Auckland');

require "database.php";
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
	


	$('.datepicker').datepicker({
		dateFormat: 'dd/mm/yy'
	});


$('#me').dataTable({
  "columns": [
    { "width": "1%" },
    { "width": "1%" },
    { "width": "1%" },
    { "width": "1%" },
    { "width": "1%" },
    { "width": "1%" },
    { "width": "1%" },
    { "width": "1%" },
    { "width": "1%" },
    { "width": "1%" },
    { "width": "1%" },
    { "width": "1%" },
    { "width": "1%" }
  ]
});


});

</script>
<!--header-->
<body>

<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header" style="background-color: #286090; ">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span style="color:white;" aria-hidden="true">×</span></button>
                    <h4 class="modal-title" id="myModalLabel" style="color:white;">Callback Editor</h4>
                </div>
                <div class="modal-body">
                    <form id="frmCallback" name="frmScript" class="form-horizontal" novalidate="">                    
                        <div class="form-group">
                            <div class="col-sm-3">
                                <label for="callback_date">Date</label>
                            </div>
                            <div class="col-sm-9">
                                <input type="text" id="callback_date" name="callback_date" class="form-control datepicker" aria-describedby="callback_date" placeholder="Date" value="<?php echo date('d/m/Y')?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-3">
                                <label for="date">Time</label>
                            </div>
                            <div class="col-sm-9">
                                <input type="time" id="callback_time" name="callback_time" class="form-control">
                            </div>
                        </div>
						
                        <div class="form-group">
                            <div class="col-sm-3">
                                <label for="notes">Notes</label>
                            </div>
                            <div class="col-sm-9">
                                <textarea class="form-control" id="notes" name="notes" aria-describedby="notes" placeholder="Notes"></textarea>
                            </div>
                        </div>
                        <button type="button" id="save_callback" class="btn btn-success form-control"><i class="fas fa-save"></i> Save Callback</button>
                    
                    </div>
                        <input type="hidden" id="lead_data_id" name="lead_data_id" value="">
                        <input type="hidden" id="client_id" name="client_id" value="">
                    </form>
                </div>
            </div>
        </div>
    </div>
<div align="center">


<!--header end-->

<!--nav bar-->


<!--nav bar end-->


<!--label-->

  <div class="jumbotron">
    <h2 class="slide">Leads Generated</h2>
</div>
<!--label end-->

<!--modal-->

<div id="myModal" class="modal">


  <div class="modal-content" >
    <span class="close">&times;</span>
    <p>Please confirm to delete all</p>

   	<input type="password" id="confirmpassword" class="addadviser"  placeholder="Password" autocomplete="new-password"/><br style="height:50px;">
   	<br style=" display: block;margin: 10px 0;">
    <input type="button" id="confirmbutton" value="Delete All" style="width: 20%;" />
  </div>

</div>




<?php




function convertNum($x){

return number_format($x, 2, '.', ',');
}


  $con=mysqli_connect($host,$username,$password,$db)or die("could not connect to sql");
 if (!$con) {
        echo "<div>";
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
	    echo "</div>";	
}
$last = date("Ymd");
$last = DateTime::createFromFormat('Ymd',$last);
$last->modify('-6 days');

$last = $last->format("Ymd");
$query = "SELECT *, u.id as user_id, l.id as telemarketer_id, u.type as usertype, l.type as leadgen_type FROM users u LEFT JOIN leadgen_tbl l ON u.linked_id = l.id WHERE u.id = " . $_SESSION['myuserid'];
$result = mysqli_query($con, $query);
$agent = (object) mysqli_fetch_assoc($result);
$linked_id = $agent->linked_id;

$query = "SELECT clients_tbl.lead_by as lead_by, ld.data as lead_data, ld.id as lead_data_id, clients_tbl.city as city, clients_tbl.zipcode as zipcode, clients_tbl.date_submitted,clients_tbl.id,clients_tbl.name,adviser_tbl.name as x,leadgen_tbl.name as y,clients_tbl.appt_date,clients_tbl.appt_time,clients_tbl.address,clients_tbl.leadgen,clients_tbl.assigned_to,clients_tbl.assigned_date,clients_tbl.type_of_lead,clients_tbl.issued,clients_tbl.date_issued,clients_tbl.notes 
 FROM clients_tbl LEFT JOIN adviser_tbl ON clients_tbl.assigned_to = adviser_tbl.id LEFT JOIN leadgen_tbl ON clients_tbl.leadgen = leadgen_tbl.id LEFT JOIN leads_data ld ON ld.client_id = clients_tbl.id WHERE binned=0 AND clients_tbl.leadgen = $linked_id AND clients_tbl.appt_date > $last order by clients_tbl.date_issued desc;";
$displayquery=mysqli_query($con,$query) or die('Could not look up user information; ' . mysqli_error($con));

?>

<div class="margined">
<table id='me' data-toggle="table" class="table table-striped" cellpadding="5px" cellspacing="5px" width='100%' style=" display: block; ">

<thead>
	<td>Client Name</td>
	<td>Appt Date</td>
	<td>Phone Number</td>
	<td>Address</td>
	<td>City</td>
	<td>Zip Code</td>
	<td>Assigned to</td>
	<td>Assigned Date</td>
	<!--td>Type of Lead</td>
	<td>Issued</td>-->
	<td>Date Submitted</td>
	<td>Notes</td>
	<td>Grade</td>
	<td>Lead's Data</td>
	<td>Convert To Callback</td>


</thead>
<tbody>
<?php

 WHILE($rows = mysqli_fetch_array($displayquery)):
$id=$rows["id"];
$name=$rows["name"];
$x=$rows["x"]; //advisername
$y=$rows["y"]; //leadgenname
	$search_lead_gen = "";
$appt_date=$rows["appt_date"];
$appt_time=$rows["appt_time"];
$address=$rows["address"];
$city=$rows["city"];
$zipcode=$rows["zipcode"];
$lead_by=$rows["lead_by"];
$leadgen=$rows["leadgen"];
$assigned_to=$rows["assigned_to"];
$assigned_date=$rows["assigned_date"];
$type_of_lead=$rows["type_of_lead"];
$issued=$rows["issued"];
$date_issued=$rows["date_issued"];
$notes=$rows["notes"];
$lead_data= json_decode($rows["lead_data"]);
$date_submitted=$rows["date_submitted"];
$lead_data_id=$rows["lead_data_id"];
$date_submitted_sort = $date_submitted;
$date_submitted = substr($date_submitted, 6,2) . "/" . substr($date_submitted, 4,2) . "/" . substr($date_submitted, 0,4);


$appt_date=date('d/m/Y',strtotime($appt_date));
if($assigned_date!=""){
	$assigned_date=date('d/m/Y',strtotime($assigned_date));
}
else{
	$x = "Unassigned";
	$assigned_date = "Unassigned";
}
$grade = (isset($lead_data->grade)) ? $lead_data->grade : "N/A";
/*$entrydate=$rows["entrydate"];
$startingdate=$rows["startingdate"];
$entrydate=substr($entrydate,0,4)."/".substr($entrydate,4,2)."/".substr($entrydate,6,2);

$startingdate=substr($startingdate,0,4)."/".substr($startingdate,4,2)."/".substr($startingdate,6,2);


$convertdate=substr($rows["entrydate"],6,2)."/".substr($rows["entrydate"],4,2)."/".substr($rows["entrydate"],0,4);

$convertstartingdate=substr($rows["startingdate"],6,2)."/".substr($rows["startingdate"],4,2)."/".substr($rows["startingdate"],0,4);
*/
$lg = "";
if($lead_by=="Self-Generated"){
	$lg = $x;
}
else{
	$lg = $y;
}
echo "
<tr id='lead$id' cellpadding='5px' cellspacing='5px'>

	<td>$name</td>
	<td>$appt_date</td>
	<td>$appt_time</td> 
	<td>$address</td>
	<td>$city</td>
	<td>$zipcode</td>
	<td>$x</td>
	<td>$assigned_date</td>
	<td data-order=".$date_submitted_sort.">$date_submitted</td>
	<td>$notes</td>
	<td>$grade</td>";

	$href="";
	$css ="";
	if($lead_data_id!=''){
			$href= "href='leads_data_pdf?id=$lead_data_id'";
	}
	else{
		$css = "style='color:gray;'";
	}

	echo "
		<td><a $href target='_blank' $css><i class='fas fa-file-pdf' style='font-size:30px;'></i></a></td>
	";

	$href="";
	$css ="";
	if($lead_data_id!=''){
			$href= "data-toggle='modal' data-target='#myModal'";
			$css = "style='color:AA0000;'";
	}
	else{
		$css = "style='color:gray;'";
	}
	//href='convert_appt_to_callback?id=$lead_data_id' 

	echo "
		<td>
			<button $href class='btn btn-link convert_to_callback' data-id='$id' value='$lead_data_id' $css>
				<i class='fas fa-undo' style='font-size:30px;'></i>
			</button>
		</td>
	";

?>



 <?php 
 echo "</tr>";	

 endwhile;




 ?>
</tbody>
</table>
</div>
</div>

<script>
	$(function(){
		$(document).on("click",".convert_to_callback", function(){
			var client_id = $(this).data("id");
			var lead_data_id = $(this).val();
			console.log("clicked client " + client_id + " lead_data_id " + lead_data_id);
			$("#client_id").val(client_id);
			$("#lead_data_id").val(lead_data_id);			
		});


		$("#save_callback").on("click", function(){
			var data = $("#frmCallback").serialize();
			$.ajax({
				data: data,
				type: "post",
				url: "convert_appt_to_callback.php",
				success: function(data){
					$("#lead" + data).remove();					
                    $('#myModal').modal('hide');			
				},
				error: function(data){
					console.log("Error", data);
					alert("An error occurred, please contact the IT Support.");
				}
			});
		})
	});
</script>
</body>
</html>

<?php

}
?>