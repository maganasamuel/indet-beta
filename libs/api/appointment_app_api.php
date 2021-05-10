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

if (file_exists("controllers/Deal.controller.php"))          //for Controllers in libs
    include_once("controllers/Deal.controller.php");

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
    case "get_bdm_clients_in_range":
        echo getBDMClientsInRange();
        break;
    case "update_client_status":
        echo updateClientStatus();
        break;
}

function updateClientStatus(){
    $clientController = new ClientController();
    extract($_POST);
    
    $output = $clientController->updateAppointmentStatus($client_id, $status);
    return json_encode($output);
}


/**
	@desc: delete the Resource
 */

function GetBDMClientsInRange()
{
    $clientController = new ClientController();
    extract($_POST);

    $clients = [];
    $issued_deals = [];

    $date_from = date("Ymd", strtotime($from));
    $date_to = date("Ymd", strtotime($to));

    //Get Clients in Range
    foreach($bdms as $bdm){
        $data = $clientController->getClientsGeneratedByLeadGeneratorInRange($bdm, $date_from, $date_to);

        $clients = array_merge($clients, $data);
    }

    //Get Issued Data
    $bdm_clients = $clientController->getClientsGeneratedBy($bdm);
    foreach($bdm_clients as $index => $client){
        $client_data = $clientController->getIssuedClientProfile($client["id"])->fetch_assoc();

        if($client_data != null){
            $deals_data = [];
            
            $deals_data["client_id"] = $client["id"];
            $deals_data["lead_generator_id"] = $bdm;
            $deals_data["client_name"] = $client["name"];
            
            $deals = json_decode($client_data["deals_data"], true);
            $current_issued = [];

            foreach($deals as $deal){
                if($deal["status"] == "Issued"){
                    $current_issued[] = $deal;
                }
            }

            $deals_data["deals"] = $current_issued;

            if(count($current_issued) > 0)
                $issued_deals[] = $deals_data;
        }
    }

    $op = [
        "clients" => $clients,
        "issued" => $issued_deals
    ];

    return json_encode($op);
}
