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

})


  $('.datepicker').datepicker({
      dateFormat: 'dd/mm/yy'
  });


$('#me').dataTable({
  "columns": [
    { "width": "1%" },
    { "width": "1%" },
    { "width": "1%" }
  ]
});


});

</script>
<!--header-->
<div align="center">


<!--header end-->

<!--nav bar-->


<!--nav bar end-->


<!--label-->

  <div class="jumbotron">
    <h2 class="slide">Leads Generated</h2>
</div>
<!--label end-->

<!--
Modals
Editor
-->
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
      <div class="modal-dialog">
          <div class="modal-content">
              <div class="modal-header" style="background-color: #286090; ">
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span style="color:white;" aria-hidden="true">×</span></button>
                  <h4 class="modal-title" id="myModalLabel" style="color:white;">Appointment Editor</h4>
              </div>
              <div class="modal-body">
                  <form id="frmData" name="frmData" class="form-horizontal" novalidate="">
                    
                    <div class="row">
                        <div class="form-group">
                            <div class="col-sm-2">
                                <label for="venue">Venue</label>
                            </div>
                            <div class="col-sm-10">
                                <textarea class="form-control" id="venue" name="venue"  placeholder="Appointment Venue"></textarea>
                            </div>
                        </div>
                    </div>
                    <p></p>
                    <div class="row">
                        <div class="form-group">
                            <div class="col-sm-2">
                                <label for="date">Date</label>
                            </div>
                            <div class="col-sm-10">
                                <input type="text" class="form-control datepicker" id="appointment_date" name="appointment_date" aria-describedby="date" placeholder="Date" value="">
                            </div>
                        </div>
                    </div>
                    <p></p>

                    <div class="row">
                        <div class="form-group">
                            <div class="col-sm-2">
                                <label for="venue">Time</label>
                            </div>
                            <div class="col-sm-3">
                                <select class="form-control" id="appointment_hour" name="appointment_hour"   aria-describedby="appointment_hour">
                                    <option>01</option>
                                    <option>02</option>
                                    <option>03</option>
                                    <option>04</option>
                                    <option>05</option>
                                    <option>06</option>
                                    <option>07</option>
                                    <option>08</option>
                                    <option>09</option>
                                    <option>10</option>
                                    <option>11</option>
                                    <option>12</option>
                                </select>
                            </div>
                            <div class="col-sm-1">
                                <h3 style="margin-top:0px;">:</h3>
                            </div>
                            <div class="col-sm-3">
                                <select class="form-control" id="appointment_minute" name="appointment_minute"   aria-describedby="appointment_hour">
                                    <option>00</option>
                                    <option>15</option>
                                    <option>30</option>
                                    <option>45</option>
                                </select>
                            </div>
                            <div class="col-sm-3">
                                <select class="form-control" id="appointment_period" name="appointment_period"   aria-describedby="appointment_hour">
                                    <option>AM</option>
                                    <option>PM</option>
                                </select>
                            </div>
                        </div>
                        
                    </div>
                      <input type="hidden" id="formtype" name="formtype" value="0">
                      <input type="hidden" id="lead_data_id" name="lead_data_id" value="0">
                  </form>
              </div>
              <div class="modal-footer">
                  <button type="button" class="btn btn-primary" id="btn-save" value="add">Save changes</button>
                  
              </div>
          </div>
      </div>
  </div>
  <!--
End of Editor
-->




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

$query = "SELECT c.*, l.name as lead_name, l.id as lead_id from leads_data l INNER JOIN clients_tbl c ON l.client_id = c.id";
$displayquery=mysqli_query($con,$query) or die('Could not look up user information; ' . mysqli_error($con));
?>

<div class="margined">
<table id='me' data-toggle="table" class="table table-striped" cellpadding="5px" cellspacing="5px" width='100%' style=" display: block; ">

<thead>
	<td>Client Name</td>
	<td>View PDF</td>
	<td>Edit Data</td>


</thead>
<tbody>
<?php

 WHILE($rows = mysqli_fetch_array($displayquery)):
$id=$rows["lead_id"];
$name=$rows["lead_name"];

echo "
<tr cellpadding='5px' cellspacing='5px'>

	<td>$name</td>
  <td><a href='leads_data_pdf?id=$id' target='_blank'><i class='fas fa-file-pdf' style='font-size:30px;'></i></a></td>
  <td><input data-toggle='modal' data-target='#myModal' type='image' class='open-modal'  src='edit.png' value='$id'></td>
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


<script src="js/lead_data-crud.js"></script>
</html>

<?php

}
?>