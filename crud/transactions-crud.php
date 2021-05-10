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
  $data = "";

  $clients_list_string = "";
  if(isset($clients_list)){
    if(is_array($clients_list)){
      $clients_list_string = implode(",",$clients_list);
    }
    else{
      $clients_list_string = $clients_list;
    }
  }
  
  if($method=="DELETE"){
      $sql = "DELETE FROM `transactions` WHERE `id` = " . $id; 
      $data = mysqli_query($con,$sql);
    }
  elseif($method=="POST"){
      //Convert date for before input
      $date = DateTimeToNZEntry($date);
      


      $sql ="INSERT INTO transactions (adviser_id,status,date,number_of_leads,amount,notes, clients_list) VALUES ('$adviser_id','$status','$date','$number_of_leads','$amount','$notes', '$clients_list_string')"; 
      $row = mysqli_query($con,$sql);
      $transaction_id = mysqli_insert_id($con);
    }
  elseif($method=="PUT"){
      //Convert date for before input
      $notes = addslashes($notes);
      $date = DateTimeToNZEntry($date);
      $sql ="UPDATE `transactions` SET `date` = '$date', `number_of_leads` = '$number_of_leads', `amount` = '$amount', `notes` = '$notes', clients_list = '$clients_list_string' WHERE id=$transaction_id"; 
      $row = mysqli_query($con,$sql);
    }

  if($method!="DELETE"){
    $sql = "SELECT * FROM `transactions` WHERE `id` = " . $transaction_id . " LIMIT 1";
    $row = mysqli_query($con,$sql);
    $data = array();

    while($r = mysqli_fetch_assoc($row)) {
        $data[] = $r;
    }
    //Convert date for user reading
    $data[0]["date"] = NZEntryToDateTime($data[0]["date"]);
    $data = json_encode($data[0]);
  }
    
  print $data;
}


//GET ROUTE
if(!empty($_GET['id'])){
  $sql = "SELECT * FROM `transactions` WHERE `id` = " . $_GET['id'] . " LIMIT 1";
  $row = mysqli_query($con,$sql);
  $data = array();
  while($r = mysqli_fetch_assoc($row)) {
      $data[] = $r;
  }
  $data[0]["date"] = NZEntryToDateTime($data[0]["date"]);
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