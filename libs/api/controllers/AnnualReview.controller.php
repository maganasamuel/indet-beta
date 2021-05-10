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

class AnnualReviewController extends Database {	
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
	public function getAllReviews () {
        $query = "Select c.name, c.id, a.id as review_id, a.data, i.date_issued, i.id as issued_client_id, i.issued, a.date_created as date_reviewed from annual_reviews a LEFT JOIN clients_tbl c ON c.id = a.client_id LEFT JOIN issued_clients_tbl i ON i.name = c.id ORDER BY date_created DESC";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);
        
		return $dataset;
    }	
    
	/**
		@desc: Get all questions from the specified Question Set
	*/
	public function getReview (
        $id = 0    //
    ) {
        $query = "Select c.name, c.id, a.id as review_id, a.data, i.date_issued, i.id as issued_client_id, i.issued, a.date_created as date_reviewed from annual_reviews a LEFT JOIN clients_tbl c ON c.id = a.client_id LEFT JOIN issued_clients_tbl i ON i.name = c.id  WHERE a.id = $id";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);
        
		return $dataset;
    }	
    
    
	/**
		@desc: Create new user with name and email
	*/
	public function createReview (
        $id = 0,     //The Client's ID
        $data = ""
    ) {
        $data = json_decode($data, true);

        foreach ($data as $key => $value) { 
            $value = str_replace("\r\n","<br>",$value);      
            $data[$key] = $value;
        }
        $data = json_encode($data, JSON_HEX_APOS);
        $date = date("Ymd");
        
        $query = "INSERT INTO annual_reviews (client_id, data, date_created) VALUES ($id,'$data','$date')";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);
        $review_id = $this->mysqli->insert_id;
        
        $query = "Select * from annual_reviews WHERE id = $review_id";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

		return $dataset;
	}	
    
	/**
		@desc: Get all questions from the specified Question Set
	*/
	public function deleteUser (
        $id = 0    //
    ) {
        $query = "DELETE from users WHERE id = $id";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);
        
		return $dataset;
	}	
}
?>