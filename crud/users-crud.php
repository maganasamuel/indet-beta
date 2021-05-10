<?php
session_start();
header('Content-Type: application/json');
require "../database.php";
$data = ""
  /** whatever you're serializing **/
;
$date = date("Y-m-d");
$currentUser = $_SESSION['myuserid'];
//POST ROUTE


if (!empty($_POST['method'])) {
  extract($_POST);
  if ($method == "delete") {
    $sql = "DELETE FROM `users` WHERE `id` = " . $id;
    $data = mysqli_query($con, $sql);
  }
  print $data;
}

if (!empty($_POST['formtype'])) {
  extract($_POST);
  
  if (!empty($password))
    $password = password_hash($password, PASSWORD_BCRYPT);

  if ($formtype == "add") {
    if ($type != "Telemarketer" && $type != "Adviser") {
      $linked_id = 0;
    }
    $sql = "INSERT INTO users (username,password,type,created_at,created_by,updated_at,updated_by,linked_id) VALUES ('$username','$password','$type','$date','$currentUser','$date','$currentUser','$linked_id')";
    $row = mysqli_query($con, $sql);
    $id = mysqli_insert_id($con);
  } elseif ($formtype == "update") {
    $sql = "UPDATE `users` SET `username` = '$username'";

    if (!empty($password)) {
      $sql .= ", `password` = '$password'";
    }

    if (!empty($type)) {
      $sql .= ", `type` = '$type'";
    }

    if (!empty($type)) {
      $sql .= ", `linked_id` = '$linked_id'";
    }

    $sql .= ", `updated_at` = '$date', `updated_by` = '$currentUser' WHERE `id` = $user_id";
    $row = mysqli_query($con, $sql);
    $id = $user_id;
  }

  $sql = "SELECT * FROM `users` WHERE `id` = " . $id . " LIMIT 1";
  $row = mysqli_query($con, $sql);
  $data = array();
  while ($r = mysqli_fetch_assoc($row)) {
    $data[] = $r;
  }
  $data = json_encode($data[0]);
  print $data;
}

//GET ROUTE
if (!empty($_GET['id'])) {
  $sql = "SELECT * FROM `users` WHERE `id` = " . $_GET['id'] . " LIMIT 1";
  $row = mysqli_query($con, $sql);
  $data = array();
  while ($r = mysqli_fetch_assoc($row)) {
    $data[] = $r;
  }
  $data = json_encode($data[0]);
  print $data;
}
