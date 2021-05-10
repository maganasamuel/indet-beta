<?php
session_start();
header('Content-Type: application/json');
$restrict_session_check = true;
require "database.php";
$data = ""/** whatever you're serializing **/;
$date = date("Y-m-d");
$currentUser = $_SESSION['myuserid'];
//POST ROUTE

$sql = "SELECT * FROM `script_groups` ORDER BY priority DESC LIMIT 1";
$result = mysqli_query($con,$sql);
$row = mysqli_fetch_assoc($result);
$data = json_encode($row);
print $data;
