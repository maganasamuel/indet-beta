$(document).ready(function(){

    var url = "crud/scripts-crud.php";

    //display modal form for model editing
    $(document).on("click", ".open-modal", function(e){
        e.preventDefault();
        var mat_id = $(this).val();
                
        console.log(mat_id);
        $.get(url + '/?id=' + mat_id, function (data) {            
            console.log(data);
            $('#script_id').val(data.id);
            $('#script').val(data.script);
            $('#caption').val(data.caption);
            $('#script_group').val(data.script_group);
            $('#formtype').val("update");
        });
    });
   
 

    //display modal form for creating new model
    $('#btn-add').click(function(){
        $('#formtype').val("add");
        $('#frmScript').trigger("reset");
    });

    $('#btn-delete-cancel').on("click", function(){
        $('#confirmModal').modal('hide');
    });

    $('#scripts-list').on("click", ".delete-script", function(){
        var mat_id = $(this).val();
        $('#confirmModal').modal('show');
        $('#delete-script').val(mat_id);
    });

    $('#btn-delete-confirm').on("click", function(){
        var mat_id = $('#delete-script').val();
        var data = {
            method: $('#_method').val(),
            id: $('#delete-script').val(),
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
                $("#script" + mat_id).remove();
                table.row("#script" + mat_id).remove().draw(false);
                $('#confirmModal').modal('hide');
            },
            error: function (data) {
                console.log(data);
                console.log('Error:', data);
            }
        });
       
    });

    $(document).on('click','#btn-save',function(e) {
        var data = $("#frmScript").serialize();
        state = $("#formtype").val();
        console.log(state);

            $.ajax({
                 data: data,
                 type: "post",
                 url: "crud/scripts-crud.php",
                 success: function(data){
                    console.log(data);

                    var newData = [data.script_group, data.caption, data.script,
                        "<input type='image' class='delete-script'  src='delete.png' value='"+ data.id +"'>",
                        "<input data-toggle='modal' data-target='#myModal' type='image' class='open-modal'  src='edit.png' value='"+data.id+"'>"
                    ];

                    if (state == "add"){ //if user added a new record
                        tbl_r = table.row.add(newData).node().id = "script"+data.id;
                        table.draw(false);
                    }else{ //if user updated an existing record
                        
                        table.row("#script" + data.id).data(newData).draw(false);
                    }

                
                    
                    console.log("Data Saved: " + data);
                    $("#report_text").html("Script saved.");
                    $('#myModal').modal('hide');
                    $('#frmScript').trigger("reset");
                 },
                 error: function(data){
                    console.log("Error", data);
                    $("#report_text").val(data.reason);
                 }
            });
        
    });

});
