<?php
$photos_stash_url = "../indet_photos_stash/";
include_once "libs/api/controllers/PersonalData.controller.php";
include_once "libs/api/controllers/Adviser.controller.php";
include_once "libs/api/controllers/LeadGenerator.controller.php";
include_once "libs/api/controllers/Magazine.controller.php";

$personalDataController = new PersonalDataController();
$dataset = $personalDataController->getAllActiveStaff();

$images = [];

while ($row = $dataset->fetch_assoc()) {
    if ($row["image"] != "") {
        $images[] = $row["image"];
    }
}

$adviserController = new AdviserController();
$dataset = $adviserController->getActiveAdvisers();

while ($row = $dataset->fetch_assoc()) {
    if ($row["image"] != "") {
        $images[] = $row["image"];
    }
}

$leadgenController = new LeadGeneratorController();
$dataset = $leadgenController->getActiveLeadGenerators();

while ($row = $dataset->fetch_assoc()) {
    if ($row["image"] != "") {
        $images[] = $row["image"];
    }
}

foreach ($images as $index => $image) {
    $images[$index] = $photos_stash_url . $image;
}

$magazineController = new MagazineController();
$dataset = $magazineController->getAllMagazinePhotos();

while ($row = $dataset->fetch_assoc()) {

    if (is_array($row)) {
        if (count($row) > 0) {
            $photos = json_decode($row["photos"], true);

            foreach ($photos as $photo) {
                $images[] = $photo["filename"];
            }
        }

    }
}

//echo "<pre>";
//var_dump($images);
//echo "<hr>";

$files = glob($photos_stash_url . '*'); // get all file names

//var_dump($files);
//echo "<hr>";

$deleteable_files = [];
$deleted_files = 0;
foreach ($files as $file) { // iterate files
    if (is_file($file)) {
        if (!in_array($file, $images)) {
            $deleteable_files[] = $file;
        }
    }

}

//var_dump($deleteable_files);
//echo "</pre>";

foreach ($deleteable_files as $file) {
    if (is_file($file)) {
        $deleted_files++;
        unlink($file);
    }
}
echo "$deleted_files deleted files.";

/*
$files = glob('files/*.pdf'); // get all file names
foreach($files as $file){ // iterate files
if(is_file($file))
unlink($file); // delete file
}

$files = glob('files/*.png'); // get all file names
foreach($files as $file){ // iterate files
if(is_file($file))
unlink($file); // delete file
}
 */
