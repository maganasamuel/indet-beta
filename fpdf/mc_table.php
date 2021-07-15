<?php
require 'gradient_pdf.php';

class PDF_MC_Table extends PDF_Gradients
{
    public $widths;
    public $aligns;
    public $mc_fonts = array();

    public function SetWidths($w)
    {
        //Set the array of column widths
        $this->widths = $w;
    }

    public function SetAligns($a)
    {
        //Set the array of column alignments
        $this->aligns = $a;
    }

    public function SetMCFonts($f)
    {
        //Set the array of column alignments
        $this->mc_fonts = $f;
    }

    public function Row($data, $border = false, $rectColor = array(255, 255, 255), $useFontStyles = false)
    {
        //Calculate the height of the row
        $nb = 0;
        for ($i = 0; $i < count($data); $i++) {
            $nb = max($nb, $this->NbLines($this->widths[$i], $data[$i]));
        }

        $h = 5 * $nb;
        //Issue a page break first if needed
        $this->CheckPageBreak($h);
        //Draw the cells of the row
        for ($i = 0; $i < count($data); $i++) {
            $w = $this->widths[$i];
            $a = isset($this->aligns[$i]) ? $this->aligns[$i] : 'C';

            if (count($this->mc_fonts) >= 1) {
                if (isset($this->mc_fonts[$i])) {
                    $this->SetFont($this->mc_fonts[$i][0], $this->mc_fonts[$i][1], $this->mc_fonts[$i][2]);
                }
            }

            //Save the current position
            $x = $this->GetX();
            $y = $this->GetY();
            //Draw the border
            $this->SetDrawColor($rectColor[0], $rectColor[1], $rectColor[2]);
            if ($border) {
                $this->Rect($x, $y, $w, $h, "FD");
            }

            //Print the text
            $this->MultiCell($w, 5, $data[$i], 0, $a);
            //Put the position to the right of the cell
            $this->SetXY($x + $w, $y);
        }
        //Go to the next line
        $this->Ln($h);
    }

    public function LRow($data, $border = false, $rectColor = array(255, 255, 255), $useFontStyles = false)
    {
        //Calculate the height of the row
        $nb = 0;
        for ($i = 0; $i < count($data); $i++) {
            $nb = max($nb, $this->NbLines($this->widths[$i], $data[$i]));
        }

        $h = 5 * $nb;
        //Issue a page break first if needed
        $this->CheckPageBreak($h);
        //Draw the cells of the row
        for ($i = 0; $i < count($data); $i++) {
            $w = $this->widths[$i];
            $a = isset($this->aligns[$i]) ? $this->aligns[$i] : 'L';

            if (count($this->mc_fonts) >= 1) {
                if (isset($this->mc_fonts[$i])) {
                    $this->SetFont($this->mc_fonts[$i][0], $this->mc_fonts[$i][1], $this->mc_fonts[$i][2]);
                }
            }

            //Save the current position
            $x = $this->GetX();
            $y = $this->GetY();
            //Draw the border
            $this->SetDrawColor($rectColor[0], $rectColor[1], $rectColor[2]);
            if ($border) {
                $this->Rect($x, $y, $w, $h, "FD");
            }

            //Print the text
            $this->MultiCell($w, 5, $data[$i], 0, $a);
            //Put the position to the right of the cell
            $this->SetXY($x + $w, $y);
        }
        //Go to the next line
        $this->Ln($h);
    }

    public function CheckPageBreak($h)
    {
        //If the height h would cause an overflow, add a new page immediately
        if ($this->GetY() + $h > $this->PageBreakTrigger) {
            $this->AddPage($this->CurOrientation);
        }

    }

    public function NbLines($w, $txt)
    {
        //Computes the number of lines a MultiCell of width w will take
        $cw = &$this->CurrentFont['cw'];
        if ($w == 0) {
            $w = $this->w - $this->rMargin - $this->x;
        }

        $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
        $s = str_replace("\r", '', $txt);
        $nb = strlen($s);
        if ($nb > 0 and $s[$nb - 1] == "\n") {
            $nb--;
        }

        $sep = -1;
        $i = 0;
        $j = 0;
        $l = 0;
        $nl = 1;
        while ($i < $nb) {
            $c = $s[$i];
            if ($c == "\n") {
                $i++;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
                continue;
            }
            if ($c == ' ') {
                $sep = $i;
            }

            $l += $cw[$c];
            if ($l > $wmax) {
                if ($sep == -1) {
                    if ($i == $j) {
                        $i++;
                    }

                } else {
                    $i = $sep + 1;
                }

                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
            } else {
                $i++;
            }

        }
        return $nl;
    }
}
