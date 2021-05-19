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
if (!isset($_SESSION)) session_start();


if (file_exists("controllers/STeam.controller.php"))          //for Controllers in libs
    include_once("controllers/STeam.controller.php");

//fetch POST request parameter 
$action = $_POST["action"];

//determine which function to trigger
switch ($action) {
    case "":
    default:
        echo json_encode(array("message" => "invalid request"));
        break;
    case "get_teams":
        echo getAllTeams();
        break;
    case "get_team":
        echo GetTeam();
        break;
    case "create_team":
        echo CreateTeam();
        break;
    case "update_team":
        echo UpdateTeam();
        break;
    case "delete_team":
        echo DeleteTeam();
        break;
}

/**
	@desc: delete the Resource
 */

function GetAllTeams()
{
    $controller = new STeamController();
    extract($_POST);

    $data = $controller->getAllTeams();
    $op = array();

    while ($data = $data->fetch_assoc()) {
        $op[] = $data;
    }

    return json_encode($op);
}


/**
	@desc: delete the Resource
 */

function GetTeam()
{
    $controller = new STeamController();
    extract($_POST);

    $data = $controller->getTeam($team_id);

    return json_encode($data);
}


/**
	@desc: CreateTeams the Resource
 */

function CreateTeam()
{
    $controller = new STeamController();
    extract($_POST);

    $data = $controller->createTeam($name, $leader);

    return json_encode($data);
}

/**
	@desc: UpdateTeam the Resource
 */

function UpdateTeam()
{
    $controller = new STeamController();
    extract($_POST);

    $data = $controller->updateTeam($team_id, $name, $leader);

    return json_encode($data);
}

/**
	@desc: delete the Resource
 */

function DeleteTeam()
{
    $controller = new STeamController();
    extract($_POST);

    $data = $controller->deleteTeam($team_id);

    return json_encode($data);
}
