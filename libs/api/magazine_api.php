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


if (file_exists("classes/magazine.class.php"))
    require "classes/magazine.class.php";


if (file_exists("controllers/Magazine.controller.php"))          //for Controllers in libs
    include_once("controllers/Magazine.controller.php");

if (file_exists("classes/dateHelper.class.php"))          //for Controllers in libs
    include_once("classes/dateHelper.class.php");

$action = $_POST["action"];

//determine which function to trigger
switch ($action) {
    case "":
    default:
        echo json_encode(array("message" => "invalid request"));
        break;
        case "get_series":
            echo GetSeries();
            break;
        case "generate_magazine":
            echo GenerateMagazine();
            break;
        case "create_magazine":
            echo CreateMagazine();
            break;
        case "delete_record":
            echo DeleteRecord();
            break;
}

function GetSeries(){
    $date_helper = new DateHelper();
    extract($_POST);
    $date = $date_helper->NZFormatToDate($_POST["date"]);

    $magazine = new Series($date);
    
    $series = $magazine->issue_number;
    $magazine_data = json_encode($magazine);
    return json_encode(compact(explode(" ", "date series")));
}

function CreateMagazine(){
    extract($_POST);
    $magazineController = new MagazineController();
    $magazine = $magazineController->createMagazine($magazine_data);
    return json_encode(["magazine" => $magazine]);
}

function DeleteRecord(){
    extract($_POST);
    $magazineController = new MagazineController();
    $deleted = $magazineController->deleteRecord($id);
    $message = "Record successfully deleted";

    return json_encode(["message" => $message]);
}
