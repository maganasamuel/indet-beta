<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: PUT, GET, POST");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Authorization, Content-Type, Accept");

session_start();
header('Content-Type: application/json');
date_default_timezone_set('Pacific/Auckland');

$restrict_session_check = true;

require "../database.php";
require "../libs/indet_dates_helper.php";

$date_helper = new INDET_DATES_HELPER();

if(isset($_SESSION['myuserid'])){
  $currentUser = $_SESSION['myuserid'];
}
else{
  if(!empty(file_get_contents("php://input"))){
    $_POST = json_decode(file_get_contents("php://input"), true);
  }
}

$data = ""/** whatever you're serializing **/;
$date = date("Y-m-d");

$currentUser = 0;



//POST ROUTE

if(!empty($_POST['method'])){
  extract($_POST);
  if($_SESSION["myusertype"]=="Admin"){
    if($method=="delete"){
        $sql = "DELETE FROM `clients_tbl` WHERE `id` = " . $id; 
        
      }
  }
  elseif($_SESSION["myusertype"]=="User"){
    $sql="INSERT INTO bin_entries (table_name,table_id,binned_by) VALUES('clients_tbl','" . $id . "','" . $_SESSION['myuserid'] . "')";
    mysqli_query($con, $sql);
    
    $sql="UPDATE clients_tbl SET binned=1 WHERE id=$id";
  }

  $data = mysqli_query($con,$sql);
  print $data;
}

if(!empty($_POST['formtype'])){   
  //extract data
  $formtype = $_POST['formtype'];
  $client_id=isset($_POST["client_id"])?$_POST["client_id"]:'';
  $name=isset($_POST["name"])?$_POST["name"]:'';
  $email=isset($_POST["email"])?$_POST["email"]:'';
  $appt_date=isset($_POST["appt_date"])?$_POST["appt_date"]:'';
  $date_submitted=isset($_POST["date_submitted"])?$_POST["date_submitted"]:'';
  $phone=isset($_POST["phone_num"])?$_POST["phone_num"]:'';
  $address=isset($_POST["address"])?$_POST["address"]:'';
  $city=isset($_POST["city"])?$_POST["city"]:'';
  $time=isset($_POST["time"])?$_POST["time"]:'';
  $zipcode=isset($_POST["zipcode"])?$_POST["zipcode"]:'';
  $leadgen=isset($_POST["leadgen"])?$_POST["leadgen"]:'';
  $leadby=isset($_POST["lead_by"])?$_POST["lead_by"]:'';
  $assigned_to=isset($_POST["assigned_to"])?$_POST["assigned_to"]:'';
  $assigned_date=isset($_POST["assigned_date"])?$_POST["assigned_date"]:'';
  $type_of_lead=isset($_POST["type_of_lead"])?$_POST["type_of_lead"]:'';
  $issued=isset($_POST["issued"])?$_POST["issued"]:'';
  $date_issued=isset($_POST["date_issued"])?$_POST["date_issued"]:'';
  $status=isset($_POST["status"])?$_POST["status"]:'Seen';
  $date_status_updated=isset($_POST["date_status_updated"])?$_POST["date_status_updated"]:$_POST["date_submitted"];
  $notes=htmlspecialchars($_POST["notes"]);

  $name = addslashes($name);
  $address = addslashes($address);
  $city = addslashes($city);
  $phone = addslashes($phone);
  $notes = addslashes($notes);

  $date_submitted=substr($date_submitted,6,4).substr($date_submitted,3,2).substr($date_submitted,0,2);
  $date_status_updated=substr($date_status_updated,6,4).substr($date_status_updated,3,2).substr($date_status_updated,0,2);
  $assigned_date=substr($assigned_date,6,4).substr($assigned_date,3,2).substr($assigned_date,0,2);
  $appt_date=substr($appt_date,6,4).substr($appt_date,3,2).substr($appt_date,0,2);
  $creation_date = date("Y-m-d H:i:s");

    if($formtype=="add"){
      $sql="INSERT INTO clients_tbl (name,email,appt_date,date_submitted,appt_time,time,address,city,zipcode,lead_by,leadgen,assigned_to,assigned_date,type_of_lead,issued,date_issued,notes,status,date_status_updated,creation_date) 
      VALUES ('$name','$email','$appt_date','$date_submitted','$phone','$time','$address','$city','$zipcode','$leadby','$leadgen','$assigned_to','$assigned_date','','','','$notes','$status','$date_status_updated','$creation_date')"; 
      
      $row = mysqli_query($con,$sql);
      $id = mysqli_insert_id($con);
    }
    elseif($formtype=="update"){
      $sql="UPDATE clients_tbl SET name=\"$name\", email=\"$email\", appt_date=\"$appt_date\",time=\"$time\",date_submitted=\"$date_submitted\",appt_time=\"$phone\",address=\"$address\",city=\"$city\",zipcode=\"$zipcode\",leadgen=\"$leadgen\",lead_by=\"$leadby\",assigned_to=\"$assigned_to\",assigned_date=\"$assigned_date\",notes=\"$notes\",status=\"$status\",date_status_updated=\"$date_status_updated\"
      WHERE id=$client_id";

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
  //for localhost
  // $sql = "SELECT 
  //   c.*, 
  //   l.name as leadgen_name, 
  //   a.name as adviser_name,
  //   (SELECT instructions FROM appointment_setter.appointments WHERE appointment_setter.appointments.indet_id IN (SELECT c.id FROM clients_tbl)) AS instructions,
  //   (SELECT additional_notes FROM appointment_setter.appointments WHERE appointment_setter.appointments.indet_id IN (SELECT c.id FROM clients_tbl)) AS additional_notes 
  // FROM 
  //   `clients_tbl` c LEFT JOIN 
  //   `leadgen_tbl` l ON c.leadgen = l.id LEFT JOIN 
  //   `adviser_tbl` a ON c.assigned_to = a.id 
  // WHERE 
  //   c.id = " . $_GET['id'] . " 
  // LIMIT 1";

  //for server
  $sql = "SELECT 
    c.*, 
    l.name as leadgen_name, 
    a.name as adviser_name,
    IFNULL((SELECT instructions FROM onlinei1_appointment_setter.appointments WHERE onlinei1_appointment_setter.appointments.indet_id IN (SELECT c.id FROM clients_tbl) LIMIT 1),'') AS instructions,
    IFNULL((SELECT additional_notes FROM onlinei1_appointment_setter.appointments WHERE onlinei1_appointment_setter.appointments.indet_id IN (SELECT c.id FROM clients_tbl) LIMIT 1),'') AS additional_notes 
  FROM 
    `clients_tbl` c LEFT JOIN 
    `leadgen_tbl` l ON c.leadgen = l.id LEFT JOIN 
    `adviser_tbl` a ON c.assigned_to = a.id 
  WHERE 
    c.id = " . $_GET['id'] . " 
  LIMIT 1";

  $result = mysqli_query($con,$sql);
  $row = mysqli_fetch_assoc($result);
  $data = json_encode($row);
  print $data;
}