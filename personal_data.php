 <?php

    include_once("libs/api/classes/general.class.php");
    include_once("libs/api/controllers/PersonalData.controller.php");

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
        $dataController = new PersonalDataController();
        $generalController = new General();
    ?>
     <html>

     <head>

         <!--nav bar-->
         <?php
            include "partials/nav_bar.html";
            ?>
         <!--nav bar end-->
         <meta name="viewport" content="width=device-width, initial-scale=1">
         <?php echo '<meta name="_token" content="$token">'; ?>
         <link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">
         <link rel="stylesheet" href="styles.css">
         <link id="favicon" rel="icon" href="Logo_ImageOnly.png" type="image/png" sizes="16x16">
         <title>INDET</title>
         <title>Personal Data</title>

     </head>

     <body>
         <div align="center">
             <div class="jumbotron">
                 <h2 class="slide">Personal Data</h2>

             </div>

             <div class="margined table-responsive">
                 <table data-toggle="table" id='personal_data_table' class="table table-striped display" cellpadding="5px" cellspacing="5px" width='95%'>
                     <thead>
                         <tr>
                             <th colspan="4" class="text-center"></th>
                             <th colspan="2" class="text-center"><button id="btn-add" name="btn-add" data-toggle="modal" data-target="#myModal" class="pull-right btn btn-primary btn-m"><i class="fa fa-plus"></i> Add New Data</button></th>
                         </tr>
                         <tr>
                             <th class="text-center">Name</th>
                             <th class="text-center">Email</th>
                             <th class="text-center">Birthday</th>
                             <th class="text-center">Role</th>
                             <th class="text-center"></th>
                             <th class="text-center"></th>
                         </tr>
                     </thead>

                     <tbody id="datas-list">
                         <?php

                            $data = $dataController->getAllData();

                            while ($rows = $data->fetch_assoc()) :
                                extract($rows);

                                $email = $generalController->convertToNA($rows, "email");
                                $birthday = $generalController->convertToNA($rows, "birthday");

                                if ($birthday != "" && $birthday != "N/A") {
                                    $birthday = date("d/m/Y", strtotime($birthday));
                                }

                                echo "
            <tr id='data$id' cellpadding='5px' cellspacing='5px'>
                <td>$full_name</td>
                <td>$email</td>
                <td>$birthday</td>
                <td>$role</td>
                ";
                            ?>

                             <td><input type="image" class="delete-data" src="delete.png" value='<?php echo "$id" ?>'></td>
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
                 <div class="modal-dialog modal-lg">
                     <div class="modal-content">
                         <div class="modal-header" style="background-color: #286090; ">
                             <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span style="color:white;" aria-hidden="true">×</span></button>
                             <h4 class="modal-title" id="myModalLabel" style="color:white;">Data Editor</h4>
                         </div>
                         <div class="modal-body">
                             <form id="frmData" name="frmData" class="form-horizontal" novalidate="">
                                 <div class="row">
                                     <div class="col-sm-8">
                                         <div class="form-group error">
                                             <label for="inputTask" class="col-sm-3 control-label">Name</label>
                                             <div class="col-sm-9">
                                                 <input type="text" class="form-control has-error" id="full_name" name="full_name" placeholder="Name" value="" required>
                                             </div>
                                         </div>
                                         <div class="form-group error">
                                             <label for="inputTask" class="col-sm-3 control-label">Email</label>
                                             <div class="col-sm-9">
                                                 <input type="text" class="form-control has-error" id="email" name="email" placeholder="Email" value="" required>
                                             </div>
                                         </div>
                                         <div class="form-group error">
                                             <label for="inputTask" class="col-sm-3 control-label">Role</label>
                                             <div class="col-sm-9">
                                             <select name="role_selection" id="role_selection" class="form-control has-error">
                                                <option value="" selected disabled hidden>Select Role</option>
                                                <option>Junior Admin</option>
                                                <option>Admin</option>
                                                <option>Executive Admin</option>
                                                <option>Other</option>
                                             </select>
                                                 <input type="text" class="form-control has-error" id="role" name="role" placeholder="Role" value="" style="display:none;">
                                             </div>
                                         </div>
                                         <div class="form-group error">
                                             <label for="inputTask" class="col-sm-3 control-label">Birthday</label>
                                             <div class="col-sm-9">
                                                 <input type="text" class="datepicker form-control has-error" id="birthday" name="birthday" placeholder="Birthday" value="" required>
                                             </div>
                                         </div>
                                         <div class="form-group error">
                                             <label for="inputTask" class="col-sm-3 control-label">Date Hired</label>
                                             <div class="col-sm-9">
                                                 <input type="text" class="datepicker form-control has-error" id="date_hired" name="date_hired" placeholder="Date Hired" value="" required>
                                             </div>
                                         </div>
                                         <div class="form-group error">
                                             <label for="inputTask" class="col-sm-3 control-label">Termination Date</label>
                                             <div class="col-sm-9">
                                                 <input type="text" class="datepicker form-control has-error" id="termination_date" name="termination_date" placeholder="Termination Date" value="" required>
                                             </div>
                                         </div>
                                     </div>
                                     <div class="col-sm-4">
                                         <div class="row">
                                             <div class="col">
                                                 <img alt="..." class="img-thumbnail" id="imgPreview" style="width:100%; min-height:30vh;" />
                                             </div>
                                         </div>
                                         <div class="row" style="padding-bottom: 50px !important;">
                                             <div class="col"><input type="file" class="form-control has-error" id="imageInput" name="imageInput" value="" required>
                                                 <div class="col"><input type="hidden" class="form-control has-error" id="image" name="image" value="" required>
                                                 </div>
                                             </div>
                                         </div>
                                     </div>


                                     <input type="hidden" id="action" name="action" value="create_data">
                                     <input type="hidden" id="data_id" name="id" value="0">
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
                         <form id="frmDelUser" name="frmDelUser" class="form-horizontal" novalidate="">
                             <div class="modal-body">
                                 <div class="form-group error">
                                     <label for="inputTask" class="col-sm-12 control-label">Are you sure you want to delete this Data?
                                     </label>

                                 </div>

                             </div>
                             <div class="modal-footer">
                                 <button type="button" class="btn btn-danger" id="btn-delete-confirm" value="Yes">Confirm Delete</button>
                                 <button type="button" class="btn btn-primary" id="btn-delete-cancel" value="No">Cancel</button>
                                 <input name="_method" id="_method" type="hidden" value="delete" />
                                 <input type="hidden" id="delete-data" value="0">
                             </div>
                         </form>
                     </div>
                 </div>
             </div>
             <!--
		End of Confirm Delete
       -->
             <script src="js/date_helper.js"></script>
             <script src="js/personal_data-crud.js"></script>
             <script>
                 var table = null;
                 $(function() {

                     $('.datepicker').datepicker({
                         dateFormat: 'dd/mm/yy'
                     });

                     $('#personal_data_table').DataTable();

                     table = $("#personal_data_table").DataTable();
                     var counter = 1;
                 });
             </script>
     </body>

     </html>

 <?php

    }
    ?>