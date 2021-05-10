<?php
require "database.php";
if(isset($_GET["invoice_id"])){
  extract($_GET);
  if($invoice_id=="all"){
    $query="DELETE FROM invoices";
  }
  else{
    $query="DELETE FROM invoices WHERE id='$invoice_id'";    
  }
  //Delete Record
  if (mysqli_query($con, $query)) {
    header("Location: invoices.php");
  }
  else{
    echo "Error deleting record: " . mysqli_error($conn);
  }
}

?>
