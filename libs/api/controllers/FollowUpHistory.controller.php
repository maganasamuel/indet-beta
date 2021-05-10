<?php
/**
@name: Question.controller.php
@author: Jesse
@desc:
	Serves as the API of the users
    This page handles all asynchronous javascript request from the above mentioned page
    
@returnType:
	JSON
*/
if(!isset($_SESSION)){
    session_start();
}

if(file_exists("api/classes/database.class.php"))
	include_once("api/classes/database.class.php");
elseif(file_exists("libs/api/classes/database.class.php"))
    include_once("libs/api/classes/database.class.php");
elseif(file_exists("classes/database.class.php"))
    include_once("classes/database.class.php");
elseif(file_exists("../classes/database.class.php"))
    include_once("../classes/database.class.php");

    
if (file_exists("indet_dates_helper.php"))
    include_once("indet_dates_helper.php");
elseif (file_exists("libs/indet_dates_helper.php"))
    include_once("libs/indet_dates_helper.php");
elseif (file_exists("../libs/indet_dates_helper.php"))
    include_once("../libs/indet_dates_helper.php");
elseif (file_exists("../../libs/indet_dates_helper.php"))
    include_once("../../libs/indet_dates_helper.php");

class FollowUpHistoryController extends Database {	
    /**
        @desc: Init class
    */
    public function __construct () {
        // init API
        parent::__construct();
    }
	
	/**
		@desc: Get all users
	*/
	public function getAllFollowUpHistory ($client_id) {
        $query = "SELECT f.id, f.notes, f.timestamp, f.user_id as user_id, u.username FROM follow_up_histories f LEFT JOIN users u ON f.user_id = u.id WHERE f.client_id = $client_id ORDER BY f.timestamp DESC, f.id DESC";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);
        
		return $dataset;
    }	

	/**
		@desc: Get all users
	*/
	public function getFollowUpHistory ($history_id) {
        $query = "SELECT f.id, f.notes, f.timestamp, f.user_id as user_id, u.username FROM follow_up_histories f LEFT JOIN users u ON f.user_id = u.id WHERE f.id = $history_id";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);
        
		return $dataset;
    }	


	/**
		@desc: Create new user with name and email
	*/
	public function createFollowUpHistory (
        $client_id = 0,
        $notes = ""
    ) {
        $notes = str_replace("\r\n","<br>",$notes);
        $notes = $this->clean($notes);
        $user_id = $_SESSION["myuserid"];
        
        $query = "INSERT INTO follow_up_histories (user_id, client_id, notes) VALUES ($user_id,$client_id,'$notes')";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);
        $history_id = $this->mysqli->insert_id;  

		return $this->getFollowUpHistory($history_id);
	}	
    
	/**
		@desc: Create new user with name and email
	*/
	public function updateFollowUpHistory (
        $history_id = 0,
        $notes = ""
    ) {
        $notes = str_replace("\r\n","<br>",$notes);
        $notes = $this->clean($notes);
        
        $query = "UPDATE follow_up_histories SET notes = '$notes' WHERE id=$history_id";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

		return $this->getFollowUpHistory($history_id);
	}	
    
	/**
		@desc: Get all questions from the specified Question Set
	*/
	public function deleteFollowUpHistory (
        $id = 0    //
    ) {
        $query = "DELETE from follow_up_histories WHERE id = $id";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);
        
		return $dataset;
	}	
}
