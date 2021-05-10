<?php
/***********************************************************************************************************

This line graph function was developed by Anthony Master

***********************************************************************************************************/
require('sector.php');

class PDF_With_Graph extends PDF_Sector  {

    function Footer()
	{
		global $fsp_num;
		global $name;
		$this->SetY(-15);
		$this->SetFont('Helvetica','',10);
		$this->SetTextColor(0,0,0);
		$this->Cell(200,10,"",0,0,'C');	
		$this->AliasNbPages('{totalPages}');	
		$this->Cell(0,10,'Page '.$this->PageNo() . " of " . "{totalPages}",0,1,'R');
	}

	function getPage(){
		return $this->PageNo();
    }
    
    //For Dashed Lines
    function SetDash($black=null, $white=null)
    {
        if($black!==null)
            $s=sprintf('[%.3F %.3F] 0 d',$black*$this->k,$white*$this->k);
        else
            $s='[] 0 d';
        $this->_out($s);
    }
    //LINE GRAPH
    function LineGraph($w, $h, $data, $options='', $colors=null, $maxVal=0, $nbDiv=4, $dash_indexes = array(), $dash_values = array()){
        /*******************************************
            Explain the variables:
            $w = the width of the diagram
            $h = the height of the diagram
            $data = the data for the diagram in the form of a multidimensional array
            $options = the possible formatting options which include:
                'V' = Print Vertical Divider lines
                'H' = Print Horizontal Divider Lines
                'kB' = Print bounding box around the Key (legend)
                'vB' = Print bounding box around the values under the graph
                'gB' = Print bounding box around the graph
                'dB' = Print bounding box around the entire diagram
            $colors = A multidimensional array containing RGB values
            $maxVal = The Maximum Value for the graph vertically
            $nbDiv = The number of vertical Divisions
        *******************************************/

        $this->SetDrawColor(0,0,0);
        $this->SetLineWidth(0.2);

        $keys = array_keys($data);
        $ordinateWidth = 10;
        $w -= $ordinateWidth;
        $valX = $this->getX()+$ordinateWidth;
        $valY = $this->getY();
        $margin = 1;
        $titleH = 8;
        $titleW = $w;
        $lineh = 5;
        $keyH = count($data)*$lineh;

        $keyW = $w/9;

        $graphValH = 5;
        $graphValW = $w-$keyW-3*$margin;
        $graphH = $h-(3*$margin)-$graphValH;
        $graphW = $w-(2*$margin)-($keyW+$margin);
        $graphX = $valX+$margin;
        $graphY = $valY+$margin;
        $graphValX = $valX+$margin;
        $graphValY = $valY+2*$margin+$graphH;
        $keyX = $valX+(2*$margin)+$graphW;
        $keyY = $valY+$margin+.5*($h-(2*$margin))-.5*($keyH);

        //draw graph frame border
        if(strstr($options,'gB')){
            $this->Rect($valX,$valY,$w,$h);
        }

        //draw graph diagram border
        if(strstr($options,'dB')){
            $this->Rect($valX+$margin,$valY+$margin,$graphW,$graphH);
        }

        //draw key legend border
        if(strstr($options,'kB')){
            $this->Rect($keyX,$keyY,$keyW,$keyH);
        }
        //draw graph value box
        if(strstr($options,'vB')){
            $this->Rect($graphValX,$graphValY,$graphValW,$graphValH);
        }
        //define colors
        if($colors===null){
            $safeColors = array(0,51,102,153,204,225);
            for($i=0;$i<count($data);$i++){
                $colors[$keys[$i]] = array($safeColors[array_rand($safeColors)],$safeColors[array_rand($safeColors)],$safeColors[array_rand($safeColors)]);
            }
        }
        //form an array with all data values from the multi-demensional $data array
        $ValArray = array();
        foreach($data as $key => $value){
            foreach($data[$key] as $val){
                $ValArray[]=$val;                    
            }
        }

        
        //define max value
        if($maxVal<ceil(max($ValArray))){
            $maxVal = ceil(max($ValArray));
        }
        //draw horizontal lines
        $vertDivH = $graphH/$nbDiv;
        if(strstr($options,'H')){
            for($i=0;$i<=$nbDiv;$i++){
                if($i<$nbDiv){
                    $this->Line($graphX,$graphY+$i*$vertDivH,$graphX+$graphW,$graphY+$i*$vertDivH);
                } else{
                    $this->Line($graphX,$graphY+$graphH,$graphX+$graphW,$graphY+$graphH);
                }
            }
        }
        
        //draw vertical lines
        $horiDivW = 0;
        if($graphW!=0 && (count($data[$keys[0]])-1) != 0)
            $horiDivW = floor($graphW/(count($data[$keys[0]])-1));

        if(strstr($options,'V')){
            for($i=0;$i<=(count($data[$keys[0]])-1);$i++){
                if($i<(count($data[$keys[0]])-1)){
                    $this->Line($graphX+$i*$horiDivW,$graphY,$graphX+$i*$horiDivW,$graphY+$graphH);
                } else {
                    $this->Line($graphX+$graphW,$graphY,$graphX+$graphW,$graphY+$graphH);
                }
            }
        }
        
        $linectr = 0;

           // var_dump($dash_values);
        //draw graph lines
        foreach($data as $key => $value){
            $this->setDrawColor($colors[$key][0],$colors[$key][1],$colors[$key][2]);
            $this->SetLineWidth(0.8);
            //check if data index is in array of dash indexes
            if(!empty($dash_indexes)){
                if(in_array( $linectr , $dash_indexes)){
                    $this->SetDash($dash_values[$linectr][0], $dash_values[$linectr][1]);
                }
            }
            $valueKeys = array_keys($value);
            for($i=0;$i<count($value);$i++){
                if($i==count($value)-2){
                    $this->Line(
                        $graphX+($i*$horiDivW),
                        $graphY+$graphH-($value[$valueKeys[$i]]/$maxVal*$graphH),
                        $graphX+$graphW,
                        $graphY+$graphH-($value[$valueKeys[$i+1]]/$maxVal*$graphH)
                    );
                } else if($i<(count($value)-1)) {
                    //var_dump(((int)$value[$valueKeys[$i]][0]));
                    $this->Line(
                        $graphX+($i*$horiDivW),
                        $graphY+$graphH-(((int)$value[$valueKeys[$i]])/$maxVal*$graphH),
                        $graphX+($i+1)*$horiDivW,
                        $graphY+$graphH-($value[$valueKeys[$i+1]]/$maxVal*$graphH)
                    );
                }
            }
            //Set the Key (legend)
            $this->SetFont('Courier','',7);
            if(!isset($n))$n=0;
            $this->Line($keyX+1,$keyY+$lineh/2+$n*$lineh,$keyX+8,$keyY+$lineh/2+$n*$lineh);
            $this->SetXY($keyX+8,$keyY+$n*$lineh);
            $this->Cell($keyW,$lineh,$key,0,1,'L');
            $this->SetDash();
            $n++;
            $linectr++;
        }
        //print the abscissa values
        foreach($valueKeys as $key => $value){
            if($key==0){
                $this->SetXY($graphValX,$graphValY);
                $this->Cell(30,$lineh,$value,0,0,'L');
            } else if($key==count($valueKeys)-1){
                $this->SetXY($graphValX+$graphValW-30,$graphValY);
                $this->Cell(30,$lineh,$value,0,0,'R');
            } else {
                $this->SetXY($graphValX+$key*$horiDivW-15,$graphValY);
                $this->Cell(30,$lineh,$value,0,0,'C');
            }
        }
        //print the ordinate values
        for($i=0;$i<=$nbDiv;$i++){
            $this->SetXY($graphValX-10,$graphY+($nbDiv-$i)*$vertDivH-3);
            $this->Cell(8,6,sprintf('%.1f',$maxVal/$nbDiv*$i),0,0,'R');
        }
        $this->SetDrawColor(0,0,0);
        $this->SetLineWidth(0.2);
    }
    //LINE GRAPH

    //PIE AND BAR GRAPH
    var $legends;
    var $wLegend;
    var $sum;
    var $NbVal;
    //PIE CHART
    function PieChart($w, $h, $data, $format, $colors=null, $legendsWidth, $borders = false)
    {
        $this->SetFont('Courier', '', 10);
        $this->SetLegends($data,$format);

        $XPage = $this->GetX();
        $YPage = $this->GetY();
        $margin = 2;
        $hLegend = 5;
        $radius = min($w - $margin * 4 - $hLegend - $this->wLegend, $h - $margin * 2);
        $radius = floor($radius / 2);
        $XDiag = $XPage + $margin + $radius;
        $YDiag = $YPage + $margin + $radius;
        if($colors == null) {
            for($i = 0; $i < $this->NbVal; $i++) {
                $gray = $i * intval(255 / $this->NbVal);
                $colors[$i] = array($gray,$gray,$gray);
            }
        }
        if($borders)
            $this->Rect($XPage,$YPage,$w,$h);
        //Sectors
        $this->SetLineWidth(0.2);
        $angleStart = 0;
        $angleEnd = 0;
        $i = 0;
        foreach($data as $val) {
            $angle = ($val * 360) / doubleval($this->sum);
            if ($angle != 0) {
                $angleEnd = $angleStart + $angle;
                $this->SetFillColor($colors[$i][0],$colors[$i][1],$colors[$i][2]);
                $this->Sector($XDiag, $YDiag+5, $radius, $angleStart, $angleEnd);
                $angleStart += $angle;
            }
            $i++;
        }

        //Legends
        $this->SetFont('Courier', 'B', 9);
        $x1 = $XPage + 2 * $radius + 4 * $margin;
        $x2 = $x1 + $hLegend + $margin;
        $y1 = $YDiag - $radius + (2 * $radius - $this->NbVal*($hLegend + $margin)) / 2;
        for($i=0; $i<$this->NbVal; $i++) {
            $this->SetFillColor($colors[$i][0],$colors[$i][1],$colors[$i][2]);
            $this->Rect($x1, $y1, $hLegend, $hLegend, 'DF');
            $this->SetXY($x2,$y1);
            $this->Cell($legendsWidth,$hLegend,$this->legends[$i]);
            $y1+=$hLegend + $margin;
        }
        /*
        $cells = count($data);
            $step = 100 / $cells;
            $i = 0;
            $hLegend = 5;
            foreach($data as $itemName=>$item){
                $this->SetFillColor($color[$i][0],$color[$i][1],$color[$i][2]);
                $this->SetXY($x1,$y1);
                $this->Rect($x1+10, $y1, $hLegend, $hLegend, 'DF');
                $this->Cell($x1,$hLegend,"       " . $this->legends[$i]);
                $y1+=$hLegend + $margin;
                $x1 += $step;
                $y1 -= 7;
                $i++;
            }
        */
    }
    //PIE CHART
    //BAR GRAPH
    function BarDiagram($w, $h, $data, $format, $color=null, $maxVal=0, $nbDiv=4, $orientation = "horizontal", $borders = false)
    {
        $this->SetFont('Courier', '', 10);
        $this->SetLegends($data,$format);

        $XPage = $this->GetX();
        $YPage = $this->GetY();
        $margin = 2;
        if($orientation=="horizontal"){
            $YDiag = $YPage + $margin;
            $hDiag = floor($h - $margin * 2);
            $XDiag = $XPage + $margin * 2 + $this->wLegend;
            $lDiag = floor($w - $margin * 3 - $this->wLegend);
            if($color == null)
                $color=array(155,155,155);
            if ($maxVal == 0) {
                $maxVal = max($data);
            }
            $valIndRepere = ceil($maxVal / $nbDiv);
            $maxVal = $valIndRepere * $nbDiv;
            $lRepere = floor($lDiag / $nbDiv);
            $lDiag = $lRepere * $nbDiv;
            $unit = $lDiag / $maxVal;
            $hBar = floor($hDiag / ($this->NbVal + 1));
            $hDiag = $hBar * ($this->NbVal + 1);
            $eBaton = floor($hBar * 80 / 100);

            $this->SetLineWidth(0.2);

            $this->Rect($XDiag, $YDiag, $lDiag, $hDiag);

            $this->SetFont('Courier', '', 10);
            
            $i=0;
            foreach($data as $val) {
                //Bar
                $this->SetFillColor($color[$i][0],$color[$i][1],$color[$i][2]);
                $xval = $XDiag;
                $lval = (int)($val * $unit);
                $yval = $YDiag + ($i + 1) * $hBar - $eBaton / 2;
                $hval = $eBaton;
                $this->Rect($xval, $yval, $lval, $hval, 'DF');
                //Legend
                $this->SetXY(0, $yval);
                $this->Cell($xval - $margin, $hval, $this->legends[$i],0,0,'R');
                $i++;
            }

            //Scales
            for ($i = 0; $i <= $nbDiv; $i++) {
                $xpos = $XDiag + $lRepere * $i;
                $this->Line($xpos, $YDiag, $xpos, $YDiag + $hDiag);
                $val = $i * $valIndRepere;
                $xpos = $XDiag + $lRepere * $i - $this->GetStringWidth($val) / 2;
                $ypos = $YDiag + $hDiag - $margin;
                $this->Text($xpos, $ypos+6, $val);
            }
        }
        else{
            $chartX=$XPage;
            $chartY=$YPage;

            //dimension
            $chartWidth=$w;
            $chartHeight=$h;

            //padding
            $chartTopPadding=10;
            $chartLeftPadding=20;
            $chartBottomPadding=20;
            $chartRightPadding=5;

            //chart box
            $chartBoxX=$chartX+$chartLeftPadding;
            $chartBoxY=$chartY+$chartTopPadding;
            $chartBoxWidth=$chartWidth-$chartLeftPadding-$chartRightPadding;
            $chartBoxHeight=$chartHeight-$chartBottomPadding-$chartTopPadding;

            //bar width
            $barWidth=20;

            //$dataMax
            $dataMax=0;
            $dataLeast=9999999999999999999999;
            foreach($data as $item){
                if($item>$dataMax)$dataMax=$item;
                if($item<$dataLeast)$dataLeast=$item;
            }

            //data step
            $dataStep=($dataMax) / 4;
            $dataStep = (int)$dataStep;
            //set font, line width and color
            $this->SetFont('Arial','',9);
            $this->SetLineWidth(0.2);
            $this->SetDrawColor(0);

            //chart boundary
            if($borders)
            $this->Rect($chartX+5,$chartY,$chartWidth-5,$chartHeight);

            //vertical axis line
            $this->Line(
                $chartBoxX ,
                $chartBoxY , 
                $chartBoxX , 
                ($chartBoxY+$chartBoxHeight)
                );
            //horizontal axis line
            $this->Line(
                $chartBoxX-2 , 
                ($chartBoxY+$chartBoxHeight) , 
                $chartBoxX+($chartBoxWidth) , 
                ($chartBoxY+$chartBoxHeight)
                );

            ///vertical axis
            //calculate chart's y axis scale unit
            $yAxisUnits=$chartBoxHeight/$dataMax;

            //draw the vertical (y) axis labels
            for($i=0 ; $i<=$dataMax ; $i+=$dataStep){
                //y position
                $yAxisPos=$chartBoxY+($yAxisUnits*$i);
                //draw y axis line
                $this->Line(
                    $chartBoxX-2 ,
                    $yAxisPos ,
                    $chartBoxX ,
                    $yAxisPos
                );
                //set cell position for y axis labels
                $this->SetXY($chartBoxX-$chartLeftPadding , $yAxisPos-2);
                //$pdf->Cell($chartLeftPadding-4 , 5 , $dataMax-$i , 1);---------------
                $this->Cell($chartLeftPadding-4 , 5 , $dataMax-$i, 0 , 0 , 'R');
            }

            ///horizontal axis
            //set cells position
            $this->SetXY($chartBoxX , $chartBoxY+$chartBoxHeight);

            //cell's width
            $xLabelWidth=$chartBoxWidth / count($data);

            //$pdf->Cell($xLabelWidth , 5 , $itemName , 1 , 0 , 'C');-------------
            //loop horizontal axis and draw the bar
            $barXPos=0;
            $ctr = 0;
            foreach($data as $itemName=>$item){
                //print the label
                //$pdf->Cell($xLabelWidth , 5 , $itemName , 1 , 0 , 'C');--------------
                $this->Cell($xLabelWidth , 5 , $itemName , 0 , 0 , 'C');
                
                ///drawing the bar
                //bar color
                $this->SetFillColor($color[$ctr][0],$color[$ctr][1],$color[$ctr][2]);
                //bar height
                $barHeight=$yAxisUnits*$item;
                //bar x position
                $barX=($xLabelWidth/2)+($xLabelWidth*$barXPos);
                $barX=$barX-($barWidth/2);
                $barX=$barX+$chartBoxX;
                //bar y position
                $barY=$chartBoxHeight-$barHeight;
                $barY=$barY+$chartBoxY;
                //draw the bar
                $this->Rect($barX,$barY,$barWidth,$barHeight,'DF');
                //increase x position (next series)
                $barXPos++;
                $ctr++;
            }

            //axis labels
            //$this->SetFont('Courier', '', 10);
            $this->SetFont('Courier','B',10);
            $this->SetXY($chartX,$chartY);
            //$this->Cell(100,10,"Amount",0);
            $x1 = ($chartWidth/2)-50+$chartX;
            $y1 = $chartY+$chartHeight-($chartBottomPadding/2);
            $this->SetXY($x1,$y1);
            //LEGEND
            $cells = count($data);
            $step = 100 / $cells;
            $i = 0;
            $hLegend = 5;
            foreach($data as $itemName=>$item){
                $this->SetFillColor($color[$i][0],$color[$i][1],$color[$i][2]);
                $this->SetXY($x1,$y1);
                $this->Rect($x1+10, $y1, $hLegend, $hLegend, 'DF');
                $this->Cell($x1,$hLegend,"       " . $this->legends[$i]);
                $y1+=$hLegend + $margin;
                $x1 += $step;
                $y1 -= 7;
                $i++;
            }
        }
    }
    //BAR GRAPH
    //LEGENDS EDITOR
    function SetLegends($data, $format)
    {

        $this->legends=array();
        $this->wLegend=0;
        $this->sum=array_sum($data);
        $this->NbVal=count($data);

        foreach($data as $l=>$val)
        {   
            $p=sprintf('%.2f',$val/$this->sum*100).'%';
            $legend=str_replace(array('%l','%v','%p'),array($l,$val,$p),$format);
            $this->legends[]=$legend;
            $this->wLegend=max($this->GetStringWidth($legend),$this->wLegend);
        }
    }
    //EDIT LEGEND


    //GRADIENTS
    var $gradients = array();

    function LinearGradient($x, $y, $w, $h, $col1=array(), $col2=array(), $coords=array(0,0,1,0)){
        $this->Clip($x,$y,$w,$h);
        $this->Gradient(2,$col1,$col2,$coords);
    }

    function RadialGradient($x, $y, $w, $h, $col1=array(), $col2=array(), $coords=array(0.5,0.5,0.5,0.5,1)){
        $this->Clip($x,$y,$w,$h);
        $this->Gradient(3,$col1,$col2,$coords);
    }

    function CoonsPatchMesh($x, $y, $w, $h, $col1=array(), $col2=array(), $col3=array(), $col4=array(), $coords=array(0.00,0.0,0.33,0.00,0.67,0.00,1.00,0.00,1.00,0.33,1.00,0.67,1.00,1.00,0.67,1.00,0.33,1.00,0.00,1.00,0.00,0.67,0.00,0.33), $coords_min=0, $coords_max=1){
        $this->Clip($x,$y,$w,$h);        
        $n = count($this->gradients)+1;
        $this->gradients[$n]['type']=6; //coons patch mesh
        //check the coords array if it is the simple array or the multi patch array
        if(!isset($coords[0]['f'])){
            //simple array -> convert to multi patch array
            if(!isset($col1[1]))
                $col1[1]=$col1[2]=$col1[0];
            if(!isset($col2[1]))
                $col2[1]=$col2[2]=$col2[0];
            if(!isset($col3[1]))
                $col3[1]=$col3[2]=$col3[0];
            if(!isset($col4[1]))
                $col4[1]=$col4[2]=$col4[0];
            $patch_array[0]['f']=0;
            $patch_array[0]['points']=$coords;
            $patch_array[0]['colors'][0]['r']=$col1[0];
            $patch_array[0]['colors'][0]['g']=$col1[1];
            $patch_array[0]['colors'][0]['b']=$col1[2];
            $patch_array[0]['colors'][1]['r']=$col2[0];
            $patch_array[0]['colors'][1]['g']=$col2[1];
            $patch_array[0]['colors'][1]['b']=$col2[2];
            $patch_array[0]['colors'][2]['r']=$col3[0];
            $patch_array[0]['colors'][2]['g']=$col3[1];
            $patch_array[0]['colors'][2]['b']=$col3[2];
            $patch_array[0]['colors'][3]['r']=$col4[0];
            $patch_array[0]['colors'][3]['g']=$col4[1];
            $patch_array[0]['colors'][3]['b']=$col4[2];
        }
        else{
            //multi patch array
            $patch_array=$coords;
        }
        $bpcd=65535; //16 BitsPerCoordinate
        //build the data stream
        $this->gradients[$n]['stream']='';
        for($i=0;$i<count($patch_array);$i++){
            $this->gradients[$n]['stream'].=chr($patch_array[$i]['f']); //start with the edge flag as 8 bit
            for($j=0;$j<count($patch_array[$i]['points']);$j++){
                //each point as 16 bit
                $patch_array[$i]['points'][$j]=(($patch_array[$i]['points'][$j]-$coords_min)/($coords_max-$coords_min))*$bpcd;
                if($patch_array[$i]['points'][$j]<0) $patch_array[$i]['points'][$j]=0;
                if($patch_array[$i]['points'][$j]>$bpcd) $patch_array[$i]['points'][$j]=$bpcd;
                $this->gradients[$n]['stream'].=chr(floor($patch_array[$i]['points'][$j]/256));
                $this->gradients[$n]['stream'].=chr(floor($patch_array[$i]['points'][$j]%256));
            }
            for($j=0;$j<count($patch_array[$i]['colors']);$j++){
                //each color component as 8 bit
                $this->gradients[$n]['stream'].=chr($patch_array[$i]['colors'][$j]['r']);
                $this->gradients[$n]['stream'].=chr($patch_array[$i]['colors'][$j]['g']);
                $this->gradients[$n]['stream'].=chr($patch_array[$i]['colors'][$j]['b']);
            }
        }
        //paint the gradient
        $this->_out('/Sh'.$n.' sh');
        //restore previous Graphic State
        $this->_out('Q');
    }

    function Clip($x,$y,$w,$h){
        //save current Graphic State
        $s='q';
        //set clipping area
        $s.=sprintf(' %.2F %.2F %.2F %.2F re W n', $x*$this->k, ($this->h-$y)*$this->k, $w*$this->k, -$h*$this->k);
        //set up transformation matrix for gradient
        $s.=sprintf(' %.3F 0 0 %.3F %.3F %.3F cm', $w*$this->k, $h*$this->k, $x*$this->k, ($this->h-($y+$h))*$this->k);
        $this->_out($s);
    }

    function Gradient($type, $col1, $col2, $coords){
        $n = count($this->gradients)+1;
        $this->gradients[$n]['type']=$type;
        if(!isset($col1[1]))
            $col1[1]=$col1[2]=$col1[0];
        $this->gradients[$n]['col1']=sprintf('%.3F %.3F %.3F',($col1[0]/255),($col1[1]/255),($col1[2]/255));
        if(!isset($col2[1]))
            $col2[1]=$col2[2]=$col2[0];
        $this->gradients[$n]['col2']=sprintf('%.3F %.3F %.3F',($col2[0]/255),($col2[1]/255),($col2[2]/255));
        $this->gradients[$n]['coords']=$coords;
        //paint the gradient
        $this->_out('/Sh'.$n.' sh');
        //restore previous Graphic State
        $this->_out('Q');
    }

    function _putshaders(){
        foreach($this->gradients as $id=>$grad){  
            if($grad['type']==2 || $grad['type']==3){
                $this->_newobj();
                $this->_out('<<');
                $this->_out('/FunctionType 2');
                $this->_out('/Domain [0.0 1.0]');
                $this->_out('/C0 ['.$grad['col1'].']');
                $this->_out('/C1 ['.$grad['col2'].']');
                $this->_out('/N 1');
                $this->_out('>>');
                $this->_out('endobj');
                $f1=$this->n;
            }
            
            $this->_newobj();
            $this->_out('<<');
            $this->_out('/ShadingType '.$grad['type']);
            $this->_out('/ColorSpace /DeviceRGB');
            if($grad['type']=='2'){
                $this->_out(sprintf('/Coords [%.3F %.3F %.3F %.3F]',$grad['coords'][0],$grad['coords'][1],$grad['coords'][2],$grad['coords'][3]));
                $this->_out('/Function '.$f1.' 0 R');
                $this->_out('/Extend [true true] ');
                $this->_out('>>');
            }
            elseif($grad['type']==3){
                //x0, y0, r0, x1, y1, r1
                //at this time radius of inner circle is 0
                $this->_out(sprintf('/Coords [%.3F %.3F 0 %.3F %.3F %.3F]',$grad['coords'][0],$grad['coords'][1],$grad['coords'][2],$grad['coords'][3],$grad['coords'][4]));
                $this->_out('/Function '.$f1.' 0 R');
                $this->_out('/Extend [true true] ');
                $this->_out('>>');
            }
            elseif($grad['type']==6){
                $this->_out('/BitsPerCoordinate 16');
                $this->_out('/BitsPerComponent 8');
                $this->_out('/Decode[0 1 0 1 0 1 0 1 0 1]');
                $this->_out('/BitsPerFlag 8');
                $this->_out('/Length '.strlen($grad['stream']));
                $this->_out('>>');
                $this->_putstream($grad['stream']);
            }
            $this->_out('endobj');
            $this->gradients[$id]['id']=$this->n;
        }
    }

    function _putresourcedict(){
        parent::_putresourcedict();
        $this->_out('/Shading <<');
        foreach($this->gradients as $id=>$grad)
             $this->_out('/Sh'.$id.' '.$grad['id'].' 0 R');
        $this->_out('>>');
    }

    function _putresources(){
        $this->_putshaders();
        parent::_putresources();
    }
    //GRADIENTS
}
?>