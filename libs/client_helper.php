<?php
session_start();
date_default_timezone_set('Pacific/Auckland');
$restrict_session_check = true;

require "../database.php";
require "../libs/indet_dates_helper.php";
require "../libs/api/controllers/Client.controller.php";
header('Content-Type: application/json');

$date_helper = new INDET_DATES_HELPER();
$data = ""
    /** whatever you're serializing **/
;


$action = "";
//fetch POST request parameter 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST["action"];
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = (!empty($_GET["action"])) ? $_GET["action"] : "";
}

switch ($action) {
    default:
        $row["message"] = "Request Failed";
        break;
    case "check_client_if_issued":
        $client_id = $_GET["client_id"];
        $row = oldestAssignedClient($con, $client_id);
        break;
    case "update_issued_client_profile":
        $row = updateIssuedClientProfile();
        break;
    case "exempt_client_from_invoice":
        $row = exemptClientFromInvoice();
        break;
    case "remove_client_exception":
        $row = removeClientExemption();
        break;
}

function checkClientIfIssued($con, $client_id)
{
    //Get first client
    $query = "Select * from issued_clients_tbl where name = '$client_id' LIMIT 1";
    $result = mysqli_query($con, $query);
    $rows = mysqli_num_rows($result);
    $op = ($rows > 0) ? true : false;

    return $op;
}


function updateIssuedClientProfile()
{
    //Get first client
    extract($_POST);
    $clientController = new ClientController();
    $dataset = $clientController->UpdateIssuedClientProfile($client_id, $assigned_to, $leadgen);
    $dataset = $dataset->fetch_assoc();

    return $dataset;
}

function exemptClientFromInvoice()
{
    //Get first client
    extract($_POST);
    $clientController = new ClientController();
    $dataset = $clientController->exemptClientFromInvoice($client_id, $status);
    $data = $dataset;
    $data["id"] = $data["exemption_id"];
    $data["client_name"] = $data["name"];
    $data["date_assigned"] = date("d/m/Y", strtotime($data["date_status_updated"]));
    $data["date_assigned_order"] = $data["date_status_updated"];

    return $data;
}

function removeClientExemption()
{
    //Get first client
    extract($_POST);
    $clientController = new ClientController();
    $dataset = $clientController->removeClientExemption($exemption_id);
    $data = $dataset;
    $data["date_assigned"] = date("d/m/Y", strtotime($data["assigned_date"]));
    $data["date_assigned_order"] = $data["assigned_date"];

    return $data;
}


$data = json_encode($row);
print $data;
