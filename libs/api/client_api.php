<?php

/**
@name: leadGenerator_api.php
@author: Jesse
@desc:
	Serves as the API of the admins
	This page handles all asynchronous javascript request from the above mentioned page
@returnType:
	JSON
 */
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: PUT, GET, POST");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Authorization, Content-Type, Accept");

if (!isset($_SESSION)) session_start();


if (file_exists("controllers/Client.controller.php"))          //for Controllers in libs
    include_once("controllers/Client.controller.php");

if(!empty(file_get_contents("php://input"))){
    if(empty($_POST)){
        $_POST = json_decode(file_get_contents("php://input"), true);
    }
}
//fetch POST request parameter 
$action = $_POST["action"];

//determine which function to trigger
switch ($action) {
    case "":
    default:
        echo json_encode(array("message" => "invalid request"));
        break;
    case "get_clients":
        echo getAllClients();
        break;
    case "get_clients":
        echo getAllClients();
        break;
    case "get_client":
        echo GetClient();
        break;
    case "get_client_and_fetch":
        echo GetClientAndFetch();
        break;
    case "create_client":
        echo CreateClient();
        break;
    case "update_client":
        echo UpdateClient();
        break;
    case "delete_client":
        echo DeleteClient();
        break;
    case "create_send_client_data_entry":
        echo CreateSendClientDataEntry();
        break;
    case "create_send_issued_client_data_entry":
        echo CreateSendIssuedClientDataEntry();
        break;
    case "send_client_data":
        echo SendClientData();
        break;
    case "get_clients_assigned_to_adviser":
        echo GetClientsAssignedToAdviser();
        break;
    case "update_client_seen_status":
        echo UpdateClientSeenStatus();
        break;
    case "update_client_appointment":
        echo UpdateClientAppointment();
        break;
    case "create_client_updates":
        echo CreateClientUpdate();
        break;
    case "get_client_updates":
        echo GetClientUpdates();
        break;
}

/**
	@desc: delete the Resource
 */

function GetAllClients()
{
    $controller = new ClientController();
    extract($_POST);

    $dataset = $controller->getAllClients();
    $op = array();

    while ($data = $dataset->fetch_assoc()) {
        $op[] = $data;
    }

    return json_encode($op);
}


/**
	@desc: delete the Resource
 */

function GetClient()
{
    $controller = new ClientController();
    extract($_POST);

    $data = $controller->getClient($client_id);

    return json_encode($data);
}



/**
	@desc: delete the Resource
 */

function GetClientsAssignedToAdviser()
{
    $controller = new ClientController();
    extract($_POST);

    $data = $controller->getClientsAssignedToAdviser($adviser_id, $requesting);
    $clients = [];

    while($row = $data->fetch_assoc()){
        $clients[] = ConvertToLeadTrackerClientData($row);
    }
    return json_encode($clients);
}

function UpdateClientSeenStatus()
{
    $controller = new ClientController();
    extract($_POST);

    $data = $controller->updateClientSeenStatus($client_id, $status);
    $client = $data->fetch_assoc();

    return json_encode(ConvertToLeadTrackerClientData($client));
}

function UpdateClientAppointment()
{
    $controller = new ClientController();
    extract($_POST);
    $appointment_date = str_replace("-","", $appointment_date);
    $data = $controller->updateClientAppointment($client_id, $appointment_date, $appointment_time);
    return json_encode(ConvertToLeadTrackerClientData($data));
}

function ConvertToLeadTrackerClientData($row){    
    $row["phone"] = (!empty($row["appt_time"])) ? $row["appt_time"] : "N/A";
    $row["address"] = (!empty($row["address"])) ? $row["address"] : "N/A";
    $row["source"] = $row["lead_by"];
    $row["leadgen_name"] = ($row["source"] == "Self-Generated") ? "Self" : $row["leadgen_name"];

    if($row["appt_date"] == "")
        $row["appt_date"] = "19700101";

    $row["appt_date_order"] = $row["appt_date"]; 
    $row["appt_date"] = date("d/m/Y", strtotime($row["appt_date"])); 


    if($row["time"] == "")
        $row["time"] = "12:00";

    $row["appt_time"] = date("h:i a", strtotime($row["time"]));

    $row["appt_date_and_time_order"] = $row["appt_date_order"] . date("Hi", strtotime($row["time"])); 
    $row["appt_date_and_time"] = $row["appt_date"] . " " . $row["appt_time"]; 

    $row["appt_date_and_time_timestamp"] = strtotime($row["appt_date_and_time_order"]) + 18000; 

    $row["date_assigned_order"] = $row["assigned_date"]; 
    $row["date_assigned"] = date("d/m/Y", strtotime($row["assigned_date"])); 
    $row["created_at"] = strtotime($row["creation_date"]);
    $row["date_generated"] = date("d/m/Y", strtotime($row["date_submitted"])); 
    return $row;
}

/**
	@desc: delete the Resource
 */

function GetClientAndFetch()
{
    $controller = new ClientController();
    extract($_POST);

    $data = $controller->getClient($client_id);

    return json_encode($data);
}

/**
	@desc: CreateClients the Resource
 */

function CreateClient()
{
    $controller = new ClientController();
    extract($_POST);

    $data = $controller->createClient(
        $team_id,
        $name,
        $company_name,
        $payroll_name,
        $fsp_num,
        $address,
        $email,
        $birthday,
        $leads,
        $bonus
    );

    $data = $data->fetch_assoc();
    $data["birthday"] = date("d/m/Y", strtotime($data["birthday"]));

    return json_encode($data);
}


/**
	@desc: CreateClients the Resource
 */

function CreateClientUpdate()
{
    $controller = new ClientController();
    extract($_POST);
    $data = $controller->createClientUpdate(
        $client_id,
        $sender_id,
        $message
    );

    $data = $data->fetch_assoc();

    return json_encode($data);
}



/**
	@desc: CreateClients the Resource
 */

function GetClientUpdates()
{
    $controller = new ClientController();
    extract($_POST);

    $updates = $controller->getClientUpdates($client_id);

    return json_encode($updates);
}

/**
	@desc: UpdateClient the Resource
 */

function UpdateClient()
{
    $controller = new ClientController();
    extract($_POST);

    $data = $controller->updateClient(
        $client_id,
        $team_id,
        $name,
        $company_name,
        $payroll_name,
        $fsp_num,
        $address,
        $email,
        $birthday,
        $leads,
        $bonus
    );
    $data = $data->fetch_assoc();
    $data["birthday"] = date("d/m/Y", strtotime($data["birthday"]));

    return json_encode($data);
}

/**
	@desc: delete the Resource
 */

function DeleteClient()
{
    $controller = new ClientController();
    extract($_POST);

    $data = $controller->deleteClient($client_id);

    return json_encode($data);
}

/**
	@desc: create send client data entry
 */

function CreateSendClientDataEntry()
{
    $controller = new ClientController();
    extract($_POST);

    $data = $controller->createSendClientDataEntry($name, $email, $client_ids);

    return json_encode($data);
}


/**
	@desc: create send client data entry
 */

function CreateSendIssuedClientDataEntry()
{
    $controller = new ClientController();
    extract($_POST);

    $data = $controller->createSendIssuedClientDataEntry($name, $email, $client_ids);

    return json_encode($data);
}

/**
	@desc: delete the Resource
 */

function SendClientData()
{
    $controller = new ClientController();
    extract($_POST);

    //$data = $controller->createSendClientDataEntry($send_client_data_id);

    return json_encode($data);
}
