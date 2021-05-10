 <?php
session_start();


if (empty($_SESSION['token'])) {
    $_SESSION['token'] = bin2hex(random_bytes(32));
}
$token = $_SESSION['token'];
if(!isset($_SESSION["myusername"])){
session_destroy();
header("Refresh:0; url=index.php");

}

else{
?>
 <html>
<head>

<!--nav bar-->
<?php include "partials/nav_bar.html";?>
<!--nav bar end-->
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php echo '<meta name="_token" content="$token">'; ?>
<link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">
<link rel="stylesheet" href="styles.css">
<link id="favicon" rel="icon" href="Logo_ImageOnly.png" type="image/png" sizes="16x16">
<title>INDET</title>
	<title>Clients</title>
</head>

<body>
<div align="center">
  <div class="jumbotron">
    <h2 class="slide">Assigned Leads</h2>
</div>
<?php require "database.php";
    $filters = "";
    if(!empty($_GET["from"]) && !empty($_GET["to"])){
        $filters = " AND clients_tbl.appt_date <= " . $_GET["to"] . " AND clients_tbl.appt_date >= " . $_GET["from"];
    }

	$query = "SELECT clients_tbl.lead_by as lead_by, clients_tbl.city as city, clients_tbl.zipcode as zipcode, clients_tbl.date_submitted,clients_tbl.id,clients_tbl.name,adviser_tbl.name as x,leadgen_tbl.name as y,clients_tbl.appt_date,clients_tbl.appt_time,clients_tbl.address,clients_tbl.leadgen,clients_tbl.assigned_to,clients_tbl.assigned_date,clients_tbl.type_of_lead,clients_tbl.issued,clients_tbl.date_issued,clients_tbl.notes 
    FROM clients_tbl LEFT JOIN adviser_tbl ON clients_tbl.assigned_to = adviser_tbl.id LEFT JOIN leadgen_tbl ON clients_tbl.leadgen = leadgen_tbl.id WHERE binned=0 AND clients_tbl.assigned_to = " . $_SESSION["mylinkedid"] . " AND clients_tbl.seen_status = 'Scheduled' $filters order by clients_tbl.date_issued desc;";
    $displayquery=mysqli_query($con,$query) or die('Could not look up script information; ' . mysqli_error($con));

    $query2 = "SELECT clients_tbl.lead_by as lead_by, clients_tbl.city as city, clients_tbl.zipcode as zipcode, clients_tbl.date_submitted,clients_tbl.id,clients_tbl.name,adviser_tbl.name as x,leadgen_tbl.name as y,clients_tbl.appt_date,clients_tbl.appt_time,clients_tbl.address,clients_tbl.leadgen,clients_tbl.assigned_to,clients_tbl.assigned_date,clients_tbl.type_of_lead,clients_tbl.issued,clients_tbl.date_issued,clients_tbl.notes 
    FROM clients_tbl LEFT JOIN adviser_tbl ON clients_tbl.assigned_to = adviser_tbl.id LEFT JOIN leadgen_tbl ON clients_tbl.leadgen = leadgen_tbl.id WHERE binned=0 AND clients_tbl.assigned_to = " . $_SESSION["mylinkedid"] . " AND clients_tbl.seen_status = 'Seen' $filters order by clients_tbl.date_issued desc;";
    $displayquery2=mysqli_query($con,$query2) or die('Could not look up script information; ' . mysqli_error($con));
    
	$query3 = "SELECT clients_tbl.lead_by as lead_by, clients_tbl.city as city, clients_tbl.zipcode as zipcode, clients_tbl.date_submitted,clients_tbl.id,clients_tbl.name,adviser_tbl.name as x,leadgen_tbl.name as y,clients_tbl.appt_date,clients_tbl.appt_time,clients_tbl.address,clients_tbl.leadgen,clients_tbl.assigned_to,clients_tbl.assigned_date,clients_tbl.type_of_lead,clients_tbl.issued,clients_tbl.date_issued,clients_tbl.notes 
    FROM clients_tbl LEFT JOIN adviser_tbl ON clients_tbl.assigned_to = adviser_tbl.id LEFT JOIN leadgen_tbl ON clients_tbl.leadgen = leadgen_tbl.id WHERE binned=0 AND clients_tbl.assigned_to = " . $_SESSION["mylinkedid"] . " AND clients_tbl.seen_status = 'Not Seen' $filters order by clients_tbl.date_issued desc;";
    $displayquery3=mysqli_query($con,$query3) or die('Could not look up script information; ' . mysqli_error($con));
?>
<div class="margined table-responsive">
    
<ul class="nav nav-tabs">
  <li class="active"><a data-toggle="tab" href="#scheduled" style="color:#0c4664 !important;">Scheduled</a></li>
  <li><a data-toggle="tab" href="#seen" style="color:#0c4664 !important;">Seen</a></li>
  <li><a data-toggle="tab" href="#not_seen" style="color:#0c4664 !important;">Not Seen</a></li>
    <li class="pull-right">
        <button class="btn btn-danger" onClick="window.location = 'leads_assigned'"><i class="fas fa-refresh"></i> Clear Filter</button>
    </li>
    <li class="pull-right">
        <button class="btn btn-primary"  data-toggle='modal' data-target='#filterModal' ><i class="fas fa-filter"></i> Filter Data</button>
    </li>
</ul>

<div class="tab-content">
  <div id="not_seen" class="tab-pane fade in">
    <div class="row">
        <div class="col-sm-12 text-center">
            <h1>Leads Not Seen</h1>
            <table id='not_seen_table' data-toggle="table" class="table table-striped " cellpadding="5px" cellspacing="5px" width='95%'>
                <thead>
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>Client Name</td>
                        <td>Appt Date</td>
                        <td>Phone Number</td>
                        <td>Address</td>
                    </tr>
                </thead>

                <tbody id="clients-list"">
                    <?php
                    WHILE($rows = mysqli_fetch_array($displayquery3)):
                        extract($rows);

                        $id=$rows["id"];
                        $name=$rows["name"];
                        $x=$rows["x"]; //advisername
                        $y=$rows["y"]; //leadgenname
                            $search_lead_gen = "";
                        $appt_date=$rows["appt_date"];
                        $appt_time=$rows["appt_time"];
                        $address=$rows["address"];
                        $city=$rows["city"];
                        $zipcode=$rows["zipcode"];
                        $lead_by=$rows["lead_by"];
                        $leadgen=$rows["leadgen"];
                        $assigned_to=$rows["assigned_to"];
                        $assigned_date=$rows["assigned_date"];
                        $type_of_lead=$rows["type_of_lead"];
                        $issued=$rows["issued"];
                        $date_issued=$rows["date_issued"];
                        $notes=$rows["notes"];
                        $date_submitted=$rows["date_submitted"];
                        $date_submitted_sort = $date_submitted;
                        $date_submitted = substr($date_submitted, 6,2) . "/" . substr($date_submitted, 4,2) . "/" . substr($date_submitted, 0,4);

                        $appt_date_sort=$appt_date;
                        $appt_date=date('d/m/Y',strtotime($appt_date));
                        $assigned_date=date('d/m/Y',strtotime($assigned_date));

                        /*$entrydate=$rows["entrydate"];
                        $startingdate=$rows["startingdate"];
                        $entrydate=substr($entrydate,0,4)."/".substr($entrydate,4,2)."/".substr($entrydate,6,2);

                        $startingdate=substr($startingdate,0,4)."/".substr($startingdate,4,2)."/".substr($startingdate,6,2);


                        $convertdate=substr($rows["entrydate"],6,2)."/".substr($rows["entrydate"],4,2)."/".substr($rows["entrydate"],0,4);

                        $convertstartingdate=substr($rows["startingdate"],6,2)."/".substr($rows["startingdate"],4,2)."/".substr($rows["startingdate"],0,4);
                        */
                        $lg = "";
                        if($lead_by=="Self-Generated"){
                            $lg = $x;
                        }
                        else{
                            $lg = $y;
                        }
                        echo "
                        <tr id='client$id' cellpadding='5px' cellspacing='5px'>
                            <td>
                                <button class='mark-seen pull-right btn btn-link btn-lg' value='$id'><i class='fa fa-eye text-success'></i></button>
                            </td>
                            
                            <td>
                                <button class='mark-scheduled pull-right btn btn-link btn-lg' value='$id'><i class='fa fa-eye text-warning'></i></button>
                            </td>

                            <td>
                                <button data-toggle='modal' data-target='#myModal' class='open-modal pull-right btn btn-link btn-lg' value='$id'><i class='fa fa-search'></i></button>
                            </td>

                            <td>$name</td>
                            <td data-order=".$appt_date_sort.">$appt_date</td>
                            <td>$appt_time</td>
                            <td>$address</td>
                        ";
                    ?>

                        
                    <?php 
                    echo "</tr>";	

                endwhile;
                ?>
                </tbody>
            </table>
        </div>
        <!--
            <div class="col-sm-6 text-center">
                <button id="btn-add" name="btn-add" data-toggle="modal" data-target="#myModal" class="pull-right btn btn-primary btn-m"><i class="fa fa-plus"></i> Add New Client</button>
            </div>
        -->
    </div>
  </div>
  <div id="scheduled" class="tab-pane fade in active">
    <div class="row">
        <div class="col-sm-12 text-center">
            <h1>Scheduled Leads</h1>
            <table id='scheduled_table' data-toggle="table" class="table table-striped " cellpadding="5px" cellspacing="5px" width='95%'>
                <thead>
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>Client Name</td>
                        <td>Appt Date</td>
                        <td>Phone Number</td>
                        <td>Address</td>
                    </tr>
                </thead>

                <tbody id="clients-list"">
                    <?php
                    WHILE($rows = mysqli_fetch_array($displayquery)):
                        extract($rows);

                        $id=$rows["id"];
                        $name=$rows["name"];
                        $x=$rows["x"]; //advisername
                        $y=$rows["y"]; //leadgenname
                            $search_lead_gen = "";
                        $appt_date=$rows["appt_date"];
                        $appt_time=$rows["appt_time"];
                        $address=$rows["address"];
                        $city=$rows["city"];
                        $zipcode=$rows["zipcode"];
                        $lead_by=$rows["lead_by"];
                        $leadgen=$rows["leadgen"];
                        $assigned_to=$rows["assigned_to"];
                        $assigned_date=$rows["assigned_date"];
                        $type_of_lead=$rows["type_of_lead"];
                        $issued=$rows["issued"];
                        $date_issued=$rows["date_issued"];
                        $notes=$rows["notes"];
                        $date_submitted=$rows["date_submitted"];
                        $date_submitted_sort = $date_submitted;
                        $date_submitted = substr($date_submitted, 6,2) . "/" . substr($date_submitted, 4,2) . "/" . substr($date_submitted, 0,4);

                        $appt_date_sort=$appt_date;
                        $appt_date=date('d/m/Y',strtotime($appt_date));
                        $assigned_date=date('d/m/Y',strtotime($assigned_date));

                        /*$entrydate=$rows["entrydate"];
                        $startingdate=$rows["startingdate"];
                        $entrydate=substr($entrydate,0,4)."/".substr($entrydate,4,2)."/".substr($entrydate,6,2);

                        $startingdate=substr($startingdate,0,4)."/".substr($startingdate,4,2)."/".substr($startingdate,6,2);


                        $convertdate=substr($rows["entrydate"],6,2)."/".substr($rows["entrydate"],4,2)."/".substr($rows["entrydate"],0,4);

                        $convertstartingdate=substr($rows["startingdate"],6,2)."/".substr($rows["startingdate"],4,2)."/".substr($rows["startingdate"],0,4);
                        */
                        $lg = "";
                        if($lead_by=="Self-Generated"){
                            $lg = $x;
                        }
                        else{
                            $lg = $y;
                        }
                        echo "
                        <tr id='client$id' cellpadding='5px' cellspacing='5px'>
                            <td>
                                <button class='mark-seen pull-right btn btn-link btn-lg' value='$id'><i class='fa fa-eye text-success'></i></button>
                            </td>
                            
                            <td>
                                <button  data-toggle='modal' data-target='#confirmModal' class='mark-not-seen pull-right btn btn-link btn-lg' value='$id'><i class='fa fa-eye text-danger'></i></button>
                            </td>

                            <td>
                                <button data-toggle='modal' data-target='#myModal' type='image' class='open-modal pull-right btn btn-link btn-lg' value='$id'><i class='fa fa-search'></i></button>
                            </td>

                            <td>$name</td>
                            <td data-order=".$appt_date_sort.">$appt_date</td>
                            <td>$appt_time</td>
                            <td>$address</td>
                        ";
                    ?>

                        
                    <?php 
                    echo "</tr>";	

                endwhile;
                ?>
                </tbody>
            </table>
        </div>
        <!--
            <div class="col-sm-6 text-center">
                <button id="btn-add" name="btn-add" data-toggle="modal" data-target="#myModal" class="pull-right btn btn-primary btn-m"><i class="fa fa-plus"></i> Add New Client</button>
            </div>
        -->
    </div>
  </div>
  <div id="seen" class="tab-pane fade">
    <div class="row">
        <div class="col-sm-12 text-center">
            <h1>Seen Leads</h1>
            <table id='seen_table' data-toggle="table" class="table table-striped " cellpadding="5px" cellspacing="5px" width='95%'>
                <thead>
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>Client Name</td>
                        <td>Appt Date</td>
                        <td>Phone Number</td>
                        <td>Address</td>
                    </tr>
                </thead>

                <tbody id="clients-list"">
                    <?php
                    WHILE($rows = mysqli_fetch_array($displayquery2)):
                        extract($rows);

                        $id=$rows["id"];
                        $name=$rows["name"];
                        $x=$rows["x"]; //advisername
                        $y=$rows["y"]; //leadgenname
                            $search_lead_gen = "";
                        $appt_date=$rows["appt_date"];
                        $appt_time=$rows["appt_time"];
                        $address=$rows["address"];
                        $city=$rows["city"];
                        $zipcode=$rows["zipcode"];
                        $lead_by=$rows["lead_by"];
                        $leadgen=$rows["leadgen"];
                        $assigned_to=$rows["assigned_to"];
                        $assigned_date=$rows["assigned_date"];
                        $type_of_lead=$rows["type_of_lead"];
                        $issued=$rows["issued"];
                        $date_issued=$rows["date_issued"];
                        $notes=$rows["notes"];
                        $date_submitted=$rows["date_submitted"];
                        $date_submitted_sort = $date_submitted;
                        $date_submitted = substr($date_submitted, 6,2) . "/" . substr($date_submitted, 4,2) . "/" . substr($date_submitted, 0,4);

                        $appt_date_sort=$appt_date;
                        $appt_date=date('d/m/Y',strtotime($appt_date));
                        $assigned_date=date('d/m/Y',strtotime($assigned_date));

                        /*$entrydate=$rows["entrydate"];
                        $startingdate=$rows["startingdate"];
                        $entrydate=substr($entrydate,0,4)."/".substr($entrydate,4,2)."/".substr($entrydate,6,2);

                        $startingdate=substr($startingdate,0,4)."/".substr($startingdate,4,2)."/".substr($startingdate,6,2);


                        $convertdate=substr($rows["entrydate"],6,2)."/".substr($rows["entrydate"],4,2)."/".substr($rows["entrydate"],0,4);

                        $convertstartingdate=substr($rows["startingdate"],6,2)."/".substr($rows["startingdate"],4,2)."/".substr($rows["startingdate"],0,4);
                        */
                        $lg = "";
                        if($lead_by=="Self-Generated"){
                            $lg = $x;
                        }
                        else{
                            $lg = $y;
                        }
                        echo "
                        <tr id='client$id' cellpadding='5px' cellspacing='5px'>
                            <td>
                                <button class='mark-scheduled pull-right btn btn-link btn-lg' value='$id'><i class='fa fa-eye text-warning'></i></button>
                            </td>
                            
                            <td>
                            <button  data-toggle='modal' data-target='#confirmModal' class='mark-not-seen pull-right btn btn-link btn-lg' value='$id'><i class='fa fa-eye text-danger'></i></button>
                            </td>

                            <td>
                                <button data-toggle='modal' data-target='#myModal' type='image' class='open-modal pull-right btn btn-link btn-lg' value='$id'><i class='fa fa-search'></i></button>
                            </td>

                            <td>$name</td>
                            <td data-order=".$appt_date_sort.">$appt_date</td>
                            <td>$appt_time</td>
                            <td>$address</td>
                        ";
                    ?>

                        
                    <?php 
                    echo "</tr>";	

                endwhile;
                ?>
                </tbody>
            </table>
        </div>
        <!--
            <div class="col-sm-6 text-center">
                <button id="btn-add" name="btn-add" data-toggle="modal" data-target="#myModal" class="pull-right btn btn-primary btn-m"><i class="fa fa-plus"></i> Add New Client</button>
            </div>
        -->
    </div>
  </div>
</div>
<br>
</div>

	<!--
	Modals
	Editor
	-->
	<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header" style="background-color: #286090; ">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span style="color:white;" aria-hidden="true">×</span></button>
                    <h4 class="modal-title" id="myModalLabel" style="color:white;">Client Information</h4>
                </div>
                <div class="modal-body">
                    <form id="frmClient" name="frmClient" class="form-horizontal" novalidate="">
                        <div class="form-group error">
                            <div class="col-sm-12">
                                <h3 id="name" class="text-left"></h3>
                            </div>  
                        </div>
                        <hr>
                        <div class="form-group error">
                            
                            <div class="col-sm-6">
                                <h3 class="text-left" id="date_submitted" ></h3>
                            </div>

                            <div class="col-sm-6">
                                <h3 class="text-left" id="appt_date" ></h3>
                            </div>                        
                        </div>
                        <hr>

                        <div class="form-group error">                   
                            
                            <div class="col-sm-6">
                                <h3 class="text-left" id="assigned_date" ></h3>
                            </div>       
                            
                            <div class="col-sm-6">
                                <h3 class="text-left" id="phone_num" ></h3>
                            </div>       
                        </div>
                        <hr>
                        
                        <div class="form-group error">
                            
                            <div class="col-sm-12">
                                <h3 class="text-left" id="address" ></h3>
                            </div>
                        </div>
                        <hr>

                        <div class="form-group error">    
                            <div class="col-sm-6">
                                <h3 class="text-left" id="lead_by" ></h3>
                            </div>    
                            <div class="col-sm-6">
                                <h3 class="text-left" id="leadgen" ></h3>
                            </div>    
                        </div>
                        <hr>

                        <div class="form-group error">
                            <h2 class="col-sm-12">
                                Notes:
                            </h2>
                            <div class="col-sm-12">
                                <h3 class="text-left" id="notes" ></h3>
                            </div>
                        </div>
                        <hr>

                            
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <!--
	End of Editor
	-->

	<!--
		Confirm Not Seen
	-->
	<div class="modal fade" id="confirmModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                    <h4 class="modal-title" id="myModalLabel">Confirm Deletion</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group error row">
                        <label for="inputTask" class="col-sm-12 text-left">
                            Why weren't you able to see this client?                            
                        </label>
                        <textarea name="notes" id="seen_notes" class="form-control col-sm-12"></textarea>
                        <input type="hidden" id="client_not_seen">
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="row">
                        <div class="col-lg-6">
                        </div>
                        <div class="col-lg-3">
                            <button type="button" class="btn btn-danger form-control confirm-not-seen">Confirm</button>
                        </div>
                        <div class="col-lg-3">
                            <button type="button" class="btn btn-primary form-control" data-dismiss="modal">Cancel</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--
		End of Confirm Not Seen
    -->
       
       
	<!--
		Filter Modal
	-->
	<div class="modal fade" id="filterModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                    <h4 class="modal-title" id="myModalLabel">Input Date Filter</h4>
                </div>
                <div class="modal-body">                    
                    <div class="form-group error row">
                        <div class="row">
                            <div class="col-sm-3">
                                <label for="inputTask">
                                    Filter From
                                </label>
                            </div>
                            <div class="col-sm-3">
                                <input type="text" class="form-control datepicker" id="from">
                            </div>
                            <div class="col-sm-3">
                                <label for="inputTask">
                                    Filter To
                                </label>
                            </div>
                            <div class="col-sm-3">
                                <input type="text" class="form-control datepicker" id="to">
                            </div>
                        </div>                        
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="row">
                        <div class="col-lg-6">
                        </div>
                        <div class="col-lg-3">
                            <button type="button" class="btn btn-danger form-control" id="filter">Confirm</button>
                        </div>
                        <div class="col-lg-3">
                            <button type="button" class="btn btn-primary form-control" data-dismiss="modal">Cancel</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--
		End of Filter Modal
   	-->
      <script src="js/leads_assigned_view.js"></script>

      <script>
          var scheduled_table = null;
          var seen_table = null;
          var not_seen_table = null;
          $(function(){    
              

            $('.datepicker').datepicker({
                dateFormat: 'dd/mm/yy'
            });

            $('#not_seen_table').dataTable({
                "order": [[ 4, "desc" ]],
                "columnDefs": 
                [ {
                    "targets": [1,2] ,
                    "orderable": true
                } ]
            });   
            not_seen_table = $("#not_seen_table").DataTable();

            $('#scheduled_table').dataTable({
                "order": [[ 4, "desc" ]],
                "columnDefs": 
                [ {
                    "targets": [1,2] ,
                    "orderable": true
                } ]
            });
            scheduled_table = $("#scheduled_table").DataTable();
            
            $('#seen_table').dataTable({
                "order": [[ 4, "desc" ]],
                "columnDefs": 
                [ {
                    "targets": [1,2] ,
                    "orderable": true
                } ]
            });
            seen_table = $("#seen_table").DataTable();

        });
      </script>
</body>

</html>

<?php

}
?>