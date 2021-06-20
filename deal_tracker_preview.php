<?php
date_default_timezone_set('Pacific/Auckland');
ob_start();
session_start();
require('fpdf/mc_table.php');

require('database.php');
include_once('libs/api/classes/general.class.php');
include_once('libs/api/controllers/Deal.controller.php');
$dealController = new DealController();
$generalController = new General();

// session_start();
//post

class PDF extends PDF_MC_Table
{
    public function Footer()
    {
        global $fsp_num;
        global $name;

        $this->SetY(-15);
        $this->SetFillColor(0, 0, 0);
        $this->Rect(5, 342, 330, .5, 'FD');
        $this->SetFont('Helvetica', '', 10);
        $this->SetTextColor(0, 0, 0);
        $this->Cell(100, 10, 'Deal Tracker-' . $name, 0, 0, 'L');
        $this->AliasNbPages('{totalPages}');
        $this->Cell(248, 10, 'Page ' . $this->PageNo() . ' of ' . '{totalPages}', 0, 1, 'R');
    }

    public function Header()
    {
        $this->SetFillColor(0, 0, 0);
        $this->Image('logo_vertical.png', 165, 5, 30);
        $this->Rect(10, 25, 330, .5, 'FD');
        $this->SetFont('Helvetica', 'B', 18);
        $this->SetTextColor(0, 0, 0);
        $this->Cell(0, 20, '', '0', '1', 'C');
        $this->SetTextColor(0, 0, 0);
        $this->SetFont('Helvetica', 'B', 10);
        $this->SetFillColor(224, 224, 224);
    }

    public function getPage()
    {
        return $this->PageNo();
    }
}

//ifs

//FORMULAS end

//convert to 2 decimal number
function convertNum($x)
{
    return number_format($x, 2, '.', ',');
}

function convertNegNum($x)
{
    $x = $x * -1;

    return number_format($x, 2, '.', ',');
}

function removeparent($x)
{
    return preg_replace("/\([^)]+\)/", '', $x); // 'ABC ';
}

//retrieving
$adv_name = $_POST['adv_name'] ?? '';				//Adviser name
$adviser_id = $_POST['adviser_id'] ?? '';		//Adviser id
$date_from = $_POST['date_from'] ?? '';			//Date from
$date_created = $_POST['date_created'] ?? '';	//Invoice Date
$due_date = $_POST['due_date'] ?? '';				//Due date
$pay_date = $_POST['pay_date'] ?? '';				//Due date
$until = $_POST['until'] ?? '';				//Due date
$note_entries = $_POST['notes'] ?? '';						//Date until

$created_by = $_SESSION['myuserid'];

$creator_query = "Select u.*, p.full_name from users u LEFT JOIN personal_data p ON u.linked_id = p.id where u.id = $created_by";
$creator_result = mysqli_query($con, $creator_query) or die('Could not look up user information; ' . mysqli_error($con));
$creator_row = mysqli_fetch_array($creator_result);

$created_by = $creator_row['full_name'];
//Production Desc
//Test Desc
//$desc=$_POST['desc'];
$date_created = date('d/m/Y');

//Fetch Adviser Data
$searchadv = "SELECT *, a.id as adviser_id, a.name as adviser_name, t.name as team_name FROM adviser_tbl a LEFT JOIN teams t ON a.team_id = t.id WHERE a.id='$adviser_id'";
$search = mysqli_query($con, $searchadv) or die('Could not look up user information; ' . mysqli_error($con));
$rows = mysqli_fetch_array($search);

$report_data = new stdClass();
//uncomment this after debugging
$report_data->adviser_data = new stdClass();

//Extract Data
/*
    $address=$rows["address"];
    $leads=$rows["leads"];
    $issued=$rows["bonus"];
    $fsp_num=$rows['fsp_num'];
    $email=$rows['email'];
    $adviser_name = $rows["adviser_name"];
    $adv_name = $adviser_name;
 */

$report_data->adviser_data->id = $rows['adviser_id'];
$report_data->adviser_data->name = $rows['adviser_name'];
$report_data->adviser_data->team_name = $rows['team_name'];
$report_data->adviser_data->address = $rows['address'];
$report_data->adviser_data->leads = $rows['leads'];
$report_data->adviser_data->issued = $rows['bonus'];
$report_data->adviser_data->fsp_num = $rows['fsp_num'];
$report_data->adviser_data->email = $rows['email'];
$report_data->adviser_data->company_name = $rows['company_name'];

$adviser_team = (! empty($report_data->adviser_data->team_name)) ? $report_data->adviser_data->team_name : 'Not Assigned';

$name = $report_data->adviser_data->name;

$date_from = substr($date_from, 6, 4) . substr($date_from, 3, 2) . substr($date_from, 0, 2);
$until = substr($until, 6, 4) . substr($until, 3, 2) . substr($until, 0, 2);

$d1 = new DateTime($date_from); // Y-m-d
$d2 = new DateTime($until);

$period_covered_title = $d1->format('d/m/Y') . '-' . $d2->format('d/m/Y');

//fetch deals

$report_data->total_kiwisaver_commission = 0;
$report_data->total_kiwisaver_gst = 0;
$report_data->total_kiwisaver_balance = 0;
$report_data->kiwisaver_deals = [];

$dealController = new DealController();
$kiwisaver_deals = $dealController->GetKiwiSaversIssuedByAdviserInDateRange($report_data->adviser_data->id, $date_from, $until, false);

while ($row = $kiwisaver_deals->fetch_assoc()) {
    $report_data->kiwisaver_deals[] = [
        'insured_name' => $row['insured_name'],
        'client_name' => $row['client_name'],
        'source_adviser' => $row['adviser_name'],
        'commission' => $row['commission'],
        'gst' => $row['gst'],
        'balance' => $row['balance'],
        'date' => date('d/m/Y', strtotime($row['issue_date'])),
    ];

    $report_data->total_kiwisaver_commission += $row['commission'];
    $report_data->total_kiwisaver_gst += $row['gst'];
    $report_data->total_kiwisaver_balance += $row['balance'];
}

$search_leads = "SELECT *, c.name as client_name, l.name as source FROM clients_tbl c LEFT JOIN submission_clients s ON c.id = s.client_id LEFT JOIN leadgen_tbl l ON l.id = c.leadgen WHERE assigned_to='$adviser_id' AND c.status!='Cancelled'";
//echo $search_leads . "<hr>";
$leads_exec = mysqli_query($con, $search_leads) or die('Could not look up user information; ' . mysqli_error($con));

$report_data->total_pending_api = 0;
$report_data->total_issued_api = 0;
$report_data->total_cancelled_api = 0;
$report_data->pending_deals = [];
$report_data->issued_deals = [];
$report_data->cancelled_deals = [];

while ($row = mysqli_fetch_array($leads_exec)) {
    if (! isset($row['deals'])) {
        continue;
    }
    $source = $adv_name;

    if (! empty($row['source'])) {
        $source = $row['source'];
    }

    $deals = json_decode($row['deals']);

    foreach ($deals as $deal) {
        $life_insured = $row['client_name'];

        if (! isset($deal->refund_status)) {
            $deal->refund_status = 'No';
        }

        if (! empty($deal->life_insured)) {
            $life_insured .= ', ' . $deal->life_insured;
        }

        if ('Issued' == $deal->status) {
            if ('Not Paid' == $deal->commission_status) {
                if ($deal->date_issued <= $until) {
                    $report_data->issued_deals[] = [
                        'date' => $deal->date_issued,
                        'life_insured' => $life_insured,
                        'policy_number' => $deal->policy_number,
                        'company' => $deal->company,
                        'source' => $source,
                        'api' => $deal->issued_api,
                        'compliance_status' => $deal->compliance_status,
                        'notes' => $deal->notes,
                        'deal' => $deal,
                    ];
                    $report_data->total_issued_api += $deal->issued_api;
                }
            }

            //Add to Cancelled Deals
            if (isset($deal->clawback_status)) {
                if ('None' != $deal->clawback_status) {
                    if ('No' == $deal->refund_status) {
                        if ($deal->clawback_date <= $until) {
                            $report_data->cancelled_deals[] = [
                                'date' => $deal->clawback_date,
                                'issued_date' => $deal->date_issued,
                                'life_insured' => $life_insured,
                                'policy_number' => $deal->policy_number,
                                'company' => $deal->company,
                                'api' => $deal->clawback_api,
                                'clawback_status' => $deal->clawback_status,
                                'notes' => $deal->clawback_notes,
                                'deal' => $deal,
                            ];

                            $report_data->total_cancelled_api += $deal->clawback_api;
                        }
                    }
                }
            }
        }
    }
}

//Fetch payables
$query = "SELECT * FROM transactions WHERE adviser_id = $adviser_id ORDER BY date DESC";
$displayquery = mysqli_query($con, $query) or die('Could not look up user information; ' . mysqli_error($con));
$report_data->total_leads_payable = 0;
$report_data->total_issued_payable = 0;
$report_data->total_balance = 0;
while ($row = mysqli_fetch_assoc($displayquery)) {
    extract($row);
    $status = CheckTransactionStatus($status);

    switch ($status) {
        case 'Manual Billed Assigned Leads':
            $report_data->total_leads_payable += $number_of_leads;

            break;
        case 'Manual Billed Issued Leads':
            $report_data->total_issued_payable += $number_of_leads;

            break;
        case 'Billed Assigned Leads':
            $report_data->total_leads_payable += $number_of_leads;

            break;
        case 'Billed Issued Leads':
            $report_data->total_issued_payable += $number_of_leads;

            break;
        case 'Paid Issued Leads':
            $report_data->total_issued_payable -= $number_of_leads;

            break;
        default:
            $report_data->total_leads_payable -= $number_of_leads;

            break;
    }

    $report_data->total_balance += $amount;
}

$query = "SELECT * FROM clients_tbl WHERE assigned_to = $adviser_id AND date_submitted>=$date_from AND date_submitted<=$until";
$displayquery = mysqli_query($con, $query) or die('Could not look up user information; ' . mysqli_error($con));
$report_data->assigned_leads_for_period = mysqli_num_rows($displayquery);

$query = "SELECT * FROM issued_clients_tbl WHERE assigned_to = $adviser_id AND date_issued>=$date_from AND date_issued<=$until";
$displayquery = mysqli_query($con, $query) or die('Could not look up user information; ' . mysqli_error($con));
$report_data->issued_leads_for_period = mysqli_num_rows($displayquery);

function CheckTransactionStatus($status)
{
    $issued = false !== stripos($status, 'Billed Issued Leads');
    $assigned = false !== stripos($status, 'Billed Assigned Leads');
    $op = $status;

    if ($issued) {
        $op = 'Billed Issued Leads';
    } elseif ($assigned) {
        $op = 'Billed Assigned Leads';
    }

    return $op;
}

//Report Data

function addToDeals($deal, $status)
{
    $add_to = '';

    switch ($status) {
        case 'Issued':

            break;
    }
}

$search_issued = "SELECT * FROM issued_clients_tbl i LEFT JOIN clients_tbl c ON i.name = c.id WHERE c.assigned_to='$adviser_id' AND  i.date_issued<='$until' AND i.date_issued>=$date_from AND c.lead_by!='Telemarketer'";
//Remove c.lead_by!='Telemarketer' to include leads from telemarketers
$issued_exec = mysqli_query($con, $search_issued) or die('Could not look up user information; ' . mysqli_error($con));
$count_issued = mysqli_num_rows($issued_exec);

$pdf = new PDF('L', 'mm', 'Legal');
$x = $pdf->GetX();
$y = $pdf->GetY();

//page 1
// $pdf->AddPage('L', 'Legal');
$pdf->AddPage();

$pdf->SetFillColor(224, 224, 224);
$pdf->SetFont('Helvetica', 'B', 20);
$pdf->Cell(330, 10, 'Deal Tracker', '0', '1', 'C', 'true');

$pdf->SetFont('Helvetica', 'B', 15);
$pdf->Cell(17, 10, 'Name:', '0', '0', 'L');
$pdf->SetFont('Helvetica', '', 15);
$pdf->Cell(78, 10, $report_data->adviser_data->name, '0', '0', 'L');
$pdf->Cell(82, 10, '', '0', '0', 'R');
$pdf->SetFont('Helvetica', 'B', 15);
$pdf->Cell(16, 10, 'Team:', '0', '0', 'L');
$pdf->SetFont('Helvetica', '', 15);
$pdf->Cell(84, 10, "$adviser_team", '0', '1', 'L');

$pdf->SetFont('Helvetica', 'B', 15);
$pdf->Cell(35, 10, 'FSP Number:', '0', '0', 'L');
$pdf->SetFont('Helvetica', '', 15);
$pdf->Cell(60, 10, $report_data->adviser_data->fsp_num, '0', '0', 'L');

$pdf->Cell(82, 10, '', '0', '0', 'R');
$pdf->SetFont('Helvetica', 'B', 15);
$pdf->Cell(42, 10, 'Period Covered:', '0', '0', 'L');
$pdf->SetFont('Helvetica', '', 15);
$pdf->Cell(58, 10, "$period_covered_title", '0', '1', 'L');

$pdf->SetFont('Helvetica', 'B', 15);
$pdf->Cell(17, 10, 'Email:', '0', '0', 'L');
$pdf->SetFont('Helvetica', '', 15);
$pdf->Cell(78, 10, $report_data->adviser_data->email, '0', '0', 'L');
$pdf->Cell(82, 10, '', '0', '0', 'R');
$pdf->SetFont('Helvetica', 'B', 15);
$pdf->Cell(25, 10, 'Pay Date:', '0', '0', 'L');
$pdf->SetFont('Helvetica', '', 15);
$pdf->Cell(75, 10, "$pay_date", '0', '1', 'L');

$pdf->SetFont('Helvetica', 'B', 14);
$pdf->Cell(25, 10, 'Company:', '0', '0', 'L');
$pdf->SetFont('Helvetica', '', 14);
$pdf->Cell(70, 10, $report_data->adviser_data->company_name, '0', '0', 'L');

$pdf->Cell(82, 10, '', '0', '0', 'R');
$pdf->SetFont('Helvetica', 'B', 14);
$pdf->Cell(27, 10, 'Report By:', '0', '0', 'L');
$pdf->SetFont('Helvetica', '', 14);
$pdf->Cell(73, 10, "$created_by", '0', '1', 'L');

//Space
AddLineSpace($pdf);

$pdf->SetFont('Helvetica', 'B', 15);
$pdf->SetFillColor(224, 224, 224);
$pdf->Cell(330, 10, 'NOTES', 0, 1, 'C', 'true');

$show_desc = '';

//formula
if (count($note_entries) > 0) {
    if (! empty($note_entries[0])) {
        $pdf->SetFont('Helvetica', '', 13);
        $ctr = 1;

        foreach ($note_entries as $key => $note) {
            $pdf->SetFillColor(255, 255, 255);
            $fill = (($ctr % 2) === 0) ? true : false;

            if (($ctr % 2) === 0) {
                $pdf->SetFillColor(235, 235, 235);
            }
            $pdf->MultiCell(330, 10, $ctr . '. ' . $note, 0, 'L', $fill);
            $ctr++;
            $note_entries[$key] = str_replace("\n", '<br>', $note);
        }
    } else {
        $pdf->Cell(330, 10, 'No Entries Recorded.', 0, 1, 'C');
    }
} else {
    $pdf->Cell(330, 10, 'No Entries Recorded.', 0, 1, 'C');
}

//Space
AddLineSpace($pdf);

$pdf->SetFont('Helvetica', 'B', 15);
$pdf->SetFillColor(224, 224, 224);
$pdf->Cell(330, 10, 'LEADS', 0, 1, 'C', 'true');

$pdf->SetFont('Helvetica', 'B', 13);
$pdf->Cell(34, 10, 'Rate Per Lead:', '0', '0', 'L');
$pdf->SetFont('Helvetica', '', 13);
$pdf->Cell(61, 10, '$' . $report_data->adviser_data->leads, '0', '0', 'L');
$pdf->Cell(82, 10, '', '0', '0', 'R');

$pdf->SetFont('Helvetica', 'B', 13);
$pdf->Cell(50, 10, 'Rate Per Issued Lead:', '0', '', 'L');
$pdf->SetFont('Helvetica', '', 13);
$pdf->Cell(50, 10, '$' . $report_data->adviser_data->issued, '0', '1', 'L');

$pdf->SetFont('Helvetica', 'B', 13);
$pdf->Cell(33, 10, 'Total Balance:', '0', '0', 'L');
$pdf->SetFont('Helvetica', '', 13);
$pdf->Cell(62, 10, '$' . number_format($report_data->total_balance, 2), '0', '0', 'L');
$pdf->Cell(82, 10, '', '0', '0', 'R');

$pdf->SetFont('Helvetica', 'B', 13);
$pdf->Cell(70, 10, 'Assigned Leads for the Period:', '0', '0', 'L');
$pdf->SetFont('Helvetica', '', 13);
$pdf->Cell(30, 10, "$report_data->assigned_leads_for_period", '0', '1', 'L');

$pdf->SetFont('Helvetica', 'B', 13);
$pdf->Cell(47, 10, 'Total Leads Payable:', '0', '0', 'L');
$pdf->SetFont('Helvetica', '', 13);
$pdf->Cell(48, 10, "$report_data->total_leads_payable", '0', '0', 'L');
$pdf->Cell(82, 10, '', '0', '0', 'R');
$pdf->SetFont('Helvetica', 'B', 13);
$pdf->Cell(63, 10, 'Leads Issued for the Period:', '0', '0', 'L');
$pdf->SetFont('Helvetica', '', 13);
$pdf->Cell(37, 10, "$report_data->issued_leads_for_period", '0', '1', 'L');

$pdf->SetFont('Helvetica', 'B', 13);
$pdf->Cell(48, 10, 'Total Issued Payable:', '0', '0', 'L');
$pdf->SetFont('Helvetica', '', 13);
$pdf->Cell(47, 10, "$report_data->total_issued_payable", '0', '0', 'L');
$pdf->Cell(82, 10, '', '0', '0', 'R');
$pdf->SetFont('Helvetica', 'B', 13);
$pdf->Cell(82, 10, 'KiwiSaver Enrolments for the Period:', '0', '0', 'L');
$pdf->SetFont('Helvetica', '', 13);
$pdf->Cell(18, 10, count($report_data->kiwisaver_deals), '0', '1', 'L');

if (count($report_data->issued_deals) > 0) {

    //Space
    AddLineSpace($pdf);
	$pdf->AddPage();

    //Production
    $pdf->SetFont('Helvetica', 'B', 14);
    $pdf->SetFillColor(224, 224, 224);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell(330, 10, 'PRODUCTION', 0, 1, 'C', 'true');
    /*
        $pdf->SetFont('Helvetica','',9);
        $pdf->SetFillColor(224,224,224);
        $pdf->SetTextColor(0,0,0);
        $pdf->MultiCell(200,10,$search_leads, 0, 1,'C','true');
     */

    $pdf->SetFont('Helvetica', 'U', 12);
    $pdf->SetFillColor(0, 0, 0);
    $pdf->SetTextColor(0, 0, 0);

    $pdf->SetWidths([40, 30, 30, 30, 30, 30, 30, 30, 30, 50]);
    $pdf->Row(
        [
            'Life Insured',
            'Policy #',
            'Co.',
            'Source',
            'Issue Date',
            'API',
            'Record Keeping',
            'Comp. Admin',
            'Comp. CO',
            'Notes',
        ],
        false,
        [224, 224, 224]
    );

    $pdf->SetFont('Helvetica', '', 9);
    $ctr = 0;
    $rep_data = json_encode($report_data->issued_deals);
    $report_data->issued_deals = json_decode($rep_data, true);
    usort($report_data->issued_deals, 'sortFunction');

    foreach ($report_data->issued_deals as $deal) {
        extract($deal);

        if ('' == $source) {
            $source = $report_data->adviser_data->name;
        }

        $audit_status = (isset($deal['audit_status'])) ? $deal['audit_status'] : 'Pending';

        $pdf->SetFillColor(224, 224, 224);
        $fill = (($ctr % 2) === 0) ? true : false;
        $pdf->Row(
            [
                $life_insured,
                $policy_number,
                $company,
                $source,
                NZEntryToDateTime($date),
                '$' . number_format($api, 2),
				$deal['record_keeping'] ?? '',
                $compliance_status,
                $audit_status,
                str_replace('<br>', "\r\n", $notes),
            ],
            $fill,
            [224, 224, 224]
        );
        $ctr++;
    }

    $pdf->SetDrawColor(0, 0, 0);

    $pdf->SetFont('Helvetica', 'B', 11);
    $pdf->Cell(160, 10, 'Total Payable API', 'T', 0, 'L');
    $pdf->Cell(30, 10, '$' . number_format($report_data->total_issued_api, 2), 'T', 0, 'C');
    $pdf->Cell(140, 10, '', 'T', 1, 'C');
}

if (count($report_data->cancelled_deals) > 0) {

    //Space
    AddLineSpace($pdf);

    //Clawbacks
    $pdf->SetFont('Helvetica', 'B', 14);
    $pdf->SetFillColor(224, 224, 224);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell(330, 10, 'CLAWBACKS', 0, 1, 'C', 'true');

    $pdf->SetWidths([70, 30, 30, 30, 30, 50, 90]);

    $pdf->SetFont('Helvetica', 'U', 12);
    $pdf->SetFillColor(0, 0, 0);
    $pdf->SetTextColor(0, 0, 0);

    $pdf->Row(['Life Insured', 'Policy #', 'Co.', 'Issue Date', 'API', 'Status', 'Notes'], false, [224, 224, 224]);

    $pdf->SetFont('Helvetica', '', 9);
    $ctr = 0;
    $rep_data = json_encode($report_data->cancelled_deals);
    $report_data->cancelled_deals = json_decode($rep_data, true);
    usort($report_data->cancelled_deals, 'sortFunction');

    foreach ($report_data->cancelled_deals as $deal) {
        extract($deal);
        $pdf->SetFillColor(224, 224, 224);
        $fill = (($ctr % 2) === 0) ? true : false;
        $pdf->Row([$life_insured, $policy_number, $company, NZEntryToDateTime($issued_date), '$' . number_format($api, 2), $clawback_status, str_replace('<br>', "\r\n", $notes)], $fill, [224, 224, 224]);
        $ctr++;
    }

    $pdf->SetDrawColor(0, 0, 0);
    $pdf->SetFont('Helvetica', 'B', 11);
    $pdf->Cell(160, 10, 'Total Paid API', 'T', 0, 'L');
    $pdf->Cell(30, 10, '$' . number_format($report_data->total_cancelled_api, 2), 'T', 0, 'C');
    $pdf->Cell(140, 10, '', 'T', 1, 'C');
}
/*
if (isset($report_data->kiwisaver_deals)) {
    if(count($report_data->kiwisaver_deals) > 0){

        //Space
        AddLineSpace($pdf);

        //KiwiSaver
        $pdf->SetFont('Helvetica', 'B', 14);
        $pdf->SetFillColor(224, 224, 224);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(330, 10, 'KIWISAVER', 0, 1, 'C', 'true');
        $pdf->SetFont('Helvetica', 'U', 12);
        $pdf->SetFillColor(0, 0, 0);
        $pdf->SetTextColor(0, 0, 0);

        //$pdf->SetWidths(array(40, 35, 30, 40, 25, 30));
        $pdf->SetWidths(array(55, 35, 45, 30, 35));
        $pdf->Row(array("Insured Name", "Date Applied", "Commission", "GST", "Balance"), false, array(224, 224, 224));
        $pdf->SetFont('Helvetica', '', 9);
        $ctr = 0;
        $rep_data = json_encode($report_data->kiwisaver_deals);
        $report_data->kiwisaver_deals = json_decode($rep_data, true);
        usort($report_data->kiwisaver_deals, "sortFunction");

        foreach ($report_data->kiwisaver_deals as $deal) {
            extract($deal);
            $pdf->SetFillColor(224, 224, 224);
            $fill = (($ctr % 2) === 0) ? true : false;
            //$pdf->Row(array($insured_name, $source_adviser, $date, "$" . number_format($commission, 2), "$" . number_format($gst, 2), "$" . number_format($balance, 2)), $fill, array(224, 224, 224));
            $pdf->Row(array($insured_name, $date, "$" . number_format($commission, 2), "$" . number_format($gst, 2), "$" . number_format($balance, 2)), $fill, array(224, 224, 224));
            $ctr++;
        }

        $pdf->SetDrawColor(0, 0, 0);

        $pdf->SetFont('Helvetica', 'B', 11);
        $pdf->Cell(55, 10, 'Total Payable API', "T", 0, 'L');
        $pdf->Cell(35, 10, '', "T", 0, 'C');
        $pdf->Cell(45, 10, "$" . number_format($report_data->total_kiwisaver_commission, 2), "T", 0, 'C');
        $pdf->Cell(30, 10, "$" . number_format($report_data->total_kiwisaver_gst, 2), "T", 0, 'C');
        $pdf->Cell(35, 10, "$" . number_format($report_data->total_kiwisaver_balance, 2), "T", 1, 'C');
    }
}
 */
$mix = '';
//$mix="$mydateorig"."-$name-"."BCTI".-date("ymd");
//$path="files/".$mix.".pdf";

$preview = 'deal_tracker_' . md5(uniqid());
$path = "files/$preview" . '_preview.pdf';
$pdf->Output($path, 'F');
//$pdf->Output();

ob_end_clean();
//OUTPUT
$file = [];
$file['adviser_id'] = $adviser_id;
$file['link'] = $path;
$file['filename'] = $mix;
$file['report_data'] = json_encode($report_data);
$file['notes'] = json_encode($note_entries, JSON_HEX_APOS);
$file['from'] = $date_from;
$file['pay_date'] = DateTimeToNZEntry($pay_date);
$file['to'] = $until;
//$file['amount'] = $total_payable;
//$file['payable_leads'] = $payable_leads;
//$file['payable_issued_leads'] = $payable_issued_leads;

echo json_encode($file);
//db add end
//}

function DateTimeToNZEntry($date_submitted)
{
    return substr($date_submitted, 6, 4) . substr($date_submitted, 3, 2) . substr($date_submitted, 0, 2);
}

function NZEntryToDateTime($NZEntry)
{
    return substr($NZEntry, 6, 2) . '/' . substr($NZEntry, 4, 2) . '/' . substr($NZEntry, 0, 4);
}

function sortFunction($a, $b)
{
    return strtotime($a['date']) - strtotime($b['date']);
}

function AddLineSpace($pdf, $linespace = 10)
{
    $pdf->SetFillColor(255, 255, 255);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell(330, $linespace, '', 0, 1, 'C', 'true');
}
