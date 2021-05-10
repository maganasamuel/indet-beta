$(document).ready(function(){

    var url = "crud/products-crud.php";

    //display modal form for model editing
    $(document).on("click", ".open-modal", function(e){
        e.preventDefault();
        var mat_id = $(this).val();
                
        console.log(mat_id);
        $.get(url + '/?id=' + mat_id, function (data) {            
            console.log(data);
            $('#product_id').val(data.id);
            $('#acronym').val(data.acronym);
            $('#name').val(data.name);
            $('#formtype').val("update");
        });
    });
   
 

    //display modal form for creating new model
    $('#btn-add').click(function(){
        $('#formtype').val("add");
        $('#frmProduct').trigger("reset");
    });

    $('#btn-delete-cancel').on("click", function(){
        $('#confirmModal').modal('hide');
    });

    $('#products-list').on("click", ".delete-product", function(){
        var mat_id = $(this).val();
        $('#confirmModal').modal('show');
        $('#delete-product').val(mat_id);
    });

    $('#btn-delete-confirm').on("click", function(){
        var mat_id = $('#delete-product').val();
        var data = {
            method: $('#_method').val(),
            id: $('#delete-product').val(),
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
                $("#product" + mat_id).remove();
                table.row("#product" + mat_id).remove().draw(false);
                $('#confirmModal').modal('hide');
            },
            error: function (data) {
                console.log(data);
                console.log('Error:', data);
            }
        });
       
    });

    $(document).on('click','#btn-save',function(e) {
        var data = $("#frmProduct").serialize();
        state = $("#formtype").val();
        console.log(state);

            $.ajax({
                 data: data,
                 type: "post",
                 url: "crud/products-crud.php",
                 success: function(data){
                    console.log(data);

                    if(data.acronym=="")
                        data.acronym="N/A";
                        
                    var newData = [data.acronym, data.name,
                        "<input type='image' class='delete-product'  src='delete.png' value='"+ data.id +"'>",
                        "<input data-toggle='modal' data-target='#myModal' type='image' class='open-modal'  src='edit.png' value='"+data.id+"'>"
                    ];

                    if (state == "add"){ //if user added a new record
                        tbl_r = table.row.add(newData).node().id = "product"+data.id;
                        table.draw(false);
                    }else{ //if user updated an existing record
                        
                        table.row("#product" + data.id).data(newData).draw(false);
                    }

                
                    
                    console.log("Data Saved: " + data);
                    $("#report_text").html("Product saved.");
                    $('#myModal').modal('hide');
                    $('#frmProduct').trigger("reset");
                 },
                 error: function(data){
                    console.log("Error", data);
                    $("#report_text").val(data.reason);
                 }
            });
        
    });

});
