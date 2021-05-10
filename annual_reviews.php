<?php
include_once("libs/api/classes/general.class.php");
include_once("libs/api/classes/dateHelper.class.php");
include_once("libs/api/controllers/AnnualReview.controller.php");

$app = new General();
$dateHelper = new DateHelper();
$reviewController = new AnnualReviewController();

$dataset = $reviewController->getAllReviews();

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
        <link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">
        <link rel="stylesheet" href="styles.css">
        <link id="favicon" rel="icon" href="Logo_ImageOnly.png" type="image/png" sizes="16x16">
        <title>INDET</title>
    </head>

    <body>
        <div align="center">
            <div class="jumbotron">
                <h2 class="slide">Annual Reviews</h2>
            </div>
            <div class="margined table-responsive">
                <div class="row">
                    <div class="col-sm-9 text-center"></div>
                    <div class="col-sm-3 text-center"><button id="btn-add" name="btn-add" data-toggle="modal" data-target="#myModal" class="pull-right btn btn-primary btn-m"><i class="fa fa-plus"></i> Add New Client</button></div>
                </div>
                <br>
                <table id='clients_table' data-toggle="table" class="table table-striped " cellpadding="5px" cellspacing="5px" width='95%' style=" display: block;">
                    <thead>
                        <tr>
                            <td></td>
                            <td></td>
                            <td>Client Name</td>
                            <td>Date Issued</td>
                            <td>Notes</td>
                        </tr>
                    </thead>

                    <tbody id="clients-list"">
        <?php
            while ($rows =  $dataset->fetch_assoc()) :
                extract($rows);

                $date_issued_sort = $date_issued;
                $date_issued = $dateHelper->DateToNZFormat($date_issued);

                $data = json_decode($data);

                $notes = $data->extra_information;

                echo "
            <tr id='client$id' cellpadding='5px' cellspacing='5px'>
                <td><a style='font-size:30px;' target='_blank'  data-toggle='tooltip' title='View Annual Review'  href='view_annual_review?id=$review_id'><i class='fas fa-search'></i></a></td>
                <td><a style='font-size:30px;' href='view_issued_client_profile?id=$issued_client_id'  data-toggle='tooltip' title='View Issued Client Profile' ><i class='text-success fas fa-briefcase'></i></a></td>
                <td>$name</td>
				<td data-order=" . $date_issued_sort . ">$date_issued </td>
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
		Confirm Delete
	-->
	<div class=" modal fade" id="confirmModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                                    <h4 class="modal-title" id="myModalLabel">Confirm Deletion</h4>
                                </div>
                                <div class="modal-body">
                                    <form id="frmDelUser" name="frmDelUser" class="form-horizontal" novalidate="">
                                        <div class="form-group error">
                                            <label for="inputTask" class="col-sm-12 control-label">Are you sure you want to delete this Client?
                                            </label>

                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-danger" id="btn-delete-confirm" value="Yes">Confirm Delete</button>
                                            <button type="button" class="btn btn-primary" id="btn-delete-cancel" value="No">Cancel</button>
                                            <input name="_method" id="_method" type="hidden" value="delete" />
                                            <input type="hidden" id="delete-client" value="0">
                                        </div>
                                </div>
                            </div>
                        </div>
                        <!--
		End of Confirm Delete
   	-->
                        <script src="js/clients-crud.js"></script>
                        <script>
                            var table = null;
                            $(function() {


                                $('body').tooltip({
                                    selector: '[rel=tooltip]'
                                });


                                $('.datepicker').datepicker({
                                    dateFormat: 'dd/mm/yy'
                                });


                                $('#clients_table').dataTable({
                                    "order": [
                                        [2, "asc"]
                                    ],
                                    "columnDefs": [{
                                        "width": "1%",
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
    </body>

    </html>

<?php

}
?>