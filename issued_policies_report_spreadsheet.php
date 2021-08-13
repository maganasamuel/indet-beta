<?php
error_reporting(1);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

session_start();
date_default_timezone_set('Pacific/Auckland');
$_SESSION['x'] = 1;

if (! isset($_SESSION['myusername'])) {
    session_destroy();
    header('Refresh:0; url=index.php');

    return;
}

function formatDate($date)
{
    return substr($date, 6, 2) . '/' . substr($date, 4, 2) . '/' . substr($date, 0, 4);
}

require_once('libs/api/classes/database.class.php');
require_once('PHPExcel/PHPExcel.php');
require_once('libs/composer/vendor/autoload.php');

$db = new Database();
$excel = new PHPExcel();

$filterBy = $_GET['filter_by'];
$value = $_GET['value'];
$advisers = $_GET['advisers'];

if ('date' == $filterBy) {
    $dates = explode('|', $value);

    $dateFrom = formatDate($dates[0]);
    $dateTo = formatDate($dates[1]);

    $periodValue = $dateFrom . ' - ' . $dateTo;
} elseif ('month' == $filterBy) {
    $periodValue = date('F', mktime(0, 0, 0, $value));
} elseif ('year' == $filterBy) {
    $periodValue = $value;
}

$sheet = $excel->createSheet(1);
$sheet->setTitle('Filter Information');

$sheet->fromArray([
    'Period:',
    ucfirst($filterBy),
], null, 'A1');

$sheet->fromArray([
    'Value:',
    $periodValue,
], null, 'A2');

$sheet->fromArray([
    'Advisers:',
], null, 'A3');

$index = 4;

if ($advisers) {
    $advisersQuery = $db->execute($db->prepare('SELECT name FROM adviser_tbl WHERE id IN (' . $advisers . ')'));

    while ($adviser = $advisersQuery->fetch_assoc()) {
        $sheet->fromArray([
            $adviser['name'],
        ], null, 'A' . $index);

        $index++;
    }
} else {
    $sheet->fromArray([
        'All',
    ], null, 'B3');
}

foreach (range('A', 'B') as $column) {
    $sheet->getColumnDimension($column)->setAutoSize(true);
}

$sheet = $excel->setActiveSheetIndex(0);
$sheet->setTitle('Issued Policies List');

$sheet->fromArray([
    'Name of Client',
    'Insurer',
    'Policy Number',
    'Adviser',
    'Lead Source',
    'Issued Date',
    'API',
    'Status',
    'Phone Number',
    'Address',
], null, 'A1');

$sql = 'SELECT
        i.id,
        c.name AS client_name,
        c.appt_time as contact_number,
        c.address,
        c.lead_by,
        a.name AS adviser_name,
        s.deals
    FROM issued_clients_tbl i
    LEFT JOIN submission_clients s ON s.client_id = i.name
    LEFT JOIN clients_tbl c ON c.id = i.name
    LEFT JOIN adviser_tbl a ON a.id = i.assigned_to
    WHERE
        (s.deals IS NOT NULL and s.deals != "")
        AND c.id IS NOT NULL
        AND a.id IS NOT NULL
';

if ($advisers) {
    $sql .= ' AND a.id in (' . $advisers . ')';
}

$issuedClients = $db->execute($db->prepare($sql));

$policies = [];

while ($issuedClient = $issuedClients->fetch_assoc()) {
    foreach (json_decode($issuedClient['deals'], true) as $deal) {
        $policies[] = [
            'client_name' => $issuedClient['client_name'],
            'contact_number' => $issuedClient['contact_number'],
            'address' => $issuedClient['address'],
            'insurer' => $deal['company'],
            'policy_number' => $deal['policy_number'],
            'adviser_name' => $issuedClient['adviser_name'],
            'lead_by' => $issuedClient['lead_by'],
            'issued_date' => $deal['date_issued'],
            'issued_month' => substr($deal['date_issued'], 4, 2),
            'issued_year' => substr($deal['date_issued'], 0, 4),
            'issued_date_formatted' => formatDate($deal['date_issued']),
            'api' => $deal['issued_api'],
            'status' => $deal['status'],
            'clawback_status' => $deal['clawback_status'],
        ];
    }
}

$policies = collect($policies)->where('status', 'Issued');

if ('date' == $filterBy) {
    $dates = explode('|', $value);

    $dateFrom = $dates[0];
    $dateTo = $dates[1];

    $policies = $policies->whereBetween('issued_date', [$dateFrom, $dateTo]);
} elseif ('month' == $filterBy) {
    $policies = $policies->where('issued_month', str_pad($value, 2, '0', STR_PAD_LEFT));
} elseif ('year' == $filterBy) {
    $policies = $policies->where('issued_year', $value);
}

$policies = $policies->sortBy('issued_date')->values()->each(function ($policy, $key) use ($sheet) {
    $sheet->fromArray([
        $policy['client_name'],
        $policy['insurer'],
        $policy['policy_number'] . ' ',
        $policy['adviser_name'],
        $policy['lead_by'],
        $policy['issued_date_formatted'],
        $policy['api'],
        ($policy['clawback_status'] ?? '') == 'Cancelled' ? $policy['clawback_status'] : $policy['status'],
        $policy['contact_number'] . ' ',
        $policy['address'],
    ], null, 'A' . ($key + 2));
});

$style = $sheet->getStyle('A1:I1');
$style->getFont()->setBold(true);
$style->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

foreach (range('A', 'I') as $column) {
    $sheet->getColumnDimension($column)->setAutoSize(true);
}

$sheet->freezePane('A2');

$writer = new PHPExcel_Writer_Excel2007($excel);
$writer->setOffice2003Compatibility(true);

$filename = 'issued-policies-list-report';

header('Pragma: public');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Content-Type: application/force-download');
header('Content-Type: application/octet-stream');
header('Content-Type: application/download');
header('Content-Transfer-Encoding: binary ');
header("Content-Disposition: attachment;filename=$filename.xlsx");

$writer->save('php://output');
