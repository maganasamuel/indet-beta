<?php
require("database.php");
date_default_timezone_set('Pacific/Auckland');
extract($_POST);

$notes = addslashes($notes);
$notes = str_replace("\r\n", "<br>", $notes);

$callback_notes = addslashes($callback_notes);
$callback_notes = str_replace("\r\n", "<br>", $callback_notes);


$lead_data = json_decode($lead_data);
$lead_data->notes = str_replace("\r\n", "<br>", $lead_data->notes);
$lead_data = json_encode($lead_data);

$lead_data = addslashes($lead_data);

$errors = 0;

if($is_update=="No"){
    //Insert into record
    $sql='INSERT INTO leads_data (name,data,notes,agent_id) VALUES ("' . $name . '","' . $lead_data . '","' . $notes . '",' . $agent_id . ')'; 
    if ($result = mysqli_query($con,$sql)) {
    }else{
        echo $sql;
        $errors++;
    } 
    
    $last_id = mysqli_insert_id($con);

    //Insert into record
    $sql="INSERT INTO callbacks (leads_data_id,agent_id,name,callback_date,callback_time,notes) 
    VALUES ($last_id,$agent_id,'$name','$callback_date','$callback_time','$callback_notes')"; 
    if ($result = mysqli_query($con,$sql)) {
    }else{
        echo $sql;
        $errors++;
    } 
}
else{
    $sql =  "SELECT * FROM callbacks where ID = $callback_id";
    $result = mysqli_query($con,$sql);
    $row = mysqli_fetch_assoc($result);
    
    $leads_data_id = $row["leads_data_id"];

    $sql = "UPDATE callbacks SET name='$name', callback_date='$callback_date', callback_time='$callback_time', notes='$callback_notes' WHERE id=$callback_id";
    if ($result = mysqli_query($con,$sql)) {
    }else{
        echo $sql;
        $errors++;
    } 

    $sql = "UPDATE leads_data SET name='$name', notes='$notes', data='$lead_data' WHERE id=$leads_data_id";
    if ($result = mysqli_query($con,$sql)) {
    }else{
        echo $sql;
        $errors++;
    } 
}



if ($errors>0) {
    echo "Errors found " . $errors;
}else{
	echo "Data Saved Successfully";
    //echo "Error deleting record: " . mysqli_error($con);

}

?>