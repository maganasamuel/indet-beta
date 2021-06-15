$(document).ready(function(){

    var url = "crud/users-crud.php";

    //display modal form for model editing
    $(document).on("click", ".open-modal", function(e){
        e.preventDefault();
        var mat_id = $(this).val();
       
        $.get(url + '/?id=' + mat_id, function (data) {            
            console.log(data);
            $('#user_id').val(data.id);
            $('#username').val(data.username);
            $('#type').val(data.type);
            $("#linked_id_div").hide();

            $('#linked_id').val(data.linked_id);
            $('#formtype').val("update");

            
            var usertype = data.type;
            $("#telemarketer_linked").hide();
            $("#f2fmarketer_linked").hide();
            $("#adviser_linked").hide();
            $("#personal_data_linked").hide();

            if (usertype == "Adviser") {
                $("#adviser_linked").show();
                $('#adviser_linked').val(data.linked_id);

            } else if (usertype == "Telemarketer") {
                $("#telemarketer_linked").show();
                $('#telemarketer_linked').val(data.linked_id);

            } else if (usertype == "Admin" || usertype == "User") {
                $("#personal_data_linked").show();
                $('#personal_data_linked').val(data.linked_id);
            } else if (usertype == "Face-to-Face Marketer") {
                $("#f2fmarketer_linked").show();
                $('#f2fmarketer_linked').val(data.linked_id);
            } else {
                $('#telemarketer_linked').val("0");
                $("#adviser_linked").val("0");
                $("#personal_data_linked").val("0");
            }

            $("#linked_id_div").slideDown();

        });
    });
   
 

    //display modal form for creating new model
    $('#btn-add').click(function(){
        $('#formtype').val("add");
        $('#frmUser').trigger("reset");
    });

    $('#btn-delete-cancel').on("click", function(){
        $('#confirmModal').modal('hide');
    });

    $('#users-list').on("click", ".delete-user", function(){
        var mat_id = $(this).val();
        $('#confirmModal').modal('show');
        $('#delete-user').val(mat_id);
    });

    $('#btn-delete-confirm').on("click", function(){
        var mat_id = $('#delete-user').val();
        var data = {
            method: $('#_method').val(),
            id: $('#delete-user').val(),
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
                $("#user" + mat_id).remove();
                $('#confirmModal').modal('hide');
            },
            error: function (data) {
                console.log(data);
                console.log('Error:', data);
            }
        });
       
    });

    $(document).on('click','#btn-save',function(e) {
        var data = $("#frmUser").serialize();
        state = $("#formtype").val();
        console.log(validatePassword());
        console.log(data);
        if(validatePassword()){
            $.ajax({
                 data: data,
                 type: "post",
                 url: "crud/users-crud.php",
                 success: function(data){
                    console.log(data);
                    var user = "<tr id='user"+data.id+"' cellpadding='5px' cellspacing='5px'><td>"+data.username+"</td><td>"+data.type+"</td>";
                    user += "<td><input type='image' class='delete-user'  src='delete.png' value='"+ data.id +"'></td>";
                    user += "<td><input data-toggle='modal' data-target='#myModal' type='image' class='open-modal'  src='edit.png' value='"+data.id+"'></td></tr>";


                    if (state == "add"){ //if user added a new record
                        $('#users-list').append(user);
                    }else{ //if user updated an existing record
                        $("#user" + data.id).replaceWith(user);
                    }
                    console.log("Data Saved: " + data);
                    $("#report_text").html("User Credentials saved.");
                    $('#myModal').modal('hide');
                    $("#linked_id_div").hide();
                    $('#frmUser').trigger("reset");
                 },
                 error: function(data){
                    $("#report_text").val(data.reason);
                    console.log(data);
                 }
            });
        }
    });

});


var password = document.getElementById("password")
  , confirm_password = document.getElementById("confirm_password");
var password_label= document.getElementById("password_label")
  , confirm_password_label = document.getElementById("confirm_password_label");

function validatePassword(){
    if($("#formtype").val()=="update" && password.value == "" && confirm_password.value == "")
        return true;

    console.log(password.value + "/" + confirm_password.value);
    if(password.value.length<6){
        password_label.innerHTML = "Please enter a name of length minimum 6 characters";
    }
    else{
        password_label.innerHTML = "";
    }
    console.log(confirm_password.value.length);
    if(confirm_password.value.length > 0){
        if(password.value != confirm_password.value) {
          confirm_password_label.innerHTML = "Passwords don't match!";
        } else {
          confirm_password_label.innerHTML = "";
        }        
    }

    if(password.value == confirm_password.value&&password.value.length>5){
        return true;
    }
    else{
        return false;
    }
}

password.onchange = validatePassword;
confirm_password.onkeyup = validatePassword;