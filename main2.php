<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">
<link rel="stylesheet" href="styles.css">
<link id="favicon" rel="icon" href="Logo_ImageOnly.png" type="image/png" sizes="16x16">
<title>INDET</title> 
<?php include_once "bootstrap.html"; ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@2.8.0/dist/Chart.min.js"></script>
</head>
<?php
require_once "libs/indet_dates_helper.php";

$indet_dates_helper = new INDET_DATES_HELPER();

require "database.php";
if(!isset($_SESSION["myusername"])){
session_destroy();
header("Refresh:0; url=index.php");
}
else{
    
if($_SESSION['myusertype']=="Adviser"){
    header("Refresh:0; url=leads_assigned");
}

include "partials/nav_bar.html";

if($_SESSION['myusertype']=="User"){

}
elseif($_SESSION['myusertype']=="Telemarketer"){
    include("telemarketer_script_app.php");
}
else{

    include "statistics/fetch_statistics_data.php";
?>
<!--header-->
<div align="center">


<div id="client_labels">
<style type="text/css">

	.profile_header div:after {
	  content: '';
	  height: 6200%;
	  width: 1px;

	  position: absolute;
	  right: 0;
	  top: 0; 

	  background-color: #000000;
	}

	.profile_header div:last-child:after{
		content: '';
	  	height: 0%;
	  	width: 1px;
	}

    .nav-tabs > li {
        float:none;
        display:inline-block;
        zoom:1;
    }

    .nav-tabs {
        text-align:center;
    }

    .nav-tabs li a{
        color:#337ab7 !important;
        border-bottom: 1px solid #ddd;
    }

    .nav-tabs li.active a {
        color:black !important;
        border-bottom: none;
    }

</style>

<div class="jumbotron">
    <h2 class="slide">ELITEINSURE OVERALL PERFORMANCE DATA</h2>
</div>

<div class="row profile_header">
	  	<div class="col-sm-4">
		  	<h3>
		  		Lead Generation Data:
		  	</h3>
            <ul class="nav nav-tabs">
                <li class="active"><a data-toggle="tab" href="#leadgen_data">Data</a></li>
                <li><a data-toggle="tab" href="#leadgen_graphs">Graphs</a></li>
            </ul>
		</div>
	  	<div class="col-sm-4">
	  		<h3>
	  			Production:
            </h3>
            <ul class="nav nav-tabs">
                <li class="active"><a data-toggle="tab" href="#production_data">Data</a></li>
                <li><a data-toggle="tab" href="#production_graphs">Graphs</a></li>
            </ul>

	  	</div>
  		<div class="col-sm-4" >
	  		<h3>
	  			Company Performance:
	  		</h3>
	  	</div>
</div>
    
<div class="row">
    <div class="col-sm-4" >
        <!-- First Column -->
        
        <div class="row">
            <div class="col-sm-12" >
                <div class="tab-content">
                    <div id="leadgen_data" class="tab-pane fade in active">
                        <?php include "statistics/leadgen_data.php" ?>                        
                    </div>
                    <div id="leadgen_graphs" class="tab-pane fade">            
                        <canvas id="lead-generation-quarterly-generated" width="800" height="450"></canvas>
                        <canvas id="lead-generation-quarterly-api" width="800" height="450"></canvas>  
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-4">
        <!-- Second Column -->
        <div class="row">
            <div class="col-sm-12">
                            
                <div class="tab-content">
                    <div id="production_data" class="tab-pane fade in active">
                        <?php include "statistics/production_data.php" ?>                        
                    </div>
                    <div id="production_graphs" class="tab-pane fade">
                        
                        <canvas id="quarterly-production" width="800" height="450"></canvas>

                        <canvas id="quarterly-production-api" width="800" height="450"></canvas>  

                        <canvas id="monthly-production" width="800" height="450"></canvas>

                        <canvas id="monthly-production-api" width="800" height="450"></canvas>  

                    </div>
                </div>
            </div>
        </div>


    </div>
    
    <div class="col-sm-4">
        <?php include "statistics/company_performance_data.php" ?>   
    </div>

</div>

        <br>

<!--
    For Debugging purposes
<table class="table">
    <thead>
        <tr>
            <th>Client</th>
            <th>Lead By</th>
            <th>API</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $total = 0;
            foreach($data->debug->client as $client_debug){
                if($client_debug->issued_api>0){
                    $total += $client_debug->issued_api;
                    echo "
                        <tr>
                            <td>$client_debug->name</td>
                            <td>$client_debug->lead_gen</td>
                            <td>$client_debug->issued_api</td>
                        </tr>
                    ";
                }
            }
            echo "
            <tr>
                <td>Total</td>
                <td></td>
                <td>$total</td>
            </tr>
            ";
        ?>
    </tbody>
</table>
-->

<?php require "statistics/statistics-charts-data.php" ?>

<?php require "statistics/statistics-charts.php" ?>

</html>

<?php

}


}
  

 


?>