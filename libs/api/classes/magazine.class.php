<?php

if (file_exists("libs/api/controllers/Adviser.controller.php")) {
    require "libs/api/controllers/Adviser.controller.php";
} elseif (file_exists("controllers/Adviser.controller.php")) {
    require "controllers/Adviser.controller.php";
}

if (file_exists("libs/api/controllers/LeadGenerator.controller.php")) {
    require "libs/api/controllers/LeadGenerator.controller.php";
} elseif (file_exists("controllers/LeadGenerator.controller.php")) {
    require "controllers/LeadGenerator.controller.php";
}

if (file_exists("libs/indet_dates_helper.php")) {
    require_once "libs/indet_dates_helper.php";
} elseif (file_exists("../../indet_dates_helper.php")) {
    require_once "../../indet_dates_helper.php";
}

class Magazine extends Database
{
    public $date = "";
    public $announcement = "";
    public $quote = "";
    public $message = "";
    public $issue_number = "";
    public $issue_number_line_2 = "";

    public $cumulativeRange = "";
    public $actualCumulativeRange = "";

    public $bimonthRange = "";
    public $quarterRange = "";
    public $quarterTitle = "";
    public $currentBiMonthRange = "";

    public $pages = [];
    public $last_page_index = 1;
    public $photos = [];

    public $bi_monthly_advisers = [];
    public $cumulative_advisers = [];

    public $rba_cumulative_advisers = [];

    public $bi_monthly_bdms = [];
    public $cumulative_bdms = [];

    public $bi_monthly_advisers_kiwisavers = [];
    public $cumulative_advisers_kiwisavers = [];

    public $bdm_performances = [];
    public $bdm_ks_performances = [];

    public $tm_performances = [];
    public $tm_ks_performances = [];

    public $winner_score = [];
    public $all_winner_score = [];

    public $records_to_beat = [];
    public $new_faces = [];
    public $upcoming_birthdays = [];
    public $upcoming_work_anniversaries = [];

    public $featured_bi_monthly_adviser_id = "";
    public $adviserController = null;

    /**
    @desc: init the class
     */
    public function __construct($date, $announcement = "", $quote = "", $message = "", $photos = [])
    {
        //initialize database connection
        parent::__construct();

        $this->adviserController = new AdviserController();
        $this->leadGeneratorController = new LeadGeneratorController();

        $this->date = $date;
        $this->announcement = $announcement;
        $this->quote = mb_convert_encoding($quote, "HTML-ENTITIES", "UTF-8");
        $this->message = $message;
        $this->photos = $photos;

        $this->bimonthRange = $this->getBiMonthlyRange($date);
        $this->cumulativeRange = $this->getCumulativeRange($date);
        $this->actualCumulativeRange = $this->getActualCumulativeRange($date);
        $this->quarterTitle = "";
        $this->quarterRange = $this->getQuarterRange($date);
        
        $this->currentBiMonthRange = $this->getCurrentBiMonthlyRange($date);
        $this->issue_number = $this->getIssueFromDate($this->bimonthRange->from, $this->actualCumulativeRange);
        $this->issue_number_line_2 = $this->getIssueLine2FromDate($this->bimonthRange);

        $this->bi_monthly_advisers = $this->GetBiMonthlyAdvisers();
        $this->bi_monthly_advisers = $this->CheckRows($this->bi_monthly_advisers);

        $this->bi_monthly_bdms = $this->GetBiMonthlyBDMs();
        // $this->bi_monthly_bdms = $this->GetBiMonthlyBDMs($this->bi_monthly_bdms);

        $this->cumulative_advisers = $this->GetCumulativeAdvisers();
        $this->cumulative_advisers = $this->CheckRows($this->cumulative_advisers);

        $this->rba_cumulative_advisers = $this->GetCumulativeRBAAdvisers();
        $this->rba_cumulative_advisers = $this->CheckRows($this->rba_cumulative_advisers);

        $this->cumulative_bdms = $this->CumulativeBDMs();
        $this->cumulative_bdms = $this->CheckRows($this->cumulative_bdms);

        $this->bi_monthly_advisers_kiwisavers = $this->GetBiMonthlyAdvisersKiwiSavers();
        $this->cumulative_advisers_kiwisavers = $this->GetCumulativeAdvisersKiwiSavers();

        $this->bdm_performances = $this->GetCumulativeBDMs();
        $this->bdm_ks_performances = $this->GetCumulativeBDMsKS();

        //$this->tm_performances = $this->GetCumulativeTMs();
        $this->SetWinnerScore();
        $this->winner_score = $this->GetWinnerBiMonthlyAdvisers();
        $this->winner_score = $this->CheckRows($this->winner_score);

        $this->all_winner_score = $this->GetStringsWinnerScore();
        $this->winnerScore = $this->winnerScore();
        $this->winnerScore = $this->CheckRows($this->winnerScore);
        

        $this->records_to_beat = $this->GetRecordsToBeat();
        $this->new_faces = array_chunk($this->GetNewFaces(), 4);
        $this->upcoming_birthdays = array_chunk($this->GetBirthdays(), 3);

        $work_anniversaries = $this->GetWorkAnniversaries();

        /*Specifically include one adviser into the magazine
            $specific_adviser = [
                "name"        => "Sumit Monga",
                "image"       => "92904245143e65336d848e1dbd495e20.png",
                "role"        => "Financial Adviser",
                "date_hired"  => "20160301",
                "anniversary" => "1st March",
                "years"       => 4
            ];

            if(is_array($work_anniversaries)){
                if(count($work_anniversaries) > 0){
                    array_unshift($work_anniversaries, $specific_adviser);
                }
                else{
                    $work_anniversaries[] = $specific_adviser;
                }
            }
            else {
                $work_anniversaries = $specific_adviser;
            }

        */

            
            $this->upcoming_work_anniversaries = array_chunk($work_anniversaries, 3);

        //Stress test
        //$this->DuplicateArray($this->bi_monthly_advisers, 8);
        //$this->DuplicateArray($this->cumulative_advisers, 8);

        //Announcements
            if (!empty($this->message) > 0) {
                $this->last_page_index++;
            }

        //Bi-Monthly
            if (count($this->bi_monthly_advisers)) {
                $bm_pages = $this->PageCounter($this->bi_monthly_advisers, 10, 24);
                $this->PushToPages("Bi-Monthly API", "Page {$this->getPages($bm_pages)}");
            }

        //Cumulative
            if (count($this->cumulative_advisers)) {
                $cumulative_pages = $this->PageCounter($this->cumulative_advisers, 10, 24);
                $this->PushToPages("Cumulative API", "Page {$this->getPages($cumulative_pages)}");
            }

        //Bi-Monthly
            if (count($this->bi_monthly_advisers_kiwisavers)) {
                $bi_monthly_advisers_kiwisavers = $this->PageCounter($this->bi_monthly_advisers_kiwisavers, 10, 24);
                $this->PushToPages("Bi-Monthly KiwiSaver", "Page {$this->getPages($bi_monthly_advisers_kiwisavers)}");
            }

        //Cumulative
            if (count($this->cumulative_advisers_kiwisavers)) {
                $cumulative_advisers_kiwisavers = $this->PageCounter($this->cumulative_advisers_kiwisavers, 10, 24);
                $this->PushToPages("Cumulative KiwiSaver", "Page {$this->getPages($cumulative_advisers_kiwisavers)}");
            }

        //BDM
            // if (count($this->bdm_performances) > 0) {
            //     $bdm_performances = $this->PageCounter($this->bdm_performances, 9, 24);
            //     $this->PushToPages("BDM Cumulative Performance", "Page {$this->getPages($bdm_performances)}");
            // }

        //Bi-Monthly Winner
            if (count($this->winner_score) > 0) {
                $winner_score = $this->PageCounter($this->winner_score, 9, 24);
                $this->PushToPages("Bi Monthly Winners", "Page {$this->getPages($winner_score)}");
            }
        //Winner Score
            if (count($this->all_winner_score) > 0) {
                $all_winner_score = $this->PageCounter($this->all_winner_score, 9, 24);
                $this->PushToPages("Winner Strings", "Page {$this->getPages($all_winner_score)}");
            }
        //BDM KS
            if (count($this->bdm_ks_performances) > 0) {
                $bdm_ks_performances = $this->PageCounter($this->bdm_ks_performances, 10, 24);
                $this->PushToPages("BDM KiwiSavers Performance", "Page {$this->getPages($bdm_ks_performances)}");
            }



        //TM
            if (count($this->tm_performances) > 0) {
                $tm_performances = $this->PageCounter($this->tm_performances, 9, 24);
                $this->PushToPages("TM Cumulative Performance", "Page {$this->getPages($tm_performances)}");
            }


        //Records
            if (count($this->records_to_beat) > 0) {
                $this->last_page_index++;
                $this->PushToPages("Records to Break", "Page {$this->last_page_index}");
            }

        //New Faces
            if (count($this->new_faces) > 0) {
                $this->PushToPages("New Faces", "Page " . $this->getPages(count($this->new_faces)));
            }

        //Birthdays
            if (count($this->upcoming_birthdays) > 0) {
                $this->PushToPages("Birthdays", "Page " . $this->getPages(count($this->upcoming_birthdays)));
            }

        //Work Anniversaries
            if (count($this->upcoming_work_anniversaries) > 0) {
                $this->PushToPages("Work Anniversaries", "Page " . $this->getPages(count($this->upcoming_work_anniversaries)));
            }

        //Announcements
            if (!empty($this->announcement) > 0) {
                $this->last_page_index++;
                $this->PushToPages("Announcements", "Page {$this->last_page_index}");
            }

            if (count($this->photos) > 0) {
                $this->last_page_index++;
                $this->PushToPages("Photos", "Page {$this->last_page_index}");
            }
        }

    //Return empty if the only rows left are others
        

        public function CheckRows ($rows){
            if(count($rows) == 1){
                if($rows[0]["name"] == "Others"){
                    return [];
                }
                else{
                    return $rows;
                }
            }
            else{
                return $rows;
            }

            return $rows;
        }

        public function PushToPages($page_title, $pages)
        {

            $this->pages[] = [
                "title" => $page_title,
                "page" => $pages,
                "page_start" => $this->GetFirstPage($pages),
                "page_debugger" => $pages,
            ];
        }

        public function GetFirstPage($pages)
        {
            $pages = str_replace("Page ", "", $pages);

            $output = $pages;

            if (strpos($pages, '-') !== false) {
                $output = explode("-", $pages)[0];
            }

            return $output;
        }

    /**
     * @desc: Stress test hell
     * @param:
     *     Array - Array to duplicate
     *     Int - The number of times you want to duplicate the array
     */
    public function DuplicateArray(&$array, $times = 1)
    {
        for ($i = 0; $i < $times; $i++) {
            $array = array_merge($array, $array);
        }
    }

    public function PageCounter($array, $initial_limit, $limit_per_page)
    {
        if (count($array) <= $initial_limit) {
            return 1;
        } else {
            $rows = count($array) - $initial_limit;
            return ceil($rows / $limit_per_page) + 1;
        }
    }

    public function getPages($pages = 1)
    {
        $output = $this->last_page_index + 1;

        if ($pages > 1) {
            $output .= "-" . ($this->last_page_index + $pages);
        }

        $this->last_page_index += $pages;

        return $output;
    }

    public function getIssueString($issue)
    {
        $issue_array = explode("_", $issue);
        foreach ($issue_array as $value) {
            $value = ucfirst($value);
        }

        return $issue_array[0] . " " . $issue_array[1] . " - " . $issue_array[2] . " " . $issue_array[3];
    }

    public function getIssueFromDate($date, $cumulativeRange)
    {
        $first_volume_year = "2017";

        $year = date("Y", strtotime($date));
        $month = date("m", strtotime($date));
        $day = date("d", strtotime($date));

        $volume = ($year - $first_volume_year) * 2;
        if ($month > 6) {
            $volume++;
            $month -= 6;
        }
        
        $issue = ($month - 1) * 2;

        if ($day >= 16) {
            $issue += 2;
        } else {
            $issue++;
        }

        return "Volume $volume: " . date("jS F", strtotime($cumulativeRange->from)) . " - " . date("jS F", strtotime($cumulativeRange->to));
    }


    public function getIssueLine2FromDate($bimonthRange)
    {
        return "Issue Period: " . date("jS F", strtotime($bimonthRange->from)) . " - " . date("jS F", strtotime($bimonthRange->to));
    }

    public function getCumulativeRange($date)
    {
        $output = new stdClass();

        $year = date("Y", strtotime($date));
        $month = date("m", strtotime($date));
        $day = date("d", strtotime($date));

        if ($month > 6) {
            if ($day < 16 && $month == "07") {
                $output->from = $year . "0101";
            } else {
                $output->from = $year . "0701";
            }
        } else {
            if ($day < 16 && $month == "01") {
                $year -= 1;
                $output->from = $year . "0701";
            } else {
                $output->from = $year . "0101";
            }
        }

        $output->to = $this->bimonthRange->to;
        
        return $output;
    }

    public function getActualCumulativeRange($date)
    {
        $output = new stdClass();

        $year = date("Y", strtotime($date));
        $month = date("m", strtotime($date));
        $day = date("d", strtotime($date));
        if ($month > 6) {
            if ($day < 16 && $month == "07") {
                $output->from = $year . "0101";
                $output->to = $year . "0630";
            } else {
                $output->from = $year . "0701";
                $output->to = $year . "1231";
            }
        } else {
            if ($day < 16 && $month == "01") {
                $year -= 1;
                $output->from = $year . "0701";
                $output->to = $year . "1231";
            } else {
                $output->from = $year . "0101";
                $output->to = $year . "0630";
            }
        }
        return $output;
    }

    public function getQuarterRange($date)
    {
        $output = new stdClass();
        $date_helper = new INDET_DATES_HELPER();

        $day = date("d", strtotime($date));
        $start_date = "";

        if ($day >= 16) {
            $start_date = date("Ym", strtotime($date)) . "01";
        } else {
            $start_date = date('Ym', strtotime('last day of last month', strtotime($date))) . "16";
        }

        $year = date("Y", strtotime($start_date));

        $quarters = [];
        $quarters[] = $date_helper->GetQuarter("First", $year);
        $quarters[] = $date_helper->GetQuarter("Second", $year);
        $quarters[] = $date_helper->GetQuarter("Third", $year);
        $quarters[] = $date_helper->GetQuarter("Fourth", $year);

        foreach($quarters as $q => $quarter){
            $from = $quarter->from->format('Ymd');
            $to = $quarter->to->format('Ymd');
            $start_date = $start_date;
            if($start_date >= $from && $start_date <= $to){
                $output->from = $from;
                $output->to = $to;
                
                $this->quarterTitle = "Q" . ($q + 1) . " " . $year;
                break;
            }
        }

        $output->to = $this->bimonthRange->to;

        return $output;
    }

    public function getBiMonthlyRange($date)
    {
        $output = new stdClass();

        $year = date("Y", strtotime($date));
        $month = date("m", strtotime($date));
        $day = date("d", strtotime($date));

        if ($day >= 16) {
            $output->from = date("Ym", strtotime($date)) . "01";
            $output->to = date("Ym", strtotime($date)) . "15";
        } else {
            $output->from = date('Ym', strtotime('last day of last month', strtotime($date))) . "16";
            $output->to = date('Ymd', strtotime('last day of last month', strtotime($date)));
        }
        
        return $output;
    }

    public function getCurrentCumulativeRange($date)
    {
        $output = new stdClass();

        $year = date("Y", strtotime($date));
        $month = date("m", strtotime($date));

        if ($month > 6) {
            if ($day < 16 && $month == "07") {
                $output->from = $year . "0101";
                $output->to = $year . "0630";
            } else {
                $output->from = $year . "0701";
                $output->to = $year . "1231";
            }
        } else {
            if ($day < 16 && $month == "01") {
                $year -= 1;
                $output->from = $year . "0701";
                $output->to = $year . "1231";
            } else {
                $output->from = $year . "0101";
                $output->to = $year . "0630";
            }
        }

        if ($date < $output->to) {
            $output->to = $date;
        }

        return $output;
    }

    public function getCurrentBiMonthlyRange($date)
    {
        $output = new stdClass();

        $year = date("Y", strtotime($date));
        $month = date("m", strtotime($date));
        $day = date("d", strtotime($date));

        $output->from = date("Ymd", strtotime($date));

        if ($day == 16) {
            $output->to = date("Ymt", strtotime($date));
        } else {
            $output->to = $year . $month . "15";
        }

        return $output;
    }

    public function getNextBiMonthlyRange($date)
    {
        $output = new stdClass();

        $year = date("Y", strtotime($date));
        $month = date("m", strtotime($date));
        $day = date("d", strtotime($date));

        $output->from = date("Ymd", strtotime($date));

        if ($day == 16) {
            $output->to = date("Ymt", strtotime($date));
        } else {
            $output->to = $year . $month . "15";
        }

        return $output;
    }

    public function GetBiMonthlyAdvisers()
    {
        $output = [];
        $activeAdvisers = [];
        $otherAdvisers = [];
        $dataset = $this->adviserController->getActiveAdvisers();

        $others = [];
        $others["name"] = "Others";
        $others["issued_api"] = 0;
        $others["deals"] = 0;
        $output["Others"] = $others;

        while ($row = $dataset->fetch_assoc()) {
            $activeAdvisers[] = $row["id"];
            if($row["team"] == "")
                $row["team"] = "None";


            if($row["name"] == "Sumit Monga"){
                $otherAdvisers[] = $row["id"];
            }
            else{
                $output[$row["id"]] = $row;
                $output[$row["id"]]["issued_api"] = 0;
                $output[$row["id"]]["deals"] = 0;
            }
        }

        //Register inactive advisers
        $dataset = $this->adviserController->getExAdvisers();
        while ($row = $dataset->fetch_assoc()) {
            $otherAdvisers[] = $row["id"];
        }

        //Active Advisers deal fetching
        $advisersArrayString = implode(",", $activeAdvisers);

        $query = "SELECT c.id as client_id, a.name as adviser_name, a.id as adviser_id, s.deals as deals FROM issued_clients_tbl i LEFT JOIN submission_clients s ON s.client_id = i.name LEFT JOIN adviser_tbl a ON i.assigned_to = a.id LEFT JOIN clients_tbl c ON i.name = c.id  WHERE i.assigned_to IN ($advisersArrayString) order by a.name";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        while ($row = $dataset->fetch_assoc()) {
            $deals = json_decode($row["deals"], true);
            $total_issued_api = 0;
            $total_issued_deals = 0;

            if ($deals == null) {
                continue;
            }

            foreach ($deals as $deal) {
                if (isset($deal["status"])) {
                    if ($deal["status"] == "Issued") {

                        //Check if cancelled
                        if (isset($deal["clawback_status"])) {
                            if ($deal["clawback_status"] == "Cancelled") {
                                continue;
                            }
                        }

                        //
                        if ($this->WithinDateRange($deal["date_issued"], $this->bimonthRange)) {
                            $total_issued_api += $deal["issued_api"];
                            $total_issued_deals++;
                        }
                    }
                }
            }
            
            if($row["adviser_name"] != "Sumit Monga"){
                $output[$row["adviser_id"]]["issued_api"] += floatval($total_issued_api);
                $output[$row["adviser_id"]]["deals"] += floatval($total_issued_deals);
            }
        }

        //Ex advisers deals fetching
        if ($otherAdvisers) {

            $otherAdvisersArrayString = implode(",", $otherAdvisers);

            $query = "SELECT c.id as client_id, a.id as adviser_id, s.deals as deals FROM issued_clients_tbl i LEFT JOIN submission_clients s ON s.client_id = i.name LEFT JOIN adviser_tbl a ON i.assigned_to = a.id LEFT JOIN clients_tbl c ON i.name = c.id  WHERE i.assigned_to IN ($otherAdvisersArrayString) order by a.name";
            $statement = $this->prepare($query);
            $dataset = $this->execute($statement);

            while ($row = $dataset->fetch_assoc()) {
                $deals = json_decode($row["deals"], true);
                $total_issued_api = 0;
                $total_issued_deals = 0;
                if ($row["client_id"] == "55") {
                }
                if ($deals == null) {
                    continue;
                }

                foreach ($deals as $deal) {
                    if (isset($deal["status"])) {
                        if ($deal["status"] == "Issued") {

                            //Check if cancelled
                            if (isset($deal["clawback_status"])) {
                                if ($deal["clawback_status"] == "Cancelled") {
                                    continue;
                                }
                            }

                            //
                            if ($this->WithinDateRange($deal["date_issued"], $this->bimonthRange)) {
                                $total_issued_api += $deal["issued_api"];
                                $total_issued_deals++;
                            }
                        }
                    }
                }

                $output["Others"]["issued_api"] += floatval($total_issued_api);
                $output["Others"]["deals"] += floatval($total_issued_deals);
            }
        }

        $issued_apis = array_column($output, 'issued_api');
        
        array_multisort($issued_apis, SORT_DESC, $output);

        $key = array_search('Others', array_column($output, 'name')); // $key = 2;

        $this->moveElement($output, $key, count($output) - 1);

        return $this->FilterOutput($output, "deals");
    }

    public function GetWinnerBiMonthlyAdvisers()
    {
        $output = [];
        $activeAdvisers = [];
        $otherAdvisers = [];
        $dataset = $this->adviserController->getActiveAdvisers();

        $others = [];
        $others["name"] = "Others";
        $others["issued_api"] = 0;
        $others["deals"] = 0;
        $output["Others"] = $others;

        while ($row = $dataset->fetch_assoc()) {
            $activeAdvisers[] = $row["id"];
            if($row["team"] == "")
                $row["team"] = "None";


            if($row["name"] == "Sumit Monga"){
                $otherAdvisers[] = $row["id"];
            }
            else{
                $output[$row["id"]] = $row;
                $output[$row["id"]]["issued_api"] = 0;
                $output[$row["id"]]["deals"] = 0;
            }
        }

        //Register inactive advisers
        $dataset = $this->adviserController->getExAdvisers();
        while ($row = $dataset->fetch_assoc()) {
            $otherAdvisers[] = $row["id"];
        }

        //Active Advisers deal fetching
        $advisersArrayString = implode(",", $activeAdvisers);

        // $query = "SELECT c.id as client_id, a.name as adviser_name, a.id as adviser_id, s.deals as deals FROM issued_clients_tbl i LEFT JOIN submission_clients s ON s.client_id = i.name LEFT JOIN adviser_tbl a ON i.assigned_to = a.id LEFT JOIN clients_tbl c ON i.name = c.id  WHERE i.assigned_to IN ($advisersArrayString) order by a.name";
        // var_dump($advisersArrayString);die;
        $query = "SELECT c.id as client_id, a.name as adviser_name, a.id as adviser_id, s.deals as deals, w.score as scores, w.bimonthly_range as current_bimonthly_date FROM issued_clients_tbl i LEFT JOIN submission_clients s ON s.client_id = i.name LEFT JOIN adviser_tbl a ON i.assigned_to = a.id LEFT JOIN winner_score w ON a.id = w.adviser_id LEFT JOIN clients_tbl c ON i.name = c.id  WHERE i.assigned_to IN ($advisersArrayString) order by a.name";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        while ($row = $dataset->fetch_assoc()) {
            $deals = json_decode($row["deals"], true);
            $total_issued_api = 0;
            $total_issued_deals = 0;

            if ($deals == null) {
                continue;
            }

            foreach ($deals as $deal) {
                if (isset($deal["status"])) {
                    if ($deal["status"] == "Issued") {

                        //Check if cancelled
                        if (isset($deal["clawback_status"])) {
                            if ($deal["clawback_status"] == "Cancelled") {
                                continue;
                            }
                        }

                        //
                        if ($this->WithinDateRange($deal["date_issued"], $this->bimonthRange)) {
                            $total_issued_api += $deal["issued_api"];
                            $total_issued_deals++;
                            
                        }
                    }
                }
            }
            
            if($row["adviser_name"] != "Sumit Monga"){
                $output[$row["adviser_id"]]["issued_api"] += floatval($total_issued_api);
                $output[$row["adviser_id"]]["deals"] += floatval($total_issued_deals);
                $output[$row["adviser_id"]]["current_bimonthly_date"] = $row["current_bimonthly_date"];
                $output[$row["adviser_id"]]["scores"] = $row["scores"];
            }
        }

        //Ex advisers deals fetching
        if ($otherAdvisers) {

            $otherAdvisersArrayString = implode(",", $otherAdvisers);

            $query = "SELECT c.id as client_id, a.id as adviser_id, s.deals as deals FROM issued_clients_tbl i LEFT JOIN submission_clients s ON s.client_id = i.name LEFT JOIN adviser_tbl a ON i.assigned_to = a.id LEFT JOIN clients_tbl c ON i.name = c.id  WHERE i.assigned_to IN ($otherAdvisersArrayString) order by a.name";
            $statement = $this->prepare($query);
            $dataset = $this->execute($statement);

            while ($row = $dataset->fetch_assoc()) {
                $deals = json_decode($row["deals"], true);
                $total_issued_api = 0;
                $total_issued_deals = 0;
                if ($row["client_id"] == "55") {
                }
                if ($deals == null) {
                    continue;
                }

                foreach ($deals as $deal) {
                    if (isset($deal["status"])) {
                        if ($deal["status"] == "Issued") {

                            //Check if cancelled
                            if (isset($deal["clawback_status"])) {
                                if ($deal["clawback_status"] == "Cancelled") {
                                    continue;
                                }
                            }

                            //
                            if ($this->WithinDateRange($deal["date_issued"], $this->bimonthRange)) {
                                $total_issued_api += $deal["issued_api"];
                                $total_issued_deals++;
                            }
                        }
                    }
                }

                $output["Others"]["issued_api"] += floatval($total_issued_api);
                $output["Others"]["deals"] += floatval($total_issued_deals);
            }
        }

        // $issued_apis = array_column($output, 'issued_api');
        $deals_winner = array_column($output, 'deals');
        
        array_multisort($deals_winner, SORT_DESC, $output);

        $key = array_search('Others', array_column($output, 'name')); // $key = 2;

        $this->moveElement($output, $key, count($output) - 1);

        return $this->FilterOutput($output, "deals");
    }

    public function GetCumulativeAdvisers()
    {
        $output = [];
        $activeAdvisers = [];
        $otherAdvisers = [];
        $dataset = $this->adviserController->getActiveAdvisers();


        $others = [];
        $others["name"] = "Others";
        $others["issued_api"] = 0;
        $others["deals"] = 0;
        $others["team"] = "Ex-Advisers";
        $output["Others"] = $others;

        //Get Active
        while ($row = $dataset->fetch_assoc()) {
            $activeAdvisers[] = $row["id"];
            if($row["team"] == "")
                $row["team"] = "None";

            if($row["name"] == "Sumit Monga"){
                $otherAdvisers[] = $row["id"];
            }
            else{
                $output[$row["id"]] = $row;
                $output[$row["id"]]["issued_api"] = 0;
                $output[$row["id"]]["deals"] = 0;
            }
        }

        //Register inactive advisers
        $dataset = $this->adviserController->getExAdvisers();

        //Get Others
        while ($row = $dataset->fetch_assoc()) {
            $otherAdvisers[] = $row["id"];
        }

        //Active Advisers deal fetching
        $advisersArrayString = implode(",", $activeAdvisers);

        $query = "SELECT c.id as client_id, a.name as adviser_name, a.id as adviser_id, s.deals as deals FROM issued_clients_tbl i LEFT JOIN submission_clients s ON s.client_id = i.name LEFT JOIN adviser_tbl a ON i.assigned_to = a.id LEFT JOIN clients_tbl c ON i.name = c.id  WHERE i.assigned_to IN ($advisersArrayString) order by a.name";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        while ($row = $dataset->fetch_assoc()) {
            $deals = json_decode($row["deals"], true);
            $total_issued_api = 0;
            $total_issued_deals = 0;

            if ($deals == null) {
                continue;
            }

            foreach ($deals as $deal) {
                if (isset($deal["status"])) {
                    if ($deal["status"] == "Issued") {
                        //Check if cancelled
                        if (isset($deal["clawback_status"])) {
                            if ($deal["clawback_status"] == "Cancelled") {
                                continue;
                            }
                        }

                        //
                        if ($this->WithinDateRange($deal["date_issued"], $this->cumulativeRange)) {
                            $total_issued_api += $deal["issued_api"];
                            $total_issued_deals++;
                        }

                    }
                }
            }

            if($row["adviser_name"] != "Sumit Monga"){
                $output[$row["adviser_id"]]["issued_api"] += floatval($total_issued_api);
                $output[$row["adviser_id"]]["deals"] += floatval($total_issued_deals);
            }
        }

        //Ex advisers deals fetching
        if (count($otherAdvisers) > 0) {

            $otherAdvisersArrayString = implode(",", $otherAdvisers);

            $query = "SELECT c.id as client_id, a.id as adviser_id, s.deals as deals FROM issued_clients_tbl i LEFT JOIN submission_clients s ON s.client_id = i.name LEFT JOIN adviser_tbl a ON i.assigned_to = a.id LEFT JOIN clients_tbl c ON i.name = c.id  WHERE i.assigned_to IN ($otherAdvisersArrayString) order by a.name";
            $statement = $this->prepare($query);
            $dataset = $this->execute($statement);

            while ($row = $dataset->fetch_assoc()) {
                $deals = json_decode($row["deals"], true);
                $total_issued_api = 0;
                $total_issued_deals = 0;
                if ($row["client_id"] == "55") {
                }
                if ($deals == null) {
                    continue;
                }

                foreach ($deals as $deal) {
                    if (isset($deal["status"])) {
                        if ($deal["status"] == "Issued") {

                            //Check if cancelled
                            if (isset($deal["clawback_status"])) {
                                if ($deal["clawback_status"] == "Cancelled") {
                                    continue;
                                }
                            }

                            //
                            if ($this->WithinDateRange($deal["date_issued"], $this->cumulativeRange)) {
                                $total_issued_api += $deal["issued_api"];
                                $total_issued_deals++;
                            }
                        }
                    }
                }

                $output["Others"]["issued_api"] += floatval($total_issued_api);
                $output["Others"]["deals"] += floatval($total_issued_deals);
            }
        }

        $issued_apis = array_column($output, 'issued_api');

        array_multisort($issued_apis, SORT_DESC, $output);

        $key = array_search('Others', array_column($output, 'name')); // $key = 2;

        $this->moveElement($output, $key, count($output) - 1);
        return $this->FilterOutput($output, "deals");
    }

    public function GetCumulativeRBAAdvisers()
    {
        $output = [];
        $activeAdvisers = [];
        $otherAdvisers = [];
        $dataset = $this->adviserController->getActiveAdvisers();


        $others = [];
        $others["name"] = "Others";
        $others["issued_api"] = 0;
        $others["deals"] = 0;
        $others["rba"] = 0;
        $others["percent_rba"] = 0;
        $others["team"] = "Ex-Advisers";
        $output["Others"] = $others;

        //Get Active
        while ($row = $dataset->fetch_assoc()) {
            $activeAdvisers[] = $row["id"];
            if($row["team"] == "")
                $row["team"] = "None";

            if($row["name"] == "Sumit Monga"){
                $otherAdvisers[] = $row["id"];
            }
            else{
                $output[$row["id"]] = $row;
                $output[$row["id"]]["issued_api"] = 0;
                $output[$row["id"]]["deals"] = 0;
                $output[$row["id"]]["rba"] = 0;
            }
        }

        //Register inactive advisers
        $dataset = $this->adviserController->getExAdvisers();

        //Get Others
        while ($row = $dataset->fetch_assoc()) {
            $otherAdvisers[] = $row["id"];
        }

        //Active Advisers deal fetching
        $advisersArrayString = implode(",", $activeAdvisers);

        $query = "SELECT c.id as client_id, a.name as adviser_name, a.id as adviser_id, s.deals as deals FROM issued_clients_tbl i LEFT JOIN submission_clients s ON s.client_id = i.name LEFT JOIN adviser_tbl a ON i.assigned_to = a.id LEFT JOIN clients_tbl c ON i.name = c.id  WHERE i.assigned_to IN ($advisersArrayString) order by a.name";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        while ($row = $dataset->fetch_assoc()) {
            
            $deals = json_decode($row["deals"], true);
            $total_issued_api = 0;
            $total_issued_deals = 0;
            $total_rba = 0;

            if ($deals == null) {
                continue;
            }

            foreach ($deals as $deal) {
                
                if (isset($deal["status"])) {
                    if ($deal["status"] == "Issued") {
                        // var_dump($deal);
                        //Check if cancelled
                        if (isset($deal["clawback_status"])) {
                            if ($deal["clawback_status"] == "Cancelled") {
                                continue;
                            }
                        }

                        //
                        if ($this->WithinDateRange($deal["date_issued"], $this->cumulativeRange)) {
                            $total_issued_api += $deal["issued_api"];
                            $total_issued_deals++;
                            if(isset($deal["replacement_business"])){
                                if($deal["replacement_business"] == "1"){
                                    $total_rba++;
                                }
                            }
                        }

                    }
                }
            }

            if($row["adviser_name"] != "Sumit Monga"){
                $output[$row["adviser_id"]]["issued_api"] += floatval($total_issued_api);
                $output[$row["adviser_id"]]["deals"] += floatval($total_issued_deals);
                $output[$row["adviser_id"]]["rba"] += floatval($total_rba);        
            }

        }


        //Ex advisers deals fetching
        if (count($otherAdvisers) > 0) {

            $otherAdvisersArrayString = implode(",", $otherAdvisers);

            $query = "SELECT c.id as client_id, a.id as adviser_id, s.deals as deals FROM issued_clients_tbl i LEFT JOIN submission_clients s ON s.client_id = i.name LEFT JOIN adviser_tbl a ON i.assigned_to = a.id LEFT JOIN clients_tbl c ON i.name = c.id  WHERE i.assigned_to IN ($otherAdvisersArrayString) order by a.name";
            $statement = $this->prepare($query);
            $dataset = $this->execute($statement);

            while ($row = $dataset->fetch_assoc()) {
                $deals = json_decode($row["deals"], true);
                $total_issued_api = 0;
                $total_issued_deals = 0;
                if ($row["client_id"] == "55") {
                }
                if ($deals == null) {
                    continue;
                }

                foreach ($deals as $deal) {
                    if (isset($deal["status"])) {
                        if ($deal["status"] == "Issued") {

                            //Check if cancelled
                            if (isset($deal["clawback_status"])) {
                                if ($deal["clawback_status"] == "Cancelled") {
                                    continue;
                                }
                            }

                            //
                            if ($this->WithinDateRange($deal["date_issued"], $this->cumulativeRange)) {
                                $total_issued_api += $deal["issued_api"];
                                $total_issued_deals++;
                            }
                        }
                    }
                }

                $output["Others"]["issued_api"] += floatval($total_issued_api);
                $output["Others"]["deals"] += floatval($total_issued_deals);
            }
        }

        foreach($output as $index => $data){
                if($data["deals"] < 5){
                    // var_dump($test["name"]);
                    unset($output[$index]);
                }
            // if($index != "Others"){
            // }
        }


        foreach($output as $index => $data){
            if($index != "Others"){
                $output[$data["id"]]["percent_rba"] = ($data["rba"]/$data["deals"])*100;
            }
        }

        $rba = array_column($output, 'percent_rba');

        array_multisort($rba, SORT_ASC, $output);

        $key = array_search('Others', array_column($output, 'name')); // $key = 2;

        $this->moveElement($output, $key, count($output) - 1);
        return $this->FilterOutput($output, "deals");
    }

    public function GetBiMonthlyAdvisersKiwiSavers()
    {
        $output = [];
        $activeAdvisers = [];
        $otherAdvisers = [];

        $from = $this->bimonthRange->from;
        $to = $this->bimonthRange->to;

        $dataset = $this->adviserController->getActiveAdvisers();

        $others = [];
        $others["name"] = "Others";
        $others["issued_api"] = 0;
        $others["deals"] = 0;
        $others["team"] = "Ex-Advisers";
        $output["Others"] = $others;

        while ($row = $dataset->fetch_assoc()) {
            $activeAdvisers[] = $row["id"];
            if($row["team"] == "")
                $row["team"] = "None";

            if($row["name"] == "Sumit Monga"){
                $otherAdvisers[] = $row["id"];
            }
            else{
                $output[$row["id"]] = $row;
                $output[$row["id"]]["issued_api"] = 0;
                $output[$row["id"]]["deals"] = 0;
            }
        }


        //Register inactive advisers
        $dataset = $this->adviserController->getExAdvisers();

        while ($row = $dataset->fetch_assoc()) {
            $otherAdvisers[] = $row["id"];
        }

        //Active Advisers deal fetching
        $advisersArrayString = implode(",", $activeAdvisers);

        $query = "SELECT a.name as name, a.id as adviser_id, COUNT(commission) as deals, SUM(commission) as total_commission, SUM(gst) as total_gst, SUM(balance) as total_balance FROM adviser_tbl a LEFT JOIN clients_tbl c ON c.assigned_to = a.id LEFT JOIN kiwisaver_profiles kp ON kp.client_id = c.id LEFT JOIN kiwisaver_deals kd ON kd.kiwisaver_profile_id = kp.id WHERE a.id IN ($advisersArrayString) AND kd.issue_date <= '$to' AND kd.issue_date >= '$from' AND kd.count = 'Yes' GROUP BY a.id";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        while ($row = $dataset->fetch_assoc()) {
            if ($row["deals"] > 0) {
                if($row["name"] != "Sumit Monga"){
                    $output[$row["adviser_id"]]["deals"] = $row["deals"];
                }
            }
        }

        //Ex advisers deals fetching
        if (count($otherAdvisers) > 0) {
            $otherAdvisersArrayString = implode(",", $otherAdvisers);
            $query = "SELECT 'Others' as name, 'Ex-Advisers' as team, COUNT(commission) as deals, SUM(commission) as total_commission, SUM(gst) as total_gst, SUM(balance) as total_balance FROM adviser_tbl a LEFT JOIN clients_tbl c ON c.assigned_to = a.id LEFT JOIN kiwisaver_profiles kp ON kp.client_id = c.id LEFT JOIN kiwisaver_deals kd ON kd.kiwisaver_profile_id = kp.id WHERE a.id IN ($otherAdvisersArrayString) AND kd.issue_date <= '$to' AND kd.issue_date >= '$from' AND kd.count = 'Yes' GROUP BY a.id";
            $statement = $this->prepare($query);
            $dataset = $this->execute($statement);

            // $output["Others"]["deals"] = $dataset->fetch_assoc()["deals"];
        }

        $deals = array_column($output, 'deals');

        array_multisort($deals, SORT_DESC, $output);

        $key = array_search('Others', array_column($output, 'name')); // $key = 2;

        $this->moveElement($output, $key, count($output) - 1);

        return $this->FilterOutput($output, "deals");
    }

    public function GetCumulativeAdvisersKiwiSavers()
    {
        $output = [];
        $activeAdvisers = [];
        $otherAdvisers = [];

        $from = $this->cumulativeRange->from;
        $to = $this->cumulativeRange->to;


        $others = [];
        $others["name"] = "Others";
        $others["deals"] = 0;
        $output["Others"] = $others;

        $dataset = $this->adviserController->getActiveAdvisers();
        
        while ($row = $dataset->fetch_assoc()) {
            $activeAdvisers[] = $row["id"];

            if($row["team"] == "")
                $row["team"] = "None";

            if($row["name"] == "Sumit Monga"){
                $otherAdvisers[] = $row["id"];
            }
            else{
                $output[$row["id"]] = $row;
                $output[$row["id"]]["issued_api"] = 0;
                $output[$row["id"]]["deals"] = 0;
            }
        }


        //Register inactive advisers
        $dataset = $this->adviserController->getExAdvisers();

        while ($row = $dataset->fetch_assoc()) {
            $otherAdvisers[] = $row["id"];
        }

        //Active Advisers deal fetching
        $advisersArrayString = implode(",", $activeAdvisers);

        $query = "SELECT a.name as name, a.id as adviser_id, COUNT(commission) as deals, SUM(commission) as total_commission, SUM(gst) as total_gst, SUM(balance) as total_balance FROM adviser_tbl a LEFT JOIN clients_tbl c ON c.assigned_to = a.id LEFT JOIN kiwisaver_profiles kp ON kp.client_id = c.id LEFT JOIN kiwisaver_deals kd ON kd.kiwisaver_profile_id = kp.id WHERE a.id IN ($advisersArrayString) AND kd.issue_date <= '$to' AND kd.issue_date >= '$from' AND kd.count = 'Yes' GROUP BY a.id";
        //var_dump($query);
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        while ($row = $dataset->fetch_assoc()) {
            if ($row["deals"] > 0) {
                if($row["name"] == "Sumit Monga"){
                    $output["Others"]["deals"] = $row["deals"];
                }
                else{
                    $output[$row["adviser_id"]]["deals"] = $row["deals"];
                }
            }

        }

        //Ex advisers deals fetching
        if (count($otherAdvisers) > 0) {
            $otherAdvisersArrayString = implode(",", $otherAdvisers);

            $query = "SELECT 'Others' as name, 'Ex-Advisers' as team, COUNT(commission) as deals, SUM(commission) as total_commission, SUM(gst) as total_gst, SUM(balance) as total_balance FROM adviser_tbl a LEFT JOIN clients_tbl c ON c.assigned_to = a.id LEFT JOIN kiwisaver_profiles kp ON kp.client_id = c.id LEFT JOIN kiwisaver_deals kd ON kd.kiwisaver_profile_id = kp.id WHERE a.id IN ($otherAdvisersArrayString) AND kd.issue_date <= '$to' AND kd.issue_date >= '$from' AND kd.count = 'Yes' GROUP BY a.id";
            $statement = $this->prepare($query);
            $dataset = $this->execute($statement);
            // $output["Others"]["deals"] = $dataset->fetch_assoc()["deals"];
        }

        $deals = array_column($output, 'deals');

        array_multisort($deals, SORT_DESC, $output);

        $key = array_search('Others', array_column($output, 'name')); // $key = 2;

        $this->moveElement($output, $key, count($output) - 1);

        return $this->FilterOutput($output, "deals");
    }

    public function GetCumulativeBDMs()
    {
        $output = [];
        $activeBDMs = [];
        $otherBDMs = [];
        $dataset = $this->leadGeneratorController->getActiveBDMsData($this->cumulativeRange->from, $this->cumulativeRange->to);
        // var_dump($this->cumulativeRange->from);
        // var_dump($this->cumulativeRange->to);die;
        $others = [];
        $others["name"] = "Others";
        $others["issued_api"] = 0;
        $others["deals"] = 0;
        $others["generated"] = 0;
        $others["cancelled"] = 0;
        $output["Others"] = $others;

        while ($row = $dataset->fetch_assoc()) {
            $activeBDMs[] = $row["id"];
            $output[$row["id"]] = $row;
            $output[$row["id"]]["issued_api"] = 0;
            $output[$row["id"]]["deals"] = 0;
        }
        
        $bdms_array = array_merge($activeBDMs, $otherBDMs);
        $bdmsArrayString = implode(",", $bdms_array);
        // var_dump($bdmsArrayString);die;
        $query = "SELECT c.id as client_id, l.name as leadgen_name, l.id as leadgen_id, s.deals as deals FROM issued_clients_tbl i LEFT JOIN submission_clients s ON s.client_id = i.name LEFT JOIN leadgen_tbl l ON i.leadgen = l.id LEFT JOIN clients_tbl c ON i.name = c.id  WHERE i.leadgen IN ($bdmsArrayString) order by l.name";

        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);
        while ($row = $dataset->fetch_assoc()) {
            $deals = json_decode($row["deals"], true);

            $total_issued_api = 0;
            $total_issued_deals = 0;

            if ($deals == null) {
                continue;
            }

            foreach ($deals as $deal) {
                if (isset($deal["status"])) {
                    if ($deal["status"] == "Issued") {

                        //Check if cancelled
                        if (isset($deal["clawback_status"])) {
                            if ($deal["clawback_status"] == "Cancelled") {
                                continue;
                            }
                        }

                        //
                        if ($this->WithinDateRange($deal["date_issued"], $this->cumulativeRange)) {
                            $total_issued_api += $deal["issued_api"];
                            $total_issued_deals++;
                        }
                    }
                }
            }

            $output[$row["leadgen_id"]]["issued_api"] += floatval($total_issued_api);
            $output[$row["leadgen_id"]]["deals"] += floatval($total_issued_deals);
            
        }

        $dataset = $this->leadGeneratorController->getInactiveBDMsData($this->cumulativeRange->from, $this->cumulativeRange->to);

        while ($row = $dataset->fetch_assoc()) {
            $otherBDMs[] = $row["id"];

            $output["Others"]["generated"] += $row["generated"];
            $output["Others"]["cancelled"] += $row["cancelled"];
        }

        $bdmsArrayString = implode(",", $otherBDMs);

        $query = "SELECT c.id as client_id, l.id as leadgen_id, s.deals as deals FROM issued_clients_tbl i LEFT JOIN submission_clients s ON s.client_id = i.name LEFT JOIN leadgen_tbl l ON i.leadgen = l.id LEFT JOIN clients_tbl c ON i.name = c.id  WHERE i.leadgen IN ($bdmsArrayString) order by l.name";

        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        while ($row = $dataset->fetch_assoc()) {
            $deals = json_decode($row["deals"], true);
            $total_issued_api = 0;
            $total_issued_deals = 0;

            if ($deals == null) {
                continue;
            }

            foreach ($deals as $deal) {
                if (isset($deal["status"])) {
                    if ($deal["status"] == "Issued") {

                        //Check if cancelled
                        if (isset($deal["clawback_status"])) {
                            if ($deal["clawback_status"] == "Cancelled") {
                                continue;
                            }
                        }

                        //
                        if ($this->WithinDateRange($deal["date_issued"], $this->cumulativeRange)) {
                            $total_issued_api += $deal["issued_api"];
                            $total_issued_deals++;
                        }
                    }
                }
            }

            $output["Others"]["issued_api"] += floatval($total_issued_api);
            $output["Others"]["deals"] += floatval($total_issued_deals);
        }

        //Lower priority to higher priority ordering
        $issued_apis = array_column($output, 'issued_api');
        $generated_leads = array_column($output, 'generated');

        array_multisort($issued_apis, SORT_DESC, $generated_leads, SORT_DESC, $output);

        $key = array_search('Others', array_column($output, 'name')); // $key = 2;

        $this->moveElement($output, $key, count($output) - 1);

        
        return $this->FilterOutput($output, "generated");
    }

    public function GetCumulativeBDMsKS()
    {
        $output = [];
        $dataset = $this->leadGeneratorController->getActiveBDMsKSData($this->quarterRange->from, $this->quarterRange->to);
        $others = [];
        $others["name"] = "Others";
        $others["deals"] = 0;
        $output["Others"] = $others;

        while ($row = $dataset->fetch_assoc()) {
            $output[$row["id"]] = $row;
        }
        
        $dataset = $this->leadGeneratorController->getInactiveBDMsKSData($this->quarterRange->from, $this->quarterRange->to);

        while ($row = $dataset->fetch_assoc()) {
            $output["Others"]["deals"] += $row["deals"];
        }


        //Lower priority to higher priority ordering
        $deals = array_column($output, 'deals');
        array_multisort($deals, SORT_DESC, $output);

        $key = array_search('Others', array_column($output, 'name')); // $key = 2;
        $this->moveElement($output, $key, count($output) - 1);
        
        return $this->FilterOutput($output, "deals");
    }

    public function GetBiMonthlyBDMs(){
        $output = [];
        $activeBDMs = [];
        $otherBDMs = [];

        $dataset = $this->leadGeneratorController->getActiveBDMsData($this->bimonthRange->from, $this->bimonthRange->to);
        // var_dump($this->bimonthRange->to);die;
        $others = [];
        $others["name"] = "Others";
        $others["issued_api"] = 0;
        $others["deals"] = 0;
        $others["generated"] = 0;
        $others["cancelled"] = 0;
        $output["Others"] = $others;

        while ($row = $dataset->fetch_assoc()) {
            $activeBDMs[] = $row["id"];
            $output[$row["id"]] = $row;
            $output[$row["id"]]["issued_api"] = 0;
            $output[$row["id"]]["deals"] = 0;
        }
        // while ($row = $dataset->fetch_assoc()) {
        //     $activeBDMs[] = $row["id"];
        //     // if($row["name"] == "Sumit Monga"){
        //     //     $otherAdvisers[] = $row["id"];
        //     // }else{
        //     //     $output[$row["id"]] = $row;
        //     //     $output[$row["id"]]["issued_api"] = 0;
        //     //     $output[$row["id"]]["deals"] = 0;
        //     // }

        //     if($row["name"] == "Sumit Monga"){
        //         $otherBDMs[] = $row["id"];
        //     }
        //     else{
        //         $output[$row["id"]] = $row;
        //         $output[$row["id"]]["issued_api"] = 0;
        //         $output[$row["id"]]["deals"] = 0;        
        //         $others["generated"] = 0;
        //         $others["cancelled"] = 0;
        //     }
        // }

        // $bdms_array = array_merge($activeBDMs, $otherBDMs);
        // $bdmsArrayString = implode(",", $bdms_array);
        // var_dump($bdmsArrayString);die;
        $bdmsArrayString = implode(",", $activeBDMs);
        
        // $query = "SELECT c.id as client_id, l.name as leadgen_name, l.id as leadgen_id, s.deals as deals FROM issued_clients_tbl i LEFT JOIN submission_clients s ON s.client_id = i.name LEFT JOIN leadgen_tbl l ON i.assigned_to = l.id LEFT JOIN clients_tbl c ON i.name = c.id  WHERE i.assigned_to IN ($bdmsArrayString) order by l.name";
        $query= "SELECT c.id as client_id, l.name as leadgen_name, l.id as leadgen_id, s.deals as deals FROM issued_clients_tbl i LEFT JOIN submission_clients s ON s.client_id = i.name LEFT JOIN leadgen_tbl l ON i.leadgen = l.id LEFT JOIN clients_tbl c ON i.name = c.id  WHERE i.leadgen IN ($bdmsArrayString) order by l.name";
        $statement = $this->prepare($query);

        $dataset = $this->execute($statement);

        while ($row = $dataset->fetch_assoc()) {
            $deals = json_decode($row["deals"], true);
            $total_issued_api = 0;
            $total_issued_deals = 0;

            if ($deals == null) {
                continue;
            }

            foreach ($deals as $deal) {

                if (isset($deal["status"])) {
                    if ($deal["status"] == "Issued") {

                        //Check if cancelled
                        if (isset($deal["clawback_status"])) {
                            if ($deal["clawback_status"] == "Cancelled") {
                                continue;
                            }
                        }

                        //
                        if ($this->WithinDateRange($deal["date_issued"], $this->bimonthRange)) {
                            $total_issued_api += $deal["issued_api"];
                            $total_issued_deals++;
                        }
                    }
                }
            }
            
            $output[$row["leadgen_id"]]["issued_api"] += floatval($total_issued_api);
            $output[$row["leadgen_id"]]["deals"] += floatval($total_issued_deals);
        }
        
        $dataset = $this->leadGeneratorController->getInactiveBDMsData($this->bimonthRange->from, $this->bimonthRange->to);

        while ($row = $dataset->fetch_assoc()) {
            $otherBDMs[] = $row["id"];
            $output["Others"]["generated"] += $row["generated"];
            $output["Others"]["cancelled"] += $row["cancelled"];
        }

        $otherBDMsArrayString = implode(",", $otherBDMs);

        $query = "SELECT c.id as client_id, l.name as lname, l.id as leadgen_id, s.deals as deals FROM issued_clients_tbl i LEFT JOIN submission_clients s ON s.client_id = i.name LEFT JOIN leadgen_tbl l ON i.assigned_to = l.id LEFT JOIN clients_tbl c ON i.name = c.id  WHERE i.leadgen IN ($otherBDMsArrayString) order by l.name";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        while ($row = $dataset->fetch_assoc()) {
            $deals = json_decode($row["deals"], true);
            $total_issued_api = 0;
            $total_issued_deals = 0;

            if ($deals == null) {
                continue;
            }

            foreach ($deals as $deal) {
                if (isset($deal["status"])) {
                    if ($deal["status"] == "Issued") {
                    //Check if cancelled
                        if (isset($deal["clawback_status"])) {
                            if ($deal["clawback_status"] == "Cancelled") {
                                continue;
                            }
                        }

                    //
                        // var_dump($deal["date_issued"]);
                        if ($this->WithinDateRange($deal["date_issued"], $this->bimonthRange)) {
                            $total_issued_api += $deal["issued_api"];
                            $total_issued_deals++;
                        } else {
                            $total_issued_api = 0;
                            $total_issued_deals = 0;
                        }
                    }
                }
            }
            $output["Others"]["issued_api"] += floatval($total_issued_api);
            $output["Others"]["deals"] += floatval($total_issued_deals);
        }
        // var_dump($output);die;
        $issued_apis = array_column($output, 'issued_api');
        $generated_leads = array_column($output, 'generated');

        array_multisort($issued_apis, SORT_DESC, $generated_leads, SORT_DESC, $output);
        $key = array_search('Others', array_column($output, 'name'));
        $this->moveElement($output, $key, count($output) - 1);


        return $this->FilterOutput($output, "deals");
    }

    public function CumulativeBDMs()
    {
        $output = [];
        $activeBDMs = [];
        $otherBDMs = [];
        $dataset = $this->leadGeneratorController->getActiveBDMsData($this->quarterRange->from, $this->quarterRange->to);

        $others = [];
        $others["name"] = "Others";
        $others["issued_api"] = 0;
        $others["deals"] = 0;
        // $others["generated"] = 0;
        // $others["cancelled"] = 0;
        $output["Others"] = $others;

        //Get Active
        while ($row = $dataset->fetch_assoc()) {
            $activeBDMs[] = $row["id"];

            if($row["name"] == "Sumit Monga"){
                $otherBDMs[] = $row["id"];
            }
            else{
                $output[$row["id"]] = $row;
                $output[$row["id"]]["issued_api"] = 0;
                $output[$row["id"]]["deals"] = 0;
            }
        }

        //Register inactive advisers
        $dataset = $this->leadGeneratorController->getInactiveBDMsKSData($this->quarterRange->from, $this->quarterRange->to);

        //Get Others
        // while ($row = $dataset->fetch_assoc()) {
        //     $otherBDMs[] = $row["id"];
        // }

        //Active Advisers deal fetching
        $bdmsArrayString = implode(",", $activeBDMs);

        $query = "SELECT c.id as client_id, l.name as leadgen_name, l.id as leadgen_id, s.deals as deals FROM issued_clients_tbl i LEFT JOIN submission_clients s ON s.client_id = i.name LEFT JOIN leadgen_tbl l ON i.assigned_to = l.id LEFT JOIN clients_tbl c ON i.name = c.id  WHERE i.assigned_to IN ($bdmsArrayString) order by l.name";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        while ($row = $dataset->fetch_assoc()) {
            $deals = json_decode($row["deals"], true);
            $total_issued_api = 0;
            $total_issued_deals = 0;

            if ($deals == null) {
                continue;
            }

            foreach ($deals as $deal) {
                if (isset($deal["status"])) {
                    if ($deal["status"] == "Issued") {
                        //Check if cancelled
                        if (isset($deal["clawback_status"])) {
                            if ($deal["clawback_status"] == "Cancelled") {
                                continue;
                            }
                        }

                        //
                        if ($this->WithinDateRange($deal["date_issued"], $this->quarterRange)) {
                            $total_issued_api += $deal["issued_api"];
                            $total_issued_deals++;
                        }

                    }
                }
            }

            if($row["leadgen_name"] != "Sumit Monga"){
                $output[$row["leadgen_id"]]["issued_api"] += floatval($total_issued_api);
                $output[$row["leadgen_id"]]["deals"] += floatval($total_issued_deals);
            }
        }

        //Ex advisers deals fetching
        if (count($otherBDMs) > 0) {

            $otherBDMsArrayString = implode(",", $otherBDMs);

            $query = "SELECT c.id as client_id, l.id as leadgen_id, s.deals as deals FROM issued_clients_tbl i LEFT JOIN submission_clients s ON s.client_id = i.name LEFT JOIN leadgen_tbl l ON i.assigned_to = l.id LEFT JOIN clients_tbl c ON i.name = c.id  WHERE i.assigned_to IN ($otherBDMsArrayString) order by l.name";
            $statement = $this->prepare($query);
            $dataset = $this->execute($statement);

            while ($row = $dataset->fetch_assoc()) {
                $deals = json_decode($row["deals"], true);
                $total_issued_api = 0;
                $total_issued_deals = 0;
                if ($row["client_id"] == "55") {
                }
                if ($deals == null) {
                    continue;
                }

                foreach ($deals as $deal) {
                    if (isset($deal["status"])) {
                        if ($deal["status"] == "Issued") {

                            //Check if cancelled
                            if (isset($deal["clawback_status"])) {
                                if ($deal["clawback_status"] == "Cancelled") {
                                    continue;
                                }
                            }

                            //
                            if ($this->WithinDateRange($deal["date_issued"], $this->quarterRange)) {
                                $total_issued_api += $deal["issued_api"];
                                $total_issued_deals++;
                            }
                        }
                    }
                }

                $output["Others"]["issued_api"] += floatval($total_issued_api);
                $output["Others"]["deals"] += floatval($total_issued_deals);
            }
        }

        $issued_apis = array_column($output, 'issued_api');
        array_multisort($issued_apis, SORT_DESC, $output);
        $key = array_search('Others', array_column($output, 'name')); // $key = 2;
        $this->moveElement($output, $key, count($output) - 1);
        return $this->FilterOutput($output, "deals");
    }

    public function GetCumulativeTMs()
    {
        $output = [];
        $activeTMs = [];
        $otherTMs = [];
        $dataset = $this->leadGeneratorController->getActiveTMsData($this->quarterRange->from, $this->quarterRange->to);
        $others = [];
        $others["name"] = "Others";
        $others["kiwisavers"] = 0;
        $others["submissions"] = 0;
        $others["issued_api"] = 0;
        $others["deals"] = 0;
        $others["generated"] = 0;
        $others["cancelled"] = 0;
        $output["Others"] = $others;

        while ($row = $dataset->fetch_assoc()) {
            $activeTMs[] = $row["id"];
            $output[$row["id"]] = $row;
            $output[$row["id"]]["kiwisavers"] = 0;
            $output[$row["id"]]["submissions"] = 0;
            $output[$row["id"]]["issued_api"] = 0;
            $output[$row["id"]]["deals"] = 0;
        }
        
        $tms_array = array_merge($activeTMs, $otherTMs);
        $tmsArrayString = implode(",", $tms_array);

        $query = "SELECT c.id as client_id, l.id as leadgen_id, s.deals as deals FROM issued_clients_tbl i LEFT JOIN submission_clients s ON s.client_id = i.name LEFT JOIN leadgen_tbl l ON i.leadgen = l.id LEFT JOIN clients_tbl c ON i.name = c.id  WHERE i.leadgen IN ($tmsArrayString) order by l.name";

        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);
        while ($row = $dataset->fetch_assoc()) {
            $deals = json_decode($row["deals"], true);

            $total_issued_api = 0;
            $total_issued_deals = 0;

            if ($deals == null) {
                continue;
            }

            foreach ($deals as $deal) {
                if (isset($deal["status"])) {
                    if ($deal["status"] == "Issued") {


                        //Check if cancelled
                        if (isset($deal["clawback_status"])) {
                            if ($deal["clawback_status"] == "Cancelled") {
                                continue;
                            }
                        }

                        //
                        if ($this->WithinDateRange($deal["date_issued"], $this->quarterRange)) {
                            $total_issued_api += $deal["issued_api"];
                            $total_issued_deals++;
                        }
                    }
                }
            }

            $output[$row["leadgen_id"]]["issued_api"] += floatval($total_issued_api);
            $output[$row["leadgen_id"]]["deals"] += $total_issued_deals;
        }

        $query = "SELECT c.id as client_id, l.id as leadgen_id, s.deals as deals FROM submission_clients s LEFT JOIN clients_tbl c ON s.client_id = c.id LEFT JOIN leadgen_tbl l ON c.leadgen = l.id WHERE c.leadgen IN ($tmsArrayString) order by l.name";
        
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);
        while ($row = $dataset->fetch_assoc()) {
            $deals = json_decode($row["deals"], true);

            $total_submissions = 0;

            if ($deals == null) {
                continue;
            }

            foreach ($deals as $deal) {
                if (isset($deal["status"])) {
                    if ($deal["status"] != "Deferred" || $deal["status"] != "Withdrawn") {
                        if ($this->WithinDateRange($deal["submission_date"], $this->quarterRange)) {
                            $total_submissions++;
                        }
                    }
                }
            }

            $output[$row["leadgen_id"]]["submissions"] += $total_submissions;
        }

        $dataset = $this->leadGeneratorController->getInactiveTMsData($this->quarterRange->from, $this->quarterRange->to);

        while ($row = $dataset->fetch_assoc()) {
            $otherTMs[] = $row["id"];
            
            $output["Others"]["generated"] += $row["generated"];
            $output["Others"]["cancelled"] += $row["cancelled"];
        }

        $tmsArrayString = implode(",", $otherTMs);

        $query = "SELECT c.id as client_id, l.id as leadgen_id, s.deals as deals FROM submission_clients s LEFT JOIN clients_tbl c ON s.client_id = c.id LEFT JOIN leadgen_tbl l ON c.leadgen = l.id WHERE c.leadgen IN ($tmsArrayString) order by l.name";

        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        while ($row = $dataset->fetch_assoc()) {
            $deals = json_decode($row["deals"], true);
            $total_issued_api = 0;
            $total_issued_deals = 0;

            if ($deals == null) {
                continue;
            }

            foreach ($deals as $deal) {
                if (isset($deal["status"])) {
                    if ($deal["status"] == "Issued") {

                        //Check if cancelled
                        if (isset($deal["clawback_status"])) {
                            if ($deal["clawback_status"] == "Cancelled") {
                                continue;
                            }
                        }

                        //
                        if ($this->WithinDateRange($deal["date_issued"], $this->quarterRange)) {
                            $total_issued_api += $deal["issued_api"];
                            $total_issued_deals++;
                        }
                    }
                }
            }

            $output["Others"]["issued_api"] += floatval($total_issued_api);
            $output["Others"]["deals"] += $total_issued_deals;
        }


        $query = "SELECT c.id as client_id, l.id as leadgen_id, s.deals as deals FROM issued_clients_tbl i LEFT JOIN submission_clients s ON s.client_id = i.name LEFT JOIN leadgen_tbl l ON i.leadgen = l.id LEFT JOIN clients_tbl c ON i.name = c.id  WHERE i.leadgen IN ($tmsArrayString) order by l.name";

        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        while ($row = $dataset->fetch_assoc()) {
            $deals = json_decode($row["deals"], true);
            $total_submissions = 0;

            if ($deals == null) {
                continue;
            }

            foreach ($deals as $deal) {
                if (isset($deal["status"])) {
                    if ($deal["status"] != "Deferred" || $deal["status"] != "Withdrawn") {
                        if ($this->WithinDateRange($deal["submission_date"], $this->quarterRange)) {
                            $total_submissions++;
                        }
                    }
                }
            }

            $output["Others"]["submissions"] += $total_submissions;
        }

        $dataset = $this->leadGeneratorController->getActiveTMsKSData($this->quarterRange->from, $this->quarterRange->to);

        while ($row = $dataset->fetch_assoc()) {
            $output[$row["id"]]["kiwisavers"] = $row["deals"];
        }

        $dataset = $this->leadGeneratorController->getInactiveTMsKSData($this->quarterRange->from, $this->quarterRange->to);

        while ($row = $dataset->fetch_assoc()) {
            $output["Others"]["kiwisavers"] += $row["deals"];
        }

        //Lower priority to higher priority ordering
        $issued_apis = array_column($output, 'submissions');
        $generated_leads = array_column($output, 'generated');

        array_multisort($issued_apis, SORT_DESC, $generated_leads, SORT_DESC, $output);

        $key = array_search('Others', array_column($output, 'name')); // $key = 2;

        $this->moveElement($output, $key, count($output) - 1);

        return $this->FilterOutput($output, "generated");
    }

    public function GetCumulativeTMsKS()
    {
        $output = [];
        $dataset = $this->leadGeneratorController->getActiveTMsKSData($this->quarterRange->from, $this->quarterRange->to);
        $others = [];
        $others["name"] = "Others";
        $others["deals"] = 0;
        $output["Others"] = $others;

        while ($row = $dataset->fetch_assoc()) {
            $output[$row["id"]] = $row;
        }
        
        $dataset = $this->leadGeneratorController->getInactiveTMsKSData($this->quarterRange->from, $this->quarterRange->to);

        while ($row = $dataset->fetch_assoc()) {
            $output["Others"]["deals"] += $row["deals"];
        }


        //Lower priority to higher priority ordering
        $deals = array_column($output, 'deals');
        array_multisort($deals, SORT_DESC, $output);

        $key = array_search('Others', array_column($output, 'name')); // $key = 2;
        $this->moveElement($output, $key, count($output) - 1);
        
        return $this->FilterOutput($output, "deals");
    }

    public function FilterOutput($output, $key)
    {
        $filtered_output = [];
        foreach ($output as $row) {
            if ($row[$key] > 0) {
                $filtered_output[] = $row;
            }
        }
        
        return $filtered_output;
    }

    public function TieRule($collection){
        $output = [];

        $ties = [];
        
        foreach ($collection as $item){

        }

        //Get Ties

    }

    public function CheckRecordsToBeat()
    {

    }

    public function GetRecordsToBeat()
    {
        $period = date("j", strtotime($this->bimonthRange->from)) . "-" . date("j M Y", strtotime($this->bimonthRange->to));
        $magazine_date = date("Ymd", strtotime($this->bimonthRange->to));

        $output = [];

        $query = "SELECT
        records_to_beat.*, record_types.*
        FROM
        (SELECT
        record_type_id, MAX(magazine_date) AS magazine_date
        FROM
        records_to_beat
        GROUP BY
        record_type_id) AS latest_records_to_beat
        INNER JOIN
        records_to_beat
        ON
        records_to_beat.record_type_id = latest_records_to_beat.record_type_id AND
        records_to_beat.magazine_date = latest_records_to_beat.magazine_date
        INNER JOIN
        record_types
        ON records_to_beat.record_type_id = record_types.id
        WHERE
        is_shown = 1;";

        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);
        while ($row = $dataset->fetch_assoc()) {
            $output[] = $row;
        }

        
        //Uncomment if there are BDMs again
        $highest_bdm_record = $output[0]["record"];
        //Get Current Bi-Monthly Data
        // $dataset = $this->leadGeneratorController->getActiveBDMsData($this->bimonthRange->from, $this->bimonthRange->to);
        // while ($row = $dataset->fetch_assoc()) {
        //     if ($row["generated"] > $highest_bdm_record) {
        //         $period = date("j", strtotime($this->bimonthRange->from)) . "-" . date("j M Y", strtotime($this->bimonthRange->to));
        //         $this->SetNewRecord(1, $period, $row["name"], $row["image"], "BDM", "Leads Generated", "Count", $row["generated"]);
        //     }
        // }

        $highest_adviser_record = (float) $output[1]["record"];
        //Get Current Bi-Monthly Data
        $dataset = $this->bi_monthly_advisers;
        foreach ($dataset as $row) {
            if ($row["issued_api"] > $highest_adviser_record) {
                $period = date("j", strtotime($this->bimonthRange->from)) . "-" . date("j M Y", strtotime($this->bimonthRange->to));
                $this->SetNewRecord(2, $period, $row["name"], $row["image"], "Adviser", "Issued API", "Currency", $row["issued_api"]);
            }
        }
        
        
        $highest_adviser_record = "";
        foreach($output as $out){
            if($out["type"] == "All-Time Bi-Monthly Highest Issued API"){
                $highest_adviser_record = (float) $out["record"];
            }
        }
        //Add a check here that if the highest record is null, it should skip the whole process.
        
        //var_dump($highest_adviser_record);
        
        //Get Current Bi-Monthly Data
        $collection = $this->bi_monthly_advisers;
        $column = "issued_api";
        foreach ($collection as $row) {
            if ($row[$column] > $highest_adviser_record) {
                $highest_adviser_record = $row[$column];
                $period = date("j", strtotime($this->bimonthRange->from)) . "-" . date("j M Y", strtotime($this->bimonthRange->to));
                $magazine_date = date("Ymd", strtotime($this->bimonthRange->to));
                $this->SetNewRecord(2, $row["name"], $period, $row[$column], $magazine_date, $row["image"]);
            }
        }
        
        foreach($output as $out){
            if($out["type"] == "All-Time Bi-Monthly Highest KiwiSaver Enrolments"){
                $highest_adviser_record = (float) $out["record"];
            }
        }
        //Get Current Bi-Monthly Data
        $collection = $this->bi_monthly_advisers_kiwisavers;
        //var_dump($collection);
        $column = "deals";
        foreach ($collection as $row) {
            if ($row[$column] > $highest_adviser_record) {
                $highest_adviser_record = $row[$column];
                $this->SetNewRecord(3, $row["name"], $period, $row[$column], $magazine_date, $row["image"]);
            }
        }

        $output = [];

        $query = "SELECT
        records_to_beat.*, record_types.*
        FROM
        (SELECT
        record_type_id, MAX(magazine_date) AS magazine_date
        FROM
        records_to_beat
        GROUP BY
        record_type_id) AS latest_records_to_beat
        INNER JOIN
        records_to_beat
        ON
        records_to_beat.record_type_id = latest_records_to_beat.record_type_id AND
        records_to_beat.magazine_date = latest_records_to_beat.magazine_date
        INNER JOIN
        record_types
        ON records_to_beat.record_type_id = record_types.id
        WHERE
        is_shown = 1 ORDER BY record_types.id;";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        while ($row = $dataset->fetch_assoc()) {
            $output[] = $row;
        }
        return $output;
    }

    public function SetNewRecord($record_type_id, $name, $date, $record, $magazine_date, $image)
    {
        $name = $this->clean($name);
        $image = $this->clean($image);

        $query = "INSERT INTO records_to_beat (record_type_id, name, date, record, magazine_date, image) VALUES ($record_type_id, '$name', '$date', '$record', '$magazine_date', '$image')";
        
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

    }

    public function GetWinnerScore(){
        $winner_adviser = $this->GetWinnerBiMonthlyAdvisers();
        $query = "SELECT * FROM `winner_score` LEFT JOIN adviser_tbl ON adviser_tbl.id = winner_score.adviser_id";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        while($output = $dataset->fetch_assoc()) {
            $advisers[] = $output;
        }

        return $advisers;
    }


    public function winnerScore(){

        $output = [];
        $activeAdvisers = [];
        $otherAdvisers = [];
        $dataset = $this->adviserController->getActiveAdvisers();

        $others = [];
        $others["name"] = "Others";
        $others["issued_api"] = 0;
        $others["deals"] = 0;
        $output["Others"] = $others;

        while ($row = $dataset->fetch_assoc()) {
            $activeAdvisers[] = $row["id"];
            if($row["team"] == "")
                $row["team"] = "None";


            if($row["name"] == "Sumit Monga"){
                $otherAdvisers[] = $row["id"];
            }
            else{
                $output[$row["id"]] = $row;
                $output[$row["id"]]["issued_api"] = 0;
                $output[$row["id"]]["deals"] = 0;
            }
        }

        //Register inactive advisers
        $dataset = $this->adviserController->getExAdvisers();
        while ($row = $dataset->fetch_assoc()) {
            $otherAdvisers[] = $row["id"];
        }

        //Active Advisers deal fetching
        $advisersArrayString = implode(",", $activeAdvisers);
        // var_dump($advisersArrayString);
        
        $query = "SELECT c.id as client_id, a.name as adviser_name, a.id as adviser_id, s.deals as deals, w.score as scores, w.bimonthly_range as current_bimonthly_date, w.silver as silver, w.gold as gold, w.platinum as platinum, w.titanium as titanium FROM issued_clients_tbl i LEFT JOIN submission_clients s ON s.client_id = i.name LEFT JOIN adviser_tbl a ON i.assigned_to = a.id LEFT JOIN winner_score w ON a.id = w.adviser_id LEFT JOIN clients_tbl c ON i.name = c.id  WHERE i.assigned_to IN ($advisersArrayString) order by a.name";

         // $query = "SELECT c.id as client_id, a.name as adviser_name, a.id as adviser_id, s.deals as deals, w.score as scores, w.bimonthly_range as current_bimonthly_date, w.silver as silver, w.gold as gold, w.platinum as platinum, w.titanium as titanium FROM winner_score w LEFT JOIN adviser_tbl a ON a.id = w.adviser_id LEFT JOIN issued_clients_tbl i ON i.assigned_to = a.id LEFT JOIN submission_clients s on s.client_id = i.name LEFT JOIN clients_tbl c on i.name = c.id WHERE i.assigned_to IN ($advisersArrayString) order by a.name";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);


        while ($row = $dataset->fetch_assoc()) {
            
            $deals = json_decode($row["deals"], true);
            $scores = json_decode($row["scores"], true);
            
            $total_issued_api = 0;
            $total_issued_deals = 0;

            if ($deals == null) {
                continue;
            }

            if($row["scores"] == null){
                continue;
            }
            
            foreach ($deals as $deal) {
                if (isset($deal["status"])) {
                    if ($deal["status"] == "Issued") {

                        //Check if cancelled
                        if (isset($deal["clawback_status"])) {
                            if ($deal["clawback_status"] == "Cancelled") {
                                continue;
                            }
                        }

                        //
                        if ($this->WithinDateRange($deal["date_issued"], $this->bimonthRange)) {
                            $total_issued_api += $deal["issued_api"];
                            $total_issued_deals++;
                            
                        }
                    }
                }
            }
            
            if($row["adviser_name"] != "Sumit Monga"){
                $output[$row["adviser_id"]]["issued_api"] += floatval($total_issued_api);
                $output[$row["adviser_id"]]["deals"] += floatval($total_issued_deals);
                $output[$row["adviser_id"]]["scores"] = $row["scores"];
                $output[$row["adviser_id"]]["silver"] = $row["silver"];
                $output[$row["adviser_id"]]["gold"] = $row["gold"];
                $output[$row["adviser_id"]]["platinum"] = $row["platinum"];
                $output[$row["adviser_id"]]["titanium"] = $row["titanium"];
                $output[$row["adviser_id"]]["current_bimonthly_date"] = $row["current_bimonthly_date"];

            }
        }

        //Ex advisers deals fetching
        if ($otherAdvisers) {

            $otherAdvisersArrayString = implode(",", $otherAdvisers);

            $query = "SELECT c.id as client_id, a.id as adviser_id, s.deals as deals FROM issued_clients_tbl i LEFT JOIN submission_clients s ON s.client_id = i.name LEFT JOIN adviser_tbl a ON i.assigned_to = a.id LEFT JOIN clients_tbl c ON i.name = c.id  WHERE i.assigned_to IN ($otherAdvisersArrayString) order by a.name";
            $statement = $this->prepare($query);
            $dataset = $this->execute($statement);

            while ($row = $dataset->fetch_assoc()) {
                $deals = json_decode($row["deals"], true);
                $total_issued_api = 0;
                $total_issued_deals = 0;
                if ($row["client_id"] == "55") {
                }
                if ($deals == null) {
                    continue;
                }

                foreach ($deals as $deal) {
                    if (isset($deal["status"])) {
                        if ($deal["status"] == "Issued") {

                            //Check if cancelled
                            if (isset($deal["clawback_status"])) {
                                if ($deal["clawback_status"] == "Cancelled") {
                                    continue;
                                }
                            }

                            //
                            if ($this->WithinDateRange($deal["date_issued"], $this->bimonthRange)) {
                                $total_issued_api += $deal["issued_api"];
                                $total_issued_deals++;
                            }
                        }
                    }
                }

                $output["Others"]["issued_api"] += floatval($total_issued_api);
                $output["Others"]["deals"] += floatval($total_issued_deals);
            }
        }
        
        // foreach($output as $index => $data){
        //     if($data["scores"] == "Titanium"){
        //         $output[$index]["scores"] = "a";
        //     } else if ($data["scores"] == "Platinum"){
        //         $output[$index]["scores"] = "i";
        //     } else if ($data["scores"] =="Gold"){
        //         $output[$index]["scores"] = "o";
        //     } else if ($data["scores"] =="Silver"){
        //         $output[$index]["scores"] = "v";
        //     }
        // }

        // usort($output, array($this, "cmp"));

        // foreach($output as $index => $data){
        //     if($data["scores"] == "a"){
        //         $output[$index]["scores"] = "Titanium";
        //     } else if ($data["scores"] == "i"){
        //         $output[$index]["scores"] = "Platinum";
        //     } else if ($data["scores"] =="o"){
        //         $output[$index]["scores"] = "Gold";
        //     } else if ($data["scores"] =="v"){
        //         $output[$index]["scores"] = "Silver";
        //     }
        // }

        // var_dump($output);
        // $issued_apis = array_column($output, 'issued_api');
        // $newScores = [];
        // $scores = array_filter(array_column($output, 'scores'), 'strlen');
        // $titanium = array();
        // $platinum = array();
        // $gold = array();
        // $silver = array();

        // $newArray = array();
        // foreach($scores as $index => $score){
        //     if($score == "Titanium"){
        //         array_push($titanium, $score);
        //     } else if($score == "Platinum"){
        //         array_push($platinum, $score);
        //     } else if($score == "Gold"){
        //         array_push($gold, $score);
        //     } else if($score == "Silver"){
        //         array_push($silver, $score);
        //     }
        // }


        // foreach($titanium as $t){
        //     $newArray[] = $t;
        // }

        // foreach($platinum as $p){
        //     $newArray[] = $p;
        // }

        // foreach($gold as $g){
        //     $newArray[] = $g;
        // }

        // foreach($silver as $s){
        //     $newArray[] = $s;
        // }

        // foreach($output as $index => $data){
        //     if($data["deals"] < 2 || $data["issued_api"] < 2000){
        //         unset($output[$index]);
        //     }
        // }

        $deals_winner = array_column($output, 'deals');
        // $deals_winner = array_column($output, 'titanium');
        array_multisort($deals_winner, SORT_DESC, $output);

        $key = array_search('Others', array_column($output, 'name')); // $key = 2;

        $this->moveElement($output, $key, count($output) - 1);

        return $this->FilterOutput($output, "deals");
    }

    public function array_reorder($array, $oldIndex, $newIndex) {
        array_splice(
            $array,
            $newIndex,
            count($array),
            array_merge(
                array_splice($array, $oldIndex, 1),
                array_slice($array, $newIndex, count($array))
            )
        );
        return $array;
    }

    public function cmp($a, $b) {
        return strcmp($a["score"], $b["score"]);
    }

    public function SetWinnerScore()
    {
        $datetoday = date('Ymd');
        $bimonthlyrange = $this->getCurrentBiMonthlyRange($datetoday);
        $bimonthly_range = date("Y-m-d", strtotime($this->bimonthRange->from));
        $winner_adviser = $this->GetWinnerBiMonthlyAdvisers();
        // var_dump($winner_adviser);die;
        $advisers = [];

        $dataset = $this->adviserController->getActiveAdvisers();

        while ($row = $dataset->fetch_assoc()) {
            $activeAdvisers[] = $row["id"];
        }

        $query = "SELECT * FROM `winner_score` LEFT JOIN adviser_tbl ON adviser_tbl.id = winner_score.adviser_id";
        // $query = "SELECT c.id as client_id, a.name as adviser_name, a.id as adviser_id, s.deals as deals, w.score as scores, w.bimonthly_range as current_bimonthly_date, w.silver as silver, w.gold as gold, w.platinum as platinum, w.titanium as titanium FROM winner_score w LEFT JOIN adviser_tbl a ON a.id = w.adviser_id LEFT JOIN issued_clients_tbl i ON i.assigned_to = a.id LEFT JOIN submission_clients s on s.client_id = i.name LEFT JOIN clients_tbl c on i.name = c.id WHERE i.assigned_to IN ($advisersArrayString) order by a.name";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);
        while($test = $dataset->fetch_assoc()) {
            $advisers[] = $test;
        }

        foreach($advisers as $index => $data){
            if($data["score"] == "Titanium"){
                $advisers[$index]["score"] = "a";
            } else if ($data["score"] == "Platinum"){
                $advisers[$index]["score"] = "i";
            } else if ($data["score"] =="Gold"){
                $advisers[$index]["score"] = "o";
            } else if ($data["score"] =="Silver"){
                $advisers[$index]["score"] = "v";
            }
        }

        usort($advisers, array($this, "cmp"));

        foreach($advisers as $index => $data){
            if($data["score"] == "a"){
                $advisers[$index]["score"] = "Titanium";
            } else if ($data["score"] == "i"){
                $advisers[$index]["score"] = "Platinum";
            } else if ($data["score"] =="o"){
                $advisers[$index]["score"] = "Gold";
            } else if ($data["score"] =="v"){
                $advisers[$index]["score"] = "Silver";
            }
        }

        $adviser_id = array();
        $bi_monthly_adv = array();
        $all_adviser = array();

        foreach($advisers as $adv_id){
            $all_adviser[] = $adv_id["adviser_id"];
            $adviser_id[] = $adv_id["adviser_id"];
        }

         foreach($winner_adviser as $adviser){
            if($adviser["name"] != "Others"){
                $all_adviser[] = $adviser["id"];
                $bi_monthly_adv[] = $adviser["id"];

            }
        }

        // var_dump($bi_monthly_adv);
        foreach($all_adviser as $adv){
            if(!in_array($adv, $bi_monthly_adv)){
                $query = "DELETE FROM winner_score WHERE adviser_id = {$adv}";
                $statement = $this->prepare($query);
                $dataset = $this->execute($statement);
            }
        }

        // var_dump($this->bimonthRange->from);
        foreach($winner_adviser as $adviser){
            if($adviser["name"] != "Others"){
                if(in_array($adviser["id"], $adviser_id)){
                    if($adviser["current_bimonthly_date"] <= $this->bimonthRange->from){
                       if($adviser["current_bimonthly_date"] != $bimonthly_range){
                            if($adviser["deals"] >= 5 && $adviser["issued_api"] >= 7500){
                                $query = "UPDATE winner_score SET `titanium` = titanium + 1, score = \"Titanium\", bimonthly_range = '$bimonthly_range' WHERE adviser_id = {$adviser["id"]}";
                                $statement = $this->prepare($query);
                                $dataset = $this->execute($statement);
                            } else if($adviser["deals"] >= 4 && $adviser["issued_api"] >= 6000) {
                                if($adviser["scores"] == "Titanium"){
                                    $query = "UPDATE winner_score SET `platinum` = platinum + 1, `titanium` = 0, score = \"Platinum\", bimonthly_range = '$bimonthly_range' WHERE adviser_id = {$adviser["id"]}";
                                    $statement = $this->prepare($query);
                                    $dataset = $this->execute($statement);
                                }else{
                                    $query = "UPDATE winner_score SET `platinum` = platinum + 1, score = \"Platinum\", bimonthly_range = '$bimonthly_range' WHERE adviser_id = {$adviser["id"]}";
                                    $statement = $this->prepare($query);
                                    $dataset = $this->execute($statement);
                                }
                            } else if ($adviser["deals"] >= 3 && $adviser["issued_api"] >= 4500) {
                                if($adviser["scores"] == "Titanium"){
                                    $query = "UPDATE winner_score SET `gold` = titanium + 1, `titanium` = 0, `platinum` = 0, score = \"Gold\", bimonthly_range = '$bimonthly_range' WHERE adviser_id = {$adviser["id"]}";
                                    $statement = $this->prepare($query);
                                    $dataset = $this->execute($statement);
                                }else if($adviser["scores"] == "Platinum"){
                                    $query = "UPDATE winner_score SET `gold` = platinum + 1, `titanium` = 0, `platinum` = 0, score = \"Gold\", bimonthly_range = '$bimonthly_range' WHERE adviser_id = {$adviser["id"]}";
                                    $statement = $this->prepare($query);
                                    $dataset = $this->execute($statement);
                                }else{
                                    $query = "UPDATE winner_score SET `gold` = gold + 1, `platinum` = 0, `titanium` = 0, score = \"Gold\", bimonthly_range = '$bimonthly_range' WHERE adviser_id = {$adviser["id"]}";
                                    $statement = $this->prepare($query);
                                    $dataset = $this->execute($statement);
                                }
                            } else if ($adviser["deals"] >= 2 && $adviser["issued_api"] >= 2000){
                                if($adviser["scores"] == "Titanium"){
                                    $query = "UPDATE winner_score SET `silver` = titanium + 1, `platinum` = 0, `titanium` = 0, `gold` = 0, score = \"Silver\", bimonthly_range = '$bimonthly_range' WHERE adviser_id = {$adviser["id"]}";
                                    $statement = $this->prepare($query);
                                    $dataset = $this->execute($statement);
                                }else if($adviser["scores"] == "Platinum"){
                                    $query = "UPDATE winner_score SET `silver` = platinum + 1, `platinum` = 0, `titanium` = 0, `gold` = 0, score = \"Silver\", bimonthly_range = '$bimonthly_range' WHERE adviser_id = {$adviser["id"]}";
                                    $statement = $this->prepare($query);
                                    $dataset = $this->execute($statement);
                                }else if($adviser["scores"] == "Gold"){
                                    $query = "UPDATE winner_score SET `silver` = gold + 1, `platinum` = 0, `titanium` = 0, `gold` = 0, score = \"Silver\", bimonthly_range = '$bimonthly_range' WHERE adviser_id = {$adviser["id"]}";
                                    $statement = $this->prepare($query);
                                    $dataset = $this->execute($statement);
                                }else {
                                    $query = "UPDATE winner_score SET `silver` = silver + 1, score = \"Silver\", bimonthly_range = '$bimonthly_range' WHERE adviser_id = {$adviser["id"]}";
                                    $statement = $this->prepare($query);
                                    $dataset = $this->execute($statement);
                                }
                            } else {
                                $query = "DELETE FROM winner_score WHERE adviser_id = {$adviser["id"]}";
                                $statement = $this->prepare($query);
                                $dataset = $this->execute($statement);
                            }
                        }
                    } else {
                        if($adviser["deals"] >= 5 && $adviser["issued_api"] >= 7500){
                            $query = "INSERT INTO winner_score (adviser_id, silver, gold, platinum, titanium, score, bimonthly_range) VALUES ({$adviser["id"]}, 0, 0, 0, 1, \"Titanium\", '$bimonthly_range')";
                            $statement = $this->prepare($query);
                            $dataset = $this->execute($statement);
                        } else if($adviser["deals"] >= 4 && $adviser["issued_api"] >= 6000) {
                            $query = "INSERT INTO winner_score (adviser_id, silver, gold, platinum, titanium, score, bimonthly_range) VALUES ({$adviser["id"]}, 0, 0, 1, 0, \"Platinum\", '$bimonthly_range')";
                            $statement = $this->prepare($query);
                            $dataset = $this->execute($statement);
                        } else if ($adviser["deals"] >= 3 && $adviser["issued_api"] >= 4500) {
                            $query = "INSERT INTO winner_score (adviser_id, silver, gold, platinum, titanium, score, bimonthly_range) VALUES ({$adviser["id"]}, 0, 1, 0, 0, \"Gold\", '$bimonthly_range')";
                            $statement = $this->prepare($query);
                            $dataset = $this->execute($statement);
                        } else if ($adviser["deals"] >= 2 && $adviser["issued_api"] >= 2000){
                            $query = "INSERT INTO winner_score (adviser_id, silver, gold, platinum, titanium, score, bimonthly_range) VALUES ({$adviser["id"]}, 1, 0, 0, 0, \"Silver\", '$bimonthly_range')";
                            $statement = $this->prepare($query);
                            $dataset = $this->execute($statement);
                        } else {
                            $query = "DELETE FROM winner_score WHERE adviser_id = {$adviser["id"]}";
                            $statement = $this->prepare($query);
                            $dataset = $this->execute($statement);
                        }
                    }
                } else {
                    if($adviser["deals"] >= 5 && $adviser["issued_api"] >= 7500){
                        $query = "INSERT INTO winner_score (adviser_id, silver, gold, platinum, titanium, score, bimonthly_range) VALUES ({$adviser["id"]}, 0, 0, 0, 1, \"Titanium\", '$bimonthly_range')";
                        $statement = $this->prepare($query);
                        $dataset = $this->execute($statement);
                    } else if($adviser["deals"] >= 4 && $adviser["issued_api"] >= 6000) {
                        $query = "INSERT INTO winner_score (adviser_id, silver, gold, platinum, titanium, score, bimonthly_range) VALUES ({$adviser["id"]}, 0, 0, 1, 0, \"Platinum\", '$bimonthly_range')";
                        $statement = $this->prepare($query);
                        $dataset = $this->execute($statement);
                    } else if ($adviser["deals"] >= 3 && $adviser["issued_api"] >= 4500) {
                        $query = "INSERT INTO winner_score (adviser_id, silver, gold, platinum, titanium, score, bimonthly_range) VALUES ({$adviser["id"]}, 0, 1, 0, 0, \"Gold\", '$bimonthly_range')";
                        $statement = $this->prepare($query);
                        $dataset = $this->execute($statement);
                    } else if ($adviser["deals"] >= 2 && $adviser["issued_api"] >= 2000){
                        $query = "INSERT INTO winner_score (adviser_id, silver, gold, platinum, titanium, score, bimonthly_range) VALUES ({$adviser["id"]}, 1, 0, 0, 0, \"Silver\", '$bimonthly_range')";
                        $statement = $this->prepare($query);
                        $dataset = $this->execute($statement);
                    } else {
                        $query = "DELETE FROM winner_score WHERE adviser_id = {$adviser["id"]}";
                        $statement = $this->prepare($query);
                        $dataset = $this->execute($statement);
                    }
                }
            }
        }
        // var_dump($advisers);die;
        // foreach($advisers as $index => $adv){
        //     if($adv["score"] == "Titanium"){
        //         $sort = $this->array_orderby($advisers, 'titanium', SORT_DESC);
        //     } else if($adv["score"] == "Platinum"){
        //         $sort = $this->array_orderby($advisers, 'platinum', SORT_DESC);
        //     }
        // }

        // $sort = $this->array_orderby($advisers, 'silver', SORT_DESC);
        // $sort = $this->array_orderby($advisers, 'gold', SORT_DESC);
        
        

        // die;
        return $advisers;
    }

    public function GetStringsWinnerScore(){
        $query = "SELECT * FROM `winner_score` LEFT JOIN adviser_tbl ON adviser_tbl.id = winner_score.adviser_id";
        // $query = "SELECT c.id as client_id, a.name as adviser_name, a.id as adviser_id, s.deals as deals, w.score as scores, w.bimonthly_range as current_bimonthly_date, w.silver as silver, w.gold as gold, w.platinum as platinum, w.titanium as titanium FROM winner_score w LEFT JOIN adviser_tbl a ON a.id = w.adviser_id LEFT JOIN issued_clients_tbl i ON i.assigned_to = a.id LEFT JOIN submission_clients s on s.client_id = i.name LEFT JOIN clients_tbl c on i.name = c.id WHERE i.assigned_to IN ($advisersArrayString) order by a.name";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);
        while($test = $dataset->fetch_assoc()) {
            $advisers[] = $test;
        }

        return $advisers;
    }
    public function array_orderby()
    {
        $args = func_get_args();
        $data = array_shift($args);
        foreach ($args as $n => $field) {
            if (is_string($field)) {
                $tmp = array();
                foreach ($data as $key => $row)
                    $tmp[$key] = $row[$field];
                $args[$n] = $tmp;
                }
        }
        $args[] = &$data;
        call_user_func_array('array_multisort', $args);
        return array_pop($args);
    }


    public function GetNewFaces()
    {
        $output = [];
        $from = $this->bimonthRange->from;
        $to = $this->bimonthRange->to;

        //Get Admins
        
        $query = "SELECT p.full_name as name, p.image as image, p.role, p.birthday FROM personal_data p WHERE p.date_hired >= '$from' AND p.date_hired <= '$to' AND p.termination_date = '' GROUP BY name";
        // $query = "SELECT p.full_name as name, p.image as image, p.role, p.birthday FROM personal_data p WHERE p.termination_date = '' GROUP BY name";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        while ($row = $dataset->fetch_assoc()) {
            $output[] = $row;
        }

        //Get Advisers
        $query = "SELECT name, image, 'Financial Adviser' as role FROM adviser_tbl WHERE date_hired >= '$from' AND date_hired <= '$to' GROUP BY name";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        while ($row = $dataset->fetch_assoc()) {
            $output[] = $row;
        }

        //Get Lead Generators
        $query = "SELECT name, image, type as role FROM leadgen_tbl WHERE date_hired >= '$from' AND date_hired <= '$to' GROUP BY name";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        while ($row = $dataset->fetch_assoc()) {
            if ($row["role"] == "Face-to-Face Marketer") {
                $row["role"] = "BDM";
            }

            $output[] = $row;
        }
        
        //Alphabetically arrange
        usort($output, array('Magazine', 'sortByName'));
        
        return $output;
    }

    public function GetBirthdays()
    {
        $output = [];
        $from = date("md", strtotime($this->currentBiMonthRange->to));
        $to = date("md", strtotime($this->currentBiMonthRange->from));
        
        //Get Admins
        $query = "SELECT p.full_name as name, p.image as image, p.role, p.birthday FROM personal_data p WHERE RIGHT(p.birthday,4) >= '$from' AND RIGHT(p.birthday,4) <= '$to' AND p.termination_date = '' GROUP BY name";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        while ($row = $dataset->fetch_assoc()) {
            $row["birthday"] = date("jS F", strtotime($row["birthday"]));
            $output[] = $row;
        }

        //Get Advisers
        $query = "SELECT name, image, 'Financial Adviser' as role, birthday FROM adviser_tbl WHERE RIGHT(birthday,4) >= '$from' AND RIGHT(birthday,4) <= '$to' AND termination_date = '' GROUP BY name";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        while ($row = $dataset->fetch_assoc()) {
            $row["birthday"] = date("jS F", strtotime($row["birthday"]));
            $output[] = $row;
        }

        //Get Lead Generators
        $query = "SELECT name, image, type as role, birthday FROM leadgen_tbl WHERE RIGHT(birthday,4) >= '$from' AND RIGHT(birthday,4) <= '$to' AND termination_date = '' GROUP BY name";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        while ($row = $dataset->fetch_assoc()) {
            $row["birthday"] = date("jS F", strtotime($row["birthday"]));
            if ($row["role"] == "Face-to-Face Marketer") {
                $row["role"] = "BDM";
            }

            $output[] = $row;
        }

        //Alphabetically arrange
        usort($output, array('Magazine', 'sortByName'));
        
        return $output;
    }


    public function GetWorkAnniversaries()
    {
        $output = [];
        $from = date("md", strtotime($this->currentBiMonthRange->from));
        $to = date("md", strtotime($this->currentBiMonthRange->to));
        $current_year = date("Y");

        //Get Admins
        $query = "SELECT p.full_name as name, p.image as image, p.role, p.date_hired FROM users u LEFT JOIN personal_data p ON p.id = u.linked_id WHERE RIGHT(p.date_hired,4) >= '$from' AND RIGHT(p.date_hired,4) <= '$to' AND p.termination_date = ''";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        while ($row = $dataset->fetch_assoc()) {
            $row["anniversary"] = date("jS F", strtotime($row["date_hired"]));
            $year = date("Y", strtotime($row["date_hired"]));
            $row["years"] = $current_year - $year;

            $output[] = $row;
        }

        //Get Advisers
        $query = "SELECT name, image, 'Financial Adviser' as role, date_hired FROM adviser_tbl WHERE RIGHT(date_hired,4) >= '$from' AND RIGHT(date_hired,4) <= '$to' AND termination_date = ''";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        while ($row = $dataset->fetch_assoc()) {
            $row["anniversary"] = date("jS F", strtotime($row["date_hired"]));
            $year = date("Y", strtotime($row["date_hired"]));
            $row["years"] = $current_year - $year;
            $output[] = $row;
        }

        //Get Lead Generators
        $query = "SELECT name, image, type as role, date_hired FROM leadgen_tbl WHERE RIGHT(date_hired,4) >= '$from' AND RIGHT(date_hired,4) <= '$to' AND termination_date = ''";
        $statement = $this->prepare($query);
        $dataset = $this->execute($statement);

        while ($row = $dataset->fetch_assoc()) {
            $row["anniversary"] = date("jS F", strtotime($row["date_hired"]));
            $year = date("Y", strtotime($row["date_hired"]));
            $row["years"] = $current_year - $year;
            $output[] = $row;
        }

        //Alphabetically arrange
        usort($output, array('Magazine', 'sortByName'));

        return $this->FilterOutput($output, "years");
    }

    private static function sortByName($a, $b)
    {
        return strcmp($a["name"], $b["name"]);
    }

    //Range should be an object with from and to as attributes
    public function WithinDateRange($date, $range)
    {
        if ($date <= $range->to && $date >= $range->from) {
            return true;
        }
        return false;
    }

    //Move array to specified index
    public function moveElement(&$array, $from, $to)
    {
        $out = array_splice($array, $from, 1);
        array_splice($array, $to, 0, $out);
    }
}

class Series extends Database {

    public $bimonthRange = "";
    public $actualCumulativeRange = "";
    public $issue_number = "";


    public function __construct($date, $announcement = "", $quote = "", $message = "", $photos = []) {
        $this->bimonthRange = $this->getBiMonthlyRange($date);
        $this->actualCumulativeRange = $this->getActualCumulativeRange($date);
        $this->issue_number = $this->getIssueFromDate($this->bimonthRange->from, $this->actualCumulativeRange);
    }

    public function getBiMonthlyRange($date)
    {
        $output = new stdClass();

        $year = date("Y", strtotime($date));
        $month = date("m", strtotime($date));
        $day = date("d", strtotime($date));

        if ($day >= 16) {
            $output->from = date("Ym", strtotime($date)) . "01";
            $output->to = date("Ym", strtotime($date)) . "15";
        } else {
            $output->from = date('Ym', strtotime('last day of last month', strtotime($date))) . "16";
            $output->to = date('Ymd', strtotime('last day of last month', strtotime($date)));
        }
        
        return $output;
    }

    public function getActualCumulativeRange($date)
    {
        $output = new stdClass();

        $year = date("Y", strtotime($date));
        $month = date("m", strtotime($date));
        $day = date("d", strtotime($date));
        if ($month > 6) {
            if ($day < 16 && $month == "07") {
                $output->from = $year . "0101";
                $output->to = $year . "0630";
            } else {
                $output->from = $year . "0701";
                $output->to = $year . "1231";
            }
        } else {
            if ($day < 16 && $month == "01") {
                $year -= 1;
                $output->from = $year . "0701";
                $output->to = $year . "1231";
            } else {
                $output->from = $year . "0101";
                $output->to = $year . "0630";
            }
        }
        return $output;
    }

    public function getIssueFromDate($date, $cumulativeRange)
    {
        $first_volume_year = "2017";

        $year = date("Y", strtotime($date));
        $month = date("m", strtotime($date));
        $day = date("d", strtotime($date));

        $volume = ($year - $first_volume_year) * 2;
        if ($month > 6) {
            $volume++;
            $month -= 6;
        }
        
        $issue = ($month - 1) * 2;

        if ($day >= 16) {
            $issue += 2;
        } else {
            $issue++;
        }

        return "Volume $volume: " . date("jS F", strtotime($cumulativeRange->from)) . " - " . date("jS F", strtotime($cumulativeRange->to));
    }
} 
