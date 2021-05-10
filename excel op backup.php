<?php 
/*
header("Pragma: public");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Content-Type: application/force-download");
header("Content-Type: application/octet-stream");
header("Content-Type: application/download");;
header("Content-Disposition: attachment;filename=ExamplePHPExcel.xlsx");
header("Content-Transfer-Encoding: binary ");
*/
require('database.php');
require('PHPExcel/PHPExcel.php');


$date_from = "";
$date_to = "";
extract($_POST);


//Set dates
if($date_from!="" && $date_to!=""){
    //echo $date_from . "-" . $date_to;
}
else{
    $date_to_query = "SELECT * FROM clients_tbl ORDER BY date_submitted DESC LIMIT 1";
    $date_to_result = mysqli_query($con,$date_to_query) or die('Could not look up user information; ' . mysqli_error($con));
    $date_to_fetch = mysqli_fetch_assoc($date_to_result);
    $date_to = $date_to_fetch["date_submitted"];
    $date_from_query = "SELECT * FROM clients_tbl ORDER BY date_submitted ASC LIMIT 1";
    $date_from_result = mysqli_query($con,$date_from_query) or die('Could not look up user information; ' . mysqli_error($con));
    $date_from_fetch = mysqli_fetch_assoc($date_from_result);
    $date_from = $date_from_fetch["date_submitted"];
}

$until = $date_to;
$date_covered = $date_from . " - " . $date_to;
//echo $date_covered;
//CREATE REFERENCE NUMBER
$refnum_query = "SELECT COUNT(*) as total FROM client_data_reports WHERE reference_number LIKE '%$date_now'";
//echo $refnum_query;
$refnum_result = mysqli_query($con,$refnum_query) or die('Could not look up user information; ' . mysqli_error($con));
$refnum_count = mysqli_fetch_assoc($refnum_result);

$leadgen_refnum = "CD-" .  convertToFourDigits(($refnum_count['total'] + 1)) . str_replace("/", "",$date_now);

function convertToFourDigits($num = 0){
    $op = "";
    if($num < 10){
        $op = "000" . $num;
    }
    elseif($num < 100){
        $op = "00" . $num;
    }
    elseif($num < 1000){
        $op = "0" . $num;
    }
    elseif($num < 10000){
        $op = "" . $num;
    }
    return $op;
}

function convertToDate($input){
    $output = "";
    $output = substr($input,6,2). "/" . substr($input,4,2). "/" . substr($input,0,4);
    return $output;
}
    
//echo "<br><hr>$date_covered<hr><br>";
//Headers
$objPHPExcel = new PHPExcel(); 
$objPHPExcel->setActiveSheetIndex(0); 

$rowCount = 1; 
$objPHPExcel->getActiveSheet()->mergeCells('A1:G1');
$objPHPExcel->getActiveSheet()->mergeCells('A2:D2');
$objPHPExcel->getActiveSheet()->mergeCells('E2:G2');
//Header
$objPHPExcel->getActiveSheet()->setCellValue('A1','Client Database for ' . $clienttype);
$objPHPExcel->getActiveSheet()->setCellValue('A2','Reference Number: ' . $leadgen_refnum);
$objPHPExcel->getActiveSheet()->setCellValue('E2','Date Covered: ' . $date_covered);
$objPHPExcel->getActiveSheet()->getStyle("E2")->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
$from = "A1"; // or any value
$to = "G3"; // or any value
$objPHPExcel->getActiveSheet()->getStyle("$from:$to")->getFont()->setBold( true );

$data = array();
//EXCEL RENDERING
$rowCount = 3; 
//CLIENTS
if($clienttype!="Clients with Enforced Policies"){
    //QUERY BUILDER
    $select = "SELECT *, c.name as client_name, c.id as client_id";
    $from = " from clients_tbl c ";
    $where = "";
    $sort = " ORDER BY c.leadgen DESC, c.date_submitted DESC, c.name ASC";


    //if Leadgen is specified
    if(!empty($leadgens)){
        $wherestring = implode("','",explode(",",$leadgens));      
        $where = " WHERE l.id IN ('" . $wherestring . "') ";
    }
    $select.=", l.name as leadgen_name ";
    $from .= " LEFT JOIN leadgen_tbl l ON c.leadgen = l.id ";

    //if Adviser is specified
    if(!empty($advisers)){
        $wherestring = implode("','",explode(",",$advisers));
        if($where==""){
            $where = " WHERE ";
        }
        else{ 
            $where .= " AND ";
        }
        $where .= "  a.id IN ('" . $wherestring . "') ";
    }
    $select.=", a.name as adviser_name ";
    $from .= " LEFT JOIN adviser_tbl a ON c.assigned_to = a.id ";

    //if date is specified
    if($date_from!="" && $date_to!=""){
        if($where==""){
            $where = " WHERE ";
        }
        else{ 
            $where .= " AND ";
        }
        $where .= " c.date_submitted <= '$until' AND c.date_submitted >= '$date_from' ";
    }
//END OF QUERY BUILDER
    if($where==""){
            $where = " WHERE ";
        }
        else{ 
            $where .= " AND ";
        }
    $where .= " c.id NOT IN (SELECT name FROM issued_clients_tbl) ";
    $query = "$select$from$where$sort";  
    //echo $query;
    $result = mysqli_query($con, $query);
    if($result){
        
        $objPHPExcel->getActiveSheet()->setCellValue('A' . $rowCount,'Clients with No Policies');
        
        $objPHPExcel->getActiveSheet()->mergeCells('A$rowCount:G$rowCount');
        //Headers Bold
        $from = "A" . $rowCount; // or any value
        $to = "G" . $rowCount; // or any value
        $objPHPExcel->getActiveSheet()->getStyle("$from:$to")->getFont()->setBold( true );

        $rowCount++; 
        
        $data[0] = 'Lead Generator';
        $data[1] = 'Adviser';
        $data[2] = 'Name';
        $data[3] = 'Appointment Date';
        $data[4] = 'Address';
        $data[5] = 'Phone';
        $data[6] = 'Date Submitted';
        var_dump($data);
        $objPHPExcel->getActiveSheet()->fromArray($data,NULL,'A'.$rowCount);
        while($row = mysqli_fetch_array($result)){ 
            $rowCount++;
            //var_dump($row);
            extract($row);
            $data[0] = $leadgen_name;
            $data[1] = $adviser_name;
            $data[2] = $client_name;
            $data[3] = convertToDate($appt_date);
            $data[4] = $address;
            $data[5] = $appt_time;
            $data[6] = convertToDate($date_submitted);
            $objPHPExcel->getActiveSheet()->fromArray($data,NULL,'A'.$rowCount);

            if($rowCount % 2 == 0){
                $objPHPExcel->getActiveSheet()->getStyle('A' . $rowCount . ":G" . $rowCount)->applyFromArray(
                    array(
                        'fill' => array(
                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                            'color' => array('rgb' => 'DDDDDD')
                        )
                    )
                );
            }
        } 

        $rowCount+=2;
    }
}

//CLIENTS ISSUED
if($clienttype!="Clients with No Policies"){
    //QUERY BUILDER
    $select = "SELECT *, c.name as client_name, c.id as client_id, i.date_issued as issued_date";
    $from = " from clients_tbl c INNER JOIN issued_clients_tbl i ON c.id = i.name ";
    $where = "";
    $sort = " ORDER BY c.leadgen DESC, c.date_submitted DESC, c.name ASC";


    //if Leadgen is specified
    if(!empty($leadgens)){
        $wherestring = implode("','",explode(",",$leadgens));      
        $where = " WHERE l.id IN ('" . $wherestring . "') ";
    }
    $select.=", l.name as leadgen_name ";
    $from .= " LEFT JOIN leadgen_tbl l ON c.leadgen = l.id ";

    //if Adviser is specified
    if(!empty($advisers)){
        $wherestring = implode("','",explode(",",$advisers));
        if($where==""){
            $where = " WHERE ";
        }
        else{ 
            $where .= " AND ";
        }
        $where .= "  a.id IN ('" . $wherestring . "') ";
    }
    $select.=", a.name as adviser_name ";
    $from .= " LEFT JOIN adviser_tbl a ON c.assigned_to = a.id ";

    //if date is specified
    if($date_from!="" && $date_to!=""){
        if($where==""){
            $where = " WHERE ";
        }
        else{ 
            $where .= " AND ";
        }
        $where .= " c.date_submitted <= '$until' AND c.date_submitted >= '$date_from' ";
    }
//END OF QUERY BUILDER
    $query = "$select$from$where$sort";  
    echo $query;
    $result = mysqli_query($con, $query);
    if($result){
        
        $objPHPExcel->getActiveSheet()->setCellValue('A' . $rowCount,'Clients with Enforced Policies');
        $objPHPExcel->getActiveSheet()->mergeCells('A$rowCount:G$rowCount');
        
        //Headers Bold
        $from = "A" . $rowCount; // or any value
        $to = "G" . $rowCount; // or any value
        $objPHPExcel->getActiveSheet()->getStyle("$from:$to")->getFont()->setBold( true );

        $rowCount++; 
        $data[0] = 'Lead Generator';
        $data[1] = 'Adviser';
        $data[2] = 'Name';
        $data[3] = 'Appointment Date';
        $data[4] = 'Address';
        $data[5] = 'Phone';
        $data[6] = 'Date Issued';

        $objPHPExcel->getActiveSheet()->fromArray($data,NULL,'A'.$rowCount);

        while($row = mysqli_fetch_array($result)){ 
            $rowCount++;
            //var_dump($row);
            extract($row);
            $data[0] = $leadgen_name;
            $data[1] = $adviser_name;
            $data[2] = $client_name;
            $data[3] = convertToDate($appt_date);
            $data[4] = $address;
            $data[5] = $appt_time;
            $data[6] = convertToDate($issued_date);
            $objPHPExcel->getActiveSheet()->fromArray($data,NULL,'A'.$rowCount);

            if($rowCount % 2 == 0){
                $objPHPExcel->getActiveSheet()->getStyle('A' . $rowCount . ":G" . $rowCount)->applyFromArray(
                    array(
                        'fill' => array(
                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                            'color' => array('rgb' => 'DDDDDD')
                        )
                    )
                );
            }
        } 
    }
}


$objPHPExcel->getActiveSheet()->getStyle("A1:G$rowCount")->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    

foreach(range('A','G') as $columnID) {
        $objPHPExcel->getActiveSheet()->getColumnDimension($columnID)->setAutoSize(true);
    }
echo $rowCount;

//$objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel); 
//$objWriter->setOffice2003Compatibility(true);
//$objWriter->save('php://output');
?>