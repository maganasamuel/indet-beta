
$(document).ready(function(){
    var date_helper = new DateHelper();
    var url = "crud/leadgen-crud.php";
    var uploads_url = "../indet_photos_stash/";
    //display modal form for model editing
    $(document).on("click", ".open-modal", function(e){
        e.preventDefault();
        var mat_id = $(this).val();
                
        console.log(mat_id);
        $.get(url + '/?id=' + mat_id, function (data) {            
            console.log(data);
            $('#leadgen_id').val(data.id);
            $('#name').val(data.name);
            $('#email').val(data.email);
            if(data.birthday!="" && data.birthday!=undefined){
                $('#birthday').val(date_helper.convertDatestampToNZ(data.birthday));
            }
            else{
                $('#birthday').val("");
            }

            //Hired
            if (data.date_hired != "" && data.date_hired != undefined) {
                $('#date_hired').val(date_helper.convertDatestampToNZ(data.date_hired));
            } else {
                $('#date_hired').val("");
            }

            //Termination
            if (data.termination_date != "" && data.termination_date != undefined) {
                $('#termination_date').val(date_helper.convertDatestampToNZ(data.termination_date));
            } else {
                $('#termination_date').val("");
            }

            let link = uploads_url + data.image;
            console.log(link);
            $('#imgPreview').attr('src', link);
            $('#type').val(data.type);
            $("#action").val("update_lead_generator");
            $('#formtype').val("update");
        });
    });
   
 

    //display modal form for creating new model
    $('#btn-add').click(function(){
        $('#formtype').val("add");
        $("#type").val(leadgen_type);
        $("#action").val("create_lead_generator");
        $('#frmLeadgen').trigger("reset");
    });

    $('#imageInput').on("change", function() {
        var fileUpload = document.getElementById('imageInput');
        var formData = new FormData();

        if (fileUpload.files.length == 0) {
            alert('Select a file!');
            return;
        }

        formData.append("fileToUpload", fileUpload.files[0], fileUpload.files[0].name);

        $.ajax({
            url: "libs/api/uploader",
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,

            success: function(data, textStatus, jqXHR) {
                console.log(data);
                let link = uploads_url + data;
                console.log(link);
                $('#imgPreview').attr('src', link);
                $('#image').val(data);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log(errorThrown);
                alert('An error occurred when uploading the file!');
            }
        });
    });

    $('#btn-delete-cancel').on("click", function(){
        $('#confirmModal').modal('hide');
    });

    $('#leadgens-list').on("click", ".delete-leadgen", function(){
        var mat_id = $(this).val();
        $('#confirmModal').modal('show');
        $('#delete-leadgen').val(mat_id);
        $("#action").val("delete_lead_generator");
    });

    $('#btn-delete-confirm').on("click", function(){
        var mat_id = $('#delete-leadgen').val();
        var data = {
            method: $('#_method').val(),
            id: $('#delete-leadgen').val(),
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
                $("#leadgen" + mat_id).remove();
                table.row("#leadgen" + mat_id).remove().draw(false);
                $('#confirmModal').modal('hide');
            },
            error: function (data) {
                console.log(data);
                console.log('Error:', data);
            }
        });
       
    });

    $(document).on('click','#btn-save',function(e) {
        $("#save_spinner").show();
        console.log("Saving..");
        var data = $("#frmLeadgen").serialize();
        state = $("#formtype").val();
        console.log(data);

            $.ajax({
                 data: data,
                 type: "post",
                 url: "libs/api/leadGenerator_api.php",
                 dataType: "json",
                 success: function(data){
                    $("#save_spinner").hide();
                    console.log(data);
                    console.log(data.id);
                    var newData = [
                        data.name,
                        data.email,
                        data.birthday,
                        data.leads_generated,
                        data.leads_cancelled,
                        "<input type='image' class='delete-leadgen'  src='delete.png' value='"+ data.id +"'>",
                        "<input data-toggle='modal' data-target='#myModal' type='image' class='open-modal'  src='edit.png' value='"+data.id+"'>",
                        "<a href='leadgen_profile?id=" + data.id + "' class='btn btn-primary'><i class='fas fa-search'></i></a>"
                    ];
                    
                    console.log(newData);
                    if (state == "add"){ //if user added a new record
                        tbl_r = table.row.add(newData).node().id = "leadgen"+data.id;
                        table.draw(false);
                    }else{ //if user updated an existing record
                        
                        table.row("#leadgen" + data.id).data(newData).draw(false);
                    }

                
                    
                    console.log("Data Saved: " + data);
                    $("#report_text").html("Script saved.");
                    $('#myModal').modal('hide');
                    $('#frmLeadgen').trigger("reset");
                 },
                 error: function(data){
                    console.log("Error", data);
                    $("#report_text").val(data.reason);
                 }
            });
        
    });

});
