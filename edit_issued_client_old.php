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
<?php include "partials/nav_bar.html";?>
</head>
<?php 
require "database.php";
 

if(!isset($_SESSION["myusername"])){
  session_destroy();
  header("Refresh:0; url=index.php");
}

else{
?>
  
<script type="text/javascript">
  
$(document).ready(function() {

$('#edit_client').on("click", function(){
  $('#client_labels').slideUp();
  $('#client_data').slideDown();
});

$('#save_client').on("click", function(){

  var formData = {
    client_id : $('#client_id').val(),
    name : $('#name').val(),
    address : $('#address').val(),
    city : $('#city').val(),
    zipcode : $('#zipcode').val(),
    phone : $('#phone').val(),
    leadgen : $('#leadgen').val(),
    adviser : $('#adviser').val(),
    date_generated : $('#date_generated').val(),
    appt_date : $('#appt_date').val(),
  }


  $.ajax({
        type: "POST",
        url: "update_client_data",
        data: formData,
        dataType: 'json',
        //DO SOMETHING IF SUCCESSFUL     
        success: function (data) { 
            //LOG OUTPUT DATA    
            console.log(data);        
            ResetClientData(true);
            $('#client_labels').slideDown();

            $('#client_data').slideUp();
        },
        //DO SOMETHING IF UNSUCCESSFUL  
        error: function (data) {
            console.log('Error:', data);
        }
    });
});

$('.datepicker').datepicker({
    dateFormat: 'dd/mm/yy'});
    

$(document).on('focus',".datepicker_dynamic", function(){
    $(this).datepicker({
    dateFormat: 'dd/mm/yy'});
});


$('#selectme').change(function(){

  $('#leadgen').val($(this).find('option:selected').data('leadgen'));
  $('#assigned_to').val($(this).find('option:selected').data('assignedto'));

});

  var deals = null;
  var objectsHidden = true;
  var dealsCount = 0;
  $('#client_id').on("change", function(){
        ResetClientData();
    });

  $('#deals_div').on("keyup change", ".api", function(){
        var api = $(this).val();
        api = api.replace(/[^0-9.]/g, "");
        console.log(api);
        $(this).val(api);
    });

  $('#deals_div').on("change", ".company_options", function(){
        var company_selected = $(this).val();
        var dealCount = $(this).data("dc");
        console.log("Selected: " + company_selected + "/n Deal Count: " + dealCount);
        if(company_selected=="Others"){
          $('#specific_company_' + dealCount).prop('required',true);
          $('#specific_company_' + dealCount).slideDown();
        }
        else{
          $('#specific_company_' + dealCount).prop('required',false);
          $('#specific_company_' + dealCount).slideUp();
        }
    });

  $('#deals_div').on("change", ".status_options", function(){
        var status_selected = $(this).val();
        var dealCount = $(this).data("dc");
        console.log("Selected: " + status_selected + "/n Deal Count: " + dealCount);
        if(status_selected=="Pending"){
          $('#status_date_' + dealCount).prop('required',false);
          $('#date_issued_' + dealCount).prop('required',false);
          $('#issued_api_' + dealCount).prop('required',false);
          $('#status_date_' + dealCount).slideUp();
          $('#issued_div_' + dealCount).slideUp();
          $('#issued_div_extra_' + dealCount).slideUp();
        }
        else if(status_selected=="Issued"){
          $('#status_date_' + dealCount).prop('required',false);
          $('#date_issued_' + dealCount).prop('required',true);
          $('#issued_api_' + dealCount).prop('required',true);
          $('#issued_div_' + dealCount).slideDown();
          $('#issued_div_extra_' + dealCount).slideDown();
        }
        else{
          $('#status_date_' + dealCount).prop('required',true);
          $('#date_issued_' + dealCount).prop('required',false);
          $('#issued_api_' + dealCount).prop('required',false);
          $('#status_date_' + dealCount).slideDown();
          $('#issued_div_' + dealCount).slideUp();
          $('#issued_div_extra_' + dealCount).slideUp();
        }
    });

  $('#deals_div').on("change", ".clawback_options", function(){
        var clawback_selected = $(this).val();
        var dealCount = $(this).data("dc");
        console.log("Selected: " + clawback_selected + "/n Deal Count: " + dealCount);
        if(clawback_selected!="None"){
          $('#clawback_date_' + dealCount).prop('required',true);
          $('#clawback_api_' + dealCount).prop('required',true);
          $('#clawback_div_' + dealCount).slideDown();
          DisplayTotal();
        }
        else{
          $('#clawback_date_' + dealCount).prop('required',false);
          $('#clawback_api_' + dealCount).prop('required',false);
          $('#clawback_div_' + dealCount).slideUp();
        }
    });

    $('#deals_div').on("click", ".remove_deal", function(){
        var deal = $(this).data("dc");
        $("#deal_" + deal).html('<h2 style="color:red;"><i class="fas fa-file-invoice-dollar" ></i> Deal ' + deal + '</h2>');
    });

    $('#add_deal_btn').on("click", function(){
      AddDealBTN();
    });

    $('body').on("keyup", ".issued_api", function(){
      DisplayTotal();
    });

    $('body').on("keyup", ".cancellation_api", function(){
      DisplayTotal();
    });

    function DisplayTotal(){
      var total_api = TotalIssuedAPI() - TotalCancellationAPI();
      console.log(total_api);
      $('#total_api').html("<h3>" + formatter.format(total_api) + "</h3>");
    }

    function TotalIssuedAPI(){
      var op = 0;
      var issued_apis = document.getElementsByClassName("issued_api");
      
      for (i = 0; i < issued_apis.length; i++) {
          if(issued_apis[i].value!=""&&!(isNaN(issued_apis[i].value)))
            var api =  $("#issued_api_" + (i+1)).val().replace(",", "");
            console.log(api);
            op += parseFloat(api);
        } 
      return op;
    }

    function TotalCancellationAPI(){
      var op = 0;
      var cancellation_apis = document.getElementsByClassName("cancellation_api");
      
      for (i = 0; i < cancellation_apis.length; i++) {
        var clawback_status = $("#clawback_status_" + (i+1)).val();
        //Check Clawback status
        if(clawback_status=="Cancelled"){
          //Check Cancellation API Value
          if(cancellation_apis[i].value!=""&&!(isNaN(cancellation_apis[i].value))){
            var api = cancellation_apis[i].value;
            console.log(cancellation_apis[i].value);
            api = api.replace(/[^0-9.]/g, "");
            op += parseFloat(api);
          }
        }
          
      } 
      return op;
    }



function ResetClientData(resetLabelsOnly = false){
  var client_id = $("#client_id").val();

        $.get('fetch_submission_client_data?client_id=' + client_id, function (data) {
            //success data
            console.log(data);
            var date_generated = data.date_submitted.substr(6) + "/" + data.date_submitted.substr(4,2) + "/" + data.date_submitted.substr(0,4);
            var appt_date = data.appt_date.substr(6) + "/" + data.appt_date.substr(4,2) + "/" + data.appt_date.substr(0,4);
            if(data.leadgen_name==null){
              data.leadgen_name = "Self-Generated";
            }
            //LABEL FIELDS
            $('#client_name_label').html("Client Name: <h3 class='form-control'>" + data.name + "</h3>");
            $('#address_label').html("Address: <h3 class='form-control'>" + data.address + "</h3>");
            $('#city_label').html("City: <h3 class='form-control'>" + data.city + "</h3>");
            $('#zipcode_label').html("Zipcode: <h3 class='form-control'>" + data.zipcode + "</h3>");
            $('#phone_label').html("Phone: <h3 class='form-control'>" + data.appt_time + "</h3>");
            $('#adviser_label').html("Adviser: <h3 class='form-control'>" + data.adviser_name + "</h3>");
            $('#leadgen_label').html("Lead Generator: <h3 class='form-control'>" + data.leadgen_name + "</h3>");
            $('#date_generated_label').html("Client Name: <h3 class='form-control'>" + date_generated + "</h3>");
            $('#appt_date_label').html("Client Name: <h3 class='form-control'>" + appt_date + "</h3>");

            if(resetLabelsOnly)
              return true;

            //EDIT FIELDS
            $('#name').val(data.name);
            $('#address').val(data.address);
            $('#city').val(data.city);
            $('#zipcode').val(data.zipcode);
            $('#phone').val(data.appt_time);
            $('#adviser').val(data.assigned_to);
            $('#leadgen').val(data.leadgen);
            $('#date_generated').val(date_generated);
            $('#appt_date').val(appt_date);

            
            $("#client_name").val(data.name);

            deals = data.deals;
            SyncDeals(deals);
            if(objectsHidden){
              objectsHidden=false;
              $('#client_labels').slideDown();
              $('.total_api').slideDown(); 
              $('#deals_div').slideDown();        
              $('#add_deal_btn_div').slideDown(); 
              $('#add_submission').slideDown(); 
            }
        });
}

function AddDealBTN(){
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
            disabled:true,
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
          $('#status_' + dealsCount).append($('<option>', {
            value: 'Issued',
            text: 'Issued'
          }));

          //Add Compliance Status Options
          $('#compliance_status_' + dealsCount).append($('<option>', {
            selected: true,
            value: 'For Checking',
            text: 'For Checking'
          }));

          $('#compliance_status_' + dealsCount).append($('<option>', {
            value: 'Passed',
            text: 'Passed'
          }));

          $('#compliance_status_' + dealsCount).append($('<option>', {
            value: 'Failed',
            text: 'Failed'
          }));

          $('#compliance_status_' + dealsCount).append($('<option>', {
            value: 'Exempted',
            text: 'Exempted'
          }));


          //Add Clawback Options
          $('#clawback_status_' + dealsCount).append($('<option>', {
            selected: true,
            value: 'None',
            text: 'None'
          }));
          $('#clawback_status_' + dealsCount).append($('<option>', {
            value: 'Arrears',
            text: 'Arrears'
          }));
          $('#clawback_status_' + dealsCount).append($('<option>', {
            value: 'Possible Cancellation',
            text: 'Possible Cancellation'
          }));
          $('#clawback_status_' + dealsCount).append($('<option>', {
            value: 'Cancelled',
            text: 'Cancelled'
          }));

          //Add Commission Status Options
          $('#commission_status_' + dealsCount).append($('<option>', {
            selected: true,
            value: 'Not Paid',
            text: 'Not Paid'
          }));

          $('#commission_status_' + dealsCount).append($('<option>', {
            value: 'Paid',
            text: 'Paid'
          }));

          
          //Add Refund Status Options
          $('#refund_status_' + dealsCount).append($('<option>', {
            value: 'Yes',
            text: 'Yes'
          }));
          $('#refund_status_' + dealsCount).append($('<option>', {
            selected: true,
            value: 'No',
            text: 'No'
          }));

          
          //Add Audit Status Options
          $('#audit_status_' + dealsCount).append($('<option>', {
            value: 'Passed',
            text: 'Passed'
          }));

          $('#audit_status_' + dealsCount).append($('<option>', {
            value: 'Failed',
            text: 'Failed'
          }));

          $('#audit_status_' + dealsCount).append($('<option>', {
            value: 'No Answer',
            text: 'No Answer'
          }));

          $('#audit_status_' + dealsCount).append($('<option>', {
            value: 'Exempted',
            text: 'Exempted'
          }));

          $('#audit_status_' + dealsCount).append($('<option>', {
            value: 'Did not Call',
            text: 'Did not Call'
          }));

          $('#audit_status_' + dealsCount).append($('<option>', {
            selected: true,
            value: 'Pending',
            text: 'Pending'
          }));
        }   

        
    function SyncDeals(deals){
      var syncCtr = 0;
      dealsCount = 0;
      $('#deals_div').html("");
      //Create new fields
      deals.forEach(function(deal){
        AddDealBTN();
      });

      deals.forEach(function(deal){
        syncCtr++;
        $('#company_' + syncCtr).val(deal.company);
        if(deal.company=="Others"){
          $('#specific_company_' + syncCtr).slideDown();
          $('#specific_company_' + syncCtr).val(deal.specific_company);
        }
        $('#status_' + syncCtr).val(deal.status);
        if(deal.status!="Pending" && deal.status!="Issued"){
          $('#status_date_' + syncCtr).slideDown();
          $('#status_date_' + syncCtr).val(deal.status_date.substr(6) + "/" + deal.status_date.substr(4,2) + "/" + deal.status_date.substr(0,4));
        }
        $('#policy_number_' + syncCtr).val(deal.policy_number);
        $('#original_api_' + syncCtr).val(deal.original_api);
        $('#submission_date_' + syncCtr).val(deal.submission_date.substr(6) + "/" + deal.submission_date.substr(4,2) + "/" + deal.submission_date.substr(0,4));
        $('#life_insured_' + syncCtr).val(deal.life_insured);
        if(deal.status=="Issued"){
          $('#date_issued_' + syncCtr).val(deal.date_issued.substr(6) + "/" + deal.date_issued.substr(4,2) + "/" + deal.date_issued.substr(0,4));
          $('#issued_api_' + syncCtr).val(deal.issued_api);
          $('#compliance_status_' + syncCtr).val(deal.compliance_status);
          deal.notes = deal.notes.replace(/<br>/g,"\r\n");
          $('#notes_' + syncCtr).val(deal.notes);
          console.log(deal.notes);
          //Set a value for the commission status if not set
          if(typeof deal.commission_status === 'undefined'){
            deal.commission_status = "Not Paid";
          }

          $('#commission_status_' + syncCtr).val(deal.commission_status);

          //Set a value for the commission status if not set
          if(typeof deal.audit_status === 'undefined'){
            deal.audit_status = "Pending";
          }

          console.log(deal.company + ":" + deal.audit_status);

          $('#audit_status_' + syncCtr).val(deal.audit_status);
          
          //Set a value for the email if not set
          if(typeof deal.email === 'undefined'){
            deal.email = "";
          }

          //Set a value for the email if not set
          if(typeof deal.secondary_email === 'undefined'){
            deal.secondary_email = "";
          }

          $('#email_' + syncCtr).val(deal.email);
          $('#secondary_email_' + syncCtr).val(deal.secondary_email);
          
          //Set a value for the email if not set
          if(typeof deal.birthday !== 'undefined'){
            if(deal.birthday!="")
              $('#birthday_' + syncCtr).val(deal.birthday.substr(6) + "/" + deal.birthday.substr(4,2) + "/" + deal.birthday.substr(0,4));
          }
          else{
            deal.birthday = "";
          }

          //Set a value for the email if not set
          if(typeof deal.secondary_birthday !== 'undefined'){
            if(deal.secondary_birthday!="")
              $('#secondary_birthday_' + syncCtr).val(deal.secondary_birthday.substr(6) + "/" + deal.secondary_birthday.substr(4,2) + "/" + deal.secondary_birthday.substr(0,4));
          }
          else{
            deal.secondary_birthday = "";
          }

          if(deal.secondary_birthday!="" || deal.secondary_email!=""){
            console.log(deal.secondary_birthday + ":" + deal.secondary_email);
            $('#collapse_extra_secondary_' + syncCtr).slideDown();
          }

          $('#submission_date_' + syncCtr).val(deal.submission_date.substr(6) + "/" + deal.submission_date.substr(4,2) + "/" + deal.submission_date.substr(0,4));
          
           
          $('#issued_div_' + syncCtr).slideDown();
          
          $('#issued_div_extra_' + syncCtr).slideDown();
          
          if(deal.clawback_status==undefined)
            deal.clawback_status="None";

          $('#clawback_status_' + syncCtr).val(deal.clawback_status);
        }
        if(deal.clawback_status!="None"&&deal.clawback_status!==undefined){
          $('#clawback_date_' + syncCtr).val(deal.clawback_date.substr(6) + "/" + deal.clawback_date.substr(4,2) + "/" + deal.clawback_date.substr(0,4));
          $('#clawback_api_' + syncCtr).val(deal.clawback_api);
          deal.clawback_notes = deal.clawback_notes.replace(/<br>/g,"\r\n");
          $('#clawback_notes_' + syncCtr).val(deal.clawback_notes);
          if(deal.refund_status==null)
            deal.refund_status = "No";

          $('#refund_status_' + syncCtr).val(deal.refund_status);

          $('#refund_notes_' + syncCtr).val(deal.refund_notes);
          $('#clawback_div_' + syncCtr).slideDown();
        }
      });
      DisplayTotal();
    }


    $("#client_id").val(<?php echo $_GET['edit_id'];?>);
    ResetClientData();
    //JQUERY END
  });

    const formatter = new Intl.NumberFormat('en-US', {
      style: 'currency',
      currency: 'USD',
      minimumFractionDigits: 2
    });
    
function AddDeal(dealsCount){
  // <button type="button" class="btn btn-danger" data-dc = "' + dealsCount + '">x</button>
    //Submission Row
      var new_deal = '\
        <div class="row" id="deal_' + dealsCount + '" style="display:none;">\
            <h2><i class="fas fa-file-invoice-dollar"></i> Deal ' + dealsCount + ' <button type="button" class="btn btn-danger remove_deal" data-dc="' + dealsCount + '">X</button></h2>\
            <div class="col-sm-2">\
                <label>Insurer\
                    <div class="input-group">\
                        <span class="input-group-addon"><i class="fas fa-building"></i></span>\
                        <select  data-dc = "' + dealsCount + '"  id="company_' + dealsCount + '" class="form-control company_options" name="company_' + dealsCount + '" required/>\
                        </select>\
                        <input class="form-control" autocomplete="off" type="text" name="specific_company_' + dealsCount + '" id="specific_company_' + dealsCount + '" step="any" style="display:none;"/>\
                    </div>\
                </label>\
            </div>\
            <div class="col-sm-2">\
                <label>Policy Number\
                    <div class="input-group">\
                        <span class="input-group-addon"><i class="fas fa-hashtag"></i></span>\
                        <input class="form-control" autocomplete="off" type="text" name="policy_number_' + dealsCount + '" id="policy_number_' + dealsCount + '" step="any" required/>\
                    </div>\
                </label>\
            </div>\
            <div class="col-sm-2">\
                <label>Original API\
                    <div class="input-group">\
                        <span class="input-group-addon"><i class="fas fa-usd"></i></span>\
                        <input class="form-control api" autocomplete="off" type="text" id="original_api_' + dealsCount + '" name="original_api_' + dealsCount + '" step="any" required/>\
                    </div>\
                </label>\
            </div>\
            <div class="col-sm-2">\
                <label style="width: 100% !important;">Date of Submission\
                    <div class="input-group">\
                        <span class="input-group-addon"><i class="fa fa-calendar" aria-hidden="divue"></i></span>\
                        <input class="form-control datepicker_dynamic" autocomplete="off"  type="text" id="submission_date_' + dealsCount + '" name="submission_date_' + dealsCount + '" />\
                    </div>\
                </label>\
            </div>\
            <div class="col-sm-2">\
                <label>Add Life Insured\
                    <div class="input-group">\
                        <span class="input-group-addon"><i class="fas fa-user"></i></span>\
                        <input class="form-control" autocomplete="off" type="text" name="life_insured_' + dealsCount + '" id="life_insured_' + dealsCount + '" step="any"/>\
                    </div>\
                </label>\
            </div>\
            <div class="col-sm-2">\
                <label>Status\
                    <div class="input-group">\
                        <span class="input-group-addon"><i class="fas fa-question-circle"></i></span>\
                        <select class="form-control status_options" data-dc = "' + dealsCount + '" name="status_' + dealsCount + '" id="status_' + dealsCount + '" required />\
                        </select>\
                        <input class="form-control datepicker_dynamic" autocomplete="off"  type="text" id="status_date_' + dealsCount + '" name="status_date_' + dealsCount + '"  style="display:none;" />\
                    </div>\
                </label>\
            </div>';

            //Issued Row 
new_deal += '<div class="row"  style="display:none;" id="issued_div_' + dealsCount + '">\
                <div class="col-sm-2">\
                    <label style="width: 100% !important;">Date Issued\
                        <div class="input-group">\
                            <span class="input-group-addon">\
                            <i class="fa fa-calendar" aria-hidden="true"></i></span>\
                            <input class="form-control datepicker_dynamic" autocomplete="off"  type="text" id="date_issued_' + dealsCount + '" name="date_issued_' + dealsCount + '" />\
                        </div>\
                    </label>\
                </div>\
                <div class="col-sm-2">\
                    <label>Issued API\
                        <div class="input-group">\
                            <span class="input-group-addon"><i class="fas fa-usd"></i></span>\
                            <input class="form-control issued_api api" autocomplete="off" type="text" name="issued_api_' + dealsCount + '" id="issued_api_' + dealsCount + '" step="any"/>\
                        </div>\
                    </label>\
                </div>\
                <div class="col-sm-2">\
                    <label>Compliance Status\
                    <div class="input-group">\
                        <span class="input-group-addon"><i class="fas fa-question-circle status_options"></i></span>\
                        <select data-dc = "' + dealsCount + '" class="form-control compliance_status_options" name="compliance_status_' + dealsCount + '" id="compliance_status_' + dealsCount + '"/>\
                        </select>\
                    </div>\
                    </label>\
                </div>\
                <div class="col-sm-2">\
                    <label>Notes\
                        <div class="input-group">\
                        <span class="input-group-addon"><i class="fas fa-user"></i></span>\
                        <textarea class="form-control" name="notes_' + dealsCount + '" id="notes_' + dealsCount + '"></textarea>\
                        </div>\
                    </label>\
                </div>\
                <div class="col-sm-2">\
                    <label>Clawbacks\
                    <div class="input-group">\
                        <span class="input-group-addon"><i class="fas fa-question-circle status_options"></i></span>\
                        <select data-dc = "' + dealsCount + '" class="form-control clawback_options" name="clawback_status_' + dealsCount + '" id="clawback_status_' + dealsCount + '"/>\
                        </select>\
                    </div>\
                    </label>\
                </div>\
                <div class="col-sm-2">\
                    <label>Commission Status\
                    <div class="input-group">\
                        <span class="input-group-addon"><i class="fas fa-question-circle status_options"></i></span>\
                        <select data-dc = "1" class="form-control commission_status_options" name="commission_status_' + dealsCount + '" id="commission_status_' + dealsCount + '"/>\
                        </select>\
                    </div>\
                    </label>\
                </div>\
            </div>\
            \
            <div class="row"  id="issued_div_extra_' + dealsCount + '">\
                <div class="col-sm-2">\
                    <label>Audit Status\
                        <div class="input-group">\
                            <span class="input-group-addon"><i class="fas fa-question-circle audit_status_options"></i></span>\
                            <select data-dc = "1" class="form-control audit_status_options" name="audit_status_' + dealsCount + '" id="audit_status_' + dealsCount + '"/>\
                            </select>\
                        </div>\
                    </label>\
                </div>\
                <div class="col-sm-2">\
                    <label style="width: 100% !important;">Email\
                        <div class="input-group">\
                            <span class="input-group-addon">\
                            <i class="fa fa-envelope" aria-hidden="true"></i></span>\
                            <input class="form-control" autocomplete="off"  type="email" id="email_' + dealsCount + '" name="email_' + dealsCount + '" />\
                        </div>\
                    </label>\
                </div>\
                <div class="col-sm-2">\
                    <label>Birthday\
                        <div class="input-group">\
                            <span class="input-group-addon"><i class="fas fa-user"></i></span>\
                            <input class="form-control datepicker_dynamic" autocomplete="off" type="text" name="birthday_' + dealsCount + '" id="birthday_' + dealsCount + '"/>\
                        </div>\
                    </label>\
                </div>\
                <div class="col-sm-2">\
                    <label>Add Life Insured\
                        <div class="input-group">\
                            <button class="btn btn-primary secondary_information_toggle" autocomplete="off" type="button"  data-toggle="collapse" data-target="#collapse_extra_secondary_' + dealsCount + '" aria-expanded="false" aria-controls="collapse_extra_secondary_' + dealsCount + '">Show Secondary</button>\
                        </div>\
                    </label>\
                </div>\
                <div id="collapse_extra_secondary_' + dealsCount + '" class="collapse">\
                    <div class="col-sm-2">\
                        <label style="width: 100% !important;">Life Insured Email\
                            <div class="input-group">\
                                <span class="input-group-addon">\
                                <i class="fa fa-envelope" aria-hidden="true"></i></span>\
                                <input class="form-control" autocomplete="off"  type="email" id="secondary_email_' + dealsCount + '" name="secondary_email_' + dealsCount + '" />\
                            </div>\
                        </label>\
                    </div>\
                    <div class="col-sm-2">\
                        <label>Life Insured Birthday\
                            <div class="input-group">\
                                <span class="input-group-addon"><i class="fas fa-user"></i></span>\
                                <input class="form-control datepicker_dynamic" autocomplete="off" type="text" name="secondary_birthday_' + dealsCount + '" id="secondary_birthday_' + dealsCount + '"/>\
                            </div>\
                        </label>\
                    </div>\
                </div>\
            </div>';
          //Clawbacks row
new_deal += '<div class="row"  style="display:none;" id="clawback_div_' + dealsCount + '">\
                <div class="col-sm-2">\
                <label style="width: 100% !important;">Date Clawed Back\
                <div class="input-group">\
                    <span class="input-group-addon">\
                    <i class="fa fa-calendar" aria-hidden="true"></i></span>\
                    <input class="form-control datepicker_dynamic" autocomplete="off"  type="text" id="clawback_date_' + dealsCount + '" name="clawback_date_' + dealsCount + '" />\
                </div>\
                </label>\
            </div>\
                <div class="col-sm-2">\
                <label>Amount Clawed Back\
                <div class="input-group">\
                    <span class="input-group-addon"><i class="fas fa-usd"></i></span>\
                <input class="form-control cancellation_api api" autocomplete="off" type="text" name="clawback_api_' + dealsCount + '" id="clawback_api_' + dealsCount + '" step="any"/>\
                </div>\
                </label>\
            </div>\
            <div class="col-sm-2">\
                <label>Notes\
                    <div class="input-group">\
                    <span class="input-group-addon"><i class="fas fa-user"></i></span>\
                    <textarea class="form-control" name="clawback_notes_' + dealsCount + '" id="clawback_notes_' + dealsCount + '"></textarea>\
                    </div>\
                </label>\
                </div>\
                <div class="col-sm-2">\
                <label>Refund Status\
                <div class="input-group">\
                    <span class="input-group-addon"><i class="fas fa-question-circle status_options"></i></span>\
                    <select data-dc = "1" class="form-control refund_status_options" name="refund_status_' + dealsCount + '" id="refund_status_' + dealsCount + '"/>\
                    </select>\
                </div>\
                </label>\
            </div>\
            <div class="col-sm-2">\
                <label>Refund Notes\
                    <div class="input-group">\
                    <span class="input-group-addon"><i class="fas fa-user"></i></span>\
                    <textarea class="form-control" name="refund_notes_' + dealsCount + '" id="refund_notes_' + dealsCount + '"></textarea>\
                    </div>\
                </label>\
                </div>\
            </div>\
        </div>\
    </div>';
return new_deal;
}

</script>
<!--header-->
<div align="center">
<!--header end-->

<!--nav bar-->

<!--nav bar end-->

  <div class="jumbotron">
    <h2 class="slide">Edit Issued Client Profile</h2>
  </div>

<div>


<form method="POST" class="margined">
<input type='hidden' name='deals_count' id='deals_count' value="1">
<div id="client_labels" style="display:none;">
  <div class="row">
    <div class="col-sm-3" id="client_name_label"></div>
    <div class="col-sm-4" id="address_label"></div>
    <div class="col-sm-3" id="city_label"></div>
    <div class="col-sm-2" id="zipcode_label"></div>
  </div>

  <div class="row">
    <div class="col-sm-1"></div>
    <div class="col-sm-2" id="phone_label"></div>
    <div class="col-sm-2" id="leadgen_label"></div>
    <div class="col-sm-2" id="adviser_label"></div>
    <div class="col-sm-2" id="date_generated_label"></div>
    <div class="col-sm-2" id="appt_date_label"></div>
    <div class="col-sm-1"><button type="button"class="btn btn-warning" style="margin-top:34px;" id="edit_client" ><span style="font-size:30px;" class="glyphicon glyphicon-pencil"></span></button></div>
  </div>
  
</div>
<div id="client_data" style="display:none;">
  <div class="row">
    <div class="col-sm-2">
      <label>Client Name
       <div class="input-group">
          <span class="input-group-addon"><i class="fas fa-user"></i></span>
          <input class="form-control" autocomplete="off" type="text" name="name" id="name" required/>
        </div>
      </label>
    </div>
    <div class="col-sm-4">
      <label>Address
       <div class="input-group">
          <span class="input-group-addon"><i class="fas fa-map-marker"></i></span>
        <input class="form-control" autocomplete="off" type="text" name="address" id="address" required/>
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
        <input class="form-control" autocomplete="off" type="text" name="zipcode" id="zipcode" required/>
        </div>
      </label>
    </div>

    <div class="col-sm-2">
      <button type="button"class="btn btn-primary" style="margin-top:13px;" id="save_client"><span style="font-size:30px;" class="glyphicon glyphicon-floppy-disk"></span></button>
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
                  $query = "SELECT * from leadgen_tbl WHERE type='Face-to-Face Marketer' ORDER BY name ASC";
                  $displayquery=mysqli_query($con,$query) or die('Could not look up user information; ' . mysqli_error($con));

                  WHILE($rows = mysqli_fetch_array($displayquery)){
                    $id=$rows["id"];
                    $name=$rows["name"];
                    //echo "<option value='".$id."'>".$name."</option>";
                    echo "<option value='".$id."'>".$name."</option>";
                  }
                ?>
              </optgroup>            
              <optgroup label="Telemarketers">           
                  <?php 

                    $query = "SELECT * from leadgen_tbl where type='Telemarketer' ORDER BY name ASC";
                    $displayquery=mysqli_query($con,$query) or die('Could not look up user information; ' . mysqli_error($con));

                    WHILE($rows = mysqli_fetch_array($displayquery)){
                      $id=$rows["id"];
                      $name=$rows["name"];
                      //echo "<option value='".$id."'>".$name."</option>";
                      echo "<option value='".$id."'>".$name."</option>";
                    }
                  ?>
              </optgroup>  
              <option value="0">Self-Generated</option>
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

                $query = "SELECT * from adviser_tbl ORDER BY name ASC";
                $displayquery=mysqli_query($con,$query) or die('Could not look up user information; ' . mysqli_error($con));

                WHILE($rows = mysqli_fetch_array($displayquery)){
                $id=$rows["id"];
                $name=$rows["name"];
                //echo "<option value='".$id."'>".$name."</option>";
                echo "<option value='".$id."'>".$name."</option>";
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
          <input class="form-control datepicker" autocomplete="off"  type="text" id="date_generated" name="date_generated" required="" />
        </div>
      </label>
    </div>


    <div class="col-sm-2">
      <label style="width: 100% !important;">Appointment Date
        <div class="input-group">
          <span class="input-group-addon">
          <i class="fa fa-calendar" aria-hidden="true"></i></span>
          <input class="form-control datepicker" autocomplete="off"  type="text" id="appt_date" name="appt_date" required="" />
        </div>
      </label>
    </div>

  </div>
</div>

<div class='row' style="padding-top: 30px;">
    <div class='col-sm-2'><h3 class="total_api" style="display: none;">Issued API: </h3></div>
    <div class='col-sm-2'><h3 class="total_api" style="display: none;" id="total_api">$0.00</h3></div>
    <div class='col-sm-4'>
<label>Existing Client
 <div class="input-group">
    <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
    <input type="hidden" name="client_id" class="form-control" id='client_id' readonly/>
    <input type="text" name="client_name" class="form-control" id='client_name' readonly/>
  </div>
</label>

</div>
  <div class='col-sm-2' id="add_deal_btn_div" style="display:none;"><label> New Deal
    <button type="button" class="btn btn-info center form-control" id="add_deal_btn" style="width: 100%; "><i class="glyphicon glyphicon-plus"></i> Add Deal</button>
  </label>
    </div>


</div>
<div id="deals_div" style="display:none;" >
    

</div>

<div class="row" style="padding-top: 30px;">

  <div class="row">
          <div class="col-sm-2 center" >
  <button name="enter" id="add_submission" class="btn btn-info center" type="submit" style="width: 100%; display:none;" >Update Issued Client Profile</button>
</div>
</div>

</div>

</form>

<?php
if(isset($_POST["enter"])){
  extract($_POST);
  //echo $_POST['client_id'];

  $deals = [];
  $total_issued = 0;
  $first_issued_date = "0";
  for($i = 1; $i<= $deals_count; $i++){
    $deal = new stdClass();
    if(isset(${"company_" . $i})){
      $deal->company = ${"company_" . $i};
      if($deal->company=="Others"){
        $deal->specific_company = ${"specific_company_" . $i};
      }
      $deal->policy_number = ${"policy_number_" . $i};
      $deal->original_api = FilterNumber(${"original_api_" . $i});
      $deal->submission_date = ${"submission_date_" . $i};
      $deal->submission_date = DateTimeToNZEntry($deal->submission_date);
      $deal->life_insured = ${"life_insured_" . $i};
      $deal->status = ${"status_" . $i};

      //Extra
      $deal->audit_status = ${"audit_status_" . $i};
      $deal->email = ${"email_" . $i};
      $deal->birthday = ${"birthday_" . $i};
      $deal->birthday = DateTimeToNZEntry($deal->birthday);
      $deal->secondary_email = ${"secondary_email_" . $i};
      $deal->secondary_birthday = ${"secondary_birthday_" . $i};
      $deal->secondary_birthday = DateTimeToNZEntry($deal->secondary_birthday);

      //Set Issuance Info
      if($deal->status=="Issued"){
        $deal->date_issued = DateTimeToNZEntry(${"date_issued_" . $i});

        if($first_issued_date=="0"){
          $first_issued_date = $deal->date_issued;
        }
        else{
          if($first_issued_date > $deal->date_issued && !empty($deal->date_issued))
            $first_issued_date = $deal->date_issued;
        }

        $deal->issued_api = FilterNumber(${"issued_api_" . $i});
        $total_issued +=  (float)${"issued_api_" . $i};

        $deal->compliance_status = ${"compliance_status_" . $i};
        $deal->notes = ${"notes_" . $i};
        $deal->notes = str_replace("'", "\\'", $deal->notes);
        $deal->notes = str_replace("\r\n", "<br>", $deal->notes);
        //$deal->notes = json_encode($deal->notes);
        $deal->commission_status = ${"commission_status_" . $i};
        $deal->clawback_status = ${"clawback_status_" . $i};
        
        //Set Clawback Info
        if($deal->clawback_status!="None"){
          $deal->clawback_date = DateTimeToNZEntry(${"clawback_date_" . $i});          
          $deal->clawback_api = FilterNumber(${"clawback_api_" . $i});
          $deal->clawback_notes = ${"clawback_notes_" . $i};
          $deal->clawback_notes = str_replace("'", "\\'", $deal->clawback_notes);
          $deal->clawback_notes = str_replace("\r\n", "<br>", $deal->clawback_notes);
          $deal->refund_status = ${"refund_status_" . $i};
          $deal->refund_notes = ${"refund_notes_" . $i};
          $deal->refund_notes = str_replace("'", "\\'", $deal->refund_notes);
          $deal->refund_notes = str_replace("\r\n", "<br>", $deal->refund_notes);
        }
      }

      if($deal->status!="Pending" && $deal->status!="Issued"){
        $deal->status_date = ${"status_date_" . $i};
        $deal->status_date = DateTimeToNZEntry($deal->status_date);
      }

      //Push deal to deals array
      $deals[] = $deal;
    }

  }

  debuggingLog("Deals: ",$deals);
    debuggingLog("Total: ",$total_issued);
  $deals_op = json_encode($deals, JSON_HEX_APOS);
   debuggingLog("Deals OP: ",$deals_op);
   $errors = "";
  if($first_issued_date!="0"){

    $sql = "UPDATE submission_clients SET deals='$deals_op' WHERE client_id=$client_id"; 
    
    if(!mysqli_query($con,$sql)){
       $errors .= "UPDATE Submission Clients Error: " . mysqli_error($con) . "<br>";
    }

    $sql = "UPDATE issued_clients_tbl SET leadgen = $leadgen, assigned_to = $adviser, issued = $total_issued WHERE name = $client_id";
    debuggingLog("Update ISSUED CLIENT", $sql);
    if(!mysqli_query($con,$sql)){
       $errors .= "UPDATE ISSUED CLIENTS Error description: " . mysqli_error($con) . "<br>$sql";
    }

    if(empty($errors)){
      echo "<script>
        alert('Issued Client successfully updated!');
      </script>";

      
      //ob_end_flush();
    }
    else{
     echo(var_dump($errors));
    }

  }
  else{
      echo "<script>alert('No issued deal, Issue Client unsuccessful!');</script>";
  }

}

?>
</div>




</html>

<?php

}

function debuggingLog($header="Logged Data",$variable){
  //SET TO TRUE WHEN DEBUGGING SET TO FALSE WHEN NOT
  $isDebuggerActive= false;
  if(!$isDebuggerActive)
    return;
  $op = "<br>";
  $op .=  $header;
  echo $op . "<hr>" . "<pre>";
  var_dump($variable);
  echo "</pre>" . "<hr>";
}

function DateTimeToNZEntry($date_submitted){
  return substr($date_submitted,6,4).substr($date_submitted,3,2).substr($date_submitted,0,2);
}

function NZEntryToDateTime($NZEntry){
    return substr($NZEntry,6,2) . "/" . substr($NZEntry,4,2) . "/" . substr($NZEntry, 0, 4);
}

function FilterNumber($number){
    $op = str_replace( ',', '', $number);
    $op = str_replace( ' ', '', $op);
    return $op;
}
?>