<?php
$restrict_session_check = true;
require("database.php");
require_once "libs/indet_dates_helper.php";
date_default_timezone_set('Pacific/Auckland');

if(!empty($_POST)){
    extract($_POST);
    $date_helper = new INDET_DATES_HELPER();
    $errors = 0;
    $notes = addslashes($notes);
    $notes = str_replace("\r\n", "<br>", $notes);
    
    $sql = "SELECT * FROM `leads_data` WHERE `id` = " . $lead_data_id; 
    $res = mysqli_query($con,$sql);
    $row = mysqli_fetch_assoc($res);
    
    $name = $row["name"];
    $agent_id = $row["agent_id"];
    
    $sql = "DELETE FROM `clients_tbl` WHERE `id` = " . $client_id; 
    mysqli_query($con,$sql);     
    


    //Insert into leads_data
    $sql="INSERT INTO `callbacks` (leads_data_id,agent_id,name,callback_date, callback_time,notes) 
    VALUES ($lead_data_id,$agent_id,'$name','$callback_date', '$callback_time', '$notes')"; 
    if ($result = mysqli_query($con,$sql)) {
        $leads_data_id = mysqli_insert_id($con);
    }else{
        echo $sql;
        $errors++;
    }
    
    if ($errors>0) {
        echo "Errors found " . $errors;
    }else{
        echo $client_id;
        //echo "Error deleting record: " . mysqli_error($con);
    }    
}
else{
    print "Error: No ID found.";
}
?>