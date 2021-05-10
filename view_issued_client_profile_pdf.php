<?php
    
    include("issued_client_profile_pdf.php");
    
    $client_id = $_GET["id"];
    $preview = (isset($_GET["output_file"])) ? false : true;


    echo CreateIssuedClientProfilePDF($client_id, "preview" . date("dmY"), $preview);
?>