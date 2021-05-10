<?php

session_start();

//Restrict access to admin only
include "partials/admin_only.php";
include_once("libs/api/classes/general.class.php");
include_once("libs/api/controllers/Adviser.controller.php");
include_once("libs/api/controllers/Client.controller.php");

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
                $('#client_id')
                    .select2({
                        placeholder: 'Select a client/s'
                    });

                $('#adviser_id')
                    .select2({
                        placeholder: 'Select an adviser'
                    });

                $('[data-toggle="tooltip"]').tooltip();

                $("#adviser_id").on("change", function() {
                    let adviser_id = $(this).val();

                    $.ajax({
                        data: {
                            adviser_id: adviser_id,
                            action: "get_adviser"
                        },
                        type: "post",
                        url: "libs/api/adviser_api.php",
                        success: function(data) {
                            data = JSON.parse(data);
                            console.log(data);
                            $('#name').val(data.name);
                            $("#email").val(data.email);
                        },
                        error: function(data) {
                            console.log("Error", data);
                        }
                    });
                });

                function startLoading(button_id, spinner_id, icon_id = "") {
                    $("#" + button_id).prop("disabled", true);
                    if (icon_id != "") {
                        $("#" + icon_id).hide();
                    }
                    $("#" + spinner_id).show();
                }

                function endLoading(button_id, spinner_id, icon_id = "") {
                    $("#" + button_id).prop("disabled", false);
                    if (icon_id != "") {
                        $("#" + icon_id).show();
                    }
                    $("#" + spinner_id).hide();
                }
                
                $('#create').prop('disabled', false);

                $('#send_client_data').on('click', function(e) {
                    e.preventDefault();
                    let client_id = $("#client_id").val();
                    let name = $("#name").val();
                    let email = $("#email").val();
                    startLoading("send_client_data", "send_spinner");
                    $.ajax({
                        data: {
                            client_ids: client_id,
                            name: name,
                            email: email,
                            action: "create_send_client_data_entry"
                        },
                        type: "post",
                        url: "libs/api/client_api.php",
                        success: function(data) {
                            $.ajax({
                                type: "get",
                                url: "send_client_data?id=" + data,
                                success: function(data) {
                                    endLoading("send_client_data", "send_spinner");
                                    console.log(data);
                                    alert("Email Sent");
                                    window.location.reload();
                                },
                                error: function(data) {
                                    console.log("Error", data);
                                }
                            });
                        },
                        error: function(data) {
                            console.log("Error", data);
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
                <h2 class="slide">Send Client Data</h2>
            </div>
            <!--label end-->

            <?php
                require "database.php";
                $adviserController = new AdviserController();
                $clientController = new ClientController();
                $generalController = new General();
                ?>

            <form method="POST" action="create_customized_invoice.php" id="form" autocomplete="off" class="margined">
                <table align="center" bgcolor="ededed" cellpadding="5px">
                    <div class='row'>
                        <div class='col-sm-2 text-right'>Clients</div>
                        <div class='col-sm-10'>
                            <label>
                                <div class="input-group" style="width:60vw;">
                                    <select name="client_id" class="form-control" id="client_id" multiple required />
                                    <option disabled hidden value="">Select Client</option>
                                    <?php
                                        $clients = $clientController->getAllClients();
                                        while ($data = $clients->fetch_assoc()) {
                                            echo "<option value='" . $data['id'] . "'>" . $data["name"] . " | " . $data["appt_time"] . "</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class='row'>
                        <div class="col-sm-2 text-right">
                            Adviser
                        </div>
                        <div class="col-sm-10">
                            <label>
                                <div class="input-group" style="width:60vw;">
                                    <select name="adviser_id" class="form-control" id="adviser_id" required style="display:none;" />
                                    <option selected disabled hidden value="">Select Adviser</option>
                                    <?php
                                        $advisers = $adviserController->getAllAdvisers();
                                        while ($data = $advisers->fetch_assoc()) {
                                            echo "<option value='" . $data['id'] . "'>" . $data["name"] . "</option>";
                                        }
                                        ?>
                                    </select>
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
                            Email
                        </div>
                        <div class="col-sm-10">
                            <label>
                                <input type='text' class="form-control" name='email' id='email' value='' style="width:60vw;">
                            </label>
                        </div>
                    </div>
        </div>

        <div class="row">
            <div class="col-sm-2 center">
                <button type="button" id='send_client_data' style='margin-top: 30px;width: 100%;' class="btn btn-danger center"><i id="send_spinner" class="fas fa-spinner fa-spin" style="display:none;"></i> Send Client Data</button>
            </div>
        </div>
        </form>

    </body>

    </html>

<?php

}

?>