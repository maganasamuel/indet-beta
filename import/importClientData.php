<?php
// Load the database configuration file
include_once '../database.php';

if(isset($_POST['importSubmit'])){
    $errors = 0;
    // Allowed mime types
    $csvMimes = array('text/x-comma-separated-values', 'text/comma-separated-values', 'application/octet-stream', 'application/vnd.ms-excel', 'application/x-csv', 'text/x-csv', 'text/csv', 'application/csv', 'application/excel', 'application/vnd.msexcel', 'text/plain');
    
    // Validate whether selected file is a CSV file
    if(!empty($_FILES['file']['name']) && in_array($_FILES['file']['type'], $csvMimes)){
        
        // If the file is uploaded
        if(is_uploaded_file($_FILES['file']['tmp_name'])){
            
            // Open uploaded CSV file with read-only mode
            $csvFile = fopen($_FILES['file']['tmp_name'], 'r');
            
            // Skip the first line
            fgetcsv($csvFile);
            
            // Parse data from CSV file line by line
            while(($line = fgetcsv($csvFile)) !== FALSE){
                // Get row data
                $id   = $line[0];
                $name  = $line[1];
                $appt_date  = $line[2];
                $appt_time   = $line[3];
                $address  = $line[4];
                $leadgen  = $line[5];
                $assigned_to   = $line[6];
                $assigned_date  = $line[7];
                $type_of_lead  = $line[8];
                $issued   = $line[9];
                $date_issued  = $line[10];
                $notes  = $line[11];
                
                // Check whether member already exists in the database with the same email
                $prevQuery = "SELECT id FROM clients_tbl WHERE id = $id";
                $prevResult = mysqli_query($con, $prevQuery);
                $num_rows = mysqli_num_rows($prevResult);

                if($num_rows > 0){
                    // Update member data in the database
                    $query = "UPDATE clients_tbl SET name = '".$name."', appt_date = '".$appt_date."', appt_time = '".$appt_time."', address = '".$address."' , leadgen = '".$leadgen."', assigned_to = '".$assigned_to."', assigned_date = '".$assigned_date."' , type_of_lead = '".$type_of_lead."', issued = '".$issued."', date_issued = '".$date_issued."' , notes = '".$notes."' WHERE id = '".$id."'";
                }else{
                    // Insert member data in the database
                    $query = "INSERT INTO clients_tbl (id,name,appt_date,appt_time,address,leadgen,assigned_to,assigned_date,type_of_lead,issued,date_issued,notes, date_submitted) VALUES ($id,'".$name."', '".$appt_date."', '".$appt_time."','".$address."', '".$leadgen."', '".$assigned_to."','".$assigned_date."', '".$type_of_lead."', '".$issued."','".$date_issued."', '".$notes."','".$assigned_date."')";
                }
                if (!mysqli_query($con, $query)) {
                    $errors++;
                    echo $query;
                }
            }
            
            // Close opened CSV file
            fclose($csvFile);
            
            $qstring = '?status=succ';
        }else{
            $qstring = '?status=err';
        }
    }else{
        $qstring = '?status=invalid_file';
    }
}

if($errors!=0){
    $qstring.= "&errors=$errors";
}
header("Location: importClients.php".$qstring);

// Redirect to the listing page

?>