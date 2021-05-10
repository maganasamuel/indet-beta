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
      $sql = "DELETE FROM `scripts` WHERE `id` = " . $id; 
      $data = mysqli_query($con,$sql);
    }
  print $data;
}

if(!empty($_POST['formtype'])){
  extract($_POST);
  $caption = addslashes($caption);
  $script = addslashes($script);
  $script = str_replace("\r\n", "<br>", $script);
    if($formtype=="add"){
      $sql ="INSERT INTO scripts (script_group, caption,script,created_by) VALUES ('$script_group','$caption','$script','$currentUser')"; 
      //$data["responseText"] = $sql;
      $row = mysqli_query($con,$sql);
      $id = mysqli_insert_id($con);
    }
    elseif($formtype=="update"){
      $sql ="UPDATE `scripts` SET `script_group`='$script_group', `script` = '$script', `caption` = '$caption' WHERE `id` = $script_id";
      $row = mysqli_query($con,$sql);
      $id = $script_id;
    }

    $sql = "SELECT * FROM `scripts` WHERE `id` = " . $id . " LIMIT 1";
    $result = mysqli_query($con,$sql);
    $row = mysqli_fetch_assoc($result);
    $data = json_encode($row);
    print $data;
}

//GET ROUTE
if(!empty($_GET['id'])){
  $sql = "SELECT * FROM `scripts` WHERE `id` = " . $_GET['id'] . " LIMIT 1";
  $result = mysqli_query($con,$sql);
  $row = mysqli_fetch_assoc($result);
  $row["translated_script"] = TelemarketerScriptTranslator($row["script"]);
  $row["script"] = str_replace("<br>","\r\n", $row["script"]);
  $data = json_encode($row);
  print $data;
}

function TelemarketerScriptTranslator($script){
  $script = str_replace("{{username}}", $_SESSION['myusername'], $script);

  $time_of_day = date('H');
  $time_of_day_string = "";
  if ($time_of_day < "12") {
      $time_of_day_string = "morning";
  } else
  /* If the time is grater than or equal to 1200 hours, but less than 1700 hours, so good afternoon */
  if ($time_of_day >= "12" && $time_of_day < "17") {
    $time_of_day_string = "afternoon";
  } else
  /* Should the time be between or equal to 1700 and 1900 hours, show good evening */
  if ($time_of_day >= "17" && $time_of_day < "19") {
    $time_of_day_string = "evening";
  } else
  /* Finally, show good night if the time is greater than or equal to 1900 hours */
  if ($time_of_day >= "19") {
    $time_of_day_string = "night";
  }

  $script = str_replace("{{time_of_day}}", $time_of_day_string, $script);
  return $script;
}
