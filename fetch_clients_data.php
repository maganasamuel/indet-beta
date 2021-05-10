<?php
session_start();
$restrict_session_check = true;
require "database.php";
$allowedOrigins = array(
    '(http(s)://)?(www\.)?my\-domain\.com',
    'http://localhost:8100'
  );
   
  if (isset($_SERVER['HTTP_ORIGIN']) && $_SERVER['HTTP_ORIGIN'] != '') {
    foreach ($allowedOrigins as $allowedOrigin) {
      if (preg_match('#' . $allowedOrigin . '#', $_SERVER['HTTP_ORIGIN'])) {
        header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
        header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
        header('Access-Control-Max-Age: 1000');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        break;
      }
    }
  }
header('Content-Type: application/json');

$query="Select c.name as client_name, c.status, c.appt_date, c.appt_time as client_phone, c.city as client_city, c.zipcode as client_zipcode, c.assigned_date, c.lead_by, c.date_submitted, c.address as client_address, a.name as adviser_name, a.address as adviser_address, l.name as leadgen_name from clients_tbl c LEFT JOIN adviser_tbl a ON c.assigned_to = a.id LEFT JOIN leadgen_tbl l ON c.leadgen = l.id";	
if ($result = mysqli_query($con, $query)) {
    $arr = array();
    while($row=mysqli_fetch_assoc($result)){
        if($row["adviser_name"]==""){
            $row["adviser_name"] = "Unassigned";
        }
        $arr[] = $row;
    }
    
    print json_encode($arr);
}else{
    echo "<br>Error: " . mysqli_error($con);
}

?>
