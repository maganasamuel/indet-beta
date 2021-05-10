<?php
session_start();
require("database.php");
//ADD REPORT TO DATABASE
$date_from = "";
$date_to = "";
extract($_POST);
$now = date('Ymd');
$user = $_SESSION['myuserid'];
$filterdata_array = explode(",", $filterdata);
$filterdata = json_encode($filterdata_array);


function convertToFourDigits($num = 0){
    $op = "";
    if($num < 10){
        $op = "000" . $num;
    }
    elseif($num < 100){
        $op = "00" . $num;
    }
    elseif($num < 1000){
        $op = "0" . $num;
    }
    elseif($num < 10000){
        $op = "" . $num;
    }
    return $op;
}

(isset($_POST['specific_month'])) ? $convertedDate = date_parse($specific_month) : "";


if(!isset($_POST['date_from'])){
    $raw_date_from = "01-".$convertedDate["month"]."-".$year_from;
    $raw_date_to = cal_days_in_month(CAL_GREGORIAN, $convertedDate["month"], $year_to)."-".$convertedDate["month"]."-".$year_to;

    $date_from = date('Ymd', strtotime($raw_date_from));
    $date_to = date('Ymd', strtotime($raw_date_to));

} else {
    if($date_from != "" && $date_to != ""){
        $date_from = substr($date_from, 6,4) . substr($date_from, 3,2) . substr($date_from, 0,2);
        $date_to = substr($date_to, 6,4) . substr($date_to, 3,2) . substr($date_to, 0,2);
    }
    else{
        $date_to_query = "SELECT * FROM clients_tbl  WHERE date_submitted != '' AND date_submitted != '//' AND date_submitted != '///'  ORDER BY date_submitted DESC LIMIT 1";
        $date_to_result = mysqli_query($con,$date_to_query) or die('Could not look up user information; ' . mysqli_error($con));
        $date_to_fetch = mysqli_fetch_assoc($date_to_result);
        $date_to = $date_to_fetch["date_submitted"];
        
        $date_from_query = "SELECT * FROM clients_tbl  WHERE date_submitted != '' AND date_submitted != '//' AND date_submitted != '///' ORDER BY date_submitted ASC LIMIT 1";
        $date_from_result = mysqli_query($con,$date_from_query) or die('Could not look up user information; ' . mysqli_error($con));
        $date_from_fetch = mysqli_fetch_assoc($date_from_result);
        $date_from = $date_from_fetch["date_submitted"];
    }
}

// //Set dates
//echo "$date_from - $date_to";
$until = $date_to;
$date_covered = $date_from . "-" . $date_to;

//CREATE REFERENCE NUMBER
$refnum_query = "SELECT * FROM client_data_reports WHERE reference_number LIKE '%$date_now' ORDER BY reference_number DESC LIMIT 1";
//echo $refnum_query;
$refnum_result = mysqli_query($con,$refnum_query) or die('Could not look up user information; ' . mysqli_error($con));
$refnum_count = mysqli_fetch_assoc($refnum_result);
$refnum_count = $refnum_count['reference_number'];
$latest_number = substr($refnum_count, 3, 4);
$latest_number += 1;

$leadgen_refnum = "CD-" .  convertToFourDigits($latest_number) . str_replace("/", "",$date_now);



$sql="INSERT INTO client_data_reports (reference_number,client_type,source,lead_gens,advisers,filterby,filterdata,date_from,date_to,created_at,created_by) 
VALUES ('$leadgen_refnum','$clienttype','$source','$lead_gens','$advisers','$filterby','$filterdata',$date_from,$date_to,$now,'$user')"; 

$result = mysqli_query($con,$sql);

$output = array();
$output['status'] = "success";
$output['reference_number'] = $leadgen_refnum;
$output['SQL'] = $sql;
echo json_encode($output);
//END 
?>