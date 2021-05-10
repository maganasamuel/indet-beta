$(document).ready(function () {
    var date_helper = new DateHelper();
    var api = "libs/api/personalData_api.php";
    var uploads_url = "../indet_photos_stash/";
    //display modal form for model editing
    $(document).on("click", ".open-modal", function (e) {
        e.preventDefault();
        var mat_id = $(this).val();

        $.ajax({
            data: {
                action: "fetch_data",
                id: mat_id
            },
            url: api,
            type: "post",
            dataType: "json",
            success: function (data) {
                console.log(data);
                $('#data_id').val(data.id);
                $('#full_name').val(data.full_name);
                $('#email').val(data.email);

                let role = data.role;

                $('#role_selection').val(role);
                $('#role').val(role);
                console.log(role);
                switch (role) {
                    case "Junior Admin":
                    case "Admin":
                    case "Executive Admin":
                        $("#role").hide();
                        break;
                    case "":
                        $('#role_selection').val("");
                        $("#role").hide();
                        break;
                    default:
                        $('#role_selection').val("Other");
                        $("#role").show();
                        break;
                }

                if (data.birthday != "" && data.birthday != undefined) {
                    $('#birthday').val(date_helper.convertDatestampToNZ(data.birthday));
                } else {
                    $('#birthday').val("");
                }

                //Hired
                if (data.date_hired != "" && data.date_hired != undefined) {
                    $('#date_hired').val(date_helper.convertDatestampToNZ(data.date_hired));
                } else {
                    $('#date_hired').val("");
                }

                //Termination
                if (data.termination_date != "" && data.termination_date != undefined) {
                    $('#termination_date').val(date_helper.convertDatestampToNZ(data.termination_date));
                } else {
                    $('#termination_date').val("");
                }

                let link = uploads_url + data.image;
                console.log(link);
                $('#imgPreview').attr('src', link);

                $("#action").val("update_data");
                $('#formtype').val("update");
            },
            error: function (data) {
                console.log(data);
                console.log('Error:', data);
            }
        });
    });




    //display modal form for creating new model
    $('#role_selection').click(function () {
        let role = $(this).val();
        console.log(role);
        if (role == "Other") {
            $("#role").val("");
            $("#role").slideDown();
        } else {
            $("#role").slideUp();
            $("#role").val(role);
        }
    });

    //display modal form for creating new model
    $('#btn-add').click(function () {
        $('#formtype').val("add");
        $("#action").val("create_data");
        $('#frmData').trigger("reset");
    });

    $('#btn-delete-cancel').on("click", function () {
        $('#confirmModal').modal('hide');
    });

    $('#datas-list').on("click", ".delete-data", function () {
        var mat_id = $(this).val();
        $('#confirmModal').modal('show');
        $('#delete-data').val(mat_id);
        $("#action").val("delete_data");
    });

    $('#btn-delete-confirm').on("click", function () {
        var mat_id = $('#delete-data').val();
        var data = {
            method: $('#_method').val(),
            action: "delete_data",
            id: $('#delete-data').val(),
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
            url: api,
            success: function (data) {
                console.log(data);
                $("#data" + mat_id).remove();
                table.row("#data" + mat_id).remove().draw(false);
                $('#confirmModal').modal('hide');
            },
            error: function (data) {
                console.log(data);
                console.log('Error:', data);
            }
        });

    });

    $('#imageInput').on("change", function () {
        var fileUpload = document.getElementById('imageInput');
        var formData = new FormData();

        if (fileUpload.files.length == 0) {
            alert('Select a file!');
            return;
        }

        formData.append("fileToUpload", fileUpload.files[0], fileUpload.files[0].name);

        $.ajax({
            url: "libs/api/uploader",
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,

            success: function (data, textStatus, jqXHR) {
                console.log(data);
                let link = uploads_url + data;
                console.log(link);
                $('#imgPreview').attr('src', link);
                $('#image').val(data);
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log(errorThrown);
                alert('An error occurred when uploading the file!');
            }
        });
    });


    $(document).on('click', '#btn-save', function (e) {
        console.log("Saving..");
        var data = $("#frmData").serialize();
        state = $("#formtype").val();
        console.log(data);

        $.ajax({
            data: data,
            type: "post",
            url: api,
            dataType: "json",
            success: function (data) {
                console.log(data);
                console.log(data.id);
                var newData = [
                    data.full_name,
                    data.email,
                    data.birthday,
                    data.role,
                    "<input type='image' class='delete-data'  src='delete.png' value='" + data.id + "'>",
                    "<input data-toggle='modal' data-target='#myModal' type='image' class='open-modal'  src='edit.png' value='" + data.id + "'>"
                ];

                console.log(newData);
                if (state == "add") { //if user added a new record
                    tbl_r = table.row.add(newData).node().id = "data" + data.id;
                    table.draw(false);
                } else { //if user updated an existing record
                    table.row("#data" + data.id).data(newData).draw(false);
                }

                console.log("Data Saved: " + data);
                $("#report_text").html("Script saved.");
                $('#myModal').modal('hide');
                $('#frmData').trigger("reset");
            },
            error: function (data) {
                console.log("Error", data);
                $("#report_text").val(data.reason);
            }
        });

    });

});