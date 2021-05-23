<?php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    require "libs/api/classes/magazine.class.php";

    include("magazine_pdf.php");

    $magazine_file = "";
    if(isset($_POST["date"])){

        $photos = [];

        if(isset($_POST["widths"])){

            $target_dir = "../indet_photos_stash/";
            if(strpos($_POST["widths"], ",") !== false){
                $_POST["widths"] = explode(",", $_POST["widths"]);
                $_POST["heights"] = explode(",", $_POST["heights"]);
                $_POST["labels"] = explode(",", $_POST["labels"]);
                $_POST["filenames"] = explode(",", $_POST["filenames"]);
            }
            
            if(is_array($_POST["widths"])){
                // Count total files
                $countfiles = count($_POST["widths"]);    
                // Loop all files
                for($index = 0;$index < $countfiles; $index++){
                    if($_POST["filenames"][$index]!=""){                           
                        $photo_data = [
                            "filename" => FallbackValue($target_dir  . $_POST["filenames"][$index],"Nani"),
                            "label" => FallbackValue($_POST["labels"][$index],""),
                            "width" => FallbackValue($_POST["widths"][$index],30),
                            "height" => FallbackValue($_POST["heights"][$index],30)
                        ];
        
                        $photos[] = $photo_data;    
                    }
                }
                
            }
            else{
                if($_POST["filenames"]!=""){                        
                    // Count total files
                    $photo_data = [
                        "filename" => FallbackValue($target_dir  . $_POST["filenames"],"Nani"),
                        "label" => FallbackValue($_POST["labels"],""),
                        "width" => FallbackValue($_POST["widths"],30),
                        "height" => FallbackValue($_POST["heights"],30)
                    ];

                    $photos[] = $photo_data;  
                }  
            }
        }
    
        $date = (isset($_POST["date"])) ? $_POST["date"] : "";
        if($date){
            if (strpos($date, '/') !== false) {
                $date_array = explode("/", $date);
                $day = $date_array[0];
                $month = $date_array[1];
                $year = $date_array[2];
                $date = $year . $month . $day;
            }
        }
        $announcement = (isset($_POST["announcement"])) ? $_POST["announcement"] : "";
        $quote = (isset($_POST["quote"])) ? $_POST["quote"] : "";
        $message = (isset($_POST["message"])) ? $_POST["message"] : "";
        $preview = (isset($_POST["output"])) ? false : true;
        $random_filename = (isset($_POST["random_filename"])) ? true : false;
        
        $magazine_data = new Magazine($date, $announcement, $quote, $message, $photos);
        $magazine_file = CreateMagazinePDF($magazine_data, $preview, $random_filename);
        
        echo $magazine_file;
    }
    //Generate file only
    elseif(isset($magazine_id)){
        $magazineController = new MagazineController();
        $magazine = $magazineController->getMagazine($magazine_id)->fetch_assoc();
        $magazine_data = json_decode($magazine["magazine_data"]);
        MagazineDatabaseToMagazineData($magazine_data);
        $magazine = CreateMagazinePDF($magazine_data, false, false);
        $magazine = json_decode($magazine,true);
        $period = date("j", strtotime($magazine_data->currentBiMonthRange->from)) . "-" . date("j M Y", strtotime($magazine_data->currentBiMonthRange->to));
        $magazine_filepath = $magazine["link"];
    }
    //View
    elseif(isset($_GET["view_id"])){
        $magazineController = new MagazineController();
        $magazine = $magazineController->getMagazine($_GET["view_id"])->fetch_assoc();
        $magazine_data = json_decode($magazine["magazine_data"]);
        MagazineDatabaseToMagazineData($magazine_data);

        //var_dump($magazine_data->pages);
        //extract($magazine_data);
        //$magazine_data = new Magazine($date, $announcement, $quote, $message, $photos);
        $magazine_file = CreateMagazinePDF($magazine_data, true, false);
    }

    function FallbackValue($value, $default){
        if(empty($value))
            return $default;

        return $value;
    }

    function ConvertToArray($object){
        return json_decode(json_encode($object), true);
    }

    function MagazineDatabaseToMagazineData(&$magazine_data){
        $magazine_data->pages = ConvertToArray($magazine_data->pages);
        $magazine_data->bi_monthly_advisers = ConvertToArray($magazine_data->bi_monthly_advisers);
        $magazine_data->cumulative_advisers = ConvertToArray($magazine_data->cumulative_advisers);
        $magazine_data->bi_monthly_advisers_kiwisavers = ConvertToArray($magazine_data->bi_monthly_advisers_kiwisavers);
        $magazine_data->cumulative_advisers_kiwisavers = ConvertToArray($magazine_data->cumulative_advisers_kiwisavers);
        $magazine_data->bdm_performances = ConvertToArray($magazine_data->bdm_performances);
        $magazine_data->bdm_ks_performances = ConvertToArray($magazine_data->bdm_ks_performances);
        $magazine_data->records_to_beat = ConvertToArray($magazine_data->records_to_beat);
        $magazine_data->new_faces = ConvertToArray($magazine_data->new_faces);
        $magazine_data->upcoming_birthdays = ConvertToArray($magazine_data->upcoming_birthdays);
        $magazine_data->upcoming_work_anniversaries = ConvertToArray($magazine_data->upcoming_work_anniversaries);
        $magazine_data->photos = ConvertToArray($magazine_data->photos);

        $magazine_data->bi_monthly_bdms = isset($magazine_data->bi_monthly_bdms) ? ConvertToArray($magazine_data->bi_monthly_bdms) : null;
        $magazine_data->all_winner_score = isset($magazine_data->all_winner_score) ? ConvertToArray($magazine_data->all_winner_score) : null;
        $magazine_data->winner_score = isset($magazine_data->winner_score) ? ConvertToArray($magazine_data->winner_score) : null;
        $magazine_data->rba_cumulative_advisers = isset($magazine_data->rba_cumulative_advisers) ? ConvertToArray($magazine_data->rba_cumulative_advisers) : null;

        $magazine_data->adr_bi_monthly_advisers = isset($magazine_data->adr_bi_monthly_advisers) ? ConvertToArray($magazine_data->adr_bi_monthly_advisers) : null;
        $magazine_data->adr_cumulative_advisers = isset($magazine_data->adr_cumulative_advisers) ? ConvertToArray($magazine_data->adr_cumulative_advisers) : null;
        $magazine_data->adr_bi_monthly_advisers_kiwisavers = isset($magazine_data->adr_bi_monthly_advisers_kiwisavers) ? ConvertToArray($magazine_data->adr_bi_monthly_advisers_kiwisavers) : null;
        $magazine_data->adr_cumulative_advisers_kiwisavers = isset($magazine_data->adr_cumulative_advisers_kiwisavers) ? ConvertToArray($magazine_data->adr_cumulative_advisers_kiwisavers) : null;
        $magazine_data->sadr_bi_monthly_advisers = isset($magazine_data->sadr_bi_monthly_advisers) ? ConvertToArray($magazine_data->sadr_bi_monthly_advisers) : null;
        $magazine_data->sadr_cumulative_advisers = isset($magazine_data->sadr_cumulative_advisers) ? ConvertToArray($magazine_data->sadr_cumulative_advisers) : null;
        $magazine_data->sadr_bi_monthly_advisers_kiwisavers = isset($magazine_data->sadr_bi_monthly_advisers_kiwisavers) ? ConvertToArray($magazine_data->sadr_bi_monthly_advisers_kiwisavers) : null;
        $magazine_data->sadr_cumulative_advisers_kiwisavers = isset($magazine_data->sadr_cumulative_advisers_kiwisavers) ? ConvertToArray($magazine_data->sadr_cumulative_advisers_kiwisavers) : null;
    }
