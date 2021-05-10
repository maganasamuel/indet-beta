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

                let url = "libs/api/magazine_api";

                $('body').tooltip({
                    selector: '[rel=tooltip]'
                });


                let table = $("#me").DataTable({
                    "order": [
                        [2, "desc"]
                    ],
                    "columnDefs": [{
                        "targets": [4],
                        "orderable": false
                    }]
                });

                $(".delete-btn").on("click", function(){
                   
                    let id = $(this).data("id");

                    $.confirm({
                        title: 'Confirm Action',
                        content: 'Are you sure that you want to delete this record?',
                        type: 'red',
                        typeAnimated: true,
                        buttons: {
                            confirm: {
                                text: 'Confirm',
                                btnClass: 'btn-red',
                                action: function(){    
                                    var data = {
                                        action: "delete_record",
                                        id: id,
                                    };
                                    console.log(id);

                                    $.ajax({
                                        data: data,
                                        type: "post",
                                        url: url,
                                        success: function (data) {
                                            //console.log(data);
                                            $("#record_" + id).remove();
                                            table.row("#record_" + id).remove().draw(false);
                                        },
                                        error: function (data) {
                                            console.log(data);
                                            console.log('Error:', data);
                                        }
                                    });

                                }
                            },
                            cancel: function () {
                            }
                        }
                    });

                });
            });
        </script>
        <!--nav bar end-->

        <div align="center">

            <div class="jumbotron">
                <h2 class="slide">Record Breakers</h2>
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
                                <th class='text-center'>Record Title</th>
                                <th class='text-center'>Record Holder</th>
                                <th class='text-center'>Magazine Date</th>
                                <th class='text-center'>Record</th>
                                <th class='text-center'></th>
                                <!--td></td-->

                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                $magazines = $magazineController->getAllRecords();
                                while ($rows = $magazines->fetch_assoc()) :
                                    extract($rows);
                                    $actual_record = $record;

                                    if($record_type == "Currency")
                                        $record = "$" .  number_format($record, 2);

                                    $record .= " " . $record_label;
                                    
                                    echo "
                                        <tr id='record_$record_id'>
                                            <td>$type</td>
                                            <td>$name</td>
                                            <td data-order='$magazine_date'>$date</td>
                                            <td data-order='$actual_record'>$record</td>
                                        ";
                                    ?>
                                    <td>
                                        <button class="delete-btn btn btn-danger" data-toggle="tooltip" title="Delete Record" data-id="<?php echo $record_id ?>">
                                            <i class="fas fa fa-trash"></i>
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