<?php



$pdf->SetFillColor(224, 224, 224);
$pdf->SetFont('Helvetica', 'B', 14);
$pdf->Cell(200, 10, 'Overall Performance', 0, 1, 'C', 'true');
$pdf->SetFont('Helvetica', 'B', 12);

$pdf->Cell(25, 6, 'Leads', 0, 0, 'C');
$pdf->Cell(25, 6, 'Leads', 0, 0, 'C');
$pdf->Cell(25, 6, 'Leads', 0, 0, 'C');
$pdf->Cell(30, 6, 'Kiwi-', 0, 0, 'C');
$pdf->Cell(35, 6, 'Issued', 0, 0, 'C');
$pdf->Cell(25, 6, 'Issued', 0, 0, 'C');
$pdf->Cell(35, 6, '', 0, 1, 'C');

$pdf->Cell(25, 6, 'Gen', 0, 0, 'C');
$pdf->Cell(25, 6, 'Canx', 0, 0, 'C');
$pdf->Cell(25, 6, 'Issued', 0, 0, 'C');
$pdf->Cell(30, 6, 'Savers', 0, 0, 'C');
$pdf->Cell(35, 6, 'API', 0, 0, 'C');
$pdf->Cell(25, 6, 'Leads %', 0, 0, 'C');
$pdf->Cell(35, 6, 'Proficiency', 0, 1, 'C');

$pdf->SetFont('Helvetica', '', 12);
$pdf->Cell(25, 10, $totalclients, 0, 0, 'C');
$pdf->Cell(25, 10, $totalcclients, 0, 0, 'C');
$pdf->Cell(25, 10, $totalissuedclients, 0, 0, 'C');
$pdf->Cell(30, 10, $totalissuedks, 0, 0, 'C');
$pdf->Cell(35, 10, "$" . number_format($totalissuedpremiums, 2), 0, 0, 'C');
$pdf->Cell(25, 10, number_format($issuedLeadsPercent, 2) . "%", 0, 0, 'C');
$pdf->Cell(35, 10, "$" . $proficiency, 0, 1, 'C');
?>