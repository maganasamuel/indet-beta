$(document).ready(function(){

    var url = "crud/clients-crud.php";

    //display modal form for model editing
    $(document).on("click", ".open-modal", function(e){
        e.preventDefault();
        var mat_id = $(this).val();
        $("#status_div").show();

        console.log(mat_id);
        $.get(url + '/?id=' + mat_id, function (data) {            
            console.log(data);
            $('#client_id').html(data.id);
            $('#name').html("Name: " + data.name);

            var appt_date = TimestampToNZFormat(data.appt_date);
            var assigned_date = TimestampToNZFormat(data.assigned_date);
            var date_submitted = TimestampToNZFormat(data.date_submitted);
            var notes = data.notes.replace(/(?:\r\n|\r|\n)/g, '<br>');
            $('#date_submitted').html("Date Generated: " + date_submitted);
            $('#appt_date').html("Appointment Date: " + appt_date);
            $('#assigned_date').html("Date Assigned: " + assigned_date);
            $("#phone_num").html("Phone Number: " + data.appt_time);
            $("#address").html("Address: " + data.address);
            $("#lead_by").html("Source: " + data.lead_by);
            $("#leadgen").html("Lead Generator: "  + data.leadgen_name);

            $("#notes").html(notes);
            $('#formtype').html("update");
        });
    });
    
    //display modal form for model editing
    $(document).on("click", ".mark-seen", function(e){
        e.preventDefault();
        var mat_id = $(this).val();
        
        console.log(mat_id);
        var data = {
            client_id : mat_id,
            seen : "Seen",
            notes : "",
            formtype : "update"
        };
        $.confirm({
            title:'Confirm',
            content:'Are you sure you want to mark this as Seen?',
            buttons:{
                confirm:function(){               
                    $.ajax({
                        data: data,
                        type: "post",
                        url: "crud/leads-assigned-crud",
                        success: function(data){
                        var appt_date = TimestampToNZFormat(data.appt_date);
                        var newData = {
                            "0" : "<button class='mark-scheduled pull-right btn btn-link btn-lg' value='"+ data.id +"'><i class='fa fa-eye text-warning'></i></button>",
                            "1" : "<button data-toggle='modal' data-target='#confirmModal' class='mark-not-seen pull-right btn btn-link btn-lg' value='"+ data.id +"'><i class='fa fa-eye text-danger'></i></button>",
                            "2" : "<button data-toggle='modal' data-target='#myModal' type='image' class='open-modal pull-right btn btn-link btn-lg' value='"+ data.id +"'><i class='fa fa-search'></i></button>",
                            "3" : data.name,
                            "4" : {
                                "display" : appt_date,
                                "@data-order" : data.appt_date
                            },
                            "5" : data.appt_time,
                            "6" : data.address
                        };

                            //remove row
                            $("#client" + data.id).remove();

                            //add new row to seen table
                            tbl_r = seen_table.row.add(newData).node().id = "client"+data.id;
                            seen_table.row("#client" + data.id).data(newData).draw(false);

                        },
                        error: function(data){
                            console.log("Error", data);
                            $('#myModal').modal('hide');
                        }
                    });
                },
                cancel:function(){
                }	
            }
        });
    });   

    //display modal form for model editing
    $(document).on("click", ".mark-scheduled", function(e){
        e.preventDefault();
        var mat_id = $(this).val();
        
        console.log(mat_id);
        var data = {
            client_id : mat_id,
            seen : "Scheduled",
            notes : "",
            formtype : "update"
        };
        $.confirm({
            title:'Confirm',
            content:'Are you sure you want to mark this as Scheduled?',
            buttons:{
                confirm:function(){               
                    $.ajax({
                        data: data,
                        type: "post",
                        url: "crud/leads-assigned-crud",
                        success: function(data){
                        var appt_date = TimestampToNZFormat(data.appt_date);
                        var newData = {
                            "0" : "<button class='mark-seen pull-right btn btn-link btn-lg' value='"+ data.id +"'><i class='fa fa-eye text-success'></i></button>",
                            "1" : "<button data-toggle='modal' data-target='#confirmModal' class='mark-not-seen pull-right btn btn-link btn-lg' value='"+ data.id +"'><i class='fa fa-eye text-danger'></i></button>",
                            "2" : "<button data-toggle='modal' data-target='#myModal' type='image' class='open-modal pull-right btn btn-link btn-lg' value='"+ data.id +"'><i class='fa fa-search'></i></button>",
                            "3" : data.name,
                            "4" : {
                                "display" : appt_date,
                                "@data-order" : data.appt_date
                            },
                            "5" : data.appt_time,
                            "6" : data.address
                        };

                            //remove row
                            $("#client" + data.id).remove();

                            //add new row to seen table
                            tbl_r = scheduled_table.row.add(newData).node().id = "client"+data.id;
                            scheduled_table.row("#client" + data.id).data(newData).draw(false);

                        },
                        error: function(data){
                            console.log("Error", data);
                            $('#myModal').modal('hide');
                        }
                    });
                },
                cancel:function(){
                }	
            }
        });
    });   

 //display modal form for model editing
 $(document).on("click", ".mark-not-seen", function(e){
    e.preventDefault();
    var mat_id = $(this).val();
    $("#client_not_seen").val(mat_id);
    $("#notes").val("");    
});   

$("#filter").click(function(){
    var from = $("#from").val();
    var to = $("#to").val();
    from = NZFormatToTimestamp(from);
    to = NZFormatToTimestamp(to);
    window.location = "leads_assigned?from=" + from + "&to=" + to; 
});

$(document).on("click", ".confirm-not-seen", function(e){
    e.preventDefault();
    var mat_id = $("#client_not_seen").val();
    
    console.log(mat_id);
    var data = {
        client_id : mat_id,
        seen : "Not Seen",
        notes : $("#seen_notes").val(),
        formtype : "update"
    };
    
    if($("#seen_notes").val()!=""){
        $.ajax({
            data: data,
            type: "post",
            url: "crud/leads-assigned-crud",
            success: function(data){
            var appt_date = TimestampToNZFormat(data.appt_date);
            console.log(appt_date);

            var newData = {
                "0" : "<button class='mark-seen pull-right btn btn-link btn-lg' value='"+ data.id +"'><i class='fa fa-eye text-success'></i></button>",
                "1" : "<button class='mark-scheduled pull-right btn btn-link btn-lg' value='"+ data.id +"'><i class='fa fa-eye text-warning'></i></button>",
                "2" : "<button data-toggle='modal' data-target='#myModal' type='image' class='open-modal pull-right btn btn-link btn-lg' value='"+ data.id +"'><i class='fa fa-search'></i></button>",
                "3" : data.name,
                "4" : {
                    "display" : appt_date,
                    "@data-order" : data.appt_date
                },
                "5" : data.appt_time,
                "6" : data.address
            };
    
                //remove row
                $("#client" + data.id).remove();
    
                //add new row to seen table
                tbl_r = not_seen_table.row.add(newData).node().id = "client"+data.id;
                not_seen_table.row("#client" + data.id).data(newData).draw(false);
    
                $('#confirmModal').modal('hide');
            },
            error: function(data){
                console.log("Error", data);
                $('#confirmModal').modal('hide');
            }
        });
    }
    else{
        alert("You need to fill up the notes before proceeding");
    }
    
});   


    //display modal form for creating new model
    $('#btn-add').click(function(){
        $("#status_div").hide();
        $('#frmClient').trigger("reset");
        $('#formtype').val("add");
        $('#lead_by').val("");
        $('#leadgen').val("0");
        $('#assigned_to').val("0");
        $(".leadgen").val("0");
        $(".leadgen").hide();
        $('#date_submitted').datepicker().datepicker("setDate", new Date());  
        $('#date_status_updated').datepicker().datepicker("setDate", new Date());  

    });

    $('#btn-delete-cancel').on("click", function(){
        $('#confirmModal').modal('hide');
    });

    $('#clients-list').on("click", ".delete-client", function(){
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
                        "3" : appt_date,
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

    function NZFormatToTimestamp(timestamp){       
        return timestamp.substring(6,10) + timestamp.substring(3,5) + timestamp.substring(0,2);
    }
});
