var table = null;
var deals_table = null;

//JQuery Start
$(document).ready(function () {

    $('body').tooltip({
        selector: '[rel=tooltip]'
    });

    table = $("#me").DataTable();
    deals_table = $("#client_deals_table").DataTable();

    $(document).on('focus', ".datepicker_dynamic", function () {
        $(this).datepicker({
            dateFormat: 'dd/mm/yy'
        });
    });

    $("#myModal").on("hidden.bs.modal", function () {
        $("#clients_list").val("").trigger("change");
    });

    $(".leadgen").on('change', function () {
        $("#leadgen").val($(this).val());
    });

    $("#lead_by").on('change', function () {
        $(".leadgen").slideUp();
        var lead_by = $(this).val();
        var lg_field = $("#leadgen");

        if (lead_by == "Self-Generated") {
            lg_field.val(0);
        } else if (lead_by == "Telemarketer") {
            $("#leadgen_telemarketer").val("0");
            $("#leadgen_telemarketer").slideDown();
        } else if (lead_by == "Face-to-Face Marketer") {
            $("#leadgen_bdm").val("0");
            $("#leadgen_bdm").slideDown();
        }
    });

    $("#btn-issue_client").on("click", function () {
        var client_id = $(this).data("id");

        $.confirm({
            title: 'Confirm Action',
            content: 'Are you sure that you want to issue this client?',
            type: 'red',
            typeAnimated: true,
            buttons: {
                confirm: {
                    text: 'Confirm Issuing Client',
                    btnClass: 'btn-green',
                    action: function () {
                        window.location = "issued_client_profiles?issue_client=" + client_id;
                    }
                },
                cancel: {
                    text: 'Cancel',
                    btnClass: 'btn-red',
                    action: function () {}

                }
            }
        });
    });

    $("#btn-add").on("click", function () {
        $("#btn-save-deal_data").data("action", "add");
        $('#client_data_nav').find('a').trigger('click');
        $("#select2-clients_list-container").parent().show();
        hideData();
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
        //$("#client_id").hide();
    });

    $(document).on("click", ".btn-view", function () {
        $('#client_data_nav').find('a').trigger('click');
        var client_id = $(this).data("id");
        $('#clientDealsModal').modal('show');

        ViewDeals(client_id);
        //$("#client_id").hide();
    });

    $(document).on("click", ".btn-delete", function () {
        var client_id = $(this).data("id");
        var client_name = $(this).data("name");
        $.confirm({
            title: 'Confirm Action',
            content: 'Do you want to delete the submission profile for this client?',
            type: 'red',
            typeAnimated: true,
            buttons: {
                confirm: {
                    text: 'Confirm',
                    btnClass: 'btn-red',
                    action: function () {
                        var data = {
                            client_id: client_id,
                            action: "delete_submission"
                        }
                        $.ajax({
                            data: data,
                            type: "post",
                            url: deal_api_url,
                            success: function (data) {
                                $("#client" + client_id).remove();
                                table.row("#client" + client_id).remove().draw(false);

                                // Set the value, creating a new option if necessary
                                if ($('#clients_list').find("option[value='" + client_id + "']").length) {
                                    $('#clients_list').val(client_id).trigger('change');
                                } else {
                                    // Create a DOM Option
                                    var newOption = new Option(client_name, client_id, false, false);
                                    // Append it to the select
                                    $('#clients_list').append(newOption);
                                }

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

    var client_api_url = "crud/clients-crud.php";
    var deal_api_url = "libs/api/deal_api.php";

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

        $("#save_deal_spinner").show();
        btn.prop("disabled", true);

        var dealsData = $("#frmDeals").serializeObject();
        var client_id = $("#client_id").val();
        var data = {
            client_id: client_id,
            deals_data: dealsData,
            action: state + "_submission"
        };

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
                    "1": data.unique_policy_numbers,
                    "2": data.unique_insurers,
                    "3": data.adviser_name,
                    "4": {
                        "display": data.timestamp,
                        "@data-order": data.timestamp_order
                    },
                    "5": {
                        "display": "$" + data.total_api,
                        "@data-order": data.total_api
                    },
                    "6": data.deals_count,
                    "7": '<a href="show_deal?submission_id=' + data.id + '"><span class="btn btn-primary glyphicon glyphicon-search"></span></a>',
                    "8": '<a class="btn-edit" data-id="' + data.client_id + '"><span class="btn btn-warning glyphicon glyphicon-pencil"></span></a>',
                    "9": '<a class="btn-delete" data-id="' + data.client_id + '" data-name="' + data.client_name + '"><span class="btn btn-danger glyphicon glyphicon-trash"></span></a>'
                };

                if (state == "add") { //if user added a new record
                    tbl_r = table.row.add(newData).node().id = "client" + data.client_id;
                    table.row("#client" + data.client_id).data(newData).draw(false);
                } else { //if user updated an existing record
                    table.row("#client" + data.client_id).data(newData).draw(false);
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

                $("#save_client_spinner").hide();
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
                $('#status_date_' + syncCtr).val(deal.status_date.substr(6) + "/" + deal.status_date.substr(4, 2) + "/" + deal.status_date.substr(0, 4));
            }
            $('#policy_number_' + syncCtr).val(deal.policy_number);
            $('#original_api_' + syncCtr).val(deal.original_api);
            $('#submission_date_' + syncCtr).val(deal.submission_date.substr(6) + "/" + deal.submission_date.substr(4, 2) + "/" + deal.submission_date.substr(0, 4));
            $('#life_insured_' + syncCtr).val(deal.life_insured);
            if (deal.status == "Issued") {
                $('#date_issued_' + syncCtr).val(deal.date_issued.substr(6) + "/" + deal.date_issued.substr(4, 2) + "/" + deal.date_issued.substr(0, 4));
                $('#issued_api_' + syncCtr).val(deal.issued_api);
                $('#compliance_status_' + syncCtr).val(deal.compliance_status);
                deal.notes = deal.notes.replace(/<br>/g, "\r\n");
                $('#notes_' + syncCtr).val(deal.notes);
                console.log(deal.notes);
                //Set a value for the commission status if not set
                if (typeof deal.commission_status === 'undefined') {
                    deal.commission_status = "Not Paid";
                }

                $('#commission_status_' + syncCtr).val(deal.commission_status);

                //Set a value for the commission status if not set
                if (typeof deal.audit_status === 'undefined') {
                    deal.audit_status = "Pending";
                }

                console.log(deal.company + ":" + deal.audit_status);

                $('#audit_status_' + syncCtr).val(deal.audit_status);

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
                if (deal.refund_status == null)
                    deal.refund_status = "No";

                $('#refund_status_' + syncCtr).val(deal.refund_status);

                $('#refund_notes_' + syncCtr).val(deal.refund_notes);
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


    var deals = null;
    var objectsHidden = true;
    var dealsCount = 0;

    $('#clients_list').on("change", function () {
        var client_id = $(this).val();
        $("#client_id").val(client_id);

        LoadClient(client_id);
        LoadDeals();
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

    function ViewDeals(client_id = 0) {
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
                    console.log(deals);

                    $("#total_api_header").html("Total API:" + data.total_api);

                    deals_table.rows().remove().draw(false);
                    var deal_index = 1;

                    deals.forEach(function (deal) {
                        var unique_client_names = (deal.life_insured == "") ? data.client_name : data.client_name + ", " + deal.life_insured;
                        console.log("Original API:" + deal.original_api);
                        var newData = {
                            "0": unique_client_names,
                            "1": {
                                "display": TimestampToNZFormat(deal.submission_date),
                                "@data-order": deal.submission_date
                            },
                            "2": deal.company,
                            "3": deal.policy_number,
                            "4": {
                                "display": "$" + ToCurrency(deal.original_api),
                                "@data-order": deal.original_api
                            },
                            "5": deal.status
                        };

                        tbl_r = deals_table.row.add(newData).node().id = "deal" + deal_index;
                        deals_table.row("#deal" + deal_index).data(newData).draw(false);

                        deal_index++;
                    });

                    $("#btn-issue_client").data("id", client_id);
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

    $('#deals_div').on("change", ".status_options", function () {
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

function AddDeal(dealsCount) {

    var new_deal = '\
      <div class="row"  id="deal_' + dealsCount + '" style="display:none;">\
          <div class="row">\
            <div class="col text-center">\
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
                    <input class="form-control datepicker_dynamic" autocomplete="off" type="text" id="submission_date_' + dealsCount + '" name="submission_date_' + dealsCount + '">\
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
//JavaScript End