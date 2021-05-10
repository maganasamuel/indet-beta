//APIs
var deal_api_url = "libs/api/deal_api.php";

//Tables
var table = null;

//JQuery Start
$(document).ready(function () {
  $('body').tooltip({
    selector: '[rel=tooltip]'
  });

  table = $("#issued_clients_table").DataTable();

  $("#btn-add_history").on("click", function () {
    $("#btn-save-save_history").data("action", "add");
  });

  $(document).on("click", ".btn-edit", function () {
    var deal_id = $(this).data("id");
    $("#deal_id").val(deal_id);
    LoadKiwiSaverDeal(deal_id);
    $('#myModal').modal('show');
  });

  $(document).on("click", ".btn-delete", function () {
    var deal_id = $(this).data("id");
    $.confirm({
      title: 'Confirm Action',
      content: 'Do you want to delete this KiwiSaver Deal?',
      type: 'red',
      typeAnimated: true,
      buttons: {
        confirm: {
          text: 'Confirm',
          btnClass: 'btn-red',
          action: function () {
            var data = {
                id: deal_id,
                action: "delete_kiwisaver_deal"
            }
            $.ajax({
              data: data,
              type: "post",
              url: deal_api_url,
              success: function (data) {
                $("#kiwisaver_" + deal_id).remove();
                table.row("#kiwisaver_" + deal_id).remove().draw(false);
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
    btn.prop("disabled", true);

    $("#save_deal_spinner").show();

    var dealData = $("#frmDeals").serializeObject();
    var deal_id = $("#deal_id").val();
    var data = {
      id: deal_id,
      deal_data: dealData,
      action: "update_kiwisaver_deal"
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

        let issue_date_display = TimestampToNZFormat(data.issue_date);

        var newData = {
          "0": data.name,
          "1": data.source_client,
          "2":  data.adviser_name,
          "3": data.leadgen_name,
          "4": "KiwiSaver",
          "5": {
            "display": ToCurrency(data.commission),
            "@data-order": data.commission
          },
          "6": {
            "display": ToCurrency(data.gst),
            "@data-order": data.gst
          },
          "7": {
            "display": ToCurrency(data.balance),
            "@data-order": data.balance
          },
          "8": {
            "display": issue_date_display,
            "@data-order": data.issue_date
          },
          "9": '<a class="btn-edit" data-toggle="tooltip" title="Edit KiwiSaver Deal" data-id="' + data.id + '"><span class="btn btn-warning glyphicon glyphicon-pencil"></span></a>',
          "10": '<a class="btn-delete" data-toggle="tooltip" title="Delete KiwiSaver Deal" data-id="' + data.id + '"><span class="btn btn-danger glyphicon glyphicon-trash"></span></td>'
        };

        table.row("#kiwisaver_" + data.id).data(newData).draw(false);

        $('#myModal').modal('hide');
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

  $('.datepicker').datepicker({
    dateFormat: 'dd/mm/yy'
  });

  function LoadKiwiSaverDeal(deal_id = 0) {
    if (deal_id != 0) {
      var data = {
        id: deal_id,
        action: "get_kiwisaver_deal"
      };

      $.ajax({
        data: data,
        type: "post",
        url: deal_api_url,
        success: function (data) {
          console.log(data);
          data = JSON.parse(data);
          console.log(data);
          
          $("#name").val(data.name);
          $("#commission").val(data.commission);
          $("#gst").val(data.gst);
          $("#balance").val(data.balance);
          $("#issue_date").val(TimestampToNZFormat(data.issue_date));
    
          let count = (data.count == "Yes");
          $("#count_switch").prop("checked", count);
          $("#count").val(data.count);

          $("#btn-save-deal_data").prop("disabled", false);
        },
        error: function (data) {
          $("#btn-save-deal_data").prop("disabled", false);
          console.log("Error", data);
          $('#myModal').modal('hide');
        }
      });
    }
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
    $("#count").val("Yes");
  }
  else{
    $("#count").val("No");
  }
}

//JavaScript Start
function ClearDeals() {
  document.getElementById("deals_div").innerHTML = "";
}

function ToCurrency(n) {
  return Number(parseFloat(n).toFixed(2)).toLocaleString('en', {
    minimumFractionDigits: 2
  });
}


//JavaScript End