<?php 
session_start();
/*
header("Pragma: public");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Content-Type: application/force-download");
header("Content-Type: application/octet-stream");
header("Content-Type: application/download");
header("Content-Transfer-Encoding: binary ");
*/
require('database.php');
require('PHPExcel/PHPExcel.php');







function convertToDate($input){
    $output = "";
    $output = substr($input,6,2). "/" . substr($input,4,2). "/" . substr($input,0,4);
    return $output;
}



$data = array();

$objPHPExcel = new PHPExcel(); 
$sheetIndex = 0;
$objWorkSheet = $objPHPExcel->createSheet($sheetIndex);
$filename = $leadgen_refnum . "_" . str_replace("/", "", $date_covered);
//echo $filename;
//header("Content-Disposition: attachment;filename=$filename.xlsx");

$lead_gens = json_decode($lead_gens);
$advisers = json_decode($advisers);
//EXCEL RENDERING
if($clienttype!="Clients with Enforced Policies"){
    $objPHPExcel->setActiveSheetIndex($sheetIndex); 
    $objWorkSheet = $objPHPExcel->getActiveSheet();
    $objWorkSheet->setTitle("Clients");
    $rowCount = 1;

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
$query = "$select$from$where$sort";  
//echo $query . "<br><hr>";
$result = mysqli_query($con, $query);
    if($result){
        $data[0] = 'Lead Generator';
        $data[1] = 'Adviser';
        $data[2] = 'Name';
        $data[3] = 'Appointment Date';
        $data[4] = 'Address';
        $data[5] = 'Phone';
        $data[6] = 'Date Submitted';
        //var_dump($data);
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

        foreach(range('A','G') as $columnID) {
            $objPHPExcel->getActiveSheet()->getColumnDimension($columnID)->setAutoSize(true);
        }
        
        $objPHPExcel->getActiveSheet()->getStyle("A1:G$rowCount")->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $rowCount+=2; 
        $sheetIndex++;
    }
    else{
        echo mysqli_connect_error();
    }
}
if($clienttype!="Clients with No Policies"){
    $objPHPExcel->setActiveSheetIndex($sheetIndex); 
    $objWorkSheet = $objPHPExcel->getActiveSheet();
    $objWorkSheet->setTitle("Issued Clients");
    $rowCount = 1;
//QUERY BUILDER
    $select = "SELECT *, c.name as client_name, c.id as client_id, c.appt_time as phone, c.appt_date as appt_date, i.date_issued as issued_date";
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
//echo $query;
$result = mysqli_query($con, $query);
    if($result){
        $data[0] = 'Lead Generator';
        $data[1] = 'Adviser';
        $data[2] = 'Name';
        $data[3] = 'Appointment Date';
        $data[4] = 'Address';
        $data[5] = 'Phone';
        $data[6] = 'Date Issued';
        //var_dump($data);
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
            $data[5] = $phone;
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

        foreach(range('A','G') as $columnID) {
            $objPHPExcel->getActiveSheet()->getColumnDimension($columnID)->setAutoSize(true);
        }
        

        $objPHPExcel->getActiveSheet()->getStyle("A1:G$rowCount")->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

    }

    else{
        echo mysqli_connect_error();
    }
}
/*
$objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel); 
$objWriter->setOffice2003Compatibility(true);
$objWriter->save('php://output');
*/
?>