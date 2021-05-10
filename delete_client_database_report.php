<?php
require "database.php";
if(isset($_GET["id"])){
  extract($_GET);
  if($id=="all"){
    $query="DELETE FROM client_data_reports";
  }
  else{
    $query="DELETE FROM client_data_reports WHERE id='$id'";    
  }
  //Delete Record
  if (mysqli_query($con, $query)) {
    header("Location: client_databases.php");
  }
  else{
    echo "Error deleting record: " . mysqli_error($conn);
  }
}

?>
