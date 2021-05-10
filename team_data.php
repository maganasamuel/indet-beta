 <?php

header('Content-Type: application/json');
session_start();
date_default_timezone_set('Pacific/Auckland');
//Restrict access to admin only
include "partials/admin_only.php";

if(!isset($_SESSION["myusername"])){
session_destroy();
header("Refresh:0; url=index.php");
}

else{
    $restrict_session_check = true;
    require "database.php";

    $team_id = $_GET["id"];
    $team = new stdClass();

    $query = "SELECT * FROM teams WHERE id = $team_id";
    $displayquery=mysqli_query($con,$query) or die('Could not look up user information; ' . mysqli_error($con));
    $row=mysqli_fetch_assoc($displayquery);
    $leader_id = $row["leader"];
    $team->name = $row["name"];

    $query = "SELECT * FROM adviser_tbl WHERE id = $leader_id";
    $displayquery=mysqli_query($con,$query) or die('Could not look up user information; ' . mysqli_error($con));
    $row=mysqli_fetch_assoc($displayquery);
    
    $team->leader = $row;
    

    $team->advisers = array();
    $team->adviser_ids = array();

    $query = "SELECT * FROM adviser_tbl";
    $query .= ($team->name!="EliteInsure Team") ? " WHERE team_id = $team_id" : "";
    $query .= " ORDER BY name ASC";

    $displayquery=mysqli_query($con,$query) or die('Could not look up user information; ' . mysqli_error($con));

    while($row=mysqli_fetch_assoc($displayquery)){
        $team->advisers[] = $row;
        $team->adviser_ids[] = $row["id"];
    }
    
    $team->adviser_ids_string = implode(",", $team->adviser_ids);
    
    print json_encode($team);
    function DateTimeToNZEntry($date_submitted){
    return substr($date_submitted,6,4).substr($date_submitted,3,2).substr($date_submitted,0,2);
    }

    function NZEntryToDateTime($NZEntry){
        return substr($NZEntry,6,2) . "/" . substr($NZEntry,4,2) . "/" . substr($NZEntry, 0, 4);
    }

    function CheckTransactionStatus($status){
        $issued = stripos($status, 'Billed Issued Leads') !== false;
        $assigned = stripos($status, 'Billed Assigned Leads') !== false;
        $op = $status;
        if($issued){
            $op = "Billed Issued Leads";
        }
        elseif($assigned){
            $op = "Billed Assigned Leads";
        }

        return $op;
    }
}
?>