$(document).ready(function(){

    var url = "crud/lead_data-crud";

    //display modal form for model editing
    $(document).on("click", ".open-modal", function(e){
        e.preventDefault();
        var mat_id = $(this).val();
                
        var end_url = url + '?id=' + mat_id;
        console.log(end_url);
        $.get(end_url, function (data) {            
            console.log(data.data);
            $('#lead_data_id').val(data.id);
            $('#appointment_date').val(data.data.appointment_date);
            $('#appointment_hour').val(data.data.appointment_hour);
            $('#appointment_minute').val(data.data.appointment_minute);
            $('#appointment_period').val(data.data.appointment_period);
            $('#venue').val(data.data.venue);
            $('#formtype').val("update");
        });
    });

    $('#btn-save').on('click',function(e) {
        var btn = $(this);
        btn.prop("disabled",true);
        var data = $("#frmData").serialize();
        state = $("#formtype").val();
        console.log(data);

            $.ajax({
                 data: data,
                 type: "post",
                 url: "crud/lead_data-crud.php",
                 success: function(data){
                    console.log(data);

                    console.log("Data Updated: " + data);
                    
                    console.log("Lead Data ID: ", data.id);

                    var emailurl = "email_lead_data?id=" + data.id; 
                    $.ajax({
                        data: data,
                        type: "get",
                        url: emailurl,
                        success: function(data){
                            console.log("Feedback: ", data);
                            btn.prop("disabled",false);
                            alert("Email sent");
                        },
                        error: function(data){
                            console.log("Error Sending Mail", data);
                            btn.prop("disabled",false);
                            alert("An error occurred, please contact the IT Support.");                           
                        }
                    });
                    
                    $('#myModal').modal('hide');

                    $('#frmData').trigger("reset");
                 },
                 error: function(data){
                    console.log("Error", data);
                    $("#report_text").val(data.reason);
                 }
            });
        
    });

});
