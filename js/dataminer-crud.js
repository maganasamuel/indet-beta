$(document).ready(function(){

    var url = "crud/dataminer-crud.php";

    //display modal form for model editing
    $(document).on("click", ".edit", function(e){
        e.preventDefault();
        var mat_id = $(this).val();
                
        console.log(mat_id);
        $.get(url + '/?id=' + mat_id, function (data) {            
            console.log(data);
            $("#updating_notification").html("Updating Number.");
            $("#updating_notification").css("color", "green");
            $("#updating_notification").slideDown();
            $('#data_id').val(data.id);
            $('#name').val(data.name);
            $('#number').val(data.number);
            $('#status').val(data.status);
            $('#formtype').val("update");
        });
    });      

    //display modal form for creating new model
    $('#reset').click(function(){
        $("#updating_notification").slideUp();
        $("#updating_notification").val("");
        $('#formtype').val("add");
        $('#frmLead').trigger("reset");
    });  

    $('#data-list').on("click", ".delete-data", function(){
        var mat_id = $(this).val();
        console.log("Attempting to delete data:" + mat_id);
        $('#confirmModal').modal('show');
        $('#delete-data').val(mat_id);
    });

    $('#btn-delete-cancel').on("click", function(){
        $('#confirmModal').modal('hide');
    });

    $('#btn-delete-confirm').on("click", function(){
        var mat_id = $('#delete-data').val();
        var data = {
            method: $('#_method').val(),
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
            url: url,
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

    $(document).on('click','#btn-save',function(e) {
        console.log("Attempting to save");
        $("#updating_notification").slideUp();
        var name = $("#name").val();
        var number = $("#number").val();
        if(name==""||number==""){
            $("#updating_notification").html("Name Or Number is empty.");
            $("#updating_notification").css("color", "red");
            $("#updating_notification").slideDown();
            return;
        }
        var data = $("#frmLead").serialize();
        state = $("#formtype").val();
        console.log(state);

            $.ajax({
                 data: data,
                 type: "post",
                 url: "crud/dataminer-crud.php",
                 success: function(data){
                    console.log(data);

                    var newData = [data.name, data.number,
                        "<input type='image' class='edit'  src='edit.png' value='" + data.id + "'> &nbsp;" +
                        " &nbsp; &nbsp; <input type='image' class='delete-data'  src='delete.png' value='" + data.id + "'>"
                    ];

                    if (state == "add"){ //if user added a new record
                        tbl_r = table.row.add(newData).node().id = "data"+data.id;
                        table.draw(false);
                    }else{ //if user updated an existing record
                        
                        table.row("#data" + data.id).data(newData).draw(false);
                    }

                    console.log("Data Saved: " + data);
                    $('#name').val("");
                    $('#number').val("");
                    $('#formtype').val("add");
                $('#status').val("Do Not Call");
                 },
                 error: function(data){
                    console.log("Error", data);
                 }
            });
        
    });

});
