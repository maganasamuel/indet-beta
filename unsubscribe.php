<?php
    ob_start();
    date_default_timezone_set('Pacific/Auckland');

    $restrict_session_check = true;
    require "database.php";
    $unsub_key = $_GET["key"];
    $client_id = $_GET["id"];
    $will_update = false;

    $query = "SELECT * FROM submission_clients WHERE deals LIKE '%$unsub_key%' LIMIT 1";

    //echo $query;
    $result = mysqli_query($con,$query);

    $row = mysqli_fetch_assoc($result);

    $deals = json_decode($row["deals"]);

    foreach($deals as $deal){
        if(isset($deal->unsub_key)){
            if($deal->unsub_key == $unsub_key){
                $deal->unsubscribed = true;
                $will_update = true;
                break;
            }
        }
        
        if(isset($deal->unsub_key2)){
            if($deal->unsub_key2 == $unsub_key){
                $deal->unsubscribed2 = true;
                $will_update = true;
                break;
            }    
        }
    }

    if($will_update){
        $deals = json_encode($deals);
        $query = "UPDATE submission_clients SET deals = '$deals' WHERE id = $client_id";
        $result = mysqli_query($con, $query);
    }
?>