 <?php
session_start();
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

$('.checkme').on('click',function(){
var id=$(this).attr('data-id');
var com=$(this).attr('data-com');
var me=$(this);
});

$('.table').dataTable();





});


</script>
<!--header-->
<div align="center">


<!--header end-->

<!--nav bar-->


<!--nav bar end-->


<!--label-->

  <div class="jumbotron">
    <h2 class="slide">Arrears Deals</h2>
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


 require "database.php";


function convertNum($x){

return number_format($x, 2, '.', ',');
}


  $con=mysqli_connect($host,$username,$password,$db)or die("could not connect to sql");
 if (!$con) {
        echo "<div>";
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
	    echo "</div>";	
}

$query = "SELECT s.deals, c.id as client_id, c.lead_by, i.id,c.name,a.name as x,l.name as y,i.appt_date,i.appt_time,i.address,i.leadgen,i.assigned_to,i.assigned_date,i.type_of_lead,i.issued,i.date_issued,i.notes FROM submission_clients s LEFT JOIN issued_clients_tbl i ON s.client_id = i.name LEFT JOIN adviser_tbl a ON i.assigned_to = a.id LEFT JOIN leadgen_tbl l ON i.leadgen = l.id LEFT JOIN clients_tbl c ON i.name = c.id order by i.date_issued desc";


$displayquery=mysqli_query($con,$query) or die('Could not look up user information; ' . mysqli_error($con));

$displayquery2=mysqli_query($con,$query) or die('Could not look up user information; ' . mysqli_error($con));

$displayquery3=mysqli_query($con,$query) or die('Could not look up user information; ' . mysqli_error($con));
?>

<div class="margined table-responsive">
		
	<ul class="nav nav-tabs">
	<li class="active"><a data-toggle="tab" href="#arrears" style="color:#0c4664 !important;">Arrears</a></li>
	<li><a data-toggle="tab" href="#possible" style="color:#0c4664 !important;">Possible Cancellations</a></li>
	<li><a data-toggle="tab" href="#cancelled" style="color:#0c4664 !important;">Cancelled</a></li>
	</ul>
	
<div class="tab-content">
  <div id="arrears" class="tab-pane fade in active">
    <div class="row">
        <div class="col-sm-12 text-center">
            <h1>Arrears</h1>            
				<table id='arrears_tbl' data-toggle="table"  class="table table-striped " cellpadding="5px" cellspacing="5px" width='95%'>
					<thead>
						<td>Client Name</td>
						<td>Adviser</td>
						<td>Policy Number</td>
						<td>Notes</td>
						<td></td>
					</thead>

					<tbody>
					<?php

					while($rows = mysqli_fetch_array($displayquery)){

						if($rows["name"]==null)
							continue;

						$client_id = $rows["client_id"];
						$id=$rows["id"];
						$name=$rows["name"];
						$x=$rows["x"]; //advisername
						$y=$rows["y"]; //leadgenname
						$lead_by=$rows["lead_by"];
						$appt_date=$rows["appt_date"];
						$appt_time=$rows["appt_time"];
						$address=$rows["address"];
						$leadgen=$rows["leadgen"];
						$assigned_to=$rows["assigned_to"];
						$assigned_date=$rows["assigned_date"];
						$type_of_lead=$rows["type_of_lead"];
						$deals = json_decode($rows["deals"]);

						//<td>$".number_format((float)$api,2)."</td>	api
						foreach($deals as $deal){
							if($deal->status!="None"){
								if(isset($deal->clawback_status)){
									if($deal->clawback_status=="Arrears"){
										$api=$deal->clawback_api;
										if(!empty($deal->life_insured))
											$name .= ", " . $deal->life_insured;

										$policy_number = $deal->policy_number;
										$date_cancelled=$deal->clawback_date;
										$notes=$deal->clawback_notes;

										$date_cancelled=date('d/m/Y',strtotime($date_cancelled));

										$lg = $y;
										if($lead_by=="Self-Generated"){
											$lg = $x;
										}

										echo "
										<tr cellpadding='5px' cellspacing='5px'>

										<td>$name</td>
										<td>$x</td>
										<td>$policy_number</td>
										
												
										<td>$notes</td>
										<td><a href='view_issued_client_profile?id=$id' class='btn btn-primary'><i class='fa fa-search'></i></a></td>
										</tr>";
									}
								}
							}
						}
					}

					?>

					</tbody>
				</table>
        </div>
        <!--
            <div class="col-sm-6 text-center">
                <button id="btn-add" name="btn-add" data-toggle="modal" data-target="#myModal" class="pull-right btn btn-primary btn-m"><i class="fa fa-plus"></i> Add New Client</button>
            </div>
        -->
    </div>
  </div>
  <div id="possible" class="tab-pane fade">
    <div class="row">
        <div class="col-sm-12 text-center">
            <h1>Possible Cancellation</h1>           
				<table id='possible_tbl' data-toggle="table"  class="table table-striped " cellpadding="5px" cellspacing="5px" width='95%'>
					<thead>
						<td>Client Name</td>
						<td>Adviser</td>
						<td>Policy Number</td>
						<td>Notes</td>
						<td></td>
					</thead>

					<tbody>
					<?php

					while($rows = mysqli_fetch_array($displayquery2)){

						if($rows["name"]==null)
							continue;

						$client_id = $rows["client_id"];
						$id=$rows["id"];
						$name=$rows["name"];
						$x=$rows["x"]; //advisername
						$y=$rows["y"]; //leadgenname
						$lead_by=$rows["lead_by"];
						$appt_date=$rows["appt_date"];
						$appt_time=$rows["appt_time"];
						$address=$rows["address"];
						$leadgen=$rows["leadgen"];
						$assigned_to=$rows["assigned_to"];
						$assigned_date=$rows["assigned_date"];
						$type_of_lead=$rows["type_of_lead"];
						$deals = json_decode($rows["deals"]);

						//<td>$".number_format((float)$api,2)."</td>	api
						foreach($deals as $deal){
							if($deal->status!="None"){
								if(isset($deal->clawback_status)){
									if($deal->clawback_status=="Possible Cancellation"){
										$api=$deal->clawback_api;
										if(!empty($deal->life_insured))
											$name .= ", " . $deal->life_insured;

										$policy_number = $deal->policy_number;
										$date_cancelled=$deal->clawback_date;
										$notes=$deal->clawback_notes;

										$date_cancelled=date('d/m/Y',strtotime($date_cancelled));

										$lg = $y;
										if($lead_by=="Self-Generated"){
											$lg = $x;
										}

										echo "
										<tr cellpadding='5px' cellspacing='5px'>

										<td>$name</td>
										<td>$x</td>
										<td>$policy_number</td>
										
												
										<td>$notes</td>
										<td><a href='view_issued_client_profile?id=$id' class='btn btn-primary'><i class='fa fa-search'></i></a></td>
										</tr>";
									}
								}
							}
						}
					}

					?>

					</tbody>
				</table>
        </div>
        <!--
            <div class="col-sm-6 text-center">
                <button id="btn-add" name="btn-add" data-toggle="modal" data-target="#myModal" class="pull-right btn btn-primary btn-m"><i class="fa fa-plus"></i> Add New Client</button>
            </div>
        -->
    </div>
  </div>
  <div id="cancelled" class="tab-pane fade">
    <div class="row">
        <div class="col-sm-12 text-center">
            <h1>Cancelled</h1>           
				<table id='cancelled_tbl' data-toggle="table"  class="table table-striped " cellpadding="5px" cellspacing="5px" width='95%'>
					<thead>
						<td>Client Name</td>
						<td>Adviser</td>
						<td>Policy Number</td>
						<td>Notes</td>
						<td></td>
					</thead>

					<tbody>
					<?php

					while($rows = mysqli_fetch_array($displayquery3)){

						if($rows["name"]==null)
							continue;

						$client_id = $rows["client_id"];
						$id=$rows["id"];
						$name=$rows["name"];
						$x=$rows["x"]; //advisername
						$y=$rows["y"]; //leadgenname
						$lead_by=$rows["lead_by"];
						$appt_date=$rows["appt_date"];
						$appt_time=$rows["appt_time"];
						$address=$rows["address"];
						$leadgen=$rows["leadgen"];
						$assigned_to=$rows["assigned_to"];
						$assigned_date=$rows["assigned_date"];
						$type_of_lead=$rows["type_of_lead"];
						$deals = json_decode($rows["deals"]);

						//<td>$".number_format((float)$api,2)."</td>	api
						foreach($deals as $deal){
							if($deal->status!="None"){
								if(isset($deal->clawback_status)){
									if($deal->clawback_status=="Cancelled"){
										$api=$deal->clawback_api;
										if(!empty($deal->life_insured))
											$name .= ", " . $deal->life_insured;

										$policy_number = $deal->policy_number;
										$date_cancelled=$deal->clawback_date;
										$notes=$deal->clawback_notes;

										$date_cancelled=date('d/m/Y',strtotime($date_cancelled));

										$lg = $y;
										if($lead_by=="Self-Generated"){
											$lg = $x;
										}

										echo "
										<tr cellpadding='5px' cellspacing='5px'>

										<td>$name</td>
										<td>$x</td>
										<td>$policy_number</td>
										
												
										<td>$notes</td>
										<td><a href='view_issued_client_profile?id=$id' class='btn btn-primary'><i class='fa fa-search'></i></a></td>
										</tr>";
									}
								}
							}
						}
					}

					?>

					</tbody>
				</table>
        </div>
        <!--
            <div class="col-sm-6 text-center">
                <button id="btn-add" name="btn-add" data-toggle="modal" data-target="#myModal" class="pull-right btn btn-primary btn-m"><i class="fa fa-plus"></i> Add New Client</button>
            </div>
        -->
    </div>
  </div>
</div>
</div>
</div>


</html>

<?php

}
?>