$(document).ready(function () {
    $('body').tooltip({
        selector: '[rel=tooltip]'
    });


    var url = "crud/clients-crud.php";

    //display modal form for model editing
    $(document).on("click", ".open-modal", function (e) {
        e.preventDefault();
        var mat_id = $(this).val();
        $("#status_div").show();

        console.log(mat_id);
        $.get(url + '/?id=' + mat_id, function (data) {
            console.log(data);
            $('#client_id').val(data.id);
            $('#name').val(data.name);
            $('#email').val(data.email);

            var appt_date = TimestampToNZFormat(data.appt_date);
            var assigned_date = TimestampToNZFormat(data.assigned_date);
            var date_submitted = TimestampToNZFormat(data.date_submitted);
            var date_status_updated = TimestampToNZFormat(data.date_status_updated);

            $('#date_submitted').val(date_submitted);
            $('#appt_date').val(appt_date);
            $('#assigned_date').val(assigned_date);
            $("#phone_num").val(data.appt_time);
            $("#address").val(data.address);
            $("#city").val(data.city);
            $("#time").val(data.time);
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
            
            var instructions_tmp = '';
            var anotes_tmp = '';
            var notes_disp = '';

            if (data.instructions != null) 
                instructions_tmp = "\r\nInstructions: "+data.instructions;
            if (data.additional_notes != null) 
                anotes_tmp = "\r\nAdditional Notes: "+data.additional_notes;
            if (data.notes != null) 
                notes_disp = data.notes;
            

            if (!(notes_disp.indexOf("Instructions: ") >= 0)) 
                notes_disp = notes_disp+instructions_tmp;
            if (!(notes_disp.indexOf("Additional Notes: ") >= 0)) 
                notes_disp = notes_disp+anotes_tmp;
            

            $("#notes").val(notes_disp);
            $("#status").val(data.status);
            $('#date_status_updated').val(date_status_updated);

            $('#formtype').val("update");
        });
    });




    //display modal form for creating new model
    $('#btn-add').click(function () {
        $("#status_div").hide();
        $('#frmClient').trigger("reset");
        $('#formtype').val("add");
        $('#lead_by').val("");
        $('#leadgen').val("0");
        $('#assigned_to').val("0");
        $(".leadgen").val("0");
        $(".leadgen").hide();
        $('#date_submitted').datepicker().datepicker("setDate", new Date());
        $('#date_status_updated').datepicker().datepicker("setDate", new Date());

    });

    $(document).on("click", ".send-data", function () {
        let client_id = $(this).val();
        let adviser_id = $(this).data("adviser_id");

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
                action: "create_send_client_data_entry"
            },
            type: "post",
            url: "libs/api/client_api.php",
            success: function (data) {
                console.log(data);
                $.ajax({
                    data: data,
                    type: "get",
                    url: "send_client_data?id=" + data,
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

    $('#btn-delete-cancel').on("click", function () {
        $('#confirmModal').modal('hide');
    });

    $('#clients-list').on("click", ".delete-client", function () {
        var mat_id = $(this).val();
        $('#confirmModal').modal('show');
        $('#delete-client').val(mat_id);
    });

    $('#btn-delete-confirm').on("click", function () {
        var mat_id = $('#delete-client').val();
        var data = {
            method: $('#_method').val(),
            id: $('#delete-client').val(),
        }
        console.log(data);
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        })
        $.ajax({
            data: data,
            type: "post",
            url: url,
            success: function (data) {
                console.log(data);
                $("#client" + mat_id).remove();
                table.row("#client" + mat_id).remove().draw(false);
                $('#confirmModal').modal('hide');
            },
            error: function (data) {
                console.log(data);
                console.log('Error:', data);
            }
        });

    });

    $(document).on('click', '#btn-save', function (e) {
        $(this).prop("disabled", true);
        var data = $("#frmClient").serialize();
        console.log(data);
        $("#save_client_spinner").show();
        let state = $("#formtype").val();
        console.log(state);

        $.ajax({
            data: data,
            type: "post",
            url: "crud/clients-crud.php",
            success: function (data) {
                $("#save_client_spinner").hide();
                $("#btn-save").prop("disabled", null);
                var appt_date = TimestampToNZFormat(data.appt_date);
                var assigned_date = TimestampToNZFormat(data.assigned_date);
                var creation_date = DatestampToNZFormat(data.creation_date);
                var creation_date_sort = DatestampToTimestamp(data.creation_date);
                var date_submitted = TimestampToNZFormat(data.date_submitted);
                console.log("Date Submitted:" + date_submitted);

                var newData = {
                    "0": "<td><input id='btn-send-'" + data.id + "'' type='image' src='email.png' value='" + data.id + "' class='send-data'   data-toggle='modal' data-target='#sendModal' data-toggle='tooltip' title='Send Client Data' ></td>",
                    "1": "<input data-toggle='modal' data-target='#myModal' type='image' class='open-modal'  src='edit.png' value='" + data.id + "'>",
                    "2": "<input type='image' class='delete-client'  src='delete.png' value='" + data.id + "'>",
                    "3": data.name,
                    "4": {
                        "display": appt_date,
                        "@data-order": data.appt_date
                    },
                    "5": {
                        "display": creation_date,
                        "@data-order": creation_date_sort
                    },
                    "6": data.appt_time,
                    "7": data.address,
                    "8": data.city,
                    "9": data.zipcode,
                    "10": data.lead_by,
                    "11": data.leadgen_name,
                    "12": data.adviser_name,
                    "13": assigned_date,
                    "14": {
                        "display": date_submitted,
                        "@data-order": data.date_submitted
                    },
                    "15": data.notes
                };

                console.log(newData);

                if (state == "add") { //if user added a new record
                    tbl_r = table.row.add(newData).node().id = "client" + data.id;
                    table.row("#client" + data.id).data(newData).draw(false);
                } else { //if user updated an existing record
                    table.row("#client" + data.id).data(newData).draw(false);
                }

                $('#myModal').modal('hide');
                $('#frmClient').trigger("reset");
            },
            error: function (data) {
                $(this).prop("disabled", false);
                console.log("Error", data);
                $('#myModal').modal('hide');
            }
        });

    });


    function TimestampToNZFormat(timestamp) {
        if (timestamp == "" || timestamp == null) {
            return "";
        }
        return timestamp.substring(6, 8) + "/" + timestamp.substring(4, 6) + "/" + timestamp.substring(0, 4);
    }

    function DatestampToNZFormat(timestamp) {
        if (timestamp == "" || timestamp == null) {
            return "N/A";
        }
        // Split timestamp into [ Y, M, D, h, m, s ]
        var t = timestamp.split(/[- :]/);

        var ampm = "am";
        var h = t[3];

        if (h >= 12 && h > 0) {
            ampm = "pm";
            h -= 12;
        } else if (h == 0) {
            h = 12;
        }

        // Apply each element to the Date function
        var d = t[2] + "/" + t[1] + "/" + t[0] + " " + h + ":" + t[4] + ":" + t[5] + " " + ampm;

        return d;
        // -> Wed Jun 09 2010 14:12:01 GMT+0100 (BST)
    }

    function DatestampToTimestamp(timestamp) {
        if (timestamp == "" || timestamp == null) {
            return "";
        }
        // Split timestamp into [ Y, M, D, h, m, s ]
        var t = timestamp.split(/[- :]/);

        // Apply each element to the Date function
        var d = t[0] + t[1] + t[2] + t[3] + t[4] + t[5];

        return d;
        // -> Wed Jun 09 2010 14:12:01 GMT+0100 (BST)
    }


});