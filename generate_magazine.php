<?php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    require 'libs/api/classes/magazine.class.php';

    include('magazine_pdf.php');

    $magazine_file = '';

    if (isset($_POST['date'])) {
        $filenames = [];

        $photos = $_POST['photos'] ?? [];

        if (count($photos)) {
            foreach ($photos as $photo) {
                $blob = substr($photo, strpos($photo, ',') + 1);
                $blob = base64_decode($blob);

                $filename = md5(uniqid(rand(), true)) . '.png';

                file_put_contents('../indet_photos_stash/' . $filename, $blob);

                $filenames[] = $filename;
            }
        }

        $date = (isset($_POST['date'])) ? $_POST['date'] : '';

        if ($date) {
            if (false !== strpos($date, '/')) {
                $date_array = explode('/', $date);
                $day = $date_array[0];
                $month = $date_array[1];
                $year = $date_array[2];
                $date = $year . $month . $day;
            }
        }
        $announcement = (isset($_POST['announcement'])) ? $_POST['announcement'] : '';
        $quote = (isset($_POST['quote'])) ? $_POST['quote'] : '';
        $message = (isset($_POST['message'])) ? $_POST['message'] : '';
        $preview = (isset($_POST['output'])) ? false : true;
        $random_filename = (isset($_POST['random_filename'])) ? true : false;

        $magazine_data = new Magazine($date, $announcement, $quote, $message, $filenames);
        $magazine_file = CreateMagazinePDF($magazine_data, $preview, $random_filename);

        echo $magazine_file;
    }
    //Generate file only
    elseif (isset($magazine_id)) {
        $magazineController = new MagazineController();
        $magazine = $magazineController->getMagazine($magazine_id)->fetch_assoc();
        $magazine_data = json_decode($magazine['magazine_data']);
        MagazineDatabaseToMagazineData($magazine_data);
        $magazine = CreateMagazinePDF($magazine_data, false, false);
        $magazine = json_decode($magazine, true);
        $period = date('j', strtotime($magazine_data->currentBiMonthRange->from)) . '-' . date('j M Y', strtotime($magazine_data->currentBiMonthRange->to));
        $magazine_filepath = $magazine['link'];
    }
    //View
    elseif (isset($_GET['view_id'])) {
        $magazineController = new MagazineController();
        $magazine = $magazineController->getMagazine($_GET['view_id'])->fetch_assoc();
        $magazine_data = json_decode($magazine['magazine_data']);
        MagazineDatabaseToMagazineData($magazine_data);

        //var_dump($magazine_data->pages);
        //extract($magazine_data);
        //$magazine_data = new Magazine($date, $announcement, $quote, $message, $photos);
        $magazine_file = CreateMagazinePDF($magazine_data, true, false);
    }

    function FallbackValue($value, $default)
    {
        if (empty($value)) {
            return $default;
        }

        return $value;
    }

    function ConvertToArray($object)
    {
        return json_decode(json_encode($object), true);
    }

    function MagazineDatabaseToMagazineData(&$magazine_data)
    {
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
