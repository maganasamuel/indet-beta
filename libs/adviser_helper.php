<?php
session_start();
date_default_timezone_set('Pacific/Auckland');
$restrict_session_check = true;

require "../database.php";
require "../libs/indet_dates_helper.php";
header('Content-Type: application/json');

$date_helper = new INDET_DATES_HELPER();
$data = ""/** whatever you're serializing **/;

$action = "";
//fetch POST request parameter 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$action = $_POST["action"];
}
elseif($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = (!empty($_GET["action"])) ? $_GET["action"] : "";
}

switch($action){
    default:
        $row["message"] = "Request Failed";
    break;
    case "fetch_oldest_assigned_client":
        $adviser_id = $_GET["adviser_id"];
        $row = oldestAssignedClient($con,$adviser_id);
    break;
}

function oldestAssignedClient($con,$adviser_id){
    //Get first client
    $date_helper = new INDET_DATES_HELPER();

    $query = "Select * from clients_tbl where assigned_to = '$adviser_id' AND assigned_date != '' ORDER BY assigned_date ASC LIMIT 1";
    $result = mysqli_query($con,$query);
    $row = mysqli_fetch_assoc($result);
    $row["translated_assigned_date"] = $date_helper->NZEntryToDateTime($row["assigned_date"]);
    return $row;
}

    $data = json_encode($row);
    print $data;
?>