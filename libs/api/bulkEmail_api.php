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


if (file_exists("controllers/BulkEmail.controller.php"))          //for Controllers in libs
    include_once("controllers/BulkEmail.controller.php");

//fetch POST request parameter 
$action = $_POST["action"];

//determine which function to trigger
switch ($action) {
    case "":
    default:
        echo json_encode(array("message" => "invalid request"));
        break;
    case "get_bulk_emails":
        echo getAllBulkEmails();
        break;
    case "get_bulk_email":
        echo GetBulkEmail();
        break;
    case "create_bulk_email":
        echo CreateBulkEmail();
        break;
    case "update_bulk_email":
        echo UpdateBulkEmail();
        break;
    case "delete_bulk_email":
        echo destroy();
        break;
}

/**
	@desc: delete the Resource
 */

function GetAllBulkEmails()
{
    $controller = new BulkEmailController();
    extract($_POST);

    $data = $controller->getAllBulkEmails();
    $op = array();

    while ($data = $data->fetch_assoc()) {
        $op[] = $data;
    }

    return json_encode($op);
}


/**
	@desc: delete the Resource
 */

function GetBulkEmail()
{
    $controller = new BulkEmailController();
    extract($_POST);

    $data = $controller->getBulkEmail($id);
    $data = $data->fetch_assoc();

    return json_encode($data);
}


/**
	@desc: CreateBulkEmails the Resource
 */

function CreateBulkEmail()
{
    $controller = new BulkEmailController();
    extract($_POST);

    $data = $controller->createBulkEmail($receipients, $subject, $body);
    $data = $data->fetch_assoc();

    return json_encode($data);
}

/**
	@desc: UpdateBulkEmail the Resource
 */

function UpdateBulkEmail()
{
    $controller = new BulkEmailController();
    extract($_POST);

    $data = $controller->updateBulkEmail($id, $full_name, $email, $birthday);
    $data = $data->fetch_assoc();
    $data["birthday"] = date("d/m/Y", strtotime($data["birthday"]));
    return json_encode($data);
}

/**
	@desc: delete the Resource
 */

function DeleteBulkEmail()
{
    $controller = new BulkEmailController();
    extract($_POST);

    $data = $controller->deleteBulkEmail($id);

    return json_encode($data);
}
