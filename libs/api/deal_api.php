<?php

/**
@name: leadGenerator_api.php
@author: Jesse
@desc:
	Serves as the API of the admins
	This page handles all asynchronous javascript request from the above mentioned page
@returnType:
	JSON
 */
if (!isset($_SESSION)) session_start();

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: PUT, GET, POST");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");

if (file_exists("controllers/Deal.controller.php"))          //for Controllers in libs
    include_once("controllers/Deal.controller.php");

if (file_exists("controllers/Adviser.controller.php"))          //for Controllers in libs
    include_once("controllers/Adviser.controller.php");

if (file_exists("classes/dateHelper.class.php"))          //for Controllers in libs
    include_once("classes/dateHelper.class.php");

//fetch POST request parameter 
$action = $_POST["action"];

//determine which function to trigger
switch ($action) {
    case "":
    default:
        echo json_encode(array("message" => "invalid request"));
        break;
    case "get_deal_tracker_report":
        echo GetDealTrackerReport();
        break;
    case "unissue_client":
        echo UnissueClient();
        break;
    case "get_submission_profile":
        echo GetSubmissionProfile();
        break;
    case "get_issued_client_profile":
        echo GetIssuedClientProfile();
        break;
    case "add_submission":
        echo AddSubmission();
        break;
    case "add_issued_policy":
        echo AddIssuedPolicy();
        break;
    case "update_submission":
        echo UpdateSubmission();
        break;
    case "update_issued_policy":
        echo UpdateIssuedPolicy();
        break;
    case "delete_submission":
        echo DeleteSubmission();
        break;
    case "get_unpaid_deals":
        echo GetUnpaidDeals();
        break;
    case "get_kiwisavers_from_adviser":
        echo GetKiwiSaversFromAdviser();
        break;
    case "add_kiwisaver_profile":
        echo AddKiwiSaver();
        break;
    case "update_kiwisaver_profile":
        echo UpdateKiwiSaver();
        break;
    case "get_kiwisaver_profile":
        echo GetKiwiSaver();
        break;
    case "delete_kiwisaver_profile":
        echo DeleteKiwiSaver();
        break;
    case "update_kiwisaver_deal":
        echo UpdateKiwiSaverDeal();
        break;
    case "get_kiwisaver_deal":
        echo GetKiwiSaverDeal();
    break;
    case "delete_kiwisaver_deal":
        echo DeleteKiwiSaverDeal();
    break;
}


/**
    @desc: Get Deal Tracker Report
 */
function GetDealTrackerReport()
{
    $controller = new DealController();
    extract($_POST);
    $data = $controller->GetDealTrackerReport($report_id);

    return json_encode($data);
}


/**
    @desc: Get Unpaid Deals
 */
function GetUnpaidDeals()
{
    $controller = new DealController();
    extract($_POST);
    $data = "";

    if (isset($request_from)) {
        switch ($request_from) {
            case "Payroll":
                $adviserController = new AdviserController();
                $adviser = (object) $adviserController->getAdviser($adviser_id);

                $start_date = $starting_date;

                $end_date = date("Ymt", strtotime($start_date));
                if (isset($adviser->id)) {
                    $data = $controller->GetUnpaidDeals($adviser->id, $start_date, $end_date);
                } else {
                    $data = array();
                    $data["error"] = "Adviser not registered on INDET.";
                }
                break;
        }
    }

    return json_encode($data);
}

/**
    @desc: Get KiwiSaver Deals
 */
function GetKiwiSaversFromAdviser()
{
    $controller = new DealController();
    extract($_POST);
    $data = "";

    if (isset($request_from)) {
        switch ($request_from) {
            case "Payroll":
                $adviserController = new AdviserController();
                $adviser = (object) $adviserController->getAdviser($adviser_id);

                $start_date = $starting_date;
                $end_date = date("Ymt", strtotime($start_date));
                if (isset($adviser->id)) {
                    $data = $controller->GetKiwiSaversFromAdviserForPayroll($adviser->id, $start_date, $end_date);
                } else {
                    $data = array();
                    $data["error"] = "Adviser not registered on INDET.";
                }

                break;
        }
    }


    return json_encode($data);
}

/**
    @desc: Unissue Specified Client
 */
function UnissueClient()
{
    $controller = new DealController();
    extract($_POST);
    $data = $controller->unissueClient($client_id);

    return json_encode($data);
}

function GetSubmissionProfile()
{
    $controller = new DealController();
    extract($_POST);
    $data = $controller->getSubmissionClientProfile($client_id);

    //Start data extraction
    extract($data);
    $timestamp_order = $timestamp;
    $timestamp = date('d/m/Y', strtotime($timestamp));
    $deals = json_decode($deals);
    $deals_count = 0;
    $deals_count = count($deals);

    $total_api = 0;

    $unique_client_names = [];
    $unique_policy_numbers = [];
    $statuses = array();
    $unique_insurers = [];
    $unique_client_names[] = $client_name;

    foreach ($deals as $deal) {
        $statuses[] = $deal->status;

        //push into array if not in there
        if (!in_array($deal->company, $unique_insurers)) {
            if ($deal->company != "Others")
                $unique_insurers[] = $deal->company;
            else
                $unique_insurers[] = $deal->specific_company;
        }

        if (!in_array($deal->life_insured, $unique_client_names)) {
            if (!empty($deal->life_insured))
                $unique_client_names[] = $deal->life_insured;
        }

        if (!in_array($deal->policy_number, $unique_policy_numbers)) {
            $unique_policy_numbers[] = $deal->policy_number;
        }

        if ($deal->status == "Pending")
            $total_api += $deal->original_api;
    }

    $data["client_name"] = $client_name;
    $data["timestamp_order"] = $timestamp_order;
    $data["timestamp"] = $timestamp;
    $data["statuses"] = implode(", ", $statuses);
    $data["unique_client_names"] = implode(', ', $unique_client_names);
    $data["unique_policy_numbers"] = implode(', ', $unique_policy_numbers);
    $data["unique_insurers"] = implode(', ', $unique_insurers);
    $data["total_api"] = number_format($total_api, 2);
    $data["deals_count"] = $deals_count;

    return json_encode($data);
}

function GetIssuedClientProfile()
{
    $controller = new DealController();
    extract($_POST);
    $data = $controller->getIssuedClientProfile($client_id);

    //Start data extraction
    extract($data);
    $deals = json_decode($deals_data);
    $deals_count = 0;
    $deals_count = count($deals);

    $total_api = 0;

    $statuses = array();
    $unique_client_names = [];
    $unique_policy_numbers = [];
    $unique_insurers = [];
    $unique_client_names[] = $name;

    foreach ($deals as $deal) {
        $statuses[] = $deal->status;

        //push into array if not in there
        if (!in_array($deal->company, $unique_insurers)) {
            if ($deal->company != "Others")
                $unique_insurers[] = $deal->company;
            else
                $unique_insurers[] = $deal->specific_company;
        }

        if (!in_array($deal->life_insured, $unique_client_names)) {
            if (!empty($deal->life_insured))
                $unique_client_names[] = $deal->life_insured;
        }

        if (!in_array($deal->policy_number, $unique_policy_numbers)) {
            $unique_policy_numbers[] = $deal->policy_number;
        }

        if ($deal->status == "Pending")
            $total_api += $deal->original_api;
    }

    $data["client_name"] = $name;
    $data["statuses"] = implode(", ", $statuses);
    $data["unique_client_names"] = implode(', ', $unique_client_names);
    $data["unique_policy_numbers"] = implode(', ', $unique_policy_numbers);
    $data["unique_insurers"] = implode(', ', $unique_insurers);
    $data["total_api"] = number_format($total_api, 2);
    $data["deals_count"] = $deals_count;

    return json_encode($data);
}

/**
    @desc: Add Submission
 */
function AddSubmission()
{
    $controller = new DealController();
    extract($_POST);
    $data = $controller->AddSubmission($client_id, $deals_data);

    //Start data extraction
    extract($data);
    $timestamp_order = $timestamp;
    $timestamp = date('d/m/Y', strtotime($timestamp));
    $deals = json_decode($deals);
    $deals_count = 0;
    $deals_count = count($deals);

    $total_api = 0;

    $statuses = [];
    $unique_client_names = [];
    $unique_policy_numbers = [];
    $unique_insurers = [];
    $unique_client_names[] = $client_name;

    foreach ($deals as $deal) {
        $statuses[] = $deal->status;

        //push into array if not in there
        if (!in_array($deal->company, $unique_insurers)) {
            if ($deal->company != "Others")
                $unique_insurers[] = $deal->company;
            else
                $unique_insurers[] = $deal->specific_company;
        }

        if (!in_array($deal->life_insured, $unique_client_names)) {
            if (!empty($deal->life_insured))
                $unique_client_names[] = $deal->life_insured;
        }

        if (!in_array($deal->policy_number, $unique_policy_numbers)) {
            $unique_policy_numbers[] = $deal->policy_number;
        }

        if ($deal->status == "Pending")
            $total_api += $deal->original_api;
    }

    $data["client_name"] = $client_name;
    $data["timestamp_order"] = $timestamp_order;
    $data["timestamp"] = $timestamp;
    $data["statuses"] = implode(", ", $statuses);
    $data["unique_client_names"] = implode(', ', $unique_client_names);
    $data["unique_policy_numbers"] = implode(', ', $unique_policy_numbers);
    $data["unique_insurers"] = implode(', ', $unique_insurers);
    $data["total_api"] = number_format($total_api, 2);
    $data["deals_count"] = $deals_count;

    //End data extraction
    return json_encode($data);
}

/**
    @desc: Add Issued Policy
 */
function AddIssuedPolicy()
{
    $controller = new DealController();
    extract($_POST);
    $data = $controller->AddIssuedPolicy($client_id, $leadgen, $assigned_to, $deals_data);

    //Start data extraction
    extract($data);
    $client_id = $data["client_id"];
    $name = $data["name"];
    $x = $data["x"]; //advisername
    $deals_data = json_decode($data["deals_data"]);
    $statuses = [];
    $policy_numbers = array();
    $unique_client_names = array();
    $unique_client_names[] = $name;

    foreach ($deals_data as $deal) {
        $statuses[] = $deal->status;

        if (!in_array($deal->life_insured, $unique_client_names)) {
            if (!empty($deal->life_insured))
                $unique_client_names[] = $deal->life_insured;
        }

        if (!in_array($deal->policy_number, $policy_numbers)) {
            $policy_numbers[] = $deal->policy_number;
        }
    }

    $policy_number = implode(", ", $policy_numbers);
    $unique_client_names = implode(", ", $unique_client_names);


    $data["adviser"] = $x;
    $data["statuses"] = implode(", ", $statuses);
    $data["unique_policy_numbers"] = $policy_number;
    $data["unique_client_names"] = $unique_client_names;


    //End data extraction
    return json_encode($data);
}

/**
    @desc: Get KiwiSaver
 */
function GetKiwiSaver()
{
    $controller = new DealController();
    extract($_POST);
    $data = $controller->GetKiwiSaverProfile($kiwisaver_id);

    $data["client_id"] = $data["id"];
    $data["adviser_name"] = $data["adviser_name"];
    $data["timestamp"] = date("d/m/Y", strtotime($data["timestamp"]));

    //End data extraction
    return json_encode($data);
}

/**
    @desc: Get KiwiSaver
 */
function GetKiwiSaverDeal()
{
    $controller = new DealController();
    extract($_POST);
    $data = $controller->GetKiwiSaverDeal($id);

    return json_encode($data);
}

/**
    @desc: Add KiwiSaver
 */
function UpdateKiwiSaverDeal()
{
    $controller = new DealController();
    extract($_POST);

    $data = $controller->UpdateKiwiSaverDeal($id, $deal_data);
    
    //End data extraction
    return json_encode($data);
}

/**
    @desc: Get KiwiSaver
 */
function DeleteKiwiSaverDeal()
{
    $controller = new DealController();
    extract($_POST);
    $data = $controller->DeleteKiwiSaverDeal($id);

    return json_encode($data);
}


/**
    @desc: Add KiwiSaver
 */
function AddKiwiSaver()
{
    $controller = new DealController();
    extract($_POST);
    extract($_POST["deals_data"]);

    $data = $controller->AddKiwiSaverProfile($client_id, $deals_data);

    $commission = 0; //advisername
    $gst = 0; //advisername
    $balance = 0; //advisername

    foreach ($data["kiwisaver_deals"] as $deal) {
        $commission += $deal["commission"];
        $gst += $deal["gst"];
        $balance += $deal["balance"];
    }

    $data["issue_date_order"] = date("Ymd", strtotime($data["timestamp"]));
    $data["issue_date"] = date("d/m/Y", strtotime($data["timestamp"]));
    $data["commission"] = $commission;
    $data["gst"] = $gst;
    $data["balance"] = $balance;

    //End data extraction
    return json_encode($data);
}


/**
    @desc: Add KiwiSaver
 */
function UpdateKiwiSaver()
{
    $controller = new DealController();
    extract($_POST);
    extract($_POST["deals_data"]);

    $data = $controller->UpdateKiwiSaverProfile($kiwisaver_profile_id, $deals_data);
    
    $commission = 0; //advisername
    $gst = 0; //advisername
    $balance = 0; //advisername

    foreach ($data["kiwisaver_deals"] as $deal) {
        $commission += $deal["commission"];
        $gst += $deal["gst"];
        $balance += $deal["balance"];
    }

    $data["issue_date_order"] = date("Ymd", strtotime($data["timestamp"]));
    $data["issue_date"] = date("d/m/Y", strtotime($data["timestamp"]));
    $data["commission"] = $commission;
    $data["gst"] = $gst;
    $data["balance"] = $balance;

    //End data extraction
    return json_encode($data);
}

/**
    @desc: Delete KiwiSaver
 */
function DeleteKiwiSaver()
{
    $controller = new DealController();
    extract($_POST);
    $data = $controller->DeleteKiwiSaverProfile($kiwisaver_profile_id);

    //End data extraction
    return json_encode($data);
}
/**
    @desc: Unissue Specified Client
 */
function UpdateSubmission()
{
    $controller = new DealController();
    extract($_POST);
    $data = $controller->UpdateSubmission($client_id, $deals_data);

    //Start data extraction
    extract($data);
    $timestamp_order = $timestamp;
    $timestamp = date('d/m/Y', strtotime($timestamp));
    $deals = json_decode($deals);
    $deals_count = 0;
    $deals_count = count($deals);

    $total_api = 0;

    $statuses = [];
    $unique_client_names = [];
    $unique_policy_numbers = [];
    $unique_insurers = [];
    $unique_client_names[] = $client_name;

    foreach ($deals as $deal) {
        $statuses[] = $deal->status;

        //push into array if not in there
        if (!in_array($deal->company, $unique_insurers)) {
            if ($deal->company != "Others")
                $unique_insurers[] = $deal->company;
            else
                $unique_insurers[] = $deal->specific_company;
        }

        if (!in_array($deal->life_insured, $unique_client_names)) {
            if (!empty($deal->life_insured))
                $unique_client_names[] = $deal->life_insured;
        }

        if (!in_array($deal->policy_number, $unique_policy_numbers)) {
            $unique_policy_numbers[] = $deal->policy_number;
        }

        if ($deal->status == "Pending")
            $total_api += $deal->original_api;
    }

    $data["client_name"] = $client_name;
    $data["timestamp_order"] = $timestamp_order;
    $data["timestamp"] = $timestamp;
    $data["statuses"] = implode(", ", $statuses);
    $data["unique_client_names"] = implode(', ', $unique_client_names);
    $data["unique_policy_numbers"] = implode(', ', $unique_policy_numbers);
    $data["unique_insurers"] = implode(', ', $unique_insurers);
    $data["total_api"] = number_format($total_api, 2);
    $data["deals_count"] = $deals_count;

    //End data extraction
    return json_encode($data);
}

/**
    @desc: Add Issued Policy
 */
function UpdateIssuedPolicy()
{
    $controller = new DealController();
    extract($_POST);
    $data = $controller->UpdateIssuedPolicy($client_id, $deals_data);

    //Start data extraction
    extract($data);
    $client_id = $data["client_id"];
    $name = $data["name"];
    $x = $data["x"]; //advisername
    $deals_data = json_decode($data["deals_data"]);
    $policy_numbers = array();
    $unique_client_names = array();
    $statuses = [];
    $unique_client_names[] = $name;

    foreach ($deals_data as $deal) {
        $statuses[] = $deal->status;

        if (!in_array($deal->life_insured, $unique_client_names)) {
            if (!empty($deal->life_insured))
                $unique_client_names[] = $deal->life_insured;
        }

        if (!in_array($deal->policy_number, $policy_numbers)) {
            $policy_numbers[] = $deal->policy_number;
        }
    }

    $policy_number = implode(", ", $policy_numbers);
    $unique_client_names = implode(", ", $unique_client_names);


    $data["adviser"] = $x;
    $data["unique_policy_numbers"] = $policy_number;
    $data["unique_client_names"] = $unique_client_names;
    $data["statuses"] = implode(", ", $statuses);


    //End data extraction
    return json_encode($data);
}
/**
    @desc: Delete Specified Submission Client
 */
function DeleteSubmission()
{
    $controller = new DealController();
    extract($_POST);
    $data = $controller->deleteSubmissionClientProfile($client_id);

    return json_encode($data);
}
