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


if (file_exists("indet_dates_helper.php"))
    include_once("indet_dates_helper.php");
elseif (file_exists("libs/indet_dates_helper.php"))
    include_once("libs/indet_dates_helper.php");
elseif (file_exists("../libs/indet_dates_helper.php"))
    include_once("../libs/indet_dates_helper.php");
elseif (file_exists("../../libs/indet_dates_helper.php"))
    include_once("../../libs/indet_dates_helper.php");

class AdviserController extends Database
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
    public function getAllAdvisers()
    {
        $query = "Select * from adviser_tbl ORDER BY name";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        return $dataset;
    }

    /**
		@desc: Get all users
     */
    public function getAllAdvisersOrderedByTerminationDate()
    {
        $query = "Select * from adviser_tbl ORDER BY termination_date, name";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        return $dataset;
    }
    /**
		@desc: Get all users
     */
    public function getActiveAdvisers()
    {
        $query = "Select a.*, t.name as team from adviser_tbl a LEFT JOIN teams t ON a.team_id = t.id  WHERE a.termination_date = '' ORDER BY a.name";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        return $dataset;
    }

    /**
		@desc: Get all users
     */
    public function getExAdvisers()
    {
        $date = date("Ymd");
        $query = "Select * from adviser_tbl WHERE termination_date != '' AND termination_date <= '$date' ORDER BY name";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        return $dataset;
    }

    /**
		@desc: Get all questions from the specified Question Set
     */
    public function getAdviser(
        $id = 0    //
    ) {
        $query = "Select * from adviser_tbl WHERE id = $id LIMIT 1";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);
        $dataset = $dataset->fetch_assoc();
        return $dataset;
    }

    /**
		@desc: Get all questions from the specified Question Set
     */
    public function getAdviserFromPayroll(
        $name = ""    //
    ) {
        $query = "Select * from adviser_tbl WHERE payroll_name = '$name' LIMIT 1";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);
        $dataset = $dataset->fetch_assoc();
        return $dataset;
    }

    public function getAdviserPayables(
        $adviser_id = 0
    ) {
        //Fetch payables
        $query = "SELECT * FROM transactions WHERE adviser_id = $adviser_id ORDER BY date DESC";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        $total_leads_payable = 0;
        $total_issued_payable = 0;
        $total_outstanding_payable_amount_header = 0;
        while ($row = $dataset->fetch_assoc()) {
            extract($row);
            $status = $this->CheckTransactionStatus($status);
            switch ($status) {
                case "Manual Billed Assigned Leads":
                case "Billed Assigned Leads":
                    $total_leads_payable += $number_of_leads;
                    break;
                case "Manual Billed Issued Leads":
                case "Billed Issued Leads":
                    $total_issued_payable += $number_of_leads;
                    break;
                case "Paid Issued Leads":
                case "Waived Issued Leads":
                case "Cancelled Issued Leads":
                    $total_issued_payable -= $number_of_leads;
                    break;
                default:
                    $total_leads_payable -= $number_of_leads;
                    break;
            }
            //echo $status . " from " . $amount . "<hr>";
            $total_outstanding_payable_amount_header += $amount;
        }

        $dataset = new stdClass();
        $dataset->total_leads_payable = $total_leads_payable;
        $dataset->total_issued_payable = $total_issued_payable;
        $dataset->total_amount_payable = $total_outstanding_payable_amount_header;

        return $dataset;
    }

    public function getInvoiceNumbersFromTransactions(
        $adviser_id = 0
    ) {
        $invoices = array();
        $query = "SELECT DISTINCT LEFT(status, 13) as invoice_number FROM transactions WHERE adviser_id = '$adviser_id' AND LEFT(status,2) = 'EI'";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        while ($row = $dataset->fetch_assoc()) {
            $invoices[] = $row["invoice_number"];
        }

        return $invoices;
    }

    public function getTransactions(
        $adviser_id = 0
    ) {
        $query = "SELECT * FROM transactions WHERE adviser_id = '$adviser_id' ORDER BY date DESC";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);
        return $dataset;
    }

    public function getTransactionsInDateRange(
        $adviser_id = 0,
        $date_from,
        $date_to
    ) {
        $query = "SELECT * FROM transactions WHERE adviser_id = '$adviser_id' AND date <= '$date_to' AND date >='$date_from' ORDER BY date DESC";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);
        return $dataset;
    }

    public function getAdviserValidInvoices(
        $adviser_id = 0,
        $valid_invoices     //This is an array of invoice numbers
    ) {
        //Fetch payables
        $query = "SELECT *, a.leads as payperlead, i.leads as client_leads FROM invoices i LEFT JOIN adviser_tbl a ON i.adviser_id=a.id WHERE i.adviser_id='$adviser_id' AND i.number IN ('" . $valid_invoices . "') ORDER BY i.date_created ASC";

        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        return $dataset;
    }

    public function getAdviserValidInvoicesInRange(
        $adviser_id = 0,
        $date_from,
        $date_to,
        $valid_invoices     //This is an array of invoice numbers
    ) {
        //Fetch payables
        $query = "SELECT *, a.leads as payperlead, i.leads as client_leads FROM invoices i LEFT JOIN adviser_tbl a ON i.adviser_id=a.id WHERE i.adviser_id='$adviser_id' AND i.date_created <= '$date_to' AND i.date_created>='$date_from' AND i.number IN ('" . $valid_invoices . "')  ORDER BY i.date_created DESC";

        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        return $dataset;
    }

    public function getAdviserPayableLeads(
        $adviser_id = 0
    ) {
        $adviserController = new AdviserController();
        $clientController = new ClientController();

        $date_from = $this->getAdviserOldestClient($adviser_id)["assigned_date"];
        $until = date("Ymd");

        $summary_data = new stdClass();

        $summary_data->date_from = $date_from;
        $summary_data->date_to = $until;
        $summary_data->invoices = array();
        $summary_data->invoice_numbers = array();
        $summary_data->invoices_in_range = array();
        $summary_data->payable_invoices = array();
        $summary_data->invoice_transaction_histories = array();
        $summary_data->data = new stdClass();
        $summary_data->data->leads_payable = 0;
        $summary_data->data->issued_payable = 0;
        $summary_data->valid_invoices = array();
        $summary_data->total_paid_assigned_leads = 0;
        $summary_data->total_paid_issued_leads = 0;
        $summary_data->total_billed_assigned_leads = 0;
        $summary_data->total_billed_issued_leads = 0;
        $summary_data->leads = array();
        $summary_data->issued = array();
        $summary_data->invoice_numbers_list = array();

        //Amendents
        $summary_data->clients_amended = array();
        $summary_data->issued_clients_amended = array();

        //amendments variables
        $summary_data->issued_clients = array();
        $summary_data->amendments = 0;
        $summary_data->issued_amendments = 0;
        $summary_data->amendments_amount = 0;
        $summary_data->issued_amendments_amount = 0;

        //exempted variables
        $summary_data->exempted_clients = array();
        $summary_data->exempted_issued_clients = array();

        //Search all issued clients and store them in a string
        $dataset = $clientController->getIssuedClientsAssignedTo($adviser_id);
        while ($row = $dataset->fetch_assoc()) {
            $summary_data->issued_clients[] = $row["name"];
        }
        $issued_clients_list = implode(",", $summary_data->issued_clients);

        //Fetch Invoices Data
        $invoices_id_list = "";
        $invoices_array = array();
        $total_due = 0;


        $summary_data->adviser = $adviserController->getAdviser($adviser_id);

        $name = $summary_data->adviser["name"];
        $fsp_num = $summary_data->adviser["fsp_num"];
        $adviser_address = $summary_data->adviser["address"];
        $adviser_id = $summary_data->adviser["id"];

        //fetch all valid invoices
        $summary_data->valid_invoices = $adviserController->getInvoiceNumbersFromTransactions($adviser_id);
        $valid_invoices = implode("','", $summary_data->valid_invoices);

        //Get all invoice transaction history and get all paid data
        $dataset = $adviserController->getTransactionsInDateRange($adviser_id, $date_from, $until);

        //Load billing and payment info
        while ($row = $dataset->fetch_assoc()) {
            $summary_data->invoice_transaction_histories[] = $row;

            if (strpos($row["status"], 'Billed') !== false) {
                if (strpos($row["status"], 'Issued') !== false) {
                    //Manual Issued Leads
                    if (strpos($row["status"], 'Manual') !== false) {
                        $summary_data->total_paid_issued_leads -= $row["number_of_leads"];
                    } else {
                        $summary_data->total_billed_issued_leads += $row["number_of_leads"];
                    }
                } else {
                    //Manual Assigned Leads
                    if (strpos($row["status"], 'Manual') !== false) {
                        $summary_data->total_paid_assigned_leads -= $row["number_of_leads"];
                    } else {
                        $summary_data->total_billed_assigned_leads += $row["number_of_leads"];
                    }
                }
            } else {
                switch ($row["status"]) {
                    case "Paid Assigned Leads":
                        $summary_data->total_paid_assigned_leads += $row["number_of_leads"];
                        break;
                    case "Paid Issued Leads":
                        $summary_data->total_paid_issued_leads += $row["number_of_leads"];
                        break;
                    case "Cancelled Leads":
                    case "Waived Leads":
                        if ($row["clients_list"] != "") {
                            $clients_list_array = explode(",", $row["clients_list"]);
                            $summary_data->exempted_clients = array_merge($summary_data->exempted_clients, $clients_list_array);
                        }
                        $summary_data->total_billed_assigned_leads -= $row["number_of_leads"];
                        $clients_in_transaction = explode(",", $row["clients_list"]);
                        $summary_data->clients_amended = array_merge($summary_data->clients_amended, $clients_in_transaction);
                        break;
                    case "Cancelled Issued Leads":
                    case "Waived Issued Leads":
                        if ($row["clients_list"] != "") {
                            $clients_list_array = explode(",", $row["clients_list"]);
                            $summary_data->exempted_issued_clients = array_merge($summary_data->exempted_issued_clients, $clients_list_array);
                        }
                        $summary_data->total_billed_issued_leads -= $row["number_of_leads"];
                        $clients_in_transaction = explode(",", $row["clients_list"]);
                        $summary_data->issued_clients_amended = array_merge($summary_data->clients_amended, $clients_in_transaction);
                        break;
                }
            }
        }

        $dataset = $clientController->getInvoiceExemptedClientsFromAdviser($adviser_id);
        $exempted_clients = [];

        while ($row = $dataset->fetch_assoc()) {
            $exempted_clients[] = $row["id"];
        }

        //Attach Clients Exempted
        $summary_data->clients_amended = array_merge($summary_data->clients_amended, $exempted_clients);

        //Get Ammendments
        foreach ($summary_data->invoice_transaction_histories as $transaction) {
            if ($transaction["status"] == "Waived Leads" || $transaction["status"] == "Cancelled Leads") {
                $summary_data->amendments += $transaction["number_of_leads"];
                $summary_data->amendments_amount += $transaction["amount"];
            } elseif ($transaction["status"] == "Waived Issued Leads" || $transaction["status"] == "Cancelled Issued Leads") {
                $summary_data->issued_amendments += $transaction["number_of_leads"];
                $summary_data->issued_amendments_amount += $transaction["amount"];
            }
        }

        $paid_assigned_wallet = $summary_data->total_paid_assigned_leads + $summary_data->amendments;
        $paid_issued_wallet = $summary_data->total_paid_issued_leads + $summary_data->issued_amendments;

        $dataset = $adviserController->getAdviserValidInvoices($adviser_id, $valid_invoices);
        while ($row = $dataset->fetch_assoc()) {
            $rowleads = json_decode($row['client_leads']);
            $rowissued = json_decode($row['issued']);

            $inv = new stdClass();
            $inv->invoice_no = $row['number'];
            $inv->date_created = $row['date_created'];
            $inv->amount = $row['amount'];
            $inv->status = $row['status'];
            $inv->leads = count($rowleads);
            $inv->issued = count($rowissued);

            $inv->remaining_amount = $row['amount'];

            //check if wallet is empty and if not reduce assigned amount
            if ($paid_assigned_wallet > 0 && $inv->leads > 0) {
                $payment = 0;
                //Check if wallet is lesser than remaining leads
                if ($paid_assigned_wallet < $inv->leads) {
                    $payment = $summary_data->adviser["leads"] * $paid_assigned_wallet;
                    $paid_assigned_wallet = 0;
                }
                //If wallet is greater than or equal to remaining leads
                else {
                    $payment = $summary_data->adviser["leads"] * $inv->leads;
                    $paid_assigned_wallet -= $inv->leads;
                }
                $payment += ($payment * .15);
                $inv->remaining_amount -= $payment;
            }

            //check if wallet is empty and if not reduce billed amount
            if ($paid_issued_wallet > 0 && $inv->issued > 0) {
                $payment = 0;

                //Check if wallet is lesser than remaining leads
                if ($paid_issued_wallet < $inv->issued) {
                    $payment = $summary_data->adviser["bonus"] * $paid_issued_wallet;
                    $paid_issued_wallet = 0;
                }
                //If wallet is greater than or equal to remaining leads
                else {
                    $payment = $summary_data->adviser["bonus"] * $inv->issued;
                    $paid_issued_wallet -= $inv->issued;
                }
                $payment += ($payment * .15);
                $inv->remaining_amount -= $payment;
            }


            $total_due += $row['amount'];

            if ($inv->remaining_amount > 0) {
                $summary_data->invoices[] = $inv;
                $summary_data->invoice_numbers_list[] = $inv->invoice_no;
            }
        }

        //Get numbers
        $summary_data->invoice_numbers_list = implode(", ", $summary_data->invoice_numbers_list);

        //fetch all valid invoice numbers within date range
        $dataset = $adviserController->getAdviserValidInvoicesInRange($adviser_id, $date_from, $until, $valid_invoices);
        while ($row = $dataset->fetch_assoc()) {
            array_push($invoices_array, $row['number']);

            $rowleads = json_decode($row['client_leads']);
            $rowissued = json_decode($row['issued']);
            $summary_data->invoice_numbers[] = $row['number'];

            //Remove clients in ammendments
            foreach ($rowleads as $lead) {
                if (!in_array($lead, $summary_data->clients_amended)) {
                    $summary_data->leads[] = $lead;
                }
            }

            foreach ($rowissued as $issued) {
                if (!in_array($issued, $summary_data->issued_clients_amended)) {
                    $summary_data->issued[] = $issued;
                }
            }
        }

        //Get all valid invoices and place it in the invoices in range pool 
        foreach ($summary_data->invoices as $inv) {
            if (in_array($inv->invoice_no, $summary_data->invoice_numbers)) {
                $summary_data->invoices_in_range[] = $inv;
            }
        }

        $invoices_id_list = implode(", ", $summary_data->invoice_numbers);


        $summary_data->payable_assigned_leads = $summary_data->total_billed_assigned_leads - $summary_data->total_paid_assigned_leads;
        $summary_data->payable_issued_leads = $summary_data->total_billed_issued_leads - $summary_data->total_paid_issued_leads;

        $summary_data->leads = array_slice($summary_data->leads, 0, $summary_data->payable_assigned_leads);
        $summary_data->issued = array_slice($summary_data->issued, 0, $summary_data->payable_issued_leads);
        return $summary_data;
    }


    function CheckTransactionStatus($status)
    {
        $issued = stripos($status, 'Billed Issued Leads') !== false;
        $assigned = stripos($status, 'Billed Assigned Leads') !== false;
        $op = $status;
        if ($issued) {
            $op = "Billed Issued Leads";
        } elseif ($assigned) {
            $op = "Billed Assigned Leads";
        }

        return $op;
    }

    /**
		@desc: Get all questions from the specified Question Set
     */
    public function getAdviserByEmail(
        $email = ""    //
    ) {
        $query = "Select * from adviser_tbl WHERE email = '$email' AND email != '' LIMIT 1";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);
        return $dataset;
    }


    /**
		@desc: Get all questions from the specified Question Set
     */
    public function getAdviserWithTeamData(
        $id = 0    //
    ) {
        $query = "SELECT * , a.name as name, t.name as team_name FROM adviser_tbl a LEFT JOIN teams t ON a.team_id = t.id where a.id = $id LIMIT 1";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);
        $dataset = $dataset->fetch_assoc();
        return $dataset;
    }


    /**
		@desc: Get oldest client assigned to adviser
     */
    public function getAdviserOldestClient(
        $adviser_id = 0    //
    ) {
        //Get first client
        $date_helper = new INDET_DATES_HELPER();
        $query = "Select * from clients_tbl where assigned_to = '$adviser_id' ORDER BY assigned_date ASC LIMIT 1";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);
        $data = $dataset->fetch_assoc();
        $data["translated_assigned_date"] = $date_helper->NZEntryToDateTime($data["assigned_date"]);
        return $data;
    }

    /**
		@desc: Get all questions from the specified Question Set
     */
    public function getAdviserDealsData(
        $id = 0    //
    ) {
        $query = "SELECT *, a.name as name, t.name as team_name, c.name as client_name, c.date_submitted as date_generated, s.timestamp as date_submitted, i.date_issued as date_issued, a.id as adviser_id, a.fsp_num as fsp_num, c.id as client_id, i.id as issued_client_id, s.id as submission_client_id FROM clients_tbl c LEFT JOIN submission_clients s ON c.id = s.client_id LEFT JOIN issued_clients_tbl i ON i.name = c.id LEFT JOIN adviser_tbl a ON a.id=c.assigned_to LEFT JOIN teams t ON a.team_id = t.id WHERE a.id = $id AND c.status!='Cancelled'";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        return $dataset;
    }

    /**
		@desc: Create new user with name and email
     */
    public function createAdviser(
        $team_id = "",
        $name = "",
        $company_name = "",
        $payroll_name = "",
        $fsp_num = "",
        $address = "",
        $email = "",
        $birthday = "",
        $leads = 35,
        $bonus = 50,
        $image = "",
        $date_hired = "",
        $termination_date = ""
    ) {

        $date_helper = new INDET_DATES_HELPER();

        $name = $this->clean($name);
        $company_name = $this->clean($company_name);
        $payroll_name = $this->clean($payroll_name);
        $address = $this->clean($address);

        if (strpos($birthday, '/') !== false) {
            $birthday = $date_helper->DateTimeToNZEntry($birthday);
        }
        if (strpos($date_hired, '/') !== false) {
            $date_hired = $date_helper->DateTimeToNZEntry($date_hired);
        }
        if (strpos($termination_date, '/') !== false) {
            $termination_date = $date_helper->DateTimeToNZEntry($termination_date);
        }

        if(!empty($image)){

        }

        $query = "INSERT INTO adviser_tbl 
        (team_id, name, company_name, payroll_name, fsp_num, address, email, birthday, leads, bonus, image, date_hired, termination_date) VALUES 
        ($team_id,'$name','$company_name','$payroll_name','$fsp_num','$address','$email','$birthday','$leads','$bonus', '$image', '$date_hired', '$termination_date')";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);
        $review_id = $this->mysqli->insert_id;

        $query = "Select * from adviser_tbl WHERE id = $review_id";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        return $dataset;
    }

    /**
		@desc: Create new user with name and email
     */
    public function updateAdviser(
        $id = 0,
        $team_id = "",
        $name = "",
        $company_name = "",
        $payroll_name = "",
        $fsp_num = "",
        $address = "",
        $email = "",
        $birthday = "",
        $leads = 35,
        $bonus = 50,
        $image = "",
        $date_hired = "",
        $termination_date = ""
    ) {
        $date_helper = new INDET_DATES_HELPER();

        $name = $this->clean($name);
        $company_name = $this->clean($company_name);
        $payroll_name = $this->clean($payroll_name);
        $address = $this->clean($address);

        if (strpos($birthday, '/') !== false) {
            $birthday = $date_helper->DateTimeToNZEntry($birthday);
        }
        if (strpos($date_hired, '/') !== false) {
            $date_hired = $date_helper->DateTimeToNZEntry($date_hired);
        }
        if (strpos($termination_date, '/') !== false) {
            $termination_date = $date_helper->DateTimeToNZEntry($termination_date);
        }

        if(!empty($image)){

        }

        $query = "UPDATE adviser_tbl SET team_id = '$team_id', name = '$name', company_name = '$company_name', payroll_name = '$payroll_name', fsp_num = '$fsp_num', 
        address = '$address', email = '$email', birthday = '$birthday', leads = '$leads', bonus = '$bonus', 
        date_hired = '$date_hired', termination_date = '$termination_date', image = '$image'
        WHERE id = $id";
        
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        $query = "Select * from adviser_tbl WHERE id = $id";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        return $dataset;
    }

    /**
		@desc: Get all questions from the specified Question Set
     */
    public function deleteAdviser(
        $id = 0    //
    ) {
        $query = "DELETE from adviser_tbl WHERE id = $id";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        return $dataset;
    }
}
