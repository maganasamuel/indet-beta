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

class BulkEmailController extends Database {	
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
	public function getAllBulkEmails () {
        $query = "Select *, u.username as name, b.id as id from bulk_emails b LEFT JOIN users u ON b.sender_id = u.id";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);
        
		return $dataset;
    }		
    
	/**
		@desc: Get all questions from the specified Question Set
	*/
	public function getBulkEmail (
        $id = 0    //
    ) {
        $query = "Select * from bulk_emails WHERE id = $id LIMIT 1";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);
        $dataset = $dataset->fetch_assoc();
		return $dataset;
    }	
    
    
	/**
		@desc: Create new user with name and email
	*/
	public function createBulkEmail (
        $receipients = "",
        $subject = "",
        $body = ""
    ) {

        $receipients = $this->clean($receipients);
        $body = $this->clean($body);
        $sender_id = $_SESSION["myuserid"];
        $date = date("Ymd"); 

        $query = "INSERT INTO bulk_emails (receipients, subject, body, sender_id, date_created) VALUES ('$receipients','$subject','$body',$sender_id, '$date')";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);
        $review_id = $this->mysqli->insert_id;  
        
        $query = "Select * from bulk_emails WHERE id = $review_id";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

		return $dataset;
	}	
    
	/**
		@desc: Get all questions from the specified Question Set
	*/
	public function deleteBulkEmail (
        $id = 0    //
    ) {
        $query = "DELETE from bulk_emails WHERE id = $id";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);
        
		return $dataset;
	}	
}
