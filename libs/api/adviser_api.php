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
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization, authorization");
if (!isset($_SESSION)) {
    session_start();
}

if (file_exists("controllers/Adviser.controller.php")) //for Controllers in libs
{
    include_once "controllers/Adviser.controller.php";
}

if (file_exists("classes/general.class.php")) //for Controllers in libs
{
    include_once "classes/general.class.php";
}

//fetch POST request parameter
$action = $_POST["action"];

//determine which function to trigger
switch ($action) {
    case "":
    default:
        echo json_encode(array("message" => "invalid request"));
        break;
    case "get_advisers":
        echo getAllAdvisers();
        break;
    case "get_lead_tracker_advisers":
        echo getAllAdvisersForLeadTracker();
        break;
    case "get_adviser":
        echo GetAdviser();
        break;
    case "get_adviser_and_fetch":
        echo GetAdviserAndFetch();
        break;
    case "create_adviser":
        echo CreateAdviser();
        break;
    case "update_adviser":
        echo UpdateAdviser();
        break;
    case "delete_adviser":
        echo DeleteAdviser();
        break;
    case "get_adviser_payables":
        echo GetAdviserPayables();
        break;
}

/**
@desc: delete the Resource
 */

function GetAllAdvisers()
{
    $controller = new AdviserController();
    extract($_POST);

    $dataset = $controller->getAllAdvisers();
    $op = array();

    while ($data = $dataset->fetch_assoc()) {
        $op[] = $data;
    }

    return json_encode($op);
}

/**
@desc: delete the Resource
 */

function getAllAdvisersForLeadTracker()
{
    $controller = new AdviserController();
    extract($_POST);

    $dataset = $controller->getAllAdvisersOrderedByTerminationDate();
    $op = array();

    while ($data = $dataset->fetch_assoc()) {

        $status = ($data["termination_date"] == "") ? "active" : "terminated"; 
        $adviser = [
            "text" => $data["name"],
            "value" => $data["id"],
            "status" => $status
        ];
        $op[] = $adviser;
    }

    return json_encode($op);
}

/**
@desc: delete the Resource
 */

function GetAdviserPayables()
{
    $controller = new AdviserController();
    extract($_POST);
    $data = "";

    if (isset($request_from)) {
        switch ($request_from) {
            case "Payroll":
                $adviserController = new AdviserController();
                $adviser = (object) $adviserController->getAdviser($adviser_id);

                if (isset($adviser->id)) {
                    $data = $adviserController->getAdviserPayables($adviser->id);
                } else {
                    $data = array();
                    $data["error"] = "Adviser not registered on INDET.";
                }
                break;
        }
    }

    return json_encode($data);
}

/**
@desc: delete the Resource
 */

function GetAdviser()
{
    $controller = new AdviserController();
    extract($_POST);

    $data = $controller->getAdviser($adviser_id);

    return json_encode($data);
}

/**
@desc: delete the Resource
 */

function GetAdviserAndFetch()
{
    $controller = new AdviserController();
    extract($_POST);

    $data = $controller->getAdviser($adviser_id);

    return json_encode($data);
}

/**
@desc: CreateAdvisers the Resource
 */

function CreateAdviser()
{
    $controller = new AdviserController();
    extract($_POST);

    $data = $controller->createAdviser(
        $team_id,
        $steam_id,
        $position_id,
        $name,
        $company_name,
        $payroll_name,
        $fsp_num,
        $address,
        $email,
        $birthday,
        $leads,
        $bonus,
        $image,
        $date_hired,
        $termination_date,
        ''
    );

    $data = $data->fetch_assoc();

    $data["birthday"] = date("d/m/Y", strtotime($data["birthday"]));
    $data["date_hired"] = date("d/m/Y", strtotime($data["date_hired"]));
    $data["termination_date"] = date("d/m/Y", strtotime($data["termination_date"]));

    return json_encode($data);
}

/**
@desc: UpdateAdviser the Resource
 */

function UpdateAdviser()
{
    $controller = new AdviserController();
    $helper = new General();
    extract($_POST);

    if (!isset($image)) {
        $image = "";
    }

    $data = $controller->updateAdviser(
        $adviser_id,
        $team_id,
        $steam_id,
        $position_id,
        $name,
        $company_name,
        $payroll_name,
        $fsp_num,
        $address,
        $email,
        $birthday,
        $leads,
        $bonus,
        $image,
        $date_hired,
        $termination_date
    );

    $data = $data->fetch_assoc();

    $data["birthday"] = date("d/m/Y", strtotime($data["birthday"]));
    $data["date_hired"] = date("d/m/Y", strtotime($data["date_hired"]));
    $data["termination_date"] = date("d/m/Y", strtotime($data["termination_date"]));

    return json_encode($data);
}

/**
@desc: delete the Resource
 */

function DeleteAdviser()
{
    $controller = new AdviserController();
    extract($_POST);

    $data = $controller->deleteAdviser($adviser_id);

    return json_encode($data);
}
