<?php
require "database.php";
if(isset($_GET["id"])){
  extract($_GET);
  if($deal_id=="all"){
    $query="DELETE FROM team_deal_tracker_reports";
  }
  else{
    $query="DELETE FROM team_deal_tracker_reports WHERE id='$deal_id'";    
  }
  //Delete Record
  if (mysqli_query($con, $query)) {
    header("Location: team_deal_tracker_reports.php");
  }
  else{
    echo "Error deleting record: " . mysqli_error($conn);
  }
}

?>
