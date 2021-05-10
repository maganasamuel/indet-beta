<?php
ob_start();
date_default_timezone_set('Pacific/Auckland');
session_start();
if(!isset($_SESSION["myusername"])){
session_destroy();
header("Refresh:0; url=index.php");
}

if(!isset($_GET["edit_id"])){

header("Refresh:0; url=client_profiles.php");
}
else{
require "database.php";
$edit_id=$_GET["edit_id"];

?>

 <html>
<head>
<?php include "partials/nav_bar.html";?>
<!--nav bar end-->
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">
<link rel="stylesheet" href="styles.css">
<link id="favicon" rel="icon" href="Logo_ImageOnly.png" type="image/png" sizes="16x16">
<title>INDET</title>
<script>
$(document).ready(function(){
$('.datepicker').datepicker({
    dateFormat: 'dd/mm/yy'});

$('#datepicker2').datepicker({
    dateFormat: 'dd/mm/yy'});

$('#datepicker3').datepicker({
    dateFormat: 'dd/mm/yy'});



  $('#lead_by').on('change', function(){
      var leadby = $(this).val();
      console.log(leadby);
      var formData = {
        leadby : leadby,
      }
      if(leadby!="Self-Generated"){
        $.ajax({
          dataType:'json',
          type:'POST',
          data:formData,
          url:"fetch_leadgen.php",
          success:function(e){
            console.log(e);
            var rows= $.parseJSON(JSON.stringify(e));
            $('#leadgen').empty();
             $('#leadgen').append('<option value="" disabled hidden selected>Select Lead Generator</option>');
             $.each(rows, function(i, d) {
                    // You will need to alter the below to get the right values from your json object.  Guessing that d.id / d.modelName are columns in your carModels data
                    $('#leadgen').append('<option value="' + d.id + '">' + d.name + '</option>');
                });
          },
          error: function (x) {
            console.log(x);
                    }
        });
        $('#leadgen_div').slideDown();
        $('#leadgen_div').required = true;
      }
    else{
        $('#leadgen_div').slideUp();
        $('#leadgen_div').required = false;
      }
    });



});
</script>
</head>

<!--header-->
<div align="center">



<!--header end-->

<!--nav bar-->


<!--nav bar end-->


<!--label-->


  <div class="jumbotron">
    <h2 class="slide">Edit Client</h2>
</div>
<!--label end-->

<?php require "database.php";

  $con=mysqli_connect($host,$username,$password,$db)or die("could not connect to sql");
 if (!$con) {
        echo "<div>";
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
	    echo "</div>";	
}
$query = "SELECT * FROM clients_tbl WHERE id='$edit_id'";
$displayquery=mysqli_query($con,$query) or die('Could not look up user information; ' . mysqli_error($con));
$rows = mysqli_fetch_array($displayquery);

$id=$rows["id"];
$name=$rows["name"];
$appt_date=$rows['appt_date'];
$date_submitted=$rows['date_submitted'];
$appt_time=$rows['appt_time'];
$address=$rows['address'];
$leadby=$rows['lead_by'];
$leadgen=$rows['leadgen'];
$assigned_to=$rows['assigned_to'];
$assigned_date=$rows['assigned_date'];
$type_of_lead=$rows['type_of_lead'];
$issued=$rows['issued'];
$date_issued=$rows['date_issued'];
$notes=$rows['notes'];
$date_submitted=substr($date_submitted, 6,2).'/'.substr($date_submitted, 4,2).'/'.substr($date_submitted, 0,4);
$appt_date=substr($appt_date, 6,2).'/'.substr($appt_date, 4,2).'/'.substr($appt_date, 0,4);
$assigned_date=substr($assigned_date, 6,2).'/'.substr($assigned_date, 4,2).'/'.substr($assigned_date, 0,4);
$city = $rows['city'];
$zipcode = $rows['zipcode'];
$status = $rows['status'];
$status_color = "green";

if($status=="Cancelled")
  $status_color = "red";
	?>

            <div class="row">
              <div class="col-sm-3">
                
              </div>
              <div class="col-sm-6">
                <h4> <span style="color:black;">Status:</span><span style="color:<?php echo $status_color ?>;"> <?php echo $status ?> </span> 
              </h4>
            </div>
</div>

<form method="POST" class="margined" name="form_a">

	<div class="row">
    <div class='col-sm-2'></div>
		<div class='col-sm-2'>
      <label>Client Name
        <div class="input-group">
          <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
          <input id="name" type="text" class="form-control" name="name" value="<?php echo $name; ?>" placeholder="<?php echo $name; ?>"required>
        </div>
      </label>
    </div>
    <div class='col-sm-2'>
      <label>Date Submitted
        <div class="input-group">
          <span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
          <input id="date_submitted" type="text" class="form-control datepicker" autocomplete="off" name="date_submitted"  value="<?php echo $date_submitted; ?>" placeholder="<?php echo $appt_date; ?>"required>
        </div>
      </label>
		</div>
    <div class='col-sm-2'>
      <label>Appt Date
        <div class="input-group">
            <span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
            <input id="appt_date" type="text" class="form-control datepicker" autocomplete="off" name="appt_date"  value="<?php echo $appt_date; ?>" placeholder="<?php echo $appt_date; ?>"required>
        </div>
      </label>
		</div>
    <div class="col-sm-2">
      <label>Phone Number: 
        <div class="input-group">
          <span class="input-group-addon">
          <i class="fa fa-phone" aria-hidden="divue"></i></span>
          <input class="form-control" autocomplete="off" type="phone" name="phone_num" value="<?php echo $appt_time; ?>" placeholder="<?php echo $appt_time; ?>">
        </div>
      </label><!--new-->
    </div>
	</div><!--end row-->

  <div class='row'>
  <div class='col-sm-2'>
  </div>
  <div class='col-sm-3'>
    <label>Address
     <div class="input-group">
        <span class="input-group-addon"><i class="glyphicon glyphicon-map-marker"></i></span>
        <textarea class="form-control" rows="3" name="address"/><?php echo $address; ?></textarea>
      </div>
    </label>
  </div>
  <div class='col-sm-3'>
    <label>City
     <div class="input-group">
        <span class="input-group-addon"><i class="glyphicon glyphicon-map-marker"></i></span>
        <input id="city" type="text" class="form-control" name="city" value="<?php echo $city; ?>" >
      </div>
    </label>
  </div>
  <div class='col-sm-2'>
    <label>Zip Code
     <div class="input-group">
        <span class="input-group-addon"><i class="glyphicon glyphicon-map-marker"></i></span>
        <input id="zipcode" type="text" class="form-control" name="zipcode" value="<?php echo $zipcode; ?>" >
      </div>
    </label>
  </div>
</div>

		<div class="row">
			   <div class='col-sm-2'></div>
  <div class='col-sm-2'>
    <label>Source
      <div class="input-group">
          <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
          <select name="lead_by" id="lead_by" class="form-control" required/>
            <option value="" disabled hidden selected>Select Source</option>
            <option <?php echo ($leadby=="Self-Generated") ? "selected" : ""; ?>>Self-Generated</option>
            <option <?php echo ($leadby=="Telemarketer") ? "selected" : ""; ?>>Telemarketer</option>
            <option <?php echo ($leadby=="Face-to-Face Marketer") ? "selected" : ""; ?>>Face-to-Face Marketer</option>
          </select>
      </div>
    </label>
      <div id="leadgen_div">
        <label>Lead Generator
         <div class="input-group">
            <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
            <select name="leadgen" id="leadgen" class="form-control" />

              <option value="" disabled selected>Select Lead Generator</option>
              <option value="">--None--</option>
              <?php 
                $query = "SELECT id, name FROM leadgen_tbl ORDER BY name ASC";
                $displayquery=mysqli_query($con,$query) or die('Could not look up user information; ' . mysqli_error($con));

                WHILE($rows = mysqli_fetch_array($displayquery)){
                  $thisid=$rows["id"];
                  $name=$rows["name"];
                    if($thisid==$leadgen){
                    //echo "<option value='$thisid' selected>$name</option>";
                      echo "<option value='".$thisid."' selected>".$name."</option>";
                    }
                    else{
                      echo "<option value='".$thisid."'>".$name."</option>";
                    }
                  }
              ?>
            </select>
        </div>
      </label>
    </div>
  </div>


  <div class='col-sm-2'>
<label>Assigned to
 <div class="input-group">
    <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
    <select name="assigned_to" class="form-control" required />

  <option value="" disabled selected>Select Adviser</option>
  <option value="0">--None--</option>
<?php 

$query = "SELECT id,name FROM adviser_tbl ORDER BY name ASC";
$displayquery=mysqli_query($con,$query) or die('Could not look up user information; ' . mysqli_error($con));

WHILE($rows = mysqli_fetch_array($displayquery)){
$thisid=$rows["id"];
$name=$rows["name"];
if($thisid==$assigned_to){
echo "<option value='$thisid' selected>$name</option>";
echo "<option value='".$thisid."' selected>".$name."</option>";
}
else{
echo "<option value='".$thisid."'>".$name."</option>";
}


}
?>






?><!--<td><input class="addadviser" type="text" id="datepicker" name="mydate" required></td>-->
    </select>
  </div>
</label>
    </div>

      <div class='col-sm-2'>
<label>Assigned Date
 <div class="input-group">
    <span class="input-group-addon"><i class="fa fa-calendar" aria-hidden="true"></i></span>
 <input class="form-control datepicker" autocomplete="off" type="text" name="assigned_date" value="<?php echo $assigned_date; ?>" placeholder="<?php echo $assigned_date; ?>" step="any"/>
  </div>
</label>
    </div>

      <div class='col-sm-2'>
<label>Notes
 <div class="input-group">
     <textarea class="form-control" rows="4" name="notes"  placeholder="<?php echo $notes; ?>" /><?php echo $notes; ?></textarea>
  </div>
</label>
    </div>
   

    </div>
<div class="row" style="padding-top: 30px;">

  <div class="row">
          <div class="col-sm-2 center" >
	<input name="enter" class="btn btn-info center" type="submit" value="Update Client" style="width: 100%;"  /><br>
  <input name="open_cancel" id="open_cancel" class="btn btn-danger center" type="submit" value="Update Status" style="width: 100%;"  />
</div>
</div>

</div>

</form>






</div>


  <div class="modal fade" id="confirmModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
      <div class="modal-dialog">
          <div class="modal-content">
              <div class="modal-header">
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                  <h4 class="modal-title" id="myModalLabel">Confirm Client Status</h4>
              </div>
               <form method="POST" id="frmClientStatus" name="form_b" class="form-horizontal" novalidate="">
              <div class="modal-body">
                      
                      <div class="form-group error">

                        <label for="inputTask" class="col-sm-2 control-label">
                          Status:
                        </label>
                        <label for="inputTask" class="col-sm-5 control-label">
                          <select class="form-control has-error" id="status" name="status" required="">
                            <option value="" hidden="" disabled="" selected="">Select New Status</option>
                            <option>Seen</option>
                            <option>Agreement</option>
                            <option>Cancelled</option>
                          </select>
                        </label>
                        <label for="inputTask" class="col-sm-5 control-label">
                          <input id="date_updated" type="text" class="form-control datepicker" autocomplete="off" placeholder="Date Confirmed" name="date_status_updated"  value="<?php echo date("d/m/Y") ?>" required="">
                        </label>
                      </div>
              <div class="modal-footer">
                  <button type="submit" class="btn btn-danger" id="btn-update-confirm" name='confirm_status' value="Yes">Confirm Update</button>
                  <button type="button" class="btn btn-primary" id="btn-update-cancel" value="No">Cancel</button>
                  <input name="_method" id="_method" type="hidden" value="update" />
                  <input type="hidden" id="cancel_client" value="0">
              </div>
            </div></form>
          </div>
      </div>
  </div>

<script>
  $(document).ready(function(){
    $('#open_cancel').on("click", function(e){
        e.preventDefault();
        if($('#lead_by').val()=="Telemarketer"){
          $('#telemarketer_div').show();
        }
        else{
          $('#telemarketer_div').hide();
        }
        $('#confirmModal').modal('show');
    });
  });
</script>
</html>

<?php

}
?>


<?php


  if(isset($_POST["enter"])){
      $name=isset($_POST["name"])?$_POST["name"]:'';
      $appt_date=isset($_POST["appt_date"])?$_POST["appt_date"]:'';
      $appt_time=isset($_POST["phone_num"])?$_POST["phone_num"]:'';
      $address=isset($_POST["address"])?$_POST["address"]:'';
      $city=isset($_POST["city"])?$_POST["city"]:'';
      $zipcode=isset($_POST["zipcode"])?$_POST["zipcode"]:'';
      $leadgen=isset($_POST["leadgen"])?$_POST["leadgen"]:'';
      $leadby=isset($_POST["lead_by"])?$_POST["lead_by"]:'';
      $assigned_to=isset($_POST["assigned_to"])?$_POST["assigned_to"]:'';
      $assigned_date=isset($_POST["assigned_date"])?$_POST["assigned_date"]:'';
      $date_submitted=isset($_POST["date_submitted"])?$_POST["date_submitted"]:'';
      $type_of_lead=isset($_POST["type_of_lead"])?$_POST["type_of_lead"]:'';
      $issued=isset($_POST["issued"])?$_POST["issued"]:'';
      $date_issued=isset($_POST["date_issued"])?$_POST["date_issued"]:'';
      $notes=htmlspecialchars($_POST["notes"]);


      $assigned_date=substr($assigned_date,6,4).substr($assigned_date,3,2).substr($assigned_date,0,2);
      $date_submitted=substr($date_submitted,6,4).substr($date_submitted,3,2).substr($date_submitted,0,2);
      $appt_date=substr($appt_date,6,4).substr($appt_date,3,2).substr($appt_date,0,2);




        $con=mysqli_connect($host,$username,$password,$db)or die("could not connect to sql");
       if (!$con) {
              echo "<div>";
              echo "Failed to connect to MySQL: " . mysqli_connect_error();
            echo "</div>";  
      }

      $sql="UPDATE clients_tbl SET name=\"$name\",appt_date=\"$appt_date\",date_submitted=\"$date_submitted\",appt_time=\"$appt_time\",address=\"$address\",city=\"$city\",zipcode=\"$zipcode\",leadgen=\"$leadgen\",lead_by=\"$leadby\",assigned_to=\"$assigned_to\",assigned_date=\"$assigned_date\",notes=\"$notes\"
      WHERE id=\"$edit_id\""; 

      /*$ssql="UPDATE pdf_tbl SET name='$name',email='$email'
      WHERE adviser_id='$edit_id'"; */


      if(mysqli_query($con,$sql)){
        //header("Refresh:0; url=client_profiles.php");
        echo "<script>
          alert('Client Profile successfully updated! Press Ok to redirect to the client profiles page.');
        </script>";
        header("Refresh:0; url=client_profiles");
      }
    }

if(isset($_POST["confirm_status"])){
    extract($_POST);
    $date_updated=substr($date_status_updated,6,4).substr($date_status_updated,3,2).substr($date_status_updated,0,2);
    $updatable_fields = 0;

    $sql="UPDATE clients_tbl SET ";
    if(isset($status)){
      $sql .= addToUpdateQuery("status=\"$status\"",$updatable_fields); 
      $updatable_fields++;
    }

    if(isset($date_updated)){
      $sql .= addToUpdateQuery("date_status_updated=\"$date_updated\"",$updatable_fields); 
      $updatable_fields++;
    }

    if(isset($submission)){
      $sql .= addToUpdateQuery("submission=\"$submission\"",$updatable_fields); 
      $updatable_fields++;
    }

    if(isset($submission_amount)){
      $sql .= addToUpdateQuery("submission_amount=\"$submission_amount\"",$updatable_fields); 
      $updatable_fields++;
    }

    $sql .= "  WHERE id=\"$edit_id\""; 
    if($updatable_fields>0){
      if(mysqli_query($con,$sql)){
        header("Refresh:0; url=editclient.php?edit_id=$edit_id");
      }
      else{
        echo "<script>alert('$sql!');
        </script>";
      }
    }
    else{
      echo "<script>alert('No Client Status field filled, please fill data to update them.');</script>";
    }
  }

  function addToUpdateQuery($update_query, $updatable_fields){
      $op = "";
      if($updatable_fields>0){
        $op .= ",";
      }
      $op.= $update_query;
      return $op;
  }

?>
