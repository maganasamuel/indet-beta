<?php
session_start();
date_default_timezone_set('Pacific/Auckland');
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
        $sql = "DELETE FROM `adviser_tbl` WHERE `id` = " . $id; 
        
      }
  }
  elseif($_SESSION["myusertype"]=="User"){
    $sql="INSERT INTO bin_entries (table_name,table_id,binned_by) VALUES('adviser_tbl','" . $id . "','" . $_SESSION['myuserid'] . "')";
    mysqli_query($con, $sql);
    
    $sql="UPDATE adviser_tbl SET binned=1 WHERE id=$id";
  }

  $data = mysqli_query($con,$sql);
  print $data;
}

if(!empty($_POST['formtype'])){   
  //extract data
  $formtype = $_POST['formtype'];
  $adviser_id=isset($_POST["adviser_id"])?$_POST["adviser_id"]:'';
  $team=$_POST["team"];
  $name=$_POST["name"];
  $birthday=(!empty($_POST["birthday"])) ? $date_helper->DateTimeToNZEntry($_POST["birthday"]) : "";
  $fsp_num=$_POST["fsp_num"];
  $address=$_POST["address"];
  $ird_num=(isset($_POST["ird_num"])) ? $_POST["ird_num"] : "";
  $myemail=$_POST["myemail"];
  $leads=$_POST["leads"];
  $bonus=$_POST["bonus"];
  $company_name = $_POST["company_name"];

  $name = addslashes($name);
  $address = addslashes($address);

    if($formtype=="add"){            
      $sql="INSERT INTO adviser_tbl (name,company_name,team_id,fsp_num,address,ird_num,email,birthday,leads,bonus) 
      VALUES ('$name','$company_name','$team','$fsp_num','$address','$ird_num','$myemail','$birthday','$leads','$bonus')";
      $row = mysqli_query($con,$sql);
      $id = mysqli_insert_id($con);
    }
    elseif($formtype=="update"){
      $sql="UPDATE adviser_tbl SET name='$name',  company_name='$company_name', team_id='$team',fsp_num='$fsp_num',address='$address',ird_num='$ird_num',email='$myemail', birthday='$birthday',leads='$leads', bonus='$bonus'
      WHERE id='$adviser_id'"; 

      $row = mysqli_query($con,$sql);
      $id = $adviser_id;
    }

    $sql =  "SELECT * from adviser_tbl WHERE id = " . $id . " LIMIT 1";
    $result = mysqli_query($con,$sql);
    $row = mysqli_fetch_assoc($result);

    $row["leads"] = number_format($row["leads"],2);
    $row["bonus"] = number_format($row["bonus"],2);

    $data = json_encode($row);
    print $data;
}

//GET ROUTE
if(!empty($_GET['id'])){
  $sql = "SELECT * from adviser_tbl WHERE id = " . $_GET['id'] . " LIMIT 1";
  $result = mysqli_query($con,$sql);
  $row = mysqli_fetch_assoc($result);

  $row["leads"] = number_format($row["leads"],2);
  $row["bonus"] = number_format($row["bonus"],2);
  
  $data = json_encode($row);
  print $data;
}