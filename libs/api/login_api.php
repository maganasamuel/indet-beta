<?php
/**
@name: common_api.php
@author: Jesse
@desc:
	Serves as the API of the admins
	This page handles all asynchronous javascript request from the above mentioned page
@returnType:
	JSON
*/
if (!isset($_SESSION)) session_start();

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: PUT, GET, POST");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization");

if (file_exists("controllers/User.controller.php"))          //for Controllers in libs
    include_once("controllers/User.controller.php");

if(!empty(file_get_contents("php://input")))
    $_POST = json_decode(file_get_contents("php://input"), true);

//fetch POST request parameter 
$action = $_POST["action"];

//determine which function to trigger
switch($action) {
	case "":
	default:
		echo json_encode(array("message"=>"invalid request"));
	break;
	case "attempt_authentication":
		echo attemptAuthentication();
	break;
	case "check_authentication":
		echo checkAuthentication();
	break;
}


/**
	@desc: saves the score of the specificied id_admin_detail
	@param:
		idtd: id_admin_detail - id of the admin answer
		id: id_user - id of the user who's checking the admin
		score: - score given to the specified id_admin_detail/ admin answer
*/
function attemptAuthentication () {
	$userController = new UserController();
    extract($_POST);
    
    $data = $userController->attemptLogin($username, $password);    
    
	return json_encode($data);
}

/**
	@desc: saves the score of the specificied id_admin_detail
	@param:
		idtd: id_admin_detail - id of the admin answer
		id: id_user - id of the user who's checking the admin
		score: - score given to the specified id_admin_detail/ admin answer
*/
function checkAuthentication () {
	$userController = new UserController();
	extract($_POST);
	$user = json_decode($_POST["user"]);
	
	$user_id = $user->id;
	$password_token = $user->bind;

    $data = $userController->checkAuthentication($user_id, $password_token);    
    
	return json_encode($data);
}

?>