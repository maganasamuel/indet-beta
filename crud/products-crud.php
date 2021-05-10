<?php
session_start();
date_default_timezone_set('Pacific/Auckland');
header('Content-Type: application/json');
require "../database.php";
$data = ""/** whatever you're serializing **/;
$date = date("Y-m-d");
$currentUser = $_SESSION['myuserid'];
//POST ROUTE


if(!empty($_POST['method'])){
  extract($_POST);
  if($method=="delete"){
      $sql = "DELETE FROM `products` WHERE `id` = " . $id; 
      $data = mysqli_query($con,$sql);
    }
  print $data;
}

if(!empty($_POST['formtype'])){
  extract($_POST);
  $acronym = addslashes($acronym);
  $name = addslashes($name);
  
    if($formtype=="add"){
      $sql ="INSERT INTO products (acronym,name) VALUES ('$acronym','$name')"; 
      //$data["responseText"] = $sql;
      $row = mysqli_query($con,$sql);
      $id = mysqli_insert_id($con);
    }
    elseif($formtype=="update"){
      $sql ="UPDATE `products` SET `name`='$name',`acronym` = '$acronym' WHERE `id` = $product_id";
      $row = mysqli_query($con,$sql);
      $id = $product_id;
    }

    $sql = "SELECT * FROM `products` WHERE `id` = " . $id . " LIMIT 1";
    $result = mysqli_query($con,$sql);
    $row = mysqli_fetch_assoc($result);
    $data = json_encode($row);
    print $data;
}

//GET ROUTE
if(!empty($_GET['id'])){
  $sql = "SELECT * FROM `products` WHERE `id` = " . $_GET['id'] . " LIMIT 1";
  $result = mysqli_query($con,$sql);
  $row = mysqli_fetch_assoc($result);
  $data = json_encode($row);
  print $data;
}
