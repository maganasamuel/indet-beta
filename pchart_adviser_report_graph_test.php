<?php // content="text/plain; charset=utf-8"
require_once('libs/Chart.helper.php');

$chartHelper = new ChartHelper();

$chart_data = new stdClass();
//$chart_data->bar_chart = new stdClass();
$chart_data->bar_chart = new stdClass();
$chart_data->pie_chart = new stdClass();
$chart_data->pie_chart2 = new stdClass();
$chart_data->pie_chart3 = new stdClass();
$chart_data->line_chart = new stdClass();
$chart_data->line_chart2 = new stdClass();
$chart_data->line_chart3 = new stdClass();
$chart_data->line_chart4 = new stdClass();


$maroon = array(150, 50, 50);
$blue_green = array(0, 75, 0);
$dark_blue = array(0, 0, 150);
$violet = array(150, 50, 150);
$gray = array(100, 100, 100);


//Bar Chart
$chart_data->bar_chart->points_array = array(
    "Assigned Leads" => array(30),
    "Submitted Deals" => array(10),
    "Issued Deals" => array(5),
    "Cancelled Deals" => array(7),
    "KiwiSaver Deals" => array(3)
);


$chart_data->bar_chart->xLabels = array("Number of Leads");
$chart_data->bar_chart->xLabelName = "Leads per Lead Generator";

$chart_data->bar_chart->colors_array = array(
    "Assigned Leads" => $violet,
    "Issued Deals" => $blue_green,
    "Submitted Deals" => $dark_blue,
    "Cancelled Deals" => $maroon,
    "KiwiSaver Deals" => $gray
);


//Pie Chart
$chart_data->pie_chart->points_array = array(
    "BDM Leads" => array(30, 10, 0)
);

$chart_data->pie_chart->xLabels = array("BDM Leads", "Tele Leads", "Self-Gen Leads");
$chart_data->pie_chart->xLabelName = "BDM Leads";

$chart_data->pie_chart->graphTitle = "Submissions %";
$chart_data->pie_chart->colors_array = array(
    "BDM Leads" => $maroon,
    "Tele Leads" => $dark_blue,
    "Self-Gen Leads" => $blue_green
);


//Pie Chart 2
$chart_data->pie_chart2->xLabels = array("BDM Leads", "Tele Leads", "Self-Gen Leads");
$chart_data->pie_chart2->graphTitle = "Issued Leads %";
$chart_data->pie_chart2->points_array = array(
    "BDM Leads" => array(100, 80, 30)
);

//Pie Chart 3
$chart_data->pie_chart3->graphTitle = "Issued Leads %";
$chart_data->pie_chart3->points_array = array(
    "BDM Leads" => array(4, 6, 5)
);


//Line Chart
$chart_data->line_chart->points_array = array(
    "Submissions" => array(-4, 0, 0, 12, 8, 3),
    "Issued Policies" => array(3, 12, 15, 8, 5, -5),
    "Cancellations" => array(2, 7, 5, 18, 19, 22),
    "KiwiSavers" => array(5, 10, 9, 25, 39, 52)
);

$chart_data->line_chart->ticks_array = array(
    "Cancellations" => 4
);

$chart_data->line_chart->colors_array = array(
    "Submissions" => $dark_blue,
    "Issued Policies" => $blue_green,
    "Cancellations" => $maroon,
    "KiwiSavers" => $gray
);

$chart_data->line_chart->weights_array = array(
    "Submissions" => 2,
    "Issued Policies" => 3,
    "Cancellations" => 1,
    "KiwiSavers" => 1.5
);

$chart_data->line_chart->axis_names_array = array(
    0 => "Deals per Month"
);


$chart_data->line_chart->xLabels = array("Jan", "Feb", "Mar", "Apr", "May", "Jun");
$chart_data->line_chart->xLabelName = "Months";
$chart_data->line_chart->graphTitle = "Deals Data";


//Line Chart2
$chart_data->line_chart2->points_array = array(
    "Submissions" => array(1000, 1357, 5250, 12000, 5300, 400),
    "Issued Policies" => array(500, 1000, 1255, 1350, 5000, 4750),
    "Cancellations" => array(0, 1350, 0, 2000, 1500, 0),
    "KiwiSavers" => array(500, 150, 300, 200, 1500, 0)
);

$chart_data->line_chart2->ticks_array = array(
    "Cancellations" => 4
);

$chart_data->line_chart2->colors_array = $chart_data->line_chart->colors_array;

$chart_data->line_chart2->weights_array = array(
    "Submissions" => 2,
    "Issued Policies" => 3,
    "Cancellations" => 1,
    "KiwiSavers" => 1.5
);

$chart_data->line_chart2->axis_names_array = array(
    0 => "API per Month"
);


$chart_data->line_chart2->xLabels = array("Jan", "Feb", "Mar", "Apr", "May", "Jun");
$chart_data->line_chart2->xLabelName = "Months";
$chart_data->line_chart2->graphTitle = "API Data";

//Line chart 3

$chart_data->line_chart3->points_array = array(
    "Percentages" => array(57, 30, 10, 23, 35, 40)
);

$chart_data->line_chart3->colors_array = array(
    "Percentages" => $maroon
);

$chart_data->line_chart3->weights_array = array(
    "Percentages" => 1
);

$chart_data->line_chart3->axis_names_array = array(
    0 => "Percentages"
);


$chart_data->line_chart3->xLabels = array("Jan", "Feb", "Mar", "Apr", "May", "Jun");
$chart_data->line_chart3->xLabelName = "Months";
$chart_data->line_chart3->graphTitle = "Cancellation Rate";

//Line Chart4
$chart_data->line_chart4->points_array = array(
    "Proficiency" => array(500, 150, 300, 200, 1500, 300)
);

$chart_data->line_chart4->colors_array = array(
    "Proficiency" => $blue_green
);

$chart_data->line_chart4->weights_array = array(
    "Proficiency" => 3
);

$chart_data->line_chart4->axis_names_array = array(
    0 => "Adviser Proficiency"
);


$chart_data->line_chart4->xLabels = array("Jan", "Feb", "Mar", "Apr", "May", "Jun");
$chart_data->line_chart4->xLabelName = "Months";
$chart_data->line_chart4->graphTitle = "Proficiency";


$filename = "files/adviser_report_deals_" . md5(uniqid()) . ".png";

$chartHelper->GenerateCustomMixedChartForAdviserReport($chart_data);
