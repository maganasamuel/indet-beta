<?php
require "database.php";
if(isset($_GET["id"])){
  extract($_GET);
  if($summary_id=="all"){
    $query="DELETE FROM lead_gen_report";
  }
  else{
    $query="DELETE FROM lead_gen_report WHERE id='$id'";    
  }
  //Delete Record
  if (mysqli_query($con, $query)) {
    header("Location: lead_gen_reports.php");
  }
  else{
    echo "Error deleting record: " . mysqli_error($conn);
  }
}

?>
