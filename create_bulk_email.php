<?php

session_start();

//Restrict access to admin only
include "partials/admin_only.php";
include_once("libs/api/classes/general.class.php");
include_once("libs/api/controllers/Adviser.controller.php");

$_SESSION["x"] = 1;
unset($_SESSION['adviser_id']);
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
        <style>
            div#myModal {
                padding-top: 0px !important;
            }

            .modal-dialog {
                min-width: 100%;
                min-height: 100%;
                height: auto;
                width: auto;
                margin: 0;
                padding: 0;
            }

            .modal-content {
                width: 100% !important;
                height: auto;
                min-height: 100%;
                border-radius: 0;
            }
        </style>
        <script>
            let adviser_api = "libs/api/bulkEmail_api.php";

            $(document).ready(function() {
                $('[data-toggle="tooltip"]').tooltip();
                $("#receipients_type").on("change", function() {
                    let type = $(this).val();
                    switch (type) {
                        default:
                        case "All Issued Clients":
                        case "All Submission Clients":
                        case "All Cancelled Clients":
                            $("#adviser_id").slideUp();
                            $("#emails").slideUp();
                            break;
                        case "Adviser Issued Clients":
                        case "Adviser Submission Clients":
                        case "Adviser Cancelled Clients":
                        case "Specify Adviser":
                            $("#adviser_id").slideDown();
                            $("#emails").slideUp();
                            break;
                        case "Specify":
                            $("#adviser_id").slideUp();
                            $("#emails").slideDown();
                            break;
                    }
                });

                $('#create').prop('disabled', false);

                $('#create').on('click', function(e) {
                    e.preventDefault();

                    var form = $("#form").serialize();

                    $.ajax({
                        dataType: 'json',
                        type: 'POST',
                        data: form,
                        url: "bulk_email_preview.php",
                        success: function(e) {
                            console.log(e);
                            var mydata = e;
                                mydata.action = "create_bulk_email";
                                mydata = mydata;

                            var link = e['link'];
                            var htm = '<iframe src="' + link + '" style="width: 100%;height: 75%;"></iframe>';
                            $('#myModal').modal('show');
                            $('.modal-body').html(htm);
                            $('#save_pdf').unbind("click");
                            $('#save_pdf').on('click', function() {
                                $.ajax({
                                    data: mydata,
                                    type: 'POST',
                                    url: adviser_api,
                                    success: function(x) {
                                        console.log(x);
                                        $.confirm({
                                            title: 'Success!',
                                            content: 'You have successfully created a bulk email.',
                                            buttons: {
                                                Ok: function() {
                                                    console.log(x);
                                                    window.location = 'create_bulk_email.php';
                                                },
                                            }
                                        });
                                    }
                                });
                            });
                        },
                        error: function(x) {
                            x = JSON.stringify(x);
                            console.log("Data:" + x);
                        }
                    });

                });

            });
        </script>
    </head>
    <body>
        
        <!--header-->
        <div align="center">
            <div class="jumbotron">
                <h2 class="slide">Create Bulk Email</h2>
            </div>
            <!--label end-->

            <?php
                require "database.php";
                $adviserController = new AdviserController();
                $generalController = new General();
                ?>

            <form method="POST" action="create_customized_invoice.php" id="form" autocomplete="off" class="margined">
                <table align="center" bgcolor="ededed" cellpadding="5px">
                    <div class='row'>
                        <div class='col-sm-2 text-right'>Receipient</div>
                        <div class='col-sm-10'>
                            <label>
                                <div class="input-group" style="width:60vw;">
                                    <select name="receipients_type" class="form-control" id="receipients_type" required />
                                        <option>All Issued Clients</option>
                                        <option>All Submission Clients</option>
                                        <option>All Cancelled Clients</option>
                                        <option>Adviser Issued Clients</option>                                        
                                        <option>Adviser Submission Clients</option>
                                        <option>Adviser Cancelled Clients</option>
                                        <option>Active Advisers</option>
                                        <option>Specify Adviser</option>
                                        <option>Specify</option>
                                    </select>
                                    <select name="adviser_id" class="form-control" id="adviser_id" required style="display:none;" />
                                        <option selected disabled hidden value="">Select Adviser</option>
                                        <?php
                                            $advisers = $adviserController->getAllAdvisers();
                                            while ($data = $advisers->fetch_assoc()) {
                                                echo "<option value='" . $data['id'] . "'>" . $data["name"] . "</option>";
                                            }
                                        ?>
                                    </select>
                                    <textarea class="form-control" name='emails' id='emails' placeholder="Specify email(s) here" style="display:none;"></textarea>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class='row'>
                        <div class="col-sm-2 text-right">
                            Name
                        </div>
                        <div class="col-sm-10">
                            <label>
                                <input type='text' class="form-control" name='name' id='name' value='' style="width:60vw;">
                            </label>
                        </div>
                    </div>

                    <div class='row'>
                        <div class="col-sm-2 text-right">
                            Subject
                        </div>
                        <div class="col-sm-10">
                            <label>
                                <input type='text' class="form-control" name='subject' id='subject' value='' style="width:60vw;">
                            </label>
                        </div>
                    </div>

                    <div class='row'>
                        <div class='col-sm-2 text-right'>Body <i data-toggle="tooltip" data-placement="top" title="You can enter '{indent}' to add an indentations to the first line of your paragraph." class="fas fa-question-circle"></i>
                        </div>
                        <div class='col-sm-10'>
                            <textarea class="form-control" name='body' id='body' style="width:60vw; height:20vh">To whom it may concern,

    {indent}Message</textarea>
                        </div>
                    </div>
        </div>

        <div class="row">
            <div class="col-sm-2 center">
                <input name="enter" type="submit" id='create' value="Create Email" style='margin-top: 30px;width: 100%;' class="btn btn-danger center" />
            </div>
        </div>
        </form>

        <div class="container">
            <!-- Modal -->
            <div class="modal fade" id="myModal" role="dialog" style="z-index:10000;width: 100%;">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                            <h2 class="modal-title" style="float: left;">Invoice Preview</h2>
                        </div>
                        <div class="modal-body">

                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-info" id='save_pdf'>Save</button>
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>

<?php

}

?>