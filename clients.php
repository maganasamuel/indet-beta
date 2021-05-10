 <?php
    session_start();


    if (empty($_SESSION['token'])) {
        $_SESSION['token'] = bin2hex(random_bytes(32));
    }
    $token = $_SESSION['token'];
    if (!isset($_SESSION["myusername"])) {
        session_destroy();
        header("Refresh:0; url=index.php");
    } else {
        ?>
     <html>

     <head>

         <!--nav bar-->
         <?php include "partials/nav_bar.html"; ?>
         <!--nav bar end-->
         <meta name="viewport" content="width=device-width, initial-scale=1">
         <?php echo '<meta name="_token" content="$token">'; ?>
         <link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">
         <link rel="stylesheet" href="styles.css">
         <link id="favicon" rel="icon" href="Logo_ImageOnly.png" type="image/png" sizes="16x16">
         <title>INDET</title>
     </head>

     <body>
         <div align="center">
             <div class="jumbotron">
                 <h2 class="slide">Client Profiles</h2>
             </div>

             <?php
                    require "database.php";
                    $query = "SELECT clients_tbl.lead_by as lead_by, clients_tbl.city as city, clients_tbl.creation_date as creation_date, clients_tbl.zipcode as zipcode, clients_tbl.date_submitted,clients_tbl.id,clients_tbl.name,adviser_tbl.name as x,leadgen_tbl.name as y,clients_tbl.appt_date,clients_tbl.appt_time,clients_tbl.address,clients_tbl.leadgen,clients_tbl.assigned_to,clients_tbl.assigned_date,clients_tbl.type_of_lead,clients_tbl.issued,clients_tbl.date_issued,clients_tbl.notes 
                        FROM clients_tbl LEFT JOIN adviser_tbl ON clients_tbl.assigned_to = adviser_tbl.id LEFT JOIN leadgen_tbl ON clients_tbl.leadgen = leadgen_tbl.id WHERE binned=0 order by clients_tbl.date_issued desc;";
                    $displayquery = mysqli_query($con, $query) or die('Could not look up script information; ' . mysqli_error($con));
                    ?>

             <div class="margined table-responsive">
                 <div class="row">
                     <div class="col-sm-9 text-center"></div>
                     <div class="col-sm-3 text-center"><button id="btn-add" name="btn-add" data-toggle="modal" data-target="#myModal" class="pull-right btn btn-primary btn-m"><i class="fa fa-plus"></i> Add New Client</button></div>
                 </div>
                 <br>
                 <table id='clients_table' data-toggle="table" class="table table-striped " cellpadding="5px" cellspacing="5px" width='95%' style=" display: block; overflow-x: auto; white-space: nowrap;">
                     <thead>
                         <tr>
                             <td></td>
                             <td></td>
                             <td></td>
                             <td>Client Name</td>
                             <td>Appt Date</td>
                             <td>Date Added</td>
                             <td>Phone Number</td>
                             <td>Address</td>
                             <td>City</td>
                             <td>Zip Code</td>
                             <td>Source</td>
                             <td>Lead Generator</td>
                             <td>Assigned to</td>
                             <td>Assigned Date</td>
                             <!--td>Type of Lead</td>
            <td>Issued</td>-->
                             <td>Date Submitted</td>
                             <td>Notes</td>
                         </tr>
                     </thead>

                     <tbody id="clients-list"">
            <?php
                while ($rows = mysqli_fetch_array($displayquery)) :
                    extract($rows);

                    $id = $rows["id"];
                    $name = $rows["name"];
                    $x = $rows["x"]; //advisername
                    $y = $rows["y"]; //leadgenname
                    $search_lead_gen = "";
                    $appt_date = $rows["appt_date"];
                    $appt_date_sort = $appt_date;
                    $creation_date = $rows["creation_date"];
                    $creation_date_sort = $creation_date;
                    $appt_time = $rows["appt_time"];
                    $address = $rows["address"];
                    $city = $rows["city"];
                    $zipcode = $rows["zipcode"];
                    $lead_by = $rows["lead_by"];
                    $leadgen = $rows["leadgen"];
                    $assigned_to = $rows["assigned_to"];
                    $assigned_date = $rows["assigned_date"];
                    $type_of_lead = $rows["type_of_lead"];
                    $issued = $rows["issued"];
                    $date_issued = $rows["date_issued"];
                    $notes = $rows["notes"];
                    $date_submitted = $rows["date_submitted"];
                    $date_submitted_sort = $date_submitted;
                    $date_submitted = substr($date_submitted, 6, 2) . "/" . substr($date_submitted, 4, 2) . "/" . substr($date_submitted, 0, 4);

                    $appt_date = date('d/m/Y', strtotime($appt_date));
                    $assigned_date = date('d/m/Y', strtotime($assigned_date));
                    if ($creation_date != "") {
                        $creation_date_sort = date('YmdHis', strtotime($creation_date));
                        $creation_date = date('d/m/Y h:i:s a', strtotime($creation_date));
                    } else {
                        $creation_date = "N/A";
                        $creation_date_sort = "0";
                    }


                    /*$entrydate=$rows["entrydate"];
                $startingdate=$rows["startingdate"];
                $entrydate=substr($entrydate,0,4)."/".substr($entrydate,4,2)."/".substr($entrydate,6,2);

                $startingdate=substr($startingdate,0,4)."/".substr($startingdate,4,2)."/".substr($startingdate,6,2);


                $convertdate=substr($rows["entrydate"],6,2)."/".substr($rows["entrydate"],4,2)."/".substr($rows["entrydate"],0,4);

                $convertstartingdate=substr($rows["startingdate"],6,2)."/".substr($rows["startingdate"],4,2)."/".substr($rows["startingdate"],0,4);
                */
                    $lg = "";
                    if ($lead_by == "Self-Generated") {
                        $lg = $x;
                    } else {
                        $lg = $y;
                    }
                    echo "
                <tr id='client$id' cellpadding='5px' cellspacing='5px'>
                    <td><input id='btn-send-$id' type='image' src='email.png' value='$id' data-adviser_id= '$assigned_to' class='send-data'  data-toggle='modal' data-target='#sendModal' data-toggle='tooltip' title='Send Client Data' ></td>
                    <td><input id='btn-edit-$id' data-toggle='modal' data-target='#myModal' type='image' class='open-modal'  src='edit.png' value='$id' data-toggle='tooltip' title='Edit Client Profile' ></td>
                    <td><input type='image' class='delete-client'  src='delete.png' value='$id'  data-toggle='tooltip' title='Delete Client Profile'></td>
                    <td>$name</td>
                    <td data-order=" . $appt_date_sort . ">$appt_date</td>
                    <td data-order=" . $creation_date_sort . ">$creation_date</td>
                    <td>$appt_time</td>
                    <td>$address</td>
                    <td>$city</td>
                    <td>$zipcode</td>
                    <td>$lead_by</td>
                    <td>$lg</td>
                    <td>$x</td>
                    <td>$assigned_date</td>
                    <td data-order=" . $date_submitted_sort . ">$date_submitted</td>
                    <td>$notes</td>
                    ";

                    ?>

                
            <?php
                    echo "</tr>";

                endwhile;
                ?>
            </tbody>
        </table>
</div>

	<!--
	Modals
	Editor
	-->
	<div class=" modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                         <div class="modal-dialog modal-lg">
                             <div class="modal-content">
                                 <div class="modal-header" style="background-color: #286090; ">
                                     <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span style="color:white;" aria-hidden="true">×</span></button>
                                     <h4 class="modal-title" id="myModalLabel" style="color:white;">Client Editor</h4>
                                 </div>
                                 <div class="modal-body">
                                     <form id="frmClient" name="frmClient" class="form-horizontal" novalidate="">
                                         <div class="form-group error">
                                             <label for="inputTask" class="col-sm-2 control-label">Name</label>
                                             <div class="col-sm-4">
                                                 <input type="text" class="form-control has-error" id="name" name="name" placeholder="Name" value="" required>
                                             </div>
                                             <label for="inputTask" class="col-sm-2 control-label">Email</label>
                                             <div class="col-sm-4">
                                                 <input type="text" class="form-control has-error" id="email" name="email" placeholder="Email" value="" required>
                                             </div>
                                         </div>

                                         <div class="form-group error">
                                             <label for="inputTask" class="col-sm-2 control-label">Date Generated</label>
                                             <div class="col-sm-4">
                                                 <input type="text" class="form-control has-error datepicker" id="date_submitted" name="date_submitted" placeholder="Date Generated" value="" required>
                                             </div>
                                             <label for="inputTask" class="col-sm-2 control-label">Appt Date</label>
                                             <div class="col-sm-4">
                                                 <input type="text" class="form-control has-error datepicker" id="appt_date" name="appt_date" placeholder="Appointment Date" value="" required>
                                             </div>
                                         </div>

                                         <div class="form-group error">
                                             <label for="inputTask" class="col-sm-2 control-label">Assigned Date</label>
                                             <div class="col-sm-4">
                                                 <input type="text" class="form-control has-error datepicker" id="assigned_date" name="assigned_date" placeholder="Assigned Date" value="" required>
                                             </div>
                                             <label for="inputTask" class="col-sm-2 control-label">Phone</label>
                                             <div class="col-sm-4">
                                                 <input type="text" class="form-control has-error" id="phone_num" name="phone_num" placeholder="Phone" value="" required>
                                             </div>
                                         </div>

                                         <div class="form-group error">
                                             <label for="inputTask" class="col-sm-2 control-label">Appt Time</label>
                                             <div class="col-sm-4">
                                                 <input type="time" class="form-control has-error" id="time" name="time" placeholder="Time" value="">
                                             </div>
                                             <label for="inputTask" class="col-sm-2 control-label"></label>
                                             <div class="col-sm-4">
                                                 
                                             </div>
                                         </div>


                                         <div class="form-group error">
                                             <label for="inputTask" class="col-sm-2 control-label">Address</label>
                                             <div class="col-sm-10">
                                                 <textarea class="form-control has-error" id="address" name="address" placeholder="Address" required></textarea>
                                             </div>
                                         </div>

                                         <div class="form-group error">
                                             <label for="inputTask" class="col-sm-2 control-label">City</label>
                                             <div class="col-sm-4">
                                                 <input type="text" class="form-control has-error" id="city" name="city" placeholder="City" value="">
                                             </div>
                                             <label for="inputTask" class="col-sm-2 control-label">Zipcode</label>
                                             <div class="col-sm-4">
                                                 <input type="text" class="form-control has-error" id="zipcode" name="zipcode" placeholder="Zipcode" value="">
                                             </div>
                                         </div>

                                         <div class="form-group error">
                                             <label for="inputTask" class="col-sm-2 control-label">Source</label>
                                             <div class="col-sm-4">
                                                 <select name="lead_by" id="lead_by" class="form-control" required />
                                                 <option value="" disabled hidden selected>Select Source</option>
                                                 <option>Self-Generated</option>
                                                 <option>Telemarketer</option>
                                                 <option>Face-to-Face Marketer</option>
                                                 </select>
                                                 <select name="leadgen_telemarketer" id="leadgen_telemarketer" class="form-control leadgen" style="display:none;" />
                                                 <option value="0" disabled hidden selected>Select Telemarketer</option>
                                                 <?php
                                                        $tele_query = "Select * from leadgen_tbl where type='Telemarketer' ORDER BY name";
                                                        $tele_result = mysqli_query($con, $tele_query);
                                                        while ($tele_row = mysqli_fetch_assoc($tele_result)) {
                                                            echo "<option value='" . $tele_row['id'] . "'>" . $tele_row['name'] . "</option>";
                                                        }
                                                        ?>
                                                 </select>
                                                 <select name="leadgen_bdm" id="leadgen_bdm" class="form-control leadgen" style="display:none;" />
                                                 <option value="0" disabled hidden selected>Select Face-to-Face Marketer</option>
                                                 <?php
                                                        $bdm_query = "Select * from leadgen_tbl where type='Face-to-Face Marketer' ORDER BY name";
                                                        $bdm_result = mysqli_query($con, $bdm_query);
                                                        while ($bdm_row = mysqli_fetch_assoc($bdm_result)) {
                                                            echo "<option value='" . $bdm_row['id'] . "'>" . $bdm_row['name'] . "</option>";
                                                        }
                                                        ?>
                                                 </select>
                                                 <input type="hidden" name="leadgen" id="leadgen">
                                             </div>

                                             <label for="inputTask" class="col-sm-2 control-label">Assigned To</label>
                                             <div class="col-sm-4">
                                                 <select name="assigned_to" id="assigned_to" class="form-control" />
                                                 <option value="0" disabled hidden selected>Select Adviser</option>
                                                 <?php
                                                        $adv_query = "Select * from adviser_tbl ORDER BY name";
                                                        $adv_result = mysqli_query($con, $adv_query);
                                                        while ($adv_row = mysqli_fetch_assoc($adv_result)) {
                                                            echo "<option value='" . $adv_row['id'] . "'>" . $adv_row['name'] . "</option>";
                                                        }
                                                        ?>
                                                 </select>
                                             </div>
                                         </div>

                                         <div class="form-group error">
                                             <label for="inputTask" class="col-sm-2 control-label">Notes</label>
                                             <div class="col-sm-10">
                                                 <textarea class="form-control has-error" id="notes" name="notes" placeholder="Notes" required></textarea>
                                             </div>
                                         </div>

                                         <div class="form-group error" id="status_div" style="display:none;">
                                             <label for="inputTask" class="col-sm-2 control-label">Status</label>
                                             <div class="col-sm-4">
                                                 <select name="status" id="status" class="form-control" required />
                                                 <option>Seen</option>
                                                 <option>Agreement</option>
                                                 <option>Cancelled</option>
                                                 </select>
                                             </div>

                                             <label for="inputTask" class="col-sm-1 control-label">Date</label>
                                             <div class="col-sm-4">
                                                 <input type="text" class="form-control has-error datepicker" id="date_status_updated" name="date_status_updated" placeholder="Date" value="" required>
                                             </div>
                                         </div>

                                         <input type="hidden" id="client_id" name="client_id" value="0">
                                         <input type="hidden" id="formtype" name="formtype" value="0">
                                     </form>
                                 </div>
                                 <div class="modal-footer">
                                     <button type="button" class="btn btn-primary" id="btn-save" value="add"><i id="save_client_spinner" class="fas fa-spinner fa-spin" style="display:none;"></i>Save</button>
                                 </div>
                             </div>
                         </div>
             </div>
             <!--
	End of Editor
	-->

             <!--
		Confirm Delete
	-->
             <div class="modal fade" id="confirmModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                 <div class="modal-dialog">
                     <div class="modal-content">
                         <div class="modal-header">
                             <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                             <h4 class="modal-title" id="myModalLabel">Confirm Deletion</h4>
                         </div>
                         <form id="frmDelUser" name="frmDelUser" class="form-horizontal" novalidate="">
                             <div class="modal-body">
                                 <div class="form-group error">
                                     <label for="inputTask" class="col-sm-12 control-label">Are you sure you want to delete this Client?
                                     </label>
                                 </div>
                             </div>
                             <div class="modal-footer">
                                 <button type="button" class="btn btn-danger" id="btn-delete-confirm" value="Yes">Confirm Delete</button>
                                 <button type="button" class="btn btn-primary" id="btn-delete-cancel" value="No">Cancel</button>
                                 <input name="_method" id="_method" type="hidden" value="delete" />
                                 <input type="hidden" id="delete-client" value="0">
                             </div>
                         </form>
                     </div>
                 </div>
             </div>
             <!--
		End of Confirm Delete
       -->

             <!--
            Send Client Data
        -->
             <div class="modal fade" id="sendModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                 <div class="modal-dialog">
                     <div class="modal-content">
                         <div class="modal-header">
                             <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                             <h4 class="modal-title" id="myModalLabel">Send Client Data</h4>
                         </div>
                         <form id="frmSendData" name="frmSendData" class="form-horizontal" novalidate="">
                             <div class="modal-body">
                                 <div class="row form-group error">
                                     <label for="inputTask" class="col-sm-2 control-label">Name</label>
                                     <div class="col-sm-10">
                                         <input type="text" class="form-control has-error" id="send_name" name="name" placeholder="Receipient Name" value="">
                                     </div>
                                 </div>
                                 <br>
                                 <div class="row form-group error" >
                                     <label for="inputTask" class="col-sm-2 control-label">Email</label>
                                     <div class="col-sm-10">
                                         <input type="text" class="form-control has-error" id="send_email" name="email" placeholder="Receipient Email" value="">
                                         <input type="hidden" class="form-control has-error" id="send_client_id" name="client_id" placeholder="" value="">
                                     </div>
                                 </div>
                             </div>
                             <div class="modal-footer">
                                 <button type="button" class="btn btn-danger" id="btn-data-send" value="Yes"><i id="send_spinner" class="fas fa-spinner fa-spin" style="display:none;"></i> Send Client Data</button>
                             </div>
                         </form>
                     </div>
                 </div>
             </div>
             <!--
            End of Send Client Data
        -->



        <script src="js/loading.js"></script>
             <script src="js/clients-crud.js"></script>
             <script>
                 var table = null;
                 $(function() {

                     $('.datepicker').datepicker({
                         dateFormat: 'dd/mm/yy'
                     });


                     $('#clients_table').dataTable({
                         "order": [
                             [2, "asc"]
                         ],
                         "columnDefs": [{
                             "targets": [1, 2],
                             "orderable": true
                         }]
                     });

                     table = $("#clients_table").DataTable();
                     var counter = 1;

                     $(".leadgen").on('change', function() {
                         $("#leadgen").val($(this).val());
                     });

                     $("#lead_by").on('change', function() {
                         $(".leadgen").slideUp();
                         var lead_by = $(this).val();
                         var lg_field = $("#leadgen");

                         if (lead_by == "Self-Generated") {
                             lg_field.val(0);
                         } else if (lead_by == "Telemarketer") {
                             $("#leadgen_telemarketer").val("0");
                             $("#leadgen_telemarketer").slideDown();
                         } else if (lead_by == "Face-to-Face Marketer") {
                             $("#leadgen_bdm").val("0");
                             $("#leadgen_bdm").slideDown();
                         }
                     });
                 });
             </script>


             <?php
                    if (isset($_GET["edit"])) {
                        ?>

                 <script>
                     <?php echo "let edit_id = " . $_GET["edit"] . ";
							let name = '" . $_GET["name"] . "'
				 " ?>
                     $(function() {
                         table.search(name).draw();

                         setTimeout(function() {
                             $("#btn-edit-" + edit_id).trigger("click");
                         }, 50);
                     });
                 </script>
             <?php
                    }
                    ?>

     </body>

     </html>

 <?php

    }
    ?>