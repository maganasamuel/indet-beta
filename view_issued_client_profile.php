 <?php
  session_start();

  require "database.php";

  if (!isset($_SESSION["myusername"])) {
    session_destroy();
    header("Refresh:0; url=index.php");
  } else {

    if (!isset($_GET["id"])) {
      header("Refresh:0; url=issued_client_profiles.php");
    } else {
      $i_id = $_GET["id"];
      $query = "SELECT c.id as client_id, s.id as id,s.deals as deals, s.timestamp, c.address, c.appt_time,c.appt_date, c.date_submitted, c.city, c.zipcode, c.name as client_name, l.name as leadgen_name, c.leadgen, c.assigned_to, a.name as adviser_name, s.deals FROM submission_clients s LEFT JOIN issued_clients_tbl i ON i.name = s.client_id LEFT JOIN clients_tbl c ON s.client_id = c.id LEFT JOIN leadgen_tbl l ON c.leadgen = l.id LEFT JOIN adviser_tbl a ON c.assigned_to = a.id WHERE i.id = $i_id order by s.timestamp desc;";
      //echo $query;
      $result = mysqli_query($con, $query) or die('Could not look up user information; ' . mysqli_error($con));
      $data = mysqli_fetch_assoc($result);
      if (!empty($data))
        extract($data);
    }
    ?>
   <html>

   <head>
     <!--nav bar-->
     <?php include "partials/nav_bar.html"; ?>
     <!--nav bar end-->
     <meta name="viewport" content="width=device-width, initial-scale=1">
     <link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">
     <link rel="stylesheet" href="styles.css">
     <link id="favicon" rel="icon" href="Logo_ImageOnly.png" type="image/png" sizes="16x16">
     <title>INDET</title>
     <script>
       $(function() {

         $('.checkme').on('click', function() {
           var id = $(this).attr('data-id');
           var com = $(this).attr('data-com');
           var me = $(this);
         });

         $('#me').dataTable();

         $('#edit_client').on("click", function() {
           $('#client_labels').slideUp();
           $('#client_data').slideDown();
         });

         $("#add_follow_up_history").on("click", function() {
           $("#frmHistory").trigger("reset");
           $('#method').val('POST');
           $("#client_id2").val($(this).data("client_id"));
           $('#myModal').modal('show');
         });

         $(document).on("click", ".open-modal", function(e) {
           e.preventDefault();
           var mat_id = $(this).val();
           $("#status_div").hide();
           $.get('crud/follow-up-histories-crud.php/?id=' + mat_id, function(data) {
             console.log(data);
             $("#notes").val(data.notes);
             $('#history_id').val(data.id);
             $('#method').val("PUT");
             $('#myModal').modal('show');
           });
         });

         $(document).on('click', '#btn-save', function(e) {
           var data = $("#frmHistory").serialize();
           method = $("#method").val();
           console.log(data);
           $.ajax({
             data: data,
             type: "post",
             url: "crud/follow-up-histories-crud.php",
             success: function(data) {
               /*
					var total_leads_payable = $("#total_leads_payable_header").html();
	                var total_issued_leads_payable = $("#total_issued_leads_payable_header").html();
	                var total_outstanding_payable_amount = $("#total_outstanding_payable_amount_header").html();

	                if(data.status=="Paid Issued Leads"){
	                	total_issued_leads_payable -= data.number_of_leads;
	                	$("#total_issued_leads_payable_header").html(total_issued_leads_payable);
	                }
	                else if(data.status=="Manual Billed Assigned Leads"){
	                	total_leads_payable += data.number_of_leads;
	                	$("#total_leads_payable_header").html(total_leads_payable);
	                } 
	                else if(data.status=="Manual Billed Issued Leads"){
	                	total_issued_leads_payable -= data.number_of_leads;
	                	$("#total_issued_leads_payable_header").html(total_issued_leads_payable);
	                }
	                else{
	                	total_leads_payable-= data.number_of_leads;
	                	$("#total_leads_payable_header").html(total_leads_payable);
	                }

	                total_outstanding_payable_amount = parseFloat(data.amount) + parseFloat(total_outstanding_payable_amount);
	                $("#total_outstanding_payable_amount_header").html(total_outstanding_payable_amount);

	                var transaction = "<tr id='transaction"+data.id+"' cellpadding='5px' cellspacing='5px'><td>"+data.status+"</td><td>"+data.date+"</td>\
	                <td>"+data.number_of_leads+"</td><td>"+data.amount+"</td><td>"+data.notes+"</td>\
	                <td><input data-toggle='modal' data-target='#myModal' type='image' class='open-modal'  src='edit.png' value='"+data.id+"'></td>\
	                <td><input type='image' class='delete-transaction'  src='delete.png' value='"+ data.id +"'></td></tr>";

	                if (method == "POST"){ //if user added a new record
	                    $('#transactions-list').prepend(transaction);
	                }else{ //if user updated an existing record
	                    $("#transaction" + data.id).replaceWith(transaction);
	                }
	                console.log(data);
	                $("#report_text").html("User Credentials saved.");
                */
               $('#myModal').modal('hide');
               $('#frmHistory').trigger("reset");
               window.location.reload();
             },
             error: function(data) {
               $("#report_text").val(data.reason);
               console.log(data);
             }
           });
         });

         $('#save_client').on("click", function() {

           var formData = {
             client_id: $('#client_id').val(),
             name: $('#name').val(),
             address: $('#address').val(),
             city: $('#city').val(),
             zipcode: $('#zipcode').val(),
             phone: $('#phone').val(),
             leadgen: $('#leadgen').val(),
             adviser: $('#adviser').val(),
             date_generated: $('#date_generated').val(),
             appt_date: $('#appt_date').val(),
           }


           $.ajax({
             type: "POST",
             url: "update_client_data",
             data: formData,
             dataType: 'json',
             //DO SOMETHING IF SUCCESSFUL     
             success: function(data) {
               //LOG OUTPUT DATA    
               console.log(data);
               ResetClientData(true);
               $('#client_labels').slideDown();

               $('#client_data').slideUp();
             },
             //DO SOMETHING IF UNSUCCESSFUL  
             error: function(data) {
               console.log('Error:', data);
             }
           });
         });


         function ResetClientData(resetLabelsOnly = false) {
           var client_id = $("#client_id").val();
           console.log("Client ID:" + client_id);
           $.get('fetch_submission_client_data?client_id=' + client_id, function(data) {
             //success data
             console.log(data);
             var date_generated = data.date_submitted.substr(6) + "/" + data.date_submitted.substr(4, 2) + "/" + data.date_submitted.substr(0, 4);
             var appt_date = data.appt_date.substr(6) + "/" + data.appt_date.substr(4, 2) + "/" + data.appt_date.substr(0, 4);
             if (data.leadgen_name == null) {
               data.leadgen_name = "Self-Generated";
             }
             //LABEL FIELDS
             $('#client_name_label').html("<h3>Client Name: <span class='form-control'>" + data.name + "</span></h3>");
             $('#address_label').html("<h3>Address: <span class='form-control'>" + data.address + "</span></h3>");
             $('#city_label').html("<h3>City: <span class='form-control'>" + data.city + "</span></h3>");
             $('#zipcode_label').html("<h3>Zipcode: <span class='form-control'>" + data.zipcode + "</span></h3>");
             $('#phone_label').html("<h3>Phone: <span class='form-control'>" + data.appt_time + "</span></h3>");
             $('#adviser_label').html("<h3>Adviser: <span class='form-control'>" + data.adviser_name + "</span></h3>");
             $('#leadgen_label').html("<h3>Lead Generator: <span class='form-control'>" + data.leadgen_name + "</span></h3>");
             $('#date_generated_label').html("<h3>Client Name: <span class='form-control'>" + date_generated + "</span></h3>");
             $('#appt_date_label').html("<h3>Client Name: <span class='form-control'>" + appt_date + "</span></h3>");

             if (resetLabelsOnly)
               return true;

             //EDIT FIELDS
             $('#name').val(data.name);
             $('#address').val(data.address);
             $('#city').val(data.city);
             $('#zipcode').val(data.zipcode);
             $('#phone').val(data.appt_time);
             $('#adviser').val(data.assigned_to);
             console.log($("#adviser").val());
             $('#leadgen').val(data.leadgen);
             $('#date_generated').val(date_generated);
             $('#appt_date').val(appt_date);
           });
         }

         $("#me").on("click", ".edit_deal", function() {

         });




         ResetClientData();
         console.log("Reset Client Data");
         //END JQUERY
       });
     </script>
     <!--header-->

     <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
       <div class="modal-dialog modal-lg">
         <div class="modal-content">
           <div class="modal-header" style="background-color: #286090; ">
             <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span style="color:white;" aria-hidden="true">×</span></button>
             <h4 class="modal-title" id="myModalLabel" style="color:white;">Follow Up History Editor</h4>
           </div>
           <div class="modal-body">
             <form id="frmHistory" name="frmHistory" class="form-horizontal" novalidate="">
               <div class="form-group error">
                 <label for="inputTask" class="col-sm-1 control-label">Notes</label>
                 <div class="col-sm-11">
                   <textarea type="number" class="form-control has-error" id="notes" name="notes" placeholder="Notes" rows="10" value=""></textarea>
                   <label id="password_label" for="number_of_leads" style="color:red;"></label>
                 </div>
               </div>
               <input type="hidden" id="method" name="method" value="0">
               <input type="hidden" id="client_id2" name="client_id" value="0">
               <input type="hidden" id="history_id" name="history_id" value="0">
             </form>
           </div>
           <div class="modal-footer">
             <button type="button" class="btn btn-primary" id="btn-save" value="add">Save changes</button>
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
         <h2 class="slide">
           Client Profiles
         </h2>
       </div>
       <!--label end-->

       <!--modal-->
       <div id="client_labels">
         <div class="row">
           <div class="col-sm-1"></div>
           <div class="col-sm-2" id="client_name_label"></div>
           <div class="col-sm-4" id="address_label"></div>
           <div class="col-sm-3" id="city_label"></div>
           <div class="col-sm-1" id="zipcode_label"></div>
           <?php
              if ($_SESSION["myusertype"] == "Admin") {
                echo '
            <div class="col-sm-1"><a class="btn btn-warning" style="margin-top:50px;" href="edit_issued_client.php?edit_id=' . $client_id . '"><span class="glyphicon glyphicon-pencil"></span></a></div>
          ';
              }
              ?>
         </div>

         <div class="row">
           <div class="col-sm-1"></div>
           <div class="col-sm-2" id="phone_label"></div>
           <div class="col-sm-2" id="leadgen_label"></div>
           <div class="col-sm-2" id="adviser_label"></div>
           <div class="col-sm-2" id="date_generated_label"></div>
           <div class="col-sm-2" id="appt_date_label"></div>
         </div>
         <!--
	<button type="button"class="btn btn-warning" style="margin-top:34px;" id="edit_client" ><span style="font-size:30px;" class="glyphicon glyphicon-pencil"></span></button>
  -->
       </div>
       <div id="client_data" style="display:none;">
         <div class="row">
           <div class="col-sm-2">
             <label>Client Name
               <div class="input-group">
                 <span class="input-group-addon"><i class="fas fa-user"></i></span>
                 <input class="form-control" autocomplete="off" type="text" name="name" id="name" value="<?php echo $client_name ?>" required />
                 <input class="form-control" autocomplete="off" type="hidden" name="client_id" id="client_id" value="<?php echo $client_id ?>" required />
               </div>
             </label>
           </div>
           <div class="col-sm-4">
             <label>Address
               <div class="input-group">
                 <span class="input-group-addon"><i class="fas fa-map-marker"></i></span>
                 <input class="form-control" autocomplete="off" type="text" name="address" id="address" value="<?php echo $address ?>" required />
               </div>
             </label>
           </div>

           <div class="col-sm-2">
             <label style="width: 100% !important;">City
               <div class="input-group">
                 <span class="input-group-addon">
                   <i class="fa fa-map-marker" aria-hidden="divue"></i></span>
                 <input class="form-control" autocomplete="off" type="text" id="city" name="city" value="<?php echo $city ?>" required="" />
               </div>
             </label>
           </div>
           <div class="col-sm-2">
             <label>Zip Code
               <div class="input-group">
                 <span class="input-group-addon"><i class="fas fa-map-marker"></i></span>
                 <input class="form-control" autocomplete="off" type="text" name="zipcode" id="zipcode" value="<?php echo $zipcode ?>" required />
               </div>
             </label>
           </div>

           <div class="col-sm-2">
             <button type="button" class="btn btn-primary" style="margin-top:13px;" id="save_client"><span style="font-size:30px;" class="glyphicon glyphicon-floppy-disk"></span></button>
           </div>
         </div>
         <div class="row">
           <div class="col-sm-1"></div>
           <div class="col-sm-2">
             <label style="width: 100% !important;">Phone
               <div class="input-group">
                 <span class="input-group-addon">
                   <i class="fa fa-phone" aria-hidden="divue"></i></span>
                 <input class="form-control" autocomplete="off" type="text" id="phone" value="<?php echo $appt_time ?>" name="phone" />
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
                      $displayquery = mysqli_query($con, $query) or die('Could not look up user information; ' . mysqli_error($con));

                      while ($rows = mysqli_fetch_array($displayquery)) {
                        $id = $rows["id"];
                        $name = $rows["name"];
                        //echo "<option value='".$id."'>".$name."</option>";
                        echo "<option value='" . $id . "'>" . $name . "</option>";
                      }
                      ?>
                 </optgroup>
                 <optgroup label="Telemarketers">
                   <?php

                      $query = "SELECT * from leadgen_tbl where type='Telemarketer' ORDER BY name ASC";
                      $displayquery = mysqli_query($con, $query) or die('Could not look up user information; ' . mysqli_error($con));

                      while ($rows = mysqli_fetch_array($displayquery)) {
                        $id = $rows["id"];
                        $name = $rows["name"];
                        //echo "<option value='".$id."'>".$name."</option>";
                        echo "<option value='" . $id . "'>" . $name . "</option>";
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
                    $displayquery = mysqli_query($con, $query) or die('Could not look up user information; ' . mysqli_error($con));

                    while ($rows = mysqli_fetch_array($displayquery)) {
                      $id = $rows["id"];
                      $name = $rows["name"];
                      //echo "<option value='".$id."'>".$name."</option>";
                      echo "<option value='" . $id . "'>" . $name . "</option>";
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
                 <input class="form-control datepicker" autocomplete="off" value="<?php echo NZEntryToDateTime($date_submitted) ?>" type="text" id="date_generated" name="date_generated" required="" />
               </div>
             </label>
           </div>


           <div class="col-sm-2">
             <label style="width: 100% !important;">Appointment Date
               <div class="input-group">
                 <span class="input-group-addon">
                   <i class="fa fa-calendar" aria-hidden="true"></i></span>
                 <input class="form-control datepicker" autocomplete="off" value="<?php echo NZEntryToDateTime($appt_date) ?>" type="text" id="appt_date" name="appt_date" required="" />
               </div>
             </label>
           </div>

         </div>
       </div>
       <div id="myModal" class="modal">
         <div class="modal-content">
           <span class="close">&times;</span>
           <p>Please confirm to delete all</p>

           <input type="password" id="confirmpassword" class="addadviser" placeholder="Password" autocomplete="new-password" /><br style="height:50px;">
           <br style=" display: block;margin: 10px 0;">
           <input type="button" id="confirmbutton" value="Delete All" style="width: 20%;" />
         </div>

       </div>




       <?php



          function convertNum($x)
          {

            return number_format($x, 2, '.', ',');
          }




          ?>

       <div class="margined table-responsive">
         <table id='me' data-toggle="table" class="table table-striped " cellpadding="5px" cellspacing="5px" width='95%' style=" display: block; overflow-x: auto; white-space: nowrap;">

           <thead>
             <td>Policy Number</td>
             <td>Insurer</td>
             <td>Status</td>
             <td>Arrear Status</td>
             <td>Client Name, Life Insured</td>
             <td>Date of Submission</td>
             <td>Original API</td>
             <td>Date Issued</td>
             <td>Issued API</td>
             <td>Notes</td>
           </thead>
           <tbody>
             <?php

                $total_api = 0;
                $issued_premium = 0;
                if (!empty($data)) {

                  $timestamp = date('d/m/Y', strtotime($timestamp));

                  $deals = json_decode($deals);
                  $deals_count = 0;
                  $deals_count = count($deals);


                  $unique_client_names = [];
                  $unique_policy_numbers = [];
                  $unique_insurers = [];
                  $unique_client_names[] = $client_name;

                  foreach ($deals as $deal) {
                    $submission_date = $deal->submission_date;
                    $submission_date = NZEntryToDateTime($submission_date);
                    $issued_date = "";
                    if (isset($deal->date_issued))
                      $issued_date = NZEntryToDateTime($deal->date_issued);
                    if ($deal->status == "Pending")
                      $total_api += $deal->original_api;

                    if ($deal->status == "Issued")
                      $issued_premium += $deal->issued_api;

                    echo "
          <tr cellpadding='5px' cellspacing='5px'>
          <td>$deal->policy_number</td>
          <td>
        ";

                    //Show Company Name if others
                    if ($deal->company != "Others")
                      echo "$deal->company";
                    else
                      echo "$deal->specific_company";

                    echo "</td>
        <td>$deal->status</td>
        ";

                    if (isset($deal->clawback_status)) {
                      echo "
            <td>$deal->clawback_status</td>
          ";
                    } else {
                      echo "
          <td>None</td>";
                    }

                    echo "
          <td>$client_name
        ";

                    if (!empty($deal->life_insured))
                      echo ", $deal->life_insured";

                    echo "</td>
          <td>$submission_date</td>

        <td>$" . number_format($deal->original_api, 2)  . "</td>
        ";

                    if ($deal->status == "Issued") {
                      echo "
            <td>$issued_date</td>
            <td>$deal->issued_api</td>
            <td>$deal->notes</td>
          ";
                    } else {
                      echo "
          <td></td>
          <td></td>
          <td></td>";
                    }
                  }

                  /*$entrydate=$rows["entrydate"];
      $startingdate=$rows["startingdate"];
      $entrydate=substr($entrydate,0,4)."/".substr($entrydate,4,2)."/".substr($entrydate,6,2);

      $startingdate=substr($startingdate,0,4)."/".substr($startingdate,4,2)."/".substr($startingdate,6,2);
      $convertdate=substr($rows["entrydate"],6,2)."/".substr($rows["entrydate"],4,2)."/".substr($rows["entrydate"],0,4);

      $convertstartingdate=substr($rows["startingdate"],6,2)."/".substr($rows["startingdate"],4,2)."/".substr($rows["startingdate"],0,4);
      */
                }

                ?>
             <div class="row">
               <div class="col-sm-2 align-center"></div>
               <div class="col-sm-4 align-center">
                 <h4>Total Pending Application API: $ <?php echo number_format($total_api, 2) ?> </h4>
               </div>
               <div class="col-sm-4 align-center">
                 <h4>Total Issued Policies API: $ <?php echo number_format($issued_premium, 2) ?></h4>
               </div>

             </div>
             <!--
      <td><a href="editclient.php<?php echo "?edit_id=$id" ?>"><img src="edit.png"</a>
        </td>
      -->
             <!--
      <td><a class="a" href="edit_adviser.php<?php echo "?edit_id=$id" ?>"><img src="edit.png"></a>
        </td>
      -->
             <?php
                echo "</tr>";





                ?>
           </tbody>
         </table>
       </div>


       <h2 class="sub-header" style="text-align:center;">Follow Up History <button type="button" class="btn btn-success" style="text-align: right;" id="add_follow_up_history" data-client_id="<?php echo $client_id ?>"><span style="font-size:15px;" class="glyphicon glyphicon-plus"></span></button> </h2>
       <div class="table-responsive">
         <table id='me' data-toggle="table" id='example' class="table table-striped " cellpadding="5px" cellspacing="5px" style="width:90%; overflow-x: auto; white-space: nowrap;">
           <thead>
             <td>Added By</td>
             <td>Notes</td>
             <td>Added On</td>
             <td colspan="2">Controls</td>
           </thead>
           <tbody id="follow-ups-list">
             <?php
                $query = "SELECT f.id, f.notes, f.timestamp, f.user_id as user_id, u.username FROM follow_up_histories f LEFT JOIN users u ON f.user_id = u.id WHERE f.client_id = $client_id ORDER BY f.timestamp DESC, f.id DESC";

                $displayquery = mysqli_query($con, $query) or die('Could not look up user information; ' . mysqli_error($con));

                if (mysqli_num_rows($displayquery) > 0) {
                  while ($rows = mysqli_fetch_array($displayquery)) :
                    $id = $rows["id"];
                    $user_id = $rows["user_id"];
                    $notes = $rows["notes"];
                    $username = $rows["username"];
                    $timestamp = $rows["timestamp"];

                    $timestamp = date_create_from_format("Y-m-d H:i:s", $timestamp);
                    $timestamp = $timestamp->format("d/m/Y g:i:s A");

                    echo "
      <tr id='follow_up$id' cellpadding='5px' cellspacing='5px'>
        <td>$username</td>
        <td>$notes</td>
        <td>$timestamp</td>
      ";

                    if ($_SESSION["myuserid"] == $user_id) {
                      echo "
          <td><input data-toggle='modal' data-target='#myModal' type='image' class='open-modal'  src='edit.png' value='$id'></td>
          <td><input type='image' class='delete-transaction'  src='delete.png'  value='$id'></td>
        ";
                    } else {
                      echo "<td colspan='2'>
          <a class='btn btn-link' style='cursor: not-allowed;'><i class='fas fa-ban text-danger'></i></a>
        </td>";
                    }

                    echo "</tr>";
                  endwhile;
                } else {

                  echo "
      <tr cellpadding='5px' cellspacing='5px'>
        <td colspan='5'>No Follow Up History</td>
      </tr>";
                }
                ?>
           </tbody>
         </table>
       </div>
     </div>


   </html>

 <?php



  }
  function debuggingLog($header = "Logged Data", $variable)
  {
    //SET TO TRUE WHEN DEBUGGING SET TO FALSE WHEN NOT
    $isDebuggerActive = false;

    if (!$isDebuggerActive)
      return;

    $op = "<br>";
    $op .=  $header;
    echo $op . "<hr>" . "<pre>";
    var_dump($variable);
    echo "</pre>" . "<hr>";
  }



  function NZEntryToDateTime($NZEntry)
  {
    return substr($NZEntry, 6, 2) . "/" . substr($NZEntry, 4, 2) . "/" . substr($NZEntry, 0, 4);
  }

  function DateTimeToNZEntry($date_submitted)
  {
    return substr($date_submitted, 6, 4) . substr($date_submitted, 3, 2) . substr($date_submitted, 0, 2);
  }
  ?>