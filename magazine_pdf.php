<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE);

date_default_timezone_set('Pacific/Auckland');
ob_start();
// require('fpdf/mc_table.php');
require('fpdf/cellfit.php');

require('database.php');

require_once 'libs/indet_dates_helper.php';
require_once 'libs/indet_alphanumeric_helper.php';
require_once 'libs/api/classes/general.class.php';
require_once 'libs/api/controllers/Magazine.controller.php';
require_once 'libs/api/controllers/User.controller.php';

class PDF extends FPDF_CellFit
{
    public $uploads_folder = '../indet_photos_stash/';

    public $default_image = 'images/default_pic.png';

    public $table_of_contents = [];

    public $adviser = '';

    //Transparency
    protected $extgstates = [];

    public function AddPage($orientation = '', $size = '', $rotation = 0)
    {
        parent::AddPage();

        if (1 != $this->PageNo()) {
            //Add bg
            $this->Image('images/Magazine BG.png', 0, 0, 216);
        }
    }

    public function GetCurrentSection()
    {
        $current_section = $this->table_of_contents[0]['title'];

        foreach ($this->table_of_contents as $page) {
            if ($page['page_start'] > $this->PageNo()) {
                break;
            }

            $current_section = $page['title'];
        }

        return $current_section;
    }

    public function SetPage($num)
    {
        $this->page = $num;
    }

    public function Footer()
    {
        global $fsp_num;
        global $reference_no;
        global $timestamp;
        global $agent_name;

        $this->SetY(-15);
        $this->SetDrawColor(200, 200, 200);
        $this->SetFillColor(200, 200, 200);
        $this->SetFont('Helvetica', 'B', 10);
        $this->SetTextColor(0, 0, 0);
        $this->Cell(50, 10, '', 0, 0, 'L');
        $this->Cell(90, 10, '', 0, 0, 'C');
        $this->AliasNbPages('{totalPages}');
        $this->Cell(50, 10, $this->PageNo(), 0, 1, 'R');
    }

    public function getPage()
    {
        return $this->PageNo();
    }

    public function NLines($w, $txt)
    {
        return $this->NbLines($w, $txt);
    }

    public function Header1($y, $text, $fontSize = 23)
    {
        $this->SetFillColor(102, 163, 194);
        $this->SetDrawColor(102, 163, 194);
        $this->Rect(10.5, $y - 5, 195, 15, 'DF');

        //set the coordinates x1,y1,x2,y2 of the gradient (see linear_gradient_coords.jpg)
        $coords = [0, 0, 0, 1];
        $color1 = [160, 200, 220];
        $color2 = [255, 255, 255];
        $this->LinearGradient(11, $y - 4.5, 194, 14, $color1, $color2, $coords);

        $this->SetTextColor(0, 0, 0);
        $this->SetFont('Calibri', 'B', $fontSize);
        $this->SetX(8);
        $this->SetY($y);
        $this->Cell(200, 8, $text, '', '1', 'C');
    }

    public function Header2($y, $text, $fontSize = 23)
    {
        $this->SetFillColor(102, 163, 194);
        $this->SetDrawColor(102, 163, 194);
        $this->Rect(10.5, $y - 5, 195, 15, 'DF');

        //set the coordinates x1,y1,x2,y2 of the gradient (see linear_gradient_coords.jpg)
        $coords = [0, 0, 0, 1];
        $color1 = [160, 200, 220];
        $color2 = [255, 255, 255];
        $this->LinearGradient(11, $y - 4.5, 194, 14, $color1, $color2, $coords);

        $this->SetTextColor(0, 0, 0);
        $this->SetFont('Calibri', 'B', $fontSize);
        $this->SetX(8);
        $this->SetY($y);
        $this->Cell(200, 8, $text, '', '1', 'C');
    }

    public function RoundedRect($x, $y, $w, $h, $r, $corners = '1234', $style = '')
    {
        $k = $this->k;
        $hp = $this->h;

        if ('F' == $style) {
            $op = 'f';
        } elseif ('FD' == $style || 'DF' == $style) {
            $op = 'B';
        } else {
            $op = 'S';
        }
        $MyArc = 4 / 3 * (sqrt(2) - 1);
        $this->_out(sprintf('%.2F %.2F m', ($x + $r) * $k, ($hp - $y) * $k));

        $xc = $x + $w - $r;
        $yc = $y + $r;
        $this->_out(sprintf('%.2F %.2F l', $xc * $k, ($hp - $y) * $k));

        if (false === strpos($corners, '2')) {
            $this->_out(sprintf('%.2F %.2F l', ($x + $w) * $k, ($hp - $y) * $k));
        } else {
            $this->_Arc($xc + $r * $MyArc, $yc - $r, $xc + $r, $yc - $r * $MyArc, $xc + $r, $yc);
        }

        $xc = $x + $w - $r;
        $yc = $y + $h - $r;
        $this->_out(sprintf('%.2F %.2F l', ($x + $w) * $k, ($hp - $yc) * $k));

        if (false === strpos($corners, '3')) {
            $this->_out(sprintf('%.2F %.2F l', ($x + $w) * $k, ($hp - ($y + $h)) * $k));
        } else {
            $this->_Arc($xc + $r, $yc + $r * $MyArc, $xc + $r * $MyArc, $yc + $r, $xc, $yc + $r);
        }

        $xc = $x + $r;
        $yc = $y + $h - $r;
        $this->_out(sprintf('%.2F %.2F l', $xc * $k, ($hp - ($y + $h)) * $k));

        if (false === strpos($corners, '4')) {
            $this->_out(sprintf('%.2F %.2F l', ($x) * $k, ($hp - ($y + $h)) * $k));
        } else {
            $this->_Arc($xc - $r * $MyArc, $yc + $r, $xc - $r, $yc + $r * $MyArc, $xc - $r, $yc);
        }

        $xc = $x + $r;
        $yc = $y + $r;
        $this->_out(sprintf('%.2F %.2F l', ($x) * $k, ($hp - $yc) * $k));

        if (false === strpos($corners, '1')) {
            $this->_out(sprintf('%.2F %.2F l', ($x) * $k, ($hp - $y) * $k));
            $this->_out(sprintf('%.2F %.2F l', ($x + $r) * $k, ($hp - $y) * $k));
        } else {
            $this->_Arc($xc - $r, $yc - $r * $MyArc, $xc - $r * $MyArc, $yc - $r, $xc, $yc - $r);
        }
        $this->_out($op);
    }

    public function _Arc($x1, $y1, $x2, $y2, $x3, $y3)
    {
        $h = $this->h;
        $this->_out(sprintf(
            '%.2F %.2F %.2F %.2F %.2F %.2F c ',
            $x1 * $this->k,
            ($h - $y1) * $this->k,
            $x2 * $this->k,
            ($h - $y2) * $this->k,
            $x3 * $this->k,
            ($h - $y3) * $this->k
        ));
    }

    public function FlexibleHeader1($x, $y, $w, $h, $text, $fontSize = 23)
    {
        $this->SetFillColor(102, 163, 194);
        $this->SetDrawColor(102, 163, 194);
        $this->Rect($x, $y - 5, $w, $h, 'DF');

        //set the coordinates x1,y1,x2,y2 of the gradient (see linear_gradient_coords.jpg)
        $coords = [0, 0, 0, 1];
        $color1 = [160, 200, 220];
        $color2 = [255, 255, 255];
        $this->LinearGradient($x + .5, $y - 4.5, $w - 1, $h - 1, $color1, $color2, $coords);

        $this->SetTextColor(0, 0, 0);
        $this->SetFont('Calibri', 'B', $fontSize);
        $this->SetY($y);
        $this->SetX($x);
        $this->Cell($w, 8, $text, '', '1', 'C');
    }

    public function BiMonthlyPage($advisers)
    {
        //page 2 BiMonthly API
        $this->AddPage();

        $this->Header1(15, 'Adviser with the Highest Total API from Policies Issued');

        $image_path = '';

        if (empty($advisers[0]['image'])) {
            $image_path = $this->default_image;
        } else {
            $image_path = $this->uploads_folder . $advisers[0]['image'];
        }

        if (file_exists($image_path)) {
            $this->Image($image_path, 20, 30, 75, 75);
        } else {
            $this->Image($this->default_image, 20, 30, 75, 75);
        }

        $this->SetFillColor(255, 255, 255);

        $this->Rect(100, 35, 100, 65, 'DF');
        $this->SetTextColor(0, 0, 0);
        $this->SetY(38);

        //Name
        $this->SetFont('Calibri', 'B', 20);
        $this->Cell(92, 8, '', '', '0', 'L');
        $this->Cell(50, 8, 'Adviser:', '', '1', 'L');
        $this->SetFont('Calibri', '', 20);
        $this->Cell(100, 8, '', '', '0', 'L');
        $this->Cell(50, 8, $advisers[0]['name'], '', '1', 'L');
        $this->Ln(5);
        //Policies Issued
        $this->SetFont('Calibri', 'B', 20);
        $this->Cell(92, 8, '', '', '0', 'L');
        $this->Cell(50, 8, 'Policies Issued:', '', '1', 'L');
        $this->SetFont('Calibri', '', 20);
        $this->Cell(100, 8, '', '', '0', 'L');
        $this->Cell(50, 8, $advisers[0]['deals'], '', '1', 'L');
        $this->Ln(5);
        //Total API
        $this->SetFont('Calibri', 'B', 20);
        $this->Cell(92, 8, '', '', '0', 'L');
        $this->Cell(50, 8, 'Total API:', '', '1', 'L');
        $this->SetFont('Calibri', '', 20);
        $this->Cell(100, 8, '', '', '0', 'L');
        $this->Cell(50, 8, '$' . number_format($advisers[0]['issued_api'], 2), '', '1', 'L');

        //Tables
        $this->Header1(125, 'Bi-Monthly Issued Policies Table');

        $this->SetDrawColor(0, 0, 0);
        $this->SetX(8);
        $this->SetY(140);
        $this->SetFont('Arial', 'B', 15);
        $this->SetDrawColor(0, 0, 0);
        $this->SetFillColor(255, 255, 255);
        $this->Cell(80, 10, 'Name Of Adviser', 1, '0', 'C', true);
        $this->Cell(60, 10, 'No. of Policies Issued', 1, '0', 'C', true);
        $this->Cell(55, 10, 'Issued API', 1, '1', 'C', true);

        $this->SetFont('Arial', '', 15);

        $total = 0;
        $total_api = 0;

        foreach ($advisers as $adviser) {
            if ($adviser['deals'] > 0 && $adviser['issued_api'] > 0) {
                $total += $adviser['deals'];
                $total_api += $adviser['issued_api'];
                $this->Cell(80, 10, $adviser['name'], 1, '0', 'L', true);
                $this->Cell(60, 10, $adviser['deals'], 1, '0', 'C', true);
                $this->Cell(55, 10, '$' . number_format($adviser['issued_api'], 2), 1, '1', 'C', true);
            }
        }

        $this->SetFont('Arial', 'B', 15);
        $this->Cell(80, 10, 'Total', 1, '0', 'L', true);
        $this->Cell(60, 10, $total, 1, '0', 'C', true);
        $this->Cell(55, 10, '$' . number_format($total_api, 2), 1, '1', 'C', true);
    }

    public function CumulativePage($advisers)
    {
        //page 2 BiMonthly API
        $this->AddPage();

        $this->Header1(15, 'Adviser with the Highest Cumulative API from Policies Issued', 20);

        $image_path = '';

        if (empty($advisers[0]['image'])) {
            $image_path = $this->default_image;
        } else {
            $image_path = $this->uploads_folder . $advisers[0]['image'];
        }

        if (file_exists($image_path)) {
            $this->Image($image_path, 20, 30, 75, 75);
        } else {
            $this->Image($this->default_image, 20, 30, 75, 75);
        }

        $this->SetFillColor(255, 255, 255);

        $this->Rect(100, 35, 100, 65, 'DF');
        $this->SetTextColor(0, 0, 0);
        $this->SetY(38);
        //Name
        $this->SetFont('Calibri', 'B', 20);
        $this->Cell(92, 8, '', '', '0', 'L');
        $this->Cell(50, 8, 'Adviser:', '', '1', 'L');
        $this->SetFont('Calibri', '', 20);
        $this->Cell(100, 8, '', '', '0', 'L');
        $this->Cell(50, 8, $advisers[0]['name'], '', '1', 'L');
        $this->Ln(5);

        //Policies Issued
        $this->SetFont('Calibri', 'B', 20);
        $this->Cell(92, 8, '', '', '0', 'L');
        $this->Cell(50, 8, 'Team:', '', '1', 'L');
        $this->SetFont('Calibri', '', 20);
        $this->Cell(100, 8, '', '', '0', 'L');
        $this->Cell(50, 8, $advisers[0]['team'], '', '1', 'L');
        $this->Ln(5);
        //Total API
        $this->SetFont('Calibri', 'B', 20);
        $this->Cell(92, 8, '', '', '0', 'L');
        $this->Cell(50, 8, 'Total API:', '', '1', 'L');
        $this->SetFont('Calibri', '', 20);
        $this->Cell(100, 8, '', '', '0', 'L');
        $this->Cell(50, 8, '$' . number_format($advisers[0]['issued_api'], 2), '', '1', 'L');

        //Tables
        $this->Header1(125, 'Cumulative Table for Issued Policies');

        $this->SetDrawColor(0, 0, 0);
        $this->SetX(8);
        $this->SetY(140);
        $this->SetFont('Arial', 'B', 15);
        $this->SetDrawColor(0, 0, 0);
        $this->SetFillColor(255, 255, 255);

        $this->Cell(80, 10, 'Name Of Adviser', 1, '0', 'C', true);
        $this->Cell(60, 10, 'No. of Policies Issued', 1, '0', 'C', true);
        $this->Cell(55, 10, 'Issued API', 1, '1', 'C', true);

        $this->SetFont('Arial', '', 15);
        $total = 0;
        $total_api = 0;

        foreach ($advisers as $adviser) {
            if ($adviser['deals'] > 0) {
                $total += $adviser['deals'];
                $total_api += $adviser['issued_api'];
                $this->Cell(80, 10, $adviser['name'], 1, '0', 'L', true);
                $this->Cell(60, 10, $adviser['deals'], 1, '0', 'C', true);
                $this->Cell(55, 10, '$' . number_format($adviser['issued_api'], 2), 1, '1', 'C', true);
            }
        }

        $this->SetFont('Arial', 'B', 15);
        $this->Cell(80, 10, 'Total', 1, '0', 'L', true);
        $this->Cell(60, 10, $total, 1, '0', 'C', true);
        $this->Cell(55, 10, '$' . number_format($total_api, 2), 1, '1', 'C', true);
    }

    public function CumulativeRBAPage($advisers, $overallCumulativeRBA)
    {
        //page 2 BiMonthly API
        $this->AddPage();

        $this->Header1(15, 'EliteInsure Overall Cumulative Table for Percentage of Replacement Business', 16);

        $this->SetDrawColor(0, 0, 0);
        $this->SetX(8);
        $this->SetY(30);
        $this->SetFont('Arial', 'B', 15);
        $this->SetDrawColor(0, 0, 0);
        $this->SetFillColor(255, 255, 255);

        $this->Cell(97, 10, 'No. of Policies Issued', 1, '0', 'C', true);
        $this->Cell(98, 10, '% Replacement Business', 1, '0', 'C', true);
        $this->Ln();
        $this->Cell(97, 10, $overallCumulativeRBA['issuedPolicyCount'], 1, '0', 'C', true);
        $this->Cell(98, 10, number_format($overallCumulativeRBA['rbaPercentage'], 2) . '%', 1, '0', 'C', true);

        $this->Header1(/* 15 */65, 'Adviser with the Lowest Cumulative Percentage of Replacement Business', 17);

        $image_path = '';

        if (empty($advisers[0]['image'])) {
            $image_path = $this->default_image;
        } else {
            $image_path = $this->uploads_folder . $advisers[0]['image'];
        }

        if (file_exists($image_path)) {
            $this->Image($image_path, 20, 80, 75, 75);
        } else {
            $this->Image($this->default_image, 20, 80, 75, 75);
        }

        $this->SetFillColor(255, 255, 255);

        $this->Rect(100, 85, 100, 65, 'DF');
        $this->SetTextColor(0, 0, 0);
        $this->SetY(88);
        //Name
        $this->SetFont('Calibri', 'B', 20);
        $this->Cell(92, 8, '', '', '0', 'L');
        $this->Cell(50, 8, 'Adviser:', '', '1', 'L');
        $this->SetFont('Calibri', '', 20);
        $this->Cell(100, 8, '', '', '0', 'L');
        $this->Cell(50, 8, $advisers[0]['name'], '', '1', 'L');
        $this->Ln(5);

        //Total API
        $this->SetFont('Calibri', 'B', 20);
        $this->Cell(92, 8, '', '', '0', 'L');
        $this->Cell(50, 8, 'RBA:', '', '1', 'L');
        $this->SetFont('Calibri', '', 20);
        $this->Cell(100, 8, '', '', '0', 'L');
        $this->Cell(50, 8, number_format($advisers[0]['percent_rba'], 2) . '%', '', '1', 'L');

        //Tables
        $this->Header1(175, 'Cumulative Table for Percentage of Replacement Business', 20);

        $this->SetDrawColor(0, 0, 0);
        $this->SetX(8);
        $this->SetY(190);
        $this->SetFont('Arial', 'B', 15);
        $this->SetDrawColor(0, 0, 0);
        $this->SetFillColor(255, 255, 255);

        $this->Cell(65, 10, 'Name Of Adviser', 1, '0', 'C', true);
        $this->Cell(60, 10, 'No. of Policies Issued', 1, '0', 'C', true);
        $this->Cell(70, 10, '% Replacement Business', 1, '1', 'C', true);

        $this->SetFont('Arial', '', 15);

        $total = 0;
        $total_api = 0;

        foreach ($advisers as $adviser) {
            if ($adviser['deals'] > 0) {
                $total += $adviser['deals'];
                $total_api += $adviser['issued_api'];
                $this->Cell(65, 10, $adviser['name'], 1, '0', 'L', true);
                $this->Cell(60, 10, $adviser['deals'], 1, '0', 'C', true);
                $this->Cell(70, 10, number_format($adviser['percent_rba'], 2) . '%', 1, '1', 'C', true);
            }
        }

        $this->SetFont('Calibri', '', 15);
        $this->SetXY(10, $this->GetPageHeight() - 25);
        $this->Write(0, 'Note: table excludes advisers with less than 5 policies issued in the period.');
    }

    public function BiMonthlyKiwiSaversPage($advisers)
    {
        //page 2 BiMonthly API
        $this->AddPage();

        $this->Header1(15, 'Adviser with the Highest Bi-Monthly Kiwisaver Enrolments', 20);

        $image_path = '';

        if (empty($advisers[0]['image'])) {
            $image_path = $this->default_image;
        } else {
            $image_path = $this->uploads_folder . $advisers[0]['image'];
        }

        if (file_exists($image_path)) {
            $this->Image($image_path, 20, 30, 75, 75);
        } else {
            $this->Image($this->default_image, 20, 30, 75, 75);
        }

        $this->SetFillColor(255, 255, 255);

        $this->Rect(100, 35, 100, 65, 'DF');
        $this->SetTextColor(0, 0, 0);
        $this->SetY(38);
        //Name
        $this->SetFont('Calibri', 'B', 20);
        $this->Cell(92, 8, '', '', '0', 'L');
        $this->Cell(50, 8, 'Adviser:', '', '1', 'L');
        $this->SetFont('Calibri', '', 20);
        $this->Cell(100, 8, '', '', '0', 'L');

        $this->Cell(50, 8, $advisers[0]['name'], '', '1', 'L');
        $this->Ln(5);

        //Policies Issued
        $this->SetFont('Calibri', 'B', 20);
        $this->Cell(92, 8, '', '', '0', 'L');
        $this->Cell(50, 8, 'Team:', '', '1', 'L');
        $this->SetFont('Calibri', '', 20);
        $this->Cell(100, 8, '', '', '0', 'L');

        $this->Cell(50, 8, $advisers[0]['team'], '', '1', 'L');
        $this->Ln(5);
        //Total API
        $this->SetFont('Calibri', 'B', 20);
        $this->Cell(92, 8, '', '', '0', 'L');
        $this->Cell(50, 8, 'Total Enrolments:', '', '1', 'L');
        $this->SetFont('Calibri', '', 20);
        $this->Cell(100, 8, '', '', '0', 'L');
        $this->Cell(50, 8, $advisers[0]['deals'], '', '1', 'L');

        //Tables
        $this->Header1(125, 'Bi-Monthly Table for Kiwisaver Enrolments');

        $this->SetDrawColor(0, 0, 0);
        $this->SetX(8);
        $this->SetY(140);
        $this->SetFont('Arial', 'B', 15);
        $this->SetDrawColor(0, 0, 0);
        $this->SetFillColor(255, 255, 255);

        $this->Cell(125, 10, 'Name Of Adviser', 1, '0', 'C', true);
        $this->Cell(70, 10, 'No. of  KiwiSavers Enrolled', 1, '1', 'C', true);

        $this->SetFont('Arial', '', 15);

        $total = 0;

        foreach ($advisers as $index => $adviser) {
            if ($adviser['deals'] > 0) {
                $total += $adviser['deals'];
                $this->Cell(125, 10, $adviser['name'], 1, '0', 'L', true);
                $this->Cell(70, 10, $adviser['deals'], 1, '1', 'C', true);
            }
        }

        $this->SetFont('Arial', 'B', 15);
        $this->Cell(125, 10, 'Total', 1, '0', 'L', true);
        $this->Cell(70, 10, $total, 1, '1', 'C', true);
    }

    public function CumulativeKiwiSaversPage($advisers)
    {
        //page 2 BiMonthly API
        $this->AddPage();

        $this->Header1(15, 'Adviser with the Highest Cumulative Kiwisaver Enrolments', 20);

        $image_path = '';

        if (empty($advisers[0]['image'])) {
            $image_path = $this->default_image;
        } else {
            $image_path = $this->uploads_folder . $advisers[0]['image'];
        }

        if (file_exists($image_path)) {
            $this->Image($image_path, 20, 30, 75, 75);
        } else {
            $this->Image($this->default_image, 20, 30, 75, 75);
        }

        $this->SetFillColor(255, 255, 255);

        $this->Rect(100, 35, 100, 65, 'DF');
        $this->SetTextColor(0, 0, 0);
        $this->SetY(38);

        //Name
        $this->SetFont('Calibri', 'B', 20);
        $this->Cell(92, 8, '', '', '0', 'L');
        $this->Cell(50, 8, 'Adviser:', '', '1', 'L');
        $this->SetFont('Calibri', '', 20);
        $this->Cell(100, 8, '', '', '0', 'L');

        $this->Cell(50, 8, $advisers[0]['name'], '', '1', 'L');
        $this->Ln(5);

        //Policies Issued
        $this->SetFont('Calibri', 'B', 20);
        $this->Cell(92, 8, '', '', '0', 'L');
        $this->Cell(50, 8, 'Team:', '', '1', 'L');
        $this->SetFont('Calibri', '', 20);
        $this->Cell(100, 8, '', '', '0', 'L');
        $this->Cell(50, 8, $advisers[0]['team'], '', '1', 'L');
        $this->Ln(5);

        //Total API
        $this->SetFont('Calibri', 'B', 20);
        $this->Cell(92, 8, '', '', '0', 'L');
        $this->Cell(50, 8, 'Total Enrolments:', '', '1', 'L');
        $this->SetFont('Calibri', '', 20);
        $this->Cell(100, 8, '', '', '0', 'L');
        $this->Cell(50, 8, $advisers[0]['deals'], '', '1', 'L');

        //Tables
        $this->Header1(125, 'Cumulative Table for Kiwisaver Enrolments');

        $this->SetDrawColor(0, 0, 0);
        $this->SetX(8);
        $this->SetY(140);
        $this->SetFont('Arial', 'B', 15);
        $this->SetDrawColor(0, 0, 0);
        $this->SetFillColor(255, 255, 255);

        $this->Cell(125, 10, 'Name Of Adviser', 1, '0', 'C', true);
        $this->Cell(70, 10, 'No. of KiwiSavers Enrolled', 1, '1', 'C', true);

        $this->SetFont('Arial', '', 15);

        $total = 0;

        foreach ($advisers as $index => $adviser) {
            if ($adviser['deals'] > 0) {
                $total += $adviser['deals'];
                $this->Cell(125, 10, $adviser['name'], 1, '0', 'L', true);
                $this->Cell(70, 10, $adviser['deals'], 1, '1', 'C', true);
            }
        }

        $this->SetFont('Arial', 'B', 15);
        $this->Cell(125, 10, 'Total', 1, '0', 'L', true);
        $this->Cell(70, 10, $total, 1, '1', 'C', true);
    }

    public function BDMBiMonthlyPage($bdms)
    {
        //page 2 BiMonthly API
        $this->AddPage();

        $this->Header1(15, 'Marketer with the Highest Total Issued API from Generated Leads', 17);

        $image_path = '';

        if (empty($bdms[0]['image'])) {
            $image_path = $this->default_image;
        } else {
            $image_path = $this->uploads_folder . $bdms[0]['image'];
        }

        if (file_exists($image_path)) {
            $this->Image($image_path, 20, 30, 75, 75);
        } else {
            $this->Image($this->default_image, 20, 30, 75, 75);
        }

        $this->SetFillColor(255, 255, 255);

        $this->Rect(100, 35, 100, 65, 'DF');
        $this->SetTextColor(0, 0, 0);
        $this->SetY(38);

        //Name
        $this->SetFont('Calibri', 'B', 20);
        $this->Cell(92, 8, '', '', '0', 'L');
        $this->Cell(50, 8, 'BDM:', '', '1', 'L');
        $this->SetFont('Calibri', '', 20);
        $this->Cell(100, 8, '', '', '0', 'L');
        $this->Cell(50, 8, $bdms[0]['name'], '', '1', 'L');
        $this->Ln(5);

        //Total API
        $this->SetFont('Calibri', 'B', 20);
        $this->Cell(100, 8, '', '', '0', 'L');
        $this->Cell(95, 8, 'Total API:', '', '1', 'L');
        $this->SetFont('Calibri', '', 20);
        $this->Cell(100, 8, '', '', '0', 'L');
        $this->Cell(95, 8, '$' . number_format($bdms[0]['issued_api'], 2), '', '1', 'L');

        //Tables
        $this->Header1(125, 'Bi-Monthly Issued Policies Table');

        $this->SetDrawColor(0, 0, 0);
        $this->SetX(8);
        $this->SetY(140);
        $this->SetFont('Arial', 'B', 14);
        $this->SetDrawColor(0, 0, 0);
        $this->SetFillColor(255, 255, 255);
        $this->Cell(70, 10, 'Name Of BDM', 1, '0', 'C', true);
        $this->Cell(60, 10, 'No. of Policies Issued', 1, '0', 'C', true);
        $this->Cell(60, 10, 'Issued API', 1, '1', 'C', true);

        $this->SetFont('Arial', '', 15);

        $total = 0;
        $total_api = 0;

        foreach ($bdms as $bdm) {
            if ($bdm['deals'] > 0) {
                $total += $bdm['deals'];
                $total_api += $bdm['issued_api'];

                if (empty($bdm['image'])) {
                    $image_path = $this->default_image;
                } else {
                    $image_path = $this->uploads_folder . $bdm['image'];
                }

                if (file_exists($image_path)) {
                    $this->Cell(15, 15, $this->Image($image_path, $this->GetX(), $this->GetY(), 15), 1, '0', 'L', false);
                } else {
                    $this->Cell(15, 15, $this->Image($this->default_image, $this->GetX(), $this->GetY(), 15), 1, '0', 'L', false);
                }

                $this->Cell(55, 15, $bdm['name'], 1, '0', 'L', true);
                $this->Cell(60, 15, $bdm['deals'], 1, '0', 'C', true);
                $this->Cell(60, 15, '$' . number_format($bdm['issued_api'], 2), 1, '1', 'C', true);
            }
        }

        $this->SetFont('Arial', 'B', 15);
        $this->Cell(70, 10, 'Total', 1, '0', 'L', true);
        $this->Cell(120, 10, '$' . number_format($total_api, 2), 1, '1', 'C', true);
    }

    public function BDMCumulativePage($bdms)
    {
        //page 2 BiMonthly API
        $this->AddPage();

        $this->Header1(15, 'Marketer with Highest Cumulative API from Leads Generated', 17);

        $image_path = '';

        if (empty($bdms[0]['image'])) {
            $image_path = $this->default_image;
        } else {
            $image_path = $this->uploads_folder . $bdms[0]['image'];
        }

        if (file_exists($image_path)) {
            $this->Image($image_path, 20, 30, 75, 75);
        } else {
            $this->Image($this->default_image, 20, 30, 75, 75);
        }

        $this->SetFillColor(255, 255, 255);

        $this->Rect(100, 35, 100, 65, 'DF');
        $this->SetTextColor(0, 0, 0);
        $this->SetY(38);

        //Name
        $this->SetFont('Calibri', 'B', 20);
        $this->Cell(92, 8, '', '', '0', 'L');
        $this->Cell(50, 8, 'BDM:', '', '1', 'L');
        $this->SetFont('Calibri', '', 20);
        $this->Cell(100, 8, '', '', '0', 'L');
        $this->Cell(50, 8, $bdms[0]['name'], '', '1', 'L');
        $this->Ln(5);

        //Total API
        $this->SetFont('Calibri', 'B', 20);
        $this->Cell(100, 8, '', '', '0', 'L');
        $this->Cell(95, 8, 'Total API:', '', '1', 'L');
        $this->SetFont('Calibri', '', 20);
        $this->Cell(100, 8, '', '', '0', 'L');
        $this->Cell(95, 8, '$' . number_format($bdms[0]['issued_api'], 2), '', '1', 'L');

        //Tables
        $this->Header1(125, 'Marketer Cumulative Performance');

        $this->SetDrawColor(0, 0, 0);
        $this->SetX(8);
        $this->SetY(140);
        $this->SetFont('Arial', 'B', 14);
        $this->SetDrawColor(0, 0, 0);
        $this->SetFillColor(255, 255, 255);
        $this->Cell(70, 10, 'Name Of BDM', 1, '0', 'C', true);
        $this->Cell(60, 10, 'No. of Policies Issued', 1, '0', 'C', true);
        $this->Cell(60, 10, 'Issued API', 1, '1', 'C', true);

        $this->SetFont('Arial', '', 15);

        $total = 0;
        $total_api = 0;

        foreach ($bdms as $bdm) {
            if ($bdm['deals'] > 0) {
                $total += $bdm['deals'];
                $total_api += $bdm['issued_api'];

                if (empty($bdm['image'])) {
                    $image_path = $this->default_image;
                } else {
                    $image_path = $this->uploads_folder . $bdm['image'];
                }

                if (file_exists($image_path)) {
                    $this->Cell(15, 15, $this->Image($image_path, $this->GetX(), $this->GetY(), 15), 1, '0', 'L', false);
                } else {
                    $this->Cell(15, 15, $this->Image($this->default_image, $this->GetX(), $this->GetY(), 15), 1, '0', 'L', false);
                }

                $this->Cell(55, 15, $bdm['name'], 1, '0', 'L', true);
                $this->Cell(60, 15, $bdm['deals'], 1, '0', 'C', true);
                $this->Cell(60, 15, '$' . number_format($bdm['issued_api'], 2), 1, '1', 'C', true);
            }
        }

        $this->SetFont('Arial', 'B', 15);
        $this->Cell(70, 10, 'Total', 1, '0', 'L', true);
        $this->Cell(120, 10, '$' . number_format($total_api, 2), 1, '1', 'C', true);
    }

    public function BDMPage($bdms, $quarterTitle)
    {
        //page 2 BiMonthly API
        $this->AddPage();

        if ($bdms[0]['issued_api'] > 0) {
            $this->Header1(15, 'Marketer with Highest Cumulative API from Leads Generated', 17);
        } else {
            $this->Header1(15, 'Marketer with Highest Cumulative API from Leads Generated', 17);
        }
        $this->SetDrawColor(4, 129, 185);
        $this->RoundedRect(10, $this->GetY() + 10, 195, 55, 5, '24', 'DF');

        $image_path = '';

        if (empty($bdms[0]['image'])) {
            $image_path = $this->default_image;
        } else {
            $image_path = $this->uploads_folder . $bdms[0]['image'];
        }

        if (file_exists($image_path)) {
            $this->Image($image_path, 20, $this->GetY() + 15, 40, 40);
        } else {
            $this->Image($this->default_image, 20, $this->GetY() + 15, 40, 40);
        }

        $this->SetFillColor(255, 255, 255);

        $this->SetDrawColor(0, 0, 0);

        $this->Rect($this->GetX() + 70, $this->GetY() + 15, 100, 45, 'DF');
        $this->SetTextColor(0, 0, 0);
        $this->SetX($this->GetX());
        $this->SetY($this->GetY() + 18);

        //Name
        $this->SetFont('Calibri', 'B', 20);
        $this->Cell(75, 8, '', '', '0', 'L');
        $this->Cell(50, 8, 'BDM:', '', '1', 'L');
        $this->SetFont('Calibri', '', 20);
        $this->Cell(80, 8, '', '', '0', 'L');

        $this->Cell(50, 8, $bdms[0]['name'], '', '1', 'L');
        $this->Ln(5);

        //Policies Issued
        $this->SetFont('Calibri', 'B', 18);
        $this->Cell(75, 8, '', '', '0', 'L');
        $this->Cell(50, 8, 'Issued API from Leads Generated:', '', '1', 'L');
        $this->SetFont('Calibri', '', 20);
        $this->Cell(80, 8, '', '', '0', 'L');
        $this->Cell(50, 8, '$' . number_format($bdms[0]['issued_api'], 2), '', '1', 'L');
        $this->Ln(5);

        $this->Header1($this->GetY() + 20, 'Marketer Cumulative Performance', 20);

        $this->SetDrawColor(0, 0, 0);
        $this->SetX(8);
        $this->SetY(120);
        $this->SetFont('Calibri', 'B', 15);
        $this->SetFillColor(255, 255, 255);
        $this->SetDrawColor(4, 129, 185);

        $total_generated = 0;
        $total_cancelled = 0;
        $total_issued = 0;
        $x = 20;
        $cellX = 50;
        $width = 185;
        $r = 163;
        $g = 201;
        $b = 220;
        $color = [$r, $g, $b];
        $this->SetFont('Arial', '', 15);

        foreach ($bdms as $bdm) {
            if ($bdm['generated'] > 0) {
                if ($bdm['issued_api'] > 0 && 'Others' != $bdm['name']) {
                    $total_generated += $bdm['generated'];
                    $total_cancelled += $bdm['cancelled'];
                    $total_issued += $bdm['issued_api'];

                    $this->SetFillColor($color[0], $color[1], $color[2]);
                    $this->RoundedRect($x, $this->GetY(), $width, 40, 5, '24', 'DF');

                    $image_path = '';

                    if (empty($bdm['image'])) {
                        $image_path = $this->default_image;
                    } else {
                        $image_path = $this->uploads_folder . $bdm['image'];
                    }

                    if (file_exists($image_path)) {
                        $this->Image($image_path, $x + 5, $this->GetY() + 5, 30, 30);
                    } else {
                        $this->Image($this->default_image, $x + 5, $this->GetY() + 5, 30, 30);
                    }

                    $color[0] += 18;
                    $color[1] += 11;
                    $color[2] += 7;

                    $this->SetTextColor(0, 0, 0);
                    $this->SetX($this->GetX());
                    $this->SetY($this->GetY() + 10);

                    //Name
                    $this->SetFont('Calibri', 'B', 15);
                    $this->Cell($cellX, 8, '', '', '0', 'L');
                    $this->Cell(50, 8, 'Marketer: ' . $bdm['name'], '', '1', 'L');

                    //Policies Issued
                    $this->SetFont('Calibri', 'B', 15);
                    $this->Cell($cellX, 8, '', '', '0', 'L');
                    $this->Cell(50, 8, 'Issued API from Leads Generated: $' . number_format($bdm['issued_api'], 2), '', '1', 'L');

                    $this->SetFont('Calibri', 'B', 15);
                    $this->Cell($cellX, 8, '', '', '0', 'L');
                    $this->Cell(50, 8, 'Issued Deals from Leads Generated: ' . $bdm['deals'], '', '1', 'L');
                    $this->Ln(20);
                    $width -= 10;
                    $x += 10;
                    $cellX += 10;
                }
            }
        }

        $this->SetFont('Arial', 'B', 15);
    }

    public function BDMKSPage($bdms, $quarterTitle)
    {
        //page 2 BiMonthly API
        $this->AddPage();

        if ($bdms[0]['deals'] > 0) {
            $this->Header1(15, 'BDM with the Highest Kiwisaver Application from Leads Generated from ' . $quarterTitle, 16);
        } else {
            $this->Header1(15, 'BDM with the Highest Kiwisaver Application from Leads Generated from ' . $quarterTitle, 16);
        }

        $image_path = '';

        if (empty($bdms[0]['image'])) {
            $image_path = $this->default_image;
        } else {
            $image_path = $this->uploads_folder . $bdms[0]['image'];
        }

        if (file_exists($image_path)) {
            $this->Image($image_path, 20, 30, 75, 75);
        } else {
            $this->Image($this->default_image, 20, 30, 75, 75);
        }

        $this->SetFillColor(255, 255, 255);

        $this->Rect(100, 35, 100, 42.5, 'DF');
        $this->SetTextColor(0, 0, 0);
        $this->SetY(38);
        //Name
        $this->SetFont('Calibri', 'B', 20);
        $this->Cell(92, 8, '', '', '0', 'L');
        $this->Cell(50, 8, 'BDM:', '', '1', 'L');
        $this->SetFont('Calibri', '', 20);
        $this->Cell(100, 8, '', '', '0', 'L');

        $this->Cell(50, 8, $bdms[0]['name'], '', '1', 'L');
        $this->Ln(5);

        //Total API
        $this->SetFont('Calibri', 'B', 20);
        $this->Cell(92, 8, '', '', '0', 'L');
        $this->Cell(50, 8, 'Total Deals:', '', '1', 'L');
        $this->SetFont('Calibri', '', 20);
        $this->Cell(100, 8, '', '', '0', 'L');
        $this->Cell(50, 8, $bdms[0]['deals'], '', '1', 'L');

        $this->Header1(125, 'BDM Cumulative KiwiSavers Performance from ' . $quarterTitle, 20);

        $this->SetDrawColor(0, 0, 0);
        $this->SetX(8);
        $this->SetY(140);
        $this->SetFont('Calibri', 'B', 15);
        $this->SetDrawColor(0, 0, 0);
        $this->SetFillColor(255, 255, 255);

        $this->Cell(130, 10, 'Name', '1', '0', 'C', true);
        $this->Cell(65, 10, 'Total Deals', '1', '1', 'C', true);

        $total_deals = 0;

        $this->SetFont('Arial', '', 15);

        foreach ($bdms as $bdm) {
            if ($bdm['deals'] > 0) {
                $this->Cell(130, 10, $bdm['name'], 1, '0', 'L', true);

                $total_deals += $bdm['deals'];

                $this->Cell(65, 10, $bdm['deals'], 1, '1', 'C', true);
            }
        }

        $this->SetFont('Arial', 'B', 15);
        $this->Cell(130, 10, 'Total', 1, '0', 'L', true);

        $this->Cell(65, 10, $total_deals, 1, '1', 'C', true);
    }

    public function TMPage($tms)
    {
        //page 2 BiMonthly API
        $this->AddPage();

        if ($tms[0]['issued_api'] > 0) {
            $this->Header1(15, 'TM with Highest Cumulative API from Leads Generated from ' . $quarterTitle, 18);
        } else {
            $this->Header1(15, 'TM with Highest Cumulative API from Leads Generated from ' . $quarterTitle, 18);
        }

        $image_path = '';

        if (empty($tms[0]['image'])) {
            $image_path = $this->default_image;
        } else {
            $image_path = $this->uploads_folder . $tms[0]['image'];
        }

        if (file_exists($image_path)) {
            $this->Image($image_path, 20, 30, 75, 75);
        } else {
            $this->Image($this->default_image, 20, 30, 75, 75);
        }

        $this->SetFillColor(255, 255, 255);

        $this->Rect(100, 35, 100, 65, 'DF');
        $this->SetTextColor(0, 0, 0);
        $this->SetY(38);

        //Name
        $this->SetFont('Calibri', 'B', 20);
        $this->Cell(92, 8, '', '', '0', 'L');
        $this->Cell(50, 8, 'TM:', '', '1', 'L');
        $this->SetFont('Calibri', '', 20);
        $this->Cell(100, 8, '', '', '0', 'L');

        $this->Cell(50, 8, $tms[0]['name'], '', '1', 'L');
        $this->Ln(5);

        //Policies Issued
        $this->SetFont('Calibri', 'B', 18);
        $this->Cell(92, 8, '', '', '0', 'L');
        $this->Cell(50, 8, 'Issued API from Leads Generated:', '', '1', 'L');
        $this->SetFont('Calibri', '', 20);
        $this->Cell(100, 8, '', '', '0', 'L');
        $this->Cell(50, 8, '$' . number_format($tms[0]['issued_api'], 2), '', '1', 'L');
        $this->Ln(5);

        //Total API
        $this->SetFont('Calibri', 'B', 20);
        $this->Cell(92, 8, '', '', '0', 'L');
        $this->Cell(50, 8, 'Total Leads:', '', '1', 'L');
        $this->SetFont('Calibri', '', 20);
        $this->Cell(100, 8, '', '', '0', 'L');
        $this->Cell(50, 8, $tms[0]['generated'] - $tms[0]['cancelled'], '', '1', 'L');

        $this->Header1(125, 'TM Cumulative Performance from ' . $quarterTitle, 18);

        $this->SetDrawColor(0, 0, 0);
        $this->SetX(8);
        $this->SetY(140);
        $this->SetFont('Calibri', 'B', 15);
        $this->SetDrawColor(0, 0, 0);
        $this->SetFillColor(255, 255, 255);

        $this->Cell(60, 7, '', 'TLR', '0', 'C', true);
        $this->Cell(30, 7, 'Total Leads', 'TLR', '0', 'C', true);
        $this->Cell(30, 7, '# Of', 'TLR', '0', 'C', true);
        $this->Cell(30, 7, '# Of', 'TLR', '0', 'C', true);
        $this->Cell(45, 7, 'Issued API from', 'TLR', '1', 'C', true);

        $this->Cell(60, 7, '', 'BLR', '0', 'C', true);
        $this->Cell(30, 7, '(Gen - Canx)', 'BLR', '0', 'C', true);
        $this->Cell(30, 7, 'Submissions', 'BLR', '0', 'C', true);
        $this->Cell(30, 7, 'KiwiSavers', 'BLR', '0', 'C', true);
        $this->Cell(45, 7, 'Leads Generated', 'BLR', '1', 'C', true);

        $this->SetY(140);
        $this->Cell(60, 14, 'Name', '', '0', 'C', false);
        $this->Cell(30, 14, '', 'TLR', '0', 'C', false);
        $this->Cell(30, 14, '', 'TLR', '0', 'C', false);
        $this->Cell(30, 14, '', 'TLR', '0', 'C', false);
        $this->Cell(45, 14, '', '', '1', 'C', false);

        $total_submissions = 0;
        $total_kiwisavers = 0;
        $total_generated = 0;
        $total_cancelled = 0;
        $total_issued = 0;

        $this->SetFont('Arial', '', 15);

        foreach ($tms as $tm) {
            if ($tm['generated'] > 0) {
                $this->Cell(60, 10, $tm['name'], 1, '0', 'L', true);

                $total_submissions += $tm['submissions'];
                $total_kiwisavers += $tm['kiwisavers'];
                $total_generated += $tm['generated'];
                $total_cancelled += $tm['cancelled'];
                $total_issued += $tm['issued_api'];

                $this->Cell(30, 10, $tm['generated'], 1, '0', 'C', true);
                $this->Cell(30, 10, $tm['submissions'], 1, '0', 'C', true);
                $this->Cell(30, 10, $tm['kiwisavers'], 1, '0', 'C', true);

                $this->Cell(45, 10, '$' . number_format($tm['issued_api'], 2), 1, '1', 'C', true);
            }
        }

        $this->SetFont('Arial', 'B', 15);
        $this->Cell(60, 10, 'Total', 1, '0', 'L', true);
        $this->Cell(30, 10, $total_generated - $total_cancelled, 1, '0', 'C', true);
        $this->Cell(30, 10, $total_submissions, 1, '0', 'C', true);
        $this->Cell(30, 10, $total_kiwisavers, 1, '0', 'C', true);

        $this->Cell(45, 10, '$' . number_format($total_issued, 2), 1, '1', 'C', true);
    }

    public function TMKSPage($tms)
    {
        //page 2 BiMonthly API
        $this->AddPage();

        if ($tms[0]['deals'] > 0) {
            $this->Header1(15, 'TM with the Highest Kiwisaver Application from Leads Generated', 18);
        } else {
            $this->Header1(15, 'TM with the Highest Kiwisaver Application from Leads Generated', 18);
        }

        $image_path = '';

        if (empty($tms[0]['image'])) {
            $image_path = $this->default_image;
        } else {
            $image_path = $this->uploads_folder . $tms[0]['image'];
        }

        if (file_exists($image_path)) {
            $this->Image($image_path, 20, 30, 75, 75);
        } else {
            $this->Image($this->default_image, 20, 30, 75, 75);
        }

        $this->SetFillColor(255, 255, 255);

        $this->Rect(100, 35, 100, 42.5, 'DF');
        $this->SetTextColor(0, 0, 0);
        $this->SetY(38);
        //Name
        $this->SetFont('Calibri', 'B', 20);
        $this->Cell(92, 8, '', '', '0', 'L');
        $this->Cell(50, 8, 'TM:', '', '1', 'L');
        $this->SetFont('Calibri', '', 20);
        $this->Cell(100, 8, '', '', '0', 'L');

        $this->Cell(50, 8, $tms[0]['name'], '', '1', 'L');
        $this->Ln(5);

        //Total API
        $this->SetFont('Calibri', 'B', 20);
        $this->Cell(92, 8, '', '', '0', 'L');
        $this->Cell(50, 8, 'Total Deals:', '', '1', 'L');
        $this->SetFont('Calibri', '', 20);
        $this->Cell(100, 8, '', '', '0', 'L');
        $this->Cell(50, 8, $tms[0]['deals'], '', '1', 'L');

        $this->Header1(125, 'TM Cumulative KiwiSavers Performance', 20);

        $this->SetDrawColor(0, 0, 0);
        $this->SetX(8);
        $this->SetY(140);
        $this->SetFont('Calibri', 'B', 15);
        $this->SetDrawColor(0, 0, 0);
        $this->SetFillColor(255, 255, 255);

        $this->Cell(130, 10, 'Name', '1', '0', 'C', true);
        $this->Cell(65, 10, 'Total Deals', '1', '1', 'C', true);

        $total_deals = 0;

        $this->SetFont('Arial', '', 15);

        foreach ($tms as $tm) {
            if ($tm['deals'] > 0) {
                $this->Cell(130, 10, $tm['name'], 1, '0', 'L', true);

                $total_deals += $tm['deals'];

                $this->Cell(65, 10, $tm['deals'], 1, '1', 'C', true);
            }
        }

        $this->SetFont('Arial', 'B', 15);
        $this->Cell(130, 10, 'Total', 1, '0', 'L', true);

        $this->Cell(65, 10, $total_deals, 1, '1', 'C', true);
    }

    public function RecordsPage($records)
    {
        foreach ($records as $index => $record) {
            $image_path = '';

            if (empty($record['image'])) {
                $image_path = $this->default_image;
            } else {
                $image_path = $this->uploads_folder . $record['image'];
            }

            if (0 == $index % 2) {
                $this->AddPage();
                $record_type = $record['type'] ?? null;
                $this->Header1(15, $record_type, 20);

                if (file_exists($image_path)) {
                    $this->Image($image_path, 20, 30, 90, 90);
                } else {
                    $this->Image($this->default_image, 20, 30, 90, 90);
                }

                $this->SetFillColor(255, 255, 255);

                $this->Rect(120, 42.5, 80, 65, 'DF');
                $this->SetTextColor(0, 0, 0);
                $this->SetY(45.5);

                //Name
                $this->SetFont('Calibri', 'B', 20);
                $this->Cell(112, 8, '', '', '0', 'L');
                $this->Cell(50, 8, $record['role'] . ':', '', '1', 'L');
                $this->SetFont('Calibri', '', 20);
                $this->Cell(120, 8, '', '', '0', 'L');
                $this->Cell(50, 8, $record['name'], '', '1', 'L');
                $this->Ln(5);

                //Policies Issued
                $this->SetFont('Calibri', 'B', 20);
                $this->Cell(112, 8, '', '', '0', 'L');
                $this->Cell(50, 8, 'Date:', '', '1', 'L');
                $this->SetFont('Calibri', '', 20);
                $this->Cell(120, 8, '', '', '0', 'L');
                $this->Cell(50, 8, $record['date'], '', '1', 'L');
                $this->Ln(5);
                //Total API
                $this->SetFont('Calibri', 'B', 20);
                $this->Cell(112, 8, '', '', '0', 'L');
                $this->Cell(50, 8, $record['record_label'] . ':', '', '1', 'L');
                $this->SetFont('Calibri', '', 20);
                $this->Cell(120, 8, '', '', '0', 'L');
                $record_display = ('Count' == $record['record_type']) ? $record['record'] : '$' . number_format($record['record'], 2);
                $this->Cell(50, 8, $record_display, '', '1', 'L');
            } else {
                $record_type = $record['type'] ?? null;
                $this->Header1(140, $record_type, 20);

                if (file_exists($image_path)) {
                    $this->Image($image_path, 110, 155, 90, 90);
                } else {
                    $this->Image($this->default_image, 110, 155, 90, 90);
                }

                $this->SetFillColor(255, 255, 255);

                $this->Rect(20, 167.5, 80, 65, 'DF');
                $this->SetTextColor(0, 0, 0);
                $this->SetY(170.5);

                //Name
                $this->SetFont('Calibri', 'B', 20);
                $this->Cell(12, 8, '', '', '0', 'L');
                $this->Cell(50, 8, $record['role'] . ':', '', '1', 'L');
                $this->SetFont('Calibri', '', 20);
                $this->Cell(20, 8, '', '', '0', 'L');
                $this->Cell(50, 8, $record['name'], '', '1', 'L');
                $this->Ln(5);

                //Policies Issued
                $this->SetFont('Calibri', 'B', 20);
                $this->Cell(12, 8, '', '', '0', 'L');
                $this->Cell(50, 8, 'Date:', '', '1', 'L');
                $this->SetFont('Calibri', '', 20);
                $this->Cell(20, 8, '', '', '0', 'L');
                $this->Cell(50, 8, $record['date'], '', '1', 'L');
                $this->Ln(5);
                //Total API
                $this->SetFont('Calibri', 'B', 20);
                $this->Cell(12, 8, '', '', '0', 'L');
                $this->Cell(50, 8, $record['record_label'] . ':', '', '1', 'L');
                $this->SetFont('Calibri', '', 20);
                $this->Cell(20, 8, '', '', '0', 'L');
                $record_display = ('Count' == $record['record_type']) ? $record['record'] : '$' . number_format($record['record'], 2);
                $this->Cell(50, 8, $record_display, '', '1', 'L');
            }
        }
    }

    public function NewFacesPage($persons)
    {
        //page 2 BiMonthly API
        $this->AddPage();

        $this->Header1(15, 'New Faces in EliteInsure', 20);

        $y = 30;

        foreach ($persons as $index => $person) {
            $image_path = '';

            if (empty($person['image'])) {
                $image_path = $this->default_image;
            } else {
                $image_path = $this->uploads_folder . $person['image'];
            }

            if (0 == $index % 2) {
                if (file_exists($image_path)) {
                    $this->Image($image_path, 20, $y, 65, 65);
                } else {
                    $this->Image($this->default_image, 20, $y, 65, 65);
                }

                $this->SetFillColor(255, 255, 255);

                $this->Rect(100, $y + 7.5, 100, 50, 'DF');
                $this->SetTextColor(0, 0, 0);
                $this->SetY($y + 14);
                //Name
                $this->SetFont('Calibri', 'B', 20);
                $this->Cell(92, 8, '', '', '0', 'L');
                $this->Cell(50, 8, 'Name:', '', '1', 'L');
                $this->SetFont('Calibri', '', 20);
                $this->Cell(100, 8, '', '', '0', 'L');
                $this->Cell(50, 8, $person['name'], '', '1', 'L');
                $this->Ln(3);
                //Role
                $this->SetFont('Calibri', 'B', 20);
                $this->Cell(92, 8, '', '', '0', 'L');
                $this->Cell(50, 8, 'Role:', '', '1', 'L');
                $this->SetFont('Calibri', '', 20);
                $this->Cell(100, 8, '', '', '0', 'L');
                $this->Cell(50, 8, $person['role'], '', '1', 'L');
                $this->Ln(3);
            } else {
                if (file_exists($image_path)) {
                    $this->Image($image_path, 135, $y, 65, 65);
                } else {
                    $this->Image($this->default_image, 135, $y, 65, 65);
                }

                $this->SetFillColor(255, 255, 255);

                $this->Rect(20, $y + 7.5, 100, 50, 'DF');
                $this->SetTextColor(0, 0, 0);
                $this->SetY($y + 14);
                //Name
                $this->SetFont('Calibri', 'B', 20);
                $this->Cell(12, 8, '', '', '0', 'L');
                $this->Cell(50, 8, 'Name:', '', '1', 'L');
                $this->SetFont('Calibri', '', 20);
                $this->Cell(20, 8, '', '', '0', 'L');
                $this->Cell(50, 8, $person['name'], '', '1', 'L');
                $this->Ln(3);
                //Role
                $this->SetFont('Calibri', 'B', 20);
                $this->Cell(12, 8, '', '', '0', 'L');
                $this->Cell(50, 8, 'Role:', '', '1', 'L');
                $this->SetFont('Calibri', '', 20);
                $this->Cell(20, 8, '', '', '0', 'L');
                $this->Cell(50, 8, $person['role'], '', '1', 'L');
                $this->Ln(3);
            }

            //Add space for next line
            $y += 60;
        }
    }

    public function BirthdaysPage($persons)
    {
        //page 2 BiMonthly API
        $this->AddPage();

        $this->Header1(15, 'Upcoming Birthdays', 20);

        $y = 30;

        foreach ($persons as $index => $person) {
            $image_path = '';

            if (empty($person['image'])) {
                $image_path = $this->default_image;
            } else {
                $image_path = $this->uploads_folder . $person['image'];
            }

            if (0 == $index % 2) {
                if (0 == $index) {
                    if (file_exists($image_path)) {
                        $this->Image($image_path, 20, $y, 65, 65);
                    } else {
                        $this->Image($image_path, 20, $y, 65, 65);
                    }
                } else {
                    if (file_exists($image_path)) {
                        $this->Image($image_path, 20, $y + 5, 65, 65);
                    } else {
                        $this->Image($this->default_image, 20, $y + 5, 65, 65);
                    }
                }
                $this->SetFillColor(255, 255, 255);

                $this->Rect(100, $y + 10, 100, 50, 'DF');
                $this->SetTextColor(0, 0, 0);
                $this->SetY($y + 11);
                //Name
                $this->SetFont('Calibri', 'B', 20);
                $this->Cell(92, 8, '', '', '0', 'L');
                $this->Cell(50, 8, 'Name:', '', '1', 'L');
                $this->SetFont('Calibri', '', 20);
                $this->Cell(100, 8, '', '', '0', 'L');
                $this->Cell(50, 8, $person['name'], '', '1', 'L');
                //Birthday
                $this->SetFont('Calibri', 'B', 20);
                $this->Cell(92, 8, '', '', '0', 'L');
                $this->Cell(50, 8, 'Role:', '', '1', 'L');
                $this->SetFont('Calibri', '', 20);
                $this->Cell(100, 8, '', '', '0', 'L');
                $this->Cell(50, 8, $person['role'], '', '1', 'L');
                $this->SetFont('Calibri', 'B', 20);
                $this->Cell(92, 8, '', '', '0', 'L');
                $this->Cell(50, 8, 'Birthday:', '', '1', 'L');
                $this->SetFont('Calibri', '', 20);
                $this->Cell(100, 8, '', '', '0', 'L');
                $this->Cell(50, 8, $person['birthday'], '', '1', 'L');
            } else {
                if (file_exists($image_path)) {
                    $this->Image($image_path, 135, $y, 65, 65);
                } else {
                    $this->Image($this->default_image, 135, $y, 65, 65);
                }

                $this->SetFillColor(255, 255, 255);

                $this->Rect(20, $y + 10, 100, 50, 'DF');
                $this->SetTextColor(0, 0, 0);
                $this->SetY($y + 11);
                //Name
                $this->SetFont('Calibri', 'B', 20);
                $this->Cell(12, 8, '', '', '0', 'L');
                $this->Cell(50, 8, 'Name:', '', '1', 'L');
                $this->SetFont('Calibri', '', 20);
                $this->Cell(20, 8, '', '', '0', 'L');
                $this->Cell(50, 8, $person['name'], '', '1', 'L');
                //Role
                $this->SetFont('Calibri', 'B', 20);
                $this->Cell(12, 8, '', '', '0', 'L');
                $this->Cell(50, 8, 'Role:', '', '1', 'L');
                $this->SetFont('Calibri', '', 20);
                $this->Cell(20, 8, '', '', '0', 'L');
                $this->Cell(50, 8, $person['role'], '', '1', 'L');
                //Birthday
                $this->SetFont('Calibri', 'B', 20);
                $this->Cell(12, 8, '', '', '0', 'L');
                $this->Cell(50, 8, 'Birthday:', '', '1', 'L');
                $this->SetFont('Calibri', '', 20);
                $this->Cell(20, 8, '', '', '0', 'L');
                $this->Cell(50, 8, $person['birthday'], '', '1', 'L');
            }

            //Add space for next line
            $y += 65;
        }
    }

    public function WorkAnniversariesPage($persons)
    {
        //page 2 BiMonthly API
        $this->AddPage();
        $this->Header1(15, 'Work Anniversaries', 20);

        $y = 30;

        foreach ($persons as $index => $person) {
            $image_path = '';

            if (empty($person['image'])) {
                $image_path = $this->default_image;
            } else {
                $image_path = $this->uploads_folder . $person['image'];
            }

            if (0 == $index % 2) {
                if (0 == $index) {
                    if (file_exists($image_path)) {
                        $this->Image($image_path, 20, $y, 65, 65);
                    } else {
                        $this->Image($this->default_image, 20, $y + 5, 65, 65);
                    }
                } else {
                    if (file_exists($image_path)) {
                        $this->Image($image_path, 20, $y + 5, 65, 65);
                    } else {
                        $this->Image($this->default_image, 20, $y + 5, 65, 65);
                    }
                }
                $this->SetFillColor(255, 255, 255);

                $this->Rect(100, $y + 10, 100, 50, 'DF');
                $this->SetTextColor(0, 0, 0);
                $this->SetY($y + 11);
                //Name
                $this->SetFont('Calibri', 'B', 20);
                $this->Cell(92, 8, '', '', '0', 'L');
                $this->Cell(50, 8, 'Name:', '', '1', 'L');
                $this->SetFont('Calibri', '', 20);
                $this->Cell(100, 8, '', '', '0', 'L');
                $this->Cell(50, 8, $person['name'], '', '1', 'L');
                //Birthday
                $this->SetFont('Calibri', 'B', 20);
                $this->Cell(92, 8, '', '', '0', 'L');
                $this->Cell(50, 8, 'Years:', '', '1', 'L');
                $this->SetFont('Calibri', '', 20);
                $this->Cell(100, 8, '', '', '0', 'L');
                $this->Cell(50, 8, $person['years'], '', '1', 'L');
                $this->SetFont('Calibri', 'B', 20);
                $this->Cell(92, 8, '', '', '0', 'L');
                $this->Cell(50, 8, 'Work Anniversary:', '', '1', 'L');
                $this->SetFont('Calibri', '', 20);
                $this->Cell(100, 8, '', '', '0', 'L');
                $this->Cell(50, 8, $person['anniversary'], '', '1', 'L');
            } else {
                if (file_exists($image_path)) {
                    $this->Image($image_path, 135, $y, 65, 65);
                } else {
                    $this->Image($this->default_image, 135, $y, 65, 65);
                }

                $this->SetFillColor(255, 255, 255);

                $this->Rect(20, $y + 10, 100, 50, 'DF');
                $this->SetTextColor(0, 0, 0);
                $this->SetY($y + 11);
                //Name
                $this->SetFont('Calibri', 'B', 20);
                $this->Cell(12, 8, '', '', '0', 'L');
                $this->Cell(50, 8, 'Adviser:', '', '1', 'L');
                $this->SetFont('Calibri', '', 20);
                $this->Cell(20, 8, '', '', '0', 'L');
                $this->Cell(50, 8, $person['name'], '', '1', 'L');
                //Role
                $this->SetFont('Calibri', 'B', 20);
                $this->Cell(12, 8, '', '', '0', 'L');
                $this->Cell(50, 8, 'Years:', '', '1', 'L');
                $this->SetFont('Calibri', '', 20);
                $this->Cell(20, 8, '', '', '0', 'L');
                $this->Cell(50, 8, $person['years'], '', '1', 'L');
                //Birthday
                $this->SetFont('Calibri', 'B', 20);
                $this->Cell(12, 8, '', '', '0', 'L');
                $this->Cell(50, 8, 'Work Anniversary:', '', '1', 'L');
                $this->SetFont('Calibri', '', 20);
                $this->Cell(20, 8, '', '', '0', 'L');
                $this->Cell(50, 8, $person['anniversary'], '', '1', 'L');
            }

            //Add space for next line
            $y += 65;
        }
    }

    public function AnnouncementsPage($announcement)
    {
        //page 2 BiMonthly API
        $this->AddPage();

        $this->Header1(15, 'Announcements', 20);

        $this->SetFillColor(255, 255, 255);
        $this->SetDrawColor(255, 255, 255);
        $this->Rect(11, $this->GetY() + 2, 194, 220, 'DF');
        $this->Ln(5);
        $this->SetX(11);
        $this->MultiCell(194, 7, $announcement, 0, 'C', true, 15);
    }

    public function MessagePage($message)
    {
        //page 2 BiMonthly API
        $this->AddPage();

        $this->SetFont('Arial', '', 30);
        $this->Image('images/EliteInsure Horizonal Logo.png', 10, 4, 110);
        $this->Cell(92, 17.5, '', 0, 0);
        $this->Cell(75, 17.5, '', 0, 1, 'L');
        $this->SetFillColor(13, 100, 152);
        $this->SetDrawColor(13, 100, 152);
        $this->Rect(30, 23, 140, 1, 'DF');
        $this->SetFillColor(4, 129, 185);
        $this->SetDrawColor(4, 129, 185);
        $this->Rect(35, 25, 135, 1, 'DF');
        $this->SetFont('Calibri', 'B', 30);
        $this->SetY(25);
        $this->Cell(164, 15, 'From the Desk of Sumit Monga', 0, 1, 'L');
        $this->SetFillColor(255, 255, 255);
        $this->SetDrawColor(255, 255, 255);
        $this->Rect(11, 40, 194, 225, 'DF');
        $this->SetFont('Arial', 'B', 10);
        $this->SetFillColor(15, 68, 99);
        $this->SetDrawColor(15, 68, 99);
        // restore full opacity
        $this->Rect(152, 9, 59, 59, 'DF');
        $this->Image('images/Sir Sumit.png', 153, 10, 57);
        $this->SetY(50);
        $this->SetX(16);
        $this->MultiCell(184, 7, $message, 0, 'L', false, 15);
    }

    public function PhotosPage($photos)
    {
        $this->AddPage();

        $this->Header1(15, 'Photos', 20);

        foreach ($photos as $key => $photo) {
            if ($key) {
                $this->AddPage();
            }

            $this->Image('../indet_photos_stash/' . $photo, 10, $key ? 20 : 30, 195);
        }
    }

    public function ClippingRoundedRect($x, $y, $w, $h, $r, $outline = false)
    {
        $k = $this->k;
        $hp = $this->h;
        $op = $outline ? 'S' : 'n';
        $MyArc = 4 / 3 * (sqrt(2) - 1);

        $this->_out(sprintf('q %.2F %.2F m', ($x + $r) * $k, ($hp - $y) * $k));
        $xc = $x + $w - $r;
        $yc = $y + $r;
        $this->_out(sprintf('%.2F %.2F l', $xc * $k, ($hp - $y) * $k));

        $this->_Arc($xc + $r * $MyArc, $yc - $r, $xc + $r, $yc - $r * $MyArc, $xc + $r, $yc);
        $xc = $x + $w - $r;
        $yc = $y + $h - $r;
        $this->_out(sprintf('%.2F %.2F l', ($x + $w) * $k, ($hp - $yc) * $k));
        $this->_Arc($xc + $r, $yc + $r * $MyArc, $xc + $r * $MyArc, $yc + $r, $xc, $yc + $r);
        $xc = $x + $r;
        $yc = $y + $h - $r;
        $this->_out(sprintf('%.2F %.2F l', $xc * $k, ($hp - ($y + $h)) * $k));
        $this->_Arc($xc - $r * $MyArc, $yc + $r, $xc - $r, $yc + $r * $MyArc, $xc - $r, $yc);
        $xc = $x + $r;
        $yc = $y + $r;
        $this->_out(sprintf('%.2F %.2F l', ($x) * $k, ($hp - $yc) * $k));
        $this->_Arc($xc - $r, $yc - $r * $MyArc, $xc - $r * $MyArc, $yc - $r, $xc, $yc - $r);
        $this->_out(' W ' . $op);
    }

    public function Strings($advisers, $bimonthRange)
    {
        $sorted_advisers = [];
        $t_array = [];
        $p_array = [];
        $g_array = [];
        $s_array = [];

        foreach ($advisers as $index => $adviser) {
            if ('Titanium' == $adviser['score']) {
                $adviser[`platinum`] = 0;
                $adviser[`gold`] = 0;
                $adviser[`silver`] = 0;

                array_push($t_array, $adviser);
                $titanium = array_column($t_array, 'titanium');
            } else {
                if ('Platinum' == $adviser['score']) {
                    $adviser[`titanium`] = 0;
                    $adviser[`gold`] = 0;
                    $adviser[`silver`] = 0;

                    array_push($p_array, $adviser);
                    $platinum = array_column($p_array, 'platinum');
                } else {
                    if ('Gold' == $adviser['score']) {
                        $adviser[`titanium`] = 0;
                        $adviser[`platinum`] = 0;
                        $adviser[`silver`] = 0;

                        array_push($g_array, $adviser);
                        $gold = array_column($g_array, 'gold');
                    } else {
                        if ('Silver' == $adviser['score']) {
                            array_push($s_array, $adviser);
                            $silver = array_column($s_array, 'silver');
                        }
                    }
                }
            }
        }

        if ($t_array) {
            array_multisort($titanium, SORT_DESC, $t_array);

            foreach ($t_array as $data) {
                array_push($sorted_advisers, $data);
            }
        }

        if ($p_array) {
            array_multisort($platinum, SORT_DESC, $p_array);

            foreach ($p_array as $data) {
                array_push($sorted_advisers, $data);
            }
        }

        if ($g_array) {
            array_multisort($gold, SORT_DESC, $g_array);

            foreach ($g_array as $data) {
                array_push($sorted_advisers, $data);
            }
        }

        if ($s_array) {
            array_multisort($silver, SORT_DESC, $s_array);

            foreach ($s_array as $data) {
                array_push($sorted_advisers, $data);
            }
        }

        $this->AddPage();

        $from = date_create_from_format('Ymd', $bimonthRange->from);
        $to = date_create_from_format('Ymd', $bimonthRange->to);
        $range = $from->format('j') . '-' . $to->format('j F Y');

        $this->Header1(15, 'Strings as of ' . $range, 20);
        $this->Ln(6);
        $this->SetFillColor(102, 163, 194);
        $this->SetDrawColor(102, 163, 194);
        $this->Rect(10.5, $this->GetY() - 5, 195, 12, 'DF');
        $this->SetTextColor(255, 255, 255);
        $this->Cell(200, 3, 'Everything good happens to a stringwriter.', '', '1', 'C');

        $this->SetY(45);

        $this->SetFont('Arial', 'B', 15);
        $this->SetFillColor(255, 255, 255);
        $this->SetDrawColor(0, 0, 0);
        $this->SetTextColor(0, 0, 0);

        // Table header
        $this->SetDrawColor(0, 0, 0);
        $this->SetFont('Arial', 'B', 12);
        $this->SetFillColor(255, 255, 255);
        $this->SetTextColor(0, 0, 0);
        $this->Cell(95, 15, 'Adviser', 1, 0, 'C', true);
        $this->Cell(25, 15, 'Silver', 1, 0, 'C', true);
        $this->Cell(25, 15, 'Gold', 1, 0, 'C', true);
        $this->Cell(25, 15, 'Platinum', 1, 0, 'C', true);
        $this->Cell(25, 15, 'Titanium', 1, 0, 'C', true);
        $this->Ln();

        $fillColors = [
            'Silver' => [
                'red' => 192,
                'green' => 192,
                'blue' => 192,
            ],
            'Gold' => [
                'red' => 255,
                'green' => 239,
                'blue' => 148,
            ],
            'Platinum' => [
                'red' => 212,
                'green' => 91,
                'blue' => 91,
            ],
            'Titanium' => [
                'red' => 102,
                'green' => 163,
                'blue' => 194,
            ],
        ];

        $drawColors = [
            'Silver' => [
                'red' => 163,
                'green' => 163,
                'blue' => 163,
            ],
            'Gold' => [
                'red' => 214,
                'green' => 181,
                'blue' => 0,
            ],
            'Platinum' => [
                'red' => 207,
                'green' => 21,
                'blue' => 21,
            ],
            'Titanium' => [
                'red' => 102,
                'green' => 163,
                'blue' => 200,
            ],
        ];

        // Table first row

        $image_path = '';

        if (empty($sorted_advisers[0]['image'])) {
            $image_path = $this->default_image;
        } else {
            $image_path = $this->uploads_folder . $sorted_advisers[0]['image'];
        }

        $score = $sorted_advisers[0]['score'];

        $this->SetTextColor(0, 0, 0);
        $this->SetFillColor($fillColors[$score]['red'], $fillColors[$score]['green'], $fillColors[$score]['blue']);
        // $this->SetDrawColor($drawColors[$score]['red'], $drawColors[$score]['green'], $drawColors[$score]['blue']);
        $this->SetFont('Arial', 'B', 20);
        $this->Cell(40, 40, $this->Image($image_path, $this->GetX(), $this->GetY(), 40, 40), 1, 0, 'C', false);
        $this->CellFitScale(55, 40, strtoupper($sorted_advisers[0]['name']), 1, 0, 'C', true);
        $this->Cell(25, 40, $sorted_advisers[0]['silver'], 1, 0, 'C', true);
        $this->Cell(25, 40, $sorted_advisers[0]['gold'], 1, 0, 'C', true);
        $this->Cell(25, 40, $sorted_advisers[0]['platinum'], 1, 0, 'C', true);
        $this->Cell(25, 40, $sorted_advisers[0]['titanium'], 1, 0, 'C', true);
        $this->Ln();

        // Table preceding row
        $this->SetFont('Arial', 'B', 12);

        foreach (array_slice($sorted_advisers, 1) as $index => $adviser) {
            if (empty($adviser['image'])) {
                $image = $this->default_image;
            } else {
                $image = $this->uploads_folder . $adviser['image'];
            }

            $score = $adviser['score'];

            $this->SetFillColor($fillColors[$score]['red'], $fillColors[$score]['green'], $fillColors[$score]['blue']);
            // $this->SetDrawColor($drawColors[$score]['red'], $drawColors[$score]['green'], $drawColors[$score]['blue']);
            $this->Cell(15, 15, $this->Image($image, $this->GetX(), $this->GetY(), 15, 15), 1, 0, 'C');
            $this->CellFitScale(80, 15, strtoupper($adviser['name']), 1, 0, 'C', true);
            $this->Cell(25, 15, $adviser['silver'], 1, 0, 'C', true);
            $this->Cell(25, 15, $adviser['gold'], 1, 0, 'C', true);
            $this->Cell(25, 15, $adviser['platinum'], 1, 0, 'C', true);
            $this->Cell(25, 15, $adviser['titanium'], 1, 0, 'C', true);
            $this->Ln();
        }

        /* foreach (array_slice($sorted_advisers, 1) as $index => $adviser) {
            if (5 != $index) {
                if ('Others' != $adviser['name'] && $adviser['score']) {
                    if ('Titanium' == $adviser['score']) {
                        $this->SetDrawColor(102, 163, 200);
                        $this->SetFillColor(102, 163, 194);
                    } elseif ('Platinum' == $adviser['score']) {
                        $this->SetDrawColor(207, 21, 21);
                        $this->setFillColor(212, 91, 91);
                    } elseif ('Gold' == $adviser['score']) {
                        $this->SetDrawColor(214, 181, 0);
                        $this->setFillColor(255, 239, 148);
                    } else {
                        $this->SetDrawColor(163, 163, 163);
                        $this->setFillColor(192, 192, 192);
                    }

                    $this->Ln(10);
                    $this->RoundedRect($this->GetX() + 45, $this->GetY() - 5, 150, 30, 5, '24', 'DF');
                    $image_path = '';

                    if (empty($adviser['image'])) {
                        $image_path = $this->default_image;
                    } else {
                        $image_path = $this->uploads_folder . $adviser['image'];
                    }

                    if (file_exists($image_path)) {
                        $this->Image($image_path, $this->GetX() + 50, $this->GetY(), 20, 20);
                    } else {
                        $this->Image($this->default_image, $this->GetX() + 50, $this->GetY(), 20, 20);
                    }
                    $this->Ln(25);

                    $this->SetTextColor(0, 0, 0);
                    $this->SetX($this->GetX() + 40);
                    $this->SetY($this->GetY() - 30);
                    $this->Cell(50, 6, '', '', '1', 'L');
                    $this->SetFont('Calibri', 'B', 20);
                    $this->Cell(75, 6, '', '', '0', 'L');
                    $this->Cell(50, 6, strtoupper($adviser['name']), '', '1', 'L');

                    $this->SetFont('Calibri', '', 15);
                    $this->Cell(60, 7, '', '', '0', 'L');
                    $this->Cell(15, 7, '', '', '0', 'L');
                    $this->Cell(10, 7, 'Score: ' . $adviser['score'], '', '0', 'L');
                    $this->Cell(30, 7, '', '', '0', 'L');
                    $this->Cell(50, 7, 'String: ' . $adviser[strtolower($adviser['score'])], '', '0', 'L');
                    $this->Ln(15);
                    $this->SetDrawColor(0, 0, 0);
                }
            } else {
                $this->AddPage();

                if ('Others' != $adviser['name'] && $adviser['score']) {
                    if ('Titanium' == $adviser['score']) {
                        $this->SetDrawColor(102, 163, 200);
                        $this->SetFillColor(102, 163, 194);
                    } elseif ('Platinum' == $adviser['score']) {
                        $this->SetDrawColor(207, 21, 21);
                        $this->setFillColor(212, 91, 91);
                    } elseif ('Gold' == $adviser['score']) {
                        $this->SetDrawColor(214, 181, 0);
                        $this->setFillColor(255, 239, 148);
                    } else {
                        $this->SetDrawColor(163, 163, 163);
                        $this->setFillColor(192, 192, 192);
                    }

                    $this->Ln(10);
                    $this->RoundedRect($this->GetX() + 45, $this->GetY() - 5, 150, 30, 5, '24', 'DF');
                    $image_path = '';

                    if (empty($adviser['image'])) {
                        $image_path = $this->default_image;
                    } else {
                        $image_path = $this->uploads_folder . $adviser['image'];
                    }

                    if (file_exists($image_path)) {
                        $this->Image($image_path, $this->GetX() + 50, $this->GetY(), 20, 20);
                    } else {
                        $this->Image($this->default_image, $this->GetX() + 50, $this->GetY(), 20, 20);
                    }
                    $this->Ln(25);

                    $this->SetTextColor(0, 0, 0);
                    $this->SetX($this->GetX() + 40);
                    $this->SetY($this->GetY() - 30);
                    $this->Cell(50, 6, '', '', '1', 'L');
                    $this->SetFont('Calibri', 'B', 20);
                    $this->Cell(75, 6, '', '', '0', 'L');
                    $this->Cell(50, 6, strtoupper($adviser['name']), '', '1', 'L');

                    $this->SetFont('Calibri', '', 15);
                    $this->Cell(60, 7, '', '', '0', 'L');
                    $this->Cell(15, 7, '', '', '0', 'L');
                    $this->Cell(10, 7, 'Score: ' . $adviser['score'], '', '0', 'L');
                    $this->Cell(30, 7, '', '', '0', 'L');
                    $this->Cell(50, 7, 'String: ' . $adviser[strtolower($adviser['score'])], '', '0', 'L');
                    $this->Ln(15);
                    $this->SetDrawColor(0, 0, 0);
                }
            }
        } */
    }

    public function WinnerScore($advisers)
    {
        $this->AddPage();

        $this->Header1(15, 'Adviser with the Highest Score');

        $image_path = '';

        if (empty($advisers[0]['image'])) {
            $image_path = $this->default_image;
        } else {
            $image_path = $this->uploads_folder . $advisers[0]['image'];
        }

        if (file_exists($image_path)) {
            $this->Image($image_path, 20, 30, 75, 75);
        } else {
            $this->Image($this->default_image, 20, 30, 75, 75);
        }

        $this->SetFillColor(255, 255, 255);

        $this->Rect(100, 35, 100, 65, 'DF');
        $this->SetTextColor(0, 0, 0);
        $this->SetY(38);

        //Name
        $this->SetFont('Calibri', 'B', 20);
        $this->Cell(92, 8, '', '', '0', 'L');
        $this->Cell(50, 8, "Adviser: {$advisers[0]['name']}", '', '1', 'L');
        $this->SetFont('Calibri', '', 20);
        $this->Ln(5);
        //Policies Issued
        $this->SetFont('Calibri', 'B', 20);
        $this->Cell(92, 8, '', '', '0', 'L');
        $this->SetFont('Calibri', '', 20);

        if ($advisers[0]['deals'] >= 5 && $advisers[0]['issued_api'] >= 7500) {
            $this->SetFont('Calibri', 'B', 20);
            $this->Cell(50, 8, 'Score: Titanium', '', '1', 'L');
        } elseif ($advisers[0]['deals'] >= 4 && $advisers[0]['issued_api'] >= 6000) {
            $this->SetFont('Calibri', 'B', 20);
            $this->Cell(50, 8, 'Score: Platinum', '', '1', 'L');
        } elseif ($advisers[0]['deals'] >= 3 && $advisers[0]['issued_api'] >= 4500) {
            $this->SetFont('Calibri', 'B', 20);
            $this->Cell(50, 8, 'Score: Gold', '', '1', 'L');
        } elseif ($advisers[0]['deals'] >= 2 && $advisers[0]['issued_api'] >= 2000) {
            $this->SetFont('Calibri', 'B', 20);
            $this->Cell(50, 8, 'Score: Silver', '', '1', 'L');
        } else {
        }

        //Tables
        $this->Header1(125, 'Bi-Monthly Winner Scores');

        $this->SetDrawColor(0, 0, 0);
        $this->SetX(8);
        $this->SetY(140);
        $this->SetFont('Arial', 'B', 15);
        $this->SetDrawColor(0, 0, 0);
        $this->SetFillColor(255, 255, 255);
        $this->Cell(110, 15, 'Adviser', 1, '0', 'C', true);
        $this->Cell(85, 15, 'Score', 1, '1', 'C', true);
        $this->SetFont('Arial', '', 15);

        $total = 0;
        $total_api = 0;

        foreach ($advisers as  $index => $adviser) {
            if (5 != $index) {
                if ($adviser['deals'] > 0) {
                    $total += $adviser['deals'];
                    $total_api += $adviser['issued_api'];
                    $this->SetFont('Arial', 'B', 15);

                    if ('Others' !== $adviser['name']) {
                        if ($adviser['deals'] >= 5 && $adviser['issued_api'] >= 7500) {
                            if (empty($adviser['image'])) {
                                $image_path = $this->default_image;
                            } else {
                                $image_path = $this->uploads_folder . $adviser['image'];
                            }

                            if (file_exists($image_path)) {
                                $this->Cell(15, 15, $this->Image($image_path, $this->GetX(), $this->GetY(), 15, 15), 1, '0', 'L', false);
                            } else {
                                $this->Cell(15, 15, $this->Image($this->default_image, $this->GetX(), $this->GetY(), 15), 1, '0', 'L', false);
                            }
                            $this->Cell(95, 15, $adviser['name'], 1, '0', 'C', true);
                            $this->SetFont('Arial', '', 15);
                            $this->SetFillColor(102, 163, 194);
                            $this->Cell(85, 15, 'Titanium', 1, '1', 'C', true);
                            $this->setFillColor(255, 255, 255);
                        } elseif ($adviser['deals'] >= 4 && $adviser['issued_api'] >= 6000) {
                            if (empty($adviser['image'])) {
                                $image_path = $this->default_image;
                            } else {
                                $image_path = $this->uploads_folder . $adviser['image'];
                            }

                            if (file_exists($image_path)) {
                                $this->Cell(15, 15, $this->Image($image_path, $this->GetX(), $this->GetY(), 15, 15), 1, '0', 'L', false);
                            } else {
                                $this->Cell(15, 15, $this->Image($this->default_image, $this->GetX(), $this->GetY(), 15, 15), 1, '0', 'L', false);
                            }
                            $this->Cell(95, 15, $adviser['name'], 1, '0', 'C', true);
                            $this->SetFont('Arial', '', 15);
                            $this->setFillColor(212, 91, 91);
                            $this->Cell(85, 15, 'Platinum', 1, '1', 'C', true);
                            $this->setFillColor(255, 255, 255);
                        } elseif ($adviser['deals'] >= 3 && $adviser['issued_api'] >= 4500) {
                            if (empty($adviser['image'])) {
                                $image_path = $this->default_image;
                            } else {
                                $image_path = $this->uploads_folder . $adviser['image'];
                            }

                            if (file_exists($image_path)) {
                                $this->Cell(15, 15, $this->Image($image_path, $this->GetX(), $this->GetY(), 15, 15), 1, '0', 'L', false);
                            } else {
                                $this->Cell(15, 15, $this->Image($this->default_image, $this->GetX(), $this->GetY(), 15, 15), 1, '0', 'L', false);
                            }
                            $this->Cell(95, 15, $adviser['name'], 1, '0', 'C', true);
                            $this->SetFont('Arial', '', 15);
                            $this->setFillColor(255, 239, 148);
                            $this->Cell(85, 15, 'Gold', 1, '1', 'C', true);
                            $this->setFillColor(255, 255, 255);
                        } elseif ($adviser['deals'] >= 2 && $adviser['issued_api'] >= 2000) {
                            if (empty($adviser['image'])) {
                                $image_path = $this->default_image;
                            } else {
                                $image_path = $this->uploads_folder . $adviser['image'];
                            }

                            if (file_exists($image_path)) {
                                $this->Cell(15, 15, $this->Image($image_path, $this->GetX(), $this->GetY(), 15, 15), 1, '0', 'L', false);
                            } else {
                                $this->Cell(15, 15, $this->Image($this->default_image, $this->GetX(), $this->GetY(), 15, 15), 1, '0', 'L', false);
                            }
                            $this->Cell(95, 15, $adviser['name'], 1, '0', 'C', true);
                            $this->SetFont('Arial', '', 15);
                            $this->setFillColor(192, 192, 192);
                            $this->Cell(85, 15, 'Silver', 1, '1', 'C', true);
                            $this->setFillColor(255, 255, 255);
                        } else {
                            continue;
                        }
                    }
                }
            } elseif ($adviser['deals'] >= 2 && $adviser['issued_api'] >= 2000) {
                $this->SetFont('Arial', '', 7);
                $this->SetXY(10, $this->GetPageHeight() - 37);
                $this->Write(0, 'CRITERIA: ');
                $this->Ln(3.3);
                $this->Write(0, 'To qualify, in a 15 day period an adviser should have: ');
                $this->Ln(3.3);
                $this->Write(0, 'a. 2 policies and over $2000 API for Silver;');
                $this->Ln(3.3);
                $this->Write(0, 'b. 3 policies and over $4500 API for Gold; ');
                $this->Ln(3.3);
                $this->Write(0, 'c. 4 policies and over $6000 API for Platinum; and ');
                $this->Ln(3.3);
                $this->Write(0, 'd. 5 policies and over $7500 APIfor Titanium ');

                $this->AddPage();

                if ($adviser['deals'] > 0) {
                    $total += $adviser['deals'];
                    $total_api += $adviser['issued_api'];
                    $this->SetFont('Calibri', 'B', 15);

                    if ('Others' !== $adviser['name']) {
                        if ($adviser['deals'] >= 5 && $adviser['issued_api'] >= 7500) {
                            if (empty($adviser['image'])) {
                                $image_path = $this->default_image;
                            } else {
                                $image_path = $this->uploads_folder . $adviser['image'];
                            }

                            if (file_exists($image_path)) {
                                $this->Cell(15, 15, $this->Image($image_path, $this->GetX(), $this->GetY(), 15, 15), 1, '0', 'L', false);
                            } else {
                                $this->Cell(15, 15, $this->Image($this->default_image, $this->GetX(), $this->GetY(), 15, 15), 1, '0', 'L', false);
                            }
                            $this->Cell(95, 15, $adviser['name'], 1, '0', 'C', true);
                            $this->SetFont('Calibri', '', 15);
                            $this->SetFillColor(102, 163, 194);
                            $this->Cell(85, 15, 'Titanium', 1, '1', 'C', true);
                            $this->setFillColor(255, 255, 255);
                        } elseif ($adviser['deals'] >= 4 && $adviser['issued_api'] >= 6000) {
                            if (empty($adviser['image'])) {
                                $image_path = $this->default_image;
                            } else {
                                $image_path = $this->uploads_folder . $adviser['image'];
                            }

                            if (file_exists($image_path)) {
                                $this->Cell(15, 15, $this->Image($image_path, $this->GetX(), $this->GetY(), 15, 15), 1, '0', 'L', false);
                            } else {
                                $this->Cell(15, 15, $this->Image($this->default_image, $this->GetX(), $this->GetY(), 15, 15), 1, '0', 'L', false);
                            }
                            $this->Cell(95, 15, $adviser['name'], 1, '0', 'C', true);
                            $this->SetFont('Calibri', '', 15);
                            $this->setFillColor(212, 91, 91);
                            $this->Cell(85, 15, 'Platinum', 1, '1', 'C', true);
                            $this->setFillColor(255, 255, 255);
                        } elseif ($adviser['deals'] >= 3 && $adviser['issued_api'] >= 4500) {
                            if (empty($adviser['image'])) {
                                $image_path = $this->default_image;
                            } else {
                                $image_path = $this->uploads_folder . $adviser['image'];
                            }

                            if (file_exists($image_path)) {
                                $this->Cell(15, 15, $this->Image($image_path, $this->GetX(), $this->GetY(), 15, 15), 1, '0', 'L', false);
                            } else {
                                $this->Cell(15, 15, $this->Image($this->default_image, $this->GetX(), $this->GetY(), 15, 15), 1, '0', 'L', false);
                            }
                            $this->Cell(95, 15, $adviser['name'], 1, '0', 'C', true);
                            $this->SetFont('Calibri', '', 15);
                            $this->setFillColor(255, 239, 148);
                            $this->Cell(85, 15, 'Gold', 1, '1', 'C', true);
                            $this->setFillColor(255, 255, 255);
                        } elseif ($adviser['deals'] >= 2 && $adviser['issued_api'] >= 2000) {
                            if (empty($adviser['image'])) {
                                $image_path = $this->default_image;
                            } else {
                                $image_path = $this->uploads_folder . $adviser['image'];
                            }

                            if (file_exists($image_path)) {
                                $this->Cell(15, 15, $this->Image($image_path, $this->GetX(), $this->GetY(), 15, 15), 1, '0', 'L', false);
                            } else {
                                $this->Cell(15, 15, $this->Image($this->default_image, $this->GetX(), $this->GetY(), 15, 15), 1, '0', 'L', false);
                            }
                            $this->Cell(95, 15, $adviser['name'], 1, '0', 'C', true);
                            $this->SetFont('Calibri', '', 15);
                            $this->setFillColor(192, 192, 192);
                            $this->Cell(85, 15, 'Silver', 1, '1', 'C', true);
                            $this->setFillColor(255, 255, 255);
                        } else {
                            continue;
                        }
                    }
                }
            }
        }
        $this->SetFont('Arial', '', 7);
        $this->SetXY(10, $this->GetPageHeight() - 37);
        $this->Write(0, 'CRITERIA: ');
        $this->Ln(3.3);
        $this->Write(0, 'To qualify, in a 15 day period an adviser should have: ');
        $this->Ln(3.3);
        $this->Write(0, 'a. 2 policies and over $2000 API for Silver;');
        $this->Ln(3.3);
        $this->Write(0, 'b. 3 policies and over $4500 API for Gold; ');
        $this->Ln(3.3);
        $this->Write(0, 'c. 4 policies and over $6000 API for Platinum; and ');
        $this->Ln(3.3);
        $this->Write(0, 'd. 5 policies and over $7500 APIfor Titanium ');
    }

    public function SetAlpha($alpha, $bm = 'Normal')
    {
        // set alpha for stroking (CA) and non-stroking (ca) operations
        $gs = $this->AddExtGState(['ca' => $alpha, 'CA' => $alpha, 'BM' => '/' . $bm]);
        $this->SetExtGState($gs);
    }

    public function AddExtGState($parms)
    {
        $n = count($this->extgstates) + 1;
        $this->extgstates[$n]['parms'] = $parms;

        return $n;
    }

    public function SetExtGState($gs)
    {
        $this->_out(sprintf('/GS%d gs', $gs));
    }

    public function _enddoc()
    {
        if (! empty($this->extgstates) && $this->PDFVersion < '1.4') {
            $this->PDFVersion = '1.4';
        }
        parent::_enddoc();
    }

    public function _putextgstates()
    {
        for ($i = 1; $i <= count($this->extgstates); $i++) {
            $this->_newobj();
            $this->extgstates[$i]['n'] = $this->n;
            $this->_put('<</Type /ExtGState');
            $parms = $this->extgstates[$i]['parms'];
            $this->_put(sprintf('/ca %.3F', $parms['ca']));
            $this->_put(sprintf('/CA %.3F', $parms['CA']));
            $this->_put('/BM ' . $parms['BM']);
            $this->_put('>>');
            $this->_put('endobj');
        }
    }

    public function _putresourcedict()
    {
        parent::_putresourcedict();
        $this->_put('/ExtGState <<');

        foreach ($this->extgstates as $k => $extgstate) {
            $this->_put('/GS' . $k . ' ' . $extgstate['n'] . ' 0 R');
        }
        $this->_put('>>');
    }

    public function _putresources()
    {
        $this->_putextgstates();
        parent::_putresources();
    }

    //End of pdf
    //START ADR page
    public function ADRBiMonthlyPage($bimonthly)
    {
        $highest = [];

        if (isset($bimonthly['highest'])) {
            $highest = $bimonthly['highest'];
            unset($bimonthly['highest']);
        }

        $highest_team = '';
        $highest_issued_api = '';
        $highest_deals = '';
        $highest_team_advisers = [];

        if (isset($highest) && sizeof($highest) >= 1) {
            $highest_team = ($highest['name'] ?? '');
            $highest_issued_api = ($highest['issued_api'] ?? '');
            $highest_deals = ($highest['deals'] ?? '');
            $highest_team_advisers = ($highest['advisers'] ?? '');
        }

        //page 2 BiMonthly API
        // begin comment - as per requested by Sir Sumit
        /* $this->AddPage();

        if(is_array($highest_team_advisers) && sizeof($highest_team_advisers) >= 1) {
            $this->Header1(15, 'ADR Team with Highest Total API from Policies Issued');

            $this->SetFillColor(255, 255, 255);

            // $this->Rect(11, 35, 100, 65, 'DF');
            // $this->SetTextColor(0, 0, 0);
            $this->SetY(38);
            // $this->SetX(14);

            $this->Cell(0, 3, '', 'L,T,R', '1', 'L', true);
            //Team Name
            $this->SetFont('Calibri', 'B', 25);
            $this->Cell(1, 8, '', 'L', '', '', true);
            $this->Cell(0, 8, 'Team: '.$highest_team, 'R', '1', 'C', true);
            $this->Cell(0, 5, '', 'L,R', '1', 'L', true);

            //Advisers list
            $this->SetFont('Calibri', 'B', 20);
            $this->Cell(10, 8, '', 'L', '', '', true);
            $this->Cell(90, 8, 'Advisers: ', '0', '0', 'L', true);
            $this->Cell(50, 8, 'Policies Issued: ', '0', '0', 'L', true);
            $this->SetFont('Calibri', '', 20);
            $this->Cell(0, 8, $highest_deals, 'R', '1', 'L', true);

            foreach ($highest_team_advisers as $k => $v) {
                if($k == 0) {
                    $this->Cell(12, 8, '', 'L', '', '', true);
                    $this->Cell(88, 8, $highest_team_advisers[$k].' (ADR)', '0', '0', 'L', true);
                    $this->SetFont('Calibri', 'B', 20);
                    $this->Cell(50, 8, 'Total API: ', '0', '0', 'L', true);
                    $this->SetFont('Calibri', '', 20);
                    $this->Cell(0, 8, number_format($highest_issued_api, 2), 'R', '1', 'L', true);
                } else {
                    $this->Cell(12, 8, '', 'L', '', '', true);
                    $this->Cell(0, 8, $highest_team_advisers[$k], 'R', '1', 'L', true);
                }
            }

            $this->Cell(0, 3, '', 'L,B,R', '1', 'L', true);
        } */
        // end comment

        $next_Y = $this->GetY();

        if (($next_Y + 15) >= 200) {
            $this->AddPage();
            $next_Y = $this->GetY();
        }

        //Tables
        $this->Header1(($next_Y + 15), 'ADR Team Bi-Monthly Issued Policies Table');

        $this->SetDrawColor(0, 0, 0);
        $this->SetX(8);
        $this->SetY(($next_Y + 38));
        $this->SetFont('Arial', 'B', 15);
        $this->SetDrawColor(0, 0, 0);
        $this->SetFillColor(255, 255, 255);

        $this->Cell(58, 10, 'ADR', 1, '0', 'C', true);
        $this->Cell(57, 10, 'Team Name', 1, '0', 'C', true);
        $this->Cell(40, 10, 'Policies Issued', 1, '0', 'C', true);
        $this->Cell(40, 10, 'Issued API', 1, '1', 'C', true);
        // $this->Cell(55, 10, 'Issued API', 1, '1', 'C', true);

        $this->SetFont('Arial', '', 15);

        $total = 0;
        $total_api = 0;

        foreach ($bimonthly as $team) {
            if ($team['deals'] > 0) {
                $total += $team['deals'];
                $total_api += $team['issued_api'];
                $this->Cell(58, 10, $team['adr'], 1, '0', 'L', true);
                $this->Cell(57, 10, $team['name'], 1, '0', 'L', true);
                $this->Cell(40, 10, $team['deals'], 1, '0', 'C', true);
                $this->Cell(40, 10, '$' . number_format($team['issued_api'], 2), 1, '1', 'C', true);
                // $this->Cell(55, 10, '$' . number_format($team['issued_api'], 2), 1, '1', 'C', true);
            }
        }

        $this->SetFont('Arial', 'B', 15);
        $this->Cell(115, 10, 'Total', 1, '0', 'L', true);
        $this->Cell(40, 10, number_format($total), 1, '0', 'C', true);
        $this->Cell(40, 10, '$' . number_format($total_api, 2), 1, '1', 'C', true);
        // $this->Cell(50, 10, $total, 1, '0', 'C', true);
        // $this->Cell(55, 10, '$' . number_format($total_api, 2), 1, '1', 'C', true);
    }

    public function ADRCumulativePage($cumulative)
    {
        $next_Y = $this->GetY();

        if (($next_Y + 15) >= 200) {
            $this->AddPage();
            $next_Y = $this->GetY();
        }

        //Tables
        $this->Header1(($next_Y + 15), 'ADR Team Cumulative Issued Policies Table');

        $this->SetDrawColor(0, 0, 0);
        $this->SetX(8);
        $this->SetY($next_Y + 38);
        $this->SetFont('Arial', 'B', 15);
        $this->SetDrawColor(0, 0, 0);
        $this->SetFillColor(255, 255, 255);
        $this->Cell(58, 10, 'ADR', 1, '0', 'C', true);
        $this->Cell(57, 10, 'Team Name', 1, '0', 'C', true);
        $this->Cell(40, 10, 'Policies Issued', 1, '0', 'C', true);
        $this->Cell(40, 10, 'Issued API', 1, '1', 'C', true);
        // $this->Cell(50, 10, 'No. of Policies Issued', 1, '0', 'C', true);
        // $this->Cell(55, 10, 'Issued API', 1, '1', 'C', true);

        $this->SetFont('Arial', '', 15);

        $total = 0;
        $total_api = 0;

        foreach ($cumulative as $team) {
            if ($team['deals'] > 0) {
                $total += $team['deals'];
                $total_api += $team['issued_api'];
                $this->Cell(58, 10, $team['adr'], 1, '0', 'L', true);
                $this->Cell(57, 10, $team['name'], 1, '0', 'L', true);
                $this->Cell(40, 10, $team['deals'], 1, '0', 'C', true);
                $this->Cell(40, 10, '$' . number_format($team['issued_api'], 2), 1, '1', 'C', true);
                // $this->Cell(55, 10, '$' . number_format($team['issued_api'], 2), 1, '1', 'C', true);
            }
        }

        $this->SetFont('Arial', 'B', 15);
        $this->Cell(115, 10, 'Total', 1, '0', 'L', true);
        $this->Cell(40, 10, number_format($total), 1, '0', 'C', true);
        $this->Cell(40, 10, '$' . number_format($total_api, 2), 1, '1', 'C', true);
        // $this->Cell(50, 10, $total, 1, '0', 'C', true);
        // $this->Cell(55, 10, '$' . number_format($total_api, 2), 1, '1', 'C', true);
    }

    public function ADRBiMonthlyKiwiSaversPage($bimonthlykiwisavers)
    {
        $highest = [];

        if (isset($bimonthlykiwisavers['highest'])) {
            $highest = $bimonthlykiwisavers['highest'];
            unset($bimonthlykiwisavers['highest']);
        }

        $highest_team = '';
        $highest_issued_api = '';
        $highest_deals = '';
        $highest_team_advisers = [];

        if (isset($highest) && sizeof($highest) >= 1) {
            $highest_team = ($highest['name'] ?? '');
            $highest_deals = ($highest['deals'] ?? '');
            $highest_team_advisers = ($highest['advisers'] ?? '');
        }

        //page 2 BiMonthly API
        // begin comment - Requested by Sir Wilfred
        /* $this->AddPage();

        if(is_array($highest_team_advisers) && sizeof($highest_team_advisers) >= 1) {
            $this->Header1(15, 'ADR Team with Highest Total Kiwisaver Enrolments');

            $this->SetFillColor(255, 255, 255);

            // $this->Rect(11, 35, 100, 65, 'DF');
            // $this->SetTextColor(0, 0, 0);
            $this->SetY(38);
            // $this->SetX(14);

            $this->Cell(0, 3, '', 'L,T,R', '1', 'L', true);
            //Team Name
            $this->SetFont('Calibri', 'B', 25);
            $this->Cell(1, 8, '', 'L', '', '', true);
            $this->Cell(0, 8, 'Team: '.$highest_team, 'R', '1', 'C', true);
            $this->Cell(0, 5, '', 'L,R', '1', 'L', true);

            //Advisers list
            $this->SetFont('Calibri', 'B', 20);
            $this->Cell(10, 8, '', 'L', '', '', true);
            $this->Cell(90, 8, 'Advisers: ', '0', '0', 'L', true);
            $this->Cell(50, 8, 'Enrolled: ', '0', '0', 'L', true);
            $this->SetFont('Calibri', '', 20);
            $this->Cell(0, 8, $highest_deals, 'R', '1', 'L', true);

            foreach ($highest_team_advisers as $k => $v) {
                if($k == 0) {
                    $this->Cell(12, 8, '', 'L', '', '', true);
                    $this->Cell(0, 8, $highest_team_advisers[$k].' (ADR)', 'R', '1', 'L', true);
                } else {
                    $this->Cell(12, 8, '', 'L', '', '', true);
                    $this->Cell(0, 8, $highest_team_advisers[$k], 'R', '1', 'L', true);
                }
            }

            $this->Cell(0, 3, '', 'L,B,R', '1', 'L', true);
        } */
        // end comment

        $next_Y = $this->GetY();

        if (($next_Y + 15) >= 200) {
            $this->AddPage();
            $next_Y = $this->GetY();
        }

        //Tables
        $this->Header1(($next_Y + 15), 'ADR Team Bi-Monthly Table for Kiwisaver Enrolments');

        $this->SetDrawColor(0, 0, 0);
        $this->SetX(8);
        $this->SetY(($next_Y + 38));
        $this->SetFont('Arial', 'B', 15);
        $this->SetDrawColor(0, 0, 0);
        $this->SetFillColor(255, 255, 255);

        $this->Cell(65, 10, 'ADR', 1, '0', 'C', true);
        $this->Cell(55, 10, 'Team Name', 1, '0', 'C', true);
        $this->Cell(75, 10, 'No. of  KiwiSavers Enrolled', 1, '1', 'C', true);

        $this->SetFont('Arial', '', 15);

        $total = 0;

        foreach ($bimonthlykiwisavers as $index => $bimonthlykiwisaver) {
            if ($bimonthlykiwisaver['deals'] > 0) {
                $total += $bimonthlykiwisaver['deals'];
                $this->Cell(65, 10, $bimonthlykiwisaver['adr'], 1, '0', 'L', true);
                $this->Cell(55, 10, $bimonthlykiwisaver['name'], 1, '0', 'L', true);
                $this->Cell(75, 10, $bimonthlykiwisaver['deals'], 1, '1', 'C', true);
            }
        }

        $this->SetFont('Arial', 'B', 15);
        $this->Cell(120, 10, 'Total', 1, '0', 'L', true);
        $this->Cell(75, 10, $total, 1, '1', 'C', true);
    }

    public function ADRCumulativeKiwiSaversPage($cumulativekiwisavers)
    {
        $next_Y = $this->GetY();

        if (($next_Y + 15) >= 200) {
            $this->AddPage();
            $next_Y = $this->GetY();
        }
        //Tables
        $this->Header1(($next_Y + 15), 'ADR Team Cumulative Table for Kiwisaver Enrolments');

        $this->SetDrawColor(0, 0, 0);
        $this->SetX(8);
        $this->SetY($next_Y + 38);
        $this->SetFont('Arial', 'B', 15);
        $this->SetDrawColor(0, 0, 0);
        $this->SetFillColor(255, 255, 255);

        $this->Cell(65, 10, 'ADR', 1, '0', 'C', true);
        $this->Cell(55, 10, 'Team Name', 1, '0', 'C', true);
        $this->Cell(75, 10, 'No. of  KiwiSavers Enrolled', 1, '1', 'C', true);

        $this->SetFont('Arial', '', 15);

        $total = 0;

        foreach ($cumulativekiwisavers as $index => $cumulativekiwisaver) {
            if ($cumulativekiwisaver['deals'] > 0) {
                $total += $cumulativekiwisaver['deals'];
                $this->Cell(65, 10, $cumulativekiwisaver['adr'], 1, '0', 'L', true);
                $this->Cell(55, 10, $cumulativekiwisaver['name'], 1, '0', 'L', true);
                $this->Cell(75, 10, $cumulativekiwisaver['deals'], 1, '1', 'C', true);
            }
        }

        $this->SetFont('Arial', 'B', 15);
        $this->Cell(120, 10, 'Total', 1, '0', 'L', true);
        $this->Cell(75, 10, $total, 1, '1', 'C', true);
    }

    //END ADR page

    //START SADR page
    //SADR page
    public function SADRBiMonthlyPage($bimonthly)
    {
        $highest = [];

        if (isset($bimonthly['highest'])) {
            $highest = $bimonthly['highest'];
            unset($bimonthly['highest']);
        }

        $highest_team = '';
        $highest_issued_api = '';
        $highest_deals = '';
        $highest_team_advisers = [];

        if (isset($highest) && sizeof($highest) >= 1) {
            $highest_team = ($highest['name'] ?? '');
            $highest_issued_api = ($highest['issued_api'] ?? '');
            $highest_deals = ($highest['deals'] ?? '');
            $highest_team_advisers = ($highest['advisers'] ?? '');
        }

        //page 2 BiMonthly API
        // begin comment - requested by Sir Leif
        /* $this->AddPage();
        if(is_array($highest_team_advisers) && sizeof($highest_team_advisers) >= 1) {
            $this->Header1(15, 'SADR Team with Highest Total API from Policies Issued');

            $this->SetFillColor(255, 255, 255);

            // $this->Rect(11, 35, 100, 65, 'DF');
            // $this->SetTextColor(0, 0, 0);
            $this->SetY(38);
            // $this->SetX(14);

            $this->Cell(0, 3, '', 'L,T,R', '1', 'L', true);
            //Team Name
            $this->SetFont('Calibri', 'B', 25);
            $this->Cell(1, 8, '', 'L', '', '', true);
            $this->Cell(0, 8, 'Team: '.$highest_team, 'R', '1', 'C', true);
            $this->Cell(0, 5, '', 'L,R', '1', 'L', true);

            //Advisers list
            $this->SetFont('Calibri', 'B', 20);
            $this->Cell(10, 8, '', 'L', '', '', true);
            $this->Cell(90, 8, 'Advisers: ', '0', '0', 'L', true);
            $this->Cell(50, 8, 'Policies Issued: ', '0', '0', 'L', true);
            $this->SetFont('Calibri', '', 20);
            $this->Cell(0, 8, $highest_deals, 'R', '1', 'L', true);

            foreach ($highest_team_advisers as $k => $v) {
                if($k == 0) {
                    $this->Cell(12, 8, '', 'L', '', '', true);
                    $this->Cell(88, 8, $highest_team_advisers[$k].' (SADR)', '0', '0', 'L', true);
                    $this->SetFont('Calibri', 'B', 20);
                    $this->Cell(50, 8, 'Total API: ', '0', '0', 'L', true);
                    $this->SetFont('Calibri', '', 20);
                    $this->Cell(0, 8, number_format($highest_issued_api, 2), 'R', '1', 'L', true);
                } else {
                    $this->Cell(12, 8, '', 'L', '', '', true);
                    $this->Cell(0, 8, $highest_team_advisers[$k], 'R', '1', 'L', true);
                }
            }

            $this->Cell(0, 3, '', 'L,B,R', '1', 'L', true);
        } */
        // end comment

        $next_Y = $this->GetY();

        if (($next_Y + 15) >= 200) {
            $this->AddPage();
            $next_Y = $this->GetY();
        }

        //Tables
        $this->Header1(($next_Y + 15), 'SADR Team Bi-Monthly Issued Policies Table');

        $this->SetDrawColor(0, 0, 0);
        $this->SetX(8);
        $this->SetY($next_Y + 38);
        $this->SetFont('Arial', 'B', 14);
        $this->SetDrawColor(0, 0, 0);
        $this->SetFillColor(255, 255, 255);
        $this->Cell(58, 10, 'SADR', 1, '0', 'C', true);
        $this->Cell(57, 10, 'Team Name', 1, '0', 'C', true);
        $this->Cell(40, 10, 'Policies Issued', 1, '0', 'C', true);
        $this->Cell(40, 10, 'Issued API', 1, '1', 'C', true);

        $this->SetFont('Arial', '', 15);

        $total = 0;
        $total_api = 0;

        foreach ($bimonthly as $team) {
            if ($team['deals'] > 0) {
                $total += $team['deals'];
                $total_api += $team['issued_api'];
                $this->Cell(58, 10, $team['sadr'], 1, '0', 'L', true);
                $this->Cell(57, 10, $team['name'], 1, '0', 'L', true);
                $this->Cell(40, 10, number_format($team['deals']), 1, '0', 'C', true);
                $this->Cell(40, 10, '$' . number_format($team['issued_api'], 2), 1, '1', 'C', true);
            }
        }

        $this->SetFont('Arial', 'B', 15);
        $this->Cell(115, 10, 'Total', 1, '0', 'L', true);
        $this->Cell(40, 10, number_format($total), 1, '0', 'C', true);
        $this->Cell(40, 10, '$' . number_format($total_api, 2), 1, '1', 'C', true);
    }

    public function SADRCumulativePage($cumulative)
    {
        $next_Y = $this->GetY();

        if (($next_Y + 15) >= 200) {
            $this->AddPage();
            $next_Y = $this->GetY();
        }
        //Tables
        $this->Header1(($next_Y + 15), 'SADR Team Cumulative Issued Policies Table');

        $this->SetDrawColor(0, 0, 0);
        $this->SetX(8);
        $this->SetY($next_Y + 38);
        $this->SetFont('Arial', 'B', 14);
        $this->SetDrawColor(0, 0, 0);
        $this->SetFillColor(255, 255, 255);
        $this->Cell(58, 10, 'SADR', 1, '0', 'C', true);
        $this->Cell(57, 10, 'Team Name', 1, '0', 'C', true);
        $this->Cell(40, 10, 'Policies Issued', 1, '0', 'C', true);
        $this->Cell(40, 10, 'Issued API', 1, '1', 'C', true);

        $this->SetFont('Arial', '', 15);

        $total = 0;
        $total_api = 0;

        foreach ($cumulative as $team) {
            if ($team['deals'] > 0) {
                $total += $team['deals'];
                $total_api += $team['issued_api'];
                $this->Cell(58, 10, $team['sadr'], 1, '0', 'L', true);
                $this->Cell(57, 10, $team['name'], 1, '0', 'L', true);
                $this->Cell(40, 10, number_format($team['deals']), 1, '0', 'C', true);
                $this->Cell(40, 10, '$' . number_format($team['issued_api'], 2), 1, '1', 'C', true);
            }
        }

        $this->SetFont('Arial', 'B', 15);
        $this->Cell(115, 10, 'Total', 1, '0', 'L', true);
        $this->Cell(40, 10, number_format($total), 1, '0', 'C', true);
        $this->Cell(40, 10, '$' . number_format($total_api, 2), 1, '1', 'C', true);
    }

    public function SADRBiMonthlyKiwiSaversPage($bimonthlykiwisavers)
    {
        $highest = [];

        if (isset($bimonthlykiwisavers['highest'])) {
            $highest = $bimonthlykiwisavers['highest'];
            unset($bimonthlykiwisavers['highest']);
        }

        $highest_team = '';
        $highest_issued_api = '';
        $highest_deals = '';
        $highest_team_advisers = [];

        if (isset($highest) && sizeof($highest) >= 1) {
            $highest_team = ($highest['name'] ?? '');
            $highest_deals = ($highest['deals'] ?? '');
            $highest_team_advisers = ($highest['advisers'] ?? '');
        }

        //page 2 BiMonthly API
        // begin comment - requested by Sir Wilfred
        /* $this->AddPage();

        if(is_array($highest_team_advisers) && sizeof($highest_team_advisers) >= 1) {
            $this->Header1(15, 'SADR Team with Highest Total Kiwisaver Enrolments');

            $this->SetFillColor(255, 255, 255);

            // $this->Rect(11, 35, 100, 65, 'DF');
            // $this->SetTextColor(0, 0, 0);
            $this->SetY(38);
            // $this->SetX(14);

            $this->Cell(0, 3, '', 'L,T,R', '1', 'L', true);
            //Team Name
            $this->SetFont('Calibri', 'B', 25);
            $this->Cell(1, 8, '', 'L', '', '', true);
            $this->Cell(0, 8, 'Team: '.$highest_team, 'R', '1', 'C', true);
            $this->Cell(0, 5, '', 'L,R', '1', 'L', true);

            //Advisers list
            $this->SetFont('Calibri', 'B', 20);
            $this->Cell(10, 8, '', 'L', '', '', true);
            $this->Cell(90, 8, 'Advisers: ', '0', '0', 'L', true);
            $this->Cell(50, 8, 'Enrolled: ', '0', '0', 'L', true);
            $this->SetFont('Calibri', '', 20);
            $this->Cell(0, 8, $highest_deals, 'R', '1', 'L', true);

            foreach ($highest_team_advisers as $k => $v) {
                if($k == 0) {
                    $this->Cell(12, 8, '', 'L', '', '', true);
                    $this->Cell(0, 8, $highest_team_advisers[$k].' (SADR)', 'R', '1', 'L', true);
                } else {
                    $this->Cell(12, 8, '', 'L', '', '', true);
                    $this->Cell(0, 8, $highest_team_advisers[$k], 'R', '1', 'L', true);
                }
            }

            $this->Cell(0, 3, '', 'L,B,R', '1', 'L', true);
        } */
        // end comment

        $next_Y = $this->GetY();

        if (($next_Y + 15) >= 200) {
            $this->AddPage();
            $next_Y = $this->GetY();
        }

        //Tables
        $this->Header1(($next_Y + 15), 'SADR Team Bi-Monthly Table for Kiwisaver Enrolments');

        $this->SetDrawColor(0, 0, 0);
        $this->SetX(8);
        $this->SetY(($next_Y + 38));
        $this->SetFont('Arial', 'B', 14);
        $this->SetDrawColor(0, 0, 0);
        $this->SetFillColor(255, 255, 255);

        $this->Cell(65, 10, 'SADR', 1, '0', 'C', true);
        $this->Cell(65, 10, 'Team Name', 1, '0', 'C', true);
        $this->Cell(65, 10, 'No. of  KiwiSavers Enrolled', 1, '1', 'C', true);

        $this->SetFont('Arial', '', 15);

        $total = 0;

        foreach ($bimonthlykiwisavers as $index => $bimonthlykiwisaver) {
            if ($bimonthlykiwisaver['deals'] > 0) {
                $total += $bimonthlykiwisaver['deals'];
                $this->Cell(65, 10, $bimonthlykiwisaver['sadr'], 1, '0', 'L', true);
                $this->Cell(65, 10, $bimonthlykiwisaver['name'], 1, '0', 'L', true);
                $this->Cell(65, 10, $bimonthlykiwisaver['deals'], 1, '1', 'C', true);
            }
        }

        $this->SetFont('Arial', 'B', 15);
        $this->Cell(130, 10, 'Total', 1, '0', 'L', true);
        $this->Cell(65, 10, $total, 1, '1', 'C', true);
    }

    public function SADRCumulativeKiwiSaversPage($cumulativekiwisavers)
    {
        $next_Y = $this->GetY();

        if (($next_Y + 15) >= 200) {
            $this->AddPage();
            $next_Y = $this->GetY();
        }
        //Tables
        $this->Header1(($next_Y + 15), 'SADR Team Cumulative Table for Kiwisaver Enrolments');

        $this->SetDrawColor(0, 0, 0);
        $this->SetX(8);
        $this->SetY($next_Y + 38);
        $this->SetFont('Arial', 'B', 14);
        $this->SetDrawColor(0, 0, 0);
        $this->SetFillColor(255, 255, 255);

        $this->Cell(65, 10, 'SADR', 1, '0', 'C', true);
        $this->Cell(65, 10, 'Team Name', 1, '0', 'C', true);
        $this->Cell(65, 10, 'No. of  KiwiSavers Enrolled', 1, '1', 'C', true);

        $this->SetFont('Arial', '', 15);

        $total = 0;

        foreach ($cumulativekiwisavers as $index => $cumulativekiwisaver) {
            if ($cumulativekiwisaver['deals'] > 0) {
                $total += $cumulativekiwisaver['deals'];
                $this->Cell(65, 10, $cumulativekiwisaver['sadr'], 1, '0', 'L', true);
                $this->Cell(65, 10, $cumulativekiwisaver['name'], 1, '0', 'L', true);
                $this->Cell(65, 10, $cumulativekiwisaver['deals'], 1, '1', 'C', true);
            }
        }

        $this->SetFont('Arial', 'B', 15);
        $this->Cell(130, 10, 'Total', 1, '0', 'L', true);
        $this->Cell(65, 10, $total, 1, '1', 'C', true);
    }

    //END SADR page
}

function CreateMagazinePDF($magazine_data, $preview = true, $randomize_name = true)
{
    $magazineController = new MagazineController();
    $generalController = new General();
    $date_helper = new INDET_DATES_HELPER();
    $alphanumeric_helper = new INDET_ALPHANUMERIC_HELPER();

    //Fetch user
    $userController = new UserController();

    if (! isset($_SESSION['myuserid'])) {
        $user = $userController->getUserWithData(27);
    } else {
        $user = $userController->getUserWithData($_SESSION['myuserid']);
    }

    $indet_dates_helper = new INDET_DATES_HELPER();

    $created_by = $user['full_name'];

    $pdf_data = new stdClass();

    //set colors for gradients (r,g,b) or (grey 0-255)
    $white = [255, 255, 255];
    $gray = [220, 220, 220];

    $pdf = new PDF('P', 'mm', 'Letter');
    $pdf->reference_no = $magazine_data->issue_number;
    $pdf->table_of_contents = $magazine_data->pages;
    $x = $pdf->GetX();
    $y = $pdf->GetY();

    //page 1
    $pdf->AddPage();

    //set the coordinates x1,y1,x2,y2 of the gradient (see linear_gradient_coords.jpg)
    $coords = [0, 0.69, 1, 0];

    //paint a linear gradient
    $pdf->LinearGradient(81, 11, 120, 223, $white, $gray, $coords);

    $pdf->Image('images/EliteInsure Horizonal Logo.png', 10, 15, 185);

    //Title
    $pdf->SetFillColor(224, 224, 224);
    $pdf->AddFont('Calibri', 'B', 'calibrib.php');
    $pdf->AddFont('Calibri', '', 'calibri.php');
    $pdf->SetFont('Calibri', 'B', 42);
    $pdf->SetTextColor(0, 102, 153);
    $pdf->Text(65, 55, 'Bi Monthly Magazine');
    $pdf->AddFont('Calibri', '', 'calibrib.php');
    $pdf->SetFont('Calibri', '', 16);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetY(58);
    $pdf->SetX(85);
    $pdf->Cell(121, 8, $magazine_data->issue_number, 0, 1, 'L');
    $pdf->SetX(85);
    $pdf->Cell(121, 8, ($magazine_data->issue_number_line_2 ?? ''), 0, 1, 'L');

    $blue = [50, 143, 168];
    $darkblue = [50, 91, 168];
    $pdf->SetY(78);
    $pdf->SetX(15);

    $sorted_advisers = [];
    $t_array = [];
    $p_array = [];
    $g_array = [];
    $s_array = [];

    if (isset($magazine_data->all_winner_score) && (is_array($magazine_data->all_winner_score) || is_object($magazine_data->all_winner_score))) {
        foreach ($magazine_data->all_winner_score as $index => $adviser) {
            if ('Titanium' == $adviser['score']) {
                $adviser[`platinum`] = 0;
                $adviser[`gold`] = 0;
                $adviser[`silver`] = 0;

                array_push($t_array, $adviser);
                $titanium = array_column($t_array, 'titanium');
            } else {
                if ('Platinum' == $adviser['score']) {
                    $adviser[`titanium`] = 0;
                    $adviser[`gold`] = 0;
                    $adviser[`silver`] = 0;

                    array_push($p_array, $adviser);
                    $platinum = array_column($p_array, 'platinum');
                } else {
                    if ('Gold' == $adviser['score']) {
                        $adviser[`titanium`] = 0;
                        $adviser[`platinum`] = 0;
                        $adviser[`silver`] = 0;

                        array_push($g_array, $adviser);
                        $gold = array_column($g_array, 'gold');
                    } else {
                        if ('Silver' == $adviser['score']) {
                            array_push($s_array, $adviser);
                            $silver = array_column($s_array, 'silver');
                        }
                    }
                }
            }
        }
    }

    if ($t_array) {
        array_multisort($titanium, SORT_DESC, $t_array);

        foreach ($t_array as $data) {
            array_push($sorted_advisers, $data);
        }
    }

    if ($p_array) {
        array_multisort($platinum, SORT_DESC, $p_array);

        foreach ($p_array as $data) {
            array_push($sorted_advisers, $data);
        }
    }

    if ($g_array) {
        array_multisort($gold, SORT_DESC, $g_array);

        foreach ($g_array as $data) {
            array_push($sorted_advisers, $data);
        }
    }

    if ($s_array) {
        array_multisort($silver, SORT_DESC, $s_array);

        foreach ($s_array as $data) {
            array_push($sorted_advisers, $data);
        }
    }

    if (isset($magazine_data->all_winner_score) && (is_array($magazine_data->all_winner_score) && count($magazine_data->all_winner_score) > 0)) {
        if ('Others' != $magazine_data->all_winner_score[0]['name']) {
            $pdf->SetDrawColor(50, 143, 168);
            $pdf->SetLineWidth(1);
            $pdf->RoundedRect($pdf->GetX() - 3, $pdf->GetY() - 2, 80, 52, 5, '1234', 'DF');
            $pdf->SetFont('Arial', 'I', 15);
            $pdf->Cell(8, 3, $pdf->MultiCell(78, 8, '"Everything good happens to a stringwriter."', 0, 'L', false, 15), '0', '1', 'L');
            $pdf->Cell(5, 3, '', '0', '1', 'L');
            $pdf->Cell(5, 3, '', '0', '0', 'L');
            $pdf->SetFont('Arial', 'B', 20);
            $pdf->Cell(80, 3, 'Top String Writer: ', '0', '1', 'L');
            $pdf->Cell(5, 5, '', '0', '1', 'L');
            $pdf->Cell(5, 5, '', '0', '0', 'L');
            $pdf->SetFont('Arial', 'B', 20);
            $pdf->SetTextColor(0, 102, 153);
            $pdf->CellFitScale(75, 10, strtoupper($sorted_advisers[0]['name']), 'B', '1', 'L');
            $pdf->SetTextColor(0, 0, 0);
            $pdf->Cell(5, 3, '', '0', '1', 'L');
            $pdf->Cell(5, 3, '', '0', '', 'L');
            $pdf->SetFont('Arial', 'I', 12);

            foreach ($magazine_data->pages as $winner_string) {
                if ('Winner Strings' == $winner_string['title']) {
                    $pdf->Cell(80, 3, $winner_string['page'], '0', '1', 'L');
                }
            }
        }
    }

    if (strlen($magazine_data->quote) > 100) {
        $pdf->SetFont('Arial', 'BI', 10);
        $pdf->SetY(80);
        $pdf->Cell(90, 3, '', '0', 'L');
        $pdf->Cell(100, 3, $pdf->MultiCell(90, 5, '"' . $magazine_data->quote . '"', 0, 'C', false, 10), '0', 'L');
    } else {
        $pdf->SetY(80);
        $pdf->Cell(90, 3, '', '0', 'L');
        $pdf->Cell(100, 3, $pdf->MultiCell(90, 5, $magazine_data->quote, 0, 'C', false, 10), '0', 'L');
    }

    $pdf->SetFont('Calibri', 'U', 16);
    $pdf->Text(101, 123, 'Contents');

    $pdf->SetY(128);
    $pdf->SetX(10);
    $pdf->SetFont('Calibri', '', 16);

    foreach ($magazine_data->pages as $content) {
        $pdf->Cell(90, 8, '', '', '0');
        $pdf->Cell(77, 8, $content['title'], '', '0', 'L');
        $pdf->Cell(115, 8, $content['page_start'], '', '1', 'L');
    }

    //Featured Adviser
    $featured_adviser = (is_array($magazine_data->bi_monthly_advisers) && count($magazine_data->bi_monthly_advisers) > 0) ?
        ($magazine_data->bi_monthly_advisers[0] ?? null) :
        ($magazine_data->cumulative_advisers[0] ?? null);
    $featured_title = (is_array($magazine_data->bi_monthly_advisers) && count($magazine_data->bi_monthly_advisers) > 0) ? 'Top Adviser of the Period ' . date('j', strtotime($magazine_data->bimonthRange->from)) . '-' . date('j F Y', strtotime($magazine_data->bimonthRange->to)) : 'Top Adviser of the Period ' . date('j M', strtotime($magazine_data->cumulativeRange->from)) . '-' . date('j M Y', strtotime($magazine_data->cumulativeRange->to));

    $pdf->SetX(0);
    $pdf->SetX(190);

    //Production
    $image_path = '';

    if (empty($featured_adviser['image'])) {
        $image_path = $pdf->default_image;
    } else {
        $image_path = $pdf->uploads_folder . $featured_adviser['image'];
    }

    if (file_exists($image_path)) {
        $pdf->Image($image_path, 15, 130, 75);
    } else {
        $pdf->Image($pdf->default_image, 15, 130, 75);
    }

    $pdf->SetDrawColor(200, 200, 200);
    $pdf->Rect(15, 205, 185.5, 33, 'FD');
    $pdf->SetFont('Arial', 'BU', 18);
    $pdf->SetTextColor(100, 100, 100);
    $pdf->Text(18, 213, $featured_title);
    $pdf->SetTextColor(0, 77, 115);
    $pdf->SetFont('Arial', 'B', 27);
    $pdf->Text(18, 225, ($featured_adviser['name'] ?? null));
    $pdf->SetTextColor(115, 146, 161);
    $pdf->SetFont('Arial', 'B', 22);
    $pdf->Text(18, 235, (isset($featured_adviser['issued_api']) ? ('$' . number_format($featured_adviser['issued_api'], 2) . ' Issued API') : null));
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetY(228);
    $pdf->SetX(0);
    $pdf->Cell(198, 10, $magazine_data->pages[0]['page'], 0, 0, 'R');

    // Disclaimer
    if ($magazine_data->disclaimer) {
        $pdf->SetFont('Arial', '', 7);
        $pdf->SetXY(14, $pdf->GetPageHeight() - 37);
        $pdf->MultiCell(187, 3, 'DISCLAIMER: ' . $magazine_data->disclaimer);
    }

    if (! empty($magazine_data->message)) {
        $pdf->MessagePage($magazine_data->message);
    }

    if (is_array($magazine_data->bi_monthly_advisers) && count($magazine_data->bi_monthly_advisers) > 0) {
        if ('Others' != $magazine_data->bi_monthly_advisers[0]['name']) {
            $pdf->BiMonthlyPage($magazine_data->bi_monthly_advisers);
        }
    }

    if (is_array($magazine_data->cumulative_advisers) && count($magazine_data->cumulative_advisers) > 0) {
        if ('Others' != $magazine_data->cumulative_advisers[0]['name']) {
            $pdf->CumulativePage($magazine_data->cumulative_advisers);
        }
    }

    if (is_array($magazine_data->bi_monthly_advisers_kiwisavers) && count($magazine_data->bi_monthly_advisers_kiwisavers) > 0) {
        if ('Others' != $magazine_data->bi_monthly_advisers_kiwisavers[0]['name']) {
            $pdf->BiMonthlyKiwiSaversPage($magazine_data->bi_monthly_advisers_kiwisavers);
        }
    }

    if (is_array($magazine_data->cumulative_advisers_kiwisavers) && count($magazine_data->cumulative_advisers_kiwisavers) > 0) {
        if ('Others' != $magazine_data->cumulative_advisers_kiwisavers[0]['name']) {
            $pdf->CumulativeKiwiSaversPage($magazine_data->cumulative_advisers_kiwisavers);
        }
    }

    if (isset($magazine_data->winner_score) && (is_array($magazine_data->winner_score) && count($magazine_data->winner_score) > 0)) {
        if ('Others' != $magazine_data->winner_score[0]['name']) {
            $pdf->WinnerScore($magazine_data->winner_score);
        }
    }

    if (isset($magazine_data->all_winner_score) && (is_array($magazine_data->all_winner_score) && count($magazine_data->all_winner_score) > 0)) {
        if ('Others' != $magazine_data->all_winner_score[0]['name']) {
            $pdf->Strings($magazine_data->all_winner_score, $magazine_data->bimonthRange);
        }
    }

    if (isset($magazine_data->rba_cumulative_advisers) && (is_array($magazine_data->rba_cumulative_advisers) && count($magazine_data->rba_cumulative_advisers) > 0)) {
        if ('Others' != $magazine_data->rba_cumulative_advisers[0]['name']) {
            $pdf->CumulativeRBAPage($magazine_data->rba_cumulative_advisers, $magazine_data->overallCumulativeRBA);
        }
    }

    if (is_array($magazine_data->records_to_beat) && count($magazine_data->records_to_beat) > 0) {
        $pdf->RecordsPage($magazine_data->records_to_beat);
    }

    //start of ADR and SADR tables
    if (isset($magazine_data->adr_bi_monthly_advisers) && is_array($magazine_data->adr_bi_monthly_advisers) && count($magazine_data->adr_bi_monthly_advisers) > 0) {
        $pdf->ADRBiMonthlyPage($magazine_data->adr_bi_monthly_advisers);

        if (isset($magazine_data->adr_cumulative_advisers) && is_array($magazine_data->adr_cumulative_advisers) && count($magazine_data->adr_cumulative_advisers) > 0) {
            $pdf->ADRCumulativePage($magazine_data->adr_cumulative_advisers);
        }
    } else {
        if (isset($magazine_data->adr_cumulative_advisers) && is_array($magazine_data->adr_cumulative_advisers) && count($magazine_data->adr_cumulative_advisers) > 0) {
            $pdf->AddPage();
            $pdf->ADRCumulativePage($magazine_data->adr_cumulative_advisers);
        }
    }

    if (isset($magazine_data->adr_bi_monthly_advisers_kiwisavers) && is_array($magazine_data->adr_bi_monthly_advisers_kiwisavers) && count($magazine_data->adr_bi_monthly_advisers_kiwisavers) > 0) {
        $pdf->ADRBiMonthlyKiwiSaversPage($magazine_data->adr_bi_monthly_advisers_kiwisavers);

        if (isset($magazine_data->adr_cumulative_advisers_kiwisavers) && is_array($magazine_data->adr_cumulative_advisers_kiwisavers) && count($magazine_data->adr_cumulative_advisers_kiwisavers) > 0) {
            $pdf->ADRCumulativeKiwiSaversPage($magazine_data->adr_cumulative_advisers_kiwisavers);
        }
    } else {
        if (isset($magazine_data->adr_cumulative_advisers_kiwisavers) && is_array($magazine_data->adr_cumulative_advisers_kiwisavers) && count($magazine_data->adr_cumulative_advisers_kiwisavers) > 0) {
            $pdf->AddPage();
            $pdf->ADRCumulativeKiwiSaversPage($magazine_data->adr_cumulative_advisers_kiwisavers);
        }
    }

    //end of ADR
    //start of SADR
    if (isset($magazine_data->sadr_bi_monthly_advisers) && is_array($magazine_data->sadr_bi_monthly_advisers) && count($magazine_data->sadr_bi_monthly_advisers) > 0) {
        $pdf->SADRBiMonthlyPage($magazine_data->sadr_bi_monthly_advisers);

        if (isset($magazine_data->sadr_cumulative_advisers) && is_array($magazine_data->sadr_cumulative_advisers) && count($magazine_data->sadr_cumulative_advisers) > 0) {
            $pdf->SADRCumulativePage($magazine_data->sadr_cumulative_advisers);
        }
    } else {
        if (isset($magazine_data->sadr_cumulative_advisers) && is_array($magazine_data->sadr_cumulative_advisers) && count($magazine_data->sadr_cumulative_advisers) > 0) {
            $pdf->AddPage();
            $pdf->SADRCumulativePage($magazine_data->sadr_cumulative_advisers);
        }
    }

    if (isset($magazine_data->sadr_bi_monthly_advisers_kiwisavers) && is_array($magazine_data->sadr_bi_monthly_advisers_kiwisavers) && count($magazine_data->sadr_bi_monthly_advisers_kiwisavers) > 0) {
        $pdf->SADRBiMonthlyKiwiSaversPage($magazine_data->sadr_bi_monthly_advisers_kiwisavers);

        if (isset($magazine_data->sadr_cumulative_advisers_kiwisavers) && is_array($magazine_data->sadr_cumulative_advisers_kiwisavers) && count($magazine_data->sadr_cumulative_advisers_kiwisavers) > 0) {
            $pdf->SADRCumulativeKiwiSaversPage($magazine_data->sadr_cumulative_advisers_kiwisavers);
        }
    } else {
        if (isset($magazine_data->sadr_cumulative_advisers_kiwisavers) && is_array($magazine_data->sadr_cumulative_advisers_kiwisavers) && count($magazine_data->sadr_cumulative_advisers_kiwisavers) > 0) {
            $pdf->AddPage();
            $pdf->SADRCumulativeKiwiSaversPage($magazine_data->sadr_cumulative_advisers_kiwisavers);
        }
    }
    //end of ADR and SADR tables

    //UNCOMMENT IF WE NEED BDM DATA

    if (isset($magazine_data->bi_monthly_bdms) && (is_array($magazine_data->bi_monthly_bdms) && count($magazine_data->bi_monthly_bdms) > 0)) {
        if ('Others' != $magazine_data->bi_monthly_bdms[0]['name']) {
            $pdf->BDMBiMonthlyPage($magazine_data->bi_monthly_bdms);
        }
    }

    if (is_array($magazine_data->bdm_performances) && count($magazine_data->bdm_performances) > 0) {
        if ('Others' != $magazine_data->bdm_performances[0]['name']) {
            $quarterTitle = $magazine_data->quarterTitle ?? null;
            $pdf->BDMCumulativePage($magazine_data->bdm_performances, $quarterTitle);
        }
    }

    foreach ($magazine_data->new_faces as $new_faces_array) {
        $pdf->NewFacesPage($new_faces_array);
    }

    foreach ($magazine_data->upcoming_birthdays as $birthdays_array) {
        $pdf->BirthdaysPage($birthdays_array);
    }

    foreach ($magazine_data->upcoming_work_anniversaries as $work_anniversaries_array) {
        $pdf->WorkAnniversariesPage($work_anniversaries_array);
    }

    if (! empty($magazine_data->announcement)) {
        $pdf->AnnouncementsPage($magazine_data->announcement);
    }

    if (! empty($magazine_data->photos)) {
        $pdf->PhotosPage($magazine_data->photos);
    }

    $path = '';

    if ($preview) {
        $pdf->Output('I', 'EliteInsure Magazine ' . $magazine_data->issue_number . ' ' . ($magazine_data->issue_number_line_2 ?? '') . '.pdf');
    } else {
        $filename = (! $randomize_name) ? 'EliteInsure Magazine ' . $magazine_data->issue_number . ' ' . $magazine_data->issue_number_line_2 . '.pdf' : 'EliteInsure Magazine ' . md5(uniqid()) . '.pdf';
        $path = 'files/' . $filename;
        $pdf->Output($path, 'F');

        $data = [
            'data' => $magazine_data,
            'link' => $path,
        ];

        return json_encode($data);
    }
}

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
        $pdf->Cell(200, $linespace, '', 0, 1, 'C', 'true');
    }

/**
 * @param int $number
 *
 * @return string
 */
function numberToRomanRepresentation($number)
{
    $map = ['M' => 1000, 'CM' => 900, 'D' => 500, 'CD' => 400, 'C' => 100, 'XC' => 90, 'L' => 50, 'XL' => 40, 'X' => 10, 'IX' => 9, 'V' => 5, 'IV' => 4, 'I' => 1];
    $returnValue = '';
    while ($number > 0) {
        foreach ($map as $roman => $int) {
            if ($number >= $int) {
                $number -= $int;
                $returnValue .= $roman;

                break;
            }
        }
    }

    return $returnValue;
}

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
