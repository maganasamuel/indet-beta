<?php
    session_start();
    include_once("libs/api/classes/general.class.php");
    include_once("libs/api/controllers/LeadGenerator.controller.php");

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
     <title>Telemarketers</title>

 </head>

 <body>
     <div align="center">
         <div class="jumbotron">
             <h2 class="slide">Telemarketers</h2>

         </div>
         <?php require "database.php";
                $leadgenController = new LeadGeneratorController();
                $generalController = new General();
                ?>
         <div class="margined table-responsive">
             <table data-toggle="table" id='leadgens_table' class="table table-striped display" cellpadding="5px" cellspacing="5px" width='95%'>
                 <thead>
                     <tr>
                         <th colspan="6" class="text-center"></th>
                         <th colspan="2" class="text-center"><button id="btn-add" name="btn-add" data-toggle="modal" data-target="#myModal" class="pull-right btn btn-primary btn-m"><i class="fa fa-plus"></i> Add New Telemarketer</button></th>
                     </tr>
                     <tr>
                         <th class="text-center">Name</th>
                         <th class="text-center">Email</th>
                         <th class="text-center">Birthday</th>
                         <th class="text-center">Leads Generated</th>
                         <th class="text-center">Leads Cancelled</th>
                         <th class="text-center"></th>
                         <th class="text-center"></th>
                         <th class="text-center"></th>
                     </tr>
                 </thead>

                 <tbody id="leadgens-list">
                     <?php
                            $telemarketers = $leadgenController->getAllTelemarketers();
                            while ($rows = $telemarketers->fetch_assoc()) :
                                extract($rows);

                                $email = $generalController->convertToNA($rows, "email");
                                $birthday = $generalController->convertToNA($rows, "birthday");

                                if($birthday!=""&&$birthday!="N/A"){                                    
                                    $birthday = date("d/m/Y", strtotime($birthday));
                                }

                                echo "
            <tr id='leadgen$id' cellpadding='5px' cellspacing='5px'>
                <td>$name</td>
                <td>$email</td>
                <td>$birthday</td>
                <td>$leads_generated</td>
                <td>$leads_cancelled</td>
                ";
                                ?>

                     <td><input type="image" class="delete-leadgen" src="delete.png" value='<?php echo "$id" ?>'></td>
                     <td><input data-toggle="modal" data-target="#myModal" type="image" class="open-modal" src="edit.png" value='<?php echo "$id" ?>'></td>
                     <td><a href="leadgen_profile<?php echo "?id=$id" ?>" class="btn btn-primary"><i class="fas fa-search"></i></a></td>

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
                         <h4 class="modal-title" id="myModalLabel" style="color:white;">Telemarketer Editor</h4>
                     </div>
                     <div class="modal-body">
                         <form id="frmLeadgen" name="frmLeadgen" class="form-horizontal" novalidate="">
                             <div class="form-group error">
                                 <label for="inputTask" class="col-sm-3 control-label">Name</label>
                                 <div class="col-sm-9">
                                     <input type="text" class="form-control has-error" id="name" name="name" placeholder="Name" value="" required>
                                 </div>
                             </div>
                             <div class="form-group error">
                                 <label for="inputTask" class="col-sm-3 control-label">Email</label>
                                 <div class="col-sm-9">
                                     <input type="text" class="form-control has-error" id="email" name="email" placeholder="Email" value="" required>
                                 </div>
                             </div>
                             <div class="form-group error">
                                 <label for="inputTask" class="col-sm-3 control-label">Birthday</label>
                                 <div class="col-sm-9">
                                     <input type="text" class="datepicker form-control has-error" id="birthday" name="birthday" placeholder="Birthday" value="" required>
                                 </div>
                             </div>

                             <input type="hidden" id="type" name="type" value="Face-to-Face Marketer">
                             <input type="hidden" id="action" name="action" value="create_lead_generator">
                             <input type="hidden" id="leadgen_id" name="id" value="0">
                             <input type="hidden" id="formtype" name="formtype" value="0">
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
                     <div class="modal-body">
                         <form id="frmDelUser" name="frmDelUser" class="form-horizontal" novalidate="">
                             <div class="form-group error">
                                 <label for="inputTask" class="col-sm-12 control-label">Are you sure you want to delete this Telemarketer?
                                 </label>

                             </div>
                             <div class="modal-footer">
                                 <button type="button" class="btn btn-danger" id="btn-delete-confirm" value="Yes">Confirm Delete</button>
                                 <button type="button" class="btn btn-primary" id="btn-delete-cancel" value="No">Cancel</button>
                                 <input name="_method" id="_method" type="hidden" value="delete" />
                                 <input type="hidden" id="delete-leadgen" value="0">
                             </div>
                     </div>
                 </div>
             </div>
             <!--
		End of Confirm Delete
   	-->
            <script src="js/date_helper.js"></script>
             <script src="js/leadgen-crud.js"></script>
             <script>
                 var table = null;
                 $(function() {

                     $('.datepicker').datepicker({
                         dateFormat: 'dd/mm/yy'
                     });

                     $('#leadgens_table').DataTable();

                     table = $("#leadgens_table").DataTable();
                     var counter = 1;
                 });
             </script>
 </body>

 </html>

 <?php

    }
    ?>