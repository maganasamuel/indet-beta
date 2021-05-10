var scripts;
var insurance_companies;
var insurance_types;
var products;

var client_current_insurance_count = 1;
var client_current_landline_count = 1;
var client_current_mobile_count = 1;
var client_current_email_count = 1;
var partner_current_insurance_count = 1;
var lead_data;

function LoadScripts(array){
    scripts = array.slice();
}

function LoadInsuranceCompanies(array){
    insurance_companies = array.slice();
}

function LoadInsuranceTypes(array){
    insurance_types = array.slice();
}

function LoadProducts(array){
    products = array.slice();
}

function LoadLeadData(json){
    lead_data = JSON.parse(json);
    $("#frmLead").slideDown();

}

function NewClientInsurance(){
    var insurance_row = '<div class="row" id="client_current_insurance' + client_current_insurance_count + '">\
                            <div class="form-group col-sm-5">\
                                <label for="client_current_insurance_type' + client_current_insurance_count + '">Insurance For:</label>\
                                <select class="form-control client_insurance_type" id="client_current_insurance_type' + client_current_insurance_count + '" name="client_current_insurance_type' + client_current_insurance_count + '" aria-describedby="civil_status">\
                                    <option value="" disabled="" hidden="" selected="">Select One</option>\
                                </select>\
                            </div>\
                            <div class="form-group col-sm-5">\
                                <label for="client_current_insurance_company' + client_current_insurance_count + '">Insurance Company:</label>\
                                <select class="form-control client_insurance_company" id="client_current_insurance_company' + client_current_insurance_count + '" name="client_current_insurance_company' + client_current_insurance_count + '" aria-describedby="civil_status">\
                                    <option value="" disabled="" hidden="" selected="">Select One</option>\
                                </select>\
                            </div>\
                            <div class="col-sm-2">\
                                <button type="button" class="btn btn-danger form-control remove_client_current_insurance" style="margin-top:20px;" data-index="' + client_current_insurance_count + '"><i class="fas fa-times"></i></button>\
                            </div>\
                        </div>';
    
    $("#client_current_insurance_div").append(insurance_row);

    $.each(insurance_types, function (i, item) {
        $('#client_current_insurance_type'+ client_current_insurance_count).append($('<option>', { 
            //value: item.name,
            text : item.acronym 
        }));
    });

    $.each(insurance_companies, function (i, item) {
        $('#client_current_insurance_company'+ client_current_insurance_count).append($('<option>', { 
            //value: item.name,
            text : item.acronym 
        }));
    });

    $('#client_current_insurance_company'+ client_current_insurance_count).attr("name","client_current_insurance_company");
    $('#client_current_insurance_type'+ client_current_insurance_count).attr("name","client_current_insurance_type");

    client_current_insurance_count++;
}

function NewPartnerInsurance(){
    var insurance_row = '<div class="row" id="partner_current_insurance' + partner_current_insurance_count + '">\
                            <div class="form-group col-sm-5">\
                            <label for="partner_current_insurance_type">Insurance For:</label>\
                            <select class="form-control partner_insurance_type" id="partner_current_insurance_type' + partner_current_insurance_count + '" name="partner_current_insurance_type' + partner_current_insurance_count + '" aria-describedby="civil_status">\
                                <option value="" disabled="" hidden="" selected="">Select One</option>\
                            </select>\
                        </div>\
                        <div class="form-group col-sm-5">\
                            <label for="partner_current_insurance_company' + partner_current_insurance_count + '">Insurance Company:</label>\
                            <select class="form-control partner_insurance_company" id="partner_current_insurance_company' + partner_current_insurance_count + '" name="partner_current_insurance_company' + partner_current_insurance_count + '" aria-describedby="civil_status">\
                                <option value="" disabled="" hidden="" selected="">Select One</option>\
                            </select>\
                        </div>\
                        <div class="col-sm-2">\
                            <button type="button" class="btn btn-danger form-control remove_partner_current_insurance" style="margin-top:20px;" data-index="' + partner_current_insurance_count + '"><i class="fas fa-times"></i></button>\
                        </div>\
                    </div>';
    
    $("#partner_current_insurance_div").append(insurance_row);

    $.each(insurance_types, function (i, item) {
        
        console.log(item);
        $('#partner_current_insurance_type'+ partner_current_insurance_count).append($('<option>', { 
            //value: item.name,
            text : item.acronym 
        }));
    });

    $.each(insurance_companies, function (i, item) {
        $('#partner_current_insurance_company'+ partner_current_insurance_count).append($('<option>', { 
            //value: item.name,
            text : item.acronym 
        }));
    });

    $('#partner_current_insurance_company'+ partner_current_insurance_count).attr("name","partner_current_insurance_company");
    $('#partner_current_insurance_type'+ partner_current_insurance_count).attr("name","partner_current_insurance_type");

    partner_current_insurance_count++;
}

function NewClientLandline(){
    var html = '<div class="form-group row" id="client_landline_div' + client_current_landline_count + '">\
                            <div class="col-sm-9">\
                                <input type="text" class="form-control client_landline" id="client_landline' + client_current_landline_count + '"  name="client_landline" style="margin-top:20px;" placeholder="Landline #">\
                            </div>\
                            <div class="col-sm-3">\
                                <button type="button" class="btn btn-danger form-control remove_client_landline" style="margin-top:20px;" data-index="' + client_current_landline_count + '"><i class="fas fa-times"></i></button>\
                            </div>\
                        </div>';
    
    $("#landline_numbers_div").append(html);

    client_current_landline_count++;
}

function NewClientMobile(){
    var html = '<div class="form-group row" id="client_mobile_div' + client_current_mobile_count + '">\
                            <div class="col-sm-9">\
                                <input type="text" class="form-control client_mobile" id="client_mobile' + client_current_mobile_count + '"  name="client_mobile" style="margin-top:20px;" placeholder="Mobile #">\
                            </div>\
                            <div class="col-sm-3">\
                                <button type="button" class="btn btn-danger form-control remove_client_mobile" style="margin-top:20px;" data-index="' + client_current_mobile_count + '"><i class="fas fa-times"></i></button>\
                            </div>\
                        </div>';
    
    $("#mobile_numbers_div").append(html);

    client_current_mobile_count++;
}

function NewClientEmail(){
    var html = '<div class="form-group row" id="client_email_div' + client_current_email_count + '">\
                            <div class="col-sm-9">\
                                <input type="text" class="form-control client_email" id="client_email' + client_current_email_count + '"  name="client_email" style="margin-top:20px;" placeholder="Email">\
                            </div>\
                            <div class="col-sm-3">\
                                <button type="button" class="btn btn-danger form-control remove_client_email" style="margin-top:20px;" data-index="' + client_current_email_count + '"><i class="fas fa-times"></i></button>\
                            </div>\
                        </div>';
    
    $("#emails_div").append(html);

    client_current_email_count++;
}

function LoadLeadDataIntoForm(){
    //Appointment Data
    $("#venue_type").val(lead_data.venue_type);
    if($("#venue_type").val()=="Other"){
        $("#venue").val("");
        $("#venue").slideDown();
    }
    else{
        $("#venue").slideUp();                    
    }
    $("#venue").val(lead_data.venue);
    $("#appointment_date").val(lead_data.appointment_date);
    $("#appointment_hour").val(lead_data.appointment_hour);
    $("#appointment_time").val(lead_data.appointment_time);
    $("#appointment_period").val(lead_data.appointment_period);

    //Lead Data
    $("#client_name").val(lead_data.client_name);
    $("#client_company_name").val(lead_data.company_name);
    $('input:radio[name=client_gender]').val([lead_data.client_gender]);
    $("#client_address").val(lead_data.client_address);
    $("#company_address").val(lead_data.company_address);
    $("#client_city").val(lead_data.client_city);
    $("#client_zipcode").val(lead_data.client_zipcode);

    if(Array.isArray(lead_data.client_mobile)){
        //Add Current Insurance Fields depending on the number of current insurances data
        for(var i = 0; i < (lead_data.client_mobile.length - 1); i++){
            NewClientMobile();
        }

        //Load the data into the client's current insurance fields 
        $(".client_mobile").each(function(index,item){
            item.value = lead_data.client_mobile[index];
        });
    }
    else{
        $("#client_mobile0").val(lead_data.client_mobile);
    }

    if(Array.isArray(lead_data.client_landline)){
        //Add Current Insurance Fields depending on the number of current insurances data
        for(var i = 0; i < (lead_data.client_landline.length - 1); i++){
            NewClientLandline();
        }

        //Load the data into the client's current insurance fields 
        $(".client_landline").each(function(index,item){
            item.value = lead_data.client_landline[index];
        });
    }
    else{
        $("#client_landline0").val(lead_data.client_landline);
    }

    if(Array.isArray(lead_data.client_email)){
        //Add Current Insurance Fields depending on the number of current insurances data
        for(var i = 0; i < (lead_data.client_email.length - 1); i++){
            NewClientEmail();
        }

        //Load the data into the client's current insurance fields 
        $(".client_email").each(function(index,item){
            item.value = lead_data.client_email[index];
        });
    }
    else{
        $("#client_email0").val(lead_data.client_email);
    }

    $("#client_civil_status").val(lead_data.client_civil_status);
    $("#client_dependents").val(lead_data.client_dependents);
    $("#client_age").val(lead_data.client_age);
    $("#client_occupation").val(lead_data.client_occupation);
    $("#client_income").val(lead_data.client_income);
    $('input:radio[name=client_citizenship]').val([lead_data.client_citizenship]);
    if(lead_data.client_citizenship=="Working VISA"){        
        $("#working_visa_years_div").slideDown();
        $("#working_visa_years").val(lead_data.working_visa_years);
    }
    
    $(".client_health_concerns").each(function(index,item){
        item.value = (lead_data.client_health_concerns[index]!=undefined) ? lead_data.client_health_concerns[index] : "";
    });

    //Current Insurances
    var client_has_insurance = (lead_data.client_has_insurance=="on") ? true : false;

    $('#client_already_has_insurance').prop('checked', client_has_insurance);

    //Client Is Smoking
    var client_is_smoking = (lead_data.client_is_smoking=="on") ? true : false;

    $('#client_is_smoking').prop('checked', client_is_smoking);

    if(client_has_insurance){
        $("#add_client_current_insurance").slideDown();
        $("#client_current_insurance_div").slideDown();
        if(Array.isArray(lead_data.client_current_insurance_company)){
            //Add Current Insurance Fields depending on the number of current insurances data
            for(var i = 0; i < (lead_data.client_current_insurance_company.length - 1); i++){
                NewClientInsurance();
            }

            //Load the data into the client's current insurance fields 
            $(".client_insurance_type").each(function(index,item){
                item.value = lead_data.client_current_insurance_type[index];
            });

            $(".client_insurance_company").each(function(index,item){
                item.value = lead_data.client_current_insurance_company[index];
            });
        }
        else{
            $("#client_current_insurance_type0").val(lead_data.client_current_insurance_type);
            $("#client_current_insurance_company0").val(lead_data.client_current_insurance_company);
        }

    }
    else{
        $("#add_client_current_insurance").slideUp();
        $("#client_current_insurance_div").slideUp();
    }

    //Load Client's Health Concerns Data
    $.each(function(index,item){
        item.value = lead_data.client_health_concerns[index];
    });

    //Check what products the client is interested in:
    $.each(products, function (i, item) {
        var acronym = item.acronym;
        if(acronym==""){
            acronym = item.name;
        }
        
        var interested_id = "client_interested_in_" + item.safe_name;
        eval("var client_interested_in = (lead_data." + interested_id + " == 'on') ? true : false;");
        $('#' + interested_id).prop('checked', client_interested_in);
    });
        
    //Has Partner
    var has_partner = (lead_data.has_partner=="on") ? true : false;
    $('#partner_toggle').prop('checked', has_partner);

    if(has_partner){
        $("#partner_div").slideDown();
        
        //Partner Data
        $("#partner_name").val(lead_data.partner_name);
        $("#partner_company_name").val(lead_data.company_name);
        $('input:radio[name=partner_gender]').val([lead_data.partner_gender]);
        
        /*
        $("#partner_address").val(lead_data.partner_address);
        $("#partner_city").val(lead_data.partner_city);
        $("#partner_zipcode").val(lead_data.partner_zipcode);
        */

        $("#partner_mobile").val(lead_data.partner_mobile);
        $("#partner_telephone").val(lead_data.partner_telephone);
        $("#partner_email").val(lead_data.partner_email);
        $("#partner_civil_status").val(lead_data.partner_civil_status);
        $("#partner_dependents").val(lead_data.partner_dependents);
        $("#partner_age").val(lead_data.partner_age);
        $("#partner_occupation").val(lead_data.partner_occupation);
        $("#partner_income").val(lead_data.partner_income);


        $(".partner_health_concerns").each(function(index,item){
            item.value = lead_data.partner_health_concerns[index];
        });
    
        //Current Insurances
        var partner_has_insurance = (lead_data.partner_has_insurance=="on") ? true : false;

        $('#partner_already_has_insurance').prop('checked', partner_has_insurance);
        
        //Partner Is Smoking
        var partner_is_smoking = (lead_data.partner_is_smoking=="on") ? true : false;

        $('#partner_is_smoking').prop('checked', partner_is_smoking);
        
        if(partner_has_insurance){
            $("#add_partner_current_insurance").slideDown();
            $("#partner_current_insurance_div").slideDown();

            if(Array.isArray(lead_data.partner_current_insurance_company)){
                //Add Current Insurance Fields depending on the number of current insurances data
                for(var i = 0; i < (lead_data.partner_current_insurance_company.length - 1); i++){
                    NewPartnerInsurance();
                }

                //Load the data into the partner's current insurance fields 
                $(".partner_insurance_type").each(function(index,item){
                    console.log(lead_data.partner_current_insurance_type[index]);
                    item.value = lead_data.partner_current_insurance_type[index];
                });

                $(".partner_insurance_company").each(function(index,item){
                    item.value = lead_data.partner_current_insurance_company[index];
                });
            }
            else{
                $("#partner_current_insurance_type0").val(lead_data.partner_current_insurance_type);
                $("#partner_current_insurance_company0").val(lead_data.partner_current_insurance_company);
            }
            
    
        }
        else{
            $("#add_partner_current_insurance").slideUp();
            $("#partner_current_insurance_div").slideUp();
        }
    
        //Load Client's Health Concerns Data
        $.each(function(index,item){
            item.value = lead_data.partner_health_concerns[index];
        });
    
        //Check what products the partner is interested in:
        $.each(products, function (i, item) {
            var acronym = item.acronym;
            if(acronym==""){
                acronym = item.name;
            }
            
            var interested_id = "partner_interested_in_" + item.safe_name;
            eval("var partner_interested_in = (lead_data." + interested_id + " == 'on') ? true : false;");
            $('#' + interested_id).prop('checked', partner_interested_in);
        });
    }

    lead_data.notes = lead_data.notes.replace(/<br>/g,"\r\n");
    //Other Data
    $("#notes").val(lead_data.notes);
}

$(document).ready(function(){

    $(".script_button").on("click", function(){
        var script_index = $(this).data("index");
        console.log(script_index);
        $("#script_header").html(scripts[script_index]);
    });

    $("#add_client_current_insurance").on("click", function(){
        NewClientInsurance();
    });

    $("#client_current_insurance_div").on("click", ".remove_client_current_insurance", function(){
        var insurance_index = $(this).data("index");
        console.log(insurance_index);
        $("#client_current_insurance" + insurance_index).remove();
    });

    $("#add_partner_current_insurance").on("click", function(){
        NewPartnerInsurance();
    });

    $("#partner_current_insurance_div").on("click", ".remove_partner_current_insurance", function(){
        var insurance_index = $(this).data("index");
        console.log(insurance_index);
        $("#partner_current_insurance" + insurance_index).remove();
    });

    $("#add_landline").on("click", function(){
        NewClientLandline();
    });

    $("#landline_numbers_div").on("click", ".remove_client_landline", function(){
        var html_index = $(this).data("index");
        $("#client_landline_div" + html_index).remove();
    });

    $("#add_mobile").on("click", function(){
        NewClientMobile();
    });

    $("#mobile_numbers_div").on("click", ".remove_client_mobile", function(){
        var html_index = $(this).data("index");
        $("#client_mobile_div" + html_index).remove();
    });

    $("#add_email").on("click", function(){
        NewClientEmail();
    });

    $("#emails_div").on("click", ".remove_client_email", function(){
        var html_index = $(this).data("index");
        $("#client_email_div" + html_index).remove();
    });
});