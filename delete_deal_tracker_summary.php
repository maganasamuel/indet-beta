<?php
require "database.php";
if(isset($_GET["id"])){
  extract($_GET);
  if($deal_id=="all"){
    $query="DELETE FROM deal_tracker_summary";
  }
  else{
    $query="DELETE FROM deal_tracker_summary WHERE id='$deal_id'";    
  }
  //Delete Record
  if (mysqli_query($con, $query)) {
    header("Location: deal_tracker_summaries.php");
  }
  else{
    echo "Error deleting record: " . mysqli_error($conn);
  }
}

?>
