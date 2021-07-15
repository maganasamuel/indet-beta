<?php

session_start();

//Restrict access to admin only
include "partials/admin_only.php";

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
        let items = 0;
        $(document).ready(function() {
            $.fn.serializeObject = function() {
                var o = {};
                var a = this.serializeArray();
                $.each(a, function() {
                    if (o[this.name]) {
                        if (!o[this.name].push) {
                            o[this.name] = [o[this.name]];
                        }
                        o[this.name].push(this.value || '');
                    } else {
                        o[this.name] = this.value || '';
                    }
                });
                return o;
            };

            // AddItemRow();

            $(document).on("click", ".delete-item", function() {
                let item = $(this).data("item");
                $("#item_" + item).remove();
            });

            $("#add-item").on("click", function() {
            	if(items == 0)
            		items = parseInt($('#row_id').val());
                AddItemRow();
            });

            $('#update').prop('disabled', false);

            $('#update').on('click', function(e) {
                e.preventDefault();

                var invoice_id = $("#invoice_id").val();
                var invoice_number = $("#invoice_number").val();
                var form = $("#form").serializeObject();

                let form_items = [];
                if (Array.isArray(form.total_amount)) {
                    for (i = 0; i < form.total_amount.length; i++) {
                        let item = new Object();
                        item.name = form.item_name[i];
                        item.description = form.description[i];
                        item.total_amount = form.total_amount[i];
                        form_items.push(item);
                    }
                } else {
                    let item = new Object();
                    item.name = form.item_name;
                    item.description = form.description;
                    item.total_amount = form.total_amount;
                    form_items.push(item);
                }

                form.items = form_items;
                form.invoice_number = invoice_number;

                $.ajax({
                    dataType: 'json',
                    type: 'POST',
                    data: form,
                    url: "customized_invoice_preview.php",
                    success: function(e) {
                        console.log(e);
                        var mydata = JSON.stringify(e);
                        var link = e['link'];
                        var htm = '<iframe src="' + link + '" style="width: 100%;height: 75%;"></iframe>';
                        $('#myModal').modal('show');
                        $('.modal-body').html(htm);
                        $('#update_pdf').unbind("click");
                        $('#update_pdf').on('click', function() {
                            $.ajax({
                                //dataType:'JSON',
                                data: {
                                    mydata: mydata,
                                    invoice_id: invoice_id
                                },
                                type: 'POST',
                                url: "update_customized_invoice.php",
                                beforeSend: function() {

                                },
                                success: function(x) {
                                    console.log(x);
                                    $.alert({
			                      		title: 'Success!',
				                      	content: 'You have successfully updated an invoice ',
				                    });

				                    $('#myModal').modal('hide');
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



            function AddItemRow() {
                var item_row = AddItem(items);
                items++;

                $("#item_container").append(item_row);
            }


            function AddItem(item_index) {
                return '\
                    <div class="row" id="item_' + item_index + '">\
                        <div class="col-sm-1"></div>\
                        <div class="col-sm-1">\
                            <h4>Item #' + (item_index + 1) + '</h4>\
                        </div>\
        \
                        <div class="col-sm-3">\
                            <input name="item_name" type="text" id="item_name" class="form-control" placeholder="Item" />\
                        </div>\
                        <div class="col-sm-3">\
                            <input name="description" type="text" id="description" class="form-control" placeholder="Description" />\
                        </div>\
                        <div class="col-sm-3">\
                            <input name="total_amount" type="text" id="total_amount" class="form-control" placeholder="Total Amount" />\
                        </div>\
                        <div class="col-sm-1">\
                            <button type="button" class="btn btn-danger delete-item" data-item="' + item_index + '">X</button>\
                        </div>\
                    </div>';
            }
        });
    </script>

</head>

<body>

    <!--header-->
    <div align="center">



        <!--header end-->

        <!--nav bar-->

        <!--nav bar end-->

        <!--label-->
        <div class="jumbotron">
            <h2 class="slide">Update Customized Invoice</h2>
        </div>
        <!--label end-->

        <?php
            require "database.php";
        	if(isset($_GET["id"])){
				extract($_GET);
				$query = "SELECT * FROM customized_invoices WHERE id = $id";
				$invoice_query = mysqli_query($con, $query) or die('Could not look up user information; ' . mysqli_error($con));
				$rows = mysqli_fetch_array($invoice_query);

				$invoice_number = $rows['invoice_number'];
				$company_name = $rows['company_name'];
				$name = $rows['name'];
				$address = $rows['address'];
				$gst = $rows['gst'];
				$gst_type = $rows['gst_type'];
				$total_amount = $rows['total_amount'];
				$data = json_decode($rows['data']);

				$subtotal = $data->subtotal;
				$items = $data->items;
				$date_created = isset($rows['date_created']) ? date_format(date_create($rows['date_created']),'d/m/Y') : '';
		     }
        ?>

        <form method="POST" action="update_customized_invoice.php" id="form" autocomplete="off" class="margined">
            <table align="center" bgcolor="ededed" cellpadding="5px" onload="form1.reset();">

                <div class='row'>
                    <div class='col-sm-1'></div>
                    <div class='col-sm-2'>
                        <label>Company Name
                            <div class="input-group">
                                <span class="input-group-addon"><i class="glyphicon glyphicon-briefcase"></i></span>
                                <input type='text' class="form-control" name='company_name' id='company_name' value='<?= $company_name; ?>'>
                                <input type='hidden' class="form-control" name='user_id' id='user_id' value='<?php echo $_SESSION["myuserid"] ?>'>
                                <input type='hidden' class="form-control" name='invoice_id' id='invoice_id' value='<?php echo $id ?>'>
                                <input type='hidden' class="form-control" name='invoice_id' id='invoice_number' value='<?php echo $invoice_number ?>'>
                            </div>
                        </label>
                    </div>
                    <div class='col-sm-2'>
                        <label>Name
                            <div class="input-group">
                                <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
                                <input type='text' class="form-control" name='name' id='name' value='<?= $name; ?>'>
                            </div>
                        </label>
                    </div>
                    <div class='col-sm-2'>
                        <label>Address
                            <div class="input-group">
                                <span class="input-group-addon"><i class="glyphicon glyphicon-map-marker"></i></span>
                                <textarea class="form-control" name='address' id='address'><?= $address; ?></textarea>
                            </div>
                        </label>
                    </div>
                    <div class='col-sm-2'>
                        <label>GST
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <i class="fa fa-file" aria-hidden="divue"></i></span>
                                <select name="gst_type" class="form-control" id="gst_type" required />
                                <?php if($gst_type == "Percentage"): $gst_val = round(($gst / $subtotal) * 100); ?>
                                	<option selected>Percentage</option>
                                <?php else : ?>
                                	<option>Percentage</option>
                                <?php endif; ?>

                                <?php if($gst_type == "Raw"): $gst_val = $gst; ?>
                                	<option selected>Raw</option>
                                <?php else : ?>
                                	<option>Raw</option>
                                <?php endif; ?>
                                
                                </select>
                                <input class='form-control' type="text" id='gst' value='<?= $gst_val; ?>' name='gst' placeholder="Enter value">

                            </div>
                        </label>
                    </div>
                    <div class='col-sm-2'>
                        <label>Date
                            <div class="input-group">
                                <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
                                <input type='text' class="form-control" name='date' id='date' value='<?php echo date("d-m-Y"); ?>'>
                            </div>
                        </label>
                    </div>
                </div>
    </div>

    <div id="item_container">
        <div class="row">
            <div class="col-sm-5"></div>
            <div class="col-sm-2">
                <h2>Items
                    <button name="add-item" type="button" id='add-item' class="btn btn-primary" /><i class="glyphicon glyphicon-plus"></i></button>
                </h2>
            </div>
        </div>

        <?php for ($i=0; $i < sizeof($items) ; $i++) : 
        	$item_name = $items[$i]->name;
        	$description = $items[$i]->description;
        	$total_amount = $items[$i]->total_amount;
        ?>
        <div class="row" id="item_<?= $i; ?>">
	        <div class="col-sm-1"></div>
	        <div class="col-sm-1">
	            <h4>Item #<?= $i+1; ?></h4>
	        </div>
	
	        <div class="col-sm-3">
	            <input name="item_name" type="text" id="item_name" class="form-control" value="<?= $item_name; ?>" placeholder="Item" />
	        </div>
	        <div class="col-sm-3">
	            <input name="description" type="text" id="description" class="form-control" value="<?= $description; ?>" placeholder="Description" />
	        </div>
	        <div class="col-sm-3">
	            <input name="total_amount" type="text" id="total_amount" class="form-control" value="<?= $total_amount; ?>" placeholder="Total Amount" />
	        </div>
	        <div class="col-sm-1">
	            <button type="button" class="btn btn-danger delete-item" data-item="<?= $i; ?>">X</button>
	        </div>
	    </div>
		<?php endfor; ?>
		<input type="hidden" id="row_id" value=<?= $i; ?>>
    </div>

    <div class="row">
        <div class="col-sm-2 center">
            <input name="enter" type="submit" id='update' value="Update Invoice" style='margin-top: 30px;width: 100%;' class="btn btn-danger center" />
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
                        <button type="button" class="btn btn-info" id='update_pdf'>Update</button>
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