$(document).ready(function(){

    var url = "crud/callbacks-crud.php";


    //display modal form for creating new model
    $('#btn-add').click(function(){
        $('#formtype').val("add");
        $('#frmType').trigger("reset");
    });

    $('#btn-delete-cancel').on("click", function(){
        $('#confirmModal').modal('hide');
    });

    $('#callbacks-list').on("click", ".delete-callback", function(){
        var mat_id = $(this).val();
        $('#confirmModal').modal('show');
        $('#delete-callback').val(mat_id);
    });

    $('#btn-delete-confirm').on("click", function(){
        var mat_id = $('#delete-callback').val();
        var data = {
            method: $('#_method').val(),
            id: $('#delete-callback').val(),
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
                $("#callback" + mat_id).remove();
                table.row("#callback" + mat_id).remove().draw(false);
                $('#confirmModal').modal('hide');
            },
            error: function (data) {
                console.log(data);
                console.log('Error:', data);
            }
        });
       
    });
});
