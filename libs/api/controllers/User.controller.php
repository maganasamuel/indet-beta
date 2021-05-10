<?php

/**
@name: User.controller.php
@author: Jesse
@desc:
	Serves as the API of the users
    This page handles all asynchronous javascript request from the above mentioned page
    
@returnType:
	JSON
 */
if (!isset($_SESSION)) {
    session_start();
}

if (file_exists("api/classes/database.class.php"))          //for Controllers in libs
    include_once("api/classes/database.class.php");
elseif (file_exists("libs/api/classes/database.class.php")) //For Root pages
    include_once("libs/api/classes/database.class.php");
elseif (file_exists("classes/database.class.php"))          //For API
    include_once("classes/database.class.php");
elseif (file_exists("../classes/database.class.php"))       //For Controllers in Controllers
    include_once("../classes/database.class.php");

if (file_exists("indet_dates_helper.php"))
    include_once("indet_dates_helper.php");
elseif (file_exists("libs/indet_dates_helper.php"))
    include_once("libs/indet_dates_helper.php");
elseif (file_exists("../libs/indet_dates_helper.php"))
    include_once("../libs/indet_dates_helper.php");
elseif (file_exists("../../libs/indet_dates_helper.php"))
    include_once("../../libs/indet_dates_helper.php");


class UserController extends Database
{
    /**
        @desc: Init class
     */
    public function __construct()
    {
        // init API
        parent::__construct();
    }

    /**
		@desc: Get all resources
     */
    public function getAllUsers()
    {
        $query = "SELECT * FROM users ORDER BY username ASC";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        return $dataset;
    }


    /**
		@desc: Get all resources
     */
    public function attemptLogin($username, $password)
    {

        $username_input = $this->clean($username);
        $password_input = $this->clean($password);
        
        $data = [];
        $data["message"] = "Unknown error occurred.";

        $query = "SELECT * FROM users WHERE username = '$username_input' LIMIT 1";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);
        if($dataset->num_rows > 0){
            $user = $dataset->fetch_assoc();
            extract($user);
            if (password_verify($password_input, $password)) {
                $user["bind"] = $user["password"];
                unset($user["password"]);
                $data["user"] = $user;           
                $data["token"] = "user_token_" . md5(uniqid());     
                $data["message"] = "Login successful";
            }else{
                $data["message"] = "Username and password does not match.";
            }
        }
        else {
            $data["message"] = "Unknown username.";
        }
        return $data;
    }

    /**
		@desc: Get all resources
     */
    public function checkAuthentication($user_id, $password)
    {

        $user_id = $this->clean($user_id);
        $password = $this->clean($password);
        
        $data = [];
        $data["message"] = "Unknown error occurred.";

        $query = "SELECT * FROM users WHERE id = $user_id AND password = '$password' LIMIT 1";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);
        if($dataset->num_rows > 0){
            $data["message"] = "User authenticated";
        }
        else {
            $data["message"] = "User unauthenticated.";
        }
        return $data;
    }

    /**
		@desc: Get resource specified
     */
    public function getUser($id = 0)
    {
        $query = "SELECT * FROM users WHERE id = $id LIMIT 1";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        return $dataset;
    }

    /**
		@desc: Get user with personal data
     */
    public function getUserWithData($id = 0)
    {
        $query = "SELECT * FROM users u LEFT JOIN personal_data p ON u.linked_id = p.id WHERE u.id = $id LIMIT 1";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);
        $dataset = $dataset->fetch_assoc();
        
        return $dataset;
    }

    /**
		@desc: Create resource
     */
    public function storeUser(
        $full_name = "",    
        $email = "",
        $birthday = ""
    ) {
        $date_helper = new INDET_DATES_HELPER();

        $full_name = $this->clean($full_name);
        $email = $this->clean($email);
        $birthday = $this->clean($birthday);
        $birthday = $date_helper->DateTimeToNZEntry($birthday);

        $query = "INSERT INTO users (full_name, email, birthday) VALUES ('$full_name','$email','$birthday')";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);
        $resource_id = $this->mysqli->insert_id;

        $dataset = $this->getUser($resource_id);
        return $dataset;
    }

    
    /**
		@desc: Update resource
     */
    public function updateUser(
        $id = 0,
        $full_name = "",     //The Client's ID
        $email = "",
        $birthday = ""
    ) {
        $date_helper = new INDET_DATES_HELPER();
        $full_name = $this->clean($full_name);
        $email = $this->clean($email);
        $birthday = $this->clean($birthday);
        $birthday = $date_helper->DateTimeToNZEntry($birthday);

        $query = "UPDATE users set full_name='$full_name', email='$email', birthday='$birthday' WHERE id = $id";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        $dataset = $this->getUser($id);
        
        return $dataset;
    }

    /**
		@desc: Destroy Resource
     */
    public function destroyUser(
        $id = 0    //
    ) {
        $query = "DELETE from users WHERE id = $id";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        return $dataset;
    }


}
