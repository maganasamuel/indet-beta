<?php
require "database.php";
if(isset($_GET["deal_id"])){
  extract($_GET);
  if($deal_id=="all"){
    $query="DELETE FROM deals_report";
  }
  else{
    $query="DELETE FROM deals_report WHERE id='$deal_id'";    
  }
  //Delete Record
  if (mysqli_query($con, $query)) {
    header("Location: deals_reports.php");
  }
  else{
    echo "Error deleting record: " . mysqli_error($conn);
  }
}

?>
