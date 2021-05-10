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
      $sql = "DELETE FROM `callbacks` WHERE `id` = " . $id; 
      $data = mysqli_query($con,$sql);
    }
  print $data;
}

//GET ROUTE
if(!empty($_GET['id'])){
  $sql = "SELECT * FROM `callbacks` WHERE `id` = " . $_GET['id'] . " LIMIT 1";
  $result = mysqli_query($con,$sql);
  $row = mysqli_fetch_assoc($result);
  $data = json_encode($row);
  print $data;
}
