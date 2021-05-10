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
date_default_timezone_set('Pacific/Auckland');

if (!isset($_SESSION)) {
    session_start();
}

if (file_exists("api/classes/database.class.php"))
    include_once("api/classes/database.class.php");
elseif (file_exists("libs/api/classes/database.class.php"))
    include_once("libs/api/classes/database.class.php");
elseif (file_exists("classes/database.class.php"))
    include_once("classes/database.class.php");
elseif (file_exists("../classes/database.class.php"))
    include_once("../classes/database.class.php");

if (file_exists("api/classes/general.class.php"))
    include_once("api/classes/general.class.php");
elseif (file_exists("libs/api/classes/general.class.php"))
    include_once("libs/api/classes/general.class.php");
elseif (file_exists("classes/general.class.php"))
    include_once("classes/general.class.php");
elseif (file_exists("../classes/general.class.php"))
    include_once("../classes/general.class.php");

if (file_exists("../PHPMailer/PHPMailerAutoload.php"))
    include_once("../PHPMailer/PHPMailerAutoload.php");
elseif (file_exists("PHPMailer/PHPMailerAutoload.php"))
    include_once("PHPMailer/PHPMailerAutoload.php");
elseif (file_exists("../../PHPMailer/PHPMailerAutoload.php"))
    include_once("../../PHPMailer/PHPMailerAutoload.php");


class ClientController extends Database
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
		@desc: Get all users
     */
    public function getAllClients()
    {
        $query = "Select * from clients_tbl ORDER BY name";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        return $dataset;
    }

    /**
		@desc: Get all users
     */
    public function getNonKiwiSaverClients()
    {
        $query = "Select * from clients_tbl WHERE id NOT IN (SELECT client_id FROM kiwisaver_profiles) AND assigned_to != 0 ORDER BY name";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        return $dataset;
    }

    /**
		@desc: Get client with the specified id
     */
    public function getLeadDataFromClientID($client_id)
    {
        //Fetch latest reference number
        $query = "SELECT * FROM leads_data WHERE client_id='$client_id'";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        return $dataset;
    }


    /**
		@desc: Get client with the specified id
     */
    public function getLatestClientDataReferenceNumber($prefix, $date)
    {
        //Fetch latest reference number
        $query = "Select RIGHT(reference_number, 3) as reference_number FROM send_client_data WHERE reference_number LIKE '$prefix" . "$date%' ORDER BY id DESC LIMIT 1";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        return $dataset;
    }

    /**
		@desc: Send Client Data to receipient
     */
    public function createSendClientDataEntry($name, $email, $client_ids)
    {
        $date = date("dmY");
        $today = date("Ymd");
        $app = new General();

        $reference_number = "BDM$date" . $app->convertToNDigits(($this->getLatestClientDataReferenceNumber("BDM",$date)->fetch_assoc()["reference_number"] + 1), 3);
        $user_id = $_SESSION["myuserid"];

        $client_ids = json_encode($client_ids);

        //Fetch latest reference number
        $query = "INSERT INTO send_client_data (reference_number, client_ids,name, email, date_sent, user_id) VALUES ('$reference_number','$client_ids','$name','$email','$today', $user_id)";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        return $this->mysqli->insert_id;
    }

    public function getSendClientDataRequest($id)
    {
        $query = "SELECT * FROM send_client_data WHERE id = $id";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        return $dataset;
    }

    
    /**
		@desc: Send Client Data to receipient
     */
    public function createSendIssuedClientDataEntry($name, $email, $client_ids)
    {
        $date = date("dmY");
        $today = date("Ymd");
        $app = new General();

        $reference_number = "IC$date" . $app->convertToNDigits(($this->getLatestClientDataReferenceNumber("IC",$date)->fetch_assoc()["reference_number"] + 1), 3);
        $user_id = $_SESSION["myuserid"];

        $client_ids = json_encode($client_ids);

        //Fetch latest reference number
        $query = "INSERT INTO send_issued_client_data (reference_number, client_ids,name, email, date_sent, user_id) VALUES ('$reference_number','$client_ids','$name','$email','$today', $user_id)";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        return $this->mysqli->insert_id;
    }


    public function getSendIssuedClientDataRequest($id)
    {
        $query = "SELECT * FROM send_issued_client_data WHERE id = $id";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        return $dataset;
    }

    /**
		@desc: Get client with the specified id
     */
    public function getClient($client_id = "")
    {
        $query = "Select c.*, a.name as adviser_name, a.email as adviser_email, l.name as leadgen_name from clients_tbl c LEFT JOIN adviser_tbl a ON c.assigned_to = a.id LEFT JOIN leadgen_tbl l ON l.id = c.leadgen WHERE c.id = $client_id LIMIT 1";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);
        return $dataset;
    }

    /**
		@desc: Get client with the specified id
     */
    public function updateClientSeenStatus($client_id, $status)
    {
        $query = "UPDATE `clients_tbl` SET `seen_status` = '$status' WHERE id = '$client_id'";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        $query = "Select c.*, a.name as adviser_name, a.email as adviser_email, l.name as leadgen_name from clients_tbl c LEFT JOIN adviser_tbl a ON c.assigned_to = a.id LEFT JOIN leadgen_tbl l ON l.id = c.leadgen WHERE c.id = '$client_id'";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);
        return $dataset;
    }

    /**
		@desc: Get client with the specified id
     */
    public function updateClientAppointment($client_id, $appt_date, $appt_time)
    {
        $query = "Select c.*, a.name as adviser_name, a.email as adviser_email, l.name as leadgen_name from clients_tbl c LEFT JOIN adviser_tbl a ON c.assigned_to = a.id LEFT JOIN leadgen_tbl l ON l.id = c.leadgen WHERE c.id = '$client_id'";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);
        $client = $dataset->fetch_assoc();

        $seen_status = "";

        switch($client["seen_status"]){
            case "Seen":
                $seen_status = ", seen_status = 'Re-moved'";
            break;
            case "Unseen":
                $seen_status = ", seen_status = 'Scheduled'";
            break;
        }

        $query = "UPDATE `clients_tbl` SET `appt_date` = '$appt_date', `time` = '$appt_time'$seen_status WHERE id = '$client_id'";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        $query = "Select c.*, a.name as adviser_name, a.email as adviser_email, l.name as leadgen_name from clients_tbl c LEFT JOIN adviser_tbl a ON c.assigned_to = a.id LEFT JOIN leadgen_tbl l ON l.id = c.leadgen WHERE c.id = '$client_id'";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);
        $client = $dataset->fetch_assoc();
        //Notify admin abput the update
        ob_start();

        $mail = new PHPMailer;
        $mail->setFrom('indet@onlineinsure.co.nz', 'INDET');
        $debugging_mode = true;

        if ($debugging_mode) {
         $mail->addAddress('jesse@eliteinsure.co.nz', 'Test');
        } else {
            $mail->addAddress('admin@eliteinsure.co.nz', 'Admin');
            $mail->addBCC('jesse@eliteinsure.co.nz', 'IT Support');
        }
        
        $mail->addReplyTo('indet@onlineinsure.co.nz', 'INDET');
        $mail->Subject  = "Client Appointment Schedule Update";
        $mail->Body     = 
            "Name: " . $client["name"] . "
            \r\nAdviser: " . $client["adviser_name"] . "
            \r\nEmail: " . $client["email"] . "
            \r\nPhone: " . $client["appt_time"] . "
            \r\nAddress: " . $client["address"] . "
            \r\nAppointment schedule changed to: " . date("d/m/Y", strtotime($client["appt_date"])) . " " . date("h:i a", strtotime($client["time"]));

        if (!$mail->send()) {
        echo 'Message could not be sent.<br>';
        echo 'Mailer Error: ' . $mail->ErrorInfo;
        } else {
            return $client;
        }
        ob_end_flush();

    }

    /**
		@desc: Send Client Data to receipient
     */
    public function createClientUpdate($client_id, $sender_id, $message)
    {
        $client_id = $this->clean($client_id);
        $sender_id = $this->clean($sender_id);
        $message = $this->clean($message);
        
        //Fetch latest reference number
        $query = "INSERT INTO client_updates (client_id, sender_id, message, created_at) VALUES ($client_id,$sender_id,'$message'," . time() . ")";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        return $this->getClientUpdate($this->mysqli->insert_id);
    }

    /**
		@desc: Send Client Data to receipient
     */
    public function getClientUpdates($client_id)
    {        
        //Fetch latest reference number
        $query = "SELECT cu.message, u.username as username, u.id as user_id, cu.created_at as delivery_time FROM client_updates cu LEFT JOIN users u ON cu.sender_id = u.id WHERE cu.client_id = $client_id ORDER BY cu.created_at";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        $updates = [];

        while($row = $dataset->fetch_assoc()){
            $updates[] = $row;
        }
        return $updates;
    }


    /**
		@desc: Send Client Data to receipient
     */
    public function getClientUpdate($client_update_id)
    {        
        //Fetch latest reference number
        $query = "SELECT cu.message, u.username as username, u.id as user_id, cu.created_at as delivery_time FROM client_updates cu LEFT JOIN users u ON cu.sender_id = u.id WHERE cu.id = $client_update_id";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);
        return $dataset;
    }


    /**
		@desc: Get client with the specified id
     */
    public function getClientsAssignedToAdviser($adviser_id = "", $requesting = "Adviser")
    {
        $six_months_ago = date("Ymd", strtotime(date("Ymd") . " -6 months"));
        $four_months_ago = date("Ymd", strtotime(date("Ymd") . " -4 months"));

        //Fetch all clients assigned to adviser 
        $query = "Select c.*, a.name as adviser_name, a.email as adviser_email, l.name as leadgen_name from clients_tbl c LEFT JOIN adviser_tbl a ON c.assigned_to = a.id LEFT JOIN leadgen_tbl l ON l.id = c.leadgen WHERE c.assigned_to = $adviser_id";
        
        //Remove clients that have submissions and/or have kiwisaver enrollments
        $query .= " AND c.id NOT IN (SELECT client_id FROM submission_clients) AND c.id NOT IN (SELECT client_id FROM kiwisaver_profiles)";

        //var_dump($has_expiration);
        if($requesting != "Admin"){
            //Remove Appts that were not seen after 4 months since the date of assigning the lead
            $query .= " AND NOT (c.seen_status != 'Seen' AND c.assigned_date <= '$four_months_ago')";

            //Remove Appts that were seen after 6 months since the date of assigning the lead
            $query .= " AND NOT (c.seen_status = 'Seen' AND c.assigned_date <= '$six_months_ago')";
        }

        $query .= " ORDER BY c.appt_date DESC, c.time DESC";

        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        return $dataset;
    }

    /**
		@desc: Get client with the specified id
     */
    public function getClientsInArray($client_ids)
    {
        $imploded_array = $client_ids;
        if (is_array($client_ids)) {
            $imploded_array = implode(",", $client_ids);
        }

        $query = "SELECT * from clients_tbl where id IN ($imploded_array) ORDER BY assigned_date DESC";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);
        return $dataset;
    }

    /**
		@desc: Get client exempted in invoice
     */
    public function getInvoiceExemptedClients()
    {

        $query = "SELECT * from clients_tbl WHERE status = 'Agreement'";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);
        return $dataset;
    }

    /**
		@desc: Get client exempted in invoice
     */
    public function removeClientExemption($exemption_id)
    {
        $query = "SELECT i.id as exemption_id, i.date_status_updated,  i.client_id, c.*, i.status as exemption_status  from invoice_exempted_clients i LEFT JOIN clients_tbl c ON i.client_id = c.id WHERE i.id = $exemption_id";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);
        $data = $dataset->fetch_assoc();

        $query = "DELETE FROM invoice_exempted_clients WHERE id = $exemption_id";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        $data = $this->getClient($data["id"]);

        return $data->fetch_assoc();
    }


    /**
		@desc: Get client exempted in invoice
     */
    public function getExemptedClient($exemption_id)
    {
        $query = "SELECT i.id as exemption_id, i.client_id, i.date_status_updated, c.*, i.status as exemption_status  from invoice_exempted_clients i LEFT JOIN clients_tbl c ON i.client_id = c.id WHERE i.id = $exemption_id";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);
        return $dataset->fetch_assoc();
    }

    /**
		@desc: Get client exempted in invoice
     */
    public function getInvoiceExemptedClientsFromAdviser($adviser_id)
    {
        $query = " SELECT * from clients_tbl WHERE status = 'Agreement' AND assigned_to = $adviser_id";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);
        return $dataset;
    }


    function exemptClientFromInvoice(
        $client_id = 0,
        $status = "Waived"
    ) {
        $date = date("Ymd");

        $query = "INSERT INTO invoice_exempted_clients (client_id, status,date_status_updated) VALUES ($client_id, '$status', '$date')";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        $insert_id = $this->mysqli->insert_id;

        return $this->getExemptedClient($insert_id);
    }
    /**
		@desc: Get client with the matching email
     */
    public function getClientByEmail($email = "")
    {
        $query = "Select * from clients_tbl WHERE email = '$email' AND email != '' LIMIT 1";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);
        return $dataset;
    }

    /**
		@desc: Get client generated by
     */
    public function getClientsGeneratedBy($leadgen_id)
    {
        $clients = array();

        $query = "Select * from clients_tbl WHERE leadgen = '$leadgen_id'";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        while ($row = $dataset->fetch_assoc()) {
            $clients[] = $row;
        }
        return $clients;
    }

    /**
		@desc: Get client generated by
     */
    public function getClientsGeneratedByLeadGeneratorInRange($leadgen_id, $date_from, $date_to)
    {
        $clients = array();

        $query = "Select * from clients_tbl WHERE leadgen = '$leadgen_id' AND date_submitted >= '$date_from' AND date_submitted <= '$date_to' ORDER BY date_submitted DESC";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        while ($row = $dataset->fetch_assoc()) {
            $clients[] = $row;
        }
        return $clients;
    }

    /**
		@desc: Get all issued clients
     */
    public function getAllIssuedClients()
    {
        $query = "Select c.*, TRIM(c.name) as name from clients_tbl c LEFT JOIN issued_clients_tbl i ON i.name = c.id WHERE c.id IN (SELECT name from issued_clients_tbl) ORDER BY name";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        return $dataset;
    }


    /**
		@desc: Get all issued clients assigned to specified adviser
     */
    public function getIssuedClientsAssignedTo($adviser_id = 0)
    {
        $query = "Select c.*, i.name as issued_client_id from clients_tbl c LEFT JOIN issued_clients_tbl i ON i.name = c.id WHERE c.assigned_to = $adviser_id ORDER BY name";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        return $dataset;
    }

    /**
		@desc: Get all users
     */
    public function getAllIssuedClientProfiles()
    {
        $query = "SELECT c.id as client_id, c.lead_by, i.id,c.email as client_email, c.name,a.name as x,l.name as y,i.appt_date,i.appt_time,i.address,i.leadgen,i.assigned_to,i.assigned_date,i.type_of_lead,i.issued,i.date_issued,i.notes,s.deals as deals_data FROM issued_clients_tbl i LEFT JOIN submission_clients s ON s.client_id = i.name LEFT JOIN adviser_tbl a ON i.assigned_to = a.id LEFT JOIN leadgen_tbl l ON i.leadgen = l.id LEFT JOIN clients_tbl c ON i.name = c.id order by i.date_issued desc";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        return $dataset;
    }

    /**
		@desc: Get all users
     */
    public function getIssuedClientProfile($client_id)
    {
        $query = "SELECT c.id as client_id, c.lead_by, i.id,c.address as client_address, c.email as client_email,c.appt_time as client_phone, c.name,a.name as x,l.name as y,i.appt_date,i.appt_time,i.address,i.leadgen,i.assigned_to,i.assigned_date,i.type_of_lead,i.issued,i.date_issued,i.notes,s.deals as deals_data FROM issued_clients_tbl i LEFT JOIN submission_clients s ON s.client_id = i.name LEFT JOIN adviser_tbl a ON i.assigned_to = a.id LEFT JOIN leadgen_tbl l ON i.leadgen = l.id LEFT JOIN clients_tbl c ON i.name = c.id WHERE c.id = $client_id";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        return $dataset;
    }

    /**
		@desc: Get all users
     */
    public function UpdateIssuedClientProfile($client_id, $assigned_to, $leadgen)
    {
        $query = "UPDATE issued_clients_tbl SET assigned_to = $assigned_to, leadgen = $leadgen WHERE name = $client_id";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        $dataset = $this->getClient($client_id);

        return $dataset;
    }


    /**
		@desc: Get all issued clients with issued deals
     */
    public function getAllIssuedClientsWithIssuedDeals()
    {
        $query = "Select c.*, s.deals as deals from clients_tbl c LEFT JOIN issued_clients_tbl i ON i.name = c.id LEFT JOIN submission_clients s ON s.client_id = c.id WHERE c.id IN (SELECT name FROM issued_clients_tbl) ORDER BY c.name";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);
        $clients = array();

        while ($data = $dataset->fetch_assoc()) {
            $issued_deals = 0;
            $deals = json_decode($data["deals"]);

            foreach ($deals as $deal) {
                if (isset($deal->status)) {
                    if ($deal->status == "Issued")
                        $issued_deals++;
                }

                if (isset($deal->clawback_status)) {
                    if ($deal->clawback_status == "Cancelled")
                        $issued_deals--;
                }
            }

            $data["issued_deals"] = $issued_deals;
            if ($issued_deals > 0) {
                $clients[] = $data;
            }
        }

        return $clients;
    }

    /**
		@desc: Get all clients without any Submissions
     */
    public function getAllClientsWithoutSubmissions()
    {
        $query = "SELECT id,name,leadgen,assigned_to FROM clients_tbl WHERE status!='Cancelled' AND id NOT IN (SELECT client_id FROM submission_clients) ORDER BY TRIM(name) ASC";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        return $dataset;
    }

    /**
		@desc: Get all Submission clients without any Issued Deals
     */
    public function getAllSubmissionClients()
    {
        $query = "Select c.*, s.deals as deals from clients_tbl c LEFT JOIN submission_clients s ON s.client_id = c.id WHERE s.client_id NOT IN (SELECT name FROM issued_clients_tbl) ORDER BY c.name";

        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);
        $clients = array();

        while ($data = $dataset->fetch_assoc()) {
            $deals = 0;
            $submissions = 0;
            $deals = json_decode($data["deals"]);
            foreach ($deals as $deal) {
                if (isset($deal->status)) {
                    if ($deal->status == "Pending" || $deal->status == "Deferred" || $deal->status == "Withdrawn")
                        $submissions++;
                }
            }

            $deals = count($deals);
            if ($deals == $submissions && $deals > 0) {
                $clients[] = $data;
            }
        }
        return $clients;
    }

    public function updateAppointmentStatus($id, $status){
        $query = "UPDATE `clients_tbl` SET `status`='$status' WHERE `id`=$id";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);
        
        return true;
    }

    /**
		@desc: Get all issued clients with issued deals
     */
    public function getAllCancelledClients()
    {
        $query = "Select c.*, s.deals as deals from clients_tbl c LEFT JOIN issued_clients_tbl i ON i.name = c.id LEFT JOIN submission_clients s ON s.client_id = c.id WHERE c.id IN (SELECT name FROM issued_clients_tbl) ORDER BY name";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);
        $clients = array();

        while ($data = $dataset->fetch_assoc()) {
            $cancelled_deals = 0;
            $deals = json_decode($data["deals"]);

            foreach ($deals as $deal) {
                if (isset($deal->clawback_status)) {
                    if ($deal->clawback_status == "Cancelled")
                        $cancelled_deals++;
                }
            }

            if (count($deals) == $cancelled_deals && $cancelled_deals > 0) {
                $clients[] = $data;
            }
        }

        return $clients;
    }

    /**
		@desc: Get all Submission clients assigned to specified adviser
     */
    public function getAllSubmissionClientsAssignedTo($adviser_id)
    {
        $query = "Select c.*, s.deals as deals from clients_tbl c LEFT JOIN submission_clients s ON s.client_id = c.id WHERE c.id NOT IN (SELECT name FROM issued_clients_tbl) AND c.assigned_to = $adviser_id ORDER BY name";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);
        $clients = array();

        while ($data = $dataset->fetch_assoc()) {
            $deals = 0;
            $submissions = 0;
            $deals = json_decode($data["deals"]);
            foreach ($deals as $deal) {
                if (isset($deal->status)) {
                    if ($deal->status == "Pending" || $deal->status == "Deferred" || $deal->status == "Withdrawn")
                        $submissions++;
                }
            }

            $deals = count($deals);
            if ($deals == $submissions && $deals > 0) {
                $clients[] = $data;
            }
        }

        return $clients;
    }

    /**
		@desc: Get all issued clients assigned to specified adviser
     */
    public function getAllIssuedClientsWithIssuedDealsAssignedTo($adviser_id)
    {
        $query = "Select c.*, s.deals as deals from clients_tbl c LEFT JOIN issued_clients_tbl i ON i.name = c.id LEFT JOIN submission_clients s ON s.client_id = c.id WHERE c.id IN (SELECT name FROM issued_clients_tbl) AND c.assigned_to = $adviser_id ORDER BY name";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);
        $clients = array();

        while ($data = $dataset->fetch_assoc()) {
            $issued_deals = 0;
            $deals = json_decode($data["deals"]);

            foreach ($deals as $deal) {
                if (isset($deal->status)) {
                    if ($deal->status == "Issued")
                        $issued_deals++;
                }

                if (isset($deal->clawback_status)) {
                    if ($deal->clawback_status == "Cancelled")
                        $issued_deals--;
                }
            }

            $data["issued_deals"] = $issued_deals;
            if ($issued_deals > 0) {
                $clients[] = $data;
            }
        }

        return $clients;
    }

    /**
		@desc: Get all issued clients assigned to specified adviser
     */
    public function getAllCancelledClientsAssignedTo($adviser_id)
    {
        $query = "Select c.*, s.deals as deals from clients_tbl c LEFT JOIN issued_clients_tbl i ON i.name = c.id LEFT JOIN submission_clients s ON s.client_id = c.id WHERE c.id IN (SELECT name FROM issued_clients_tbl) AND c.assigned_to = $adviser_id ORDER BY name";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);
        $clients = array();

        while ($data = $dataset->fetch_assoc()) {
            $cancelled_deals = 0;
            $deals = json_decode($data["deals"]);

            foreach ($deals as $deal) {
                if (isset($deal->clawback_status)) {
                    if ($deal->clawback_status == "Cancelled")
                        $cancelled_deals++;
                }
            }

            if (count($deals) == $cancelled_deals && $cancelled_deals > 0) {
                $clients[] = $data;
            }
        }

        return $clients;
    }

    /**
		@desc: Get all questions from the specified Question Set
     */
    public function deleteClient(
        $id = 0    //
    ) {
        $query = "DELETE from clients_tbl WHERE id = $id";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        return $dataset;
    }
}
