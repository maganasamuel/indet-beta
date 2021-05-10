<?php
// Load the database configuration file

// Get status message
if(!empty($_GET['status'])){
    switch($_GET['status']){
        case 'succ':
            $statusType = 'alert-success';
            $statusMsg = 'Members data has been imported successfully.';
            break;
        case 'err':
            $statusType = 'alert-danger';
            $statusMsg = 'Some problem occurred, please try again.';
            break;
        case 'invalid_file':
            $statusType = 'alert-danger';
            $statusMsg = 'Please upload a valid CSV file.';
            break;
        default:
            $statusType = '';
            $statusMsg = '';
    }
}
?>

<!-- Display status message -->
<?php if(!empty($statusMsg)){ ?>
<div class="col-xs-12">
    <div class="alert <?php echo $statusType; ?>"><?php echo $statusMsg; ?></div>
</div>
<?php } ?>

<div class="row">
    <!-- Import link -->
    <div class="col-md-12 head">
        <div class="float-right">
            <a href="javascript:void(0);" class="btn btn-success" onclick="formToggle('importFrm');"><i class="plus"></i> Import</a>
        </div>
    </div>
    <!-- CSV file upload form -->
    <div class="col-md-12" id="importFrm" style="display: none;">
        <form action="importClientData.php" method="post" enctype="multipart/form-data">
            <input type="file" name="file" />
            <input type="submit" class="btn btn-primary" name="importSubmit" value="IMPORT">
        </form>
    </div>

    <!-- Data list table --> 
    <table class="table table-striped table-bordered">
        <thead class="thead-dark">
            <tr>
                <th>#ID</th>
                <th>Name</th>
                <th>Appt Date</th>
                <th>Phone</th>
                <th>Address</th>
                <th>Lead Gen</th>
                <th>Adviser</th>
                <th>Assigned Date</th>
                <th>Type of Lead</th>
                <th>Issued</th>
                <th>Date Issued</th>
                <th>Notes</th>
            </tr>
        </thead>
        <tbody>
        <?php
        // Get member rows
        require '../database.php';
        $query = "SELECT * FROM clients_tbl ORDER BY id DESC";
        $result = mysqli_query($con, $query);
            while($row = mysqli_fetch_array($result)){
        ?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td><?php echo $row['name']; ?></td>
                <td><?php echo $row['appt_date']; ?></td>
                <td><?php echo $row['appt_time']; ?></td>
                <td><?php echo $row['address']; ?></td>
                <td><?php echo $row['leadgen']; ?></td>
                <td><?php echo $row['assigned_to']; ?></td>
                <td><?php echo $row['assigned_date']; ?></td>
                <td><?php echo $row['type_of_lead']; ?></td>
                <td><?php echo $row['issued']; ?></td>
                <td><?php echo $row['date_issued']; ?></td>
                <td><?php echo $row['notes']; ?></td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
</div>

<!-- Show/hide CSV upload form -->
<script>
function formToggle(ID){
    var element = document.getElementById(ID);
    if(element.style.display === "none"){
        element.style.display = "block";
    }else{
        element.style.display = "none";
    }
}
</script>