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
    var client_id = $(this).data("id");
    $("#client_id").val();
    $("#select2-clients_list-container").parent().hide();
    $("#nav_tabs").show();
    $("#data_tabs").show();
    LoadClient(client_id);
    LoadDeals(client_id);
    $('#myModal').modal('show');
  });

  $(document).on("click", ".btn-edit_history", function () {
    $("#btn-save_history").data("action", "update");
    var history_id = $(this).data("id");
    $("#history_id").val(history_id);
    LoadNote(history_id);
    $('#followUpHistoryEditorModal').modal('show');
  });

  $(document).on("click", ".btn-delete_history", function () {
    var history_id = $(this).data("id");
    $.confirm({
      title: 'Confirm Action',
      content: 'Do you want to delete this follow-up history entry?',
      type: 'red',
      typeAnimated: true,
      buttons: {
        confirm: {
          text: 'Confirm',
          btnClass: 'btn-red',
          action: function () {
            var data = {
              history_id: history_id,
              action: "delete_follow_up_history"
            }
            $.ajax({
              data: data,
              type: "post",
              url: follow_up_history_api_url,
              success: function (data) {
                $("#history_" + history_id).remove();
                history_table.row("#history_" + history_id).remove().draw(false);
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

  $(document).on('click', '.btn-pdf', function () {
    var client_id = $(this).data("id");
    let adviser_id = $(this).data("adviser_id");

    let btn = $(this);
    btn.prop("disabled", true);

    $.ajax({
      type: "get",
      url: "view_issued_client_profile_pdf?output_file=true&id=" + client_id,
      beforeSend: function () {
        startLoading("view_pdf", "view_pdf_spinner_" + client_id, "view_pdf_icon_" + client_id);
      },
      success: function (link) {
        console.log(link);
        endLoading("view_pdf", "view_pdf_spinner_" + client_id, "view_pdf_icon_" + client_id);
        $("#send_pdf").data("id", client_id);
        $("#send_pdf").data("adviser_id", adviser_id);
        btn.prop("disabled", false);

        var htm = '<iframe src="' + link + '" style="width: 100%;height: 75%;"></iframe>';
        $('#pdfModal').modal('show');
        $('#pdfModalBody').html(htm);
      },
      error: function (data) {
        btn.prop("disabled", false);
        console.log("Error", data);
        $('#pdfModal').modal('hide');
      }
    });
  });

  $(document).on("click", "#send_pdf", function () {
    let client_id = $(this).data("id");
    let adviser_id = $(this).data("adviser_id");

    $('#pdfModal').modal('hide');
    $('#sendModal').modal('show');

    $.ajax({
      data: {
        adviser_id: adviser_id,
        action: "get_adviser"
      },
      type: "post",
      url: "libs/api/adviser_api.php",
      success: function (data) {
        data = JSON.parse(data);
        console.log(data);
        $('#send_client_id').val(client_id);
        $('#send_name').val(data.name);
        $("#send_email").val(data.email);
      },
      error: function (data) {
        console.log("Error", data);
      }
    });
  });

  $(document).on("click", "#btn-data-send", function () {
    let client_id = $("#send_client_id").val();
    let name = $("#send_name").val();
    let email = $("#send_email").val();
    startLoading("btn-data-send", "send_spinner");

    $.ajax({
      data: {
        client_ids: client_id,
        name: name,
        email: email,
        action: "create_send_issued_client_data_entry"
      },
      type: "post",
      url: "libs/api/client_api.php",
      success: function (data) {
        console.log(data);
        $.ajax({
          data: data,
          type: "get",
          url: "send_issued_client_data?id=" + data,
          success: function (data) {
            endLoading("btn-data-send", "send_spinner");
            console.log(data);
            alert("Email sent");
          },
          error: function (data) {
            console.log("Error", data);
          }
        });
      },
      error: function (data) {
        console.log("Error", data);
      }
    });
  });


  $(document).on('click', '#btn-save-deal_data', function () {

    var btn = $(this);
    var state = $("#btn-save-deal_data").data("action");

    btn.prop("disabled", true);

    $("#save_deal_spinner").show();

    var dealsData = $("#frmDeals").serializeObject();
    var client_id = $("#client_id").val();
    var leadgen = $("#leadgen").val();
    var assigned_to = $("#assigned_to").val();
    var data = {
      client_id: client_id,
      leadgen: leadgen,
      assigned_to: assigned_to,
      deals_data: dealsData,
      action: state + "_issued_policy"
    };

    console.log(data);

    $.ajax({
      data: data,
      type: "post",
      url: deal_api_url,
      success: function (data) {
        $("#save_deal_spinner").hide();
        console.log(data);
        data = JSON.parse(data);
        console.log(data);

        var newData = {
          "0": data.unique_client_names,
          "1": data.statuses,
          "2": {
            "display": "$" + ToCurrency(data.issued),
            "@data-order": data.issued
          },
          "3": {
            "display": TimestampToNZFormat(data.date_issued),
            "@data-order": data.date_issued
          },
          "4": data.adviser,
          "5": data.unique_policy_numbers,
          "6": '<a class="btn-pdf" id="btn-pdf-' + data.client_id + '" ata-toggle="tooltip" title="View Issued Client Profile and Deals Data PDF" data-adviser_id=' + data.assigned_to + '  data-id="' + data.client_id + '"><i id="view_pdf_spinner_' + data.client_id + '" class="fas fa-spinner fa-spin" style="display:none;"></i> <span class="btn btn-primary glyphicon glyphicon-file" id="view_pdf_icon_' + data.client_id + '"></span></a>',
          "7": '<a class="btn-view" data-toggle="tooltip" title="View Issued Client Profile" data-id="' + data.client_id + '"><span class="btn btn-primary glyphicon glyphicon-search"></span></a></td>',
          "8": '<a class="btn-history" data-toggle="tooltip" title="View Follow-Up History" data-id="' + data.client_id + '"><span class="btn btn-info glyphicon glyphicon-book"></span></a>',
          "9": '<a class="btn-edit" data-toggle="tooltip" title="Edit Issued Client Profile" data-id="' + data.client_id + '"><span class="btn btn-warning glyphicon glyphicon-pencil"></span></a>',
          "10": '<a class="unissue_client" data-toggle="tooltip" title="Unissue Client" data-id="' + data.client_id + '"><span class="btn btn-danger glyphicon glyphicon-refresh"></span></td>'
        };

        if (state == "add") { //if user added a new record
          tbl_r = table.row.add(newData).node().id = "issued_client_" + data.client_id;
          table.row("#issued_client_" + data.client_id).data(newData).draw(false);
        } else { //if user updated an existing record
          table.row("#issued_client_" + data.client_id).data(newData).draw(false);
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
          action: "update_issued_client_profile",
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
            let row = table.row("#issued_client_" + data.id);
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
              action: function () { }

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
      $('#clawback_status_' + dealCount).trigger("change");
      $('#status_date_' + dealCount).slideUp();
    } else {
      $('#status_date_' + dealCount).prop('required', true);
      $('#date_issued_' + dealCount).prop('required', false);
      $('#issued_api_' + dealCount).prop('required', false);
      $('#status_date_' + dealCount).slideDown();
      $('#issued_div_' + dealCount).slideUp();
      $('#issued_div_extra_' + dealCount).slideUp();
      $('#clawback_div_' + dealCount).slideUp();
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

  $(document).on("change", ".refund_status_2_options", function () {
    var status = $(this).val();
    console.log(status);
    var dealCount = $(this).data("dc");
    console.log("Selected: " + status + "/n Deal Count: " + dealCount);
    if (status == "Yes") {
      $('#refund_amount_2_' + dealCount).slideDown();
    } else {
      $('#refund_amount_2_' + dealCount).val("").slideUp();
    }
  });

  function TimestampToNZFormat(timestamp) {
    if (timestamp == "")
      return "";

    return timestamp.substring(6, 8) + "/" + timestamp.substring(4, 6) + "/" + timestamp.substring(0, 4);
  }

  function SyncDeals(deals) {
    var syncCtr = 0;
    dealsCount = 0;
    $('#deals_div').html("");
    //Create new fields
    deals.forEach(function (deal) {
      AddDealBtn();
    });

    deals.forEach(function (deal) {
      syncCtr++;
      $('#company_' + syncCtr).val(deal.company);
      if (deal.company == "Others") {
        $('#specific_company_' + syncCtr).slideDown();
        $('#specific_company_' + syncCtr).val(deal.specific_company);
      }
      $('#status_' + syncCtr).val(deal.status);
      if (deal.status != "Pending" && deal.status != "Issued") {
        $('#status_date_' + syncCtr).slideDown();
        if (deal.status_date != undefined)
          $('#status_date_' + syncCtr).val(deal.status_date.substr(6) + "/" + deal.status_date.substr(4, 2) + "/" + deal.status_date.substr(0, 4));
      }
      $('#policy_number_' + syncCtr).val(deal.policy_number);
      $('#original_api_' + syncCtr).val(deal.original_api);
      $('#submission_date_' + syncCtr).val(deal.submission_date.substr(6) + "/" + deal.submission_date.substr(4, 2) + "/" + deal.submission_date.substr(0, 4));
      $('#life_insured_' + syncCtr).val(deal.life_insured);
      if (deal.status == "Issued") {
        //Andre Replacement Business
        $('#replacement_business_' + syncCtr).val(deal.replacement_business);
        $('#date_issued_' + syncCtr).val(deal.date_issued.substr(6) + "/" + deal.date_issued.substr(4, 2) + "/" + deal.date_issued.substr(0, 4));
        $('#issued_api_' + syncCtr).val(deal.issued_api);
        $('#compliance_status_' + syncCtr).val(deal.compliance_status);
        deal.notes = deal.notes.replace(/<br>/g, "\r\n");
        $('#notes_' + syncCtr).val(deal.notes);
        //Set a value for the commission status if not set
        if (typeof deal.commission_status === 'undefined') {
          deal.commission_status = "Not Paid";
        }

        $('#commission_status_' + syncCtr).val(deal.commission_status);

        //Set a value for the commission status if not set
        if (typeof deal.audit_status === 'undefined') {
          deal.audit_status = "Pending";
        }


        $('#audit_status_' + syncCtr).val(deal.audit_status);
        $('#record_keeping_' + syncCtr).val(deal.record_keeping);

        //Set a value for the email if not set
        if (typeof deal.email === 'undefined') {
          deal.email = "";
        }

        //Set a value for the email if not set
        if (typeof deal.secondary_email === 'undefined') {
          deal.secondary_email = "";
        }

        $('#email_' + syncCtr).val(deal.email);
        $('#secondary_email_' + syncCtr).val(deal.secondary_email);

        //Set a value for the email if not set
        if (typeof deal.birthday !== 'undefined') {
          if (deal.birthday != "")
            $('#birthday_' + syncCtr).val(deal.birthday.substr(6) + "/" + deal.birthday.substr(4, 2) + "/" + deal.birthday.substr(0, 4));
        } else {
          deal.birthday = "";
        }

        //Set a value for the email if not set
        if (typeof deal.secondary_birthday !== 'undefined') {
          if (deal.secondary_birthday != "")
            $('#secondary_birthday_' + syncCtr).val(deal.secondary_birthday.substr(6) + "/" + deal.secondary_birthday.substr(4, 2) + "/" + deal.secondary_birthday.substr(0, 4));
        } else {
          deal.secondary_birthday = "";
        }

        if (deal.secondary_birthday != "" || deal.secondary_email != "") {
          console.log(deal.secondary_birthday + ":" + deal.secondary_email);
          $('#collapse_extra_secondary_' + syncCtr).slideDown();
        }

        $('#submission_date_' + syncCtr).val(deal.submission_date.substr(6) + "/" + deal.submission_date.substr(4, 2) + "/" + deal.submission_date.substr(0, 4));


        $('#issued_div_' + syncCtr).slideDown();

        $('#issued_div_extra_' + syncCtr).slideDown();

        if (deal.clawback_status == undefined)
          deal.clawback_status = "None";

        $('#clawback_status_' + syncCtr).val(deal.clawback_status);
      }
      if (deal.clawback_status != "None" && deal.clawback_status !== undefined) {
        $('#clawback_date_' + syncCtr).val(deal.clawback_date.substr(6) + "/" + deal.clawback_date.substr(4, 2) + "/" + deal.clawback_date.substr(0, 4));
        $('#clawback_api_' + syncCtr).val(deal.clawback_api);
        deal.clawback_notes = deal.clawback_notes.replace(/<br>/g, "\r\n");
        $('#clawback_notes_' + syncCtr).val(deal.clawback_notes);
        console.log(deal.refund_status);
        if (deal.refund_status == null)
          deal.refund_status = "No";

        if (deal.refund_status_2 == null) {
          // deal.refund_status = "No";
          deal.refund_amount_2 = "";
        }

        $('#refund_status_' + syncCtr).val(deal.refund_status);

        $('#refund_status_2_' + syncCtr).val(deal.refund_status_2);

        $('#refund_amount_2_' + syncCtr).val(deal.refund_amount_2);

        if (deal.refund_status_2 == "Yes") {
          $('#refund_amount_2_' + syncCtr).slideDown();
        }
        else {
          $('#refund_amount_2_' + syncCtr).slideUp();
        }

        if (deal.refund_notes_2 == null)
          deal.refund_notes_2 = "";

        $('#refund_notes_' + syncCtr).val(deal.refund_notes);
        $('#refund_notes_2_' + syncCtr).val(deal.refund_notes_2);
        $('#clawback_div_' + syncCtr).slideDown();
      }
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
    LoadDeals(client_id);

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

  function LoadDeals(client_id = 0) {
    if (client_id == "" || client_id == null) {
      console.log("No Client ID Loaded.");
      return;
    }

    if (client_id != 0) {
      var data = {
        client_id: client_id,
        action: "get_submission_profile"
      };

      $.ajax({
        data: data,
        type: "post",
        url: deal_api_url,
        success: function (data) {
          console.log(data);
          data = JSON.parse(data);
          deals = JSON.parse(data.deals);
          console.log(data);

          SyncDeals(deals);
          console.log(SyncDeals(deals));
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

  function LoadNote(history_id = 0) {
    if (history_id != 0) {
      var data = {
        history_id: history_id,
        action: "get_follow_up_history"
      };

      $.ajax({
        data: data,
        type: "post",
        url: follow_up_history_api_url,
        success: function (data) {
          console.log(data);
          data = JSON.parse(data);
          console.log(data);

          $("#follow_up_history_notes").val(data.notes);
        },
        error: function (data) {
          console.log("Error", data);
        }
      });
    }
  }

  function ViewDeals(client_id = 0) {
    if (client_id != 0) {
      var data = {
        client_id: client_id,
        action: "get_issued_client_profile"
      };

      $.ajax({
        data: data,
        type: "post",
        url: deal_api_url,
        success: function (data) {
          console.log(data);
          data = JSON.parse(data);
          deals = JSON.parse(data.deals_data);
          console.log(deals);

          $("#total_api_header").html("Total API:" + data.issued);

          deals_table.rows().remove().draw(false);
          var deal_index = 1;

          deals.forEach(function (deal) {
            var unique_client_names = (deal.life_insured == "") ? data.client_name : data.client_name + ", " + deal.life_insured;
            var arrear_status = (deal.arrear_status !== undefined) ? deal.arrear_status : "N/A";
            console.log("Original API:" + deal.original_api);

            var newData = {
              "0": deal.policy_number,
              "1": deal.company,
              "2": deal.status,
              "3": arrear_status,
              "4": unique_client_names,
              "5": {
                "display": TimestampToNZFormat(deal.submission_date),
                "@data-order": deal.submission_date
              },
              "6": {
                "display": "$" + ToCurrency(deal.original_api),
                "@data-order": deal.original_api
              },
              "7": {
                "display": TimestampToNZFormat(deal.date_issued),
                "@data-order": deal.date_issued
              },
              "8": {
                "display": "$" + ToCurrency(deal.issued_api),
                "@data-order": deal.issued_api
              },
              "9": deal.notes
            };

            tbl_r = deals_table.row.add(newData).node().id = "deal" + deal_index;
            deals_table.row("#deal" + deal_index).data(newData).draw(false);

            deal_index++;
          });

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

  function ViewHistory(client_id = 0) {
    if (client_id != 0) {
      var data = {
        client_id: client_id,
        action: "get_all_follow_up_history"
      };
      console.log(client_id);
      $.ajax({
        data: data,
        type: "post",
        url: follow_up_history_api_url,
        success: function (data) {
          console.log(data);
          var history = JSON.parse(data);
          console.log(history);

          history_table.rows().remove().draw(false);

          history.forEach(function (row) {
            var newData = null;
            if (current_user_id == row.user_id) {
              newData = {
                "0": row.username,
                "1": row.notes,
                "2": {
                  "display": row.formatted_timestamp,
                  "@data-order": row.timestamp
                },
                "3": '<a class="btn-edit_history" data-id="' + row.id + '"><span class="btn btn-warning glyphicon glyphicon-pencil"></span></i></a>',
                "4": '<a class="btn-delete_history" data-id="' + row.id + '"><span class="btn btn-danger glyphicon glyphicon-trash"></span></i></a>'
              };
            } else {
              newData = {
                "0": row.username,
                "1": row.notes,
                "2": {
                  "display": row.formatted_timestamp,
                  "@data-order": row.timestamp
                },
                "3": '',
                "4": "<a class='btn btn-link' style='cursor: not-allowed;'><i class='fas fa-ban text-danger'></i></a>"
              };
            }

            tbl_r = history_table.row.add(newData).node().id = "history_" + row.id;
            history_table.row("#history_" + row.id).data(newData).draw(false);
          });

          $("#btn-add_history").data("id", client_id);
        },
        error: function (data) {
          $("#btn-save-deal_data").prop("disabled", false);
          console.log("Error", data);
        }
      });
    } else {
      if (objectsHidden) {
        AddDealBtn();
        objectsHidden = false;
      }
    }
  }

  $('#deals_div').on("change", ".company_options", function () {
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

  $('#deals_div').on("keyup change", ".api", function () {
    var api = $(this).val();
    api = api.replace(/[^0-9.]/g, "");
    console.log(api);
    $(this).val(api);
  });

  $('#deals_div').on("click", ".remove_deal", function () {
    var deal = $(this).data("dc");
    $("#deal_" + deal).html('<h2 style="color:red; text-align:center;"><i class="fas fa-file-invoice-dollar" ></i> Deal ' + deal + '</h2>');
  });

  $('#add_deal_btn').on("click", function () {
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
      value: 'Accuro',
      text: 'Accuro'
    }));
    $('#company_' + dealsCount).append($('<option>', {
      value: 'Cigna',
      text: 'Cigna'
    }));
    $('#company_' + dealsCount).append($('<option>', {
      value: 'Southern Cross',
      text: 'Southern Cross'
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

    //Add Replacement Business Options
    $('#replacement_business_' + dealsCount).append($('<option>', {
      value: 1,
      text: 'Yes'
    }));

    $('#replacement_business_' + dealsCount).append($('<option>', {
      value: 0,
      text: 'No'
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


    //Add BCTI Status Options
    $('#refund_status_' + dealsCount).append($('<option>', {
      value: 'Yes',
      text: 'Yes'
    }));
    $('#refund_status_' + dealsCount).append($('<option>', {
      selected: true,
      value: 'No',
      text: 'No'
    }));

    //Add Refund Status Options
    $('#refund_status_2_' + dealsCount).append($('<option>', {
      value: 'Yes',
      text: 'Yes'
    }));
    $('#refund_status_2_' + dealsCount).append($('<option>', {
      selected: true,
      value: 'No',
      text: 'No'
    }));

    //Add Audit Status Options
    $('#audit_status_' + dealsCount).append($('<option>', {
      value: 'For Checking',
      text: 'For Checking'
    }));

    $('#audit_status_' + dealsCount).append($('<option>', {
      value: 'Passed',
      text: 'Passed'
    }));

    $('#audit_status_' + dealsCount).append($('<option>', {
      value: 'Failed',
      text: 'Failed'
    }));

    $('#audit_status_' + dealsCount).append($('<option>', {
      value: 'Exempted',
      text: 'Exempted'
    }));

    // Add Record Keeping Options
    ['For Checking', 'Complete', 'Incomplete', 'Exempted'].forEach((option) => {
      $('#record_keeping_' + dealsCount).append($('<option>', { value: option, text: option, }));
    });
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

  //JQuery End
});

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
        <div class="row"  id="deal_' + dealsCount + '" style="display:none;">\
            <div class="row">\
              <div class="col text-center">\
                <h2><i class="fas fa-file-invoice-dollar"></i> Deal ' + dealsCount + ' <button type="button" class="btn btn-danger remove_deal" data-toggle="tooltip" title="Remove Deal"  data-dc="' + dealsCount + '">X</button></h2>\
              </div>\
            </div>\
            <br>\
            <div class="row">\
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
                      <input class="form-control datepicker_dynamic" autocomplete="off"  type="text" id="status_date_' + dealsCount + '" name="status_date_' + dealsCount + '"  style="display:none;" />\
                    </div>\
                  </div>\
                </div>\
              </div>\
              <div class="col-sm-3" style="display:none;" id="issued_div_' + dealsCount + '">\
                <div class="row">\
                  <div class="col-sm-4">\
                    Date Issued\
                  </div>\
                  <div class="col-sm-8">\
                    <div class="input-group">\
                      <span class="input-group-addon"><i class="fa fa-calendar" aria-hidden="true"></i></span>\
                      <input class="form-control datepicker_dynamic " autocomplete="off" type="text" id="date_issued_' + dealsCount + '" name="date_issued_' + dealsCount + '">\
                    </div>\
                  </div>\
                </div>\
                <br>\
                <div class="row">\
                  <div class="col-sm-4">\
                    Issued API\
                  </div>\
                  <div class="col-sm-8">\
                  <div class="input-group">\
                    <span class="input-group-addon"><i class="fas fa-usd"></i></span>\
                    <input class="form-control issued_api api" autocomplete="off" type="text" name="issued_api_' + dealsCount + '" id="issued_api_' + dealsCount + '" step="any">\
                  </div>\
                  </div>\
                </div>\
                <br>\
                <div class="row">\
                  <div class="col-sm-4">\
                    Record Keeping \
                  </div>\
                  <div class="col-sm-8">\
                    <div class="input-group">\
                      <span class="input-group-addon"><i class="fas fa-question-circle"></i></span>\
                      <select data-dc="' + dealsCount + '" class="form-control record_keeping_options" name="record_keeping_' + dealsCount + '" id="record_keeping_' + dealsCount + '"></select>\
                    </div>\
                  </div>\
                </div>\
                <br>\
                <div class="row">\
                  <div class="col-sm-4">\
                    Compliance Check Admin \
                  </div>\
                  <div class="col-sm-8">\
                    <div class="input-group">\
                      <span class="input-group-addon"><i class="fas fa-question-circle"></i></span>\
                      <select data-dc="' + dealsCount + '" class="form-control compliance_status_options" name="compliance_status_' + dealsCount + '" id="compliance_status_' + dealsCount + '">\
                      </select>\
                    </div>\
                  </div>\
                </div>\
                <br>\
                <div class="row">\
                  <div class="col-sm-4">\
                    Compliance Check CO \
                  </div>\
                  <div class="col-sm-8">\
                    <div class="input-group">\
                      <span class="input-group-addon"><i class="fas fa-question-circle"></i></span>\
                      <select data-dc="' + dealsCount + '" class="form-control audit_status_options" name="audit_status_' + dealsCount + '" id="audit_status_' + dealsCount + '">\
                      </select>\
                    </div>\
                  </div>\
                </div>\
                <br>\
                <div class="row">\
                  <div class="col-sm-4">\
                    Commission Status \
                  </div>\
                  <div class="col-sm-8">\
                    <div class="input-group">\
                      <span class="input-group-addon"><i class="fas fa-question-circle"></i></span>\
                      <select data-dc="' + dealsCount + '" class="form-control commission_status_options" name="commission_status_' + dealsCount + '" id="commission_status_' + dealsCount + '">\
                      </select>\
                    </div>\
                  </div>\
                </div>\
                <br>\
                <div class="row">\
                  <div class="col-sm-4">\
                    Issue Notes\
                  </div>\
                  <div class="col-sm-8">\
                    <div class="input-group">\
                      <span class="input-group-addon"><i class="fas fa-user"></i></span>\
                      <textarea class="form-control" name="notes_' + dealsCount + '" id="notes_' + dealsCount + '"></textarea>\
                    </div>\
                  </div>\
                </div>\
                <br>';

  if ($("#btn-save-deal_data").data("action") == "update") {
    new_deal += '<div class="row hide-non-admin">\
                  <div class="col-sm-4">\
                    Clawback Status\
                  </div>\
                  <div class="col-sm-8">\
                    <div class="input-group">\
                      <span class="input-group-addon"><i class="fas fa-question-circle status_options"></i></span>\
                      <select data-dc="' + dealsCount + '" class="form-control clawback_options" name="clawback_status_' + dealsCount + '" id="clawback_status_' + dealsCount + '">\
                      </select>\
                    </div>\
                  </div>\
                </div>';
  }

  new_deal += '</div>';

  if ($("#btn-save-deal_data").data("action") == "update") {
    //If editing
    new_deal += '<div class="col-sm-3" style="display:none;" id="clawback_div_' + dealsCount + '">\
                <div class="row">\
                  <div class="col-sm-4">\
                    Clawback Date\
                  </div>\
                  <div class="col-sm-8">\
                    <div class="input-group">\
                      <span class="input-group-addon"><i class="fa fa-calendar" aria-hidden="true"></i></span>\
                      <input class="form-control datepicker_dynamic " autocomplete="off" type="text" id="clawback_date_' + dealsCount + '" name="clawback_date_' + dealsCount + '" >\
                    </div>\
                  </div>\
                </div>\
                <br>\
                <div class="row">\
                  <div class="col-sm-4">\
                  Clawback Amount \
                  </div>\
                  <div class="col-sm-8">\
                    <div class="input-group">\
                      <span class="input-group-addon"><i class="fas fa-usd"></i></span>\
                      <input class="form-control cancellation_api api" autocomplete="off" type="text" name="clawback_api_' + dealsCount + '" id="clawback_api_' + dealsCount + '" step="any" >\
                    </div>\
                  </div>\
                </div>\
                <br>\
                <div class="row">\
                  <div class="col-sm-4">\
                   Clawback Notes\
                  </div>\
                  <div class="col-sm-8">\
                    <div class="input-group">\
                      <span class="input-group-addon"><i class="fas fa-info"></i></span>\
                      <textarea class="form-control" name="clawback_notes_' + dealsCount + '" id="clawback_notes_' + dealsCount + '"></textarea>\
                    </div>\
                  </div>\
                </div>\
                <br>\
                <div class="row">\
                  <div class="col-sm-4">\
                    BCTI Status\
                  </div>\
                  <div class="col-sm-8">\
                    <div class="input-group">\
                      <span class="input-group-addon"><i class="fas fa-question-circle status_options"></i></span>\
                      <select data-dc="' + dealsCount + '" class="form-control refund_status_options" name="refund_status_' + dealsCount + '" id="refund_status_' + dealsCount + '">\
                      </select>\
                    </div>\
                  </div>\
                </div>\
                <br>\
                <div class="row">\
                  <div class="col-sm-4">\
                  BCTI Notes\
                  </div>\
                  <div class="col-sm-8">\
                    <div class="input-group">\
                      <span class="input-group-addon"><i class="fas fa-info"></i></span>\
                      <textarea class="form-control" name="refund_notes_' + dealsCount + '" id="refund_notes_' + dealsCount + '"></textarea>\
                    </div>\
                  </div>\
                </div>\
                <br>\
                <div class="row">\
                  <div class="col-sm-4">\
                  Refund Status\
                  </div>\
                  <div class="col-sm-8">\
                    <div class="input-group">\
                      <span class="input-group-addon"><i class="fas fa-question-circle status_options"></i></span>\
                      <select data-dc="' + dealsCount + '" class="form-control refund_status_2_options" name="refund_status_2_' + dealsCount + '" id="refund_status_2_' + dealsCount + '">\
                      </select>\
                      <input class="form-control api" placeholder="Refund Amount" type="text" id="refund_amount_2_' + dealsCount + '" name="refund_amount_2_' + dealsCount + '"  style="display:none;" />\
                    </div>\
                  </div>\
                </div>\
                <br>\
                <div class="row">\
                  <div class="col-sm-4">\
                  Refund Notes\
                  </div>\
                  <div class="col-sm-8">\
                    <div class="input-group">\
                      <span class="input-group-addon"><i class="fas fa-info"></i></span>\
                      <textarea class="form-control" name="refund_notes_2_' + dealsCount + '" id="refund_notes_2_' + dealsCount + '"></textarea>\
                    </div>\
                  </div>\
                </div>\
                <br>\
              </div>';
  }

  new_deal += '<div class="col-sm-3" style="display:none;" id="issued_div_extra_' + dealsCount + '">\
                <div class="row">\
                  <div class="col-sm-4">\
                    Email Address\
                  </div>\
                  <div class="col-sm-8">\
                    <div class="input-group">\
                      <span class="input-group-addon"><i class="fa fa-envelope" aria-hidden="true"></i></span>\
                      <input class="form-control" autocomplete="off" type="email" id="email_' + dealsCount + '" name="email_' + dealsCount + '">\
                    </div>\
                  </div>\
                </div>\
                <br>\
                <div class="row">\
                  <div class="col-sm-4">\
                    Birthday\
                  </div>\
                  <div class="col-sm-8">\
                    <div class="input-group">\
                      <span class="input-group-addon"><i class="fas fa-user"></i></span>\
                      <input class="form-control datepicker_dynamic " autocomplete="off" type="text" name="birthday_' + dealsCount + '" id="birthday_' + dealsCount + '">\
                    </div>\
                  </div>\
                </div>\
                <br>\
                <div class="row">\
                  <div class="col-sm-4">\
                   Replacement Business\
                  </div>\
                  <div class="col-sm-8">\
                    <div class="input-group">\
                      <span class="input-group-addon"><i class="fa fa-briefcase" aria-hidden="true"></i></span>\
                      <select data-dc="' + dealsCount + '" class="form-control replacement_business_options" name="replacement_business_' + dealsCount + '" id="replacement_business_' + dealsCount + '">\
                      </select>\
                    </div>\
                  </div>\
                </div>\
                <br>\
                <div class="row">\
                  <div class="col">\
                    <div class="input-group">\
                      <button class="btn btn-primary secondary_information_toggle" autocomplete="off" type="button" data-toggle="collapse" data-target="#collapse_extra_secondary_' + dealsCount + '" aria-expanded="true" aria-controls="collapse_extra_secondary_' + dealsCount + '">Show Life Insured Inputs</button>\
                    </div>\
                  </div>\
                </div>\
                <br>\
                <div id="collapse_extra_secondary_' + dealsCount + '"  class="collapse" aria-expanded="true">\
                  <div class="row">\
                    <div class="col-sm-4">\
                      Email Address\
                    </div>\
                    <div class="col-sm-8">\
                      <div class="input-group">\
                        <span class="input-group-addon"><i class="fa fa-envelope" aria-hidden="true"></i></span>\
                        <input class="form-control" autocomplete="off" type="email" id="secondary_email_' + dealsCount + '" name="secondary_email_' + dealsCount + '">\
                      </div>\
                    </div>\
                  </div>\
                  <br>\
                  <div class="row">\
                    <div class="col-sm-4">\
                    Birthday\
                    </div>\
                    <div class="col-sm-8">\
                      <div class="input-group">\
                        <span class="input-group-addon"><i class="fas fa-user"></i></span>\
                        <input class="form-control datepicker_dynamic " autocomplete="off" type="text" name="secondary_birthday_' + dealsCount + '" id="secondary_birthday_' + dealsCount + '">\
                      </div>\
                    </div>\
                  </div>\
                </div>\
                <br>\
              </div>\
            </div>\
          </div>\
        ';
  return new_deal;
}
//JavaScript End