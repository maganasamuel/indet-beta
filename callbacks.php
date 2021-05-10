 <?php
session_start();

//Restrict access to admin only
include "partials/admin_only.php";

if (empty($_SESSION['token'])) {
    $_SESSION['token'] = bin2hex(random_bytes(32));
}
$token = $_SESSION['token'];
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
<?php echo '<meta name="_token" content="$token">'; ?>
<link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">
<link rel="stylesheet" href="styles.css">
<link id="favicon" rel="icon" href="Logo_ImageOnly.png" type="image/png" sizes="16x16">
<title>INDET</title>
	<title>Callbacks</title>
</head>

<body>
<div align="center">
  <div class="jumbotron">
    <h2 class="slide">Callbacks</h2>
</div>
<?php require "database.php";
	$query = "SELECT c.* FROM callbacks c LEFT JOIN users u ON c.agent_id = u.linked_id where u.id = " . $_SESSION['myuserid'];
	$displayquery=mysqli_query($con,$query) or die('Could not look up script information; ' . mysqli_error($con));
?>
<div class="margined table-responsive">
<table data-toggle="table" id='callbacks_table' class="table table-striped display" cellpadding="5px" cellspacing="5px" width='95%'>
    <thead>
        <tr>
            <th class="text-center">Name</th>
            <th class="text-center">Callback Date</th>
            <th class="text-center">Callback Time</th>
            <th class="text-center">Notes</th>
            <th class="text-center"></th>
            <th class="text-center"></th>
        </tr>
    </thead>

    <tbody id="callbacks-list">
        <?php
        WHILE($rows = mysqli_fetch_array($displayquery)):
            extract($rows);
            $callback_time = date("g:i A", strtotime($callback_time));
            echo "
            <tr id='callback$id' cellpadding='5px' cellspacing='5px'>
                <td>$name</td>
                <td>$callback_date</td>
                <td>" . $callback_time . "</td>
                <td>$notes</td>
                ";
        ?>

            <!--
                <td><input data-toggle="modal" data-target="#myModal" type="image" class="open-modal"  src="edit.png" value='<?php echo "$id" ?>'></td>    
            -->
            <td><a onclick="document.getElementById('form_<?php echo $id ?>').submit(); return false;"><strong style="font-size:25px;"><i class="fas fa-sync-alt"></i></strong></a>

                <form id="form_<?php echo $id ?>" action="main.php" method="POST" style="display:none;">
                    <input type="hidden" name="leads_data_id" value="<?php echo $leads_data_id ?>">
                    <input type="hidden" name="callback_id" value="<?php echo $id ?>">
                </form>
                
            </td>
            <td><input type="image" class="delete-callback"  src="delete.png" value='<?php echo "$id" ?>'></td>
                
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
                    <h4 class="modal-title" id="myModalLabel" style="color:white;">Callback Editor</h4>
                </div>
                <div class="modal-body">
                    <form id="frmCallback" name="frmScript" class="form-horizontal" novalidate="">                    
                        <div class="form-group">
                            <div class="col-sm-3">
                                <label for="callback_date">Date</label>
                            </div>
                            <div class="col-sm-9">
                                <input type="text" id="callback_date" name="callback_date" class="form-control datepicker" aria-describedby="callback_date" placeholder="Date" value="<?php echo date('d/m/Y')?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-3">
                                <label for="date">Time</label>
                            </div>
                            <div class="col-sm-9">
                                <input type="time" id="callback_time" name="callback_time" class="form-control">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-3">
                                <label for="notes">Notes</label>
                            </div>
                            <div class="col-sm-9">
                                <textarea class="form-control" id="notes" aria-describedby="notes" placeholder="Notes"></textarea>
                            </div>
                        </div>

                        <button type="button" id="save_callback" class="btn btn-success form-control"><i class="fas fa-save"></i> Save</button>
                    
                    </div>
                        <input type="hidden" id="callback_id" name="callback_id" value="">
                        <input type="hidden" id="is_update" name="is_update" value="No">
                    </form>
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
                        	<label for="inputTask" class="col-sm-12 control-label">Are you sure you want to delete this Callback?
                          	</label>

                        </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" id="btn-delete-confirm" value="Yes">Confirm Delete</button>
                    <button type="button" class="btn btn-primary" id="btn-delete-cancel" value="No">Cancel</button>
                    <input name="_method" id="_method" type="hidden" value="delete" />
                    <input type="hidden" id="delete-callback" value="0">
                </div>
            </div>
        </div>
    </div>
    <!--
		End of Confirm Delete
       -->
       
      <script src="js/callbacks-crud.js"></script>
      <script>
          var table = null;
          $(function(){
            $('#callbacks_table').dataTable({
                "order": [[ 0, "asc" ]],
                "columnDefs": 
                [ {
                "targets": [1,2],
                "orderable": true
                } ]
            });

            table = $("#callbacks_table").DataTable();
            var counter = 1;

        });
      </script>
</body>

</html>

<?php

}
?>