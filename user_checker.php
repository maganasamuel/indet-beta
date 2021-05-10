<?php
    //echo $_SESSION["myusertype"];
    $actual_link = $_SERVER["REQUEST_URI"];
    //echo "<hr>$actual_link<hr>";
    $split_uri = explode("/", $actual_link);
    $current_link = end($split_uri);
    $allowed_links = array();

    if($_SESSION["myusertype"]=="Admin"){
        $allowed_links = array(
            "index",
            "main",
            "adviser",
            "magazine",
            "kiwisavers",
            "leadgen",
            "telemarketer",
            "client",
            "bin",
            "deal",
            "create",
            "invoice",
            "invoices",
            "summary",
            "summaries",
            "reports",
            "tracker",
            "data",
            "script",
            "insurance",
            "user",
            "setting",
            "deal",
            "bulk_emails",
            "team",
            "product",
            "do_not_calls",
            "bdm",
            "crud"
        );
    }
    elseif($_SESSION["myusertype"]=="User"){
        $allowed_links = array(
            "index",
            "main",
            "add_client",
            "editclient",
            "delete_client",
            "do_not_calls",
            "clients",
            "leads_data",
            "view_issued_client_profile",
            "fetch_submission_client_data",
            "client_profiles",
            "create_lead_gen_report",
            "lead_gen_reports",
            "arrear_deals",
            "generate_magazine",
            "create_magazine",
            "magazines",
            "records_to_beat"
        );

    }
    elseif($_SESSION["myusertype"]=="Telemarketer"){
        $allowed_links = array(
            "index",
            "main",
            "my_leads_generated",
            "callbacks",
            "leads_data_pdf",
            "dataminer",
            "crud"           
        );
    }
    elseif($_SESSION["myusertype"]=="Adviser"){
        $allowed_links = array(
            "index",
            "leads_assigned",
            "main",
            "crud"           
        );
    }

    Check($allowed_links, $current_link);

    function KickUser(){
        header("Location: index");
        return;
    }

    function Check($allowed_links, $current_link){

        $ctr = 0;
        foreach($allowed_links as $link){
            if(strpos($current_link, $link) !== false){
                break;
            }
            else{
                if($ctr == (count($allowed_links) - 1)){
                    session_destroy();
                    KickUser();
                }
                else{
                    $ctr++;
                    continue;
                }
            }
        }
    }
?>