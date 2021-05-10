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


if (file_exists("controllers/FollowUpHistory.controller.php"))          //for Controllers in libs
    include_once("controllers/FollowUpHistory.controller.php");

//fetch POST request parameter 
$action = $_POST["action"];

//determine which function to trigger
switch ($action) {
    case "":
    default:
        echo json_encode(array("message" => "invalid request"));
        break;
    case "get_all_follow_up_history":
        echo GetAllFollowUpHistory();
        break;
    case "get_follow_up_history":
        echo GetFollowUpHistory();
        break;
    case "add_follow_up_history":
        echo CreateFollowUpHistory();
        break;
    case "update_follow_up_history":
        echo UpdateFollowUpHistory();
        break;
    case "delete_follow_up_history":
        echo DeleteFollowUpHistory();
        break;
}

/**
	@desc: delete the Resource
 */

function GetAllFollowUpHistory()
{
    $controller = new FollowUpHistoryController();
    extract($_POST);

    $dataset = $controller->getAllFollowUpHistory($client_id);
    $op = array();

    while ($data = $dataset->fetch_assoc()) {
        $timestamp = $data["timestamp"];

        $timestamp = date_create_from_format("Y-m-d H:i:s", $timestamp);
        $formatted_timestamp = $timestamp->format("d/m/Y g:i:s A");
        $data["formatted_timestamp"] = $formatted_timestamp;
        $data["timestamp"] = $timestamp->format("YmdHis");

        
        $data["notes"] = str_replace("<br>","\r\n",$data["notes"]);

        $op[] = $data;
    }


    return json_encode($op);
}


/**
	@desc: delete the Resource
 */

function GetFollowUpHistory()
{
    $controller = new FollowUpHistoryController();
    extract($_POST);

    $data = $controller->getFollowUpHistory($history_id);
    $data = $data->fetch_assoc();
    $timestamp = $data["timestamp"];

    $timestamp = date_create_from_format("Y-m-d H:i:s", $timestamp);
    $formatted_timestamp = $timestamp->format("d/m/Y g:i:s A");
    $data["formatted_timestamp"] = $formatted_timestamp;
    $data["timestamp"] = $timestamp->format("YmdHis");

    $data["notes"] = str_replace("<br>","\r\n",$data["notes"]);

    return json_encode($data);
}


/**
	@desc: CreateFollowUpHistorys the Resource
 */

function CreateFollowUpHistory()
{
    $controller = new FollowUpHistoryController();
    extract($_POST);

    $data = $controller->createFollowUpHistory(
        $client_id,
        $notes
    );

    $data = $data->fetch_assoc();

    $timestamp = $data["timestamp"];

    $timestamp = date_create_from_format("Y-m-d H:i:s", $timestamp);
    $formatted_timestamp = $timestamp->format("d/m/Y g:i:s A");
    $data["formatted_timestamp"] = $formatted_timestamp;
    $data["timestamp"] = $timestamp->format("YmdHis");

    return json_encode($data);
}

/**
	@desc: UpdateFollowUpHistory the Resource
 */

function UpdateFollowUpHistory()
{
    $controller = new FollowUpHistoryController();
    extract($_POST);

    $data = $controller->updateFollowUpHistory(
        $history_id,
        $notes
    );

    $data = $data->fetch_assoc();

    $timestamp = $data["timestamp"];

    $timestamp = date_create_from_format("Y-m-d H:i:s", $timestamp);
    $formatted_timestamp = $timestamp->format("d/m/Y g:i:s A");
    $data["formatted_timestamp"] = $formatted_timestamp;
    $data["timestamp"] = $timestamp->format("YmdHis");

    return json_encode($data);
}

/**
	@desc: delete the Resource
 */

function DeleteFollowUpHistory()
{
    $controller = new FollowUpHistoryController();
    extract($_POST);

    $data = $controller->deleteFollowUpHistory($history_id);

    return json_encode($data);
}
