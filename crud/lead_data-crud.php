<?php
session_start();
date_default_timezone_set('Pacific/Auckland');
header('Content-Type: application/json');

$restrict_session_check = true;
require "../database.php";
$data = ""/** whatever you're serializing **/;
$date = date("Y-m-d");
$currentUser = $_SESSION['myuserid'];
//POST ROUTE

if(!empty($_POST['method'])){
  extract($_POST);
  if($method=="delete"){
      $sql = "DELETE FROM `leads_data` WHERE `id` = " . $id; 
      $data = mysqli_query($con,$sql);
    }
  print $data;
}

if(!empty($_POST['formtype'])){
  extract($_POST);

    if($formtype=="add"){
      $sql ="INSERT INTO leads_data (name,type) VALUES ('$name','$type')"; 
      //$data["responseText"] = $sql;
    
      $row = mysqli_query($con,$sql);
      $id = mysqli_insert_id($con);
      
    }
    elseif($formtype=="update"){
        $sql = "SELECT * FROM `leads_data` WHERE `id` = " . $lead_data_id . " LIMIT 1";
        $result = mysqli_query($con,$sql);
        $row = mysqli_fetch_assoc($result);
        $lead_data = json_decode($row["data"]);
        $lead_data->appointment_date = $appointment_date;
        $lead_data->appointment_hour = $appointment_hour;
        $lead_data->appointment_minute = $appointment_minute;
        $lead_data->appointment_period = $appointment_period;
        $lead_data->venue = $venue;
        $lead_data = json_encode($lead_data);
        $lead_data = addslashes($lead_data);
        
        $date = date_create_from_format('d/m/Y', $appointment_date);
        $date = $date->format('Ymd');
        $client_id = $row["client_id"];
        
        $sql ="UPDATE `clients_tbl` SET `appt_date`='$date' WHERE `id` = $client_id";
        $row = mysqli_query($con,$sql);

        $sql ="UPDATE `leads_data` SET `data`='$lead_data' WHERE `id` = $lead_data_id";
        $row = mysqli_query($con,$sql);
        $id = $lead_data_id;
    }

    $sql = "SELECT * FROM `leads_data` WHERE `id` = " . $id . " LIMIT 1";
    $result = mysqli_query($con,$sql);
    $row = mysqli_fetch_assoc($result);
    $row["data"] = json_decode($row["data"]);
    $data = json_encode($row);
    print $data;
}

//GET ROUTE
if(!empty($_GET['id'])){
  $sql = "SELECT * FROM `leads_data` WHERE `id` = " . $_GET['id'] . " LIMIT 1";
  $result = mysqli_query($con,$sql);
  $row = mysqli_fetch_assoc($result);
  $row["data"] = json_decode($row["data"]);
  $data = json_encode($row);
  print $data;
}

