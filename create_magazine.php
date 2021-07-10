<?php

session_start();

include_once 'libs/api/classes/general.class.php';
include_once 'libs/api/controllers/Adviser.controller.php';

$_SESSION['x'] = 1;
unset($_SESSION['adviser_id']);

if (!isset($_SESSION['myusername'])) {
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

  <title>INDET</title>

  <link id="favicon" rel="icon" href="Logo_ImageOnly.png" type="image/png" sizes="16x16">
  <link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">
  <link rel="stylesheet" href="styles.css">
  <link rel="stylesheet" href="css/create-magazine.css" />


  <script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/451/fabric.min.js"
    integrity="sha512-qeu8RcLnpzoRnEotT3r1CxB17JtHrBqlfSTOm4MQzb7efBdkcL03t343gyRmI6OTUW6iI+hShiysszISQ/IahA=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>
  <script type="text/javascript" src="js/create-magazine.js"></script>
</head>

<body>
  <?php
  require 'database.php';
  $adviserController = new AdviserController();
  $generalController = new General();
  ?>
  <!--header-->
  <div align="center">
    <div class="jumbotron">
      <h2 class="slide">Create Magazine</h2>
    </div>
    <!--label end-->

    <form method="POST" action="create_customized_invoice.php" id="form" autocomplete="off" class="margined">
      <div class="row">
        <div class="col-md-6 col-md-offset-3">
          <div class='row'>
            <div class='col-md-2 text-right'>
              <h4>Date</h4>
            </div>
            <div class='col-md-4'>
              <input type='text' class="form-control datepicker" name='date' id='date'>
            </div>

            <div class='col-md-2 text-right'>
              <h4>Series</h4>
            </div>
            <div class='col-md-4'>
              <input type='text' readonly class="form-control" name='series' id='series'>
              <input type='hidden' readonly class="form-control" name='magazine_date' id='magazine_date'>
              <input type='hidden' readonly class="form-control" name='magazine_data' id='magazine_data'>
            </div>
          </div>

          <div class='row'>
            <div class='col-md-12 text-center'>
              <h3>
                <button type="button" class="btn btn-info" data-toggle="modal"
                  data-target="#photosModal">Photos</button>
                <i id="photos_tooltip" data-toggle="popover" data-content="Add photos and texts to magazine."
                  data-placement="top" data-trigger="hover" class="fas fa-question-circle"></i>
              </h3>
            </div>
          </div>

          <div class='row'>
            <div class='col-md-2 text-right'>
              <h4>Quote </h4>
            </div>
            <div class='col-md-10'>
              <input type='text' class="form-control" name='quote' id='quote'>
            </div>
          </div>

          <div class='row'>
            <div class='col-md-12 text-center'>
              <h3> Message <i data-toggle="popover" data-placement="top" data-trigger="hover"
                  data-content="You can enter '{indent}' to add an indentations to the first line of your paragraph."
                  class="fas fa-question-circle"></i> </h3>
            </div>
          </div>

          <div class='row'>
            <div class='col-md-12'>
              <textarea class="form-control" name='message' id='message'
                style="height:15vh">Team EliteInsure,</textarea>
            </div>
          </div>

          <div class='row'>
            <div class='col-md-12 text-center'>
              <h3> Announcement <i data-toggle="popover" data-placement="top" data-trigger="hover"
                  data-content="You can enter '{indent}' to add an indentations to the first line of your paragraph."
                  class="fas fa-question-circle"></i> </h3>
            </div>
          </div>

          <div class='row'>
            <div class='col-md-12'>
              <textarea class="form-control" name='announcement' id='announcement'
                style="height:15vh"></textarea>
            </div>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-md-2 center">
          <button name="enter" type="button" id='create' style='margin-top: 30px;width: 100%;'
            class="btn btn-danger center" /><i id="create_magazine_spinner" style="display:none;"
            class="fas fa-spinner fa-spin"></i> <span id="create_magazine_text">Create
            Magazine</span></button>
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
            <button type="button" class="btn btn-info" id='save_pdf'><i id="save_magazine_spinner"
                style="display:none;" class="fas fa-spinner fa-spin"></i> <span id="save_magazine_text">Save
                Magazine</span></button>
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="photosModal" tabindex="-1" role="dialog" aria-labelledby="photosModal">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
              aria-hidden="true">&times;</span></button>
          <h4 class="modal-title" id="myModalLabel">Photos</h4>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-2">
              <div class="mb-1">
                <label class="btn btn-sm btn-info">
                  Upload Photos
                  <input type="file" id="photos" name="photos[]" multiple
                    accept="image/jpeg,image/png,image/bmp" class="hidden" />
                </label>
              </div>
              <div>
                <button type="button" id="addPage" class="btn btn-sm btn-info">Add Page</button>
              </div>
            </div>

            <div class="col-md-7 text-left">
              <div class="row">
                <div class="col-md-6 text-left">
                  <input type="text" id="photoText" class="form-control input-sm"
                    placeholder="Write your photo text">
                </div>
                <div class="col-md-6 text-right">
                  <select id="font" class="form-control input-sm"></select>
                </div>
              </div>
              <div class="mb-1"></div>
              <div class="row">
                <div class="col-md-6 text-left">
                  <div class="btn-group" data-toggle="buttons">
                    <label class="btn btn-sm btn-info">
                      <input type="checkbox" id="bold" /><i class="fa fa-bold"></i>
                    </label>
                    <label class="btn btn-sm btn-info">
                      <input type="checkbox" id="italic" /> <i class="fa fa-italic"></i>
                    </label>
                    <label class="btn btn-sm btn-info">
                      <input type="checkbox" id="underline" /><i class="fa fa-underline"></i>
                    </label>
                  </div>
                  <input type="color" id="color">
                </div>
                <div class="col-md-6 text-right">
                  <button type="button" id="addText" class="btn btn-sm btn-info">Add Text</button>
                </div>
              </div>
            </div>

            <div class="col-md-3 text-right">
              <div class="mb-1">
                <button type="button" id="removeObject" class="btn btn-sm btn-danger">Remove Image /
                  Text</button>
              </div>
              <div>
                <button type="button" id="removePage" class="btn btn-sm btn-danger">Remove Page</button>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-12">
              <div id="pageWrapper">
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>
</body>

</html>

<?php
}

?>
