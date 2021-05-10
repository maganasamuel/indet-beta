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
</head>
<?php 
require "database.php";
 

if(!isset($_SESSION["myusername"])){
session_destroy();
header("Refresh:0; url=index.php");
}

else{
?>
<script type="text/javascript">
  
  $(function() {

    $('.datepicker').datepicker({
        dateFormat: 'dd/mm/yy'
      });
    
    $('#date_submitted').datepicker().datepicker("setDate", new Date());  



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
<!--header-->
<div align="center">
<!--header end-->

<!--nav bar-->

<!--nav bar end-->

  <div class="jumbotron">
    <h2 class="slide">Add Client</h2>
</div>

<div>

<form method="POST" class="margined">

<div class='row'>
    <div class='col-sm-2'></div>
		<div class='col-sm-2'>
    <label>Client Name
     <div class="input-group">
        <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
        <input id="name" type="text" class="form-control" name="name" required>
      </div>
        </label>
    		</div>
    <div class='col-sm-2'>
      <label>Date Generated
       <div class="input-group">
          <span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
          <input id="date_submitted" type="text" class="form-control datepicker" autocomplete="off" name="date_submitted">
        </div>
      </label>
    </div>
    <div class='col-sm-2'>
      <label>Appt Date
       <div class="input-group">
          <span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
          <input id="appt_date" type="text" class="form-control datepicker" autocomplete="off" name="appt_date">
        </div>
      </label>
    </div>
    <div class="col-sm-2"><label>Phone Number: 
          <div class="input-group">
        <span class="input-group-addon">
        <i class="fa fa-phone" aria-hidden="divue"></i></span>
        <input class="form-control" autocomplete="off" type="phone" name="phone_num"></div></label><!--new-->
    </div>
</div>

<div class='row'>
  <div class='col-sm-2'>
    
  </div>
  
  <div class='col-sm-3'>
    <label>Address
     <div class="input-group">
        <span class="input-group-addon"><i class="glyphicon glyphicon-map-marker"></i></span>
        <textarea class="form-control" rows="3" name="address"/></textarea>
      </div>
    </label>
  </div>

  <div class='col-sm-2'>
    <label>City
     <div class="input-group">
        <span class="input-group-addon"><i class="glyphicon glyphicon-map-marker"></i></span>
        <input id="city" type="text" class="form-control" name="city">
      </div>
    </label>
  </div>
  <div class='col-sm-2'>
    <label>Zip Code
     <div class="input-group">
        <span class="input-group-addon"><i class="glyphicon glyphicon-map-marker"></i></span>
        <input id="zipcode" type="number" class="form-control" name="zipcode">
      </div>
    </label>
  </div>
</div>

<div class='row'>
   <div class='col-sm-2'>
     
   </div>


  <div class='col-sm-2'>
    <label>Source
      <div class="input-group">
          <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
          <select name="lead_by" id="lead_by" class="form-control" required/>
            <option value="" disabled hidden selected>Select Source</option>
            <option>Self-Generated</option>
            <option>Telemarketer</option>
            <option>Face-to-Face Marketer</option>
          </select>
      </div>
    </label>

  <div id="leadgen_div" style="display: none;">
  <label id="leadgen_label">Lead Generator</label>
   <div class="input-group">
      <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
      <select name="leadgen" id="leadgen" class="form-control" />
      </select>
    </div>
  </div>
</div>

  <div class='col-sm-2'>
<label>Assigned to
 <div class="input-group">
    <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
    <select name="assigned_to" class="form-control" required />

  <option value="" disabled hidden selected>Select Adviser</option>
<option value="">--None--</option>
<?php 

$query = "SELECT id,name FROM adviser_tbl ORDER BY name asc";
$displayquery=mysqli_query($con,$query) or die('Could not look up user information; ' . mysqli_error($con));

WHILE($rows = mysqli_fetch_array($displayquery)){
$id=$rows["id"];
$name=$rows["name"];
echo  "<option value='".$id."'>".$name."</option>";

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
 <input class="form-control datepicker" autocomplete="off" type="text" name="assigned_date" step="any"/>
  </div>
</label>
    </div>
  <!--    <div class='col-sm-2'>
<label>Type of Lead
 <div class="input-group">
    <span class="input-group-addon"><i class="glyphicon glyphicon-file"></i></span>
<select class="form-control" name="type_of_lead" id="type_of_lead">
<option value="payable">Payable</option>
<option value="free">Free</option>
<option value="replacement">Replacement</option>
<option value="invalid">Invalid</option>
</select>
  </div>
</label>
    </div>
</div>

<div class='row'>
   <div class='col-sm-2'></div>
<div class='col-sm-2'>
<label>Issued
 <div class="input-group">
    <span class="input-group-addon"><i class="fa fa-calendar" aria-hidden="true"></i></span>
 <input class="form-control datepicker" type="text" name="issued" step="any" required/>
  </div>
</label>
    </div>

  <div class='col-sm-2'>
<label>Date Issued
 <div class="input-group">
    <span class="input-group-addon"><i class="fa fa-calendar" aria-hidden="true"></i></span>
 <input class="form-control datepicker" type="text" name="date_issued" step="any" required/>
  </div>
</label>
    </div>
-->
  <div class='col-sm-2'>
<label>Notes
 <div class="input-group">
     <textarea class="form-control" rows="4" name="notes"/></textarea>
  </div>
</label>
    </div>
   

    </div>
<div class="row" style="padding-top: 30px;">

  <div class="row">
          <div class="col-sm-2 center" >
	<input name="enter" class="btn btn-info center" type="submit" value="Add Client" style="width: 100%;"  />
</div>
</div>

</div>

</form>
<?php
if(isset($_POST["enter"])){
  $name=isset($_POST["name"])?$_POST["name"]:'';
  $appt_date=isset($_POST["appt_date"])?$_POST["appt_date"]:'';
  $date_submitted=isset($_POST["date_submitted"])?$_POST["date_submitted"]:'';
  $appt_time=isset($_POST["phone_num"])?$_POST["phone_num"]:'';
  $address=isset($_POST["address"])?$_POST["address"]:'';
  $city=isset($_POST["city"])?$_POST["city"]:'';
  $zipcode=isset($_POST["zipcode"])?$_POST["zipcode"]:'';
  $leadgen=isset($_POST["leadgen"])?$_POST["leadgen"]:'';
  $leadby=isset($_POST["lead_by"])?$_POST["lead_by"]:'';
  $assigned_to=isset($_POST["assigned_to"])?$_POST["assigned_to"]:'';
  $assigned_date=isset($_POST["assigned_date"])?$_POST["assigned_date"]:'';
  $type_of_lead=isset($_POST["type_of_lead"])?$_POST["type_of_lead"]:'';
  $issued=isset($_POST["issued"])?$_POST["issued"]:'';
  $date_issued=isset($_POST["date_issued"])?$_POST["date_issued"]:'';
  $notes=htmlspecialchars($_POST["notes"]);

  $name = addslashes($name);
  $address = addslashes($address);
  $city = addslashes($city);
  $notes = addslashes($notes);

  $date_submitted=substr($date_submitted,6,4).substr($date_submitted,3,2).substr($date_submitted,0,2);
  $assigned_date=substr($assigned_date,6,4).substr($assigned_date,3,2).substr($assigned_date,0,2);
  $appt_date=substr($appt_date,6,4).substr($appt_date,3,2).substr($appt_date,0,2);

  $sql="INSERT INTO clients_tbl (name,appt_date,date_submitted,appt_time,address,city,zipcode,lead_by,leadgen,assigned_to,assigned_date,type_of_lead,issued,date_issued,notes) 
  VALUES ('$name','$appt_date','$date_submitted','$appt_time','$address','$city','$zipcode','$leadby','$leadgen','$assigned_to','$assigned_date','$type_of_lead','$issue','$date_issued','$notes')"; 

  if(mysqli_query($con,$sql)){
    
    echo "<script>alert('Client successfully added!');</script>";
    header('Location:add_client.php');
    ob_end_flush();
    
  }
  else{
  echo("Error description: " . mysqli_error($con));
  }
}



?>
</div>




</html>

<?php

}


?>