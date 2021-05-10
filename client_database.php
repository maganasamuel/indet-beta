<?php
session_start();




require('database.php');
require('PHPExcel/PHPExcel.php');

$reference_number = $_GET['reference_number'];



$sql = "SELECT * FROM client_data_reports WHERE reference_number = '$reference_number'";
$result = mysqli_query($con, $sql);
$cdr = mysqli_fetch_assoc($result);
extract($cdr);
//echo $filterdata."<br>";
$filterdata = json_decode($filterdata);

// die;
function convertToDate($input)
{
    $output = "";
    $output = substr($input, 6, 2) . "/" . substr($input, 4, 2) . "/" . substr($input, 0, 4);
    return $output;
}

function timestampToDate($input)
{
    return date("d/m/Y", strtotime($input));
}


$data = array();

$objPHPExcel = new PHPExcel();
$sheetIndex = 0;
$filename = "$reference_number$date_from-$date_to";
//echo $filename;


$lead_gens = json_decode($lead_gens);
$advisers = json_decode($advisers);
//EXCEL RENDERING
//LEADS

if($filterby == "specificmonth"){
    $year_from = date('Y', strtotime($date_from));
    $year_to = date('Y', strtotime($date_to));

    $temp_date_from = $date_from;
    $temp_date_to = $date_to;
}




if ($client_type == "Unassigned Clients" || $client_type == "All Clients") {
    if ($sheetIndex > 0) {
        $objWorkSheet = $objPHPExcel->createSheet($sheetIndex);
    }
    if($filterby == "specificmonth"){
        for($year = $year_from; $year <= $year_to; $year++){
            $date_from = substr_replace($temp_date_from, $year, 0, 4);
            $date_to = substr_replace($temp_date_to, $year, 0, 4);

            //QUERY BUILDER
            $select = "SELECT c.city as city, c.zipcode as zipcode, c.address as c_address, c.appt_time as phone, c.appt_date as appt_date, c.date_submitted as date_submitted, c.name as client_name, c.id as client_id";
            $from = " from clients_tbl c ";
            $where = " WHERE c.assigned_to = 0";
            $sort = " ORDER BY c.leadgen DESC, c.date_submitted DESC, c.name ASC";

            //if Leadgen is specified
            if (!empty($lead_gens)) {
                $wherestring = $lead_gens;

                if ($where == "") {
                    $where = " WHERE ";
                } else {
                    $where .= " AND ";
                }

                if (count($lead_gens) > 1) {
                    $wherestring = implode("','", $lead_gens);
                    $where .= " l.id IN ('" . $wherestring . "') ";
                } else {
                    $where .= " l.id = '" . $wherestring[0] . "'";
                }
            }

            $select .= ", l.name as leadgen_name ";
            $from .= " LEFT JOIN leadgen_tbl l ON c.leadgen = l.id ";
            // '", $advisers));die;
            //if Adviser is specified
            if (!empty($advisers)) {
                $wherestring = $advisers;

                if ($where == "") {
                    $where = " WHERE ";
                } else {
                    $where .= " AND ";
                }

                if (count($advisers) > 1) {
                    $wherestring = implode("','", $advisers);
                    $where .= "  a.id IN ('" . $wherestring . "') ";
                } else {
                    $where .= " a.id = '" . $wherestring[0] . "'";
                }
            }

            $select .= ", a.name as adviser_name ";
            $from .= " LEFT JOIN adviser_tbl a ON c.assigned_to = a.id ";

            //if date is specified
            if ($date_from != "" && $date_to != "") {
                if ($where == "") {
                    $where = " WHERE ";
                } else {
                    $where .= " AND ";
                }
                $where .= " c.date_submitted <= '$date_to' AND c.date_submitted >= '$date_from' ";
            }

            if ($source != "" && !empty($source)) {
                if ($where == "") {
                    $where = " WHERE ";
                } else {
                    $where .= " AND ";
                }

                $where .= " c.lead_by = '$source'";
            }

            //NOT IN ISSUED CLIENTS
            if ($where == "") {
                $where = " WHERE ";
            } else {
                $where .= " AND ";
            }
            $where .= " c.id NOT IN (SELECT name FROM issued_clients_tbl)";

            //END OF QUERY BUILDER
            $query = "$select$from$where$sort";
            // die;

            $result = mysqli_query($con, $query);

            if ($result) {
                
                
                $objPHPExcel->setActiveSheetIndex($sheetIndex);
                $objWorkSheet = $objPHPExcel->getActiveSheet();
                // die;
                $objWorkSheet->setTitle("Unassigned Clients");
                $rowCount = 1;

                $data[0] = 'Lead Generator';
                $data[1] = 'Adviser';
                $data[2] = 'Name';
                $data[3] = 'Appointment Date';
                $data[4] = 'Address';
                $data[5] = 'City';
                $data[6] = 'Zip Code';
                $data[7] = 'Phone';
                $data[8] = 'Date Submitted';
                //
                $objPHPExcel->getActiveSheet()->fromArray($data, NULL, 'A' . $rowCount);
                $objPHPExcel->getActiveSheet()->getStyle("A1:I1")->getFont()->setBold(true);
                while ($row = mysqli_fetch_array($result)) {
                    $rowCount++;
                    //
                    extract($row);
                    //echo "<hr>$c_address<hr>";
                    $data[0] = ($leadgen_name == "") ? "Self-Generated" : $leadgen_name;
                    $data[1] = ($adviser_name == "") ? "Not Assigned" : $adviser_name;
                    $data[2] = $client_name;
                    $data[3] = convertToDate($appt_date);
                    $data[4] = $c_address;
                    $data[5] = $city;
                    $data[6] = $zipcode;
                    $data[7] = $phone;
                    $data[8] = convertToDate($date_submitted);
                    $objPHPExcel->getActiveSheet()->fromArray($data, NULL, 'A' . $rowCount);

                    if ($rowCount % 2 == 0) {
                        $objPHPExcel->getActiveSheet()->getStyle('A' . $rowCount . ":I" . $rowCount)->applyFromArray(
                            array(
                                'fill' => array(
                                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                    'color' => array('rgb' => 'DDDDDD')
                                )
                            )
                        );
                    }
                }

                foreach (range('A', 'I') as $columnID) {
                    $objPHPExcel->getActiveSheet()->getColumnDimension($columnID)->setAutoSize(true);
                }

                $objPHPExcel->getActiveSheet()->getStyle("A1:I$rowCount")->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $rowCount += 2;
            } else {
                echo mysqli_connect_error();
            }
        }
        $sheetIndex++;
        $date_from = $temp_date_from;
        $date_to = $temp_date_to;
    } else {
        //QUERY BUILDER
        $select = "SELECT c.city as city, c.zipcode as zipcode, c.address as c_address, c.appt_time as phone, c.appt_date as appt_date, c.date_submitted as date_submitted, c.name as client_name, c.id as client_id";
        $from = " from clients_tbl c ";
        $where = " WHERE c.assigned_to = 0";
        $sort = " ORDER BY c.leadgen DESC, c.date_submitted DESC, c.name ASC";

        //if Leadgen is specified
        if (!empty($lead_gens)) {
            $wherestring = $lead_gens;

            if ($where == "") {
                $where = " WHERE ";
            } else {
                $where .= " AND ";
            }

            if (count($lead_gens) > 1) {
                $wherestring = implode("','", $lead_gens);
                $where .= " l.id IN ('" . $wherestring . "') ";
            } else {
                $where .= " l.id = '" . $wherestring[0] . "'";
            }
        }

        $select .= ", l.name as leadgen_name ";
        $from .= " LEFT JOIN leadgen_tbl l ON c.leadgen = l.id ";
        // '", $advisers));die;
        //if Adviser is specified
        if (!empty($advisers)) {
            $wherestring = $advisers;

            if ($where == "") {
                $where = " WHERE ";
            } else {
                $where .= " AND ";
            }

            if (count($advisers) > 1) {
                $wherestring = implode("','", $advisers);
                $where .= "  a.id IN ('" . $wherestring . "') ";
            } else {
                $where .= " a.id = '" . $wherestring[0] . "'";
            }
        }

        $select .= ", a.name as adviser_name ";
        $from .= " LEFT JOIN adviser_tbl a ON c.assigned_to = a.id ";

        //if date is specified
        if ($date_from != "" && $date_to != "") {
            if ($where == "") {
                $where = " WHERE ";
            } else {
                $where .= " AND ";
            }
            $where .= " c.date_submitted <= '$date_to' AND c.date_submitted >= '$date_from' ";
        }

        if ($filterby != "none" && !empty($filterdata)) {
            if ($where == "") {
                $where = " WHERE ";
            } else {
                $where .= " AND ";
            }

            //trim whitespace from array
            $filterdata = array_map('trim', $filterdata);

            $wherestring = implode("','", $filterdata);
            $where .= " c.$filterby IN ('" . $wherestring . "')";
        }

        if ($source != "" && !empty($source)) {
            if ($where == "") {
                $where = " WHERE ";
            } else {
                $where .= " AND ";
            }

            $where .= " c.lead_by = '$source'";
        }

        //NOT IN ISSUED CLIENTS
        if ($where == "") {
            $where = " WHERE ";
        } else {
            $where .= " AND ";
        }
        $where .= " c.id NOT IN (SELECT name FROM issued_clients_tbl)";

        //END OF QUERY BUILDER
        $query = "$select$from$where$sort";

        $result = mysqli_query($con, $query);

        if ($result) {
            
            
            $objPHPExcel->setActiveSheetIndex($sheetIndex);
            $objWorkSheet = $objPHPExcel->getActiveSheet();
            // die;
            $objWorkSheet->setTitle("Unassigned Clients");
            $rowCount = 1;

            $data[0] = 'Lead Generator';
            $data[1] = 'Adviser';
            $data[2] = 'Name';
            $data[3] = 'Appointment Date';
            $data[4] = 'Address';
            $data[5] = 'City';
            $data[6] = 'Zip Code';
            $data[7] = 'Phone';
            $data[8] = 'Date Submitted';
            //
            $objPHPExcel->getActiveSheet()->fromArray($data, NULL, 'A' . $rowCount);
            $objPHPExcel->getActiveSheet()->getStyle("A1:I1")->getFont()->setBold(true);
            while ($row = mysqli_fetch_array($result)) {
                $rowCount++;
                //
                extract($row);
                //echo "<hr>$c_address<hr>";
                $data[0] = ($leadgen_name == "") ? "Self-Generated" : $leadgen_name;
                $data[1] = ($adviser_name == "") ? "Not Assigned" : $adviser_name;
                $data[2] = $client_name;
                $data[3] = convertToDate($appt_date);
                $data[4] = $c_address;
                $data[5] = $city;
                $data[6] = $zipcode;
                $data[7] = $phone;
                $data[8] = convertToDate($date_submitted);
                $objPHPExcel->getActiveSheet()->fromArray($data, NULL, 'A' . $rowCount);

                if ($rowCount % 2 == 0) {
                    $objPHPExcel->getActiveSheet()->getStyle('A' . $rowCount . ":I" . $rowCount)->applyFromArray(
                        array(
                            'fill' => array(
                                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                'color' => array('rgb' => 'DDDDDD')
                            )
                        )
                    );
                }
            }

            foreach (range('A', 'I') as $columnID) {
                $objPHPExcel->getActiveSheet()->getColumnDimension($columnID)->setAutoSize(true);
            }

            $objPHPExcel->getActiveSheet()->getStyle("A1:I$rowCount")->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $rowCount += 2;
            $sheetIndex++;
        } else {
            echo mysqli_connect_error();
        }
    }
}




if ($client_type == "Clients with No Policies" || $client_type == "All Clients") {
    if ($sheetIndex > 0) {
        $objWorkSheet = $objPHPExcel->createSheet($sheetIndex);
    }
    if($filterby == "specificmonth"){
        for($year = $year_from; $year <= $year_to; $year++){
            $date_from = substr_replace($temp_date_from, $year, 0, 4);
            $date_to = substr_replace($temp_date_to, $year, 0, 4);

            //QUERY BUILDER
            $select = "SELECT c.city as city, c.zipcode as zipcode, c.address as c_address, c.appt_time as phone, c.appt_date as appt_date, c.date_submitted as date_submitted, c.name as client_name, c.id as client_id";
            $from = " from clients_tbl c ";
            $where = "";
            $sort = " ORDER BY c.leadgen DESC, c.date_submitted DESC, c.name ASC";

            //if Leadgen is specified
            if (!empty($lead_gens)) {
                $wherestring = $lead_gens;

                if ($where == "") {
                    $where = " WHERE ";
                } else {
                    $where .= " AND ";
                }

                if (count($lead_gens) > 1) {
                    $wherestring = implode("','", $lead_gens);
                    $where .= " l.id IN ('" . $wherestring . "') ";
                } else {
                    $where .= " l.id = '" . $wherestring[0] . "'";
                }
            }

            $select .= ", l.name as leadgen_name ";
            $from .= " LEFT JOIN leadgen_tbl l ON c.leadgen = l.id ";

            //if Adviser is specified
            if (!empty($advisers)) {
                $wherestring = $advisers;

                if ($where == "") {
                    $where = " WHERE ";
                } else {
                    $where .= " AND ";
                }

                if (count($advisers) > 1) {
                    $wherestring = implode("','", $advisers);
                    $where .= "  a.id IN ('" . $wherestring . "') ";
                } else {
                    $where .= " a.id = '" . $wherestring[0] . "'";
                }
            }

            $select .= ", a.name as adviser_name ";
            $from .= " LEFT JOIN adviser_tbl a ON c.assigned_to = a.id ";

            //if date is specified
            if ($date_from != "" && $date_to != "") {
                if ($where == "") {
                    $where = " WHERE ";
                } else {
                    $where .= " AND ";
                }
                $where .= " c.date_submitted <= '$date_to' AND c.date_submitted >= '$date_from' ";
            }

            if ($source != "" && !empty($source)) {
                if ($where == "") {
                    $where = " WHERE ";
                } else {
                    $where .= " AND ";
                }

                $where .= " c.lead_by = '$source'";
            }

            //NOT IN ISSUED CLIENTS
            if ($where == "") {
                $where = " WHERE ";
            } else {
                $where .= " AND ";
            }
            $where .= " c.id NOT IN (SELECT name FROM issued_clients_tbl)";

            //END OF QUERY BUILDER
            $query = "$select$from$where$sort";
            // die;
            $result = mysqli_query($con, $query);
            if ($result) {
                $objPHPExcel->setActiveSheetIndex($sheetIndex);
                $objWorkSheet = $objPHPExcel->getActiveSheet();
                $objWorkSheet->setTitle("Clients");
                $rowCount = 1;

                $data[0] = 'Lead Generator';
                $data[1] = 'Adviser';
                $data[2] = 'Name';
                $data[3] = 'Appointment Date';
                $data[4] = 'Address';
                $data[5] = 'City';
                $data[6] = 'Zip Code';
                $data[7] = 'Phone';
                $data[8] = 'Date Submitted';
                //
                $objPHPExcel->getActiveSheet()->fromArray($data, NULL, 'A' . $rowCount);
                $objPHPExcel->getActiveSheet()->getStyle("A1:I1")->getFont()->setBold(true);
                while ($row = mysqli_fetch_array($result)) {
                    $rowCount++;
                    //
                    extract($row);
                    //echo "<hr>$c_address<hr>";
                    $data[0] = ($leadgen_name == "") ? "Self-Generated" : $leadgen_name;
                    $data[1] = ($adviser_name == "") ? "Not Assigned" : $adviser_name;
                    $data[2] = $client_name;
                    $data[3] = convertToDate($appt_date);
                    $data[4] = $c_address;
                    $data[5] = $city;
                    $data[6] = $zipcode;
                    $data[7] = $phone;
                    $data[8] = convertToDate($date_submitted);
                    $objPHPExcel->getActiveSheet()->fromArray($data, NULL, 'A' . $rowCount);

                    if ($rowCount % 2 == 0) {
                        $objPHPExcel->getActiveSheet()->getStyle('A' . $rowCount . ":I" . $rowCount)->applyFromArray(
                            array(
                                'fill' => array(
                                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                    'color' => array('rgb' => 'DDDDDD')
                                )
                            )
                        );
                    }
                }

                foreach (range('A', 'I') as $columnID) {
                    $objPHPExcel->getActiveSheet()->getColumnDimension($columnID)->setAutoSize(true);
                }

                $objPHPExcel->getActiveSheet()->getStyle("A1:I$rowCount")->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $rowCount += 2;
            } else {
                echo mysqli_connect_error();
            }
        }
        $sheetIndex++;
        $date_from = $temp_date_from;
        $date_to = $temp_date_to;
    } else {
        $select = "SELECT c.city as city, c.zipcode as zipcode, c.address as c_address, c.appt_time as phone, c.appt_date as appt_date, c.date_submitted as date_submitted, c.name as client_name, c.id as client_id";
            $from = " from clients_tbl c ";
            $where = "";
            $sort = " ORDER BY c.leadgen DESC, c.date_submitted DESC, c.name ASC";

            //if Leadgen is specified
            if (!empty($lead_gens)) {
                $wherestring = $lead_gens;

                if ($where == "") {
                    $where = " WHERE ";
                } else {
                    $where .= " AND ";
                }

                if (count($lead_gens) > 1) {
                    $wherestring = implode("','", $lead_gens);
                    $where .= " l.id IN ('" . $wherestring . "') ";
                } else {
                    $where .= " l.id = '" . $wherestring[0] . "'";
                }
            }

            $select .= ", l.name as leadgen_name ";
            $from .= " LEFT JOIN leadgen_tbl l ON c.leadgen = l.id ";

            //if Adviser is specified
            if (!empty($advisers)) {
                $wherestring = $advisers;

                if ($where == "") {
                    $where = " WHERE ";
                } else {
                    $where .= " AND ";
                }

                if (count($advisers) > 1) {
                    $wherestring = implode("','", $advisers);
                    $where .= "  a.id IN ('" . $wherestring . "') ";
                } else {
                    $where .= " a.id = '" . $wherestring[0] . "'";
                }
            }

            $select .= ", a.name as adviser_name ";
            $from .= " LEFT JOIN adviser_tbl a ON c.assigned_to = a.id ";

            //if date is specified
            if ($date_from != "" && $date_to != "") {
                if ($where == "") {
                    $where = " WHERE ";
                } else {
                    $where .= " AND ";
                }
                $where .= " c.date_submitted <= '$date_to' AND c.date_submitted >= '$date_from' ";
            }

            if ($filterby != "none" && !empty($filterdata)) {
                if ($where == "") {
                    $where = " WHERE ";
                } else {
                    $where .= " AND ";
                }

                //trim whitespace from array
                $filterdata = array_map('trim', $filterdata);

                $wherestring = implode("','", $filterdata);
                $where .= " c.$filterby IN ('" . $wherestring . "')";
            }

            if ($source != "" && !empty($source)) {
                if ($where == "") {
                    $where = " WHERE ";
                } else {
                    $where .= " AND ";
                }

                $where .= " c.lead_by = '$source'";
            }

            //NOT IN ISSUED CLIENTS
            if ($where == "") {
                $where = " WHERE ";
            } else {
                $where .= " AND ";
            }
            $where .= " c.id NOT IN (SELECT name FROM issued_clients_tbl)";

            //END OF QUERY BUILDER
            $query = "$select$from$where$sort";
            // die;
            $result = mysqli_query($con, $query);
            if ($result) {
                $objPHPExcel->setActiveSheetIndex($sheetIndex);
                $objWorkSheet = $objPHPExcel->getActiveSheet();
                $objWorkSheet->setTitle("Clients");
                $rowCount = 1;

                $data[0] = 'Lead Generator';
                $data[1] = 'Adviser';
                $data[2] = 'Name';
                $data[3] = 'Appointment Date';
                $data[4] = 'Address';
                $data[5] = 'City';
                $data[6] = 'Zip Code';
                $data[7] = 'Phone';
                $data[8] = 'Date Submitted';
                //
                $objPHPExcel->getActiveSheet()->fromArray($data, NULL, 'A' . $rowCount);
                $objPHPExcel->getActiveSheet()->getStyle("A1:I1")->getFont()->setBold(true);
                while ($row = mysqli_fetch_array($result)) {
                    $rowCount++;
                    //
                    extract($row);
                    //echo "<hr>$c_address<hr>";
                    $data[0] = ($leadgen_name == "") ? "Self-Generated" : $leadgen_name;
                    $data[1] = ($adviser_name == "") ? "Not Assigned" : $adviser_name;
                    $data[2] = $client_name;
                    $data[3] = convertToDate($appt_date);
                    $data[4] = $c_address;
                    $data[5] = $city;
                    $data[6] = $zipcode;
                    $data[7] = $phone;
                    $data[8] = convertToDate($date_submitted);
                    $objPHPExcel->getActiveSheet()->fromArray($data, NULL, 'A' . $rowCount);

                    if ($rowCount % 2 == 0) {
                        $objPHPExcel->getActiveSheet()->getStyle('A' . $rowCount . ":I" . $rowCount)->applyFromArray(
                            array(
                                'fill' => array(
                                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                    'color' => array('rgb' => 'DDDDDD')
                                )
                            )
                        );
                    }
                }

                foreach (range('A', 'I') as $columnID) {
                    $objPHPExcel->getActiveSheet()->getColumnDimension($columnID)->setAutoSize(true);
                }

                $objPHPExcel->getActiveSheet()->getStyle("A1:I$rowCount")->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $rowCount += 2;
            } else {
                echo mysqli_connect_error();
            }
    }
}


if ($client_type == "Clients with Submissions" || $client_type == "All Clients") {
    if ($sheetIndex > 0) {
        $objWorkSheet = $objPHPExcel->createSheet($sheetIndex);
    }
    if($filterby == "specificmonth"){
        for($year = $year_from; $year <= $year_to; $year++){
            $date_from = substr_replace($temp_date_from, $year, 0, 4);
            $date_to = substr_replace($temp_date_to, $year, 0, 4);
    
    //Submission CLIENTS

            $select = "SELECT c.city as city, c.zipcode as zipcode, c.address as c_address, c.appt_time as phone, c.appt_date as appt_date, c.date_submitted as date_submitted, c.name as client_name, c.id as client_id, s.timestamp as submission_timestamp";

            $from = " from clients_tbl c INNER JOIN submission_clients s ON c.id = s.client_id ";
            $where = "";
            $sort = " ORDER BY c.leadgen DESC, c.appt_date DESC, c.name ASC";

            //if Leadgen is specified
            if (!empty($lead_gens)) {
                $wherestring = $lead_gens;

                if ($where == "") {
                    $where = " WHERE ";
                } else {
                    $where .= " AND ";
                }

                if (count($lead_gens) > 1) {
                    $wherestring = implode("','", $lead_gens);
                    $where .= " l.id IN ('" . $wherestring . "') ";
                } else {
                    $where .= " l.id = '" . $wherestring[0] . "'";
                }
            }
            $select .= ", l.name as leadgen_name ";
            $from .= " LEFT JOIN leadgen_tbl l ON c.leadgen = l.id ";

            //if Adviser is specified
            if (!empty($advisers)) {
                $wherestring = $advisers;

                if ($where == "") {
                    $where = " WHERE ";
                } else {
                    $where .= " AND ";
                }

                if (count($advisers) > 1) {
                    $wherestring = implode("','", $advisers);
                    $where .= "  a.id IN ('" . $wherestring . "') ";
                } else {
                    $where .= " a.id = '" . $wherestring[0] . "'";
                }
            }
            $select .= ", a.name as adviser_name ";
            $from .= " LEFT JOIN adviser_tbl a ON c.assigned_to = a.id ";

            //if date is specified
            if ($date_from != "" && $date_to != "") {
                if ($where == "") {
                    $where = " WHERE ";
                } else {
                    $where .= " AND ";
                }
                $where .= " c.date_submitted <= '$date_to' AND c.date_submitted >= '$date_from' ";
            }

            if ($source != "" && !empty($source)) {
                if ($where == "") {
                    $where = " WHERE ";
                } else {
                    $where .= " AND ";
                }

                $where .= " c.lead_by = '$source'";
            }

            //NOT IN ISSUED CLIENTS
            if ($where == "") {
                $where = " WHERE ";
            } else {
                $where .= " AND ";
            }
            $where .= " c.id NOT IN (SELECT name FROM issued_clients_tbl)";
            //END OF QUERY BUILDER
            $query = "$select$from$where$sort";
            //echo $query;
            $result = mysqli_query($con, $query);
            if ($result) {
                $objPHPExcel->setActiveSheetIndex($sheetIndex);
                $objWorkSheet = $objPHPExcel->getActiveSheet();

                $objWorkSheet->setTitle("Clients with Submissions");
                $rowCount = 1;

                $data[0] = 'Lead Generator';
                $data[1] = 'Adviser';
                $data[2] = 'Name';
                $data[3] = 'Appointment Date';
                $data[4] = 'Address';
                $data[5] = 'City';
                $data[6] = 'Zip Code';
                $data[7] = 'Phone';
                $data[8] = 'Date Submitted';

                //
                $objPHPExcel->getActiveSheet()->fromArray($data, NULL, 'A' . $rowCount);
                $objPHPExcel->getActiveSheet()->getStyle("A1:I1")->getFont()->setBold(true);
                while ($row = mysqli_fetch_array($result)) {
                    $rowCount++;
                    //
                    extract($row);
                    $data[0] = ($leadgen_name == "") ? "Self-Generated" : $leadgen_name;
                    $data[1] = ($adviser_name == "") ? "Not Assigned" : $adviser_name;
                    $data[2] = $client_name;
                    $data[3] = convertToDate($appt_date);
                    $data[4] = $c_address;
                    $data[5] = $city;
                    $data[6] = $zipcode;
                    $data[7] = $phone;
                    $data[8] = timestampToDate($submission_timestamp);
                    $objPHPExcel->getActiveSheet()->fromArray($data, NULL, 'A' . $rowCount);

                    if ($rowCount % 2 == 0) {
                        $objPHPExcel->getActiveSheet()->getStyle('A' . $rowCount . ":I" . $rowCount)->applyFromArray(
                            array(
                                'fill' => array(
                                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                    'color' => array('rgb' => 'DDDDDD')
                                )
                            )
                        );
                    }
                }

                foreach (range('A', 'I') as $columnID) {
                    $objPHPExcel->getActiveSheet()->getColumnDimension($columnID)->setAutoSize(true);
                }


                $objPHPExcel->getActiveSheet()->getStyle("A1:I$rowCount")->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            } else {
                echo mysqli_connect_error();
            }
        }//QUERY BUILDER
        $date_from = $temp_date_from;
        $date_to = $temp_date_to;
    } else {
        $select = "SELECT c.city as city, c.zipcode as zipcode, c.address as c_address, c.appt_time as phone, c.appt_date as appt_date, c.date_submitted as date_submitted, c.name as client_name, c.id as client_id, s.timestamp as submission_timestamp";

        $from = " from clients_tbl c INNER JOIN submission_clients s ON c.id = s.client_id ";
        $where = "";
        $sort = " ORDER BY c.leadgen DESC, c.appt_date DESC, c.name ASC";

        //if Leadgen is specified
        if (!empty($lead_gens)) {
            $wherestring = $lead_gens;

            if ($where == "") {
                $where = " WHERE ";
            } else {
                $where .= " AND ";
            }

            if (count($lead_gens) > 1) {
                $wherestring = implode("','", $lead_gens);
                $where .= " l.id IN ('" . $wherestring . "') ";
            } else {
                $where .= " l.id = '" . $wherestring[0] . "'";
            }
        }
        $select .= ", l.name as leadgen_name ";
        $from .= " LEFT JOIN leadgen_tbl l ON c.leadgen = l.id ";

        //if Adviser is specified
        if (!empty($advisers)) {
            $wherestring = $advisers;

            if ($where == "") {
                $where = " WHERE ";
            } else {
                $where .= " AND ";
            }

            if (count($advisers) > 1) {
                $wherestring = implode("','", $advisers);
                $where .= "  a.id IN ('" . $wherestring . "') ";
            } else {
                $where .= " a.id = '" . $wherestring[0] . "'";
            }
        }
        $select .= ", a.name as adviser_name ";
        $from .= " LEFT JOIN adviser_tbl a ON c.assigned_to = a.id ";

        //if date is specified
        if ($date_from != "" && $date_to != "") {
            if ($where == "") {
                $where = " WHERE ";
            } else {
                $where .= " AND ";
            }
            $where .= " c.date_submitted <= '$date_to' AND c.date_submitted >= '$date_from' ";
        }


        if ($filterby != "none" && !empty($filterdata)) {
            if ($where == "") {
                $where = " WHERE ";
            } else {
                $where .= " AND ";
            }

            //trim whitespace from array
            $filterdata = array_map('trim', $filterdata);

            $wherestring = implode("','", $filterdata);
            $where .= " c.$filterby IN ('" . $wherestring . "')";
        }

        if ($source != "" && !empty($source)) {
            if ($where == "") {
                $where = " WHERE ";
            } else {
                $where .= " AND ";
            }

            $where .= " c.lead_by = '$source'";
        }

        //NOT IN ISSUED CLIENTS
        if ($where == "") {
            $where = " WHERE ";
        } else {
            $where .= " AND ";
        }
        $where .= " c.id NOT IN (SELECT name FROM issued_clients_tbl)";
        //END OF QUERY BUILDER
        $query = "$select$from$where$sort";
        //echo $query;
        $result = mysqli_query($con, $query);
        if ($result) {
            $objPHPExcel->setActiveSheetIndex($sheetIndex);
            $objWorkSheet = $objPHPExcel->getActiveSheet();

            $objWorkSheet->setTitle("Clients with Submissions");
            $rowCount = 1;

            $data[0] = 'Lead Generator';
            $data[1] = 'Adviser';
            $data[2] = 'Name';
            $data[3] = 'Appointment Date';
            $data[4] = 'Address';
            $data[5] = 'City';
            $data[6] = 'Zip Code';
            $data[7] = 'Phone';
            $data[8] = 'Date Submitted';

            //
            $objPHPExcel->getActiveSheet()->fromArray($data, NULL, 'A' . $rowCount);
            $objPHPExcel->getActiveSheet()->getStyle("A1:I1")->getFont()->setBold(true);
            while ($row = mysqli_fetch_array($result)) {
                $rowCount++;
                //
                extract($row);
                $data[0] = ($leadgen_name == "") ? "Self-Generated" : $leadgen_name;
                $data[1] = ($adviser_name == "") ? "Not Assigned" : $adviser_name;
                $data[2] = $client_name;
                $data[3] = convertToDate($appt_date);
                $data[4] = $c_address;
                $data[5] = $city;
                $data[6] = $zipcode;
                $data[7] = $phone;
                $data[8] = timestampToDate($submission_timestamp);
                $objPHPExcel->getActiveSheet()->fromArray($data, NULL, 'A' . $rowCount);

                if ($rowCount % 2 == 0) {
                    $objPHPExcel->getActiveSheet()->getStyle('A' . $rowCount . ":I" . $rowCount)->applyFromArray(
                        array(
                            'fill' => array(
                                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                'color' => array('rgb' => 'DDDDDD')
                            )
                        )
                    );
                }
            }

            foreach (range('A', 'I') as $columnID) {
                $objPHPExcel->getActiveSheet()->getColumnDimension($columnID)->setAutoSize(true);
            }


            $objPHPExcel->getActiveSheet()->getStyle("A1:I$rowCount")->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        } else {
            echo mysqli_connect_error();
        }
    }
}


if ($client_type == "Clients with Enforced Policies" || $client_type == "All Clients") {
    if ($sheetIndex > 0) {
        $objWorkSheet = $objPHPExcel->createSheet($sheetIndex);
    }

    if($filterby == "specificmonth"){
        for($year = $year_from; $year <= $year_to; $year++){
            $date_from = substr_replace($temp_date_from, $year, 0, 4);
            $date_to = substr_replace($temp_date_to, $year, 0, 4);
                //ISSUED CLIENTS

            //QUERY BUILDER
            $select = "SELECT c.city as city, s.deals as deals, c.zipcode as zipcode, c.address as c_address, c.appt_time as phone, c.appt_date as appt_date, c.date_submitted as date_submitted, c.name as client_name, c.id as client_id, i.date_issued as issued_date";

            $from = " from clients_tbl c INNER JOIN issued_clients_tbl i ON c.id = i.name INNER JOIN submission_clients s ON c.id = s.client_id ";
            $where = "";
            $sort = " ORDER BY c.leadgen DESC, c.appt_date DESC, c.name ASC";

            //if Leadgen is specified
            //if Leadgen is specified
            if (!empty($lead_gens)) {
                $wherestring = $lead_gens;

                if ($where == "") {
                    $where = " WHERE ";
                } else {
                    $where .= " AND ";
                }

                if (count($lead_gens) > 1) {
                    $wherestring = implode("','", $lead_gens);
                    $where .= " l.id IN ('" . $wherestring . "') ";
                } else {
                    $where .= " l.id = '" . $wherestring[0] . "'";
                }
            }
            $select .= ", l.name as leadgen_name ";
            $from .= " LEFT JOIN leadgen_tbl l ON c.leadgen = l.id ";

            //if Adviser is specified
            if (!empty($advisers)) {
                $wherestring = $advisers;

                if ($where == "") {
                    $where = " WHERE ";
                } else {
                    $where .= " AND ";
                }

                if (count($advisers) > 1) {
                    $wherestring = implode("','", $advisers);
                    $where .= "  a.id IN ('" . $wherestring . "') ";
                } else {
                    $where .= " a.id = '" . $wherestring[0] . "'";
                }
            }
            $select .= ", a.name as adviser_name ";
            $from .= " LEFT JOIN adviser_tbl a ON c.assigned_to = a.id ";

            //if date is specified
            if ($date_from != "" && $date_to != "") {
                if ($where == "") {
                    $where = " WHERE ";
                } else {
                    $where .= " AND ";
                }
                $where .= " c.date_submitted <= '$date_to' AND c.date_submitted >= '$date_from' ";
            }
            
            if ($source != "" && !empty($source)) {
                if ($where == "") {
                    $where = " WHERE ";
                } else {
                    $where .= " AND ";
                }

                $where .= " c.lead_by = '$source'";
            }

            //END OF QUERY BUILDER
            $query = "$select$from$where$sort";
            
            $result = mysqli_query($con, $query);
            if ($result) {
                $objPHPExcel->setActiveSheetIndex($sheetIndex);
                $objWorkSheet = $objPHPExcel->getActiveSheet();
                $objWorkSheet->setTitle("Issued Clients");
                $rowCount = 1;

                $data[0] = 'Lead Generator';
                $data[1] = 'Adviser';
                $data[2] = 'Name';
                $data[3] = 'Appointment Date';
                $data[4] = 'Address';
                $data[5] = 'City';
                $data[6] = 'Zip Code';
                $data[7] = 'Phone';
                $data[8] = 'Date Issued';

                //
                $objPHPExcel->getActiveSheet()->fromArray($data, NULL, 'A' . $rowCount);
                $objPHPExcel->getActiveSheet()->getStyle("A1:I1")->getFont()->setBold(true);
                while ($row = mysqli_fetch_array($result)) {

                    $deals = json_decode($row["deals"]);
                    $issued_deals = 0;
                    $oldest_cancellation = 9999999999999999999;
                    foreach ($deals as $deal) {
                        if (isset($deal->clawback_status)) {
                            if ($deal->clawback_status != "Cancelled") {
                                $issued_deals++;
                            }
                        } else {
                            $issued_deals++;
                        }
                    }

                    if ($issued_deals == 0)
                        continue;

                    $rowCount++;
                    //
                    extract($row);
                    $data[0] = ($leadgen_name == "") ? "Self-Generated" : $leadgen_name;
                    $data[1] = ($adviser_name == "") ? "Not Assigned" : $adviser_name;
                    $data[2] = $client_name;
                    $data[3] = convertToDate($appt_date);
                    $data[4] = $c_address;
                    $data[5] = $city;
                    $data[6] = $zipcode;
                    $data[7] = $phone;
                    $data[8] = convertToDate($issued_date);
                    $objPHPExcel->getActiveSheet()->fromArray($data, NULL, 'A' . $rowCount);

                    if ($rowCount % 2 == 0) {
                        $objPHPExcel->getActiveSheet()->getStyle('A' . $rowCount . ":I" . $rowCount)->applyFromArray(
                            array(
                                'fill' => array(
                                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                    'color' => array('rgb' => 'DDDDDD')
                                )
                            )
                        );
                    }
                }

                foreach (range('A', 'I') as $columnID) {
                    $objPHPExcel->getActiveSheet()->getColumnDimension($columnID)->setAutoSize(true);
                }


                $objPHPExcel->getActiveSheet()->getStyle("A1:I$rowCount")->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            } else {
                echo mysqli_connect_error();
            }
        }
        $date_from = $temp_date_from;
        $date_to = $temp_date_to;
    } else {
        $select = "SELECT c.city as city, s.deals as deals, c.zipcode as zipcode, c.address as c_address, c.appt_time as phone, c.appt_date as appt_date, c.date_submitted as date_submitted, c.name as client_name, c.id as client_id, i.date_issued as issued_date";

        $from = " from clients_tbl c INNER JOIN issued_clients_tbl i ON c.id = i.name INNER JOIN submission_clients s ON c.id = s.client_id ";
        $where = "";
        $sort = " ORDER BY c.leadgen DESC, c.appt_date DESC, c.name ASC";

        //if Leadgen is specified
        //if Leadgen is specified
        if (!empty($lead_gens)) {
            $wherestring = $lead_gens;

            if ($where == "") {
                $where = " WHERE ";
            } else {
                $where .= " AND ";
            }

            if (count($lead_gens) > 1) {
                $wherestring = implode("','", $lead_gens);
                $where .= " l.id IN ('" . $wherestring . "') ";
            } else {
                $where .= " l.id = '" . $wherestring[0] . "'";
            }
        }
        $select .= ", l.name as leadgen_name ";
        $from .= " LEFT JOIN leadgen_tbl l ON c.leadgen = l.id ";

        //if Adviser is specified
        if (!empty($advisers)) {
            $wherestring = $advisers;

            if ($where == "") {
                $where = " WHERE ";
            } else {
                $where .= " AND ";
            }

            if (count($advisers) > 1) {
                $wherestring = implode("','", $advisers);
                $where .= "  a.id IN ('" . $wherestring . "') ";
            } else {
                $where .= " a.id = '" . $wherestring[0] . "'";
            }
        }
        $select .= ", a.name as adviser_name ";
        $from .= " LEFT JOIN adviser_tbl a ON c.assigned_to = a.id ";

        //if date is specified
        if ($date_from != "" && $date_to != "") {
            if ($where == "") {
                $where = " WHERE ";
            } else {
                $where .= " AND ";
            }
            $where .= " c.date_submitted <= '$date_to' AND c.date_submitted >= '$date_from' ";
        }


        if ($filterby != "none" && !empty($filterdata)) {
            if ($where == "") {
                $where = " WHERE ";
            } else {
                $where .= " AND ";
            }

            //trim whitespace from array
            $filterdata = array_map('trim', $filterdata);

            $wherestring = implode("','", $filterdata);
            $where .= " c.$filterby IN ('" . $wherestring . "')";
        }
        
        if ($source != "" && !empty($source)) {
            if ($where == "") {
                $where = " WHERE ";
            } else {
                $where .= " AND ";
            }

            $where .= " c.lead_by = '$source'";
        }

        //END OF QUERY BUILDER
        $query = "$select$from$where$sort";
        $result = mysqli_query($con, $query);
        if ($result) {
            $objPHPExcel->setActiveSheetIndex($sheetIndex);
            $objWorkSheet = $objPHPExcel->getActiveSheet();
            $objWorkSheet->setTitle("Issued Clients");
            $rowCount = 1;

            $data[0] = 'Lead Generator';
            $data[1] = 'Adviser';
            $data[2] = 'Name';
            $data[3] = 'Appointment Date';
            $data[4] = 'Address';
            $data[5] = 'City';
            $data[6] = 'Zip Code';
            $data[7] = 'Phone';
            $data[8] = 'Date Issued';

            //
            $objPHPExcel->getActiveSheet()->fromArray($data, NULL, 'A' . $rowCount);
            $objPHPExcel->getActiveSheet()->getStyle("A1:I1")->getFont()->setBold(true);
            while ($row = mysqli_fetch_array($result)) {

                $deals = json_decode($row["deals"]);
                $issued_deals = 0;
                $oldest_cancellation = 9999999999999999999;
                foreach ($deals as $deal) {
                    if (isset($deal->clawback_status)) {
                        if ($deal->clawback_status != "Cancelled") {
                            $issued_deals++;
                        }
                    } else {
                        $issued_deals++;
                    }
                }

                if ($issued_deals == 0)
                    continue;

                $rowCount++;
                //
                extract($row);
                $data[0] = ($leadgen_name == "") ? "Self-Generated" : $leadgen_name;
                $data[1] = ($adviser_name == "") ? "Not Assigned" : $adviser_name;
                $data[2] = $client_name;
                $data[3] = convertToDate($appt_date);
                $data[4] = $c_address;
                $data[5] = $city;
                $data[6] = $zipcode;
                $data[7] = $phone;
                $data[8] = convertToDate($issued_date);
                $objPHPExcel->getActiveSheet()->fromArray($data, NULL, 'A' . $rowCount);

                if ($rowCount % 2 == 0) {
                    $objPHPExcel->getActiveSheet()->getStyle('A' . $rowCount . ":I" . $rowCount)->applyFromArray(
                        array(
                            'fill' => array(
                                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                'color' => array('rgb' => 'DDDDDD')
                            )
                        )
                    );
                }
            }

            foreach (range('A', 'I') as $columnID) {
                $objPHPExcel->getActiveSheet()->getColumnDimension($columnID)->setAutoSize(true);
            }


            $objPHPExcel->getActiveSheet()->getStyle("A1:I$rowCount")->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        } else {
            echo mysqli_connect_error();
        }
    }
}



if ($client_type == "Cancellations List" || $client_type == "All Clients") {
    if ($sheetIndex > 0) {
        $objWorkSheet = $objPHPExcel->createSheet($sheetIndex);
    }

    if($filterby == "specificmonth"){

        for($year = $year_from; $year <= $year_to; $year++){
            $date_from = substr_replace($temp_date_from, $year, 0, 4);
            $date_to = substr_replace($temp_date_to, $year, 0, 4);
        
            //CANCELLED CLIENTS

            //QUERY BUILDER
            $select = "SELECT c.city as city, s.deals as deals, c.zipcode as zipcode, c.address as c_address, c.appt_time as phone, c.appt_date as appt_date, c.date_submitted as date_submitted, c.name as client_name, c.id as client_id, i.date_issued as issued_date";

            $from = " from clients_tbl c INNER JOIN issued_clients_tbl i ON c.id = i.name INNER JOIN submission_clients s ON c.id = s.client_id ";
            $where = "";
            $sort = " ORDER BY c.leadgen DESC, c.appt_date DESC, c.name ASC";

            //if Leadgen is specified
            if (!empty($lead_gens)) {
                $wherestring = $lead_gens;

                if ($where == "") {
                    $where = " WHERE ";
                } else {
                    $where .= " AND ";
                }

                if (count($lead_gens) > 1) {
                    $wherestring = implode("','", $lead_gens);
                    $where .= " l.id IN ('" . $wherestring . "') ";
                } else {
                    $where .= " l.id = '" . $wherestring[0] . "'";
                }
            }
            $select .= ", l.name as leadgen_name ";
            $from .= " LEFT JOIN leadgen_tbl l ON c.leadgen = l.id ";

            //if Adviser is specified
            if (!empty($advisers)) {
                $wherestring = $advisers;

                if ($where == "") {
                    $where = " WHERE ";
                } else {
                    $where .= " AND ";
                }

                if (count($advisers) > 1) {
                    $wherestring = implode("','", explode(",", $advisers));
                    $where .= "  a.id IN ('" . $wherestring . "') ";
                } else {
                    $where .= " a.id = '" . $wherestring[0] . "'";
                }
            }
            $select .= ", a.name as adviser_name ";
            $from .= " LEFT JOIN adviser_tbl a ON c.assigned_to = a.id ";

            //if date is specified
            if ($date_from != "" && $date_to != "") {
                if ($where == "") {
                    $where = " WHERE ";
                } else {
                    $where .= " AND ";
                }
                $where .= " c.date_submitted <= '$date_to' AND c.date_submitted >= '$date_from' ";
            }
            
            if ($source != "" && !empty($source)) {
                if ($where == "") {
                    $where = " WHERE ";
                } else {
                    $where .= " AND ";
                }

                $where .= " c.lead_by = '$source'";
            }
            //END OF QUERY BUILDER
            $query = "$select$from$where$sort";
            //echo $query;
            $result = mysqli_query($con, $query);
            if ($result) {


                $objPHPExcel->setActiveSheetIndex($sheetIndex);
                $objWorkSheet = $objPHPExcel->getActiveSheet();
                $objWorkSheet->setTitle("Cancelled Clients");
                $rowCount = 1;

                $data[0] = 'Lead Generator';
                $data[1] = 'Adviser';
                $data[2] = 'Name';
                $data[3] = 'Appointment Date';
                $data[4] = 'Address';
                $data[5] = 'City';
                $data[6] = 'Zip Code';
                $data[7] = 'Phone';
                $data[8] = 'Cancellation Date';

                //
                $objPHPExcel->getActiveSheet()->fromArray($data, NULL, 'A' . $rowCount);
                $objPHPExcel->getActiveSheet()->getStyle("A1:I1")->getFont()->setBold(true);
                while ($row = mysqli_fetch_array($result)) {
                    //Check if client is cancelled
                    $deals = json_decode($row["deals"]);
                    $issued_deals = 0;
                    $oldest_cancellation = 9999999999999999999;
                    foreach ($deals as $deal) {
                        if (isset($deal->clawback_status)) {
                            if ($deal->clawback_status != "Cancelled") {
                                $issued_deals++;
                            }
                            else {
                                if($deal->clawback_date < $oldest_cancellation)
                                    $oldest_cancellation = $deal->clawback_date;
                            }
                        } else {
                            $issued_deals++;
                        }
                    }

                    if ($issued_deals > 0)
                        continue;

                    $rowCount++;
                    //
                    extract($row);
                    $data[0] = ($leadgen_name == "") ? "Self-Generated" : $leadgen_name;
                    $data[1] = ($adviser_name == "") ? "Not Assigned" : $adviser_name;
                    $data[2] = $client_name;
                    $data[3] = convertToDate($appt_date);
                    $data[4] = $c_address;
                    $data[5] = $city;
                    $data[6] = $zipcode;
                    $data[7] = $phone;
                    $data[8] = convertToDate($oldest_cancellation);
                    
                    $objPHPExcel->getActiveSheet()->fromArray($data, NULL, 'A' . $rowCount);

                    if ($rowCount % 2 == 0) {
                        $objPHPExcel->getActiveSheet()->getStyle('A' . $rowCount . ":I" . $rowCount)->applyFromArray(
                            array(
                                'fill' => array(
                                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                    'color' => array('rgb' => 'DDDDDD')
                                )
                            )
                        );
                    }
                }

                foreach (range('A', 'I') as $columnID) {
                    $objPHPExcel->getActiveSheet()->getColumnDimension($columnID)->setAutoSize(true);
                }


                $objPHPExcel->getActiveSheet()->getStyle("A1:I$rowCount")->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            } else {
                echo mysqli_connect_error();
            }
        }
    } else {
        $select = "SELECT c.city as city, s.deals as deals, c.zipcode as zipcode, c.address as c_address, c.appt_time as phone, c.appt_date as appt_date, c.date_submitted as date_submitted, c.name as client_name, c.id as client_id, i.date_issued as issued_date";

        $from = " from clients_tbl c INNER JOIN issued_clients_tbl i ON c.id = i.name INNER JOIN submission_clients s ON c.id = s.client_id ";
        $where = "";
        $sort = " ORDER BY c.leadgen DESC, c.appt_date DESC, c.name ASC";

        //if Leadgen is specified
        if (!empty($lead_gens)) {
            $wherestring = $lead_gens;

            if ($where == "") {
                $where = " WHERE ";
            } else {
                $where .= " AND ";
            }

            if (count($lead_gens) > 1) {
                $wherestring = implode("','", $lead_gens);
                $where .= " l.id IN ('" . $wherestring . "') ";
            } else {
                $where .= " l.id = '" . $wherestring[0] . "'";
            }
        }
        $select .= ", l.name as leadgen_name ";
        $from .= " LEFT JOIN leadgen_tbl l ON c.leadgen = l.id ";

        //if Adviser is specified
        if (!empty($advisers)) {
            $wherestring = $advisers;

            if ($where == "") {
                $where = " WHERE ";
            } else {
                $where .= " AND ";
            }

            if (count($advisers) > 1) {
                $wherestring = implode("','", explode(",", $advisers));
                $where .= "  a.id IN ('" . $wherestring . "') ";
            } else {
                $where .= " a.id = '" . $wherestring[0] . "'";
            }
        }
        $select .= ", a.name as adviser_name ";
        $from .= " LEFT JOIN adviser_tbl a ON c.assigned_to = a.id ";

        //if date is specified
        if ($date_from != "" && $date_to != "") {
            if ($where == "") {
                $where = " WHERE ";
            } else {
                $where .= " AND ";
            }
            $where .= " c.date_submitted <= '$date_to' AND c.date_submitted >= '$date_from' ";
        }


        if ($filterby != "none" && !empty($filterdata)) {
            if ($where == "") {
                $where = " WHERE ";
            } else {
                $where .= " AND ";
            }

            //trim whitespace from array
            $filterdata = array_map('trim', $filterdata);

            $wherestring = implode("','", $filterdata);
            $where .= " c.$filterby IN ('" . $wherestring . "')";
        }
        
        if ($source != "" && !empty($source)) {
            if ($where == "") {
                $where = " WHERE ";
            } else {
                $where .= " AND ";
            }

            $where .= " c.lead_by = '$source'";
        }
        //END OF QUERY BUILDER
        $query = "$select$from$where$sort";
        //echo $query;
        $result = mysqli_query($con, $query);
        if ($result) {


            $objPHPExcel->setActiveSheetIndex($sheetIndex);
            $objWorkSheet = $objPHPExcel->getActiveSheet();
            $objWorkSheet->setTitle("Cancelled Clients");
            $rowCount = 1;

            $data[0] = 'Lead Generator';
            $data[1] = 'Adviser';
            $data[2] = 'Name';
            $data[3] = 'Appointment Date';
            $data[4] = 'Address';
            $data[5] = 'City';
            $data[6] = 'Zip Code';
            $data[7] = 'Phone';
            $data[8] = 'Cancellation Date';

            //
            $objPHPExcel->getActiveSheet()->fromArray($data, NULL, 'A' . $rowCount);
            $objPHPExcel->getActiveSheet()->getStyle("A1:I1")->getFont()->setBold(true);
            while ($row = mysqli_fetch_array($result)) {
                //Check if client is cancelled
                $deals = json_decode($row["deals"]);
                $issued_deals = 0;
                $oldest_cancellation = 9999999999999999999;
                foreach ($deals as $deal) {
                    if (isset($deal->clawback_status)) {
                        if ($deal->clawback_status != "Cancelled") {
                            $issued_deals++;
                        }
                        else {
                            if($deal->clawback_date < $oldest_cancellation)
                                $oldest_cancellation = $deal->clawback_date;
                        }
                    } else {
                        $issued_deals++;
                    }
                }

                if ($issued_deals > 0)
                    continue;

                $rowCount++;
                //
                extract($row);
                $data[0] = ($leadgen_name == "") ? "Self-Generated" : $leadgen_name;
                $data[1] = ($adviser_name == "") ? "Not Assigned" : $adviser_name;
                $data[2] = $client_name;
                $data[3] = convertToDate($appt_date);
                $data[4] = $c_address;
                $data[5] = $city;
                $data[6] = $zipcode;
                $data[7] = $phone;
                $data[8] = convertToDate($oldest_cancellation);
                
                $objPHPExcel->getActiveSheet()->fromArray($data, NULL, 'A' . $rowCount);

                if ($rowCount % 2 == 0) {
                    $objPHPExcel->getActiveSheet()->getStyle('A' . $rowCount . ":I" . $rowCount)->applyFromArray(
                        array(
                            'fill' => array(
                                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                'color' => array('rgb' => 'DDDDDD')
                            )
                        )
                    );
                }
            }

            foreach (range('A', 'I') as $columnID) {
                $objPHPExcel->getActiveSheet()->getColumnDimension($columnID)->setAutoSize(true);
            }


            $objPHPExcel->getActiveSheet()->getStyle("A1:I$rowCount")->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        } else {
            echo mysqli_connect_error();
        }
    }
}

$objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
$objWriter->setOffice2003Compatibility(true);
/*
//Output File
*/
header("Pragma: public");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Content-Type: application/force-download");
header("Content-Type: application/octet-stream");
header("Content-Type: application/download");
header("Content-Transfer-Encoding: binary ");
header("Content-Disposition: attachment;filename=$filename.xlsx");


$objWriter->save('php://output');
function debuggingLog($header = "Logged Data", $variable)
{
    //SET TO TRUE WHEN DEBUGGING SET TO FALSE WHEN NOT
    $isDebuggerActive = false;
    if (!$isDebuggerActive)
        return;
    $op = "<br>";
    $op .=  $header;
    echo $op . "<hr>" . "<pre>";
    var_dump($variable);
    echo "</pre>" . "<hr>";
}
