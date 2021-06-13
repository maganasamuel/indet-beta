<?php

/**
@name: Deal.controller.php
@author: Jesse
@desc:
	Serves as the API of the users
    This page handles all asynchronous javascript request from the above mentioned page
    
@returnType:
	JSON
 */
// error_reporting(E_ALL ^ E_WARNING); 

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

if (file_exists("indet_dates_helper.php"))
    include_once("indet_dates_helper.php");
elseif (file_exists("libs/indet_dates_helper.php"))
    include_once("libs/indet_dates_helper.php");
elseif (file_exists("../libs/indet_dates_helper.php"))
    include_once("../libs/indet_dates_helper.php");
elseif (file_exists("../../libs/indet_dates_helper.php"))
    include_once("../../libs/indet_dates_helper.php");

if (file_exists("api/controllers/Client.controller.php"))
    include_once("api/controllers/Client.controller.php");
elseif (file_exists("libs/api/controllers/Client.controller.php"))
    include_once("libs/api/controllers/Client.controller.php");
elseif (file_exists("../libs/api/controllers/Client.controller.php"))
    include_once("../libs/api/controllers/Client.controller.php");
elseif (file_exists("../../libs/api/controllers/Client.controller.php"))
    include_once("../../libs/api/controllers/Client.controller.php");
elseif (file_exists("Client.controller.php"))
    include_once("Client.controller.php");

if (file_exists("api/controllers/Adviser.controller.php"))
    include_once("api/controllers/Adviser.controller.php");
elseif (file_exists("libs/api/controllers/Adviser.controller.php"))
    include_once("libs/api/controllers/Adviser.controller.php");
elseif (file_exists("../libs/api/controllers/Adviser.controller.php"))
    include_once("../libs/api/controllers/Adviser.controller.php");
elseif (file_exists("../../libs/api/controllers/Adviser.controller.php"))
    include_once("../../libs/api/controllers/Adviser.controller.php");
elseif (file_exists("Adviser.controller.php"))
    include_once("Adviser.controller.php");


class DealController extends Database
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
		@desc: Unissue specified client
     */
    public function unissueClient($client_id = 0)
    {
        //Fetch Deals Data
        $query = "SELECT * from submission_clients WHERE client_id = $client_id LIMIT 1";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);
        $client_deals = $dataset->fetch_assoc();
        $deals = json_decode($client_deals["deals"]);

        //Set Deals as if Pending
        foreach ($deals as $deal) {
            $deal->status = "Pending";
            //Unset Properties
            if (isset($deal->date_issued))
                unset($deal->date_issued);

            if (isset($deal->issued_api))
                unset($deal->issued_api);

            if (isset($deal->compliance_status))
                unset($deal->compliance_status);

            if (isset($deal->notes))
                unset($deal->notes);

            if (isset($deal->clawback_status))
                unset($deal->clawback_status);

            if (isset($deal->clawback_date))
                unset($deal->clawback_date);

            if (isset($deal->clawback_api))
                unset($deal->clawback_api);

            if (isset($deal->clawback_notes))
                unset($deal->clawback_notes);

            if (isset($deal->refund_status))
                unset($deal->refund_status);

            if (isset($deal->refund_notes))
                unset($deal->refund_notes);

            if (isset($deal->email))
                unset($deal->email);

            if (isset($deal->birthday))
                unset($deal->birthday);

            if (isset($deal->secondary_email))
                unset($deal->secondary_email);

            if (isset($deal->secondary_birthday))
                unset($deal->secondary_birthday);
        }

        $deals = json_encode($deals, JSON_HEX_APOS);

        //Save deals changes
        $query = "UPDATE submission_clients SET deals='$deals' WHERE client_id=$client_id";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        //Delete Issued Client Profile
        $query = "DELETE FROM issued_clients_tbl WHERE name='$client_id'";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        return $dataset;
    }

    public function AddSubmission($client_id, $deals_data)
    {
        $general = new General();
        $date_helper = new INDET_DATES_HELPER();

        $deals = array();

        extract($deals_data);

        for ($i = 1; $i <= $deals_count; $i++) {
            $deal = new stdClass();
            if (isset(${"company_" . $i})) {
                $deal->company = ${"company_" . $i};
                if ($deal->company == "Others") {
                    $deal->specific_company = ${"specific_company_" . $i};
                }
                $deal->policy_number = ${"policy_number_" . $i};
                $deal->original_api = $general->FilterNumber(${"original_api_" . $i});
                $deal->submission_date = ${"submission_date_" . $i};
                $deal->submission_date = $date_helper->DateTimeToNZEntry($deal->submission_date);
                $deal->life_insured = ${"life_insured_" . $i};
                $deal->status = ${"status_" . $i};
                if ($deal->status != "Pending") {
                    $deal->status_date = ${"status_date_" . $i};
                    $deal->status_date = $date_helper->DateTimeToNZEntry($deal->status_date);
                }
                $deals[] = $deal;
            }
        }


        $deals_op = json_encode($deals, JSON_HEX_APOS);

        //Check if client is already submitted
        $query = "SELECT * FROM submission_clients WHERE client_id = $client_id";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        //Create submission
        if ($dataset->num_rows == 0) {
            $query = "INSERT INTO submission_clients (client_id,deals) VALUES ($client_id,'$deals_op')";
            $statement = $this->prepare($query);
            $dataset = $this->execute($statement);
        }

        return $this->getSubmissionClientProfile($client_id);
    }

    public function UpdateSubmission($client_id, $deals_data)
    {
        $general = new General();
        $date_helper = new INDET_DATES_HELPER();

        $deals = array();

        extract($deals_data);

        for ($i = 1; $i <= $deals_count; $i++) {
            $deal = new stdClass();
            if (isset(${"company_" . $i})) {
                $deal->company = ${"company_" . $i};
                if ($deal->company == "Others") {
                    $deal->specific_company = ${"specific_company_" . $i};
                }
                $deal->policy_number = ${"policy_number_" . $i};
                $deal->original_api = $general->FilterNumber(${"original_api_" . $i});
                $deal->submission_date = ${"submission_date_" . $i};
                $deal->submission_date = $date_helper->DateTimeToNZEntry($deal->submission_date);
                $deal->life_insured = ${"life_insured_" . $i};
                $deal->status = ${"status_" . $i};
                if ($deal->status != "Pending") {
                    $deal->status_date = ${"status_date_" . $i};
                    $deal->status_date = $date_helper->DateTimeToNZEntry($deal->status_date);
                }
                $deals[] = $deal;
            }
        }


        $deals_op = json_encode($deals, JSON_HEX_APOS);

        $query = "UPDATE submission_clients SET deals = '$deals_op' WHERE client_id = $client_id";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        return $this->getSubmissionClientProfile($client_id);
    }

    public function GetUnpaidDeals(
        $adviser_id = 0,
        $start_date = "",
        $end_date = ""
    ) {
        $adviserController = new AdviserController();
        $adviser = $adviserController->getAdviser($adviser_id);

        //fetch deals
        $query = "SELECT *, c.name as client_name, l.name as source FROM clients_tbl c LEFT JOIN submission_clients s ON c.id = s.client_id LEFT JOIN leadgen_tbl l ON l.id = c.leadgen WHERE assigned_to='$adviser_id' AND c.status!='Cancelled'";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        $issued_deals = [];

        while ($row = $dataset->fetch_assoc()) {
            if (!isset($row["deals"]))
                continue;

            $source = $adviser["name"];

            if (!empty($row["source"]))
                $source = $row["source"];

            $deals = json_decode($row["deals"]);

            foreach ($deals as $deal) {
                $life_insured = $row["client_name"];
                if (!isset($deal->refund_status))
                    $deal->refund_status = "No";

                if (!empty($deal->life_insured))
                    $life_insured .= ", " . $deal->life_insured;


                if ($deal->status == "Issued") {
                    if ($deal->commission_status == "Not Paid") {
                        if ($deal->date_issued <= $end_date) {

                            $issued_deals[] = array(
                                "date" => $deal->date_issued,
                                "life_insured" => $life_insured,
                                "policy_number" => $deal->policy_number,
                                "company" => $deal->company,
                                "source" => $source,
                                "api" => $deal->issued_api,
                                "compliance_status" => $deal->compliance_status,
                                "notes" => $deal->notes,
                                "deal" => $deal,
                            );
                        }
                    }
                }
            }
        }

        $deals = json_encode($issued_deals);
        $issued_deals = json_decode($deals, true);
        usort($issued_deals, array('DealController', 'sortFunction'));

        return $issued_deals;
    }

    private static function sortFunction($a, $b)
    {
        return strtotime($a["date"]) - strtotime($b["date"]);
    }


    public function AddIssuedPolicy($client_id, $leadgen, $assigned_to, $deals_data)
    {
        $general = new General();
        $date_helper = new INDET_DATES_HELPER();

        $deals = array();

        extract($deals_data);
        
        $total_issued = 0;
        $first_issued_date = "0";

        for ($i = 1; $i <= $deals_count; $i++) {
            $deal = new stdClass();
            if (isset(${"company_" . $i})) {
                $deal->company = ${"company_" . $i};
                if ($deal->company == "Others") {
                    $deal->specific_company = ${"specific_company_" . $i};
                }
                $deal->policy_number = ${"policy_number_" . $i};
                $deal->original_api = $general->FilterNumber(${"original_api_" . $i});
                $deal->submission_date = ${"submission_date_" . $i};
                $deal->submission_date = $date_helper->DateTimeToNZEntry($deal->submission_date);
                $deal->life_insured = ${"life_insured_" . $i};
                $deal->status = ${"status_" . $i};
                //Extra
                $deal->audit_status = ${"audit_status_" . $i};
                $deal->record_keeping = ${"record_keeping_" . $i};
                $deal->replacement_business = ${"replacement_business_" . $i};
                $deal->email = ${"email_" . $i};
                $deal->birthday = ${"birthday_" . $i};
                $deal->birthday = $date_helper->DateTimeToNZEntry($deal->birthday);
                $deal->secondary_email = ${"secondary_email_" . $i};
                $deal->secondary_birthday = ${"secondary_birthday_" . $i};
                $deal->secondary_birthday = $date_helper->DateTimeToNZEntry($deal->secondary_birthday);

                //Set Issuance Info
                if ($deal->status == "Issued") {
                    $deal->date_issued = $date_helper->DateTimeToNZEntry(${"date_issued_" . $i});

                    if ($first_issued_date == "0") {
                        $first_issued_date = $deal->date_issued;
                    } else {
                        if ($first_issued_date > $deal->date_issued && !empty($deal->date_issued))
                            $first_issued_date = $deal->date_issued;
                    }

                    $deal->issued_api = $general->FilterNumber(${"issued_api_" . $i});
                    $total_issued +=  (float) ${"issued_api_" . $i};

                    $deal->compliance_status = ${"compliance_status_" . $i};
                    $deal->notes = ${"notes_" . $i};
                    $deal->notes = str_replace("'", "\\'", $deal->notes);
                    $deal->notes = str_replace("\r\n", "<br>", $deal->notes);
                    //$deal->notes = json_encode($deal->notes);
                    $deal->commission_status = ${"commission_status_" . $i};

                    $deal->clawback_status = "None";
                }

                if ($deal->status != "Pending" && $deal->status != "Issued" && $deal->status != "Deferred" && $deal->status != "Withdrawn") {
                    $deal->status_date = ${"status_date_" . $i};
                    $deal->status_date = $date_helper->DateTimeToNZEntry($deal->status_date);
                }

                $deals[] = $deal;
            }
        }


        $deals_op = json_encode($deals, JSON_HEX_APOS);

        $query = "UPDATE submission_clients SET deals = '$deals_op' WHERE client_id = $client_id";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        $query = "INSERT INTO issued_clients_tbl (appt_date,appt_time,address,assigned_date,type_of_lead,name,leadgen,assigned_to,issued, date_issued) VALUES ('', '', '', '', '', $client_id, $leadgen, $assigned_to, $total_issued, '$first_issued_date')";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        return $this->getIssuedClientProfile($client_id);
    }


    public function GetCompanyCumulativeKiwiSaver()
    {
        $query = "SELECT COUNT(commission) as total_deals, SUM(commission) as total_commission, SUM(gst) as total_gst, SUM(balance) as total_balance FROM kiwisaver_deals";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        return $dataset->fetch_assoc();
    }

    public function GetCompanyCumulativeKiwiSaverFromLeadGenerators($leadgen_type)
    {
        $query = "SELECT COUNT(commission) as total_deals, SUM(commission) as total_commission, SUM(gst) as total_gst, SUM(balance) as total_balance FROM kiwisaver_profiles kp LEFT JOIN clients_tbl c ON kp.client_id = c.id LEFT JOIN kiwisaver_deals kd ON kd.kiwisaver_profile_id = kp.id WHERE c.lead_by='$leadgen_type'";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        return $dataset->fetch_assoc();
    }

    public function GetCompanyCumulativeKiwiSaverFromLeadGeneratorsInRange($leadgen_type, $date_from, $date_to)
    {
        $query = "SELECT COUNT(commission) as total_deals, SUM(commission) as total_commission, SUM(gst) as total_gst, SUM(balance) as total_balance FROM kiwisaver_profiles kp LEFT JOIN clients_tbl c ON kp.client_id = c.id LEFT JOIN kiwisaver_deals kd ON kd.kiwisaver_profile_id = kp.id WHERE c.lead_by='$leadgen_type' AND  issue_date<='$date_to' AND issue_date>='$date_from'";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        return $dataset->fetch_assoc();
    }

    public function GetKiwiSaversFromLeadGeneratorInRange($leadgen_id, $date_from, $date_to)
    {
        $query = "SELECT kd.*, c.id as client_id, a.name as adviser_name FROM kiwisaver_profiles kp LEFT JOIN clients_tbl c ON kp.client_id = c.id LEFT JOIN adviser_tbl a ON c.assigned_to = a.id LEFT JOIN kiwisaver_deals kd ON kd.kiwisaver_profile_id = kp.id WHERE c.leadgen='$leadgen_id' AND  kd.issue_date<='$date_to' AND kd.issue_date>='$date_from' AND kd.count = 'Yes'";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        return $dataset;
    }

    public function GetKiwiSaverTotalsFromLeadGeneratorInRange($leadgen_id, $date_from, $date_to)
    {
        $query = "SELECT COUNT(commission) as total_deals, SUM(commission) as total_commission, GROUP_CONCAT(client_id) as client_ids, SUM(gst) as total_gst, SUM(balance) as total_balance FROM kiwisaver_profiles kp LEFT JOIN clients_tbl c ON kp.client_id = c.id LEFT JOIN kiwisaver_deals kd ON kd.kiwisaver_profile_id = kp.id WHERE c.leadgen='$leadgen_id' AND  kd.issue_date<='$date_to' AND kd.issue_date>='$date_from'";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        return $dataset->fetch_assoc();
    }

    public function GetKiwiSaverTotalsFromLeadGenerator($leadgen_id)
    {
        $query = "SELECT COUNT(commission) as total_deals, SUM(commission) as total_commission, SUM(gst) as total_gst, SUM(balance) as total_balance FROM kiwisaver_profiles kp LEFT JOIN clients_tbl c ON kp.client_id = c.id LEFT JOIN kiwisaver_deals kd ON kd.kiwisaver_profile_id = kp.id WHERE c.leadgen='$leadgen_id'";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        return $dataset->fetch_assoc();
    }

    public function GetCompanyCumulativeKiwiSaverInDateRange($date_from, $date_to)
    {
        $query = "SELECT COUNT(commission) as total_deals, SUM(commission) as total_commission, SUM(gst) as total_gst, SUM(balance) as total_balance FROM kiwisaver_deals WHERE issue_date<='$date_to' AND issue_date>='$date_from'";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        return $dataset->fetch_assoc();
    }

    public function GetClientKiwiSaverProfile($client_id)
    {
        $adviserController = new AdviserController();
        $output = [];

        $query = "SELECT k.id as kiwisaver_profile_id, a.name as adviser_name, k.timestamp, c.* FROM kiwisaver_profiles k LEFT JOIN clients_tbl c ON k.client_id = c.id LEFT JOIN adviser_tbl a ON c.assigned_to = a.id WHERE k.client_id = $client_id";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);
        while ($row = $dataset->fetch_assoc()) {
            $row["kiwisaver_deals"] = $this->GetAllKiwiSaverDealsFromProfile($row["kiwisaver_profile_id"]);
            $output = $row;
        }
        return $output;
    }


    public function GetKiwiSaversIssuedByTeamInDateRange($date_from = "", $date_to = "")
    {
        $query = "SELECT d.*, c.name as client_name, a.name as adviser_name FROM clients_tbl c INNER JOIN kiwisaver_profiles k ON k.client_id = c.id LEFT JOIN adviser_tbl a ON c.assigned_to = a.id LEFT JOIN kiwisaver_deals d ON d.kiwisaver_profile_id = k.id WHERE d.issue_date <= '$date_to' AND d.issue_date >= '$date_from' ORDER BY d.issue_date";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);
        return $dataset;
    }

    public function GetKiwiSaversIssuedByAdviserInDateRange($adviser_id = 0, $date_from = "", $date_to = "", $count = true)
    {
        $count_query = ($count) ? "AND d.count = 'Yes'" : "";
        $query = "SELECT d.name as insured_name, d.*, c.name as client_name, a.name as adviser_name FROM clients_tbl c INNER JOIN kiwisaver_profiles k ON k.client_id = c.id LEFT JOIN adviser_tbl a ON c.assigned_to = a.id LEFT JOIN kiwisaver_deals as d ON d.kiwisaver_profile_id = k.id WHERE a.id = $adviser_id AND d.issue_date <= '$date_to' AND d.issue_date >= '$date_from' $count_query ORDER BY d.issue_date";
        //echo $query;
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);
        return $dataset;
    }

    public function GetKiwiSaversIssuedInDateRange($date_from = "", $date_to = "")
    {
        $query = "SELECT d.*, c.name as client_name, a.name as adviser_name FROM clients_tbl c INNER JOIN kiwisaver_profiles k ON k.client_id = c.id LEFT JOIN adviser_tbl a ON c.assigned_to = a.id LEFT JOIN kiwisaver_deals as d ON d.kiwisaver_profile_id = k.id WHERE d.issue_date <= '$date_to' AND d.issue_date >= '$date_from' ORDER BY d.issue_date";
        //return $query;
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);
        return $dataset;
    }

    public function GetKiwiSaversFromAdviserForPayroll($adviser_id = 0, $date_from = "", $date_to = "")
    {
        $dataset = $this->GetKiwiSaversIssuedByAdviserInDateRange($adviser_id, $date_from, $date_to);
        
        $issued_deals = [];

        while ($row = $dataset->fetch_assoc()) {
            $row["date"] = $row["issue_date"];
            $issued_deals[] = $row;
        }

        $deals = json_encode($issued_deals);
        $issued_deals = json_decode($deals, true);
        usort($issued_deals, array('DealController', 'sortFunction'));

        return $issued_deals;
    }

    public function GetKiwiSaversIssuedByAdviser($adviser_id = 0)
    {
        $query = "SELECT d.*, c.name as client_name, a.name as adviser_name FROM clients_tbl c INNER JOIN kiwisaver_profiles k ON k.client_id = c.id LEFT JOIN adviser_tbl a ON c.assigned_to = a.id LEFT JOIN kiwisaver_deals as d ON d.kiwisaver_profile_id = k.id WHERE a.id = $adviser_id ORDER BY d.issue_date";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);
        return $dataset;
    }
    public function GetKiwiSaversIssuedByTeam($adviser_id = 0)
    {
        $query = "SELECT d.*, c.name as client_name, a.name as adviser_name FROM clients_tbl c INNER JOIN kiwisavers k ON k.client_id = c.id LEFT JOIN adviser_tbl a ON c.assigned_to = a.id LEFT JOIN kiwisaver_deals as d ON d.kiwisaver_profile_id = k.id ORDER BY d.issue_date";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);
        return $dataset;
    }

    public function GetAllKiwiSavers()
    {
        $adviserController = new AdviserController();

        $output = [];
        //$query = "SELECT kp.*, kd.*, c.name as client_name, a.name as adviser_name FROM clients_tbl c INNER JOIN kiwisaver_profiles kp ON kp.client_id = c.id LEFT JOIN adviser_tbl a ON c.assigned_to = a.id LEFT JOIN kiwisaver_deals kd ON kd.kiwisaver_profile_id = kp.id ORDER BY kp.timestamp";
        $query = "SELECT * FROM clients_tbl WHERE id IN (SELECT client_id FROM kiwisaver_profiles) ORDER BY TRIM(name)";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);
        while ($row = $dataset->fetch_assoc()) {
            $row["kiwisaver_profile"] = $this->GetClientKiwiSaverProfile($row["id"]);
            $row["adviser_profile"] = $adviserController->GetAdviser($row["assigned_to"]);
            $output[] = $row;
        }
        return $output;
    }

    public function GetAllKiwiSaverDeals()
    {
        $adviserController = new AdviserController();

        $output = [];
        //$query = "SELECT kp.*, kd.*, c.name as client_name, a.name as adviser_name FROM clients_tbl c INNER JOIN kiwisaver_profiles kp ON kp.client_id = c.id LEFT JOIN adviser_tbl a ON c.assigned_to = a.id LEFT JOIN kiwisaver_deals kd ON kd.kiwisaver_profile_id = kp.id ORDER BY kp.timestamp";
        $query = "SELECT kd.*, c.name as source_client, a.name as adviser_name, l.name as leadgen_name FROM kiwisaver_deals kd LEFT JOIN kiwisaver_profiles kp on kd.kiwisaver_profile_id = kp.id LEFT JOIN clients_tbl c ON kp.client_id = c.id LEFT JOIN adviser_tbl a ON c.assigned_to = a.id LEFT JOIN leadgen_tbl l ON c.leadgen = l.id ORDER BY TRIM(kd.issue_date)";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);
        while ($row = $dataset->fetch_assoc()) {
            $output[] = $row;
        }
        return $output;
    }

    public function GetKiwiSaverDeals($kiwisaver_profile_id)
    {
        $query = "SELECT kd.*, c.name as source_client, a.name as adviser_name, l.name as leadgen_name FROM kiwisaver_deals kd LEFT JOIN kiwisaver_profiles kp on kd.kiwisaver_profile_id = kp.id LEFT JOIN clients_tbl c ON kp.client_id = c.id LEFT JOIN adviser_tbl a ON c.assigned_to = a.id LEFT JOIN leadgen_tbl l ON c.leadgen = l.id WHERE kp.id = $kiwisaver_profile_id";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        return $dataset->fetch_assoc();
    }

    public function GetKiwiSaverDeal($kiwisaver_id)
    {
        $query = "SELECT kd.*, c.name as source_client, a.name as adviser_name, l.name as leadgen_name FROM kiwisaver_deals kd LEFT JOIN kiwisaver_profiles kp on kd.kiwisaver_profile_id = kp.id LEFT JOIN clients_tbl c ON kp.client_id = c.id LEFT JOIN adviser_tbl a ON c.assigned_to = a.id LEFT JOIN leadgen_tbl l ON c.leadgen = l.id WHERE kd.id = $kiwisaver_id";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        return $dataset->fetch_assoc();
    }

    public function UpdateKiwiSaverDeal($id, $deal_data)
    {
        extract($deal_data);
        
        if (strpos($issue_date, '/') !== false) {
            $date_helper = new INDET_DATES_HELPER();
            $issue_date = $date_helper->DateTimeToNZEntry($issue_date);
        }

        $name = $this->clean($name);
        $commission = $this->clean($commission);
        $gst = $this->clean($gst);
        $balance = $this->clean($balance);

        $query = "UPDATE kiwisaver_deals SET name = '$name', commission = '$commission', gst = '$gst', balance = '$balance', issue_date = '$issue_date', count = '$count' WHERE id = $id";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        return $this->GetKiwiSaverDeal($id);
    }

    public function DeleteKiwiSaverDeal($kiwisaver_deal_id)
    {
        $query = "DELETE FROM kiwisaver_deals WHERE id = $kiwisaver_deal_id";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        return $dataset;
    }

    public function GetKiwiSaverProfile($kiwisaver_id)
    {
        $output = null;
        $query = "SELECT c.name as client_name, a.name as adviser_name, k.* FROM kiwisaver_profiles k LEFT JOIN clients_tbl c ON k.client_id = c.id LEFT JOIN adviser_tbl a ON c.assigned_to = a.id WHERE k.id = $kiwisaver_id ORDER BY timestamp DESC";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);
        while ($row = $dataset->fetch_assoc()) {
            $row["kiwisaver_deals"] = $this->GetAllKiwiSaverDealsFromProfile($kiwisaver_id);
            $output = $row;
        }
        return $output;
    }

    public function GetAllKiwiSaverDealsFromProfile($kiwisaver_profile_id)
    {
        $output = [];
        $query = "SELECT * FROM kiwisaver_deals WHERE kiwisaver_profile_id = $kiwisaver_profile_id ORDER BY issue_date DESC";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);
        while ($row = $dataset->fetch_assoc()) {
            $output[] = $row;
        }
        return $output;
    }

    public function AddKiwiSaverProfile($client_id, $data)
    {
        $query = "INSERT INTO kiwisaver_profiles (client_id) VALUES ($client_id)";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        $insert_id = $this->mysqli->insert_id;

        $oldest_kiwisaver_deal = "99999999";
        for ($i = 1; $i <= $data["deals_count"]; $i++) {
            if (!isset($data["name_" . $i]) || !isset($data["commission_" . $i]) || !isset($data["gst_" . $i]) || !isset($data["balance_" . $i]) || !isset($data["issue_date_" . $i])) {
                continue;
            }
            $date_helper = new INDET_DATES_HELPER();
            $issue_date = $date_helper->DateTimeToNZEntry($data["issue_date_" . $i]);

            if((int)$oldest_kiwisaver_deal > (int)$issue_date){
                $oldest_kiwisaver_deal = $issue_date;
            }

            $this->AddKiwiSaverDeals($insert_id, $data["name_" . $i], $data["commission_" . $i], $data["gst_" . $i], $data["balance_" . $i], $issue_date, $data["count_" . $i]);
        }
        $oldest_kiwisaver_deal = date("Y-m-d H:i:s", strtotime($oldest_kiwisaver_deal));
        
        $query = "UPDATE kiwisaver_profiles SET timestamp = '$oldest_kiwisaver_deal' WHERE id = $insert_id";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        return $this->GetKiwiSaverProfile($insert_id);
    }

    public function AddKiwiSaverDeals($kiwisaver_profile_id, $name, $commission, $gst, $balance, $issue_date, $count)
    {
        $name = $this->clean($name);
        $commission = $this->clean($commission);
        $gst = $this->clean($gst);
        $balance = $this->clean($balance);

        $query = "INSERT INTO kiwisaver_deals (kiwisaver_profile_id, name, commission, gst, balance, issue_date, count) VALUES ($kiwisaver_profile_id, '$name', '$commission', '$gst', '$balance', '$issue_date', '$count')";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        return $this->mysqli->insert_id;
    }


    public function UpdateKiwiSaverProfile($kiwisaver_profile_id, $data)
    {
        $surviving_deals = [];

        $oldest_kiwisaver_deal = "99999999";
        for ($i = 1; $i <= $data["deals_count"]; $i++) {
            if (!isset($data["name_" . $i]) || !isset($data["commission_" . $i]) || !isset($data["gst_" . $i]) || !isset($data["balance_" . $i]) || !isset($data["issue_date_" . $i])) {
                continue;
            }

            $date_helper = new INDET_DATES_HELPER();
            $issue_date = $date_helper->DateTimeToNZEntry($data["issue_date_" . $i]);

            if((int)$oldest_kiwisaver_deal > (int)$issue_date){
                $oldest_kiwisaver_deal = $issue_date;
            }
            $put_id = 0;

            if(empty($data["kiwisaver_deal_id_" . $i])){
                $put_id = $this->AddKiwiSaverDeals($kiwisaver_profile_id, $data["name_" . $i], $data["commission_" . $i], $data["gst_" . $i], $data["balance_" . $i], $issue_date, $data["count_" . $i]);
            }
            else{
                $kiwisaver_deal_data = [];
                $kiwisaver_deal_data["name"] = $data["name_" . $i];
                $kiwisaver_deal_data["commission"] = $data["commission_" . $i];
                $kiwisaver_deal_data["gst"] = $data["gst_" . $i];
                $kiwisaver_deal_data["balance"] = $data["balance_" . $i];
                $kiwisaver_deal_data["issue_date"] = $issue_date;
                $kiwisaver_deal_data["count"] = $data["count_" . $i];
    
                $this->UpdateKiwiSaverDeal($data["kiwisaver_deal_id_" . $i], $kiwisaver_deal_data);

                $put_id = $data["kiwisaver_deal_id_" . $i];
            }

            $surviving_deals[] = $put_id;
        }

        $surviving_deals_array = implode(",", $surviving_deals);

        $query = "DELETE FROM kiwisaver_deals WHERE kiwisaver_profile_id = $kiwisaver_profile_id AND id NOT IN ($surviving_deals_array)";

        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        $oldest_kiwisaver_deal = date("Y-m-d H:i:s", strtotime($oldest_kiwisaver_deal));
        $query = "UPDATE kiwisaver_profiles SET timestamp = '$oldest_kiwisaver_deal' WHERE id = $kiwisaver_profile_id";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);
        
        return $this->GetKiwiSaverProfile($kiwisaver_profile_id);
    }

    public function DeleteKiwiSaverProfile($kiwisaver_profile_id)
    {
        $query = "DELETE FROM kiwisaver_deals WHERE kiwisaver_profile_id = $kiwisaver_profile_id";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        $query = "DELETE FROM kiwisaver_profiles WHERE id = $kiwisaver_profile_id";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        return $dataset;
    }

    public function UpdateIssuedPolicy($client_id, $deals_data)
    {
        $general = new General();
        $date_helper = new INDET_DATES_HELPER();

        $deals = array();

        extract($deals_data);

        $total_issued = 0;
        $first_issued_date = "0";

        for ($i = 1; $i <= $deals_count; $i++) {
            $deal = new stdClass();
            if (isset(${"company_" . $i})) {
                $deal->company = ${"company_" . $i};
                if ($deal->company == "Others") {
                    $deal->specific_company = ${"specific_company_" . $i};
                }
                $deal->policy_number = ${"policy_number_" . $i};
                $deal->original_api = $general->FilterNumber(${"original_api_" . $i});
                $deal->submission_date = ${"submission_date_" . $i};
                $deal->submission_date = $date_helper->DateTimeToNZEntry($deal->submission_date);
                $deal->life_insured = ${"life_insured_" . $i};
                $deal->status = ${"status_" . $i};
                //Extra
                $deal->audit_status = ${"audit_status_" . $i};
                $deal->record_keeping = ${"record_keeping_" . $i};
                $deal->replacement_business = ${"replacement_business_" . $i};
                $deal->email = ${"email_" . $i};
                $deal->birthday = ${"birthday_" . $i};
                $deal->birthday = $date_helper->DateTimeToNZEntry($deal->birthday);
                $deal->secondary_email = ${"secondary_email_" . $i};
                $deal->secondary_birthday = ${"secondary_birthday_" . $i};
                $deal->secondary_birthday = $date_helper->DateTimeToNZEntry($deal->secondary_birthday);

                //Set Issuance Info
                if ($deal->status == "Issued") {
                    $deal->date_issued = $date_helper->DateTimeToNZEntry(${"date_issued_" . $i});

                    if ($first_issued_date == "0") {
                        $first_issued_date = $deal->date_issued;
                    } else {
                        if ($first_issued_date > $deal->date_issued && !empty($deal->date_issued))
                            $first_issued_date = $deal->date_issued;
                    }

                    $deal->issued_api = $general->FilterNumber(${"issued_api_" . $i});
                    $total_issued +=  (float) ${"issued_api_" . $i};

                    $deal->compliance_status = ${"compliance_status_" . $i};
                    $deal->notes = ${"notes_" . $i};
                    $deal->notes = str_replace("'", "\\'", $deal->notes);
                    $deal->notes = str_replace("\r\n", "<br>", $deal->notes);
                    //$deal->notes = json_encode($deal->notes);
                    $deal->commission_status = ${"commission_status_" . $i};
                    $deal->clawback_status = ${"clawback_status_" . $i};

                    if (isset($deal->clawback_status) || !empty($deal->clawback_status)) {
                        //Set Clawback Info
                        if ($deal->clawback_status != "None") {
                            $deal->clawback_date = $date_helper->DateTimeToNZEntry(${"clawback_date_" . $i});
                            $deal->clawback_api = $general->FilterNumber(${"clawback_api_" . $i});
                            $deal->clawback_notes = ${"clawback_notes_" . $i};
                            $deal->clawback_notes = str_replace("'", "\\'", $deal->clawback_notes);
                            $deal->clawback_notes = str_replace("\r\n", "<br>", $deal->clawback_notes);
                            $deal->refund_status = ${"refund_status_" . $i};
                            $deal->refund_notes = ${"refund_notes_" . $i};
                            $deal->refund_notes = str_replace("'", "\\'", $deal->refund_notes);
                            $deal->refund_notes = str_replace("\r\n", "<br>", $deal->refund_notes);
                        }
                    }
                }

                if ($deal->status != "Pending" && $deal->status != "Issued" && $deal->status != "Deferred" && $deal->status != "Withdrawn") {
                    $deal->status_date = ${"status_date_" . $i};
                    $deal->status_date = $date_helper->DateTimeToNZEntry($deal->status_date);
                }

                $deals[] = $deal;
            }
        }

        $deals_op = json_encode($deals, JSON_HEX_APOS);

        $query = "UPDATE submission_clients SET deals = '$deals_op' WHERE client_id = $client_id";
        //echo $query;
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        $clientController = new ClientController();
        $client = $clientController->getClient($client_id);
        $client = $client->fetch_assoc();
        $assigned_to = $client["assigned_to"];
        $leadgen = $client["leadgen"];

        $query = "UPDATE issued_clients_tbl SET issued = $total_issued, date_issued = '$first_issued_date', assigned_to = $assigned_to, leadgen = $leadgen WHERE name = $client_id";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        return $this->getIssuedClientProfile($client_id);
    }

    /**
		@desc: Get Specified Deal Tracker Report 
     */
    public function getDealTrackerReport($report_id)
    {
        $query = "SELECT * FROM deal_tracker_reports WHERE id = $report_id";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);
        $dataset = $dataset->fetch_assoc();

        return $dataset;
    }

    /**
		@desc: Get specified submission client profile 
     */
    public function getSubmissionClientProfile($client_id)
    {
        $query = "SELECT s.id as id,s.deals as deals, s.timestamp, c.id as client_id, c.name as client_name, l.name as leadgen_name, a.name as adviser_name, s.deals FROM submission_clients s LEFT JOIN clients_tbl c ON s.client_id = c.id LEFT JOIN leadgen_tbl l ON c.leadgen = l.id LEFT JOIN adviser_tbl a ON c.assigned_to = a.id WHERE c.id = $client_id";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);
        $dataset = $dataset->fetch_assoc();

        return $dataset;
    }

    /**
		@desc: Get specified Issued client profile 
     */
    public function getIssuedClientProfile($client_id)
    {
        $query = "SELECT c.id as client_id, c.lead_by, i.id,c.name,a.name as x,l.name as y,i.appt_date,i.appt_time,i.address,i.leadgen,i.assigned_to,i.assigned_date,i.type_of_lead,i.issued,i.date_issued,i.notes,s.deals as deals_data FROM issued_clients_tbl i LEFT JOIN submission_clients s ON s.client_id = i.name LEFT JOIN adviser_tbl a ON i.assigned_to = a.id LEFT JOIN leadgen_tbl l ON i.leadgen = l.id LEFT JOIN clients_tbl c ON i.name = c.id WHERE c.id = $client_id";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);
        $dataset = $dataset->fetch_assoc();

        return $dataset;
    }

    /**
		@desc: Delete specified submission client profile 
     */
    public function deleteSubmissionClientProfile($client_id)
    {
        $query = "DELETE FROM submission_clients WHERE client_id = $client_id";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        return $dataset;
    }


    /**
		@desc: Get specified Issued client profile 
     */
    public function getLeadGeneratorIssuedClients($leadgen_id)
    {
        $query = "SELECT c.id as client_id, c.lead_by, i.id,c.name,a.name as x,l.name as y,i.appt_date,i.appt_time,i.address,i.leadgen,i.assigned_to,i.assigned_date,i.type_of_lead,i.issued,i.date_issued,i.notes,s.deals as deals_data FROM issued_clients_tbl i LEFT JOIN submission_clients s ON s.client_id = i.name LEFT JOIN adviser_tbl a ON i.assigned_to = a.id LEFT JOIN leadgen_tbl l ON i.leadgen = l.id LEFT JOIN clients_tbl c ON i.name = c.id WHERE c.leadgen = $leadgen_id";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        return $dataset;
    }

    /**
		@desc: Get specified Issued client profile 
     */
    public function getLeadGeneratorIssuedClientsInPeriod($leadgen_id, $date_from, $date_to)
    {
        $query = "SELECT c.id as client_id, c.lead_by, i.id,c.name,a.name as x,l.name as y,i.appt_date,i.appt_time,i.address,i.leadgen,i.assigned_to,i.assigned_date,i.type_of_lead,i.issued,i.date_issued,i.notes,s.deals as deals_data FROM issued_clients_tbl i LEFT JOIN submission_clients s ON s.client_id = i.name LEFT JOIN adviser_tbl a ON i.assigned_to = a.id LEFT JOIN leadgen_tbl l ON i.leadgen = l.id LEFT JOIN clients_tbl c ON i.name = c.id WHERE c.leadgen = $leadgen_id AND c.date_issued >= '$date_from' AND c.date_issued <= '$date_to'";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        return $dataset;
    }

    /**
		@desc: Get specified Issued client profile 
     */
    public function getLeadGeneratorIssuedSubmittedAndCancelledDealsInPeriod($leadgen_id, $date_from, $date_to)
    {
        $data = new stdClass();
        $data->issued_deals = [];
        $data->cancellations = [];
        $data->submissions = [];

        $query = "SELECT c.id as client_id, c.lead_by, c.name as client_name, a.name as adviser_name, l.name as leadgen_name, s.deals as deals_data FROM clients_tbl c LEFT JOIN submission_clients s ON s.client_id = c.id LEFT JOIN adviser_tbl a ON c.assigned_to = a.id LEFT JOIN leadgen_tbl l ON c.leadgen = l.id WHERE c.leadgen = $leadgen_id";

        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);
        $data->issued_deals = array();
        $data->cancelled_deals = array();
        while ($row = $dataset->fetch_assoc()) {
            if(empty($row["deals_data"]))
                continue;
            $deals = json_decode($row["deals_data"]);
            foreach ($deals as $deal) {
                
                if (isset($deal->status)) {

                    if(!empty($deal->life_insured))
                        $deal->name = $row["client_name"] . ", " . $deal->life_insured;
                    else
                        $deal->name = $row["client_name"];

                    $deal->adviser_name = $row["adviser_name"];

                    if($deal->status=="Pending"){
                        if($deal->submission_date>=$date_from&&$deal->submission_date<=$date_to)
                            $data->submissions[] = $deal;
                    }
                    elseif ($deal->status == "Issued") {
                        if($deal->date_issued>=$date_from&&$deal->date_issued<=$date_to){
                            $data->issued_deals[] = $deal;
                        }
                            
                        if (isset($deal->clawback_status)) {
                            if ($deal->clawback_status == "Cancelled") {
                                if($deal->clawback_date>=$date_from&&$deal->clawback_date<=$date_to)
                                $data->cancelled_deals[] = $deal;
                            }
                        }
                    }
                }
            }
        }

        return $data;
    }

    /**
		@desc: Get specified Issued client profile 
     */
    public function getLeadGeneratorIssuedAndCancelledDealsInPeriod($leadgen_id, $date_from, $date_to)
    {
        $data = new stdClass();
        $data->issued_api = 0;
        $data->cancellation_api = 0;

        $query = "SELECT c.id as client_id, c.lead_by, i.id,c.name,a.name as x,l.name as y,i.appt_date,i.appt_time,i.address,i.leadgen,i.assigned_to,i.assigned_date,i.type_of_lead,i.issued,i.date_issued,i.notes,s.deals as deals_data FROM issued_clients_tbl i LEFT JOIN submission_clients s ON s.client_id = i.name LEFT JOIN adviser_tbl a ON i.assigned_to = a.id LEFT JOIN leadgen_tbl l ON i.leadgen = l.id LEFT JOIN clients_tbl c ON i.name = c.id WHERE c.leadgen = $leadgen_id AND i.date_issued >= '$date_from' AND i.date_issued <= '$date_to'";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);
        $data->issued_deals = array();
        $data->cancelled_deals = array();
        while ($row = $dataset->fetch_assoc()) {
            $deals = json_decode($row["deals_data"]);
            foreach ($deals as $deal) {
                if (isset($deal->status)) {
                    if ($deal->status == "Issued") {
                        $data->issued_deals[] = $deal;
                        $data->issued_api += $deal->issued_api;

                        if (isset($deal->clawback_status)) {
                            if ($deal->clawback_status == "Cancelled") {
                                $data->cancelled_deals[] = $deal;
                                $data->cancellation_api += $deal->issued_api;
                            }
                        }
                    }
                }
            }
        }

        return $data;
    }
}
