<?php
session_start();
header('Content-Type: application/json');
require "../database.php";
$data = ""/** whatever you're serializing **/;
$date = date("Y-m-d");
$currentUser = $_SESSION['myuserid'];
//POST ROUTE


if(!empty($_POST['method'])){
  extract($_POST);
  if($method=="delete"){
      $sql = "DELETE FROM `insurance_types` WHERE `id` = " . $id; 
      $data = mysqli_query($con,$sql);
    }
  print $data;
}

if(!empty($_POST['formtype'])){
  extract($_POST);
  $acronym = addslashes($acronym);
  $description = addslashes($description);
    if($formtype=="add"){
      $sql ="INSERT INTO insurance_types (acronym, description) VALUES ('$acronym','$description')"; 
      //$data["reason"] = $sql;
      $row = mysqli_query($con,$sql);
      $id = mysqli_insert_id($con);
    }
    elseif($formtype=="update"){
      $sql ="UPDATE `insurance_types` SET `acronym`='$acronym', `description` = '$description' WHERE `id` = $type_id";
      $row = mysqli_query($con,$sql);
      $id = $type_id;
    }

    $sql = "SELECT * FROM `insurance_types` WHERE `id` = " . $id . " LIMIT 1";
    $result = mysqli_query($con,$sql);
    $row = mysqli_fetch_assoc($result);
    $data = json_encode($row);
    //$data = json_encode($data);
    print $data;
}

//GET ROUTE
if(!empty($_GET['id'])){
  $sql = "SELECT * FROM `insurance_types` WHERE `id` = " . $_GET['id'] . " LIMIT 1";
  $result = mysqli_query($con,$sql);
  $row = mysqli_fetch_assoc($result);
  $data = json_encode($row);
  print $data;
}
