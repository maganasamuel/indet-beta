<?php

$pdf->SetFillColor(224, 224, 224);
$pdf->SetFont('Helvetica', 'B', 14);
$pdf->Cell(200, 10, 'Overall Performance', 0, 1, 'C', 'true');

$pdf->SetFont('Helvetica', 'B', 12);
$pdf->Cell(36, 10, "Leads Generated:", "0", "0", "L");
$pdf->SetFont('Helvetica', '', 12);
$pdf->Cell(59, 10, "$totalclients", "0", "0", "L");
$pdf->Cell(5, 10, '', "0", "0", "R");
$pdf->SetFont('Helvetica', 'B', 12);
$pdf->Cell(29, 10, "Leads Issued:", "0", "0", "L");
$pdf->SetFont('Helvetica', '', 12);
$pdf->Cell(71, 10, "$totalissuedclients", "0", "1", "L");

$pdf->SetFont('Helvetica', 'B', 12);
$pdf->Cell(26, 10, "Leads Seen:", "0", "0", "L");
$pdf->SetFont('Helvetica', '', 12);
$pdf->Cell(69, 10, ($totalclients - $totalcclients), "0", "0", "L");
$pdf->Cell(5, 10, '', "0", "0", "R");
$pdf->SetFont('Helvetica', 'B', 12);
$pdf->Cell(35.5, 10, "Leads Cancelled:", "0", "0", "L");
$pdf->SetFont('Helvetica', '', 12);
$pdf->Cell(64.5, 10, "$totalcclients", "0", "1", "L");

$pdf->SetFont('Helvetica', 'B', 12);
$pdf->Cell(24, 10, "Issued API:", "0", "0", "L");
$pdf->SetFont('Helvetica', '', 12);
$pdf->Cell(71, 10, "$" . number_format($totalissuedpremiums, 2), "0", "0", "L");
$pdf->Cell(5, 10, '', "0", "0", "R");
$pdf->SetFont('Helvetica', 'B', 12);
$pdf->Cell(34, 10, "Issued Leads %:", "0", "0", "L");
$pdf->SetFont('Helvetica', '', 12);
$pdf->Cell(66, 10, number_format($issuedLeadsPercent, 2) . "%", "0", "1", "L");

$pdf->SetFont('Helvetica', 'B', 12);
$pdf->Cell(28.5, 10, "Submissions:", "0", "0", "L");
$pdf->SetFont('Helvetica', '', 12);
$pdf->Cell(65.5, 10, "$totalsubmissions", "0", "0", "L");
$pdf->Cell(5, 10, '', "0", "0", "R");
$pdf->SetFont('Helvetica', 'B', 12);
$pdf->Cell(44, 10, "Submission Amount:", "0", "0", "L");
$pdf->SetFont('Helvetica', '', 12);
$pdf->Cell(56, 10,"$" .  number_format($totalsubmissionamount, 2), "0", "1", "L");

$pdf->SetFont('Helvetica', 'B', 12);
$pdf->Cell(25, 10, "Proficiency:", "0", "0", "L");
$pdf->SetFont('Helvetica', '', 12);
$pdf->Cell(70, 10, "$" . $proficiency, "0", "0", "L");
$pdf->Cell(5, 10, '', "0", "0", "R");
$pdf->SetFont('Helvetica', 'B', 12);
$pdf->Cell(25, 10, "KiwiSavers:", "0", "0", "L");
$pdf->SetFont('Helvetica', '', 12);
$pdf->Cell(75, 10, $totalissuedks, "0", "1", "L");

//GRAPHS
$pdf->SetXY($x + 10, $y + 120);