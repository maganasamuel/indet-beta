<?php

$restrict_session_check = true;

require("database.php");

if(session_id()==""){
	session_start();
}

$where = "WHERE type = '";
if(isset($_POST['leadby'])){
	switch ($_POST['leadby']) {
		case 'Telemarketer':
			$where .= "Telemarketer'";
			break;
		
		case 'Face-to-Face Marketer':
			$where .= "Face-to-Face Marketer'";
			break;
		case '':
			$where = "";
			break;
	}
}

$sql="Select * from leadgen_tbl " . $where; 

$result = mysqli_query($con,$sql);
$rows = array();
while($r = mysqli_fetch_assoc($result)) {
    $rows[] = $r;
}
print json_encode($rows);
?>