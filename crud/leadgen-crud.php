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
      $sql = "DELETE FROM `leadgen_tbl` WHERE `id` = " . $id; 
      $data = mysqli_query($con,$sql);
    }
  print $data;
}

if(!empty($_POST['formtype'])){
  extract($_POST);
  $name = addslashes($name);
  $type = addslashes($type);

    if($formtype=="add"){
      $sql ="INSERT INTO leadgen_tbl (name,type) VALUES ('$name','$type')"; 
      //$data["responseText"] = $sql;
    
      $row = mysqli_query($con,$sql);
      $id = mysqli_insert_id($con);
      
    }
    elseif($formtype=="update"){
      $sql ="UPDATE `leadgen_tbl` SET `name`='$name', `type` = '$type' WHERE `id` = $leadgen_id";
      $row = mysqli_query($con,$sql);
      $id = $leadgen_id;
    }

    $sql = "SELECT * FROM `leadgen_tbl` WHERE `id` = " . $id . " LIMIT 1";
    $result = mysqli_query($con,$sql);
    $row = mysqli_fetch_assoc($result);
    $data = json_encode($row);

    print $data;
}

//GET ROUTE
if(!empty($_GET['id'])){
  $sql = "SELECT * FROM `leadgen_tbl` WHERE `id` = " . $_GET['id'] . " LIMIT 1";
  $result = mysqli_query($con,$sql);
  $row = mysqli_fetch_assoc($result);
  $data = json_encode($row);
  print $data;
}

