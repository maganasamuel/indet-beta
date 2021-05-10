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

/*

if (file_exists("api/controllers/User.controller.php"))          //for Controllers in libs
    include_once("api/controllers/User.controller.php");
elseif (file_exists("libs/api/controllers/User.controller.php")) //For Root pages
    include_once("libs/api/controllers/User.controller.php");
elseif (file_exists("controllers/User.controller.php"))          //For API
    include_once("controllers/User.controller.php");
elseif (file_exists("../controllers/User.controller.php"))       //For Controllers in Controllers
    include_once("../controllers/User.controller.php");
    
*/
class Staff
{
    var $name = "";
    var $birthday = "";
    var $photo = "";
    var $role = "";
}

class MagazineController extends Database
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
		@desc: Get Birthdays
     */
    public function getMagazine(
        $magazine_id  //
    ) {
        //Get from Admins or Users
        $query = "SELECT * FROM magazines WHERE id = $magazine_id";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        return $dataset;
    }


    /**
		@desc: Get Birthdays
     */
    public function getAllMagazinePhotos() {
        //Get from Admins or Users
        $query = "SELECT JSON_EXTRACT(magazine_data, '$.photos') as photos FROM magazines";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        return $dataset;
    }

    /**
		@desc: Get Birthdays
     */
    public function getAllMagazines() {
        //Get from Admins or Users
        $query = "SELECT m.*, p.full_name FROM magazines m LEFT JOIN users u ON m.created_by = u.id LEFT JOIN personal_data p ON p.id = u.linked_id";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        return $dataset;
    }

    /**
		@desc: Get Birthdays
     */
    public function createMagazine(
        $magazine_data  //
    ) {
        $magazine_data_array = json_decode($magazine_data, true);
        $series = $magazine_data_array["issue_number"] . " " . $magazine_data_array["issue_number_line_2"];
        $date_created = date("Ymd");
        $user =$_SESSION["myuserid"];
        $magazine_data = $this->mysqli->real_escape_string($magazine_data);
        //Get from Admins or Users
        $query = "INSERT INTO magazines (series, magazine_data, date_created, created_by) VALUES ('$series', '$magazine_data', '$date_created', $user)";
        echo $query;
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);
        $magazine_id = $this->mysqli->insert_id;

        return $this->getMagazine($magazine_id);
    }

    

    
    function GetReceipients()
    {
        $output = [];

        //Get Admins
        $query = "SELECT p.full_name as name, p.email as email FROM users u LEFT JOIN personal_data p ON p.id = u.linked_id WHERE p.termination_date = ''";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        while ($row = $dataset->fetch_assoc()) {
            $output[] = $row;
        }

        //Get Advisers
        $query = "SELECT name, email FROM adviser_tbl WHERE termination_date = ''";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        while ($row = $dataset->fetch_assoc()) {
            $output[] = $row;
        }

        //Get Lead Generators
        $query = "SELECT name, email FROM leadgen_tbl WHERE termination_date = ''";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        while ($row = $dataset->fetch_assoc()) {
            $output[] = $row;
        }

        //Alphabetically arrange
        //usort($output, array('Magazine', 'sortByName'));

        return $output;
    }

    /**
		@desc: Get Birthdays
     */
    public function getAllRecords() {
        //Get from Admins or Users
        $query = "SELECT
        records_to_beat.*, record_types.*, records_to_beat.id as record_id
      FROM
        records_to_beat
      INNER JOIN
       record_types
      ON records_to_beat.record_type_id = record_types.id
      WHERE
      is_shown = 1";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        return $dataset;
    }

    
    /**
		@desc: Get Birthdays
     */
    public function deleteRecord($id) {
        //Get from Admins or Users
        
        $query = "DELETE from records_to_beat WHERE id=$id";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        return $dataset;
    }

    
}
