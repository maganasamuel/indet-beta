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


class LeadGeneratorController extends Database
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
		@desc: Get all data
     */
    public function getAllLeadGenerators()
    {
        $query = "SELECT * FROM leadgen_tbl ORDER BY name ASC";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        return $dataset;
    }

    /**
		@desc: Get all data
     */
    public function getActiveLeadGenerators()
    {
        $query = "SELECT * FROM leadgen_tbl where termination_date = '' ORDER BY name ASC";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        return $dataset;
    }

    /**
		@desc: Get all bdms
     */
    public function getAllBDMs()
    {
        $query = "
        SELECT leadgen_tbl.*, 
        (SELECT COUNT(*) FROM clients_tbl c where c.leadgen = leadgen_tbl.id) AS leads_generated,
        (SELECT COUNT(*) FROM clients_tbl c where c.leadgen = leadgen_tbl.id AND c.status='Cancelled') AS leads_cancelled
         FROM leadgen_tbl where type = 'Face-to-Face Marketer' ORDER BY name ASC";

        //echo $query;
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        return $dataset;
    }

    /**
		@desc: Get all bdms
     */
    public function getAllActiveBDM()
    {
        // $query = "
        // SELECT leadgen_tbl.* FROM leadgen_tbl where type = 'Face-to-Face Marketer' and termination_date = '' ORDER BY name ASC";
        $query = "
        SELECT leadgen_tbl.*, 
        (SELECT COUNT(*) FROM clients_tbl c where c.leadgen = leadgen_tbl.id) AS leads_generated,
        (SELECT COUNT(*) FROM clients_tbl c where c.leadgen = leadgen_tbl.id AND c.status='Cancelled') AS leads_cancelled
         FROM leadgen_tbl where type = 'Face-to-Face Marketer' AND termination_date ='' ORDER BY name ASC";

        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        return $dataset;
    }

    public function getExBDM()
    {
        $date = date("Ymd");
        // $query = "Select * from leadgen_tbl WHERE termination_date != '' AND termination_date <= '$date' ORDER BY name";
        $query = "
        SELECT leadgen_tbl.*, 
        (SELECT COUNT(*) FROM clients_tbl c where c.leadgen = leadgen_tbl.id) AS leads_generated,
        (SELECT COUNT(*) FROM clients_tbl c where c.leadgen = leadgen_tbl.id AND c.status='Cancelled') AS leads_cancelled
         FROM leadgen_tbl where type = 'Face-to-Face Marketer' AND termination_date <= '$date' ORDER BY name ASC";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        return $dataset;
    }


    /**
        @desc: Get all bdms 
        @used_in: Magazine
     */
    public function getActiveBDMsKSData($date_from, $date_to)
    {
        $query = "SELECT COUNT(kd.commission) as deals, l.id as id, l.image as image, l.name as name FROM kiwisaver_deals kd LEFT JOIN kiwisaver_profiles kp ON kd.kiwisaver_profile_id = kp.id LEFT JOIN clients_tbl c ON c.id = kp.client_id LEFT JOIN leadgen_tbl l ON l.id = c.leadgen WHERE kd.issue_date <= '$date_to' AND kd.issue_date >= '$date_from' AND l.termination_date = '' AND kd.count = 'Yes' AND l.type='Face-to-Face Marketer' GROUP BY l.id";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        return $dataset;
    }

    /**
        @desc: Get all bdms
        @used_in: Magazine
     */
    public function getInactiveBDMsKSData($date_from, $date_to)
    {
        $query = "SELECT COUNT(kd.commission) as deals, l.image as image, l.name as name FROM kiwisaver_deals kd LEFT JOIN kiwisaver_profiles kp ON kd.kiwisaver_profile_id = kp.id LEFT JOIN clients_tbl c ON c.id = kp.client_id LEFT JOIN leadgen_tbl l ON l.id = c.leadgen WHERE kd.issue_date <= '$date_to' AND kd.issue_date >= '$date_from' AND l.termination_date != '' AND kd.count = 'Yes' AND l.type='Face-to-Face Marketer' GROUP BY l.id";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        return $dataset;
    }

    /**
		@desc: Get all bdms
     */
    public function getActiveBDMsData($date_from, $date_to)
    {
        //BDM's Data only
        // $query = "
        // SELECT leadgen_tbl.*, 
        // (SELECT COUNT(*) FROM clients_tbl c where c.leadgen = leadgen_tbl.id AND c.date_submitted <= $date_to AND c.date_submitted >= $date_from) AS 'generated',
        // (SELECT COUNT(*) FROM clients_tbl c where c.leadgen = leadgen_tbl.id AND c.status='Cancelled' AND c.date_status_updated <= $date_to AND c.date_status_updated >= $date_from) AS 'cancelled'
        //  FROM leadgen_tbl where type = 'Face-to-Face Marketer' AND termination_date = '' ORDER BY name ASC";
        
        //BDM and Telemarketers Data
        $query = "
        SELECT leadgen_tbl.*, 
        (SELECT COUNT(*) FROM clients_tbl c where c.leadgen = leadgen_tbl.id AND c.date_submitted <= $date_to AND c.date_submitted >= $date_from) AS 'generated',
        (SELECT COUNT(*) FROM clients_tbl c where c.leadgen = leadgen_tbl.id AND c.status='Cancelled' AND c.date_status_updated <= $date_to AND c.date_status_updated >= $date_from) AS 'cancelled'
         FROM leadgen_tbl where termination_date = '' ORDER BY name ASC";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        return $dataset;
    }


    /**
		@desc: Get all bdms
     */
    public function getInactiveBDMsData($date_from, $date_to)
    {
        $query = "
        SELECT leadgen_tbl.*, 
        (SELECT COUNT(*) FROM clients_tbl c where c.leadgen = leadgen_tbl.id AND c.date_submitted <= $date_to AND c.date_submitted >= $date_from) AS 'generated',
        (SELECT COUNT(*) FROM clients_tbl c where c.leadgen = leadgen_tbl.id AND c.status='Cancelled' AND c.date_submitted <= $date_to AND c.date_submitted >= $date_from) AS 'cancelled'
         FROM leadgen_tbl where type = 'Face-to-Face Marketer' AND termination_date != '' ORDER BY name ASC";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        return $dataset;
    }

    /**
		@desc: Get all bdms
     */
    public function getAllTelemarketers()
    {
        $query = "
        SELECT leadgen_tbl.*, 
        (SELECT COUNT(*) FROM clients_tbl c where c.leadgen = leadgen_tbl.id) AS leads_generated,
        (SELECT COUNT(*) FROM clients_tbl c where c.leadgen = leadgen_tbl.id AND c.status='Cancelled') AS leads_cancelled
         FROM leadgen_tbl where type = 'Telemarketer' ORDER BY name ASC";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        return $dataset;
    }

    /**
        @desc: Get all tms 
        @used_in: Magazine
     */
    public function getActiveTMsKSData($date_from, $date_to)
    {
        $query = "SELECT COUNT(kd.commission) as deals, l.id as id, l.image as image, l.name as name FROM kiwisaver_deals kd LEFT JOIN kiwisaver_profiles kp ON kd.kiwisaver_profile_id = kp.id LEFT JOIN clients_tbl c ON c.id = kp.client_id LEFT JOIN leadgen_tbl l ON l.id = c.leadgen WHERE kd.issue_date <= '$date_to' AND kd.issue_date >= '$date_from' AND l.termination_date = '' AND kd.count = 'Yes' AND l.type='Telemarketer' GROUP BY l.id";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        return $dataset;
    }

    /**
        @desc: Get all tms
        @used_in: Magazine
     */
    public function getInactiveTMsKSData($date_from, $date_to)
    {
        $query = "SELECT COUNT(kd.commission) as deals, l.image as image, l.name as name FROM kiwisaver_deals kd LEFT JOIN kiwisaver_profiles kp ON kd.kiwisaver_profile_id = kp.id LEFT JOIN clients_tbl c ON c.id = kp.client_id LEFT JOIN leadgen_tbl l ON l.id = c.leadgen WHERE kd.issue_date <= '$date_to' AND kd.issue_date >= '$date_from' AND l.termination_date != '' AND kd.count = 'Yes' AND l.type='Telemarketer' GROUP BY l.id";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        return $dataset;
    }

    /**
		@desc: Get all tms
     */
    public function getActiveTMsData($date_from, $date_to)
    {
        $query = "
        SELECT leadgen_tbl.*, 
        (SELECT COUNT(*) FROM clients_tbl c where c.leadgen = leadgen_tbl.id AND c.date_submitted <= $date_to AND c.date_submitted >= $date_from) AS 'generated',
        (SELECT COUNT(*) FROM clients_tbl c where c.leadgen = leadgen_tbl.id AND c.status='Cancelled' AND c.date_status_updated <= $date_to AND c.date_status_updated >= $date_from) AS 'cancelled'
         FROM leadgen_tbl where type = 'Telemarketer' AND termination_date = '' ORDER BY name ASC";
         $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        return $dataset;
    }

    /**
		@desc: Get all tms
     */
    public function getInactiveTMsData($date_from, $date_to)
    {
        $query = "
        SELECT leadgen_tbl.*, 
        (SELECT COUNT(*) FROM clients_tbl c where c.leadgen = leadgen_tbl.id AND c.date_submitted <= $date_to AND c.date_submitted >= $date_from) AS 'generated',
        (SELECT COUNT(*) FROM clients_tbl c where c.leadgen = leadgen_tbl.id AND c.status='Cancelled' AND c.date_submitted <= $date_to AND c.date_submitted >= $date_from) AS 'cancelled'
         FROM leadgen_tbl where type = 'Telemarketer' AND termination_date != '' ORDER BY name ASC";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        return $dataset;
    }

    /**
		@desc: Get data of specified lead generator
     */
    public function getLeadGenerator($id = 0)
    {
        $query = "SELECT leadgen_tbl.*, 
        (SELECT COUNT(*) FROM clients_tbl c where c.leadgen = leadgen_tbl.id) AS leads_generated,
        (SELECT COUNT(*) FROM clients_tbl c where c.leadgen = leadgen_tbl.id AND c.status='Cancelled') AS leads_cancelled
         FROM leadgen_tbl where id = $id LIMIT 1";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        return $dataset;
    }

    /**
		@desc: Get all clients of the specified lead generator
     */
    public function getLeadGeneratorClients(
        $id = 0    //
    ) {
        $query = "SELECT * FROM clients_tbl where leadgen = $id";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        return $dataset;
    }

    /**
		@desc: Get all clients of the specified lead generator
     */
    public function getLeadGeneratorClientsWithSubmissions(
        $id = 0    //
    ) {
        $query = "SELECT * FROM clients_tbl c LEFT JOIN submission_clients s ON c.id = s.client_id where c.leadgen = $id";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        return $dataset;
    }


    /**
		@desc: Get all clients of the specified lead generator
     */
    public function getLeadGeneratorSubmissionsAPI(
        $id = 0    //
    ) {
        $query = "SELECT * FROM clients_tbl c LEFT JOIN submission_clients s ON c.id = s.client_id where c.leadgen = $id";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);
        $api = 0;
        while($row = $dataset->fetch_assoc()){
            extract($row);
            $deals = json_decode($deals);
            
            foreach((array) $deals as $deal){
                if($deal->status == "Pending"){
                    $api += $deal->original_api;
                }
            }
        }
        return $api;
    }

    /**
		@desc: Get all clients of the specified lead generator
     */
    public function getLeadGeneratorIssuedAPI(
        $id = 0    //
    ) {
        $query = "SELECT * FROM clients_tbl c LEFT JOIN submission_clients s ON c.id = s.client_id where c.leadgen = $id AND c.id IN(SELECT name FROM issued_clients_tbl WHERE leadgen = $id)";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);
        $api = 0;
        while($row = $dataset->fetch_assoc()){
            extract($row);
            $deals = json_decode($deals);
            
            foreach((array) $deals as $deal){
                if($deal->status == "Issued"){
                    $api += $deal->issued_api;
                }
            }
        }
        return $api;
    }

    /**
		@desc: Get all clients of the specified lead generator
     */
    public function getLeadGeneratorSubmissionsAPIInDateRange(
        $id = 0,    
        $from = "",
        $to = ""
    ) {
        $query = "SELECT * FROM clients_tbl c LEFT JOIN submission_clients s ON c.id = s.client_id where c.leadgen = $id";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);
        $api = 0;
        while($row = $dataset->fetch_assoc()){
            extract($row);
            $deals = json_decode($deals);
            
            foreach((array) $deals as $deal){
                if($deal->status == "Pending" && $deal->submission_date >= $from && $deal->submission_date <= $to){
                    $api += $deal->original_api;
                }
            }
        }
        return $api;
    }

    /**
		@desc: Get all clients of the specified lead generator
     */
    public function getLeadGeneratorIssuedAPIInDateRange(
        $id = 0,
        $from = "",
        $to = ""
    ) {
        $query = "SELECT * FROM clients_tbl c LEFT JOIN submission_clients s ON c.id = s.client_id where c.leadgen = $id AND c.id IN(SELECT name FROM issued_clients_tbl WHERE leadgen = $id)";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);
        $api = 0;
        while($row = $dataset->fetch_assoc()){
            extract($row);
            $deals = json_decode($deals);
            
            foreach((array) $deals as $deal){
                if($deal->status == "Issued" && $deal->date_issued >= $from && $deal->date_issued <= $to){
                    $api += $deal->issued_api;
                }
            }
        }
        return $api;
    }
    /**
		@desc: Get all clients of the specified lead generator
     */
    public function getLeadGeneratorIssuedClients(
        $id = 0    //
    ) {
        $query = "SELECT * FROM clients_tbl c LEFT JOIN submission_clients s ON c.id = s.client_id LEFT JOIN issued_clients_tbl i ON i.name = c.id where c.leadgen = $id";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        return $dataset;
    }

    /**
		@desc: Get all clients of the specified lead generator
     */
    public function getLeadsGeneratedInDateRange(
        $id = 0,   //
        $from = "",
        $to = ""
    ) {
        $query = "SELECT * FROM clients_tbl c WHERE c.leadgen = $id AND c.date_submitted <= '$to' AND c.date_submitted >= '$from'";
        //echo $query;
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);
        return $dataset;
    }

    /**
		@desc: Get all clients of the specified lead generator
     */
    public function getLeadsCancelledInDateRange(
        $id = 0,   //
        $from = "",
        $to = ""
    ) {
        $query = "SELECT * FROM clients_tbl c WHERE c.status='Cancelled' AND c.leadgen = $id AND c.date_submitted <= '$to' AND c.date_submitted >= '$from'";
        //echo $query;
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);
        return $dataset;
    }

    /**
		@desc: Create lead generator
     */
    public function createLeadGenerator(
        $name = "",    
        $email = "",
        $birthday = "",
        $type = "",
        $image = "",
        $date_hired = "",
        $termination_date = ""
    ) {
        $date_helper = new INDET_DATES_HELPER();

        $name = $this->clean($name);
        $email = $this->clean($email);
        $birthday = $this->clean($birthday);
        $birthday = $date_helper->DateTimeToNZEntry($birthday);

        $date_hired = $this->clean($date_hired);
        $date_hired = $date_helper->DateTimeToNZEntry($date_hired);

        $termination_date = $this->clean($termination_date);
        $termination_date = $date_helper->DateTimeToNZEntry($termination_date);

        $query = "INSERT INTO leadgen_tbl (name, email, birthday, type, image, date_hired, termination_date) VALUES ('$name','$email','$birthday', '$type', '$image', '$date_hired', '$termination_date')";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);
        $leadgen_id = $this->mysqli->insert_id;

        $dataset = $this->getLeadGenerator($leadgen_id);
        return $dataset;
    }

    
    /**
		@desc: Edit lead generator
     */
    public function updateLeadGenerator(
        $id = 0,
        $name = "",     //The Client's ID
        $email = "",
        $birthday = "",
        $type = "",
        $image = "",
        $date_hired = "",
        $termination_date = ""
    ) {
        $date_helper = new INDET_DATES_HELPER();
        $name = $this->clean($name);
        $email = $this->clean($email);

        $birthday = $this->clean($birthday);
        $birthday = $date_helper->DateTimeToNZEntry($birthday);        

        $date_hired = $this->clean($date_hired);
        $date_hired = $date_helper->DateTimeToNZEntry($date_hired);

        $termination_date = $this->clean($termination_date);
        $termination_date = $date_helper->DateTimeToNZEntry($termination_date);

        $query = "UPDATE leadgen_tbl set name='$name', email='$email', birthday='$birthday', type='$type', image='$image', date_hired='$date_hired', termination_date='$termination_date' WHERE id = $id";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        $dataset = $this->getLeadGenerator($id);
        
        return $dataset;
    }

    /**
		@desc: Delete Lead Generator
     */
    public function deleteLeadGenerator(
        $id = 0    //
    ) {
        $query = "DELETE from leadgen_tbl WHERE id = $id";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        return $dataset;
    }


}
