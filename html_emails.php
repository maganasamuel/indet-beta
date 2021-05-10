<?php
session_start();

//Restrict access to admin only
include "partials/admin_only.php";

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
        <script>
            $(function() {

                $('body').tooltip({
                    selector: '[rel=tooltip]'
                });


                $('#me').dataTable({
                    "order": [
                        [2, "desc"]
                    ],
                    "columnDefs": [{
                        "targets": [3, 4],
                        "orderable": true
                    }]
                });

                $('.note').on('change', function(e) {
                    e.preventDefault();
                    var note = $(this).val();
                    var me = $(this);
                    var prev = $(this).data('prev');
                    var id = $(this).data('id');
                    $.confirm({
                        title: 'Confirm',
                        content: 'Are you sure you want to save this?',
                        buttons: {
                            confirm: function() {
                                $.ajax({
                                    url: 'save_note.php',
                                    data: {
                                        id: id,
                                        note: note
                                    },
                                    type: 'POST',
                                    success: function(e) {
                                        $.confirm(e);
                                    }
                                });
                            },
                            cancel: function() {
                                me.val(prev);
                            }
                        }
                    });
                });

                $(".delete").on("click", function() {
                    var id = $(this).data("id");
                    data = {
                        action: 'delete_html_email',
                        id: id
                    };

                    $.confirm({
                        title: 'Confirm',
                        content: 'Are you sure you want to delete this?',
                        buttons: {
                            confirm: function() {
                                $.ajax({
                                    url: 'libs/api/htmlEmail_api.php',
                                    data: data,
                                    type: 'POST',
                                    success: function(e) {
                                        console.log(e);
                                        $("#be_" + id).remove();
                                    },
                                    error: function(e) {
                                        console.log(e);
                                    }
                                });
                            },
                            cancel: function() {
                                me.val(prev);
                            }
                        }
                    });
                });

                $(".email_html_email").on("click", function() {
                    var id = $(this).data("id");
                    var emailurl = "libs/api/htmlEmail_api";
                    let form = {
                        action : "send_html_email",
                        id: id
                    }
                    $(this).prop('disabled', true);
                    $.confirm({
                        title: 'Confirm Sending HTML Email',
                        content: "Are you sure that you want to send this html email?",
                        buttons: {
                            confirm: function() {
                                $.ajax({
                                    type: "post",
                                    data: form,
                                    url: emailurl,
                                    success: function(data) {
                                        console.log("Feedback: ", data);
                                        //alert("Invoice " + invoice_num + " Sent");
                                        $(this).prop('disabled', false);
                                    },
                                    error: function(data) {
                                        console.log("Error Sending Mail", data);
                                        //alert("Invoice " + invoice_num + " Sent");
                                        //alert("An error occurred, please contact the IT Support.");
                                        $(this).prop('disabled', false);
                                    }
                                });
                            },
                            cancel: function() {

                            },

                        }
                    });
                });

                $("#confirm_email").on("click", function() {
                    var data = $("#email_info").serializeArray();
                    var url = "";
                    $("#sending_spinner").show();
                    var btn = $(this);
                    var cancel_btn = $("#cancel_email");
                    btn.prop("disabled", true);
                    cancel_btn.prop("disabled", true);

                    $.ajax({
                        data: data,
                        type: "post",
                        url: 'email_html_email.php',
                        success: function(data) {
                            console.log(data);
                            btn.prop("disabled", false);
                            cancel_btn.prop("disabled", false);
                            $("#email_info").trigger("reset");
                            $('#emailModal').modal('hide');
                        },
                        error: function(data) {
                            btn.prop("disabled", false);
                            cancel_btn.prop("disabled", false);
                            console.log(data);
                            console.log('Error:', data);
                        }
                    });
                });

                $("#cancel_email").on("click", function() {
                    $('#emailModal').modal('hide');
                    $("#email_info").trigger("reset");
                });
            });
        </script>
        <!--nav bar end-->

        <div align="center">

            <div class="jumbotron">
                <h2 class="slide">HTML Emails</h2>
            </div>
            <!--label end-->


            <!--modal-->


            <!--modal end-->
            <!--search-->
            <div>



                <!--search end-->
                <?php
include_once "libs/api/controllers/HTMLEmail.controller.php";
    include_once "libs/api/controllers/Adviser.controller.php";
    $htmlEmailController = new HTMLEmailController();
    $adviserController = new AdviserController();
    ?>
                <div class="margined table-responsive">
                    <table id='me' data-toggle="table" class="table table-striped " cellpadding="5px" cellspacing="5px" width='95%'>
                        <thead>
                            <tr>
                                <th class='text-center'>Name</th>
                                <th class='text-center'>Subject</th>
                                <th class='text-center'>Date Created</th>
                                <th class='text-center'>Receipients Type</th>
                                <th class='text-center'>Adviser (if any)</th>
                                <th class='text-center'>Sent By</th>
                                <th></th>
                                <th class='text-center'></th>
                                <th class='text-center'></th>
                                <!--td></td-->

                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                $htmlEmails = $htmlEmailController->getAllSavedHTMLEmails();
                                    while ($rows = $htmlEmails->fetch_assoc()):
                                        extract($rows);
                                        $adviser = "N/A";
                                        $receipients = json_decode($receipients);
                                        $convertdate = date("d/m/Y", strtotime($date_created));
                                        if (strpos($receipients->type, 'Adviser') !== false) {
                                            $adviser = $adviserController->getAdviser($receipients->adviser);
                                            $adviser = $adviser["name"];
                                        }

                                        if($receipient_name == "")
                                            $receipient_name = "N/A";
                                        
                                        echo "
                                        <tr id='be_$id'>
                                            <td>$receipient_name</td>
                                            <td>$subject</td>
                                            <td data-order=" . $date_created . ">" . $convertdate . "</td>
                                            <td>$receipients->type</td>
                                            <td>$adviser</td>
                                            <td>$name</td>
                                            ";
                                        ?>
	                                <td><a class="a_single_view btn btn-primary" target="_blank" data-toggle="tooltip" title="View HTML Email" href="mail_viewer<?php echo "?tok=$uniq" ?>"><span class="glyphicon glyphicon-search" style="font-size:15px;"></span>
	                                    </a></td>

	                                <td><input type='image' class='delete' src='delete.png' data-toggle="tooltip" title="Delete HTML Email" data-id="<?php echo $id ?>"></td>
	                                <td><a class="email_html_email" href="#" data-toggle="tooltip" title="Send HTML Email" data-id="<?php echo "$id" ?>"><img src="email.png"></a>
	                                </td>
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
                <div class="modal fade" id="emailModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                                <h4 class="modal-title" id="myModalLabel">Email Invoice</h4>
                            </div>
                            <form id="email_info" name="email_info" class="form-horizontal" novalidate="">
                                <div class="modal-body">
                                    <div class="form-group error">
                                        <label for="inputTask" class="col-sm-2 control-label">Email(s)</label>
                                        <div class="col-sm-10">
                                            <textarea class="form-control has-error" id="emails" name="emails" placeholder="Separate multiple emails by adding a comma in between." value="" required></textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-danger" id="confirm_email" value="Yes"> <i id="sending_spinner" class="fas fa-spinner fa-spin" style="display:none;"></i> Send</button>
                                    <button type="button" class="btn btn-primary" id="cancel_email" value="No">Cancel</button>
                                    <input name="action" id="action" type="hidden" value="send_html_email" />
                                    <input type="hidden" id="id" name="id" value="0">
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <!--
		End of Confirm Delete
   	-->
            </div>

    </html>

<?php

}
?>