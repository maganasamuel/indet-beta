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

    $("#datefrom").datepicker(
      {dateFormat: 'dd/mm/yy',
        beforeShowDay: function (date) {

        if (date.getDate() == 16 || date.getDate() == 1) {
            return [true, ''];
        }
        return [false, ''];
       }
    });

/*
$('.datepicker').datepicker({
    dateFormat: 'dd/mm/yy'});
    */
$('#selectme').change(function(){

$('#leadgen').val($(this).find('option:selected').data('leadgen'));
$('#assigned_to').val($(this).find('option:selected').data('assignedto'));

});

$("#datefrom").on('change',function(){
    var $this=$(this).val();
    var m=$this.substr(3,2);
    var month=m-1;
    var year=$this.substr(6,4);
    var ifday=$this.substr(0,2);

    var lastday = function(y,m){
        return new Date(y, m +1, 0).getDate();
    }
    if(ifday==16){
        var ld=lastday(year,month)+'/'+m+'/'+year;
    }
    else{
        var ld='15'+'/'+m+'/'+year; 
    }
    
    $('#dateuntil').val(ld);
        console.log(month);
    });


  
  var objectsHidden = true;

  $('#client_id').on("change", function(){
        var client_id = $(this).val();

        $.get('fetch_submission_client_data?client_id=' + client_id, function (data) {
            //success data
            console.log(data);
            $('#name').val(data.name);
            $('#address').val(data.address);
            $('#city').val(data.city);
            $('#zipcode').val(data.zipcode);
            $('#phone').val(data.appt_time);
            $('#adviser').val(data.assigned_to);
            $('#leadgen').val(data.leadgen);
            $('#date_generated').val(data.date_submitted.substr(6) + "/" + data.date_submitted.substr(4,2) + "/" + data.date_submitted.substr(0,4));
            $('#appt_date').val(data.appt_date.substr(6) + "/" + data.appt_date.substr(4,2) + "/" + data.appt_date.substr(0,4));
            if(objectsHidden){
              objectsHidden=false;
              $('#client_data').slideDown(); 
              $('#deals_div').slideDown();        
              $('#add_issued_client').slideDown(); 
            }
            FillDealsTable(data);
        });
    });


    function FillDealsTable(data){
      var table = document.getElementById("dealsTable");
      data.deals.forEach(function(deal){
        var client_name = data.name;
        client_name += (deal.life_insured!="") ? ", " + deal.life_insured : "";
        var submission_date = deal.submission_date.substr(6) + "/" + deal.submission_date.substr(4,2) + "/" + deal.submission_date.substr(0,4);
        var company = (deal.company!="Others") ? deal.company : deal.specific_company;

        $('#dealsTable > tbody:last-child').append('\
          <tr cellpadding="5px" cellspacing="5px">\
            <td>' + client_name + '</td>\
            <td>' + submission_date + '</td>\
            <td>' + company + '</td>\
              <td>' + deal.policy_number + '</td>\
              <td>' + formatter.format(deal.original_api) + '</td>\
              <td>' + deal.status + '</td>\
          </tr>');
      });
    }




  
function moneyFormat(n, c, d, t) {
  var c = isNaN(c = Math.abs(c)) ? 2 : c,
    d = d == undefined ? "." : d,
    t = t == undefined ? "," : t,
    s = n < 0 ? "-" : "",
    i = String(parseInt(n = Math.abs(Number(n) || 0).toFixed(c))),
    j = (j = i.length) > 3 ? j % 3 : 0;

  return s + "$" + (j ? i.substr(0, j) + t : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : "");
};
  //END OF JQUERY SCRIPT
  });

const formatter = new Intl.NumberFormat('en-US', {
  style: 'currency',
  currency: 'USD',
  minimumFractionDigits: 2
})


</script>
<!--header-->
<div align="center">
<!--header end-->

<!--nav bar-->

<!--nav bar end-->

  <div class="jumbotron">
    <h2 class="slide">Add Issued Client</h2>
</div>

<div>

<form method="POST" class="margined">
  <div id="client_data" style="display:none;">
  <div class="row">
    <div class="col-sm-2"></div>
    <div class="col-sm-2">
      <label>Client Name
       <div class="input-group">
          <span class="input-group-addon"><i class="fas fa-user"></i></span>
          <input class="form-control" autocomplete="off" type="text" name="name" id="name" required/>
        </div>
      </label>
    </div>
    <div class="col-sm-2">
      <label>Address
       <div class="input-group">
          <span class="input-group-addon"><i class="fas fa-map-marker"></i></span>
        <input class="form-control" autocomplete="off" type="text" name="address" id="address" required/>
        </div>
      </label>
    </div>

    <div class="col-sm-2">
      <label style="width: 100% !important;">City
        <div class="input-group">
          <span class="input-group-addon">
          <i class="fa fa-map-marker" aria-hidden="divue"></i></span>
          <input class="form-control" autocomplete="off" type="text" id="city" name="city" required="" />
        </div>
      </label>
    </div>
    <div class="col-sm-2">
      <label>Zip Code
       <div class="input-group">
          <span class="input-group-addon"><i class="fas fa-map-marker"></i></span>
        <input class="form-control" autocomplete="off" type="text" name="zipcode" id="zipcode" required/>
        </div>
      </label>
    </div>
  </div>
  <div class="row">
    <div class="col-sm-1"></div>
    <div class="col-sm-2">
      <label style="width: 100% !important;">Phone
        <div class="input-group">
          <span class="input-group-addon">
          <i class="fa fa-phone" aria-hidden="divue"></i></span>
          <input class="form-control" autocomplete="off" type="text" id="phone" name="phone" />
        </div>
      </label>
    </div>
    <div class="col-sm-2">
      <label>Lead Generator
        <div class="input-group">
          <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
          <select id="leadgen" class="form-control" name="leadgen" required />
            <option value="" disabled hidden selected>Select Lead Generator</option> 
              <optgroup label="Face-to-Face Marketers">           
                <?php
                  $query = "SELECT * from leadgen_tbl WHERE type='Face-to-Face Marketer' ORDER BY name ASC";
                  $displayquery=mysqli_query($con,$query) or die('Could not look up user information; ' . mysqli_error($con));

                  WHILE($rows = mysqli_fetch_array($displayquery)){
                    $id=$rows["id"];
                    $name=$rows["name"];
                    //echo "<option value='".$id."'>".$name."</option>";
                    echo "<option value='".$id."'>".$name."</option>";
                  }
                ?>
              </optgroup>            
              <optgroup label="Telemarketers">           
                  <?php 

                    $query = "SELECT * from leadgen_tbl where type='Telemarketer' ORDER BY name ASC";
                    $displayquery=mysqli_query($con,$query) or die('Could not look up user information; ' . mysqli_error($con));

                    WHILE($rows = mysqli_fetch_array($displayquery)){
                      $id=$rows["id"];
                      $name=$rows["name"];
                      //echo "<option value='".$id."'>".$name."</option>";
                      echo "<option value='".$id."'>".$name."</option>";
                    }
                  ?>
              </optgroup>  
        </select>
      </div>
    </label>
  </div>
    <div class="col-sm-2">
      <label>Adviser
        <div class="input-group">
          <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
          <select id="adviser" class="form-control" name="adviser" required />
            <option value="" disabled hidden selected>Select Adviser</option>                        
                <?php 

                $query = "SELECT * from adviser_tbl ORDER BY name ASC";
                $displayquery=mysqli_query($con,$query) or die('Could not look up user information; ' . mysqli_error($con));

                WHILE($rows = mysqli_fetch_array($displayquery)){
                $id=$rows["id"];
                $name=$rows["name"];
                //echo "<option value='".$id."'>".$name."</option>";
                echo "<option value='".$id."'>".$name."</option>";
                }
                ?>






                ?>
          </select>
        </div>
      </label>
    </div>


    <div class="col-sm-2">
      <label style="width: 100% !important;">Date Generated
        <div class="input-group">
          <span class="input-group-addon">
          <i class="fa fa-calendar" aria-hidden="true"></i></span>
          <input class="form-control datepicker" autocomplete="off"  type="text" id="date_generated" name="date_generated" required="" />
        </div>
      </label>
    </div>


    <div class="col-sm-2">
      <label style="width: 100% !important;">Appointment Date
        <div class="input-group">
          <span class="input-group-addon">
          <i class="fa fa-calendar" aria-hidden="true"></i></span>
          <input class="form-control datepicker" autocomplete="off"  type="text" id="appt_date" name="appt_date" required="" />
        </div>
      </label>
    </div>

  </div>
</div>
<?php 

$query = "SELECT c.id, c.name, c.leadgen, c.assigned_to FROM submission_clients s LEFT JOIN clients_tbl c ON s.client_id = c.id WHERE c.status!='Cancelled' AND c.id NOT IN (SELECT name FROM issued_clients_tbl) ORDER BY name ASC";
$displayquery=mysqli_query($con,$query) or die('Could not look up user information; ' . mysqli_error($con));

?>

<div class='row'>
    <div class='col-sm-4'></div>
		<div class='col-sm-4'>
<label>Existing Client
 <div class="input-group">
    <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
    <select name="client_id" id="client_id" class="form-control" id='selectme' required />

  <option value="" disabled selected>Select Client</option>

<?php 
WHILE($rows = mysqli_fetch_array($displayquery)){
$id=$rows["id"];
$name=$rows["name"];
$leadgen=$rows["leadgen"];
$assigned_to=$rows["assigned_to"];
//echo "<option value='".$id."'>".$name."</option>";
echo "<option value='".$id."' data-leadgen='".$leadgen."' data-assignedto='".$assigned_to."'>".$name."</option>";
}
?>






?><!--<td><input class="addadviser" type="text" id="datepicker" name="mydate" required></td>-->
    </select>
  </div>
</label>

</div>


  <!--div class='col-sm-2'>

<div class='col-sm-2'>
<label>Issued Premium
 <div class="input-group">
    <span class="input-group-addon"><i class="fa fa-usd" aria-hidden="true"></i></span>
 <input class="form-control" autocomplete="off" type="text" name="issued" step="any" required/>
  </div>
</label>
    </div>

<label>Issued Date
 <div class="input-group">
    <span class="input-group-addon"><i class="fa fa-calendar" aria-hidden="true"></i></span>
 <input class="form-control datepicker" autocomplete="off"  type="text" name="date_issued" step="any" required/>
  </div>
</label>
    </div>
</div>
<div class="row">
  <div class='col-sm-2'></div>
  <div class='col-sm-2'>
<label style="width: 100% !important;">Date Issued
<div class="input-group">
    <span class="input-group-addon">
    <i class="fa fa-calendar" aria-hidden="divue"></i></span>
    <input class="form-control" autocomplete="off" type="text" id="datefrom" name="date_issued" /></div>
</label>

</div>
  <div class='col-sm-2'>
  <label>Until
 <div class="input-group">
 <span class="input-group-addon">
    <i class="fa fa-calendar" aria-hidden="divue"></i></span>
    <input class="form-control" autocomplete="off" type="text" id="dateuntil" name="until" readonly="" /></div>
</label>
</div>
</div>

<div class="row">
    <div class='col-sm-2'></div>
  <div class='col-sm-2'>
<label>Notes
 <div class="input-group">
     <textarea class="form-control" rows="4" name="notes"/></textarea>
  </div>
</label>
    </div>
   


  -->
    

    </div>
    <div class="margined table-responsive" id="deals_div" style="display:none;">
<table id='dealsTable' data-toggle="table"  class="table table-striped " cellpadding="5px" cellspacing="5px" width='95%'>

<thead>

  <td>Client Name, Life Insured</td>
  <td>Date of Submission</td>
  <td>Insurer</td>
  <td>Policy Number</td>
  <td>Original API</td>
  <td>Status</td>



</thead>
<tbody>

</tbody>
</table>
</div>
<div class="row" style="padding-top: 30px;">

  <div class="row">
          <div class="col-sm-2 center" >
	<input name="enter" class="btn btn-info center" id="add_issued_client" type="submit" value="Add Issued Client" style="width: 100%;"  />
</div>
</div>

</div>

</form>
<?php
if(isset($_POST["enter"])){
$name=isset($_POST["name"])?$_POST["name"]:'';
$appt_date=isset($_POST["appt_date"])?$_POST["appt_date"]:'';
$appt_time=isset($_POST["phone_num"])?$_POST["phone_num"]:'';
$address=isset($_POST["address"])?$_POST["address"]:'';
$leadgen=isset($_POST["leadgen"])?$_POST["leadgen"]:'';
$assigned_to=isset($_POST["assigned_to"])?$_POST["assigned_to"]:'';
$assigned_date=isset($_POST["assigned_date"])?$_POST["assigned_date"]:'';
$type_of_lead=isset($_POST["type_of_lead"])?$_POST["type_of_lead"]:'';
$issued=isset($_POST["issued"])?$_POST["issued"]:'';
$date_issued=isset($_POST["date_issued"])?$_POST["date_issued"]:'';
$notes=htmlspecialchars($_POST["notes"]);

$assigned_date=substr($assigned_date,6,4).substr($assigned_date,3,2).substr($assigned_date,0,2);
$date_issued=substr($date_issued,6,4).substr($date_issued,3,2).substr($date_issued,0,2);

$sql="INSERT INTO issued_clients_tbl (name,appt_date,appt_time,address,leadgen,assigned_to,assigned_date,type_of_lead,issued,date_issued,notes) 
VALUES ('$name','$appt_date','$appt_time','$address','$leadgen','$assigned_to','$assigned_date','$type_of_lead','$issued','$date_issued','$notes')"; 


if(mysqli_query($con,$sql)){
  
  echo "<script>alert('Issued Client successfully added!');</script>";
  header("Refresh:0");
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