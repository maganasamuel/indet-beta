 <?php
session_start();
if (!isset($_SESSION["myusername"])) {
    session_destroy();
    header("Refresh:0; url=index.php");
} else {
    ?>
 	<html>

 	<head>
 		<!--nav bar-->
 		<?php include "partials/nav_bar.html";?>
 		<!--nav bar end-->
 		<meta name="viewport" content="width=device-width, initial-scale=1">
 		<link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">
 		<link rel="stylesheet" href="styles.css">
 		<link id="favicon" rel="icon" href="Logo_ImageOnly.png" type="image/png" sizes="16x16">
 		<title>INDET</title>
		 <style>
			/* The switch - the box around the slider */
			.switch {
			position: relative;
			display: inline-block;
			width: 60px;
			height: 34px;
			}

			/* Hide default HTML checkbox */
			.switch input {
			opacity: 0;
			width: 0;
			height: 0;
			}

			/* The slider */
			.slider {
			position: absolute;
			cursor: pointer;
			top: 0;
			left: 0;
			right: 0;
			bottom: 0;
			background-color: #ccc;
			-webkit-transition: .4s;
			transition: .4s;
			}

			.slider:before {
			position: absolute;
			content: "";
			height: 26px;
			width: 26px;
			left: 4px;
			bottom: 4px;
			background-color: white;
			-webkit-transition: .4s;
			transition: .4s;
			}

			input:checked + .slider {
			background-color: #2196F3;
			}

			input:focus + .slider {
			box-shadow: 0 0 1px #2196F3;
			}

			input:checked + .slider:before {
			-webkit-transform: translateX(26px);
			-ms-transform: translateX(26px);
			transform: translateX(26px);
			}

			/* Rounded sliders */
			.slider.round {
			border-radius: 34px;
			}

			.slider.round:before {
			border-radius: 50%;
			}
		 </style>
 	</head>
 	<!--header-->

 	<body>
 		<div align="center">


 			<!--header end-->

 			<!--nav bar-->


 			<!--nav bar end-->


 			<!--label-->

 			<div class="jumbotron">
 				<h2 class="slide">KiwiSaver Deals</h2>
 			</div>
 			<!--label end-->

 			<!--modal-->



 			<?php

    require "database.php";
    include_once "libs/api/classes/general.class.php";
    include_once "libs/api/controllers/Deal.controller.php";
    include_once "libs/api/controllers/Client.controller.php";

    function convertNum($x)
    {

        return number_format($x, 2, '.', ',');
    }

    $dealController = new DealController();
    $clientController = new ClientController();
    $generalController = new General();
    if (!$con) {
        echo "<div>";
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
        echo "</div>";
    }

    $kiwisavers = $dealController->GetAllKiwiSaverDeals();
    ?>

 			<div class="margined table-responsive">

 				<div class="row">
 					<table id='issued_clients_table' data-toggle="table" class="table table-striped " cellpadding="5px" cellspacing="5px" width='95%'>

 						<thead>
                            <td>Name</td>
 							<td>Source Client</td>
 							<td>Adviser</td>
 							<td>Lead Generator</td>
 							<td>Insurer</td>
 							<td>Commission</td>
 							<td>GST</td>
 							<td>Total Payment</td>
 							<td>Date Applied</td>
 							<td></td>
 							<td></td>
 						</thead>
 						<tbody>
 							<?php

    foreach ($kiwisavers as $rows) {
        if ($rows["name"] == null) {
            continue;
        }

        extract($rows);

        $issue_date_order = date('Ymd', strtotime($issue_date));

        $issue_date = date('d/m/Y', strtotime($issue_date));

        echo "
                                            <tr id='kiwisaver_$id' cellpadding='5px' cellspacing='5px'>

                                                <td>$name</td>
                                                <td>$source_client</td>
                                                <td>$adviser_name</td>
                                                <td>$leadgen_name</td>
                                                <td>KiwiSaver</td>
                                                <td data-order='$commission'>$" . number_format((float)$commission, 2) . "</td>
                                                <td data-order='$gst'>$" . number_format((float)$gst, 2) . "</td>
                                                <td data-order='$balance'>$" . number_format((float)$balance, 2) . "</td>
                                                <td data-order='$issue_date_order'>$issue_date</td>
                                            ";
        /*<td data-order=".$entrydate.">".$convertdate."</td>
        <td data-order=".$startingdate.">".$convertstartingdate."</td>
        href="view_kiwisaver_profile<?php echo "?id=$id" ?>"
         */

        ?>



 								<td><a class="btn-edit" id='btn-edit-<?php echo "$id" ?>' data-toggle="tooltip" title="Edit KiwiSaver Deal" data-id='<?php echo "$id" ?>'><span class="btn btn-warning glyphicon glyphicon-pencil"></span></i></a></td>
 								<td><a class="btn-delete" data-toggle="tooltip" title="Delete KiwiSaver Deal" data-id="<?php echo $id ?>"><span class="btn btn-danger glyphicon glyphicon-trash"></span></td>

 							<?php
echo "</tr>";
    }

    ?>
 						</tbody>
 					</table>
 				</div>
 			</div>
 		</div>

 		<script>
 			var table = null;
 			$(function() {
 				$('body').on('focus', ".datepicker", function() {
 					$(this).datepicker({
 						dateFormat: 'dd/mm/yy'
 					});
 				});

 				$('#issued_clients_table').dataTable();
 				table = $("#issued_clients_table").DataTable();

 			});
 		</script>


 		<style>
 			.datepicker {
 				z-index: 9999 !important
 			}

 			.thumbnail {
 				box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.5);
 				transition: 0.3s;
 				min-width: 40%;
 				border-radius: 5px;
 			}

 			.thumbnail-description {
 				min-height: 40px;
 			}

 			.thumbnail:hover {
 				box-shadow: 0 8px 16px 0 rgba(0, 0, 0, 1);
 			}

 			.datepicker_dynamic {
 				z-index: 2000 !important;
 			}

 			.full-screen {
 				width: 90%;
 				height: 90%;
 				margin: 0;
 				top: 5%;
 				left: 5%;
 			}

 			.nav-tabs>li {
 				float: none;
 				display: inline-block;
 				zoom: 1;
 			}

 			.nav-tabs {
 				text-align: center;
 			}

 			.nav-tabs li a {
 				color: #337ab7 !important;
 				border-bottom: 1px solid #ddd;
 			}

 			.nav-tabs li.active a {
 				color: black !important;
 				border-bottom: none;
 			}
 		</style>

 		<div class=" modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
 			<div id="myModalDialog" class="modal-dialog modal-lg full-screen">
 				<div class="modal-content">
                    <div class="modal-header" style="background-color: #286090; ">
                        <button type="button" class="close" id="close_client_modal" data-dismiss="modal" aria-label="Close"><span style="color:white;" aria-hidden="true">×</span></button>
                        <h4 class="modal-title" id="myModalLabel" style="color:white;">Deal Editor</h4>
                    </div>
                    <form id="frmDeals">
                        <div class="modal-body">
                            <div class='row' style="padding-top: 30px;">
                                <div class='col-sm-4'></div>
                                <div class='col-sm-4 text-center'>
                                    <h2>Deal Information</h2>
                                </div>
                            </div>
                            <div class='row'>
                                <div class="row" id="deal">
                                    <div class="row">
                                        <div class="row">
                                            <div class="col-sm-1">
                                                <label>Insurer</label>
                                                <div class="input-group">
                                                <input class="form-control" autocomplete="off" readonly value="KiwiSaver" type="text" name="insurer" id="insurer" step="any" />
                                                </div>
                                            </div>
                                            <div class="col-sm-2">
                                                <label>Name</label>
                                                <div class="input-group">
                                                <input class="form-control" autocomplete="off" type="text" name="name" id="name" step="any" />
                                                </div>
                                            </div>
                                            <div class="col-sm-2">
                                                <label>Commission</label>
                                                <div class="input-group">
                                                <input class="form-control" autocomplete="off" type="text" name="commission" id="commission" step="any" />
                                                </div>
                                            </div>
                                            <div class="col-sm-2">
                                                <label>GST</label>
                                                <div class="input-group">
                                                <input class="form-control" autocomplete="off" type="text" name="gst" id="gst" step="any" />
                                                </div>
                                            </div>
                                            <div class="col-sm-2">
                                                <label>Total Payment</label>
                                                <div class="input-group">
                                                <input class="form-control" autocomplete="off" type="text" name="balance" id="balance" step="any" />
                                                </div>
                                            </div>
                                            <div class="col-sm-2">
                                                <label>Date Applied</label>
                                                <div class="input-group">
                                                <input class="form-control datepicker" autocomplete="off" type="text" name="issue_date" id="issue_date" step="any" />
                                                </div>
                                            </div>
                                            <div class="col-sm-1">
                                                <label>Count</label>
                                                <div class="input-group">
                                                <label class="switch">
                                                    <input type="checkbox" data-index="" class="count_switch" name="count_switch" id="count_switch" checked>
                                                    <span class="slider round"></span>
                                                </label>
                                                <input type="hidden" name="count" id="count" value="Yes">
                                                <input type="hidden" name="deal_id" id="deal_id" value="">
                                                <input type="hidden" name="action" id="action" value="update_kiwisaver_deal">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>


                        </div>
                        <div class="modal-footer">
                            <div id="buttons_div">
                                <button type="button" class="btn btn-primary" id="btn-save-deal_data" data-action="add"><i id="save_deal_spinner" class="fas fa-spinner fa-spin" style="display:none;"></i>Save Changes</button>
                            </div>
                        </div>
                    </form>
 				</div>
 			</div>
 		</div>
 		<!--
	End of Editor
	-->

 		<script>
 			var current_user_id = <?php echo $_SESSION["myuserid"]; ?>;
 		</script>
 		<script src="js/kiwisaver_deals-crud.js"></script>

 		<?php
//Issue client if it came from submissions page
    if (isset($_GET["edit"])) {

        ?>
 			<script>
 				<?php
echo "let edit_id = " . $_GET["edit"] . ";
				 let name = '" . $_GET["name"] . "'";
        ?>

 				$(function() {
 					let tbl = $('#issued_clients_table').DataTable();
 					tbl.search(name).draw();

 					setTimeout(function() {
 						$("#btn-edit-" + edit_id).trigger("click");
 						setTimeout(function() {
 							$("#data_tabs").show();
 							$("#nav_tabs").show();
 							$("#buttons_div").show();
 						}, 400);
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