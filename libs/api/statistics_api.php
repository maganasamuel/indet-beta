<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: PUT, GET, POST");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
require_once "../indet_dates_helper.php";
include_once("../api/classes/general.class.php");
include_once("../api/controllers/Deal.controller.php");

$indet_dates_helper = new INDET_DATES_HELPER();
$restrict_session_check = false;
require "../../database.php";
//Get Data here
include "../../statistics/fetch_statistics_data.php";
require "../../statistics/statistics-charts-data-for-api.php";

echo json_encode($data);
