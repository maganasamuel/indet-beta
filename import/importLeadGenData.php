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
                
                // Check whether member already exists in the database with the same email
                $prevQuery = "SELECT id FROM leadgen_tbl WHERE id = $id";
                $prevResult = mysqli_query($con, $prevQuery);
                $num_rows = mysqli_num_rows($prevResult);

                if($num_rows > 0){
                    // Update member data in the database
                    $query = "UPDATE leadgen_tbl SET name = '".$name."' WHERE id = '".$id."'";
                }else{
                    // Insert member data in the database
                    $query = "INSERT INTO leadgen_tbl (id,name,type) VALUES ($id,'".$name."', 'Face-to-Face Marketer')";
                }

                if (mysqli_query($con, $query)) {
                    echo "Success inserting record.<hr>";
                }else{
                    $errors++;
                    echo $query;
                    //echo "Error deleting record: " . mysqli_error($con);
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
header("Location: importLeadGens.php".$qstring);

// Redirect to the listing page

?>