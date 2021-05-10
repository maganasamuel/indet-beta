<?php // content="text/plain; charset=utf-8"
require_once('libs/Chart.helper.php');

$chartHelper = new ChartHelper();

$chart_data = new stdClass();
$chart_data->points_array = array(
    "Submissions" => array(-4, 0, 0, 12, 8, 3),
    "Issued Policies" => array(3, 12, 15, 8, 5, -5),
    "Cancellations" => array(2, 7, 5, 18, 19, 22)
);

$chart_data->ticks_array = array(
    "Issued Policies" => 4
);

$chart_data->weights_array = array(
    "Cancellations" => 2
);

$chart_data->axis_names_array = array(
    0 => "Deals per Month"
);

$chart_data->xLabels = array("Jan", "Feb", "Mar", "Apr", "May", "Jun");
$chart_data->xLabelName = "Months";
$chart_data->graphTitle = "Deals Data";


$chartHelper->GenerateLineChart($chart_data, "Test");
