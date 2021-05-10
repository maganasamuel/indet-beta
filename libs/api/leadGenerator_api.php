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

if (!isset($_SESSION)) session_start();


if (file_exists("controllers/LeadGenerator.controller.php"))          //for Controllers in libs
    include_once("controllers/LeadGenerator.controller.php");

//fetch POST request parameter 
$action = $_POST["action"];

//determine which function to trigger
switch($action) {
	case "":
	default:
		echo json_encode(array("message"=>"invalid request"));
	break;
	case "fetch_lead_generator":
		echo show();
	break;
	case "create_lead_generator":
		echo store();
	break;
	case "update_lead_generator":
		echo update();
	break;
	case "delete_lead_generator":
		echo destroy();
	break;
	case "fetch_active_bdms":
		echo fetchActiveBDMs();
	break;
}

/**
	@desc: delete the Resource
	@param:
		id: id of the Lead Generator
*/

function show () {
	$controller = new LeadGeneratorController();
	extract($_POST);
	
    $data = $controller->getLeadGenerator($id);    
	$data = $data->fetch_assoc();
	
	return json_encode($data);
}


/**
	@desc: stores the Resource
	@param:
		name: name of the Lead Generator
		email: email of the Lead Generator
		birthday: birthday of the Lead Generator
		type: type of the Lead Generator
*/

function store () {
	$controller = new LeadGeneratorController();
	extract($_POST);
			
    $data = $controller->createLeadGenerator($name, $email, $birthday, $type, $image, $date_hired, $termination_date);      
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
	$controller = new LeadGeneratorController();
	extract($_POST);
	
    $data = $controller->updateLeadGenerator($id, $name, $email, $birthday, $type, $image, $date_hired, $termination_date);    
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
	$controller = new LeadGeneratorController();
	extract($_POST);
	
    $data = $controller->deleteLeadGenerator($id);    
    
	return json_encode($data);
}

/**
	@desc: delete the Resource
	@param:
		id: id of the Lead Generator
*/

function fetchActiveBDMs () {
	$controller = new LeadGeneratorController();
	extract($_POST);
	
	$data = $controller->getAllActiveBDM();    
	$bdms = [];

	while($row = $data->fetch_assoc()){
		$bdms[] = $row;
	}
	
	return json_encode($bdms);
}

?>