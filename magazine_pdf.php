<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

date_default_timezone_set('Pacific/Auckland');
ob_start();
require('fpdf/mc_table.php');

require('database.php');

require_once 'libs/indet_dates_helper.php';
require_once 'libs/indet_alphanumeric_helper.php';
require_once 'libs/api/classes/general.class.php';
require_once 'libs/api/controllers/Magazine.controller.php';
require_once 'libs/api/controllers/User.controller.php';

class PDF extends PDF_MC_Table
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
        $this->SetFont('Calibri', 'B', 15);
        $this->SetDrawColor(0, 0, 0);
        $this->SetFillColor(255, 255, 255);
        $this->Cell(90, 10, 'Name Of Adviser', 1, '0', 'C', true);
        $this->Cell(50, 10, 'No. of Policies Issued', 1, '0', 'C', true);
        $this->Cell(55, 10, 'Issued API', 1, '1', 'C', true);

        $this->SetFont('Arial', '', 15);

        $total = 0;
        $total_api = 0;

        foreach ($advisers as $adviser) {
            if ($adviser['deals'] > 0) {
                $total += $adviser['deals'];
                $total_api += $adviser['issued_api'];
                $this->Cell(90, 10, $adviser['name'], 1, '0', 'L', true);
                $this->Cell(50, 10, $adviser['deals'], 1, '0', 'C', true);
                $this->Cell(55, 10, '$' . number_format($adviser['issued_api'], 2), 1, '1', 'C', true);
            }
        }

        $this->SetFont('Arial', 'B', 15);
        $this->Cell(90, 10, 'Total', 1, '0', 'L', true);
        $this->Cell(50, 10, $total, 1, '0', 'C', true);
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
        $this->SetFont('Calibri', 'B', 15);
        $this->SetDrawColor(0, 0, 0);
        $this->SetFillColor(255, 255, 255);

        $this->Cell(90, 10, 'Name Of Adviser', 1, '0', 'C', true);
        $this->Cell(50, 10, 'No. of Policies Issued', 1, '0', 'C', true);
        $this->Cell(55, 10, 'Issued API', 1, '1', 'C', true);

        $this->SetFont('Arial', '', 15);
        $total = 0;
        $total_api = 0;

        foreach ($advisers as $adviser) {
            if ($adviser['deals'] > 0) {
                $total += $adviser['deals'];
                $total_api += $adviser['issued_api'];
                $this->Cell(90, 10, $adviser['name'], 1, '0', 'L', true);
                $this->Cell(50, 10, $adviser['deals'], 1, '0', 'C', true);
                $this->Cell(55, 10, '$' . number_format($adviser['issued_api'], 2), 1, '1', 'C', true);
            }
        }

        $this->SetFont('Arial', 'B', 15);
        $this->Cell(90, 10, 'Total', 1, '0', 'L', true);
        $this->Cell(50, 10, $total, 1, '0', 'C', true);
        $this->Cell(55, 10, '$' . number_format($total_api, 2), 1, '1', 'C', true);
    }

    public function CumulativeRBAPage($advisers)
    {
        //page 2 BiMonthly API
        $this->AddPage();

        $this->Header1(15, 'Adviser with the Lowest Cumulative Percentage of Replacement Business', 17);

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

        //Total API
        $this->SetFont('Calibri', 'B', 20);
        $this->Cell(92, 8, '', '', '0', 'L');
        $this->Cell(50, 8, 'RBA:', '', '1', 'L');
        $this->SetFont('Calibri', '', 20);
        $this->Cell(100, 8, '', '', '0', 'L');
        $this->Cell(50, 8, number_format($advisers[0]['percent_rba'], 2) . '%', '', '1', 'L');

        //Tables
        $this->Header1(125, 'Cumulative Table for Percentage of Replacement Business', 20);

        $this->SetDrawColor(0, 0, 0);
        $this->SetX(8);
        $this->SetY(140);
        $this->SetFont('Calibri', 'B', 15);
        $this->SetDrawColor(0, 0, 0);
        $this->SetFillColor(255, 255, 255);

        $this->Cell(85, 10, 'Name Of Adviser', 1, '0', 'C', true);
        $this->Cell(50, 10, 'No. of Policies Issued', 1, '0', 'C', true);
        $this->Cell(60, 10, '% Replacement Business', 1, '1', 'C', true);

        $this->SetFont('Arial', '', 15);

        $total = 0;
        $total_api = 0;

        foreach ($advisers as $adviser) {
            if ($adviser['deals'] > 0) {
                $total += $adviser['deals'];
                $total_api += $adviser['issued_api'];
                $this->Cell(85, 10, $adviser['name'], 1, '0', 'L', true);
                $this->Cell(50, 10, $adviser['deals'], 1, '0', 'C', true);
                $this->Cell(60, 10, number_format($adviser['percent_rba'], 2) . '%', 1, '1', 'C', true);
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
        $this->SetFont('Calibri', 'B', 15);
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
        $this->SetFont('Calibri', 'B', 15);
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
        $this->SetFont('Calibri', 'B', 15);
        $this->SetDrawColor(0, 0, 0);
        $this->SetFillColor(255, 255, 255);
        $this->Cell(80, 10, 'Name Of BDM', 1, '0', 'C', true);
        $this->Cell(50, 10, 'No. of Policies Issued', 1, '0', 'C', true);
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

                $this->Cell(65, 15, $bdm['name'], 1, '0', 'L', true);
                $this->Cell(50, 15, $bdm['deals'], 1, '0', 'C', true);
                $this->Cell(60, 15, '$' . number_format($bdm['issued_api'], 2), 1, '1', 'C', true);
            }
        }

        $this->SetFont('Arial', 'B', 15);
        $this->Cell(80, 10, 'Total', 1, '0', 'L', true);
        $this->Cell(110, 10, '$' . number_format($total_api, 2), 1, '1', 'C', true);
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
        $this->SetFont('Calibri', 'B', 15);
        $this->SetDrawColor(0, 0, 0);
        $this->SetFillColor(255, 255, 255);
        $this->Cell(80, 10, 'Name Of BDM', 1, '0', 'C', true);
        $this->Cell(50, 10, 'No. of Policies Issued', 1, '0', 'C', true);
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

                $this->Cell(65, 15, $bdm['name'], 1, '0', 'L', true);
                $this->Cell(50, 15, $bdm['deals'], 1, '0', 'C', true);
                $this->Cell(60, 15, '$' . number_format($bdm['issued_api'], 2), 1, '1', 'C', true);
            }
        }

        $this->SetFont('Arial', 'B', 15);
        $this->Cell(80, 10, 'Total', 1, '0', 'L', true);
        $this->Cell(110, 10, '$' . number_format($total_api, 2), 1, '1', 'C', true);
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

                $this->Header1(15, $record['type'], 20);

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
                $this->Header1(140, $record['type'], 20);

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
                $this->Cell(50, 8, 'Adviser:', '', '1', 'L');
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
        //page 2 BiMonthly API
        $this->AddPage();

        $startingX = 10;
        $startingY = 10;

        $marginX = 5;
        $marginY = 5;

        $pointerX = $startingX;
        $pointerY = 30;

        $widthLimit = 196 + ($marginX * 2);
        $heightLimit = 259 + ($marginY * 2);

        $rowHeight = 0;

        $labelHeight = 20;

        $labelAndImageMargin = 6;

        $this->Header1(15, 'Photos', 20);

        foreach ($photos as $photo) {
            $imgWidth = $photo['width'];
            $imgHeight = $photo['height'];
            $imgLabel = $photo['label'];

            if ('' == $imgLabel) {
                $labelHeight = 0;
            } else {
                $labelHeight = 20;
            }

            if (($imgWidth + $pointerX) > $widthLimit) {
                $pointerY += $rowHeight + $marginY + $labelHeight + $labelAndImageMargin;
                $pointerX = $startingX;
                $rowHeight = 0;
            }

            if (($imgHeight + $labelHeight + $pointerY + $marginY) > $heightLimit) {
                $this->AddPage();
                $pointerX = $startingX;
                $pointerY = $startingY;
                $rowHeight = 0;
            }

            if ($rowHeight < $photo['height'] + $labelHeight) {
                $rowHeight = $photo['height'] + $labelHeight;
            }

            $this->Image($photo['filename'], $pointerX, $pointerY, $imgWidth, $imgHeight);

            if ('' != $imgLabel) {
                $this->FlexibleHeader1($pointerX, $pointerY + $imgHeight + $labelAndImageMargin, $imgWidth, $labelHeight, $imgLabel);
            }

            $pointerX += $marginX + $imgWidth;
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

    public function Strings($advisers)
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
        $this->Header1(15, 'Strings', 20);
        $this->Ln(6);
        $this->SetFillColor(102, 163, 194);
        $this->SetDrawColor(102, 163, 194);
        $this->Rect(10.5, $this->GetY() - 5, 195, 12, 'DF');
        $this->SetTextColor(255, 255, 255);
        $this->Cell(200, 3, 'Everything good happens to a stringwriter.', '', '1', 'C');

        $this->SetY(15);

        $score = $sorted_advisers[0]['score'];

        switch ($score) {
            case 'Titanium':
                $this->SetDrawColor(28, 135, 189);
                $this->SetFillColor(102, 163, 194);

                break;
            case 'Platinum':
                $this->SetDrawColor(207, 21, 21);
                $this->setFillColor(212, 91, 91);

                break;
            case 'Gold':
                $this->SetDrawColor(214, 181, 0);
                $this->setFillColor(255, 239, 148);

                break;
            case 'Silver':
                $this->SetDrawColor(163, 163, 163);
                $this->setFillColor(192, 192, 192);

                break;
            default:
                break;
        }

        $this->RoundedRect(10, $this->GetY() + 25, 195, 50, 5, '24', 'DF');

        $image_path = '';

        if (empty($sorted_advisers[0]['image'])) {
            $image_path = $this->default_image;
        } else {
            $image_path = $this->uploads_folder . $sorted_advisers[0]['image'];
        }

        if (file_exists($image_path)) {
            $this->Image($image_path, 20, $this->GetY() + 30, 40, 40);
        } else {
            $this->Image($this->default_image, 20, $this->GetY() + 30, 40, 40);
        }

        $this->SetTextColor(0, 0, 0);

        $this->SetX(50);
        $this->SetY($this->GetY() + 30);

        $this->Cell(92, 8, '', '', '0', 'L');
        $this->Cell(50, 8, '', '', '1', 'L');
        $this->SetFont('Calibri', 'B', 30);
        $this->Cell(60, 8, '', '', '0', 'L');
        $this->Cell(50, 8, strtoupper($sorted_advisers[0]['name']), '', '1', 'L');
        $this->Ln(5);

        $this->SetTextColor(255, 255, 255);
        $this->SetFont('Calibri', 'B', 20);
        $this->Cell(60, 8, '', '', '0', 'L');
        $this->Cell(10, 8, 'Score: ' . $sorted_advisers[0]['score'], '', '0', 'L');
        $this->Cell(50, 8, '', '', '0', 'L');
        $this->Cell(50, 8, 'String: ' . $sorted_advisers[0][strtolower($sorted_advisers[0]['score'])], '', '0', 'L');

        $this->SetFillColor(255, 255, 255);
        $this->SetTextColor(0, 0, 0);
        $this->SetY(100);

        foreach (array_slice($sorted_advisers, 1) as $index => $adviser) {
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
        }
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
        $this->SetFont('Calibri', 'B', 15);
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
                    $this->SetFont('Calibri', 'B', 15);

                    if ('Others' !== $adviser['name']) {
                        if ($adviser['deals'] >= 5 && $adviser['issued_api'] >= 7500) {
                            if (empty($adviser['image'])) {
                                $image_path = $this->default_image;
                            } else {
                                $image_path = $this->uploads_folder . $adviser['image'];
                            }

                            if (file_exists($image_path)) {
                                $this->Cell(15, 15, $this->Image($image_path, $this->GetX(), $this->GetY(), 15), 1, '0', 'L', false);
                            } else {
                                $this->Cell(15, 15, $this->Image($this->default_image, $this->GetX(), $this->GetY(), 15), 1, '0', 'L', false);
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
                                $this->Cell(15, 15, $this->Image($image_path, $this->GetX(), $this->GetY(), 15), 1, '0', 'L', false);
                            } else {
                                $this->Cell(15, 15, $this->Image($this->default_image, $this->GetX(), $this->GetY(), 15), 1, '0', 'L', false);
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
                                $this->Cell(15, 15, $this->Image($image_path, $this->GetX(), $this->GetY(), 15), 1, '0', 'L', false);
                            } else {
                                $this->Cell(15, 15, $this->Image($this->default_image, $this->GetX(), $this->GetY(), 15), 1, '0', 'L', false);
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
                                $this->Cell(15, 15, $this->Image($image_path, $this->GetX(), $this->GetY(), 15), 1, '0', 'L', false);
                            } else {
                                $this->Cell(15, 15, $this->Image($this->default_image, $this->GetX(), $this->GetY(), 15), 1, '0', 'L', false);
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
            } elseif ($adviser['deals'] >= 2 && $adviser['issued_api'] >= 2000) {
                $this->AddPage();
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
                                $this->Cell(15, 15, $this->Image($image_path, $this->GetX(), $this->GetY(), 15), 1, '0', 'L', false);
                            } else {
                                $this->Cell(15, 15, $this->Image($this->default_image, $this->GetX(), $this->GetY(), 15), 1, '0', 'L', false);
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
                                $this->Cell(15, 15, $this->Image($image_path, $this->GetX(), $this->GetY(), 15), 1, '0', 'L', false);
                            } else {
                                $this->Cell(15, 15, $this->Image($this->default_image, $this->GetX(), $this->GetY(), 15), 1, '0', 'L', false);
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
                                $this->Cell(15, 15, $this->Image($image_path, $this->GetX(), $this->GetY(), 15), 1, '0', 'L', false);
                            } else {
                                $this->Cell(15, 15, $this->Image($this->default_image, $this->GetX(), $this->GetY(), 15), 1, '0', 'L', false);
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
                                $this->Cell(15, 15, $this->Image($image_path, $this->GetX(), $this->GetY(), 15), 1, '0', 'L', false);
                            } else {
                                $this->Cell(15, 15, $this->Image($this->default_image, $this->GetX(), $this->GetY(), 15), 1, '0', 'L', false);
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
    $pdf->LinearGradient(81, 11, 120, 254, $white, $gray, $coords);

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
    $pdf->Cell(121, 8, $magazine_data->issue_number_line_2, 0, 1, 'L');

    $blue = [50, 143, 168];
    $darkblue = [50, 91, 168];
    $pdf->SetY(78);
    $pdf->SetX(15);

    $sorted_advisers = [];
    $t_array = [];
    $p_array = [];
    $g_array = [];
    $s_array = [];

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

    if (count($magazine_data->all_winner_score) > 0) {
        if ('Others' != $magazine_data->all_winner_score[0]['name']) {
            $pdf->SetDrawColor(50, 143, 168);
            $pdf->SetLineWidth(1);
            $pdf->RoundedRect($pdf->GetX() - 3, $pdf->GetY() - 2, 80, 62, 5, '1234', 'DF');
            $pdf->SetFont('Arial', 'I', 15);
            $pdf->Cell(8, 3, $pdf->MultiCell(78, 10, '"Everything good happens to a stringwriter."', 0, 'L', false, 15), '0', '1', 'L');
            $pdf->Cell(5, 3, '', '0', '1', 'L');
            $pdf->Cell(5, 3, '', '0', '1', 'L');
            $pdf->Cell(5, 3, '', '0', '0', 'L');
            $pdf->SetFont('Arial', 'B', 20);
            $pdf->Cell(80, 3, 'Top String Writer: ', '0', '1', 'L');
            $pdf->Cell(5, 5, '', '0', '1', 'L');
            $pdf->Cell(5, 5, '', '0', '0', 'L');
            $pdf->SetFont('Arial', 'BU', 20);
            $pdf->SetTextColor(0, 102, 153);
            $pdf->Cell(80, 10, strtoupper($sorted_advisers[0]['name']), '0', '1', 'L');
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

    $pdf->SetY(136);
    $pdf->SetFont('Calibri', 'U', 16);

    $pdf->Text(100, 125, 'Contents');
    $pdf->SetX(10);
    $pdf->SetFont('Calibri', '', 16);

    foreach ($magazine_data->pages as $content) {
        $pdf->Cell(90, 8, '', '', '0');
        $pdf->Cell(77, 8, $content['title'], '', '0', 'L');
        $pdf->Cell(115, 8, $content['page_start'], '', '1', 'L');
    }

    //Featured Adviser
    $featured_adviser = (count($magazine_data->bi_monthly_advisers) > 0) ? $magazine_data->bi_monthly_advisers[0] : $magazine_data->cumulative_advisers[0];
    $featured_title = (count($magazine_data->bi_monthly_advisers) > 0) ? 'Top Adviser of the Period ' . date('j', strtotime($magazine_data->bimonthRange->from)) . '-' . date('j F Y', strtotime($magazine_data->bimonthRange->to)) : 'Top Adviser of the Period ' . date('j M', strtotime($magazine_data->cumulativeRange->from)) . '-' . date('j M Y', strtotime($magazine_data->cumulativeRange->to));
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
        $pdf->Image($image_path, 15, 140, 75);
    } else {
        $pdf->Image($pdf->default_image, 15, 140, 75);
    }

    $pdf->SetDrawColor(200, 200, 200);
    $pdf->Rect(15, 210, 185, 49, 'FD');
    $pdf->SetFont('Arial', 'BU', 23);
    $pdf->SetTextColor(100, 100, 100);
    $pdf->Text(16.5, 222, $featured_title);
    $pdf->SetTextColor(0, 77, 115);
    $pdf->SetFont('Arial', 'B', 32);
    $pdf->Text(16.5, 235, $featured_adviser['name']);
    $pdf->SetTextColor(115, 146, 161);
    $pdf->SetFont('Arial', 'B', 27);
    $pdf->Text(16.5, 245, '$' . number_format($featured_adviser['issued_api'], 2) . ' Issued API');
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('Arial', 'B', 15);
    $pdf->Text(16.5, 255, $magazine_data->pages[0]['page']);

    if (! empty($magazine_data->message)) {
        $pdf->MessagePage($magazine_data->message);
    }

    if (count($magazine_data->bi_monthly_advisers) > 0) {
        if ('Others' != $magazine_data->bi_monthly_advisers[0]['name']) {
            $pdf->BiMonthlyPage($magazine_data->bi_monthly_advisers);
        }
    }

    if (count($magazine_data->cumulative_advisers) > 0) {
        if ('Others' != $magazine_data->cumulative_advisers[0]['name']) {
            $pdf->CumulativePage($magazine_data->cumulative_advisers);
        }
    }

    if (count($magazine_data->bi_monthly_advisers_kiwisavers) > 0) {
        if ('Others' != $magazine_data->bi_monthly_advisers_kiwisavers[0]['name']) {
            $pdf->BiMonthlyKiwiSaversPage($magazine_data->bi_monthly_advisers_kiwisavers);
        }
    }

    if (count($magazine_data->cumulative_advisers_kiwisavers) > 0) {
        if ('Others' != $magazine_data->cumulative_advisers_kiwisavers[0]['name']) {
            $pdf->CumulativeKiwiSaversPage($magazine_data->cumulative_advisers_kiwisavers);
        }
    }

    if (count($magazine_data->winner_score) > 0) {
        if ('Others' != $magazine_data->winner_score[0]['name']) {
            $pdf->WinnerScore($magazine_data->winner_score);
        }
    }

    if (count($magazine_data->all_winner_score) > 0) {
        if ('Others' != $magazine_data->all_winner_score[0]['name']) {
            $pdf->Strings($magazine_data->all_winner_score);
        }
    }

    if (count($magazine_data->rba_cumulative_advisers) > 0) {
        if ('Others' != $magazine_data->rba_cumulative_advisers[0]['name']) {
            $pdf->CumulativeRBAPage($magazine_data->rba_cumulative_advisers);
        }
    }

    if (count($magazine_data->records_to_beat) > 0) {
        $pdf->RecordsPage($magazine_data->records_to_beat);
    }

    //UNCOMMENT IF WE NEED BDM DATA

    if (count($magazine_data->bi_monthly_bdms) > 0) {
        if ('Others' != $magazine_data->bi_monthly_bdms[0]['name']) {
            $pdf->BDMBiMonthlyPage($magazine_data->bi_monthly_bdms);
        }
    }

    if (count($magazine_data->bdm_performances) > 0) {
        if ('Others' != $magazine_data->bdm_performances[0]['name']) {
            $pdf->BDMCumulativePage($magazine_data->bdm_performances, $magazine_data->quarterTitle);
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
        $pdf->Output('I', 'EliteInsure Magazine ' . $magazine_data->issue_number . ' ' . $magazine_data->issue_number_line_2 . '.pdf');
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
