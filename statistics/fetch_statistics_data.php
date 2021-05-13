<?php

error_reporting(E_ERROR | E_PARSE);

//Class for Quarters
class QuarterData
{
    public $quantity = 0;
    public $api = 0;
    public $deals_data = array();
}

//Class for Months
class MonthData
{
    public $deals = 0;
    public $api = 0;
    public $deals_data = array();
}

//Class for Year
class YearData
{
    public $months = array();
    public $quarters = array();
    public $quantity = 0;
    public $api = 0;

    function __construct($ms = 12, $qs = 4)
    {

        for ($i = 0; $i < $ms; $i++) {
            $this->months[] = new MonthData();
        }

        for ($i = 0; $i < $qs; $i++) {
            $this->quarters[] = new QuarterData();
        }
    }
}

$adviser = array();             //Advisers Array
$all_deals = array();           //Deals Array
$data = new stdClass();         //Data storage


$data->year = date("Y");        //Get Current Year
if (isset($_GET["year"]))        //Give option to display data from another year
    $data->year = $_GET["year"]; //Set entered year as current year        

//Set Quarters Data
$data->first_quarter = $indet_dates_helper->GetQuarter("First", $data->year);
$data->second_quarter = $indet_dates_helper->GetQuarter("Second", $data->year);
$data->third_quarter = $indet_dates_helper->GetQuarter("Third", $data->year);
$data->fourth_quarter = $indet_dates_helper->GetQuarter("Fourth", $data->year);

//Lead Gen
$data->total_leads = 0;

//Telemarketers Data
$data->telemarketers_data = new YearData();
$data->telemarketers_data_total = 0;

//BDM's Data
$data->bdms_data = new YearData();
$data->bdms_data_total = 0;

$data->self_gen_data = new YearData();
$data->self_gen_data_total = 0;

$data->total_issued = 0;
$data->total_issued_api = 0;
$data->total_submissions = 0;
$data->total_submission_api = 0;

//For Period
$data->leads_assigned_for_period = 0;
$data->leads_submitted_for_period = 0;
$data->submission_api_for_period = 0;
$data->leads_issued_for_period = 0;
$data->issued_api_for_period = 0;

$data->deal_cancellations_for_period = 0;
$data->deal_cancellations_api_for_period = 0;

//Fetch date span today
$now = $initial = $end = date("Ymd");
$due = date('d/m/Y', strtotime('+7 days'));

if (date("d") > 15) {
    $initial = date("Ym") . "16";
    $end = date("Ymt");
    //Second Date Range
} else {
    $initial = date("Ym") . "01";
    $end = date("Ym") . "15";
}

//Fetch all of Adviser's issued leads data
$query = "SELECT *, c.name as client_name, c.date_submitted as date_generated, s.timestamp as date_submitted, i.date_issued as date_issued, a.id as adviser_id, a.fsp_num as fsp_num, c.id as client_id FROM clients_tbl c LEFT JOIN submission_clients s ON c.id = s.client_id LEFT JOIN issued_clients_tbl i ON i.name = c.id LEFT JOIN adviser_tbl a ON a.id=c.assigned_to WHERE c.status!='Cancelled'";
//echo $query . "<hr>";
$displayquery = mysqli_query($con, $query) or die('Could not look up user information; ' . mysqli_error($con));
$ctr = 0;

$data->totals = new stdClass();

$data->totals->pending_deals = 0;
$data->totals->pending_deals_api = 0;
$data->totals->submissions = 0;
$data->totals->submission_api = 0;
$data->totals->issued_deals = 0;
$data->totals->issued_deals_api = 0;
$data->totals->cancelled_deals = 0;
$data->totals->cancelled_deals_api = 0;

$data->issued_data = new YearData();

$data->submissions_data = new YearData();

$data->cancellations_data = new YearData();


$data->debug = new stdClass();
$data->debug->client = array();

while ($row = mysqli_fetch_assoc($displayquery)) {
    extract($row);

    $client_debug_data = new stdClass();

    //Reset Client API
    $data->totals->client_api = 0;

    //Get Adviser Info
    if ($ctr == 0) {
        $adviser = (object) $row;
    }

    //Get Deals
    $all_deals[] = json_encode($deals);

    if ($date_issued != null) {

        $data->total_issued++;

        $data->total_issued_api += (float) $issued;
        //echo "<h3>$client_name</h3><br>Issued: $issued <br> Original API: $deal->original_api <hr>";
        $date_to_compare = $date_issued;
        if ($date_to_compare <= $end && $date_to_compare >= $initial) {
            $data->issued_api_for_period += $issued;
            $data->leads_issued_for_period++;
            $data->issued[] = array(
                "Client" => $client_name,
                "Amount" => $issued,
                "Date" => $date_to_compare,
                "Deals" => $deals,
            );
        }
    }


    if ($date_submitted != null) {

        $submission_date = date("Ymd", strtotime($date_submitted));
        $date_to_compare = $submission_date;
        $sub_deals = json_decode($deals);
        //var_dump($sub_deals);
        //echo "<hr>$client_name<hr>";
        foreach ($sub_deals as $deal) {
            $data->total_submissions++;
            $data->total_submission_api += $deal->original_api;
            /*
                    if($deal->status=="Pending"||$deal->status=="Issued"){ 
                        $data->total_submission_api += $deal->original_api;
                    }
                */
            $d_to_compare = $deal->submission_date;
            //Quarterly Data
            if ($d_to_compare <= $data->first_quarter->to->format("Ymd") && $d_to_compare >= $data->first_quarter->from->format("Ymd")) {
                $data->submissions_data->quarters[0]->quantity++;
                $data->submissions_data->quarters[0]->api += $deal->original_api;
                $data->submissions_data->quarters[0]->deals_data[] = $deal;

                $data->submissions_data->quantity++;
                $data->submissions_data->api += $deal->original_api;
            } elseif ($d_to_compare <= $data->second_quarter->to->format("Ymd") && $d_to_compare >= $data->second_quarter->from->format("Ymd")) {
                $data->submissions_data->quarters[1]->quantity++;
                $data->submissions_data->quarters[1]->api += $deal->original_api;
                $data->submissions_data->quarters[1]->deals_data[] = $deal;

                $data->submissions_data->quantity++;
                $data->submissions_data->api += $deal->original_api;
            } elseif ($d_to_compare <= $data->third_quarter->to->format("Ymd") && $d_to_compare >= $data->third_quarter->from->format("Ymd")) {
                $data->submissions_data->quarters[2]->quantity++;
                $data->submissions_data->quarters[2]->api += $deal->original_api;
                $data->submissions_data->quarters[2]->deals_data[] = $deal;
                //echo "3 Q";     
                $data->submissions_data->quantity++;
                $data->submissions_data->api += $deal->original_api;
            } elseif ($d_to_compare <= $data->fourth_quarter->to->format("Ymd") && $d_to_compare >= $data->fourth_quarter->from->format("Ymd")) {
                $data->submissions_data->quarters[3]->quantity++;
                $data->submissions_data->quarters[3]->api += $deal->original_api;
                $data->submissions_data->quarters[3]->deals_data[] = $deal;
                //echo "4 Q";     
                $data->submissions_data->quantity++;
                $data->submissions_data->api += $deal->original_api;
            }

            //Monthly Data
            $m1 = date('m', strtotime($d_to_compare));
            $y1 = date('Y', strtotime($d_to_compare));

            if ($y1 == date('Y')) {
                $data->submissions_data->months[($m1 - 1)]->deals++;
                $data->submissions_data->months[($m1 - 1)]->api += $deal->original_api;
                $data->submissions_data->months[($m1 - 1)]->deals_data[] = $deal;
            }


            if ($deal->status == "Pending") {
                $data->totals->pending_deals++;
                $data->totals->pending_deals_api += $deal->original_api;
                $life_insured = $client_name;
                if (!empty($deal->life_insured))
                    $life_insured .= ", " . $deal->life_insured;

                $data->submitted[] = array(
                    "Client" => $life_insured,
                    "Date" => $indet_dates_helper->NZEntryToDateTime($deal->submission_date),
                    "Deal" => $deal,
                    "SubmissionAPI" => $deal->original_api,
                );
            } elseif ($deal->status == "Issued") {
                $data->totals->issued_deals++;
                $data->totals->issued_deals_api += $deal->issued_api;

                //Client Specific api
                $data->totals->client_api += $deal->issued_api;

                $life_insured = $client_name;
                if (!empty($deal->life_insured))
                    $life_insured .= ", " . $deal->life_insured;

                $data->issued_deals[] = array(
                    "Client" => $life_insured,
                    "Date" => $indet_dates_helper->NZEntryToDateTime($deal->date_issued),
                    "Deal" => $deal,
                    "IssuedAPI" => $deal->issued_api,
                );
                //echo "<hr>". NZEntryToDateTime($deal->date_issued);
                //Quarterly and Monthly 
                $d_to_compare = $deal->date_issued;

                if ($d_to_compare <= $data->first_quarter->to->format("Ymd") && $d_to_compare >= $data->first_quarter->from->format("Ymd")) {
                    $data->issued_data->quarters[0]->quantity++;
                    $data->issued_data->quarters[0]->api += $deal->issued_api;
                    $data->issued_data->quarters[0]->deals_data[] = $deal;

                    $data->issued_data->quantity++;
                    $data->issued_data->api += $deal->issued_api;
                } elseif ($d_to_compare <= $data->second_quarter->to->format("Ymd") && $d_to_compare >= $data->second_quarter->from->format("Ymd")) {
                    $data->issued_data->quarters[1]->quantity++;
                    $data->issued_data->quarters[1]->api += $deal->issued_api;
                    $data->issued_data->quarters[1]->deals_data[] = $deal;

                    $data->issued_data->quantity++;
                    $data->issued_data->api += $deal->issued_api;
                } elseif ($d_to_compare <= $data->third_quarter->to->format("Ymd") && $d_to_compare >= $data->third_quarter->from->format("Ymd")) {
                    $data->issued_data->quarters[2]->quantity++;
                    $data->issued_data->quarters[2]->api += $deal->issued_api;
                    $data->issued_data->quarters[2]->deals_data[] = $deal;
                    //echo "3 Q";     
                    $data->issued_data->quantity++;
                    $data->issued_data->api += $deal->issued_api;
                } elseif ($d_to_compare <= $data->fourth_quarter->to->format("Ymd") && $d_to_compare >= $data->fourth_quarter->from->format("Ymd")) {
                    $data->issued_data->quarters[3]->quantity++;
                    $data->issued_data->quarters[3]->api += $deal->issued_api;
                    $data->issued_data->quarters[3]->deals_data[] = $deal;
                    //echo "4 Q";     
                    $data->issued_data->quantity++;
                    $data->issued_data->api += $deal->issued_api;
                }

                $m1 = date('m', strtotime($d_to_compare));
                $y1 = date('Y', strtotime($d_to_compare));

                if ($y1 == date('Y')) {
                    $data->issued_data->months[($m1 - 1)]->deals++;
                    $data->issued_data->months[($m1 - 1)]->api += $deal->issued_api;
                    $data->issued_data->months[($m1 - 1)]->deals_data[] = $deal;
                }

                if (isset($deal->clawback_status)) {
                    if ($deal->clawback_status == "Cancelled") {
                        $data->totals->cancelled_deals++;
                        $data->totals->cancelled_deals_api += $deal->clawback_api;
                        $life_insured = $client_name;
                        if (!empty($deal->life_insured))
                            $life_insured .= ", " . $deal->life_insured;

                        $data->cancelled_deals[] = array(
                            "Client" => $life_insured,
                            "Date" => $indet_dates_helper->NZEntryToDateTime($deal->clawback_date),
                            "Deal" => $deal,
                            "CancelledAPI" => $deal->clawback_api,
                        );

                        $d_to_compare = $deal->clawback_date;

                        //Quarterly Data
                        if ($d_to_compare <= $data->first_quarter->to->format("Ymd") && $d_to_compare >= $data->first_quarter->from->format("Ymd")) {
                            $data->cancellations_data->quarters[0]->quantity++;
                            $data->cancellations_data->quarters[0]->api += $deal->clawback_api;
                            $data->cancellations_data->quarters[0]->deals_data[] = $deal;

                            $data->cancellations_data->quantity++;
                            $data->cancellations_data->api += $deal->clawback_api;
                        } elseif ($d_to_compare <= $data->second_quarter->to->format("Ymd") && $d_to_compare >= $data->second_quarter->from->format("Ymd")) {
                            $data->cancellations_data->quarters[1]->quantity++;
                            $data->cancellations_data->quarters[1]->api += $deal->clawback_api;
                            $data->cancellations_data->quarters[1]->deals_data[] = $deal;

                            $data->cancellations_data->quantity++;
                            $data->cancellations_data->api += $deal->clawback_api;
                        } elseif ($d_to_compare <= $data->third_quarter->to->format("Ymd") && $d_to_compare >= $data->third_quarter->from->format("Ymd")) {
                            $data->cancellations_data->quarters[2]->quantity++;
                            $data->cancellations_data->quarters[2]->api += $deal->clawback_api;
                            $data->cancellations_data->quarters[2]->deals_data[] = $deal;
                            //echo "3 Q";     
                            $data->cancellations_data->quantity++;
                            $data->cancellations_data->api += $deal->clawback_api;
                        } elseif ($d_to_compare <= $data->fourth_quarter->to->format("Ymd") && $d_to_compare >= $data->fourth_quarter->from->format("Ymd")) {
                            $data->cancellations_data->quarters[3]->quantity++;
                            $data->cancellations_data->quarters[3]->api += $deal->clawback_api;
                            $data->cancellations_data->quarters[3]->deals_data[] = $deal;
                            //echo "4 Q";     
                            $data->cancellations_data->quantity++;
                            $data->cancellations_data->api += $deal->clawback_api;
                        }

                        //Monthly Data
                        $m1 = date('m', strtotime($d_to_compare));
                        $y1 = date('Y', strtotime($d_to_compare));

                        if ($y1 == date('Y')) {
                            $data->cancellations_data->months[($m1 - 1)]->deals++;
                            $data->cancellations_data->months[($m1 - 1)]->api += $deal->clawback_api;
                            $data->cancellations_data->months[($m1 - 1)]->deals_data[] = $deal;
                        }

                        if ($deal->clawback_date <= $end && $deal->clawback_date >= $initial) {
                            $data->deal_cancellations_for_period++;
                            $data->deal_cancellations_api_for_period += $deal->clawback_api;
                        }
                    }
                }
            }
        }
        if ($date_to_compare <= $end && $date_to_compare >= $initial) {
            $data->submission_api_for_period += $deal->original_api;
            $data->leads_submitted_for_period++;
        }
    }

    if ($date_generated != null) {
        $date_to_compare = $date_generated;
        if ($date_to_compare <= $end && $date_to_compare >= $initial) {
            $data->leads_assigned_for_period++;
            $data->generated[] = array(
                "Client" => $client_name,
                "Date" => $date_to_compare,
            );
        }
    }


    $data->total_leads++;



    if ($lead_by == "Telemarketer") {
        $data->telemarketers_data_total++;

        if ($date_generated <= $data->first_quarter->to->format("Ymd") && $date_generated >= $data->first_quarter->from->format("Ymd")) {
            $data->telemarketers_data->quarters[0]->quantity++;
            $data->telemarketers_data->quarters[0]->api += $data->totals->client_api;
        } elseif ($date_generated <= $data->second_quarter->to->format("Ymd") && $date_generated >= $data->second_quarter->from->format("Ymd")) {
            $data->telemarketers_data->quarters[1]->quantity++;
            $data->telemarketers_data->quarters[1]->api += $data->totals->client_api;
        } elseif ($date_generated <= $data->third_quarter->to->format("Ymd") && $date_generated >= $data->third_quarter->from->format("Ymd")) {
            $data->telemarketers_data->quarters[2]->quantity++;
            $data->telemarketers_data->quarters[2]->api += $data->totals->client_api;
        } elseif ($date_generated <= $data->fourth_quarter->to->format("Ymd") && $date_generated >= $data->fourth_quarter->from->format("Ymd")) {
            $data->telemarketers_data->quarters[3]->quantity++;
            $data->telemarketers_data->quarters[3]->api += $data->totals->client_api;
            //debug data
            $client_debug_data->name = $client_name;
            $client_debug_data->issued_api = $data->totals->client_api;
            $client_debug_data->lead_gen = $lead_by;

            $data->debug->client[] = $client_debug_data;
        }


        $year_submitted = date('Y', strtotime($date_generated));

        if ($year_submitted <= $data->fourth_quarter->to->format("Y") && $year_submitted >= $data->fourth_quarter->from->format("Y")) {
            //Monthly Data
            $m1 = date('m', strtotime($date_generated));
            $data->telemarketers_data->months[($m1 - 1)]->deals++;
            $data->telemarketers_data->months[($m1 - 1)]->api += $data->totals->client_api;

            //Yearly Data   
            $data->telemarketers_data->quantity++;
            $data->telemarketers_data->api += $data->totals->client_api;
        }
    } elseif ($lead_by == "Face-to-Face Marketer") {
        $data->bdms_data_total++;
        if ($date_generated <= $data->first_quarter->to->format("Ymd") && $date_generated >= $data->first_quarter->from->format("Ymd")) {
            $data->bdms_data->quarters[0]->quantity++;
            $data->bdms_data->quarters[0]->api += $data->totals->client_api;


        } elseif ($date_generated <= $data->second_quarter->to->format("Ymd") && $date_generated >= $data->second_quarter->from->format("Ymd")) {
            $data->bdms_data->quarters[1]->quantity++;
            $data->bdms_data->quarters[1]->api += $data->totals->client_api;
        } elseif ($date_generated <= $data->third_quarter->to->format("Ymd") && $date_generated >= $data->third_quarter->from->format("Ymd")) {
            $data->bdms_data->quarters[2]->quantity++;
            $data->bdms_data->quarters[2]->api += $data->totals->client_api;
        } elseif ($date_generated <= $data->fourth_quarter->to->format("Ymd") && $date_generated >= $data->fourth_quarter->from->format("Ymd")) {
            $data->bdms_data->quarters[3]->quantity++;
            $data->bdms_data->quarters[3]->api += $data->totals->client_api;
        }


        $year_submitted = date('Y', strtotime($date_generated));

        if ($year_submitted <= $data->fourth_quarter->to->format("Y") && $year_submitted >= $data->fourth_quarter->from->format("Y")) {
            //Monthly Data
            $m1 = date('m', strtotime($date_generated));
            $data->bdms_data->months[($m1 - 1)]->deals++;
            $data->bdms_data->months[($m1 - 1)]->api += $data->totals->client_api;

            //Yearly Data   
            $data->bdms_data->quantity++;
            $data->bdms_data->api += $data->totals->client_api;
        }
    } elseif ($lead_by == "Self-Generated") {
        $data->self_gen_data_total++;

        if ($date_generated <= $data->first_quarter->to->format("Ymd") && $date_generated >= $data->first_quarter->from->format("Ymd")) {
            $data->self_gen_data->quarters[0]->quantity++;
            $data->self_gen_data->quarters[0]->api += $data->totals->client_api;
        } elseif ($date_generated <= $data->second_quarter->to->format("Ymd") && $date_generated >= $data->second_quarter->from->format("Ymd")) {
            $data->self_gen_data->quarters[1]->quantity++;
            $data->self_gen_data->quarters[1]->api += $data->totals->client_api;
        } elseif ($date_generated <= $data->third_quarter->to->format("Ymd") && $date_generated >= $data->third_quarter->from->format("Ymd")) {
            $data->self_gen_data->quarters[2]->quantity++;
            $data->self_gen_data->quarters[2]->api += $data->totals->client_api;
        } elseif ($date_generated <= $data->fourth_quarter->to->format("Ymd") && $date_generated >= $data->fourth_quarter->from->format("Ymd")) {
            $data->self_gen_data->quarters[3]->quantity++;
            $data->self_gen_data->quarters[3]->api += $data->totals->client_api;
        }

        $year_submitted = date('Y', strtotime($date_generated));

        if ($year_submitted <= $data->fourth_quarter->to->format("Y") && $year_submitted >= $data->fourth_quarter->from->format("Y")) {
            //Monthly Data
            $m1 = date('m', strtotime($date_generated));
            $data->self_gen_data->months[($m1 - 1)]->deals++;
            $data->self_gen_data->months[($m1 - 1)]->api += $data->totals->client_api;

            //Yearly Data    
            $data->self_gen_data->quantity++;
            $data->self_gen_data->api += $data->totals->client_api;
        }
    }

    $ctr++;
}


$dealController = new DealController();
$generalController = new General();

$data->totals->kiwisaver_deals = 0;
$data->totals->kiwisaver_deals_api = 0;
$data->kiwisavers_data = new YearData();

//Get First Quarter
$kiwisaver_deals = $dealController->GetCompanyCumulativeKiwiSaver();
$data->totals->kiwisaver_deals = $kiwisaver_deals["total_deals"];
$data->totals->kiwisaver_deals_api = $kiwisaver_deals["total_commission"];
if($data->totals->kiwisaver_deals_api==null){
    $data->totals->kiwisaver_deals_api = 0;
}

//Get First Quarter
$kiwisaver_deals = $dealController->GetCompanyCumulativeKiwiSaverInDateRange($data->first_quarter->from->format("Ymd"), $data->first_quarter->to->format("Ymd"));
$data->kiwisavers_data->quarters[0]->quantity = $kiwisaver_deals["total_deals"];
$data->kiwisavers_data->quarters[0]->api += $kiwisaver_deals["total_commission"];

$data->kiwisavers_data->quantity += $kiwisaver_deals["total_deals"];
$data->kiwisavers_data->api += $kiwisaver_deals["total_commission"];

$kiwisaver_deals = $dealController->GetKiwiSaversIssuedByTeamInDateRange($data->first_quarter->from->format("Ymd"), $data->first_quarter->to->format("Ymd"));
while ($deal = $kiwisaver_deals->fetch_assoc()) {
    $data->kiwisavers_data->quarters[0]->deals_data[] = $deal;
}

//Get Second Quarter
$kiwisaver_deals = $dealController->GetCompanyCumulativeKiwiSaverInDateRange($data->second_quarter->from->format("Ymd"), $data->second_quarter->to->format("Ymd"));

$data->kiwisavers_data->quarters[1]->quantity = $kiwisaver_deals["total_deals"];
$data->kiwisavers_data->quarters[1]->api += $kiwisaver_deals["total_commission"];

$data->kiwisavers_data->quantity += $kiwisaver_deals["total_deals"];
$data->kiwisavers_data->api += $kiwisaver_deals["total_commission"];

$kiwisaver_deals = $dealController->GetKiwiSaversIssuedByTeamInDateRange($data->second_quarter->from->format("Ymd"), $data->second_quarter->to->format("Ymd"));

while ($deal = $kiwisaver_deals->fetch_assoc()) {
    $data->kiwisavers_data->quarters[1]->deals_data[] = $deal;
}

//Get Third Quarter
$kiwisaver_deals = $dealController->GetCompanyCumulativeKiwiSaverInDateRange($data->third_quarter->from->format("Ymd"), $data->third_quarter->to->format("Ymd"));

$data->kiwisavers_data->quarters[2]->quantity = $kiwisaver_deals["total_deals"];
$data->kiwisavers_data->quarters[2]->api += $kiwisaver_deals["total_commission"];

$data->kiwisavers_data->quantity += $kiwisaver_deals["total_deals"];
$data->kiwisavers_data->api += $kiwisaver_deals["total_commission"];

$kiwisaver_deals = $dealController->GetKiwiSaversIssuedByTeamInDateRange($data->third_quarter->from->format("Ymd"), $data->third_quarter->to->format("Ymd"));

while ($deal = $kiwisaver_deals->fetch_assoc()) {
    $data->kiwisavers_data->quarters[2]->deals_data[] = $deal;
}

//Get Fourth Quarter
$kiwisaver_deals = $dealController->GetCompanyCumulativeKiwiSaverInDateRange($data->fourth_quarter->from->format("Ymd"), $data->fourth_quarter->to->format("Ymd"));

$data->kiwisavers_data->quarters[3]->quantity = $kiwisaver_deals["total_deals"];
$data->kiwisavers_data->quarters[3]->api += $kiwisaver_deals["total_commission"];

$data->kiwisavers_data->quantity += $kiwisaver_deals["total_deals"];
$data->kiwisavers_data->api += $kiwisaver_deals["total_commission"];

$kiwisaver_deals = $dealController->GetKiwiSaversIssuedByTeamInDateRange($data->fourth_quarter->from->format("Ymd"), $data->fourth_quarter->to->format("Ymd"));

while ($deal = $kiwisaver_deals->fetch_assoc()) {
    $data->kiwisavers_data->quarters[3]->deals_data[] = $deal;
}

$kiwisaver_deals = $dealController->GetKiwiSaversIssuedByTeamInDateRange($data->year . "0101", $data->year . "1231");

while ($deal = $kiwisaver_deals->fetch_assoc()) {
    $m1 = date('m', strtotime($deal["issue_date"]));
    $data->kiwisavers_data->months[($m1 - 1)]->deals++;
    $data->kiwisavers_data->months[($m1 - 1)]->api += $deal["commission"];
    $data->kiwisavers_data->months[($m1 - 1)]->deals_data[] = $deal;
}

//Telemarketers Data
$data->telemarketers_kiwisaver_data = new YearData();

//BDM's Data
$data->bdms_kiwisaver_data = new YearData();

//Self Gen's Data
$data->self_gen_kiwisaver_data = new YearData();


//First Quarter
$kiwisaver_deals = $dealController->GetCompanyCumulativeKiwiSaverFromLeadGeneratorsInRange("Face-to-Face Marketer", $data->first_quarter->from->format("Ymd"), $data->first_quarter->to->format("Ymd"));

$data->bdms_kiwisaver_data->quarters[0]->quantity += $kiwisaver_deals["total_deals"];
$data->bdms_kiwisaver_data->quarters[0]->api += $kiwisaver_deals["total_commission"];

//Second Quarter
$kiwisaver_deals = $dealController->GetCompanyCumulativeKiwiSaverFromLeadGeneratorsInRange("Face-to-Face Marketer", $data->second_quarter->from->format("Ymd"), $data->second_quarter->to->format("Ymd"));

$data->bdms_kiwisaver_data->quarters[1]->quantity+= $kiwisaver_deals["total_deals"];
$data->bdms_kiwisaver_data->quarters[1]->api += $kiwisaver_deals["total_commission"];

//Third Quarter
$kiwisaver_deals = $dealController->GetCompanyCumulativeKiwiSaverFromLeadGeneratorsInRange("Face-to-Face Marketer", $data->third_quarter->from->format("Ymd"), $data->third_quarter->to->format("Ymd"));

$data->bdms_kiwisaver_data->quarters[2]->quantity+= $kiwisaver_deals["total_deals"];
$data->bdms_kiwisaver_data->quarters[2]->api += $kiwisaver_deals["total_commission"];

//Fourth Quarter
$kiwisaver_deals = $dealController->GetCompanyCumulativeKiwiSaverFromLeadGeneratorsInRange("Face-to-Face Marketer", $data->fourth_quarter->from->format("Ymd"), $data->fourth_quarter->to->format("Ymd"));

$data->bdms_kiwisaver_data->quarters[3]->quantity+= $kiwisaver_deals["total_deals"];
$data->bdms_kiwisaver_data->quarters[3]->api += $kiwisaver_deals["total_commission"];


//First Quarter
$kiwisaver_deals = $dealController->GetCompanyCumulativeKiwiSaverFromLeadGeneratorsInRange("Telemarketer", $data->first_quarter->from->format("Ymd"), $data->first_quarter->to->format("Ymd"));

$data->telemarketers_kiwisaver_data->quarters[0]->quantity+= $kiwisaver_deals["total_deals"];
$data->telemarketers_kiwisaver_data->quarters[0]->api += $kiwisaver_deals["total_commission"];

//Second Quarter
$kiwisaver_deals = $dealController->GetCompanyCumulativeKiwiSaverFromLeadGeneratorsInRange("Telemarketer", $data->second_quarter->from->format("Ymd"), $data->second_quarter->to->format("Ymd"));

$data->telemarketers_kiwisaver_data->quarters[1]->quantity+= $kiwisaver_deals["total_deals"];
$data->telemarketers_kiwisaver_data->quarters[1]->api += $kiwisaver_deals["total_commission"];

//Third Quarter
$kiwisaver_deals = $dealController->GetCompanyCumulativeKiwiSaverFromLeadGeneratorsInRange("Telemarketer", $data->third_quarter->from->format("Ymd"), $data->third_quarter->to->format("Ymd"));

$data->telemarketers_kiwisaver_data->quarters[2]->quantity+= $kiwisaver_deals["total_deals"];
$data->telemarketers_kiwisaver_data->quarters[2]->api += $kiwisaver_deals["total_commission"];

//Fourth Quarter
$kiwisaver_deals = $dealController->GetCompanyCumulativeKiwiSaverFromLeadGeneratorsInRange("Telemarketer", $data->fourth_quarter->from->format("Ymd"), $data->fourth_quarter->to->format("Ymd"));

$data->telemarketers_kiwisaver_data->quarters[3]->quantity+= $kiwisaver_deals["total_deals"];
$data->telemarketers_kiwisaver_data->quarters[3]->api += $kiwisaver_deals["total_commission"];



//First Quarter
$kiwisaver_deals = $dealController->GetCompanyCumulativeKiwiSaverFromLeadGeneratorsInRange("Self-Generated", $data->first_quarter->from->format("Ymd"), $data->first_quarter->to->format("Ymd"));

$data->self_gen_kiwisaver_data->quarters[0]->quantity+= $kiwisaver_deals["total_deals"];
$data->self_gen_kiwisaver_data->quarters[0]->api += $kiwisaver_deals["total_commission"];

//Second Quarter
$kiwisaver_deals = $dealController->GetCompanyCumulativeKiwiSaverFromLeadGeneratorsInRange("Self-Generated", $data->second_quarter->from->format("Ymd"), $data->second_quarter->to->format("Ymd"));

$data->self_gen_kiwisaver_data->quarters[1]->quantity+= $kiwisaver_deals["total_deals"];
$data->self_gen_kiwisaver_data->quarters[1]->api += $kiwisaver_deals["total_commission"];

//Third Quarter
$kiwisaver_deals = $dealController->GetCompanyCumulativeKiwiSaverFromLeadGeneratorsInRange("Self-Generated", $data->third_quarter->from->format("Ymd"), $data->third_quarter->to->format("Ymd"));

$data->self_gen_kiwisaver_data->quarters[2]->quantity+= $kiwisaver_deals["total_deals"];
$data->self_gen_kiwisaver_data->quarters[2]->api += $kiwisaver_deals["total_commission"];

//Fourth Quarter
$kiwisaver_deals = $dealController->GetCompanyCumulativeKiwiSaverFromLeadGeneratorsInRange("Self-Generated", $data->fourth_quarter->from->format("Ymd"), $data->fourth_quarter->to->format("Ymd"));

$data->self_gen_kiwisaver_data->quarters[3]->quantity+= $kiwisaver_deals["total_deals"];
$data->self_gen_kiwisaver_data->quarters[3]->api += $kiwisaver_deals["total_commission"];


$today = date('Ymd');
$current_quarter = 0;

if ($today <= $data->second_quarter->to->format("Ymd") && $today >= $data->second_quarter->from->format("Ymd")) {
    $current_quarter = 1;
} elseif ($today <= $data->third_quarter->to->format("Ymd") && $today >= $data->third_quarter->from->format("Ymd")) {
    $current_quarter = 2;
} elseif ($today <= $data->fourth_quarter->to->format("Ymd") && $today >= $data->fourth_quarter->from->format("Ymd")) {
    $current_quarter = 3;
}

$curr_month = date('m');

$data->today = $today;
$data->current_quarter = $current_quarter;
$data->curr_month = $curr_month;