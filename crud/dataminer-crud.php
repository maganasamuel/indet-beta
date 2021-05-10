<?php
session_start();
date_default_timezone_set('Pacific/Auckland');
header('Content-Type: application/json');
$restrict_session_check = true;
require "../database.php";

//$data = array();/** whatever you're serializing **/;
$date = date("Y-m-d");


//POST ROUTE


if (!empty($_POST['method'])) {
  extract($_POST);
  if ($method == "delete") {
    $sql = "DELETE FROM `datamined_numbers` WHERE `id` = " . $id;
    $data = mysqli_query($con, $sql);
  }
  print $data;
}

if (!empty($_POST['formtype'])) {
  extract($_POST);

  if (isset($name))
    $name = addslashes($name);

  if (isset($number))
    $number = addslashes($number);

  if ($formtype == "add") {
    $sql = "INSERT INTO datamined_numbers (`name`, `number`,`status`,`agent_id`) VALUES ('$name','$number','$status','$agent_id')";

    $row = mysqli_query($con, $sql);
    $id = mysqli_insert_id($con);
  } elseif ($formtype == "update") {
    $sql = "UPDATE `datamined_numbers` SET `name`='$name', `number` = '$number', `status` = '$status' WHERE `id` = $data_id";
    $row = mysqli_query($con, $sql);
    $id = $data_id;
  }

  $sql = "SELECT * FROM `datamined_numbers` WHERE `id` = " . $id . " LIMIT 1";
  $result = mysqli_query($con, $sql);
  $row = mysqli_fetch_assoc($result);
  $data = json_encode($row);
  print $data;
}

//GET ROUTE
if (!empty($_GET['id'])) {
  $sql = "SELECT * FROM `datamined_numbers` WHERE `id` = " . $_GET['id'] . " LIMIT 1";
  $result = mysqli_query($con, $sql);
  $row = mysqli_fetch_assoc($result);
  $data = json_encode($row);
  print $data;
}
