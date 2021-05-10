<?php
session_start();
header('Content-Type: application/json');
require "../database.php";
$data = ""/** whatever you're serializing **/;
$date = date("Y-m-d");
$currentUser = $_SESSION['myuserid'];
//POST ROUTE

if(!empty($_POST['method'])){
    $history_id = 0;
  extract($_POST);
  $data = "";
  if($method=="DELETE"){
      $sql = "DELETE FROM `follow_up_histories` WHERE `id` = " . $id; 
      $data = mysqli_query($con,$sql);
    }
  elseif($method=="POST"){
      //Convert date for before input
      $user_id = $_SESSION["myuserid"];
      $notes = addslashes($notes);
      $notes = str_replace("/r/n","<br>",$notes);
      $sql ="INSERT INTO follow_up_histories (user_id, client_id, notes) VALUES ($user_id,$client_id,'$notes')"; 
      //echo $sql;
      $row = mysqli_query($con,$sql);
      $history_id = mysqli_insert_id($con);
    }
  elseif($method=="PUT"){
      //Convert date for before input
      $notes = addslashes($notes);
      $sql ="UPDATE `follow_up_histories` SET `notes` = '$notes' WHERE id=$history_id"; 
      $row = mysqli_query($con,$sql);
    }

  if($method!="DELETE"){
    $sql = "SELECT * FROM `follow_up_histories` WHERE `id` = " . $history_id . " LIMIT 1";
    $row = mysqli_query($con,$sql);
    $data = array();

    while($r = mysqli_fetch_assoc($row)) {
        $data[] = $r;
    }
    
    //Convert date for user reading
    $timestamp = date_create_from_format("Y-m-d H:i:s", $data[0]["timestamp"]);
    $timestamp->format("Ymd");
    $data[0]["timestamp"] = NZEntryToDateTime($timestamp->format("Ymd"));
    $data = json_encode($data[0]);
  }
    
  print $data;
}


//GET ROUTE
if(!empty($_GET['id'])){
  $sql = "SELECT * FROM `follow_up_histories` WHERE `id` = " . $_GET['id'] . " LIMIT 1";
  $row = mysqli_query($con,$sql);
  $data = array();
  while($r = mysqli_fetch_assoc($row)) {
      $data[] = $r;
  }
  $timestamp = date_create_from_format("Y-m-d H:i:s", $data[0]["timestamp"]);
  $timestamp->format("Ymd");
  $data[0]["timestamp"] = NZEntryToDateTime($timestamp->format("Ymd"));
  $data = json_encode($data[0]);
  print $data;
}


function DateTimeToNZEntry($date_submitted){
  return substr($date_submitted,6,4).substr($date_submitted,3,2).substr($date_submitted,0,2);
}

function NZEntryToDateTime($NZEntry){
    return substr($NZEntry,6,2) . "/" . substr($NZEntry,4,2) . "/" . substr($NZEntry, 0, 4);
}

?>