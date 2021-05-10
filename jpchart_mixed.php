<?php // content="text/plain; charset=utf-8"
require_once('libs/Chart.helper.php');

$chartHelper = new ChartHelper();

$chart_data = new stdClass();
$chart_data->bar_chart = new stdClass();
$chart_data->pie_chart = new stdClass();
$chart_data->line_chart = new stdClass();
$chart_data->line_chart2 = new stdClass();
$chart_data->line_chart3 = new stdClass();


$maroon = array(150, 50, 50);
$blue_green = array(0, 75, 0);
$dark_blue = array(0, 0, 150);
$violet = array(150, 50, 150);
$gray = array(100, 100, 100);

//Bar Chart
$chart_data->bar_chart->points_array = array(
    "Leads Required" => array(365),
    "Leads" => array(285)
);


$chart_data->bar_chart->xLabels = array("Leads");
$chart_data->bar_chart->xLabelName = "Leads";

//Pie Chart
$chart_data->pie_chart->points_array = array(
    "Leads" => array(285, 15)
);

$chart_data->pie_chart->xLabels = array("Non-Issued Leads", "Issued Leads");
$chart_data->pie_chart->xLabelName = "Leads";

$chart_data->pie_chart->colors_array = array(
    "Non-Issued Leads" => $maroon,
    "Issued Leads" => $blue_green
);

//Line Chart
$chart_data->line_chart->points_array = array(
    "Leads Required" => array(7, 7, 7, 7, 7, 7),
    "Leads Generated" => array(3, 12, 15, 8, 5, -5),
    "Leads Cancelled" => array(2, 7, 5, 18, 19, 22)
);

$chart_data->line_chart->weights_array = array(
    "Leads Required" => 3,
    "Leads Generated" => 2.5,
    "Leads Cancelled" => 1.5  
);

$chart_data->line_chart->ticks_array = array(
    "Leads Cancelled" => 4
);

$chart_data->line_chart->colors_array = array(
    "Leads Required" => $dark_blue,
    "Leads Generated" => $blue_green,
    "Leads Cancelled" => $maroon
);

$chart_data->line_chart->axis_names_array = array(
    0 => "Deals per Month"
);

$chart_data->line_chart->xLabels = array("Jan", "Feb", "Mar", "Apr", "May", "Jun");
$chart_data->line_chart->xLabelName = "Months";
$chart_data->line_chart->graphTitle = "Deals Data";

//Line chart 3

$chart_data->line_chart2->points_array = array(
    "Percentages" => array(57, 30, 10, 23, 35, 40)
);

$chart_data->line_chart2->colors_array = array(
    "Percentages" => $maroon
);

$chart_data->line_chart2->weights_array = array(
    "Percentages" => 1
);

$chart_data->line_chart2->axis_names_array = array(
    0 => "Percentages"
);


$chart_data->line_chart2->xLabels = array("Jan", "Feb", "Mar", "Apr", "May", "Jun");
$chart_data->line_chart2->xLabelName = "Months";
$chart_data->line_chart2->graphTitle = "Cancellation Rate";

//Line Chart4
$chart_data->line_chart3->points_array = array(
    "Proficiency" => array(500, 150, 300, 200, 1500, 300)
);

$chart_data->line_chart3->colors_array = array(
    "Proficiency" => $blue_green
);

$chart_data->line_chart3->weights_array = array(
    "Proficiency" => 3
);

$chart_data->line_chart3->axis_names_array = array(
    0 => "Adviser Proficiency"
);


$chart_data->line_chart3->xLabels = array("Jan", "Feb", "Mar", "Apr", "May", "Jun");
$chart_data->line_chart3->xLabelName = "Months";
$chart_data->line_chart3->graphTitle = "Proficiency";



$chartHelper->GenerateCustomMixedChartForLeadGeneratorReport($chart_data);
