$(document).ready(function () {
    var url = "libs/api/steam_api.php";

    //display modal form for model editing
    $(document).on("click", ".open-modal", function (e) {
        e.preventDefault();
        var mat_id = $(this).val();

        console.log(mat_id);

        $.ajax({
            data: {
                action : "get_team",
                team_id : mat_id
            },
            type: "post",
            url: url,
            dataType: "json",
            success: function (data) {
                console.log(data);
                $('#name').val(data.name);
                $('#leader').val(data.leader);
                $('#type').val(data.type);
                $('#action').val("update_team");
                $('#team_id').val(mat_id);
                $('#formtype').val("update");
            },
            error: function (data) {
                console.log("Error", data);
            }
        });        
    });



    //display modal form for creating new model
    $('#btn-add').click(function () {
        $('#formtype').val("add");
        $('#action').val("create_team");
        $('#frmTeam').trigger("reset");
    });

    $('#btn-delete-cancel').on("click", function () {
        $('#confirmModal').modal('hide');
    });

    $('#teams-list').on("click", ".delete-team", function () {
        var mat_id = $(this).val();
        $('#confirmModal').modal('show');
        $('#delete-team').val(mat_id);
        $("#action").val("delete_lead_generator");
    });

    $('#btn-delete-confirm').on("click", function () {
        var mat_id = $('#delete-team').val();
        var data = {
            method: $('#_method').val(),
            id: $('#delete-team').val(),
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
                $("#team" + mat_id).remove();
                table.row("#team" + mat_id).remove().draw(false);
                $('#confirmModal').modal('hide');
            },
            error: function (data) {
                console.log(data);
                console.log('Error:', data);
            }
        });

    });

    $(document).on('click', '#btn-save', function (e) {
        console.log("Saving..");
        var data = $("#frmTeam").serialize();
        state = $("#formtype").val();
        console.log(data);

        $.ajax({
            data: data,
            type: "post",
            url: url,
            dataType: "json",
            success: function (data) {
                console.log(data);
                console.log(data.id);
                var newData = [
                    data.name,
                    data.adviser_name,
                    '<input data-toggle="modal" data-target="#myModal" type="image" class="open-modal" src="edit.png" value="'+ data.id + '">' +
                    '<input type="image" class="delete" src="delete.png" data-toggle="tooltip" title="Delete Team" data-id="'+ data.id +'">',
                    '<a href="team_members?id='+ data.id + '" class="btn btn-primary"><i class="fas fa-search"></i></a>'
                ];

                console.log(newData);
                if (state == "add") { //if user added a new record
                    table.row.add(newData).node().id = "team" + data.id;
                    table.draw(false);
                } else { //if user updated an existing record
                    table.row("#team" + data.id).data(newData).draw(false);
                }

                console.log("Data Saved: " + data);
                $("#report_text").html("Script saved.");
                $('#myModal').modal('hide');
                $('#frmTeam').trigger("reset");
            },
            error: function (data) {
                console.log("Error", data);
                $("#report_text").val(data.reason);
            }
        });

    });

    //delete button
    $(document).on('click','.delete',function() {
        var id = $(this).data("id");
        data = {
            action: 'delete_team',
            team_id: id
        };

        $.confirm({
            title: 'Confirm',
            content: 'Are you sure you want to delete this?',
            buttons: {
                confirm: function() {
                    $.ajax({
                        url: url,
                        data: data,
                        type: 'POST',
                        success: function(e) {
                            console.log(e);
                            table.row("#team" + id).remove().draw(false);
                        },
                        error: function(e) {
                            console.log(e);
                        }
                    });
                },
                cancel: function() {}
            }
        });
    });
});