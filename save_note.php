<?php require "database.php";

$id=$_POST['id'];
$note=$_POST['note'];

$sql="UPDATE invoices SET note='$note' WHERE id='$id'"; 

mysqli_query($con,$sql);



echo 'Succesfully saved';
?>