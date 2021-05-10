<?php
    require("database.php");
    require_once "libs/indet_dates_helper.php";
    date_default_timezone_set('Pacific/Auckland');
    extract($_POST);

    $notes = addslashes($notes);
    $notes = str_replace("\r\n", "<br>", $notes);

    $date_helper = new INDET_DATES_HELPER();

    $errors = 0;

    if($is_update=="Yes"){
        $sql = "SELECT * FROM `callbacks` WHERE `id` = " . $callback_id; 
        $res = mysqli_query($con,$sql);
        $row = mysqli_fetch_assoc($res);

        $leads_data_id = $row["leads_data_id"];

        $sql = "DELETE FROM `callbacks` WHERE `id` = " . $callback_id; 
        mysqli_query($con,$sql);    

        $sql = "DELETE FROM `leads_data` WHERE `id` = " . $leads_data_id; 
        mysqli_query($con,$sql);        
    }

    $client_data = json_decode($lead_data);
    $appt_date = $date_helper->DateTimeToNZEntry($client_data->appointment_date);
    $address= FetchAddress($client_data->client_address, $client_data->company_address);
    $city = $client_data->client_city;
    $zipcode = $client_data->client_zipcode;
    $lead_by = "Telemarketer";
    $leadgen = $agent_id;
    $assigned_to = 0;
    $assigned_date = "";
    $creation_date = date("Y-m-d H:i:s");
    $date_submitted = date("Ymd");
    $client_id = 0;
    $phone = "";
    if(is_array($client_data->client_mobile)){
        $phone = $client_data->client_mobile[0];
    }
    else{
        $phone = $client_data->client_mobile;
    }


    //Insert into clients tbl
    $sql ="INSERT INTO clients_tbl (name,appt_date,appt_time,address,city,zipcode,lead_by,leadgen,assigned_to,assigned_date,notes,date_submitted,creation_date) 
    VALUES ('$name','$appt_date','$phone','$address','$city','$zipcode','$lead_by','$leadgen','$assigned_to','$assigned_date','$notes','$date_submitted','$creation_date')";
    if ($result = mysqli_query($con,$sql)) {
        $client_id = mysqli_insert_id($con);
    }else{
        echo $sql;
        $errors++;
    } 

    $lead_data = json_decode($lead_data);
    $lead_data->notes = str_replace("\r\n", "<br>", $lead_data->notes);
    $lead_data = json_encode($lead_data);
    $lead_data = addslashes($lead_data);

    $leads_data_id = 0;
    //Insert into leads_data
    $sql="INSERT INTO leads_data (name,data,notes,agent_id, client_id) 
    VALUES ('$name','$lead_data','$notes',$agent_id, $client_id)"; 
    if ($result = mysqli_query($con,$sql)) {
        $leads_data_id = mysqli_insert_id($con);
    }else{
        echo $sql;
        $errors++;
    } 

    if ($errors>0) {
        echo "Errors found " . $errors;
    }else{
        echo $leads_data_id;
        //echo "Error deleting record: " . mysqli_error($con);
    }

    function FetchAddress($client_address, $company_address){
        if(strtolower($client_address) =="n/a" || $client_address == ""){
            if(strtolower($company_address) =="n/a" || $company_address == ""){
                return "n/a";
            }
            return $company_address;
        }
        else{
            return $client_address;
        }
    }
?>