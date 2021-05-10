<?php
session_start();
date_default_timezone_set('Pacific/Auckland');
header('Content-Type: application/json');
$restrict_session_check = true;
require "database.php";

extract($_POST);

$sql = "SELECT * FROM `datamined_numbers` WHERE `number` = " . $number . " AND (`status` = 'Do Not Call' OR `status` = 'Called')";
$result = mysqli_query($con,$sql);
$data = array();

while($row = mysqli_fetch_assoc($result)){
    $data[] = $row;
}

$data = json_encode($data);
print $data;