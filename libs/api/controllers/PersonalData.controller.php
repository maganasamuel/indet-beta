<?php

/**
@name: LeadGenerator.controller.php
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


class PersonalDataController extends Database
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
    public function getAllData()
    {
        $query = "SELECT * FROM personal_data ORDER BY full_name ASC";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        return $dataset;
    }


    /**
		@desc: Get all resources
     */
    public function getAllActiveStaff()
    {
        $query = "SELECT * FROM personal_data WHERE termination_date = '' ORDER BY full_name ASC";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        return $dataset;
    }

    /**
		@desc: Get all resources
     */
    public function getData($id = 0)
    {
        $query = "SELECT * FROM personal_data WHERE id = $id LIMIT 1";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        return $dataset;
    }

    /**
		@desc: Create resource
     */
    public function storeData(
        $full_name = "",    
        $email = "",
        $birthday = "",
        $role = "",
        $image = "",
        $date_hired = "",
        $termination_date = ""
    ) {
        $date_helper = new INDET_DATES_HELPER();

        $full_name = $this->clean($full_name);
        $email = $this->clean($email);
        
        $birthday = $this->clean($birthday);
        $birthday = $date_helper->DateTimeToNZEntry($birthday);

        $role = $this->clean($role);        
        
        $date_hired = $this->clean($date_hired);
        $date_hired = $date_helper->DateTimeToNZEntry($date_hired);
        
        $termination_date = $this->clean($termination_date);
        $termination_date = $date_helper->DateTimeToNZEntry($termination_date);


        $query = "INSERT INTO personal_data (full_name, email, birthday, role, image, date_hired, termination_date) 
        VALUES ('$full_name','$email','$birthday','$role','$image','$date_hired','$termination_date')";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);
        $resource_id = $this->mysqli->insert_id;

        $dataset = $this->getData($resource_id);
        return $dataset;
    }

    
    /**
		@desc: Update resource
     */
    public function updateData(
        $id = 0,
        $full_name = "",     //The Client's ID
        $email = "",
        $birthday = "",
        $role = "",
        $image = "",
        $date_hired = "",
        $termination_date = ""
    ) {
        $date_helper = new INDET_DATES_HELPER();
        $full_name = $this->clean($full_name);
        $email = $this->clean($email);

        $birthday = $this->clean($birthday);
        $birthday = $date_helper->DateTimeToNZEntry($birthday); 
        $role = $this->clean($role);          
        
        $date_hired = $this->clean($date_hired);
        $date_hired = $date_helper->DateTimeToNZEntry($date_hired);
        
        $termination_date = $this->clean($termination_date);
        $termination_date = $date_helper->DateTimeToNZEntry($termination_date);


        $query = "UPDATE personal_data set full_name='$full_name', email='$email', birthday='$birthday', role='$role', 
        image='$image', date_hired='$date_hired', termination_date='$termination_date' WHERE id = $id";

        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        $dataset = $this->getData($id);
        
        return $dataset;
    }

    /**
		@desc: Destroy Resource
     */
    public function destroyData(
        $id = 0    //
    ) {
        $query = "DELETE from personal_data WHERE id = $id";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        return $dataset;
    }


}
