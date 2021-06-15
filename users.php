 <?php
    session_start();

    //Restrict access to admin only
    include "partials/admin_only.php";

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
     <title>Users</title>
     <script>
         $(function() {

             /*
             $('#me').dataTable({
             "columnDefs": [ {
             "targets": [14,15],
             "orderable": false
             } ]
             });

             */
         });
     </script>
 </head>

 <body>
     <div align="center">
         <div class="jumbotron">
             <h2 class="slide">Users</h2>

         </div>
         <?php require "database.php";
                $query = "SELECT * FROM users";
                $displayquery = mysqli_query($con, $query) or die('Could not look up user information; ' . mysqli_error($con));
                ?>
         <div class="margined table-responsive">
             <table id='me' data-toggle="table" id='example' class="table table-striped " cellpadding="5px" cellspacing="5px" width='95%'>
                 <thead>
                     <th class="text-center">Username</th>
                     <th class="text-center">Type</th>
                     <th colspan="2"><button id="btn-add" name="btn-add" data-toggle="modal" data-target="#myModal" class="pull-right btn btn-primary btn-m"><i class="fa fa-plus"></i> Add New User</button></th>
                 </thead>
                 <tbody id="users-list" name="users-list">
                     <?php
                            while ($rows = mysqli_fetch_array($displayquery)) :
                                extract($rows);
                                echo "
			<tr id='user$id' cellpadding='5px' cellspacing='5px'>
			<td>$username</td>
			<td>$type</td>
			";
                                ?>

                     <td><input type="image" class="delete-user" src="delete.png" value='<?php echo "$id" ?>'></td>
                     <td><input data-toggle="modal" data-target="#myModal" type="image" class="open-modal" src="edit.png" value='<?php echo "$id" ?>'></td>

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
         <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
             <div class="modal-dialog">
                 <div class="modal-content">
                     <div class="modal-header" style="background-color: #286090; ">
                         <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span style="color:white;" aria-hidden="true">×</span></button>
                         <h4 class="modal-title" id="myModalLabel" style="color:white;">User Editor</h4>
                     </div>
                     <div class="modal-body">
                         <form id="frmUser" name="frmUser" class="form-horizontal" novalidate="">
                             <div class="form-group error">
                                 <label for="inputTask" class="col-sm-3 control-label">Username</label>
                                 <div class="col-sm-9">
                                     <input type="text" class="form-control has-error" id="username" name="username" placeholder="Username" value="" required>
                                 </div>
                             </div>
                             <div class="form-group error">
                                 <label for="inputTask" class="col-sm-3 control-label">Password</label>
                                 <div class="col-sm-9">
                                     <input type="password" class="form-control has-error" id="password" name="password" placeholder="Password" value="" required minlength="6" required="">
                                     <label id="password_label" for="password" data-error="" data-success="Perfect!" style="color:red;"></label>
                                 </div>
                             </div>
                             <div class="form-group error">
                                 <label for="inputTask" class="col-sm-3 control-label">Confirm Password</label>
                                 <div class="col-sm-9">
                                     <input type="password" class="form-control has-error" id="confirm_password" placeholder="Password" value="" required>
                                     <label id="confirm_password_label" for="confirm_password" data-error="Please enter the same password again" data-success="Perfect!" style="color:red;"></label>
                                 </div>
                             </div>
                             <div class="form-group error">
                                 <label for="inputTask" class="col-sm-3 control-label">Type</label>
                                 <div class="col-sm-9">
                                     <select class="form-control has-error" id="type" name="type" required>
                                         <option>User</option>
                                         <option>Face-to-Face Marketer</option>
                                         <option>Telemarketer</option>
                                         <option>Adviser</option>
                                         <option>Admin</option>
                                     </select>
                                 </div>
                             </div>

                             <div class="form-group error" id="linked_id_div">
                                 <label for="inputTask" class="col-sm-3 control-label">Linked ID</label>
                                 <div class="col-sm-9">

                                     <select class="form-control has-error linked_ids" id="personal_data_linked" name="personal_data_linked" required>
                                         <option disabled hidden selected value="0">Select Data</option>
                                         <?php
                                                $telequery = "Select * from personal_data ORDER BY full_name";
                                                $teleresult = mysqli_query($con, $telequery);
                                                while ($telerow = mysqli_fetch_assoc($teleresult)) {
                                                    echo "<option value='" . $telerow['id'] . "'>" . $telerow['full_name'] . "</option>";
                                                }
                                                ?>
                                     </select>

                                     <select class="form-control has-error linked_ids" id="telemarketer_linked" name="telemarketer_linked" style="display:none;" required>
                                         <option disabled hidden selected value="0">Select Telemarketer</option>
                                         <?php
                                                $telequery = "Select * from leadgen_tbl where type='Telemarketer' ORDER BY name";
                                                $teleresult = mysqli_query($con, $telequery);
                                                while ($telerow = mysqli_fetch_assoc($teleresult)) {
                                                    echo "<option value='" . $telerow['id'] . "'>" . $telerow['name'] . "</option>";
                                                }
                                                ?>
                                     </select>

                                     <select class="form-control has-error linked_ids" id="f2fmarketer_linked" name="f2fmarketer_linked" style="display:none;" required>
                                         <option disabled hidden selected value="0">Select Face to face Marketer</option>
                                         <?php
                                                $marketerquery = "Select * from leadgen_tbl where type='Face-to-Face Marketer' ORDER BY name";
                                                $marketerresult = mysqli_query($con, $marketerquery);
                                                while ($marketerrow = mysqli_fetch_assoc($marketerresult)) {
                                                    echo "<option value='" . $marketerrow['id'] . "'>" . $marketerrow['name'] . "</option>";
                                                }
                                                ?>
                                     </select>

                                     <select class="form-control has-error linked_ids" id="adviser_linked" name="adviser_linked" style="display:none;" required>
                                         <option disabled hidden selected value="0">Select Adviser</option>
                                         <?php
                                                $adviser_query = "Select * from adviser_tbl ORDER BY name";
                                                $adviser_result = mysqli_query($con, $adviser_query);
                                                while ($adviser_row = mysqli_fetch_assoc($adviser_result)) {

                                                    echo "<option value='" . $adviser_row['id'] . "'>" . $adviser_row['name'] . "</option>";
                                                }
                                                ?>
                                     </select>

                                 </div>
                             </div>

                             <input type="hidden" id="linked_id" name="linked_id" class="form-control has-error" value="0">
                             <input type="hidden" id="formtype" name="formtype" value="0">
                             <input type="hidden" id="user_id" name="user_id" value="0">
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
                                 <label for="inputTask" class="col-sm-12 control-label">Are you sure you want to delete this User?
                                 </label>

                             </div>
                         </div>
                         <div class="modal-footer">
                             <button type="button" class="btn btn-danger" id="btn-delete-confirm" value="Yes">Confirm Delete</button>
                             <button type="button" class="btn btn-primary" id="btn-delete-cancel" value="No">Cancel</button>
                             <input name="_method" id="_method" type="hidden" value="delete" />
                             <input type="hidden" id="delete-user" value="0">
                         </div>
                     </form>
                 </div>
             </div>
         </div>
         <!--
		End of Confirm Delete
   	-->
         <script src="js/users-crud.js"></script>
         <script>
             $(function() {

                 $("#type").on('change', function() {
                     var usertype = $(this).val();
                     $("#telemarketer_linked").hide();
                     $("#adviser_linked").hide();
                     $("#personal_data_linked").hide();
                     $("#f2fmarketer_linked").hide();
                     if (usertype == "Adviser") {
                         $("#adviser_linked").show();
                     } else if (usertype == "Telemarketer") {
                         $("#telemarketer_linked").show();
                     } else if (usertype == "Admin" || usertype == "User") {
                         $("#personal_data_linked").show();
                     } else if (usertype == "Face-to-Face Marketer") {
                         $("#f2fmarketer_linked").show();
                     } 

                     $("#linked_id_div").slideDown();

                     $("#telemarketer_linked").val("0");
                     $("#f2fmarketer_linked").val("0");
                     $("#adviser_linked").val("0");
                 });

                 $(".linked_ids").on('change', function() {
                     var linked_id = $(this).val();
                     $("#linked_id").val(linked_id);
                 });

             });
         </script>
 </body>

 </html>

 <?php

    }
    ?>