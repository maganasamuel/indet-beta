$(document).ready(function(){

    var url = "crud/insurance_types-crud.php";

    //display modal form for model editing
    $(document).on("click", ".open-modal", function(e){
        e.preventDefault();
        var mat_id = $(this).val();
       
        $.get(url + '/?id=' + mat_id, function (data) {            
            console.log(data);
            $('#type_id').val(data.id);
            $('#acronym').val(data.acronym);
            $('#description').val(data.description);
            $('#formtype').val("update");
        });
    }); 

    //display modal form for creating new model
    $('#btn-add').click(function(){
        $('#formtype').val("add");
        $('#frmType').trigger("reset");
    });

    $('#btn-delete-cancel').on("click", function(){
        $('#confirmModal').modal('hide');
    });

    $('#insurance_types-list').on("click", ".delete-insurance_type", function(){
        var mat_id = $(this).val();
        $('#confirmModal').modal('show');
        $('#delete-insurance_type').val(mat_id);
    });

    $('#btn-delete-confirm').on("click", function(){
        var mat_id = $('#delete-insurance_type').val();
        var data = {
            method: $('#_method').val(),
            id: $('#delete-insurance_type').val(),
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
                $("#insurance_type" + mat_id).remove();
                table.row("#insurance_type" + mat_id).remove().draw(false);
                $('#confirmModal').modal('hide');
            },
            error: function (data) {
                console.log(data);
                console.log('Error:', data);
            }
        });
       
    });

    $(document).on('click','#btn-save',function(e) {
        var data = $("#frmType").serialize();
        state = $("#formtype").val();
        console.log(data);

            $.ajax({
                 data: data,
                 type: "post",
                 url: "crud/insurance_types-crud.php",
                 success: function(data){
                    console.log(data);

                    if(data.acronym=="")
                        data.acronym="N/A";

                    var newData = [data.acronym, data.description,
                        "<input type='image' class='delete-insurance_type'  src='delete.png' value='"+ data.id +"'>",
                        "<input data-toggle='modal' data-target='#myModal' type='image' class='open-modal'  src='edit.png' value='"+data.id+"'>"
                    ];
                    
                    if (state == "add"){ //if user added a new record
                        tbl_r = table.row.add(newData).node().id = "insurance_type"+data.id;
                        table.draw(false);
                    }else{ //if user updated an existing record   
                        table.row("#insurance_type" + data.id).data(newData).draw(false);
                    }

                    console.log("Data Saved: " + data);
                    $('#myModal').modal('hide');
                    $('#frmType').trigger("reset");
                 },
                 error: function(data){
                    console.log("Error", data);
                 }
            });
        
    });

});
