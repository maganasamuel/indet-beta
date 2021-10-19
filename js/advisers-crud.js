window.addEventListener("load", function () {
    $(document).ready(function () {
        var date_helper = new DateHelper();

        var url = "libs/api/adviser_api.php";
        var uploads_url = "../indet_photos_stash/";

        //display modal form for model editing
        $(document).on("click", ".open-modal", function (e) {
            e.preventDefault();
            var mat_id = $(this).val();
            $("#status_div").show();
            console.log(mat_id);
            $.ajax({
                data: {
                    adviser_id: mat_id,
                    action: "get_adviser",
                },
                type: "post",
                url: url,
                success: function (data) {
                    data = JSON.parse(data);
                    console.log(data);
                    let link = uploads_url + data.image;
                    console.log(link);
                    $("#imgPreview").attr("src", link);
                    $("#image").val(data.image);
                    $("#adviser_id").val(data.id);
                    $("#name").val(data.name);
                    $("#payroll_name").val(data.payroll_name);
                    let team = data.team_id;
                    if (team == null) team = 0;
                    $(
                        'form select[name="team_id"] option[value="' +
                            team +
                            '"]'
                    ).prop("selected", "selected");

                    let steam = data.steam_id;
                    if (steam == null) steam = 0;
                    $(
                        'form select[name="steam_id"] option[value="' +
                            steam +
                            '"]'
                    ).prop("selected", "selected");

                    let position_id = data.position_id;
                    if (position_id == null) position_id = 0;
                    $(
                        'form select[name="position_id"] option[value="' +
                            position_id +
                            '"]'
                    ).prop("selected", "selected");

                    $("#fsp_num").val(data.fsp_num);
                    $("#company_name").val(data.company_name);
                    $("#email").val(data.email);
                    $("#leads").val(data.leads);
                    $("#bonus").val(data.bonus);
                    $("#address").val(data.address);

                    //Birthday
                    if (data.birthday != "" && data.birthday != undefined) {
                        $("#birthday").val(TimestampToNZFormat(data.birthday));
                    } else {
                        $("#birthday").val("");
                    }

                    //Hired
                    if (data.date_hired != "" && data.date_hired != undefined) {
                        $("#date_hired").val(
                            TimestampToNZFormat(data.date_hired)
                        );
                    } else {
                        $("#date_hired").val("");
                    }

                    //Termination
                    if (
                        data.termination_date != "" &&
                        data.termination_date != undefined
                    ) {
                        $("#termination_date").val(
                            TimestampToNZFormat(data.termination_date)
                        );
                    } else {
                        $("#termination_date").val("");
                    }

                    $("#action").val("update_adviser");
                    $("#formtype").val("update");
                },
                error: function (data) {
                    console.log("Error", data);
                },
            });
        });

        //display modal form for creating new model
        $("#btn-add").click(function () {
            $("#status_div").hide();
            $("#frmAdviser").trigger("reset");
            $("#formtype").val("add");
            $("#action").val("create_adviser");
            $("#leads").val("35");
            $("#bonus").val("50");
        });

        $("#btn-delete-cancel").on("click", function () {
            $("#confirmModal").modal("hide");
        });

        $("#advisers-list").on("click", ".delete-adviser", function () {
            var mat_id = $(this).val();
            $("#confirmModal").modal("show");
            $("#delete-adviser").val(mat_id);
        });

        $("#btn-delete-confirm").on("click", function () {
            var mat_id = $("#delete-adviser").val();
            var data = {
                method: $("#_method").val(),
                id: $("#delete-adviser").val(),
            };

            console.log(data);
            $.ajaxSetup({
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="_token"]').attr("content"),
                },
            });
            $.ajax({
                data: data,
                type: "post",
                url: url,
                success: function (data) {
                    console.log(data);
                    $("#adviser" + mat_id).remove();
                    table
                        .row("#adviser" + mat_id)
                        .remove()
                        .draw(false);
                    $("#confirmModal").modal("hide");
                },
                error: function (data) {
                    console.log(data);
                    console.log("Error:", data);
                },
            });
        });

        $("#imageInput").on("change", function () {
            var fileUpload = document.getElementById("imageInput");
            var formData = new FormData();

            if (fileUpload.files.length == 0) {
                alert("Select a file!");
                return;
            }

            formData.append(
                "fileToUpload",
                fileUpload.files[0],
                fileUpload.files[0].name
            );

            $.ajax({
                url: "libs/api/uploader",
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,

                success: function (data, textStatus, jqXHR) {
                    console.log(data);
                    let link = uploads_url + data;
                    console.log(link);
                    $("#imgPreview").attr("src", link);
                    $("#image").val(data);
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    console.log(errorThrown);
                    alert("An error occurred when uploading the file!");
                },
            });
        });

        $(document).on("click", "#btn-save", function (e) {
            $("#save_spinner").show();
            let btn = $(this);
            btn.prop("disabled", true);
            var data = $("#frmAdviser").serialize();
            state = $("#formtype").val();
            console.log(state);

            $.ajax({
                data: data,
                type: "post",
                url: url,
                success: function (data) {
                    $("#save_spinner").hide();
                    console.log(data);
                    data = JSON.parse(data);
                    $("#btn-save").prop("disabled", null);
                    var newData = {
                        0: data.name,
                        1: data.fsp_num,
                        2: data.address,
                        3: data.email,
                        4: data.leads,
                        5: data.bonus,
                        6:
                            "<input data-toggle='modal' data-target='#myModal' type='image' class='open-modal'  src='edit.png' value='" +
                            data.id +
                            "'>",
                        7:
                            '<a href="adviser_profile?id=' +
                            data.id +
                            '" class="btn btn-primary"><i class="fas fa-search"></i></a>&nbsp;&nbsp;&nbsp;' +
                            '<a href="adviser_strings.php?adviser_id=' +
                            data.id +
                            '">View Strings</i></a>',
                    };
                    console.log(newData);

                    if (state == "add") {
                        //if user added a new record
                        tbl_r = table.row.add(newData).node().id =
                            "adviser" + data.id;
                        table
                            .row("#adviser" + data.id)
                            .data(newData)
                            .draw(false);
                    } else {
                        //if user updated an existing record
                        table
                            .row("#adviser" + data.id)
                            .data(newData)
                            .draw(false);
                    }
                    btn.prop("disabled", false);

                    $("#myModal").modal("hide");
                    $("#frmAdviser").trigger("reset");
                },
                error: function (data) {
                    btn.prop("disabled", false);
                    console.log("Error", data);
                    $("#myModal").modal("hide");
                },
            });
        });
    });
});

function TimestampToNZFormat(timestamp) {
    return (
        timestamp.substring(6, 8) +
        "/" +
        timestamp.substring(4, 6) +
        "/" +
        timestamp.substring(0, 4)
    );
}

function NZFormatToTimestamp(timestamp) {
    return (
        timestamp.substring(6, 10) +
        timestamp.substring(3, 5) +
        timestamp.substring(0, 2)
    );
}
