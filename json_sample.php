<?php

header('Content-Type: application/json');
    $drug = new stdClass();
    $drug->name = "Drug 1";
    
    $drug->substances = array();

    $drug->substances[] = 1;
    $drug->substances[] = 4;
    $drug->substances[] = 7;

    $drug->details = new stdClass();
    $drug->details->type = 1;
    $drug->details->shape = 30;
    $drug->details->administration_way = 1;
    

    $json_string = json_encode($drug);

    echo $json_string;
?>