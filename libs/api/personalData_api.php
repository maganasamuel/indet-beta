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


if (file_exists("controllers/PersonalData.controller.php"))          //for Controllers in libs
    include_once("controllers/PersonalData.controller.php");

//fetch POST request parameter 
$action = $_POST["action"];

//determine which function to trigger
switch($action) {
	case "":
	default:
		echo json_encode(array("message"=>"invalid request"));
	break;
	case "fetch_data":
		echo show();
	break;
	case "create_data":
		echo store();
	break;
	case "update_data":
		echo update();
	break;
	case "delete_data":
		echo destroy();
	break;
}

/**
	@desc: delete the Resource
	@param:
		id: id of the Lead Generator
*/

function show () {
	$controller = new PersonalDataController();
	extract($_POST);
	
    $data = $controller->getData($id);    
	$data = $data->fetch_assoc();
	
	return json_encode($data);
}


/**
	@desc: stores the Resource
	@param:
		full_name: name of the Lead Generator
		email: email of the Lead Generator
		birthday: birthday of the Lead Generator
*/

function store () {
	$controller = new PersonalDataController();
	extract($_POST);
	
    $data = $controller->storeData($full_name, $email, $birthday, $role, $image, $date_hired, $termination_date);      
	$data = $data->fetch_assoc();
    $data["birthday"] = date("d/m/Y", strtotime($data["birthday"])); 
    $data["date_hired"] = date("d/m/Y", strtotime($data["date_hired"])); 
    $data["termination_date"] = date("d/m/Y", strtotime($data["termination_date"])); 
    
	return json_encode($data);
}

/**
	@desc: update the Resource
	@param:
		name: name of the Lead Generator
		email: email of the Lead Generator
		birthday: birthday of the Lead Generator
		type: type of the Lead Generator
*/

function update () {
	$controller = new PersonalDataController();
	extract($_POST);
	
    $data = $controller->updateData($id, $full_name, $email, $birthday, $role, $image, $date_hired, $termination_date);    
	$data = $data->fetch_assoc();

    $data["birthday"] = date("d/m/Y", strtotime($data["birthday"]));
    $data["date_hired"] = date("d/m/Y", strtotime($data["date_hired"]));
	$data["termination_date"] = date("d/m/Y", strtotime($data["termination_date"]));
	
	return json_encode($data);
}

/**
	@desc: delete the Resource
	@param:
		id: id of the Lead Generator
*/

function destroy () {
	$controller = new PersonalDataController();
	extract($_POST);
	
    $data = $controller->destroyData($id);    
    
	return json_encode($data);
}

?>