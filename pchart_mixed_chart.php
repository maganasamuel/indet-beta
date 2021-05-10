<?php
/* CAT:Bar Chart */

/* pChart library inclusions */

require_once('libs/plugins/pChart2.1.4/class/pData.class.php');
require_once('libs/plugins/pChart2.1.4/class/pDraw.class.php');
require_once('libs/plugins/pChart2.1.4/class/pPie.class.php');
require_once('libs/plugins/pChart2.1.4/class/pImage.class.php');

$width = 1000;
$height = 650;
$title = "Data";
/* Create and populate the pData object */
$MyData = new pData();
$MyData->addPoints(array(365), "Leads Required");
$MyData->addPoints(array(285), "Leads");
$MyData->setSerieTicks("Leads", 4);
$MyData->setAxisName(0, "Leads");
$MyData->addPoints(array("Leads"), "Labels");
$MyData->setSerieDescription("Labels", "Leads");
$MyData->setAbscissa("Labels");


/* Create the pChart object */
$myPicture = new pImage($width, $height, $MyData);


/* Draw the background */
$Settings = array("R" => 170, "G" => 183, "B" => 87, "Dash" => 1, "DashR" => 190, "DashG" => 203, "DashB" => 107);
$myPicture->drawFilledRectangle(0, 0, $width, $height, $Settings);

/* Overlay with a gradient */
$Settings = array("StartR" => 219, "StartG" => 231, "StartB" => 139, "EndR" => 1, "EndG" => 138, "EndB" => 68, "Alpha" => 50);
$myPicture->drawGradientArea(0, 0, $width, $height, DIRECTION_VERTICAL, $Settings);
//$myPicture->drawGradientArea(0, 0, $width, 20, DIRECTION_VERTICAL, array("StartR" => 0, "StartG" => 0, "StartB" => 0, "EndR" => 50, "EndG" => 50, "EndB" => 50, "Alpha" => 80));

/* Add a border to the picture */
$myPicture->drawRectangle(0, 0, $width - 1, $height - 1, array("R" => 0, "G" => 0, "B" => 0));

/* Write the picture title */
$myPicture->setFontProperties(array("FontName" => "fonts/Silkscreen.ttf", "FontSize" => 6));

/* Write the chart title */
$myPicture->setFontProperties(array("FontName" => "fonts/Forgotte.ttf", "FontSize" => 11));
//$myPicture->drawText(250, 55, "Average temperature", array("FontSize" => 20, "Align" => TEXT_ALIGN_BOTTOMMIDDLE));

$color_dark = array(0,50,204);
$color_light = array(62,175,255);
$myPicture->drawGradientArea(0,0,$width,$height,DIRECTION_VERTICAL,array("StartR"=>$color_light[0],"StartG"=>$color_light[1],"StartB"=>$color_light[2],"EndR"=>$color_dark[0],"EndG"=>$color_dark[1],"EndB"=>$color_dark[2],"Alpha"=>100));
$myPicture->drawGradientArea(0,0,$width,$height,DIRECTION_HORIZONTAL,array("StartR"=>$color_light[0],"StartG"=>$color_light[1],"StartB"=>$color_light[2],"EndR"=>$color_dark[0],"EndG"=>$color_dark[1],"EndB"=>$color_dark[2],"Alpha"=>20));

//Draw graph bg
$myPicture->drawFilledRectangle(30, 30, $width - 30, $height - 30, array("R" => 255, "G" => 255, "B" => 255, "Surrounding" => -200, "Alpha" => 10));


/* Draw the scale and the 1st chart */
$myPicture->setGraphArea($width / 2, 60, $width - 220, $height / 2 - 30);
$myPicture->drawScale(array("DrawSubTicks" => TRUE,"Mode"=>SCALE_MODE_START0));
$myPicture->setShadow(TRUE, array("X" => 1, "Y" => 1, "R" => 0, "G" => 0, "B" => 0, "Alpha" => 10));
$myPicture->setFontProperties(array("FontName" => "../fonts/pf_arma_five.ttf", "FontSize" => 12));
$myPicture->drawBarChart(array("DisplayValues" => TRUE, "DisplayColor" => DISPLAY_AUTO, "Rounded" => TRUE, "Surrounding" => 30));
$myPicture->setShadow(FALSE);


/* Write the chart legend */
$myPicture->setFontProperties(array("FontName" => "fonts/Silkscreen.ttf", "FontSize" => 12));
$myPicture->drawLegend($width - 210, $height / 4,array("Style"=>LEGEND_NOBORDER,"Mode"=>LEGEND_VERTICAL));



$pieData = new pData();   
$pieData->addPoints(array(285,15),"Leads");  
$pieData->setSerieDescription("ScoreA","Application A");

/* Define the absissa serie */
$pieData->addPoints(array("Non-Issued Leads","Issued Leads"),"Labels");
$pieData->setAbscissa("Labels");

/* Create the pPie object */
$PieChart = new pPie($myPicture, $pieData);

$myPicture->setFontProperties(array("FontName" => "fonts/Silkscreen.ttf", "FontSize" => 10));
/* Draw two AA pie chart */
$PieChart->draw2DPie($width / 4, $height / 4, array("Radius"=>80,"DrawLabels"=>TRUE,
"DataGapRadius"=>6,"Border" => TRUE));



 /* Create and populate the pData object line */
 $MyData->addPoints(array(-4,0,0,12,8,3),"Probe 1");
 $MyData->addPoints(array(3,12,15,8,5,-5),"Probe 2");
 $MyData->addPoints(array(2,7,5,18,19,22),"Probe 3");
 $MyData->setSerieTicks("Probe 2",4);
 $MyData->setSerieWeight("Probe 3",2);
 $MyData->setAxisName(0,"Temperatures");
 $MyData->setSerieDescription("Labels","Months");
 $MyData->setAbscissa("Labels");

$MyData->setSerieDrawable("Leads Required", FALSE);
$MyData->setSerieDrawable("Leads", FALSE);
$MyData->setSerieDrawable("Probe 1", TRUE);
$MyData->setSerieDrawable("Probe 2", TRUE);
$MyData->setSerieDrawable("Probe 3", TRUE);
$MyData->setSerieDrawable("Probe 3", TRUE);

$MyData->addPoints(array("Jan","Feb","Mar","Apr","May","Jun"),"Labels");

/* Write down the legend next to the 2nd chart*/
$myPicture->setFontProperties(array("FontName" => "fonts/Silkscreen.ttf", "FontSize" => 12));

 /* Write the chart title */ 
 $myPicture->setFontProperties(array("FontName"=>"fonts/Forgotte.ttf","FontSize"=>11));

 /* Draw the scale and the 1st chart */
 $myPicture->setGraphArea(90,$height / 2,$width - 220,$height - 60);
 $myPicture->drawScale(array("DrawSubTicks"=>TRUE));
 $myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>10));
 $myPicture->setFontProperties(array("FontName"=>"fonts/pf_arma_five.ttf","FontSize"=>12));
 $myPicture->drawLineChart(array("DisplayValues"=>TRUE,"DisplayColor"=>DISPLAY_AUTO));
 $myPicture->setShadow(FALSE);


 /* Write the chart legend */
$myPicture->setFontProperties(array("FontName" => "fonts/Silkscreen.ttf", "FontSize" => 12));
 $myPicture->drawLegend($width - 210,$height - 200,array("Style"=>LEGEND_NOBORDER,"Mode"=>LEGEND_VERTICAL));


/* Render the picture (choose the best way) */
$myPicture->autoOutput("pictures/example.drawBarChart.png");
