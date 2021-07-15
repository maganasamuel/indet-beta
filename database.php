<?php
if ('' == session_id()) {
    session_start();
}

if (false === strpos($_SERVER['REQUEST_URI'], '?')) {
    if ('GET' == $_SERVER['REQUEST_METHOD']) {
        if (! isset($restrict_session_check)) {
            if (isset($_SESSION['myusertype'])) {
                include_once('user_checker.php');
            }

            echo "
				<script>
				$(function(){

				function session_check(){
				$.get('session_check.php',function(data){
				console.log('Session Checked. ' + data);
				if(data=='SESSION INACTIVE'){
				console.debug('Session Expired, logging out.');
				window.location.replace('index.php');
				}
				});
				};
				window.setInterval(function(){ session_check() }, 5000);
				});
				</script>
				";
        }
    }
}

$_SESSION['LAST_ACTIVITY'] = $_SERVER['REQUEST_TIME'];

$config = parse_ini_file('libs/api/classes/configurations/config.ini');

$host = $config['host'];
$username = $config['username'];
$password = $config['password'];
$db = $config['database'];

$con = mysqli_connect($host, $username, $password, $db) or die('could not connect to sql');

if (! $con) {
    echo '<div>';
    echo 'Failed to connect to MySQL: ' . mysqli_connect_error();
    echo '</div>';
}
  

  $_SESSION['LAST_ACTIVITY'] = $_SERVER['REQUEST_TIME'];  

  // Production
  // $host="localhost";
  // $username="onlinei1_user";
  // $password="cW;h8Yjw@h_}";
  // $db="onlinei1_indet";
 
  // Local Development
  $host="localhost";
  $username="root";
  $password="";
  $db="ei_indet";


  $con=mysqli_connect($host,$username,$password,$db)or die("could not connect to sql");
  if (!$con) {
    echo "<div>";
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
    echo "</div>";  
  }

?>
