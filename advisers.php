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
                 <h2 class="slide">Adviser Profiles</h2>
             </div>
             <?php require "database.php";
                    include_once("libs/api/controllers/Adviser.controller.php");
                    $adviserController = new AdviserController();
                    $advisers = $adviserController->getAllAdvisers();
                    ?>
             <div class="margined table-responsive">
                 <div class="row">
                     <div class="col-sm-9 text-center"></div>
                     <div class="col-sm-3 text-center"><button id="btn-add" name="btn-add" data-toggle="modal" data-target="#myModal" class="pull-right btn btn-primary btn-m"><i class="fa fa-plus"></i> Add New Adviser</button></div>
                 </div>
                 <br>
                 <table id='advisers_table' data-toggle="table" class="table table-striped " cellpadding="5px" cellspacing="5px" width='95%' style=" display: block; overflow-x: auto; white-space: nowrap;">
                     <thead>
                         <td>Adviser Name</td>
                         <td>Adviser FSP number</td>
                         <td>Adviser Address</td>
                         <!--td>IRD number</td-->
                         <td>Email Address</td>
                         <td>Leads Charge</td>
                         <td>Issued Charge</td>

                         <!--
                <td><a  id="deleteall" class="a" href="delete_adviser.php?del_id=all">
                <img src="delete.png" />
                </a>
                </td>
	-->
                         <td></td>
                         <td></td>
                     </thead>

                     <tbody id="advisers-list"">
        <?php
            while ($rows = $advisers->fetch_assoc()) :
                $id = $rows["id"];
                $name = $rows["name"];
                $fsp_num = $rows["fsp_num"];
                $address = $rows["address"];
                $ird_num = $rows["ird_num"];
                $email = $rows["email"];
                $leads = $rows["leads"];
                $bonus = $rows["bonus"];


                echo "
        <tr id='adviser$id' cellpadding='5px' cellspacing='5px'>
            <td>$name</td>
            <td>$fsp_num</td>
            <td>$address</td>
            <td>$email</td>
            <td>$leads</td>
            <td>$bonus</td>        
        ";

                ?>

            <td><input data-toggle="modal" data-target="#myModal" type="image" class="open-modal" src="edit.png" data-toggle="tooltip" title="Edit Adviser Profile" value='<?php echo "$id" ?>'>
                         </td>
                         <td>
                             <a href="adviser_profile.php<?php echo "?id=$id" ?>" class="btn btn-primary" data-toggle="tooltip" title="View Adviser Profile"><i class="fas fa-search"></i></a>
                             &nbsp;
                             <a href="adviser_strings.php?adviser_id=<?php echo $id; ?>">View Strings</i></a>
                        </td>
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
                             <h4 class="modal-title" id="myModalLabel" style="color:white;">Adviser Editor</h4>
                         </div>
                         <div class="modal-body">
                             <form id="frmAdviser" name="frmAdviser" class="form-horizontal" novalidate="">
                                 <div class="form-group error">
                                    <div class="col-sm-8">
                                        <div class="row">
                                            <div class="col">

                                                <div class="row" style="margin-top: 10px !important;">
                                                    <div class="col">
                                                        <label for="inputTask" class="col-sm-4 control-label">Position</label>
                                                        <div class="col-sm-8">
                                                            <select id="position" class="form-control" name="position_id" required />
                                                            <option value="" selected disabled>--Select Position--</option>
                                                                <?php

                                                                    $query = "SELECT * from positions ORDER BY name ASC";
                                                                    $displayquery = mysqli_query($con, $query) or die('Could not look up user information; ' . mysqli_error($con));

                                                                    while ($rows = mysqli_fetch_array($displayquery)) {
                                                                        $id = $rows["id"];
                                                                        $name = $rows["name"];
                                                                        //echo "<option value='".$id."'>".$name."</option>";
                                                                        if ($name != "EliteInsure Team")
                                                                            echo "<option value='" . $id . "'>" . $name . "</option>";
                                                                    }
                                                                ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="row" style="margin-top: 10px !important;">
                                                    <div class="col">
                                                        <label for="inputTask" class="col-sm-4 control-label">Name</label>
                                                        <div class="col-sm-8">
                                                            <input type="text" class="form-control has-error" id="name" name="name" placeholder="Name" value="" required>
                                                        </div>
                                                    </div>
                                                </div>
                            
                                                <div class="row"  style="margin-top: 10px !important;">
                                                    <div class="col">
                                                        <label for="inputTask" class="col-sm-4 control-label">Company Name</label>
                                                        <div class="col-sm-8">
                                                            <input type="text" class="form-control has-error" id="company_name" name="company_name" placeholder="Company Name" value="" required>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div class="row" style="margin-top: 10px !important;">
                                                    <div class="col">
                                                        <label for="inputTask" class="col-sm-4 control-label">Payroll Name</label>
                                                        <div class="col-sm-8">
                                                            <input type="text" class="form-control has-error" id="payroll_name" name="payroll_name" placeholder="Payroll Name" value="" required>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="row" style="margin-top: 10px !important;">
                                                    <div class="col">
                                                        <label for="inputTask" class="col-sm-4 control-label">Team (ADR)</label>
                                                        <div class="col-sm-8">
                                                            <select id="team" class="form-control" name="team_id" required />
                                                            <option value="0" selected>None</option>
                                                                <?php

                                                                    $query = "SELECT * from teams ORDER BY name ASC";
                                                                    $displayquery = mysqli_query($con, $query) or die('Could not look up user information; ' . mysqli_error($con));

                                                                    while ($rows = mysqli_fetch_array($displayquery)) {
                                                                        $id = $rows["id"];
                                                                        $name = $rows["name"];
                                                                        //echo "<option value='".$id."'>".$name."</option>";
                                                                        if ($name != "EliteInsure Team")
                                                                            echo "<option value='" . $id . "'>" . $name . "</option>";
                                                                    }
                                                                ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="row" style="margin-top: 10px !important;">
                                                    <div class="col">
                                                        <label for="inputTask" class="col-sm-4 control-label">Team (SADR)</label>
                                                        <div class="col-sm-8">
                                                            <select id="steam" class="form-control" name="steam_id" required />
                                                            <option value="0" selected>None</option>
                                                                <?php

                                                                    $query = "SELECT * from steams ORDER BY name ASC";
                                                                    $displayquery = mysqli_query($con, $query) or die('Could not look up user information; ' . mysqli_error($con));

                                                                    while ($rows = mysqli_fetch_array($displayquery)) {
                                                                        $id = $rows["id"];
                                                                        $name = $rows["name"];
                                                                        //echo "<option value='".$id."'>".$name."</option>";
                                                                        if ($name != "EliteInsure Team")
                                                                            echo "<option value='" . $id . "'>" . $name . "</option>";
                                                                    }
                                                                ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="row" style="margin-top: 10px !important;">
                                                    <div class="col">
                                                        <label for="inputTask" class="col-sm-4 control-label">FSP Number</label>
                                                        <div class="col-sm-8">
                                                        <input type="text" class="form-control has-error" id="fsp_num" name="fsp_num" placeholder="FSP Number" value="" required>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="row" style="margin-top: 10px !important;">
                                                    <div class="col">
                                                        <label for="inputTask" class="col-sm-4 control-label">Birthday</label>
                                                        <div class="col-sm-8">
                                                        <input type="text" class="form-control has-error datepicker" id="birthday" name="birthday" placeholder="Birthday" value="" required>
                                                        </div>
                                                    </div>
                                                </div>


                                                <div class="row" style="margin-top: 10px !important;">
                                                    <div class="col">
                                                        <label for="inputTask" class="col-sm-4 control-label">Email Address </label>
                                                        <div class="col-sm-8">
                                                        <input type="text" class="form-control has-error" id="email" name="email" placeholder="Email Address" value="" required>
                                                        </div>
                                                    </div>
                                                </div>


                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">

                                            <div class="row">
                                                <div class="col">
                                                    <img alt="..." class="img-thumbnail" id="imgPreview" style="width:100%; min-height:30vh;"/>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col"><input type="file" class="form-control has-error" id="imageInput" name="imageInput" value="" required>
                                                <div class="col"><input type="hidden" class="form-control has-error" id="image" name="image" value="" required>
                                                </div>
                                            </div>
                                        </div>                                    </div>
                                 </div>
                                 

                                 <div class="form-group error row" style=" margin-top:10px !important;">
                                     <label for="inputTask" class="col-sm-2 control-label">Address</label>
                                     <div class="col-sm-10">
                                         <textarea class="form-control has-error" id="address" name="address" placeholder="Address" required></textarea>
                                     </div>
                                 </div>

                                 <div class="form-group error row" style="margin-top: 10px !important;">
                                     <label for="inputTask" class="col-sm-2 control-label">Leads</label>
                                     <div class="col-sm-4">
                                         <input type="text" class="form-control has-error" id="leads" name="leads" placeholder="Leads" value="">
                                     </div>
                                     <label for="inputTask" class="col-sm-2 control-label">Issue Charge</label>
                                     <div class="col-sm-4">
                                         <input type="text" class="form-control has-error" id="bonus" name="bonus" placeholder="Issue Charge" value="">
                                     </div>
                                 </div>
                                 <!--    
                                    <div class="form-group error">
                                        <label for="inputTask" class="col-sm-12"><strong><h3 class="text-center">API Integration</h3></strong></label>
                                    </div>

                                    <div class="form-group error">
                                        <label for="inputTask" class="col-sm-6 control-label">Adviser's Name in Payroll Software</label>
                                        <div class="col-sm-4">
                                            <input type="text" class="form-control has-error" id="payroll_name" name="payroll_name" placeholder="Name" value="">
                                        </div>
                                        <label for="inputTask" class="col-sm-2 control-label"></label>
                                        <div class="col-sm-4">
                                        </div>
                                    </div>
                                -->


                                <div class="form-group error row" style="margin-top: 10px !important;">
                                     <label for="inputTask" class="col-sm-2 control-label">Date Hired</label>
                                     <div class="col-sm-4">
                                         <input type="text" class="form-control has-error datepicker" id="date_hired" name="date_hired" placeholder="Date Hired" value="" required>
                                     </div>
                                     <label for="inputTask" class="col-sm-2 control-label">Date Terminated</label>
                                     <div class="col-sm-4">
                                         <input type="text" class="form-control has-error datepicker" id="termination_date" name="termination_date" placeholder="Date Terminated" value="" required>
                                     </div>
                                 </div>

                                 <input type="hidden" id="adviser_id" name="adviser_id" value="0">
                                 <input type="hidden" id="formtype" name="formtype" value="0">
                                 <input type="hidden" id="action" name="action" value="0">
                             </form>
                         </div>
                         <div class="modal-footer">
                             <button type="button" class="btn btn-primary" id="btn-save" value="add"><i id="save_spinner" class="fas fa-spinner fa-spin" style="display:none;"></i> Save</button>
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
                                 <input type="hidden" id="delete-adviser" value="0">
                             </div>
                         </form>
                     </div>
                 </div>
             </div>
             <!--
		End of Confirm Delete
   	-->
             <script src="js/date_helper.js"></script>
             <script src="js/advisers-crud.js"></script>
             <script>
                 var table = null;
                 $(function() {

                     $('body').tooltip({
                         selector: '[rel=tooltip]'
                     });

                     $('.datepicker').datepicker({
                         dateFormat: 'dd/mm/yy'
                     });


                     $('#advisers_table').dataTable({
                         "order": [
                             [0, "asc"]
                         ],
                         "columnDefs": [{
                             "targets": [1, 2],
                             "orderable": true
                         }]
                     });

                     table = $("#advisers_table").DataTable();
                     var counter = 1;

                 });
             </script>
     </body>

     </html>

 <?php

    }
    ?>