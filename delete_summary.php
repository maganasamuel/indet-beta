<?php
require "database.php";
if(isset($_GET["summary_id"])){
  extract($_GET);
  if($summary_id=="all"){
    $query="DELETE FROM summary";
  }
  else{
    $query="DELETE FROM summary WHERE id='$summary_id'";    
  }
  //Delete Record
  if (mysqli_query($con, $query)) {
    header("Location: summaries.php");
  }
  else{
    echo "Error deleting record: " . mysqli_error($conn);
  }
}

?>
