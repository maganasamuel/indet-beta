<?php
    
    include("client_data_pdf.php");

    
    $client_id = $_GET["id"];
    CreateClientDataPDF($client_id, "123");
?>