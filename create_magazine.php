<?php

session_start();

include_once 'libs/api/classes/general.class.php';
include_once 'libs/api/controllers/Adviser.controller.php';

$_SESSION['x'] = 1;
unset($_SESSION['adviser_id']);

if (! isset($_SESSION['myusername'])) {
    session_destroy();
    header('Refresh:0; url=index.php');
} else {
    ?>
    <html>

    <head>
        <!--nav bar-->
        <?php include 'partials/nav_bar.html'; ?>

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

            #myModal .modal-dialog {
                min-width: 100%;
                min-height: 100%;
                height: auto;
                width: auto;
                margin: 0;
                padding: 0;
            }

            #mymodal .modal-content {
                width: 100% !important;
                height: auto;
                min-height: 100%;
                border-radius: 0;
            }

            #drop_zone {
                border: 5px solid blue;
                width: 200px;
                height: 100px;
            }
            .nopadding {
                padding: 0 !important;
                margin: 0 !important;
            }

            .sort_handle {
                cursor:pointer;
            }

            #canvas{
                background-color: #ffffff;
                border: 1px solid #cccccc;
            }

        </style>

        <script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/451/fabric.min.js" integrity="sha512-qeu8RcLnpzoRnEotT3r1CxB17JtHrBqlfSTOm4MQzb7efBdkcL03t343gyRmI6OTUW6iI+hShiysszISQ/IahA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

        <script>
            window.onload = function(){
            
            let api = "libs/api/magazine_api.php";
            let photosList = [];
            var uploads_url = "../indet_photos_stash/";

            var canvas = new fabric.Canvas('canvas');
            var currentImage;

            canvas.on('mouse:down', function(options){
                if(options.target){
                    $('#removeImage').removeClass('hidden');
                }else{
                    $('#removeImage').addClass('hidden');
                }
            });

            $('#removeImage').on('click', function(){
                var activeObject = canvas.getActiveObject();

                var filename = activeObject.filename;

                $.ajax({
                    url: "libs/api/magazine_image_deleter",
                    type: 'POST',
                    data: {
                        filename,
                    },
                    success: function(response) {
                        console.log(response);

                        canvas.remove(activeObject);

                        $('#removeImage').addClass('hidden');
                    }
                });
            });

            function addImageUrlToCanvas(link, filename){
                fabric.Image.fromURL(link, function(image){
                    var object = image.set({
                        top: currentImage ? currentImage.top + 15 : 0,
                        left: currentImage ? currentImage.left + 15 : 0,
                        filename: filename
                    });

                    object.scaleToHeight((canvas.height / 2) * 0.75);
                    object.scaleToWidth((canvas.width / 2) * 0.75);

                    currentImage = object;

                    canvas.add(object);
                });
            }

            function preview_image() {
                var total_file = document.getElementById("photos").files.length;
                var fileUpload = document.getElementById('photos');

                var formData = new FormData();

                if (fileUpload.files.length == 0) {
                    alert('Select a file!');
                    return;
                }

                for (var i = 0; i < total_file; i++) {
                    formData.append("fileToUpload", fileUpload.files[i], fileUpload.files[i].name);

                    $.ajax({
                        url: "libs/api/uploader",
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,

                        success: function(data, textStatus, jqXHR) {
                            let link = uploads_url + data;
                            let fn = data.split(".")[0];

                            /* $('#image_preview').append("\
                            <div class='row nopadding' id='" + fn + "'>\
                                <div class='col-md-4' >\
                                    <img class='img img-thumbnail' style='min-height:20vh; max-height:20vh; min-width:100%; max-width:100%;' src='" + link + "'>\
                                </div>\
                                <div class='col-md-8'>\
                                    <div class='row nopadding'>\
                                        <div class='col-xs-2'>\
                                            <h4 class='nopadding'>Label:</h4>\
                                        </div>\
                                        <div class='col-xs-10'>\
                                            <input type='text' class='form-control labels' placeholder='' id='label" + i + "' name='label" + i + "'>\
                                            <input type='hidden' class='form-control filenames' placeholder='filenames' id='filename" + i + "' value='" + link + "' name='filename" + i + "'>\
                                        </div>\
                                    </div>\
                                    <div class='row nopadding'>\
                                        <div class='col-xs-2'>\
                                            <h4 class='nopadding'>Width:</h4>\
                                        </div>\
                                        <div class='col-xs-10'>\
                                            <input type='text' class='form-control widths' placeholder='' id='width" + i + "' name='width" + i + "'>\
                                        </div>\
                                    </div>\
                                    <div class='row nopadding'>\
                                        <div class='col-xs-2'>\
                                            <h4 class='nopadding'>Height:</h4>\
                                        </div>\
                                        <div class='col-xs-10'>\
                                            <input type='text' class='form-control heights' placeholder='' id='height" + i + "' name='height" + i + "'>\
                                        </div>\
                                    </div>\
                                    <div class='row nopadding'>\
                                        <div class='col-xs-4 text-left'>\
                                            <button type='button' class='btn btn-primary form-control sort_handle'>Sort <i class='fas fa-sort'></i></button>\
                                        </div>\
                                        <div class='col-xs-4'>\
                                        </div>\
                                        <div class='col-xs-4'>\
                                            <button type='button' class='btn btn-danger form-control delete_img' data-name='" + fn + "' >Delete <i class='fas fa-trash'></i></button>\
                                        </div>\
                                    </div>\
                                </div>\
                            </div>"); */

                            addImageUrlToCanvas(link, data);
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                            console.log(errorThrown);
                            alert('An error occurred when uploading the file!');
                        }
                    });
                }

                fileUpload.value = '';
            }

            $(document).ready(function() {
                $('#photos').on('change', function(){
                    preview_image();
                });

                $('#image_preview').sortable({
                    handle: 'button.sort_handle',
                    cancel: ''
                });

                $('.datepicker').datepicker({
                    dateFormat: 'dd/mm/yy'
                });

                $('body').tooltip({
                    selector: '[data-toggle=tooltip]',
                    html: true
                });

                $(document).on("click", ".delete_img",  function(){
                    let name =  $(this).data("name");
                    $("#" + name).remove();
                });

                $("#date").on("change", function() {
                    let date = $(this).val();
                    let data = {
                        date: date,
                        action: "get_series"
                    }

                    $("#series").val("Loading...");

                    $.ajax({
                        dataType: 'json',
                        type: 'POST',
                        data: data,
                        url: api,
                        success: function(e) {
                            $("#series").val(e.series);
                            $("#magazine_date").val(e.date);
                            $("#magazine_data").val(e.magazine_data);
                        },
                        error: function(x) {
                            console.log(x);
                        }
                    });
                });

                $('#create').on('click', function(e) {
                    e.preventDefault();

                    var formData = new FormData();
                    var fileUpload = document.getElementById('photos');
                    let button = $(this);


                    var widths = [];
                    var heights = [];
                    var labels = [];
                    var filenames = [];

                    $('.widths').each(function() {
                        widths.push(this.value);
                    });

                    $('.heights').each(function() {
                        heights.push(this.value);
                    });

                    $('.labels').each(function() {
                        labels.push(this.value);
                    });

                    $('.filenames').each(function() {
                        filenames.push(this.value);
                    });

                    var date = $("#date").val();
                    var quote = $("#quote").val();
                    var message = $("#message").val();
                    var announcement = $("#announcement").val();

                    formData.append("widths", widths);
                    formData.append("heights", heights);
                    formData.append("labels", labels);
                    formData.append("date", date);
                    formData.append("quote", quote);
                    formData.append("message", message);
                    formData.append("announcement", announcement);
                    formData.append("output", true);
                    formData.append("random_filename", true);

                    if(canvas.getObjects().length){
                        var dataUrl = canvas.toDataURL({
                            format: 'png',
                        });

                        formData.append('photo_blob', dataUrl);
                    }

                    $.ajax({
                        dataType: 'json',
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        url: "generate_magazine.php",
                        beforeSend: function(){
                            button.prop("disabled", true);
                            $("#create_magazine_text").text("Generating Report");
                            $("#create_magazine_spinner").show();
                        },
                        success: function(e) {
                            button.prop("disabled", false);
                            $("#create_magazine_text").text("Create Magazine");
                            $("#create_magazine_spinner").hide();
                            
                            var mydata = JSON.stringify(e.data);
                            var link = e['link'];
                            var htm = '<iframe src="' + link + '" style="width: 100%;height: 75%;"></iframe>';
                            $('#myModal').modal('show');
                            $('.modal-body').html(htm);
                            $('#save_pdf').unbind("click");
                            $('#save_pdf').on('click', function() {
                                $.ajax({
                                    //dataType:'JSON',
                                    data: {
                                        magazine_data: mydata,
                                        action: "create_magazine"
                                    },
                                    type: 'POST',
                                    url: "libs/api/magazine_api.php",
                                    beforeSend: function() {
                                        $("#save_pdf").prop("disabled", true);
                                        $("#save_magazine_text").text("Saving Magazine");
                                        $("#save_magazine_spinner").show();
                                    },
                                    success: function(x) {
                                        $("#save_pdf").prop("disabled", false);
                                        $("#save_magazine_text").text("Create Magazine");
                                        $("#save_magazine_spinner").hide();
                                        
                                        $.confirm({
                                            title: 'Success!',
                                            content: 'You have successfully created a magazine ',
                                            buttons: {
                                                Ok: function() {
                                                    window.location = 'create_magazine.php';
                                                },
                                            }
                                        });
                                    },
                                    error: function(x){
                                        console.log(x);
                                        $("#save_pdf").prop("disabled", false);
                                        $("#save_magazine_text").text("Create Magazine");
                                        $("#save_magazine_spinner").hide();
                                    }
                                });
                            });
                        },
                        error: function(x) {
                            console.log(x);
                            button.prop("disabled", false);
                            $("#create_magazine_text").text("Create Magazine");
                            $("#create_magazine_spinner").hide();
                        }
                    });

                });

            });

            }
        </script>
    </head>

    <body>

        <!--header-->
        <div align="center">
            <div class="jumbotron">
                <h2 class="slide">Create Magazine</h2>
            </div>
            <!--label end-->

            <?php
            require 'database.php';
    $adviserController = new AdviserController();
    $generalController = new General(); ?>

            <form method="POST" action="create_customized_invoice.php" id="form" autocomplete="off" class="margined">
                <div class="row">
                    <div class="col-xs-6">

                        <div class='row'>
                            <div class='col-xs-2 text-right'>
                                <h4>Date</h4>
                            </div>
                            <div class='col-xs-4'>
                                <input type='text' class="form-control datepicker" name='date' id='date'>
                            </div>

                            <div class='col-xs-2 text-right'>
                                <h4>Series</h4>
                            </div>
                            <div class='col-xs-4'>
                                <input type='text' readonly class="form-control" name='series' id='series'>
                                <input type='hidden' readonly class="form-control" name='magazine_date' id='magazine_date'>
                                <input type='hidden' readonly class="form-control" name='magazine_data' id='magazine_data'>
                            </div>
                        </div>

                        <div class='row'>
                            <div class='col-xs-2 text-right'>
                                <h4>Quote </h4>
                            </div>
                            <div class='col-xs-10'>
                                <input type='text' class="form-control" name='quote' id='quote'>
                            </div>
                        </div>

                        <div class='row'>
                            <div class='col-xs-12 text-center'>
                                <h3> Message <i data-toggle="tooltip" data-placement="top" title="You can enter '{indent}' to add an indentations to the first line of your paragraph." class="fas fa-question-circle"></i> </h3>
                            </div>
                        </div>

                        <div class='row'>
                            <div class='col-xs-12'>
                                <textarea class="form-control" name='message' id='message' style="height:20vh">Team EliteInsure,</textarea>
                            </div>
                        </div>

                        <br>

                        <div class='row'>
                            <div class='col-xs-12 text-center'>
                                <h3> Announcement <i data-toggle="tooltip" data-placement="top" title="You can enter '{indent}' to add an indentations to the first line of your paragraph." class="fas fa-question-circle"></i> </h3>
                            </div>
                        </div>

                        <div class='row'>
                            <div class='col-xs-12'>
                                <textarea class="form-control" name='announcement' id='announcement' style="height:20vh"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="col-xs-6">
                        <div class='row'>
                            <div class='col-xs-12 text-center'>
                                <h3> Photos <i id="photos_tooltip" data-toggle="tooltip" data-html="true" title="Adjust the width (w) and height (h) accordingly. Not setting the w/h will set it to 30 by default. Maximum width is 196, maximum height is 259." data-placement="top" class="fas fa-question-circle"></i> </h3>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-xs-12" id="photosDiv">
                                <!--
                                <div class="form-group" for="photos" ondrop="dropHandler(event);" ondragover="dragOverHandler(event);" style="background-color:#EEEEEE; padding:30px; border-style:dashed; border-color: #CCCCFF;">
                                    <h3 class="text-center">Drop images here</h3>
                                </div>
                                -->
                                <input type="file" id="photos" name="photos[]" class="form-control" style="text-align:center;padding-top:30px; padding-bottom:50px; background-color:white; border-style:dashed; border-color:#00A;" multiple accept="image/jpeg,image/png,image/bmp" />
                            </div>
                        </div>

                        <!-- <div id="image_preview" style="max-height:60vh; overflow-x:auto;" class="row"></div> -->

                        <div class="row">
                            <br>
                            <div class="col-xs-12 text-left">
                                <canvas id="canvas" width="622" height="526"></canvas>
                                <br>
                                <button type="button" id="removeImage" class="btn btn-sm btn-danger hidden">Remove Image</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xs-2 center">
                        <button name="enter" type="button" id='create' style='margin-top: 30px;width: 100%;' class="btn btn-danger center" /><i id="create_magazine_spinner" style="display:none;" class="fas fa-spinner fa-spin"></i> <span id="create_magazine_text">Create Magazine</span></button>
                    </div>
                </div>
            </form>
        </div>

        <div class="container">
            <!-- Modal -->
            <div class="modal fade" id="myModal" role="dialog" style="z-index:10000;width: 100%;">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                            <h2 class="modal-title" style="float: left;">Magazine Preview</h2>
                        </div>
                        <div class="modal-body">

                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-info" id='save_pdf'><i id="save_magazine_spinner" style="display:none;" class="fas fa-spinner fa-spin"></i> <span id="save_magazine_text">Save Magazine</span></button>
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