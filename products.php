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
	<title>Products</title>

</head>

<body>
<div align="center">
  <div class="jumbotron">
    <h2 class="slide">Products Interested In:</h2>
	
</div>
<?php require "database.php";
	$query = "SELECT * FROM products";
	$displayquery=mysqli_query($con,$query) or die('Could not look up product information; ' . mysqli_error($con));
?>
<div class="margined table-responsive">
<table data-toggle="table" id='products_table' class="table table-striped display" cellpadding="5px" cellspacing="5px" width='95%'>
    <thead>
        <tr>
            <th colspan="3" class="text-center"></th>
            <th colspan="2" class="text-center"><button id="btn-add" name="btn-add" data-toggle="modal" data-target="#myModal" class="pull-right btn btn-primary btn-m"><i class="fa fa-plus"></i> Add New Product</button></th>
        </tr>
        <tr>
            <th class="text-center">Acronym</th>
            <th class="text-center">Name</th>
            <th class="text-center"></th>
            <th class="text-center"></th>
        </tr>
    </thead>

    <tbody id="products-list"">
        <?php
        WHILE($rows = mysqli_fetch_array($displayquery)):
            extract($rows);

            if($acronym=="")
                $acronym = "N/A";

            echo "
            <tr id='product$id' cellpadding='5px' cellspacing='5px'>
                <td>$acronym</td>
                <td>$name</td>
                ";
        ?>

            <td><input type="image" class="delete-product"  src="delete.png" value='<?php echo "$id" ?>'></td>
            <td><input data-toggle="modal" data-target="#myModal" type="image" class="open-modal"  src="edit.png" value='<?php echo "$id" ?>'></td>

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
                    <h4 class="modal-title" id="myModalLabel" style="color:white;">Product Editor</h4>
                </div>
                <div class="modal-body">
                    <form id="frmProduct" name="frmProduct" class="form-horizontal" novalidate="">
                        
                    <div class="form-group error">
                            <label for="inputTask" class="col-sm-3 control-label">Acronym</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control has-error" id="acronym" name="acronym" placeholder="Acronym" value="" required>
                                <small class="error-label" for="caption">(optional) Leave empty to use the full name as the term. </small>
                           </div>
                        </div>
                        <div class="form-group error">
                            <label for="inputTask" class="col-sm-3 control-label">Name</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control has-error" id="name" name="name" placeholder="Name" value="" required>
                            </div>
                        </div>
                        
                        <input type="hidden" id="product_id" name="product_id" value="0">
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
                        	<label for="inputTask" class="col-sm-12 control-label">Are you sure you want to delete this User?
                          	</label>

                        </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" id="btn-delete-confirm" value="Yes">Confirm Delete</button>
                    <button type="button" class="btn btn-primary" id="btn-delete-cancel" value="No">Cancel</button>
                    <input name="_method" id="_method" type="hidden" value="delete" />
                    <input type="hidden" id="delete-product" value="0">
                </div>
            </div>
        </div>
    </div>
    <!--
		End of Confirm Delete
   	-->
      <script src="js/products-crud.js"></script>
      <script>
          var table = null;
          $(function(){
            $('#products_table').dataTable({
                "order": [[ 0, "asc" ]],
                "columnDefs": 
                [ {
                "targets": [1,2],
                "orderable": true
                } ]
            });

            table = $("#products_table").DataTable();
            var counter = 1;            
        });
      </script>
</body>

</html>

<?php

}
?>