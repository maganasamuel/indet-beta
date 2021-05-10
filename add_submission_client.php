<?php session_start();
ob_start(); ?>
<html>

<head>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">
  <link rel="stylesheet" href="styles.css">
  <link id="favicon" rel="icon" href="Logo_ImageOnly.png" type="image/png" sizes="16x16">
  <title>INDET</title>
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css" integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
  <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js" type="text/javascript"></script>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.0.0/jquery.js"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.7/css/select2.min.css" rel="stylesheet" />
  <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.7/js/select2.min.js"></script>
  <?php include "partials/nav_bar.html"; ?>
</head>
<?php
require "database.php";
include_once("libs/api/classes/general.class.php");
include_once("libs/api/controllers/Adviser.controller.php");
include_once("libs/api/controllers/LeadGenerator.controller.php");
include_once("libs/api/controllers/Deal.controller.php");
include_once("libs/api/controllers/Client.controller.php");

$leadGeneratorController = new LeadGeneratorController();
$adviserController = new AdviserController();
$clientController = new ClientController();
$dealController = new DealController();
$general = new General();

if (!isset($_SESSION["myusername"])) {
  session_destroy();
  header("Refresh:0; url=index.php");
} else {
  ?>
  <script type="text/javascript">
    $(document).ready(function() {

      $("#client_id").select2({
        placeholder: 'Select a client'
      });

      $('.datepicker').datepicker({
        dateFormat: 'dd/mm/yy'
      });


      $(document).on('focus', ".datepicker_dynamic", function() {
        $(this).datepicker({
          dateFormat: 'dd/mm/yy'
        });
      });


      $('#selectme').change(function() {

        $('#leadgen').val($(this).find('option:selected').data('leadgen'));
        $('#assigned_to').val($(this).find('option:selected').data('assignedto'));

      });

      var deals = null;
      var objectsHidden = true;
      var dealsCount = 0;
      $('#client_id').on("change", function() {
        var client_id = $(this).val();

        $.get('fetch_client_data?client_id=' + client_id, function(data) {
          //success data
          console.log(data);
          $('#name').val(data.name);
          $('#address').val(data.address);
          $('#city').val(data.city);
          $('#zipcode').val(data.zipcode);
          $('#phone').val(data.appt_time);
          $('#adviser').val(data.assigned_to);
          $('#leadgen').val(data.leadgen);
          $('#date_generated').val(data.date_submitted.substr(6) + "/" + data.date_submitted.substr(4, 2) + "/" + data.date_submitted.substr(0, 4));
          $('#appt_date').val(data.appt_date.substr(6) + "/" + data.appt_date.substr(4, 2) + "/" + data.appt_date.substr(0, 4));
          deals = data.deals;
          if (objectsHidden) {
            AddDealBtn();
            objectsHidden = false;
            $('#client_data').slideDown();
            $('#deals_div').slideDown();
            $('#add_deal_btn_div').slideDown();
            $('#add_submission').slideDown();
          }
        });
      });

      $('#deals_div').on("change", ".company_options", function() {
        var company_selected = $(this).val();
        var dealCount = $(this).data("dc");
        console.log("Selected: " + company_selected + "/n Deal Count: " + dealCount);
        if (company_selected == "Others") {
          $('#specific_company_' + dealCount).prop('required', true);
          $('#specific_company_' + dealCount).slideDown();
        } else {
          $('#specific_company_' + dealCount).prop('required', false);
          $('#specific_company_' + dealCount).slideUp();
        }
      });

      $('#deals_div').on("keyup change", ".api", function() {
        var api = $(this).val();
        api = api.replace(/[^0-9.]/g, "");
        console.log(api);
        $(this).val(api);
      });

      $('#deals_div').on("change", ".status_options", function() {
        var status_selected = $(this).val();
        var dealCount = $(this).data("dc");
        console.log("Selected: " + status_selected + "/n Deal Count: " + dealCount);
        if (status_selected != "Pending") {
          $('#status_date_' + dealCount).prop('required', true);
          $('#status_date_' + dealCount).slideDown();
        } else {
          $('#status_date_' + dealCount).prop('required', false);
          $('#status_date_' + dealCount).slideUp();
        }
      });

      $('#deals_div').on("click", ".remove_deal", function() {
        var deal = $(this).data("dc");
        $("#deal_" + deal).html('<h2 style="color:red;"><i class="fas fa-file-invoice-dollar" ></i> Deal ' + deal + '</h2>');
      });

      $('#add_deal_btn').on("click", function() {
        AddDealBtn();
      });

      function AddDealBtn() {
        dealsCount++;
        $('#deals_count').val(dealsCount);
        $('#deals_div').append(AddDeal(dealsCount));
        $('#deal_' + dealsCount).slideDown();

        //Add Status Options
        $('#company_' + dealsCount).append($('<option>', {
          value: '',
          text: 'Select Company',
          selected: true,
          hidden: true,
          disabled: true,
        }));
        $('#company_' + dealsCount).append($('<option>', {
          value: 'Fidelity Life',
          text: 'Fidelity Life'
        }));
        $('#company_' + dealsCount).append($('<option>', {
          value: 'AIA',
          text: 'AIA'
        }));
        $('#company_' + dealsCount).append($('<option>', {
          value: 'NIB',
          text: 'NIB'
        }));
        $('#company_' + dealsCount).append($('<option>', {
          value: 'Asteron Life',
          text: 'Asteron Life'
        }));
        $('#company_' + dealsCount).append($('<option>', {
          value: 'Partners Life',
          text: 'Partners Life'
        }));
        $('#company_' + dealsCount).append($('<option>', {
          value: 'Sovereign',
          text: 'Sovereign'
        }));
        $('#company_' + dealsCount).append($('<option>', {
          value: 'Others',
          text: 'Others'
        }));

        //Add Status Options
        $('#status_' + dealsCount).append($('<option>', {
          selected: true,
          value: 'Pending',
          text: 'Pending'
        }));
        $('#status_' + dealsCount).append($('<option>', {
          value: 'Deferred',
          text: 'Deferred'
        }));
        $('#status_' + dealsCount).append($('<option>', {
          value: 'Withdrawn',
          text: 'Withdrawn'
        }));
      }
    });

    function AddDeal(dealsCount) {

      var new_deal = '\
    <div class="row"  id="deal_' + dealsCount + '" style="display:none;">\
        <div class="row">\
          <div class="col">\
            <h2><i class="fas fa-file-invoice-dollar"></i> Deal ' + dealsCount + ' <button type="button" class="btn btn-danger remove_deal" data-dc="' + dealsCount + '">X</button></h2>\
          </div>\
        </div>\
        <br>\
        <div class="row">\
        <div class="col-sm-4"></div>\
          <div class="col-sm-3">\
            <div class="row">\
              <div class="col-sm-4">\
                Insurer\
              </div>\
              <div class="col-sm-8">\
                <div class="input-group">\
                  <span class="input-group-addon"><i class="fas fa-building"></i></span>\
                  <select data-dc="' + dealsCount + '" id="company_' + dealsCount + '" class="form-control company_options" name="company_' + dealsCount + '" required />\
                  </select>\
                  <input class="form-control" autocomplete="off" type="text" name="specific_company_' + dealsCount + '" id="specific_company_' + dealsCount + '" step="any" style="display:none;" />\
                </div>\
              </div>\
            </div>\
            <br>\
            <div class="row">\
              <div class="col-sm-4">\
                Policy Number\
              </div>\
              <div class="col-sm-8">\
                <div class="input-group">\
                  <span class="input-group-addon"><i class="fas fa-hashtag"></i></span>\
                  <input class="form-control" autocomplete="off" type="text" name="policy_number_' + dealsCount + '" id="policy_number_' + dealsCount + '" step="any" required="">\
                </div>\
              </div>\
            </div>\
            <br>\
            <div class="row">\
              <div class="col-sm-4">\
                Original API\
              </div>\
              <div class="col-sm-8">\
                <div class="input-group">\
                  <span class="input-group-addon"><i class="fas fa-usd"></i></span>\
                  <input class="form-control api" autocomplete="off" type="text" id="original_api_' + dealsCount + '" name="original_api_' + dealsCount + '" step="any" required="">\
                </div>\
              </div>\
            </div>\
            <br>\
            <div class="row">\
              <div class="col-sm-4">\
                Submission Date\
              </div>\
              <div class="col-sm-8">\
                <div class="input-group">\
                  <span class="input-group-addon"><i class="fa fa-calendar" aria-hidden="divue"></i></span>\
                  <input class="form-control datepicker_dynamic " autocomplete="off" type="text" id="submission_date_' + dealsCount + '" name="submission_date_' + dealsCount + '">\
                </div>\
              </div>\
            </div>\
            <br>\
            <div class="row">\
              <div class="col-sm-4">\
                Life Insured\
              </div>\
              <div class="col-sm-8">\
                <div class="input-group">\
                  <span class="input-group-addon">\
                    <i class="fas fa-user"></i>\
                  </span>\
                  <input class="form-control" autocomplete="off" type="text" name="life_insured_' + dealsCount + '" id="life_insured_' + dealsCount + '" step="any">\
                </div>\
              </div>\
            </div>\
            <br>\
            <div class="row">\
              <div class="col-sm-4">\
                Status\
              </div>\
              <div class="col-sm-8">\
                <div class="input-group">\
                  <span class="input-group-addon"><i class="fas fa-building"></i></span>\
                  <select data-dc="' + dealsCount + '" id="status_' + dealsCount + '" class="form-control status_options" name="status_' + dealsCount + '" required />\
                  </select>\
                </div>\
              </div>\
            </div>\
          </div>\
      </div>\
    ';
      return new_deal;
    }
  </script>
  <!--header-->
  <div align="center">
    <!--header end-->

    <!--nav bar-->

    <!--nav bar end-->

    <div class="jumbotron">
      <h2 class="slide">Add Client Submission Profile</h2>
    </div>

    <div>

      <form method="POST" class="margined">
        <input type='hidden' name='deals_count' id='deals_count' value="1">
        <div id="client_data" style="display:none;">
          <div class="row">
            <div class="col-sm-2"></div>
            <div class="col-sm-2">
              <label>Client Name
                <div class="input-group">
                  <span class="input-group-addon"><i class="fas fa-user"></i></span>
                  <input class="form-control" autocomplete="off" type="text" name="name" id="name" required />
                </div>
              </label>
            </div>
            <div class="col-sm-2">
              <label>Address
                <div class="input-group">
                  <span class="input-group-addon"><i class="fas fa-map-marker"></i></span>
                  <input class="form-control" autocomplete="off" type="text" name="address" id="address" required />
                </div>
              </label>
            </div>

            <div class="col-sm-2">
              <label style="width: 100% !important;">City
                <div class="input-group">
                  <span class="input-group-addon">
                    <i class="fa fa-map-marker" aria-hidden="divue"></i></span>
                  <input class="form-control" autocomplete="off" type="text" id="city" name="city" required="" />
                </div>
              </label>
            </div>
            <div class="col-sm-2">
              <label>Zip Code
                <div class="input-group">
                  <span class="input-group-addon"><i class="fas fa-map-marker"></i></span>
                  <input class="form-control" autocomplete="off" type="text" name="zipcode" id="zipcode" required />
                </div>
              </label>
            </div>
          </div>
          <div class="row">
            <div class="col-sm-1"></div>
            <div class="col-sm-2">
              <label style="width: 100% !important;">Phone
                <div class="input-group">
                  <span class="input-group-addon">
                    <i class="fa fa-phone" aria-hidden="divue"></i></span>
                  <input class="form-control" autocomplete="off" type="text" id="phone" name="phone" />
                </div>
              </label>
            </div>
            <div class="col-sm-2">
              <label>Lead Generator
                <div class="input-group">
                  <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
                  <select id="leadgen" class="form-control" name="leadgen" required />
                  <option value="" disabled hidden selected>Select Lead Generator</option>
                  <optgroup label="Face-to-Face Marketers">
                    <?php
                      $bdms = $leadGeneratorController->getAllBDMs();

                      while ($rows = $bdms->fetch_assoc()) {
                        $id = $rows["id"];
                        $name = $rows["name"];
                        //echo "<option value='".$id."'>".$name."</option>";
                        echo "<option value='" . $id . "'>" . $name . "</option>";
                      }
                      ?>
                  </optgroup>
                  <optgroup label="Telemarketers">
                    <?php

                      $tms = $leadGeneratorController->getAllTelemarketers();
                      while ($rows = $tms->fetch_assoc()) {
                        $id = $rows["id"];
                        $name = $rows["name"];
                        //echo "<option value='".$id."'>".$name."</option>";
                        echo "<option value='" . $id . "'>" . $name . "</option>";
                      }
                      ?>
                  </optgroup>

                  <option value="0">Self Generated</option>
                  </select>
                </div>
              </label>
            </div>
            <div class="col-sm-2">
              <label>Adviser
                <div class="input-group">
                  <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
                  <select id="adviser" class="form-control" name="adviser" required />
                  <option value="" disabled hidden selected>Select Adviser</option>
                  <?php

                    $advisers = $adviserController->getAllAdvisers();

                    while ($rows = $advisers->fetch_assoc()) {
                      $id = $rows["id"];
                      $name = $rows["name"];
                      //echo "<option value='".$id."'>".$name."</option>";
                      echo "<option value='" . $id . "'>" . $name . "</option>";
                    }
                    ?>






                  ?>
                  </select>
                </div>
              </label>
            </div>


            <div class="col-sm-2">
              <label style="width: 100% !important;">Date Generated
                <div class="input-group">
                  <span class="input-group-addon">
                    <i class="fa fa-calendar" aria-hidden="true"></i></span>
                  <input class="form-control datepicker" autocomplete="off" type="text" id="date_generated" name="date_generated" required="" />
                </div>
              </label>
            </div>


            <div class="col-sm-2">
              <label style="width: 100% !important;">Appointment Date
                <div class="input-group">
                  <span class="input-group-addon">
                    <i class="fa fa-calendar" aria-hidden="true"></i></span>
                  <input class="form-control datepicker" autocomplete="off" type="text" id="appt_date" name="appt_date" required="" />
                </div>
              </label>
            </div>

          </div>
        </div>


        <div class='row' style="padding-top: 30px;">
          <div class='col-sm-4'></div>
          <div class='col-sm-3'>
            <label>Existing Client
              <div class="input-group">
                <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
                <select id="client_id" name="client_id" class="form-control">
                  <option value="" disabled selected>Select Client</option>
                  <?php

                    $clients = $clientController->getAllClientsWithoutSubmissions();
                    while ($rows = $clients->fetch_assoc()) {
                      $id = $rows["id"];
                      $name = $rows["name"];
                      //echo "<option value='".$id."'>".$name."</option>";
                      echo "<option value='" . $id . "'>" . $name . "</option>";
                    }
                    ?>
                </select>
              </div>
            </label>
          </div>
          <div class='col-sm-3' id="add_deal_btn_div" style="display:none;">
            <label> New Deal
              <button type="button" class="btn btn-info center form-control" id="add_deal_btn" style="width: 100%; "><i class="glyphicon glyphicon-plus"></i> Add Deal</button>
            </label>
          </div>
        </div>
        <div id="deals_div" style="display:none;">

        </div>
        <div class="row" style="padding-top: 30px;">

          <div class="row">
            <div class="col-sm-2 center">
              <input name="enter" id="add_submission" class="btn btn-info center" type="submit" value="Add Submission Client" style="width: 100%; display:none;" />
            </div>
          </div>

        </div>

      </form>

      <?php

        if (isset($_POST["enter"])) {
          extract($_POST);
          //echo $_POST['client_id'];

          debuggingLog("POST DATA", $_POST);
          $deals = [];

          for ($i = 1; $i <= $deals_count; $i++) {
            $deal = new stdClass();
            if (isset(${"company_" . $i})) {
              $deal->company = ${"company_" . $i};
              if ($deal->company == "Others") {
                $deal->specific_company = ${"specific_company_" . $i};
              }
              $deal->policy_number = ${"policy_number_" . $i};
              $deal->original_api = FilterNumber(${"original_api_" . $i});
              $deal->submission_date = ${"submission_date_" . $i};
              $deal->submission_date = DateTimeToNZEntry($deal->submission_date);
              $deal->life_insured = ${"life_insured_" . $i};
              $deal->status = ${"status_" . $i};
              if ($deal->status != "Pending") {
                $deal->status_date = ${"status_date_" . $i};
                $deal->status_date = DateTimeToNZEntry($deal->status_date);
              }
              $deals[] = $deal;
            }
          }

          debuggingLog("Deals: ", $deals);
          $deals_op = json_encode($deals);
          $lead_by = "";
          if ($leadgen != 0) {
            $sql = "Select * from leadgen_tbl where id=$leadgen";
            $result = mysqli_query($con, $sql);
            $row = mysqli_fetch_assoc($result);
            $lead_by = $row['type'];
          } else {
            $lead_by = "Self-Generated";
          }
          $appt_date = DateTimeToNZEntry($appt_date);
          $date_generated = DateTimeToNZEntry($date_generated);

          $sql = "UPDATE clients_tbl set name='$name', address='$address', appt_date='$appt_date', date_submitted='$date_generated', city='$city', zipcode='$zipcode', appt_time='$phone', leadgen='$leadgen', lead_by='$lead_by', assigned_to='$adviser' WHERE id=$client_id";
          mysqli_query($con, $sql);

          $sql = "INSERT INTO submission_clients (client_id,deals) VALUES ($client_id,'$deals_op')";

          if (mysqli_query($con, $sql)) {

            header("Location:add_submission_client?success");
            //ob_end_flush();

          } else {
            echo ("Error description: " . mysqli_error($con));
          }
        }

        ?>
    </div>

    <?php
      if (isset($_GET["success"])) {
        echo "<script>
    alert('Submission Client successfully added!');
    window.location = 'add_submission_client';
    </script>";
      }
      ?>


</html>

<?php

}

function debuggingLog($header = "Logged Data", $variable)
{
  //SET TO TRUE WHEN DEBUGGING SET TO FALSE WHEN NOT
  $isDebuggerActive = false;
  if (!$isDebuggerActive)
    return;
  $op = "<br>";
  $op .=  $header;
  echo $op . "<hr>" . "<pre>";
  var_dump($variable);
  echo "</pre>" . "<hr>";
}

function DateTimeToNZEntry($date_submitted)
{
  return substr($date_submitted, 6, 4) . substr($date_submitted, 3, 2) . substr($date_submitted, 0, 2);
}

function FilterNumber($number)
{
  $op = str_replace(',', '', $number);
  $op = str_replace(' ', '', $op);
  return $op;
}
?>