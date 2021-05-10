//APIs
var client_api_url = "crud/clients-crud.php";
var deal_api_url = "libs/api/deal_api.php";
var follow_up_history_api_url = "libs/api/follow_up_history_api.php";

//Tables
var table = null;
var deals_table = null;
var history_table = null;

//JQuery Start
$(document).ready(function () {
  $('body').tooltip({
    selector: '[rel=tooltip]'
  });

  table = $("#issued_clients_table").DataTable();
  deals_table = $("#client_deals_table").DataTable();
  history_table = $("#follow_up_history_table").DataTable();

  $(document).on('focus', ".datepicker_dynamic", function () {
    $(this).datepicker({
      dateFormat: 'dd/mm/yy'
    });
  });

  $("#myModal").on("hidden.bs.modal", function () {
    $("#clients_list").val("").trigger("change");
  });

  $("#btn-add").on("click", function () {
    resetDealsData();
    $("#btn-save-deal_data").data("action", "add");
    $('#client_data_nav').find('a').trigger('click');
    $("#select2-clients_list-container").parent().show();
    hideData();
  });

  $("#btn-add_history").on("click", function () {
    $("#btn-save-save_history").data("action", "add");
  });

  $(document).on("click", ".btn-edit", function () {
    $("#btn-save-deal_data").data("action", "update");
    $('#client_data_nav').find('a').trigger('click');
    var client_id = $(this).data("client_id");
    var kiwisaver_id = $(this).data("id");
    $("#client_id").val();
    $("#select2-clients_list-container").parent().hide();
    $("#nav_tabs").show();
    $("#data_tabs").show();
    $("#kiwisaver_id").val(kiwisaver_id);
    LoadClient(client_id);
    LoadKiwiSaver(kiwisaver_id);
    $('#myModal').modal('show');
  });

  $(document).on("click", ".btn-edit_history", function () {
    $("#btn-save_history").data("action", "update");
    var history_id = $(this).data("id");
    $("#history_id").val(history_id);
    LoadNote(history_id);
    $('#followUpHistoryEditorModal').modal('show');
  });

  $(document).on("click", ".btn-delete", function () {
    var kiwisaver_profile_id = $(this).data("id");
    $.confirm({
      title: 'Confirm Action',
      content: 'Do you want to delete this KiwiSaver Profile?',
      type: 'red',
      typeAnimated: true,
      buttons: {
        confirm: {
          text: 'Confirm',
          btnClass: 'btn-red',
          action: function () {
            var data = {
              kiwisaver_profile_id: kiwisaver_profile_id,
              action: "delete_kiwisaver_profile"
            }
            $.ajax({
              data: data,
              type: "post",
              url: deal_api_url,
              success: function (data) {
                $("#kiwisaver_" + kiwisaver_profile_id).remove();
                table.row("#kiwisaver_" + kiwisaver_profile_id).remove().draw(false);
                console.log(data);
              },
              error: function (data) {
                console.log("Error", data);
              }
            });
          }
        },
        cancel: {}
      }
    });
    //$("#client_id").hide();
  });

  $(document).on("click", ".btn-view", function () {
    var client_id = $(this).data("id");
    $('#clientDealsModal').modal('show');

    ViewDeals(client_id);
  });

  $(document).on("click", ".btn-history", function () {
    var client_id = $(this).data("id");
    $('#followUpHistoryModal').modal('show');
    $("#client_id").val(client_id);
    ViewHistory(client_id);
  });


  $.fn.serializeObject = function () {
    var o = {};
    var a = this.serializeArray();
    $.each(a, function () {
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

  $(document).on('click', '#btn-save-deal_data', function () {

    var btn = $(this);
    var state = $("#btn-save-deal_data").data("action");

    btn.prop("disabled", true);

    $("#save_deal_spinner").show();

    var dealsData = $("#frmDeals").serializeObject();
    var client_id = $("#client_id").val();
    var kiwisaver_profile_id = $("#kiwisaver_profile_id").val();
    var leadgen = $("#leadgen").val();
    var assigned_to = $("#assigned_to").val();
    var data = {
      client_id: client_id,
      kiwisaver_profile_id: kiwisaver_profile_id,
      deals_data: dealsData,
      action: state + "_kiwisaver_profile"
    };

    console.log(data);

    $.ajax({
      data: data,
      type: "post",
      url: deal_api_url,
      success: function (data) {
        $("#save_deal_spinner").hide();
        
        data = JSON.parse(data);
        console.log(data);

        var newData = {
          "0": data.client_name,
          "1": {
            "display": "$" + ToCurrency(data.commission),
            "@data-order": data.commission
          },
          "2": {
            "display": "$" + ToCurrency(data.gst),
            "@data-order": data.gst
          },
          "3": {
            "display": "$" + ToCurrency(data.balance),
            "@data-order": data.balance
          },
          "4": {
            "display": data.issue_date,
            "@data-order": data.issue_date_order
          },
          "5": data.adviser_name,
          "6": '<a class="btn-edit" data-toggle="tooltip" title="Edit KiwiSaver Profile" data-id="' + data.id + '" data-client_id=' + data.client_id + '><span class="btn btn-warning glyphicon glyphicon-pencil"></span></a>',
          "7": '<a class="btn-delete" data-toggle="tooltip" title="Delete KiwiSaver Profile" data-id="' + data.id + '"><span class="btn btn-danger glyphicon glyphicon-trash"></span></td>'
        };

        console.log(newData);
        if (state == "add") { //if user added a new record
          tbl_r = table.row.add(newData).node().id = "kiwisaver_" + data.id;
          table.row("#kiwisaver_" + data.id).data(newData).draw(false);
        } else { //if user updated an existing record
          table.row("#kiwisaver_" + data.id).data(newData).draw(false);
        }


        $("#clients_list option[value='" + client_id + "']").detach();
        $("#clients_list").val("");
        $('#myModal').modal('hide');
        btn.prop("disabled", false);
        resetDealsData();
      },
      error: function (data) {
        btn.prop("disabled", false);
        console.log("Error", data);
        $('#myModal').modal('hide');
        $("#client_id").val("");

        hideData();
      }
    });
  });


  $(document).on('click', '#btn-save_history', function () {

    var btn = $(this);
    var state = $("#btn-save_history").data("action");

    btn.prop("disabled", true);

    var client_id = $("#client_id").val();

    var data = {
      client_id: client_id,
      history_id: $("#history_id").val(),
      notes: $("#follow_up_history_notes").val(),
      action: state + "_follow_up_history"
    };

    console.log(data);

    $.ajax({
      data: data,
      type: "post",
      url: follow_up_history_api_url,
      success: function (data) {
        console.log(data);
        data = JSON.parse(data);
        console.log(data);

        var newData = {
          "0": data.username,
          "1": data.notes,
          "2": {
            "display": data.formatted_timestamp,
            "@data-order": data.timestamp
          },
          "3": '<a class="btn-edit_history" data-id="' + data.id + '"><span class="btn btn-warning glyphicon glyphicon-pencil"></span></i></a>',
          "4": '<a class="btn-delete_history" data-id="' + data.id + '"><span class="btn btn-danger glyphicon glyphicon-trash"></span></i></a>'
        };
        if (state == "add") { //if user added a new record
          tbl_r = history_table.row.add(newData).node().id = "history_" + data.id;
          history_table.row("#history_" + data.id).data(newData).draw(false);
        } else { //if user updated an existing record
          history_table.row("#history_" + data.id).data(newData).draw(false);
        }

        $('#followUpHistoryEditorModal').modal('hide');
        btn.prop("disabled", false);
        $("#follow_up_history_notes").val("");
      },
      error: function (data) {
        btn.prop("disabled", false);
        console.log("Error", data);

      }
    });
  });

  $(document).on('click', '#btn-save-client_data', function (e) {

    $("#save_client_spinner").show();
    var btn = $(this);
    btn.prop("disabled", true);
    var data = $("#frmClient").serialize();

    //Add Client ID
    data = data + "&client_id=" + $("#client_id").val();

    state = "update";
    console.log(state);
    $.ajax({
      data: data,
      type: "post",
      url: client_api_url,
      success: function (data) {
        console.log(data);
        let client_data = {
          action: "update_kiwisaver_profile",
          assigned_to: data.assigned_to,
          client_id: data.id,
          leadgen: data.leadgen
        }
        $.ajax({
          data: client_data,
          type: "post",
          url: "libs/client_helper.php",
          success: function (data) {
            $("#save_client_spinner").hide();
            console.log(data);
            let row = table.row("#kiwisaver_" + data.id);
            let rowIndex = row.index();
            console.log("Drawing");
            table.cell({
              row: rowIndex,
              column: 3
            }).data(data.adviser_name);
            console.log("Drawn");
          },
          error: function (data) {
            console.log("Error", data);
          }
        });

        $.confirm({
          title: 'Update Success',
          content: 'Client data updated!',
          type: 'green',
          typeAnimated: true,
          buttons: {
            confirm: {
              text: 'Confirm',
              btnClass: 'btn-green',
              action: function () {}

            }
          }
        });
        btn.prop("disabled", false);
      },
      error: function (data) {
        btn.prop("disabled", false);
        console.log("Error", data);
        $('#myModal').modal('hide');
      }
    });

  });

  $('#deals_div').on("change", ".status_options", function () {
    var status_selected = $(this).val();
    var dealCount = $(this).data("dc");
    console.log("Selected: " + status_selected + "/n Deal Count: " + dealCount);
    if (status_selected == "Pending") {
      $('#status_date_' + dealCount).prop('required', false);
      $('#date_issued_' + dealCount).prop('required', false);
      $('#issued_api_' + dealCount).prop('required', false);
      $('#status_date_' + dealCount).slideUp();
      $('#issued_div_' + dealCount).slideUp();
      $('#issued_div_extra_' + dealCount).slideUp();
      $('#clawback_div_' + dealCount).slideUp();
    } else if (status_selected == "Issued") {
      $('#status_date_' + dealCount).prop('required', false);
      $('#date_issued_' + dealCount).prop('required', true);
      $('#issued_api_' + dealCount).prop('required', true);
      $('#issued_div_' + dealCount).slideDown();
      $('#issued_div_extra_' + dealCount).slideDown();
    } else {
      $('#status_date_' + dealCount).prop('required', true);
      $('#date_issued_' + dealCount).prop('required', false);
      $('#issued_api_' + dealCount).prop('required', false);
      $('#status_date_' + dealCount).slideDown();
      $('#issued_div_' + dealCount).slideUp();
      $('#issued_div_extra_' + dealCount).slideUp();
    }
  });

  $(document).on("change", ".clawback_options", function () {
    var clawback_selected = $(this).val();
    console.log(clawback_selected);
    var dealCount = $(this).data("dc");
    console.log("Selected: " + clawback_selected + "/n Deal Count: " + dealCount);
    if (clawback_selected != "None") {
      $('#clawback_div_' + dealCount).slideDown();
    } else {
      $('#clawback_div_' + dealCount).slideUp();
    }
  });

  function TimestampToNZFormat(timestamp) {
    if (timestamp == "")
      return "";

    return timestamp.substring(6, 8) + "/" + timestamp.substring(4, 6) + "/" + timestamp.substring(0, 4);
  }

  function SyncDeals(deals) {
    dealsCount = 0;
    $('#deals_div').html("");
    deals.forEach(function (deal, index) {
      AddDealBtn();
      $("#kiwisaver_deal_id_"+ (index + 1)).val(deal.id);
      $("#name_" + (index + 1)).val(deal.name);
      $("#commission_" + (index + 1)).val(deal.commission);
      $("#gst_" + (index + 1)).val(deal.gst);
      $("#balance_" + (index + 1)).val(deal.balance);
      $("#issue_date_" + (index + 1)).val(TimestampToNZFormat(deal.issue_date));
      let count = (deal.count == "Yes");

      $("#count_switch_" + (index + 1)).prop("checked", count);
      $("#count_" + (index + 1)).val(deal.count);
    });

  }

  $("#clients_list").select2({
    placeholder: 'Select a client',
    dropdownParent: $("#myModalDialog")
  });

  $('.datepicker').datepicker({
    dateFormat: 'dd/mm/yy'
  });




  $('#selectme').change(function () {

    $('#leadgen').val($(this).find('option:selected').data('leadgen'));
    $('#assigned_to').val($(this).find('option:selected').data('assignedto'));

  });

  var deals = null;
  var objectsHidden = true;
  var dealsCount = 0;

  $('#clients_list').on("change", function () {
    if ($("#clients_list").val() == null || $("#clients_list").val() == "" || $("#clients_list").val() == 0) {
      console.log("No client selected");
      return;
    }

    var client_id = $(this).val();
    $("#client_id").val(client_id);

    LoadClient(client_id);
  });

  function resetDealsData() {
    deals = null;
    objectsHidden = true;
    dealsCount = 0;
    $("#nav_tabs").hide();
    $("#data_tabs").hide();
    $('#client_data_nav').find('a').trigger('click');
    ClearDeals();
  }

  function LoadClient(client_id) {
    if (client_id == "" || client_id == null) {
      console.log("No Client ID Loaded.");
      return;
    }

    $.get('fetch_client_data?client_id=' + client_id, function (data) {
      //success data
      console.log(data);
      $("#status_div").show();
      //new
      $('#client_id').val(data.id);
      $('#name').val(data.name);
      $('#email').val(data.email);

      var appt_date = TimestampToNZFormat(data.appt_date);
      var assigned_date = TimestampToNZFormat(data.assigned_date);
      var date_submitted = TimestampToNZFormat(data.date_submitted);

      $('#date_submitted').val(date_submitted);
      $('#appt_date').val(appt_date);
      $('#assigned_date').val(assigned_date);
      $("#phone_num").val(data.appt_time);
      $("#address").val(data.address);
      $("#city").val(data.city);
      $("#zipcode").val(data.zipcode);
      $("#lead_by").val(data.lead_by);
      $("#leadgen").val(data.leadgen);

      if (data.lead_by == "Telemarketer") {
        $("#leadgen_telemarketer").show();
        $("#leadgen_bdm").hide();
        $("#leadgen_telemarketer").val(data.leadgen);
      } else if (data.lead_by == "Face-to-Face Marketer") {
        $("#leadgen_bdm").show();
        $("#leadgen_telemarketer").hide();
        $("#leadgen_bdm").val(data.leadgen);
      }

      $("#assigned_to").val(data.assigned_to);
      $("#notes").val(data.notes);
      $("#status").val(data.status);
      $('#date_status_updated').datepicker().datepicker("setDate", new Date());

      $('#formtype').val("update");
    });
  }

  function LoadKiwiSaver(kiwisaver_id = 0) {
    if (kiwisaver_id == "" || kiwisaver_id == null) {
      console.log("No KiwiSaver ID Loaded.");
      return;
    }

    if (client_id != 0) {
      var data = {
        kiwisaver_id: kiwisaver_id,
        action: "get_kiwisaver_profile"
      };

      $("#kiwisaver_profile_id").val(kiwisaver_id);
      $.ajax({
        data: data,
        type: "post",
        url: deal_api_url,
        success: function (data) {
          console.log(data);
          data = JSON.parse(data);
          console.log(data);
          SyncDeals(data.kiwisaver_deals);
          $("#buttons_div").show();
          $("#btn-save-deal_data").prop("disabled", false);
        },
        error: function (data) {
          $("#btn-save-deal_data").prop("disabled", false);
          console.log("Error", data);
          $('#myModal').modal('hide');
        }
      });
    } else {
      if (objectsHidden) {
        AddDealBtn();
        objectsHidden = false;
      }
    }
  }




  $("#client_data_nav").on("click", function () {
    $("#btn-save-client_data").show();
    $("#btn-save-deal_data").hide();
  });

  $("#deal_data_nav").on("click", function () {
    $("#btn-save-client_data").hide();
    $("#btn-save-deal_data").show();
  });

  function showData() {
    $("#data_tabs").slideDown();
    $("#buttons_div").slideDown();
    $("#nav_tabs").slideDown();
  }

  function hideData() {
    $("#data_tabs").slideUp();
    $("#buttons_div").slideUp();
    $("#nav_tabs").slideUp();
  }

  hideData();

  $("#clients_list").on("change", function () {

    if ($("#clients_list").val() != "") {
      showData();
    } else {
      hideData();
    }

  });

  $('#add_deal_btn').on("click", function () {
    AddDealBtn();
  });

  $('#deals_div').on("click", ".remove_deal", function () {
    var deal = $(this).data("dc");
    $("#deal_" + deal).html('<h2 style="color:red; text-align:center;"><i class="fas fa-file-invoice-dollar" ></i> Deal ' + deal + '</h2>');
  });

  function AddDealBtn() {
    dealsCount++;
    $('#deals_count').val(dealsCount);
    $('#deals_div').append(AddDeal(dealsCount));
    $('#deal_' + dealsCount).slideDown();
  }

  $(document).on("change",".count_switch", function(){
    let index = $(this).data("index");
    let checked = $(this). prop("checked");
    
    UpdateSwitch(index, checked);
  });

  //JQuery End
});

function UpdateSwitch(index, checked){
  if(checked){
    $("#count_" + index).val("Yes");
  }
  else{
    $("#count_" + index).val("No");
  }
}

//JavaScript Start
function ClearDeals() {
  document.getElementById("deals_div").innerHTML = "";
}

function TimestampToNZFormat(timestamp) {
  return timestamp.substring(6, 8) + "/" + timestamp.substring(4, 6) + "/" + timestamp.substring(0, 4);
}

function ToCurrency(n) {
  return Number(parseFloat(n).toFixed(2)).toLocaleString('en', {
    minimumFractionDigits: 2
  });
}


/**
 * @desc: will output a string that will act as the element that can be appended to the deals div
 * 
 */
function AddDeal(dealsCount) {
  var new_deal = '\
  <div class="row" id="deal_' + dealsCount + '">\
  <div class="row">\
    <div class="col text-center">\
      <h2><i class="fas fa-file-invoice-dollar"></i> Deal ' + dealsCount + ' <button type="button" class="btn btn-danger remove_deal" data-toggle="tooltip" title="Remove Deal" data-dc="' + dealsCount + '">X</button></h2>\
    </div>\
  </div>\
  <div class="row">\
  <div class="row">\
  <div class="col-sm-1">\
    <label>Insurer</label>\
    <div class="input-group">\
    <input class="form-control" autocomplete="off" readonly value="KiwiSaver" type="text" name="insurer_' + dealsCount + '" id="insurer_' + dealsCount + '" step="any" />\
    <input class="form-control" autocomplete="off" readonly value="" type="text" name="kiwisaver_deal_id_' + dealsCount + '" id="kiwisaver_deal_id_' + dealsCount + '" step="any" />\
    </div>\
  </div>\
  <div class="col-sm-2">\
    <label>Name</label>\
    <div class="input-group">\
      <input class="form-control" autocomplete="off" type="text" name="name_' + dealsCount + '" id="name_' + dealsCount + '" step="any" />\
    </div>\
  </div>\
  <div class="col-sm-2">\
    <label>Commission</label>\
    <div class="input-group">\
      <input class="form-control" autocomplete="off" type="text" name="commission_' + dealsCount + '" id="commission_' + dealsCount + '" step="any"/>\
    </div>\
  </div>\
  <div class="col-sm-2">\
    <label>GST</label>\
    <div class="input-group">\
      <input class="form-control" autocomplete="off" type="text" name="gst_' + dealsCount + '" id="gst_' + dealsCount + '" step="any" />\
    </div>\
  </div>\
  <div class="col-sm-2">\
    <label>Total Payment</label>\
    <div class="input-group">\
      <input class="form-control" autocomplete="off" type="text" name="balance_' + dealsCount + '" id="balance_' + dealsCount + '" step="any" />\
    </div>\
  </div>\
  <div class="col-sm-2">\
    <label>Date Applied</label>\
    <div class="input-group">\
      <input class="form-control datepicker" autocomplete="off" type="text" name="issue_date_' + dealsCount + '" id="issue_date_' + dealsCount + '" step="any" />\
    </div>\
  </div>\
  <div class="col-sm-1">\
    <label>Count</label>\
    <div class="input-group">\
      <label class="switch">\
        <input type="checkbox" data-index="' + dealsCount + '" class="count_switch" name="count_switch_' + dealsCount + '" id="count_switch_' + dealsCount + '" checked>\
        <span class="slider round"></span>\
      </label>\
      <input type="hidden" name="count_' + dealsCount + '" id="count_' + dealsCount + '" value="Yes">\
    </div>\
  </div>\
      </div>\
      </div>\
</div>';

  return new_deal;
}
//JavaScript End