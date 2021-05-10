$(document).ready(function(){

    var url = "crud/insurance_companies-crud.php";

    //display modal form for model editing
    $(document).on("click", ".open-modal", function(e){
        e.preventDefault();
        var mat_id = $(this).val();
       
        $.get(url + '/?id=' + mat_id, function (data) {            
            console.log(data);
            $('#company_id').val(data.id);
            $('#acronym').val(data.acronym);
            $('#name').val(data.name);
            $('#formtype').val("update");
        });
    }); 

    //display modal form for creating new model
    $('#btn-add').click(function(){
        $('#formtype').val("add");
        $('#frmCompany').trigger("reset");
    });

    $('#btn-delete-cancel').on("click", function(){
        $('#confirmModal').modal('hide');
    });

    $('#insurance_companies-list').on("click", ".delete-insurance_company", function(){
        var mat_id = $(this).val();
        $('#confirmModal').modal('show');
        $('#delete-insurance_company').val(mat_id);
    });

    $('#btn-delete-confirm').on("click", function(){
        var mat_id = $('#delete-insurance_company').val();
        var data = {
            method: $('#_method').val(),
            id: $('#delete-insurance_company').val(),
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
                $("#insurance_company" + mat_id).remove();
                table.row("#insurance_company" + mat_id).remove().draw(false);
                $('#confirmModal').modal('hide');
            },
            error: function (data) {
                console.log(data);
                console.log('Error:', data);
            }
        });
       
    });

    $(document).on('click','#btn-save',function(e) {
        var data = $("#frmCompany").serialize();
        state = $("#formtype").val();
        console.log(data);

            $.ajax({
                 data: data,
                 type: "post",
                 url: "crud/insurance_companies-crud.php",
                 success: function(data){
                    console.log(data);

                    if(data.acronym=="")
                        data.acronym="N/A";

                    var newData = [data.acronym, data.name,
                        "<input type='image' class='delete-insurance_company'  src='delete.png' value='"+ data.id +"'>",
                        "<input data-toggle='modal' data-target='#myModal' type='image' class='open-modal'  src='edit.png' value='"+data.id+"'>"
                    ];
                    
                    if (state == "add"){ //if user added a new record
                        tbl_r = table.row.add(newData).node().id = "insurance_company"+data.id;
                        table.draw(false);
                    }else{ //if user updated an existing record   
                        table.row("#insurance_company" + data.id).data(newData).draw(false);
                    }

                    console.log("Data Saved: " + data);
                    $('#myModal').modal('hide');
                    $('#frmCompany').trigger("reset");
                 },
                 error: function(data){
                    console.log("Error", data);
                 }
            });
        
    });

});
