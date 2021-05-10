$(document).ready(function(){

    $('#btn-delete-cancel').on("click", function(){
        $('#confirmModal').modal('hide');
    });

    $('#issued_clients_table').on("click", ".unissue_client", function(){
        var mat_id = $(this).val();
        $('#confirmModal').modal('show');
        $('#delete-client').val(mat_id);
    });

    $('#btn-delete-confirm').on("click", function(){
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

    $(document).on('click','#btn-save',function(e) {
        $(this).prop("disabled", true);
        var data = $("#frmClient").serialize();

        state = $("#formtype").val();
        console.log(state);
            $.ajax({
                 data: data,
                 type: "post",
                 url: "crud/clients-crud.php",
                 success: function(data){
                    $("#btn-save").prop("disabled", null);
                    var appt_date = TimestampToNZFormat(data.appt_date);
                    var assigned_date = TimestampToNZFormat(data.assigned_date);
                    var date_submitted = TimestampToNZFormat(data.date_submitted);
                    console.log("Date Submitted:" + date_submitted);

                    var newData = {
                        "0" : "<input data-toggle='modal' data-target='#myModal' type='image' class='open-modal'  src='edit.png' value='"+data.id+"'>",
                        "1" : "<input type='image' class='delete-client'  src='delete.png' value='"+ data.id +"'>",
                        "2" : data.name,
                        "3" : {
                            "display" : appt_date,
                            "@data-order" : data.appt_date
                        },
                        "4" : data.appt_time,
                        "5" : data.address,
                        "6" : data.city,
                        "7" : data.zipcode,
                        "8" : data.lead_by, 
                        "9" :  data.leadgen_name, 
                        "10" : data.adviser_name,
                        "11" : assigned_date,
                        "12" : {
                                "display" : date_submitted,
                                "@data-order" : data.date_submitted
                            },
                        "13" : data.notes
                    };

                    if (state == "add"){ //if user added a new record
                        tbl_r = table.row.add(newData).node().id = "client"+data.id;
                        table.row("#client" + data.id).data(newData).draw(false);
                    }else{ //if user updated an existing record
                        table.row("#client" + data.id).data(newData).draw(false);
                    }
                
                    $('#myModal').modal('hide');
                    $('#frmClient').trigger("reset");
                 },
                 error: function(data){
                    $(this).prop("disabled", false);
                    console.log("Error", data);
                    $('#myModal').modal('hide');
                 }
            });
        
    });

    
    function TimestampToNZFormat(timestamp){       
        return timestamp.substring(6, 8) + "/" + timestamp.substring(4, 6) + "/" + timestamp.substring(0, 4);
    }
});
