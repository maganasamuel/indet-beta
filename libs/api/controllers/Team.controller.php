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

class TeamController extends Database {
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
	public function getAllTeams () {
        $query = "SELECT t.*, t.id as team_id, t.name as team_name, a.name as adviser_name FROM teams t LEFT JOIN adviser_tbl a ON t.leader = a.id ORDER BY t.name";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);
        
		return $dataset;
    }		
    
	/**
		@desc: Get all questions from the specified Question Set
	*/
	public function getTeam (
        $id = 0    //
    ) {
        $query = "SELECT t.*, t.id as team_id, t.name as team_name, a.name as adviser_name FROM teams t LEFT JOIN adviser_tbl a ON t.leader = a.id WHERE t.id = $id ORDER BY t.name LIMIT 1";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);
        $dataset = $dataset->fetch_assoc();
		return $dataset;
    }	
    
    
	/**
		@desc: Create new user with name and email
	*/
	public function createTeam (
        $name = "",
        $leader = 0
    ) {
        $name = $this->clean($name);
        
        $query = "INSERT INTO teams (name, leader) VALUES  ('$name',$leader)";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);
        $insert_id = $this->mysqli->insert_id;  
        
        $dataset = $this->getTeam($insert_id);
		return $dataset;
	}	
    
	/**
		@desc: Create new user with name and email
	*/
	public function updateTeam (
        $id = 0,
        $name = "",
        $leader = ""
    ) {
        $name = $this->clean($name);

        $query = "UPDATE teams SET leader = $leader, name = '$name' WHERE id = $id";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);
        
        $dataset = $this->getTeam($id);
		return $dataset;
	}	
    
	/**
		@desc: Get all questions from the specified Question Set
	*/
	public function deleteTeam (
        $id = 0    //
    ) {
        $query = "DELETE from teams WHERE id = $id";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);
        
		return $dataset;
	}	
}
