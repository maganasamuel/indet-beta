<?php
session_start();
header('Content-Type: application/json');

$restrict_session_check = true;

require "../database.php";
require "../libs/indet_dates_helper.php";

$date_helper = new INDET_DATES_HELPER();

$data = ""/** whatever you're serializing **/;
$date = date("Y-m-d");
$currentUser = $_SESSION['myuserid'];
//POST ROUTE
if(!empty($_POST['method'])){
  extract($_POST);
  if($_SESSION["myusertype"]=="Admin"){
    if($method=="delete"){
        $sql = "DELETE FROM `clients_tbl` WHERE `id` = " . $id; 
        
      }
  }
  elseif($_SESSION["myusertype"]=="User"){
['notes'];    mysqli_query($con, $sql);
    
    $sql="UPDATE clients_tbl SET binned=1 WHERE id=$id";
  }

  $data = mysqli_query($con,$sql);
  print $data;
}

if(!empty($_POST['formtype'])){   
  //extract data
  $formtype = $_POST['formtype'];
  $seen = $_POST['seen'];
  $client_id = $_POST['client_id'];
  $notes = $_POST['notes'];

    if($formtype=="update"){
      $sql="UPDATE clients_tbl SET seen_status=\"$seen\", seen_notes=\"$notes\" WHERE id=$client_id";
      $row = mysqli_query($con,$sql);
      $id = $client_id;
    }

    $sql =  "SELECT c.*, l.name as leadgen_name, a.name as adviser_name FROM `clients_tbl` c LEFT JOIN `leadgen_tbl` l ON c.leadgen = l.id LEFT JOIN `adviser_tbl` a ON c.assigned_to = a.id WHERE c.id = " . $id . " LIMIT 1";
    $result = mysqli_query($con,$sql);
    $row = mysqli_fetch_assoc($result);

    if($row["leadgen_name"]==""&&$row["lead_by"]=="Self-Generated"){
      $row["leadgen_name"] = $row["adviser_name"];
    }

    $data = json_encode($row);
    print $data;
}

//GET ROUTE
if(!empty($_GET['id'])){
  $sql = "SELECT c.*, l.name as leadgen_name, a.name as adviser_name FROM `clients_tbl` c LEFT JOIN `leadgen_tbl` l ON c.leadgen = l.id LEFT JOIN `adviser_tbl` a ON c.assigned_to = a.id WHERE c.id = " . $_GET['id'] . " LIMIT 1";
  $result = mysqli_query($con,$sql);
  $row = mysqli_fetch_assoc($result);
  $data = json_encode($row);
  print $data;
}