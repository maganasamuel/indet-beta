<html>
    <head>
    	<meta name="viewport" content="width=device-width, initial-scale=1">
        <link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet"> 
        <link rel="stylesheet" type="text/css" href="styles.css">
        <link id="favicon" rel="icon" href="Logo_ImageOnly.png" type="image/png" sizes="16x16">
        <title>INDET</title> 
        <?php include 'bootstrap.html'; ?>
    </head>
<body>
<?php

    // Here we start as usual
    session_start();

    // More code...

    // Now we found in some database that the user whishes
    // the cookie to expire after for example 10 minutes
    // we can change it instantly !




    if(isset($_SESSION["myusername"])){
        session_destroy();
    }

    if(!isset($_POST["myusername"])){
?>
    <!--header-->
    <div align="container">
        <div class="row">
            <div class="col-md-3">
                <img id='logo' src="logo.png" class='center' style="margin-top:0px; height: auto;display: block; max-width: 100%;">
            </div>
        </div>
    <!--header end-->
        <form method="post">
            <div class='row addpad'>
            	<div class="col-md-3 center">
                    <h3 style="color:#0C4664;">
                        <strong>INDET<br>"the EliteInsure Tracker"</strong> 
                    </h3>
                </div>
            </div>
            <div class='row addpad'>
            	<div class="col-md-3 center">
                    <input class="logintext col-sm-2 form-control col-sm-2" name="myusername" type="text" placeholder="Username" />
                </div>
            </div>
            <div class='row addpad'>
            	<div class="col-md-3 center">
                    <input class="logintext col-sm-2 form-control col-sm-2" name="mypassword" type="password" placeholder="Password" />
                </div>
            </div>

            <div class='row addpad'>
                <div class='center col-md-3'>
                    <input type="submit" value="Sign In" class='btn btn-info center' style="width: 100%;">
                </div>
            </div>
        </form>
    </div>

<?php
}
    //if set
else{
    require "database.php";

    //GET USER INPUT
    $myusername=$_POST["myusername"];
    $mypassword=$_POST["mypassword"];
    
    //SEARCH DATABASE FOR USER'S USERNAME
    $query = "SELECT * FROM users WHERE username = '$myusername' LIMIT 1";
    $displayquery=mysqli_query($con,$query) or die('Could not look up user information; ' . mysqli_error($con));
    $userdata = mysqli_fetch_array($displayquery);
    
    //Login Logic
    // IF A MATCH IS FOUND EXTRACT USER DATA
    if(!empty($userdata)){
        extract($userdata);
    }
    // IF NOT, SHOW ERROR
    else{
            echo "
            <script>
            $(function(){
               $.alert({
                    title: 'Alert!',
                    content: 'Incorrect Username/Password.',
                     buttons: {
                        okay:function(){
                            window.location.href = 'index.php';
                                }
                     }
                });

            });
            </script>";
    }

    // VERIFY IF PASSWORD ENTERED IS EQUAL TO HASHED PASSWORD
    if (password_verify($mypassword, $password)) {
        
        $_SESSION["myusername"]=$myusername;
        $_SESSION["myuserid"]=$id;
        $_SESSION["myusertype"]=$type;
        $_SESSION["mylinkedid"] = $linked_id;
        
        header("Refresh:0; url=main");
        echo "Loading...";
    }
    else {
        echo "
            <script>
            $(function(){
               $.alert({
                    title: 'Alert!',
                    content: 'Incorrect Username/Password.',
                     buttons: {
                        okay:function(){
                            window.location.href = 'index.php';
                                }
                     }
                });

            });
            </script>";
    }
}




?>




</body>


</html>