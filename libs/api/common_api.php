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
include_once("../Common.controller.php");

//fetch POST request parameter 
$action = $_POST["action"];

//determine which function to trigger
switch($action) {
	case "":
	default:
		echo json_encode(array("message"=>"invalid request"));
	break;
	case "delete_customized_invoice":
		echo deleteCustomizedInvoice($_POST["id"]);
	break;
}


/**
	@desc: saves the score of the specificied id_admin_detail
	@param:
		idtd: id_admin_detail - id of the admin answer
		id: id_user - id of the user who's checking the admin
		score: - score given to the specified id_admin_detail/ admin answer
*/

function deleteCustomizedInvoice ($id) {
	$common = new CommonController();
	
    $data = $common->deleteCustomizedInvoice($id);    
    
	return json_encode($data);
}

?>