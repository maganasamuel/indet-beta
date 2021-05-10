<?php // content="text/plain; charset=utf-8"
require_once('libs/plugins/pChart2.1.4/class/pData.class.php');
require_once('libs/plugins/pChart2.1.4/class/pDraw.class.php');
require_once('libs/plugins/pChart2.1.4/class/pPie.class.php');
require_once('libs/plugins/pChart2.1.4/class/pImage.class.php');

class ChartHelper
{

    /**
        @desc: Creates a Chart
        @params:
            chart_data: the chart data in object form 
                chart_data needs the following attributes:
                    points_array: an array of float numbers which will hold the points that will be plotted into the graph
                    ticks_array: an array of integer numbers which will specify the number of ticks for the specified line
                    weights_array: an array of integer numbers which will specify the thickness of the lines
                    axis_names_array: an array of strings which will specify the labels of the axises (Important, have the keys or indices as 0 and 1 only)
                    xLabels: an array of strings which will specify the labels for the x Axis
                    xLabelName: a string that will specify the description of the label
                    graphTitle: a string that will be displayed above the graph

            title: the chart title
            filename: the file name for the chart to be outputted on (Defaults to 0)
            width: the chart width
            height: the chart height
     */
    public function GenerateLineChart($chart_data, $title = "Chart", $filename = "", $addBorders = true, $width = 1000, $height = 330)
    {

        $MyData = new pData();

        //Set points
        if (isset($chart_data->points_array)) {
            foreach ($chart_data->points_array as $key => $points) {
                $MyData->addPoints($points, $key);
            }
        }

        //Set Line ticks
        if (isset($chart_data->ticks_array)) {
            foreach ($chart_data->ticks_array as $key => $tick) {
                //Make sure tick is at least 1
                $tick = ($tick == 0) ? 1 : $tick;

                $MyData->setSerieTicks($key, $tick);
            }
        }

        //Set Line weights
        if (isset($chart_data->weights_array)) {
            foreach ($chart_data->weights_array as $key => $weight) {
                $MyData->setSerieWeight($key, $weight);
            }
        }

        //Set Line colors
        //Color must be an array with R, G, and B Keys array("R"=>229,"G"=>11,"B"=>11);
        if (isset($chart_data->colors_array)) {
            foreach ($chart_data->colors_array as $key => $color) {
                if (!isset($color["R"]) || !isset($color["G"]) || !isset($color["B"])) {
                    $color["R"] = $color[0];
                    $color["G"] = $color[1];
                    $color["B"] = $color[2];
                }

                $MyData->setPalette($key, $color);
            }
        }

        //Set Axis names
        foreach ($chart_data->axis_names_array as $index => $axis_name) {
            $MyData->setAxisName($index, $axis_name);
        }

        $MyData->addPoints($chart_data->xLabels, "Labels");
        $MyData->setSerieDescription("Labels", $chart_data->xLabelName);
        $MyData->setAbscissa("Labels");

        /* Create the pChart object */
        $myPicture = new pImage($width, $height, $MyData);

        /* Draw the background */
        $Settings = array("R" => 170, "G" => 183, "B" => 87, "Dash" => 1, "DashR" => 190, "DashG" => 203, "DashB" => 107);
        $myPicture->drawFilledRectangle(0, 0, $width, $height, $Settings);

        /* Overlay with a gradient */
        //$Settings = array("StartR" => 129, "StartG" => 129, "StartB" => 184, "EndR" => 225, "EndG" => 225, "EndB" => 225, "Alpha" => 50);

        $Settings = array("StartR" => 219, "StartG" => 231, "StartB" => 139, "EndR" => 1, "EndG" => 138, "EndB" => 68, "Alpha" => 50);
        $myPicture->drawGradientArea(0, 0, $width, $height, DIRECTION_VERTICAL, $Settings);
        $myPicture->drawGradientArea(0, 0, $width, 20, DIRECTION_VERTICAL, array("StartR" => 0, "StartG" => 0, "StartB" => 0, "EndR" => 50, "EndG" => 50, "EndB" => 50, "Alpha" => 80));

        $color_dark = array(0, 50, 204);
        $color_light = array(62, 175, 255);
        $myPicture->drawGradientArea(0, 0, $width, $height, DIRECTION_VERTICAL, array("StartR" => $color_light[0], "StartG" => $color_light[1], "StartB" => $color_light[2], "EndR" => $color_dark[0], "EndG" => $color_dark[1], "EndB" => $color_dark[2], "Alpha" => 100));
        $myPicture->drawGradientArea(0, 0, $width, $height, DIRECTION_HORIZONTAL, array("StartR" => $color_light[0], "StartG" => $color_light[1], "StartB" => $color_light[2], "EndR" => $color_dark[0], "EndG" => $color_dark[1], "EndB" => $color_dark[2], "Alpha" => 20));

        /* Add a border to the picture */
        if ($addBorders)
            $myPicture->drawRectangle(0, 0, $width - 1, $height - 1, array("R" => 0, "G" => 0, "B" => 0));

        /* Write the picture title */
        $myPicture->setFontProperties(array("FontName" => "fonts/Silkscreen.ttf", "FontSize" => 6));
        $myPicture->drawText(10, 13, $title, array("R" => 255, "G" => 255, "B" => 255));

        /* Write the chart title */
        $myPicture->setFontProperties(array("FontName" => "fonts/Forgotte.ttf", "FontSize" => 11));
        $myPicture->drawText($width / 2, 55, $chart_data->graphTitle, array("FontSize" => 20, "Align" => TEXT_ALIGN_BOTTOMMIDDLE));

        /* Draw the scale and the 1st chart */
        $myPicture->setGraphArea(60, 60, $width - 30, $height - 40);
        $myPicture->drawFilledRectangle(60, 60, $width - 30, $height - 40, array("R" => 255, "G" => 255, "B" => 255, "Surrounding" => -200, "Alpha" => 10));
        $myPicture->drawScale(array("DrawSubTicks" => TRUE));
        $myPicture->setShadow(TRUE, array("X" => 1, "Y" => 1, "R" => 0, "G" => 0, "B" => 0, "Alpha" => 10));
        $myPicture->setFontProperties(array("FontName" => "fonts/verdana.ttf", "FontSize" => 13));
        $myPicture->drawLineChart(array("DisplayValues" => TRUE, "DisplayColor" => DISPLAY_AUTO));
        $myPicture->setShadow(FALSE);

        // Write the chart legend
        $myPicture->setFontProperties(array("FontName" => "fonts/verdana.ttf", "FontSize" => 15));
        $myPicture->drawLegend($width / 4, $height - 15, array("Style" => LEGEND_NOBORDER, "Mode" => LEGEND_HORIZONTAL));

        /* Render the picture (choose the best way) */
        if ($filename == "")
            $myPicture->autoOutput("Report");
        else
            $myPicture->render($filename);
    }

    /**
        @desc: Creates a Chart
        @params:
            chart_data: the chart data in object form 
                chart_data needs the following attributes for each pie, line, and bar graphs:
                    points_array: an array of float numbers which will hold the points that will be plotted into the graph
                    ticks_array: an array of integer numbers which will specify the number of ticks for the specified line
                    weights_array: an array of integer numbers which will specify the thickness of the lines
                    axis_names_array: an array of strings which will specify the labels of the axises (Important, have the keys or indices as 0 and 1 only)
                    xLabels: an array of strings which will specify the labels for the x Axis
                    xLabelName: a string that will specify the description of the label
                    graphTitle: a string that will be displayed above the graph

            title: the chart title
            filename: the file name for the chart to be outputted on (Defaults to 0)
            width: the chart width
            height: the chart height
     */
    public function GenerateCustomMixedChartForLeadGeneratorReport($chart_data, $filename = "", $addBorders = true, $width = 1000, $height = 500)
    {
        /* Create and populate the pData object */
        $MyData = new pData();

        //Set points
        if (isset($chart_data->bar_chart->points_array)) {
            foreach ($chart_data->bar_chart->points_array as $key => $points) {
                $MyData->addPoints($points, $key);
            }
        }

        //Set Line ticks
        if (isset($chart_data->bar_chart->ticks_array)) {
            foreach ($chart_data->bar_chart->ticks_array as $key => $tick) {
                //Make sure tick is at least 1
                $tick = ($tick == 0) ? 1 : $tick;

                $MyData->setSerieTicks($key, $tick);
            }
        }

        //Set Line weights
        if (isset($chart_data->bar_chart->weights_array)) {
            foreach ($chart_data->bar_chart->weights_array as $key => $weight) {
                $MyData->setSerieWeight($key, $weight);
            }
        }

        //Set Line colors
        //Color must be an array with R, G, and B Keys array("R"=>229,"G"=>11,"B"=>11);
        if (isset($chart_data->bar_chart->colors_array)) {
            foreach ($chart_data->bar_chart->colors_array as $key => $color) {
                if (!isset($color["R"]) || !isset($color["G"]) || !isset($color["B"])) {
                    $color["R"] = $color[0];
                    $color["G"] = $color[1];
                    $color["B"] = $color[2];
                }
                $MyData->setPalette($key, $color);
            }
        }

        //Set Axis names
        if (isset($chart_data->bar_chart->axis_names_array)) {
            foreach ($chart_data->bar_chart->axis_names_array as $index => $axis_name) {
                $MyData->setAxisName($index, $axis_name);
            }
        }

        $MyData->addPoints($chart_data->bar_chart->xLabels, "Labels");
        $MyData->setSerieDescription("Labels", $chart_data->bar_chart->xLabelName);
        $MyData->setAbscissa("Labels");

        /*
            $MyData->addPoints(array(365), "Leads Required");
            $MyData->addPoints(array(285), "Leads");
            $MyData->setSerieTicks("Leads", 4);
            $MyData->setAxisName(0, "Leads");
            $MyData->addPoints(array("Leads"), "Labels");
            $MyData->setSerieDescription("Labels", "Leads");
            $MyData->setAbscissa("Labels");
        */

        /* Create the pChart object */
        $myPicture = new pImage($width, $height, $MyData);

        $color_dark = array(0, 50, 204);
        $color_light = array(62, 175, 255);

        /* Draw the background */
        $Settings = array("R" => 170, "G" => 183, "B" => 87, "Dash" => 1, "DashR" => 190, "DashG" => 203, "DashB" => 107);
        $myPicture->drawFilledRectangle(0, 0, $width, $height, $Settings);

        /* Overlay with a gradient */
        $Settings = array("StartR" => 219, "StartG" => 231, "StartB" => 139, "EndR" => 1, "EndG" => 138, "EndB" => 68, "Alpha" => 50);

        /* Add a border to the picture */
        $myPicture->drawRectangle(0, 0, $width - 1, $height - 1, array("R" => 0, "G" => 0, "B" => 0));

        /* Write the chart title */
        $myPicture->setFontProperties(array("FontName" => "fonts/Forgotte.ttf", "FontSize" => 11));

        //Set image bg
        $myPicture->drawGradientArea(0, 0, $width, $height, DIRECTION_VERTICAL, array("StartR" => $color_light[0], "StartG" => $color_light[1], "StartB" => $color_light[2], "EndR" => $color_dark[0], "EndG" => $color_dark[1], "EndB" => $color_dark[2], "Alpha" => 100));
        $myPicture->drawGradientArea(0, 0, $width, $height, DIRECTION_HORIZONTAL, array("StartR" => $color_light[0], "StartG" => $color_light[1], "StartB" => $color_light[2], "EndR" => $color_dark[0], "EndG" => $color_dark[1], "EndB" => $color_dark[2], "Alpha" => 20));

        //Draw graph bg
        $myPicture->drawFilledRectangle(30, 30, $width - 30, $height - 30, array("R" => 255, "G" => 255, "B" => 255, "Surrounding" => -200, "Alpha" => 10));


        /* Draw the scale and the 1st chart */
        $myPicture->setGraphArea($width / 2, 60, $width - 220, $height / 2 - 30);
        $myPicture->drawScale(array("DrawSubTicks" => TRUE, "Mode" => SCALE_MODE_START0));
        $myPicture->setShadow(TRUE, array("X" => 1, "Y" => 1, "R" => 0, "G" => 0, "B" => 0, "Alpha" => 10));
        $myPicture->setFontProperties(array("FontName" => "../fonts/pf_arma_five.ttf", "FontSize" => 12));
        $myPicture->drawBarChart(array("DisplayValues" => TRUE, "DisplayColor" => DISPLAY_AUTO, "Rounded" => TRUE, "Surrounding" => 30));
        $myPicture->setShadow(FALSE);

        /* Write the chart legend */
        $myPicture->setFontProperties(array("FontName" => "fonts/pf_arma_five.ttf", "FontSize" => 10, "R" => 0, "G" => 0, "B" => 0));
        $myPicture->drawLegend($width - 210, $height / 4, array("Style" => LEGEND_NOBORDER, "Mode" => LEGEND_VERTICAL));

        //End of Bar Chart

        $pieData = new pData();

        //Set points
        if (isset($chart_data->pie_chart->points_array)) {
            foreach ($chart_data->pie_chart->points_array as $key => $points) {
                $pieData->addPoints($points, $key);
            }
        }


        $MyData->addPoints($chart_data->pie_chart->xLabels, "Labels");

        $pieData->setAbscissa("Labels");

        /*
        $pieData->addPoints($chart_data->pie_chart->xLabels, "Labels");
        $pieData->setSerieDescription("Labels", $chart_data->pie_chart->xLabelName);
        $pieData->setAbscissa("Labels");
        $pieData->addPoints(array(285, 15), "Leads");
        $pieData->setSerieDescription("ScoreA", "Application A");
        $pieData->addPoints(array("Non-Issued Leads", "Issued Leads"), "Labels");
        $pieData->setAbscissa("Labels");
        */


        $pieData->setSerieDescription("ScoreA", "Application A");

        /* Define the absissa serie */
        $pieData->addPoints($chart_data->pie_chart->xLabels, "Labels");
        $pieData->setAbscissa("Labels");

        /* Create the pPie object */
        $PieChart = new pPie($myPicture, $pieData);

        //Set Line colors
        //Color must be an array with R, G, and B Keys array("R"=>229,"G"=>11,"B"=>11);
        if (isset($chart_data->pie_chart->colors_array)) {
            $ctr = 0;
            foreach ($chart_data->pie_chart->colors_array as $key => $color) {
                if (!isset($color["R"]) || !isset($color["G"]) || !isset($color["B"])) {
                    $color["R"] = $color[0];
                    $color["G"] = $color[1];
                    $color["B"] = $color[2];
                }
                $PieChart->setSliceColor($ctr, $color);
                $ctr++;
            }
        }


        $myPicture->setFontProperties(array("FontName" => "fonts/pf_arma_five.ttf", "FontSize" => 10, "R" => 0, "G" => 0, "B" => 0));
        $myPicture->setShadow(FALSE, array("X" => 3, "Y" => 3, "R" => 0, "G" => 0, "B" => 0, "Alpha" => 10));
        $PieChart->draw2DPie($width / 4 - $width / 20, $height / 2 - $height / 5, array("Radius" => 60, "Border" => TRUE, "WriteValues" => PIE_VALUE_PERCENTAGE, "ValuePosition" => PIE_VALUE_OUTSIDE, "ValueR" => 0, "ValueG" => 0, "ValueB" => 0,));

        $PieChart->drawPieLegend($width / 4 + $width / 15, $height / 2 - $height / 4, array("Style" => LEGEND_NOBORDER, "Mode" => LEGEND_VERTICAL,));
        /*
            $PieChart->draw2DPie($width / 4, $height / 4, array(
                "Radius" => 80, "DrawLabels" => TRUE,
                "DataGapRadius" => 6, "Border" => TRUE
            ));
        */
        $myPicture->setFontProperties(array("FontName" => "fonts/pf_arma_five.ttf", "FontSize" => 10));
        /* Draw two AA pie chart */

        $myPicture->setShadow(TRUE, array("X" => 3, "Y" => 3, "R" => 0, "G" => 0, "B" => 0, "Alpha" => 10));

        $myPicture->setFontProperties(array("FontName" => "fonts/pf_arma_five.ttf", "FontSize" => 7));
        //$PieChart->drawPieLegend($width / 15, $height / 2 - 30, array("Style" => LEGEND_BOX, "Mode" => LEGEND_HORIZONTAL));

        $myPicture->setFontProperties(array("FontName" => "fonts/pf_arma_five.ttf", "FontSize" => 10, "R" => 0, "G" => 0, "B" => 0));
        //End of Pie Chart

        //Set points
        if (isset($chart_data->line_chart->points_array)) {
            foreach ($chart_data->line_chart->points_array as $key => $points) {
                $MyData->addPoints($points, $key);
            }
        }

        //Remove from labels
        if (isset($chart_data->bar_chart->points_array)) {
            foreach ($chart_data->bar_chart->points_array as $key => $points) {
                $MyData->removeFromLabels($key);
            }
        }

        //Remove from labels
        if (isset($chart_data->pie_chart->xLabels)) {
            foreach ($chart_data->pie_chart->xLabels as $key => $points) {
                $MyData->removeFromLabels($points);
            }
        }

        //Remove from data
        if (isset($chart_data->bar_chart->points_array)) {
            foreach ($chart_data->bar_chart->points_array as $key => $points) {
                $MyData->removeSerie($key);
            }
        }

        //Remove from data
        if (isset($chart_data->pie_chart->points_array)) {
            foreach ($chart_data->pie_chart->points_array as $key => $points) {
                $MyData->removeSerie($key);
            }
        }

        //Remove bar graph keys
        if (isset($chart_data->bar_chart->points_array)) {
            foreach ($chart_data->bar_chart->points_array as $key => $points) {
                $MyData->setSerieDrawable($key, FALSE);
            }
        }

        //Set Line ticks
        if (isset($chart_data->line_chart->ticks_array)) {
            foreach ($chart_data->line_chart->ticks_array as $key => $tick) {
                //Make sure tick is at least 1
                $tick = ($tick == 0) ? 1 : $tick;
                $MyData->setSerieTicks($key, $tick);
            }
        }

        //Set Line weights
        if (isset($chart_data->line_chart->weights_array)) {
            foreach ($chart_data->line_chart->weights_array as $key => $weight) {
                $MyData->setSerieWeight($key, $weight);
            }
        }

        //Set Line colors
        //Color must be an array with R, G, and B Keys array("R"=>229,"G"=>11,"B"=>11);
        if (isset($chart_data->line_chart->colors_array)) {
            foreach ($chart_data->line_chart->colors_array as $key => $color) {
                if (!isset($color["R"]) || !isset($color["G"]) || !isset($color["B"])) {
                    $color["R"] = $color[0];
                    $color["G"] = $color[1];
                    $color["B"] = $color[2];
                }
                $MyData->setPalette($key, $color);
            }
        }

        $MyData->setSerieWeight("Leads Cancelled", 1);
        $MyData->setSerieWeight("Leads Generated", 3);
        $MyData->setPalette("Leads Cancelled", array("R" => 200, "G" => 50, "B" => 50));
        $MyData->setPalette("Leads Generated", array("R" => 100, "G" => 200, "B" => 100));

        //Set Axis names
        foreach ($chart_data->line_chart->axis_names_array as $index => $axis_name) {
            $MyData->setAxisName($index, $axis_name);
        }

        $MyData->addPoints($chart_data->line_chart->xLabels, "Labels");
        $MyData->setSerieDescription("Labels", $chart_data->line_chart->xLabelName);
        $MyData->setAbscissa("Labels");

        /* Create and populate the pData object line */
        /*
        $MyData->addPoints(array(-4, 0, 0, 12, 8, 3), "Probe 1");
        $MyData->addPoints(array(3, 12, 15, 8, 5, -5), "Probe 2");
        $MyData->addPoints(array(2, 7, 5, 18, 19, 22), "Probe 3");
        $MyData->setSerieTicks("Probe 2", 4);
        $MyData->setSerieWeight("Probe 3", 2);
        $MyData->setAxisName(0, "Temperatures");
        $MyData->setSerieDescription("Labels", "Months");
        $MyData->setAbscissa("Labels");
        $MyData->addPoints(array("Jan", "Feb", "Mar", "Apr", "May", "Jun"), "Labels");
        */


        /* Write the chart title */
        $myPicture->setFontProperties(array("FontName" => "fonts/Forgotte.ttf", "FontSize" => 11));

        /* Draw the scale and the 1st chart */
        $myPicture->setGraphArea(90, $height / 2, $width - 220, $height - 60);
        $myPicture->drawScale(array("DrawSubTicks" => TRUE));
        $myPicture->setShadow(TRUE, array("X" => 1, "Y" => 1, "R" => 0, "G" => 0, "B" => 0, "Alpha" => 10));
        $myPicture->setFontProperties(array("FontName" => "fonts/pf_arma_five.ttf", "FontSize" => 12));
        $myPicture->drawLineChart(array("DisplayValues" => TRUE, "DisplayColor" => DISPLAY_AUTO));
        $myPicture->setShadow(FALSE);


        /* Write the chart legend */
        $myPicture->setFontProperties(array("FontName" => "fonts/pf_arma_five.ttf", "FontSize" => 10, "R" => 0, "G" => 0, "B" => 0));
        $myPicture->drawLegend($width - 210, $height - 200, array("Style" => LEGEND_NOBORDER, "Mode" => LEGEND_VERTICAL));
        /* Render the picture (choose the best way)
             */
        if ($filename == "")
            $myPicture->autoOutput("Report");
        else
            $myPicture->render($filename);
    }



    /**
        @desc: Creates a Chart
        @params:
            chart_data: the chart data in object form 
                chart_data needs the following attributes for each pie, line, and bar graphs:
                    points_array: an array of float numbers which will hold the points that will be plotted into the graph
                    ticks_array: an array of integer numbers which will specify the number of ticks for the specified line
                    weights_array: an array of integer numbers which will specify the thickness of the lines
                    axis_names_array: an array of strings which will specify the labels of the axises (Important, have the keys or indices as 0 and 1 only)
                    xLabels: an array of strings which will specify the labels for the x Axis
                    xLabelName: a string that will specify the description of the label
                    graphTitle: a string that will be displayed above the graph

            title: the chart title
            filename: the file name for the chart to be outputted on (Defaults to 0)
            width: the chart width
            height: the chart height
     */
    public function GenerateCustomMixedChartForAdviserReport($chart_data, $filename = "", $addBorders = true, $width = 1000, $height = 900)
    {
        /* Create and populate the pData object */
        $MyData = new pData();


        /*
            $MyData->addPoints(array(365), "Leads Required");
            $MyData->addPoints(array(285), "Leads");
            $MyData->setSerieTicks("Leads", 4);
            $MyData->setAxisName(0, "Leads");
            $MyData->addPoints(array("Leads"), "Labels");
            $MyData->setSerieDescription("Labels", "Leads");
            $MyData->setAbscissa("Labels");
        */

        /* Create the pChart object */
        $myPicture = new pImage($width, $height, $MyData);

        $color_dark = array(0, 50, 204);
        $color_light = array(62, 175, 255);

        /* Draw the background */
        $Settings = array("R" => 170, "G" => 183, "B" => 87, "Dash" => 1, "DashR" => 190, "DashG" => 203, "DashB" => 107);
        $myPicture->drawFilledRectangle(0, 0, $width, $height, $Settings);

        /* Overlay with a gradient */
        $Settings = array("StartR" => 219, "StartG" => 231, "StartB" => 139, "EndR" => 1, "EndG" => 138, "EndB" => 68, "Alpha" => 50);

        /* Add a border to the picture */
        $myPicture->drawRectangle(0, 0, $width - 1, $height - 1, array("R" => 0, "G" => 0, "B" => 0));

        /* Write the chart title */
        $myPicture->setFontProperties(array("FontName" => "fonts/Forgotte.ttf", "FontSize" => 11));

        //Set image bg
        $myPicture->drawGradientArea(0, 0, $width, $height, DIRECTION_VERTICAL, array("StartR" => $color_light[0], "StartG" => $color_light[1], "StartB" => $color_light[2], "EndR" => $color_dark[0], "EndG" => $color_dark[1], "EndB" => $color_dark[2], "Alpha" => 100));
        $myPicture->drawGradientArea(0, 0, $width, $height, DIRECTION_HORIZONTAL, array("StartR" => $color_light[0], "StartG" => $color_light[1], "StartB" => $color_light[2], "EndR" => $color_dark[0], "EndG" => $color_dark[1], "EndB" => $color_dark[2], "Alpha" => 20));

        //Draw graph bg
        $myPicture->drawFilledRectangle(30, 30, $width - 30, $height - 30, array("R" => 255, "G" => 255, "B" => 255, "Surrounding" => -200, "Alpha" => 10));

        //Start of bar chart
        /*
        //Set points
        if (isset($chart_data->bar_chart->points_array)) {
            foreach ($chart_data->bar_chart->points_array as $key => $points) {
                $MyData->addPoints($points, $key);
            }
        }

        //Set Line ticks
        if (isset($chart_data->bar_chart->ticks_array)) {
            foreach ($chart_data->bar_chart->ticks_array as $key => $tick) {
                //Make sure tick is at least 1
                $tick = ($tick == 0) ? 1 : $tick;

                $MyData->setSerieTicks($key, $tick);
            }
        }

        //Set Line weights
        if (isset($chart_data->bar_chart->weights_array)) {
            foreach ($chart_data->bar_chart->weights_array as $key => $weight) {
                $MyData->setSerieWeight($key, $weight);
            }
        }

        //Set Line colors
        //Color must be an array with R, G, and B Keys array("R"=>229,"G"=>11,"B"=>11);
        if (isset($chart_data->bar_chart->colors_array)) {
            foreach ($chart_data->bar_chart->colors_array as $key => $color) {
                if (!isset($color["R"]) || !isset($color["G"]) || !isset($color["B"])) {
                    $color["R"] = $color[0];
                    $color["G"] = $color[1];
                    $color["B"] = $color[2];
                }
                $MyData->setPalette($key, $color);
            }
        }

        //Set Axis names
        if (isset($chart_data->bar_chart->axis_names_array)) {
            foreach ($chart_data->bar_chart->axis_names_array as $index => $axis_name) {
                $MyData->setAxisName($index, $axis_name);
            }
        }

        $MyData->addPoints($chart_data->bar_chart->xLabels, "Labels");
        $MyData->setSerieDescription("Labels", $chart_data->bar_chart->xLabelName);
        $MyData->setAbscissa("Labels");

        // Draw the scale and the 1st chart 
        $myPicture->setGraphArea($width / 2 + $width / 20, 60, $width - 220, $height / 3 - $height / 20);
        $myPicture->drawScale(array("DrawSubTicks" => TRUE, "Mode" => SCALE_MODE_START0));
        $myPicture->setShadow(TRUE, array("X" => 1, "Y" => 1, "R" => 0, "G" => 0, "B" => 0, "Alpha" => 10));
        $myPicture->setFontProperties(array("FontName" => "../fonts/pf_arma_five.ttf", "FontSize" => 12));
        $myPicture->drawBarChart(array("DisplayValues" => TRUE, "DisplayColor" => DISPLAY_AUTO, "Rounded" => TRUE, "Surrounding" => 30));
        $myPicture->setShadow(FALSE);

        // Write the chart legend
        $myPicture->setFontProperties(array("FontName" => "fonts/pf_arma_five.ttf", "FontSize" => 10, "R" => 0, "G" => 0, "B" => 0));
        $myPicture->drawLegend($width - 200, $height / 6, array("Style" => LEGEND_NOBORDER, "Mode" => LEGEND_VERTICAL));
        */
        //End of Bar Chart

        $pieData = new pData();

        //Set points
        if (isset($chart_data->pie_chart->points_array)) {
            foreach ($chart_data->pie_chart->points_array as $key => $points) {
                $pieData->addPoints($points, $key);
            }
        }


        $MyData->addPoints($chart_data->pie_chart->xLabels, "Labels");

        $pieData->setAbscissa("Labels");

        /*
        $pieData->addPoints($chart_data->pie_chart->xLabels, "Labels");
        $pieData->setSerieDescription("Labels", $chart_data->pie_chart->xLabelName);
        $pieData->setAbscissa("Labels");
        $pieData->addPoints(array(285, 15), "Leads");
        $pieData->setSerieDescription("ScoreA", "Application A");
        $pieData->addPoints(array("Non-Issued Leads", "Issued Leads"), "Labels");
        $pieData->setAbscissa("Labels");
        */


        $pieData->setSerieDescription("ScoreA", "Application A");

        /* Define the absissa serie */
        $pieData->addPoints($chart_data->pie_chart->xLabels, "Labels");
        $pieData->setAbscissa("Labels");

        /* Create the pPie object */
        $PieChart = new pPie($myPicture, $pieData);

        //Set Line colors
        //Color must be an array with R, G, and B Keys array("R"=>229,"G"=>11,"B"=>11);
        if (isset($chart_data->pie_chart->colors_array)) {
            $ctr = 0;
            foreach ($chart_data->pie_chart->colors_array as $key => $color) {
                if (!isset($color["R"]) || !isset($color["G"]) || !isset($color["B"])) {
                    $color["R"] = $color[0];
                    $color["G"] = $color[1];
                    $color["B"] = $color[2];
                }
                $PieChart->setSliceColor($ctr, $color);
                $ctr++;
            }
        }


        $myPicture->setFontProperties(array("FontName" => "fonts/pf_arma_five.ttf", "FontSize" => 10, "R" => 0, "G" => 0, "B" => 0));
        $myPicture->setShadow(FALSE, array("X" => 3, "Y" => 3, "R" => 0, "G" => 0, "B" => 0, "Alpha" => 10));
        $PieChart->draw2DPie($width / 5, $height / 3 -  $height / 6.4, array("Radius" => 80, "Border" => TRUE, "WriteValues" => PIE_VALUE_PERCENTAGE, "ValuePosition" => PIE_VALUE_OUTSIDE, "ValueR" => 0, "ValueG" => 0, "ValueB" => 0,));

        $myPicture->setFontProperties(array("FontName" => "fonts/pf_arma_five.ttf", "FontSize" => 9));

        $PieChart->drawPieLegend($width / 2.7, $height / 3 - $height / 30, array("Style" => LEGEND_NOBORDER, "Mode" => LEGEND_HORIZONTAL));

        $myPicture->setFontProperties(array("FontName" => "fonts/pf_arma_five.ttf", "FontSize" => 10));
        /* Draw two AA pie chart */

        $myPicture->setShadow(TRUE, array("X" => 3, "Y" => 3, "R" => 0, "G" => 0, "B" => 0, "Alpha" => 10));

        $myPicture->setFontProperties(array("FontName" => "fonts/pf_arma_five.ttf", "FontSize" => 7));
        //$PieChart->drawPieLegend($width / 15, $height / 2 - 30, array("Style" => LEGEND_BOX, "Mode" => LEGEND_HORIZONTAL));

        $myPicture->setFontProperties(array("FontName" => "fonts/pf_arma_five.ttf", "FontSize" => 10, "R" => 0, "G" => 0, "B" => 0));
        $myPicture->drawText($width / 5, $height / 3 -  $height / 3.7, $chart_data->pie_chart->graphTitle, array("FontSize" => 14, "Align" => TEXT_ALIGN_BOTTOMMIDDLE));
        //End of Pie Chart

        //Start of Pie Chart2
        if (isset($chart_data->pie_chart2)) {

            $pieData2 = new pData();

            //Set points
            if (isset($chart_data->pie_chart2->points_array)) {
                foreach ($chart_data->pie_chart2->points_array as $key => $points) {
                    $pieData2->addPoints($points, $key);
                }
            }


            $MyData->addPoints($chart_data->pie_chart->xLabels, "Labels");

            $pieData2->setAbscissa("Labels");

            /*
        $pieData2->addPoints($chart_data->pie_chart2->xLabels, "Labels");
        $pieData2->setSerieDescription("Labels", $chart_data->pie_chart2->xLabelName);
        $pieData2->setAbscissa("Labels");
        $pieData2->addPoints(array(285, 15), "Leads");
        $pieData2->setSerieDescription("ScoreA", "Application A");
        $pieData2->addPoints(array("Non-Issued Leads", "Issued Leads"), "Labels");
        $pieData2->setAbscissa("Labels");
        */


            /* Define the absissa serie */
            $pieData2->addPoints($chart_data->pie_chart->xLabels, "Labels");
            $pieData2->setAbscissa("Labels");

            /* Create the pPie object */
            $PieChart2 = new pPie($myPicture, $pieData2);

            //Set Line colors
            //Color must be an array with R, G, and B Keys array("R"=>229,"G"=>11,"B"=>11);
            if (isset($chart_data->pie_chart->colors_array)) {
                $ctr = 0;
                foreach ($chart_data->pie_chart->colors_array as $key => $color) {
                    if (!isset($color["R"]) || !isset($color["G"]) || !isset($color["B"])) {
                        $color["R"] = $color[0];
                        $color["G"] = $color[1];
                        $color["B"] = $color[2];
                    }
                    $PieChart2->setSliceColor($ctr, $color);
                    $ctr++;
                }
            }


            $myPicture->setFontProperties(array("FontName" => "fonts/pf_arma_five.ttf", "FontSize" => 10, "R" => 0, "G" => 0, "B" => 0));
            $myPicture->setShadow(FALSE, array("X" => 3, "Y" => 3, "R" => 0, "G" => 0, "B" => 0, "Alpha" => 10));
            $PieChart2->draw2DPie($width / 5 * 2.5, $height / 3 -  $height / 6.4, array("Radius" => 80, "Border" => TRUE, "WriteValues" => PIE_VALUE_PERCENTAGE, "ValuePosition" => PIE_VALUE_OUTSIDE, "ValueR" => 0, "ValueG" => 0, "ValueB" => 0,));

            $myPicture->setFontProperties(array("FontName" => "fonts/pf_arma_five.ttf", "FontSize" => 10));
            $myPicture->setShadow(TRUE, array("X" => 3, "Y" => 3, "R" => 0, "G" => 0, "B" => 0, "Alpha" => 10));
            $myPicture->setFontProperties(array("FontName" => "fonts/pf_arma_five.ttf", "FontSize" => 10, "R" => 0, "G" => 0, "B" => 0));
            $myPicture->drawText($width / 2, $height / 3 -  $height / 3.7, $chart_data->pie_chart2->graphTitle, array("FontSize" => 14, "Align" => TEXT_ALIGN_BOTTOMMIDDLE));
            //End of Pie Chart2
        }

        //Start of Pie Chart3
        if (isset($chart_data->pie_chart3)) {

            $pieData2 = new pData();

            //Set points
            if (isset($chart_data->pie_chart3->points_array)) {
                foreach ($chart_data->pie_chart3->points_array as $key => $points) {
                    $pieData2->addPoints($points, $key);
                }
            }


            $MyData->addPoints($chart_data->pie_chart->xLabels, "Labels");

            $pieData2->setAbscissa("Labels");

            /*
        $pieData2->addPoints($chart_data->pie_chart3->xLabels, "Labels");
        $pieData2->setSerieDescription("Labels", $chart_data->pie_chart3->xLabelName);
        $pieData2->setAbscissa("Labels");
        $pieData2->addPoints(array(285, 15), "Leads");
        $pieData2->setSerieDescription("ScoreA", "Application A");
        $pieData2->addPoints(array("Non-Issued Leads", "Issued Leads"), "Labels");
        $pieData2->setAbscissa("Labels");
        */


            /* Define the absissa serie */
            $pieData2->addPoints($chart_data->pie_chart->xLabels, "Labels");
            $pieData2->setAbscissa("Labels");

            /* Create the pPie object */
            $PieChart3 = new pPie($myPicture, $pieData2);

            //Set Line colors
            //Color must be an array with R, G, and B Keys array("R"=>229,"G"=>11,"B"=>11);
            if (isset($chart_data->pie_chart->colors_array)) {
                $ctr = 0;
                foreach ($chart_data->pie_chart->colors_array as $key => $color) {
                    if (!isset($color["R"]) || !isset($color["G"]) || !isset($color["B"])) {
                        $color["R"] = $color[0];
                        $color["G"] = $color[1];
                        $color["B"] = $color[2];
                    }
                    $PieChart3->setSliceColor($ctr, $color);
                    $ctr++;
                }
            }


            $myPicture->setFontProperties(array("FontName" => "fonts/pf_arma_five.ttf", "FontSize" => 10, "R" => 0, "G" => 0, "B" => 0));
            $myPicture->setShadow(FALSE, array("X" => 3, "Y" => 3, "R" => 0, "G" => 0, "B" => 0, "Alpha" => 10));
            $PieChart3->draw2DPie($width / 5 * 4, $height / 3 - $height / 6.4, array("Radius" => 80, "Border" => TRUE, "WriteValues" => PIE_VALUE_PERCENTAGE, "ValuePosition" => PIE_VALUE_OUTSIDE, "ValueR" => 0, "ValueG" => 0, "ValueB" => 0,));

            $myPicture->setFontProperties(array("FontName" => "fonts/pf_arma_five.ttf", "FontSize" => 10));
            $myPicture->setShadow(TRUE, array("X" => 3, "Y" => 3, "R" => 0, "G" => 0, "B" => 0, "Alpha" => 10));
            $myPicture->setFontProperties(array("FontName" => "fonts/pf_arma_five.ttf", "FontSize" => 10, "R" => 0, "G" => 0, "B" => 0));

            $myPicture->drawText($width / 1.25, $height / 3 -  $height / 3.7, $chart_data->pie_chart3->graphTitle, array("FontSize" => 14, "Align" => TEXT_ALIGN_BOTTOMMIDDLE));
        }
        //End of Pie Chart3

        //Start of Line Chart

        //Set points
        if (isset($chart_data->line_chart->points_array)) {
            foreach ($chart_data->line_chart->points_array as $key => $points) {
                $MyData->addPoints($points, $key);
            }
        }

        //Remove from labels
        if (isset($chart_data->bar_chart->points_array)) {
            foreach ($chart_data->bar_chart->points_array as $key => $points) {
                $MyData->removeFromLabels($key);
            }
        }

        //Remove from labels
        $MyData->removeFromLabels($chart_data->pie_chart->xLabels);

        //Remove from labels
        if (isset($chart_data->pie_chart->xLabels)) {
            foreach ($chart_data->pie_chart->xLabels as $key => $points) {
                $MyData->removeFromLabels($points);
            }
        }

        //Remove from labels
        if (isset($chart_data->pie_chart2->xLabels)) {
            foreach ($chart_data->pie_chart2->xLabels as $key => $points) {
                $MyData->removeFromLabels($points);
            }
        }
        //Remove from labels
        if (isset($chart_data->pie_chart3->xLabels)) {
            foreach ($chart_data->pie_chart3->xLabels as $key => $points) {
                $MyData->removeFromLabels($points);
            }
        }

        //Remove from data
        if (isset($chart_data->bar_chart->points_array)) {
            foreach ($chart_data->bar_chart->points_array as $key => $points) {
                $MyData->removeSerie($key);
            }
        }

        //Remove from data
        if (isset($chart_data->pie_chart->points_array)) {
            foreach ($chart_data->pie_chart->points_array as $key => $points) {
                $MyData->removeSerie($key);
            }
        }
        //Remove from data
        if (isset($chart_data->pie_chart2->points_array)) {
            foreach ($chart_data->pie_chart2->points_array as $key => $points) {
                $MyData->removeSerie($key);
            }
        }
        //Remove from data
        if (isset($chart_data->pie_chart3->points_array)) {
            foreach ($chart_data->pie_chart3->points_array as $key => $points) {
                $MyData->removeSerie($key);
            }
        }

        //Remove bar graph keys
        if (isset($chart_data->bar_chart->points_array)) {
            foreach ($chart_data->bar_chart->points_array as $key => $points) {
                $MyData->setSerieDrawable($key, FALSE);
            }
        }

        //Set Line ticks
        if (isset($chart_data->line_chart->ticks_array)) {
            foreach ($chart_data->line_chart->ticks_array as $key => $tick) {
                //Make sure tick is at least 1
                $tick = ($tick == 0) ? 1 : $tick;
                $MyData->setSerieTicks($key, $tick);
            }
        }

        //Set Line weights
        if (isset($chart_data->line_chart->weights_array)) {
            foreach ($chart_data->line_chart->weights_array as $key => $weight) {
                $MyData->setSerieWeight($key, $weight);
            }
        }
        //Set Line colors
        //Color must be an array with R, G, and B Keys array("R"=>229,"G"=>11,"B"=>11);
        if (isset($chart_data->line_chart->colors_array)) {
            foreach ($chart_data->line_chart->colors_array as $key => $color) {
                if (!isset($color["R"]) || !isset($color["G"]) || !isset($color["B"])) {
                    $color["R"] = $color[0];
                    $color["G"] = $color[1];
                    $color["B"] = $color[2];
                }
                $MyData->setPalette($key, $color);
            }
        }

        $MyData->setSerieWeight("Submissions", 1);
        $MyData->setSerieWeight("Issued Policies", 3);
        $MyData->setSerieWeight("KiwiSavers", 3);
        $MyData->setPalette("Leads Cancelled", array("R" => 200, "G" => 50, "B" => 50));
        $MyData->setPalette("Leads Generated", array("R" => 100, "G" => 200, "B" => 100));

        //Set Axis names
        foreach ($chart_data->line_chart->axis_names_array as $index => $axis_name) {
            $MyData->setAxisName($index, $axis_name);
        }

        $myPicture->drawText($width / 2, ($height / 3), $chart_data->line_chart->graphTitle, array("FontSize" => 13, "Align" => TEXT_ALIGN_BOTTOMMIDDLE));

        $MyData->addPoints($chart_data->line_chart->xLabels, "Labels");
        $MyData->setSerieDescription("Labels", $chart_data->line_chart->xLabelName);
        $MyData->setAbscissa("Labels");

        /* Write the chart title */
        $myPicture->setFontProperties(array("FontName" => "fonts/Forgotte.ttf", "FontSize" => 11));

        /* Draw the scale and the 1st chart */
        $myPicture->setGraphArea(100, $height / 3 + 10, $width - 220, $height / 3 + (($height / 3) * .8));
        $myPicture->drawScale(array("DrawSubTicks" => TRUE));
        $myPicture->setShadow(TRUE, array("X" => 1, "Y" => 1, "R" => 0, "G" => 0, "B" => 0, "Alpha" => 10));
        $myPicture->setFontProperties(array("FontName" => "fonts/pf_arma_five.ttf", "FontSize" => 12));
        $myPicture->drawLineChart(array("DisplayValues" => TRUE, "DisplayColor" => DISPLAY_AUTO));
        $myPicture->setShadow(FALSE);


        /* Write the chart legend */
        $myPicture->setFontProperties(array("FontName" => "fonts/pf_arma_five.ttf", "FontSize" => 10, "R" => 0, "G" => 0, "B" => 0));
        $myPicture->drawLegend($width - 210, $height / 3 + (($height / 3) / 3), array("Style" => LEGEND_NOBORDER, "Mode" => LEGEND_VERTICAL));
        //End of Line chart

        //Start of Line Chart2

        if (isset($chart_data->line_chart2)) {

            //Remove from data
            if (isset($chart_data->line_chart->points_array)) {
                foreach ($chart_data->line_chart->points_array as $key => $points) {
                    $MyData->removeSerie($key);
                }
            }

            //Set points
            if (isset($chart_data->line_chart2->points_array)) {
                foreach ($chart_data->line_chart2->points_array as $key => $points) {
                    $MyData->addPoints($points, $key);
                }
            }

            //Set Axis names
            foreach ($chart_data->line_chart2->axis_names_array as $index => $axis_name) {
                $MyData->setAxisName($index, $axis_name);
            }

            //Set Line ticks
            if (isset($chart_data->line_chart2->ticks_array)) {
                foreach ($chart_data->line_chart2->ticks_array as $key => $tick) {
                    //Make sure tick is at least 1
                    $tick = ($tick == 0) ? 1 : $tick;
                    $MyData->setSerieTicks($key, $tick);
                }
            }

            //Set Line weights
            if (isset($chart_data->line_chart2->weights_array)) {
                foreach ($chart_data->line_chart2->weights_array as $key => $weight) {
                    $MyData->setSerieWeight($key, $weight);
                }
            }
            //Set Colors
            if (isset($chart_data->line_chart2->colors_array)) {
                foreach ($chart_data->line_chart2->colors_array as $key => $color) {
                    if (!isset($color["R"]) || !isset($color["G"]) || !isset($color["B"])) {
                        $color["R"] = $color[0];
                        $color["G"] = $color[1];
                        $color["B"] = $color[2];
                    }
                    $MyData->setPalette($key, $color);
                }
            }

            $myPicture->drawText($width / 2, ($height / 3) * 2, $chart_data->line_chart2->graphTitle, array("FontSize" => 13, "Align" => TEXT_ALIGN_BOTTOMMIDDLE));

            //$MyData->addPoints($chart_data->line_chart2->xLabels, "Labels");
            $MyData->setSerieDescription("Labels", $chart_data->line_chart2->xLabelName);
            $MyData->setAbscissa("Labels");

            /* Write the chart title */
            $myPicture->setFontProperties(array("FontName" => "fonts/Forgotte.ttf", "FontSize" => 11));

            /* Draw the scale and the 1st chart */
            $myPicture->setGraphArea(100, ($height / 3 + 10) * 2, $width - 220, (($height / 3) * 2) + (($height / 3) * .8));
            $myPicture->drawScale(array("DrawSubTicks" => TRUE));
            $myPicture->setShadow(TRUE, array("X" => 1, "Y" => 1, "R" => 0, "G" => 0, "B" => 0, "Alpha" => 10));
            $myPicture->setFontProperties(array("FontName" => "fonts/pf_arma_five.ttf", "FontSize" => 12));
            $myPicture->drawLineChart(array("DisplayValues" => TRUE, "DisplayColor" => DISPLAY_AUTO));
            $myPicture->setShadow(FALSE);


            /* Write the chart legend */
            $myPicture->setFontProperties(array("FontName" => "fonts/pf_arma_five.ttf", "FontSize" => 10, "R" => 0, "G" => 0, "B" => 0));
            $myPicture->drawLegend($width - 210, (($height / 3) * 2) + (($height / 3) / 3), array("Style" => LEGEND_NOBORDER, "Mode" => LEGEND_VERTICAL));
        }
        //End of line chart2
        /* Render the picture (choose the best way)
             */

        if ($filename == "")
            $myPicture->autoOutput("Report");
        else
            $myPicture->render($filename);
    }
}
