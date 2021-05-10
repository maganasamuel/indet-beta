<?php
require("database.php");
date_default_timezone_set('Pacific/Auckland');

$data=json_decode($_POST["mydata"], true);
extract($data);

//Insert into pdf table
/*

$file['filename']=$mix;
$file['date_created']=$customized_invoice_data->date_stamp;
$file['invoice_number']=$customized_invoice_data->invoice_number;
$file['type']='invoice';
$file['gst_type']=$customized_invoice_data->gst_type;
$file['total_amount']=$customized_invoice_data->total_amount;
$file['data'] = json_encode($customized_invoice_data);
$file['company_name'] = $customized_invoice_data->company_name;
$file['name'] = $customized_invoice_data->name;
$file['address'] = $customized_invoice_data->address;
$sql="INSERT INTO pdf_tbl (adviser_id,name,email,link,filename,entrydate,type) 
VALUES ('$adviser_id','$name','$email','$link','$filename','$entrydate','$type')"; 
mysqli_query($con,$sql);
*/

$user_id = $_SESSION["myuserid"];

//Add slashes to safely create the query
$name = addslashes($name);
$company_name = addslashes($company_name);
$address = addslashes($address);

$data = json_encode($data);

//Insert into invoice table
$sql="INSERT INTO customized_invoices (invoice_number,company_name,name,address,gst,gst_type,total_amount,user_id,date_created,data) 
VALUES ('$invoice_number','$company_name','$name','$address','$gst','$gst_type','$total_amount',$user_id,'$date_created',$data)"; 
mysqli_query($con,$sql);

echo $sql;

?>