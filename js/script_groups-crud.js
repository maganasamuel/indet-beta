$(document).ready(function(){

    var url = "crud/script_groups-crud.php";

    //display modal form for model editing
    $(document).on("click", ".open-modal", function(e){
        e.preventDefault();
        var mat_id = $(this).val();
       
        $.get(url + '/?id=' + mat_id, function (data) {            
            console.log(data);
            $('#script_group_id').val(data.id);
            $('#acronym').val(data.acronym);
            $('#name').val(data.name);
            $('#formtype').val("update");
        });
    }); 

    //display modal form for creating new model
    $('#btn-add').click(function(){
        console.log("Add");

        $.ajax({
            type: "GET",
            url: "get_highest_script_group_priority.php"
        }).done(function (data) {
            console.log(data);
            var prio = parseFloat(data.priority) + 1
            $("#priority").val(prio);

        }).fail(function (jqXHR, textStatus, errorThrown) {
            alert("AJAX call failed: " + textStatus + ", " + errorThrown);
        });

        $('#formtype').val("add");
        $('#frmGroup').trigger("reset");
    });

    $('#btn-delete-cancel').on("click", function(){
        $('#confirmModal').modal('hide');
    });

    $('#script_groups-list').on("click", ".delete-script_group", function(){
        var mat_id = $(this).val();
        $('#confirmModal').modal('show');
        $('#delete-script_group').val(mat_id);
    });

    $('#btn-delete-confirm').on("click", function(){
        var mat_id = $('#delete-script_group').val();
        var data = {
            method: $('#_method').val(),
            id: $('#delete-script_group').val(),
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
                $("#script_group" + mat_id).remove();
                table.row("#script_group" + mat_id).remove().draw(false);
                $('#confirmModal').modal('hide');
            },
            error: function (data) {
                console.log(data);
                console.log('Error:', data);
            }
        });
       
    });

    $(document).on('click','#btn-save',function(e) {
        var data = $("#frmGroup").serialize();
        state = $("#formtype").val();
        console.log(data);

            $.ajax({
                 data: data,
                 type: "post",
                 url: "crud/script_groups-crud.php",
                 success: function(data){
                    console.log(data);

                    var newData = [data.priority, data.name,
                        "<input type='image' class='delete-script_group'  src='delete.png' value='"+ data.id +"'>",
                        "<input data-toggle='modal' data-target='#myModal' type='image' class='open-modal'  src='edit.png' value='"+data.id+"'>"
                    ];
                    
                    if (state == "add"){ //if user added a new record
                        tbl_r = table.row.add(newData).node().id = "script_group"+data.id;
                        table.draw(false);
                    }else{ //if user updated an existing record   
                        table.row("#script_group" + data.id).data(newData).draw(false);
                    }

                    console.log("Data Saved: " + data);
                    $('#myModal').modal('hide');
                    $('#frmGroup').trigger("reset");
                 },
                 error: function(data){
                    console.log("Error", data);
                 }
            });
        
    });

});
