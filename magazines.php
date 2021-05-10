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
        <?php include "partials/nav_bar.html"; ?>
        <!--nav bar end-->
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">
        <link rel="stylesheet" href="styles.css">
        <link id="favicon" rel="icon" href="Logo_ImageOnly.png" type="image/png" sizes="16x16">
        <title>INDET</title>
        <script src="js/loading.js"></script>
        <script>
            $(function() {

                $('body').tooltip({
                    selector: '[rel=tooltip]'
                });


                $('#me').dataTable({
                    "order": [
                        [1, "desc"]
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
                        action: 'delete_magazine',
                        id: id
                    };

                    $.confirm({
                        title: 'Confirm',
                        content: 'Are you sure you want to delete this?',
                        buttons: {
                            confirm: function() {
                                $.ajax({
                                    url: 'libs/api/common_api.php',
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
                
                $(".email_magazine").on("click", function() {
                    var id = $(this).data("id");
                    var emailurl = "send_magazine.php?id=" + id;
                    $.confirm({
                        title: 'Confirm Sending Magazine',
                        content: "Are you sure that you want to send this Magazine to the whole team?",
                        buttons: {
                            confirm: function() {
                                $.ajax({
                                    type: "get",
                                    url: emailurl,
                                    beforeSend: function (){
                                        startLoading("send_magazine_button_" + id, "send_magazine_spinner_" + id,"send_magazine_icon_" + id);
                                    },
                                    success: function(data) {
                                        endLoading("send_magazine_button_" + id, "send_magazine_spinner_" + id,"send_magazine_icon_" + id);

                                        console.log("Feedback: ", data);
                                        alert("Magazine sent to whole team.");
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

            });
        </script>
        <!--nav bar end-->

        <div align="center">

            <div class="jumbotron">
                <h2 class="slide">Magazines</h2>
            </div>
            <!--label end-->


            <!--modal-->


            <!--modal end-->
            <!--search-->
            <div>



                <!--search end-->
                <?php
                    include_once("libs/api/controllers/Magazine.controller.php");
                    $magazineController = new MagazineController();
                    ?>
                <div class="margined table-responsive">
                    <table id='me' data-toggle="table" id='example' class="table table-striped " cellpadding="5px" cellspacing="5px" width='95%'>
                        <thead>
                            <tr>
                                <th class='text-center'>Series</th>
                                <th class='text-center'>Date Created</th>
                                <th class='text-center'>Created By</th>
                                <th class='text-center'></th>
                                <th class='text-center'></th>
                                <th class='text-center'></th>
                                <!--td></td-->

                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                $magazines = $magazineController->getAllMagazines();
                                while ($rows = $magazines->fetch_assoc()) :
                                    extract($rows);
                                    $date_created_order = $date_created;
                                    $date_created = date("d/m/Y", strtotime($date_created));
                                    

                                    echo "
                                        <tr id='be_$id'>
                                            <td>$series</td>
                                            <td data-order=" . $date_created_order . ">" . $date_created . "</td>
                                            <td>$full_name</td>
                                        ";
                                    ?>
                                <td><a class="a_single_view btn btn-primary" target="_blank" data-toggle="tooltip" title="View Magazine" href="generate_magazine<?php echo "?view_id=$id" ?>"><span class="glyphicon glyphicon-search" style="font-size:15px;"></span>
                                    </a></td>

                                <td><input type='image' class='delete' src='delete.png' data-toggle="tooltip" title="Delete Magazine" data-id="<?php echo $id ?>"></td>
                                <td>
                                    <button id="send_magazine_button_<?php echo $id ?>" class="btn btn-primary email_magazine" data-toggle="tooltip" title="Send Magazine" data-id="<?php echo "$id" ?>">
                                        <i id="send_magazine_icon_<?php echo $id ?>" class="fas fa-envelope"></i>
                                        <i id="send_magazine_spinner_<?php echo $id ?>" class="fas fa-spin fa-spinner" style="display:none;"></i>
                                    </button>
                                </td>
                            <?php
                                    echo "</tr>";
                                endwhile;
                                ?>
                        </tbody>
                    </table>
                </div>

            </div>

    </html>

<?php

}
?>