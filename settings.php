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
	<title>Users</title>
<script>
$(function(){
	
/*
$('#me').dataTable({
"columnDefs": [ {
"targets": [14,15],
"orderable": false
} ]
});

*/
});

</script>
</head>

<body>
<div align="center">
  <div class="jumbotron">
    <h2 class="slide">Account Settings</h2>
	
</div>

<?php require "database.php";

	$query = "SELECT * FROM users where id =" . $_SESSION['myuserid'] . " LIMIT 1";
	$displayquery=mysqli_query($con,$query) or die('Could not look up user information; ' . mysqli_error($con));
    $result = mysqli_fetch_assoc($displayquery);
    extract($result);
?>
    <div class="col-sm-12">
	<form id="frmUser" name="frmUser" class="form-horizontal" novalidate="">
                        <div class="form-group">
                            <div class="col-sm-12">
                                <label for="inputTask" class="control-label text-center" id="report_text" style="color:green;"></label>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-4">
                                <label for="inputTask" class="col-sm-3 control-label pull-right">Username</label>
                            </div>
                            <div class="col-sm-4">
                                <input type="text" class="form-control has-error" id="username" name="username" placeholder="Username" value="<?php echo $username ?>" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-4">
                                <label for="inputTask" class="col-sm-3 control-label pull-right">Password</label>
                            </div>
                            <div class="col-sm-4">
                                <input type="password" class="form-control has-error" id="password" name="password" placeholder="Password" value="" required minlength="6" required="">
                                <label id="password_label" for="password" data-error="" data-success="Perfect!" style="color:red;"></label>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-4">
                                <label for="inputTask" class="col-sm-3 control-label pull-right">Confirm Password</label>
                            </div>
                            <div class="col-sm-4">
                                <input type="password" class="form-control has-error" id="confirm_password" placeholder="Password" value="" required>
                                <label id="confirm_password_label" for="confirm_password" data-error="Please enter the same password again" data-success="Perfect!" style="color:red;"></label>
                            </div>
                        </div>
                        <input type="hidden" id="formtype" name="formtype" value="update">
                        <input type="hidden" id="user_id" name="user_id" value="<?php echo $id ?>">
                        <button type="button" class="btn btn-primary" id="btn-save" value="add">Save changes</button>
                    
                    </form>
    </div>
  	<script src="js/users-crud.js"></script>
</body>

</html>

<?php

}
?>